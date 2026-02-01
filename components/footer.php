    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <div class="footer-brand">
                        <i class="fas fa-cloud"></i>
                        <span>InfraLabs Cloud</span>
                    </div>
                    <p class="footer-description">
                        Professional hosting solutions with 99.99% uptime guarantee, 
                        24/7 support, and cutting-edge technology.
                    </p>
                </div>

                <div class="footer-column">
                    <h4>Hosting</h4>
                    <ul>
                        <li><a href="hosting-types.php#shared">Shared Hosting</a></li>
                        <li><a href="hosting-types.php#vps">VPS Hosting</a></li>
                        <li><a href="hosting-types.php#wordpress">WordPress Hosting</a></li>
                        <li><a href="hosting-types.php#nodejs">Node.js Hosting</a></li>
                        <li><a href="hosting-types.php#react">React Hosting</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="features.php">Features</a></li>
                        <li><a href="hosting-types.php">Hosting</a></li>
                        <li><a href="custom-solutions.php">Custom Solutions</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
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
                <p>&copy; <?php echo date('Y'); ?> InfraLabs Cloud. All rights reserved.</p>
                <div class="footer-badges">
                    <div class="footer-badge">
                        <i class="fas fa-shield-alt"></i>
                        <span>SSL Secured</span>
                    </div>
                    <div class="footer-badge">
                        <i class="fas fa-clock"></i>
                        <span>24/7 Support</span>
                    </div>
                    <div class="footer-badge">
                        <i class="fas fa-server"></i>
                        <span>99.99% Uptime</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        document.getElementById('mobileToggle').addEventListener('click', function() {
            document.getElementById('navbarMenu').classList.toggle('active');
            this.classList.toggle('active');
        });

        // Software Tabs (if present on page)
        document.querySelectorAll('.tab-btn').forEach(button => {
            button.addEventListener('click', function() {
                const tabId = this.dataset.tab;
                
                // Remove active class from all buttons and panes
                document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
                
                // Add active class to clicked button and corresponding pane
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href.length > 1) {
                    const target = document.querySelector(href);
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                }
            });
        });
    </script>
</body>
</html>
