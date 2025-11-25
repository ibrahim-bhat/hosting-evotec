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
                'features' => sanitizeInput($_POST['features']),
                'price_monthly' => !empty($_POST['price_monthly']) ? floatval($_POST['price_monthly']) : null,
                'price_yearly' => !empty($_POST['price_yearly']) ? floatval($_POST['price_yearly']) : null,
                'price_2years' => !empty($_POST['price_2years']) ? floatval($_POST['price_2years']) : null,
                'price_4years' => !empty($_POST['price_4years']) ? floatval($_POST['price_4years']) : null,
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
                'features' => sanitizeInput($_POST['features']),
                'price_monthly' => !empty($_POST['price_monthly']) ? floatval($_POST['price_monthly']) : null,
                'price_yearly' => !empty($_POST['price_yearly']) ? floatval($_POST['price_yearly']) : null,
                'price_2years' => !empty($_POST['price_2years']) ? floatval($_POST['price_2years']) : null,
                'price_4years' => !empty($_POST['price_4years']) ? floatval($_POST['price_4years']) : null,
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
                    <th>Pricing Available</th>
                    <th>Features Preview</th>
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
                                <div class="d-flex flex-column gap-1">
                                    <?php if (!empty($package['price_monthly'])): ?>
                                        <span class="badge bg-primary">Monthly: ₹<?php echo number_format($package['price_monthly'], 0); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($package['price_yearly'])): ?>
                                        <span class="badge bg-info">Yearly: ₹<?php echo number_format($package['price_yearly'], 0); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($package['price_2years'])): ?>
                                        <span class="badge bg-success">2 Years: ₹<?php echo number_format($package['price_2years'], 0); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($package['price_4years'])): ?>
                                        <span class="badge bg-warning">4 Years: ₹<?php echo number_format($package['price_4years'], 0); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div style="max-width: 200px; font-size: 12px;">
                                    <?php 
                                    $features = isset($package['features']) ? $package['features'] : '';
                                    echo nl2br(htmlspecialchars(substr($features, 0, 100)));
                                    if (strlen($features) > 100) echo '...';
                                    ?>
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
                    
                    <div class="mb-3">
                        <label class="form-label">Features *</label>
                        <textarea class="form-control" id="features" name="features" rows="8" placeholder="Enter each feature on a new line, e.g.:&#10;CloudPanel Pre-installed&#10;Full Root SSH Access&#10;Daily Automated Backups&#10;Free SSL Certificate&#10;DDoS Protection&#10;99.9% Uptime Guarantee&#10;1 vCPU&#10;1GB RAM&#10;25GB SSD Storage&#10;1TB Bandwidth" required></textarea>
                        <small class="text-muted">Enter each feature on a new line. These will be displayed as bullet points.</small>
                    </div>
                    
                    <hr>
                    <h6 class="mb-3">Pricing (₹) - Leave blank to hide that billing cycle</h6>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Monthly Price</label>
                            <input type="number" step="0.01" class="form-control" id="price_monthly" name="price_monthly" placeholder="e.g., 499">
                            <small class="text-muted">Leave empty to hide</small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Yearly Price</label>
                            <input type="number" step="0.01" class="form-control" id="price_yearly" name="price_yearly" placeholder="e.g., 4999">
                            <small class="text-muted">Total for 1 year</small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">2 Years Price</label>
                            <input type="number" step="0.01" class="form-control" id="price_2years" name="price_2years" placeholder="e.g., 8999">
                            <small class="text-muted">Total for 2 years</small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">4 Years Price</label>
                            <input type="number" step="0.01" class="form-control" id="price_4years" name="price_4years" placeholder="e.g., 15999">
                            <small class="text-muted">Total for 4 years</small>
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="mb-3">Additional Charges</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Setup Fee (₹)</label>
                            <input type="number" step="0.01" class="form-control" id="setup_fee" name="setup_fee" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">GST %</label>
                            <input type="number" step="0.01" class="form-control" id="gst_percentage" name="gst_percentage" value="18">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Processing Fee (₹)</label>
                            <input type="number" step="0.01" class="form-control" id="processing_fee" name="processing_fee" value="0">
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
            document.getElementById('features').value = p.features || '';
            document.getElementById('price_monthly').value = p.price_monthly || '';
            document.getElementById('price_yearly').value = p.price_yearly || '';
            document.getElementById('price_2years').value = p.price_2years || '';
            document.getElementById('price_4years').value = p.price_4years || '';
            document.getElementById('setup_fee').value = p.setup_fee || 0;
            document.getElementById('gst_percentage').value = p.gst_percentage || 18;
            document.getElementById('processing_fee').value = p.processing_fee || 0;
            document.getElementById('status').value = p.status;
            document.getElementById('is_popular').checked = p.is_popular == 1;
            document.getElementById('sort_order').value = p.sort_order || 0;
            
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

