<?php
/**
 * User Guide & System Manual
 * Integrated Help Center & Visual Frontend-to-Backend Map
 * Modeled after VNCHIP Labs Control Center
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';

$adminTitle = 'User Guide & Documentation';
$adminBreadcrumb = 'Documentation / <span>User Guide</span>';
$adminActivePage = 'user-guide';

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* Custom Guide Styling */
.guide-hero {
  background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
  border-radius: 16px;
  padding: 32px 36px;
  color: #FFFFFF;
  margin-bottom: 24px;
  position: relative;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
}

.guide-hero::before {
  content: '';
  position: absolute;
  top: -50px;
  right: -50px;
  width: 260px;
  height: 260px;
  background: radial-gradient(circle, rgba(14, 165, 233, 0.2) 0%, transparent 70%);
  border-radius: 50%;
  pointer-events: none;
}

.guide-hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(14, 165, 233, 0.15);
  border: 1px solid rgba(14, 165, 233, 0.35);
  color: #38BDF8;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  padding: 6px 14px;
  border-radius: 9999px;
  margin-bottom: 14px;
}

.guide-hero h1 {
  font-size: 1.85rem;
  font-weight: 800;
  color: #FFFFFF;
  letter-spacing: -0.5px;
  margin-bottom: 10px;
}

.guide-hero p {
  color: #94A3B8;
  font-size: 0.95rem;
  max-width: 800px;
  line-height: 1.6;
  margin-bottom: 20px;
}

/* Quick Jump Pills Nav */
.guide-jump-nav {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.guide-jump-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #E2E8F0;
  padding: 6px 14px;
  border-radius: 9999px;
  font-size: 0.8rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s;
}

.guide-jump-pill:hover {
  background: var(--admin-primary);
  color: #FFFFFF;
  border-color: var(--admin-primary);
  transform: translateY(-1px);
}

/* Interactive Map Card */
.guide-section-card {
  background: #FFFFFF;
  border: 1px solid var(--admin-border);
  border-radius: 14px;
  padding: 24px 28px;
  margin-bottom: 28px;
  box-shadow: var(--admin-shadow-sm);
  transition: box-shadow 0.2s;
}

.guide-section-card:hover {
  box-shadow: var(--admin-shadow);
}

.guide-section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--admin-border);
}

.guide-section-title {
  display: flex;
  align-items: center;
  gap: 12px;
}

.guide-section-title .step-number {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: linear-gradient(135deg, #0284C7 0%, #2563EB 100%);
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 1.1rem;
}

.guide-section-title h2 {
  font-size: 1.25rem;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
}

.guide-section-title p {
  font-size: 0.85rem;
  color: #64748B;
  margin: 2px 0 0;
}

/* Visual Comparison Split */
.visual-split {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

@media (max-width: 991px) {
  .visual-split {
    grid-template-columns: 1fr;
  }
}

.visual-pane {
  background: #F8FAFC;
  border: 1px solid var(--admin-border);
  border-radius: 12px;
  overflow: hidden;
  transition: border-color 0.2s;
}

.visual-pane:hover {
  border-color: #CBD5E1;
}

.visual-pane-header {
  padding: 10px 16px;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--admin-border);
}

.visual-pane-frontend .visual-pane-header {
  background: #EFF6FF;
  color: #1D4ED8;
}

.visual-pane-backend .visual-pane-header {
  background: #F0FDF4;
  color: #15803D;
}

.visual-pane-img {
  position: relative;
  background: #0F172A;
  cursor: pointer;
  overflow: hidden;
}

.visual-pane-img img {
  width: 100%;
  height: auto;
  display: block;
  transition: transform 0.3s;
}

.visual-pane-img:hover img {
  transform: scale(1.02);
}

.visual-pane-img .zoom-badge {
  position: absolute;
  bottom: 10px;
  right: 10px;
  background: rgba(15, 23, 42, 0.8);
  color: #FFFFFF;
  font-size: 0.72rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 6px;
  backdrop-filter: blur(4px);
  pointer-events: none;
}

.visual-pane-footer {
  padding: 10px 16px;
  font-size: 0.82rem;
  color: #475569;
  background: #FFFFFF;
  border-top: 1px solid var(--admin-border);
}

/* Steps list */
.step-checklist {
  list-style: none;
  padding: 0;
  margin: 0 0 20px 0;
}

.step-checklist li {
  position: relative;
  padding-left: 36px;
  margin-bottom: 14px;
  font-size: 0.9rem;
  color: #334155;
  line-height: 1.6;
}

.step-checklist li .step-bullet {
  position: absolute;
  left: 0;
  top: 2px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #E0F2FE;
  color: #0284C7;
  font-weight: 700;
  font-size: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.step-checklist li strong {
  color: #0F172A;
}

/* Map Table styling */
.map-table th {
  background: #F8FAFC;
  font-weight: 700;
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #475569;
  border-bottom: 2px solid var(--admin-border);
}

.map-table td {
  font-size: 0.86rem;
  vertical-align: middle;
  padding: 14px 16px;
}

.map-search-box {
  position: relative;
  max-width: 320px;
}

.map-search-box input {
  padding-left: 36px;
  border-radius: 9999px;
  font-size: 0.85rem;
}

.map-search-box i {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #94A3B8;
  font-size: 0.85rem;
}

/* Alert Boxes */
.guide-alert {
  border-radius: 10px;
  padding: 14px 18px;
  margin-bottom: 18px;
  font-size: 0.88rem;
  display: flex;
  gap: 12px;
  align-items: flex-start;
}

.guide-alert-info {
  background: #F0F9FF;
  border: 1px solid #BAE6FD;
  color: #0369A1;
}

.guide-alert-warning {
  background: #FFFBEB;
  border: 1px solid #FDE68A;
  color: #B45309;
}

.guide-alert-success {
  background: #F0FDF4;
  border: 1px solid #BBF7D0;
  color: #15803D;
}

.guide-alert i {
  font-size: 1.1rem;
  margin-top: 2px;
}

/* Image Modal */
#imageZoomModal .modal-body {
  padding: 0;
  background: #0F172A;
}

#imageZoomModal img {
  width: 100%;
  height: auto;
  display: block;
}

@media print {
  .admin-sidebar, .admin-topbar, .guide-jump-nav, .btn, .btn-action-jump {
    display: none !important;
  }
  .admin-content {
    padding: 0 !important;
  }
  .guide-section-card {
    border: none;
    box-shadow: none;
    page-break-inside: avoid;
  }
}
</style>

<!-- 1. Hero Header -->
<div class="guide-hero">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="guide-hero-badge">
        <i class="fa-solid fa-graduation-cap"></i> VNCHIPS CMS MANUAL &middot; PHIÊN BẢN 2.0 PRO
      </div>
      <h1>Cẩm nang Hướng dẫn Sử dụng & Quản trị Website</h1>
      <p>
        Tài liệu được biên soạn trực quan, chi tiết từng bước cho người mới bắt đầu. Giúp bạn nắm rõ bản đồ đối chiếu giữa vị trí trên website ngoài (Frontend) và các màn hình quản trị bên trong (Backend) để cập nhật nội dung chuẩn xác và nhanh nhất.
      </p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <a href="<?= url() ?>" target="_blank" class="btn btn-outline-light btn-sm px-3 py-2" style="border-radius: 8px; font-weight: 700;">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Xem Web ngoài
      </a>
      <button onclick="window.print()" class="btn btn-primary btn-sm px-3 py-2" style="border-radius: 8px; font-weight: 700;">
        <i class="fa-solid fa-print me-1"></i> In tài liệu / Lưu PDF
      </button>
    </div>
  </div>

  <!-- Quick Jump Nav -->
  <div class="guide-jump-nav">
    <a href="#live-edit" class="guide-jump-pill" style="background: rgba(245, 158, 11, 0.22); border-color: rgba(245, 158, 11, 0.55); color: #FDE68A;"><i class="fa-solid fa-bolt"></i> ⚡ Live Edit Trực tiếp</a>
    <a href="#map" class="guide-jump-pill"><i class="fa-solid fa-map-location-dot"></i> 1. Bản đồ đối chiếu nhanh</a>
    <a href="#step1" class="guide-jump-pill"><i class="fa-solid fa-right-to-bracket"></i> 2. Đăng nhập & Dashboard</a>
    <a href="#step2" class="guide-jump-pill"><i class="fa-solid fa-image"></i> 3. Hero Banners & Video 4K</a>
    <a href="#step3" class="guide-jump-pill"><i class="fa-solid fa-sliders"></i> 4. Chỉ số Trang chủ</a>
    <a href="#step4" class="guide-jump-pill"><i class="fa-solid fa-newspaper"></i> 5. Bài viết & Tin tức</a>
    <a href="#step5" class="guide-jump-pill"><i class="fa-solid fa-user-tie"></i> 6. Ban Lãnh đạo</a>
    <a href="#step6" class="guide-jump-pill"><i class="fa-solid fa-handshake"></i> 7. Đối tác & Case Study</a>
    <a href="#step7" class="guide-jump-pill"><i class="fa-solid fa-envelope-open-text"></i> 8. Yêu cầu Khách hàng</a>
    <a href="#step8" class="guide-jump-pill"><i class="fa-solid fa-cubes"></i> 9. Khối Trang tĩnh (Builder)</a>
    <a href="#step9" class="guide-jump-pill"><i class="fa-solid fa-photo-film"></i> 10. Thư viện Media WebP</a>
    <a href="#step10" class="guide-jump-pill"><i class="fa-solid fa-gear"></i> 11. Cấu hình SEO & Thương hiệu</a>
    <a href="#specs" class="guide-jump-pill"><i class="fa-solid fa-ruler-combined"></i> 12. Quy chuẩn kích thước ảnh</a>
    <a href="#faq" class="guide-jump-pill"><i class="fa-solid fa-circle-question"></i> 13. Hỏi đáp thường gặp (FAQ)</a>
  </div>
</div>

<!-- 2. Interactive Map Section -->
<div class="guide-section-card" id="map">
  <div class="guide-section-header">
    <div class="guide-section-title">
      <div class="step-number" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);">
        <i class="fa-solid fa-map-location-dot"></i>
      </div>
      <div>
        <h2>Bản đồ Đối chiếu Nhanh (Frontend ⟷ Backend Map)</h2>
        <p>Gõ từ khóa vào ô tìm kiếm để tra cứu ngay: "Bạn thấy gì ngoài web ➡️ Vào đâu trong Admin để sửa". Bấm nút để chuyển thẳng đến màn hình đó.</p>
      </div>
    </div>
    <div class="map-search-box">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" id="mapSearchInput" class="form-control" placeholder="Tìm kiếm nhanh vị trí...">
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle map-table" id="mapTable">
      <thead>
        <tr>
          <th style="width: 50px;" class="text-center">STT</th>
          <th style="width: 25%;">Vị trí hiển thị ngoài Website (Frontend)</th>
          <th style="width: 20%;">Xem trang ngoài</th>
          <th style="width: 25%;">Module Quản trị bên trong (Backend)</th>
          <th style="width: 15%;" class="text-center">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="text-center font-monospace text-muted">01</td>
          <td>
            <strong>Video 4K Nền & Tiêu đề mở đầu Trang chủ</strong>
            <div class="text-muted small">Đoạn video bán dẫn 4K vòng lặp và câu slogan lớn</div>
          </td>
          <td>
            <a href="<?= url() ?>" target="_blank" class="text-decoration-none">
              <i class="fa-solid fa-link me-1"></i> Trang chủ (index.php)
            </a>
          </td>
          <td>
            <span class="badge bg-primary bg-opacity-10 text-primary py-1 px-2 mb-1"><i class="fa-solid fa-image me-1"></i> Hero Banners</span>
            <div class="small text-muted">Thay video MP4, ảnh nền lớn, tiêu đề chính & phụ</div>
          </td>
          <td class="text-center">
            <a href="<?= admin_url('banners.php') ?>" class="btn btn-sm btn-outline-primary w-100" style="border-radius: 6px; font-weight: 700;">
              Chỉnh sửa &rarr;
            </a>
          </td>
        </tr>
        <tr>
          <td class="text-center font-monospace text-muted">02</td>
          <td>
            <strong>Thanh Pill Banner & 3 Chỉ số vàng Trang chủ</strong>
            <div class="text-muted small">15+ Năm kinh nghiệm, 30+ ASICs, 50+ Kỹ sư thiết kế</div>
          </td>
          <td>
            <a href="<?= url() ?>" target="_blank" class="text-decoration-none">
              <i class="fa-solid fa-link me-1"></i> Dưới Video Trang chủ
            </a>
          </td>
          <td>
            <span class="badge bg-secondary bg-opacity-10 text-dark py-1 px-2 mb-1"><i class="fa-solid fa-sliders me-1"></i> Cài đặt hệ thống</span>
            <div class="small text-muted">Sửa số liệu thống kê, nhãn mô tả, thanh chạy chữ nổi</div>
          </td>
          <td class="text-center">
            <a href="<?= admin_url('settings.php') ?>" class="btn btn-sm btn-outline-secondary w-100" style="border-radius: 6px; font-weight: 700;">
              Chỉnh sửa &rarr;
            </a>
          </td>
        </tr>
        <tr>
          <td class="text-center font-monospace text-muted">03</td>
          <td>
            <strong>Danh sách Bài viết, Tin tức & Sự kiện</strong>
            <div class="text-muted small">Tin tức báo chí, bài phân tích kỹ thuật, cập nhật công nghệ</div>
          </td>
          <td>
            <a href="<?= url('media-center.php') ?>" target="_blank" class="text-decoration-none">
              <i class="fa-solid fa-link me-1"></i> /media-center.php
            </a>
          </td>
          <td>
            <span class="badge bg-success bg-opacity-10 text-success py-1 px-2 mb-1"><i class="fa-solid fa-newspaper me-1"></i> Bài viết & Tin tức</span>
            <div class="small text-muted">Thêm mới, kéo thả sắp xếp, ghim bài, soạn thảo WYSIWYG/HTML</div>
          </td>
          <td class="text-center">
            <a href="<?= admin_url('articles.php') ?>" class="btn btn-sm btn-outline-success w-100" style="border-radius: 6px; font-weight: 700;">
              Chỉnh sửa &rarr;
            </a>
          </td>
        </tr>
        <tr>
          <td class="text-center font-monospace text-muted">04</td>
          <td>
            <strong>Đội ngũ Ban Lãnh đạo & Chuyên gia</strong>
            <div class="text-muted small">Ảnh chân dung, họ tên, chức danh và tiểu sử chi tiết</div>
          </td>
          <td>
            <a href="<?= url('about-us.php#leadership') ?>" target="_blank" class="text-decoration-none">
              <i class="fa-solid fa-link me-1"></i> /about-us.php#leadership
            </a>
          </td>
          <td>
            <span class="badge bg-info bg-opacity-10 text-info py-1 px-2 mb-1"><i class="fa-solid fa-user-tie me-1"></i> Đội ngũ Lãnh đạo</span>
            <div class="small text-muted">Quản lý thành viên, ảnh chân dung, chức vụ, LinkedIn</div>
          </td>
          <td class="text-center">
            <a href="<?= admin_url('members.php') ?>" class="btn btn-sm btn-outline-info w-100" style="border-radius: 6px; font-weight: 700;">
              Chỉnh sửa &rarr;
            </a>
          </td>
        </tr>
        <tr>
          <td class="text-center font-monospace text-muted">05</td>
          <td>
            <strong>Logo Đối tác & Tài liệu Case Study</strong>
            <div class="text-muted small">Mạng lưới đối tác công nghệ và tệp PDF giới thiệu giải pháp</div>
          </td>
          <td>
            <a href="<?= url('partners.php') ?>" target="_blank" class="text-decoration-none">
              <i class="fa-solid fa-link me-1"></i> /partners.php
            </a>
          </td>
          <td>
            <span class="badge bg-warning bg-opacity-10 text-dark py-1 px-2 mb-1"><i class="fa-solid fa-handshake me-1"></i> Đối tác & Case Study</span>
            <div class="small text-muted">Thêm logo doanh nghiệp, gắn tệp PDF Case Study tải về</div>
          </td>
          <td class="text-center">
            <a href="<?= admin_url('partners.php') ?>" class="btn btn-sm btn-outline-warning w-100" style="border-radius: 6px; font-weight: 700;">
              Chỉnh sửa &rarr;
            </a>
          </td>
        </tr>
        <tr>
          <td class="text-center font-monospace text-muted">06</td>
          <td>
            <strong>Yêu cầu Tư vấn & Báo giá của Khách hàng</strong>
            <div class="text-muted small">Thông tin khách gửi từ biểu mẫu liên hệ trang Talk to Us</div>
          </td>
          <td>
            <a href="<?= url('talk-to-us.php') ?>" target="_blank" class="text-decoration-none">
              <i class="fa-solid fa-link me-1"></i> /talk-to-us.php
            </a>
          </td>
          <td>
            <span class="badge bg-danger bg-opacity-10 text-danger py-1 px-2 mb-1"><i class="fa-solid fa-envelope-open-text me-1"></i> Liên hệ & Yêu cầu</span>
            <div class="small text-muted">Xem tin nhắn chi tiết (Modal), cập nhật tiến độ, xuất Excel UTF-8</div>
          </td>
          <td class="text-center">
            <a href="<?= admin_url('inquiries.php') ?>" class="btn btn-sm btn-outline-danger w-100" style="border-radius: 6px; font-weight: 700;">
              Xem hộp thư &rarr;
            </a>
          </td>
        </tr>
        <tr>
          <td class="text-center font-monospace text-muted">07</td>
          <td>
            <strong>Khối nội dung Công nghệ, Giới thiệu & Kinh doanh</strong>
            <div class="text-muted small">Sơ đồ khối, quy trình thiết kế chiplet, cam kết phát triển</div>
          </td>
          <td>
            <a href="<?= url('technology.php') ?>" target="_blank" class="text-decoration-none">
              <i class="fa-solid fa-link me-1"></i> /technology.php & /about-us.php
            </a>
          </td>
          <td>
            <span class="badge bg-dark bg-opacity-10 text-dark py-1 px-2 mb-1"><i class="fa-solid fa-cubes me-1"></i> Khối trang tĩnh</span>
            <div class="small text-muted">Trình dựng khối nội dung (Site Builder) linh hoạt cho 4 trang con</div>
          </td>
          <td class="text-center">
            <a href="<?= admin_url('page-sections.php') ?>" class="btn btn-sm btn-outline-dark w-100" style="border-radius: 6px; font-weight: 700;">
              Chỉnh sửa &rarr;
            </a>
          </td>
        </tr>
        <tr>
          <td class="text-center font-monospace text-muted">08</td>
          <td>
            <strong>Banner đầu trang (Header Banner) các trang con</strong>
            <div class="text-muted small">Ảnh nền lớn phía trên của About Us, Technology, Business...</div>
          </td>
          <td>
            <a href="<?= url('about-us.php') ?>" target="_blank" class="text-decoration-none">
              <i class="fa-solid fa-link me-1"></i> Đầu các trang con
            </a>
          </td>
          <td>
            <span class="badge bg-primary bg-opacity-10 text-primary py-1 px-2 mb-1"><i class="fa-solid fa-image me-1"></i> Hero Banners</span>
            <div class="small text-muted">Thay đổi ảnh nền đại diện và câu khẩu hiệu cho từng trang</div>
          </td>
          <td class="text-center">
            <a href="<?= admin_url('banners.php') ?>" class="btn btn-sm btn-outline-primary w-100" style="border-radius: 6px; font-weight: 700;">
              Chỉnh sửa &rarr;
            </a>
          </td>
        </tr>
        <tr>
          <td class="text-center font-monospace text-muted">09</td>
          <td>
            <strong>Menu Điều hướng trên cùng (Navbar & Mega-Menu)</strong>
            <div class="text-muted small">Các liên kết chính ở thanh đầu trang và menu con mở rộng</div>
          </td>
          <td>
            <a href="<?= url() ?>" target="_blank" class="text-decoration-none">
              <i class="fa-solid fa-link me-1"></i> Thanh Menu đầu trang
            </a>
          </td>
          <td>
            <span class="badge bg-secondary bg-opacity-10 text-dark py-1 px-2 mb-1"><i class="fa-solid fa-compass me-1"></i> Quản lý Menu</span>
            <div class="small text-muted">Thêm, sửa, xóa liên kết và phân cấp danh mục menu</div>
          </td>
          <td class="text-center">
            <a href="<?= admin_url('menus.php') ?>" class="btn btn-sm btn-outline-secondary w-100" style="border-radius: 6px; font-weight: 700;">
              Chỉnh sửa &rarr;
            </a>
          </td>
        </tr>
        <tr>
          <td class="text-center font-monospace text-muted">10</td>
          <td>
            <strong>Logo, Hotline, Email Chân trang & Google Analytics</strong>
            <div class="text-muted small">Thông tin nhận diện thương hiệu, thẻ SEO và mã theo dõi truy cập</div>
          </td>
          <td>
            <a href="<?= url() ?>" target="_blank" class="text-decoration-none">
              <i class="fa-solid fa-link me-1"></i> Chân trang & Toàn web
            </a>
          </td>
          <td>
            <span class="badge bg-dark bg-opacity-10 text-dark py-1 px-2 mb-1"><i class="fa-solid fa-gear me-1"></i> Cấu hình thương hiệu</span>
            <div class="small text-muted">Tải logo, favicon, dán mã Google Analytics (GA4), cấu hình SEO</div>
          </td>
          <td class="text-center">
            <a href="<?= admin_url('site-settings.php') ?>" class="btn btn-sm btn-outline-dark w-100" style="border-radius: 6px; font-weight: 700;">
              Chỉnh sửa &rarr;
            </a>
          </td>
        </tr>
        <tr style="background: rgba(245, 158, 11, 0.05);">
          <td class="text-center font-monospace fw-bold" style="color: #D97706;">11</td>
          <td>
            <strong>Chỉnh sửa Trực tiếp trên Giao diện ngoài (Live Edit Mode)</strong>
            <div class="text-muted small">Sửa ngay tại chỗ Hero, Journey, Timeline, Vision, Leadership, Code of Conduct, Business, Sustainable, Technology, Contact, CTA &amp; Footer</div>
          </td>
          <td>
            <a href="<?= url() ?>" target="_blank" class="text-decoration-none fw-semibold">
              <i class="fa-solid fa-bolt text-warning me-1"></i> Tất cả các trang ngoài web
            </a>
          </td>
          <td>
            <span class="badge py-1 px-2 mb-1" style="background: rgba(245, 158, 11, 0.16); color: #B45309;"><i class="fa-solid fa-pen-to-square me-1"></i> VNCHIP Admin Bar (EDIT MODE: ON)</span>
            <div class="small text-muted">Rê chuột hiện viền Neon vàng cam &rarr; bấm nút "Sửa ..." để mở Popup chỉnh sửa &amp; Ace HTML</div>
          </td>
          <td class="text-center">
            <a href="#live-edit" class="btn btn-sm btn-warning text-dark w-100" style="border-radius: 6px; font-weight: 700;">
              Xem hướng dẫn &darr;
            </a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- 2.5. Flagship Feature: Frontend Live Edit Engine -->
<div class="guide-section-card" id="live-edit" style="border-top: 4px solid #F59E0B;">
  <div class="guide-section-header">
    <div class="guide-section-title">
      <div class="step-number" style="background: linear-gradient(135deg, #F59E0B 0%, #EA580C 100%);">
        <i class="fa-solid fa-bolt"></i>
      </div>
      <div>
        <h2>Chỉnh sửa Trực tiếp trên Giao diện ngoài (Live Edit Mode)</h2>
        <p>Sửa nội dung, thay ảnh, thêm/xóa thẻ và biên tập mã HTML có màu ngay trên website thật mà không cần chuyển vào trang quản trị.</p>
      </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a href="<?= url('about-us.php#conduct') ?>" target="_blank" class="btn btn-sm btn-outline-primary fw-bold" style="border-radius: 8px;">
        <i class="fa-solid fa-scale-balanced me-1"></i> Thử trên About Us
      </a>
      <a href="<?= url() ?>" target="_blank" class="btn btn-sm btn-warning text-dark fw-bold" style="border-radius: 8px;">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở Web &amp; Bật Live Edit
      </a>
    </div>
  </div>

  <!-- Quick 4-Step Workflow -->
  <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-play-circle text-warning me-2"></i>Quy trình 4 bước sử dụng Live Edit cực nhanh</h5>
  <ul class="step-checklist">
    <li>
      <span class="step-bullet" style="background: #FEF3C7; color: #B45309;">1</span>
      <strong>Đăng nhập Quản trị &amp; mở website ngoài:</strong> Sau khi đăng nhập Admin (phiên đăng nhập được tự động duy trì <strong>30 ngày</strong>), mở bất kỳ trang nào trên website ngoài, bạn sẽ thấy thanh <strong>VNCHIP ADMIN</strong> màu xanh đen nằm cố định ở mép trên cùng màn hình (chỉ Quản trị viên nhìn thấy, khách truy cập hoàn toàn không thấy).
    </li>
    <li>
      <span class="step-bullet" style="background: #FEF3C7; color: #B45309;">2</span>
      <strong>Bật công tắc <code>EDIT MODE: ON</code>:</strong> Nhấp vào bất kỳ vị trí nào trên cụm nút gạt <strong>EDIT MODE</strong> ở chính giữa thanh VNCHIP Admin Bar. Công tắc sẽ chuyển sang màu xanh dương kèm nhãn <strong>ON</strong>. Trạng thái này được lưu tự động trên trình duyệt — khi bạn chuyển trang khác hoặc nhấn nút <em>Back</em> quay lại, Live Edit vẫn tiếp tục bật sẵn.
    </li>
    <li>
      <span class="step-bullet" style="background: #FEF3C7; color: #B45309;">3</span>
      <strong>Rê chuột vào khu vực cần sửa (Hiệu ứng viền Neon):</strong> Mọi khối hỗ trợ Live Edit sẽ có đường viền nét đứt màu xanh. Khi đưa chuột vào đúng phân khu muốn chỉnh sửa, <strong>viền khối sẽ lập tức phát sáng dạng đèn Neon màu vàng cam nổi bật</strong> (màu nền gốc của trang vẫn giữ nguyên 100% để bạn quan sát bố cục trung thực) kèm nút bấm <strong>"Sửa ..."</strong> hiển thị ở góc trên bên phải.
    </li>
    <li>
      <span class="step-bullet" style="background: #FEF3C7; color: #B45309;">4</span>
      <strong>Chỉnh sửa trực quan, Upload ảnh &amp; Lưu tức thì:</strong> Nhấp vào nút <strong>"Sửa ..."</strong> để mở cửa sổ Popup chỉnh sửa. Bạn có thể sửa văn bản, thêm/xóa/đổi thứ tự các thẻ (Timeline, Vision, Code of Conduct), <strong>tải ảnh trực tiếp từ máy tính để thay thế ngay lập tức</strong>, hoặc mở tab HTML với <strong>Ace Code Editor tô màu</strong>. Nhấn nút <strong>Lưu</strong> để cập nhật ngay lập tức lên website.
    </li>
  </ul>

  <!-- Coverage Table Across All Pages -->
  <h5 class="fw-bold text-dark mt-4 mb-3"><i class="fa-solid fa-table-cells-large text-primary me-2"></i>Danh mục đầy đủ các Trang &amp; Thành phần hỗ trợ Live Edit</h5>
  <div class="table-responsive mb-4">
    <table class="table table-bordered table-hover align-middle map-table mb-0">
      <thead>
        <tr>
          <th style="width: 22%;">Trang Website</th>
          <th style="width: 58%;">Các khối hỗ trợ Live Edit trực tiếp</th>
          <th style="width: 20%;" class="text-center">Mở nhanh</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>Trang Chủ</strong><div class="small text-muted">index.php</div></td>
          <td>
            <span class="badge bg-primary bg-opacity-10 text-primary me-1 mb-1">Hero Video &amp; Banner</span>
            <span class="badge bg-primary bg-opacity-10 text-primary me-1 mb-1">3 Chỉ số vàng (6+ years, 40+ ASIC, 5M+ chips)</span>
            <span class="badge bg-primary bg-opacity-10 text-primary me-1 mb-1">Thanh Pill Banner</span>
            <span class="badge bg-primary bg-opacity-10 text-primary me-1 mb-1">Khối Business</span>
            <span class="badge bg-primary bg-opacity-10 text-primary me-1 mb-1">Media Center</span>
            <span class="badge bg-primary bg-opacity-10 text-primary me-1 mb-1">Sustainable ESG</span>
          </td>
          <td class="text-center">
            <a href="<?= url() ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100 fw-bold" style="border-radius: 6px;">Mở Trang chủ &rarr;</a>
          </td>
        </tr>
        <tr>
          <td><strong>Giới thiệu (About Us)</strong><div class="small text-muted">about-us.php (100% toàn trang)</div></td>
          <td>
            <span class="badge bg-info bg-opacity-10 text-dark me-1 mb-1">Hero Banner (Upload ảnh &amp; Tiêu đề)</span>
            <span class="badge bg-info bg-opacity-10 text-dark me-1 mb-1">VNCHIP Journey &amp; Upload Ảnh Trụ sở (#overview)</span>
            <span class="badge bg-info bg-opacity-10 text-dark me-1 mb-1">Lịch sử &amp; Timeline (#journey — Thêm/Xóa/Sắp xếp mốc)</span>
            <span class="badge bg-info bg-opacity-10 text-dark me-1 mb-1">Vision, Mission &amp; Core Values (#vision — Chọn Icon SVG)</span>
            <span class="badge bg-info bg-opacity-10 text-dark me-1 mb-1">Leadership &amp; Thông điệp Founder (#leadership — Upload ảnh)</span>
            <span class="badge bg-info bg-opacity-10 text-dark me-1 mb-1">Ban Lãnh đạo (Executives) &amp; Key Team (Bật/Tắt)</span>
            <span class="badge bg-danger bg-opacity-10 text-danger me-1 mb-1">Code of Conduct (#conduct — Thêm/Xóa/Sửa 4 thẻ Quy tắc)</span>
          </td>
          <td class="text-center">
            <a href="<?= url('about-us.php#conduct') ?>" target="_blank" class="btn btn-sm btn-outline-danger w-100 fw-bold" style="border-radius: 6px;">Mở About Us &rarr;</a>
          </td>
        </tr>
        <tr>
          <td><strong>Giải pháp &amp; Kinh doanh</strong><div class="small text-muted">business.php</div></td>
          <td>
            <span class="badge bg-primary bg-opacity-10 text-primary me-1 mb-1">3 Trụ cột Công nghệ (#technology: Chiplet, Interconnect, AI SoC)</span>
            <span class="badge bg-primary bg-opacity-10 text-primary me-1 mb-1">Dịch vụ Thiết kế &amp; IP Matrix (#design-service)</span>
            <span class="badge bg-primary bg-opacity-10 text-primary me-1 mb-1">Mô hình Hợp tác &amp; Dự án</span>
          </td>
          <td class="text-center">
            <a href="<?= url('business.php#technology') ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100 fw-bold" style="border-radius: 6px;">Mở Business &rarr;</a>
          </td>
        </tr>
        <tr>
          <td><strong>Công nghệ (Technology)</strong><div class="small text-muted">technology.php</div></td>
          <td>
            <span class="badge bg-warning bg-opacity-10 text-dark me-1 mb-1">Chiplet Disaggregation (#chiplet-disaggregation)</span>
            <span class="badge bg-warning bg-opacity-10 text-dark me-1 mb-1">Interconnect Standards (#interconnect-standards)</span>
          </td>
          <td class="text-center">
            <a href="<?= url('technology.php') ?>" target="_blank" class="btn btn-sm btn-outline-warning text-dark w-100 fw-bold" style="border-radius: 6px;">Mở Technology &rarr;</a>
          </td>
        </tr>
        <tr>
          <td><strong>Phát triển Bền vững</strong><div class="small text-muted">sustainable.php</div></td>
          <td>
            <span class="badge bg-success bg-opacity-10 text-success me-1 mb-1">Trách nhiệm Xã hội ESG (#social-responsibilities)</span>
            <span class="badge bg-success bg-opacity-10 text-success me-1 mb-1">Phòng sạch Clean Room (#clean-room)</span>
            <span class="badge bg-success bg-opacity-10 text-success me-1 mb-1">Đào tạo &amp; Programs (#talents-development)</span>
            <span class="badge bg-success bg-opacity-10 text-success me-1 mb-1">Cam kết Bảo mật &amp; Đối tác (#confidentiality)</span>
          </td>
          <td class="text-center">
            <a href="<?= url('sustainable.php') ?>" target="_blank" class="btn btn-sm btn-outline-success w-100 fw-bold" style="border-radius: 6px;">Mở Sustainable &rarr;</a>
          </td>
        </tr>
        <tr>
          <td><strong>Liên hệ (Talk To Us)</strong><div class="small text-muted">talk-to-us.php</div></td>
          <td>
            <span class="badge bg-warning bg-opacity-10 text-dark me-1 mb-1">Tiêu đề &amp; Giới thiệu trang Liên hệ</span>
            <span class="badge bg-warning bg-opacity-10 text-dark me-1 mb-1">Địa chỉ Trụ sở Việt Nam &amp; Hoa Kỳ (USA)</span>
            <span class="badge bg-warning bg-opacity-10 text-dark me-1 mb-1">Hotline, Email &amp; lối tắt mở Hộp thư Khách hàng</span>
          </td>
          <td class="text-center">
            <a href="<?= url('talk-to-us.php') ?>" target="_blank" class="btn btn-sm btn-outline-secondary w-100 fw-bold" style="border-radius: 6px;">Mở Talk To Us &rarr;</a>
          </td>
        </tr>
        <tr>
          <td><strong>Khối Dùng chung Toàn web</strong><div class="small text-muted">Hiển thị trên mọi trang</div></td>
          <td>
            <span class="badge bg-dark bg-opacity-10 text-dark me-1 mb-1">Call to Action Banner (Tiêu đề, Nút bấm, Upload ảnh nền)</span>
            <span class="badge bg-dark bg-opacity-10 text-dark me-1 mb-1">Footer 3 Cột Menu (Ace HTML)</span>
            <span class="badge bg-dark bg-opacity-10 text-dark me-1 mb-1">Footer Logo chính &amp; 2 Logo Đối tác (Upload &amp; Thay ảnh trực tiếp)</span>
            <span class="badge bg-dark bg-opacity-10 text-dark me-1 mb-1">Copyright, Địa chỉ, Hotline, Email &amp; 3 Mạng xã hội</span>
          </td>
          <td class="text-center">
            <a href="<?= url() ?>#siteFooter" target="_blank" class="btn btn-sm btn-outline-dark w-100 fw-bold" style="border-radius: 6px;">Xem Footer &rarr;</a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Feature Cards: Ace Code Editor & Instant Image Upload -->
  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <div class="p-3 rounded-3 h-100" style="background: #0F172A; color: #E2E8F0; border: 1px solid #1E293B;">
        <div class="fw-bold mb-2" style="color: #38BDF8; font-size: 0.95rem;">
          <i class="fa-brands fa-html5 text-warning me-1"></i> Trình soạn thảo Mã HTML có màu (Ace Code Editor)
        </div>
        <p class="small mb-2" style="color: #CBD5E1; line-height: 1.6;">
          Tất cả các ô nhập mã HTML trong Live Edit (cũng như trong <strong>Page Sections</strong> &amp; <strong>Site Settings</strong>) đều sử dụng <strong>Ace Code Editor</strong> tự động tô màu cú pháp giúp dễ đọc và tránh lỗi thiếu thẻ đóng:
        </p>
        <ul class="small mb-0 ps-3" style="color: #94A3B8; line-height: 1.7;">
          <li><strong class="text-white">Sáng / Tối:</strong> Đổi nhanh giữa giao diện nền tối <em>Tomorrow Night</em> và nền sáng <em>Chrome</em>.</li>
          <li><strong class="text-white">Thu phóng:</strong> Mở rộng chiều cao khung soạn thảo khi cần chỉnh đoạn mã dài.</li>
          <li><strong class="text-white">Phím tắt tiện lợi:</strong> Nhấn <code>Ctrl + F</code> để tìm/thay thế nhanh, nhấn <code>Ctrl + S</code> để lưu ngay.</li>
          <li><strong class="text-white">Khôi phục mẫu chuẩn:</strong> Nếu từng nhập HTML nâng cao ở Tab 2 và muốn quay về form nhập trực quan Tab 1, chỉ cần bấm nút <em>"Xóa HTML tùy chỉnh"</em> rồi bấm Lưu.</li>
        </ul>
      </div>
    </div>
    <div class="col-md-6">
      <div class="p-3 rounded-3 h-100" style="background: #F8FAFC; border: 1px solid #CBD5E1;">
        <div class="fw-bold mb-2 text-primary" style="font-size: 0.95rem;">
          <i class="fa-solid fa-cloud-arrow-up me-1"></i> Tải &amp; Thay thế Ảnh Trực tiếp (Instant Upload)
        </div>
        <p class="small text-secondary mb-2" style="line-height: 1.6;">
          Ở mọi khu vực có hình ảnh (Hero About Us, Ảnh sảnh Trụ sở, Chân dung Founder, Nền CTA Banner, Logo Footer &amp; Logo đối tác), bạn không cần sao chép đường dẫn thủ công:
        </p>
        <ul class="small text-secondary mb-0 ps-3" style="line-height: 1.7;">
          <li>Bấm nút <strong>"Tải ảnh mới lên (Upload &amp; Thay thế)"</strong> và chọn tệp ảnh từ máy tính (<code>JPG</code>, <code>PNG</code>, <code>WebP</code>, <code>SVG</code>).</li>
          <li>Hệ thống tự động tải lên máy chủ, lưu cấu hình và <strong>thay đổi hình ảnh trực tiếp ngoài trang web ngay lập tức</strong> cùng ô xem trước (Preview).</li>
          <li>Sau khi hoàn tất chỉnh sửa, hãy gạt công tắc <strong>EDIT MODE</strong> về <strong>OFF</strong> để xem lại giao diện chuẩn như khách truy cập thông thường.</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="guide-alert guide-alert-info mb-0">
    <i class="fa-solid fa-circle-info"></i>
    <div>
      <strong>Tự động chống Cache cho Admin:</strong> Khi đang đăng nhập Quản trị viên, hệ thống tự động tắt bộ nhớ đệm (<code>no-cache</code>) cho riêng phiên của bạn, giúp mọi thay đổi từ Live Edit hiển thị mới nhất ngay lập tức mà không bị lỗi ảnh/chữ cũ.
    </div>
  </div>
</div>

<!-- 3. Step 1: Login & Dashboard -->
<div class="guide-section-card" id="step1">
  <div class="guide-section-header">
    <div class="guide-section-title">
      <div class="step-number">01</div>
      <div>
        <h2>Đăng nhập Hệ thống & Bảng điều khiển Tổng quan (Dashboard)</h2>
        <p>Cách truy cập an toàn và làm quen với trung tâm điều hành VNCHIPS Control Center.</p>
      </div>
    </div>
    <a href="<?= admin_url('index.php') ?>" class="btn btn-sm btn-primary" style="border-radius: 6px; font-weight: 700;">
      <i class="fa-solid fa-gauge-high me-1"></i> Đến Dashboard
    </a>
  </div>

  <div class="visual-split">
    <div class="visual-pane visual-pane-frontend">
      <div class="visual-pane-header">
        <span><i class="fa-solid fa-shield-halved me-1"></i> Màn hình Đăng nhập Quản trị</span>
        <span class="badge bg-primary">chip-admin/login.php</span>
      </div>
      <div class="visual-pane-img" onclick="openZoomModal('<?= admin_url('assets/guide/admin_login.png') ?>', 'Màn hình Đăng nhập Quản trị')">
        <img src="<?= admin_url('assets/guide/admin_login.png') ?>" alt="Admin Login">
        <div class="zoom-badge"><i class="fa-solid fa-magnifying-glass-plus"></i> Nhấp để phóng to</div>
      </div>
      <div class="visual-pane-footer">
        Đăng nhập với tài khoản: <code>admin</code> / Mật khẩu: <code>password</code>
      </div>
    </div>

    <div class="visual-pane visual-pane-backend">
      <div class="visual-pane-header">
        <span><i class="fa-solid fa-chart-pie me-1"></i> Bảng điều khiển Tổng quan</span>
        <span class="badge bg-success">chip-admin/index.php</span>
      </div>
      <div class="visual-pane-img" onclick="openZoomModal('<?= admin_url('assets/guide/admin_dashboard.png') ?>', 'Bảng điều khiển Tổng quan Dashboard')">
        <img src="<?= admin_url('assets/guide/admin_dashboard.png') ?>" alt="Admin Dashboard">
        <div class="zoom-badge"><i class="fa-solid fa-magnifying-glass-plus"></i> Nhấp để phóng to</div>
      </div>
      <div class="visual-pane-footer">
        Hiển thị 4 thẻ KPI thống kê, khách hàng mới và nút tạo bài viết nhanh.
      </div>
    </div>
  </div>

  <ul class="step-checklist">
    <li>
      <span class="step-bullet">1</span>
      <strong>Đăng nhập:</strong> Truy cập <code><?= admin_url() ?></code>. Nhập tên đăng nhập <code>admin</code> và mật khẩu <code>password</code>. Tích chọn <em>"Ghi nhớ đăng nhập"</em> để không phải nhập lại lần sau.
    </li>
    <li>
      <span class="step-bullet">2</span>
      <strong>Nút "Xem Website" (View Website):</strong> Nằm ở góc trên bên phải thanh Topbar. Bạn có thể bấm vào đây bất cứ lúc nào để mở website ngoài trong tab mới và kiểm tra nội dung vừa chỉnh sửa.
    </li>
    <li>
      <span class="step-bullet">3</span>
      <strong>Đổi mật khẩu định kỳ:</strong> Bấm vào tên tài khoản của bạn ở góc trên bên phải &rarr; chọn <strong>Admin Profile</strong> &rarr; nhập mật khẩu mới để bảo mật tuyệt đối cho hệ thống.
    </li>
  </ul>
</div>

<!-- 4. Step 2: Hero Banners & 4K Video -->
<div class="guide-section-card" id="step2">
  <div class="guide-section-header">
    <div class="guide-section-title">
      <div class="step-number">02</div>
      <div>
        <h2>Quản lý Banner Mở đầu & Video Nền 4K (Hero Banners)</h2>
        <p>Cập nhật video công nghệ bán dẫn chuyển động và khẩu hiệu thương hiệu trên toàn trang.</p>
      </div>
    </div>
    <a href="<?= admin_url('banners.php') ?>" class="btn btn-sm btn-primary" style="border-radius: 6px; font-weight: 700;">
      <i class="fa-solid fa-image me-1"></i> Đến Hero Banners
    </a>
  </div>

  <div class="visual-split">
    <div class="visual-pane visual-pane-frontend">
      <div class="visual-pane-header">
        <span><i class="fa-solid fa-globe me-1"></i> Giao diện Ngoài Trang chủ (Frontend)</span>
        <span class="badge bg-primary">vnchips.trantam.net</span>
      </div>
      <div class="visual-pane-img" onclick="openZoomModal('<?= admin_url('assets/guide/frontend_homepage.png') ?>', 'Video 4K Nền Trang chủ Frontend')">
        <img src="<?= admin_url('assets/guide/frontend_homepage.png') ?>" alt="Frontend Homepage">
        <div class="zoom-badge"><i class="fa-solid fa-magnifying-glass-plus"></i> Nhấp để phóng to</div>
      </div>
      <div class="visual-pane-footer">
        Đoạn video vi mạch bán dẫn 4K vòng lặp và thông điệp mở đầu.
      </div>
    </div>

    <div class="visual-pane visual-pane-backend">
      <div class="visual-pane-header">
        <span><i class="fa-solid fa-sliders me-1"></i> Màn hình Quản trị Hero Banners</span>
        <span class="badge bg-success">chip-admin/banners.php</span>
      </div>
      <div class="visual-pane-img" onclick="openZoomModal('<?= admin_url('assets/guide/admin_hero_banners.png') ?>', 'Quản trị Hero Banners trong Admin')">
        <img src="<?= admin_url('assets/guide/admin_hero_banners.png') ?>" alt="Admin Hero Banners">
        <div class="zoom-badge"><i class="fa-solid fa-magnifying-glass-plus"></i> Nhấp để phóng to</div>
      </div>
      <div class="visual-pane-footer">
        Quản lý video/ảnh nền cho 6 trang: Trang chủ, About Us, Technology, Business...
      </div>
    </div>
  </div>

  <ul class="step-checklist">
    <li>
      <span class="step-bullet">1</span>
      Vào menu <strong>Hero Banners</strong> ở cột trái.
    </li>
    <li>
      <span class="step-bullet">2</span>
      Tại mục <strong>Homepage Hero Video</strong>: Bạn có thể tải lên tệp video mới (định dạng <code>.mp4</code> hoặc <code>.webm</code>, dung lượng dưới 10MB để tải nhanh) hoặc dán đường dẫn video.
    </li>
    <li>
      <span class="step-bullet">3</span>
      Tại các mục <strong>About Us, Technology, Business Hero</strong>: Bấm nút <em>"Chọn ảnh mới"</em> để cập nhật ảnh nền kích thước lớn (1920x800 px).
    </li>
    <li>
      <span class="step-bullet">4</span>
      Chỉnh sửa <strong>Headline (Tiêu đề lớn)</strong> và <strong>Subheadline (Phụ đề)</strong> rồi bấm <strong>"Cập nhật Banner"</strong>.
    </li>
  </ul>
</div>

<!-- 5. Step 3: Homepage Stats & Pill Banner -->
<div class="guide-section-card" id="step3">
  <div class="guide-section-header">
    <div class="guide-section-title">
      <div class="step-number">03</div>
      <div>
        <h2>Cập nhật 3 Chỉ số Vàng & Thanh Chạy Chữ (Pill Banner)</h2>
        <p>15+ Năm kinh nghiệm, 30+ Dự án ASICs thành công, 50+ Kỹ sư thiết kế vi mạch.</p>
      </div>
    </div>
    <a href="<?= admin_url('settings.php') ?>" class="btn btn-sm btn-primary" style="border-radius: 6px; font-weight: 700;">
      <i class="fa-solid fa-sliders me-1"></i> Đến Cài đặt Trang chủ
    </a>
  </div>

  <div class="guide-alert guide-alert-info">
    <i class="fa-solid fa-circle-info"></i>
    <div>
      <strong>Vị trí hiển thị:</strong> Khối 3 thẻ chỉ số vàng và thanh Pill Banner chạy chữ nằm ngay bên dưới phần Video Trang chủ ngoài website.
    </div>
  </div>

  <ul class="step-checklist">
    <li>
      <span class="step-bullet">1</span>
      Vào menu <strong>Cài đặt hệ thống</strong> (Settings) &rarr; chọn tab <strong>Chỉ số Trang chủ</strong>.
    </li>
    <li>
      <span class="step-bullet">2</span>
      <strong>Thanh Pill Banner:</strong> Nhập câu thông báo nổi bật (ví dụ: <em>"Pioneering Vietnam's Semiconductor Revolution &middot; Tapeout Ready"</em>).
    </li>
    <li>
      <span class="step-bullet">3</span>
      <strong>3 Chỉ số vàng:</strong>
      <ul>
        <li>Chỉ số 1: Con số <code>15+</code> | Nhãn: <code>Năm kinh nghiệm trong ngành vi mạch</code></li>
        <li>Chỉ số 2: Con số <code>30+</code> | Nhãn: <code>Dự án Tapeout thành công</code></li>
        <li>Chỉ số 3: Con số <code>50+</code> | Nhãn: <code>Kỹ sư thiết kế bán dẫn chuyên sâu</code></li>
      </ul>
    </li>
    <li>
      <span class="step-bullet">4</span>
      Bấm nút <strong>"Lưu cài đặt"</strong> để cập nhật ra ngoài trang chủ.
    </li>
  </ul>
</div>

<!-- 6. Step 4: Articles & News (WYSIWYG & HTML Ace Editor) -->
<div class="guide-section-card" id="step4">
  <div class="guide-section-header">
    <div class="guide-section-title">
      <div class="step-number">04</div>
      <div>
        <h2>Quản trị Bài viết, Tin tức & Sự kiện (Media Center)</h2>
        <p>Kéo thả sắp xếp thứ tự, ghim lên đầu, nhân bản 1-click và trình soạn thảo kép Visual CKEditor + Code Ace Editor.</p>
      </div>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= admin_url('article-create.php') ?>" class="btn btn-sm btn-success" style="border-radius: 6px; font-weight: 700;">
        <i class="fa-solid fa-plus me-1"></i> Viết bài mới
      </a>
      <a href="<?= admin_url('articles.php') ?>" class="btn btn-sm btn-primary" style="border-radius: 6px; font-weight: 700;">
        <i class="fa-solid fa-newspaper me-1"></i> Danh sách bài viết
      </a>
    </div>
  </div>

  <div class="visual-split">
    <div class="visual-pane visual-pane-frontend">
      <div class="visual-pane-header">
        <span><i class="fa-solid fa-globe me-1"></i> Trang Tin tức ngoài Website</span>
        <span class="badge bg-primary">/media-center.php</span>
      </div>
      <div class="visual-pane-img" onclick="openZoomModal('<?= admin_url('assets/guide/frontend_media_center.png') ?>', 'Trang Media Center ngoài Website')">
        <img src="<?= admin_url('assets/guide/frontend_media_center.png') ?>" alt="Frontend Media Center">
        <div class="zoom-badge"><i class="fa-solid fa-magnifying-glass-plus"></i> Nhấp để phóng to</div>
      </div>
      <div class="visual-pane-footer">
        Bộ lọc chuyên mục, bài viết nổi bật (Featured) và danh sách bài viết chuẩn SEO.
      </div>
    </div>

    <div class="visual-pane visual-pane-backend">
      <div class="visual-pane-header">
        <span><i class="fa-solid fa-list-check me-1"></i> Quản lý Danh sách Bài viết</span>
        <span class="badge bg-success">chip-admin/articles.php</span>
      </div>
      <div class="visual-pane-img" onclick="openZoomModal('<?= admin_url('assets/guide/admin_articles_list.png') ?>', 'Danh sách bài viết trong Admin')">
        <img src="<?= admin_url('assets/guide/admin_articles_list.png') ?>" alt="Admin Articles List">
        <div class="zoom-badge"><i class="fa-solid fa-magnifying-glass-plus"></i> Nhấp để phóng to</div>
      </div>
      <div class="visual-pane-footer">
        Kéo thả icon ⠿ để đổi vị trí, bấm sao ★ để nổi bật, ghim 📌 lên đầu trang.
      </div>
    </div>
  </div>

  <div class="visual-split mt-3">
    <div class="visual-pane visual-pane-frontend">
      <div class="visual-pane-header">
        <span><i class="fa-solid fa-pen-nib me-1"></i> Chế độ Soạn thảo Trực quan (Visual - CKEditor)</span>
        <span class="badge bg-info">Dành cho Biên tập viên</span>
      </div>
      <div class="visual-pane-img" onclick="openZoomModal('<?= admin_url('assets/guide/admin_article_edit_visual.png') ?>', 'Trình soạn thảo trực quan CKEditor')">
        <img src="<?= admin_url('assets/guide/admin_article_edit_visual.png') ?>" alt="Visual Editor">
        <div class="zoom-badge"><i class="fa-solid fa-magnifying-glass-plus"></i> Nhấp để phóng to</div>
      </div>
      <div class="visual-pane-footer">
        Dễ dùng như Microsoft Word: In đậm, in nghiêng, chèn ảnh, tạo bảng, canh lề.
      </div>
    </div>

    <div class="visual-pane visual-pane-backend">
      <div class="visual-pane-header">
        <span><i class="fa-solid fa-code me-1"></i> Chế độ Soạn thảo Code HTML (Ace Code Editor)</span>
        <span class="badge bg-dark">Dành cho Kỹ sư / Chuyên sâu</span>
      </div>
      <div class="visual-pane-img" onclick="openZoomModal('<?= admin_url('assets/guide/admin_article_edit_code.png') ?>', 'Trình soạn thảo Code HTML Ace')">
        <img src="<?= admin_url('assets/guide/admin_article_edit_code.png') ?>" alt="Code Editor">
        <div class="zoom-badge"><i class="fa-solid fa-magnifying-glass-plus"></i> Nhấp để phóng to</div>
      </div>
      <div class="visual-pane-footer">
        Chỉnh sửa HTML chuyên sâu, nhúng iframe video YouTube, định dạng code sạch.
      </div>
    </div>
  </div>

  <ul class="step-checklist mt-3">
    <li>
      <span class="step-bullet">1</span>
      <strong>Kéo thả đổi thứ tự bài:</strong> Rê chuột vào biểu tượng 6 dấu chấm (⠿) ở bên trái tiêu đề bài viết &rarr; bấm giữ chuột trái và kéo lên/xuống. Thứ tự ngoài trang web sẽ cập nhật ngay lập tức!
    </li>
    <li>
      <span class="step-bullet">2</span>
      <strong>Ghim lên đầu trang (Pin to Top):</strong> Nhấp vào biểu tượng đinh ghim 📌 màu xanh để bài viết luôn nằm ở vị trí đầu tiên.
    </li>
    <li>
      <span class="step-bullet">3</span>
      <strong>Đánh dấu Nổi bật (Featured):</strong> Nhấp vào biểu tượng ngôi sao ★ để đưa bài viết vào khung Banner to đầu trang Media Center.
    </li>
    <li>
      <span class="step-bullet">4</span>
      <strong>Nhân bản bài viết 1-click (Duplicate):</strong> Bấm vào biểu tượng hai trang giấy để tạo một bản sao lưu nháp của bài viết trong 1 giây mà không cần copy paste thủ công.
    </li>
  </ul>
</div>

<!-- 7. Step 5: Team Members & Leadership -->
<div class="guide-section-card" id="step5">
  <div class="guide-section-header">
    <div class="guide-section-title">
      <div class="step-number">05</div>
      <div>
        <h2>Quản lý Đội ngũ Lãnh đạo & Chuyên gia Bán dẫn (Leadership)</h2>
        <p>Cập nhật chân dung, học vị, chức danh và hồ sơ năng lực chuyên gia trên trang About Us.</p>
      </div>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= admin_url('member-edit.php') ?>" class="btn btn-sm btn-success" style="border-radius: 6px; font-weight: 700;">
        <i class="fa-solid fa-user-plus me-1"></i> Thêm thành viên
      </a>
      <a href="<?= admin_url('members.php') ?>" class="btn btn-sm btn-primary" style="border-radius: 6px; font-weight: 700;">
        <i class="fa-solid fa-user-tie me-1"></i> Danh sách thành viên
      </a>
    </div>
  </div>

  <div class="visual-split">
    <div class="visual-pane visual-pane-frontend">
      <div class="visual-pane-header">
        <span><i class="fa-solid fa-globe me-1"></i> Khối Lãnh đạo ngoài Website</span>
        <span class="badge bg-primary">/about-us.php#leadership</span>
      </div>
      <div class="visual-pane-img" onclick="openZoomModal('<?= admin_url('assets/guide/frontend_about_leadership.png') ?>', 'Khối Lãnh đạo ngoài Website')">
        <img src="<?= admin_url('assets/guide/frontend_about_leadership.png') ?>" alt="Frontend Leadership">
        <div class="zoom-badge"><i class="fa-solid fa-magnifying-glass-plus"></i> Nhấp để phóng to</div>
      </div>
      <div class="visual-pane-footer">
        Hiển thị ảnh chân dung, chức danh và liên kết xem tiểu sử chi tiết (Bio modal).
      </div>
    </div>

    <div class="visual-pane visual-pane-backend">
      <div class="visual-pane-header">
        <span><i class="fa-solid fa-users-gear me-1"></i> Khối Quản trị Thành viên</span>
        <span class="badge bg-success">chip-admin/members.php</span>
      </div>
      <div class="visual-pane-img" onclick="openZoomModal('<?= admin_url('assets/guide/admin_page_sections_about.png') ?>', 'Quản trị Thành viên Lãnh đạo')">
        <img src="<?= admin_url('assets/guide/admin_page_sections_about.png') ?>" alt="Admin Members">
        <div class="zoom-badge"><i class="fa-solid fa-magnifying-glass-plus"></i> Nhấp để phóng to</div>
      </div>
      <div class="visual-pane-footer">
        Dễ dàng kéo thả sắp xếp cấp bậc và cập nhật thông tin LinkedIn cá nhân.
      </div>
    </div>
  </div>

  <ul class="step-checklist">
    <li>
      <span class="step-bullet">1</span>
      Vào menu <strong>Đội ngũ Lãnh đạo</strong> (Members). Bấm <strong>"Thêm thành viên"</strong> hoặc bấm nút sửa (cây bút).
    </li>
    <li>
      <span class="step-bullet">2</span>
      <strong>Ảnh chân dung:</strong> Tải ảnh kích thước vuông (khuyến nghị 600x600 px hoặc 800x800 px), nền tối hoặc trong suốt để hiển thị đồng bộ.
    </li>
    <li>
      <span class="step-bullet">3</span>
      <strong>Chức danh & Tiểu sử:</strong> Nhập học hàm/học vị (TS, ThS, Kỹ sư trưởng...), vị trí công tác và đoạn giới thiệu ngắn về các thành tựu tapeout vi mạch đã đạt được.
    </li>
    <li>
      <span class="step-bullet">4</span>
      Bấm <strong>"Lưu thông tin"</strong>.
    </li>
  </ul>
</div>

<!-- 8. Step 6: Partners & Case Studies -->
<div class="guide-section-card" id="step6">
  <div class="guide-section-header">
    <div class="guide-section-title">
      <div class="step-number">06</div>
      <div>
        <h2>Quản lý Đối tác & Khách hàng Chiến lược (Partners)</h2>
        <p>Quản lý logo mạng lưới doanh nghiệp vi mạch và đính kèm tài liệu giải pháp PDF.</p>
      </div>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= admin_url('partner-edit.php') ?>" class="btn btn-sm btn-success" style="border-radius: 6px; font-weight: 700;">
        <i class="fa-solid fa-plus me-1"></i> Thêm đối tác
      </a>
      <a href="<?= admin_url('partners.php') ?>" class="btn btn-sm btn-primary" style="border-radius: 6px; font-weight: 700;">
        <i class="fa-solid fa-handshake me-1"></i> Danh sách đối tác
      </a>
    </div>
  </div>

  <div class="visual-split">
    <div class="visual-pane visual-pane-frontend">
      <div class="visual-pane-header">
        <span><i class="fa-solid fa-globe me-1"></i> Lưới Đối tác ngoài Website</span>
        <span class="badge bg-primary">/partners.php</span>
      </div>
      <div class="visual-pane-img" onclick="openZoomModal('<?= admin_url('assets/guide/frontend_partners_grid.png') ?>', 'Lưới Đối tác ngoài Website')">
        <img src="<?= admin_url('assets/guide/frontend_partners_grid.png') ?>" alt="Frontend Partners">
        <div class="zoom-badge"><i class="fa-solid fa-magnifying-glass-plus"></i> Nhấp để phóng to</div>
      </div>
      <div class="visual-pane-footer">
        Logo hãng công nghệ, phân loại đối tác (Foundry, EDA, Academic) và nút tải Case Study.
      </div>
    </div>

    <div class="visual-pane visual-pane-backend">
      <div class="visual-pane-header">
        <span><i class="fa-solid fa-handshake-angle me-1"></i> Quản trị Đối tác trong Admin</span>
        <span class="badge bg-success">chip-admin/partners.php</span>
      </div>
      <div class="visual-pane-img" onclick="openZoomModal('<?= admin_url('assets/guide/admin_media_gallery.png') ?>', 'Thư viện Media & Tệp đối tác')">
        <img src="<?= admin_url('assets/guide/admin_media_gallery.png') ?>" alt="Admin Partners Media">
        <div class="zoom-badge"><i class="fa-solid fa-magnifying-glass-plus"></i> Nhấp để phóng to</div>
      </div>
      <div class="visual-pane-footer">
        Đính kèm tệp PDF tài liệu dự án trực tiếp vào thông tin đối tác.
      </div>
    </div>
  </div>

  <ul class="step-checklist">
    <li>
      <span class="step-bullet">1</span>
      Vào menu <strong>Đối tác & Case Study</strong> (Partners) &rarr; Bấm <strong>"Thêm đối tác"</strong>.
    </li>
    <li>
      <span class="step-bullet">2</span>
      <strong>Logo đối tác:</strong> Nên sử dụng định dạng <code>.png</code> nền trong suốt (Transparent) màu trắng hoặc màu sáng để hiển thị đẹp mắt trên nền tối của website.
    </li>
    <li>
      <span class="step-bullet">3</span>
      <strong>Tài liệu Case Study:</strong> Bạn có thể tải file PDF bài nghiên cứu / giải pháp tapeout để khách hàng bấm tải về trực tiếp từ website.
    </li>
  </ul>
</div>

<!-- 9. Step 7: Inquiries & Leads Management -->
<div class="guide-section-card" id="step7">
  <div class="guide-section-header">
    <div class="guide-section-title">
      <div class="step-number">07</div>
      <div>
        <h2>Tiếp nhận & Xử lý Yêu cầu Khách hàng (Inquiries & Leads)</h2>
        <p>Cửa sổ xem nhanh tin nhắn (Modal), gắn nhãn trạng thái và xuất danh bạ ra file Excel tiếng Việt chuẩn UTF-8 BOM.</p>
      </div>
    </div>
    <a href="<?= admin_url('inquiries.php') ?>" class="btn btn-sm btn-primary" style="border-radius: 6px; font-weight: 700;">
      <i class="fa-solid fa-envelope-open-text me-1"></i> Mở Hộp thư Yêu cầu
    </a>
  </div>

  <div class="visual-split">
    <div class="visual-pane visual-pane-frontend">
      <div class="visual-pane-header">
        <span><i class="fa-solid fa-globe me-1"></i> Form Gửi yêu cầu ngoài Website</span>
        <span class="badge bg-primary">/talk-to-us.php</span>
      </div>
      <div class="visual-pane-img" onclick="openZoomModal('<?= admin_url('assets/guide/frontend_talk_to_us.png') ?>', 'Form gửi yêu cầu tư vấn ngoài Website')">
        <img src="<?= admin_url('assets/guide/frontend_talk_to_us.png') ?>" alt="Frontend Talk To Us">
        <div class="zoom-badge"><i class="fa-solid fa-magnifying-glass-plus"></i> Nhấp để phóng to</div>
      </div>
      <div class="visual-pane-footer">
        Nơi đối tác và khách hàng điền form yêu cầu báo giá hoặc tư vấn kỹ thuật bán dẫn.
      </div>
    </div>

    <div class="visual-pane visual-pane-backend">
      <div class="visual-pane-header">
        <span><i class="fa-solid fa-eye me-1"></i> Cửa sổ Xem nhanh Tin nhắn Khách (Modal)</span>
        <span class="badge bg-success">chip-admin/inquiries.php</span>
      </div>
      <div class="visual-pane-img" onclick="openZoomModal('<?= admin_url('assets/guide/admin_inquiry_modal.png') ?>', 'Xem chi tiết tin nhắn khách trong Modal')">
        <img src="<?= admin_url('assets/guide/admin_inquiry_modal.png') ?>" alt="Admin Inquiry Modal">
        <div class="zoom-badge"><i class="fa-solid fa-magnifying-glass-plus"></i> Nhấp để phóng to</div>
      </div>
      <div class="visual-pane-footer">
        Đọc trọn vẹn thông tin khách, chuyển trạng thái và gửi email phản hồi trực tiếp.
      </div>
    </div>
  </div>

  <ul class="step-checklist">
    <li>
      <span class="step-bullet">1</span>
      Vào menu <strong>Liên hệ & Yêu cầu</strong> (Inquiries). Khi có khách mới gửi form, menu sẽ hiển thị số thông báo màu đỏ.
    </li>
    <li>
      <span class="step-bullet">2</span>
      <strong>Xem chi tiết không cần tải lại trang:</strong> Bấm vào biểu tượng con mắt <i class="fa-solid fa-eye"></i> để mở cửa sổ Modal đọc ngay nội dung tin nhắn.
    </li>
    <li>
      <span class="step-bullet">3</span>
      <strong>Chuyển trạng thái xử lý:</strong> Chọn <em>Mới (New)</em> &rarr; <em>Đang xử lý (In Progress)</em> &rarr; <em>Đã giải quyết (Resolved)</em> để theo dõi tiến độ chăm sóc khách hàng.
    </li>
    <li>
      <span class="step-bullet">4</span>
      <strong>Xuất danh sách ra Excel:</strong> Bấm nút <strong>"Xuất danh sách (CSV)"</strong> ở góc trên bên phải. File tải về chuẩn UTF-8 BOM, đảm bảo mở bằng Excel hiển thị tiếng Việt có dấu rõ nét 100%, không bao giờ bị lỗi font.
    </li>
  </ul>
</div>

<!-- 10. Step 8: Page Sections Builder -->
<div class="guide-section-card" id="step8">
  <div class="guide-section-header">
    <div class="guide-section-title">
      <div class="step-number">08</div>
      <div>
        <h2>Tùy biến Khối Nội dung Trang tĩnh (Page Sections Builder)</h2>
        <p>Chỉnh sửa các khối văn bản, triết lý, sứ mệnh, quy trình thiết kế chiplet trên Technology, About Us, Business...</p>
      </div>
    </div>
    <a href="<?= admin_url('page-sections.php') ?>" class="btn btn-sm btn-primary" style="border-radius: 6px; font-weight: 700;">
      <i class="fa-solid fa-cubes me-1"></i> Đến Khối trang tĩnh
    </a>
  </div>

  <div class="visual-split">
    <div class="visual-pane visual-pane-frontend">
      <div class="visual-pane-header">
        <span><i class="fa-solid fa-globe me-1"></i> Khối Công nghệ ngoài Website</span>
        <span class="badge bg-primary">/technology.php</span>
      </div>
      <div class="visual-pane-img" onclick="openZoomModal('<?= admin_url('assets/guide/frontend_technology.png') ?>', 'Khối nội dung Công nghệ ngoài Website')">
        <img src="<?= admin_url('assets/guide/frontend_technology.png') ?>" alt="Frontend Technology">
        <div class="zoom-badge"><i class="fa-solid fa-magnifying-glass-plus"></i> Nhấp để phóng to</div>
      </div>
      <div class="visual-pane-footer">
        Khối giới thiệu Kiến trúc Chiplet, quy trình tapeout và dịch vụ đóng gói tiên tiến.
      </div>
    </div>

    <div class="visual-pane visual-pane-backend">
      <div class="visual-pane-header">
        <span><i class="fa-solid fa-pen-ruler me-1"></i> Quản trị Khối Trang tĩnh trong Admin</span>
        <span class="badge bg-success">chip-admin/page-sections.php</span>
      </div>
      <div class="visual-pane-img" onclick="openZoomModal('<?= admin_url('assets/guide/admin_page_sections_tech.png') ?>', 'Trình dựng khối nội dung trong Admin')">
        <img src="<?= admin_url('assets/guide/admin_page_sections_tech.png') ?>" alt="Admin Page Sections">
        <div class="zoom-badge"><i class="fa-solid fa-magnifying-glass-plus"></i> Nhấp để phóng to</div>
      </div>
      <div class="visual-pane-footer">
        Dễ dàng sửa câu chữ, hình ảnh minh họa cho từng khối mà không cần biết lập trình.
      </div>
    </div>
  </div>

  <ul class="step-checklist">
    <li>
      <span class="step-bullet">1</span>
      Vào menu <strong>Khối trang tĩnh</strong> (Page Sections).
    </li>
    <li>
      <span class="step-bullet">2</span>
      Ở thanh tab phía trên, bấm chọn trang bạn muốn chỉnh sửa: <strong>Technology</strong>, <strong>About Us</strong>, <strong>Business</strong> hoặc <strong>Sustainable</strong>.
    </li>
    <li>
      <span class="step-bullet">3</span>
      Bấm nút <strong>"Chỉnh sửa"</strong> tại khối tương ứng để đổi tiêu đề, mô tả văn bản, nút bấm hoặc hình minh họa.
    </li>
    <li>
      <span class="step-bullet">4</span>
      Bấm <strong>"Lưu thay đổi"</strong> và nhấn <code>Ctrl + F5</code> ngoài website để kiểm tra.
    </li>
  </ul>
</div>

<!-- 11. Step 9: Media Gallery & WebP -->
<div class="guide-section-card" id="step9">
  <div class="guide-section-header">
    <div class="guide-section-title">
      <div class="step-number">09</div>
      <div>
        <h2>Thư viện Media & Tải ảnh Thông minh Chuẩn WebP</h2>
        <p>Kéo thả nhiều ảnh cùng lúc, tự động tối ưu hóa dung lượng chuẩn Google WebP 80% và sao chép link 1-click.</p>
      </div>
    </div>
    <a href="<?= admin_url('media.php') ?>" class="btn btn-sm btn-primary" style="border-radius: 6px; font-weight: 700;">
      <i class="fa-solid fa-photo-film me-1"></i> Mở Thư viện Media
    </a>
  </div>

  <div class="visual-pane visual-pane-backend mb-3">
    <div class="visual-pane-header">
      <span><i class="fa-solid fa-cloud-arrow-up me-1"></i> Giao diện Thư viện Ảnh & Tệp tin</span>
      <span class="badge bg-success">chip-admin/media.php</span>
    </div>
    <div class="visual-pane-img" onclick="openZoomModal('<?= admin_url('assets/guide/admin_media_gallery.png') ?>', 'Giao diện Thư viện Media Quản trị')">
      <img src="<?= admin_url('assets/guide/admin_media_gallery.png') ?>" alt="Admin Media Gallery">
      <div class="zoom-badge"><i class="fa-solid fa-magnifying-glass-plus"></i> Nhấp để phóng to</div>
    </div>
    <div class="visual-pane-footer">
      Kéo thả ảnh trực tiếp vào khung nét đứt màu xanh. Sao chép link URL hoặc đường dẫn nội bộ chỉ với 1 click.
    </div>
  </div>

  <ul class="step-checklist">
    <li>
      <span class="step-bullet">1</span>
      <strong>Tải ảnh nhanh:</strong> Kéo trực tiếp các tệp ảnh từ máy tính thả vào khung viền nét đứt màu xanh.
    </li>
    <li>
      <span class="step-bullet">2</span>
      <strong>Tự động tối ưu WebP:</strong> Hệ thống tự động chuyển đổi định dạng ảnh sang chuẩn WebP 80% chất lượng cao của Google, giúp giảm 70% dung lượng tệp, làm website tải nhanh vượt trội.
    </li>
    <li>
      <span class="step-bullet">3</span>
      <strong>Sao chép link chèn bài viết:</strong> Bấm nút <strong>"Copy URL"</strong> ở góc mỗi ảnh để dán vào bài viết tin tức hoặc khối trang tĩnh.
    </li>
  </ul>
</div>

<!-- 12. Step 10: Global Settings & SEO -->
<div class="guide-section-card" id="step10">
  <div class="guide-section-header">
    <div class="guide-section-title">
      <div class="step-number">10</div>
      <div>
        <h2>Cấu hình Thương hiệu, SEO & Google Analytics</h2>
        <p>Cập nhật logo website, biểu tượng Favicon, thông tin liên hệ và mã đo lường truy cập.</p>
      </div>
    </div>
    <a href="<?= admin_url('site-settings.php') ?>" class="btn btn-sm btn-primary" style="border-radius: 6px; font-weight: 700;">
      <i class="fa-solid fa-gear me-1"></i> Đến Cấu hình Thương hiệu
    </a>
  </div>

  <ul class="step-checklist">
    <li>
      <span class="step-bullet">1</span>
      Vào menu <strong>Cấu hình thương hiệu</strong> (Site Settings) ở cột trái.
    </li>
    <li>
      <span class="step-bullet">2</span>
      <strong>Logo & Favicon:</strong> Tải logo chính của công ty (dạng PNG trong suốt hoặc SVG) và biểu tượng icon hiển thị trên tab trình duyệt.
    </li>
    <li>
      <span class="step-bullet">3</span>
      <strong>Tối ưu hóa SEO:</strong> Nhập Tiêu đề trang mặc định (Site Title), Thẻ mô tả (Meta Description) và Ảnh chia sẻ khi gửi link qua Zalo / Facebook (OG Image).
    </li>
    <li>
      <span class="step-bullet">4</span>
      <strong>Google Analytics:</strong> Dán mã đo lường (ví dụ <code>G-XXXXXXXXXX</code>) vào ô tương ứng để tự động kích hoạt theo dõi lượng người xem website mỗi ngày.
    </li>
  </ul>
</div>

<!-- 13. Image Specifications Table -->
<div class="guide-section-card" id="specs">
  <div class="guide-section-header">
    <div class="guide-section-title">
      <div class="step-number" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%);">
        <i class="fa-solid fa-ruler-combined"></i>
      </div>
      <div>
        <h2>Bảng Quy chuẩn Kích thước & Định dạng Ảnh Tối ưu</h2>
        <p>Áp dụng các thông số chuẩn dưới đây để website hiển thị sắc nét nhất trên cả máy tính lẫn điện thoại di động.</p>
      </div>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>Vị trí hiển thị</th>
          <th class="text-center">Kích thước chuẩn (Dài x Rộng)</th>
          <th class="text-center">Tỷ lệ khung hình</th>
          <th class="text-center">Định dạng khuyên dùng</th>
          <th class="text-center">Dung lượng tối đa</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>Video nền Trang chủ</strong></td>
          <td class="text-center font-monospace text-primary">1920 x 1080 px</td>
          <td class="text-center">16:9 (Ngang chuẩn)</td>
          <td class="text-center"><code>.mp4</code> hoặc <code>.webm</code></td>
          <td class="text-center text-success font-monospace">&lt; 10 MB</td>
        </tr>
        <tr>
          <td><strong>Banner Hero các trang con</strong></td>
          <td class="text-center font-monospace text-primary">1920 x 800 px</td>
          <td class="text-center">~ 21:9 (Toàn cảnh)</td>
          <td class="text-center"><code>.webp</code>, <code>.jpg</code></td>
          <td class="text-center text-success font-monospace">&lt; 350 KB</td>
        </tr>
        <tr>
          <td><strong>Ảnh đại diện Bài viết Tin tức</strong></td>
          <td class="text-center font-monospace text-primary">1200 x 675 px</td>
          <td class="text-center">16:9 (Chuẩn MXH)</td>
          <td class="text-center"><code>.webp</code>, <code>.jpg</code></td>
          <td class="text-center text-success font-monospace">&lt; 200 KB</td>
        </tr>
        <tr>
          <td><strong>Ảnh chân dung Ban Lãnh đạo</strong></td>
          <td class="text-center font-monospace text-primary">600 x 600 px</td>
          <td class="text-center">1:1 (Hình vuông)</td>
          <td class="text-center"><code>.webp</code>, <code>.png</code></td>
          <td class="text-center text-success font-monospace">&lt; 150 KB</td>
        </tr>
        <tr>
          <td><strong>Logo Đối tác & Khách hàng</strong></td>
          <td class="text-center font-monospace text-primary">400 x 200 px</td>
          <td class="text-center">Ngang tự do</td>
          <td class="text-center"><code>.png</code> (nền trong) hoặc <code>.svg</code></td>
          <td class="text-center text-success font-monospace">&lt; 80 KB</td>
        </tr>
        <tr>
          <td><strong>Logo Website chính</strong></td>
          <td class="text-center font-monospace text-primary">280 x 70 px</td>
          <td class="text-center">Ngang</td>
          <td class="text-center"><code>.svg</code> hoặc <code>.png</code> trong suốt</td>
          <td class="text-center text-success font-monospace">&lt; 50 KB</td>
        </tr>
        <tr>
          <td><strong>Favicon (Icon tab trình duyệt)</strong></td>
          <td class="text-center font-monospace text-primary">64 x 64 px</td>
          <td class="text-center">1:1 (Vuông nhỏ)</td>
          <td class="text-center"><code>.png</code> hoặc <code>.ico</code></td>
          <td class="text-center text-success font-monospace">&lt; 20 KB</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- 14. Verification Checklist & FAQ Section -->
<div class="guide-section-card" id="faq">
  <div class="guide-section-header">
    <div class="guide-section-title">
      <div class="step-number" style="background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);">
        <i class="fa-solid fa-circle-question"></i>
      </div>
      <div>
        <h2>Quy trình Kiểm tra sau khi Cập nhật & Câu hỏi Thường gặp (FAQ)</h2>
        <p>Các câu hỏi phổ biến và mẹo khắc phục nhanh khi gặp sự cố bộ nhớ đệm.</p>
      </div>
    </div>
  </div>

  <div class="guide-alert guide-alert-warning mb-4">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>
      <strong>Quy tắc xóa bộ nhớ đệm (Clear Cache):</strong> Sau khi lưu thay đổi trong Admin, bạn hãy mở website ngoài và nhấn tổ hợp phím <code>Ctrl + F5</code> (trên Windows) hoặc <code>Cmd + Shift + R</code> (trên Mac) để trình duyệt tải dữ liệu mới nhất từ máy chủ!
    </div>
  </div>

  <div class="accordion" id="guideFaqAccordion">
    <div class="accordion-item mb-2 border rounded">
      <h2 class="accordion-header" id="headingOne">
        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
          1. Tôi đã tạo bài viết mới và bấm lưu, tại sao ra ngoài trang web tìm không thấy?
        </button>
      </h2>
      <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#guideFaqAccordion">
        <div class="accordion-body text-secondary">
          Hãy kiểm tra lại ô <strong>Trạng thái (Status)</strong> của bài viết. Nếu bài viết đang ở trạng thái <em>Lưu nháp (Draft)</em> thì chỉ có quản trị viên mới thấy. Bạn cần chuyển trạng thái sang <strong>Xuất bản (Published)</strong> rồi bấm <em>Lưu thay đổi</em> để bài viết xuất hiện ngoài công chúng.
        </div>
      </div>
    </div>

    <div class="accordion-item mb-2 border rounded">
      <h2 class="accordion-header" id="headingTwo">
        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          2. Tôi đã thay đổi ảnh Banner hoặc Logo nhưng ngoài web vẫn hiện ảnh cũ?
        </button>
      </h2>
      <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#guideFaqAccordion">
        <div class="accordion-body text-secondary">
          Đây là hiện tượng lưu cache của trình duyệt web trên máy tính của bạn. Bạn hãy nhấn tổ hợp phím <code>Ctrl + F5</code> để xóa cache. Hoặc mở một cửa sổ trình duyệt ở chế độ Ẩn danh (<code>Ctrl + Shift + N</code>) để kiểm tra.
        </div>
      </div>
    </div>

    <div class="accordion-item mb-2 border rounded">
      <h2 class="accordion-header" id="headingThree">
        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
          3. Tải file danh sách liên hệ khách hàng (CSV) mở bằng Microsoft Excel có bị lỗi font tiếng Việt không?
        </button>
      </h2>
      <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#guideFaqAccordion">
        <div class="accordion-body text-secondary">
          Hoàn toàn không! Hệ thống VNCHIPS đã được nâng cấp tích hợp sẵn chuẩn quốc tế <strong>UTF-8 BOM</strong> khi xuất file. Bạn chỉ cần bấm <em>"Xuất danh sách (CSV)"</em> trong menu Liên hệ & Yêu cầu và mở trực tiếp bằng Excel, toàn bộ tiếng Việt có dấu sẽ hiển thị nguyên vẹn và chuẩn xác 100%.
        </div>
      </div>
    </div>

    <div class="accordion-item mb-2 border rounded">
      <h2 class="accordion-header" id="headingFour">
        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
          4. Tôi muốn chèn thêm liên kết vào thanh Menu trên cùng thì làm thế nào?
        </button>
      </h2>
      <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#guideFaqAccordion">
        <div class="accordion-body text-secondary">
          Vào menu <strong>Quản lý Menu</strong> (Navigation & Menus) ở cột trái &rarr; Bấm <em>"Thêm mục menu mới"</em> &rarr; Nhập Tiêu đề hiển thị (ví dụ: Tuyển dụng, Nghiên cứu...) &rarr; Nhập đường dẫn liên kết URL &rarr; Chọn vị trí menu cha (nếu muốn làm menu con) rồi bấm <em>Lưu thay đổi</em>.
        </div>
      </div>
    </div>

    <div class="accordion-item border rounded">
      <h2 class="accordion-header" id="headingFive">
        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
          5. Tại sao khi mở website ngoài tôi không thấy thanh VNCHIP ADMIN hoặc nút gạt Live Edit?
        </button>
      </h2>
      <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#guideFaqAccordion">
        <div class="accordion-body text-secondary">
          Thanh công cụ <strong>VNCHIP ADMIN</strong> và tính năng <strong>Live Edit</strong> chỉ hiển thị khi bạn đang đăng nhập Quản trị viên trên cùng trình duyệt đó. Hãy truy cập <a href="<?= admin_url('login.php') ?>"><code>/chip-admin/login.php</code></a> để đăng nhập lại (phiên sẽ được tự động lưu trong <strong>30 ngày</strong>). Sau đó quay lại website ngoài và nhấp vào công tắc <strong>EDIT MODE</strong> để chuyển sang trạng thái <strong>ON</strong>.
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal for image zooming -->
<div class="modal fade" id="imageZoomModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0">
      <div class="modal-header bg-dark text-white border-0 py-2 px-3">
        <h6 class="modal-title font-monospace" id="imageZoomTitle" style="font-size: 0.88rem;"></h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img id="imageZoomSrc" src="" alt="Zoomed Preview">
      </div>
    </div>
  </div>
</div>

<script>
// Filter map table by search keywords
document.getElementById('mapSearchInput').addEventListener('input', function(e) {
  const query = e.target.value.toLowerCase().trim();
  const rows = document.querySelectorAll('#mapTable tbody tr');
  
  rows.forEach(row => {
    const text = row.innerText.toLowerCase();
    if (text.includes(query)) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
});

// Open high-res image zoom modal
function openZoomModal(imageSrc, title) {
  document.getElementById('imageZoomSrc').src = imageSrc;
  document.getElementById('imageZoomTitle').innerText = title || 'Ảnh minh họa chi tiết';
  const modal = new bootstrap.Modal(document.getElementById('imageZoomModal'));
  modal.show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
