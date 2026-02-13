<?php
require_once '../config.php';
require_once '../components/auth_helper.php';
require_once '../components/admin_helper.php';
require_once '../components/coupon_helper.php';
require_once '../components/flash_message.php';

// Require admin access
requireAdmin();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    
    switch ($action) {
        case 'create_coupon':
            $data = [
                'code' => sanitizeInput($_POST['code'] ?? ''),
                'discount_type' => $_POST['discount_type'] ?? 'fixed',
                'discount_value' => $_POST['discount_value'] ?? 0,
                'max_uses' => $_POST['max_uses'] ?? '',
                'min_order_amount' => $_POST['min_order_amount'] ?? 0,
                'expiry_date' => $_POST['expiry_date'] ?? '',
                'is_active' => 1
            ];
            
            if (empty($data['code'])) {
                echo json_encode(['success' => false, 'message' => 'Coupon code is required']);
                break;
            }
            
            // Check for duplicate code
            $existing = getCouponByCode($conn, $data['code']);
            if ($existing) {
                echo json_encode(['success' => false, 'message' => 'A coupon with this code already exists']);
                break;
            }
            
            $result = createCoupon($conn, $data);
            echo json_encode(['success' => (bool)$result, 'message' => $result ? 'Coupon created successfully' : 'Failed to create coupon']);
            break;
            
        case 'get_coupon':
            $couponId = intval($_POST['coupon_id'] ?? 0);
            $coupon = getCouponById($conn, $couponId);
            if ($coupon) {
                echo json_encode(['success' => true, 'coupon' => $coupon]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Coupon not found']);
            }
            break;
            
        case 'update_coupon':
            $couponId = intval($_POST['coupon_id'] ?? 0);
            $data = [
                'code' => sanitizeInput($_POST['code'] ?? ''),
                'discount_type' => $_POST['discount_type'] ?? 'fixed',
                'discount_value' => $_POST['discount_value'] ?? 0,
                'max_uses' => $_POST['max_uses'] ?? '',
                'min_order_amount' => $_POST['min_order_amount'] ?? 0,
                'expiry_date' => $_POST['expiry_date'] ?? '',
                'is_active' => isset($_POST['is_active']) ? (int)$_POST['is_active'] : 0
            ];
            
            if (empty($data['code'])) {
                echo json_encode(['success' => false, 'message' => 'Coupon code is required']);
                break;
            }
            
            $success = updateCoupon($conn, $couponId, $data);
            echo json_encode(['success' => $success, 'message' => $success ? 'Coupon updated successfully' : 'Failed to update coupon']);
            break;
            
        case 'delete_coupon':
            $couponId = intval($_POST['coupon_id'] ?? 0);
            $success = deleteCoupon($conn, $couponId);
            echo json_encode(['success' => $success, 'message' => $success ? 'Coupon deleted successfully' : 'Failed to delete coupon']);
            break;
    }
    exit;
}

// Get all coupons
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$coupons = getAllCoupons($conn, $search);

$pageTitle = 'Coupon Management';
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Coupon Management</h1>
        <p class="page-subtitle">Create and manage discount coupons</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#couponModal" onclick="resetForm()">
        <i class="bi bi-plus-circle"></i> Create Coupon
    </button>
</div>

<?php displayFlashMessage(); ?>

<!-- Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-8">
                <input type="text" class="form-control" name="search" placeholder="Search by coupon code..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-primary me-2"><i class="bi bi-search"></i> Search</button>
                <?php if ($search): ?>
                    <a href="coupons.php" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Coupons Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Discount</th>
                        <th>Usage</th>
                        <th>Min Order</th>
                        <th>Expiry</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($coupons)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No coupons found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($coupons as $coupon): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-dark font-monospace fs-6"><?php echo htmlspecialchars($coupon['code']); ?></span>
                                </td>
                                <td>
                                    <?php if ($coupon['discount_type'] === 'percentage'): ?>
                                        <span class="text-success fw-bold"><?php echo number_format($coupon['discount_value'], 0); ?>%</span> off
                                    <?php else: ?>
                                        <span class="text-success fw-bold">&#8377;<?php echo number_format($coupon['discount_value'], 2); ?></span> off
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $coupon['used_count']; ?>
                                    <?php if ($coupon['max_uses']): ?>
                                        / <?php echo $coupon['max_uses']; ?>
                                    <?php else: ?>
                                        <span class="text-muted">/ &infin;</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $coupon['min_order_amount'] > 0 ? '&#8377;' . number_format($coupon['min_order_amount'], 2) : '<span class="text-muted">None</span>'; ?>
                                </td>
                                <td>
                                    <?php if ($coupon['expiry_date']): ?>
                                        <?php echo date('M d, Y', strtotime($coupon['expiry_date'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">No expiry</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($coupon['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editCoupon(<?php echo $coupon['id']; ?>)" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteCoupon(<?php echo $coupon['id']; ?>, '<?php echo htmlspecialchars($coupon['code']); ?>')" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Coupon Modal -->
<div class="modal fade" id="couponModal" tabindex="-1" aria-labelledby="couponModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="couponModalLabel">Create Coupon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="couponForm">
                <div class="modal-body">
                    <input type="hidden" id="coupon_id" name="coupon_id" value="">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="code" class="form-label">Coupon Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" id="code" name="code" placeholder="e.g. SAVE20" required>
                            <small class="text-muted">Will be auto-converted to uppercase</small>
                        </div>
                        <div class="col-md-6">
                            <label for="discount_type" class="form-label">Discount Type</label>
                            <select class="form-select" id="discount_type" name="discount_type">
                                <option value="fixed">Fixed Amount (&#8377;)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="discount_value" class="form-label">Discount Value <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="discount_value" name="discount_value" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label for="max_uses" class="form-label">Max Uses</label>
                            <input type="number" class="form-control" id="max_uses" name="max_uses" min="0" placeholder="Leave empty for unlimited">
                            <small class="text-muted">e.g. 1 = one-time, 2 = two uses, blank = unlimited</small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="min_order_amount" class="form-label">Min Order Amount (&#8377;)</label>
                            <input type="number" class="form-control" id="min_order_amount" name="min_order_amount" step="0.01" min="0" value="0">
                        </div>
                        <div class="col-md-6">
                            <label for="expiry_date" class="form-label">Expiry Date</label>
                            <input type="datetime-local" class="form-control" id="expiry_date" name="expiry_date">
                            <small class="text-muted">Leave empty for no expiry</small>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="activeToggleGroup" style="display:none;">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="bi bi-check-circle"></i> Save Coupon
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('couponForm').reset();
    document.getElementById('coupon_id').value = '';
    document.getElementById('couponModalLabel').textContent = 'Create Coupon';
    document.getElementById('activeToggleGroup').style.display = 'none';
}

function editCoupon(id) {
    fetch('coupons.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=get_coupon&coupon_id=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const c = data.coupon;
            document.getElementById('coupon_id').value = c.id;
            document.getElementById('code').value = c.code;
            document.getElementById('discount_type').value = c.discount_type;
            document.getElementById('discount_value').value = c.discount_value;
            document.getElementById('max_uses').value = c.max_uses || '';
            document.getElementById('min_order_amount').value = c.min_order_amount;
            document.getElementById('expiry_date').value = c.expiry_date ? c.expiry_date.replace(' ', 'T').substring(0, 16) : '';
            document.getElementById('is_active').checked = c.is_active == 1;
            document.getElementById('activeToggleGroup').style.display = 'block';
            document.getElementById('couponModalLabel').textContent = 'Edit Coupon';
            new bootstrap.Modal(document.getElementById('couponModal')).show();
        } else {
            alert(data.message || 'Failed to load coupon');
        }
    });
}

function deleteCoupon(id, code) {
    if (!confirm('Are you sure you want to delete coupon "' + code + '"?')) return;
    
    fetch('coupons.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=delete_coupon&coupon_id=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to delete coupon');
        }
    });
}

document.getElementById('couponForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const couponId = document.getElementById('coupon_id').value;
    formData.append('action', couponId ? 'update_coupon' : 'create_coupon');
    if (!formData.get('is_active') && couponId) {
        formData.set('is_active', '0');
    }
    
    fetch('coupons.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Operation failed');
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
