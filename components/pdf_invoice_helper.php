<?php
/**
 * PDF Invoice Helper Functions
 * Generates PDF invoices for orders and manual payments
 */

require_once __DIR__ . '/settings_helper.php';
require_once __DIR__ . '/payment_settings_helper.php';

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
 * Generate PDF invoice using HTML to PDF conversion
 */
function generatePDFInvoice($invoiceData, $download = true) {
    // Generate HTML content
    $html = generateInvoiceHTML($invoiceData);
    
    if ($download) {
        // Set headers for HTML download (printable as PDF)
        $filename = 'Invoice-' . $invoiceData['invoice_number'] . '.html';
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        // Generate HTML that can be printed as PDF
        // Note: For true PDF generation, use libraries like TCPDF, mPDF, or wkhtmltopdf
        echo $html;
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
    
    $currency = $company['currency_symbol'] ?? '₹';
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Invoice <?php echo htmlspecialchars($invoiceNumber); ?></title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Arial', sans-serif;
                line-height: 1.6;
                color: #333;
                background: #fff;
                font-size: 14px;
            }
            
            .invoice-container {
                max-width: 800px;
                margin: 0 auto;
                padding: 40px;
                background: white;
            }
            
            .invoice-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 40px;
                padding-bottom: 20px;
                border-bottom: 3px solid #007bff;
            }
            
            .company-info {
                flex: 1;
            }
            
            .company-logo {
                max-width: 150px;
                margin-bottom: 15px;
            }
            
            .company-name {
                font-size: 24px;
                font-weight: bold;
                color: #007bff;
                margin-bottom: 10px;
            }
            
            .company-details {
                color: #666;
                line-height: 1.4;
            }
            
            .invoice-info {
                text-align: right;
                flex: 1;
            }
            
            .invoice-title {
                font-size: 32px;
                font-weight: bold;
                color: #007bff;
                margin-bottom: 20px;
            }
            
            .invoice-details {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
            }
            
            .invoice-detail-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 8px;
            }
            
            .invoice-detail-label {
                font-weight: 600;
                color: #666;
            }
            
            .invoice-detail-value {
                font-weight: bold;
                color: #333;
            }
            
            .billing-section {
                display: flex;
                justify-content: space-between;
                margin-bottom: 40px;
            }
            
            .bill-to {
                flex: 1;
                margin-right: 40px;
            }
            
            .service-info {
                flex: 1;
            }
            
            .section-title {
                font-size: 16px;
                font-weight: bold;
                color: #007bff;
                margin-bottom: 15px;
                padding-bottom: 5px;
                border-bottom: 2px solid #007bff;
            }
            
            .customer-name {
                font-size: 18px;
                font-weight: bold;
                margin-bottom: 8px;
            }
            
            .customer-details {
                color: #666;
                line-height: 1.4;
            }
            
            .items-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 30px;
                background: white;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                border-radius: 8px;
                overflow: hidden;
            }
            
            .items-table th {
                background: #007bff;
                color: white;
                padding: 15px 12px;
                text-align: left;
                font-weight: 600;
                font-size: 14px;
            }
            
            .items-table td {
                padding: 15px 12px;
                border-bottom: 1px solid #eee;
                vertical-align: top;
            }
            
            .items-table tr:last-child td {
                border-bottom: none;
            }
            
            .items-table tr:nth-child(even) {
                background: #f8f9fa;
            }
            
            .text-right {
                text-align: right;
            }
            
            .text-center {
                text-align: center;
            }
            
            .item-description {
                font-weight: 600;
                margin-bottom: 5px;
            }
            
            .item-details {
                font-size: 12px;
                color: #666;
            }
            
            .totals-section {
                display: flex;
                justify-content: flex-end;
                margin-bottom: 40px;
            }
            
            .totals-table {
                width: 300px;
                border-collapse: collapse;
            }
            
            .totals-table td {
                padding: 8px 15px;
                border-bottom: 1px solid #eee;
            }
            
            .totals-table .total-label {
                text-align: right;
                color: #666;
                font-weight: 500;
            }
            
            .totals-table .total-value {
                text-align: right;
                font-weight: bold;
                color: #333;
            }
            
            .totals-table .final-total {
                background: #007bff;
                color: white;
                font-size: 16px;
                font-weight: bold;
            }
            
            .totals-table .final-total .total-label,
            .totals-table .final-total .total-value {
                color: white;
            }
            
            .payment-info {
                background: #e8f5e8;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 30px;
                border-left: 4px solid #28a745;
            }
            
            .payment-info h3 {
                color: #28a745;
                margin-bottom: 10px;
                font-size: 16px;
            }
            
            .service-period {
                background: #e3f2fd;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 30px;
                border-left: 4px solid #2196f3;
            }
            
            .service-period h3 {
                color: #2196f3;
                margin-bottom: 10px;
                font-size: 16px;
            }
            
            .invoice-footer {
                text-align: center;
                padding-top: 30px;
                border-top: 2px solid #eee;
                color: #666;
                font-size: 12px;
            }
            
            .footer-company {
                font-weight: bold;
                color: #333;
                margin-bottom: 10px;
            }
            
            .status-badge {
                display: inline-block;
                padding: 6px 15px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: bold;
                text-transform: uppercase;
                margin-top: 10px;
            }
            
            .status-paid {
                background: #d4edda;
                color: #155724;
            }
            
            .status-pending {
                background: #fff3cd;
                color: #856404;
            }
            
            .status-failed {
                background: #f8d7da;
                color: #721c24;
            }
            
            @media print {
                body {
                    font-size: 12px;
                }
                
                .invoice-container {
                    padding: 20px;
                }
                
                .invoice-header {
                    margin-bottom: 30px;
                }
                
                .billing-section {
                    margin-bottom: 30px;
                }
            }
        </style>
    </head>
    <body>
        <div class="invoice-container">
            <!-- Header -->
            <div class="invoice-header">
                <div class="company-info">
                    <?php if (!empty($company['logo'])): ?>
                        <img src="<?php echo htmlspecialchars($company['logo']); ?>" alt="Company Logo" class="company-logo">
                    <?php endif; ?>
                    <div class="company-name"><?php echo htmlspecialchars($company['name']); ?></div>
                    <div class="company-details">
                        <?php if (!empty($company['address'])): ?>
                            <div><?php echo nl2br(htmlspecialchars($company['address'])); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($company['phone'])): ?>
                            <div>Phone: <?php echo htmlspecialchars($company['phone']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($company['email'])): ?>
                            <div>Email: <?php echo htmlspecialchars($company['email']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($company['gst_number'])): ?>
                            <div>GST: <?php echo htmlspecialchars($company['gst_number']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="invoice-info">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-details">
                        <div class="invoice-detail-row">
                            <span class="invoice-detail-label">Invoice #:</span>
                            <span class="invoice-detail-value"><?php echo htmlspecialchars($invoiceNumber); ?></span>
                        </div>
                        <div class="invoice-detail-row">
                            <span class="invoice-detail-label">Issue Date:</span>
                            <span class="invoice-detail-value"><?php echo $issueDate; ?></span>
                        </div>
                        <div class="invoice-detail-row">
                            <span class="invoice-detail-label">Due Date:</span>
                            <span class="invoice-detail-value"><?php echo $dueDate; ?></span>
                        </div>
                        <div class="invoice-detail-row">
                            <span class="invoice-detail-label">Total Amount:</span>
                            <span class="invoice-detail-value"><?php echo $currency . number_format($totalAmount, 2); ?></span>
                        </div>
                        <?php if ($invoiceData['type'] === 'order'): ?>
                            <div class="status-badge status-<?php echo $invoiceData['order']['payment_status']; ?>">
                                <?php echo strtoupper($invoiceData['order']['payment_status']); ?>
                            </div>
                        <?php else: ?>
                            <div class="status-badge status-<?php echo $invoiceData['payment']['payment_status']; ?>">
                                <?php echo strtoupper($invoiceData['payment']['payment_status']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Billing Information -->
            <div class="billing-section">
                <div class="bill-to">
                    <div class="section-title">Bill To</div>
                    <div class="customer-name"><?php echo htmlspecialchars($user['name']); ?></div>
                    <div class="customer-details">
                        <div><?php echo htmlspecialchars($user['email']); ?></div>
                        <?php if (!empty($user['phone'])): ?>
                            <div>Phone: <?php echo htmlspecialchars($user['phone']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="service-info">
                    <div class="section-title">Service Details</div>
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
            </div>
            
            <!-- Items Table -->
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
                                <div class="item-description"><?php echo htmlspecialchars($item['description']); ?></div>
                                <?php if ($invoiceData['type'] === 'order'): ?>
                                    <div class="item-details">
                                        Service Period: <?php echo $invoiceData['service_period']['start']; ?> to <?php echo $invoiceData['service_period']['end']; ?>
                                    </div>
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
            
            <!-- Totals -->
            <div class="totals-section">
                <table class="totals-table">
                    <tr>
                        <td class="total-label">Subtotal:</td>
                        <td class="total-value"><?php echo $currency . number_format($subtotal, 2); ?></td>
                    </tr>
                    <?php if ($totalGst > 0): ?>
                        <tr>
                            <td class="total-label">GST (<?php echo $items[0]['gst_percentage'] ?? '18'; ?>%):</td>
                            <td class="total-value"><?php echo $currency . number_format($totalGst, 2); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($totalProcessingFee > 0): ?>
                        <tr>
                            <td class="total-label">Processing Fee:</td>
                            <td class="total-value"><?php echo $currency . number_format($totalProcessingFee, 2); ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr class="final-total">
                        <td class="total-label">Total Amount:</td>
                        <td class="total-value"><?php echo $currency . number_format($totalAmount, 2); ?></td>
                    </tr>
                </table>
            </div>
            
            <!-- Payment Information -->
            <?php if ($invoiceData['type'] === 'order' && $invoiceData['order']['payment_status'] === 'paid'): ?>
                <div class="payment-info">
                    <h3>Payment Information</h3>
                    <div><strong>Payment Method:</strong> <?php echo ucfirst($invoiceData['order']['payment_method'] ?? 'Razorpay'); ?></div>
                    <?php if (!empty($invoiceData['order']['payment_date'])): ?>
                        <div><strong>Payment Date:</strong> <?php echo date('d M, Y H:i:s', strtotime($invoiceData['order']['payment_date'])); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($invoiceData['order']['razorpay_payment_id'])): ?>
                        <div><strong>Transaction ID:</strong> <?php echo htmlspecialchars($invoiceData['order']['razorpay_payment_id']); ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Service Period -->
            <?php if ($invoiceData['service_period']['start'] !== 'N/A' || $invoiceData['service_period']['end'] !== 'N/A'): ?>
                <div class="service-period">
                    <h3>Service Period</h3>
                    <div><strong>Start Date:</strong> <?php echo $invoiceData['service_period']['start']; ?></div>
                    <div><strong>End Date:</strong> <?php echo $invoiceData['service_period']['end']; ?></div>
                </div>
            <?php endif; ?>
            
            <!-- Footer -->
            <div class="invoice-footer">
                <div class="footer-company"><?php echo htmlspecialchars($company['name']); ?></div>
                <div>Thank you for your business!</div>
                <div style="margin-top: 15px;">
                    For any queries, please contact us at <?php echo htmlspecialchars($company['email']); ?>
                </div>
                <div style="margin-top: 10px; font-style: italic;">
                    This is a computer-generated invoice and does not require a signature.
                </div>
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
        'email' => getSetting($conn, 'company_email', 'info@example.com'),
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
