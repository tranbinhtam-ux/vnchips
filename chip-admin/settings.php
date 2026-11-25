<?php
/**
 * Admin Homepage & System Settings Management
 * Modern Semiconductor UI with Live Statistics, Pill Banner, Office Info, and Program Links.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../models/Setting.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = [
        'stat_years_number', 'stat_years_label', 'stat_years_desc',
        'stat_asic_number', 'stat_asic_label', 'stat_asic_desc',
        'stat_chips_number', 'stat_chips_label', 'stat_chips_desc',
        'homepage_pill_banner',
        'cta_title', 'cta_button_text', 'cta_button_url',
        'footer_copyright',
        'footer_partner_link_1', 'footer_partner_logo_1',
        'footer_partner_link_2', 'footer_partner_logo_2',
        'site_email', 'site_phone', 'site_address_vn', 'site_address_us',
        'program_talent_apply_url', 'program_interns_apply_url', 'about_show_key_team',
        'about_leadership_title', 'about_founder_photo', 'about_founder_name', 
        'about_founder_title', 'about_founder_quote', 'about_leadership_subtext',
        'about_key_team_title', 'about_key_team_subtext',
        'contact_page_title', 'contact_offices_title', 'contact_offices_desc',
        'sus_cleanroom_heading', 'sus_cleanroom_subheading', 'sus_talents_intro',
        'sus_confidentiality_text', 'sus_partner_quote'
    ];

    $updates = [];
    foreach ($keys as $k) {
        if (isset($_POST[$k])) {
            $updates[$k] = trim($_POST[$k]);
        }
    }

    if (Setting::updateBatch($updates)) {
        $message = 'Homepage stats and system settings updated successfully!';
    } else {
        $error = 'Failed to update settings in database.';
    }
}

$adminTitle = 'Homepage & System Settings';
$adminActivePage = 'settings';

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-panel mb-4">
  <div class="panel-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <h3 class="panel-title mb-1">
        <i class="fa-solid fa-sliders text-primary me-2"></i>Homepage Content & System Metrics
      </h3>
      <p class="text-muted small mb-0">Manage key performance metrics, pill announcement text, office addresses, and application forms</p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= url('index.php') ?>" target="_blank" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View Live Homepage
      </a>
    </div>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fa-solid fa-circle-check me-2"></i><?= e($message) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fa-solid fa-circle-exclamation me-2"></i><?= e($error) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <form action="<?= admin_url('settings.php') ?>" method="POST">
    
    <!-- 1. Hero Key Stats Section -->
    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
      <h5 class="fw-bold text-dark mb-1">
        <i class="fa-solid fa-chart-simple text-primary me-2"></i>Homepage Key Metrics Bar (3 Silicon Metrics)
      </h5>
      <p class="text-muted small mb-4">Displayed prominently beneath the hero video banner on the homepage</p>

      <div class="row g-4">
        <!-- Stat 1 -->
        <div class="col-12 col-md-4">
          <div class="p-3 rounded-3 border bg-light h-100">
            <div class="fw-bold text-dark mb-3"><i class="fa-solid fa-calendar-check text-primary me-1"></i> Metric #1: Experience</div>
            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label small text-muted">Number</label>
                <input type="text" name="stat_years_number" class="form-control" value="<?= e(Setting::get('stat_years_number', '6+')) ?>">
              </div>
              <div class="col-6">
                <label class="form-label small text-muted">Unit Label</label>
                <input type="text" name="stat_years_label" class="form-control" value="<?= e(Setting::get('stat_years_label', 'years')) ?>">
              </div>
            </div>
            <div>
              <label class="form-label small text-muted">Description</label>
              <textarea name="stat_years_desc" rows="3" class="form-control small"><?= e(Setting::get('stat_years_desc', 'Deep expertise in physical design and implementation, custom circuit layout, and system design - from 65nm to 3nm nodes')) ?></textarea>
            </div>
          </div>
        </div>

        <!-- Stat 2 -->
        <div class="col-12 col-md-4">
          <div class="p-3 rounded-3 border bg-light h-100">
            <div class="fw-bold text-dark mb-3"><i class="fa-solid fa-microchip text-primary me-1"></i> Metric #2: ASICs Delivered</div>
            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label small text-muted">Number</label>
                <input type="text" name="stat_asic_number" class="form-control" value="<?= e(Setting::get('stat_asic_number', '40+')) ?>">
              </div>
              <div class="col-6">
                <label class="form-label small text-muted">Unit Label</label>
                <input type="text" name="stat_asic_label" class="form-control" value="<?= e(Setting::get('stat_asic_label', 'ASIC')) ?>">
              </div>
            </div>
            <div>
              <label class="form-label small text-muted">Description</label>
              <textarea name="stat_asic_desc" rows="3" class="form-control small"><?= e(Setting::get('stat_asic_desc', 'Successful silicon solutions delivered across AI compute, mobile SOC, and 5G signal processing')) ?></textarea>
            </div>
          </div>
        </div>

        <!-- Stat 3 -->
        <div class="col-12 col-md-4">
          <div class="p-3 rounded-3 border bg-light h-100">
            <div class="fw-bold text-dark mb-3"><i class="fa-solid fa-boxes-stacked text-primary me-1"></i> Metric #3: Volume Production</div>
            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label small text-muted">Number</label>
                <input type="text" name="stat_chips_number" class="form-control" value="<?= e(Setting::get('stat_chips_number', '5M+')) ?>">
              </div>
              <div class="col-6">
                <label class="form-label small text-muted">Unit Label</label>
                <input type="text" name="stat_chips_label" class="form-control" value="<?= e(Setting::get('stat_chips_label', 'chips')) ?>">
              </div>
            </div>
            <div>
              <label class="form-label small text-muted">Description</label>
              <textarea name="stat_chips_desc" rows="3" class="form-control small"><?= e(Setting::get('stat_chips_desc', 'Delivered annually to global partners, testing and post-silicon supports')) ?></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Homepage Pill Banner -->
    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
      <h5 class="fw-bold text-dark mb-1">
        <i class="fa-solid fa-bullhorn text-info me-2"></i>Homepage Curved Pill Banner Announcement
      </h5>
      <p class="text-muted small mb-3">Highlights mission statement and technology transfer orientation on the homepage</p>
      
      <div>
        <textarea name="homepage_pill_banner" rows="3" class="form-control"><?= e(Setting::get('homepage_pill_banner', 'We drive progress through training, research collaboration, and technology transfer, aligning with international standards and evolving industry trends in the global semiconductor landscape.')) ?></textarea>
      </div>
    </div>

    <!-- 2b. Global Call To Action (CTA Banner) -->
    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
      <h5 class="fw-bold text-dark mb-1">
        <i class="fa-solid fa-bullhorn text-warning me-2"></i>Global Call To Action (CTA Banner)
      </h5>
      <p class="text-muted small mb-3">Banner xuất hiện ở cuối trang chủ và tất cả các trang giới thiệu dịch vụ để kêu gọi liên hệ</p>
      
      <div class="mb-3">
        <label class="form-label small fw-semibold text-muted">Tiêu đề Call To Action</label>
        <input type="text" name="cta_title" class="form-control fw-bold" value="<?= e(Setting::get('cta_title', 'NEED SPECIALIZED SEMICONDUCTOR DESIGN SUPPORT?')) ?>">
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold text-muted">Chữ trên nút bấm (Button Text)</label>
          <input type="text" name="cta_button_text" class="form-control" value="<?= e(Setting::get('cta_button_text', 'Talk to our experts')) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold text-muted">Đường dẫn liên kết (Button URL)</label>
          <input type="text" name="cta_button_url" class="form-control" value="<?= e(Setting::get('cta_button_url', 'talk-to-us.php')) ?>" placeholder="talk-to-us.php hoặc https://...">
        </div>
      </div>
    </div>

    <!-- 3. Office & Contact Details -->
    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
      <h5 class="fw-bold text-dark mb-1">
        <i class="fa-solid fa-location-dot text-danger me-2"></i>Headquarters & Global Office Addresses
      </h5>
      <p class="text-muted small mb-4">Displayed on footer and Talk to Us contact pages</p>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold text-muted"><i class="fa-solid fa-envelope me-1"></i> Primary Contact Email</label>
          <input type="email" name="site_email" class="form-control" value="<?= e(Setting::get('site_email', 'institute@vnchip-inc.com')) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold text-muted"><i class="fa-solid fa-phone me-1"></i> Hotline Phone Number</label>
          <input type="text" name="site_phone" class="form-control" value="<?= e(Setting::get('site_phone', '+84 - 28 268 268 1058')) ?>">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold text-muted"><i class="fa-solid fa-building me-1"></i> Vietnam R&D Center Address</label>
          <textarea name="site_address_vn" rows="2" class="form-control"><?= e(Setting::get('site_address_vn', '190 Doc Lap Street, Phu Tho Hoa Ward, Ho Chi Minh City, Vietnam')) ?></textarea>
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold text-muted"><i class="fa-solid fa-earth-americas me-1"></i> USA Liaison Office Address</label>
          <textarea name="site_address_us" rows="2" class="form-control"><?= e(Setting::get('site_address_us', '6500 River Place Blvd, Building 7, Suites 250 Austin, TX. 78730')) ?></textarea>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label small fw-semibold text-muted"><i class="fa-solid fa-copyright me-1"></i> Dòng chữ bản quyền Chân trang (Footer Copyright)</label>
        <input type="text" name="footer_copyright" class="form-control" value="<?= e(Setting::get('footer_copyright', 'Design & Copyright &copy; 2026 by VNCHIP Technology JSC. All rights reserved.')) ?>">
      </div>

      <hr class="my-3 text-muted opacity-25">

      <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-handshake text-primary me-2"></i>Logo Đối tác & Phân nhánh Chân trang (Partner & Sub-brand Logos)</h6>
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold text-muted">Đối tác 1 - URL Website</label>
          <input type="text" name="footer_partner_link_1" class="form-control" value="<?= e(Setting::get('footer_partner_link_1', 'https://vnchip-inc.com/')) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold text-muted">Đối tác 1 - Đường dẫn ảnh Logo</label>
          <input type="text" name="footer_partner_logo_1" class="form-control" value="<?= e(Setting::get('footer_partner_logo_1', 'images/logo_vnchip_main.png')) ?>">
        </div>
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold text-muted">Đối tác 2 - URL Website</label>
          <input type="text" name="footer_partner_link_2" class="form-control" value="<?= e(Setting::get('footer_partner_link_2', 'https://vnchip.edu.vn/')) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold text-muted">Đối tác 2 - Đường dẫn ảnh Logo</label>
          <input type="text" name="footer_partner_logo_2" class="form-control" value="<?= e(Setting::get('footer_partner_logo_2', 'images/footer_logo2.png')) ?>">
        </div>
      </div>
    </div>

    <!-- 4. Program Application Links & Visibility -->
    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
      <h5 class="fw-bold text-dark mb-1">
        <i class="fa-solid fa-graduation-cap text-success me-2"></i>Talent Programs & Visibility Settings
      </h5>
      <p class="text-muted small mb-4">Direct application links for candidates and section visibility switches</p>

      <div class="form-check form-switch mb-4">
        <input type="hidden" name="about_show_key_team" value="0">
        <input class="form-check-input" type="checkbox" name="about_show_key_team" id="about_show_key_team" value="1" 
               <?= Setting::get('about_show_key_team', '1') === '1' ? 'checked' : '' ?>>
        <label class="form-check-label fw-bold text-dark" for="about_show_key_team">
          Show the Key Team section on About Us page
        </label>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold text-muted">Talent Program Application Link</label>
          <input type="text" name="program_talent_apply_url" class="form-control" 
                 value="<?= e(Setting::get('program_talent_apply_url', '')) ?>" placeholder="https://forms.gle/... or talk-to-us.php">
          <div class="form-text small">Left blank: defaults to Talk to Us contact page.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold text-muted">Interns Program Application Link</label>
          <input type="text" name="program_interns_apply_url" class="form-control" 
                 value="<?= e(Setting::get('program_interns_apply_url', '')) ?>" placeholder="https://forms.gle/... or talk-to-us.php">
          <div class="form-text small">Left blank: defaults to Talk to Us contact page.</div>
        </div>
      </div>
    </div>

    <!-- 5. Leadership & Message from Founder -->
    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
      <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
        <h5 class="fw-bold text-dark mb-0">
          <i class="fa-solid fa-users-gear text-teal me-2" style="color: #0D9488;"></i>Leadership & Message from Founder Settings
        </h5>
        <a href="<?= admin_url('members.php') ?>" class="btn btn-sm btn-outline-primary">
          <i class="fa-solid fa-users me-1"></i> Quản lý danh sách Lãnh Đạo (Team Members)
        </a>
      </div>
      <p class="text-muted small mb-4">Cấu hình thông điệp của Người sáng lập và các tiêu đề hiển thị trong mục Leadership trang About Us</p>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold text-muted">Ảnh chân dung Người sáng lập (Standing Photo)</label>
          <input type="text" name="about_founder_photo" class="form-control" 
                 value="<?= e(Setting::get('about_founder_photo', 'assets/images/founder_tri_standing.webp')) ?>">
          <div class="form-text small">Mặc định: <code>assets/images/founder_tri_standing.webp</code>. Hoặc dùng Live Edit để upload ảnh trực tiếp.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold text-muted">Tên & Chức vụ Người sáng lập (Alt Text)</label>
          <input type="text" name="about_founder_name" class="form-control" 
                 value="<?= e(Setting::get('about_founder_name', 'Luc Duc Tri - Founder & CEO')) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold text-muted">Tiêu đề khung thông điệp</label>
          <input type="text" name="about_founder_title" class="form-control" 
                 value="<?= e(Setting::get('about_founder_title', 'MESSAGE FROM<br>FOUNDER')) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold text-muted">Tiêu đề mục Leadership</label>
          <input type="text" name="about_leadership_title" class="form-control" 
                 value="<?= e(Setting::get('about_leadership_title', 'LEADERSHIP')) ?>">
        </div>
        <div class="col-12">
          <label class="form-label small fw-semibold text-muted">Lời phát biểu / Thông điệp của Người sáng lập (Founder Quote)</label>
          <textarea name="about_founder_quote" rows="3" class="form-control"><?= e(Setting::get('about_founder_quote', "Our mission has always been to empower aspiring engineers and professionals with the skills needed to thrive in the fast-paced world of Integrated Circuit (IC) design. Today, we remain committed to delivering innovative, high-impact design solutions while driving growth and excellence in Vietnam’s IC design industry.")) ?></textarea>
        </div>
        <div class="col-12">
          <label class="form-label small fw-semibold text-muted">Đoạn giới thiệu phụ Leadership (Subtext)</label>
          <textarea name="about_leadership_subtext" rows="2" class="form-control"><?= e(Setting::get('about_leadership_subtext', 'Our focus is on driving innovation, enhancing performance, and addressing the critical challenges faced by the industry today in Vietnam.')) ?></textarea>
        </div>
      </div>
    </div>

    <!-- Sticky Save Bar -->
    <div class="card border-0 shadow-sm p-3 d-flex flex-row justify-content-between align-items-center" style="border-radius: 12px; background: #f8fafc;">
      <div class="small text-muted">
        <i class="fa-solid fa-circle-check text-success me-1"></i>
        Changes update database and live cache instantly.
      </div>
      <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
        <i class="fa-solid fa-floppy-disk me-1"></i> Save All Settings
      </button>
    </div>

  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
