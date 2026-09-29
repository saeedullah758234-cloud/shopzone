<?php
// index.php
require_once 'includes/functions.php';

$pageTitle = SITE_NAME. 'Shop Zone'. ' - Shop Everything You Love';
require_once 'includes/header.php';

// Featured Products
$stmt = $pdo->query("SELECT p.*, c.name as category_name, u.shop_name,
    (SELECT AVG(rating) FROM reviews WHERE product_id = p.id) as avg_rating,
    (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) as review_count
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    JOIN users u ON p.seller_id = u.id
    WHERE p.status = 'approved' AND p.featured = 1
    ORDER BY p.created_at DESC LIMIT 8");
$featured = $stmt->fetchAll();

// Latest Products
$stmt = $pdo->query("SELECT p.*, c.name as category_name, u.shop_name,
    (SELECT AVG(rating) FROM reviews WHERE product_id = p.id) as avg_rating,
    (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) as review_count
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    JOIN users u ON p.seller_id = u.id
    WHERE p.status = 'approved'
    ORDER BY p.created_at DESC LIMIT 12");
$latest = $stmt->fetchAll();

// Sale Products
$stmt = $pdo->query("SELECT p.*, c.name as category_name, u.shop_name,
    (SELECT AVG(rating) FROM reviews WHERE product_id = p.id) as avg_rating,
    (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) as review_count
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    JOIN users u ON p.seller_id = u.id
    WHERE p.status = 'approved' AND p.sale_price IS NOT NULL AND p.sale_price > 0
    ORDER BY RAND() LIMIT 8");
$saleProducts = $stmt->fetchAll();
?>

<?= renderFlash('msg') ?>

<!-- Hero Banner -->
<section class="hero">
    <div class="container">
        <div class="hero-inner">
            <span class="hero-badge">🔥 Mega Seasonal Sale is Live!</span>
            <h1>Discover Premium Deals Today</h1>
            <p>Experience safe e-commerce shopping with reliable vendors, secure transactions, and highly affordable delivery pricing structures.</p>
            <div class="hero-btns">
                <a href="<?= SITE_URL ?>/shop.php" class="btn btn-white btn-lg">
                    <i class="fas fa-shopping-bag"></i> Start Shopping
                </a>
                <a href="<?= SITE_URL ?>/auth/register.php?role=seller" class="btn btn-outline-w btn-lg">
                    <i class="fas fa-store"></i> Register as Vendor
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2 class="section-title"><i class="fas fa-th-large"></i> Explore Categories</h2>
            <a href="<?= SITE_URL ?>/shop.php" class="view-all">All Products <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="<?= SITE_URL ?>/shop.php?category=<?= $cat['id'] ?>" class="cat-card">
                    <i class="fas <?= e($cat['icon']) ?>"></i>
                    <span><?= e($cat['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Sale Products Section -->
<?php if (!empty($saleProducts)): ?>
<section class="section" style="background: var(--g200);">
    <div class="container">
        <div class="section-head">
            <h2 class="section-title"><i class="fas fa-fire" style="color: var(--danger);"></i> Flash Deals On Sale</h2>
            <a href="<?= SITE_URL ?>/shop.php?sort=sale" class="view-all">Browse Discounts <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="products-grid">
            <?php foreach ($saleProducts as $product): ?>
                <?php include 'includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured Products Section -->
<?php if (!empty($featured)): ?>
<section class="section" style="background: var(--white)">
    <div class="container">
        <div class="section-head">
            <h2 class="section-title"><i class="fas fa-star" style="color: var(--warning);"></i> Top Featured Items</h2>
            <a href="<?= SITE_URL ?>/shop.php?featured=1" class="view-all">See Featured Items <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="products-grid">
            <?php foreach ($featured as $product): ?>
                <?php include 'includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Latest Products Section -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2 class="section-title"><i class="fas fa-clock"></i> New Arrivals Today</h2>
            <a href="<?= SITE_URL ?>/shop.php?sort=newest" class="view-all">See All New Arrivals <i class="fas fa-arrow-right"></i></a>
        </div>
        <?php if (!empty($latest)): ?>
            <div class="products-grid">
                <?php foreach ($latest as $product): ?>
                    <?php include 'includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-box">
                <i class="fas fa-box-open"></i>
                <h3>No Items Available</h3>
                <p>Register as a vendor and list your items here first!</p>
                <a href="<?= SITE_URL ?>/auth/register.php?role=seller" class="btn btn-primary">Establish Your Store</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>