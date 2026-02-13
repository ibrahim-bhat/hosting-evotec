<?php
/**
 * AJAX endpoint for validating and applying coupons during checkout
 */
require_once 'config.php';
require_once 'components/auth_helper.php';
require_once 'components/coupon_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['valid' => false, 'message' => 'Invalid request.']);
    exit;
}

$couponCode = trim($_POST['coupon_code'] ?? '');
$orderId = intval($_POST['order_id'] ?? 0);
$orderTotal = floatval($_POST['order_total'] ?? 0);
$userId = isLoggedIn() ? (int)$_SESSION['user_id'] : 0;
$action = $_POST['action'] ?? '';

// Handle coupon removal
if ($action === 'remove' && $orderId > 0) {
    $stmt = $conn->prepare("UPDATE hosting_orders SET coupon_id = NULL, discount_amount = 0, total_amount = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("dii", $orderTotal, $orderId, $userId);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['valid' => true, 'message' => 'Coupon removed.']);
    exit;
}

if (empty($couponCode)) {
    echo json_encode(['valid' => false, 'message' => 'Please enter a coupon code.']);
    exit;
}

// Validate the coupon
$result = validateCoupon($conn, $couponCode, $userId, $orderTotal);

if ($result['valid'] && $orderId > 0) {
    // Update the order with coupon info
    $discount = $result['discount'];
    $couponId = $result['coupon']['id'];
    $newTotal = max(0, $orderTotal - $discount);

    $stmt = $conn->prepare("UPDATE hosting_orders SET coupon_id = ?, discount_amount = ?, total_amount = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iddii", $couponId, $discount, $newTotal, $orderId, $userId);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        'valid' => true,
        'message' => $result['message'],
        'discount' => $discount,
        'new_total' => $newTotal,
        'coupon_id' => $couponId,
        'discount_type' => $result['coupon']['discount_type'],
        'discount_value' => $result['coupon']['discount_value']
    ]);
} elseif ($result['valid']) {
    // Just return validation result without updating order
    echo json_encode([
        'valid' => true,
        'message' => $result['message'],
        'discount' => $result['discount'],
        'discount_type' => $result['coupon']['discount_type'],
        'discount_value' => $result['coupon']['discount_value']
    ]);
} else {
    echo json_encode([
        'valid' => false,
        'message' => $result['message']
    ]);
}
