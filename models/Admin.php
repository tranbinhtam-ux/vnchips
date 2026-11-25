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

    /** Set once a session is dropped for being older than SESSION_LIFETIME */
    public static $sessionExpired = false;

    /**
     * How long a sign-in lasts, counted from the moment of login (24 hours).
     *
     * This is an absolute window, not a sliding one: staying active does not extend
     * it, so after 24 hours everyone signs in again.
     */
    const SESSION_LIFETIME = 86400;

    public static function startSession() {
        if (session_status() !== PHP_SESSION_NONE) {
            self::enforceSessionLifetime();
            return;
        }

        ini_set('session.gc_maxlifetime', self::SESSION_LIFETIME);
        ini_set('session.cookie_lifetime', self::SESSION_LIFETIME);

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

        /**
         * Start the admin session with safe cookie settings and a writable save path
         */
        session_set_cookie_params(self::cookieParams(self::SESSION_LIFETIME));
        session_start();

        // Deliberately no cookie refresh here: re-sending it on every request would
        // turn the 24-hour window into a rolling one that never expires for anyone
        // who keeps the tab open.
        self::enforceSessionLifetime();
    }

    /** Cookie settings shared by login, expiry and logout so all three match exactly */
    private static function cookieParams($lifetime) {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
            || (($_SERVER['SERVER_PORT'] ?? '') == 443);

        return [
            'lifetime' => $lifetime,
            'path'     => BASE_URL,
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ];
    }

    /**
     * Sign out a session that is past its 24 hours.
     *
     * The browser cookie expires on its own, but the server must not trust that:
     * a cookie's expiry is a request the browser is free to ignore, while the
     * timestamp stored in the session is ours.
     */
    private static function enforceSessionLifetime() {
        if (empty($_SESSION['admin_logged_in'])) return;

        $startedAt = (int)($_SESSION['admin_login_at'] ?? 0);

        // Sessions opened before this rule existed carry no timestamp. Stamp them now
        // rather than signing those people out mid-task; their 24 hours start here.
        if ($startedAt <= 0) {
            $_SESSION['admin_login_at'] = time();
            return;
        }

        if ((time() - $startedAt) <= self::SESSION_LIFETIME) {
            return;
        }

        // Flagged in memory, not in the session: the session is about to be destroyed,
        // so anything written to it now would be thrown away with it.
        self::$sessionExpired = true;
        self::destroySession();
    }

    /** Empty the session and tell the browser to drop the cookie */
    private static function destroySession() {
        self::resetCaches();
        $_SESSION = [];
        if (ini_get('session.use_cookies') && !headers_sent()) {
            $params = self::cookieParams(0);
            $params['expires'] = time() - 42000;
            unset($params['lifetime']);
            setcookie(session_name(), '', $params);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
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

            if ($admin && array_key_exists('is_active', $admin) && (int)$admin['is_active'] === 0) {
                self::$lastError = 'disabled';
                return false;
            }

            if ($admin && password_verify($password, $admin['password_hash'])) {
                self::startSession();
                session_regenerate_id(true);

                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_fullname'] = $admin['fullname'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_role_id'] = $admin['role_id'] ?? null;
                // Start of the 24-hour window; never refreshed while browsing
                $_SESSION['admin_login_at'] = time();
                self::resetCaches();

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
     * Does the current session claim to be signed in? (session only, no database)
     */
    private static function hasSession() {
        self::startSession();
        return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    /**
     * The signed-in admin's current row, re-read once per request.
     *
     * Reading it back matters: it is how deactivating an account or changing its role
     * takes effect on the next click instead of whenever that person happens to log in
     * again. If the database is unreachable we fall back to the session rather than
     * locking everyone out of a site that is merely having a bad minute.
     */
    /** Per-request caches; reset whenever the session itself changes */
    private static $recordCache = false;
    private static $permissionsCache = null;

    /** Forget what we know about the signed-in admin (after login, logout or expiry) */
    private static function resetCaches() {
        self::$recordCache = false;
        self::$permissionsCache = null;
    }

    public static function record() {
        if (self::$recordCache !== false) return self::$recordCache;
        if (!self::hasSession()) return self::$recordCache = null;

        $sessionRecord = [
            'id'       => $_SESSION['admin_id'] ?? 0,
            'username' => $_SESSION['admin_username'] ?? '',
            'fullname' => $_SESSION['admin_fullname'] ?? 'Admin',
            'email'    => '',
            'role'     => $_SESSION['admin_role'] ?? self::ROLE_FALLBACK,
            'role_id'  => $_SESSION['admin_role_id'] ?? null,
            'is_active' => 1,
        ];

        $db = Database::getInstance()->getConnection();
        if (!$db) return self::$recordCache = $sessionRecord;

        try {
            $stmt = $db->prepare("SELECT * FROM `admins` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => (int)$sessionRecord['id']]);
            $row = $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Admin::record error: " . $e->getMessage());
            return self::$recordCache = $sessionRecord;
        }

        // Account deleted or switched off since sign-in
        if (!$row || (array_key_exists('is_active', $row) && (int)$row['is_active'] === 0)) {
            return self::$recordCache = null;
        }

        $_SESSION['admin_fullname'] = $row['fullname'];
        $_SESSION['admin_role'] = $row['role'];
        $_SESSION['admin_role_id'] = $row['role_id'] ?? null;

        return self::$recordCache = $row;
    }

    /**
     * Check if current session is logged in as admin
     */
    public static function check() {
        return self::record() !== null;
    }

    /**
     * Require authentication or redirect to login page
     */
    public static function requireAuth() {
        if (!self::check()) {
            $suffix = self::$sessionExpired ? '?expired=1' : '';
            header("Location: " . admin_url('login.php' . $suffix));
            exit;
        }
    }

    /* ------------------------------------------------------- permissions (RBAC) */

    /** Role slug used for accounts created before roles existed */
    const ROLE_FALLBACK = 'superadmin';

    /** Permission keys the signed-in admin holds */
    public static function permissions() {
        
        if (self::$permissionsCache !== null) return self::$permissionsCache;

        $me = self::record();
        if (!$me) return self::$permissionsCache = [];

        require_once __DIR__ . '/AdminRole.php';

        // Accounts from before roles existed, and anything still flagged superadmin,
        // keep full access -- an upgrade must never lock the owner out of their own site.
        if (empty($me['role_id'])) {
            return self::$permissionsCache = AdminRole::isSuperadmin($me['role'] ?? self::ROLE_FALLBACK)
                ? AdminRole::allPermissions()
                : [];
        }

        return self::$permissionsCache = AdminRole::permissionsOf($me['role_id']);
    }

    /** Does the signed-in admin hold this permission? */
    public static function can($permission) {
        if ($permission === null || $permission === '') return self::check();
        return in_array($permission, self::permissions(), true);
    }

    /**
     * First admin page this account may actually open.
     *
     * Sending someone to a dashboard they cannot view would bounce them to an error
     * page whose only button leads back to the same dashboard.
     */
    public static function landingUrl() {
        require_once __DIR__ . '/AdminRole.php';
        foreach (AdminRole::pageMap() as $page => $permission) {
            if (self::can($permission)) {
                return admin_url($page);
            }
        }
        return admin_url('profile.php');
    }

    /** True when the signed-in admin holds the built-in superadmin role */
    public static function isSuperadmin() {
        $me = self::record();
        if (!$me) return false;
        require_once __DIR__ . '/AdminRole.php';
        if (!empty($me['role_id'])) {
            return AdminRole::isSuperadmin(AdminRole::getById($me['role_id']));
        }
        return AdminRole::isSuperadmin($me['role'] ?? self::ROLE_FALLBACK);
    }

    /**
     * Require a permission; signed-out users go to the login page, signed-in users
     * without the permission get a dead end rather than a redirect loop.
     */
    public static function requirePermission($permission) {
        self::requireAuth();
        if ($permission === null || $permission === '') return;
        if (self::can($permission)) return;

        require_once __DIR__ . '/AdminRole.php';
        http_response_code(403);
        $label = e(AdminRole::labelOf($permission));
        $backUrl = e(self::landingUrl());
        echo <<<HTML
<!doctype html>
<html lang="vi"><head><meta charset="utf-8"><title>Không đủ quyền</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></head>
<body style="background:#F1F5F9">
<div class="container" style="max-width:560px;margin-top:12vh">
  <div class="card border-0 shadow-sm p-4 text-center" style="border-radius:14px">
    <div style="font-size:2.6rem;line-height:1">&#128274;</div>
    <h4 class="fw-bold mt-3 mb-2">Bạn không có quyền vào mục này</h4>
    <p class="text-muted mb-1">Trang này cần quyền <strong>{$label}</strong>.</p>
    <p class="text-muted small mb-4">Liên hệ quản trị viên cấp cao để được cấp quyền.</p>
    <a href="{$backUrl}" class="btn btn-primary">Về Bảng điều khiển</a>
  </div>
</div>
</body></html>
HTML;
        exit;
    }

    /* ------------------------------------------------------------ user accounts */

    /** Every admin account, with its role label, for the users list */
    public static function getAllUsers() {
        $db = Database::getInstance()->getConnection();
        if (!$db) return [];
        try {
            return $db->query("SELECT a.*, r.`label` AS role_label, r.`name` AS role_slug, r.`is_system` AS role_is_system
                FROM `admins` a LEFT JOIN `admin_roles` r ON r.`id` = a.`role_id`
                ORDER BY a.`id` ASC")->fetchAll();
        } catch (PDOException $e) {
            error_log("Admin::getAllUsers error: " . $e->getMessage());
            return [];
        }
    }

    public static function getUserById($id) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return null;
        try {
            $stmt = $db->prepare("SELECT * FROM `admins` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => (int)$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Admin::getUserById error: " . $e->getMessage());
            return null;
        }
    }

    /** How many enabled accounts still hold the built-in superadmin role */
    public static function activeSuperadminCount() {
        $db = Database::getInstance()->getConnection();
        if (!$db) return 0;
        try {
            $sql = "SELECT COUNT(*) FROM `admins` a LEFT JOIN `admin_roles` r ON r.`id` = a.`role_id`
                    WHERE a.`is_active` = 1 AND (r.`name` = 'superadmin' OR (a.`role_id` IS NULL AND a.`role` = 'superadmin'))";
            return (int)$db->query($sql)->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Create ($id = null) or update an admin account.
     * Returns the account id, or an error string.
     */
    public static function saveUser($id, array $data) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return 'Không kết nối được cơ sở dữ liệu.';

        require_once __DIR__ . '/AdminRole.php';

        $username = strtolower(trim((string)($data['username'] ?? '')));
        $fullname = trim((string)($data['fullname'] ?? ''));
        $email    = trim((string)($data['email'] ?? ''));
        $password = (string)($data['password'] ?? '');
        $roleId   = (int)($data['role_id'] ?? 0);
        $isActive = !empty($data['is_active']) ? 1 : 0;

        if ($username === '' || $fullname === '') return 'Vui lòng nhập Họ tên và Tên đăng nhập.';
        if (!preg_match('/^[a-z0-9._-]{3,50}$/', $username)) {
            return 'Tên đăng nhập chỉ gồm chữ thường, số, dấu chấm, gạch ngang hoặc gạch dưới (3-50 ký tự).';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Email không hợp lệ.';
        if (!AdminRole::getById($roleId)) return 'Vui lòng chọn vai trò hợp lệ.';
        if (!$id && strlen($password) < 8) return 'Mật khẩu phải có ít nhất 8 ký tự.';
        if ($id && $password !== '' && strlen($password) < 8) return 'Mật khẩu mới phải có ít nhất 8 ký tự.';

        $roleSlug = AdminRole::getById($roleId)['name'];

        try {
            $check = $db->prepare("SELECT COUNT(*) FROM `admins` WHERE `username` = :u AND `id` != :id");
            $check->execute([':u' => $username, ':id' => (int)$id]);
            if ($check->fetchColumn() > 0) return 'Tên đăng nhập này đã tồn tại.';

            if ($id) {
                // Never let the site end up with no usable superadmin
                $existing = self::getUserById($id);
                if ($existing) {
                    $wasSuper = AdminRole::isSuperadmin($existing['role'] ?? '')
                        || (!empty($existing['role_id']) && AdminRole::isSuperadmin(AdminRole::getById($existing['role_id'])));
                    $staysSuper = AdminRole::isSuperadmin($roleSlug) && $isActive === 1;
                    if ($wasSuper && (int)($existing['is_active'] ?? 1) === 1 && !$staysSuper
                        && self::activeSuperadminCount() <= 1) {
                        return 'Đây là tài khoản Superadmin đang hoạt động duy nhất. Hãy cấp quyền Superadmin cho một tài khoản khác trước khi đổi hoặc khoá tài khoản này.';
                    }
                }

                $sql = "UPDATE `admins` SET `username` = :u, `fullname` = :f, `email` = :e, `role` = :rs, `role_id` = :r, `is_active` = :a";
                $params = [':u' => $username, ':f' => $fullname, ':e' => $email, ':rs' => $roleSlug,
                           ':r' => $roleId, ':a' => $isActive, ':id' => (int)$id];
                if ($password !== '') {
                    $sql .= ", `password_hash` = :p";
                    $params[':p'] = password_hash($password, PASSWORD_DEFAULT);
                }
                $sql .= " WHERE `id` = :id";
                $db->prepare($sql)->execute($params);
                return (int)$id;
            }

            $stmt = $db->prepare("INSERT INTO `admins` (`username`, `password_hash`, `fullname`, `email`, `role`, `role_id`, `is_active`)
                VALUES (:u, :p, :f, :e, :rs, :r, :a)");
            $stmt->execute([
                ':u' => $username,
                ':p' => password_hash($password, PASSWORD_DEFAULT),
                ':f' => $fullname,
                ':e' => $email,
                ':rs' => $roleSlug,
                ':r' => $roleId,
                ':a' => $isActive,
            ]);
            return (int)$db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Admin::saveUser error: " . $e->getMessage());
            return 'Lỗi cơ sở dữ liệu khi lưu tài khoản.';
        }
    }

    /** Delete an account. Returns true, or an error string. */
    public static function deleteUser($id) {
        $id = (int)$id;
        $me = self::record();
        if ($me && (int)$me['id'] === $id) return 'Bạn không thể tự xoá tài khoản của chính mình.';

        $user = self::getUserById($id);
        if (!$user) return 'Tài khoản không tồn tại.';

        require_once __DIR__ . '/AdminRole.php';
        $isSuper = AdminRole::isSuperadmin($user['role'] ?? '')
            || (!empty($user['role_id']) && AdminRole::isSuperadmin(AdminRole::getById($user['role_id'])));
        if ($isSuper && (int)($user['is_active'] ?? 1) === 1 && self::activeSuperadminCount() <= 1) {
            return 'Đây là tài khoản Superadmin đang hoạt động duy nhất, không thể xoá.';
        }

        $db = Database::getInstance()->getConnection();
        if (!$db) return 'Không kết nối được cơ sở dữ liệu.';
        try {
            $db->prepare("DELETE FROM `admins` WHERE `id` = :id")->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            error_log("Admin::deleteUser error: " . $e->getMessage());
            return 'Lỗi cơ sở dữ liệu khi xoá tài khoản.';
        }
    }

    /**
     * Log out current admin
     */
    public static function logout() {
        self::startSession();
        self::destroySession();
    }

    /**
     * Get logged-in admin data
     */
    public static function current() {
        $me = self::record();
        if (!$me) return null;

        $roleLabel = $me['role'] ?? self::ROLE_FALLBACK;
        if (!empty($me['role_id'])) {
            require_once __DIR__ . '/AdminRole.php';
            $role = AdminRole::getById($me['role_id']);
            if ($role) $roleLabel = $role['label'];
        }

        return [
            'id' => $me['id'] ?? 0,
            'username' => $me['username'] ?? '',
            'fullname' => $me['fullname'] ?? 'Admin',
            'email' => $me['email'] ?? '',
            'role' => $roleLabel,
            'role_id' => $me['role_id'] ?? null,
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
