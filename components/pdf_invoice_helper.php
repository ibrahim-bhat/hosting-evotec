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
        $options->set('defaultFont', 'Deja Vu Sans');
        
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
        <title>Invoice <?php echo htmlspecialchars($invoiceNumber); ?></title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: Deja Vu Sans, sans-serif; line-height: 1.5; color: #1f2937; background: #fff; font-size: 14px; }
            .invoice-container { max-width: 800px; margin: 0 auto; padding: 40px; background: #fff; }
            .user-block { margin-bottom: 28px; }
            .user-name { font-size: 20px; font-weight: bold; color: #111; margin-bottom: 6px; }
            .user-details { color: #4b5563; font-size: 14px; }
            .section-title { font-size: 16px; font-weight: bold; color: #2563eb; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 2px solid #2563eb; }
            .service-details { margin-bottom: 24px; }
            .service-details div { margin-bottom: 4px; color: #374151; }
            .items-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
            .items-table th { background: #2563eb; color: #fff; padding: 12px 10px; text-align: left; font-weight: 600; font-size: 13px; }
            .items-table th.text-right { text-align: right; }
            .items-table th.text-center { text-align: center; }
            .items-table td { padding: 12px 10px; border-bottom: 1px solid #e5e7eb; vertical-align: top; color: #374151; }
            .items-table .text-right { text-align: right; }
            .items-table .text-center { text-align: center; }
            .item-desc { font-weight: 600; margin-bottom: 4px; }
            .item-period { font-size: 12px; color: #6b7280; }
            .table-subtotal { text-align: right; margin-bottom: 20px; font-weight: bold; color: #111; }
            .summary-band { background: #374151; color: #fff; padding: 16px 20px; margin-top: 4px; }
            .summary-band div { margin-bottom: 6px; font-size: 14px; }
            .summary-band div:last-child { margin-bottom: 0; }
            .summary-band .label { color: #d1d5db; }
            .summary-band .value { font-weight: bold; }
            .invoice-footer { text-align: center; padding-top: 24px; margin-top: 24px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 12px; }
            .invoice-footer a { color: #2563eb; }
        </style>
    </head>
    <body>
        <div class="invoice-container">
            <!-- User (Bill To) at top -->
            <div class="user-block">
                <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                <div class="user-details"><?php echo htmlspecialchars($user['email']); ?></div>
                <?php if (!empty($user['phone'])): ?>
                    <div class="user-details">Phone: <?php echo htmlspecialchars($user['phone']); ?></div>
                <?php endif; ?>
            </div>

            <!-- Service Details -->
            <div class="section-title">Service Details</div>
            <div class="service-details">
                <?php if ($invoiceData['type'] === 'order'): ?>
                    <div><strong>Package:</strong> <?php echo htmlspecialchars($invoiceData['package']['name']); ?></div>
                    <div><strong>Billing Cycle:</strong> <?php echo $invoiceData['billing_cycle']; ?></div>
                    <?php if (!empty($invoiceData['order']['domain_name'])): ?>
                        <div><strong>Domain:</strong> <?php echo htmlspecialchars($invoiceData['order']['domain_name']); ?></div>
                    <?php endif; ?>
                <?php else: ?>
                    <div><strong>Payment Type:</strong> Manual Payment</div>
                    <div><strong>Reason:</strong> <?php echo htmlspecialchars($invoiceData['payment']['payment_reason']); ?></div>
                <?php endif; ?>
            </div>

            <!-- Charges table with blue header -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Setup Fee</th>
                        <th class="text-right">Subtotal</th>
                        <th class="text-right">GST</th>
                        <th class="text-right">Processing Fee</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div class="item-desc"><?php echo htmlspecialchars($item['description']); ?></div>
                                <?php if ($invoiceData['type'] === 'order' && ($invoiceData['service_period']['start'] !== 'N/A' || $invoiceData['service_period']['end'] !== 'N/A')): ?>
                                    <div class="item-period">Service Period: <?php echo $invoiceData['service_period']['start']; ?> to <?php echo $invoiceData['service_period']['end']; ?></div>
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

            <!-- Subtotal at bottom-right of table -->
            <div class="table-subtotal">Subtotal: <strong><?php echo $currency . number_format($subtotal, 2); ?></strong></div>

            <!-- Bottom summary band (dark) -->
            <div class="summary-band">
                <?php if ($totalGst > 0): ?>
                    <div><span class="label">GST (<?php echo $gstPercent; ?>%):</span> <span class="value"><?php echo $currency . number_format($totalGst, 2); ?></span></div>
                <?php endif; ?>
                <?php if ($totalProcessingFee > 0): ?>
                    <div><span class="label">Processing Fee:</span> <span class="value"><?php echo $currency . number_format($totalProcessingFee, 2); ?></span></div>
                <?php endif; ?>
                <?php if ($discountAmount > 0): ?>
                    <div><span class="label">Coupon Discount<?php echo $couponCode ? ' (' . htmlspecialchars($couponCode) . ')' : ''; ?>:</span> <span class="value" style="color:#6ee7b7;">-<?php echo $currency . number_format($discountAmount, 2); ?></span></div>
                <?php endif; ?>
                <div style="margin-top:8px; padding-top:8px; border-top:1px solid #4b5563;"><span class="label">Total Amount:</span> <span class="value"><?php echo $currency . number_format($discountAmount > 0 ? $finalTotal : $totalAmount, 2); ?></span></div>
            </div>

            <!-- Footer: company and contact -->
            <div class="invoice-footer">
                <div style="font-weight:bold; color:#374151; margin-bottom:6px;"><?php echo htmlspecialchars($company['name']); ?></div>
                <div>For any queries, contact us at <?php echo htmlspecialchars($company['email']); ?></div>
                <div style="margin-top:8px; font-style:italic;">This is a computer-generated invoice.</div>
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
