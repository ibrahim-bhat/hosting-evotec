<?php
session_start();
$pageTitle = "Hosting Types - InfraLabs Cloud";
$pageDescription = "Explore our comprehensive range of hosting solutions including Shared, VPS, WordPress, Node.js, React, PHP, and Business hosting.";
$additionalCSS = "./assets/css/pages.css";
include 'components/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
    <div class="container">
        <h1 class="page-title">Hosting Solutions</h1>
        <p class="page-subtitle">Choose the perfect hosting plan for your needs</p>
    </div>
</section>

<!-- Hosting Types Content -->
<section class="content-section">
    <div class="container">
        <!-- Shared Hosting -->
        <div class="hosting-detail-section" id="shared">
            <div class="hosting-detail-grid">
                <div class="hosting-detail-content">
                    <div class="section-badge">SHARED HOSTING</div>
                    <h2>Shared Web Hosting</h2>
                    <p class="section-description">
                        Perfect for beginners and small websites. Get started with professional hosting at an affordable price.
                        Share server resources with other websites while maintaining excellent performance.
                    </p>
                    
                    <h3>Key Features</h3>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> 99.99% uptime guarantee</li>
                        <li><i class="fas fa-check-circle"></i> Free SSL certificate</li>
                        <li><i class="fas fa-check-circle"></i> Unlimited bandwidth</li>
                        <li><i class="fas fa-check-circle"></i> cPanel control panel</li>
                        <li><i class="fas fa-check-circle"></i> 1-click app installer</li>
                        <li><i class="fas fa-check-circle"></i> Daily backups</li>
                        <li><i class="fas fa-check-circle"></i> Email accounts</li>
                        <li><i class="fas fa-check-circle"></i> 24/7 support</li>
                    </ul>

                    <h3>Perfect For</h3>
                    <ul class="feature-list">
                        <li><i class="fas fa-star"></i> Personal blogs and portfolios</li>
                        <li><i class="fas fa-star"></i> Small business websites</li>
                        <li><i class="fas fa-star"></i> Landing pages</li>
                        <li><i class="fas fa-star"></i> Development and testing</li>
                    </ul>

                    <a href="index.php#pricing" class="btn-primary btn-large">View Plans</a>
                </div>
                <div class="hosting-detail-visual">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-server"></i></div>
                        <h4>NVMe SSD Storage</h4>
                        <p>Lightning-fast storage for optimal performance</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                        <h4>Free SSL</h4>
                        <p>Secure your website with Let's Encrypt SSL</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-tachometer-alt"></i></div>
                        <h4>LiteSpeed Cache</h4>
                        <p>Up to 84x faster than traditional hosting</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- VPS Hosting -->
        <div class="hosting-detail-section" id="vps">
            <div class="hosting-detail-grid reverse">
                <div class="hosting-detail-visual">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-microchip"></i></div>
                        <h4>Dedicated Resources</h4>
                        <p>Guaranteed CPU, RAM, and storage</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-crown"></i></div>
                        <h4>Root Access</h4>
                        <p>Full control over your server</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-rocket"></i></div>
                        <h4>Scalable</h4>
                        <p>Upgrade resources as you grow</p>
                    </div>
                </div>
                <div class="hosting-detail-content">
                    <div class="section-badge">VPS HOSTING</div>
                    <h2>Virtual Private Server</h2>
                    <p class="section-description">
                        Get dedicated resources and full control with VPS hosting. Perfect for growing websites and applications
                        that need more power and flexibility than shared hosting.
                    </p>
                    
                    <h3>Key Features</h3>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> Dedicated CPU & RAM</li>
                        <li><i class="fas fa-check-circle"></i> Root/SSH access</li>
                        <li><i class="fas fa-check-circle"></i> Choice of OS (Ubuntu, CentOS, Debian)</li>
                        <li><i class="fas fa-check-circle"></i> Full server customization</li>
                        <li><i class="fas fa-check-circle"></i> Scalable resources</li>
                        <li><i class="fas fa-check-circle"></i> DDoS protection</li>
                        <li><i class="fas fa-check-circle"></i> Managed or unmanaged options</li>
                        <li><i class="fas fa-check-circle"></i> 24/7 expert support</li>
                    </ul>

                    <h3>Perfect For</h3>
                    <ul class="feature-list">
                        <li><i class="fas fa-star"></i> High-traffic websites</li>
                        <li><i class="fas fa-star"></i> E-commerce platforms</li>
                        <li><i class="fas fa-star"></i> Custom applications</li>
                        <li><i class="fas fa-star"></i> Development environments</li>
                    </ul>

                    <a href="index.php#pricing" class="btn-primary btn-large">View Plans</a>
                </div>
            </div>
        </div>

        <!-- WordPress Hosting -->
        <div class="hosting-detail-section" id="wordpress">
            <div class="hosting-detail-grid">
                <div class="hosting-detail-content">
                    <div class="section-badge">WORDPRESS HOSTING</div>
                    <h2>Optimized WordPress Hosting</h2>
                    <p class="section-description">
                        Specially optimized for WordPress with enhanced performance, security, and automatic updates.
                        Launch your WordPress site in minutes with our 1-click installer.
                    </p>
                    
                    <h3>Key Features</h3>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> 1-click WordPress installation</li>
                        <li><i class="fas fa-check-circle"></i> Automatic WordPress updates</li>
                        <li><i class="fas fa-check-circle"></i> WordPress-optimized caching</li>
                        <li><i class="fas fa-check-circle"></i> Pre-installed security plugins</li>
                        <li><i class="fas fa-check-circle"></i> Staging environment</li>
                        <li><i class="fas fa-check-circle"></i> WP-CLI access</li>
                        <li><i class="fas fa-check-circle"></i> Free WordPress themes</li>
                        <li><i class="fas fa-check-circle"></i> Expert WordPress support</li>
                    </ul>

                    <h3>Perfect For</h3>
                    <ul class="feature-list">
                        <li><i class="fas fa-star"></i> WordPress blogs</li>
                        <li><i class="fas fa-star"></i> Business websites</li>
                        <li><i class="fas fa-star"></i> Online magazines</li>
                        <li><i class="fas fa-star"></i> Portfolio sites</li>
                    </ul>

                    <a href="index.php#pricing" class="btn-primary btn-large">View Plans</a>
                </div>
                <div class="hosting-detail-visual">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fab fa-wordpress"></i></div>
                        <h4>1-Click Install</h4>
                        <p>Launch WordPress in under 60 seconds</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-sync"></i></div>
                        <h4>Auto Updates</h4>
                        <p>Always stay secure and up-to-date</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                        <h4>Optimized Speed</h4>
                        <p>LiteSpeed cache for blazing performance</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Node.js Hosting -->
        <div class="hosting-detail-section" id="nodejs">
            <div class="hosting-detail-grid reverse">
                <div class="hosting-detail-visual">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fab fa-node-js"></i></div>
                        <h4>Latest Node.js</h4>
                        <p>Support for all Node.js versions</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-terminal"></i></div>
                        <h4>NPM & Yarn</h4>
                        <p>Full package manager support</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-database"></i></div>
                        <h4>MongoDB Ready</h4>
                        <p>Pre-configured database support</p>
                    </div>
                </div>
                <div class="hosting-detail-content">
                    <div class="section-badge">NODE.JS HOSTING</div>
                    <h2>Node.js Application Hosting</h2>
                    <p class="section-description">
                        Deploy and scale your Node.js applications with ease. Full support for Express, Next.js, and other
                        popular Node.js frameworks with automatic SSL and process management.
                    </p>
                    
                    <h3>Key Features</h3>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> Multiple Node.js versions</li>
                        <li><i class="fas fa-check-circle"></i> NPM & Yarn support</li>
                        <li><i class="fas fa-check-circle"></i> PM2 process manager</li>
                        <li><i class="fas fa-check-circle"></i> WebSocket support</li>
                        <li><i class="fas fa-check-circle"></i> MongoDB & PostgreSQL</li>
                        <li><i class="fas fa-check-circle"></i> Git deployment</li>
                        <li><i class="fas fa-check-circle"></i> Environment variables</li>
                        <li><i class="fas fa-check-circle"></i> Auto-restart on crash</li>
                    </ul>

                    <h3>Perfect For</h3>
                    <ul class="feature-list">
                        <li><i class="fas fa-star"></i> REST APIs</li>
                        <li><i class="fas fa-star"></i> Real-time applications</li>
                        <li><i class="fas fa-star"></i> Microservices</li>
                        <li><i class="fas fa-star"></i> Chat applications</li>
                    </ul>

                    <a href="index.php#pricing" class="btn-primary btn-large">View Plans</a>
                </div>
            </div>
        </div>

        <!-- React Hosting -->
        <div class="hosting-detail-section" id="react">
            <div class="hosting-detail-grid">
                <div class="hosting-detail-content">
                    <div class="section-badge">REACT HOSTING</div>
                    <h2>React Application Hosting</h2>
                    <p class="section-description">
                        Host your React applications with optimized build processes, CDN integration, and automatic deployments.
                        Perfect for SPAs and modern web applications.
                    </p>
                    
                    <h3>Key Features</h3>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> Automatic build optimization</li>
                        <li><i class="fas fa-check-circle"></i> CDN integration</li>
                        <li><i class="fas fa-check-circle"></i> Git-based deployments</li>
                        <li><i class="fas fa-check-circle"></i> Environment variables</li>
                        <li><i class="fas fa-check-circle"></i> Preview deployments</li>
                        <li><i class="fas fa-check-circle"></i> Custom domains</li>
                        <li><i class="fas fa-check-circle"></i> HTTPS by default</li>
                        <li><i class="fas fa-check-circle"></i> Rollback support</li>
                    </ul>

                    <h3>Perfect For</h3>
                    <ul class="feature-list">
                        <li><i class="fas fa-star"></i> Single Page Applications</li>
                        <li><i class="fas fa-star"></i> Progressive Web Apps</li>
                        <li><i class="fas fa-star"></i> Admin dashboards</li>
                        <li><i class="fas fa-star"></i> E-commerce frontends</li>
                    </ul>

                    <a href="index.php#pricing" class="btn-primary btn-large">View Plans</a>
                </div>
                <div class="hosting-detail-visual">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fab fa-react"></i></div>
                        <h4>React Optimized</h4>
                        <p>Optimized for React and Next.js apps</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-globe"></i></div>
                        <h4>Global CDN</h4>
                        <p>Fast delivery worldwide</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-code-branch"></i></div>
                        <h4>Git Deploy</h4>
                        <p>Push to deploy automatically</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- PHP Hosting -->
        <div class="hosting-detail-section" id="php">
            <div class="hosting-detail-grid reverse">
                <div class="hosting-detail-visual">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fab fa-php"></i></div>
                        <h4>PHP 8.3</h4>
                        <p>Latest PHP with OPcache</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-cogs"></i></div>
                        <h4>Composer</h4>
                        <p>Full dependency management</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-database"></i></div>
                        <h4>MySQL & MariaDB</h4>
                        <p>High-performance databases</p>
                    </div>
                </div>
                <div class="hosting-detail-content">
                    <div class="section-badge">PHP HOSTING</div>
                    <h2>PHP Application Hosting</h2>
                    <p class="section-description">
                        Professional PHP hosting with support for Laravel, Symfony, CodeIgniter, and all major PHP frameworks.
                        Multiple PHP versions and full Composer support.
                    </p>
                    
                    <h3>Key Features</h3>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> PHP 7.4 to 8.3 support</li>
                        <li><i class="fas fa-check-circle"></i> OPcache enabled</li>
                        <li><i class="fas fa-check-circle"></i> Composer pre-installed</li>
                        <li><i class="fas fa-check-circle"></i> MySQL & PostgreSQL</li>
                        <li><i class="fas fa-check-circle"></i> Redis & Memcached</li>
                        <li><i class="fas fa-check-circle"></i> SSH access</li>
                        <li><i class="fas fa-check-circle"></i> Cron jobs</li>
                        <li><i class="fas fa-check-circle"></i> Framework support</li>
                    </ul>

                    <h3>Perfect For</h3>
                    <ul class="feature-list">
                        <li><i class="fas fa-star"></i> Laravel applications</li>
                        <li><i class="fas fa-star"></i> Custom PHP projects</li>
                        <li><i class="fas fa-star"></i> E-commerce sites</li>
                        <li><i class="fas fa-star"></i> Business applications</li>
                    </ul>

                    <a href="index.php#pricing" class="btn-primary btn-large">View Plans</a>
                </div>
            </div>
        </div>

        <!-- Business Tools -->
        <div class="hosting-detail-section" id="business">
            <div class="hosting-detail-grid">
                <div class="hosting-detail-content">
                    <div class="section-badge">BUSINESS TOOLS</div>
                    <h2>CRM & Business Application Hosting</h2>
                    <p class="section-description">
                        Host powerful business applications like Odoo, ERPNext, n8n, and more. Pre-configured environments
                        for seamless deployment of enterprise tools.
                    </p>
                    
                    <h3>Supported Applications</h3>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> Odoo ERP & CRM</li>
                        <li><i class="fas fa-check-circle"></i> ERPNext</li>
                        <li><i class="fas fa-check-circle"></i> n8n Workflow Automation</li>
                        <li><i class="fas fa-check-circle"></i> SuiteCRM</li>
                        <li><i class="fas fa-check-circle"></i> Mautic Marketing</li>
                        <li><i class="fas fa-check-circle"></i> Invoice Ninja</li>
                        <li><i class="fas fa-check-circle"></i> Nextcloud</li>
                        <li><i class="fas fa-check-circle"></i> Custom business apps</li>
                    </ul>

                    <h3>Perfect For</h3>
                    <ul class="feature-list">
                        <li><i class="fas fa-star"></i> Small to medium businesses</li>
                        <li><i class="fas fa-star"></i> Startups</li>
                        <li><i class="fas fa-star"></i> Agencies</li>
                        <li><i class="fas fa-star"></i> Enterprise teams</li>
                    </ul>

                    <a href="custom-solutions.php" class="btn-primary btn-large">Request Custom Setup</a>
                </div>
                <div class="hosting-detail-visual">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-briefcase"></i></div>
                        <h4>Pre-Configured</h4>
                        <p>Ready-to-use business applications</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-users"></i></div>
                        <h4>Multi-User</h4>
                        <p>Support for teams and organizations</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-lock"></i></div>
                        <h4>Secure</h4>
                        <p>Enterprise-grade security</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.hosting-detail-section {
    padding: 80px 0;
    border-bottom: 1px solid var(--border-color);
}

.hosting-detail-section:last-child {
    border-bottom: none;
}

.hosting-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: start;
}

.hosting-detail-grid.reverse {
    direction: rtl;
}

.hosting-detail-grid.reverse > * {
    direction: ltr;
}

.hosting-detail-content h2 {
    font-size: 42px;
    font-weight: 800;
    margin-bottom: 16px;
}

.hosting-detail-content h3 {
    font-size: 24px;
    font-weight: 700;
    margin: 32px 0 16px 0;
}

.hosting-detail-visual {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.feature-card {
    background: var(--card-bg);
    border: 2px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    transition: all 0.3s ease;
}

.feature-card:hover {
    border-color: var(--primary-blue);
    transform: translateX(5px);
}

.feature-icon {
    width: 60px;
    height: 60px;
    background: rgba(30, 144, 255, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: var(--primary-blue);
    margin-bottom: 16px;
}

.feature-card h4 {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 8px;
}

.feature-card p {
    font-size: 14px;
    color: var(--text-secondary);
}

@media (max-width: 992px) {
    .hosting-detail-grid,
    .hosting-detail-grid.reverse {
        grid-template-columns: 1fr;
        direction: ltr;
    }
    
    .hosting-detail-content h2 {
        font-size: 32px;
    }
}
</style>

<?php include 'components/footer.php'; ?>
