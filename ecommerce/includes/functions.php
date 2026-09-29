<?php
// includes/functions.php

require_once __DIR__ . '/../config/database.php';
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/../assets/images/uploads/');
}

// ==================== AUTH FUNCTIONS ====================

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function getUserRole(): ?string {
    return $_SESSION['user_role'] ?? null;
}

function getUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . '/auth/login.php');
        exit;
    }
}

function requireRole(string $role): void {
    requireLogin();
    if (getUserRole() !== $role) {
        header('Location: ' . SITE_URL);
        exit;
    }
}

function currentUser(PDO $pdo): ?array {
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([getUserId()]);
    return $stmt->fetch() ?: null;
}

// ==================== HELPER FUNCTIONS ====================

function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string {
    $text = preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower(trim($text)));
    return trim($text, '-') . '-' . substr(uniqid(), -6);
}

function formatPrice(float $price): string {
    return '$' . number_format($price, 2);
}

function getProductPrice(array $p): float {
    return (!empty($p['sale_price']) && $p['sale_price'] > 0 && $p['sale_price'] < $p['price'])
        ? (float)$p['sale_price'] : (float)$p['price'];
}

function getDiscount(array $p): int {
    if (!empty($p['sale_price']) && $p['sale_price'] > 0 && $p['sale_price'] < $p['price']) {
        return round((($p['price'] - $p['sale_price']) / $p['price']) * 100);
    }
    return 0;
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', strtotime($datetime));
}

function flash(string $key = 'msg', ?string $message = null, string $type = 'success'): ?array {
    if ($message !== null) {
        $_SESSION['flash'][$key] = ['message' => $message, 'type' => $type];
        return null;
    }
    if (isset($_SESSION['flash'][$key])) {
        $flash = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $flash;
    }
    return null;
}

function renderFlash(string $key = 'msg'): string {
    $f = flash($key);
    if (!$f) return '';
    $icon = $f['type'] === 'success' ? 'check-circle' : ($f['type'] === 'danger' ? 'times-circle' : 'info-circle');
    return '<div class="alert alert-' . $f['type'] . '"><i class="fas fa-' . $icon . '"></i> ' . e($f['message']) . '</div>';
}

function uploadImage(array $file, string $folder = ''): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] === 0) return false;
    $allowed = ['jpg','jpeg','png','gif','webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false;

    $dir = UPLOAD_DIR . ($folder ? $folder . '/' : '');
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $filename = uniqid('img_') . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
        return $filename;
    }
    return false;
}

function productImage(?string $img): string {
    if ($img && $img !== 'no-image.png' && file_exists(UPLOAD_DIR . $img)) {
        return SITE_URL . '/assets/images/uploads/' . $img;
    }
    return 'https://via.placeholder.com/400x400/f0f0f0/999?text=No+Image';
}

function getCategories(PDO $pdo): array {
    return $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
}

function getCartCount(PDO $pdo): int {
    if (!isLoggedIn()) return 0;
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = ?");
    $stmt->execute([getUserId()]);
    return (int)$stmt->fetchColumn();
}

function getStarRating(float $rating): string {
    $html = '';
    $rounded = round($rating);
    for ($i = 1; $i <= 5; $i++) {
        $html .= ($i <= $rounded) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
    }
    return $html;
}