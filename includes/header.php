<?php
/**
 * Global Header Template - VNCHIP Technology JSC
 * Features high-tech typography (Inter + Montserrat + Orbitron)
 * and rich navigation with Mega Menu & animated mobile drawer ported from vnchips_old.
 */

if (!defined('VNCHIP_APP')) {
    require_once __DIR__ . '/../config/config.php';
}

$pageTitle = $pageTitle ?? SITE_NAME . ' | ' . SITE_SLOGAN;
$pageDescription = $pageDescription ?? Setting::getOr('seo_description', 'VNCHIP Technology JSC is a leading turnkey IC design service provider in Vietnam, offering turnkey ASIC/SoC design, custom layout, and advanced semiconductor talent development.');
$activePage = $activePage ?? 'home';

// Branding & SEO (Admin > Global Settings)
$siteFavicon = Setting::getOr('favicon_url', 'images/favicon.png');
$siteLogo = Setting::getOr('logo_header', 'images/logo_vnchip_labs.png');
$seoKeywords = Setting::get('seo_keywords', '');
$seoShareImage = Setting::get('seo_share_image', '');
$customCss = Setting::get('custom_css', '');

trackVisit();

// Query dynamic menus from DB if available
$all_menus = [];
try {
    $pdo = Database::getInstance()->getConnection();
    if ($pdo) {
        $stmt = $pdo->query("SELECT * FROM menus ORDER BY sort_order ASC");
        $all_menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (\Throwable $e) {
    $all_menus = [];
}

// Fallback menu structure matching vnchips_old with full Mega Menus
if (empty($all_menus)) {
    $parents = [
        [
            'id' => 3,
            'title' => 'VNCHIP JOURNEY',
            'link' => url('about-us.php'),
            'type' => 'mega',
            'tag' => 'COMPANY',
            'intro' => 'Over 6 years of pioneering semiconductor excellence in Vietnam, driving talent development, ethical governance, and world-class IC design standards.',
            'col1_title' => '',
            'col1' => [
                ['title' => 'Leadership', 'link' => url('about-us.php#leadership')],
                ['title' => 'Code of Conduct', 'link' => url('about-us.php#conduct')],
            ],
            'col2_title' => '',
            'col2' => [],
            'action_text' => 'OUR STORY',
            'action_link' => url('about-us.php')
        ],
        [
            'id' => 2,
            'title' => 'Business',
            'link' => url('business.php'),
            'type' => 'mega',
            'tag' => 'EXPERTISE',
            'intro' => 'VNCHIP provides turnkey IC design services from architectural specification, RTL design, and verification to physical implementation, custom layout, and GDSII tapeout.',
            'col1_title' => '',
            'col1' => [
                ['title' => 'Technology', 'link' => url('business.php#technology')],
                ['title' => 'Design Service', 'link' => url('business.php#design-service'), 'children' => [
                    ['title' => 'Specification to GDSII', 'link' => url('business.php#spec-gdsii')],
                    ['title' => 'RTL to GDSII', 'link' => url('business.php#rtl-gdsii')],
                    ['title' => 'Netlist to GDSII', 'link' => url('business.php#netlist-gdsii')],
                ]],
                ['title' => 'IP Service', 'link' => url('business.php#ip-service'), 'children' => [
                    ['title' => 'IP Development', 'link' => url('business.php#ip-development')],
                    ['title' => 'Circuit Design', 'link' => url('business.php#circuit-design')],
                    ['title' => 'Custom Layout', 'link' => url('business.php#custom-layout')],
                ]],
            ],
            'col2_title' => '',
            'col2' => [
                ['title' => 'VNCHIP R&D', 'link' => url('business.php#rd-collaborations')],
                ['title' => 'Success in Action', 'link' => url('business.php#success-in-action')],
            ],
            'action_text' => 'GET IN TOUCH',
            'action_link' => url('talk-to-us.php')
        ],
        [
            'id' => 4,
            'title' => 'Sustainable',
            'link' => url('sustainable.php'),
            'type' => 'mega',
            'tag' => 'COMMITMENT',
            'intro' => 'Empowering Vietnam’s next-generation semiconductor workforce through comprehensive training programs, university ties, and strict clean-room governance.',
            'col1_title' => '',
            'col1' => [
                ['title' => 'Social Responsibilities', 'link' => url('sustainable.php#social-responsibilities')],
                ['title' => 'Clean Room', 'link' => url('sustainable.php#clean-room')],
                ['title' => 'Talents Development', 'link' => url('sustainable.php#talents-development'), 'children' => [
                    ['title' => 'VNCHIP Talent Program', 'link' => url('program-detail.php?program=talent')],
                    ['title' => 'VNCHIP Interns Program', 'link' => url('program-detail.php?program=interns')],
                ]],
            ],
            'col2_title' => '',
            'col2' => [
                ['title' => 'Confidentiality', 'link' => url('sustainable.php#confidentiality')],
                ['title' => 'Partners', 'link' => url('sustainable.php#partner')],
            ],
            'action_text' => 'OUR COMMITMENT',
            'action_link' => url('sustainable.php')
        ],
        [
            'id' => 3,
            'title' => 'Partners',
            'link' => url('partners.php'),
            'type' => 'standard',
            'children' => []
        ],
        [
            'id' => 5,
            'title' => 'Media Center',
            'link' => url('media-center.php'),
            'type' => 'standard',
            'children' => []
        ],
        [
            'id' => 6,
            'title' => 'TALK TO US',
            'link' => url('talk-to-us.php'),
            'type' => 'button',
            'children' => []
        ]
    ];
} else {
    // Transform DB rows into structured menu
    $parents = [];
    $raw_parents = array_filter($all_menus, function($m) { return $m['parent_id'] == 0; });
    foreach ($raw_parents as $rp) {
        $isAbout = stripos($rp['title'], 'About') !== false || stripos($rp['title'], 'Journey') !== false || stripos($rp['link'], 'about-us') !== false;
        $children = array_filter($all_menus, function($m) use ($rp) { return $m['parent_id'] == $rp['id']; });
        // Third level: e.g. Business > Design Service > Specification to GDSII
        foreach ($children as $k => $child) {
            $children[$k]['children'] = array_values(array_filter($all_menus, function($m) use ($child) { return $m['parent_id'] == $child['id']; }));
        }
        $col1 = array_filter($children, function($c) { return ($c['column_group'] ?? 1) == 1; });
        $col2 = array_filter($children, function($c) { return ($c['column_group'] ?? 1) == 2; });
        
        // If col2 is empty but there are 2 or more children, split them nicely across 2 columns
        // (VNCHIP Journey / About stays a single column: Leadership / Code of Conduct)
        if (empty($col2) && count($children) > 1 && !$isAbout) {
            $half = ceil(count($children) / 2);
            $col1 = array_slice($children, 0, $half);
            $col2 = array_slice($children, $half);
        }
        
        $isBtn = (strtoupper($rp['title']) === 'TALK TO US');
        $hasChildren = count($children) > 0;
        $type = $isBtn ? 'button' : ($hasChildren ? 'mega' : 'standard');
        
        $tag = 'EXPERTISE';
        $intro = 'VNCHIP provides turnkey IC design services and advanced silicon engineering solutions.';
        $col1_title = 'SOLUTIONS & SERVICES';
        $col2_title = 'ADDITIONAL CAPABILITIES';
        $action_text = 'GET IN TOUCH';
        $action_link = url('talk-to-us.php');

        if (stripos($rp['title'], 'Technology') !== false) {
            $tag = 'INNOVATION';
            $intro = 'Pioneering advanced silicon architectures, chiplet disaggregation, interconnect protocols, and AI-centric SoC design.';
            $col1_title = 'CORE ARCHITECTURES';
            $col2_title = 'APPLICATIONS & R&D';
            $action_text = 'EXPLORE TECH';
            $action_link = url('business.php#technology');
        } elseif ($isAbout) {
            $tag = 'COMPANY';
            $intro = 'Over 6 years of pioneering semiconductor excellence in Vietnam, driving talent development, ethical governance, and world-class IC design standards.';
            $col1_title = '';
            $col2_title = '';
            $action_text = 'OUR STORY';
            $action_link = url('about-us.php');
        } elseif (stripos($rp['title'], 'Sustainable') !== false) {
            $tag = 'COMMITMENT';
            $intro = 'Empowering Vietnam’s next-generation semiconductor workforce through comprehensive training programs and strict clean-room governance.';
            $col1_title = '';
            $col2_title = '';
            $action_text = 'OUR COMMITMENT';
            $action_link = url('sustainable.php');
        } elseif (stripos($rp['title'], 'Design') !== false || stripos($rp['title'], 'Solution') !== false || stripos($rp['title'], 'Business') !== false) {
            $tag = 'EXPERTISE';
            $intro = 'Turnkey IC design services from architecture specification, RTL design, and verification to custom layout and GDSII tapeout.';
            $col1_title = '';
            $col2_title = '';
            $action_text = 'GET IN TOUCH';
            $action_link = url('talk-to-us.php');
        }
        
        $parents[] = [
            'id' => $rp['id'],
            'title' => $rp['title'],
            'link' => $rp['link'],
            'type' => $type,
            'tag' => $tag,
            'intro' => $intro,
            'action_text' => $action_text,
            'action_link' => $action_link,
            'col1_title' => $col1_title,
            'col1' => $col1,
            'col2_title' => $col2_title,
            'col2' => $col2,
            'children' => $children
        ];
    }
}

/**
 * Render the links of one mega-menu column; items with children get a collapsible sub-list
 */
$renderMegaItems = function (array $items) {
    foreach ($items as $c) {
        if (!empty($c['is_divider'])) {
            echo '<li class="mega-divider"></li>';
            continue;
        }
        if (strtoupper(trim($c['title'])) === 'DIVIDER') {
            continue;
        }
        $sub = $c['children'] ?? [];
        ?>
        <li<?= $sub ? ' class="mega-has-sub"' : '' ?>>
          <div class="mega-sub-row">
            <a class="mega-link dropdown-item" href="<?= e($c['link']) ?>">
              <i class="fas fa-chevron-right d-none d-lg-inline"></i>
              <?= e($c['title']) ?>
            </a>
            <?php if ($sub): ?>
              <button type="button" class="mega-sub-toggle" aria-expanded="false" aria-label="<?= e('Show ' . $c['title'] . ' items') ?>">
                <i class="fa-solid fa-chevron-down"></i>
              </button>
            <?php endif; ?>
          </div>
          <?php if ($sub): ?>
            <ul class="list-unstyled mega-sublist">
              <?php foreach ($sub as $sc): ?>
                <li><a class="mega-link mega-sublink dropdown-item" href="<?= e($sc['link']) ?>"><?= e($sc['title']) ?></a></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </li>
        <?php
    }
};
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($pageDescription) ?>">
  <link rel="canonical" href="<?= url() ?>">
  <?php if ($seoKeywords !== ''): ?>
  <meta name="keywords" content="<?= e($seoKeywords) ?>">
  <?php endif; ?>
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($pageDescription) ?>">
  <?php if ($seoShareImage !== ''): ?>
  <meta property="og:image" content="<?= e(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . asset($seoShareImage)) ?>">
  <?php endif; ?>
  <link rel="icon" href="<?= asset($siteFavicon) ?>">
  
  <!-- Google Fonts: Inter (Headings 800) + Montserrat (Body 400-800) + Orbitron (Tech Accent 600-900) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=Orbitron:wght@600;700;800;900&display=swap" rel="stylesheet">
  
  <!-- Font Awesome 6 Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- Main Stylesheets -->
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
  <link rel="stylesheet" href="<?= asset('css/responsive.css') ?>?v=<?= filemtime(__DIR__ . '/../assets/css/responsive.css') ?>">
<?php if (trim($customCss) !== ''): ?>
  <style id="admin-custom-css"><?= str_ireplace('</style', '', $customCss) ?></style>
  <?php endif; ?>
  <?php 
  $customHead = Setting::get('custom_head_scripts', '');
  if (trim($customHead) !== ''): 
    echo $customHead;
  endif; 
  ?>
</head>
<body>
  <?php
  // The frontend Live Edit bar is itself a permission: an account without it gets
  // the plain public page, exactly as a visitor would see it.
  require_once __DIR__ . '/../models/Admin.php';
  if (Admin::can('live_edit.use')) {
      require_once __DIR__ . '/admin_bar.php';
  }
  ?>

  <!-- Sticky Header Navigation (Ported & Enhanced from vnchips_old) -->
  <header class="navbar site-header" id="mainNav">
    <div class="container header-container">
      <a href="<?= url() ?>" class="navbar-brand site-logo" aria-label="VNCHIP Homepage">
        <img src="<?= asset($siteLogo) ?>" alt="VNCHIP Institute Logo">
      </a>

      <!-- Mobile Toggler Icon morphs into X -->
      <button class="navbar-toggler collapsed" type="button" id="navToggler" aria-label="Toggle Navigation Menu">
        <div class="navbar-toggler-icon">
          <span></span>
        </div>
      </button>

      <!-- Main Navigation Menu -->
      <nav class="navbar-collapse collapse" id="vnchipNav" aria-label="Main Navigation">
        <ul class="navbar-nav ms-auto align-items-center">
          <?php foreach ($parents as $item): ?>
            <?php if ($item['type'] === 'button'): ?>
              <!-- CTA Button Item -->
              <li class="nav-item nav-item-cta">
                <a href="<?= e($item['link']) ?>" class="btn-talk btn-talk-nav">
                  <?= e($item['title']) ?>
                </a>
              </li>
            <?php elseif ($item['type'] === 'mega'): ?>
              <!-- Mega Menu Dropdown -->
              <li class="nav-item dropdown">
                <a href="javascript:void(0)" class="nav-link dropdown-toggle" role="button">
                  <span><?= e($item['title']) ?></span> <i class="fa-solid fa-chevron-down nav-arrow"></i>
                </a>
                <div class="dropdown-menu mega-content shadow-lg">
                  <div class="container px-0 px-lg-3">
                    <div class="row align-items-stretch mega-row">
                      
                      <!-- Left Side Box: Intro & Action Link -->
                      <div class="col-lg-4 mega-side-col d-none d-lg-block">
                        <div class="mega-side-box">
                          <a href="<?= e($item['link']) ?>" class="mega-side-cover" aria-label="<?= e($item['title']) ?>"></a>
                          <div>
                            <h3 class="fw-black text-white mb-2 mega-box-title"><?= e($item['title']) ?></h3>
                            <p class="mega-box-desc"><?= e($item['intro']) ?></p>
                          </div>
                          <a href="<?= e($item['action_link'] ?? url('talk-to-us.php')) ?>" class="mega-side-action">
                            <?= e($item['action_text'] ?? 'GET IN TOUCH') ?> <i class="fas fa-arrow-right ms-1"></i>
                          </a>
                        </div>
                      </div>

                      <!-- Right Content Columns: Balanced 50%-50% or single column -->
                      <div class="col-lg-8 mega-main-col">
                        <div class="mega-inner-cols">
                          <div class="row mega-columns-wrapper <?= empty($item['col2']) ? 'single-column' : '' ?>">
                            
                            <!-- Column 1 -->
                            <div class="col-lg-6 mega-col">
                              <?php if (!empty($item['col1_title'])): ?>
                                <span class="mega-col-header"><?= e($item['col1_title']) ?></span>
                                <div class="mega-divider"></div>
                              <?php endif; ?>
                              <ul class="list-unstyled mb-0 mega-list">
                                <?php $renderMegaItems($item['col1']); ?>
                              </ul>
                            </div>

                            <!-- Column 2 -->
                            <?php if (!empty($item['col2'])): ?>
                            <div class="col-lg-6 mega-col">
                              <?php if (!empty($item['col2_title'])): ?>
                                <span class="mega-col-header"><?= e($item['col2_title']) ?></span>
                                <div class="mega-divider"></div>
                              <?php endif; ?>
                              <ul class="list-unstyled mb-0 mega-list">
                                <?php $renderMegaItems($item['col2']); ?>
                              </ul>
                            </div>
                            <?php endif; ?>

                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              </li>
            <?php elseif ($item['type'] === 'dropdown'): ?>
              <!-- Standard Dropdown -->
              <li class="nav-item dropdown nav-item-simple-dropdown">
                <a href="javascript:void(0)" class="nav-link dropdown-toggle" role="button">
                  <span><?= e($item['title']) ?></span> <i class="fa-solid fa-chevron-down nav-arrow"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark shadow-lg">
                  <?php foreach ($item['children'] as $c): ?>
                    <?php if (!empty($c['is_divider'])): ?>
                      <li><hr class="dropdown-divider"></li>
                    <?php else: ?>
                      <li>
                        <a class="dropdown-item" href="<?= e($c['link']) ?>">
                          <?= e($c['title']) ?>
                        </a>
                      </li>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </ul>
              </li>
            <?php else: ?>
              <!-- Simple Nav Link -->
              <li class="nav-item">
                <a href="<?= e($item['link']) ?>" class="nav-link">
                  <?= e($item['title']) ?>
                </a>
              </li>
            <?php endif; ?>
          <?php endforeach; ?>

        </ul>
      </nav>
    </div>
  </header>
