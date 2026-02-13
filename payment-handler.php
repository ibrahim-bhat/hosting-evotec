<?php
require_once 'config.php';
require_once 'components/auth_helper.php';
require_once 'components/hosting_helper.php';
require_once 'components/flash_message.php';
require_once 'components/cleanup_helper.php';
require_once 'components/mail_helper.php';
require_once 'components/coupon_helper.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get payment details
$orderId = intval($_POST['order_id'] ?? 0);
$paymentId = sanitizeInput($_POST['payment_id'] ?? '');
$status = sanitizeInput($_POST['status'] ?? 'failed');

if ($orderId == 0) {
    setFlashMessage('error', 'Invalid order');
    redirect('hosting.php');
}

// Get order details
$order = getOrderById($conn, $orderId);

if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    setFlashMessage('error', 'Invalid order access');
    redirect('hosting.php');
}

if ($status === 'success' && !empty($paymentId)) {
    // Payment successful
    // Update order status
    updatePaymentStatus($conn, $orderId, 'paid', $paymentId, null, null);
    
    // Update order status to active
    updateOrderStatus($conn, $orderId, 'active');
    
    // Create payment history record
    $paymentData = [
        'order_id' => $orderId,
        'user_id' => $_SESSION['user_id'],
        'payment_amount' => $order['total_amount'],
        'currency' => 'INR',
        'payment_method' => 'razorpay',
        'payment_status' => 'success',
        'razorpay_order_id' => $order['order_number'],
        'razorpay_payment_id' => $paymentId,
        'razorpay_signature' => '',
        'transaction_id' => $paymentId,
        'transaction_date' => date('Y-m-d H:i:s'),
        'payment_description' => 'Payment for ' . $order['package_name'],
        'failure_reason' => ''
    ];
    
    createPaymentHistory($conn, $paymentData);
    
    // If this order is an upgrade, mark the original order as 'upgraded'
    if (!empty($order['upgraded_from_order_id'])) {
        $stmt = $conn->prepare("UPDATE hosting_orders SET order_status = 'upgraded' WHERE id = ?");
        $stmt->bind_param("i", $order['upgraded_from_order_id']);
        $stmt->execute();
        $stmt->close();
    }
    
    // Record coupon usage if a coupon was applied
    if (!empty($order['coupon_id']) && $order['coupon_id'] > 0) {
        applyCoupon($conn, $order['coupon_id'], $_SESSION['user_id'], $orderId, $order['discount_amount']);
    }
    
    // Auto-cleanup: Cancel other pending orders for this user
    cancelUserPendingOrders($conn, $_SESSION['user_id'], $orderId);
    
    // Send subscription confirmation email
    $user = getUserById($conn, $_SESSION['user_id']);
    $package = getPackageById($conn, $order['package_id']);
    // Refresh order data to get updated status
    $updatedOrder = getOrderById($conn, $orderId);
    if ($user && $package && $updatedOrder) {
        sendSubscriptionMail($conn, $user, $updatedOrder, $package);
    }
    
    setFlashMessage('success', 'Payment successful! Your hosting account has been activated.');
    redirect('user/index.php');
} else {
    // Payment failed
    updateOrderStatus($conn, $orderId, 'pending');
    updatePaymentStatus($conn, $orderId, 'pending');
    
    setFlashMessage('error', 'Payment failed. Please try again.');
    redirect('checkout.php?package=' . getPackageById($conn, $order['package_id'])['slug'] . '&cycle=' . $order['billing_cycle']);
}

