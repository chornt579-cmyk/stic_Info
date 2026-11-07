/**
 * STIC Info - Student Portal Scripts
 * WCAG AA accessible and CSP compliant (No inline JS)
 */

document.addEventListener('DOMContentLoaded', function () {
    const studentTabButtons = document.querySelectorAll('[data-student-tab]');
    const studentTabContents = document.querySelectorAll('.student-tab-content');

    function switchStudentTab(tabId) {
        if (!tabId) return;

        studentTabButtons.forEach(btn => {
            if (btn.getAttribute('data-student-tab') === tabId) {
                btn.classList.add('active');
                btn.setAttribute('aria-selected', 'true');
            } else {
                btn.classList.remove('active');
                btn.setAttribute('aria-selected', 'false');
            }
        });

        studentTabContents.forEach(content => {
            if (content.id === tabId) {
                content.classList.remove('hidden');
                content.style.display = 'block';
            } else {
                content.classList.add('hidden');
                content.style.display = 'none';
            }
        });

        if (history.pushState) {
            history.pushState(null, null, '#' + tabId);
        }
    }

    studentTabButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const target = this.getAttribute('data-student-tab');
            switchStudentTab(target);
        });
    });

    // Check URL hash on load
    const hash = window.location.hash ? window.location.hash.substring(1) : '';
    if (hash && document.getElementById(hash)) {
        switchStudentTab(hash);
    }

    // Weekly Schedule Day Filter Pills
    const dayFilterPills = document.querySelectorAll('[data-student-day-filter]');
    const dayScheduleSections = document.querySelectorAll('[data-student-day-section]');

    dayFilterPills.forEach(pill => {
        pill.addEventListener('click', function () {
            const targetDay = this.getAttribute('data-student-day-filter');

            dayFilterPills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');

            dayScheduleSections.forEach(sec => {
                if (targetDay === 'all' || sec.getAttribute('data-student-day-section') === targetDay) {
                    sec.style.display = 'block';
                } else {
                    sec.style.display = 'none';
                }
            });
        });
    });
});
