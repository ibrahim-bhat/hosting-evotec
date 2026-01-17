<?php
/**
 * Cleanup Helper Functions
 * Auto-cleanup old pending orders
 */

/**
 * Clean up old pending orders
 * Cancels pending orders older than specified days
 */
function cleanupOldPendingOrders($conn, $daysOld = 7) {
    // Cancel pending orders older than X days
    $stmt = $conn->prepare("
        UPDATE hosting_orders 
        SET order_status = 'cancelled',
            updated_at = NOW()
        WHERE payment_status = 'pending' 
        AND order_status = 'pending'
        AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
    ");
    $stmt->bind_param("i", $daysOld);
    $stmt->execute();
    $affectedRows = $stmt->affected_rows;
    $stmt->close();
    
    return $affectedRows;
}

/**
 * Clean up duplicate pending orders for same user and package
 * Keeps only the latest pending order
 */
function cleanupDuplicatePendingOrders($conn, $userId = null) {
    if ($userId) {
        // Clean for specific user
        $stmt = $conn->prepare("
            UPDATE hosting_orders o1
            INNER JOIN (
                SELECT user_id, package_id, MAX(id) as latest_id
                FROM hosting_orders
                WHERE payment_status = 'pending' AND order_status = 'pending'
                AND user_id = ?
                GROUP BY user_id, package_id
                HAVING COUNT(*) > 1
            ) o2 ON o1.user_id = o2.user_id AND o1.package_id = o2.package_id
            SET o1.order_status = 'cancelled', o1.updated_at = NOW()
            WHERE o1.payment_status = 'pending' 
            AND o1.order_status = 'pending'
            AND o1.id < o2.latest_id
        ");
        $stmt->bind_param("i", $userId);
    } else {
        // Clean for all users
        $stmt = $conn->prepare("
            UPDATE hosting_orders o1
            INNER JOIN (
                SELECT user_id, package_id, MAX(id) as latest_id
                FROM hosting_orders
                WHERE payment_status = 'pending' AND order_status = 'pending'
                GROUP BY user_id, package_id
                HAVING COUNT(*) > 1
            ) o2 ON o1.user_id = o2.user_id AND o1.package_id = o2.package_id
            SET o1.order_status = 'cancelled', o1.updated_at = NOW()
            WHERE o1.payment_status = 'pending' 
            AND o1.order_status = 'pending'
            AND o1.id < o2.latest_id
        ");
    }
    
    $stmt->execute();
    $affectedRows = $stmt->affected_rows;
    $stmt->close();
    
    return $affectedRows;
}

/**
 * Cancel user's other pending orders after successful payment
 */
function cancelUserPendingOrders($conn, $userId, $excludeOrderId = null) {
    if ($excludeOrderId) {
        $stmt = $conn->prepare("
            UPDATE hosting_orders 
            SET order_status = 'cancelled',
                updated_at = NOW()
            WHERE user_id = ? 
            AND id != ?
            AND payment_status = 'pending'
            AND order_status = 'pending'
        ");
        $stmt->bind_param("ii", $userId, $excludeOrderId);
    } else {
        $stmt = $conn->prepare("
            UPDATE hosting_orders 
            SET order_status = 'cancelled',
                updated_at = NOW()
            WHERE user_id = ? 
            AND payment_status = 'pending'
            AND order_status = 'pending'
        ");
        $stmt->bind_param("i", $userId);
    }
    
    $stmt->execute();
    $affectedRows = $stmt->affected_rows;
    $stmt->close();
    
    return $affectedRows;
}

/**
 * Update expired orders status
 */
function updateExpiredOrders($conn) {
    $stmt = $conn->prepare("
        UPDATE hosting_orders 
        SET order_status = 'expired',
            updated_at = NOW()
        WHERE order_status = 'active' 
        AND payment_status = 'paid'
        AND expiry_date < CURDATE()
    ");
    
    $stmt->execute();
    $affectedRows = $stmt->affected_rows;
    $stmt->close();
    
    return $affectedRows;
}

/**
 * Run all cleanup tasks
 */
function runAllCleanupTasks($conn, $userId = null) {
    $results = [
        'old_pending' => cleanupOldPendingOrders($conn, 7),
        'duplicate_pending' => cleanupDuplicatePendingOrders($conn, $userId),
        'expired_orders' => updateExpiredOrders($conn)
    ];
    
    return $results;
}
?>
