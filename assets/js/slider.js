/**
 * VNCHIP Technology JSC - Interactivity & Sliders
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Partners Carousel Navigation
  const partnerTrack = document.querySelector('.partners-carousel-track');
  const prevPartnerBtn = document.querySelector('.partners-prev-btn');
  const nextPartnerBtn = document.querySelector('.partners-next-btn');

  if (partnerTrack && prevPartnerBtn && nextPartnerBtn) {
    nextPartnerBtn.addEventListener('click', () => {
      partnerTrack.scrollBy({ left: 350, behavior: 'smooth' });
    });

    prevPartnerBtn.addEventListener('click', () => {
      partnerTrack.scrollBy({ left: -350, behavior: 'smooth' });
    });
  }

  // 3. Media Center Slider Navigation
  const mediaTrack = document.querySelector('.media-grid');
  const prevMediaBtn = document.querySelector('.media-prev-btn');
  const nextMediaBtn = document.querySelector('.media-next-btn');

  if (mediaTrack && prevMediaBtn && nextMediaBtn) {
    nextMediaBtn.addEventListener('click', () => {
      mediaTrack.scrollBy({ left: 300, behavior: 'smooth' });
    });

    prevMediaBtn.addEventListener('click', () => {
      mediaTrack.scrollBy({ left: -300, behavior: 'smooth' });
    });
  }
});
