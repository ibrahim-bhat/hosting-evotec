<?php
/**
 * Check if user is logged in and redirect if not (admin specific)
 */
function requireAdminLogin() {
    global $conn;
    
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please login to access this page');
        redirect('../login.php');
    }
    
    // Check if user is blocked
    $user = getUserById($conn, $_SESSION['user_id']);
    if ($user && $user['status'] === 'blocked') {
        destroyUserSession();
        setFlashMessage('error', 'Your account has been blocked. Please contact support.');
        redirect('../login.php');
    }
}

/**
 * Check if user is admin and redirect if not
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        setFlashMessage('error', 'Access denied. Admin privileges required.');
        redirect('index.php');
    }
}

/**
 * Get all users from database
 */
function getAllUsers($conn, $search = '', $limit = null, $offset = 0) {
    if (!empty($search)) {
        $searchTerm = "%{$search}%";
        $stmt = $conn->prepare("SELECT * FROM users WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bind_param("sssii", $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
    } else {
        if ($limit) {
            $stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $stmt->bind_param("ii", $limit, $offset);
        } else {
            $stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC");
        }
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $users;
}

/**
 * Count total users
 */
function countUsers($conn, $search = '') {
    if (!empty($search)) {
        $searchTerm = "%{$search}%";
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?");
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['total'];
}

/**
 * Count users by status
 */
function countUsersByStatus($conn, $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE status = ?");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['total'];
}

/**
 * Update user status
 */
function updateUserStatus($conn, $userId, $status) {
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $userId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Update user role
 */
function updateUserRole($conn, $userId, $role) {
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $userId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Delete user
 */
function deleteUser($conn, $userId) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Get recent user activities
 */
function getRecentUsers($conn, $limit = 5) {
    $stmt = $conn->prepare("SELECT name, created_at FROM users ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $users;
}

/**
 * Calculate activity rate
 */
function calculateActivityRate($conn) {
    $total = countUsers($conn);
    $active = countUsersByStatus($conn, 'active');
    
    if ($total == 0) return 0;
    return round(($active / $total) * 100, 1);
}

/**
 * Get percentage change for stats
 */
function getPercentageChange($current, $previous) {
    if ($previous == 0) return 0;
    return round((($current - $previous) / $previous) * 100, 1);
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
        return floor($diff / 60) . ' hours ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return date('M d, Y', $time);
    }
}
?>
