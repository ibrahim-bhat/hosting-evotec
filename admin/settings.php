<?php
require_once '../config.php';
require_once '../components/auth_helper.php';
require_once '../components/admin_helper.php';
require_once '../components/settings_helper.php';
require_once '../components/mail_helper.php';

// Check if user is logged in and is admin
requireLogin($conn);
requireAdmin($conn);

// Validate CSRF for all POSTs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_message'] = 'Invalid form submission. Please try again.';
        $_SESSION['flash_type'] = 'error';
        header('Location: settings.php');
        exit;
    }
}

// Handle test email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test_email'])) {
    $testEmail = sanitizeInput($_POST['test_email'] ?? '');
    if (!empty($testEmail) && filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        $result = sendTestMail($conn, $testEmail);
        if ($result['success']) {
            $_SESSION['flash_message'] = 'Test email sent successfully to ' . htmlspecialchars($testEmail);
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Failed to send test email: ' . htmlspecialchars($result['message']);
            $_SESSION['flash_type'] = 'error';
        }
    } else {
        $_SESSION['flash_message'] = 'Please enter a valid email address';
        $_SESSION['flash_type'] = 'error';
    }
    header('Location: settings.php');
    exit;
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $updated = 0;
    $failed = 0;
    
    foreach ($_POST as $key => $value) {
        if ($key === 'update_settings' || $key === 'csrf_token') continue;
        if (strpos($key, 'setting_') === 0) {
            $settingKey = str_replace('setting_', '', $key);
            $settingValue = is_array($value) ? json_encode($value) : sanitizeInput($value);
            
            $stmt = $conn->prepare("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
            $stmt->bind_param("ss", $settingValue, $settingKey);
            
            if ($stmt->execute()) {
                $updated++;
            } else {
                $failed++;
            }
            $stmt->close();
        }
    }
    
    // Handle file uploads
    if (!empty($_FILES)) {
        foreach ($_FILES as $key => $file) {
            if ($file['error'] === UPLOAD_ERR_OK && strpos($key, 'setting_') === 0) {
                $settingKey = str_replace('setting_', '', $key);
                $uploadDir = '../uploads/settings/';
                
                // Create directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = $settingKey . '_' . time() . '.' . $extension;
                $filepath = $uploadDir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $relativePath = 'uploads/settings/' . $filename;
                    $stmt = $conn->prepare("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
                    $stmt->bind_param("ss", $relativePath, $settingKey);
                    
                    if ($stmt->execute()) {
                        $updated++;
                    }
                    $stmt->close();
                }
            }
        }
    }
    
    if ($updated > 0) {
        $_SESSION['flash_message'] = 'Settings updated successfully!';
        $_SESSION['flash_type'] = 'success';
    } elseif ($failed > 0) {
        $_SESSION['flash_message'] = 'Failed to update some settings';
        $_SESSION['flash_type'] = 'error';
    }
    
    header('Location: settings.php');
    exit;
}

// Get all settings grouped
$settingsQuery = "SELECT * FROM settings ORDER BY setting_group, setting_key";
$settingsResult = $conn->query($settingsQuery);
$settings = [];
while ($row = $settingsResult->fetch_assoc()) {
    $settings[$row['setting_group']][] = $row;
}

$pageTitle = 'System Settings';
include 'includes/header.php';
?>

<div id="flashMessage">
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?php echo $_SESSION['flash_type'] === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill'; ?>"></i>
            <span><?php echo htmlspecialchars($_SESSION['flash_message']); ?></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php 
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        ?>
    <?php endif; ?>
</div>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">System Settings</h1>
    <p class="page-subtitle">Configure your application settings</p>
</div>

<!-- Settings Form -->
<form method="POST" enctype="multipart/form-data">
    <?php echo csrfField(); ?>
    <div class="settings-container">
        
        <!-- Company Information -->
        <?php if (isset($settings['company'])): ?>
        <div class="settings-card">
            <div class="settings-card-header">
                <i class="bi bi-building"></i>
                <h3>Company Information</h3>
            </div>
            <div class="settings-card-body">
                <?php foreach ($settings['company'] as $setting): ?>
                    <div class="form-group">
                        <label for="setting_<?php echo $setting['setting_key']; ?>" class="form-label">
                            <?php echo ucwords(str_replace('_', ' ', str_replace('company_', '', $setting['setting_key']))); ?>
                        </label>
                        
                        <?php if ($setting['setting_type'] === 'file'): ?>
                            <?php if ($setting['setting_value']): ?>
                                <div class="current-file mb-2">
                                    <img src="../<?php echo htmlspecialchars($setting['setting_value']); ?>" 
                                         alt="Current" 
                                         style="max-width: 200px; max-height: 100px; border: 1px solid #e5e7eb; border-radius: 4px; padding: 4px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" 
                                   class="form-control" 
                                   id="setting_<?php echo $setting['setting_key']; ?>" 
                                   name="setting_<?php echo $setting['setting_key']; ?>"
                                   accept="image/*">
                        <?php else: ?>
                            <input type="<?php echo $setting['setting_type'] === 'email' ? 'email' : 'text'; ?>" 
                                   class="form-control" 
                                   id="setting_<?php echo $setting['setting_key']; ?>" 
                                   name="setting_<?php echo $setting['setting_key']; ?>"
                                   value="<?php echo htmlspecialchars($setting['setting_value'] ?? ''); ?>">
                        <?php endif; ?>
                        
                        <?php if ($setting['description']): ?>
                            <small class="form-text text-muted"><?php echo htmlspecialchars($setting['description']); ?></small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- System Configuration -->
        <?php if (isset($settings['system'])): ?>
        <div class="settings-card">
            <div class="settings-card-header">
                <i class="bi bi-gear"></i>
                <h3>System Configuration</h3>
            </div>
            <div class="settings-card-body">
                <?php foreach ($settings['system'] as $setting): ?>
                    <div class="form-group">
                        <label for="setting_<?php echo $setting['setting_key']; ?>" class="form-label">
                            <?php echo ucwords(str_replace('_', ' ', $setting['setting_key'])); ?>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="setting_<?php echo $setting['setting_key']; ?>" 
                               name="setting_<?php echo $setting['setting_key']; ?>"
                               value="<?php echo htmlspecialchars($setting['setting_value'] ?? ''); ?>">
                        
                        <?php if ($setting['description']): ?>
                            <small class="form-text text-muted"><?php echo htmlspecialchars($setting['description']); ?></small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Security Settings -->
        <?php if (isset($settings['security'])): ?>
        <div class="settings-card">
            <div class="settings-card-header">
                <i class="bi bi-shield-check"></i>
                <h3>Security Settings</h3>
            </div>
            <div class="settings-card-body">
                <?php foreach ($settings['security'] as $setting): ?>
                    <div class="form-group">
                        <?php if ($setting['setting_type'] === 'boolean'): ?>
                            <div class="form-check form-switch">
                                <input type="hidden" name="setting_<?php echo $setting['setting_key']; ?>" value="0">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="setting_<?php echo $setting['setting_key']; ?>" 
                                       name="setting_<?php echo $setting['setting_key']; ?>"
                                       value="1"
                                       <?php echo $setting['setting_value'] == '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="setting_<?php echo $setting['setting_key']; ?>">
                                    <?php echo ucwords(str_replace('_', ' ', $setting['setting_key'])); ?>
                                </label>
                            </div>
                        <?php else: ?>
                            <label for="setting_<?php echo $setting['setting_key']; ?>" class="form-label">
                                <?php echo ucwords(str_replace('_', ' ', $setting['setting_key'])); ?>
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="setting_<?php echo $setting['setting_key']; ?>" 
                                   name="setting_<?php echo $setting['setting_key']; ?>"
                                   value="<?php echo htmlspecialchars($setting['setting_value'] ?? ''); ?>">
                        <?php endif; ?>
                        
                        <?php if ($setting['description']): ?>
                            <small class="form-text text-muted"><?php echo htmlspecialchars($setting['description']); ?></small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Maintenance Mode -->
        <?php if (isset($settings['maintenance'])): ?>
        <div class="settings-card">
            <div class="settings-card-header">
                <i class="bi bi-tools"></i>
                <h3>Maintenance Mode</h3>
            </div>
            <div class="settings-card-body">
                <?php foreach ($settings['maintenance'] as $setting): ?>
                    <div class="form-group">
                        <?php if ($setting['setting_type'] === 'boolean'): ?>
                            <div class="form-check form-switch">
                                <input type="hidden" name="setting_<?php echo $setting['setting_key']; ?>" value="0">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="setting_<?php echo $setting['setting_key']; ?>" 
                                       name="setting_<?php echo $setting['setting_key']; ?>"
                                       value="1"
                                       <?php echo $setting['setting_value'] == '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="setting_<?php echo $setting['setting_key']; ?>">
                                    <?php echo ucwords(str_replace('_', ' ', $setting['setting_key'])); ?>
                                </label>
                            </div>
                        <?php else: ?>
                            <label for="setting_<?php echo $setting['setting_key']; ?>" class="form-label">
                                <?php echo ucwords(str_replace('_', ' ', $setting['setting_key'])); ?>
                            </label>
                            <textarea class="form-control" 
                                      id="setting_<?php echo $setting['setting_key']; ?>" 
                                      name="setting_<?php echo $setting['setting_key']; ?>"
                                      rows="3"><?php echo htmlspecialchars($setting['setting_value'] ?? ''); ?></textarea>
                        <?php endif; ?>
                        
                        <?php if ($setting['description']): ?>
                            <small class="form-text text-muted"><?php echo htmlspecialchars($setting['description']); ?></small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Email / SMTP Settings -->
        <div class="settings-card">
            <div class="settings-card-header">
                <i class="bi bi-envelope"></i>
                <h3>Email / SMTP Settings</h3>
            </div>
            <div class="settings-card-body">
                <div class="form-group">
                    <div class="form-check form-switch">
                        <input type="hidden" name="setting_smtp_enabled" value="0">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="setting_smtp_enabled" 
                               name="setting_smtp_enabled"
                               value="1"
                               <?php echo getSetting($conn, 'smtp_enabled', '0') == '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="setting_smtp_enabled">
                            Enable Email Sending
                        </label>
                    </div>
                    <small class="form-text text-muted">Turn on/off all outgoing emails (OTP, welcome, subscription notifications)</small>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="setting_smtp_host" class="form-label">SMTP Host</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="setting_smtp_host" 
                                   name="setting_smtp_host"
                                   placeholder="e.g. smtp.gmail.com"
                                   value="<?php echo htmlspecialchars(getSetting($conn, 'smtp_host', '')); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="setting_smtp_port" class="form-label">SMTP Port</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="setting_smtp_port" 
                                   name="setting_smtp_port"
                                   placeholder="587"
                                   value="<?php echo htmlspecialchars(getSetting($conn, 'smtp_port', '587')); ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="setting_smtp_username" class="form-label">SMTP Username</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="setting_smtp_username" 
                                   name="setting_smtp_username"
                                   value="<?php echo htmlspecialchars(getSetting($conn, 'smtp_username', '')); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="setting_smtp_password" class="form-label">SMTP Password</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="setting_smtp_password" 
                                   name="setting_smtp_password"
                                   value="<?php echo htmlspecialchars(getSetting($conn, 'smtp_password', '')); ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="setting_smtp_encryption" class="form-label">Encryption</label>
                            <select class="form-select" id="setting_smtp_encryption" name="setting_smtp_encryption">
                                <option value="tls" <?php echo getSetting($conn, 'smtp_encryption', 'tls') == 'tls' ? 'selected' : ''; ?>>TLS</option>
                                <option value="ssl" <?php echo getSetting($conn, 'smtp_encryption', 'tls') == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="setting_smtp_from_email" class="form-label">From Email</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="setting_smtp_from_email" 
                                   name="setting_smtp_from_email"
                                   placeholder="noreply@example.com"
                                   value="<?php echo htmlspecialchars(getSetting($conn, 'smtp_from_email', '')); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="setting_smtp_from_name" class="form-label">From Name</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="setting_smtp_from_name" 
                                   name="setting_smtp_from_name"
                                   placeholder="InfraLabs Cloud"
                                   value="<?php echo htmlspecialchars(getSetting($conn, 'smtp_from_name', '')); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle"></i>
                    <strong>For Gmail:</strong> Use <code>smtp.gmail.com</code>, port <code>587</code>, TLS encryption. 
                    You may need to use an <a href="https://myaccount.google.com/apppasswords" target="_blank">App Password</a> instead of your regular password.
                </div>
            </div>
        </div>
        
        <!-- Payment Settings (Razorpay) -->
        <?php if (isset($settings['payment'])): ?>
        <div class="settings-card">
            <div class="settings-card-header">
                <i class="bi bi-credit-card"></i>
                <h3>Payment Gateway Settings (Razorpay)</h3>
            </div>
            <div class="settings-card-body">
                <?php foreach ($settings['payment'] as $setting): ?>
                    <?php 
                    // Skip global payment settings - they have dedicated fields below
                    if (in_array($setting['setting_key'], ['global_setup_fee', 'global_gst_percentage', 'global_processing_fee', 'currency_symbol', 'currency_code', 'renewal_markup_percentage'])) {
                        continue;
                    }
                    ?>
                    <div class="form-group">
                        <?php if ($setting['setting_type'] === 'boolean'): ?>
                            <div class="form-check form-switch">
                                <input type="hidden" name="setting_<?php echo $setting['setting_key']; ?>" value="0">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="setting_<?php echo $setting['setting_key']; ?>" 
                                       name="setting_<?php echo $setting['setting_key']; ?>"
                                       value="1"
                                       <?php echo $setting['setting_value'] == '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="setting_<?php echo $setting['setting_key']; ?>">
                                    <?php echo ucwords(str_replace('_', ' ', str_replace('razorpay_', '', $setting['setting_key']))); ?>
                                </label>
                            </div>
                        <?php else: ?>
                            <label for="setting_<?php echo $setting['setting_key']; ?>" class="form-label">
                                <?php echo ucwords(str_replace('_', ' ', str_replace('razorpay_', '', $setting['setting_key']))); ?>
                            </label>
                            <input type="<?php echo strpos($setting['setting_key'], 'key') !== false && strpos($setting['setting_key'], 'secret') !== false ? 'password' : 'text'; ?>" 
                                   class="form-control" 
                                   id="setting_<?php echo $setting['setting_key']; ?>" 
                                   name="setting_<?php echo $setting['setting_key']; ?>"
                                   value="<?php echo htmlspecialchars($setting['setting_value'] ?? ''); ?>">
                        <?php endif; ?>
                        
                        <?php if ($setting['description']): ?>
                            <small class="form-text text-muted"><?php echo htmlspecialchars($setting['description']); ?></small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle"></i>
                    <strong>How to get Razorpay Credentials:</strong>
                    <ol class="mb-0 mt-2">
                        <li>Go to <a href="https://dashboard.razorpay.com" target="_blank">Razorpay Dashboard</a></li>
                        <li>Navigate to Settings → API Keys</li>
                        <li>Generate or copy your Key ID and Key Secret</li>
                        <li>Paste them in the fields above</li>
                        <li>Enable the payment gateway</li>
                    </ol>
                </div>
                
                <!-- Global Payment Settings -->
                <hr class="my-4">
                <h5 class="mb-3">Global Payment Settings</h5>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="setting_renewal_markup_percentage" class="form-label">Renewal Markup (%)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="setting_renewal_markup_percentage" 
                                   name="setting_renewal_markup_percentage"
                                   step="0.01"
                                   min="0"
                                   max="500"
                                   value="<?php echo htmlspecialchars(getSetting($conn, 'renewal_markup_percentage', '0')); ?>">
                            <small class="form-text text-muted">Markup % added to base price on renewals (e.g. 60% means 1000 renews at 1600)</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="setting_global_setup_fee" class="form-label">Setup Fee (%)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="setting_global_setup_fee" 
                                   name="setting_global_setup_fee"
                                   step="0.01"
                                   min="0"
                                   max="100"
                                   value="<?php echo htmlspecialchars(getSetting($conn, 'global_setup_fee', '0.00')); ?>">
                            <small class="form-text text-muted">One-time setup fee % applied to new orders (calculated on base price)</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="setting_global_gst_percentage" class="form-label">GST Percentage (%)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="setting_global_gst_percentage" 
                                   name="setting_global_gst_percentage"
                                   step="0.01"
                                   min="0"
                                   max="100"
                                   value="<?php echo htmlspecialchars(getSetting($conn, 'global_gst_percentage', '18.00')); ?>">
                            <small class="form-text text-muted">GST percentage applied to all orders</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="setting_global_processing_fee" class="form-label">Processing Fee (%)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="setting_global_processing_fee" 
                                   name="setting_global_processing_fee"
                                   step="0.01"
                                   min="0"
                                   max="100"
                                   value="<?php echo htmlspecialchars(getSetting($conn, 'global_processing_fee', '0.00')); ?>">
                            <small class="form-text text-muted">Processing fee % applied to all orders (calculated on base price)</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="setting_currency_symbol" class="form-label">Currency Symbol</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="setting_currency_symbol" 
                                   name="setting_currency_symbol"
                                   value="<?php echo htmlspecialchars(getSetting($conn, 'currency_symbol', '₹')); ?>">
                            <small class="form-text text-muted">Currency symbol for display (e.g., ₹, $, €)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="setting_currency_code" class="form-label">Currency Code</label>
                            <select class="form-select" id="setting_currency_code" name="setting_currency_code">
                                <option value="INR" <?php echo getSetting($conn, 'currency_code', 'INR') == 'INR' ? 'selected' : ''; ?>>INR - Indian Rupee</option>
                                <option value="USD" <?php echo getSetting($conn, 'currency_code', 'INR') == 'USD' ? 'selected' : ''; ?>>USD - US Dollar</option>
                                <option value="EUR" <?php echo getSetting($conn, 'currency_code', 'INR') == 'EUR' ? 'selected' : ''; ?>>EUR - Euro</option>
                                <option value="GBP" <?php echo getSetting($conn, 'currency_code', 'INR') == 'GBP' ? 'selected' : ''; ?>>GBP - British Pound</option>
                            </select>
                            <small class="form-text text-muted">Currency code for transactions</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Save Button -->
    <div class="settings-actions">
        <button type="submit" name="update_settings" class="btn-save-settings">
            <i class="bi bi-check-circle"></i> Save All Settings
        </button>
    </div>
</form>

<!-- Send Test Email (separate form) -->
<form method="POST">
    <?php echo csrfField(); ?>
    <div class="settings-container">
        <div class="settings-card">
            <div class="settings-card-header">
                <i class="bi bi-send"></i>
                <h3>Send Test Email</h3>
            </div>
            <div class="settings-card-body">
                <p class="text-muted mb-3">Save your SMTP settings above first, then send a test email to verify the configuration.</p>
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <div class="form-group mb-0">
                            <label for="test_email" class="form-label">Recipient Email</label>
                            <input type="email" class="form-control" id="test_email" name="test_email" placeholder="test@example.com" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="send_test_email" class="btn btn-primary w-100">
                            <i class="bi bi-send me-1"></i> Send Test Email
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php include 'includes/footer.php'; ?>
