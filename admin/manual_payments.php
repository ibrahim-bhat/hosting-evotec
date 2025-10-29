<?php
require_once '../config.php';
require_once '../components/auth_helper.php';
require_once '../components/admin_helper.php';
require_once '../components/payment_settings_helper.php';
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
        case 'create_manual_payment':
            try {
                // Validate required fields
                if (empty($_POST['user_id']) || empty($_POST['payment_reason']) || empty($_POST['payment_amount'])) {
                    echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
                    break;
                }
                
                $data = [
                    'user_id' => intval($_POST['user_id']),
                    'order_id' => !empty($_POST['order_id']) ? intval($_POST['order_id']) : null,
                    'payment_reason' => sanitizeInput($_POST['payment_reason']),
                    'payment_amount' => floatval($_POST['payment_amount']),
                    'currency' => $_POST['currency'] ?? 'INR',
                    'payment_method' => 'manual',
                    'payment_status' => $_POST['payment_status'] ?? 'paid',
                    'order_date' => $_POST['order_date'] ?? date('Y-m-d'),
                    'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
                    'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
                    'description' => sanitizeInput($_POST['description'] ?? ''),
                    'admin_notes' => sanitizeInput($_POST['admin_notes'] ?? ''),
                    'created_by' => $_SESSION['user_id']
                ];
                
                $paymentId = createManualPayment($conn, $data);
                if ($paymentId) {
                    echo json_encode(['success' => true, 'message' => 'Manual payment created successfully', 'payment_id' => $paymentId]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to create manual payment. Please check your data.']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            break;
            
        case 'get_manual_payment':
            $paymentId = intval($_POST['payment_id']);
            $payment = getManualPaymentById($conn, $paymentId);
            if ($payment) {
                echo json_encode(['success' => true, 'payment' => $payment]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Manual payment not found']);
            }
            break;
            
        case 'update_manual_payment':
            $paymentId = intval($_POST['payment_id']);
            $data = [
                'payment_reason' => sanitizeInput($_POST['payment_reason']),
                'payment_amount' => floatval($_POST['payment_amount']),
                'currency' => $_POST['currency'] ?? 'INR',
                'payment_status' => $_POST['payment_status'] ?? 'paid',
                'order_date' => $_POST['order_date'],
                'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
                'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
                'description' => sanitizeInput($_POST['description'] ?? ''),
                'admin_notes' => sanitizeInput($_POST['admin_notes'] ?? '')
            ];
            
            $success = updateManualPayment($conn, $paymentId, $data);
            echo json_encode(['success' => $success, 'message' => $success ? 'Manual payment updated successfully' : 'Failed to update manual payment']);
            break;
            
        case 'delete_manual_payment':
            $paymentId = intval($_POST['payment_id']);
            $success = deleteManualPayment($conn, $paymentId);
            echo json_encode(['success' => $success, 'message' => $success ? 'Manual payment deleted successfully' : 'Failed to delete manual payment']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
    ob_end_flush();
    exit;
}

// Get search parameters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

// Get all users for dropdown
$allUsers = getAllUsers($conn);

// Get all manual payments
$manualPayments = getAllManualPayments($conn, $search, $userId);

$pageTitle = "Manual Payments";
?>

<?php include 'includes/header.php'; ?>

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Manual Payments</h1>
            <p class="page-subtitle">Manage manual payments and billing entries</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="openAddManualPaymentModal()">
                <i class="bi bi-plus-circle me-2"></i>
                Add Manual Payment
            </button>
        </div>
    </div>
</div>

<!-- Flash Message -->
<div id="flashMessage"></div>

<!-- Search and Filters -->
<div class="content-card mb-4">
    <form method="GET" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Search</label>
            <input type="text" class="form-control" name="search" placeholder="Search by reason, user name, email, or order number..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">User</label>
            <select class="form-select" name="user_id">
                <option value="">All Users</option>
                <?php foreach ($allUsers as $user): ?>
                    <option value="<?php echo $user['id']; ?>" <?php echo $userId == $user['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i>
                </button>
                <a href="manual_payments.php" class="btn btn-secondary">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Manual Payments Table -->
<div class="content-card">
    <div class="table-responsive">
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Reason</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Order Date</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($manualPayments)): ?>
                    <?php foreach ($manualPayments as $payment): ?>
                        <tr>
                            <td>#<?php echo $payment['id']; ?></td>
                            <td>
                                <div class="user-info">
                                    <div class="user-name"><?php echo htmlspecialchars($payment['user_name']); ?></div>
                                    <div class="user-email"><?php echo htmlspecialchars($payment['user_email']); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($payment['payment_reason']); ?></div>
                                <?php if ($payment['order_number']): ?>
                                    <small class="text-muted">Order: <?php echo htmlspecialchars($payment['order_number']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="fw-bold">₹<?php echo number_format($payment['payment_amount'], 2); ?></span>
                                <div class="text-muted small"><?php echo $payment['currency']; ?></div>
                            </td>
                            <td>
                                <span class="payment-status <?php echo $payment['payment_status']; ?>">
                                    <?php echo ucfirst($payment['payment_status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($payment['order_date'])); ?></td>
                            <td>
                                <?php if ($payment['start_date']): ?>
                                    <?php echo date('M d, Y', strtotime($payment['start_date'])); ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($payment['end_date']): ?>
                                    <?php echo date('M d, Y', strtotime($payment['end_date'])); ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($payment['created_by_name'] ?? 'System'); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewManualPayment(<?php echo $payment['id']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editManualPayment(<?php echo $payment['id']; ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="manual_payment_invoice.php?id=<?php echo $payment['id']; ?>" class="btn btn-sm btn-outline-info" title="View Invoice">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </a>
                                    <a href="manual_payment_invoice.php?id=<?php echo $payment['id']; ?>&download=1" class="btn btn-sm btn-outline-success" title="Download Invoice">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteManualPayment(<?php echo $payment['id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-receipt" style="font-size: 48px; margin-bottom: 16px;"></i>
                                <div>No manual payments found</div>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Manual Payment Modal -->
<div class="modal fade" id="addManualPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Manual Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addManualPaymentForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Select User *</label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">Choose user...</option>
                                <?php foreach ($allUsers as $user): ?>
                                    <option value="<?php echo $user['id']; ?>">
                                        <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Order ID (Optional)</label>
                            <input type="number" class="form-control" id="order_id" name="order_id" placeholder="Leave empty if not related to an order">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Payment Reason *</label>
                            <input type="text" class="form-control" id="payment_reason" name="payment_reason" placeholder="e.g., Additional services, Refund, Credit" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Amount *</label>
                            <input type="number" class="form-control" id="payment_amount" name="payment_amount" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Currency</label>
                            <select class="form-select" id="currency" name="currency">
                                <option value="INR">INR</option>
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Status</label>
                            <select class="form-select" id="payment_status" name="payment_status">
                                <option value="paid">Paid</option>
                                <option value="pending">Pending</option>
                                <option value="failed">Failed</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Order Date *</label>
                            <input type="date" class="form-control" id="order_date" name="order_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date (Optional)</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date (Optional)</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Additional details about this payment"></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Admin Notes</label>
                            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="2" placeholder="Internal notes (not visible to user)"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveManualPayment()">Create Payment</button>
            </div>
        </div>
    </div>
</div>

<!-- View/Edit Manual Payment Modal -->
<div class="modal fade" id="viewManualPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manual Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewManualPaymentContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editManualPaymentBtn" onclick="editManualPayment()" style="display: none;">Edit</button>
            </div>
        </div>
    </div>
</div>

<script>
// Open add manual payment modal
function openAddManualPaymentModal() {
    document.getElementById('addManualPaymentForm').reset();
    document.getElementById('order_date').value = new Date().toISOString().split('T')[0];
    const modal = new bootstrap.Modal(document.getElementById('addManualPaymentModal'));
    modal.show();
}

// Save manual payment
function saveManualPayment() {
    const formData = new FormData(document.getElementById('addManualPaymentForm'));
    formData.append('action', 'create_manual_payment');
    
    fetch('manual_payments.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        showFlash(data.success ? 'success' : 'error', data.message);
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addManualPaymentModal')).hide();
            setTimeout(() => location.reload(), 1500);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showFlash('error', 'An error occurred while creating manual payment');
    });
}

// View manual payment
function viewManualPayment(paymentId) {
    const formData = new FormData();
    formData.append('action', 'get_manual_payment');
    formData.append('payment_id', paymentId);
    
    fetch('manual_payments.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const payment = data.payment;
            document.getElementById('viewManualPaymentContent').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">User</label>
                        <div class="form-control-plaintext">${payment.user_name} (${payment.user_email})</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment ID</label>
                        <div class="form-control-plaintext">#${payment.id}</div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Payment Reason</label>
                        <div class="form-control-plaintext">${payment.payment_reason}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Amount</label>
                        <div class="form-control-plaintext">₹${parseFloat(payment.payment_amount).toFixed(2)} ${payment.currency}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <div class="form-control-plaintext">
                            <span class="payment-status ${payment.payment_status}">${payment.payment_status.charAt(0).toUpperCase() + payment.payment_status.slice(1)}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Order Date</label>
                        <div class="form-control-plaintext">${new Date(payment.order_date).toLocaleDateString()}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Created By</label>
                        <div class="form-control-plaintext">${payment.created_by_name || 'System'}</div>
                    </div>
                    ${payment.start_date ? `
                    <div class="col-md-6">
                        <label class="form-label">Start Date</label>
                        <div class="form-control-plaintext">${new Date(payment.start_date).toLocaleDateString()}</div>
                    </div>
                    ` : ''}
                    ${payment.end_date ? `
                    <div class="col-md-6">
                        <label class="form-label">End Date</label>
                        <div class="form-control-plaintext">${new Date(payment.end_date).toLocaleDateString()}</div>
                    </div>
                    ` : ''}
                    ${payment.description ? `
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <div class="form-control-plaintext">${payment.description}</div>
                    </div>
                    ` : ''}
                    ${payment.admin_notes ? `
                    <div class="col-md-12">
                        <label class="form-label">Admin Notes</label>
                        <div class="form-control-plaintext">${payment.admin_notes}</div>
                    </div>
                    ` : ''}
                </div>
            `;
            document.getElementById('editManualPaymentBtn').style.display = 'inline-block';
            document.getElementById('editManualPaymentBtn').setAttribute('onclick', `editManualPayment(${paymentId})`);
        } else {
            showFlash('error', data.message);
        }
    });
    
    const modal = new bootstrap.Modal(document.getElementById('viewManualPaymentModal'));
    modal.show();
}

// Edit manual payment
function editManualPayment(paymentId) {
    // Implementation for edit functionality
    showFlash('info', 'Edit functionality will be implemented');
}

// Delete manual payment
function deleteManualPayment(paymentId) {
    if (confirm('Are you sure you want to delete this manual payment?')) {
        const formData = new FormData();
        formData.append('action', 'delete_manual_payment');
        formData.append('payment_id', paymentId);
        
        fetch('manual_payments.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            showFlash(data.success ? 'success' : 'error', data.message);
            if (data.success) {
                setTimeout(() => location.reload(), 1500);
            }
        });
    }
}

function showFlash(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
    const icon = type === 'success' ? 'bi-check-circle-fill' : type === 'error' ? 'bi-exclamation-circle-fill' : 'bi-info-circle-fill';
    
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
