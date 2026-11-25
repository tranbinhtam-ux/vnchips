<?php /** Partners sidebar: CTA, technical support contact, ASIC service links (shared by partners.php & partner-detail.php) */ ?>
      <aside class="sidebar-col">
        <div class="sidebar-sticky" style="position: sticky; top: 120px;">
          
          <!-- Quick Contact Widget -->
          <div class="sidebar-widget" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 20px; padding: 30px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
            <h5 style="font-family: var(--heading-font); font-weight: 800; font-size: 1.1rem; color: #0F172A; text-transform: uppercase; margin-bottom: 20px; border-bottom: 2px solid #F1F5F9; padding-bottom: 12px; letter-spacing: -0.01em;">
              Technical Support
            </h5>
            
            <div style="display: flex; gap: 16px; margin-bottom: 18px; align-items: flex-start;">
              <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(242, 91, 34, 0.1); color: var(--primary-orange); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.1rem;">
                <i class="fas fa-headset"></i>
              </div>
              <div>
                <div style="font-size: 0.78rem; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">CONSULTATION</div>
                <a href="mailto:<?= SITE_EMAIL ?>" style="font-size: 0.95rem; font-weight: 700; color: #0F172A; text-decoration: none; word-break: break-all;"><?= SITE_EMAIL ?></a>
              </div>
            </div>
         
            <div style="display: flex; gap: 16px; margin-bottom: 24px; align-items: flex-start;">
              <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(242, 91, 34, 0.1); color: var(--primary-orange); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.1rem;">
                <i class="fas fa-building"></i>
              </div>
              <div>
                <div style="font-size: 0.78rem; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">USA OFFICE</div>
                <div style="font-size: 0.88rem; color: #334155; line-height: 1.5;"><?= SITE_ADDRESS_US ?></div>
              </div>
            </div>
            
            <a href="<?= url('talk-to-us.php') ?>" style="display: block; width: 100%; text-align: center; border: 1.5px solid #0F172A; color: #0F172A; padding: 12px; border-radius: 50px; font-weight: 700; font-size: 0.9rem; text-decoration: none; transition: 0.3s;" onmouseover="this.style.background='#0F172A';this.style.color='#fff';" onmouseout="this.style.background='transparent';this.style.color='#0F172A';">
              Send a Message
            </a>
          </div>

          <!-- ASIC Service Links Widget -->
          <div class="sidebar-widget" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 20px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
            <h5 style="font-family: var(--heading-font); font-weight: 800; font-size: 1.05rem; color: #0F172A; text-transform: uppercase; margin-bottom: 16px; border-bottom: 2px solid #F1F5F9; padding-bottom: 12px; letter-spacing: -0.01em;">
              Design Service
            </h5>
            <ul style="list-style: none; padding: 0; margin: 0 0 24px 0; display: flex; flex-direction: column; gap: 12px;">
              <li><a href="<?= url('business.php#spec-gdsii') ?>" style="color: #475569; font-size: 0.92rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 10px;"><i class="fas fa-file-invoice" style="color: var(--primary-orange); font-size: 0.85rem;"></i> Specification to GDSII</a></li>
              <li><a href="<?= url('business.php#rtl-gdsii') ?>" style="color: #475569; font-size: 0.92rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 10px;"><i class="fas fa-microchip" style="color: var(--primary-orange); font-size: 0.85rem;"></i> RTL to GDSII</a></li>
              <li><a href="<?= url('business.php#netlist-gdsii') ?>" style="color: #475569; font-size: 0.92rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 10px;"><i class="fas fa-code" style="color: var(--primary-orange); font-size: 0.85rem;"></i> Netlist to GDSII</a></li>
            </ul>

            <h5 style="font-family: var(--heading-font); font-weight: 800; font-size: 1.05rem; color: #0F172A; text-transform: uppercase; margin-bottom: 16px; border-bottom: 2px solid #F1F5F9; padding-bottom: 12px; letter-spacing: -0.01em;">
              IP Service
            </h5>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">
              <li><a href="<?= url('business.php#ip-development') ?>" style="color: #475569; font-size: 0.92rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 10px;"><i class="fas fa-file-invoice" style="color: var(--primary-orange); font-size: 0.85rem;"></i> IP Development</a></li>
              <li><a href="<?= url('business.php#circuit-design') ?>" style="color: #475569; font-size: 0.92rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 10px;"><i class="fas fa-microchip" style="color: var(--primary-orange); font-size: 0.85rem;"></i> Circuit Design</a></li>
              <li><a href="<?= url('business.php#custom-layout') ?>" style="color: #475569; font-size: 0.92rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 10px;"><i class="fas fa-code" style="color: var(--primary-orange); font-size: 0.85rem;"></i> Custom Layout</a></li>
            </ul>
          </div>

          <!-- Banner Section: SIDEBAR -->
          <div class="sidebar-cta-banner" style="background: linear-gradient(135deg, #0A192F 0%, #062A4D 100%); border: 1px solid rgba(0, 210, 255, 0.25); border-radius: 20px; padding: 32px 26px; color: #FFFFFF; text-align: center; margin-top: 30px; box-shadow: 0 12px 35px rgba(0,0,0,0.12);">
            <div style="width: 52px; height: 52px; border-radius: 50%; background: var(--primary-orange); display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; font-size: 1.3rem; color: #FFF; box-shadow: 0 0 20px var(--primary-orange-glow);">
              <i class="fa-solid fa-microchip"></i>
            </div>
            <h4 style="font-family: var(--heading-font); font-weight: 800; font-size: 1.15rem; color: #FFFFFF; margin-bottom: 12px; text-transform: uppercase; letter-spacing: -0.01em;">
              NEED SPECIALIZED IC DESIGN SUPPORT?
            </h4>
            <p style="font-size: 0.88rem; color: #CBD5E1; margin-bottom: 22px; line-height: 1.6;">
              Collaborate directly with our technical fellows and physical implementation architects from 65nm down to 3nm FinFET.
            </p>
            <a href="<?= url('talk-to-us.php') ?>" style="display: inline-flex; align-items: center; gap: 8px; background: var(--primary-orange); color: #FFFFFF; font-weight: 700; font-size: 0.92rem; padding: 12px 28px; border-radius: 50px; text-decoration: none; transition: 0.3s; box-shadow: 0 4px 18px var(--primary-orange-glow);">
              Talk to our experts <i class="fa-solid fa-arrow-right"></i>
            </a>
          </div>

        </div>
      </aside>
