<?php
session_start();
require_once 'config.php';
require_once 'components/auth_helper.php';
require_once 'components/hosting_helper.php';
require_once 'components/payment_settings_helper.php';
require_once 'components/settings_helper.php';
require_once 'components/coupon_helper.php';

// Get package from URL
$packageSlug = isset($_GET['package']) ? sanitizeInput($_GET['package']) : '';
$billingCycle = isset($_GET['cycle']) ? sanitizeInput($_GET['cycle']) : 'yearly';
$renewOrderId = isset($_GET['renew']) ? (int)$_GET['renew'] : null;

// Get renewal order details if renewing
$renewOrder = null;
if ($renewOrderId) {
    $renewOrder = getOrderById($conn, $renewOrderId);
    if (!$renewOrder) {
        setFlashMessage('error', 'Renewal order not found');
        redirect('hosting.php');
    }
}

if (empty($packageSlug)) {
    setFlashMessage('error', 'Invalid package selected');
    redirect('index.php#pricing');
}

// Get package details
if ($renewOrder) {
    $stmt = $conn->prepare("SELECT * FROM hosting_packages WHERE slug = ?");
    $stmt->bind_param("s", $packageSlug);
    $stmt->execute();
    $result = $stmt->get_result();
    $package = $result->fetch_assoc();
    $stmt->close();
    
    if (!$package) {
        setFlashMessage('error', 'Package not found');
        redirect('index.php');
    }
} else {
    $package = getPackageBySlug($conn, $packageSlug);
    if (!$package || $package['status'] !== 'active') {
        setFlashMessage('error', 'Package not found or not available');
        redirect('index.php');
    }
}

// Check if user is logged in
$isLoggedIn = isLoggedIn();

// Get pricing for different cycles
$isRenewal = !empty($renewOrderId);
$availableCycles = [];
if (!empty($package['price_yearly']) && $package['price_yearly'] > 0) {
    $renewalYearly = getPackageRenewalPrice($package, 'yearly', $conn);
    $availableCycles['yearly'] = [
        'price' => $package['price_yearly'] / 12,
        'label' => 'Yearly',
        'total' => $isRenewal ? $renewalYearly : $package['price_yearly'],
        'renewal' => $renewalYearly
    ];
}
if (!empty($package['price_2years']) && $package['price_2years'] > 0) {
    $renewal2y = getPackageRenewalPrice($package, '2years', $conn);
    $availableCycles['2years'] = [
        'price' => $package['price_2years'] / 24,
        'label' => '2 Years',
        'total' => $isRenewal ? $renewal2y : $package['price_2years'],
        'renewal' => $renewal2y
    ];
}
if (!empty($package['price_4years']) && $package['price_4years'] > 0) {
    $renewal4y = getPackageRenewalPrice($package, '4years', $conn);
    $availableCycles['4years'] = [
        'price' => $package['price_4years'] / 48,
        'label' => '4 Years',
        'total' => $isRenewal ? $renewal4y : $package['price_4years'],
        'renewal' => $renewal4y
    ];
}

// If no cycles available, redirect back
if (empty($availableCycles)) {
    setFlashMessage('error', 'No pricing available for this package');
    redirect('index.php');
}

// Validate billing cycle
if (!isset($availableCycles[$billingCycle])) {
    $billingCycle = array_key_first($availableCycles);
}

// Calculate pricing using global settings
$cyclePrice = $availableCycles[$billingCycle]['total'];
$calculations = calculateOrderTotal($conn, $cyclePrice, $isRenewal);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($package['name']); ?> - InfraLabs Cloud</title>
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>☁️</text></svg>">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-blue: #5B5FED;
            --primary-blue-hover: #4A4ED6;
            --dark-bg: #0F1117;
            --card-bg: #FFFFFF;
            --border-color: #E5E7EB;
            --text-primary: #1F2937;
            --text-secondary: #6B7280;
            --success-green: #10B981;
            --light-bg: #F9FAFB;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light-bg);
            color: var(--text-primary);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .package-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            margin-bottom: 32px;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: var(--text-primary);
        }

        .package-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 32px;
        }

        .package-details {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 40px;
        }

        .package-header h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .package-description {
            color: var(--text-secondary);
            font-size: 15px;
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--text-primary);
        }

        .cycle-options {
            display: grid;
            gap: 12px;
        }

        .cycle-option {
            background: var(--light-bg);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 16px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: var(--text-primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cycle-option:hover {
            border-color: var(--primary-blue);
            background: #F5F6FF;
        }

        .cycle-option.active {
            background: var(--primary-blue);
            border-color: var(--primary-blue);
            color: white;
        }

        .cycle-left {
            display: flex;
            flex-direction: column;
        }

        .cycle-name {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .cycle-price {
            font-size: 14px;
            opacity: 0.8;
        }

        .cycle-option.active .cycle-price {
            opacity: 1;
        }

        .order-summary {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 32px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .summary-header {
            margin-bottom: 24px;
        }

        .summary-header h2 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .summary-label {
            color: var(--text-secondary);
        }

        .summary-value {
            font-weight: 600;
            color: var(--text-primary);
        }

        .summary-divider {
            height: 1px;
            background: var(--border-color);
            margin: 20px 0;
        }

        .price-display {
            text-align: center;
            margin: 24px 0;
        }

        .price-amount {
            font-size: 48px;
            font-weight: 800;
            color: var(--primary-blue);
            line-height: 1;
        }

        .price-period {
            font-size: 14px;
            color: var(--text-secondary);
            margin-top: 8px;
        }

        .btn-continue {
            width: 100%;
            background: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-continue:hover {
            background: var(--primary-blue-hover);
            transform: translateY(-2px);
            color: white;
        }

        .signup-text {
            text-align: center;
            margin-top: 16px;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .signup-text a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
        }

        .coupon-section {
            background: var(--light-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }

        @media (max-width: 768px) {
            .package-grid {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
            }

            .package-header h1 {
                font-size: 24px;
            }

            .price-amount {
                font-size: 36px;
            }
        }
    </style>
</head>
<body>
    <div class="package-container">
        <a href="index.php#pricing" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Pricing
        </a>

        <div class="package-grid">
            <!-- Package Details -->
            <div class="package-details">
                <div class="package-header">
                    <h1><?php echo htmlspecialchars($package['name']); ?></h1>
                    <p class="package-description"><?php echo htmlspecialchars($package['short_description'] ?? $package['description']); ?></p>
                </div>

                <div class="section-title">Select Billing Cycle</div>
                <div class="cycle-options">
                    <?php foreach ($availableCycles as $cycleKey => $cycleData): ?>
                    <a href="?package=<?php echo urlencode($packageSlug); ?>&cycle=<?php echo $cycleKey; ?><?php echo $renewOrderId ? '&renew=' . $renewOrderId : ''; ?>" 
                       class="cycle-option <?php echo $billingCycle === $cycleKey ? 'active' : ''; ?>">
                        <div class="cycle-left">
                            <div class="cycle-name"><?php echo $cycleData['label']; ?></div>
                            <div class="cycle-price">₹<?php echo number_format($cycleData['price'], 2); ?>/mo</div>
                        </div>
                        <div class="cycle-right">
                            <div style="font-weight: 700; font-size: 18px;">₹<?php echo number_format($cycleData['total'], 2); ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <div class="summary-header">
                    <h2>Order Summary</h2>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Package:</span>
                    <span class="summary-value"><?php echo htmlspecialchars($package['name']); ?></span>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Billing Cycle:</span>
                    <span class="summary-value"><?php echo $availableCycles[$billingCycle]['label']; ?></span>
                </div>

                <div class="summary-divider"></div>

                <!-- Fee Breakdown -->
                <div class="summary-row">
                    <span class="summary-label">Base Price:</span>
                    <span class="summary-value">₹<?php echo number_format($calculations['base_price'], 2); ?></span>
                </div>
                <?php if ($calculations['setup_fee'] > 0): ?>
                <div class="summary-row">
                    <span class="summary-label">Setup Fee (<?php echo $calculations['setup_fee_percentage']; ?>%):</span>
                    <span class="summary-value">₹<?php echo number_format($calculations['setup_fee'], 2); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($calculations['gst_amount'] > 0): ?>
                <div class="summary-row">
                    <span class="summary-label">GST (<?php echo $calculations['gst_percentage']; ?>%):</span>
                    <span class="summary-value">₹<?php echo number_format($calculations['gst_amount'], 2); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($calculations['processing_fee'] > 0): ?>
                <div class="summary-row">
                    <span class="summary-label">Processing Fee (<?php echo $calculations['processing_fee_percentage']; ?>%):</span>
                    <span class="summary-value">₹<?php echo number_format($calculations['processing_fee'], 2); ?></span>
                </div>
                <?php endif; ?>

                <div class="summary-divider"></div>

                <!-- Coupon discount row (hidden until applied) -->
                <div class="summary-row" id="coupon-discount-row" style="display:none;">
                    <span class="summary-label" style="color:#10B981;">Coupon Discount</span>
                    <span class="summary-value" style="color:#10B981;" id="coupon-discount-value">-₹0.00</span>
                </div>

                <div class="summary-divider"></div>

                <div class="price-display">
                    <div class="price-amount" id="total-amount-display">₹<?php echo number_format($calculations['total_amount'], 2); ?></div>
                    <div class="price-period">Total payable amount</div>
                </div>

                <?php if (!$isRenewal && isset($availableCycles[$billingCycle]['renewal'])): ?>
                <?php
                    $renewalTotal = $availableCycles[$billingCycle]['renewal'];
                    $renewalMonths = ['monthly' => 1, 'yearly' => 12, '2years' => 24, '4years' => 48];
                    $months = $renewalMonths[$billingCycle] ?? 12;
                    $renewalPerMonth = $months > 0 ? $renewalTotal / $months : $renewalTotal;
                ?>
                <div style="text-align:center; font-size:13px; color:var(--text-secondary); margin-bottom:12px;">
                    <i class="fas fa-sync-alt"></i> Renews at ₹<?php echo number_format($renewalPerMonth, 2); ?>/month
                </div>
                <?php endif; ?>

                <!-- Coupon Input -->
                <div class="coupon-section" id="coupon-section">
                    <div style="display:flex; gap:8px; align-items:flex-end;">
                        <div style="flex:1;">
                            <label style="font-size:13px; font-weight:600; margin-bottom:6px; display:block; color:var(--text-secondary);">Have a coupon?</label>
                            <input type="text" id="coupon_code" placeholder="Enter code" 
                                   style="width:100%; padding:10px 14px; border:2px solid var(--border-color); border-radius:10px; font-size:14px; font-family:inherit; text-transform:uppercase; background:var(--light-bg); transition:border-color 0.3s;"
                                   onfocus="this.style.borderColor='var(--primary-blue)'" onblur="this.style.borderColor='var(--border-color)'">
                        </div>
                        <button type="button" id="applyCouponBtn" onclick="applyCoupon()"
                                style="padding:10px 20px; background:var(--primary-blue); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; white-space:nowrap; transition:background 0.3s;"
                                onmouseover="this.style.background='var(--primary-blue-hover)'" onmouseout="this.style.background='var(--primary-blue)'">
                            Apply
                        </button>
                    </div>
                    <div id="coupon-message" style="margin-top:8px; font-size:13px;"></div>
                    <div id="coupon-applied" style="display:none; margin-top:8px; display:none; justify-content:space-between; align-items:center;">
                        <span style="color:#10B981; font-weight:600; font-size:13px;" id="coupon-applied-text"></span>
                        <button type="button" onclick="removeCoupon()" 
                                style="padding:4px 12px; font-size:12px; border:1px solid #EF4444; color:#EF4444; background:none; border-radius:6px; cursor:pointer;">Remove</button>
                    </div>
                </div>


                <?php if ($isLoggedIn): ?>
                    <a id="continueBtn" href="checkout.php?package=<?php echo urlencode($packageSlug); ?>&cycle=<?php echo $billingCycle; ?><?php echo $renewOrderId ? '&renew=' . $renewOrderId : ''; ?>" class="btn-continue">
                        <i class="fas fa-shopping-cart"></i> Continue to Checkout
                    </a>
                <?php else: ?>
                    <a href="login.php?redirect=<?php echo urlencode('select-package.php?package=' . $packageSlug . '&cycle=' . $billingCycle); ?>" class="btn-continue">
                        <i class="fas fa-lock"></i> Login to Continue
                    </a>
                    <p class="signup-text">
                        Don't have an account? <a href="register.php">Sign up</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        var originalTotal = <?php echo $calculations['total_amount']; ?>;
        var appliedCouponCode = '';
        var baseCheckoutUrl = document.getElementById('continueBtn') ? document.getElementById('continueBtn').getAttribute('href') : '';

        function applyCoupon() {
            var code = document.getElementById('coupon_code').value.trim();
            if (!code) {
                showCouponMsg('Please enter a coupon code.', '#EF4444');
                return;
            }

            var btn = document.getElementById('applyCouponBtn');
            btn.disabled = true;
            btn.textContent = 'Applying...';

            var formData = new URLSearchParams();
            formData.append('coupon_code', code);
            formData.append('order_id', '0');
            formData.append('order_total', originalTotal);

            fetch('apply-coupon.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.textContent = 'Apply';

                if (data.valid) {
                    var discount = parseFloat(data.discount);
                    var newTotal = Math.max(0, originalTotal - discount);
                    appliedCouponCode = code.toUpperCase();

                    // Update price display
                    document.getElementById('coupon-discount-row').style.display = 'flex';
                    document.getElementById('coupon-discount-value').textContent = '-₹' + discount.toFixed(2);
                    document.getElementById('total-amount-display').textContent = '₹' + newTotal.toFixed(2);

                    // Show applied state
                    document.getElementById('coupon_code').disabled = true;
                    btn.style.display = 'none';

                    var appliedDiv = document.getElementById('coupon-applied');
                    appliedDiv.style.display = 'flex';
                    var label = data.discount_type === 'percentage'
                        ? appliedCouponCode + ' (' + parseFloat(data.discount_value) + '% off) applied!'
                        : appliedCouponCode + ' (₹' + parseFloat(data.discount_value).toFixed(2) + ' off) applied!';
                    document.getElementById('coupon-applied-text').textContent = label;

                    // Update checkout URL to include coupon
                    updateCheckoutUrl();

                    showCouponMsg('Coupon applied successfully!', '#10B981');
                } else {
                    showCouponMsg(data.message, '#EF4444');
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.textContent = 'Apply';
                showCouponMsg('Something went wrong. Please try again.', '#EF4444');
            });
        }

        function removeCoupon() {
            appliedCouponCode = '';

            // Reset UI
            document.getElementById('coupon-discount-row').style.display = 'none';
            document.getElementById('total-amount-display').textContent = '₹' + originalTotal.toFixed(2);
            document.getElementById('coupon_code').disabled = false;
            document.getElementById('coupon_code').value = '';
            document.getElementById('applyCouponBtn').style.display = 'block';
            document.getElementById('coupon-applied').style.display = 'none';
            document.getElementById('coupon-message').innerHTML = '';

            // Remove coupon from checkout URL
            updateCheckoutUrl();
        }

        function updateCheckoutUrl() {
            var btn = document.getElementById('continueBtn');
            if (!btn) return;

            var url = baseCheckoutUrl;
            if (appliedCouponCode) {
                url += (url.indexOf('?') >= 0 ? '&' : '?') + 'coupon=' + encodeURIComponent(appliedCouponCode);
            }
            btn.setAttribute('href', url);
        }

        function showCouponMsg(msg, color) {
            var el = document.getElementById('coupon-message');
            el.innerHTML = '<span style="color:' + color + ';">' + msg + '</span>';
            setTimeout(function() { el.innerHTML = ''; }, 5000);
        }
    </script>
</body>
</html>
