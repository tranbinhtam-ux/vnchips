<?php
/**
 * Partners Project Detail Page (cards on business.php#partners-projects)
 * Projects are defined in data/projects.php
 */

require_once __DIR__ . '/config/config.php';

$projects = require __DIR__ . '/data/projects.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$project = null;
foreach ($projects as $item) {
    if ($item['slug'] === $slug) {
        $project = $item;
        break;
    }
}

if (!$project) {
    header('Location: ' . url('business.php#partners-projects'));
    exit;
}

$pageTitle = $project['title'] . ' | Partners Projects | ' . SITE_NAME;
$pageDescription = $project['subtitle'];
$activePage = 'business';

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Hero Section (same backdrop as Business) -->
<section class="biz-hero-section">
  <div class="biz-hero-bg"></div>
  <div class="container biz-hero-content">
    <h1 class="biz-hero-title"><?= e($project['title']) ?></h1>
  </div>
</section>

<!-- 2. Project Content -->
<section class="biz-rd-section">
  <div class="container">

    <div class="biz-rd-header">
      <h2 class="biz-rd-title"><?= e($project['title']) ?></h2>
      <span class="biz-rd-badge">Partners Projects</span>
    </div>

    <div class="biz-rd-top-grid">
      <div class="biz-rd-main-photo">
        <img src="<?= asset($project['image']) ?>" alt="<?= e($project['title']) ?>">
      </div>

      <div class="biz-rd-right-col">
        <h3 class="biz-rd-headline"><?= e($project['subtitle']) ?></h3>
        <p class="biz-rd-sublead"><?= e($project['summary']) ?></p>
      </div>
    </div>

    <?= $project['content'] ?>

    <div class="partner-detail-footer biz-project-detail-footer">
      <a href="<?= url('business.php#partners-projects') ?>" class="biz-project-back">
        <i class="fa-solid fa-arrow-left"></i> Back to Projects
      </a>
      <a href="<?= url('talk-to-us.php') ?>" class="btn-talk">
        Partner with us <i class="fa-solid fa-arrow-right ms-1"></i>
      </a>
    </div>

  </div>
</section>

<!-- CTA & Footer -->
<?php require_once __DIR__ . '/includes/cta_banner.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
