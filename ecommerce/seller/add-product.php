<?php
// seller/add-product.php
require_once __DIR__ . '/../includes/functions.php';
requireRole('seller');
$user = currentUser($pdo);
$categories = getCategories($pdo);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $stock = (int)($_POST['stock'] ?? 0);
    
    if (empty($name)) $errors[] = 'Product name is required';
    if (!$category_id) $errors[] = 'Category is required';
    if ($price <= 0) $errors[] = 'Valid price is required';
    
    $image = 'no-image.png';
    if (!empty($_FILES['image']['name'])) {
        $uploaded = uploadImage($_FILES['image']);
        if ($uploaded) $image = $uploaded;
        else $errors[] = 'Invalid image file';
    }
    
    if (empty($errors)) {
        $slug = slugify($name);
        $stmt = $pdo->prepare("INSERT INTO products (seller_id, category_id, name, slug, description, price, sale_price, stock, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user['id'], $category_id, $name, $slug, $description, $price, $sale_price, $stock, $image]);
        
        flash('message', 'Product added! Waiting for admin approval.', 'success');
        header('Location: ' . SITE_URL . '/seller/products.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <script>const SITE_URL = '<?= SITE_URL ?>';</script>
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="<?= SITE_URL ?>" class="logo"><i class="fas fa-shopping-bag"></i> Shop<span>Zone</span></a>
        </div>
        <div class="sidebar-user">
            <div class="avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
            <div class="user-info"><h4><?= htmlspecialchars($user['shop_name'] ?? $user['name']) ?></h4><span>Seller</span></div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Main</div>
            <a href="<?= SITE_URL ?>/seller/"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="<?= SITE_URL ?>/seller/products.php"><i class="fas fa-box"></i> My Products</a>
            <a href="<?= SITE_URL ?>/seller/add-product.php" class="active"><i class="fas fa-plus-circle"></i> Add Product</a>
            <a href="<?= SITE_URL ?>/seller/profile.php"><i class="fas fa-store"></i> Shop Profile</a>
            <div class="nav-label">Quick Links</div>
            <a href="<?= SITE_URL ?>"><i class="fas fa-globe"></i> View Store</a>
            <a href="<?= SITE_URL ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>
    
    <main class="dashboard-main">
        <div class="dashboard-header">
            <div>
                <h1>Add New Product</h1>
                <div class="breadcrumb"><a href="<?= SITE_URL ?>/seller/">Dashboard</a> <i class="fas fa-chevron-right"></i> Add Product</div>
            </div>
        </div>
        
        <?php if ($errors): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= implode('<br>', $errors) ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header"><h3>Product Information</h3></div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="Enter product name" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Category *</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Stock Quantity *</label>
                            <input type="number" name="stock" class="form-control" value="<?= $_POST['stock'] ?? 0 ?>" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Regular Price ($) *</label>
                            <input type="number" name="price" class="form-control" value="<?= $_POST['price'] ?? '' ?>" step="0.01" min="0.01" placeholder="0.00" required>
                        </div>
                        <div class="form-group">
                            <label>Sale Price ($) <small style="color:var(--gray-500)">(Optional)</small></label>
                            <input type="number" name="sale_price" class="form-control" value="<?= $_POST['sale_price'] ?? '' ?>" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Describe your product..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Product Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this, 'img-preview')">
                        <img id="img-preview" src="" style="display:none;margin-top:12px;max-height:200px;border-radius:8px">
                    </div>
                    
                    <div class="alert alert-info" style="margin-top:0">
                        <i class="fas fa-info-circle"></i>
                        Your product will be reviewed by admin before it goes live.
                    </div>
                    
                    <div style="display:flex;gap:12px;margin-top:24px">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus-circle"></i> Add Product
                        </button>
                        <a href="<?= SITE_URL ?>/seller/products.php" class="btn btn-lg" style="background:var(--gray-200);color:var(--gray-700)">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>