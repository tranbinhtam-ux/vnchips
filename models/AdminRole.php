<?php
/**
 * Admin Roles & Permissions (RBAC)
 *
 * Roles live in `admin_roles`, their granted permissions in `admin_role_permissions`.
 * The list of permissions themselves is defined here in code, not in the database:
 * a permission only means something if some page checks it, so the catalogue belongs
 * with the code that enforces it and is versioned alongside it.
 */

if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}

class AdminRole {

    /** Slug of the built-in role that always holds every permission */
    const SUPERADMIN = 'superadmin';

    /**
     * Permission catalogue, grouped exactly like the admin sidebar so the
     * checkbox matrix reads the same way as the menu it controls.
     */
    public static function catalogue() {
        return [
            'Overview' => [
                'dashboard.view'   => ['Bảng điều khiển', 'Xem trang Dashboard tổng quan.'],
                'analytics.view'   => ['Thống kê truy cập', 'Xem báo cáo lưu lượng truy cập.'],
                'guide.view'       => ['Hướng dẫn sử dụng', 'Xem trang User Guide.'],
            ],
            'Nội dung' => [
                'articles.manage'   => ['Bài viết & Tin tức', 'Thêm, sửa, xoá bài viết.'],
                'categories.manage' => ['Danh mục', 'Quản lý danh mục bài viết.'],
                'members.manage'    => ['Đội ngũ nhân sự', 'Quản lý Ban lãnh đạo và Key Team.'],
                'partners.manage'   => ['Đối tác & Case study', 'Quản lý đối tác và case study.'],
                'media.manage'      => ['Thư viện ảnh', 'Tải lên và xoá ảnh trong Media Gallery.'],
                'services.manage'   => ['Dịch vụ & Tooltip', 'Quản lý khối Design/IP Service và từ điển tooltip.'],
            ],
            'Khách hàng' => [
                'inquiries.manage' => ['Liên hệ & Khách hàng tiềm năng', 'Xem và xử lý yêu cầu liên hệ gửi về.'],
            ],
            'Dựng trang' => [
                'settings.homepage'    => ['Trang chủ & Chỉ số', 'Sửa nội dung trang chủ và các con số nổi bật.'],
                'banners.manage'       => ['Ảnh bìa Hero', 'Đổi ảnh bìa các trang.'],
                'page_sections.manage' => ['Khối nội dung trang', 'Sửa mã HTML tuỳ biến của từng khối.'],
                'menus.manage'         => ['Menu điều hướng', 'Sửa menu đầu trang và chân trang.'],
                'live_edit.use'        => ['Live Edit ngoài trang', 'Hiện thanh công cụ và sửa trực tiếp trên giao diện công khai.'],
            ],
            'Hệ thống' => [
                'site_settings.manage' => ['Cấu hình chung', 'Sửa thông tin liên hệ, mạng xã hội, cấu hình toàn site.'],
                'users.manage'         => ['Tài khoản & Phân quyền', 'Thêm, sửa, xoá tài khoản quản trị và vai trò. Quyền nhạy cảm nhất.'],
            ],
        ];
    }

    /** Flat list of every permission key */
    public static function allPermissions() {
        $out = [];
        foreach (self::catalogue() as $perms) {
            foreach ($perms as $key => $meta) {
                $out[] = $key;
            }
        }
        return $out;
    }

    /** Human label for a permission key */
    public static function labelOf($permission) {
        foreach (self::catalogue() as $perms) {
            if (isset($perms[$permission])) return $perms[$permission][0];
        }
        return $permission;
    }

    /**
     * Which permission each admin script requires.
     *
     * A script that is not listed stays open to any signed-in admin: new pages must
     * never lock their author out before the permission exists. Add them here as
     * they are built.
     */
    public static function pageMap() {
        return [
            'index.php'          => 'dashboard.view',
            'analytics.php'      => 'analytics.view',
            'user-guide.php'     => 'guide.view',

            'articles.php'       => 'articles.manage',
            'article-create.php' => 'articles.manage',
            'article-edit.php'   => 'articles.manage',
            'categories.php'     => 'categories.manage',
            'members.php'        => 'members.manage',
            'member-edit.php'    => 'members.manage',
            'partners.php'       => 'partners.manage',
            'partner-edit.php'   => 'partners.manage',
            'media.php'          => 'media.manage',
            'services.php'       => 'services.manage',
            'service-edit.php'   => 'services.manage',
            'tooltips.php'       => 'services.manage',

            'inquiries.php'      => 'inquiries.manage',

            'settings.php'       => 'settings.homepage',
            'banners.php'        => 'banners.manage',
            'page-sections.php'  => 'page_sections.manage',
            'menus.php'          => 'menus.manage',

            'site-settings.php'  => 'site_settings.manage',
            'users.php'          => 'users.manage',
            'user-edit.php'      => 'users.manage',
            'roles.php'          => 'users.manage',
            'role-edit.php'      => 'users.manage',

            // profile.php and logout.php stay open: everyone manages their own account.
        ];
    }

    /** Permission required by an admin script, or null when it is open to all admins */
    public static function permissionForPage($script) {
        $map = self::pageMap();
        return $map[basename((string)$script)] ?? null;
    }

    /**
     * Permission required by an AJAX endpoint action.
     *
     * entity-actions.php serves several modules at once, so the script name alone
     * cannot say what a request is allowed to do -- the action has to decide.
     * Keeping the table here (rather than inside each endpoint) means the policy
     * lives in one place next to pageMap().
     */
    public static function ajaxActionMap() {
        return [
            'article-actions.php' => [
                '*' => 'articles.manage',
            ],
            'entity-actions.php' => [
                'reorder_members'       => 'members.manage',
                'toggle_member_active'  => 'members.manage',
                'reorder_partners'      => 'partners.manage',
                'toggle_partner_pinned' => 'partners.manage',
                'toggle_partner_status' => 'partners.manage',
                'get_inquiry_detail'    => 'inquiries.manage',
                'update_inquiry_status' => 'inquiries.manage',
                'reorder_service_items' => 'services.manage',
                'toggle_service_item'   => 'services.manage',
                'toggle_service_tooltip' => 'services.manage',
            ],
        ];
    }

    /**
     * Permission required by the current AJAX request, or null when the script or
     * action is not listed (then only sign-in is required, as before).
     */
    public static function permissionForAjax($script, $action) {
        $map = self::ajaxActionMap();
        $script = basename((string)$script);
        if (!isset($map[$script])) return null;
        return $map[$script][(string)$action] ?? $map[$script]['*'] ?? null;
    }

    /* ------------------------------------------------------------------ roles */

    public static function getAll() {
        $db = Database::getInstance()->getConnection();
        if (!$db) return [];
        try {
            return $db->query("SELECT * FROM `admin_roles` ORDER BY `is_system` DESC, `label` ASC")->fetchAll();
        } catch (PDOException $e) {
            error_log("AdminRole::getAll error: " . $e->getMessage());
            return [];
        }
    }

    public static function getById($id) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return null;
        try {
            $stmt = $db->prepare("SELECT * FROM `admin_roles` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => (int)$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("AdminRole::getById error: " . $e->getMessage());
            return null;
        }
    }

    public static function getBySlug($slug) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return null;
        try {
            $stmt = $db->prepare("SELECT * FROM `admin_roles` WHERE `name` = :name LIMIT 1");
            $stmt->execute([':name' => (string)$slug]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("AdminRole::getBySlug error: " . $e->getMessage());
            return null;
        }
    }

    /** True when this role is the built-in superadmin (implicitly holds every permission) */
    public static function isSuperadmin($role) {
        if (!$role) return false;
        $slug = is_array($role) ? ($role['name'] ?? '') : (string)$role;
        return $slug === self::SUPERADMIN;
    }

    /** Permission keys granted to a role id (superadmin gets the full catalogue) */
    public static function permissionsOf($roleId) {
        $role = self::getById($roleId);
        if (!$role) return [];
        if (self::isSuperadmin($role)) return self::allPermissions();

        $db = Database::getInstance()->getConnection();
        if (!$db) return [];
        try {
            $stmt = $db->prepare("SELECT `permission` FROM `admin_role_permissions` WHERE `role_id` = :id");
            $stmt->execute([':id' => (int)$roleId]);
            return array_column($stmt->fetchAll(), 'permission');
        } catch (PDOException $e) {
            error_log("AdminRole::permissionsOf error: " . $e->getMessage());
            return [];
        }
    }

    /** How many accounts currently use a role */
    public static function userCount($roleId) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return 0;
        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM `admins` WHERE `role_id` = :id");
            $stmt->execute([':id' => (int)$roleId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Create ($id = null) or update a role together with its permission set.
     * Returns the role id, or false.
     */
    public static function save($id, array $data, array $permissions) {
        $db = Database::getInstance()->getConnection();
        if (!$db) return false;

        $label = trim((string)($data['label'] ?? ''));
        if ($label === '') return false;
        $slug = !empty($data['name']) ? normalizeSlug($data['name']) : slugify($label);
        if ($slug === '') return false;

        // Only ever store permissions that actually exist in the catalogue
        $permissions = array_values(array_intersect(self::allPermissions(), $permissions));

        try {
            $check = $db->prepare("SELECT COUNT(*) FROM `admin_roles` WHERE `name` = :name AND `id` != :id");
            $check->execute([':name' => $slug, ':id' => (int)$id]);
            if ($check->fetchColumn() > 0) {
                return false; // slug taken
            }

            if ($id) {
                $existing = self::getById($id);
                if (!$existing) return false;
                // The built-in superadmin keeps its slug; renaming it would orphan the implicit grant
                if (self::isSuperadmin($existing)) {
                    $slug = self::SUPERADMIN;
                }
                $stmt = $db->prepare("UPDATE `admin_roles` SET `name` = :name, `label` = :label, `description` = :descr WHERE `id` = :id");
                $stmt->execute([
                    ':name' => $slug,
                    ':label' => $label,
                    ':descr' => trim((string)($data['description'] ?? '')),
                    ':id' => (int)$id,
                ]);
                $roleId = (int)$id;
            } else {
                $stmt = $db->prepare("INSERT INTO `admin_roles` (`name`, `label`, `description`, `is_system`) VALUES (:name, :label, :descr, 0)");
                $stmt->execute([
                    ':name' => $slug,
                    ':label' => $label,
                    ':descr' => trim((string)($data['description'] ?? '')),
                ]);
                $roleId = (int)$db->lastInsertId();
            }

            // Superadmin's grant is implicit; never materialise rows for it
            if (!self::isSuperadmin($slug)) {
                $db->prepare("DELETE FROM `admin_role_permissions` WHERE `role_id` = :id")->execute([':id' => $roleId]);
                if ($permissions) {
                    $ins = $db->prepare("INSERT INTO `admin_role_permissions` (`role_id`, `permission`) VALUES (:id, :perm)");
                    foreach ($permissions as $perm) {
                        $ins->execute([':id' => $roleId, ':perm' => $perm]);
                    }
                }
            }

            // Keep the legacy `admins`.`role` text column in step with the slug
            $db->prepare("UPDATE `admins` SET `role` = :name WHERE `role_id` = :id")->execute([':name' => $slug, ':id' => $roleId]);

            return $roleId;
        } catch (PDOException $e) {
            error_log("AdminRole::save error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a role. Built-in roles and roles still in use are refused;
     * returns true, or an error string explaining why not.
     */
    public static function delete($id) {
        $role = self::getById($id);
        if (!$role) return 'Vai trò không tồn tại.';
        if (!empty($role['is_system'])) return 'Không thể xoá vai trò hệ thống.';
        if (self::userCount($id) > 0) return 'Vai trò này đang được gán cho tài khoản khác. Hãy chuyển các tài khoản đó sang vai trò khác trước.';

        $db = Database::getInstance()->getConnection();
        if (!$db) return 'Không kết nối được cơ sở dữ liệu.';
        try {
            $db->prepare("DELETE FROM `admin_role_permissions` WHERE `role_id` = :id")->execute([':id' => (int)$id]);
            $db->prepare("DELETE FROM `admin_roles` WHERE `id` = :id")->execute([':id' => (int)$id]);
            return true;
        } catch (PDOException $e) {
            error_log("AdminRole::delete error: " . $e->getMessage());
            return 'Lỗi cơ sở dữ liệu khi xoá vai trò.';
        }
    }
}
