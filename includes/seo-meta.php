<?php
/**
 * SEO Meta Tags Generator - FIXED VERSION
 * No direct HTML output - only generates meta tags when called
 * Works with your existing database structure
 */

// Don't output anything directly - this prevents "headers already sent" errors

// Get site settings from database (only if not already loaded)
if (!isset($site_settings) || empty($site_settings)) {
    $site_settings = [];
    try {
        if (isset($db) && $db instanceof PDO) {
            $stmt = $db->query("SELECT * FROM site_settings WHERE id = 1");
            $site_settings = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch(PDOException $e) {
        error_log("SEO Meta error: " . $e->getMessage());
    }
}

// Use database values or fallback to constants
$site_name = $site_settings['site_name'] ?? (defined('SITE_NAME') ? SITE_NAME : 'Wall of Marketing');
$site_url = $site_settings['site_url'] ?? (defined('SITE_URL') ? SITE_URL : 'https://wallofmarketing.co');
$site_url = rtrim($site_url, '/'); // Remove trailing slash

// Initialize SEO variables with defaults from database
$title = $site_settings['meta_title'] ?? ($site_name . ' - Transform Your Digital Presence');
$description = $site_settings['meta_description'] ?? 'Leading digital marketing agency providing SEO, social media marketing, PPC, and comprehensive digital solutions.';
$keywords = $site_settings['meta_keywords'] ?? 'digital marketing, SEO, social media, PPC, content marketing';
$image = $site_url . '/assets/images/og-image.jpg';
$url = $site_url . $_SERVER['REQUEST_URI'];
$type = 'website';
$author = $site_name;
$published_date = date('c');
$modified_date = date('c');
$category = '';

// If custom SEO data is provided (for blog posts, case studies, etc.)
if (isset($customSeoData) && !empty($customSeoData)) {
    $title = $customSeoData['title'] ?? $title;
    $description = $customSeoData['description'] ?? $description;
    $keywords = $customSeoData['keywords'] ?? $keywords;
    $image = $customSeoData['image'] ?? $image;
    $url = $customSeoData['url'] ?? $url;
    $type = $customSeoData['type'] ?? $type;
    $author = $customSeoData['author'] ?? $author;
    $published_date = $customSeoData['published_date'] ?? $published_date;
    $modified_date = $customSeoData['modified_date'] ?? $modified_date;
    $category = $customSeoData['category'] ?? '';
} else {
    // Try to get page-specific SEO from database
    if (isset($page) && isset($db) && $db instanceof PDO) {
        try {
            $stmt = $db->prepare("SELECT * FROM seo_meta WHERE page_name = ?");
            $stmt->execute([$page]);
            $page_seo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($page_seo) {
                $title = $page_seo['meta_title'] ?? $title;
                $description = $page_seo['meta_description'] ?? $description;
                $keywords = $page_seo['meta_keywords'] ?? $keywords;
                $image = $page_seo['og_image'] ?? $image;
                $url = $page_seo['canonical_url'] ?? $url;
            }
        } catch(PDOException $e) {
            error_log("SEO Meta page query error: " . $e->getMessage());
        }
    }

    // Page-specific defaults (fallback if no database entry)
    if (isset($page)) {
        switch($page) {
            case 'index':
            case 'home':
                $title = $site_name . ' - Transform Your Digital Presence';
                $description = 'Leading digital marketing agency specializing in SEO, social media marketing, PPC, and content marketing. Drive real results for your business.';
                $keywords = 'digital marketing agency, SEO services, social media marketing, PPC advertising, content marketing';
                break;

            case 'about':
                $title = 'About Us - ' . $site_name;
                $description = 'Learn about our team, mission, and values. We are passionate about helping businesses succeed online through innovative digital marketing strategies.';
                $keywords = 'about us, digital marketing team, marketing agency, our story';
                break;

            case 'services':
                $title = 'Our Services - Digital Marketing Solutions | ' . $site_name;
                $description = 'Comprehensive digital marketing services including SEO, social media marketing, PPC, content marketing, email marketing, and analytics.';
                $keywords = 'digital marketing services, SEO optimization, social media management, PPC campaigns, content strategy';
                break;

            case 'blogs':
            case 'blog':
                $title = 'Blog - Latest Digital Marketing Insights | ' . $site_name;
                $description = 'Stay updated with the latest digital marketing trends, tips, and strategies from industry experts. Learn how to grow your business online.';
                $keywords = 'digital marketing blog, SEO tips, social media strategies, marketing guides, industry insights';
                $type = 'blog';
                break;

            case 'contact':
                $title = 'Contact Us - Get In Touch | ' . $site_name;
                $description = 'Get in touch with our team. We\'re here to help you grow your business online. Schedule a free consultation today.';
                $keywords = 'contact us, get in touch, digital marketing consultation, free quote';
                break;

            case 'case-studies':
                $title = 'Case Studies - Our Success Stories | ' . $site_name;
                $description = 'Explore our portfolio of successful digital marketing campaigns and case studies. See how we helped businesses achieve their goals.';
                $keywords = 'case studies, success stories, digital marketing results, portfolio, client testimonials';
                break;

            case 'resources':
                $title = 'Free Resources - Marketing Tools & Templates | ' . $site_name;
                $description = 'Download free digital marketing resources, templates, guides, and tools to help grow your business.';
                $keywords = 'marketing resources, free templates, marketing guides, business tools';
                break;
        }
    }
}

// Escape output for security
$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
$keywords = htmlspecialchars($keywords, ENT_QUOTES, 'UTF-8');
$url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
$image = htmlspecialchars($image, ENT_QUOTES, 'UTF-8');
$author = htmlspecialchars($author, ENT_QUOTES, 'UTF-8');

// Function to render SEO meta tags (call this in <head>)
function renderSeoMeta() {
    global $title, $description, $keywords, $url, $image, $type, $author, $published_date, $modified_date, $category, $site_name, $site_url, $site_settings;
    ?>
<!-- Basic Meta Tags -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title><?= $title ?></title>
<meta name="description" content="<?= $description ?>">
<meta name="keywords" content="<?= $keywords ?>">
<meta name="author" content="<?= $author ?>">
<link rel="canonical" href="<?= $url ?>">

<!-- Open Graph / Facebook Meta Tags -->
<meta property="og:type" content="<?= $type ?>">
<meta property="og:url" content="<?= $url ?>">
<meta property="og:title" content="<?= $title ?>">
<meta property="og:description" content="<?= $description ?>">
<meta property="og:image" content="<?= $image ?>">
<meta property="og:site_name" content="<?= $site_name ?>">
<?php if (!empty($published_date)): ?>
<meta property="article:published_time" content="<?= $published_date ?>">
<?php endif; ?>
<?php if (!empty($modified_date)): ?>
<meta property="article:modified_time" content="<?= $modified_date ?>">
<?php endif; ?>
<?php if (!empty($category)): ?>
<meta property="article:section" content="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>

<!-- Twitter Card Meta Tags -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="<?= $url ?>">
<meta name="twitter:title" content="<?= $title ?>">
<meta name="twitter:description" content="<?= $description ?>">
<meta name="twitter:image" content="<?= $image ?>">

<!-- Additional SEO Meta Tags -->
<meta name="robots" content="index, follow">
<meta name="googlebot" content="index, follow">
<meta name="language" content="English">
<meta name="revisit-after" content="7 days">
<meta name="rating" content="general">

<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="<?= $site_url ?>/assets/images/favicon.ico">
<link rel="apple-touch-icon" sizes="180x180" href="<?= $site_url ?>/assets/images/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?= $site_url ?>/assets/images/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?= $site_url ?>/assets/images/favicon-16x16.png">

<!-- Theme Color -->
<?php if (!empty($site_settings['theme_color'])): ?>
<meta name="theme-color" content="<?= htmlspecialchars($site_settings['theme_color'], ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>

<!-- Structured Data (JSON-LD) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "<?= $type === 'article' ? 'Article' : 'WebPage' ?>",
  "headline": "<?= addslashes($title) ?>",
  "description": "<?= addslashes($description) ?>",
  "image": "<?= $image ?>",
  "url": "<?= $url ?>",
  "publisher": {
    "@type": "Organization",
    "name": "<?= $site_name ?>",
    "logo": {
      "@type": "ImageObject",
      "url": "<?= $site_url ?><?= $site_settings['site_logo'] ?? '/assets/images/Logo.png' ?>"
    }
  },
  "datePublished": "<?= $published_date ?>",
  "dateModified": "<?= $modified_date ?>"
}
</script>
    <?php
}

// Alternative: Direct output method (for backward compatibility)
// If this file is included in <head>, you can directly output:
if (!function_exists('outputSeoMeta')) {
    function outputSeoMeta() {
        renderSeoMeta();
    }
}
?>