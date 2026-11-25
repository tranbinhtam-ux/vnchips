<?php
/**
 * Partners Projects (business.php#partners-projects cards -> project-detail.php?slug=...)
 * To add a project: append an entry with a unique slug. 'content' is trusted HTML (detail page body).
 */

$projects = [
    [
        'slug'     => 'viet-edge-ai',
        'title'    => 'VIET EDGE AI',
        'subtitle' => 'A Shared Platform to Build Edge AI Semiconductor Products in Viet Nam',
        'image'    => 'images/rd_team_group_photo.webp',
        'summary'  => 'Viet Edge AI should be presented as a shared development platform for Vietnamese institutions and companies that want to build practical Edge AI semiconductor products. The platform should connect algorithm development, AI models, accelerator/NPU architecture, SoC integration, FPGA prototyping, software, and eventual ASIC implementation. VNCHIP’s role is to provide a credible semiconductor engineering base and an open collaboration structure—not to imply that one company owns the national ecosystem.',
        'content'  => <<<'HTML'
<div class="biz-rd-bottom-text">
  <h3 class="biz-rd-subtitle">Core message</h3>
  <ul class="biz-rd-points">
      <li><i class="fa-solid fa-check"></i><span>Designed for collaboration among universities, research centers, startups, product companies, and semiconductor engineering teams.</span></li>
      <li><i class="fa-solid fa-check"></i><span>From AI model → hardware architecture → FPGA prototype → software stack → product-oriented ASIC roadmap.</span></li>
      <li><i class="fa-solid fa-check"></i><span>Focus on efficient, practical inference at the edge: vision, robotics, cameras, industrial systems, smart infrastructure, and embedded AI.</span></li>
  </ul>

  <h3 class="biz-rd-subtitle">Live demonstration: face detection and recognition</h3>
  <p>
    The live demo should use a camera feeding an FPGA system that runs a VNCHIP-developed NPU or NPU subsystem. The system detects faces in a live video stream and, where permitted and appropriately consented, demonstrates recognition against a small local demo database. The purpose is not the application itself; the application is a visible proof that VNCHIP can integrate AI compute, memory movement, control software, camera/video data paths, and system-level FPGA implementation. The setup includes the following:
  </p>
  <ul class="biz-rd-points">
      <li><i class="fa-solid fa-check"></i><span>FPGA board with the camera attached to it. We should have a stand so the camera can be firmly face the front of the booth.</span></li>
      <li><i class="fa-solid fa-check"></i><span>A desktop monitor with HDMI interface: live camera image with bounding boxes and inference status.</span></li>
      <li><i class="fa-solid fa-check"></i><span>A control laptop: hardware telemetry through microUSB cable, can change parameters, enroll a new face.</span></li>
      <li><i class="fa-solid fa-check"></i><span>Demo is working in “privacy mode” mode that performs face detection only and does not store faces. Recognition should be limited to pre-enrolled demo participants or staff with consent.</span></li>
  </ul>

  <h3 class="biz-rd-subtitle">What the organizer must prepare</h3>
  <ul class="biz-rd-points">
      <li><i class="fa-solid fa-check"></i><span>One stable demo counter with camera mount, FPGA board, power supplies, cooling clearance, and cable management.</span></li>
      <li><i class="fa-solid fa-check"></i><span>One 27–32 inch monitor facing the aisle; a second smaller engineering monitor is optional.</span></li>
      <li><i class="fa-solid fa-check"></i><span>Controlled front lighting so detection remains stable throughout the day.</span></li>
      <li><i class="fa-solid fa-check"></i><span>A physical “demo boundary” mark so visitors know where to stand for camera interaction.</span></li>
      <li><i class="fa-solid fa-check"></i><span>A backup recorded demo video on the presentation PC in case of camera, FPGA, or software failure.</span></li>
  </ul>

  <h3 class="biz-rd-subtitle">Partnership call-to-action</h3>
  <p>
    The local team should prominently display a partner invitation:
  </p>
  <blockquote class="biz-rd-cta-quote">
    “Bring your model, sensor, IP, software, or application. Let us build and benchmark the Edge AI platform together.”
  </blockquote>
  <p>
    Use QR codes for separate partner categories: Research / University, Product Company, Semiconductor / IP, and Investment / Strategic Partnership.
  </p>
</div>
HTML
    ],
];

return $projects;
