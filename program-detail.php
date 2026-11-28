<?php
/**
 * Talent / Interns Program Detail Page (opened from the program cards on sustainable.php)
 * VNCHIP Technology JSC
 *
 * Content comes from models/Program.php: built-in defaults, overridden by what an admin
 * saved through Live Edit on this very page.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Program.php';

$programKey = isset($_GET['program']) ? trim($_GET['program']) : '';
if (!Program::isValidKey($programKey)) {
    header("Location: " . url('sustainable.php#talents-development'));
    exit;
}
$program  = Program::get($programKey);
$otherKey = $programKey === 'talent' ? 'interns' : 'talent';
$otherProgram = Program::get($otherKey);

// Apply link (Admin > Homepage & System Settings, or Live Edit); relative paths resolve against the site root
$applyUrl = Setting::getOr('program_' . $programKey . '_apply_url', 'talk-to-us.php');
$applyExternal = (bool)preg_match('~^https?://~i', $applyUrl);
if (!$applyExternal) $applyUrl = url($applyUrl);

$renderSteps = function (array $steps) {
    foreach ($steps as $step): ?>
        <div class="sus-step-card">
          <div class="sus-step-media">
            <img src="<?= Program::imageSrc($step['img']) ?>" alt="<?= e($step['title']) ?>" loading="lazy">
          </div>
          <div class="sus-step-body">
            <h4 class="sus-step-title"><?= e($step['title']) ?></h4>
            <ul class="sus-step-points">
              <?php foreach ($step['points'] as $point): ?>
                <li class="sus-step-point"><i class="fa-solid fa-check sus-step-point-icon"></i><span><?= e($point) ?></span></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
    <?php endforeach;
};

// Live Edit (admin_bar.php) reads this to know which program the page shows
$liveProgramKey = $programKey;

$pageTitle = $program['title'] . ' | VNCHIP Technology JSC';
$pageDescription = $program['subtitle'];
$activePage = 'sustainable';

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Hero Header Banner -->
<section class="sus-detail-hero">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="sus-detail-breadcrumb">
        <li><a href="<?= url() ?>">Home</a></li>
        <li>/</li>
        <li><a href="<?= url('sustainable.php#talents-development') ?>">Sustainable</a></li>
        <li>/</li>
        <li><?= e($program['title']) ?></li>
      </ol>
    </nav>
    <h1 class="sus-detail-hero-title"><?= e($program['title']) ?></h1>
  </div>
</section>

<!-- 2. Program Detail (White Section) -->
<section class="sus-detail-section">
  <div class="container" id="live-program-detail" data-program="<?= e($programKey) ?>">

    <?php if ($program['badge'] !== ''): ?><span class="sus-detail-badge"><?= e($program['badge']) ?></span><?php endif; ?>
    <p class="sus-detail-subtitle"><?= e($program['subtitle']) ?></p>
    <?php foreach ($program['desc'] as $paragraph): ?>
      <p class="sus-detail-desc"><?= e($paragraph) ?></p>
    <?php endforeach; ?>

    <?php if ($programKey === 'talent'): ?>
      <?php if (!empty($program['modules'])): ?>
      <div class="sus-modules-grid">
        <?php foreach ($program['modules'] as $module): ?>
          <div class="sus-module-card">
            <div class="sus-module-icon-box"><i class="fa-solid <?= e($module['icon']) ?>"></i></div>
            <div class="sus-module-title"><?= e($module['title']) ?></div>
            <?php if ($module['hours'] !== ''): ?><span class="sus-module-hours"><?= e($module['hours']) ?></span><?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    <?php elseif (!empty($program['announcement']['title']) || !empty($program['announcement']['desc'])): ?>
      <div class="sus-announcement-box">
        <?php if ($program['announcement']['title'] !== ''): ?><div class="sus-announcement-title"><?= e($program['announcement']['title']) ?></div><?php endif; ?>
        <?php if ($program['announcement']['desc'] !== ''): ?><p class="sus-announcement-desc"><?= e($program['announcement']['desc']) ?></p><?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($program['steps'])): ?>
    <div class="sus-process-header">
      <h2 class="sus-process-title"><?= e($program['process']) ?> <span>Process</span></h2>
    </div>
    <div class="sus-steps-grid">
      <?php $renderSteps($program['steps']); ?>
    </div>
    <?php endif; ?>

    <div class="sus-apply-row">
      <a href="<?= e($applyUrl) ?>" class="sus-apply-btn"<?= $applyExternal ? ' target="_blank" rel="noopener"' : '' ?>>
        Apply <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>

    <div class="sus-detail-footer">
      <a href="<?= url('sustainable.php#talents-development') ?>">
        <i class="fa-solid fa-arrow-left"></i> Back to Sustainable
      </a>
      <a href="<?= url('program-detail.php?program=' . $otherKey) ?>" class="sus-detail-next">
        <?= e($otherProgram['title']) ?> <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>

  </div>
</section>

<!-- CTA & Footer -->
<?php require_once __DIR__ . '/includes/cta_banner.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
