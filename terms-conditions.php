<?php
session_start();
require_once 'config.php';
require_once 'components/settings_helper.php';

// Get company settings
$companyName = getCompanyName($conn);
$companyEmail = getSetting($conn, 'company_email', 'info@example.com');

$pageTitle = "Terms & Conditions - InfraLabs Cloud";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="Terms and Conditions for InfraLabs Cloud. Read our Terms of Service for hosting services.">
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>☁️</text></svg>">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="./assets/css/index.css">
    <link rel="stylesheet" href="./assets/css/pages.css">
</head>

<body>
    <?php include 'components/header.php'; ?>

    <!-- Page Hero -->
    <section class="page-hero">
        <div class="container">
            <h1 class="page-title">Terms & Conditions</h1>
            <p class="page-subtitle">Please read these terms carefully before using our services</p>
            <p class="page-subtitle">Last updated: <?php echo date('F d, Y'); ?></p>
        </div>
    </section>

    <!-- Terms & Conditions Content -->
    <section class="content-section">
        <div class="container">
            <div class="content-wrapper">
                <div class="content-main">
                    <div class="content-block">
                        <h2>1. Acceptance of Terms</h2>
                        <p>
                            By accessing and using the services provided by <?php echo htmlspecialchars($companyName); ?> 
                            ("we", "us", or "our"), you accept and agree to be bound by these Terms and Conditions. 
                            If you do not agree to these terms, please do not use our services.
                        </p>
                        <p>
                            These terms apply to all users of the service, including without limitation users who are browsers, 
                            customers, merchants, and/or contributors of content.
                        </p>
                    </div>

                    <div class="content-block">
                        <h2>2. Service Description</h2>
                        <p><?php echo htmlspecialchars($companyName); ?> provides web hosting services including:</p>
                        <ul>
                            <li>Shared Web Hosting</li>
                            <li>VPS (Virtual Private Server) Hosting</li>
                            <li>WordPress Hosting</li>
                            <li>Node.js Hosting</li>
                            <li>PHP Hosting</li>
                            <li>Email Hosting Services</li>
                            <li>Domain Registration and Management</li>
                            <li>SSL Certificates</li>
                            <li>Website Builder Tools</li>
                            <li>Related hosting services and applications</li>
                        </ul>
                    </div>

                    <div class="content-block">
                        <h2>3. Account Registration</h2>
                        
                        <h3>3.1 Account Requirements</h3>
                        <p>To use our services, you must:</p>
                        <ul>
                            <li>Be at least 18 years of age</li>
                            <li>Provide accurate and complete registration information</li>
                            <li>Maintain and update your information to keep it accurate and current</li>
                            <li>Maintain the security of your account credentials</li>
                            <li>Accept responsibility for all activities under your account</li>
                        </ul>

                        <h3>3.2 Account Security</h3>
                        <p>
                            You are responsible for maintaining the confidentiality of your account login credentials and 
                            for all activities that occur under your account. You must immediately notify us of any 
                            unauthorized use of your account.
                        </p>
                    </div>

                    <div class="content-block">
                        <h2>4. Acceptable Use Policy</h2>
                        
                        <h3>4.1 Prohibited Content</h3>
                        <p>You may not use our services to host, transmit, or share:</p>
                        <ul>
                            <li>Illegal content or content that promotes illegal activities</li>
                            <li>Copyrighted material without proper authorization</li>
                            <li>Malware, viruses, or any malicious software</li>
                            <li>Phishing pages or fraudulent content</li>
                            <li>Adult content (pornography, explicit material)</li>
                            <li>Content that is defamatory, harassing, or threatening</li>
                            <li>Spam or unsolicited email campaigns</li>
                            <li>Content that infringes on intellectual property rights</li>
                        </ul>

                        <h3>4.2 Prohibited Activities</h3>
                        <p>You may not:</p>
                        <ul>
                            <li>Use excessive server resources that affect other users</li>
                            <li>Engage in cryptocurrency mining activities</li>
                            <li>Run proxy services or TOR nodes</li>
                            <li>Conduct port scanning or network attacks</li>
                            <li>Attempt to gain unauthorized access to systems or networks</li>
                            <li>Resell our services without authorization</li>
                            <li>Create "doorway" pages designed solely for search engines</li>
                        </ul>

                        <div class="warning-box">
                            <strong><i class="fas fa-exclamation-triangle"></i> Violation Consequences</strong>
                            <p style="margin-top: 8px;">
                                Violation of our Acceptable Use Policy may result in immediate suspension or 
                                termination of your account without refund.
                            </p>
                        </div>
                    </div>

                    <div class="content-block">
                        <h2>5. Service Level Agreement (SLA)</h2>
                        
                        <h3>5.1 Uptime Guarantee</h3>
                        <p>
                            We guarantee 99.99% uptime for our hosting services. If we fail to meet this guarantee, 
                            you may be eligible for service credits.
                        </p>

                        <h3>5.2 Exclusions from SLA</h3>
                        <p>The uptime guarantee does not cover downtime caused by:</p>
                        <ul>
                            <li>Scheduled maintenance (with prior notice)</li>
                            <li>Force majeure events</li>
                            <li>Issues with your website's code or configuration</li>
                            <li>Third-party services or applications</li>
                            <li>Your violation of terms of service</li>
                            <li>DDoS attacks targeting your website</li>
                        </ul>
                    </div>

                    <div class="content-block">
                        <h2>6. Billing and Payment</h2>
                        
                        <h3>6.1 Payment Terms</h3>
                        <ul>
                            <li>All fees are payable in advance unless otherwise agreed</li>
                            <li>Payment is due on the date specified in your invoice</li>
                            <li>We accept major credit cards, PayPal, and other payment methods</li>
                            <li>All prices are in USD unless otherwise stated</li>
                            <li>Promotional prices apply only to the initial term</li>
                        </ul>

                        <h3>6.2 Renewals</h3>
                        <p>
                            Unless you cancel your service before the renewal date, your service will automatically 
                            renew at our standard renewal rates. We will charge your payment method on file for 
                            the renewal amount.
                        </p>
                        <p>
                            <strong>Important:</strong> Renewal prices may differ from promotional or initial pricing. 
                            Standard renewal rates apply after the initial term.
                        </p>

                        <h3>6.3 Late Payments</h3>
                        <p>
                            If payment is not received by the due date, we may suspend your account. Accounts 
                            suspended for non-payment may be terminated after 7 days.
                        </p>

                        <h3>6.4 Refunds</h3>
                        <p>
                            We offer a 7-day money-back guarantee on eligible services. Please refer to our 
                            <a href="refund-policy.php">Refund Policy</a> for complete details.
                        </p>
                    </div>

                    <div class="content-block">
                        <h2>7. Data and Backups</h2>
                        
                        <h3>7.1 Your Responsibility</h3>
                        <p>
                            You are solely responsible for maintaining backups of your data. While we provide 
                            automated backup services as a courtesy, these should not be considered a replacement 
                            for your own backup procedures.
                        </p>

                        <h3>7.2 Backup Services</h3>
                        <p>
                            We perform regular backups of our servers, but we cannot guarantee the restoration 
                            of your specific data. Backup retention periods vary by plan.
                        </p>

                        <h3>7.3 Data Loss</h3>
                        <p>
                            We are not liable for any data loss or corruption. You should maintain your own 
                            backup copies of all content and data.
                        </p>
                    </div>

                    <div class="content-block">
                        <h2>8. Resource Usage</h2>
                        
                        <h3>8.1 Fair Use Policy</h3>
                        <p>
                            "Unlimited" resources are subject to fair use. If your usage significantly exceeds 
                            typical customer usage patterns, we may contact you to upgrade to a more appropriate plan.
                        </p>

                        <h3>8.2 Resource Limits</h3>
                        <p>Shared hosting accounts are subject to:</p>
                        <ul>
                            <li>CPU usage limits</li>
                            <li>Memory (RAM) limits</li>
                            <li>Database query limits</li>
                            <li>Email sending limits</li>
                            <li>Inodes (files) limits</li>
                        </ul>
                        <p>Specific limits vary by plan and are available in our knowledge base.</p>
                    </div>

                    <div class="content-block">
                        <h2>9. Support Services</h2>
                        
                        <p>We provide 24/7 technical support for:</p>
                        <ul>
                            <li>Server-related issues</li>
                            <li>Hosting platform problems</li>
                            <li>Account management</li>
                            <li>General hosting questions</li>
                        </ul>

                        <p>Support does not include:</p>
                        <ul>
                            <li>Website development or coding assistance</li>
                            <li>Third-party application configuration</li>
                            <li>Custom script troubleshooting</li>
                            <li>SEO or marketing advice</li>
                        </ul>
                    </div>

                    <div class="content-block">
                        <h2>10. Termination</h2>
                        
                        <h3>10.1 Termination by You</h3>
                        <p>
                            You may cancel your service at any time through your account dashboard. Cancellation 
                            will take effect at the end of your current billing period unless you request immediate 
                            termination.
                        </p>

                        <h3>10.2 Termination by Us</h3>
                        <p>We may suspend or terminate your account if:</p>
                        <ul>
                            <li>You violate these Terms and Conditions</li>
                            <li>Payment is not received</li>
                            <li>Your account poses a security risk</li>
                            <li>You engage in illegal activities</li>
                            <li>We discontinue the service (with notice)</li>
                        </ul>

                        <h3>10.3 Data Retention After Termination</h3>
                        <p>
                            After termination, your data may be retained for up to 7 days, after which it will 
                            be permanently deleted. It is your responsibility to download your data before termination.
                        </p>
                    </div>

                    <div class="content-block">
                        <h2>11. Limitation of Liability</h2>
                        <p>
                            To the maximum extent permitted by law, <?php echo htmlspecialchars($companyName); ?> 
                            shall not be liable for any indirect, incidental, special, consequential, or punitive 
                            damages, or any loss of profits or revenues, whether incurred directly or indirectly, 
                            or any loss of data, use, goodwill, or other intangible losses.
                        </p>
                        <p>
                            Our total liability in any matter arising out of or related to these terms is limited 
                            to the fees you paid to us in the 12 months prior to the event giving rise to the liability.
                        </p>
                    </div>

                    <div class="content-block">
                        <h2>12. Indemnification</h2>
                        <p>
                            You agree to indemnify and hold harmless <?php echo htmlspecialchars($companyName); ?>, 
                            its officers, directors, employees, and agents from any claims, demands, damages, or 
                            expenses (including attorney fees) arising from:
                        </p>
                        <ul>
                            <li>Your use of our services</li>
                            <li>Your violation of these terms</li>
                            <li>Your violation of any rights of another party</li>
                            <li>Content you upload or transmit through our services</li>
                        </ul>
                    </div>

                    <div class="content-block">
                        <h2>13. Intellectual Property</h2>
                        
                        <h3>13.1 Our Property</h3>
                        <p>
                            All content, features, and functionality of our services, including but not limited to 
                            software, text, graphics, logos, and service marks, are owned by 
                            <?php echo htmlspecialchars($companyName); ?> and are protected by copyright, 
                            trademark, and other intellectual property laws.
                        </p>

                        <h3>13.2 Your Content</h3>
                        <p>
                            You retain all rights to content you upload to our services. By uploading content, 
                            you grant us a limited license to host and display that content as necessary to provide 
                            our services.
                        </p>
                    </div>

                    <div class="content-block">
                        <h2>14. Privacy Policy</h2>
                        <p>
                            Your use of our services is also governed by our <a href="privacy-policy.php">Privacy Policy</a>. 
                            Please review our Privacy Policy to understand our practices regarding your personal information.
                        </p>
                    </div>

                    <div class="content-block">
                        <h2>15. Modifications to Terms</h2>
                        <p>
                            We reserve the right to modify these terms at any time. We will notify you of significant 
                            changes via email or through a notice on our website. Your continued use of our services 
                            after such modifications constitutes your acceptance of the updated terms.
                        </p>
                    </div>

                    <div class="content-block">
                        <h2>16. Governing Law</h2>
                        <p>
                            These terms shall be governed by and construed in accordance with applicable laws, 
                            without regard to conflict of law provisions. Any disputes arising from these terms 
                            shall be subject to the exclusive jurisdiction of the courts in our location.
                        </p>
                    </div>

                    <div class="content-block">
                        <h2>17. Severability</h2>
                        <p>
                            If any provision of these terms is found to be unenforceable or invalid, that provision 
                            will be limited or eliminated to the minimum extent necessary so that the remaining terms 
                            will otherwise remain in full force and effect.
                        </p>
                    </div>

                    <div class="content-block">
                        <h2>18. Contact Information</h2>
                        <p>
                            If you have any questions about these Terms and Conditions, please contact us:
                        </p>
                        <ul>
                            <li><strong>Email:</strong> <?php echo htmlspecialchars($companyEmail); ?></li>
                            <li><strong>Company:</strong> <?php echo htmlspecialchars($companyName); ?></li>
                        </ul>
                    </div>

                    <div class="info-box" style="margin-top: 48px;">
                        <strong><i class="fas fa-info-circle"></i> Agreement</strong>
                        <p style="margin-top: 8px;">
                            By using our services, you acknowledge that you have read, understood, and agreed 
                            to be bound by these Terms and Conditions.
                        </p>
                    </div>
                </div>

                <!-- Sidebar Navigation -->
                <div class="content-sidebar">
                    <div class="sidebar-nav">
                        <h3>Quick Navigation</h3>
                        <ul>
                            <li><a href="#acceptance">Acceptance of Terms</a></li>
                            <li><a href="#service">Service Description</a></li>
                            <li><a href="#account">Account Registration</a></li>
                            <li><a href="#acceptable-use">Acceptable Use</a></li>
                            <li><a href="#sla">Service Level Agreement</a></li>
                            <li><a href="#billing">Billing & Payment</a></li>
                            <li><a href="#data">Data & Backups</a></li>
                            <li><a href="#resources">Resource Usage</a></li>
                            <li><a href="#support">Support Services</a></li>
                            <li><a href="#termination">Termination</a></li>
                            <li><a href="#liability">Limitation of Liability</a></li>
                            <li><a href="#contact">Contact</a></li>
                        </ul>
                    </div>

                    <div class="sidebar-cta">
                        <i class="fas fa-file-contract"></i>
                        <h4>Have Questions?</h4>
                        <p>Our support team is available 24/7 to answer any questions about our terms.</p>
                        <a href="index.php#contact" class="btn-primary" style="margin-top: 16px; text-align: center; display: inline-block; width: 100%;">Contact Support</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'components/footer.php'; ?>
</body>
</html>
