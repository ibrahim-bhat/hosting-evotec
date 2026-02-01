<?php
session_start();
require_once 'config.php';
require_once 'components/settings_helper.php';

// Get company settings
$companyName = getCompanyName($conn);
$companyLogo = getCompanyLogo($conn);
$companyEmail = getSetting($conn, 'company_email', 'info@example.com');

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

$pageTitle = "Refund Policy - " . $companyName;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="Refund Policy for <?php echo htmlspecialchars($companyName); ?>. Learn about our 7-day money-back guarantee and refund process.">
    
    <!-- Favicon -->
    <?php if (!empty($companyLogo)): ?>
        <link rel="icon" href="<?php echo htmlspecialchars($companyLogo); ?>" type="image/x-icon">
    <?php else: ?>
        <link rel="icon" href="netic/images/fevicon.png" type="image/gif" />
    <?php endif; ?>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="./assets/css/index.css">
    <link rel="stylesheet" href="./assets/css/pages.css">
</head>

<body>
    <!-- Header Navigation -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <div class="navbar-brand">
                    <?php if (!empty($companyLogo)): ?>
                        <a href="index.php">
                            <img src="<?php echo htmlspecialchars($companyLogo); ?>" alt="<?php echo htmlspecialchars($companyName); ?>" class="logo-img">
                        </a>
                    <?php else: ?>
                        <a href="index.php" style="text-decoration: none; color: inherit;">
                            <div class="logo-box">
                                <i class="fas fa-cloud"></i>
                                <span class="logo-text"><?php echo htmlspecialchars($companyName); ?></span>
                            </div>
                        </a>
                    <?php endif; ?>
                </div>

                <button class="mobile-toggle" id="mobileToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <div class="navbar-menu" id="navbarMenu">
                    <a href="index.php" class="nav-link">Home</a>
                    <a href="index.php#features" class="nav-link">Features</a>
                    <a href="index.php#pricing" class="nav-link">Pricing</a>
                    <?php if ($isLoggedIn): ?>
                        <a href="<?php echo $isAdmin ? 'admin/index.php' : 'user/index.php'; ?>" class="nav-link">
                            <i class="fas fa-user"></i> Dashboard
                        </a>
                        <a href="logout.php" class="btn-secondary">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="nav-link">Login</a>
                        <a href="register.php" class="btn-primary">Get Started</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <!-- Page Hero -->
    <section class="page-hero">
        <div class="container">
            <h1 class="page-title">Refund Policy</h1>
            <p class="page-subtitle">7-Day Money-Back Guarantee | Last updated: <?php echo date('F d, Y'); ?></p>
        </div>
    </section>

    <!-- Refund Policy Content -->
    <section class="content-section">
        <div class="container">
            <div class="content-wrapper">
                <div class="content-main">
                    <div class="content-block">
                        <h2>Our Commitment to You</h2>
                        <p>
                            At <?php echo htmlspecialchars($companyName); ?>, we stand behind the quality of our hosting services. 
                            We offer a 7-day money-back guarantee on all hosting plans to ensure your complete satisfaction.
                        </p>
                        <div class="success-box">
                            <strong><i class="fas fa-shield-alt"></i> 7-Day Money-Back Guarantee</strong>
                            <p style="margin-top: 8px;">
                                If you're not completely satisfied with our service within the first 7 days of your purchase, 
                                we'll refund your payment—no questions asked.
                            </p>
                        </div>
                    </div>

                    <div class="content-block">
                        <h2>Refund Eligibility</h2>
                        <p>To be eligible for a refund, the following conditions must be met:</p>
                        
                        <h3>Eligible for Refund</h3>
                        <ul>
                            <li>Refund request submitted within 7 days of initial purchase</li>
                            <li>First-time purchases of shared hosting, VPS, or WordPress hosting</li>
                            <li>Account has not violated our Terms of Service</li>
                            <li>No excessive resource usage or abuse detected</li>
                            <li>Request made by the account owner or authorized representative</li>
                        </ul>

                        <h3>Not Eligible for Refund</h3>
                        <ul>
                            <li>Requests made after the 7-day period has expired</li>
                            <li>Domain name registration fees (these are non-refundable)</li>
                            <li>Setup fees or migration services</li>
                            <li>SSL certificates purchased separately</li>
                            <li>Add-on services and premium features</li>
                            <li>Renewal charges (only initial purchase qualifies)</li>
                            <li>Accounts suspended for Terms of Service violations</li>
                            <li>Services already delivered (e.g., completed migrations)</li>
                        </ul>
                    </div>

                    <div class="content-block">
                        <h2>How to Request a Refund</h2>
                        <p>Follow these simple steps to request a refund:</p>
                        
                        <ol>
                            <li>
                                <strong>Submit a Support Ticket</strong>
                                <p>Log in to your account and submit a support ticket requesting a refund. Include your account details and reason for the refund (optional).</p>
                            </li>
                            <li>
                                <strong>Email Us</strong>
                                <p>Send an email to <?php echo htmlspecialchars($companyEmail); ?> with your account information and refund request.</p>
                            </li>
                            <li>
                                <strong>Verification</strong>
                                <p>Our team will verify your account and eligibility. This typically takes 24-48 hours.</p>
                            </li>
                            <li>
                                <strong>Processing</strong>
                                <p>Once approved, your refund will be processed to the original payment method.</p>
                            </li>
                        </ol>

                        <div class="info-box">
                            <strong><i class="fas fa-info-circle"></i> Required Information</strong>
                            <p style="margin-top: 8px;">Please provide your account username, registered email address, and order number when requesting a refund.</p>
                        </div>
                    </div>

                    <div class="content-block">
                        <h2>Refund Processing Time</h2>
                        <p>We strive to process refunds as quickly as possible:</p>
                        <ul>
                            <li><strong>Approval Time:</strong> 24-48 hours for eligibility verification</li>
                            <li><strong>Processing Time:</strong> 3-5 business days to process the refund</li>
                            <li><strong>Bank Processing:</strong> 5-10 business days for the funds to appear in your account (depends on your bank)</li>
                        </ul>
                        <p>
                            Total refund time from request to receiving funds: 10-15 business days on average.
                        </p>
                    </div>

                    <div class="content-block">
                        <h2>Refund Methods</h2>
                        <p>Refunds are processed using the original payment method:</p>
                        <ul>
                            <li><strong>Credit/Debit Card:</strong> Refund to the same card used for payment</li>
                            <li><strong>PayPal:</strong> Refund to your PayPal account</li>
                            <li><strong>Razorpay:</strong> Refund to the original payment source</li>
                            <li><strong>Bank Transfer:</strong> Refund to the same bank account</li>
                        </ul>
                        <p>
                            If the original payment method is no longer available, please contact our support team 
                            to arrange an alternative refund method.
                        </p>
                    </div>

                    <div class="content-block">
                        <h2>Service Cancellation</h2>
                        <p>When a refund is processed:</p>
                        <ul>
                            <li>Your hosting account will be immediately suspended</li>
                            <li>All data will be retained for 7 days for backup purposes</li>
                            <li>After 7 days, all data will be permanently deleted</li>
                            <li>Domain names (if purchased) will remain active until expiration</li>
                            <li>Email services will be discontinued immediately</li>
                        </ul>
                        
                        <div class="warning-box">
                            <strong><i class="fas fa-exclamation-triangle"></i> Important Notice</strong>
                            <p style="margin-top: 8px;">
                                Please ensure you have backed up all your data before requesting a refund. 
                                We are not responsible for any data loss after account cancellation.
                            </p>
                        </div>
                    </div>

                    <div class="content-block">
                        <h2>Partial Refunds</h2>
                        <p>In certain situations, partial refunds may be issued:</p>
                        <ul>
                            <li>If only specific add-on services are being cancelled</li>
                            <li>For prorated refunds on upgraded features within the 7-day period</li>
                            <li>When domain registration fees need to be excluded</li>
                        </ul>
                        <p>
                            Partial refund calculations will be clearly explained by our support team before processing.
                        </p>
                    </div>

                    <div class="content-block">
                        <h2>Exceptions and Special Cases</h2>
                        
                        <h3>Promotional Offers</h3>
                        <p>
                            If you purchased hosting during a promotional period with a discount, the refund will be 
                            based on the amount you actually paid, not the regular price.
                        </p>

                        <h3>Bundle Packages</h3>
                        <p>
                            For bundle packages, refunds may be prorated based on eligible components. Non-refundable 
                            items will be deducted from the total refund amount.
                        </p>

                        <h3>Legal Requirements</h3>
                        <p>
                            We reserve the right to refuse refunds if we believe there is fraud, abuse, or violation 
                            of our Terms of Service. Accounts terminated for abuse are not eligible for refunds.
                        </p>
                    </div>

                    <div class="content-block">
                        <h2>Questions About Refunds?</h2>
                        <p>
                            If you have any questions about our refund policy or need assistance with a refund request, 
                            our support team is here to help.
                        </p>
                        <ul>
                            <li><strong>Email:</strong> <?php echo htmlspecialchars($companyEmail); ?></li>
                            <li><strong>Response Time:</strong> Within 24 hours</li>
                            <li><strong>Support:</strong> Available 24/7</li>
                        </ul>
                    </div>

                    <div class="content-block">
                        <h2>Policy Updates</h2>
                        <p>
                            We may update this Refund Policy from time to time. Any changes will be posted on this page 
                            and will become effective immediately upon posting. Your continued use of our services after 
                            any changes indicates your acceptance of the updated policy.
                        </p>
                    </div>
                </div>

                <!-- Sidebar Navigation -->
                <div class="content-sidebar">
                    <div class="sidebar-nav">
                        <h3>Quick Navigation</h3>
                        <ul>
                            <li><a href="#commitment">Our Commitment</a></li>
                            <li><a href="#eligibility">Eligibility</a></li>
                            <li><a href="#how-to-request">How to Request</a></li>
                            <li><a href="#processing-time">Processing Time</a></li>
                            <li><a href="#methods">Refund Methods</a></li>
                            <li><a href="#cancellation">Service Cancellation</a></li>
                            <li><a href="#questions">Questions</a></li>
                        </ul>
                    </div>

                    <div class="sidebar-cta">
                        <i class="fas fa-shield-alt"></i>
                        <h4>7-Day Guarantee</h4>
                        <p>Try our services risk-free. If you're not satisfied, get a full refund within 7 days.</p>
                        <a href="register.php" class="btn-primary" style="margin-top: 16px; text-align: center; display: inline-block; width: 100%;">Get Started</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <div class="footer-brand">
                        <i class="fas fa-cloud"></i>
                        <span><?php echo htmlspecialchars($companyName); ?></span>
                    </div>
                    <p class="footer-description">
                        Professional hosting solutions with 99.99% uptime guarantee, 
                        24/7 support, and cutting-edge technology.
                    </p>
                </div>

                <div class="footer-column">
                    <h4>Hosting</h4>
                    <ul>
                        <li><a href="index.php#pricing">Shared Hosting</a></li>
                        <li><a href="index.php#pricing">VPS Hosting</a></li>
                        <li><a href="index.php#pricing">WordPress Hosting</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="index.php#reviews">Reviews</a></li>
                        <li><a href="index.php">Home</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="privacy-policy.php">Privacy Policy</a></li>
                        <li><a href="terms-conditions.php">Terms & Conditions</a></li>
                        <li><a href="refund-policy.php">Refund Policy</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($companyName); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        document.getElementById('mobileToggle').addEventListener('click', function() {
            document.getElementById('navbarMenu').classList.toggle('active');
            this.classList.toggle('active');
        });
    </script>
</body>
</html>
