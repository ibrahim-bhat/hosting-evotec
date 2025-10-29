<?php
require_once '../config.php';
require_once '../components/auth_helper.php';
require_once '../components/admin_helper.php';
require_once '../components/hosting_helper.php';
require_once '../components/settings_helper.php';

// Require admin access
requireAdmin();

// Run automatic cleanup of pending orders (run once per day)
$lastCleanup = getSetting($conn, 'last_cleanup', '');
$today = date('Y-m-d');

if ($lastCleanup !== $today) {
    $cleanupResult = runAutomaticCleanup($conn);
    updateSetting($conn, 'last_cleanup', $today);
}

// Dummy dashboard statistics
$totalUsers = 2543;
$activeUsers = 1892;
$inactiveUsers = 651;
$activityRate = 74.4;

// Dummy percentage changes
$totalUsersChange = 5.2;
$activeUsersChange = 8.1;
$inactiveUsersChange = -1.3;
$activityRateChange = 2.6;

// Dummy recent users
$recentUsers = [
    ['name' => 'User adon1', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))],
    ['name' => 'User adon2', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))],
    ['name' => 'User adon3', 'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours'))],
    ['name' => 'User adon4', 'created_at' => date('Y-m-d H:i:s', strtotime('-4 hours'))]
];

$pageTitle = "Dashboard";
include 'includes/header.php';
?>
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Welcome back! Here's an overview of your system.</p>
            </div>
            
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
            
            <!-- Content Row -->
            <div class="row g-4">
                <!-- Recent Activity -->
                <div class="col-12 col-lg-7">
                    <div class="content-card">
                        <h2 class="card-title">Recent Activity</h2>
                        <p class="card-subtitle">Latest user activities in your system</p>
                        
                        <div class="activity-list">
                            <?php if (!empty($recentUsers)): ?>
                                <?php foreach ($recentUsers as $index => $user): ?>
                                    <div class="activity-item">
                                        <div class="activity-dot"></div>
                                        <div class="activity-content">
                                            <div class="activity-user"><?php echo htmlspecialchars($user['name']); ?></div>
                                            <div class="activity-time"><?php echo timeAgo($user['created_at']); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No recent activity</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- System Status -->
                <div class="col-12 col-lg-5">
                    <div class="content-card">
                        <h2 class="card-title">System Status</h2>
                        <p class="card-subtitle">Current system performance metrics</p>
                        
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
                                <span class="status-label">API Response</span>
                                <span class="status-badge fast">Fast</span>
                            </div>
                            
                            <div class="status-item">
                                <span class="status-label">Uptime</span>
                                <span class="status-value">99.9%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
<?php include 'includes/footer.php'; ?>
