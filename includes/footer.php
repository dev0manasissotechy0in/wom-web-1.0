<?php
$settings = getSiteSettings($db);
?>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <!-- Footer Top -->
        <div class="footer-top">
            <div class="footer-grid">
                <!-- Company Info -->
                <div class="footer-col">
                    <div class="footer-logo">
                        <img src="/assets/images/Logo.png"
                             alt="<?php echo SITE_NAME; ?>"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <h3 style="display:none; color:white; font-size:24px;"><?php echo SITE_NAME; ?></h3>
                    </div>
                    <p class="footer-description">
                        Leading digital marketing agency providing comprehensive solutions to help businesses grow online.
                    </p>
                    <div class="footer-contact">
                        <?php if (!empty($settings['contact_phone'])): ?>
    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($settings['contact_phone']); ?></p>
<?php endif; ?>
                        <!--<p><i class="fas fa-phone"></i> <?php // echo $settings['contact_phone'] ?? '+91 1234567890'; ?></p>-->
                        <p><i class="fas fa-envelope"></i> <?php echo $settings['contact_email'] ?? '[email protected]'; ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo $settings['address'] ?? 'Your Address Here'; ?></p>
                    </div>
                </div>
                
                <!-- Important Links -->
                <div class="footer-col">
                    <h3 class="footer-title">Important Links</h3>
                    <ul class="footer-links">
                        <li><a href="/"><i class="fas fa-chevron-right"></i> Home</a></li>
                        <li><a href="/about.php"><i class="fas fa-chevron-right"></i> About Us</a></li>
                        <li><a href="/services.php"><i class="fas fa-chevron-right"></i> Services</a></li>
                        <li><a href="/blogs.php"><i class="fas fa-chevron-right"></i> Blog</a></li>
                        <li><a href="/contact.php"><i class="fas fa-chevron-right"></i> Contact</a></li>
                        <!--<li><a href="/sitemap.php"><i class="fas fa-chevron-right"></i> Sitemap</a></li>-->
                    </ul>
                </div>
                
                <!-- Legal Links -->
                <div class="footer-col">
                    <h3 class="footer-title">Legal</h3>
                    <ul class="footer-links">
                        <li><a href="/privacy-policy.php"><i class="fas fa-chevron-right"></i> Privacy Policy</a></li>
                        <li><a href="/terms-conditions.php"><i class="fas fa-chevron-right"></i> Terms & Conditions</a></li>
                        <li><a href="/cookie-policy.php"><i class="fas fa-chevron-right"></i> Cookie Policy</a></li>
                        <li><a href="/refund-policy.php"><i class="fas fa-chevron-right"></i> Refund Policy</a></li>
                        <li><a href="/disclaimer.php"><i class="fas fa-chevron-right"></i> Disclaimer</a></li>
                    </ul>
                </div>
                
                <!-- Newsletter -->
                <div class="footer-col">
                    <h3 class="footer-title">Newsletter</h3>
                    <p class="footer-newsletter-text">Subscribe to get latest updates and insights</p>
                    <form id="newsletter-form" class="footer-newsletter-form">
                        <input type="email" name="email" placeholder="Your Email Address" required>
                        <button type="submit">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                    <div id="newsletter-response"></div>
                    
                    <!-- Social Media Icons -->
                    <div class="social-media-section">
                        <h4>Follow Us</h4>
                        <div class="social-icons">
                            <?php if(!empty($settings['facebook_url'])): ?>
                                <a href="<?php echo $settings['facebook_url']; ?>" target="_blank" rel="noopener noreferrer" class="social-icon facebook" title="Facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if(!empty($settings['instagram_url'])): ?>
                                <a href="<?php echo $settings['instagram_url']; ?>" target="_blank" rel="noopener noreferrer" class="social-icon instagram" title="Instagram">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if(!empty($settings['linkedin_url'])): ?>
                                <a href="<?php echo $settings['linkedin_url']; ?>" target="_blank" rel="noopener noreferrer" class="social-icon linkedin" title="LinkedIn">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if(!empty($settings['twitter_url'])): ?>
                                <a href="<?php echo $settings['twitter_url']; ?>" target="_blank" rel="noopener noreferrer" class="social-icon twitter" title="Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            <?php endif; ?>
                            
                            <a href="https://www.youtube.com" target="_blank" rel="noopener noreferrer" class="social-icon youtube" title="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                            
                            <a href="https://wa.me/918296911955" target="_blank" rel="noopener noreferrer" class="social-icon whatsapp" title="WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p class="copyright">
                    &copy; <span id="current-year"></span>. All Rights Reserved.
                </p>
                
                <!--<p class="footer-time">-->
                <!--    <i class="far fa-clock"></i> Current Time: <span id="current-time"></span>-->
                <!--</p>-->
                
                <p class="footer-credits">
                    Designed & Developed with <i class="fas fa-heart"></i> by <a herf="www.neuvantechnology.com">Neuvan Technology</a>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Scroll to Top Button -->
<button id="scrollToTop" class="scroll-to-top" style="display: none;">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- JavaScript -->
<script src="/assets/js/main.js"></script>
<script src="/assets/js/cookie-consent.js"></script>
<script src="/assets/js/tracking.js"></script>

<script>
// Display Current Date and Time
function updateDateTime() {
    const now = new Date();
    const options = { 
        weekday: 'short', 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    };
    
    document.getElementById('current-year').textContent = now.getFullYear();
    const timeElement = document.getElementById('current-time');
    if(timeElement) {
        timeElement.textContent = now.toLocaleString('en-IN', options);
    }
}

updateDateTime();
setInterval(updateDateTime, 1000);

// Newsletter Form Submission
const newsletterForm = document.getElementById('newsletter-form');
if(newsletterForm) {
    newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const responseDiv = document.getElementById('newsletter-response');
        
        // Show loading
        responseDiv.innerHTML = '<p style="color: rgba(255,255,255,0.8); font-size: 14px; margin-top: 10px;">Subscribing...</p>';
        
        fetch('/api/newsletter-subscribe.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                responseDiv.innerHTML = '<p style="color: #4caf50; font-size: 14px; margin-top: 10px;"><i class="fas fa-check-circle"></i> ' + data.message + '</p>';
                this.reset();
            } else {
                responseDiv.innerHTML = '<p style="color: #f44336; font-size: 14px; margin-top: 10px;"><i class="fas fa-exclamation-circle"></i> ' + data.message + '</p>';
            }
            
            // Clear message after 5 seconds
            setTimeout(() => {
                responseDiv.innerHTML = '';
            }, 5000);
        })
        .catch(error => {
            responseDiv.innerHTML = '<p style="color: #f44336; font-size: 14px; margin-top: 10px;"><i class="fas fa-exclamation-circle"></i> An error occurred. Please try again.</p>';
        });
    });
}

// Scroll to Top Button
const scrollToTopBtn = document.getElementById('scrollToTop');

window.addEventListener('scroll', function() {
    if (window.pageYOffset > 300) {
        scrollToTopBtn.style.display = 'block';
    } else {
        scrollToTopBtn.style.display = 'none';
    }
});

scrollToTopBtn.addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

// Smooth Scroll for Anchor Links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
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

// Add animation on scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observe all sections
document.querySelectorAll('section').forEach(section => {
    observer.observe(section);
});
</script>

<style>
/* Footer Specific Styles */
.footer {
    background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%);
    color: white;
    padding-top: 60px;
    margin-top: 0;
}

.footer-top {
    padding-bottom: 40px;
}

.footer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 40px;
}

.footer-col {
    display: flex;
    flex-direction: column;
}

.footer-logo {
    margin-bottom: 20px;
}

.footer-logo img {
    height: 50px;
    width: auto;
    filter: brightness(0) invert(1);
}

.footer-description {
    color: rgba(255,255,255,0.8);
    line-height: 1.7;
    margin-bottom: 20px;
    font-size: 14px;
}

.footer-contact p {
    color: rgba(255,255,255,0.9);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
}

.footer-contact i {
    color: rgba(255,255,255,0.9);
    font-size: 16px;
    width: 20px;
}

.footer-title {
    font-size: 20px;
    margin-bottom: 25px;
    position: relative;
    padding-bottom: 10px;
    font-weight: 600;
    color: white;
}

.footer-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 3px;
    background: white;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: rgba(255,255,255,0.8);
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    font-size: 14px;
}

.footer-links a i {
    font-size: 10px;
    transition: transform 0.3s;
}

.footer-links a:hover {
    color: white;
    padding-left: 5px;
}

.footer-links a:hover i {
    transform: translateX(3px);
}

.footer-newsletter-text {
    color: rgba(255,255,255,0.8);
    margin-bottom: 15px;
    font-size: 14px;
}

.footer-newsletter-form {
    display: flex;
    margin-bottom: 25px;
}

.footer-newsletter-form input {
    flex: 1;
    padding: 12px 15px;
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 5px 0 0 5px;
    outline: none;
    background: rgba(255,255,255,0.1);
    color: white;
    font-size: 14px;
    transition: all 0.3s;
}

.footer-newsletter-form input::placeholder {
    color: rgba(255,255,255,0.5);
}

.footer-newsletter-form input:focus {
    background: rgba(255,255,255,0.15);
    border-color: rgba(255,255,255,0.4);
}

.footer-newsletter-form button {
    background: white;
    color: black;
    border: none;
    padding: 12px 20px;
    border-radius: 0 5px 5px 0;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 16px;
}

.footer-newsletter-form button:hover {
    background: rgba(255,255,255,0.9);
    transform: translateY(-2px);
}

#newsletter-response {
    margin-top: 10px;
    font-size: 14px;
}

.social-media-section {
    margin-top: 25px;
}

.social-media-section h4 {
    font-size: 16px;
    margin-bottom: 15px;
    font-weight: 600;
    color: white;
}

.social-icons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.social-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: white;
    background: rgba(255,255,255,0.1);
    border: 2px solid rgba(255,255,255,0.3);
    transition: all 0.3s;
    text-decoration: none;
    font-size: 16px;
}

.social-icon:hover {
    transform: translateY(-3px);
    background: white;
    color: black;
    border-color: white;
}

.footer-bottom {
    border-top: 1px solid rgba(255,255,255,0.1);
    padding: 30px 0;
}

.footer-bottom-content {
    text-align: center;
}

.footer-bottom-content p {
    color: rgba(255,255,255,0.8);
    margin-bottom: 10px;
    font-size: 14px;
}

.copyright {
    font-size: 14px;
}

.copyright strong {
    color: white;
    font-weight: 600;
}

.footer-time {
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.footer-credits {
    font-size: 14px;
}

.footer-credits i {
    color: #ff0000;
    animation: heartbeat 1.5s infinite;
}

@keyframes heartbeat {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

/* Scroll to Top Button */
.scroll-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: black;
    color: white;
    border: 2px solid white;
    border-radius: 50%;
    cursor: pointer;
    font-size: 20px;
    transition: all 0.3s;
    z-index: 999;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}

.scroll-to-top:hover {
    background: white;
    color: black;
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.4);
}

/* Responsive */
@media (max-width: 768px) {
    .footer-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .footer-bottom-content {
        text-align: center;
    }
    
    .footer-time {
        justify-content: center;
    }
    
    .scroll-to-top {
        width: 45px;
        height: 45px;
        bottom: 20px;
        right: 20px;
        font-size: 18px;
    }
}

@media (max-width: 480px) {
    .footer {
        padding-top: 40px;
    }
    
    .footer-grid {
        gap: 25px;
    }
    
    .footer-title {
        font-size: 18px;
    }
    
    .social-icon {
        width: 36px;
        height: 36px;
        font-size: 14px;
    }
}
</style>

</body>
</html>
