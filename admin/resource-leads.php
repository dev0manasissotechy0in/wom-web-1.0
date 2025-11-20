<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: /admin/login.php');
    exit;
}

// Load config and database connection
require_once __DIR__ . '/../config/config.php';

$resource_id = (int)($_GET['resource_id'] ?? 0);

// Get resource
try {
    $stmt = $db->prepare("SELECT * FROM resources WHERE id = ?");
    $stmt->execute([$resource_id]);
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resource) {
        header('Location: resources.php');
        exit;
    }
    
    // Get leads
    $stmt = $db->prepare("SELECT * FROM resource_downloads WHERE resource_id = ? ORDER BY downloaded_at DESC");
    $stmt->execute([$resource_id]);
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    error_log("Error fetching resource leads: " . $e->getMessage());
    $leads = [];
    $error = "Error loading leads data";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leads - <?php echo htmlspecialchars($resource['title']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; }
        .admin-header { background: #000; color: white; padding: 15px 30px; }
        .container { max-width: 1400px; margin: 30px auto; padding: 0 20px; }
        .back-link { color: #007bff; text-decoration: none; margin-bottom: 20px; display: inline-block; }
        .leads-header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .leads-table { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8f9fa; }
        th { padding: 15px; text-align: left; font-weight: 600; border-bottom: 2px solid #dee2e6; }
        td { padding: 15px; border-bottom: 1px solid #dee2e6; }
        tbody tr:hover { background: #f8f9fa; }
        .no-leads { text-align: center; padding: 60px; color: #666; }
        .btn-export { background: #28a745; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; display: inline-block; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1><i class="fas fa-shield-alt"></i> Admin Panel - Resource Leads</h1>
    </div>

    <div class="container">
        <a href="resources.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Resources</a>

        <div class="leads-header">
            <h2>Leads for: <?php echo htmlspecialchars($resource['title']); ?></h2>
            <p>Total Downloads: <strong><?php echo count($leads); ?></strong></p>
            <?php if (count($leads) > 0): ?>
                <a href="export-leads.php?resource_id=<?php echo $resource_id; ?>" class="btn-export">
                    <i class="fas fa-download"></i> Export to CSV
                </a>
            <?php endif; ?>
        </div>

        <?php if (count($leads) > 0): ?>
            <div class="leads-table">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>IP Address</th>
                            <th>Download Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $lead): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($lead['name']); ?></td>
                                <td><?php echo htmlspecialchars($lead['email']); ?></td>
                                <td><?php echo htmlspecialchars($lead['phone'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($lead['company'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($lead['ip_address']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($lead['downloaded_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-leads">
                <i class="fas fa-users" style="font-size:60px;color:#ddd;margin-bottom:20px;"></i>
                <h3>No Leads Yet</h3>
                <p>Leads will appear here when users download this resource.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
