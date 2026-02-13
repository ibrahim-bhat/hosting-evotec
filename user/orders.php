<?php
require_once 'includes/header.php';
require_once '../components/user_helper.php';
require_once '../components/payment_settings_helper.php';

$userId = $_SESSION['user_id'];

// Get search parameter
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Get combined payments (orders + manual payments)
$combinedPayments = getCombinedPaymentsForUser($conn, $userId);

$pageTitle = "My Orders & Payments";
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">My Orders & Payments</h1>
            <p class="page-subtitle">View and manage your hosting orders and payments</p>
        </div>
        <a href="../select-package.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>
            New Order
        </a>
    </div>
</div>

<!-- Search -->
<?php if (!empty($combinedPayments)): ?>
    <div class="search-container">
        <form method="GET" class="d-flex">
            <div class="position-relative flex-fill">
                <i class="bi bi-search search-icon"></i>
                <input type="text" 
                       class="form-control search-input" 
                       name="search" 
                       placeholder="Search orders and payments..."
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <button type="submit" class="btn btn-primary ms-2">
                <i class="bi bi-search"></i>
            </button>
            <?php if (!empty($search)): ?>
                <a href="orders.php" class="btn btn-secondary ms-2">
                    <i class="bi bi-x"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>
<?php endif; ?>

<!-- Orders & Payments Table -->
<?php if (!empty($combinedPayments)): ?>
    <div class="content-card">
        <div class="table-responsive">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($combinedPayments as $payment): ?>
                        <tr>
                            <td>
                                <span class="badge <?php echo $payment['type'] === 'order' ? 'bg-primary' : 'bg-secondary'; ?>">
                                    <?php echo $payment['type'] === 'order' ? 'Order' : 'Manual'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="user-name"><?php echo htmlspecialchars($payment['reference']); ?></span>
                            </td>
                            <td>
                                <span class="user-email"><?php echo htmlspecialchars($payment['description'] ?? $payment['reason']); ?></span>
                            </td>
                            <td>
                                <span class="user-email">₹<?php echo number_format($payment['amount'], 2); ?></span>
                            </td>
                            <td>
                                <span class="order-status <?php echo $payment['status']; ?>">
                                    <?php echo ucfirst($payment['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="user-email"><?php echo date('M d, Y', strtotime($payment['order_date'])); ?></span>
                            </td>
                            <td>
                                <?php if ($payment['start_date']): ?>
                                    <span class="user-email"><?php echo date('M d, Y', strtotime($payment['start_date'])); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($payment['end_date']): ?>
                                    <span class="user-email"><?php echo date('M d, Y', strtotime($payment['end_date'])); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <?php if ($payment['type'] === 'order'): ?>
                                        <a href="orders.php?id=<?php echo $payment['id']; ?>" class="btn-action" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if ($payment['status'] === 'pending'): ?>
                                            <a href="../payment-handler.php?order_id=<?php echo $payment['id']; ?>" class="btn-action" title="Pay Now">
                                                <i class="bi bi-credit-card"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="../admin/invoice.php?id=<?php echo $payment['id']; ?>&download=1" class="btn-action" title="Download Invoice">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="../admin/manual_payment_invoice.php?id=<?php echo $payment['id']; ?>" class="btn-action" title="View Invoice">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </a>
                                        <a href="../admin/manual_payment_invoice.php?id=<?php echo $payment['id']; ?>&download=1" class="btn-action" title="Download Invoice">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <!-- No Orders -->
    <div class="content-card text-center">
        <div class="py-5">
            <i class="bi bi-cart" style="font-size: 64px; color: #9ca3af; margin-bottom: 16px;"></i>
            <h3 class="card-title">No Orders or Payments Found</h3>
            <p class="card-subtitle">
                <?php if (!empty($search)): ?>
                    No orders or payments match your search criteria.
                <?php else: ?>
                    You haven't placed any orders or received any payments yet.
                <?php endif; ?>
            </p>
            <a href="../select-package.php" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle me-2"></i>
                Order Hosting
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- Order Details Modal (if viewing specific order) -->
<?php if (isset($_GET['id'])): ?>
    <?php
    $orderId = (int)$_GET['id'];
    if (canUserAccessOrder($conn, $userId, $orderId)) {
        $stmt = $conn->prepare("
            SELECT ho.*, hp.name as package_name, hp.description as package_description,
                   c.code as coupon_code
            FROM hosting_orders ho 
            LEFT JOIN hosting_packages hp ON ho.package_id = hp.id 
            LEFT JOIN coupons c ON ho.coupon_id = c.id
            WHERE ho.id = ?
        ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $orderDetails = $result->fetch_assoc();
        $stmt->close();
    } else {
        $orderDetails = null;
    }
    ?>
    
    <?php if ($orderDetails): ?>
        <div class="modal fade show" style="display: block;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Order Details - <?php echo htmlspecialchars($orderDetails['order_number']); ?></h5>
                        <a href="orders.php" class="btn-close"></a>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Package Name</label>
                                <div class="form-control-plaintext"><?php echo htmlspecialchars($orderDetails['package_name'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Billing Cycle</label>
                                <div class="form-control-plaintext"><?php echo ucfirst($orderDetails['billing_cycle']); ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Base Price</label>
                                <div class="form-control-plaintext"><?php echo formatCurrency($orderDetails['base_price']); ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Setup Fee</label>
                                <div class="form-control-plaintext"><?php echo formatCurrency($orderDetails['setup_fee']); ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">GST Amount</label>
                                <div class="form-control-plaintext"><?php echo formatCurrency($orderDetails['gst_amount']); ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Processing Fee</label>
                                <div class="form-control-plaintext"><?php echo formatCurrency($orderDetails['processing_fee']); ?></div>
                            </div>
                            <?php if (!empty($orderDetails['discount_amount']) && $orderDetails['discount_amount'] > 0): ?>
                            <div class="col-md-6">
                                <label class="form-label">Coupon Discount<?php echo !empty($orderDetails['coupon_code']) ? ' (' . htmlspecialchars($orderDetails['coupon_code']) . ')' : ''; ?></label>
                                <div class="form-control-plaintext" style="color:#10B981; font-weight:600;">-<?php echo formatCurrency($orderDetails['discount_amount']); ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-6">
                                <label class="form-label">Total Amount</label>
                                <div class="form-control-plaintext fw-bold"><?php echo formatCurrency($orderDetails['total_amount']); ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Status</label>
                                <div class="form-control-plaintext">
                                    <span class="order-status <?php echo $orderDetails['payment_status']; ?>">
                                        <?php echo ucfirst($orderDetails['payment_status']); ?>
                                    </span>
                                </div>
                            </div>
                            <?php if ($orderDetails['domain_name']): ?>
                                <div class="col-md-6">
                                    <label class="form-label">Domain Name</label>
                                    <div class="form-control-plaintext"><?php echo htmlspecialchars($orderDetails['domain_name']); ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if ($orderDetails['expiry_date']): ?>
                                <div class="col-md-6">
                                    <label class="form-label">Expiry Date</label>
                                    <div class="form-control-plaintext"><?php echo formatDate($orderDetails['expiry_date']); ?></div>
                                </div>
                            <?php endif; ?>
                            <div class="col-md-6">
                                <label class="form-label">Order Date</label>
                                <div class="form-control-plaintext"><?php echo formatDate($orderDetails['created_at']); ?></div>
                            </div>
                            <?php if ($orderDetails['notes']): ?>
                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <div class="form-control-plaintext"><?php echo htmlspecialchars($orderDetails['notes']); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <?php if ($orderDetails['payment_status'] === 'pending'): ?>
                            <a href="../payment-handler.php?order_id=<?php echo $orderDetails['id']; ?>" class="btn btn-primary">
                                <i class="bi bi-credit-card me-1"></i>
                                Pay Now
                            </a>
                        <?php endif; ?>
                        <a href="../admin/invoice.php?id=<?php echo $orderDetails['id']; ?>&download=1" class="btn btn-info">
                            <i class="bi bi-file-earmark-text me-1"></i>
                            Download Invoice
                        </a>
                        <a href="orders.php" class="btn btn-secondary">Close</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
