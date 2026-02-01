<?php
session_start();
$pageTitle = "Custom Solutions - InfraLabs Cloud";
$pageDescription = "Enterprise-grade custom hosting solutions tailored to your specific needs. Get dedicated support and infrastructure.";
$additionalCSS = "./assets/css/pages.css";
include 'components/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
    <div class="container">
        <h1 class="page-title">Custom Solutions</h1>
        <p class="page-subtitle">Tailored hosting infrastructure for your unique requirements</p>
    </div>
</section>

<!-- Custom Solutions Content -->
<section class="content-section">
    <div class="container">
        <div style="max-width: 900px; margin: 0 auto;">
            <div class="content-block">
                <h2>Enterprise-Grade Custom Hosting</h2>
                <p>
                    At InfraLabs Cloud, we understand that every business has unique requirements. Our custom solutions team
                    works with you to design, deploy, and manage hosting infrastructure that perfectly matches your needs.
                </p>
                <p>
                    Whether you need a complex multi-server setup, specific compliance requirements, or specialized application
                    hosting, we have the expertise and infrastructure to deliver.
                </p>
            </div>

            <div class="stats-grid" style="margin: 48px 0;">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-building"></i></div>
                    <div class="stat-value">500+</div>
                    <div class="stat-label">Enterprise Clients</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-server"></i></div>
                    <div class="stat-value">10k+</div>
                    <div class="stat-label">Custom Deployments</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-headset"></i></div>
                    <div class="stat-value">24/7</div>
                    <div class="stat-label">Dedicated Support</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-shield-alt"></i></div>
                    <div class="stat-value">100%</div>
                    <div class="stat-label">SLA Guarantee</div>
                </div>
            </div>

            <div class="content-block">
                <h2>What We Offer</h2>
                
                <div class="hosting-grid" style="margin-top: 32px;">
                    <div class="hosting-card">
                        <div class="hosting-icon"><i class="fas fa-network-wired"></i></div>
                        <h3>Custom Infrastructure</h3>
                        <p>Tailored server configurations, load balancing, and network architecture designed for your specific workload.</p>
                        <ul class="hosting-features">
                            <li><i class="fas fa-check"></i> Multi-server setups</li>
                            <li><i class="fas fa-check"></i> Load balancers</li>
                            <li><i class="fas fa-check"></i> CDN integration</li>
                            <li><i class="fas fa-check"></i> Custom networking</li>
                        </ul>
                    </div>

                    <div class="hosting-card">
                        <div class="hosting-icon"><i class="fas fa-shield-alt"></i></div>
                        <h3>Compliance & Security</h3>
                        <p>Meet industry-specific compliance requirements with our certified infrastructure and security protocols.</p>
                        <ul class="hosting-features">
                            <li><i class="fas fa-check"></i> HIPAA compliance</li>
                            <li><i class="fas fa-check"></i> PCI DSS certified</li>
                            <li><i class="fas fa-check"></i> GDPR compliant</li>
                            <li><i class="fas fa-check"></i> SOC 2 Type II</li>
                        </ul>
                    </div>

                    <div class="hosting-card">
                        <div class="hosting-icon"><i class="fas fa-database"></i></div>
                        <h3>Database Solutions</h3>
                        <p>Managed database clusters with automatic failover, replication, and performance optimization.</p>
                        <ul class="hosting-features">
                            <li><i class="fas fa-check"></i> MySQL/MariaDB clusters</li>
                            <li><i class="fas fa-check"></i> PostgreSQL HA</li>
                            <li><i class="fas fa-check"></i> MongoDB replica sets</li>
                            <li><i class="fas fa-check"></i> Redis caching</li>
                        </ul>
                    </div>

                    <div class="hosting-card">
                        <div class="hosting-icon"><i class="fas fa-code"></i></div>
                        <h3>Application Hosting</h3>
                        <p>Specialized hosting for complex applications with custom runtime environments and dependencies.</p>
                        <ul class="hosting-features">
                            <li><i class="fas fa-check"></i> Custom tech stacks</li>
                            <li><i class="fas fa-check"></i> Microservices architecture</li>
                            <li><i class="fas fa-check"></i> Container orchestration</li>
                            <li><i class="fas fa-check"></i> CI/CD pipelines</li>
                        </ul>
                    </div>

                    <div class="hosting-card">
                        <div class="hosting-icon"><i class="fas fa-users-cog"></i></div>
                        <h3>Managed Services</h3>
                        <p>Let our experts handle server management, monitoring, updates, and optimization for you.</p>
                        <ul class="hosting-features">
                            <li><i class="fas fa-check"></i> 24/7 monitoring</li>
                            <li><i class="fas fa-check"></i> Proactive maintenance</li>
                            <li><i class="fas fa-check"></i> Security patching</li>
                            <li><i class="fas fa-check"></i> Performance tuning</li>
                        </ul>
                    </div>

                    <div class="hosting-card">
                        <div class="hosting-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <h3>Migration Services</h3>
                        <p>Seamless migration from your current hosting provider with zero downtime and data integrity.</p>
                        <ul class="hosting-features">
                            <li><i class="fas fa-check"></i> Zero-downtime migration</li>
                            <li><i class="fas fa-check"></i> Data integrity checks</li>
                            <li><i class="fas fa-check"></i> DNS management</li>
                            <li><i class="fas fa-check"></i> Post-migration support</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="content-block">
                <h2>Our Process</h2>
                <div style="display: grid; gap: 24px; margin-top: 24px;">
                    <div class="feature-card" style="padding: 24px;">
                        <div style="display: flex; gap: 20px; align-items: start;">
                            <div style="width: 50px; height: 50px; background: rgba(30, 144, 255, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800; color: var(--primary-blue); flex-shrink: 0;">1</div>
                            <div>
                                <h4 style="font-size: 20px; font-weight: 700; margin-bottom: 8px;">Discovery & Consultation</h4>
                                <p style="color: var(--text-secondary);">We start with an in-depth consultation to understand your requirements, current infrastructure, and business goals.</p>
                            </div>
                        </div>
                    </div>

                    <div class="feature-card" style="padding: 24px;">
                        <div style="display: flex; gap: 20px; align-items: start;">
                            <div style="width: 50px; height: 50px; background: rgba(30, 144, 255, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800; color: var(--primary-blue); flex-shrink: 0;">2</div>
                            <div>
                                <h4 style="font-size: 20px; font-weight: 700; margin-bottom: 8px;">Solution Design</h4>
                                <p style="color: var(--text-secondary);">Our architects design a custom solution with detailed specifications, cost estimates, and implementation timeline.</p>
                            </div>
                        </div>
                    </div>

                    <div class="feature-card" style="padding: 24px;">
                        <div style="display: flex; gap: 20px; align-items: start;">
                            <div style="width: 50px; height: 50px; background: rgba(30, 144, 255, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800; color: var(--primary-blue); flex-shrink: 0;">3</div>
                            <div>
                                <h4 style="font-size: 20px; font-weight: 700; margin-bottom: 8px;">Deployment & Testing</h4>
                                <p style="color: var(--text-secondary);">We deploy your infrastructure, conduct thorough testing, and ensure everything meets your performance requirements.</p>
                            </div>
                        </div>
                    </div>

                    <div class="feature-card" style="padding: 24px;">
                        <div style="display: flex; gap: 20px; align-items: start;">
                            <div style="width: 50px; height: 50px; background: rgba(30, 144, 255, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800; color: var(--primary-blue); flex-shrink: 0;">4</div>
                            <div>
                                <h4 style="font-size: 20px; font-weight: 700; margin-bottom: 8px;">Ongoing Support</h4>
                                <p style="color: var(--text-secondary);">Receive dedicated support, monitoring, and optimization to ensure your infrastructure continues to perform optimally.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-block">
                <h2>Industries We Serve</h2>
                <div class="reviews-grid" style="margin-top: 32px;">
                    <div class="review-card">
                        <div class="review-stars"><i class="fas fa-heartbeat"></i></div>
                        <h4 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">Healthcare</h4>
                        <p class="review-text">HIPAA-compliant hosting for medical records, telemedicine platforms, and healthcare applications.</p>
                    </div>

                    <div class="review-card">
                        <div class="review-stars"><i class="fas fa-shopping-cart"></i></div>
                        <h4 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">E-Commerce</h4>
                        <p class="review-text">High-performance hosting for online stores with PCI DSS compliance and scalability.</p>
                    </div>

                    <div class="review-card">
                        <div class="review-stars"><i class="fas fa-graduation-cap"></i></div>
                        <h4 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">Education</h4>
                        <p class="review-text">Reliable hosting for learning management systems, student portals, and educational platforms.</p>
                    </div>

                    <div class="review-card">
                        <div class="review-stars"><i class="fas fa-chart-line"></i></div>
                        <h4 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">Finance</h4>
                        <p class="review-text">Secure, compliant hosting for fintech applications, trading platforms, and financial services.</p>
                    </div>

                    <div class="review-card">
                        <div class="review-stars"><i class="fas fa-gamepad"></i></div>
                        <h4 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">Gaming</h4>
                        <p class="review-text">Low-latency hosting for game servers, matchmaking systems, and gaming communities.</p>
                    </div>

                    <div class="review-card">
                        <div class="review-stars"><i class="fas fa-bullhorn"></i></div>
                        <h4 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">Media & Publishing</h4>
                        <p class="review-text">Scalable hosting for content delivery, streaming platforms, and high-traffic publications.</p>
                    </div>
                </div>
            </div>

            <div class="content-block">
                <h2>Get Started with Custom Solutions</h2>
                <p>
                    Ready to discuss your custom hosting needs? Our solutions team is here to help you design the perfect
                    infrastructure for your business.
                </p>
                
                <div style="background: var(--card-bg); border: 2px solid var(--primary-blue); border-radius: 16px; padding: 40px; margin-top: 32px; text-align: center;">
                    <h3 style="font-size: 28px; font-weight: 700; margin-bottom: 16px;">Schedule a Consultation</h3>
                    <p style="font-size: 16px; color: var(--text-secondary); margin-bottom: 24px;">
                        Talk to our solutions architects about your requirements. We'll provide a detailed proposal and cost estimate.
                    </p>
                    <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                        <a href="contact.php" class="btn-primary btn-large">
                            <i class="fas fa-calendar"></i> Contact Us
                        </a>
                        <a href="mailto:enterprise@infralabs.cloud" class="btn-outline btn-large">
                            <i class="fas fa-envelope"></i> Email Enterprise Team
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'components/footer.php'; ?>
