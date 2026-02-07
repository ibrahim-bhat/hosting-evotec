<?php
require_once 'includes/header.php';
require_once '../components/user_helper.php';

$userId = $_SESSION['user_id'];

// Get user profile
$user = getUserProfile($conn, $userId);

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
            // Update session if name changed
            $_SESSION['user_name'] = $name;
        } else {
            setFlashMessage('error', 'Failed to update profile');
        }
        $stmt->close();
        redirect('profile.php');
    } elseif ($_POST['action'] === 'update_password') {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            setFlashMessage('error', 'All password fields are required');
        } elseif (!verifyPassword($currentPassword, $user['password'])) {
            setFlashMessage('error', 'Current password is incorrect');
        } elseif (!validatePassword($newPassword)) {
            setFlashMessage('error', 'New password must be at least 8 characters long');
        } elseif ($newPassword !== $confirmPassword) {
            setFlashMessage('error', 'New passwords do not match');
        } else {
            if (updateUserPassword($conn, $userId, $newPassword)) {
                setFlashMessage('success', 'Password updated successfully');
            } else {
                setFlashMessage('error', 'Failed to update password');
            }
        }
    }
}

$pageTitle = "Profile";
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-content">
        <div class="page-header-info">
            <h1 class="page-title">My Profile</h1>
            <p class="page-subtitle">Manage your account information and settings</p>
        </div>
        <div class="page-header-avatar">
            <?php if (!empty($user['profile_picture'])): ?>
                <img src="../<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                     alt="Profile Picture" 
                     class="header-avatar">
            <?php else: ?>
                <div class="header-avatar-placeholder">
                    <i class="bi bi-person"></i>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php displayFlashMessage(); ?>

<!-- Main Content -->
<div class="row g-4">
    <!-- Profile Information -->
    <div class="col-12 col-lg-8">
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="bi bi-person-circle me-2"></i>
                    Profile Information
                </h2>
                <p class="card-subtitle">Update your personal information and account details</p>
            </div>
            
            <form method="POST" class="profile-form">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">
                            <i class="bi bi-person me-1"></i>
                            Full Name
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="name" 
                               name="name" 
                               value="<?php echo htmlspecialchars($user['name']); ?>" 
                               required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="phone" class="form-label">
                            <i class="bi bi-telephone me-1"></i>
                            Phone Number
                        </label>
                        <input type="tel" 
                               class="form-control" 
                               id="phone" 
                               name="phone" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                               placeholder="Enter your phone number">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-1"></i>
                            Email Address
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" 
                               required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="created_at" class="form-label">
                            <i class="bi bi-calendar me-1"></i>
                            Member Since
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="created_at" 
                               value="<?php echo formatDate($user['created_at']); ?>" 
                               disabled>
                    </div>
                </div>
                
                <div class="form-actions mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Profile Picture & Quick Info -->
    <div class="col-12 col-lg-4">
        <!-- Profile Picture -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="bi bi-camera me-2"></i>
        <!-- Account Quick Info -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="bi bi-info-circle me-2"></i>
                    Account Info
                </h2>
            </div>
            
            <div class="row g-3" style="padding: 20px;">
                <div class="col-12">
                    <div class="d-flex align-items-center p-3" style="background: #f8f9fa; border-radius: 8px;">
                        <i class="bi bi-calendar-event" style="font-size: 24px; color: #4f46e5; margin-right: 15px;"></i>
                        <div>
                            <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">MEMBER SINCE</div>
                            <div style="font-weight: 600; color: #1f2937;"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Server Access Credentials -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="bi bi-hdd-network me-2"></i>
                    Server Access
                </h2>
                <p class="card-subtitle">CloudPanel server login credentials</p>
            </div>
            
            <?php if (!empty($user['server_username']) || !empty($user['server_url'])): ?>
                <div class="account-info-list">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="bi bi-link-45deg"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Server URL</div>
                            <div class="info-value">
                                <a href="<?php echo htmlspecialchars($user['server_url'] ?? 'https://server.infralabs.cloud'); ?>" 
                                   target="_blank" 
                                   class="text-primary">
                                    <?php echo htmlspecialchars($user['server_url'] ?? 'https://server.infralabs.cloud'); ?>
                                    <i class="bi bi-box-arrow-up-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($user['server_username'])): ?>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Username</div>
                                <div class="info-value">
                                    <code><?php echo htmlspecialchars($user['server_username']); ?></code>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($user['server_password'])): ?>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-key-fill"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Password</div>
                                <div class="info-value">
                                    <div class="d-flex align-items-center gap-2">
                                        <code id="serverPassword" style="letter-spacing: 2px;">••••••••</code>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-secondary" 
                                                onclick="togglePassword()"
                                                id="toggleBtn">
                                            <i class="bi bi-eye" id="toggleIcon"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" id="actualPassword" value="<?php echo htmlspecialchars($user['server_password']); ?>">
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>Use these credentials to log in to the CloudPanel server management interface.</small>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state py-4">
                    <div class="empty-state-icon">
                        <i class="bi bi-hdd-network"></i>
                    </div>
                    <h5 class="empty-state-title">No Server Access</h5>
                    <p class="empty-state-text">Server credentials have not been assigned yet. Contact admin for access.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordField = document.getElementById('serverPassword');
    const actualPassword = document.getElementById('actualPassword').value;
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordField.textContent === '••••••••') {
        passwordField.textContent = actualPassword;
        toggleIcon.className = 'bi bi-eye-slash';
    } else {
        passwordField.textContent = '••••••••';
        toggleIcon.className = 'bi bi-eye';
    }
}
</script>

<!-- Change Password -->
<div class="row g-4 mt-4">
    <div class="col-12 col-lg-8">
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="bi bi-shield-lock me-2"></i>
                    Change Password
                </h2>
                <p class="card-subtitle">Update your account password for better security</p>
            </div>
            
            <form method="POST" class="profile-form">
                <input type="hidden" name="action" value="update_password">
                
                <div class="row g-3">
                    <div class="col-12">
                        <label for="current_password" class="form-label">
                            <i class="bi bi-key me-1"></i>
                            Current Password
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="current_password" 
                               name="current_password" 
                               placeholder="Enter current password" 
                               required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="new_password" class="form-label">
                            <i class="bi bi-lock me-1"></i>
                            New Password
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="new_password" 
                               name="new_password" 
                               placeholder="Enter new password" 
                               required>
                        <div class="form-text">Password must be at least 8 characters long</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">
                            <i class="bi bi-lock-fill me-1"></i>
                            Confirm New Password
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="confirm_password" 
                               name="confirm_password" 
                               placeholder="Confirm new password" 
                               required>
                    </div>
                </div>
                
                <div class="form-actions mt-4">
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-shield-check me-2"></i>
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
