<?php
/**
 * AJAX Handler for Members, Partners, and Inquiries
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../../models/Member.php';
require_once __DIR__ . '/../../models/Partner.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db = Database::getInstance()->getConnection();

if ($action === 'reorder_members') {
    $order = $_POST['order'] ?? [];
    if (!is_array($order) && is_string($order)) {
        $order = json_decode($order, true);
    }
    if (is_array($order)) {
        $success = Member::updateOrder($order);
        echo json_encode(['success' => (bool)$success]);
        exit;
    }
}

if ($action === 'toggle_member_active') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $success = Member::toggleActive($id);
        $m = Member::getById($id);
        echo json_encode([
            'success' => (bool)$success,
            'is_active' => (int)($m['is_active'] ?? 0),
            'message' => 'Member visibility updated'
        ]);
        exit;
    }
}

if ($action === 'reorder_partners') {
    $order = $_POST['order'] ?? [];
    if (!is_array($order) && is_string($order)) {
        $order = json_decode($order, true);
    }
    if (is_array($order)) {
        $success = Partner::updateOrder($order);
        echo json_encode(['success' => (bool)$success]);
        exit;
    }
}

if ($action === 'toggle_partner_pinned') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $success = Partner::togglePinned($id);
        $p = Partner::getById($id);
        echo json_encode([
            'success' => (bool)$success,
            'is_pinned' => (int)($p['is_pinned'] ?? 0),
            'message' => 'Partner pin updated'
        ]);
        exit;
    }
}

if ($action === 'toggle_partner_status') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $success = Partner::toggleStatus($id);
        $p = Partner::getById($id);
        echo json_encode([
            'success' => (bool)$success,
            'status' => $p['status'] ?? 'published',
            'message' => 'Partner status changed to ' . ($p['status'] ?? '')
        ]);
        exit;
    }
}

if ($action === 'get_inquiry_detail') {
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    if ($id > 0 && $db) {
        $stmt = $db->prepare("SELECT * FROM `inquiries` WHERE `id` = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $inq = $stmt->fetch();
        if ($inq) {
            echo json_encode(['success' => true, 'inquiry' => $inq]);
            exit;
        }
    }
    echo json_encode(['success' => false, 'error' => 'Inquiry not found']);
    exit;
}

if ($action === 'update_inquiry_status') {
    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? 'responded';
    if ($id > 0 && $db) {
        $stmt = $db->prepare("UPDATE `inquiries` SET `status` = :st WHERE `id` = :id");
        $success = $stmt->execute([':st' => $status, ':id' => $id]);
        echo json_encode(['success' => (bool)$success, 'status' => $status, 'message' => 'Status updated to ' . $status]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
exit;
