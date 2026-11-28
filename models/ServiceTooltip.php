<?php
/**
 * Interactive Keyword Tooltips Model
 *
 * A small dictionary of keyword -> explanation. Editors write `[[keyword]]` inside a
 * service item body and this model expands it into the `.tuong-tac` / `.chu-thich`
 * markup that assets/js/main.js already knows how to position (and turn into a bottom
 * sheet on mobile).
 */

if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}

class ServiceTooltip {

    /** Marker syntax: [[keyword]] or [[text shown|keyword]] */
    const MARKER_PATTERN = '/\[\[\s*([^\[\]|]+?)\s*(?:\|\s*([^\[\]|]+?)\s*)?\]\]/u';

    private static $dictionary = null;

    public static function getAll() {
        return self::query("SELECT * FROM `service_tooltips` ORDER BY `keyword` ASC") ?? [];
    }

    public static function getActive() {
        return self::query("SELECT * FROM `service_tooltips` WHERE `is_active` = 1 ORDER BY `keyword` ASC") ?? [];
    }

    public static function getById($id) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("SELECT * FROM `service_tooltips` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => (int)$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("ServiceTooltip::getById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create (id = null) or update one entry. Returns the id, or false on failure.
     * A duplicate keyword is rejected so markers always resolve to exactly one entry.
     */
    public static function save($id, array $data) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;

        $keyword = trim(preg_replace('/\s+/u', ' ', (string)($data['keyword'] ?? '')));
        if ($keyword === '') return false;

        try {
            $check = $db->prepare("SELECT COUNT(*) FROM `service_tooltips` WHERE LOWER(`keyword`) = LOWER(:kw) AND `id` != :id");
            $check->execute([':kw' => $keyword, ':id' => (int)$id]);
            if ($check->fetchColumn() > 0) {
                return false;
            }

            $params = [
                ':kw'      => $keyword,
                ':title'   => trim((string)($data['title'] ?? '')) ?: $keyword,
                ':content' => (string)($data['content'] ?? ''),
                ':active'  => !empty($data['is_active']) ? 1 : 0,
            ];

            if ($id) {
                $params[':id'] = (int)$id;
                $stmt = $db->prepare("UPDATE `service_tooltips` SET `keyword` = :kw, `title` = :title,
                    `content` = :content, `is_active` = :active WHERE `id` = :id");
                $stmt->execute($params);
                self::$dictionary = null;
                return (int)$id;
            }

            $stmt = $db->prepare("INSERT INTO `service_tooltips` (`keyword`, `title`, `content`, `is_active`)
                VALUES (:kw, :title, :content, :active)");
            $stmt->execute($params);
            self::$dictionary = null;
            return (int)$db->lastInsertId();
        } catch (PDOException $e) {
            error_log("ServiceTooltip::save error: " . $e->getMessage());
            return false;
        }
    }

    public static function toggleActive($id) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;
        try {
            self::$dictionary = null;
            return $db->prepare("UPDATE `service_tooltips` SET `is_active` = 1 - `is_active` WHERE `id` = :id")
                      ->execute([':id' => (int)$id]);
        } catch (PDOException $e) {
            error_log("ServiceTooltip::toggleActive error: " . $e->getMessage());
            return false;
        }
    }

    public static function delete($id) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;
        try {
            self::$dictionary = null;
            return $db->prepare("DELETE FROM `service_tooltips` WHERE `id` = :id")->execute([':id' => (int)$id]);
        } catch (PDOException $e) {
            error_log("ServiceTooltip::delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * How many service item bodies currently reference a keyword (shown in the admin list
     * so an editor can tell whether deleting an entry would break any text).
     */
    public static function usageCounts() {
        $counts = [];
        $items = ServiceItem::getAll();
        foreach ($items as $item) {
            if (preg_match_all(self::MARKER_PATTERN, (string)$item['body'], $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $key = self::normalise(isset($m[2]) && $m[2] !== '' ? $m[2] : $m[1]);
                    $counts[$key] = ($counts[$key] ?? 0) + 1;
                }
            }
        }
        return $counts;
    }

    /**
     * Expand every [[marker]] in a body of HTML into tooltip markup.
     * An unknown or disabled keyword degrades to plain text - a visitor never sees brackets.
     */
    public static function expand($html) {
        $html = (string)$html;
        if ($html === '' || strpos($html, '[[') === false) {
            return $html;
        }

        $dict = self::dictionary();

        return preg_replace_callback(self::MARKER_PATTERN, function ($m) use ($dict) {
            $label = trim($m[1]);
            $key   = self::normalise(isset($m[2]) && $m[2] !== '' ? $m[2] : $m[1]);

            if (!isset($dict[$key])) {
                return $label;
            }

            $tip = $dict[$key];
            $note = '<b>' . e($tip['title'] !== '' ? $tip['title'] : $tip['keyword']) . '</b><br><br>' . $tip['content'];

            // data-tip-* lets Live Edit open the right dictionary entry from the page itself
            return '<b class="tuong-tac" tabindex="0" data-tip-id="' . (int)$tip['id'] . '" data-tip-key="' . e($tip['keyword']) . '">' . e($label)
                 . '<span class="chu-thich">' . $note . '</span></b>';
        }, $html);
    }

    /** The inner HTML of one hover panel, shared by expand() and the Live Edit refresh */
    public static function renderNote(array $tip) {
        $title = isset($tip['title']) && $tip['title'] !== '' ? $tip['title'] : ($tip['keyword'] ?? '');
        return '<b>' . e($title) . '</b><br><br>' . (string)($tip['content'] ?? '');
    }

    /** Dictionary row for a keyword (case-insensitive), or null */
    public static function findByKeyword($keyword) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return null;
        try {
            $stmt = $db->prepare("SELECT * FROM `service_tooltips` WHERE LOWER(`keyword`) = LOWER(:kw) LIMIT 1");
            $stmt->execute([':kw' => trim(preg_replace('/\s+/u', ' ', (string)$keyword))]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("ServiceTooltip::findByKeyword error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Keywords referenced by one body ([[label]] or [[label|keyword]]), each with the
     * matching dictionary row when there is one. Used by the Live Edit item editor.
     */
    public static function keywordsIn($html) {
        $out = [];
        if (!preg_match_all(self::MARKER_PATTERN, (string)$html, $matches, PREG_SET_ORDER)) {
            return $out;
        }
        $dict = self::dictionary();
        foreach ($matches as $m) {
            $keyword = trim(isset($m[2]) && $m[2] !== '' ? $m[2] : $m[1]);
            $key = self::normalise($keyword);
            if (isset($out[$key])) continue;
            $row = $dict[$key] ?? self::findByKeyword($keyword);
            $out[$key] = [
                'keyword' => $row ? $row['keyword'] : $keyword,
                'id'      => $row ? (int)$row['id'] : 0,
                'title'   => $row ? $row['title'] : '',
                'active'  => $row ? (int)$row['is_active'] : 0,
                'has'     => $row ? 1 : 0,
            ];
        }
        return array_values($out);
    }

    /** Keywords an editor can insert, for the picker on the item edit screen */
    public static function keywordList() {
        return array_map(static function ($row) {
            return $row['keyword'];
        }, self::getActive());
    }

    public static function normalise($keyword) {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string)$keyword)), 'UTF-8');
    }

    /** Active entries indexed by normalised keyword (loaded once per request) */
    private static function dictionary() {
        if (self::$dictionary !== null) {
            return self::$dictionary;
        }

        self::$dictionary = [];
        foreach (self::getActive() as $row) {
            self::$dictionary[self::normalise($row['keyword'])] = $row;
        }
        return self::$dictionary;
    }

    /** Run a SELECT; null when the DB/table is unavailable so callers can fall back */
    private static function query($sql) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return null;

        try {
            return $db->query($sql)->fetchAll();
        } catch (PDOException $e) {
            error_log("ServiceTooltip query error: " . $e->getMessage());
            return null;
        }
    }
}
