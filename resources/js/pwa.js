/**
 * STIC Info - Progressive Web App (PWA) Manager
 * Handles Service Worker registration, mobile install banners, and lifecycle events
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then((registration) => {
                    // Registration successful
                })
                .catch((err) => {
                    console.warn('PWA ServiceWorker registration failed:', err);
                });
        });
    }

    // 2. Mobile Install Prompt Handling (A2HS - Add to Home Screen)
    let deferredInstallPrompt = null;
    const installBanner = document.getElementById('pwa-install-banner');
    const installBtn = document.getElementById('pwa-install-btn');
    const dismissBtn = document.getElementById('pwa-dismiss-btn');

    // Check if user previously dismissed today
    const dismissedAt = localStorage.getItem('stic_pwa_dismissed');
    const isDismissedRecently = dismissedAt && (Date.now() - parseInt(dismissedAt, 10)) < (24 * 60 * 60 * 1000);

    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent default mini-infobar on mobile Chrome
        e.preventDefault();
        deferredInstallPrompt = e;

        // If not dismissed recently, show institutional install pill
        if (installBanner && !isDismissedRecently) {
            installBanner.classList.remove('hidden');
            installBanner.style.display = 'flex';
        }
    });

    if (installBtn) {
        installBtn.addEventListener('click', async () => {
            if (!deferredInstallPrompt) return;

            // Trigger the native Android install prompt
            deferredInstallPrompt.prompt();
            const { outcome } = await deferredInstallPrompt.userChoice;

            if (outcome === 'accepted') {
                if (installBanner) {
                    installBanner.style.display = 'none';
                    installBanner.classList.add('hidden');
                }
            }
            deferredInstallPrompt = null;
        });
    }

    if (dismissBtn) {
        dismissBtn.addEventListener('click', () => {
            if (installBanner) {
                installBanner.style.display = 'none';
                installBanner.classList.add('hidden');
            }
            localStorage.setItem('stic_pwa_dismissed', Date.now().toString());
        });
    }

    window.addEventListener('appinstalled', () => {
        if (installBanner) {
            installBanner.style.display = 'none';
            installBanner.classList.add('hidden');
        }
        deferredInstallPrompt = null;
    });
});
