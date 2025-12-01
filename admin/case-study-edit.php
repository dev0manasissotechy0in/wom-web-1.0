<?php
require_once '../config/config.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$id = (int)$_GET['id'];
$errors = [];

// Get existing case study
$stmt = $db->prepare("SELECT * FROM case_studies WHERE id = ?");
$stmt->execute([$id]);
$case_study = $stmt->fetch();

if(!$case_study) {
    header('Location: case-studies.php');
    exit();
}

// Decode JSON
$key_results = json_decode($case_study['key_results'], true) ?: [];
$key_results_text = implode("\n", $key_results);

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Same validation as add form
    $title = sanitize($_POST['title']);
    $slug = sanitize($_POST['slug']);
    $client_name = sanitize($_POST['client_name']);
    $industry = sanitize($_POST['industry']);
    $featured_image = sanitize($_POST['featured_image']);
    $banner_image = sanitize($_POST['banner_image']);
    $excerpt = sanitize($_POST['excerpt']);
    $challenge = $_POST['challenge'];
    $solution = $_POST['solution'];
    $results = $_POST['results'];
    $technologies = sanitize($_POST['technologies_used']);
    $duration = sanitize($_POST['project_duration']);
    $services_provided = sanitize($_POST['team_size']);
    $budget = sanitize($_POST['budget_range']);
    $testimonial = sanitize($_POST['testimonial']);
    $testimonial_author = sanitize($_POST['testimonial_author']);
    $testimonial_position = sanitize($_POST['testimonial_position']);
    $meta_title = sanitize($_POST['meta_title']);
    $meta_description = sanitize($_POST['meta_description']);
    $meta_keywords = sanitize($_POST['meta_keywords']);
    $status = $_POST['status'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $display_order = (int)$_POST['display_order'];
    
    // Key results
    $key_results = [];
    if(!empty($_POST['key_results'])) {
        $key_results = array_filter(array_map('trim', explode("\n", $_POST['key_results'])));
    }
    $key_results_json = json_encode($key_results);
    
    // Validation
    if(empty($title)) $errors[] = 'Title is required';
    if(empty($slug)) $errors[] = 'Slug is required';
    
    // Check if slug exists (excluding current case study)
    if(empty($errors)) {
        $check = $db->prepare("SELECT id FROM case_studies WHERE slug = ? AND id != ?");
        $check->execute([$slug, $id]);
        if($check->fetch()) {
            $errors[] = 'Slug already exists.';
        }
    }
    
    // Update if no errors
    if(empty($errors)) {
        try {
            $stmt = $db->prepare("UPDATE case_studies SET 
                title = ?, slug = ?, client_name = ?, industry = ?, featured_image = ?, 
                banner_image = ?, excerpt = ?, challenge = ?, solution = ?, results = ?, 
                technologies = ?, duration = ?, services_provided = ?, budget = ?, 
                testimonial = ?, testimonial_author = ?, testimonial_position = ?, key_results = ?, 
                meta_title = ?, meta_description = ?, meta_keywords = ?, status = ?, featured = ?, 
                display_order = ? 
                WHERE id = ?");
            
            $stmt->execute([
                $title, $slug, $client_name, $industry, $featured_image, $banner_image, $excerpt,
                $challenge, $solution, $results, $technologies, $duration, $services_provided,
                $budget, $testimonial, $testimonial_author, $testimonial_position, $key_results_json,
                $meta_title, $meta_description, $meta_keywords, $status, $featured, $display_order, $id
            ]);
            
            header('Location: case-studies.php?msg=updated');
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
    <title>Edit Case Study - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>Edit Case Study</h1>
                <div style="display: flex; gap: 10px;">
                    <a href="/case-studies/<?php echo $case_study['slug']; ?>" class="btn-secondary" target="_blank">
                        <i class="fas fa-eye"></i> Preview
                    </a>
                    <a href="case-studies.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            
            <?php if(!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach($errors as $error): ?>
                        <div><?php echo $error; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- SAME FORM AS ADD, but pre-filled with $case_study data -->
            <form method="POST" class="case-study-form">
                <div class="form-grid">
                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Title <span class="required">*</span></label>
                                <input type="text" name="title" class="form-control" required 
                                       value="<?php echo htmlspecialchars($case_study['title']); ?>"
                                       onkeyup="generateSlug(this.value)">
                            </div>
                            
                            <div class="form-group">
                                <label>Slug <span class="required">*</span></label>
                                <input type="text" name="slug" id="slug" class="form-control" required
                                       value="<?php echo htmlspecialchars($case_study['slug']); ?>">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Client Name</label>
                                    <input type="text" name="client_name" class="form-control"
                                           value="<?php echo htmlspecialchars($case_study['client_name']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Industry</label>
                                    <select name="industry" class="form-control">
                                        <option value="">Select Industry</option>
                                        <?php 
                                        $industries = ['E-commerce', 'Technology', 'Health & Fitness', 'SaaS', 'Finance', 'Real Estate', 'Education', 'Retail', 'Manufacturing', 'Other'];
                                        foreach($industries as $ind): 
                                        ?>
                                            <option value="<?php echo $ind; ?>" <?php echo $case_study['industry'] == $ind ? 'selected' : ''; ?>>
                                                <?php echo $ind; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Excerpt</label>
                                <textarea name="excerpt" class="form-control" rows="3"><?php echo htmlspecialchars($case_study['excerpt']); ?></textarea>
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
                                <label>Featured Image URL</label>
                                <input type="url" name="featured_image" class="form-control"
                                       value="<?php echo htmlspecialchars($case_study['featured_image']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Banner Image URL</label>
                                <input type="url" name="banner_image" class="form-control"
                                       value="<?php echo htmlspecialchars($case_study['banner_image']); ?>">
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
                            <textarea name="challenge" class="editor-content"><?php echo htmlspecialchars($case_study['challenge']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Our Solution</label>
                            <textarea name="solution" class="editor-content"><?php echo htmlspecialchars($case_study['solution']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>The Results</label>
                            <textarea name="results" class="editor-content"><?php echo htmlspecialchars($case_study['results']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Key Results</label>
                            <textarea name="key_results" class="form-control" rows="5"><?php echo htmlspecialchars($key_results_text); ?></textarea>
                            <small>One result per line</small>
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
                                       value="<?php echo htmlspecialchars($case_study['technologies']); ?>">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Project Duration</label>
                                    <input type="text" name="project_duration" class="form-control"
                                           value="<?php echo htmlspecialchars($case_study['duration']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Team Size</label>
                                    <input type="text" name="team_size" class="form-control"
                                           value="<?php echo htmlspecialchars($case_study['services_provided']); ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Budget Range</label>
                                <input type="text" name="budget_range" class="form-control"
                                       value="<?php echo htmlspecialchars($case_study['budget']); ?>">
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
                                <textarea name="testimonial" class="form-control" rows="4"><?php echo htmlspecialchars($case_study['testimonial']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Author Name</label>
                                <input type="text" name="testimonial_author" class="form-control"
                                       value="<?php echo htmlspecialchars($case_study['testimonial_author']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Author Position</label>
                                <input type="text" name="testimonial_position" class="form-control"
                                       value="<?php echo htmlspecialchars($case_study['testimonial_position']); ?>">
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
                                   value="<?php echo htmlspecialchars($case_study['meta_title']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Meta Description</label>
                            <textarea name="meta_description" class="form-control" rows="3"><?php echo htmlspecialchars($case_study['meta_description']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Meta Keywords</label>
                            <input type="text" name="meta_keywords" class="form-control"
                                   value="<?php echo htmlspecialchars($case_study['meta_keywords']); ?>">
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
                                    <option value="draft" <?php echo $case_study['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo $case_study['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Display Order</label>
                                <input type="number" name="display_order" class="form-control" 
                                       value="<?php echo (int)$case_study['display_order']; ?>" min="0">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="featured" value="1" 
                                       <?php echo $case_study['featured'] ? 'checked' : ''; ?>>
                                <span>Mark as Featured</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Update Case Study
                    </button>
                    <a href="case-studies.php" class="btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- CKEditor for rich text editing -->
    <script src="https://cdn.ckeditor.com/4.22.1/standard-all/ckeditor.js"></script>
    <script>
        // Initialize CKEditor for all editor-content textareas
        window.addEventListener('load', function() {
            if (typeof CKEDITOR !== 'undefined') {
                document.querySelectorAll('.editor-content').forEach(function(textarea) {
                    CKEDITOR.replace(textarea.id || textarea.name, {
                height: 400,
                extraPlugins: 'embed,image2,codesnippet',
                removePlugins: 'elementspath',
                resize_enabled: true,
                toolbar: [
                    { name: 'document', items: ['Source', '-', 'Preview'] },
                    { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', '-', 'Undo', 'Redo'] },
                    '/',
                    { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat'] },
                    { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote'] },
                    { name: 'links', items: ['Link', 'Unlink'] },
                    { name: 'insert', items: ['Image', 'Table', 'HorizontalRule'] },
                    '/',
                    { name: 'styles', items: ['Format', 'Font', 'FontSize'] },
                    { name: 'colors', items: ['TextColor', 'BGColor'] },
                    { name: 'tools', items: ['Maximize'] }
                ]
                });
            });
            } else {
                console.error('CKEditor failed to load');
            }
        });
        
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
