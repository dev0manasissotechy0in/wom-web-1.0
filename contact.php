<?php require_once 'includes/header.php'; ?>

<?php
$settings = getSiteSettings($db);
?>

<style>
/* Contact Hero Section */
.contact-hero {
    background: linear-gradient(135deg, #1a1a1a 0%, #000 100%);
    color: white;
    padding: 100px 0 60px;
    text-align: center;
}

.contact-hero h1 {
    font-size: 3rem;
    margin-bottom: 15px;
    font-weight: 700;
}

.contact-hero p {
    font-size: 1.2rem;
    opacity: 0.9;
}

/* Contact Section */
.contact-section {
    padding: 80px 0;
    background: #f8f9fa;
}

.contact-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    max-width: 1200px;
    margin: 0 auto;
}

/* Contact Info */
.contact-info h2 {
    font-size: 2rem;
    margin-bottom: 20px;
    font-weight: 700;
}

.contact-info p {
    color: #666;
    margin-bottom: 30px;
    line-height: 1.6;
}

.contact-details {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    padding: 20px;
    background: #f8f8f8;
    border-radius: 10px;
    border: 2px solid #f0f0f0;
    transition: all 0.3s;
}

.contact-item:hover {
    border-color: #000;
    transform: translateX(5px);
}

.contact-icon {
    width: 50px;
    height: 50px;
    background: #000;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.contact-item-content h3 {
    font-size: 1.2rem;
    margin-bottom: 8px;
    font-weight: 600;
    color: #000;
}

.contact-item-content p {
    margin: 0;
    color: #666;
}

.contact-item-content a {
    color: #000;
    text-decoration: none;
    font-weight: 600;
}
    transition: opacity 0.3s;
}

.contact-item-content a:hover {
    opacity
    text-decoration: underline;
}

/* Contact Form */
.contact-form-wrapper {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
}

.contact-form-wrapper h2 {
    font-size: 2rem;
    margin-bottom: 30px;
    font-weight: 700;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.form-group label .required {
    color: #e74c3c;
}

.form-control {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 15px;
    font-family: inherit;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #000;
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
}

textarea.form-control {
    min-height: 150px;
    resize: vertical;
}

.btn-submit {
    width: 100%;
    padding: 16px;
    background: #000;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-submit:hover {
    background: #333;
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
}

.btn-submit:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
}

.social-links {
    display: flex
;
    gap: 15px;
    margin-top: 30px;
}

.social-link {
    width: 45px;
    height: 45px;
    background: #000;
    color: white;
    display: flex
;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    text-decoration: none;
    font-size: 1.2rem;
    transition: all 0.3s;
}

/* Alert Messages */
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: none;
}

.alert.show {
    display: block;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Responsive */
@media (max-width: 968px) {
    .contact-container {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .contact-hero {
        padding: 60px 0 40px;
    }
    
    .contact-hero h1 {
        font-size: 2rem;
    }
    
    .contact-form-wrapper {
        padding: 30px 20px;
    }
}

.map-section {
    margin-top: 80px;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.map-section iframe {
    width: 100%;
    height: 450px;
    border: none;
    display: block;
}
</style>

<!-- Hero Section -->
<section class="contact-hero">
    <div class="container">
        <h1>Get In Touch</h1>
        <p>We'd love to hear from you. Let's start a conversation.</p>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section">
    <div class="container">
        <div class="contact-container">
            <!-- Contact Info -->
            <div class="contact-info">
                <h2>Contact Information</h2>
                <p>Have a question or want to work together? Fill out the form or reach out through any of the following channels.</p>
                
                <div class="contact-details">
                    <?php if (!empty($settings['contact_phone'])): ?>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-item-content">
                            <h3>Phone</h3>
                                                       <p>
                                <a href="tel:<?php echo $settings['contact_phone'] ?? '[phone number protected]'; ?>">
                                    <?php echo $settings['contact_phone'] ?? '[phone number protected]'; ?>
                                </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-item-content">
                            <h3>Email</h3>
                            <p>
                                <a href="mailto:<?php echo $settings['contact_email'] ?? '[email protected]'; ?>">
                                    <?php echo $settings['contact_email'] ?? '[email protected]'; ?>
                                </a>
                            </p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-item-content">
                            <h3>Location</h3>
                            <p><?php echo $settings['address'] ?? 'Your Address Here'; ?></p>
                        </div>
                    </div>
                    
                    <!--<div class="contact-item">-->
                    <!--    <div class="contact-icon">-->
                    <!--        <i class="fas fa-clock"></i>-->
                    <!--    </div>-->
                    <!--    <div class="contact-item-content">-->
                    <!--        <h3>Business Hours</h3>-->
                    <!--        <p>Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday: 10:00 AM - 4:00 PM</p>-->
                    <!--    </div>-->
                    <!--</div>-->
                    
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div class="contact-item-content">
                            <h3>Connect on WhatsApp</h3>
                            <button class="btn-submit" onclick="window.open('https://wa.me/918269611955', '_blank');" style="width: auto; padding: 10px 20px;">Chat Now</button>
                        </div>
                    </div>
                </div>
                
                <div class="social-links">
                    <a href="#" class="social-link" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-link" title="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            
            
            
            <!-- Contact Form -->
            <div class="contact-form-wrapper">
                <h2>Send Us a Message</h2>
                
                <!-- Alert Messages -->
                <div id="formAlert" class="alert"></div>
                
                <form id="contactForm" method="POST">
                    <div class="form-group">
                        <label for="name">Full Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required placeholder="Enter your full name">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" required placeholder="Enter your email address">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="Enter your phone number">
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject <span class="required">*</span></label>
                        <input type="text" id="subject" name="subject" class="form-control" required placeholder="How can we help you?">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message <span class="required">*</span></label>
                        <textarea id="message" name="message" class="form-control" required placeholder="Tell us more about your project or inquiry..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <span class="btn-text">Send Message</span>
                        <span class="btn-loading" style="display:none;">
                            <i class="fas fa-spinner fa-spin"></i> Sending...
                        </span>
                    </button>
                </form>
            </div>
        </div>
                        <!-- Map Section -->
        <div class="map-section">
            
            <iframe src="https://maps.google.com/maps?width=600&amp;height=400&amp;hl=en&amp;q=Ground floor, Cinnabar Hills, Embassy Golf Links Business Park, Challaghatta, Bengaluru, Karnataka 560071&amp;t=&amp;z=17&amp;ie=UTF8&amp;iwloc=B&amp;output=embed" loading="lazy"></iframe>
        </div>

    </div>
</section>



<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    
    if (!form) {
        console.error('Contact form not found');
        return;
    }
    
    const submitBtn = document.getElementById('submitBtn');
    const formAlert = document.getElementById('formAlert');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        console.log('=== FORM SUBMISSION STARTED ===');
        
        // Get form elements
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const subjectInput = document.getElementById('subject');
        const messageInput = document.getElementById('message');
        
        // Validate
        if (!nameInput || !nameInput.value.trim()) {
            showAlert('Please enter your name', 'error');
            return;
        }
        
        if (!emailInput || !emailInput.value.trim()) {
            showAlert('Please enter your email', 'error');
            return;
        }
        
        if (!subjectInput || !subjectInput.value.trim()) {
            showAlert('Please enter a subject', 'error');
            return;
        }
        
        if (!messageInput || !messageInput.value.trim()) {
            showAlert('Please enter a message', 'error');
            return;
        }
        
        // Disable button
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
        }
        
        // Create form data
        const formData = new FormData();
        formData.append('name', nameInput.value.trim());
        formData.append('email', emailInput.value.trim());
        formData.append('phone', document.getElementById('phone') ? document.getElementById('phone').value.trim() : '');
        formData.append('subject', subjectInput.value.trim());
        formData.append('message', messageInput.value.trim());
        
        console.log('Submitting to: /contact-submit.php');
        
        // Use XMLHttpRequest for better compatibility
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/contact-submit.php', true);
        
        xhr.onload = function() {
            console.log('Response status:', xhr.status);
            console.log('Response text:', xhr.responseText);
            
            if (xhr.status === 200) {
                try {
                    const result = JSON.parse(xhr.responseText);
                    console.log('Parsed result:', result);
                    
                    if (result.success) {
                        showAlert(result.message, 'success');
                        form.reset();
                    } else {
                        showAlert(result.message, 'error');
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    showAlert('Server returned invalid response. Please try again.', 'error');
                }
            } else {
                showAlert('Server error (' + xhr.status + '). Please try again.', 'error');
            }
            
            // Re-enable button
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Message';
            }
        };
        
        xhr.onerror = function() {
            console.error('Network error');
            showAlert('Network error. Please check your connection.', 'error');
            
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Message';
            }
        };
        
        xhr.send(formData);
    });
    
    function showAlert(message, type) {
        if (!formAlert) return;
        
        formAlert.textContent = message;
        formAlert.className = 'alert alert-' + type + ' show';
        formAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Auto-hide success message after 5 seconds
        if (type === 'success') {
            setTimeout(function() {
                formAlert.classList.remove('show');
            }, 5000);
        }
    }
});
</script>



<?php require_once 'includes/footer.php'; ?>
