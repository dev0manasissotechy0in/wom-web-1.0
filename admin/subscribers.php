<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$page_title = 'Newsletter Subscribers';

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
<?php include 'includes/layout-start.php'; ?>
    <div class="page-header">
        <h1>Newsletter Subscribers</h1>
        <p>Manage newsletter subscriptions</p>
    </div>
    
    <div class="content-card">
        <div class="card-header">
            <h3>Filter Subscribers</h3>
        </div>
        
        <form method="get" style="padding: 20px;">
            <div class="form-row">
                <div class="form-group">
                    <label>Newsletter</label>
                    <select name="newsletter" class="form-control">
                        <option value="all">All Newsletters</option>
                        <?php foreach ($newsletterNames as $nl): ?>
                            <option value="<?php echo htmlspecialchars($nl); ?>" <?php echo ($filters['newsletter'] == $nl ? 'selected' : ''); ?>>
                                <?php echo htmlspecialchars($nl); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="all">All Status</option>
                        <option value="subscribed" <?php echo ($filters['status'] == 'subscribed' ? 'selected' : ''); ?>>Subscribed</option>
                        <option value="unsubscribed" <?php echo ($filters['status'] == 'unsubscribed' ? 'selected' : ''); ?>>Unsubscribed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by email or name" value="<?php echo htmlspecialchars($filters['search']); ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>
    
    <div class="content-card">
        <div class="card-header">
            <h3>All Subscribers (<?php echo count($subscribers); ?>)</h3>
        </div>
        <div class="table-responsive">
            <table class="data-table">
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
                    <?php if(count($subscribers) > 0): ?>
                        <?php foreach ($subscribers as $subscriber): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                            <td><?php echo htmlspecialchars($subscriber['name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($subscriber['newsletter_name']); ?></td>
                            <td><span class="badge <?php echo $subscriber['status'] == 'subscribed' ? 'badge-success' : 'badge-warning'; ?>"><?php echo htmlspecialchars($subscriber['status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($subscriber['created_at'])); ?></td>
                            <td>
                                <?php if ($subscriber['status'] === 'subscribed'): ?>
                                    <button class="btn-icon btn-danger unsubscribe-btn" data-email="<?php echo htmlspecialchars($subscriber['email']); ?>" data-newsletter="<?php echo htmlspecialchars($subscriber['newsletter_name']); ?>" title="Unsubscribe">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #999;">No subscribers found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.unsubscribe-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const email = this.dataset.email;
            const newsletter = this.dataset.newsletter;
            
            if (confirm('Are you sure you want to unsubscribe this user?')) {
                try {
                    const response = await fetch('/api/unsubscribe.php?email=' + encodeURIComponent(email) + '&newsletter=' + encodeURIComponent(newsletter));
                    const data = await response.json();
                    
                    if (data.success) {
                        alert('User unsubscribed successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error'));
                    }
                } catch(err) {
                    alert('Error: ' + err.message);
                }
            }
        });
    });
</script>

<?php include 'includes/layout-end.php'; ?>
