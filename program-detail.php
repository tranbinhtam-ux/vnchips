<?php
/**
 * Talent / Interns Program Detail Page (opened from the program cards on sustainable.php)
 * VNCHIP Technology JSC
 */

require_once __DIR__ . '/config/config.php';

$talentModules = [
    ['icon' => 'fa-microchip',    'title' => 'Semiconductor IC Design Practical Competencies', 'hours' => '120 hours'],
    ['icon' => 'fa-user-tie',     'title' => 'Professional Working Competencies (Personal Development Skills)', 'hours' => '60 hours'],
    ['icon' => 'fa-people-group', 'title' => 'Management Competencies for Engineers (Management Module)', 'hours' => '30 hours'],
];

$talentSteps = [
    ['img' => 'Application-Submission-1.png', 'title' => '1. Application Submission', 'points' => [
        'Register before the deadline via the QR code',
        'Submit your CV, academic level, university and major',
    ]],
    ['img' => 'Attend-VNCHIP-Orientation-2.png', 'title' => '2. Attend VNCHIP Orientation', 'points' => [
        'Register for the orientation event',
        'Attend the VNCHIP Orientation Day',
    ]],
    ['img' => 'Initial-Screening-3.png', 'title' => '3. Initial Screening', 'points' => [
        'Review of academic performance',
        'Relevant coursework and interest in IC design',
    ]],
    ['img' => 'Assessment-Test-4.png', 'title' => '4. Assessment Test', 'points' => [
        'Foundational IC design knowledge',
        'Problem-solving and critical thinking',
    ]],
    ['img' => 'Interview-Process-5.png', 'title' => '5. Interview', 'points' => [
        'Technical skills and communication',
        'Interpersonal skills and cultural fit',
    ]],
    ['img' => 'VNCHIP-Training-Program-6.png', 'title' => '6. Training Program', 'points' => [
        'Hands-on ASIC design training from Netlist to GDSII',
        'Place & Route, STA, physical & power verification, sign-off',
    ]],
    ['img' => 'Receive-Certificates-7.png', 'title' => '7. Receive Certificates', 'points' => [
        'Successful candidates are awarded VNCHIP certificates',
    ]],
    ['img' => 'Program-Enrollment-Internship-8.png', 'title' => '8. Enrollment & Internship', 'points' => [
        'Top-performing students are offered internships at VNCHIP',
    ]],
];

$internSteps = [
    ['img' => 'Resume-Screening-1.png', 'title' => '1. Resume Screening', 'points' => [
        'Review applications and shortlist candidates',
        'Match candidates to job requirements',
    ]],
    ['img' => 'Initial-Screening-Call-2.png', 'title' => '2. Initial Screening Call', 'points' => [
        'Discuss background and motivation',
        'Clarify role expectations and align on goals',
    ]],
    ['img' => 'Technical-Skills-Assessment-3.png', 'title' => '3. Technical Skills Assessment', 'points' => [
        'Coding or technical test',
        'Evaluate problem-solving ability',
    ]],
    ['img' => 'First-Interview-4.png', 'title' => '4. First Interview (Technical & Role-Specific)', 'points' => [
        'Assess job knowledge and past experience',
        'Technical problem-solving',
    ]],
    ['img' => 'Interview-With-HR-5.png', 'title' => '5. Interview With HR', 'points' => [
        'Cultural fit and values',
        'Adaptability and interpersonal skills',
    ]],
    ['img' => 'Final-Decision-6.png', 'title' => '6. Final Decision & Offer', 'points' => [
        'Gather interviewer feedback',
        'Finalize terms and extend the offer',
    ]],
    ['img' => 'Onboarding-7.png', 'title' => '7. Onboarding', 'points' => [
        'Set up the new intern',
        'Introduce the team, resources and tools',
    ]],
];

$renderSteps = function (array $steps) {
    foreach ($steps as $step): ?>
        <div class="sus-step-card">
          <div class="sus-step-media">
            <img src="<?= asset('images/programs/' . $step['img']) ?>" alt="<?= e($step['title']) ?>" loading="lazy">
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

$programs = [
    'talent' => [
        'title'    => 'VNCHIP Talent Program',
        'badge'    => '35&ndash;50 participants / year &middot; 3 months',
        'subtitle' => 'Developing young engineers in IC design and core semiconductor technologies to international standards.',
        'desc'     => [
            'The first phase of the program focused on technical training in integrated circuit design, together with foundational skills such as communication and teamwork. From 2026, the program expands to include business-technology thinking, management awareness and professional working skills.',
            'Each year, 35&ndash;50 selected participants join a 3-month program, running from the Orientation Day in late April to the certification ceremony in September.',
        ],
        'process'  => 'Selection',
        'steps'    => $talentSteps,
    ],
    'interns' => [
        'title'    => 'VNCHIP Interns Program',
        'badge'    => '10 interns / year &middot; 3 months',
        'subtitle' => 'Practical experience for students, with support for capstone projects and university internship requirements.',
        'desc'     => [
            'VNCHIP selects 10 interns each year for a 3-month program with a flexible schedule. Interns combine online training with hands-on practice in semiconductor research and IC design.',
            'Interns are mentored by VNCHIP experts, work on real projects, and may be considered for the VNCHIP Talent Program.',
        ],
        'process'  => 'Recruitment',
        'steps'    => $internSteps,
    ],
];

$programKey = isset($_GET['program']) ? trim($_GET['program']) : '';
if (!isset($programs[$programKey])) {
    header("Location: " . url('sustainable.php#talents-development'));
    exit;
}
$program = $programs[$programKey];
// Apply link (Admin > Homepage & System Settings); relative paths resolve against the site root
$applyUrl = Setting::getOr('program_' . $programKey . '_apply_url', 'talk-to-us.php');
$applyExternal = (bool)preg_match('~^https?://~i', $applyUrl);
if (!$applyExternal) $applyUrl = url($applyUrl);
$otherKey = $programKey === 'talent' ? 'interns' : 'talent';

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
  <div class="container">

    <span class="sus-detail-badge"><?= $program['badge'] ?></span>
    <p class="sus-detail-subtitle"><?= e($program['subtitle']) ?></p>
    <?php foreach ($program['desc'] as $paragraph): ?>
      <p class="sus-detail-desc"><?= $paragraph ?></p>
    <?php endforeach; ?>

    <?php if ($programKey === 'talent'): ?>
      <div class="sus-modules-grid">
        <?php foreach ($talentModules as $module): ?>
          <div class="sus-module-card">
            <div class="sus-module-icon-box"><i class="fa-solid <?= e($module['icon']) ?>"></i></div>
            <div class="sus-module-title"><?= e($module['title']) ?></div>
            <span class="sus-module-hours"><?= e($module['hours']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="sus-announcement-box">
        <div class="sus-announcement-title">Applications open year-round</div>
        <p class="sus-announcement-desc">Internship applications are reviewed continuously throughout the year.</p>
      </div>
    <?php endif; ?>

    <div class="sus-process-header">
      <h2 class="sus-process-title"><?= e($program['process']) ?> <span>Process</span></h2>
    </div>
    <div class="sus-steps-grid">
      <?php $renderSteps($program['steps']); ?>
    </div>

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
        <?= e($programs[$otherKey]['title']) ?> <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>

  </div>
</section>

<!-- CTA & Footer -->
<?php require_once __DIR__ . '/includes/cta_banner.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
