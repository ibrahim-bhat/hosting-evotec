<?php
session_start();
$pageTitle = "Message Sent - InfraLabs Cloud";
$pageDescription = "Thank you for contacting InfraLabs Cloud. We'll get back to you soon.";
include 'components/header.php';
?>

<!-- Success Section -->
<section class="content-section" style="padding: 120px 0;">
    <div class="container">
        <div style="max-width: 600px; margin: 0 auto; text-align: center;">
            <div style="width: 100px; height: 100px; background: rgba(16, 185, 129, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 32px;">
                <i class="fas fa-check-circle" style="font-size: 60px; color: var(--success-green);"></i>
            </div>
            
            <h1 style="font-size: 42px; font-weight: 800; margin-bottom: 16px;">Message Sent Successfully!</h1>
            <p style="font-size: 18px; color: var(--text-secondary); margin-bottom: 32px; line-height: 1.7;">
                Thank you for contacting InfraLabs Cloud. We've received your message and our team will get back to you within 24 hours.
            </p>

            <div style="background: var(--card-bg); border: 2px solid var(--border-color); border-radius: 12px; padding: 32px; margin-bottom: 32px;">
                <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 16px;">What happens next?</h3>
                <ul style="list-style: none; text-align: left; max-width: 400px; margin: 0 auto;">
                    <li style="padding: 12px 0; display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-envelope" style="color: var(--primary-blue);"></i>
                        <span>You'll receive a confirmation email shortly</span>
                    </li>
                    <li style="padding: 12px 0; display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-user-headset" style="color: var(--primary-blue);"></i>
                        <span>Our team will review your message</span>
                    </li>
                    <li style="padding: 12px 0; display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-reply" style="color: var(--primary-blue);"></i>
                        <span>We'll respond within 24 hours</span>
                    </li>
                </ul>
            </div>

            <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                <a href="index.php" class="btn-primary btn-large">
                    <i class="fas fa-home"></i> Back to Home
                </a>
                <a href="features.php" class="btn-outline btn-large">
                    <i class="fas fa-star"></i> Explore Features
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'components/footer.php'; ?>
