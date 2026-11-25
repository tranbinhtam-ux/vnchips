<?php
/**
 * Migration: Design Service / IP Service accordion + interactive keyword tooltips
 *
 * Creates `service_items` and `service_tooltips`, then imports the content that used to be
 * hard-coded in index.php and business.php. The import reads the snapshot in
 * database/seed_design_ip_services.html, NOT the live pages, so re-running this after the
 * pages were switched over to the shared renderer still works.
 *
 * Safe to run repeatedly: tables are created IF NOT EXISTS and the import only runs while
 * `service_items` is still empty.
 *
 * Usage (CLI on the server):  php database/migrate_services_tooltips.php
 */

// Schema changes are not something a visitor should be able to trigger: the database/
// folder is served over HTTP on this host, so refuse anything that is not a shell run.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This migration can only be run from the command line.\n");
}

require_once __DIR__ . '/../config/config.php';

$db = Database::getInstance()->getConnection();
if (!$db) {
    exit("Cannot connect to the database.\n");
}

$log = static function ($msg) { echo $msg . "\n"; };

/* ------------------------------------------------------------------ 1. Schema */

$db->exec("CREATE TABLE IF NOT EXISTS `service_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `column_group` ENUM('design','ip') NOT NULL DEFAULT 'design',
    `anchor_id` VARCHAR(80) NOT NULL DEFAULT '',
    `title` VARCHAR(190) NOT NULL,
    `image_url` VARCHAR(255) NOT NULL DEFAULT '',
    `body` LONGTEXT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_group_sort` (`column_group`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$log('OK  table service_items');

$db->exec("CREATE TABLE IF NOT EXISTS `service_tooltips` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `keyword` VARCHAR(190) NOT NULL,
    `title` VARCHAR(190) NOT NULL DEFAULT '',
    `content` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_keyword` (`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$log('OK  table service_tooltips');

/* --------------------------------------------------- 2. Import (first run only) */

$existing = (int)$db->query("SELECT COUNT(*) FROM `service_items`")->fetchColumn();
if ($existing > 0) {
    $log("SKIP import: service_items already holds {$existing} row(s).");
    exit(0);
}

$seedFile = __DIR__ . '/seed_design_ip_services.html';
if (!is_file($seedFile)) {
    exit("Missing snapshot: {$seedFile}\n");
}

/** innerHTML of a DOM node */
$innerHtml = static function (DOMNode $node) {
    $html = '';
    foreach ($node->childNodes as $child) {
        $html .= $node->ownerDocument->saveHTML($child);
    }
    return $html;
};

/** XPath class test that does not match partial class names */
$hasClass = static function ($class) {
    return "contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')";
};

$doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->loadHTML(
    '<?xml encoding="utf-8" ?><div id="seed-root">' . file_get_contents($seedFile) . '</div>',
    LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
);
libxml_clear_errors();
$xp = new DOMXPath($doc);

$tooltips = [];   // lowercased keyword => ['keyword', 'title', 'content']
$items    = [];

foreach ($xp->query('//div[' . $hasClass('service-col') . ']') as $col) {
    $accordion = $xp->query('.//div[' . $hasClass('service-accordion') . ']', $col)->item(0);
    $accId = $accordion ? $accordion->getAttribute('id') : '';
    $group = (stripos($accId, 'ip') !== false) ? 'ip' : 'design';

    $sort = 0;
    foreach ($xp->query('.//div[' . $hasClass('service-item') . ']', $col) as $item) {
        $nameNode = $xp->query('.//span[' . $hasClass('service-name') . ']', $item)->item(0);
        $bodyNode = $xp->query('.//div[' . $hasClass('service-body') . ']', $item)->item(0);
        if (!$nameNode || !$bodyNode) {
            continue;
        }

        // Pull the hero image out of the body; it lives in its own column.
        $image = '';
        $imgNode = $xp->query('.//img[' . $hasClass('service-body-img') . ']', $bodyNode)->item(0);
        if ($imgNode) {
            $image = trim($imgNode->getAttribute('src'));
            $imgNode->parentNode->removeChild($imgNode);
        }

        // Harvest every interactive keyword and leave a [[marker]] behind.
        $keywordNodes = [];
        foreach ($xp->query('.//b[' . $hasClass('tuong-tac') . ']', $bodyNode) as $kw) {
            $keywordNodes[] = $kw;
        }

        foreach ($keywordNodes as $kw) {
            $noteNode = $xp->query('.//span[' . $hasClass('chu-thich') . ']', $kw)->item(0);

            $label = $kw->textContent;
            if ($noteNode) {
                $label = str_replace($noteNode->textContent, '', $label);
            }
            $label = trim(preg_replace('/\s+/u', ' ', $label));
            if ($label === '') {
                continue;
            }

            $title = '';
            $content = '';
            if ($noteNode) {
                $noteHtml = $innerHtml($noteNode);
                // A note opens with "<b>Heading</b><br><br>" and then the explanation.
                if (preg_match('#^\s*<b>(.*?)</b>\s*(?:<br\s*/?>\s*)*#is', $noteHtml, $m)) {
                    $title = trim(strip_tags($m[1]));
                    $content = substr($noteHtml, strlen($m[0]));
                } else {
                    $content = $noteHtml;
                }
            }

            $key = mb_strtolower($label, 'UTF-8');
            if (!isset($tooltips[$key])) {
                $tooltips[$key] = [
                    'keyword' => $label,
                    'title'   => $title !== '' ? $title : $label,
                    'content' => trim($content),
                ];
            }

            $kw->parentNode->replaceChild($doc->createTextNode('[[' . $label . ']]'), $kw);
        }

        $body = trim($innerHtml($bodyNode));
        $body = preg_replace("/\n\s*\n+/", "\n", $body);

        $items[] = [
            'column_group' => $group,
            'anchor_id'    => $item->getAttribute('id'),
            'title'        => trim(preg_replace('/\s+/u', ' ', $nameNode->textContent)),
            'image_url'    => $image,
            'body'         => $body,
            'sort_order'   => $sort++,
        ];
    }
}

if (!$items) {
    exit("Parsed 0 service items from the snapshot - nothing imported.\n");
}

$db->beginTransaction();

$insTip = $db->prepare("INSERT INTO `service_tooltips` (`keyword`, `title`, `content`, `is_active`)
                        VALUES (:kw, :title, :content, 1)
                        ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `content` = VALUES(`content`)");
foreach ($tooltips as $t) {
    $insTip->execute([':kw' => $t['keyword'], ':title' => $t['title'], ':content' => $t['content']]);
}

$insItem = $db->prepare("INSERT INTO `service_items` (`column_group`, `anchor_id`, `title`, `image_url`, `body`, `sort_order`, `is_active`)
                         VALUES (:grp, :anchor, :title, :img, :body, :sort, 1)");
foreach ($items as $it) {
    $insItem->execute([
        ':grp'    => $it['column_group'],
        ':anchor' => $it['anchor_id'],
        ':title'  => $it['title'],
        ':img'    => $it['image_url'],
        ':body'   => $it['body'],
        ':sort'   => $it['sort_order'],
    ]);
}

$db->commit();

$log('OK  imported ' . count($items) . ' service item(s) and ' . count($tooltips) . ' tooltip keyword(s).');
foreach ($items as $it) {
    $log(sprintf('    [%-6s] %-26s img=%s', $it['column_group'], $it['title'], $it['image_url'] ?: '-'));
}
foreach ($tooltips as $t) {
    $log(sprintf('    tooltip: %-34s %s', $t['keyword'], mb_substr(strip_tags($t['content']), 0, 46) . '...'));
}
