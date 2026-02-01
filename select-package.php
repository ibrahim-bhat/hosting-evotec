<?php
session_start();
require_once 'config.php';
require_once 'components/auth_helper.php';
require_once 'components/hosting_helper.php';

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
$availableCycles = [];
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
$cyclePrice = $availableCycles[$billingCycle]['total'];
$calculations = calculateOrderTotal($cyclePrice, $package['setup_fee'], $package['gst_percentage'], $package['processing_fee']);
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

        .billing-notice {
            background: #FEF3C7;
            border: 1px solid #FCD34D;
            border-radius: 8px;
            padding: 12px;
            margin: 16px 0;
            font-size: 13px;
            color: #92400E;
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

                <div class="price-display">
                    <div class="price-amount">₹<?php echo number_format($calculations['total_amount'], 2); ?></div>
                    <div class="price-period">₹<?php echo number_format($availableCycles[$billingCycle]['price'], 2); ?> per month</div>
                </div>

                <div class="billing-notice">
                    <i class="fas fa-info-circle"></i> You will be billed ₹<?php echo number_format($cyclePrice, 2); ?> for <?php echo ucfirst(str_replace('years', ' Years', $billingCycle)); ?> period
                </div>

                <?php if ($isLoggedIn): ?>
                    <a href="checkout.php?package=<?php echo urlencode($packageSlug); ?>&cycle=<?php echo $billingCycle; ?><?php echo $renewOrderId ? '&renew=' . $renewOrderId : ''; ?>" class="btn-continue">
                        <i class="fas fa-lock"></i> Login to Continue
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
</body>
</html>
