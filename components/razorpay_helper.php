<?php
/**
 * Razorpay Helper Functions
 * Payment gateway integration
 */

/**
 * Get Razorpay settings from database
 */
function getRazorpaySettings($conn) {
    $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_group = 'payment'");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $settings = [];
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    $stmt->close();
    
    return $settings;
}

/**
 * Check if Razorpay is enabled
 */
function isRazorpayEnabled($conn) {
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'razorpay_enabled'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row && $row['setting_value'] == '1';
}

/**
 * Get Razorpay Key ID
 */
function getRazorpayKeyId($conn) {
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'razorpay_key_id'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row ? $row['setting_value'] : '';
}

/**
 * Get Razorpay Key Secret
 */
function getRazorpayKeySecret($conn) {
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'razorpay_key_secret'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row ? $row['setting_value'] : '';
}

/**
 * Initialize Razorpay API
 * Note: Requires Razorpay SDK installation via composer
 * Run: composer require razorpay/razorpay
 */
function initializeRazorpay($conn) {
    $keyId = getRazorpayKeyId($conn);
    $keySecret = getRazorpayKeySecret($conn);
    
    if (empty($keyId) || empty($keySecret)) {
        return null;
    }
    
    // Check if Razorpay SDK is available
    if (!class_exists('Razorpay\Api\Api')) {
        // Fallback: Return API key for frontend
        return [
            'key_id' => $keyId,
            'key_secret' => $keySecret,
            'enabled' => isRazorpayEnabled($conn),
            'sdk_installed' => false
        ];
    }
    
    try {
        require __DIR__ . '/../vendor/autoload.php';
        $api = new Razorpay\Api\Api($keyId, $keySecret);
        
        return [
            'api' => $api,
            'key_id' => $keyId,
            'key_secret' => $keySecret,
            'enabled' => isRazorpayEnabled($conn),
            'sdk_installed' => true
        ];
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Create Razorpay Order
 * Creates an order on Razorpay servers
 */
function createRazorpayOrder($conn, $amount, $orderId, $customerData) {
    $razorpay = initializeRazorpay($conn);
    
    if (!$razorpay || !$razorpay['sdk_installed']) {
        return [
            'success' => false,
            'message' => 'Razorpay SDK not installed. Please run: composer require razorpay/razorpay'
        ];
    }
    
    $api = $razorpay['api'];
    
    try {
        $orderData = [
            'receipt' => 'order_' . $orderId,
            'amount' => $amount * 100, // Amount in paise
            'currency' => 'INR',
            'notes' => [
                'order_id' => $orderId,
                'customer_email' => $customerData['email'],
                'customer_name' => $customerData['name'],
                'customer_phone' => $customerData['phone'] ?? ''
            ]
        ];
        
        $razorpayOrder = $api->order->create($orderData);
        
        return [
            'success' => true,
            'razorpay_order_id' => $razorpayOrder['id'],
            'amount' => $razorpayOrder['amount'],
            'currency' => $razorpayOrder['currency'],
            'key_id' => $razorpay['key_id']
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Verify Payment Signature
 * Verifies the payment signature received from Razorpay
 */
function verifyPaymentSignature($conn, $razorpayOrderId, $razorpayPaymentId, $razorpaySignature) {
    $razorpay = initializeRazorpay($conn);
    
    if (!$razorpay || !$razorpay['sdk_installed']) {
        return false;
    }
    
    $api = $razorpay['api'];
    
    try {
        $attributes = array(
            'razorpay_order_id' => $razorpayOrderId,
            'razorpay_payment_id' => $razorpayPaymentId,
            'razorpay_signature' => $razorpaySignature
        );
        
        $api->utility->verifyPaymentSignature($attributes);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Update order with Razorpay details
 */
function updateOrderWithRazorpayPayment($conn, $orderId, $razorpayOrderId, $razorpayPaymentId, $razorpaySignature) {
    $stmt = $conn->prepare("UPDATE hosting_orders SET 
        razorpay_order_id = ?, 
        razorpay_payment_id = ?, 
        payment_status = 'paid',
        payment_id = ?,
        payment_date = NOW()
        WHERE id = ?");
    
    $stmt->bind_param("ssssi", $razorpayOrderId, $razorpayPaymentId, $razorpayPaymentId, $orderId);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

/**
 * Get payment gateway configuration for frontend
 */
function getPaymentGatewayConfig($conn) {
    return [
        'razorpay_key_id' => getRazorpayKeyId($conn),
        'razorpay_enabled' => isRazorpayEnabled($conn),
        'razorpay_currency' => 'INR'
    ];
}

?>

