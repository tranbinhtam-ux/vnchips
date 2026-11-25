<?php
/**
 * Admin Media Library
 * Enhanced with Drag-and-Drop Dropzone, Automatic WebP Optimization, 4-Column High-Tech Grid, and 1-Click Copy with Toasts.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/upload.php';

const MEDIA_IMAGE_EXTS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
const MEDIA_VIDEO_EXTS = ['mp4', 'webm', 'mov'];
const MEDIA_DOC_EXTS = ['pdf', 'doc', 'docx', 'zip'];

$db = Database::getInstance()->getConnection();
$uploaded = [];
$errors = [];
$message = '';

/**
 * Re-encode a JPG/PNG/WebP upload as WebP (quality 80)
 */
function mediaConvertToWebp($srcPath, $destPath) {
    if (!function_exists('imagewebp')) return false;
    $info = @getimagesize($srcPath);
    if (!$info) return false;

    switch ($info['mime']) {
        case 'image/jpeg': $img = @imagecreatefromjpeg($srcPath); break;
        case 'image/png':
            $img = @imagecreatefrompng($srcPath);
            if ($img) { imagepalettetotruecolor($img); imagealphablending($img, true); imagesavealpha($img, true); }
            break;
        case 'image/webp': $img = @imagecreatefromwebp($srcPath); break;
        default: return false;
    }
    if (!$img) return false;

    $ok = imagewebp($img, $destPath, 80);
    imagedestroy($img);
    return $ok;
}

// Handle Upload (multiple files)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['media_files']['name'][0]) && $db) {
    $dir = __DIR__ . '/../uploads/media/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $insert = $db->prepare("INSERT INTO `media` (`file_name`, `file_path`, `file_size`) VALUES (?, ?, ?)");

    foreach ($_FILES['media_files']['name'] as $i => $origName) {
        $file = [
            'name' => $origName,
            'tmp_name' => $_FILES['media_files']['tmp_name'][$i],
            'error' => $_FILES['media_files']['error'][$i],
            'size' => $_FILES['media_files']['size'][$i],
        ];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if ($file['error'] === UPLOAD_ERR_OK && in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $fileName = 'media_' . time() . '_' . $i . '.webp';
            if (mediaConvertToWebp($file['tmp_name'], $dir . $fileName)) {
                $path = 'uploads/media/' . $fileName;
                $insert->execute([$fileName, $path, adminFormatBytes(filesize($dir . $fileName))]);
                $uploaded[] = $origName;
                continue;
            }
        }

        $result = adminStoreUpload($file, 'media', 'media', array_merge(MEDIA_IMAGE_EXTS, MEDIA_VIDEO_EXTS, MEDIA_DOC_EXTS));
        if (isset($result['error'])) {
            $errors[] = $origName . ': ' . $result['error'];
            continue;
        }
        $insert->execute([$result['name'], $result['path'], adminFormatBytes($result['size'])]);
        $uploaded[] = $origName;
    }

    if ($uploaded) {
        $message = count($uploaded) . ' asset(s) successfully uploaded and cataloged.';
    }
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id']) && $db) {
    $stmt = $db->prepare("SELECT `file_path` FROM `media` WHERE `id` = ?");
    $stmt->execute([(int)$_GET['id']]);
    $path = $stmt->fetchColumn();

    if ($path !== false) {
        $db->prepare("DELETE FROM `media` WHERE `id` = ?")->execute([(int)$_GET['id']]);
        $full = realpath(__DIR__ . '/../' . $path);
        $uploadsRoot = realpath(__DIR__ . '/../uploads');
        if ($full && $uploadsRoot && strpos($full, $uploadsRoot) === 0 && is_file($full)) {
            @unlink($full);
        }
        $message = 'Media file removed successfully.';
    }
}

$keyword = trim($_GET['q'] ?? '');
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 24;
$total = 0;
$totalPages = 1;
$items = [];
if ($db) {
    try {
        $countStmt = $db->prepare("SELECT COUNT(*) FROM `media` WHERE `file_name` LIKE ?");
        $countStmt->execute(['%' . $keyword . '%']);
        $total = (int)$countStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $limit));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $limit;

        $stmt = $db->prepare("SELECT * FROM `media` WHERE `file_name` LIKE ? ORDER BY `created_at` DESC, `id` DESC LIMIT $limit OFFSET $offset");
        $stmt->execute(['%' . $keyword . '%']);
        $items = $stmt->fetchAll();
    } catch (PDOException $e) {
        $errors[] = 'Media database table error.';
    }
}

$siteOrigin = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'vnchips.trantam.net');

$adminTitle = 'Media Asset Library';
$adminActivePage = 'media';

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-panel mb-4">
  <div class="panel-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <h3 class="panel-title mb-1">
        <i class="fa-solid fa-photo-film text-primary me-2"></i>Media Asset Library
      </h3>
      <p class="text-muted small mb-0">Upload and catalog semiconductor photos, silicon diagrams, banners, and technical whitepapers</p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= admin_url('banners.php') ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-images me-1"></i> Hero Banners
      </a>
    </div>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fa-solid fa-circle-check me-2"></i><?= e($message) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fa-solid fa-circle-exclamation me-2"></i><?= e($err) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endforeach; ?>

  <!-- Drag-and-drop Upload Zone -->
  <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px; background: #f8fafc; border: 2px dashed #cbd5e1 !important;">
    <form action="<?= admin_url('media.php') ?>" method="POST" enctype="multipart/form-data" id="upload-form">
      <div class="text-center py-4">
        <div class="fs-1 text-primary mb-2"><i class="fa-solid fa-cloud-arrow-up"></i></div>
        <h5 class="fw-bold text-dark mb-1">Drag & Drop Assets Here or Click to Browse</h5>
        <p class="text-muted small mb-3">
          Automatic WebP compression (quality 80) for high-speed page loads. Supports JPG, PNG, WEBP, GIF, MP4, WEBM, PDF (up to <?= ini_get('upload_max_filesize') ?>).
        </p>
        <div class="d-inline-block">
          <input type="file" name="media_files[]" id="file-dropzone-input" class="form-control" multiple 
                 accept=".jpg,.jpeg,.png,.webp,.gif,.mp4,.webm,.mov,.pdf" style="display: none;">
          <button type="button" class="btn btn-primary px-4 py-2" onclick="document.getElementById('file-dropzone-input').click();">
            <i class="fa-solid fa-folder-open me-2"></i> Select Files
          </button>
        </div>
        <div id="file-selected-info" class="small text-primary mt-2 fw-semibold" style="display: none;"></div>
      </div>
    </form>
  </div>

  <!-- Search & Filter Bar -->
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div class="fw-bold text-dark fs-5">
      All Assets (<?= (int)$total ?>)
    </div>
    <form action="<?= admin_url('media.php') ?>" method="GET" class="d-flex gap-2">
      <input type="text" name="q" value="<?= e($keyword) ?>" placeholder="Search by filename..." class="form-control form-control-sm" style="width: 250px;">
      <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i></button>
      <?php if ($keyword): ?>
        <a href="<?= admin_url('media.php') ?>" class="btn btn-outline-secondary btn-sm" title="Clear search"><i class="fa-solid fa-rotate-left"></i></a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Media Grid -->
  <?php if (empty($items)): ?>
    <div class="text-center py-5 text-muted">
      <i class="fa-regular fa-images fs-1 d-block mb-2"></i>
      No media files found matching your search.
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($items as $item): ?>
        <?php
          $ext = strtolower(pathinfo($item['file_path'], PATHINFO_EXTENSION));
          $isImage = in_array($ext, MEDIA_IMAGE_EXTS, true);
          $isVideo = in_array($ext, MEDIA_VIDEO_EXTS, true);
          $fileUrl = asset($item['file_path']);
        ?>
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
          <div class="card h-100 border-0 shadow-sm overflow-hidden" style="border-radius: 12px; background: #ffffff;">
            <!-- Thumbnail View -->
            <div style="height: 150px; background: #0f172a; position: relative; display: flex; align-items: center; justify-content: center; overflow: hidden;">
              <?php if ($isImage): ?>
                <img src="<?= $fileUrl ?>" alt="" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
                     onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
              <?php elseif ($isVideo): ?>
                <div class="text-center text-white">
                  <i class="fa-solid fa-video fs-1 text-primary"></i>
                  <div class="small mt-1">Video Asset</div>
                </div>
              <?php else: ?>
                <div class="text-center text-white">
                  <i class="fa-solid fa-file-pdf fs-1 text-danger"></i>
                  <div class="small mt-1">PDF Document</div>
                </div>
              <?php endif; ?>
              
              <span class="badge bg-dark text-white-50 position-absolute top-0 end-0 m-2 text-uppercase" style="font-size: 0.65rem;">
                <?= $ext ?>
              </span>
            </div>

            <!-- Card Body -->
            <div class="p-3 d-flex flex-column justify-content-between flex-grow-1">
              <div>
                <div class="fw-bold text-dark text-truncate small mb-1" title="<?= e($item['file_name']) ?>">
                  <?= e($item['file_name']) ?>
                </div>
                <div class="text-muted" style="font-size: 0.72rem;">
                  <span><?= e($item['file_size']) ?></span> &bull; 
                  <span><?= formatDate($item['created_at'], 'M d, Y') ?></span>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="mt-3 pt-2 border-top d-flex gap-1 justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm flex-grow-1 px-2 py-1" 
                        onclick="copyToClipboard('<?= e($item['file_path']) ?>', 'Relative path copied!');" 
                        title="Copy relative path: <?= e($item['file_path']) ?>">
                  <i class="fa-solid fa-copy me-1"></i>Path
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1 px-2 py-1" 
                        onclick="copyToClipboard('<?= e($siteOrigin . $fileUrl) ?>', 'Full URL copied!');" 
                        title="Copy full public URL">
                  <i class="fa-solid fa-link me-1"></i>URL
                </button>
                <a href="<?= admin_url('media.php?action=delete&id=' . $item['id']) ?>" 
                   onclick="return confirm('Delete <?= addslashes($item['file_name']) ?> permanently?');"
                   class="btn btn-outline-danger btn-sm px-2 py-1" title="Delete Asset">
                  <i class="fa-solid fa-trash"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 pt-3 border-top">
      <div class="text-muted small">
        Page <?= $page ?> of <?= $totalPages ?> (Total <?= $total ?> items)
      </div>
      <nav>
        <ul class="pagination pagination-sm mb-0">
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
              <a class="page-link" href="<?= admin_url('media.php?page=' . $i . ($keyword ? '&q=' . urlencode($keyword) : '')) ?>">
                <?= $i ?>
              </a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    </div>
  <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const fileInput = document.getElementById('file-dropzone-input');
  const uploadForm = document.getElementById('upload-form');
  const infoEl = document.getElementById('file-selected-info');

  if (fileInput) {
    fileInput.addEventListener('change', function() {
      if (this.files.length > 0) {
        infoEl.style.display = 'block';
        infoEl.textContent = `Uploading ${this.files.length} file(s)... Please wait.`;
        uploadForm.submit();
      }
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
