<?php
// buyer/checkout.php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock, p.seller_id
    FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    header('Location: ' . SITE_URL . '/buyer/cart.php');
    exit;
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $price = ($item['sale_price'] && $item['sale_price'] < $item['price']) ? $item['sale_price'] : $item['price'];
    $subtotal += $price * $item['quantity'];
}
$shipping = $subtotal >= 50 ? 0 : 5.99;
$total = $subtotal + $shipping;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['shipping_name'] ?? '');
    $phone = trim($_POST['shipping_phone'] ?? '');
    $address = trim($_POST['shipping_address'] ?? '');
    $city = trim($_POST['shipping_city'] ?? '');
    $payment = $_POST['payment_method'] ?? 'cod';
    
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($phone)) $errors[] = 'Phone is required';
    if (empty($address)) $errors[] = 'Address is required';
    if (empty($city)) $errors[] = 'City is required';
    
    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            // Create Order
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, shipping_name, shipping_phone, shipping_address, shipping_city, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $total, $name, $phone, $address, $city, $payment]);
            $orderId = $pdo->lastInsertId();
            
            // Order Items
            foreach ($cartItems as $item) {
                $price = ($item['sale_price'] && $item['sale_price'] < $item['price']) ? $item['sale_price'] : $item['price'];
                $pdo->prepare("INSERT INTO order_items (order_id, product_id, seller_id, quantity, price) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$orderId, $item['product_id'], $item['seller_id'], $item['quantity'], $price]);
                
                // Reduce stock
                $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Clear cart
            $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$_SESSION['user_id']]);
            
            $pdo->commit();
            flash('message', 'Order #' . $orderId . ' placed successfully!', 'success');
            header('Location: ' . SITE_URL . '/buyer/orders.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Order failed. Please try again.';
        }
    }
}

$user = currentUser($pdo);
$pageTitle = 'Checkout - ' . SITE_NAME;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding:40px 20px">
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-lock"></i> Secure Checkout</h2>
    </div>
    
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= implode('<br>', $errors) ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="checkout-grid">
            <div>
                <div class="card" style="margin-bottom:24px">
                    <div class="card-header"><h3>Shipping Information</h3></div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" name="shipping_name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Phone *</label>
                                <input type="tel" name="shipping_phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Address *</label>
                            <textarea name="shipping_address" class="form-control" rows="3" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>City *</label>
                            <input type="text" name="shipping_city" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header"><h3>Payment Method</h3></div>
                    <div class="card-body">
                        <div class="role-selector">
                            <div class="role-option">
                                <input type="radio" name="payment_method" value="cod" id="cod" checked>
                                <label for="cod"><i class="fas fa-money-bill-wave"></i><span>Cash on Delivery</span></label>
                            </div>
                            <div class="role-option">
                                <input type="radio" name="payment_method" value="card" id="card">
                                <label for="card"><i class="fas fa-credit-card"></i><span>Card Payment</span></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="cart-summary">
                <h3>Order Summary</h3>
                <?php foreach ($cartItems as $item): 
                    $price = ($item['sale_price'] && $item['sale_price'] < $item['price']) ? $item['sale_price'] : $item['price'];
                ?>
                    <div class="summary-row">
                        <span><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></span>
                        <strong><?= formatPrice($price * $item['quantity']) ?></strong>
                    </div>
                <?php endforeach; ?>
                <div class="summary-row" style="border-top:1px solid var(--gray-200);padding-top:12px">
                    <span>Shipping</span>
                    <strong><?= $shipping > 0 ? formatPrice($shipping) : 'FREE' ?></strong>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span><?= formatPrice($total) ?></span>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:20px">
                    <i class="fas fa-check"></i> Place Order
                </button>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>