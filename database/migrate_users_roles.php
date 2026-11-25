<?php
/**
 * Migration: admin accounts, roles & permissions (RBAC)
 *
 * Safe to run more than once: every step checks before it changes anything.
 *
 *   php database/migrate_users_roles.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/AdminRole.php';

$db = Database::getInstance()->getConnection();
if (!$db) {
    fwrite(STDERR, "Khong ket noi duoc CSDL: " . Database::getInstance()->getError() . "\n");
    exit(1);
}

function step($msg) { echo "  - $msg\n"; }

function columnExists(PDO $db, $table, $column) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c");
    $stmt->execute([':t' => $table, ':c' => $column]);
    return (int)$stmt->fetchColumn() > 0;
}

echo "== 1. Bang vai tro ==\n";
$db->exec("CREATE TABLE IF NOT EXISTS `admin_roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `label` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) NOT NULL DEFAULT '',
  `is_system` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
step("admin_roles san sang");

$db->exec("CREATE TABLE IF NOT EXISTS `admin_role_permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_id` INT NOT NULL,
  `permission` VARCHAR(64) NOT NULL,
  UNIQUE KEY `role_permission` (`role_id`, `permission`),
  KEY `role_idx` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
step("admin_role_permissions san sang");

echo "== 2. Mo rong bang admins ==\n";
// ENUM('superadmin','editor') khong chua duoc vai tro tu tao -> doi sang VARCHAR
$db->exec("ALTER TABLE `admins` MODIFY `role` VARCHAR(50) NOT NULL DEFAULT 'superadmin'");
step("admins.role -> VARCHAR(50)");

if (!columnExists($db, 'admins', 'role_id')) {
    $db->exec("ALTER TABLE `admins` ADD COLUMN `role_id` INT NULL AFTER `role`");
    step("them admins.role_id");
} else {
    step("admins.role_id da co");
}

if (!columnExists($db, 'admins', 'is_active')) {
    $db->exec("ALTER TABLE `admins` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `role_id`");
    step("them admins.is_active");
} else {
    step("admins.is_active da co");
}

echo "== 3. Seed vai tro mac dinh ==\n";
$seedRoles = [
    'superadmin' => [
        'label' => 'Superadmin',
        'description' => 'Toan quyen he thong, bao gom quan ly tai khoan va phan quyen.',
        'is_system' => 1,
        'permissions' => [], // implicit: luon co toan bo quyen
    ],
    'editor' => [
        'label' => 'Bien tap noi dung',
        'description' => 'Quan ly bai viet, nhan su, doi tac, thu vien anh va lien he. Khong vao duoc cau hinh he thong.',
        'is_system' => 0,
        'permissions' => [
            'dashboard.view', 'analytics.view', 'guide.view',
            'articles.manage', 'categories.manage', 'members.manage',
            'partners.manage', 'media.manage', 'services.manage',
            'inquiries.manage', 'live_edit.use',
        ],
    ],
    'viewer' => [
        'label' => 'Chi xem',
        'description' => 'Chi xem bang dieu khien, thong ke va lien he gui ve.',
        'is_system' => 0,
        'permissions' => ['dashboard.view', 'analytics.view', 'guide.view', 'inquiries.manage'],
    ],
];

$findRole = $db->prepare("SELECT `id` FROM `admin_roles` WHERE `name` = :n LIMIT 1");
$insRole  = $db->prepare("INSERT INTO `admin_roles` (`name`, `label`, `description`, `is_system`) VALUES (:n, :l, :d, :s)");
$insPerm  = $db->prepare("INSERT IGNORE INTO `admin_role_permissions` (`role_id`, `permission`) VALUES (:r, :p)");
$roleIds = [];

foreach ($seedRoles as $slug => $role) {
    $findRole->execute([':n' => $slug]);
    $roleId = (int)$findRole->fetchColumn();

    if (!$roleId) {
        $insRole->execute([':n' => $slug, ':l' => $role['label'], ':d' => $role['description'], ':s' => $role['is_system']]);
        $roleId = (int)$db->lastInsertId();
        foreach ($role['permissions'] as $perm) {
            $insPerm->execute([':r' => $roleId, ':p' => $perm]);
        }
        step("tao vai tro '$slug' (#$roleId, " . count($role['permissions']) . " quyen)");
    } else {
        step("vai tro '$slug' da co (#$roleId) - giu nguyen quyen hien tai");
    }
    $roleIds[$slug] = $roleId;
}

echo "== 4. Gan vai tro cho tai khoan hien co ==\n";
// Tai khoan chua co role_id: anh xa theo cot role cu, khong ro thi cho superadmin
// de khong ai bi khoa ra khoi chinh site cua minh.
$assign = $db->prepare("UPDATE `admins` SET `role_id` = :r, `role` = :n WHERE `id` = :id");
$rows = $db->query("SELECT `id`, `username`, `role`, `role_id` FROM `admins`")->fetchAll();
foreach ($rows as $row) {
    if (!empty($row['role_id'])) {
        step("#{$row['id']} {$row['username']}: da co vai tro, bo qua");
        continue;
    }
    $slug = isset($roleIds[$row['role']]) ? $row['role'] : 'superadmin';
    $assign->execute([':r' => $roleIds[$slug], ':n' => $slug, ':id' => $row['id']]);
    step("#{$row['id']} {$row['username']} -> $slug");
}

echo "== 5. Tong ket ==\n";
foreach ($db->query("SELECT r.`name`, r.`label`, r.`is_system`,
        (SELECT COUNT(*) FROM `admin_role_permissions` p WHERE p.`role_id` = r.`id`) AS perms,
        (SELECT COUNT(*) FROM `admins` a WHERE a.`role_id` = r.`id`) AS users
        FROM `admin_roles` r ORDER BY r.`is_system` DESC, r.`label`") as $r) {
    $perms = $r['is_system'] ? 'toan quyen' : $r['perms'] . ' quyen';
    step("{$r['label']} ({$r['name']}): $perms, {$r['users']} tai khoan");
}
echo "Xong.\n";
