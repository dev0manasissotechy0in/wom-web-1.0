<?php
require_once '../config/config.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Case Studies - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="/assets/css/admin.css" />
    <style>
        

/* Sidebar Layout */
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    width: 230px;
    background: #000;
    z-index: 1000;
    color: #fff;
    height: 100vh;
}
.main-content {
    margin-left: 230px; /* Should match sidebar width */
    padding: 0;
    min-height: 100vh;
    background: #f5f5f5;
    transition: margin-left .2s;
    display: flex;
    flex-direction: column;
}
.content {
    padding: 32px;
}
@media (max-width: 900px) {
    .sidebar {
        width: 100vw;
        position: static;
        height: auto;
    }
    .main-content {
        margin-left: 0;
    }
}

/* Table Responsiveness */
.table-responsive {
    width: 100%;
    overflow-x: auto;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(30,41,59,0.07);
    padding: 0.5em 0;
}

/* Admin Table & Rows */
.data-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
}

.data-table th,
.data-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #ececec;
    font-size: 1rem;
    vertical-align: middle;
}

.data-table th {
    background: #fafbfc;
    text-align: left;
    color: #222;
    font-weight: 700;
    letter-spacing: .02em;
}

/* Row hover highlight */
.data-table tbody tr:hover td {
    background: #f2f7ff;
}

/* Buttons */
.btn-primary,
.btn-icon {
    background: #000;
    color: #fff;
    border: none;
    border-radius: 7px;
    padding: 8px 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-primary:hover,
.btn-icon:hover {
    background: #333;
}
.btn-danger {
    background: #e53e3e !important;
    color: #fff !important;
}
.btn-danger:hover {
    background: #c53030 !important;
}

/* Badges */
.badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 16px;
    font-size: 0.87rem;
    font-weight: 600;
    background: #e3f2fd;
    color: #1565c0;
    letter-spacing: .01em;
}
.badge-success { background: #d3f9df; color: #129e44; }
.badge-warning { background: #ffe6c9; color: #ff9400; }
.badge-info    { background: #deeafb; color: #4068b2;}
/* Misc fixes */
img {
    max-width: 100%;
}

    </style>
</head>
<body style="font-family:poppins;">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1>Manage Case Studies</h1>
                <a href="case-study-add.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Add New Case Study
                </a>
            </div>
            
            <?php if(isset($_GET['msg'])): ?>
                <div class="alert">
                    <i class="fas fa-check-circle"></i>
                    <?php 
                    if($_GET['msg'] == 'added') echo 'Case study added successfully!';
                    if($_GET['msg'] == 'updated') echo 'Case study updated successfully!';
                    if($_GET['msg'] == 'deleted') echo 'Case study deleted successfully!';
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="content-card">
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
                            <td><?php echo $case['display_order']; ?></td>
                            <td>
                                <img src="<?php echo htmlspecialchars($case['featured_image']); ?>" 
                                     style="width: 60px; height: 40px; object-fit: cover; border-radius: 5px;"
                                     onerror="this.src='https://via.placeholder.com/60x40/000000/FFFFFF?text=CS'">
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($case['title']); ?></strong><br>
                                <small style="color: #666;"><?php echo $case['slug']; ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($case['client_name']); ?></td>
                            <td><span class="badge badge-info"><?php echo htmlspecialchars($case['industry']); ?></span></td>
                            <td>
                                <span class="badge <?php echo $case['status'] == 'published' ? 'badge-success' : 'badge-warning'; ?>">
                                    <?php echo ucfirst($case['status']); ?>
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
                                <a href="/case-studies/<?php echo $case['slug']; ?>" class="btn-icon" title="View" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="case-study-edit.php?id=<?php echo $case['id']; ?>" class="btn-icon" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?php echo $case['id']; ?>" class="btn-icon btn-danger" title="Delete" 
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
</body>
</html>
