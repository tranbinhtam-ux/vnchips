<?php
/**
 * Admin Security Profile & Credentials Management
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../models/Admin.php';

$currentAdmin = Admin::current();
$adminId = $currentAdmin['id'] ?? 1;

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($fullname) || empty($email)) {
            $error = 'Full name and email address are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            if (Admin::updateProfile($adminId, $fullname, $email)) {
                $message = 'Profile details updated successfully!';
                $_SESSION['admin_user']['fullname'] = $fullname;
                $_SESSION['admin_user']['email'] = $email;
                $currentAdmin = Admin::current();
            } else {
                $error = 'Failed to update profile details.';
            }
        }
    }

    if ($action === 'change_password') {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (empty($currentPass) || empty($newPass)) {
            $error = 'Current password and new password are required.';
        } elseif (strlen($newPass) < 6) {
            $error = 'New password must be at least 6 characters long.';
        } elseif ($newPass !== $confirmPass) {
            $error = 'New password and confirmation password do not match.';
        } else {
            $res = Admin::updatePassword($adminId, $currentPass, $newPass);
            if ($res['success']) {
                $message = 'Password changed successfully! Please use your new password next time you log in.';
            } else {
                $error = $res['error'] ?? 'Incorrect current password.';
            }
        }
    }
}

$adminTitle = 'Admin Profile & Security';
$adminActivePage = 'profile';

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-panel mb-4">
  <div class="panel-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <h3 class="panel-title mb-1">
        <i class="fa-solid fa-user-shield text-primary me-2"></i>Admin Security & Profile
      </h3>
      <p class="text-muted small mb-0">Manage administrator account credentials, authentication security, and session preferences</p>
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
    <!-- Left Column: Profile Info & Update -->
    <div class="col-12 col-lg-6">
      
      <!-- Account Badge Card -->
      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <div class="d-flex align-items-center gap-3 mb-4">
          <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" 
               style="width: 64px; height: 64px; background: linear-gradient(135deg, #050C17, #F25B22); font-size: 1.5rem;">
            <?= strtoupper(substr($currentAdmin['fullname'] ?? 'A', 0, 1)) ?>
          </div>
          <div>
            <h4 class="fw-bold text-dark mb-0"><?= e($currentAdmin['fullname'] ?? 'Administrator') ?></h4>
            <div class="d-flex align-items-center gap-2 mt-1">
              <span class="badge bg-primary-subtle text-primary text-uppercase"><?= e($currentAdmin['role'] ?? 'super_admin') ?></span>
              <span class="small text-muted"><i class="fa-solid fa-user me-1"></i><?= e($currentAdmin['username'] ?? 'admin') ?></span>
            </div>
          </div>
        </div>

        <form action="<?= admin_url('profile.php') ?>" method="POST">
          <input type="hidden" name="action" value="update_profile">

          <div class="mb-3">
            <label class="form-label small fw-semibold text-muted">Username (System Identifier)</label>
            <input type="text" class="form-control" value="<?= e($currentAdmin['username'] ?? 'admin') ?>" disabled style="background: #f1f5f9;">
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold text-muted">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="fullname" class="form-control" required value="<?= e($currentAdmin['fullname'] ?? '') ?>">
          </div>

          <div class="mb-4">
            <label class="form-label small fw-semibold text-muted">Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" required value="<?= e($currentAdmin['email'] ?? '') ?>">
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
            <i class="fa-solid fa-floppy-disk me-1"></i> Save Profile Details
          </button>
        </form>
      </div>

      <!-- Current Session Info Card -->
      <div class="card border-0 shadow-sm p-4" style="border-radius: 14px; background: #f8fafc;">
        <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-network-wired text-primary me-2"></i>Active Session Security</h6>
        <div class="row g-2 small text-muted">
          <div class="col-6">IP Address: <strong class="text-dark"><?= e($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') ?></strong></div>
          <div class="col-6">Protocol: <strong class="text-dark"><?= (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'HTTPS / TLS' : 'HTTP' ?></strong></div>
          <div class="col-12 mt-2">Browser: <span class="text-truncate d-inline-block" style="max-width: 95%;"><?= e($_SERVER['HTTP_USER_AGENT'] ?? 'Web Browser') ?></span></div>
        </div>
      </div>

    </div>

    <!-- Right Column: Password Change -->
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 14px;">
        <h5 class="fw-bold text-dark mb-3">
          <i class="fa-solid fa-key text-warning me-2"></i>Change Administrator Password
        </h5>
        <p class="text-muted small mb-4">
          To maintain database safety and security, ensure your password uses a combination of upper/lowercase letters, numbers, and symbols.
        </p>

        <form action="<?= admin_url('profile.php') ?>" method="POST">
          <input type="hidden" name="action" value="change_password">

          <div class="mb-3">
            <label class="form-label small fw-semibold text-muted">Current Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" name="current_password" id="current_password" class="form-control" required placeholder="Enter current password">
              <button class="btn btn-outline-secondary" type="button" onclick="togglePass('current_password')">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold text-muted">New Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" name="new_password" id="new_password" class="form-control" required placeholder="At least 6 characters">
              <button class="btn btn-outline-secondary" type="button" onclick="togglePass('new_password')">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label small fw-semibold text-muted">Confirm New Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" name="confirm_password" id="confirm_password" class="form-control" required placeholder="Re-type new password">
              <button class="btn btn-outline-secondary" type="button" onclick="togglePass('confirm_password')">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </div>

          <div class="alert alert-info py-2 small mb-4">
            <i class="fa-solid fa-shield-halved me-1"></i> Current login password for user <code>admin</code> is set to <code>password</code>.
          </div>

          <button type="submit" class="btn btn-warning w-100 py-2 fw-bold text-dark">
            <i class="fa-solid fa-lock me-1"></i> Update Password
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function togglePass(id) {
  const el = document.getElementById(id);
  if (el.type === 'password') {
    el.type = 'text';
  } else {
    el.type = 'password';
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
