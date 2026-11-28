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
 * Generate a fully qualified URL (scheme + host + path).
 *
 * BASE_URL is a root-relative path ("/"), which is fine everywhere inside the
 * page itself. It is NOT enough for resources loaded into a separate document
 * such as CKEditor's editing iframe: that iframe gets its own <base href>, so a
 * "/chip-admin/..." path cannot be resolved there and the file silently fails
 * to load. Use this helper for anything handed to an editor or an external
 * consumer (contentsCss, baseHref, feeds, e-mail, canonical tags).
 */
function absolute_url($path = '') {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return ($isHttps ? 'https' : 'http') . '://' . $host . url($path);
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
    $text = preg_replace('~[^\pL\d]+~u', '-', (string)$text);
    // iconv //TRANSLIT depends on the process locale: under the web server (locale "C") it
    // returns "?" or false for anything non-ASCII, which used to collapse every slug to "n-a".
    // Strip Vietnamese diacritics ourselves first, then let iconv handle whatever is left.
    $text = strtr($text, slugify_accent_map());
    $ascii = @iconv('utf-8', 'us-ascii//TRANSLIT//IGNORE', $text);
    if ($ascii === false || trim($ascii, '-? ') === '') {
        $ascii = preg_replace('~[^ -~]~', '', $text);
    }
    $ascii = preg_replace('~[^-\w]+~', '', $ascii);
    $ascii = trim($ascii, '-');
    $ascii = preg_replace('~-+~', '-', $ascii);
    $ascii = strtolower($ascii);
    return $ascii === '' ? 'n-a' : $ascii;
}

/** Vietnamese (and common Latin) accented letters -> ASCII, for slugify() */
function slugify_accent_map() {
    static $map = null;
    if ($map !== null) return $map;
    $groups = [
        'a' => 'àáạảãâầấậẩẫăằắặẳẵ', 'A' => 'ÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴ',
        'e' => 'èéẹẻẽêềếệểễ',       'E' => 'ÈÉẸẺẼÊỀẾỆỂỄ',
        'i' => 'ìíịỉĩ',             'I' => 'ÌÍỊỈĨ',
        'o' => 'òóọỏõôồốộổỗơờớợởỡ', 'O' => 'ÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠ',
        'u' => 'ùúụủũưừứựửữ',       'U' => 'ÙÚỤỦŨƯỪỨỰỬỮ',
        'y' => 'ỳýỵỷỹ',             'Y' => 'ỲÝỴỶỸ',
        'd' => 'đ',                 'D' => 'Đ',
    ];
    $map = [];
    foreach ($groups as $ascii => $chars) {
        foreach (preg_split('//u', $chars, -1, PREG_SPLIT_NO_EMPTY) as $ch) {
            $map[$ch] = $ascii;
        }
    }
    return $map;
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
    $text = trim((string)$text);
    if ($text === '') return '';   // let the caller fall back to a slug built from the title
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

/* ==========================================================================
   LIVE TEXT / LIVE IMAGE
   Mark a piece of text or an image that used to be hard-coded in a template so a
   signed-in admin can edit it in place (Live Edit pencil). The value lives in the
   settings table under $key; the template's literal stays as the default.
   ========================================================================== */

/** Current value of a live-text slot (escaped unless $html) */
function lt($key, $default, $html = false) {
    $value = Setting::getOr($key, $default);
    return $html ? $value : e($value);
}

/**
 * Attributes to put on the element that holds the text, e.g.
 *   <h2<?= lt_attr('home_business_title') ?>><?= lt('home_business_title', 'BUSINESS') ?></h2>
 * $html = true lets the editor save HTML (paragraph blocks); $label is the field name shown
 * in the editor (defaults to a readable form of the key).
 */
function lt_attr($key, $html = false, $label = '') {
    return ' data-lt="' . e($key) . '"' . ($html ? ' data-lt-html="1"' : '') . ($label !== '' ? ' data-lt-label="' . e($label) . '"' : '');
}

/** Image URL for a live-image slot */
function lt_img($key, $default) {
    return asset(Setting::getOr($key, $default));
}

/** Attributes for the <img> (or background element) of a live-image slot */
function lt_img_attr($key, $label = '') {
    return ' data-lt-img="' . e($key) . '"' . ($label !== '' ? ' data-lt-label="' . e($label) . '"' : '');
}
