<?php
/**
 * Coupon Helper Functions
 * CRUD and validation for the coupon/discount system
 */

/**
 * Get a coupon by its code
 */
function getCouponByCode($conn, $code) {
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    $coupon = $result->fetch_assoc();
    $stmt->close();
    return $coupon;
}

/**
 * Get a coupon by ID
 */
function getCouponById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $coupon = $result->fetch_assoc();
    $stmt->close();
    return $coupon;
}

/**
 * Get all coupons (for admin listing)
 */
function getAllCoupons($conn, $search = '') {
    $sql = "SELECT * FROM coupons";
    if (!empty($search)) {
        $sql .= " WHERE code LIKE ?";
    }
    $sql .= " ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($search)) {
        $searchTerm = '%' . $search . '%';
        $stmt->bind_param("s", $searchTerm);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $coupons = [];
    while ($row = $result->fetch_assoc()) {
        $coupons[] = $row;
    }
    $stmt->close();
    return $coupons;
}

/**
 * Validate a coupon for use by a specific user on a given order total
 *
 * @return array ['valid' => bool, 'message' => string, 'coupon' => array|null, 'discount' => float]
 */
function validateCoupon($conn, $code, $userId, $orderTotal) {
    $code = strtoupper(trim($code));
    if (empty($code)) {
        return ['valid' => false, 'message' => 'Please enter a coupon code.', 'coupon' => null, 'discount' => 0];
    }

    $coupon = getCouponByCode($conn, $code);
    if (!$coupon) {
        return ['valid' => false, 'message' => 'Invalid coupon code.', 'coupon' => null, 'discount' => 0];
    }

    // Active check
    if (!$coupon['is_active']) {
        return ['valid' => false, 'message' => 'This coupon is no longer active.', 'coupon' => null, 'discount' => 0];
    }

    // Expiry check
    if ($coupon['expiry_date'] !== null) {
        $stmt = $conn->prepare("SELECT NOW() > ? as is_expired");
        $stmt->bind_param("s", $coupon['expiry_date']);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($res['is_expired']) {
            return ['valid' => false, 'message' => 'This coupon has expired.', 'coupon' => null, 'discount' => 0];
        }
    }

    // Max uses check
    if ($coupon['max_uses'] !== null && $coupon['used_count'] >= $coupon['max_uses']) {
        return ['valid' => false, 'message' => 'This coupon has reached its usage limit.', 'coupon' => null, 'discount' => 0];
    }

    // Min order amount check
    if ($coupon['min_order_amount'] > 0 && $orderTotal < $coupon['min_order_amount']) {
        return [
            'valid' => false,
            'message' => 'Minimum order amount for this coupon is ₹' . number_format($coupon['min_order_amount'], 2),
            'coupon' => null,
            'discount' => 0
        ];
    }

    // Check if user already used this coupon (for one-time per-user coupons, check max_uses = 1)
    // This prevents the same user from using a coupon on multiple orders
    $stmt = $conn->prepare("SELECT COUNT(*) as use_count FROM coupon_usage WHERE coupon_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $coupon['id'], $userId);
    $stmt->execute();
    $usage = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($coupon['max_uses'] !== null && $coupon['max_uses'] <= $usage['use_count']) {
        return ['valid' => false, 'message' => 'You have already used this coupon.', 'coupon' => null, 'discount' => 0];
    }

    // Calculate discount
    $discount = 0;
    if ($coupon['discount_type'] === 'percentage') {
        $discount = round($orderTotal * ($coupon['discount_value'] / 100), 2);
    } else {
        $discount = min($coupon['discount_value'], $orderTotal); // can't exceed order total
    }

    return [
        'valid' => true,
        'message' => 'Coupon applied successfully!',
        'coupon' => $coupon,
        'discount' => $discount
    ];
}

/**
 * Record coupon usage after successful payment
 */
function applyCoupon($conn, $couponId, $userId, $orderId, $discountAmount) {
    // Insert usage record
    $stmt = $conn->prepare("INSERT INTO coupon_usage (coupon_id, user_id, order_id, discount_amount) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $couponId, $userId, $orderId, $discountAmount);
    $stmt->execute();
    $stmt->close();

    // Increment used_count
    $stmt = $conn->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?");
    $stmt->bind_param("i", $couponId);
    $stmt->execute();
    $stmt->close();
}

/**
 * Create a new coupon (admin)
 */
function createCoupon($conn, $data) {
    $code = strtoupper(trim($data['code']));
    $discountType = $data['discount_type'];
    $discountValue = (float)$data['discount_value'];
    $maxUses = !empty($data['max_uses']) ? (int)$data['max_uses'] : null;
    $minOrderAmount = (float)($data['min_order_amount'] ?? 0);
    $expiryDate = !empty($data['expiry_date']) ? $data['expiry_date'] : null;
    $isActive = isset($data['is_active']) ? 1 : 1; // default active on create

    $stmt = $conn->prepare("INSERT INTO coupons (code, discount_type, discount_value, max_uses, min_order_amount, expiry_date, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdidsi", $code, $discountType, $discountValue, $maxUses, $minOrderAmount, $expiryDate, $isActive);
    $success = $stmt->execute();
    $insertId = $conn->insert_id;
    $stmt->close();

    return $success ? $insertId : false;
}

/**
 * Update an existing coupon (admin)
 */
function updateCoupon($conn, $id, $data) {
    $code = strtoupper(trim($data['code']));
    $discountType = $data['discount_type'];
    $discountValue = (float)$data['discount_value'];
    $maxUses = !empty($data['max_uses']) ? (int)$data['max_uses'] : null;
    $minOrderAmount = (float)($data['min_order_amount'] ?? 0);
    $expiryDate = !empty($data['expiry_date']) ? $data['expiry_date'] : null;
    $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 0;

    $stmt = $conn->prepare("UPDATE coupons SET code = ?, discount_type = ?, discount_value = ?, max_uses = ?, min_order_amount = ?, expiry_date = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("ssdidsii", $code, $discountType, $discountValue, $maxUses, $minOrderAmount, $expiryDate, $isActive, $id);
    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

/**
 * Delete a coupon (admin)
 */
function deleteCoupon($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Get coupon usage stats
 */
function getCouponUsageCount($conn, $couponId) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM coupon_usage WHERE coupon_id = ?");
    $stmt->bind_param("i", $couponId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)$result['total'];
}
?>
