<?php
// shop.php
$pageTitle = 'Shop - Zone '. 'Shop - Zone';
require_once 'includes/header.php';

$where = ["p.status = 'approved'"];
$params = [];

// Category Filter
if (!empty($_GET['category'])) {
    $where[] = "p.category_id = ?";
    $params[] = (int)$_GET['category'];
}

// Search
if (!empty($_GET['search'])) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = '%' . $_GET['search'] . '%';
    $params[] = '%' . $_GET['search'] . '%';
}

// Featured
if (!empty($_GET['featured'])) {
    $where[] = "p.featured = 1";
}

// Price Range
if (!empty($_GET['min_price'])) {
    $where[] = "(IFNULL(p.sale_price, p.price)) >= ?";
    $params[] = (float)$_GET['min_price'];
}
if (!empty($_GET['max_price'])) {
    $where[] = "(IFNULL(p.sale_price, p.price)) <= ?";
    $params[] = (float)$_GET['max_price'];
}

$whereClause = implode(' AND ', $where);

// Sorting
$sort = $_GET['sort'] ?? 'newest';
$orderBy = match($sort) {
    'price_low'  => 'IFNULL(p.sale_price, p.price) ASC',
    'price_high' => 'IFNULL(p.sale_price, p.price) DESC',
    'popular'    => 'p.views DESC',
    'sale'       => 'p.sale_price IS NOT NULL DESC, p.created_at DESC',
    default      => 'p.created_at DESC'
};

// Pagination
$perPage = 16;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE $whereClause");
$countStmt->execute($params);
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $perPage);

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, u.shop_name,
    (SELECT AVG(rating) FROM reviews WHERE product_id = p.id) as avg_rating,
    (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) as review_count
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    JOIN users u ON p.seller_id = u.id
    WHERE $whereClause
    ORDER BY $orderBy
    LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

$activeCategory = $_GET['category'] ?? null;
?>

<div class="container">
    <div class="shop-layout">
        <!-- Sidebar -->
        <aside class="shop-sidebar">
            <div class="filter-group">
                <h4><i class="fas fa-list"></i> Categories</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/shop.php" class="<?= !$activeCategory ? 'active' : '' ?>">All Categories</a></li>
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="<?= SITE_URL ?>/shop.php?category=<?= $cat['id'] ?>" 
                               class="<?= $activeCategory == $cat['id'] ? 'active' : '' ?>">
                                <span><i class="fas <?= $cat['icon'] ?>"></i> <?= htmlspecialchars($cat['name']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="filter-group">
                <h4><i class="fas fa-dollar-sign"></i> Price Range</h4>
                <form method="GET" action="<?= SITE_URL ?>/shop.php">
                    <?php if ($activeCategory): ?>
                        <input type="hidden" name="category" value="<?= $activeCategory ?>">
                    <?php endif; ?>
                    <div class="form-row" style="margin-bottom:12px">
                        <input type="number" name="min_price" class="form-control" placeholder="Min" 
                               value="<?= $_GET['min_price'] ?? '' ?>" style="padding:8px 12px">
                        <input type="number" name="max_price" class="form-control" placeholder="Max" 
                               value="<?= $_GET['max_price'] ?? '' ?>" style="padding:8px 12px">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm btn-block">Apply Filter</button>
                </form>
            </div>
        </aside>
        
        <!-- Products -->
        <div class="shop-content">
            <div class="shop-toolbar">
                <span class="result-count">
                    Showing <?= count($products) ?> of <?= $totalProducts ?> products
                </span>
                <select class="sort-select" onchange="location.href=this.value">
                    <?php 
                    $baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
                    $queryParams = $_GET;
                    foreach (['newest','popular','price_low','price_high','sale'] as $s):
                        $queryParams['sort'] = $s;
                        $labels = ['newest'=>'Newest First','popular'=>'Most Popular','price_low'=>'Price: Low to High','price_high'=>'Price: High to Low','sale'=>'On Sale'];
                    ?>
                        <option value="<?= SITE_URL . '/shop.php?' . http_build_query($queryParams) ?>" 
                                <?= $sort === $s ? 'selected' : '' ?>>
                            <?= $labels[$s] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <?php if ($products): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <?php include __DIR__ . '/includes/product-card.php'; ?>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php 
                        $queryParams = $_GET;
                        for ($i = 1; $i <= $totalPages; $i++):
                            $queryParams['page'] = $i;
                        ?>
                            <?php if ($i === $page): ?>
                                <span class="active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= SITE_URL . '/shop.php?' . http_build_query($queryParams) ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h3>No Products Found</h3>
                    <p>Try adjusting your search or filters</p>
                    <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary">Clear Filters</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>