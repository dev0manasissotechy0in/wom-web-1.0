<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$page_title = 'Case Study Categories';

// Handle add/edit
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $description = $_POST['description'] ?? '';
    $icon = $_POST['icon'] ?? 'fas fa-folder';
    $display_order = $_POST['display_order'] ?? 0;
    
    if(isset($_POST['id']) && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE case_study_categories SET name=?, slug=?, description=?, icon=?, display_order=? WHERE id=?");
        $stmt->execute([$name, $slug, $description, $icon, $display_order, $id]);
        $msg = 'updated';
    } else {
        $stmt = $db->prepare("INSERT INTO case_study_categories (name, slug, description, icon, display_order) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $description, $icon, $display_order]);
        $msg = 'added';
    }
    
    header("Location: case-study-categories.php?msg=$msg");
    exit();
}

// Handle delete
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM case_study_categories WHERE id = ?")->execute([$id]);
    header('Location: case-study-categories.php?msg=deleted');
    exit();
}

// Get category for editing
$edit_category = null;
if(isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM case_study_categories WHERE id = ?");
    $stmt->execute([$id]);
    $edit_category = $stmt->fetch();
}

$categories = $db->query("SELECT c.*, (SELECT COUNT(*) FROM case_studies cs WHERE cs.category = c.name) as case_count FROM case_study_categories c ORDER BY display_order, name")->fetchAll();
?>
<?php include 'includes/layout-start.php'; ?>
    <div class="page-header">
        <h1>Case Study Categories</h1>
        <p>Manage categories for case studies</p>
    </div>
    
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php 
            if($_GET['msg'] == 'added') echo 'Category added successfully!';
            elseif($_GET['msg'] == 'updated') echo 'Category updated successfully!';
            elseif($_GET['msg'] == 'deleted') echo 'Category deleted successfully!';
            ?>
        </div>
    <?php endif; ?>
    
    <div class="content-grid" style="grid-template-columns: 1fr 2fr;">
        <!-- Add/Edit Form -->
        <div class="content-card">
            <div class="card-header">
                <h3><?php echo $edit_category ? 'Edit Category' : 'Add New Category'; ?></h3>
            </div>

            <form method="POST" style="padding: 20px;">
                <?php if($edit_category): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_category['id']); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Slug <span class="required">*</span></label>
                    <input type="text" name="slug" class="form-control" value="<?php echo $edit_category ? htmlspecialchars($edit_category['slug']) : ''; ?>" required>
                    <small>URL-friendly version (e.g., saas-marketing)</small>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo $edit_category ? htmlspecialchars($edit_category['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Icon Class</label>
                    <input type="text" name="icon" class="form-control" value="<?php echo $edit_category ? htmlspecialchars($edit_category['icon']) : 'fas fa-folder'; ?>">
                    <small>Font Awesome icon (e.g., fas fa-cloud)</small>
                </div>
                
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" class="form-control" value="<?php echo $edit_category ? htmlspecialchars($edit_category['display_order']) : '0'; ?>">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo $edit_category ? 'Update' : 'Add'; ?> Category
                </button>
                <?php if($edit_category): ?>
                    <a href="case-study-categories.php" class="btn btn-primary" style="background: #666;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Categories List -->
        <div class="content-card">
            <div class="card-header">
                <h3>All Categories (<?php echo count($categories); ?>)</h3>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Icon</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Case Studies</th>
                            <th>Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($categories as $category): ?>
                        <tr>
                            <td><i class="<?php echo htmlspecialchars($category['icon']); ?>"></i></td>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><code><?php echo htmlspecialchars($category['slug']); ?></code></td>
                            <td><span class="badge badge-info"><?php echo $category['case_count']; ?></span></td>
                            <td><?php echo htmlspecialchars($category['display_order']); ?></td>
                            <td>
                                <a href="?edit=<?php echo htmlspecialchars($category['id']); ?>" class="btn-icon"><i class="fas fa-edit"></i></a>
                                <a href="?delete=<?php echo htmlspecialchars($category['id']); ?>" class="btn-icon btn-danger" onclick="return confirm('Are you sure? This will not delete case studies.')"><i class="fas fa-trash"></i></a>
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
