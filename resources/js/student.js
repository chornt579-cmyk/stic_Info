/**
 * STIC Info - Student Portal Scripts
 * WCAG AA accessible, two-way sidebar sync, and CSP compliant
 */

document.addEventListener('DOMContentLoaded', function () {
    const studentTabButtons = document.querySelectorAll('[data-student-tab]');
    const studentTabContents = document.querySelectorAll('.student-tab-content');
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    const overlay = document.getElementById('sidebar-overlay');
    const sidebar = document.getElementById('app-sidebar');

    function syncSidebarActive(tabId) {
        sidebarLinks.forEach(function (link) {
            const linkHref = link.getAttribute('href') || '';
            const linkTab = link.getAttribute('data-sidebar-tab');

            const isMatch = linkTab === tabId || linkHref.endsWith('#' + tabId);
            if (isMatch) {
                link.classList.add('active');
            } else if (linkTab !== 'main-dashboard') {
                link.classList.remove('active');
            } else {
                link.classList.toggle('active', !tabId || tabId === 'tab-overview' || tabId === 'main-dashboard');
            }
        });
    }

    function scrollToSection(targetTabId) {
        if (!targetTabId || targetTabId === 'tab-overview' || targetTabId === 'main-dashboard') {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        const tabsContainer = document.querySelector('.student-tabs') || document.getElementById(targetTabId);
        if (tabsContainer) {
            const topbarOffset = 76;
            const topPos = tabsContainer.getBoundingClientRect().top + window.pageYOffset;
            window.scrollTo({
                top: Math.max(0, topPos - topbarOffset),
                behavior: 'smooth'
            });
        }
    }

    function switchStudentTab(tabId, shouldScroll = false) {
        if (!tabId) return;

        if (tabId === 'main-dashboard') {
            tabId = 'tab-overview';
        }

        const targetContent = document.getElementById(tabId);
        if (!targetContent) return;

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

        syncSidebarActive(tabId);

        if (sidebar && sidebar.classList.contains('mobile-open')) {
            sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (history.replaceState) {
            history.replaceState(null, null, '#' + tabId);
        } else {
            window.location.hash = tabId;
        }

        if (shouldScroll) {
            scrollToSection(tabId);
        }
    }

    studentTabButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const target = this.getAttribute('data-student-tab');
            switchStudentTab(target, true);
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
                    switchStudentTab(targetHash, true);
                }
            } else if (dataTab === 'main-dashboard' && document.getElementById('tab-overview')) {
                e.preventDefault();
                switchStudentTab('tab-overview', true);
            }
        });
    });

    window.addEventListener('hashchange', function () {
        const hash = window.location.hash.replace('#', '');
        if (hash && document.getElementById(hash)) {
            switchStudentTab(hash);
        }
    });

    // Check URL hash on load
    const hash = window.location.hash ? window.location.hash.substring(1) : '';
    if (hash && document.getElementById(hash)) {
        switchStudentTab(hash);
    } else {
        syncSidebarActive('tab-overview');
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
