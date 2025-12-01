<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$page_title = 'Case Study Tags';

// Handle add/edit
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if(isset($_POST['id']) && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE case_study_tags SET name=?, slug=?, description=? WHERE id=?");
        $stmt->execute([$name, $slug, $description, $id]);
        $msg = 'updated';
    } else {
        $stmt = $db->prepare("INSERT INTO case_study_tags (name, slug, description) VALUES (?, ?, ?)");
        $stmt->execute([$name, $slug, $description]);
        $msg = 'added';
    }
    
    header("Location: case-study-tags.php?msg=$msg");
    exit();
}

// Handle delete
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Get tag name
    $tag = $db->prepare("SELECT name FROM case_study_tags WHERE id = ?");
    $tag->execute([$id]);
    $tagName = $tag->fetchColumn();
    
    // Remove tag from all case studies
    $stmt = $db->query("SELECT id, tags FROM case_studies WHERE tags IS NOT NULL");
    while($case = $stmt->fetch()) {
        $tags = array_filter(array_map('trim', explode(',', $case['tags'])));
        $tags = array_filter($tags, function($t) use ($tagName) {
            return $t !== $tagName;
        });
        $newTags = !empty($tags) ? implode(', ', $tags) : null;
        $update = $db->prepare("UPDATE case_studies SET tags = ? WHERE id = ?");
        $update->execute([$newTags, $case['id']]);
    }
    
    $db->prepare("DELETE FROM case_study_tags WHERE id = ?")->execute([$id]);
    header('Location: case-study-tags.php?msg=deleted');
    exit();
}

// Get tag for editing
$edit_tag = null;
if(isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM case_study_tags WHERE id = ?");
    $stmt->execute([$id]);
    $edit_tag = $stmt->fetch();
}

// Get all tags with case study counts
$tags_stmt = $db->query("SELECT * FROM case_study_tags ORDER BY name");
$tags = [];
foreach($tags_stmt->fetchAll() as $tag) {
    // Count case studies with this tag
    $count_stmt = $db->prepare("SELECT COUNT(*) FROM case_studies WHERE CONCAT(',', REPLACE(tags, ' ', ''), ',') LIKE CONCAT('%,', REPLACE(?, ' ', ''), ',%') OR tags = ?");
    $count_stmt->execute([$tag['name'], $tag['name']]);
    $tag['case_count'] = $count_stmt->fetchColumn();
    $tags[] = $tag;
}
?>
<?php include 'includes/layout-start.php'; ?>
    <div class="page-header">
        <h1>Case Study Tags</h1>
        <p>Manage tags for case studies</p>
    </div>
    
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php 
            if($_GET['msg'] == 'added') echo 'Tag added successfully!';
            elseif($_GET['msg'] == 'updated') echo 'Tag updated successfully!';
            elseif($_GET['msg'] == 'deleted') echo 'Tag deleted successfully and removed from all case studies!';
            ?>
        </div>
    <?php endif; ?>
    
    <div class="content-grid" style="grid-template-columns: 1fr 2fr;">
        <!-- Add/Edit Form -->
        <div class="content-card">
            <div class="card-header">
                <h3><?php echo $edit_tag ? 'Edit Tag' : 'Add New Tag'; ?></h3>
            </div>

            <form method="POST" style="padding: 20px;">
                <?php if($edit_tag): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_tag['id']); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?php echo $edit_tag ? htmlspecialchars($edit_tag['name']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Slug <span class="required">*</span></label>
                    <input type="text" name="slug" class="form-control" value="<?php echo $edit_tag ? htmlspecialchars($edit_tag['slug']) : ''; ?>" required>
                    <small>URL-friendly version (e.g., google-ads)</small>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo $edit_tag ? htmlspecialchars($edit_tag['description']) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo $edit_tag ? 'Update' : 'Add'; ?> Tag
                </button>
                <?php if($edit_tag): ?>
                    <a href="case-study-tags.php" class="btn btn-primary" style="background: #666;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Tags List -->
        <div class="content-card">
            <div class="card-header">
                <h3>All Tags (<?php echo count($tags); ?>)</h3>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Case Studies</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($tags as $tag): ?>
                        <tr>
                            <td>
                                <i class="fas fa-tag" style="color: #666; margin-right: 5px;"></i>
                                <?php echo htmlspecialchars($tag['name']); ?>
                            </td>
                            <td><code><?php echo htmlspecialchars($tag['slug']); ?></code></td>
                            <td><span class="badge badge-info"><?php echo $tag['case_count']; ?></span></td>
                            <td>
                                <a href="?edit=<?php echo htmlspecialchars($tag['id']); ?>" class="btn-icon"><i class="fas fa-edit"></i></a>
                                <a href="?delete=<?php echo htmlspecialchars($tag['id']); ?>" class="btn-icon btn-danger" onclick="return confirm('Are you sure? This will remove the tag from all case studies.')"><i class="fas fa-trash"></i></a>
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
