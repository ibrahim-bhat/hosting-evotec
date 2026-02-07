<?php
/**
 * Run Migration: Add Admin Plans and Server Credentials
 * This script applies the database changes for renewal pricing and private packages
 */

require_once 'config.php';

echo "<!DOCTYPE html><html><head><title>Running Migration</title>";
echo "<style>body{font-family:Arial;padding:40px;max-width:800px;margin:0 auto;}";
echo ".success{color:green;}.error{color:red;}.info{color:blue;}</style></head><body>";
echo "<h1>Database Migration</h1>";

// Read the migration file
$migrationFile = __DIR__ . '/migrations/add_admin_plans_and_server_credentials.sql';

if (!file_exists($migrationFile)) {
    echo "<p class='error'>❌ Migration file not found!</p>";
    exit;
}

$sql = file_get_contents($migrationFile);

// Split by semicolons to execute each statement separately
$statements = array_filter(array_map('trim', explode(';', $sql)));

echo "<h2>Executing Migration...</h2>";
echo "<ul>";

$success = 0;
$failed = 0;

foreach ($statements as $statement) {
    // Skip empty statements and comments
    if (empty($statement) || strpos($statement, '--') === 0) {
        continue;
    }
    
    // Execute the statement
    if ($conn->query($statement)) {
        $success++;
        // Extract table/action from statement for display
        preg_match('/ALTER TABLE `?(\w+)`?/i', $statement, $matches);
        $table = $matches[1] ?? 'unknown';
        echo "<li class='success'>✓ Modified table: <strong>$table</strong></li>";
    } else {
        $failed++;
        $error = $conn->error;
        echo "<li class='error'>✗ Failed: " . htmlspecialchars($error) . "</li>";
        echo "<pre style='background:#f5f5f5;padding:10px;margin:5px 0;'>" . htmlspecialchars(substr($statement, 0, 200)) . "...</pre>";
    }
}

echo "</ul>";

if ($failed > 0) {
    echo "<h3 class='error'>⚠️ Migration completed with errors</h3>";
    echo "<p>Successful: $success | Failed: $failed</p>";
} else {
    echo "<h3 class='success'>✅ Migration completed successfully!</h3>";
    echo "<p>Applied $success database changes</p>";
}

echo "<hr>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Go to <a href='admin/packages.php'>Package Management</a></li>";
echo "<li>Edit a package to see the new renewal pricing fields</li>";
echo "<li>Delete this file (run_migration.php) for security</li>";
echo "</ol>";

echo "</body></html>";

$conn->close();
?>
