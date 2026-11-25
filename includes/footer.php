<?php
/**
 * Global Footer Template
 * 100% Dynamic & Supports Admin Live Edit for Every Component
 */

if (!defined('VNCHIP_APP')) {
    exit('Direct access not permitted');
}

$footerAddress = Setting::getOr('site_address_vn', SITE_ADDRESS_VN);
$footerEmail = Setting::getOr('site_email', SITE_EMAIL);
$footerPhone = Setting::getOr('site_phone', SITE_PHONE);
$footerCopyright = Setting::getOr('footer_copyright', 'Design & Copyright &copy; 2026 by VNCHIP Technology JSC. All rights reserved.');
$footerFb = Setting::getOr('social_facebook', 'https://www.facebook.com/vnchipinstitute');
$footerLi = Setting::getOr('social_linkedin', 'https://www.linkedin.com/company/vnchip-technology-jsc/');
$footerYt = Setting::getOr('social_youtube', 'https://www.youtube.com/@vnchiptechnology2362');
$footerLogo = Setting::getOr('logo_footer', 'images/logo_vnchip_labs.png');

$partnerLink1 = Setting::getOr('footer_partner_link_1', 'https://vnchip-inc.com/');
$partnerLogo1 = Setting::getOr('footer_partner_logo_1', 'images/logo_vnchip_main.png');
$partnerLink2 = Setting::getOr('footer_partner_link_2', 'https://vnchip.edu.vn/');
$partnerLogo2 = Setting::getOr('footer_partner_logo_2', 'images/footer_logo2.png');

// Default 3 Navigation Columns Markup
$defaultFooterNav = <<<HTML
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

$customFooterNav = Setting::get('section_footer_nav', '');
?>
  <!-- Global Footer -->
  <footer class="site-footer" id="siteFooter">
    <div class="container">
      
      <!-- Top Navigation Columns (Fully Customizable) -->
      <div class="footer-nav-grid" id="live-footer-nav-grid">
        <?= !empty($customFooterNav) ? $customFooterNav : $defaultFooterNav ?>
      </div>

      <div class="footer-divider"></div>

      <!-- Middle Brand Row: Main Logo, Info, Sub-brand Logos -->
      <div class="footer-brand-row" id="live-footer-brand-row">
        <div class="footer-brand-info">
          <img src="<?= asset($footerLogo) ?>" alt="VNCHIP" class="footer-logo" id="live-footer-logo">
          <p class="footer-contact-info">
            <span id="live-footer-address"><?= nl2br(e($footerAddress)) ?></span><br>
            <a href="mailto:<?= e($footerEmail) ?>" id="live-footer-email" style="color: inherit;"><?= e($footerEmail) ?></a> | <a href="tel:<?= str_replace([' ', '-', '.'], '', e($footerPhone)) ?>" id="live-footer-phone" style="color: inherit;"><?= e($footerPhone) ?></a>
          </p>
        </div>

        <div class="footer-partner-logos" id="live-footer-partner-logos">
          <a href="<?= e($partnerLink1) ?>" target="_blank" rel="noopener" id="live-footer-partner-link-1">
            <img src="<?= asset($partnerLogo1) ?>" alt="VNCHIP Technology" class="footer-sub-logo footer-sub-logo--main" id="live-footer-partner-img-1">
          </a>
          <a href="<?= e($partnerLink2) ?>" target="_blank" rel="noopener" id="live-footer-partner-link-2">
            <img src="<?= asset($partnerLogo2) ?>" alt="VNCHIP Institute" class="footer-sub-logo footer-sub-logo--institute" id="live-footer-partner-img-2">
          </a>
        </div>
      </div>

      <!-- Bottom Copyright Row -->
      <div class="footer-bottom-row" id="live-footer-bottom-row">
        <div id="live-footer-copyright">
          <?= $footerCopyright ?>
        </div>
        <div class="social-icons" id="live-footer-social-icons">
          <a href="<?= e($footerFb) ?>" target="_blank" rel="noopener" class="social-btn" id="live-footer-fb" aria-label="Facebook">
            <i class="fa-brands fa-facebook-f"></i>
          </a>
          <a href="<?= e($footerLi) ?>" target="_blank" rel="noopener" class="social-btn" id="live-footer-li" aria-label="LinkedIn">
            <i class="fa-brands fa-linkedin-in"></i>
          </a>
          <a href="<?= e($footerYt) ?>" target="_blank" rel="noopener" class="social-btn" id="live-footer-yt" aria-label="YouTube">
            <i class="fa-brands fa-youtube"></i>
          </a>
        </div>
      </div>

    </div>
  </footer>

  <!-- Global Frontpage Scroll To Top Button -->
  <button type="button" id="vnchipScrollTopBtn" class="vnchip-scroll-top-btn" aria-label="Scroll to top" title="Lên đầu trang">
    <svg class="vnchip-scroll-ring" viewBox="0 0 48 48" aria-hidden="true">
      <circle class="vnchip-scroll-ring-bg" cx="24" cy="24" r="21"></circle>
      <circle class="vnchip-scroll-ring-progress" id="vnchipScrollTopProgress" cx="24" cy="24" r="21"></circle>
    </svg>
    <i class="fa-solid fa-arrow-up"></i>
  </button>

  <style>
  .vnchip-scroll-top-btn {
    position: fixed;
    right: 26px;
    bottom: 26px;
    z-index: 9990;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 1px solid rgba(56, 189, 248, 0.35);
    padding: 0;
    background: rgba(7, 21, 43, 0.92);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: #FFFFFF;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35), 0 0 12px rgba(0, 210, 255, 0.18);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transform: translateY(16px);
    transition: opacity 0.25s ease, transform 0.25s ease, visibility 0.25s ease, background 0.22s ease, border-color 0.22s ease, box-shadow 0.22s ease;
  }
  .vnchip-scroll-top-btn.is-visible {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
    transform: translateY(0);
  }
  .vnchip-scroll-top-btn:hover {
    background: linear-gradient(135deg, #F97316 0%, #EA580C 100%);
    border-color: rgba(255, 184, 0, 0.85);
    transform: translateY(-3px);
    box-shadow: 0 10px 26px rgba(234, 88, 12, 0.5), 0 0 18px rgba(255, 184, 0, 0.45);
  }
  .vnchip-scroll-top-btn i {
    position: relative;
    z-index: 2;
    font-size: 1rem;
  }
  .vnchip-scroll-top-btn .vnchip-scroll-ring {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
    pointer-events: none;
  }
  .vnchip-scroll-top-btn .vnchip-scroll-ring-bg {
    fill: none;
    stroke: rgba(255, 255, 255, 0.14);
    stroke-width: 2.4;
  }
  .vnchip-scroll-top-btn .vnchip-scroll-ring-progress {
    fill: none;
    stroke: #00D2FF;
    stroke-width: 2.6;
    stroke-linecap: round;
    stroke-dasharray: 131.95;
    stroke-dashoffset: 131.95;
    transition: stroke-dashoffset 0.08s linear, stroke 0.22s ease;
  }
  .vnchip-scroll-top-btn:hover .vnchip-scroll-ring-progress {
    stroke: #FDE68A;
  }
  @media (max-width: 576px) {
    .vnchip-scroll-top-btn {
      right: 16px;
      bottom: 18px;
      width: 42px;
      height: 42px;
    }
  }
  @media print {
    .vnchip-scroll-top-btn {
      display: none !important;
    }
  }
  </style>

  <!-- Scripts -->
  <script src="<?= asset('js/main.js') ?>?v=<?= filemtime(__DIR__ . '/../assets/js/main.js') ?>"></script>
  <script src="<?= asset('js/slider.js') ?>"></script>
  <script>
  (function() {
    const btn = document.getElementById('vnchipScrollTopBtn');
    const ring = document.getElementById('vnchipScrollTopProgress');
    if (!btn) return;
    const circumference = 2 * Math.PI * 21;
    let ticking = false;

    function updateFrontScrollTop() {
      const scrollTop = window.scrollY || document.documentElement.scrollTop || 0;
      const docHeight = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
      const ratio = Math.min(1, Math.max(0, scrollTop / docHeight));

      if (scrollTop > 260) {
        btn.classList.add('is-visible');
      } else {
        btn.classList.remove('is-visible');
      }
      if (ring) {
        ring.style.strokeDashoffset = (circumference * (1 - ratio)).toFixed(2);
      }
      ticking = false;
    }

    window.addEventListener('scroll', function() {
      if (!ticking) {
        ticking = true;
        window.requestAnimationFrame(updateFrontScrollTop);
      }
    }, { passive: true });

    btn.addEventListener('click', function() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    updateFrontScrollTop();
  })();
  </script>
  <?php 
  $customFooter = Setting::get('custom_footer_scripts', '');
  if (trim($customFooter) !== ''): 
    echo $customFooter;
  endif; 
  ?>
</body>
</html>
