<?php
/**
 * Advanced Technology & Chiplet Solutions Page
 * VNCHIP Technology JSC
 */

require_once __DIR__ . '/config/config.php';

$pageTitle = 'Advanced Technology & Chiplet Solutions | ' . SITE_NAME;
$pageDescription = 'Exploring Chiplet Disaggregation, UCIe Interconnect Standards, and AI-Centric SoC Architecture at VNCHIP Technology.';
$activePage = 'business';

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Hero Header Banner -->
<section class="page-hero-banner" style="background: linear-gradient(180deg, rgba(6, 15, 30, 0.85) 0%, rgba(6, 15, 30, 0.98) 100%), url('<?= asset(Setting::getOr('hero_technology', 'images/hero_chip_bg.webp')) ?>') center/cover no-repeat; padding: 180px 0 90px; position: relative;">
  <div class="container">
    <div class="reveal active">
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" style="margin-bottom: 20px;">
        <ol style="display: flex; gap: 8px; font-size: 0.88rem; color: #94A3B8; list-style: none; padding: 0; margin: 0; align-items: center;">
          <li><a href="<?= url() ?>" style="color: #94A3B8; text-decoration: none;">Home</a></li>
          <li>/</li>
          <li><a href="<?= url('business.php') ?>" style="color: #94A3B8; text-decoration: none;">Business</a></li>
          <li>/</li>
          <li style="color: var(--primary-orange); font-weight: 700; text-transform: uppercase;">Technology</li>
        </ol>
      </nav>
      
      <h1 style="font-family: var(--heading-font); font-size: 3.2rem; font-weight: 900; color: #FFFFFF; text-transform: uppercase; letter-spacing: -0.02em; line-height: 1.1; margin-bottom: 16px;">
        OUR TECHNOLOGIES
      </h1>
      <p style="color: #CBD5E1; font-size: 1.15rem; max-width: 750px; line-height: 1.7; margin: 0;">
        Pioneering modular silicon architectures, high-bandwidth interconnects, and AI-native System-on-Chip engineering.
      </p>
    </div>
  </div>
</section>

<!-- 2. Technology Content Sections -->
<div class="tech-content-wrapper" style="background-color: #FFFFFF; color: #1E293B; overflow: hidden;">

  <!-- Section 1: Chiplet Disaggregation -->
  <section class="tech-section" id="chiplet-disaggregation" style="padding: 100px 0; border-bottom: 1px solid #E2E8F0;">
    <div class="container">
      <?php if (!empty($customTechIntro = Setting::get('section_tech_intro'))): ?>
        <div class="reveal active">
          <?= $customTechIntro ?>
        </div>
      <?php else: ?>
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
            <img src="<?= asset('uploads/media/img_1778150461_0.webp') ?>" alt="Semiconductor Packaging and Chiplets" style="width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.6s ease;">
          </div>
        </div>

      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Section 2: Interconnect Standards -->
  <section class="tech-section" id="interconnect-standards" style="padding: 100px 0; background: #F8FAFC; border-bottom: 1px solid #E2E8F0;">
    <div class="container">
      <?php if (!empty($customTechFeatures = Setting::get('section_tech_features'))): ?>
        <div class="reveal active">
          <?= $customTechFeatures ?>
        </div>
      <?php else: ?>
      <div class="row align-items-center" style="display: grid; grid-template-columns: 0.9fr 1.1fr; gap: 60px; align-items: center;">
        
        <div class="tech-media-col reveal active">
          <div style="border-radius: 24px; overflow: hidden; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12); border: 1px solid #E2E8F0; background: #0A192F;">
            <img src="<?= asset('uploads/media/img_1777025379_0.webp') ?>" alt="High Speed Interconnect Technology" style="width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.6s ease;">
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
            
            <div style="background: #FFFFFF; border-left: 4px solid #0284C7; padding: 28px 32px; border-radius: 0 16px 16px 0; box-shadow: 0 6px 20px rgba(0,0,0,0.04); border: 1px solid #E2E8F0; border-left: 4px solid #0284C7;">
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
      <?php endif; ?>
    </div>
  </section>

  <!-- Section 3: AI-Centric SoC Building -->
  <section class="tech-section" id="ai-centric-soc" style="padding: 100px 0;">
    <div class="container">
      <div class="row align-items-center" style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 60px; align-items: center;">
        
        <div class="tech-text-col reveal active">
          <div style="display: inline-flex; align-items: center; gap: 10px; background: rgba(242, 91, 34, 0.1); color: var(--primary-orange); padding: 6px 16px; border-radius: 50px; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; margin-bottom: 20px; letter-spacing: 0.5px;">
            <i class="fas fa-brain"></i> Accelerated Intelligence
          </div>
          <h2 style="font-family: var(--heading-font); font-size: 2.4rem; font-weight: 800; color: #0F172A; text-transform: uppercase; line-height: 1.2; margin-bottom: 24px; letter-spacing: -0.01em;">
            AI-Centric SoC Building
          </h2>
          <div style="font-size: 1.05rem; line-height: 1.85; color: #334155;">
            <p style="margin-bottom: 20px;">
              AI workloads are fundamentally reshaping how SoCs are architected — from compute topology and tensor acceleration to memory bandwidth and data movement. Building an AI-optimized SoC requires a holistic approach that integrates heterogeneous compute engines, memory subsystems, interconnect fabrics, and software-aware compiler co-design.
            </p>
            
            <div style="background: #F8FAFC; border-left: 4px solid var(--primary-orange); padding: 28px 32px; border-radius: 0 16px 16px 0; margin-bottom: 25px; box-shadow: 0 6px 20px rgba(0,0,0,0.03);">
              <h4 style="font-family: var(--heading-font); font-weight: 800; color: var(--primary-orange); margin-bottom: 10px; font-size: 1.1rem; text-transform: uppercase;">
                Strategic Integration
              </h4>
              <p style="margin: 0; font-size: 0.98rem; line-height: 1.7; color: #475569;">
                At VNCHIP Labs, we design AI-centric SoCs that are optimized for both training and inference workloads. Our approach emphasizes efficient dataflow, memory locality, and scalable interconnects to eliminate compute bottlenecks.
              </p>
            </div>

            <p style="margin: 0;">
              We integrate CPUs, GPUs, NPUs, and domain-specific accelerators into cohesive systems, supported by high-bandwidth memory (HBM3/LPDDR5X) and intelligent caching strategies. By aligning hardware architecture with AI model characteristics, we enable customers to achieve superior performance-per-watt and faster time-to-market.
            </p>
          </div>
        </div>

        <div class="tech-media-col reveal active">
          <div style="border-radius: 24px; overflow: hidden; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.15); border: 1px solid #E2E8F0; background: #0A192F;">
            <img src="<?= asset('uploads/media/img_1777025511_0.webp') ?>" alt="AI Neural Network Hardware Acceleration" style="width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.6s ease;">
          </div>
        </div>

      </div>
    </div>
  </section>

</div>

<!-- Responsive styling for 2-col tech layouts -->
<style>
  @media (max-width: 991px) {
    .tech-section .row {
      grid-template-columns: 1fr !important;
      gap: 40px !important;
    }
    .tech-section .row > div:first-child {
      order: 1;
    }
    .tech-section .row > div:last-child {
      order: 2;
    }
  }
</style>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Smooth scroll if URL contains hash (#chiplet-disaggregation, etc.)
    if (window.location.hash) {
      const id = window.location.hash.substring(1);
      const el = document.getElementById(id);
      if (el) {
        setTimeout(() => {
          const headerOffset = 110;
          const elementPosition = el.getBoundingClientRect().top;
          const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
          window.scrollTo({
            top: offsetPosition,
            behavior: "smooth"
          });
        }, 300);
      }
    }
  });
</script>

<!-- 3. Reusable CTA Banner -->
<?php require_once __DIR__ . '/includes/cta_banner.php'; ?>

<!-- 4. Global Footer -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>
