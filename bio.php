<?php
/**
 * Member Biography Page (Ported & Enhanced from vnchips_old bio.php)
 * VNCHIP Technology JSC
 */

require_once __DIR__ . '/config/config.php';

// Members are managed in Admin > Team Members (falls back to data/members.php)
require_once __DIR__ . '/models/Member.php';
$allMembers = Member::getActive();

// Resolve requested member slug from URL, default to the first leader
$slug = isset($_GET['id']) ? trim($_GET['id']) : '';
$currentMember = Member::getBySlug($slug) ?? ($allMembers[0] ?? null);

if (!$currentMember) {
    header("Location: " . url('about-us.php'));
    exit;
}

// Leadership and Key Team share this page; labels and the "other members" list follow the member's section
$memberGroup = Member::groupOf($currentMember);
$isKeyTeam = $memberGroup === 'key_team';
$groupLabel = $isKeyTeam ? 'Key Team' : 'Leadership';
$groupAnchor = $isKeyTeam ? 'about-us.php#key-team' : 'about-us.php#leadership';
$otherMembers = Member::getActive($memberGroup);

$pageTitle = e($currentMember['name']) . ' - Biography | ' . SITE_NAME;
$pageDescription = e($currentMember['name']) . ' - ' . e($currentMember['position']) . ' at VNCHIP Technology JSC.';
$activePage = 'about';

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Hero Banner -->
<section class="page-hero-banner" style="background: linear-gradient(180deg, rgba(6, 15, 30, 0.88) 0%, rgba(6, 15, 30, 0.98) 100%), url('<?= asset(Setting::getOr('hero_about', 'images/hero_chip_bg.webp')) ?>') center/cover no-repeat; padding: 180px 0 85px; position: relative;">
  <div class="container">
    <div class="reveal active">
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" style="margin-bottom: 20px;">
        <ol style="display: flex; gap: 8px; font-size: 0.88rem; color: #94A3B8; list-style: none; padding: 0; margin: 0; align-items: center; flex-wrap: wrap;">
          <li><a href="<?= url() ?>" style="color: #94A3B8; text-decoration: none;">Home</a></li>
          <li>/</li>
          <li><a href="<?= url('about-us.php') ?>" style="color: #94A3B8; text-decoration: none;">About Us</a></li>
          <li>/</li>
          <li><a href="<?= url($groupAnchor) ?>" style="color: #94A3B8; text-decoration: none;"><?= e($groupLabel) ?></a></li>
          <li>/</li>
          <li style="color: var(--primary-orange); font-weight: 700; text-transform: uppercase;">Biography</li>
        </ol>
      </nav>

      <h1 style="font-family: var(--heading-font); font-size: 3.2rem; font-weight: 900; color: #FFFFFF; text-transform: uppercase; letter-spacing: -0.02em; line-height: 1.1; margin-bottom: 12px;">
        <?= e(strtoupper($groupLabel)) ?> BIOGRAPHY
      </h1>
      <p style="color: var(--primary-orange); font-size: 1.15rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin: 0;">
        <?= e($currentMember['position']) ?>
      </p>
    </div>
  </div>
</section>

<!-- 2. Detailed Biography Section -->
<section class="bio-detail-section" style="background-color: #FFFFFF; padding: 90px 0 110px; color: #1E293B;">
  <div class="container"<?= !empty($currentMember['id']) ? lt_admin('member-edit.php?id=' . (int)$currentMember['id'], 'Sửa hồ sơ thành viên này') : lt_admin('members.php', 'Quản lý thành viên') ?>>
    <div class="bio-layout-grid" style="display: grid; grid-template-columns: 400px 1fr; gap: 65px; align-items: start;">
      
      <!-- Left Column: Portrait Card -->
      <div class="bio-photo-card reveal active">
        <div style="border-radius: 24px; overflow: hidden; background: #0A192F; box-shadow: 0 25px 60px rgba(15, 23, 42, 0.16); border: 1px solid #E2E8F0; position: sticky; top: 120px;">
          <img src="<?= asset($currentMember['image_url']) ?>" alt="<?= e($currentMember['name']) ?>" style="width: 100%; aspect-ratio: 2 / 3; object-fit: cover; object-position: top center; display: block;">
          
          <div style="padding: 24px 24px 28px; background: linear-gradient(180deg, #0A192F 0%, #06152B 100%); text-align: center; border-top: 2px solid var(--primary-orange);">
            <h3 style="font-family: var(--heading-font); font-size: 1.35rem; font-weight: 800; color: #FFFFFF; margin-bottom: 6px; letter-spacing: -0.01em;">
              <?= e($currentMember['name']) ?>
            </h3>
            <p style="font-size: 0.88rem; color: #94A3B8; margin-bottom: 20px; font-weight: 500;">
              <?= e($currentMember['position']) ?>
            </p>

            <!-- Social Links -->
            <div style="display: flex; justify-content: center; gap: 14px;">
              <?php if (!empty($currentMember['linkedin_url'])): ?>
                <a href="<?= e($currentMember['linkedin_url']) ?>" target="_blank" rel="noopener" style="width: 42px; height: 42px; border-radius: 50%; border: 1.5px solid var(--primary-orange); display: inline-flex; align-items: center; justify-content: center; color: var(--primary-orange); text-decoration: none; transition: 0.3s;" title="LinkedIn Profile">
                  <i class="fa-brands fa-linkedin-in"></i>
                </a>
              <?php endif; ?>

              <?php if (!empty($currentMember['email_address'])): ?>
                <a href="mailto:<?= e($currentMember['email_address']) ?>" style="width: 42px; height: 42px; border-radius: 50%; border: 1.5px solid var(--primary-orange); display: inline-flex; align-items: center; justify-content: center; color: var(--primary-orange); text-decoration: none; transition: 0.3s;" title="Send Direct Email">
                  <i class="fa-solid fa-envelope"></i>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Biography Content -->
      <div class="bio-text-col reveal active">
        <h2 style="font-family: var(--heading-font); font-size: 2.5rem; font-weight: 800; color: var(--primary-orange); letter-spacing: -0.02em; margin-bottom: 8px;">
          <?= e($currentMember['name']) ?>
        </h2>
        <div style="font-size: 1.15rem; font-weight: 700; color: #0F172A; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 30px;">
          <?= e($currentMember['position']) ?>
        </div>
        
        <div class="bio-prose" style="font-size: 1.1rem; line-height: 2; color: #334155; text-align: justify;">
          <?= nl2br(e($currentMember['bio'])) ?>
        </div>

        <div style="margin-top: 50px; padding-top: 35px; border-top: 1px solid #E2E8F0; display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
          <a href="<?= url($groupAnchor) ?>" style="display: inline-flex; align-items: center; gap: 10px; color: var(--primary-orange); font-weight: 700; text-decoration: none; font-size: 1rem;">
            <i class="fa-solid fa-arrow-left"></i> Back to <?= $isKeyTeam ? 'Key Team' : 'Leadership Team' ?>
          </a>
          <a href="<?= url('talk-to-us.php') ?>" class="btn btn-cta-large" style="margin-left: auto;">
            Connect with our team <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>

        <?php if (count($otherMembers) > 1): ?>
        <!-- Other Leaders Carousel/Quick Links -->
        <div style="margin-top: 70px; padding: 35px; background: #F8FAFC; border-radius: 20px; border: 1px solid #E2E8F0;">
          <h4 style="font-family: var(--heading-font); font-size: 1.15rem; font-weight: 800; color: #0F172A; text-transform: uppercase; margin-bottom: 20px; letter-spacing: -0.01em;">
            <?= $isKeyTeam ? 'Other Key Team Members' : 'Other Executive Leaders' ?>
          </h4>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 18px;">
            <?php foreach ($otherMembers as $mItem): ?>
              <?php $mSlug = $mItem['slug']; ?>
              <?php if ($mSlug !== $currentMember['slug']): ?>
                <a href="<?= url('bio.php?id=' . $mSlug) ?>" style="display: flex; align-items: center; gap: 14px; padding: 12px 16px; background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; text-decoration: none; color: inherit; transition: 0.3s;" class="other-lead-card">
                  <img src="<?= asset($mItem['image_url']) ?>" alt="<?= e($mItem['name']) ?>" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; object-position: top center; flex-shrink: 0;">
                  <div>
                    <div style="font-family: var(--heading-font); font-size: 0.88rem; font-weight: 800; color: #0F172A;"><?= e($mItem['name']) ?></div>
                    <div style="font-size: 0.76rem; color: var(--primary-orange); font-weight: 600;"><?= e($mItem['position']) ?></div>
                  </div>
                </a>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

      </div>

    </div>
  </div>
</section>

<style>
  .other-lead-card:hover {
    transform: translateY(-3px);
    border-color: var(--primary-orange) !important;
    box-shadow: 0 8px 20px rgba(242, 91, 34, 0.12);
  }
  @media (max-width: 991px) {
    .bio-layout-grid {
      grid-template-columns: 1fr !important;
      gap: 40px !important;
    }
    .bio-photo-card div {
      position: static !important;
      max-width: 380px;
      margin: 0 auto;
    }
  }
</style>

<!-- 3. Global CTA Banner -->
<?php require_once __DIR__ . '/includes/cta_banner.php'; ?>

<!-- 4. Global Footer -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>
