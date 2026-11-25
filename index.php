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
      <h2 class="section-title-tech home-section-title">BUSINESS</h2>
      
      <div class="business-grid">
        <!-- Card 1: Design Solutions -->
        <a href="<?= url('business.php#design-service') ?>" class="business-card">
          <img src="<?= asset('images/business_design_solutions.webp') ?>" alt="Semiconductor Design Solutions" class="business-card-img">
          <div class="business-card-overlay">
            <h3 class="business-card-title">DESIGN SOLUTIONS</h3>
            <p class="business-card-desc">
              Comprehensive turnkey IC design services spanning specification, architectural modeling, RTL, synthesis, and advanced physical implementation.
            </p>
          </div>
        </a>

        <!-- Card 2: VNCHIP R&D -->
        <a href="<?= url('business.php#rd-collaborations') ?>" class="business-card">
          <img src="<?= asset('images/business_rd_cube.webp') ?>" alt="VNCHIP R&D Innovation" class="business-card-img">
          <div class="business-card-overlay">
            <h3 class="business-card-title">VNCHIP R&D</h3>
            <p class="business-card-desc">
              Pioneering next-generation IP blocks, chiplet disaggregation, interconnect standards, and AI-centric System-on-Chip architectures.
            </p>
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
          <h2 class="partners-title home-section-title">PARTNERS</h2>
          <p class="section-subtitle">Building a robust semiconductor ecosystem with global industry leaders.</p>
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

      <div class="partners-carousel-track">
        
        <!-- Partner Card 1 -->
        <a href="<?= url('partner-detail.php?slug=hierarchical-apr-on-tsmc-n3e-axmm-topblock') ?>" class="partner-card">
          <div class="partner-card-media">
            <img src="<?= asset('images/featured_1778551077.webp') ?>" alt="Hierarchical APR on TSMC N3E">
          </div>
          <div class="partner-card-content">
            <h3 class="partner-card-title">Hierarchical APR on TSMC N3E — AXMM top-block</h3>
            <p class="partner-card-desc">
              4-partition hierarchical PnR for edge AI inference processor. IOPin budgeting, ELVT leakage containment, EUV multi-patterning DRC/DFM compliance, and MCMM STA sign-off.
            </p>
          </div>
        </a>

        <!-- Partner Card 2 -->
        <a href="<?= url('partner-detail.php?slug=rtltogdsii-fullchip-150m-instances') ?>" class="partner-card">
          <div class="partner-card-media">
            <img src="<?= asset('images/featured_1778550238.webp') ?>" alt="RTL-to-GDSII full-chip — 150M instances">
          </div>
          <div class="partner-card-content">
            <h3 class="partner-card-title">RTL-to-GDSII full-chip — 150M instances</h3>
            <p class="partner-card-desc">
              Turnkey hierarchical PnR for AI inference SoC across 26 partitions, 8 hardened IPs, 596 clock domains with MCMM/POCV STA sign-off and EM-IR closure.
            </p>
          </div>
        </a>

        <!-- Partner Card 3 -->
        <a href="<?= url('partner-detail.php?slug=largestdie-rtltogdsii-175-x-28-mm') ?>" class="partner-card">
          <div class="partner-card-media">
            <img src="<?= asset('images/featured_1778551109.webp') ?>" alt="Largest-die RTL-to-GDSII — 17.5 x 28 mm">
          </div>
          <div class="partner-card-content">
            <h3 class="partner-card-title">Largest-die RTL-to-GDSII — 17.5 x 28 mm</h3>
            <p class="partner-card-desc">
              Turnkey full-chip PnR for Automotive ADAS vision SoC with 13 partitions, 396 clock domains, AOCV MCMM STA sign-off, EM-IR closure, and complete DRC/LVS.
            </p>
          </div>
        </a>

        <!-- Partner Card 4 -->
        <a href="<?= url('partner-detail.php?slug=highdensity-fullchip-hrp_top-629-hard-macros') ?>" class="partner-card">
          <div class="partner-card-media">
            <img src="<?= asset('images/featured_1778553729.webp') ?>" alt="High-density full-chip — HRP_TOP, 629 hard macros">
          </div>
          <div class="partner-card-content">
            <h3 class="partner-card-title">High-density full-chip — HRP_TOP, 629 hard macros</h3>
            <p class="partner-card-desc">
              Full chiptop for Automotive safety MCU on M1-M9 stack with 629 hard macros, AC timing and interface STA sign-off at 1 GHz under narrow-channel routing.
            </p>
          </div>
        </a>

      </div>

    </div>
  </section>

  <!-- 5. SUSTAINABLE & TALENTS SECTION -->
  <section class="sustainable-section" id="sustainable">
    <div class="container">
      
      <div class="sustainable-layout">
        <!-- Left Content -->
        <div class="sustainable-info">
          <h2 class="sustainable-main-title home-section-title">SUSTAINABLE</h2>
          <h3 class="sustainable-subtitle">TALENTS DEVELOPMENT</h3>
          <p class="sustainable-desc">
            At VNCHIP, we believe that talent is the core value of innovation and success in the IC design industry.
          </p>
          <p class="sustainable-desc">
            VNCHIP experts' team is committed to pushing the boundaries of technology while staying true to our core values of knowledge-sharing, innovation, and client satisfaction. We are excited to continue growing and making a lasting impact on Vietnam IC Design industry.
          </p>
        </div>

        <!-- Right Program Cards -->
        <div class="sustainable-cards-group">
          <a href="<?= url('program-detail.php?program=talent') ?>" class="program-card" title="VNCHIP Talent Program">
            <div class="program-card-inner">
              <div class="program-card-visual">
                <img src="<?= asset('images/interns.svg') ?>" alt="VNCHIP Talent Program Icon" class="program-icon-img">
              </div>
              <div class="program-card-footer">
                <div class="program-card-brand">VNCHIP</div>
                <div class="program-card-title">TALENT PROGRAM</div>
              </div>
            </div>
          </a>

          <a href="<?= url('program-detail.php?program=interns') ?>" class="program-card" title="VNCHIP Interns Program">
            <div class="program-card-inner">
              <div class="program-card-visual">
                <img src="<?= asset('images/talent.svg') ?>" alt="VNCHIP Interns Program Icon" class="program-icon-img">
              </div>
              <div class="program-card-footer">
                <div class="program-card-brand">VNCHIP</div>
                <div class="program-card-title">INTERNS PROGRAM</div>
              </div>
            </div>
          </a>
        </div>
      </div>

    </div>
  </section>


  <!-- 6. MEDIA CENTER SECTION (Dynamic from Database) -->
  <section class="media-section" id="media-center">
    <div class="container">
      
      <div class="partners-header">
        <div>
          <h2 class="section-title-tech home-section-title">MEDIA CENTER</h2>
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
      <div class="media-grid">
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
