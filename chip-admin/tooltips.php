<?php
/**
 * Admin: interactive keyword tooltip dictionary
 *
 * Each entry turns `[[keyword]]` inside a service item body into the orange underlined
 * keyword that shows an explanation panel on hover (and a bottom sheet on mobile).
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../models/ServiceItem.php';
require_once __DIR__ . '/../models/ServiceTooltip.php';

$message = '';
$error = '';

$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = $editId ? ServiceTooltip::getById($editId) : null;
if ($editId && !$editing) {
    header("Location: " . admin_url('tooltips.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_tooltip') {
    $postId = (int)($_POST['id'] ?? 0);
    $data = [
        'keyword'   => trim($_POST['keyword'] ?? ''),
        'title'     => trim($_POST['title'] ?? ''),
        'content'   => trim($_POST['content'] ?? ''),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
    ];

    if ($data['keyword'] === '') {
        $error = 'Vui lòng nhập Từ khoá.';
        $editing = array_merge(['id' => $postId], $data);
    } elseif (ServiceTooltip::save($postId ?: null, $data)) {
        header("Location: " . admin_url('tooltips.php?saved=1'));
        exit;
    } else {
        $error = 'Không lưu được. Từ khoá “' . $data['keyword'] . '” có thể đã tồn tại.';
        $editing = array_merge(['id' => $postId], $data);
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['del'])) {
    if (ServiceTooltip::delete((int)$_GET['del'])) {
        $message = 'Đã xoá từ khoá.';
    } else {
        $error = 'Không xoá được từ khoá.';
    }
}

if (isset($_GET['saved'])) {
    $message = 'Đã lưu từ khoá tooltip!';
}

$tooltips = ServiceTooltip::getAll();
$usage = ServiceTooltip::usageCounts();

$editing = $editing ?? ['id' => 0, 'keyword' => '', 'title' => '', 'content' => '', 'is_active' => 1];

$adminTitle = 'Từ điển Tooltip';
$adminActivePage = 'services';

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-panel mb-4">
  <div class="panel-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <h3 class="panel-title mb-1">
        <i class="fa-solid fa-comment-dots text-primary me-2"></i>Từ điển Tooltip
      </h3>
      <p class="text-muted small mb-0">Giải thích hiện lên khi rê chuột vào từ khoá trong khối Design &amp; IP Services</p>
    </div>
    <a href="<?= admin_url('services.php') ?>" class="btn btn-outline-secondary">
      <i class="fa-solid fa-arrow-left me-1"></i> Về Design &amp; IP Services
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

  <div class="row g-4">
    <!-- Editor -->
    <div class="col-12 col-lg-5">
      <form method="POST" class="card border-0 shadow-sm" style="border-radius: 14px;">
        <input type="hidden" name="action" value="save_tooltip">
        <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
        <div class="card-body p-4">
          <h5 class="fw-bold text-dark mb-3">
            <i class="fa-solid <?= $editing['id'] ? 'fa-pen-to-square' : 'fa-plus-circle' ?> text-primary me-2"></i>
            <?= $editing['id'] ? 'Sửa từ khoá' : 'Thêm từ khoá mới' ?>
          </h5>

          <div class="mb-3">
            <label class="form-label small fw-semibold text-muted">Từ khoá <span class="text-danger">*</span></label>
            <input type="text" name="keyword" class="form-control" required
                   value="<?= e($editing['keyword']) ?>" placeholder="VD: IP integration">
            <div class="form-text small">
              Viết <code>[[<?= e($editing['keyword'] ?: 'IP integration') ?>]]</code> trong nội dung mục dịch vụ để dùng.
              Không phân biệt hoa thường.
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold text-muted">Tiêu đề trong bảng giải thích</label>
            <input type="text" name="title" class="form-control"
                   value="<?= e($editing['title']) ?>" placeholder="Để trống sẽ dùng chính từ khoá">
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold text-muted">Nội dung giải thích</label>
            <textarea name="content" rows="10" class="form-control" style="font-size: 0.86rem;"
                      placeholder="Cho phép HTML đơn giản: <br>, <b>, <i>, <ul><li>..."><?= e($editing['content']) ?></textarea>
            <div class="form-text small">Dùng <code>&lt;br&gt;&lt;br&gt;</code> để ngắt đoạn giống các mục hiện có.</div>
          </div>

          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" id="tip_active" value="1"
                   <?= !empty($editing['is_active']) ? 'checked' : '' ?>>
            <label class="form-check-label fw-bold text-dark" for="tip_active">Đang dùng</label>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">
              <i class="fa-solid fa-floppy-disk me-1"></i> <?= $editing['id'] ? 'Cập nhật' : 'Thêm từ khoá' ?>
            </button>
            <?php if ($editing['id']): ?>
              <a href="<?= admin_url('tooltips.php') ?>" class="btn btn-outline-secondary">Hủy</a>
            <?php endif; ?>
          </div>
        </div>
      </form>
    </div>

    <!-- List -->
    <div class="col-12 col-lg-7">
      <div class="card border-0 shadow-sm" style="border-radius: 14px;">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-dark mb-0">
              <i class="fa-solid fa-list text-primary me-2"></i>Danh sách
              <span class="badge badge-primary ms-1"><?= count($tooltips) ?></span>
            </h5>
            <span class="small text-muted">Cột “Dùng” = số lần được nhắc trong nội dung các mục</span>
          </div>

          <div class="table-responsive">
            <table class="admin-table align-middle">
              <thead>
                <tr>
                  <th>Từ khoá</th>
                  <th>Tiêu đề bảng</th>
                  <th style="width: 70px; text-align: center;">Dùng</th>
                  <th style="width: 90px; text-align: center;">Trạng thái</th>
                  <th style="width: 100px; text-align: right;">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($tooltips)): ?>
                  <tr><td colspan="5" class="text-center py-4 text-muted">Chưa có từ khoá nào.</td></tr>
                <?php else: ?>
                  <?php foreach ($tooltips as $tip): ?>
                    <?php $used = $usage[ServiceTooltip::normalise($tip['keyword'])] ?? 0; ?>
                    <tr>
                      <td>
                        <a href="<?= admin_url('tooltips.php?id=' . (int)$tip['id']) ?>" class="fw-bold text-decoration-none">
                          <?= e($tip['keyword']) ?>
                        </a>
                        <div class="small text-muted text-truncate" style="max-width: 320px;">
                          <?= e(mb_substr(strip_tags((string)$tip['content']), 0, 90)) ?>…
                        </div>
                      </td>
                      <td style="font-size: 0.86rem; color: #334155;"><?= e($tip['title']) ?></td>
                      <td style="text-align: center;">
                        <span class="badge <?= $used > 0 ? 'badge-primary' : 'badge-secondary' ?>"><?= $used ?></span>
                      </td>
                      <td style="text-align: center;">
                        <button type="button" class="btn btn-sm btn-tip-toggle <?= !empty($tip['is_active']) ? 'btn-success' : 'btn-secondary' ?>"
                                data-id="<?= (int)$tip['id'] ?>" title="Bật/tắt">
                          <i class="fa-solid <?= !empty($tip['is_active']) ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                        </button>
                      </td>
                      <td style="text-align: right;">
                        <div class="btn-group btn-group-sm">
                          <a href="<?= admin_url('tooltips.php?id=' . (int)$tip['id']) ?>" class="btn btn-outline-secondary" title="Sửa">
                            <i class="fa-solid fa-pen-to-square"></i>
                          </a>
                          <a href="<?= admin_url('tooltips.php?action=delete&del=' . (int)$tip['id']) ?>"
                             onclick="return confirm('Xoá từ khoá &quot;<?= addslashes($tip['keyword']) ?>&quot;?<?= $used > 0 ? ' Từ khoá này đang được dùng ' . $used . ' lần, chỗ đó sẽ hiện ra chữ thường.' : '' ?>');"
                             class="btn btn-outline-danger" title="Xoá">
                            <i class="fa-solid fa-trash"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.btn-tip-toggle').forEach(btn => {
    btn.addEventListener('click', function() {
      const formData = new FormData();
      formData.append('action', 'toggle_service_tooltip');
      formData.append('id', this.getAttribute('data-id'));
      this.disabled = true;

      fetch('<?= admin_url("ajax/entity-actions.php") ?>', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
          this.disabled = false;
          if (!data.success) { showToast('Không đổi được trạng thái.', 'error'); return; }
          const on = data.is_active == 1;
          this.className = 'btn btn-sm btn-tip-toggle ' + (on ? 'btn-success' : 'btn-secondary');
          this.innerHTML = '<i class="fa-solid ' + (on ? 'fa-eye' : 'fa-eye-slash') + '"></i>';
          showToast(on ? 'Từ khoá đang dùng' : 'Đã tắt từ khoá', 'success');
        })
        .catch(() => { this.disabled = false; showToast('Lỗi mạng.', 'error'); });
    });
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
