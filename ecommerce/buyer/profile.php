<?php
// buyer/profile.php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$user = currentUser($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?")
        ->execute([$name, $phone, $address, $user['id']]);
    
    // Password change
    if (!empty($_POST['new_password'])) {
        if (password_verify($_POST['current_password'], $user['password'])) {
            $hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $user['id']]);
        }
    }
    
    flash('message', 'Profile updated successfully!', 'success');
    header('Location: ' . SITE_URL . '/buyer/profile.php');
    exit;
}

$pageTitle = 'My Profile - ' . SITE_NAME;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding:40px 20px;max-width:700px">
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-user"></i> My Profile</h2>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email (cannot change)</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>
                <hr style="margin:24px 0;border-color:var(--gray-200)">
                <h4 style="margin-bottom:16px">Change Password</h4>
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control">
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>