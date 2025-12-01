<?php
/**
 * Main Header Template - COMPLETELY FIXED
 * Properly integrates SEO, tracking, and cookie consent
 * No premature output - prevents header errors
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load configuration and database (should be done in page file, but check anyway)
if (!isset($db)) {
    require_once __DIR__ . '/../config/config.php';
}

// Set page variable if not already set
if (!isset($page)) {
    $page = basename($_SERVER['PHP_SELF'], '.php');
}

// Get site settings from database
if (!isset($site_settings) || empty($site_settings)) {
    $site_settings = [];
    try {
        $stmt = $db->query("SELECT * FROM site_settings WHERE id = 1");
        $site_settings = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Header settings error: " . $e->getMessage());
    }
}

$site_name = $site_settings['site_name'] ?? (defined('SITE_NAME') ? SITE_NAME : 'Wall of Marketing');
$site_url = $site_settings['site_url'] ?? (defined('SITE_URL') ? SITE_URL : 'https://wallofmarketing.co');
$site_url = rtrim($site_url, '/');
$site_logo = $site_settings['site_logo'] ?? '/assets/images/Logo.png';

// Load SEO meta (prepares variables but doesn't output yet)
require_once __DIR__ . '/seo-meta.php';
?>

<?php
require_once __DIR__ . '/../config/config.php';
$settings = getSiteSettings($db);
$page = basename($_SERVER['PHP_SELF'], '.php');
trackPageView($db);


// Get the base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'];


?>
<!DOCTYPE html>
<html lang="en">
<head>
    
        <?php 
    // Now output SEO meta tags
    renderSeoMeta(); 
    ?>

    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo $base_url; ?>/assets/images/favicon.png">
    <link rel="apple-touch-icon" href="<?php echo $base_url; ?>/assets/images/apple-touch-icon.jpg">
    
    <!-- CSS - Using absolute paths -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
<!-- <script src="/assets/js/main.js" defer></script> -->
    <script src="/assets/js/tracking.js" defer></script>

    <?php 
    // Load Tracking Scripts (Google Analytics, Facebook Pixel, etc.)
    require_once __DIR__ . '/tracking.php'; 
    ?>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: <?php echo $settings['theme_color'] ?? '#000000'; ?>;
            --secondary-color: #333333;
            --text-dark: #1a1a1a;
            --text-light: #666666;
            --bg-light: #f5f5f5;
            --white: #ffffff;
            --shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    
    <?php 
    // Google Tag Manager Body Code (if applicable)
    if (function_exists('getGTMBodyCode')) {
        echo getGTMBodyCode();
    }
    ?>
    
        <!-- Cookie Consent Banner -->
    <?php require_once __DIR__ . '/cookie-banner.php'; ?>
     
 <!--Cookie Consent Banner -->
<!--<div id="cookie-consent-banner" class="cookie-banner" style="display:none;">-->
<!--    <div class="cookie-container">-->
<!--        <div class="cookie-content">-->
<!--            <div class="cookie-header">-->
<!--                <i class="fas fa-cookie-bite"></i>-->
<!--                <h3>Cookie Preferences</h3>-->
<!--            </div>-->
<!--            <p>We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic. Please choose your cookie preferences:</p>-->
            
<!--            <div class="cookie-options">-->
<!--                <label class="cookie-option">-->
<!--                    <input type="checkbox" id="necessary-cookies" checked disabled>-->
<!--                    <span class="cookie-label">-->
<!--                        <strong>Necessary Cookies</strong>-->
<!--                        <small>Required for basic site functionality</small>-->
<!--                    </span>-->
<!--                </label>-->
                
<!--                <label class="cookie-option">-->
<!--                    <input type="checkbox" id="analytics-cookies">-->
<!--                    <span class="cookie-label">-->
<!--                        <strong>Analytics Cookies</strong>-->
<!--                        <small>Help us understand how visitors use our site</small>-->
<!--                    </span>-->
<!--                </label>-->
                
<!--                <label class="cookie-option">-->
<!--                    <input type="checkbox" id="marketing-cookies">-->
<!--                    <span class="cookie-label">-->
<!--                        <strong>Marketing Cookies</strong>-->
<!--                        <small>Used to deliver personalized advertisements</small>-->
<!--                    </span>-->
<!--                </label>-->
<!--            </div>-->
            
<!--            <div class="cookie-actions">-->
<!--                <button onclick="acceptAllCookies()" class="btn-accept-all">Accept All</button>-->
<!--                <button onclick="acceptSelectedCookies()" class="btn-save">Save Preferences</button>-->
<!--                <button onclick="rejectCookies()" class="btn-reject">Reject Non-Essential</button>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->



<!-- Header -->
<header class="header" id="header">
    <div class="container">
        <nav class="navbar">
            <!-- Logo -->
            <div class="logo">
                <a href="/">
                    <?php if(!empty($settings['site_logo'])): ?>
                        <img src="assets/images/Logo.png" 
                             alt="<?php echo SITE_NAME; ?>" 
                             class="logo-img"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <?php endif; ?>
                    <span style="<?php echo !empty($settings['site_logo']) ? 'display:none;' : ''; ?> font-size:24px; font-weight:700; color:var(--primary-color);">
                        <?php echo SITE_NAME; ?>
                    </span>
                </a>
            </div>
            
            <!-- Navigation Menu -->
            <div class="nav-menu" id="navMenu">
                <a href="/" class="nav-link <?php echo $page == 'index' ? 'active' : ''; ?>">Home</a>
                <!--<a href="/about.php" class="nav-link <?php echo $page == 'about' ? 'active' : ''; ?>">About</a>-->
                <a href="/services.php" class="nav-link <?php echo $page == 'services' ? 'active' : ''; ?>">Services</a>
                <a href="/blogs.php" class="nav-link <?php echo $page == 'blogs' || $page == 'blog-detailed' ? 'active' : ''; ?>">Blog</a>
                <a href="/resources.php" class="nav-link <?php echo $page == 'contact' ? 'active' : ''; ?>">Resources</a>
            </div>
            
            <!-- Consultation Button -->
            <div class="consultation-btn">
                <a href="/contact.php?type=consultation" class="btn-primary">
                    <i class="fas fa-comments"></i> Get Consultation
                </a>
            </div>
            
            <!-- Mobile Menu Toggle -->
            <div class="mobile-menu-toggle" id="mobileMenuToggle">
                <i class="fas fa-bars"></i>
            </div>
        </nav>
    </div>
</header>

<!-- Mobile Menu Overlay -->
<div class="mobile-menu-overlay" id="mobileMenuOverlay">
    <div class="mobile-menu-close" id="mobileMenuClose">
        <i class="fas fa-times"></i>
    </div>
    <div class="mobile-menu-content">
        <a href="/" class="mobile-nav-link">Home</a>
        <a href="/about.php" class="mobile-nav-link">About</a>
        <a href="/services.php" class="mobile-nav-link">Services</a>
        <a href="/blogs.php" class="mobile-nav-link">Blog</a>
        <a href="/contact.php" class="mobile-nav-link">Contact</a>
        <a href="/contact.php?type=consultation" class="mobile-nav-link consultation-mobile">
            <i class="fas fa-comments"></i> Get Consultation
        </a>
    </div>
</div>

<script>
// Mobile Menu Toggle
document.getElementById('mobileMenuToggle').addEventListener('click', function() {
    document.getElementById('mobileMenuOverlay').classList.add('active');
    document.body.style.overflow = 'hidden';
});

document.getElementById('mobileMenuClose').addEventListener('click', function() {
    document.getElementById('mobileMenuOverlay').classList.remove('active');
    document.body.style.overflow = 'auto';
});

// Close mobile menu when clicking outside
document.getElementById('mobileMenuOverlay').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
});

// Sticky Header on Scroll
window.addEventListener('scroll', function() {
    const header = document.getElementById('header');
    if (window.scrollY > 100) {
        header.classList.add('sticky');
    } else {
        header.classList.remove('sticky');
    }
});

// Cookie consent functionality is now handled by PHP-based banner in cookie-banner.php
// See: includes/cookie-banner.php for implementation
</script>
</head>
<body>
