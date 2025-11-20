<?php
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1>Transform Your Digital Presence</h1>
            <p>Leading Digital Marketing Solutions for Modern Businesses</p>
            <a href="/book-call.php" class="btn-primary">Book a Call</a>
        </div>
        
        <!-- Running Animation of 20+ Icons -->
        <div class="icon-animation-container">
            <div class="icon-track">
                <?php
                $icons = ['fa-facebook', 'fa-instagram', 'fa-google', 'fa-linkedin', 'fa-youtube', 
                          'fa-twitter', 'fa-whatsapp', 'fa-tiktok', 'fa-pinterest', 'fa-snapchat',
                          'fa-reddit', 'fa-telegram', 'fa-discord', 'fa-behance', 'fa-dribbble',
                          'fa-github', 'fa-wordpress', 'fa-shopify', 'fa-amazon', 'fa-paypal',
                          'fa-stripe', 'fa-mailchimp'];
                
                // Duplicate for seamless loop
                foreach(array_merge($icons, $icons) as $icon): ?>
                    <div class="icon-item">
                        <i class="fab <?php echo $icon; ?>"></i>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!--UNWANTED Sections-->
<!-- Consultation Section -->
<!--<section class="consultation-section">-->
<!--    <div class="container">-->
<!--        <div class="consultation-content">-->
<!--            <h2>Free Consultation Available</h2>-->
<!--            <p>Get expert advice on your digital marketing strategy</p>-->
<!--            <a href="/contact.php?type=consultation" class="btn-secondary">Book Your Consultation</a>-->
<!--        </div>-->
<!--    </div>-->
<!--</section>-->

<!-- About Us Section -->
<!--<section class="about-section" id="about">-->
<!--    <div class="container">-->
<!--        <div class="section-header">-->
<!--            <h2>About Us</h2>-->
<!--            <p class="section-subtitle">Your Trusted Digital Marketing Partner</p>-->
<!--        </div>-->
        
<!--        <div class="about-content">-->
<!--            <div class="about-text">-->
<!--                <p>We are a leading digital marketing agency specializing in comprehensive digital solutions that drive real results. With years of experience and a team of dedicated professionals, we help businesses of all sizes achieve their online goals.</p>-->
                
<!--                <div class="about-features">-->
<!--                    <div class="feature-item">-->
<!--                        <i class="fas fa-check-circle"></i>-->
<!--                        <span>10+ Years Experience</span>-->
<!--                    </div>-->
<!--                    <div class="feature-item">-->
<!--                        <i class="fas fa-check-circle"></i>-->
<!--                        <span>500+ Successful Projects</span>-->
<!--                    </div>-->
<!--                    <div class="feature-item">-->
<!--                        <i class="fas fa-check-circle"></i>-->
<!--                        <span>Expert Team</span>-->
<!--                    </div>-->
<!--                    <div class="feature-item">-->
<!--                        <i class="fas fa-check-circle"></i>-->
<!--                        <span>24/7 Support</span>-->
<!--                    </div>-->
<!--                </div>-->
                
<!--                <a href="/about.php" class="btn-outline">Learn More About Us</a>-->
<!--            </div>-->
            
<!--            <div class="about-image">-->
<!--                <img src="/assets/images/about-illustration.png" alt="About Us" onerror="this.src='https://via.placeholder.com/600x400/000000/FFFFFF?text=About+Us'">-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--</section>-->

<!-- Services Section -->
<section class="services-section" id="services">
    <div class="container">
        <div class="section-header">
            <h2>Our Services</h2>
            <p class="section-subtitle">Comprehensive Digital Marketing Solutions</p>
        </div>
        
        <div class="services-grid">
            <?php
            try {
                $stmt = $db->query("SELECT * FROM services WHERE status = 'active' ORDER BY display_order LIMIT 6");
                $services_found = false;
                while($service = $stmt->fetch()): 
                    $services_found = true;
                ?>
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($service['description'], 0, 120)); ?>...</p>
                        <a href="/services.php#<?php echo $service['slug']; ?>" class="service-link">Learn More →</a>
                    </div>
                <?php 
                endwhile;
                
                // If no services found, show default services
                if (!$services_found):
                    $default_services = [
                        ['icon' => 'fas fa-search', 'title' => 'SEO Optimization', 'description' => 'Improve your website ranking and visibility on search engines with our advanced SEO strategies.'],
                        ['icon' => 'fas fa-bullhorn', 'title' => 'Social Media Marketing', 'description' => 'Engage your audience and build your brand presence across all major social media platforms.'],
                        ['icon' => 'fas fa-ad', 'title' => 'PPC Advertising', 'description' => 'Drive targeted traffic and maximize ROI with strategic pay-per-click advertising campaigns.'],
                        ['icon' => 'fas fa-pen-fancy', 'title' => 'Content Marketing', 'description' => 'Create compelling content that resonates with your audience and drives meaningful engagement.'],
                        ['icon' => 'fas fa-envelope', 'title' => 'Email Marketing', 'description' => 'Build lasting relationships with personalized email campaigns that convert subscribers to customers.'],
                        ['icon' => 'fas fa-chart-line', 'title' => 'Analytics & Reporting', 'description' => 'Get detailed insights and data-driven recommendations to optimize your marketing performance.']
                    ];
                    
                    foreach($default_services as $service): ?>
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="<?php echo $service['icon']; ?>"></i>
                            </div>
 <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($service['description'], 0, 120)); ?>...</p>
                            <a href="/services.php" class="service-link">Learn More →</a>
                        </div>
                    <?php endforeach;
                endif;
            } catch(PDOException $e) {
                error_log("Services query error: " . $e->getMessage());
            }
            ?>
        </div>
        
        <div class="text-center" style="margin-top: 40px;">
            <a href="/services.php" class="btn-primary">View All Services</a>
        </div>
    </div>
</section>

<!-- SEO Boost Suite Products Section [Original]-->

<!--<section class="products-section" id="products">-->
<!--    <div class="container">-->
<!--        <div class="section-header">-->
<!--            <h2>Try Now</h2>-->
<!--            <p class="section-subtitle">Powerful Tools to Enhance Your Online Presence</p>-->
<!--                        <p class="section-subtitle">Wall of Marketing (WOM)'s own SaaS which are started from zero having 100K + Daily Visits & 10K + Monthly Sign-ups</p>-->
<!--        </div>-->
        
        <!--<div class="products-grid">-->
            <?php
            try {
                $stmt = $db->query("SELECT * FROM products WHERE status = 'active' ORDER BY display_order LIMIT 4");
                $products_found = false;
                while($product = $stmt->fetch()): 
                    $products_found = true;
                ?>
                    <!--<div class="product-card">-->
                    <!--    <div class="product-image">-->
                    <!--        <img src="<?php echo htmlspecialchars($product['image']); ?>" -->
                    <!--             alt="<?php echo htmlspecialchars($product['product_name']); ?>"-->
                    <!--             onerror="this.src='https://via.placeholder.com/400x250/000000/FFFFFF?text=<?php echo urlencode($product['product_name']); ?>'">-->
                    <!--        <div class="product-badge">Popular</div>-->
                    <!--    </div>-->
                        
                        <!--<div class="product-content">-->
                            <!--<h3><?php echo htmlspecialchars($product['product_name']); ?></h3>-->
                            <!--<p><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>-->
                            
                            <!--<div class="product-features">-->
                                <?php
                                $features = json_decode($product['features'], true);
                                if($features && is_array($features)):
                                    foreach(array_slice($features, 0, 3) as $feature): ?>
                                        <!--<div class="feature">-->
                                        <!--    <i class="fas fa-check"></i> <?php echo htmlspecialchars($feature); ?>-->
                                        <!--</div>-->
                                <?php endforeach;
                                endif; ?>
                            <!--</div>-->
                            
                    <!--        <div class="product-footer">-->
                    <!--            <span class="price">₹<?php echo number_format($product['price'], 2); ?></span>-->
                    <!--            <a href="<?php echo $product['url']; ?>" class="btn-small">Get Started</a>-->
                    <!--        </div>-->
                    <!--    </div>-->
                    <!--</div>-->
                <?php 
                endwhile;
                
                // If no products found, show default products
                if (!$products_found):
                    $default_products = [
                        ['name' => 'SEO Starter Pack', 'description' => 'Essential SEO tools for small businesses and startups', 'price' => 9999, 'features' => ['Keyword Research', 'On-page SEO', 'Monthly Reports']],
                        ['name' => 'Social Media Manager', 'description' => 'Complete social media management solution', 'price' => 14999, 'features' => ['Multi-platform Support', 'Content Scheduling', 'Analytics Dashboard']],
                        ['name' => 'Content Marketing Suite', 'description' => 'All-in-one content creation and distribution', 'price' => 19999, 'features' => ['Content Calendar', 'SEO Optimization', 'Performance Tracking']],
                        ['name' => 'Enterprise Package', 'description' => 'Comprehensive digital marketing solution', 'price' => 49999, 'features' => ['Custom Strategy', 'Dedicated Manager', '24/7 Support']]
                    ];
                    
                    foreach($default_products as $product): ?>
                        <!--<div class="product-card">-->
                        <!--    <div class="product-image">-->
                        <!--        <img src="https://via.placeholder.com/400x250/000000/FFFFFF?text=<?php echo urlencode($product['name']); ?>" -->
                        <!--             alt="<?php echo $product['name']; ?>">-->
                        <!--        <div class="product-badge">Popular</div>-->
                        <!--    </div>-->
                            
                        <!--    <div class="product-content">-->
                        <!--        <h3><?php echo $product['name']; ?></h3>-->
                        <!--        <p><?php echo $product['description']; ?></p>-->
                                
                        <!--        <div class="product-features">-->
                        <!--            <?php foreach($product['features'] as $feature): ?>-->
                        <!--                <div class="feature">-->
                        <!--                    <i class="fas fa-check"></i> <?php echo $feature; ?>-->
                        <!--                </div>-->
                        <!--            <?php endforeach; ?>-->
                        <!--        </div>-->
                                
                        <!--        <div class="product-footer">-->
                        <!--            <span class="price">₹<?php echo number_format($product['price'], 2); ?></span>-->
                        <!--            <a href="/contact.php" class="btn-small">Get Started</a>-->
                        <!--        </div>-->
                        <!--    </div>-->
                        <!--</div>-->
                    <?php endforeach;
                endif;
            } catch(PDOException $e) {
                error_log("Products query error: " . $e->getMessage());
            }
            ?>
<!--        </div>-->
<!--</div>-->
<!--    </section>-->
    


<!--=============================================Current Product CODE===========================================-->
<section class="products-section" id="products">
    <div class="container">
        <div class="section-header">
            <h2>Try Now</h2>
            <p class="section-subtitle">Powerful Tools to Enhance Your Online Presence</p>
            <p class="section-subtitle">Wall of Marketing (WOM)'s own SaaS which are started from zero, Today having 100K + Daily Visits & 10K + Monthly Sign-ups</p>
        </div>
        
        <?php
        try {
            // First, count active products
            $count_stmt = $db->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
            $product_count = $count_stmt->fetchColumn();
            
            // Determine if slider should be active
            $enable_slider = $product_count > 1;
            
            // If no products, use default products
            if ($product_count == 0) {
                $product_count = 4;
                $enable_slider = true;
            }
        ?>
        
        <div class="<?php echo $enable_slider ? 'swiper products-slider' : 'products-single'; ?>">
            <div class="<?php echo $enable_slider ? 'swiper-wrapper' : ''; ?>">
                <?php
                $stmt = $db->query("SELECT * FROM products WHERE status = 'active' ORDER BY display_order LIMIT 4");
                $products_found = false;
                
                while($product = $stmt->fetch()): 
                    $products_found = true;
                ?>
                    <div class="<?php echo $enable_slider ? 'swiper-slide' : ''; ?> product-card">
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                 onerror="this.src='https://placehold.co/600x400?text=<?php echo urlencode($product['product_name']); ?>'">
                            <div class="product-badge">Popular</div>
                        </div>
                        
                        <div class="product-content">
                             <h3><?php echo html_entity_decode ($product['product_name']); ?></h3>
                            <p><?php echo htmlspecialchars($product['short_description']); ?>...</p>
                            
                            <div class="product-features">
                                <?php
                                $features = json_decode($product['features'], true);
                                if($features && is_array($features)):
                                    foreach(array_slice($features, 0, 3) as $feature): ?>
                                        <div class="feature">
                                            <i class="fas fa-check"></i> <?php echo htmlspecialchars($feature); ?>
                                        </div>
                                <?php endforeach;
                                endif; ?>
                            </div>
                            
                            <div class="product-footer">
                                <span class="price">₹<?php echo number_format($product['price'], 2); ?></span>
                                <a href="<?php echo $product['url']; ?>" class="btn-small">Get Started</a>
                            </div>
                        </div>
                    </div>
                <?php 
                endwhile;
                
                // If no products found, show default products
                if (!$products_found):
                    $default_products = [
                        ['name' => 'SEO Starter Pack', 'description' => 'Essential SEO tools for small businesses and startups', 'price' => 9999, 'features' => ['Keyword Research', 'On-page SEO', 'Monthly Reports']],
                        ['name' => 'Social Media Manager', 'description' => 'Complete social media management solution', 'price' => 14999, 'features' => ['Multi-platform Support', 'Content Scheduling', 'Analytics Dashboard']],
                        ['name' => 'Content Marketing Suite', 'description' => 'All-in-one content creation and distribution', 'price' => 19999, 'features' => ['Content Calendar', 'SEO Optimization', 'Performance Tracking']],
                        ['name' => 'Enterprise Package', 'description' => 'Comprehensive digital marketing solution', 'price' => 49999, 'features' => ['Custom Strategy', 'Dedicated Manager', '24/7 Support']]
                    ];
                    
                    foreach($default_products as $product): ?>
                        <div class="<?php echo $enable_slider ? 'swiper-slide' : ''; ?> product-card">
                            <div class="product-image">
                                <img src="https://placeholder.co/400x250/000000/FFFFFF?text=<?php echo urlencode($product['name']); ?>" 
                                     alt="<?php echo $product['name']; ?>">
                                <div class="product-badge">Popular</div>
                            </div>
                            
                            <div class="product-content">
                                <h3><?php echo $product['name']; ?></h3>
                                <p><?php echo $product['description']; ?></p>
                                
                                <div class="product-features">
                                    <?php foreach($product['features'] as $feature): ?>
                                        <div class="feature">
                                            <i class="fas fa-check"></i> <?php echo $feature; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="product-footer">
                                    <span class="price">₹<?php echo number_format($product['price'], 2); ?></span>
                                    <a href="/contact.php" class="btn-small">Get Started</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach;
                endif;
            } catch(PDOException $e) {
                error_log("Products query error: " . $e->getMessage());
                $enable_slider = false;
            }
            ?>
            </div>
            
            <?php if($enable_slider): ?>
                <!-- Slider Navigation -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                
                <!-- Slider Pagination -->
                <div class="swiper-pagination"></div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Add Swiper CSS to your <head> section -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<!-- Add this script before closing </body> tag -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if($enable_slider): ?>
    const productsSwiper = new Swiper('.products-slider', {
        slidesPerView: 1, // Always show 1 slide at full size
        spaceBetween: 30,
        centeredSlides: true,
        loop: true,
        autoplay: {
            delay: 4000, // 4 seconds per slide
            disableOnInteraction: false,
            pauseOnMouseEnter: true
        },
        speed: 800, // Smooth transition
        effect: 'slide', // Can change to 'fade', 'cube', 'coverflow', or 'flip'
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
            dynamicBullets: true
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        }
    });
    <?php endif; ?>
});
</script>

<style>
/* Slider container */
.products-slider {
    padding: 20px 0 60px;
    /*max-width: 800px; */-/* Control max width for better full-size display */
    margin: 0 auto;
}

.products-slider .swiper-slide {
    height: auto;
    display: flex;
    justify-content: center;
}

/* Full-size product card */
.products-slider .product-card {
    width: 100%;
    /*max-width: 600px;*/
    margin: 0 auto;
}

/* Single product view (no slider) */
.products-single {
    /*max-width: 600px;*/
    margin: 0 auto;
    padding: 20px 0;
}

.products-single .product-card {
    width: 100%;
}

/* Navigation buttons */
.swiper-button-next,
.swiper-button-prev {
    color: #000;
    background: rgba(255, 255, 255, 0.95);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.swiper-button-next:hover,
.swiper-button-prev:hover {
    background: rgba(255, 255, 255, 1);
    transform: scale(1.1);
}

.swiper-button-next:after,
.swiper-button-prev:after {
    font-size: 22px;
    font-weight: bold;
}

/* Pagination */
.swiper-pagination {
    bottom: 20px !important;
}

.swiper-pagination-bullet {
    background: #000;
    opacity: 0.3;
    width: 12px;
    height: 12px;
    transition: all 0.3s ease;
}

.swiper-pagination-bullet-active {
    opacity: 1;
    background: #000;
    width: 30px;
    border-radius: 6px;
}

/* Product card styling */
.product-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.product-image {
    position: relative;
    overflow: hidden;
    /*height: 250px;*/
    height: auto;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-content {
    padding: 25px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-content h3 {
    font-size: 24px;
    margin-bottom: 12px;
    color: #000;
}

.product-content > p {
    color: #666;
    margin-bottom: 20px;
    line-height: 1.6;
}

.product-features {
    margin-bottom: 20px;
    flex: 1;
}

.product-features .feature {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    color: #333;
}

.product-features .feature i {
    color: #28a745;
}

.product-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.price {
    font-size: 28px;
    font-weight: bold;
    color: #000;
}

.btn-small {
    background: #000;
    color: #fff;
    padding: 12px 24px;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 600;
}

.btn-small:hover {
    background: #333;
    transform: scale(1.05);
}

.product-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #ff6b6b;
    color: #fff;
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

/* Responsive */
@media (max-width: 768px) {
    .products-slider {
        max-width: 100%;
        padding: 10px 20px 50px;
    }
    
    .product-card {
        max-width: 100%;
    }
    
    .swiper-button-next,
    .swiper-button-prev {
        width: 40px;
        height: 40px;
    }
    
    .swiper-button-next:after,
    .swiper-button-prev:after {
        font-size: 18px;
    }
}
</container>

## What Changed:

### Slider Configuration
- **`slidesPerView: 1`**: Always shows 1 full-size product
- **`centeredSlides: true`**: Centers the active slide
- **`autoplay.delay: 4000`**: Auto-rotates every 4 seconds
- **`loop: true`**: Continuous infinite loop
- **`pauseOnMouseEnter: true`**: Pauses when user hovers

### Visual Improvements
- Centered layout with max-width for better focus
- Smooth 800ms transitions
- Larger navigation buttons with hover effects
- Animated pagination dots (active dot expands)
- Enhanced card hover effects

### Optional Effect Variations
Change `effect: 'slide'` to:
- `'fade'` - Fade transition
- `'cube'` - 3D cube rotation
- `'coverflow'` - Apple Cover Flow style
- `'flip'` - Card flip animation

Perfect for showcasing your WOM SaaS products one at a time with full attention! 🚀



<!--========================================================================================-->


<!-- Case Studies Section -->
<section class="case-studies-section" id="case-studies">
    <div class="container">
        <div class="section-header">
            <h2>Success Stories</h2>
            <p class="section-subtitle">Real Results for Real Businesses</p>
        </div>
        
        <div class="case-studies-grid">
            <?php
            try {
                $case_studies = getFeaturedCaseStudies($db, 3);
                
                if(!empty($case_studies)):
                    foreach($case_studies as $case_study): 
            ?>
                <article class="case-study-card">
                    <div class="case-study-image">
                        <img src="<?php echo htmlspecialchars($case_study['featured_image']); ?>" 
                             alt="<?php echo htmlspecialchars($case_study['title']); ?>"
                             onerror="this.src='https://via.placeholder.com/600x400/000000/FFFFFF?text=Case+Study'">
                        <div class="case-study-overlay">
                            <a href="/case-studies/<?php echo $case_study['slug']; ?>" class="view-case-study">
                                View Case Study <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="case-study-content">
                        <div class="case-study-meta">
                            <span class="industry"><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($case_study['industry']); ?></span>
                            <span class="client"><i class="fas fa-user"></i> <?php echo htmlspecialchars($case_study['client_name']); ?></span>
                        </div>
                        
                        <h3>
                            <a href="/case-studies/<?php echo $case_study['slug']; ?>">
                                <?php echo htmlspecialchars($case_study['title']); ?>
                            </a>
                        </h3>
                        
                        <p><?php echo htmlspecialchars(substr($case_study['short_description'], 0, 150)); ?>...</p>
                        
                        <?php if($case_study['services_provided']): ?>
                        <div class="case-study-tags">
                            <?php 
                            $services = array_slice(explode(',', $case_study['services_provided']), 0, 3);
                            foreach($services as $service): 
                            ?>
                                <span class="tag"><?php echo trim($service); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <a href="/case-studies/<?php echo $case_study['slug']; ?>" class="read-more">
                            Read Full Story <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </article>
            <?php 
                    endforeach;
                else:
                    // Show default case studies if none in database
            ?>
                <article class="case-study-card">
                    <div class="case-study-image">
                        <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600" alt="Case Study">
                    </div>
                    <div class="case-study-content">
                        <div class="case-study-meta">
                            <span class="industry"><i class="fas fa-briefcase"></i> E-commerce</span>
                        </div>
                        <h3><a href="/case-studies">300% Revenue Growth</a></h3>
                        <p>Transformed an emerging e-commerce brand into a market leader.</p>
                        <a href="/case-studies" class="read-more">Read Full Story <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
            <?php 
                endif;
            } catch(Exception $e) {
                error_log("Case studies error: " . $e->getMessage());
            }
            ?>
        </div>
        
        <div class="text-center" style="margin-top: 50px;">
            <a href="/case-studies" class="btn-primary">View All Case Studies</a>
        </div>
    </div>
</section>

<style>
.case-studies-section {
    padding: 100px 0;
    background: #f8f8f8;
}

.case-studies-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 40px;
    margin-top: 60px;
}

.case-study-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.case-study-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    border-color: #000;
}

.case-study-image {
    position: relative;
    height: 280px;
    overflow: hidden;
}

.case-study-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.case-study-card:hover .case-study-image img {
    transform: scale(1.1);
}

.case-study-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 100%);
    display: flex;
    align-items: flex-end;
    padding: 30px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.case-study-card:hover .case-study-overlay {
    opacity: 1;
}

.view-case-study {
    color: white;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.1rem;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.case-study-content {
    padding: 30px;
}

.case-study-meta {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.case-study-meta span {
    font-size: 13px;
    color: #666;
    display: flex;
    align-items: center;
    gap: 6px;
}

.case-study-meta i {
    color: #000;
}

.case-study-content h3 {
    font-size: 1.4rem;
    margin-bottom: 15px;
    line-height: 1.4;
}

.case-study-content h3 a {
    color: #1a1a1a;
    text-decoration: none;
    transition: color 0.3s;
}

.case-study-content h3 a:hover {
    color: #000;
}

.case-study-content p {
    color: #666;
    line-height: 1.7;
    margin-bottom: 20px;
}

.case-study-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.case-study-tags .tag {
    background: #f0f0f0;
    color: #333;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.case-study-content .read-more {
    color: #000;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: gap 0.3s;
}

.case-study-content .read-more:hover {
    gap: 15px;
}

@media (max-width: 768px) {
    .case-studies-grid {
        grid-template-columns: 1fr;
    }
}
</style>


<!-- Blog Section -->
<section class="blog-section" id="blogs">
    <div class="container">
        <div class="section-header">
            <h2>Latest Blogs</h2>
            <p class="section-subtitle">Insights, Tips & Industry Updates</p>
        </div>
        
        <div class="blog-grid">
            <?php
            try {
                $blogs = getRecentBlogs($db, 6);
                $blogs_found = !empty($blogs);
                
                foreach($blogs as $blog): 
                    $category_slug = strtolower(str_replace(' ', '-', $blog['category']));
                    $blog_url = '/blogs/' . $category_slug . '/' . $blog['slug'];
                ?>
                    <article class="blog-card">
                        <div class="blog-image">
                            <img src="<?php echo htmlspecialchars($blog['featured_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($blog['title']); ?>"
                                 onerror="this.src='https://via.placeholder.com/400x250/000000/FFFFFF?text=Blog+Image'">
                            <span class="blog-category"><?php echo htmlspecialchars($blog['category']); ?></span>
                        </div>
                        
                        <div class="blog-content">
                            <div class="blog-meta">
                                <span><i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($blog['created_at'])); ?></span>
                                <span><i class="far fa-eye"></i> <?php echo $blog['views']; ?></span>
                            </div>
                            
                            <h3>
                                <a href="<?php echo $blog_url; ?>">
                                    <?php echo htmlspecialchars($blog['title']); ?>
                                </a>
                            </h3>
                            
                            <p><?php echo htmlspecialchars(substr($blog['excerpt'], 0, 120)); ?>...</p>
                            
                            <a href="<?php echo $blog_url; ?>" class="read-more">
                                Read More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                <?php 
                endforeach;
                
                if (!$blogs_found):
                    $default_blogs = [
                        ['title' => '10 SEO Strategies for 2025', 'category' => 'SEO', 'excerpt' => 'Discover the latest SEO techniques.', 'date' => date('Y-m-d'), 'slug' => '10-seo-strategies-for-2025'],
                        ['title' => 'Social Media Marketing Guide', 'category' => 'Social Media', 'excerpt' => 'Learn how to create engaging content.', 'date' => date('Y-m-d'), 'slug' => 'social-media-marketing-guide']
                    ];
                    
                    foreach($default_blogs as $blog): 
                        $category_slug = strtolower(str_replace(' ', '-', $blog['category']));
                    ?>
                        <article class="blog-card">
                            <div class="blog-image">
                                <img src="https://via.placeholder.com/400x250/000000/FFFFFF?text=Blog+Post" alt="<?php echo $blog['title']; ?>">
                                <span class="blog-category"><?php echo $blog['category']; ?></span>
                            </div>
                            <div class="blog-content">
                                <div class="blog-meta">
                                    <span><i class="far fa-calendar"></i> <?php echo date('M d, Y'); ?></span>
                                    <span><i class="far fa-eye"></i> 0</span>
                                </div>
                                <h3><a href="/blogs"><?php echo $blog['title']; ?></a></h3>
                                <p><?php echo $blog['excerpt']; ?></p>
                                <a href="/blogs" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </article>
                    <?php endforeach;
                endif;
            } catch(Exception $e) {
                error_log("Blogs query error: " . $e->getMessage());
            }
            ?>
        </div>
        
        <div class="text-center" style="margin-top: 40px;">
            <a href="blogs.php" class="btn-primary">View All Blogs</a>
        </div>
    </div>
</section>

<!-- Contact/CTA Section -->
<section class="contact-cta-section" id="contact">
    <div class="container">
        <div class="cta-content">
            <h2>Ready to Boost Your Digital Presence?</h2>
            <p>Let's discuss how we can help your business grow online</p>
            <div class="cta-buttons">
                <a href="/contact.php" class="btn-primary">Contact Us Today</a>
                <a href="/book-call.php" class="btn-secondary">
                    <i class="fas fa-phone"></i> Call Now
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-content">
            <div class="newsletter-text">
                <h2>Subscribe to Our Newsletter</h2>
                <p>Get the latest digital marketing tips and industry insights delivered to your inbox</p>
            </div>
            
            <form id="home-newsletter-form" class="newsletter-form">
                <input type="email" name="email" placeholder="Enter your email address" required>
                <button type="submit" class="btn-primary">
                    Subscribe <i class="fas fa-paper-plane"></i>
                </button>
            </form>
            <div id="newsletter-message"></div>
        </div>
    </div>
</section>

<!-- Statistics/Counter Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="counter" data-target="500">0</h3>
                <p>Happy Clients</p>
            </div>
            
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <h3 class="counter" data-target="1200">0</h3>
                <p>Projects Completed</p>
            </div>
            
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-award"></i>
                </div>
                <h3 class="counter" data-target="50">0</h3>
                <p>Awards Won</p>
            </div>
            
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3 class="counter" data-target="10">0</h3>
                <p>Years Experience</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<script>
// Newsletter Form Submission
document.getElementById('home-newsletter-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const messageDiv = document.getElementById('newsletter-message');
    
    messageDiv.textContent = 'Subscribing...';
    messageDiv.style.display = 'block';
    
    fetch('/api/newsletter-subscribe.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        messageDiv.textContent = data.message;
        messageDiv.className = data.success ? 'success-message' : 'error-message';
        messageDiv.style.display = 'block';
        
        if(data.success) {
            this.reset();
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        }
    })
    .catch(error => {
        messageDiv.textContent = 'An error occurred. Please try again.';
        messageDiv.className = 'error-message';
        messageDiv.style.display = 'block';
    });
});

// Counter Animation
function animateCounters() {
    const counters = document.querySelectorAll('.counter');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const duration = 2000;
        const increment = target / (duration / 16);
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                counter.textContent = target + '+';
                clearInterval(timer);
            } else {
                counter.textContent = Math.floor(current);
            }
        }, 16);
    });
}

// Trigger counter animation when section is visible
const statsSection = document.querySelector('.stats-section');
if (statsSection) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounters();
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    observer.observe(statsSection);
}
</script>


