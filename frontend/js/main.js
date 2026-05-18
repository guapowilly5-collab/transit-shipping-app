
Copier

// ─── MENU HAMBURGER MOBILE ───
const hamburger = document.getElementById('hamburger');
const navLinks  = document.getElementById('navLinks');
 
if (hamburger && navLinks) {
  hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('open');
  });
 
  // Ferme le menu quand on clique sur un lien
  navLinks.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      navLinks.classList.remove('open');
    });
  });
}
 
// ─── NAVBAR : fond au scroll ───
const nav = document.querySelector('nav');
window.addEventListener('scroll', () => {
  if (window.scrollY > 20) {
    nav.style.boxShadow = '0 2px 20px rgba(0,0,0,0.4)';
  } else {
    nav.style.boxShadow = 'none';
  }
});
 
// ─── SMOOTH SCROLL sur les ancres ───
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
});
 
// ─── FORMULAIRE : validation basique ───
const form = document.getElementById('contactForm');
if (form) {
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = form.querySelector('.btn-send');
    btn.textContent = 'Message envoyé ✓';
    btn.style.background = 'var(--teal-sea)';
    btn.disabled = true;
    setTimeout(() => {
      btn.textContent = 'Envoyer la demande';
      btn.style.background = '';
      btn.disabled = false;
      form.reset();
    }, 3000);
  });
}