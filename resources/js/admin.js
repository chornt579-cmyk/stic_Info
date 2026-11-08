/**
 * STIC Info — Admin Dashboard UI Script
 * Clean Event-driven tab management, two-way sidebar sync, direct smooth scroll jumping,
 * and collapsible on-demand creation forms.
 */

document.addEventListener('DOMContentLoaded', function () {
    const adminTabBtns = document.querySelectorAll('.admin-tab-btn');
    const adminPanels = document.querySelectorAll('.admin-panel');
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    const overlay = document.getElementById('sidebar-overlay');
    const sidebar = document.getElementById('app-sidebar');
    const tabsNav = document.getElementById('admin-tabs-nav');

    function syncSidebarActive(tabId) {
        sidebarLinks.forEach(function (link) {
            const linkHref = link.getAttribute('href') || '';
            const linkTab = link.getAttribute('data-sidebar-tab');

            const isMatch = linkTab === tabId || linkHref.endsWith('#' + tabId);
            if (isMatch) {
                link.classList.add('active');
            } else if (linkTab !== 'main-dashboard' && linkTab !== 'overview') {
                link.classList.remove('active');
            } else {
                link.classList.toggle('active', !tabId || tabId === 'overview' || tabId === 'main-dashboard');
            }
        });
    }

    function scrollDirectlyToSection(targetTabId) {
        if (!targetTabId || targetTabId === 'overview' || targetTabId === 'main-dashboard') {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        if (tabsNav) {
            const topbarOffset = 76; // 68px sticky header + 8px spacing
            const navTop = tabsNav.getBoundingClientRect().top + window.pageYOffset;
            window.scrollTo({
                top: Math.max(0, navTop - topbarOffset),
                behavior: 'smooth'
            });
        }
    }

    function switchAdminTab(targetTabId, shouldScroll = true) {
        if (!targetTabId) return;

        // Support clicking 'main-dashboard' to return to default Overview tab
        if (targetTabId === 'main-dashboard') {
            targetTabId = 'overview';
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

        // 5. Store active tab in URL hash without page reload
        if (history.replaceState) {
            history.replaceState(null, '', '#' + targetTabId);
        } else {
            window.location.hash = targetTabId;
        }

        // 6. Direct Smooth Scroll Jump to Content
        if (shouldScroll) {
            scrollDirectlyToSection(targetTabId);
        }
    }

    // A. Listen to horizontal tab button clicks
    adminTabBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tabId = this.getAttribute('data-tab');
            switchAdminTab(tabId, true);
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
                    switchAdminTab(targetHash, true);
                }
            } else if ((dataTab === 'main-dashboard' || dataTab === 'overview') && document.getElementById('panel-overview')) {
                e.preventDefault();
                switchAdminTab('overview', true);
            }
        });
    });

    // C. Listen to Quick Jump buttons (e.g. inside Overview cards)
    document.querySelectorAll('.quick-jump-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const jumpTab = this.getAttribute('data-jump-tab');
            if (jumpTab) {
                switchAdminTab(jumpTab, true);
            }
        });
    });

    // D. Listen to Collapsible Create Form Toggle Buttons
    document.querySelectorAll('.toggle-create-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const card = document.getElementById(targetId);
            if (card) {
                const isHidden = card.style.display === 'none' || card.classList.contains('hidden');
                if (isHidden) {
                    card.style.display = 'block';
                    card.classList.remove('hidden');
                    // Focus on first input
                    const firstInput = card.querySelector('input:not([type="hidden"]), select, textarea');
                    if (firstInput) {
                        firstInput.focus();
                    }
                    card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    card.style.display = 'none';
                    card.classList.add('hidden');
                }
            }
        });
    });

    // E. Listen to Collapsible Create Form Cancel Buttons
    document.querySelectorAll('.close-create-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const card = document.getElementById(targetId);
            if (card) {
                card.style.display = 'none';
                card.classList.add('hidden');
            }
        });
    });

    // F. Listen to hashchange events (browser Back/Forward navigation)
    window.addEventListener('hashchange', function () {
        const hash = window.location.hash.replace('#', '');
        if (hash && document.getElementById('panel-' + hash)) {
            switchAdminTab(hash, true);
        }
    });

    // G. Handle initial tab on page load
    const initialHash = window.location.hash.replace('#', '');
    if (initialHash && document.getElementById('panel-' + initialHash)) {
        switchAdminTab(initialHash, false);
    } else {
        switchAdminTab('overview', false);
    }
});