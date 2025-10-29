<?php
require_once 'config.php';
require_once 'components/hosting_helper.php';
require_once 'components/settings_helper.php';

// Get active packages
$packages = getActivePackages($conn);

// Get company settings
$companyName = getCompanyName($conn);
$companyLogo = getCompanyLogo($conn);
$companyEmail = getSetting($conn, 'company_email', 'info@example.com');
$companyPhone = getSetting($conn, 'company_phone', '+91 123 456 7890');

$pageTitle = "Professional Web Hosting Solutions";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo htmlspecialchars($companyName); ?></title>
    <meta name="description" content="Professional web hosting solutions with 99.9% uptime, 24/7 support, and enterprise-grade security. Choose from our range of hosting plans designed for businesses of all sizes.">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #7c3aed;
            --accent-color: #06b6d4;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            overflow-x: hidden;
        }
        
        /* Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--primary-color);
        }
        
        .nav-link {
            font-weight: 500;
            color: var(--dark-color);
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--primary-color);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            padding: 12px 24px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 120px 0 80px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)"/><circle cx="800" cy="300" r="150" fill="url(%23a)"/><circle cx="400" cy="700" r="120" fill="url(%23a)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            max-width: 600px;
        }
        
        .hero-stats {
            display: flex;
            gap: 2rem;
            margin-top: 3rem;
            flex-wrap: wrap;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            display: block;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        /* Features Section */
        .features-section {
            padding: 100px 0;
            background: var(--light-color);
        }
        
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        
        .feature-icon i {
            font-size: 1.5rem;
            color: white;
        }
        
        /* Pricing Section */
        .pricing-section {
            padding: 100px 0;
            background: white;
        }
        
        .pricing-card {
            background: white;
            border: 2px solid var(--border-color);
            border-radius: 20px;
            padding: 2.5rem;
            position: relative;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-xl);
        }
        
        .pricing-card.popular {
            border-color: var(--primary-color);
            box-shadow: var(--shadow-lg);
        }
        
        .pricing-card.popular::before {
            content: 'MOST POPULAR';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary-color);
            color: white;
            padding: 8px 24px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .pricing-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }
        
        .pricing-description {
            color: var(--text-muted);
            margin-bottom: 2rem;
        }
        
        .pricing-price {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .pricing-period {
            color: var(--text-muted);
            margin-bottom: 2rem;
        }
        
        .pricing-features {
            list-style: none;
            margin-bottom: 2rem;
        }
        
        .pricing-features li {
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
        }
        
        .pricing-features li:last-child {
            border-bottom: none;
        }
        
        .pricing-features li i {
            color: var(--success-color);
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }
        
        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, var(--dark-color) 0%, #374151 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .cta-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }
        
        .cta-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }
        
        /* Footer */
        .footer {
            background: var(--dark-color);
            color: white;
            padding: 60px 0 30px;
        }
        
        .footer-brand {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 0.5rem;
        }
        
        .footer-links a {
            color: #9ca3af;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-stats {
                gap: 1rem;
            }
            
            .pricing-card {
                padding: 2rem;
            }
        }
        
        /* Animation Classes */
        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
        
        .fade-in-up.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">
            <?php if (!empty($companyLogo)): ?>
                <img src="<?php echo htmlspecialchars($companyLogo); ?>" alt="<?php echo htmlspecialchars($companyName); ?>" height="40" class="me-2">
            <?php endif; ?>
            <?php echo htmlspecialchars($companyName); ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#features">Features</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#pricing">Pricing</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contact">Contact</a>
                </li>
                <li class="nav-item ms-2">
                    <a href="login.php" class="btn btn-primary">Get Started</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content" data-aos="fade-right">
                    <h1 class="hero-title">Professional Web Hosting Solutions</h1>
                    <p class="hero-subtitle">
                        Experience lightning-fast performance with our enterprise-grade hosting infrastructure. 
                        Built for businesses that demand reliability, security, and scalability.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#pricing" class="btn btn-primary btn-lg">View Plans</a>
                        <a href="login.php" class="btn btn-outline-light btn-lg">Get Started</a>
                    </div>
                    
                    <div class="hero-stats">
                        <div class="stat-item">
                            <span class="stat-number">99.9%</span>
                            <span class="stat-label">Uptime SLA</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">24/7</span>
                            <span class="stat-label">Support</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">10K+</span>
                            <span class="stat-label">Happy Customers</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="text-center" data-aos="fade-left">
                    <div class="position-relative">
                        <div class="bg-white rounded-4 p-5 shadow-lg">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-success rounded-circle p-2 me-3">
                                    <i class="bi bi-check-lg text-white"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">Server Status</h5>
                                    <small class="text-muted">All systems operational</small>
                                </div>
                            </div>
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="h4 text-primary mb-1">99.9%</div>
                                    <small class="text-muted">Uptime</small>
                                </div>
                                <div class="col-4">
                                    <div class="h4 text-success mb-1">45ms</div>
                                    <small class="text-muted">Response</small>
                                </div>
                                <div class="col-4">
                                    <div class="h4 text-info mb-1">100%</div>
                                    <small class="text-muted">SSL</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section" id="features">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold mb-3">Why Choose Our Hosting?</h2>
            <p class="lead text-muted">Enterprise-grade infrastructure with unmatched performance and reliability</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon">
                        <i class="bi bi-lightning-charge"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Lightning Fast</h5>
                    <p class="text-muted">SSD storage and optimized servers ensure your website loads in milliseconds.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Enterprise Security</h5>
                    <p class="text-muted">Advanced security measures including DDoS protection and SSL certificates.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-icon">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h5 class="fw-bold mb-3">24/7 Expert Support</h5>
                    <p class="text-muted">Round-the-clock support from our team of hosting specialists.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-icon">
                        <i class="bi bi-arrow-clockwise"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Daily Backups</h5>
                    <p class="text-muted">Automated daily backups ensure your data is always safe and recoverable.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Scalable Resources</h5>
                    <p class="text-muted">Easily scale your hosting resources as your business grows.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card" data-aos="fade-up" data-aos-delay="600">
                    <div class="feature-icon">
                        <i class="bi bi-globe"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Global CDN</h5>
                    <p class="text-muted">Content delivery network ensures fast loading worldwide.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section class="pricing-section" id="pricing">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold mb-3">Choose Your Perfect Plan</h2>
            <p class="lead text-muted">Flexible pricing options designed for businesses of all sizes</p>
        </div>
        
        <div class="row g-4">
            <?php if (!empty($packages)): ?>
                <?php foreach ($packages as $index => $package): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="pricing-card <?php echo $package['is_popular'] ? 'popular' : ''; ?>" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                            <div class="pricing-name"><?php echo htmlspecialchars($package['name']); ?></div>
                            <div class="pricing-description"><?php echo htmlspecialchars($package['short_description']); ?></div>
                            
                            <div class="pricing-price">₹<?php echo number_format($package['price_monthly'], 0); ?></div>
                            <div class="pricing-period">per month</div>
                            
                            <ul class="pricing-features">
                                <li>
                                    <i class="bi bi-hdd"></i>
                                    <span><strong><?php echo $package['storage_gb']; ?> GB</strong> SSD Storage</span>
                                </li>
                                <li>
                                    <i class="bi bi-cloud-arrow-up"></i>
                                    <span><strong><?php echo $package['bandwidth_gb']; ?> GB</strong> Bandwidth</span>
                                </li>
                                <li>
                                    <i class="bi bi-globe"></i>
                                    <span><strong><?php echo $package['allowed_websites'] > 999 ? 'Unlimited' : $package['allowed_websites']; ?></strong> Websites</span>
                                </li>
                                <li>
                                    <i class="bi bi-database"></i>
                                    <span><strong><?php echo $package['database_limit'] > 999 ? 'Unlimited' : $package['database_limit']; ?></strong> Databases</span>
                                </li>
                                <?php if ($package['email_accounts'] > 0): ?>
                                    <li>
                                        <i class="bi bi-envelope"></i>
                                        <span><strong><?php echo $package['email_accounts'] > 999 ? 'Unlimited' : $package['email_accounts']; ?></strong> Email Accounts</span>
                                    </li>
                                <?php endif; ?>
                                <?php if ($package['ssh_access']): ?>
                                    <li>
                                        <i class="bi bi-terminal"></i>
                                        <span>SSH Access</span>
                                    </li>
                                <?php endif; ?>
                                <?php if ($package['ssl_free']): ?>
                                    <li>
                                        <i class="bi bi-shield-check"></i>
                                        <span>Free SSL Certificate</span>
                                    </li>
                                <?php endif; ?>
                                <?php if ($package['daily_backups']): ?>
                                    <li>
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                        <span>Daily Backups</span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                            
                            <a href="select-package.php?package=<?php echo $package['slug']; ?>&cycle=monthly" class="btn btn-primary w-100">
                                Get Started
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="bi bi-server display-1 text-muted"></i>
                        <h3 class="mt-3">No Packages Available</h3>
                        <p class="text-muted">Please check back later for our hosting plans.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center" data-aos="fade-up">
                <h2 class="cta-title">Ready to Launch Your Website?</h2>
                <p class="cta-subtitle">Join thousands of satisfied customers who trust us with their hosting needs.</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="login.php" class="btn btn-primary btn-lg">Start Your Journey</a>
                    <a href="register.php" class="btn btn-outline-light btn-lg">Create Account</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="footer-brand"><?php echo htmlspecialchars($companyName); ?></div>
                <p class="text-muted mb-3">Professional web hosting solutions for businesses of all sizes. Reliable, secure, and scalable hosting infrastructure.</p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-muted"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-muted"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="text-muted"><i class="bi bi-linkedin"></i></a>
                    <a href="#" class="text-muted"><i class="bi bi-instagram"></i></a>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">Hosting</h6>
                <ul class="footer-links">
                    <li><a href="#pricing">Web Hosting</a></li>
                    <li><a href="#pricing">VPS Hosting</a></li>
                    <li><a href="#pricing">Dedicated Servers</a></li>
                    <li><a href="#pricing">Cloud Hosting</a></li>
                </ul>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">Support</h6>
                <ul class="footer-links">
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Documentation</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">Status Page</a></li>
                </ul>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">Company</h6>
                <ul class="footer-links">
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Press</a></li>
                </ul>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">Contact</h6>
                <ul class="footer-links">
                    <li><i class="bi bi-envelope me-2"></i><?php echo htmlspecialchars($companyEmail); ?></li>
                    <li><i class="bi bi-telephone me-2"></i><?php echo htmlspecialchars($companyPhone); ?></li>
                </ul>
            </div>
        </div>
        
        <hr class="my-4" style="border-color: #374151;">
        
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-muted mb-0">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($companyName); ?>. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="#" class="text-muted me-3">Privacy Policy</a>
                <a href="#" class="text-muted">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS Animation -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // Initialize AOS
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });
    
    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            navbar.style.boxShadow = '0 2px 20px rgba(0,0,0,0.1)';
        } else {
            navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            navbar.style.boxShadow = 'none';
        }
    });
</script>

</body>
</html>