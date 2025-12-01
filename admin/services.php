<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$page_title = 'Manage Services';

// Handle add/edit
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $description = $_POST['description'] ?? '';
    $icon = $_POST['icon'] ?? 'fas fa-cog';
    $status = $_POST['status'] ?? 'active';
    $services_info = json_encode(array_filter(array_map('trim', explode(',', $_POST['services_info'] ?? ''))));
    $featured_image = $_POST['featured_image'] ?? '';
    $video_url = $_POST['video_url'] ?? null;
    $meta_description = $_POST['meta_description'] ?? '';
    
    // Process gallery images (comma-separated URLs)
    $gallery_urls = array_filter(array_map('trim', explode(',', $_POST['gallery_images'] ?? '')));
    $gallery_images = !empty($gallery_urls) ? json_encode($gallery_urls) : null;
    
    // Process process steps (4 steps with title and description)
    $process_steps = [];
    for($i = 1; $i <= 4; $i++) {
        $step_title = $_POST["step_{$i}_title"] ?? '';
        $step_desc = $_POST["step_{$i}_desc"] ?? '';
        if(!empty($step_title) && !empty($step_desc)) {
            $process_steps[] = [
                'title' => $step_title,
                'description' => $step_desc
            ];
        }
    }
    $process_steps_json = !empty($process_steps) ? json_encode($process_steps) : null;
    
    if(isset($_POST['id']) && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE services SET title=?, slug=?, description=?, icon=?, services_info=?, featured_image=?, video_url=?, gallery_images=?, process_steps=?, meta_description=?, status=? WHERE id=?");
        $stmt->execute([$title, $slug, $description, $icon, $services_info, $featured_image, $video_url, $gallery_images, $process_steps_json, $meta_description, $status, $id]);
        $msg = 'updated';
    } else {
        $stmt = $db->prepare("INSERT INTO services (title, slug, description, icon, services_info, featured_image, video_url, gallery_images, process_steps, meta_description, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $description, $icon, $services_info, $featured_image, $video_url, $gallery_images, $process_steps_json, $meta_description, $status]);
        $msg = 'added'; 
    }
    
    header("Location: services.php?msg=$msg");
    exit();
}

// Handle delete
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM services WHERE id = ?")->execute([$id]);
    header('Location: services.php?msg=deleted');
    exit();
}

// Get service for editing
$edit_service = null;
if(isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $edit_service = $stmt->fetch();
}

$services = $db->query("SELECT * FROM services ORDER BY id DESC")->fetchAll();
?>
<?php include 'includes/layout-start.php'; ?>
    <div class="page-header">
        <h1>Manage Services</h1>
        <p>Add, edit, or delete services</p>
    </div>
    
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php 
            if($_GET['msg'] == 'added') echo 'Service added successfully!';
            elseif($_GET['msg'] == 'updated') echo 'Service updated successfully!';
            elseif($_GET['msg'] == 'deleted') echo 'Service deleted successfully!';
            ?>
        </div>
    <?php endif; ?>
    
    <div class="content-grid" style="grid-template-columns: 1.2fr 1fr; gap: 20px;">
        <!-- Add/Edit Form -->
        <div class="content-card" style="max-height: 112vh;  overflow-y: auto;">
            <div class="card-header" style="top: 0; background: white; z-index: 10;">
                <h3><?php echo $edit_service ? 'Edit Service' : 'Add New Service'; ?></h3>
            </div>

            <form method="POST" style="padding: 20px;">
                <?php if($edit_service): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_service['id']); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Service Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" value="<?php echo $edit_service ? htmlspecialchars($edit_service['title']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Slug</label>
                    <input type="text" name="slug" class="form-control" value="<?php echo $edit_service ? htmlspecialchars($edit_service['slug']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Description <span class="required">*</span></label>
                    <textarea name="description" class="form-control" rows="4" required><?php echo $edit_service ? htmlspecialchars($edit_service['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Icon Class <span class="required">*</span></label>
                    <input type="text" name="icon" class="form-control" value="<?php echo $edit_service ? htmlspecialchars($edit_service['icon']) : 'fas fa-cog'; ?>" required>
                    <small>Font Awesome icon class (e.g., fas fa-search)</small>
                </div>
                
                <div class="form-group">
                    <label>Features (comma separated) <span class="required">*</span></label>
                    <textarea name="services_info" class="form-control" rows="3" required><?php echo $edit_service ? implode(', ', json_decode($edit_service['services_info'], true) ?? []) : ''; ?></textarea>
                    <small>e.g., Feature 1, Feature 2, Feature 3</small>
                </div>
            
                <div class="form-group">
                    <label>Featured Image URL</label>
                    <input type="url" name="featured_image" class="form-control" value="<?php echo $edit_service ? htmlspecialchars($edit_service['featured_image']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Video URL (YouTube/Vimeo Embed)</label>
                    <input type="url" name="video_url" class="form-control" value="<?php echo $edit_service ? htmlspecialchars($edit_service['video_url']) : ''; ?>" placeholder="https://www.youtube.com/embed/VIDEO_ID">
                    <small>Use embed URL format</small>
                </div>
                
                <div class="form-group">
                    <label>Gallery Images (comma separated URLs)</label>
                    <textarea name="gallery_images" class="form-control" rows="3"><?php echo $edit_service && $edit_service['gallery_images'] ? implode(', ', json_decode($edit_service['gallery_images'], true) ?? []) : ''; ?></textarea>
                    <small>Separate multiple image URLs with commas</small>
                </div>
                
                <div class="form-group">
                    <label>Meta Description (SEO)</label>
                    <textarea name="meta_description" class="form-control" rows="2"><?php echo $edit_service ? htmlspecialchars($edit_service['meta_description']) : ''; ?></textarea>
                </div>
                
                <hr style="margin: 20px 0;">
                <h4 style="margin-bottom: 15px;">Process Steps (Optional)</h4>
                
                <?php 
                $process_steps = [];
                if($edit_service && $edit_service['process_steps']) {
                    $process_steps = json_decode($edit_service['process_steps'], true) ?? [];
                }
                for($i = 1; $i <= 4; $i++): 
                    $step = $process_steps[$i-1] ?? ['title' => '', 'description' => ''];
                ?>
                <div style="background: #f8f9fa; padding: 15px; margin-bottom: 15px; border-radius: 8px;">
                    <h5>Step <?php echo $i; ?></h5>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="step_<?php echo $i; ?>_title" class="form-control" value="<?php echo htmlspecialchars($step['title']); ?>" placeholder="e.g., Discovery & Strategy">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="step_<?php echo $i; ?>_desc" class="form-control" rows="2"><?php echo htmlspecialchars($step['description']); ?></textarea>
                    </div>
                </div>
                <?php endfor; ?>
                
                <hr style="margin: 20px 0;">
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="active" <?php echo ($edit_service && $edit_service['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($edit_service && $edit_service['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo $edit_service ? 'Update' : 'Add'; ?> Service
                </button>
                <?php if($edit_service): ?>
                    <a href="services.php" class="btn btn-primary" style="background: #666;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Services List -->
        <div class="content-card">
            <div class="card-header">
                <h3>All Services</h3>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Icon</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Details</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($services as $service): ?>
                        <tr>
                            <td><i class="<?php echo htmlspecialchars($service['icon']); ?>"></i></td>
                            <td>
                                <?php echo htmlspecialchars($service['title']); ?>
                                <?php if($service['video_url']): ?>
                                    <i class="fas fa-video" style="color: #666; margin-left: 5px;" title="Has video"></i>
                                <?php endif; ?>
                                <?php if($service['gallery_images']): ?>
                                    <i class="fas fa-images" style="color: #666; margin-left: 5px;" title="Has gallery"></i>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge <?php echo $service['status'] == 'active' ? 'badge-success' : 'badge-warning'; ?>"><?php echo htmlspecialchars($service['status']); ?></span></td>
                            <td>
                                <a href="/service-detail?slug=<?php echo htmlspecialchars($service['slug']); ?>" target="_blank" class="btn-icon" title="View Page">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </td>
                            <td>
                                <a href="?edit=<?php echo htmlspecialchars($service['id']); ?>" class="btn-icon"><i class="fas fa-edit"></i></a>
                                <a href="?delete=<?php echo htmlspecialchars($service['id']); ?>" class="btn-icon btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/layout-end.php'; ?>
