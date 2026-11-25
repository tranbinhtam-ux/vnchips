<?php
/**
 * Admin: Email & SMTP
 *
 * SMTP credentials used to send mail, the addresses notified when a visitor submits the
 * Talk To Us form, and the anti-spam switches for that form.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../models/Mailer.php';
require_once __DIR__ . '/../models/Captcha.php';

$message = '';
$error = '';
$testLog = [];

$hasStoredPassword = trim((string)Setting::get('smtp_pass', '')) !== '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_email') {
    $values = [
        'smtp_enabled'           => isset($_POST['smtp_enabled']) ? '1' : '0',
        'smtp_host'              => trim($_POST['smtp_host'] ?? ''),
        'smtp_port'              => (string)max(1, (int)($_POST['smtp_port'] ?? 587)),
        'smtp_secure'            => in_array($_POST['smtp_secure'] ?? 'tls', ['tls', 'ssl', 'none'], true) ? $_POST['smtp_secure'] : 'tls',
        'smtp_user'              => trim($_POST['smtp_user'] ?? ''),
        'smtp_from_email'        => trim($_POST['smtp_from_email'] ?? ''),
        'smtp_from_name'         => trim($_POST['smtp_from_name'] ?? ''),
        'smtp_timeout'           => (string)max(5, (int)($_POST['smtp_timeout'] ?? 15)),
        'contact_notify_emails'  => trim($_POST['contact_notify_emails'] ?? ''),
        'contact_notify_enabled' => isset($_POST['contact_notify_enabled']) ? '1' : '0',
        'contact_captcha_enabled'=> isset($_POST['contact_captcha_enabled']) ? '1' : '0',
    ];

    // An empty password field means "keep what is stored", never "erase it".
    $postedPass = (string)($_POST['smtp_pass'] ?? '');
    if ($postedPass !== '') {
        $values['smtp_pass'] = $postedPass;
    }

    if (Setting::updateBatch($values, Mailer::SETTING_GROUP)) {
        $message = 'Đã lưu cấu hình email.';
        $hasStoredPassword = $hasStoredPassword || $postedPass !== '';
    } else {
        $error = 'Không lưu được cấu hình email.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'send_test') {
    $to = trim($_POST['test_to'] ?? '');
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $error = 'Địa chỉ email thử nghiệm không hợp lệ.';
    } else {
        $body = '<div style="font-family:Arial,sans-serif;font-size:14px;color:#1E293B;">'
              . '<h2 style="color:#F25B22;margin:0 0 10px;">Email thử nghiệm</h2>'
              . '<p>Nếu bạn đọc được email này thì cấu hình SMTP của <strong>' . e(SITE_NAME) . '</strong> đã hoạt động.</p>'
              . '<p style="color:#64748B;font-size:12px;">Gửi lúc ' . e(date('d/m/Y H:i:s')) . '.</p></div>';

        $err = null;
        if (Mailer::send($to, 'VNCHIP - Email thử nghiệm cấu hình SMTP', $body, $err)) {
            $message = 'Đã gửi email thử nghiệm tới ' . $to . '. Kiểm tra cả hộp thư Spam.';
        } else {
            $error = 'Gửi thất bại: ' . $err;
        }
        $testLog = Mailer::lastLog();
    }
}

$cfg = static function ($key) { return Mailer::config($key); };

$captchaOn = Setting::get('contact_captcha_enabled', '1') === '1';
$notifyList = Mailer::notifyRecipients();

$adminTitle = 'Email & SMTP';
$adminActivePage = 'email-settings';

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-panel mb-4">
  <div class="panel-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <h3 class="panel-title mb-1">
        <i class="fa-solid fa-envelope-circle-check text-primary me-2"></i>Email &amp; SMTP
      </h3>
      <p class="text-muted small mb-0">Máy chủ gửi mail, địa chỉ nhận thông báo liên hệ và chống spam cho biểu mẫu</p>
    </div>
    <a href="<?= url('talk-to-us.php#contact-form') ?>" target="_blank" class="btn btn-outline-secondary">
      <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Xem biểu mẫu
    </a>
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

  <?php if ($testLog): ?>
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
      <div class="card-body p-4">
        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-terminal me-2"></i>Nhật ký phiên SMTP vừa rồi</h6>
        <pre style="background:#0F172A;color:#CBD5E1;padding:14px;border-radius:10px;font-size:0.78rem;max-height:280px;overflow:auto;margin:0;"><?php
          foreach ($testLog as $line) { echo e($line) . "\n"; }
        ?></pre>
        <div class="form-text small">Mật khẩu không bao giờ được ghi vào nhật ký này.</div>
      </div>
    </div>
  <?php endif; ?>

  <form method="POST">
    <input type="hidden" name="action" value="save_email">

    <div class="row g-4">
      <!-- SMTP server -->
      <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
          <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
              <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-server text-primary me-2"></i>Máy chủ SMTP</h5>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" name="smtp_enabled" id="smtp_enabled" value="1"
                       <?= $cfg('smtp_enabled') === '1' ? 'checked' : '' ?>>
                <label class="form-check-label fw-bold text-dark" for="smtp_enabled">Dùng SMTP</label>
              </div>
            </div>

            <div class="alert alert-info small" style="border-radius: 10px;">
              <i class="fa-solid fa-circle-info me-1"></i>
              Tắt công tắc này thì hệ thống dùng hàm <code>mail()</code> của máy chủ &mdash; thường bị Gmail/Outlook cho vào Spam.
              Nên khai báo SMTP thật. Máy chủ này mở sẵn cổng <strong>587</strong> và <strong>465</strong> ra ngoài.
            </div>

            <div class="row g-3">
              <div class="col-md-8">
                <label class="form-label small fw-semibold text-muted">SMTP Host</label>
                <input type="text" name="smtp_host" class="form-control" value="<?= e($cfg('smtp_host')) ?>"
                       placeholder="VD: smtp.gmail.com">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Cổng</label>
                <input type="number" name="smtp_port" class="form-control" value="<?= e($cfg('smtp_port')) ?>" min="1">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Bảo mật</label>
                <select name="smtp_secure" class="form-select">
                  <option value="tls" <?= $cfg('smtp_secure') === 'tls' ? 'selected' : '' ?>>STARTTLS (cổng 587)</option>
                  <option value="ssl" <?= $cfg('smtp_secure') === 'ssl' ? 'selected' : '' ?>>SSL/TLS (cổng 465)</option>
                  <option value="none" <?= $cfg('smtp_secure') === 'none' ? 'selected' : '' ?>>Không mã hoá</option>
                </select>
              </div>
              <div class="col-md-8">
                <label class="form-label small fw-semibold text-muted">Tài khoản SMTP</label>
                <input type="text" name="smtp_user" class="form-control" value="<?= e($cfg('smtp_user')) ?>"
                       autocomplete="off" placeholder="thường là địa chỉ email đầy đủ">
              </div>

              <div class="col-md-8">
                <label class="form-label small fw-semibold text-muted">Mật khẩu SMTP</label>
                <input type="password" name="smtp_pass" class="form-control" value="" autocomplete="new-password"
                       placeholder="<?= $hasStoredPassword ? '•••••••• (đã lưu - để trống nếu không đổi)' : 'Nhập mật khẩu hoặc App Password' ?>">
                <div class="form-text small">Để trống thì giữ nguyên mật khẩu đang lưu. Trang này không bao giờ hiển thị lại mật khẩu.</div>
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Timeout (giây)</label>
                <input type="number" name="smtp_timeout" class="form-control" value="<?= e($cfg('smtp_timeout')) ?>" min="5">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">Gửi đi từ địa chỉ</label>
                <input type="email" name="smtp_from_email" class="form-control" value="<?= e($cfg('smtp_from_email')) ?>"
                       placeholder="no-reply@vnchips.trantam.net">
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">Tên người gửi</label>
                <input type="text" name="smtp_from_name" class="form-control" value="<?= e($cfg('smtp_from_name')) ?>"
                       placeholder="VNCHIP Technology JSC">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Notifications + anti-spam -->
      <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
          <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
              <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-bell text-primary me-2"></i>Thông báo liên hệ</h5>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" name="contact_notify_enabled" id="contact_notify_enabled" value="1"
                       <?= $cfg('contact_notify_enabled') === '1' ? 'checked' : '' ?>>
                <label class="form-check-label fw-bold text-dark" for="contact_notify_enabled">Bật</label>
              </div>
            </div>

            <label class="form-label small fw-semibold text-muted">Email nhận thông báo</label>
            <textarea name="contact_notify_emails" rows="3" class="form-control"
                      placeholder="admin@vnchip.com, sales@vnchip.com"><?= e($cfg('contact_notify_emails')) ?></textarea>
            <div class="form-text small">
              Cách nhau bằng dấu phẩy hoặc xuống dòng. Mỗi lần có người gửi biểu mẫu Talk To Us, các địa chỉ này nhận một email
              (bấm Reply là trả lời thẳng cho người gửi).
            </div>

            <?php if ($notifyList): ?>
              <div class="mt-2 d-flex flex-wrap gap-1">
                <?php foreach ($notifyList as $addr): ?>
                  <span class="badge badge-primary"><?= e($addr) ?></span>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="alert alert-warning small mt-2 mb-0" style="border-radius: 10px;">
                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                Chưa có địa chỉ nào hợp lệ &mdash; liên hệ vẫn được lưu vào mục Inquiries nhưng sẽ không có email báo.
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
          <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
              <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-shield-halved text-primary me-2"></i>Chống spam</h5>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" name="contact_captcha_enabled" id="contact_captcha_enabled" value="1"
                       <?= $captchaOn ? 'checked' : '' ?>>
                <label class="form-check-label fw-bold text-dark" for="contact_captcha_enabled">Bật captcha</label>
              </div>
            </div>

            <ul class="small text-muted mb-2" style="padding-left: 18px;">
              <li>Captcha ảnh tự sinh, 5 ký tự, có bóp méo &mdash; hiệu lực 10 phút, tối đa 6 lần nhập sai.</li>
              <li>Bẫy honeypot: ô ẩn mà người thật không thấy, bot điền vào là bị chặn.</li>
              <li>Chặn biểu mẫu gửi nhanh hơn 3 giây (ký HMAC nên không giả mạo được).</li>
            </ul>

            <div class="d-flex align-items-center gap-3 p-2 rounded-3 border bg-light">
              <img src="<?= url('captcha.php?preview=' . time()) ?>" alt="Xem thử captcha"
                   style="height: 52px; border-radius: 6px;" id="captcha-preview">
              <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-captcha-refresh">
                <i class="fa-solid fa-rotate me-1"></i> Xem mã khác
              </button>
            </div>
            <?php if (!Captcha::isAvailable()): ?>
              <div class="alert alert-danger small mt-2 mb-0">Máy chủ không có thư viện GD nên không vẽ được captcha.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
      <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
        <i class="fa-solid fa-floppy-disk me-1"></i> Lưu cấu hình email
      </button>
    </div>
  </form>

  <!-- Test send -->
  <form method="POST" class="card border-0 shadow-sm mt-4" style="border-radius: 14px;">
    <input type="hidden" name="action" value="send_test">
    <div class="card-body p-4">
      <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-paper-plane text-primary me-2"></i>Gửi thử</h5>
      <div class="row g-3 align-items-end">
        <div class="col-md-8">
          <label class="form-label small fw-semibold text-muted">Gửi email thử nghiệm tới</label>
          <input type="email" name="test_to" class="form-control" required
                 value="<?= e($notifyList[0] ?? '') ?>" placeholder="dia-chi@cua-ban.com">
        </div>
        <div class="col-md-4">
          <button type="submit" class="btn btn-outline-primary w-100">
            <i class="fa-solid fa-paper-plane me-1"></i> Gửi thử ngay
          </button>
        </div>
      </div>
      <div class="form-text small mt-2">Lưu cấu hình trước khi gửi thử. Toàn bộ hội thoại với máy chủ SMTP sẽ hiện ở trên để dò lỗi.</div>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const btn = document.getElementById('btn-captcha-refresh');
  const img = document.getElementById('captcha-preview');
  if (btn && img) {
    btn.addEventListener('click', function() {
      img.src = '<?= url('captcha.php') ?>?preview=' + Date.now();
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
