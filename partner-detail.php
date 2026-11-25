<?php
/**
 * Partner Case Study Detail Page (Ported from vnchips_old single.php /article/{slug})
 * VNCHIP Technology JSC
 */

require_once __DIR__ . '/config/config.php';

require_once __DIR__ . '/models/Partner.php';
$allPartners = Partner::getPublished();

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$partner = null;
foreach ($allPartners as $item) {
    if ($item['slug'] === $slug) {
        $partner = $item;
        break;
    }
}

if (!$partner) {
    header("Location: " . url('partners.php'));
    exit;
}

// Related: the other partner case studies, pinned first (same rule as the old site)
$relatedPartners = array_values(array_filter($allPartners, function ($p) use ($partner) {
    return $p['slug'] !== $partner['slug'];
}));
usort($relatedPartners, function ($a, $b) {
    return $b['is_pinned'] <=> $a['is_pinned'];
});
$relatedPartners = array_slice($relatedPartners, 0, 3);

$pageTitle = $partner['title'] . ' | ' . SITE_NAME;
$pageDescription = $partner['excerpt'];
$activePage = 'partners';

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Hero Header Banner -->
<section class="page-hero-banner" style="background: linear-gradient(180deg, rgba(6, 15, 30, 0.88) 0%, rgba(6, 15, 30, 0.98) 100%), url('<?= asset(Setting::getOr('hero_partners', 'images/hero_chip_bg.webp')) ?>') center/cover no-repeat; padding: 180px 0 85px; position: relative;">
  <div class="container">
    <div class="reveal active">
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" style="margin-bottom: 20px;">
        <ol style="display: flex; gap: 8px; font-size: 0.88rem; color: #94A3B8; list-style: none; padding: 0; margin: 0; align-items: center; flex-wrap: wrap;">
          <li><a href="<?= url() ?>" style="color: #94A3B8; text-decoration: none;">Home</a></li>
          <li>/</li>
          <li><a href="<?= url('partners.php') ?>" style="color: #94A3B8; text-decoration: none;">Partners</a></li>
          <li>/</li>
          <li style="color: var(--primary-orange); font-weight: 700;"><?= e($partner['title']) ?></li>
        </ol>
      </nav>

      <h1 class="partner-detail-title">
        <?= e($partner['title']) ?>
      </h1>
    </div>
  </div>
</section>

<!-- 2. Article Body with SIDEBAR -->
<section style="background-color: #FFFFFF; padding: 70px 0 90px; color: #1E293B;">
  <div class="container">
    <div class="partners-layout-grid" style="display: grid; grid-template-columns: 1fr 380px; gap: 50px; align-items: start;">

      <article class="partner-detail-body">
        <?php if (!empty($partner['content_image'])): ?>
        <div class="partner-detail-figure">
          <img src="<?= asset($partner['content_image']) ?>" alt="<?= e($partner['title']) ?>">
        </div>
        <?php endif; ?>

        <h2 class="partner-detail-heading"><?= e($partner['content_heading']) ?></h2>
        <?php $partnerBody = (string)($partner['content_body'] ?? ''); ?>
        <?php if ($partnerBody !== strip_tags($partnerBody)): /* Rich HTML from CKEditor */ ?>
          <div class="partner-detail-text partner-detail-rich"><?= $partnerBody ?></div>
        <?php else: /* Legacy plain-text content */ ?>
          <p class="partner-detail-text"><?= nl2br(e($partnerBody)) ?></p>
        <?php endif; ?>

        <div class="partner-detail-footer">
          <a href="<?= url('partners.php') ?>" class="partner-detail-back">
            <i class="fa-solid fa-arrow-left"></i> Back to Partners
          </a>
          <a href="<?= url('talk-to-us.php') ?>" class="partner-detail-cta">
            Discuss a similar project <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </article>

      <?php require __DIR__ . '/includes/partners_sidebar.php'; ?>

    </div>
  </div>
</section>

<!-- 3. Related Posts -->
<?php if (!empty($relatedPartners)): ?>
<section style="background: #F8FAFC; border-top: 1px solid #EDF2F7; padding: 80px 0 100px;">
  <div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 32px; flex-wrap: wrap;">
      <h2 style="font-family: var(--heading-font); font-size: 1.5rem; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: -0.01em; margin: 0;">
        Related Posts
      </h2>
      <a href="<?= url('partners.php') ?>" style="color: var(--primary-orange); font-weight: 700; font-size: 0.92rem; text-decoration: none;">
        View all partners <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>

    <div class="partner-related-grid">
      <?php foreach ($relatedPartners as $item): ?>
        <a href="<?= url('partner-detail.php?slug=' . urlencode($item['slug'])) ?>" class="partner-post-card">
          <div class="partner-card-media-wrap">
            <img src="<?= asset($item['image_url']) ?>" alt="<?= e($item['title']) ?>" class="partner-card-thumb" loading="lazy">
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
</section>
<?php endif; ?>

<style>
  .partner-detail-title {
    font-family: var(--heading-font);
    font-size: 2.8rem;
    font-weight: 900;
    color: #FFFFFF;
    text-transform: uppercase;
    letter-spacing: -0.02em;
    line-height: 1.15;
    margin: 0;
    max-width: 980px;
  }
  .partner-detail-figure {
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid #E2E8F0;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
    background: #0A192F;
    margin-bottom: 36px;
  }
  .partner-detail-figure img {
    display: block;
    width: 100%;
    height: auto;
  }
  .partner-detail-heading {
    font-family: Arial, sans-serif;
    font-size: 1.5rem;
    font-weight: 700;
    color: #E67E22;
    line-height: 1.4;
    margin-bottom: 16px;
  }
  .partner-detail-text {
    font-size: 1.15rem;
    line-height: 1.85;
    color: #475569;
    text-align: justify;
    margin: 0;
  }
  .partner-detail-rich > *:first-child { margin-top: 0; }
  .partner-detail-rich > *:last-child { margin-bottom: 0; }
  .partner-detail-rich p { margin: 0 0 18px; }
  .partner-detail-rich ul,
  .partner-detail-rich ol { margin: 0 0 18px; padding-left: 24px; }
  .partner-detail-rich li { margin-bottom: 8px; }
  .partner-detail-rich h2,
  .partner-detail-rich h3,
  .partner-detail-rich h4 {
    font-family: Arial, sans-serif;
    font-weight: 700;
    color: #1E293B;
    line-height: 1.4;
    text-align: left;
    margin: 32px 0 14px;
  }
  .partner-detail-rich h2 { font-size: 1.45rem; }
  .partner-detail-rich h3 { font-size: 1.25rem; }
  .partner-detail-rich h4 { font-size: 1.1rem; }
  .partner-detail-rich a { color: #E67E22; text-decoration: underline; }
  .partner-detail-rich img {
    max-width: 100%;
    height: auto;
    border-radius: 10px;
    margin: 12px 0;
  }
  .partner-detail-rich blockquote {
    margin: 24px 0;
    padding: 14px 22px;
    border-left: 4px solid #E67E22;
    background: #F8FAFC;
    color: #334155;
    font-style: italic;
  }
  .partner-detail-rich table {
    width: 100%;
    border-collapse: collapse;
    margin: 0 0 18px;
    font-size: 1rem;
  }
  .partner-detail-rich table td,
  .partner-detail-rich table th {
    border: 1px solid #E2E8F0;
    padding: 10px 12px;
    text-align: left;
  }
  .partner-detail-rich table th { background: #F1F5F9; font-weight: 700; color: #1E293B; }
  .partner-detail-footer {
    margin-top: 44px;
    padding-top: 28px;
    border-top: 1px solid #E2E8F0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
  }
  .partner-detail-back {
    color: var(--primary-orange);
    font-weight: 700;
    font-size: 0.95rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  .partner-detail-cta {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--primary-orange);
    color: #FFFFFF;
    font-weight: 700;
    font-size: 0.92rem;
    padding: 12px 26px;
    border-radius: 50px;
    text-decoration: none;
    box-shadow: 0 4px 18px var(--primary-orange-glow);
  }
  .partner-related-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
  }
  @media (max-width: 991px) {
    .partners-layout-grid {
      grid-template-columns: 1fr !important;
      gap: 50px !important;
    }
    .sidebar-sticky {
      position: static !important;
    }
    .partner-related-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }
  @media (max-width: 600px) {
    .partner-detail-title {
      font-size: 1.8rem;
    }
    .partner-related-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<!-- 4. Reusable CTA Banner -->
<?php require_once __DIR__ . '/includes/cta_banner.php'; ?>

<!-- 5. Global Footer -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>
