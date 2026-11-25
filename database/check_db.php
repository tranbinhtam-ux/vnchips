<?php
/**
 * TEMPORARY database connection check — DELETE THIS FILE after use.
 * Open: https://your-domain/database/check_db.php
 * Prints the real PDO error (never the password) and which required tables exist.
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: text/plain; charset=utf-8');

echo "config.local.php found: " . (file_exists(__DIR__ . '/../config/config.local.php') ? 'YES' : 'NO  <-- create it from config.local.example.php') . "\n";
echo "DB_HOST: " . DB_HOST . "\nDB_PORT: " . DB_PORT . "\nDB_NAME: " . DB_NAME . "\nDB_USER: " . DB_USER . "\n";
echo "DB_PASS: " . (DB_PASS === '' ? '(empty)' : '(set, ' . strlen(DB_PASS) . ' chars)') . "\n\n";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ]);
    echo "[OK] Connected to database.\n\n";
} catch (PDOException $e) {
    echo "[FAIL] Connection error: " . $e->getMessage() . "\n";
    exit;
}

$required = ['admins', 'settings', 'categories', 'news_articles', 'inquiries', 'menus', 'partners', 'members', 'media', 'visitor_logs'];
$existing = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

foreach ($required as $table) {
    if (in_array($table, $existing, true)) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo "[OK]      $table ($count rows)\n";
    } else {
        echo "[MISSING] $table\n";
    }
}

echo "\nREMEMBER: delete database/check_db.php now.\n";
