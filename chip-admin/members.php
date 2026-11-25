<?php
/**
 * Admin Team Members & Leadership Management
 * Enhanced with SortableJS drag-and-drop reordering and 1-click visibility toggles.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../models/Member.php';

$message = '';
$error = '';

// Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    if (Member::delete((int)$_GET['id'])) {
        $message = 'Member deleted successfully.';
    } else {
        $error = 'Failed to delete member.';
    }
}

if (isset($_GET['saved'])) {
    $message = 'Member saved successfully!';
}

$sectionFilter = $_GET['group'] ?? 'all';
$allMembers = Member::getAll();

$members = array_filter($allMembers, function($m) use ($sectionFilter) {
    if ($sectionFilter === 'all') return true;
    return Member::groupOf($m) === $sectionFilter;
});

// Stats
$totalCount = count($allMembers);
$leadershipCount = count(array_filter($allMembers, fn($m) => Member::groupOf($m) === 'leadership'));
$keyTeamCount = count(array_filter($allMembers, fn($m) => Member::groupOf($m) === 'key_team'));
$activeCount = count(array_filter($allMembers, fn($m) => !empty($m['is_active'])));

$adminTitle = 'Executive Leadership & Team Members';
$adminActivePage = 'members';

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-panel mb-4">
  <div class="panel-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <h3 class="panel-title mb-1">
        <i class="fa-solid fa-users text-primary me-2"></i>Executive Leaders & Key Team
      </h3>
      <p class="text-muted small mb-0">Shown on About Us &gt; Leadership and individual biography pages</p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= url('about-us.php#leadership') ?>" target="_blank" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View on Site
      </a>
      <a href="<?= admin_url('member-edit.php') ?>" class="btn btn-primary">
        <i class="fa-solid fa-user-plus me-1"></i> Add Member
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

  <!-- Summary Stats -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">Total Members</div>
          <div class="fs-4 fw-bold text-dark"><?= $totalCount ?></div>
        </div>
        <div class="text-primary fs-3"><i class="fa-solid fa-user-group"></i></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">Leadership</div>
          <div class="fs-4 fw-bold text-dark"><?= $leadershipCount ?></div>
        </div>
        <div class="text-info fs-3"><i class="fa-solid fa-user-tie"></i></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">Key Team</div>
          <div class="fs-4 fw-bold text-dark"><?= $keyTeamCount ?></div>
        </div>
        <div class="text-warning fs-3"><i class="fa-solid fa-user-gear"></i></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">Active Public</div>
          <div class="fs-4 fw-bold text-success"><?= $activeCount ?></div>
        </div>
        <div class="text-success fs-3"><i class="fa-solid fa-circle-check"></i></div>
      </div>
    </div>
  </div>

  <!-- Helpful Card: Leadership Section & Message from Founder Guide -->
  <div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4" style="border-radius: 12px; background: #F0FDF4; border: 1px solid #BBF7D0;">
    <div class="d-flex align-items-center gap-3">
      <div style="width: 44px; height: 44px; border-radius: 50%; background: #DCFCE7; display: flex; align-items: center; justify-content: center; color: #16A34A; font-size: 1.2rem; flex-shrink: 0;">
        <i class="fa-solid fa-user-tie"></i>
      </div>
      <div>
        <div class="fw-bold text-dark" style="font-size: 0.95rem;">Quản lý Thông điệp Người Sáng Lập & Tiêu đề Leadership</div>
        <div class="small text-muted">
          Để chỉnh sửa <strong>Message from Founder</strong>, ảnh chân dung Người sáng lập hoặc đoạn giới thiệu chung, bạn có thể chỉnh sửa trực tiếp bằng <strong>Live Edit</strong> trên <a href="<?= url('about-us.php#leadership') ?>" target="_blank" class="fw-semibold text-primary">about-us.php#leadership</a> hoặc mở tab <a href="<?= admin_url('page-sections.php?tab=about_us') ?>" class="fw-semibold text-primary">Page Sections &gt; About Us</a>.
        </div>
      </div>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= admin_url('page-sections.php?tab=about_us') ?>" class="btn btn-sm btn-outline-success">
        <i class="fa-solid fa-pen-to-square me-1"></i> Sửa Khối Leadership trong Page Sections
      </a>
      <a href="<?= url('about-us.php#leadership') ?>" target="_blank" class="btn btn-sm btn-success text-white">
        <i class="fa-solid fa-bolt me-1"></i> Bật Live Edit
      </a>
    </div>
  </div>
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="btn-group btn-group-sm">
      <a href="<?= admin_url('members.php') ?>" class="btn <?= ($sectionFilter === 'all') ? 'btn-primary' : 'btn-outline-secondary' ?>">
        All Members (<?= $totalCount ?>)
      </a>
      <a href="<?= admin_url('members.php?group=leadership') ?>" class="btn <?= ($sectionFilter === 'leadership') ? 'btn-primary' : 'btn-outline-secondary' ?>">
        Leadership (<?= $leadershipCount ?>)
      </a>
      <a href="<?= admin_url('members.php?group=key_team') ?>" class="btn <?= ($sectionFilter === 'key_team') ? 'btn-primary' : 'btn-outline-secondary' ?>">
        Key Team (<?= $keyTeamCount ?>)
      </a>
    </div>

    <div class="small text-muted">
      <i class="fa-solid fa-info-circle text-primary me-1"></i>
      Drag <i class="fa-solid fa-grip-vertical text-muted"></i> to reorder.
    </div>
  </div>

  <!-- Members Table -->
  <div class="table-responsive">
    <table class="admin-table align-middle">
      <thead>
        <tr>
          <th style="width: 40px; text-align: center;"><i class="fa-solid fa-arrows-up-down text-muted"></i></th>
          <th style="width: 70px;">Photo</th>
          <th>Name & Biography URL</th>
          <th>Section</th>
          <th>Position / Title</th>
          <th>Card Role</th>
          <th style="width: 110px; text-align: center;">Visibility</th>
          <th style="text-align: right; width: 140px;">Actions</th>
        </tr>
      </thead>
      <tbody id="members-sortable-list">
        <?php if (empty($members)): ?>
          <tr>
            <td colspan="8" class="text-center py-4 text-muted">No members found in this section.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($members as $m): ?>
            <tr data-id="<?= $m['id'] ?>">
              <!-- Drag Handle -->
              <td style="text-align: center; cursor: grab;" class="drag-handle" title="Drag to reorder">
                <i class="fa-solid fa-grip-vertical text-muted"></i>
              </td>

              <!-- Photo -->
              <td>
                <a href="<?= admin_url('member-edit.php?id=' . $m['id']) ?>" title="Edit member: <?= e($m['name']) ?>">
                  <img src="<?= asset($m['image_url'] ?: 'images/favicon.png') ?>" alt="" 
                       style="width: 48px; height: 48px; object-fit: cover; object-position: top center; border-radius: 50%; border: 2px solid #E2E8F0;"
                       onerror="this.src='<?= asset('assets/images/logo.png') ?>'">
                </a>
              </td>

              <!-- Name & Slug -->
              <td>
                <div class="fw-bold">
                  <a href="<?= admin_url('member-edit.php?id=' . $m['id']) ?>" 
                     class="article-title-link text-decoration-none fw-bold" 
                     title="Click to edit: <?= e($m['name']) ?>">
                    <?= e($m['name']) ?>
                  </a>
                </div>
                <div class="small text-muted">
                  <a href="<?= url('bio.php?id=' . urlencode($m['slug'])) ?>" target="_blank" class="text-decoration-none text-muted">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>bio.php?id=<?= e($m['slug']) ?>
                  </a>
                </div>
              </td>

              <!-- Group / Section -->
              <td>
                <span class="badge <?= Member::groupOf($m) === 'key_team' ? 'badge-info' : 'badge-primary' ?>">
                  <?= e(Member::GROUPS[Member::groupOf($m)]) ?>
                </span>
              </td>

              <!-- Position -->
              <td style="font-size: 0.88rem; color: #334155;">
                <?= e($m['position']) ?>
              </td>

              <!-- Card Role -->
              <td style="font-size: 0.85rem; color: #64748B;">
                <?= e($m['short_position'] ?: '-') ?>
              </td>

              <!-- Active Toggle -->
              <td style="text-align: center;">
                <button type="button" class="btn btn-sm btn-member-toggle <?= !empty($m['is_active']) ? 'btn-success' : 'btn-secondary' ?>"
                        data-id="<?= $m['id'] ?>" title="Toggle Visibility">
                  <i class="fa-solid <?= !empty($m['is_active']) ? 'fa-eye' : 'fa-eye-slash' ?> me-1"></i>
                  <?= !empty($m['is_active']) ? 'Visible' : 'Hidden' ?>
                </button>
              </td>

              <!-- Actions -->
              <td style="text-align: right;">
                <div class="btn-group btn-group-sm">
                  <a href="<?= url('bio.php?id=' . urlencode($m['slug'])) ?>" target="_blank" class="btn btn-outline-secondary" title="View Biography">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                  <a href="<?= admin_url('member-edit.php?id=' . $m['id']) ?>" class="btn btn-outline-secondary" title="Edit Member">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>
                  <a href="<?= admin_url('members.php?action=delete&id=' . $m['id']) ?>" 
                     onclick="return confirm('Are you sure you want to delete member &quot;<?= addslashes($m['name']) ?>&quot;?');" 
                     class="btn btn-outline-danger" title="Delete Member">
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
  const sortableList = document.getElementById('members-sortable-list');
  if (sortableList && typeof Sortable !== 'undefined') {
    new Sortable(sortableList, {
      handle: '.drag-handle',
      animation: 150,
      ghostClass: 'table-active',
      onEnd: function() {
        const rows = sortableList.querySelectorAll('tr[data-id]');
        const order = Array.from(rows).map(row => row.getAttribute('data-id'));
        
        const formData = new FormData();
        formData.append('action', 'reorder_members');
        formData.append('order', JSON.stringify(order));

        fetch('<?= admin_url("ajax/entity-actions.php") ?>', {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            showToast('Member sequence updated successfully!', 'success');
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

  // 2. Member Active Toggle
  document.querySelectorAll('.btn-member-toggle').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const id = this.getAttribute('data-id');

      const formData = new FormData();
      formData.append('action', 'toggle_member_active');
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
          showToast(data.message || 'Visibility updated', 'success');
          if (data.is_active == 1) {
            this.className = 'btn btn-sm btn-member-toggle btn-success';
            this.innerHTML = '<i class="fa-solid fa-eye me-1"></i> Visible';
          } else {
            this.className = 'btn btn-sm btn-member-toggle btn-secondary';
            this.innerHTML = '<i class="fa-solid fa-eye-slash me-1"></i> Hidden';
          }
        } else {
          showToast('Failed to update visibility', 'error');
        }
      })
      .catch(err => {
        this.disabled = false;
        showToast('Network error updating member.', 'error');
      });
    });
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
