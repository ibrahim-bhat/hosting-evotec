<?php
session_start();
require_once 'config.php';
require_once 'components/auth_helper.php';
require_once 'components/flash_message.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('user/index.php');
}

$token = $_GET['token'] ?? '';
$tokenValid = false;
$resetSuccess = false;
$resetUser = null;

// Validate token
if (!empty($token)) {
    $stmt = $conn->prepare("SELECT pr.*, u.name, u.email FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW() LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $resetRecord = $result->fetch_assoc();
    $stmt->close();

    if ($resetRecord) {
        $tokenValid = true;
        $resetUser = $resetRecord;
    }
}

// Handle password reset form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid form submission. Please try again.');
        redirect('reset-password.php?token=' . urlencode($token));
    }

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirmPassword)) {
        setFlashMessage('error', 'Please fill in all fields.');
    } elseif (strlen($password) < 8) {
        setFlashMessage('error', 'Password must be at least 8 characters long.');
    } elseif ($password !== $confirmPassword) {
        setFlashMessage('error', 'Passwords do not match.');
    } else {
        // Update password
        $hashedPassword = hashPassword($password);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $resetUser['user_id']);
        $stmt->execute();
        $stmt->close();

        // Mark token as used
        $stmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->close();

        // Delete all tokens for this user
        $stmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
        $stmt->bind_param("i", $resetUser['user_id']);
        $stmt->execute();
        $stmt->close();

        $resetSuccess = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - InfraLabs Cloud</title>
    
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

        .error-icon {
            width: 64px; height: 64px;
            background: #FEE2E2; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px; font-size: 28px; color: #991B1B;
        }

        .password-wrapper { position: relative; }

        .toggle-password {
            position: absolute; right: 16px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: var(--text-secondary); cursor: pointer; font-size: 18px;
        }

        .password-strength {
            height: 4px; border-radius: 2px;
            margin-top: 8px; background: #E5E7EB;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%; width: 0; border-radius: 2px;
            transition: all 0.3s ease;
        }

        .strength-text {
            font-size: 12px; margin-top: 4px;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <?php if ($resetSuccess): ?>
                <!-- Success -->
                <div class="auth-header">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h1 class="auth-title">Password Reset!</h1>
                    <p class="auth-subtitle">Your password has been successfully updated. You can now log in with your new password.</p>
                </div>
                <a href="login.php" class="btn-primary" style="display:block; text-align:center; text-decoration:none;">
                    <i class="fas fa-sign-in-alt"></i> Go to Login
                </a>

            <?php elseif (!$tokenValid): ?>
                <!-- Invalid / Expired Token -->
                <div class="auth-header">
                    <div class="error-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h1 class="auth-title">Invalid Link</h1>
                    <p class="auth-subtitle">This password reset link is invalid or has expired. Please request a new one.</p>
                </div>
                <a href="forgot-password.php" class="btn-primary" style="display:block; text-align:center; text-decoration:none;">
                    <i class="fas fa-redo"></i> Request New Link
                </a>
                <div class="back-link">
                    <a href="login.php"><i class="fas fa-arrow-left"></i> Back to login</a>
                </div>

            <?php else: ?>
                <!-- Reset Form -->
                <div class="auth-header">
                    <div class="logo-box">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h1 class="auth-title">New Password</h1>
                    <p class="auth-subtitle">Enter a new password for <strong><?php echo htmlspecialchars($resetUser['email']); ?></strong></p>
                </div>

                <?php displayFlashMessage(); ?>

                <form action="" method="POST">
                    <?php echo csrfField(); ?>
                    <div class="form-group">
                        <label for="password" class="form-label">New Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Minimum 8 characters" required minlength="8">
                            <button type="button" class="toggle-password" onclick="togglePass('password', 'toggleIcon1')">
                                <i class="fas fa-eye" id="toggleIcon1"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="strength-text" id="strengthText"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required minlength="8">
                            <button type="button" class="toggle-password" onclick="togglePass('confirm_password', 'toggleIcon2')">
                                <i class="fas fa-eye" id="toggleIcon2"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Reset Password
                    </button>
                </form>

                <div class="back-link">
                    <a href="login.php"><i class="fas fa-arrow-left"></i> Back to login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function togglePass(inputId, iconId) {
            var input = document.getElementById(inputId);
            var icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password strength meter
        var passwordInput = document.getElementById('password');
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                var val = this.value;
                var strength = 0;
                if (val.length >= 8) strength++;
                if (/[A-Z]/.test(val)) strength++;
                if (/[0-9]/.test(val)) strength++;
                if (/[^A-Za-z0-9]/.test(val)) strength++;

                var bar = document.getElementById('strengthBar');
                var text = document.getElementById('strengthText');
                var colors = ['#EF4444', '#F59E0B', '#10B981', '#059669'];
                var labels = ['Weak', 'Fair', 'Good', 'Strong'];
                var widths = ['25%', '50%', '75%', '100%'];

                if (val.length === 0) {
                    bar.style.width = '0';
                    text.textContent = '';
                } else {
                    var idx = Math.max(0, strength - 1);
                    bar.style.width = widths[idx];
                    bar.style.background = colors[idx];
                    text.textContent = labels[idx];
                    text.style.color = colors[idx];
                }
            });
        }
    </script>
</body>
</html>
