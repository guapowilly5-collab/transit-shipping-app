/* ══════════════════════════════════════
   LOME MARINE — catalogue.js
   Filtres · Recherche · Panier · Toast
   Compatible : catalogue-public.html
                catalogue.html (connecté)
                dashboard catalogue
══════════════════════════════════════ */

'use strict';

/* ════════════════════════════
   FILTRE PAR CATÉGORIE
════════════════════════════ */
(function initFilters() {
  const filters  = document.querySelectorAll('.cat-filter');
  const sections = document.querySelectorAll('.cat-section');
  if (!filters.length) return;

  filters.forEach(btn => {
    btn.addEventListener('click', function () {
      filters.forEach(b => b.classList.remove('active'));
      this.classList.add('active');

      const cat = this.dataset.cat;

      sections.forEach(section => {
        const show = cat === 'all' || section.dataset.cat === cat;
        section.style.display = show ? '' : 'none';
      });

      updateResultsCount();

      // Scroll doux vers le top de la grille (mobile)
      const grid = document.getElementById('catGrid');
      if (grid && window.innerWidth <= 900) {
        grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
})();

/* ════════════════════════════
   RECHERCHE EN TEMPS RÉEL
════════════════════════════ */
(function initSearch() {
  const input = document.getElementById('catSearch');
  if (!input) return;

  let debounceTimer;

  input.addEventListener('input', function () {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      const query = this.value.toLowerCase().trim();
      let totalVisible = 0;

      document.querySelectorAll('.cat-card').forEach(card => {
        const text = (card.dataset.name || '' + card.textContent).toLowerCase();
        const match = !query || text.includes(query);
        card.style.display = match ? '' : 'none';
        if (match) totalVisible++;
      });

      // Masquer sections sans résultats
      document.querySelectorAll('.cat-section').forEach(section => {
        const hasVisible = section.querySelector('.cat-card:not([style*="display: none"]):not([style*="display:none"])');
        section.style.display = hasVisible ? '' : 'none';
      });

      // Message aucun résultat
      const noResults = document.getElementById('noResults');
      if (noResults) noResults.classList.toggle('hidden', totalVisible > 0 || !query);

      // Compteur
      updateResultsCount(totalVisible);

      // Réinitialiser les filtres actifs si recherche active
      if (query) {
        document.querySelectorAll('.cat-filter').forEach(f => f.classList.remove('active'));
        const allBtn = document.querySelector('.cat-filter[data-cat="all"]');
        if (allBtn) allBtn.classList.add('active');
      }
    }, 180);
  });
})();

/* ════════════════════════════
   COMPTEUR DE RÉSULTATS
════════════════════════════ */
function updateResultsCount(forceCount) {
  const counter = document.getElementById('resultsCount');
  if (!counter) return;

  const count = forceCount !== undefined
    ? forceCount
    : document.querySelectorAll('.cat-card:not([style*="display: none"]):not([style*="display:none"])').length;

  counter.textContent = `${count} produit${count !== 1 ? 's' : ''}`;
}

/* ════════════════════════════
   RÉINITIALISER LA RECHERCHE
════════════════════════════ */
window.resetSearch = function () {
  const input = document.getElementById('catSearch');
  if (input) input.value = '';

  document.querySelectorAll('.cat-card').forEach(card => card.style.display = '');
  document.querySelectorAll('.cat-section').forEach(s => s.style.display = '');
  document.getElementById('noResults')?.classList.add('hidden');

  document.querySelectorAll('.cat-filter').forEach(f => f.classList.remove('active'));
  const allBtn = document.querySelector('.cat-filter[data-cat="all"]');
  if (allBtn) allBtn.classList.add('active');

  updateResultsCount();
};

/* ════════════════════════════
   TOAST NOTIFICATION
════════════════════════════ */
(function initToast() {
  // Crée le conteneur toast si pas déjà présent
  if (document.getElementById('lm-toast')) return;
  const toast = document.createElement('div');
  toast.id = 'lm-toast';
  toast.style.cssText = `
    position: fixed;
    bottom: 90px;
    right: 24px;
    z-index: 1000;
    background: var(--navy-soft, #132d4f);
    border: 1px solid rgba(27,188,156,0.25);
    border-radius: 8px;
    padding: 0.75rem 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: 0.83rem;
    font-weight: 600;
    color: rgba(255,255,255,0.9);
    box-shadow: 0 8px 28px rgba(0,0,0,0.35);
    transform: translateY(20px);
    opacity: 0;
    transition: transform 0.28s cubic-bezier(0.4,0,0.2,1), opacity 0.28s;
    pointer-events: none;
    max-width: 280px;
  `;
  document.body.appendChild(toast);
})();

function showToast(icon, message) {
  const toast = document.getElementById('lm-toast');
  if (!toast) return;
  toast.innerHTML = `<span style="font-size:1rem;">${icon}</span><span>${message}</span>`;
  toast.style.transform = 'translateY(0)';
  toast.style.opacity = '1';
  clearTimeout(toast._timer);
  toast._timer = setTimeout(() => {
    toast.style.transform = 'translateY(20px)';
    toast.style.opacity = '0';
  }, 2400);
}

/* ════════════════════════════
   PANIER
   (uniquement pour le catalogue client connecté)
════════════════════════════ */
let cart = [];

// Charger depuis sessionStorage (pas localStorage : confidentialité des prix)
try {
  const saved = sessionStorage.getItem('lm-cart');
  if (saved) cart = JSON.parse(saved);
} catch (e) {
  cart = [];
}

/* ── Sauvegarder ── */
function saveCart() {
  try {
    sessionStorage.setItem('lm-cart', JSON.stringify(cart));
  } catch (e) { /* silencieux */ }
}

/* ── Ouvrir / fermer le panneau ── */
window.toggleCart = function () {
  const panel   = document.getElementById('cartPanel');
  const overlay = document.getElementById('cartOverlay');
  if (!panel) return;

  const willOpen = !panel.classList.contains('open');
  panel.classList.toggle('open', willOpen);
  if (overlay) overlay.classList.toggle('open', willOpen);

  // Dans le dashboard, bloquer le scroll sur dash-main ; sinon sur le body
  const scrollTarget = document.querySelector('.dash-main') || document.body;
  scrollTarget.style.overflow = willOpen ? 'hidden' : '';
};

/* ── Ajouter au panier ── */
window.addToCart = function (id, name, price, unit, icon) {
  const existing = cart.find(item => item.id === id);
  if (existing) {
    existing.qty++;
    showToast('➕', `Quantité mise à jour (×${existing.qty})`);
  } else {
    cart.push({ id, name, price, unit, icon: icon || '📦', qty: 1 });
    showToast('✓', `${name.slice(0, 30)}${name.length > 30 ? '…' : ''} ajouté`);
  }

  saveCart();
  renderCart();
  updateCartBadge();

  // Feedback visuel bouton
  const btn = document.querySelector(`[data-product-id="${id}"]`);
  if (btn) {
    const original = btn.textContent;
    btn.textContent = '✓ Ajouté !';
    btn.style.background = 'linear-gradient(135deg, #0b6b62, #0e5a85)';
    btn.disabled = true;
    setTimeout(() => {
      btn.textContent = original;
      btn.style.background = '';
      btn.disabled = false;
    }, 1600);
  }
};

/* ── Demande de devis (dashboard) ── */
window.requestQuote = function (productName) {
  showToast('📋', `Devis demandé : ${productName.slice(0, 28)}${productName.length > 28 ? '…' : ''}`);

  // Ici on ajouterait l'item en "mode devis" dans une liste séparée
  // Pour l'instant, feedback visuel uniquement
};

/* ── Changer quantité ── */
window.changeQty = function (id, delta) {
  const item = cart.find(i => i.id === id);
  if (!item) return;

  item.qty += delta;

  if (item.qty <= 0) {
    cart = cart.filter(i => i.id !== id);
    showToast('🗑️', 'Produit retiré du panier');
  }

  saveCart();
  renderCart();
  updateCartBadge();
};

/* ── Supprimer du panier ── */
window.removeFromCart = function (id) {
  cart = cart.filter(i => i.id !== id);
  saveCart();
  renderCart();
  updateCartBadge();
  showToast('🗑️', 'Produit retiré du panier');
};

/* ── Mettre à jour les badges ── */
function updateCartBadge() {
  const total = cart.reduce((sum, i) => sum + i.qty, 0);

  // Badge navbar bouton
  const badge = document.getElementById('cartCount');
  if (badge) {
    badge.textContent = total;
    badge.style.display = total > 0 ? 'flex' : 'none';
  }

  // Bouton flottant
  const floatBtn = document.getElementById('cartFloatBtn');
  if (floatBtn) {
    floatBtn.style.display = total > 0 ? 'flex' : 'none';
    floatBtn.innerHTML = `🛒 Panier <strong style="background:rgba(255,255,255,0.2);border-radius:10px;padding:1px 7px;font-size:0.8rem;margin-left:2px;">${total}</strong>`;
  }

  // Badge trigger dashboard
  const triggerCount = document.querySelector('.cat-cart-count');
  if (triggerCount) {
    triggerCount.textContent = total;
    triggerCount.style.display = total > 0 ? 'inline-flex' : 'none';
  }
}

/* ── Rendu du panier ── */
function renderCart() {
  const itemsEl = document.getElementById('cartItems');
  const totalEl = document.getElementById('cartTotal') || document.getElementById('cartItemCount');
  if (!itemsEl) return;

  if (cart.length === 0) {
    itemsEl.innerHTML = `
      <div class="cat-cart-empty">
        <span>🛒</span>
        <p>Votre panier est vide.<br/>Ajoutez des produits pour passer commande.</p>
      </div>`;
    if (totalEl) {
      totalEl.textContent = totalEl.id === 'cartItemCount' ? '0 article' : '—';
    }
    return;
  }

  itemsEl.innerHTML = cart.map(item => `
    <div class="cat-cart-item">
      <div class="cat-cart-item-icon">${item.icon}</div>
      <div class="cat-cart-item-info">
        <div class="cat-cart-item-name">${escapeHtml(item.name)}</div>
        <div class="cat-cart-item-price">${item.price !== '—' ? item.price + ' $ / ' + item.unit : 'Sur devis'}</div>
      </div>
      <div class="cat-cart-item-qty">
        <button class="cat-cart-qty-btn" onclick="changeQty('${escapeAttr(item.id)}', -1)" aria-label="Diminuer">−</button>
        <span class="cat-cart-qty-val">${item.qty}</span>
        <button class="cat-cart-qty-btn" onclick="changeQty('${escapeAttr(item.id)}', 1)" aria-label="Augmenter">+</button>
      </div>
      <button class="cat-cart-item-remove" onclick="removeFromCart('${escapeAttr(item.id)}')" aria-label="Supprimer">✕</button>
    </div>
  `).join('');

  // Calcul total (items avec prix connu)
  const knownTotal = cart.reduce((sum, i) => {
    const p = parseFloat(i.price);
    return sum + (isNaN(p) ? 0 : p * i.qty);
  }, 0);

  const hasSurDevis = cart.some(i => isNaN(parseFloat(i.price)));

  if (totalEl) {
    if (totalEl.id === 'cartItemCount') {
      const count = cart.reduce((sum, i) => sum + i.qty, 0);
      totalEl.textContent = `${count} article${count > 1 ? 's' : ''}`;
    } else {
      totalEl.textContent = knownTotal > 0
        ? `${knownTotal.toFixed(2)} $${hasSurDevis ? ' + devis' : ''}`
        : 'Sur devis';
    }
  }
}

/* ── Passer commande ── */
window.checkout = function () {
  if (cart.length === 0) {
    showToast('⚠️', 'Votre panier est vide');
    return;
  }
  window.location.href = 'passer-commande.html';
};

/* ── Envoyer demande de devis (dashboard) ── */
window.sendQuoteRequest = function () {
  if (cart.length === 0) {
    showToast('⚠️', 'Sélectionnez au moins un produit');
    return;
  }
  showToast('📨', 'Demande envoyée — un agent vous contacte');
  cart = [];
  saveCart();
  renderCart();
  updateCartBadge();
  // Fermer le panneau
  const scrollTarget = document.querySelector('.dash-main') || document.body;
  scrollTarget.style.overflow = '';
  document.getElementById('cartPanel')?.classList.remove('open');
  document.getElementById('cartOverlay')?.classList.remove('open');
};

/* ════════════════════════════
   UTILITAIRES XSS
════════════════════════════ */
function escapeHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function escapeAttr(str) {
  return String(str).replace(/'/g, "\\'");
}

/* ════════════════════════════
   FERMER PANIER AVEC ECHAP
════════════════════════════ */
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    const panel = document.getElementById('cartPanel');
    if (panel && panel.classList.contains('open')) {
      window.toggleCart();
    }
  }
});

/* ════════════════════════════
   INITIALISATION AU CHARGEMENT
════════════════════════════ */
document.addEventListener('DOMContentLoaded', function () {
  renderCart();
  updateCartBadge();
  updateResultsCount();
});