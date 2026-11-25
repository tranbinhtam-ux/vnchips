<?php
/**
 * Admin Roles list
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../models/AdminRole.php';

$message = ($_GET['saved'] ?? '') ? 'Đã lưu vai trò thành công.' : '';
$error = '';

if (($_GET['action'] ?? '') === 'delete' && isset($_GET['id'])) {
    $result = AdminRole::delete((int)$_GET['id']);
    if ($result === true) {
        $message = 'Đã xoá vai trò.';
    } else {
        $error = (string)$result;
    }
}

$roles = AdminRole::getAll();
$totalPermissions = count(AdminRole::allPermissions());

$adminTitle = 'Vai trò & Quyền';
$adminActivePage = 'roles';

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
  <div>
    <h3 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-user-lock text-primary me-2"></i>Vai trò & Quyền
    </h3>
    <p class="text-muted small mb-0">Mỗi vai trò là một bộ quyền. Gán vai trò cho tài khoản ở trang Tài khoản.</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= admin_url('users.php') ?>" class="btn btn-outline-secondary">
      <i class="fa-solid fa-users-gear me-1"></i> Danh sách Tài khoản
    </a>
    <a href="<?= admin_url('role-edit.php') ?>" class="btn btn-primary">
      <i class="fa-solid fa-plus me-1"></i> Thêm Vai trò
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

<div class="row g-4">
  <?php foreach ($roles as $r): ?>
    <?php
      $isSystem = !empty($r['is_system']);
      $permCount = $isSystem ? $totalPermissions : count(AdminRole::permissionsOf($r['id']));
      $userCount = AdminRole::userCount($r['id']);
    ?>
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 14px;">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
          <div>
            <h5 class="fw-bold text-dark mb-1">
              <?= e($r['label']) ?>
              <?php if ($isSystem): ?>
                <span class="badge bg-danger-subtle text-danger ms-1">Hệ thống</span>
              <?php endif; ?>
            </h5>
            <code class="small text-muted"><?= e($r['name']) ?></code>
          </div>
          <div class="d-flex gap-2">
            <a href="<?= admin_url('role-edit.php?id=' . (int)$r['id']) ?>" class="btn btn-sm btn-outline-secondary">
              <i class="fa-solid fa-pen-to-square"></i>
            </a>
            <?php if (!$isSystem): ?>
              <a href="<?= admin_url('roles.php?action=delete&id=' . (int)$r['id']) ?>"
                 class="btn btn-sm btn-outline-danger"
                 onclick="return confirm('Xoá vai trò <?= e($r['label']) ?>?');">
                <i class="fa-solid fa-trash"></i>
              </a>
            <?php endif; ?>
          </div>
        </div>

        <p class="text-muted small mb-3"><?= e($r['description']) ?: '<span class="fst-italic">Chưa có mô tả</span>' ?></p>

        <div class="d-flex gap-4 mt-auto pt-2 border-top">
          <div>
            <div class="text-muted small">Số quyền</div>
            <div class="fw-bold text-dark">
              <?= $isSystem ? 'Toàn quyền' : $permCount . ' / ' . $totalPermissions ?>
            </div>
          </div>
          <div>
            <div class="text-muted small">Tài khoản đang dùng</div>
            <div class="fw-bold text-dark"><?= $userCount ?></div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="alert alert-light border mt-4 small text-muted">
  <i class="fa-solid fa-circle-info me-1 text-primary"></i>
  Vai trò <strong>Superadmin</strong> luôn có toàn bộ quyền, kể cả những quyền được thêm về sau, nên không thể xoá hay bỏ tick.
  Vai trò đang được gán cho tài khoản thì phải chuyển các tài khoản đó sang vai trò khác trước khi xoá.
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
