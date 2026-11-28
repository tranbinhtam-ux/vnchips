<?php
/**
 * The two program cards (Talent / Interns) shown on index.php and sustainable.php#talents-development.
 * Icons and labels are Live Text / Live Image slots; the pages themselves are program-detail.php.
 */
if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}
?>
          <a href="<?= url('program-detail.php?program=talent') ?>" class="program-card" title="VNCHIP Talent Program">
            <div class="program-card-inner">
              <div class="program-card-visual">
                <img src="<?= lt_img('program_card_talent_icon', 'images/interns.svg') ?>" alt="VNCHIP Talent Program Icon" class="program-icon-img"<?= lt_img_attr('program_card_talent_icon', 'Icon thẻ Talent Program') ?>>
              </div>
              <div class="program-card-footer">
                <div class="program-card-brand"<?= lt_attr('program_card_talent_brand', false, 'Dòng trên thẻ Talent') ?>><?= lt('program_card_talent_brand', 'VNCHIP') ?></div>
                <div class="program-card-title"<?= lt_attr('program_card_talent_title', false, 'Tên thẻ Talent') ?>><?= lt('program_card_talent_title', 'TALENT PROGRAM') ?></div>
              </div>
            </div>
          </a>

          <a href="<?= url('program-detail.php?program=interns') ?>" class="program-card" title="VNCHIP Interns Program">
            <div class="program-card-inner">
              <div class="program-card-visual">
                <img src="<?= lt_img('program_card_interns_icon', 'images/talent.svg') ?>" alt="VNCHIP Interns Program Icon" class="program-icon-img"<?= lt_img_attr('program_card_interns_icon', 'Icon thẻ Interns Program') ?>>
              </div>
              <div class="program-card-footer">
                <div class="program-card-brand"<?= lt_attr('program_card_interns_brand', false, 'Dòng trên thẻ Interns') ?>><?= lt('program_card_interns_brand', 'VNCHIP') ?></div>
                <div class="program-card-title"<?= lt_attr('program_card_interns_title', false, 'Tên thẻ Interns') ?>><?= lt('program_card_interns_title', 'INTERNS PROGRAM') ?></div>
              </div>
            </div>
          </a>
