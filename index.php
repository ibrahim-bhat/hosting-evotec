<?php
session_start();
require_once 'config.php';
require_once 'components/hosting_helper.php';
require_once 'components/settings_helper.php';

// Get active packages
$packages = getActivePackages($conn);

$pageTitle = "InfraLabs Cloud - Professional Hosting Solutions | VPS, Shared, WordPress & More";
$pageDescription = "Professional hosting solutions with 99.99% uptime, free SSL, daily backups, and 7-day money-back guarantee. Support for 75+ applications including WordPress, Node.js, React, Odoo, n8n and more.";
include 'components/header.php';
?>

   <!-- Hero Section -->
   <section class="hero-section">
      <div class="container">
         <div class="hero-content">
            <div class="hero-text">
               <div class="hero-badges">
                  <div class="hero-badge">
                     <i class="fas fa-shield-alt"></i>
                     7-DAY MONEY BACK GUARANTEE
                  </div>
                  <div class="hero-badge secondary">
                     <i class="fas fa-star"></i>
                     TRUSTED BY 100,000+ USERS
                  </div>
               </div>
               <h1 class="hero-title">
                  Make Your Website<br>
                  <span class="text-gradient">in Minutes</span>
               </h1>
               <p class="hero-description">
                  Just describe your idea – AI Website Builder will create your layout, images, and text. 
                  Then use the drag-and-drop editor to customize. Whether it's an online store or a blog, 
                  you'll be live in moments.
               </p>
               <div class="hero-buttons">
                  <a href="register.php" class="btn-large btn-primary">
                     <i class="fas fa-rocket"></i> Start Building Now
                  </a>
                  <a href="#pricing" class="btn-large btn-outline">
                     View Pricing
                  </a>
               </div>
               
               <!-- Trust Indicators -->
               <div class="trust-indicators">
                  <div class="trust-item">
                     <i class="fas fa-lock"></i>
                     <span>Free SSL Certificate</span>
                  </div>
                  <div class="trust-item">
                     <i class="fas fa-cloud-upload-alt"></i>
                     <span>Daily Backups</span>
                  </div>
                  <div class="trust-item">
                     <i class="fas fa-headset"></i>
                     <span>24/7 Support</span>
                  </div>
               </div>
            </div>
            <div class="hero-image">
               <div class="cloud-visualization">
                  <div class="hero-visual-card">
                     <div class="code-preview">
                        <div class="code-header">
                           <span class="dot red"></span>
                           <span class="dot yellow"></span>
                           <span class="dot green"></span>
                        </div>
                        <div class="code-content">
                           <p><span class="code-comment">// Deploy instantly</span></p>
                           <p><span class="code-keyword">const</span> server = <span class="code-string">'ultra-fast'</span>;</p>
                           <p><span class="code-keyword">const</span> uptime = <span class="code-number">99.99</span>%;</p>
                           <p><span class="code-function">deploy</span>(server);</p>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <!-- Stats Section -->
         <div class="stats-grid">
            <div class="stat-card">
               <div class="stat-icon"><i class="fas fa-server"></i></div>
               <div class="stat-value">99.99%</div>
               <div class="stat-label">Uptime Guarantee</div>
            </div>
            <div class="stat-card">
               <div class="stat-icon"><i class="fas fa-globe"></i></div>
               <div class="stat-value">40+</div>
               <div class="stat-label">Data Centers</div>
            </div>
            <div class="stat-card">
               <div class="stat-icon"><i class="fas fa-users"></i></div>
               <div class="stat-value">100k+</div>
               <div class="stat-label">Happy Customers</div>
            </div>
            <div class="stat-card">
               <div class="stat-icon"><i class="fas fa-bolt"></i></div>
               <div class="stat-value">24/7</div>
               <div class="stat-label">Expert Support</div>
            </div>
         </div>
      </div>
   </section>

   <!-- AI Website Builder Section -->
   <section class="ai-builder-section" id="features">
      <div class="container">
         <div class="split-content">
            <div class="split-text">
               <div class="section-badge">AI-POWERED</div>
               <h2 class="section-title">Create with Website Builder</h2>
               <p class="section-description">
                  Just describe your idea – AI Website Builder will create your layout, images, and text. 
                  Then use the drag-and-drop editor to customize.
               </p>
               <ul class="feature-list">
                  <li><i class="fas fa-check-circle"></i> AI-generated layouts and content</li>
                  <li><i class="fas fa-check-circle"></i> Drag-and-drop customization</li>
                  <li><i class="fas fa-check-circle"></i> Responsive design templates</li>
                  <li><i class="fas fa-check-circle"></i> SEO-optimized structure</li>
                  <li><i class="fas fa-check-circle"></i> One-click publishing</li>
               </ul>
               <a href="register.php" class="btn-large btn-primary">Start Building Free</a>
            </div>
            <div class="split-visual">
               <div class="browser-mockup">
                  <div class="browser-header">
                     <span class="dot"></span>
                     <span class="dot"></span>
                     <span class="dot"></span>
                  </div>
                  <div class="browser-content">
                     <div class="ai-demo">
                        <i class="fas fa-magic"></i>
                        <p>AI Building Your Site...</p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </section>

   <!-- WordPress Section -->
   <section class="wordpress-section">
      <div class="container">
         <div class="split-content reverse">
            <div class="split-visual">
               <div class="wordpress-visual">
                  <i class="fab fa-wordpress wordpress-icon"></i>
                  <div class="wordpress-features">
                     <div class="wp-feature">
                        <i class="fas fa-rocket"></i>
                        <span>Quick Start</span>
                     </div>
                     <div class="wp-feature">
                        <i class="fas fa-shield-alt"></i>
                        <span>Secure</span>
                     </div>
                     <div class="wp-feature">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Fast</span>
                     </div>
                  </div>
               </div>
            </div>
            <div class="split-text">
               <div class="section-badge">WORDPRESS</div>
               <h2 class="section-title">WordPress without the complexity</h2>
               <p class="section-description">
                  Create a WordPress site with AI, use a pre-built template, or start from scratch. 
                  While we take care of site maintenance, speed, and security for you.
               </p>
               <ul class="feature-list">
                  <li><i class="fas fa-check-circle"></i> AI-powered WordPress setup</li>
                  <li><i class="fas fa-check-circle"></i> Pre-built professional templates</li>
                  <li><i class="fas fa-check-circle"></i> Automatic updates & security</li>
                  <li><i class="fas fa-check-circle"></i> Optimized for speed</li>
                  <li><i class="fas fa-check-circle"></i> Free SSL & CDN included</li>
               </ul>
               <a href="register.php" class="btn-large btn-primary">Build a WordPress Site</a>
            </div>
         </div>
      </div>
   </section>

   <!-- Automatic Backups Section -->
   <section class="backups-section">
      <div class="container">
         <div class="backups-content">
            <div class="section-badge">SECURITY & RELIABILITY</div>
            <h2 class="section-title">Automatic Backups Included</h2>
            <p class="section-subtitle">Never lose your data. We've got you covered with automated backup solutions.</p>
            
            <div class="backups-grid">
               <div class="backup-card">
                  <div class="backup-icon">
                     <i class="fas fa-calendar-day"></i>
                  </div>
                  <h3>Daily Backups</h3>
                  <p>Automatic daily backups of your entire website, databases, and files.</p>
               </div>
               <div class="backup-card">
                  <div class="backup-icon">
                     <i class="fas fa-mouse-pointer"></i>
                  </div>
                  <h3>One-Click Restore</h3>
                  <p>Restore your website to any previous state with a single click.</p>
               </div>
               <div class="backup-card">
                  <div class="backup-icon">
                     <i class="fas fa-cloud"></i>
                  </div>
                  <h3>Cloud Storage</h3>
                  <p>Backups stored securely in multiple cloud locations for redundancy.</p>
               </div>
               <div class="backup-card">
                  <div class="backup-icon">
                     <i class="fas fa-gift"></i>
                  </div>
                  <h3>Free Forever</h3>
                  <p>No additional charges. Automatic backups included with all plans.</p>
               </div>
            </div>
         </div>
      </div>
   </section>

   <!-- Hosting Types Section -->
   <section class="hosting-types-section">
      <div class="container">
         <div class="section-header">
            <div class="section-badge">HOSTING SOLUTIONS</div>
            <h2 class="section-title">Choose Your Perfect Hosting</h2>
            <p class="section-subtitle">From shared hosting to VPS, we have the perfect solution for every need.</p>
         </div>

         <div class="hosting-grid">
            <!-- Shared Hosting -->
            <div class="hosting-card featured">
               <div class="hosting-icon">
                  <i class="fas fa-share-alt"></i>
               </div>
               <h3>Shared Hosting</h3>
               <p>Perfect for beginners and small websites. Affordable and easy to use.</p>
               <ul class="hosting-features">
                  <li><i class="fas fa-check"></i> Free domain for 1 year</li>
                  <li><i class="fas fa-check"></i> Free SSL certificate</li>
                  <li><i class="fas fa-check"></i> 99.9% uptime guarantee</li>
                  <li><i class="fas fa-check"></i> cPanel control panel</li>
                  <li><i class="fas fa-check"></i> 1-click WordPress install</li>
                  <li><i class="fas fa-check"></i> Daily backups</li>
               </ul>
               <a href="#pricing" class="btn-hosting">View Plans</a>
            </div>

            <!-- VPS Hosting -->
            <div class="hosting-card">
               <div class="hosting-icon">
                  <i class="fas fa-server"></i>
               </div>
               <h3>VPS Hosting</h3>
               <p>Dedicated resources with full root access. Scalable and powerful.</p>
               <ul class="hosting-features">
                  <li><i class="fas fa-check"></i> Full root SSH access</li>
                  <li><i class="fas fa-check"></i> NVMe SSD storage</li>
                  <li><i class="fas fa-check"></i> Dedicated IP address</li>
                  <li><i class="fas fa-check"></i> CloudPanel pre-installed</li>
                  <li><i class="fas fa-check"></i> DDoS protection</li>
                  <li><i class="fas fa-check"></i> Scalable resources</li>
               </ul>
               <a href="#pricing" class="btn-hosting">View Plans</a>
            </div>

            <!-- Node.js Hosting -->
            <div class="hosting-card">
               <div class="hosting-icon">
                  <i class="fab fa-node-js"></i>
               </div>
               <h3>Node.js Hosting</h3>
               <p>Optimized for Node.js applications with latest runtime support.</p>
               <ul class="hosting-features">
                  <li><i class="fas fa-check"></i> Latest Node.js versions</li>
                  <li><i class="fas fa-check"></i> NPM & Yarn support</li>
                  <li><i class="fas fa-check"></i> PM2 process manager</li>
                  <li><i class="fas fa-check"></i> MongoDB support</li>
                  <li><i class="fas fa-check"></i> WebSocket support</li>
                  <li><i class="fas fa-check"></i> Easy deployment</li>
               </ul>
               <a href="#pricing" class="btn-hosting">View Plans</a>
            </div>

            <!-- React Hosting -->
            <div class="hosting-card">
               <div class="hosting-icon">
                  <i class="fab fa-react"></i>
               </div>
               <h3>React Hosting</h3>
               <p>Perfect for React applications with CDN and build optimization.</p>
               <ul class="hosting-features">
                  <li><i class="fas fa-check"></i> Optimized for React apps</li>
                  <li><i class="fas fa-check"></i> Global CDN included</li>
                  <li><i class="fas fa-check"></i> Auto SSL & HTTPS</li>
                  <li><i class="fas fa-check"></i> CI/CD pipelines</li>
                  <li><i class="fas fa-check"></i> Environment variables</li>
                  <li><i class="fas fa-check"></i> Instant deployments</li>
               </ul>
               <a href="#pricing" class="btn-hosting">View Plans</a>
            </div>

            <!-- PHP Hosting -->
            <div class="hosting-card">
               <div class="hosting-icon">
                  <i class="fab fa-php"></i>
               </div>
               <h3>PHP Hosting</h3>
               <p>Blazing fast PHP hosting with latest versions and optimizations.</p>
               <ul class="hosting-features">
                  <li><i class="fas fa-check"></i> PHP 7.4 - 8.3 support</li>
                  <li><i class="fas fa-check"></i> OPcache enabled</li>
                  <li><i class="fas fa-check"></i> Composer support</li>
                  <li><i class="fas fa-check"></i> MySQL & PostgreSQL</li>
                  <li><i class="fas fa-check"></i> Laravel optimized</li>
                  <li><i class="fas fa-check"></i> Git integration</li>
               </ul>
               <a href="#pricing" class="btn-hosting">View Plans</a>
            </div>

            <!-- Business Tools -->
            <div class="hosting-card">
               <div class="hosting-icon">
                  <i class="fas fa-briefcase"></i>
               </div>
               <h3>Business Tools</h3>
               <p>CRM, automation, and business applications ready to deploy.</p>
               <ul class="hosting-features">
                  <li><i class="fas fa-check"></i> Odoo ERP/CRM</li>
                  <li><i class="fas fa-check"></i> n8n automation</li>
                  <li><i class="fas fa-check"></i> 75+ modules included</li>
                  <li><i class="fas fa-check"></i> Custom integrations</li>
                  <li><i class="fas fa-check"></i> API access</li>
                  <li><i class="fas fa-check"></i> Training & support</li>
               </ul>
               <a href="#pricing" class="btn-hosting">View Plans</a>
            </div>
         </div>
      </div>
   </section>

   <!-- Reviews Section -->
   <section class="reviews-section" id="reviews">
      <div class="container">
         <div class="section-header">
            <div class="section-badge">TESTIMONIALS</div>
            <h2 class="section-title">Trusted by thousands worldwide</h2>
            <div class="rating-summary">
               <div class="stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
               </div>
               <p>4.9 out of 5 based on 15,000+ reviews</p>
            </div>
         </div>

         <div class="reviews-grid">
            <div class="review-card">
               <div class="review-stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
               </div>
               <p class="review-text">
                  "Incredible hosting service! The uptime is fantastic and support team is always ready to help. 
                  Migrated 5 websites and all are running faster than ever."
               </p>
               <div class="review-author">
                  <div class="author-avatar">
                     <i class="fas fa-user"></i>
                  </div>
                  <div class="author-info">
                     <strong>Sarah Johnson</strong>
                     <span>Web Developer</span>
                  </div>
               </div>
            </div>

            <div class="review-card">
               <div class="review-stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
               </div>
               <p class="review-text">
                  "Best VPS hosting I've used. The performance is outstanding and the price is very competitive. 
                  CloudPanel makes server management a breeze."
               </p>
               <div class="review-author">
                  <div class="author-avatar">
                     <i class="fas fa-user"></i>
                  </div>
                  <div class="author-info">
                     <strong>Michael Chen</strong>
                     <span>Startup Founder</span>
                  </div>
               </div>
            </div>

            <div class="review-card">
               <div class="review-stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
               </div>
               <p class="review-text">
                  "The 7-day money back guarantee gave me confidence to try. Now I'm a customer for 2 years! 
                  Excellent service, great features, unbeatable support."
               </p>
               <div class="review-author">
                  <div class="author-avatar">
                     <i class="fas fa-user"></i>
                  </div>
                  <div class="author-info">
                     <strong>Emily Rodriguez</strong>
                     <span>E-commerce Owner</span>
                  </div>
               </div>
            </div>

            <div class="review-card">
               <div class="review-stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
               </div>
               <p class="review-text">
                  "WordPress hosting is incredibly fast. My site load time improved by 60%. 
                  The automatic backups have saved me multiple times. Highly recommended!"
               </p>
               <div class="review-author">
                  <div class="author-avatar">
                     <i class="fas fa-user"></i>
                  </div>
                  <div class="author-info">
                     <strong>David Kumar</strong>
                     <span>Blogger</span>
                  </div>
               </div>
            </div>

            <div class="review-card">
               <div class="review-stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
               </div>
               <p class="review-text">
                  "Switched from another host and wish I'd done it sooner. Better performance, 
                  better support, better price. The whole package is just better."
               </p>
               <div class="review-author">
                  <div class="author-avatar">
                     <i class="fas fa-user"></i>
                  </div>
                  <div class="author-info">
                     <strong>Lisa Anderson</strong>
                     <span>Digital Marketer</span>
                  </div>
               </div>
            </div>

            <div class="review-card">
               <div class="review-stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
               </div>
               <p class="review-text">
                  "The n8n automation hosting is perfect for my workflows. Easy setup, 
                  reliable performance, and great documentation. Support team knows their stuff!"
               </p>
               <div class="review-author">
                  <div class="author-avatar">
                     <i class="fas fa-user"></i>
                  </div>
                  <div class="author-info">
                     <strong>James Wilson</strong>
                     <span>Automation Engineer</span>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </section>

   <!-- Operating Systems Section -->
   <section class="os-section" id="software">
      <div class="container">
         <div class="section-header">
            <div class="section-badge">OPERATING SYSTEMS</div>
            <h2 class="section-title">Support for All Major Operating Systems</h2>
            <p class="section-subtitle">Choose your preferred OS with one-click installation</p>
         </div>

         <div class="os-grid">
            <div class="os-card">
               <i class="fab fa-linux"></i>
               <span>AlmaLinux</span>
            </div>
            <div class="os-card">
               <i class="fab fa-debian"></i>
               <span>Debian</span>
            </div>
            <div class="os-card">
               <i class="fab fa-linux"></i>
               <span>Rocky Linux</span>
            </div>
            <div class="os-card">
               <i class="fab fa-ubuntu"></i>
               <span>Ubuntu</span>
            </div>
            <div class="os-card">
               <i class="fab fa-linux"></i>
               <span>Alpine Linux</span>
            </div>
            <div class="os-card">
               <i class="fab fa-linux"></i>
               <span>Arch Linux</span>
            </div>
            <div class="os-card">
               <i class="fab fa-centos"></i>
               <span>CentOS</span>
            </div>
            <div class="os-card">
               <i class="fab fa-linux"></i>
               <span>CloudLinux</span>
            </div>
            <div class="os-card">
               <i class="fab fa-fedora"></i>
               <span>Fedora Cloud</span>
            </div>
            <div class="os-card">
               <i class="fab fa-linux"></i>
               <span>Kali Linux</span>
            </div>
            <div class="os-card">
               <i class="fab fa-suse"></i>
               <span>openSUSE</span>
            </div>
         </div>
      </div>
   </section>

   <!-- Software & Applications Section -->
   <section class="software-section">
      <div class="container">
         <div class="section-header">
            <div class="section-badge">75+ APPLICATIONS</div>
            <h2 class="section-title">One-Click Install for Popular Software</h2>
            <p class="section-subtitle">Deploy your favorite applications instantly with our one-click installer</p>
         </div>

         <div class="software-tabs">
            <div class="tab-buttons">
               <button class="tab-btn active" data-tab="control-panels">Control Panels</button>
               <button class="tab-btn" data-tab="cms">CMS & E-commerce</button>
               <button class="tab-btn" data-tab="frameworks">Frameworks</button>
               <button class="tab-btn" data-tab="devops">DevOps</button>
               <button class="tab-btn" data-tab="business">Business Apps</button>
            </div>

            <div class="tab-content">
               <div class="tab-pane active" id="control-panels">
                  <div class="software-grid">
                     <div class="software-item"><i class="fas fa-cog"></i> Dokploy</div>
                     <div class="software-item"><i class="fas fa-cog"></i> Coolify</div>
                     <div class="software-item"><i class="fas fa-cog"></i> CloudPanel</div>
                     <div class="software-item"><i class="fas fa-cog"></i> cPanel</div>
                     <div class="software-item"><i class="fas fa-cog"></i> Plesk</div>
                     <div class="software-item"><i class="fas fa-cog"></i> HestiaCP</div>
                     <div class="software-item"><i class="fas fa-cog"></i> FASTPANEL</div>
                     <div class="software-item"><i class="fas fa-cog"></i> Easypanel</div>
                     <div class="software-item"><i class="fas fa-cog"></i> Cloudron</div>
                     <div class="software-item"><i class="fas fa-cog"></i> TinyCP</div>
                     <div class="software-item"><i class="fas fa-cog"></i> Webuzo</div>
                     <div class="software-item"><i class="fas fa-cog"></i> Webmin</div>
                     <div class="software-item"><i class="fas fa-cog"></i> Kusanagi</div>
                     <div class="software-item"><i class="fas fa-cog"></i> DirectAdmin</div>
                     <div class="software-item"><i class="fas fa-cog"></i> CyberPanel</div>
                  </div>
               </div>

               <div class="tab-pane" id="cms">
                  <div class="software-grid">
                     <div class="software-item"><i class="fab fa-wordpress"></i> WordPress</div>
                     <div class="software-item"><i class="fas fa-shopping-cart"></i> WooCommerce</div>
                     <div class="software-item"><i class="fas fa-shopping-bag"></i> PrestaShop</div>
                     <div class="software-item"><i class="fas fa-shopping-basket"></i> OpenCart</div>
                     <div class="software-item"><i class="fas fa-store"></i> Magento 2</div>
                     <div class="software-item"><i class="fas fa-file-alt"></i> TYPO3</div>
                     <div class="software-item"><i class="fab fa-joomla"></i> Joomla</div>
                     <div class="software-item"><i class="fab fa-drupal"></i> Drupal</div>
                     <div class="software-item"><i class="fas fa-ghost"></i> Ghost</div>
                     <div class="software-item"><i class="fas fa-newspaper"></i> Moodle</div>
                  </div>
               </div>

               <div class="tab-pane" id="frameworks">
                  <div class="software-grid">
                     <div class="software-item"><i class="fas fa-code"></i> LAMP Stack</div>
                     <div class="software-item"><i class="fas fa-code"></i> LEMP Stack</div>
                     <div class="software-item"><i class="fab fa-node-js"></i> MEAN Stack</div>
                     <div class="software-item"><i class="fab fa-react"></i> MERN Stack</div>
                     <div class="software-item"><i class="fab fa-vuejs"></i> MEVN Stack</div>
                     <div class="software-item"><i class="fab fa-laravel"></i> Laravel</div>
                     <div class="software-item"><i class="fab fa-php"></i> OpenLiteSpeed + PHP</div>
                     <div class="software-item"><i class="fab fa-node-js"></i> OpenLiteSpeed + Node.js</div>
                     <div class="software-item"><i class="fab fa-python"></i> Django</div>
                     <div class="software-item"><i class="fas fa-gem"></i> Rails</div>
                     <div class="software-item"><i class="fas fa-code"></i> ASP.NET</div>
                  </div>
               </div>

               <div class="tab-pane" id="devops">
                  <div class="software-grid">
                     <div class="software-item"><i class="fab fa-docker"></i> Docker</div>
                     <div class="software-item"><i class="fab fa-gitlab"></i> GitLab</div>
                     <div class="software-item"><i class="fas fa-chart-line"></i> Grafana</div>
                     <div class="software-item"><i class="fas fa-terminal"></i> Portainer</div>
                     <div class="software-item"><i class="fas fa-heartbeat"></i> Uptime Kuma</div>
                     <div class="software-item"><i class="fas fa-code"></i> VS Code</div>
                     <div class="software-item"><i class="fas fa-robot"></i> n8n</div>
                     <div class="software-item"><i class="fas fa-robot"></i> n8n (queue mode)</div>
                     <div class="software-item"><i class="fas fa-robot"></i> n8n (+100 workflows)</div>
                     <div class="software-item"><i class="fas fa-wind"></i> Windmill</div>
                     <div class="software-item"><i class="fas fa-shield-alt"></i> WireGuard</div>
                     <div class="software-item"><i class="fas fa-vpn"></i> OpenVPN</div>
                  </div>
               </div>

               <div class="tab-pane" id="business">
                  <div class="software-grid">
                     <div class="software-item"><i class="fas fa-briefcase"></i> Odoo</div>
                     <div class="software-item"><i class="fas fa-database"></i> Supabase</div>
                     <div class="software-item"><i class="fas fa-layer-group"></i> Strapi</div>
                     <div class="software-item"><i class="fas fa-comments"></i> Mattermost</div>
                     <div class="software-item"><i class="fas fa-headset"></i> Chatwoot</div>
                     <div class="software-item"><i class="fas fa-cloud"></i> Nextcloud</div>
                     <div class="software-item"><i class="fas fa-video"></i> Jitsi</div>
                     <div class="software-item"><i class="fas fa-film"></i> Plex Media Server</div>
                     <div class="software-item"><i class="fas fa-photo-video"></i> Jellyfin</div>
                     <div class="software-item"><i class="fas fa-home"></i> Home Assistant</div>
                     <div class="software-item"><i class="fas fa-comment"></i> Discourse</div>
                     <div class="software-item"><i class="fas fa-cube"></i> Immich</div>
                  </div>
               </div>
            </div>
         </div>

         <div class="software-cta">
            <p>And many more applications available...</p>
            <a href="features.php" class="btn-large btn-primary">View All Applications</a>
         </div>
      </div>
   </section>

   <!-- Pricing Section -->
   <section class="pricing-section" id="pricing">
      <div class="container">
         <div class="section-header">
            <div class="section-badge">PRICING</div>
            <h2 class="section-title">Simple, Transparent Pricing</h2>
            <p class="section-subtitle">Choose the perfect plan for your needs. All plans include 24/7 support and 99.99% uptime guarantee.</p>
            <div class="guarantee-badge">
               <i class="fas fa-shield-alt"></i>
               7-Day Money Back Guarantee - No Questions Asked
            </div>
         </div>

         <div class="pricing-grid">
            <?php
            if (!empty($packages) && count($packages) > 0):
               foreach ($packages as $index => $package):
                  // Determine available price cycles
                  $availablePrices = [];
                  if (!empty($package['price_monthly']) && $package['price_monthly'] > 0) {
                     $renewalMonthly = !empty($package['renewal_price_monthly']) ? $package['renewal_price_monthly'] : $package['price_monthly'];
                     $availablePrices['monthly'] = [
                        'price' => $package['price_monthly'],
                        'label' => 'Monthly',
                        'perMonth' => $package['price_monthly'],
                        'totalPrice' => $package['price_monthly'],
                        'renewal' => $renewalMonthly
                     ];
                  }
                  if (!empty($package['price_yearly']) && $package['price_yearly'] > 0) {
                     $renewalYearly = !empty($package['renewal_price_yearly']) ? $package['renewal_price_yearly'] : $package['price_yearly'];
                     $availablePrices['yearly'] = [
                        'price' => $package['price_yearly'],
                        'label' => 'Yearly',
                        'perMonth' => $package['price_yearly'] / 12,
                        'totalPrice' => $package['price_yearly'],
                        'renewal' => $renewalYearly
                     ];
                  }
                  if (!empty($package['price_2years']) && $package['price_2years'] > 0) {
                     $renewal2y = !empty($package['renewal_price_2years']) ? $package['renewal_price_2years'] : $package['price_2years'];
                     $availablePrices['2years'] = [
                        'price' => $package['price_2years'],
                        'label' => '2 Years',
                        'perMonth' => $package['price_2years'] / 24,
                        'totalPrice' => $package['price_2years'],
                        'renewal' => $renewal2y
                     ];
                  }
                  if (!empty($package['price_4years']) && $package['price_4years'] > 0) {
                     $renewal4y = !empty($package['renewal_price_4years']) ? $package['renewal_price_4years'] : $package['price_4years'];
                     $availablePrices['4years'] = [
                        'price' => $package['price_4years'],
                        'label' => '4 Years',
                        'perMonth' => $package['price_4years'] / 48,
                        'totalPrice' => $package['price_4years'],
                        'renewal' => $renewal4y
                     ];
                  }

                  // Skip if no pricing
                  if (empty($availablePrices)) continue;

                  // Get default price
                  $defaultCycle = 'monthly';
                  if (isset($availablePrices['monthly'])) {
                     $defaultCycle = 'monthly';
                     $displayPrice = $availablePrices['monthly']['perMonth'];
                     $renewalPrice = $availablePrices['monthly']['renewal'];
                  } elseif (isset($availablePrices['yearly'])) {
                     $defaultCycle = 'yearly';
                     $displayPrice = $availablePrices['yearly']['perMonth'];
                     $renewalPrice = $availablePrices['yearly']['renewal'];
                  } elseif (isset($availablePrices['2years'])) {
                     $defaultCycle = '2years';
                     $displayPrice = $availablePrices['2years']['perMonth'];
                     $renewalPrice = $availablePrices['2years']['renewal'];
                  } else {
                     $defaultCycle = '4years';
                     $displayPrice = $availablePrices['4years']['perMonth'];
                     $renewalPrice = $availablePrices['4years']['renewal'];
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
                        <div style="font-size:12px; color:#9CA3AF; margin-top:4px;">
                           * Prices exclude applicable GST & fees
                        </div>
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
                           <li><i class="fas fa-check"></i> Free SSL Certificate</li>
                           <li><i class="fas fa-check"></i> Daily Backups</li>
                           <li><i class="fas fa-check"></i> 24/7 Support</li>
                           <li><i class="fas fa-check"></i> DDoS Protection</li>
                        <?php endif; ?>
                     </ul>

                     <a href="select-package.php?package=<?php echo urlencode($package['slug']); ?>&cycle=<?php echo $defaultCycle; ?>"
                        class="btn-pricing <?php echo $isPopular ? 'btn-primary' : 'btn-outline'; ?>">
                        Get Started Now
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

         <div class="pricing-note">
            <i class="fas fa-shield-alt"></i>
            <p>All prices shown are introductory rates. Renewal prices may vary. 7-day money-back guarantee applies to all plans.</p>
         </div>
      </div>
   </section>

   <!-- FAQ Section -->
   <section class="faq-section">
      <div class="container">
         <div class="section-header">
            <div class="section-badge">FAQ</div>
            <h2 class="section-title">Frequently Asked Questions</h2>
         </div>

         <div class="faq-grid">
            <div class="faq-item">
               <h3><i class="fas fa-question-circle"></i> What is the money-back guarantee?</h3>
               <p>We offer a 7-day money-back guarantee on all hosting plans. If you're not satisfied for any reason, contact us within 7 days for a full refund.</p>
            </div>
            <div class="faq-item">
               <h3><i class="fas fa-question-circle"></i> Can I upgrade my plan later?</h3>
               <p>Yes! You can upgrade your hosting plan at any time. We'll prorate the charges and you'll only pay the difference.</p>
            </div>
            <div class="faq-item">
               <h3><i class="fas fa-question-circle"></i> Do you provide SSL certificates?</h3>
               <p>Absolutely! All hosting plans include free SSL certificates via Let's Encrypt with automatic renewal.</p>
            </div>
            <div class="faq-item">
               <h3><i class="fas fa-question-circle"></i> How do automatic backups work?</h3>
               <p>We perform daily automatic backups of your entire website, databases, and files. You can restore any backup with one click.</p>
            </div>
            <div class="faq-item">
               <h3><i class="fas fa-question-circle"></i> What kind of support do you offer?</h3>
               <p>We provide 24/7 expert support via live chat, email, and phone. Our team is always ready to help with any issues.</p>
            </div>
            <div class="faq-item">
               <h3><i class="fas fa-question-circle"></i> Can I host multiple websites?</h3>
               <p>Yes! Depending on your plan, you can host multiple websites on a single hosting account. Check plan details for limits.</p>
            </div>
         </div>
      </div>
   </section>

   <!-- CTA Section -->
   <section class="cta-section">
      <div class="container">
         <div class="cta-content">
            <h2 class="cta-title">Ready to get started?</h2>
            <p class="cta-description">Join 100,000+ satisfied customers. Start your hosting journey today with our 7-day money-back guarantee.</p>
            <div class="cta-buttons">
               <a href="register.php" class="btn-large btn-white">Get Started Now</a>
               <a href="#pricing" class="btn-large btn-outline-white">View Pricing</a>
            </div>
         </div>
      </div>
   </section>

   <?php include 'components/footer.php'; ?>

