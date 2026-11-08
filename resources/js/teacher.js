/**
 * STIC Info - Teacher Dashboard Scripts
 * WCAG AA accessible, two-way sidebar sync, and CSP compliant
 */

document.addEventListener('DOMContentLoaded', function () {
    const teacherTabButtons = document.querySelectorAll('[data-teacher-tab]');
    const teacherTabContents = document.querySelectorAll('.teacher-tab-content');
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    const overlay = document.getElementById('sidebar-overlay');
    const sidebar = document.getElementById('app-sidebar');

    function syncSidebarActive(tabId) {
        const activeTab = (!tabId || tabId === 'main-dashboard') ? 'tab-overview' : tabId;

        sidebarLinks.forEach(function (link) {
            const linkHref = link.getAttribute('href') || '';
            const linkTab = link.getAttribute('data-sidebar-tab');

            const isMatch = linkTab === activeTab || linkHref.endsWith('#' + activeTab);
            if (isMatch) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    }

    function scrollToSection(targetTabId) {
        if (!targetTabId || targetTabId === 'tab-overview' || targetTabId === 'main-dashboard') {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        const tabsContainer = document.querySelector('.teacher-tabs') || document.getElementById(targetTabId);
        if (tabsContainer) {
            const topbarOffset = 76;
            const topPos = tabsContainer.getBoundingClientRect().top + window.pageYOffset;
            window.scrollTo({
                top: Math.max(0, topPos - topbarOffset),
                behavior: 'smooth'
            });
        }
    }

    function switchTeacherTab(tabId, shouldScroll = false) {
        if (!tabId) return;

        if (tabId === 'main-dashboard') {
            tabId = 'tab-overview';
        }

        const targetContent = document.getElementById(tabId);
        if (!targetContent) return;

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

        syncSidebarActive(tabId);

        if (sidebar && sidebar.classList.contains('mobile-open')) {
            sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (history.replaceState) {
            history.replaceState(null, null, window.location.pathname + window.location.search + '#' + tabId);
        } else {
            window.location.hash = tabId;
        }

        if (shouldScroll) {
            scrollToSection(tabId);
        }
    }

    teacherTabButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const target = this.getAttribute('data-teacher-tab');
            switchTeacherTab(target, true);
        });
    });

    sidebarLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href') || '';
            const hashIndex = href.indexOf('#');
            const dataTab = this.getAttribute('data-sidebar-tab');

            if (hashIndex !== -1) {
                const targetHash = href.substring(hashIndex + 1);
                if (document.getElementById(targetHash)) {
                    e.preventDefault();
                    switchTeacherTab(targetHash, true);
                }
            } else if (dataTab === 'main-dashboard' && document.getElementById('tab-overview')) {
                e.preventDefault();
                switchTeacherTab('tab-overview', true);
            }
        });
    });

    window.addEventListener('hashchange', function () {
        const hash = window.location.hash.replace('#', '');
        if (hash && document.getElementById(hash)) {
            switchTeacherTab(hash);
        }
    });

    // Check URL hash or query params on load
    const urlParams = new URLSearchParams(window.location.search);
    const hash = window.location.hash ? window.location.hash.substring(1) : '';

    if (hash && document.getElementById(hash)) {
        switchTeacherTab(hash, hash !== 'tab-overview');
    } else if (urlParams.get('tab') && document.getElementById(urlParams.get('tab'))) {
        switchTeacherTab(urlParams.get('tab'), true);
    } else {
        syncSidebarActive('tab-overview');
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
