<?php
/**
 * Global Helper Functions
 */

if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}

/**
 * Generate asset URL
 */
function asset($path) {
    $cleanPath = ltrim($path, '/');
    if (strpos($cleanPath, '../' . ADMIN_DIR . '/') === 0) {
        return BASE_URL . substr($cleanPath, 3);
    }
    if (strpos($cleanPath, ADMIN_DIR . '/assets/') === 0) {
        return BASE_URL . $cleanPath;
    }
    if (strpos($cleanPath, 'uploads/') === 0) {
        return BASE_URL . $cleanPath;
    }
    if (strpos($cleanPath, 'assets/') === 0) {
        $cleanPath = substr($cleanPath, 7);
    }
    return BASE_URL . 'assets/' . $cleanPath;
}

/**
 * Generate internal page URL
 */
function url($path = '') {
    return BASE_URL . ltrim($path, '/');
}

/**
 * Generate admin panel URL (e.g. admin_url('articles.php') => /chip-admin/articles.php)
 */
function admin_url($path = '') {
    return url(ADMIN_DIR . '/' . ltrim($path, '/'));
}

/**
 * Safe HTML escaping
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format date for display
 */
function formatDate($dateString, $format = 'M d, Y') {
    if (empty($dateString)) return '';
    $timestamp = strtotime($dateString);
    return date($format, $timestamp);
}

/**
 * Generate URL-friendly slug
 */
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

/**
 * Truncate text cleanly with ellipsis
 */
function truncateText($text, $limit = 120) {
    $text = strip_tags($text);
    if (mb_strlen($text) <= $limit) return $text;
    return mb_substr($text, 0, $limit) . '...';
}

/**
 * Helper to check active navigation item
 */
function isActiveNav($currentPage, $targetPage) {
    return ($currentPage === $targetPage) ? 'active' : '';
}

/**
 * Keep an already URL-safe slug as-is (old slugs contain "_"), otherwise slugify it
 */
function normalizeSlug($text) {
    $text = trim($text);
    return preg_match('~^[a-z0-9_-]+$~', $text) ? $text : slugify($text);
}

/**
 * Log a page view for Admin > Traffic Analytics (ported from vnchips_old track_visitor)
 * Skips bots and repeat hits of the same page by the same IP within 5 minutes.
 */
function trackVisit() {
    if (php_sapi_name() === 'cli' || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') return;

    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if ($agent === '' || preg_match('~bot|crawl|spider|slurp|facebookexternalhit|preview|monitor|curl|wget|python~i', $agent)) return;

    $db = Database::getInstance()->getConnection();
    if (!$db) return;

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $page = mb_substr($_SERVER['REQUEST_URI'] ?? '/', 0, 255);
    $referrer = mb_substr($_SERVER['HTTP_REFERER'] ?? '', 0, 255);

    $browser = 'Other';
    foreach (['Edg' => 'Edge', 'OPR' => 'Opera', 'Chrome' => 'Chrome', 'Firefox' => 'Firefox', 'Safari' => 'Safari', 'MSIE' => 'Internet Explorer', 'Trident' => 'Internet Explorer'] as $needle => $name) {
        if (stripos($agent, $needle) !== false) { $browser = $name; break; }
    }
    $device = preg_match('~iPad|Tablet~i', $agent) ? 'Tablet' : (preg_match('~Mobile|Android|iPhone~i', $agent) ? 'Mobile' : 'Desktop');

    try {
        $check = $db->prepare("SELECT 1 FROM `visitor_logs` WHERE `ip_address` = ? AND `page_url` = ? AND `created_at` > (NOW() - INTERVAL 5 MINUTE) LIMIT 1");
        $check->execute([$ip, $page]);
        if (!$check->fetchColumn()) {
            $db->prepare("INSERT INTO `visitor_logs` (`ip_address`, `page_url`, `referrer`, `browser`, `device`) VALUES (?, ?, ?, ?, ?)")
               ->execute([$ip, $page, $referrer, $browser, $device]);
        }
    } catch (PDOException $e) {
        error_log("trackVisit error: " . $e->getMessage());
    }
}
