<?php
// seller/products.php
require_once __DIR__ . '/../includes/functions.php';
requireRole('seller');
$user = currentUser($pdo);

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
    $stmt->execute([(int)$_GET['delete'], $user['id']]);
    flash('message', 'Product deleted.', 'success');
    header('Location: ' . SITE_URL . '/seller/products.php');
    exit;
}

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.seller_id = ? ORDER BY p.created_at DESC");
$stmt->execute([$user['id']]);
$products = $stmt->fetchAll();
$flash_msg = flash('message');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products - <?= SITE_NAME ?></title>
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
            <div><h1>My Products</h1></div>
            <a href="<?= SITE_URL ?>/seller/add-product.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</a>
        </div>
        
        <?php if ($flash_msg): ?>
            <div class="alert alert-<?= $flash_msg['type'] ?>"><i class="fas fa-info-circle"></i> <?= $flash_msg['message'] ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): 
                            $img = $p['image'] && $p['image'] !== 'no-image.png' 
                                ? SITE_URL . '/assets/images/uploads/' . $p['image'] : 'https://via.placeholder.com/50';
                        ?>
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <img src="<?= $img ?>" alt="">
                                        <strong><?= htmlspecialchars($p['name']) ?></strong>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($p['category_name']) ?></td>
                                <td>
                                    <strong><?= formatPrice($p['sale_price'] ?: $p['price']) ?></strong>
                                    <?php if ($p['sale_price']): ?><br><small style="text-decoration:line-through;color:var(--gray-500)"><?= formatPrice($p['price']) ?></small><?php endif; ?>
                                </td>
                                <td><?= $p['stock'] ?></td>
                                <td><span class="status-badge <?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="<?= SITE_URL ?>/seller/edit-product.php?id=<?= $p['id'] ?>" class="action-btn edit" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="?delete=<?= $p['id'] ?>" class="action-btn delete" title="Delete" onclick="return confirm('Delete this product?')"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($products)): ?>
                            <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--gray-500)">
                                No products yet. <a href="<?= SITE_URL ?>/seller/add-product.php" style="color:var(--primary);font-weight:600">Add your first product</a>
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>