<?php
/**
 * Admin Partner Case Studies Management
 * Enhanced with SortableJS reordering, Pin-to-top toggle, and 1-click Status toggles.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../models/Partner.php';

$message = '';
$error = '';

// Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    if (Partner::delete((int)$_GET['id'])) {
        $message = 'Partner deleted successfully.';
    } else {
        $error = 'Failed to delete partner.';
    }
}

if (isset($_GET['saved'])) {
    $message = 'Partner saved successfully!';
}

$partners = Partner::getAll();

// Stats
$totalCount = count($partners);
$publishedCount = count(array_filter($partners, fn($p) => $p['status'] === 'published'));
$draftCount = count(array_filter($partners, fn($p) => $p['status'] === 'draft'));
$pinnedCount = count(array_filter($partners, fn($p) => !empty($p['is_pinned'])));

$adminTitle = 'Partner Case Studies & Collaborations';
$adminActivePage = 'partners';

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-panel mb-4">
  <div class="panel-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <h3 class="panel-title mb-1">
        <i class="fa-solid fa-handshake text-primary me-2"></i>Partner Case Studies & Collaborations
      </h3>
      <p class="text-muted small mb-0">Shown on the Partners page: pinned items first, then by sort order (lowest first)</p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= url('partners.php') ?>" target="_blank" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View Live Page
      </a>
      <a href="<?= admin_url('partner-edit.php') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i> Add Partner Case
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

  <!-- Stats Pills -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">Total Partners</div>
          <div class="fs-4 fw-bold text-dark"><?= $totalCount ?></div>
        </div>
        <div class="text-primary fs-3"><i class="fa-solid fa-handshake"></i></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">Published</div>
          <div class="fs-4 fw-bold text-success"><?= $publishedCount ?></div>
        </div>
        <div class="text-success fs-3"><i class="fa-solid fa-circle-check"></i></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">Pinned Priority</div>
          <div class="fs-4 fw-bold text-warning"><?= $pinnedCount ?></div>
        </div>
        <div class="text-warning fs-3"><i class="fa-solid fa-thumbtack"></i></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">Draft / Hidden</div>
          <div class="fs-4 fw-bold text-muted"><?= $draftCount ?></div>
        </div>
        <div class="text-muted fs-3"><i class="fa-solid fa-pen-ruler"></i></div>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-between align-items-center mb-2 px-1">
    <div class="small text-muted">
      <i class="fa-solid fa-info-circle text-primary me-1"></i>
      Drag <i class="fa-solid fa-grip-vertical text-muted"></i> to reorder. Changes are saved automatically.
    </div>
    <div class="small text-muted">
      Showing <strong><?= count($partners) ?></strong> partner records
    </div>
  </div>

  <!-- Partners Table -->
  <div class="table-responsive">
    <table class="admin-table align-middle">
      <thead>
        <tr>
          <th style="width: 40px; text-align: center;"><i class="fa-solid fa-arrows-up-down text-muted"></i></th>
          <th style="width: 80px;">Logo / Img</th>
          <th>Partner Title & Slug</th>
          <th>Category</th>
          <th style="width: 100px; text-align: center;">Pinned</th>
          <th style="width: 100px; text-align: center;">Status</th>
          <th style="width: 120px;">Created</th>
          <th style="text-align: right; width: 140px;">Actions</th>
        </tr>
      </thead>
      <tbody id="partners-sortable-list">
        <?php if (empty($partners)): ?>
          <tr>
            <td colspan="8" class="text-center py-4 text-muted">No partners found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($partners as $p): ?>
            <tr data-id="<?= $p['id'] ?>" class="<?= !empty($p['is_pinned']) ? 'table-warning-subtle' : '' ?>" style="<?= !empty($p['is_pinned']) ? 'background: rgba(245, 158, 11, 0.05);' : '' ?>">
              <!-- Drag Handle -->
              <td style="text-align: center; cursor: grab;" class="drag-handle" title="Drag to reorder">
                <i class="fa-solid fa-grip-vertical text-muted"></i>
              </td>

              <!-- Image -->
              <td>
                <a href="<?= admin_url('partner-edit.php?id=' . $p['id']) ?>" title="Edit partner: <?= e($p['title']) ?>">
                  <img src="<?= asset($p['image_url'] ?: 'images/hero_chip_bg.webp') ?>" alt="" 
                       style="width: 64px; height: 44px; object-fit: cover; border-radius: 6px; border: 1px solid #E2E8F0;"
                       onerror="this.src='<?= asset('assets/images/logo.png') ?>'">
                </a>
              </td>

              <!-- Title & Slug -->
              <td>
                <div style="max-width: 380px; line-height: 1.35;">
                  <?php if (!empty($p['is_pinned'])): ?>
                    <span class="badge bg-warning text-dark me-1"><i class="fa-solid fa-thumbtack"></i> Pinned</span>
                  <?php endif; ?>
                  <a href="<?= admin_url('partner-edit.php?id=' . $p['id']) ?>" 
                     class="article-title-link text-decoration-none fw-bold" 
                     title="Click to edit: <?= e($p['title']) ?>">
                    <?= e($p['title']) ?>
                  </a>
                </div>
                <div class="small text-muted mt-1">
                  <code>partner-detail.php?slug=<?= e($p['slug']) ?></code>
                </div>
              </td>

              <!-- Category -->
              <td>
                <span class="badge bg-primary-subtle text-primary" style="font-size: 0.78rem;">
                  <?= e($p['category_name'] ?: 'Partners') ?>
                </span>
              </td>

              <!-- Pinned Toggle -->
              <td style="text-align: center;">
                <button type="button" class="btn btn-sm btn-partner-pin <?= !empty($p['is_pinned']) ? 'btn-warning' : 'btn-outline-secondary' ?>"
                        data-id="<?= $p['id'] ?>" title="Toggle Pin Priority">
                  <i class="fa-solid fa-thumbtack"></i>
                </button>
              </td>

              <!-- Status Toggle -->
              <td style="text-align: center;">
                <button type="button" class="btn btn-sm btn-partner-status <?= ($p['status'] === 'published') ? 'btn-success' : 'btn-secondary' ?>"
                        data-id="<?= $p['id'] ?>" title="Toggle Published / Draft">
                  <i class="fa-solid <?= ($p['status'] === 'published') ? 'fa-circle-check' : 'fa-pen-ruler' ?> me-1"></i>
                  <?= ucfirst($p['status']) ?>
                </button>
              </td>

              <!-- Created At -->
              <td style="font-size: 0.82rem; color: #64748B;">
                <?= formatDate($p['created_at']) ?>
              </td>

              <!-- Actions -->
              <td style="text-align: right;">
                <div class="btn-group btn-group-sm">
                  <a href="<?= url('partner-detail.php?slug=' . urlencode($p['slug'])) ?>" target="_blank" class="btn btn-outline-secondary" title="View live page">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                  </a>
                  <a href="<?= admin_url('partner-edit.php?id=' . $p['id']) ?>" class="btn btn-outline-secondary" title="Edit Partner">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>
                  <a href="<?= admin_url('partners.php?action=delete&id=' . $p['id']) ?>" 
                     onclick="return confirm('Are you sure you want to delete partner &quot;<?= addslashes($p['title']) ?>&quot;?');" 
                     class="btn btn-outline-danger" title="Delete Partner">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
  // 1. SortableJS
  const sortableList = document.getElementById('partners-sortable-list');
  if (sortableList && typeof Sortable !== 'undefined') {
    new Sortable(sortableList, {
      handle: '.drag-handle',
      animation: 150,
      ghostClass: 'table-active',
      onEnd: function() {
        const rows = sortableList.querySelectorAll('tr[data-id]');
        const order = Array.from(rows).map(row => row.getAttribute('data-id'));
        
        const formData = new FormData();
        formData.append('action', 'reorder_partners');
        formData.append('order', JSON.stringify(order));

        fetch('<?= admin_url("ajax/entity-actions.php") ?>', {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            showToast('Partner sequence updated successfully!', 'success');
          } else {
            showToast('Failed to save reordering.', 'error');
          }
        })
        .catch(err => {
          showToast('Network error updating order.', 'error');
        });
      }
    });
  }

  // 2. Partner Pin Toggle
  document.querySelectorAll('.btn-partner-pin').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const id = this.getAttribute('data-id');
      const formData = new FormData();
      formData.append('action', 'toggle_partner_pinned');
      formData.append('id', id);

      this.disabled = true;

      fetch('<?= admin_url("ajax/entity-actions.php") ?>', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        this.disabled = false;
        if (data.success) {
          showToast(data.message || 'Pin updated', 'success');
          if (data.is_pinned == 1) {
            this.className = 'btn btn-sm btn-partner-pin btn-warning';
            this.closest('tr').style.background = 'rgba(245, 158, 11, 0.05)';
          } else {
            this.className = 'btn btn-sm btn-partner-pin btn-outline-secondary';
            this.closest('tr').style.background = '';
          }
        } else {
          showToast('Failed to update pin', 'error');
        }
      })
      .catch(err => {
        this.disabled = false;
        showToast('Network error', 'error');
      });
    });
  });

  // 3. Partner Status Toggle
  document.querySelectorAll('.btn-partner-status').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const id = this.getAttribute('data-id');
      const formData = new FormData();
      formData.append('action', 'toggle_partner_status');
      formData.append('id', id);

      this.disabled = true;

      fetch('<?= admin_url("ajax/entity-actions.php") ?>', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        this.disabled = false;
        if (data.success) {
          showToast(data.message || 'Status updated', 'success');
          if (data.status === 'published') {
            this.className = 'btn btn-sm btn-partner-status btn-success';
            this.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Published';
          } else {
            this.className = 'btn btn-sm btn-partner-status btn-secondary';
            this.innerHTML = '<i class="fa-solid fa-pen-ruler me-1"></i> Draft';
          }
        } else {
          showToast('Failed to update status', 'error');
        }
      })
      .catch(err => {
        this.disabled = false;
        showToast('Network error', 'error');
      });
    });
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
