<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hosting');

// Site Configuration
define('SITE_URL', 'http://localhost/hosting');
define('SITE_NAME', 'SecureAuth');

// Razorpay Configuration - Load from database
function getRazorpayKeyId() {
    global $conn;
    if ($conn) {
        $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'razorpay_key_id'");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['setting_value'];
        }
        $stmt->close();
    }
    return 'YOUR_RAZORPAY_KEY_ID';
}

function getRazorpayKeySecret() {
    global $conn;
    if ($conn) {
        $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'razorpay_key_secret'");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['setting_value'];
        }
        $stmt->close();
    }
    return 'YOUR_RAZORPAY_KEY_SECRET';
}

// Note: Don't call functions at global scope
// These will be called when needed
define('RAZORPAY_CURRENCY', 'INR');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Session Configuration - Fetch from database settings
$sessionTimeoutMinutes = 30; // Default fallback
$stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'session_timeout'");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $sessionTimeoutMinutes = (int)$row['setting_value'];
    }
    $stmt->close();
}
define('SESSION_LIFETIME', $sessionTimeoutMinutes * 60); // Convert minutes to seconds
?>
