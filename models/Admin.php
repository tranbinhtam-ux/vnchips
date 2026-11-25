<?php
/**
 * Admin User Model & Authentication
 */

if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}

class Admin {
    /**
     * Reason for the last failed login: 'credentials' or 'database'
     */
    public static $lastError = null;

    /**
     * Start the admin session with safe cookie settings and a writable save path
     */
    public static function startSession() {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        // Keep sessions active for 30 days (2,592,000 seconds)
        ini_set('session.gc_maxlifetime', 2592000);
        ini_set('session.cookie_lifetime', 2592000);

        // Some hosts ship an unwritable session.save_path, which silently drops the login
        $savePath = session_save_path();
        if ($savePath === '' || !is_writable($savePath)) {
            $fallback = __DIR__ . '/../storage/sessions';
            if (!is_dir($fallback)) {
                @mkdir($fallback, 0700, true);
            }
            if (is_writable($fallback)) {
                session_save_path($fallback);
            }
        }

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
            || (($_SERVER['SERVER_PORT'] ?? '') == 443);

        session_set_cookie_params([
            'lifetime' => 2592000, // 30 days persistent cookie
            'path'     => BASE_URL,
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        // Refresh existing session cookie lifetime to 30 days for active admin sessions
        if (!empty($_SESSION['admin_logged_in']) && !headers_sent()) {
            setcookie(session_name(), session_id(), [
                'expires'  => time() + 2592000,
                'path'     => BASE_URL,
                'secure'   => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }

    /**
     * Authenticate admin by username & password
     */
    public static function login($username, $password) {
        self::$lastError = null;
        $db = Database::getInstance()->getConnection();
        if (!$db) {
            self::$lastError = 'database';
            error_log("Admin::login DB connection failed: " . Database::getInstance()->getError());
            return false;
        }

        try {
            $stmt = $db->prepare("SELECT * FROM `admins` WHERE `username` = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                self::startSession();
                session_regenerate_id(true);

                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_fullname'] = $admin['fullname'];
                $_SESSION['admin_role'] = $admin['role'];

                // Update last login
                $db->prepare("UPDATE `admins` SET `last_login` = NOW() WHERE `id` = :id")->execute([':id' => $admin['id']]);

                // Persist the session before the redirect that follows
                session_write_close();
                return true;
            }
            self::$lastError = 'credentials';
        } catch (PDOException $e) {
            self::$lastError = 'database';
            error_log("Admin::login error: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Check if current session is logged in as admin
     */
    public static function check() {
        self::startSession();
        return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    /**
     * Require authentication or redirect to login page
     */
    public static function requireAuth() {
        if (!self::check()) {
            header("Location: " . admin_url('login.php'));
            exit;
        }
    }

    /**
     * Log out current admin
     */
    public static function logout() {
        self::startSession();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Get logged-in admin data
     */
    public static function current() {
        if (!self::check()) return null;
        return [
            'id' => $_SESSION['admin_id'] ?? 0,
            'username' => $_SESSION['admin_username'] ?? '',
            'fullname' => $_SESSION['admin_fullname'] ?? 'Admin',
            'role' => $_SESSION['admin_role'] ?? 'superadmin'
        ];
    }

    /**
     * Update admin profile
     */
    public static function updateProfile($id, $fullname, $email) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;
        try {
            $stmt = $db->prepare("UPDATE `admins` SET `fullname` = ?, `email` = ? WHERE `id` = ?");
            $ok = $stmt->execute([$fullname, $email, (int)$id]);
            if ($ok && isset($_SESSION['admin_id']) && $_SESSION['admin_id'] == $id) {
                $_SESSION['admin_fullname'] = $fullname;
            }
            return $ok;
        } catch (PDOException $e) {
            error_log("Admin::updateProfile error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update admin password
     */
    public static function updatePassword($id, $currentPass, $newPass) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;
        try {
            $stmt = $db->prepare("SELECT `password_hash` FROM `admins` WHERE `id` = ?");
            $stmt->execute([(int)$id]);
            $hash = $stmt->fetchColumn();
            if (!$hash || !password_verify($currentPass, $hash)) {
                return false;
            }
            $newHash = password_hash($newPass, PASSWORD_DEFAULT);
            $update = $db->prepare("UPDATE `admins` SET `password_hash` = ? WHERE `id` = ?");
            return $update->execute([$newHash, (int)$id]);
        } catch (PDOException $e) {
            error_log("Admin::updatePassword error: " . $e->getMessage());
            return false;
        }
    }
}
