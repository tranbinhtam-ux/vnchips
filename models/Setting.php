<?php
/**
 * System & Homepage Settings Model
 */

if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}

class Setting {
    private static $cache = [];

    /**
     * Get a setting by key with a fallback default
     */
    public static function get($key, $default = '') {
        self::loadAll();
        return array_key_exists($key, self::$cache) ? self::$cache[$key] : $default;
    }

    /**
     * Like get(), but an empty stored value also falls back to the default (used for image paths)
     */
    public static function getOr($key, $default) {
        $val = self::get($key, '');
        return ($val === '' || $val === null) ? $default : $val;
    }

    /**
     * Load every setting in one query (settings are read many times per page)
     */
    private static function loadAll() {
        static $loaded = false;
        if ($loaded) return;
        $loaded = true;

        $db = Database::getInstance()->getConnection();
        if (!$db) return;

        try {
            foreach ($db->query("SELECT `setting_key`, `setting_value` FROM `settings`") as $row) {
                self::$cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (PDOException $e) {
            error_log("Setting::loadAll error: " . $e->getMessage());
        }
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup($group) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("SELECT * FROM `settings` WHERE `setting_group` = :group");
            $stmt->execute([':group' => $group]);
            $rows = $stmt->fetchAll();
            $result = [];
            foreach ($rows as $r) {
                $result[$r['setting_key']] = $r['setting_value'];
            }
            return $result;
        } catch (PDOException $e) {
            error_log("Setting::getByGroup error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Set/Update a single setting
     */
    public static function set($key, $value, $group = 'general', $description = null) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`, `description`) 
                VALUES (:key, :val, :group, :desc) 
                ON DUPLICATE KEY UPDATE `setting_value` = :val2");
            $stmt->execute([
                ':key' => $key,
                ':val' => $value,
                ':group' => $group,
                ':desc' => $description,
                ':val2' => $value
            ]);
            self::$cache[$key] = $value;
            return true;
        } catch (PDOException $e) {
            error_log("Setting::set error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove a setting so the page falls back to its built-in default
     * (used by Live Edit "reset" actions: reset_section, reset_program).
     */
    public static function delete($key) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;

        try {
            $db->prepare("DELETE FROM `settings` WHERE `setting_key` = :key")->execute([':key' => $key]);
            unset(self::$cache[$key]);
            return true;
        } catch (PDOException $e) {
            error_log("Setting::delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Batch update an array of key => value pairs
     */
    public static function updateBatch(array $keyValues, $group = 'general') {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;

        try {
            // Upsert so keys that were never seeded (new settings) are created on first save
            $stmt = $db->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`) VALUES (:key, :val, :group)
                ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)");
            foreach ($keyValues as $k => $v) {
                $stmt->execute([':val' => $v, ':key' => $k, ':group' => $group]);
                self::$cache[$k] = $v;
            }
            return true;
        } catch (PDOException $e) {
            error_log("Setting::updateBatch error: " . $e->getMessage());
            return false;
        }
    }
}
