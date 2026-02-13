<?php
require_once 'includes/header.php';
require_once '../components/user_helper.php';
require_once '../components/hosting_helper.php';
require_once '../components/settings_helper.php';

// Get user's hosting statistics using helper
$userId = $_SESSION['user_id'];
$stats = getUserHostingStats($conn, $userId);
$totalOrders = $stats['total_orders'];
$activeOrders = $stats['active_orders'];
$totalWebsites = $stats['active_websites'];
$pendingOrders = $stats['pending_orders'];

// Total spent (paid orders only)
$totalSpent = 0;
$stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM hosting_orders WHERE user_id = ? AND payment_status = 'paid'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if ($row) {
    $totalSpent = (float) $row['total'];
}
$stmt->close();

// Next renewal (earliest expiry among active orders)
$nextRenewal = null;
$stmt = $conn->prepare("SELECT MIN(expiry_date) as next_expiry FROM hosting_orders WHERE user_id = ? AND order_status = 'active' AND payment_status = 'paid' AND expiry_date >= CURDATE()");
$stmt->bind_param("i", $userId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if ($row && !empty($row['next_expiry'])) {
    $nextRenewal = $row['next_expiry'];
}
$stmt->close();

$supportEmail = getSetting($conn, 'company_email', 'hi@infralabs.in');

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

// If user has no active orders, get available packages
$availablePackages = [];
if ($activeOrders == 0) {
    $availablePackages = getActivePackages($conn);
}

$pageTitle = "Dashboard";
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Welcome back! Here's an overview of your hosting services.</p>
</div>

<?php if ($activeOrders == 0 && !empty($availablePackages)): ?>
<!-- No Active Plan - Show Available Packages -->
<div class="row mb-4">
    <div class="col-12">
        <div class="content-card text-center mb-4" style="padding: 32px;">
            <i class="bi bi-server" style="font-size: 48px; color: #5B5FED; margin-bottom: 12px;"></i>
            <h3 style="margin-bottom: 8px; font-weight: 700;">Get Started with Hosting</h3>
            <p style="color: #6b7280; margin-bottom: 8px;">You don't have any active hosting plans yet. Browse our packages below and get started.</p>
            <p style="font-size: 13px; color: #9ca3af;">Need help? Contact support at <a href="mailto:<?php echo htmlspecialchars($supportEmail); ?>"><?php echo htmlspecialchars($supportEmail); ?></a></p>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 4px;">Available Hosting Plans</h2>
        <p style="color: #6b7280; font-size: 14px;">Choose a plan that suits your needs</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <?php foreach ($availablePackages as $pkg): ?>
        <?php if (isset($pkg['is_private']) && $pkg['is_private']) continue; ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="content-card h-100" style="padding: 28px; border: <?php echo !empty($pkg['is_popular']) ? '2px solid #5B5FED' : '1px solid #e5e7eb'; ?>; position: relative;">
                <?php if (!empty($pkg['is_popular'])): ?>
                    <span style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #5B5FED; color: #fff; font-size: 12px; font-weight: 600; padding: 4px 16px; border-radius: 20px;">Popular</span>
                <?php endif; ?>
                
                <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 6px;"><?php echo htmlspecialchars($pkg['name']); ?></h3>
                <p style="color: #6b7280; font-size: 14px; margin-bottom: 16px; min-height: 40px;">
                    <?php echo htmlspecialchars($pkg['short_description'] ?? $pkg['description']); ?>
                </p>
                
                <!-- Pricing -->
                <div style="margin-bottom: 20px;">
                    <?php
                    $displayPrice = null;
                    $perMonth = null;
                    if (!empty($pkg['price_yearly']) && $pkg['price_yearly'] > 0) {
                        $displayPrice = $pkg['price_yearly'];
                        $perMonth = $pkg['price_yearly'] / 12;
                    } elseif (!empty($pkg['price_monthly']) && $pkg['price_monthly'] > 0) {
                        $displayPrice = $pkg['price_monthly'];
                        $perMonth = $pkg['price_monthly'];
                    } elseif (!empty($pkg['price_2years']) && $pkg['price_2years'] > 0) {
                        $displayPrice = $pkg['price_2years'];
                        $perMonth = $pkg['price_2years'] / 24;
                    }
                    ?>
                    <?php if ($perMonth): ?>
                        <div style="font-size: 32px; font-weight: 800; color: #5B5FED; line-height: 1;">
                            ₹<?php echo number_format($perMonth, 0); ?><span style="font-size: 15px; font-weight: 500; color: #6b7280;">/mo</span>
                        </div>
                        <?php if ($displayPrice != $perMonth): ?>
                            <div style="font-size: 13px; color: #9ca3af; margin-top: 4px;">
                                ₹<?php echo number_format($displayPrice, 0); ?> billed yearly
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div style="font-size: 18px; font-weight: 600; color: #6b7280;">Contact for pricing</div>
                    <?php endif; ?>
                </div>
                
                <!-- Features -->
                <?php if (!empty($pkg['features'])): ?>
                    <ul style="list-style: none; padding: 0; margin: 0 0 20px 0;">
                        <?php 
                        $features = array_filter(array_map('trim', explode("\n", $pkg['features'])));
                        $displayFeatures = array_slice($features, 0, 5);
                        foreach ($displayFeatures as $feature): 
                        ?>
                            <li style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-size: 14px; color: #374151;">
                                <i class="bi bi-check-circle-fill" style="color: #10B981; flex-shrink: 0;"></i>
                                <?php echo htmlspecialchars($feature); ?>
                            </li>
                        <?php endforeach; ?>
                        <?php if (count($features) > 5): ?>
                            <li style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #9ca3af;">
                                <i class="bi bi-plus-circle" style="flex-shrink: 0;"></i>
                                +<?php echo count($features) - 5; ?> more features
                            </li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
                
                <a href="../select-package.php?package=<?php echo urlencode($pkg['slug']); ?>&cycle=yearly" 
                   class="btn btn-<?php echo !empty($pkg['is_popular']) ? 'primary' : 'outline-primary'; ?> w-100" 
                   style="padding: 12px; font-weight: 600; border-radius: 10px;">
                    <i class="bi bi-cart-plus me-1"></i> Get Started
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php else: ?>
<!-- Stats Cards (shown when user has orders) -->
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
    <!-- <div class="col-12 col-sm-6 col-lg-3">
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
    </div> -->
    
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

    <!-- Total Spent -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Total Spent</span>
                <i class="bi bi-currency-rupee stats-icon"></i>
            </div>
            <div class="stats-value">₹<?php echo number_format($totalSpent, 2); ?></div>
            <div class="stats-change positive">
                <span>Paid orders total</span>
            </div>
        </div>
    </div>

    <?php if ($nextRenewal): ?>
    <!-- Next Renewal -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Next Renewal</span>
                <i class="bi bi-calendar-event stats-icon"></i>
            </div>
            <div class="stats-value" style="font-size: 1.1rem;"><?php echo date('M d, Y', strtotime($nextRenewal)); ?></div>
            <div class="stats-change positive">
                <span>Earliest expiring plan</span>
            </div>
        </div>
    </div>
    <?php endif; ?>
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
                                <th>Expires</th>
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
                                    <td>
                                        <?php if (!empty($order['expiry_date'])): ?>
                                            <span class="user-email"><?php echo date('M d, Y', strtotime($order['expiry_date'])); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
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
            <p class="text-muted small mb-3">Need help? Contact support at <a href="mailto:<?php echo htmlspecialchars($supportEmail); ?>"><?php echo htmlspecialchars($supportEmail); ?></a></p>
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
                <!-- <div class="col-12 col-sm-6 col-md-3">
                    <a href="websites.php" class="btn btn-secondary w-100">
                        <i class="bi bi-globe me-2"></i>
                        Manage Websites
                    </a>
                </div> -->
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
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
