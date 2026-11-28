<?php
/**
 * About Us Page (Page 2 of Design PDF)
 * VNCHIP Technology JSC
 * 100% Dynamic & Live Editable Across Every Section
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Member.php';
require_once __DIR__ . '/models/Admin.php';

// Helper: safely render multiline card titles (supports both newline and <br>)
$formatMultilineTitle = static function ($text) {
    $normalized = str_ireplace(['<br />', '<br/>', '<br>'], "\n", (string)$text);
    return nl2br(e($normalized));
};

// 1. Hero Banner dynamic settings
$aboutHeroBg    = Setting::getOr('hero_about', 'images/hero_chip_bg.webp');
$aboutHeroTitle = Setting::getOr('about_hero_title', 'ABOUT US');

// 2. Overview / VNCHIP Journey Intro dynamic settings
$aboutOverviewTitle = Setting::getOr('about_overview_title', 'VNCHIP JOURNEY');
$aboutLobbyPhoto    = Setting::getOr('about_lobby_photo', 'images/about_lobby_reception.webp');
$aboutLobbyAlt      = Setting::getOr('about_lobby_alt', 'VNCHIP Headquarters & Lobby');

// 3. VNCHIP Journey Timeline dynamic settings
$aboutJourneySubtitle = Setting::getOr(
    'about_journey_subtitle',
    'FROM ESTABLISHMENT TO THE PRESENT DAY, THROUGH UPS AND DOWNS, VNCHIP HAS ALWAYS BEEN A PIONEER IN THE FIELD OF SEMICONDUCTOR DESIGN.'
);
$defaultTimelineItems = [
    ['year' => '2018',      'desc' => 'Establish a Training Center and offer Free IC Design Training Courses'],
    ['year' => '2020',      'desc' => 'VNCHIP Technology JSC. establishment'],
    ['year' => '2021',      'desc' => 'Taped out ASIC/SOC from 90nm down to 16nm FinFET process'],
    ['year' => '2021',      'desc' => 'Taped Out 7nm FinFET process'],
    ['year' => '2023',      'desc' => 'Starting working'],
    ['year' => '2024',      'desc' => 'Taped out 3nm FinFET process'],
    ['year' => 'Until now', 'desc' => 'Signed 2P NDA with Samsung Foundry for N14, N8, N5, N4 access.'],
];
$timelineItems = $defaultTimelineItems;
$rawTimelineJson = Setting::get('about_timeline_items', '');
if ($rawTimelineJson !== '') {
    $decodedTimeline = json_decode($rawTimelineJson, true);
    if (is_array($decodedTimeline) && count($decodedTimeline) > 0) {
        $timelineItems = $decodedTimeline;
    }
}
$customTimelineHtml = trim((string)Setting::get('section_about_timeline', ''));

// 4. Vision, Mission & Core Values dynamic settings
$aboutVisionTitle    = Setting::getOr('about_vision_main_title', 'VISION');
$aboutVisionSubtitle = Setting::getOr(
    'about_vision_main_subtitle',
    'VNCHIP delivers world-class solutions and shapes a better future through innovation and collaboration.'
);
$visionIconPresets = [
    'eye'    => '<svg class="vision-icon-svg" viewBox="0 0 128 64" fill="none" stroke="currentColor" stroke-width="2.6" aria-hidden="true"><ellipse cx="64" cy="32" rx="60" ry="19"/><circle cx="64" cy="32" r="15"/><circle cx="64" cy="32" r="6" fill="currentColor" stroke="none"/><circle cx="104" cy="16" r="6.5" fill="currentColor" stroke="none"/><circle cx="22" cy="47" r="5.5" fill="currentColor" stroke="none"/></svg>',
    'target' => '<svg class="vision-icon-svg" viewBox="0 0 64 64" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="30" cy="34" r="27" stroke-width="2" stroke-dasharray="2 3.2"/><circle cx="30" cy="34" r="21" stroke-width="2"/><circle cx="30" cy="34" r="15" stroke-width="1.6"/><circle cx="30" cy="34" r="9" stroke-width="1.6"/><circle cx="30" cy="34" r="3.2" fill="currentColor" stroke="none"/><path d="M30 34 L52 9" stroke-width="2.4" stroke-linecap="round"/><circle cx="53" cy="8" r="3.6" fill="currentColor" stroke="none"/></svg>',
    'atom'   => '<svg class="vision-icon-svg" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><ellipse cx="32" cy="32" rx="29" ry="10"/><ellipse cx="32" cy="32" rx="29" ry="10" transform="rotate(60 32 32)"/><ellipse cx="32" cy="32" rx="29" ry="10" transform="rotate(-60 32 32)"/><circle cx="32" cy="32" r="6" fill="currentColor" stroke="none"/><circle cx="61" cy="32" r="3.4" fill="currentColor" stroke="none"/><circle cx="17.5" cy="7" r="3.4" fill="currentColor" stroke="none"/><circle cx="17.5" cy="57" r="3.4" fill="currentColor" stroke="none"/></svg>',
    'chip'   => '<svg class="vision-icon-svg" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><rect x="18" y="18" width="28" height="28" rx="4"/><rect x="25" y="25" width="14" height="14" rx="2"/><path d="M24 18V10M32 18V10M40 18V10M24 46V54M32 46V54M40 46V54M18 24H10M18 32H10M18 40H10M46 24H54M46 32H54M46 40H54" stroke-linecap="round"/></svg>',
    'shield' => '<svg class="vision-icon-svg" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M32 8L52 16V31C52 44 43.5 53 32 57C20.5 53 12 44 12 31V16L32 8Z"/><path d="M23 32L29 38L42 25" stroke-linecap="round" stroke-linejoin="round"/></svg>',
];
$defaultVisionCards = [
    [
        'icon'  => 'eye',
        'title' => 'VISION',
        'text'  => "To become Vietnam’s most trusted Turnkey IC design partner by delivering innovative, end-to-end design solutions that empower our customers' success.",
    ],
    [
        'icon'  => 'target',
        'title' => 'MISSION',
        'text'  => 'To co-create innovative IC design solutions with strategic partners, while empowering Vietnamese engineering talent and contributing to the advancement of education and technology.',
    ],
    [
        'icon'  => 'atom',
        'title' => 'CORE VALUES',
        'text'  => 'Committed to cultivating talented engineers, driving continuous improvement, and advancing a sustainable IC Design industry',
    ],
];
$visionCards = $defaultVisionCards;
$rawVisionCardsJson = Setting::get('about_vision_cards', '');
if ($rawVisionCardsJson !== '') {
    $decodedVisionCards = json_decode($rawVisionCardsJson, true);
    if (is_array($decodedVisionCards) && count($decodedVisionCards) > 0) {
        $visionCards = $decodedVisionCards;
    }
}
$customVisionCardsHtml = trim((string)Setting::get('section_about_vision_cards', ''));

// 5. Executive leaders & Leadership dynamic settings
$executives   = Member::getActive('leadership');
$keyTeam      = Member::getActive('key_team');
$showKeyTeam  = Setting::get('about_show_key_team', '1') === '1';

// A hidden section is still rendered for signed-in admins (greyed out, flagged), otherwise
// switching it off would also remove the Live Edit badge needed to switch it back on.
$keyTeamAdminPreview = Admin::check();
$renderKeyTeam       = $showKeyTeam || $keyTeamAdminPreview;

$leadershipTitle   = Setting::getOr('about_leadership_title', 'LEADERSHIP');
$founderPhoto      = Setting::getOr('about_founder_photo', 'assets/images/founder_tri_standing.webp');
$founderName       = Setting::getOr('about_founder_name', 'Luc Duc Tri - Founder & CEO');
$founderTitle      = Setting::getOr('about_founder_title', "MESSAGE FROM<br>FOUNDER");
$founderQuote      = Setting::getOr('about_founder_quote', "Our mission has always been to empower aspiring engineers and professionals with the skills needed to thrive in the fast-paced world of Integrated Circuit (IC) design. Today, we remain committed to delivering innovative, high-impact design solutions while driving growth and excellence in Vietnam’s IC design industry.");
$leadershipSubtext = Setting::getOr('about_leadership_subtext', 'Our focus is on driving innovation, enhancing performance, and addressing the critical challenges faced by the industry today in Vietnam.');
$keyTeamTitle      = Setting::getOr('about_key_team_title', 'KEY TEAM');
$keyTeamSubtext    = Setting::getOr('about_key_team_subtext', 'Our multidisciplinary team of senior IC design engineers, verification leads, and physical implementation specialists.');

// 6. Code of Conduct dynamic settings
$aboutConductTitle    = Setting::getOr('about_conduct_title', 'CODE OF CONDUCT');
$aboutConductSubtitle = Setting::getOr(
    'about_conduct_subtitle',
    'We uphold integrity, innovation, and excellence, delivering design services with professionalism, respect, and a commitment to trusted partnerships.'
);
$defaultConductCards = [
    [
        'title' => "INTEGRITY AND\nETHICAL STANDARDS",
        'text'  => 'We commit to maintaining the highest ethical standards in all our R&D activities. All members are expected to uphold honesty, transparency, and accountability in their work.',
    ],
    [
        'title' => "INNOVATION AND\nEXCELLENCE",
        'text'  => 'We strive for continuous innovation and excellence in semiconductor technology by encouraging creativity and pushing the boundaries of what’s possible.',
    ],
    [
        'title' => "CONFIDENTIALITY AND\nDATA PROTECTION",
        'text'  => 'We are dedicated to safeguarding proprietary information, sensitive data, and intellectual property. All staff, students, and partners are required to comply with strict confidentiality agreements to protect the interests of VNCHIP Institute and our Strategic Partners.',
    ],
    [
        'title' => "SUSTAINABILITY AND\nRESPONSIBILITY",
        'text'  => 'We are committed to conducting Institute R&D activities in an environmentally sustainable and socially responsible manner, ensuring a positive impact on society.',
    ],
];
$conductCards = $defaultConductCards;
$rawConductJson = Setting::get('about_conduct_cards', '');
if ($rawConductJson !== '') {
    $decodedConduct = json_decode($rawConductJson, true);
    if (is_array($decodedConduct) && count($decodedConduct) > 0) {
        $conductCards = $decodedConduct;
    }
}
$customConductHtml = trim((string)Setting::get('section_about_conduct', ''));

$pageTitle = 'About Us | Journey, Leadership & Code of Conduct | VNCHIP Technology JSC';
$pageDescription = 'Discover VNCHIP Technology JSC: our 6+ year journey from talent initiative to leading turnkey IC design powerhouse, core values, leadership team and ethical code of conduct.';
$activePage = 'about';

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Hero Section (Workspace Backdrop) -->
<section class="about-hero-section" id="live-about-hero" style="background-image: url('<?= asset($aboutHeroBg) ?>'); background-size: cover; background-position: center;"<?= lt_img_attr('hero_about', 'Ảnh banner About Us', 'Khuyến nghị 1920 × 650 px (tối thiểu 1600 × 650), ảnh ngang, WEBP/JPG dưới 500 KB. Ảnh cắt kiểu cover, canh giữa; tiêu đề nằm góc dưới trái.') ?> data-lt-pen="br">
  <div class="about-hero-bg"></div>
  <div class="container about-hero-content">
    <h1 class="about-hero-title" id="live-about-hero-title"><?= e($aboutHeroTitle) ?></h1>
  </div>
</section>

<!-- 2. VNCHIP Journey intro (Dark Navy, same background as the timeline) -->
<section class="about-overview-section" id="overview">
  <div class="container" id="live-about-overview-container">
    <h2 class="overview-title" id="live-about-overview-title"><?= e($aboutOverviewTitle) ?></h2>
    
    <?php if (!empty($customJourney = trim((string)Setting::get('section_about_story')))): ?>
      <div class="overview-lead" id="live-about-story">
        <?= $customJourney ?>
      </div>
    <?php else: ?>
      <div class="overview-lead" id="live-about-story">
        <p>VNCHIP first started as a training initiative aimed at bridging the skills gap in IC Design industry. Over the past 6 years, we have evolved into a specialized service provider, offering IC design services, consulting, and tailored technology solutions.</p>
        <p>VNCHIP experts' team is committed to pushing the boundaries of technology while staying true to our core values of knowledge-sharing, innovation, and client satisfaction. We are excited to continue growing and making a lasting impact on Vietnam IC Design industry.</p>
      </div>
    <?php endif; ?>

    <div class="overview-divider"></div>

    <?php if (!empty($customVision = trim((string)Setting::get('section_about_vision')))): ?>
      <div class="reveal active" id="live-about-vision-block">
        <?= $customVision ?>
      </div>
    <?php else: ?>
    <div class="overview-grid" id="live-about-vision-block">
      <div class="overview-image-wrapper">
        <img src="<?= asset($aboutLobbyPhoto) ?>" alt="<?= e($aboutLobbyAlt) ?>" class="overview-img" id="live-about-lobby-img" />
      </div>
      <div class="overview-text-wrapper">
        <p class="overview-text-p">
          VNCHIP is a leading provider of turnkey IC design services in Vietnam, offering comprehensive solutions across the semiconductor design spectrum, including Front-End Design, Design Verification, Physical Design, STA, DFT, and Custom Layout. With a mission to empower Vietnam’s engineering talent and accelerate the nation's semiconductor capabilities, VNCHIP combines deep technical expertise with a strong commitment to innovation, quality, and industry collaboration.
        </p>
        <p class="overview-text-p">
          We work closely with strategic partners and clients around the globe, delivering tailored solutions that drive performance, reduce time-to-market, and meet the highest international standards. Through the VNCHIP Institute, we also nurture the next generation of IC design engineers by offering hands-on training programs, mentorship, and career development opportunities, aligned with real industry demands. VNCHIP stands at the intersection of talent development and cutting-edge semiconductor design, driving progress for both individuals and the broader tech ecosystem in Vietnam.
        </p>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- 3. VNCHIP Journey Timeline (Dark Navy) -->
<section class="about-journey-section" id="journey">
  <div class="container" id="live-about-timeline-container">
    <?php if ($customTimelineHtml !== ''): ?>
      <div id="live-custom-timeline"><?= $customTimelineHtml ?></div>
    <?php else: ?>
      <div class="journey-header">
        <p class="journey-subtitle" id="live-journey-subtitle"><?= e($aboutJourneySubtitle) ?></p>
      </div>

      <div class="timeline-wrapper">
        <ol class="timeline-track" id="live-timeline-track">
          <?php foreach ($timelineItems as $item): ?>
            <li class="timeline-item">
              <div class="timeline-node"></div>
              <div class="timeline-year"><?= e($item['year'] ?? '') ?></div>
              <p class="timeline-desc"><?= e($item['desc'] ?? '') ?></p>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>
    <?php endif; ?>
    
    <div class="journey-divider"></div>
  </div>
</section>

<!-- 4. Vision, Mission, Core Values (Dark Navy) -->
<section class="about-vision-section" id="vision">
  <div class="container" id="live-about-vision-cards-container">
    <?php if ($customVisionCardsHtml !== ''): ?>
      <div id="live-custom-vision-cards"><?= $customVisionCardsHtml ?></div>
    <?php else: ?>
      <h2 class="section-title-tech vision-main-title" id="live-vision-main-title"><?= e($aboutVisionTitle) ?></h2>
      <p class="vision-main-subtitle" id="live-vision-main-subtitle"><?= e($aboutVisionSubtitle) ?></p>

      <div class="vision-cards-grid" id="live-vision-cards-grid">
        <?php foreach ($visionCards as $card):
          $iconKey = $card['icon'] ?? 'eye';
          $svgMarkup = $visionIconPresets[$iconKey] ?? $visionIconPresets['eye'];
        ?>
          <div class="vision-card">
            <div class="vision-icon-box">
              <?= $svgMarkup ?>
            </div>
            <h3 class="vision-card-title"><?= $formatMultilineTitle($card['title'] ?? '') ?></h3>
            <p class="vision-card-text"><?= nl2br(e($card['text'] ?? '')) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    
    <div class="journey-divider"></div>
  </div>
</section>

<!-- 5. Leadership & Message from Founder (Dark Navy) -->
<section class="about-leadership-section" id="leadership">
  <div class="container">
    <div id="live-leadership-founder-wrap">
      <div class="leadership-top-row">
        <h2 class="section-title-tech leadership-title" id="live-leadership-title"><?= e($leadershipTitle) ?></h2>
        <div class="carousel-nav-arrows">
          <button class="nav-arrow-btn" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
          <button class="nav-arrow-btn" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
      </div>

      <!-- Message from Founder & Leadership Intro -->
      <?php if (!empty($customLeadership = trim((string)Setting::get('section_about_leadership')))): ?>
        <div id="live-custom-leadership"><?= $customLeadership ?></div>
      <?php else: ?>
        <!-- Message from Founder Card -->
        <div class="founder-message-box" id="live-founder-message-box">
          <div class="founder-photo-col">
            <img src="<?= asset($founderPhoto) ?>" alt="<?= e($founderName) ?>" class="founder-full-img" id="live-founder-img" />
          </div>
          <div class="founder-quote-col">
            <h3 class="founder-box-title" id="live-founder-title"><?= $founderTitle ?></h3>
            <div class="founder-box-divider"></div>
            <p class="founder-quote-p" id="live-founder-quote">
              <?= nl2br(e($founderQuote)) ?>
            </p>
            <div class="founder-box-divider"></div>
          </div>
        </div>

        <div class="leadership-subtext-row">
          <p class="leadership-subtext" id="live-leadership-subtext"><?= e($leadershipSubtext) ?></p>
          <div class="carousel-nav-arrows">
            <button class="nav-arrow-btn" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="nav-arrow-btn" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- 4 Executive Leaders -->
    <div class="executives-grid" id="live-executives-grid">
      <?php foreach ($executives as $exec): ?>
        <a href="<?= url('bio.php?id=' . urlencode($exec['slug'])) ?>" class="executive-card" title="View <?= e(ucwords(strtolower($exec['name']))) ?> Biography">
          <img src="<?= asset($exec['image_url']) ?>" alt="<?= e(ucwords(strtolower($exec['name']))) ?>" class="exec-photo" />
          <div class="exec-info-overlay">
            <h4 class="exec-name"><?= e($exec['name']) ?></h4>
            <p class="exec-role"><?= e($exec['short_position'] ?: $exec['position']) ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if ($renderKeyTeam): ?>
    <div class="journey-divider" style="margin-top: 60px; margin-bottom: 50px;"></div>

    <!-- Key Team Carousel -->
    <div id="live-key-team-wrap" class="<?= $showKeyTeam ? '' : 'live-section-off' ?>" data-section-hidden="<?= $showKeyTeam ? '0' : '1' ?>">
      <?php if (!$showKeyTeam): ?>
        <div class="live-section-off-flag" id="live-key-team-off-flag">
          <i class="fa-solid fa-eye-slash"></i>
          Khối Key Team đang <strong>TẮT</strong> &mdash; khách truy cập không nhìn thấy. Bạn thấy được vì đang đăng nhập quản trị.
        </div>
      <?php endif; ?>
      <div class="key-team-header-row" id="key-team">
        <div>
          <h2 class="section-title-tech key-team-title" id="live-key-team-title"><?= e($keyTeamTitle) ?></h2>
          <p class="key-team-sub" id="live-key-team-sub"><?= e($keyTeamSubtext) ?></p>
        </div>
        <div class="carousel-nav-arrows">
          <button class="nav-arrow-btn" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
          <button class="nav-arrow-btn" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
      </div>

      <div class="key-team-grid">
        <?php if ($keyTeam): ?>
          <?php foreach ($keyTeam as $member): ?>
            <a href="<?= url('bio.php?id=' . urlencode($member['slug'])) ?>" class="key-team-card" title="View <?= e(ucwords(strtolower($member['name']))) ?> Biography">
              <?php if (!empty($member['image_url'])): ?>
                <img src="<?= asset($member['image_url']) ?>" alt="<?= e(ucwords(strtolower($member['name']))) ?>" class="avatar-placeholder-circle key-team-avatar" loading="lazy">
              <?php else: ?>
                <div class="avatar-placeholder-circle"></div>
              <?php endif; ?>
              <div class="team-meta">
                <h5 class="team-member-name"><?= e($member['name']) ?></h5>
                <p class="team-member-pos"><?= e($member['short_position'] ?: $member['position']) ?></p>
              </div>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <?php // Placeholders until Key Team members are added in Admin > Team Members (Section: Key Team) ?>
          <?php for ($i = 0; $i < 5; $i++): ?>
            <div class="key-team-card">
              <div class="avatar-placeholder-circle"></div>
              <div class="team-meta">
                <h5 class="team-member-name">NAME</h5>
                <p class="team-member-pos">Job Position</p>
              </div>
            </div>
          <?php endfor; ?>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<!-- 6. Code of Conduct (White Section) -->
<section class="about-conduct-section" id="conduct">
  <div class="container" id="live-about-conduct-container">
    <?php if ($customConductHtml !== ''): ?>
      <div id="live-custom-conduct"><?= $customConductHtml ?></div>
    <?php else: ?>
      <div class="conduct-header-row">
        <div>
          <h2 class="conduct-main-title" id="live-conduct-title"><?= e($aboutConductTitle) ?></h2>
          <p class="conduct-main-subtitle" id="live-conduct-subtitle"><?= e($aboutConductSubtitle) ?></p>
        </div>
        <div class="carousel-nav-arrows dark-arrows">
          <button class="nav-arrow-btn dark" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
          <button class="nav-arrow-btn dark" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
      </div>

      <div class="conduct-cards-grid" id="live-conduct-cards-grid">
        <?php foreach ($conductCards as $card): ?>
          <div class="conduct-card">
            <h3 class="conduct-card-title"><?= $formatMultilineTitle($card['title'] ?? '') ?></h3>
            <p class="conduct-card-text"><?= nl2br(e($card['text'] ?? '')) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="conduct-divider"></div>
  </div>
</section>

<!-- CTA & Footer -->
<?php require_once __DIR__ . '/includes/cta_banner.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
