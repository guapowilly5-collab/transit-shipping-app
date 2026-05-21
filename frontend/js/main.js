// ══════════════════════════════════════
//   LOME MARINE — main.js  (version propre)
// ══════════════════════════════════════

// ─── NAVBAR : scroll effet ───
const navbar = document.querySelector('.navbar');
if (navbar) {
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 20);
  });
}

// ─── NAVBAR : menu hamburger mobile ───
const hamburger = document.getElementById('hamburger');
const navLinks  = document.getElementById('navLinks');
if (hamburger && navLinks) {
  hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('open');
  });
  navLinks.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => navLinks.classList.remove('open'));
  });
}

// ─── SMOOTH SCROLL ───
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
});

// ─── FORMULAIRE CONTACT (index.html) ───
const contactForm = document.getElementById('contactForm');
if (contactForm) {
  contactForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('.btn-submit');
    if (!btn) return;
    btn.textContent = 'Message envoyé ✓';
    btn.style.background = 'linear-gradient(135deg, var(--teal), var(--sea))';
    btn.disabled = true;
    setTimeout(() => {
      btn.textContent = 'Envoyer la demande';
      btn.style.background = '';
      btn.disabled = false;
      this.reset();
    }, 3000);
  });
}

// ─── PAGE LOGIN : soumission ───
const loginForm = document.getElementById('loginForm');
if (loginForm) {
  loginForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn      = document.getElementById('loginBtn') || this.querySelector('.btn-login');
    const alertBox = document.getElementById('loginAlert');
    const email    = document.getElementById('loginEmail');
    const password = document.getElementById('loginPassword');

    if (!email || !email.value.trim() || !password || !password.value.trim()) {
      if (alertBox) { alertBox.textContent = 'Veuillez remplir tous les champs.'; alertBox.classList.add('show'); }
      return;
    }
    if (!email.value.trim().includes('@')) {
      if (alertBox) { alertBox.textContent = 'Veuillez saisir une adresse e-mail valide.'; alertBox.classList.add('show'); }
      return;
    }

    if (btn)      { btn.textContent = 'Connexion en cours...'; btn.disabled = true; }
    if (alertBox)   alertBox.classList.remove('show');

    // Simulation — à remplacer par fetch() vers API PHP
    setTimeout(() => {
      if (btn) { btn.textContent = 'Se connecter'; btn.disabled = false; }
      // Décommenter selon le rôle retourné par l'API :
      // window.location.href = 'dashboard.html';    // client validé
      // showPendingAlert();                          // client en attente
    }, 1500);
  });
}

// ─── DASHBOARD : toggle sidebar mobile ───
window.toggleSidebar = function() {
  const sidebar = document.getElementById('dashSidebar');
  if (sidebar) sidebar.classList.toggle('open');
};

// ─── DASHBOARD : dark / light mode ───
window.toggleTheme = function() {
  const html   = document.documentElement;
  const btn    = document.getElementById('themeBtn');
  const isDark = html.getAttribute('data-theme') === 'dark';
  if (isDark) {
    html.removeAttribute('data-theme');
    localStorage.setItem('dash-theme', 'light');
    if (btn) { btn.textContent = '🌙'; btn.title = 'Passer en mode sombre'; }
  } else {
    html.setAttribute('data-theme', 'dark');
    localStorage.setItem('dash-theme', 'dark');
    if (btn) { btn.textContent = '☀️'; btn.title = 'Passer en mode clair'; }
  }
};

// Appliquer thème sauvegardé
(function applyTheme() {
  const saved = localStorage.getItem('dash-theme');
  const btn   = document.getElementById('themeBtn');
  if (saved === 'dark') {
    document.documentElement.setAttribute('data-theme', 'dark');
    if (btn) { btn.textContent = '☀️'; btn.title = 'Passer en mode clair'; }
  } else {
    if (btn) { btn.textContent = '🌙'; btn.title = 'Passer en mode sombre'; }
  }
})();

// ─── DASHBOARD : dropdown langue ───
window.toggleLangDropdown = function() {
  const dropdown = document.getElementById('langDropdown');
  if (dropdown) dropdown.classList.toggle('open');
};

document.addEventListener('click', function(e) {
  const wrapper = document.querySelector('.dash-lang-wrapper');
  if (wrapper && !wrapper.contains(e.target)) {
    const dropdown = document.getElementById('langDropdown');
    if (dropdown) dropdown.classList.remove('open');
  }
});

// ─── DASHBOARD : traductions FR/EN ───
const translations = {
  fr: {
    pageTitle:'Tableau de bord', pageSub:'Bienvenue, Capitaine Martin · MV Atlantic Star · IMO 9876543',
    newOrder:'Nouvelle commande +',
    statsOrders:'Commandes en cours', statsFolders:'Dossiers ouverts',
    statsDocs:'Documents générés',    statsNextPort:'Prochaine escale',
    statsWeek:'+1 cette semaine',     statsMonth:'+2 ce mois',
    statsThisMonth:'Ce mois',         statsDays:'Dans 3 jours',
    recentOrders:'Commandes récentes', seeAll:'Voir tout →',
    colRef:'Référence', colType:'Type', colPort:'Port', colDate:'Date',
    colAmount:'Montant', colStatus:'Statut', colAction:'Action', btnSee:'Voir',
    statusProgress:'En cours', statusDelivered:'Livré',
    statusPending:'En attente',  statusCancelled:'Annulé',
    chartServices:'Dépenses par service', chartThisMonth:'Ce mois',
    chartMonths:'Commandes par mois',     chartLast6:'6 derniers mois',
    notifications:'Notifications', seeAllNotif:'Tout voir →',
    notif1Title:'Document manquant',  notif1Desc:'Le certificat de jauge du dossier LM-2024-0047 est requis.', notif1Time:'Il y a 2h',
    notif2Title:'Commande livrée',    notif2Desc:'LM-2024-0046 — Safety Equipment livré à Lagos.',            notif2Time:'Il y a 1 jour',
    notif3Title:'Facture disponible', notif3Desc:'La facture de LM-2024-0045 est prête à télécharger.',       notif3Time:'Il y a 3 jours',
    notif4Title:'Escale confirmée',   notif4Desc:'Votre escale à Lagos est confirmée pour le 21 Mai.',        notif4Time:'Il y a 4 jours',
    quickActions:'Actions rapides',
    quick1:'Commander provisions', quick2:'Demande bunker',  quick3:'Safety Equipment',
    quick4:'Pièces techniques',    quick5:'Télécharger B/L', quick6:'Ouvrir un dossier',
    navDashboard:'Tableau de bord', navCatalogue:'Catalogue',     navOrders:'Mes commandes',
    navFolders:'Mes dossiers',      navDocuments:'Mes documents', navInvoices:'Factures',
    navProfile:'Mon profil',        navNotifs:'Notifications',
    navGroupMain:'Principal',       navGroupDocs:'Documents',     navGroupAccount:'Compte',
    logout:'⏻ Déconnexion',
  },
  en: {
    pageTitle:'Dashboard', pageSub:'Welcome, Captain Martin · MV Atlantic Star · IMO 9876543',
    newOrder:'New order +',
    statsOrders:'Ongoing orders', statsFolders:'Open folders',
    statsDocs:'Generated documents', statsNextPort:'Next port call',
    statsWeek:'+1 this week',    statsMonth:'+2 this month',
    statsThisMonth:'This month', statsDays:'In 3 days',
    recentOrders:'Recent orders', seeAll:'See all →',
    colRef:'Reference', colType:'Type', colPort:'Port', colDate:'Date',
    colAmount:'Amount', colStatus:'Status', colAction:'Action', btnSee:'View',
    statusProgress:'In progress', statusDelivered:'Delivered',
    statusPending:'Pending',      statusCancelled:'Cancelled',
    chartServices:'Spending by service', chartThisMonth:'This month',
    chartMonths:'Orders per month',      chartLast6:'Last 6 months',
    notifications:'Notifications', seeAllNotif:'See all →',
    notif1Title:'Missing document',   notif1Desc:'The tonnage certificate for folder LM-2024-0047 is required.', notif1Time:'2 hours ago',
    notif2Title:'Order delivered',    notif2Desc:'LM-2024-0046 — Safety Equipment delivered in Lagos.',           notif2Time:'1 day ago',
    notif3Title:'Invoice available',  notif3Desc:'Invoice for LM-2024-0045 is ready to download.',                notif3Time:'3 days ago',
    notif4Title:'Port call confirmed',notif4Desc:'Your port call in Lagos is confirmed for May 21st.',            notif4Time:'4 days ago',
    quickActions:'Quick actions',
    quick1:'Order provisions', quick2:'Bunker request', quick3:'Safety Equipment',
    quick4:'Technical parts',  quick5:'Download B/L',   quick6:'Open a folder',
    navDashboard:'Dashboard', navCatalogue:'Catalogue',    navOrders:'My orders',
    navFolders:'My folders',  navDocuments:'My documents', navInvoices:'Invoices',
    navProfile:'My profile',  navNotifs:'Notifications',
    navGroupMain:'Main',      navGroupDocs:'Documents',    navGroupAccount:'Account',
    logout:'⏻ Logout',
  }
};

let currentLang = localStorage.getItem('dash-lang') || 'fr';

function setText(id, val) { const el = document.getElementById(id); if (el) el.textContent = val; }

function applyTranslations(lang) {
  const t   = translations[lang];
  const btn = document.getElementById('langBtn');
  if (btn) btn.innerHTML = lang === 'fr'
    ? '<span class="lang-flag">🇫🇷</span> FR ▾'
    : '<span class="lang-flag">🇬🇧</span> EN ▾';

  setText('dashPageTitle',t.pageTitle);  setText('dashPageSub',t.pageSub);    setText('dashNewOrder',t.newOrder);
  setText('statLabelOrders',t.statsOrders); setText('statLabelFolders',t.statsFolders);
  setText('statLabelDocs',t.statsDocs);     setText('statLabelNextPort',t.statsNextPort);
  setText('statTrendOrders',t.statsWeek);   setText('statTrendFolders',t.statsMonth);
  setText('statTrendDocs',t.statsThisMonth);setText('statTrendPort',t.statsDays);
  setText('colRef',t.colRef); setText('colType',t.colType); setText('colPort',t.colPort);
  setText('colDate',t.colDate); setText('colAmount',t.colAmount); setText('colStatus',t.colStatus);
  setText('colAction',t.colAction);
  setText('cardTitleOrders',t.recentOrders);  setText('cardLinkOrders',t.seeAll);
  setText('cardTitleServices',t.chartServices); setText('cardSubServices',t.chartThisMonth);
  setText('cardTitleNotifs',t.notifications);   setText('cardLinkNotifs',t.seeAllNotif);
  setText('cardTitleMonths',t.chartMonths);     setText('cardSubMonths',t.chartLast6);
  setText('cardTitleQuick',t.quickActions);
  setText('notif1Title',t.notif1Title); setText('notif1Desc',t.notif1Desc); setText('notif1Time',t.notif1Time);
  setText('notif2Title',t.notif2Title); setText('notif2Desc',t.notif2Desc); setText('notif2Time',t.notif2Time);
  setText('notif3Title',t.notif3Title); setText('notif3Desc',t.notif3Desc); setText('notif3Time',t.notif3Time);
  setText('notif4Title',t.notif4Title); setText('notif4Desc',t.notif4Desc); setText('notif4Time',t.notif4Time);
  setText('quick1',t.quick1); setText('quick2',t.quick2); setText('quick3',t.quick3);
  setText('quick4',t.quick4); setText('quick5',t.quick5); setText('quick6',t.quick6);
  setText('navDashboard',t.navDashboard); setText('navCatalogue',t.navCatalogue);
  setText('navOrders',t.navOrders);       setText('navFolders',t.navFolders);
  setText('navDocuments',t.navDocuments); setText('navInvoices',t.navInvoices);
  setText('navProfile',t.navProfile);     setText('navNotifs',t.navNotifs);
  setText('navGroupMain',t.navGroupMain); setText('navGroupDocs',t.navGroupDocs);
  setText('navGroupAccount',t.navGroupAccount); setText('dashLogout',t.logout);
  document.querySelectorAll('.dash-badge-progress').forEach(el  => el.textContent = t.statusProgress);
  document.querySelectorAll('.dash-badge-delivered').forEach(el => el.textContent = t.statusDelivered);
  document.querySelectorAll('.dash-badge-pending').forEach(el   => el.textContent = t.statusPending);
  document.querySelectorAll('.dash-badge-cancelled').forEach(el => el.textContent = t.statusCancelled);
  document.querySelectorAll('.dash-btn-sm').forEach(el          => el.textContent = t.btnSee);
}

window.setLang = function(lang) {
  currentLang = lang;
  localStorage.setItem('dash-lang', lang);
  applyTranslations(lang);
  document.querySelectorAll('.dash-lang-option').forEach(opt => {
    opt.classList.toggle('active', opt.dataset.lang === lang);
  });
  const dropdown = document.getElementById('langDropdown');
  if (dropdown) dropdown.classList.remove('open');
};

document.addEventListener('DOMContentLoaded', function() {
  const saved = localStorage.getItem('dash-lang') || 'fr';
  currentLang = saved;
  applyTranslations(saved);
  document.querySelectorAll('.dash-lang-option').forEach(opt => {
    opt.classList.toggle('active', opt.dataset.lang === saved);
  });
});