<?php
/**
 * Automatic Cleanup Script
 * Removes old pending orders and payments
 * Can be run via cron job or manually
 */

require_once 'config.php';
require_once 'components/hosting_helper.php';

// Run cleanup
$cleanupResult = runAutomaticCleanup($conn);

// Log the results
$logMessage = sprintf(
    "Cleanup completed at %s - Orders deleted: %d, Payments deleted: %d",
    date('Y-m-d H:i:s'),
    $cleanupResult['orders']['deleted_count'],
    $cleanupResult['payments']['deleted_count']
);

// Write to log file
file_put_contents('cleanup.log', $logMessage . PHP_EOL, FILE_APPEND | LOCK_EX);

// If run from command line, output results
if (php_sapi_name() === 'cli') {
    echo $logMessage . PHP_EOL;
} else {
    // If run from web, return JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $logMessage,
        'details' => $cleanupResult
    ]);
}
?>
