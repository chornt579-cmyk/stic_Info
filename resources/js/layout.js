/**
 * STIC Info — Institutional Layout & Sidebar Controller
 * Accessible, responsive mobile drawer & desktop collapse
 */

document.addEventListener('DOMContentLoaded', function () {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('app-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const appWrapper = document.getElementById('app-wrapper');
    const collapseToggle = document.getElementById('desktop-collapse-toggle');

    // 1. Mobile Drawer Toggle
    if (sidebarToggle && sidebar && overlay) {
        function openMobileSidebar() {
            sidebar.classList.add('mobile-open');
            overlay.classList.add('active');
            sidebarToggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileSidebar() {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
            sidebarToggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        sidebarToggle.addEventListener('click', function () {
            const isOpen = sidebar.classList.contains('mobile-open');
            if (isOpen) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        });

        overlay.addEventListener('click', closeMobileSidebar);

        // Close on ESC key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('mobile-open')) {
                closeMobileSidebar();
            }
        });
    }

    // 2. Desktop Sidebar Collapse
    if (collapseToggle && appWrapper) {
        const STORAGE_KEY = 'stic_sidebar_collapsed';

        // Restore persisted desktop state
        if (window.innerWidth >= 1024) {
            const isCollapsed = localStorage.getItem(STORAGE_KEY) === 'true';
            if (isCollapsed) {
                appWrapper.classList.add('sidebar-collapsed');
            }
        }

        collapseToggle.addEventListener('click', function () {
            appWrapper.classList.toggle('sidebar-collapsed');
            const state = appWrapper.classList.contains('sidebar-collapsed');
            localStorage.setItem(STORAGE_KEY, state ? 'true' : 'false');
        });
    }
});
