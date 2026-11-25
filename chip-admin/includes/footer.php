    </main>
  </div>
</div>

<!-- Floating Toast Container -->
<div class="admin-toast-container" id="adminToastContainer"></div>

<!-- Admin Scroll To Top Button -->
<button type="button" id="adminScrollTopBtn" class="admin-scroll-top-btn" aria-label="Lên đầu trang" title="Lên đầu trang (Scroll to Top)">
  <svg class="scroll-top-ring" viewBox="0 0 46 46" aria-hidden="true">
    <circle class="scroll-top-ring-bg" cx="23" cy="23" r="20"></circle>
    <circle class="scroll-top-ring-progress" id="adminScrollTopProgress" cx="23" cy="23" r="20"></circle>
  </svg>
  <i class="fa-solid fa-arrow-up"></i>
</button>

<style>
.admin-scroll-top-btn {
  position: fixed;
  right: 26px;
  bottom: 26px;
  z-index: 1020;
  width: 46px;
  height: 46px;
  border-radius: 50%;
  border: none;
  padding: 0;
  background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
  color: #FFFFFF;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 8px 22px rgba(15, 23, 42, 0.28);
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
  transform: translateY(14px);
  transition: opacity 0.25s ease, transform 0.25s ease, visibility 0.25s ease, background 0.2s ease, box-shadow 0.2s ease;
}
.admin-scroll-top-btn.is-visible {
  opacity: 1;
  visibility: visible;
  pointer-events: auto;
  transform: translateY(0);
}
.admin-scroll-top-btn:hover {
  background: linear-gradient(135deg, #0284C7 0%, #2563EB 100%);
  transform: translateY(-3px);
  box-shadow: 0 10px 26px rgba(2, 132, 199, 0.45);
}
.admin-scroll-top-btn i {
  position: relative;
  z-index: 2;
  font-size: 0.95rem;
}
.admin-scroll-top-btn .scroll-top-ring {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  transform: rotate(-90deg);
  pointer-events: none;
}
.admin-scroll-top-btn .scroll-top-ring-bg {
  fill: none;
  stroke: rgba(255, 255, 255, 0.14);
  stroke-width: 2.5;
}
.admin-scroll-top-btn .scroll-top-ring-progress {
  fill: none;
  stroke: #38BDF8;
  stroke-width: 2.5;
  stroke-linecap: round;
  stroke-dasharray: 125.66;
  stroke-dashoffset: 125.66;
  transition: stroke-dashoffset 0.08s linear;
}
@media (max-width: 576px) {
  .admin-scroll-top-btn {
    right: 16px;
    bottom: 18px;
    width: 42px;
    height: 42px;
  }
}
@media print {
  .admin-scroll-top-btn {
    display: none !important;
  }
}
</style>

<script>
// Mobile Sidebar Toggle
(function() {
  const btn = document.getElementById('mobileMenuBtn');
  const sidebar = document.querySelector('.admin-sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  
  if (btn && sidebar && overlay) {
    btn.addEventListener('click', function() {
      sidebar.classList.toggle('active');
      overlay.classList.toggle('active');
    });
    
    overlay.addEventListener('click', function() {
      sidebar.classList.remove('active');
      overlay.classList.remove('active');
    });
  }
})();

// Global Toast Notification Helper
function showAdminToast(message, type = 'info') {
  const container = document.getElementById('adminToastContainer');
  if (!container) return;
  
  const toast = document.createElement('div');
  toast.className = 'admin-toast';
  
  let icon = 'fa-circle-info';
  if (type === 'success') icon = 'fa-circle-check text-success';
  else if (type === 'error') icon = 'fa-circle-exclamation text-danger';
  else if (type === 'warning') icon = 'fa-triangle-exclamation text-warning';
  
  toast.innerHTML = `<i class="fa-solid ${icon}"></i> <span>${message}</span>`;
  container.appendChild(toast);
  
  setTimeout(() => {
    toast.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    setTimeout(() => toast.remove(), 400);
  }, 3500);
}

// Global Copy-To-Clipboard Helper
function copyToClipboard(text, successMsg = 'Link copied to clipboard!') {
  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard.writeText(text).then(() => {
      showAdminToast(successMsg, 'success');
    }).catch(() => {
      fallbackCopyText(text, successMsg);
    });
  } else {
    fallbackCopyText(text, successMsg);
  }
}

function fallbackCopyText(text, successMsg) {
  const textArea = document.createElement('textarea');
  textArea.value = text;
  textArea.style.position = 'fixed';
  textArea.style.left = '-9999px';
  document.body.appendChild(textArea);
  textArea.focus();
  textArea.select();
  try {
    document.execCommand('copy');
    showAdminToast(successMsg, 'success');
  } catch (err) {
    showAdminToast('Failed to copy text', 'error');
  }
  document.body.removeChild(textArea);
}

// Auto dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
  const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
  alerts.forEach(function(el) {
    setTimeout(function() {
      el.style.transition = 'opacity 0.5s ease';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 500);
    }, 6000);
  });
});

// Admin Scroll To Top Controller
(function() {
  const btn = document.getElementById('adminScrollTopBtn');
  const ring = document.getElementById('adminScrollTopProgress');
  if (!btn) return;
  const circumference = 2 * Math.PI * 20;
  let ticking = false;

  function updateScrollTop() {
    const scrollTop = window.scrollY || document.documentElement.scrollTop || 0;
    const docHeight = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
    const ratio = Math.min(1, Math.max(0, scrollTop / docHeight));

    if (scrollTop > 220) {
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
      window.requestAnimationFrame(updateScrollTop);
    }
  }, { passive: true });

  btn.addEventListener('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  updateScrollTop();
})();
</script>

</body>
</html>
