<?php
require_once 'config.php';
require_once 'components/auth_helper.php';
require_once 'components/flash_message.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('admin/index.php');
}

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        setFlashMessage('error', 'Please fill in all fields');
    } elseif (!validateEmail($email)) {
        setFlashMessage('error', 'Invalid email format');
    } elseif (!validatePhone($phone)) {
        setFlashMessage('error', 'Invalid phone number format');
    } elseif (!validatePassword($password)) {
        setFlashMessage('error', 'Password must be at least 8 characters long');
    } elseif ($password !== $confirm_password) {
        setFlashMessage('error', 'Passwords do not match');
    } elseif (emailExists($conn, $email)) {
        setFlashMessage('error', 'Email already exists');
    } else {
        // Hash password
        $hashedPassword = hashPassword($password);
        
        // Insert user into database
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, role, status) VALUES (?, ?, ?, ?, 'user', 'active')");
        $stmt->bind_param("ssss", $name, $email, $hashedPassword, $phone);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Registration successful! Please login to continue.');
            redirect('login.php');
        } else {
            setFlashMessage('error', 'Registration failed. Please try again.');
        }
        $stmt->close();
    }
}

$page_title = "Sign Up";
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
            
            <!-- Subtitle -->
            <p class="auth-subtitle">Create your account to get started</p>
            
            <!-- Flash Message -->
            <?php displayFlashMessage(); ?>
            
            <!-- Register Form -->
            <form action="" method="POST" class="auth-form">
                <!-- Name Field -->
                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" 
                           class="form-control" 
                           id="name" 
                           name="name" 
                           placeholder="John Doe" 
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                           required>
                </div>
                
                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           placeholder="name@example.com" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           required>
                </div>
                
                <!-- Phone Number Field -->
                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" 
                           class="form-control" 
                           id="phone" 
                           name="phone" 
                           placeholder="+1 (555) 000-0000" 
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                           required>
                </div>
                
                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           placeholder="••••••••" 
                           required>
                </div>
                
                <!-- Confirm Password Field -->
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" 
                           class="form-control" 
                           id="confirm_password" 
                           name="confirm_password" 
                           placeholder="••••••••" 
                           required>
                </div>
                
                <!-- Sign Up Button -->
                <button type="submit" class="btn btn-primary btn-signin">Sign up</button>
                
                <!-- Divider -->
                <!-- <div class="divider">
                    <span class="divider-text">OR CONTINUE WITH</span>
                </div>
                
                <button type="button" class="btn btn-google">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.64 9.20443C17.64 8.56625 17.5827 7.95262 17.4764 7.36353H9V10.8449H13.8436C13.635 11.9699 13.0009 12.9231 12.0477 13.5613V15.8194H14.9564C16.6582 14.2526 17.64 11.9453 17.64 9.20443Z" fill="#4285F4"/>
                        <path d="M8.99976 18C11.4298 18 13.467 17.1941 14.9561 15.8195L12.0475 13.5613C11.2416 14.1013 10.2107 14.4204 8.99976 14.4204C6.65567 14.4204 4.67158 12.8372 3.96385 10.71H0.957031V13.0418C2.43794 15.9831 5.48158 18 8.99976 18Z" fill="#34A853"/>
                        <path d="M3.96409 10.7098C3.78409 10.1698 3.68182 9.59301 3.68182 8.99983C3.68182 8.40665 3.78409 7.82983 3.96409 7.28983V4.95801H0.957273C0.347727 6.17301 0 7.54756 0 8.99983C0 10.4521 0.347727 11.8266 0.957273 13.0416L3.96409 10.7098Z" fill="#FBBC05"/>
                        <path d="M8.99976 3.57955C10.3211 3.57955 11.5075 4.03364 12.4402 4.92545L15.0216 2.34409C13.4629 0.891818 11.4257 0 8.99976 0C5.48158 0 2.43794 2.01682 0.957031 4.95818L3.96385 7.29C4.67158 5.16273 6.65567 3.57955 8.99976 3.57955Z" fill="#EA4335"/>
                    </svg>
                    Continue with Google
                </button> -->
                
                <!-- Sign In Link -->
                <p class="signup-text">
                    Already have an account? <a href="login.php" class="signup-link">Sign in</a>
                </p>
            </form>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
