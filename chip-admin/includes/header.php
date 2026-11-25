<?php
/**
 * Admin Top Header Template
 * Modern semiconductor high-tech styling inspired by VNCHIP Labs
 */

$adminTitle = $adminTitle ?? 'VNCHIP Control Center';
$currentAdmin = Admin::current();

if (!isset($adminBreadcrumb)) {
    $categoryMap = [
        'dashboard'     => 'Overview',
        'analytics'     => 'Overview',
        'articles'      => 'Content Management',
        'categories'    => 'Content Management',
        'members'       => 'Content Management',
        'partners'      => 'Content Management',
        'media'         => 'Content Management',
        'inquiries'     => 'Marketing & Leads',
        'settings'      => 'Site Builder',
        'banners'       => 'Site Builder',
        'page-sections' => 'Site Builder',
        'menus'         => 'Site Builder',
        'site-settings' => 'System',
        'profile'       => 'System',
        'user-guide'    => 'Documentation',
    ];
    $activeSection = $categoryMap[$adminActivePage ?? 'dashboard'] ?? 'Management';
    $adminBreadcrumb = e($activeSection) . ' / <span>' . e($adminTitle) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($adminTitle) ?> | VNCHIP Management</title>
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Montserrat:wght@400;500;600;700;800&family=Orbitron:wght@600;700;800&display=swap" rel="stylesheet">
  
  <!-- FontAwesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- Bootstrap 5.3.0 Utilities & Grid -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Admin Stylesheet -->
  <link rel="icon" type="image/png" href="<?= asset('images/favicon.png') ?>">
  <link rel="stylesheet" href="<?= admin_url('assets/css/admin.css') ?>?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?>">

  <!-- CDNs for Rich Components: SortableJS, Chart.js, CKEditor 4, Ace Editor -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.2/ace.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.2/theme-tomorrow_night.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.2/theme-chrome.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.2/mode-html.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.2/mode-css.js"></script>
  <script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
  <script>
    if (typeof CKEDITOR !== 'undefined') {
      CKEDITOR.config.versionCheck = false;
      CKEDITOR.config.baseHref = '<?= url() ?>/';
    }
  </script>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="admin-wrapper">
  
  <!-- Sidebar Navigation -->
  <?php require_once __DIR__ . '/sidebar.php'; ?>

  <!-- Main Content Container -->
  <div class="admin-main">
    
    <!-- Topbar -->
    <header class="admin-topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="mobileMenuBtn" aria-label="Toggle Navigation">
          <i class="fa-solid fa-bars"></i>
        </button>
        <div class="topbar-title-wrap">
          <h1><?= e($adminTitle) ?></h1>
          <div class="topbar-breadcrumb"><?= $adminBreadcrumb ?></div>
        </div>
      </div>

      <div class="topbar-right">
        <a href="<?= admin_url('user-guide.php') ?>" class="btn-user-guide <?= ($adminActivePage ?? '') === 'user-guide' ? 'active' : '' ?>" title="Cẩm nang hướng dẫn sử dụng & Bản đồ đối chiếu">
          <i class="fa-solid fa-book-open-reader"></i> User Guide
        </a>

        <a href="<?= url() ?>" target="_blank" class="btn-view-site">
          <i class="fa-solid fa-arrow-up-right-from-square"></i> View Website
        </a>

        <a href="<?= admin_url('profile.php') ?>" class="user-badge" title="Account Profile & Security">
          <div class="user-avatar">
            <i class="fa-solid fa-user-shield"></i>
          </div>
          <div>
            <div style="font-size: 0.82rem; font-weight: 700; color: #0F172A; line-height: 1.2;"><?= e($currentAdmin['fullname'] ?? 'Admin') ?></div>
            <div style="font-size: 0.7rem; color: #94A3B8; font-weight: 600; text-transform: uppercase;"><?= e($currentAdmin['role'] ?? 'Superadmin') ?></div>
          </div>
        </a>

        <a href="<?= admin_url('logout.php') ?>" class="logout-pill" title="Sign Out">
          <i class="fa-solid fa-power-off"></i> SIGN OUT
        </a>
      </div>
    </header>

    <!-- Main Content Body -->
    <main class="admin-content">
