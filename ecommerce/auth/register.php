<?php
// auth/register.php
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit;
}

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'buyer';
    $shop_name = trim($_POST['shop_name'] ?? '');
    
    $old = $_POST;
    
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
    if ($password !== $confirm) $errors[] = 'Passwords do not match';
    if (!in_array($role, ['buyer', 'seller'])) $role = 'buyer';
    if ($role === 'seller' && empty($shop_name)) $errors[] = 'Shop name is required for sellers';
    
    // Check if email exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = 'Email already registered';
    }
    
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $status = ($role === 'seller') ? 'pending' : 'active';
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, role, status, shop_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashedPassword, $phone, $role, $status, $shop_name]);
        
        if ($role === 'seller') {
            flash('message', 'Registration successful! Your seller account is pending approval.', 'warning');
        } else {
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_role'] = $role;
            flash('message', 'Welcome to ' . SITE_NAME . '!', 'success');
        }
        
        header('Location: ' . ($role === 'seller' ? SITE_URL . '/auth/login.php' : SITE_URL));
        exit;
    }
}

$defaultRole = $_GET['role'] ?? 'buyer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <a href="<?= SITE_URL ?>" class="logo">
            <i class="fas fa-shopping-bag"></i> Shop<span>Zone</span>
        </a>
        <h2>Create Account</h2>
        <p class="auth-subtitle">Join us and start shopping today</p>
        
        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= implode('<br>', $errors) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="role-selector">
                <div class="role-option">
                    <input type="radio" name="role" value="buyer" id="role-buyer" 
                           <?= ($old['role'] ?? $defaultRole) === 'buyer' ? 'checked' : '' ?>>
                    <label for="role-buyer">
                        <i class="fas fa-user"></i>
                        <span>Buyer</span>
                    </label>
                </div>
                <div class="role-option">
                    <input type="radio" name="role" value="seller" id="role-seller"
                           <?= ($old['role'] ?? $defaultRole) === 'seller' ? 'checked' : '' ?>>
                    <label for="role-seller">
                        <i class="fas fa-store"></i>
                        <span>Seller</span>
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="John Doe" 
                       value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="john@example.com" 
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" class="form-control" placeholder="+1 234 567 8900" 
                       value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
            </div>
            
            <div class="form-group" id="shop-name-group" style="display:none;">
                <label>Shop Name</label>
                <input type="text" name="shop_name" class="form-control" placeholder="My Awesome Store" 
                       value="<?= htmlspecialchars($old['shop_name'] ?? '') ?>">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Min 6 characters" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block btn-lg">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>
        
        <div class="auth-footer">
            Already have an account? <a href="<?= SITE_URL ?>/auth/login.php">Sign In</a>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('input[name="role"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('shop-name-group').style.display = 
            this.value === 'seller' ? 'block' : 'none';
    });
});
// Trigger on load
document.querySelector('input[name="role"]:checked')?.dispatchEvent(new Event('change'));
</script>
</body>
</html>