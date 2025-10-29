<?php
require_once '../config.php';
require_once '../components/auth_helper.php';
require_once '../components/admin_helper.php';
require_once '../components/hosting_helper.php';
require_once '../components/flash_message.php';

// Require admin access
requireAdmin();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Clear any output buffer and start fresh
    if (ob_get_level()) {
        ob_end_clean();
    }
    ob_start();
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    
    switch ($action) {
        case 'create_website':
            try {
                // Validate required fields
                if (empty($_POST['user_id']) || empty($_POST['website_name']) || empty($_POST['domain_name'])) {
                    echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
                    break;
                }
                
                $data = [
                    'order_id' => null,
                    'user_id' => intval($_POST['user_id']),
                    'package_id' => null,
                    'website_name' => sanitizeInput($_POST['website_name']),
                    'domain_name' => sanitizeInput($_POST['domain_name']),
                    'website_url' => sanitizeInput($_POST['website_url'] ?? ''),
                    'ssh_username' => sanitizeInput($_POST['ssh_username'] ?? ''),
                    'ssh_password' => sanitizeInput($_POST['ssh_password'] ?? ''),
                    'ssh_host' => sanitizeInput($_POST['ssh_host'] ?? ''),
                    'ssh_port' => intval($_POST['ssh_port'] ?? 22),
                    'db_name' => sanitizeInput($_POST['db_name'] ?? ''),
                    'db_username' => sanitizeInput($_POST['db_username'] ?? ''),
                    'db_password' => sanitizeInput($_POST['db_password'] ?? ''),
                    'db_host' => sanitizeInput($_POST['db_host'] ?? 'localhost'),
                    'ftp_username' => sanitizeInput($_POST['ftp_username'] ?? ''),
                    'ftp_password' => sanitizeInput($_POST['ftp_password'] ?? ''),
                    'ftp_host' => sanitizeInput($_POST['ftp_host'] ?? ''),
                    'ftp_port' => intval($_POST['ftp_port'] ?? 21),
                    'cpanel_url' => sanitizeInput($_POST['cpanel_url'] ?? ''),
                    'cpanel_username' => sanitizeInput($_POST['cpanel_username'] ?? ''),
                    'cpanel_password' => sanitizeInput($_POST['cpanel_password'] ?? ''),
                    'status' => $_POST['status'] ?? 'active',
                    'payment_status' => 'paid',
                    'server_ip' => sanitizeInput($_POST['server_ip'] ?? ''),
                    'nameservers' => sanitizeInput($_POST['nameservers'] ?? ''),
                    'notes' => '',
                    'admin_notes' => ''
                ];
                
                $websiteId = createWebsite($conn, $data);
                if ($websiteId) {
                    echo json_encode(['success' => true, 'message' => 'Website created successfully', 'website_id' => $websiteId]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to create website. Please check your data.']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            break;
            
        case 'assign_package':
            $userId = intval($_POST['user_id']);
            $packageId = intval($_POST['package_id']);
            $billingCycle = $_POST['billing_cycle'] ?? 'monthly';
            
            $result = manuallyAssignPackage($conn, $userId, $packageId, $billingCycle);
            echo json_encode($result);
            break;
            
        case 'get_website':
            $websiteId = intval($_POST['website_id']);
            $website = getWebsiteById($conn, $websiteId);
            if ($website) {
                echo json_encode(['success' => true, 'website' => $website]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Website not found']);
            }
            break;
            
        case 'toggle_status':
            $websiteId = intval($_POST['website_id']);
            $status = $_POST['status'];
            $success = toggleWebsiteStatus($conn, $websiteId, $status);
            echo json_encode(['success' => $success, 'message' => $success ? 'Status updated successfully' : 'Failed to update status']);
            break;
            
        case 'delete_website':
            $websiteId = intval($_POST['website_id']);
            $success = deleteWebsite($conn, $websiteId);
            echo json_encode(['success' => $success, 'message' => $success ? 'Website deleted successfully' : 'Failed to delete website']);
            break;
            
        case 'cleanup_pending':
            try {
                $removeAll = isset($_POST['remove_all']) ? (bool)$_POST['remove_all'] : false;
                $cleanupResult = runAutomaticCleanup($conn, $removeAll);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Cleanup completed successfully',
                    'details' => $cleanupResult
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Cleanup failed: ' . $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

// Get all websites
$websites = getAllWebsites($conn, $search, $statusFilter);

// Get users and packages for dropdowns
$allUsers = getAllUsers($conn);
$allPackages = getActivePackages($conn);

$pageTitle = "Websites Management";
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header-row">
    <div>
        <h1 class="page-title">Websites Management</h1>
        <p class="page-subtitle">Manage client websites and access credentials</p>
    </div>
    <button class="btn-add-user" onclick="openAssignPackageModal()">
        <i class="bi bi-plus-circle-fill"></i>
        Assign Package
    </button>
    <button class="btn-add-user" onclick="openAddWebsiteModal()" style="margin-left: 10px;">
        <i class="bi bi-server"></i>
        Add Website
    </button>
    <button class="btn btn-warning" onclick="cleanupPending()" style="margin-left: 10px;">
        <i class="bi bi-trash"></i>
        Cleanup Pending
    </button>
</div>

<!-- Flash Message -->
<div id="flashMessage"></div>

<!-- Cleanup Status -->
<div id="cleanupStatus" class="alert alert-info" style="display: none;">
    <i class="bi bi-info-circle"></i>
    <span id="cleanupStatusText">Running automatic cleanup...</span>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <?php 
    $activeWebsites = count(array_filter($websites, function($w) { return $w['status'] === 'active'; }));
    $suspendedWebsites = count(array_filter($websites, function($w) { return $w['status'] === 'suspended'; }));
    ?>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Total Websites</span>
                <i class="bi bi-globe stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo count($websites); ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Active</span>
                <i class="bi bi-check-circle stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo $activeWebsites; ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Suspended</span>
                <i class="bi bi-pause-circle stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo $suspendedWebsites; ?></div>
        </div>
    </div>
</div>

<!-- Websites Card -->
<div class="content-card">
    <!-- Search and Filter -->
    <div class="search-container mb-3">
        <form method="GET" id="searchForm">
            <i class="bi bi-search search-icon"></i>
            <input type="text" 
                   class="search-input" 
                   name="search" 
                   placeholder="Search websites..." 
                   value="<?php echo htmlspecialchars($search); ?>">
            <select name="status" class="form-select" style="width: auto; margin-left: 10px;">
                <option value="">All Status</option>
                <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="suspended" <?php echo $statusFilter === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
            </select>
            <button type="submit" class="btn btn-primary" style="margin-left: 10px;">Filter</button>
        </form>
    </div>
    
    <!-- Websites Table -->
    <div class="table-responsive">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Website Name</th>
                    <th>Domain</th>
                    <th>Customer</th>
                    <th>Package</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($websites)): ?>
                    <?php foreach ($websites as $website): ?>
                        <tr data-website-id="<?php echo $website['id']; ?>">
                            <td>
                                <div class="user-name"><?php echo htmlspecialchars($website['website_name']); ?></div>
                                <?php if ($website['website_url']): ?>
                                    <small class="text-muted"><a href="<?php echo htmlspecialchars($website['website_url']); ?>" target="_blank"><?php echo htmlspecialchars($website['website_url']); ?></a></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="user-email"><?php echo htmlspecialchars($website['domain_name']); ?></div>
                            </td>
                            <td>
                                <div class="user-name"><?php echo htmlspecialchars($website['user_name']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($website['user_email']); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo htmlspecialchars($website['package_name']); ?></span>
                            </td>
                            <td>
                                <span class="status-badge-table <?php echo $website['status']; ?>">
                                    <?php echo ucfirst($website['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge-table <?php echo $website['payment_status'] === 'paid' ? 'active' : ($website['payment_status'] === 'overdue' ? 'inactive' : 'inactive'); ?>">
                                    <?php echo ucfirst($website['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn-action" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); viewWebsiteDetails(<?php echo $website['id']; ?>)">
                                            <i class="bi bi-eye-fill"></i> View Details
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); editWebsite(<?php echo $website['id']; ?>)">
                                            <i class="bi bi-pencil-fill"></i> Edit
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); toggleWebsiteStatus(<?php echo $website['id']; ?>, '<?php echo $website['status'] === 'active' ? 'suspended' : 'active'; ?>')">
                                            <i class="bi bi-<?php echo $website['status'] === 'active' ? 'pause' : 'play'; ?>-fill"></i> 
                                            <?php echo $website['status'] === 'active' ? 'Suspend' : 'Activate'; ?>
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); deleteWebsiteAction(<?php echo $website['id']; ?>)">
                                            <i class="bi bi-trash-fill"></i> Delete
                                        </a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No websites found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Assign Package Modal -->
<div class="modal fade" id="assignPackageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manually Assign Package</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignPackageForm">
                    <div class="mb-3">
                        <label class="form-label">Select User *</label>
                        <select class="form-select" id="assign_user_id" name="user_id" required>
                            <option value="">Choose user...</option>
                            <?php foreach ($allUsers as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Select Package *</label>
                        <select class="form-select" id="assign_package_id" name="package_id" required>
                            <option value="">Choose package...</option>
                            <?php foreach ($allPackages as $package): ?>
                                <option value="<?php echo $package['id']; ?>">
                                    <?php echo htmlspecialchars($package['name']); ?> - ₹<?php echo $package['price_monthly']; ?>/month
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Billing Cycle *</label>
                        <select class="form-select" id="assign_billing_cycle" name="billing_cycle" required>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                            <option value="2years">2 Years</option>
                            <option value="4years">4 Years</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="assignPackage()">Assign Package</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Website Modal -->
<div class="modal fade" id="addWebsiteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Website</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addWebsiteForm">
                    <!-- Customer Information -->
                    <h6 class="mb-3">Customer Information</h6>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Select Customer *</label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">Choose customer...</option>
                                <?php foreach ($allUsers as $user): ?>
                                    <option value="<?php echo $user['id']; ?>">
                                        <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="mb-3">Website Details</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Website Name *</label>
                            <input type="text" class="form-control" id="website_name" name="website_name" placeholder="My Website" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Domain Name *</label>
                            <input type="text" class="form-control" id="domain_name" name="domain_name" placeholder="example.com" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Website URL</label>
                            <input type="url" class="form-control" id="website_url" name="website_url" placeholder="https://www.example.com">
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="mb-3">SSH Access</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">SSH Username</label>
                            <input type="text" class="form-control" id="ssh_username" name="ssh_username" placeholder="username">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">SSH Password</label>
                            <input type="password" class="form-control" id="ssh_password" name="ssh_password" placeholder="••••••••">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">SSH Host</label>
                            <input type="text" class="form-control" id="ssh_host" name="ssh_host" placeholder="ssh.example.com">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">SSH Port</label>
                            <input type="number" class="form-control" id="ssh_port" name="ssh_port" value="22">
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="mb-3">Database Access</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Database Name</label>
                            <input type="text" class="form-control" id="db_name" name="db_name" placeholder="mydb">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">DB Username</label>
                            <input type="text" class="form-control" id="db_username" name="db_username" placeholder="dbuser">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">DB Password</label>
                            <input type="password" class="form-control" id="db_password" name="db_password" placeholder="••••••••">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Database Host</label>
                            <input type="text" class="form-control" id="db_host" name="db_host" value="localhost">
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="mb-3">FTP Access</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">FTP Username</label>
                            <input type="text" class="form-control" id="ftp_username" name="ftp_username" placeholder="ftpuser">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">FTP Password</label>
                            <input type="password" class="form-control" id="ftp_password" name="ftp_password" placeholder="••••••••">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">FTP Host</label>
                            <input type="text" class="form-control" id="ftp_host" name="ftp_host" placeholder="ftp.example.com">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">FTP Port</label>
                            <input type="number" class="form-control" id="ftp_port" name="ftp_port" value="21">
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="mb-3">Control Panel Access</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">cPanel URL</label>
                            <input type="url" class="form-control" id="cpanel_url" name="cpanel_url" placeholder="https://cpanel.example.com:2083">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">cPanel Username</label>
                            <input type="text" class="form-control" id="cpanel_username" name="cpanel_username" placeholder="cpanel_user">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">cPanel Password</label>
                            <input type="password" class="form-control" id="cpanel_password" name="cpanel_password" placeholder="••••••••">
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="mb-3">Server Information</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Server IP</label>
                            <input type="text" class="form-control" id="server_ip" name="server_ip" placeholder="192.168.1.1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nameservers</label>
                            <input type="text" class="form-control" id="nameservers" name="nameservers" placeholder="ns1.example.com, ns2.example.com">
                            <small class="text-muted">Separate multiple nameservers with commas</small>
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="mb-3">Status</h6>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Website Status *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveWebsite()">Add Website</button>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-fill user and package when order is selected
document.addEventListener('DOMContentLoaded', function() {
    const orderSelect = document.getElementById('order_id');
    const userSelect = document.getElementById('user_id');
    const packageSelect = document.getElementById('package_id');
    
    if (orderSelect) {
        orderSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                // Extract user ID and package ID from the order data
                // This would need to be implemented with proper data attributes
                // For now, keep the existing selects functional
            }
        });
    }
});

// Open assign package modal
function openAssignPackageModal() {
    document.getElementById('assignPackageForm').reset();
    const modal = new bootstrap.Modal(document.getElementById('assignPackageModal'));
    modal.show();
}

// Assign package
function assignPackage() {
    const formData = new FormData(document.getElementById('assignPackageForm'));
    formData.append('action', 'assign_package');
    
    fetch('websites.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        showFlash(data.success ? 'success' : 'error', data.message);
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('assignPackageModal')).hide();
            setTimeout(() => location.reload(), 1500);
        }
    });
}

// Cleanup pending orders and payments
function cleanupPending() {
    if (!confirm('This will remove ALL pending orders and payments immediately. Continue?')) {
        return;
    }
    
    // Show status indicator
    const statusDiv = document.getElementById('cleanupStatus');
    const statusText = document.getElementById('cleanupStatusText');
    statusDiv.style.display = 'block';
    statusText.textContent = 'Running cleanup...';
    
    fetch('websites.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=cleanup_pending&remove_all=1'
    })
    .then(res => res.json())
    .then(data => {
        statusDiv.style.display = 'none';
        
        if (data.success) {
            showFlash('success', data.message);
            if (data.details) {
                console.log('Cleanup details:', data.details);
                // Show detailed cleanup results
                const message = `Cleanup completed: ${data.details.orders.deleted_count} orders and ${data.details.payments.deleted_count} payments removed`;
                showFlash('info', message);
            }
            setTimeout(() => location.reload(), 2000);
        } else {
            showFlash('error', data.message);
        }
    })
    .catch(error => {
        statusDiv.style.display = 'none';
        console.error('Cleanup error:', error);
        showFlash('error', 'An error occurred during cleanup');
    });
}

// Auto cleanup on page load (check every 5 minutes)
let lastCleanupCheck = 0;
const CLEANUP_CHECK_INTERVAL = 5 * 60 * 1000; // 5 minutes

function checkAutoCleanup() {
    const now = Date.now();
    if (now - lastCleanupCheck > CLEANUP_CHECK_INTERVAL) {
        lastCleanupCheck = now;
        
        // Show brief status for auto cleanup
        const statusDiv = document.getElementById('cleanupStatus');
        const statusText = document.getElementById('cleanupStatusText');
        statusDiv.style.display = 'block';
        statusText.textContent = 'Running automatic cleanup...';
        
        // Run cleanup silently in background
        fetch('websites.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=cleanup_pending'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.details) {
                const totalCleaned = data.details.orders.deleted_count + data.details.payments.deleted_count;
                if (totalCleaned > 0) {
                    console.log(`Auto cleanup: ${totalCleaned} pending records removed`);
                    statusText.textContent = `Auto cleanup completed: ${totalCleaned} records removed`;
                    setTimeout(() => {
                        statusDiv.style.display = 'none';
                    }, 3000);
                } else {
                    statusDiv.style.display = 'none';
                }
            } else {
                statusDiv.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Auto cleanup error:', error);
            statusDiv.style.display = 'none';
        });
    }
}

// Start auto cleanup check
setInterval(checkAutoCleanup, CLEANUP_CHECK_INTERVAL);

// Placeholder functions (to be implemented)
function viewWebsiteDetails(websiteId) {
    alert('View details for website #' + websiteId);
}

function editWebsite(websiteId) {
    alert('Edit website #' + websiteId);
}

function toggleWebsiteStatus(websiteId, status) {
    if (!confirm('Are you sure you want to change this website\'s status?')) return;
    
    fetch('websites.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=toggle_status&website_id=${websiteId}&status=${status}`
    })
    .then(res => res.json())
    .then(data => {
        showFlash(data.success ? 'success' : 'error', data.message);
        if (data.success) setTimeout(() => location.reload(), 1500);
    });
}

function deleteWebsiteAction(websiteId) {
    if (!confirm('Are you sure you want to delete this website?')) return;
    
    fetch('websites.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete_website&website_id=${websiteId}`
    })
    .then(res => res.json())
    .then(data => {
        showFlash(data.success ? 'success' : 'error', data.message);
        if (data.success) setTimeout(() => location.reload(), 1500);
    });
}

// Open add website modal
function openAddWebsiteModal() {
    document.getElementById('addWebsiteForm').reset();
    // Set default values
    document.getElementById('ssh_port').value = 22;
    document.getElementById('ftp_port').value = 21;
    document.getElementById('db_host').value = 'localhost';
    const modal = new bootstrap.Modal(document.getElementById('addWebsiteModal'));
    modal.show();
}

// Save website
function saveWebsite() {
    const formData = new FormData(document.getElementById('addWebsiteForm'));
    formData.append('action', 'create_website');
    
    fetch('websites.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        showFlash(data.success ? 'success' : 'error', data.message);
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addWebsiteModal')).hide();
            setTimeout(() => location.reload(), 1500);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showFlash('error', 'An error occurred while creating website');
    });
}

function showFlash(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';
    
    document.getElementById('flashMessage').innerHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="bi ${icon}"></i> <span>${message}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) alert.remove();
    }, 5000);
}
</script>

<?php include 'includes/footer.php'; ?>

