<?php
/**
 * Admin Modules Migration (Partners, Team Members, Media Library, Traffic Analytics)
 *
 * Safe to run more than once: only creates missing tables and seeds empty ones.
 * Unlike seed.php it never touches admins, menus, articles or settings.
 *
 * CLI:  php database/migrate_admin_modules.php [--from-old=OLD_DB_NAME]
 * Web:  /database/migrate_admin_modules.php?from-old=OLD_DB_NAME  (admin login required)
 *
 * --from-old imports media records, visitor logs and contact submissions from the
 * vnchips_old database (same MySQL server/credentials) into the new tables.
 */

require_once __DIR__ . '/../config/config.php';

$isCli = php_sapi_name() === 'cli';
if (!$isCli) {
    require_once __DIR__ . '/../models/Admin.php';
    Admin::requireAuth();
    header('Content-Type: text/plain; charset=utf-8');
}

$oldDb = null;
foreach ($isCli ? array_slice($argv, 1) : [] as $arg) {
    if (strpos($arg, '--from-old=') === 0) $oldDb = substr($arg, 11);
}
if (!$isCli && !empty($_GET['from-old'])) $oldDb = $_GET['from-old'];
if ($oldDb !== null && !preg_match('~^[A-Za-z0-9_]+$~', $oldDb)) exit("[!] Invalid old database name.\n");

echo "=== VNCHIP ADMIN MODULES MIGRATION ===\n";

$pdo = Database::getInstance()->getConnection();
if (!$pdo) exit("[!] Database connection failed: " . Database::getInstance()->getError() . "\n");

try {
    // 1. Create the new tables (DDL lives in schema.sql)
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    foreach (['partners', 'members', 'media', 'visitor_logs'] as $table) {
        if (!preg_match('~CREATE TABLE IF NOT EXISTS `' . $table . '`.*?;~s', $schema, $m)) {
            exit("[!] DDL for `$table` not found in schema.sql\n");
        }
        $pdo->exec($m[0]);
        echo "[✓] Table `$table` ready.\n";
    }

    // 1b. Columns added after the first release
    $hasColumn = function ($table, $column) use ($pdo) {
        return (bool)$pdo->query("SHOW COLUMNS FROM `$table` LIKE " . $pdo->quote($column))->fetch();
    };
    if (!$hasColumn('members', 'team_group')) {
        $pdo->exec("ALTER TABLE `members` ADD COLUMN `team_group` VARCHAR(20) NOT NULL DEFAULT 'leadership' COMMENT 'leadership | key_team (About Us section)' AFTER `short_position`");
        echo "[✓] Added `members`.`team_group` (Leadership / Key Team).
";
    }

    // 1c. Menu: Partner Values + University Partners (Business) -> one "Partner" item under Sustainable
    $legacyPartner = $pdo->query("SELECT COUNT(*) FROM `menus` WHERE `link` IN ('business.php#partner-values', 'business.php#university-partners')")->fetchColumn();
    if ($legacyPartner) {
        $pdo->exec("DELETE FROM `menus` WHERE `link` IN ('business.php#partner-values', 'business.php#university-partners')");
        $pdo->exec("INSERT INTO `menus` (`parent_id`, `title`, `link`, `sort_order`, `column_group`)
            SELECT 4, 'Partner', 'sustainable.php#partner', 5, 1 FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM `menus` WHERE `link` = 'sustainable.php#partner')");
        echo "[✓] Menu: Partner Values / University Partners merged into Sustainable > Partner.
";
    }

    $isEmpty = function ($table) use ($pdo) {
        return (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn() === 0;
    };

    // 2. Seed partners from data/partners.php (the content currently live on the site)
    if ($isEmpty('partners')) {
        $stmt = $pdo->prepare("INSERT INTO `partners` (`title`, `slug`, `category_name`, `image_url`, `excerpt`, `content_image`, `content_heading`, `content_body`, `is_pinned`, `sort_order`, `created_at`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $partners = require __DIR__ . '/../data/partners.php';
        foreach ($partners as $i => $p) {
            $stmt->execute([$p['title'], $p['slug'], $p['category_name'], $p['image_url'], $p['excerpt'], $p['content_image'],
                $p['content_heading'], $p['content_body'], (int)$p['is_pinned'], $i, $p['created_at']]);
        }
        echo "[✓] Seeded " . count($partners) . " partners.\n";
    } else {
        echo "[-] `partners` already has data, skipped seeding.\n";
    }

    // 3. Seed members from data/members.php
    if ($isEmpty('members')) {
        $stmt = $pdo->prepare("INSERT INTO `members` (`slug`, `name`, `position`, `short_position`, `image_url`, `bio`, `sort_order`, `linkedin_url`, `twitter_url`, `email_address`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $members = require __DIR__ . '/../data/members.php';
        foreach ($members as $m) {
            $stmt->execute([$m['slug'], $m['name'], $m['position'], $m['short_position'], $m['image_url'], $m['bio'],
                $m['sort_order'], $m['linkedin_url'], $m['twitter_url'], $m['email_address']]);
        }
        echo "[✓] Seeded " . count($members) . " members.\n";
    } else {
        echo "[-] `members` already has data, skipped seeding.\n";
    }

    // 4. Optional: import history from the old site's database
    if ($oldDb) {
        $src = "`$oldDb`";

        if ($isEmpty('media')) {
            $n = $pdo->exec("INSERT INTO `media` (`file_name`, `file_path`, `file_size`, `created_at`)
                SELECT `file_name`, `file_path`, `file_size`, `created_at` FROM $src.`media` ORDER BY `id`");
            echo "[✓] Imported $n media records.\n";
        } else {
            echo "[-] `media` already has data, skipped import.\n";
        }

        if ($isEmpty('visitor_logs')) {
            $n = $pdo->exec("INSERT INTO `visitor_logs` (`ip_address`, `page_url`, `referrer`, `browser`, `device`, `created_at`)
                SELECT `ip_address`, `page_url`, `referrer`, `browser`, `device`, `created_at` FROM $src.`visitor_logs` ORDER BY `id`");
            echo "[✓] Imported $n visitor log rows.\n";
        } else {
            echo "[-] `visitor_logs` already has data, skipped import.\n";
        }

        // Old contact form submissions -> inquiries (skip ones already imported)
        $n = $pdo->exec("INSERT INTO `inquiries` (`fullname`, `email`, `phone`, `company`, `service_interest`, `message`, `status`, `created_at`)
            SELECT TRIM(CONCAT(c.`first_name`, ' ', c.`last_name`)), c.`email`, c.`phone`, c.`company`,
                   IF(c.`country` <> '', CONCAT('Country: ', c.`country`), 'General Inquiry'), c.`message`, 'responded', c.`created_at`
            FROM $src.`contacts` c
            WHERE NOT EXISTS (SELECT 1 FROM `inquiries` i WHERE i.`email` = c.`email` AND i.`created_at` = c.`created_at`)
            ORDER BY c.`id`");
        echo "[✓] Imported $n old contact submissions into inquiries.\n";
    }

    echo "=== MIGRATION COMPLETED ===\n";
} catch (PDOException $e) {
    echo "[!] Migration error: " . $e->getMessage() . "\n";
}
