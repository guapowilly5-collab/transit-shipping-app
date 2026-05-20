// ══════════════════════════════════════
//   LOME MARINE — main.js
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

// ─── FORMULAIRE CONTACT ───
const contactForm = document.getElementById('contactForm');
if (contactForm) {
  contactForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('.btn-submit');
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

// ─── PAGE LOGIN : onglets rôles ───
window.setRole = function(btn, role) {
  document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  const emailInput = document.getElementById('loginEmail');
  if (emailInput) {
    emailInput.placeholder = role === 'admin'
      ? 'admin@lomemarine.com'
      : 'email@exemple.com';
  }
};

// ─── PAGE LOGIN : soumission formulaire ───
const loginForm = document.getElementById('loginForm');
if (loginForm) {
  loginForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn       = this.querySelector('.btn-login');
    const alertBox  = document.getElementById('loginAlert');
    const email     = document.getElementById('loginEmail').value.trim();
    const password  = document.getElementById('loginPassword').value.trim();

    if (!email || !password) {
      if (alertBox) {
        alertBox.textContent = 'Veuillez remplir tous les champs.';
        alertBox.classList.add('show');
      }
      return;
    }

    btn.textContent = 'Connexion en cours...';
    btn.disabled = true;
    if (alertBox) alertBox.classList.remove('show');

    // Simulation — à remplacer par appel API PHP
    setTimeout(() => {
      btn.textContent = 'Se connecter';
      btn.disabled = false;
      // window.location.href = 'dashboard.html';
    }, 2000);
  });
}

// ─── PAGE REGISTER : soumission formulaire ───
const registerForm = document.getElementById('registerForm');
if (registerForm) {
  registerForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn      = this.querySelector('.btn-register');
    const alertBox = document.getElementById('registerAlert');

    btn.textContent = 'Envoi en cours...';
    btn.disabled = true;
    if (alertBox) alertBox.classList.remove('show');

    // Simulation — à remplacer par appel API PHP
    setTimeout(() => {
      btn.textContent = 'Demande envoyée ✓';
      btn.style.background = 'linear-gradient(135deg, var(--teal), var(--sea))';
      // window.location.href = 'login.html';
    }, 2000);
  });
}// ─── PAGE REGISTER V2 : onglets type de compte ───
window.switchRegTab = function(btn, type) {
  document.querySelectorAll('.reg-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');

  const vesselFields = document.getElementById('vesselFields');
  const agentFields  = document.getElementById('agentFields');
  if (!vesselFields || !agentFields) return;

  if (type === 'vessel') {
    vesselFields.classList.remove('hidden');
    agentFields.classList.add('hidden');
  } else {
    vesselFields.classList.add('hidden');
    agentFields.classList.remove('hidden');
  }
};

// ─── PAGE REGISTER V2 : navigation entre étapes ───
let currentRegStep = 1;

window.goStep = function(step) {
  if (step > currentRegStep && !validateRegStep(currentRegStep)) return;

  // Masquer l'étape actuelle, afficher la nouvelle
  document.querySelectorAll('.reg-step-content').forEach(s => s.classList.remove('active'));
  const target = document.getElementById('step' + step);
  if (target) target.classList.add('active');

  // Mettre à jour les indicateurs d'étapes
  document.querySelectorAll('.reg-step').forEach((el, i) => {
    el.classList.remove('active', 'done');
    if (i + 1 < step)       el.classList.add('done');
    else if (i + 1 === step) el.classList.add('active');
  });

  currentRegStep = step;
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

// ─── PAGE REGISTER V2 : validation par étape ───
function validateRegStep(step) {
  if (step === 1) {
    const email   = document.getElementById('regEmail');
    const pwd     = document.getElementById('regPassword');
    const confirm = document.getElementById('regConfirm');

    if (!email || !email.value.trim().includes('@')) {
      highlightField(email, 'Veuillez entrer un e-mail valide.');
      return false;
    }
    if (!pwd || pwd.value.length < 6) {
      highlightField(pwd, 'Le mot de passe doit contenir au moins 6 caractères.');
      return false;
    }
    if (!confirm || confirm.value !== pwd.value) {
      highlightField(confirm, 'Les mots de passe ne correspondent pas.');
      return false;
    }
  }

  if (step === 2) {
    const homePort = document.getElementById('homePort');
    if (!homePort || !homePort.value) {
      highlightField(homePort, "Veuillez sélectionner un port d'escale.");
      return false;
    }
  }

  return true;
}

function highlightField(el, msg) {
  if (!el) return;
  el.style.borderColor = '#e07b54'; // var(--coral)
  el.focus();

  // Supprimer ancien message d'erreur si présent
  const parent = el.closest('.reg-input-icon') || el.parentElement;
  const existing = parent.parentElement.querySelector('.field-error');
  if (existing) existing.remove();

  const err = document.createElement('small');
  err.className = 'field-error';
  err.style.cssText = 'color:#e07b54; font-size:0.75rem; display:block; margin-top:0.3rem;';
  err.textContent = msg;
  parent.parentElement.appendChild(err);

  setTimeout(() => {
    el.style.borderColor = '';
    err.remove();
  }, 3000);
}

// ─── PAGE REGISTER V2 : toggle visibilité mot de passe ───
window.togglePassword = function(id, btn) {
  const input = document.getElementById(id);
  if (!input) return;
  if (input.type === 'password') {
    input.type = 'text';
    btn.textContent = '🙈';
  } else {
    input.type = 'password';
    btn.textContent = '👁';
  }
};

// ─── PAGE REGISTER V2 : indicateur de force du mot de passe ───
const regPwdInput = document.getElementById('regPassword');
if (regPwdInput) {
  regPwdInput.addEventListener('input', function() {
    const val = this.value;
    const bar = document.getElementById('pwdBar');
    const lbl = document.getElementById('pwdLabel');
    if (!bar || !lbl) return;

    let score = 0;
    if (val.length >= 6)           score++;
    if (val.length >= 10)          score++;
    if (/[A-Z]/.test(val))         score++;
    if (/[0-9]/.test(val))         score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const widths = ['0%', '20%', '45%', '65%', '85%', '100%'];
    const colors = ['', '#e07b54', '#e8a838', '#e8a838', '#1abc9c', '#0e7f74'];
    const labels = ['', 'Très faible', 'Faible', 'Moyen', 'Fort', 'Très fort'];

    bar.style.width      = widths[score];
    bar.style.background = colors[score];
    lbl.textContent      = labels[score];
  });
}

// ─── PAGE REGISTER V2 : affichage nom de fichier uploadé ───
window.showFileName = function(input, labelId) {
  const label = document.getElementById(labelId);
  const zone  = input.closest('.reg-doc-zone');
  if (input.files && input.files[0] && label) {
    label.textContent = '✅ ' + input.files[0].name;
    if (zone) zone.classList.add('uploaded');
  }
};

// ─── PAGE REGISTER V2 : soumission finale ───
const registerFormV2 = document.getElementById('registerForm');
if (registerFormV2) {
  registerFormV2.addEventListener('submit', function(e) {
    e.preventDefault();

    const terms = document.getElementById('acceptTerms');
    if (!terms || !terms.checked) {
      alert("Veuillez accepter les conditions générales d'utilisation.");
      return;
    }

    const submitBtn  = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const spinner    = document.getElementById('submitSpinner');

    if (submitBtn) submitBtn.disabled = true;
    if (submitText) submitText.classList.add('hidden');
    if (spinner) spinner.classList.remove('hidden');

    // Simulation envoi — à remplacer par fetch() vers l'API PHP
    setTimeout(() => {
      registerFormV2.classList.add('hidden');
      const success = document.getElementById('regSuccess');
      if (success) success.classList.remove('hidden');
    }, 1800);
  });
}// ─── PAGE LOGIN : onglets rôles avec changement visuel ───
window.setRole = function(btn, role) {
  document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');

  const emailInput    = document.getElementById('loginEmail');
  const passwordInput = document.getElementById('loginPassword');
  const loginBtn      = document.getElementById('loginBtn');
  const roleLabel     = document.getElementById('roleLabel');
  const formPanel     = document.querySelector('.login-form-panel');
  const loginTitle    = document.querySelector('.login-form-panel h3');
  const loginSub      = document.querySelector('.login-form-subtitle');

  if (role === 'admin') {
    // Changer les placeholders
    if (emailInput)    emailInput.placeholder    = 'admin@lomemarine.com';
    if (passwordInput) passwordInput.placeholder = 'Mot de passe admin';

    // Changer le titre et sous-titre
    if (loginTitle) loginTitle.textContent = 'Connexion';
    if (loginSub)   loginSub.textContent   = 'Accès réservé au personnel autorisé';

    // Changer le label du bouton
    if (loginBtn) loginBtn.textContent = 'Accéder au back-office';

    // Ajouter classe admin au panneau
    if (formPanel) formPanel.classList.add('admin-mode');

    // Changer le label du rôle si présent
    if (roleLabel) roleLabel.textContent = 'Administration';

  } else {
    // Revenir à Navire / Agent
    if (emailInput)    emailInput.placeholder    = 'email@exemple.com';
    if (passwordInput) passwordInput.placeholder = '••••••••';

    if (loginTitle) loginTitle.textContent = 'Connexion';
    if (loginSub)   loginSub.textContent   = 'Accédez à votre espace personnel';

    if (loginBtn) loginBtn.textContent = 'Se connecter';

    if (formPanel) formPanel.classList.remove('admin-mode');

    if (roleLabel) roleLabel.textContent = 'Navire / Agent';
  }
};