<?php
/**
 * Media Center - News & Press Releases
 * Matching Page 5 of VNCHIP Design Specification
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/News.php';

$categorySlug = isset($_GET['category']) ? trim($_GET['category']) : null;
$keyword = isset($_GET['q']) ? trim($_GET['q']) : null;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

// If 'all-categories' was passed, treat as null
if ($categorySlug === 'all-categories') {
    $categorySlug = null;
}

// Partner news belongs to the Partners page, not the Media Center
$excludedCategories = ['partners'];

$newsData = News::getAll($limit, $offset, $categorySlug, $keyword, 'published', $excludedCategories);
$articles = $newsData['items'];
$total = $newsData['total'];
$totalPages = max(1, ceil($total / $limit));

$categories = News::getCategories();

$pageTitle = 'Media Center | News, Events & Semiconductor Breakthroughs | VNCHIP Technology JSC';
$pageDescription = 'Explore the latest news, press releases, physical design achievements, and research milestones from VNCHIP Technology JSC.';
$activePage = 'media';

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Hero Section -->
<section class="media-hero-section">
  <div class="media-hero-bg" style="background-image: url('<?= lt_img('hero_media', 'images/media_hero_network.webp') ?>');"<?= lt_img_attr('hero_media', 'Ảnh banner Media Center') ?> data-lt-size="Khuyến nghị 1920 × 650 px (tối thiểu 1600 × 650), ảnh ngang, WEBP hoặc JPG dưới 500 KB. Ảnh được cắt theo kiểu cover, canh giữa ngang và 35% từ trên xuống; phần chữ nằm góc dưới trái."></div>
  <div class="container media-hero-content">
    <h1 class="media-hero-title"<?= lt_attr('media_hero_title', false, 'Tiêu đề Hero') ?>><?= lt('media_hero_title', 'MEDIA CENTER') ?></h1>
  </div>
</section>

<!-- 2. Main Media Center Content Section -->
<section class="media-center-page-section">
  <div class="container">

    <h2 class="media-page-title"<?= lt_attr('media_page_title', false, 'Tiêu đề trang') ?>><?= lt('media_page_title', 'MEDIA CENTER') ?></h2>

    <!-- Filter Pills & Search Bar -->
    <div class="media-filter-bar"<?= lt_admin('categories.php', 'Quản lý danh mục') ?>>
      <div class="media-filter-pills">
        <a href="<?= url('media-center.php') ?>" class="media-filter-pill <?= empty($categorySlug) ? 'active' : '' ?>">
          All Categories
        </a>
        <?php foreach ($categories as $cat): ?>
          <?php if ($cat['slug'] === 'all-categories' || in_array($cat['slug'], $excludedCategories, true)) continue; ?>
          <a href="<?= url('media-center.php?category=' . urlencode($cat['slug'])) ?>" class="media-filter-pill <?= ($categorySlug === $cat['slug']) ? 'active' : '' ?>">
            <?= e($cat['name']) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <form action="<?= url('media-center.php') ?>" method="GET" class="media-search-form">
        <?php if ($categorySlug): ?>
          <input type="hidden" name="category" value="<?= e($categorySlug) ?>">
        <?php endif; ?>
        <input type="text" name="q" value="<?= e($keyword) ?>" placeholder="Search articles..." class="media-search-input">
        <button type="submit" class="media-search-btn" aria-label="Search">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
      </form>
    </div>

    <!-- Articles Grid (3 columns x 2 rows) -->
    <?php if (empty($articles)): ?>
      <div style="background: #F8FAFC; padding: 60px; text-align: center; border-radius: 20px; color: #64748B; border: 1px dashed #CBD5E1;">
        <i class="fa-solid fa-newspaper" style="font-size: 3rem; color: #94A3B8; margin-bottom: 16px;"></i>
        <h3 style="font-family: var(--heading-font); font-weight: 800; letter-spacing: -0.02em; color: #0F172A; margin-bottom: 8px;">No Articles Found</h3>
        <p>Try refining your search keyword or selecting a different category filter.</p>
      </div>
    <?php else: ?>
      <div class="media-page-grid"<?= lt_admin('articles.php', 'Quản lý bài viết & tin tức') ?>>
        <?php foreach ($articles as $art): ?>
          <article class="media-page-card">
            <a href="<?= url('media-detail.php?slug=' . urlencode($art['slug'])) ?>" class="media-page-thumb" aria-label="<?= e($art['title']) ?>">
              <img src="<?= asset($art['thumbnail']) ?>" alt="<?= e($art['title']) ?>" loading="lazy">
            </a>
            <div class="media-page-body">
              <span class="media-page-cat"><?= e($art['category_name'] ?? 'News') ?></span>
              <h3 class="media-page-card-title">
                <a href="<?= url('media-detail.php?slug=' . urlencode($art['slug'])) ?>" style="color: inherit; text-decoration: none;">
                  <?= e($art['title']) ?>
                </a>
              </h3>
              <p class="media-page-summary"><?= e($art['summary']) ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <!-- Carousel / Pagination Arrows matching Page 5 of PDF -->
      <div class="media-pagination-row">
        <?php
          $prevPage = max(1, $page - 1);
          $nextPage = min($totalPages, $page + 1);
          $queryBase = '?';
          if ($categorySlug) $queryBase .= 'category=' . urlencode($categorySlug) . '&';
          if ($keyword) $queryBase .= 'q=' . urlencode($keyword) . '&';
        ?>
        <a href="<?= url('media-center.php' . $queryBase . 'page=' . $prevPage) ?>" class="nav-arrow-btn dark <?= ($page <= 1) ? 'disabled' : '' ?>" aria-label="Previous Page" style="<?= ($page <= 1) ? 'opacity: 0.4; pointer-events: none;' : '' ?>">
          <i class="fa-solid fa-chevron-left"></i>
        </a>
        <a href="<?= url('media-center.php' . $queryBase . 'page=' . $nextPage) ?>" class="nav-arrow-btn dark <?= ($page >= $totalPages) ? 'disabled' : '' ?>" aria-label="Next Page" style="<?= ($page >= $totalPages) ? 'opacity: 0.4; pointer-events: none;' : '' ?>">
          <i class="fa-solid fa-chevron-right"></i>
        </a>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
