<?php
require_once '../config.php';
require_once '../components/auth_helper.php';
require_once '../components/admin_helper.php';
require_once '../components/hosting_helper.php';
require_once '../components/flash_message.php';

// Require admin access
requireAdmin();

// Handle manual payment entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    
    switch ($action) {
        case 'add_manual_payment':
            $orderId = intval($_POST['order_id']);
            $amount = floatval($_POST['amount']);
            $paymentMethod = sanitizeInput($_POST['payment_method']);
            $transactionId = sanitizeInput($_POST['transaction_id'] ?? '');
            $notes = sanitizeInput($_POST['notes'] ?? '');
            
            // Get order details
            $order = getOrderById($conn, $orderId);
            if (!$order) {
                echo json_encode(['success' => false, 'message' => 'Order not found']);
                break;
            }
            
            // Create payment record
            $paymentData = [
                'order_id' => $orderId,
                'user_id' => $order['user_id'],
                'payment_amount' => $amount,
                'currency' => 'INR',
                'payment_method' => $paymentMethod,
                'payment_status' => 'success',
                'razorpay_order_id' => $transactionId,
                'razorpay_payment_id' => $transactionId,
                'razorpay_signature' => '',
                'transaction_id' => $transactionId,
                'transaction_date' => date('Y-m-d H:i:s'),
                'payment_description' => $notes,
                'failure_reason' => ''
            ];
            
            $paymentId = createPaymentHistory($conn, $paymentData);
            
            // Update order payment status
            updatePaymentStatus($conn, $orderId, 'paid', $transactionId);
            
            echo json_encode(['success' => (bool)$paymentId, 'message' => $paymentId ? 'Payment recorded successfully' : 'Failed to record payment']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

// Get filters
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$paymentStatus = isset($_GET['payment_status']) ? $_GET['payment_status'] : '';

// Get all orders for payment tracking
$sql = "SELECT o.*, u.name as user_name, u.email as user_email, p.name as package_name 
        FROM hosting_orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        LEFT JOIN hosting_packages p ON o.package_id = p.id 
        WHERE 1=1";
$params = [];
$types = '';

if (!empty($dateFrom)) {
    $sql .= " AND DATE(o.created_at) >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}

if (!empty($dateTo)) {
    $sql .= " AND DATE(o.created_at) <= ?";
    $params[] = $dateTo;
    $types .= 's';
}

if ($userId > 0) {
    $sql .= " AND o.user_id = ?";
    $params[] = $userId;
    $types .= 'i';
}

if (!empty($paymentStatus)) {
    $sql .= " AND o.payment_status = ?";
    $params[] = $paymentStatus;
    $types .= 's';
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get all users for filter
$allUsers = getAllUsers($conn);

$pageTitle = "Billing & Transactions";
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header-row">
    <div>
        <h1 class="page-title">Billing & Transactions</h1>
        <p class="page-subtitle">Manage all payments and invoices</p>
    </div>
</div>

<!-- Flash Message -->
<div id="flashMessage"></div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <?php
    $totalRevenue = array_sum(array_column($orders, 'total_amount'));
    $paidOrders = count(array_filter($orders, function($o) { return $o['payment_status'] === 'paid'; }));
    $pendingPayments = count(array_filter($orders, function($o) { return $o['payment_status'] === 'pending'; }));
    ?>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Total Revenue</span>
                <i class="bi bi-currency-dollar stats-icon"></i>
            </div>
            <div class="stats-value">₹<?php echo number_format($totalRevenue, 2); ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Paid Orders</span>
                <i class="bi bi-check-circle stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo $paidOrders; ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Pending Payments</span>
                <i class="bi bi-clock stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo $pendingPayments; ?></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Total Transactions</span>
                <i class="bi bi-receipt stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo count($orders); ?></div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="content-card mb-4">
    <form method="GET" id="filterForm" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Date From</label>
            <input type="date" class="form-control" name="date_from" value="<?php echo $dateFrom; ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Date To</label>
            <input type="date" class="form-control" name="date_to" value="<?php echo $dateTo; ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Customer</label>
            <select class="form-select" name="user_id">
                <option value="">All Customers</option>
                <?php foreach ($allUsers as $user): ?>
                    <option value="<?php echo $user['id']; ?>" <?php echo $userId == $user['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Payment Status</label>
            <select class="form-select" name="payment_status">
                <option value="">All Status</option>
                <option value="paid" <?php echo $paymentStatus === 'paid' ? 'selected' : ''; ?>>Paid</option>
                <option value="pending" <?php echo $paymentStatus === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="failed" <?php echo $paymentStatus === 'failed' ? 'selected' : ''; ?>>Failed</option>
            </select>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="billing.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<!-- Transactions Table -->
<div class="content-card">
    <div class="table-responsive">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Package</th>
                    <th>Amount</th>
                    <th>Payment Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <div class="user-name"><?php echo htmlspecialchars($order['order_number']); ?></div>
                            </td>
                            <td>
                                <div class="user-name"><?php echo htmlspecialchars($order['user_name']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($order['user_email']); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo htmlspecialchars($order['package_name']); ?></span>
                            </td>
                            <td>
                                <strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong>
                            </td>
                            <td>
                                <?php
                                $badgeClass = [
                                    'paid' => 'success',
                                    'pending' => 'warning',
                                    'failed' => 'danger'
                                ];
                                $class = $badgeClass[$order['payment_status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $class; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn-action" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); viewInvoice(<?php echo $order['id']; ?>)">
                                            <i class="bi bi-file-earmark-pdf"></i> View Invoice
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); downloadInvoice(<?php echo $order['id']; ?>)">
                                            <i class="bi bi-download"></i> Download PDF
                                        </a></li>
                                        <?php if ($order['payment_status'] !== 'paid'): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-success" href="#" onclick="event.preventDefault(); addManualPayment(<?php echo $order['id']; ?>)">
                                                <i class="bi bi-plus-circle"></i> Add Manual Payment
                                            </a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No transactions found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Manual Payment Modal -->
<div class="modal fade" id="manualPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Manual Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="manualPaymentForm">
                    <input type="hidden" id="manual_order_id" name="order_id">
                    <div class="mb-3">
                        <label class="form-label">Amount *</label>
                        <input type="number" step="0.01" class="form-control" id="manual_amount" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method *</label>
                        <select class="form-select" id="manual_payment_method" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="upi">UPI</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transaction ID / Reference</label>
                        <input type="text" class="form-control" id="manual_transaction_id" name="transaction_id" placeholder="Optional">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="manual_notes" name="notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveManualPayment()">Record Payment</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewInvoice(orderId) {
    window.open('invoice.php?id=' + orderId, '_blank');
}

function downloadInvoice(orderId) {
    window.open('invoice.php?id=' + orderId + '&download=1', '_blank');
}

function addManualPayment(orderId) {
    document.getElementById('manual_order_id').value = orderId;
    document.getElementById('manualPaymentForm').reset();
    const modal = new bootstrap.Modal(document.getElementById('manualPaymentModal'));
    modal.show();
}

function saveManualPayment() {
    const formData = new FormData(document.getElementById('manualPaymentForm'));
    formData.append('action', 'add_manual_payment');
    
    fetch('billing.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        showFlash(data.success ? 'success' : 'error', data.message);
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('manualPaymentModal')).hide();
            setTimeout(() => location.reload(), 1500);
        }
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

