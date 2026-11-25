<?php
/**
 * Admin Sidebar Navigation
 * Semiconductor High-Tech styling modeled after VNCHIP Labs Control Center
 */

$currentPage = $adminActivePage ?? 'dashboard';

// Fetch unread inquiries count for badge
$newInqCount = 0;
$db = Database::getInstance()->getConnection();
if ($db) {
    try {
        $newInqCount = (int)$db->query("SELECT COUNT(*) FROM `inquiries` WHERE `status` = 'new'")->fetchColumn();
    } catch (PDOException $e) {}
}

$sidebarGroups = [
    'Overview' => [
        'dashboard'   => ['index.php', 'fa-gauge-high', 'Dashboard', 0],
        'analytics'   => ['analytics.php', 'fa-chart-line', 'Traffic Analytics', 0],
        'user-guide'  => ['user-guide.php', 'fa-book-open-reader', 'User Guide', 0],
    ],
    'Content Management' => [
        'articles'   => ['articles.php', 'fa-newspaper', 'Articles & News', 0],
        'categories' => ['categories.php', 'fa-tags', 'Categories', 0],
        'members'    => ['members.php', 'fa-user-tie', 'Team Members', 0],
        'partners'   => ['partners.php', 'fa-handshake', 'Partners', 0],
        'media'      => ['media.php', 'fa-photo-film', 'Media Gallery', 0],
    ],
    'Marketing & Leads' => [
        'inquiries'  => ['inquiries.php', 'fa-envelope-open-text', 'Inquiries & Leads', $newInqCount],
    ],
    'Site Builder' => [
        'settings'      => ['settings.php', 'fa-sliders', 'Homepage & Stats', 0],
        'banners'       => ['banners.php', 'fa-image', 'Hero Banners', 0],
        'page-sections' => ['page-sections.php', 'fa-cubes', 'Page Sections', 0],
        'menus'         => ['menus.php', 'fa-compass', 'Navigation & Menus', 0],
    ],
    'System' => [
        'site-settings' => ['site-settings.php', 'fa-gear', 'Global Settings', 0],
        'profile'       => ['profile.php', 'fa-user-shield', 'Admin Profile', 0],
    ],
];
?>
<!-- Sidebar -->
<aside class="admin-sidebar">
  <a href="<?= admin_url('index.php') ?>" class="sidebar-brand">
    <div class="sidebar-brand-icon">
      <img src="<?= asset(Setting::getOr('logo_header', 'images/logo_vnchip_labs.png')) ?>" alt="VNCHIP">
    </div>
    <div class="sidebar-brand-text">
      VNCHIP <span>LABS</span>
    </div>
  </a>

  <ul class="sidebar-menu">
    <?php foreach ($sidebarGroups as $groupLabel => $links): ?>
      <li class="sidebar-section"><?= e($groupLabel) ?></li>
      <?php foreach ($links as $key => [$file, $icon, $label, $badge]): ?>
        <li>
          <a href="<?= admin_url($file) ?>" class="sidebar-link <?= ($currentPage === $key) ? 'active' : '' ?>">
            <i class="fa-solid <?= $icon ?>"></i>
            <span><?= e($label) ?></span>
            <?php if ($badge > 0): ?>
              <span class="sidebar-link-badge"><?= (int)$badge ?></span>
            <?php endif; ?>
          </a>
        </li>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </ul>

  <div class="sidebar-footer">
    <div>VNCHIP Control Center &copy; 2026</div>
    <a href="<?= admin_url('logout.php') ?>" style="color: #EF4444; font-size: 0.95rem;" title="Sign Out">
      <i class="fa-solid fa-arrow-right-from-bracket"></i>
    </a>
  </div>
</aside>
