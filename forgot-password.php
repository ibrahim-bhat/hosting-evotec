<?php
session_start();
require_once 'config.php';
require_once 'components/auth_helper.php';
require_once 'components/flash_message.php';
require_once 'components/mail_helper.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('user/index.php');
}

$emailSent = false;

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid form submission. Please try again.');
        redirect('forgot-password.php');
    }
    
    $email = sanitizeInput($_POST['email'] ?? '');
    
    if (empty($email) || !validateEmail($email)) {
        setFlashMessage('error', 'Please enter a valid email address.');
    } else {
        $user = getUserByEmail($conn, $email);
        
        if ($user) {
            // Delete any existing reset tokens for this user
            $delStmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $delStmt->bind_param("i", $user['id']);
            $delStmt->execute();
            $delStmt->close();
            
            // Generate secure token
            $token = bin2hex(random_bytes(32));
            
            // Store token with MySQL-based expiry (1 hour)
            $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
            $stmt->bind_param("is", $user['id'], $token);
            $stmt->execute();
            $stmt->close();
            
            // Build reset URL from current request so deployment uses correct domain (not localhost)
            $baseUrl = SITE_URL;
            if (!empty($_SERVER['HTTP_HOST'])) {
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
                $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $path;
            }
            $resetUrl = rtrim($baseUrl, '/') . '/reset-password.php?token=' . urlencode($token);
            
            // Send reset email
            try {
                sendPasswordResetMail($conn, $user['email'], $resetUrl, $user['name']);
            } catch (\Exception $e) {
                // Fail silently to prevent email enumeration
            }
        }
        
        // Always show success to prevent email enumeration
        $emailSent = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - InfraLabs Cloud</title>
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>&#x2601;&#xFE0F;</text></svg>">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary-blue: #5B5FED;
            --primary-blue-hover: #4A4ED6;
            --card-bg: #FFFFFF;
            --border-color: #E5E7EB;
            --text-primary: #1F2937;
            --text-secondary: #6B7280;
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

        .auth-container { width: 100%; max-width: 440px; }

        .auth-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 48px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .auth-header { text-align: center; margin-bottom: 32px; }

        .logo-box {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, var(--primary-blue) 0%, #7C3AED 100%);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            font-size: 32px; color: white;
        }

        .auth-title { font-size: 28px; font-weight: 800; margin-bottom: 8px; }
        .auth-subtitle { font-size: 15px; color: var(--text-secondary); line-height: 1.6; }

        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 14px; }

        .form-control {
            width: 100%; padding: 12px 16px;
            border: 2px solid var(--border-color); border-radius: 10px;
            font-size: 15px; font-family: inherit; transition: all 0.3s ease;
            background: var(--light-bg);
        }

        .form-control:focus { outline: none; border-color: var(--primary-blue); background: white; }

        .btn-primary {
            width: 100%; padding: 14px;
            background: var(--primary-blue); color: white;
            border: none; border-radius: 10px;
            font-size: 16px; font-weight: 700;
            cursor: pointer; transition: all 0.3s ease;
        }

        .btn-primary:hover { background: var(--primary-blue-hover); transform: translateY(-2px); }

        .alert {
            padding: 12px 16px; border-radius: 10px;
            margin-bottom: 20px; font-size: 14px;
        }
        .alert-error { background: #FEE2E2; border: 1px solid #FECACA; color: #991B1B; }
        .alert-success { background: #D1FAE5; border: 1px solid #A7F3D0; color: #065F46; }
        .alert-info { background: #DBEAFE; border: 1px solid #BFDBFE; color: #1E40AF; }

        .back-link {
            text-align: center; margin-top: 24px;
        }
        .back-link a {
            color: var(--text-secondary); text-decoration: none;
            font-size: 14px; display: inline-flex; align-items: center; gap: 6px;
        }
        .back-link a:hover { color: var(--text-primary); }

        .success-icon {
            width: 64px; height: 64px;
            background: #D1FAE5; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px; font-size: 28px; color: #065F46;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <?php if ($emailSent): ?>
                <!-- Success State -->
                <div class="auth-header">
                    <div class="success-icon">
                        <i class="fas fa-envelope-circle-check"></i>
                    </div>
                    <h1 class="auth-title">Check Your Email</h1>
                    <p class="auth-subtitle">
                        If an account with that email exists, we've sent a password reset link. Please check your inbox and spam folder.
                    </p>
                </div>
                
                <div class="back-link">
                    <a href="login.php">
                        <i class="fas fa-arrow-left"></i> Back to login
                    </a>
                </div>
            <?php else: ?>
                <!-- Form State -->
                <div class="auth-header">
                    <div class="logo-box">
                        <i class="fas fa-key"></i>
                    </div>
                    <h1 class="auth-title">Forgot Password?</h1>
                    <p class="auth-subtitle">Enter your email address and we'll send you a link to reset your password.</p>
                </div>

                <?php displayFlashMessage(); ?>

                <form action="" method="POST">
                    <?php echo csrfField(); ?>
                    <div class="form-group">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               placeholder="you@example.com" 
                               required
                               autofocus>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Reset Link
                    </button>
                </form>

                <div class="back-link">
                    <a href="login.php">
                        <i class="fas fa-arrow-left"></i> Back to login
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
