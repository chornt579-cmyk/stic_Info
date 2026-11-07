/**
 * STIC Info — Authentication UI & Accessibility Enhancements
 * Complies with strict CSP (No inline scripts or handlers)
 */

document.addEventListener('DOMContentLoaded', function () {
    const loginTabBtn = document.getElementById('tab-login-btn');
    const registerTabBtn = document.getElementById('tab-register-btn');
    const loginPanel = document.getElementById('login-panel');
    const registerPanel = document.getElementById('register-panel');

    // 1. Tab Switching Functionality
    function switchTab(target) {
        if (!loginTabBtn || !registerTabBtn || !loginPanel || !registerPanel) return;

        if (target === 'login') {
            loginTabBtn.classList.add('tab-active');
            loginTabBtn.classList.remove('tab-inactive');
            loginTabBtn.setAttribute('aria-selected', 'true');
            loginTabBtn.setAttribute('tabindex', '0');

            registerTabBtn.classList.remove('tab-active');
            registerTabBtn.classList.add('tab-inactive');
            registerTabBtn.setAttribute('aria-selected', 'false');
            registerTabBtn.setAttribute('tabindex', '-1');

            loginPanel.classList.remove('hidden');
            registerPanel.classList.add('hidden');
        } else {
            registerTabBtn.classList.add('tab-active');
            registerTabBtn.classList.remove('tab-inactive');
            registerTabBtn.setAttribute('aria-selected', 'true');
            registerTabBtn.setAttribute('tabindex', '0');

            loginTabBtn.classList.remove('tab-active');
            loginTabBtn.classList.add('tab-inactive');
            loginTabBtn.setAttribute('aria-selected', 'false');
            loginTabBtn.setAttribute('tabindex', '-1');

            registerPanel.classList.remove('hidden');
            loginPanel.classList.add('hidden');
        }
    }

    if (loginTabBtn && registerTabBtn) {
        loginTabBtn.addEventListener('click', function () { switchTab('login'); });
        registerTabBtn.addEventListener('click', function () { switchTab('register'); });

        // Keyboard arrow navigation for tabs
        loginTabBtn.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                registerTabBtn.focus();
                switchTab('register');
            }
        });

        registerTabBtn.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                loginTabBtn.focus();
                switchTab('login');
            }
        });
    }

    // 2. Role Selection (Student vs Teacher)
    const roleRadios = document.querySelectorAll('input[name="role"]');
    const classContainer = document.getElementById('class-container');
    const teacherNotice = document.getElementById('teacher-notice');
    const classSelect = document.getElementById('reg-class');

    function syncRoleUI(selectedRole) {
        if (!classContainer || !teacherNotice || !classSelect) return;

        if (selectedRole === 'teacher') {
            classContainer.classList.add('hidden');
            teacherNotice.classList.remove('hidden');
            classSelect.removeAttribute('required');
        } else {
            classContainer.classList.remove('hidden');
            teacherNotice.classList.add('hidden');
            classSelect.setAttribute('required', 'required');
        }
    }

    roleRadios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            syncRoleUI(this.value);
        });
        if (radio.checked) {
            syncRoleUI(radio.value);
        }
    });

    // 3. Department & Class Dynamic Select (Safari/iOS compatible DOM rebuilding)
    const deptSelect = document.getElementById('reg-dept');
    const classesDataEl = document.getElementById('classes-data-json');

    if (deptSelect && classSelect && classesDataEl) {
        let classesList = [];
        try {
            classesList = JSON.parse(classesDataEl.textContent);
        } catch (e) {
            console.error('Failed to parse classes JSON:', e);
        }

        const selectedClassId = classSelect.getAttribute('data-old-value') || '';

        function rebuildClassOptions(deptId) {
            const defaultPlaceholder = classSelect.getAttribute('data-placeholder') || '-- Select Class --';
            const selectDeptFirstPlaceholder = classSelect.getAttribute('data-dept-first') || '-- Select Dept First --';

            classSelect.innerHTML = '';

            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = deptId ? defaultPlaceholder : selectDeptFirstPlaceholder;
            classSelect.appendChild(defaultOpt);

            if (!deptId) return;

            const filtered = classesList.filter(function (item) {
                return String(item.department_id) === String(deptId);
            });

            filtered.forEach(function (item) {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.label;
                if (String(item.id) === String(selectedClassId)) {
                    opt.selected = true;
                }
                classSelect.appendChild(opt);
            });
        }

        deptSelect.addEventListener('change', function () {
            rebuildClassOptions(this.value);
        });

        // Initialize on load with existing old value
        if (deptSelect.value) {
            rebuildClassOptions(deptSelect.value);
        }
    }

    // 4. Password Visibility Toggles
    const toggleBtns = document.querySelectorAll('.password-toggle-btn');
    toggleBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                this.setAttribute('aria-pressed', 'true');
                this.textContent = '👁️';
            } else {
                input.type = 'password';
                this.setAttribute('aria-pressed', 'false');
                this.textContent = '👁️‍🗨️';
            }
        });
    });

    // 5. Initial Tab state from URL query or validation error
    const authWrapper = document.getElementById('auth-card-wrapper');
    if (authWrapper) {
        const initialTab = authWrapper.getAttribute('data-initial-tab');
        if (initialTab === 'register') {
            switchTab('register');
        }
    }
});

