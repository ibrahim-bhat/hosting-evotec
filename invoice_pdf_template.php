<?php
/**
 * Invoice PDF Template
 * This file contains the HTML template for generating PDF invoices
 */

// Prevent direct access
if (!defined('INVOICE_TEMPLATE')) {
    die('Direct access not allowed');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo htmlspecialchars($invoice['order_number']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }
        
        .invoice-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-info {
            float: left;
            width: 50%;
        }
        
        .invoice-info {
            float: right;
            width: 50%;
            text-align: right;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
            margin: 0;
        }
        
        .invoice-number {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .invoice-date {
            color: #666;
            margin: 5px 0;
        }
        
        .billing-info {
            margin: 30px 0;
        }
        
        .billing-section {
            float: left;
            width: 48%;
            margin-right: 4%;
        }
        
        .billing-section h3 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 16px;
        }
        
        .billing-section p {
            margin: 5px 0;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        
        .items-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .items-table .text-center {
            text-align: center;
        }
        
        .total-section {
            float: right;
            width: 300px;
            margin-top: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .total-row.final {
            font-weight: bold;
            font-size: 18px;
            border-top: 2px solid #007bff;
            border-bottom: 2px solid #007bff;
            margin-top: 10px;
            padding: 15px 0;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Invoice Header -->
    <div class="invoice-header clearfix">
        <div class="company-info">
            <h1 class="invoice-title"><?php echo htmlspecialchars($company['name'] ?? 'Your Company'); ?></h1>
            <p><?php echo htmlspecialchars($company['address'] ?? ''); ?></p>
            <p>Phone: <?php echo htmlspecialchars($company['phone'] ?? ''); ?></p>
            <p>Email: <?php echo htmlspecialchars($company['email'] ?? ''); ?></p>
        </div>
        <div class="invoice-info">
            <div class="invoice-number">Invoice #<?php echo htmlspecialchars($invoice['order_number']); ?></div>
            <div class="invoice-date">Date: <?php echo date('M d, Y', strtotime($invoice['created_at'])); ?></div>
            <div class="invoice-date">Due Date: <?php echo date('M d, Y', strtotime($invoice['created_at'] . ' +30 days')); ?></div>
            <div style="margin-top: 10px;">
                <span class="status-badge status-<?php echo $invoice['payment_status']; ?>">
                    <?php echo ucfirst($invoice['payment_status']); ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Billing Information -->
    <div class="billing-info clearfix">
        <div class="billing-section">
            <h3>Bill To:</h3>
            <p><strong><?php echo htmlspecialchars($invoice['user_name']); ?></strong></p>
            <p><?php echo htmlspecialchars($invoice['user_email']); ?></p>
            <?php if (!empty($invoice['user_phone'])): ?>
                <p>Phone: <?php echo htmlspecialchars($invoice['user_phone']); ?></p>
            <?php endif; ?>
        </div>
        <div class="billing-section">
            <h3>Service Details:</h3>
            <p><strong>Package:</strong> <?php echo htmlspecialchars($invoice['package_name']); ?></p>
            <p><strong>Billing Cycle:</strong> <?php echo ucfirst($invoice['billing_cycle']); ?></p>
            <?php if (!empty($invoice['domain_name'])): ?>
                <p><strong>Domain:</strong> <?php echo htmlspecialchars($invoice['domain_name']); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-center">Billing Cycle</th>
                <th class="text-right">Base Price</th>
                <th class="text-right">Setup Fee</th>
                <th class="text-right">GST (<?php echo $invoice['gst_percentage'] ?? 18; ?>%)</th>
                <th class="text-right">Processing Fee</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo htmlspecialchars($invoice['package_name']); ?> Hosting</td>
                <td class="text-center"><?php echo ucfirst($invoice['billing_cycle']); ?></td>
                <td class="text-right">₹<?php echo number_format($invoice['base_price'], 2); ?></td>
                <td class="text-right">₹<?php echo number_format($invoice['setup_fee'], 2); ?></td>
                <td class="text-right">₹<?php echo number_format($invoice['gst_amount'], 2); ?></td>
                <td class="text-right">₹<?php echo number_format($invoice['processing_fee'], 2); ?></td>
                <td class="text-right"><strong>₹<?php echo number_format($invoice['total_amount'], 2); ?></strong></td>
            </tr>
        </tbody>
    </table>
    
    <!-- Total Section -->
    <div class="total-section">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>₹<?php echo number_format($invoice['subtotal'], 2); ?></span>
        </div>
        <div class="total-row">
            <span>GST (<?php echo $invoice['gst_percentage'] ?? 18; ?>%):</span>
            <span>₹<?php echo number_format($invoice['gst_amount'], 2); ?></span>
        </div>
        <div class="total-row">
            <span>Processing Fee:</span>
            <span>₹<?php echo number_format($invoice['processing_fee'], 2); ?></span>
        </div>
        <div class="total-row final">
            <span>Total Amount:</span>
            <span>₹<?php echo number_format($invoice['total_amount'], 2); ?></span>
        </div>
    </div>
    
    <!-- Payment Information -->
    <?php if ($invoice['payment_status'] === 'paid'): ?>
        <div style="margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;">
            <h3 style="color: #28a745; margin-top: 0;">Payment Information</h3>
            <p><strong>Payment Method:</strong> <?php echo ucfirst($invoice['payment_method'] ?? 'Razorpay'); ?></p>
            <?php if (!empty($invoice['payment_date'])): ?>
                <p><strong>Payment Date:</strong> <?php echo date('M d, Y H:i:s', strtotime($invoice['payment_date'])); ?></p>
            <?php endif; ?>
            <?php if (!empty($invoice['razorpay_payment_id'])): ?>
                <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($invoice['razorpay_payment_id']); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Service Period -->
    <?php if (!empty($invoice['start_date']) && !empty($invoice['expiry_date'])): ?>
        <div style="margin-top: 30px; padding: 20px; background-color: #e3f2fd; border-radius: 5px;">
            <h3 style="color: #1976d2; margin-top: 0;">Service Period</h3>
            <p><strong>Start Date:</strong> <?php echo date('M d, Y', strtotime($invoice['start_date'])); ?></p>
            <p><strong>Expiry Date:</strong> <?php echo date('M d, Y', strtotime($invoice['expiry_date'])); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Footer -->
    <div class="footer">
        <p>Thank you for your business!</p>
        <p>For any queries, please contact us at <?php echo htmlspecialchars($company['email'] ?? 'support@yourcompany.com'); ?></p>
        <p><em>This is a computer-generated invoice and does not require a signature.</em></p>
    </div>
</body>
</html>
