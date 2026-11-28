/**
 * VNCHIP Technology JSC - Main Navigation & Global Scripts
 * Ported & Enhanced navigation logic from vnchips_old
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. STICKY NAVBAR SCROLL EFFECT (Ported from vnchips_old)
  const nav = document.getElementById('mainNav') || document.querySelector('.site-header');
  const handleScroll = () => {
    if (!nav) return;
    if (window.scrollY > 40) {
      nav.classList.add('scrolled');
    } else {
      nav.classList.remove('scrolled');
    }
  };
  window.addEventListener('scroll', handleScroll);
  handleScroll();

  // 3. MOBILE MENU TOGGLER & ACCORDION (Ported from vnchips_old)
  const navToggler = document.getElementById('navToggler') || document.querySelector('.navbar-toggler');
  const navCollapse = document.getElementById('vnchipNav') || document.querySelector('.navbar-collapse');

  if (navToggler && navCollapse) {
    navToggler.addEventListener('click', (e) => {
      e.stopPropagation();
      const isCollapsed = navToggler.classList.contains('collapsed');
      if (isCollapsed) {
        navToggler.classList.remove('collapsed');
        navCollapse.classList.add('show', 'open');
      } else {
        navToggler.classList.add('collapsed');
        navCollapse.classList.remove('show', 'open');
      }
    });

    // Close mobile drawer when clicking outside
    document.addEventListener('click', (e) => {
      if (window.innerWidth < 992 && !navCollapse.contains(e.target) && !navToggler.contains(e.target)) {
        navToggler.classList.add('collapsed');
        navCollapse.classList.remove('show', 'open');
      }
    });
  }

  // 4. MOBILE ACCORDION DROPDOWN HANDLER
  const setupMobileDropdowns = () => {
    if (window.innerWidth < 992) {
      const dropdowns = document.querySelectorAll('.navbar .nav-item.dropdown');
      dropdowns.forEach(dd => {
        const link = dd.querySelector('.dropdown-toggle');
        const menu = dd.querySelector('.dropdown-menu');
        
        if (link && menu && !link.dataset.hasMobileListener) {
          link.dataset.hasMobileListener = 'true';
          link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const isOpen = menu.classList.contains('show');

            // Close other open submenus in mobile drawer
            document.querySelectorAll('.navbar .dropdown-menu.show').forEach(m => {
              if (m !== menu) m.classList.remove('show', 'open');
            });
            document.querySelectorAll('.navbar .dropdown-toggle.active-toggle').forEach(t => {
              if (t !== link) t.classList.remove('active-toggle');
            });

            // Toggle current submenu
            if (!isOpen) {
              menu.classList.add('show', 'open');
              link.classList.add('active-toggle');
            } else {
              menu.classList.remove('show', 'open');
              link.classList.remove('active-toggle');
            }
          });
        }
      });
    }
  };

  setupMobileDropdowns();
  window.addEventListener('resize', setupMobileDropdowns);

  // 4b. DESKTOP MEGA MENU & DROPDOWN HOVER HANDLER
  let desktopDropdownsInitialized = false;
  const setupDesktopDropdowns = () => {
    if (desktopDropdownsInitialized) return;
    desktopDropdownsInitialized = true;

    const dropdownItems = document.querySelectorAll('.navbar .nav-item.dropdown');
    dropdownItems.forEach(dd => {
      let timeout;
      const menu = dd.querySelector('.dropdown-menu');
      if (!menu) return;

      const show = () => {
        if (window.innerWidth < 992) return;
        clearTimeout(timeout);
        document.querySelectorAll('.navbar .dropdown-menu.active-desktop').forEach(m => {
          if (m !== menu) {
            m.classList.remove('active-desktop');
            m.style.visibility = '';
            m.style.opacity = '';
            m.style.pointerEvents = '';
            m.style.display = '';
          }
        });
        menu.classList.add('active-desktop');
        menu.style.display = 'block';
        menu.style.visibility = 'visible';
        menu.style.opacity = '1';
        menu.style.pointerEvents = 'auto';
      };

      const hide = () => {
        if (window.innerWidth < 992) return;
        timeout = setTimeout(() => {
          menu.classList.remove('active-desktop');
          menu.style.visibility = '';
          menu.style.opacity = '';
          menu.style.pointerEvents = '';
          menu.style.display = '';
        }, 120);
      };

      dd.addEventListener('mouseenter', show);
      dd.addEventListener('mouseleave', hide);
      menu.addEventListener('mouseenter', show);
      menu.addEventListener('mouseleave', hide);
    });

    // Dismiss any active dropdown when moving to non-dropdown nav items or logo
    document.querySelectorAll('.navbar .nav-item:not(.dropdown), .navbar-brand').forEach(el => {
      el.addEventListener('mouseenter', () => {
        if (window.innerWidth >= 992) {
          document.querySelectorAll('.navbar .dropdown-menu.active-desktop').forEach(m => {
            m.classList.remove('active-desktop');
            m.style.visibility = '';
            m.style.opacity = '';
            m.style.pointerEvents = '';
            m.style.display = '';
          });
        }
      });
    });
  };

  setupDesktopDropdowns();

  // 4c. MEGA MENU 3RD LEVEL TOGGLES (e.g. Business > Design Service > sub-items)
  document.querySelectorAll('.mega-sub-toggle').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const item = btn.closest('.mega-has-sub');
      const isOpen = item.classList.toggle('open');
      btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  });

  // 5. SERVICE ACCORDION TOGGLES (Index & Business Page)
  const serviceItems = document.querySelectorAll('.service-item');
  serviceItems.forEach(item => {
    const header = item.querySelector('.service-header');
    if (header) {
      header.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const wasActive = item.classList.contains('active');

        // Close other items in the same accordion group
        const parentAccordion = item.closest('.service-accordion');
        if (parentAccordion) {
          parentAccordion.querySelectorAll('.service-item').forEach(sibling => {
            if (sibling !== item) {
              sibling.classList.remove('active');
            }
          });
        }

        item.classList.toggle('active', !wasActive);
      });
    }
  });

  // Auto-open and smooth scroll to accordion item if matched by URL hash
  const handleHashAccordion = () => {
    const hash = window.location.hash;
    if (hash) {
      try {
        const target = document.querySelector(hash);
        if (target && target.classList.contains('service-item')) {
          target.classList.add('active');
          const parentAccordion = target.closest('.service-accordion');
          if (parentAccordion) {
            parentAccordion.querySelectorAll('.service-item').forEach(sibling => {
              if (sibling !== target) sibling.classList.remove('active');
            });
          }
          setTimeout(() => {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }, 200);
        }
      } catch (err) {}
    }
  };
  handleHashAccordion();
  window.addEventListener('hashchange', handleHashAccordion);

  // 6. CAROUSEL HELPERS
  const setupCarousel = (prevBtnSel, nextBtnSel, trackSel) => {
    const prevBtn = document.querySelector(prevBtnSel);
    const nextBtn = document.querySelector(nextBtnSel);
    const track = document.querySelector(trackSel);

    if (prevBtn && nextBtn && track) {
      // Step = one card + gap when the track has cards, else a fixed 340px
      const step = () => {
        const card = track.firstElementChild;
        if (!card) return 340;
        const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
        return card.getBoundingClientRect().width + gap;
      };
      prevBtn.addEventListener('click', () => {
        track.scrollBy({ left: -step(), behavior: 'smooth' });
      });
      nextBtn.addEventListener('click', () => {
        track.scrollBy({ left: step(), behavior: 'smooth' });
      });
    }
  };

  setupCarousel('.partners-prev-btn', '.partners-next-btn', '.partners-carousel-track');
  setupCarousel('.media-prev-btn', '.media-next-btn', '.media-grid');
  setupCarousel('.biz-success-section .nav-arrow-btn[aria-label="Previous Slide"]', '.biz-success-section .nav-arrow-btn[aria-label="Next Slide"]', '.biz-success-grid');
  setupCarousel('.biz-rd-section .nav-arrow-btn[aria-label="Previous Project"]', '.biz-rd-section .nav-arrow-btn[aria-label="Next Project"]', '.biz-project-track');
  setupCarousel('.biz-values-section .nav-arrow-btn[aria-label="Previous Partner"]', '.biz-values-section .nav-arrow-btn[aria-label="Next Partner"]', '.biz-uni-grid');

  // 7. SCROLL REVEAL OBSERVER (Ported from vnchips_old)
  const reveals = document.querySelectorAll('.reveal');
  if (reveals.length > 0 && 'IntersectionObserver' in window) {
    const observerOptions = { threshold: 0.1 };
    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('active');
        }
      });
    }, observerOptions);
    reveals.forEach(el => revealObserver.observe(el));
  }

  // 8. HERO BACKGROUND VIDEO PLAYBACK
  const heroVideo = document.getElementById('heroBgVideo');
  if (heroVideo) {
    heroVideo.playbackRate = 0.85;
    const playPromise = heroVideo.play();
    if (playPromise !== undefined) {
      playPromise.catch(() => {
        heroVideo.muted = true;
        heroVideo.play().catch(() => {});
      });
    }
  }

  // 9. GLOBAL BOTTOM SHEET FOR INTERACTIVE ASIC KEYWORDS ON MOBILE (< 992px)
  let sheet = document.getElementById('global-bottom-sheet');
  if (!sheet) {
    sheet = document.createElement('div');
    sheet.id = 'global-bottom-sheet';
    sheet.innerHTML = `
      <div class="sheet-content" style="position: relative;">
        <div class="sheet-drag-handle"></div>
        <button type="button" class="sheet-close-btn" aria-label="Close" style="position: absolute; top: 16px; right: 20px; background: rgba(255,255,255,0.1); border: none; color: #FFFFFF; font-size: 1.4rem; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; line-height: 1;">&times;</button>
        <div class="sheet-body"></div>
      </div>
    `;
    document.body.appendChild(sheet);

    // Close bottom sheet when clicking overlay background or close button
    sheet.addEventListener('click', (e) => {
      if (!e.target.closest('.sheet-content') || e.target.closest('.sheet-close-btn')) {
        sheet.classList.remove('show');
        document.body.style.overflow = '';
      }
    });
  }

  // Listen for clicks on .tuong-tac keywords on mobile
  document.addEventListener('click', (e) => {
    const target = e.target.closest('.tuong-tac');
    if (target && window.innerWidth < 992) {
      e.preventDefault();
      e.stopPropagation();

      const tooltipContent = target.querySelector('.chu-thich');
      if (tooltipContent && sheet) {
        sheet.querySelector('.sheet-body').innerHTML = tooltipContent.innerHTML;
        sheet.classList.add('show');
        document.body.style.overflow = 'hidden';
      }
    }
  });

  // 10. INTERACTIVE KEYWORDS (.tuong-tac) DESKTOP TOOLTIP POSITIONING & BOUNDS CLAMPING
  const setupTuongTacTooltips = () => {
    const tuongTacElements = document.querySelectorAll('.tuong-tac');
    tuongTacElements.forEach(el => {
      const tooltip = el.querySelector('.chu-thich');
      if (!tooltip) return;

      el.addEventListener('mouseenter', () => {
        if (window.innerWidth >= 992) {
          // Reset default center alignment
          tooltip.style.left = '50%';
          tooltip.style.transform = 'translateX(-50%) translateY(0)';

          // Check bounding box against browser viewport
          const rect = tooltip.getBoundingClientRect();
          const margin = 24;

          if (rect.left < margin) {
            const shiftX = margin - rect.left;
            tooltip.style.transform = `translateX(calc(-50% + ${shiftX}px)) translateY(0)`;
          } else if (rect.right > window.innerWidth - margin) {
            const shiftX = rect.right - (window.innerWidth - margin);
            tooltip.style.transform = `translateX(calc(-50% - ${shiftX}px)) translateY(0)`;
          }
        }
      });

      el.addEventListener('mouseleave', () => {
        if (window.innerWidth >= 992) {
          tooltip.style.transform = '';
        }
      });
    });
  };

  setupTuongTacTooltips();

});


