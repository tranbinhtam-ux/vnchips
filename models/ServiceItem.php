<?php
/**
 * Design Service / IP Service accordion items
 *
 * One row = one accordion entry on index.php and business.php. Both pages render from
 * includes/design_ip_services.php, so editing a row here updates both at once.
 */

if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}

class ServiceItem {

    const GROUPS = ['design' => 'Design Service', 'ip' => 'IP Service'];

    /** Visible items of one column, in display order; $group = 'design' | 'ip' | null (all) */
    public static function getActive($group = null) {
        $rows = self::query("SELECT * FROM `service_items` WHERE `is_active` = 1 ORDER BY `sort_order` ASC, `id` ASC") ?? [];
        if ($group === null) return $rows;
        return array_values(array_filter($rows, static function ($r) use ($group) {
            return self::groupOf($r) === $group;
        }));
    }

    /** Every item for the admin list */
    public static function getAll($group = null) {
        $rows = self::query("SELECT * FROM `service_items` ORDER BY `column_group` ASC, `sort_order` ASC, `id` ASC") ?? [];
        if ($group === null) return $rows;
        return array_values(array_filter($rows, static function ($r) use ($group) {
            return self::groupOf($r) === $group;
        }));
    }

    public static function groupOf(array $item) {
        $group = $item['column_group'] ?? 'design';
        return isset(self::GROUPS[$group]) ? $group : 'design';
    }

    public static function getById($id) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("SELECT * FROM `service_items` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => (int)$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("ServiceItem::getById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create (id = null) or update an item. Returns the item id or false.
     * The anchor id has to stay unique: business.php links to #spec-gdsii and friends.
     */
    public static function save($id, array $data) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;

        try {
            $anchor = normalizeSlug((string)($data['anchor_id'] ?? ''));
            if ($anchor === '') {
                $anchor = slugify((string)($data['title'] ?? 'service'));
            }
            $check = $db->prepare("SELECT COUNT(*) FROM `service_items` WHERE `anchor_id` = :a AND `id` != :id");
            $check->execute([':a' => $anchor, ':id' => (int)$id]);
            if ($check->fetchColumn() > 0) {
                $anchor .= '-' . time();
            }

            $params = [
                ':grp'    => self::groupOf($data),
                ':anchor' => $anchor,
                ':title'  => trim((string)($data['title'] ?? '')),
                ':img'    => (string)($data['image_url'] ?? ''),
                ':body'   => (string)($data['body'] ?? ''),
                ':sort'   => (int)($data['sort_order'] ?? 0),
                ':active' => !empty($data['is_active']) ? 1 : 0,
            ];

            if ($id) {
                $params[':id'] = (int)$id;
                $stmt = $db->prepare("UPDATE `service_items` SET `column_group` = :grp, `anchor_id` = :anchor,
                    `title` = :title, `image_url` = :img, `body` = :body, `sort_order` = :sort,
                    `is_active` = :active WHERE `id` = :id");
                $stmt->execute($params);
                return (int)$id;
            }

            $stmt = $db->prepare("INSERT INTO `service_items` (`column_group`, `anchor_id`, `title`, `image_url`, `body`, `sort_order`, `is_active`)
                VALUES (:grp, :anchor, :title, :img, :body, :sort, :active)");
            $stmt->execute($params);
            return (int)$db->lastInsertId();
        } catch (PDOException $e) {
            error_log("ServiceItem::save error: " . $e->getMessage());
            return false;
        }
    }

    public static function updateOrder(array $orderIds) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;
        try {
            $stmt = $db->prepare("UPDATE `service_items` SET `sort_order` = :order WHERE `id` = :id");
            foreach ($orderIds as $index => $id) {
                $stmt->execute([':order' => (int)$index, ':id' => (int)$id]);
            }
            return true;
        } catch (PDOException $e) {
            error_log("ServiceItem::updateOrder error: " . $e->getMessage());
            return false;
        }
    }

    /** Drag-and-drop between the two columns also moves an item to that column */
    public static function updateOrderInGroup(array $orderIds, $group) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;
        $group = isset(self::GROUPS[$group]) ? $group : 'design';
        try {
            $stmt = $db->prepare("UPDATE `service_items` SET `sort_order` = :order, `column_group` = :grp WHERE `id` = :id");
            foreach ($orderIds as $index => $id) {
                $stmt->execute([':order' => (int)$index, ':grp' => $group, ':id' => (int)$id]);
            }
            return true;
        } catch (PDOException $e) {
            error_log("ServiceItem::updateOrderInGroup error: " . $e->getMessage());
            return false;
        }
    }

    public static function toggleActive($id) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;
        try {
            return $db->prepare("UPDATE `service_items` SET `is_active` = 1 - `is_active` WHERE `id` = :id")
                      ->execute([':id' => (int)$id]);
        } catch (PDOException $e) {
            error_log("ServiceItem::toggleActive error: " . $e->getMessage());
            return false;
        }
    }

    public static function delete($id) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;
        try {
            return $db->prepare("DELETE FROM `service_items` WHERE `id` = :id")->execute([':id' => (int)$id]);
        } catch (PDOException $e) {
            error_log("ServiceItem::delete error: " . $e->getMessage());
            return false;
        }
    }

    /** Run a SELECT; null when the DB/table is unavailable so callers can fall back */
    private static function query($sql) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return null;

        try {
            return $db->query($sql)->fetchAll();
        } catch (PDOException $e) {
            error_log("ServiceItem query error: " . $e->getMessage());
            return null;
        }
    }
}
