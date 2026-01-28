<?php
session_start();
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

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
$isAdmin = $isLoggedIn && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// Get first letter of company name for logo fallback
$companyInitial = !empty($companyName) ? strtoupper(substr($companyName, 0, 1)) : 'H';

$pageTitle = $companyName . " - Web Hosting & Domain Services";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- basic -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- mobile metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <!-- site metas -->
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="keywords" content="web hosting, domain, VPS hosting, cloud hosting">
    <meta name="description" content="Professional web hosting and domain services with instant deployment and 24/7 support.">
    <meta name="author" content="">

    <!-- Favicon -->
    <?php if (!empty($companyLogo)): ?>
        <link rel="icon" href="<?php echo htmlspecialchars($companyLogo); ?>" type="image/x-icon">
    <?php else: ?>
        <link rel="icon" href="netic/images/fevicon.png" type="image/gif" />
    <?php endif; ?>

    <!-- bootstrap css -->
    <link rel="stylesheet" type="text/css" href="netic/css/bootstrap.min.css">
    <!-- style css -->
    <link rel="stylesheet" type="text/css" href="netic/css/style.css">
    <!-- Responsive-->
    <link rel="stylesheet" href="netic/css/responsive.css">
    <!-- fonts -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,700|Sen:400,700,800&display=swap" rel="stylesheet">
    <!-- Scrollbar Custom CSS -->
    <link rel="stylesheet" href="netic/css/jquery.mCustomScrollbar.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">

    <!-- Custom Styles -->
    <style>
        /* Modern Package Card Styling */
        .modern-pricing-box {
            background: #fff;
            border-radius: 15px;
            padding: 40px 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
            border: 2px solid #f0f0f0;
            position: relative;
            overflow: hidden;
        }

        .modern-pricing-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(139, 39, 145, 0.2);
            border-color: #8b2791;
        }

        .modern-pricing-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #8b2791, #5a1a8f);
        }

        .package-badge {
            position: absolute;
            top: 20px;
            right: -35px;
            background: linear-gradient(135deg, #8b2791, #5a1a8f);
            color: #fff;
            padding: 5px 40px;
            transform: rotate(45deg);
            font-size: 12px;
            font-weight: 600;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        }

        .package-name {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .package-price {
            font-size: 48px;
            font-weight: 800;
            color: #8b2791;
            margin: 20px 0;
            line-height: 1;
        }

        .package-price small {
            font-size: 18px;
            font-weight: 400;
            color: #666;
        }

        .package-cycle {
            font-size: 14px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 25px;
            display: block;
        }

        .package-features {
            list-style: none;
            padding: 0;
            margin: 30px 0;
            text-align: left;
        }

        .package-features li {
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
            color: #555;
            font-size: 15px;
            display: flex;
            align-items: center;
        }

        .package-features li:last-child {
            border-bottom: none;
        }

        .package-features li i {
            color: #8b2791;
            margin-right: 12px;
            font-size: 18px;
            min-width: 20px;
        }

        .package-features li strong {
            color: #333;
            font-weight: 600;
        }

        .modern-select-btn {
            background: linear-gradient(135deg, #8b2791, #5a1a8f);
            color: #fff;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            display: inline-block;
            box-shadow: 0 5px 15px rgba(139, 39, 145, 0.3);
            border: none;
        }

        .modern-select-btn:hover {
            background: linear-gradient(135deg, #5a1a8f, #8b2791);
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(139, 39, 145, 0.4);
            color: #fff;
        }

        /* Cloud Hosting Overview Modern Style */
        .hosting-overview-modern {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .hosting-features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 30px;
        }

        .hosting-feature-item {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            width: 100%;
            box-sizing: border-box;
        }

        .hosting-feature-item:hover {
            transform: translateX(10px);
            box-shadow: 0 8px 25px rgba(139, 39, 145, 0.15);
        }

        .hosting-feature-item i {
            font-size: 32px;
            color: #8b2791;
            margin-right: 15px;
            min-width: 40px;
            flex-shrink: 0;
        }

        .hosting-feature-item strong {
            color: #333;
            font-size: 16px;
            font-weight: 600;
            line-height: 1.4;
            flex: 1;
        }

        /* Footer Modern Color */
        .footer_section {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important;
        }

        .footer_text {
            color: #fff !important;
        }

        .location_text a,
        .footer_menu a {
            color: #ddd !important;
        }

        .location_text a:hover,
        .footer_menu a:hover {
            color: #8b2791 !important;
        }

        .copyright_section {
            background: #0f0f1e !important;
        }

        .copyright_text {
            color: #999 !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hosting-features-grid {
                grid-template-columns: 1fr !important;
                gap: 8px !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .hosting-feature-item {
                padding: 12px 10px !important;
                font-size: 13px !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                margin: 0 !important;
            }

            .hosting-feature-item i {
                font-size: 20px !important;
                margin-right: 8px !important;
                min-width: 30px !important;
            }

            .hosting-feature-item strong {
                font-size: 13px !important;
                word-break: break-word !important;
                line-height: 1.3 !important;
            }

            .hosting-overview-modern {
                padding: 20px 15px !important;
                border-radius: 10px !important;
                overflow: hidden !important;
            }

            .hosting_taital {
                font-size: 24px !important;
                padding-bottom: 10px !important;
            }

            .hosting_text {
                font-size: 14px !important;
                line-height: 1.6 !important;
            }

            .package-price {
                font-size: 36px;
            }

            .modern-pricing-box {
                padding: 30px 20px;
            }

            .modern-select-btn {
                padding: 12px 25px !important;
                font-size: 13px !important;
                white-space: nowrap !important;
            }

            .container {
                padding-left: 15px !important;
                padding-right: 15px !important;
            }
        }
    </style>
</head>

<body>
    <div class="header_section">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href="index.php">
                    <?php if (!empty($companyLogo)): ?>
                        <img src="<?php echo htmlspecialchars($companyLogo); ?>" alt="<?php echo htmlspecialchars($companyName); ?>" class="company-logo" style="height: 40px;">
                    <?php else: ?>
                        <div class="logo-box" style="width: 40px; height: 40px; background-color: #8b2791; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 5px; font-weight: bold; font-size: 20px;">
                            <span class="logo-text"><?php echo $companyInitial; ?></span>
                        </div>
                    <?php endif; ?>
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item active">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#about">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#hosting">Hosting</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#pricing">Packages</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#services">Services</a>
                        </li>
                        <?php if ($isLoggedIn): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo $isAdmin ? 'admin/index.php' : 'user/index.php'; ?>">
                                    <i class="fa fa-user"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="logout.php">
                                    <i class="fa fa-sign-out"></i> Logout
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="login.php">
                                    <i class="fa fa-sign-in"></i> Login
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="register.php">
                                    <i class="fa fa-user-plus"></i> Register
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <form class="form-inline my-2 my-lg-0">
                    </form>
                </div>
            </nav>
            <div class="custom_bg">
                <div class="custom_menu">
                    <ul>
                        <li class="active"><a href="index.php">Home</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#hosting">Hosting</a></li>
                        <li><a href="#pricing">Packages</a></li>
                        <li><a href="#services">Services</a></li>
                        <?php if ($isLoggedIn): ?>
                            <li><a href="<?php echo $isAdmin ? 'admin/index.php' : 'user/index.php'; ?>">Dashboard</a></li>
                            <li><a href="logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Login</a></li>
                            <li><a href="register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <form class="form-inline my-2 my-lg-0">
                    <div class="search_btn">
                        <li><a href="#"><i class="fa fa-search" aria-hidden="true"></i></a></li>
                        <?php if ($isLoggedIn): ?>
                            <li><a href="<?php echo $isAdmin ? 'admin/orders.php' : 'user/orders.php'; ?>"><i class="fa fa-shopping-cart" aria-hidden="true"></i></a></li>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        <!-- banner section start -->
        <div class="banner_section layout_padding">
            <div id="my_slider" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-6">
                                    <h1 class="banner_taital">Professional <br>Hosting & Domain</h1>
                                    <p style="color: #fff; margin-top: 20px;">Deploy your applications in minutes with our powerful hosting solutions. Get started with professional hosting packages today!</p>
                                    <div class="read_bt"><a href="#pricing">View Packages</a></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="banner_img"><img src="netic/images/banner-img.png"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-6">
                                    <h1 class="banner_taital">Cloud VPS <br>Hosting Solutions</h1>
                                    <p style="color: #fff; margin-top: 20px;">Powerful VPS hosting with instant deployment. Scale your applications with ease and reliability.</p>
                                    <div class="read_bt"><a href="#pricing">Get Started</a></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="banner_img"><img src="netic/images/banner-img.png"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-6">
                                    <h1 class="banner_taital">24/7 Support <br>& Security</h1>
                                    <p style="color: #fff; margin-top: 20px;">Enterprise-grade security with round-the-clock support. Your applications are in safe hands.</p>
                                    <div class="read_bt"><a href="<?php echo $isLoggedIn ? ($isAdmin ? 'admin/index.php' : 'user/index.php') : 'register.php'; ?>">Start Now</a></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="banner_img"><img src="netic/images/banner-img.png"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <a class="carousel-control-prev" href="#my_slider" role="button" data-slide="prev">
                    <i class="fa fa-angle-left"></i>
                </a>
                <a class="carousel-control-next" href="#my_slider" role="button" data-slide="next">
                    <i class="fa fa-angle-right"></i>
                </a>
            </div>
        </div>
        <!-- banner section end -->
    </div>
    <!-- header section end -->



    <!-- about section start -->
    <div class="about_section layout_padding" id="about">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="about_box">
                        <div class="icon_1"><img src="netic/images/icon-1.png"></div>
                        <h3 class="faster_text">10-Minute Deployment</h3>
                        <p class="lorem_text">Get your server ready in just 10 minutes with pre-configured Ubuntu server, full root SSH access, and automatic setup. Start deploying your applications immediately.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="about_box">
                        <div class="icon_1"><img src="netic/images/icon-2.png"></div>
                        <h3 class="faster_text">AWS S3 Compatible Storage</h3>
                        <p class="lorem_text">Seamlessly integrate with AWS S3 compatible object storage. Scale your storage needs effortlessly with industry-standard API compatibility.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="about_box">
                        <div class="icon_1"><img src="netic/images/icon-3.png"></div>
                        <h3 class="faster_text">Advanced Security</h3>
                        <p class="lorem_text">Military-grade encryption, DDoS protection, automated security patches, firewall management, and SSL certificates included. Your data is completely secure.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- about section end -->

    <!-- hosting section start -->
    <div class="hosting_section layout_padding" id="hosting">
        <div class="container">
            <div class="hosting-overview-modern">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="hosting_taital" style="color: #333;">CLOUD/VPS HOSTING OVERVIEW</h1>
                        <p class="hosting_text" style="color: #555; font-size: 16px; line-height: 1.8;">
                            Enterprise-grade Cloud/VPS hosting powered by cutting-edge infrastructure. Deploy Ubuntu servers with CloudPanel pre-installed, get full root SSH access in under 10 minutes, and scale effortlessly with AWS S3-compatible object storage.
                        </p>
                        <p class="hosting_text" style="color: #555; font-weight: 600; margin-top: 25px; font-size: 18px;">
                            <i class="fa fa-star" style="color: #8b2791;"></i> Enterprise Features Included:
                        </p>
                        <div class="hosting-features-grid">
                            <div class="hosting-feature-item">
                                <i class="fa fa-terminal"></i>
                                <strong>Full Root SSH Access</strong>
                            </div>
                            <div class="hosting-feature-item">
                                <i class="fa fa-dashboard"></i>
                                <strong>CloudPanel Control Panel</strong>
                            </div>
                            <div class="hosting-feature-item">
                                <i class="fa fa-database"></i>
                                <strong>Automated Daily Backups</strong>
                            </div>
                            <div class="hosting-feature-item">
                                <i class="fa fa-shield"></i>
                                <strong>DDoS Protection & Firewall</strong>
                            </div>
                            <div class="hosting-feature-item">
                                <i class="fa fa-lock"></i>
                                <strong>Free SSL Certificates</strong>
                            </div>
                            <div class="hosting-feature-item">
                                <i class="fa fa-linux"></i>
                                <strong>Ubuntu LTS Auto Updates</strong>
                            </div>
                            <div class="hosting-feature-item">
                                <i class="fa fa-cubes"></i>
                                <strong>75+ Integrated Modules</strong>
                            </div>
                            <div class="hosting-feature-item">
                                <i class="fa fa-code-fork"></i>
                                <strong>n8n, Supabase & Odoo</strong>
                            </div>
                        </div>
                        <div class="click_bt" style="margin-top: 30px;">
                            <a href="#pricing" class="modern-select-btn">View Packages</a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="hosting_img">
                            <img src="netic/images/hosting-img.png" style="max-width: 100%; border-radius: 15px; box-shadow: 0 15px 50px rgba(0,0,0,0.1);">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- hosting section end -->

    <!-- pricing section start -->
    <div class="pricing_section layout_padding" id="pricing">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h1 class="pricing_taital">Our Hosting Packages</h1>
                    <p class="pricing_text" style="font-size: 16px; max-width: 700px; margin: 0 auto 50px;">Choose from our flexible hosting plans designed to meet your needs. All plans include 24/7 support, 99.9% uptime guarantee, and enterprise-grade security.</p>
                </div>
            </div>
            <div class="pricing_section_2">
                <div class="row justify-content-center">
                    <?php
                    if (!empty($packages) && count($packages) > 0):
                        $count = 0;
                        foreach ($packages as $package):
                            $count++;
                            $isFeatured = ($count == 2 || strtolower($package['name']) == 'professional' || strtolower($package['name']) == 'business');
                    ?>
                            <?php
                            // Determine available price cycles (hide if 0 or null)
                            $availablePrices = [];
                            if (!empty($package['price_monthly']) && $package['price_monthly'] > 0) {
                                $availablePrices['monthly'] = [
                                    'price' => $package['price_monthly'],
                                    'label' => 'Monthly',
                                    'perMonth' => $package['price_monthly'],
                                    'totalPrice' => $package['price_monthly']
                                ];
                            }
                            if (!empty($package['price_yearly']) && $package['price_yearly'] > 0) {
                                $availablePrices['yearly'] = [
                                    'price' => $package['price_yearly'],
                                    'label' => 'Yearly',
                                    'perMonth' => $package['price_yearly'] / 12,
                                    'totalPrice' => $package['price_yearly']
                                ];
                            }
                            if (!empty($package['price_2years']) && $package['price_2years'] > 0) {
                                $availablePrices['2years'] = [
                                    'price' => $package['price_2years'],
                                    'label' => '2 Years',
                                    'perMonth' => $package['price_2years'] / 24,
                                    'totalPrice' => $package['price_2years']
                                ];
                            }
                            if (!empty($package['price_4years']) && $package['price_4years'] > 0) {
                                $availablePrices['4years'] = [
                                    'price' => $package['price_4years'],
                                    'label' => '4 Years',
                                    'perMonth' => $package['price_4years'] / 48,
                                    'totalPrice' => $package['price_4years']
                                ];
                            }

                            // Skip package if no pricing available
                            if (empty($availablePrices)) {
                                continue;
                            }

                            // Priority: Monthly > Yearly > 2Years > 4Years
                            $defaultCycle = 'monthly';
                            $displayPrice = 0;
                            $displayLabel = 'Monthly';

                            if (isset($availablePrices['monthly'])) {
                                $defaultCycle = 'monthly';
                                $displayPrice = $availablePrices['monthly']['perMonth'];
                                $displayLabel = $availablePrices['monthly']['label'];
                            } elseif (isset($availablePrices['yearly'])) {
                                $defaultCycle = 'yearly';
                                $displayPrice = $availablePrices['yearly']['perMonth'];
                                $displayLabel = $availablePrices['yearly']['label'] . ' (₹' . number_format($availablePrices['yearly']['totalPrice'], 0) . '/year)';
                            } elseif (isset($availablePrices['2years'])) {
                                $defaultCycle = '2years';
                                $displayPrice = $availablePrices['2years']['perMonth'];
                                $displayLabel = $availablePrices['2years']['label'] . ' (₹' . number_format($availablePrices['2years']['totalPrice'], 0) . ' total)';
                            } elseif (isset($availablePrices['4years'])) {
                                $defaultCycle = '4years';
                                $displayPrice = $availablePrices['4years']['perMonth'];
                                $displayLabel = $availablePrices['4years']['label'] . ' (₹' . number_format($availablePrices['4years']['totalPrice'], 0) . ' total)';
                            }

                            // Parse features from database
                            $features = [];
                            if (!empty($package['features'])) {
                                $features = array_filter(array_map('trim', explode("\n", $package['features'])));
                            }
                            ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="modern-pricing-box text-center">
                                    <?php if ($isFeatured): ?>
                                        <span class="package-badge">POPULAR</span>
                                    <?php endif; ?>

                                    <h5 class="package-name"><?php echo htmlspecialchars($package['name']); ?></h5>

                                    <div class="package-price">
                                        ₹<?php echo number_format($displayPrice, 0); ?>
                                        <small>/mo</small>
                                    </div>

                                    <span class="package-cycle"><?php echo htmlspecialchars($displayLabel); ?></span>

                                    <ul class="package-features">
                                        <?php if (!empty($features)): ?>
                                            <?php foreach ($features as $feature): ?>
                                                <li>
                                                    <i class="fa fa-check-circle"></i>
                                                    <?php echo htmlspecialchars($feature); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li>
                                                <i class="fa fa-microchip"></i>
                                                <strong><?php echo isset($package['cpu']) ? htmlspecialchars($package['cpu']) : '1'; ?></strong> vCPU Cores
                                            </li>
                                            <li>
                                                <i class="fa fa-memory"></i>
                                                <strong><?php echo isset($package['ram']) ? htmlspecialchars($package['ram']) : '1GB'; ?></strong> RAM
                                            </li>
                                            <li>
                                                <i class="fa fa-hdd-o"></i>
                                                <strong><?php echo isset($package['storage']) ? htmlspecialchars($package['storage']) : '25GB'; ?></strong> SSD Storage
                                            </li>
                                            <li>
                                                <i class="fa fa-exchange"></i>
                                                <strong><?php echo isset($package['bandwidth']) ? htmlspecialchars($package['bandwidth']) : '1TB'; ?></strong> Bandwidth
                                            </li>
                                            <li>
                                                <i class="fa fa-check-circle"></i>
                                                CloudPanel Pre-installed
                                            </li>
                                            <li>
                                                <i class="fa fa-check-circle"></i>
                                                Full Root SSH Access
                                            </li>
                                            <li>
                                                <i class="fa fa-check-circle"></i>
                                                Daily Automated Backups
                                            </li>
                                            <li>
                                                <i class="fa fa-check-circle"></i>
                                                Free SSL Certificate
                                            </li>
                                        <?php endif; ?>
                                    </ul>

                                    <a href="select-package.php?package=<?php echo urlencode($package['slug']); ?>&cycle=<?php echo $defaultCycle; ?>" class="modern-select-btn">
                                        Select Plan
                                    </a>
                                </div>
                            </div>
                        <?php
                        endforeach;
                    else:
                        ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="modern-pricing-box text-center">
                                <h5 class="package-name">Starter Plan</h5>
                                <div class="package-price">₹499<small>/mo</small></div>
                                <span class="package-cycle">Monthly Billing</span>
                                <ul class="package-features">
                                    <li><i class="fa fa-microchip"></i> <strong>1</strong> vCPU Core</li>
                                    <li><i class="fa fa-memory"></i> <strong>1GB</strong> RAM</li>
                                    <li><i class="fa fa-hdd-o"></i> <strong>25GB</strong> SSD Storage</li>
                                    <li><i class="fa fa-exchange"></i> <strong>1TB</strong> Bandwidth</li>
                                    <li><i class="fa fa-check-circle"></i> CloudPanel Pre-installed</li>
                                    <li><i class="fa fa-check-circle"></i> Full Root SSH Access</li>
                                    <li><i class="fa fa-check-circle"></i> Daily Automated Backups</li>
                                    <li><i class="fa fa-check-circle"></i> Free SSL Certificate</li>
                                </ul>
                                <a href="register.php" class="modern-select-btn">Select Plan</a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="modern-pricing-box text-center">
                                <span class="package-badge">POPULAR</span>
                                <h5 class="package-name">Business Plan</h5>
                                <div class="package-price">₹999<small>/mo</small></div>
                                <span class="package-cycle">Monthly Billing</span>
                                <ul class="package-features">
                                    <li><i class="fa fa-microchip"></i> <strong>2</strong> vCPU Cores</li>
                                    <li><i class="fa fa-memory"></i> <strong>2GB</strong> RAM</li>
                                    <li><i class="fa fa-hdd-o"></i> <strong>50GB</strong> SSD Storage</li>
                                    <li><i class="fa fa-exchange"></i> <strong>2TB</strong> Bandwidth</li>
                                    <li><i class="fa fa-check-circle"></i> CloudPanel Pre-installed</li>
                                    <li><i class="fa fa-check-circle"></i> Full Root SSH Access</li>
                                    <li><i class="fa fa-check-circle"></i> Daily Automated Backups</li>
                                    <li><i class="fa fa-check-circle"></i> Free SSL Certificate</li>
                                </ul>
                                <a href="register.php" class="modern-select-btn">Select Plan</a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="modern-pricing-box text-center">
                                <h5 class="package-name">Enterprise Plan</h5>
                                <div class="package-price">₹1999<small>/mo</small></div>
                                <span class="package-cycle">Monthly Billing</span>
                                <ul class="package-features">
                                    <li><i class="fa fa-microchip"></i> <strong>4</strong> vCPU Cores</li>
                                    <li><i class="fa fa-memory"></i> <strong>4GB</strong> RAM</li>
                                    <li><i class="fa fa-hdd-o"></i> <strong>100GB</strong> SSD Storage</li>
                                    <li><i class="fa fa-exchange"></i> <strong>4TB</strong> Bandwidth</li>
                                    <li><i class="fa fa-check-circle"></i> CloudPanel Pre-installed</li>
                                    <li><i class="fa fa-check-circle"></i> Full Root SSH Access</li>
                                    <li><i class="fa fa-check-circle"></i> Daily Automated Backups</li>
                                    <li><i class="fa fa-check-circle"></i> Free SSL Certificate</li>
                                </ul>
                                <a href="register.php" class="modern-select-btn">Select Plan</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- pricing section end -->

    <!-- services section start -->
    <div class="services_section layout_padding" id="services">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 class="services_taital">Enterprise Features Included</h1>
                    <p class="services_text">Professional cloud hosting with enterprise-grade features built-in. Everything you need to run your applications securely and efficiently.</p>
                </div>
            </div>
            <div class="services_section_2">
                <div class="row">
                    <div class="col-md-4">
                        <div class="service_box">
                            <div class="services_icon">
                                <img src="netic/images/icon-4.png" class="image_1">
                                <img src="netic/images/icon-7.png" class="image_2">
                            </div>
                            <h3 class="wordpress_text">CloudPanel Control Panel</h3>
                            <p class="opposed_text">Modern, intuitive control panel pre-installed. Manage websites, databases, domains, and SSL certificates with ease. One-click installations for popular applications.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="service_box">
                            <div class="services_icon">
                                <img src="netic/images/icon-5.png" class="image_1">
                                <img src="netic/images/icon-5.png" class="image_2">
                            </div>
                            <h3 class="wordpress_text">Automated Backup System</h3>
                            <p class="opposed_text">Daily automated backups with 7-day retention. One-click restore functionality. Your data is safe with off-site backup storage and instant recovery options.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="service_box">
                            <div class="services_icon">
                                <img src="netic/images/icon-6.png" class="image_1">
                                <img src="netic/images/icon-9.png" class="image_2">
                            </div>
                            <h3 class="wordpress_text">Full SSH Root Access</h3>
                            <p class="opposed_text">Complete control with root SSH access. Install custom software, configure server settings, and manage your Ubuntu server environment as needed.</p>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="service_box">
                            <div class="services_icon">
                                <img src="netic/images/icon-4.png" class="image_1">
                                <img src="netic/images/icon-7.png" class="image_2">
                            </div>
                            <h3 class="wordpress_text">AWS S3 Compatible Storage</h3>
                            <p class="opposed_text">Scalable object storage with AWS S3 API compatibility. Perfect for backups, media files, and static assets. Pay only for what you use.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="service_box">
                            <div class="services_icon">
                                <img src="netic/images/icon-5.png" class="image_1">
                                <img src="netic/images/icon-5.png" class="image_2">
                            </div>
                            <h3 class="wordpress_text">Advanced Security</h3>
                            <p class="opposed_text">DDoS protection, firewall management, automated security updates, malware scanning, and free SSL certificates. Military-grade encryption for all data.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="service_box">
                            <div class="services_icon">
                                <img src="netic/images/icon-6.png" class="image_1">
                                <img src="netic/images/icon-9.png" class="image_2">
                            </div>
                            <h3 class="wordpress_text">Ubuntu LTS Server</h3>
                            <p class="opposed_text">Latest Ubuntu Long Term Support version with automatic security patches. Optimized for performance with pre-configured LAMP/LEMP stack options.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- services section end -->

    <!-- footer section start -->
    <div class="footer_section layout_padding">
        <div class="container">
            <div class="row">
                <div class="col-sm-4">
                    <h3 class="footer_text">Quick Links</h3>
                    <div class="footer_menu">
                        <ul>
                            <li class="active"><a href="index.php"><span class="angle_icon active"><i class="fa fa-arrow-right" aria-hidden="true"></i></span> Home</a></li>
                            <li><a href="#about"><span class="angle_icon"><i class="fa fa-arrow-right" aria-hidden="true"></i></span> About</a></li>
                            <li><a href="#services"><span class="angle_icon"><i class="fa fa-arrow-right" aria-hidden="true"></i></span> Services</a></li>
                            <li><a href="#pricing"><span class="angle_icon"><i class="fa fa-arrow-right" aria-hidden="true"></i></span> Packages</a></li>
                            <li><a href="#hosting"><span class="angle_icon"><i class="fa fa-arrow-right" aria-hidden="true"></i></span> Hosting</a></li>
                            <?php if (!$isLoggedIn): ?>
                                <li><a href="login.php"><span class="angle_icon"><i class="fa fa-arrow-right" aria-hidden="true"></i></span> Login</a></li>
                                <li><a href="register.php"><span class="angle_icon"><i class="fa fa-arrow-right" aria-hidden="true"></i></span> Register</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-sm-4">
                    <h3 class="footer_text">Contact Info</h3>
                    <div class="location_text">
                        <ul>
                            <li>
                                <a href="#">
                                    <span class="padding_left_10"><i class="fa fa-map-marker" aria-hidden="true"></i></span>
                                    <?php echo htmlspecialchars($companyName); ?><br>Professional Hosting Services
                                </a>
                            </li>
                            <li>
                                <a href="tel:<?php echo htmlspecialchars($companyPhone); ?>">
                                    <span class="padding_left_10"><i class="fa fa-phone" aria-hidden="true"></i></span>
                                    <?php echo htmlspecialchars($companyPhone); ?>
                                </a>
                            </li>
                            <li>
                                <a href="mailto:<?php echo htmlspecialchars($companyEmail); ?>">
                                    <span class="padding_left_10"><i class="fa fa-envelope" aria-hidden="true"></i></span>
                                    <?php echo htmlspecialchars($companyEmail); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- footer section end -->

    <!-- copyright section start -->
    <div class="copyright_section">
        <div class="container">
            <p class="copyright_text">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($companyName); ?>. All Rights Reserved.</p>
        </div>
    </div>
    <!-- copyright section end -->

    <!-- Javascript files-->
    <script src="netic/js/jquery.min.js"></script>
    <script src="netic/js/popper.min.js"></script>
    <script src="netic/js/bootstrap.bundle.min.js"></script>
    <script src="netic/js/jquery-3.0.0.min.js"></script>
    <script src="netic/js/plugin.js"></script>
    <!-- sidebar -->
    <script src="netic/js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="netic/js/custom.js"></script>
</body>

</html>