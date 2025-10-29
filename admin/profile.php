<?php
require_once '../config.php';
require_once '../components/auth_helper.php';
require_once '../components/flash_message.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    redirect('../login.php');
}

// Get user data
$userId = $_SESSION['user_id'];
$user = getUserById($conn, $userId);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = sanitizeInput($_POST['fullname']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($phone)) {
        setFlashMessage('error', 'Please fill in all fields');
    } elseif (!validateEmail($email)) {
        setFlashMessage('error', 'Invalid email format');
    } elseif (!validatePhone($phone)) {
        setFlashMessage('error', 'Invalid phone number format');
    } else {
        // Check if email is taken by another user
        if ($email !== $user['email'] && emailExists($conn, $email)) {
            setFlashMessage('error', 'Email already exists');
        } else {
            // Update user profile
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $email, $phone, $userId);
            
            if ($stmt->execute()) {
                // Update session
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                
                setFlashMessage('success', 'Profile updated successfully');
                $user = getUserById($conn, $userId); // Refresh user data
            } else {
                setFlashMessage('error', 'Failed to update profile');
            }
            $stmt->close();
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($newPassword) || empty($confirmPassword)) {
        setFlashMessage('error', 'Please fill in all password fields');
    } elseif (!validatePassword($newPassword)) {
        setFlashMessage('error', 'Password must be at least 8 characters long');
    } elseif ($newPassword !== $confirmPassword) {
        setFlashMessage('error', 'Passwords do not match');
    } else {
        // Update password
        $hashedPassword = hashPassword($newPassword);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Password updated successfully');
        } else {
            setFlashMessage('error', 'Failed to update password');
        }
        $stmt->close();
    }
}

$pageTitle = "Profile";
include 'includes/header.php';
?>
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Profile</h1>
                <p class="page-subtitle">Manage your account settings and preferences</p>
            </div>
            
            <!-- Flash Message -->
            <?php displayFlashMessage(); ?>
            
            <!-- Profile Content -->
            <div class="row g-4">
                <!-- Profile Picture Section -->
                <div class="col-12 col-lg-6">
                    <div class="content-card">
                        <h2 class="card-title">Profile Picture</h2>
                        <p class="card-subtitle">Update your profile picture</p>
                        
                        <div class="profile-picture-section">
                            <div class="profile-avatar">
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=John" alt="Profile Picture" class="avatar-img">
                            </div>
                            <div class="profile-picture-actions">
                                <button class="btn-change-picture">
                                    <i class="bi bi-camera"></i>
                                    Change Picture
                                </button>
                                <p class="picture-hint">PNG, JPEG or GIF. Max size 2MB</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Personal Information Section -->
                <div class="col-12 col-lg-6">
                    <div class="content-card">
                        <h2 class="card-title">Personal Information</h2>
                        <p class="card-subtitle">Update your personal details</p>
                        
                        <form method="POST" class="profile-form">
                            <div class="form-group-profile">
                                <label for="fullname" class="form-label-profile">Full Name</label>
                                <input type="text" class="form-control-profile" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            
                            <div class="form-group-profile">
                                <label for="email-profile" class="form-label-profile">Email</label>
                                <input type="email" class="form-control-profile" id="email-profile" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <div class="form-group-profile">
                                <label for="phone-profile" class="form-label-profile">Phone Number</label>
                                <input type="tel" class="form-control-profile" id="phone-profile" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn-save-changes">Save Changes</button>
                        </form>
                    </div>
                </div>
                
                <!-- Change Password Section -->
                <div class="col-12">
                    <div class="content-card">
                        <h2 class="card-title">Change Password</h2>
                        <p class="card-subtitle">Update your password</p>
                        
                        <form method="POST" class="profile-form">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="form-group-profile">
                                        <label for="new-password" class="form-label-profile">New Password</label>
                                        <input type="password" class="form-control-profile" id="new-password" name="new_password" placeholder="Enter new password" required>
                                    </div>
                                </div>
                                
                                <div class="col-12 col-md-6">
                                    <div class="form-group-profile">
                                        <label for="confirm-password" class="form-label-profile">Confirm New Password</label>
                                        <input type="password" class="form-control-profile" id="confirm-password" name="confirm_password" placeholder="Confirm new password" required>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" name="update_password" class="btn-update-password">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        
<?php include 'includes/footer.php'; ?>
