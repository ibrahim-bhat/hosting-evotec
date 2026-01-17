<?php
require_once '../config.php';
require_once '../components/auth_helper.php';
require_once '../components/admin_helper.php';
require_once '../components/hosting_helper.php';
require_once '../components/settings_helper.php';
require_once '../components/cleanup_helper.php';
require_once '../components/user_helper.php';

// Require admin access
requireAdmin();

// Run automatic cleanup tasks every time admin dashboard loads
$cleanupResults = runAllCleanupTasks($conn);

// Store cleanup stats
$cleanupMessage = '';
$totalCleaned = array_sum($cleanupResults);
if ($totalCleaned > 0) {
    $details = [];
    if ($cleanupResults['old_pending'] > 0) {
        $details[] = "{$cleanupResults['old_pending']} old pending orders cancelled";
    }
    if ($cleanupResults['duplicate_pending'] > 0) {
        $details[] = "{$cleanupResults['duplicate_pending']} duplicate pending orders cancelled";
    }
    if ($cleanupResults['expired_orders'] > 0) {
        $details[] = "{$cleanupResults['expired_orders']} orders marked as expired";
    }
    $cleanupMessage = 'Auto-cleanup completed: ' . implode(', ', $details);
}

// Get real dashboard statistics
// Total Users
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$totalUsers = mysqli_fetch_assoc($result)['total'];

// Active Users (users who placed an order in last 30 days)
$result = mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) as active FROM hosting_orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$activeUsers = mysqli_fetch_assoc($result)['active'];

// Inactive Users
$inactiveUsers = $totalUsers - $activeUsers;

// Activity Rate
$activityRate = $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 1) : 0;

// Calculate changes from last month
// Total Users Change
$result = mysqli_query($conn, "SELECT COUNT(*) as last_month FROM users WHERE role = 'user' AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
$lastMonthUsers = mysqli_fetch_assoc($result)['last_month'];
$newUsersThisMonth = $totalUsers - $lastMonthUsers;
$totalUsersChange = $lastMonthUsers > 0 ? round(($newUsersThisMonth / $lastMonthUsers) * 100, 1) : 0;

// Active Users Change
$result = mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) as active FROM hosting_orders WHERE created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY)");
$lastMonthActive = mysqli_fetch_assoc($result)['active'];
$activeUsersChange = $lastMonthActive > 0 ? round((($activeUsers - $lastMonthActive) / $lastMonthActive) * 100, 1) : 0;

// Inactive Users Change
$lastMonthInactive = $lastMonthUsers - $lastMonthActive;
$inactiveUsersChange = $lastMonthInactive > 0 ? round((($inactiveUsers - $lastMonthInactive) / $lastMonthInactive) * 100, 1) : 0;

// Activity Rate Change
$lastMonthActivityRate = $lastMonthUsers > 0 ? round(($lastMonthActive / $lastMonthUsers) * 100, 1) : 0;
$activityRateChange = $lastMonthActivityRate > 0 ? round($activityRate - $lastMonthActivityRate, 1) : 0;

// Get Orders Statistics
$result = mysqli_query($conn, "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_orders,
    SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN order_status = 'active' THEN 1 ELSE 0 END) as active_orders,
    SUM(CASE WHEN order_status = 'expired' THEN 1 ELSE 0 END) as expired_orders
    FROM hosting_orders");
$ordersStats = mysqli_fetch_assoc($result);

// Get Revenue Statistics
$result = mysqli_query($conn, "SELECT 
    SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as total_revenue,
    SUM(CASE WHEN payment_status = 'paid' AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) THEN total_amount ELSE 0 END) as month_revenue,
    SUM(CASE WHEN payment_status = 'paid' AND DATE(created_at) = CURDATE() THEN total_amount ELSE 0 END) as today_revenue
    FROM hosting_orders");
$revenueStats = mysqli_fetch_assoc($result);

// Get last month revenue for comparison
$result = mysqli_query($conn, "SELECT 
    SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as last_month_revenue
    FROM hosting_orders 
    WHERE MONTH(created_at) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) 
    AND YEAR(created_at) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))");
$lastMonthRevenue = mysqli_fetch_assoc($result)['last_month_revenue'] ?? 0;
$revenueChange = $lastMonthRevenue > 0 ? round((($revenueStats['month_revenue'] - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) : 0;

// Get Conversion Rate
$result = mysqli_query($conn, "SELECT 
    COUNT(*) as total_visitors,
    (SELECT COUNT(*) FROM hosting_orders WHERE payment_status = 'paid') as converted_orders
    FROM users WHERE role = 'user'");
$conversionStats = mysqli_fetch_assoc($result);
$conversionRate = $conversionStats['total_visitors'] > 0 ? round(($conversionStats['converted_orders'] / $conversionStats['total_visitors']) * 100, 1) : 0;

// Get Average Order Value
$result = mysqli_query($conn, "SELECT AVG(total_amount) as avg_order_value FROM hosting_orders WHERE payment_status = 'paid'");
$avgOrderValue = mysqli_fetch_assoc($result)['avg_order_value'] ?? 0;

// Get Payment Method Breakdown
$result = mysqli_query($conn, "SELECT 
    payment_method,
    COUNT(*) as count,
    SUM(total_amount) as revenue
    FROM hosting_orders 
    WHERE payment_status = 'paid'
    GROUP BY payment_method
    ORDER BY revenue DESC");
$paymentMethods = [];
while ($row = mysqli_fetch_assoc($result)) {
    $paymentMethods[] = $row;
}

// Get Customer Lifetime Value
$result = mysqli_query($conn, "SELECT 
    COUNT(DISTINCT user_id) as total_customers,
    SUM(total_amount) as total_revenue
    FROM hosting_orders 
    WHERE payment_status = 'paid'");
$cltvStats = mysqli_fetch_assoc($result);
$customerLifetimeValue = $cltvStats['total_customers'] > 0 ? round($cltvStats['total_revenue'] / $cltvStats['total_customers'], 2) : 0;

// Get Packages Statistics
$result = mysqli_query($conn, "SELECT 
    hp.name as package_name,
    COUNT(ho.id) as order_count,
    SUM(CASE WHEN ho.payment_status = 'paid' THEN ho.total_amount ELSE 0 END) as revenue
    FROM hosting_packages hp
    LEFT JOIN hosting_orders ho ON hp.id = ho.package_id
    GROUP BY hp.id, hp.name
    ORDER BY order_count DESC
    LIMIT 5");
$popularPackages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $popularPackages[] = $row;
}

// Get Recent Users
$result = mysqli_query($conn, "SELECT name, email, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 8");
$recentUsers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $recentUsers[] = $row;
}

// Get Recent Orders
$result = mysqli_query($conn, "SELECT 
    ho.order_number, ho.total_amount, ho.payment_status, ho.order_status, ho.created_at,
    u.name as user_name, u.email as user_email,
    hp.name as package_name
    FROM hosting_orders ho
    LEFT JOIN users u ON ho.user_id = u.id
    LEFT JOIN hosting_packages hp ON ho.package_id = hp.id
    ORDER BY ho.created_at DESC
    LIMIT 10");
$recentOrders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $recentOrders[] = $row;
}

$pageTitle = "Dashboard";
include 'includes/header.php';
?>
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Welcome back! Here's an overview of your system.</p>
            </div>
            
            <?php if (!empty($cleanupMessage)): ?>
            <!-- Cleanup Alert -->
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <strong>System Cleanup:</strong> <?php echo $cleanupMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <!-- Total Users -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stats-header">
                            <span class="stats-label">Total Users</span>
                            <i class="bi bi-people stats-icon"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($totalUsers); ?></div>
                        <div class="stats-change <?php echo $totalUsersChange >= 0 ? 'positive' : 'negative'; ?>">
                            <i class="bi bi-arrow-<?php echo $totalUsersChange >= 0 ? 'up' : 'down'; ?>"></i>
                            <span><?php echo $totalUsersChange >= 0 ? '+' : ''; ?><?php echo $totalUsersChange; ?>% from last month</span>
                        </div>
                    </div>
                </div>
                
                <!-- Active Users -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stats-header">
                            <span class="stats-label">Active Users</span>
                            <i class="bi bi-person-check stats-icon"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($activeUsers); ?></div>
                        <div class="stats-change <?php echo $activeUsersChange >= 0 ? 'positive' : 'negative'; ?>">
                            <i class="bi bi-arrow-<?php echo $activeUsersChange >= 0 ? 'up' : 'down'; ?>"></i>
                            <span><?php echo $activeUsersChange >= 0 ? '+' : ''; ?><?php echo $activeUsersChange; ?>% from last month</span>
                        </div>
                    </div>
                </div>
                
                <!-- Inactive Users -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stats-header">
                            <span class="stats-label">Inactive Users</span>
                            <i class="bi bi-person-x stats-icon"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($inactiveUsers); ?></div>
                        <div class="stats-change <?php echo $inactiveUsersChange >= 0 ? 'positive' : 'negative'; ?>">
                            <i class="bi bi-arrow-<?php echo $inactiveUsersChange >= 0 ? 'up' : 'down'; ?>"></i>
                            <span><?php echo $inactiveUsersChange >= 0 ? '+' : ''; ?><?php echo $inactiveUsersChange; ?>% from last month</span>
                        </div>
                    </div>
                </div>
                
                <!-- Activity Rate -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stats-header">
                            <span class="stats-label">Activity Rate</span>
                            <i class="bi bi-graph-up stats-icon"></i>
                        </div>
                        <div class="stats-value"><?php echo $activityRate; ?>%</div>
                        <div class="stats-change <?php echo $activityRateChange >= 0 ? 'positive' : 'negative'; ?>">
                            <i class="bi bi-arrow-<?php echo $activityRateChange >= 0 ? 'up' : 'down'; ?>"></i>
                            <span><?php echo $activityRateChange >= 0 ? '+' : ''; ?><?php echo $activityRateChange; ?>% from last month</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Business Metrics Row -->
            <div class="row g-4 mb-4">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stats-header">
                            <span class="stats-label">Conversion Rate</span>
                            <i class="bi bi-graph-up-arrow stats-icon"></i>
                        </div>
                        <div class="stats-value"><?php echo $conversionRate; ?>%</div>
                        <div class="stats-change">
                            <span><?php echo $conversionStats['converted_orders']; ?> / <?php echo $conversionStats['total_visitors']; ?> users converted</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stats-header">
                            <span class="stats-label">Avg Order Value</span>
                            <i class="bi bi-cash-stack stats-icon"></i>
                        </div>
                        <div class="stats-value"><?php echo formatCurrency($avgOrderValue); ?></div>
                        <div class="stats-change">
                            <span>Per order average</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stats-header">
                            <span class="stats-label">Customer LTV</span>
                            <i class="bi bi-person-hearts stats-icon"></i>
                        </div>
                        <div class="stats-value"><?php echo formatCurrency($customerLifetimeValue); ?></div>
                        <div class="stats-change">
                            <span>Lifetime value per customer</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stats-header">
                            <span class="stats-label">Paying Customers</span>
                            <i class="bi bi-people-fill stats-icon"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($cltvStats['total_customers']); ?></div>
                        <div class="stats-change">
                            <span>Customers with paid orders</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content Row -->
            <div class="row g-4 mb-4">
                <!-- Revenue Cards -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stats-header">
                            <span class="stats-label">Total Revenue</span>
                            <i class="bi bi-currency-rupee stats-icon"></i>
                        </div>
                        <div class="stats-value"><?php echo formatCurrency($revenueStats['total_revenue']); ?></div>
                        <div class="stats-change">
                            <span>All time earnings</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stats-header">
                            <span class="stats-label">This Month</span>
                            <i class="bi bi-calendar-month stats-icon"></i>
                        </div>
                        <div class="stats-value"><?php echo formatCurrency($revenueStats['month_revenue']); ?></div>
                        <div class="stats-change <?php echo $revenueChange >= 0 ? 'positive' : 'negative'; ?>">
                            <i class="bi bi-arrow-<?php echo $revenueChange >= 0 ? 'up' : 'down'; ?>"></i>
                            <span><?php echo $revenueChange >= 0 ? '+' : ''; ?><?php echo $revenueChange; ?>% from last month</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stats-header">
                            <span class="stats-label">Today</span>
                            <i class="bi bi-calendar-day stats-icon"></i>
                        </div>
                        <div class="stats-value"><?php echo formatCurrency($revenueStats['today_revenue']); ?></div>
                        <div class="stats-change">
                            <span>Today's earnings</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stats-header">
                            <span class="stats-label">Total Orders</span>
                            <i class="bi bi-cart-check stats-icon"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($ordersStats['total_orders']); ?></div>
                        <div class="stats-change positive">
                            <span><?php echo $ordersStats['paid_orders']; ?> paid</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Orders Row -->
            <div class="row g-4 mb-4">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stats-header">
                            <span class="stats-label">Active Orders</span>
                            <i class="bi bi-check-circle stats-icon"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($ordersStats['active_orders']); ?></div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stats-header">
                            <span class="stats-label">Pending Orders</span>
                            <i class="bi bi-clock-history stats-icon"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($ordersStats['pending_orders']); ?></div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stats-header">
                            <span class="stats-label">Expired Orders</span>
                            <i class="bi bi-x-circle stats-icon"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($ordersStats['expired_orders']); ?></div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stats-header">
                            <span class="stats-label">Paid Orders</span>
                            <i class="bi bi-credit-card stats-icon"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($ordersStats['paid_orders']); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Content Row -->
            <div class="row g-4">
                <!-- Recent Orders -->
                <div class="col-12 col-lg-8">
                    <div class="content-card">
                        <h2 class="card-title">Recent Orders</h2>
                        <p class="card-subtitle">Latest customer orders</p>
                        
                        <div class="table-responsive">
                            <table class="user-table">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Customer</th>
                                        <th>Package</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recentOrders)): ?>
                                        <?php foreach ($recentOrders as $order): ?>
                                            <tr>
                                                <td>
                                                    <span class="user-name">#<?php echo htmlspecialchars($order['order_number']); ?></span>
                                                </td>
                                                <td>
                                                    <div>
                                                        <span class="user-name"><?php echo htmlspecialchars($order['user_name']); ?></span>
                                                        <span class="user-email"><?php echo htmlspecialchars($order['user_email']); ?></span>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($order['package_name']); ?></td>
                                                <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                                <td>
                                                    <span class="status-badge <?php echo $order['payment_status']; ?>">
                                                        <?php echo ucfirst($order['payment_status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center text-muted">No orders yet</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Popular Packages & Recent Users -->
                <div class="col-12 col-lg-4">
                    <!-- Popular Packages -->
                    <div class="content-card mb-4">
                        <h2 class="card-title">Popular Packages</h2>
                        <p class="card-subtitle">Best selling packages</p>
                        
                        <div class="status-list">
                            <?php if (!empty($popularPackages)): ?>
                                <?php foreach ($popularPackages as $package): ?>
                                    <div class="status-item">
                                        <div>
                                            <span class="status-label"><?php echo htmlspecialchars($package['package_name']); ?></span>
                                            <span class="user-email"><?php echo $package['order_count']; ?> orders</span>
                                        </div>
                                        <span class="status-value"><?php echo formatCurrency($package['revenue']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No packages ordered yet</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Payment Methods -->
                    <div class="content-card mb-4">
                        <h2 class="card-title">Payment Methods</h2>
                        <p class="card-subtitle">Revenue by payment method</p>
                        
                        <div class="status-list">
                            <?php if (!empty($paymentMethods)): ?>
                                <?php foreach ($paymentMethods as $method): ?>
                                    <div class="status-item">
                                        <div>
                                            <span class="status-label"><?php echo htmlspecialchars(ucfirst($method['payment_method'] ?? 'Unknown')); ?></span>
                                            <span class="user-email"><?php echo $method['count']; ?> transactions</span>
                                        </div>
                                        <span class="status-value"><?php echo formatCurrency($method['revenue']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No payments yet</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- System Status -->
                    <div class="content-card">
                        <h2 class="card-title">System Status</h2>
                        <p class="card-subtitle">Current system metrics</p>
                        
                        <div class="status-list">
                            <div class="status-item">
                                <span class="status-label">Server Status</span>
                                <span class="status-badge online">Online</span>
                            </div>
                            
                            <div class="status-item">
                                <span class="status-label">Database</span>
                                <span class="status-badge healthy">Healthy</span>
                            </div>
                            
                            <div class="status-item">
                                <span class="status-label">Auto Cleanup</span>
                                <span class="status-badge <?php echo $totalCleaned > 0 ? 'warning' : 'healthy'; ?>">
                                    <?php echo $totalCleaned > 0 ? 'Cleaned ' . $totalCleaned : 'Active'; ?>
                                </span>
                            </div>
                            
                            <div class="status-item">
                                <span class="status-label">Total Users</span>
                                <span class="status-value"><?php echo number_format($totalUsers); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Users -->
            <div class="row g-4 mt-4">
                <div class="col-12">
                    <div class="content-card">
                        <h2 class="card-title">Recent Users</h2>
                        <p class="card-subtitle">Latest registered users</p>
                        
                        <div class="activity-list">
                            <?php if (!empty($recentUsers)): ?>
                                <?php foreach ($recentUsers as $user): ?>
                                    <div class="activity-item">
                                        <div class="activity-dot"></div>
                                        <div class="activity-content">
                                            <div class="activity-user">
                                                <?php echo htmlspecialchars($user['name']); ?>
                                                <span class="user-email"><?php echo htmlspecialchars($user['email']); ?></span>
                                            </div>
                                            <div class="activity-time"><?php echo timeAgo($user['created_at']); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No users registered yet</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        
<?php include 'includes/footer.php'; ?>
