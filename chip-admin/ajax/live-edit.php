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

// ---------------------------------------------------------------------------
// 5. Design Service / IP Service items + keyword tooltips, edited straight on the page
// ---------------------------------------------------------------------------
require_once __DIR__ . '/../../models/ServiceItem.php';
require_once __DIR__ . '/../../models/ServiceTooltip.php';

/** Body as the public page shows it (markers expanded), for an in-place DOM refresh */
function liveServiceItemPayload(array $item) {
    return [
        'id'        => (int)$item['id'],
        'title'     => $item['title'],
        'image_url' => $item['image_url'],
        'image_src' => $item['image_url'] ? asset($item['image_url']) : '',
        'body'      => $item['body'],
        'body_html' => ServiceTooltip::expand($item['body']),
        'keywords'  => ServiceTooltip::keywordsIn($item['body']),
        'dictionary'=> array_map(static function ($row) {
            return ['id' => (int)$row['id'], 'keyword' => $row['keyword'], 'title' => $row['title']];
        }, ServiceTooltip::getActive()),
    ];
}

// 5a. Load one accordion item for the editor
if ($action === 'get_service_item') {
    $item = ServiceItem::getById((int)($_POST['id'] ?? 0));
    if (!$item) {
        echo json_encode(['success' => false, 'error' => 'Không tìm thấy mục dịch vụ.']);
        exit;
    }
    echo json_encode(['success' => true, 'item' => liveServiceItemPayload($item)]);
    exit;
}

// 5b. Save title / image / body of one item (other columns are kept as they are)
if ($action === 'save_service_item') {
    $item = ServiceItem::getById((int)($_POST['id'] ?? 0));
    if (!$item) {
        echo json_encode(['success' => false, 'error' => 'Không tìm thấy mục dịch vụ.']);
        exit;
    }
    $title = trim((string)($_POST['title'] ?? ''));
    if ($title === '') {
        echo json_encode(['success' => false, 'error' => 'Tên mục không được để trống.']);
        exit;
    }
    $data = $item;
    $data['title']     = $title;
    $data['body']      = (string)($_POST['body'] ?? '');
    $data['image_url'] = trim((string)($_POST['image_url'] ?? $item['image_url']));

    if (!ServiceItem::save((int)$item['id'], $data)) {
        echo json_encode(['success' => false, 'error' => 'Lỗi cơ sở dữ liệu khi lưu mục dịch vụ.']);
        exit;
    }
    $saved = ServiceItem::getById((int)$item['id']);
    echo json_encode(['success' => true, 'message' => 'Đã lưu mục dịch vụ!', 'item' => liveServiceItemPayload($saved)]);
    exit;
}

// 5c. Load one tooltip by id or by keyword (a keyword with no entry yet comes back empty)
if ($action === 'get_tooltip') {
    $id = (int)($_POST['id'] ?? 0);
    $keyword = trim((string)($_POST['keyword'] ?? ''));
    $tip = $id ? ServiceTooltip::getById($id) : ($keyword !== '' ? ServiceTooltip::findByKeyword($keyword) : null);
    if ($tip) {
        echo json_encode(['success' => true, 'exists' => true, 'tooltip' => [
            'id' => (int)$tip['id'], 'keyword' => $tip['keyword'], 'title' => $tip['title'],
            'content' => $tip['content'], 'is_active' => (int)$tip['is_active'],
        ]]);
    } else {
        echo json_encode(['success' => true, 'exists' => false, 'tooltip' => [
            'id' => 0, 'keyword' => $keyword, 'title' => '', 'content' => '', 'is_active' => 1,
        ]]);
    }
    exit;
}

// 5d. Create or update a tooltip; returns the panel HTML so the page can refresh in place
if ($action === 'save_tooltip') {
    $id = (int)($_POST['id'] ?? 0);
    $data = [
        'keyword'   => trim((string)($_POST['keyword'] ?? '')),
        'title'     => trim((string)($_POST['title'] ?? '')),
        'content'   => trim((string)($_POST['content'] ?? '')),
        'is_active' => 1,
    ];
    if ($data['keyword'] === '') {
        echo json_encode(['success' => false, 'error' => 'Từ khoá không được để trống.']);
        exit;
    }
    // A keyword typed for the first time may already exist under another case: reuse that row
    if (!$id) {
        $existing = ServiceTooltip::findByKeyword($data['keyword']);
        if ($existing) $id = (int)$existing['id'];
    }
    // Editing never flips the admin's on/off switch for an existing entry
    if ($id && ($current = ServiceTooltip::getById($id))) {
        $data['is_active'] = (int)$current['is_active'];
    }
    $savedId = ServiceTooltip::save($id ?: null, $data);
    if (!$savedId) {
        echo json_encode(['success' => false, 'error' => 'Không lưu được tooltip. Từ khoá có thể đã tồn tại.']);
        exit;
    }
    $tip = ServiceTooltip::getById($savedId);
    echo json_encode([
        'success' => true,
        'message' => 'Đã lưu tooltip!',
        'tooltip' => [
            'id' => (int)$tip['id'], 'keyword' => $tip['keyword'], 'title' => $tip['title'],
            'content' => $tip['content'], 'note_html' => ServiceTooltip::renderNote($tip),
        ],
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Hành động không hợp lệ (Unknown action).']);
exit;
