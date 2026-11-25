<?php
/**
 * VNCHIP Technology JSC - System Configuration
 */

// Prevent direct access to config
if (!defined('VNCHIP_APP')) {
    define('VNCHIP_APP', true);
}

// Buffer output so stray warnings/whitespace never block header() redirects or session cookies
if (php_sapi_name() !== 'cli' && ob_get_level() === 0) {
    ob_start();
}

// Server-specific overrides (DB credentials, etc.) live in config.local.php, which is not overwritten on deploy
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

// Database Configuration
defined('DB_HOST')    || define('DB_HOST', 'localhost');
defined('DB_PORT')    || define('DB_PORT', '3306');
defined('DB_NAME')    || define('DB_NAME', 'vnchip_db');
defined('DB_USER')    || define('DB_USER', 'root');
defined('DB_PASS')    || define('DB_PASS', '');
defined('DB_CHARSET') || define('DB_CHARSET', 'utf8mb4');

// Admin panel folder name (URL path: /chip-admin/)
defined('ADMIN_DIR')  || define('ADMIN_DIR', 'chip-admin');

// Base URL calculation (flexible for localhost, subfolders & production)
if (php_sapi_name() === 'cli') {
    $baseUrl = '/vnchips/';
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    
    // Determine application base path dynamically
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    
    // Strip the admin subdirectory (/chip-admin/...) and script filename to find app root
    $cleanPath = preg_replace('#/' . preg_quote(ADMIN_DIR, '#') . '(/.*)?$#i', '', $scriptName);
    $cleanPath = preg_replace('#/[^/]+\.php$#i', '', $cleanPath);
    $cleanPath = trim($cleanPath, '/');
    
    $baseUrl = !empty($cleanPath) ? '/' . $cleanPath . '/' : '/';
}

define('BASE_URL', $baseUrl);
define('SITE_NAME', 'VNCHIP Technology JSC');
define('SITE_SLOGAN', 'Pioneering Semiconductor & IC Design Solutions');

// Load helper functions
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../models/Setting.php';

// Contact details are editable in Admin > Homepage & Settings (falls back to these defaults)
define('SITE_EMAIL', Setting::getOr('site_email', 'institute@vnchip-inc.com'));
define('SITE_PHONE', Setting::getOr('site_phone', '+84 - 28 268 268 1058'));
define('SITE_ADDRESS_VN', Setting::getOr('site_address_vn', '190 Doc Lap Street, Phu Tho Hoa Ward, Ho Chi Minh City, Vietnam'));
define('SITE_ADDRESS_US', Setting::getOr('site_address_us', '6500 River Place Blvd, Building 7, Suites 250 Austin, TX. 78730'));
