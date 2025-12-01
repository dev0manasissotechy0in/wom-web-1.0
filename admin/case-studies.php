<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$page_title = 'Manage Case Studies';

// Handle delete
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM case_studies WHERE id = ?")->execute([$id]);
    header('Location: case-studies.php?msg=deleted');
    exit();
}

// Get all case studies
$case_studies = $db->query("SELECT * FROM case_studies ORDER BY display_order ASC, created_at DESC")->fetchAll();
?>
<?php include 'includes/layout-start.php'; ?>
    <div class="page-header">
        <h1>Manage Case Studies</h1>
        <a href="case-study-add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Case Study
        </a>
    </div>
    
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php 
            if($_GET['msg'] == 'added') echo 'Case study added successfully!';
            elseif($_GET['msg'] == 'updated') echo 'Case study updated successfully!';
            elseif($_GET['msg'] == 'deleted') echo 'Case study deleted successfully!';
            ?>
        </div>
    <?php endif; ?>
    
    <div class="content-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="60">Order</th>
                        <th width="80">Image</th>
                        <th>Title</th>
                        <th>Client</th>
                        <th>Industry</th>
                        <th width="100">Status</th>
                        <th width="80">Featured</th>
                        <th width="80">Views</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($case_studies as $case): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($case['display_order']); ?></td>
                        <td>
                            <img src="<?php echo htmlspecialchars($case['featured_image']); ?>" 
                                 style="width: 60px; height: 40px; object-fit: cover; border-radius: 5px;"
                                 onerror="this.src='https://via.placeholder.com/60x40/000000/FFFFFF?text=CS'">
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($case['title']); ?></strong><br>
                            <small style="color: #666;"><?php echo htmlspecialchars($case['slug']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($case['client_name']); ?></td>
                        <td><span class="badge badge-info"><?php echo htmlspecialchars($case['industry']); ?></span></td>
                        <td>
                            <span class="badge <?php echo $case['status'] == 'published' ? 'badge-success' : 'badge-warning'; ?>">
                                <?php echo ucfirst(htmlspecialchars($case['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <?php if($case['featured']): ?>
                                <i class="fas fa-star" style="color: #ffd700;"></i>
                            <?php else: ?>
                                <i class="far fa-star" style="color: #ccc;"></i>
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format($case['views']); ?></td>
                        <td>
                            <a href="/case-studies/<?php echo htmlspecialchars($case['slug']); ?>" class="btn-icon" title="View" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="case-study-edit.php?id=<?php echo htmlspecialchars($case['id']); ?>" class="btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?php echo htmlspecialchars($case['id']); ?>" class="btn-icon btn-danger" title="Delete" 
                               onclick="return confirm('Are you sure you want to delete this case study?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/layout-end.php'; ?>
