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
        case 'approve_order':
            $orderId = intval($_POST['order_id']);
            $notes = isset($_POST['admin_notes']) ? sanitizeInput($_POST['admin_notes']) : null;
            $success = approveOrder($conn, $orderId, $notes);
            echo json_encode(['success' => $success, 'message' => $success ? 'Order approved successfully' : 'Failed to approve order']);
            break;
            
        case 'reject_order':
            $orderId = intval($_POST['order_id']);
            $notes = isset($_POST['admin_notes']) ? sanitizeInput($_POST['admin_notes']) : null;
            $success = rejectOrder($conn, $orderId, $notes);
            echo json_encode(['success' => $success, 'message' => $success ? 'Order rejected successfully' : 'Failed to reject order']);
            break;
            
        case 'update_order_status':
            $orderId = intval($_POST['order_id']);
            $status = $_POST['order_status'];
            $notes = isset($_POST['admin_notes']) ? sanitizeInput($_POST['admin_notes']) : null;
            
            $success = updateOrderStatus($conn, $orderId, $status);
            if ($notes) {
                $stmt = $conn->prepare("UPDATE hosting_orders SET admin_notes = ? WHERE id = ?");
                $stmt->bind_param("si", $notes, $orderId);
                $stmt->execute();
                $stmt->close();
            }
            
            echo json_encode(['success' => $success, 'message' => $success ? 'Order status updated successfully' : 'Failed to update order status']);
            break;
            
        case 'update_payment_status':
            $orderId = intval($_POST['order_id']);
            $paymentStatus = $_POST['payment_status'];
            $success = updatePaymentStatus($conn, $orderId, $paymentStatus);
            echo json_encode(['success' => $success, 'message' => $success ? 'Payment status updated successfully' : 'Failed to update payment status']);
            break;
            
        case 'get_order':
            $orderId = intval($_POST['order_id']);
            $order = getOrderById($conn, $orderId);
            if ($order) {
                echo json_encode(['success' => true, 'order' => $order]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Order not found']);
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
$paymentFilter = isset($_GET['payment_status']) ? sanitizeInput($_GET['payment_status']) : '';

// Get all orders
$orders = getAllOrders($conn, $search, $statusFilter, $paymentFilter);

// Get statistics
$stats = getOrderStatistics($conn);

$pageTitle = "Orders Management";
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header-row">
    <div>
        <h1 class="page-title">Orders Management</h1>
        <p class="page-subtitle">Manage hosting orders and subscriptions</p>
    </div>
</div>

<!-- Flash Message -->
<div id="flashMessage"></div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Total Orders</span>
                <i class="bi bi-cart-fill stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo $stats['total_orders']; ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Pending</span>
                <i class="bi bi-clock-fill stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo $stats['pending_orders']; ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Active</span>
                <i class="bi bi-check-circle-fill stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo $stats['active_orders']; ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Paid</span>
                <i class="bi bi-currency-dollar stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo $stats['paid_orders']; ?></div>
        </div>
    </div>
</div>

<!-- Orders Card -->
<div class="content-card">
    <!-- Search and Filter -->
    <div class="search-container mb-3">
        <form method="GET" class="search-container" id="searchForm">
            <i class="bi bi-search search-icon"></i>
            <input type="text" 
                   class="search-input" 
                   name="search" 
                   placeholder="Search orders..." 
                   value="<?php echo htmlspecialchars($search); ?>">
            <select name="status" class="form-select" style="width: auto; margin-left: 10px;">
                <option value="">All Status</option>
                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="suspended" <?php echo $statusFilter === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                <option value="expired" <?php echo $statusFilter === 'expired' ? 'selected' : ''; ?>>Expired</option>
            </select>
            <select name="payment_status" class="form-select" style="width: auto; margin-left: 10px;">
                <option value="">All Payments</option>
                <option value="pending" <?php echo $paymentFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="paid" <?php echo $paymentFilter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                <option value="failed" <?php echo $paymentFilter === 'failed' ? 'selected' : ''; ?>>Failed</option>
            </select>
            <button type="submit" class="btn btn-primary" style="margin-left: 10px;">Filter</button>
        </form>
    </div>
    
    <!-- Orders Table -->
    <div class="table-responsive">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Package</th>
                    <th>Billing Cycle</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Expiry Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr data-order-id="<?php echo $order['id']; ?>">
                            <td>
                                <div class="user-name"><?php echo htmlspecialchars($order['order_number']); ?></div>
                                <small class="text-muted"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></small>
                            </td>
                            <td>
                                <div class="user-name"><?php echo htmlspecialchars($order['user_name']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($order['user_email']); ?></small>
                            </td>
                            <td>
                                <div class="user-email"><?php echo htmlspecialchars($order['package_name']); ?></div>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo ucfirst(str_replace('years', ' Years', $order['billing_cycle'])); ?></span>
                            </td>
                            <td>
                                <div class="user-email">₹<?php echo number_format($order['total_amount'], 2); ?></div>
                            </td>
                            <td>
                                <?php
                                $paymentBadge = [
                                    'paid' => 'success',
                                    'pending' => 'warning',
                                    'failed' => 'danger',
                                    'refunded' => 'secondary'
                                ];
                                $badgeClass = $paymentBadge[$order['payment_status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $badgeClass; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusBadge = [
                                    'active' => 'success',
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'suspended' => 'danger',
                                    'cancelled' => 'secondary',
                                    'expired' => 'dark'
                                ];
                                $badgeClass = $statusBadge[$order['order_status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $badgeClass; ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($order['expiry_date']): ?>
                                    <div class="user-email"><?php echo date('M d, Y', strtotime($order['expiry_date'])); ?></div>
                                    <?php
                                    $expiryDate = new DateTime($order['expiry_date']);
                                    $today = new DateTime();
                                    if ($expiryDate < $today):
                                    ?>
                                        <small class="text-danger">Expired</small>
                                    <?php elseif ($expiryDate->diff($today)->days <= 7): ?>
                                        <small class="text-warning">Expires soon</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn-action" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); viewOrderDetails(<?php echo $order['id']; ?>)">
                                            <i class="bi bi-eye-fill"></i> View Details
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); changeOrderStatus(<?php echo $order['id']; ?>)">
                                            <i class="bi bi-pencil-fill"></i> Change Status
                                        </a></li>
                                        <?php if ($order['order_status'] === 'pending' && $order['payment_status'] === 'paid'): ?>
                                            <li><a class="dropdown-item text-success" href="#" onclick="event.preventDefault(); approveOrder(<?php echo $order['id']; ?>)">
                                                <i class="bi bi-check-circle-fill"></i> Approve
                                            </a></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); rejectOrderPrompt(<?php echo $order['id']; ?>)">
                                                <i class="bi bi-x-circle-fill"></i> Reject
                                            </a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            No orders found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Change Status Modal -->
<div class="modal fade" id="changeStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="changeStatusForm">
                    <input type="hidden" id="status_order_id" name="order_id">
                    <div class="mb-3">
                        <label class="form-label">Order Status</label>
                        <select class="form-select" id="order_status" name="order_status" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Admin Notes (Optional)</label>
                        <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveStatusChange()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
// View order details
function viewOrderDetails(orderId) {
    fetch('orders.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=get_order&order_id=${orderId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const o = data.order;
            const expiryDate = o.expiry_date ? new Date(o.expiry_date).toLocaleDateString() : 'N/A';
            const startDate = o.start_date ? new Date(o.start_date).toLocaleDateString() : 'N/A';
            
            document.getElementById('orderDetailsContent').innerHTML = `
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Order Number:</strong><br>
                        ${o.order_number}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Customer:</strong><br>
                        ${o.user_name}<br>
                        <small class="text-muted">${o.user_email}</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Package:</strong><br>
                        ${o.package_name}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Billing Cycle:</strong><br>
                        ${o.billing_cycle.charAt(0).toUpperCase() + o.billing_cycle.slice(1)}
                    </div>
                    <div class="col-md-12 mb-3">
                        <hr>
                        <h6>Pricing Details</h6>
                        <div class="row">
                            <div class="col-md-6">Base Price:</div>
                            <div class="col-md-6">₹${o.base_price}</div>
                            <div class="col-md-6">Setup Fee:</div>
                            <div class="col-md-6">₹${o.setup_fee}</div>
                            <div class="col-md-6">GST Amount:</div>
                            <div class="col-md-6">₹${o.gst_amount}</div>
                            <div class="col-md-6">Processing Fee:</div>
                            <div class="col-md-6">₹${o.processing_fee}</div>
                            <div class="col-md-6"><strong>Total Amount:</strong></div>
                            <div class="col-md-6"><strong>₹${o.total_amount}</strong></div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Payment Status:</strong><br>
                        <span class="badge bg-success">${o.payment_status}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Order Status:</strong><br>
                        <span class="badge bg-info">${o.order_status}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Start Date:</strong><br>
                        ${startDate}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Expiry Date:</strong><br>
                        ${expiryDate}
                    </div>
                </div>
            `;
            const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
            modal.show();
        }
    });
}

// Change order status
function changeOrderStatus(orderId) {
    document.getElementById('status_order_id').value = orderId;
    const modal = new bootstrap.Modal(document.getElementById('changeStatusModal'));
    modal.show();
}

// Save status change
function saveStatusChange() {
    const formData = new FormData(document.getElementById('changeStatusForm'));
    formData.append('action', 'update_order_status');
    
    fetch('orders.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        showFlash(data.success ? 'success' : 'error', data.message);
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('changeStatusModal')).hide();
            setTimeout(() => location.reload(), 1500);
        }
    });
}

// Approve order
function approveOrder(orderId) {
    if (!confirm('Are you sure you want to approve this order and activate it?')) return;
    
    const adminNotes = prompt('Admin Notes (Optional):');
    
    fetch('orders.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=approve_order&order_id=${orderId}&admin_notes=${encodeURIComponent(adminNotes || '')}`
    })
    .then(res => res.json())
    .then(data => {
        showFlash(data.success ? 'success' : 'error', data.message);
        if (data.success) setTimeout(() => location.reload(), 1500);
    });
}

// Reject order
function rejectOrderPrompt(orderId) {
    if (!confirm('Are you sure you want to reject this order?')) return;
    
    const adminNotes = prompt('Reason for rejection (Optional):');
    
    fetch('orders.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=reject_order&order_id=${orderId}&admin_notes=${encodeURIComponent(adminNotes || '')}`
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

