<?php
/**
 * User Dashboard Helper Functions
 */

/**
 * Get user's hosting orders
 */
function getUserOrders($conn, $userId, $search = '', $limit = null, $offset = 0) {
    if (!empty($search)) {
        $searchTerm = "%{$search}%";
        $stmt = $conn->prepare("
            SELECT ho.*, hp.name as package_name 
            FROM hosting_orders ho 
            LEFT JOIN hosting_packages hp ON ho.package_id = hp.id 
            WHERE ho.user_id = ? AND (ho.order_number LIKE ? OR hp.name LIKE ?)
            ORDER BY ho.created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("issii", $userId, $searchTerm, $searchTerm, $limit, $offset);
    } else {
        if ($limit) {
            $stmt = $conn->prepare("
                SELECT ho.*, hp.name as package_name 
                FROM hosting_orders ho 
                LEFT JOIN hosting_packages hp ON ho.package_id = hp.id 
                WHERE ho.user_id = ? 
                ORDER BY ho.created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param("iii", $userId, $limit, $offset);
        } else {
            $stmt = $conn->prepare("
                SELECT ho.*, hp.name as package_name 
                FROM hosting_orders ho 
                LEFT JOIN hosting_packages hp ON ho.package_id = hp.id 
                WHERE ho.user_id = ? 
                ORDER BY ho.created_at DESC
            ");
            $stmt->bind_param("i", $userId);
        }
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $orders;
}

/**
 * Get user's websites
 */
function getUserWebsites($conn, $userId, $search = '', $limit = null, $offset = 0) {
    if (!empty($search)) {
        $searchTerm = "%{$search}%";
        $stmt = $conn->prepare("
            SELECT hw.*, hp.name as package_name 
            FROM hosting_websites hw 
            LEFT JOIN hosting_packages hp ON hw.package_id = hp.id 
            WHERE hw.user_id = ? AND (hw.website_name LIKE ? OR hw.domain_name LIKE ?)
            ORDER BY hw.created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("issii", $userId, $searchTerm, $searchTerm, $limit, $offset);
    } else {
        if ($limit) {
            $stmt = $conn->prepare("
                SELECT hw.*, hp.name as package_name 
                FROM hosting_websites hw 
                LEFT JOIN hosting_packages hp ON hw.package_id = hp.id 
                WHERE hw.user_id = ? 
                ORDER BY hw.created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param("iii", $userId, $limit, $offset);
        } else {
            $stmt = $conn->prepare("
                SELECT hw.*, hp.name as package_name 
                FROM hosting_websites hw 
                LEFT JOIN hosting_packages hp ON hw.package_id = hp.id 
                WHERE hw.user_id = ? 
                ORDER BY hw.created_at DESC
            ");
            $stmt->bind_param("i", $userId);
        }
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $websites = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $websites;
}

/**
 * Get user's hosting statistics
 */
function getUserHostingStats($conn, $userId) {
    $stats = [];
    
    // Total orders
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM hosting_orders WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_orders'] = $result->fetch_assoc()['total'];
    $stmt->close();
    
    // Active orders
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM hosting_orders WHERE user_id = ? AND order_status = 'active'");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['active_orders'] = $result->fetch_assoc()['total'];
    $stmt->close();
    
    // Total websites
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM hosting_websites WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_websites'] = $result->fetch_assoc()['total'];
    $stmt->close();
    
    // Active websites
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM hosting_websites WHERE user_id = ? AND status = 'active'");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['active_websites'] = $result->fetch_assoc()['total'];
    $stmt->close();
    
    // Pending orders
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM hosting_orders WHERE user_id = ? AND payment_status = 'pending'");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['pending_orders'] = $result->fetch_assoc()['total'];
    $stmt->close();
    
    // Total spent
    $stmt = $conn->prepare("SELECT SUM(total_amount) as total FROM hosting_orders WHERE user_id = ? AND payment_status = 'paid'");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_spent'] = $result->fetch_assoc()['total'] ?? 0;
    $stmt->close();
    
    return $stats;
}

/**
 * Get user's recent activity
 */
function getUserRecentActivity($conn, $userId, $limit = 10) {
    $activities = [];
    
    // Recent orders
    $stmt = $conn->prepare("
        SELECT 'order' as type, order_number as title, created_at, payment_status as status
        FROM hosting_orders 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->bind_param("ii", $userId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Recent websites
    $stmt = $conn->prepare("
        SELECT 'website' as type, website_name as title, created_at, status
        FROM hosting_websites 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->bind_param("ii", $userId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $websites = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Merge and sort by date
    $activities = array_merge($orders, $websites);
    usort($activities, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    return array_slice($activities, 0, $limit);
}

/**
 * Get user's upcoming renewals
 */
function getUserUpcomingRenewals($conn, $userId, $days = 30) {
    $stmt = $conn->prepare("
        SELECT ho.*, hp.name as package_name 
        FROM hosting_orders ho 
        LEFT JOIN hosting_packages hp ON ho.package_id = hp.id 
        WHERE ho.user_id = ? 
        AND ho.order_status = 'active' 
        AND ho.expiry_date IS NOT NULL 
        AND ho.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
        ORDER BY ho.expiry_date ASC
    ");
    $stmt->bind_param("ii", $userId, $days);
    $stmt->execute();
    $result = $stmt->get_result();
    $renewals = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $renewals;
}

/**
 * Get user's payment history
 */
function getUserPaymentHistory($conn, $userId, $limit = null, $offset = 0) {
    if ($limit) {
        $stmt = $conn->prepare("
            SELECT ph.*, ho.order_number, ho.total_amount
            FROM payment_history ph 
            LEFT JOIN hosting_orders ho ON ph.order_id = ho.id 
            WHERE ph.user_id = ? 
            ORDER BY ph.created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iii", $userId, $limit, $offset);
    } else {
        $stmt = $conn->prepare("
            SELECT ph.*, ho.order_number, ho.total_amount
            FROM payment_history ph 
            LEFT JOIN hosting_orders ho ON ph.order_id = ho.id 
            WHERE ph.user_id = ? 
            ORDER BY ph.created_at DESC
        ");
        $stmt->bind_param("i", $userId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $payments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $payments;
}

/**
 * Check if user can access order
 */
function canUserAccessOrder($conn, $userId, $orderId) {
    $stmt = $conn->prepare("SELECT id FROM hosting_orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $orderId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $canAccess = $result->num_rows > 0;
    $stmt->close();
    return $canAccess;
}

/**
 * Check if user can access website
 */
function canUserAccessWebsite($conn, $userId, $websiteId) {
    $stmt = $conn->prepare("SELECT id FROM hosting_websites WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $websiteId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $canAccess = $result->num_rows > 0;
    $stmt->close();
    return $canAccess;
}

/**
 * Get user's profile information
 */
function getUserProfile($conn, $userId) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

/**
 * Update user profile
 */
function updateUserProfile($conn, $userId, $name, $phone, $profilePicture = null) {
    if ($profilePicture) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, profile_picture = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $phone, $profilePicture, $userId);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $phone, $userId);
    }
    
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Update user password
 */
function updateUserPassword($conn, $userId, $newPassword) {
    $hashedPassword = hashPassword($newPassword);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $userId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Format currency for display
 */
function formatCurrency($amount, $currency = 'INR') {
    return '₹' . number_format($amount, 2);
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Get days until expiry
 */
function getDaysUntilExpiry($expiryDate) {
    $expiry = strtotime($expiryDate);
    $now = time();
    $diff = $expiry - $now;
    return max(0, floor($diff / (60 * 60 * 24)));
}

/**
 * Check if order is expiring soon
 */
function isOrderExpiringSoon($expiryDate, $days = 7) {
    return getDaysUntilExpiry($expiryDate) <= $days;
}

/**
 * Format time ago
 */
function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return $diff . ' seconds ago';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return date('M d, Y', $time);
    }
}

?>
