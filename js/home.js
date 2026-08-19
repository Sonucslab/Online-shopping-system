/* home.js — Nexus Electronics Homepage Interactions */

// ===== CART COUNT SYNC =====
function updateCartBadge() {
  const cart = JSON.parse(localStorage.getItem('cart') || '[]');
  const count = cart.reduce((acc, item) => acc + (item.qty || 1), 0);
  const badge = document.getElementById('cart-count');
  if (badge) badge.textContent = count;
}
updateCartBadge();

// ===== PRODUCT TABS =====
const tabBtns = document.querySelectorAll('.tab-btn');
const productCards = document.querySelectorAll('.product-card');

tabBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    tabBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const tab = btn.dataset.tab;
    productCards.forEach(card => {
      const match = tab === 'all' || card.dataset.category === tab;
      card.style.display = match ? '' : 'none';
    });
  });
});

// ===== WISHLIST TOGGLE =====
document.querySelectorAll('.wishlist-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    btn.classList.toggle('active');
    const icon = btn.querySelector('i');
    if (btn.classList.contains('active')) {
      icon.classList.replace('fa-regular', 'fa-solid');
      icon.style.color = '#ef4444';
    } else {
      icon.classList.replace('fa-solid', 'fa-regular');
      icon.style.color = '';
    }
  });
});

// ===== DEAL COUNTDOWN TIMER =====
(function startCountdown() {
  // Set a target ~8 hours from now
  let target = localStorage.getItem('deal_target');
  if (!target || Date.now() > parseInt(target)) {
    target = Date.now() + 8 * 60 * 60 * 1000 + 34 * 60 * 1000 + 22 * 1000;
    localStorage.setItem('deal_target', target);
  }

  const hEl = document.getElementById('cd-hours');
  const mEl = document.getElementById('cd-mins');
  const sEl = document.getElementById('cd-secs');

  function tick() {
    const diff = Math.max(0, parseInt(target) - Date.now());
    const h = Math.floor(diff / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    if (hEl) hEl.textContent = String(h).padStart(2, '0');
    if (mEl) mEl.textContent = String(m).padStart(2, '0');
    if (sEl) sEl.textContent = String(s).padStart(2, '0');
    if (diff > 0) setTimeout(tick, 1000);
  }
  tick();
})();

// ===== NEWSLETTER =====
function handleNewsletter(e) {
  e.preventDefault();
  const input = document.getElementById('newsletter-email');
  const btn = document.getElementById('newsletter-submit');
  if (input && input.value) {
    btn.textContent = 'Subscribed!';
    btn.style.background = '#16a34a';
    input.value = '';
    setTimeout(() => {
      btn.textContent = 'Subscribe';
      btn.style.background = '';
    }, 3000);
  }
}

// ===== STICKY NAVBAR SHADOW =====
const navbar = document.getElementById('main-navbar');
window.addEventListener('scroll', () => {
  if (navbar) {
    navbar.style.boxShadow = window.scrollY > 10
      ? '0 2px 20px rgba(0,0,0,.10)'
      : '0 1px 0 #e5e7eb';
  }
}, { passive: true });

// ===== CART ADD FEEDBACK =====
// Extend addToCart if it exists to also animate the button
const _originalAddToCart = typeof addToCart === 'function' ? addToCart : null;
if (_originalAddToCart) {
  window.addToCart = function(id, name, price) {
    _originalAddToCart(id, name, price);
    updateCartBadge();
    // Brief visual feedback on the clicked button
    const btn = document.getElementById('add-cart-' + id);
    if (btn) {
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="fa-solid fa-check"></i> Added!';
      btn.style.background = '#16a34a';
      setTimeout(() => {
        btn.innerHTML = originalText;
        btn.style.background = '';
      }, 1400);
    }
  };
}
