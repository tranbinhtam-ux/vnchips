<?php
/**
 * Business & Design Services Page (Page 3 of Design PDF)
 * VNCHIP Technology JSC
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Partner.php';

// Success in Action carousel (managed in Admin > Partners)
$successProjects = Partner::getPublished();

// Partners Projects cards (data/projects.php)
$projects = require __DIR__ . '/data/projects.php';

$bizTechTitle = Setting::getOr('business_tech_title', 'TECHNOLOGY');

$pageTitle = 'Business & Solutions | Turnkey IC Design & R&D | VNCHIP Technology JSC';
$pageDescription = 'Comprehensive turnkey semiconductor design solutions: RTL to GDSII, custom IC layout, advanced IP blocks, and strategic research partnerships in AI-Centric SoCs, Chiplets, and advanced FinFET nodes.';
$activePage = 'business';

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Hero Section (PCB Traces Backdrop) -->
<section class="biz-hero-section" style="background-image: url('<?= asset(Setting::getOr('hero_business', 'images/hero_chip_bg.webp')) ?>'); background-size: cover; background-position: center;">
  <div class="biz-hero-bg"></div>
  <div class="container biz-hero-content">
    <h1 class="biz-hero-title">BUSINESS</h1>
  </div>
</section>

<!-- 2. Services Section (White Background: Technology Card + Design & IP Services) -->
<section class="biz-services-section" id="business-solutions">
  <div class="container">
    
    <!-- Technology Capsule Card (Fully on white background below Hero banner) -->
    <div class="biz-tech-container" id="technology">
      <h2 class="biz-tech-title" id="live-biz-tech-title"><?= e($bizTechTitle) ?></h2>
      <?php if (!empty($customBizTech = Setting::get('section_business_services'))): ?>
        <div id="live-biz-tech-box"><?= $customBizTech ?></div>
      <?php else: ?>
      <div class="biz-tech-box" id="live-biz-tech-box">
        <div class="biz-tech-items">
          
          <!-- Tech Item 1: Chiplet Disaggregation -->
          <a href="<?= url('technology.php#chiplet-disaggregation') ?>" class="biz-tech-item" title="Chiplet Disaggregation">
            <div class="biz-tech-icon">
              <i class="fas fa-microchip"></i>
            </div>
            <div class="biz-tech-text">
              CHIPLET<br>DISAGGREGATION
            </div>
          </a>

          <!-- Tech Item 2: Interconnect Standards -->
          <a href="<?= url('technology.php#interconnect-standards') ?>" class="biz-tech-item" title="Interconnect Standards">
            <div class="biz-tech-icon">
              <i class="fas fa-network-wired"></i>
            </div>
            <div class="biz-tech-text">
              INTERCONNECT<br>STANDARDS
            </div>
          </a>

          <!-- Tech Item 3: AI-Centric SoC Building -->
          <a href="<?= url('technology.php#ai-centric-soc') ?>" class="biz-tech-item" title="AI-Centric SoC Building">
            <div class="biz-tech-icon">
              <i class="fas fa-brain"></i>
            </div>
            <div class="biz-tech-text">
              AI-CENTRIC SOC<br>BUILDING
            </div>
          </a>

        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Design Services & IP Matrix (Detailed Port from Homepage & Design Specs) -->
    <div class="services-grid" id="design-service">
      
      <!-- Column 1: DESIGN SERVICE -->
      <div class="service-col">
        <h2 class="service-col-title">DESIGN SERVICE</h2>
        <div class="service-accordion" id="accDesign">
          
          <!-- Item 1: Specification to GDSII -->
          <div class="service-item" id="spec-gdsii">
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
          <div class="service-item" id="rtl-gdsii">
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
          <div class="service-item" id="netlist-gdsii">
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
      <div class="service-col" id="ip-service">
        <h2 class="service-col-title">IP SERVICE</h2>
        <div class="service-accordion" id="accIP">
          
          <!-- Item 1: IP Development -->
          <div class="service-item" id="ip-development">
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
          <div class="service-item" id="circuit-design">
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
          <div class="service-item" id="custom-layout">
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

<!-- 4. Partners Projects - card carousel, each card opens project-detail.php (data/projects.php) -->
<section class="biz-rd-section" id="rd-collaborations">
  <div class="container" id="partners-projects">

    <div class="biz-rd-header">
      <h2 class="biz-rd-title">PROJECTS</h2>
      <div class="carousel-nav-arrows dark-arrows">
        <button class="nav-arrow-btn dark" aria-label="Previous Project"><i class="fa-solid fa-chevron-left"></i></button>
        <button class="nav-arrow-btn dark" aria-label="Next Project"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
    </div>

    <div class="biz-project-track">
      <?php foreach ($projects as $project): ?>
        <div class="biz-project-card">
          <div class="biz-project-media">
            <img src="<?= asset($project['image']) ?>" alt="<?= e($project['title']) ?>" loading="lazy">
          </div>
          <div class="biz-project-body">
            <h3 class="biz-project-title"><?= e($project['title']) ?></h3>
            <p class="biz-project-subtitle"><?= e($project['subtitle']) ?></p>
            <p class="biz-project-summary"><?= e($project['summary']) ?></p>
            <a href="<?= url('project-detail.php?slug=' . urlencode($project['slug'])) ?>" class="biz-project-more">Read more <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- 5. Success in Action Section (Dark Navy) -->
<section class="biz-success-section" id="success-in-action">
  <div class="container">
    
    <div class="biz-success-header">
      <div>
        <h2 class="biz-success-title">SUCCESS IN ACTION</h2>
        <p class="biz-success-subtitle">Excellence in semiconductor design delivery through proven methodologies and technical innovation.</p>
      </div>
      <div class="carousel-nav-arrows">
        <button class="nav-arrow-btn" aria-label="Previous Slide"><i class="fa-solid fa-chevron-left"></i></button>
        <button class="nav-arrow-btn" aria-label="Next Slide"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
    </div>

    <div class="biz-success-grid">
      <?php foreach ($successProjects as $project): ?>
        <a href="<?= url('partner-detail.php?slug=' . urlencode($project['slug'])) ?>" class="biz-success-card">
          <div class="biz-success-media">
            <img src="<?= asset($project['image_url']) ?>" alt="<?= e($project['title']) ?>" loading="lazy">
          </div>
          <div class="biz-success-body">
            <h3 class="biz-success-card-title"><?= e($project['title']) ?></h3>
            <p class="biz-success-card-desc"><?= e($project['excerpt']) ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- Partner Values / University Partners moved to sustainable.php#partner -->
<script>
  (function () {
    if (location.hash === '#partner-values' || location.hash === '#university-partners') {
      location.replace('<?= url('sustainable.php#partner') ?>');
    }
  })();
</script>

<!-- CTA & Footer -->
<?php require_once __DIR__ . '/includes/cta_banner.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
