<?php
/**
 * Admin Categories Management
 * Enhanced with Edit capability, Module Type mapping, and Article Filter links.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../models/News.php';

$message = '';
$error = '';
$db = Database::getInstance()->getConnection();

// Handle Edit Mode Fetch
$editCategory = null;
if (isset($_GET['edit']) && !empty($_GET['edit']) && $db) {
    $editId = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM `categories` WHERE `id` = :id LIMIT 1");
    $stmt->execute([':id' => $editId]);
    $editCategory = $stmt->fetch();
}

// Handle Add or Update Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $db) {
    $name = trim($_POST['name'] ?? '');
    $slug = !empty($_POST['slug']) ? slugify($_POST['slug']) : slugify($name);
    $moduleType = trim($_POST['module_type'] ?? 'news');
    $desc = trim($_POST['description'] ?? '');

    if ($_POST['action'] === 'save_category') {
        $id = (int)($_POST['id'] ?? 0);

        if (empty($name)) {
            $error = 'Category name cannot be empty.';
        } else {
            try {
                if ($id > 0) {
                    // Update
                    $stmt = $db->prepare("UPDATE `categories` SET `name` = ?, `slug` = ?, `module_type` = ?, `description` = ? WHERE `id` = ?");
                    $stmt->execute([$name, $slug, $moduleType, $desc, $id]);
                    $message = 'Category updated successfully!';
                    $editCategory = null;
                } else {
                    // Insert
                    $stmt = $db->prepare("INSERT INTO `categories` (`name`, `slug`, `module_type`, `description`) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $slug, $moduleType, $desc]);
                    $message = 'Category created successfully!';
                }
            } catch (PDOException $e) {
                $error = 'A category with this name or slug already exists.';
            }
        }
    }
}

// Handle Delete Category
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id']) && $db) {
    $id = (int)$_GET['id'];
    
    // Check if category has articles
    $checkStmt = $db->prepare("SELECT COUNT(*) FROM `news_articles` WHERE `category_id` = :id");
    $checkStmt->execute([':id' => $id]);
    $artCount = (int)$checkStmt->fetchColumn();

    if ($artCount > 0) {
        $error = "Cannot delete category: It still contains $artCount articles. Please reassign or delete them first.";
    } else {
        try {
            $stmt = $db->prepare("DELETE FROM `categories` WHERE `id` = :id");
            $stmt->execute([':id' => $id]);
            $message = 'Category deleted successfully.';
        } catch (PDOException $e) {
            $error = 'Error deleting category.';
        }
    }
}

$categories = News::getCategories();

$adminTitle = 'Categories Management';
$adminActivePage = 'categories';

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
  <div>
    <h3 class="fw-bold text-dark mb-1">
      <i class="fa-solid fa-folder-tree text-primary me-2"></i>Categories Management
    </h3>
    <p class="text-muted small mb-0">Organize articles and publications into technical modules and content taxonomies</p>
  </div>
  <a href="<?= admin_url('articles.php') ?>" class="btn btn-outline-secondary">
    <i class="fa-solid fa-newspaper me-1"></i> View All Articles
  </a>
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

<div class="row g-4">
  
  <!-- Left: Add / Edit Form -->
  <div class="col-12 col-lg-4">
    <div class="card border-0 shadow-sm p-4" style="border-radius: 14px;">
      <h5 class="fw-bold text-dark mb-3">
        <i class="fa-solid <?= $editCategory ? 'fa-pen-to-square text-warning' : 'fa-plus-circle text-primary' ?> me-2"></i>
        <?= $editCategory ? 'Edit Category #' . $editCategory['id'] : 'Add New Category' ?>
      </h5>

      <form action="<?= admin_url('categories.php') ?>" method="POST">
        <input type="hidden" name="action" value="save_category">
        <?php if ($editCategory): ?>
          <input type="hidden" name="id" value="<?= $editCategory['id'] ?>">
        <?php endif; ?>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Category Name <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" required 
                 value="<?= e($editCategory['name'] ?? '') ?>" 
                 placeholder="e.g. ASIC Tapeout Milestones">
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">URL Slug (Auto-generated if empty)</label>
          <input type="text" name="slug" class="form-control" 
                 value="<?= e($editCategory['slug'] ?? '') ?>" 
                 placeholder="e.g. asic-tapeout-milestones">
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Module Type</label>
          <select name="module_type" class="form-select">
            <?php 
            $currentModule = $editCategory['module_type'] ?? 'news';
            $moduleOptions = [
                'news' => 'News & Media Releases',
                'rnd' => 'Semiconductor R&D Innovation',
                'product' => 'Products & IP Solutions',
                'career' => 'Talents, Education & Careers',
                'press' => 'Press & Corporate Milestones',
                'general' => 'General Content'
            ];
            foreach ($moduleOptions as $key => $label): ?>
              <option value="<?= $key ?>" <?= ($currentModule === $key) ? 'selected' : '' ?>>
                <?= $label ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-4">
          <label class="form-label small fw-semibold text-muted">Description (Optional)</label>
          <textarea name="description" rows="3" class="form-control" 
                    placeholder="Brief description of articles in this topic..."><?= e($editCategory['description'] ?? '') ?></textarea>
        </div>

        <div class="d-flex gap-2">
          <?php if ($editCategory): ?>
            <a href="<?= admin_url('categories.php') ?>" class="btn btn-outline-secondary flex-grow-1">Cancel</a>
          <?php endif; ?>
          <button type="submit" class="btn btn-primary flex-grow-1">
            <i class="fa-solid fa-check me-1"></i> <?= $editCategory ? 'Update Category' : 'Create Category' ?>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Right: Existing Categories List Table -->
  <div class="col-12 col-lg-8">
    <div class="card border-0 shadow-sm p-4" style="border-radius: 14px;">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-dark mb-0">
          <i class="fa-solid fa-list-check text-primary me-2"></i>Active Categories (<?= count($categories) ?>)
        </h5>
        <span class="small text-muted">Click article count to view filtered articles</span>
      </div>

      <div class="table-responsive">
        <table class="admin-table align-middle">
          <thead>
            <tr>
              <th>Category Name</th>
              <th>Module Type</th>
              <th>Slug</th>
              <th style="text-align: center;">Articles</th>
              <th style="text-align: right; width: 110px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($categories)): ?>
              <tr>
                <td colspan="5" class="text-center py-4 text-muted">No categories created yet.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($categories as $c): ?>
                <tr>
                  <td>
                    <div class="fw-bold">
                      <a href="<?= admin_url('categories.php?action=edit&id=' . $c['id']) ?>" 
                         class="article-title-link text-decoration-none fw-bold"
                         title="Click to edit: <?= e($c['name']) ?>">
                        <?= e($c['name']) ?>
                      </a>
                    </div>
                    <?php if (!empty($c['description'])): ?>
                      <div class="small text-muted"><?= e($c['description']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge bg-secondary-subtle text-secondary text-uppercase" style="font-size: 0.72rem;">
                      <?= e($c['module_type'] ?? 'news') ?>
                    </span>
                  </td>
                  <td>
                    <code><?= e($c['slug']) ?></code>
                  </td>
                  <td style="text-align: center;">
                    <a href="<?= admin_url('articles.php?category=' . urlencode($c['slug'])) ?>" 
                       class="badge badge-info text-decoration-none px-2 py-1" title="View articles in this category">
                      <i class="fa-solid fa-newspaper me-1"></i><?= (int)$c['article_count'] ?> articles
                    </a>
                  </td>
                  <td style="text-align: right;">
                    <div class="btn-group btn-group-sm">
                      <a href="<?= admin_url('categories.php?edit=' . $c['id']) ?>" 
                         class="btn btn-outline-secondary" title="Edit Category">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </a>
                      <a href="<?= admin_url('categories.php?action=delete&id=' . $c['id']) ?>" 
                         onclick="return confirm('Are you sure you want to delete category &quot;<?= addslashes($c['name']) ?>&quot;?');" 
                         class="btn btn-outline-danger" title="Delete Category">
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
  </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
