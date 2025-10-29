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
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    
    switch ($action) {
        case 'create_package':
            $data = [
                'name' => sanitizeInput($_POST['name']),
                'slug' => sanitizeInput($_POST['slug']),
                'description' => sanitizeInput($_POST['description']),
                'short_description' => sanitizeInput($_POST['short_description']),
                'price_monthly' => floatval($_POST['price_monthly']),
                'price_yearly' => floatval($_POST['price_yearly']),
                'price_2years' => floatval($_POST['price_2years']),
                'price_4years' => floatval($_POST['price_4years']),
                'storage_gb' => floatval($_POST['storage_gb']),
                'bandwidth_gb' => floatval($_POST['bandwidth_gb']),
                'allowed_websites' => intval($_POST['allowed_websites']),
                'database_limit' => intval($_POST['database_limit']),
                'ftp_accounts' => intval($_POST['ftp_accounts']),
                'email_accounts' => intval($_POST['email_accounts']),
                'ssh_access' => isset($_POST['ssh_access']) ? 1 : 0,
                'ssl_free' => isset($_POST['ssl_free']) ? 1 : 0,
                'daily_backups' => isset($_POST['daily_backups']) ? 1 : 0,
                'dedicated_ip' => isset($_POST['dedicated_ip']) ? 1 : 0,
                'setup_fee' => floatval($_POST['setup_fee']),
                'gst_percentage' => floatval($_POST['gst_percentage']),
                'processing_fee' => floatval($_POST['processing_fee']),
                'status' => $_POST['status'],
                'is_popular' => isset($_POST['is_popular']) ? 1 : 0,
                'sort_order' => intval($_POST['sort_order'])
            ];
            
            if (empty($data['slug'])) {
                $data['slug'] = generateSlug($data['name']);
            }
            
            $success = createPackage($conn, $data);
            echo json_encode(['success' => $success, 'message' => $success ? 'Package created successfully' : 'Failed to create package']);
            break;
            
        case 'get_package':
            $packageId = intval($_POST['package_id']);
            $package = getPackageById($conn, $packageId);
            if ($package) {
                echo json_encode(['success' => true, 'package' => $package]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Package not found']);
            }
            break;
            
        case 'update_package':
            $packageId = intval($_POST['package_id']);
            $data = [
                'name' => sanitizeInput($_POST['name']),
                'slug' => sanitizeInput($_POST['slug']),
                'description' => sanitizeInput($_POST['description']),
                'short_description' => sanitizeInput($_POST['short_description']),
                'price_monthly' => floatval($_POST['price_monthly']),
                'price_yearly' => floatval($_POST['price_yearly']),
                'price_2years' => floatval($_POST['price_2years']),
                'price_4years' => floatval($_POST['price_4years']),
                'storage_gb' => floatval($_POST['storage_gb']),
                'bandwidth_gb' => floatval($_POST['bandwidth_gb']),
                'allowed_websites' => intval($_POST['allowed_websites']),
                'database_limit' => intval($_POST['database_limit']),
                'ftp_accounts' => intval($_POST['ftp_accounts']),
                'email_accounts' => intval($_POST['email_accounts']),
                'ssh_access' => isset($_POST['ssh_access']) ? 1 : 0,
                'ssl_free' => isset($_POST['ssl_free']) ? 1 : 0,
                'daily_backups' => isset($_POST['daily_backups']) ? 1 : 0,
                'dedicated_ip' => isset($_POST['dedicated_ip']) ? 1 : 0,
                'setup_fee' => floatval($_POST['setup_fee']),
                'gst_percentage' => floatval($_POST['gst_percentage']),
                'processing_fee' => floatval($_POST['processing_fee']),
                'status' => $_POST['status'],
                'is_popular' => isset($_POST['is_popular']) ? 1 : 0,
                'sort_order' => intval($_POST['sort_order'])
            ];
            
            $success = updatePackage($conn, $packageId, $data);
            echo json_encode(['success' => $success, 'message' => $success ? 'Package updated successfully' : 'Failed to update package']);
            break;
            
        case 'delete_package':
            $packageId = intval($_POST['package_id']);
            $success = deletePackage($conn, $packageId);
            echo json_encode(['success' => $success, 'message' => $success ? 'Package deleted successfully' : 'Failed to delete package']);
            break;
            
        case 'toggle_status':
            $packageId = intval($_POST['package_id']);
            $status = $_POST['status'];
            $success = togglePackageStatus($conn, $packageId, $status);
            echo json_encode(['success' => $success, 'message' => $success ? 'Status updated successfully' : 'Failed to update status']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

// Get search parameter
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

// Get all packages
$packages = getAllPackages($conn, $statusFilter, $search);

$pageTitle = "Hosting Packages";
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header-row">
    <div>
        <h1 class="page-title">Hosting Packages</h1>
        <p class="page-subtitle">Manage your hosting packages and pricing</p>
    </div>
    <button class="btn-add-user" onclick="openAddPackageModal()">
        <i class="bi bi-plus-circle-fill"></i>
        Add Package
    </button>
</div>

<!-- Flash Message -->
<div id="flashMessage"></div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Total Packages</span>
                <i class="bi bi-server stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo count($packages); ?></div>
        </div>
    </div>
    <?php 
    $activeCount = count(array_filter($packages, function($p) { return $p['status'] === 'active'; }));
    $inactiveCount = count(array_filter($packages, function($p) { return $p['status'] === 'inactive'; }));
    ?>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Active Packages</span>
                <i class="bi bi-check-circle stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo $activeCount; ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Inactive Packages</span>
                <i class="bi bi-x-circle stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo $inactiveCount; ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Popular Packages</span>
                <i class="bi bi-star-fill stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo count(array_filter($packages, function($p) { return $p['is_popular']; })); ?></div>
        </div>
    </div>
</div>

<!-- Packages Card -->
<div class="content-card">
    <!-- Search and Filter -->
    <div class="search-container mb-3">
        <form method="GET" class="search-container" id="searchForm">
            <i class="bi bi-search search-icon"></i>
            <input type="text" 
                   class="search-input" 
                   name="search" 
                   placeholder="Search packages..." 
                   value="<?php echo htmlspecialchars($search); ?>">
            <select name="status" class="form-select" style="width: auto; margin-left: 10px;">
                <option value="">All Status</option>
                <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
            <button type="submit" class="btn btn-primary" style="margin-left: 10px;">Filter</button>
        </form>
    </div>
    
    <!-- Packages Table -->
    <div class="table-responsive">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Short Description</th>
                    <th>Price (Monthly)</th>
                    <th>Storage</th>
                    <th>Bandwidth</th>
                    <th>Features</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($packages)): ?>
                    <?php foreach ($packages as $package): ?>
                        <tr data-package-id="<?php echo $package['id']; ?>">
                            <td>
                                <div class="user-name">
                                    <?php if ($package['is_popular']): ?>
                                        <i class="bi bi-star-fill text-warning"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($package['name']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="user-email"><?php echo htmlspecialchars($package['short_description']); ?></div>
                            </td>
                            <td>
                                <div class="user-email">₹<?php echo number_format($package['price_monthly'], 2); ?></div>
                                <small class="text-muted">₹<?php echo number_format($package['price_yearly']/12, 2); ?>/mo yearly</small>
                            </td>
                            <td>
                                <div class="user-email"><?php echo $package['storage_gb']; ?> GB</div>
                            </td>
                            <td>
                                <div class="user-email"><?php echo $package['bandwidth_gb']; ?> GB</div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="badge bg-secondary"><?php echo $package['allowed_websites']; ?> Sites</span>
                                    <span class="badge bg-secondary"><?php echo $package['database_limit']; ?> DBs</span>
                                    <?php if ($package['ssh_access']): ?>
                                        <span class="badge bg-success">SSH</span>
                                    <?php endif; ?>
                                    <?php if ($package['ssl_free']): ?>
                                        <span class="badge bg-success">SSL</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge-table <?php echo $package['status']; ?>">
                                    <?php echo ucfirst($package['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn-action" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); editPackage(<?php echo $package['id']; ?>)">
                                            <i class="bi bi-pencil-fill"></i> Edit
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); toggleStatus(<?php echo $package['id']; ?>, '<?php echo $package['status'] === 'active' ? 'inactive' : 'active'; ?>')">
                                            <i class="bi bi-toggle-<?php echo $package['status'] === 'active' ? 'off' : 'on'; ?>"></i> 
                                            <?php echo $package['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); deletePackageAction(<?php echo $package['id']; ?>)">
                                            <i class="bi bi-trash-fill"></i> Delete
                                        </a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No packages found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Package Modal -->
<div class="modal fade" id="packageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="packageModalTitle">Add New Package</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="packageForm">
                    <input type="hidden" id="package_id" name="package_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Package Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug">
                            <small class="text-muted">Leave blank to auto-generate</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Short Description *</label>
                        <input type="text" class="form-control" id="short_description" name="short_description" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <hr>
                    <h6 class="mb-3">Pricing (₹)</h6>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Monthly</label>
                            <input type="number" step="0.01" class="form-control" id="price_monthly" name="price_monthly" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Yearly</label>
                            <input type="number" step="0.01" class="form-control" id="price_yearly" name="price_yearly" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">2 Years</label>
                            <input type="number" step="0.01" class="form-control" id="price_2years" name="price_2years" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">4 Years</label>
                            <input type="number" step="0.01" class="form-control" id="price_4years" name="price_4years" required>
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="mb-3">Resources</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Storage (GB)</label>
                            <input type="number" step="0.01" class="form-control" id="storage_gb" name="storage_gb" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bandwidth (GB)</label>
                            <input type="number" step="0.01" class="form-control" id="bandwidth_gb" name="bandwidth_gb" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Allowed Websites</label>
                            <input type="number" class="form-control" id="allowed_websites" name="allowed_websites" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Database Limit</label>
                            <input type="number" class="form-control" id="database_limit" name="database_limit" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email Accounts</label>
                            <input type="number" class="form-control" id="email_accounts" name="email_accounts" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">FTP Accounts</label>
                            <input type="number" class="form-control" id="ftp_accounts" name="ftp_accounts" required>
                        </div>
                    </div>
                    
                    <hr>
             
                    
                    <hr>
                    <h6 class="mb-3">Features</h6>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="ssh_access" name="ssh_access">
                                <label class="form-check-label" for="ssh_access">SSH Access</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="ssl_free" name="ssl_free" checked>
                                <label class="form-check-label" for="ssl_free">Free SSL</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="daily_backups" name="daily_backups">
                                <label class="form-check-label" for="daily_backups">Daily Backups</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="dedicated_ip" name="dedicated_ip">
                                <label class="form-check-label" for="dedicated_ip">Dedicated IP</label>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" id="is_popular" name="is_popular">
                                <label class="form-check-label" for="is_popular">Mark as Popular</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" value="0">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="savePackage()">Save Package</button>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-generate slug
document.getElementById('name').addEventListener('input', function() {
    const slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    document.getElementById('slug').value = slug;
});

// Open add package modal
function openAddPackageModal() {
    document.getElementById('packageModalTitle').textContent = 'Add New Package';
    document.getElementById('packageForm').reset();
    document.getElementById('package_id').value = '';
    const modal = new bootstrap.Modal(document.getElementById('packageModal'));
    modal.show();
}

// Edit package
function editPackage(packageId) {
    fetch('packages.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=get_package&package_id=${packageId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const p = data.package;
            document.getElementById('packageModalTitle').textContent = 'Edit Package';
            document.getElementById('package_id').value = p.id;
            document.getElementById('name').value = p.name;
            document.getElementById('slug').value = p.slug;
            document.getElementById('short_description').value = p.short_description;
            document.getElementById('description').value = p.description || '';
            document.getElementById('price_monthly').value = p.price_monthly;
            document.getElementById('price_yearly').value = p.price_yearly;
            document.getElementById('price_2years').value = p.price_2years;
            document.getElementById('price_4years').value = p.price_4years;
            document.getElementById('storage_gb').value = p.storage_gb;
            document.getElementById('bandwidth_gb').value = p.bandwidth_gb;
            document.getElementById('allowed_websites').value = p.allowed_websites;
            document.getElementById('database_limit').value = p.database_limit;
            document.getElementById('ftp_accounts').value = p.ftp_accounts;
            document.getElementById('email_accounts').value = p.email_accounts;
            document.getElementById('ssh_access').checked = p.ssh_access == 1;
            document.getElementById('ssl_free').checked = p.ssl_free == 1;
            document.getElementById('daily_backups').checked = p.daily_backups == 1;
            document.getElementById('dedicated_ip').checked = p.dedicated_ip == 1;
            document.getElementById('setup_fee').value = p.setup_fee;
            document.getElementById('gst_percentage').value = p.gst_percentage;
            document.getElementById('processing_fee').value = p.processing_fee;
            document.getElementById('status').value = p.status;
            document.getElementById('is_popular').checked = p.is_popular == 1;
            document.getElementById('sort_order').value = p.sort_order;
            
            const modal = new bootstrap.Modal(document.getElementById('packageModal'));
            modal.show();
        }
    });
}

// Save package
function savePackage() {
    const formData = new FormData(document.getElementById('packageForm'));
    const isEdit = document.getElementById('package_id').value !== '';
    formData.append('action', isEdit ? 'update_package' : 'create_package');
    
    fetch('packages.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        showFlash(data.success ? 'success' : 'error', data.message);
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('packageModal')).hide();
            setTimeout(() => location.reload(), 1500);
        }
    });
}

// Delete package
function deletePackageAction(packageId) {
    if (!confirm('Are you sure you want to delete this package?')) return;
    
    fetch('packages.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete_package&package_id=${packageId}`
    })
    .then(res => res.json())
    .then(data => {
        showFlash(data.success ? 'success' : 'error', data.message);
        if (data.success) setTimeout(() => location.reload(), 1500);
    });
}

// Toggle status
function toggleStatus(packageId, status) {
    fetch('packages.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=toggle_status&package_id=${packageId}&status=${status}`
    })
    .then(res => res.json())
    .then(data => {
        showFlash(data.success ? 'success' : 'error', data.message);
        if (data.success) setTimeout(() => location.reload(), 1500);
    });
}

// Show flash message
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

