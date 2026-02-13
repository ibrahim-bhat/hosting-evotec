<?php
/**
 * Mail Helper Functions
 * Centralized email sending using PHPMailer with SMTP settings from database
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Get SMTP settings from database
 */
function getSmtpSettings($conn) {
    $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_group = 'email'");
    $stmt->execute();
    $result = $stmt->get_result();

    $settings = [];
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    $stmt->close();

    return [
        'host'       => $settings['smtp_host'] ?? '',
        'port'       => (int)($settings['smtp_port'] ?? 587),
        'username'   => $settings['smtp_username'] ?? '',
        'password'   => $settings['smtp_password'] ?? '',
        'encryption' => $settings['smtp_encryption'] ?? 'tls',
        'from_email' => $settings['smtp_from_email'] ?? '',
        'from_name'  => $settings['smtp_from_name'] ?? 'Hosting Platform',
        'enabled'    => (bool)($settings['smtp_enabled'] ?? 0),
    ];
}

/**
 * Core mail sending function
 *
 * @param mysqli $conn   Database connection
 * @param string $to     Recipient email
 * @param string $subject Email subject
 * @param string $body   HTML body
 * @return array ['success' => bool, 'message' => string]
 */
function sendMail($conn, $to, $subject, $body) {
    $smtp = getSmtpSettings($conn);

    if (!$smtp['enabled']) {
        return ['success' => false, 'message' => 'Email sending is disabled'];
    }

    if (empty($smtp['host']) || empty($smtp['from_email'])) {
        return ['success' => false, 'message' => 'SMTP settings are incomplete'];
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $smtp['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp['username'];
        $mail->Password   = $smtp['password'];
        $mail->SMTPSecure = $smtp['encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtp['port'];
        $mail->Timeout       = 10;    // 10 second connection timeout
        $mail->SMTPKeepAlive = false;  // close connection after each send

        $mail->setFrom($smtp['from_email'], $smtp['from_name']);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));

        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Mailer Error: ' . $mail->ErrorInfo];
    }
}

/**
 * Get the base email template wrapper
 */
function getEmailTemplate($title, $content) {
    $companyName = 'InfraLabs Cloud';
    $year = date('Y');

    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . '</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f9; font-family: Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f9; padding:40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color:#5B5FED; padding:30px 40px; text-align:center;">
                            <h1 style="color:#ffffff; margin:0; font-size:24px; font-weight:700;">' . htmlspecialchars($companyName) . '</h1>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding:40px;">
                            ' . $content . '
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f9fafb; padding:20px 40px; text-align:center; border-top:1px solid #e5e7eb;">
                            <p style="color:#9ca3af; font-size:13px; margin:0;">&copy; ' . $year . ' ' . htmlspecialchars($companyName) . '. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
}

/**
 * Send OTP verification email
 */
function sendOtpMail($conn, $email, $otp, $userName = '') {
    $greeting = $userName ? 'Hi ' . htmlspecialchars($userName) . ',' : 'Hi,';
    $content = '
        <h2 style="color:#1f2937; margin:0 0 16px 0; font-size:20px;">Verify Your Email</h2>
        <p style="color:#6b7280; font-size:15px; line-height:1.6;">' . $greeting . '</p>
        <p style="color:#6b7280; font-size:15px; line-height:1.6;">Your one-time verification code is:</p>
        <div style="text-align:center; margin:30px 0;">
            <div style="display:inline-block; background-color:#f3f4f6; border:2px dashed #5B5FED; border-radius:12px; padding:20px 40px;">
                <span style="font-size:36px; font-weight:800; letter-spacing:8px; color:#5B5FED;">' . htmlspecialchars($otp) . '</span>
            </div>
        </div>
        <p style="color:#6b7280; font-size:15px; line-height:1.6;">This code expires in <strong>10 minutes</strong>. Do not share it with anyone.</p>
        <p style="color:#9ca3af; font-size:13px; margin-top:24px;">If you did not request this, please ignore this email.</p>';

    $body = getEmailTemplate('Verify Your Email', $content);
    return sendMail($conn, $email, 'Your Verification Code', $body);
}

/**
 * Send welcome email after account verification
 */
function sendWelcomeMail($conn, $user) {
    $siteUrl = defined('SITE_URL') ? SITE_URL : '';
    $dashboardUrl = $siteUrl . '/user/index.php';

    $content = '
        <h2 style="color:#1f2937; margin:0 0 16px 0; font-size:20px;">Welcome Aboard!</h2>
        <p style="color:#6b7280; font-size:15px; line-height:1.6;">Hi ' . htmlspecialchars($user['name']) . ',</p>
        <p style="color:#6b7280; font-size:15px; line-height:1.6;">Your account has been successfully verified. You now have full access to your hosting dashboard.</p>
        <div style="text-align:center; margin:30px 0;">
            <a href="' . htmlspecialchars($dashboardUrl) . '" style="display:inline-block; background-color:#5B5FED; color:#ffffff; text-decoration:none; padding:14px 32px; border-radius:8px; font-weight:600; font-size:15px;">Go to Dashboard</a>
        </div>
        <p style="color:#6b7280; font-size:15px; line-height:1.6;">From your dashboard you can browse hosting plans, manage your services, and more.</p>';

    $body = getEmailTemplate('Welcome to InfraLabs Cloud', $content);
    return sendMail($conn, $user['email'], 'Welcome to InfraLabs Cloud - Account Verified', $body);
}

/**
 * Send subscription purchase confirmation email
 */
function sendSubscriptionMail($conn, $user, $order, $package) {
    $siteUrl = defined('SITE_URL') ? SITE_URL : '';
    $dashboardUrl = $siteUrl . '/user/hosting.php';

    $cycleLabels = [
        'monthly' => 'Monthly',
        'yearly'  => 'Yearly',
        '2years'  => '2 Years',
        '4years'  => '4 Years',
    ];
    $cycleLabel = $cycleLabels[$order['billing_cycle']] ?? ucfirst($order['billing_cycle']);

    $content = '
        <h2 style="color:#1f2937; margin:0 0 16px 0; font-size:20px;">Subscription Confirmed!</h2>
        <p style="color:#6b7280; font-size:15px; line-height:1.6;">Hi ' . htmlspecialchars($user['name']) . ',</p>
        <p style="color:#6b7280; font-size:15px; line-height:1.6;">Thank you for your purchase. Here are your order details:</p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
            <tr style="background-color:#f9fafb;">
                <td style="padding:12px 16px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Order Number</td>
                <td style="padding:12px 16px; font-size:14px; font-weight:600; color:#1f2937; border-bottom:1px solid #e5e7eb;">' . htmlspecialchars($order['order_number']) . '</td>
            </tr>
            <tr>
                <td style="padding:12px 16px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Package</td>
                <td style="padding:12px 16px; font-size:14px; font-weight:600; color:#1f2937; border-bottom:1px solid #e5e7eb;">' . htmlspecialchars($package['name']) . '</td>
            </tr>
            <tr style="background-color:#f9fafb;">
                <td style="padding:12px 16px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Billing Cycle</td>
                <td style="padding:12px 16px; font-size:14px; font-weight:600; color:#1f2937; border-bottom:1px solid #e5e7eb;">' . htmlspecialchars($cycleLabel) . '</td>
            </tr>
            <tr>
                <td style="padding:12px 16px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Amount Paid</td>
                <td style="padding:12px 16px; font-size:14px; font-weight:600; color:#5B5FED; border-bottom:1px solid #e5e7eb;">₹' . number_format($order['total_amount'], 2) . '</td>
            </tr>
            <tr style="background-color:#f9fafb;">
                <td style="padding:12px 16px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Start Date</td>
                <td style="padding:12px 16px; font-size:14px; font-weight:600; color:#1f2937; border-bottom:1px solid #e5e7eb;">' . date('M d, Y', strtotime($order['start_date'])) . '</td>
            </tr>
            <tr>
                <td style="padding:12px 16px; font-size:14px; color:#6b7280;">Expiry Date</td>
                <td style="padding:12px 16px; font-size:14px; font-weight:600; color:#1f2937;">' . date('M d, Y', strtotime($order['expiry_date'])) . '</td>
            </tr>
        </table>
        <div style="text-align:center; margin:30px 0;">
            <a href="' . htmlspecialchars($dashboardUrl) . '" style="display:inline-block; background-color:#5B5FED; color:#ffffff; text-decoration:none; padding:14px 32px; border-radius:8px; font-weight:600; font-size:15px;">View Your Hosting</a>
        </div>';

    $body = getEmailTemplate('Subscription Confirmed', $content);
    return sendMail($conn, $user['email'], 'Subscription Confirmed - ' . $package['name'], $body);
}

/**
 * Send a test email to verify SMTP configuration
 */
function sendTestMail($conn, $to) {
    $content = '
        <h2 style="color:#1f2937; margin:0 0 16px 0; font-size:20px;">Test Email</h2>
        <p style="color:#6b7280; font-size:15px; line-height:1.6;">This is a test email to confirm your SMTP settings are working correctly.</p>
        <p style="color:#10b981; font-size:15px; font-weight:600;">If you can read this, your email configuration is set up properly!</p>';

    $body = getEmailTemplate('Test Email', $content);
    return sendMail($conn, $to, 'Test Email - SMTP Configuration', $body);
}

/**
 * Send password reset email
 */
function sendPasswordResetMail($conn, $email, $resetUrl, $userName = '') {
    $greeting = $userName ? 'Hi ' . htmlspecialchars($userName) . ',' : 'Hi,';
    $content = '
        <h2 style="color:#1f2937; margin:0 0 16px 0; font-size:20px;">Reset Your Password</h2>
        <p style="color:#6b7280; font-size:15px; line-height:1.6;">' . $greeting . '</p>
        <p style="color:#6b7280; font-size:15px; line-height:1.6;">We received a request to reset the password for your account. Click the button below to set a new password:</p>
        <div style="text-align:center; margin:30px 0;">
            <a href="' . htmlspecialchars($resetUrl) . '" style="display:inline-block; background-color:#5B5FED; color:#ffffff; text-decoration:none; padding:14px 32px; border-radius:8px; font-weight:600; font-size:15px;">Reset Password</a>
        </div>
        <p style="color:#6b7280; font-size:15px; line-height:1.6;">This link will expire in <strong>1 hour</strong>. If you did not request a password reset, you can safely ignore this email.</p>
        <p style="color:#9ca3af; font-size:13px; margin-top:24px;">If the button doesn\'t work, copy and paste this link into your browser:<br>
        <a href="' . htmlspecialchars($resetUrl) . '" style="color:#5B5FED; word-break:break-all;">' . htmlspecialchars($resetUrl) . '</a></p>';

    $body = getEmailTemplate('Reset Your Password', $content);
    return sendMail($conn, $email, 'Password Reset Request', $body);
}

/**
 * Send expiry reminder email
 */
function sendExpiryReminderMail($conn, $user, $order, $package, $daysLeft) {
    $siteUrl = defined('SITE_URL') ? SITE_URL : '';
    $renewUrl = $siteUrl . '/user/renew.php?order_id=' . $order['id'];

    $urgencyColor = $daysLeft <= 3 ? '#EF4444' : ($daysLeft <= 7 ? '#F59E0B' : '#6B7280');
    $urgencyLabel = $daysLeft <= 1 ? 'Tomorrow' : "in {$daysLeft} days";

    $content = '
        <h2 style="color:#1f2937; margin:0 0 16px 0; font-size:20px;">Your Plan is Expiring Soon</h2>
        <p style="color:#6b7280; font-size:15px; line-height:1.6;">Hi ' . htmlspecialchars($user['name']) . ',</p>
        <p style="color:#6b7280; font-size:15px; line-height:1.6;">Your hosting plan is expiring <strong style="color:' . $urgencyColor . ';">' . $urgencyLabel . '</strong>. Renew now to avoid any service interruption.</p>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
            <tr style="background-color:#f9fafb;">
                <td style="padding:12px 16px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Package</td>
                <td style="padding:12px 16px; font-size:14px; font-weight:600; color:#1f2937; border-bottom:1px solid #e5e7eb;">' . htmlspecialchars($package['name']) . '</td>
            </tr>
            <tr>
                <td style="padding:12px 16px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Order #</td>
                <td style="padding:12px 16px; font-size:14px; font-weight:600; color:#1f2937; border-bottom:1px solid #e5e7eb;">' . htmlspecialchars($order['order_number']) . '</td>
            </tr>
            <tr style="background-color:#f9fafb;">
                <td style="padding:12px 16px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Expiry Date</td>
                <td style="padding:12px 16px; font-size:14px; font-weight:600; color:' . $urgencyColor . '; border-bottom:1px solid #e5e7eb;">' . date('M d, Y', strtotime($order['expiry_date'])) . '</td>
            </tr>
            <tr>
                <td style="padding:12px 16px; font-size:14px; color:#6b7280;">Days Remaining</td>
                <td style="padding:12px 16px; font-size:14px; font-weight:600; color:' . $urgencyColor . ';">' . $daysLeft . ' day' . ($daysLeft != 1 ? 's' : '') . '</td>
            </tr>
        </table>

        <div style="text-align:center; margin:30px 0;">
            <a href="' . htmlspecialchars($renewUrl) . '" style="display:inline-block; background-color:#5B5FED; color:#ffffff; text-decoration:none; padding:14px 32px; border-radius:8px; font-weight:600; font-size:15px;">Renew Now</a>
        </div>
        <p style="color:#9ca3af; font-size:13px;">If your plan expires, your services may be suspended until renewal.</p>';

    $body = getEmailTemplate('Plan Expiring Soon', $content);
    return sendMail($conn, $user['email'], 'Your ' . $package['name'] . ' plan expires ' . $urgencyLabel, $body);
}
?>
