<?php
require_once 'includes/header.php';
?>

<section class="services-page">
    <div class="container">
        <div class="services-grid">
            <?php
            $stmt = $db->query("SELECT * FROM services WHERE status = 'active' ORDER BY display_order");
            while($service = $stmt->fetch()): ?>
                <div class="service-card" id="<?php echo e($service['slug']); ?>">
                    <div class="service-icon">
                        <i class="<?php echo e($service['icon']); ?>"></i>
                    </div>
                    <h3><?php echo e($service['title']); ?></h3>
                    <p><?php echo e($service['description']); ?></p>
                    <!-- <a href="/contact.php?service=<?php // echo e($service['slug']); ?>" class="btn-primary">Get Started</a> -->
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- SEO Boost Suite Products Section -->
<section class="products-section" id="products">
    <div class="container">


        <div class="products-grid">
                        <?php
            $stmt = $db->query("SELECT * FROM services WHERE status = 'active' ORDER BY display_order");
            while($service = $stmt->fetch()): ?>
                                    <div class="service-card" id="<?php echo e($service['slug']); ?>">
                        <div class="product-image">
                            <img src="<?php echo e ($service['featured_image']); ?>" 
                                 alt="<?php echo e ($service['title']); ?>"
                                 onerror="this.src='https://via.placeholder.com/400x250/000000/FFFFFF?text=<?php echo urlencode($service['product_name']); ?>'">
                            <div class="product-badge">Popular</div>
                        </div>
                        
                        <div class="product-content">
                            <h3><?php echo htmlspecialchars($product['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                            
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
                        <div class="product-card">
                            <div class="product-image">
                                <img src="https://via.placeholder.com/400x250/000000/FFFFFF?text=<?php echo urlencode($product['name']); ?>" 
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
            }
            ?>
        </div>
    </div>
</section>