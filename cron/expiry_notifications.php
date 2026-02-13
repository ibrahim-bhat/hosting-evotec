<?php
/**
 * Expiry Notification Cron Script
 * 
 * Sends email reminders to users whose hosting plans are expiring soon.
 * Checks at 10, 7, 3, and 1 day(s) before expiry.
 * 
 * Usage:
 *   - Cron job:  php /path/to/hosting-evotec/cron/expiry_notifications.php
 *   - Browser:   https://domain.com/cron/expiry_notifications.php?key=YOUR_CRON_KEY
 *   - Admin URL: Callable from admin panel (checks admin session)
 * 
 * Set up a daily cron job for automatic notifications:
 *   0 8 * * * php /path/to/hosting-evotec/cron/expiry_notifications.php
 */

// Determine if running from CLI or web
$isCli = (php_sapi_name() === 'cli');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../components/auth_helper.php';
require_once __DIR__ . '/../components/settings_helper.php';
require_once __DIR__ . '/../components/hosting_helper.php';
require_once __DIR__ . '/../components/mail_helper.php';

// Security: If accessed via web, require admin session or secret key
if (!$isCli) {
    $cronKey = $_GET['key'] ?? '';
    $expectedKey = getSetting($conn, 'cron_secret_key', '');
    
    $authorized = false;
    
    // Check cron key
    if (!empty($expectedKey) && $cronKey === $expectedKey) {
        $authorized = true;
    }
    
    // Check admin session
    if (!$authorized && isLoggedIn() && isAdmin()) {
        $authorized = true;
    }
    
    if (!$authorized) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    header('Content-Type: application/json');
}

// Notification intervals (days before expiry)
$intervals = [10, 7, 3, 1];

$sent = 0;
$skipped = 0;
$errors = 0;
$details = [];

foreach ($intervals as $daysBefore) {
    // Find active orders expiring in exactly $daysBefore days
    $stmt = $conn->prepare("
        SELECT ho.*, hp.name as package_name, hp.slug as package_slug,
               u.id as uid, u.name as user_name, u.email as user_email
        FROM hosting_orders ho
        JOIN hosting_packages hp ON ho.package_id = hp.id
        JOIN users u ON ho.user_id = u.id
        WHERE ho.order_status = 'active'
          AND ho.payment_status = 'paid'
          AND DATEDIFF(ho.expiry_date, CURDATE()) = ?
    ");
    $stmt->bind_param("i", $daysBefore);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Check if notification already sent for this order + interval
        $checkStmt = $conn->prepare("SELECT id FROM expiry_notifications WHERE order_id = ? AND days_before = ?");
        $checkStmt->bind_param("ii", $row['id'], $daysBefore);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $skipped++;
            $checkStmt->close();
            continue;
        }
        $checkStmt->close();
        
        // Build user and package arrays for the mail function
        $user = [
            'id' => $row['uid'],
            'name' => $row['user_name'],
            'email' => $row['user_email']
        ];
        
        $package = [
            'name' => $row['package_name'],
            'slug' => $row['package_slug']
        ];
        
        $order = [
            'id' => $row['id'],
            'order_number' => $row['order_number'],
            'expiry_date' => $row['expiry_date']
        ];
        
        // Send the email
        try {
            $mailResult = sendExpiryReminderMail($conn, $user, $order, $package, $daysBefore);
            
            if ($mailResult['success']) {
                // Log the notification
                $logStmt = $conn->prepare("INSERT INTO expiry_notifications (order_id, user_id, days_before) VALUES (?, ?, ?)");
                $logStmt->bind_param("iii", $row['id'], $row['uid'], $daysBefore);
                $logStmt->execute();
                $logStmt->close();
                
                $sent++;
                $details[] = [
                    'order' => $row['order_number'],
                    'user' => $row['user_email'],
                    'days_before' => $daysBefore,
                    'status' => 'sent'
                ];
            } else {
                $errors++;
                $details[] = [
                    'order' => $row['order_number'],
                    'user' => $row['user_email'],
                    'days_before' => $daysBefore,
                    'status' => 'failed',
                    'error' => $mailResult['message']
                ];
            }
        } catch (\Exception $e) {
            $errors++;
            $details[] = [
                'order' => $row['order_number'],
                'user' => $row['user_email'],
                'days_before' => $daysBefore,
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }
    
    $stmt->close();
}

// Also run expired order cleanup
require_once __DIR__ . '/../components/utils.php';
$expired = updateExpiredOrders($conn);

$summary = [
    'timestamp' => date('Y-m-d H:i:s'),
    'notifications_sent' => $sent,
    'skipped_duplicates' => $skipped,
    'errors' => $errors,
    'expired_orders_updated' => $expired,
    'details' => $details
];

if ($isCli) {
    echo "=== Expiry Notification Report ===" . PHP_EOL;
    echo "Time: " . $summary['timestamp'] . PHP_EOL;
    echo "Sent: {$sent}" . PHP_EOL;
    echo "Skipped (already sent): {$skipped}" . PHP_EOL;
    echo "Errors: {$errors}" . PHP_EOL;
    echo "Expired orders marked: {$expired}" . PHP_EOL;
    
    if (!empty($details)) {
        echo PHP_EOL . "Details:" . PHP_EOL;
        foreach ($details as $d) {
            echo "  - Order {$d['order']}: {$d['user']} ({$d['days_before']}d before) => {$d['status']}" . PHP_EOL;
        }
    }
} else {
    echo json_encode($summary, JSON_PRETTY_PRINT);
}
