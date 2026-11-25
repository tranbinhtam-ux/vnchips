<?php
/**
 * Admin Create / Edit User Account
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../models/AdminRole.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user = $id ? Admin::getUserById($id) : null;

if ($id && !$user) {
    header("Location: " . admin_url('users.php'));
    exit;
}

$user = $user ?? ['username' => '', 'fullname' => '', 'email' => '', 'role_id' => null, 'is_active' => 1, 'last_login' => null];
$roles = AdminRole::getAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username'  => $_POST['username'] ?? '',
        'fullname'  => $_POST['fullname'] ?? '',
        'email'     => $_POST['email'] ?? '',
        'password'  => $_POST['password'] ?? '',
        'role_id'   => (int)($_POST['role_id'] ?? 0),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
    ];

    if (($_POST['password'] ?? '') !== ($_POST['password_confirm'] ?? '')) {
        $error = 'Hai ô mật khẩu không khớp nhau.';
    } else {
        $result = Admin::saveUser($id ?: null, $data);
        if (is_string($result)) {
            $error = $result;
        } else {
            header("Location: " . admin_url('users.php?saved=1'));
            exit;
        }
    }

    $user = array_merge($user, $data);
}

$adminTitle = $id ? 'Sửa tài khoản #' . $id : 'Thêm tài khoản';
$adminActivePage = 'users';

require_once __DIR__ . '/includes/header.php';
?>

<form action="<?= admin_url('user-edit.php' . ($id ? '?id=' . $id : '')) ?>" method="POST">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
      <div class="text-muted small mb-1">
        <a href="<?= admin_url('users.php') ?>" class="text-decoration-none text-muted">
          <i class="fa-solid fa-arrow-left me-1"></i> Về danh sách tài khoản
        </a>
      </div>
      <h3 class="mb-0 fw-bold text-dark">
        <i class="fa-solid <?= $id ? 'fa-user-pen' : 'fa-user-plus' ?> text-primary me-2"></i>
        <?= $id ? 'Sửa tài khoản: ' . e($user['username']) : 'Thêm tài khoản quản trị' ?>
      </h3>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= admin_url('users.php') ?>" class="btn btn-outline-secondary">Huỷ bỏ</a>
      <button type="submit" class="btn btn-primary px-3">
        <i class="fa-solid fa-check me-1"></i> <?= $id ? 'Cập nhật' : 'Tạo tài khoản' ?>
      </button>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fa-solid fa-circle-exclamation me-2"></i><?= e($error) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-12 col-lg-8">
      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-id-card text-primary me-2"></i>Thông tin tài khoản</h5>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Họ và tên <span class="text-danger">*</span></label>
          <input type="text" name="fullname" class="form-control" required
                 value="<?= e($user['fullname']) ?>" placeholder="VD: Nguyễn Văn A">
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">Tên đăng nhập <span class="text-danger">*</span></label>
            <input type="text" name="username" class="form-control" required
                   value="<?= e($user['username']) ?>" placeholder="vd: nguyenvana" autocomplete="off">
            <div class="form-text small">Chữ thường, số, dấu <code>. - _</code> — từ 3 đến 50 ký tự.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?= e($user['email']) ?>" placeholder="name@vnchip-inc.com">
          </div>
        </div>

        <hr class="my-4">

        <h6 class="fw-bold text-dark mb-3">
          <i class="fa-solid fa-key text-warning me-2"></i>
          <?= $id ? 'Đổi mật khẩu' : 'Mật khẩu' ?>
        </h6>
        <?php if ($id): ?>
          <div class="alert alert-light border small text-muted py-2">
            Để trống hai ô dưới nếu không muốn đổi mật khẩu của tài khoản này.
          </div>
        <?php endif; ?>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">
              Mật khẩu <?= $id ? 'mới' : '' ?> <?= $id ? '' : '<span class="text-danger">*</span>' ?>
            </label>
            <input type="password" name="password" class="form-control" autocomplete="new-password"
                   <?= $id ? '' : 'required' ?> placeholder="Tối thiểu 8 ký tự">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">Nhập lại mật khẩu</label>
            <input type="password" name="password_confirm" class="form-control" autocomplete="new-password"
                   <?= $id ? '' : 'required' ?> placeholder="Nhập lại cho khớp">
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-4">
      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-user-lock text-primary me-2"></i>Vai trò & Trạng thái</h5>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Vai trò <span class="text-danger">*</span></label>
          <select name="role_id" class="form-select" required>
            <option value="">— Chọn vai trò —</option>
            <?php foreach ($roles as $r): ?>
              <option value="<?= (int)$r['id'] ?>" <?= ((int)$user['role_id'] === (int)$r['id']) ? 'selected' : '' ?>>
                <?= e($r['label']) ?><?= !empty($r['is_system']) ? ' (toàn quyền)' : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="form-text small">
            Xem và sửa quyền của từng vai trò ở <a href="<?= admin_url('roles.php') ?>">trang Vai trò</a>.
          </div>
        </div>

        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                 <?= (int)$user['is_active'] === 1 ? 'checked' : '' ?>>
          <label class="form-check-label fw-bold text-dark" for="is_active">
            <i class="fa-solid fa-circle-check text-success me-1"></i> Cho phép đăng nhập
          </label>
        </div>
        <div class="form-text small mb-3">
          Bỏ chọn để khoá tài khoản. Người bị khoá không đăng nhập được, và nếu đang đăng nhập sẽ bị đẩy ra ở thao tác kế tiếp.
        </div>

        <?php if ($id): ?>
          <div class="border-top pt-3 small text-muted">
            <div>Đăng nhập gần nhất:</div>
            <div class="fw-semibold text-dark">
              <?= !empty($user['last_login']) ? e(formatDate($user['last_login'], 'd/m/Y H:i')) : 'Chưa đăng nhập lần nào' ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="d-grid mt-3">
          <button type="submit" class="btn btn-primary fw-bold py-2">
            <i class="fa-solid fa-floppy-disk me-1"></i> <?= $id ? 'Lưu thay đổi' : 'Tạo tài khoản' ?>
          </button>
        </div>
      </div>
    </div>
  </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
