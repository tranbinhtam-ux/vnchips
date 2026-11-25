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

            return '<b class="tuong-tac" tabindex="0">' . e($label)
                 . '<span class="chu-thich">' . $note . '</span></b>';
        }, $html);
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
