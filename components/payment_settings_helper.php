<?php
/**
 * Payment Settings Helper Functions
 * Functions for managing global payment settings
 */

/**
 * Get global payment settings
 */
function getGlobalPaymentSettings($conn) {
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
 * Get global setup fee percentage
 */
function getGlobalSetupFee($conn) {
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'global_setup_fee'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row ? floatval($row['setting_value']) : 0.00;
}

/**
 * Get global GST percentage
 */
function getGlobalGstPercentage($conn) {
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'global_gst_percentage'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row ? floatval($row['setting_value']) : 18.00;
}

/**
 * Get global processing fee percentage
 */
function getGlobalProcessingFee($conn) {
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'global_processing_fee'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row ? floatval($row['setting_value']) : 0.00;
}

/**
 * Get currency settings
 */
function getCurrencySettings($conn) {
    $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('currency_symbol', 'currency_code')");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $settings = [];
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    $stmt->close();
    
    return [
        'symbol' => $settings['currency_symbol'] ?? '₹',
        'code' => $settings['currency_code'] ?? 'INR'
    ];
}

/**
 * Update global payment settings
 */
function updateGlobalPaymentSettings($conn, $settings) {
    $success = true;
    
    foreach ($settings as $key => $value) {
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value, setting_type, setting_group, description, is_public) 
                               VALUES (?, ?, 'decimal', 'payment', 'Global payment setting', 0) 
                               ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("sss", $key, $value, $value);
        if (!$stmt->execute()) {
            $success = false;
        }
        $stmt->close();
    }
    
    return $success;
}

/**
 * Calculate order total with global settings
 */
function calculateOrderTotalWithGlobalSettings($conn, $basePrice, $isRenewal = false) {
    $setupFeePercentage = $isRenewal ? 0 : getGlobalSetupFee($conn);
    $gstPercentage = getGlobalGstPercentage($conn);
    $processingFeePercentage = getGlobalProcessingFee($conn);
    
    // Calculate setup fee as percentage of base price
    $setupFeeAmount = ($basePrice * $setupFeePercentage) / 100;
    
    // Calculate processing fee as percentage of base price
    $processingFeeAmount = ($basePrice * $processingFeePercentage) / 100;
    
    // Subtotal = base + setup + processing (taxable value)
    $subtotal = $basePrice + $setupFeeAmount + $processingFeeAmount;
    
    // GST applies on the full subtotal (all fees are part of the taxable service)
    $gstAmount = ($subtotal * $gstPercentage) / 100;
    
    $total = $subtotal + $gstAmount;
    
    return [
        'base_price' => $basePrice,
        'setup_fee' => round($setupFeeAmount, 2),
        'setup_fee_percentage' => $setupFeePercentage,
        'subtotal' => round($subtotal, 2),
        'gst_amount' => round($gstAmount, 2),
        'gst_percentage' => $gstPercentage,
        'processing_fee' => round($processingFeeAmount, 2),
        'processing_fee_percentage' => $processingFeePercentage,
        'total_amount' => round($total, 2)
    ];
}

/**
 * Format currency for display
 */
function formatCurrencyWithSettings($conn, $amount) {
    $currency = getCurrencySettings($conn);
    return $currency['symbol'] . number_format($amount, 2);
}

// ========== MANUAL PAYMENTS MANAGEMENT ==========

/**
 * Create manual payment
 */
function createManualPayment($conn, $data) {
    $stmt = $conn->prepare("INSERT INTO manual_payments (
        user_id, order_id, payment_reason, payment_amount, currency, 
        payment_method, payment_status, order_date, start_date, end_date,
        description, admin_notes, created_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("iissssssssssi",
        $data['user_id'], $data['order_id'], $data['payment_reason'], 
        $data['payment_amount'], $data['currency'], $data['payment_method'],
        $data['payment_status'], $data['order_date'], $data['start_date'], 
        $data['end_date'], $data['description'], $data['admin_notes'], 
        $data['created_by']
    );
    
    $success = $stmt->execute();
    $paymentId = $conn->insert_id;
    $stmt->close();
    return $success ? $paymentId : false;
}

/**
 * Get manual payments by user
 */
function getManualPaymentsByUser($conn, $userId, $limit = null, $offset = 0) {
    $sql = "SELECT mp.*, u.name as created_by_name 
            FROM manual_payments mp 
            LEFT JOIN users u ON mp.created_by = u.id 
            WHERE mp.user_id = ? 
            ORDER BY mp.order_date DESC, mp.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT ? OFFSET ?";
    }
    
    $stmt = $conn->prepare($sql);
    if ($limit) {
        $stmt->bind_param("iii", $userId, $limit, $offset);
    } else {
        $stmt->bind_param("i", $userId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $payments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $payments;
}

/**
 * Get all manual payments (admin)
 */
function getAllManualPayments($conn, $search = '', $userId = null, $limit = null, $offset = 0) {
    $sql = "SELECT mp.*, u.name as user_name, u.email as user_email, 
                   admin.name as created_by_name, ho.order_number
            FROM manual_payments mp 
            LEFT JOIN users u ON mp.user_id = u.id 
            LEFT JOIN users admin ON mp.created_by = admin.id
            LEFT JOIN hosting_orders ho ON mp.order_id = ho.id
            WHERE 1=1";
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $sql .= " AND (mp.payment_reason LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR ho.order_number LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ssss';
    }
    
    if ($userId) {
        $sql .= " AND mp.user_id = ?";
        $params[] = $userId;
        $types .= 'i';
    }
    
    $sql .= " ORDER BY mp.order_date DESC, mp.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
    }
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $payments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $payments;
}

/**
 * Get manual payment by ID
 */
function getManualPaymentById($conn, $paymentId) {
    $stmt = $conn->prepare("SELECT mp.*, u.name as user_name, u.email as user_email, 
                                   admin.name as created_by_name, ho.order_number
                            FROM manual_payments mp 
                            LEFT JOIN users u ON mp.user_id = u.id 
                            LEFT JOIN users admin ON mp.created_by = admin.id
                            LEFT JOIN hosting_orders ho ON mp.order_id = ho.id
                            WHERE mp.id = ?");
    $stmt->bind_param("i", $paymentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();
    $stmt->close();
    return $payment;
}

/**
 * Update manual payment
 */
function updateManualPayment($conn, $paymentId, $data) {
    $stmt = $conn->prepare("UPDATE manual_payments SET 
        payment_reason = ?, payment_amount = ?, currency = ?, 
        payment_status = ?, order_date = ?, start_date = ?, end_date = ?,
        description = ?, admin_notes = ?
        WHERE id = ?");
    
    $stmt->bind_param("sdsssssssi",
        $data['payment_reason'], $data['payment_amount'], $data['currency'],
        $data['payment_status'], $data['order_date'], $data['start_date'], 
        $data['end_date'], $data['description'], $data['admin_notes'],
        $paymentId
    );
    
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Delete manual payment
 */
function deleteManualPayment($conn, $paymentId) {
    $stmt = $conn->prepare("DELETE FROM manual_payments WHERE id = ?");
    $stmt->bind_param("i", $paymentId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Get combined payments (orders + manual payments) for user
 */
function getCombinedPaymentsForUser($conn, $userId, $limit = null, $offset = 0) {
    // Get hosting orders
    $ordersSql = "SELECT 'order' as type, ho.id, ho.order_number as reference, 
                         ho.total_amount as amount, ho.payment_status as status,
                         ho.created_at as order_date, ho.start_date, ho.expiry_date as end_date,
                         hp.name as description, 'Hosting Order' as reason
                  FROM hosting_orders ho 
                  LEFT JOIN hosting_packages hp ON ho.package_id = hp.id 
                  WHERE ho.user_id = ?";
    
    // Get manual payments
    $manualSql = "SELECT 'manual' as type, mp.id, mp.payment_reason as reference,
                         mp.payment_amount as amount, mp.payment_status as status,
                         mp.order_date, mp.start_date, mp.end_date,
                         mp.description, mp.payment_reason as reason
                  FROM manual_payments mp 
                  WHERE mp.user_id = ?";
    
    // Combine with UNION
    $sql = "($ordersSql) UNION ALL ($manualSql) ORDER BY order_date DESC, id DESC";
    
    if ($limit) {
        $sql .= " LIMIT ? OFFSET ?";
    }
    
    $stmt = $conn->prepare($sql);
    if ($limit) {
        $stmt->bind_param("iiii", $userId, $userId, $limit, $offset);
    } else {
        $stmt->bind_param("ii", $userId, $userId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $payments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $payments;
}

?>
