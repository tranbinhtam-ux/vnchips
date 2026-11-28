<?php
/**
 * VNCHIP Technology JSC - Homepage (Index)
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/News.php';
require_once __DIR__ . '/models/Setting.php';

// Fetch dynamic featured news articles for Media Center section
$featuredNews = News::getFeatured(4);

// Page Metadata
$pageTitle = Setting::get('site_name', 'VNCHIP Technology JSC') . ' | Turnkey IC Design & Semiconductor Innovation';
$pageDescription = 'VNCHIP Technology JSC delivers world-class turnkey IC design services, custom layout, physical design from 65nm to 3nm FinFET nodes, and high-impact talent development in Vietnam.';
$activePage = 'home';

// Dynamic Stats from Admin Settings
$stat1_num = Setting::get('stat_years_number', '6+');
$stat1_lbl = Setting::get('stat_years_label', 'years');
$stat1_desc = Setting::get('stat_years_desc', 'Deep expertise in physical design and implementation, custom circuit layout, and system design - from 65nm to 3nm nodes');

$stat2_num = Setting::get('stat_asic_number', '40+');
$stat2_lbl = Setting::get('stat_asic_label', 'ASIC');
$stat2_desc = Setting::get('stat_asic_desc', 'Successful silicon solutions delivered across AI compute, mobile SOC, and 5G signal processing');

$stat3_num = Setting::get('stat_chips_number', '5M+');
$stat3_lbl = Setting::get('stat_chips_label', 'chips');
$stat3_desc = Setting::get('stat_chips_desc', 'Delivered annually to global partners, testing and post-silicon supports');

// Partner case studies for the PARTNERS slider (Admin > Partners, pinned first)
require_once __DIR__ . '/models/Partner.php';
$homePartners = Partner::getPublished();

$pillBanner = Setting::get('homepage_pill_banner', 'We drive progress through training, research collaboration, and technology transfer, aligning with international standards and evolving industry trends in the global semiconductor landscape.');

// Include Global Header
require_once __DIR__ . '/includes/header.php';
?>

  <!-- 1. HERO SECTION (High-Tech Semiconductor Background) -->
  <section class="hero-section">
    <video class="hero-video" id="heroBgVideo" autoplay loop muted playsinline poster="<?= asset('images/hero_chip_bg.webp') ?>">
      <source src="<?= asset(Setting::getOr('home_hero_video', 'videos/vecteezy_a-computer-chip-with-glowing-blue-lines_76491895.mp4')) ?>" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
    <div class="container hero-content">

      <!-- Key Statistics Bar (Dynamic from Database) -->
      <div class="stats-bar" id="live-stats-bar">
        <div class="stat-item">
          <div class="stat-number"><?= e($stat1_num) ?> <span><?= e($stat1_lbl) ?></span></div>
          <p class="stat-desc"><?= e($stat1_desc) ?></p>
        </div>

        <div class="stat-item">
          <div class="stat-number"><?= e($stat2_num) ?> <span><?= e($stat2_lbl) ?></span></div>
          <p class="stat-desc"><?= e($stat2_desc) ?></p>
        </div>

        <div class="stat-item">
          <div class="stat-number"><?= e($stat3_num) ?> <span><?= e($stat3_lbl) ?></span></div>
          <p class="stat-desc"><?= e($stat3_desc) ?></p>
        </div>
      </div>

    </div>
  </section>

  <!-- 2. BUSINESS SECTION (Primary Dark Background) -->
  <section class="business-section" id="business">
    <div class="business-title-backdrop"></div>
    <div class="container">
      <h2 class="section-title-tech home-section-title"<?= lt_attr('home_business_title', false, 'Tiêu đề khối Business') ?>><?= lt('home_business_title', 'BUSINESS') ?></h2>

      <div class="business-grid">
        <!-- Card 1: Design Solutions -->
        <a href="<?= url('business.php#design-service') ?>" class="business-card">
          <img src="<?= lt_img('home_business_card1_img', 'images/business_design_solutions.webp') ?>" alt="Semiconductor Design Solutions" class="business-card-img"<?= lt_img_attr('home_business_card1_img', 'Ảnh thẻ Design Solutions') ?>>
          <div class="business-card-overlay">
            <h3 class="business-card-title"<?= lt_attr('home_business_card1_title', false, 'Tiêu đề thẻ 1') ?>><?= lt('home_business_card1_title', 'DESIGN SOLUTIONS') ?></h3>
            <p class="business-card-desc"<?= lt_attr('home_business_card1_desc', false, 'Mô tả thẻ 1') ?>><?= lt('home_business_card1_desc', 'Comprehensive turnkey IC design services spanning specification, architectural modeling, RTL, synthesis, and advanced physical implementation.') ?></p>
          </div>
        </a>

        <!-- Card 2: VNCHIP R&D -->
        <a href="<?= url('business.php#rd-collaborations') ?>" class="business-card">
          <img src="<?= lt_img('home_business_card2_img', 'images/business_rd_cube.webp') ?>" alt="VNCHIP R&D Innovation" class="business-card-img"<?= lt_img_attr('home_business_card2_img', 'Ảnh thẻ VNCHIP R&D') ?>>
          <div class="business-card-overlay">
            <h3 class="business-card-title"<?= lt_attr('home_business_card2_title', false, 'Tiêu đề thẻ 2') ?>><?= lt('home_business_card2_title', 'VNCHIP R&D') ?></h3>
            <p class="business-card-desc"<?= lt_attr('home_business_card2_desc', false, 'Mô tả thẻ 2') ?>><?= lt('home_business_card2_desc', 'Pioneering next-generation IP blocks, chiplet disaggregation, interconnect standards, and AI-centric System-on-Chip architectures.') ?></p>
          </div>
        </a>
      </div>

    </div>
  </section>

  <!-- 3. SERVICES SECTION: ASIC SERVICE (Ported & Enhanced from vnchips_old id="home_asic_service") -->
  <section class="services-section design-service-section" id="home_asic_service" data-section="services">
    <div class="container">

      <!-- Curved Blue Highlight Pill Banner (Dynamic) -->
      <div class="pill-banner-container" id="live-pill-banner">
        <div class="pill-banner">
          <p><?= e($pillBanner) ?></p>
        </div>
      </div>

      <!-- DESIGN SERVICE / IP SERVICE accordion - shared with business.php, managed in Admin > Design & IP Services -->
      <?php require __DIR__ . '/includes/design_ip_services.php'; ?>
    </div>
  </section>

  <!-- 4. PARTNERS SECTION -->
  <section class="partners-section" id="partners">
    <div class="container">

      <div class="partners-header">
        <div>
          <h2 class="partners-title home-section-title"<?= lt_attr('home_partners_title', false, 'Tiêu đề khối Partners') ?>><?= lt('home_partners_title', 'PARTNERS') ?></h2>
          <p class="section-subtitle"<?= lt_attr('home_partners_subtitle', false, 'Mô tả khối Partners') ?>><?= lt('home_partners_subtitle', 'Building a robust semiconductor ecosystem with global industry leaders.') ?></p>
        </div>

        <div class="slider-controls">
          <button class="slider-btn partners-prev-btn" aria-label="Previous Slide">
            <i class="fa-solid fa-chevron-left"></i>
          </button>
          <button class="slider-btn partners-next-btn" aria-label="Next Slide">
            <i class="fa-solid fa-chevron-right"></i>
          </button>
        </div>
      </div>

      <!-- Case-study cards come from Admin > Partners (pinned first); the slider shows the first ones -->
      <div class="partners-carousel-track"<?= lt_admin('partners.php', 'Quản lý case study (Partners)') ?>>
        <?php foreach (array_slice($homePartners, 0, (int)Setting::getOr('home_partners_count', 6)) as $hp): ?>
        <a href="<?= url('partner-detail.php?slug=' . urlencode($hp['slug'])) ?>" class="partner-card">
          <div class="partner-card-media">
            <img src="<?= asset($hp['image_url']) ?>" alt="<?= e($hp['title']) ?>" loading="lazy">
          </div>
          <div class="partner-card-content">
            <h3 class="partner-card-title"><?= e($hp['title']) ?></h3>
            <p class="partner-card-desc"><?= e($hp['excerpt']) ?></p>
          </div>
        </a>
        <?php endforeach; ?>
      </div>

    </div>
  </section>

  <!-- 5. SUSTAINABLE & TALENTS SECTION -->
  <section class="sustainable-section" id="sustainable">
    <div class="container">

      <div class="sustainable-layout">
        <!-- Left Content -->
        <div class="sustainable-info">
          <h2 class="sustainable-main-title home-section-title"<?= lt_attr('home_sus_title', false, 'Tiêu đề khối Sustainable') ?>><?= lt('home_sus_title', 'SUSTAINABLE') ?></h2>
          <h3 class="sustainable-subtitle"<?= lt_attr('home_sus_subtitle', false, 'Tiêu đề phụ') ?>><?= lt('home_sus_subtitle', 'TALENTS DEVELOPMENT') ?></h3>
          <div<?= lt_attr('home_sus_desc', true, 'Đoạn giới thiệu (HTML, mỗi đoạn một thẻ p)') ?>>
            <?= lt('home_sus_desc', "<p class=\"sustainable-desc\">At VNCHIP, we believe that talent is the core value of innovation and success in the IC design industry.</p>\n<p class=\"sustainable-desc\">VNCHIP experts' team is committed to pushing the boundaries of technology while staying true to our core values of knowledge-sharing, innovation, and client satisfaction. We are excited to continue growing and making a lasting impact on Vietnam IC Design industry.</p>", true) ?>
          </div>
        </div>

        <!-- Right Program Cards -->
        <div class="sustainable-cards-group">
          <?php require __DIR__ . '/includes/program_cards.php'; ?>
        </div>
      </div>

    </div>
  </section>


  <!-- 6. MEDIA CENTER SECTION (Dynamic from Database) -->
  <section class="media-section" id="media-center">
    <div class="container">

      <div class="partners-header">
        <div>
          <h2 class="section-title-tech home-section-title"<?= lt_attr('home_media_title', false, 'Tiêu đề khối Media Center') ?>><?= lt('home_media_title', 'MEDIA CENTER') ?></h2>
        </div>
        <div class="slider-controls">
          <button class="slider-btn media-prev-btn" aria-label="Previous News">
            <i class="fa-solid fa-chevron-left"></i>
          </button>
          <button class="slider-btn media-next-btn" aria-label="Next News">
            <i class="fa-solid fa-chevron-right"></i>
          </button>
        </div>
      </div>

      <!-- Dynamic Grid of Articles from MySQL Database -->
      <div class="media-grid"<?= lt_admin('articles.php', 'Quản lý bài viết & tin tức') ?>>
        <?php foreach ($featuredNews as $article): ?>
          <a href="<?= url('media-detail.php?slug=' . urlencode($article['slug'])) ?>" class="media-card">
            <div class="media-thumb-wrapper">
              <img src="<?= asset($article['thumbnail']) ?>" alt="<?= e($article['title']) ?>" class="media-thumb">
              <?php if (!empty($article['category_name'])): ?>
                <span class="media-category-badge"><?= e($article['category_name']) ?></span>
              <?php endif; ?>
            </div>
            <h3 class="media-title"><?= e($article['title']) ?></h3>
            <p class="media-desc"><?= e($article['summary']) ?></p>
          </a>
        <?php endforeach; ?>
      </div>

    </div>
  </section>

  <!-- 7. REUSABLE CTA BANNER -->
  <?php require_once __DIR__ . '/includes/cta_banner.php'; ?>

  <!-- 8. GLOBAL FOOTER -->
  <?php require_once __DIR__ . '/includes/footer.php'; ?>
