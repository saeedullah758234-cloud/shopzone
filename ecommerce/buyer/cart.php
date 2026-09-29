<?php
// buyer/cart.php
require_once __DIR__ . '/../includes/functions.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'login_required' => true]);
        exit;
    }
    
    $action = $_POST['action'] ?? '';
    $userId = $_SESSION['user_id'];
    
    if ($action === 'add') {
        $productId = (int)$_POST['product_id'];
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        
        $stmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        
        if ($stmt->fetch()) {
            $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?")
                ->execute([$qty, $userId, $productId]);
        } else {
            $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)")
                ->execute([$userId, $productId, $qty]);
        }
        
        echo json_encode(['success' => true, 'cart_count' => getCartCount($pdo)]);
        exit;
    }
    
    if ($action === 'remove') {
        $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?")->execute([(int)$_POST['cart_id'], $userId]);
        echo json_encode(['success' => true]);
        exit;
    }
    
    if ($action === 'update') {
        $qty = max(1, (int)$_POST['quantity']);
        $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?")->execute([$qty, (int)$_POST['cart_id'], $userId]);
        echo json_encode(['success' => true]);
        exit;
    }
    
    echo json_encode(['success' => false]);
    exit;
}

requireLogin();
$pageTitle = 'My Cart - ' . SITE_NAME;
require_once __DIR__ . '/../includes/header.php';

$stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock, u.shop_name
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    JOIN users u ON p.seller_id = u.id
    WHERE c.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

$subtotal = 0;
foreach ($cartItems as $item) {
    $price = ($item['sale_price'] && $item['sale_price'] < $item['price']) ? $item['sale_price'] : $item['price'];
    $subtotal += $price * $item['quantity'];
}
$shipping = $subtotal >= 50 ? 0 : 5.99;
$total = $subtotal + $shipping;
?>

<div class="container cart-page">
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-shopping-cart"></i> Shopping Cart</h2>
    </div>
    
    <?php if ($cartItems): ?>
        <div class="cart-grid">
            <div class="cart-items">
                <?php foreach ($cartItems as $item): 
                    $itemPrice = ($item['sale_price'] && $item['sale_price'] < $item['price']) ? $item['sale_price'] : $item['price'];
                    $imgSrc = $item['image'] && $item['image'] !== 'no-image.png' 
                        ? SITE_URL . '/assets/images/uploads/' . $item['image'] 
                        : 'https://via.placeholder.com/100';
                ?>
                    <div class="cart-item">
                        <img src="<?= $imgSrc ?>" alt="">
                        <div class="cart-item-info">
                            <h4><a href="<?= SITE_URL ?>/product.php?id=<?= $item['product_id'] ?>"><?= htmlspecialchars($item['name']) ?></a></h4>
                            <p class="seller">Sold by: <?= htmlspecialchars($item['shop_name']) ?></p>
                        </div>
                        <div class="quantity-selector">
                            <button onclick="updateCartQty(<?= $item['id'] ?>, <?= $item['quantity'] - 1 ?>)">−</button>
                            <input type="number" value="<?= $item['quantity'] ?>" min="1" readonly style="width:50px">
                            <button onclick="updateCartQty(<?= $item['id'] ?>, <?= $item['quantity'] + 1 ?>)">+</button>
                        </div>
                        <strong style="color:var(--primary);font-size:16px"><?= formatPrice($itemPrice * $item['quantity']) ?></strong>
                        <button class="action-btn delete" onclick="removeFromCart(<?= $item['id'] ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal (<?= count($cartItems) ?> items)</span>
                    <strong><?= formatPrice($subtotal) ?></strong>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <strong><?= $shipping > 0 ? formatPrice($shipping) : '<span style="color:var(--success)">FREE</span>' ?></strong>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span><?= formatPrice($total) ?></span>
                </div>
                <a href="<?= SITE_URL ?>/buyer/checkout.php" class="btn btn-primary btn-block btn-lg" style="margin-top:20px">
                    <i class="fas fa-lock"></i> Proceed to Checkout
                </a>
                <a href="<?= SITE_URL ?>/shop.php" class="btn btn-block" style="margin-top:10px;background:var(--gray-100);color:var(--gray-700)">
                    Continue Shopping
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-shopping-cart"></i>
            <h3>Your Cart is Empty</h3>
            <p>Looks like you haven't added anything to your cart yet.</p>
            <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary btn-lg">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>