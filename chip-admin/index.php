<?php
/**
 * Admin Dashboard Overview
 * Modeled after VNCHIP Labs Control Center
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../models/News.php';

$stats = News::getDashboardStats();
$recentArticles = News::getAll(6, 0, null, null, 'all')['items'];

$db = Database::getInstance()->getConnection();
$recentInquiries = [];
$moduleStats = [
    'partners' => 0, 
    'members' => 0, 
    'media' => 0, 
    'custom_pages' => 0,
    'visits_today' => 0, 
    'unique_today' => 0,
    'visits_month' => 0,
    'new_inquiries' => 0
];

if ($db) {
    try {
        $recentInquiries = $db->query("SELECT * FROM `inquiries` ORDER BY `created_at` DESC LIMIT 6")->fetchAll();
    } catch (PDOException $e) {}

    $countQueries = [
        'partners'      => "SELECT COUNT(*) FROM `partners`",
        'members'       => "SELECT COUNT(*) FROM `members`",
        'media'         => "SELECT COUNT(*) FROM `media`",
        'custom_pages'  => "SELECT COUNT(*) FROM `custom_pages`",
        'visits_today'  => "SELECT COUNT(*) FROM `visitor_logs` WHERE `created_at` >= CURDATE()",
        'unique_today'  => "SELECT COUNT(DISTINCT `ip_address`) FROM `visitor_logs` WHERE `created_at` >= CURDATE()",
        'visits_month'  => "SELECT COUNT(*) FROM `visitor_logs` WHERE `created_at` >= CURDATE() - INTERVAL 29 DAY",
        'new_inquiries' => "SELECT COUNT(*) FROM `inquiries` WHERE `status` = 'new'",
    ];
    foreach ($countQueries as $k => $sql) {
        try { 
            $moduleStats[$k] = (int)$db->query($sql)->fetchColumn(); 
        } catch (PDOException $e) {}
    }
}

$adminTitle = 'Control Center Dashboard';
$adminBreadcrumb = 'Overview / <span>Dashboard</span>';
$adminActivePage = 'dashboard';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Welcome & User Guide Callout -->
<div class="card mb-4" style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 14px; padding: 18px 24px; color: #FFFFFF; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
      <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3); color: #38BDF8; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
        <i class="fa-solid fa-book-open-reader"></i>
      </div>
      <div>
        <div style="font-weight: 700; font-size: 0.95rem; color: #FFFFFF;">Cẩm nang Hướng dẫn Quản trị (User Guide) & Bản đồ Đối chiếu</div>
        <div style="font-size: 0.82rem; color: #94A3B8;">Xem chi tiết từng bước, đối chiếu vị trí ngoài Website (Frontend) với các mục cài đặt bên trong (Backend).</div>
      </div>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= admin_url('user-guide.php') ?>" class="btn btn-primary btn-sm px-3 py-2" style="border-radius: 8px; font-weight: 700; font-size: 0.8rem; letter-spacing: 0.3px;">
        <i class="fa-solid fa-compass me-1"></i> MỞ USER GUIDE
      </a>
      <a href="<?= url() ?>" target="_blank" class="btn btn-outline-light btn-sm px-3 py-2" style="border-radius: 8px; font-weight: 700; font-size: 0.8rem;">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> XEM WEBSITE
      </a>
    </div>
  </div>
</div>

<!-- 1. Top KPI Stat Cards -->
<div class="stats-grid">
  <!-- Today's Traffic (Hero Dark Card) -->
  <a href="<?= admin_url('analytics.php') ?>" class="card-stat card-stat-dark">
    <div>
      <div class="stat-label">TODAY'S TRAFFIC</div>
      <div class="stat-value"><?= number_format($moduleStats['visits_today']) ?> <span style="font-size: 1.1rem; font-weight: 600; color: #94A3B8;">Views</span></div>
      <div class="stat-subtext">
        <span style="color: var(--admin-primary); font-weight: 700;"><?= number_format($moduleStats['unique_today']) ?></span> Unique Visitors &middot; Live Data
      </div>
      <div style="margin-top: 10px; font-size: 0.78rem; color: var(--admin-primary); font-weight: 700;">
        View live analytics &rarr;
      </div>
    </div>
    <div class="icon-shape icon-orange">
      <i class="fa-solid fa-chart-line"></i>
    </div>
  </a>

  <!-- Customer Inquiries -->
  <a href="<?= admin_url('inquiries.php') ?>" class="card-stat">
    <div>
      <div class="stat-label">CUSTOMER LEADS</div>
      <div class="stat-value"><?= (int)$stats['inquiries'] ?></div>
      <div class="stat-subtext">
        <?php if ($moduleStats['new_inquiries'] > 0): ?>
          <span class="badge badge-danger"><?= $moduleStats['new_inquiries'] ?> New</span>
        <?php else: ?>
          <span style="color: var(--admin-success); font-weight: 600;"><i class="fa-solid fa-check-circle"></i> All responded</span>
        <?php endif; ?>
      </div>
      <div style="margin-top: 8px; font-size: 0.78rem; color: #0284C7; font-weight: 700;">
        View all leads &rarr;
      </div>
    </div>
    <div class="icon-shape icon-green">
      <i class="fa-solid fa-envelope-open-text"></i>
    </div>
  </a>

  <!-- Total Posts / Articles -->
  <a href="<?= admin_url('articles.php') ?>" class="card-stat">
    <div>
      <div class="stat-label">TOTAL ARTICLES</div>
      <div class="stat-value"><?= (int)$stats['articles'] ?></div>
      <div class="stat-subtext">Across <?= (int)$stats['categories'] ?> active categories</div>
    </div>
    <div class="icon-shape icon-blue">
      <i class="fa-solid fa-newspaper"></i>
    </div>
  </a>

  <!-- Team Members -->
  <a href="<?= admin_url('members.php') ?>" class="card-stat">
    <div>
      <div class="stat-label">TEAM MEMBERS</div>
      <div class="stat-value"><?= (int)$moduleStats['members'] ?></div>
      <div class="stat-subtext">Executive leaders & engineers</div>
    </div>
    <div class="icon-shape icon-purple">
      <i class="fa-solid fa-user-tie"></i>
    </div>
  </a>

  <!-- Partners & Clients -->
  <a href="<?= admin_url('partners.php') ?>" class="card-stat">
    <div>
      <div class="stat-label">PARTNERS</div>
      <div class="stat-value"><?= (int)$moduleStats['partners'] ?></div>
      <div class="stat-subtext">Foundry, EDA & IP Case Studies</div>
    </div>
    <div class="icon-shape icon-orange">
      <i class="fa-solid fa-handshake"></i>
    </div>
  </a>
</div>

<!-- 2. Quick Access Shortcut Pills -->
<div class="quick-access-bar">
  <div class="quick-access-title">
    <i class="fa-solid fa-bolt" style="color: var(--admin-primary);"></i> Quick Access
  </div>
  <div class="quick-access-links">
    <a href="<?= admin_url('user-guide.php') ?>" class="btn btn-sm btn-pill" style="background: linear-gradient(135deg, rgba(2, 132, 199, 0.15) 0%, rgba(37, 99, 235, 0.15) 100%); color: #0284C7; border: 1px solid rgba(2, 132, 199, 0.3); font-weight: 700;">
      <i class="fa-solid fa-book-open-reader"></i> User Guide & Map
    </a>
    <a href="<?= admin_url('article-create.php') ?>" class="btn btn-primary btn-sm btn-pill">
      <i class="fa-solid fa-plus"></i> New Article
    </a>
    <a href="<?= admin_url('banners.php') ?>" class="btn btn-secondary btn-sm btn-pill">
      <i class="fa-solid fa-image"></i> Hero Banners
    </a>
    <a href="<?= admin_url('page-sections.php') ?>" class="btn btn-secondary btn-sm btn-pill">
      <i class="fa-solid fa-cubes"></i> Page Sections
    </a>
    <a href="<?= admin_url('analytics.php') ?>" class="btn btn-secondary btn-sm btn-pill">
      <i class="fa-solid fa-chart-pie"></i> Full Reports
    </a>
    <a href="<?= admin_url('settings.php') ?>" class="btn btn-secondary btn-sm btn-pill">
      <i class="fa-solid fa-sliders"></i> Homepage & Stats
    </a>
    <a href="<?= admin_url('site-settings.php') ?>" class="btn btn-secondary btn-sm btn-pill">
      <i class="fa-solid fa-gear"></i> Global Settings
    </a>
  </div>
</div>

<!-- 3. Two-Column Dashboard Content Grid -->
<div class="row g-4">
  
  <!-- Left Panel: Recent Media Center Articles -->
  <div class="col-12 col-xl-7">
    <div class="admin-panel h-100" style="margin-bottom: 0;">
      <div class="panel-header">
        <div>
          <h3 class="panel-title"><i class="fa-solid fa-newspaper" style="color: var(--admin-primary);"></i> Recent Articles</h3>
          <div class="panel-subtitle">Latest posts published on Media Center & Insights</div>
        </div>
        <a href="<?= admin_url('articles.php') ?>" class="btn btn-secondary btn-sm btn-pill">
          View All <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>

      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Article</th>
              <th>Category</th>
              <th>Status</th>
              <th>Views</th>
              <th style="text-align: right;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recentArticles)): ?>
              <tr><td colspan="5" style="text-align: center; color: #94A3B8; padding: 40px;">No articles found.</td></tr>
            <?php else: ?>
              <?php foreach ($recentArticles as $art): ?>
                <tr>
                  <td>
                    <div style="max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                      <a href="<?= admin_url('article-edit.php?id=' . $art['id']) ?>" 
                         class="article-title-link text-decoration-none fw-bold" 
                         style="font-size: 0.88rem;"
                         title="Click to edit: <?= e($art['title']) ?>">
                        <?= e($art['title']) ?>
                      </a>
                    </div>
                    <div style="font-size: 0.75rem; color: #94A3B8; margin-top: 2px;">
                      <i class="fa-regular fa-clock"></i> <?= formatDate($art['published_at'], 'M d, Y') ?>
                    </div>
                  </td>
                  <td>
                    <span class="badge badge-info"><?= e($art['category_name'] ?? 'General') ?></span>
                  </td>
                  <td>
                    <span class="badge <?= ($art['status'] === 'published') ? 'badge-success' : 'badge-warning' ?>">
                      <i class="fa-solid <?= ($art['status'] === 'published') ? 'fa-circle-check' : 'fa-clock' ?>"></i>
                      <?= e($art['status']) ?>
                    </span>
                  </td>
                  <td style="font-weight: 600; color: #334155;"><?= number_format((int)$art['views']) ?></td>
                  <td style="text-align: right;">
                    <a href="<?= admin_url('article-edit.php?id=' . $art['id']) ?>" class="btn btn-secondary btn-sm btn-icon" title="Edit Article">
                      <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Right Panel: Inbound Customer Leads -->
  <div class="col-12 col-xl-5">
    <div class="admin-panel h-100" style="margin-bottom: 0;">
      <div class="panel-header">
        <div>
          <h3 class="panel-title"><i class="fa-solid fa-envelope" style="color: var(--admin-success);"></i> Recent Inquiries</h3>
          <div class="panel-subtitle">Customer messages submitted via Talk to Us form</div>
        </div>
        <a href="<?= admin_url('inquiries.php') ?>" class="btn btn-secondary btn-sm btn-pill">
          View All <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>

      <?php if (empty($recentInquiries)): ?>
        <div style="text-align: center; padding: 50px 0; color: #94A3B8;">
          <i class="fa-regular fa-envelope-open" style="font-size: 3rem; margin-bottom: 12px; color: #CBD5E1;"></i>
          <p style="margin: 0;">No inquiries submitted yet.</p>
        </div>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 12px;">
          <?php foreach ($recentInquiries as $inq): ?>
            <div style="padding: 16px; background: #F8FAFC; border-radius: 14px; border: 1px solid var(--admin-border); border-left: 4px solid <?= ($inq['status'] === 'new') ? 'var(--admin-primary)' : 'var(--admin-success)' ?>;">
              <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px;">
                <strong style="font-size: 0.92rem; color: #0F172A;"><?= e($inq['fullname']) ?></strong>
                <span style="font-size: 0.75rem; color: #94A3B8;"><?= formatDate($inq['created_at'], 'M d, H:i') ?></span>
              </div>
              <div style="font-size: 0.78rem; color: var(--admin-primary); font-weight: 700; margin-bottom: 6px;">
                <?= e($inq['service_interest'] ?? 'General Inquiry') ?>
              </div>
              <p style="font-size: 0.82rem; color: #64748B; margin-bottom: 8px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                <?= e($inq['message']) ?>
              </p>
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <a href="mailto:<?= e($inq['email']) ?>" style="font-size: 0.78rem; color: #0284C7; text-decoration: none;">
                  <i class="fa-solid fa-envelope"></i> <?= e($inq['email']) ?>
                </a>
                <span class="badge <?= ($inq['status'] === 'new') ? 'badge-danger' : 'badge-success' ?>" style="font-size: 0.68rem;">
                  <?= ($inq['status'] === 'new') ? 'New' : 'Responded' ?>
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
