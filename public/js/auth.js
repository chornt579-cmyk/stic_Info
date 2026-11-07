/**
 * auth.js — Authentication & routing for STIC Info
 * Handles login, registration, auth state changes, and logout.
 */

'use strict';

// ─── XSS Prevention ──────────────────────────────────────────────────────────
function escapeHtml(str) {
  if (typeof str !== 'string') return '';
  return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

// ─── Toast Notifications ──────────────────────────────────────────────────────
function showToast(message, type = 'info') {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const icons = { success: '✔', error: '✖', info: 'ℹ', warning: '⚠' };
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `<span class="toast-icon">${icons[type] || 'ℹ'}</span><span>${escapeHtml(message)}</span>`;
  container.appendChild(toast);
  setTimeout(() => { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 4200);
}

// ─── Global helpers ───────────────────────────────────────────────────────────
window.escapeHtml = escapeHtml;
window.showToast   = showToast;

// ─── State ────────────────────────────────────────────────────────────────────
let currentUser     = null;
let currentUserData = null;

// ─── Auth State Listener ──────────────────────────────────────────────────────
auth.onAuthStateChanged(async (firebaseUser) => {
  if (firebaseUser) {
    currentUser = firebaseUser;
    try {
      const doc = await db.collection('users').doc(firebaseUser.uid).get();
      if (!doc.exists) {
        // User authenticated but no Firestore record yet — handled during register
        hideLoading();
        return;
      }
      currentUserData = doc.data();
      window.currentUser     = currentUser;
      window.currentUserData = currentUserData;

      routeToDashboard(currentUserData);
    } catch (err) {
      console.error('Auth state error:', err);
      hideLoading();
      showToast('មានបញ្ហាក្នុងការផ្ទុក — ' + err.message, 'error');
    }
  } else {
    currentUser     = null;
    currentUserData = null;
    window.currentUser     = null;
    window.currentUserData = null;
    hideLoading();
    showAuthForm();
  }
});

function hideLoading() {
  const el = document.getElementById('loading-screen');
  if (el) el.style.display = 'none';
}

function showAuthForm() {
  const el = document.getElementById('auth-wrapper');
  if (el) el.style.display = 'flex';
}

function routeToDashboard(userData) {
  // Hide auth form
  const authEl = document.getElementById('auth-wrapper');
  if (authEl) authEl.style.display = 'none';

  // Hide pending screen
  const pendingEl = document.getElementById('pending-screen');
  if (pendingEl) pendingEl.style.display = 'none';

  if (userData.role === 'teacher' && userData.status === 'pending') {
    showPendingScreen(userData);
    return;
  }

  // Show main app layout
  const appEl = document.getElementById('app-layout');
  if (appEl) appEl.style.display = 'flex';

  // Update sidebar user info
  const nameEl = document.getElementById('sidebar-user-name');
  const roleEl = document.getElementById('sidebar-user-role');
  if (nameEl) nameEl.textContent = userData.name || userData.email;
  if (roleEl) {
    const roleLabels = { student: 'សិស្ស', teacher: 'គ្រូ', admin: 'អ្នកគ្រប់គ្រង' };
    roleEl.textContent = roleLabels[userData.role] || userData.role;
  }

  // Show role-appropriate sidebar sections
  document.querySelectorAll('[data-role]').forEach(el => {
    el.style.display = el.dataset.role.includes(userData.role) ? '' : 'none';
  });

  // Load correct dashboard
  if (userData.role === 'student' && typeof loadStudentDashboard === 'function') {
    loadStudentDashboard();
  } else if (userData.role === 'teacher' && typeof loadTeacherDashboard === 'function') {
    loadTeacherDashboard();
  } else if (userData.role === 'admin' && typeof loadAdminDashboard === 'function') {
    loadAdminDashboard();
  }
}

function showPendingScreen(userData) {
  const el = document.getElementById('pending-screen');
  if (!el) return;
  el.style.display = 'flex';
  const nameEl = el.querySelector('.pending-teacher-name');
  if (nameEl) nameEl.textContent = escapeHtml(userData.name || '');
}

// ─── Auth Tab Toggle ──────────────────────────────────────────────────────────
function setupAuthToggle() {
  const loginBtn    = document.getElementById('tab-login');
  const registerBtn = document.getElementById('tab-register');
  const loginForm   = document.getElementById('login-form');
  const registerForm= document.getElementById('register-form');

  if (!loginBtn || !registerBtn) return;

  loginBtn.addEventListener('click', () => {
    loginBtn.classList.add('active');
    registerBtn.classList.remove('active');
    loginForm.style.display = 'block';
    registerForm.style.display = 'none';
  });

  registerBtn.addEventListener('click', () => {
    registerBtn.classList.add('active');
    loginBtn.classList.remove('active');
    registerForm.style.display = 'block';
    loginForm.style.display = 'none';
  });
}

// ─── Department / Class selectors in register form ────────────────────────────
async function populateDepartments() {
  const deptSelects = document.querySelectorAll('.dept-select');
  if (!deptSelects.length) return;
  try {
    const snap = await db.collection('departments').orderBy('name').get();
    deptSelects.forEach(sel => {
      sel.innerHTML = '<option value="">-- ជ្រើសរើសដេប៉ាតឺម៉ង់ --</option>';
      snap.forEach(doc => {
        const opt = document.createElement('option');
        opt.value = doc.id;
        opt.textContent = escapeHtml(doc.data().name);
        sel.appendChild(opt);
      });
    });
  } catch (e) {
    console.error('Failed to load departments:', e);
  }
}

async function populateClassesByDept(deptId, classSelect) {
  if (!classSelect) return;
  classSelect.innerHTML = '<option value="">-- ជ្រើសរើសថ្នាក់ --</option>';
  if (!deptId) return;
  try {
    const snap = await db.collection('classes')
      .where('deptId', '==', deptId)
      .orderBy('name')
      .get();
    snap.forEach(doc => {
      const opt = document.createElement('option');
      opt.value = doc.id;
      opt.textContent = escapeHtml(doc.data().name);
      classSelect.appendChild(opt);
    });
  } catch (e) {
    console.error('Failed to load classes:', e);
  }
}

// ─── Login Form ───────────────────────────────────────────────────────────────
function setupLoginForm() {
  const form = document.getElementById('login-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email    = form.querySelector('#login-email').value.trim();
    const password = form.querySelector('#login-password').value;
    const btn      = form.querySelector('[type=submit]');

    if (!email || !password) {
      showToast('សូមបំពេញ Email និង Password', 'warning'); return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner spinner-sm"></span> កំពុងចូល...';

    try {
      await auth.signInWithEmailAndPassword(email, password);
      // onAuthStateChanged will handle routing
    } catch (err) {
      console.error('Login error:', err);
      let msg = 'ចូលគណនីបានបរាជ័យ';
      if (err.code === 'auth/user-not-found' || err.code === 'auth/wrong-password') {
        msg = 'Email ឬ Password មិនត្រូវ';
      } else if (err.code === 'auth/too-many-requests') {
        msg = 'ព្យាយាមចូលច្រើនពេក។ សូមព្យាយាមម្តងទៀតក្រោយ';
      }
      showToast(msg, 'error');
      btn.disabled = false;
      btn.innerHTML = 'ចូល / Login';
    }
  });
}

// ─── Register Form ────────────────────────────────────────────────────────────
function setupRegisterForm() {
  const form = document.getElementById('register-form');
  if (!form) return;

  const roleSelect  = form.querySelector('#reg-role');
  const studentExtra= form.querySelector('#student-extra');
  const teacherExtra= form.querySelector('#teacher-extra');
  const deptSelect  = form.querySelector('#reg-dept');
  const classSelect = form.querySelector('#reg-class');

  // Show/hide role-specific fields
  if (roleSelect) {
    roleSelect.addEventListener('change', () => {
      const role = roleSelect.value;
      if (studentExtra) studentExtra.style.display = role === 'student' ? 'block' : 'none';
      if (teacherExtra) teacherExtra.style.display = role === 'teacher' ? 'block' : 'none';
    });
  }

  // Auto-populate classes when dept changes (student)
  if (deptSelect && classSelect) {
    deptSelect.addEventListener('change', () => {
      populateClassesByDept(deptSelect.value, classSelect);
    });
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const name     = form.querySelector('#reg-name').value.trim();
    const phone    = form.querySelector('#reg-phone').value.trim();
    const email    = form.querySelector('#reg-email').value.trim();
    const password = form.querySelector('#reg-password').value;
    const role     = form.querySelector('#reg-role').value;

    // Validation
    if (!name || !email || !password || !role) {
      showToast('សូមបំពេញព័ត៌មានទាំងអស់', 'warning'); return;
    }
    if (password.length < 6) {
      showToast('Password ត្រូវតែ 6 តួអក្សរឡើង', 'warning'); return;
    }

    let classId = '';
    let deptId  = '';

    if (role === 'student') {
      classId = form.querySelector('#reg-class') ? form.querySelector('#reg-class').value : '';
      deptId  = form.querySelector('#reg-dept') ? form.querySelector('#reg-dept').value : '';
      if (!classId || !deptId) {
        showToast('សូមជ្រើសរើសដេប៉ាតឺម៉ង់ និងថ្នាក់', 'warning'); return;
      }
    }

    if (role === 'teacher') {
      deptId = form.querySelector('#reg-teacher-dept') ? form.querySelector('#reg-teacher-dept').value : '';
    }

    const btn = form.querySelector('[type=submit]');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner spinner-sm"></span> កំពុងចុះឈ្មោះ...';

    try {
      const cred = await auth.createUserWithEmailAndPassword(email, password);
      const uid  = cred.user.uid;

      const userData = {
        uid,
        name,
        phone: phone || '',
        email,
        role,
        status: role === 'teacher' ? 'pending' : 'active',
        createdAt: firebase.firestore.FieldValue.serverTimestamp()
      };

      if (role === 'student') {
        userData.classId = classId;
        userData.deptId  = deptId;
      }

      if (role === 'teacher') {
        userData.deptId        = deptId || '';
        userData.teacherClasses= [];
      }

      await db.collection('users').doc(uid).set(userData);
      // onAuthStateChanged fires automatically
    } catch (err) {
      console.error('Register error:', err);
      let msg = 'ចុះឈ្មោះបានបរាជ័យ';
      if (err.code === 'auth/email-already-in-use') {
        msg = 'Email នេះត្រូវបានប្រើរួចហើយ';
      } else if (err.code === 'auth/invalid-email') {
        msg = 'Email មិនត្រឹមត្រូវ';
      }
      showToast(msg, 'error');
      btn.disabled = false;
      btn.innerHTML = 'ចុះឈ្មោះ / Register';
    }
  });
}

// ─── Logout ───────────────────────────────────────────────────────────────────
function logout() {
  auth.signOut().then(() => {
    // Clear all dashboards
    const appEl = document.getElementById('app-layout');
    if (appEl) appEl.style.display = 'none';
    showToast('បានចាកចេញពីប្រព័ន្ធ', 'info');
  }).catch(err => {
    showToast('មានបញ្ហាក្នុងការចាកចេញ: ' + err.message, 'error');
  });
}

window.logout = logout;

// ─── Hamburger / Sidebar on mobile ────────────────────────────────────────────
function setupMobileSidebar() {
  const hamburger = document.getElementById('hamburger');
  const sidebar   = document.getElementById('sidebar');
  const overlay   = document.getElementById('sidebar-overlay');

  if (!hamburger || !sidebar) return;

  hamburger.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('active');
  });

  if (overlay) {
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('active');
    });
  }
}

// ─── Initialise on DOM ready ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  setupAuthToggle();
  setupLoginForm();
  setupRegisterForm();
  setupMobileSidebar();
  populateDepartments();
});
