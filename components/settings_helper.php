<?php
/**
 * Settings Helper Functions
 * Functions to retrieve and manage system settings
 */

/**
 * Get a single setting value by key
 */
function getSetting($conn, $key, $default = '') {
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row ? $row['setting_value'] : $default;
}

/**
 * Get all settings as an associative array
 */
function getAllSettings($conn) {
    $query = "SELECT setting_key, setting_value FROM settings";
    $result = $conn->query($query);
    
    $settings = [];
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    return $settings;
}

/**
 * Get settings by group
 */
function getSettingsByGroup($conn, $group) {
    $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_group = ?");
    $stmt->bind_param("s", $group);
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
 * Update a setting value
 */
function updateSetting($conn, $key, $value) {
    $stmt = $conn->prepare("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
    $stmt->bind_param("ss", $value, $key);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

/**
 * Get company name from settings
 */
function getCompanyName($conn) {
    return getSetting($conn, 'company_name', 'SecureAuth');
}

/**
 * Get company logo from settings
 */
function getCompanyLogo($conn) {
    return getSetting($conn, 'company_logo', '');
}
?>
