<?php
/**
 * Admin Authentication Guard
 */

if (!defined('VNCHIP_APP')) {
    require_once __DIR__ . '/../../config/config.php';
}

require_once __DIR__ . '/../../models/Admin.php';
require_once __DIR__ . '/../../models/AdminRole.php';

// Enforce authentication
Admin::requireAuth();

/**
 * Enforce the permission this page needs.
 *
 * A page may set $adminRequiredPermission before including this file; otherwise the
 * permission is looked up from the script name in AdminRole::pageMap(). Scripts in
 * neither place stay open to any signed-in admin, so a new page is never unreachable
 * before its permission is declared.
 */
$adminScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
$adminIsAjax = strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/ajax/') !== false;

if (isset($adminRequiredPermission)) {
    $requiredPermission = $adminRequiredPermission;
} elseif ($adminIsAjax) {
    $requiredPermission = AdminRole::permissionForAjax(
        $adminScript,
        $_POST['action'] ?? $_GET['action'] ?? ''
    );
} else {
    $requiredPermission = AdminRole::permissionForPage($adminScript);
}

if ($requiredPermission && !Admin::can($requiredPermission)) {
    if ($adminIsAjax) {
        // Endpoints answer JSON; an HTML error page here would just break the caller
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Tài khoản của bạn không có quyền "' . AdminRole::labelOf($requiredPermission) . '".',
        ]);
        exit;
    }
    Admin::requirePermission($requiredPermission);
}

$currentAdmin = Admin::current();
