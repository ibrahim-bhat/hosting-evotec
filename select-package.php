<?php
require_once 'config.php';
require_once 'components/auth_helper.php';
require_once 'components/hosting_helper.php';

// Get package from URL
$packageSlug = isset($_GET['package']) ? sanitizeInput($_GET['package']) : '';
$billingCycle = isset($_GET['cycle']) ? sanitizeInput($_GET['cycle']) : 'monthly';

if (empty($packageSlug)) {
    setFlashMessage('error', 'Invalid package selected');
    redirect('hosting.php');
}

// Get package details
$package = getPackageBySlug($conn, $packageSlug);
if (!$package || $package['status'] !== 'active') {
    setFlashMessage('error', 'Package not found');
    redirect('hosting.php');
}

// Check if user is logged in
$isLoggedIn = isLoggedIn();

// Get pricing for different cycles
$prices = [
    'monthly' => $package['price_monthly'],
    'yearly' => $package['price_yearly'] / 12,
    '2years' => $package['price_2years'] / 24,
    '4years' => $package['price_4years'] / 48
];

// Calculate pricing
function getDiscount($originalPrice, $discountedPrice) {
    if ($originalPrice == 0) return 0;
    return round((($originalPrice - $discountedPrice) / $originalPrice) * 100);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Package - <?php echo htmlspecialchars($package['name']); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fa;
            padding: 40px 0;
        }
        
        .package-select-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .billing-cycle-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .billing-tab {
            flex: 1;
            min-width: 150px;
            padding: 15px;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #333;
        }
        
        .billing-tab:hover {
            border-color: #4f46e5;
            transform: translateY(-2px);
        }
        
        .billing-tab.active {
            border-color: #4f46e5;
            background: #4f46e5;
            color: white;
        }
        
        .package-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .price-display {
            font-size: 48px;
            font-weight: 700;
            color: #4f46e5;
            margin: 20px 0;
        }
        
        .price-savings {
            background: #10b981;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .btn-checkout {
            width: 100%;
            padding: 15px;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin-top: 30px;
        }
        
        .btn-checkout:hover {
            background: #6366f1;
        }
        
        .btn-login {
            width: 100%;
            padding: 15px;
            background: #333;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin-top: 30px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container package-select-container">
        <div class="row">
            <div class="col-md-8">
                <div class="package-card">
                    <h1><?php echo htmlspecialchars($package['name']); ?></h1>
                    <p class="text-muted"><?php echo htmlspecialchars($package['description']); ?></p>
                    
                    <!-- Billing Cycle Selection -->
                    <h5 class="mt-4 mb-3">Select Billing Cycle</h5>
                    <div class="billing-cycle-tabs">
                        <a href="?package=<?php echo $packageSlug; ?>&cycle=monthly" 
                           class="billing-tab <?php echo $billingCycle === 'monthly' ? 'active' : ''; ?>">
                            <div style="font-weight: 600;">Monthly</div>
                            <small>₹<?php echo number_format($prices['monthly'], 2); ?>/mo</small>
                            <?php if ($getDiscount = getDiscount($prices['monthly'] * 12, $package['price_yearly']) > 0): ?>
                            <div class="price-savings">Save <?php echo $getDiscount; ?>%</div>
                            <?php endif; ?>
                        </a>
                        
                        <a href="?package=<?php echo $packageSlug; ?>&cycle=yearly" 
                           class="billing-tab <?php echo $billingCycle === 'yearly' ? 'active' : ''; ?>">
                            <div style="font-weight: 600;">Yearly</div>
                            <small>₹<?php echo number_format($prices['yearly'], 2); ?>/mo</small>
                            <?php $discount = getDiscount($prices['monthly'], $prices['yearly']); ?>
                            <?php if ($discount > 0): ?>
                            <div class="price-savings">Save <?php echo $discount; ?>%</div>
                            <?php endif; ?>
                        </a>
                        
                        <a href="?package=<?php echo $packageSlug; ?>&cycle=2years" 
                           class="billing-tab <?php echo $billingCycle === '2years' ? 'active' : ''; ?>">
                            <div style="font-weight: 600;">2 Years</div>
                            <small>₹<?php echo number_format($prices['2years'], 2); ?>/mo</small>
                            <?php $discount = getDiscount($prices['monthly'], $prices['2years']); ?>
                            <?php if ($discount > 0): ?>
                            <div class="price-savings">Save <?php echo $discount; ?>%</div>
                            <?php endif; ?>
                        </a>
                        
                        <a href="?package=<?php echo $packageSlug; ?>&cycle=4years" 
                           class="billing-tab <?php echo $billingCycle === '4years' ? 'active' : ''; ?>">
                            <div style="font-weight: 600;">4 Years</div>
                            <small>₹<?php echo number_format($prices['4years'], 2); ?>/mo</small>
                            <?php $discount = getDiscount($prices['monthly'], $prices['4years']); ?>
                            <?php if ($discount > 0): ?>
                            <div class="price-savings">Save <?php echo $discount; ?>%</div>
                            <?php endif; ?>
                        </a>
                    </div>
                    
                    <!-- Package Features -->
                    <h5 class="mt-4 mb-3">What's Included</h5>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-check-circle-fill text-success"></i> <?php echo $package['storage_gb']; ?> GB Storage</li>
                        <li><i class="bi bi-check-circle-fill text-success"></i> <?php echo $package['bandwidth_gb']; ?> GB Bandwidth</li>
                        <li><i class="bi bi-check-circle-fill text-success"></i> <?php echo $package['allowed_websites'] > 999 ? 'Unlimited' : $package['allowed_websites']; ?> Websites</li>
                        <li><i class="bi bi-check-circle-fill text-success"></i> <?php echo $package['database_limit'] > 999 ? 'Unlimited' : $package['database_limit']; ?> Databases</li>
                        <?php if ($package['ssh_access']): ?>
                        <li><i class="bi bi-check-circle-fill text-success"></i> SSH Access</li>
                        <?php endif; ?>
                        <?php if ($package['ssl_free']): ?>
                        <li><i class="bi bi-check-circle-fill text-success"></i> Free SSL Certificate</li>
                        <?php endif; ?>
                        <?php if ($package['daily_backups']): ?>
                        <li><i class="bi bi-check-circle-fill text-success"></i> Daily Backups</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-md-4">
                <div class="package-card">
                    <h5 class="mb-4">Order Summary</h5>
                    
                    <div class="mb-3">
                        <strong>Package:</strong><br>
                        <span class="text-muted"><?php echo htmlspecialchars($package['name']); ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Billing Cycle:</strong><br>
                        <span class="text-muted"><?php echo ucfirst(str_replace('years', ' Years', $billingCycle)); ?></span>
                    </div>
                    
                    <?php
                    $cyclePrice = getPackagePrice($package, $billingCycle);
                    $calculations = calculateOrderTotal($cyclePrice, $package['setup_fee'], $package['gst_percentage'], $package['processing_fee']);
                    ?>
                    
                    <div class="price-display">
                        ₹<?php echo number_format($calculations['total_amount'], 2); ?>
                    </div>
                    
                    <?php if ($billingCycle !== 'monthly'): ?>
                    <?php
                    $monthlyEquivalent = $cyclePrice;
                    if ($billingCycle === 'yearly') $monthlyEquivalent = $cyclePrice / 12;
                    if ($billingCycle === '2years') $monthlyEquivalent = $cyclePrice / 24;
                    if ($billingCycle === '4years') $monthlyEquivalent = $cyclePrice / 48;
                    ?>
                    <p class="text-center text-muted">₹<?php echo number_format($monthlyEquivalent, 2); ?> per month</p>
                    <?php endif; ?>
                    
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 20px;">
                        <small class="text-muted">You will be billed ₹<?php echo number_format($cyclePrice, 2); ?> for <?php echo ucfirst(str_replace('years', ' Years', $billingCycle)); ?> period</small>
                    </div>
                    
                    <?php if ($isLoggedIn): ?>
                        <a href="checkout.php?package=<?php echo $packageSlug; ?>&cycle=<?php echo $billingCycle; ?>" class="btn-checkout">
                            Continue to Payment <i class="bi bi-arrow-right"></i>
                        </a>
                    <?php else: ?>
                        <a href="login.php?redirect=<?php echo urlencode('select-package.php?package=' . $packageSlug . '&cycle=' . $billingCycle); ?>" class="btn-login">
                            Login to Continue <i class="bi bi-box-arrow-in-right"></i>
                        </a>
                        <p class="text-center mt-3">
                            <small class="text-muted">Don't have an account? <a href="register.php">Sign up</a></small>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

