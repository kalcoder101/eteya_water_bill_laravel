/**
 * Eteya Water Bill — Global JS utilities
 * - Toast notifications
 * - Custom confirm dialog (replaces native confirm())
 * - Modal open/close helpers
 *
 * Usage:
 *   showToast('Customer saved', 'success');
 *   confirmDialog('Delete this customer?', 'This cannot be undone', 'danger')
 *     .then(ok => { if (ok) doDelete(); });
 */
(function() {
    // ---------- Toast container ----------
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }

    function iconSvg(name) {
        const icons = {
            check:    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
            x:        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
            alert:    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            info:     '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
        };
        return icons[name] || icons.info;
    }

    window.showToast = function(message, type = 'info', duration = 4000) {
        const toast = document.createElement('div');
        toast.className = 'toast ' + type;
        const iconName = type === 'success' ? 'check' : (type === 'error' ? 'x' : (type === 'warning' ? 'alert' : 'info'));
        toast.innerHTML = '<div class="toast-icon">' + iconSvg(iconName) + '</div><div>' + message + '</div>';
        toastContainer.appendChild(toast);
        setTimeout(() => {
            toast.style.transition = 'opacity 0.2s, transform 0.2s';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 200);
        }, duration);
    };

    // ---------- Custom confirm dialog ----------
    let confirmBackdrop = null;
    window.confirmDialog = function(title, text = '', type = 'warning') {
        return new Promise((resolve) => {
            // Remove any existing
            if (confirmBackdrop) confirmBackdrop.remove();

            confirmBackdrop = document.createElement('div');
            confirmBackdrop.className = 'modal-backdrop v2 show';
            const iconClass = type === 'danger' ? 'danger' : '';
            const iconSvgStr = type === 'danger' ? iconSvg('x') : iconSvg('alert');
            confirmBackdrop.innerHTML = `
                <div class="confirm-dialog" onclick="event.stopPropagation()">
                    <div class="confirm-icon ${iconClass}">${iconSvgStr}</div>
                    <div class="confirm-title">${escapeHtml(title)}</div>
                    ${text ? '<div class="confirm-text">' + escapeHtml(text) + '</div>' : ''}
                    <div class="confirm-actions">
                        <button class="confirm-no">Cancel</button>
                        <button class="confirm-yes">Confirm</button>
                    </div>
                </div>
            `;
            document.body.appendChild(confirmBackdrop);

            confirmBackdrop.querySelector('.confirm-no').addEventListener('click', () => {
                confirmBackdrop.remove();
                confirmBackdrop = null;
                resolve(false);
            });
            confirmBackdrop.querySelector('.confirm-yes').addEventListener('click', () => {
                confirmBackdrop.remove();
                confirmBackdrop = null;
                resolve(true);
            });
            confirmBackdrop.addEventListener('click', (e) => {
                if (e.target === confirmBackdrop) {
                    confirmBackdrop.remove();
                    confirmBackdrop = null;
                    resolve(false);
                }
            });
        });
    };

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
        }[c]));
    }

    // ---------- Modal helpers (use .v2 modals) ----------
    window.openModal = function(id) {
        const m = document.getElementById(id);
        if (m) {
            m.classList.add('show');
            m.classList.add('v2');
            const inner = m.querySelector('.modal');
            if (inner) inner.classList.add('v2');
        }
    };
    window.closeModal = function(id) {
        const m = document.getElementById(id);
        if (m) m.classList.remove('show');
    };

    // Override native confirm() to use our custom dialog
    // (Can't override synchronously — only async version. Native confirm still works
    //  for inline onclick handlers; new code should use confirmDialog().)
})();
