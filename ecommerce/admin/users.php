<?php
// admin/users.php
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'activate') {
        $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$id]);
        flash('message', 'User activated!', 'success');
    } elseif ($action === 'deactivate') {
        $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ?")->execute([$id]);
        flash('message', 'User deactivated.', 'warning');
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'")->execute([$id]);
        flash('message', 'User deleted.', 'danger');
    }
    
    header('Location: ' . SITE_URL . '/admin/users.php' . (isset($_GET['status']) ? '?status=' . $_GET['status'] : ''));
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$roleFilter = $_GET['role'] ?? '';
$where = "role != 'admin'";
$params = [];

if ($statusFilter && in_array($statusFilter, ['active', 'inactive', 'pending'])) {
    $where .= " AND status = ?";
    $params[] = $statusFilter;
}
if ($roleFilter && in_array($roleFilter, ['buyer', 'seller'])) {
    $where .= " AND role = ?";
    $params[] = $roleFilter;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE $where ORDER BY created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();
$user = currentUser($pdo);
$flash_msg = flash('message');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?= SITE_NAME ?></title>
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
            <a href="<?= SITE_URL ?>/admin/users.php" class="active"><i class="fas fa-users"></i> Users</a>
            <a href="<?= SITE_URL ?>/admin/orders.php"><i class="fas fa-shopping-bag"></i> Orders</a>
            <div class="nav-label">Quick Links</div>
            <a href="<?= SITE_URL ?>"><i class="fas fa-globe"></i> View Store</a>
            <a href="<?= SITE_URL ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>
    
    <main class="dashboard-main">
        <div class="dashboard-header">
            <div>
                <h1>Manage Users</h1>
                <div class="breadcrumb"><a href="<?= SITE_URL ?>/admin/">Dashboard</a> <i class="fas fa-chevron-right"></i> Users</div>
            </div>
        </div>
        
        <?php if ($flash_msg): ?>
            <div class="alert alert-<?= $flash_msg['type'] ?>"><i class="fas fa-info-circle"></i> <?= $flash_msg['message'] ?></div>
        <?php endif; ?>
        
        <div style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap">
            <a href="?role=seller&status=pending" class="btn btn-sm" style="background:#fff3cd;color:#856404">Pending Sellers</a>
            <a href="?role=seller" class="btn btn-sm" style="background:#e3f2fd;color:#1565c0">All Sellers</a>
            <a href="?role=buyer" class="btn btn-sm" style="background:#e8f5e9;color:#388e3c">All Buyers</a>
            <a href="<?= SITE_URL ?>/admin/users.php" class="btn btn-sm" style="background:var(--gray-200);color:var(--gray-700)">All Users</a>
        </div>
        
        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Shop Name</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <div class="avatar" style="width:36px;height:36px;border-radius:50%;background:var(--primary);color:white;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0">
                                            <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                        </div>
                                        <strong><?= htmlspecialchars($u['name']) ?></strong>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><span style="text-transform:capitalize;font-weight:600"><?= $u['role'] ?></span></td>
                                <td><?= htmlspecialchars($u['shop_name'] ?? '—') ?></td>
                                <td><span class="status-badge <?= $u['status'] ?>"><?= ucfirst($u['status']) ?></span></td>
                                <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <div class="action-btns">
                                        <?php if ($u['status'] === 'pending' || $u['status'] === 'inactive'): ?>
                                            <a href="?action=activate&id=<?= $u['id'] ?>&status=<?= $statusFilter ?>&role=<?= $roleFilter ?>" class="action-btn approve" title="Activate"><i class="fas fa-check"></i></a>
                                        <?php endif; ?>
                                        <?php if ($u['status'] === 'active'): ?>
                                            <a href="?action=deactivate&id=<?= $u['id'] ?>&status=<?= $statusFilter ?>&role=<?= $roleFilter ?>" class="action-btn reject" title="Deactivate"><i class="fas fa-ban"></i></a>
                                        <?php endif; ?>
                                        <a href="?action=delete&id=<?= $u['id'] ?>" class="action-btn delete" title="Delete" onclick="return confirm('Delete this user?')"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--gray-500)">No users found</td></tr>
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