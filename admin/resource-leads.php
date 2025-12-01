<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$page_title = 'Resource Leads';
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
    
    // Get leads - debug the query
    error_log("Fetching leads for resource_id: $resource_id");
    $stmt = $db->prepare("SELECT * FROM resource_downloads WHERE resource_id = ? ORDER BY downloaded_at DESC");
    $stmt->execute([$resource_id]);
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Found " . count($leads) . " leads for resource $resource_id");
    
} catch(PDOException $e) {
    error_log("Error fetching resource leads: " . $e->getMessage());
    $leads = [];
    $error = "Error loading leads data: " . $e->getMessage();
}
?>

<?php include 'includes/layout-start.php'; ?>

<style>
    .back-link { 
        color: #007bff; 
        text-decoration: none; 
        margin-bottom: 20px; 
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
    }
    .back-link:hover {
        color: #0056b3;
    }
    .leads-header { 
        background: white; 
        padding: 25px; 
        border-radius: 8px; 
        margin-bottom: 30px; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .leads-header h2 {
        margin-bottom: 10px;
        color: #000;
    }
    .leads-header p {
        color: #666;
        margin-bottom: 15px;
    }
    .leads-table { 
        background: white; 
        border-radius: 8px; 
        overflow: hidden; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
    }
    .leads-table table { 
        width: 100%; 
        border-collapse: collapse; 
    }
    .leads-table thead { 
        background: #f8f9fa; 
    }
    .leads-table th { 
        padding: 15px; 
        text-align: left; 
        font-weight: 600; 
        border-bottom: 2px solid #dee2e6; 
    }
    .leads-table td { 
        padding: 15px; 
        border-bottom: 1px solid #dee2e6; 
    }
    .leads-table tbody tr:hover { 
        background: #f8f9fa; 
    }
    .no-leads { 
        text-align: center; 
        padding: 60px; 
        color: #666; 
        background: white;
        border-radius: 8px;
    }
    .no-leads i {
        font-size: 60px;
        color: #ddd;
        margin-bottom: 20px;
        display: block;
    }
    .btn-export { 
        background: #28a745; 
        color: white; 
        padding: 10px 20px; 
        border-radius: 6px; 
        text-decoration: none; 
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
    }
    .btn-export:hover {
        background: #218838;
    }
</style>

<a href="resources.php" class="back-link">
    <i class="fas fa-arrow-left"></i> Back to Resources
</a>

<div class="leads-header">
    <h2>Leads for: <?php echo htmlspecialchars($resource['title']); ?></h2>
    <p>Total Downloads: <strong><?php echo count($leads); ?></strong></p>
    <?php if (count($leads) > 0): ?>
        <a href="export-leads.php?resource_id=<?php echo $resource_id; ?>" class="btn-export">
            <i class="fas fa-download"></i> Export to CSV
        </a>
    <?php endif; ?>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if (count($leads) > 0): ?>
    <div class="leads-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
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
                        <td><?php echo htmlspecialchars($lead['id']); ?></td>
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
        <i class="fas fa-users"></i>
        <h3>No Leads Yet</h3>
        <p>Leads will appear here when users download this resource.</p>
    </div>
<?php endif; ?>

<?php include 'includes/layout-end.php'; ?>
