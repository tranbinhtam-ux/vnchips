<?php
/**
 * Partners Listing Page with Sidebar (Ported & Enhanced from vnchips_old partners.php)
 * VNCHIP Technology JSC
 */

require_once __DIR__ . '/config/config.php';

$pageTitle = 'Our Global Partners & Silicon Solutions | ' . SITE_NAME;
$pageDescription = 'VNCHIP Technology collaborates with world-class foundries and EDA vendors to deliver high-performance semiconductor solutions.';
$activePage = 'partners';

// Partner case studies (managed in Admin > Partners; falls back to data/partners.php)
require_once __DIR__ . '/models/Partner.php';
$allPartners = Partner::getPublished();

// Pagination logic
$itemsPerPage = 6;
$totalPages = ceil(count($allPartners) / $itemsPerPage);
$chunks = array_chunk($allPartners, $itemsPerPage);

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Hero Header Banner -->
<section class="page-hero-banner" style="background: linear-gradient(180deg, rgba(6, 15, 30, 0.88) 0%, rgba(6, 15, 30, 0.98) 100%), url('<?= asset(Setting::getOr('hero_partners', 'images/hero_chip_bg.webp')) ?>') center/cover no-repeat; padding: 180px 0 85px; position: relative;">
  <div class="container">
    <div class="reveal active">
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" style="margin-bottom: 20px;">
        <ol style="display: flex; gap: 8px; font-size: 0.88rem; color: #94A3B8; list-style: none; padding: 0; margin: 0; align-items: center;">
          <li><a href="<?= url() ?>" style="color: #94A3B8; text-decoration: none;">Home</a></li>
          <li>/</li>
          <li style="color: var(--primary-orange); font-weight: 700; text-transform: uppercase;">Partners</li>
        </ol>
      </nav>

      <h1 style="font-family: var(--heading-font); font-size: 3.2rem; font-weight: 900; color: #FFFFFF; text-transform: uppercase; letter-spacing: -0.02em; line-height: 1.1; margin-bottom: 14px;"<?= lt_attr('partners_hero_title', false, 'Tiêu đề Hero') ?>><?= lt('partners_hero_title', 'OUR PARTNERS') ?></h1>
      <p style="color: #CBD5E1; font-size: 1.15rem; max-width: 750px; line-height: 1.7; margin: 0;"<?= lt_attr('partners_hero_subtitle', false, 'Mô tả Hero') ?>><?= lt('partners_hero_subtitle', 'Collaborating with tier-1 foundries, leading EDA providers, and innovative fabless innovators to pioneer next-generation silicon tapeouts.') ?></p>
    </div>
  </div>
</section>

<!-- 2. Main Partners Area with SIDEBAR -->
<section class="partners-page-content" id="partnersContent" style="background-color: #FFFFFF; padding: 80px 0 100px; color: #1E293B;">
  <div class="container">
    <div class="partners-layout-grid" style="display: grid; grid-template-columns: 1fr 380px; gap: 50px; align-items: start;">

      <!-- Left Column: Partners Cards Grid -->
      <div class="partners-main-col">

        <?php foreach ($chunks as $index => $pageItems): ?>
          <?php $pageNum = $index + 1; ?>
          <div class="partner-page <?= ($pageNum === 1) ? 'active' : '' ?>" id="page-<?= $pageNum ?>">
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 28px;">
              <?php foreach ($pageItems as $item): ?>
                <a href="<?= url('partner-detail.php?slug=' . urlencode($item['slug'])) ?>" class="partner-post-card reveal active">
                  <div class="partner-card-media-wrap">
                    <img src="<?= asset($item['image_url']) ?>" alt="<?= e($item['title']) ?>" class="partner-card-thumb">
                    <?php if (!empty($item['is_pinned'])): ?>
                      <span class="badge-featured"><i class="fa-solid fa-thumbtack"></i> FEATURED</span>
                    <?php endif; ?>
                  </div>
                  <div class="partner-card-info">
                    <span class="partner-card-category"><?= e($item['category_name']) ?></span>
                    <h3 class="partner-card-name"><?= e($item['title']) ?></h3>
                    <p class="partner-card-summary"><?= e($item['excerpt']) ?></p>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>

        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
          <div class="pagination-bar" style="margin-top: 60px; display: flex; justify-content: center; gap: 10px;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <button class="page-nav-btn <?= ($i === 1) ? 'active' : '' ?>" data-page="<?= $i ?>">
                <?= $i ?>
              </button>
            <?php endfor; ?>
            <button class="page-nav-btn next-btn" id="nextPartnerPage">Next &rarr;</button>
          </div>
        <?php endif; ?>

      </div>

      <!-- Right Column: Sidebar (ported from the vnchips_old "Banner Section: SIDEBAR") -->
      <?php require __DIR__ . '/includes/partners_sidebar.php'; ?>

    </div>
  </div>
</section>

<!-- Partner Cards and Pagination Styles -->
<style>
  .partner-page {
    display: none;
  }
  .partner-page.active {
    display: block;
    animation: fadeInPartner 0.4s ease forwards;
  }
  @keyframes fadeInPartner {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .page-nav-btn {
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    color: #0F172A;
    padding: 10px 20px;
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.88rem;
    cursor: pointer;
    transition: 0.3s;
  }
  .page-nav-btn:hover,
  .page-nav-btn.active {
    background: var(--primary-orange);
    border-color: var(--primary-orange);
    color: #FFFFFF;
    box-shadow: 0 6px 18px var(--primary-orange-glow);
  }
  @media (max-width: 991px) {
    .partners-layout-grid {
      grid-template-columns: 1fr !important;
      gap: 50px !important;
    }
    .partner-page > div {
      grid-template-columns: 1fr !important;
    }
    .sidebar-sticky {
      position: static !important;
    }
  }
</style>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const pageButtons = document.querySelectorAll('.page-nav-btn[data-page]');
    const pages = document.querySelectorAll('.partner-page');
    const nextBtn = document.getElementById('nextPartnerPage');
    let currentPage = 1;

    function goToPartnerPage(num) {
      currentPage = num;
      pages.forEach(p => p.classList.remove('active'));
      const targetPage = document.getElementById(`page-${num}`);
      if (targetPage) {
        targetPage.classList.add('active');
      }

      pageButtons.forEach(btn => {
        if (parseInt(btn.getAttribute('data-page')) === num) {
          btn.classList.add('active');
        } else {
          btn.classList.remove('active');
        }
      });

      const scrollTarget = document.getElementById('partnersContent').offsetTop - 100;
      window.scrollTo({ top: scrollTarget, behavior: 'smooth' });
    }

    pageButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const pNum = parseInt(btn.getAttribute('data-page'));
        goToPartnerPage(pNum);
      });
    });

    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        let next = currentPage + 1;
        if (next > pages.length) next = 1;
        goToPartnerPage(next);
      });
    }
  });
</script>

<!-- 3. Reusable CTA Banner -->
<?php require_once __DIR__ . '/includes/cta_banner.php'; ?>

<!-- 4. Global Footer -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>
