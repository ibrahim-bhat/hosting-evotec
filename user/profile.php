<?php
require_once 'includes/header.php';
require_once '../components/user_helper.php';

$userId = $_SESSION['user_id'];

// Get user profile
$user = getUserProfile($conn, $userId);

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $name = sanitizeInput($_POST['name']);
        $phone = sanitizeInput($_POST['phone']);
        
        if (empty($name)) {
            setFlashMessage('error', 'Name is required');
        } else {
            if (updateUserProfile($conn, $userId, $name, $phone)) {
                setFlashMessage('success', 'Profile updated successfully');
                // Update session
                $_SESSION['user_name'] = $name;
                // Refresh user data
                $user = getUserProfile($conn, $userId);
            } else {
                setFlashMessage('error', 'Failed to update profile');
            }
        }
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
                               value="<?php echo htmlspecialchars($user['email']); ?>" 
                               disabled>
                        <div class="form-text">Email address cannot be changed</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="role" class="form-label">
                            <i class="bi bi-shield-check me-1"></i>
                            Account Type
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="role" 
                               value="<?php echo ucfirst($user['role']); ?>" 
                               disabled>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="status" class="form-label">
                            <i class="bi bi-circle-fill me-1"></i>
                            Account Status
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="status" 
                               value="<?php echo ucfirst($user['status']); ?>" 
                               disabled>
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
                    Profile Picture
                </h2>
                <p class="card-subtitle">Your account avatar</p>
            </div>
            
            <div class="profile-picture-section">
                <div class="profile-avatar-large">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="../<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                             alt="Profile Picture" 
                             class="avatar-img-large">
                    <?php else: ?>
                        <div class="avatar-placeholder-large">
                            <i class="bi bi-person"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="profile-picture-actions">
                    <button type="button" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-camera me-1"></i>
                        Change Picture
                    </button>
                    <p class="picture-hint">JPG, PNG or GIF. Max size 2MB.</p>
                </div>
            </div>
        </div>
        
        <!-- Account Quick Info -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="bi bi-info-circle me-2"></i>
                    Account Info
                </h2>
            </div>
            
            <div class="account-info-list">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">User ID</div>
                        <div class="info-value">#<?php echo $user['id']; ?></div>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Joined</div>
                        <div class="info-value"><?php echo formatDate($user['created_at']); ?></div>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="badge bg-success"><?php echo ucfirst($user['status']); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="bi bi-person-gear"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Role</div>
                        <div class="info-value">
                            <span class="badge bg-primary"><?php echo ucfirst($user['role']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

<!-- Recent Activity -->
<div class="row g-4 mt-4">
    <div class="col-12">
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="bi bi-clock-history me-2"></i>
                    Recent Activity
                </h2>
                <p class="card-subtitle">Your latest account activities and transactions</p>
            </div>
            
            <?php
            $recentActivity = getUserRecentActivity($conn, $userId, 10);
            ?>
            
            <?php if (!empty($recentActivity)): ?>
                <div class="activity-timeline">
                    <?php foreach ($recentActivity as $activity): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker">
                                <?php if ($activity['type'] === 'order'): ?>
                                    <i class="bi bi-cart-fill"></i>
                                <?php else: ?>
                                    <i class="bi bi-globe"></i>
                                <?php endif; ?>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <h6 class="timeline-title">
                                        <?php if ($activity['type'] === 'order'): ?>
                                            Order #<?php echo htmlspecialchars($activity['title']); ?>
                                        <?php else: ?>
                                            Website: <?php echo htmlspecialchars($activity['title']); ?>
                                        <?php endif; ?>
                                    </h6>
                                    <span class="timeline-time"><?php echo timeAgo($activity['created_at']); ?></span>
                                </div>
                                <div class="timeline-body">
                                    <span class="badge bg-<?php echo $activity['status'] === 'active' ? 'success' : ($activity['status'] === 'pending' ? 'warning' : 'secondary'); ?>">
                                        <?php echo ucfirst($activity['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h5 class="empty-state-title">No Recent Activity</h5>
                    <p class="empty-state-text">Your recent activities will appear here</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
