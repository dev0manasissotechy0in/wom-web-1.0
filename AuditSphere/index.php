<?php
require_once 'config.php';

// Fetch site settings
$settings_query = mysqli_query($conn, "SELECT * FROM site_settings LIMIT 1");
$settings = $settings_query ? mysqli_fetch_assoc($settings_query) : [];
$meta_title = isset($settings['meta_title']) ? $settings['meta_title'] : 'AuditSphere - Smart SEO Audit Platform';
$meta_description = isset($settings['meta_description']) ? $settings['meta_description'] : 'Advanced SEO auditing platform for modern websites and SaaS products.';
$meta_keywords = isset($settings['meta_keywords']) ? $settings['meta_keywords'] : 'seo audit, website analysis, auditsphere';

// Fetch features from admin database
$features_result = mysqli_query($conn, "SELECT * FROM features ORDER BY display_order ASC");

// Fetch testimonials from admin database
$testimonials_result = mysqli_query($conn, "SELECT * FROM testimonials ORDER BY created_at DESC LIMIT 6");

// Fetch gallery items from admin database
$gallery_result = mysqli_query($conn, "SELECT * FROM gallery ORDER BY created_at DESC LIMIT 9");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">
    <title><?php echo htmlspecialchars($meta_title); ?></title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-content">
                <a href="#" class="logo">
                    <i class="fas fa-chart-line"></i>
                    <span>AuditSphere</span>
                </a>
                <ul class="nav-menu">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#demos">Demos</a></li>
                    <li><a href="#testimonials">Testimonials</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                <button class="cta-btn">Get Started</button>
                <button class="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Animated Background -->
    <canvas id="animated-background"></canvas>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-background">
            <div class="gradient-orb orb-1"></div>
            <div class="gradient-orb orb-2"></div>
            <div class="gradient-orb orb-3"></div>
            <div class="animated-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
                <div class="shape shape-4"></div>
                <div class="shape shape-5"></div>
            </div>
        </div>
        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fas fa-sparkles"></i>
                    <span>Smart SEO Analysis Platform</span>
                </div>
                <h1 class="hero-title">
                    Elevate Your Website's
                    <span class="gradient-text">Performance</span>
                </h1>
                <p class="hero-subtitle">
                    Comprehensive SEO audits, actionable insights, and real-time monitoring
                    to help your website rank higher and perform better.
                </p>
                <div class="hero-cta">
                    <button class="btn btn-primary" id="openSoftware">
                        <span>Launch AuditSphere</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    <button class="btn btn-secondary">
                        <i class="fas fa-play"></i>
                        <span>Watch Demo</span>
                    </button>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-number">50K+</div>
                        <div class="stat-label">Audits Performed</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Accuracy Rate</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Monitoring</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Features</span>
                <h2 class="section-title">Everything You Need to <span class="gradient-text">Succeed</span></h2>
                <p class="section-subtitle">Powerful tools designed to give you complete control over your website's SEO performance</p>
            </div>
            
            <div class="features-grid">
                <?php 
                $icon_classes = ['fa-chart-line', 'fa-search', 'fa-gauge-high', 'fa-shield-halved', 'fa-mobile-screen', 'fa-clock'];
                $colors = ['#667eea', '#f093fb', '#4facfe', '#43e97b', '#fa709a', '#feca57'];
                $index = 0;
                
                if ($features_result && mysqli_num_rows($features_result) > 0):
                    while($feature = mysqli_fetch_assoc($features_result)): 
                        $color = $colors[$index % count($colors)];
                        $icon = $icon_classes[$index % count($icon_classes)];
                ?>
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, <?php echo $color; ?>22, <?php echo $color; ?>11);">
                        <i class="fas <?php echo $icon; ?>" style="color: <?php echo $color; ?>;"></i>
                    </div>
                    <h3 class="feature-title"><?php echo htmlspecialchars($feature['title']); ?></h3>
                    <p class="feature-description"><?php echo htmlspecialchars($feature['description']); ?></p>
                    <a href="#" class="feature-link">
                        <span>Learn more</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <?php 
                    $index++;
                    endwhile;
                else:
                    // Default features if none in database
                    $default_features = [
                        ['title' => 'SEO Analysis', 'desc' => 'Deep dive into your website\'s SEO performance with detailed reports'],
                        ['title' => 'Keyword Tracking', 'desc' => 'Monitor your keyword rankings across all major search engines'],
                        ['title' => 'Performance Metrics', 'desc' => 'Track page speed, Core Web Vitals, and loading performance'],
                        ['title' => 'Security Audit', 'desc' => 'Identify security vulnerabilities and get recommendations'],
                        ['title' => 'Mobile Optimization', 'desc' => 'Ensure your site performs perfectly on all mobile devices'],
                        ['title' => 'Real-time Monitoring', 'desc' => '24/7 monitoring with instant alerts for any issues']
                    ];
                    foreach($default_features as $idx => $feat):
                        $color = $colors[$idx % count($colors)];
                        $icon = $icon_classes[$idx % count($icon_classes)];
                ?>
                <div class="feature-card">
                    <div class="feature-icon" style="background: linear-gradient(135deg, <?php echo $color; ?>22, <?php echo $color; ?>11);">
                        <i class="fas <?php echo $icon; ?>" style="color: <?php echo $color; ?>;"></i>
                    </div>
                    <h3 class="feature-title"><?php echo $feat['title']; ?></h3>
                    <p class="feature-description"><?php echo $feat['desc']; ?></p>
                    <a href="#" class="feature-link">
                        <span>Learn more</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <?php 
                    endforeach;
                endif;
                ?>
            </div>
        </div>
    </section>

    <!-- Demos Gallery Section -->
    <section id="demos" class="demos">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Gallery</span>
                <h2 class="section-title">See AuditSphere in <span class="gradient-text">Action</span></h2>
                <p class="section-subtitle">Explore screenshots and videos of our platform's powerful features</p>
            </div>
            
            <div class="gallery-grid">
                <?php 
                if ($gallery_result && mysqli_num_rows($gallery_result) > 0):
                    while($item = mysqli_fetch_assoc($gallery_result)): 
                ?>
                <div class="gallery-item">
                    <div class="gallery-image">
                        <?php if($item['file_type'] == 'image'): ?>
                            <img src="<?php echo htmlspecialchars($item['file_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                        <?php else: ?>
                            <img src="<?php echo htmlspecialchars($item['thumbnail']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <div class="play-overlay">
                                <i class="fas fa-play"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="gallery-content">
                        <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                        <?php if(!empty($item['description'])): ?>
                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                    // Placeholder gallery items
                    for($i = 1; $i <= 6; $i++):
                ?>
                <div class="gallery-item">
                    <div class="gallery-image">
                        <div class="gallery-placeholder">
                            <i class="fas fa-image"></i>
                            <span>Demo Screenshot <?php echo $i; ?></span>
                        </div>
                    </div>
                    <div class="gallery-content">
                        <h4>Feature Demo <?php echo $i; ?></h4>
                        <p>Explore this powerful feature in action</p>
                    </div>
                </div>
                <?php 
                    endfor;
                endif;
                ?>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Testimonials</span>
                <h2 class="section-title">Loved by <span class="gradient-text">Thousands</span></h2>
                <p class="section-subtitle">See what our customers have to say about AuditSphere</p>
            </div>
            
            <div class="testimonials-grid" id="testimonialsGrid">
                <?php 
                if ($testimonials_result && mysqli_num_rows($testimonials_result) > 0):
                    while($testimonial = mysqli_fetch_assoc($testimonials_result)): 
                ?>
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <?php 
                        $rating = isset($testimonial['rating']) ? intval($testimonial['rating']) : 5;
                        for($i = 0; $i < $rating; $i++): 
                        ?>
                        <i class="fas fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="testimonial-text">"<?php echo isset($testimonial['feedback']) ? htmlspecialchars($testimonial['feedback']) : 'Great service!'; ?>"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <?php if(isset($testimonial['user_image']) && !empty($testimonial['user_image'])): ?>
                            <img src="<?php echo htmlspecialchars($testimonial['user_image']); ?>" alt="<?php echo isset($testimonial['user_name']) ? htmlspecialchars($testimonial['user_name']) : 'User'; ?>">
                            <?php else: ?>
                            <div class="avatar-placeholder">
                                <?php echo isset($testimonial['user_name']) ? strtoupper(substr($testimonial['user_name'], 0, 1)) : 'U'; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="author-info">
                            <h5><?php echo isset($testimonial['user_name']) ? htmlspecialchars($testimonial['user_name']) : 'Anonymous'; ?></h5>
                            <p><?php echo isset($testimonial['user_role']) ? htmlspecialchars($testimonial['user_role']) : 'Customer'; ?></p>
                        </div>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                    // Default testimonials
                    $default_testimonials = [
                        ['name' => 'Sarah Johnson', 'position' => 'Marketing Director', 'text' => 'AuditSphere transformed our SEO strategy. The insights are invaluable!'],
                        ['name' => 'Michael Chen', 'position' => 'CEO, TechStart', 'text' => 'Best SEO audit tool we\'ve ever used. Highly recommended!'],
                        ['name' => 'Emily Rodriguez', 'position' => 'SEO Specialist', 'text' => 'The real-time monitoring feature has saved us countless hours.']
                    ];
                    foreach($default_testimonials as $test):
                ?>
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"<?php echo $test['text']; ?>"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <div class="avatar-placeholder">
                                <?php echo strtoupper(substr($test['name'], 0, 1)); ?>
                            </div>
                        </div>
                        <div class="author-info">
                            <h5><?php echo $test['name']; ?></h5>
                            <p><?php echo $test['position']; ?></p>
                        </div>
                    </div>
                </div>
                <?php 
                    endforeach;
                endif;
                ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Transform Your SEO?</h2>
                <p>Join thousands of businesses already using AuditSphere</p>
                <button class="btn btn-primary btn-large">
                    <span>Get Started Now</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="logo">
                        <i class="fas fa-chart-line"></i>
                        <span>AuditSphere</span>
                    </div>
                    <p>Smart SEO audits for modern SaaS and websites.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <div class="footer-column">
                        <h4>Product</h4>
                        <ul>
                            <li><a href="#features">Features</a></li>
                            <li><a href="#demos">Demos</a></li>
                            <li><a href="#">Pricing</a></li>
                            <li><a href="#">Updates</a></li>
                        </ul>
                    </div>
                    <div class="footer-column">
                        <h4>Company</h4>
                        <ul>
                            <li><a href="#">About</a></li>
                            <li><a href="#">Blog</a></li>
                            <li><a href="#">Careers</a></li>
                            <li><a href="#">Contact</a></li>
                        </ul>
                    </div>
                    <div class="footer-column">
                        <h4>Legal</h4>
                        <ul>
                            <li><a href="#">Privacy</a></li>
                            <li><a href="#">Terms</a></li>
                            <li><a href="#">Cookie Policy</a></li>
                            <li><a href="#">Disclaimer</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 AuditSphere by Wall of Marketing. All rights reserved.</p>
                <p>Made in India with <i class="fas fa-heart"></i></p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/js/script.js"></script>
</body>
</html>
