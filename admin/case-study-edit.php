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
                technologies_used = ?, project_duration = ?, team_size = ?, budget_range = ?, 
                testimonial = ?, testimonial_author = ?, testimonial_position = ?, key_results = ?, 
                meta_title = ?, meta_description = ?, meta_keywords = ?, status = ?, featured = ?, 
                display_order = ? 
                WHERE id = ?");
            
            $stmt->execute([
                $title, $slug, $client_name, $industry, $featured_image, $banner_image, $excerpt,
                $challenge, $solution, $results, $technologies_used, $project_duration, $team_size,
                $budget_range, $testimonial, $testimonial_author, $testimonial_position, $key_results_json,
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
    <link rel="stylesheet" href="/assets/css/admin.css">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js"></script>
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
                    
                    <!-- Rest of form fields pre-filled similarly -->
                    <!-- Copy remaining sections from add form and replace $_POST with $case_study values -->
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
    
    <script>
        tinymce.init({
            selector: '.tinymce',
            height: 400,
            menubar: false,
            plugins: 'lists link image code',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code'
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
</body>
</html>
