<?php
// auth/login.php
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit;
}

$errors = [];
$flash_msg = flash('message');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $errors[] = 'Please fill in all fields';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'inactive') {
                $errors[] = 'Your account has been deactivated';
            } elseif ($user['status'] === 'pending') {
                $errors[] = 'Your seller account is pending admin approval';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                
                $redirect = SITE_URL;
                if ($user['role'] === 'admin') $redirect = SITE_URL . '/admin/';
                elseif ($user['role'] === 'seller') $redirect = SITE_URL . '/seller/';
                
                header('Location: ' . $redirect);
                exit;
            }
        } else {
            $errors[] = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= SITE_NAME ?></title>
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
        <h2>Welcome Back</h2>
        <p class="auth-subtitle">Sign in to your account</p>
        
        <?php if ($flash_msg): ?>
            <div class="alert alert-<?= $flash_msg['type'] ?>">
                <i class="fas fa-info-circle"></i> <?= $flash_msg['message'] ?>
            </div>
        <?php endif; ?>
        
        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= implode('<br>', $errors) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="your@email.com" 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>
        
        <div class="auth-footer">
            Don't have an account? <a href="<?= SITE_URL ?>/auth/register.php">Create one</a>
            <br><br>
            <small style="color:var(--gray-400)">Admin: admin@admin.com / password</small>
        </div>
    </div>
</div>
</body>
</html>