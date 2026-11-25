<?php
/**
 * Media Article Detail Page
 * VNCHIP Technology JSC
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/News.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$article = News::getBySlug($slug);

if (!$article) {
    // If article not found, redirect to media center
    header("Location: " . url('media-center.php'));
    exit;
}

$relatedArticles = News::getRelated($article['category_id'], $article['id'], 3);

$pageTitle = $article['title'] . ' | VNCHIP Media Center';
$pageDescription = $article['summary'];
$activePage = 'media';

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Hero Header Banner (compact, image only) -->
<section class="media-hero-section media-hero-section--compact">
  <div class="media-hero-bg"></div>
</section>

<!-- 2. Article Body Section -->
<section style="background-color: #FFFFFF; padding: 40px 0 80px; color: #1E293B;">
  <div class="container" style="max-width: 960px;">

    <!-- Breadcrumb -->
    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.88rem; color: #64748B; margin-bottom: 20px; flex-wrap: wrap;">
      <a href="<?= url() ?>" style="color: #64748B; text-decoration: none;">Home</a>
      <span>/</span>
      <a href="<?= url('media-center.php') ?>" style="color: #64748B; text-decoration: none;">Media Center</a>
      <span>/</span>
      <span style="color: var(--primary-orange); font-weight: 700; text-transform: uppercase;">
        <?= e($article['category_name'] ?? 'Partners') ?>
      </span>
    </div>

    <!-- Article Title -->
    <h1 style="font-family: var(--heading-font); font-size: 2.4rem; font-weight: 800; line-height: 1.3; color: #0F172A; margin-bottom: 20px; letter-spacing: -0.02em;">
      <?= e($article['title']) ?>
    </h1>

    <!-- Meta Info -->
    <div style="display: flex; gap: 24px; color: #475569; font-size: 0.88rem; align-items: center; flex-wrap: wrap; margin-bottom: 35px;">
      <span><i class="fa-regular fa-calendar" style="color: var(--primary-orange); margin-right: 6px;"></i> <?= formatDate($article['published_at'], 'F d, Y') ?></span>
      <span><i class="fa-regular fa-user" style="color: var(--primary-orange); margin-right: 6px;"></i> <?= e($article['author']) ?></span>
      <span><i class="fa-regular fa-eye" style="color: var(--primary-orange); margin-right: 6px;"></i> <?= (int)$article['views'] ?> views</span>
    </div>

    <!-- Featured Image -->
    <div style="border-radius: 20px; overflow: hidden; margin-bottom: 40px; box-shadow: 0 12px 35px rgba(0,0,0,0.08); border: 1px solid #E2E8F0; background: #0A192F;">
      <img src="<?= asset($article['thumbnail']) ?>" alt="<?= e($article['title']) ?>" style="width: 100%; max-height: 520px; object-fit: contain; display: block; margin: 0 auto;">
    </div>

    <!-- Summary Lead Block -->
    <div style="font-size: 1.15rem; font-weight: 500; line-height: 1.75; color: #1E293B; margin-bottom: 35px; border-left: 4px solid var(--primary-orange); padding: 12px 24px; background: #FFF7ED; border-radius: 0 14px 14px 0;">
      <?= e($article['summary']) ?>
    </div>

    <!-- Rich HTML Content -->
    <div style="font-size: 1.05rem; line-height: 1.85; color: #334155;" class="article-content-body">
      <?= $article['content'] ?>
    </div>

    <!-- Social Share & Back Button Row -->
    <div style="margin-top: 50px; padding-top: 30px; border-top: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
      <a href="<?= url('media-center.php') ?>" style="color: var(--primary-orange); font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 0.95rem;">
        <i class="fa-solid fa-arrow-left"></i> Back to Media Center
      </a>
      
      <div style="display: flex; gap: 12px; align-items: center;">
        <span style="font-size: 0.88rem; color: #64748B; font-weight: 600;">Share this story:</span>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(url('media-detail.php?slug=' . $article['slug'])) ?>" target="_blank" rel="noopener" style="width: 36px; height: 36px; border-radius: 50%; background: #1877F2; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;" title="Share on Facebook">
          <i class="fa-brands fa-facebook-f"></i>
        </a>
        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode(url('media-detail.php?slug=' . $article['slug'])) ?>&title=<?= urlencode($article['title']) ?>" target="_blank" rel="noopener" style="width: 36px; height: 36px; border-radius: 50%; background: #0A66C2; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;" title="Share on LinkedIn">
          <i class="fa-brands fa-linkedin-in"></i>
        </a>
      </div>
    </div>

    <!-- Related Articles Grid -->
    <?php if (!empty($relatedArticles)): ?>
      <div style="margin-top: 70px; padding-top: 50px; border-top: 1px solid #E2E8F0;">
        <h3 style="font-family: var(--heading-font); font-size: 1.5rem; font-weight: 800; color: var(--primary-orange); letter-spacing: -0.02em; margin-bottom: 30px;">
          RELATED STORIES
        </h3>
        <div class="media-page-grid" style="margin-bottom: 0;">
          <?php foreach ($relatedArticles as $rel): ?>
            <article class="media-page-card">
              <a href="<?= url('media-detail.php?slug=' . urlencode($rel['slug'])) ?>" class="media-page-thumb" aria-label="<?= e($rel['title']) ?>">
                <img src="<?= asset($rel['thumbnail']) ?>" alt="<?= e($rel['title']) ?>" loading="lazy">
              </a>
              <div class="media-page-body">
                <span class="media-page-cat"><?= e($rel['category_name'] ?? 'Partners') ?></span>
                <h4 class="media-page-card-title" style="font-size: 1rem;">
                  <a href="<?= url('media-detail.php?slug=' . urlencode($rel['slug'])) ?>" style="color: inherit; text-decoration: none;">
                    <?= e($rel['title']) ?>
                  </a>
                </h4>
                <p class="media-page-summary" style="font-size: 0.85rem;"><?= e($rel['summary']) ?></p>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
