<link rel="stylesheet" href="features_styles.css">

<!-- Split Screen Features Section -->
<section class="features-split-section">
    <div class="container">
        <div class="features-header">
            <h2 class="section-title animation fade-in-up">Why Choose Us</h2>
            <p class="section-subtitle animation fade-in-up">Features that make a difference</p>
        </div>

        <?php 
        $features_split = mysqli_query($conn, "SELECT * FROM features WHERE is_active = 1 ORDER BY display_order");
        $index = 0;
        while($feature = mysqli_fetch_assoc($features_split)): 
            $is_even = $index % 2 == 0;
        ?>
            <div class="split-feature <?php echo $is_even ? 'reverse' : ''; ?> animation">
                <div class="split-content">
                    <div class="feature-badge" style="background: <?php echo $feature['icon_color']; ?>20; color: <?php echo $feature['icon_color']; ?>">
                        <?php echo $feature['icon']; ?> <?php echo ucfirst($feature['category']); ?>
                    </div>
                    <h3 class="split-title"><?php echo htmlspecialchars($feature['title']); ?></h3>
                    <p class="split-description"><?php echo htmlspecialchars($feature['description']); ?></p>
                    <ul class="feature-benefits">
                        <li>✓ Quick setup in minutes</li>
                        <li>✓ No coding required</li>
                        <li>✓ 24/7 support included</li>
                    </ul>
                </div>
                
                <div class="split-visual">
                    <div class="visual-card" style="border-color: <?php echo $feature['icon_color']; ?>">
                        <div class="card-glow" style="background: <?php echo $feature['icon_color']; ?>"></div>
                        <div class="card-content">
                            <div class="visual-icon" style="color: <?php echo $feature['icon_color']; ?>">
                                <?php echo $feature['icon']; ?>
                            </div>
                            <div class="visual-stats">
                                <div class="stat">
                                    <span class="stat-number">99.9%</span>
                                    <span class="stat-label">Uptime</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-number">10k+</span>
                                    <span class="stat-label">Users</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php 
            $index++;
        endwhile; 
        ?>
    </div>
</section>
