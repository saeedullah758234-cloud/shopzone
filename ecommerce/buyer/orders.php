<?php
// buyer/orders.php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
$pageTitle = 'My Orders - ' . SITE_NAME;
require_once __DIR__ . '/../includes/header.php';

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<div class="container" style="padding:40px 20px">
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-box"></i> My Orders</h2>
    </div>
    
    <?php if ($orders): ?>
        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order):
                            $stmt2 = $pdo->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                            $stmt2->execute([$order['id']]);
                            $items = $stmt2->fetchAll();
                        ?>
                            <tr>
                                <td><strong>#<?= $order['id'] ?></strong></td>
                                <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <?php foreach ($items as $item): ?>
                                        <small><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></small><br>
                                    <?php endforeach; ?>
                                </td>
                                <td><strong><?= formatPrice($order['total']) ?></strong></td>
                                <td><?= strtoupper($order['payment_method']) ?></td>
                                <td><span class="status-badge <?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>No Orders Yet</h3>
            <p>You haven't placed any orders yet.</p>
            <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>