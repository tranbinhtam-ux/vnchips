<?php
/**
 * VNCHIP Technology JSC - Homepage (Index)
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/News.php';
require_once __DIR__ . '/models/Setting.php';

// Fetch dynamic featured news articles for Media Center section
$featuredNews = News::getFeatured(4);

// Page Metadata
$pageTitle = Setting::get('site_name', 'VNCHIP Technology JSC') . ' | Turnkey IC Design & Semiconductor Innovation';
$pageDescription = 'VNCHIP Technology JSC delivers world-class turnkey IC design services, custom layout, physical design from 65nm to 3nm FinFET nodes, and high-impact talent development in Vietnam.';
$activePage = 'home';

// Dynamic Stats from Admin Settings
$stat1_num = Setting::get('stat_years_number', '6+');
$stat1_lbl = Setting::get('stat_years_label', 'years');
$stat1_desc = Setting::get('stat_years_desc', 'Deep expertise in physical design and implementation, custom circuit layout, and system design - from 65nm to 3nm nodes');

$stat2_num = Setting::get('stat_asic_number', '40+');
$stat2_lbl = Setting::get('stat_asic_label', 'ASIC');
$stat2_desc = Setting::get('stat_asic_desc', 'Successful silicon solutions delivered across AI compute, mobile SOC, and 5G signal processing');

$stat3_num = Setting::get('stat_chips_number', '5M+');
$stat3_lbl = Setting::get('stat_chips_label', 'chips');
$stat3_desc = Setting::get('stat_chips_desc', 'Delivered annually to global partners, testing and post-silicon supports');

$pillBanner = Setting::get('homepage_pill_banner', 'We drive progress through training, research collaboration, and technology transfer, aligning with international standards and evolving industry trends in the global semiconductor landscape.');

// Include Global Header
require_once __DIR__ . '/includes/header.php';
?>

  <!-- 1. HERO SECTION (High-Tech Semiconductor Background) -->
  <section class="hero-section">
    <video class="hero-video" id="heroBgVideo" autoplay loop muted playsinline poster="<?= asset('images/hero_chip_bg.webp') ?>">
      <source src="<?= asset(Setting::getOr('home_hero_video', 'videos/vecteezy_a-computer-chip-with-glowing-blue-lines_76491895.mp4')) ?>" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
    <div class="container hero-content">
      
      <!-- Key Statistics Bar (Dynamic from Database) -->
      <div class="stats-bar" id="live-stats-bar">
        <div class="stat-item">
          <div class="stat-number"><?= e($stat1_num) ?> <span><?= e($stat1_lbl) ?></span></div>
          <p class="stat-desc"><?= e($stat1_desc) ?></p>
        </div>

        <div class="stat-item">
          <div class="stat-number"><?= e($stat2_num) ?> <span><?= e($stat2_lbl) ?></span></div>
          <p class="stat-desc"><?= e($stat2_desc) ?></p>
        </div>

        <div class="stat-item">
          <div class="stat-number"><?= e($stat3_num) ?> <span><?= e($stat3_lbl) ?></span></div>
          <p class="stat-desc"><?= e($stat3_desc) ?></p>
        </div>
      </div>

    </div>
  </section>

  <!-- 2. BUSINESS SECTION (Primary Dark Background) -->
  <section class="business-section" id="business">
    <div class="business-title-backdrop"></div>
    <div class="container">
      <h2 class="section-title-tech home-section-title">BUSINESS</h2>
      
      <div class="business-grid">
        <!-- Card 1: Design Solutions -->
        <a href="<?= url('business.php#design-service') ?>" class="business-card">
          <img src="<?= asset('images/business_design_solutions.webp') ?>" alt="Semiconductor Design Solutions" class="business-card-img">
          <div class="business-card-overlay">
            <h3 class="business-card-title">DESIGN SOLUTIONS</h3>
            <p class="business-card-desc">
              Comprehensive turnkey IC design services spanning specification, architectural modeling, RTL, synthesis, and advanced physical implementation.
            </p>
          </div>
        </a>

        <!-- Card 2: VNCHIP R&D -->
        <a href="<?= url('business.php#rd-collaborations') ?>" class="business-card">
          <img src="<?= asset('images/business_rd_cube.webp') ?>" alt="VNCHIP R&D Innovation" class="business-card-img">
          <div class="business-card-overlay">
            <h3 class="business-card-title">VNCHIP R&D</h3>
            <p class="business-card-desc">
              Pioneering next-generation IP blocks, chiplet disaggregation, interconnect standards, and AI-centric System-on-Chip architectures.
            </p>
          </div>
        </a>
      </div>

    </div>
  </section>

  <!-- 3. SERVICES SECTION: ASIC SERVICE (Ported & Enhanced from vnchips_old id="home_asic_service") -->
  <section class="services-section design-service-section" id="home_asic_service" data-section="services">
    <div class="container">
      
      <!-- Curved Blue Highlight Pill Banner (Dynamic) -->
      <div class="pill-banner-container" id="live-pill-banner">
        <div class="pill-banner">
          <p><?= e($pillBanner) ?></p>
        </div>
      </div>

      <div class="services-grid">
        
        <!-- Column 1: DESIGN SERVICE -->
        <div class="service-col">
          <h2 class="service-col-title">DESIGN SERVICE</h2>
          <div class="service-accordion" id="accDesign">
            
            <!-- Item 1: Specification to GDSII -->
            <div class="service-item">
              <div class="service-header">
                <span class="service-name">Specification to GDSII</span>
                <span class="service-icon">+</span>
              </div>
              <div class="service-body">
                <img src="<?= asset('uploads/media/img_1778150011_0.webp') ?>" alt="Specification to GDSII" class="service-body-img img-fluid rounded mb-3" loading="lazy">
                <p class="spec-paragraph">
                  As semiconductor companies increasingly develop highly customized silicon solutions, the demand for advanced backend implementation and full-chip integration expertise continues to grow. VNCHIP Labs provides end-to-end Spec-to-GDSII design services that transform architecture concepts into production-ready silicon.
                </p>
                <p class="spec-paragraph">
                  Our services include SoC architecture support, <b class="tuong-tac" tabindex="0">IP integration<span class="chu-thich"><b>IP integration</b><br><br>VNCHIP Labs provides comprehensive IP integration services for complex SoC and chiplet designs, enabling seamless integration of customer-developed IPs, VNCHIP Labs IPs, and third-party commercial or open-source IPs into a unified system architecture. Our engineering teams design and optimize interconnect fabrics, Network-on-Chip (NoC) architectures, bridges, and subsystem connectivity to meet performance, power, scalability, and integration requirements.<br><br>We support both industry-standard and custom protocols, including AXI, AHB, APB, CHI, PCIe, CXL, UCIe, Ethernet, and proprietary interfaces. Our services include protocol adaptation, clock and reset integration, address-map definition, coherency integration, bandwidth optimization, verification, timing closure support, and system-level validation to ensure reliable operation across heterogeneous IP environments.</span></b>, <b class="tuong-tac" tabindex="0">RTL development<span class="chu-thich"><b>RTL Development/Refinement</b><br><br>VNCHIP Labs provides RTL development and refinement services for SoCs, ASICs, chiplets, and subsystem designs across AI, networking, data center, automotive, and edge applications. Our engineering teams can develop original RTL implementations based on customer-provided architecture and microarchitecture specifications, delivering scalable, synthesizable, and verification-ready designs optimized for performance, power, and area goals.<br><br>In addition to new RTL development, VNCHIP Labs offers RTL refinement services to enhance existing or incomplete RTL implementations. This includes improving functionality, performance optimization, protocol compliance, code restructuring, timing-aware modifications, low-power enhancements, debug support, and integration readiness. Our teams work closely with customers to accelerate development schedules while improving design quality, maintainability, and first-silicon success.</span></b>, <b class="tuong-tac" tabindex="0">verification<span class="chu-thich"><b>Design verification</b><br><br>VNCHIP Labs provides comprehensive design verification services using advanced UVM-based verification methodologies to validate RTL implementations against architecture and microarchitecture specifications. Our teams develop scalable verification environments, testbenches, protocol monitors, scoreboards, assertions, and coverage-driven regression infrastructures that thoroughly stimulate and validate functional correctness across complex SoC and IP designs.<br><br>To accelerate verification closure, VNCHIP Labs leverages AI-assisted automation and intelligent verification agents to improve testcase generation, regression analysis, bug triage, and coverage optimization. By combining traditional verification expertise with AI-driven workflows, we help customers achieve verification milestones faster while improving functional, code, and assertion coverage metrics, reducing overall verification cycle time and increasing first-silicon confidence.</span></b>, <b class="tuong-tac" tabindex="0">prototyping<span class="chu-thich"><b>Prototyping</b><br><br>VNCHIP Labs provides FPGA-based prototyping services to help customers validate IPs, subsystems, and critical portions of complex SoC designs before silicon availability. Our teams can implement customer IPs and system architectures on FPGA platforms to enable high-speed functional validation, software development, performance analysis, and early ecosystem integration.<br><br>We develop drivers, firmware, and validation software to exercise the design at realistic operating speeds and to interface with actual partner or third-party devices and platforms. In addition, VNCHIP Labs supports software bring-up, application development, and customer driver validation using the prototyping environment, allowing hardware and software teams to identify issues early, accelerate system integration, and reduce overall silicon development risk.</span></b>, <b class="tuong-tac" tabindex="0">power and performance analysis<span class="chu-thich"><b>Power and performance analysis</b><br><br>VNCHIP Labs provides power and performance analysis services to help customers evaluate their designs against key application workloads and system use cases. Our teams analyze architecture behavior, data movement, memory bandwidth, latency, interconnect efficiency, and compute utilization to identify performance bottlenecks and estimate dynamic and static power consumption under realistic operating conditions.<br><br>Using simulation, profiling, emulation, and workload-driven analysis techniques, we help customers understand tradeoffs between performance, power, area, and thermal constraints early in the development cycle. Based on the analysis results, VNCHIP Labs can recommend architectural, RTL, clocking, memory, and interconnect optimizations to improve system throughput, reduce latency, and lower overall power consumption while maintaining design goals and implementation feasibility.</span></b>, physical implementation, <b class="tuong-tac" tabindex="0">STA/DFT signoff<span class="chu-thich"><b>STA/DFT signoff</b><br><br>VNCHIP Labs provides comprehensive STA and DFT signoff services to ensure designs meet timing, testability, reliability, and manufacturability requirements before tapeout. Our physical implementation teams perform detailed analysis and verification throughout the backend flow, including timing constraint validation, multi-mode multi-corner (MMMC) static timing analysis, clock-domain crossing checks, signal integrity analysis, IR drop and electromigration evaluation, power integrity verification, noise analysis, thermal assessment, and physical design rule compliance.<br><br>For DFT signoff, we support scan insertion, ATPG generation, scan compression, memory BIST integration, JTAG/debug infrastructure, test coverage analysis, and manufacturing test readiness validation. During implementation and signoff, VNCHIP Labs conducts extensive backend verification iterations to identify and resolve timing violations, congestion issues, routing challenges, DRC/LVS violations, testability gaps, and ECO-related issues. Our disciplined signoff methodology helps customers achieve reliable first-silicon success and production-ready GDSII handoff for fabrication.</span></b>, and <b class="tuong-tac" tabindex="0">final GDSII handoff<span class="chu-thich"><b>GDSII handoff</b><br><br>VNCHIP Labs provides comprehensive GDSII handoff services to ensure designs are fully verified, properly configured, and ready for successful fabrication. Prior to tapeout, our teams perform final implementation reviews and signoff checks covering all physical design layers, process technology rules, timing constraints, power integrity, manufacturability requirements, and foundry-specific configurations.<br><br>We work closely with customers through structured tapeout readiness reviews to validate DRC/LVS closure, STA signoff, antenna checks, density verification, IR/EM analysis, metal fill insertion, packaging interface requirements, and final netlist-to-layout consistency. Our methodology ensures that all design databases, masks, configuration files, and deliverables are aligned with foundry requirements before final GDSII release. This disciplined review and handoff process helps reduce fabrication risk and improves confidence in first-silicon success.</span></b>. With experience across advanced-node FinFET technologies and complex SoC designs, we help customers optimize performance, power, area, and development schedule for AI, networking, data center, automotive, and edge applications.
                </p>
                <p class="spec-paragraph">
                  VNCHIP Labs supports both turnkey silicon development and targeted engineering engagement models, delivering disciplined execution from specification through tapeout.
                </p>
              </div>
            </div>

            <!-- Item 2: RTL to GDSII -->
            <div class="service-item">
              <div class="service-header">
                <span class="service-name">RTL to GDSII</span>
                <span class="service-icon">+</span>
              </div>
              <div class="service-body">
                <img src="<?= asset('uploads/media/media_1776072068.webp') ?>" alt="RTL to GDSII" class="service-body-img img-fluid rounded mb-3" loading="lazy">
                <p class="spec-paragraph">
                  VNCHIP Labs provides comprehensive RTL-to-GDSII implementation services for complex SoCs, ASICs, and chiplet-based designs. Starting from customer RTL, our teams support IP integration, <b class="tuong-tac" tabindex="0">RTL development refinement<span class="chu-thich"><b>RTL Development/Refinement</b><br><br>VNCHIP Labs provides RTL development and refinement services for SoCs, ASICs, chiplets, and subsystem designs across AI, networking, data center, automotive, and edge applications. Our engineering teams can develop original RTL implementations based on customer-provided architecture and microarchitecture specifications, delivering scalable, synthesizable, and verification-ready designs optimized for performance, power, and area goals.<br><br>In addition to new RTL development, VNCHIP Labs offers RTL refinement services to enhance existing or incomplete RTL implementations. This includes improving functionality, performance optimization, protocol compliance, code restructuring, timing-aware modifications, low-power enhancements, debug support, and integration readiness. Our teams work closely with customers to accelerate development schedules while improving design quality, maintainability, and first-silicon success.</span></b>, <b class="tuong-tac" tabindex="0">verification<span class="chu-thich"><b>Design verification</b><br><br>VNCHIP Labs provides comprehensive design verification services using advanced UVM-based verification methodologies to validate RTL implementations against architecture and microarchitecture specifications. Our teams develop scalable verification environments, testbenches, protocol monitors, scoreboards, assertions, and coverage-driven regression infrastructures that thoroughly stimulate and validate functional correctness across complex SoC and IP designs.<br><br>To accelerate verification closure, VNCHIP Labs leverages AI-assisted automation and intelligent verification agents to improve testcase generation, regression analysis, bug triage, and coverage optimization. By combining traditional verification expertise with AI-driven workflows, we help customers achieve verification milestones faster while improving functional, code, and assertion coverage metrics, reducing overall verification cycle time and increasing first-silicon confidence.</span></b>, <b class="tuong-tac" tabindex="0">prototyping<span class="chu-thich"><b>Prototyping</b><br><br>VNCHIP Labs provides FPGA-based prototyping services to help customers validate IPs, subsystems, and critical portions of complex SoC designs before silicon availability. Our teams can implement customer IPs and system architectures on FPGA platforms to enable high-speed functional validation, software development, performance analysis, and early ecosystem integration.<br><br>We develop drivers, firmware, and validation software to exercise the design at realistic operating speeds and to interface with actual partner or third-party devices and platforms. In addition, VNCHIP Labs supports software bring-up, application development, and customer driver validation using the prototyping environment, allowing hardware and software teams to identify issues early, accelerate system integration, and reduce overall silicon development risk.</span></b>, <b class="tuong-tac" tabindex="0">power and performance analysis<span class="chu-thich"><b>Power and performance analysis</b><br><br>VNCHIP Labs provides power and performance analysis services to help customers evaluate their designs against key application workloads and system use cases. Our teams analyze architecture behavior, data movement, memory bandwidth, latency, interconnect efficiency, and compute utilization to identify performance bottlenecks and estimate dynamic and static power consumption under realistic operating conditions.<br><br>Using simulation, profiling, emulation, and workload-driven analysis techniques, we help customers understand tradeoffs between performance, power, area, and thermal constraints early in the development cycle. Based on the analysis results, VNCHIP Labs can recommend architectural, RTL, clocking, memory, and interconnect optimizations to improve system throughput, reduce latency, and lower overall power consumption while maintaining design goals and implementation feasibility.</span></b>, and complete backend physical implementation through final tapeout.
                </p>
                <p class="spec-paragraph">
                  Our implementation flow includes synthesis, scan insertion, floorplanning, placement and routing, clock-tree synthesis, timing closure, signal integrity analysis, and <b class="tuong-tac" tabindex="0">STA/DFT signoff<span class="chu-thich"><b>STA/DFT signoff</b><br><br>VNCHIP Labs provides comprehensive STA and DFT signoff services to ensure designs meet timing, testability, reliability, and manufacturability requirements before tapeout. Our physical implementation teams perform detailed analysis and verification throughout the backend flow, including timing constraint validation, multi-mode multi-corner (MMMC) static timing analysis, clock-domain crossing checks, signal integrity analysis, IR drop and electromigration evaluation, power integrity verification, noise analysis, thermal assessment, and physical design rule compliance.<br><br>For DFT signoff, we support scan insertion, ATPG generation, scan compression, memory BIST integration, JTAG/debug infrastructure, test coverage analysis, and manufacturing test readiness validation. During implementation and signoff, VNCHIP Labs conducts extensive backend verification iterations to identify and resolve timing violations, congestion issues, routing challenges, DRC/LVS violations, testability gaps, and ECO-related issues. Our disciplined signoff methodology helps customers achieve reliable first-silicon success and production-ready GDSII handoff for fabrication.</span></b>. Throughout the design cycle, we perform extensive electrical and physical verification to identify and resolve timing, noise, congestion, thermal, and manufacturability challenges before delivering the final <b class="tuong-tac" tabindex="0">GDSII handoff<span class="chu-thich"><b>GDSII handoff</b><br><br>VNCHIP Labs provides comprehensive GDSII handoff services to ensure designs are fully verified, properly configured, and ready for successful fabrication. Prior to tapeout, our teams perform final implementation reviews and signoff checks covering all physical design layers, process technology rules, timing constraints, power integrity, manufacturability requirements, and foundry-specific configurations.<br><br>We work closely with customers through structured tapeout readiness reviews to validate DRC/LVS closure, STA signoff, antenna checks, density verification, IR/EM analysis, metal fill insertion, packaging interface requirements, and final netlist-to-layout consistency. Our methodology ensures that all design databases, masks, configuration files, and deliverables are aligned with foundry requirements before final GDSII release. This disciplined review and handoff process helps reduce fabrication risk and improves confidence in first-silicon success.</span></b> for fabrication.
                </p>
                <p class="spec-paragraph">
                  With experience across advanced-node technologies and large-scale SoC integration, VNCHIP Labs helps customers reduce risk, accelerate schedules, and improve first-silicon success.
                </p>
              </div>
            </div>

            <!-- Item 3: Netlist to GDSII -->
            <div class="service-item">
              <div class="service-header">
                <span class="service-name">Netlist to GDSII</span>
                <span class="service-icon">+</span>
              </div>
              <div class="service-body">
                <img src="<?= asset('uploads/media/img_1778205036_0.webp') ?>" alt="Netlist to GDSII" class="service-body-img img-fluid rounded mb-3" loading="lazy">
                <p class="spec-paragraph">
                  VNCHIP Labs offers high-quality Netlist-to-GDSII implementation services for customers who already completed front-end design and require experienced backend execution teams to achieve timing, power, area, and manufacturability goals.
                </p>
                <p class="spec-paragraph">
                  Starting from synthesized netlists, our services include backend verification, scan and DFT integration support, floorplanning, placement and routing, clock-tree synthesis, ECO implementation, power and performance analysis, physical verification, and <b class="tuong-tac" tabindex="0">STA/DFT signoff<span class="chu-thich"><b>STA/DFT signoff</b><br><br>VNCHIP Labs provides comprehensive STA and DFT signoff services to ensure designs meet timing, testability, reliability, and manufacturability requirements before tapeout. Our physical implementation teams perform detailed analysis and verification throughout the backend flow, including timing constraint validation, multi-mode multi-corner (MMMC) static timing analysis, clock-domain crossing checks, signal integrity analysis, IR drop and electromigration evaluation, power integrity verification, noise analysis, thermal assessment, and physical design rule compliance.<br><br>For DFT signoff, we support scan insertion, ATPG generation, scan compression, memory BIST integration, JTAG/debug infrastructure, test coverage analysis, and manufacturing test readiness validation. During implementation and signoff, VNCHIP Labs conducts extensive backend verification iterations to identify and resolve timing violations, congestion issues, routing challenges, DRC/LVS violations, testability gaps, and ECO-related issues. Our disciplined signoff methodology helps customers achieve reliable first-silicon success and production-ready GDSII handoff for fabrication.</span></b>. Our engineers work closely with customer front-end and verification teams to resolve implementation challenges related to timing closure, signal integrity, IR drop, electromigration, thermal constraints, and design-rule compliance.
                </p>
                <p class="spec-paragraph">
                  The final deliverable includes a production-ready <b class="tuong-tac" tabindex="0">GDSII handoff<span class="chu-thich"><b>GDSII handoff</b><br><br>VNCHIP Labs provides comprehensive GDSII handoff services to ensure designs are fully verified, properly configured, and ready for successful fabrication. Prior to tapeout, our teams perform final implementation reviews and signoff checks covering all physical design layers, process technology rules, timing constraints, power integrity, manufacturability requirements, and foundry-specific configurations.<br><br>We work closely with customers through structured tapeout readiness reviews to validate DRC/LVS closure, STA signoff, antenna checks, density verification, IR/EM analysis, metal fill insertion, packaging interface requirements, and final netlist-to-layout consistency. Our methodology ensures that all design databases, masks, configuration files, and deliverables are aligned with foundry requirements before final GDSII release. This disciplined review and handoff process helps reduce fabrication risk and improves confidence in first-silicon success.</span></b> package optimized for successful fabrication and silicon bring-up.
                </p>
              </div>
            </div>

          </div>
        </div>

        <!-- Column 2: IP SERVICE -->
        <div class="service-col">
          <h2 class="service-col-title">IP SERVICE</h2>
          <div class="service-accordion" id="accIP">
            
            <!-- Item 1: IP Development -->
            <div class="service-item">
              <div class="service-header">
                <span class="service-name">IP Development</span>
                <span class="service-icon">+</span>
              </div>
              <div class="service-body">
                <img src="<?= asset('uploads/media/img_1778149883_0.webp') ?>" alt="IP Development" class="service-body-img img-fluid rounded mb-3" loading="lazy">
                <p class="spec-paragraph">
                  VNCHIP Labs provides comprehensive IP development services for semiconductor companies building custom SoCs, chiplets, and platform solutions. Similar to a full Spec-to-GDSII development flow, our IP development process spans architecture definition, <b class="tuong-tac" tabindex="0">RTL development<span class="chu-thich"><b>RTL Development/Refinement</b><br><br>VNCHIP Labs provides RTL development and refinement services for SoCs, ASICs, chiplets, and subsystem designs across AI, networking, data center, automotive, and edge applications. Our engineering teams can develop original RTL implementations based on customer-provided architecture and microarchitecture specifications, delivering scalable, synthesizable, and verification-ready designs optimized for performance, power, and area goals.<br><br>In addition to new RTL development, VNCHIP Labs offers RTL refinement services to enhance existing or incomplete RTL implementations. This includes improving functionality, performance optimization, protocol compliance, code restructuring, timing-aware modifications, low-power enhancements, debug support, and integration readiness. Our teams work closely with customers to accelerate development schedules while improving design quality, maintainability, and first-silicon success.</span></b>, <b class="tuong-tac" tabindex="0">verification<span class="chu-thich"><b>Design verification</b><br><br>VNCHIP Labs provides comprehensive design verification services using advanced UVM-based verification methodologies to validate RTL implementations against architecture and microarchitecture specifications. Our teams develop scalable verification environments, testbenches, protocol monitors, scoreboards, assertions, and coverage-driven regression infrastructures that thoroughly stimulate and validate functional correctness across complex SoC and IP designs.<br><br>To accelerate verification closure, VNCHIP Labs leverages AI-assisted automation and intelligent verification agents to improve testcase generation, regression analysis, bug triage, and coverage optimization. By combining traditional verification expertise with AI-driven workflows, we help customers achieve verification milestones faster while improving functional, code, and assertion coverage metrics, reducing overall verification cycle time and increasing first-silicon confidence.</span></b>, <b class="tuong-tac" tabindex="0">prototyping<span class="chu-thich"><b>Prototyping</b><br><br>VNCHIP Labs provides FPGA-based prototyping services to help customers validate IPs, subsystems, and critical portions of complex SoC designs before silicon availability. Our teams can implement customer IPs and system architectures on FPGA platforms to enable high-speed functional validation, software development, performance analysis, and early ecosystem integration.<br><br>We develop drivers, firmware, and validation software to exercise the design at realistic operating speeds and to interface with actual partner or third-party devices and platforms. In addition, VNCHIP Labs supports software bring-up, application development, and customer driver validation using the prototyping environment, allowing hardware and software teams to identify issues early, accelerate system integration, and reduce overall silicon development risk.</span></b>, <b class="tuong-tac" tabindex="0">power and performance analysis<span class="chu-thich"><b>Power and performance analysis</b><br><br>VNCHIP Labs provides power and performance analysis services to help customers evaluate their designs against key application workloads and system use cases. Our teams analyze architecture behavior, data movement, memory bandwidth, latency, interconnect efficiency, and compute utilization to identify performance bottlenecks and estimate dynamic and static power consumption under realistic operating conditions.<br><br>Using simulation, profiling, emulation, and workload-driven analysis techniques, we help customers understand tradeoffs between performance, power, area, and thermal constraints early in the development cycle. Based on the analysis results, VNCHIP Labs can recommend architectural, RTL, clocking, memory, and interconnect optimizations to improve system throughput, reduce latency, and lower overall power consumption while maintaining design goals and implementation feasibility.</span></b>, physical implementation, <b class="tuong-tac" tabindex="0">STA/DFT signoff<span class="chu-thich"><b>STA/DFT signoff</b><br><br>VNCHIP Labs provides comprehensive STA and DFT signoff services to ensure designs meet timing, testability, reliability, and manufacturability requirements before tapeout. Our physical implementation teams perform detailed analysis and verification throughout the backend flow, including timing constraint validation, multi-mode multi-corner (MMMC) static timing analysis, clock-domain crossing checks, signal integrity analysis, IR drop and electromigration evaluation, power integrity verification, noise analysis, thermal assessment, and physical design rule compliance.<br><br>For DFT signoff, we support scan insertion, ATPG generation, scan compression, memory BIST integration, JTAG/debug infrastructure, test coverage analysis, and manufacturing test readiness validation. During implementation and signoff, VNCHIP Labs conducts extensive backend verification iterations to identify and resolve timing violations, congestion issues, routing challenges, DRC/LVS violations, testability gaps, and ECO-related issues. Our disciplined signoff methodology helps customers achieve reliable first-silicon success and production-ready GDSII handoff for fabrication.</span></b>, and final delivery. The difference is that the final deliverables are reusable and configurable IP solutions designed for integration into larger customer systems.
                </p>
                <p class="spec-paragraph">
                  Our IP deliverables can include configurable soft IP RTL packages, verification environments, integration documentation, firmware or driver support, synthesis constraints, timing and power models, and subsystem-level validation collateral. Each IP is thoroughly verified using coverage-driven methodologies and validated for functionality, interoperability, and integration readiness across target use cases and protocols.
                </p>
                <p class="spec-paragraph">
                  For customers requiring implementation-ready solutions, VNCHIP Labs can also harden IPs for specific foundry technologies and process nodes to meet targeted frequency, power, area, and performance goals. Hardened IP deliverables may include gate-level netlists, floorplans, timing views, physical abstracts, and layout databases that are ready for efficient integration into full-chip physical design flows.
                </p>
              </div>
            </div>

            <!-- Item 2: Circuit Design -->
            <div class="service-item">
              <div class="service-header">
                <span class="service-name">Circuit Design</span>
                <span class="service-icon">+</span>
              </div>
              <div class="service-body">
                <img src="<?= asset('uploads/media/media_1776160790_0.webp') ?>" alt="Circuit Design" class="service-body-img img-fluid rounded mb-3" loading="lazy">
                <p class="spec-paragraph">
                  VNCHIP Labs provides circuit design support services for analog and mixed-signal designs where customers can provide detailed architecture specifications, performance targets, and design constraints. Our teams can assist with circuit enhancement, design refinement, technology migration, and implementation support for selected analog and mixed-signal functions as part of broader SoC or subsystem development efforts.
                </p>
                <p class="spec-paragraph">
                  Our services include circuit optimization, transistor-level modifications, process-node porting, layout coordination, and simulation-based validation across performance, power, timing, and operating-corner requirements. We support SPICE-level analysis and verification to evaluate functionality and compliance with customer specifications. While VNCHIP Labs is primarily focused on digital and system-level silicon development, we can work collaboratively with customers and ecosystem partners to support practical analog and mixed-signal integration needs within larger chip development programs.
                </p>
              </div>
            </div>

            <!-- Item 3: Custom Layout -->
            <div class="service-item">
              <div class="service-header">
                <span class="service-name">Custom Layout</span>
                <span class="service-icon">+</span>
              </div>
              <div class="service-body">
                <img src="<?= asset('uploads/media/media_1776305627_0.webp') ?>" alt="Custom Layout" class="service-body-img img-fluid rounded mb-3" loading="lazy">
                <p class="spec-paragraph">
                  VNCHIP Labs provides custom layout services through experienced layout engineers supporting analog, mixed-signal, memory, high-speed interface, and custom digital circuit implementations across multiple process technologies. Our teams work closely with circuit designers and customers to translate schematics and design intent into high-quality physical layouts optimized for performance, area, matching accuracy, manufacturability, and reliability.
                </p>
                <p class="spec-paragraph">
                  Our custom layout capabilities include transistor-level layout implementation, floorplanning, device matching, shielding, guard-ring design, parasitic-aware optimization, electromigration-aware routing, density management, and layout-dependent effect considerations for advanced process nodes. We support full physical verification flows including DRC, LVS, ERC, antenna checks, and parasitic extraction validation to ensure layouts meet foundry requirements and design specifications.
                </p>
                <p class="spec-paragraph">
                  VNCHIP Labs can support both new custom layout development and layout migration or refinement for existing designs, helping customers accelerate tapeout schedules while maintaining high-quality silicon implementation standards.
                </p>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- 4. PARTNERS SECTION -->
  <section class="partners-section" id="partners">
    <div class="container">
      
      <div class="partners-header">
        <div>
          <h2 class="partners-title home-section-title">PARTNERS</h2>
          <p class="section-subtitle">Building a robust semiconductor ecosystem with global industry leaders.</p>
        </div>
        
        <div class="slider-controls">
          <button class="slider-btn partners-prev-btn" aria-label="Previous Slide">
            <i class="fa-solid fa-chevron-left"></i>
          </button>
          <button class="slider-btn partners-next-btn" aria-label="Next Slide">
            <i class="fa-solid fa-chevron-right"></i>
          </button>
        </div>
      </div>

      <div class="partners-carousel-track">
        
        <!-- Partner Card 1 -->
        <a href="<?= url('partner-detail.php?slug=hierarchical-apr-on-tsmc-n3e-axmm-topblock') ?>" class="partner-card">
          <div class="partner-card-media">
            <img src="<?= asset('images/featured_1778551077.webp') ?>" alt="Hierarchical APR on TSMC N3E">
          </div>
          <div class="partner-card-content">
            <h3 class="partner-card-title">Hierarchical APR on TSMC N3E — AXMM top-block</h3>
            <p class="partner-card-desc">
              4-partition hierarchical PnR for edge AI inference processor. IOPin budgeting, ELVT leakage containment, EUV multi-patterning DRC/DFM compliance, and MCMM STA sign-off.
            </p>
          </div>
        </a>

        <!-- Partner Card 2 -->
        <a href="<?= url('partner-detail.php?slug=rtltogdsii-fullchip-150m-instances') ?>" class="partner-card">
          <div class="partner-card-media">
            <img src="<?= asset('images/featured_1778550238.webp') ?>" alt="RTL-to-GDSII full-chip — 150M instances">
          </div>
          <div class="partner-card-content">
            <h3 class="partner-card-title">RTL-to-GDSII full-chip — 150M instances</h3>
            <p class="partner-card-desc">
              Turnkey hierarchical PnR for AI inference SoC across 26 partitions, 8 hardened IPs, 596 clock domains with MCMM/POCV STA sign-off and EM-IR closure.
            </p>
          </div>
        </a>

        <!-- Partner Card 3 -->
        <a href="<?= url('partner-detail.php?slug=largestdie-rtltogdsii-175-x-28-mm') ?>" class="partner-card">
          <div class="partner-card-media">
            <img src="<?= asset('images/featured_1778551109.webp') ?>" alt="Largest-die RTL-to-GDSII — 17.5 x 28 mm">
          </div>
          <div class="partner-card-content">
            <h3 class="partner-card-title">Largest-die RTL-to-GDSII — 17.5 x 28 mm</h3>
            <p class="partner-card-desc">
              Turnkey full-chip PnR for Automotive ADAS vision SoC with 13 partitions, 396 clock domains, AOCV MCMM STA sign-off, EM-IR closure, and complete DRC/LVS.
            </p>
          </div>
        </a>

        <!-- Partner Card 4 -->
        <a href="<?= url('partner-detail.php?slug=highdensity-fullchip-hrp_top-629-hard-macros') ?>" class="partner-card">
          <div class="partner-card-media">
            <img src="<?= asset('images/featured_1778553729.webp') ?>" alt="High-density full-chip — HRP_TOP, 629 hard macros">
          </div>
          <div class="partner-card-content">
            <h3 class="partner-card-title">High-density full-chip — HRP_TOP, 629 hard macros</h3>
            <p class="partner-card-desc">
              Full chiptop for Automotive safety MCU on M1-M9 stack with 629 hard macros, AC timing and interface STA sign-off at 1 GHz under narrow-channel routing.
            </p>
          </div>
        </a>

      </div>

    </div>
  </section>

  <!-- 5. SUSTAINABLE & TALENTS SECTION -->
  <section class="sustainable-section" id="sustainable">
    <div class="container">
      
      <div class="sustainable-layout">
        <!-- Left Content -->
        <div class="sustainable-info">
          <h2 class="sustainable-main-title home-section-title">SUSTAINABLE</h2>
          <h3 class="sustainable-subtitle">TALENTS DEVELOPMENT</h3>
          <p class="sustainable-desc">
            At VNCHIP, we believe that talent is the core value of innovation and success in the IC design industry.
          </p>
          <p class="sustainable-desc">
            VNCHIP experts' team is committed to pushing the boundaries of technology while staying true to our core values of knowledge-sharing, innovation, and client satisfaction. We are excited to continue growing and making a lasting impact on Vietnam IC Design industry.
          </p>
        </div>

        <!-- Right Program Cards -->
        <div class="sustainable-cards-group">
          <a href="<?= url('program-detail.php?program=talent') ?>" class="program-card" title="VNCHIP Talent Program">
            <div class="program-card-inner">
              <div class="program-card-visual">
                <img src="<?= asset('images/interns.svg') ?>" alt="VNCHIP Talent Program Icon" class="program-icon-img">
              </div>
              <div class="program-card-footer">
                <div class="program-card-brand">VNCHIP</div>
                <div class="program-card-title">TALENT PROGRAM</div>
              </div>
            </div>
          </a>

          <a href="<?= url('program-detail.php?program=interns') ?>" class="program-card" title="VNCHIP Interns Program">
            <div class="program-card-inner">
              <div class="program-card-visual">
                <img src="<?= asset('images/talent.svg') ?>" alt="VNCHIP Interns Program Icon" class="program-icon-img">
              </div>
              <div class="program-card-footer">
                <div class="program-card-brand">VNCHIP</div>
                <div class="program-card-title">INTERNS PROGRAM</div>
              </div>
            </div>
          </a>
        </div>
      </div>

    </div>
  </section>


  <!-- 6. MEDIA CENTER SECTION (Dynamic from Database) -->
  <section class="media-section" id="media-center">
    <div class="container">
      
      <div class="partners-header">
        <div>
          <h2 class="section-title-tech home-section-title">MEDIA CENTER</h2>
        </div>
        <div class="slider-controls">
          <button class="slider-btn media-prev-btn" aria-label="Previous News">
            <i class="fa-solid fa-chevron-left"></i>
          </button>
          <button class="slider-btn media-next-btn" aria-label="Next News">
            <i class="fa-solid fa-chevron-right"></i>
          </button>
        </div>
      </div>

      <!-- Dynamic Grid of Articles from MySQL Database -->
      <div class="media-grid">
        <?php foreach ($featuredNews as $article): ?>
          <a href="<?= url('media-detail.php?slug=' . urlencode($article['slug'])) ?>" class="media-card">
            <div class="media-thumb-wrapper">
              <img src="<?= asset($article['thumbnail']) ?>" alt="<?= e($article['title']) ?>" class="media-thumb">
              <?php if (!empty($article['category_name'])): ?>
                <span class="media-category-badge"><?= e($article['category_name']) ?></span>
              <?php endif; ?>
            </div>
            <h3 class="media-title"><?= e($article['title']) ?></h3>
            <p class="media-desc"><?= e($article['summary']) ?></p>
          </a>
        <?php endforeach; ?>
      </div>

    </div>
  </section>

  <!-- 7. REUSABLE CTA BANNER -->
  <?php require_once __DIR__ . '/includes/cta_banner.php'; ?>

  <!-- 8. GLOBAL FOOTER -->
  <?php require_once __DIR__ . '/includes/footer.php'; ?>
