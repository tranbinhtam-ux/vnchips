<?php
/**
 * Admin Create / Edit Team Member
 * Enhanced with Live Preview, Instant Slug Generator, and Semiconductor Styling.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/upload.php';
require_once __DIR__ . '/../models/Member.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$member = $id ? Member::getById($id) : null;

if ($id && !$member) {
    header("Location: " . admin_url('members.php'));
    exit;
}

$member = $member ?? [
    'slug' => '', 'name' => '', 'position' => '', 'short_position' => '', 'image_url' => '', 'bio' => '',
    'sort_order' => count(Member::getAll()) + 1, 'linkedin_url' => '', 'twitter_url' => '', 'email_address' => '', 'is_active' => 1,
    'team_group' => ($_GET['group'] ?? '') === 'key_team' ? 'key_team' : 'leadership',
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'slug' => trim($_POST['slug'] ?? ''),
        'name' => trim($_POST['name'] ?? ''),
        'position' => trim($_POST['position'] ?? ''),
        'short_position' => trim($_POST['short_position'] ?? ''),
        'bio' => trim($_POST['bio'] ?? ''),
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
        'linkedin_url' => trim($_POST['linkedin_url'] ?? ''),
        'twitter_url' => trim($_POST['twitter_url'] ?? ''),
        'email_address' => trim($_POST['email_address'] ?? ''),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'team_group' => trim($_POST['team_group'] ?? 'leadership'),
    ];
    $data['image_url'] = adminUploadField('avatar_file', 'members', 'avatar', trim($_POST['image_url'] ?? ''), $errors);

    if ($data['name'] === '' || $data['position'] === '') {
        $errors[] = 'Please fill in Full Name and Position.';
    }
    if ($data['email_address'] !== '' && !filter_var($data['email_address'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email address is not valid.';
    }

    if (empty($errors)) {
        if (Member::save($id ?: null, $data)) {
            header("Location: " . admin_url('members.php?saved=1'));
            exit;
        }
        $errors[] = 'Database error saving member.';
    }

    $member = array_merge($member, $data);
}

$adminTitle = $id ? 'Edit Member #' . $id : 'Add New Member';
$adminActivePage = 'members';

require_once __DIR__ . '/includes/header.php';
?>

<form action="<?= admin_url('member-edit.php' . ($id ? '?id=' . $id : '')) ?>" method="POST" enctype="multipart/form-data">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
      <div class="text-muted small mb-1">
        <a href="<?= admin_url('members.php') ?>" class="text-decoration-none text-muted">
          <i class="fa-solid fa-arrow-left me-1"></i> Back to Members
        </a>
      </div>
      <h3 class="mb-0 fw-bold text-dark">
        <i class="fa-solid <?= $id ? 'fa-user-pen' : 'fa-user-plus' ?> text-primary me-2"></i>
        <?= $id ? 'Edit Member: ' . e($member['name']) : 'Add New Member' ?>
      </h3>
    </div>
    <div class="d-flex gap-2">
      <?php if ($id && !empty($member['slug'])): ?>
        <a href="<?= url('bio.php?id=' . urlencode($member['slug'])) ?>" target="_blank" class="btn btn-outline-secondary">
          <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View Bio Page
        </a>
      <?php endif; ?>
      <a href="<?= admin_url('members.php') ?>" class="btn btn-outline-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary px-3">
        <i class="fa-solid fa-check me-1"></i> <?= $id ? 'Update Member' : 'Save Member' ?>
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
    <!-- Left Column: Details & Bio (8 cols) -->
    <div class="col-12 col-lg-8">
      
      <!-- Primary Info Card -->
      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-id-card text-primary me-2"></i>Professional Profile</h5>
        
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-bold text-dark">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="member-name" class="form-control" required 
                   value="<?= e($member['name']) ?>" placeholder="e.g. LUC DUC TRI">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">URL Slug (bio.php?id=...)</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light text-muted">bio.php?id=</span>
              <input type="text" name="slug" id="member-slug" class="form-control" 
                     value="<?= e($member['slug']) ?>" placeholder="luc-duc-tri">
              <button type="button" class="btn btn-outline-secondary" id="btn-slug-gen" title="Generate from name">
                <i class="fa-solid fa-rotate"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">Primary Position / Title <span class="text-danger">*</span></label>
            <input type="text" name="position" class="form-control" required 
                   value="<?= e($member['position']) ?>" placeholder="e.g. Chief Executive Officer (CEO)">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">Card Role (Short summary on About card)</label>
            <input type="text" name="short_position" class="form-control" 
                   value="<?= e($member['short_position']) ?>" placeholder="e.g. Co-Founder & CEO">
          </div>
        </div>

        <div>
          <label class="form-label small fw-semibold text-muted">Full Biography (Supports paragraphs & HTML)</label>
          <textarea name="bio" id="bio-editor" rows="10" class="form-control" 
                    placeholder="Enter full career biography, achievements, and contributions..."><?= e($member['bio']) ?></textarea>
        </div>
      </div>

      <!-- Social & Contact Info Card -->
      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-share-nodes text-primary me-2"></i>Contact & Social Connections</h5>
        
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted"><i class="fa-brands fa-linkedin text-primary me-1"></i> LinkedIn Profile URL</label>
            <input type="url" name="linkedin_url" class="form-control" 
                   value="<?= e($member['linkedin_url']) ?>" placeholder="https://linkedin.com/in/...">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted"><i class="fa-brands fa-x-twitter me-1"></i> Twitter / X Profile URL</label>
            <input type="url" name="twitter_url" class="form-control" 
                   value="<?= e($member['twitter_url']) ?>" placeholder="https://x.com/...">
          </div>
        </div>

        <div>
          <label class="form-label small fw-semibold text-muted"><i class="fa-solid fa-envelope text-danger me-1"></i> Email Address</label>
          <input type="email" name="email_address" class="form-control" 
                 value="<?= e($member['email_address']) ?>" placeholder="executive@vnchips.com">
        </div>
      </div>

    </div>

    <!-- Right Column: Settings & Photo (4 cols) -->
    <div class="col-12 col-lg-4">
      
      <!-- Organization & Status Card -->
      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-sliders text-primary me-2"></i>Status & Hierarchy</h5>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Section on About Us Page</label>
          <select name="team_group" class="form-select">
            <?php foreach (Member::GROUPS as $value => $label): ?>
              <option value="<?= $value ?>" <?= Member::groupOf($member) === $value ? 'selected' : '' ?>>
                <?= e($label) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="form-text small">Leadership = large portrait cards; Key Team = circular avatar cards.</div>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Sort Order</label>
          <input type="number" name="sort_order" class="form-control" value="<?= (int)$member['sort_order'] ?>">
          <div class="form-text small">Lower numbers display first (0, 1, 2...).</div>
        </div>

        <div class="form-check form-switch mb-4">
          <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" 
                 <?= !empty($member['is_active']) ? 'checked' : '' ?>>
          <label class="form-check-label fw-bold text-dark" for="is_active">
            <i class="fa-solid fa-eye text-success me-1"></i> Visible on Website
          </label>
        </div>

        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-primary fw-bold py-2">
            <i class="fa-solid fa-floppy-disk me-1"></i> Save Member Profile
          </button>
        </div>
      </div>

      <!-- Photo Card -->
      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-user-tie text-primary me-2"></i>Portrait Photo</h5>

        <div class="text-center mb-3 p-3 bg-light rounded-3 border">
          <img id="avatar-preview" src="<?= asset($member['image_url'] ?: 'images/favicon.png') ?>" alt="" 
               style="width: 140px; height: 180px; object-fit: cover; object-position: top center; border-radius: 12px; border: 2px solid #E2E8F0;"
               onerror="this.src='<?= asset('assets/images/logo.png') ?>'">
          <div class="small text-muted mt-2 text-truncate"><?= e($member['image_url'] ?: 'No photo set') ?></div>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Upload Portrait Photo</label>
          <input type="file" name="avatar_file" id="avatar-file-input" class="form-control" accept="image/*">
          <div class="form-text small">Recommended: 2:3 ratio (e.g. 600x900px), PNG or WEBP.</div>
        </div>

        <div>
          <label class="form-label small fw-semibold text-muted">Or Direct Image Path</label>
          <input type="text" name="image_url" class="form-control form-control-sm" 
                 value="<?= e($member['image_url']) ?>" placeholder="uploads/members/photo.jpg">
        </div>
      </div>

    </div>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Slug generator
  const nameInput = document.getElementById('member-name');
  const slugInput = document.getElementById('member-slug');
  const btnSlug = document.getElementById('btn-slug-gen');

  function makeSlug(text) {
    return text.toString().toLowerCase().trim()
      .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .replace(/đ/g, 'd').replace(/Đ/g, 'd')
      .replace(/[^a-z0-9 -]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-');
  }

  if (nameInput && slugInput && btnSlug) {
    nameInput.addEventListener('keyup', function() {
      if (!slugInput.dataset.manual && !slugInput.value) {
        slugInput.value = makeSlug(nameInput.value);
      }
    });
    slugInput.addEventListener('input', function() {
      slugInput.dataset.manual = 'true';
    });
    btnSlug.addEventListener('click', function() {
      if (nameInput.value) {
        slugInput.value = makeSlug(nameInput.value);
        slugInput.dataset.manual = 'true';
        showToast('Slug generated', 'info');
      }
    });
  }

  // Avatar preview
  const fileInput = document.getElementById('avatar-file-input');
  const preview = document.getElementById('avatar-preview');
  if (fileInput && preview) {
    fileInput.addEventListener('change', function(e) {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(ev) {
          preview.src = ev.target.result;
        }
        reader.readAsDataURL(this.files[0]);
      }
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
