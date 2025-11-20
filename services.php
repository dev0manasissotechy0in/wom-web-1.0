<?php
require_once 'includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Our Services</h1>
        <p>Comprehensive Digital Marketing Solutions</p>
    </div>
</section>

<section class="services-page">
    <div class="container">
        <div class="services-grid">
            <?php
            $stmt = $db->query("SELECT * FROM services WHERE status = 'active' ORDER BY display_order");
            while($service = $stmt->fetch()): ?>
                <div class="service-card" id="<?php echo e($service['slug']); ?>">
                    <!--<div class="service-icon">-->
                    <!--    <i class="<?php echo e($service['icon']); ?>"></i>-->
                    <!--</div>-->
                <div class="blog-image">
                    <img src="<?php echo e($service['featured_image']); ?>" alt="<?php echo e($service['title']); ?>">
                                </div>
                    <h3><?php echo e($service['title']); ?></h3>
                    <p><?php echo e($service['description']); ?></p>
                    <!--Features Check-->
                    <div class="product-features">
                                <?php
                                $features = json_decode($service['services_info'], true);
                                if($features && is_array($features)):
                                    foreach(array_slice($features, 0, 10) as $feature): ?>
                                        <div class="feature">
                                            <i class="fas fa-check"></i> <?php echo e ($feature); ?>
                                        </div>
                                <?php endforeach;
                                endif; ?>
                            </div>
                    <!-- <a href="/contact.php?service=<?php // echo e($service['slug']); ?>" class="btn-primary">Get Started</a> -->
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<style>
.page-hero {
    background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%);
    color: white;
    padding: 80px 0;
    text-align: center;
}

.page-hero h1 {
    font-size: 3rem;
    margin-bottom: 10px;
}

.services-page {
    padding: 80px 0;
}
</style>

<?php require_once 'includes/footer.php'; ?>
