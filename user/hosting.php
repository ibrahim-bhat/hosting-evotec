<?php
require_once 'includes/header.php';
require_once '../components/user_helper.php';

$userId = $_SESSION['user_id'];

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
                    
                    <div class="package-price"><?php echo formatCurrency($order['total_amount']); ?></div>
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
                                <?php if (isOrderExpiringSoon($order['expiry_date'])): ?>
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
                        <?php else: ?>
                            <button class="btn btn-secondary flex-fill" disabled>
                                <i class="bi bi-check-circle me-1"></i>
                                Paid
                            </button>
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
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="stats-value" style="font-size: 24px;"><?php echo formatCurrency($stats['total_spent']); ?></div>
                            <div class="stats-label">Total Spent</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
