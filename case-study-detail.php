<?php 
// Enable error display for debugging - REMOVE IN PRODUCTION
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering to prevent header errors
ob_start();

require_once 'includes/header.php';

// Get slug from URL
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if(empty($slug)) {
    header('Location: /case-studies');
    exit();
}

try {
    // Check if database connection exists
    if(!isset($db) || !($db instanceof PDO)) {
        throw new Exception("Database connection not available");
    }
    
    // Get case study details
    $stmt = $db->prepare("SELECT * FROM case_studies WHERE slug = ? LIMIT 1");
    
    if(!$stmt) {
        throw new Exception("Failed to prepare statement");
    }
    
    $stmt->execute([$slug]);
    $case_study = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$case_study) {
        header('Location: /case-studies');
        exit();
    }
    
    // Check if status column exists and filter
    if(isset($case_study['status']) && $case_study['status'] !== 'published') {
        header('Location: /case-studies');
        exit();
    }
    
    // Update view count
    try {
        $update_views = $db->prepare("UPDATE case_studies SET views = views + 1 WHERE id = ?");
        $update_views->execute([$case_study['id']]);
    } catch(PDOException $e) {
        // Continue even if view count update fails
        error_log("View count update failed: " . $e->getMessage());
    }
    
    // Get related case studies
    try {
        $related_stmt = $db->prepare("SELECT * FROM case_studies WHERE industry = ? AND id != ? LIMIT 3");
        $related_stmt->execute([$case_study['industry'] ?? '', $case_study['id']]);
        $related_cases = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $related_cases = [];
        error_log("Related cases query failed: " . $e->getMessage());
    }
    
} catch(PDOException $e) {
    die("Database Error: " . $e->getMessage());
} catch(Exception $e) {
    die("Error: " . $e->getMessage());
}

// Parse services into array
$services = !empty($case_study['services_provided']) ? array_map('trim', explode(',', $case_study['services_provided'])) : [];

// Check if SITE_URL is defined
if(!defined('SITE_URL')) {
    define('SITE_URL', 'https://self.manasissotechy.in');
}

// Safe field retrieval function
function getField($array, $key, $default = '') {
    return isset($array[$key]) && !empty($array[$key]) ? $array[$key] : $default;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(getField($case_study, 'meta_title', getField($case_study, 'title'))); ?> - Case Study</title>
    <meta name="description" content="<?php echo htmlspecialchars(getField($case_study, 'meta_description', getField($case_study, 'excerpt'))); ?>">
    <?php if(!empty($case_study['meta_keywords'])): ?>
        <meta name="keywords" content="<?php echo htmlspecialchars($case_study['meta_keywords']); ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?php echo rtrim(SITE_URL, '/'); ?>/case-studies/<?php echo getField($case_study, 'slug'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<style>
/* Your existing CSS from previous response - use the complete CSS I provided earlier */
body { 
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; 
    margin: 0; 
    padding: 0; 
    color: #333;
    line-height: 1.6;
}

.container { 
    max-width: 1200px; 
    margin: 0 auto; 
    padding: 0 20px; 
}

.case-hero {
    background: linear-gradient(135deg, rgba(0,0,0,0.85), rgba(0,0,0,0.7)), 
                url('<?php echo htmlspecialchars(getField($case_study, 'banner_image', getField($case_study, 'featured_image', 'https://via.placeholder.com/1920x600/000000/FFFFFF?text=Case+Study'))); ?>') center/cover no-repeat;
    color: white;
    padding: 140px 0 80px;
    position: relative;
}

.case-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
    z-index: 1;
}

.case-hero-content { 
    max-width: 900px; 
    margin: 0 auto; 
    text-align: center; 
    position: relative;
    z-index: 2;
}

.case-breadcrumb { 
    margin-bottom: 20px; 
    opacity: 0.9; 
    font-size: 14px; 
}

.case-breadcrumb a { 
    color: white; 
    text-decoration: none; 
}

.case-hero h1 { 
    font-size: 3rem; 
    margin-bottom: 20px; 
    font-weight: 700; 
}

.case-hero-meta {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
    margin-top: 30px;
}

.meta-item {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.case-content {
    padding: 80px 0;
    background: white;
}

.content-section {
    margin-bottom: 60px;
}

.content-section h2 {
    font-size: 2rem;
    margin-bottom: 20px;
    font-weight: 700;
}

.services-list {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 30px;
}

.service-tag {
    padding: 10px 20px;
    background: #f0f0f0;
    border-radius: 20px;
    font-size: 14px;
}

.cta-section {
    background: linear-gradient(135deg, #1a1a1a 0%, #000 100%);
    color: white;
    padding: 80px 0;
    text-align: center;
}

.btn-cta {
    display: inline-block;
    padding: 15px 40px;
    background: white;
    color: #000;
    text-decoration: none;
    border-radius: 30px;
    font-weight: 600;
}

@media (max-width: 768px) {
    .case-hero {
        padding: 100px 0 60px;
    }
    
    .case-hero h1 {
        font-size: 2rem;
    }
}
</style>

<section class="case-hero">
    <div class="container">
        <div class="case-hero-content">
            <div class="case-breadcrumb">
                <a href="/">Home</a> / <a href="/case-studies">Case Studies</a> / <?php echo htmlspecialchars(getField($case_study, 'title')); ?>
            </div>
            <h1><?php echo htmlspecialchars(getField($case_study, 'title')); ?></h1>
            <p><?php echo htmlspecialchars(getField($case_study, 'excerpt')); ?></p>
            
            <div class="case-hero-meta">
                <div class="meta-item">
                    <i class="fas fa-briefcase"></i>
                    <span>Industry</span>
                    <strong><?php echo htmlspecialchars(getField($case_study, 'industry')); ?></strong>
                </div>
                <div class="meta-item">
                    <i class="fas fa-user"></i>
                    <span>Client</span>
                    <strong><?php echo htmlspecialchars(getField($case_study, 'client_name')); ?></strong>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="case-content">
    <div class="container">
        <?php if(getField($case_study, 'challenge')): ?>
        <div class="content-section">
            <h2><i class="fas fa-exclamation-triangle"></i> The Challenge</h2>
            <div><?php echo getField($case_study, 'challenge'); ?></div>
        </div>
        <?php endif; ?>

        <?php if(getField($case_study, 'solution')): ?>
        <div class="content-section">
            <h2><i class="fas fa-lightbulb"></i> Our Solution</h2>
            <div><?php echo getField($case_study, 'solution'); ?></div>
            
            <?php if(!empty($services)): ?>
            <h3>Services Provided:</h3>
            <div class="services-list">
                <?php foreach($services as $service): ?>
                    <span class="service-tag"><?php echo htmlspecialchars($service); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if(getField($case_study, 'results')): ?>
        <div class="content-section">
            <h2><i class="fas fa-chart-line"></i> The Results</h2>
            <div><?php echo getField($case_study, 'results'); ?></div>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <h2>Ready to Achieve Similar Results?</h2>
        <p>Let's discuss how we can help grow your business</p>
        <a href="/contact" class="btn-cta">Get Started Today</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

</body>
</html>
