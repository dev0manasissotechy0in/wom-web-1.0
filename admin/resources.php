<?php
// Security check first - centralized authentication
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$page_title = 'Manage Resources';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $db->prepare("SELECT image, file_path FROM resources WHERE id = ?");
        $stmt->execute([$id]);
        $resource = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resource) {
            // Delete files
            if ($resource['image'] && file_exists("../assets/images/uploads/resources/" . $resource['image'])) {
                unlink("../assets/images/uploads/resources/" . $resource['image']);
            }
            if ($resource['file_path'] && file_exists("../assets/images/uploads/resources/" . $resource['file_path'])) {
                unlink("../assets/images/uploads/resources/" . $resource['file_path']);
            }
            
            $db->prepare("DELETE FROM resources WHERE id = ?")->execute([$id]);
            $success = "Resource deleted successfully!";
        }
    } catch(PDOException $e) {
        error_log("Error deleting resource: " . $e->getMessage());
        $error = "Error deleting resource";
    }
}

// Get all resources
try {
    $stmt = $db->query("SELECT * FROM resources ORDER BY created_at DESC");
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate stats
    $totalDownloads = array_sum(array_column($resources, 'downloads'));
    $freeCount = count(array_filter($resources, function($r) { return $r['resource_type'] === 'free'; }));
    $paidCount = count($resources) - $freeCount;
    
} catch(PDOException $e) {
    error_log("Error fetching resources: " . $e->getMessage());
    $resources = [];
    $totalDownloads = 0;
    $freeCount = 0;
    $paidCount = 0;
    $error = "Error loading resources";
}
?>
<?php include 'includes/layout-start.php'; ?>
    <div class="page-header">
        <h1>Manage Resources</h1>
        <a href="resource-add.php" class="btn-primary"><i class="fas fa-plus"></i> Add New Resource</a>
    </div>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Resources - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
        }

        .admin-header {
            background: #000;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-header h1 {
            font-size: 1.3rem;
        }

        .admin-header a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            background: #333;
            border-radius: 4px;
        }

        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-header h2 {
            font-size: 1.8rem;
        }

        .btn-add {
            background: #28a745;
            color: white;
            padding: 12px 25px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #000;
        }

        .stat-label {
            color: #666;
            margin-top: 5px;
        }

        .resources-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        .resource-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-free {
            background: #d4edda;
            color: #155724;
        }

        .badge-paid {
            background: #fff3cd;
            color: #856404;
        }

        .badge-published {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge-draft {
            background: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            color: white;
        }

        .btn-edit {
            background: #007bff;
        }

        .btn-delete {
            background: #dc3545;
        }

        .btn-leads {
            background: #6c757d;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .no-resources {
            text-align: center;
            padding: 60px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo count($resources); ?></div>
                <div class="stat-label">Total Resources</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($totalDownloads); ?></div>
                <div class="stat-label">Total Downloads</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $freeCount; ?></div>
                <div class="stat-label">Free Resources</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $paidCount; ?></div>
                <div class="stat-label">Paid Resources</div>
            </div>
        </div>

        <!-- Resources Table -->
        <?php if (count($resources) > 0): ?>
            <div class="resources-table">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Downloads</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resources as $resource): ?>
                            <tr>
                                <td>
                                    <?php if ($resource['image']): ?>
                                        <img src="../assets/images/uploads/resources/<?php echo htmlspecialchars($resource['image']); ?>" 
                                             alt="" class="resource-thumb">
                                    <?php else: ?>
                                        <div style="width:60px;height:60px;background:#e9ecef;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-file" style="color:#adb5bd;"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($resource['title']); ?></strong><br>
                                    <small style="color:#666;"><?php echo htmlspecialchars(substr($resource['excerpt'], 0, 60)); ?>...</small>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $resource['resource_type']; ?>">
                                        <?php echo $resource['resource_type'] === 'free' ? 'FREE' : '$' . number_format($resource['price'], 2); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $resource['status']; ?>">
                                        <?php echo ucfirst($resource['status']); ?>
                                    </span>
                                </td>
                                <td><strong><?php echo number_format($resource['downloads']); ?></strong></td>
                                <td><?php echo date('M d, Y', strtotime($resource['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="resource-edit.php?id=<?php echo $resource['id']; ?>" class="btn-action btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="resource-leads.php?resource_id=<?php echo $resource['id']; ?>" class="btn-action btn-leads" title="Leads">
                                            <i class="fas fa-users"></i>
                                        </a>
                                        <a href="?delete=<?php echo $resource['id']; ?>" 
                                           class="btn-action btn-delete" 
                                           onclick="return confirm('Delete this resource?')"
                                           title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No resources found. Click "Add New Resource" to create one.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/layout-end.php'; ?>
