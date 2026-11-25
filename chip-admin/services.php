<?php
/**
 * Admin: Design Service / IP Service accordion
 *
 * The two columns rendered on index.php and business.php#design-service. Drag a row to
 * reorder it, or drag it across to the other column to move it there.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../models/ServiceItem.php';
require_once __DIR__ . '/../models/ServiceTooltip.php';

$message = '';
$error = '';

// Block-level settings (column headings + master on/off switch)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_block') {
    $ok = Setting::updateBatch([
        'services_design_title'    => trim($_POST['services_design_title'] ?? 'DESIGN SERVICE'),
        'services_ip_title'        => trim($_POST['services_ip_title'] ?? 'IP SERVICE'),
        'show_design_ip_services'  => isset($_POST['show_design_ip_services']) ? '1' : '0',
    ], 'homepage');
    $ok ? $message = 'Block settings saved.' : $error = 'Could not save block settings.';
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    if (ServiceItem::delete((int)$_GET['id'])) {
        $message = 'Service item deleted.';
    } else {
        $error = 'Failed to delete service item.';
    }
}

if (isset($_GET['saved'])) {
    $message = 'Service item saved successfully!';
}

$designItems = ServiceItem::getAll('design');
$ipItems     = ServiceItem::getAll('ip');
$allItems    = array_merge($designItems, $ipItems);

$activeCount  = count(array_filter($allItems, fn($i) => !empty($i['is_active'])));
$tooltipCount = count(ServiceTooltip::getAll());

$blockOn      = Setting::get('show_design_ip_services', '1') === '1';
$designTitle  = Setting::getOr('services_design_title', 'DESIGN SERVICE');
$ipTitle      = Setting::getOr('services_ip_title', 'IP SERVICE');

$adminTitle = 'Design Service & IP Service';
$adminActivePage = 'services';

require_once __DIR__ . '/includes/header.php';

/** One sortable column of accordion rows */
function renderServiceColumnCard(array $items, $group, $heading, $accent) {
    ?>
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
            <h5 class="fw-bold text-dark mb-0">
              <i class="fa-solid fa-layer-group me-2" style="color: <?= $accent ?>;"></i><?= e($heading) ?>
              <span class="badge badge-primary ms-1"><?= count($items) ?></span>
            </h5>
            <a href="<?= admin_url('service-edit.php?group=' . $group) ?>" class="btn btn-sm btn-outline-primary">
              <i class="fa-solid fa-plus me-1"></i> Thêm mục
            </a>
          </div>

          <div class="service-sortable" data-group="<?= e($group) ?>" style="display: flex; flex-direction: column; gap: 10px; min-height: 60px;">
            <?php if (empty($items)): ?>
              <div class="text-muted small text-center py-4 border border-dashed rounded-3">
                Chưa có mục nào. Kéo một mục từ cột bên cạnh sang, hoặc bấm “Thêm mục”.
              </div>
            <?php endif; ?>

            <?php foreach ($items as $item): ?>
              <div class="d-flex align-items-center gap-3 p-2 border rounded-3 bg-white" data-id="<?= (int)$item['id'] ?>">
                <span class="drag-handle text-muted" style="cursor: grab;" title="Kéo để sắp xếp / chuyển cột">
                  <i class="fa-solid fa-grip-vertical"></i>
                </span>

                <img src="<?= asset($item['image_url'] ?: 'images/favicon.png') ?>" alt=""
                     style="width: 56px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #E2E8F0; flex: none;">

                <div class="flex-grow-1 min-width-0">
                  <a href="<?= admin_url('service-edit.php?id=' . (int)$item['id']) ?>" class="fw-bold text-decoration-none d-block text-truncate">
                    <?= e($item['title']) ?>
                  </a>
                  <div class="small text-muted text-truncate">
                    #<?= e($item['anchor_id']) ?>
                    &middot; <?= (int)preg_match_all(ServiceTooltip::MARKER_PATTERN, (string)$item['body']) ?> tooltip
                  </div>
                </div>

                <button type="button" class="btn btn-sm btn-service-toggle <?= !empty($item['is_active']) ? 'btn-success' : 'btn-secondary' ?>"
                        data-id="<?= (int)$item['id'] ?>" title="Bật/tắt hiển thị">
                  <i class="fa-solid <?= !empty($item['is_active']) ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                </button>

                <div class="btn-group btn-group-sm">
                  <a href="<?= admin_url('service-edit.php?id=' . (int)$item['id']) ?>" class="btn btn-outline-secondary" title="Sửa">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>
                  <a href="<?= admin_url('services.php?action=delete&id=' . (int)$item['id']) ?>"
                     onclick="return confirm('Xoá mục &quot;<?= addslashes($item['title']) ?>&quot;?');"
                     class="btn btn-outline-danger" title="Xoá">
                    <i class="fa-solid fa-trash"></i>
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
    <?php
}
?>

<div class="admin-panel mb-4">
  <div class="panel-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <h3 class="panel-title mb-1">
        <i class="fa-solid fa-sitemap text-primary me-2"></i>Design Service &amp; IP Service
      </h3>
      <p class="text-muted small mb-0">Khối accordion dùng chung cho Trang chủ và Business Solutions</p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= admin_url('tooltips.php') ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-comment-dots me-1"></i> Từ điển Tooltip (<?= $tooltipCount ?>)
      </a>
      <a href="<?= url('business.php#design-service') ?>" target="_blank" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Xem trên site
      </a>
      <a href="<?= admin_url('service-edit.php') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i> Thêm mục mới
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

  <!-- Block settings -->
  <form method="POST" class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
    <input type="hidden" name="action" value="save_block">
    <div class="card-body p-4">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-md-4">
          <label class="form-label small fw-semibold text-muted">Tiêu đề cột trái</label>
          <input type="text" name="services_design_title" class="form-control" value="<?= e($designTitle) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label small fw-semibold text-muted">Tiêu đề cột phải</label>
          <input type="text" name="services_ip_title" class="form-control" value="<?= e($ipTitle) ?>">
        </div>
        <div class="col-12 col-md-4 d-flex justify-content-between align-items-center gap-3">
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" name="show_design_ip_services" id="show_design_ip_services"
                   value="1" <?= $blockOn ? 'checked' : '' ?>>
            <label class="form-check-label fw-bold text-dark" for="show_design_ip_services">
              Hiển thị khối trên site
            </label>
          </div>
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk me-1"></i> Lưu
          </button>
        </div>
      </div>
      <div class="form-text small mt-2">
        Tắt khối thì khách truy cập không thấy gì; quản trị viên đang đăng nhập vẫn thấy khối mờ kèm nhãn ĐANG TẮT để bật lại.
        Hiện có <strong><?= $activeCount ?></strong>/<strong><?= count($allItems) ?></strong> mục đang hiển thị.
      </div>
    </div>
  </form>

  <div class="d-flex justify-content-end mb-2">
    <div class="small text-muted">
      <i class="fa-solid fa-info-circle text-primary me-1"></i>
      Kéo <i class="fa-solid fa-grip-vertical"></i> để sắp xếp, hoặc kéo sang cột bên cạnh để đổi cột.
    </div>
  </div>

  <div class="row g-4">
    <?php renderServiceColumnCard($designItems, 'design', $designTitle, '#F25B22'); ?>
    <?php renderServiceColumnCard($ipItems, 'ip', $ipTitle, '#0284C7'); ?>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  function persistOrder(listEl) {
    const group = listEl.getAttribute('data-group');
    const order = Array.from(listEl.querySelectorAll('[data-id]')).map(row => row.getAttribute('data-id'));

    const formData = new FormData();
    formData.append('action', 'reorder_service_items');
    formData.append('group', group);
    formData.append('order', JSON.stringify(order));

    fetch('<?= admin_url("ajax/entity-actions.php") ?>', { method: 'POST', body: formData })
      .then(res => res.json())
      .then(data => {
        showToast(data.success ? 'Đã lưu thứ tự mục dịch vụ.' : 'Không lưu được thứ tự.', data.success ? 'success' : 'error');
      })
      .catch(() => showToast('Lỗi mạng khi lưu thứ tự.', 'error'));
  }

  const lists = document.querySelectorAll('.service-sortable');
  if (typeof Sortable !== 'undefined') {
    lists.forEach(list => {
      new Sortable(list, {
        group: 'service-items',
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'bg-light',
        onEnd: function(evt) {
          persistOrder(evt.to);
          if (evt.from !== evt.to) persistOrder(evt.from);
        }
      });
    });
  }

  document.querySelectorAll('.btn-service-toggle').forEach(btn => {
    btn.addEventListener('click', function() {
      const formData = new FormData();
      formData.append('action', 'toggle_service_item');
      formData.append('id', this.getAttribute('data-id'));
      this.disabled = true;

      fetch('<?= admin_url("ajax/entity-actions.php") ?>', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
          this.disabled = false;
          if (!data.success) { showToast('Không đổi được trạng thái.', 'error'); return; }
          const on = data.is_active == 1;
          this.className = 'btn btn-sm btn-service-toggle ' + (on ? 'btn-success' : 'btn-secondary');
          this.innerHTML = '<i class="fa-solid ' + (on ? 'fa-eye' : 'fa-eye-slash') + '"></i>';
          showToast(on ? 'Mục đã hiển thị' : 'Mục đã ẩn', 'success');
        })
        .catch(() => { this.disabled = false; showToast('Lỗi mạng.', 'error'); });
    });
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
