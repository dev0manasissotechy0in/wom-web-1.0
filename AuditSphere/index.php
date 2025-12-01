
<?php
include 'db_config.php';
$settings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM site_settings LIMIT 1"));
$meta_title = $settings['meta_title'] ?: 'AuditSphere by Wall of Marketing';
$meta_description = $settings['meta_description'] ?: 'Smart SEO audits for modern SaaS and websites.';
$meta_keywords = $settings['meta_keywords'] ?: 'seo audit, auditsphere, wall of marketing';
$meta_robots = $settings['meta_robots'] ?: 'index,follow';
$canonical_url = $settings['canonical_url'] ?: 'https://auditsphere.wallofmarketing.co/';

// Fetch testimonials
$testimonials = $conn->query("SELECT * FROM testimonials ORDER BY created_at DESC");

// Fetch gallery items
$gallery_items = $conn->query("SELECT * FROM gallery ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo htmlspecialchars($meta_title); ?></title>
<meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">
<meta name="robots" content="<?php echo htmlspecialchars($meta_robots); ?>">
<link rel="canonical" href="<?php echo htmlspecialchars($canonical_url); ?>">

<?php
// Google Tag Manager (head) if GTM is set
if (!empty($settings['gtm_container_id'])): ?>
<script>
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?php echo $settings['gtm_container_id']; ?>');
</script>
<?php endif; ?>

<?php
// Google Analytics gtag.js (if used without GTM)
if (!empty($settings['ga_measurement_id']) && empty($settings['gtm_container_id'])): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $settings['ga_measurement_id']; ?>"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '<?php echo $settings['ga_measurement_id']; ?>');
</script>
<?php endif; ?>

<?php
// Meta Pixel
if (!empty($settings['meta_pixel_id'])): ?>
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src='https://connect.facebook.net/en_US/fbevents.js';
s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script');
fbq('init', '<?php echo $settings['meta_pixel_id']; ?>');
fbq('track', 'PageView');
</script>
<noscript>
    <img alt="fb" height="1" width="1" style="display:none"
         src="https://www.facebook.com/tr?id=<?php echo $settings['meta_pixel_id']; ?>&ev=PageView&noscript=1"/>
</noscript>
<?php endif; ?>

<?php
// Custom head HTML
if (!empty($settings['custom_head'])) {
    echo $settings['custom_head'];
}
?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animated Landing Page</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="footer-css.css">
    <!-- <link rel="stylesheet" href="scroll.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="features/features_styles.css">
</head>
<body>
    <?php
// Google Tag Manager (body noscript)
if (!empty($settings['gtm_container_id'])): ?>
<noscript>
<iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $settings['gtm_container_id']; ?>"
        height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>
<?php endif; ?>

<?php
// Custom body top
if (!empty($settings['custom_body_top'])) {
    echo $settings['custom_body_top'];
}
?>

    <!-- Atmospheric Background -->
    <div id="particles-container"></div>
    <div id="floating-text-container"></div>

    <!-- Main Content -->
    <main class="content">
        <!-- Hero Section with Animated Button -->
        <!-- <section class="hero">
            <h1 class="hero-title">Welcome to Our Platform</h1>
            <button class="animated-btn" id="openSoftware">
                <span>Launch Software</span>
                <div class="btn-particles"></div>
            </button>
        </section> -->

        <!-- Update Hero Section 3D-->
<section class="hero perspective-wrapper">
    <h1 class="hero-title float-3d">Welcome to Our Platform</h1>
    <button class="animated-btn tilt-3d" id="openSoftware">
        <span class="tilt-3d-inner">Launch Software</span>
    </button>
</section>

<!-- Add 3D Background Text -->
<div class="text-3d-scroll">AUDITSPHERE</div>

<!-- Features Section -->
<!-- <section class="features-section">
    <div class="container">
        <h2 class="section-title animation fade-in-up">Core Features</h2>
        <p class="section-subtitle animation fade-in-up">Everything you need to succeed</p>
        
        <div class="features-grid">
            <?php 
            $features_query = mysqli_query($conn, "SELECT * FROM features WHERE is_active = 1 ORDER BY display_order ASC");
            $delay = 0;
            while($feature = mysqli_fetch_assoc($features_query)): 
            ?>
                <div class="feature-card animation" style="animation-delay: <?php echo $delay; ?>s">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon" style="color: <?php echo $feature['icon_color']; ?>">
                            <?php echo $feature['icon']; ?>
                        </div>
                        <div class="feature-icon-bg" style="background: <?php echo $feature['icon_color']; ?>20"></div>
                    </div>
                    <h3 class="feature-title"><?php echo htmlspecialchars($feature['title']); ?></h3>
                    <p class="feature-description"><?php echo htmlspecialchars($feature['description']); ?></p>
                </div>
            <?php 
            $delay += 0.1;
            endwhile; 
            ?>
        </div>
    </div>
</section> -->
<?php // include 'Split-Screen-features.php'; ?>

<?php include 'Tabbed_Features_Section.php'; ?>

<?php // include 'features/Bento-Grid-features.php'; ?>

        <!-- Gallery Section -->
<div class="gallery-item card-3d tilt-3d">
            <section class="gallery-section">
            <h2>AuditSpeher Demos</h2>
            <div class="gallery-grid">
                <?php while($item = $gallery_items->fetch_assoc()): ?>
                    <div class="gallery-item" data-type="<?php echo $item['file_type']; ?>">
                        <?php if($item['file_type'] == 'image'): ?>
                            <img src="<?php echo $item['file_path']; ?>" 
                                 alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                 class="gallery-thumb">
                        <?php else: ?>
                            <img src="<?php echo $item['thumbnail']; ?>" 
                                 alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                 class="gallery-thumb">
                            <div class="play-icon">▶</div>
                        <?php endif; ?>
                        <div class="gallery-overlay">
                            <p><?php echo htmlspecialchars($item['title']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
</div>


        <!-- Testimonials Section -->
<div class="testimonial-card card-3d tilt-3d">
            <section class="testimonials-section">
            <h2>What Our Users Say</h2>
            <div class="testimonials-carousel">
                <div class="testimonials-track">
                    <?php while($testimonial = $testimonials->fetch_assoc()): ?>
                        <div class="testimonial-card">
                            <img src="<?php echo $testimonial['user_image']; ?>" 
                                 alt="<?php echo htmlspecialchars($testimonial['user_name']); ?>" 
                                 class="user-avatar">
                            <div class="testimonial-content">
                                <p class="feedback">"<?php echo htmlspecialchars($testimonial['feedback']); ?>"</p>
                                <h4 class="user-name"><?php echo htmlspecialchars($testimonial['user_name']); ?></h4>
                                <p class="user-role"><?php echo htmlspecialchars($testimonial['user_role']); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
</div>
    </main>

    <!-- Modal for Gallery Fullscreen -->
    <div id="gallery-modal" class="modal">
        <span class="close">&times;</span>
        <div class="modal-content" id="modal-content"></div>
    </div>

    <script src="script.js"></script>



    <?php
// Fetch tracking + SEO + footer config (single row table)
$settings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM site_settings LIMIT 1"));
?>
<footer class="site-footer">
    <div class="footer-top">
        <div class="footer-left">
            <div class="footer-logo">
                <img src="assets/img/wom-logo-light.svg" alt="Wall of Marketing">
                <span>Wall of Marketing</span>
            </div>
            <p class="footer-tagline">Building powerful SaaS experiences with AuditSphere.</p>
        </div>

        <div class="footer-middle">
            <h4>Legal</h4>
            <ul>
                <li><a href="/privacy-policy.php">Privacy Policy</a></li>
                <li><a href="/terms-of-service.php">Terms of Service</a></li>
                <li><a href="/cookie-policy.php">Cookie Policy</a></li>
            </ul>
        </div>

        <div class="footer-right">
            <h4>Connect</h4>
            <div class="footer-social">
                <a href="https://twitter.com/yourhandle" target="_blank" aria-label="Twitter" class="social-btn twitter">
                    <i class="fa fa-twitter"></i>
                </a>
                <a href="https://linkedin.com/company/yourcompany" target="_blank" aria-label="LinkedIn" class="social-btn linkedin">
                    <i class="fa fa-linkedin"></i>
                </a>
                <a href="https://instagram.com/yourhandle" target="_blank" aria-label="Instagram" class="social-btn instagram">
                    <i class="fa fa-instagram"></i>
                </a>
                <a href="https://youtube.com/@yourchannel" target="_blank" aria-label="YouTube" class="social-btn youtube">
                    <i class="fa fa-youtube-play"></i>
                </a>
            </div>

            <button class="footer-contact-btn" onclick="document.getElementById('contactSection').scrollIntoView({behavior:'smooth'});">
                Contact Us
            </button>
        </div>
    </div>

    <div class="footer-bottom">
        <span>© <?php echo date('Y'); ?> Wall of Marketing. All rights reserved.</span>
        <span>Made in India with ♥</span>
    </div>
</footer>
<?php
if (!empty($settings['custom_body_bottom'])) {
    echo $settings['custom_body_bottom'];
}
?>

</body>
</html>
