<?php
require_once 'config.php';
require_once 'components/auth_helper.php';
require_once 'components/hosting_helper.php';

// Get package from URL
$packageSlug = isset($_GET['package']) ? sanitizeInput($_GET['package']) : '';
$billingCycle = isset($_GET['cycle']) ? sanitizeInput($_GET['cycle']) : 'monthly';
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
    redirect('hosting.php');
}

// Get package details
// For renewals, we need to get the package even if it's deactivated
if ($renewOrder) {
    // Get package by slug without status check for renewals
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
    // For new orders, only get active packages
    $package = getPackageBySlug($conn, $packageSlug);
    if (!$package || $package['status'] !== 'active') {
        setFlashMessage('error', 'Package not found or not available');
        redirect('index.php');
    }
}

// Check if user is logged in
$isLoggedIn = isLoggedIn();

// Get pricing for different cycles (only if they exist and are greater than 0)
$availableCycles = [];
if (!empty($package['price_monthly']) && $package['price_monthly'] > 0) {
    $availableCycles['monthly'] = [
        'price' => $package['price_monthly'],
        'label' => 'Monthly',
        'total' => $package['price_monthly']
    ];
}
if (!empty($package['price_yearly']) && $package['price_yearly'] > 0) {
    $availableCycles['yearly'] = [
        'price' => $package['price_yearly'] / 12,
        'label' => 'Yearly',
        'total' => $package['price_yearly']
    ];
}
if (!empty($package['price_2years']) && $package['price_2years'] > 0) {
    $availableCycles['2years'] = [
        'price' => $package['price_2years'] / 24,
        'label' => '2 Years',
        'total' => $package['price_2years']
    ];
}
if (!empty($package['price_4years']) && $package['price_4years'] > 0) {
    $availableCycles['4years'] = [
        'price' => $package['price_4years'] / 48,
        'label' => '4 Years',
        'total' => $package['price_4years']
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
                    <?php if ($renewOrderId && $renewOrder): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-arrow-repeat me-2"></i>
                            <strong><?php echo $renewOrder['package_id'] == $package['id'] ? 'Renewal' : 'Upgrade'; ?>:</strong> 
                            You are <?php echo $renewOrder['package_id'] == $package['id'] ? 'renewing' : 'upgrading from'; ?> 
                            <?php if ($renewOrder['package_id'] != $package['id']): ?>
                                <strong><?php echo htmlspecialchars($renewOrder['package_name']); ?></strong> to 
                            <?php endif; ?>
                            your hosting plan.
                            <small class="d-block mt-1">Previous Order: #<?php echo htmlspecialchars($renewOrder['order_number']); ?></small>
                        </div>
                        <?php if ($package['status'] !== 'active'): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Note:</strong> This package is no longer available for new customers, but you can still renew your existing plan.
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <h1><?php echo htmlspecialchars($package['name']); ?></h1>
                    <p class="text-muted"><?php echo htmlspecialchars($package['description']); ?></p>
                    
                    <!-- Billing Cycle Selection -->
                    <h5 class="mt-4 mb-3">Select Billing Cycle</h5>
                    <div class="billing-cycle-tabs">
                        <?php foreach ($availableCycles as $cycleKey => $cycleData): ?>
                        <a href="?package=<?php echo $packageSlug; ?>&cycle=<?php echo $cycleKey; ?><?php echo $renewOrderId ? '&renew=' . $renewOrderId : ''; ?>" 
                           class="billing-tab <?php echo $billingCycle === $cycleKey ? 'active' : ''; ?>">
                            <div style="font-weight: 600;"><?php echo $cycleData['label']; ?></div>
                            <small>₹<?php echo number_format($cycleData['price'], 2); ?>/mo</small>
                            <?php if (isset($availableCycles['monthly'])): ?>
                                <?php $discount = getDiscount($availableCycles['monthly']['price'], $cycleData['price']); ?>
                                <?php if ($discount > 0): ?>
                                <div class="price-savings">Save <?php echo $discount; ?>%</div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    
        
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
                        <span class="text-muted"><?php echo $availableCycles[$billingCycle]['label']; ?></span>
                    </div>
                    
                    <?php
                    $cyclePrice = $availableCycles[$billingCycle]['total'];
                    $calculations = calculateOrderTotal($cyclePrice, $package['setup_fee'], $package['gst_percentage'], $package['processing_fee']);
                    ?>
                    
                    <div class="price-display">
                        ₹<?php echo number_format($calculations['total_amount'], 2); ?>
                    </div>
                    
                    <?php if ($billingCycle !== 'monthly'): ?>
                    <?php
                    $monthlyEquivalent = $availableCycles[$billingCycle]['price'];
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

