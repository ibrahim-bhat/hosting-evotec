<?php
session_start();
require_once 'config.php';
require_once 'components/auth_helper.php';
require_once 'components/flash_message.php';
require_once 'components/mail_helper.php';

// Ensure we have a pending OTP verification
if (!isset($_SESSION['otp_user_id']) || !isset($_SESSION['otp_email'])) {
    redirect('login.php');
}

$userId = (int)$_SESSION['otp_user_id'];
$userEmail = $_SESSION['otp_email'];

// Validate CSRF for all POSTs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid form submission. Please try again.');
        redirect('verify-otp.php');
    }
}

// Handle OTP resend
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_otp'])) {
    // Throttle: use MySQL TIMESTAMPDIFF to avoid PHP/MySQL timezone mismatch
    $checkStmt = $conn->prepare("SELECT TIMESTAMPDIFF(SECOND, created_at, NOW()) as seconds_ago FROM otp_verifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $checkStmt->bind_param("i", $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $lastOtp = $checkResult->fetch_assoc();
    $checkStmt->close();
    
    $canResend = true;
    if ($lastOtp) {
        if ($lastOtp['seconds_ago'] < 60) {
            $canResend = false;
            $waitSeconds = 60 - (int)$lastOtp['seconds_ago'];
            setFlashMessage('error', "Please wait {$waitSeconds} seconds before requesting a new code.");
        }
    }
    
    if ($canResend) {
        // Delete old OTPs
        $delStmt = $conn->prepare("DELETE FROM otp_verifications WHERE user_id = ?");
        $delStmt->bind_param("i", $userId);
        $delStmt->execute();
        $delStmt->close();
        
        // Generate new OTP (use MySQL DATE_ADD to avoid timezone mismatch)
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $otpStmt = $conn->prepare("INSERT INTO otp_verifications (user_id, otp_code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
        $otpStmt->bind_param("is", $userId, $otp);
        $otpStmt->execute();
        $otpStmt->close();
        
        // Get user name for the email
        $user = getUserById($conn, $userId);
        try {
            sendOtpMail($conn, $userEmail, $otp, $user['name'] ?? '');
        } catch (\Exception $e) {
            // Mail failed but OTP is in DB; user can try again
        }
        
        setFlashMessage('success', 'A new verification code has been sent to your email.');
    }
    
    redirect('verify-otp.php');
}

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $enteredOtp = sanitizeInput($_POST['otp_code'] ?? '');
    
    if (empty($enteredOtp)) {
        setFlashMessage('error', 'Please enter the verification code.');
    } else {
        // Verify OTP
        $stmt = $conn->prepare("SELECT * FROM otp_verifications WHERE user_id = ? AND otp_code = ? AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("is", $userId, $enteredOtp);
        $stmt->execute();
        $result = $stmt->get_result();
        $otpRecord = $result->fetch_assoc();
        $stmt->close();
        
        if ($otpRecord) {
            // OTP is valid - mark user as verified
            $updateStmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $updateStmt->bind_param("i", $userId);
            $updateStmt->execute();
            $updateStmt->close();
            
            // Delete used OTPs
            $delStmt = $conn->prepare("DELETE FROM otp_verifications WHERE user_id = ?");
            $delStmt->bind_param("i", $userId);
            $delStmt->execute();
            $delStmt->close();
            
            // Get user data and create session
            $user = getUserById($conn, $userId);
            createUserSession($user);
            updateLastLogin($conn, $user['id']);
            
            // Send welcome email
            sendWelcomeMail($conn, $user);
            
            // Clean up OTP session vars
            unset($_SESSION['otp_user_id']);
            unset($_SESSION['otp_email']);
            
            setFlashMessage('success', 'Account verified successfully! Welcome to your dashboard.');
            redirect('user/index.php');
        } else {
            setFlashMessage('error', 'Invalid or expired verification code. Please try again.');
        }
    }
}

// Mask the email for display
$emailParts = explode('@', $userEmail);
$maskedLocal = substr($emailParts[0], 0, 2) . str_repeat('*', max(strlen($emailParts[0]) - 2, 0));
$maskedEmail = $maskedLocal . '@' . $emailParts[1];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - InfraLabs Cloud</title>
    
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

        .otp-inputs { display: flex; gap: 10px; justify-content: center; margin: 24px 0; }

        .otp-input {
            width: 52px; height: 60px;
            text-align: center;
            font-size: 24px; font-weight: 700;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            background: var(--light-bg);
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .otp-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            background: white;
        }

        .btn-primary {
            width: 100%; padding: 14px;
            background: var(--primary-blue); color: white;
            border: none; border-radius: 10px;
            font-size: 16px; font-weight: 700;
            cursor: pointer; transition: all 0.3s ease;
        }

        .btn-primary:hover { background: var(--primary-blue-hover); transform: translateY(-2px); }

        .resend-section {
            text-align: center; margin-top: 20px;
            font-size: 14px; color: var(--text-secondary);
        }

        .resend-btn {
            background: none; border: none;
            color: var(--primary-blue); font-weight: 600;
            cursor: pointer; font-size: 14px; font-family: inherit;
        }

        .resend-btn:hover { text-decoration: underline; }
        .resend-btn:disabled { color: var(--text-secondary); cursor: not-allowed; text-decoration: none; }

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

        /* Hidden real input */
        .hidden-otp { position: absolute; opacity: 0; pointer-events: none; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo-box">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <h1 class="auth-title">Verify Email</h1>
                <p class="auth-subtitle">
                    We sent a 6-digit verification code to<br>
                    <strong><?php echo htmlspecialchars($maskedEmail); ?></strong>
                </p>
            </div>

            <?php displayFlashMessage(); ?>

            <form action="" method="POST" id="otpForm">
                <?php echo csrfField(); ?>
                <input type="hidden" name="verify_otp" value="1">
                <input type="hidden" name="otp_code" id="otpHidden" value="">
                
                <div class="otp-inputs" id="otpInputs">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="0" autofocus>
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="1">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="2">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="3">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="4">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="5">
                </div>

                <button type="submit" class="btn-primary" id="verifyBtn">
                    <i class="fas fa-check-circle"></i> Verify Account
                </button>
            </form>

            <div class="resend-section">
                <span>Didn't receive the code?</span>
                <form action="" method="POST" style="display:inline;">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="resend_otp" value="1">
                    <button type="submit" class="resend-btn" id="resendBtn">Resend Code</button>
                </form>
                <span id="resendTimer" style="display:none; color: var(--text-secondary);"></span>
            </div>

            <div class="back-link">
                <a href="login.php">
                    <i class="fas fa-arrow-left"></i> Back to login
                </a>
            </div>
        </div>
    </div>

    <script>
        // OTP input handling
        const inputs = document.querySelectorAll('.otp-input');
        const hiddenInput = document.getElementById('otpHidden');
        const form = document.getElementById('otpForm');

        function updateHiddenInput() {
            let otp = '';
            inputs.forEach(input => { otp += input.value; });
            hiddenInput.value = otp;
        }

        inputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                // Only allow digits
                this.value = this.value.replace(/[^0-9]/g, '');
                
                if (this.value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                updateHiddenInput();
                
                // Auto-submit when all 6 digits entered
                if (hiddenInput.value.length === 6) {
                    form.submit();
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    inputs[index - 1].focus();
                    inputs[index - 1].value = '';
                    updateHiddenInput();
                }
            });

            // Handle paste
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
                if (pasted.length >= 6) {
                    for (let i = 0; i < 6; i++) {
                        inputs[i].value = pasted[i] || '';
                    }
                    inputs[5].focus();
                    updateHiddenInput();
                    if (hiddenInput.value.length === 6) {
                        form.submit();
                    }
                }
            });
        });

        // Resend timer (60 seconds cooldown)
        const resendBtn = document.getElementById('resendBtn');
        const resendTimer = document.getElementById('resendTimer');
        let cooldown = 60;

        function startCooldown() {
            resendBtn.style.display = 'none';
            resendTimer.style.display = 'inline';
            
            const interval = setInterval(() => {
                cooldown--;
                resendTimer.textContent = `Resend in ${cooldown}s`;
                
                if (cooldown <= 0) {
                    clearInterval(interval);
                    resendBtn.style.display = 'inline';
                    resendTimer.style.display = 'none';
                    cooldown = 60;
                }
            }, 1000);
        }

        // Start cooldown on page load (user just got/resent OTP)
        startCooldown();
    </script>
</body>
</html>
