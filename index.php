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

// Get first letter of company name for logo fallback
$companyInitial = !empty($companyName) ? strtoupper(substr($companyName, 0, 1)) : 'V';

$pageTitle = "Premium Shared VPS Hosting - Deploy in 10 Minutes";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - <?php echo htmlspecialchars($companyName); ?></title>
    <meta name="description" content="Professional VPS hosting solutions with instant deployment, 70+ applications, and 24/7 support. Choose from our range of hosting plans designed for businesses of all sizes.">
    
    <!-- Favicon -->
    <?php if (!empty($companyLogo)): ?>
        <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($companyLogo); ?>">
    <?php endif; ?>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #1e3a8a;
            --primary-dark: #1e40af;
            --secondary: #2563eb;
            --accent: #3b82f6;
            --dark: #1e293b;
            --dark-light: #334155;
            --gray: #64748b;
            --light: #f1f5f9;
            --white: #ffffff;
            --green: #16a34a;
            --green-dark: #15803d;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            overflow-x: hidden;
        }

        nav {
            background: var(--primary);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--white);
            text-decoration: none;
        }

        .logo img {
            height: 40px;
            width: auto;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--primary);
            border-radius: 8px;
            font-size: 1.25rem;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--white);
            font-weight: 500;
            transition: opacity 0.3s;
        }

        .nav-links a:hover {
            opacity: 0.8;
        }

        .cta-btn {
            background: var(--white);
            color: var(--primary);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .cta-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.3);
        }

        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 5px;
        }

        .hamburger span {
            width: 28px;
            height: 3px;
            background: var(--white);
            transition: 0.3s;
            border-radius: 2px;
        }

        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(8px, 8px);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }

        .hero {
            background: var(--primary);
            color: var(--white);
            padding: 4rem 1.5rem;
            text-align: center;
        }

        .hero-content {
            max-width: 900px;
            margin: 0 auto;
        }

        .featured-badge {
            display: inline-block;
            background: var(--accent);
            color: var(--white);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .hero h1 {
            font-size: clamp(2rem, 5vw, 3.5rem);
            margin-bottom: 1rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .hero p {
            font-size: clamp(1rem, 2.5vw, 1.25rem);
            margin-bottom: 2rem;
            opacity: 0.95;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 3rem;
        }

        .btn-primary {
            background: var(--green);
            color: var(--white);
            padding: 1rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: inline-block;
        }

        .btn-primary:hover {
            background: var(--green-dark);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: transparent;
            color: var(--white);
            padding: 1rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            border: 2px solid var(--white);
            transition: all 0.3s;
        }

        .btn-secondary:hover {
            background: var(--white);
            color: var(--primary);
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: bold;
        }

        .stat-label {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .section {
            padding: 4rem 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: clamp(1.75rem, 4vw, 2.5rem);
            margin-bottom: 1rem;
            color: var(--primary);
            font-weight: 700;
        }

        .section-subtitle {
            text-align: center;
            color: var(--gray);
            font-size: clamp(1rem, 2vw, 1.1rem);
            margin-bottom: 3rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .feature-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border: 2px solid var(--light);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
            border-color: var(--accent);
        }

        .feature-icon {
            width: 55px;
            height: 55px;
            background: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .feature-card h3 {
            color: var(--dark);
            margin-bottom: 0.75rem;
            font-size: 1.2rem;
        }

        .feature-card p {
            color: var(--gray);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .apps-section {
            background: var(--light);
        }

        .apps-container {
            position: relative;
        }

        .apps-categories {
            max-height: 1200px;
            overflow: hidden;
            transition: max-height 0.5s ease;
        }

        .apps-categories.expanded {
            max-height: none;
        }

        .app-category {
            margin-bottom: 2.5rem;
        }

        .category-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .category-title {
            font-size: clamp(1.2rem, 3vw, 1.5rem);
            color: var(--primary);
            font-weight: 600;
        }

        .category-count {
            background: var(--accent);
            color: var(--white);
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .apps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
        }

        .app-card {
            background: var(--white);
            padding: 1rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 1px 5px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border: 1px solid transparent;
        }

        .app-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
            transform: translateX(5px);
            border-color: var(--accent);
        }

        .app-icon {
            width: 45px;
            height: 45px;
            background: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: bold;
            flex-shrink: 0;
            font-size: 1.2rem;
        }

        .app-icon i {
            font-size: 1.2rem;
        }

        .app-name {
            font-weight: 500;
            color: var(--dark);
            font-size: 0.95rem;
        }

        .show-more-btn {
            display: block;
            margin: 2rem auto 0;
            padding: 0.75rem 2rem;
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .show-more-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .pricing-card {
            background: var(--white);
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            border: 2px solid var(--light);
            position: relative;
        }

        .pricing-card.featured {
            border-color: var(--primary);
            transform: scale(1.02);
        }

        .pricing-card.featured::before {
            content: 'MOST POPULAR';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary);
            color: var(--white);
            padding: 0.4rem 1.5rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .pricing-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .pricing-card.featured:hover {
            transform: translateY(-8px) scale(1.02);
        }

        .plan-name {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .price {
            font-size: 3rem;
            font-weight: bold;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .price span {
            font-size: 1.1rem;
            color: var(--gray);
            font-weight: normal;
        }

        .features-list {
            list-style: none;
            margin: 2rem 0;
        }

        .features-list li {
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--light);
            color: var(--dark);
        }

        .features-list li:before {
            content: '✓';
            color: var(--green);
            font-weight: bold;
            margin-right: 0.75rem;
            font-size: 1.2rem;
        }

        .select-plan-btn {
            width: 100%;
            padding: 1rem;
            background: var(--green);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .select-plan-btn:hover {
            background: var(--green-dark);
            transform: translateY(-2px);
        }

        .pricing-card.featured .select-plan-btn {
            background: var(--primary);
        }

        .pricing-card.featured .select-plan-btn:hover {
            background: var(--primary-dark);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            backdrop-filter: blur(5px);
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            padding: 2.5rem;
            border-radius: 12px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 2rem;
            cursor: pointer;
            color: var(--gray);
            background: none;
            border: none;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
            line-height: 1;
        }

        .close-modal:hover {
            background: var(--light);
            color: var(--dark);
        }

        .modal-content h2 {
            margin-bottom: 2rem;
            color: var(--primary);
            font-size: 1.75rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid var(--light);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .info-box {
            background: linear-gradient(135deg, var(--light) 0%, #e0e7ff 100%);
            padding: 1.5rem;
            border-radius: 10px;
            margin: 1.5rem 0;
            border-left: 4px solid var(--primary);
        }

        .info-box h4 {
            margin-bottom: 0.75rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-box h4::before {
            content: '🎉';
            font-size: 1.5rem;
        }

        .info-box p {
            color: var(--dark);
            font-size: 0.9rem;
            line-height: 1.8;
            margin: 0;
        }

        .info-box .highlight {
            background: var(--primary);
            color: var(--white);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 600;
        }

        .trust-badges {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .trust-badge {
            background: var(--light);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            color: var(--dark);
            font-weight: 500;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--green) 0%, var(--green-dark) 100%);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(22, 163, 74, 0.3);
        }

        .urgency-banner {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px dashed var(--warning-color);
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .urgency-banner strong {
            color: var(--dark);
            font-size: 0.95rem;
        }

        footer {
            background: var(--dark);
            color: var(--white);
            padding: 3rem 1.5rem;
            text-align: center;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .footer-links a {
            color: var(--white);
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .footer-links a:hover {
            opacity: 0.7;
        }

        @media (max-width: 768px) {
            .nav-links {
                position: fixed;
                left: -100%;
                top: 68px;
                flex-direction: column;
                background: var(--primary);
                width: 100%;
                padding: 2rem;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                transition: 0.3s;
                align-items: flex-start;
            }

            .nav-links.active {
                left: 0;
            }

            .hamburger {
                display: flex;
            }

            .hero {
                padding: 3rem 1.5rem;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn-primary, .btn-secondary {
                width: 100%;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .apps-grid {
                grid-template-columns: 1fr;
            }

            .pricing-card.featured {
                transform: scale(1);
            }

            .pricing-card.featured:hover {
                transform: translateY(-8px);
            }

            .modal-content {
                padding: 2rem 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .nav-container {
                padding: 1rem;
            }

            .section {
                padding: 3rem 1rem;
            }

            .feature-card {
                padding: 1.5rem;
            }

            .pricing-card {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-container">
            <a href="#" class="logo">
                <?php if (!empty($companyLogo)): ?>
                    <img src="<?php echo htmlspecialchars($companyLogo); ?>" alt="<?php echo htmlspecialchars($companyName); ?>">
                <?php else: ?>
                    <div class="logo-icon"><?php echo htmlspecialchars($companyInitial); ?></div>
                <?php endif; ?>
                <!-- <span><?php echo htmlspecialchars($companyName); ?></span> -->
            </a>
            <ul class="nav-links" id="navLinks">
                <li><a href="#home">Home</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#applications">Applications</a></li>
                <li><a href="#pricing">Pricing</a></li>
                <li><a href="login.php" >Get Started</a></li>
            </ul>
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <section class="hero" id="home">
        <div class="hero-content">
            <span class="featured-badge">Featured Item</span>
            <h1>Shared VPS Management System</h1>
            <p>A robust and comprehensive solution designed to streamline server administration, enhance application deployment, and improve communication between developers and clients.</p>
            <div class="hero-buttons">
                <a href="register.php" class="btn-primary">Register Your Server</a>
            </div>
            <div class="hero-stats">
                <div class="stat">
                    <div class="stat-number">70+</div>
                    <div class="stat-label">Applications</div>
                </div>
                <div class="stat">
                    <div class="stat-number">10min</div>
                    <div class="stat-label">Quick Setup</div>
                </div>
                <div class="stat">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Support</div>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="features">
        <h2 class="section-title">Why Choose Our VPS?</h2>
        <p class="section-subtitle">Everything you need for powerful cloud hosting</p>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3>Lightning Fast Setup</h3>
                <p>Get your VPS running in just 10 minutes with SSH access and instant configuration</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔐</div>
                <h3>Full SSH Access</h3>
                <p>Complete control over your server with secure SSH terminal access and root privileges</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🛠️</div>
                <h3>70+ Applications</h3>
                <p>Pre-configured apps ready to deploy with small configuration across multiple categories</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔄</div>
                <h3>Auto Backups</h3>
                <p>Daily automated backups to keep your data safe with one-click restore functionality</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🚀</div>
                <h3>High Performance</h3>
                <p>SSD storage and optimized configurations for maximum speed and reliability</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3>Enhanced Security</h3>
                <p>Built-in firewall, DDoS protection, and regular security updates included</p>
            </div>
        </div>
    </section>

    <section class="section apps-section" id="applications">
        <h2 class="section-title">70+ Pre-Configured Applications</h2>
        <p class="section-subtitle">Deploy any application instantly with small configuration</p>

        <div class="apps-container">
            <div class="apps-categories" id="appsCategories">
                <div class="app-category">
                    <div class="category-header">
                        <h3 class="category-title">Data Science</h3>
                        <span class="category-count">1 application</span>
                    </div>
                    <div class="apps-grid">
                        <div class="app-card"><div class="app-icon"><i class="bi bi-python"></i></div><div class="app-name">Anaconda</div></div>
                    </div>
                </div>

                <div class="app-category">
                    <div class="category-header">
                        <h3 class="category-title">Databases</h3>
                        <span class="category-count">2 applications</span>
                    </div>
                    <div class="apps-grid">
                        <div class="app-card"><div class="app-icon"><i class="bi bi-server"></i></div><div class="app-name">ClusterControl</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-database-fill"></i></div><div class="app-name">PocketBase</div></div>
                    </div>
                </div>

                <div class="app-category">
                    <div class="category-header">
                        <h3 class="category-title">Developer Tools</h3>
                        <span class="category-count">21 applications</span>
                    </div>
                    <div class="apps-grid">
                        <div class="app-card"><div class="app-icon"><i class="bi bi-ship"></i></div><div class="app-name">Docker</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-diagram-3"></i></div><div class="app-name">n8n</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-airplane"></i></div><div class="app-name">Airflow</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-box-seam"></i></div><div class="app-name">Appwrite</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-robot"></i></div><div class="app-name">Claude Code</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-cloud-fill"></i></div><div class="app-name">Dokku</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-diagram-2"></i></div><div class="app-name">Flowise</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-git"></i></div><div class="app-name">GitLab</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-chat-dots"></i></div><div class="app-name">Mattermost</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-cpu"></i></div><div class="app-name">MCP Server</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-diagram-3-fill"></i></div><div class="app-name">n8n (+100 workflows)</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-clock-history"></i></div><div class="app-name">n8n (queue mode)</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-flow-chart"></i></div><div class="app-name">Node-RED</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-cpu-fill"></i></div><div class="app-name">Ollama</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-container-stack"></i></div><div class="app-name">Portainer</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-graph-up"></i></div><div class="app-name">Posthog</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-display"></i></div><div class="app-name">Remote Desktop</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-laptop"></i></div><div class="app-name">Sim Studio</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-database"></i></div><div class="app-name">Supabase</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-code-square"></i></div><div class="app-name">VS Code</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-wind"></i></div><div class="app-name">Windmill</div></div>
                    </div>
                </div>

                <div class="app-category">
                    <div class="category-header">
                        <h3 class="category-title">eCommerce</h3>
                        <span class="category-count">4 applications</span>
                    </div>
                    <div class="apps-grid">
                        <div class="app-card"><div class="app-icon"><i class="bi bi-cart4"></i></div><div class="app-name">WooCommerce</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-shop"></i></div><div class="app-name">Magento 2</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-bag"></i></div><div class="app-name">OpenCart</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-bag-check"></i></div><div class="app-name">PrestaShop</div></div>
                    </div>
                </div>

                <div class="app-category">
                    <div class="category-header">
                        <h3 class="category-title">Frameworks</h3>
                        <span class="category-count">10 applications</span>
                    </div>
                    <div class="apps-grid">
                        <div class="app-card"><div class="app-icon"><i class="bi bi-boxes"></i></div><div class="app-name">OpenLiteSpeed + Node.js</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-window"></i></div><div class="app-name">ASP.NET</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-stack"></i></div><div class="app-name">LAMP Stack</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-lightning"></i></div><div class="app-name">Laravel</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-stack-overflow"></i></div><div class="app-name">LEMP Stack</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-app"></i></div><div class="app-name">MEAN Stack</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-app-indicator"></i></div><div class="app-name">MERN Stack</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-apple"></i></div><div class="app-name">MEVN Stack</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-layers"></i></div><div class="app-name">OpenLiteSpeed + Django</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-diamond"></i></div><div class="app-name">OpenLiteSpeed + Rails</div></div>
                    </div>
                </div>

                <div class="app-category">
                    <div class="category-header">
                        <h3 class="category-title">eLearning</h3>
                        <span class="category-count">2 applications</span>
                    </div>
                    <div class="apps-grid">
                        <div class="app-card"><div class="app-icon"><i class="bi bi-book"></i></div><div class="app-name">MediaWiki</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-mortarboard"></i></div><div class="app-name">Moodle</div></div>
                    </div>
                </div>

                <div class="app-category">
                    <div class="category-header">
                        <h3 class="category-title">Media</h3>
                        <span class="category-count">6 applications</span>
                    </div>
                    <div class="apps-grid">
                        <div class="app-card"><div class="app-icon"><i class="bi bi-mic"></i></div><div class="app-name">AzuraCast</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-camera"></i></div><div class="app-name">Immich</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-play-circle"></i></div><div class="app-name">Jellyfin</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-camera-video"></i></div><div class="app-name">Jitsi</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-broadcast"></i></div><div class="app-name">Owncast</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-tv"></i></div><div class="app-name">Plex Media Server</div></div>
                    </div>
                </div>

                <div class="app-category">
                    <div class="category-header">
                        <h3 class="category-title">Monitoring</h3>
                        <span class="category-count">3 applications</span>
                    </div>
                    <div class="apps-grid">
                        <div class="app-card"><div class="app-icon"><i class="bi bi-speedometer2"></i></div><div class="app-name">Grafana</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-activity"></i></div><div class="app-name">Uptime Kuma</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-bar-chart"></i></div><div class="app-name">Zabbix</div></div>
                    </div>
                </div>

                <div class="app-category">
                    <div class="category-header">
                        <h3 class="category-title">Security</h3>
                        <span class="category-count">2 applications</span>
                    </div>
                    <div class="apps-grid">
                        <div class="app-card"><div class="app-icon"><i class="bi bi-shield-lock"></i></div><div class="app-name">OpenVPN</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-shield-check"></i></div><div class="app-name">WireGuard</div></div>
                    </div>
                </div>

                <div class="app-category">
                    <div class="category-header">
                        <h3 class="category-title">Storage</h3>
                        <span class="category-count">1 application</span>
                    </div>
                    <div class="apps-grid">
                        <div class="app-card"><div class="app-icon"><i class="bi bi-search"></i></div><div class="app-name">ElasticSearch</div></div>
                    </div>
                </div>

                <div class="app-category">
                    <div class="category-header">
                        <h3 class="category-title">Blogs and Forums</h3>
                        <span class="category-count">9 applications</span>
                    </div>
                    <div class="apps-grid">
                        <div class="app-card"><div class="app-icon"><i class="bi bi-wordpress"></i></div><div class="app-name">WordPress</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-twitter"></i></div><div class="app-name">Bluesky</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-chat-quote"></i></div><div class="app-name">Discourse</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-file-text"></i></div><div class="app-name">Ghost</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-layers-fill"></i></div><div class="app-name">OpenLiteSpeed + Drupal</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-stack"></i></div><div class="app-name">OpenLiteSpeed + Joomla</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-wordpress"></i></div><div class="app-name">OpenLiteSpeed + WordPress</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-bezier"></i></div><div class="app-name">Strapi</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-fonts"></i></div><div class="app-name">TYPO3</div></div>
                    </div>
                </div>

                <div class="app-category">
                    <div class="category-header">
                        <h3 class="category-title">Business Apps</h3>
                        <span class="category-count">10 applications</span>
                    </div>
                    <div class="apps-grid">
                        <div class="app-card"><div class="app-icon"><i class="bi bi-cash-coin"></i></div><div class="app-name">Akaunting</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-telephone"></i></div><div class="app-name">FreePBX</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-house-door"></i></div><div class="app-name">Home Assistant</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-envelope"></i></div><div class="app-name">IceWarp</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-graph-up-arrow"></i></div><div class="app-name">MetaTrader 4</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-graph-up-arrow"></i></div><div class="app-name">MetaTrader 5</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-cloud"></i></div><div class="app-name">Nextcloud</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-briefcase"></i></div><div class="app-name">Odoo</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-terminal"></i></div><div class="app-name">Peppermint.sh</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-kanban"></i></div><div class="app-name">Redmine</div></div>
                    </div>
                </div>

                <div class="app-category">
                    <div class="category-header">
                        <h3 class="category-title">Chat</h3>
                        <span class="category-count">2 applications</span>
                    </div>
                    <div class="apps-grid">
                        <div class="app-card"><div class="app-icon"><i class="bi bi-chat-heart"></i></div><div class="app-name">Chatwoot</div></div>
                        <div class="app-card"><div class="app-icon"><i class="bi bi-chat-left-text"></i></div><div class="app-name">Rocket.Chat</div></div>
                    </div>
                </div>
            </div>
            <button class="show-more-btn" id="showMoreAppsBtn" onclick="toggleApps()">Show More Applications</button>
        </div>
    </section>

    <section class="section" id="pricing">
        <h2 class="section-title">Choose Your Plan</h2>
        <p class="section-subtitle">All plans include SSH access and 70+ applications</p>
        <div class="pricing-grid">
            <?php if (!empty($packages)): ?>
                <?php foreach ($packages as $index => $package): ?>
                    <div class="pricing-card <?php echo $package['is_popular'] ? 'featured' : ''; ?>">
                        <div class="plan-name"><?php echo htmlspecialchars($package['name']); ?></div>
                        <div class="price">₹<?php echo number_format($package['price_monthly'], 0); ?><span>/month</span></div>
                        <ul class="features-list">
                            <li><?php echo $package['storage_gb']; ?> GB SSD Storage</li>
                            <li><?php echo $package['bandwidth_gb'] > 999 ? 'Unlimited' : $package['bandwidth_gb'] . ' GB'; ?> Bandwidth</li>
                            <li><?php echo $package['allowed_websites'] > 999 ? 'Unlimited' : $package['allowed_websites']; ?> Websites</li>
                            <li><?php echo $package['database_limit'] > 999 ? 'Unlimited' : $package['database_limit']; ?> Databases</li>
                            <?php if ($package['email_accounts'] > 0): ?>
                                <li><?php echo $package['email_accounts'] > 999 ? 'Unlimited' : $package['email_accounts']; ?> Email Accounts</li>
                            <?php endif; ?>
                            <?php if ($package['ftp_accounts'] > 0): ?>
                                <li><?php echo $package['ftp_accounts'] > 999 ? 'Unlimited' : $package['ftp_accounts']; ?> FTP Accounts</li>
                            <?php endif; ?>
                            <?php if ($package['ssh_access']): ?>
                                <li>Full SSH Access</li>
                            <?php endif; ?>
                            <?php if ($package['ssl_free']): ?>
                                <li>Free SSL Certificate</li>
                            <?php endif; ?>
                            <?php if ($package['daily_backups']): ?>
                                <li>Daily Backups</li>
                            <?php endif; ?>
                            <li>70+ Applications</li>
                            <li>24/7 Priority Support</li>
                        </ul>
                        <a href="select-package.php?package=<?php echo $package['slug']; ?>&cycle=monthly" class="select-plan-btn">
                            Select Plan
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--gray);">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📦</div>
                    <h3>No Packages Available</h3>
                    <p>Please check back later for our hosting plans.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer>
        <div class="footer-content">
            <h3 style="margin-bottom: 1rem;">🚀 <?php echo htmlspecialchars($companyName); ?></h3>
            <p style="color: var(--gray); margin-bottom: 2rem;">Professional VPS hosting with instant deployment and 70+ applications</p>
            <div class="footer-links">
                <a href="#features">Features</a>
                <a href="#applications">Applications</a>
                <a href="#pricing">Pricing</a>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
                <a href="https://evotec.in/contact">Support</a>
            </div>
            <div style="color: var(--gray); font-size: 0.9rem; margin-bottom: 1rem;">
                <p style="margin-bottom: 0.5rem;">📧 <?php echo htmlspecialchars($companyEmail); ?></p>
                <p style="margin-bottom: 0.5rem;">📞 <?php echo htmlspecialchars($companyPhone); ?></p>
            </div>
            <p style="color: var(--gray); font-size: 0.9rem;">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($companyName); ?>. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const hamburger = document.getElementById('hamburger');
        const navLinks = document.getElementById('navLinks');

        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navLinks.classList.toggle('active');
        });

        document.addEventListener('click', (e) => {
            if (!hamburger.contains(e.target) && !navLinks.contains(e.target)) {
                hamburger.classList.remove('active');
                navLinks.classList.remove('active');
            }
        });

        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navLinks.classList.remove('active');
            });
        });

        function toggleApps() {
            const appsCategories = document.getElementById('appsCategories');
            const btn = document.getElementById('showMoreAppsBtn');
            
            if (appsCategories.classList.contains('expanded')) {
                appsCategories.classList.remove('expanded');
                btn.textContent = 'Show More Applications';
                document.getElementById('applications').scrollIntoView({ behavior: 'smooth' });
            } else {
                appsCategories.classList.add('expanded');
                btn.textContent = 'Show Less Applications';
            }
        }

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && !this.onclick) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });


        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.feature-card, .pricing-card, .app-card').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'all 0.6s ease';
            observer.observe(el);
        });
    </script>
</body>
</html>
