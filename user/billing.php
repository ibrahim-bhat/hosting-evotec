<?php
require_once 'includes/header.php';
require_once '../components/user_helper.php';

$userId = $_SESSION['user_id'];

// Get user's payment history
$payments = getUserPaymentHistory($conn, $userId);

// Get user's upcoming renewals
$upcomingRenewals = getUserUpcomingRenewals($conn, $userId, 30);

// Get user's hosting statistics
$stats = getUserHostingStats($conn, $userId);

$pageTitle = "Billing";
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Billing</h1>
    <p class="page-subtitle">Manage your payments and view billing history</p>
</div>

<!-- Billing Summary -->
<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Total Spent</span>
                <i class="bi bi-currency-rupee stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo formatCurrency($stats['total_spent']); ?></div>
            <div class="stats-change positive">
                <i class="bi bi-arrow-up"></i>
                <span>All time payments</span>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Active Orders</span>
                <i class="bi bi-check-circle stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo $stats['active_orders']; ?></div>
            <div class="stats-change positive">
                <i class="bi bi-arrow-up"></i>
                <span>Currently active</span>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Pending Orders</span>
                <i class="bi bi-clock stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo $stats['pending_orders']; ?></div>
            <div class="stats-change <?php echo $stats['pending_orders'] > 0 ? 'negative' : 'positive'; ?>">
                <i class="bi bi-arrow-<?php echo $stats['pending_orders'] > 0 ? 'up' : 'down'; ?>"></i>
                <span><?php echo $stats['pending_orders'] > 0 ? 'Awaiting payment' : 'All paid'; ?></span>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="stats-header">
                <span class="stats-label">Upcoming Renewals</span>
                <i class="bi bi-calendar-check stats-icon"></i>
            </div>
            <div class="stats-value"><?php echo count($upcomingRenewals); ?></div>
            <div class="stats-change <?php echo count($upcomingRenewals) > 0 ? 'negative' : 'positive'; ?>">
                <i class="bi bi-arrow-<?php echo count($upcomingRenewals) > 0 ? 'up' : 'down'; ?>"></i>
                <span><?php echo count($upcomingRenewals) > 0 ? 'Next 30 days' : 'No renewals'; ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Renewals -->
<?php if (!empty($upcomingRenewals)): ?>
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="content-card">
                <h2 class="card-title">Upcoming Renewals</h2>
                <p class="card-subtitle">Orders that will expire in the next 30 days</p>
                
                <div class="table-responsive">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Package</th>
                                <th>Expiry Date</th>
                                <th>Days Left</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingRenewals as $renewal): ?>
                                <tr>
                                    <td>
                                        <span class="user-name"><?php echo htmlspecialchars($renewal['order_number']); ?></span>
                                    </td>
                                    <td>
                                        <span class="user-email"><?php echo htmlspecialchars($renewal['package_name'] ?? 'N/A'); ?></span>
                                    </td>
                                    <td>
                                        <span class="user-email"><?php echo formatDate($renewal['expiry_date']); ?></span>
                                    </td>
                                    <td>
                                        <span class="user-email <?php echo isOrderExpiringSoon($renewal['expiry_date']) ? 'text-danger fw-bold' : ''; ?>">
                                            <?php echo getDaysUntilExpiry($renewal['expiry_date']); ?> days
                                        </span>
                                    </td>
                                    <td>
                                        <span class="user-email"><?php echo formatCurrency($renewal['total_amount']); ?></span>
                                    </td>
                                    <td>
                                        <a href="renew.php?order_id=<?php echo $renewal['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="bi bi-arrow-clockwise me-1"></i>
                                            Renew / Upgrade
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Payment History -->
<div class="row g-4">
    <div class="col-12">
        <div class="content-card">
            <h2 class="card-title">Payment History</h2>
            <p class="card-subtitle">Your payment transactions and billing history</p>
            
            <?php if (!empty($payments)): ?>
                <div class="table-responsive">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Order #</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td>
                                        <span class="user-name"><?php echo htmlspecialchars($payment['transaction_id'] ?? 'N/A'); ?></span>
                                    </td>
                                    <td>
                                        <span class="user-email"><?php echo htmlspecialchars($payment['order_number'] ?? 'N/A'); ?></span>
                                    </td>
                                    <td>
                                        <span class="user-email"><?php echo formatCurrency($payment['payment_amount']); ?></span>
                                    </td>
                                    <td>
                                        <span class="user-email"><?php echo ucfirst($payment['payment_method'] ?? 'N/A'); ?></span>
                                    </td>
                                    <td>
                                        <span class="order-status <?php echo $payment['payment_status']; ?>">
                                            <?php echo ucfirst($payment['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="user-email"><?php echo formatDate($payment['created_at']); ?></span>
                                    </td>
                                    <td>
                                        <a href="billing.php?id=<?php echo $payment['id']; ?>" class="btn-action" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="bi bi-receipt" style="font-size: 48px; color: #9ca3af; margin-bottom: 16px;"></i>
                    <h5 class="card-title">No Payment History</h5>
                    <p class="card-subtitle">You haven't made any payments yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Payment Details Modal (if viewing specific payment) -->
<?php if (isset($_GET['id'])): ?>
    <?php
    $paymentId = (int)$_GET['id'];
    $stmt = $conn->prepare("
        SELECT ph.*, ho.order_number, ho.total_amount
        FROM payment_history ph 
        LEFT JOIN hosting_orders ho ON ph.order_id = ho.id 
        WHERE ph.id = ? AND ph.user_id = ?
    ");
    $stmt->bind_param("ii", $paymentId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $paymentDetails = $result->fetch_assoc();
    $stmt->close();
    ?>
    
    <?php if ($paymentDetails): ?>
        <div class="modal fade show" style="display: block;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Payment Details - <?php echo htmlspecialchars($paymentDetails['transaction_id'] ?? 'N/A'); ?></h5>
                        <a href="billing.php" class="btn-close"></a>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Transaction ID</label>
                                <div class="form-control-plaintext"><?php echo htmlspecialchars($paymentDetails['transaction_id'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Order Number</label>
                                <div class="form-control-plaintext"><?php echo htmlspecialchars($paymentDetails['order_number'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Amount</label>
                                <div class="form-control-plaintext fw-bold"><?php echo formatCurrency($paymentDetails['payment_amount']); ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Currency</label>
                                <div class="form-control-plaintext"><?php echo htmlspecialchars($paymentDetails['currency']); ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Method</label>
                                <div class="form-control-plaintext"><?php echo ucfirst($paymentDetails['payment_method'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Status</label>
                                <div class="form-control-plaintext">
                                    <span class="order-status <?php echo $paymentDetails['payment_status']; ?>">
                                        <?php echo ucfirst($paymentDetails['payment_status']); ?>
                                    </span>
                                </div>
                            </div>
                            <?php if ($paymentDetails['razorpay_payment_id']): ?>
                                <div class="col-md-6">
                                    <label class="form-label">Razorpay Payment ID</label>
                                    <div class="form-control-plaintext"><?php echo htmlspecialchars($paymentDetails['razorpay_payment_id']); ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if ($paymentDetails['transaction_date']): ?>
                                <div class="col-md-6">
                                    <label class="form-label">Transaction Date</label>
                                    <div class="form-control-plaintext"><?php echo formatDate($paymentDetails['transaction_date']); ?></div>
                                </div>
                            <?php endif; ?>
                            <div class="col-md-6">
                                <label class="form-label">Payment Date</label>
                                <div class="form-control-plaintext"><?php echo formatDate($paymentDetails['created_at']); ?></div>
                            </div>
                            <?php if ($paymentDetails['payment_description']): ?>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <div class="form-control-plaintext"><?php echo htmlspecialchars($paymentDetails['payment_description']); ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if ($paymentDetails['failure_reason']): ?>
                                <div class="col-12">
                                    <label class="form-label">Failure Reason</label>
                                    <div class="form-control-plaintext text-danger"><?php echo htmlspecialchars($paymentDetails['failure_reason']); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="billing.php" class="btn btn-secondary">Close</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
