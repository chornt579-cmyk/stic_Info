/**
 * dashboard-teacher.js — Teacher dashboard logic for STIC Info
 */

'use strict';

// ─── Entry Point ──────────────────────────────────────────────────────────────
async function loadTeacherDashboard() {
  activateTeacherSidebar('nav-teacher-schedule');
  showTeacherSection('section-teacher-schedule');

  document.getElementById('nav-teacher-schedule')?.addEventListener('click', () => {
    activateTeacherSidebar('nav-teacher-schedule');
    showTeacherSection('section-teacher-schedule');
    renderTeacherSchedule();
  });

  document.getElementById('nav-teacher-announce')?.addEventListener('click', () => {
    activateTeacherSidebar('nav-teacher-announce');
    showTeacherSection('section-teacher-announce');
    setupAnnouncementForm();
    renderTeacherAnnouncements();
  });

  renderTeacherSchedule();
}

function activateTeacherSidebar(id) {
  document.querySelectorAll('.sidebar-link').forEach(el => el.classList.remove('active'));
  document.getElementById(id)?.classList.add('active');
}

function showTeacherSection(id) {
  document.querySelectorAll('.dashboard-section').forEach(el => el.style.display = 'none');
  const el = document.getElementById(id);
  if (el) el.style.display = 'block';
}

// ─── Teaching Schedule ────────────────────────────────────────────────────────
const DAY_LABELS = {
  monday: 'ច័ន្ទ', tuesday: 'អង្គារ', wednesday: 'ពុធ',
  thursday: 'ព្រហស្បតិ៍', friday: 'សុក្រ', saturday: 'សៅរ៍', sunday: 'អាទិត្យ'
};
const DAY_ORDER = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

async function renderTeacherSchedule() {
  const container = document.getElementById('teacher-schedule-container');
  if (!container) return;

  const user = window.currentUserData;
  if (!user) return;

  container.innerHTML = '<div class="spinner"></div>';

  try {
    const snap = await db.collection('schedules')
      .where('teacherId', '==', window.currentUser.uid)
      .orderBy('dayOfWeek')
      .orderBy('shiftStart')
      .get();

    if (snap.empty) {
      container.innerHTML = '<div class="empty-state"><div class="empty-icon">📋</div><div class="empty-text">គ្មានកាលវិភាគបង្រៀន</div></div>';
      return;
    }

    const items = snap.docs.map(d => ({ id: d.id, ...d.data() }));

    // Fetch class names
    const classIds = [...new Set(items.map(i => i.classId).filter(Boolean))];
    const classMap = {};
    await Promise.all(classIds.map(async id => {
      try {
        const doc = await db.collection('classes').doc(id).get();
        classMap[id] = doc.exists ? doc.data().name : id;
      } catch (_) { classMap[id] = id; }
    }));

    // Group by day
    const byDay = {};
    DAY_ORDER.forEach(k => { byDay[k] = []; });
    items.forEach(item => {
      if (byDay[item.dayOfWeek]) byDay[item.dayOfWeek].push(item);
    });

    const todayKey = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'][new Date().getDay()];

    container.innerHTML = DAY_ORDER.map(dayKey => {
      const dayItems = byDay[dayKey];
      if (dayItems.length === 0) return '';
      const isToday = dayKey === todayKey;

      return `
        <div class="card mb-2" style="${isToday ? 'border:2px solid var(--secondary)' : ''}">
          <div class="card-header">
            <span class="card-title">
              ${isToday ? '📍 ' : ''}ថ្ងៃ${escapeHtml(DAY_LABELS[dayKey] || dayKey)}
              ${isToday ? '<span class="badge badge-teacher">ថ្ងៃនេះ</span>' : ''}
            </span>
          </div>
          <div class="card-body" style="padding:.75rem 1.25rem">
            ${dayItems.map(item => `
              <div class="schedule-card" style="border-left-color:var(--secondary);background:var(--secondary-light)">
                <div class="schedule-time" style="color:var(--secondary)">${escapeHtml(item.shiftStart||'')}–${escapeHtml(item.shiftEnd||'')}</div>
                <div class="schedule-info">
                  <div class="schedule-subject">${escapeHtml(item.subject||'មិនបញ្ជាក់')}</div>
                  <div class="schedule-meta">
                    🏫 ថ្នាក់: ${escapeHtml(classMap[item.classId]||item.classId||'N/A')} &nbsp;•&nbsp;
                    🏢 ជាន់${escapeHtml(String(item.floor||'?'))} ห้อง${escapeHtml(item.room||'?')} &nbsp;•&nbsp;
                    📅 ${escapeHtml(item.shift||'')}
                  </div>
                </div>
              </div>
            `).join('')}
          </div>
        </div>`;
    }).join('');

  } catch (err) {
    console.error('renderTeacherSchedule error:', err);
    container.innerHTML = `<div class="alert alert-danger">⚠ ${escapeHtml(err.message)}</div>`;
  }
}

// ─── Announcement Form ────────────────────────────────────────────────────────
let annFormReady = false;

async function setupAnnouncementForm() {
  if (annFormReady) { renderTeacherAnnouncements(); return; }
  annFormReady = true;

  const user       = window.currentUserData;
  const targetSel  = document.getElementById('ann-target-class');
  const form       = document.getElementById('teacher-ann-form');

  if (!form || !targetSel) return;

  // Populate teacher's assigned classes
  const teacherClasses = user.teacherClasses || [];
  if (teacherClasses.length === 0) {
    targetSel.innerHTML = '<option value="">-- ยังไม่ได้รับมอบหมายชั้น --</option>';
  } else {
    targetSel.innerHTML = '<option value="">-- ជ្រើសរើសថ្នាក់ --</option>';
    await Promise.all(teacherClasses.map(async classId => {
      try {
        const doc = await db.collection('classes').doc(classId).get();
        const opt = document.createElement('option');
        opt.value = classId;
        opt.textContent = doc.exists ? escapeHtml(doc.data().name) : classId;
        targetSel.appendChild(opt);
      } catch (_) {}
    }));
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const title    = form.querySelector('#ann-title').value.trim();
    const message  = form.querySelector('#ann-message').value.trim();
    const targetId = targetSel.value;
    const btn      = form.querySelector('[type=submit]');

    if (!title || !message) { showToast('សូមបំពេញចំណងជើង និងខ្លឹមសារ', 'warning'); return; }
    if (!targetId)           { showToast('សូមជ្រើសរើសថ្នាក់', 'warning'); return; }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner spinner-sm"></span> កំពុងផ្ញើ...';

    try {
      await db.collection('announcements').add({
        title,
        message,
        targetType:  'class',
        targetId,
        authorId:    window.currentUser.uid,
        authorName:  user.name || '',
        teacherOnly: false,
        sendTelegram:false,
        createdAt:   firebase.firestore.FieldValue.serverTimestamp()
      });
      showToast('ការប្រកាសបានផ្ញើដោយជោគជ័យ!', 'success');
      form.reset();
      renderTeacherAnnouncements();
    } catch (err) {
      showToast('ផ្ញើបានបរាជ័យ: ' + err.message, 'error');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '📢 ផ្ញើការប្រកាស';
    }
  });
}

// ─── Sent Announcements ───────────────────────────────────────────────────────
async function renderTeacherAnnouncements() {
  const container = document.getElementById('teacher-sent-ann-list');
  if (!container) return;

  container.innerHTML = '<div class="spinner"></div>';

  try {
    const snap = await db.collection('announcements')
      .where('authorId', '==', window.currentUser.uid)
      .orderBy('createdAt', 'desc')
      .limit(30)
      .get();

    if (snap.empty) {
      container.innerHTML = '<div class="empty-state"><div class="empty-icon">📭</div><div class="empty-text">មិនទាន់ផ្ញើការប្រកាសណាមួយ</div></div>';
      return;
    }

    // Fetch class name map
    const classIds = [...new Set(snap.docs.map(d => d.data().targetId).filter(Boolean))];
    const classMap = {};
    await Promise.all(classIds.map(async id => {
      try {
        const doc = await db.collection('classes').doc(id).get();
        classMap[id] = doc.exists ? doc.data().name : id;
      } catch (_) { classMap[id] = id; }
    }));

    container.innerHTML = snap.docs.map(doc => {
      const ann  = doc.data();
      const date = ann.createdAt?.toDate ? ann.createdAt.toDate().toLocaleDateString('km-KH') : '';
      return `
        <div class="ann-card">
          <div class="ann-card-header">
            <span class="ann-card-title">${escapeHtml(ann.title||'ការប្រកាស')}</span>
            <span class="ann-card-meta">${escapeHtml(date)}</span>
          </div>
          <div class="ann-card-body">${escapeHtml(ann.message||'')}</div>
          <div style="margin-top:.5rem;display:flex;gap:.5rem;align-items:center">
            <span class="badge badge-teacher">🏫 ${escapeHtml(classMap[ann.targetId]||ann.targetId||'')}</span>
            <button class="btn btn-sm btn-danger" onclick="deleteTeacherAnnouncement('${escapeHtml(doc.id)}')">🗑 លុប</button>
          </div>
        </div>`;
    }).join('');

  } catch (err) {
    container.innerHTML = `<div class="alert alert-danger">⚠ ${escapeHtml(err.message)}</div>`;
  }
}

async function deleteTeacherAnnouncement(annId) {
  if (!confirm('លុបការប្រកាសនេះ?')) return;
  try {
    await db.collection('announcements').doc(annId).delete();
    showToast('បានលុបការប្រកាស', 'success');
    renderTeacherAnnouncements();
  } catch (err) {
    showToast('លុបបានបរាជ័យ: ' + err.message, 'error');
  }
}

window.loadTeacherDashboard       = loadTeacherDashboard;
window.deleteTeacherAnnouncement  = deleteTeacherAnnouncement;
