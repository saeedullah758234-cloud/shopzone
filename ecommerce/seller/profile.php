<?php
// seller/profile.php
require_once __DIR__ . '/../includes/functions.php';
requireRole('seller');
$user = currentUser($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $shop_name = trim($_POST['shop_name']);
    $shop_description = trim($_POST['shop_description']);
    $address = trim($_POST['address']);
    
    $pdo->prepare("UPDATE users SET name = ?, phone = ?, shop_name = ?, shop_description = ?, address = ? WHERE id = ?")
        ->execute([$name, $phone, $shop_name, $shop_description, $address, $user['id']]);
    
    flash('message', 'Profile updated!', 'success');
    header('Location: ' . SITE_URL . '/seller/profile.php');
    exit;
}

$flash_msg = flash('message');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Profile - <?= SITE_NAME ?></title>
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
            <a href="<?= SITE_URL ?>/seller/products.php"><i class="fas fa-box"></i> My Products</a>
            <a href="<?= SITE_URL ?>/seller/add-product.php"><i class="fas fa-plus-circle"></i> Add Product</a>
            <a href="<?= SITE_URL ?>/seller/profile.php" class="active"><i class="fas fa-store"></i> Shop Profile</a>
            <div class="nav-label">Quick Links</div>
            <a href="<?= SITE_URL ?>"><i class="fas fa-globe"></i> View Store</a>
            <a href="<?= SITE_URL ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>
    
    <main class="dashboard-main">
        <div class="dashboard-header"><h1>Shop Profile</h1></div>
        
        <?php if ($flash_msg): ?>
            <div class="alert alert-<?= $flash_msg['type'] ?>"><i class="fas fa-check-circle"></i> <?= $flash_msg['message'] ?></div>
        <?php endif; ?>
        
        <div class="card" style="max-width:700px">
            <div class="card-body">
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Your Name</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Shop Name</label>
                        <input type="text" name="shop_name" class="form-control" value="<?= htmlspecialchars($user['shop_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Shop Description</label>
                        <textarea name="shop_description" class="form-control" rows="4"><?= htmlspecialchars($user['shop_description'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                </form>
            </div>
        </div>
    </main>
</div>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>