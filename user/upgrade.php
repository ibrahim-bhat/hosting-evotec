<?php
require_once 'includes/header.php';
require_once '../components/user_helper.php';
require_once '../components/hosting_helper.php';

$userId = $_SESSION['user_id'];

// Get order ID from URL
if (!isset($_GET['order_id'])) {
    header('Location: hosting.php');
    exit;
}

$orderId = intval($_GET['order_id']);

// Get current order details
require_once '../components/hosting_helper.php';
$currentOrder = getOrderById($conn, $orderId);

// Verify order belongs to user
if (!$currentOrder || $currentOrder['user_id'] != $userId) {
    setFlashMessage('error', 'Order not found');
    header('Location: hosting.php');
    exit;
}

// Get available upgrade options (packages with higher price)
$currentPackageId = $currentOrder['package_id'];
$currentBillingCycle = $currentOrder['billing_cycle'];
$upgradeOptions = getUpgradeOptions($conn, $currentPackageId, $currentBillingCycle);

$pageTitle = "Upgrade Plan";
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Upgrade Your Hosting Plan</h1>
    <p class="page-subtitle">Choose a better plan to enhance your hosting experience</p>
</div>

<!-- Current Plan -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="content-card">
            <h2 class="card-title">
                <i class="bi bi-server me-2"></i>
                Current Plan
            </h2>
            <div class="row g-3 mt-2">
                <div class="col-md-3">
                    <div class="text-muted">Package</div>
                    <div class="fw-bold"><?php echo htmlspecialchars($currentOrder['package_name']); ?></div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted">Billing Cycle</div>
                    <div class="fw-bold"><?php echo ucfirst($currentOrder['billing_cycle']); ?></div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted">Current Price</div>
                    <div class="fw-bold"><?php echo formatCurrency($currentOrder['total_amount']); ?></div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted">Expiry Date</div>
                    <div class="fw-bold"><?php echo formatDate($currentOrder['expiry_date']); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upgrade Options -->
<?php if (!empty($upgradeOptions)): ?>
    <div class="row g-4">
        <div class="col-12">
            <h3 class="mb-3">Available Upgrade Options</h3>
        </div>
        
        <?php foreach ($upgradeOptions as $package): ?>
            <div class="col-12 col-lg-6 col-xl-4">
                <div class="hosting-package-card <?php echo $package['is_popular'] ? 'popular' : ''; ?>">
                    <?php if ($package['is_popular']): ?>
                        <div class="popular-badge">Popular</div>
                    <?php endif; ?>
                    
                    <div class="package-name"><?php echo htmlspecialchars($package['name']); ?></div>
                    <div class="package-description">
                        <?php echo htmlspecialchars($package['short_description']); ?>
                    </div>
                    
                    <div class="package-price">
                        <?php 
                        $price = getPackagePrice($package, $currentBillingCycle);
                        echo formatCurrency($price);
                        ?>
                    </div>
                    <div class="package-period"><?php echo ucfirst($currentBillingCycle); ?> billing</div>
                    
                    <div class="package-features">
                        <?php 
                        $features = explode("\n", $package['features']);
                        foreach (array_slice($features, 0, 8) as $feature): 
                            if (trim($feature)):
                        ?>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <?php echo htmlspecialchars(trim($feature)); ?>
                            </li>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                    
                    <a href="../checkout.php?package_id=<?php echo $package['id']; ?>&billing_cycle=<?php echo $currentBillingCycle; ?>&upgrade_from=<?php echo $orderId; ?>" 
                       class="btn btn-primary w-100">
                        <i class="bi bi-arrow-up-circle me-2"></i>
                        Upgrade to This Plan
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-12">
            <div class="content-card text-center">
                <div class="py-5">
                    <i class="bi bi-info-circle" style="font-size: 64px; color: #9ca3af; margin-bottom: 16px;"></i>
                    <h3 class="card-title">No Upgrade Options Available</h3>
                    <p class="card-subtitle">You're already on the best plan for your current billing cycle, or there are no higher-tier plans available.</p>
                    <a href="hosting.php" class="btn btn-primary mt-3">
                        <i class="bi bi-arrow-left me-2"></i>
                        Back to My Hosting
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Info Alert -->
<div class="row g-4 mt-4">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Note:</strong> When you upgrade, you'll be charged the difference between your current plan and the new plan, prorated for the remaining time in your billing cycle.
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
