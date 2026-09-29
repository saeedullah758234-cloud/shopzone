<?php
// admin/products.php
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        $pdo->prepare("UPDATE products SET status = 'approved' WHERE id = ?")->execute([$id]);
        flash('message', 'Product approved successfully!', 'success');
    } elseif ($action === 'reject') {
        $pdo->prepare("UPDATE products SET status = 'rejected' WHERE id = ?")->execute([$id]);
        flash('message', 'Product rejected.', 'warning');
    } elseif ($action === 'feature') {
        $pdo->prepare("UPDATE products SET featured = NOT featured WHERE id = ?")->execute([$id]);
        flash('message', 'Product featured status updated.', 'success');
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        flash('message', 'Product deleted.', 'danger');
    }
    
    header('Location: ' . SITE_URL . '/admin/products.php' . (isset($_GET['status']) ? '?status=' . $_GET['status'] : ''));
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$where = "1=1";
$params = [];
if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
    $where = "p.status = ?";
    $params[] = $statusFilter;
}

$search = $_GET['search'] ?? '';
if ($search) {
    $where .= " AND p.name LIKE ?";
    $params[] = "%{$search}%";
}

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, u.shop_name 
    FROM products p JOIN categories c ON p.category_id = c.id JOIN users u ON p.seller_id = u.id
    WHERE $where ORDER BY p.created_at DESC");
$stmt->execute($params);
$products = $stmt->fetchAll();
$user = currentUser($pdo);
$flash_msg = flash('message');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - <?= SITE_NAME ?></title>
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
            <a href="<?= SITE_URL ?>/admin/products.php" class="active"><i class="fas fa-box"></i> Products</a>
            <a href="<?= SITE_URL ?>/admin/users.php"><i class="fas fa-users"></i> Users</a>
            <a href="<?= SITE_URL ?>/admin/orders.php"><i class="fas fa-shopping-bag"></i> Orders</a>
            <div class="nav-label">Quick Links</div>
            <a href="<?= SITE_URL ?>"><i class="fas fa-globe"></i> View Store</a>
            <a href="<?= SITE_URL ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>
    
    <main class="dashboard-main">
        <div class="dashboard-header">
            <div>
                <h1>Manage Products</h1>
                <div class="breadcrumb"><a href="<?= SITE_URL ?>/admin/">Dashboard</a> <i class="fas fa-chevron-right"></i> Products</div>
            </div>
            <button class="menu-toggle sidebar-toggle"><i class="fas fa-bars"></i></button>
        </div>
        
        <?php if ($flash_msg): ?>
            <div class="alert alert-<?= $flash_msg['type'] ?>">
                <i class="fas fa-info-circle"></i> <?= $flash_msg['message'] ?>
            </div>
        <?php endif; ?>
        
        <!-- Filters -->
        <div style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap;align-items:center">
            <a href="<?= SITE_URL ?>/admin/products.php" class="btn btn-sm <?= !$statusFilter ? 'btn-primary' : '' ?>" style="<?= $statusFilter ? 'background:var(--gray-200);color:var(--gray-700)' : '' ?>">All</a>
            <a href="?status=pending" class="btn btn-sm <?= $statusFilter === 'pending' ? 'btn-primary' : '' ?>" style="<?= $statusFilter !== 'pending' ? 'background:#fff3cd;color:#856404' : '' ?>">Pending</a>
            <a href="?status=approved" class="btn btn-sm <?= $statusFilter === 'approved' ? 'btn-primary' : '' ?>" style="<?= $statusFilter !== 'approved' ? 'background:#d4edda;color:#155724' : '' ?>">Approved</a>
            <a href="?status=rejected" class="btn btn-sm <?= $statusFilter === 'rejected' ? 'btn-primary' : '' ?>" style="<?= $statusFilter !== 'rejected' ? 'background:#f8d7da;color:#721c24' : '' ?>">Rejected</a>
            
            <form style="margin-left:auto;display:flex;gap:8px">
                <?php if ($statusFilter): ?><input type="hidden" name="status" value="<?= $statusFilter ?>"><?php endif; ?>
                <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>" style="padding:8px 16px;width:220px">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
            </form>
        </div>
        
        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Seller</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): 
                            $img = $p['image'] && $p['image'] !== 'no-image.png' 
                                ? SITE_URL . '/assets/images/uploads/' . $p['image'] 
                                : 'https://via.placeholder.com/50';
                        ?>
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <img src="<?= $img ?>" alt="">
                                        <div>
                                            <strong><?= htmlspecialchars($p['name']) ?></strong>
                                            <?php if ($p['featured']): ?><br><span class="badge badge-featured" style="font-size:10px">Featured</span><?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($p['category_name']) ?></td>
                                <td><?= htmlspecialchars($p['shop_name'] ?? 'N/A') ?></td>
                                <td>
                                    <strong><?= formatPrice($p['sale_price'] ?: $p['price']) ?></strong>
                                    <?php if ($p['sale_price']): ?><br><small style="text-decoration:line-through;color:var(--gray-500)"><?= formatPrice($p['price']) ?></small><?php endif; ?>
                                </td>
                                <td><?= $p['stock'] ?></td>
                                <td><span class="status-badge <?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                                <td>
                                    <div class="action-btns">
                                        <?php if ($p['status'] === 'pending'): ?>
                                            <a href="?action=approve&id=<?= $p['id'] ?>&status=<?= $statusFilter ?>" class="action-btn approve" title="Approve"><i class="fas fa-check"></i></a>
                                            <a href="?action=reject&id=<?= $p['id'] ?>&status=<?= $statusFilter ?>" class="action-btn reject" title="Reject"><i class="fas fa-times"></i></a>
                                        <?php endif; ?>
                                        <a href="<?= SITE_URL ?>/product.php?id=<?= $p['id'] ?>" class="action-btn view" title="View"><i class="fas fa-eye"></i></a>
                                        <a href="?action=feature&id=<?= $p['id'] ?>&status=<?= $statusFilter ?>" class="action-btn edit" title="Toggle Featured"><i class="fas fa-star"></i></a>
                                        <a href="?action=delete&id=<?= $p['id'] ?>&status=<?= $statusFilter ?>" class="action-btn delete" title="Delete" onclick="return confirm('Delete this product?')"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($products)): ?>
                            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--gray-500)">No products found</td></tr>
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