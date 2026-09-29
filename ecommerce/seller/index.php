<?php
// seller/index.php
require_once __DIR__ . '/../includes/functions.php';
requireRole('seller');
$user = currentUser($pdo);
$sellerId = $user['id'];

$totalProducts = $pdo->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ?");
$totalProducts->execute([$sellerId]);
$totalProducts = $totalProducts->fetchColumn();

$approvedProducts = $pdo->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ? AND status = 'approved'");
$approvedProducts->execute([$sellerId]);
$approvedProducts = $approvedProducts->fetchColumn();

$pendingProducts = $pdo->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ? AND status = 'pending'");
$pendingProducts->execute([$sellerId]);
$pendingProducts = $pendingProducts->fetchColumn();

$totalOrderItems = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE seller_id = ?");
$totalOrderItems->execute([$sellerId]);
$totalOrderItems = $totalOrderItems->fetchColumn();

$totalRevenue = $pdo->prepare("SELECT IFNULL(SUM(oi.price * oi.quantity), 0) FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE oi.seller_id = ? AND o.status != 'cancelled'");
$totalRevenue->execute([$sellerId]);
$totalRevenue = $totalRevenue->fetchColumn();

// Recent Orders
$stmt = $pdo->prepare("SELECT oi.*, o.status as order_status, o.created_at as order_date, p.name as product_name, u.name as buyer_name
    FROM order_items oi 
    JOIN orders o ON oi.order_id = o.id 
    JOIN products p ON oi.product_id = p.id
    JOIN users u ON o.user_id = u.id
    WHERE oi.seller_id = ? ORDER BY o.created_at DESC LIMIT 10");
$stmt->execute([$sellerId]);
$recentOrders = $stmt->fetchAll();
$flash_msg = flash('message');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - <?= SITE_NAME ?></title>
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
            <div class="user-info">
                <h4><?= htmlspecialchars($user['shop_name'] ?? $user['name']) ?></h4>
                <span>Seller</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Main</div>
            <a href="<?= SITE_URL ?>/seller/" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="<?= SITE_URL ?>/seller/products.php"><i class="fas fa-box"></i> My Products</a>
            <a href="<?= SITE_URL ?>/seller/add-product.php"><i class="fas fa-plus-circle"></i> Add Product</a>
            <a href="<?= SITE_URL ?>/seller/profile.php"><i class="fas fa-store"></i> Shop Profile</a>
            <div class="nav-label">Quick Links</div>
            <a href="<?= SITE_URL ?>"><i class="fas fa-globe"></i> View Store</a>
            <a href="<?= SITE_URL ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>
    
    <main class="dashboard-main">
        <div class="dashboard-header">
            <div>
                <h1>Seller Dashboard</h1>
                <div class="breadcrumb">Welcome, <?= htmlspecialchars($user['name']) ?>!</div>
            </div>
            <a href="<?= SITE_URL ?>/seller/add-product.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</a>
        </div>
        
        <?php if ($flash_msg): ?>
            <div class="alert alert-<?= $flash_msg['type'] ?>"><i class="fas fa-info-circle"></i> <?= $flash_msg['message'] ?></div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-box"></i></div>
                <div class="stat-info"><h3><?= $totalProducts ?></h3><p>Total Products</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info"><h3><?= $approvedProducts ?></h3><p>Approved</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
                <div class="stat-info"><h3><?= $pendingProducts ?></h3><p>Pending</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-info"><h3><?= formatPrice($totalRevenue) ?></h3><p>Total Revenue</p></div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>Recent Orders</h3>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr><th>Product</th><th>Buyer</th><th>Qty</th><th>Price</th><th>Status</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $oi): ?>
                            <tr>
                                <td><?= htmlspecialchars($oi['product_name']) ?></td>
                                <td><?= htmlspecialchars($oi['buyer_name']) ?></td>
                                <td><?= $oi['quantity'] ?></td>
                                <td><strong><?= formatPrice($oi['price'] * $oi['quantity']) ?></strong></td>
                                <td><span class="status-badge <?= $oi['order_status'] ?>"><?= ucfirst($oi['order_status']) ?></span></td>
                                <td><?= timeAgo($oi['order_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentOrders)): ?>
                            <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--gray-500)">No orders yet</td></tr>
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