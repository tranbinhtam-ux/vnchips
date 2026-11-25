<?php
require_once __DIR__ . '/config/config.php';
$pdo = Database::getInstance()->getConnection();

$tables = ['news_articles', 'categories', 'members', 'partners', 'settings', 'menus', 'inquiries', 'media', 'visitor_logs', 'admins'];
foreach ($tables as $t) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo str_pad($t, 20) . ": $count\n";
    } catch (Exception $e) {
        echo str_pad($t, 20) . ": ERROR (" . $e->getMessage() . ")\n";
    }
}
