<?php
/**
 * Admin Global Settings: Branding, SEO, Social Links, Custom CSS & Analytics Scripts
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/upload.php';

// Image settings: key => [label, default, preview class]
$imageSettings = [
    'logo_header'     => ['Header Logo', 'images/logo_vnchip_labs.png', 'image-preview--contain'],
    'logo_footer'     => ['Footer Logo', 'images/logo_vnchip_labs.png', 'image-preview--contain'],
    'favicon_url'     => ['Favicon (PNG / ICO, square)', 'images/favicon.png', 'image-preview--contain'],
    'seo_share_image' => ['Social Share Image (og:image, 1200×630)', '', ''],
];

$textSettings = [
    'site_title_suffix' => 'VNCHIP Technology - Semiconductor & Turnkey IC Design',
    'seo_description' => 'VNCHIP Technology JSC is a leading turnkey IC design service provider in Vietnam, offering turnkey ASIC/SoC design, custom layout, and advanced semiconductor talent development.',
    'seo_keywords'    => 'vnchip, ic design, asic, semiconductor vietnam, cadence partner',
    'social_facebook' => 'https://www.facebook.com/vnchipinstitute',
    'social_linkedin' => 'https://www.linkedin.com/company/vnchip-technology-jsc/',
    'social_youtube'  => 'https://www.youtube.com/@vnchiptechnology2362',
    'custom_css'      => '',
    'custom_head_scripts' => '',
    'custom_footer_scripts' => '',
];

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = [];

    foreach ($imageSettings as $key => $cfg) {
        if (!empty($_POST['reset'][$key])) {
            $updates[$key] = '';
        } elseif (!empty($_FILES[$key]['name'])) {
            $result = adminStoreUpload($_FILES[$key], 'branding', $key);
            if (isset($result['error'])) {
                $errors[] = $cfg[0] . ': ' . $result['error'];
            } else {
                $updates[$key] = $result['path'];
            }
        }
    }

    foreach ($textSettings as $key => $default) {
        if (isset($_POST[$key])) {
            $updates[$key] = trim($_POST[$key]);
        }
    }

    foreach (['social_facebook', 'social_linkedin', 'social_youtube'] as $key) {
        if (isset($updates[$key]) && $updates[$key] !== '' && !filter_var($updates[$key], FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid URL for ' . ucfirst(substr($key, 7)) . '.';
            unset($updates[$key]);
        }
    }

    if (Setting::updateBatch($updates, 'site')) {
        $message = 'Global site settings and tracking codes saved successfully!';
    } else {
        $errors[] = 'Failed to update settings in database.';
    }
}

$adminTitle = 'Branding, SEO & Custom Scripts';
$adminActivePage = 'site-settings';

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-panel mb-4">
  <div class="panel-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <h3 class="panel-title mb-1">
        <i class="fa-solid fa-gears text-primary me-2"></i>Branding, SEO &amp; Custom Scripts
      </h3>
      <p class="text-muted small mb-0">Configure company logos, OpenGraph meta, social channels, and analytics tracking code</p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= url('index.php') ?>" target="_blank" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View Website
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

  <form action="<?= admin_url('site-settings.php') ?>" method="POST" enctype="multipart/form-data">

    <!-- 1. Branding Logos -->
    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
      <h5 class="fw-bold text-dark mb-1">
        <i class="fa-solid fa-palette text-primary me-2"></i>Company Branding &amp; Favicon
      </h5>
      <p class="text-muted small mb-4">Displayed on desktop header, mobile drawer, footer, and browser tabs</p>

      <div class="row g-4">
        <?php foreach ($imageSettings as $key => [$label, $default, $previewClass]): ?>
          <?php $current = Setting::getOr($key, $default); ?>
          <div class="col-12 col-md-6">
            <div class="p-3 rounded-3 border bg-light h-100">
              <label class="form-label fw-bold text-dark mb-2"><?= e($label) ?></label>
              
              <div class="d-flex align-items-center gap-3 mb-3 p-2 bg-white rounded-3 border">
                <?php if ($current !== ''): ?>
                  <img src="<?= asset($current) ?>" alt="" style="max-height: 50px; max-width: 140px; object-fit: contain;">
                <?php else: ?>
                  <div class="text-muted small py-2 px-3">No custom image set</div>
                <?php endif; ?>
                <div class="small text-muted text-truncate" style="max-width: 200px;">
                  <code><?= e($current !== '' ? $current : 'Default') ?></code>
                </div>
              </div>

              <div class="mb-2">
                <input type="file" name="<?= e($key) ?>" class="form-control form-control-sm" accept="image/*,.ico">
              </div>

              <?php if ($current !== $default && $current !== ''): ?>
                <div class="form-check form-switch pt-1">
                  <input class="form-check-input" type="checkbox" name="reset[<?= e($key) ?>]" id="reset_<?= e($key) ?>" value="1">
                  <label class="form-check-label small text-muted" for="reset_<?= e($key) ?>">
                    Reset to theme default
                  </label>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- 2. Search Engine Optimization (SEO) -->
    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
      <h5 class="fw-bold text-dark mb-1">
        <i class="fa-solid fa-magnifying-glass-chart text-info me-2"></i>Search Engine Optimization (SEO)
      </h5>
      <p class="text-muted small mb-4">Metadata crawled by Google, Bing, LinkedIn, and social media bots</p>

      <div class="mb-3">
        <label class="form-label small fw-semibold text-muted">Global Site Title Suffix</label>
        <input type="text" name="site_title_suffix" class="form-control" 
               value="<?= e(Setting::getOr('site_title_suffix', $textSettings['site_title_suffix'])) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label small fw-semibold text-muted">Default Meta Description</label>
        <textarea name="seo_description" rows="3" class="form-control"><?= e(Setting::getOr('seo_description', $textSettings['seo_description'])) ?></textarea>
      </div>

      <div class="mb-0">
        <label class="form-label small fw-semibold text-muted">Meta Keywords (comma separated)</label>
        <input type="text" name="seo_keywords" class="form-control" 
               value="<?= e(Setting::getOr('seo_keywords', $textSettings['seo_keywords'])) ?>">
      </div>
    </div>

    <!-- 3. Social Media Links -->
    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
      <h5 class="fw-bold text-dark mb-1">
        <i class="fa-solid fa-share-nodes text-primary me-2"></i>Social Channels &amp; Video Streams
      </h5>
      <p class="text-muted small mb-4">Linked in the header top bar, mobile menu, and global footer</p>

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label small fw-semibold text-muted"><i class="fa-brands fa-linkedin text-primary me-1"></i> LinkedIn Page</label>
          <input type="url" name="social_linkedin" class="form-control" 
                 value="<?= e(Setting::getOr('social_linkedin', $textSettings['social_linkedin'])) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold text-muted"><i class="fa-brands fa-facebook text-info me-1"></i> Facebook Fanpage</label>
          <input type="url" name="social_facebook" class="form-control" 
                 value="<?= e(Setting::getOr('social_facebook', $textSettings['social_facebook'])) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold text-muted"><i class="fa-brands fa-youtube text-danger me-1"></i> YouTube Channel</label>
          <input type="url" name="social_youtube" class="form-control" 
                 value="<?= e(Setting::getOr('social_youtube', $textSettings['social_youtube'])) ?>">
        </div>
      </div>
    </div>

    <!-- 4. Custom CSS & Tracking Code -->
    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
      <h5 class="fw-bold text-dark mb-1">
        <i class="fa-solid fa-code text-warning me-2"></i>Custom CSS &amp; Tracking Scripts (Ace Code Editor)
      </h5>
      <p class="text-muted small mb-4">Tùy biến CSS và mã nhúng theo dõi (Google Analytics, GTM, Pixel) với trình soạn thảo Ace tô màu cú pháp trực quan.</p>

      <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <label class="form-label small fw-semibold text-muted mb-0"><i class="fa-brands fa-css3-alt text-primary me-1"></i> Custom CSS Overrides</label>
          <span class="badge bg-dark text-info font-monospace" style="font-size: 0.72rem;">CSS Syntax Highlighting</span>
        </div>
        <textarea name="custom_css" id="custom_css" class="d-none"><?= e(Setting::get('custom_css', '')) ?></textarea>
        <div id="ace_custom_css" style="width: 100%; height: 180px; border-radius: 8px; border: 1px solid #1E293B; font-size: 13.5px;"></div>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label small fw-semibold text-muted mb-0"><i class="fa-brands fa-html5 text-danger me-1"></i> Head Scripts (GTAG, Google Tag Manager)</label>
            <span class="badge bg-dark text-warning font-monospace" style="font-size: 0.72rem;">HTML &lt;script&gt;</span>
          </div>
          <textarea name="custom_head_scripts" id="custom_head_scripts" class="d-none"><?= e(Setting::get('custom_head_scripts', '')) ?></textarea>
          <div id="ace_custom_head_scripts" style="width: 100%; height: 170px; border-radius: 8px; border: 1px solid #1E293B; font-size: 13.5px;"></div>
        </div>
        <div class="col-md-6">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label small fw-semibold text-muted mb-0"><i class="fa-brands fa-html5 text-danger me-1"></i> Footer Scripts (Live Chat, Pixel, Widget)</label>
            <span class="badge bg-dark text-warning font-monospace" style="font-size: 0.72rem;">HTML &lt;script&gt;</span>
          </div>
          <textarea name="custom_footer_scripts" id="custom_footer_scripts" class="d-none"><?= e(Setting::get('custom_footer_scripts', '')) ?></textarea>
          <div id="ace_custom_footer_scripts" style="width: 100%; height: 170px; border-radius: 8px; border: 1px solid #1E293B; font-size: 13.5px;"></div>
        </div>
      </div>
    </div>

    <!-- Sticky Save Bar -->
    <div class="card border-0 shadow-sm p-3 d-flex flex-row justify-content-between align-items-center" style="border-radius: 12px; background: #f8fafc;">
      <div class="small text-muted">
        <i class="fa-solid fa-circle-check text-success me-1"></i>
        Settings update globally for all visitors and search engines.
      </div>
      <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
        <i class="fa-solid fa-floppy-disk me-1"></i> Save Global Settings
      </button>
    </div>

  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof ace === 'undefined') return;

  const editors = {};

  // 1. Custom CSS
  const taCss = document.getElementById('custom_css');
  if (taCss) {
    const edCss = ace.edit('ace_custom_css');
    edCss.setTheme('ace/theme/tomorrow_night');
    edCss.session.setMode('ace/mode/css');
    edCss.setValue(taCss.value || '', -1);
    edCss.setOptions({
      fontSize: '13.5px',
      fontFamily: "'JetBrains Mono', 'Fira Code', 'Consolas', monospace",
      wrap: true,
      showPrintMargin: false,
      tabSize: 2
    });
    edCss.session.on('change', () => { taCss.value = edCss.getValue(); });
    editors['custom_css'] = edCss;
  }

  // 2. Head Scripts
  const taHead = document.getElementById('custom_head_scripts');
  if (taHead) {
    const edHead = ace.edit('ace_custom_head_scripts');
    edHead.setTheme('ace/theme/tomorrow_night');
    edHead.session.setMode('ace/mode/html');
    edHead.setValue(taHead.value || '', -1);
    edHead.setOptions({
      fontSize: '13.5px',
      fontFamily: "'JetBrains Mono', 'Fira Code', 'Consolas', monospace",
      wrap: true,
      showPrintMargin: false,
      tabSize: 2
    });
    edHead.session.on('change', () => { taHead.value = edHead.getValue(); });
    editors['custom_head_scripts'] = edHead;
  }

  // 3. Footer Scripts
  const taFoot = document.getElementById('custom_footer_scripts');
  if (taFoot) {
    const edFoot = ace.edit('ace_custom_footer_scripts');
    edFoot.setTheme('ace/theme/tomorrow_night');
    edFoot.session.setMode('ace/mode/html');
    edFoot.setValue(taFoot.value || '', -1);
    edFoot.setOptions({
      fontSize: '13.5px',
      fontFamily: "'JetBrains Mono', 'Fira Code', 'Consolas', monospace",
      wrap: true,
      showPrintMargin: false,
      tabSize: 2
    });
    edFoot.session.on('change', () => { taFoot.value = edFoot.getValue(); });
    editors['custom_footer_scripts'] = edFoot;
  }

  // Sync back before submit
  const form = document.querySelector('form');
  if (form) {
    form.addEventListener('submit', function() {
      for (const k in editors) {
        const ta = document.getElementById(k);
        if (ta && editors[k]) ta.value = editors[k].getValue();
      }
    });
  }
});
</script>

  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
