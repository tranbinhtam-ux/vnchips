<?php
/**
 * AJAX Handler for Article actions: reorder, toggle_pinned, toggle_featured, toggle_status, duplicate
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../../models/News.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'reorder') {
    $order = $_POST['order'] ?? [];
    if (!is_array($order) && is_string($order)) {
        $order = json_decode($order, true);
    }
    if (is_array($order)) {
        $success = News::updateOrder($order);
        echo json_encode(['success' => (bool)$success]);
        exit;
    }
    echo json_encode(['success' => false, 'error' => 'Invalid order data']);
    exit;
}

if ($action === 'toggle_pinned') {
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    if ($id > 0) {
        $success = News::togglePinned($id);
        $article = News::getById($id);
        echo json_encode([
            'success' => (bool)$success,
            'is_pinned' => (int)($article['is_pinned'] ?? 0),
            'message' => 'Pin status updated'
        ]);
        exit;
    }
}

if ($action === 'toggle_featured') {
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    if ($id > 0) {
        $success = News::toggleFeatured($id);
        $article = News::getById($id);
        echo json_encode([
            'success' => (bool)$success,
            'is_featured' => (int)($article['is_featured'] ?? 0),
            'message' => 'Featured status updated'
        ]);
        exit;
    }
}

if ($action === 'toggle_status') {
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    if ($id > 0) {
        $success = News::toggleStatus($id);
        $article = News::getById($id);
        echo json_encode([
            'success' => (bool)$success,
            'status' => $article['status'] ?? 'published',
            'message' => 'Article status changed to ' . ($article['status'] ?? '')
        ]);
        exit;
    }
}

if ($action === 'duplicate') {
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    if ($id > 0) {
        $newId = News::duplicate($id);
        if ($newId) {
            echo json_encode(['success' => true, 'new_id' => $newId, 'message' => 'Article duplicated as draft']);
            exit;
        }
    }
    echo json_encode(['success' => false, 'error' => 'Failed to duplicate article']);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
exit;
