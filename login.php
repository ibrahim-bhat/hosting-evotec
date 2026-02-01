<?php
session_start();
require_once 'config.php';
require_once 'components/auth_helper.php';
require_once 'components/flash_message.php';
require_once 'components/settings_helper.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/index.php');
    } else {
        redirect('user/index.php');
    }
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        setFlashMessage('error', 'Please fill in all fields');
    } elseif (!validateEmail($email)) {
        setFlashMessage('error', 'Invalid email format');
    } else {
        // Get user from database
        $user = getUserByEmail($conn, $email);
        
        if ($user) {
            // Check if account is blocked
            if ($user['status'] === 'blocked') {
                setFlashMessage('error', 'Your account has been blocked. Please contact support.');
            } elseif (verifyPassword($password, $user['password'])) {
                // Password is correct
                createUserSession($user);
                updateLastLogin($conn, $user['id']);
                setFlashMessage('success', 'Login successful! Welcome back, ' . $user['name']);
                
                // Check for redirect parameter
                if (isset($_GET['redirect'])) {
                    redirect($_GET['redirect']);
                }
                
                // Redirect based on user role
                if (isAdmin()) {
                    redirect('admin/index.php');
                } else {
                    redirect('user/index.php');
                }
            } else {
                setFlashMessage('error', 'Invalid email or password');
            }
        } else {
            setFlashMessage('error', 'Invalid email or password');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - InfraLabs Cloud</title>
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>☁️</text></svg>">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-blue: #5B5FED;
            --primary-blue-hover: #4A4ED6;
            --dark-bg: #0F1117;
            --card-bg: #FFFFFF;
            --border-color: #E5E7EB;
            --text-primary: #1F2937;
            --text-secondary: #6B7280;
            --error-red: #EF4444;
            --success-green: #10B981;
            --light-bg: #F9FAFB;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light-bg);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-container {
            width: 100%;
            max-width: 440px;
        }

        .auth-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 48px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-box {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary-blue) 0%, #7C3AED 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 32px;
            color: white;
        }

        .auth-title {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .auth-subtitle {
            font-size: 15px;
            color: var(--text-secondary);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
            color: var(--text-primary);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: var(--light-bg);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-blue);
            background: white;
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 18px;
        }

        .forgot-link {
            color: var(--primary-blue);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            float: right;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-primary {
            width: 100%;
            padding: 14px;
            background: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
        }

        .btn-primary:hover {
            background: var(--primary-blue-hover);
            transform: translateY(-2px);
        }

        .signup-text {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .signup-link {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
        }

        .signup-link:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #FEE2E2;
            border: 1px solid #FECACA;
            color: #991B1B;
        }

        .alert-success {
            background: #D1FAE5;
            border: 1px solid #A7F3D0;
            color: #065F46;
        }

        .back-home {
            text-align: center;
            margin-top: 24px;
        }

        .back-home a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .back-home a:hover {
            color: var(--text-primary);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo-box">
                    <i class="fas fa-cloud"></i>
                </div>
                <h1 class="auth-title">Welcome back</h1>
                <p class="auth-subtitle">Sign in to your InfraLabs Cloud account</p>
            </div>

            <?php displayFlashMessage(); ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           placeholder="you@example.com" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        Password
                        <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
                    </label>
                    <div class="password-wrapper">
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="••••••••" 
                               required>
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Sign in
                </button>
            </form>

            <p class="signup-text">
                Don't have an account? <a href="register.php" class="signup-link">Sign up</a>
            </p>

            <div class="back-home">
                <a href="index.php">
                    <i class="fas fa-arrow-left"></i> Back to home
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
