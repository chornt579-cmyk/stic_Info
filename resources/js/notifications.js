/**
 * STIC Info — Multi-Channel Announcement Alert System
 * Supports:
 * 1. Native HTML5 Browser Web Notifications (Desktop & Mobile Browser)
 * 2. In-App Interactive Floating Audio/Visual Toast Alert
 * 3. Mobile Phone Haptic Vibration Alert
 * 4. Web Audio API institutional harmonic chime
 */

document.addEventListener('DOMContentLoaded', function () {
    const notifBtn = document.getElementById('stic-notif-btn');
    const STORAGE_KEY = 'stic_last_seen_announcement_id';
    const DISMISSED_KEY = 'stic_dismissed_announcement_id';

    /**
     * Synthesize gentle institutional chime using Web Audio API (Zero external audio file needed).
     */
    function playNotificationChime() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;

            const ctx = new AudioContext();
            if (ctx.state === 'suspended') {
                ctx.resume();
            }

            const now = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'sine';
            // Dual harmonic chime: D5 (587.33Hz) -> A5 (880.00Hz)
            osc.frequency.setValueAtTime(587.33, now);
            osc.frequency.setValueAtTime(880.00, now + 0.12);

            gain.gain.setValueAtTime(0.18, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.45);

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.start(now);
            osc.stop(now + 0.45);
        } catch (e) {
            // Audio context gesture restriction handled silently
        }
    }

    /**
     * Trigger mobile phone vibration if supported by device.
     */
    function triggerPhoneVibration() {
        try {
            if (window.navigator && typeof window.navigator.vibrate === 'function') {
                window.navigator.vibrate([200, 100, 200]);
            }
        } catch (e) {}
    }

    /**
     * Send native OS Browser Notification (Desktop Windows/Mac & Mobile Android/iOS).
     */
    function triggerBrowserNotification(announcement) {
        if (!('Notification' in window)) return;

        if (Notification.permission === 'granted') {
            try {
                const notif = new Notification('STIC Info — ' + announcement.title, {
                    body: announcement.snippet + '\n\n' + (announcement.author_name ? 'ពី៖ ' + announcement.author_name : ''),
                    icon: '/images/stic-logo.png',
                    badge: '/images/stic-logo.png',
                    tag: 'stic-announcement-' + announcement.id,
                });

                notif.onclick = function () {
                    window.focus();
                    if (announcement.url) {
                        window.location.href = announcement.url;
                    }
                };
            } catch (e) {
                // Ignore service worker requirement on some mobile browsers
            }
        }
    }

    /**
     * Render interactive In-App Floating Toast Alert on top-right of screen.
     */
    function showInAppToastAlert(announcement) {
        // Prevent duplicate toasts
        const existingToast = document.getElementById('stic-floating-announcement-toast');
        if (existingToast) {
            existingToast.remove();
        }

        const toast = document.createElement('aside');
        toast.id = 'stic-floating-announcement-toast';
        toast.className = 'stic-toast-alert';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.style.cssText = `
            position: fixed;
            top: 72px;
            right: 18px;
            z-index: 99999;
            max-width: 360px;
            width: calc(100vw - 36px);
            background: #ffffff;
            border-left: 4px solid #303A98;
            border-top: 1px solid #E7E9F0;
            border-right: 1px solid #E7E9F0;
            border-bottom: 1px solid #E7E9F0;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(32, 41, 104, 0.14);
            padding: 1rem 1.15rem;
            animation: sticToastSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            font-family: 'Noto Sans Khmer', sans-serif;
        `;

        toast.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #303A98; box-shadow: 0 0 0 3px rgba(48, 58, 152, 0.2);"></span>
                    <strong style="color: #303A98; font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.5px;">សេចក្តីជូនដំណឹងថ្មី (Announcement Alert)</strong>
                </div>
                <button type="button" id="stic-toast-dismiss-btn" aria-label="បិទការជូនដំណឹង" style="background: none; border: none; font-size: 1.25rem; line-height: 1; color: #94a3b8; cursor: pointer; padding: 0 4px;">&times;</button>
            </div>
            <h4 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0 0 0.35rem 0; line-height: 1.4;">
                ${escapeHtml(announcement.title)}
            </h4>
            <p style="font-size: 0.85rem; color: #475569; margin: 0 0 0.75rem 0; line-height: 1.5;">
                ${escapeHtml(announcement.snippet)}
            </p>
            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 0.65rem; font-size: 0.78rem; color: #64748b;">
                <span>${escapeHtml(announcement.author_name || 'STIC')} · ${escapeHtml(announcement.created_at_human || '')}</span>
                <a href="${escapeHtml(announcement.url || '#')}" id="stic-toast-view-link" style="display: inline-flex; align-items: center; gap: 4px; font-weight: 700; color: #303A98; text-decoration: none;">
                    អានលម្អិត &rarr;
                </a>
            </div>
        `;

        document.body.appendChild(toast);

        // Dismiss action
        const dismissBtn = toast.querySelector('#stic-toast-dismiss-btn');
        if (dismissBtn) {
            dismissBtn.addEventListener('click', function () {
                localStorage.setItem(DISMISSED_KEY, announcement.id);
                toast.style.animation = 'sticToastSlideOut 0.3s ease forwards';
                setTimeout(() => toast.remove(), 300);
            });
        }

        // Auto dismiss after 14 seconds
        setTimeout(function () {
            if (document.body.contains(toast)) {
                toast.style.animation = 'sticToastSlideOut 0.3s ease forwards';
                setTimeout(() => toast.remove(), 300);
            }
        }, 14000);
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * Check server for latest announcement.
     */
    function checkForLatestAnnouncement(isInitialCheck = false) {
        fetch('/announcements/latest', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            if (!data.has_announcement || !data.announcement) return;

            const ann = data.announcement;
            const lastSeenId = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10);
            const dismissedId = parseInt(localStorage.getItem(DISMISSED_KEY) || '0', 10);

            if (isInitialCheck && lastSeenId === 0) {
                // First time visit: store current ID so we only alert on future notices
                localStorage.setItem(STORAGE_KEY, ann.id);
                return;
            }

            // If a newer announcement has arrived
            if (ann.id > lastSeenId) {
                localStorage.setItem(STORAGE_KEY, ann.id);

                // 1. Audio Harmonic Chime
                playNotificationChime();

                // 2. Mobile Phone Vibration
                triggerPhoneVibration();

                // 3. Browser Native Notification
                triggerBrowserNotification(ann);

                // 4. In-App Floating Toast Banner
                showInAppToastAlert(ann);
            } else if (isInitialCheck && ann.id > dismissedId) {
                // Show floating banner if user hasn't dismissed it yet
                // showInAppToastAlert(ann);
            }
        })
        .catch(() => {
            // Silently handle offline/network issues
        });
    }

    /**
     * Update Notification Bell UI in Topbar.
     */
    function updateBellButtonState() {
        if (!notifBtn) return;

        if (!('Notification' in window)) {
            notifBtn.style.display = 'none';
            return;
        }

        if (Notification.permission === 'granted') {
            notifBtn.classList.add('notif-granted');
            notifBtn.setAttribute('title', 'ការជូនដំណឹងលើ Browser & Phone: បានបើក');
            const dot = notifBtn.querySelector('.notif-dot');
            if (dot) dot.style.display = 'block';
        } else {
            notifBtn.classList.remove('notif-granted');
            notifBtn.setAttribute('title', 'ចុចដើម្បីបើកការជូនដំណឹងលើ Browser & Phone (Alerts)');
            const dot = notifBtn.querySelector('.notif-dot');
            if (dot) dot.style.display = 'none';
        }
    }

    if (notifBtn) {
        notifBtn.addEventListener('click', function () {
            if (!('Notification' in window)) {
                alert('Browser របស់អ្នកមិនគាំទ្រ Web Notification ឡើយ។');
                return;
            }

            if (Notification.permission === 'granted') {
                playNotificationChime();
                triggerPhoneVibration();
                new Notification('STIC Info — ប្រព័ន្ធជូនដំណឹងសកម្ម', {
                    body: 'ការជូនដំណឹងលើ Browser និងទូរស័ព្ទដៃដំណើរការប្រក្រតី!',
                    icon: '/images/stic-logo.png',
                });
            } else if (Notification.permission !== 'denied') {
                Notification.requestPermission().then(function (permission) {
                    updateBellButtonState();
                    if (permission === 'granted') {
                        playNotificationChime();
                        triggerPhoneVibration();
                        new Notification('STIC Info — ជោគជ័យ!', {
                            body: 'លោកអ្នកនឹងទទួលបាន Alerts រាល់ពេលមានសេចក្តីជូនដំណឹងថ្មីពីវិទ្យាស្ថាន!',
                            icon: '/images/stic-logo.png',
                        });
                    }
                });
            } else {
                alert('ការជូនដំណឹងត្រូវបានបិទ (Blocked) ក្នុង Browser Settings របស់អ្នក។ សូមអនុញ្ញាត Notification សម្រាប់គេហទំព័រនេះ។');
            }
        });
    }

    // Initialize Bell State
    updateBellButtonState();

    // Initial check on page load
    setTimeout(function () {
        checkForLatestAnnouncement(true);
    }, 1500);

    // Periodic Background Poller: every 40 seconds
    setInterval(function () {
        checkForLatestAnnouncement(false);
    }, 40000);
});

