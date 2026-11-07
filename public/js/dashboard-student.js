/**
 * dashboard-student.js — Student dashboard logic for STIC Info
 */

'use strict';

// Day of week mapping (JS Date.getDay() → Khmer + schedule key)
const DAYS = ['អាទិត្យ','ច័ន្ទ','អង្គារ','ពុធ','ព្រហស្បតិ៍','សុក្រ','សៅរ៍'];
const DAY_KEYS = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];

// ─── Entry Point ──────────────────────────────────────────────────────────────
async function loadStudentDashboard() {
  activateSidebarLink('nav-student-today');
  showSection('section-student-today');

  // Bind sidebar nav links
  document.getElementById('nav-student-today')?.addEventListener('click', () => {
    activateSidebarLink('nav-student-today');
    showSection('section-student-today');
    renderTodaySchedule();
  });
  document.getElementById('nav-student-week')?.addEventListener('click', () => {
    activateSidebarLink('nav-student-week');
    showSection('section-student-week');
    renderWeekSchedule();
  });
  document.getElementById('nav-student-ann')?.addEventListener('click', () => {
    activateSidebarLink('nav-student-ann');
    showSection('section-student-ann');
    renderStudentAnnouncements();
  });

  // Load initial section
  renderTodaySchedule();
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function activateSidebarLink(id) {
  document.querySelectorAll('.sidebar-link').forEach(el => el.classList.remove('active'));
  document.getElementById(id)?.classList.add('active');
}

function showSection(id) {
  document.querySelectorAll('.dashboard-section').forEach(el => el.style.display = 'none');
  const el = document.getElementById(id);
  if (el) el.style.display = 'block';
}

function setInnerHtml(id, html) {
  const el = document.getElementById(id);
  if (el) el.innerHTML = html;
}

// Fetch teacher display name safely
async function fetchTeacherName(teacherId) {
  if (!teacherId) return 'N/A';
  try {
    const doc = await db.collection('users').doc(teacherId).get();
    if (doc.exists) return escapeHtml(doc.data().name || 'N/A');
  } catch (_) {}
  return 'N/A';
}

// ─── Today's Schedule ─────────────────────────────────────────────────────────
async function renderTodaySchedule() {
  const container = document.getElementById('today-schedule-list');
  if (!container) return;

  const user = window.currentUserData;
  if (!user || !user.classId) {
    container.innerHTML = '<div class="empty-state"><div class="empty-icon">📋</div><div class="empty-text">មិនទាន់ត្រូវបានកំណត់ថ្នាក់</div></div>';
    return;
  }

  container.innerHTML = '<div class="spinner"></div>';

  const todayIndex = new Date().getDay();
  const todayKey   = DAY_KEYS[todayIndex];
  const todayName  = DAYS[todayIndex];

  // Update the day display
  const dayLabel = document.getElementById('today-day-label');
  if (dayLabel) dayLabel.textContent = `ថ្ងៃ${todayName} — ${new Date().toLocaleDateString('km-KH')}`;

  try {
    const snap = await db.collection('schedules')
      .where('classId', '==', user.classId)
      .where('dayOfWeek', '==', todayKey)
      .orderBy('shiftStart')
      .get();

    if (snap.empty) {
      container.innerHTML = `<div class="empty-state"><div class="empty-icon">🎉</div><div class="empty-text">គ្មានវេន / ថ្នាក់ ថ្ងៃនេះ!</div></div>`;
      return;
    }

    // Fetch teacher names concurrently
    const items = snap.docs.map(d => ({ id: d.id, ...d.data() }));
    const teacherNames = await Promise.all(items.map(item => fetchTeacherName(item.teacherId)));

    container.innerHTML = items.map((item, i) => `
      <div class="schedule-card">
        <div class="schedule-time">
          ${escapeHtml(item.shiftStart || '')} – ${escapeHtml(item.shiftEnd || '')}
        </div>
        <div class="schedule-info">
          <div class="schedule-subject">${escapeHtml(item.subject || 'មិនបញ្ជាក់')}</div>
          <div class="schedule-meta">
            👨‍🏫 ${teacherNames[i]} &nbsp;•&nbsp;
            🏢 ជាន់ ${escapeHtml(String(item.floor || '?'))} ห้อง ${escapeHtml(item.room || '?')} &nbsp;•&nbsp;
            📅 ${escapeHtml(item.shift || '')}
          </div>
        </div>
      </div>
    `).join('');

  } catch (err) {
    console.error('renderTodaySchedule error:', err);
    container.innerHTML = `<div class="alert alert-danger">⚠ ${escapeHtml(err.message)}</div>`;
  }
}

// ─── Weekly Schedule ──────────────────────────────────────────────────────────
async function renderWeekSchedule() {
  const container = document.getElementById('week-schedule-container');
  if (!container) return;

  const user = window.currentUserData;
  if (!user || !user.classId) {
    container.innerHTML = '<div class="empty-state"><div class="empty-text">មិនទាន់ត្រូវបានកំណត់ថ្នាក់</div></div>';
    return;
  }

  container.innerHTML = '<div class="spinner"></div>';

  try {
    const snap = await db.collection('schedules')
      .where('classId', '==', user.classId)
      .orderBy('dayOfWeek')
      .orderBy('shiftStart')
      .get();

    if (snap.empty) {
      container.innerHTML = '<div class="empty-state"><div class="empty-icon">📋</div><div class="empty-text">គ្មានកាលវិភាគសប្តាហ៍នេះ</div></div>';
      return;
    }

    const items = snap.docs.map(d => ({ id: d.id, ...d.data() }));

    // Group by day
    const byDay = {};
    DAY_KEYS.forEach(k => { byDay[k] = []; });
    items.forEach(item => {
      if (byDay[item.dayOfWeek]) byDay[item.dayOfWeek].push(item);
    });

    // Fetch all unique teacher IDs
    const teacherIds = [...new Set(items.map(i => i.teacherId).filter(Boolean))];
    const teacherMap = {};
    await Promise.all(teacherIds.map(async id => {
      try {
        const doc = await db.collection('users').doc(id).get();
        teacherMap[id] = doc.exists ? escapeHtml(doc.data().name || 'N/A') : 'N/A';
      } catch (_) { teacherMap[id] = 'N/A'; }
    }));

    const todayKey = DAY_KEYS[new Date().getDay()];

    container.innerHTML = DAY_KEYS.map((dayKey, idx) => {
      const dayItems = byDay[dayKey];
      const isToday  = dayKey === todayKey;
      if (dayItems.length === 0) return '';

      return `
        <div class="card mb-2" style="${isToday ? 'border:2px solid var(--primary)' : ''}">
          <div class="card-header">
            <span class="card-title">
              ${isToday ? '📍 ' : ''}ថ្ងៃ${escapeHtml(DAYS[idx])}
              ${isToday ? '<span class="badge badge-student" style="margin-left:.5rem">ថ្ងៃនេះ</span>' : ''}
            </span>
          </div>
          <div class="card-body" style="padding:.75rem 1.25rem">
            ${dayItems.map(item => `
              <div class="schedule-card">
                <div class="schedule-time">${escapeHtml(item.shiftStart || '')}–${escapeHtml(item.shiftEnd || '')}</div>
                <div class="schedule-info">
                  <div class="schedule-subject">${escapeHtml(item.subject || 'មិនបញ្ជាក់')}</div>
                  <div class="schedule-meta">
                    👨‍🏫 ${teacherMap[item.teacherId] || 'N/A'} &nbsp;•&nbsp;
                    🏢 ជាន់${escapeHtml(String(item.floor || '?'))} ห้อง${escapeHtml(item.room || '?')}
                  </div>
                </div>
              </div>
            `).join('')}
          </div>
        </div>`;
    }).join('');

  } catch (err) {
    console.error('renderWeekSchedule error:', err);
    container.innerHTML = `<div class="alert alert-danger">⚠ ${escapeHtml(err.message)}</div>`;
  }
}

// ─── Student Announcements ────────────────────────────────────────────────────
async function renderStudentAnnouncements() {
  const container = document.getElementById('student-ann-list');
  if (!container) return;

  const user = window.currentUserData;
  if (!user) return;

  container.innerHTML = '<div class="spinner"></div>';

  try {
    // Firestore doesn't support OR queries natively across different field values in one query,
    // so we run multiple targeted queries and merge results.
    const queries = [
      db.collection('announcements').where('targetType', '==', 'public').where('teacherOnly', '!=', true).orderBy('teacherOnly').orderBy('createdAt', 'desc').limit(20),
      db.collection('announcements').where('targetType', '==', 'all_students').orderBy('createdAt', 'desc').limit(20),
    ];

    if (user.classId) {
      queries.push(
        db.collection('announcements').where('targetType', '==', 'class').where('targetId', '==', user.classId).orderBy('createdAt', 'desc').limit(20)
      );
    }
    if (user.deptId) {
      queries.push(
        db.collection('announcements').where('targetType', '==', 'dept_students').where('targetId', '==', user.deptId).orderBy('createdAt', 'desc').limit(20)
      );
    }

    const snaps = await Promise.all(queries.map(q => q.get().catch(() => ({ docs: [] }))));
    const seen  = new Set();
    const all   = [];

    snaps.forEach(snap => {
      snap.docs.forEach(doc => {
        if (!seen.has(doc.id) && doc.data().teacherOnly !== true) {
          seen.add(doc.id);
          all.push({ id: doc.id, ...doc.data() });
        }
      });
    });

    // Sort by createdAt desc
    all.sort((a, b) => {
      const ta = a.createdAt?.toMillis ? a.createdAt.toMillis() : 0;
      const tb = b.createdAt?.toMillis ? b.createdAt.toMillis() : 0;
      return tb - ta;
    });

    if (all.length === 0) {
      container.innerHTML = '<div class="empty-state"><div class="empty-icon">📢</div><div class="empty-text">គ្មានការប្រកាសថ្មី</div></div>';
      return;
    }

    container.innerHTML = all.map(ann => {
      const date = ann.createdAt?.toDate ? ann.createdAt.toDate().toLocaleDateString('km-KH') : '';
      return `
        <div class="ann-card">
          <div class="ann-card-header">
            <span class="ann-card-title">${escapeHtml(ann.title || 'ការប្រកាស')}</span>
            <span class="ann-card-meta">${escapeHtml(date)}</span>
          </div>
          <div class="ann-card-body">${escapeHtml(ann.message || '')}</div>
          <div style="margin-top:.5rem">${renderTargetBadge(ann.targetType)}</div>
        </div>`;
    }).join('');

  } catch (err) {
    console.error('renderStudentAnnouncements error:', err);
    container.innerHTML = `<div class="alert alert-danger">⚠ ${escapeHtml(err.message)}</div>`;
  }
}

function renderTargetBadge(targetType) {
  const labels = {
    public: '<span class="badge badge-approved">🌐 សាធារណៈ</span>',
    all_students: '<span class="badge badge-student">📚 សិស្សទាំងអស់</span>',
    class: '<span class="badge badge-student">🏫 ថ្នាក់</span>',
    dept_students: '<span class="badge badge-student">🏛 ដេប</span>',
  };
  return labels[targetType] || '';
}

window.loadStudentDashboard = loadStudentDashboard;
