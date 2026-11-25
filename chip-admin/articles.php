<?php
/**
 * Admin Articles & Media Center Management
 * Enhanced with SortableJS reordering, Pin-to-top, 1-click Duplicate, Status toggles, and Multi-filter.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../models/News.php';

$message = '';
$error = '';

// Handle Fallback GET actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = (int)($_GET['id'] ?? 0);

    if ($action === 'toggle_featured' && $id > 0) {
        if (News::toggleFeatured($id)) {
            $message = 'Article featured status updated.';
        }
    } elseif ($action === 'toggle_pinned' && $id > 0) {
        if (News::togglePinned($id)) {
            $message = 'Article pin status updated.';
        }
    } elseif ($action === 'toggle_status' && $id > 0) {
        if (News::toggleStatus($id)) {
            $message = 'Article publish status updated.';
        }
    } elseif ($action === 'duplicate' && $id > 0) {
        $newId = News::duplicate($id);
        if ($newId) {
            $message = 'Article successfully duplicated as draft (ID #' . $newId . ').';
        } else {
            $error = 'Failed to duplicate article.';
        }
    } elseif ($action === 'delete' && $id > 0) {
        if (News::delete($id)) {
            $message = 'Article deleted successfully.';
        } else {
            $error = 'Failed to delete article.';
        }
    }
}

// Filters & Pagination
$categorySlug = isset($_GET['category']) ? trim($_GET['category']) : null;
$moduleType = isset($_GET['module_type']) ? trim($_GET['module_type']) : null;
$status = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$keyword = isset($_GET['q']) ? trim($_GET['q']) : null;
$month = !empty($_GET['month']) ? (int)$_GET['month'] : null;
$year = !empty($_GET['year']) ? (int)$_GET['year'] : null;

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$newsData = News::getAll($limit, $offset, $categorySlug, $keyword, $status, [], $moduleType, $month, $year);
$articles = $newsData['items'];
$total = $newsData['total'];
$totalPages = ceil($total / $limit);

$categories = News::getCategories();

// Quick statistics
$db = Database::getInstance()->getConnection();
$totalPublished = 0;
$totalDraft = 0;
$totalPinned = 0;
$totalFeatured = 0;
if ($db) {
    try {
        $totalPublished = (int)$db->query("SELECT COUNT(*) FROM `news_articles` WHERE `status` = 'published'")->fetchColumn();
        $totalDraft = (int)$db->query("SELECT COUNT(*) FROM `news_articles` WHERE `status` = 'draft'")->fetchColumn();
        $totalPinned = (int)$db->query("SELECT COUNT(*) FROM `news_articles` WHERE `is_pinned` = 1")->fetchColumn();
        $totalFeatured = (int)$db->query("SELECT COUNT(*) FROM `news_articles` WHERE `is_featured` = 1")->fetchColumn();
    } catch (Exception $e) {}
}

$adminTitle = 'Articles & Media Center';
$adminActivePage = 'articles';

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-panel mb-4">
  <div class="panel-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <h3 class="panel-title mb-1">
        <i class="fa-solid fa-newspaper text-primary me-2"></i>Articles & Media Center
      </h3>
      <p class="text-muted small mb-0">Manage news, technical articles, announcements, and research publications</p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= admin_url('categories.php') ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-folder-tree me-1"></i> Categories
      </a>
      <a href="<?= admin_url('article-create.php') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i> Add Article
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

  <!-- Summary Stat Pills -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">Total Articles</div>
          <div class="fs-4 fw-bold text-dark"><?= $totalPublished + $totalDraft ?></div>
        </div>
        <div class="text-primary fs-3"><i class="fa-solid fa-file-lines"></i></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">Published</div>
          <div class="fs-4 fw-bold text-success"><?= $totalPublished ?></div>
        </div>
        <div class="text-success fs-3"><i class="fa-solid fa-circle-check"></i></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">Pinned to Top</div>
          <div class="fs-4 fw-bold text-warning"><?= $totalPinned ?></div>
        </div>
        <div class="text-warning fs-3"><i class="fa-solid fa-thumbtack"></i></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small">Featured Home</div>
          <div class="fs-4 fw-bold text-info"><?= $totalFeatured ?></div>
        </div>
        <div class="text-info fs-3"><i class="fa-solid fa-star"></i></div>
      </div>
    </div>
  </div>

  <!-- Advanced Filter Bar -->
  <div class="card border-0 shadow-sm p-3 mb-4" style="background: #f8fafc; border-radius: 14px;">
    <form action="<?= admin_url('articles.php') ?>" method="GET" class="row g-2 align-items-center">
      <div class="col-12 col-md-3">
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
          <input type="text" name="q" value="<?= e($keyword) ?>" placeholder="Search title or content..." class="form-control border-start-0 ps-0">
        </div>
      </div>

      <div class="col-6 col-md-2">
        <select name="category" class="form-select">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <?php if ($cat['slug'] === 'all-categories' || strtolower($cat['name']) === 'all categories') continue; ?>
            <option value="<?= e($cat['slug']) ?>" <?= ($categorySlug === $cat['slug']) ? 'selected' : '' ?>>
              <?= e($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-6 col-md-2">
        <select name="module_type" class="form-select">
          <option value="">All Types</option>
          <option value="news" <?= ($moduleType === 'news') ? 'selected' : '' ?>>News & Media</option>
          <option value="rnd" <?= ($moduleType === 'rnd') ? 'selected' : '' ?>>Semiconductor R&D</option>
          <option value="product" <?= ($moduleType === 'product') ? 'selected' : '' ?>>Products</option>
          <option value="career" <?= ($moduleType === 'career') ? 'selected' : '' ?>>Talent & Career</option>
          <option value="press" <?= ($moduleType === 'press') ? 'selected' : '' ?>>Press Releases</option>
        </select>
      </div>

      <div class="col-6 col-md-2">
        <select name="status" class="form-select">
          <option value="all" <?= ($status === 'all') ? 'selected' : '' ?>>All Statuses</option>
          <option value="published" <?= ($status === 'published') ? 'selected' : '' ?>>Published</option>
          <option value="draft" <?= ($status === 'draft') ? 'selected' : '' ?>>Draft</option>
        </select>
      </div>

      <div class="col-6 col-md-2">
        <select name="year" class="form-select">
          <option value="">Any Year</option>
          <?php 
          $currentYear = (int)date('Y');
          for ($y = $currentYear + 1; $y >= 2023; $y--): ?>
            <option value="<?= $y ?>" <?= ($year === $y) ? 'selected' : '' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
      </div>

      <div class="col-12 col-md-1 d-flex gap-1">
        <button type="submit" class="btn btn-primary w-100" title="Apply Filter">
          <i class="fa-solid fa-filter"></i>
        </button>
        <?php if ($keyword || $categorySlug || $moduleType || $status !== 'all' || $year): ?>
          <a href="<?= admin_url('articles.php') ?>" class="btn btn-outline-secondary" title="Reset Filters">
            <i class="fa-solid fa-rotate-left"></i>
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Reorder Guidance Note -->
  <div class="d-flex justify-content-between align-items-center mb-2 px-1">
    <div class="small text-muted">
      <i class="fa-solid fa-info-circle text-primary me-1"></i>
      Drag the <i class="fa-solid fa-grip-vertical text-muted"></i> handle to adjust article display sequence. Changes are saved automatically.
    </div>
    <div class="small text-muted">
      Showing <strong><?= count($articles) ?></strong> of <strong><?= (int)$total ?></strong> articles
    </div>
  </div>

  <!-- Articles Table -->
  <div class="table-responsive">
    <table class="admin-table align-middle">
      <thead>
        <tr>
          <th style="width: 40px; text-align: center;"><i class="fa-solid fa-arrows-up-down text-muted"></i></th>
          <th style="width: 70px;">Thumb</th>
          <th>Article Details</th>
          <th>Category</th>
          <th style="width: 90px; text-align: center;">Pin Top</th>
          <th style="width: 110px; text-align: center;">Featured</th>
          <th style="width: 95px; text-align: center;">Status</th>
          <th style="width: 70px; text-align: center;">Views</th>
          <th style="width: 120px;">Date</th>
          <th style="text-align: right; width: 170px;">Actions</th>
        </tr>
      </thead>
      <tbody id="articles-sortable-list">
        <?php if (empty($articles)): ?>
          <tr>
            <td colspan="10" class="text-center py-5 text-muted">
              <i class="fa-regular fa-folder-open fs-2 d-block mb-2"></i>
              No articles found matching your criteria.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($articles as $art): ?>
            <tr data-id="<?= $art['id'] ?>" class="<?= !empty($art['is_pinned']) ? 'table-warning-subtle' : '' ?>" style="<?= !empty($art['is_pinned']) ? 'background: rgba(245, 158, 11, 0.05);' : '' ?>">
              <!-- Drag Handle -->
              <td style="text-align: center; cursor: grab;" class="drag-handle" title="Drag to reorder">
                <i class="fa-solid fa-grip-vertical text-muted"></i>
              </td>

              <!-- Thumbnail -->
              <td>
                <a href="<?= admin_url('article-edit.php?id=' . $art['id']) ?>" title="Edit article: <?= e($art['title']) ?>">
                  <img src="<?= asset($art['thumbnail']) ?>" alt="" 
                       style="width: 56px; height: 42px; object-fit: cover; border-radius: 8px; border: 1px solid #E2E8F0; transition: transform 0.15s;"
                       onerror="this.src='<?= asset('assets/images/logo.png') ?>'">
                </a>
              </td>

              <!-- Title & Slug -->
              <td>
                <div style="max-width: 360px; line-height: 1.35;">
                  <?php if (!empty($art['is_pinned'])): ?>
                    <span class="badge bg-warning text-dark me-1" title="Pinned to top of lists"><i class="fa-solid fa-thumbtack"></i> Pinned</span>
                  <?php endif; ?>
                  <a href="<?= admin_url('article-edit.php?id=' . $art['id']) ?>" 
                     class="fw-bold text-dark text-decoration-none article-title-link" 
                     style="font-size: 0.92rem;" 
                     title="Click to edit: <?= e($art['title']) ?>">
                    <?= e($art['title']) ?>
                  </a>
                </div>
                <div class="d-flex align-items-center gap-2 mt-1 small text-muted">
                  <span><i class="fa-solid fa-link me-1"></i><?= e($art['slug']) ?></span>
                  <?php if (!empty($art['custom_url'])): ?>
                    <span class="badge bg-info-subtle text-info" title="Custom URL: <?= e($art['custom_url']) ?>">
                      <i class="fa-solid fa-arrow-up-right-from-square"></i> Custom URL
                    </span>
                  <?php endif; ?>
                </div>
              </td>

              <!-- Category -->
              <td>
                <span class="badge badge-info" style="font-size: 0.78rem;">
                  <?= e($art['category_name'] ?? 'General') ?>
                </span>
                <?php if (!empty($art['module_type'])): ?>
                  <div class="text-muted" style="font-size: 0.72rem; text-transform: uppercase; margin-top: 2px;">
                    <?= e($art['module_type']) ?>
                  </div>
                <?php endif; ?>
              </td>

              <!-- Pin Toggle -->
              <td style="text-align: center;">
                <button type="button" class="btn btn-sm btn-action-toggle <?= !empty($art['is_pinned']) ? 'btn-warning' : 'btn-outline-secondary' ?>"
                        data-action="toggle_pinned" data-id="<?= $art['id'] ?>"
                        title="Toggle Pin to Top">
                  <i class="fa-solid fa-thumbtack"></i>
                </button>
              </td>

              <!-- Featured Toggle -->
              <td style="text-align: center;">
                <button type="button" class="btn btn-sm btn-action-toggle <?= !empty($art['is_featured']) ? 'btn-primary' : 'btn-outline-secondary' ?>"
                        data-action="toggle_featured" data-id="<?= $art['id'] ?>"
                        title="Toggle Home Feature">
                  <i class="<?= !empty($art['is_featured']) ? 'fa-solid' : 'fa-regular' ?> fa-star"></i>
                  <?= !empty($art['is_featured']) ? 'Yes' : 'No' ?>
                </button>
              </td>

              <!-- Status Toggle -->
              <td style="text-align: center;">
                <button type="button" class="btn btn-sm btn-action-toggle <?= ($art['status'] === 'published') ? 'btn-success' : 'btn-secondary' ?>"
                        data-action="toggle_status" data-id="<?= $art['id'] ?>"
                        title="Toggle Published / Draft">
                  <i class="fa-solid <?= ($art['status'] === 'published') ? 'fa-circle-check' : 'fa-pen-ruler' ?>"></i>
                  <?= ucfirst($art['status']) ?>
                </button>
              </td>

              <!-- Views -->
              <td style="text-align: center; font-size: 0.85rem; font-weight: 600; color: #475569;">
                <i class="fa-regular fa-eye me-1 text-muted"></i><?= (int)$art['views'] ?>
              </td>

              <!-- Published Date -->
              <td style="font-size: 0.82rem; color: #64748B;">
                <?= formatDate($art['published_at']) ?>
              </td>

              <!-- Actions -->
              <td style="text-align: right;">
                <div class="btn-group btn-group-sm">
                  <!-- Live Preview -->
                  <a href="<?= !empty($art['custom_url']) ? e($art['custom_url']) : url('media-detail.php?slug=' . urlencode($art['slug'])) ?>" 
                     target="_blank" class="btn btn-outline-secondary" title="View live article on website">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                  </a>
                  <!-- Duplicate -->
                  <a href="<?= admin_url('articles.php?action=duplicate&id=' . $art['id']) ?>" 
                     class="btn btn-outline-primary" title="Duplicate as draft"
                     onclick="return confirm('Duplicate this article as a draft copy?');">
                    <i class="fa-regular fa-clone"></i>
                  </a>
                  <!-- Edit -->
                  <a href="<?= admin_url('article-edit.php?id=' . $art['id']) ?>" 
                     class="btn btn-outline-secondary" title="Edit article">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>
                  <!-- Delete -->
                  <a href="<?= admin_url('articles.php?action=delete&id=' . $art['id']) ?>" 
                     onclick="return confirm('Are you sure you want to permanently delete article &quot;<?= addslashes($art['title']) ?>&quot;?');" 
                     class="btn btn-outline-danger" title="Delete article">
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

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 pt-3 border-top">
      <div class="text-muted small">
        Page <?= $page ?> of <?= $totalPages ?> (Total <?= $total ?> items)
      </div>
      <nav aria-label="Page navigation">
        <ul class="pagination pagination-sm mb-0">
          <?php if ($page > 1): ?>
            <li class="page-item">
              <a class="page-link" href="<?= admin_url('articles.php?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))) ?>">
                <i class="fa-solid fa-chevron-left"></i>
              </a>
            </li>
          <?php endif; ?>

          <?php 
          $startPage = max(1, $page - 2);
          $endPage = min($totalPages, $page + 2);
          for ($i = $startPage; $i <= $endPage; $i++): 
          ?>
            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
              <a class="page-link" href="<?= admin_url('articles.php?' . http_build_query(array_merge($_GET, ['page' => $i]))) ?>">
                <?= $i ?>
              </a>
            </li>
          <?php endfor; ?>

          <?php if ($page < $totalPages): ?>
            <li class="page-item">
              <a class="page-link" href="<?= admin_url('articles.php?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))) ?>">
                <i class="fa-solid fa-chevron-right"></i>
              </a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // 1. SortableJS Drag-and-drop Reordering
  const sortableList = document.getElementById('articles-sortable-list');
  if (sortableList && typeof Sortable !== 'undefined') {
    new Sortable(sortableList, {
      handle: '.drag-handle',
      animation: 150,
      ghostClass: 'table-active',
      onEnd: function() {
        const rows = sortableList.querySelectorAll('tr[data-id]');
        const order = Array.from(rows).map(row => row.getAttribute('data-id'));
        
        const formData = new FormData();
        formData.append('action', 'reorder');
        formData.append('order', JSON.stringify(order));

        fetch('<?= admin_url("ajax/article-actions.php") ?>', {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            showToast('Articles reordered successfully!', 'success');
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

  // 2. Interactive AJAX Toggles
  document.querySelectorAll('.btn-action-toggle').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const action = this.getAttribute('data-action');
      const id = this.getAttribute('data-id');

      const formData = new FormData();
      formData.append('action', action);
      formData.append('id', id);

      this.disabled = true;

      fetch('<?= admin_url("ajax/article-actions.php") ?>', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        this.disabled = false;
        if (data.success) {
          showToast(data.message || 'Updated successfully', 'success');
          // Update button appearance
          if (action === 'toggle_pinned') {
            if (data.is_pinned == 1) {
              this.className = 'btn btn-sm btn-action-toggle btn-warning';
              this.closest('tr').style.background = 'rgba(245, 158, 11, 0.05)';
            } else {
              this.className = 'btn btn-sm btn-action-toggle btn-outline-secondary';
              this.closest('tr').style.background = '';
            }
          } else if (action === 'toggle_featured') {
            if (data.is_featured == 1) {
              this.className = 'btn btn-sm btn-action-toggle btn-primary';
              this.innerHTML = '<i class="fa-solid fa-star"></i> Yes';
            } else {
              this.className = 'btn btn-sm btn-action-toggle btn-outline-secondary';
              this.innerHTML = '<i class="fa-regular fa-star"></i> No';
            }
          } else if (action === 'toggle_status') {
            if (data.status === 'published') {
              this.className = 'btn btn-sm btn-action-toggle btn-success';
              this.innerHTML = '<i class="fa-solid fa-circle-check"></i> Published';
            } else {
              this.className = 'btn btn-sm btn-action-toggle btn-secondary';
              this.innerHTML = '<i class="fa-solid fa-pen-ruler"></i> Draft';
            }
          }
        } else {
          showToast(data.error || 'Failed to update', 'error');
        }
      })
      .catch(err => {
        this.disabled = false;
        showToast('Network error performing action.', 'error');
      });
    });
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
