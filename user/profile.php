<?php
require_once 'includes/header.php';
require_once '../components/user_helper.php';

$userId = $_SESSION['user_id'];

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'update_profile') {
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        
        if (empty($name)) {
            setFlashMessage('error', 'Name is required');
            redirect('profile.php');
        }
        if (empty($email)) {
            setFlashMessage('error', 'Email is required');
            redirect('profile.php');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlashMessage('error', 'Invalid email format');
            redirect('profile.php');
        }

        // Check if email is already taken by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            setFlashMessage('error', 'Email address is already in use by another account');
            redirect('profile.php');
        }
        $stmt->close();
        
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $phone, $userId);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Profile updated successfully');
            $_SESSION['user_name'] = $name;
        } else {
            setFlashMessage('error', 'Failed to update profile');
        }
        $stmt->close();
        redirect('profile.php');
    } elseif ($action === 'update_password') {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            setFlashMessage('error', 'All password fields are required');
            redirect('profile.php');
        }
        
        if ($newPassword !== $confirmPassword) {
            setFlashMessage('error', 'New passwords do not match');
            redirect('profile.php');
        }
        
        if (strlen($newPassword) < 8) {
            setFlashMessage('error', 'Password must be at least 8 characters long');
            redirect('profile.php');
        }
        
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if (!password_verify($currentPassword, $user['password'])) {
            setFlashMessage('error', 'Current password is incorrect');
            redirect('profile.php');
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Password updated successfully');
        } else {
            setFlashMessage('error', 'Failed to update password');
        }
        $stmt->close();
        redirect('profile.php');
    }
}

// Get user data
$user = getUserProfile($conn, $userId);
$pageTitle = "My Profile";
?>

<style>
.profile-container {
    max-width: 1000px;
    margin: 0 auto;
}

.profile-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.profile-header {
    display: flex;
    align-items: center;
    padding-bottom: 24px;
    margin-bottom: 24px;
    border-bottom: 1px solid #e5e7eb;
}

.profile-avatar {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: 700;
    color: white;
    margin-right: 20px;
}

.profile-info h1 {
    font-size: 24px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 4px 0;
}

.profile-info p {
    color: #6b7280;
    margin: 0;
    font-size: 14px;
}

.card-title {
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
}

.card-title i {
    margin-right: 10px;
    color: #667eea;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    margin-bottom: 8px;
}

.form-control {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-control:disabled {
    background-color: #f9fafb;
    color: #6b7280;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5568d3;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}

.info-item {
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
}

.info-label {
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.info-value {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.server-credential {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px;
    background: #f9fafb;
    border-radius: 8px;
    margin-bottom: 12px;
}

.credential-info {
    flex: 1;
}

.credential-label {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 4px;
}

.credential-value {
    font-size: 14px;
    font-weight: 500;
    color: #111827;
    font-family: 'Courier New', monospace;
}

.btn-icon {
    padding: 8px 12px;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-icon:hover {
    background: #f3f4f6;
}

.server-url {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}

.server-url:hover {
    text-decoration: underline;
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
}

.alert i {
    margin-right: 10px;
}

.alert-info {
    background: #dbeafe;
    color: #1e40af;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.empty-state-icon {
    font-size: 48px;
    color: #d1d5db;
    margin-bottom: 16px;
}

.empty-state-title {
    font-size: 16px;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 8px;
}

.empty-state-text {
    font-size: 14px;
    color: #9ca3af;
}
</style>

<!-- Flash Messages -->
<?php displayFlashMessage(); ?>

<div class="profile-container">
    <!-- Profile Header -->
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($user['name']); ?></h1>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>
        
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Member Since</div>
                <div class="info-value"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Phone Number</div>
                <div class="info-value"><?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></div>
            </div>
        </div>
    </div>

    <!-- Edit Profile -->
    <div class="profile-card">
        <h2 class="card-title">
            <i class="bi bi-person-circle"></i>
            Edit Profile
        </h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="info-grid">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" 
                           class="form-control" 
                           id="name" 
                           name="name" 
                           value="<?php echo htmlspecialchars($user['name']); ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" 
                           class="form-control" 
                           id="phone" 
                           name="phone" 
                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                           required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" 
                       class="form-control" 
                       id="email" 
                       name="email" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" 
                       required>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-2"></i>
                Save Changes
            </button>
        </form>
    </div>

    <!-- Server Access -->
    <div class="profile-card">
        <h2 class="card-title">
            <i class="bi bi-hdd-network"></i>
            Server Access
        </h2>
        
        <?php if (!empty($user['server_username']) || !empty($user['server_url'])): ?>
            <div class="server-credential">
                <div class="credential-info">
                    <div class="credential-label">Server URL</div>
                    <a href="<?php echo htmlspecialchars($user['server_url'] ?? 'https://server.infralabs.cloud'); ?>" 
                       target="_blank" 
                       class="server-url">
                        <?php echo htmlspecialchars($user['server_url'] ?? 'https://server.infralabs.cloud'); ?>
                    </a>
                </div>
            </div>
            
            <div class="server-credential">
                <div class="credential-info">
                    <div class="credential-label">Username</div>
                    <div class="credential-value"><?php echo htmlspecialchars($user['server_username'] ?? 'Not set'); ?></div>
                </div>
            </div>
            
            <?php if (!empty($user['server_password'])): ?>
            <div class="server-credential">
                <div class="credential-info">
                    <div class="credential-label">Password</div>
                    <div class="credential-value" id="serverPassword">••••••••</div>
                </div>
                <button class="btn-icon" onclick="togglePassword()" type="button">
                    <i class="bi bi-eye" id="toggleIcon"></i>
                </button>
            </div>
            
            <script>
            let passwordVisible = false;
            const actualPassword = <?php echo json_encode($user['server_password']); ?>;
            
            function togglePassword() {
                passwordVisible = !passwordVisible;
                const passwordEl = document.getElementById('serverPassword');
                const iconEl = document.getElementById('toggleIcon');
                
                if (passwordVisible) {
                    passwordEl.textContent = actualPassword;
                    iconEl.className = 'bi bi-eye-slash';
                } else {
                    passwordEl.textContent = '••••••••';
                    iconEl.className = 'bi bi-eye';
                }
            }
            </script>
            <?php endif; ?>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                Use these credentials to access your CloudPanel server management interface.
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-server"></i>
                </div>
                <h5 class="empty-state-title">No Server Access</h5>
                <p class="empty-state-text">Server credentials will be provided by the administrator</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Change Password -->
    <div class="profile-card">
        <h2 class="card-title">
            <i class="bi bi-shield-lock"></i>
            Change Password
        </h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="update_password">
            
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" 
                       class="form-control" 
                       id="current_password" 
                       name="current_password" 
                       required>
            </div>
            
            <div class="info-grid">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" 
                           class="form-control" 
                           id="new_password" 
                           name="new_password" 
                           minlength="8"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" 
                           class="form-control" 
                           id="confirm_password" 
                           name="confirm_password" 
                           minlength="8"
                           required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-secondary">
                <i class="bi bi-shield-check me-2"></i>
                Update Password
            </button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
