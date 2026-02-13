<?php
require_once 'includes/header.php';
require_once '../components/user_helper.php';
require_once '../components/hosting_helper.php';
require_once '../components/payment_settings_helper.php';

$userId = $_SESSION['user_id'];

// Get order ID from URL
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$orderId) {
    setFlashMessage('error', 'Invalid order ID');
    redirect('hosting.php');
}

// Get order details
$order = getOrderById($conn, $orderId);

if (!$order || $order['user_id'] != $userId) {
    setFlashMessage('error', 'Order not found or access denied');
    redirect('hosting.php');
}

// Get the current package (even if deactivated)
$currentPackage = getPackageById($conn, $order['package_id']);

// Get all active packages for upgrade options
$activePackages = getActivePackages($conn);

// Combine packages: ensure current package is included even if deactivated
$packages = [];
$currentPackageIncluded = false;

// Add current package first (even if inactive/deactivated)
if ($currentPackage) {
    $packages[] = $currentPackage;
    $currentPackageIncluded = true;
}

// Add all active packages (skip if already included as current package)
foreach ($activePackages as $pkg) {
    if ($currentPackageIncluded && $pkg['id'] == $order['package_id']) {
        continue; // Skip duplicate
    }
    $packages[] = $pkg;
}

$pageTitle = "Renew or Upgrade - Order #" . htmlspecialchars($order['order_number']);
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Renew or Upgrade Your Hosting</h1>
    <p class="page-subtitle">Choose to renew your current plan or upgrade to a better package</p>
</div>

<!-- Current Order Info -->
<div class="row mb-4">
    <div class="col-12">
        <div class="content-card">
            <div class="alert alert-info mb-0">
                <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Current Plan Details</h5>
                <div class="row">
                    <div class="col-md-3">
                        <strong>Package:</strong><br>
                        <?php echo htmlspecialchars($order['package_name'] ?? 'N/A'); ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Order Number:</strong><br>
                        #<?php echo htmlspecialchars($order['order_number']); ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Billing Cycle:</strong><br>
                        <?php echo ucfirst($order['billing_cycle']); ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Expiry Date:</strong><br>
                        <span class="text-danger"><?php echo formatDate($order['expiry_date']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Available Packages -->
<div class="row">
    <div class="col-12 mb-3">
        <h2 class="card-title">Select a Package</h2>
        <p class="card-subtitle">Choose to continue with the same package or upgrade to a better plan</p>
    </div>
</div>

<div class="row g-4">
    <?php foreach ($packages as $package): ?>
        <div class="col-12 col-lg-6 col-xl-4">
            <div class="hosting-package-card <?php echo $package['id'] == $order['package_id'] ? 'popular' : ''; ?>">
                <?php if ($package['id'] == $order['package_id']): ?>
                    <div class="popular-badge">Current Plan</div>
                    <?php if ($package['status'] !== 'active'): ?>
                        <div class="alert alert-warning mt-2 mb-2 p-2 small">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            This package is no longer available, but you can still renew it.
                        </div>
                    <?php endif; ?>
                <?php elseif ($package['is_popular']): ?>
                    <div class="popular-badge">Popular</div>
                <?php endif; ?>
                
                <div class="package-name"><?php echo htmlspecialchars($package['name']); ?></div>
                <div class="package-description">
                    <?php echo htmlspecialchars($package['short_description'] ?? $package['description']); ?>
                </div>
                
                <div class="package-price">
                    <?php
                    // Show renewal prices for current package, regular prices for upgrades
                    $isCurrentPkg = ($package['id'] == $order['package_id']);
                    $prices = [];
                    if ($package['price_monthly'] > 0) {
                        $rp = $isCurrentPkg ? getPackageRenewalPrice($package, 'monthly', $conn) : $package['price_monthly'];
                        $prices[] = formatCurrency($rp) . '/mo';
                    }
                    if ($package['price_yearly'] > 0) {
                        $rp = $isCurrentPkg ? getPackageRenewalPrice($package, 'yearly', $conn) : $package['price_yearly'];
                        $prices[] = formatCurrency($rp) . '/yr';
                    }
                    if ($package['price_2years'] > 0) {
                        $rp = $isCurrentPkg ? getPackageRenewalPrice($package, '2years', $conn) : $package['price_2years'];
                        $prices[] = formatCurrency($rp) . '/2yr';
                    }
                    if ($package['price_4years'] > 0) {
                        $rp = $isCurrentPkg ? getPackageRenewalPrice($package, '4years', $conn) : $package['price_4years'];
                        $prices[] = formatCurrency($rp) . '/4yr';
                    }
                    
                    if (!empty($prices)) {
                        echo 'Starting from<br>' . $prices[0];
                    } else {
                        echo 'Contact for pricing';
                    }
                    ?>
                    <div style="font-size:12px; color:#6B7280; margin-top:4px;">+ applicable taxes</div>
                </div>
                
                <!-- Features -->
                <?php if (!empty($package['features'])): ?>
                    <div class="package-features">
                        <?php 
                        $features = explode("\n", $package['features']);
                        $displayFeatures = array_slice($features, 0, 5);
                        foreach ($displayFeatures as $feature): 
                            $feature = trim($feature);
                            if (!empty($feature)):
                        ?>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <?php echo htmlspecialchars($feature); ?>
                            </li>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                        <?php if (count($features) > 5): ?>
                            <li class="text-muted">
                                <i class="bi bi-plus-circle"></i>
                                And <?php echo count($features) - 5; ?> more features...
                            </li>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Select Billing Cycle -->
                <div class="billing-cycle-selector mt-4">
                    <label class="form-label"><strong>Select Billing Cycle:</strong></label>
                    <select class="form-select" id="cycle-<?php echo $package['id']; ?>">
                        <?php 
                        $isCurrentPkg = ($package['id'] == $order['package_id']);
                        if ($package['price_monthly'] > 0): 
                            $rpMonthly = $isCurrentPkg ? getPackageRenewalPrice($package, 'monthly', $conn) : $package['price_monthly'];
                        ?>
                            <option value="monthly">Monthly - <?php echo formatCurrency($rpMonthly); ?> + taxes</option>
                        <?php endif; ?>
                        <?php if ($package['price_yearly'] > 0): 
                            $rpYearly = $isCurrentPkg ? getPackageRenewalPrice($package, 'yearly', $conn) : $package['price_yearly'];
                        ?>
                            <option value="yearly" <?php echo $order['billing_cycle'] == 'yearly' ? 'selected' : ''; ?>>
                                Yearly - <?php echo formatCurrency($rpYearly); ?> + taxes
                            </option>
                        <?php endif; ?>
                        <?php if ($package['price_2years'] > 0): 
                            $rp2y = $isCurrentPkg ? getPackageRenewalPrice($package, '2years', $conn) : $package['price_2years'];
                        ?>
                            <option value="2years" <?php echo $order['billing_cycle'] == '2years' ? 'selected' : ''; ?>>
                                2 Years - <?php echo formatCurrency($rp2y); ?> + taxes
                            </option>
                        <?php endif; ?>
                        <?php if ($package['price_4years'] > 0): 
                            $rp4y = $isCurrentPkg ? getPackageRenewalPrice($package, '4years', $conn) : $package['price_4years'];
                        ?>
                            <option value="4years" <?php echo $order['billing_cycle'] == '4years' ? 'selected' : ''; ?>>
                                4 Years - <?php echo formatCurrency($rp4y); ?> + taxes
                            </option>
                        <?php endif; ?>
                    </select>
                    <?php 
                    $gstPct = getGlobalGstPercentage($conn);
                    $setupFeePct = getGlobalSetupFee($conn);
                    $processingFeePct = getGlobalProcessingFee($conn);
                    ?>
                    <small class="text-muted d-block mt-1">
                        <i class="bi bi-info-circle"></i>
                        GST <?php echo $gstPct; ?>% applicable on all plans.
                        <?php if ($setupFeePct > 0 && !$isCurrentPkg): ?>
                            Setup fee: <?php echo $setupFeePct; ?>% (one-time, new plans only).
                        <?php endif; ?>
                        <?php if ($processingFeePct > 0): ?>
                            Processing fee: <?php echo $processingFeePct; ?>%.
                        <?php endif; ?>
                    </small>
                </div>
                
                <button onclick="selectPackage('<?php echo $package['slug']; ?>', <?php echo $package['id']; ?>, <?php echo $orderId; ?>)" 
                        class="btn btn-primary w-100 mt-3">
                    <i class="bi bi-<?php echo $package['id'] == $order['package_id'] ? 'arrow-repeat' : 'arrow-up-circle'; ?> me-1"></i>
                    <?php echo $package['id'] == $order['package_id'] ? 'Renew This Plan' : 'Upgrade to This Plan'; ?>
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
.billing-cycle-selector {
    margin-top: 1rem;
}

.billing-cycle-selector .form-select {
    padding: 10px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    background: #fff;
    cursor: pointer;
    transition: all 0.3s;
}

.billing-cycle-selector .form-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.hosting-package-card.popular {
    border: 2px solid #667eea;
    transform: scale(1.02);
}
</style>

<script>
function selectPackage(packageSlug, packageId, orderId) {
    const cycle = document.getElementById('cycle-' + packageId).value;
    window.location.href = '../select-package.php?package=' + packageSlug + '&cycle=' + cycle + '&renew=' + orderId;
}
</script>

<?php include 'includes/footer.php'; ?>
