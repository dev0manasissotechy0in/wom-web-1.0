


<!-- Modern Tabbed Features Section -->
 <div class="feature-card card-3d tilt-3d"> <!-- 3D Section -->
<section class="features-modern-section">
    <div class="container">
        <div class="features-header">
            <h2 class="section-title animation fade-in-up">Powerful Features</h2>
            <p class="section-subtitle animation fade-in-up">Everything you need to scale your business</p>
        </div>

        <!-- Feature Tabs Navigation -->
        <div class="features-tabs">
            <?php 
            // Get unique categories
            $categories_query = mysqli_query($conn, "SELECT DISTINCT category FROM features WHERE is_active = 1 ORDER BY category");
            $categories = [];
            while($cat = mysqli_fetch_assoc($categories_query)) {
                $categories[] = $cat['category'];
            }
            
            $active_first = true;
            foreach($categories as $category): 
            ?>
                <button class="tab-btn <?php echo $active_first ? 'active' : ''; ?>" 
                        onclick="switchTab('<?php echo $category; ?>')">
                    <span class="tab-icon"><?php echo getCategoryIcon($category); ?></span>
                    <span class="tab-label"><?php echo ucfirst($category); ?></span>
                </button>
            <?php 
                $active_first = false;
            endforeach; 
            ?>
        </div>

        <!-- Feature Tab Content -->
        <div class="features-tab-content">
            <?php 
            $active_first = true;
            foreach($categories as $category): 
                $features_in_category = mysqli_query($conn, "SELECT * FROM features WHERE is_active = 1 AND category = '$category' ORDER BY display_order");
            ?>
                <div class="tab-pane <?php echo $active_first ? 'active' : ''; ?>" id="tab-<?php echo $category; ?>">
                    <div class="features-showcase">
                        <div class="showcase-left">
                            <?php while($feature = mysqli_fetch_assoc($features_in_category)): ?>
                                <div class="feature-item-modern">
                                    <div class="feature-icon-modern" style="background: <?php echo $feature['icon_color']; ?>20; color: <?php echo $feature['icon_color']; ?>">
                                        <?php echo $feature['icon']; ?>
                                    </div>
                                    <div class="feature-content-modern">
                                        <h3><?php echo htmlspecialchars($feature['title']); ?></h3>
                                        <p><?php echo htmlspecialchars($feature['description']); ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <div class="showcase-right">
                            <div class="feature-visual">
                                <div class="visual-mockup">
                                    <div class="mockup-screen">
                                        <div class="mockup-header">
                                            <span class="dot"></span>
                                            <span class="dot"></span>
                                            <span class="dot"></span>
                                        </div>
                                        <div class="mockup-content">
                                            <div class="animated-gradient"></div>
                                            <div class="data-bars">
                                                <div class="bar"></div>
                                                <div class="bar"></div>
                                                <div class="bar"></div>
                                                <div class="bar"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                $active_first = false;
            endforeach; 
            ?>
        </div>
    </div>
</section>
        </div>
<?php
// Helper function for category icons
function getCategoryIcon($category) {
    $icons = [
        'performance' => '⚡',
        'security' => '🔒',
        'integration' => '🔗',
        'analytics' => '📊',
        'general' => '✨'
    ];
    return $icons[$category] ?? '✨';
}
?>
