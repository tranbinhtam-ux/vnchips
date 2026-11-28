<?php
/**
 * Business & Design Services Page (Page 3 of Design PDF)
 * VNCHIP Technology JSC
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Partner.php';

// Success in Action carousel (managed in Admin > Partners)
$successProjects = Partner::getPublished();

// Partners Projects cards (models/Project.php: data/projects.php defaults + Live Edit override)
require_once __DIR__ . '/models/Project.php';
$projects = Project::all();

$bizTechTitle = Setting::getOr('business_tech_title', 'TECHNOLOGY');

$pageTitle = 'Business & Solutions | Turnkey IC Design & R&D | VNCHIP Technology JSC';
$pageDescription = 'Comprehensive turnkey semiconductor design solutions: RTL to GDSII, custom IC layout, advanced IP blocks, and strategic research partnerships in AI-Centric SoCs, Chiplets, and advanced FinFET nodes.';
$activePage = 'business';

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Hero Section (PCB Traces Backdrop) -->
<section class="biz-hero-section" style="background-image: url('<?= asset(Setting::getOr('hero_business', 'images/hero_chip_bg.webp')) ?>'); background-size: cover; background-position: center;">
  <div class="biz-hero-bg"></div>
  <div class="container biz-hero-content">
    <h1 class="biz-hero-title"<?= lt_attr('biz_hero_title', false, 'Tiêu đề Hero') ?>><?= lt('biz_hero_title', 'BUSINESS') ?></h1>
  </div>
</section>

<!-- 2. Services Section (White Background: Technology Card + Design & IP Services) -->
<section class="biz-services-section" id="business-solutions">
  <div class="container">

    <!-- Technology Capsule Card (Fully on white background below Hero banner) -->
    <div class="biz-tech-container" id="technology">
      <h2 class="biz-tech-title" id="live-biz-tech-title"><?= e($bizTechTitle) ?></h2>
      <?php if (!empty($customBizTech = Setting::get('section_business_services'))): ?>
        <div id="live-biz-tech-box"><?= $customBizTech ?></div>
      <?php else: ?>
      <div class="biz-tech-box" id="live-biz-tech-box">
        <div class="biz-tech-items">

          <!-- Tech Item 1: Chiplet Disaggregation -->
          <a href="<?= url('technology.php#chiplet-disaggregation') ?>" class="biz-tech-item" title="Chiplet Disaggregation">
            <div class="biz-tech-icon">
              <i class="fas fa-microchip"></i>
            </div>
            <div class="biz-tech-text">
              CHIPLET<br>DISAGGREGATION
            </div>
          </a>

          <!-- Tech Item 2: Interconnect Standards -->
          <a href="<?= url('technology.php#interconnect-standards') ?>" class="biz-tech-item" title="Interconnect Standards">
            <div class="biz-tech-icon">
              <i class="fas fa-network-wired"></i>
            </div>
            <div class="biz-tech-text">
              INTERCONNECT<br>STANDARDS
            </div>
          </a>

          <!-- Tech Item 3: AI-Centric SoC Building -->
          <a href="<?= url('technology.php#ai-centric-soc') ?>" class="biz-tech-item" title="AI-Centric SoC Building">
            <div class="biz-tech-icon">
              <i class="fas fa-brain"></i>
            </div>
            <div class="biz-tech-text">
              AI-CENTRIC SOC<br>BUILDING
            </div>
          </a>

        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Design Services & IP Matrix (Detailed Port from Homepage & Design Specs) -->
    <?php $servicesWithAnchors = true; ?>
    <?php require __DIR__ . '/includes/design_ip_services.php'; ?>
  </div>
</section>

<!-- 4. Partners Projects - card carousel, each card opens project-detail.php (data/projects.php) -->
<section class="biz-rd-section" id="rd-collaborations">
  <div class="container" id="partners-projects">

    <div class="biz-rd-header">
      <h2 class="biz-rd-title"<?= lt_attr('biz_projects_title', false, 'Tiêu đề khối Projects') ?>><?= lt('biz_projects_title', 'PROJECTS') ?></h2>
      <div class="carousel-nav-arrows dark-arrows">
        <button class="nav-arrow-btn dark" aria-label="Previous Project"><i class="fa-solid fa-chevron-left"></i></button>
        <button class="nav-arrow-btn dark" aria-label="Next Project"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
    </div>

    <div class="biz-project-track" id="live-biz-projects">
      <?php foreach ($projects as $project): ?>
        <div class="biz-project-card">
          <div class="biz-project-media">
            <img src="<?= asset($project['image']) ?>" alt="<?= e($project['title']) ?>" loading="lazy">
          </div>
          <div class="biz-project-body">
            <h3 class="biz-project-title"><?= e($project['title']) ?></h3>
            <p class="biz-project-subtitle"><?= e($project['subtitle']) ?></p>
            <p class="biz-project-summary"><?= e($project['summary']) ?></p>
            <a href="<?= url('project-detail.php?slug=' . urlencode($project['slug'])) ?>" class="biz-project-more">Read more <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- 5. Success in Action Section (Dark Navy) -->
<section class="biz-success-section" id="success-in-action">
  <div class="container">

    <div class="biz-success-header">
      <div>
        <h2 class="biz-success-title"<?= lt_attr('biz_success_title', false, 'Tiêu đề Success in Action') ?>><?= lt('biz_success_title', 'SUCCESS IN ACTION') ?></h2>
        <p class="biz-success-subtitle"<?= lt_attr('biz_success_subtitle', false, 'Mô tả Success in Action') ?>><?= lt('biz_success_subtitle', 'Excellence in semiconductor design delivery through proven methodologies and technical innovation.') ?></p>
      </div>
      <div class="carousel-nav-arrows">
        <button class="nav-arrow-btn" aria-label="Previous Slide"><i class="fa-solid fa-chevron-left"></i></button>
        <button class="nav-arrow-btn" aria-label="Next Slide"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
    </div>

    <div class="biz-success-grid"<?= lt_admin('partners.php', 'Quản lý Success in Action (Partners)') ?>>
      <?php foreach ($successProjects as $project): ?>
        <a href="<?= url('partner-detail.php?slug=' . urlencode($project['slug'])) ?>" class="biz-success-card">
          <div class="biz-success-media">
            <img src="<?= asset($project['image_url']) ?>" alt="<?= e($project['title']) ?>" loading="lazy">
          </div>
          <div class="biz-success-body">
            <h3 class="biz-success-card-title"><?= e($project['title']) ?></h3>
            <p class="biz-success-card-desc"><?= e($project['excerpt']) ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- Partner Values / University Partners moved to sustainable.php#partner -->
<script>
  (function () {
    if (location.hash === '#partner-values' || location.hash === '#university-partners') {
      location.replace('<?= url('sustainable.php#partner') ?>');
    }
  })();
</script>

<!-- CTA & Footer -->
<?php require_once __DIR__ . '/includes/cta_banner.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
