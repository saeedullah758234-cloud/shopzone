<?php
// admin/index.php
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $pdo->query("SELECT IFNULL(SUM(total), 0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$pendingProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'pending'")->fetchColumn();
$pendingSellers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seller' AND status = 'pending'")->fetchColumn();

// Recent Orders
$recentOrders = $pdo->query("SELECT o.*, u.name as buyer_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10")->fetchAll();

$user = currentUser($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <script>const SITE_URL = '<?= SITE_URL ?>';</script>
</head>
<body>
<div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="<?= SITE_URL ?>" class="logo">
                <i class="fas fa-shopping-bag"></i> Shop<span>Zone</span>
            </a>
        </div>
        <div class="sidebar-user">
            <div class="avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
            <div class="user-info">
                <h4><?= htmlspecialchars($user['name']) ?></h4>
                <span>Administrator</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Main</div>
            <a href="<?= SITE_URL ?>/admin/" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="<?= SITE_URL ?>/admin/products.php"><i class="fas fa-box"></i> Products 
                <?php if ($pendingProducts): ?><span class="cart-badge" style="position:static;margin-left:auto"><?= $pendingProducts ?></span><?php endif; ?>
            </a>
            <a href="<?= SITE_URL ?>/admin/users.php"><i class="fas fa-users"></i> Users
                <?php if ($pendingSellers): ?><span class="cart-badge" style="position:static;margin-left:auto"><?= $pendingSellers ?></span><?php endif; ?>
            </a>
            <a href="<?= SITE_URL ?>/admin/orders.php"><i class="fas fa-shopping-bag"></i> Orders</a>
            <div class="nav-label">Quick Links</div>
            <a href="<?= SITE_URL ?>"><i class="fas fa-globe"></i> View Store</a>
            <a href="<?= SITE_URL ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="dashboard-main">
        <div class="dashboard-header">
            <div>
                <h1>Dashboard</h1>
                <div class="breadcrumb">Welcome back, <?= htmlspecialchars($user['name']) ?>!</div>
            </div>
            <button class="menu-toggle sidebar-toggle"><i class="fas fa-bars"></i></button>
        </div>
        
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h3><?= number_format($totalUsers) ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-box"></i></div>
                <div class="stat-info">
                    <h3><?= number_format($totalProducts) ?></h3>
                    <p>Total Products</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-shopping-bag"></i></div>
                <div class="stat-info">
                    <h3><?= number_format($totalOrders) ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-info">
                    <h3><?= formatPrice($totalRevenue) ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
        </div>
        
        <!-- Pending Alerts -->
        <?php if ($pendingProducts || $pendingSellers): ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:30px">
                <?php if ($pendingProducts): ?>
                    <div class="alert alert-warning" style="margin:0">
                        <i class="fas fa-clock"></i>
                        <strong><?= $pendingProducts ?></strong> products pending approval.
                        <a href="<?= SITE_URL ?>/admin/products.php?status=pending" style="margin-left:auto;color:inherit;font-weight:700">Review →</a>
                    </div>
                <?php endif; ?>
                <?php if ($pendingSellers): ?>
                    <div class="alert alert-info" style="margin:0">
                        <i class="fas fa-store"></i>
                        <strong><?= $pendingSellers ?></strong> seller accounts pending.
                        <a href="<?= SITE_URL ?>/admin/users.php?status=pending" style="margin-left:auto;color:inherit;font-weight:700">Review →</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header">
                <h3>Recent Orders</h3>
                <a href="<?= SITE_URL ?>/admin/orders.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><strong>#<?= $order['id'] ?></strong></td>
                                <td><?= htmlspecialchars($order['buyer_name']) ?></td>
                                <td><strong><?= formatPrice($order['total']) ?></strong></td>
                                <td><?= strtoupper($order['payment_method']) ?></td>
                                <td><span class="status-badge <?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                                <td><?= timeAgo($order['created_at']) ?></td>
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