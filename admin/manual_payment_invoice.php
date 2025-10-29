<?php
require_once '../config.php';
require_once '../components/auth_helper.php';
require_once '../components/admin_helper.php';
require_once '../components/payment_settings_helper.php';
require_once '../components/pdf_invoice_helper.php';

// Require admin or user access
requireLogin();

$paymentId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$download = isset($_GET['download']) ? true : false;

// Get manual payment details
$payment = getManualPaymentById($conn, $paymentId);
if (!$payment) {
    die('Manual payment not found');
}

// Check if user can access this payment (admin or payment owner)
if (!isAdmin() && $payment['user_id'] != $_SESSION['user_id']) {
    die('Access denied');
}

if ($download) {
    // Generate PDF invoice
    $result = generateManualPaymentPDFInvoice($conn, $paymentId, true);
    if (!$result['success']) {
        die($result['message']);
    }
    exit;
}

// Get user details
$user = getUserById($conn, $payment['user_id']);

// Get company settings
$companyName = getCompanyName($conn);
$companyEmail = getSetting($conn, 'company_email', 'info@example.com');
$companyPhone = getSetting($conn, 'company_phone', '+91 123 456 7890');
$companyAddress = getSetting($conn, 'company_address', '');
$gstNumber = getSetting($conn, 'company_gst', '');
$companyLogo = getCompanyLogo($conn);

// Calculate dates
$issueDate = date('d M, Y', strtotime($payment['order_date']));
$serviceStartDate = $payment['start_date'] ? date('d M, Y', strtotime($payment['start_date'])) : 'N/A';
$serviceEndDate = $payment['end_date'] ? date('d M, Y', strtotime($payment['end_date'])) : 'N/A';

// Display HTML invoice
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice MP-<?php echo str_pad($payment['id'], 6, '0', STR_PAD_LEFT); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 30px; background: #f5f5f5; }
        .invoice-container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        
        .invoice-header { display: flex; justify-content: space-between; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 3px solid #333; }
        .invoice-header-left { flex: 1; }
        .invoice-header-right { flex: 1; text-align: right; }
        .logo { max-width: 150px; margin-bottom: 20px; }
        .invoice-number { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        .invoice-label { color: #666; font-size: 14px; }
        .invoice-value { font-size: 16px; font-weight: bold; margin-bottom: 5px; }
        
        .billed-to { margin-bottom: 30px; }
        .section-title { font-weight: bold; font-size: 14px; margin-bottom: 10px; color: #333; }
        
        .invoice-table { width: 100%; border-collapse: collapse; margin: 30px 0; }
        .invoice-table th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: 600; border-bottom: 2px solid #ddd; }
        .invoice-table td { padding: 12px; border-bottom: 1px solid #eee; }
        .invoice-table tr:last-child td { border-bottom: none; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { font-weight: bold; font-size: 16px; background: #f8f9fa; }
        
        .payment-status { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; margin-top: 20px; }
        .status-paid { background: #10b981; color: white; }
        .status-pending { background: #f59e0b; color: white; }
        .status-failed { background: #ef4444; color: white; }
        
        .invoice-footer { margin-top: 40px; padding-top: 20px; border-top: 2px solid #eee; text-align: center; color: #666; font-size: 12px; }
        
        @media print {
            body { padding: 0; background: white; }
            .invoice-container { box-shadow: none; border-radius: 0; }
            .no-print { display: none; }
        }
        
        .action-buttons { margin-top: 30px; text-align: center; }
        .btn { display: inline-block; padding: 12px 30px; margin: 5px; text-decoration: none; border-radius: 5px; font-weight: 600; transition: all 0.3s; }
        .btn-download { background: #ef4444; color: white; }
        .btn-print { background: #3b82f6; color: white; }
        .btn:hover { opacity: 0.9; transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="invoice-header-left">
                <?php if ($companyLogo): ?>
                    <img src="../<?php echo htmlspecialchars($companyLogo); ?>" alt="Logo" class="logo">
                <?php else: ?>
                    <h1 style="margin-bottom: 20px;"><?php echo htmlspecialchars($companyName); ?></h1>
                <?php endif; ?>
                <div class="billed-to">
                    <div class="section-title">BILLED TO</div>
                    <div style="font-weight: bold; margin-bottom: 5px;"><?php echo htmlspecialchars($user['name']); ?></div>
                    <div style="color: #666;"><?php echo htmlspecialchars($user['email']); ?></div>
                    <?php if ($user['phone']): ?>
                        <div style="color: #666;"><?php echo htmlspecialchars($user['phone']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="invoice-header-right">
                <div class="invoice-number">INVOICE</div>
                <div style="margin-top: 20px;">
                    <div class="invoice-label">Invoice #</div>
                    <div class="invoice-value">MP-<?php echo str_pad($payment['id'], 6, '0', STR_PAD_LEFT); ?></div>
                </div>
                <div style="margin-top: 10px;">
                    <div class="invoice-label">Issue Date</div>
                    <div class="invoice-value"><?php echo $issueDate; ?></div>
                </div>
                <div style="margin-top: 10px;">
                    <div class="invoice-label">Payment Type</div>
                    <div class="invoice-value">Manual Payment</div>
                </div>
                <div style="margin-top: 20px;">
                    <div class="invoice-value">₹<?php echo number_format($payment['payment_amount'], 2); ?></div>
                </div>
                <div class="payment-status status-<?php echo $payment['payment_status']; ?>">
                    <?php echo strtoupper($payment['payment_status']); ?>
                </div>
            </div>
        </div>
        
        <!-- Services Table -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>DESCRIPTION</th>
                    <th class="text-center">QTY</th>
                    <th class="text-right">UNIT PRICE</th>
                    <th class="text-right">AMOUNT (INR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($payment['payment_reason']); ?></strong>
                        <?php if ($payment['description']): ?>
                            <br><small style="color: #666;"><?php echo htmlspecialchars($payment['description']); ?></small>
                        <?php endif; ?>
                        <?php if ($serviceStartDate !== 'N/A' || $serviceEndDate !== 'N/A'): ?>
                            <br><small style="color: #666;">Service Period: <?php echo $serviceStartDate; ?> to <?php echo $serviceEndDate; ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">1</td>
                    <td class="text-right">₹<?php echo number_format($payment['payment_amount'], 2); ?></td>
                    <td class="text-right"><strong>₹<?php echo number_format($payment['payment_amount'], 2); ?></strong></td>
                </tr>
            </tbody>
        </table>
        
        <!-- Summary -->
        <div style="margin-top: 30px; text-align: right;">
            <div style="padding-top: 15px; border-top: 2px solid #333; margin-top: 15px; font-size: 20px;">
                <strong>Total: ₹<?php echo number_format($payment['payment_amount'], 2); ?></strong>
            </div>
            <?php if ($payment['payment_status'] === 'paid'): ?>
                <div style="color: #10b981; margin-top: 10px; font-weight: bold;">
                    PAID: (₹<?php echo number_format($payment['payment_amount'], 2); ?>)
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Payment Information -->
        <?php if ($payment['payment_status'] === 'paid'): ?>
            <div style="margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;">
                <h3 style="color: #28a745; margin-top: 0;">Payment Information</h3>
                <p><strong>Payment Method:</strong> <?php echo ucfirst($payment['payment_method']); ?></p>
                <p><strong>Payment Date:</strong> <?php echo date('M d, Y', strtotime($payment['order_date'])); ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Service Period -->
        <?php if ($serviceStartDate !== 'N/A' || $serviceEndDate !== 'N/A'): ?>
            <div style="margin-top: 30px; padding: 20px; background-color: #e3f2fd; border-radius: 5px;">
                <h3 style="color: #1976d2; margin-top: 0;">Service Period</h3>
                <p><strong>Start Date:</strong> <?php echo $serviceStartDate; ?></p>
                <p><strong>End Date:</strong> <?php echo $serviceEndDate; ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Footer -->
        <div class="invoice-footer">
            <p><strong><?php echo htmlspecialchars($companyName); ?></strong></p>
            <p><?php echo htmlspecialchars($companyAddress); ?></p>
            <p>Email: <?php echo htmlspecialchars($companyEmail); ?> | Phone: <?php echo htmlspecialchars($companyPhone); ?></p>
            <?php if ($gstNumber): ?>
                <p>GST Reg #: <?php echo htmlspecialchars($gstNumber); ?></p>
            <?php endif; ?>
            <p style="margin-top: 20px;">Thank you for your business!</p>
        </div>
        
        <!-- Action Buttons -->
        <div class="action-buttons no-print">
            <a href="?id=<?php echo $paymentId; ?>&download=1" class="btn btn-download">
                <i class="bi bi-download"></i> Download PDF
            </a>
            <button onclick="window.print()" class="btn btn-print">
                <i class="bi bi-printer"></i> Print Invoice
            </button>
        </div>
    </div>
</body>
</html>
