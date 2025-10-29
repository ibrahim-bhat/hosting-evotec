<?php
require_once 'includes/header.php';
require_once '../components/user_helper.php';

// Get user's hosting statistics
$userId = $_SESSION['user_id'];

// Get user's orders count
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM hosting_orders WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$totalOrders = $result->fetch_assoc()['total'];
$stmt->close();

// Get active orders count
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM hosting_orders WHERE user_id = ? AND order_status = 'active'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$activeOrders = $result->fetch_assoc()['total'];
$stmt->close();

// Get websites count
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM hosting_websites WHERE user_id = ? AND status = 'active'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$totalWebsites = $result->fetch_assoc()['total'];
$stmt->close();

// Get pending orders count
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM hosting_orders WHERE user_id = ? AND payment_status = 'pending'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$pendingOrders = $result->fetch_assoc()['total'];
$stmt->close();

// Get recent orders
$stmt = $conn->prepare("
    SELECT ho.*, hp.name as package_name 
    FROM hosting_orders ho 
    LEFT JOIN hosting_packages hp ON ho.package_id = hp.id 
    WHERE ho.user_id = ? 
    ORDER BY ho.created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$recentOrders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get recent websites
$stmt = $conn->prepare("
    SELECT hw.*, hp.name as package_name 
    FROM hosting_websites hw 
    LEFT JOIN hosting_packages hp ON hw.package_id = hp.id 
    WHERE hw.user_id = ? 
    ORDER BY hw.created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$recentWebsites = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = "Dashboard";
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Welcome back! Here's an overview of your hosting services.</p>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <!-- Total Orders -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Total Orders</span>
                <i class="bi bi-cart-fill stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo number_format($totalOrders); ?></div>
            <div class="stats-change positive">
                <i class="bi bi-arrow-up"></i>
                <span>All your hosting orders</span>
            </div>
        </div>
    </div>
    
    <!-- Active Orders -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Active Orders</span>
                <i class="bi bi-check-circle-fill stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo number_format($activeOrders); ?></div>
            <div class="stats-change positive">
                <i class="bi bi-arrow-up"></i>
                <span>Currently active services</span>
            </div>
        </div>
    </div>
    
    <!-- Websites -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Websites</span>
                <i class="bi bi-globe stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo number_format($totalWebsites); ?></div>
            <div class="stats-change positive">
                <i class="bi bi-arrow-up"></i>
                <span>Active websites</span>
            </div>
        </div>
    </div>
    
    <!-- Pending Orders -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Pending Orders</span>
                <i class="bi bi-clock-fill stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo number_format($pendingOrders); ?></div>
            <div class="stats-change <?php echo $pendingOrders > 0 ? 'negative' : 'positive'; ?>">
                <i class="bi bi-arrow-<?php echo $pendingOrders > 0 ? 'up' : 'down'; ?>"></i>
                <span><?php echo $pendingOrders > 0 ? 'Awaiting payment' : 'All paid'; ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row g-4">
    <!-- Recent Orders -->
    <div class="col-12 col-lg-7">
        <div class="content-card">
            <h2 class="card-title">Recent Orders</h2>
            <p class="card-subtitle">Your latest hosting orders and their status</p>
            
            <?php if (!empty($recentOrders)): ?>
                <div class="table-responsive">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Package</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>
                                        <span class="user-name"><?php echo htmlspecialchars($order['order_number']); ?></span>
                                    </td>
                                    <td>
                                        <span class="user-email"><?php echo htmlspecialchars($order['package_name'] ?? 'N/A'); ?></span>
                                    </td>
                                    <td>
                                        <span class="user-email">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                                    </td>
                                    <td>
                                        <span class="order-status <?php echo $order['payment_status']; ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="user-email"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No orders found</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Websites -->
    <div class="col-12 col-lg-5">
        <div class="content-card">
            <h2 class="card-title">My Websites</h2>
            <p class="card-subtitle">Your hosted websites and their status</p>
            
            <?php if (!empty($recentWebsites)): ?>
                <div class="activity-list">
                    <?php foreach ($recentWebsites as $website): ?>
                        <div class="activity-item">
                            <div class="activity-dot"></div>
                            <div class="activity-content">
                                <div class="activity-user"><?php echo htmlspecialchars($website['website_name']); ?></div>
                                <div class="activity-time">
                                    <?php echo htmlspecialchars($website['domain_name']); ?> - 
                                    <span class="website-status <?php echo $website['status']; ?>">
                                        <?php echo ucfirst($website['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">No websites found</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row g-4 mt-4">
    <div class="col-12">
        <div class="content-card">
            <h2 class="card-title">Quick Actions</h2>
            <p class="card-subtitle">Common tasks and shortcuts</p>
            
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-md-3">
                    <a href="hosting.php" class="btn btn-primary w-100">
                        <i class="bi bi-server me-2"></i>
                        View Hosting
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <a href="orders.php" class="btn btn-secondary w-100">
                        <i class="bi bi-cart-fill me-2"></i>
                        View Orders
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <a href="websites.php" class="btn btn-secondary w-100">
                        <i class="bi bi-globe me-2"></i>
                        Manage Websites
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <a href="../select-package.php" class="btn btn-secondary w-100">
                        <i class="bi bi-plus-circle me-2"></i>
                        Order New Hosting
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
