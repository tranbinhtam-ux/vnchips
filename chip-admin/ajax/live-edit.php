<?php
/**
 * AJAX Handler for Live Edit on Frontend & Quick Section Updates
 * Protected by Admin Authentication
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Admin.php';
require_once __DIR__ . '/../../models/Setting.php';

// Check if user is logged in as Admin
if (!Admin::check()) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Bạn chưa đăng nhập quản trị viên hoặc phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.'
    ]);
    exit;
}

// Hiding the Live Edit bar is not enough: this endpoint writes settings, so it has
// to check the permission itself rather than trust that the bar was never rendered.
if (!Admin::can('live_edit.use')) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Tài khoản của bạn không có quyền sửa nội dung trực tiếp trên giao diện (Live Edit).'
    ]);
    exit;
}

$action = $_POST['action'] ?? '';

// 1. Save single setting
if ($action === 'save_setting') {
    $key = trim($_POST['key'] ?? '');
    $val = $_POST['value'] ?? '';
    
    if (empty($key)) {
        echo json_encode(['success' => false, 'error' => 'Thiếu khóa cài đặt (key).']);
        exit;
    }
    
    $group = 'homepage';
    if (strpos($key, 'section_') === 0) {
        $group = 'content_sections';
    } elseif (strpos($key, 'hero_') === 0 || strpos($key, 'home_hero_') === 0) {
        $group = 'banners';
    }
    
    $ok = Setting::set($key, $val, $group);
    echo json_encode([
        'success' => (bool)$ok,
        'message' => 'Cập nhật thành công!',
        'key' => $key,
        'value' => $val
    ]);
    exit;
}

// 2. Save batch of settings (e.g. 3 Golden Stats)
if ($action === 'save_batch') {
    $data = $_POST['data'] ?? [];
    if (!is_array($data) && is_string($data)) {
        $data = json_decode($data, true);
    }
    
    if (is_array($data) && !empty($data)) {
        $group = $_POST['group'] ?? 'homepage';
        $ok = Setting::updateBatch($data, $group);
        echo json_encode([
            'success' => (bool)$ok,
            'message' => 'Đã lưu tất cả thay đổi thành công!',
            'data' => $data
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => 'Dữ liệu cập nhật không hợp lệ.']);
        exit;
    }
}

// 3. Reset a section to default
if ($action === 'reset_section') {
    $key = trim($_POST['key'] ?? '');
    if (!empty($key)) {
        Setting::delete($key);
        echo json_encode(['success' => true, 'message' => 'Đã khôi phục về mẫu mặc định!']);
        exit;
    }
}

// 4. Upload and replace image for Live Edit
if ($action === 'upload_image') {
    require_once __DIR__ . '/../includes/upload.php';
    
    $file = $_FILES['image'] ?? null;
    if (!$file || empty($file['name'])) {
        echo json_encode(['success' => false, 'error' => 'Vui lòng chọn file ảnh hợp lệ để tải lên.']);
        exit;
    }
    
    $subdir = trim($_POST['subdir'] ?? 'branding');
    $prefix = trim($_POST['prefix'] ?? 'footer');
    
    $res = adminStoreUpload($file, $subdir, $prefix);
    if (isset($res['error'])) {
        echo json_encode(['success' => false, 'error' => $res['error']]);
        exit;
    }
    
    $settingKey = trim($_POST['setting_key'] ?? '');
    if (!empty($settingKey)) {
        Setting::set($settingKey, $res['path'], 'branding');
    }
    
    echo json_encode([
        'success' => true,
        'path' => $res['path'],
        'full_url' => asset($res['path']),
        'name' => $res['name'],
        'size' => $res['size'],
        'setting_key' => $settingKey,
        'message' => 'Tải ảnh lên thành công!'
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Hành động không hợp lệ (Unknown action).']);
exit;
