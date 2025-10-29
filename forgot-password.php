<?php 
$page_title = "Reset Password";
include 'includes/header.php';
?>
    <div class="auth-container">
        <div class="auth-card">
            <!-- Logo and Title -->
            <div class="auth-header">
                <?php if (!empty($companyLogo)): ?>
                    <img src="<?php echo htmlspecialchars($companyLogo); ?>" alt="<?php echo htmlspecialchars($companyName); ?>" class="company-logo">
                <?php else: ?>
                    <div class="logo-box">
                        <span class="logo-text"><?php echo strtoupper(substr($companyName, 0, 1)); ?></span>
                    </div>
                <?php endif; ?>
                <!-- <h1 class="auth-title"><?php echo htmlspecialchars($companyName); ?></h1> -->
            </div>
            
            <!-- Page Title -->
            <h2 class="reset-title">Reset Password</h2>
            <p class="reset-subtitle">Enter your email address and we'll send you a reset link</p>
            
            <!-- Reset Password Form -->
            <form action="" method="POST" class="auth-form">
                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           placeholder="name@example.com" 
                           required>
                </div>
                
                <!-- Send Reset Link Button -->
                <button type="submit" class="btn btn-primary btn-signin">Send Reset Link</button>
                
                <!-- Back to Login Link -->
                <p class="back-to-login">
                    <a href="login.php" class="back-link">Back to Login</a>
                </p>
            </form>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
