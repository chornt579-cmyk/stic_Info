/**
 * STIC Info - Teacher Dashboard Scripts
 * WCAG AA accessible and CSP compliant (No inline JS)
 */

document.addEventListener('DOMContentLoaded', function () {
    const teacherTabButtons = document.querySelectorAll('[data-teacher-tab]');
    const teacherTabContents = document.querySelectorAll('.teacher-tab-content');

    function switchTeacherTab(tabId) {
        if (!tabId) return;

        teacherTabButtons.forEach(btn => {
            if (btn.getAttribute('data-teacher-tab') === tabId) {
                btn.classList.add('active');
                btn.setAttribute('aria-selected', 'true');
            } else {
                btn.classList.remove('active');
                btn.setAttribute('aria-selected', 'false');
            }
        });

        teacherTabContents.forEach(content => {
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

    teacherTabButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const target = this.getAttribute('data-teacher-tab');
            switchTeacherTab(target);
        });
    });

    // Check URL hash on load
    const hash = window.location.hash ? window.location.hash.substring(1) : '';
    if (hash && document.getElementById(hash)) {
        switchTeacherTab(hash);
    }

    // Weekly Schedule Day Filter Pills
    const dayFilterPills = document.querySelectorAll('[data-day-filter]');
    const dayScheduleSections = document.querySelectorAll('[data-day-section]');

    dayFilterPills.forEach(pill => {
        pill.addEventListener('click', function () {
            const targetDay = this.getAttribute('data-day-filter');

            dayFilterPills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');

            dayScheduleSections.forEach(sec => {
                if (targetDay === 'all' || sec.getAttribute('data-day-section') === targetDay) {
                    sec.style.display = 'block';
                } else {
                    sec.style.display = 'none';
                }
            });
        });
    });

    // Quick action: Post announcement to specific class from Classes tab
    const announceClassButtons = document.querySelectorAll('[data-announce-class-id]');
    const classSelectInput = document.getElementById('announcement_class_id');

    announceClassButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const classId = this.getAttribute('data-announce-class-id');
            if (classSelectInput) {
                classSelectInput.value = classId;
            }
            switchTeacherTab('tab-announcements');
            const titleInput = document.getElementById('announcement_title');
            if (titleInput) {
                titleInput.focus();
            }
        });
    });

    // Delete announcement confirm guard
    const deleteAnnouncementForms = document.querySelectorAll('.delete-announcement-form');
    deleteAnnouncementForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            const confirmMsg = this.getAttribute('data-confirm') || 'Are you sure you want to delete this announcement?';
            if (!confirm(confirmMsg)) {
                e.preventDefault();
            }
        });
    });
});
