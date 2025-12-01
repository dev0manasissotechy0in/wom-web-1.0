<?php
require_once 'config/config.php';

// Get slug from URL
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if(empty($slug)) {
    header("Location: /services.php");
    exit;
}

// Fetch service details
try {
    $stmt = $db->prepare("SELECT * FROM services WHERE slug = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$slug]);
    $service = $stmt->fetch();
    
    if(!$service) {
        header('Location: /error?code=404');
        exit();
    }
    
    // Parse services_info JSON
    $features = json_decode($service['services_info'], true);
    if(!is_array($features)) {
        $features = [];
    }
    
    // Parse gallery images
    $gallery_images = json_decode($service['gallery_images'], true);
    if(!is_array($gallery_images)) {
        $gallery_images = [];
    }
    
    // Parse process steps
    $process_steps = json_decode($service['process_steps'], true);
    if(!is_array($process_steps)) {
        // Default process steps if none defined
        $process_steps = [
            ["title" => "Discovery & Strategy", "description" => "We analyze your business, target audience, and competitors to create a tailored strategy."],
            ["title" => "Planning & Setup", "description" => "Our team develops a comprehensive plan and sets up necessary tools."],
            ["title" => "Execution & Launch", "description" => "We implement the strategy with precision and high-quality execution."],
            ["title" => "Optimization & Growth", "description" => "Continuous monitoring and optimization for maximum ROI."]
        ];
    }
    
} catch(PDOException $e) {
    error_log("Service Error: " . $e->getMessage());
    die("Database error occurred");
}

// SEO Data
$customSeoData = [
    'title' => htmlspecialchars($service['title']) . ' | ' . SITE_NAME,
    'description' => htmlspecialchars($service['description']),
    'keywords' => htmlspecialchars($service['title'] . ', digital marketing, SaaS marketing, ' . SITE_NAME),
    'image' => htmlspecialchars($service['featured_image']),
    'url' => SITE_URL . '/service-detail?slug=' . $service['slug'],
    'type' => 'service'
];
?>

<?php require_once 'includes/header.php'; ?>

<style>
/* Hero Section */
.service-hero {
    background: linear-gradient(135deg, #000 0%, #1a1a1a 100%);
    color: white;
    padding: 80px 0 60px;
    position: relative;
    overflow: hidden;
}

.service-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.05)"/></svg>');
    opacity: 0.3;
}

.service-hero .container {
    position: relative;
    z-index: 1;
}

.hero-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
}

.hero-text h1 {
    font-size: 3rem;
    margin-bottom: 20px;
    font-weight: 700;
    line-height: 1.2;
}

.hero-text p {
    font-size: 1.2rem;
    line-height: 1.8;
    opacity: 0.9;
    margin-bottom: 30px;
}

.hero-cta {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.btn-primary, .btn-secondary {
    padding: 15px 30px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;
}

.btn-primary {
    background: white;
    color: #000;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(255,255,255,0.2);
}

.btn-secondary {
    background: transparent;
    color: white;
    border: 2px solid white;
}

.btn-secondary:hover {
    background: white;
    color: #000;
}

.hero-image {
    position: relative;
}

.hero-image img {
    width: 100%;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

/* Features Section */
.features-section {
    padding: 80px 0;
    background: #f8f9fa;
}

.section-header {
    text-align: center;
    margin-bottom: 60px;
}

.section-header h2 {
    font-size: 2.5rem;
    margin-bottom: 15px;
    color: #000;
}

.section-header p {
    font-size: 1.1rem;
    color: #666;
    max-width: 700px;
    margin: 0 auto;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}

.feature-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.3s;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.feature-icon {
    width: 60px;
    height: 60px;
    background: #000;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.5rem;
    margin-bottom: 20px;
}

.feature-card h3 {
    font-size: 1.25rem;
    margin-bottom: 10px;
    color: #000;
}

.feature-card p {
    color: #666;
    line-height: 1.6;
}

/* Video Section */
.video-section {
    padding: 80px 0;
    background: white;
}

.video-container {
    max-width: 900px;
    margin: 0 auto;
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.video-container video,
.video-container iframe {
    width: 100%;
    height: 500px;
    display: block;
}

.video-placeholder {
    width: 100%;
    height: 500px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 4rem;
}

/* Process Section */
.process-section {
    padding: 80px 0;
    background: #f8f9fa;
}

.process-timeline {
    max-width: 800px;
    margin: 0 auto;
    position: relative;
}

.process-timeline::before {
    content: '';
    position: absolute;
    left: 30px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #ddd;
}

.process-step {
    position: relative;
    padding-left: 80px;
    margin-bottom: 40px;
}

.step-number {
    position: absolute;
    left: 0;
    width: 60px;
    height: 60px;
    background: #000;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    z-index: 1;
}

.step-content {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.step-content h3 {
    font-size: 1.5rem;
    margin-bottom: 10px;
    color: #000;
}

.step-content p {
    color: #666;
    line-height: 1.6;
}

/* Gallery Section */
.gallery-section {
    padding: 80px 0;
    background: white;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.gallery-item {
    position: relative;
    border-radius: 15px;
    overflow: hidden;
    height: 250px;
    cursor: pointer;
    transition: all 0.3s;
}

.gallery-item:hover {
    transform: scale(1.05);
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* CTA Section */
.cta-section {
    padding: 80px 0;
    background: linear-gradient(135deg, #000 0%, #1a1a1a 100%);
    color: white;
    text-align: center;
}

.cta-content h2 {
    font-size: 2.5rem;
    margin-bottom: 20px;
}

.cta-content p {
    font-size: 1.2rem;
    margin-bottom: 30px;
    opacity: 0.9;
}

.cta-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Responsive */
@media (max-width: 968px) {
    .hero-content {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .hero-text h1 {
        font-size: 2.5rem;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .process-timeline::before {
        left: 20px;
    }
    
    .process-step {
        padding-left: 70px;
    }
    
    .step-number {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
}

@media (max-width: 576px) {
    .hero-text h1 {
        font-size: 2rem;
    }
    
    .section-header h2 {
        font-size: 2rem;
    }
    
    .video-container video,
    .video-container iframe,
    .video-placeholder {
        height: 300px;
    }
}
</style>

<!-- Hero Section -->
<section class="service-hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1><?php echo htmlspecialchars($service['title']); ?></h1>
                <p><?php echo htmlspecialchars($service['description']); ?></p>
                <div class="hero-cta">
                    <a href="/book-call" class="btn-primary">
                        <i class="fas fa-calendar"></i> Book a Free Consultation
                    </a>
                    <a href="/contact" class="btn-secondary">
                        <i class="fas fa-envelope"></i> Get in Touch
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <img src="<?php echo htmlspecialchars($service['featured_image']); ?>" 
                     alt="<?php echo htmlspecialchars($service['title']); ?>">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<?php if(!empty($features)): ?>
<section class="features-section">
    <div class="container">
        <div class="section-header">
            <h2>What's Included</h2>
            <p>Everything you need to succeed with our comprehensive service package</p>
        </div>
        <div class="features-grid">
            <?php foreach($features as $index => $feature): ?>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h3><?php echo htmlspecialchars($feature); ?></h3>
                <p>Professional implementation and ongoing support to ensure maximum results.</p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Video Section -->
<?php if(!empty($service['video_url'])): ?>
<section class="video-section">
    <div class="container">
        <div class="section-header">
            <h2>See How It Works</h2>
            <p>Watch our video to learn more about this service</p>
        </div>
        <div class="video-container">
            <iframe src="<?php echo htmlspecialchars($service['video_url']); ?>" 
                    frameborder="0" 
                    allowfullscreen
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Process Section -->
<section class="process-section">
    <div class="container">
        <div class="section-header">
            <h2>Our Process</h2>
            <p>How we deliver exceptional results for your business</p>
        </div>
        <div class="process-timeline">
            <?php foreach($process_steps as $index => $step): ?>
            <div class="process-step">
                <div class="step-number"><?php echo $index + 1; ?></div>
                <div class="step-content">
                    <h3><?php echo htmlspecialchars($step['title']); ?></h3>
                    <p><?php echo htmlspecialchars($step['description']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Gallery Section -->
<?php if(!empty($gallery_images)): ?>
<section class="gallery-section">
    <div class="container">
        <div class="section-header">
            <h2>Our Work</h2>
            <p>Examples of successful projects and results we've delivered</p>
        </div>
        <div class="gallery-grid">
            <?php foreach($gallery_images as $index => $image): ?>
            <div class="gallery-item">
                <img src="<?php echo htmlspecialchars($image); ?>" alt="Project <?php echo $index + 1; ?>">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Ready to Get Started?</h2>
            <p>Let's discuss how we can help you achieve your marketing goals</p>
            <div class="cta-buttons">
                <a href="/book-call" class="btn-primary">
                    <i class="fas fa-calendar"></i> Schedule a Call
                </a>
                <a href="/contact" class="btn-secondary">
                    <i class="fas fa-envelope"></i> Contact Us
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
