<?php
require_once '../config/config.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$errors = [];
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $title = sanitize($_POST['title']);
    $slug = sanitize($_POST['slug']);
    $client_name = sanitize($_POST['client_name']);
    $industry = sanitize($_POST['industry']);
    $featured_image = sanitize($_POST['featured_image']);
    $banner_image = sanitize($_POST['banner_image']);
    $excerpt = sanitize($_POST['excerpt']);
    $challenge = $_POST['challenge']; // Allow HTML
    $solution = $_POST['solution'];
    $results = $_POST['results'];
    $technologies_used = sanitize($_POST['technologies_used']);
    $project_duration = sanitize($_POST['project_duration']);
    $team_size = sanitize($_POST['team_size']);
    $budget_range = sanitize($_POST['budget_range']);
    $testimonial = sanitize($_POST['testimonial']);
    $testimonial_author = sanitize($_POST['testimonial_author']);
    $testimonial_position = sanitize($_POST['testimonial_position']);
    $meta_title = sanitize($_POST['meta_title']);
    $meta_description = sanitize($_POST['meta_description']);
    $meta_keywords = sanitize($_POST['meta_keywords']);
    $status = $_POST['status'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $display_order = (int)$_POST['display_order'];
    
    // Key results (JSON)
    $key_results = [];
    if(!empty($_POST['key_results'])) {
        $key_results = array_filter(array_map('trim', explode("\n", $_POST['key_results'])));
    }
    $key_results_json = json_encode($key_results);
    
    // Validation
    if(empty($title)) $errors[] = 'Title is required';
    if(empty($slug)) $errors[] = 'Slug is required';
    if(empty($client_name)) $errors[] = 'Client name is required';
    if(empty($industry)) $errors[] = 'Industry is required';
    
    // Check if slug exists
    if(empty($errors)) {
        $check = $db->prepare("SELECT id FROM case_studies WHERE slug = ?");
        $check->execute([$slug]);
        if($check->fetch()) {
            $errors[] = 'Slug already exists. Please use a different slug.';
        }
    }
    
    // Insert if no errors
    if(empty($errors)) {
        try {
            $stmt = $db->prepare("INSERT INTO case_studies (
                title, slug, client_name, industry, featured_image, banner_image, excerpt, 
                challenge, solution, results, technologies_used, project_duration, team_size, 
                budget_range, testimonial, testimonial_author, testimonial_position, key_results, 
                meta_title, meta_description, meta_keywords, status, featured, display_order
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $title, $slug, $client_name, $industry, $featured_image, $banner_image, $excerpt,
                $challenge, $solution, $results, $technologies_used, $project_duration, $team_size,
                $budget_range, $testimonial, $testimonial_author, $testimonial_position, $key_results_json,
                $meta_title, $meta_description, $meta_keywords, $status, $featured, $display_order
            ]);
            
            header('Location: case-studies.php?msg=added');
            exit();
        } catch(Exception $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Case Study - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js"></script>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>Add New Case Study</h1>
                <a href="case-studies.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
            
            <?php if(!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <ul style="margin: 10px 0 0 20px;">
                        <?php foreach($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="case-study-form">
                <div class="form-grid">
                    <!-- Basic Information -->
                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Title <span class="required">*</span></label>
                                <input type="text" name="title" class="form-control" required 
                                       value="<?php echo $_POST['title'] ?? ''; ?>"
                                       onkeyup="generateSlug(this.value)">
                            </div>
                            
                            <div class="form-group">
                                <label>Slug <span class="required">*</span></label>
                                <input type="text" name="slug" id="slug" class="form-control" required
                                       value="<?php echo $_POST['slug'] ?? ''; ?>">
                                <small>URL-friendly version. Example: ecommerce-success-story</small>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Client Name <span class="required">*</span></label>
                                    <input type="text" name="client_name" class="form-control" required
                                           value="<?php echo $_POST['client_name'] ?? ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Industry <span class="required">*</span></label>
                                    <select name="industry" class="form-control" required>
                                        <option value="">Select Industry</option>
                                        <option value="E-commerce">E-commerce</option>
                                        <option value="Technology">Technology</option>
                                        <option value="Health & Fitness">Health & Fitness</option>
                                        <option value="SaaS">SaaS</option>
                                        <option value="Finance">Finance</option>
                                        <option value="Real Estate">Real Estate</option>
                                        <option value="Education">Education</option>
                                        <option value="Retail">Retail</option>
                                        <option value="Manufacturing">Manufacturing</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Excerpt <span class="required">*</span></label>
                                <textarea name="excerpt" class="form-control" rows="3" required><?php echo $_POST['excerpt'] ?? ''; ?></textarea>
                                <small>Brief summary (150-200 characters)</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Images -->
                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-image"></i> Images</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Featured Image URL <span class="required">*</span></label>
                                <input type="url" name="featured_image" class="form-control" required
                                       value="<?php echo $_POST['featured_image'] ?? ''; ?>">
                                <small>Used in listings and cards (600x400px recommended)</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Banner Image URL</label>
                                <input type="url" name="banner_image" class="form-control"
                                       value="<?php echo $_POST['banner_image'] ?? ''; ?>">
                                <small>Header background (1920x600px recommended)</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Content Sections -->
                <div class="content-card">
                    <div class="card-header">
                        <h3><i class="fas fa-file-alt"></i> Case Study Content</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>The Challenge</label>
                            <textarea name="challenge" class="tinymce"><?php echo $_POST['challenge'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Our Solution</label>
                            <textarea name="solution" class="tinymce"><?php echo $_POST['solution'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>The Results</label>
                            <textarea name="results" class="tinymce"><?php echo $_POST['results'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Key Results <i class="fas fa-info-circle" title="One result per line"></i></label>
                            <textarea name="key_results" class="form-control" rows="5" 
                                      placeholder="300% revenue increase&#10;250% organic traffic growth&#10;180% improved PPC ROI"><?php echo $_POST['key_results'] ?? ''; ?></textarea>
                            <small>Enter one result per line (e.g., "300% revenue increase")</small>
                        </div>
                    </div>
                </div>
                
                <!-- Project Details -->
                <div class="form-grid">
                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-cogs"></i> Project Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Technologies Used</label>
                                <input type="text" name="technologies_used" class="form-control"
                                       value="<?php echo $_POST['technologies_used'] ?? ''; ?>"
                                       placeholder="Google Ads, SEMrush, WordPress, HubSpot">
                                <small>Comma-separated list</small>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Project Duration</label>
                                    <input type="text" name="project_duration" class="form-control"
                                           value="<?php echo $_POST['project_duration'] ?? ''; ?>"
                                           placeholder="6 months">
                                </div>
                                
                                <div class="form-group">
                                    <label>Team Size</label>
                                    <input type="text" name="team_size" class="form-control"
                                           value="<?php echo $_POST['team_size'] ?? ''; ?>"
                                           placeholder="5 specialists">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Budget Range</label>
                                <input type="text" name="budget_range" class="form-control"
                                       value="<?php echo $_POST['budget_range'] ?? ''; ?>"
                                       placeholder="$10,000 - $25,000">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial -->
                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-quote-right"></i> Client Testimonial</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Testimonial</label>
                                <textarea name="testimonial" class="form-control" rows="4"><?php echo $_POST['testimonial'] ?? ''; ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Author Name</label>
                                <input type="text" name="testimonial_author" class="form-control"
                                       value="<?php echo $_POST['testimonial_author'] ?? ''; ?>"
                                       placeholder="John Smith">
                            </div>
                            
                            <div class="form-group">
                                <label>Author Position</label>
                                <input type="text" name="testimonial_position" class="form-control"
                                       value="<?php echo $_POST['testimonial_position'] ?? ''; ?>"
                                       placeholder="CEO, Company Name">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- SEO Settings -->
                <div class="content-card">
                    <div class="card-header">
                        <h3><i class="fas fa-search"></i> SEO Settings</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Meta Title</label>
                            <input type="text" name="meta_title" class="form-control"
                                   value="<?php echo $_POST['meta_title'] ?? ''; ?>"
                                   maxlength="60">
                            <small>Leave empty to use case study title (60 chars max)</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Meta Description</label>
                            <textarea name="meta_description" class="form-control" rows="3" 
                                      maxlength="160"><?php echo $_POST['meta_description'] ?? ''; ?></textarea>
                            <small>Leave empty to use excerpt (160 chars max)</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Meta Keywords</label>
                            <input type="text" name="meta_keywords" class="form-control"
                                   value="<?php echo $_POST['meta_keywords'] ?? ''; ?>"
                                   placeholder="case study, digital marketing, ecommerce">
                            <small>Comma-separated keywords</small>
                        </div>
                    </div>
                </div>
                
                <!-- Publishing Options -->
                <div class="content-card">
                    <div class="card-header">
                        <h3><i class="fas fa-cog"></i> Publishing Options</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="draft">Draft</option>
                                    <option value="published" selected>Published</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Display Order</label>
                                <input type="number" name="display_order" class="form-control" 
                                       value="<?php echo $_POST['display_order'] ?? '0'; ?>" min="0">
                                <small>Lower numbers appear first</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="featured" value="1" 
                                       <?php echo isset($_POST['featured']) ? 'checked' : ''; ?>>
                                <span>Mark as Featured</span>
                            </label>
                            <small>Featured case studies appear on homepage</small>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Create Case Study
                    </button>
                    <a href="case-studies.php" class="btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '.tinymce',
            height: 400,
            menubar: false,
            plugins: 'lists link image code',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
            content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }'
        });
        
        // Generate slug from title
        function generateSlug(text) {
            const slug = text.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/--+/g, '-')
                .trim();
            document.getElementById('slug').value = slug;
        }
    </script>
    
    <style>
        .case-study-form { max-width: 1400px; margin: 0 auto; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 30px; margin-bottom: 30px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-control { width: 100%; padding: 10px 15px; border: 2px solid #e0e0e0; border-radius: 5px; font-size: 14px; }
        .form-control:focus { outline: none; border-color: #000; }
        .required { color: #f00; }
        .checkbox-label { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .checkbox-label input[type="checkbox"] { width: 20px; height: 20px; cursor: pointer; }
        .form-actions { display: flex; gap: 15px; justify-content: center; margin-top: 30px; padding: 30px; background: #f8f8f8; border-radius: 10px; }
        @media (max-width: 768px) {
            .form-grid, .form-row { grid-template-columns: 1fr; }
        }
    </style>
</body>
</html>
