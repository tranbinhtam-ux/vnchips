<?php
/**
 * Navigation & Menu Manager - Admin Panel
 * VNCHIP Technology JSC
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';

$pdo = Database::getInstance()->getConnection();
$message = '';
$error = '';

// Check if menus table exists, create if not
if ($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `menus` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `parent_id` INT DEFAULT 0,
        `title` VARCHAR(255) NOT NULL,
        `link` VARCHAR(255) DEFAULT '#',
        `sort_order` INT DEFAULT 0,
        `target` VARCHAR(20) DEFAULT '_self',
        `is_divider` TINYINT(1) DEFAULT 0,
        `column_group` INT DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_item') {
        $id = (int)($_POST['id'] ?? 0);
        $parentId = (int)($_POST['parent_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $link = trim($_POST['link'] ?? '#');
        $columnGroup = (int)($_POST['column_group'] ?? 1);
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if (empty($title)) {
            $error = 'Menu title cannot be empty.';
        } else {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE `menus` SET `parent_id`=?, `title`=?, `link`=?, `column_group`=?, `sort_order`=? WHERE `id`=?");
                $stmt->execute([$parentId, $title, $link, $columnGroup, $sortOrder, $id]);
                $message = 'Menu item updated successfully!';
            } else {
                $stmt = $pdo->prepare("INSERT INTO `menus` (`parent_id`, `title`, `link`, `column_group`, `sort_order`) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$parentId, $title, $link, $columnGroup, $sortOrder]);
                $message = 'New menu item added successfully!';
            }
        }
    }

    if ($action === 'delete_item') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            // Remove the item, its children and their children (3-level menus)
            $childIds = $pdo->prepare("SELECT `id` FROM `menus` WHERE `parent_id`=?");
            $childIds->execute([$id]);
            $ids = array_merge([$id], array_map('intval', $childIds->fetchAll(PDO::FETCH_COLUMN)));
            $marks = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("DELETE FROM `menus` WHERE `id` IN ($marks) OR `parent_id` IN ($marks)");
            $stmt->execute(array_merge($ids, $ids));
            $message = 'Menu item and its children deleted successfully!';
        }
    }

    if ($action === 'reset_default') {
        $pdo->exec("TRUNCATE TABLE `menus`");
        $defaults = [
            // Parent: About Us
            ['id' => 3, 'parent_id' => 0, 'title' => 'About Us', 'link' => 'about-us.php', 'sort_order' => 1, 'column_group' => 1],
            ['id' => 32, 'parent_id' => 3, 'title' => 'VNCHIP Journey', 'link' => 'about-us.php#journey', 'sort_order' => 1, 'column_group' => 1],
            ['id' => 34, 'parent_id' => 3, 'title' => 'Leadership', 'link' => 'about-us.php#leadership', 'sort_order' => 2, 'column_group' => 1],
            ['id' => 36, 'parent_id' => 3, 'title' => 'Code of Conduct', 'link' => 'about-us.php#conduct', 'sort_order' => 3, 'column_group' => 1],

            // Parent: Business (Mega Menu)
            ['id' => 2, 'parent_id' => 0, 'title' => 'Business', 'link' => 'business.php', 'sort_order' => 2, 'column_group' => 1],
            ['id' => 21, 'parent_id' => 2, 'title' => 'Technology', 'link' => 'business.php#technology', 'sort_order' => 1, 'column_group' => 1],
            ['id' => 22, 'parent_id' => 2, 'title' => 'Design Service', 'link' => 'business.php#design-service', 'sort_order' => 2, 'column_group' => 1],
            ['id' => 23, 'parent_id' => 2, 'title' => 'IP Service', 'link' => 'business.php#ip-service', 'sort_order' => 3, 'column_group' => 1],
            ['id' => 24, 'parent_id' => 2, 'title' => 'VNCHIP R&D', 'link' => 'business.php#rd-collaborations', 'sort_order' => 1, 'column_group' => 2],
            ['id' => 25, 'parent_id' => 2, 'title' => 'Success in Action', 'link' => 'business.php#success-in-action', 'sort_order' => 2, 'column_group' => 2],
            // Business > Design Service / IP Service sub-dropdowns
            ['id' => 221, 'parent_id' => 22, 'title' => 'Specification to GDSII', 'link' => 'business.php#spec-gdsii', 'sort_order' => 1, 'column_group' => 1],
            ['id' => 222, 'parent_id' => 22, 'title' => 'RTL to GDSII', 'link' => 'business.php#rtl-gdsii', 'sort_order' => 2, 'column_group' => 1],
            ['id' => 223, 'parent_id' => 22, 'title' => 'Netlist to GDSII', 'link' => 'business.php#netlist-gdsii', 'sort_order' => 3, 'column_group' => 1],
            ['id' => 231, 'parent_id' => 23, 'title' => 'IP Development', 'link' => 'business.php#ip-development', 'sort_order' => 1, 'column_group' => 1],
            ['id' => 232, 'parent_id' => 23, 'title' => 'Circuit Design', 'link' => 'business.php#circuit-design', 'sort_order' => 2, 'column_group' => 1],
            ['id' => 233, 'parent_id' => 23, 'title' => 'Custom Layout', 'link' => 'business.php#custom-layout', 'sort_order' => 3, 'column_group' => 1],

            // Parent: Sustainable
            ['id' => 4, 'parent_id' => 0, 'title' => 'Sustainable', 'link' => 'sustainable.php', 'sort_order' => 3, 'column_group' => 1],
            ['id' => 41, 'parent_id' => 4, 'title' => 'Talents Development', 'link' => 'sustainable.php#talents-development', 'sort_order' => 1, 'column_group' => 1],
            ['id' => 44, 'parent_id' => 4, 'title' => 'Social Responsibilities', 'link' => 'sustainable.php#social-responsibilities', 'sort_order' => 2, 'column_group' => 1],
            ['id' => 45, 'parent_id' => 4, 'title' => 'Confidentiality', 'link' => 'sustainable.php#confidentiality', 'sort_order' => 3, 'column_group' => 1],
            ['id' => 46, 'parent_id' => 4, 'title' => 'Clean Room', 'link' => 'sustainable.php#clean-room', 'sort_order' => 4, 'column_group' => 1],
            ['id' => 47, 'parent_id' => 4, 'title' => 'Partner', 'link' => 'sustainable.php#partner', 'sort_order' => 5, 'column_group' => 1],
            // Sustainable > Talents Development sub-dropdown
            ['id' => 411, 'parent_id' => 41, 'title' => 'VNCHIP Talent Program', 'link' => 'program-detail.php?program=talent', 'sort_order' => 1, 'column_group' => 1],
            ['id' => 412, 'parent_id' => 41, 'title' => 'VNCHIP Interns Program', 'link' => 'program-detail.php?program=interns', 'sort_order' => 2, 'column_group' => 1],

            // Parent: Media Center
            ['id' => 5, 'parent_id' => 0, 'title' => 'Media Center', 'link' => 'media-center.php', 'sort_order' => 4, 'column_group' => 1],

            // Parent: TALK TO US
            ['id' => 6, 'parent_id' => 0, 'title' => 'TALK TO US', 'link' => 'talk-to-us.php', 'sort_order' => 5, 'column_group' => 1],
        ];

        $ins = $pdo->prepare("INSERT INTO `menus` (`id`, `parent_id`, `title`, `link`, `sort_order`, `column_group`) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($defaults as $d) {
            $ins->execute([$d['id'], $d['parent_id'], $d['title'], $d['link'], $d['sort_order'], $d['column_group']]);
        }
        $message = 'Navigation reset to default VNCHIP structure!';
    }
}

// Fetch all menus
$allMenus = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM `menus` ORDER BY `sort_order` ASC");
        $allMenus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $allMenus = [];
    }
}

$parents = array_filter($allMenus, function($m) { return $m['parent_id'] == 0; });
$childrenOf = function ($parentId) use ($allMenus) {
    return array_filter($allMenus, function($m) use ($parentId) { return $m['parent_id'] == $parentId; });
};

$adminTitle = 'Navigation & Menu Management';
$adminActivePage = 'menus';
require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-panel" style="max-width: 1060px; margin: 0 auto;">
  <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
      <h3 class="panel-title">Website Menu & Navigation Structure</h3>
      <p style="color: #64748B; font-size: 0.88rem; margin: 4px 0 0;">Manage top-level navbar items, dropdown links, and Mega Menu columns (ported from vnchips_old).</p>
    </div>
    <form action="" method="POST" onsubmit="return confirm('Reset all menus to default VNCHIP structure?');">
      <input type="hidden" name="action" value="reset_default">
      <button type="submit" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-rotate-left"></i> Reset to Default Menus
      </button>
    </form>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= e($message) ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= e($error) ?></div>
  <?php endif; ?>

  <!-- Add / Edit Menu Item Modal/Box -->
  <div style="background: #F8FAFC; border-radius: 12px; padding: 22px; margin-bottom: 28px; border: 1px solid #E2E8F0;">
    <h4 style="font-size: 1rem; font-weight: 700; color: #0F172A; margin-bottom: 16px;">
      <i class="fa-solid fa-plus-circle" style="color: #E15225;"></i> Add or Update Menu Item
    </h4>
    <form action="" method="POST" class="form-row" style="align-items: flex-end;">
      <input type="hidden" name="action" value="save_item">
      <input type="hidden" name="id" id="edit_id" value="0">

      <div class="form-group" style="flex: 2;">
        <label class="form-label">Menu Title</label>
        <input type="text" name="title" id="edit_title" class="form-control" placeholder="e.g. Design Solutions" required>
      </div>

      <div class="form-group" style="flex: 2;">
        <label class="form-label">Link (URL / Anchor)</label>
        <input type="text" name="link" id="edit_link" class="form-control" placeholder="e.g. business.php#spec-gdsii" required>
      </div>

      <div class="form-group" style="flex: 2;">
        <label class="form-label">Parent Menu</label>
        <select name="parent_id" id="edit_parent_id" class="form-control">
          <option value="0">None (Top-level Menu)</option>
          <?php foreach ($parents as $p): ?>
            <option value="<?= $p['id'] ?>"><?= e($p['title']) ?></option>
            <?php foreach ($childrenOf($p['id']) as $c): ?>
              <option value="<?= $c['id'] ?>">&nbsp;&nbsp;&nbsp;&#8627; <?= e($p['title'] . ' › ' . $c['title']) ?> (sub-dropdown)</option>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group" style="flex: 1.2;">
        <label class="form-label">Mega Col</label>
        <select name="column_group" id="edit_col_group" class="form-control">
          <option value="1">Col 1 (Design Service)</option>
          <option value="2">Col 2 (IP Service)</option>
        </select>
      </div>

      <div class="form-group" style="flex: 1;">
        <label class="form-label">Order</label>
        <input type="number" name="sort_order" id="edit_sort_order" class="form-control" value="1" min="0">
      </div>

      <div class="form-group" style="flex: 1.2;">
        <button type="submit" class="btn btn-primary" style="width: 100%;">
          <i class="fa-solid fa-save"></i> Save
        </button>
      </div>
    </form>
  </div>

  <!-- Menu Structure List -->
  <div style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; overflow: hidden;">
    <div style="padding: 16px 20px; background: #F1F5F9; border-bottom: 1px solid #E2E8F0; font-weight: 700; font-size: 0.92rem; color: #0F172A;">
      Current Navigation Tree (<?= count($parents) ?> Root Items, <?= count($allMenus) ?> Total Items)
    </div>

    <?php if (empty($parents)): ?>
      <div style="padding: 30px; text-align: center; color: #64748B;">
        No menu items found. Click <strong>Reset to Default Menus</strong> above to initialize with the vnchips_old structure.
      </div>
    <?php else: ?>
      <div style="padding: 16px 20px;">
        <?php foreach ($parents as $p): ?>
          <?php 
            $children = array_filter($allMenus, function($m) use ($p) { return $m['parent_id'] == $p['id']; }); 
            $isMega = (strtoupper($p['title']) === 'DESIGN SOLUTIONS');
          ?>
          <div style="border: 1px solid #E2E8F0; border-radius: 10px; margin-bottom: 12px; padding: 14px 18px; background: #FAFAFA;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-bars" style="color: #94A3B8;"></i>
                <strong style="font-size: 1rem; color: #0F172A;"><?= e($p['title']) ?></strong>
                <span style="font-size: 0.8rem; background: #E2E8F0; color: #475569; padding: 2px 8px; border-radius: 4px;">
                  <?= e($p['link']) ?>
                </span>
                <?php if ($isMega): ?>
                  <span style="font-size: 0.75rem; background: #FEF3C7; color: #B45309; padding: 2px 8px; border-radius: 4px; font-weight: 700;">
                    MEGA MENU (2 Columns)
                  </span>
                <?php elseif (count($children) > 0): ?>
                  <span style="font-size: 0.75rem; background: #E0E7FF; color: #4338CA; padding: 2px 8px; border-radius: 4px;">
                    Dropdown (<?= count($children) ?> items)
                  </span>
                <?php endif; ?>
              </div>

              <div style="display: flex; gap: 8px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="editMenu(<?= htmlspecialchars(json_encode($p)) ?>)">
                  <i class="fa-solid fa-pen-to-square"></i> Edit
                </button>
                <form action="" method="POST" style="display: inline;" onsubmit="return confirm('Delete this menu item and its submenus?');">
                  <input type="hidden" name="action" value="delete_item">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </form>
              </div>
            </div>

            <!-- Submenu Items -->
            <?php if (!empty($children)): ?>
              <div style="margin-top: 12px; padding-left: 24px; border-left: 2px solid #CBD5E1;">
                <?php foreach ($children as $c): ?>
                  <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px dashed #E2E8F0;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                      <i class="fa-solid fa-arrow-turn-down" style="color: #94A3B8; font-size: 0.75rem;"></i>
                      <span style="font-size: 0.9rem; color: #1E293B; font-weight: 600;"><?= e($c['title']) ?></span>
                      <span style="font-size: 0.78rem; color: #64748B;">(<?= e($c['link']) ?>)</span>
                      <?php if ($isMega): ?>
                        <span style="font-size: 0.7rem; background: #EFF6FF; color: #1D4ED8; padding: 1px 6px; border-radius: 3px;">
                          Col <?= $c['column_group'] == 2 ? '2 (IP)' : '1 (Design)' ?>
                        </span>
                      <?php endif; ?>
                    </div>
                    <div style="display: flex; gap: 6px;">
                      <button type="button" class="btn btn-secondary btn-sm" style="padding: 2px 8px; font-size: 0.75rem;" onclick="editMenu(<?= htmlspecialchars(json_encode($c)) ?>)">
                        <i class="fa-solid fa-pen"></i>
                      </button>
                      <form action="" method="POST" style="display: inline;" onsubmit="return confirm('Delete this submenu?');">
                        <input type="hidden" name="action" value="delete_item">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" style="padding: 2px 8px; font-size: 0.75rem;">
                          <i class="fa-solid fa-xmark"></i>
                        </button>
                      </form>
                    </div>
                  </div>
                  <?php foreach ($childrenOf($c['id']) as $g): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 5px 0 5px 28px; border-bottom: 1px dashed #E2E8F0;">
                      <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-arrow-turn-down" style="color: #CBD5E1; font-size: 0.7rem;"></i>
                        <span style="font-size: 0.86rem; color: #334155;"><?= e($g['title']) ?></span>
                        <span style="font-size: 0.76rem; color: #64748B;">(<?= e($g['link']) ?>)</span>
                      </div>
                      <div style="display: flex; gap: 6px;">
                        <button type="button" class="btn btn-secondary btn-sm" style="padding: 2px 8px; font-size: 0.75rem;" onclick="editMenu(<?= htmlspecialchars(json_encode($g)) ?>)">
                          <i class="fa-solid fa-pen"></i>
                        </button>
                        <form action="" method="POST" style="display: inline;" onsubmit="return confirm('Delete this submenu?');">
                          <input type="hidden" name="action" value="delete_item">
                          <input type="hidden" name="id" value="<?= $g['id'] ?>">
                          <button type="submit" class="btn btn-danger btn-sm" style="padding: 2px 8px; font-size: 0.75rem;">
                            <i class="fa-solid fa-xmark"></i>
                          </button>
                        </form>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
function editMenu(item) {
  document.getElementById('edit_id').value = item.id;
  document.getElementById('edit_title').value = item.title;
  document.getElementById('edit_link').value = item.link;
  document.getElementById('edit_parent_id').value = item.parent_id;
  document.getElementById('edit_col_group').value = item.column_group || 1;
  document.getElementById('edit_sort_order').value = item.sort_order || 0;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
