<?php
require_once 'includes/header.php';
require_once '../components/user_helper.php';

$userId = $_SESSION['user_id'];

// Auto-cleanup: Remove pending orders older than 30 minutes
$cutoffTime = date('Y-m-d H:i:s', strtotime('-30 minutes'));
$stmt = $conn->prepare("DELETE FROM hosting_orders WHERE user_id = ? AND payment_status = 'pending' AND created_at < ?");
$stmt->bind_param("is", $userId, $cutoffTime);
$stmt->execute();
$stmt->close();

// Auto-fix: Add column if it doesn't exist
$result = mysqli_query($conn, "SHOW COLUMNS FROM hosting_orders LIKE 'renewed_from_order_id'");
if (mysqli_num_rows($result) == 0) {
    // Add the column silently
    mysqli_query($conn, "ALTER TABLE hosting_orders ADD COLUMN renewed_from_order_id INT(11) NULL DEFAULT NULL COMMENT 'Previous order ID if this is a renewal/upgrade' AFTER order_number");
    mysqli_query($conn, "ALTER TABLE hosting_orders ADD INDEX idx_renewed_from (renewed_from_order_id)");
}

// Auto-detect and link renewals/upgrades - works for same package OR upgrades
// Link newer orders to older expired orders from the same user
$autoLinkQuery = "
    UPDATE hosting_orders o1
    INNER JOIN (
        SELECT o2.id, MAX(o3.id) as old_order_id
        FROM hosting_orders o2
        LEFT JOIN hosting_orders o3 ON 
            o2.user_id = o3.user_id 
            AND o2.created_at > o3.created_at
            AND o3.payment_status = 'paid'
            AND o3.expiry_date < o2.created_at
            AND NOT EXISTS (SELECT 1 FROM hosting_orders WHERE renewed_from_order_id = o3.id)
        WHERE o2.user_id = ?
            AND o2.renewed_from_order_id IS NULL
            AND o3.id IS NOT NULL
        GROUP BY o2.id
    ) latest ON o1.id = latest.id
    SET o1.renewed_from_order_id = latest.old_order_id";
$stmt = $conn->prepare($autoLinkQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->close();

// Get user's hosting orders
$orders = getUserOrders($conn, $userId);

$pageTitle = "My Hosting";
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">My Hosting</h1>
    <p class="page-subtitle">Manage your hosting services and view order details</p>
</div>

<?php if (!empty($orders)): ?>
    <!-- Hosting Orders -->
    <div class="row g-4">
        <?php foreach ($orders as $order): ?>
            <div class="col-12 col-lg-6 col-xl-4">
                <div class="hosting-package-card <?php echo $order['payment_status'] === 'paid' ? 'popular' : ''; ?>">
                    <?php if ($order['payment_status'] === 'paid'): ?>
                        <div class="popular-badge">Active</div>
                    <?php endif; ?>
                    
                    <div class="package-name"><?php echo htmlspecialchars($order['package_name'] ?? 'Unknown Package'); ?></div>
                    <div class="package-description">
                        Order #<?php echo htmlspecialchars($order['order_number']); ?>
                    </div>
                    
                    <?php
                    // Get package details to show full price (not prorated upgrade price)
                    require_once '../components/hosting_helper.php';
                    $package = getPackageById($conn, $order['package_id']);
                    $packagePrice = $package ? getPackagePrice($package, $order['billing_cycle']) : $order['total_amount'];
                    ?>
                    
                    <div class="package-price"><?php echo formatCurrency($packagePrice); ?></div>
                    <div class="package-period">
                        <?php echo ucfirst($order['billing_cycle']); ?> billing
                    </div>
                    
                    <div class="package-features">
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <strong>Status:</strong> 
                            <span class="order-status <?php echo $order['payment_status']; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </li>
                        <li>
                            <i class="bi bi-calendar"></i>
                            <strong>Order Date:</strong> <?php echo formatDate($order['created_at']); ?>
                        </li>
                        <?php if ($order['expiry_date']): ?>
                            <li>
                                <i class="bi bi-clock"></i>
                                <strong>Expires:</strong> <?php echo formatDate($order['expiry_date']); ?>
                                <?php if (strtotime($order['expiry_date']) < time()): ?>
                                    <span class="text-danger fw-bold">(Expired)</span>
                                <?php elseif (isOrderExpiringSoon($order['expiry_date'])): ?>
                                    <span class="text-danger">(<?php echo getDaysUntilExpiry($order['expiry_date']); ?> days left)</span>
                                <?php endif; ?>
                            </li>
                        <?php endif; ?>
                        <?php if ($order['domain_name']): ?>
                            <li>
                                <i class="bi bi-globe"></i>
                                <strong>Domain:</strong> <?php echo htmlspecialchars($order['domain_name']); ?>
                            </li>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <?php if ($order['payment_status'] === 'pending'): ?>
                            <a href="../payment-handler.php?order_id=<?php echo $order['id']; ?>" class="btn btn-primary flex-fill">
                                <i class="bi bi-credit-card me-1"></i>
                                Pay Now
                            </a>
                        <?php elseif ($order['expiry_date'] && strtotime($order['expiry_date']) < time()): ?>
                            <a href="renew.php?order_id=<?php echo $order['id']; ?>" class="btn btn-warning flex-fill">
                                <i class="bi bi-arrow-repeat me-1"></i>
                                Renew
                            </a>
                            <a href="upgrade.php?order_id=<?php echo $order['id']; ?>" class="btn btn-success flex-fill">
                                <i class="bi bi-arrow-up-circle me-1"></i>
                                Upgrade
                            </a>
                        <?php elseif (isOrderExpiringSoon($order['expiry_date'])): ?>
                            <a href="renew.php?order_id=<?php echo $order['id']; ?>" class="btn btn-warning flex-fill">
                                <i class="bi bi-arrow-repeat me-1"></i>
                                Renew
                            </a>
                            <a href="upgrade.php?order_id=<?php echo $order['id']; ?>" class="btn btn-success flex-fill">
                                <i class="bi bi-arrow-up-circle me-1"></i>
                                Upgrade
                            </a>
                        <?php else: ?>
                            <a href="upgrade.php?order_id=<?php echo $order['id']; ?>" class="btn btn-success flex-fill">
                                <i class="bi bi-arrow-up-circle me-1"></i>
                                Upgrade Plan
                            </a>
                        <?php endif; ?>
                        
                        <a href="orders.php?id=<?php echo $order['id']; ?>" class="btn btn-action">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <!-- No Orders -->
    <div class="row">
        <div class="col-12">
            <div class="content-card text-center">
                <div class="py-5">
                    <i class="bi bi-server" style="font-size: 64px; color: #9ca3af; margin-bottom: 16px;"></i>
                    <h3 class="card-title">No Hosting Orders</h3>
                    <p class="card-subtitle">You haven't placed any hosting orders yet.</p>
                    <a href="../select-package.php" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle me-2"></i>
                        Order Hosting
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Quick Stats -->
<?php if (!empty($orders)): ?>
    <div class="row g-4 mt-4">
        <div class="col-12">
            <div class="content-card">
                <h2 class="card-title">Hosting Summary</h2>
                <p class="card-subtitle">Overview of your hosting services</p>
                
                <div class="row g-3">
                    <?php
                    $stats = getUserHostingStats($conn, $userId);
                    ?>
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="stats-value" style="font-size: 24px;"><?php echo $stats['total_orders']; ?></div>
                            <div class="stats-label">Total Orders</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="stats-value" style="font-size: 24px;"><?php echo $stats['active_orders']; ?></div>
                            <div class="stats-label">Active Orders</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="stats-value" style="font-size: 24px;"><?php echo $stats['active_websites']; ?></div>
                            <div class="stats-label">Active Websites</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
