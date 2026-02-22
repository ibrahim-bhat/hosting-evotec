<?php
/**
 * PDF Invoice Helper Functions
 * Generates PDF invoices for orders and manual payments
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/settings_helper.php';
require_once __DIR__ . '/payment_settings_helper.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Generate PDF invoice for hosting order
 */
function generateOrderPDFInvoice($conn, $orderId, $download = true) {
    // Get order details
    $order = getOrderById($conn, $orderId);
    if (!$order) {
        return ['success' => false, 'message' => 'Order not found'];
    }
    
    // Get user and package details
    $user = getUserById($conn, $order['user_id']);
    $package = getPackageById($conn, $order['package_id']);
    
    // Get company settings
    $company = getCompanySettings($conn);
    
    // Fetch coupon code if coupon was used
    $couponCode = '';
    if (!empty($order['coupon_id'])) {
        $cStmt = $conn->prepare("SELECT code FROM coupons WHERE id = ?");
        $cStmt->bind_param("i", $order['coupon_id']);
        $cStmt->execute();
        $cResult = $cStmt->get_result();
        if ($cRow = $cResult->fetch_assoc()) {
            $couponCode = $cRow['code'];
        }
        $cStmt->close();
    }
    
    // Prepare invoice data
    $invoiceData = [
        'type' => 'order',
        'invoice_number' => $order['order_number'],
        'order_id' => $order['id'],
        'user' => $user,
        'package' => $package,
        'order' => $order,
        'company' => $company,
        'issue_date' => date('d M, Y', strtotime($order['created_at'])),
        'due_date' => date('d M, Y', strtotime($order['created_at'] . ' +30 days')),
        'service_period' => [
            'start' => $order['start_date'] ? date('d M, Y', strtotime($order['start_date'])) : 'N/A',
            'end' => $order['expiry_date'] ? date('d M, Y', strtotime($order['expiry_date'])) : 'N/A'
        ],
        'billing_cycle' => ucfirst(str_replace('years', ' Years', $order['billing_cycle'])),
        'discount_amount' => (float)($order['discount_amount'] ?? 0),
        'coupon_code' => $couponCode,
        'items' => [
            [
                'description' => $package['name'] . ' Hosting (' . ucfirst(str_replace('years', ' Years', $order['billing_cycle'])) . ')',
                'quantity' => 1,
                'unit_price' => $order['base_price'],
                'setup_fee' => $order['setup_fee'],
                'subtotal' => $order['subtotal'],
                'gst_amount' => $order['gst_amount'],
                'gst_percentage' => getGlobalGstPercentage($conn),
                'processing_fee' => $order['processing_fee'],
                'total' => $order['total_amount']
            ]
        ]
    ];
    
    return generatePDFInvoice($invoiceData, $download);
}

/**
 * Generate PDF invoice for manual payment
 */
function generateManualPaymentPDFInvoice($conn, $paymentId, $download = true) {
    // Get manual payment details
    $payment = getManualPaymentByIdForInvoice($conn, $paymentId);
    if (!$payment) {
        return ['success' => false, 'message' => 'Manual payment not found'];
    }
    
    // Get user details
    $user = getUserById($conn, $payment['user_id']);
    
    // Get company settings
    $company = getCompanySettings($conn);
    
    // Prepare invoice data
    $invoiceData = [
        'type' => 'manual',
        'invoice_number' => 'MP-' . str_pad($payment['id'], 6, '0', STR_PAD_LEFT),
        'payment_id' => $payment['id'],
        'user' => $user,
        'payment' => $payment,
        'company' => $company,
        'issue_date' => date('d M, Y', strtotime($payment['order_date'])),
        'due_date' => date('d M, Y', strtotime($payment['order_date'])),
        'service_period' => [
            'start' => $payment['start_date'] ? date('d M, Y', strtotime($payment['start_date'])) : 'N/A',
            'end' => $payment['end_date'] ? date('d M, Y', strtotime($payment['end_date'])) : 'N/A'
        ],
        'items' => [
            [
                'description' => $payment['payment_reason'],
                'quantity' => 1,
                'unit_price' => $payment['payment_amount'],
                'setup_fee' => 0,
                'subtotal' => $payment['payment_amount'],
                'gst_amount' => 0,
                'gst_percentage' => 0,
                'processing_fee' => 0,
                'total' => $payment['payment_amount']
            ]
        ]
    ];
    
    return generatePDFInvoice($invoiceData, $download);
}

/**
 * Generate PDF invoice using dompdf
 */
function generatePDFInvoice($invoiceData, $download = true) {
    // Generate HTML content
    $html = generateInvoiceHTML($invoiceData);
    
    if ($download) {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $filename = 'Invoice-' . $invoiceData['invoice_number'] . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }
    
    return ['success' => true, 'html' => $html];
}

/**
 * Generate HTML content for invoice
 */
function generateInvoiceHTML($invoiceData) {
    $company = $invoiceData['company'];
    $user = $invoiceData['user'];
    $invoiceNumber = $invoiceData['invoice_number'];
    $issueDate = $invoiceData['issue_date'];
    $dueDate = $invoiceData['due_date'];
    $items = $invoiceData['items'];
    
    // Calculate totals
    $subtotal = 0;
    $totalGst = 0;
    $totalProcessingFee = 0;
    $totalAmount = 0;
    
    foreach ($items as $item) {
        $subtotal += $item['subtotal'];
        $totalGst += $item['gst_amount'];
        $totalProcessingFee += $item['processing_fee'];
        $totalAmount += $item['total'];
    }
    
    $discountAmount = $invoiceData['discount_amount'] ?? 0;
    $couponCode = $invoiceData['coupon_code'] ?? '';
    $finalTotal = $totalAmount - $discountAmount;
    
    $currency = trim($company['currency_symbol'] ?? '₹');
    if ($currency === '') {
        $currency = '₹';
    }
    $gstPercent = $items[0]['gst_percentage'] ?? 18;
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Invoice <?php echo htmlspecialchars($invoiceNumber); ?></title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: "DejaVu Sans", sans-serif; line-height: 1.45; color: #334155; background: #fff; font-size: 11px; }
            .invoice-container { max-width: 720px; margin: 0 auto; padding: 32px 40px; background: #fff; }
            .invoice-header { width: 100%; margin-bottom: 28px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0; }
            .invoice-header td { vertical-align: top; padding: 0; }
            .invoice-header td:last-child { text-align: right; }
            .company-name { font-size: 18px; font-weight: bold; color: #0f172a; letter-spacing: -0.02em; margin-bottom: 4px; }
            .company-meta { font-size: 10px; color: #64748b; }
            .invoice-title { font-size: 22px; font-weight: bold; color: #0f172a; letter-spacing: -0.02em; margin-bottom: 4px; }
            .invoice-meta { font-size: 10px; color: #64748b; }
            .bill-to { margin-bottom: 24px; }
            .bill-to-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; margin-bottom: 6px; }
            .bill-to-name { font-size: 13px; font-weight: 600; color: #0f172a; margin-bottom: 2px; }
            .bill-to-details { font-size: 10px; color: #64748b; }
            .section-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; margin-bottom: 8px; }
            .service-details { margin-bottom: 20px; font-size: 10px; color: #475569; }
            .service-details div { margin-bottom: 2px; }
            .items-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
            .items-table th { text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 600; padding: 8px 10px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
            .items-table th.text-right { text-align: right; }
            .items-table th.text-center { text-align: center; }
            .items-table td { padding: 10px; border-bottom: 1px solid #f1f5f9; vertical-align: top; font-size: 10px; color: #334155; }
            .items-table .text-right { text-align: right; }
            .items-table .text-center { text-align: center; }
            .item-desc { font-weight: 600; color: #0f172a; margin-bottom: 2px; }
            .item-period { font-size: 9px; color: #94a3b8; }
            .table-subtotal { text-align: right; margin-bottom: 16px; font-size: 10px; color: #475569; }
            .table-subtotal strong { color: #0f172a; }
            .totals-table { width: 260px; margin-left: auto; border-collapse: collapse; border: 1px solid #e2e8f0; font-size: 10px; }
            .totals-table td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; color: #64748b; }
            .totals-table td:last-child { text-align: right; }
            .totals-table tr:last-child td { border-bottom: none; background: #f8fafc; font-weight: 600; color: #0f172a; }
            .totals-table .discount { color: #059669; }
            .invoice-footer { margin-top: 28px; padding-top: 16px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 9px; color: #94a3b8; }
            .invoice-footer .company { font-weight: 600; color: #64748b; margin-bottom: 2px; }
        </style>
    </head>
    <body>
        <div class="invoice-container">
            <table class="invoice-header" cellpadding="0" cellspacing="0"><tr>
                <td>
                    <div class="company-name"><?php echo htmlspecialchars($company['name']); ?></div>
                    <div class="company-meta"><?php echo htmlspecialchars($company['email']); ?></div>
                </td>
                <td>
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-meta">#<?php echo htmlspecialchars($invoiceNumber); ?> &nbsp;&bull;&nbsp; <?php echo $issueDate; ?></div>
                </td>
            </tr></table>

            <div class="bill-to">
                <div class="bill-to-label">Bill to</div>
                <div class="bill-to-name"><?php echo htmlspecialchars($user['name']); ?></div>
                <div class="bill-to-details"><?php echo htmlspecialchars($user['email']); ?></div>
                <?php if (!empty($user['phone'])): ?>
                    <div class="bill-to-details"><?php echo htmlspecialchars($user['phone']); ?></div>
                <?php endif; ?>
            </div>

            <div class="section-label">Service details</div>
            <div class="service-details">
                <?php if ($invoiceData['type'] === 'order'): ?>
                    <div><strong>Package:</strong> <?php echo htmlspecialchars($invoiceData['package']['name']); ?></div>
                    <div><strong>Billing cycle:</strong> <?php echo $invoiceData['billing_cycle']; ?></div>
                    <?php if (!empty($invoiceData['order']['domain_name'])): ?>
                        <div><strong>Domain:</strong> <?php echo htmlspecialchars($invoiceData['order']['domain_name']); ?></div>
                    <?php endif; ?>
                <?php else: ?>
                    <div><strong>Payment type:</strong> Manual payment</div>
                    <div><strong>Reason:</strong> <?php echo htmlspecialchars($invoiceData['payment']['payment_reason']); ?></div>
                <?php endif; ?>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Unit price</th>
                        <th class="text-right">Setup fee</th>
                        <th class="text-right">Subtotal</th>
                        <th class="text-right">GST</th>
                        <th class="text-right">Processing fee</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div class="item-desc"><?php echo htmlspecialchars($item['description']); ?></div>
                                <?php if ($invoiceData['type'] === 'order' && ($invoiceData['service_period']['start'] !== 'N/A' || $invoiceData['service_period']['end'] !== 'N/A')): ?>
                                    <div class="item-period">Service period: <?php echo $invoiceData['service_period']['start']; ?> – <?php echo $invoiceData['service_period']['end']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                            <td class="text-right"><?php echo $currency . number_format($item['unit_price'], 2); ?></td>
                            <td class="text-right"><?php echo $currency . number_format($item['setup_fee'], 2); ?></td>
                            <td class="text-right"><?php echo $currency . number_format($item['subtotal'], 2); ?></td>
                            <td class="text-right"><?php echo $currency . number_format($item['gst_amount'], 2); ?></td>
                            <td class="text-right"><?php echo $currency . number_format($item['processing_fee'], 2); ?></td>
                            <td class="text-right"><strong><?php echo $currency . number_format($item['total'], 2); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="table-subtotal">Subtotal: <strong><?php echo $currency . number_format($subtotal, 2); ?></strong></div>

            <table class="totals-table">
                <?php if ($totalGst > 0): ?>
                    <tr><td>GST (<?php echo $gstPercent; ?>%)</td><td><?php echo $currency . number_format($totalGst, 2); ?></td></tr>
                <?php endif; ?>
                <?php if ($totalProcessingFee > 0): ?>
                    <tr><td>Processing fee</td><td><?php echo $currency . number_format($totalProcessingFee, 2); ?></td></tr>
                <?php endif; ?>
                <?php if ($discountAmount > 0): ?>
                    <tr><td>Coupon<?php echo $couponCode ? ' (' . htmlspecialchars($couponCode) . ')' : ''; ?></td><td class="discount">-<?php echo $currency . number_format($discountAmount, 2); ?></td></tr>
                <?php endif; ?>
                <tr><td>Total</td><td><?php echo $currency . number_format($discountAmount > 0 ? $finalTotal : $totalAmount, 2); ?></td></tr>
            </table>

            <div class="invoice-footer">
                <div class="company"><?php echo htmlspecialchars($company['name']); ?></div>
                <div>For queries: <?php echo htmlspecialchars($company['email']); ?></div>
                <div style="margin-top:6px;">Computer-generated invoice.</div>
            </div>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

/**
 * Get company settings for invoice
 */
function getCompanySettings($conn) {
    $settings = [
        'name' => getCompanyName($conn),
        'email' => getSetting($conn, 'company_email', 'hi@infralabs.in'),
        'phone' => getSetting($conn, 'company_phone', '+91 123 456 7890'),
        'address' => getSetting($conn, 'company_address', ''),
        'gst_number' => getSetting($conn, 'company_gst', ''),
        'logo' => getCompanyLogo($conn),
        'currency_symbol' => getSetting($conn, 'currency_symbol', '₹'),
        'currency_code' => getSetting($conn, 'currency_code', 'INR')
    ];
    
    return $settings;
}

/**
 * Get manual payment by ID (wrapper function to avoid recursion)
 */
function getManualPaymentByIdForInvoice($conn, $paymentId) {
    require_once __DIR__ . '/payment_settings_helper.php';
    return getManualPaymentById($conn, $paymentId);
}

?>
