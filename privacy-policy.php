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

$pageTitle = "Privacy Policy - " . $companyName;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="Privacy Policy for <?php echo htmlspecialchars($companyName); ?>. Learn how we collect, use, and protect your personal information.">
    
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
            <h1 class="page-title">Privacy Policy</h1>
            <p class="page-subtitle">Last updated: <?php echo date('F d, Y'); ?></p>
        </div>
    </section>

    <!-- Privacy Policy Content -->
    <section class="content-section">
        <div class="container">
            <div class="content-wrapper">
                <div class="content-main">
                    <div class="content-block">
                        <h2>Introduction</h2>
                        <p>
                            <?php echo htmlspecialchars($companyName); ?> ("we", "our", or "us") is committed to protecting your privacy. 
                            This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you 
                            visit our website and use our services.
                        </p>
                        <p>
                            Please read this privacy policy carefully. If you do not agree with the terms of this privacy policy, 
                            please do not access the site or use our services.
                        </p>
                    </div>

                    <div class="content-block">
                        <h2>Information We Collect</h2>
                        <p>We collect information that you provide directly to us when using our services:</p>
                        
                        <h3>Personal Information</h3>
                        <ul>
                            <li>Name and contact information (email address, phone number)</li>
                            <li>Billing and payment information</li>
                            <li>Account credentials (username and password)</li>
                            <li>Technical support communications</li>
                            <li>Any other information you choose to provide</li>
                        </ul>

                        <h3>Automatically Collected Information</h3>
                        <ul>
                            <li>IP address and geographic location</li>
                            <li>Browser type and version</li>
                            <li>Device information</li>
                            <li>Pages visited and time spent on our website</li>
                            <li>Referring website addresses</li>
                            <li>Operating system information</li>
                        </ul>
                    </div>

                    <div class="content-block">
                        <h2>How We Use Your Information</h2>
                        <p>We use the information we collect to:</p>
                        <ul>
                            <li>Provide, operate, and maintain our hosting services</li>
                            <li>Process your transactions and manage your account</li>
                            <li>Send you technical notices, updates, and support messages</li>
                            <li>Respond to your comments, questions, and customer service requests</li>
                            <li>Monitor and analyze usage patterns to improve our services</li>
                            <li>Detect, prevent, and address technical issues and security threats</li>
                            <li>Send promotional communications (with your consent)</li>
                            <li>Comply with legal obligations</li>
                        </ul>
                    </div>

                    <div class="content-block">
                        <h2>Data Protection and Security</h2>
                        <p>
                            We implement appropriate technical and organizational security measures to protect your personal information 
                            against unauthorized access, alteration, disclosure, or destruction.
                        </p>
                        <ul>
                            <li>SSL/TLS encryption for data transmission</li>
                            <li>Encrypted storage of sensitive information</li>
                            <li>Regular security audits and updates</li>
                            <li>Access controls and authentication systems</li>
                            <li>Employee training on data protection</li>
                            <li>Regular backups and disaster recovery procedures</li>
                        </ul>
                    </div>

                    <div class="content-block">
                        <h2>Cookies and Tracking Technologies</h2>
                        <p>
                            We use cookies and similar tracking technologies to track activity on our website and hold certain information. 
                            Cookies are files with small amounts of data which may include an anonymous unique identifier.
                        </p>
                        <p>You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent. However, 
                            if you do not accept cookies, you may not be able to use some portions of our service.</p>
                    </div>

                    <div class="content-block">
                        <h2>Third-Party Service Providers</h2>
                        <p>We may share your information with third-party service providers who perform services on our behalf, including:</p>
                        <ul>
                            <li>Payment processing (Razorpay, PayPal, etc.)</li>
                            <li>Cloud infrastructure providers</li>
                            <li>Email service providers</li>
                            <li>Analytics services</li>
                            <li>Customer support platforms</li>
                        </ul>
                        <p>These third parties are obligated to protect your information and use it only for the purposes we specify.</p>
                    </div>

                    <div class="content-block">
                        <h2>Data Retention</h2>
                        <p>
                            We retain your personal information for as long as necessary to provide our services and fulfill the purposes 
                            outlined in this Privacy Policy. We will also retain and use your information to comply with legal obligations, 
                            resolve disputes, and enforce our agreements.
                        </p>
                    </div>

                    <div class="content-block">
                        <h2>Your Rights</h2>
                        <p>You have the following rights regarding your personal information:</p>
                        <ul>
                            <li><strong>Access:</strong> Request access to your personal data</li>
                            <li><strong>Correction:</strong> Request correction of inaccurate data</li>
                            <li><strong>Deletion:</strong> Request deletion of your data (subject to legal requirements)</li>
                            <li><strong>Portability:</strong> Request transfer of your data to another service</li>
                            <li><strong>Objection:</strong> Object to processing of your data</li>
                            <li><strong>Withdraw Consent:</strong> Withdraw consent for data processing</li>
                        </ul>
                        <p>To exercise these rights, please contact us at <?php echo htmlspecialchars($companyEmail); ?></p>
                    </div>

                    <div class="content-block">
                        <h2>Children's Privacy</h2>
                        <p>
                            Our services are not intended for individuals under the age of 18. We do not knowingly collect personal 
                            information from children. If you believe we have collected information from a child, please contact us 
                            immediately.
                        </p>
                    </div>

                    <div class="content-block">
                        <h2>Changes to This Privacy Policy</h2>
                        <p>
                            We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new 
                            Privacy Policy on this page and updating the "Last updated" date.
                        </p>
                        <p>
                            You are advised to review this Privacy Policy periodically for any changes. Changes to this Privacy Policy 
                            are effective when they are posted on this page.
                        </p>
                    </div>

                    <div class="content-block">
                        <h2>Contact Us</h2>
                        <p>If you have any questions about this Privacy Policy, please contact us:</p>
                        <ul>
                            <li><strong>Email:</strong> <?php echo htmlspecialchars($companyEmail); ?></li>
                            <li><strong>Website:</strong> <a href="index.php"><?php echo htmlspecialchars($companyName); ?></a></li>
                        </ul>
                    </div>
                </div>

                <!-- Sidebar Navigation -->
                <div class="content-sidebar">
                    <div class="sidebar-nav">
                        <h3>Quick Links</h3>
                        <ul>
                            <li><a href="#introduction">Introduction</a></li>
                            <li><a href="#information-we-collect">Information We Collect</a></li>
                            <li><a href="#how-we-use">How We Use Your Information</a></li>
                            <li><a href="#data-protection">Data Protection</a></li>
                            <li><a href="#cookies">Cookies</a></li>
                            <li><a href="#third-party">Third-Party Services</a></li>
                            <li><a href="#your-rights">Your Rights</a></li>
                            <li><a href="#contact">Contact Us</a></li>
                        </ul>
                    </div>

                    <div class="sidebar-cta">
                        <i class="fas fa-shield-alt"></i>
                        <h4>Your Data is Safe</h4>
                        <p>We use industry-standard security measures to protect your information.</p>
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
