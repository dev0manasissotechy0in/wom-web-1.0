<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$page_title = 'Unsubscribed Users';

// Get filter
$filter = $_GET['filter'] ?? 'all';

// Build query based on filter
$query = "SELECT * FROM newsletter_subscribers";
$params = [];

if ($filter === 'unsubscribed') {
    $query .= " WHERE status = 'unsubscribed'";
} elseif ($filter === 'subscribed') {
    $query .= " WHERE status = 'subscribed'";
}

$query .= " ORDER BY updated_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count statistics
$statsStmt = $db->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'subscribed' THEN 1 ELSE 0 END) as subscribed,
        SUM(CASE WHEN status = 'unsubscribed' THEN 1 ELSE 0 END) as unsubscribed
    FROM newsletter_subscribers
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
?>

<?php include 'includes/layout-start.php'; ?>

<style>
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid #667eea;
}

.stat-card.success {
    border-left-color: #28a745;
}

.stat-card.danger {
    border-left-color: #dc3545;
}

.stat-card h3 {
    font-size: 14px;
    color: #666;
    margin-bottom: 10px;
    text-transform: uppercase;
}

.stat-card .number {
    font-size: 32px;
    font-weight: 700;
    color: #333;
}

.filter-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 10px 20px;
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    text-decoration: none;
    color: #666;
    font-weight: 500;
    transition: all 0.3s;
}

.filter-tab:hover {
    border-color: #667eea;
    color: #667eea;
}

.filter-tab.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #e0e0e0;
}

td {
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
    color: #666;
}

tr:hover {
    background: #f8f9fa;
}

.badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge.success {
    background: #d4edda;
    color: #155724;
}

.badge.danger {
    background: #f8d7da;
    color: #721c24;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.3;
}

@media (max-width: 768px) {
    .table-container {
        overflow-x: auto;
    }
    
    table {
        min-width: 600px;
    }
}
</style>

<div class="page-header">
    <h1><?php echo $page_title; ?></h1>
    <div class="actions">
        <a href="subscribers.php" class="btn btn-secondary">
            <i class="fas fa-users"></i> All Subscribers
        </a>
    </div>
</div>

<div class="stats-cards">
    <div class="stat-card">
        <h3>Total Subscribers</h3>
        <div class="number"><?php echo number_format($stats['total']); ?></div>
    </div>
    
    <div class="stat-card success">
        <h3>Active Subscribed</h3>
        <div class="number"><?php echo number_format($stats['subscribed']); ?></div>
    </div>
    
    <div class="stat-card danger">
        <h3>Unsubscribed</h3>
        <div class="number"><?php echo number_format($stats['unsubscribed']); ?></div>
    </div>
</div>

<div class="filter-tabs">
    <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
        All (<?php echo $stats['total']; ?>)
    </a>
    <a href="?filter=subscribed" class="filter-tab <?php echo $filter === 'subscribed' ? 'active' : ''; ?>">
        Subscribed (<?php echo $stats['subscribed']; ?>)
    </a>
    <a href="?filter=unsubscribed" class="filter-tab <?php echo $filter === 'unsubscribed' ? 'active' : ''; ?>">
        Unsubscribed (<?php echo $stats['unsubscribed']; ?>)
    </a>
</div>

<div class="table-container">
    <?php if (count($subscribers) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Newsletter</th>
                    <th>Location</th>
                    <th>Subscribed</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscribers as $subscriber): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($subscriber['name']); ?></td>
                        <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                        <td>
                            <span class="badge <?php echo $subscriber['status'] === 'subscribed' ? 'success' : 'danger'; ?>">
                                <?php echo ucfirst($subscriber['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($subscriber['newsletter_name'] ?? 'main'); ?></td>
                        <td title="IP: <?php echo htmlspecialchars($subscriber['ip_address'] ?? 'N/A'); ?>">
                            <i class="fas fa-map-marker-alt"></i> 
                            <?php echo htmlspecialchars($subscriber['location'] ?? 'Unknown'); ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($subscriber['created_at'])); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($subscriber['updated_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>No subscribers found</h3>
            <p>No subscribers match the current filter.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/layout-end.php'; ?>
