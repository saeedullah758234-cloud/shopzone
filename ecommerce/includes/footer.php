<?php
// includes/footer.php
?>
<!-- Features -->
<div class="features">
    <div class="container">
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <div class="feature-text">
                    <h4>Free Shipping</h4>
                    <p>On orders over $50</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="feature-text">
                    <h4>Secure Payment</h4>
                    <p>100% secure transactions</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-undo"></i>
                </div>
                <div class="feature-text">
                    <h4>Easy Returns</h4>
                    <p>30-day return policy</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <div class="feature-text">
                    <h4>24/7 Support</h4>
                    <p>Ready to help you</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="<?= SITE_URL ?>" class="logo">
                    <i class="fas fa-shopping-bag"></i>
                    Shop<span>Zone</span>
                </a>
                <p>Your one-stop destination for everything you need. Quality products, great prices, and exceptional service.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>">Home</a></li>
                    <li><a href="<?= SITE_URL ?>/shop.php">Shop</a></li>
                    <li><a href="<?= SITE_URL ?>/shop.php?featured=1">Featured</a></li>
                    <li><a href="<?= SITE_URL ?>/shop.php?sort=newest">New Arrivals</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Customer Service</h4>
                <ul>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">FAQs</a></li>
                    <li><a href="#">Shipping Info</a></li>
                    <li><a href="#">Returns</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>My Account</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/auth/login.php">Login</a></li>
                    <li><a href="<?= SITE_URL ?>/buyer/orders.php">Order History</a></li>
                    <li><a href="<?= SITE_URL ?>/buyer/cart.php">My Cart</a></li>
                    <li><a href="<?= SITE_URL ?>/auth/register.php?role=seller">Become a Seller</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved. Built with ❤️</p>
        </div>
    </div>
</footer>

<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>