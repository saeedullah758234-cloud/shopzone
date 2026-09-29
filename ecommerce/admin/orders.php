<?php
// admin/orders.php
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

// Handle Status Update
if (isset($_GET['action']) && $_GET['action'] === 'update_status' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $newStatus = $_GET['status_to'] ?? '';
    if (in_array($newStatus, ['processing', 'shipped', 'delivered', 'cancelled'])) {
        $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
        flash('message', 'Order status updated to ' . ucfirst($newStatus), 'success');
    }
    header('Location: ' . SITE_URL . '/admin/orders.php');
    exit;
}

$stmt = $pdo->query("SELECT o.*, u.name as buyer_name, u.email as buyer_email 
    FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll();
$user = currentUser($pdo);
$flash_msg = flash('message');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - <?= SITE_NAME ?></title>
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
            <div class="user-info"><h4><?= htmlspecialchars($user['name']) ?></h4><span>Administrator</span></div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Main</div>
            <a href="<?= SITE_URL ?>/admin/"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="<?= SITE_URL ?>/admin/products.php"><i class="fas fa-box"></i> Products</a>
            <a href="<?= SITE_URL ?>/admin/users.php"><i class="fas fa-users"></i> Users</a>
            <a href="<?= SITE_URL ?>/admin/orders.php" class="active"><i class="fas fa-shopping-bag"></i> Orders</a>
            <div class="nav-label">Quick Links</div>
            <a href="<?= SITE_URL ?>"><i class="fas fa-globe"></i> View Store</a>
            <a href="<?= SITE_URL ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>
    
    <main class="dashboard-main">
        <div class="dashboard-header">
            <div>
                <h1>All Orders</h1>
                <div class="breadcrumb"><a href="<?= SITE_URL ?>/admin/">Dashboard</a> <i class="fas fa-chevron-right"></i> Orders</div>
            </div>
        </div>
        
        <?php if ($flash_msg): ?>
            <div class="alert alert-<?= $flash_msg['type'] ?>"><i class="fas fa-info-circle"></i> <?= $flash_msg['message'] ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Shipping</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong>#<?= $order['id'] ?></strong></td>
                                <td>
                                    <strong><?= htmlspecialchars($order['buyer_name']) ?></strong>
                                    <br><small style="color:var(--gray-500)"><?= $order['buyer_email'] ?></small>
                                </td>
                                <td>
                                    <small>
                                        <?= htmlspecialchars($order['shipping_name']) ?><br>
                                        <?= htmlspecialchars($order['shipping_city']) ?><br>
                                        <?= htmlspecialchars($order['shipping_phone']) ?>
                                    </small>
                                </td>
                                <td><strong><?= formatPrice($order['total']) ?></strong></td>
                                <td><?= strtoupper($order['payment_method']) ?></td>
                                <td><span class="status-badge <?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                                <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <select onchange="if(this.value) location.href=this.value" style="padding:6px 10px;border:1px solid var(--gray-300);border-radius:6px;font-size:12px;font-family:var(--font)">
                                        <option value="">Change Status</option>
                                        <?php foreach (['processing','shipped','delivered','cancelled'] as $s): ?>
                                            <option value="?action=update_status&id=<?= $order['id'] ?>&status_to=<?= $s ?>" <?= $order['status'] === $s ? 'disabled' : '' ?>>
                                                <?= ucfirst($s) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($orders)): ?>
                            <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--gray-500)">No orders yet</td></tr>
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