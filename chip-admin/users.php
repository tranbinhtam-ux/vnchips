<?php
/**
 * Admin Users Management
 * Lists every back-office account, its role and status.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../models/AdminRole.php';

$message = $_GET['saved'] ?? '' ? 'Đã lưu tài khoản thành công.' : '';
$error = '';

// Toggle active / delete
if (isset($_GET['action'], $_GET['id'])) {
    $targetId = (int)$_GET['id'];

    if ($_GET['action'] === 'delete') {
        $result = Admin::deleteUser($targetId);
        if ($result === true) {
            $message = 'Đã xoá tài khoản.';
        } else {
            $error = (string)$result;
        }
    } elseif ($_GET['action'] === 'toggle') {
        $user = Admin::getUserById($targetId);
        if (!$user) {
            $error = 'Tài khoản không tồn tại.';
        } elseif ((int)$user['id'] === (int)$currentAdmin['id']) {
            $error = 'Bạn không thể tự khoá tài khoản của chính mình.';
        } else {
            $result = Admin::saveUser($targetId, [
                'username' => $user['username'],
                'fullname' => $user['fullname'],
                'email' => $user['email'],
                'role_id' => $user['role_id'],
                'is_active' => (int)$user['is_active'] === 1 ? 0 : 1,
            ]);
            if (is_string($result)) {
                $error = $result;
            } else {
                $message = (int)$user['is_active'] === 1 ? 'Đã khoá tài khoản.' : 'Đã mở khoá tài khoản.';
            }
        }
    }
}

$users = Admin::getAllUsers();
$roles = AdminRole::getAll();

$activeCount = count(array_filter($users, fn($u) => (int)($u['is_active'] ?? 1) === 1));
$lockedCount = count($users) - $activeCount;

$adminTitle = 'Tài khoản & Phân quyền';
$adminActivePage = 'users';

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
  <div>
    <h3 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-users-gear text-primary me-2"></i>Tài khoản quản trị
    </h3>
    <p class="text-muted small mb-0">Quản lý người được vào trang quản trị và mức quyền của từng người</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= admin_url('roles.php') ?>" class="btn btn-outline-secondary">
      <i class="fa-solid fa-user-lock me-1"></i> Quản lý Vai trò
    </a>
    <a href="<?= admin_url('user-edit.php') ?>" class="btn btn-primary">
      <i class="fa-solid fa-user-plus me-1"></i> Thêm Tài khoản
    </a>
  </div>
</div>

<?php if ($message): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fa-solid fa-circle-check me-2"></i><?= e($message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fa-solid fa-circle-exclamation me-2"></i><?= e($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm p-3" style="border-radius: 12px;">
      <div class="text-muted small fw-semibold">Tổng tài khoản</div>
      <div class="fs-4 fw-bold text-dark"><?= count($users) ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm p-3" style="border-radius: 12px;">
      <div class="text-muted small fw-semibold">Đang hoạt động</div>
      <div class="fs-4 fw-bold text-success"><?= $activeCount ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm p-3" style="border-radius: 12px;">
      <div class="text-muted small fw-semibold">Đang khoá</div>
      <div class="fs-4 fw-bold text-danger"><?= $lockedCount ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm p-3" style="border-radius: 12px;">
      <div class="text-muted small fw-semibold">Vai trò</div>
      <div class="fs-4 fw-bold text-dark"><?= count($roles) ?></div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 14px; overflow: hidden;">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-4">Người dùng</th>
          <th>Vai trò</th>
          <th>Trạng thái</th>
          <th>Đăng nhập gần nhất</th>
          <th class="text-end pe-4">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$users): ?>
          <tr><td colspan="5" class="text-center text-muted py-4">Chưa có tài khoản nào.</td></tr>
        <?php endif; ?>

        <?php foreach ($users as $u): ?>
          <?php
            $isMe = (int)$u['id'] === (int)$currentAdmin['id'];
            $isOn = (int)($u['is_active'] ?? 1) === 1;
            $roleLabel = $u['role_label'] ?: ($u['role'] ?: '—');
          ?>
          <tr>
            <td class="ps-4">
              <div class="fw-bold text-dark">
                <?= e($u['fullname']) ?>
                <?php if ($isMe): ?>
                  <span class="badge bg-primary-subtle text-primary ms-1">Bạn</span>
                <?php endif; ?>
              </div>
              <div class="small text-muted">
                @<?= e($u['username']) ?><?= $u['email'] ? ' · ' . e($u['email']) : '' ?>
              </div>
            </td>
            <td>
              <span class="badge <?= !empty($u['role_is_system']) ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary' ?>">
                <?= e($roleLabel) ?>
              </span>
            </td>
            <td>
              <?php if ($isOn): ?>
                <span class="text-success fw-semibold small"><i class="fa-solid fa-circle-check me-1"></i>Hoạt động</span>
              <?php else: ?>
                <span class="text-danger fw-semibold small"><i class="fa-solid fa-lock me-1"></i>Đã khoá</span>
              <?php endif; ?>
            </td>
            <td class="small text-muted">
              <?= !empty($u['last_login']) ? e(formatDate($u['last_login'], 'd/m/Y H:i')) : 'Chưa đăng nhập' ?>
            </td>
            <td class="text-end pe-4">
              <a href="<?= admin_url('user-edit.php?id=' . (int)$u['id']) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-pen-to-square"></i>
              </a>
              <?php if (!$isMe): ?>
                <a href="<?= admin_url('users.php?action=toggle&id=' . (int)$u['id']) ?>"
                   class="btn btn-sm <?= $isOn ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                   title="<?= $isOn ? 'Khoá tài khoản' : 'Mở khoá tài khoản' ?>"
                   onclick="return confirm('<?= $isOn ? 'Khoá' : 'Mở khoá' ?> tài khoản <?= e($u['username']) ?>?');">
                  <i class="fa-solid <?= $isOn ? 'fa-lock' : 'fa-lock-open' ?>"></i>
                </a>
                <a href="<?= admin_url('users.php?action=delete&id=' . (int)$u['id']) ?>"
                   class="btn btn-sm btn-outline-danger" title="Xoá tài khoản"
                   onclick="return confirm('Xoá vĩnh viễn tài khoản <?= e($u['username']) ?>? Thao tác này không hoàn tác được.');">
                  <i class="fa-solid fa-trash"></i>
                </a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="alert alert-light border mt-4 small text-muted">
  <i class="fa-solid fa-circle-info me-1 text-primary"></i>
  Tài khoản bị khoá không đăng nhập được, và nếu đang đăng nhập thì bị đẩy ra ở thao tác kế tiếp.
  Hệ thống luôn giữ lại ít nhất một tài khoản <strong>Superadmin</strong> đang hoạt động.
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
