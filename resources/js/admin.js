/**
 * STIC Info — Admin Dashboard UI Script
 * Clean Event-driven tab management, two-way sidebar sync, and accessible navigation
 */

document.addEventListener('DOMContentLoaded', function () {
    const adminTabBtns = document.querySelectorAll('.admin-tab-btn');
    const adminPanels = document.querySelectorAll('.admin-panel');
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
                link.classList.toggle('active', !tabId || tabId === 'approvals' || tabId === 'main-dashboard');
            }
        });
    }

    function switchAdminTab(targetTabId) {
        if (!targetTabId) return;

        // Support clicking 'main-dashboard' to return to default first tab
        if (targetTabId === 'main-dashboard') {
            targetTabId = 'approvals';
        }

        const targetPanel = document.getElementById('panel-' + targetTabId);
        if (!targetPanel) return;

        // 1. Update horizontal tab buttons
        adminTabBtns.forEach(function (btn) {
            const isMatch = btn.getAttribute('data-tab') === targetTabId;
            btn.setAttribute('aria-selected', isMatch ? 'true' : 'false');
            if (isMatch) {
                btn.classList.add('tab-active');
                btn.classList.remove('tab-inactive');
            } else {
                btn.classList.remove('tab-active');
                btn.classList.add('tab-inactive');
            }
        });

        // 2. Update panel visibility
        adminPanels.forEach(function (panel) {
            if (panel.id === 'panel-' + targetTabId) {
                panel.classList.remove('hidden');
                panel.style.display = 'block';
            } else {
                panel.classList.add('hidden');
                panel.style.display = 'none';
            }
        });

        // 3. Two-way synchronization with vertical sidebar menu
        syncSidebarActive(targetTabId);

        // 4. Close mobile drawer if open
        if (sidebar && sidebar.classList.contains('mobile-open')) {
            sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        // 5. Store active tab in URL hash
        if (history.replaceState) {
            history.replaceState(null, '', '#' + targetTabId);
        } else {
            window.location.hash = targetTabId;
        }
    }

    // A. Listen to horizontal tab button clicks
    adminTabBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tabId = this.getAttribute('data-tab');
            switchAdminTab(tabId);
        });
    });

    // B. Listen to vertical sidebar menu clicks (menu ខាងបញ្ជរ)
    sidebarLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href') || '';
            const hashIndex = href.indexOf('#');
            const dataTab = this.getAttribute('data-sidebar-tab');

            if (hashIndex !== -1) {
                const targetHash = href.substring(hashIndex + 1);
                const panel = document.getElementById('panel-' + targetHash);
                if (panel) {
                    e.preventDefault();
                    switchAdminTab(targetHash);
                    const mainContent = document.querySelector('.app-content') || panel;
                    mainContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } else if (dataTab === 'main-dashboard' && document.getElementById('panel-approvals')) {
                e.preventDefault();
                switchAdminTab('approvals');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });

    // C. Listen to hashchange events (browser Back/Forward navigation)
    window.addEventListener('hashchange', function () {
        const hash = window.location.hash.replace('#', '');
        if (hash && document.getElementById('panel-' + hash)) {
            switchAdminTab(hash);
        }
    });

    // D. Handle initial tab on page load
    const currentHash = window.location.hash.replace('#', '');
    if (currentHash && document.getElementById('panel-' + currentHash)) {
        switchAdminTab(currentHash);
    } else {
        syncSidebarActive('approvals');
    }
});
