<?php
/**
 * Reusable CTA Banner Component
 * Fully dynamic and supports Admin Live Edit
 */

if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}

$ctaTitle = Setting::getOr('cta_title', 'NEED SPECIALIZED SEMICONDUCTOR DESIGN SUPPORT?');
$ctaBtnText = Setting::getOr('cta_button_text', 'Talk to our experts');
$ctaBtnUrl = Setting::getOr('cta_button_url', 'talk-to-us.php');
$ctaBgImg = Setting::getOr('cta_bg_image', '');
$ctaBoxStyle = '';
if (!empty($ctaBgImg)) {
    $ctaBoxStyle = "background: linear-gradient(90deg, rgba(6, 42, 77, 0.9) 0%, rgba(10, 25, 47, 0.8) 100%), url('" . asset($ctaBgImg) . "') center/cover no-repeat !important;";
}
?>
<!-- CTA SECTION (Ported from vnchips_old & Enhanced for Live Edit) -->
<section class="cta-section-new reveal active" id="global-cta-section">
  <div class="container">
    <div class="cta-box-new" id="live-cta-box"<?php if ($ctaBoxStyle): ?> style="<?= $ctaBoxStyle ?>"<?php endif; ?>>
      <h2 id="live-cta-title"><?= e($ctaTitle) ?></h2>
      <a href="<?= (str_starts_with($ctaBtnUrl, 'http://') || str_starts_with($ctaBtnUrl, 'https://')) ? e($ctaBtnUrl) : url($ctaBtnUrl) ?>" class="btn btn-cta-large" id="live-cta-btn">
        <span id="live-cta-btn-text"><?= e($ctaBtnText) ?></span> <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>
  </div>
</section>
