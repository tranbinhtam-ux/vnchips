<?php
/**
 * Admin Create / Edit Partner Case Study
 * Enhanced with Live Image Previews, Real-time Slug Generator, and Dual Layout.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/upload.php';
require_once __DIR__ . '/../models/Partner.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$partner = $id ? Partner::getById($id) : null;

if ($id && !$partner) {
    header("Location: " . admin_url('partners.php'));
    exit;
}

$partner = $partner ?? [
    'title' => '', 'slug' => '', 'category_name' => 'Partners', 'image_url' => '', 'excerpt' => '',
    'content_image' => '', 'content_heading' => '', 'content_body' => '', 'is_pinned' => 0, 'sort_order' => 0, 'status' => 'published',
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'category_name' => trim($_POST['category_name'] ?? 'Partners'),
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'content_heading' => trim($_POST['content_heading'] ?? ''),
        'content_body' => trim($_POST['content_body'] ?? ''),
        'is_pinned' => isset($_POST['is_pinned']) ? 1 : 0,
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
        'status' => $_POST['status'] ?? 'published',
    ];

    $data['image_url'] = adminUploadField('image_file', 'featured', 'featured', trim($_POST['image_url'] ?? ''), $errors);
    $data['content_image'] = adminUploadField('content_image_file', 'posts', 'post', trim($_POST['content_image'] ?? ''), $errors);

    if ($data['title'] === '' || $data['excerpt'] === '') {
        $errors[] = 'Please fill in Title and Excerpt.';
    }

    if (empty($errors)) {
        $data['content_heading'] = $data['content_heading'] ?: $data['title'];
        $data['content_body'] = $data['content_body'] ?: $data['excerpt'];

        $savedId = Partner::save($id ?: null, $data);
        if ($savedId) {
            header("Location: " . admin_url('partners.php?saved=1'));
            exit;
        }
        $errors[] = 'Database error saving partner.';
    }

    $partner = array_merge($partner, $data);
}

$adminTitle = $id ? 'Edit Partner #' . $id : 'Add New Partner';
$adminActivePage = 'partners';

require_once __DIR__ . '/includes/header.php';
?>

<form action="<?= admin_url('partner-edit.php' . ($id ? '?id=' . $id : '')) ?>" method="POST" enctype="multipart/form-data" id="partner-form">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
      <div class="text-muted small mb-1">
        <a href="<?= admin_url('partners.php') ?>" class="text-decoration-none text-muted">
          <i class="fa-solid fa-arrow-left me-1"></i> Back to Partners
        </a>
      </div>
      <h3 class="mb-0 fw-bold text-dark">
        <i class="fa-solid <?= $id ? 'fa-pen-to-square' : 'fa-plus-circle' ?> text-primary me-2"></i>
        <?= $id ? 'Edit Partner: ' . e($partner['title']) : 'Add New Partner Case Study' ?>
      </h3>
    </div>
    <div class="d-flex gap-2">
      <?php if ($id && !empty($partner['slug'])): ?>
        <a href="<?= url('partner-detail.php?slug=' . urlencode($partner['slug'])) ?>" target="_blank" class="btn btn-outline-secondary">
          <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View Case Study
        </a>
      <?php endif; ?>
      <a href="<?= admin_url('partners.php') ?>" class="btn btn-outline-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary px-3">
        <i class="fa-solid fa-check me-1"></i> <?= $id ? 'Update Partner' : 'Save Partner' ?>
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
    <!-- Left Column: Primary Content (8 cols) -->
    <div class="col-12 col-lg-8">
      
      <!-- Basic Details Card -->
      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-file-lines text-primary me-2"></i>Case Study Information</h5>

        <div class="mb-3">
          <label class="form-label fw-bold text-dark">Partner Case Title <span class="text-danger">*</span></label>
          <input type="text" name="title" id="partner-title" class="form-control form-control-lg fw-bold" required 
                 value="<?= e($partner['title']) ?>" placeholder="e.g. Cadence Verification Partnership 2026">
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">URL Slug</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light text-muted">partner-detail.php?slug=</span>
              <input type="text" name="slug" id="partner-slug" class="form-control" 
                     value="<?= e($partner['slug']) ?>" placeholder="cadence-verification-partnership">
              <button type="button" class="btn btn-outline-secondary" id="btn-slug-gen" title="Generate slug">
                <i class="fa-solid fa-rotate"></i>
              </button>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">Category Tag (e.g. EDA Tools, Foundry)</label>
            <input type="text" name="category_name" class="form-control form-control-sm" 
                   value="<?= e($partner['category_name']) ?>" placeholder="Partners">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Excerpt / Card Summary <span class="text-danger">*</span></label>
          <textarea name="excerpt" rows="3" class="form-control" required 
                    placeholder="Short 2-3 sentence overview displayed on the main Partners grid..."><?= e($partner['excerpt']) ?></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Detail Page Headline</label>
          <input type="text" name="content_heading" class="form-control" 
                 value="<?= e($partner['content_heading']) ?>" placeholder="Defaults to Title if left blank">
        </div>

      </div>

      <!-- Dual Mode Content Editor -->
      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
          <label class="form-label fw-bold text-dark mb-0">
            <i class="fa-solid fa-file-code text-primary me-2"></i>Full Case Study Narrative / Body Content
          </label>
          <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-primary" id="btn-mode-visual">
              <i class="fa-solid fa-eye me-1"></i> Visual (CKEditor)
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btn-mode-code">
              <i class="fa-solid fa-code me-1"></i> HTML Code (Ace)
            </button>
          </div>
        </div>

        <!-- Hidden master textarea that will be submitted with form -->
        <textarea name="content_body" id="master-content" style="display: none;"><?= e($partner['content_body']) ?></textarea>

        <!-- Container for CKEditor Visual -->
        <div id="editor-visual-container">
          <textarea id="ckeditor-instance"><?= e($partner['content_body']) ?></textarea>
        </div>

        <!-- Container for Ace HTML Editor -->
        <div id="editor-code-container" style="display: none;">
          <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-dark text-white-50 rounded-top small">
            <span><i class="fa-brands fa-html5 text-warning me-1"></i> HTML Editor Mode (Ace Editor)</span>
            <span>Syntax: HTML &bull; Theme: Monokai</span>
          </div>
          <div id="ace-editor" style="width: 100%; height: 500px; border-radius: 0 0 8px 8px; font-size: 14px;"></div>
        </div>

      </div>

      <!-- Detail Hero Image Card -->
      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-panorama text-primary me-2"></i>Detail Page Hero Banner Image</h5>

        <div class="text-center mb-3 p-3 bg-light rounded-3 border">
          <img id="content-img-preview" src="<?= asset($partner['content_image'] ?: ($partner['image_url'] ?: 'images/hero_chip_bg.webp')) ?>" alt="" 
               style="max-width: 100%; height: 180px; object-fit: cover; border-radius: 8px;">
          <div class="small text-muted mt-2 text-truncate"><?= e($partner['content_image'] ?: 'Defaulting to card image') ?></div>
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">Upload Detail Image</label>
            <input type="file" name="content_image_file" id="content-img-file" class="form-control" accept="image/*">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">Or Image Path</label>
            <input type="text" name="content_image" class="form-control" 
                   value="<?= e($partner['content_image']) ?>" placeholder="uploads/posts/hero.webp">
          </div>
        </div>
      </div>

    </div>

    <!-- Right Column: Settings & Card Thumbnail (4 cols) -->
    <div class="col-12 col-lg-4">
      
      <!-- Publish & Status Card -->
      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-sliders text-primary me-2"></i>Publishing</h5>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Status</label>
          <select name="status" class="form-select">
            <option value="published" <?= ($partner['status'] === 'published') ? 'selected' : '' ?>>🟢 Published (Visible)</option>
            <option value="draft" <?= ($partner['status'] === 'draft') ? 'selected' : '' ?>>🟡 Draft (Hidden)</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Sort Order</label>
          <input type="number" name="sort_order" class="form-control" value="<?= (int)$partner['sort_order'] ?>">
          <div class="form-text small">Lower numbers display first (0, 1, 2...).</div>
        </div>

        <div class="form-check form-switch mb-4">
          <input class="form-check-input" type="checkbox" name="is_pinned" id="is_pinned" value="1" 
                 <?= !empty($partner['is_pinned']) ? 'checked' : '' ?>>
          <label class="form-check-label fw-bold text-dark" for="is_pinned">
            <i class="fa-solid fa-thumbtack text-warning me-1"></i> Pin to Top Priority
          </label>
        </div>

        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-primary fw-bold py-2">
            <i class="fa-solid fa-floppy-disk me-1"></i> Save Partner Case
          </button>
        </div>
      </div>

      <!-- Card Thumbnail Card -->
      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-image text-primary me-2"></i>Card Grid Image / Logo</h5>

        <div class="text-center mb-3 p-3 bg-light rounded-3 border">
          <img id="card-img-preview" src="<?= asset($partner['image_url'] ?: 'images/hero_chip_bg.webp') ?>" alt="" 
               style="max-width: 100%; height: 140px; object-fit: cover; border-radius: 8px;">
          <div class="small text-muted mt-2 text-truncate"><?= e($partner['image_url'] ?: 'Default chip background') ?></div>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Upload Card Image</label>
          <input type="file" name="image_file" id="card-img-file" class="form-control" accept="image/*">
          <div class="form-text small">Recommended: 16:9 ratio, WEBP/PNG.</div>
        </div>

        <div>
          <label class="form-label small fw-semibold text-muted">Or Image Path</label>
          <input type="text" name="image_url" class="form-control form-control-sm" 
                 value="<?= e($partner['image_url']) ?>" placeholder="uploads/featured/partner.webp">
        </div>
      </div>

    </div>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Slug Generator
  const titleInput = document.getElementById('partner-title');
  const slugInput = document.getElementById('partner-slug');
  const btnSlug = document.getElementById('btn-slug-gen');

  function makeSlug(text) {
    return text.toString().toLowerCase().trim()
      .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .replace(/đ/g, 'd').replace(/Đ/g, 'd')
      .replace(/[^a-z0-9 -]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-');
  }

  if (titleInput && slugInput && btnSlug) {
    titleInput.addEventListener('keyup', function() {
      if (!slugInput.dataset.manual && !slugInput.value) {
        slugInput.value = makeSlug(titleInput.value);
      }
    });
    slugInput.addEventListener('input', function() {
      slugInput.dataset.manual = 'true';
    });
    btnSlug.addEventListener('click', function() {
      if (titleInput.value) {
        slugInput.value = makeSlug(titleInput.value);
        slugInput.dataset.manual = 'true';
        showToast('Slug generated', 'info');
      }
    });
  }

  // Previews
  function setupPreview(fileId, previewId) {
    const fileEl = document.getElementById(fileId);
    const prevEl = document.getElementById(previewId);
    if (fileEl && prevEl) {
      fileEl.addEventListener('change', function() {
        if (this.files && this.files[0]) {
          const reader = new FileReader();
          reader.onload = function(e) {
            prevEl.src = e.target.result;
          }
          reader.readAsDataURL(this.files[0]);
        }
      });
    }
  }

  setupPreview('card-img-file', 'card-img-preview');
  setupPreview('content-img-file', 'content-img-preview');

  // Dual Mode Editor (CKEditor Visual + Ace HTML) for Case Study Body
  const masterContent = document.getElementById('master-content');
  const visualContainer = document.getElementById('editor-visual-container');
  const codeContainer = document.getElementById('editor-code-container');
  const btnModeVisual = document.getElementById('btn-mode-visual');
  const btnModeCode = document.getElementById('btn-mode-code');

  let currentMode = 'visual'; // 'visual' | 'code'
  let ckInstance = null;
  let aceInstance = null;

  // Initialize CKEditor
  if (typeof CKEDITOR !== 'undefined') {
    CKEDITOR.config.versionCheck = false;
    CKEDITOR.config.baseHref = '<?= absolute_url() ?>';
    ckInstance = CKEDITOR.replace('ckeditor-instance', {
      height: 480,
      removeButtons: 'Save,NewPage,ExportPdf,Preview,Print',
      extraPlugins: 'uploadimage,justify',
      baseHref: '<?= absolute_url() ?>',
      contentsCss: ['https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', '<?= absolute_url(ADMIN_DIR . '/assets/css/editor-content.css') ?>?v=<?= @filemtime(__DIR__ . '/assets/css/editor-content.css') ?>'],
    });

    ckInstance.on('change', function() {
      masterContent.value = ckInstance.getData();
    });
  }

  // Initialize Ace Editor
  if (typeof ace !== 'undefined') {
    aceInstance = ace.edit("ace-editor");
    aceInstance.setTheme("ace/theme/monokai");
    aceInstance.session.setMode("ace/mode/html");
    aceInstance.setValue(masterContent.value, -1);
    aceInstance.setOptions({
      fontSize: "14px",
      wrap: true,
      showPrintMargin: false
    });

    aceInstance.session.on('change', function() {
      masterContent.value = aceInstance.getValue();
    });
  }

  // Mode Switchers
  if (btnModeVisual && btnModeCode) {
    btnModeVisual.addEventListener('click', function() {
      if (currentMode === 'visual') return;
      currentMode = 'visual';

      // Sync Ace to Visual
      if (aceInstance && ckInstance) {
        const codeData = aceInstance.getValue();
        ckInstance.setData(codeData);
        masterContent.value = codeData;
      }

      codeContainer.style.display = 'none';
      visualContainer.style.display = 'block';

      btnModeVisual.className = 'btn btn-primary';
      btnModeCode.className = 'btn btn-outline-secondary';
    });

    btnModeCode.addEventListener('click', function() {
      if (currentMode === 'code') return;
      currentMode = 'code';

      // Sync Visual to Ace
      if (ckInstance && aceInstance) {
        const visualData = ckInstance.getData();
        aceInstance.setValue(visualData, -1);
        masterContent.value = visualData;
      }

      visualContainer.style.display = 'none';
      codeContainer.style.display = 'block';

      btnModeCode.className = 'btn btn-primary';
      btnModeVisual.className = 'btn btn-outline-secondary';
    });
  }

  // Ensure masterContent is 100% updated on submit
  const partnerForm = document.getElementById('partner-form');
  if (partnerForm) {
    partnerForm.addEventListener('submit', function() {
      if (currentMode === 'visual' && ckInstance) {
        masterContent.value = ckInstance.getData();
      } else if (currentMode === 'code' && aceInstance) {
        masterContent.value = aceInstance.getValue();
      }
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
