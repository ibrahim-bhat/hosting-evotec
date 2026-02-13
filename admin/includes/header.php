<?php
// Include config and database connection if not already included
if (!isset($conn)) {
    require_once __DIR__ . '/../../config.php';
}

// Include auth helpers
require_once __DIR__ . '/../../components/auth_helper.php';
require_once __DIR__ . '/../../components/admin_helper.php';

// Include settings helper if not already included
if (!function_exists('getSetting')) {
    require_once __DIR__ . '/../../components/settings_helper.php';
}

// Get system settings
$companyName = getCompanyName($conn);
$companyLogo = getCompanyLogo($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . htmlspecialchars($companyName) : htmlspecialchars($companyName); ?></title>
    
    <!-- Favicon (using company logo) -->
    <?php if (!empty($companyLogo)): ?>
        <link rel="icon" type="image/x-icon" href="../<?php echo htmlspecialchars($companyLogo); ?>">
    <?php endif; ?>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <!-- Logo -->
        <div class="sidebar-header">
            <?php if (!empty($companyLogo)): ?>
                <img src="../<?php echo htmlspecialchars($companyLogo); ?>" alt="<?php echo htmlspecialchars($companyName); ?>" class="sidebar-logo-img">
            <?php else: ?>
                <div class="logo-box-admin">
                    <span class="logo-text-admin"><?php echo strtoupper(substr($companyName, 0, 1)); ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Menu Title -->
        <div class="menu-title">Menu</div>
        
        <!-- Navigation Menu -->
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                <i class="bi bi-grid-fill"></i>
                <span>Dashboard</span>
            </a>
            <a href="users.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active' : ''; ?>">
                <i class="bi bi-people-fill"></i>
                <span>User Management</span>
            </a>
            <a href="packages.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'packages.php') ? 'active' : ''; ?>">
                <i class="bi bi-server"></i>
                <span>Hosting Packages</span>
            </a>
            <a href="orders.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'orders.php') ? 'active' : ''; ?>">
                <i class="bi bi-cart-fill"></i>
                <span>Orders</span>
            </a>
            <a href="coupons.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'coupons.php') ? 'active' : ''; ?>">
                <i class="bi bi-tag-fill"></i>
                <span>Coupons</span>
            </a>
            <!-- <a href="websites.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'websites.php') ? 'active' : ''; ?>">
                <i class="bi bi-globe"></i>
                <span>Websites</span>
            </a> -->
            <a href="https://server.infralabs.cloud" class="nav-item" target="_blank">
                <i class="bi bi-hdd-network"></i>
                <span>Server Panel</span>
            </a>
            <a href="billing.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'billing.php') ? 'active' : ''; ?>">
                <i class="bi bi-cash-coin"></i>
                <span>Billing</span>
            </a>
            <a href="manual_payments.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'manual_payments.php') ? 'active' : ''; ?>">
                <i class="bi bi-receipt"></i>
                <span>Manual Payments</span>
            </a>
            <a href="settings.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'active' : ''; ?>">
                <i class="bi bi-gear-fill"></i>
                <span>Settings</span>
            </a>
            <a href="profile.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">
                <i class="bi bi-person-fill"></i>
                <span>Profile</span>
            </a>
        </nav>
        
        <!-- Logout -->
        <div class="sidebar-footer">
            <a href="../logout.php" class="nav-item logout">
                <i class="bi bi-box-arrow-left"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Bar with Toggle -->
        <div class="top-bar">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
        </div>
        
        <!-- Dashboard Container -->
        <div class="dashboard-container">
