<?php
/**
 * Partners sidebar: technical support contact, Design/IP service links, CTA banner
 * (shared by partners.php & partner-detail.php).
 *
 * Every piece of text, link and the e-mail/address is a Live Text slot (lt()), so an admin
 * edits it in place. E-mail and USA address share the site_email / site_address_us settings
 * with talk-to-us.php and the footer, so one edit updates them everywhere.
 */
if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}

$sbEmail   = Setting::getOr('site_email', SITE_EMAIL);
$sbAddress = Setting::getOr('site_address_us', SITE_ADDRESS_US);

/** One service link row: text + href are both editable */
$sbLink = static function ($key, $defaultText, $defaultHref, $icon) {
    $href = Setting::getOr($key . '_url', $defaultHref);
    $hrefOut = preg_match('~^https?://~i', $href) ? $href : url($href);
    ?>
              <li><a href="<?= e($hrefOut) ?>" data-lt="<?= e($key) ?>_url" data-lt-attr="href" data-lt-label="Link: <?= e($defaultText) ?>" style="color: #475569; font-size: 0.92rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 10px;"><i class="fas <?= e($icon) ?>" style="color: var(--primary-orange); font-size: 0.85rem;"></i> <span<?= lt_attr($key, false, 'Tên link dịch vụ') ?>><?= lt($key, $defaultText) ?></span></a></li>
    <?php
};

$sbBtnUrl = Setting::getOr('sidebar_contact_btn_url', 'talk-to-us.php');
$sbBtnUrl = preg_match('~^https?://~i', $sbBtnUrl) ? $sbBtnUrl : url($sbBtnUrl);
$sbCtaUrl = Setting::getOr('sidebar_cta_btn_url', 'talk-to-us.php');
$sbCtaUrl = preg_match('~^https?://~i', $sbCtaUrl) ? $sbCtaUrl : url($sbCtaUrl);
?>
      <aside class="sidebar-col">
        <div class="sidebar-sticky" style="position: sticky; top: 120px;">

          <!-- Quick Contact Widget -->
          <div class="sidebar-widget" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 20px; padding: 30px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
            <h5 style="font-family: var(--heading-font); font-weight: 800; font-size: 1.1rem; color: #0F172A; text-transform: uppercase; margin-bottom: 20px; border-bottom: 2px solid #F1F5F9; padding-bottom: 12px; letter-spacing: -0.01em;"<?= lt_attr('sidebar_support_title', false, 'Tiêu đề khối hỗ trợ') ?>><?= lt('sidebar_support_title', 'Technical Support') ?></h5>

            <div style="display: flex; gap: 16px; margin-bottom: 18px; align-items: flex-start;">
              <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(242, 91, 34, 0.1); color: var(--primary-orange); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.1rem;">
                <i class="fas fa-headset"></i>
              </div>
              <div>
                <div style="font-size: 0.78rem; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;"<?= lt_attr('sidebar_support_label_1', false, 'Nhãn dòng 1') ?>><?= lt('sidebar_support_label_1', 'CONSULTATION') ?></div>
                <a href="mailto:<?= e($sbEmail) ?>" style="font-size: 0.95rem; font-weight: 700; color: #0F172A; text-decoration: none; word-break: break-all;"<?= lt_attr('site_email', false, 'Email liên hệ (dùng chung toàn site)') ?>><?= e($sbEmail) ?></a>
              </div>
            </div>

            <div style="display: flex; gap: 16px; margin-bottom: 24px; align-items: flex-start;">
              <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(242, 91, 34, 0.1); color: var(--primary-orange); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.1rem;">
                <i class="fas fa-building"></i>
              </div>
              <div>
                <div style="font-size: 0.78rem; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;"<?= lt_attr('sidebar_support_label_2', false, 'Nhãn dòng 2') ?>><?= lt('sidebar_support_label_2', 'USA OFFICE') ?></div>
                <div style="font-size: 0.88rem; color: #334155; line-height: 1.5;"<?= lt_attr('site_address_us', false, 'Địa chỉ văn phòng Mỹ (dùng chung toàn site)') ?>><?= e($sbAddress) ?></div>
              </div>
            </div>

            <a href="<?= e($sbBtnUrl) ?>" data-lt="sidebar_contact_btn_url" data-lt-attr="href" data-lt-label="Link nút Send a Message" style="display: block; width: 100%; text-align: center; border: 1.5px solid #0F172A; color: #0F172A; padding: 12px; border-radius: 50px; font-weight: 700; font-size: 0.9rem; text-decoration: none; transition: 0.3s;" onmouseover="this.style.background='#0F172A';this.style.color='#fff';" onmouseout="this.style.background='transparent';this.style.color='#0F172A';">
              <span<?= lt_attr('sidebar_contact_btn_text', false, 'Chữ nút liên hệ') ?>><?= lt('sidebar_contact_btn_text', 'Send a Message') ?></span>
            </a>
          </div>

          <!-- ASIC Service Links Widget -->
          <div class="sidebar-widget" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 20px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
            <h5 style="font-family: var(--heading-font); font-weight: 800; font-size: 1.05rem; color: #0F172A; text-transform: uppercase; margin-bottom: 16px; border-bottom: 2px solid #F1F5F9; padding-bottom: 12px; letter-spacing: -0.01em;"<?= lt_attr('sidebar_design_title', false, 'Tiêu đề nhóm Design Service') ?>><?= lt('sidebar_design_title', 'Design Service') ?></h5>
            <ul style="list-style: none; padding: 0; margin: 0 0 24px 0; display: flex; flex-direction: column; gap: 12px;">
              <?php
                $sbLink('sidebar_link_design_1', 'Specification to GDSII', 'business.php#spec-gdsii', 'fa-file-invoice');
                $sbLink('sidebar_link_design_2', 'RTL to GDSII', 'business.php#rtl-gdsii', 'fa-microchip');
                $sbLink('sidebar_link_design_3', 'Netlist to GDSII', 'business.php#netlist-gdsii', 'fa-code');
              ?>
            </ul>

            <h5 style="font-family: var(--heading-font); font-weight: 800; font-size: 1.05rem; color: #0F172A; text-transform: uppercase; margin-bottom: 16px; border-bottom: 2px solid #F1F5F9; padding-bottom: 12px; letter-spacing: -0.01em;"<?= lt_attr('sidebar_ip_title', false, 'Tiêu đề nhóm IP Service') ?>><?= lt('sidebar_ip_title', 'IP Service') ?></h5>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">
              <?php
                $sbLink('sidebar_link_ip_1', 'IP Development', 'business.php#ip-development', 'fa-file-invoice');
                $sbLink('sidebar_link_ip_2', 'Circuit Design', 'business.php#circuit-design', 'fa-microchip');
                $sbLink('sidebar_link_ip_3', 'Custom Layout', 'business.php#custom-layout', 'fa-code');
              ?>
            </ul>
          </div>

          <!-- Banner Section: SIDEBAR -->
          <div class="sidebar-cta-banner" style="background: linear-gradient(135deg, #0A192F 0%, #062A4D 100%); border: 1px solid rgba(0, 210, 255, 0.25); border-radius: 20px; padding: 32px 26px; color: #FFFFFF; text-align: center; margin-top: 30px; box-shadow: 0 12px 35px rgba(0,0,0,0.12);">
            <div style="width: 52px; height: 52px; border-radius: 50%; background: var(--primary-orange); display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; font-size: 1.3rem; color: #FFF; box-shadow: 0 0 20px var(--primary-orange-glow);">
              <i class="fa-solid fa-microchip"></i>
            </div>
            <h4 style="font-family: var(--heading-font); font-weight: 800; font-size: 1.15rem; color: #FFFFFF; margin-bottom: 12px; text-transform: uppercase; letter-spacing: -0.01em;"<?= lt_attr('sidebar_cta_title', false, 'Tiêu đề khối CTA sidebar') ?>><?= lt('sidebar_cta_title', 'NEED SPECIALIZED IC DESIGN SUPPORT?') ?></h4>
            <p style="font-size: 0.88rem; color: #CBD5E1; margin-bottom: 22px; line-height: 1.6;"<?= lt_attr('sidebar_cta_desc', false, 'Mô tả khối CTA sidebar') ?>><?= lt('sidebar_cta_desc', 'Collaborate directly with our technical fellows and physical implementation architects from 65nm down to 3nm FinFET.') ?></p>
            <a href="<?= e($sbCtaUrl) ?>" data-lt="sidebar_cta_btn_url" data-lt-attr="href" data-lt-label="Link nút CTA sidebar" style="display: inline-flex; align-items: center; gap: 8px; background: var(--primary-orange); color: #FFFFFF; font-weight: 700; font-size: 0.92rem; padding: 12px 28px; border-radius: 50px; text-decoration: none; transition: 0.3s; box-shadow: 0 4px 18px var(--primary-orange-glow);">
              <span<?= lt_attr('sidebar_cta_btn_text', false, 'Chữ nút CTA sidebar') ?>><?= lt('sidebar_cta_btn_text', 'Talk to our experts') ?></span> <i class="fa-solid fa-arrow-right"></i>
            </a>
          </div>

        </div>
      </aside>
