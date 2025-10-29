<?php
require_once 'config.php';
require_once 'components/auth_helper.php';
require_once 'components/hosting_helper.php';
require_once 'components/flash_message.php';

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
    
    setFlashMessage('success', 'Payment successful! Your hosting account has been activated.');
    redirect('user/index.php');
} else {
    // Payment failed
    updateOrderStatus($conn, $orderId, 'pending');
    updatePaymentStatus($conn, $orderId, 'pending');
    
    setFlashMessage('error', 'Payment failed. Please try again.');
    redirect('checkout.php?package=' . getPackageById($conn, $order['package_id'])['slug'] . '&cycle=' . $order['billing_cycle']);
}

