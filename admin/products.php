<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$page_title = 'Manage Products';

// Handle add/edit
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $description = $_POST['description'] ?? '';
    $short_description = $_POST['short_description'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $url = $_POST['url'] ?? '';
    $features = json_encode(array_map('trim', explode(',', $_POST['features'] ?? '')));
    $image = $_POST['image'] ?? '';
    $status = $_POST['status'] ?? 'active';
    
    if(isset($_POST['id']) && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE products SET product_name=?, slug=?, description=?, short_description=?, url=?, price=?, features=?, image=?, status=? WHERE id=?");
        $stmt->execute([$product_name, $slug, $description, $short_description, $url, $price, $features, $image, $status, $id]);
        $msg = 'updated';
    } else {
        $stmt = $db->prepare("INSERT INTO products (product_name, slug, description, short_description, url, price, features, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$product_name, $slug, $description, $short_description, $url, $price, $features, $image, $status]);
        $msg = 'added';
    }
    
    header("Location: products.php?msg=$msg");
    exit();
}

// Handle delete
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    header('Location: products.php?msg=deleted');
    exit();
}

// Get product for editing
$edit_product = null;
if(isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $edit_product = $stmt->fetch();
}

$products = $db->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
?>
<?php include 'includes/layout-start.php'; ?>

<div class="content">
    <div class="page-header">
        <h1>Manage Products</h1>
        <p>Add, edit, or delete products</p>
    </div>
    
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php 
            if($_GET['msg'] == 'added') echo 'Product added successfully!';
            elseif($_GET['msg'] == 'updated') echo 'Product updated successfully!';
            elseif($_GET['msg'] == 'deleted') echo 'Product deleted successfully!';
            ?>
        </div>
    <?php endif; ?>
    
    <div class="content-grid" style="grid-template-columns: 1fr 1.5fr;">
        <!-- Add/Edit Form -->
        <div class="content-card">
            <div class="card-header">
                <h3><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h3>
            </div>

            <form method="POST" style="padding: 20px;">
                <?php if($edit_product): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_product['id']); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Product Name <span class="required">*</span></label>
                    <input type="text" name="product_name" class="form-control" value="<?php echo $edit_product ? htmlspecialchars($edit_product['product_name']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Slug</label>
                    <input type="text" name="slug" class="form-control" value="<?php echo $edit_product ? htmlspecialchars($edit_product['slug']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Price (₹) <span class="required">*</span></label>
                    <input type="number" name="price" class="form-control" step="0.01" value="<?php echo $edit_product ? htmlspecialchars($edit_product['price']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Short Description</label>
                    <textarea name="short_description" class="form-control" rows="2"><?php echo $edit_product ? htmlspecialchars($edit_product['short_description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Description <span class="required">*</span></label>
                    <textarea name="description" class="form-control" rows="4" required><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Features (comma separated)</label>
                    <textarea name="features" class="form-control" rows="3"><?php echo $edit_product ? implode(', ', json_decode($edit_product['features'], true) ?? []) : ''; ?></textarea>
                    <small>e.g., Feature 1, Feature 2, Feature 3</small>
                </div>
            
                <div class="form-group">
                    <label>Product URL</label>
                    <input type="url" name="url" class="form-control" value="<?php echo $edit_product ? htmlspecialchars($edit_product['url']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Image URL</label>
                    <input type="url" name="image" class="form-control" value="<?php echo $edit_product ? htmlspecialchars($edit_product['image']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="active" <?php echo ($edit_product && $edit_product['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($edit_product && $edit_product['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo $edit_product ? 'Update' : 'Add'; ?> Product
                </button>
                <?php if($edit_product): ?>
                    <a href="products.php" class="btn btn-primary" style="background: #666;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Products List -->
        <div class="content-card">
            <div class="card-header">
                <h3>All Products</h3>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                            <td>₹<?php echo number_format($product['price'], 2); ?></td>
                            <td><span class="badge <?php echo $product['status'] == 'active' ? 'badge-success' : 'badge-warning'; ?>"><?php echo htmlspecialchars($product['status']); ?></span></td>
                            <td>
                                <a href="?edit=<?php echo htmlspecialchars($product['id']); ?>" class="btn-icon"><i class="fas fa-edit"></i></a>
                                <a href="?delete=<?php echo htmlspecialchars($product['id']); ?>" class="btn-icon btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
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
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    

    <style>
        .form-row { display: flex; gap: 20px; }
        .form-row .form-group { flex: 1; }
        .btn-secondary { background: #666; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; border: none; cursor: pointer; }
        .btn-secondary:hover { background: #555; }
    </style>
</body>
</html>
