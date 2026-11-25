<?php
/**
 * Admin Create / Edit Role, with the permission checkbox matrix
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../models/AdminRole.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$role = $id ? AdminRole::getById($id) : null;

if ($id && !$role) {
    header("Location: " . admin_url('roles.php'));
    exit;
}

$role = $role ?? ['name' => '', 'label' => '', 'description' => '', 'is_system' => 0];
$isSystem = !empty($role['is_system']);
$granted = $id ? AdminRole::permissionsOf($id) : [];
$catalogue = AdminRole::catalogue();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $permissions = isset($_POST['permissions']) && is_array($_POST['permissions']) ? $_POST['permissions'] : [];
    $data = [
        'name' => $_POST['name'] ?? '',
        'label' => $_POST['label'] ?? '',
        'description' => $_POST['description'] ?? '',
    ];

    $result = AdminRole::save($id ?: null, $data, $permissions);
    if ($result === false) {
        $error = 'Không lưu được. Kiểm tra lại Tên vai trò và mã định danh (mã có thể đã trùng với vai trò khác).';
        $role = array_merge($role, $data);
        $granted = $permissions;
    } else {
        header("Location: " . admin_url('roles.php?saved=1'));
        exit;
    }
}

$adminTitle = $id ? 'Sửa vai trò' : 'Thêm vai trò';
$adminActivePage = 'roles';

require_once __DIR__ . '/includes/header.php';
?>

<form action="<?= admin_url('role-edit.php' . ($id ? '?id=' . $id : '')) ?>" method="POST">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
      <div class="text-muted small mb-1">
        <a href="<?= admin_url('roles.php') ?>" class="text-decoration-none text-muted">
          <i class="fa-solid fa-arrow-left me-1"></i> Về danh sách vai trò
        </a>
      </div>
      <h3 class="mb-0 fw-bold text-dark">
        <i class="fa-solid fa-user-lock text-primary me-2"></i>
        <?= $id ? 'Sửa vai trò: ' . e($role['label']) : 'Thêm vai trò mới' ?>
      </h3>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= admin_url('roles.php') ?>" class="btn btn-outline-secondary">Huỷ bỏ</a>
      <button type="submit" class="btn btn-primary px-3">
        <i class="fa-solid fa-check me-1"></i> <?= $id ? 'Cập nhật vai trò' : 'Tạo vai trò' ?>
      </button>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fa-solid fa-circle-exclamation me-2"></i><?= e($error) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if ($isSystem): ?>
    <div class="alert alert-warning">
      <i class="fa-solid fa-shield-halved me-2"></i>
      Đây là vai trò hệ thống <strong>Superadmin</strong>: luôn có toàn bộ quyền, kể cả quyền thêm về sau.
      Bạn chỉ đổi được tên hiển thị và mô tả.
    </div>
  <?php endif; ?>

  <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
    <div class="row g-3">
      <div class="col-md-5">
        <label class="form-label small fw-semibold text-muted">Tên vai trò <span class="text-danger">*</span></label>
        <input type="text" name="label" class="form-control fw-bold" required
               value="<?= e($role['label']) ?>" placeholder="VD: Biên tập nội dung">
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted">Mã định danh</label>
        <input type="text" name="name" class="form-control" value="<?= e($role['name']) ?>"
               placeholder="tu-dong-tao" <?= $isSystem ? 'readonly' : '' ?>>
        <div class="form-text small">Để trống sẽ tự tạo từ tên.</div>
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold text-muted">Mô tả ngắn</label>
        <input type="text" name="description" class="form-control"
               value="<?= e($role['description']) ?>" placeholder="Vai trò này dùng cho ai?">
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm p-4" style="border-radius: 14px;">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <h5 class="fw-bold text-dark mb-0">
        <i class="fa-solid fa-list-check text-primary me-2"></i>Quyền truy cập
      </h5>
      <?php if (!$isSystem): ?>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCheckAll">Chọn tất cả</button>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="btnUncheckAll">Bỏ chọn tất cả</button>
        </div>
      <?php endif; ?>
    </div>

    <div class="row g-4">
      <?php foreach ($catalogue as $groupName => $perms): ?>
        <div class="col-12 col-lg-6">
          <div class="border rounded-3 p-3 h-100" style="background: #F8FAFC;">
            <div class="fw-bold text-dark small text-uppercase mb-3" style="letter-spacing: 0.4px;">
              <?= e($groupName) ?>
            </div>

            <?php foreach ($perms as $key => $meta): ?>
              <?php $checked = $isSystem || in_array($key, $granted, true); ?>
              <div class="form-check mb-3">
                <input class="form-check-input perm-box" type="checkbox" name="permissions[]"
                       value="<?= e($key) ?>" id="perm_<?= e(str_replace('.', '_', $key)) ?>"
                       <?= $checked ? 'checked' : '' ?> <?= $isSystem ? 'disabled' : '' ?>>
                <label class="form-check-label" for="perm_<?= e(str_replace('.', '_', $key)) ?>">
                  <span class="fw-semibold text-dark d-block"><?= e($meta[0]) ?></span>
                  <span class="small text-muted"><?= e($meta[1]) ?></span>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="d-flex justify-content-end mt-4">
      <button type="submit" class="btn btn-primary px-4 fw-bold">
        <i class="fa-solid fa-floppy-disk me-1"></i> <?= $id ? 'Lưu thay đổi' : 'Tạo vai trò' ?>
      </button>
    </div>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const boxes = document.querySelectorAll('.perm-box:not([disabled])');
  const setAll = (state) => boxes.forEach(b => { b.checked = state; });

  const btnAll = document.getElementById('btnCheckAll');
  const btnNone = document.getElementById('btnUncheckAll');
  if (btnAll) btnAll.addEventListener('click', () => setAll(true));
  if (btnNone) btnNone.addEventListener('click', () => setAll(false));
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
