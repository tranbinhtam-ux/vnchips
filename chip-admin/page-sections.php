<?php
/**
 * Admin Page Sections & Site Builder
 * Allows administrators to fully manage and customize content blocks across Technology, About Us, Business, and Sustainability.
 * Features full HTML5 Syntax Highlighting Code Editor (Ace Editor with theme-tomorrow_night/chrome).
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../models/Setting.php';

$message = '';
$error = '';
$db = Database::getInstance()->getConnection();

// Default live content definitions matching frontend templates exactly
$defaultSectionContents = [
    'section_tech_intro' => <<<HTML
<div class="row align-items-center" style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 60px; align-items: center;">
  <div class="tech-text-col reveal active">
    <div style="display: inline-flex; align-items: center; gap: 10px; background: rgba(242, 91, 34, 0.1); color: var(--primary-orange); padding: 6px 16px; border-radius: 50px; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; margin-bottom: 20px; letter-spacing: 0.5px;">
      <i class="fas fa-microchip"></i> Architectural Evolution
    </div>
    <h2 style="font-family: var(--heading-font); font-size: 2.4rem; font-weight: 800; color: #0F172A; text-transform: uppercase; line-height: 1.2; margin-bottom: 24px; letter-spacing: -0.01em;">
      Chiplet Disaggregation
    </h2>
    <div style="font-size: 1.05rem; line-height: 1.85; color: #334155;">
      <p style="margin-bottom: 20px;">
        Modern semiconductor systems are rapidly moving away from monolithic SoCs toward chiplet-based architectures. Chiplet disaggregation enables complex systems to be partitioned into smaller, functionally optimized dies that can be independently designed, manufactured, and integrated.
      </p>
      <p style="margin-bottom: 25px;">
        This approach improves silicon yields, significantly reduces total die cost, and allows mixing of process nodes — combining leading-edge compute chiplets with mature-node I/O, power management, or analog components.
      </p>
      <div style="background: #F8FAFC; border-left: 4px solid var(--primary-orange); padding: 28px 32px; border-radius: 0 16px 16px 0; box-shadow: 0 6px 20px rgba(0,0,0,0.03);">
        <h4 style="font-family: var(--heading-font); font-weight: 800; color: var(--primary-orange); margin-bottom: 10px; font-size: 1.1rem; text-transform: uppercase;">
          VNCHIP Labs Expertise
        </h4>
        <p style="margin: 0; font-size: 0.98rem; line-height: 1.7; color: #475569;">
          At VNCHIP Labs, we specialize in architecting chiplet-based systems that balance performance, power, and scalability. Our expertise spans die partitioning, inter-chiplet communication, memory hierarchy design, and packaging-aware architecture. We help customers transition from traditional SoC designs to modular, reusable chiplet platforms that can scale across product generations.
        </p>
      </div>
    </div>
  </div>
  <div class="tech-media-col reveal active">
    <div style="border-radius: 24px; overflow: hidden; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.15); border: 1px solid #E2E8F0; background: #0A192F;">
      <img src="uploads/media/img_1778150461_0.webp" alt="Semiconductor Packaging and Chiplets" style="width: 100%; height: 100%; object-fit: cover; display: block;">
    </div>
  </div>
</div>
HTML,

    'section_tech_features' => <<<HTML
<div class="row align-items-center" style="display: grid; grid-template-columns: 0.9fr 1.1fr; gap: 60px; align-items: center;">
  <div class="tech-media-col reveal active">
    <div style="border-radius: 24px; overflow: hidden; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12); border: 1px solid #E2E8F0; background: #0A192F;">
      <img src="uploads/media/img_1777025379_0.webp" alt="High Speed Interconnect Technology" style="width: 100%; height: 100%; object-fit: cover; display: block;">
    </div>
  </div>
  <div class="tech-text-col reveal active">
    <div style="display: inline-flex; align-items: center; gap: 10px; background: rgba(0, 180, 216, 0.1); color: #0284C7; padding: 6px 16px; border-radius: 50px; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; margin-bottom: 20px; letter-spacing: 0.5px;">
      <i class="fas fa-network-wired"></i> Standardized Connectivity
    </div>
    <h2 style="font-family: var(--heading-font); font-size: 2.4rem; font-weight: 800; color: #0F172A; text-transform: uppercase; line-height: 1.2; margin-bottom: 24px; letter-spacing: -0.01em;">
      Interconnect Standards
    </h2>
    <div style="font-size: 1.05rem; line-height: 1.85; color: #334155;">
      <p style="margin-bottom: 20px;">
        A robust chiplet ecosystem depends on standardized, high-performance interconnects. Emerging industry standards such as <strong>UCIe (Universal Chiplet Interconnect Express)</strong> and <strong>BoW (Bunch of Wires)</strong> are enabling interoperable, multi-vendor chiplet integration with well-defined electrical, protocol, and software layers.
      </p>
      <p style="margin-bottom: 25px;">
        VNCHIP Labs brings deep expertise in designing and integrating interconnect fabrics across multiple abstraction levels — from PHY and link layers to protocol adaptation (e.g., AXI, CHI). We help customers evaluate trade-offs between latency, bandwidth, power efficiency, and implementation complexity, while ensuring strict compliance with industry specifications.
      </p>
      <div style="background: #FFFFFF; border-left: 4px solid #0284C7; padding: 28px 32px; border-radius: 0 16px 16px 0; box-shadow: 0 6px 20px rgba(0,0,0,0.04); border: 1px solid #E2E8F0;">
        <h4 style="font-family: var(--heading-font); font-weight: 800; color: #0284C7; margin-bottom: 10px; font-size: 1.1rem; text-transform: uppercase;">
          Custom Protocol Optimization
        </h4>
        <p style="margin: 0; font-size: 0.98rem; line-height: 1.7; color: #475569;">
          Our engineering team also supports custom extensions, high-speed SerDes mapping, and low-latency bridging tailored to specific workloads, including ultra-scale AI training clusters and high-performance computing (HPC) nodes.
        </p>
      </div>
    </div>
  </div>
</div>
HTML,

    'section_about_story' => <<<HTML
<div class="overview-lead">
  <p>VNCHIP first started as a training initiative aimed at bridging the skills gap in IC Design industry. Over the past 6 years, we have evolved into a specialized service provider, offering IC design services, consulting, and tailored technology solutions.</p>
  <p>VNCHIP experts' team is committed to pushing the boundaries of technology while staying true to our core values of knowledge-sharing, innovation, and client satisfaction. We are excited to continue growing and making a lasting impact on Vietnam IC Design industry.</p>
</div>
HTML,

    'section_about_vision' => <<<HTML
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
HTML,

    'section_about_leadership' => <<<HTML
<!-- Message from Founder Card -->
<div class="founder-message-box">
  <div class="founder-photo-col">
    <img src="assets/images/founder_tri_standing.webp" alt="Luc Duc Tri - Founder & CEO" class="founder-full-img" />
  </div>
  <div class="founder-quote-col">
    <h3 class="founder-box-title">MESSAGE FROM<br>FOUNDER</h3>
    <div class="founder-box-divider"></div>
    <p class="founder-quote-p">
      Our mission has always been to empower aspiring engineers and professionals with the skills needed to thrive in the fast-paced world of Integrated Circuit (IC) design. Today, we remain committed to delivering innovative, high-impact design solutions while driving growth and excellence in Vietnam’s IC design industry.
    </p>
    <div class="founder-box-divider"></div>
  </div>
</div>

<div class="leadership-subtext-row">
  <p class="leadership-subtext">Our focus is on driving innovation, enhancing performance, and addressing the critical challenges faced by the industry today in Vietnam.</p>
</div>
HTML,

    'section_about_timeline' => <<<HTML
<div class="journey-header">
  <p class="journey-subtitle">FROM ESTABLISHMENT TO THE PRESENT DAY, THROUGH UPS AND DOWNS, VNCHIP HAS ALWAYS BEEN A PIONEER IN THE FIELD OF SEMICONDUCTOR DESIGN.</p>
</div>
<div class="timeline-wrapper">
  <ol class="timeline-track">
    <li class="timeline-item"><div class="timeline-node"></div><div class="timeline-year">2018</div><p class="timeline-desc">Establish a Training Center and offer Free IC Design Training Courses</p></li>
    <li class="timeline-item"><div class="timeline-node"></div><div class="timeline-year">2020</div><p class="timeline-desc">VNCHIP Technology JSC. establishment</p></li>
    <li class="timeline-item"><div class="timeline-node"></div><div class="timeline-year">2021</div><p class="timeline-desc">Taped out ASIC/SOC from 90nm down to 16nm FinFET process</p></li>
    <li class="timeline-item"><div class="timeline-node"></div><div class="timeline-year">2021</div><p class="timeline-desc">Taped Out 7nm FinFET process</p></li>
    <li class="timeline-item"><div class="timeline-node"></div><div class="timeline-year">2023</div><p class="timeline-desc">Starting working</p></li>
    <li class="timeline-item"><div class="timeline-node"></div><div class="timeline-year">2024</div><p class="timeline-desc">Taped out 3nm FinFET process</p></li>
    <li class="timeline-item"><div class="timeline-node"></div><div class="timeline-year">Until now</div><p class="timeline-desc">Signed 2P NDA with Samsung Foundry for N14, N8, N5, N4 access.</p></li>
  </ol>
</div>
HTML,

    'section_about_vision_cards' => <<<HTML
<h2 class="section-title-tech vision-main-title">VISION</h2>
<p class="vision-main-subtitle">VNCHIP delivers world-class solutions and shapes a better future through innovation and collaboration.</p>
<div class="vision-cards-grid">
  <div class="vision-card">
    <h3 class="vision-card-title">VISION</h3>
    <p class="vision-card-text">To become Vietnam’s most trusted Turnkey IC design partner by delivering innovative, end-to-end design solutions that empower our customers' success.</p>
  </div>
  <div class="vision-card">
    <h3 class="vision-card-title">MISSION</h3>
    <p class="vision-card-text">To co-create innovative IC design solutions with strategic partners, while empowering Vietnamese engineering talent and contributing to the advancement of education and technology.</p>
  </div>
  <div class="vision-card">
    <h3 class="vision-card-title">CORE VALUES</h3>
    <p class="vision-card-text">Committed to cultivating talented engineers, driving continuous improvement, and advancing a sustainable IC Design industry</p>
  </div>
</div>
HTML,

    'section_about_conduct' => <<<HTML
<div class="conduct-header-row">
  <div>
    <h2 class="conduct-main-title">CODE OF CONDUCT</h2>
    <p class="conduct-main-subtitle">We uphold integrity, innovation, and excellence, delivering design services with professionalism, respect, and a commitment to trusted partnerships.</p>
  </div>
</div>
<div class="conduct-cards-grid">
  <div class="conduct-card">
    <h3 class="conduct-card-title">INTEGRITY AND<br>ETHICAL STANDARDS</h3>
    <p class="conduct-card-text">We commit to maintaining the highest ethical standards in all our R&D activities. All members are expected to uphold honesty, transparency, and accountability in their work.</p>
  </div>
  <div class="conduct-card">
    <h3 class="conduct-card-title">INNOVATION AND<br>EXCELLENCE</h3>
    <p class="conduct-card-text">We strive for continuous innovation and excellence in semiconductor technology by encouraging creativity and pushing the boundaries of what’s possible.</p>
  </div>
  <div class="conduct-card">
    <h3 class="conduct-card-title">CONFIDENTIALITY AND<br>DATA PROTECTION</h3>
    <p class="conduct-card-text">We are dedicated to safeguarding proprietary information, sensitive data, and intellectual property. All staff, students, and partners are required to comply with strict confidentiality agreements to protect the interests of VNCHIP Institute and our Strategic Partners.</p>
  </div>
  <div class="conduct-card">
    <h3 class="conduct-card-title">SUSTAINABILITY AND<br>RESPONSIBILITY</h3>
    <p class="conduct-card-text">We are committed to conducting Institute R&D activities in an environmentally sustainable and socially responsible manner, ensuring a positive impact on society.</p>
  </div>
</div>
HTML,

    'section_business_services' => <<<HTML
<div class="biz-tech-box">
  <div class="biz-tech-items">
    <a href="technology.php#chiplet-disaggregation" class="biz-tech-item" title="Chiplet Disaggregation">
      <div class="biz-tech-icon"><i class="fas fa-microchip"></i></div>
      <div class="biz-tech-text">CHIPLET<br>DISAGGREGATION</div>
    </a>
    <a href="technology.php#interconnect-standards" class="biz-tech-item" title="Interconnect Standards">
      <div class="biz-tech-icon"><i class="fas fa-network-wired"></i></div>
      <div class="biz-tech-text">INTERCONNECT<br>STANDARDS</div>
    </a>
    <a href="technology.php#ai-centric-soc" class="biz-tech-item" title="AI-Centric SoC Building">
      <div class="biz-tech-icon"><i class="fas fa-brain"></i></div>
      <div class="biz-tech-text">AI-CENTRIC SOC<br>BUILDING</div>
    </a>
  </div>
</div>
HTML,

    'section_business_engagement' => <<<HTML
<div class="p-4 rounded-3" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
  <h4 style="font-family: var(--heading-font); font-weight: 800; color: #0F172A; text-transform: uppercase; margin-bottom: 12px;">
    Turnkey Silicon Execution & Engagement Models
  </h4>
  <p style="color: #475569; line-height: 1.7; margin-bottom: 15px;">
    VNCHIP Labs supports both turnkey silicon development and targeted engineering engagement models, delivering disciplined execution from specification through final GDSII tapeout signoff.
  </p>
  <ul style="color: #334155; line-height: 1.8; margin-bottom: 0;">
    <li><strong>Turnkey RTL-to-GDSII:</strong> Comprehensive physical implementation, STA/DFT signoff, and foundry coordination.</li>
    <li><strong>Dedicated ODC Model:</strong> High-bandwidth dedicated engineering pods embedded within customer teams.</li>
    <li><strong>Design Verification & Emulation:</strong> UVM testbenches, formal verification, and hardware prototyping.</li>
  </ul>
</div>
HTML,

    'section_sustainable_esg' => <<<HTML
<div class="sus-social-text">
  <p>We are committed to making a positive impact beyond the IC design industry.</p>
  <p>We prioritize sustainable practices, foster community engagement, and invest in initiatives that support education and environmental conservation.</p>
  <p>Our mission extends to empowering local talent, creating job opportunities, and contributing to the growth of the semiconductor ecosystem.</p>
  <p>Through meaningful partnerships and programs, VNCHIP is dedicated to driving social progress and fostering a better future for the communities we serve.</p>
</div>
HTML,

    'section_sustainable_talent' => <<<HTML
<div class="cleanroom-header-row mb-4">
  <h2 class="cleanroom-heading">CLEAN ROOM & ADVANCED LAB</h2>
  <p class="cleanroom-subheading">Clean Room provides the security solutions tailored to meet the specific needs of our valued clients, ensuring a secure and controlled environment for handling sensitive semiconductor intellectual property and taped-out test chips.</p>
</div>
HTML,

    'section_footer_nav' => <<<HTML
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
HTML
];

// Pre-seed default contents into database if not already stored
foreach ($defaultSectionContents as $k => $defVal) {
    if (empty(Setting::get($k, ''))) {
        Setting::set($k, $defVal, 'content_sections');
    }
}

$pagesConfig = [
    'technology' => [
        'title' => 'Technology & R&D (technology.php)',
        'url' => 'technology.php',
        'sections' => [
            'section_tech_intro' => [
                'title' => 'Core Competencies & IC Design Overview',
                'desc' => 'Khối mở đầu giới thiệu năng lực Chiplet Disaggregation, thiết kế vi mạch tiên tiến.',
            ],
            'section_tech_features' => [
                'title' => 'Advanced Verification & Lab Infrastructure',
                'desc' => 'Khối Chuẩn giao tiếp liên kết vi mạch (Interconnect Standards: UCIe, BoW).',
            ]
        ]
    ],
    'about_us' => [
        'title' => 'About Us (about-us.php)',
        'url' => 'about-us.php',
        'sections' => [
            'section_about_story' => [
                'title' => 'Our Semiconductor Journey & Milestones',
                'desc' => 'Khối Hành trình phát triển VNCHIP Journey, câu chuyện sáng lập và định hướng.',
            ],
            'section_about_vision' => [
                'title' => 'Mission, Vision & Core Values',
                'desc' => 'Khối Tầm nhìn, Sứ mệnh và Năng lực nghiên cứu phát triển.',
            ],
            'section_about_leadership' => [
                'title' => 'Leadership & Message from Founder',
                'desc' => 'Khối Thông điệp Người sáng lập (Founder), ảnh chân dung và đoạn giới thiệu Ban Lãnh Đạo.',
            ],
            'section_about_timeline' => [
                'title' => 'Journey Timeline (Lịch sử & Mốc phát triển)',
                'desc' => 'Mã HTML tùy biến toàn bộ Timeline lịch sử phát triển trên about-us.php#journey (để trống sẽ dùng danh sách mốc từ Live Edit).',
            ],
            'section_about_vision_cards' => [
                'title' => 'Vision, Mission & Core Values Cards',
                'desc' => 'Mã HTML tùy biến khối 3 thẻ Vision / Mission / Core Values trên about-us.php#vision (để trống sẽ dùng dữ liệu thẻ từ Live Edit).',
            ],
            'section_about_conduct' => [
                'title' => 'Code of Conduct (Quy tắc Ứng xử)',
                'desc' => 'Mã HTML tùy biến khối Code of Conduct trên about-us.php#conduct (để trống sẽ dùng danh sách thẻ từ Live Edit).',
            ]
        ]
    ],
    'business' => [
        'title' => 'Business Solutions (business.php)',
        'url' => 'business.php',
        'sections' => [
            'section_business_services' => [
                'title' => 'Technology Capsule (3 Trụ cột Công nghệ)',
                'desc' => 'Khối 3 nút liên kết công nghệ: Chiplet Disaggregation, Interconnect Standards, AI-Centric SoC.',
            ],
            'section_business_engagement' => [
                'title' => 'Engagement Models & IP Portfolio',
                'desc' => 'Khối Mô hình hợp tác kinh doanh & Turnkey Silicon Execution.',
            ]
        ]
    ],
    'sustainable' => [
        'title' => 'Sustainability & ESG (sustainable.php)',
        'url' => 'sustainable.php',
        'sections' => [
            'section_sustainable_esg' => [
                'title' => 'Environmental Stewardship & Clean Operations',
                'desc' => 'Khối Phát triển Bền vững & Trách nhiệm Xã hội (ESG).',
            ],
            'section_sustainable_talent' => [
                'title' => 'Semiconductor Talent & Clean Room Lab',
                'desc' => 'Khối Phòng sạch Clean Room & Cơ sở hạ tầng nghiên cứu đạt chuẩn bảo mật.',
            ]
        ]
    ],
    'footer' => [
        'title' => 'Global Footer Navigation & Links (includes/footer.php)',
        'url' => 'index.php#siteFooter',
        'sections' => [
            'section_footer_nav' => [
                'title' => '3 Cột Menu Điều Hướng Footer (Sustainable, Design Solutions, Quick Links)',
                'desc' => 'Tùy chỉnh toàn bộ tiêu đề cột, cấu trúc phân nhóm (Design Service, IP Service) và tất cả các đường link liên kết của 3 cột Footer.',
            ]
        ]
    ],
];

$activeTab = $_GET['tab'] ?? 'technology';
if (!isset($pagesConfig[$activeTab])) {
    $activeTab = 'technology';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = [];
    foreach ($pagesConfig[$activeTab]['sections'] as $secKey => $secConf) {
        if (isset($_POST[$secKey])) {
            $updates[$secKey] = $_POST[$secKey];
        }
    }

    if (!empty($updates) && Setting::updateBatch($updates, 'content_sections')) {
        $message = 'Page section contents updated successfully!';
    } else {
        $error = 'Failed to save section contents.';
    }
}

$adminTitle = 'Page Sections & Site Builder';
$adminActivePage = 'page-sections';

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* Ace Editor Container Styling */
.ace-html-editor {
  border: 1px solid #1E293B !important;
  border-top: none !important;
  font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', 'Courier New', monospace !important;
  font-size: 14px !important;
  line-height: 1.55 !important;
  transition: height 0.25s ease;
}

.ace-editor-toolbar {
  background: #0F172A;
  color: #FFFFFF;
  padding: 8px 16px;
  border-radius: 10px 10px 0 0;
  border: 1px solid #1E293B;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 10px;
}

.ace-editor-toolbar .btn-editor-tool {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #E2E8F0;
  font-size: 0.74rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.ace-editor-toolbar .btn-editor-tool:hover {
  background: var(--admin-primary, #0284C7);
  color: #FFFFFF;
  border-color: var(--admin-primary, #0284C7);
}
</style>

<div class="admin-panel mb-4">
  <div class="panel-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <h3 class="panel-title mb-1">
        <i class="fa-solid fa-shapes text-primary me-2"></i>Page Sections & Site Content Builder
      </h3>
      <p class="text-muted small mb-0">Chỉnh sửa khối nội dung trực tiếp bằng Trình soạn thảo Code HTML có màu sắc cú pháp trực quan</p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= url($pagesConfig[$activeTab]['url']) ?>" target="_blank" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Xem trang ngoài: <?= e($pagesConfig[$activeTab]['title']) ?>
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

  <!-- Page Navigation Tabs -->
  <ul class="nav nav-pills mb-4 gap-2">
    <?php foreach ($pagesConfig as $key => $p): ?>
      <li class="nav-item">
        <a class="nav-link <?= ($activeTab === $key) ? 'active bg-primary' : 'bg-light text-dark' ?> fw-semibold" 
           href="<?= admin_url('page-sections.php?tab=' . $key) ?>">
          <?= e($p['title']) ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>

  <!-- Form for Current Page Sections -->
  <form action="<?= admin_url('page-sections.php?tab=' . $activeTab) ?>" method="POST" id="sections-form">
    <?php foreach ($pagesConfig[$activeTab]['sections'] as $secKey => $secConf): ?>
      <?php 
        $currentContent = Setting::get($secKey, $defaultSectionContents[$secKey] ?? '');
      ?>
      <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 14px;">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
          <div>
            <h5 class="fw-bold text-dark mb-0"><?= e($secConf['title']) ?></h5>
            <div class="small text-muted"><?= e($secConf['desc']) ?></div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary btn-reset-section" 
                    data-target="<?= e($secKey) ?>" 
                    data-default="<?= htmlspecialchars($defaultSectionContents[$secKey] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    title="Khôi phục lại mã mẫu gốc mặc định">
              <i class="fa-solid fa-rotate-left me-1"></i> Khôi phục mẫu gốc
            </button>
            <span class="badge bg-secondary-subtle text-muted"><code>Setting::get('<?= e($secKey) ?>')</code></span>
          </div>
        </div>

        <!-- Ace Editor Control Bar -->
        <div class="ace-editor-toolbar mt-3">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger text-white"><i class="fa-brands fa-html5 me-1"></i> HTML5 Markup</span>
            <span class="text-info small font-monospace"><i class="fa-solid fa-palette text-warning me-1"></i> Syntax Highlighting Active</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn-editor-tool btn-toggle-theme" data-editor-id="ace_<?= e($secKey) ?>" title="Chuyển đổi giao diện Sáng / Tối">
              <i class="fa-solid fa-circle-half-stroke"></i> Giao diện (Dark/Light)
            </button>
            <button type="button" class="btn-editor-tool btn-expand-editor" data-editor-id="ace_<?= e($secKey) ?>" title="Phóng to / Thu nhỏ khung soạn thảo">
              <i class="fa-solid fa-up-right-and-down-left-from-center"></i> Mở rộng khung
            </button>
          </div>
        </div>

        <!-- Hidden Textarea for form submission -->
        <textarea name="<?= e($secKey) ?>" id="textarea_<?= e($secKey) ?>" style="display: none;"><?= htmlspecialchars($currentContent, ENT_QUOTES, 'UTF-8') ?></textarea>

        <!-- The Ace Code Editor Container with Syntax Colors -->
        <div id="ace_<?= e($secKey) ?>" class="ace-html-editor mb-3 rounded-bottom" style="height: 380px; width: 100%;"></div>

        <div class="small text-muted d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div>
            <i class="fa-solid fa-circle-info text-primary me-1"></i>
            <span>Mã HTML được tô màu cú pháp trực quan (thẻ tag đỏ/hồng, thuộc tính vàng cam, chuỗi xanh lá, ghi chú xám). Hỗ trợ <kbd>Ctrl + F</kbd> tìm kiếm, <kbd>Ctrl + Z</kbd> hoàn tác.</span>
          </div>
          <div class="font-monospace small text-secondary">
            Phím tắt: <kbd>Ctrl + S</kbd> để lưu nhanh
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- Sticky Save Bar -->
    <div class="card border-0 shadow-sm p-3 d-flex flex-row justify-content-between align-items-center" style="border-radius: 12px; background: #f8fafc;">
      <div class="small text-muted">
        <i class="fa-solid fa-check-double text-success me-1"></i>
        Thay đổi có hiệu lực ngay lập tức ngoài website sau khi bấm Lưu.
      </div>
      <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
        <i class="fa-solid fa-floppy-disk me-1"></i> Lưu thay đổi nội dung (Save Changes)
      </button>
    </div>
  </form>

</div>

<script>
const editors = {};

// Initialize Ace Editors for all section blocks
document.querySelectorAll('.ace-html-editor').forEach(el => {
  const editorId = el.id;
  const secKey = editorId.replace('ace_', '');
  const textarea = document.getElementById('textarea_' + secKey);
  
  if (textarea && typeof ace !== 'undefined') {
    const editor = ace.edit(editorId);
    editor.setTheme("ace/theme/tomorrow_night");
    editor.session.setMode("ace/mode/html");
    editor.setValue(textarea.value, -1);
    editor.setOptions({
      fontSize: "14px",
      fontFamily: "'JetBrains Mono', 'Fira Code', 'Consolas', 'Courier New', monospace",
      wrap: true,
      showPrintMargin: false,
      tabSize: 2,
      useSoftTabs: true,
      highlightActiveLine: true,
      showLineNumbers: true,
      showGutter: true,
      enableBasicAutocompletion: true,
      enableLiveAutocompletion: true
    });
    
    // Sync continuously to hidden textarea
    editor.session.on('change', function() {
      textarea.value = editor.getValue();
    });
    
    // Shortcut Ctrl+S inside editor to submit form
    editor.commands.addCommand({
      name: 'saveForm',
      bindKey: { win: 'Ctrl-S', mac: 'Command-S' },
      exec: function() {
        document.getElementById('sections-form').submit();
      }
    });

    editors[secKey] = editor;
  }
});

// Ensure all editors sync values back to textareas before form submit
document.getElementById('sections-form').addEventListener('submit', function() {
  for (const key in editors) {
    const ta = document.getElementById('textarea_' + key);
    if (ta && editors[key]) {
      ta.value = editors[key].getValue();
    }
  }
});

// Button to reset section to default HTML template
document.querySelectorAll('.btn-reset-section').forEach(btn => {
  btn.addEventListener('click', function() {
    if (confirm('Bạn có chắc chắn muốn khôi phục khối này về nội dung mẫu mặc định ban đầu không?')) {
      const secKey = this.getAttribute('data-target');
      const defaultVal = this.getAttribute('data-default');
      const textarea = document.getElementById('textarea_' + secKey);
      if (textarea) {
        textarea.value = defaultVal;
      }
      if (editors[secKey]) {
        editors[secKey].setValue(defaultVal, -1);
      }
    }
  });
});

// Button to toggle theme (Dark / Light)
document.querySelectorAll('.btn-toggle-theme').forEach(btn => {
  btn.addEventListener('click', function() {
    const edId = this.getAttribute('data-editor-id');
    const secKey = edId.replace('ace_', '');
    const ed = editors[secKey];
    if (ed) {
      const currentTheme = ed.getTheme();
      if (currentTheme.includes('tomorrow_night')) {
        ed.setTheme('ace/theme/chrome');
      } else {
        ed.setTheme('ace/theme/tomorrow_night');
      }
    }
  });
});

// Button to expand / collapse editor height
document.querySelectorAll('.btn-expand-editor').forEach(btn => {
  btn.addEventListener('click', function() {
    const edId = this.getAttribute('data-editor-id');
    const el = document.getElementById(edId);
    if (el) {
      if (el.style.height === '380px') {
        el.style.height = '680px';
        this.innerHTML = '<i class="fa-solid fa-down-left-and-up-right-to-center me-1"></i> Thu gọn';
      } else {
        el.style.height = '380px';
        this.innerHTML = '<i class="fa-solid fa-up-right-and-down-left-from-center me-1"></i> Mở rộng khung';
      }
      const secKey = edId.replace('ace_', '');
      if (editors[secKey]) editors[secKey].resize();
    }
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
