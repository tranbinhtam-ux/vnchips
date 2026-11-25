<?php
/**
 * Partner Case Studies Model
 * Falls back to data/partners.php when the database (or the `partners` table) is unavailable
 */

if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}

class Partner {

    /**
     * Published partners for the public site, pinned first then by sort order
     */
    public static function getPublished() {
        $rows = self::query("SELECT * FROM `partners` WHERE `status` = 'published' ORDER BY `is_pinned` DESC, `sort_order` ASC, `created_at` DESC");
        return $rows !== null ? $rows : require __DIR__ . '/../data/partners.php';
    }

    /**
     * All partners for the admin list (includes drafts)
     */
    public static function getAll() {
        return self::query("SELECT * FROM `partners` ORDER BY `is_pinned` DESC, `sort_order` ASC, `created_at` DESC") ?? [];
    }

    public static function getBySlug($slug) {
        foreach (self::getPublished() as $p) {
            if ($p['slug'] === $slug) return $p;
        }
        return null;
    }

    public static function getById($id) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("SELECT * FROM `partners` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => (int)$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Partner::getById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create (id = null) or update a partner. Returns the partner id or false.
     */
    public static function save($id, array $data) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;

        try {
            $slug = self::uniqueSlug($db, !empty($data['slug']) ? $data['slug'] : slugify($data['title']), $id);
            $params = [
                ':title' => $data['title'],
                ':slug' => $slug,
                ':cat' => $data['category_name'] ?: 'Partners',
                ':img' => $data['image_url'],
                ':excerpt' => $data['excerpt'],
                ':cimg' => $data['content_image'],
                ':cheading' => $data['content_heading'],
                ':cbody' => $data['content_body'],
                ':pinned' => !empty($data['is_pinned']) ? 1 : 0,
                ':sort' => (int)$data['sort_order'],
                ':status' => $data['status'] === 'draft' ? 'draft' : 'published',
            ];

            if ($id) {
                $params[':id'] = (int)$id;
                $stmt = $db->prepare("UPDATE `partners` SET `title` = :title, `slug` = :slug, `category_name` = :cat, `image_url` = :img,
                    `excerpt` = :excerpt, `content_image` = :cimg, `content_heading` = :cheading, `content_body` = :cbody,
                    `is_pinned` = :pinned, `sort_order` = :sort, `status` = :status WHERE `id` = :id");
                $stmt->execute($params);
                return (int)$id;
            }

            $stmt = $db->prepare("INSERT INTO `partners` (`title`, `slug`, `category_name`, `image_url`, `excerpt`, `content_image`, `content_heading`, `content_body`, `is_pinned`, `sort_order`, `status`)
                VALUES (:title, :slug, :cat, :img, :excerpt, :cimg, :cheading, :cbody, :pinned, :sort, :status)");
            $stmt->execute($params);
            return (int)$db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Partner::save error: " . $e->getMessage());
            return false;
        }
    }

    public static function delete($id) {
        return self::exec("DELETE FROM `partners` WHERE `id` = :id", [':id' => (int)$id]);
    }

    public static function updateOrder(array $orderIds) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;
        try {
            $stmt = $db->prepare("UPDATE `partners` SET `sort_order` = :order WHERE `id` = :id");
            foreach ($orderIds as $index => $id) {
                $stmt->execute([':order' => (int)$index, ':id' => (int)$id]);
            }
            return true;
        } catch (PDOException $e) {
            error_log("Partner::updateOrder error: " . $e->getMessage());
            return false;
        }
    }

    public static function toggleStatus($id) {
        $p = self::getById($id);
        if (!$p) return false;
        $newStatus = ($p['status'] === 'published') ? 'draft' : 'published';
        return self::exec("UPDATE `partners` SET `status` = :st WHERE `id` = :id", [':st' => $newStatus, ':id' => (int)$id]);
    }

    public static function togglePinned($id) {
        return self::exec("UPDATE `partners` SET `is_pinned` = 1 - `is_pinned` WHERE `id` = :id", [':id' => (int)$id]);
    }

    /**
     * Run a SELECT; returns null when the DB/table is unavailable so callers can fall back
     */
    private static function query($sql) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return null;

        try {
            return $db->query($sql)->fetchAll();
        } catch (PDOException $e) {
            error_log("Partner query error: " . $e->getMessage());
            return null;
        }
    }

    private static function exec($sql, array $params) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;

        try {
            return $db->prepare($sql)->execute($params);
        } catch (PDOException $e) {
            error_log("Partner exec error: " . $e->getMessage());
            return false;
        }
    }

    private static function uniqueSlug($db, $text, $excludeId) {
        $slug = normalizeSlug($text);
        $stmt = $db->prepare("SELECT COUNT(*) FROM `partners` WHERE `slug` = :slug AND `id` != :id");
        $stmt->execute([':slug' => $slug, ':id' => (int)$excludeId]);
        return $stmt->fetchColumn() > 0 ? $slug . '-' . time() : $slug;
    }
}
