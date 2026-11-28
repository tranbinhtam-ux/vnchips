<?php
/**
 * Frontend Admin Bar & Live Edit Engine
 * Displayed exclusively to authenticated administrators on public frontend pages
 */

if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}

require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/Program.php';

// Only execute for logged-in administrators
if (!Admin::check()) {
    return;
}

// Prevent LiteSpeed & browser from caching Admin Live Edit HTML views
if (!headers_sent()) {
    header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('X-LiteSpeed-Cache-Control: no-cache');
}

$currentAdmin = Admin::current();

$defaultFooterNavMarkup = <<<HTML
        <!-- Column 1: Sustainable -->
        <div class="footer-col">
          <h4 class="footer-col-title">SUSTAINABLE</h4>
          <ul class="footer-links">
            <li><a href="sustainable.php#social-responsibilities">Social Responsibilities</a></li>
            <li><a href="sustainable.php#clean-room">Clean Room</a></li>
            <li><a href="sustainable.php#talents-development">Talents Development</a></li>
            <li><a href="sustainable.php#confidentiality">Confidentiality</a></li>
            <li><a href="sustainable.php#partner">Partners</a></li>
          </ul>
        </div>

        <!-- Column 2: Design Solutions -->
        <div class="footer-col">
          <h4 class="footer-col-title">DESIGN SOLUTIONS</h4>
          <div class="footer-links-subgroup">
            <div>
              <div class="footer-sub-heading">DESIGN SERVICE</div>
              <ul class="footer-links">
                <li><a href="business.php#spec-gdsii">Specification to GDSII</a></li>
                <li><a href="business.php#rtl-gdsii">RTL to GDSII</a></li>
                <li><a href="business.php#netlist-gdsii">Netlist to GDSII</a></li>
              </ul>
            </div>
            <div>
              <div class="footer-sub-heading">IP SERVICE</div>
              <ul class="footer-links">
                <li><a href="business.php#ip-development">IP Development</a></li>
                <li><a href="business.php#circuit-design">Circuit Design</a></li>
                <li><a href="business.php#custom-layout">Custom Layout</a></li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Column 3: Quick Links -->
        <div class="footer-col">
          <h4 class="footer-col-title">QUICK LINKS</h4>
          <ul class="footer-links">
            <li><a href="about-us.php">VNCHIP Journey</a></li>
            <li><a href="partners.php">Partners</a></li>
            <li><a href="media-center.php">Media Center</a></li>
            <li><a href="talk-to-us.php">Contact us</a></li>
          </ul>
        </div>
HTML;

// Default About Us content helpers for Live Edit modals
$defaultAboutStoryHtml = <<<HTML
<p>VNCHIP first started as a training initiative aimed at bridging the skills gap in IC Design industry. Over the past 6 years, we have evolved into a specialized service provider, offering IC design services, consulting, and tailored technology solutions.</p>
<p>VNCHIP experts' team is committed to pushing the boundaries of technology while staying true to our core values of knowledge-sharing, innovation, and client satisfaction. We are excited to continue growing and making a lasting impact on Vietnam IC Design industry.</p>
HTML;

$defaultAboutOverviewGridHtml = <<<HTML
<div class="overview-grid">
  <div class="overview-image-wrapper">
    <img src="assets/images/about_lobby_reception.webp" alt="VNCHIP Headquarters & Lobby" class="overview-img" />
  </div>
  <div class="overview-text-wrapper">
    <p class="overview-text-p">
      VNCHIP is a leading provider of turnkey IC design services in Vietnam, offering comprehensive solutions across the semiconductor design spectrum, including Front-End Design, Design Verification, Physical Design, STA, DFT, and Custom Layout. With a mission to empower Vietnam’s engineering talent and accelerate the nation's semiconductor capabilities, VNCHIP combines deep technical expertise with a strong commitment to innovation, quality, and industry collaboration.
    </p>
    <p class="overview-text-p">
      We work closely with strategic partners and clients around the globe, delivering tailored solutions that drive performance, reduce time-to-market, and meet the highest international standards. Through the VNCHIP Institute, we also nurture the next generation of IC design engineers by offering hands-on training programs, mentorship, and career development opportunities, aligned with real industry demands. VNCHIP stands at the intersection of talent development and cutting-edge semiconductor design, driving progress for both individuals and the broader tech ecosystem in Vietnam.
    </p>
  </div>
</div>
HTML;

$defaultAboutTimelineData = [
    ['year' => '2018',      'desc' => 'Establish a Training Center and offer Free IC Design Training Courses'],
    ['year' => '2020',      'desc' => 'VNCHIP Technology JSC. establishment'],
    ['year' => '2021',      'desc' => 'Taped out ASIC/SOC from 90nm down to 16nm FinFET process'],
    ['year' => '2021',      'desc' => 'Taped Out 7nm FinFET process'],
    ['year' => '2023',      'desc' => 'Starting working'],
    ['year' => '2024',      'desc' => 'Taped out 3nm FinFET process'],
    ['year' => 'Until now', 'desc' => 'Signed 2P NDA with Samsung Foundry for N14, N8, N5, N4 access.'],
];
$liveAboutTimelineData = $defaultAboutTimelineData;
if (($rawTl = Setting::get('about_timeline_items', '')) !== '') {
    $decTl = json_decode($rawTl, true);
    if (is_array($decTl) && count($decTl) > 0) $liveAboutTimelineData = $decTl;
}

$defaultAboutVisionCardsData = [
    [
        'icon'  => 'eye',
        'title' => 'VISION',
        'text'  => "To become Vietnam’s most trusted Turnkey IC design partner by delivering innovative, end-to-end design solutions that empower our customers' success.",
    ],
    [
        'icon'  => 'target',
        'title' => 'MISSION',
        'text'  => 'To co-create innovative IC design solutions with strategic partners, while empowering Vietnamese engineering talent and contributing to the advancement of education and technology.',
    ],
    [
        'icon'  => 'atom',
        'title' => 'CORE VALUES',
        'text'  => 'Committed to cultivating talented engineers, driving continuous improvement, and advancing a sustainable IC Design industry',
    ],
];
$liveAboutVisionCardsData = $defaultAboutVisionCardsData;
if (($rawVc = Setting::get('about_vision_cards', '')) !== '') {
    $decVc = json_decode($rawVc, true);
    if (is_array($decVc) && count($decVc) > 0) $liveAboutVisionCardsData = $decVc;
}

$defaultAboutConductCardsData = [
    [
        'title' => "INTEGRITY AND\nETHICAL STANDARDS",
        'text'  => 'We commit to maintaining the highest ethical standards in all our R&D activities. All members are expected to uphold honesty, transparency, and accountability in their work.',
    ],
    [
        'title' => "INNOVATION AND\nEXCELLENCE",
        'text'  => 'We strive for continuous innovation and excellence in semiconductor technology by encouraging creativity and pushing the boundaries of what’s possible.',
    ],
    [
        'title' => "CONFIDENTIALITY AND\nDATA PROTECTION",
        'text'  => 'We are dedicated to safeguarding proprietary information, sensitive data, and intellectual property. All staff, students, and partners are required to comply with strict confidentiality agreements to protect the interests of VNCHIP Institute and our Strategic Partners.',
    ],
    [
        'title' => "SUSTAINABILITY AND\nRESPONSIBILITY",
        'text'  => 'We are committed to conducting Institute R&D activities in an environmentally sustainable and socially responsible manner, ensuring a positive impact on society.',
    ],
];
$liveAboutConductCardsData = $defaultAboutConductCardsData;
if (($rawCc = Setting::get('about_conduct_cards', '')) !== '') {
    $decCc = json_decode($rawCc, true);
    if (is_array($decCc) && count($decCc) > 0) $liveAboutConductCardsData = $decCc;
}
?>

<!-- 1. Global Admin Top Bar -->
<div id="vnchip-admin-bar" class="vnchip-admin-bar" role="navigation" aria-label="VNCHIP Admin Toolbar">
  <div class="admin-bar-container">
    
    <!-- Left: Brand & Admin Identity -->
    <div class="admin-bar-left">
      <a href="<?= admin_url('index.php') ?>" target="_blank" rel="noopener noreferrer" class="admin-bar-brand" title="Vào Bảng điều khiển Quản trị (Tab mới)">
        <i class="fa-solid fa-microchip text-primary"></i>
        <span>VNCHIP <strong style="color: #38BDF8;">ADMIN</strong></span>
      </a>
      <span class="admin-bar-divider"></span>
      <div class="admin-bar-user">
        <i class="fa-solid fa-user-shield text-info"></i>
        <span><?= e($currentAdmin['fullname'] ?? 'Admin') ?></span>
        <span class="admin-role-badge"><?= strtoupper($currentAdmin['role'] ?? 'Superadmin') ?></span>
      </div>
    </div>

    <!-- Center: Live Edit Toggle Switch -->
    <div class="admin-bar-center">
      <div class="live-edit-switch-wrap" id="switchWrapBtn" title="Bật/Tắt chế độ chỉnh sửa trực tiếp trên giao diện website">
        <span class="live-edit-switch-label">
          <i class="fa-solid fa-pen-to-square"></i>
          <span>EDIT MODE:</span>
        </span>
        <div class="live-toggle-slot">
          <input type="checkbox" id="liveEditModeToggle" class="live-toggle-input">
          <span class="live-toggle-slider"></span>
        </div>
        <span class="live-edit-status-tag" id="liveEditStateText">OFF</span>
      </div>
    </div>

    <!-- Right: Quick Navigation Links -->
    <div class="admin-bar-right">
      <a href="<?= admin_url('index.php') ?>" target="_blank" rel="noopener noreferrer" class="admin-bar-btn" title="Bảng điều khiển Tổng quan (Tab mới)">
        <i class="fa-solid fa-gauge-high"></i> Dashboard
      </a>
      <a href="<?= admin_url('menus.php') ?>" target="_blank" rel="noopener noreferrer" class="admin-bar-btn" title="Quản lý menu điều hướng (Tab mới)">
        <i class="fa-solid fa-bars"></i> Menu
      </a>
      <a href="<?= admin_url('page-sections.php') ?>" target="_blank" rel="noopener noreferrer" class="admin-bar-btn" title="Quản lý khối trang tĩnh (Tab mới)">
        <i class="fa-solid fa-cubes"></i> Page Sections
      </a>
      <a href="<?= admin_url('banners.php') ?>" target="_blank" rel="noopener noreferrer" class="admin-bar-btn" title="Đổi Banner & Video Hero (Tab mới)">
        <i class="fa-solid fa-image"></i> Banners
      </a>
      <a href="<?= admin_url('user-guide.php') ?>" target="_blank" rel="noopener noreferrer" class="admin-bar-btn" title="Cẩm nang hướng dẫn sử dụng (Tab mới)">
        <i class="fa-solid fa-book-open-reader"></i> User Guide
      </a>
      <a href="<?= admin_url('logout.php') ?>" class="admin-bar-btn admin-bar-btn-logout" title="Đăng xuất khỏi hệ thống">
        <i class="fa-solid fa-power-off"></i> Thoát
      </a>
    </div>

  </div>
</div>

<!-- 2. Admin Live Edit Stylesheet -->
<style>
/* Admin Bar Container */
.vnchip-admin-bar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 42px;
  background: #0A1120;
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
  z-index: 999999;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.35);
  line-height: 1;
}

body.admin-bar-active {
  padding-top: 42px !important;
}

body.admin-bar-active #mainNav,
body.admin-bar-active .site-header {
  top: 42px !important;
}

.admin-bar-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 100%;
  padding: 0 18px;
  gap: 12px;
}

.admin-bar-left, .admin-bar-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.admin-bar-brand {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: #FFFFFF;
  text-decoration: none;
  font-size: 0.8rem;
  font-weight: 800;
  letter-spacing: 0.5px;
}

.admin-bar-divider {
  width: 1px;
  height: 16px;
  background: rgba(255, 255, 255, 0.15);
}

.admin-bar-user {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: #94A3B8;
  font-size: 0.78rem;
  font-weight: 600;
}

.admin-role-badge {
  background: rgba(14, 165, 233, 0.2);
  color: #38BDF8;
  font-size: 0.65rem;
  font-weight: 800;
  padding: 2px 6px;
  border-radius: 4px;
  border: 1px solid rgba(14, 165, 233, 0.3);
}

/* Edit Mode Toggle Switch */
.live-edit-switch-wrap {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.06);
  padding: 4px 12px;
  border-radius: 9999px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  cursor: pointer;
  transition: all 0.2s;
  user-select: none;
}

.live-edit-switch-wrap:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(14, 165, 233, 0.4);
}

.live-edit-switch-label {
  font-size: 0.76rem;
  font-weight: 700;
  color: #E2E8F0;
  letter-spacing: 0.5px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin: 0;
  cursor: pointer;
}

/* Custom pure CSS switch toggle */
.live-toggle-slot {
  position: relative;
  display: inline-block;
  width: 32px;
  height: 18px;
}

.live-toggle-input {
  opacity: 0;
  width: 0;
  height: 0;
  position: absolute;
}

.live-toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #334155;
  transition: .3s;
  border-radius: 18px;
}

.live-toggle-slider:before {
  position: absolute;
  content: "";
  height: 12px;
  width: 12px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .3s;
  border-radius: 50%;
}

.live-toggle-input:checked + .live-toggle-slider {
  background-color: #0284C7;
}

.live-toggle-input:checked + .live-toggle-slider:before {
  transform: translateX(14px);
}

.live-edit-status-tag {
  font-size: 0.7rem;
  font-weight: 800;
  padding: 2px 7px;
  border-radius: 4px;
  background: #334155;
  color: #94A3B8;
  letter-spacing: 0.5px;
  transition: all 0.2s;
}

body.admin-edit-active .live-edit-status-tag {
  background: #10B981;
  color: #FFFFFF;
}

/* Admin Bar Action Buttons */
.admin-bar-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 11px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 6px;
  color: #E2E8F0;
  font-size: 0.76rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.18s;
}

.admin-bar-btn:hover {
  background: var(--admin-primary, #0284C7);
  color: #FFFFFF;
  border-color: var(--admin-primary, #0284C7);
}

.admin-bar-btn-logout:hover {
  background: #DC2626 !important;
  border-color: #EF4444 !important;
}

/* ===================================================
   LIVE EDIT ACTIVE STYLES (When switch is ON)
   =================================================== */
body.admin-edit-active .live-editable-wrap {
  position: relative !important;
  outline: 2px dashed rgba(2, 132, 199, 0.65) !important;
  outline-offset: 4px !important;
  transition: outline 0.22s ease, outline-color 0.22s ease, box-shadow 0.25s ease !important;
}

body.admin-edit-active .live-editable-wrap:hover {
  outline: 2px solid #FFB800 !important;
  outline-offset: 4px !important;
  box-shadow: 0 0 16px rgba(255, 184, 0, 0.8), 0 0 32px rgba(255, 184, 0, 0.35) !important;
}

/* When hovering an inner child editable wrap, keep the outer wrap neutral */
body.admin-edit-active .live-editable-wrap:has(.live-editable-wrap:hover) {
  outline: 2px dashed rgba(2, 132, 199, 0.65) !important;
  outline-offset: 4px !important;
  box-shadow: none !important;
}

/* Full-width hero sections keep neon outline inset inside viewport */
body.admin-edit-active section.about-hero-section.live-editable-wrap,
body.admin-edit-active section.hero-section.live-editable-wrap,
body.admin-edit-active section.biz-hero-section.live-editable-wrap,
body.admin-edit-active section.sus-hero-section.live-editable-wrap {
  outline-offset: -4px !important;
}
body.admin-edit-active section.about-hero-section.live-editable-wrap:hover,
body.admin-edit-active section.hero-section.live-editable-wrap:hover,
body.admin-edit-active section.biz-hero-section.live-editable-wrap:hover,
body.admin-edit-active section.sus-hero-section.live-editable-wrap:hover {
  outline-offset: -4px !important;
  box-shadow: inset 0 0 22px rgba(255, 184, 0, 0.65) !important;
}

/* Sections switched OFF: hidden from visitors, still shown to the signed-in admin
   (greyed out) so the Live Edit badge that switches them back on stays reachable. */
.live-section-off {
  position: relative;
  opacity: 0.45;
  filter: grayscale(0.85);
  transition: opacity 0.2s ease, filter 0.2s ease;
}
.live-section-off:hover {
  opacity: 0.8;
  filter: grayscale(0.3);
}
.live-section-off-flag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
  padding: 7px 15px;
  border-radius: 999px;
  background: rgba(239, 68, 68, 0.16);
  border: 1px dashed rgba(248, 113, 113, 0.9);
  color: #FCA5A5;
  font-size: 0.8rem;
  font-weight: 600;
  letter-spacing: 0.2px;
  filter: grayscale(0);
}
.live-section-off-flag strong { color: #FECACA; }

/* Floating Live Edit Trigger Badges */
.live-edit-badge {
  display: none;
  position: absolute;
  top: 14px;
  right: 18px;
  z-index: 1000;
  background: linear-gradient(135deg, #0284C7 0%, #2563EB 100%);
  color: #FFFFFF !important;
  border: 1px solid rgba(255, 255, 255, 0.4);
  padding: 8px 16px;
  border-radius: 9999px;
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.3px;
  box-shadow: 0 6px 20px rgba(2, 132, 199, 0.45);
  cursor: pointer;
  align-items: center;
  gap: 6px;
  text-decoration: none !important;
  transition: transform 0.18s, box-shadow 0.18s, background 0.18s;
}

.live-edit-badge:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(2, 132, 199, 0.6);
  background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
  color: #FFFFFF !important;
}

body.admin-edit-active .live-edit-badge {
  display: inline-flex !important;
}

.live-edit-badge.badge-edit-hero {
  top: 96px !important;
  right: 24px !important;
}

.live-edit-badge.badge-edit-stats {
  top: -18px !important;
  right: 20px !important;
}

.live-edit-badge.badge-edit-pill {
  top: -16px !important;
  right: 24px !important;
}

.live-edit-badge.badge-edit-cta {
  top: 18px !important;
  right: 20px !important;
}

.live-edit-badge.badge-edit-footer {
  top: 14px !important;
  right: 15px !important;
  background: linear-gradient(135deg, #0284C7 0%, #1E40AF 100%) !important;
}

.live-edit-badge.badge-edit-footer-nav {
  top: 14px !important;
  right: 185px !important;
  background: linear-gradient(135deg, #10B981 0%, #059669 100%) !important;
}

.live-edit-badge.badge-edit-footer-brand {
  top: 14px !important;
  right: 15px !important;
  background: linear-gradient(135deg, #8B5CF6 0%, #6D28D9 100%) !important;
}

.live-edit-badge.badge-edit-leadership {
  top: 6px !important;
  right: 112px !important;
  z-index: 20 !important;
  background: linear-gradient(135deg, #0D9488 0%, #047857 100%) !important;
}

.live-edit-badge.badge-edit-executives {
  top: -18px !important;
  right: 12px !important;
  z-index: 25 !important;
  background: linear-gradient(135deg, #0EA5E9 0%, #0369A1 100%) !important;
}

.live-edit-badge.badge-edit-key-team {
  top: -42px !important;
  right: 0px !important;
  z-index: 20 !important;
  background: linear-gradient(135deg, #D97706 0%, #B45309 100%) !important;
}

.live-edit-badge.badge-edit-about {
  top: 0px !important;
  right: 0px !important;
  z-index: 20 !important;
  background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%) !important;
}

.live-edit-badge.badge-edit-about-timeline {
  top: -42px !important;
  right: 0px !important;
  z-index: 20 !important;
  background: linear-gradient(135deg, #6366F1 0%, #4338CA 100%) !important;
}

.live-edit-badge.badge-edit-about-vision {
  top: 0px !important;
  right: 0px !important;
  z-index: 20 !important;
  background: linear-gradient(135deg, #0891B2 0%, #0E7490 100%) !important;
}

.live-edit-badge.badge-edit-conduct {
  top: -48px !important;
  right: 0px !important;
  z-index: 20 !important;
  background: linear-gradient(135deg, #E11D48 0%, #BE123C 100%) !important;
}

.live-edit-badge.badge-edit-sus-esg,
.live-edit-badge.badge-edit-sus-clean,
.live-edit-badge.badge-edit-sus-talents,
.live-edit-badge.badge-edit-sus-sec {
  top: 18px !important;
  right: 20px !important;
  background: linear-gradient(135deg, #16A34A 0%, #15803D 100%) !important;
}

.live-edit-badge.badge-edit-biz-tech {
  top: -42px !important;
  right: 0px !important;
  background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
}

.live-edit-badge.badge-edit-biz-serv {
  top: 18px !important;
  right: 20px !important;
  background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
}

.live-edit-badge.badge-edit-contact {
  top: -42px !important;
  right: 0px !important;
  background: linear-gradient(135deg, #EA580C 0%, #C2410C 100%) !important;
}

.live-edit-badge.badge-edit-biz,
.live-edit-badge.badge-edit-media,
.live-edit-badge.badge-edit-sus,
.live-edit-badge.badge-edit-tech-intro,
.live-edit-badge.badge-edit-tech-feat,
.live-edit-badge.badge-edit-biz-services {
  top: 24px !important;
  right: 24px !important;
}

/* Repeatable Card/Item Builder inside Live Edit Modals */
.vnchip-repeater-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.vnchip-repeater-item {
  background: #F8FAFC;
  border: 1px solid #CBD5E1;
  border-radius: 10px;
  padding: 12px 14px;
}

.vnchip-repeater-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
  padding-bottom: 6px;
  border-bottom: 1px dashed #CBD5E1;
}

.vnchip-repeater-num {
  font-size: 0.78rem;
  font-weight: 800;
  color: #0F172A;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

.vnchip-repeater-actions {
  display: inline-flex;
  gap: 5px;
}

.vnchip-mini-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  padding: 4px 8px;
  font-size: 0.72rem;
  font-weight: 700;
  border-radius: 6px;
  border: 1px solid #CBD5E1;
  background: #FFFFFF;
  color: #334155;
  cursor: pointer;
  transition: all 0.15s;
}

.vnchip-mini-btn:hover {
  background: #F1F5F9;
  border-color: #94A3B8;
}

.vnchip-mini-btn.danger {
  color: #DC2626;
  border-color: #FECACA;
}

.vnchip-mini-btn.danger:hover {
  background: #FEE2E2;
  border-color: #F87171;
}

/* ===================================================
   CUSTOM MODAL STYLING (Zero external dependencies)
   =================================================== */
.vnchip-live-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 9999999;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.vnchip-live-modal.show {
  display: flex !important;
}

.vnchip-modal-backdrop {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(15, 23, 42, 0.75);
  backdrop-filter: blur(4px);
}

.vnchip-modal-dialog {
  position: relative;
  width: 100%;
  max-width: 680px;
  background: #FFFFFF;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
  animation: modalPopIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  border: 1px solid #E2E8F0;
  font-family: 'Inter', -apple-system, sans-serif;
  color: #0F172A;
}

@keyframes modalPopIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

.vnchip-modal-header {
  padding: 18px 24px;
  background: #0F172A;
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.vnchip-modal-title {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  gap: 8px;
}

.vnchip-modal-close {
  background: transparent;
  border: none;
  color: #94A3B8;
  font-size: 1.3rem;
  cursor: pointer;
  padding: 0;
  line-height: 1;
}

.vnchip-modal-close:hover {
  color: #FFFFFF;
}

.vnchip-modal-body {
  padding: 24px;
  max-height: 75vh;
  overflow-y: auto;
}

.vnchip-modal-footer {
  padding: 16px 24px;
  background: #F8FAFC;
  border-top: 1px solid #E2E8F0;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
}

/* Modal Tab Navigation */
/* Tabs wrap onto a second row instead of scrolling sideways: a tab parked off-screen
   behind a scrollbar is a tab nobody finds. */
.vnchip-modal-tabs {
  display: flex;
  flex-wrap: wrap;
  background: #F1F5F9;
  border-bottom: 1px solid #CBD5E1;
  padding: 0 16px;
  gap: 4px 2px;
}

.vnchip-tab-link {
  padding: 11px 16px;
  font-size: 0.82rem;
  font-weight: 700;
  color: #64748B;
  background: transparent;
  border: none;
  border-bottom: 2px solid transparent;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
}

.vnchip-tab-link:hover {
  color: #0284C7;
  background: rgba(2, 132, 199, 0.05);
}

.vnchip-tab-link.active {
  color: #0284C7;
  border-bottom-color: #0284C7;
  background: #FFFFFF;
}

.vnchip-tab-pane {
  display: none;
}

.vnchip-tab-pane.active {
  display: block;
}

.vnchip-form-group {
  margin-bottom: 16px;
}

.vnchip-form-label {
  display: block;
  font-size: 0.8rem;
  font-weight: 700;
  color: #475569;
  margin-bottom: 6px;
}

.vnchip-form-input, .vnchip-form-textarea {
  width: 100%;
  padding: 9px 12px;
  border-radius: 8px;
  border: 1px solid #CBD5E1;
  font-size: 0.9rem;
  color: #0F172A;
  font-family: inherit;
  transition: border-color 0.2s, box-shadow 0.2s;
  box-sizing: border-box;
}

.vnchip-form-input:focus, .vnchip-form-textarea:focus {
  outline: none;
  border-color: #0284C7;
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}

.vnchip-btn {
  padding: 8px 18px;
  border-radius: 8px;
  font-size: 0.86rem;
  font-weight: 700;
  cursor: pointer;
  border: 1px solid transparent;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
}

.vnchip-btn-primary {
  background: #0284C7;
  color: #FFFFFF;
}

.vnchip-btn-primary:hover {
  background: #0369A1;
}

.vnchip-btn-secondary {
  background: #F1F5F9;
  color: #475569;
  border-color: #CBD5E1;
}

.vnchip-btn-secondary:hover {
  background: #E2E8F0;
}

/* Toast alert */
#live-edit-toast {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 99999999;
  background: #0F172A;
  color: #FFFFFF;
  border-radius: 10px;
  padding: 14px 22px;
  display: none;
  align-items: center;
  gap: 12px;
  font-size: 0.88rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
  border-left: 4px solid #10B981;
  animation: slideUpToast 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  font-family: 'Inter', -apple-system, sans-serif;
}

@keyframes slideUpToast {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Live Edit Ace Code Editor Styles */
.vnchip-ace-wrap {
  margin-top: 8px;
  margin-bottom: 14px;
}
.vnchip-ace-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #0B132B;
  color: #94A3B8;
  padding: 6px 12px;
  border-radius: 8px 8px 0 0;
  border: 1px solid #1E293B;
  border-bottom: none;
  font-size: 0.76rem;
  font-weight: 600;
  user-select: none;
}
.vnchip-ace-header .ace-badge-tag {
  color: #38BDF8;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.vnchip-ace-header .ace-badge-tag-pill {
  background: rgba(56, 189, 248, 0.15);
  color: #38BDF8;
  font-size: 0.68rem;
  padding: 1px 6px;
  border-radius: 4px;
  font-weight: 600;
  margin-left: 4px;
}
.vnchip-ace-header .ace-tools {
  display: flex;
  align-items: center;
  gap: 6px;
}
.vnchip-ace-header .ace-btn-tool {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #E2E8F0;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 0.72rem;
  cursor: pointer;
  transition: all 0.15s ease;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  line-height: 1.4;
}
.vnchip-ace-header .ace-btn-tool:hover {
  background: rgba(255, 255, 255, 0.2);
  color: #FFFFFF;
}
.vnchip-ace-container {
  width: 100%;
  border-radius: 0 0 8px 8px;
  border: 1px solid #1E293B;
  font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', 'Courier New', monospace !important;
  font-size: 13.5px !important;
  line-height: 1.55 !important;
}
.vnchip-ace-footer-hint {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.72rem;
  color: #64748B;
  margin-top: 4px;
  padding: 0 4px;
}
.vnchip-ace-footer-hint kbd {
  background: #E2E8F0;
  color: #0F172A;
  padding: 1px 4px;
  border-radius: 3px;
  font-size: 0.7rem;
}

@media (max-width: 991px) {
  .admin-bar-user, .admin-bar-btn span, .admin-bar-divider {
    display: none !important;
  }
}

/* ===================================================
   LIVE EDIT: Design/IP Service items + keyword tooltips
   =================================================== */
.live-svc-pen {
  display: none;
  align-items: center;
  gap: 5px;
  margin-left: auto;
  margin-right: 14px;
  padding: 5px 11px;
  border-radius: 9999px;
  border: 1px solid rgba(255, 255, 255, 0.4);
  background: linear-gradient(135deg, #F25B22 0%, #C2410C 100%);
  color: #FFFFFF !important;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.3px;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(242, 91, 34, 0.35);
  transition: transform 0.15s, box-shadow 0.15s;
  font-family: 'Inter', -apple-system, sans-serif;
}
body.admin-edit-active .live-svc-pen { display: inline-flex; }
.live-svc-pen:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(242, 91, 34, 0.5); }

.live-tip-pen {
  display: none;
  position: absolute;
  top: -12px;
  right: -10px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #0F172A;
  color: #FFFFFF;
  font-size: 0.6rem;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border: 2px solid #FFFFFF;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  z-index: 5;
  transition: transform 0.15s, background 0.15s;
}
body.admin-edit-active .tuong-tac .live-tip-pen { display: inline-flex; }
.live-tip-pen:hover { background: #F25B22; transform: scale(1.12); }
body.admin-edit-active .tuong-tac { outline: 1px dashed rgba(242, 91, 34, 0.45); outline-offset: 2px; }

/* keyword panel under the editor */
.svc-kw-list { display: flex; flex-wrap: wrap; gap: 8px; }
.svc-kw-empty { font-size: 0.8rem; color: #64748B; }
.svc-kw-chip {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 5px 6px 5px 10px; border-radius: 9999px;
  border: 1px solid #FDBA74; background: #FFF7ED; font-size: 0.78rem; color: #0F172A;
}
.svc-kw-chip.missing { border-color: #CBD5E1; background: #F8FAFC; }
.svc-kw-dot { width: 8px; height: 8px; border-radius: 50%; background: #16A34A; flex: none; }
.svc-kw-chip.missing .svc-kw-dot { background: #94A3B8; }
.svc-kw-name { font-weight: 700; color: #C2410C; }
.svc-kw-chip.missing .svc-kw-name { color: #475569; }
.svc-kw-state { color: #64748B; font-size: 0.72rem; }
.svc-kw-btn {
  border: 0; border-radius: 9999px; padding: 4px 10px; font-size: 0.72rem; font-weight: 700;
  background: #F25B22; color: #FFFFFF; cursor: pointer; display: inline-flex; gap: 4px; align-items: center;
}
.svc-kw-chip.missing .svc-kw-btn { background: #0284C7; }
.svc-kw-btn:hover { filter: brightness(0.92); }

.svc-sel-bar {
  display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
  padding: 10px 12px; margin-top: 10px; border-radius: 10px;
  background: #FFFBEB; border: 1px dashed #FCD34D; font-size: 0.8rem; color: #78350F;
}
.svc-sel-bar .vnchip-btn { padding: 6px 12px; font-size: 0.78rem; }
.svc-sel-bar .vnchip-btn[disabled] { opacity: 0.45; cursor: not-allowed; }
.svc-dict-select { padding: 6px 10px; border-radius: 8px; border: 1px solid #CBD5E1; font-size: 0.8rem; max-width: 240px; }

#modalLiveEditTooltip { z-index: 10000000; }
#modalLiveEditServiceItem .vnchip-modal-body, #modalLiveEditTooltip .vnchip-modal-body { max-height: 80vh; }
.svc-img-thumb { width: 160px; height: 90px; border-radius: 10px; object-fit: cover; background: #0A1120; border: 1px solid #334155; flex: none; }

.live-edit-badge.badge-edit-program {
  top: 0px !important;
  right: 0px !important;
  background: linear-gradient(135deg, #16A34A 0%, #15803D 100%) !important;
}
body.admin-edit-active #live-program-detail.live-editable-wrap { position: relative; }

/* ===================================================
   LIVE TEXT / LIVE IMAGE pencils (any data-lt / data-lt-img element)
   =================================================== */
.live-lt-pen {
  display: none;
  position: absolute;
  top: -10px;
  right: -10px;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  border: 2px solid #FFFFFF;
  background: #F59E0B;
  color: #FFFFFF;
  font-size: 0.62rem;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.35);
  z-index: 60;
  padding: 0;
  line-height: 1;
  transition: transform 0.15s, background 0.15s;
  text-transform: none;
  letter-spacing: 0;
  font-weight: 400;
}
.live-lt-pen--img { background: #0EA5E9; top: 8px; right: 8px; width: 26px; height: 26px; font-size: 0.72rem; }
.live-lt-pen:hover { transform: scale(1.15); background: #F25B22; }
body.admin-edit-active .live-lt, body.admin-edit-active .live-lt-img-wrap { position: relative; }
body.admin-edit-active .live-lt-pen { display: inline-flex; }
body.admin-edit-active .live-lt { outline: 1px dashed rgba(245, 158, 11, 0.55); outline-offset: 3px; }
body.admin-edit-active .live-lt-img-wrap { outline: 1px dashed rgba(14, 165, 233, 0.6); outline-offset: 2px; }
.media-hero-bg .live-lt-pen--img { top: 14px; right: 14px; filter: brightness(1.35); }
/* inline text slots (span inside a button/link) keep their flow */
body.admin-edit-active span.live-lt { display: inline-block; }
.live-edit-badge.badge-edit-tech-ai { top: 24px !important; right: 24px !important; }
.live-edit-badge.badge-edit-biz-projects { top: -42px !important; right: 0 !important; background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important; }

/* "Mở trong Admin" badge for blocks managed by an admin screen */
.live-edit-badge.live-admin-badge {
  top: -16px !important;
  right: 12px !important;
  background: linear-gradient(135deg, #475569 0%, #1E293B 100%) !important;
  z-index: 70 !important;
}
</style>

<!-- 3. Modal Quick Edit: 3 Golden Stats -->
<div class="vnchip-live-modal" id="modalLiveEditStats">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveEditStats')"></div>
  <div class="vnchip-modal-dialog">
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title">
        <i class="fa-solid fa-sliders text-primary"></i> Chỉnh sửa 3 Chỉ số Vàng Trang chủ
      </h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveEditStats')">&times;</button>
    </div>
    <div class="vnchip-modal-body">
      <form id="formLiveEditStats">
        
        <!-- Stat 1 -->
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 14px;">
          <div style="font-weight: 700; color: #0284C7; font-size: 0.85rem; margin-bottom: 10px;">
            <i class="fa-solid fa-1 me-1"></i> Chỉ số 1 (Năm kinh nghiệm)
          </div>
          <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px; margin-bottom: 8px;">
            <div>
              <label class="vnchip-form-label">Con số</label>
              <input type="text" id="stat_years_number" name="stat_years_number" class="vnchip-form-input" style="font-weight: 800; color: #0284C7;" value="<?= e(Setting::get('stat_years_number', '6+')) ?>">
            </div>
            <div>
              <label class="vnchip-form-label">Đơn vị / Nhãn</label>
              <input type="text" id="stat_years_label" name="stat_years_label" class="vnchip-form-input" value="<?= e(Setting::get('stat_years_label', 'years')) ?>">
            </div>
          </div>
          <div>
            <label class="vnchip-form-label">Mô tả ngắn</label>
            <textarea id="stat_years_desc" name="stat_years_desc" rows="2" class="vnchip-form-textarea"><?= e(Setting::get('stat_years_desc', 'Deep expertise in physical design and implementation, custom circuit layout, and system design - from 65nm to 3nm nodes')) ?></textarea>
          </div>
        </div>

        <!-- Stat 2 -->
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 14px;">
          <div style="font-weight: 700; color: #10B981; font-size: 0.85rem; margin-bottom: 10px;">
            <i class="fa-solid fa-2 me-1"></i> Chỉ số 2 (Dự án ASIC Tapeout)
          </div>
          <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px; margin-bottom: 8px;">
            <div>
              <label class="vnchip-form-label">Con số</label>
              <input type="text" id="stat_asic_number" name="stat_asic_number" class="vnchip-form-input" style="font-weight: 800; color: #10B981;" value="<?= e(Setting::get('stat_asic_number', '40+')) ?>">
            </div>
            <div>
              <label class="vnchip-form-label">Đơn vị / Nhãn</label>
              <input type="text" id="stat_asic_label" name="stat_asic_label" class="vnchip-form-input" value="<?= e(Setting::get('stat_asic_label', 'ASIC')) ?>">
            </div>
          </div>
          <div>
            <label class="vnchip-form-label">Mô tả ngắn</label>
            <textarea id="stat_asic_desc" name="stat_asic_desc" rows="2" class="vnchip-form-textarea"><?= e(Setting::get('stat_asic_desc', 'Successful silicon solutions delivered across AI compute, mobile SOC, and 5G signal processing')) ?></textarea>
          </div>
        </div>

        <!-- Stat 3 -->
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
          <div style="font-weight: 700; color: #F59E0B; font-size: 0.85rem; margin-bottom: 10px;">
            <i class="fa-solid fa-3 me-1"></i> Chỉ số 3 (Chips / Nhân sự kỹ sư)
          </div>
          <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px; margin-bottom: 8px;">
            <div>
              <label class="vnchip-form-label">Con số</label>
              <input type="text" id="stat_chips_number" name="stat_chips_number" class="vnchip-form-input" style="font-weight: 800; color: #F59E0B;" value="<?= e(Setting::get('stat_chips_number', '5M+')) ?>">
            </div>
            <div>
              <label class="vnchip-form-label">Đơn vị / Nhãn</label>
              <input type="text" id="stat_chips_label" name="stat_chips_label" class="vnchip-form-input" value="<?= e(Setting::get('stat_chips_label', 'chips')) ?>">
            </div>
          </div>
          <div>
            <label class="vnchip-form-label">Mô tả ngắn</label>
            <textarea id="stat_chips_desc" name="stat_chips_desc" rows="2" class="vnchip-form-textarea"><?= e(Setting::get('stat_chips_desc', 'Delivered annually to global partners, testing and post-silicon supports')) ?></textarea>
          </div>
        </div>

      </form>
    </div>
    <div class="vnchip-modal-footer">
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveEditStats')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnSaveLiveStats">
        <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi ngay
      </button>
    </div>
  </div>
</div>

<!-- 4. Modal Quick Edit: Pill Banner -->
<div class="vnchip-live-modal" id="modalLiveEditPill">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveEditPill')"></div>
  <div class="vnchip-modal-dialog">
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title">
        <i class="fa-solid fa-bullhorn text-info"></i> Chỉnh sửa Thanh Pill Banner Trang chủ
      </h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveEditPill')">&times;</button>
    </div>
    <div class="vnchip-modal-body">
      <div class="vnchip-form-group">
        <label class="vnchip-form-label">Nội dung thông điệp chạy chữ nổi (Pill Banner):</label>
        <textarea id="inputLivePillText" rows="4" class="vnchip-form-textarea" style="line-height: 1.6;"><?= e(Setting::get('homepage_pill_banner', 'We drive progress through training, research collaboration, and technology transfer, aligning with international standards and evolving industry trends in the global semiconductor landscape.')) ?></textarea>
      </div>
      <div style="font-size: 0.8rem; color: #64748B;">
        <i class="fa-solid fa-circle-info text-primary me-1"></i>
        Thông điệp này xuất hiện ngay dưới Video nền Trang chủ để khẳng định vị thế và năng lực bán dẫn của VNCHIP.
      </div>
    </div>
    <div class="vnchip-modal-footer">
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveEditPill')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnSaveLivePill">
        <i class="fa-solid fa-floppy-disk"></i> Lưu Pill Banner
      </button>
    </div>
  </div>
</div>

<!-- 5. Modal Quick Edit: Call to Action (CTA Banner) -->
<div class="vnchip-live-modal" id="modalLiveEditCTA">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveEditCTA')"></div>
  <div class="vnchip-modal-dialog">
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title">
        <i class="fa-solid fa-bullhorn text-warning"></i> Chỉnh sửa Banner Call To Action (CTA)
      </h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveEditCTA')">&times;</button>
    </div>
    <div class="vnchip-modal-body">
      <form id="formLiveEditCTA">
        <div class="vnchip-form-group">
          <label class="vnchip-form-label">Tiêu đề Banner Call to Action:</label>
          <input type="text" id="inputLiveCtaTitle" name="cta_title" class="vnchip-form-input" style="font-weight: 800; font-size: 0.95rem;" value="<?= e(Setting::get('cta_title', 'NEED SPECIALIZED SEMICONDUCTOR DESIGN SUPPORT?')) ?>">
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;" class="vnchip-form-group">
          <div>
            <label class="vnchip-form-label">Chữ trên nút bấm (Button Text):</label>
            <input type="text" id="inputLiveCtaBtnText" name="cta_button_text" class="vnchip-form-input" value="<?= e(Setting::get('cta_button_text', 'Talk to our experts')) ?>">
          </div>
          <div>
            <label class="vnchip-form-label">Đường dẫn liên kết (Button URL):</label>
            <input type="text" id="inputLiveCtaBtnUrl" name="cta_button_url" class="vnchip-form-input" value="<?= e(Setting::get('cta_button_url', 'talk-to-us.php')) ?>" placeholder="talk-to-us.php hoặc https://...">
          </div>
        </div>

        <!-- Optional CTA Background Image Upload -->
        <div style="background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 10px; padding: 12px; margin-bottom: 12px;">
          <label class="vnchip-form-label" style="margin-bottom: 8px;">
            <i class="fa-solid fa-image text-primary me-1"></i> Ảnh nền Khối CTA (Tải ảnh từ máy tính để thay thế):
          </label>
          <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
            <div style="width: 140px; height: 56px; background: #0A1120; border-radius: 8px; display: flex; align-items: center; justify-content: center; padding: 4px; overflow: hidden; flex-shrink: 0; border: 1px solid #334155;">
              <?php 
                $ctaCurrentBg = Setting::get('cta_bg_image', '');
                $ctaPreviewSrc = !empty($ctaCurrentBg) ? asset($ctaCurrentBg) : "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='140' height='56' viewBox='0 0 140 56'><rect width='140' height='56' fill='%23062A4D'/><text x='70' y='32' fill='%2394A3B8' font-size='11' text-anchor='middle' font-family='sans-serif'>Mặc định</text></svg>";
              ?>
              <img id="preview_cta_bg_img" src="<?= $ctaPreviewSrc ?>" style="max-width: 100%; max-height: 100%; object-fit: cover;">
            </div>
            <div style="flex: 1; min-width: 200px;">
              <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px;">
                <input type="file" id="file_cta_bg" accept="image/*" style="display: none;" onchange="handleLiveImageUpload(this, 'inputLiveCtaBgImg', 'preview_cta_bg_img', 'cta_bg_image', 'live-cta-box')">
                <button type="button" class="vnchip-btn vnchip-btn-primary" onclick="document.getElementById('file_cta_bg').click()" style="padding: 6px 14px; font-size: 0.8rem;">
                  <i class="fa-solid fa-cloud-arrow-up"></i> Tải ảnh nền mới lên (Upload)
                </button>
              </div>
              <input type="text" id="inputLiveCtaBgImg" name="cta_bg_image" class="vnchip-form-input" style="font-size: 0.78rem; padding: 6px 10px; font-family: monospace;" value="<?= e(Setting::get('cta_bg_image', '')) ?>" placeholder="Để trống nếu dùng nền mặc định" oninput="updateLivePreview(this.value, 'preview_cta_bg_img')">
            </div>
          </div>
        </div>

        <div style="font-size: 0.8rem; color: #64748B;">
          <i class="fa-solid fa-circle-info text-primary me-1"></i>
          Khối CTA xuất hiện ở cuối trang chủ và tất cả các trang nội dung để thúc đẩy khách hàng liên hệ chuyên gia.
        </div>
      </form>
    </div>
    <div class="vnchip-modal-footer">
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveEditCTA')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnSaveLiveCTA">
        <i class="fa-solid fa-floppy-disk"></i> Lưu Call to Action
      </button>
    </div>
  </div>
</div>

<!-- 6. Modal Quick Edit: Full Footer (All Components With Image Uploaders) -->
<div class="vnchip-live-modal" id="modalLiveEditFooter">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveEditFooter')"></div>
  <div class="vnchip-modal-dialog" style="max-width: 820px;">
    
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title">
        <i class="fa-solid fa-shoe-prints text-info"></i> Quản lý & Chỉnh sửa Tất cả thành phần Footer
      </h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveEditFooter')">&times;</button>
    </div>

    <!-- Modal Tab Bar -->
    <div class="vnchip-modal-tabs">
      <button type="button" class="vnchip-tab-link active" data-tab="footer-tab-nav" onclick="switchModalTab('modalLiveEditFooter', 'footer-tab-nav')">
        <i class="fa-solid fa-list-check"></i> 3 Cột Menu Điều hướng
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="footer-tab-contact" onclick="switchModalTab('modalLiveEditFooter', 'footer-tab-contact')">
        <i class="fa-solid fa-building"></i> Địa chỉ & Liên hệ
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="footer-tab-partners" onclick="switchModalTab('modalLiveEditFooter', 'footer-tab-partners')">
        <i class="fa-solid fa-handshake"></i> Logo Đối tác & Phân nhánh
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="footer-tab-social" onclick="switchModalTab('modalLiveEditFooter', 'footer-tab-social')">
        <i class="fa-solid fa-share-nodes"></i> Logo Chính & MXH
      </button>
    </div>

    <div class="vnchip-modal-body">
      <form id="formLiveEditFooter">
        
        <!-- Tab 1: Navigation Columns Markup -->
        <div class="vnchip-tab-pane active" id="footer-tab-nav">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 12px;">
            <div style="font-weight: 700; color: #0284C7; font-size: 0.85rem; margin-bottom: 6px;">
              <i class="fa-solid fa-code me-1"></i> Mã HTML Cấu trúc 3 Cột Menu Điều Hướng Footer
            </div>
            <div style="font-size: 0.8rem; color: #64748B; margin-bottom: 10px;">
              Bao gồm Cột 1 (SUSTAINABLE), Cột 2 (DESIGN SOLUTIONS với 2 nhóm Design Service & IP Service), và Cột 3 (QUICK LINKS). Bạn có thể đổi tên cột, thêm bớt link tùy thích.
            </div>
            
            <textarea id="inputLiveFooterNav" name="section_footer_nav" rows="11" class="vnchip-form-textarea" 
                      style="font-family: 'Consolas', 'Courier New', monospace; font-size: 0.82rem; background: #0F172A; color: #38BDF8; line-height: 1.5; tab-size: 2;"><?= htmlspecialchars(Setting::get('section_footer_nav', $defaultFooterNavMarkup), ENT_QUOTES, 'UTF-8') ?></textarea>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; flex-wrap: gap; gap: 8px;">
              <a href="<?= admin_url('page-sections.php?tab=footer') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="font-size: 0.78rem;">
                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở Code Editor Ace trong Admin (Có tô màu cú pháp)
              </a>
              <button type="button" class="vnchip-btn vnchip-btn-secondary" id="btnResetFooterNav" style="font-size: 0.78rem;">
                <i class="fa-solid fa-rotate-left me-1"></i> Nạp lại mẫu gốc 3 cột
              </button>
            </div>
          </div>
        </div>

        <!-- Tab 2: Headquarters, Contact, Copyright -->
        <div class="vnchip-tab-pane" id="footer-tab-contact">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 14px;">
            <div style="font-weight: 700; color: #0284C7; font-size: 0.85rem; margin-bottom: 10px;">
              <i class="fa-solid fa-building me-1"></i> Thông tin Trụ sở & Liên hệ
            </div>
            <div class="vnchip-form-group">
              <label class="vnchip-form-label">Địa chỉ trụ sở R&D (Việt Nam):</label>
              <textarea id="inputLiveFooterAddress" name="site_address_vn" rows="2" class="vnchip-form-textarea"><?= e(Setting::get('site_address_vn', '190 Doc Lap Street, Phu Tho Hoa Ward, Ho Chi Minh City, Vietnam')) ?></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
              <div>
                <label class="vnchip-form-label">Email liên hệ:</label>
                <input type="email" id="inputLiveFooterEmail" name="site_email" class="vnchip-form-input" value="<?= e(Setting::get('site_email', 'institute@vnchip-inc.com')) ?>">
              </div>
              <div>
                <label class="vnchip-form-label">Số điện thoại Hotline:</label>
                <input type="text" id="inputLiveFooterPhone" name="site_phone" class="vnchip-form-input" value="<?= e(Setting::get('site_phone', '+84 - 28 268 268 1058')) ?>">
              </div>
            </div>
          </div>

          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
            <label class="vnchip-form-label"><i class="fa-solid fa-copyright me-1"></i> Dòng chữ Bản quyền Chân trang (Footer Copyright - Hỗ trợ mã HTML &copy;, &trade;, link):</label>
            <textarea id="inputLiveFooterCopyright" name="footer_copyright" rows="2" class="vnchip-form-textarea" style="line-height: 1.5;"><?= htmlspecialchars(Setting::get('footer_copyright', 'Design & Copyright &copy; 2026 by VNCHIP Technology JSC. All rights reserved.'), ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
        </div>

        <!-- Tab 3: Partner & Sub-brand Logos With Instant Upload & Replace -->
        <div class="vnchip-tab-pane" id="footer-tab-partners">
          
          <!-- Partner 1 (VNCHIP Technology / Labs) -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
            <div style="font-weight: 700; color: #10B981; font-size: 0.88rem; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
              <span><i class="fa-solid fa-1 me-1"></i> Logo Đối tác 1 (VNCHIP Technology / Labs)</span>
              <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; font-size: 0.72rem; padding: 3px 8px; border-radius: 4px;">Sub-brand 1</span>
            </div>
            
            <div class="vnchip-form-group">
              <label class="vnchip-form-label">Đường dẫn liên kết khi nhấp vào Logo (Website URL):</label>
              <input type="text" id="inputLiveFooterPartnerLink1" name="footer_partner_link_1" class="vnchip-form-input" value="<?= e(Setting::get('footer_partner_link_1', 'https://vnchip-inc.com/')) ?>">
            </div>

            <div style="background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 10px; padding: 12px;">
              <label class="vnchip-form-label" style="margin-bottom: 8px;">
                <i class="fa-solid fa-image text-primary me-1"></i> Ảnh Logo Đối tác 1 (Tải ảnh từ máy tính để thay thế):
              </label>
              <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
                <!-- Contrast Preview Box -->
                <div style="width: 140px; height: 56px; background: #0A1120; border-radius: 8px; display: flex; align-items: center; justify-content: center; padding: 4px; overflow: hidden; flex-shrink: 0; border: 1px solid #334155;">
                  <img id="preview_partner1_img" src="<?= asset(Setting::getOr('footer_partner_logo_1', 'images/logo_vnchip_main.png')) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                </div>
                
                <div style="flex: 1; min-width: 200px;">
                  <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px;">
                    <!-- Hidden File Input -->
                    <input type="file" id="file_partner1" accept="image/*" style="display: none;" onchange="handleLiveImageUpload(this, 'inputLiveFooterPartnerLogo1', 'preview_partner1_img', 'footer_partner_logo_1', 'live-footer-partner-img-1')">
                    
                    <!-- Upload Button -->
                    <button type="button" class="vnchip-btn vnchip-btn-primary" onclick="document.getElementById('file_partner1').click()" style="padding: 6px 14px; font-size: 0.8rem;">
                      <i class="fa-solid fa-cloud-arrow-up"></i> Tải ảnh mới lên (Upload)
                    </button>
                    <span id="status_inputLiveFooterPartnerLogo1" style="font-size: 0.78rem;"></span>
                  </div>
                  <!-- Path Input -->
                  <input type="text" id="inputLiveFooterPartnerLogo1" name="footer_partner_logo_1" class="vnchip-form-input" style="font-size: 0.78rem; padding: 6px 10px; font-family: monospace;" value="<?= e(Setting::get('footer_partner_logo_1', 'images/logo_vnchip_main.png')) ?>" oninput="updateLivePreview(this.value, 'preview_partner1_img')">
                </div>
              </div>
            </div>
          </div>

          <!-- Partner 2 (VNCHIP Institute) -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px;">
            <div style="font-weight: 700; color: #8B5CF6; font-size: 0.88rem; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
              <span><i class="fa-solid fa-2 me-1"></i> Logo Đối tác 2 (VNCHIP Institute)</span>
              <span class="badge" style="background: rgba(139, 92, 246, 0.15); color: #7C3AED; font-size: 0.72rem; padding: 3px 8px; border-radius: 4px;">Sub-brand 2</span>
            </div>
            
            <div class="vnchip-form-group">
              <label class="vnchip-form-label">Đường dẫn liên kết khi nhấp vào Logo (Website URL):</label>
              <input type="text" id="inputLiveFooterPartnerLink2" name="footer_partner_link_2" class="vnchip-form-input" value="<?= e(Setting::get('footer_partner_link_2', 'https://vnchip.edu.vn/')) ?>">
            </div>

            <div style="background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 10px; padding: 12px;">
              <label class="vnchip-form-label" style="margin-bottom: 8px;">
                <i class="fa-solid fa-image text-primary me-1"></i> Ảnh Logo Đối tác 2 (Tải ảnh từ máy tính để thay thế):
              </label>
              <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
                <!-- Contrast Preview Box -->
                <div style="width: 140px; height: 56px; background: #0A1120; border-radius: 8px; display: flex; align-items: center; justify-content: center; padding: 4px; overflow: hidden; flex-shrink: 0; border: 1px solid #334155;">
                  <img id="preview_partner2_img" src="<?= asset(Setting::getOr('footer_partner_logo_2', 'images/footer_logo2.png')) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                </div>
                
                <div style="flex: 1; min-width: 200px;">
                  <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px;">
                    <!-- Hidden File Input -->
                    <input type="file" id="file_partner2" accept="image/*" style="display: none;" onchange="handleLiveImageUpload(this, 'inputLiveFooterPartnerLogo2', 'preview_partner2_img', 'footer_partner_logo_2', 'live-footer-partner-img-2')">
                    
                    <!-- Upload Button -->
                    <button type="button" class="vnchip-btn vnchip-btn-primary" onclick="document.getElementById('file_partner2').click()" style="padding: 6px 14px; font-size: 0.8rem;">
                      <i class="fa-solid fa-cloud-arrow-up"></i> Tải ảnh mới lên (Upload)
                    </button>
                    <span id="status_inputLiveFooterPartnerLogo2" style="font-size: 0.78rem;"></span>
                  </div>
                  <!-- Path Input -->
                  <input type="text" id="inputLiveFooterPartnerLogo2" name="footer_partner_logo_2" class="vnchip-form-input" style="font-size: 0.78rem; padding: 6px 10px; font-family: monospace;" value="<?= e(Setting::get('footer_partner_logo_2', 'images/footer_logo2.png')) ?>" oninput="updateLivePreview(this.value, 'preview_partner2_img')">
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Tab 4: Main Logo With Instant Upload & Social Media -->
        <div class="vnchip-tab-pane" id="footer-tab-social">
          
          <!-- Main Footer Brand Logo With Upload -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
            <div style="font-weight: 700; color: #0284C7; font-size: 0.88rem; margin-bottom: 12px;">
              <i class="fa-solid fa-image me-1"></i> Logo Chính Chân trang (Footer Brand Logo)
            </div>
            
            <div style="background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 10px; padding: 12px;">
              <label class="vnchip-form-label" style="margin-bottom: 8px;">
                Tải ảnh Logo chính từ máy tính để thay thế ngay:
              </label>
              <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
                <!-- Contrast Preview Box -->
                <div style="width: 150px; height: 60px; background: #0A1120; border-radius: 8px; display: flex; align-items: center; justify-content: center; padding: 6px; overflow: hidden; flex-shrink: 0; border: 1px solid #334155;">
                  <img id="preview_main_logo_img" src="<?= asset(Setting::getOr('logo_footer', 'images/logo_vnchip_labs.png')) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                </div>
                
                <div style="flex: 1; min-width: 200px;">
                  <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px;">
                    <!-- Hidden File Input -->
                    <input type="file" id="file_main_logo" accept="image/*" style="display: none;" onchange="handleLiveImageUpload(this, 'inputLiveFooterLogo', 'preview_main_logo_img', 'logo_footer', 'live-footer-logo')">
                    
                    <!-- Upload Button -->
                    <button type="button" class="vnchip-btn vnchip-btn-primary" onclick="document.getElementById('file_main_logo').click()" style="padding: 6px 14px; font-size: 0.8rem;">
                      <i class="fa-solid fa-cloud-arrow-up"></i> Tải ảnh mới lên (Upload)
                    </button>
                    <span id="status_inputLiveFooterLogo" style="font-size: 0.78rem;"></span>
                  </div>
                  <!-- Path Input -->
                  <input type="text" id="inputLiveFooterLogo" name="logo_footer" class="vnchip-form-input" style="font-size: 0.78rem; padding: 6px 10px; font-family: monospace;" value="<?= e(Setting::get('logo_footer', 'images/logo_vnchip_labs.png')) ?>" oninput="updateLivePreview(this.value, 'preview_main_logo_img')">
                </div>
              </div>
            </div>
          </div>

          <!-- Social Media Links -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px;">
            <div style="font-weight: 700; color: #10B981; font-size: 0.88rem; margin-bottom: 12px;">
              <i class="fa-solid fa-share-nodes me-1"></i> Liên kết Mạng xã hội (Social Media)
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
              <div>
                <label class="vnchip-form-label"><i class="fa-brands fa-facebook text-primary me-1"></i> Facebook</label>
                <input type="text" id="inputLiveFooterFb" name="social_facebook" class="vnchip-form-input" value="<?= e(Setting::get('social_facebook', 'https://www.facebook.com/vnchipinstitute')) ?>">
              </div>
              <div>
                <label class="vnchip-form-label"><i class="fa-brands fa-linkedin text-info me-1"></i> LinkedIn</label>
                <input type="text" id="inputLiveFooterLi" name="social_linkedin" class="vnchip-form-input" value="<?= e(Setting::get('social_linkedin', 'https://www.linkedin.com/company/vnchip-technology-jsc/')) ?>">
              </div>
              <div>
                <label class="vnchip-form-label"><i class="fa-brands fa-youtube text-danger me-1"></i> YouTube</label>
                <input type="text" id="inputLiveFooterYt" name="social_youtube" class="vnchip-form-input" value="<?= e(Setting::get('social_youtube', 'https://www.youtube.com/@vnchiptechnology2362')) ?>">
              </div>
            </div>
          </div>
        </div>

      </form>
    </div>
    
    <div class="vnchip-modal-footer">
      <a href="<?= admin_url('page-sections.php?tab=footer') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="margin-right: auto;">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở Trình quản lý Footer trong Admin
      </a>
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveEditFooter')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnSaveLiveFooter">
        <i class="fa-solid fa-floppy-disk"></i> Lưu Tất cả Thay đổi Footer
      </button>
    </div>

  </div>
</div>

<!-- 7. Modal Quick Edit: Leadership & Message from Founder (about-us.php) -->
<div class="vnchip-live-modal" id="modalLiveEditLeadership">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveEditLeadership')"></div>
  <div class="vnchip-modal-dialog" style="max-width: 820px;">
    
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title">
        <i class="fa-solid fa-users-gear" style="color: #0D9488;"></i> Chỉnh sửa Mục Leadership & Message from Founder
      </h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveEditLeadership')">&times;</button>
    </div>

    <!-- Modal Tabs -->
    <div class="vnchip-modal-tabs">
      <button type="button" class="vnchip-tab-link active" data-tab="lead-tab-founder" onclick="switchModalTab('modalLiveEditLeadership', 'lead-tab-founder')">
        <i class="fa-solid fa-user-tie"></i> Thông điệp Sáng lập (Founder)
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="lead-tab-titles" onclick="switchModalTab('modalLiveEditLeadership', 'lead-tab-titles')">
        <i class="fa-solid fa-heading"></i> Tiêu đề & Giới thiệu
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="lead-tab-html" onclick="switchModalTab('modalLiveEditLeadership', 'lead-tab-html')">
        <i class="fa-solid fa-code"></i> Mã HTML Leadership
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="lead-tab-members" onclick="switchModalTab('modalLiveEditLeadership', 'lead-tab-members')">
        <i class="fa-solid fa-users"></i> Ban Lãnh đạo
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="lead-tab-keyteam" onclick="switchModalTab('modalLiveEditLeadership', 'lead-tab-keyteam')">
        <i class="fa-solid fa-people-group"></i> Khối Key Team
      </button>
    </div>

    <div class="vnchip-modal-body">
      <form id="formLiveEditLeadership">

        <!-- Tab 1: Founder Message -->
        <div class="vnchip-tab-pane active" id="lead-tab-founder">
          <div style="background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
            <div style="font-weight: 700; color: #0D9488; font-size: 0.9rem; margin-bottom: 12px;">
              <i class="fa-solid fa-camera me-1"></i> Ảnh chân dung Người sáng lập (Founder Standing Photo)
            </div>
            
            <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
              <!-- Contrast Preview Box -->
              <div style="width: 120px; height: 120px; background: #0A1120; border-radius: 10px; display: flex; align-items: center; justify-content: center; padding: 6px; overflow: hidden; flex-shrink: 0; border: 1px solid #334155;">
                <img id="preview_founder_photo_img" src="<?= asset(Setting::getOr('about_founder_photo', 'assets/images/founder_tri_standing.webp')) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
              </div>
              
              <div style="flex: 1; min-width: 220px;">
                <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px;">
                  <!-- Hidden File Input -->
                  <input type="file" id="file_founder_photo" accept="image/*" style="display: none;" onchange="handleLiveImageUpload(this, 'inputLiveFounderPhoto', 'preview_founder_photo_img', 'about_founder_photo', 'live-founder-img')">
                  
                  <!-- Upload Button -->
                  <button type="button" class="vnchip-btn vnchip-btn-primary" onclick="document.getElementById('file_founder_photo').click()" style="padding: 7px 16px; font-size: 0.82rem; background: linear-gradient(135deg, #0D9488 0%, #047857 100%);">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Tải ảnh mới lên (Upload & Thay thế)
                  </button>
                  <span id="status_inputLiveFounderPhoto" style="font-size: 0.78rem;"></span>
                </div>
                <label class="vnchip-form-label" style="font-size: 0.76rem; color: #64748B; margin-bottom: 4px;">Đường dẫn file ảnh:</label>
                <input type="text" id="inputLiveFounderPhoto" name="about_founder_photo" class="vnchip-form-input" style="font-size: 0.78rem; padding: 6px 10px; font-family: monospace;" value="<?= e(Setting::get('about_founder_photo', 'assets/images/founder_tri_standing.webp')) ?>" oninput="updateLivePreview(this.value, 'preview_founder_photo_img')">
              </div>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
            <div>
              <label class="vnchip-form-label">Tiêu đề khung thông điệp (Hỗ trợ HTML &lt;br&gt; ngắt dòng):</label>
              <textarea id="inputLiveFounderTitle" name="about_founder_title" rows="2" class="vnchip-form-textarea" style="line-height: 1.5; font-weight: 700;"><?= htmlspecialchars(Setting::get('about_founder_title', 'MESSAGE FROM<br>FOUNDER'), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div>
              <label class="vnchip-form-label">Tên & Chức vụ Người sáng lập (Alt Text):</label>
              <input type="text" id="inputLiveFounderName" name="about_founder_name" class="vnchip-form-input" value="<?= e(Setting::get('about_founder_name', 'Luc Duc Tri - Founder & CEO')) ?>">
            </div>
          </div>

          <div class="vnchip-form-group">
            <label class="vnchip-form-label">Lời phát biểu / Thông điệp của Người sáng lập:</label>
            <textarea id="inputLiveFounderQuote" name="about_founder_quote" rows="4" class="vnchip-form-textarea" style="line-height: 1.6;"><?= e(Setting::get('about_founder_quote', "Our mission has always been to empower aspiring engineers and professionals with the skills needed to thrive in the fast-paced world of Integrated Circuit (IC) design. Today, we remain committed to delivering innovative, high-impact design solutions while driving growth and excellence in Vietnam’s IC design industry.")) ?></textarea>
          </div>
        </div>

        <!-- Tab 2: Titles & Subtexts -->
        <div class="vnchip-tab-pane" id="lead-tab-titles">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 14px;">
            <div style="font-weight: 700; color: #0284C7; font-size: 0.85rem; margin-bottom: 10px;">
              <i class="fa-solid fa-users me-1"></i> Khối LEADERSHIP
            </div>
            <div class="vnchip-form-group">
              <label class="vnchip-form-label">Tiêu đề chính:</label>
              <input type="text" id="inputLiveLeadershipTitle" name="about_leadership_title" class="vnchip-form-input" value="<?= e(Setting::get('about_leadership_title', 'LEADERSHIP')) ?>">
            </div>
            <div class="vnchip-form-group">
              <label class="vnchip-form-label">Đoạn mô tả ngắn dưới thông điệp (Subtext):</label>
              <textarea id="inputLiveLeadershipSubtext" name="about_leadership_subtext" rows="2" class="vnchip-form-textarea"><?= e(Setting::get('about_leadership_subtext', 'Our focus is on driving innovation, enhancing performance, and addressing the critical challenges faced by the industry today in Vietnam.')) ?></textarea>
            </div>
          </div>

          <div style="font-size: 0.8rem; color: #64748B; background: #FFFBEB; border: 1px dashed #FCD34D; border-radius: 8px; padding: 10px 12px;">
            <i class="fa-solid fa-circle-info me-1" style="color: #D97706;"></i>
            Tiêu đề, mô tả và bật/tắt hiển thị của khối <strong>KEY TEAM</strong> nằm ở tab
            <a href="#" onclick="switchModalTab('modalLiveEditLeadership', 'lead-tab-keyteam'); return false;" style="color: #B45309; font-weight: 700;">Khối Key Team</a>.
          </div>
        </div>

        <!-- Tab 3: Custom Leadership HTML Block -->
        <div class="vnchip-tab-pane" id="lead-tab-html">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 6px;">
              <div style="font-weight: 700; color: #0D9488; font-size: 0.85rem;">
                <i class="fa-solid fa-code me-1"></i> Tùy biến toàn bộ Mã HTML Khối Leadership & Message from Founder
              </div>
              <button type="button" class="vnchip-mini-btn danger" onclick="clearLiveAceEditor('inputLiveLeadCustomHtml')">
                <i class="fa-solid fa-rotate-left"></i> Xóa HTML tùy chỉnh (Dùng mẫu chuẩn Tab 1)
              </button>
            </div>
            <div style="font-size: 0.8rem; color: #64748B; margin-bottom: 10px;">
              Mặc định để trống sẽ dùng Thẻ Thông điệp Người sáng lập chuẩn với ảnh và lời phát biểu bên Tab 1. Nếu bạn nhập mã HTML ở đây, hệ thống sẽ ưu tiên hiển thị theo mã HTML này.
            </div>
            <textarea id="inputLiveLeadCustomHtml" name="section_about_leadership" rows="7" class="vnchip-form-textarea"><?= htmlspecialchars(Setting::get('section_about_leadership', ''), ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
        </div>

        <!-- Tab 4: Member List & Links -->
        <div class="vnchip-tab-pane" id="lead-tab-members">
          <div style="font-weight: 700; color: #0284C7; font-size: 0.86rem; margin-bottom: 10px;">
            <i class="fa-solid fa-user-tie me-1"></i> Ban Lãnh Đạo Cấp Cao (Executive Leaders)
          </div>

          <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 14px;">
            <?php 
              $allExecs = class_exists('Member') ? Member::getActive('leadership') : [];
              foreach ($allExecs as $m): 
            ?>
              <div style="display: flex; align-items: center; justify-content: space-between; background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 10px; padding: 8px 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                  <img src="<?= asset($m['image_url']) ?>" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid #0284C7;">
                  <div>
                    <div style="font-weight: 700; color: #0F172A; font-size: 0.88rem;"><?= e($m['name']) ?></div>
                    <div style="font-size: 0.76rem; color: #64748B;"><?= e($m['short_position'] ?: $m['position']) ?></div>
                  </div>
                </div>
                <a href="<?= admin_url('member-edit.php?id=' . $m['id']) ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="font-size: 0.76rem; padding: 5px 11px;">
                  <i class="fa-solid fa-pen-to-square me-1"></i> Sửa Profile & Bio
                </a>
              </div>
            <?php endforeach; ?>
          </div>

          <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; padding-bottom: 16px; border-bottom: 1px dashed #CBD5E1;">
            <a href="<?= admin_url('member-edit.php?group=leadership') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-primary" style="font-size: 0.8rem; background: linear-gradient(135deg, #0D9488 0%, #047857 100%);">
              <i class="fa-solid fa-plus me-1"></i> Thêm Ban Lãnh Đạo Mới
            </a>
            <a href="<?= admin_url('members.php') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="font-size: 0.8rem;">
              <i class="fa-solid fa-users-viewfinder me-1"></i> Mở Trang Quản Lý Toàn Bộ Đội Ngũ
            </a>
          </div>

        </div>

        <!-- Tab 5: Key Team block (visibility + headings + members) -->
        <div class="vnchip-tab-pane" id="lead-tab-keyteam">

          <div style="background: #FFFBEB; border: 1px solid #FCD34D; border-radius: 10px; padding: 14px; margin-bottom: 14px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin: 0;">
              <input type="checkbox" id="inputLiveShowKeyTeam" <?= Setting::get('about_show_key_team', '1') === '1' ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: #D97706; flex: none;">
              <span>
                <span style="display: block; font-weight: 700; color: #92400E; font-size: 0.88rem;">
                  <i class="fa-solid fa-eye me-1"></i> Hiển thị khối Key Team trên trang About Us
                </span>
                <span style="display: block; font-size: 0.78rem; color: #78716C; margin-top: 2px;">
                  Bỏ chọn để ẩn toàn bộ khối với khách truy cập. Bạn vẫn thấy khối này (mờ đi, có nhãn ĐANG TẮT) để bật lại bất cứ lúc nào.
                </span>
              </span>
            </label>
          </div>

          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 16px;">
            <div style="font-weight: 700; color: #D97706; font-size: 0.85rem; margin-bottom: 10px;">
              <i class="fa-solid fa-heading me-1"></i> Tiêu đề & Mô tả khối
            </div>
            <div class="vnchip-form-group">
              <label class="vnchip-form-label">Tiêu đề khối Key Team:</label>
              <input type="text" id="inputLiveKeyTeamTitle" name="about_key_team_title" class="vnchip-form-input" value="<?= e(Setting::get('about_key_team_title', 'KEY TEAM')) ?>">
            </div>
            <div class="vnchip-form-group" style="margin-bottom: 0;">
              <label class="vnchip-form-label">Đoạn mô tả ngắn Key Team:</label>
              <textarea id="inputLiveKeyTeamSubtext" name="about_key_team_subtext" rows="2" class="vnchip-form-textarea"><?= e(Setting::get('about_key_team_subtext', 'Our multidisciplinary team of senior IC design engineers, verification leads, and physical implementation specialists.')) ?></textarea>
            </div>
          </div>

          <div id="lead-keyteam-members-anchor" style="font-weight: 700; color: #D97706; font-size: 0.86rem; margin-bottom: 10px;">
            <i class="fa-solid fa-people-group me-1"></i> Đội Ngũ Kỹ Sư Chủ Chốt (Key Team)
          </div>

          <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 14px;">
            <?php 
              $allKeyTeam = class_exists('Member') ? Member::getActive('key_team') : [];
              if (!empty($allKeyTeam)):
                foreach ($allKeyTeam as $km): 
            ?>
              <div style="display: flex; align-items: center; justify-content: space-between; background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 10px; padding: 8px 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                  <?php if (!empty($km['image_url'])): ?>
                    <img src="<?= asset($km['image_url']) ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #F59E0B;">
                  <?php else: ?>
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #E2E8F0; display: flex; align-items: center; justify-content: center; color: #64748B;"><i class="fa-solid fa-user"></i></div>
                  <?php endif; ?>
                  <div>
                    <div style="font-weight: 700; color: #0F172A; font-size: 0.88rem;"><?= e($km['name']) ?></div>
                    <div style="font-size: 0.76rem; color: #64748B;"><?= e($km['short_position'] ?: $km['position']) ?></div>
                  </div>
                </div>
                <a href="<?= admin_url('member-edit.php?id=' . $km['id']) ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="font-size: 0.76rem; padding: 5px 11px;">
                  <i class="fa-solid fa-pen-to-square me-1"></i> Sửa Thành Viên
                </a>
              </div>
            <?php 
                endforeach;
              else:
            ?>
              <div style="font-size: 0.8rem; color: #64748B; background: #F8FAFC; border: 1px dashed #CBD5E1; border-radius: 8px; padding: 12px;">
                Chưa có thành viên Key Team nào trong hệ thống (trang đang hiển thị 5 ô mẫu mặc định). Bấm nút bên dưới để thêm thành viên Key Team mới.
              </div>
            <?php endif; ?>
          </div>

          <a href="<?= admin_url('member-edit.php?group=key_team') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-primary" style="font-size: 0.8rem; background: linear-gradient(135deg, #D97706 0%, #B45309 100%);">
            <i class="fa-solid fa-user-plus me-1"></i> Thêm Thành Viên Key Team Mới
          </a>
        </div>

      </form>
    </div>

    <div class="vnchip-modal-footer">
      <a href="<?= admin_url('members.php') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="margin-right: auto;">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở Quản lý Thành viên Admin
      </a>
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveEditLeadership')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnSaveLiveLeadership" style="background: linear-gradient(135deg, #0D9488 0%, #047857 100%);">
        <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi Leadership
      </button>
    </div>

  </div>
</div>

<!-- 8. Modal Quick Edit: Sustainable (sustainable.php) -->
<div class="vnchip-live-modal" id="modalLiveEditSustainable">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveEditSustainable')"></div>
  <div class="vnchip-modal-dialog" style="max-width: 820px;">
    
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title">
        <i class="fa-solid fa-leaf" style="color: #16A34A;"></i> Chỉnh sửa Trang Phát Triển Bền Vững (Sustainable)
      </h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveEditSustainable')">&times;</button>
    </div>

    <!-- Modal Tabs -->
    <div class="vnchip-modal-tabs">
      <button type="button" class="vnchip-tab-link active" data-tab="sus-tab-esg" onclick="switchModalTab('modalLiveEditSustainable', 'sus-tab-esg')">
        <i class="fa-solid fa-hand-holding-heart"></i> Trách nhiệm Xã hội (ESG)
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="sus-tab-cleanroom" onclick="switchModalTab('modalLiveEditSustainable', 'sus-tab-cleanroom')">
        <i class="fa-solid fa-fan"></i> Clean Room
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="sus-tab-talents" onclick="switchModalTab('modalLiveEditSustainable', 'sus-tab-talents')">
        <i class="fa-solid fa-graduation-cap"></i> Đào tạo & Programs
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="sus-tab-security" onclick="switchModalTab('modalLiveEditSustainable', 'sus-tab-security')">
        <i class="fa-solid fa-shield-halved"></i> Bảo mật & Đối tác
      </button>
    </div>

    <div class="vnchip-modal-body">
      <form id="formLiveEditSustainable">

        <!-- Tab 1: ESG / Social Responsibilities -->
        <div class="vnchip-tab-pane active" id="sus-tab-esg">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 12px;">
            <div style="font-weight: 700; color: #16A34A; font-size: 0.85rem; margin-bottom: 6px;">
              <i class="fa-solid fa-hand-holding-heart me-1"></i> Nội dung Trách nhiệm Xã hội (Social Responsibilities)
            </div>
            <div style="font-size: 0.8rem; color: #64748B; margin-bottom: 10px;">
              Đoạn văn bản xuất hiện trên nền xanh đen ở đầu trang Sustainable. Có thể nhập đoạn văn bản thường hoặc thẻ &lt;p&gt;.
            </div>
            <textarea id="inputLiveSusEsg" name="section_sustainable_esg" rows="6" class="vnchip-form-textarea" style="line-height: 1.6; font-size: 0.85rem;"><?= htmlspecialchars(Setting::get('section_sustainable_esg', "<p>We are committed to making a positive impact beyond the IC design industry.</p>\n<p>We prioritize sustainable practices, foster community engagement, and invest in initiatives that support education and environmental conservation.</p>\n<p>Our mission extends to empowering local talent, creating job opportunities, and contributing to the growth of the semiconductor ecosystem.</p>\n<p>Through meaningful partnerships and programs, VNCHIP is dedicated to driving social progress and fostering a better future for the communities we serve.</p>"), ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
        </div>

        <!-- Tab 2: Clean Room -->
        <div class="vnchip-tab-pane" id="sus-tab-cleanroom">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
            <div class="vnchip-form-group">
              <label class="vnchip-form-label">Tiêu đề khối Clean Room:</label>
              <input type="text" id="inputLiveSusCleanHeading" name="sus_cleanroom_heading" class="vnchip-form-input" value="<?= e(Setting::get('sus_cleanroom_heading', 'CLEAN ROOM')) ?>">
            </div>
            <div class="vnchip-form-group">
              <label class="vnchip-form-label">Đoạn mô tả giải pháp phòng sạch (Cleanroom Subheading):</label>
              <textarea id="inputLiveSusCleanSubheading" name="sus_cleanroom_subheading" rows="3" class="vnchip-form-textarea" style="line-height: 1.6;"><?= e(Setting::get('sus_cleanroom_subheading', 'Clean Room provides the security solutions tailored to meet the specific needs of our valued clients, ensuring a secure and controlled environment for handling sensitive information and projects.')) ?></textarea>
            </div>
            
            <div style="margin-top: 14px; background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 10px; padding: 14px;">
              <div style="font-weight: 700; color: #16A34A; font-size: 0.85rem; margin-bottom: 6px;">
                <i class="fa-solid fa-code me-1"></i> Tùy biến toàn bộ Mã HTML Khối Clean Room (section_sustainable_talent)
              </div>
              <div style="font-size: 0.8rem; color: #64748B; margin-bottom: 8px;">
                Để trống để hiển thị tiêu đề, mô tả và 3 ảnh phòng sạch mặc định bên trên. Nhập HTML nếu bạn muốn thiết kế lại layout phòng sạch.
              </div>
              <textarea id="inputLiveSusCleanCustomHtml" name="section_sustainable_talent" rows="6" class="vnchip-form-textarea"><?= htmlspecialchars(Setting::get('section_sustainable_talent', ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
          </div>
        </div>

        <!-- Tab 3: Talents & Programs -->
        <div class="vnchip-tab-pane" id="sus-tab-talents">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 14px;">
            <div class="vnchip-form-group">
              <label class="vnchip-form-label">Đoạn văn giới thiệu Phát triển Nhân tài (Talents Development Intro):</label>
              <textarea id="inputLiveSusTalentsIntro" name="sus_talents_intro" rows="4" class="vnchip-form-textarea" style="line-height: 1.6;"><?= htmlspecialchars(Setting::get('sus_talents_intro', "<p>At VNCHIP, we believe that talent is the core value of innovation and success in the IC design industry.</p>\n<p>VNCHIP experts' team is committed to pushing the boundaries of technology while staying true to our core values of knowledge-sharing, innovation, and client satisfaction. We are excited to continue growing and making a lasting impact on Vietnam IC Design industry.</p>"), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px;">
              <label class="vnchip-form-label"><i class="fa-solid fa-user-graduate text-success me-1"></i> Link Ứng tuyển Talent Program:</label>
              <input type="text" id="inputLiveProgTalentUrl" name="program_talent_apply_url" class="vnchip-form-input" value="<?= e(Setting::get('program_talent_apply_url', '')) ?>" placeholder="https://forms.gle/... hoặc talk-to-us.php">
            </div>
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px;">
              <label class="vnchip-form-label"><i class="fa-solid fa-graduation-cap text-info me-1"></i> Link Ứng tuyển Interns Program:</label>
              <input type="text" id="inputLiveProgInternsUrl" name="program_interns_apply_url" class="vnchip-form-input" value="<?= e(Setting::get('program_interns_apply_url', '')) ?>" placeholder="https://forms.gle/... hoặc talk-to-us.php">
            </div>
          </div>
        </div>

        <!-- Tab 4: Security & University Partners -->
        <div class="vnchip-tab-pane" id="sus-tab-security">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 14px;">
            <label class="vnchip-form-label"><i class="fa-solid fa-shield-halved text-primary me-1"></i> Cam kết Bảo mật (Confidentiality Statement):</label>
            <textarea id="inputLiveSusSecurity" name="sus_confidentiality_text" rows="4" class="vnchip-form-textarea" style="line-height: 1.6;"><?= htmlspecialchars(Setting::get('sus_confidentiality_text', "<p>We understand the importance of safeguarding your sensitive information.</p>\n<p>We are committed to maintaining the highest standards of confidentiality, ensuring that all proprietary data, intellectual property, and project details are securely managed and protected.</p>\n<p>Through our policies, our Offshore Development Center (ODC) / clean room, and non-disclosure agreements, we prioritize the trust and privacy of our clients and partners.</p>\n<p>Your innovations and designs are safe and confidential, allowing you to focus on achieving your goals with confidence.</p>"), ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>

          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
            <label class="vnchip-form-label"><i class="fa-solid fa-quote-left text-warning me-1"></i> Câu châm ngôn Hợp tác Đối tác (Partners Quote):</label>
            <textarea id="inputLiveSusPartnerQuote" name="sus_partner_quote" rows="2" class="vnchip-form-textarea" style="line-height: 1.5; font-style: italic;"><?= e(Setting::get('sus_partner_quote', '“EMPOWERING OUR PARTNERS WITH EXPERT DESIGN SOLUTIONS WHILE LEVERAGING COLLABORATION TO ACHIEVE GLOBAL STANDARDS.”')) ?></textarea>
          </div>
        </div>

      </form>
    </div>

    <div class="vnchip-modal-footer">
      <a href="<?= admin_url('page-sections.php?tab=sustainable') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="margin-right: auto;">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở Page Sections Admin
      </a>
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveEditSustainable')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnSaveLiveSustainable" style="background: linear-gradient(135deg, #16A34A 0%, #15803D 100%);">
        <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi Sustainable
      </button>
    </div>

  </div>
</div>

<!-- 9. Modal Quick Edit: Business Solutions (business.php) -->
<div class="vnchip-live-modal" id="modalLiveEditBusiness">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveEditBusiness')"></div>
  <div class="vnchip-modal-dialog" style="max-width: 820px;">
    
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title">
        <i class="fa-solid fa-cubes" style="color: #2563EB;"></i> Chỉnh sửa Trang Business & Giải Pháp Vi Mạch
      </h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveEditBusiness')">&times;</button>
    </div>

    <!-- Modal Tabs -->
    <div class="vnchip-modal-tabs">
      <button type="button" class="vnchip-tab-link active" data-tab="biz-tab-tech" onclick="switchModalTab('modalLiveEditBusiness', 'biz-tab-tech')">
        <i class="fa-solid fa-microchip"></i> 3 Trụ Cột Công Nghệ (Capsule)
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="biz-tab-services" onclick="switchModalTab('modalLiveEditBusiness', 'biz-tab-services')">
        <i class="fa-solid fa-layer-group"></i> Dịch Vụ Thiết Kế & IP Matrix
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="biz-tab-projects" onclick="switchModalTab('modalLiveEditBusiness', 'biz-tab-projects')">
        <i class="fa-solid fa-diagram-project"></i> Quản Lý Dự Án & Success in Action
      </button>
    </div>

    <div class="vnchip-modal-body">
      <form id="formLiveEditBusiness">

        <!-- Tab 1: Technology Capsule -->
        <div class="vnchip-tab-pane active" id="biz-tab-tech">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 12px;">
            <div class="vnchip-form-group">
              <label class="vnchip-form-label"><i class="fa-solid fa-heading me-1"></i> Tiêu đề khối (Heading):</label>
              <input type="text" id="inputLiveBizTechTitle" name="business_tech_title" class="vnchip-form-input" style="font-weight: 700;" value="<?= e(Setting::get('business_tech_title', 'TECHNOLOGY')) ?>">
            </div>
            <div style="font-weight: 700; color: #2563EB; font-size: 0.85rem; margin-bottom: 6px;">
              <i class="fa-solid fa-microchip me-1"></i> Tùy biến mã HTML 3 Thẻ Trụ Cột Công Nghệ (Technology Capsule)
            </div>
            <div style="font-size: 0.8rem; color: #64748B; margin-bottom: 10px;">
              Mặc định: Chiplet Disaggregation, Interconnect Standards, và AI-Centric SoC. (Để trống sẽ dùng 3 thẻ mặc định chuẩn).
            </div>
            <textarea id="inputLiveBizTech" name="section_business_services" rows="7" class="vnchip-form-textarea" style="font-family: monospace; font-size: 0.82rem; line-height: 1.5;" placeholder="Để trống để hiển thị 3 thẻ Chiplet, Interconnect, AI-Centric SoC mặc định..."><?= htmlspecialchars(Setting::get('section_business_services', ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            
            <div style="margin-top: 10px; display: flex; justify-content: flex-end;">
              <a href="<?= admin_url('page-sections.php?tab=business') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="font-size: 0.78rem;">
                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở Code Editor Ace trong Admin (Có tô màu)
              </a>
            </div>
          </div>
        </div>

        <!-- Tab 2: Engagement Models & Services -->
        <div class="vnchip-tab-pane" id="biz-tab-services">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 12px;">
            <div style="font-weight: 700; color: #2563EB; font-size: 0.85rem; margin-bottom: 6px;">
              <i class="fa-solid fa-handshake me-1"></i> Mô Hình Hợp Tác & Năng Lực Triển Khai (Engagement Models)
            </div>
            <textarea id="inputLiveBizEngagement" name="section_business_engagement" rows="7" class="vnchip-form-textarea" style="font-family: monospace; font-size: 0.82rem; line-height: 1.5;"><?= htmlspecialchars(Setting::get('section_business_engagement', ''), ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
        </div>

        <!-- Tab 3: Projects & Success in Action -->
        <div class="vnchip-tab-pane" id="biz-tab-projects">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px; text-align: center;">
            <div style="font-size: 1.8rem; color: #2563EB; margin-bottom: 8px;">
              <i class="fa-solid fa-trophy"></i>
            </div>
            <div style="font-weight: 700; font-size: 0.95rem; color: #0F172A; margin-bottom: 6px;">
              Quản lý Danh mục Dự Án & Khách Hàng (Success in Action)
            </div>
            <div style="font-size: 0.82rem; color: #64748B; max-width: 520px; margin: 0 auto 16px;">
              Các dự án thành công và nghiên cứu điển hình được quản lý linh hoạt trong trang Partners & Success Projects của Admin.
            </div>
            <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
              <a href="<?= admin_url('partners.php') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-primary" style="font-size: 0.82rem;">
                <i class="fa-solid fa-list-check me-1"></i> Quản lý Dự án Success in Action
              </a>
              <a href="<?= admin_url('partner-edit.php') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="font-size: 0.82rem;">
                <i class="fa-solid fa-plus me-1"></i> Thêm Dự án Mới
              </a>
            </div>
          </div>
        </div>

      </form>
    </div>

    <div class="vnchip-modal-footer">
      <a href="<?= admin_url('page-sections.php?tab=business') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="margin-right: auto;">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở Page Sections Admin
      </a>
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveEditBusiness')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnSaveLiveBusiness" style="background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);">
        <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi Business
      </button>
    </div>

  </div>
</div>

<!-- 9.05. Modal Quick Edit: Design Service & IP Service accordion (index.php + business.php) -->
<div class="vnchip-live-modal" id="modalLiveEditServices">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveEditServices')"></div>
  <div class="vnchip-modal-dialog" style="max-width: 760px;">

    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title">
        <i class="fa-solid fa-sitemap" style="color: #F25B22;"></i> Chỉnh sửa Khối Design Service &amp; IP Service
      </h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveEditServices')">&times;</button>
    </div>

    <div class="vnchip-modal-body">
      <form id="formLiveEditServices">

        <div style="background: #FFFBEB; border: 1px solid #FCD34D; border-radius: 10px; padding: 14px; margin-bottom: 14px;">
          <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin: 0;">
            <input type="checkbox" id="inputLiveShowServices" <?= Setting::get('show_design_ip_services', '1') === '1' ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: #D97706; flex: none;">
            <span>
              <span style="display: block; font-weight: 700; color: #92400E; font-size: 0.88rem;">
                <i class="fa-solid fa-eye me-1"></i> Hiển thị khối Design Service &amp; IP Service
              </span>
              <span style="display: block; font-size: 0.78rem; color: #78716C; margin-top: 2px;">
                Áp dụng cho cả Trang chủ và trang Business Solutions. Bỏ chọn để ẩn với khách truy cập; bạn vẫn thấy khối mờ kèm nhãn ĐANG TẮT.
              </span>
            </span>
          </label>
        </div>

        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 14px;">
          <div style="font-weight: 700; color: #F25B22; font-size: 0.85rem; margin-bottom: 10px;">
            <i class="fa-solid fa-heading me-1"></i> Tiêu đề hai cột
          </div>
          <div class="vnchip-form-group">
            <label class="vnchip-form-label">Cột trái:</label>
            <input type="text" id="inputLiveServicesDesignTitle" name="services_design_title" class="vnchip-form-input" value="<?= e(Setting::get('services_design_title', 'DESIGN SERVICE')) ?>">
          </div>
          <div class="vnchip-form-group" style="margin-bottom: 0;">
            <label class="vnchip-form-label">Cột phải:</label>
            <input type="text" id="inputLiveServicesIpTitle" name="services_ip_title" class="vnchip-form-input" value="<?= e(Setting::get('services_ip_title', 'IP SERVICE')) ?>">
          </div>
        </div>

        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px; text-align: center;">
          <div style="font-size: 1.6rem; color: #F25B22; margin-bottom: 8px;">
            <i class="fa-solid fa-list-check"></i>
          </div>
          <div style="font-weight: 700; font-size: 0.95rem; color: #0F172A; margin-bottom: 6px;">
            Nội dung từng mục &amp; Từ điển Tooltip
          </div>
          <div style="font-size: 0.82rem; color: #64748B; max-width: 560px; margin: 0 auto 16px;">
            <strong>Cách nhanh nhất:</strong> ngay trên trang, mỗi mục có nút <span style="display:inline-block;background:#F25B22;color:#fff;border-radius:9999px;padding:1px 8px;font-size:0.74rem;font-weight:700;">✎ Sửa mục</span>
            để sửa tên, ảnh và nội dung; mỗi từ khoá cam gạch chân có chấm <span style="display:inline-block;background:#0F172A;color:#fff;border-radius:50%;width:16px;height:16px;font-size:0.6rem;line-height:16px;text-align:center;">✎</span>
            để sửa phần giải thích hiện khi rê chuột. Trang Admin bên dưới dùng để thêm/xoá mục, đổi thứ tự hoặc chuyển cột.
          </div>
          <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
            <a href="<?= admin_url('services.php') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-primary" style="font-size: 0.82rem; background: linear-gradient(135deg, #F25B22 0%, #C2410C 100%);">
              <i class="fa-solid fa-sitemap me-1"></i> Quản lý mục Design &amp; IP Services
            </a>
            <a href="<?= admin_url('tooltips.php') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="font-size: 0.82rem;">
              <i class="fa-solid fa-comment-dots me-1"></i> Từ điển Tooltip
            </a>
          </div>
        </div>

      </form>
    </div>

    <div class="vnchip-modal-footer">
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveEditServices')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnSaveLiveServices" style="background: linear-gradient(135deg, #F25B22 0%, #C2410C 100%);">
        <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi
      </button>
    </div>

  </div>
</div>

<!-- 9.1. Modal Quick Edit: Technology Sections (technology.php) -->
<div class="vnchip-live-modal" id="modalLiveEditTechnology">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveEditTechnology')"></div>
  <div class="vnchip-modal-dialog" style="max-width: 860px;">
    
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title">
        <i class="fa-solid fa-microchip" style="color: #F97316;"></i> Chỉnh sửa Trang Công Nghệ Vi Mạch (technology.php)
      </h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveEditTechnology')">&times;</button>
    </div>

    <div class="vnchip-modal-tabs">
      <button type="button" class="vnchip-tab-link active" data-tab="tech-tab-chiplet" onclick="switchModalTab('modalLiveEditTechnology', 'tech-tab-chiplet')">
        <i class="fa-solid fa-microchip"></i> 1. Chiplet Disaggregation
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="tech-tab-interconnect" onclick="switchModalTab('modalLiveEditTechnology', 'tech-tab-interconnect')">
        <i class="fa-solid fa-network-wired"></i> 2. Interconnect Standards
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="tech-tab-ai" onclick="switchModalTab('modalLiveEditTechnology', 'tech-tab-ai')">
        <i class="fa-solid fa-brain"></i> 3. AI-Centric SoC
      </button>
    </div>

    <div class="vnchip-modal-body">
      <form id="formLiveEditTechnology">
        <!-- Tab 1: Chiplet Disaggregation -->
        <div class="vnchip-tab-pane active" id="tech-tab-chiplet">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
            <div style="font-weight: 700; color: #EA580C; font-size: 0.85rem; margin-bottom: 6px;">
              <i class="fa-solid fa-code me-1"></i> Mã HTML Khối Architectural Evolution: Chiplet Disaggregation
            </div>
            <textarea id="inputLiveTechIntro" name="section_tech_intro" rows="8" class="vnchip-form-textarea"><?= htmlspecialchars(Setting::get('section_tech_intro', ''), ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
        </div>

        <!-- Tab 2: Interconnect Standards -->
        <div class="vnchip-tab-pane" id="tech-tab-interconnect">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
            <div style="font-weight: 700; color: #0284C7; font-size: 0.85rem; margin-bottom: 6px;">
              <i class="fa-solid fa-code me-1"></i> Mã HTML Khối High-Speed Interconnect Standards (UCIe, BoW)
            </div>
            <textarea id="inputLiveTechFeatures" name="section_tech_features" rows="8" class="vnchip-form-textarea"><?= htmlspecialchars(Setting::get('section_tech_features', ''), ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
        </div>

        <!-- Tab 3: AI-Centric SoC Building -->
        <div class="vnchip-tab-pane" id="tech-tab-ai">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
            <div style="font-weight: 700; color: #7C3AED; font-size: 0.85rem; margin-bottom: 6px;">
              <i class="fa-solid fa-code me-1"></i> Mã HTML Khối AI-Centric SoC Building (để trống = dùng nội dung mặc định)
            </div>
            <textarea id="inputLiveTechAi" name="section_tech_ai" rows="8" class="vnchip-form-textarea"><?= htmlspecialchars(Setting::get('section_tech_ai', ''), ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
        </div>
      </form>
    </div>

    <div class="vnchip-modal-footer">
      <a href="<?= admin_url('page-sections.php?tab=technology') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="margin-right: auto;">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở Page Sections Admin
      </a>
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveEditTechnology')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnSaveLiveTechnology" style="background: linear-gradient(135deg, #F97316 0%, #EA580C 100%);">
        <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi Technology
      </button>
    </div>

  </div>
</div>

<!-- 9.2. Modal Quick Edit: About Hero Banner (about-us.php) -->
<div class="vnchip-live-modal" id="modalLiveEditAboutHero">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveEditAboutHero')"></div>
  <div class="vnchip-modal-dialog" style="max-width: 720px;">
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title">
        <i class="fa-solid fa-image" style="color: #0284C7;"></i> Chỉnh sửa Hero Banner (About Us)
      </h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveEditAboutHero')">&times;</button>
    </div>

    <div class="vnchip-modal-body">
      <form id="formLiveEditAboutHero">
        <div style="background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
          <div style="font-weight: 700; color: #0284C7; font-size: 0.9rem; margin-bottom: 12px;">
            <i class="fa-solid fa-panorama me-1"></i> Ảnh nền Hero Banner (About Us)
          </div>
          <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
            <div style="width: 180px; height: 100px; background: #0A1120; border-radius: 10px; overflow: hidden; flex-shrink: 0; border: 1px solid #334155;">
              <img id="preview_about_hero_img" src="<?= asset(Setting::getOr('hero_about', 'images/hero_chip_bg.webp')) ?>" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <div style="flex: 1; min-width: 220px;">
              <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px; flex-wrap: wrap;">
                <input type="file" id="file_about_hero_bg" accept="image/*" style="display: none;" onchange="handleLiveImageUpload(this, 'inputLiveAboutHeroBg', 'preview_about_hero_img', 'hero_about', 'live-about-hero')">
                <button type="button" class="vnchip-btn vnchip-btn-primary" onclick="document.getElementById('file_about_hero_bg').click()" style="padding: 7px 16px; font-size: 0.82rem;">
                  <i class="fa-solid fa-cloud-arrow-up"></i> Tải ảnh nền mới lên (Upload & Thay thế)
                </button>
                <span id="status_inputLiveAboutHeroBg" style="font-size: 0.78rem;"></span>
              </div>
              <label class="vnchip-form-label" style="font-size: 0.76rem; color: #64748B; margin-bottom: 4px;">Đường dẫn ảnh nền:</label>
              <input type="text" id="inputLiveAboutHeroBg" name="hero_about" class="vnchip-form-input" style="font-size: 0.78rem; padding: 6px 10px; font-family: monospace;" value="<?= e(Setting::getOr('hero_about', 'images/hero_chip_bg.webp')) ?>" oninput="updateLivePreview(this.value, 'preview_about_hero_img')">
            </div>
          </div>
        </div>

        <div class="vnchip-form-group">
          <label class="vnchip-form-label">Tiêu đề chính trên Hero Banner:</label>
          <input type="text" id="inputLiveAboutHeroTitle" name="about_hero_title" class="vnchip-form-input" style="font-weight: 800; font-size: 1rem;" value="<?= e(Setting::getOr('about_hero_title', 'ABOUT US')) ?>">
        </div>
      </form>
    </div>

    <div class="vnchip-modal-footer">
      <a href="<?= admin_url('banners.php') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="margin-right: auto;">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở Quản lý Banners
      </a>
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveEditAboutHero')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnSaveLiveAboutHero">
        <i class="fa-solid fa-floppy-disk"></i> Lưu Hero Banner
      </button>
    </div>
  </div>
</div>

<!-- 9.3. Modal Quick Edit: About Overview Sections (about-us.php) -->
<div class="vnchip-live-modal" id="modalLiveEditAboutOverview">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveEditAboutOverview')"></div>
  <div class="vnchip-modal-dialog" style="max-width: 880px;">
    
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title">
        <i class="fa-solid fa-compass" style="color: #0284C7;"></i> Chỉnh sửa Giới Thiệu VNCHIP Journey & Ảnh Trụ Sở (about-us.php)
      </h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveEditAboutOverview')">&times;</button>
    </div>

    <div class="vnchip-modal-tabs">
      <button type="button" class="vnchip-tab-link active" data-tab="about-tab-story" onclick="switchModalTab('modalLiveEditAboutOverview', 'about-tab-story')">
        <i class="fa-solid fa-route"></i> 1. Tiêu đề & Câu chuyện (Journey)
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="about-tab-vision" onclick="switchModalTab('modalLiveEditAboutOverview', 'about-tab-vision')">
        <i class="fa-solid fa-building"></i> 2. Ảnh Trụ Sở & Giới thiệu Chi tiết
      </button>
    </div>

    <div class="vnchip-modal-body">
      <form id="formLiveEditAboutOverview">
        <!-- Tab 1: Journey Story -->
        <div class="vnchip-tab-pane active" id="about-tab-story">
          <div class="vnchip-form-group" style="margin-bottom: 14px;">
            <label class="vnchip-form-label">Tiêu đề khối giới thiệu (Overview Title):</label>
            <input type="text" id="inputLiveAboutOverviewTitle" name="about_overview_title" class="vnchip-form-input" style="font-weight: 700;" value="<?= e(Setting::getOr('about_overview_title', 'VNCHIP JOURNEY')) ?>">
          </div>
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
            <div style="font-weight: 700; color: #0284C7; font-size: 0.85rem; margin-bottom: 6px;">
              <i class="fa-solid fa-code me-1"></i> Mã HTML Đoạn Dẫn Hành Trình VNCHIP Journey
            </div>
            <textarea id="inputLiveAboutStory" name="section_about_story" rows="8" class="vnchip-form-textarea"><?= htmlspecialchars(Setting::getOr('section_about_story', $defaultAboutStoryHtml), ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
        </div>

        <!-- Tab 2: Lobby Photo & Detailed Overview -->
        <div class="vnchip-tab-pane" id="about-tab-vision">
          <div style="background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 10px; padding: 14px; margin-bottom: 14px;">
            <div style="font-weight: 700; color: #0284C7; font-size: 0.85rem; margin-bottom: 10px;">
              <i class="fa-solid fa-camera me-1"></i> Ảnh Trụ Sở / Sảnh Lễ Tân (VNCHIP Lobby Photo)
            </div>
            <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
              <div style="width: 140px; height: 84px; background: #0A1120; border-radius: 8px; overflow: hidden; flex-shrink: 0; border: 1px solid #334155;">
                <img id="preview_about_lobby_img" src="<?= asset(Setting::getOr('about_lobby_photo', 'images/about_lobby_reception.webp')) ?>" style="width: 100%; height: 100%; object-fit: cover;">
              </div>
              <div style="flex: 1; min-width: 220px;">
                <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px; flex-wrap: wrap;">
                  <input type="file" id="file_about_lobby_photo" accept="image/*" style="display: none;" onchange="handleLiveAboutLobbyUpload(this)">
                  <button type="button" class="vnchip-btn vnchip-btn-primary" onclick="document.getElementById('file_about_lobby_photo').click()" style="padding: 6px 14px; font-size: 0.8rem;">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Tải ảnh mới lên (Upload & Thay thế)
                  </button>
                  <span id="status_inputLiveAboutLobbyPhoto" style="font-size: 0.78rem;"></span>
                </div>
                <input type="text" id="inputLiveAboutLobbyPhoto" name="about_lobby_photo" class="vnchip-form-input" style="font-size: 0.76rem; padding: 5px 10px; font-family: monospace;" value="<?= e(Setting::getOr('about_lobby_photo', 'images/about_lobby_reception.webp')) ?>" oninput="updateLivePreview(this.value, 'preview_about_lobby_img')">
              </div>
            </div>
          </div>

          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
            <div style="font-weight: 700; color: #0284C7; font-size: 0.85rem; margin-bottom: 6px;">
              <i class="fa-solid fa-code me-1"></i> Mã HTML Khối Ảnh Trụ Sở & Năng Lực Thiết Kế Vi Mạch
            </div>
            <textarea id="inputLiveAboutVision" name="section_about_vision" rows="8" class="vnchip-form-textarea"><?= htmlspecialchars(Setting::getOr('section_about_vision', $defaultAboutOverviewGridHtml), ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
        </div>
      </form>
    </div>

    <div class="vnchip-modal-footer">
      <a href="<?= admin_url('page-sections.php?tab=about_us') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="margin-right: auto;">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở Page Sections Admin
      </a>
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveEditAboutOverview')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnSaveLiveAboutOverview" style="background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%);">
        <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi About Overview
      </button>
    </div>

  </div>
</div>

<!-- 9.4. Modal Quick Edit: Journey Timeline (about-us.php#journey) -->
<div class="vnchip-live-modal" id="modalLiveEditAboutTimeline">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveEditAboutTimeline')"></div>
  <div class="vnchip-modal-dialog" style="max-width: 860px;">
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title">
        <i class="fa-solid fa-timeline" style="color: #6366F1;"></i> Chỉnh sửa Lịch Sử Phát Triển & Timeline (about-us.php)
      </h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveEditAboutTimeline')">&times;</button>
    </div>

    <div class="vnchip-modal-tabs">
      <button type="button" class="vnchip-tab-link active" data-tab="timeline-tab-visual" onclick="switchModalTab('modalLiveEditAboutTimeline', 'timeline-tab-visual')">
        <i class="fa-solid fa-list-ol"></i> 1. Tiêu đề & Các Mốc Thời Gian
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="timeline-tab-html" onclick="switchModalTab('modalLiveEditAboutTimeline', 'timeline-tab-html')">
        <i class="fa-solid fa-code"></i> 2. Mã HTML Tùy Biến (Nâng cao)
      </button>
    </div>

    <div class="vnchip-modal-body">
      <form id="formLiveEditAboutTimeline">
        <div class="vnchip-tab-pane active" id="timeline-tab-visual">
          <div class="vnchip-form-group" style="margin-bottom: 14px;">
            <label class="vnchip-form-label">Dòng tiêu đề phụ phía trên Timeline (Journey Subtitle):</label>
            <textarea id="inputLiveJourneySubtitle" name="about_journey_subtitle" rows="2" class="vnchip-form-textarea" style="font-weight: 600;"><?= e(Setting::getOr('about_journey_subtitle', 'FROM ESTABLISHMENT TO THE PRESENT DAY, THROUGH UPS AND DOWNS, VNCHIP HAS ALWAYS BEEN A PIONEER IN THE FIELD OF SEMICONDUCTOR DESIGN.')) ?></textarea>
          </div>

          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <label class="vnchip-form-label" style="margin: 0;"><i class="fa-solid fa-flag-checkered text-primary me-1"></i> Danh sách các mốc thời gian (Milestones):</label>
            <button type="button" class="vnchip-btn vnchip-btn-primary" onclick="addLiveTimelineRow()" style="padding: 6px 12px; font-size: 0.78rem; background: linear-gradient(135deg, #6366F1 0%, #4338CA 100%);">
              <i class="fa-solid fa-plus"></i> Thêm mốc thời gian
            </button>
          </div>

          <div id="liveTimelineRepeaterList" class="vnchip-repeater-list"></div>
          <input type="hidden" id="inputLiveTimelineJson" name="about_timeline_items" value="<?= e(json_encode($liveAboutTimelineData, JSON_UNESCAPED_UNICODE)) ?>">
        </div>

        <div class="vnchip-tab-pane" id="timeline-tab-html">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 6px;">
              <div style="font-weight: 700; color: #6366F1; font-size: 0.85rem;">
                <i class="fa-solid fa-code me-1"></i> Mã HTML Tùy Biến Toàn Bộ Khối Timeline (section_about_timeline)
              </div>
              <button type="button" class="vnchip-mini-btn danger" onclick="clearLiveAceEditor('inputLiveAboutTimelineHtml')">
                <i class="fa-solid fa-rotate-left"></i> Xóa HTML tùy chỉnh (Dùng danh sách Tab 1)
              </button>
            </div>
            <div style="font-size: 0.8rem; color: #64748B; margin-bottom: 10px;">
              Để trống nếu muốn sử dụng danh sách mốc trực quan ở Tab 1. Nhập mã HTML tại đây nếu muốn thiết kế lại hoàn toàn giao diện Timeline.
            </div>
            <textarea id="inputLiveAboutTimelineHtml" name="section_about_timeline" rows="8" class="vnchip-form-textarea"><?= htmlspecialchars(Setting::get('section_about_timeline', ''), ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
        </div>
      </form>
    </div>

    <div class="vnchip-modal-footer">
      <a href="<?= admin_url('page-sections.php?tab=about_us') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="margin-right: auto;">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở Page Sections Admin
      </a>
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveEditAboutTimeline')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnSaveLiveAboutTimeline" style="background: linear-gradient(135deg, #6366F1 0%, #4338CA 100%);">
        <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi Timeline
      </button>
    </div>
  </div>
</div>

<!-- 9.5. Modal Quick Edit: Vision, Mission & Core Values (about-us.php#vision) -->
<div class="vnchip-live-modal" id="modalLiveEditAboutVision">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveEditAboutVision')"></div>
  <div class="vnchip-modal-dialog" style="max-width: 880px;">
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title">
        <i class="fa-solid fa-bullseye" style="color: #0891B2;"></i> Chỉnh sửa Vision, Mission & Core Values (about-us.php)
      </h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveEditAboutVision')">&times;</button>
    </div>

    <div class="vnchip-modal-tabs">
      <button type="button" class="vnchip-tab-link active" data-tab="vision-tab-visual" onclick="switchModalTab('modalLiveEditAboutVision', 'vision-tab-visual')">
        <i class="fa-solid fa-layer-group"></i> 1. Tiêu đề & Các Thẻ Vision / Mission / Values
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="vision-tab-html" onclick="switchModalTab('modalLiveEditAboutVision', 'vision-tab-html')">
        <i class="fa-solid fa-code"></i> 2. Mã HTML Tùy Biến (Nâng cao)
      </button>
    </div>

    <div class="vnchip-modal-body">
      <form id="formLiveEditAboutVision">
        <div class="vnchip-tab-pane active" id="vision-tab-visual">
          <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 12px; margin-bottom: 14px;">
            <div>
              <label class="vnchip-form-label">Tiêu đề chính khối:</label>
              <input type="text" id="inputLiveVisionMainTitle" name="about_vision_main_title" class="vnchip-form-input" style="font-weight: 800;" value="<?= e(Setting::getOr('about_vision_main_title', 'VISION')) ?>">
            </div>
            <div>
              <label class="vnchip-form-label">Dòng mô tả phụ dưới tiêu đề:</label>
              <input type="text" id="inputLiveVisionMainSubtitle" name="about_vision_main_subtitle" class="vnchip-form-input" value="<?= e(Setting::getOr('about_vision_main_subtitle', 'VNCHIP delivers world-class solutions and shapes a better future through innovation and collaboration.')) ?>">
            </div>
          </div>

          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <label class="vnchip-form-label" style="margin: 0;"><i class="fa-solid fa-shapes text-primary me-1"></i> Danh sách các thẻ (Vision / Mission / Core Values):</label>
            <button type="button" class="vnchip-btn vnchip-btn-primary" onclick="addLiveVisionCardRow()" style="padding: 6px 12px; font-size: 0.78rem; background: linear-gradient(135deg, #0891B2 0%, #0E7490 100%);">
              <i class="fa-solid fa-plus"></i> Thêm thẻ mới
            </button>
          </div>

          <div id="liveVisionCardsRepeaterList" class="vnchip-repeater-list"></div>
          <input type="hidden" id="inputLiveVisionCardsJson" name="about_vision_cards" value="<?= e(json_encode($liveAboutVisionCardsData, JSON_UNESCAPED_UNICODE)) ?>">
        </div>

        <div class="vnchip-tab-pane" id="vision-tab-html">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 6px;">
              <div style="font-weight: 700; color: #0891B2; font-size: 0.85rem;">
                <i class="fa-solid fa-code me-1"></i> Mã HTML Tùy Biến Toàn Bộ Khối Vision (section_about_vision_cards)
              </div>
              <button type="button" class="vnchip-mini-btn danger" onclick="clearLiveAceEditor('inputLiveAboutVisionCardsHtml')">
                <i class="fa-solid fa-rotate-left"></i> Xóa HTML tùy chỉnh (Dùng các thẻ Tab 1)
              </button>
            </div>
            <div style="font-size: 0.8rem; color: #64748B; margin-bottom: 10px;">
              Để trống để dùng giao diện chuẩn từ Tab 1. Nhập mã HTML/SVG tùy chỉnh tại đây nếu cần bố cục đặc biệt.
            </div>
            <textarea id="inputLiveAboutVisionCardsHtml" name="section_about_vision_cards" rows="8" class="vnchip-form-textarea"><?= htmlspecialchars(Setting::get('section_about_vision_cards', ''), ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
        </div>
      </form>
    </div>

    <div class="vnchip-modal-footer">
      <a href="<?= admin_url('page-sections.php?tab=about_us') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="margin-right: auto;">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở Page Sections Admin
      </a>
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveEditAboutVision')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnSaveLiveAboutVision" style="background: linear-gradient(135deg, #0891B2 0%, #0E7490 100%);">
        <i class="fa-solid fa-floppy-disk"></i> Lưu Vision & Mission
      </button>
    </div>
  </div>
</div>

<!-- 9.6. Modal Quick Edit: Code of Conduct (about-us.php#conduct) -->
<div class="vnchip-live-modal" id="modalLiveEditConduct">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveEditConduct')"></div>
  <div class="vnchip-modal-dialog" style="max-width: 880px;">
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title">
        <i class="fa-solid fa-scale-balanced" style="color: #E11D48;"></i> Chỉnh sửa Khối Code of Conduct (about-us.php#conduct)
      </h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveEditConduct')">&times;</button>
    </div>

    <div class="vnchip-modal-tabs">
      <button type="button" class="vnchip-tab-link active" data-tab="conduct-tab-visual" onclick="switchModalTab('modalLiveEditConduct', 'conduct-tab-visual')">
        <i class="fa-solid fa-shield-halved"></i> 1. Tiêu đề & 4 Thẻ Quy Tắc Ứng Xử
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="conduct-tab-html" onclick="switchModalTab('modalLiveEditConduct', 'conduct-tab-html')">
        <i class="fa-solid fa-code"></i> 2. Mã HTML Tùy Biến (Nâng cao)
      </button>
    </div>

    <div class="vnchip-modal-body">
      <form id="formLiveEditConduct">
        <div class="vnchip-tab-pane active" id="conduct-tab-visual">
          <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 12px; margin-bottom: 14px;">
            <div>
              <label class="vnchip-form-label">Tiêu đề chính (Title):</label>
              <input type="text" id="inputLiveConductTitle" name="about_conduct_title" class="vnchip-form-input" style="font-weight: 800;" value="<?= e(Setting::getOr('about_conduct_title', 'CODE OF CONDUCT')) ?>">
            </div>
            <div>
              <label class="vnchip-form-label">Đoạn mô tả phụ (Subtitle):</label>
              <textarea id="inputLiveConductSubtitle" name="about_conduct_subtitle" rows="2" class="vnchip-form-textarea"><?= e(Setting::getOr('about_conduct_subtitle', 'We uphold integrity, innovation, and excellence, delivering design services with professionalism, respect, and a commitment to trusted partnerships.')) ?></textarea>
            </div>
          </div>

          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <label class="vnchip-form-label" style="margin: 0;"><i class="fa-solid fa-list-check text-danger me-1"></i> Danh sách các thẻ Quy tắc Ứng xử (nhấn Enter trong tiêu đề để xuống dòng):</label>
            <button type="button" class="vnchip-btn vnchip-btn-primary" onclick="addLiveConductCardRow()" style="padding: 6px 12px; font-size: 0.78rem; background: linear-gradient(135deg, #E11D48 0%, #BE123C 100%);">
              <i class="fa-solid fa-plus"></i> Thêm thẻ mới
            </button>
          </div>

          <div id="liveConductCardsRepeaterList" class="vnchip-repeater-list"></div>
          <input type="hidden" id="inputLiveConductCardsJson" name="about_conduct_cards" value="<?= e(json_encode($liveAboutConductCardsData, JSON_UNESCAPED_UNICODE)) ?>">
        </div>

        <div class="vnchip-tab-pane" id="conduct-tab-html">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 6px;">
              <div style="font-weight: 700; color: #E11D48; font-size: 0.85rem;">
                <i class="fa-solid fa-code me-1"></i> Mã HTML Tùy Biến Toàn Bộ Khối Code of Conduct (section_about_conduct)
              </div>
              <button type="button" class="vnchip-mini-btn danger" onclick="clearLiveAceEditor('inputLiveConductHtml')">
                <i class="fa-solid fa-rotate-left"></i> Xóa HTML tùy chỉnh (Dùng các thẻ Tab 1)
              </button>
            </div>
            <div style="font-size: 0.8rem; color: #64748B; margin-bottom: 10px;">
              Để trống nếu muốn sử dụng danh sách thẻ trực quan ở Tab 1. Nhập mã HTML tại đây để tùy biến sâu toàn bộ khối Code of Conduct.
            </div>
            <textarea id="inputLiveConductHtml" name="section_about_conduct" rows="8" class="vnchip-form-textarea"><?= htmlspecialchars(Setting::get('section_about_conduct', ''), ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
        </div>
      </form>
    </div>

    <div class="vnchip-modal-footer">
      <a href="<?= admin_url('page-sections.php?tab=about_us') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="margin-right: auto;">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở Page Sections Admin
      </a>
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveEditConduct')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnSaveLiveConduct" style="background: linear-gradient(135deg, #E11D48 0%, #BE123C 100%);">
        <i class="fa-solid fa-floppy-disk"></i> Lưu Code of Conduct
      </button>
    </div>
  </div>
</div>

<!-- 10. Modal Quick Edit: Contact / Talk To Us (talk-to-us.php) -->
<div class="vnchip-live-modal" id="modalLiveEditContact">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveEditContact')"></div>
  <div class="vnchip-modal-dialog" style="max-width: 800px;">
    
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title">
        <i class="fa-solid fa-headset" style="color: #EA580C;"></i> Chỉnh sửa Trang Liên Hệ & Trụ Sở (Talk To Us)
      </h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveEditContact')">&times;</button>
    </div>

    <!-- Modal Tabs -->
    <div class="vnchip-modal-tabs">
      <button type="button" class="vnchip-tab-link active" data-tab="contact-tab-info" onclick="switchModalTab('modalLiveEditContact', 'contact-tab-info')">
        <i class="fa-solid fa-building"></i> Trụ sở & Hotline
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="contact-tab-headings" onclick="switchModalTab('modalLiveEditContact', 'contact-tab-headings')">
        <i class="fa-solid fa-heading"></i> Tiêu đề & Giới thiệu
      </button>
      <button type="button" class="vnchip-tab-link" data-tab="contact-tab-inquiries" onclick="switchModalTab('modalLiveEditContact', 'contact-tab-inquiries')">
        <i class="fa-solid fa-inbox"></i> Hộp Thư Khách Hàng
      </button>
    </div>

    <div class="vnchip-modal-body">
      <form id="formLiveEditContact">

        <!-- Tab 1: Offices & Contact -->
        <div class="vnchip-tab-pane active" id="contact-tab-info">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px; margin-bottom: 12px;">
            <div class="vnchip-form-group">
              <label class="vnchip-form-label"><i class="fa-solid fa-location-dot text-danger me-1"></i> Địa chỉ Trụ sở Việt Nam (Vietnam Office):</label>
              <textarea id="inputLiveContactAddrVn" name="site_address_vn" rows="2" class="vnchip-form-textarea"><?= e(Setting::get('site_address_vn', SITE_ADDRESS_VN)) ?></textarea>
            </div>
            <div class="vnchip-form-group">
              <label class="vnchip-form-label"><i class="fa-solid fa-location-dot text-primary me-1"></i> Địa chỉ Văn phòng USA (USA Office):</label>
              <textarea id="inputLiveContactAddrUs" name="site_address_us" rows="2" class="vnchip-form-textarea"><?= e(Setting::get('site_address_us', SITE_ADDRESS_US)) ?></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
              <div>
                <label class="vnchip-form-label"><i class="fa-solid fa-phone text-success me-1"></i> Số điện thoại Hotline:</label>
                <input type="text" id="inputLiveContactPhone" name="site_phone" class="vnchip-form-input" value="<?= e(Setting::get('site_phone', SITE_PHONE)) ?>">
              </div>
              <div>
                <label class="vnchip-form-label"><i class="fa-solid fa-envelope text-warning me-1"></i> Email tiếp nhận liên hệ:</label>
                <input type="email" id="inputLiveContactEmail" name="site_email" class="vnchip-form-input" value="<?= e(Setting::get('site_email', SITE_EMAIL)) ?>">
              </div>
            </div>
          </div>
        </div>

        <!-- Tab 2: Headings & Intro -->
        <div class="vnchip-tab-pane" id="contact-tab-headings">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
            <div class="vnchip-form-group">
              <label class="vnchip-form-label">Tiêu đề lớn của trang:</label>
              <input type="text" id="inputLiveContactPageTitle" name="contact_page_title" class="vnchip-form-input" value="<?= e(Setting::get('contact_page_title', 'CONNECT WITH EXPERTS')) ?>">
            </div>
            <div class="vnchip-form-group">
              <label class="vnchip-form-label">Tiêu đề cột văn phòng:</label>
              <input type="text" id="inputLiveContactOfficesTitle" name="contact_offices_title" class="vnchip-form-input" value="<?= e(Setting::get('contact_offices_title', 'VNCHIP OFFICES')) ?>">
            </div>
            <div class="vnchip-form-group">
              <label class="vnchip-form-label">Đoạn mô tả giới thiệu:</label>
              <textarea id="inputLiveContactOfficesDesc" name="contact_offices_desc" rows="3" class="vnchip-form-textarea"><?= e(Setting::get('contact_offices_desc', 'Our semiconductor experts are ready to assist you with technical inquiries and project collaborations globally.')) ?></textarea>
            </div>
          </div>
        </div>

        <!-- Tab 3: Customer Inquiries -->
        <div class="vnchip-tab-pane" id="contact-tab-inquiries">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 18px; text-align: center;">
            <div style="font-size: 2rem; color: #EA580C; margin-bottom: 8px;">
              <i class="fa-solid fa-inbox"></i>
            </div>
            <div style="font-weight: 700; font-size: 0.95rem; color: #0F172A; margin-bottom: 6px;">
              Quản lý Yêu Cầu & Thư Liên Hệ Khách Hàng (Inquiries)
            </div>
            <div style="font-size: 0.82rem; color: #64748B; max-width: 520px; margin: 0 auto 16px;">
              Mọi tin nhắn khách hàng gửi qua form "Submit Inquiry" trên trang Talk To Us đều được lưu trữ bảo mật và hiển thị trong mục Inquiries của Admin.
            </div>
            <a href="<?= admin_url('inquiries.php') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-primary" style="font-size: 0.85rem; background: linear-gradient(135deg, #EA580C 0%, #C2410C 100%);">
              <i class="fa-solid fa-envelope-open-text me-1"></i> Mở Hộp Thư Khách Hàng (Inquiries)
            </a>
          </div>
        </div>

      </form>
    </div>

    <div class="vnchip-modal-footer">
      <a href="<?= admin_url('inquiries.php') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="margin-right: auto;">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Xem Thư Khách Hàng
      </a>
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveEditContact')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnSaveLiveContact" style="background: linear-gradient(135deg, #EA580C 0%, #C2410C 100%);">
        <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi Liên Hệ
      </button>
    </div>

  </div>
</div>

<!-- 11. Toast Alert -->

<!-- Live Edit: one Design/IP Service accordion item -->
<div class="vnchip-live-modal" id="modalLiveEditServiceItem">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveEditServiceItem')"></div>
  <div class="vnchip-modal-dialog" style="max-width: 900px;">
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title">
        <i class="fa-solid fa-pen-to-square" style="color: #F25B22;"></i> Sửa mục: <span id="svcItemModalTitle">…</span>
      </h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveEditServiceItem')">&times;</button>
    </div>

    <div class="vnchip-modal-body">
      <input type="hidden" id="svcItemId" value="0">

      <div class="vnchip-form-group">
        <label class="vnchip-form-label">Tên mục (hiện trên thanh accordion):</label>
        <input type="text" id="svcItemTitle" class="vnchip-form-input" style="font-weight: 800; font-size: 1rem;">
      </div>

      <div style="background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 12px; padding: 14px; margin-bottom: 14px;">
        <div style="font-weight: 700; color: #F25B22; font-size: 0.85rem; margin-bottom: 10px;">
          <i class="fa-solid fa-image me-1"></i> Ảnh minh hoạ
        </div>
        <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
          <img id="svcItemImgPreview" class="svc-img-thumb" src="" alt="">
          <div style="flex: 1; min-width: 220px;">
            <input type="file" id="svcItemImgFile" accept="image/*" style="display: none;">
            <button type="button" class="vnchip-btn vnchip-btn-primary" onclick="document.getElementById('svcItemImgFile').click()" style="padding: 7px 14px; font-size: 0.8rem; background: linear-gradient(135deg, #F25B22 0%, #C2410C 100%);">
              <i class="fa-solid fa-cloud-arrow-up"></i> Tải ảnh mới lên
            </button>
            <span id="svcItemImgStatus" style="font-size: 0.78rem; margin-left: 8px;"></span>
            <input type="text" id="svcItemImgUrl" class="vnchip-form-input" style="font-size: 0.76rem; padding: 6px 10px; font-family: monospace; margin-top: 8px;" placeholder="uploads/media/....webp">
          </div>
        </div>
      </div>

      <div class="vnchip-form-group" style="margin-bottom: 0;">
        <label class="vnchip-form-label">Nội dung mục:</label>
        <textarea id="svcItemBody" rows="14" class="vnchip-form-textarea"></textarea>

        <div class="svc-sel-bar" id="svcSelBar">
          <span id="svcSelHint"><i class="fa-solid fa-i-cursor me-1"></i> Bôi đen một cụm từ trong nội dung để gắn tooltip cho nó.</span>
          <button type="button" class="vnchip-btn vnchip-btn-primary" id="svcBtnWrap" disabled style="background: linear-gradient(135deg, #F25B22 0%, #C2410C 100%);">
            <i class="fa-solid fa-comment-dots"></i> Gắn tooltip
          </button>
          <button type="button" class="vnchip-btn vnchip-btn-secondary" id="svcBtnEditCurrent" disabled>
            <i class="fa-solid fa-pen"></i> Sửa giải thích
          </button>
          <button type="button" class="vnchip-btn vnchip-btn-secondary" id="svcBtnUnwrap" disabled>
            <i class="fa-solid fa-eraser"></i> Bỏ tooltip
          </button>
          <span style="margin-left: auto; display: inline-flex; gap: 6px; align-items: center;">
            <select class="svc-dict-select" id="svcDictSelect"><option value="">Chèn từ khoá có sẵn…</option></select>
          </span>
        </div>

        <div style="margin-top: 12px;">
          <div style="font-weight: 700; color: #0F172A; font-size: 0.82rem; margin-bottom: 8px;">
            <i class="fa-solid fa-list-ul me-1" style="color: #F25B22;"></i> Từ khoá có tooltip trong mục này
          </div>
          <div class="svc-kw-list" id="svcKwList"></div>
        </div>
      </div>
    </div>

    <div class="vnchip-modal-footer">
      <a href="<?= admin_url('services.php') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="margin-right: auto;">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Thêm/xoá/sắp xếp mục
      </a>
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveEditServiceItem')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnSaveSvcItem" style="background: linear-gradient(135deg, #F25B22 0%, #C2410C 100%);">
        <i class="fa-solid fa-floppy-disk"></i> Lưu mục
      </button>
    </div>
  </div>
</div>

<!-- Live Edit: one keyword tooltip (the panel shown on hover) -->
<div class="vnchip-live-modal" id="modalLiveEditTooltip">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveEditTooltip')"></div>
  <div class="vnchip-modal-dialog" style="max-width: 720px;">
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title">
        <i class="fa-solid fa-comment-dots" style="color: #F25B22;"></i> Tooltip: <span id="tipModalKeyword">…</span>
      </h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveEditTooltip')">&times;</button>
    </div>

    <div class="vnchip-modal-body">
      <input type="hidden" id="tipId" value="0">
      <div id="tipNewNote" style="display:none; background: #EFF6FF; border: 1px solid #BFDBFE; color: #1E3A8A; border-radius: 10px; padding: 10px 12px; font-size: 0.8rem; margin-bottom: 12px;">
        <i class="fa-solid fa-circle-info me-1"></i> Từ khoá này chưa có giải thích. Viết nội dung rồi bấm Lưu, từ khoá sẽ hiện màu cam và có tooltip khi rê chuột.
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
        <div class="vnchip-form-group">
          <label class="vnchip-form-label">Từ khoá (chữ hiện trên trang):</label>
          <input type="text" id="tipKeyword" class="vnchip-form-input" style="font-weight: 700;">
        </div>
        <div class="vnchip-form-group">
          <label class="vnchip-form-label">Tiêu đề bảng giải thích <span style="color:#94A3B8; font-weight: 400;">(trống = dùng từ khoá)</span>:</label>
          <input type="text" id="tipTitle" class="vnchip-form-input">
        </div>
      </div>

      <div class="vnchip-form-group" style="margin-bottom: 0;">
        <label class="vnchip-form-label">Nội dung giải thích:</label>
        <textarea id="tipContent" rows="8" class="vnchip-form-textarea"></textarea>
        <div style="font-size: 0.74rem; color: #64748B; margin-top: 6px;">
          Dùng cho tất cả các mục có từ khoá này. Trên máy tính hiện khi rê chuột, trên điện thoại hiện khi chạm.
        </div>
      </div>
    </div>

    <div class="vnchip-modal-footer">
      <a href="<?= admin_url('tooltips.php') ?>" target="_blank" rel="noopener noreferrer" class="vnchip-btn vnchip-btn-secondary" style="margin-right: auto;">
        <i class="fa-solid fa-book me-1"></i> Toàn bộ từ điển
      </a>
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveEditTooltip')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnSaveTip" style="background: linear-gradient(135deg, #F25B22 0%, #C2410C 100%);">
        <i class="fa-solid fa-floppy-disk"></i> Lưu tooltip
      </button>
    </div>
  </div>
</div>

<div id="live-edit-toast">
  <i class="fa-solid fa-circle-check text-success" style="font-size: 1.2rem;"></i>
  <span id="live-edit-toast-text">Đã lưu thay đổi thành công!</span>
</div>

<!-- Ace Editor CDNs for Live Edit HTML Code Highlighting -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.2/ace.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.2/theme-tomorrow_night.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.2/theme-chrome.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.2/mode-html.js"></script>

<!-- 8. Client Script for Live Edit Engine -->
<script>
function openLiveModal(modalId, activeTabId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add('show');
    if (activeTabId) {
      switchModalTab(modalId, activeTabId);
    }
    if (modalId === 'modalLiveEditAboutTimeline' && typeof renderLiveTimelineRepeater === 'function') {
      renderLiveTimelineRepeater();
    } else if (modalId === 'modalLiveEditAboutVision' && typeof renderLiveVisionCardsRepeater === 'function') {
      renderLiveVisionCardsRepeater();
    } else if (modalId === 'modalLiveEditConduct' && typeof renderLiveConductCardsRepeater === 'function') {
      renderLiveConductCardsRepeater();
    }
    initLiveAceEditorsAll();
    setTimeout(resizeAllLiveAceEditors, 60);
  }
}

function closeLiveModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('show');
  }
}

function switchModalTab(modalId, tabId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  modal.querySelectorAll('.vnchip-tab-link').forEach(btn => {
    btn.classList.toggle('active', btn.getAttribute('data-tab') === tabId);
  });
  modal.querySelectorAll('.vnchip-tab-pane').forEach(pane => {
    pane.classList.toggle('active', pane.id === tabId);
  });
  setTimeout(resizeAllLiveAceEditors, 60);
}

// Global escapeHtml helper for live modals & Ace wrappers
function escapeHtmlLive(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.innerText = text;
  return div.innerHTML;
}

// Ace Code Editors registry
window._liveAceEditors = window._liveAceEditors || {};

function initLiveAceEditor(textareaId, heightPx, customTitle) {
  const textarea = document.getElementById(textareaId);
  if (!textarea) return null;
  if (window._liveAceEditors[textareaId]) {
    return window._liveAceEditors[textareaId];
  }
  if (typeof ace === 'undefined') {
    return null;
  }

  // 1. Hide original textarea
  textarea.style.display = 'none';

  // 2. Build wrapper, header & editor container
  const wrap = document.createElement('div');
  wrap.className = 'vnchip-ace-wrap';
  wrap.id = 'wrap_' + textareaId;

  const titleText = customTitle || 'Mã HTML (Ace Code Editor)';
  const edId = 'ace_' + textareaId;

  wrap.innerHTML = `
    <div class="vnchip-ace-header">
      <div class="ace-badge-tag">
        <i class="fa-brands fa-html5" style="color: #F97316;"></i>
        <span>${escapeHtmlLive(titleText)}</span>
        <span class="ace-badge-tag-pill"><i class="fa-solid fa-palette me-1"></i>Tô màu HTML</span>
      </div>
      <div class="ace-tools">
        <button type="button" class="ace-btn-tool" onclick="toggleAceLiveTheme('${textareaId}')" title="Chuyển đổi giao diện Sáng / Tối">
          <i class="fa-solid fa-circle-half-stroke"></i> Sáng / Tối
        </button>
        <button type="button" class="ace-btn-tool" onclick="toggleAceLiveHeight('${textareaId}', ${heightPx || 180})" title="Mở rộng / Thu gọn chiều cao">
          <i class="fa-solid fa-up-right-and-down-left-from-center"></i> Thu phóng
        </button>
      </div>
    </div>
    <div id="${edId}" class="vnchip-ace-container" style="height: ${heightPx || 180}px;"></div>
    <div class="vnchip-ace-footer-hint">
      <span><i class="fa-solid fa-circle-info text-primary me-1"></i> Mã HTML được tô màu: thẻ đỏ/hồng, thuộc tính vàng cam, chuỗi xanh lá. Hỗ trợ <kbd>Ctrl+F</kbd> tìm kiếm, <kbd>Ctrl+S</kbd> để lưu nhanh.</span>
      <span class="font-monospace">Ace Editor</span>
    </div>
  `;

  textarea.parentNode.insertBefore(wrap, textarea.nextSibling);

  // 3. Initialize Ace instance
  const editor = ace.edit(edId);
  editor.setTheme("ace/theme/tomorrow_night");
  editor.session.setMode("ace/mode/html");
  editor.setValue(textarea.value || '', -1);
  editor.setOptions({
    fontSize: "13.5px",
    fontFamily: "'JetBrains Mono', 'Fira Code', 'Consolas', 'Courier New', monospace",
    wrap: true,
    showPrintMargin: false,
    tabSize: 2,
    useSoftTabs: true,
    highlightActiveLine: true
  });

  // Ctrl+S quick save shortcut
  editor.commands.addCommand({
    name: 'saveLiveModal',
    bindKey: { win: 'Ctrl-S', mac: 'Command-S' },
    exec: function(ed) {
      flushLiveAceEditors();
      const modal = ed.container.closest('.vnchip-live-modal');
      if (modal) {
        const saveBtn = modal.querySelector('.vnchip-btn-primary[id^="btnSaveLive"]');
        if (saveBtn) saveBtn.click();
      }
    }
  });

  // 4. Sync on change
  editor.session.on('change', function() {
    textarea.value = editor.getValue();
  });

  window._liveAceEditors[textareaId] = editor;
  return editor;
}

function toggleAceLiveTheme(textareaId) {
  const editor = window._liveAceEditors[textareaId];
  if (!editor) return;
  const current = editor.getTheme();
  if (current.includes('tomorrow_night')) {
    editor.setTheme('ace/theme/chrome');
  } else {
    editor.setTheme('ace/theme/tomorrow_night');
  }
}

function toggleAceLiveHeight(textareaId, defaultHeight) {
  const edDiv = document.getElementById('ace_' + textareaId);
  const editor = window._liveAceEditors[textareaId];
  if (!edDiv || !editor) return;
  const curHeight = parseInt(edDiv.style.height || defaultHeight, 10);
  if (curHeight <= defaultHeight + 20) {
    edDiv.style.height = (defaultHeight + 220) + 'px';
  } else {
    edDiv.style.height = defaultHeight + 'px';
  }
  editor.resize();
}

function flushLiveAceEditors() {
  if (!window._liveAceEditors) return;
  for (let id in window._liveAceEditors) {
    const ta = document.getElementById(id);
    if (ta && window._liveAceEditors[id]) {
      ta.value = window._liveAceEditors[id].getValue();
    }
  }
}

function resizeAllLiveAceEditors() {
  if (!window._liveAceEditors) return;
  for (let id in window._liveAceEditors) {
    try {
      window._liveAceEditors[id].resize();
    } catch(e) {}
  }
}

function clearLiveAceEditor(textareaId) {
  const ta = document.getElementById(textareaId);
  if (ta) ta.value = '';
  if (window._liveAceEditors && window._liveAceEditors[textareaId]) {
    window._liveAceEditors[textareaId].setValue('', -1);
  }
}

function initLiveAceEditorsAll() {
  if (typeof ace === 'undefined') {
    setTimeout(initLiveAceEditorsAll, 120);
    return;
  }
  // 1. Footer Nav & Copyright
  initLiveAceEditor('inputLiveFooterNav', 260, 'Mã HTML Cấu trúc 3 Cột Menu Điều Hướng');
  initLiveAceEditor('inputLiveFooterCopyright', 75, 'Mã HTML Bản quyền Footer (Copyright)');

  // 2. Sustainable Page
  initLiveAceEditor('inputLiveSusEsg', 160, 'Mã HTML Trách Nhiệm Xã Hội (ESG)');
  initLiveAceEditor('inputLiveSusTalentsIntro', 150, 'Mã HTML Đoạn Giới Thiệu Nhân Tài (Talents Intro)');
  initLiveAceEditor('inputLiveSusSecurity', 160, 'Mã HTML Cam Kết Bảo Mật (Confidentiality Statement)');
  initLiveAceEditor('inputLiveSusCleanCustomHtml', 200, 'Mã HTML Tùy Biến Clean Room (section_sustainable_talent)');

  // 3. Business Page
  initLiveAceEditor('inputLiveBizTech', 190, 'Mã HTML 3 Trụ Cột Công Nghệ (Technology Capsule)');
  initLiveAceEditor('inputLiveBizEngagement', 160, 'Mã HTML Mô Hình Hợp Tác (Engagement Models)');

  // 4. Leadership & About Us (All Sections)
  initLiveAceEditor('inputLiveFounderTitle', 70, 'Mã HTML Tiêu Đề Thông Điệp (Founder Title - <br>)');
  const leadHtmlEd = initLiveAceEditor('inputLiveLeadCustomHtml', 240, 'Mã HTML Tùy Biến Leadership (section_about_leadership)');
  if (leadHtmlEd && !leadHtmlEd._dirtyTracked) {
    leadHtmlEd._dirtyTracked = true;
    window._leadHtmlDirty = false;
    leadHtmlEd.session.on('change', function() { window._leadHtmlDirty = true; });
  }
  initLiveAceEditor('inputLiveAboutStory', 220, 'Mã HTML Hành Trình VNCHIP Journey (section_about_story)');
  initLiveAceEditor('inputLiveAboutVision', 220, 'Mã HTML Ảnh Trụ Sở & Giới Thiệu Chi Tiết (section_about_vision)');
  initLiveAceEditor('inputLiveAboutTimelineHtml', 240, 'Mã HTML Tùy Biến Timeline (section_about_timeline)');
  initLiveAceEditor('inputLiveAboutVisionCardsHtml', 240, 'Mã HTML Tùy Biến Vision/Mission/Core Values (section_about_vision_cards)');
  initLiveAceEditor('inputLiveConductHtml', 240, 'Mã HTML Tùy Biến Code of Conduct (section_about_conduct)');

  // 5. Technology Page
  initLiveAceEditor('inputLiveTechIntro', 240, 'Mã HTML Chiplet Disaggregation (section_tech_intro)');
  initLiveAceEditor('inputLiveTechFeatures', 240, 'Mã HTML Interconnect Standards (section_tech_features)');
  initLiveAceEditor('inputLiveTechAi', 240, 'Mã HTML AI-Centric SoC Building (section_tech_ai)');
}

function resolveLiveAssetUrl(val) {
  if (!val) return '';
  if (val.startsWith('http://') || val.startsWith('https://') || val.startsWith('/')) {
    return val;
  }
  if (val.startsWith('uploads/') || val.startsWith('assets/')) {
    return '<?= BASE_URL ?>' + val;
  }
  return '<?= asset('') ?>' + val;
}

// Function to handle instant image upload from user's computer
function handleLiveImageUpload(fileInput, targetInputId, previewImgId, settingKey, liveDomImgId) {
  if (!fileInput.files || !fileInput.files[0]) return;
  const file = fileInput.files[0];
  
  const statusEl = document.getElementById('status_' + targetInputId);
  if (statusEl) {
    statusEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-primary"></i> Đang tải lên...';
  }

  const formData = new FormData();
  formData.append('action', 'upload_image');
  formData.append('image', file);
  formData.append('setting_key', settingKey);
  formData.append('subdir', 'branding');
  formData.append('prefix', settingKey || 'live_img');

  fetch('<?= admin_url("ajax/live-edit.php") ?>', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(res => {
    if (res.success) {
      // 1. Update text input
      const targetInput = document.getElementById(targetInputId);
      if (targetInput) {
        targetInput.value = res.path;
      }
      
      // 2. Update modal preview thumbnail
      const previewImg = document.getElementById(previewImgId);
      if (previewImg) {
        previewImg.src = res.full_url;
      }
      
      // 3. Update live DOM on the website immediately (supports both <img> and CSS background-image)
      if (liveDomImgId) {
        const liveDomEl = document.getElementById(liveDomImgId);
        if (liveDomEl) {
          if (liveDomEl.tagName === 'IMG') {
            liveDomEl.src = res.full_url;
          } else {
            liveDomEl.style.backgroundImage = "url('" + res.full_url + "')";
          }
        }
      }
      
      if (statusEl) {
        statusEl.innerHTML = '<span class="text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Đã thay ảnh!</span>';
        setTimeout(() => { if (statusEl) statusEl.innerHTML = ''; }, 3500);
      }
      
      const toast = document.getElementById('live-edit-toast');
      const toastText = document.getElementById('live-edit-toast-text');
      if (toast && toastText) {
        toastText.innerText = '✓ Đã tải ảnh lên và thay thế thành công!';
        toast.style.borderLeftColor = '#10B981';
        toast.style.display = 'inline-flex';
        setTimeout(() => { toast.style.display = 'none'; }, 3500);
      }
    } else {
      alert(res.error || 'Lỗi khi tải ảnh lên.');
      if (statusEl) statusEl.innerHTML = '';
    }
  })
  .catch(err => {
    alert('Lỗi kết nối máy chủ: ' + err);
    if (statusEl) statusEl.innerHTML = '';
  });
}

// Special upload helper for About Lobby photo (also syncs <img class="overview-img"> inside Ace HTML)
function handleLiveAboutLobbyUpload(fileInput) {
  if (!fileInput.files || !fileInput.files[0]) return;
  const file = fileInput.files[0];
  const statusEl = document.getElementById('status_inputLiveAboutLobbyPhoto');
  if (statusEl) statusEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-primary"></i> Đang tải lên...';

  const formData = new FormData();
  formData.append('action', 'upload_image');
  formData.append('image', file);
  formData.append('setting_key', 'about_lobby_photo');
  formData.append('subdir', 'branding');
  formData.append('prefix', 'about_lobby');

  fetch('<?= admin_url("ajax/live-edit.php") ?>', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(res => {
    if (res.success) {
      const targetInput = document.getElementById('inputLiveAboutLobbyPhoto');
      if (targetInput) targetInput.value = res.path;
      const previewImg = document.getElementById('preview_about_lobby_img');
      if (previewImg) previewImg.src = res.full_url;

      // Update live DOM image
      const domImg = document.getElementById('live-about-lobby-img') || document.querySelector('#live-about-vision-block img.overview-img');
      if (domImg) domImg.src = res.full_url;

      // Sync img src inside section_about_vision Ace editor if present
      if (window._liveAceEditors && window._liveAceEditors['inputLiveAboutVision']) {
        const ed = window._liveAceEditors['inputLiveAboutVision'];
        const curHtml = ed.getValue();
        const updatedHtml = curHtml.replace(/(<img[^>]*\bsrc=")[^"]*(")/i, '$1' + res.full_url + '$2');
        if (updatedHtml !== curHtml) {
          ed.setValue(updatedHtml, -1);
          const ta = document.getElementById('inputLiveAboutVision');
          if (ta) ta.value = updatedHtml;
        }
      }

      if (statusEl) {
        statusEl.innerHTML = '<span class="text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Đã thay ảnh Trụ sở!</span>';
        setTimeout(() => { if (statusEl) statusEl.innerHTML = ''; }, 3500);
      }
    } else {
      alert(res.error || 'Lỗi khi tải ảnh lên.');
      if (statusEl) statusEl.innerHTML = '';
    }
  })
  .catch(err => {
    alert('Lỗi kết nối máy chủ: ' + err);
    if (statusEl) statusEl.innerHTML = '';
  });
}

function updateLivePreview(val, previewImgId) {
  const previewImg = document.getElementById(previewImgId);
  if (!previewImg || !val) return;
  previewImg.src = resolveLiveAssetUrl(val);
}

// ===================================================
// DYNAMIC REPEATER BUILDERS (About Us: Timeline, Vision, Conduct)
// ===================================================
const LIVE_VISION_SVG_PRESETS = {
  eye: '<svg class="vision-icon-svg" viewBox="0 0 128 64" fill="none" stroke="currentColor" stroke-width="2.6" aria-hidden="true"><ellipse cx="64" cy="32" rx="60" ry="19"/><circle cx="64" cy="32" r="15"/><circle cx="64" cy="32" r="6" fill="currentColor" stroke="none"/><circle cx="104" cy="16" r="6.5" fill="currentColor" stroke="none"/><circle cx="22" cy="47" r="5.5" fill="currentColor" stroke="none"/></svg>',
  target: '<svg class="vision-icon-svg" viewBox="0 0 64 64" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="30" cy="34" r="27" stroke-width="2" stroke-dasharray="2 3.2"/><circle cx="30" cy="34" r="21" stroke-width="2"/><circle cx="30" cy="34" r="15" stroke-width="1.6"/><circle cx="30" cy="34" r="9" stroke-width="1.6"/><circle cx="30" cy="34" r="3.2" fill="currentColor" stroke="none"/><path d="M30 34 L52 9" stroke-width="2.4" stroke-linecap="round"/><circle cx="53" cy="8" r="3.6" fill="currentColor" stroke="none"/></svg>',
  atom: '<svg class="vision-icon-svg" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><ellipse cx="32" cy="32" rx="29" ry="10"/><ellipse cx="32" cy="32" rx="29" ry="10" transform="rotate(60 32 32)"/><ellipse cx="32" cy="32" rx="29" ry="10" transform="rotate(-60 32 32)"/><circle cx="32" cy="32" r="6" fill="currentColor" stroke="none"/><circle cx="61" cy="32" r="3.4" fill="currentColor" stroke="none"/><circle cx="17.5" cy="7" r="3.4" fill="currentColor" stroke="none"/><circle cx="17.5" cy="57" r="3.4" fill="currentColor" stroke="none"/></svg>',
  chip: '<svg class="vision-icon-svg" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><rect x="18" y="18" width="28" height="28" rx="4"/><rect x="25" y="25" width="14" height="14" rx="2"/><path d="M24 18V10M32 18V10M40 18V10M24 46V54M32 46V54M40 46V54M18 24H10M18 32H10M18 40H10M46 24H54M46 32H54M46 40H54" stroke-linecap="round"/></svg>',
  shield: '<svg class="vision-icon-svg" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M32 8L52 16V31C52 44 43.5 53 32 57C20.5 53 12 44 12 31V16L32 8Z"/><path d="M23 32L29 38L42 25" stroke-linecap="round" stroke-linejoin="round"/></svg>'
};

// 1. Timeline Repeater
function getLiveTimelineItems() {
  const hidden = document.getElementById('inputLiveTimelineJson');
  try { return JSON.parse(hidden ? hidden.value : '[]') || []; } catch(e) { return []; }
}
function collectLiveTimelineItems() {
  const list = document.getElementById('liveTimelineRepeaterList');
  if (!list) return [];
  const items = [];
  list.querySelectorAll('.vnchip-repeater-item').forEach(row => {
    const year = (row.querySelector('.tl-year-input')?.value || '').trim();
    const desc = (row.querySelector('.tl-desc-input')?.value || '').trim();
    if (year !== '' || desc !== '') items.push({ year, desc });
  });
  const hidden = document.getElementById('inputLiveTimelineJson');
  if (hidden) hidden.value = JSON.stringify(items);
  return items;
}
function renderLiveTimelineRepeater(items) {
  const list = document.getElementById('liveTimelineRepeaterList');
  if (!list) return;
  const data = Array.isArray(items) ? items : getLiveTimelineItems();
  list.innerHTML = '';
  data.forEach((item, idx) => {
    const div = document.createElement('div');
    div.className = 'vnchip-repeater-item';
    div.innerHTML = `
      <div class="vnchip-repeater-head">
        <span class="vnchip-repeater-num"><i class="fa-solid fa-circle-dot text-primary me-1"></i> Mốc #${idx + 1}</span>
        <div class="vnchip-repeater-actions">
          <button type="button" class="vnchip-mini-btn" onclick="moveLiveTimelineRow(${idx}, -1)" title="Di chuyển lên"><i class="fa-solid fa-arrow-up"></i></button>
          <button type="button" class="vnchip-mini-btn" onclick="moveLiveTimelineRow(${idx}, 1)" title="Di chuyển xuống"><i class="fa-solid fa-arrow-down"></i></button>
          <button type="button" class="vnchip-mini-btn danger" onclick="removeLiveTimelineRow(${idx})" title="Xóa mốc này"><i class="fa-solid fa-trash"></i> Xóa</button>
        </div>
      </div>
      <div style="display: grid; grid-template-columns: 160px 1fr; gap: 10px;">
        <div>
          <label class="vnchip-form-label" style="font-size: 0.76rem;">Năm / Giai đoạn:</label>
          <input type="text" class="vnchip-form-input tl-year-input" value="${escapeHtmlLive(item.year || '')}" placeholder="VD: 2025">
        </div>
        <div>
          <label class="vnchip-form-label" style="font-size: 0.76rem;">Nội dung sự kiện:</label>
          <input type="text" class="vnchip-form-input tl-desc-input" value="${escapeHtmlLive(item.desc || '')}" placeholder="Mô tả dấu mốc phát triển...">
        </div>
      </div>
    `;
    list.appendChild(div);
  });
}
function addLiveTimelineRow() {
  const items = collectLiveTimelineItems();
  items.push({ year: '2026', desc: 'New milestone description...' });
  renderLiveTimelineRepeater(items);
  collectLiveTimelineItems();
}
function moveLiveTimelineRow(idx, dir) {
  const items = collectLiveTimelineItems();
  const target = idx + dir;
  if (target < 0 || target >= items.length) return;
  const tmp = items[idx]; items[idx] = items[target]; items[target] = tmp;
  renderLiveTimelineRepeater(items);
  collectLiveTimelineItems();
}
function removeLiveTimelineRow(idx) {
  const items = collectLiveTimelineItems();
  if (items.length <= 1) { alert('Cần giữ lại ít nhất 1 mốc thời gian.'); return; }
  items.splice(idx, 1);
  renderLiveTimelineRepeater(items);
  collectLiveTimelineItems();
}

// 2. Vision / Mission / Core Values Repeater
function getLiveVisionCards() {
  const hidden = document.getElementById('inputLiveVisionCardsJson');
  try { return JSON.parse(hidden ? hidden.value : '[]') || []; } catch(e) { return []; }
}
function collectLiveVisionCards() {
  const list = document.getElementById('liveVisionCardsRepeaterList');
  if (!list) return [];
  const items = [];
  list.querySelectorAll('.vnchip-repeater-item').forEach(row => {
    const icon  = row.querySelector('.vc-icon-select')?.value || 'eye';
    const title = (row.querySelector('.vc-title-input')?.value || '').trim();
    const text  = (row.querySelector('.vc-text-input')?.value || '').trim();
    if (title !== '' || text !== '') items.push({ icon, title, text });
  });
  const hidden = document.getElementById('inputLiveVisionCardsJson');
  if (hidden) hidden.value = JSON.stringify(items);
  return items;
}
function renderLiveVisionCardsRepeater(items) {
  const list = document.getElementById('liveVisionCardsRepeaterList');
  if (!list) return;
  const data = Array.isArray(items) ? items : getLiveVisionCards();
  list.innerHTML = '';
  data.forEach((item, idx) => {
    const ic = item.icon || 'eye';
    const div = document.createElement('div');
    div.className = 'vnchip-repeater-item';
    div.innerHTML = `
      <div class="vnchip-repeater-head">
        <span class="vnchip-repeater-num"><i class="fa-solid fa-star text-info me-1"></i> Thẻ #${idx + 1}</span>
        <div class="vnchip-repeater-actions">
          <button type="button" class="vnchip-mini-btn" onclick="moveLiveVisionCardRow(${idx}, -1)" title="Di chuyển lên"><i class="fa-solid fa-arrow-up"></i></button>
          <button type="button" class="vnchip-mini-btn" onclick="moveLiveVisionCardRow(${idx}, 1)" title="Di chuyển xuống"><i class="fa-solid fa-arrow-down"></i></button>
          <button type="button" class="vnchip-mini-btn danger" onclick="removeLiveVisionCardRow(${idx})" title="Xóa thẻ"><i class="fa-solid fa-trash"></i> Xóa</button>
        </div>
      </div>
      <div style="display: grid; grid-template-columns: 180px 1fr; gap: 10px; margin-bottom: 8px;">
        <div>
          <label class="vnchip-form-label" style="font-size: 0.76rem;">Biểu tượng SVG:</label>
          <select class="vnchip-form-input vc-icon-select">
            <option value="eye" ${ic === 'eye' ? 'selected' : ''}>Vision (Mắt / Quỹ đạo)</option>
            <option value="target" ${ic === 'target' ? 'selected' : ''}>Mission (Mục tiêu)</option>
            <option value="atom" ${ic === 'atom' ? 'selected' : ''}>Core Values (Nguyên tử)</option>
            <option value="chip" ${ic === 'chip' ? 'selected' : ''}>Vi mạch (Chip IC)</option>
            <option value="shield" ${ic === 'shield' ? 'selected' : ''}>Khiên Bảo Vệ (Shield)</option>
          </select>
        </div>
        <div>
          <label class="vnchip-form-label" style="font-size: 0.76rem;">Tiêu đề thẻ:</label>
          <input type="text" class="vnchip-form-input vc-title-input" style="font-weight: 700;" value="${escapeHtmlLive((item.title || '').replace(/<br\s*\/?>/gi, ' '))}" placeholder="VD: VISION">
        </div>
      </div>
      <div>
        <label class="vnchip-form-label" style="font-size: 0.76rem;">Nội dung mô tả:</label>
        <textarea rows="3" class="vnchip-form-textarea vc-text-input">${escapeHtmlLive(item.text || '')}</textarea>
      </div>
    `;
    list.appendChild(div);
  });
}
function addLiveVisionCardRow() {
  const items = collectLiveVisionCards();
  items.push({ icon: 'chip', title: 'NEW PILLAR', text: 'Description of this core value or pillar...' });
  renderLiveVisionCardsRepeater(items);
  collectLiveVisionCards();
}
function moveLiveVisionCardRow(idx, dir) {
  const items = collectLiveVisionCards();
  const target = idx + dir;
  if (target < 0 || target >= items.length) return;
  const tmp = items[idx]; items[idx] = items[target]; items[target] = tmp;
  renderLiveVisionCardsRepeater(items);
  collectLiveVisionCards();
}
function removeLiveVisionCardRow(idx) {
  const items = collectLiveVisionCards();
  if (items.length <= 1) { alert('Cần giữ lại ít nhất 1 thẻ.'); return; }
  items.splice(idx, 1);
  renderLiveVisionCardsRepeater(items);
  collectLiveVisionCards();
}

// 3. Code of Conduct Repeater
function getLiveConductCards() {
  const hidden = document.getElementById('inputLiveConductCardsJson');
  try { return JSON.parse(hidden ? hidden.value : '[]') || []; } catch(e) { return []; }
}
function collectLiveConductCards() {
  const list = document.getElementById('liveConductCardsRepeaterList');
  if (!list) return [];
  const items = [];
  list.querySelectorAll('.vnchip-repeater-item').forEach(row => {
    const title = (row.querySelector('.cc-title-input')?.value || '').trim();
    const text  = (row.querySelector('.cc-text-input')?.value || '').trim();
    if (title !== '' || text !== '') items.push({ title, text });
  });
  const hidden = document.getElementById('inputLiveConductCardsJson');
  if (hidden) hidden.value = JSON.stringify(items);
  return items;
}
function renderLiveConductCardsRepeater(items) {
  const list = document.getElementById('liveConductCardsRepeaterList');
  if (!list) return;
  const data = Array.isArray(items) ? items : getLiveConductCards();
  list.innerHTML = '';
  data.forEach((item, idx) => {
    const cleanTitle = (item.title || '').replace(/<br\s*\/?>/gi, '\n');
    const div = document.createElement('div');
    div.className = 'vnchip-repeater-item';
    div.innerHTML = `
      <div class="vnchip-repeater-head">
        <span class="vnchip-repeater-num"><i class="fa-solid fa-scale-balanced text-danger me-1"></i> Quy tắc #${idx + 1}</span>
        <div class="vnchip-repeater-actions">
          <button type="button" class="vnchip-mini-btn" onclick="moveLiveConductCardRow(${idx}, -1)" title="Di chuyển lên"><i class="fa-solid fa-arrow-up"></i></button>
          <button type="button" class="vnchip-mini-btn" onclick="moveLiveConductCardRow(${idx}, 1)" title="Di chuyển xuống"><i class="fa-solid fa-arrow-down"></i></button>
          <button type="button" class="vnchip-mini-btn danger" onclick="removeLiveConductCardRow(${idx})" title="Xóa quy tắc"><i class="fa-solid fa-trash"></i> Xóa</button>
        </div>
      </div>
      <div style="display: grid; grid-template-columns: 240px 1fr; gap: 10px;">
        <div>
          <label class="vnchip-form-label" style="font-size: 0.76rem;">Tiêu đề thẻ (Enter để xuống dòng):</label>
          <textarea rows="3" class="vnchip-form-textarea cc-title-input" style="font-weight: 700;">${escapeHtmlLive(cleanTitle)}</textarea>
        </div>
        <div>
          <label class="vnchip-form-label" style="font-size: 0.76rem;">Nội dung quy tắc ứng xử:</label>
          <textarea rows="3" class="vnchip-form-textarea cc-text-input">${escapeHtmlLive(item.text || '')}</textarea>
        </div>
      </div>
    `;
    list.appendChild(div);
  });
}
function addLiveConductCardRow() {
  const items = collectLiveConductCards();
  items.push({ title: "NEW CONDUCT\nSTANDARD", text: 'Describe the ethical commitment or standard here...' });
  renderLiveConductCardsRepeater(items);
  collectLiveConductCards();
}
function moveLiveConductCardRow(idx, dir) {
  const items = collectLiveConductCards();
  const target = idx + dir;
  if (target < 0 || target >= items.length) return;
  const tmp = items[idx]; items[idx] = items[target]; items[target] = tmp;
  renderLiveConductCardsRepeater(items);
  collectLiveConductCards();
}
function removeLiveConductCardRow(idx) {
  const items = collectLiveConductCards();
  if (items.length <= 1) { alert('Cần giữ lại ít nhất 1 thẻ Quy tắc ứng xử.'); return; }
  items.splice(idx, 1);
  renderLiveConductCardsRepeater(items);
  collectLiveConductCards();
}

(function() {
  const STORAGE_KEY = 'vnchip_live_edit_mode';

  function isLiveEditActive() {
    return localStorage.getItem(STORAGE_KEY) === '1';
  }

  function showToast(msg, isSuccess = true) {
    const toast = document.getElementById('live-edit-toast');
    const toastText = document.getElementById('live-edit-toast-text');
    if (!toast || !toastText) return;
    toastText.innerText = msg;
    toast.style.borderLeftColor = isSuccess ? '#10B981' : '#F59E0B';
    toast.style.display = 'inline-flex';
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => {
      toast.style.display = 'none';
    }, 3500);
  }

  // Inject floating action badges into editable sections (Idempotent)
  function injectEditBadges() {
    // 1. Stats Bar (Homepage)
    const statsBar = document.querySelector('.stats-bar, #live-stats-bar');
    if (statsBar && !statsBar.querySelector('.badge-edit-stats')) {
      statsBar.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-stats';
      badge.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Sửa 3 Chỉ số vàng';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditStats');
      };
      statsBar.appendChild(badge);
    }

    // 2. Pill Banner (Homepage)
    const pill = document.querySelector('.pill-banner-container, #live-pill-banner');
    if (pill && !pill.querySelector('.badge-edit-pill')) {
      pill.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-pill';
      badge.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Sửa Pill Banner';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditPill');
      };
      pill.appendChild(badge);
    }

    // 3. Hero Video / Banner (Homepage & Interior pages)
    const aboutHero = document.querySelector('.about-hero-section');
    if (aboutHero && !aboutHero.querySelector('.badge-edit-hero')) {
      aboutHero.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-hero badge-edit-about-hero';
      badge.innerHTML = '<i class="fa-solid fa-image"></i> Sửa Hero Banner (Ảnh & Tiêu đề)';
      badge.title = 'Thay ảnh nền và tiêu đề Hero trang About Us trực tiếp';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditAboutHero');
      };
      aboutHero.appendChild(badge);
    } else {
      const hero = document.querySelector('.hero-section, .page-hero-banner, .biz-hero-section, .sus-hero-section');
      if (hero && !hero.querySelector('.badge-edit-hero')) {
        hero.classList.add('live-editable-wrap');
        const badge = document.createElement('a');
        badge.href = '<?= admin_url("banners.php") ?>';
        badge.target = '_blank';
        badge.rel = 'noopener noreferrer';
        badge.className = 'live-edit-badge badge-edit-hero';
        badge.innerHTML = '<i class="fa-solid fa-image"></i> Sửa Video & Banner Hero';
        badge.title = 'Chuyển đến màn hình quản lý Banners (Mở tab mới)';
        hero.appendChild(badge);
      }
    }

    // 4. Business Section (Homepage)
    const biz = document.querySelector('.business-section, #business');
    if (biz && !biz.querySelector('.badge-edit-biz')) {
      biz.classList.add('live-editable-wrap');
      const badge = document.createElement('a');
      badge.href = '<?= admin_url("page-sections.php?tab=business") ?>';
      badge.target = '_blank';
      badge.rel = 'noopener noreferrer';
      badge.className = 'live-edit-badge badge-edit-biz';
      badge.innerHTML = '<i class="fa-solid fa-cubes"></i> Sửa Khối Business';
      badge.title = 'Chỉnh sửa nội dung khối Business trong Admin (Mở tab mới)';
      biz.appendChild(badge);
    }

    // 5. Media Center Section (Homepage)
    const media = document.querySelector('.media-section, #media');
    if (media && !media.querySelector('.badge-edit-media')) {
      media.classList.add('live-editable-wrap');
      const badge = document.createElement('a');
      badge.href = '<?= admin_url("articles.php") ?>';
      badge.target = '_blank';
      badge.rel = 'noopener noreferrer';
      badge.className = 'live-edit-badge badge-edit-media';
      badge.innerHTML = '<i class="fa-solid fa-newspaper"></i> Quản lý Bài viết & Tin tức';
      badge.title = 'Quản lý bài viết và tin tức (Mở tab mới)';
      media.appendChild(badge);
    }

    // 6. Sustainable Sections (sustainable.php & homepage)
    const susEsg = document.querySelector('#social-responsibilities, .sus-social-section, .sustainable-section, #sustainable');
    if (susEsg && !susEsg.querySelector('.badge-edit-sus-esg')) {
      susEsg.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-sus-esg';
      badge.innerHTML = '<i class="fa-solid fa-hand-holding-heart"></i> Sửa Trách Nhiệm Xã Hội (ESG)';
      badge.title = 'Chỉnh sửa nội dung Trách nhiệm Xã hội';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditSustainable', 'sus-tab-esg');
      };
      susEsg.appendChild(badge);
    }

    const susClean = document.querySelector('#clean-room, .about-cleanroom-section');
    if (susClean && !susClean.querySelector('.badge-edit-sus-clean')) {
      susClean.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-sus-clean';
      badge.innerHTML = '<i class="fa-solid fa-fan"></i> Sửa Khối Clean Room';
      badge.title = 'Chỉnh sửa tiêu đề và mô tả Phòng sạch Clean Room';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditSustainable', 'sus-tab-cleanroom');
      };
      susClean.appendChild(badge);
    }

    const susTalents = document.querySelector('#talents-development, .sus-talents-section');
    if (susTalents && !susTalents.querySelector('.badge-edit-sus-talents')) {
      susTalents.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-sus-talents';
      badge.innerHTML = '<i class="fa-solid fa-graduation-cap"></i> Sửa Đào Tạo & Programs';
      badge.title = 'Chỉnh sửa đoạn giới thiệu và đường link 2 chương trình đào tạo';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditSustainable', 'sus-tab-talents');
      };
      susTalents.appendChild(badge);
    }

    const susSec = document.querySelector('#confidentiality, .sus-confidentiality-section');
    if (susSec && !susSec.querySelector('.badge-edit-sus-sec')) {
      susSec.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-sus-sec';
      badge.innerHTML = '<i class="fa-solid fa-shield-halved"></i> Sửa Cam Kết Bảo Mật';
      badge.title = 'Chỉnh sửa cam kết bảo mật & quyền riêng tư';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditSustainable', 'sus-tab-security');
      };
      susSec.appendChild(badge);
    }

    // 7. Technology Page Sections (technology.php)
    const techIntro = document.querySelector('#chiplet-disaggregation');
    if (techIntro && !techIntro.querySelector('.badge-edit-tech-intro')) {
      techIntro.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-tech-intro';
      badge.innerHTML = '<i class="fa-solid fa-microchip"></i> Sửa Khối Chiplet Tech';
      badge.title = 'Chỉnh sửa mã HTML khối Chiplet Disaggregation';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditTechnology', 'tech-tab-chiplet');
      };
      techIntro.appendChild(badge);
    }

    const techFeat = document.querySelector('#interconnect-standards');
    if (techFeat && !techFeat.querySelector('.badge-edit-tech-feat')) {
      techFeat.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-tech-feat';
      badge.innerHTML = '<i class="fa-solid fa-network-wired"></i> Sửa Khối Interconnect Tech';
      badge.title = 'Chỉnh sửa mã HTML khối Interconnect Standards';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditTechnology', 'tech-tab-interconnect');
      };
      techFeat.appendChild(badge);
    }

    const techAi = document.querySelector('#ai-centric-soc');
    if (techAi && !techAi.querySelector('.badge-edit-tech-ai')) {
      techAi.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-tech-ai';
      badge.innerHTML = '<i class="fa-solid fa-brain"></i> Sửa Khối AI-Centric SoC';
      badge.title = 'Mã HTML khối AI-Centric SoC Building';
      badge.onclick = (e) => { e.preventDefault(); e.stopPropagation(); openLiveModal('modalLiveEditTechnology', 'tech-tab-ai'); };
      techAi.appendChild(badge);
    }

    // 8. About Page Sections (about-us.php — 100% Every Section Live Editable!)
    // 8a. Overview / VNCHIP Journey Intro & Lobby
    const aboutOverview = document.getElementById('live-about-overview-container') || document.querySelector('.about-overview-section, #overview');
    if (aboutOverview && !aboutOverview.querySelector('.badge-edit-about')) {
      aboutOverview.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-about';
      badge.innerHTML = '<i class="fa-solid fa-compass"></i> Sửa Giới Thiệu & Ảnh Trụ Sở';
      badge.title = 'Chỉnh sửa tiêu đề, câu chuyện VNCHIP Journey và ảnh trụ sở';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditAboutOverview', 'about-tab-story');
      };
      aboutOverview.appendChild(badge);
    }

    // 8b. Journey Timeline (#journey)
    const aboutTimeline = document.getElementById('live-about-timeline-container') || document.querySelector('#journey, .about-journey-section');
    if (aboutTimeline && !aboutTimeline.querySelector('.badge-edit-about-timeline')) {
      aboutTimeline.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-about-timeline';
      badge.innerHTML = '<i class="fa-solid fa-timeline"></i> Sửa Lịch Sử & Timeline';
      badge.title = 'Chỉnh sửa tiêu đề phụ và các mốc thời gian phát triển VNCHIP';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditAboutTimeline', 'timeline-tab-visual');
      };
      aboutTimeline.appendChild(badge);
    }

    // 8c. Vision, Mission & Core Values (#vision)
    const aboutVisionCards = document.getElementById('live-about-vision-cards-container') || document.querySelector('#vision, .about-vision-section');
    if (aboutVisionCards && !aboutVisionCards.querySelector('.badge-edit-about-vision')) {
      aboutVisionCards.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-about-vision';
      badge.innerHTML = '<i class="fa-solid fa-bullseye"></i> Sửa Vision, Mission & Core Values';
      badge.title = 'Chỉnh sửa tiêu đề, mô tả và 3 thẻ Tầm nhìn - Sứ mệnh - Giá trị cốt lõi';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditAboutVision', 'vision-tab-visual');
      };
      aboutVisionCards.appendChild(badge);
    }

    // 8d. Code of Conduct (#conduct)
    const aboutConduct = document.getElementById('live-about-conduct-container') || document.querySelector('#conduct, .about-conduct-section');
    if (aboutConduct && !aboutConduct.querySelector('.badge-edit-conduct')) {
      aboutConduct.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-conduct';
      badge.innerHTML = '<i class="fa-solid fa-scale-balanced"></i> Sửa Khối Code of Conduct';
      badge.title = 'Chỉnh sửa tiêu đề, mô tả và các thẻ Quy tắc ứng xử (Code of Conduct)';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditConduct', 'conduct-tab-visual');
      };
      aboutConduct.appendChild(badge);
    }

    // 9. Business Page Sections (business.php)
    const bizTech = document.getElementById('live-biz-tech-box') || document.querySelector('.biz-tech-box') || document.querySelector('#technology, .biz-tech-container');
    if (bizTech && !bizTech.querySelector('.badge-edit-biz-tech')) {
      bizTech.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-biz-tech';
      badge.innerHTML = '<i class="fa-solid fa-microchip"></i> Sửa Trụ Cột Công Nghệ';
      badge.title = 'Chỉnh sửa 3 Trụ cột Công nghệ: Chiplet, Interconnect, AI SoC';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditBusiness', 'biz-tab-tech');
      };
      bizTech.appendChild(badge);
    }

    const bizServices = document.querySelector('#live-services-wrap, #design-service, .services-grid');
    if (bizServices && !bizServices.querySelector('.badge-edit-biz-serv')) {
      bizServices.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-biz-serv';
      badge.innerHTML = '<i class="fa-solid fa-layer-group"></i> Sửa Dịch Vụ Thiết Kế & IP';
      badge.title = 'Tiêu đề 2 cột, bật/tắt khối, nội dung từng mục và từ điển tooltip';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditServices');
      };
      bizServices.appendChild(badge);
    }

    // 9b. Per-item "Sửa mục" buttons + per-keyword tooltip pens (defined in the block below)
    if (window.vnLiveServices) window.vnLiveServices.inject();

    // 9d. Partners Projects cards on business.php
    const bizProjects = document.getElementById('live-biz-projects');
    if (bizProjects && window.vnLiveProjects && !bizProjects.parentElement.querySelector('.badge-edit-biz-projects')) {
      const host = bizProjects.parentElement;
      host.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-biz-projects';
      badge.innerHTML = '<i class="fa-solid fa-diagram-project"></i> Sửa danh sách Projects';
      badge.title = 'Thêm/xoá/sắp xếp dự án, ảnh, tóm tắt và trang chi tiết';
      badge.onclick = (e) => { e.preventDefault(); e.stopPropagation(); window.vnLiveProjects.open(); };
      host.appendChild(badge);
    }

    // 9e. Live Text / Live Image pencils on every data-lt / data-lt-img element
    if (window.vnLiveText) window.vnLiveText.inject();

    // 9c. Talent / Interns program page (program-detail.php)
    const progWrap = document.getElementById('live-program-detail');
    if (progWrap && window.vnLiveProgram && !progWrap.querySelector('.badge-edit-program')) {
      progWrap.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-program';
      badge.innerHTML = '<i class="fa-solid fa-graduation-cap"></i> Sửa trang chương trình';
      badge.title = 'Tên, nhãn, giới thiệu, học phần, các bước và link Apply';
      badge.onclick = (e) => { e.preventDefault(); e.stopPropagation(); window.vnLiveProgram.open(); };
      progWrap.appendChild(badge);
    }

    // 10. Call to Action Banner (All pages)
    const ctaTarget = document.querySelector('#live-cta-box, .cta-box-new, #global-cta-section, .cta-section-new');
    if (ctaTarget && !ctaTarget.querySelector('.badge-edit-cta')) {
      ctaTarget.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-cta';
      badge.innerHTML = '<i class="fa-solid fa-bullhorn"></i> Sửa Call to Action';
      badge.title = 'Chỉnh sửa tiêu đề, nút bấm và ảnh nền Call to Action';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditCTA');
      };
      ctaTarget.appendChild(badge);
    }

    // 11. Footer Section - Main Badge
    const footerContainer = document.querySelector('#siteFooter > .container, footer.site-footer > .container');
    const footerMain = document.querySelector('footer.site-footer, #siteFooter');
    const footerTarget = footerContainer || footerMain;
    if (footerTarget && !footerTarget.querySelector('.badge-edit-footer')) {
      footerTarget.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-footer';
      badge.innerHTML = '<i class="fa-solid fa-shoe-prints"></i> Sửa Toàn Bộ Footer';
      badge.title = 'Quản lý & chỉnh sửa tất cả các thành phần trong chân trang (3 cột menu, thông tin, logo, bản quyền)';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditFooter');
      };
      footerTarget.appendChild(badge);
    }

    // 12. Footer Navigation Columns - Granular Badge
    const footerNavGrid = document.querySelector('#live-footer-nav-grid, .footer-nav-grid');
    if (footerNavGrid && !footerNavGrid.querySelector('.badge-edit-footer-nav')) {
      footerNavGrid.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-footer-nav';
      badge.innerHTML = '<i class="fa-solid fa-list-check"></i> Sửa 3 Cột Menu';
      badge.title = 'Chỉnh sửa tiêu đề và các đường link của 3 cột menu Footer';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditFooter', 'footer-tab-nav');
      };
      footerNavGrid.appendChild(badge);
    }

    // 13. Footer Brand & Partner Row - Granular Badge
    const footerBrandRow = document.querySelector('#live-footer-brand-row, .footer-brand-row');
    if (footerBrandRow && !footerBrandRow.querySelector('.badge-edit-footer-brand')) {
      footerBrandRow.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-footer-brand';
      badge.innerHTML = '<i class="fa-solid fa-handshake"></i> Sửa Logo & Liên hệ';
      badge.title = 'Chỉnh sửa logo đối tác, logo chính và thông tin liên hệ';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditFooter', 'footer-tab-partners');
      };
      footerBrandRow.appendChild(badge);
    }

    // 14. Leadership, Executive Leaders & Key Team Sections (about-us.php)
    const leadership = document.getElementById('live-leadership-founder-wrap') || document.getElementById('live-founder-message-box') || document.querySelector('.founder-message-box') || document.querySelector('#leadership, .about-leadership-section');
    if (leadership && !leadership.querySelector('.badge-edit-leadership')) {
      leadership.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-leadership';
      badge.innerHTML = '<i class="fa-solid fa-users-gear"></i> Sửa Mục Leadership & Founder';
      badge.title = 'Chỉnh sửa Thông điệp Sáng lập, ảnh chân dung, tiêu đề và đội ngũ Lãnh đạo';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditLeadership', 'lead-tab-founder');
      };
      leadership.appendChild(badge);
    }

    const executivesGrid = document.getElementById('live-executives-grid');
    if (executivesGrid && !executivesGrid.querySelector('.badge-edit-executives')) {
      executivesGrid.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-executives';
      badge.innerHTML = '<i class="fa-solid fa-user-tie"></i> Sửa Ban Lãnh Đạo (Executives)';
      badge.title = 'Quản lý danh sách và hồ sơ tiểu sử (Bio) các thành viên Ban Lãnh Đạo';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditLeadership', 'lead-tab-members');
      };
      executivesGrid.appendChild(badge);
    }

    const keyTeamWrap = document.getElementById('live-key-team-wrap');
    if (keyTeamWrap && !keyTeamWrap.querySelector('.badge-edit-key-team')) {
      keyTeamWrap.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-key-team';
      badge.innerHTML = '<i class="fa-solid fa-people-group"></i> Sửa Khối Key Team';
      badge.title = 'Chỉnh sửa tiêu đề, mô tả, bật/tắt hiển thị và danh sách thành viên Key Team';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditLeadership', 'lead-tab-keyteam');
      };
      keyTeamWrap.appendChild(badge);
    }

    // 15. Contact / Talk To Us Page (talk-to-us.php)
    const contactGrid = document.getElementById('live-contact-grid') || document.querySelector('.contact-main-grid');
    if (contactGrid && !contactGrid.querySelector('.badge-edit-contact')) {
      contactGrid.classList.add('live-editable-wrap');
      const badge = document.createElement('button');
      badge.type = 'button';
      badge.className = 'live-edit-badge badge-edit-contact';
      badge.innerHTML = '<i class="fa-solid fa-headset"></i> Sửa Thông Tin Liên Hệ & Trụ Sở';
      badge.title = 'Chỉnh sửa Trụ sở Việt Nam, USA, Hotline, Email và Tiêu đề trang';
      badge.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        openLiveModal('modalLiveEditContact');
      };
      contactGrid.appendChild(badge);
    }
  }

  // Master Synchronize Function: reads state and synchronizes DOM classes, switch state, and badges
  function syncLiveEditState() {
    const adminBar = document.getElementById('vnchip-admin-bar');
    if (!adminBar) return;

    // Ensure body padding for top bar
    document.body.classList.add('admin-bar-active');

    const toggle = document.getElementById('liveEditModeToggle');
    const stateText = document.getElementById('liveEditStateText');
    const isActive = isLiveEditActive();

    if (toggle) {
      toggle.checked = isActive;
    }
    if (stateText) {
      stateText.innerText = isActive ? 'ON' : 'OFF';
    }

    if (isActive) {
      document.body.classList.add('admin-edit-active');
      injectEditBadges();
    } else {
      document.body.classList.remove('admin-edit-active');
    }
  }

  // Bind Switch Controls
  function bindSwitchControls() {
    const toggle = document.getElementById('liveEditModeToggle');
    const switchWrap = document.getElementById('switchWrapBtn');

    if (toggle && !toggle._hasBoundEvents) {
      toggle._hasBoundEvents = true;
      toggle.addEventListener('change', function() {
        if (this.checked) {
          localStorage.setItem(STORAGE_KEY, '1');
          syncLiveEditState();
          showToast('⚡ Chế độ Live Edit đang BẬT. Nhấp vào các vùng có khung viền xanh để sửa trực tiếp.');
        } else {
          localStorage.setItem(STORAGE_KEY, '0');
          syncLiveEditState();
          showToast('Chế độ Live Edit đã TẮT.');
        }
      });
    }

    if (switchWrap && !switchWrap._hasBoundEvents) {
      switchWrap._hasBoundEvents = true;
      switchWrap.addEventListener('click', function(e) {
        if (e.target === toggle) return;
        e.preventDefault();
        e.stopPropagation();
        if (toggle) {
          toggle.checked = !toggle.checked;
          toggle.dispatchEvent(new Event('change'));
        }
      });
    }
  }

  // Bind Ajax Actions for 3 Golden Stats, Pill Banner, CTA & Footer
  function bindAjaxForms() {
    // 1. Save Stats
    const btnSaveStats = document.getElementById('btnSaveLiveStats');
    if (btnSaveStats && !btnSaveStats._hasBoundEvents) {
      btnSaveStats._hasBoundEvents = true;
      btnSaveStats.addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';

        const form = document.getElementById('formLiveEditStats');
        if (!form) return;
        const formData = new FormData(form);
        const dataObj = {};
        formData.forEach((val, key) => { dataObj[key] = val; });

        const postData = new FormData();
        postData.append('action', 'save_batch');
        postData.append('data', JSON.stringify(dataObj));

        fetch('<?= admin_url("ajax/live-edit.php") ?>', {
          method: 'POST',
          body: postData
        })
        .then(res => res.json())
        .then(res => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu thay đổi ngay';
          if (res.success) {
            const statItems = document.querySelectorAll('.stats-bar .stat-item');
            if (statItems.length >= 3) {
              statItems[0].querySelector('.stat-number').innerHTML = `${escapeHtml(dataObj.stat_years_number)} <span>${escapeHtml(dataObj.stat_years_label)}</span>`;
              statItems[0].querySelector('.stat-desc').innerText = dataObj.stat_years_desc;

              statItems[1].querySelector('.stat-number').innerHTML = `${escapeHtml(dataObj.stat_asic_number)} <span>${escapeHtml(dataObj.stat_asic_label)}</span>`;
              statItems[1].querySelector('.stat-desc').innerText = dataObj.stat_asic_desc;

              statItems[2].querySelector('.stat-number').innerHTML = `${escapeHtml(dataObj.stat_chips_number)} <span>${escapeHtml(dataObj.stat_chips_label)}</span>`;
              statItems[2].querySelector('.stat-desc').innerText = dataObj.stat_chips_desc;
            }

            closeLiveModal('modalLiveEditStats');
            showToast('✓ Đã cập nhật 3 Chỉ số vàng thành công!');
          } else {
            alert(res.error || 'Lỗi khi lưu chỉ số.');
          }
        })
        .catch(err => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu thay đổi ngay';
          alert('Lỗi kết nối máy chủ: ' + err);
        });
      });
    }

    // 2. Save Pill Banner
    const btnSavePill = document.getElementById('btnSaveLivePill');
    if (btnSavePill && !btnSavePill._hasBoundEvents) {
      btnSavePill._hasBoundEvents = true;
      btnSavePill.addEventListener('click', function() {
        const btn = this;
        const textVal = document.getElementById('inputLivePillText').value.trim();
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';

        const postData = new FormData();
        postData.append('action', 'save_setting');
        postData.append('key', 'homepage_pill_banner');
        postData.append('value', textVal);

        fetch('<?= admin_url("ajax/live-edit.php") ?>', {
          method: 'POST',
          body: postData
        })
        .then(res => res.json())
        .then(res => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu Pill Banner';
          if (res.success) {
            const pillP = document.querySelector('.pill-banner p');
            if (pillP) {
              pillP.innerText = textVal;
            }
            closeLiveModal('modalLiveEditPill');
            showToast('✓ Đã cập nhật Pill Banner thành công!');
          } else {
            alert(res.error || 'Lỗi khi lưu Pill Banner.');
          }
        })
        .catch(err => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu Pill Banner';
          alert('Lỗi kết nối máy chủ: ' + err);
        });
      });
    }

    // 3. Save Call to Action (CTA Banner)
    const btnSaveCTA = document.getElementById('btnSaveLiveCTA');
    if (btnSaveCTA && !btnSaveCTA._hasBoundEvents) {
      btnSaveCTA._hasBoundEvents = true;
      btnSaveCTA.addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';

        const form = document.getElementById('formLiveEditCTA');
        if (!form) return;
        const formData = new FormData(form);
        const dataObj = {};
        formData.forEach((val, key) => { dataObj[key] = val; });

        const postData = new FormData();
        postData.append('action', 'save_batch');
        postData.append('data', JSON.stringify(dataObj));

        fetch('<?= admin_url("ajax/live-edit.php") ?>', {
          method: 'POST',
          body: postData
        })
        .then(res => res.json())
        .then(res => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu Call to Action';
          if (res.success) {
            const ctaTitleEl = document.getElementById('live-cta-title');
            if (ctaTitleEl) ctaTitleEl.innerText = dataObj.cta_title;

            const ctaBtnTextEl = document.getElementById('live-cta-btn-text');
            if (ctaBtnTextEl) ctaBtnTextEl.innerText = dataObj.cta_button_text;

            const ctaBtnEl = document.getElementById('live-cta-btn');
            if (ctaBtnEl) {
              ctaBtnEl.href = dataObj.cta_button_url;
            }

            if (dataObj.cta_bg_image) {
              const ctaBoxEl = document.getElementById('live-cta-box');
              if (ctaBoxEl) {
                ctaBoxEl.style.background = "linear-gradient(90deg, rgba(6, 42, 77, 0.9) 0%, rgba(10, 25, 47, 0.8) 100%), url('" + dataObj.cta_bg_image + "') center/cover no-repeat";
              }
            }

            closeLiveModal('modalLiveEditCTA');
            showToast('✓ Đã cập nhật Call to Action thành công!');
          } else {
            alert(res.error || 'Lỗi khi lưu Call to Action.');
          }
        })
        .catch(err => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu Call to Action';
          alert('Lỗi kết nối máy chủ: ' + err);
        });
      });
    }

    // 4. Reset Footer Nav to Default Markup
    const btnResetFooterNav = document.getElementById('btnResetFooterNav');
    if (btnResetFooterNav && !btnResetFooterNav._hasBoundEvents) {
      btnResetFooterNav._hasBoundEvents = true;
      btnResetFooterNav.addEventListener('click', function() {
        const textarea = document.getElementById('inputLiveFooterNav');
        if (textarea) {
          const defaultNav = <?= json_encode($defaultFooterNavMarkup) ?>;
          textarea.value = defaultNav;
          if (window._liveAceEditors && window._liveAceEditors['inputLiveFooterNav']) {
            window._liveAceEditors['inputLiveFooterNav'].setValue(defaultNav, -1);
          }
          showToast('Đã nạp lại mã mẫu 3 cột! Hãy bấm "Lưu Tất cả Thay đổi" để áp dụng.');
        }
      });
    }

    // 5. Save Full Footer (All Components)
    const btnSaveFooter = document.getElementById('btnSaveLiveFooter');
    if (btnSaveFooter && !btnSaveFooter._hasBoundEvents) {
      btnSaveFooter._hasBoundEvents = true;
      btnSaveFooter.addEventListener('click', function() {
        flushLiveAceEditors();
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';

        const form = document.getElementById('formLiveEditFooter');
        if (!form) return;
        const formData = new FormData(form);
        const dataObj = {};
        formData.forEach((val, key) => { dataObj[key] = val; });

        const postData = new FormData();
        postData.append('action', 'save_batch');
        postData.append('data', JSON.stringify(dataObj));

        fetch('<?= admin_url("ajax/live-edit.php") ?>', {
          method: 'POST',
          body: postData
        })
        .then(res => res.json())
        .then(res => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu Tất cả Thay đổi Footer';
          if (res.success) {
            // 1. Update 3 navigation columns
            const navGridEl = document.getElementById('live-footer-nav-grid');
            if (navGridEl && dataObj.section_footer_nav) {
              navGridEl.innerHTML = dataObj.section_footer_nav;
            }

            // 2. Update address, email, phone
            const addrEl = document.getElementById('live-footer-address');
            if (addrEl && dataObj.site_address_vn !== undefined) {
              addrEl.innerHTML = escapeHtml(dataObj.site_address_vn).replace(/\n/g, '<br>');
            }

            const emailEl = document.getElementById('live-footer-email');
            if (emailEl && dataObj.site_email !== undefined) {
              emailEl.innerText = dataObj.site_email;
              emailEl.href = 'mailto:' + dataObj.site_email;
            }

            const phoneEl = document.getElementById('live-footer-phone');
            if (phoneEl && dataObj.site_phone !== undefined) {
              phoneEl.innerText = dataObj.site_phone;
              phoneEl.href = 'tel:' + dataObj.site_phone.replace(/[\s\-\.]/g, '');
            }

            // 3. Update copyright
            const copyEl = document.getElementById('live-footer-copyright');
            if (copyEl && dataObj.footer_copyright !== undefined) {
              copyEl.innerHTML = dataObj.footer_copyright;
            }

            // 4. Update Partner 1 Logo & Link
            const partLink1 = document.getElementById('live-footer-partner-link-1');
            if (partLink1 && dataObj.footer_partner_link_1 !== undefined) {
              partLink1.href = dataObj.footer_partner_link_1;
            }
            const partImg1 = document.getElementById('live-footer-partner-img-1');
            if (partImg1 && dataObj.footer_partner_logo_1 !== undefined) {
              partImg1.src = dataObj.footer_partner_logo_1.startsWith('http') || dataObj.footer_partner_logo_1.startsWith('/') ? dataObj.footer_partner_logo_1 : '<?= asset('') ?>' + dataObj.footer_partner_logo_1;
            }

            // 5. Update Partner 2 Logo & Link
            const partLink2 = document.getElementById('live-footer-partner-link-2');
            if (partLink2 && dataObj.footer_partner_link_2 !== undefined) {
              partLink2.href = dataObj.footer_partner_link_2;
            }
            const partImg2 = document.getElementById('live-footer-partner-img-2');
            if (partImg2 && dataObj.footer_partner_logo_2 !== undefined) {
              partImg2.src = dataObj.footer_partner_logo_2.startsWith('http') || dataObj.footer_partner_logo_2.startsWith('/') ? dataObj.footer_partner_logo_2 : '<?= asset('') ?>' + dataObj.footer_partner_logo_2;
            }

            // 6. Update Main Footer Logo
            const mainLogoEl = document.getElementById('live-footer-logo');
            if (mainLogoEl && dataObj.logo_footer !== undefined) {
              mainLogoEl.src = dataObj.logo_footer.startsWith('http') || dataObj.logo_footer.startsWith('/') ? dataObj.logo_footer : '<?= asset('') ?>' + dataObj.logo_footer;
            }

            // 7. Update Social Media Links
            const fbEl = document.getElementById('live-footer-fb');
            if (fbEl && dataObj.social_facebook !== undefined) fbEl.href = dataObj.social_facebook;

            const liEl = document.getElementById('live-footer-li');
            if (liEl && dataObj.social_linkedin !== undefined) liEl.href = dataObj.social_linkedin;

            const ytEl = document.getElementById('live-footer-yt');
            if (ytEl && dataObj.social_youtube !== undefined) ytEl.href = dataObj.social_youtube;

            closeLiveModal('modalLiveEditFooter');
            showToast('✓ Đã cập nhật tất cả thành phần Footer thành công!');
          } else {
            alert(res.error || 'Lỗi khi lưu Footer.');
          }
        })
        .catch(err => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu Tất cả Thay đổi Footer';
          alert('Lỗi kết nối máy chủ: ' + err);
        });
      });
    }

    // 5. Save Leadership & Message from Founder
    const btnSaveLeadership = document.getElementById('btnSaveLiveLeadership');
    if (btnSaveLeadership && !btnSaveLeadership._hasBoundEvents) {
      btnSaveLeadership._hasBoundEvents = true;
      btnSaveLeadership.addEventListener('click', function() {
        flushLiveAceEditors();
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';

        const form = document.getElementById('formLiveEditLeadership');
        if (!form) return;
        const formData = new FormData(form);
        const dataObj = {};
        formData.forEach((val, key) => { dataObj[key] = val; });

        // Include Key Team visibility checkbox
        const showKtCb = document.getElementById('inputLiveShowKeyTeam');
        if (showKtCb) {
          dataObj.about_show_key_team = showKtCb.checked ? '1' : '0';
        }

        // If saving from structured Tab 1/2 without modifying Tab 3 Custom HTML, clear custom HTML override so visual edits apply immediately
        const activeTab = document.querySelector('#modalLiveEditLeadership .vnchip-tab-link.active')?.getAttribute('data-tab');
        if ((activeTab === 'lead-tab-founder' || activeTab === 'lead-tab-titles') && !window._leadHtmlDirty) {
          dataObj.section_about_leadership = '';
          clearLiveAceEditor('inputLiveLeadCustomHtml');
        }

        const postData = new FormData();
        postData.append('action', 'save_batch');
        postData.append('data', JSON.stringify(dataObj));

        fetch('<?= admin_url("ajax/live-edit.php") ?>', {
          method: 'POST',
          body: postData
        })
        .then(res => res.json())
        .then(res => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu Thay Đổi Leadership';
          if (res.success) {
            window._leadHtmlDirty = false;
            const leadTitle = document.getElementById('live-leadership-title');
            if (leadTitle && dataObj.about_leadership_title) leadTitle.innerText = dataObj.about_leadership_title;

            const founderTitle = document.getElementById('live-founder-title');
            if (founderTitle && dataObj.about_founder_title) founderTitle.innerHTML = dataObj.about_founder_title;

            const founderQuote = document.getElementById('live-founder-quote');
            if (founderQuote && dataObj.about_founder_quote) {
              founderQuote.innerHTML = escapeHtml(dataObj.about_founder_quote).replace(/\n/g, '<br>');
            }

            const leadSubtext = document.getElementById('live-leadership-subtext');
            if (leadSubtext && dataObj.about_leadership_subtext) leadSubtext.innerText = dataObj.about_leadership_subtext;

            const keyTeamTitle = document.getElementById('live-key-team-title');
            if (keyTeamTitle && dataObj.about_key_team_title) keyTeamTitle.innerText = dataObj.about_key_team_title;

            const keyTeamSub = document.getElementById('live-key-team-sub');
            if (keyTeamSub && dataObj.about_key_team_subtext) keyTeamSub.innerText = dataObj.about_key_team_subtext;

            // Reflect the Key Team on/off switch without a page reload
            const keyTeamWrapEl = document.getElementById('live-key-team-wrap');
            if (keyTeamWrapEl && typeof dataObj.about_show_key_team !== 'undefined') {
              const keyTeamOn = dataObj.about_show_key_team === '1';
              keyTeamWrapEl.classList.toggle('live-section-off', !keyTeamOn);
              keyTeamWrapEl.setAttribute('data-section-hidden', keyTeamOn ? '0' : '1');

              let offFlag = document.getElementById('live-key-team-off-flag');
              if (!keyTeamOn && !offFlag) {
                offFlag = document.createElement('div');
                offFlag.id = 'live-key-team-off-flag';
                offFlag.className = 'live-section-off-flag';
                offFlag.innerHTML = '<i class="fa-solid fa-eye-slash"></i> Khối Key Team đang <strong>TẮT</strong> &mdash; khách truy cập không nhìn thấy. Bạn thấy được vì đang đăng nhập quản trị.';
                keyTeamWrapEl.insertBefore(offFlag, keyTeamWrapEl.firstChild);
              } else if (keyTeamOn && offFlag) {
                offFlag.remove();
              }
            }

            const founderImg = document.getElementById('live-founder-img');
            if (founderImg && dataObj.about_founder_photo) {
              founderImg.src = resolveLiveAssetUrl(dataObj.about_founder_photo);
            }

            closeLiveModal('modalLiveEditLeadership');
            const needsReload = !!document.getElementById('live-custom-leadership')
              || (dataObj.section_about_leadership && dataObj.section_about_leadership.trim() !== '')
              || (showKtCb && ((showKtCb.checked && !document.getElementById('live-key-team-wrap')) || (!showKtCb.checked && !!document.getElementById('live-key-team-wrap'))));
            if (needsReload) {
              showToast('✓ Đã cập nhật mục Leadership & Key Team thành công! Đang tải lại...');
              setTimeout(() => { window.location.reload(); }, 800);
            } else {
              showToast('✓ Đã cập nhật mục Leadership & Thông điệp Sáng lập thành công!');
            }
          } else {
            alert(res.error || 'Lỗi khi lưu mục Leadership.');
          }
        })
        .catch(err => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu Thay Đổi Leadership';
          alert('Lỗi kết nối máy chủ: ' + err);
        });
      });
    }

    // 6. Save Sustainable
    const btnSaveSus = document.getElementById('btnSaveLiveSustainable');
    if (btnSaveSus && !btnSaveSus._hasBoundEvents) {
      btnSaveSus._hasBoundEvents = true;
      btnSaveSus.addEventListener('click', function() {
        flushLiveAceEditors();
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';

        const form = document.getElementById('formLiveEditSustainable');
        if (!form) return;
        const formData = new FormData(form);
        const dataObj = {};
        formData.forEach((val, key) => { dataObj[key] = val; });

        const postData = new FormData();
        postData.append('action', 'save_batch');
        postData.append('data', JSON.stringify(dataObj));

        fetch('<?= admin_url("ajax/live-edit.php") ?>', {
          method: 'POST',
          body: postData
        })
        .then(res => res.json())
        .then(res => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu Thay Đổi Sustainable';
          if (res.success) {
            const esgEl = document.getElementById('live-sus-social-text');
            if (esgEl && dataObj.section_sustainable_esg) esgEl.innerHTML = dataObj.section_sustainable_esg;

            const cleanHeading = document.getElementById('live-sus-cleanroom-heading');
            if (cleanHeading && dataObj.sus_cleanroom_heading) cleanHeading.innerText = dataObj.sus_cleanroom_heading;

            const cleanSub = document.getElementById('live-sus-cleanroom-subheading');
            if (cleanSub && dataObj.sus_cleanroom_subheading) cleanSub.innerText = dataObj.sus_cleanroom_subheading;

            const talentsEl = document.getElementById('live-sus-talents-intro');
            if (talentsEl && dataObj.sus_talents_intro) talentsEl.innerHTML = dataObj.sus_talents_intro;

            const secEl = document.getElementById('live-sus-confidentiality-text');
            if (secEl && dataObj.sus_confidentiality_text) secEl.innerHTML = dataObj.sus_confidentiality_text;

            const quoteEl = document.getElementById('live-sus-partner-quote');
            if (quoteEl && dataObj.sus_partner_quote) quoteEl.innerText = dataObj.sus_partner_quote;

            closeLiveModal('modalLiveEditSustainable');
            showToast('✓ Đã cập nhật trang Sustainable thành công!');
          } else {
            alert(res.error || 'Lỗi khi lưu Sustainable.');
          }
        })
        .catch(err => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu Thay Đổi Sustainable';
          alert('Lỗi kết nối máy chủ: ' + err);
        });
      });
    }

    // 7. Save Business
    const btnSaveBiz = document.getElementById('btnSaveLiveBusiness');
    if (btnSaveBiz && !btnSaveBiz._hasBoundEvents) {
      btnSaveBiz._hasBoundEvents = true;
      btnSaveBiz.addEventListener('click', function() {
        flushLiveAceEditors();
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';

        const form = document.getElementById('formLiveEditBusiness');
        if (!form) return;
        const formData = new FormData(form);
        const dataObj = {};
        formData.forEach((val, key) => { dataObj[key] = val; });

        const postData = new FormData();
        postData.append('action', 'save_batch');
        postData.append('data', JSON.stringify(dataObj));

        fetch('<?= admin_url("ajax/live-edit.php") ?>', {
          method: 'POST',
          body: postData
        })
        .then(res => res.json())
        .then(res => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu Thay Đổi Business';
          if (res.success) {
            const techTitleEl = document.getElementById('live-biz-tech-title');
            if (techTitleEl && dataObj.business_tech_title) techTitleEl.innerText = dataObj.business_tech_title;

            const techBoxEl = document.getElementById('live-biz-tech-box');
            if (techBoxEl && dataObj.section_business_services && dataObj.section_business_services.trim() !== '') {
              techBoxEl.innerHTML = dataObj.section_business_services;
            }

            closeLiveModal('modalLiveEditBusiness');
            showToast('✓ Đã cập nhật trang Business thành công!');
          } else {
            alert(res.error || 'Lỗi khi lưu Business.');
          }
        })
        .catch(err => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu Thay Đổi Business';
          alert('Lỗi kết nối máy chủ: ' + err);
        });
      });
    }

    // 7.1 Save Design Service & IP Service block (headings + on/off switch)
    const btnSaveServices = document.getElementById('btnSaveLiveServices');
    if (btnSaveServices && !btnSaveServices._hasBoundEvents) {
      btnSaveServices._hasBoundEvents = true;
      btnSaveServices.addEventListener('click', function() {
        const btn = this;
        const label = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu Thay Đổi';
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';

        const form = document.getElementById('formLiveEditServices');
        if (!form) return;
        const formData = new FormData(form);
        const dataObj = {};
        formData.forEach((val, key) => { dataObj[key] = val; });

        const showCb = document.getElementById('inputLiveShowServices');
        if (showCb) {
          dataObj.show_design_ip_services = showCb.checked ? '1' : '0';
        }

        const postData = new FormData();
        postData.append('action', 'save_batch');
        postData.append('data', JSON.stringify(dataObj));

        fetch('<?= admin_url("ajax/live-edit.php") ?>', { method: 'POST', body: postData })
        .then(res => res.json())
        .then(res => {
          btn.disabled = false;
          btn.innerHTML = label;
          if (!res.success) {
            alert(res.error || 'Lỗi khi lưu khối Design & IP Services.');
            return;
          }

          const colTitles = document.querySelectorAll('.services-grid .service-col-title');
          if (colTitles[0] && dataObj.services_design_title) colTitles[0].innerText = dataObj.services_design_title;
          if (colTitles[1] && dataObj.services_ip_title) colTitles[1].innerText = dataObj.services_ip_title;

          // Reflect the on/off switch without a page reload
          const wrap = document.getElementById('live-services-wrap');
          if (wrap && typeof dataObj.show_design_ip_services !== 'undefined') {
            const on = dataObj.show_design_ip_services === '1';
            wrap.classList.toggle('live-section-off', !on);
            wrap.setAttribute('data-section-hidden', on ? '0' : '1');

            let offFlag = document.getElementById('live-services-off-flag');
            if (!on && !offFlag) {
              offFlag = document.createElement('div');
              offFlag.id = 'live-services-off-flag';
              offFlag.className = 'live-section-off-flag';
              offFlag.innerHTML = '<i class="fa-solid fa-eye-slash"></i> Khối Design Service &amp; IP Service đang <strong>TẮT</strong> &mdash; khách truy cập không nhìn thấy. Bạn thấy được vì đang đăng nhập quản trị.';
              wrap.insertBefore(offFlag, wrap.firstChild);
            } else if (on && offFlag) {
              offFlag.remove();
            }
          }

          closeLiveModal('modalLiveEditServices');
          showToast('✓ Đã cập nhật khối Design & IP Services!');
        })
        .catch(err => {
          btn.disabled = false;
          btn.innerHTML = label;
          alert('Lỗi kết nối máy chủ: ' + err);
        });
      });
    }

    // 8. Save Contact
    const btnSaveContact = document.getElementById('btnSaveLiveContact');
    if (btnSaveContact && !btnSaveContact._hasBoundEvents) {
      btnSaveContact._hasBoundEvents = true;
      btnSaveContact.addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';

        const form = document.getElementById('formLiveEditContact');
        if (!form) return;
        const formData = new FormData(form);
        const dataObj = {};
        formData.forEach((val, key) => { dataObj[key] = val; });

        const postData = new FormData();
        postData.append('action', 'save_batch');
        postData.append('data', JSON.stringify(dataObj));

        fetch('<?= admin_url("ajax/live-edit.php") ?>', {
          method: 'POST',
          body: postData
        })
        .then(res => res.json())
        .then(res => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu Thay Đổi Liên Hệ';
          if (res.success) {
            const pageTitle = document.getElementById('live-contact-title');
            if (pageTitle && dataObj.contact_page_title) pageTitle.innerText = dataObj.contact_page_title;

            const offTitle = document.getElementById('live-contact-offices-title');
            if (offTitle && dataObj.contact_offices_title) offTitle.innerText = dataObj.contact_offices_title;

            const offDesc = document.getElementById('live-contact-offices-desc');
            if (offDesc && dataObj.contact_offices_desc) offDesc.innerText = dataObj.contact_offices_desc;

            const addrVn = document.getElementById('live-contact-address-vn');
            if (addrVn && dataObj.site_address_vn) addrVn.innerHTML = escapeHtml(dataObj.site_address_vn).replace(/\n/g, '<br>');

            const addrUs = document.getElementById('live-contact-address-us');
            if (addrUs && dataObj.site_address_us) addrUs.innerHTML = escapeHtml(dataObj.site_address_us).replace(/\n/g, '<br>');

            const phoneEl = document.getElementById('live-contact-phone');
            if (phoneEl && dataObj.site_phone) {
              phoneEl.innerHTML = '<a href="tel:' + dataObj.site_phone.replace(/[\s\-\.]/g, '') + '">' + escapeHtml(dataObj.site_phone) + '</a>';
            }

            const emailEl = document.getElementById('live-contact-email');
            if (emailEl && dataObj.site_email) {
              emailEl.innerHTML = '<a href="mailto:' + escapeHtml(dataObj.site_email) + '">' + escapeHtml(dataObj.site_email) + '</a>';
            }

            closeLiveModal('modalLiveEditContact');
            showToast('✓ Đã cập nhật thông tin Liên hệ & Trụ sở thành công!');
          } else {
            alert(res.error || 'Lỗi khi lưu thông tin Liên hệ.');
          }
        })
        .catch(err => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu Thay Đổi Liên Hệ';
          alert('Lỗi kết nối máy chủ: ' + err);
        });
      });
    }

    // 9. Save Technology Modal
    const btnSaveTech = document.getElementById('btnSaveLiveTechnology');
    if (btnSaveTech && !btnSaveTech._hasBoundEvents) {
      btnSaveTech._hasBoundEvents = true;
      btnSaveTech.addEventListener('click', function() {
        flushLiveAceEditors();
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';

        const form = document.getElementById('formLiveEditTechnology');
        if (!form) return;
        const formData = new FormData(form);
        const dataObj = {};
        formData.forEach((val, key) => { dataObj[key] = val; });

        const postData = new FormData();
        postData.append('action', 'save_batch');
        postData.append('data', JSON.stringify(dataObj));

        fetch('<?= admin_url("ajax/live-edit.php") ?>', {
          method: 'POST',
          body: postData
        })
        .then(res => res.json())
        .then(res => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu Thay Đổi Technology';
          if (res.success) {
            closeLiveModal('modalLiveEditTechnology');
            showToast('✓ Đã cập nhật nội dung công nghệ thành công! Đang tải lại...');
            setTimeout(() => { window.location.reload(); }, 900);
          } else {
            alert(res.error || 'Lỗi khi lưu Technology.');
          }
        })
        .catch(err => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu Thay Đổi Technology';
          alert('Lỗi kết nối máy chủ: ' + err);
        });
      });
    }

    // 10. Save About Overview Modal
    const btnSaveAboutOverview = document.getElementById('btnSaveLiveAboutOverview');
    if (btnSaveAboutOverview && !btnSaveAboutOverview._hasBoundEvents) {
      btnSaveAboutOverview._hasBoundEvents = true;
      btnSaveAboutOverview.addEventListener('click', function() {
        flushLiveAceEditors();
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';

        const form = document.getElementById('formLiveEditAboutOverview');
        if (!form) return;
        const formData = new FormData(form);
        const dataObj = {};
        formData.forEach((val, key) => { dataObj[key] = val; });

        const postData = new FormData();
        postData.append('action', 'save_batch');
        postData.append('data', JSON.stringify(dataObj));

        fetch('<?= admin_url("ajax/live-edit.php") ?>', {
          method: 'POST',
          body: postData
        })
        .then(res => res.json())
        .then(res => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu Thay Đổi About Overview';
          if (res.success) {
            const ovTitle = document.getElementById('live-about-overview-title');
            if (ovTitle && dataObj.about_overview_title) ovTitle.innerText = dataObj.about_overview_title;
            closeLiveModal('modalLiveEditAboutOverview');
            showToast('✓ Đã cập nhật Giới thiệu & Ảnh Trụ sở thành công! Đang tải lại...');
            setTimeout(() => { window.location.reload(); }, 800);
          } else {
            alert(res.error || 'Lỗi khi lưu About Overview.');
          }
        })
        .catch(err => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Lưu Thay Đổi About Overview';
          alert('Lỗi kết nối máy chủ: ' + err);
        });
      });
    }

    // 11. Save About Hero Modal
    const btnSaveAboutHero = document.getElementById('btnSaveLiveAboutHero');
    if (btnSaveAboutHero && !btnSaveAboutHero._hasBoundEvents) {
      btnSaveAboutHero._hasBoundEvents = true;
      btnSaveAboutHero.addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';

        const form = document.getElementById('formLiveEditAboutHero');
        if (!form) return;
        const formData = new FormData(form);
        const dataObj = {};
        formData.forEach((val, key) => { dataObj[key] = val; });

        const postData = new FormData();
        postData.append('action', 'save_batch');
        postData.append('group', 'banners');
        postData.append('data', JSON.stringify(dataObj));

        fetch('<?= admin_url("ajax/live-edit.php") ?>', {
          method: 'POST',
          body: postData
        })
        .then(res => res.json())
        .then(res => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Lưu Hero Banner';
          if (res.success) {
            const titleEl = document.getElementById('live-about-hero-title');
            if (titleEl && dataObj.about_hero_title) titleEl.innerText = dataObj.about_hero_title;
            const heroSec = document.getElementById('live-about-hero');
            if (heroSec && dataObj.hero_about) {
              heroSec.style.backgroundImage = "url('" + resolveLiveAssetUrl(dataObj.hero_about) + "')";
            }
            closeLiveModal('modalLiveEditAboutHero');
            showToast('✓ Đã cập nhật Hero Banner (About Us) thành công!');
          } else {
            alert(res.error || 'Lỗi khi lưu Hero Banner.');
          }
        })
        .catch(err => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Lưu Hero Banner';
          alert('Lỗi kết nối máy chủ: ' + err);
        });
      });
    }

    // 12. Save About Journey Timeline Modal
    const btnSaveAboutTimeline = document.getElementById('btnSaveLiveAboutTimeline');
    if (btnSaveAboutTimeline && !btnSaveAboutTimeline._hasBoundEvents) {
      btnSaveAboutTimeline._hasBoundEvents = true;
      btnSaveAboutTimeline.addEventListener('click', function() {
        flushLiveAceEditors();
        const items = collectLiveTimelineItems();
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';

        const form = document.getElementById('formLiveEditAboutTimeline');
        if (!form) return;
        const formData = new FormData(form);
        const dataObj = {};
        formData.forEach((val, key) => { dataObj[key] = val; });

        const postData = new FormData();
        postData.append('action', 'save_batch');
        postData.append('group', 'content_sections');
        postData.append('data', JSON.stringify(dataObj));

        fetch('<?= admin_url("ajax/live-edit.php") ?>', {
          method: 'POST',
          body: postData
        })
        .then(res => res.json())
        .then(res => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi Timeline';
          if (res.success) {
            const customHtml = (dataObj.section_about_timeline || '').trim();
            const hadCustom = !!document.getElementById('live-custom-timeline');
            if (customHtml !== '' || hadCustom) {
              closeLiveModal('modalLiveEditAboutTimeline');
              showToast('✓ Đã cập nhật Timeline thành công! Đang tải lại...');
              setTimeout(() => { window.location.reload(); }, 800);
            } else {
              const subEl = document.getElementById('live-journey-subtitle');
              if (subEl && dataObj.about_journey_subtitle !== undefined) subEl.innerText = dataObj.about_journey_subtitle;
              const trackEl = document.getElementById('live-timeline-track');
              if (trackEl) {
                trackEl.innerHTML = items.map(it => `
                  <li class="timeline-item">
                    <div class="timeline-node"></div>
                    <div class="timeline-year">${escapeHtml(it.year || '')}</div>
                    <p class="timeline-desc">${escapeHtml(it.desc || '')}</p>
                  </li>
                `).join('');
              }
              closeLiveModal('modalLiveEditAboutTimeline');
              showToast('✓ Đã cập nhật Lịch sử & Timeline thành công!');
            }
          } else {
            alert(res.error || 'Lỗi khi lưu Timeline.');
          }
        })
        .catch(err => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi Timeline';
          alert('Lỗi kết nối máy chủ: ' + err);
        });
      });
    }

    // 13. Save About Vision / Mission / Core Values Modal
    const btnSaveAboutVision = document.getElementById('btnSaveLiveAboutVision');
    if (btnSaveAboutVision && !btnSaveAboutVision._hasBoundEvents) {
      btnSaveAboutVision._hasBoundEvents = true;
      btnSaveAboutVision.addEventListener('click', function() {
        flushLiveAceEditors();
        const cards = collectLiveVisionCards();
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';

        const form = document.getElementById('formLiveEditAboutVision');
        if (!form) return;
        const formData = new FormData(form);
        const dataObj = {};
        formData.forEach((val, key) => { dataObj[key] = val; });

        const postData = new FormData();
        postData.append('action', 'save_batch');
        postData.append('group', 'content_sections');
        postData.append('data', JSON.stringify(dataObj));

        fetch('<?= admin_url("ajax/live-edit.php") ?>', {
          method: 'POST',
          body: postData
        })
        .then(res => res.json())
        .then(res => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Lưu Vision & Mission';
          if (res.success) {
            const customHtml = (dataObj.section_about_vision_cards || '').trim();
            const hadCustom = !!document.getElementById('live-custom-vision-cards');
            if (customHtml !== '' || hadCustom) {
              closeLiveModal('modalLiveEditAboutVision');
              showToast('✓ Đã cập nhật Vision & Mission thành công! Đang tải lại...');
              setTimeout(() => { window.location.reload(); }, 800);
            } else {
              const tEl = document.getElementById('live-vision-main-title');
              if (tEl && dataObj.about_vision_main_title !== undefined) tEl.innerText = dataObj.about_vision_main_title;
              const sEl = document.getElementById('live-vision-main-subtitle');
              if (sEl && dataObj.about_vision_main_subtitle !== undefined) sEl.innerText = dataObj.about_vision_main_subtitle;
              const gEl = document.getElementById('live-vision-cards-grid');
              if (gEl) {
                gEl.innerHTML = cards.map(c => {
                  const svg = LIVE_VISION_SVG_PRESETS[c.icon] || LIVE_VISION_SVG_PRESETS.eye;
                  const titleHtml = escapeHtml((c.title || '').replace(/<br\s*\/?>/gi, '\n')).replace(/\n/g, '<br>');
                  const textHtml = escapeHtml(c.text || '').replace(/\n/g, '<br>');
                  return `
                    <div class="vision-card">
                      <div class="vision-icon-box">${svg}</div>
                      <h3 class="vision-card-title">${titleHtml}</h3>
                      <p class="vision-card-text">${textHtml}</p>
                    </div>
                  `;
                }).join('');
              }
              closeLiveModal('modalLiveEditAboutVision');
              showToast('✓ Đã cập nhật Vision, Mission & Core Values thành công!');
            }
          } else {
            alert(res.error || 'Lỗi khi lưu Vision & Mission.');
          }
        })
        .catch(err => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Lưu Vision & Mission';
          alert('Lỗi kết nối máy chủ: ' + err);
        });
      });
    }

    // 14. Save Code of Conduct Modal (about-us.php#conduct)
    const btnSaveConduct = document.getElementById('btnSaveLiveConduct');
    if (btnSaveConduct && !btnSaveConduct._hasBoundEvents) {
      btnSaveConduct._hasBoundEvents = true;
      btnSaveConduct.addEventListener('click', function() {
        flushLiveAceEditors();
        const cards = collectLiveConductCards();
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang lưu...';

        const form = document.getElementById('formLiveEditConduct');
        if (!form) return;
        const formData = new FormData(form);
        const dataObj = {};
        formData.forEach((val, key) => { dataObj[key] = val; });

        const postData = new FormData();
        postData.append('action', 'save_batch');
        postData.append('group', 'content_sections');
        postData.append('data', JSON.stringify(dataObj));

        fetch('<?= admin_url("ajax/live-edit.php") ?>', {
          method: 'POST',
          body: postData
        })
        .then(res => res.json())
        .then(res => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Lưu Code of Conduct';
          if (res.success) {
            const customHtml = (dataObj.section_about_conduct || '').trim();
            const hadCustom = !!document.getElementById('live-custom-conduct');
            if (customHtml !== '' || hadCustom) {
              closeLiveModal('modalLiveEditConduct');
              showToast('✓ Đã cập nhật Code of Conduct thành công! Đang tải lại...');
              setTimeout(() => { window.location.reload(); }, 800);
            } else {
              const tEl = document.getElementById('live-conduct-title');
              if (tEl && dataObj.about_conduct_title !== undefined) tEl.innerText = dataObj.about_conduct_title;
              const sEl = document.getElementById('live-conduct-subtitle');
              if (sEl && dataObj.about_conduct_subtitle !== undefined) sEl.innerText = dataObj.about_conduct_subtitle;
              const gEl = document.getElementById('live-conduct-cards-grid');
              if (gEl) {
                gEl.innerHTML = cards.map(c => {
                  const titleHtml = escapeHtml((c.title || '').replace(/<br\s*\/?>/gi, '\n')).replace(/\n/g, '<br>');
                  const textHtml = escapeHtml(c.text || '').replace(/\n/g, '<br>');
                  return `
                    <div class="conduct-card">
                      <h3 class="conduct-card-title">${titleHtml}</h3>
                      <p class="conduct-card-text">${textHtml}</p>
                    </div>
                  `;
                }).join('');
              }
              closeLiveModal('modalLiveEditConduct');
              showToast('✓ Đã cập nhật khối Code of Conduct thành công!');
            }
          } else {
            alert(res.error || 'Lỗi khi lưu Code of Conduct.');
          }
        })
        .catch(err => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Lưu Code of Conduct';
          alert('Lỗi kết nối máy chủ: ' + err);
        });
      });
    }
  }

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
  }

  // Lifecycle Initialization
  function init() {
    bindSwitchControls();
    bindAjaxForms();
    syncLiveEditState();
    initLiveAceEditorsAll();
  }

  // 1. DOM Ready check
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // 2. Window Load check (all assets, stylesheets ready)
  window.addEventListener('load', function() {
    syncLiveEditState();
  });

  // 3. pageshow event: handles Chrome / Safari Back-Forward Cache (bfcache)
  window.addEventListener('pageshow', function(event) {
    syncLiveEditState();
  });

  // 4. focus event: handles user returning to this tab after editing in another tab
  window.addEventListener('focus', function() {
    syncLiveEditState();
  });

  // 5. MutationObserver: ensures badges remain active even if parts of DOM re-render
  if (window.MutationObserver) {
    const observer = new MutationObserver(function() {
      if (isLiveEditActive()) {
        injectEditBadges();
      }
    });
    // Start observing after DOM ready
    setTimeout(() => {
      if (document.body) {
        observer.observe(document.body, { childList: true, subtree: true });
      }
    }, 100);
  }

})();
</script>

<script src="<?= asset('js/service-editor.js') ?>?v=<?= @filemtime(__DIR__ . '/../assets/js/service-editor.js') ?>"></script>
<script>
/**
 * Live Edit for Design/IP Service items and their keyword tooltips.
 * Everything talks to chip-admin/ajax/live-edit.php (get/save_service_item, get/save_tooltip).
 */
(function () {
  const AJAX = '<?= admin_url("ajax/live-edit.php") ?>';
  const ADMIN_CSS = '<?= absolute_url(ADMIN_DIR . '/assets/css/editor-content.css') ?>?v=<?= @filemtime(__DIR__ . '/../' . ADMIN_DIR . '/assets/css/editor-content.css') ?>';
  const BASE = '<?= absolute_url() ?>';
  const SE = window.VNServiceEditor;

  let bodyCtl = null;      // item body editor
  let tipCtl = null;       // tooltip content editor
  let dictMap = {};        // normalised keyword -> {id, keyword, title}
  let currentItem = null;
  let tipReturnToItem = false;

  function toast(msg, ok = true) {
    const t = document.getElementById('live-edit-toast');
    const tx = document.getElementById('live-edit-toast-text');
    if (!t || !tx) return;
    tx.innerText = msg;
    t.style.borderLeftColor = ok ? '#10B981' : '#F59E0B';
    t.style.display = 'inline-flex';
    clearTimeout(t._timer);
    t._timer = setTimeout(() => { t.style.display = 'none'; }, 3500);
  }

  function post(data) {
    const fd = new FormData();
    Object.keys(data).forEach(k => fd.append(k, data[k]));
    return fetch(AJAX, { method: 'POST', body: fd }).then(r => r.json());
  }

  function busy(btn, on, label) {
    btn.disabled = on;
    btn.innerHTML = on ? '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...' : label;
  }

  // ------------------------------------------------------------------
  // Badges on the page
  // ------------------------------------------------------------------
  function inject() {
    document.querySelectorAll('.service-item[data-service-id]').forEach(item => {
      const header = item.querySelector('.service-header');
      if (!header || header.querySelector('.live-svc-pen')) return;
      const pen = document.createElement('button');
      pen.type = 'button';
      pen.className = 'live-svc-pen';
      pen.innerHTML = '<i class="fa-solid fa-pen"></i> Sửa mục';
      pen.title = 'Sửa tên, ảnh và nội dung của mục này';
      pen.addEventListener('click', e => {
        e.preventDefault();
        e.stopPropagation();
        openItem(parseInt(item.getAttribute('data-service-id'), 10));
      });
      const icon = header.querySelector('.service-icon');
      header.insertBefore(pen, icon || null);
    });

    document.querySelectorAll('.service-body .tuong-tac').forEach(kw => {
      if (kw.querySelector('.live-tip-pen')) return;
      const pen = document.createElement('span');
      pen.className = 'live-tip-pen';
      pen.innerHTML = '<i class="fa-solid fa-pen"></i>';
      pen.title = 'Sửa phần giải thích của từ khoá này';
      pen.addEventListener('click', e => {
        e.preventDefault();
        e.stopPropagation();
        tipReturnToItem = false;
        openTip({ id: parseInt(kw.getAttribute('data-tip-id') || '0', 10), keyword: kw.getAttribute('data-tip-key') || kw.firstChild.textContent });
      });
      const note = kw.querySelector('.chu-thich');
      kw.insertBefore(pen, note || null);
    });
  }

  // ------------------------------------------------------------------
  // Item editor
  // ------------------------------------------------------------------
  function ensureBodyEditor(cb) {
    if (bodyCtl) { bodyCtl.ready(cb); return; }
    bodyCtl = SE.create('svcItemBody', {
      height: 360,
      baseHref: BASE,
      contentsCss: ['https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', ADMIN_CSS],
      baseFloatZIndex: 10000200,
      onChange: refreshKwList,
      onCaret: updateSelBar
    });
    bodyCtl.ready(cb);
  }

  function refreshKwList() {
    if (!bodyCtl) return;
    SE.renderKeywordList(document.getElementById('svcKwList'), bodyCtl.getKeywords(), dictMap, (kw, row) => {
      tipReturnToItem = true;
      openTip({ id: row ? row.id : 0, keyword: kw });
    });
  }

  function updateSelBar(markerKeyword, selectedText) {
    const hint = document.getElementById('svcSelHint');
    const bWrap = document.getElementById('svcBtnWrap');
    const bEdit = document.getElementById('svcBtnEditCurrent');
    const bUn = document.getElementById('svcBtnUnwrap');
    if (markerKeyword) {
      hint.innerHTML = '<i class="fa-solid fa-comment-dots me-1" style="color:#F25B22"></i> Đang ở từ khoá <b>' + SE.escHtml(markerKeyword) + '</b>';
      bWrap.disabled = true; bEdit.disabled = false; bUn.disabled = false;
      bEdit.dataset.kw = markerKeyword;
    } else if (selectedText) {
      hint.innerHTML = '<i class="fa-solid fa-highlighter me-1" style="color:#F25B22"></i> Đã chọn: <b>' + SE.escHtml(selectedText) + '</b>';
      bWrap.disabled = false; bEdit.disabled = true; bUn.disabled = true;
    } else {
      hint.innerHTML = '<i class="fa-solid fa-i-cursor me-1"></i> Bôi đen một cụm từ trong nội dung để gắn tooltip cho nó.';
      bWrap.disabled = true; bEdit.disabled = true; bUn.disabled = true;
    }
  }

  function fillDictSelect() {
    const sel = document.getElementById('svcDictSelect');
    sel.innerHTML = '<option value="">Chèn từ khoá có sẵn…</option>';
    Object.keys(dictMap).sort().forEach(k => {
      const o = document.createElement('option');
      o.value = dictMap[k].keyword;
      o.textContent = dictMap[k].keyword;
      sel.appendChild(o);
    });
  }

  function openItem(id) {
    post({ action: 'get_service_item', id })
      .then(res => {
        if (!res.success) { alert(res.error || 'Không tải được mục.'); return; }
        currentItem = res.item;
        dictMap = SE.dictToMap(res.item.dictionary);
        document.getElementById('svcItemId').value = res.item.id;
        document.getElementById('svcItemModalTitle').innerText = res.item.title;
        document.getElementById('svcItemTitle').value = res.item.title;
        document.getElementById('svcItemImgUrl').value = res.item.image_url || '';
        document.getElementById('svcItemImgPreview').src = res.item.image_src || '';
        document.getElementById('svcItemImgStatus').innerHTML = '';
        fillDictSelect();
        openLiveModal('modalLiveEditServiceItem');
        ensureBodyEditor(ctl => {
          ctl.setBody(res.item.body);
          setTimeout(refreshKwList, 150);
          updateSelBar(null, '');
        });
      })
      .catch(err => alert('Lỗi kết nối máy chủ: ' + err));
  }

  function saveItem() {
    const btn = document.getElementById('btnSaveSvcItem');
    const label = '<i class="fa-solid fa-floppy-disk"></i> Lưu mục';
    const id = parseInt(document.getElementById('svcItemId').value, 10);
    const title = document.getElementById('svcItemTitle').value.trim();
    if (!title) { alert('Tên mục không được để trống.'); return; }
    busy(btn, true, label);
    post({
      action: 'save_service_item', id, title,
      image_url: document.getElementById('svcItemImgUrl').value.trim(),
      body: bodyCtl ? bodyCtl.getBody() : (currentItem ? currentItem.body : '')
    })
      .then(res => {
        busy(btn, false, label);
        if (!res.success) { alert(res.error || 'Lỗi khi lưu.'); return; }
        applyItemToPage(res.item);
        closeLiveModal('modalLiveEditServiceItem');
        toast('✓ ' + (res.message || 'Đã lưu mục dịch vụ!'));
      })
      .catch(err => { busy(btn, false, label); alert('Lỗi kết nối máy chủ: ' + err); });
  }

  /** Refresh the accordion entry in place, no reload needed */
  function applyItemToPage(item) {
    document.querySelectorAll('.service-item[data-service-id="' + item.id + '"]').forEach(el => {
      const name = el.querySelector('.service-name');
      if (name) name.textContent = item.title;
      const body = el.querySelector('.service-body');
      if (!body) return;
      let html = '';
      if (item.image_src) {
        html += '<img src="' + item.image_src + '" alt="' + SE.escHtml(item.title) + '" class="service-body-img img-fluid rounded mb-3" loading="lazy">';
      }
      body.innerHTML = html + item.body_html;
      bindTooltipClamp(body);
    });
    inject();
  }

  /** Same viewport clamping main.js applies on page load, for freshly inserted keywords */
  function bindTooltipClamp(scope) {
    scope.querySelectorAll('.tuong-tac').forEach(el => {
      const tip = el.querySelector('.chu-thich');
      if (!tip || el._clampBound) return;
      el._clampBound = true;
      el.addEventListener('mouseenter', () => {
        if (window.innerWidth < 992) return;
        tip.style.left = '50%';
        tip.style.transform = 'translateX(-50%) translateY(0)';
        const r = tip.getBoundingClientRect(), m = 24;
        if (r.left < m) tip.style.transform = 'translateX(calc(-50% + ' + (m - r.left) + 'px)) translateY(0)';
        else if (r.right > window.innerWidth - m) tip.style.transform = 'translateX(calc(-50% - ' + (r.right - (window.innerWidth - m)) + 'px)) translateY(0)';
      });
      el.addEventListener('mouseleave', () => { if (window.innerWidth >= 992) tip.style.transform = ''; });
    });
  }

  function uploadItemImage(input) {
    if (!input.files || !input.files[0]) return;
    const status = document.getElementById('svcItemImgStatus');
    status.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang tải lên...';
    const fd = new FormData();
    fd.append('action', 'upload_image');
    fd.append('image', input.files[0]);
    fd.append('subdir', 'media');
    fd.append('prefix', 'service');
    fetch(AJAX, { method: 'POST', body: fd }).then(r => r.json()).then(res => {
      if (!res.success) { status.innerHTML = ''; alert(res.error || 'Lỗi tải ảnh.'); return; }
      document.getElementById('svcItemImgUrl').value = res.path;
      document.getElementById('svcItemImgPreview').src = res.full_url;
      status.innerHTML = '<span style="color:#16A34A;font-weight:700;"><i class="fa-solid fa-circle-check"></i> Đã tải lên, bấm Lưu mục để áp dụng</span>';
    }).catch(err => { status.innerHTML = ''; alert('Lỗi kết nối máy chủ: ' + err); });
    input.value = '';
  }

  // ------------------------------------------------------------------
  // Tooltip editor
  // ------------------------------------------------------------------
  function ensureTipEditor(cb) {
    if (tipCtl) { tipCtl.ready(cb); return; }
    tipCtl = SE.create('tipContent', {
      height: 240,
      baseHref: BASE,
      contentsCss: [ADMIN_CSS],
      baseFloatZIndex: 10000300,
      inline: true,
      toolbar: [
        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'RemoveFormat'] },
        { name: 'links', items: ['Link', 'Unlink'] },
        { name: 'document', items: ['Source'] }
      ]
    });
    tipCtl.ready(cb);
  }

  function openTip(ref) {
    post({ action: 'get_tooltip', id: ref.id || 0, keyword: ref.keyword || '' })
      .then(res => {
        if (!res.success) { alert(res.error || 'Không tải được tooltip.'); return; }
        const t = res.tooltip;
        document.getElementById('tipId').value = t.id;
        document.getElementById('tipKeyword').value = t.keyword;
        document.getElementById('tipTitle').value = t.title || '';
        document.getElementById('tipModalKeyword').innerText = t.keyword || '(mới)';
        document.getElementById('tipNewNote').style.display = res.exists ? 'none' : 'block';
        openLiveModal('modalLiveEditTooltip');
        ensureTipEditor(ctl => ctl.editor.setData(t.content || ''));
      })
      .catch(err => alert('Lỗi kết nối máy chủ: ' + err));
  }

  function saveTip() {
    const btn = document.getElementById('btnSaveTip');
    const label = '<i class="fa-solid fa-floppy-disk"></i> Lưu tooltip';
    const keyword = document.getElementById('tipKeyword').value.trim();
    if (!keyword) { alert('Từ khoá không được để trống.'); return; }
    busy(btn, true, label);
    post({
      action: 'save_tooltip',
      id: parseInt(document.getElementById('tipId').value, 10) || 0,
      keyword,
      title: document.getElementById('tipTitle').value.trim(),
      content: tipCtl ? tipCtl.editor.getData() : ''
    })
      .then(res => {
        busy(btn, false, label);
        if (!res.success) { alert(res.error || 'Lỗi khi lưu tooltip.'); return; }
        const t = res.tooltip;
        // refresh every hover panel of this keyword already on the page
        const key = SE.normalise(t.keyword);
        document.querySelectorAll('.tuong-tac').forEach(el => {
          const k = SE.normalise(el.getAttribute('data-tip-key') || '');
          const sameId = parseInt(el.getAttribute('data-tip-id') || '0', 10) === t.id;
          if (k === key || sameId) {
            const note = el.querySelector('.chu-thich');
            if (note) note.innerHTML = t.note_html;
            el.setAttribute('data-tip-id', t.id);
            el.setAttribute('data-tip-key', t.keyword);
          }
        });
        dictMap[key] = { id: t.id, keyword: t.keyword, title: t.title };
        fillDictSelect();
        refreshKwList();
        closeLiveModal('modalLiveEditTooltip');
        toast('✓ ' + (res.message || 'Đã lưu tooltip!'));
      })
      .catch(err => { busy(btn, false, label); alert('Lỗi kết nối máy chủ: ' + err); });
  }

  // ------------------------------------------------------------------
  // Wiring
  // ------------------------------------------------------------------
  function bind() {
    const onceBind = (id, ev, fn) => { const el = document.getElementById(id); if (el && !el._svcBound) { el._svcBound = true; el.addEventListener(ev, fn); } };
    onceBind('btnSaveSvcItem', 'click', saveItem);
    onceBind('btnSaveTip', 'click', saveTip);
    onceBind('svcItemImgFile', 'change', function () { uploadItemImage(this); });
    onceBind('svcItemImgUrl', 'input', function () {
      const v = this.value.trim();
      document.getElementById('svcItemImgPreview').src = v ? (/^https?:\/\//.test(v) ? v : BASE + v.replace(/^\//, '')) : '';
    });
    onceBind('svcBtnWrap', 'click', () => {
      if (!bodyCtl) return;
      const kw = bodyCtl.wrapSelection();
      if (!kw) return;
      refreshKwList();
      if (!dictMap[SE.normalise(kw)]) { tipReturnToItem = true; openTip({ id: 0, keyword: kw }); }
    });
    onceBind('svcBtnEditCurrent', 'click', function () {
      if (!this.dataset.kw) return;
      const row = dictMap[SE.normalise(this.dataset.kw)];
      tipReturnToItem = true;
      openTip({ id: row ? row.id : 0, keyword: this.dataset.kw });
    });
    onceBind('svcBtnUnwrap', 'click', () => { if (bodyCtl && bodyCtl.unwrapCurrent()) { refreshKwList(); updateSelBar(null, ''); } });
    onceBind('svcDictSelect', 'change', function () {
      if (!this.value || !bodyCtl) return;
      bodyCtl.insertKeyword(this.value);
      this.value = '';
      refreshKwList();
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bind); else bind();

  window.vnLiveServices = { inject, openItem, openTip };
})();
</script>
<?php if (!empty($liveProgramKey) && Program::isValidKey($liveProgramKey)):
  $liveProgram = Program::get($liveProgramKey);
  $liveProgramApply = Setting::get('program_' . $liveProgramKey . '_apply_url', '');
?>
<!-- Live Edit: Talent / Interns program page -->
<div class="vnchip-live-modal" id="modalLiveEditProgram">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveEditProgram')"></div>
  <div class="vnchip-modal-dialog" style="max-width: 920px;">
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title">
        <i class="fa-solid fa-graduation-cap" style="color: #16A34A;"></i> Sửa trang <?= e($liveProgram['title']) ?>
      </h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveEditProgram')">&times;</button>
    </div>

    <div class="vnchip-modal-tabs">
      <button type="button" class="vnchip-tab-link active" data-tab="prog-tab-info" onclick="switchModalTab('modalLiveEditProgram', 'prog-tab-info')"><i class="fa-solid fa-align-left me-1"></i> Giới thiệu</button>
      <button type="button" class="vnchip-tab-link" data-tab="prog-tab-extra" onclick="switchModalTab('modalLiveEditProgram', 'prog-tab-extra')"><i class="fa-solid fa-table-cells-large me-1"></i> <?= $liveProgramKey === 'talent' ? 'Khối học phần' : 'Khung thông báo' ?></button>
      <button type="button" class="vnchip-tab-link" data-tab="prog-tab-steps" onclick="switchModalTab('modalLiveEditProgram', 'prog-tab-steps')"><i class="fa-solid fa-list-ol me-1"></i> Các bước <span class="prog-steps-count"></span></button>
      <button type="button" class="vnchip-tab-link" data-tab="prog-tab-apply" onclick="switchModalTab('modalLiveEditProgram', 'prog-tab-apply')"><i class="fa-solid fa-paper-plane me-1"></i> Nút Apply</button>
    </div>

    <div class="vnchip-modal-body">
      <div class="vnchip-tab-pane active" id="prog-tab-info">
        <div class="vnchip-form-group">
          <label class="vnchip-form-label">Tên chương trình (tiêu đề trang):</label>
          <input type="text" id="progTitle" class="vnchip-form-input" style="font-weight: 800; font-size: 1rem;">
        </div>
        <div class="vnchip-form-group">
          <label class="vnchip-form-label">Nhãn (badge) dưới tiêu đề <span style="color:#94A3B8;font-weight:400;">— VD: 35–50 participants / year · 3 months</span>:</label>
          <input type="text" id="progBadge" class="vnchip-form-input">
        </div>
        <div class="vnchip-form-group">
          <label class="vnchip-form-label">Câu mô tả ngắn (in đậm):</label>
          <textarea id="progSubtitle" rows="2" class="vnchip-form-textarea"></textarea>
        </div>
        <div class="vnchip-form-group">
          <label class="vnchip-form-label">Đoạn giới thiệu <span style="color:#94A3B8;font-weight:400;">— mỗi đoạn cách nhau một dòng trống</span>:</label>
          <textarea id="progDesc" rows="8" class="vnchip-form-textarea"></textarea>
        </div>
        <div class="vnchip-form-group" style="margin-bottom:0;">
          <label class="vnchip-form-label">Chữ trước “Process” ở tiêu đề quy trình <span style="color:#94A3B8;font-weight:400;">— VD: Selection, Recruitment</span>:</label>
          <input type="text" id="progProcess" class="vnchip-form-input" style="max-width: 320px;">
        </div>
      </div>

      <div class="vnchip-tab-pane" id="prog-tab-extra">
        <?php if ($liveProgramKey === 'talent'): ?>
          <div style="font-size:0.8rem;color:#64748B;margin-bottom:10px;">Ba thẻ học phần với icon, tên và số giờ. Icon dùng tên Font Awesome (VD: fa-microchip, fa-user-tie, fa-people-group, fa-code, fa-chart-line).</div>
          <div class="vnchip-repeater-list" id="progModules"></div>
          <button type="button" class="vnchip-btn vnchip-btn-secondary" style="margin-top:10px;" onclick="vnLiveProgram.addModule()"><i class="fa-solid fa-plus"></i> Thêm học phần</button>
        <?php else: ?>
          <div class="vnchip-form-group">
            <label class="vnchip-form-label">Tiêu đề khung thông báo:</label>
            <input type="text" id="progAnnTitle" class="vnchip-form-input">
          </div>
          <div class="vnchip-form-group" style="margin-bottom:0;">
            <label class="vnchip-form-label">Nội dung khung thông báo <span style="color:#94A3B8;font-weight:400;">— để trống cả hai để ẩn khung</span>:</label>
            <textarea id="progAnnDesc" rows="3" class="vnchip-form-textarea"></textarea>
          </div>
        <?php endif; ?>
      </div>

      <div class="vnchip-tab-pane" id="prog-tab-steps">
        <div style="font-size:0.8rem;color:#64748B;margin-bottom:10px;">Mỗi bước có ảnh, tiêu đề và các gạch đầu dòng (mỗi dòng một ý). Dùng mũi tên để đổi thứ tự.</div>
        <div class="vnchip-repeater-list" id="progSteps"></div>
        <button type="button" class="vnchip-btn vnchip-btn-secondary" style="margin-top:10px;" onclick="vnLiveProgram.addStep()"><i class="fa-solid fa-plus"></i> Thêm bước</button>
      </div>

      <div class="vnchip-tab-pane" id="prog-tab-apply">
        <div class="vnchip-form-group" style="margin-bottom:0;">
          <label class="vnchip-form-label">Link nút Apply:</label>
          <input type="text" id="progApplyUrl" class="vnchip-form-input" value="<?= e($liveProgramApply) ?>" placeholder="https://forms.gle/... hoặc talk-to-us.php">
          <div style="font-size:0.76rem;color:#64748B;margin-top:6px;">Link ngoài (https://...) mở tab mới. Để trống sẽ dẫn tới trang Talk To Us.</div>
        </div>
      </div>
    </div>

    <div class="vnchip-modal-footer">
      <button type="button" class="vnchip-btn vnchip-btn-secondary" style="margin-right:auto;color:#B91C1C;border-color:#FECACA;" id="btnResetProgram" title="Bỏ mọi chỉnh sửa, quay về nội dung gốc của trang">
        <i class="fa-solid fa-rotate-left"></i> Khôi phục mặc định
      </button>
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveEditProgram')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnSaveProgram" style="background: linear-gradient(135deg, #16A34A 0%, #15803D 100%);">
        <i class="fa-solid fa-floppy-disk"></i> Lưu trang chương trình
      </button>
    </div>
  </div>
</div>

<script>
(function () {
  const AJAX = '<?= admin_url("ajax/live-edit.php") ?>';
  const KEY = <?= json_encode($liveProgramKey) ?>;
  const IS_TALENT = KEY === 'talent';
  const data = <?= json_encode($liveProgram, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  const IMG_BASE = '<?= asset('images/programs/') ?>';
  const SITE_BASE = '<?= absolute_url() ?>';
  const CUSTOMISED = <?= Program::isCustomised($liveProgramKey) ? 'true' : 'false' ?>;

  // textContent, not innerText: innerText would turn the newlines between bullet points into <br>
  const esc = s => { const d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML.replace(/"/g, '&quot;'); };
  const imgSrc = img => !img ? '' : (/^https?:\/\//.test(img) ? img : (img.indexOf('/') === -1 ? IMG_BASE + img : SITE_BASE + img));
  function toast(msg, ok = true) {
    const t = document.getElementById('live-edit-toast'), tx = document.getElementById('live-edit-toast-text');
    if (!t || !tx) return;
    tx.innerText = msg; t.style.borderLeftColor = ok ? '#10B981' : '#F59E0B'; t.style.display = 'inline-flex';
    clearTimeout(t._timer); t._timer = setTimeout(() => { t.style.display = 'none'; }, 3500);
  }

  // ---------- form <-> data ----------
  function fill() {
    document.getElementById('progTitle').value = data.title || '';
    document.getElementById('progBadge').value = data.badge || '';
    document.getElementById('progSubtitle').value = data.subtitle || '';
    document.getElementById('progDesc').value = (data.desc || []).join('\n\n');
    document.getElementById('progProcess').value = data.process || '';
    if (IS_TALENT) renderModules(); else {
      document.getElementById('progAnnTitle').value = (data.announcement || {}).title || '';
      document.getElementById('progAnnDesc').value = (data.announcement || {}).desc || '';
    }
    renderSteps();
    document.getElementById('btnResetProgram').style.display = CUSTOMISED ? '' : 'none';
  }

  function collect() {
    collectSteps(); if (IS_TALENT) collectModules();
    const out = {
      title: document.getElementById('progTitle').value.trim(),
      badge: document.getElementById('progBadge').value.trim(),
      subtitle: document.getElementById('progSubtitle').value.trim(),
      desc: document.getElementById('progDesc').value.split(/\n\s*\n/).map(s => s.replace(/\s*\n\s*/g, ' ').trim()).filter(Boolean),
      process: document.getElementById('progProcess').value.trim(),
      steps: data.steps
    };
    if (IS_TALENT) out.modules = data.modules;
    else out.announcement = { title: document.getElementById('progAnnTitle').value.trim(), desc: document.getElementById('progAnnDesc').value.trim() };
    return out;
  }

  // ---------- modules repeater (talent) ----------
  function renderModules() {
    const list = document.getElementById('progModules'); if (!list) return;
    list.innerHTML = '';
    (data.modules || []).forEach((m, i) => {
      const row = document.createElement('div');
      row.className = 'vnchip-repeater-item';
      row.innerHTML = `
        <div class="vnchip-repeater-head">
          <span class="vnchip-repeater-num"><i class="fa-solid ${esc(m.icon || 'fa-microchip')}" style="color:#16A34A;margin-right:6px;"></i>Học phần ${i + 1}</span>
          <span class="vnchip-repeater-actions">
            <button type="button" class="vnchip-mini-btn" onclick="vnLiveProgram.moveModule(${i}, -1)" title="Lên"><i class="fa-solid fa-arrow-up"></i></button>
            <button type="button" class="vnchip-mini-btn" onclick="vnLiveProgram.moveModule(${i}, 1)" title="Xuống"><i class="fa-solid fa-arrow-down"></i></button>
            <button type="button" class="vnchip-mini-btn danger" onclick="vnLiveProgram.removeModule(${i})" title="Xoá"><i class="fa-solid fa-trash"></i></button>
          </span>
        </div>
        <div style="display:grid;grid-template-columns:150px 1fr 120px;gap:10px;">
          <div><label class="vnchip-form-label">Icon</label><input type="text" class="vnchip-form-input mod-icon" value="${esc(m.icon || '')}" placeholder="fa-microchip"></div>
          <div><label class="vnchip-form-label">Tên học phần</label><input type="text" class="vnchip-form-input mod-title" value="${esc(m.title || '')}"></div>
          <div><label class="vnchip-form-label">Số giờ</label><input type="text" class="vnchip-form-input mod-hours" value="${esc(m.hours || '')}" placeholder="120 hours"></div>
        </div>`;
      list.appendChild(row);
    });
    if (!(data.modules || []).length) list.innerHTML = '<div style="font-size:0.8rem;color:#64748B;">Không có học phần nào — khối này sẽ ẩn trên trang.</div>';
  }
  function collectModules() {
    const rows = document.querySelectorAll('#progModules .vnchip-repeater-item');
    if (!rows.length && !(data.modules || []).length) return;
    data.modules = [...rows].map(r => ({
      icon: r.querySelector('.mod-icon').value.trim(),
      title: r.querySelector('.mod-title').value.trim(),
      hours: r.querySelector('.mod-hours').value.trim()
    }));
  }

  // ---------- steps repeater ----------
  function renderSteps() {
    const list = document.getElementById('progSteps');
    list.innerHTML = '';
    (data.steps || []).forEach((s, i) => {
      const row = document.createElement('div');
      row.className = 'vnchip-repeater-item';
      row.innerHTML = `
        <div class="vnchip-repeater-head">
          <span class="vnchip-repeater-num">Bước ${i + 1}</span>
          <span class="vnchip-repeater-actions">
            <button type="button" class="vnchip-mini-btn" onclick="vnLiveProgram.moveStep(${i}, -1)" title="Lên"><i class="fa-solid fa-arrow-up"></i></button>
            <button type="button" class="vnchip-mini-btn" onclick="vnLiveProgram.moveStep(${i}, 1)" title="Xuống"><i class="fa-solid fa-arrow-down"></i></button>
            <button type="button" class="vnchip-mini-btn danger" onclick="vnLiveProgram.removeStep(${i})" title="Xoá bước"><i class="fa-solid fa-trash"></i></button>
          </span>
        </div>
        <div style="display:flex;gap:14px;align-items:flex-start;flex-wrap:wrap;">
          <div style="flex:none;width:150px;">
            <img class="step-img-preview" src="${esc(imgSrc(s.img))}" alt="" style="width:150px;height:96px;object-fit:cover;border-radius:8px;border:1px solid #CBD5E1;background:#F1F5F9;display:block;">
            <input type="file" accept="image/*" style="display:none;" class="step-img-file" onchange="vnLiveProgram.uploadStepImage(this, ${i})">
            <button type="button" class="vnchip-mini-btn" style="margin-top:6px;width:100%;justify-content:center;" onclick="this.previousElementSibling.click()"><i class="fa-solid fa-cloud-arrow-up"></i> Đổi ảnh</button>
            <input type="hidden" class="step-img" value="${esc(s.img || '')}">
          </div>
          <div style="flex:1;min-width:240px;">
            <label class="vnchip-form-label">Tiêu đề bước</label>
            <input type="text" class="vnchip-form-input step-title" value="${esc(s.title || '')}" placeholder="VD: 1. Application Submission">
            <label class="vnchip-form-label" style="margin-top:8px;">Các ý (mỗi dòng một ý)</label>
            <textarea class="vnchip-form-textarea step-points" rows="3">${esc((s.points || []).join('\n'))}</textarea>
          </div>
        </div>`;
      list.appendChild(row);
    });
    document.querySelectorAll('.prog-steps-count').forEach(el => { el.textContent = '(' + (data.steps || []).length + ')'; });
  }
  function collectSteps() {
    const rows = document.querySelectorAll('#progSteps .vnchip-repeater-item');
    data.steps = [...rows].map(r => ({
      img: r.querySelector('.step-img').value.trim(),
      title: r.querySelector('.step-title').value.trim(),
      points: r.querySelector('.step-points').value.split(/\r?\n/).map(s => s.trim()).filter(Boolean)
    }));
  }

  function move(arr, i, dir) { const j = i + dir; if (j < 0 || j >= arr.length) return; [arr[i], arr[j]] = [arr[j], arr[i]]; }

  window.vnLiveProgram = {
    open() { fill(); openLiveModal('modalLiveEditProgram', 'prog-tab-info'); },
    addStep() { collectSteps(); data.steps.push({ img: '', title: '', points: [] }); renderSteps(); },
    removeStep(i) { collectSteps(); if (!confirm('Xoá bước ' + (i + 1) + '?')) return; data.steps.splice(i, 1); renderSteps(); },
    moveStep(i, d) { collectSteps(); move(data.steps, i, d); renderSteps(); },
    addModule() { collectModules(); (data.modules = data.modules || []).push({ icon: 'fa-microchip', title: '', hours: '' }); renderModules(); },
    removeModule(i) { collectModules(); data.modules.splice(i, 1); renderModules(); },
    moveModule(i, d) { collectModules(); move(data.modules, i, d); renderModules(); },
    uploadStepImage(input, i) {
      if (!input.files || !input.files[0]) return;
      const row = input.closest('.vnchip-repeater-item');
      const fd = new FormData();
      fd.append('action', 'upload_image'); fd.append('image', input.files[0]); fd.append('subdir', 'media'); fd.append('prefix', 'program');
      fetch(AJAX, { method: 'POST', body: fd }).then(r => r.json()).then(res => {
        if (!res.success) { alert(res.error || 'Lỗi tải ảnh.'); return; }
        row.querySelector('.step-img').value = res.path;
        row.querySelector('.step-img-preview').src = res.full_url;
        toast('✓ Đã tải ảnh lên, bấm Lưu để áp dụng');
      }).catch(err => alert('Lỗi kết nối máy chủ: ' + err));
      input.value = '';
    }
  };

  function save() {
    const btn = document.getElementById('btnSaveProgram');
    const label = '<i class="fa-solid fa-floppy-disk"></i> Lưu trang chương trình';
    const payload = collect();
    if (!payload.title) { alert('Tên chương trình không được để trống.'); return; }
    btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...';
    const fd = new FormData();
    fd.append('action', 'save_program'); fd.append('key', KEY);
    fd.append('data', JSON.stringify(payload));
    fd.append('apply_url', document.getElementById('progApplyUrl').value.trim());
    fetch(AJAX, { method: 'POST', body: fd }).then(r => r.json()).then(res => {
      btn.disabled = false; btn.innerHTML = label;
      if (!res.success) { alert(res.error || 'Lỗi khi lưu.'); return; }
      toast('✓ Đã lưu, đang tải lại trang...');
      setTimeout(() => location.reload(), 600);
    }).catch(err => { btn.disabled = false; btn.innerHTML = label; alert('Lỗi kết nối máy chủ: ' + err); });
  }

  function reset() {
    if (!confirm('Khôi phục nội dung gốc của trang này? Mọi chỉnh sửa (kể cả các bước và ảnh đã đổi) sẽ bị bỏ.')) return;
    const fd = new FormData(); fd.append('action', 'reset_program'); fd.append('key', KEY);
    fetch(AJAX, { method: 'POST', body: fd }).then(r => r.json()).then(res => {
      if (!res.success) { alert(res.error || 'Không khôi phục được.'); return; }
      toast('✓ Đã khôi phục mặc định, đang tải lại...');
      setTimeout(() => location.reload(), 600);
    });
  }

  function bind() {
    document.getElementById('btnSaveProgram').addEventListener('click', save);
    document.getElementById('btnResetProgram').addEventListener('click', reset);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bind); else bind();
})();
</script>
<?php endif; ?>

<!-- Live Text: one small editor for any element marked data-lt (see lt()/lt_attr() in includes/functions.php) -->
<div class="vnchip-live-modal" id="modalLiveText" style="z-index: 10000050;">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveText')"></div>
  <div class="vnchip-modal-dialog" style="max-width: 720px;">
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title"><i class="fa-solid fa-i-cursor" style="color: #F59E0B;"></i> <span id="ltModalTitle">Sửa nội dung</span></h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveText')">&times;</button>
    </div>
    <div class="vnchip-modal-body">
      <div class="vnchip-form-group" style="margin-bottom: 8px;">
        <label class="vnchip-form-label" id="ltFieldLabel">Nội dung:</label>
        <input type="text" id="ltInput" class="vnchip-form-input" style="display: none;">
        <textarea id="ltTextarea" rows="4" class="vnchip-form-textarea" style="display: none;"></textarea>
      </div>
      <div style="font-size: 0.74rem; color: #94A3B8;" id="ltHint"></div>
    </div>
    <div class="vnchip-modal-footer">
      <button type="button" class="vnchip-btn vnchip-btn-secondary" id="btnLtReset" style="margin-right: auto; color: #B91C1C; border-color: #FECACA;" title="Bỏ nội dung đã sửa, quay về chữ gốc trong giao diện">
        <i class="fa-solid fa-rotate-left"></i> Về mặc định
      </button>
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveText')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnLtSave" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);">
        <i class="fa-solid fa-floppy-disk"></i> Lưu
      </button>
    </div>
  </div>
</div>

<!-- Live Image: replace any element marked data-lt-img -->
<div class="vnchip-live-modal" id="modalLiveImage" style="z-index: 10000050;">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveImage')"></div>
  <div class="vnchip-modal-dialog" style="max-width: 640px;">
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title"><i class="fa-solid fa-image" style="color: #F59E0B;"></i> <span id="liModalTitle">Đổi ảnh</span></h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveImage')">&times;</button>
    </div>
    <div class="vnchip-modal-body">
      <div id="liSizeHint" style="display: none; background: #EFF6FF; border: 1px solid #BFDBFE; color: #1E3A8A; border-radius: 10px; padding: 10px 12px; font-size: 0.8rem; margin-bottom: 12px;"></div>
      <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
        <div style="flex: none;">
          <img id="liPreview" src="" alt="" style="width: 220px; height: 130px; object-fit: contain; background: #F1F5F9; border: 1px solid #CBD5E1; border-radius: 10px; display: block;">
          <div id="liDims" style="font-size: 0.74rem; color: #64748B; margin-top: 4px; text-align: center;"></div>
        </div>
        <div style="flex: 1; min-width: 220px;">
          <input type="file" id="liFile" accept="image/*" style="display: none;">
          <button type="button" class="vnchip-btn vnchip-btn-primary" onclick="document.getElementById('liFile').click()" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);">
            <i class="fa-solid fa-cloud-arrow-up"></i> Tải ảnh mới lên
          </button>
          <span id="liStatus" style="font-size: 0.78rem; margin-left: 8px;"></span>
          <label class="vnchip-form-label" style="margin-top: 10px;">Hoặc đường dẫn ảnh:</label>
          <input type="text" id="liPath" class="vnchip-form-input" style="font-family: monospace; font-size: 0.78rem;" placeholder="uploads/media/....webp">
        </div>
      </div>
    </div>
    <div class="vnchip-modal-footer">
      <button type="button" class="vnchip-btn vnchip-btn-secondary" id="btnLiHide" style="margin-right: 6px; color: #B91C1C; border-color: #FECACA; display: none;"><i class="fa-solid fa-eye-slash"></i> Ẩn ảnh này</button>
      <button type="button" class="vnchip-btn vnchip-btn-secondary" id="btnLiReset" style="margin-right: auto; color: #B91C1C; border-color: #FECACA;"><i class="fa-solid fa-rotate-left"></i> Về ảnh gốc</button>
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveImage')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnLiSave" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);"><i class="fa-solid fa-floppy-disk"></i> Lưu</button>
    </div>
  </div>
</div>

<script>
/**
 * Live Text / Live Image layer.
 * Any element carrying data-lt="setting_key" gets a pencil in edit mode; saving writes the
 * setting and updates the element in place. data-lt-html="1" edits HTML, data-lt-attr="href"
 * edits an attribute instead of the text. data-lt-img="setting_key" does the same for images.
 */
(function () {
  const AJAX = '<?= admin_url("ajax/live-edit.php") ?>';
  const BASE = '<?= absolute_url() ?>';
  let cur = null;      // element being edited
  let curImg = null;

  function toast(msg, ok = true) {
    const t = document.getElementById('live-edit-toast'), tx = document.getElementById('live-edit-toast-text');
    if (!t || !tx) return;
    tx.innerText = msg; t.style.borderLeftColor = ok ? '#10B981' : '#F59E0B'; t.style.display = 'inline-flex';
    clearTimeout(t._timer); t._timer = setTimeout(() => { t.style.display = 'none'; }, 3500);
  }
  function post(data) {
    const fd = new FormData(); Object.keys(data).forEach(k => fd.append(k, data[k]));
    return fetch(AJAX, { method: 'POST', body: fd }).then(r => r.json());
  }
  function penFor(el) { return [...el.children].find(c => c.classList && c.classList.contains('live-lt-pen')); }
  function currentValue(el) {
    const attr = el.dataset.ltAttr;
    if (attr) return el.getAttribute(attr) || '';
    const clone = el.cloneNode(true);
    clone.querySelectorAll('.live-lt-pen').forEach(p => p.remove());
    return el.dataset.ltHtml ? clone.innerHTML.trim() : clone.textContent.replace(/\s+/g, ' ').trim();
  }
  function applyValue(el, val) {
    const attr = el.dataset.ltAttr;
    if (attr) { el.setAttribute(attr, val); return; }
    const pen = penFor(el);
    if (pen) pen.remove();
    if (el.dataset.ltHtml) el.innerHTML = val; else el.textContent = val;
    if (pen) el.appendChild(pen);
  }

  function inject() {
    document.querySelectorAll('[data-lt]').forEach(el => {
      if (el.dataset.ltBound) return;
      el.dataset.ltBound = '1';
      el.classList.add('live-lt');
      const pen = document.createElement('button');
      pen.type = 'button';
      pen.className = 'live-lt-pen';
      pen.innerHTML = '<i class="fa-solid fa-pen"></i>';
      pen.title = 'Sửa: ' + (el.dataset.ltLabel || el.dataset.lt);
      pen.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); openText(el); });
      el.appendChild(pen);
    });
    // blocks managed in an admin screen: a badge that opens that screen in a new tab
    document.querySelectorAll('[data-lt-admin]').forEach(el => {
      if (el.dataset.ltAdminBound) return;
      el.dataset.ltAdminBound = '1';
      el.classList.add('live-editable-wrap', 'live-admin-wrap');
      const a = document.createElement('a');
      a.className = 'live-edit-badge live-admin-badge';
      a.href = el.dataset.ltAdmin;
      a.target = '_blank';
      a.rel = 'noopener noreferrer';
      a.innerHTML = '<i class="fa-solid fa-arrow-up-right-from-square"></i> ' + (el.dataset.ltAdminLabel || 'Mở trong Admin');
      a.title = 'Khối này được quản lý trong trang Admin — mở trong tab mới';
      a.addEventListener('click', e => e.stopPropagation());
      el.appendChild(a);
    });
    document.querySelectorAll('[data-lt-img]').forEach(img => {
      if (img.dataset.ltBound) return;
      img.dataset.ltBound = '1';
      const host = img.tagName === 'IMG' ? img.parentElement : img;
      if (!host) return;
      host.classList.add('live-lt-img-wrap');
      const pen = document.createElement('button');
      pen.type = 'button';
      pen.className = 'live-lt-pen live-lt-pen--img';
      pen.innerHTML = '<i class="fa-solid fa-image"></i>';
      pen.title = 'Đổi ảnh: ' + (img.dataset.ltLabel || img.dataset.ltImg);
      pen.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); openImage(img); });
      host.appendChild(pen);
    });
  }

  // ---------------- text ----------------
  function openText(el) {
    cur = el;
    const html = !!el.dataset.ltHtml, attr = el.dataset.ltAttr;
    const val = currentValue(el);
    const useArea = html || val.length > 70 || val.indexOf('\n') !== -1;
    document.getElementById('ltModalTitle').innerText = el.dataset.ltLabel || 'Sửa nội dung';
    document.getElementById('ltFieldLabel').innerText = attr ? ('Giá trị ' + attr + ':') : (html ? 'Nội dung (HTML):' : 'Nội dung:');
    const inp = document.getElementById('ltInput'), ta = document.getElementById('ltTextarea');
    inp.style.display = useArea ? 'none' : ''; ta.style.display = useArea ? '' : 'none';
    ta.rows = html ? 10 : 4;
    ta.style.fontFamily = html ? 'monospace' : '';
    if (useArea) ta.value = val; else inp.value = val;
    document.getElementById('ltHint').innerText = 'Khoá: ' + el.dataset.lt + (html ? ' · Cho phép HTML (p, b, br, a...)' : '');
    openLiveModal('modalLiveText');
    setTimeout(() => (useArea ? ta : inp).focus(), 80);
  }
  function saveText() {
    if (!cur) return;
    const btn = document.getElementById('btnLtSave');
    const inp = document.getElementById('ltInput'), ta = document.getElementById('ltTextarea');
    const val = (inp.style.display === 'none' ? ta.value : inp.value).trim();
    btn.disabled = true;
    post({ action: 'save_setting', key: cur.dataset.lt, value: val }).then(res => {
      btn.disabled = false;
      if (!res.success) { alert(res.error || 'Lỗi khi lưu.'); return; }
      // every element bound to the same key gets the new value
      document.querySelectorAll('[data-lt="' + cur.dataset.lt + '"]').forEach(el => applyValue(el, val));
      closeLiveModal('modalLiveText');
      toast('✓ Đã lưu');
    }).catch(err => { btn.disabled = false; alert('Lỗi kết nối máy chủ: ' + err); });
  }
  function resetText() {
    if (!cur || !confirm('Quay về chữ gốc trong giao diện cho mục này?')) return;
    post({ action: 'reset_section', key: cur.dataset.lt }).then(res => {
      if (!res.success) { alert(res.error || 'Không khôi phục được.'); return; }
      toast('✓ Đã về mặc định, đang tải lại...'); setTimeout(() => location.reload(), 500);
    });
  }

  // ---------------- image ----------------
  function srcOf(img) {
    if (img.tagName === 'IMG') return img.getAttribute('src') || '';
    const m = (img.style.backgroundImage || '').match(/url\(["']?(.*?)["']?\)/);
    return m ? m[1] : '';
  }
  function setSrc(img, url) {
    if (img.tagName === 'IMG') img.src = url; else img.style.backgroundImage = "url('" + url + "')";
  }
  function showDims(url) {
    const out = document.getElementById('liDims');
    out.innerText = '';
    if (!url) return;
    const probe = new Image();
    probe.onload = () => { out.innerText = 'Ảnh hiện tại: ' + probe.naturalWidth + ' × ' + probe.naturalHeight + ' px'; };
    probe.src = url;
  }
  function openImage(img) {
    curImg = img;
    document.getElementById('liModalTitle').innerText = img.dataset.ltLabel || 'Đổi ảnh';
    const hint = document.getElementById('liSizeHint');
    hint.style.display = img.dataset.ltSize ? '' : 'none';
    hint.innerHTML = img.dataset.ltSize ? '<i class="fa-solid fa-ruler-combined me-1"></i> ' + img.dataset.ltSize.replace(/</g, '&lt;') : '';
    document.getElementById('liPreview').src = srcOf(img);
    showDims(srcOf(img));
    document.getElementById('liPath').value = '';
    document.getElementById('liStatus').innerHTML = '';
    document.getElementById('btnLiHide').style.display = img.dataset.ltHideable ? '' : 'none';
    openLiveModal('modalLiveImage');
  }
  function uploadImage(input) {
    if (!input.files || !input.files[0] || !curImg) return;
    const status = document.getElementById('liStatus');
    status.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang tải lên...';
    const fd = new FormData();
    fd.append('action', 'upload_image'); fd.append('image', input.files[0]);
    fd.append('subdir', 'media'); fd.append('prefix', 'live'); fd.append('setting_key', curImg.dataset.ltImg);
    fetch(AJAX, { method: 'POST', body: fd }).then(r => r.json()).then(res => {
      if (!res.success) { status.innerHTML = ''; alert(res.error || 'Lỗi tải ảnh.'); return; }
      document.getElementById('liPreview').src = res.full_url;
      showDims(res.full_url);
      document.getElementById('liPath').value = res.path;
      document.querySelectorAll('[data-lt-img="' + curImg.dataset.ltImg + '"]').forEach(el => { setSrc(el, res.full_url); el.closest('.live-empty-slot') && el.closest('.live-empty-slot').classList.remove('live-empty-slot'); });
      status.innerHTML = '<span style="color:#16A34A;font-weight:700;"><i class="fa-solid fa-circle-check"></i> Đã thay ảnh</span>';
      toast('✓ Đã tải ảnh lên và thay thế');
    }).catch(err => { status.innerHTML = ''; alert('Lỗi kết nối máy chủ: ' + err); });
    input.value = '';
  }
  function saveImagePath() {
    if (!curImg) return;
    const path = document.getElementById('liPath').value.trim();
    if (!path) { closeLiveModal('modalLiveImage'); return; }
    post({ action: 'save_setting', key: curImg.dataset.ltImg, value: path }).then(res => {
      if (!res.success) { alert(res.error || 'Lỗi khi lưu.'); return; }
      // same mapping as asset() in PHP: uploads/… stays, everything else lives under assets/
      const clean = path.replace(/^\//, '');
      const url = /^https?:\/\//.test(path) ? path : BASE + (clean.indexOf('uploads/') === 0 ? clean : 'assets/' + clean.replace(/^assets\//, ''));
      document.querySelectorAll('[data-lt-img="' + curImg.dataset.ltImg + '"]').forEach(el => setSrc(el, url));
      closeLiveModal('modalLiveImage'); toast('✓ Đã lưu đường dẫn ảnh');
    });
  }
  function hideImage() {
    if (!curImg || !confirm('Ẩn ảnh này khỏi trang?')) return;
    post({ action: 'save_setting', key: curImg.dataset.ltImg, value: 'none' }).then(res => {
      if (!res.success) { alert(res.error || 'Lỗi khi lưu.'); return; }
      toast('✓ Đã ẩn, đang tải lại...'); setTimeout(() => location.reload(), 500);
    });
  }
  function resetImage() {
    if (!curImg || !confirm('Quay về ảnh gốc của giao diện?')) return;
    post({ action: 'reset_section', key: curImg.dataset.ltImg }).then(res => {
      if (!res.success) { alert(res.error || 'Không khôi phục được.'); return; }
      toast('✓ Đã về ảnh gốc, đang tải lại...'); setTimeout(() => location.reload(), 500);
    });
  }

  function bind() {
    const once = (id, ev, fn) => { const el = document.getElementById(id); if (el && !el._ltBound) { el._ltBound = true; el.addEventListener(ev, fn); } };
    once('btnLtSave', 'click', saveText);
    once('btnLtReset', 'click', resetText);
    once('liFile', 'change', function () { uploadImage(this); });
    once('btnLiSave', 'click', saveImagePath);
    once('btnLiHide', 'click', hideImage);
    once('btnLiReset', 'click', resetImage);
    once('ltTextarea', 'keydown', e => { if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') saveText(); });
    once('ltInput', 'keydown', e => { if (e.key === 'Enter') { e.preventDefault(); saveText(); } });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bind); else bind();
  window.vnLiveText = { inject, openText, openImage };
})();
</script>

<?php if (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'business.php'):
  require_once __DIR__ . '/../models/Project.php';
  $liveProjects = Project::all();
?>
<!-- Live Edit: Partners Projects cards on business.php -->
<div class="vnchip-live-modal" id="modalLiveEditProjects">
  <div class="vnchip-modal-backdrop" onclick="closeLiveModal('modalLiveEditProjects')"></div>
  <div class="vnchip-modal-dialog" style="max-width: 940px;">
    <div class="vnchip-modal-header">
      <h5 class="vnchip-modal-title"><i class="fa-solid fa-diagram-project" style="color: #2563EB;"></i> Sửa danh sách Projects</h5>
      <button type="button" class="vnchip-modal-close" onclick="closeLiveModal('modalLiveEditProjects')">&times;</button>
    </div>
    <div class="vnchip-modal-body">
      <div style="font-size: 0.8rem; color: #64748B; margin-bottom: 10px;">Mỗi dự án là một thẻ trong khối PROJECTS và có trang riêng project-detail.php?slug=… Phần “Nội dung trang chi tiết” là HTML (tiêu đề h3.biz-rd-subtitle, danh sách ul.biz-rd-points…).</div>
      <div class="vnchip-repeater-list" id="projList"></div>
      <button type="button" class="vnchip-btn vnchip-btn-secondary" style="margin-top: 10px;" onclick="vnLiveProjects.add()"><i class="fa-solid fa-plus"></i> Thêm dự án</button>
    </div>
    <div class="vnchip-modal-footer">
      <button type="button" class="vnchip-btn vnchip-btn-secondary" id="btnResetProjects" style="margin-right: auto; color: #B91C1C; border-color: #FECACA;<?= Project::isCustomised() ? '' : ' display:none;' ?>"><i class="fa-solid fa-rotate-left"></i> Khôi phục mặc định</button>
      <button type="button" class="vnchip-btn vnchip-btn-secondary" onclick="closeLiveModal('modalLiveEditProjects')">Hủy bỏ</button>
      <button type="button" class="vnchip-btn vnchip-btn-primary" id="btnSaveProjects" style="background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);"><i class="fa-solid fa-floppy-disk"></i> Lưu danh sách dự án</button>
    </div>
  </div>
</div>

<script>
(function () {
  const AJAX = '<?= admin_url("ajax/live-edit.php") ?>';
  const BASE = '<?= absolute_url() ?>';
  const data = <?= json_encode($liveProjects, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  const esc = s => { const d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML.replace(/"/g, '&quot;'); };
  const imgSrc = p => !p ? '' : (/^https?:\/\//.test(p) ? p : BASE + (p.indexOf('uploads/') === 0 ? p : 'assets/' + p.replace(/^assets\//, '')));
  function toast(msg, ok = true) {
    const t = document.getElementById('live-edit-toast'), tx = document.getElementById('live-edit-toast-text');
    if (!t || !tx) return;
    tx.innerText = msg; t.style.borderLeftColor = ok ? '#10B981' : '#F59E0B'; t.style.display = 'inline-flex';
    clearTimeout(t._timer); t._timer = setTimeout(() => { t.style.display = 'none'; }, 3500);
  }

  function render() {
    const list = document.getElementById('projList');
    list.innerHTML = '';
    data.forEach((p, i) => {
      const row = document.createElement('div');
      row.className = 'vnchip-repeater-item';
      row.innerHTML = `
        <div class="vnchip-repeater-head">
          <span class="vnchip-repeater-num">Dự án ${i + 1}</span>
          <span class="vnchip-repeater-actions">
            <button type="button" class="vnchip-mini-btn" onclick="vnLiveProjects.move(${i}, -1)"><i class="fa-solid fa-arrow-up"></i></button>
            <button type="button" class="vnchip-mini-btn" onclick="vnLiveProjects.move(${i}, 1)"><i class="fa-solid fa-arrow-down"></i></button>
            <button type="button" class="vnchip-mini-btn danger" onclick="vnLiveProjects.remove(${i})"><i class="fa-solid fa-trash"></i> Xoá</button>
          </span>
        </div>
        <div style="display:flex;gap:14px;flex-wrap:wrap;align-items:flex-start;">
          <div style="flex:none;width:170px;">
            <img class="pj-img-preview" src="${esc(imgSrc(p.image))}" alt="" style="width:170px;height:110px;object-fit:cover;border-radius:8px;border:1px solid #CBD5E1;background:#F1F5F9;display:block;">
            <input type="file" accept="image/*" style="display:none;" class="pj-img-file" onchange="vnLiveProjects.upload(this)">
            <button type="button" class="vnchip-mini-btn" style="margin-top:6px;width:100%;justify-content:center;" onclick="this.previousElementSibling.click()"><i class="fa-solid fa-cloud-arrow-up"></i> Đổi ảnh</button>
            <input type="text" class="vnchip-form-input pj-img" value="${esc(p.image || '')}" style="font-size:0.72rem;font-family:monospace;margin-top:6px;padding:5px 8px;" placeholder="images/... hoặc uploads/...">
          </div>
          <div style="flex:1;min-width:260px;display:grid;gap:8px;">
            <div><label class="vnchip-form-label">Tên dự án</label><input type="text" class="vnchip-form-input pj-title" value="${esc(p.title || '')}"></div>
            <div><label class="vnchip-form-label">Tiêu đề phụ</label><input type="text" class="vnchip-form-input pj-subtitle" value="${esc(p.subtitle || '')}"></div>
            <div><label class="vnchip-form-label">Slug (đường dẫn, để trống sẽ tự tạo từ tên)</label><input type="text" class="vnchip-form-input pj-slug" value="${esc(p.slug || '')}" style="font-family:monospace;font-size:0.8rem;"></div>
            <div><label class="vnchip-form-label">Tóm tắt (hiện trên thẻ và đầu trang chi tiết)</label><textarea class="vnchip-form-textarea pj-summary" rows="3">${esc(p.summary || '')}</textarea></div>
            <div><label class="vnchip-form-label">Nội dung trang chi tiết (HTML)</label><textarea class="vnchip-form-textarea pj-content" rows="8" style="font-family:monospace;font-size:0.78rem;">${esc(p.content || '')}</textarea></div>
          </div>
        </div>`;
      list.appendChild(row);
    });
    if (!data.length) list.innerHTML = '<div style="font-size:0.8rem;color:#64748B;">Chưa có dự án nào — khối PROJECTS sẽ trống.</div>';
  }
  function collect() {
    const rows = document.querySelectorAll('#projList .vnchip-repeater-item');
    data.length = 0;
    rows.forEach(r => data.push({
      image: r.querySelector('.pj-img').value.trim(),
      title: r.querySelector('.pj-title').value.trim(),
      subtitle: r.querySelector('.pj-subtitle').value.trim(),
      slug: r.querySelector('.pj-slug').value.trim(),
      summary: r.querySelector('.pj-summary').value.trim(),
      content: r.querySelector('.pj-content').value.trim()
    }));
  }
  window.vnLiveProjects = {
    open() { render(); openLiveModal('modalLiveEditProjects'); },
    add() { collect(); data.push({ image: '', title: '', subtitle: '', slug: '', summary: '', content: '' }); render(); },
    remove(i) { collect(); if (!confirm('Xoá dự án "' + (data[i].title || i + 1) + '"?')) return; data.splice(i, 1); render(); },
    move(i, d) { collect(); const j = i + d; if (j < 0 || j >= data.length) return; [data[i], data[j]] = [data[j], data[i]]; render(); },
    upload(input) {
      if (!input.files || !input.files[0]) return;
      const row = input.closest('.vnchip-repeater-item');
      const fd = new FormData();
      fd.append('action', 'upload_image'); fd.append('image', input.files[0]); fd.append('subdir', 'media'); fd.append('prefix', 'project');
      fetch(AJAX, { method: 'POST', body: fd }).then(r => r.json()).then(res => {
        if (!res.success) { alert(res.error || 'Lỗi tải ảnh.'); return; }
        row.querySelector('.pj-img').value = res.path;
        row.querySelector('.pj-img-preview').src = res.full_url;
        toast('✓ Đã tải ảnh lên, bấm Lưu để áp dụng');
      });
      input.value = '';
    }
  };
  function save() {
    collect();
    const btn = document.getElementById('btnSaveProjects');
    btn.disabled = true;
    const fd = new FormData(); fd.append('action', 'save_projects'); fd.append('data', JSON.stringify(data));
    fetch(AJAX, { method: 'POST', body: fd }).then(r => r.json()).then(res => {
      btn.disabled = false;
      if (!res.success) { alert(res.error || 'Lỗi khi lưu.'); return; }
      toast('✓ Đã lưu, đang tải lại...'); setTimeout(() => location.reload(), 600);
    }).catch(err => { btn.disabled = false; alert('Lỗi kết nối máy chủ: ' + err); });
  }
  function reset() {
    if (!confirm('Khôi phục danh sách dự án mặc định? Mọi dự án đã thêm/sửa sẽ bị bỏ.')) return;
    const fd = new FormData(); fd.append('action', 'reset_projects');
    fetch(AJAX, { method: 'POST', body: fd }).then(r => r.json()).then(res => {
      if (!res.success) { alert(res.error || 'Không khôi phục được.'); return; }
      toast('✓ Đã khôi phục, đang tải lại...'); setTimeout(() => location.reload(), 600);
    });
  }
  function bind() {
    document.getElementById('btnSaveProjects').addEventListener('click', save);
    document.getElementById('btnResetProjects').addEventListener('click', reset);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bind); else bind();
})();
</script>
<?php endif; ?>

