/**
 * dashboard-admin.js — Admin dashboard logic for STIC Info
 * Tabs: Departments | Classes | Users | Schedules | Announcements
 */

'use strict';

const DAY_ORDER_ADMIN = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
const DAY_LABEL_ADMIN = { monday:'ច័ន្ទ', tuesday:'អង្គារ', wednesday:'ពុធ', thursday:'ព្រហស្បតិ៍', friday:'សុក្រ', saturday:'សៅរ៍', sunday:'អាទិត្យ' };

// ─── Entry Point ──────────────────────────────────────────────────────────────
function loadAdminDashboard() {
  activateAdminNav('nav-admin');
  showSection('section-admin');
  setupAdminTabs();
  loadAdminTab('tab-departments');
}

function activateAdminNav(id) {
  document.querySelectorAll('.sidebar-link').forEach(el => el.classList.remove('active'));
  document.getElementById(id)?.classList.add('active');
}

function showSection(id) {
  document.querySelectorAll('.dashboard-section').forEach(el => el.style.display = 'none');
  const el = document.getElementById(id);
  if (el) el.style.display = 'block';
}

// ─── Tabs ─────────────────────────────────────────────────────────────────────
function setupAdminTabs() {
  document.querySelectorAll('.admin-tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.admin-tab-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      loadAdminTab(btn.dataset.tab);
    });
  });
}

function loadAdminTab(tab) {
  document.querySelectorAll('.admin-tab-panel').forEach(p => p.style.display = 'none');
  const panel = document.getElementById(tab + '-panel');
  if (panel) panel.style.display = 'block';

  switch (tab) {
    case 'tab-departments':  renderDepartments(); break;
    case 'tab-classes':      renderClasses();     break;
    case 'tab-users':        renderUsers();       break;
    case 'tab-schedules':    renderSchedules();   break;
    case 'tab-announcements':renderAdminAnnouncements(); break;
  }
}

// ════════════════════════════════════════════════════════════════════════════
// DEPARTMENTS
// ════════════════════════════════════════════════════════════════════════════
async function renderDepartments() {
  const list = document.getElementById('dept-list');
  if (!list) return;
  list.innerHTML = '<div class="spinner"></div>';

  try {
    const snap = await db.collection('departments').orderBy('name').get();
    if (snap.empty) {
      list.innerHTML = '<div class="empty-state"><div class="empty-text">គ្មានដេប៉ាតឺម៉ង់</div></div>'; return;
    }
    list.innerHTML = `
      <div class="table-wrap">
        <table>
          <thead><tr><th>ឈ្មោះ</th><th>កូដ</th><th>ការពិពណ៌នា</th><th></th></tr></thead>
          <tbody>
            ${snap.docs.map(doc => {
              const d = doc.data();
              return `<tr>
                <td><strong>${escapeHtml(d.name||'')}</strong></td>
                <td><code>${escapeHtml(d.code||'')}</code></td>
                <td>${escapeHtml(d.description||'—')}</td>
                <td>
                  <button class="btn btn-sm btn-outline" onclick="editDepartment('${escapeHtml(doc.id)}','${escapeHtml(d.name||'')}','${escapeHtml(d.code||'')}','${escapeHtml(d.description||'')}')">✏ កែ</button>
                  <button class="btn btn-sm btn-danger" onclick="deleteDepartment('${escapeHtml(doc.id)}')">🗑</button>
                </td>
              </tr>`;
            }).join('')}
          </tbody>
        </table>
      </div>`;
  } catch (err) {
    list.innerHTML = `<div class="alert alert-danger">⚠ ${escapeHtml(err.message)}</div>`;
  }
}

function showDeptForm(id = '', name = '', code = '', description = '') {
  const modal = document.getElementById('dept-modal');
  if (!modal) return;
  modal.style.display = 'flex';
  document.getElementById('dept-form-id').value          = id;
  document.getElementById('dept-form-name').value        = name;
  document.getElementById('dept-form-code').value        = code;
  document.getElementById('dept-form-description').value = description;
  document.getElementById('dept-modal-title').textContent = id ? 'កែដេប៉ាតឺម៉ង់' : 'បន្ថែមដេប៉ាតឺម៉ង់';
}

function editDepartment(id, name, code, description) { showDeptForm(id, name, code, description); }

async function saveDepartment() {
  const id          = document.getElementById('dept-form-id').value;
  const name        = document.getElementById('dept-form-name').value.trim();
  const code        = document.getElementById('dept-form-code').value.trim();
  const description = document.getElementById('dept-form-description').value.trim();

  if (!name) { showToast('សូមបំពេញឈ្មោះដេប', 'warning'); return; }

  try {
    if (id) {
      await db.collection('departments').doc(id).update({ name, code, description, updatedAt: firebase.firestore.FieldValue.serverTimestamp() });
      showToast('បានធ្វើបច្ចុប្បន្នភាព', 'success');
    } else {
      await db.collection('departments').add({ name, code, description, createdAt: firebase.firestore.FieldValue.serverTimestamp() });
      showToast('បានបន្ថែមដេប', 'success');
    }
    closeDeptModal();
    renderDepartments();
  } catch (err) { showToast('⚠ ' + err.message, 'error'); }
}

async function deleteDepartment(id) {
  if (!confirm('លុបដេប៉ាតឺម៉ង់នេះ? ការលុបនេះ​ ​មិនអាចត្រឡប់វិញ។')) return;
  try {
    await db.collection('departments').doc(id).delete();
    showToast('បានលុប', 'success'); renderDepartments();
  } catch (err) { showToast('⚠ ' + err.message, 'error'); }
}

function closeDeptModal() {
  const modal = document.getElementById('dept-modal');
  if (modal) modal.style.display = 'none';
}

window.editDepartment  = editDepartment;
window.deleteDepartment= deleteDepartment;
window.saveDepartment  = saveDepartment;
window.closeDeptModal  = closeDeptModal;
window.showDeptForm    = showDeptForm;

// ════════════════════════════════════════════════════════════════════════════
// CLASSES
// ════════════════════════════════════════════════════════════════════════════
async function renderClasses() {
  const list = document.getElementById('class-list');
  if (!list) return;
  list.innerHTML = '<div class="spinner"></div>';

  try {
    const [classSnap, deptSnap] = await Promise.all([
      db.collection('classes').orderBy('name').get(),
      db.collection('departments').get()
    ]);

    const deptMap = {};
    deptSnap.forEach(d => { deptMap[d.id] = d.data().name; });

    if (classSnap.empty) {
      list.innerHTML = '<div class="empty-state"><div class="empty-text">គ្មានថ្នាក់</div></div>'; return;
    }

    list.innerHTML = `
      <div class="table-wrap">
        <table>
          <thead><tr><th>ឈ្មោះ</th><th>ដេប</th><th>ជាន់</th><th>ห้อง</th><th>វេន</th><th></th></tr></thead>
          <tbody>
            ${classSnap.docs.map(doc => {
              const c = doc.data();
              return `<tr>
                <td><strong>${escapeHtml(c.name||'')}</strong></td>
                <td>${escapeHtml(deptMap[c.deptId]||c.deptId||'—')}</td>
                <td>${escapeHtml(String(c.floor||'—'))}</td>
                <td>${escapeHtml(c.room||'—')}</td>
                <td>${escapeHtml(c.shift||'—')}</td>
                <td>
                  <button class="btn btn-sm btn-outline" onclick="editClass('${escapeHtml(doc.id)}')">✏ កែ</button>
                  <button class="btn btn-sm btn-danger" onclick="deleteClass('${escapeHtml(doc.id)}')">🗑</button>
                </td>
              </tr>`;
            }).join('')}
          </tbody>
        </table>
      </div>`;
  } catch (err) {
    list.innerHTML = `<div class="alert alert-danger">⚠ ${escapeHtml(err.message)}</div>`;
  }
}

async function showClassForm(id = '') {
  const modal = document.getElementById('class-modal');
  if (!modal) return;
  modal.style.display = 'flex';

  // Populate dept dropdown
  const deptSel = document.getElementById('class-form-dept');
  if (deptSel) {
    const snap = await db.collection('departments').orderBy('name').get().catch(() => ({ docs: [] }));
    deptSel.innerHTML = '<option value="">-- ជ្រើសរើសដេប --</option>';
    snap.docs.forEach(d => {
      const opt = document.createElement('option');
      opt.value = d.id; opt.textContent = escapeHtml(d.data().name);
      deptSel.appendChild(opt);
    });
  }

  document.getElementById('class-form-id').value = id;
  document.getElementById('class-modal-title').textContent = id ? 'កែថ្នាក់' : 'បន្ថែមថ្នាក់';

  if (id) {
    try {
      const doc = await db.collection('classes').doc(id).get();
      if (doc.exists) {
        const c = doc.data();
        document.getElementById('class-form-name').value  = c.name  || '';
        document.getElementById('class-form-dept').value  = c.deptId|| '';
        document.getElementById('class-form-floor').value = c.floor  || '';
        document.getElementById('class-form-room').value  = c.room   || '';
        document.getElementById('class-form-shift').value = c.shift  || '';
      }
    } catch (_) {}
  } else {
    document.getElementById('class-form-name').value  = '';
    document.getElementById('class-form-floor').value = '';
    document.getElementById('class-form-room').value  = '';
    document.getElementById('class-form-shift').value = '';
  }
}

function editClass(id) { showClassForm(id); }

async function saveClass() {
  const id    = document.getElementById('class-form-id').value;
  const name  = document.getElementById('class-form-name').value.trim();
  const deptId= document.getElementById('class-form-dept').value;
  const floor = document.getElementById('class-form-floor').value.trim();
  const room  = document.getElementById('class-form-room').value.trim();
  const shift = document.getElementById('class-form-shift').value;

  if (!name || !deptId) { showToast('សូមបំពេញឈ្មោះ និងដេប', 'warning'); return; }

  try {
    const data = { name, deptId, floor, room, shift, updatedAt: firebase.firestore.FieldValue.serverTimestamp() };
    if (id) {
      await db.collection('classes').doc(id).update(data);
      showToast('បានធ្វើបច្ចុប្បន្នភាព', 'success');
    } else {
      data.createdAt = firebase.firestore.FieldValue.serverTimestamp();
      await db.collection('classes').add(data);
      showToast('បានបន្ថែមថ្នាក់', 'success');
    }
    closeClassModal(); renderClasses();
  } catch (err) { showToast('⚠ ' + err.message, 'error'); }
}

async function deleteClass(id) {
  if (!confirm('លុបថ្នាក់នេះ?')) return;
  try {
    await db.collection('classes').doc(id).delete();
    showToast('បានលុប', 'success'); renderClasses();
  } catch (err) { showToast('⚠ ' + err.message, 'error'); }
}

function closeClassModal() {
  const modal = document.getElementById('class-modal');
  if (modal) modal.style.display = 'none';
}

window.showClassForm  = showClassForm;
window.editClass      = editClass;
window.saveClass      = saveClass;
window.deleteClass    = deleteClass;
window.closeClassModal= closeClassModal;

// ════════════════════════════════════════════════════════════════════════════
// USERS
// ════════════════════════════════════════════════════════════════════════════
async function renderUsers() {
  const studList = document.getElementById('students-list');
  const teachList= document.getElementById('teachers-list');
  if (!studList || !teachList) return;

  studList.innerHTML = '<div class="spinner"></div>';
  teachList.innerHTML= '<div class="spinner"></div>';

  try {
    const [studSnap, teachSnap, classSnap, deptSnap] = await Promise.all([
      db.collection('users').where('role', '==', 'student').orderBy('name').get(),
      db.collection('users').where('role', '==', 'teacher').orderBy('name').get(),
      db.collection('classes').get(),
      db.collection('departments').get()
    ]);

    const classMap = {}, deptMap = {};
    classSnap.forEach(d => { classMap[d.id] = d.data().name; });
    deptSnap.forEach(d => { deptMap[d.id] = d.data().name; });

    // Students
    if (studSnap.empty) {
      studList.innerHTML = '<div class="empty-state"><div class="empty-text">គ្មានសិស្ស</div></div>';
    } else {
      studList.innerHTML = `
        <div class="table-wrap">
          <table>
            <thead><tr><th>ឈ្មោះ</th><th>Email</th><th>ថ្នាក់</th><th>ដេប</th><th>ប្រតិបត្តិការ</th></tr></thead>
            <tbody>
              ${studSnap.docs.map(doc => {
                const u = doc.data();
                return `<tr>
                  <td>${escapeHtml(u.name||'')}</td>
                  <td>${escapeHtml(u.email||'')}</td>
                  <td>${escapeHtml(classMap[u.classId]||u.classId||'—')}</td>
                  <td>${escapeHtml(deptMap[u.deptId]||u.deptId||'—')}</td>
                  <td>
                    <button class="btn btn-sm btn-outline" onclick="assignStudentClass('${escapeHtml(doc.id)}','${escapeHtml(u.name||'')}')">🏫 ផ្ដល់ថ្នាក់</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteUser('${escapeHtml(doc.id)}','${escapeHtml(u.name||'')}')">🗑</button>
                  </td>
                </tr>`;
              }).join('')}
            </tbody>
          </table>
        </div>`;
    }

    // Teachers
    if (teachSnap.empty) {
      teachList.innerHTML = '<div class="empty-state"><div class="empty-text">គ្មានគ្រូ</div></div>';
    } else {
      teachList.innerHTML = `
        <div class="table-wrap">
          <table>
            <thead><tr><th>ឈ្មោះ</th><th>Email</th><th>ស្ថានភាព</th><th>ដេប</th><th>ប្រតិបត្តិការ</th></tr></thead>
            <tbody>
              ${teachSnap.docs.map(doc => {
                const u = doc.data();
                const isPending = u.status === 'pending';
                return `<tr>
                  <td>${escapeHtml(u.name||'')}</td>
                  <td>${escapeHtml(u.email||'')}</td>
                  <td><span class="badge ${isPending ? 'badge-pending' : 'badge-approved'}">${isPending ? '⏳ រង់ចាំ' : '✔ អនុម័ត'}</span></td>
                  <td>${escapeHtml(deptMap[u.deptId]||u.deptId||'—')}</td>
                  <td>
                    ${isPending ? `<button class="btn btn-sm btn-secondary" onclick="approveTeacher('${escapeHtml(doc.id)}','${escapeHtml(u.name||'')}')">✔ អនុម័ត</button>` : ''}
                    <button class="btn btn-sm btn-outline" onclick="assignTeacherClasses('${escapeHtml(doc.id)}','${escapeHtml(u.name||'')}')">🏫 ផ្ដល់ថ្នាក់</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteUser('${escapeHtml(doc.id)}','${escapeHtml(u.name||'')}')">🗑</button>
                  </td>
                </tr>`;
              }).join('')}
            </tbody>
          </table>
        </div>`;
    }
  } catch (err) {
    studList.innerHTML = `<div class="alert alert-danger">⚠ ${escapeHtml(err.message)}</div>`;
    teachList.innerHTML= '';
  }
}

async function approveTeacher(uid, name) {
  if (!confirm(`អនុម័ត ${name} ជាគ្រូ?`)) return;
  try {
    await db.collection('users').doc(uid).update({
      status: 'approved',
      approvedAt: firebase.firestore.FieldValue.serverTimestamp()
    });
    showToast(`${name} ត្រូវបានអនុម័ត`, 'success');
    renderUsers();
  } catch (err) { showToast('⚠ ' + err.message, 'error'); }
}

async function assignStudentClass(uid, name) {
  const classId = prompt(`ប្ដូរថ្នាក់សិស្ស ${name}? បញ្ចូល Class ID:`);
  if (!classId) return;
  try {
    await db.collection('users').doc(uid).update({ classId: classId.trim() });
    showToast('បានផ្ដល់ថ្នាក់', 'success'); renderUsers();
  } catch (err) { showToast('⚠ ' + err.message, 'error'); }
}

async function assignTeacherClasses(uid, name) {
  // Open modal to select classes
  const modal = document.getElementById('assign-classes-modal');
  if (!modal) { alert('Modal not found'); return; }

  document.getElementById('assign-modal-title').textContent = `ផ្ដល់ថ្នាក់ → ${name}`;
  document.getElementById('assign-teacher-uid').value = uid;

  const classListEl = document.getElementById('assign-class-checklist');
  classListEl.innerHTML = '<div class="spinner spinner-sm"></div>';
  modal.style.display = 'flex';

  try {
    const [classSnap, teacherDoc] = await Promise.all([
      db.collection('classes').orderBy('name').get(),
      db.collection('users').doc(uid).get()
    ]);
    const currentClasses = (teacherDoc.data() || {}).teacherClasses || [];

    classListEl.innerHTML = classSnap.docs.map(doc => {
      const checked = currentClasses.includes(doc.id) ? 'checked' : '';
      return `<label style="display:flex;gap:.5rem;align-items:center;padding:.35rem 0;cursor:pointer">
        <input type="checkbox" value="${escapeHtml(doc.id)}" ${checked}>
        <span>${escapeHtml(doc.data().name)}</span>
      </label>`;
    }).join('');
  } catch (err) {
    classListEl.innerHTML = `<div class="alert alert-danger">⚠ ${escapeHtml(err.message)}</div>`;
  }
}

async function saveTeacherClasses() {
  const uid = document.getElementById('assign-teacher-uid').value;
  const checks = document.querySelectorAll('#assign-class-checklist input[type=checkbox]:checked');
  const classIds = Array.from(checks).map(c => c.value);

  try {
    await db.collection('users').doc(uid).update({ teacherClasses: classIds });
    showToast('បានផ្ដល់ថ្នាក់', 'success');
    closeAssignModal(); renderUsers();
  } catch (err) { showToast('⚠ ' + err.message, 'error'); }
}

function closeAssignModal() {
  const modal = document.getElementById('assign-classes-modal');
  if (modal) modal.style.display = 'none';
}

async function deleteUser(uid, name) {
  if (!confirm(`លុបអ្នកប្រើ ${name}? ការពតបញ្ជាក់ : ត្រូវវិជ្ជាអ្នកគ្រប់គ្រង`)) return;
  try {
    // Call cloud function to delete auth + firestore atomically
    const deleteFn = firebase.functions().httpsCallable('deleteUserAccount');
    await deleteFn({ uid });
    showToast(`${name} ត្រូវបានលុប`, 'success'); renderUsers();
  } catch (err) {
    // Fallback: delete firestore doc only (auth requires cloud function)
    try {
      await db.collection('users').doc(uid).delete();
      showToast(`${name} ត្រូវបានលុក (Firestore only)`, 'warning'); renderUsers();
    } catch (e2) { showToast('⚠ ' + e2.message, 'error'); }
  }
}

window.approveTeacher      = approveTeacher;
window.assignStudentClass  = assignStudentClass;
window.assignTeacherClasses= assignTeacherClasses;
window.saveTeacherClasses  = saveTeacherClasses;
window.closeAssignModal    = closeAssignModal;
window.deleteUser          = deleteUser;

// ════════════════════════════════════════════════════════════════════════════
// SCHEDULES
// ════════════════════════════════════════════════════════════════════════════
async function renderSchedules() {
  const list = document.getElementById('schedule-list');
  if (!list) return;
  list.innerHTML = '<div class="spinner"></div>';

  await populateScheduleForm();

  try {
    const [schedSnap, classSnap, teachSnap] = await Promise.all([
      db.collection('schedules').orderBy('dayOfWeek').orderBy('shiftStart').get(),
      db.collection('classes').get(),
      db.collection('users').where('role', '==', 'teacher').get()
    ]);

    const classMap = {}, teachMap = {};
    classSnap.forEach(d => { classMap[d.id] = d.data().name; });
    teachSnap.forEach(d => { teachMap[d.id] = d.data().name; });

    if (schedSnap.empty) {
      list.innerHTML = '<div class="empty-state"><div class="empty-text">គ្មានកាលវិភាគ</div></div>'; return;
    }

    list.innerHTML = `
      <div class="table-wrap">
        <table>
          <thead><tr><th>ថ្នាក់</th><th>ថ្ងៃ</th><th>ម៉ោង</th><th>មុខវិជ្ជា</th><th>គ្រូ</th><th>ជាន់/ห้อง</th><th></th></tr></thead>
          <tbody>
            ${schedSnap.docs.map(doc => {
              const s = doc.data();
              return `<tr>
                <td>${escapeHtml(classMap[s.classId]||s.classId||'—')}</td>
                <td>${escapeHtml(DAY_LABEL_ADMIN[s.dayOfWeek]||s.dayOfWeek||'—')}</td>
                <td>${escapeHtml(s.shiftStart||'')}–${escapeHtml(s.shiftEnd||'')}</td>
                <td>${escapeHtml(s.subject||'—')}</td>
                <td>${escapeHtml(teachMap[s.teacherId]||s.teacherId||'—')}</td>
                <td>ជាន់${escapeHtml(String(s.floor||'?'))} / ${escapeHtml(s.room||'—')}</td>
                <td><button class="btn btn-sm btn-danger" onclick="deleteSchedule('${escapeHtml(doc.id)}')">🗑</button></td>
              </tr>`;
            }).join('')}
          </tbody>
        </table>
      </div>`;
  } catch (err) {
    list.innerHTML = `<div class="alert alert-danger">⚠ ${escapeHtml(err.message)}</div>`;
  }
}

async function populateScheduleForm() {
  const classSel  = document.getElementById('sched-form-class');
  const teachSel  = document.getElementById('sched-form-teacher');
  if (!classSel || !teachSel) return;

  try {
    const [classSnap, teachSnap] = await Promise.all([
      db.collection('classes').orderBy('name').get(),
      db.collection('users').where('role', '==', 'teacher').where('status', '==', 'approved').orderBy('name').get()
    ]);

    classSel.innerHTML = '<option value="">-- ជ្រើសរើសថ្នាក់ --</option>';
    classSnap.forEach(d => {
      const opt = document.createElement('option');
      opt.value = d.id; opt.textContent = escapeHtml(d.data().name);
      classSel.appendChild(opt);
    });

    teachSel.innerHTML = '<option value="">-- ជ្រើសរើសគ្រូ --</option>';
    teachSnap.forEach(d => {
      const opt = document.createElement('option');
      opt.value = d.id; opt.textContent = escapeHtml(d.data().name);
      teachSel.appendChild(opt);
    });
  } catch (_) {}
}

// Real-time conflict detection
async function checkScheduleConflict() {
  const classId   = document.getElementById('sched-form-class')?.value;
  const teacherId = document.getElementById('sched-form-teacher')?.value;
  const day       = document.getElementById('sched-form-day')?.value;
  const shift     = document.getElementById('sched-form-shift')?.value;
  const room      = document.getElementById('sched-form-room')?.value?.trim();
  const warn      = document.getElementById('conflict-warning');

  if (!warn) return;
  if (!classId || !day || !shift) { warn.style.display = 'none'; return; }

  try {
    const conflicts = [];

    // Check class conflict
    const classConflict = await db.collection('schedules')
      .where('classId', '==', classId)
      .where('dayOfWeek', '==', day)
      .where('shift', '==', shift)
      .get();
    if (!classConflict.empty) conflicts.push('ថ្នាក់នេះ​ មានម៉ោងបង្រៀនផ្ទាំងហើយ');

    // Check teacher conflict
    if (teacherId) {
      const teachConflict = await db.collection('schedules')
        .where('teacherId', '==', teacherId)
        .where('dayOfWeek', '==', day)
        .where('shift', '==', shift)
        .get();
      if (!teachConflict.empty) conflicts.push('គ្រូនេះ​ មានម៉ោងបង្រៀនផ្ទាំងហើយ');
    }

    // Check room conflict
    if (room) {
      const roomConflict = await db.collection('schedules')
        .where('room', '==', room)
        .where('dayOfWeek', '==', day)
        .where('shift', '==', shift)
        .get();
      if (!roomConflict.empty) conflicts.push(`ห้อง ${room} ត្រូវបានប្រើប្រាស់ហើយ`);
    }

    if (conflicts.length > 0) {
      warn.style.display = 'flex';
      warn.innerHTML = `⚠ ការប៉ះទង្គិច: ${conflicts.map(escapeHtml).join(' | ')}`;
      document.getElementById('sched-form-submit').disabled = true;
    } else {
      warn.style.display = 'none';
      document.getElementById('sched-form-submit').disabled = false;
    }
  } catch (err) {
    warn.style.display = 'none';
  }
}

window.checkScheduleConflict = checkScheduleConflict;

async function saveSchedule() {
  const classId   = document.getElementById('sched-form-class')?.value;
  const teacherId = document.getElementById('sched-form-teacher')?.value;
  const day       = document.getElementById('sched-form-day')?.value;
  const subject   = document.getElementById('sched-form-subject')?.value.trim();
  const shift     = document.getElementById('sched-form-shift')?.value;
  const shiftStart= document.getElementById('sched-form-start')?.value;
  const shiftEnd  = document.getElementById('sched-form-end')?.value;
  const floor     = document.getElementById('sched-form-floor')?.value.trim();
  const room      = document.getElementById('sched-form-room')?.value.trim();

  if (!classId || !teacherId || !day || !subject || !shift) {
    showToast('សូមបំពេញព័ត៌មានទាំងអស់', 'warning'); return;
  }

  const btn = document.getElementById('sched-form-submit');
  btn.disabled = true; btn.innerHTML = '<span class="spinner spinner-sm"></span> កំពុងរក្សាទុក...';

  try {
    // Final conflict check
    const [cc, tc, rc] = await Promise.all([
      db.collection('schedules').where('classId','==',classId).where('dayOfWeek','==',day).where('shift','==',shift).get(),
      db.collection('schedules').where('teacherId','==',teacherId).where('dayOfWeek','==',day).where('shift','==',shift).get(),
      room ? db.collection('schedules').where('room','==',room).where('dayOfWeek','==',day).where('shift','==',shift).get() : Promise.resolve({ empty: true })
    ]);

    if (!cc.empty || !tc.empty || !rc.empty) {
      showToast('មានការប៉ះទង្គិចកាលវិភាគ! សូមពិនិត្យម្ដងទៀត', 'error');
      btn.disabled = false; btn.innerHTML = '💾 រក្សាទុក';
      return;
    }

    await db.collection('schedules').add({
      classId, teacherId, dayOfWeek: day, subject, shift,
      shiftStart, shiftEnd, floor, room,
      createdAt: firebase.firestore.FieldValue.serverTimestamp()
    });
    showToast('បានបន្ថែមកាលវិភាគ', 'success');
    document.getElementById('schedule-add-form')?.reset();
    renderSchedules();
  } catch (err) {
    showToast('⚠ ' + err.message, 'error');
  } finally {
    btn.disabled = false; btn.innerHTML = '💾 រក្សាទុក';
  }
}

async function deleteSchedule(id) {
  if (!confirm('លុបកាលវិភាគនេះ?')) return;
  try {
    await db.collection('schedules').doc(id).delete();
    showToast('បានលុប', 'success'); renderSchedules();
  } catch (err) { showToast('⚠ ' + err.message, 'error'); }
}

window.saveSchedule   = saveSchedule;
window.deleteSchedule = deleteSchedule;

// ════════════════════════════════════════════════════════════════════════════
// ANNOUNCEMENTS (Admin)
// ════════════════════════════════════════════════════════════════════════════
async function renderAdminAnnouncements() {
  const list = document.getElementById('admin-ann-list');
  if (!list) return;

  await populateAnnForm();
  list.innerHTML = '<div class="spinner"></div>';

  try {
    const snap = await db.collection('announcements').orderBy('createdAt', 'desc').limit(50).get();
    if (snap.empty) {
      list.innerHTML = '<div class="empty-state"><div class="empty-text">គ្មានការប្រកាស</div></div>'; return;
    }

    const targetLabels = {
      public:'🌐 សាធារណៈ', all_students:'📚 សិស្សទាំងអស់',
      class:'🏫 ថ្នាក់', dept_students:'🏛 ដេប (សិស្ស)',
      all_teachers:'👩‍🏫 គ្រូទាំងអស់', dept_teachers:'🏛 ដេប (គ្រូ)'
    };

    list.innerHTML = snap.docs.map(doc => {
      const ann = doc.data();
      const date = ann.createdAt?.toDate ? ann.createdAt.toDate().toLocaleDateString('km-KH') : '';
      return `
        <div class="ann-card">
          <div class="ann-card-header">
            <span class="ann-card-title">${escapeHtml(ann.title||'ការប្រកាស')}</span>
            <span class="ann-card-meta">${escapeHtml(date)}</span>
          </div>
          <div class="ann-card-body">${escapeHtml(ann.message||'')}</div>
          <div style="margin-top:.5rem;display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
            <span class="badge badge-admin">${escapeHtml(targetLabels[ann.targetType]||ann.targetType||'')}</span>
            ${ann.teacherOnly ? '<span class="badge badge-teacher">👩‍🏫 Teacher Only</span>' : ''}
            ${ann.sendTelegram ? '<span class="badge badge-approved">📱 Telegram</span>' : ''}
            <button class="btn btn-sm btn-danger" onclick="deleteAdminAnn('${escapeHtml(doc.id)}')">🗑 លុប</button>
          </div>
        </div>`;
    }).join('');
  } catch (err) {
    list.innerHTML = `<div class="alert alert-danger">⚠ ${escapeHtml(err.message)}</div>`;
  }
}

async function populateAnnForm() {
  const targetTypeSel = document.getElementById('ann-form-target-type');
  if (!targetTypeSel) return;

  targetTypeSel.addEventListener('change', async () => {
    const type = targetTypeSel.value;
    const targetIdWrap = document.getElementById('ann-target-id-wrap');
    const targetIdSel  = document.getElementById('ann-form-target-id');
    if (!targetIdWrap || !targetIdSel) return;

    if (['class', 'dept_students', 'dept_teachers'].includes(type)) {
      targetIdWrap.style.display = 'block';
      targetIdSel.innerHTML = '<option value="">-- កំពុងផ្ទុក... --</option>';

      if (type === 'class') {
        const snap = await db.collection('classes').orderBy('name').get();
        targetIdSel.innerHTML = '<option value="">-- ជ្រើសរើសថ្នាក់ --</option>';
        snap.forEach(d => {
          const opt = document.createElement('option');
          opt.value = d.id; opt.textContent = escapeHtml(d.data().name);
          targetIdSel.appendChild(opt);
        });
      } else {
        const snap = await db.collection('departments').orderBy('name').get();
        targetIdSel.innerHTML = '<option value="">-- ជ្រើសរើសដេប --</option>';
        snap.forEach(d => {
          const opt = document.createElement('option');
          opt.value = d.id; opt.textContent = escapeHtml(d.data().name);
          targetIdSel.appendChild(opt);
        });
      }
    } else {
      targetIdWrap.style.display = 'none';
    }
  });
}

async function sendAdminAnnouncement() {
  const title       = document.getElementById('ann-form-title')?.value.trim();
  const message     = document.getElementById('ann-form-message')?.value.trim();
  const targetType  = document.getElementById('ann-form-target-type')?.value;
  const targetId    = document.getElementById('ann-form-target-id')?.value || '';
  const teacherOnly = document.getElementById('ann-form-teacher-only')?.checked || false;
  const sendTelegram= document.getElementById('ann-form-telegram')?.checked || false;
  const btn         = document.getElementById('ann-form-submit');

  if (!title || !message || !targetType) { showToast('សូមបំពេញព័ត៌មានទាំងអស់', 'warning'); return; }
  if (['class','dept_students','dept_teachers'].includes(targetType) && !targetId) {
    showToast('សូមជ្រើសរើសគោលដៅ', 'warning'); return;
  }

  btn.disabled = true; btn.innerHTML = '<span class="spinner spinner-sm"></span> កំពុងផ្ញើ...';

  try {
    const annRef = await db.collection('announcements').add({
      title, message, targetType, targetId,
      teacherOnly, sendTelegram,
      authorId:   window.currentUser.uid,
      authorName: window.currentUserData.name || '',
      createdAt:  firebase.firestore.FieldValue.serverTimestamp()
    });

    // Optionally call Telegram cloud function
    if (sendTelegram) {
      try {
        const fn = firebase.functions().httpsCallable('sendTelegramNotification');
        await fn({ message: `📢 ${title}\n\n${message}`, chatId: '' }); // chatId read from server config
      } catch (tgErr) {
        showToast('បានផ្ញើទៅ Firestore ប៉ុន្តែ Telegram បានបរាជ័យ: ' + tgErr.message, 'warning');
      }
    }

    showToast('ការប្រកាសបានផ្ញើ!', 'success');
    document.getElementById('admin-ann-form')?.reset();
    document.getElementById('ann-target-id-wrap').style.display = 'none';
    renderAdminAnnouncements();
  } catch (err) {
    showToast('⚠ ' + err.message, 'error');
  } finally {
    btn.disabled = false; btn.innerHTML = '📢 ផ្ញើការប្រកាស';
  }
}

async function deleteAdminAnn(id) {
  if (!confirm('លុបការប្រកាសនេះ?')) return;
  try {
    await db.collection('announcements').doc(id).delete();
    showToast('បានលុប', 'success'); renderAdminAnnouncements();
  } catch (err) { showToast('⚠ ' + err.message, 'error'); }
}

window.loadAdminDashboard     = loadAdminDashboard;
window.sendAdminAnnouncement  = sendAdminAnnouncement;
window.deleteAdminAnn         = deleteAdminAnn;
