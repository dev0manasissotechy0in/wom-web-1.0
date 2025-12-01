<!-- Bento Grid Features Section -->
<section class="features-bento-section">
    <div class="container">
        <div class="features-header">
            <h2 class="section-title animation fade-in-up">Built for Excellence</h2>
            <p class="section-subtitle animation fade-in-up">Every detail matters</p>
        </div>

        <div class="bento-grid">
            <?php 
            $features_bento = mysqli_query($conn, "SELECT * FROM features WHERE is_active = 1 ORDER BY display_order LIMIT 8");
            $bento_sizes = ['large', 'medium', 'medium', 'large', 'medium', 'medium', 'large', 'medium'];
            $index = 0;
            while($feature = mysqli_fetch_assoc($features_bento)): 
                $size = $bento_sizes[$index % count($bento_sizes)];
            ?>
                <div class="bento-card bento-<?php echo $size; ?> animation" 
                     style="animation-delay: <?php echo $index * 0.1; ?>s">
                    <div class="bento-bg" style="background: linear-gradient(135deg, <?php echo $feature['icon_color']; ?>10 0%, <?php echo $feature['icon_color']; ?>30 100%)"></div>
                    <div class="bento-content">
                        <div class="bento-icon" style="color: <?php echo $feature['icon_color']; ?>">
                            <?php echo $feature['icon']; ?>
                        </div>
                        <h3><?php echo htmlspecialchars($feature['title']); ?></h3>
                        <p><?php echo htmlspecialchars($feature['description']); ?></p>
                        <?php if($size === 'large'): ?>
                            <div class="bento-visual">
                                <div class="pulse-circle" style="background: <?php echo $feature['icon_color']; ?>"></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php 
                $index++;
            endwhile; 
            ?>
        </div>
    </div>
</section>
