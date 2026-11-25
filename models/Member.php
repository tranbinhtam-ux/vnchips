<?php
/**
 * Team Members / Executive Leaders Model
 * Falls back to data/members.php when the database (or the `members` table) is unavailable
 */

if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}

class Member {

    const GROUPS = ['leadership' => 'Leadership', 'key_team' => 'Key Team'];

    /**
     * Active members for the public site, by sort order; $group = 'leadership' | 'key_team' | null (all)
     */
    public static function getActive($group = null) {
        $rows = self::query("SELECT * FROM `members` WHERE `is_active` = 1 ORDER BY `sort_order` ASC, `id` ASC");
        $rows = $rows !== null ? $rows : array_values(require __DIR__ . '/../data/members.php');
        if ($group === null) return $rows;
        return array_values(array_filter($rows, function ($m) use ($group) {
            return self::groupOf($m) === $group;
        }));
    }

    /**
     * About Us section a member belongs to (rows from before the team_group column count as leadership)
     */
    public static function groupOf(array $member) {
        $group = $member['team_group'] ?? 'leadership';
        return isset(self::GROUPS[$group]) ? $group : 'leadership';
    }

    /**
     * All members for the admin list
     */
    public static function getAll() {
        return self::query("SELECT * FROM `members` ORDER BY `sort_order` ASC, `id` ASC") ?? [];
    }

    public static function getBySlug($slug) {
        foreach (self::getActive() as $m) {
            if ($m['slug'] === $slug) return $m;
        }
        return null;
    }

    public static function getById($id) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("SELECT * FROM `members` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => (int)$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Member::getById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create (id = null) or update a member. Returns the member id or false.
     */
    public static function save($id, array $data) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;

        try {
            $slug = !empty($data['slug']) ? normalizeSlug($data['slug']) : slugify($data['name']);
            $check = $db->prepare("SELECT COUNT(*) FROM `members` WHERE `slug` = :slug AND `id` != :id");
            $check->execute([':slug' => $slug, ':id' => (int)$id]);
            if ($check->fetchColumn() > 0) {
                $slug .= '-' . time();
            }

            $params = [
                ':slug' => $slug,
                ':name' => $data['name'],
                ':position' => $data['position'],
                ':short' => $data['short_position'],
                ':img' => $data['image_url'],
                ':bio' => $data['bio'],
                ':sort' => (int)$data['sort_order'],
                ':linkedin' => $data['linkedin_url'],
                ':twitter' => $data['twitter_url'],
                ':email' => $data['email_address'],
                ':active' => !empty($data['is_active']) ? 1 : 0,
                ':grp' => self::groupOf($data),
            ];

            if ($id) {
                $params[':id'] = (int)$id;
                $stmt = $db->prepare("UPDATE `members` SET `slug` = :slug, `name` = :name, `position` = :position, `short_position` = :short,
                    `image_url` = :img, `bio` = :bio, `sort_order` = :sort, `linkedin_url` = :linkedin, `twitter_url` = :twitter,
                    `email_address` = :email, `is_active` = :active, `team_group` = :grp WHERE `id` = :id");
                $stmt->execute($params);
                return (int)$id;
            }

            $stmt = $db->prepare("INSERT INTO `members` (`slug`, `name`, `position`, `short_position`, `image_url`, `bio`, `sort_order`, `linkedin_url`, `twitter_url`, `email_address`, `is_active`, `team_group`)
                VALUES (:slug, :name, :position, :short, :img, :bio, :sort, :linkedin, :twitter, :email, :active, :grp)");
            $stmt->execute($params);
            return (int)$db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Member::save error: " . $e->getMessage());
            return false;
        }
    }

    public static function updateOrder(array $orderIds) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;
        try {
            $stmt = $db->prepare("UPDATE `members` SET `sort_order` = :order WHERE `id` = :id");
            foreach ($orderIds as $index => $id) {
                $stmt->execute([':order' => (int)$index, ':id' => (int)$id]);
            }
            return true;
        } catch (PDOException $e) {
            error_log("Member::updateOrder error: " . $e->getMessage());
            return false;
        }
    }

    public static function toggleActive($id) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;
        try {
            return $db->prepare("UPDATE `members` SET `is_active` = 1 - `is_active` WHERE `id` = :id")->execute([':id' => (int)$id]);
        } catch (PDOException $e) {
            error_log("Member::toggleActive error: " . $e->getMessage());
            return false;
        }
    }

    public static function delete($id) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;

        try {
            return $db->prepare("DELETE FROM `members` WHERE `id` = :id")->execute([':id' => (int)$id]);
        } catch (PDOException $e) {
            error_log("Member::delete error: " . $e->getMessage());
            return false;
        }
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
            error_log("Member query error: " . $e->getMessage());
            return null;
        }
    }
}
