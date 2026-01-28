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
$companyInitial = !empty($companyName) ? strtoupper(substr($companyName, 0, 1)) : 'C';

$pageTitle = $companyName . " - Next-Generation Cloud Infrastructure";
?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title><?php echo htmlspecialchars($pageTitle); ?></title>
   <meta name="keywords" content="cloud hosting, VPS hosting, cloud infrastructure, scalable hosting">
   <meta name="description" content="Next-Generation Cloud Infrastructure for Scale. Deploy high-performance instances globally in seconds.">
   <meta name="author" content="">

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
</head>

<body>
   <!-- Header Navigation -->
   <header class="header">
      <div class="container">
         <nav class="navbar">
            <div class="navbar-brand">
               <?php if (!empty($companyLogo)): ?>
                  <img src="<?php echo htmlspecialchars($companyLogo); ?>" alt="<?php echo htmlspecialchars($companyName); ?>" class="logo-img">
               <?php else: ?>
                  <div class="logo-box">
                     <i class="fas fa-cloud"></i>
                     <span class="logo-text"><?php echo htmlspecialchars($companyName); ?></span>
                  </div>
               <?php endif; ?>
            </div>

            <button class="mobile-toggle" id="mobileToggle">
               <span></span>
               <span></span>
               <span></span>
            </button>

            <div class="navbar-menu" id="navbarMenu">
               <a href="#features" class="nav-link">Features</a>
               <a href="#pricing" class="nav-link">Pricing</a>
               <a href="#network" class="nav-link">Network</a>
               <a href="features.php" class="nav-link">Compute Options</a>
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

   <!-- Hero Section -->
   <section class="hero-section">
      <div class="container">
         <div class="hero-content">
            <div class="hero-text">
               <div class="hero-badge">
                  <i class="fas fa-rocket"></i>
                  FUTURE OF COMPUTE
               </div>
               <h1 class="hero-title">
                  Next-Generation<br>
                  <span class="text-gradient">Cloud Infrastructure</span><br>
                  for Scale.
               </h1>
               <p class="hero-description">
                  Deploy high-performance instances globally in seconds.<br>
                  Engineered for speed, security, and 100% uptime.
               </p>
               <div class="hero-buttons">
                  <a href="#pricing" class="btn-large btn-primary">Start Deploying</a>
                  <a href="#" class="btn-large btn-outline">
                     <i class="fas fa-play-circle"></i> Watch Demo
                  </a>
               </div>
            </div>
            <div class="hero-image">
               <div class="cloud-visualization">
                  <img src="netic/images/cloud-network.svg" alt="Cloud Infrastructure" class="cloud-img">
               </div>
            </div>
         </div>

         <!-- Stats Section -->
         <div class="stats-grid">
            <div class="stat-card">
               <div class="stat-label">RELIABILITY</div>
               <div class="stat-value">99.99%</div>
               <div class="stat-description">Global Uptime Guarantee</div>
            </div>
            <div class="stat-card">
               <div class="stat-label">NETWORK</div>
               <div class="stat-value">40+</div>
               <div class="stat-description">Regional Edge Centers</div>
            </div>
            <div class="stat-card">
               <div class="stat-label">ADOPTION</div>
               <div class="stat-value">100k+</div>
               <div class="stat-description">Scale-ready Instances</div>
            </div>
         </div>
      </div>
   </section>

   <!-- Features Section -->
   <section class="features-section" id="features">
      <div class="container">
         <div class="section-header">
            <div class="section-badge">COMPUTE OPTIONS</div>
            <h2 class="section-title">Enterprise-Grade Performance</h2>
         </div>

         <div class="compute-grid">
            <!-- VPS Hosting Card -->
            <div class="compute-card featured">
               <div class="compute-icon">
                  <i class="fas fa-server"></i>
               </div>
               <h3 class="compute-title">VPS Hosting</h3>
               <p class="compute-description">
                  Full root access with scalable resources. Built on ultra-fast NVMe storage with dedicated cores.
               </p>
               <ul class="compute-features">
                  <li><i class="fas fa-check"></i> CloudPanel Pre-installed</li>
                  <li><i class="fas fa-check"></i> Full Root SSH Access</li>
                  <li><i class="fas fa-check"></i> NVMe SSD Storage</li>
                  <li><i class="fas fa-check"></i> Automated Backups</li>
                  <li><i class="fas fa-check"></i> DDoS Protection</li>
               </ul>
               <a href="#pricing" class="btn-compute">View Pricing <i class="fas fa-arrow-right"></i></a>
            </div>
         </div>
      </div>
   </section>

   <!-- Pricing Section -->
   <section class="pricing-section" id="pricing">
      <div class="container">
         <div class="section-header">
            <h2 class="section-title">VPS Hosting Plans</h2>
            <p class="section-subtitle">Choose the perfect plan for your needs. All plans include 24/7 support and 99.99% uptime guarantee.</p>
         </div>

         <div class="pricing-grid">
            <?php
            if (!empty($packages) && count($packages) > 0):
               foreach ($packages as $index => $package):
                  // Determine available price cycles
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

                  // Skip if no pricing
                  if (empty($availablePrices)) continue;

                  // Get default price
                  $defaultCycle = 'monthly';
                  if (isset($availablePrices['monthly'])) {
                     $defaultCycle = 'monthly';
                     $displayPrice = $availablePrices['monthly']['perMonth'];
                  } elseif (isset($availablePrices['yearly'])) {
                     $defaultCycle = 'yearly';
                     $displayPrice = $availablePrices['yearly']['perMonth'];
                  } elseif (isset($availablePrices['2years'])) {
                     $defaultCycle = '2years';
                     $displayPrice = $availablePrices['2years']['perMonth'];
                  } else {
                     $defaultCycle = '4years';
                     $displayPrice = $availablePrices['4years']['perMonth'];
                  }

                  // Parse features
                  $features = [];
                  if (!empty($package['features'])) {
                     $features = array_filter(array_map('trim', explode("\n", $package['features'])));
                  }

                  $isPopular = ($index == 1);
            ?>
                  <div class="pricing-card <?php echo $isPopular ? 'popular' : ''; ?>">
                     <?php if ($isPopular): ?>
                        <div class="popular-badge">MOST POPULAR</div>
                     <?php endif; ?>

                     <div class="pricing-header">
                        <h3 class="pricing-name"><?php echo htmlspecialchars($package['name']); ?></h3>
                        <div class="pricing-price">
                           <span class="currency">₹</span>
                           <span class="amount"><?php echo number_format($displayPrice, 0); ?></span>
                           <span class="period">/month</span>
                        </div>
                        <div class="pricing-cycle">Billed <?php echo $defaultCycle == 'monthly' ? 'Monthly' : ucfirst($defaultCycle); ?></div>
                     </div>

                     <ul class="pricing-features">
                        <?php if (!empty($features)): ?>
                           <?php foreach ($features as $feature): ?>
                              <li><i class="fas fa-check"></i> <?php echo htmlspecialchars($feature); ?></li>
                           <?php endforeach; ?>
                        <?php else: ?>
                           <li><i class="fas fa-check"></i> <?php echo isset($package['cpu']) ? htmlspecialchars($package['cpu']) : '2'; ?> vCPU Cores</li>
                           <li><i class="fas fa-check"></i> <?php echo isset($package['ram']) ? htmlspecialchars($package['ram']) : '2GB'; ?> RAM</li>
                           <li><i class="fas fa-check"></i> <?php echo isset($package['storage']) ? htmlspecialchars($package['storage']) : '50GB'; ?> NVMe SSD</li>
                           <li><i class="fas fa-check"></i> <?php echo isset($package['bandwidth']) ? htmlspecialchars($package['bandwidth']) : '2TB'; ?> Bandwidth</li>
                           <li><i class="fas fa-check"></i> CloudPanel Included</li>
                           <li><i class="fas fa-check"></i> Full Root Access</li>
                           <li><i class="fas fa-check"></i> Daily Backups</li>
                           <li><i class="fas fa-check"></i> Free SSL</li>
                        <?php endif; ?>
                     </ul>

                     <a href="select-package.php?package=<?php echo urlencode($package['slug']); ?>&cycle=<?php echo $defaultCycle; ?>"
                        class="btn-pricing <?php echo $isPopular ? 'btn-primary' : 'btn-outline'; ?>">
                        Deploy Now
                     </a>
                  </div>
               <?php
               endforeach;
            else:
               ?>
               <div class="pricing-card">
                  <div class="pricing-header">
                     <h3 class="pricing-name">No Packages Available</h3>
                     <p style="color: var(--text-secondary); margin-top: 20px;">Please check back later or contact support.</p>
                  </div>
               </div>
            <?php endif; ?>
         </div>
      </div>
   </section>

   <!-- CTA Section -->
   <section class="cta-section" id="network">
      <div class="container">
         <div class="cta-content">
            <h2 class="cta-title">Ready to scale your next big idea?</h2>
            <p class="cta-description">Join 10,000+ developers building on the most advanced cloud network.</p>
            <a href="register.php" class="btn-large btn-white">Deploy Now</a>
         </div>
      </div>
   </section>

   <!-- Footer -->
   <footer class="footer">
      <div class="container">
         <div class="footer-content">
            <div class="footer-left">
               <div class="footer-brand">
                  <i class="fas fa-cloud"></i>
                  <span><?php echo htmlspecialchars($companyName); ?></span>
               </div>
               <p class="footer-copyright">© <?php echo date('Y'); ?> <?php echo htmlspecialchars($companyName); ?>. All rights reserved.</p>
            </div>
            <div class="footer-links">
               <a href="https://evotec.in/terms-conditions">Terms</a>
               <a href="https://evotec.in/privacy-policy">Privacy</a>
               <a href="https://evotec.in/refund-policy">Status</a>
            </div>
         </div>
      </div>
   </footer>

   <!-- Mobile Menu Script -->
   <script>
      document.getElementById('mobileToggle').addEventListener('click', function() {
         document.getElementById('navbarMenu').classList.toggle('active');
         this.classList.toggle('active');
      });
   </script>
</body>

</html>