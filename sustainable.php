<?php
/**
 * Sustainable & Talents Development Page (Page 4 of Design PDF)
 * VNCHIP Technology JSC
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Admin.php';

$pageTitle = 'Sustainable & Talent Development | VNCHIP Technology JSC';
$pageDescription = 'VNCHIP believes talent is the core value of semiconductor innovation. Discover our Talent Program, Interns Program, Social Responsibilities, and ISO-certified Clean Room confidentiality.';
$activePage = 'sustainable';

$susEsgText = Setting::get('section_sustainable_esg', '');
$cleanroomHeading = Setting::getOr('sus_cleanroom_heading', 'CLEAN ROOM');
$cleanroomSubheading = Setting::getOr('sus_cleanroom_subheading', 'Clean Room provides the security solutions tailored to meet the specific needs of our valued clients, ensuring a secure and controlled environment for handling sensitive information and projects.');
$talentsIntro = Setting::getOr('sus_talents_intro', "<p>At VNCHIP, we believe that talent is the core value of innovation and success in the IC design industry.</p>\n<p>VNCHIP experts' team is committed to pushing the boundaries of technology while staying true to our core values of knowledge-sharing, innovation, and client satisfaction. We are excited to continue growing and making a lasting impact on Vietnam IC Design industry.</p>");
$confidentialityText = Setting::getOr('sus_confidentiality_text', "<p>We understand the importance of safeguarding your sensitive information.</p>\n<p>We are committed to maintaining the highest standards of confidentiality, ensuring that all proprietary data, intellectual property, and project details are securely managed and protected.</p>\n<p>Through our policies, our Offshore Development Center (ODC) / clean room, and non-disclosure agreements, we prioritize the trust and privacy of our clients and partners.</p>\n<p>Your innovations and designs are safe and confidential, allowing you to focus on achieving your goals with confidence.</p>");
$partnerQuote = Setting::getOr('sus_partner_quote', '“EMPOWERING OUR PARTNERS WITH EXPERT DESIGN SOLUTIONS WHILE LEVERAGING COLLABORATION TO ACHIEVE GLOBAL STANDARDS.”');

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Hero Section (Deep Blue PCB Circuitry Backdrop) -->
<section class="sus-hero-section" style="background-image: url('<?= asset(Setting::getOr('hero_sustainable', 'images/hero_chip_bg.webp')) ?>'); background-size: cover; background-position: center;">
  <div class="sus-hero-bg"></div>
  <div class="container sus-hero-content">
    <h1 class="sus-hero-title"<?= lt_attr('sus_hero_title', false, 'Tiêu đề Hero') ?>><?= lt('sus_hero_title', 'SUSTAINABLE') ?></h1>
  </div>
</section>

<!-- 2. Social Responsibilities (navy card with orange accents) -->
<section class="sus-social-section" id="social-responsibilities">
  <div class="container">
    <div class="sus-social-card-wrap">
      <div class="sus-social-card">
        <h2 class="sus-social-title"<?= lt_attr('sus_social_title', false, 'Tiêu đề Social Responsibilities') ?>><?= lt('sus_social_title', 'SOCIAL RESPONSIBILITIES') ?></h2>
        <div class="sus-social-line"></div>
        <?php if (!empty($susEsgText)): ?>
          <div class="sus-social-text" id="live-sus-social-text">
            <?= $susEsgText ?>
          </div>
        <?php else: ?>
          <div class="sus-social-text" id="live-sus-social-text">
            <p>We are committed to making a positive impact beyond the IC design industry.</p>
            <p>We prioritize sustainable practices, foster community engagement, and invest in initiatives that support education and environmental conservation.</p>
            <p>Our mission extends to empowering local talent, creating job opportunities, and contributing to the growth of the semiconductor ecosystem.</p>
            <p>Through meaningful partnerships and programs, VNCHIP is dedicated to driving social progress and fostering a better future for the communities we serve.</p>
          </div>
        <?php endif; ?>
        <div class="sus-social-line"></div>
      </div>
    </div>
  </div>
</section>

<!-- 3. Clean Room (White Section) -->
<section class="about-cleanroom-section" id="clean-room">
  <div class="container">
    <div class="sus-cleanroom-line sus-cleanroom-line--top"></div>

    <?php if (!empty($customClean = Setting::get('section_sustainable_talent'))): ?>
      <div class="reveal active" id="live-sus-cleanroom-custom">
        <?= $customClean ?>
      </div>
    <?php else: ?>
    <div class="cleanroom-header-row" id="live-sus-cleanroom-box">
      <h2 class="cleanroom-heading" id="live-sus-cleanroom-heading"><?= e($cleanroomHeading) ?></h2>
      <p class="cleanroom-subheading" id="live-sus-cleanroom-subheading"><?= e($cleanroomSubheading) ?></p>
    </div>
    <?php endif; ?>

    <div class="cleanroom-photos-grid">
      <div class="cleanroom-photo-card">
        <img src="<?= asset('images/cleanroom_photo_hires.webp') ?>" alt="VNCHIP Clean Room Workspace 1" />
      </div>
      <div class="cleanroom-photo-card">
        <img src="<?= asset('images/cleanroom_photo_hires.webp') ?>" alt="VNCHIP Clean Room Workspace 2" />
      </div>
      <div class="cleanroom-photo-card">
        <img src="<?= asset('images/cleanroom_photo_hires.webp') ?>" alt="VNCHIP Clean Room Workspace 3" />
      </div>
    </div>

    <div class="sus-cleanroom-line sus-cleanroom-line--bottom"></div>
  </div>
</section>

<!-- 4. Talents Development Section (Dark Navy) -->
<section class="sus-talents-section" id="talents-development">
  <div class="container">

    <h2 class="sus-talents-title"<?= lt_attr('sus_talents_title', false, 'Tiêu đề Talents Development') ?>><?= lt('sus_talents_title', 'TALENTS DEVELOPMENT') ?></h2>

    <div class="sus-talents-intro" id="live-sus-talents-intro">
      <?= $talentsIntro ?>
    </div>

    <!-- 2 Large Program Cards (open the program detail page) -->
    <div class="sus-program-grid">

      <?php require __DIR__ . '/includes/program_cards.php'; ?>

    </div>

  </div>
</section>

<!-- 5. Confidentiality (bordered card) -->
<section class="sus-confidentiality-section" id="confidentiality">
  <div class="container">
    <div class="sus-confidentiality-card">
      <h2 class="sus-section-title"<?= lt_attr('sus_confidentiality_title', false, 'Tiêu đề Confidentiality') ?>><?= lt('sus_confidentiality_title', 'CONFIDENTIALITY') ?></h2>
      <div class="sus-paragraphs" id="live-sus-confidentiality-text">
        <?= $confidentialityText ?>
      </div>
    </div>
  </div>
</section>

<!-- 6. Partner: Partner values + university partner logos (White Background) -->
<section class="biz-values-section" id="partner">
  <div class="container">

    <h2 class="biz-values-title"<?= lt_attr('sus_partner_title', false, 'Tiêu đề Partners') ?>><?= lt('sus_partner_title', 'PARTNERS') ?></h2>

    <div class="biz-uni-header">
      <blockquote class="biz-values-quote" id="live-sus-partner-quote">
        <?= e($partnerQuote) ?>
      </blockquote>
      <div class="carousel-nav-arrows dark-arrows">
        <button class="nav-arrow-btn dark" aria-label="Previous Partner"><i class="fa-solid fa-chevron-left"></i></button>
        <button class="nav-arrow-btn dark" aria-label="Next Partner"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
    </div>

    <!-- 5 University Logos Grid -->
    <?php
      // University / institution logos: each slot is a Live Image (click the pencil to replace)
      $uniLogos = [
        ['key' => 'sus_uni_logo_1', 'img' => 'images/partner_hcmute.png', 'alt' => 'HCMUTE University Partner'],
        ['key' => 'sus_uni_logo_2', 'img' => 'images/partner_uit.png',    'alt' => 'UIT University Partner'],
        ['key' => 'sus_uni_logo_3', 'img' => 'images/partner_vgu.png',    'alt' => 'Vietnamese-German University Partner'],
        ['key' => 'sus_uni_logo_4', 'img' => 'images/partner_bkhcm.png',  'alt' => 'Bach Khoa Ho Chi Minh City Partner'],
        ['key' => 'sus_uni_logo_5', 'img' => 'images/partner_nic.png',    'alt' => 'National Innovation Center NIC'],
        ['key' => 'sus_uni_logo_6', 'img' => '',                          'alt' => 'Partner logo 6'],
        ['key' => 'sus_uni_logo_7', 'img' => '',                          'alt' => 'Partner logo 7'],
        ['key' => 'sus_uni_logo_8', 'img' => '',                          'alt' => 'Partner logo 8'],
      ];
    ?>
    <div class="biz-uni-grid">
      <?php foreach ($uniLogos as $i => $logo):
        $src = Setting::getOr($logo['key'], $logo['img']);
        if ($src === 'none') $src = '';                       // admin chose "Ẩn ảnh này"
        if ($src === '' && !Admin::check()) continue;        // empty slot: only an admin sees the placeholder
      ?>
      <div class="biz-uni-card<?= $src === '' ? ' live-empty-slot' : '' ?>">
        <img src="<?= $src !== '' ? asset($src) : asset('images/favicon.png') ?>" alt="<?= e($logo['alt']) ?>"<?= lt_img_attr($logo['key'], 'Logo đối tác ' . ($i + 1)) ?> data-lt-hideable="1">
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- Legacy anchors (#talent-program / #interns-program) now live on their own detail page -->
<script>
  (function () {
    var legacy = { '#talent-program': 'talent', '#interns-program': 'interns' };
    if (legacy[location.hash]) {
      location.replace('<?= url('program-detail.php?program=') ?>' + legacy[location.hash]);
    }
  })();
</script>

<!-- CTA & Footer -->
<?php require_once __DIR__ . '/includes/cta_banner.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
