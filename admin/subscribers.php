<?php
session_start();
require_once '../config/config.php';
require_once '../classes/Newsletter.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit();
}

$newsletter = new Newsletter($db);
$filters = [
    'newsletter' => $_GET['newsletter'] ?? 'main',
    'status' => $_GET['status'] ?? 'all',
    'search' => $_GET['search'] ?? ''
];

// Build query conditions
$conditions = [];
$parameters = [];

if ($filters['newsletter'] !== 'all') {
    $conditions[] = "newsletter_name = ?";
    $parameters[] = $filters['newsletter'];
}

if ($filters['status'] !== 'all') {
    $conditions[] = "status = ?";
    $parameters[] = $filters['status'];
}

if ($filters['search']) {
    $conditions[] = "(email LIKE ? OR name LIKE ?)";
    $parameters[] = '%' . $filters['search'] . '%';
    $parameters[] = '%' . $filters['search'] . '%';
}

$whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Get all newsletter names
$newsletterNames = $db->query("SELECT DISTINCT newsletter_name FROM newsletter_subscribers")->fetchAll(PDO::FETCH_COLUMN);
$subscribers = $db->prepare("SELECT * FROM newsletter_subscribers $whereClause ORDER BY created_at DESC");
$subscribers->execute($parameters);
$subscribers = $subscribers->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Subscribers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-header">
        <h1><i class="fas fa-users"></i> Newsletter Subscribers</h1>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <div class="container">
        <div class="content-card">
            <h2>Manage Subscribers</h2>
            
            <form method="get" class="admin-filters">
                <div class="filter-group">
                    <select name="newsletter">
                        <option value="all">All Newsletters</option>
                        <?php foreach ($newsletterNames as $nl): ?>
                            <option value="<?php echo htmlspecialchars($nl); ?>" <?php echo ($filters['newsletter'] == $nl ? 'selected' : ''); ?>>
                                <?php echo htmlspecialchars($nl); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="status">
                        <option value="all">All Status</option>
                        <option value="subscribed" <?php echo ($filters['status'] == 'subscribed' ? 'selected' : ''); ?>>Subscribed</option>
                        <option value="unsubscribed" <?php echo ($filters['status'] == 'unsubscribed' ? 'selected' : ''); ?>>Unsubscribed</option>
                    </select>
                    
                    <input type="text" name="search" placeholder="Search by email or name" value="<?php echo htmlspecialchars($filters['search']); ?>">
                    <button type="submit" class="btn-primary"><i class="fas fa-search"></i> Search</button>
                </div>
            </form>
            
            <div class="admin-table">
                <table>
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Newsletter</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscribers as $subscriber): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                            <td><?php echo htmlspecialchars($subscriber['name']); ?></td>
                            <td><?php echo htmlspecialchars($subscriber['newsletter_name']); ?></td>
                            <td><?php echo htmlspecialchars($subscriber['status']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($subscriber['created_at'])); ?></td>
                            <td>
                                <?php if ($subscriber['status'] === 'subscribed'): ?>
                                    <button class="btn btn-danger unsubscribe-btn" data-email="<?php echo htmlspecialchars($subscriber['email']); ?>" data-newsletter="<?php echo htmlspecialchars($subscriber['newsletter_name']); ?>">
                                        <i class="fas fa-trash"></i> Unsubscribe
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
    // Add unsubscribe functionality
    document.querySelectorAll('.unsubscribe-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const email = this.dataset.email;
            const newsletter = this.dataset.newsletter;
            
            if (confirm('Are you sure you want to unsubscribe this user?')) {
                const response = await fetch('/api/unsubscribe.php?email=' + encodeURIComponent(email) + '&newsletter=' + encodeURIComponent(newsletter));
                const data = await response.json();
                
                if (data.success) {
                    alert('User unsubscribed successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            }
        });
    });
    </script>
</body>
</html>
