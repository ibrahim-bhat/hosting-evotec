<?php
session_start();
$pageTitle = "Contact Us - InfraLabs Cloud";
$pageDescription = "Get in touch with InfraLabs Cloud. Our support team is available 24/7 to help you with your hosting needs.";
$additionalCSS = "./assets/css/pages.css";
include 'components/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
    <div class="container">
        <h1 class="page-title">Contact Us</h1>
        <p class="page-subtitle">We're here to help! Reach out to our team 24/7</p>
    </div>
</section>

<!-- Contact Section -->
<section class="content-section">
    <div class="container">
        <div class="contact-wrapper">
            <!-- Contact Form -->
            <div class="contact-form-container">
                <h2>Send us a Message</h2>
                <p class="section-description">Fill out the form below and we'll get back to you within 24 hours.</p>
                
                <form action="https://formspree.io/f/xzdzzalr" method="POST" class="contact-form" id="contactForm">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required placeholder="John Doe">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required placeholder="john@example.com">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" placeholder="+1 (555) 123-4567">
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject *</label>
                        <select id="subject" name="subject" required>
                            <option value="">Select a subject</option>
                            <option value="Sales Inquiry">Sales Inquiry</option>
                            <option value="Technical Support">Technical Support</option>
                            <option value="Billing Question">Billing Question</option>
                            <option value="Custom Solution">Custom Solution Request</option>
                            <option value="Partnership">Partnership Opportunity</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" rows="6" required placeholder="Tell us how we can help you..."></textarea>
                    </div>

                    <input type="hidden" name="_next" value="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/contact-success.php'; ?>">
                    <input type="hidden" name="_subject" value="New Contact Form Submission - InfraLabs Cloud">
                    <input type="text" name="_gotcha" style="display:none">

                    <button type="submit" class="btn-primary btn-large" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>

            <!-- Contact Information -->
            <div class="contact-info-container">
                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Our support team is available around the clock to assist you with any questions or issues.</p>
                    <a href="mailto:support@infralabs.cloud" class="contact-link">support@infralabs.cloud</a>
                </div>

                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Sales Inquiries</h3>
                    <p>Interested in our hosting solutions? Our sales team is ready to help you find the perfect plan.</p>
                    <a href="mailto:sales@infralabs.cloud" class="contact-link">sales@infralabs.cloud</a>
                </div>

                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3>Custom Solutions</h3>
                    <p>Need a tailored hosting solution? Contact our enterprise team for custom packages.</p>
                    <a href="custom-solutions.php" class="contact-link">Learn More →</a>
                </div>

                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Response Time</h3>
                    <p>We typically respond to all inquiries within 24 hours, often much sooner.</p>
                    <div style="margin-top: 12px;">
                        <strong style="color: var(--success-green);">Average: 2-4 hours</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section" style="padding: 60px 0;">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Frequently Asked Questions</h2>
            <p class="section-subtitle">Quick answers to common questions</p>
        </div>

        <div class="faq-grid">
            <div class="faq-item">
                <h3><i class="fas fa-question-circle"></i> How quickly will I receive a response?</h3>
                <p>We aim to respond to all inquiries within 24 hours. For urgent technical issues, our live chat support is available 24/7.</p>
            </div>

            <div class="faq-item">
                <h3><i class="fas fa-question-circle"></i> Can I schedule a call with your team?</h3>
                <p>Yes! For enterprise and custom solution inquiries, we're happy to schedule a call. Mention this in your message.</p>
            </div>

            <div class="faq-item">
                <h3><i class="fas fa-question-circle"></i> Do you offer migration assistance?</h3>
                <p>Absolutely! We provide free migration assistance for all new customers. Contact us to get started.</p>
            </div>

            <div class="faq-item">
                <h3><i class="fas fa-question-circle"></i> What if I need immediate support?</h3>
                <p>For immediate assistance, use our live chat feature or call our 24/7 support hotline available to all customers.</p>
            </div>
        </div>
    </div>
</section>

<style>
.contact-wrapper {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 60px;
    margin-top: 40px;
}

.contact-form-container h2 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 12px;
}

.contact-form {
    margin-top: 32px;
}

.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: var(--text-primary);
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 14px 16px;
    background: var(--card-bg);
    border: 2px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 15px;
    font-family: inherit;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-blue);
    background: var(--darker-bg);
}

.form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.contact-info-container {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.contact-info-card {
    background: var(--card-bg);
    border: 2px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    transition: all 0.3s ease;
}

.contact-info-card:hover {
    border-color: var(--primary-blue);
    transform: translateY(-3px);
}

.contact-icon {
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

.contact-info-card h3 {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 8px;
}

.contact-info-card p {
    font-size: 14px;
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 12px;
}

.contact-link {
    color: var(--primary-blue);
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
    transition: color 0.3s ease;
}

.contact-link:hover {
    color: var(--text-primary);
}

@media (max-width: 992px) {
    .contact-wrapper {
        grid-template-columns: 1fr;
        gap: 40px;
    }
}
</style>

<?php include 'components/footer.php'; ?>
