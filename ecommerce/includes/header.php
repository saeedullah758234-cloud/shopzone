<?php
// includes/header.php
if (!isset($pdo)) require_once __DIR__ . '/functions.php';
$categories = getCategories($pdo);
$cartCount = getCartCount($pdo);
$user = currentUser($pdo);
$flash_msg = flash('message');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <script>const SITE_URL = '<?= SITE_URL ?>';</script>
</head>
<body>

<!-- Top Bar -->
<div class="top-bar">
    <div class="container">
        <div>
            <span><i class="fas fa-truck"></i> Free Shipping on orders over $50</span>
        </div>
        <div>
            <?php if (isLoggedIn()): ?>
                <a href="<?= SITE_URL ?>/buyer/orders.php"><i class="fas fa-box"></i> Track Order</a>
                <a href="<?= SITE_URL ?>/buyer/profile.php"><i class="fas fa-user"></i> My Account</a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/auth/login.php">Login</a>
                <a href="<?= SITE_URL ?>/auth/register.php">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Header -->
<header class="header">
    <div class="header-main">
        <div class="container">
            <button class="menu-toggle" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>
            
            <a href="<?= SITE_URL ?>" class="logo">
                <i class="fas fa-shopping-bag"></i>
                Shop<span>Zone</span>
            </a>
            
            <div class="search-bar">
                <form action="<?= SITE_URL ?>/shop.php" method="GET">
                    <input type="text" name="search" placeholder="Search for products, brands and more..." 
                           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            
            <div class="header-actions">
                <?php if (isLoggedIn()): ?>
                    <?php if (getUserRole() === 'admin'): ?>
                        <a href="<?= SITE_URL ?>/admin/" class="header-btn btn-seller">
                            <i class="fas fa-shield-alt"></i>
                            <span>Admin</span>
                        </a>
                    <?php elseif (getUserRole() === 'seller'): ?>
                        <a href="<?= SITE_URL ?>/seller/" class="header-btn btn-seller">
                            <i class="fas fa-store"></i>
                            <span>Dashboard</span>
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?= SITE_URL ?>/buyer/cart.php" class="header-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Cart</span>
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-badge"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <a href="<?= SITE_URL ?>/buyer/profile.php" class="header-btn">
                        <i class="fas fa-user-circle"></i>
                        <span><?= htmlspecialchars($user['name'] ?? 'Account') ?></span>
                    </a>
                    
                    <a href="<?= SITE_URL ?>/auth/logout.php" class="header-btn">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/auth/login.php" class="header-btn">
                        <i class="fas fa-user"></i>
                        <span>Login</span>
                    </a>
                    <a href="<?= SITE_URL ?>/auth/register.php?role=seller" class="header-btn btn-seller">
                        <i class="fas fa-store"></i>
                        <span>Sell</span>
                    </a>
                    <a href="<?= SITE_URL ?>/buyer/cart.php" class="header-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Cart</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <nav class="nav-bar">
        <div class="container">
            <div class="categories-dropdown">
                <button class="categories-btn">
                    <i class="fas fa-th"></i>
                    All Categories
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="categories-menu">
                    <?php foreach ($categories as $cat): ?>
                        <a href="<?= SITE_URL ?>/shop.php?category=<?= $cat['id'] ?>">
                            <i class="fas <?= $cat['icon'] ?>"></i>
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="nav-links">
                <a href="<?= SITE_URL ?>" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' && !isset($_GET['page']) ? 'active' : '' ?>">Home</a>
                <a href="<?= SITE_URL ?>/shop.php">Shop</a>
                <a href="<?= SITE_URL ?>/shop.php?featured=1">Featured</a>
                <a href="<?= SITE_URL ?>/shop.php?sort=newest">New Arrivals</a>
                <a href="<?= SITE_URL ?>/shop.php?sort=sale">Deals</a>
            </div>
        </div>
    </nav>
</header>

<?php if ($flash_msg): ?>
<div class="container" style="margin-top:20px;">
    <div class="alert alert-<?= $flash_msg['type'] ?>">
        <i class="fas fa-<?= $flash_msg['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= $flash_msg['message'] ?>
    </div>
</div>
<?php endif; ?>