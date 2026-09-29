<?php
// includes/product-card.php
if (isset($product)) {
    $price = getProductPrice($product);
    $discount = getDiscount($product);
    $img = productImage($product['image']);
    $rating = round($product['avg_rating'] ?? 0, 1);
    $reviews = $product['review_count'] ?? 0;
    $url = SITE_URL . '/product.php?id=' . $product['id'];
?>
<div class="product-card">
    <div class="card-img-wrap">
        <a href="<?= $url ?>"><img src="<?= $img ?>" alt="<?= e($product['name']) ?>" loading="lazy"></a>
        <div class="card-badges">
            <?php if ($discount > 0): ?>
                <span class="badge badge-sale">-<?= $discount ?>%</span>
            <?php endif; ?>
            <?php if (!empty($product['featured'])): ?>
                <span class="badge badge-hot">HOT</span>
            <?php endif; ?>
        </div>
        <div class="card-hover-actions">
            <button onclick="addToCart(<?= $product['id'] ?>)" title="Add to Cart"><i class="fas fa-cart-plus"></i></button>
            <a href="<?= $url ?>" title="View"><i class="fas fa-eye"></i></a>
        </div>
    </div>
    <div class="card-body">
        <span class="card-category"><?= e($product['category_name'] ?? '') ?></span>
        <h3 class="card-title"><a href="<?= $url ?>"><?= e($product['name']) ?></a></h3>
        <div class="card-rating">
            <span class="stars"><?= getStarRating($rating) ?></span>
            <span class="count">(<?= $reviews ?>)</span>
        </div>
        <div class="card-price">
            <span class="current-price"><?= formatPrice($price) ?></span>
            <?php if ($discount > 0): ?>
                <span class="original-price"><?= formatPrice($product['price']) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-foot">
        <button class="btn-add-cart" onclick="addToCart(<?= $product['id'] ?>)">
            <i class="fas fa-shopping-cart"></i>
            <span>Add to Cart</span>
        </button>
    </div>
</div>
<?php }