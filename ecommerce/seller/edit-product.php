<?php
// seller/edit-product.php
require_once __DIR__ . '/../includes/functions.php';
requireRole('seller');
$user = currentUser($pdo);
$categories = getCategories($pdo);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
$stmt->execute([$id, $user['id']]);
$product = $stmt->fetch();

if (!$product) { header('Location: ' . SITE_URL . '/seller/products.php'); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $stock = (int)$_POST['stock'];
    
    if (empty($name)) $errors[] = 'Product name is required';
    if ($price <= 0) $errors[] = 'Valid price is required';
    
    $image = $product['image'];
    if (!empty($_FILES['image']['name'])) {
        $uploaded = uploadImage($_FILES['image']);
        if ($uploaded) $image = $uploaded;
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, category_id = ?, description = ?, price = ?, sale_price = ?, stock = ?, image = ?, status = 'pending' WHERE id = ? AND seller_id = ?");
        $stmt->execute([$name, $category_id, $description, $price, $sale_price, $stock, $image, $id, $user['id']]);
        
        flash('message', 'Product updated! Sent for re-approval.', 'success');
        header('Location: ' . SITE_URL . '/seller/products.php');
        exit;
    }
}

$p = $product;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - <?= SITE_NAME ?></title>
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
        <nav class="sidebar-nav">
            <div class="nav-label">Main</div>
            <a href="<?= SITE_URL ?>/seller/"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="<?= SITE_URL ?>/seller/products.php" class="active"><i class="fas fa-box"></i> My Products</a>
            <a href="<?= SITE_URL ?>/seller/add-product.php"><i class="fas fa-plus-circle"></i> Add Product</a>
            <a href="<?= SITE_URL ?>/seller/profile.php"><i class="fas fa-store"></i> Shop Profile</a>
            <div class="nav-label">Quick Links</div>
            <a href="<?= SITE_URL ?>"><i class="fas fa-globe"></i> View Store</a>
            <a href="<?= SITE_URL ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>
    
    <main class="dashboard-main">
        <div class="dashboard-header">
            <h1>Edit Product</h1>
        </div>
        
        <?php if ($errors): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= implode('<br>', $errors) ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($p['name']) ?>" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Category *</label>
                            <select name="category_id" class="form-control" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $p['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Stock</label>
                            <input type="number" name="stock" class="form-control" value="<?= $p['stock'] ?>" min="0">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Price ($) *</label>
                            <input type="number" name="price" class="form-control" value="<?= $p['price'] ?>" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Sale Price ($)</label>
                            <input type="number" name="sale_price" class="form-control" value="<?= $p['sale_price'] ?>" step="0.01">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($p['description']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Product Image</label>
                        <?php if ($p['image'] && $p['image'] !== 'no-image.png'): ?>
                            <img src="<?= SITE_URL ?>/assets/images/uploads/<?= $p['image'] ?>" style="max-height:150px;border-radius:8px;margin-bottom:12px">
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div style="display:flex;gap:12px">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Product</button>
                        <a href="<?= SITE_URL ?>/seller/products.php" class="btn" style="background:var(--gray-200);color:var(--gray-700)">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>