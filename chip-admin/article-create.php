<?php
/**
 * Admin Create Article Page
 * Featuring Dual-mode CKEditor 4 / Ace Editor, Live Slug Generator, Thumbnail Uploader Preview, and Pin controls.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../models/News.php';

$error = '';
$categories = News::getCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $customUrl = trim($_POST['custom_url'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 1);
    $author = trim($_POST['author'] ?? 'VNCHIP Editorial Team');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = $_POST['status'] ?? 'published';
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isPinned = isset($_POST['is_pinned']) ? 1 : 0;
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $publishedAt = !empty($_POST['published_at']) ? $_POST['published_at'] : date('Y-m-d H:i:s');
    $thumbnail = 'assets/images/media_htv9_event.webp'; // Default

    // Handle Image Upload if provided
    if (!empty($_FILES['thumbnail_file']['name']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/images/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmp = $_FILES['thumbnail_file']['tmp_name'];
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['thumbnail_file']['name']));
        $targetPath = $uploadDir . $fileName;

        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($ext, $allowedExts)) {
            if (move_uploaded_file($fileTmp, $targetPath)) {
                $thumbnail = 'assets/images/uploads/' . $fileName;
            } else {
                $error = 'Failed to upload image to server.';
            }
        } else {
            $error = 'Invalid image format. Allowed: JPG, PNG, WEBP, GIF.';
        }
    } elseif (!empty($_POST['thumbnail_preset'])) {
        $thumbnail = $_POST['thumbnail_preset'];
    }

    if (empty($title)) {
        $error = 'Article title cannot be empty.';
    }

    if (empty($error)) {
        $data = [
            'category_id' => $categoryId,
            'title' => $title,
            'slug' => $slug,
            'custom_url' => $customUrl,
            'thumbnail' => $thumbnail,
            'summary' => $summary,
            'content' => $content,
            'author' => $author,
            'is_featured' => $isFeatured,
            'is_pinned' => $isPinned,
            'sort_order' => $sortOrder,
            'status' => $status,
            'published_at' => $publishedAt
        ];

        if (News::create($data)) {
            header("Location: " . admin_url('articles.php?msg=created'));
            exit;
        } else {
            $error = 'Database error creating article.';
        }
    }
}

$adminTitle = 'Create New Article';
$adminActivePage = 'articles';

require_once __DIR__ . '/includes/header.php';
?>

<form action="<?= admin_url('article-create.php') ?>" method="POST" enctype="multipart/form-data" id="article-form">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
      <div class="text-muted small mb-1">
        <a href="<?= admin_url('articles.php') ?>" class="text-decoration-none text-muted">
          <i class="fa-solid fa-arrow-left me-1"></i> Back to Articles
        </a>
      </div>
      <h3 class="mb-0 fw-bold text-dark">
        <i class="fa-solid fa-plus-circle text-primary me-2"></i>Add New Article
      </h3>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= admin_url('articles.php') ?>" class="btn btn-outline-secondary">
        Cancel
      </a>
      <button type="submit" class="btn btn-primary px-3">
        <i class="fa-solid fa-check me-1"></i> Publish Article
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
    <!-- Left Column: Main Content (8 cols) -->
    <div class="col-12 col-lg-8">
      
      <!-- Basic Details Card -->
      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <div class="mb-3">
          <label class="form-label fw-bold text-dark">Article Title <span class="text-danger">*</span></label>
          <input type="text" name="title" id="article-title" class="form-control form-control-lg fw-bold" 
                 value="<?= e($_POST['title'] ?? '') ?>" placeholder="e.g. Cadence Verification AI Suite Overview 2026..." required>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">URL Slug (Auto-generated)</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light text-muted">/media-detail.php?slug=</span>
              <input type="text" name="slug" id="article-slug" class="form-control" value="<?= e($_POST['slug'] ?? '') ?>">
              <button type="button" class="btn btn-outline-secondary" id="btn-slug-generate" title="Regenerate slug from title">
                <i class="fa-solid fa-rotate"></i>
              </button>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">Custom Redirect URL (Optional)</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-link"></i></span>
              <input type="text" name="custom_url" class="form-control" 
                     placeholder="https://... or /custom-page.php" value="<?= e($_POST['custom_url'] ?? '') ?>">
            </div>
          </div>
        </div>

        <div>
          <label class="form-label small fw-semibold text-muted">Summary / Excerpt (Displayed on Cards & Social Meta)</label>
          <textarea name="summary" rows="3" class="form-control" 
                    placeholder="Brief 1-2 sentence overview of the article..."><?= e($_POST['summary'] ?? '') ?></textarea>
        </div>
      </div>

      <!-- Dual Mode Content Editor -->
      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
          <label class="form-label fw-bold text-dark mb-0">
            <i class="fa-solid fa-file-code text-primary me-2"></i>Full Article Content
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
        <textarea name="content" id="master-content" style="display: none;"><?= e($_POST['content'] ?? '') ?></textarea>

        <!-- Container for CKEditor Visual -->
        <div id="editor-visual-container">
          <textarea id="ckeditor-instance"><?= e($_POST['content'] ?? '<p>Write your detailed semiconductor article or announcement here...</p>') ?></textarea>
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

    </div>

    <!-- Right Column: Sidebar Settings (4 cols) -->
    <div class="col-12 col-lg-4">
      
      <!-- Publish Settings Card -->
      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <h5 class="fw-bold text-dark mb-3">
          <i class="fa-solid fa-sliders text-primary me-2"></i>Publication
        </h5>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Status</label>
          <select name="status" class="form-select">
            <option value="published">🟢 Published (Live)</option>
            <option value="draft">🟡 Draft (Hidden)</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Publish Date & Time</label>
          <input type="datetime-local" name="published_at" class="form-control" 
                 value="<?= date('Y-m-d\TH:i') ?>">
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Display Priority / Sort Order</label>
          <input type="number" name="sort_order" class="form-control" value="0">
          <div class="form-text small">Lower numbers display first (e.g. 0, 1, 2).</div>
        </div>

        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" name="is_pinned" id="is_pinned" value="1">
          <label class="form-check-label fw-bold text-dark" for="is_pinned">
            <i class="fa-solid fa-thumbtack text-warning me-1"></i> Pin to Top of Lists
          </label>
        </div>

        <div class="form-check form-switch mb-4">
          <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1">
          <label class="form-check-label fw-bold text-dark" for="is_featured">
            <i class="fa-solid fa-star text-primary me-1"></i> Featured on Homepage
          </label>
        </div>

        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-primary fw-bold py-2">
            <i class="fa-solid fa-paper-plane me-1"></i> Publish Article
          </button>
        </div>
      </div>

      <!-- Category & Author Card -->
      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <h5 class="fw-bold text-dark mb-3">
          <i class="fa-solid fa-tag text-primary me-2"></i>Taxonomy & Author
        </h5>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Category <span class="text-danger">*</span></label>
          <select name="category_id" class="form-select" required>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-0">
          <label class="form-label small fw-semibold text-muted">Author / Organization</label>
          <input type="text" name="author" class="form-control" value="VNCHIP Editorial Team" placeholder="VNCHIP Editorial Team">
        </div>
      </div>

      <!-- Thumbnail Card -->
      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <h5 class="fw-bold text-dark mb-3">
          <i class="fa-solid fa-image text-primary me-2"></i>Featured Thumbnail
        </h5>

        <div class="text-center mb-3 p-2 bg-light rounded-3 border">
          <img id="thumb-preview" src="<?= asset('assets/images/media_htv9_event.webp') ?>" alt="" 
               style="max-width: 100%; height: 160px; object-fit: cover; border-radius: 8px;">
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Upload Thumbnail</label>
          <input type="file" name="thumbnail_file" id="thumb-file-input" class="form-control" accept="image/*">
          <div class="form-text small">Recommended: 1200x800px, max 3MB (WEBP/PNG/JPG).</div>
        </div>

        <div>
          <label class="form-label small fw-semibold text-muted">Or Select Preset</label>
          <select name="thumbnail_preset" class="form-select form-select-sm">
            <option value="assets/images/media_htv9_event.webp">Media Event HTV9 Interview</option>
            <option value="assets/images/business_rd_cube.webp">VNCHIP R&D Holographic Cube</option>
            <option value="assets/images/business_design_solutions.webp">Robot IC Bonding Design Solutions</option>
            <option value="assets/images/partner_tsmc_layout.webp">TSMC N3E / FinFET Block Diagram</option>
          </select>
        </div>
      </div>

    </div>
  </div>
</form>

<!-- CKEditor and Ace Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // 1. Slug generator helper
  const titleInput = document.getElementById('article-title');
  const slugInput = document.getElementById('article-slug');
  const btnSlugGen = document.getElementById('btn-slug-generate');

  function makeSlug(text) {
    return text.toString().toLowerCase().trim()
      .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .replace(/đ/g, 'd').replace(/Đ/g, 'd')
      .replace(/[^a-z0-9 -]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-');
  }

  if (titleInput && slugInput) {
    titleInput.addEventListener('keyup', function() {
      if (!slugInput.dataset.manual) {
        slugInput.value = makeSlug(titleInput.value);
      }
    });
    slugInput.addEventListener('input', function() {
      slugInput.dataset.manual = 'true';
    });
  }

  if (btnSlugGen) {
    btnSlugGen.addEventListener('click', function() {
      if (titleInput.value) {
        slugInput.value = makeSlug(titleInput.value);
        slugInput.dataset.manual = 'true';
        showToast('Slug generated from title', 'info');
      }
    });
  }

  // 2. Thumbnail Preview
  const thumbInput = document.getElementById('thumb-file-input');
  const thumbPreview = document.getElementById('thumb-preview');
  if (thumbInput && thumbPreview) {
    thumbInput.addEventListener('change', function(e) {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(ev) {
          thumbPreview.src = ev.target.result;
        }
        reader.readAsDataURL(this.files[0]);
      }
    });
  }

  // 3. Initialize Visual & Ace Dual Editors
  const masterContent = document.getElementById('master-content');
  const visualContainer = document.getElementById('editor-visual-container');
  const codeContainer = document.getElementById('editor-code-container');
  const btnModeVisual = document.getElementById('btn-mode-visual');
  const btnModeCode = document.getElementById('btn-mode-code');

  let currentMode = 'visual';
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
    aceInstance.setValue(masterContent.value || '<p>Write your detailed semiconductor article or announcement here...</p>', -1);
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
  btnModeVisual.addEventListener('click', function() {
    if (currentMode === 'visual') return;
    currentMode = 'visual';

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

  document.getElementById('article-form').addEventListener('submit', function() {
    if (currentMode === 'visual' && ckInstance) {
      masterContent.value = ckInstance.getData();
    } else if (currentMode === 'code' && aceInstance) {
      masterContent.value = aceInstance.getValue();
    }
  });

});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
