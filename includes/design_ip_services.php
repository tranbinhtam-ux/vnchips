<?php
/**
 * Shared renderer: DESIGN SERVICE / IP SERVICE accordion
 *
 * Used by index.php and business.php so the two pages can never drift apart again.
 * Content comes from the `service_items` table; `[[keyword]]` markers inside an item
 * body are expanded into hover tooltips by ServiceTooltip.
 *
 * Caller may set, before the require:
 *   $servicesWithAnchors = true;   // business.php: emit the #design-service / #spec-gdsii anchors
 */

if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}

require_once __DIR__ . '/../models/ServiceItem.php';
require_once __DIR__ . '/../models/ServiceTooltip.php';
require_once __DIR__ . '/../models/Admin.php';

$servicesWithAnchors = !empty($servicesWithAnchors);

$servicesShow         = Setting::get('show_design_ip_services', '1') === '1';
$servicesAdminPreview = Admin::check();

// A hidden section is still drawn for a signed-in admin (greyed out, flagged), otherwise
// switching it off would also remove the Live Edit badge needed to switch it back on.
if (!$servicesShow && !$servicesAdminPreview) {
    return;
}

$servicesDesignTitle = Setting::getOr('services_design_title', 'DESIGN SERVICE');
$servicesIpTitle     = Setting::getOr('services_ip_title', 'IP SERVICE');

$servicesDesignItems = ServiceItem::getActive('design');
$servicesIpItems     = ServiceItem::getActive('ip');

/**
 * Safety net: if the tables are missing or still empty (migration not run yet) fall back to
 * the imported snapshot so the live pages never come up without this section.
 */
if (!$servicesDesignItems && !$servicesIpItems) {
    $servicesSnapshot = __DIR__ . '/../database/seed_design_ip_services.html';
    if (is_file($servicesSnapshot)) {
        echo str_replace('src="uploads/', 'src="' . BASE_URL . 'uploads/', file_get_contents($servicesSnapshot));
    }
    return;
}

/** One accordion column */
$renderServiceColumn = static function (array $items, $columnTitle, $accordionId, $columnAnchor, $withAnchors) {
    ?>
      <div class="service-col"<?= ($withAnchors && $columnAnchor) ? ' id="' . $columnAnchor . '"' : '' ?>>
        <h2 class="service-col-title"><?= e($columnTitle) ?></h2>
        <div class="service-accordion" id="<?= e($accordionId) ?>">
          <?php foreach ($items as $item): ?>
          <div class="service-item"<?= ($withAnchors && !empty($item['anchor_id'])) ? ' id="' . e($item['anchor_id']) . '"' : '' ?> data-service-id="<?= (int)$item['id'] ?>">
            <div class="service-header">
              <span class="service-name"><?= e($item['title']) ?></span>
              <span class="service-icon">+</span>
            </div>
            <div class="service-body">
              <?php if (!empty($item['image_url'])): ?>
              <img src="<?= asset($item['image_url']) ?>" alt="<?= e($item['title']) ?>" class="service-body-img img-fluid rounded mb-3" loading="lazy">
              <?php endif; ?>
              <?= ServiceTooltip::expand($item['body']) ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php
};
?>
    <div id="live-services-wrap"<?= $servicesShow ? '' : ' class="live-section-off" data-section-hidden="1"' ?>>
      <?php if (!$servicesShow): ?>
        <div class="live-section-off-flag" id="live-services-off-flag">
          <i class="fa-solid fa-eye-slash"></i>
          Khối Design Service &amp; IP Service đang <strong>TẮT</strong> &mdash; khách truy cập không nhìn thấy. Bạn thấy được vì đang đăng nhập quản trị.
        </div>
      <?php endif; ?>

      <div class="services-grid"<?= $servicesWithAnchors ? ' id="design-service"' : '' ?>>

        <!-- Column 1: Design Service -->
        <?php $renderServiceColumn($servicesDesignItems, $servicesDesignTitle, 'accDesign', '', $servicesWithAnchors); ?>

        <!-- Column 2: IP Service -->
        <?php $renderServiceColumn($servicesIpItems, $servicesIpTitle, 'accIP', 'ip-service', $servicesWithAnchors); ?>

      </div>
    </div>
