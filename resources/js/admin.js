/**
 * STIC Info — Admin Dashboard UI Script
 * Clean Event-driven tab management and accessible modals
 */

document.addEventListener('DOMContentLoaded', function () {
    const adminTabBtns = document.querySelectorAll('.admin-tab-btn');
    const adminPanels = document.querySelectorAll('.admin-panel');

    function switchAdminTab(targetTabId) {
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

        adminPanels.forEach(function (panel) {
            if (panel.id === 'panel-' + targetTabId) {
                panel.classList.remove('hidden');
            } else {
                panel.classList.add('hidden');
            }
        });

        // Store active tab in URL hash or session
        if (history.replaceState) {
            history.replaceState(null, '', '#' + targetTabId);
        }
    }

    adminTabBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tabId = this.getAttribute('data-tab');
            switchAdminTab(tabId);
        });
    });

    // Handle initial tab from URL hash
    const currentHash = window.location.hash.replace('#', '');
    if (currentHash && document.getElementById('panel-' + currentHash)) {
        switchAdminTab(currentHash);
    }
});
