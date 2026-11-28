<?php
/**
 * Admin: create / edit one Design Service or IP Service accordion item
 *
 * The body is written in CKEditor. Interactive keywords are inserted as [[keyword]]
 * markers and expanded into hover tooltips when the page is rendered.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/upload.php';
require_once __DIR__ . '/../models/ServiceItem.php';
require_once __DIR__ . '/../models/ServiceTooltip.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$item = $id ? ServiceItem::getById($id) : null;

if ($id && !$item) {
    header("Location: " . admin_url('services.php'));
    exit;
}

$defaultGroup = (($_GET['group'] ?? '') === 'ip') ? 'ip' : 'design';

$item = $item ?? [
    'column_group' => $defaultGroup, 'anchor_id' => '', 'title' => '', 'image_url' => '',
    'body' => '', 'sort_order' => 0, 'is_active' => 1,
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'column_group' => ($_POST['column_group'] ?? 'design') === 'ip' ? 'ip' : 'design',
        'anchor_id'    => trim($_POST['anchor_id'] ?? ''),
        'title'        => trim($_POST['title'] ?? ''),
        'body'         => trim($_POST['body'] ?? ''),
        'sort_order'   => (int)($_POST['sort_order'] ?? 0),
        'is_active'    => isset($_POST['is_active']) ? 1 : 0,
    ];

    $data['image_url'] = adminUploadField('image_file', 'media', 'service', trim($_POST['image_url'] ?? ''), $errors);

    if ($data['title'] === '') {
        $errors[] = 'Vui lòng nhập Tên mục dịch vụ.';
    }

    if (empty($errors)) {
        $savedId = ServiceItem::save($id ?: null, $data);
        if ($savedId) {
            header("Location: " . admin_url('services.php?saved=1'));
            exit;
        }
        $errors[] = 'Lỗi cơ sở dữ liệu khi lưu mục dịch vụ.';
    }

    $item = array_merge($item, $data);
}

$keywords = ServiceTooltip::getActive();

$adminTitle = $id ? 'Sửa mục dịch vụ #' . $id : 'Thêm mục dịch vụ';
$adminActivePage = 'services';

require_once __DIR__ . '/includes/header.php';
?>

<form action="<?= admin_url('service-edit.php' . ($id ? '?id=' . $id : '')) ?>" method="POST" enctype="multipart/form-data" id="service-form">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
      <div class="text-muted small mb-1">
        <a href="<?= admin_url('services.php') ?>" class="text-decoration-none text-muted">
          <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Design &amp; IP Services
        </a>
      </div>
      <h3 class="mb-0 fw-bold text-dark">
        <i class="fa-solid <?= $id ? 'fa-pen-to-square' : 'fa-plus-circle' ?> text-primary me-2"></i>
        <?= $id ? 'Sửa: ' . e($item['title']) : 'Thêm mục dịch vụ mới' ?>
      </h3>
    </div>
    <div class="d-flex gap-2">
      <?php if ($id && !empty($item['anchor_id'])): ?>
        <a href="<?= url('business.php#' . urlencode($item['anchor_id'])) ?>" target="_blank" class="btn btn-outline-secondary">
          <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Xem trên site
        </a>
      <?php endif; ?>
      <a href="<?= admin_url('services.php') ?>" class="btn btn-outline-secondary">Hủy bỏ</a>
      <button type="submit" class="btn btn-primary px-3">
        <i class="fa-solid fa-check me-1"></i> <?= $id ? 'Cập nhật' : 'Lưu mục' ?>
      </button>
    </div>
  </div>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fa-solid fa-circle-exclamation me-2"></i><?= e($err) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endforeach; ?>

  <div class="row g-4">
    <!-- Left: content -->
    <div class="col-12 col-lg-8">

      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-file-lines text-primary me-2"></i>Thông tin mục</h5>

        <div class="mb-3">
          <label class="form-label fw-bold text-dark">Tên mục (hiện trên thanh accordion) <span class="text-danger">*</span></label>
          <input type="text" name="title" id="service-title" class="form-control form-control-lg fw-bold" required
                 value="<?= e($item['title']) ?>" placeholder="VD: Specification to GDSII">
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">Cột hiển thị</label>
            <select name="column_group" class="form-select">
              <?php foreach (ServiceItem::GROUPS as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= ServiceItem::groupOf($item) === $key ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">Anchor (liên kết #... trên business.php)</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light text-muted">business.php#</span>
              <input type="text" name="anchor_id" id="service-anchor" class="form-control"
                     value="<?= e($item['anchor_id']) ?>" placeholder="spec-gdsii">
              <button type="button" class="btn btn-outline-secondary" id="btn-anchor-gen" title="Tạo từ tên mục">
                <i class="fa-solid fa-rotate"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
          <label class="form-label fw-bold text-dark mb-0">
            <i class="fa-solid fa-align-left text-primary me-2"></i>Nội dung mục
          </label>
          <span class="small text-muted">Từ khoá màu cam = có tooltip khi rê chuột. Nút <strong>Source</strong> để sửa HTML thô.</span>
        </div>

        <textarea name="body" id="service-body" class="form-control" rows="12"><?= e($item['body']) ?></textarea>

        <div class="mt-3 p-3 rounded-3" style="background: #FFFBEB; border: 1px dashed #FCD34D;">
          <div class="fw-bold mb-2" style="color: #92400E; font-size: 0.88rem;">
            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Từ khoá có tooltip
          </div>
          <div class="small text-muted mb-2">
            Trong nội dung, từ khoá có tooltip hiện màu cam gạch chân. <strong>Bôi đen một cụm từ</strong> rồi bấm “Gắn tooltip”
            để biến nó thành từ khoá; đặt con trỏ vào từ khoá và bấm “Bỏ tooltip” để gỡ. Bấm một từ khoá bên dưới để chèn vào vị trí con trỏ.
          </div>
          <div class="d-flex flex-wrap gap-2 mb-2">
            <button type="button" class="btn btn-sm btn-warning fw-bold" id="btn-wrap-keyword" disabled>
              <i class="fa-solid fa-comment-dots me-1"></i> Gắn tooltip cho chữ đang chọn
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-unwrap-keyword" disabled>
              <i class="fa-solid fa-eraser me-1"></i> Bỏ tooltip
            </button>
            <span class="small text-muted align-self-center" id="kw-sel-hint"></span>
          </div>
          <div class="d-flex flex-wrap gap-2">
            <?php if (empty($keywords)): ?>
              <span class="small text-muted fst-italic">Chưa có từ khoá nào.</span>
            <?php else: ?>
              <?php foreach ($keywords as $kw): ?>
                <button type="button" class="btn btn-sm btn-outline-warning btn-insert-keyword"
                        data-keyword="<?= e($kw['keyword']) ?>" title="<?= e(mb_substr(strip_tags((string)$kw['content']), 0, 140)) ?>">
                  <?= e($kw['keyword']) ?>
                </button>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <a href="<?= admin_url('tooltips.php') ?>" target="_blank" class="btn btn-sm btn-link px-0 mt-2">
            <i class="fa-solid fa-gear me-1"></i> Quản lý từ điển tooltip
          </a>
          <div class="mt-2 pt-2 border-top" style="border-color: #FCD34D !important;">
            <div class="small fw-bold mb-1" style="color: #92400E;">Từ khoá đang dùng trong mục này</div>
            <div class="d-flex flex-wrap gap-2" id="kw-in-item"><span class="small text-muted fst-italic">…</span></div>
          </div>
        </div>
      </div>

    </div>

    <!-- Right: settings + image -->
    <div class="col-12 col-lg-4">

      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-sliders text-primary me-2"></i>Hiển thị</h5>

        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                 <?= !empty($item['is_active']) ? 'checked' : '' ?>>
          <label class="form-check-label fw-bold text-dark" for="is_active">
            <i class="fa-solid fa-eye text-success me-1"></i> Hiện mục này trên site
          </label>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Thứ tự trong cột</label>
          <input type="number" name="sort_order" class="form-control" value="<?= (int)$item['sort_order'] ?>">
          <div class="form-text small">Số nhỏ hiện trước. Bạn cũng có thể kéo thả ở trang danh sách.</div>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-primary fw-bold py-2">
            <i class="fa-solid fa-floppy-disk me-1"></i> Lưu mục dịch vụ
          </button>
        </div>
      </div>

      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-image text-primary me-2"></i>Ảnh minh hoạ</h5>

        <div class="text-center mb-3 p-3 bg-light rounded-3 border">
          <img id="service-img-preview" src="<?= asset($item['image_url'] ?: 'images/hero_chip_bg.webp') ?>" alt=""
               style="max-width: 100%; height: 150px; object-fit: cover; border-radius: 8px;">
          <div class="small text-muted mt-2 text-truncate"><?= e($item['image_url'] ?: 'Chưa có ảnh') ?></div>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Tải ảnh lên</label>
          <input type="file" name="image_file" id="service-img-file" class="form-control" accept="image/*">
          <div class="form-text small">Khuyến nghị tỉ lệ 16:9, định dạng WEBP/PNG.</div>
        </div>

        <div>
          <label class="form-label small fw-semibold text-muted">Hoặc nhập đường dẫn ảnh</label>
          <input type="text" name="image_url" class="form-control form-control-sm"
                 value="<?= e($item['image_url']) ?>" placeholder="uploads/media/service.webp">
        </div>
      </div>

    </div>
  </div>
</form>

<script src="<?= asset('js/service-editor.js') ?>?v=<?= @filemtime(__DIR__ . '/../assets/js/service-editor.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Anchor generator
  const titleInput = document.getElementById('service-title');
  const anchorInput = document.getElementById('service-anchor');
  const btnAnchor = document.getElementById('btn-anchor-gen');

  function makeSlug(text) {
    return text.toString().toLowerCase().trim()
      .normalize('NFD').replace(/[̀-ͯ]/g, '')
      .replace(/đ/g, 'd')
      .replace(/[^a-z0-9 -]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-');
  }

  if (titleInput && anchorInput && btnAnchor) {
    titleInput.addEventListener('keyup', function() {
      if (!anchorInput.dataset.manual && !anchorInput.value) {
        anchorInput.value = makeSlug(titleInput.value);
      }
    });
    anchorInput.addEventListener('input', function() { anchorInput.dataset.manual = 'true'; });
    btnAnchor.addEventListener('click', function() {
      if (titleInput.value) {
        anchorInput.value = makeSlug(titleInput.value);
        anchorInput.dataset.manual = 'true';
        showToast('Đã tạo anchor từ tên mục', 'info');
      }
    });
  }

  // Image preview
  const imgFile = document.getElementById('service-img-file');
  const imgPreview = document.getElementById('service-img-preview');
  if (imgFile && imgPreview) {
    imgFile.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { imgPreview.src = e.target.result; };
        reader.readAsDataURL(this.files[0]);
      }
    });
  }

  // CKEditor for the item body (shared editor: keywords show as orange words, never as [[brackets]])
  const dict = <?= json_encode(array_map(static function ($kw) {
      return ['id' => (int)$kw['id'], 'keyword' => $kw['keyword'], 'title' => $kw['title']];
  }, $keywords), JSON_UNESCAPED_UNICODE) ?>;
  const dictMap = VNServiceEditor.dictToMap(dict);
  const tooltipsUrl = '<?= admin_url('tooltips.php') ?>';
  const kwList = document.getElementById('kw-in-item');
  const btnWrap = document.getElementById('btn-wrap-keyword');
  const btnUnwrap = document.getElementById('btn-unwrap-keyword');
  const selHint = document.getElementById('kw-sel-hint');

  function renderUsed(keywords) {
    VNServiceEditor.renderKeywordList(kwList, keywords, dictMap, function(kw, row) {
      window.open(row ? tooltipsUrl + '?id=' + row.id : tooltipsUrl + '?keyword=' + encodeURIComponent(kw), '_blank');
    });
  }

  const body = VNServiceEditor.create('service-body', {
    height: 420,
    baseHref: '<?= absolute_url() ?>',
    extraPlugins: 'uploadimage,justify',
    contentsCss: ['https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', '<?= absolute_url(ADMIN_DIR . '/assets/css/editor-content.css') ?>?v=<?= @filemtime(__DIR__ . '/assets/css/editor-content.css') ?>'],
    onChange: renderUsed,
    onCaret: function(markerKw, selected) {
      if (markerKw) {
        selHint.innerHTML = 'Đang ở từ khoá <b>' + VNServiceEditor.escHtml(markerKw) + '</b>';
        btnWrap.disabled = true; btnUnwrap.disabled = false;
      } else if (selected) {
        selHint.innerHTML = 'Đã chọn: <b>' + VNServiceEditor.escHtml(selected) + '</b>';
        btnWrap.disabled = false; btnUnwrap.disabled = true;
      } else {
        selHint.innerHTML = '';
        btnWrap.disabled = true; btnUnwrap.disabled = true;
      }
    }
  });
  body.ready(function(ctl) {
    // the textarea holds the stored body with [[markers]]: show it in span form
    ctl.setBody(document.getElementById('service-body').value);
    setTimeout(function() { renderUsed(ctl.getKeywords()); }, 150);
  });

  btnWrap.addEventListener('click', function() {
    const kw = body.wrapSelection();
    if (!kw) return;
    renderUsed(body.getKeywords());
    if (!dictMap[VNServiceEditor.normalise(kw)]) {
      showToast('Từ khoá mới “' + kw + '” chưa có giải thích — viết ở trang Từ điển Tooltip', 'info');
    }
  });
  btnUnwrap.addEventListener('click', function() {
    if (body.unwrapCurrent()) renderUsed(body.getKeywords());
  });

  // Insert an existing keyword at the caret (or wrap the current selection with it)
  document.querySelectorAll('.btn-insert-keyword').forEach(btn => {
    btn.addEventListener('click', function() {
      const kw = this.getAttribute('data-keyword');
      body.insertKeyword(kw);
      renderUsed(body.getKeywords());
      showToast('Đã chèn từ khoá ' + kw, 'info');
    });
  });

  // On submit, hand the server the [[marker]] form
  const form = document.getElementById('service-form');
  if (form) {
    form.addEventListener('submit', function() {
      if (body.editor) {
        document.getElementById('service-body').value = body.getBody();
      }
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
