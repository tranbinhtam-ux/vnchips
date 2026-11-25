<?php
/**
 * Admin Hero Banners Management
 * Manage full-width hero backgrounds and video loops across all frontend templates.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/upload.php';

$slots = [
    'home_hero_video' => [
        'label' => 'Homepage Hero Video Background',
        'pages' => 'Home (index.php)',
        'default' => 'videos/vecteezy_a-computer-chip-with-glowing-blue-lines_76491895.mp4',
        'video' => true,
        'hint' => 'Looping semiconductor animation background. Supports MP4, WebM (recommended 1080p, max 20MB).'
    ],
    'hero_technology' => [
        'label' => 'Technology & R&D Hero Banner',
        'pages' => 'Technology (technology.php)',
        'default' => 'images/hero_chip_bg.webp',
        'hint' => 'Full-bleed semiconductor imagery for Core Technology & Verification suites.'
    ],
    'hero_business' => [
        'label' => 'Business Solutions Hero Banner',
        'pages' => 'Business (business.php)',
        'default' => 'images/business_hero_pcb.webp',
        'hint' => 'Hero background for IP Design, ASIC Turnkey, and Embedded solutions.'
    ],
    'hero_partners' => [
        'label' => 'Partners & Ecosystem Hero Banner',
        'pages' => 'Partners (partners.php) & Case Studies',
        'default' => 'images/hero_chip_bg.webp',
        'hint' => 'Ecosystem header for TSMC, Cadence, and university research alliances.'
    ],
    'hero_about' => [
        'label' => 'About Us & Leadership Hero Banner',
        'pages' => 'About Us (about-us.php) & Biographies (bio.php)',
        'default' => 'images/about_workspace_bg.webp',
        'hint' => 'Corporate background for executive leadership and company vision.'
    ],
    'hero_sustainable' => [
        'label' => 'Sustainability & ESG Hero Banner',
        'pages' => 'Sustainability (sustainable.php)',
        'default' => 'images/sustainable_hero_pcb.webp',
        'hint' => 'Environmental stewardship and green semiconductor manufacturing initiatives.'
    ],
];

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = [];
    foreach ($slots as $key => $slot) {
        // Reset to default
        if (!empty($_POST['reset'][$key])) {
            $updates[$key] = '';
            continue;
        }

        // Upload new file
        if (!empty($_FILES[$key]['name'])) {
            $exts = !empty($slot['video']) ? ['mp4', 'webm', 'mov'] : ['jpg', 'jpeg', 'png', 'webp'];
            $result = adminStoreUpload($_FILES[$key], 'banners', $key, $exts);
            if (isset($result['error'])) {
                $errors[] = $slot['label'] . ': ' . $result['error'];
            } else {
                $updates[$key] = $result['path'];
            }
        } elseif (isset($_POST['path'][$key])) {
            // Manual path edit
            $manualPath = trim($_POST['path'][$key]);
            $currentVal = Setting::get($key);
            if ($manualPath !== '' && $manualPath !== $currentVal) {
                $updates[$key] = $manualPath;
            }
        }
    }

    if ($updates && Setting::updateBatch($updates, 'banners')) {
        $message = 'Hero banners and video loops updated successfully!';
    } elseif (!$updates && !$errors) {
        $message = 'No changes made.';
    }
}

$adminTitle = 'Hero Banners & Visual Loops';
$adminActivePage = 'banners';

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-panel mb-4">
  <div class="panel-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <h3 class="panel-title mb-1">
        <i class="fa-solid fa-images text-primary me-2"></i>Hero Banners & Visual Loops
      </h3>
      <p class="text-muted small mb-0">Customize hero backgrounds, 4K loop videos, and graphic overlays across all public pages</p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= admin_url('media.php') ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-photo-film me-1"></i> Open Media Library
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

  <form action="<?= admin_url('banners.php') ?>" method="POST" enctype="multipart/form-data">
    <div class="row g-4">
      <?php foreach ($slots as $key => $slot): ?>
        <?php 
          $current = Setting::getOr($key, $slot['default']);
          $isDefault = ($current === $slot['default'] || empty($current));
          $displayAsset = $current ?: $slot['default'];
        ?>
        <div class="col-12 col-xl-6">
          <div class="card h-100 border-0 shadow-sm p-4" style="border-radius: 14px; background: #ffffff;">
            
            <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
              <div>
                <h5 class="fw-bold text-dark mb-0"><?= e($slot['label']) ?></h5>
                <span class="badge bg-primary-subtle text-primary mt-1" style="font-size: 0.72rem;">
                  <i class="fa-solid fa-compass me-1"></i><?= e($slot['pages']) ?>
                </span>
              </div>
              <?php if (!$isDefault): ?>
                <span class="badge bg-success-subtle text-success">Custom Asset</span>
              <?php else: ?>
                <span class="badge bg-secondary-subtle text-muted">Theme Default</span>
              <?php endif; ?>
            </div>

            <p class="text-muted small mb-3"><?= e($slot['hint']) ?></p>

            <!-- Media Preview -->
            <div class="mb-3 rounded-3 overflow-hidden border" style="height: 180px; background: #0f172a; position: relative; display: flex; align-items: center; justify-content: center;">
              <?php if (!empty($slot['video'])): ?>
                <video src="<?= asset($displayAsset) ?>" style="width: 100%; height: 100%; object-fit: cover;" muted loop autoplay playsinline></video>
              <?php else: ?>
                <img src="<?= asset($displayAsset) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;"
                     onerror="this.src='<?= asset('images/hero_chip_bg.webp') ?>'">
              <?php endif; ?>

              <div class="position-absolute bottom-0 start-0 end-0 p-2 bg-dark bg-opacity-75 text-white-50 d-flex justify-content-between align-items-center small">
                <span class="text-truncate me-2" style="max-width: 80%;"><?= e($displayAsset) ?></span>
                <button type="button" class="btn btn-dark btn-sm text-white border-0 py-0 px-2" 
                        onclick="copyToClipboard('<?= e($displayAsset) ?>', 'Asset path copied!');" title="Copy Path">
                  <i class="fa-solid fa-copy"></i>
                </button>
              </div>
            </div>

            <!-- Upload Field -->
            <div class="mb-3">
              <label class="form-label small fw-semibold text-muted">Upload New <?= !empty($slot['video']) ? 'Video' : 'Banner' ?></label>
              <input type="file" name="<?= e($key) ?>" class="form-control form-control-sm" 
                     accept="<?= !empty($slot['video']) ? 'video/mp4,video/webm' : 'image/*' ?>">
            </div>

            <!-- Path Field -->
            <div class="mb-3">
              <label class="form-label small fw-semibold text-muted">Or Enter Asset Path</label>
              <input type="text" name="path[<?= e($key) ?>]" class="form-control form-control-sm" 
                     value="<?= e($current) ?>" placeholder="<?= e($slot['default']) ?>">
            </div>

            <!-- Reset Toggle -->
            <?php if (!$isDefault): ?>
              <div class="form-check form-switch pt-1 border-top">
                <input class="form-check-input" type="checkbox" name="reset[<?= e($key) ?>]" id="reset_<?= e($key) ?>" value="1">
                <label class="form-check-label small text-muted" for="reset_<?= e($key) ?>">
                  <i class="fa-solid fa-rotate-left me-1"></i> Reset to theme default asset
                </label>
              </div>
            <?php endif; ?>

          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Sticky Save Bar -->
    <div class="card border-0 shadow-sm p-3 mt-4 d-flex flex-row justify-content-between align-items-center" style="border-radius: 12px; background: #f8fafc;">
      <div class="small text-muted">
        <i class="fa-solid fa-info-circle text-primary me-1"></i>
        Banners will instantly apply to all visitor sessions across the frontend website.
      </div>
      <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
        <i class="fa-solid fa-floppy-disk me-1"></i> Save All Banners
      </button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
