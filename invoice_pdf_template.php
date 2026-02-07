<?php
/**
 * Professional Invoice PDF Template
 * Modern design matching CloudHosting invoice style
 */

// Prevent direct access
if (!defined('INVOICE_TEMPLATE')) {
    die('Direct access not allowed');
}

// Fetch company settings from database
require_once 'config.php';
$settingsQuery = "SELECT * FROM site_settings LIMIT 1";
$settingsResult = mysqli_query($conn, $settingsQuery);
$settings = mysqli_fetch_assoc($settingsResult);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo htmlspecialchars($invoice['order_number']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1f2937;
            line-height: 1.6;
            padding: 40px;
            background: #ffffff;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
        }
        
        /* Header Section */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .company-logo {
            max-width: 180px;
            height: auto;
        }
        
        .company-info h1 {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }
        
        .company-info p {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
        }
        
        .invoice-title-section {
            text-align: right;
        }
        
        .invoice-title {
            font-size: 32px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }
        
        .invoice-number {
            font-size: 14px;
            color: #6b7280;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 12px;
        }
        
        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .info-block h3 {
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }
        
        .info-block p {
            font-size: 14px;
            color: #111827;
            margin-bottom: 4px;
        }
        
        .info-block .highlight {
            font-weight: 600;
            color: #111827;
        }
        
        /* Items Table */
        .items-section {
            margin: 40px 0;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table thead {
            background-color: #f9fafb;
        }
        
        .items-table th {
            text-align: left;
            padding: 12px 16px;
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .items-table td {
            padding: 16px;
            font-size: 14px;
            color: #111827;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .items-table th.text-right,
        .items-table td.text-right {
            text-align: right;
        }
        
        .items-table th.text-center,
        .items-table td.text-center {
            text-align: center;
        }
        
        .service-description {
            font-weight: 500;
            color: #111827;
        }
        
        .service-meta {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            background-color: #eff6ff;
            color: #1e40af;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        /* Totals Section */
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
        }
        
        .totals-box {
            width: 320px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 14px;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .total-row .label {
            color: #6b7280;
        }
        
        .total-row .value {
            font-weight: 500;
            color: #111827;
        }
        
        .total-row.subtotal {
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        
        .total-row.final {
            margin-top: 12px;
            padding: 16px 0;
            border-top: 2px solid #111827;
            border-bottom: 2px solid #111827;
            font-size: 18px;
            font-weight: 700;
        }
        
        .total-row.final .value {
            color: #2563eb;
            font-size: 20px;
        }
        
        /* Footer */
        .invoice-footer {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
        
        .footer-icons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .footer-icon {
            font-size: 12px;
            color: #6b7280;
        }
        
        .footer-text {
            font-size: 12px;
            color: #9ca3af;
        }
        
        @media print {
            body {
                padding: 20px;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <div style="display: flex; align-items: center; margin-bottom: 12px;">
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96z" fill="white"/>
                        </svg>
                    </div>
                    <h1><?php echo htmlspecialchars($settings['site_name'] ?? 'EVOTEC HOSTING'); ?></h1>
                </div>
                <p style="margin-top: 12px;">
                    <?php echo htmlspecialchars($settings['address'] ?? '123 Business St, Suite 100'); ?><br>
                    <?php echo htmlspecialchars($settings['city'] ?? 'San Francisco'); ?>, <?php echo htmlspecialchars($settings['state'] ?? 'CA'); ?> <?php echo htmlspecialchars($settings['zip'] ?? '94107'); ?>
                </p>
            </div>
            <div class="invoice-title-section">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">#<?php echo htmlspecialchars($invoice['order_number']); ?></div>
                <span class="status-badge status-<?php echo $invoice['payment_status']; ?>">
                    <?php echo ucfirst($invoice['payment_status']); ?>
                </span>
            </div>
        </div>
        
        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-block">
                <h3>Date</h3>
                <p class="highlight"><?php echo date('M d, Y', strtotime($invoice['created_at'])); ?></p>
            </div>
            <div class="info-block">
                <h3>Due Date</h3>
                <p class="highlight"><?php echo date('M d, Y', strtotime($invoice['created_at'] . ' +30 days')); ?></p>
            </div>
            <div class="info-block">
                <h3>Invoice Number</h3>
                <p class="highlight"><?php echo htmlspecialchars($invoice['order_number']); ?></p>
            </div>
        </div>
        
        <!-- Bill To & Service -->
        <div class="info-grid">
            <div class="info-block">
                <h3>Billed To</h3>
                <p class="highlight"><?php echo htmlspecialchars($invoice['user_name']); ?></p>
                <p style="font-size: 13px; color: #6b7280;"><?php echo htmlspecialchars($invoice['user_email']); ?></p>
                <?php if (!empty($invoice['user_phone'])): ?>
                    <p style="font-size: 13px; color: #6b7280;"><?php echo htmlspecialchars($invoice['user_phone']); ?></p>
                <?php endif; ?>
            </div>
            <div class="info-block" style="grid-column: span 2;">
                <h3>Service Details</h3>
                <p class="highlight"><?php echo htmlspecialchars($invoice['package_name']); ?></p>
                <p style="font-size: 13px; color: #6b7280;">
                    Billing Period: <?php echo date('M d, Y', strtotime($invoice['start_date'])); ?> - <?php echo date('M d, Y', strtotime($invoice['expiry_date'])); ?>
                </p>
            </div>
        </div>
        
        <!-- Items Table -->
        <div class="items-section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Service Description</th>
                        <th class="text-center">Cycle</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="service-description"><?php echo htmlspecialchars($invoice['package_name']); ?></div>
                            <div class="service-meta">Premium Shared Hosting with SSD</div>
                        </td>
                        <td class="text-center">
                            <span class="badge"><?php echo ucfirst($invoice['billing_cycle']); ?></span>
                        </td>
                        <td class="text-right">₹<?php echo number_format($invoice['base_price'], 2); ?></td>
                    </tr>
                    <?php if ($invoice['setup_fee'] > 0): ?>
                    <tr>
                        <td>
                            <div class="service-description">Setup Fee</div>
                            <div class="service-meta">One-time setup charge</div>
                        </td>
                        <td class="text-center">
                            <span class="badge">Once</span>
                        </td>
                        <td class="text-right">₹<?php echo number_format($invoice['setup_fee'], 2); ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- Totals -->
            <div class="totals-section">
                <div class="totals-box">
                    <div class="total-row subtotal">
                        <span class="label">Subtotal</span>
                        <span class="value">₹<?php echo number_format($invoice['subtotal'], 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span class="label">Tax (18%)</span>
                        <span class="value">₹<?php echo number_format($invoice['gst_amount'], 2); ?></span>
                    </div>
                    <?php if ($invoice['processing_fee'] > 0): ?>
                    <div class="total-row">
                        <span class="label">Processing Fee</span>
                        <span class="value">₹<?php echo number_format($invoice['processing_fee'], 2); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="total-row final">
                        <span class="label">TOTAL</span>
                        <span class="value">₹<?php echo number_format($invoice['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="invoice-footer">
            <div class="footer-icons">
                <span class="footer-icon">💳 Paid Online</span>
                <span class="footer-icon">🔒 SSL Support</span>
            </div>
            <p class="footer-text">
                Thank you for your business! For any queries, contact us at <?php echo htmlspecialchars($settings['support_email'] ?? 'support@evotec.in'); ?>
            </p>
        </div>
    </div>
</body>
</html>
