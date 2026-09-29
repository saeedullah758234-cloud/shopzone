<?php
// product.php
require_once 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . SITE_URL); exit; }

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, u.shop_name, u.id as sid,
    (SELECT AVG(rating) FROM reviews WHERE product_id = p.id) as avg_rating,
    (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) as review_count
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    JOIN users u ON p.seller_id = u.id 
    WHERE p.id = ? AND p.status = 'approved'");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) { header('Location: ' . SITE_URL); exit; }

// Increment views
$pdo->prepare("UPDATE products SET views = views + 1 WHERE id = ?")->execute([$id]);

// Reviews
$stmt = $pdo->prepare("SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();

// Handle Review Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn() && isset($_POST['rating'])) {
    $rating = max(1, min(5, (int)$_POST['rating']));
    $comment = trim($_POST['comment'] ?? '');
    
    $stmt = $pdo->prepare("INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE rating = ?, comment = ?");
    $stmt->execute([$_SESSION['user_id'], $id, $rating, $comment, $rating, $comment]);
    header('Location: ' . SITE_URL . '/product.php?id=' . $id . '#reviews');
    exit;
}

// Related Products
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, u.shop_name,
    (SELECT AVG(rating) FROM reviews WHERE product_id = p.id) as avg_rating,
    (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) as review_count
    FROM products p JOIN categories c ON p.category_id = c.id JOIN users u ON p.seller_id = u.id
    WHERE p.category_id = ? AND p.id != ? AND p.status = 'approved' LIMIT 4");
$stmt->execute([$product['category_id'], $id]);
$related = $stmt->fetchAll();

$salePrice = $product['sale_price'] ?? null;
$hasDiscount = $salePrice && $salePrice < $product['price'];
$displayPrice = $hasDiscount ? $salePrice : $product['price'];
$discount = $hasDiscount ? round((($product['price'] - $salePrice) / $product['price']) * 100) : 0;
$rating = round($product['avg_rating'] ?? 0, 1);

$imgSrc = $product['image'] && $product['image'] !== 'no-image.png' 
    ? SITE_URL . '/assets/images/uploads/' . $product['image'] 
    : 'https://via.placeholder.com/600x600?text=' . urlencode($product['name']);

$pageTitle = $product['name'] . ' - ' . SITE_NAME;
require_once 'includes/header.php';
?>

<div class="container product-detail">
    <div class="breadcrumb" style="margin-bottom:24px">
        <a href="<?= SITE_URL ?>">Home</a> <i class="fas fa-chevron-right"></i>
        <a href="<?= SITE_URL ?>/shop.php?category=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name']) ?></a> 
        <i class="fas fa-chevron-right"></i>
        <span><?= htmlspecialchars($product['name']) ?></span>
    </div>
    
    <div class="product-detail-grid">
        <div class="product-gallery">
            <div class="main-image">
                <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
        </div>
        
        <div class="product-detail-info">
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            
            <div class="rating-summary">
                <span class="stars"><?= getStarRating($rating) ?></span>
                <span><?= $rating ?> (<?= $product['review_count'] ?> reviews)</span>
                <span style="color:var(--gray-400)">|</span>
                <span><?= $product['views'] ?> views</span>
            </div>
            
            <div class="price-box">
                <span class="current"><?= formatPrice($displayPrice) ?></span>
                <?php if ($hasDiscount): ?>
                    <span class="original"><?= formatPrice($product['price']) ?></span>
                    <span class="save">Save <?= $discount ?>%</span>
                <?php endif; ?>
            </div>
            
            <div class="product-meta">
                <div class="meta-item">
                    <span>Category</span>
                    <strong><?= htmlspecialchars($product['category_name']) ?></strong>
                </div>
                <div class="meta-item">
                    <span>Seller</span>
                    <strong><?= htmlspecialchars($product['shop_name'] ?? 'N/A') ?></strong>
                </div>
                <div class="meta-item">
                    <span>Availability</span>
                    <strong style="color:<?= $product['stock'] > 0 ? 'var(--success)' : 'var(--danger)' ?>">
                        <?= $product['stock'] > 0 ? "In Stock ({$product['stock']})" : 'Out of Stock' ?>
                    </strong>
                </div>
            </div>
            
            <?php if ($product['description']): ?>
                <div style="margin-bottom:24px">
                    <h4 style="margin-bottom:8px">Description</h4>
                    <p style="color:var(--gray-600);font-size:14px;line-height:1.8"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($product['stock'] > 0): ?>
                <div class="quantity-selector">
                    <label style="font-weight:600;margin-right:12px">Qty:</label>
                    <button onclick="changeQty(document.getElementById('qty'), -1)">−</button>
                    <input type="number" id="qty" value="1" min="1" max="<?= $product['stock'] ?>">
                    <button onclick="changeQty(document.getElementById('qty'), 1)">+</button>
                </div>
                
                <div class="buy-actions">
                    <button class="btn btn-primary btn-lg" onclick="addToCart(<?= $product['id'] ?>, document.getElementById('qty').value)">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Reviews Section -->
    <div class="card" style="margin-top:40px" id="reviews">
        <div class="card-header">
            <h3>Customer Reviews (<?= count($reviews) ?>)</h3>
        </div>
        <div class="card-body">
            <?php if (isLoggedIn() && getUserRole() === 'buyer'): ?>
                <form method="POST" style="margin-bottom:30px;padding:20px;background:var(--gray-100);border-radius:var(--radius-sm)">
                    <h4 style="margin-bottom:12px">Write a Review</h4>
                    <div class="form-group">
                        <label>Rating</label>
                        <select name="rating" class="form-control" required style="max-width:200px">
                            <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                            <option value="4">⭐⭐⭐⭐ Good</option>
                            <option value="3">⭐⭐⭐ Average</option>
                            <option value="2">⭐⭐ Poor</option>
                            <option value="1">⭐ Terrible</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Comment</label>
                        <textarea name="comment" class="form-control" rows="3" placeholder="Share your experience..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Submit Review</button>
                </form>
            <?php endif; ?>
            
            <?php if ($reviews): ?>
                <?php foreach ($reviews as $review): ?>
                    <div style="padding:16px 0;border-bottom:1px solid var(--gray-200)">
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
                            <strong><?= htmlspecialchars($review['user_name']) ?></strong>
                            <span class="stars" style="color:#ffc107;font-size:13px"><?= getStarRating($review['rating']) ?></span>
                            <span style="color:var(--gray-500);font-size:12px"><?= timeAgo($review['created_at']) ?></span>
                        </div>
                        <p style="color:var(--gray-600);font-size:14px"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:var(--gray-500);text-align:center;padding:30px 0">No reviews yet. Be the first to review!</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if ($related): ?>
    <section class="section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-magic"></i> Related Products</h2>
        </div>
        <div class="products-grid">
            <?php foreach ($related as $product): ?>
                <?php include 'includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>