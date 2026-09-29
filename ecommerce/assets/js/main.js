// assets/js/main.js

// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navBar = document.querySelector('.nav-bar');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            navBar.classList.toggle('active');
        });
    }

    // Sidebar Toggle (Dashboard)
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }

    // Auto-close alerts
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Add to Cart via AJAX
function addToCart(productId, qty = 1) {
    fetch(SITE_URL + '/buyer/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=add&product_id=${productId}&quantity=${qty}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Product added to cart!', 'success');
            const badge = document.querySelector('.cart-badge');
            if (badge) badge.textContent = data.cart_count;
        } else {
            if (data.login_required) {
                window.location.href = SITE_URL + '/auth/login.php';
            } else {
                showToast(data.message || 'Error occurred', 'error');
            }
        }
    })
    .catch(() => showToast('Network error', 'error'));
}

// Remove from Cart
function removeFromCart(cartId) {
    if (!confirm('Remove this item from cart?')) return;
    
    fetch(SITE_URL + '/buyer/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&cart_id=${cartId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

// Update Cart Quantity
function updateCartQty(cartId, qty) {
    if (qty < 1) return;
    
    fetch(SITE_URL + '/buyer/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update&cart_id=${cartId}&quantity=${qty}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) location.reload();
    });
}

// Quantity Controls
function changeQty(input, delta) {
    let val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    if (val > 99) val = 99;
    input.value = val;
}

// Toast Notification
function showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-circle' };
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<i class="fas ${icons[type] || icons.success}"></i><span>${message}</span>`;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Confirm Delete
function confirmDelete(url, name = 'this item') {
    if (confirm(`Are you sure you want to delete ${name}?`)) {
        window.location.href = url;
    }
}

// Image Preview
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Modal
function openModal(id) {
    document.getElementById(id).classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
    document.body.style.overflow = '';
}

// Close modal on outside click
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});