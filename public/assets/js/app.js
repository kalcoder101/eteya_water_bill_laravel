/**
 * WaterSteward Enterprise System — Global JS utilities (EOS Modern Steward layer)
 *
 * - GSAP entrance engine (gsap-* hooks, data-gsap-counter/progress)
 * - AJAX SPA navigation (intercepts same-origin <a> clicks)
 * - GSAP modal engine (openGSAPModal / closeGSAPModal)
 * - GSAP toasts (showGSAPToast) + legacy showToast (same container)
 * - Custom confirm dialog (confirmDialog)
 * - Legacy openModal / closeModal (toggles .show)
 * - Sidebar collapse + category accordion + keyboard shortcuts
 *
 * Every GSAP call is guarded with `typeof gsap !== 'undefined'` and has a
 * non-GSAP fallback so the UI still works if the CDN fails.
 */
(function() {
    'use strict';

    var hasGSAP = function() { return typeof gsap !== 'undefined'; };

    // ============================================================
    // Toast container (shared by showToast + showGSAPToast)
    // ============================================================
    var toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }

    function iconSvg(name) {
        var icons = {
            check:    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
            x:        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
            alert:    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            info:     '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
        };
        return icons[name] || icons.info;
    }

    function buildToast(message, type) {
        var toast = document.createElement('div');
        toast.className = 'toast ' + type;
        var iconName = type === 'success' ? 'check' : (type === 'error' || type === 'danger' ? 'x' : (type === 'warning' ? 'alert' : 'info'));
        toast.innerHTML = '<div class="toast-icon">' + iconSvg(iconName) + '</div><div>' + message + '</div>';
        return toast;
    }

    // Legacy toast (CSS-animated, same container)
    window.showToast = function(message, type, duration) {
        if (typeof type === 'undefined') type = 'info';
        if (typeof duration === 'undefined') duration = 4000;
        var toast = buildToast(message, type === 'error' ? 'danger' : type);
        toastContainer.appendChild(toast);
        setTimeout(function() {
            toast.style.transition = 'opacity 0.2s, transform 0.2s';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(function() { toast.remove(); }, 200);
        }, duration);
    };

    // EOS GSAP toast
    window.showGSAPToast = function(message, type) {
        if (typeof type === 'undefined') type = 'success';
        var toast = buildToast(message, type === 'error' ? 'danger' : type);
        toastContainer.appendChild(toast);
        function remove() {
            if (hasGSAP()) {
                gsap.to(toast, { opacity: 0, x: 80, duration: 0.3, ease: 'power2.in', onComplete: function() { toast.remove(); } });
            } else {
                toast.style.opacity = '0';
                setTimeout(function() { toast.remove(); }, 250);
            }
        }
        if (hasGSAP()) {
            gsap.from(toast, { opacity: 0, x: 80, scale: 0.92, duration: 0.35, ease: 'back.out(1.4)', clearProps: 'all' });
        }
        setTimeout(remove, 3800);
    };

    // Auto-fire session success flash toast
    document.addEventListener('DOMContentLoaded', function() {
        var el = document.getElementById('laravelSessionSuccess');
        if (el && el.getAttribute('data-message')) {
            window.showGSAPToast(el.getAttribute('data-message'), 'success');
        }
    });

    // ============================================================
    // Custom confirm dialog (replaces native confirm())
    // ============================================================
    var confirmBackdrop = null;
    window.confirmDialog = function(title, text, type) {
        if (typeof text === 'undefined') text = '';
        if (typeof type === 'undefined') type = 'warning';
        return new Promise(function(resolve) {
            if (confirmBackdrop) confirmBackdrop.remove();

            confirmBackdrop = document.createElement('div');
            confirmBackdrop.className = 'modal-backdrop v2 show';
            var iconName = type === 'danger' ? 'x' : 'alert';
            confirmBackdrop.innerHTML =
                '<div class="confirm-dialog" onclick="event.stopPropagation()">' +
                '  <div class="confirm-icon ' + (type === 'danger' ? 'danger' : '') + '">' + iconSvg(iconName) + '</div>' +
                '  <div class="confirm-title">' + escapeHtml(title) + '</div>' +
                (text ? '<div class="confirm-text">' + escapeHtml(text) + '</div>' : '') +
                '  <div class="confirm-actions">' +
                '    <button class="confirm-no">Cancel</button>' +
                '    <button class="confirm-yes">Confirm</button>' +
                '  </div>' +
                '</div>';
            document.body.appendChild(confirmBackdrop);

            confirmBackdrop.querySelector('.confirm-no').addEventListener('click', function() { confirmBackdrop.remove(); confirmBackdrop = null; resolve(false); });
            confirmBackdrop.querySelector('.confirm-yes').addEventListener('click', function() { confirmBackdrop.remove(); confirmBackdrop = null; resolve(true); });
            confirmBackdrop.addEventListener('click', function(e) {
                if (e.target === confirmBackdrop) { confirmBackdrop.remove(); confirmBackdrop = null; resolve(false); }
            });
        });
    };

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function(c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    // ============================================================
    // Legacy modal helpers (toggle .show on .modal-backdrop)
    // ============================================================
    window.openModal = function(id) {
        var m = document.getElementById(id);
        if (m) {
            m.classList.add('show');
            m.classList.add('v2');
            var inner = m.querySelector('.modal');
            if (inner) inner.classList.add('v2');
        }
    };
    window.closeModal = function(id) {
        var m = document.getElementById(id);
        if (m) m.classList.remove('show');
    };

    // ============================================================
    // EOS GSAP modal engine
    // ============================================================
    window.openGSAPModal = function(id) {
        var modal = document.getElementById(id);
        if (!modal) return;
        if (modal.parentElement !== document.body) document.body.appendChild(modal);
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        var card = modal.querySelector(':scope > *');
        if (hasGSAP()) {
            gsap.fromTo(modal, { opacity: 0 }, { opacity: 1, duration: 0.25, ease: 'power2.out', clearProps: 'opacity' });
            if (card) gsap.fromTo(card, { scale: 0.88, y: 30 }, { scale: 1, y: 0, duration: 0.35, ease: 'back.out(1.4)', clearProps: 'all' });
        }
        setTimeout(function() {
            var input = modal.querySelector('input:not([readonly]), select, textarea');
            if (input) input.focus();
        }, 150);
    };

    window.closeGSAPModal = function(id) {
        var modal = document.getElementById(id);
        if (!modal) return;
        var card = modal.querySelector(':scope > *');
        var done = function() {
            modal.style.display = 'none';
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        };
        if (hasGSAP()) {
            if (card) gsap.to(card, { scale: 0.9, y: 20, opacity: 0, duration: 0.2, ease: 'power2.in', onComplete: done });
            else setTimeout(done, 200);
        } else {
            done();
        }
    };

    document.addEventListener('click', function(e) {
        var modal = e.target;
        if (modal.classList && modal.classList.contains('modal-backdrop') && modal.classList.contains('show')) {
            window.closeModal(modal.id || undefined);
        }
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.show').forEach(function(m) {
                window.closeModal(m.id);
            });
            if (confirmBackdrop) { confirmBackdrop.remove(); confirmBackdrop = null; }
        }
    });

    // ============================================================
    // GSAP entrance engine (declarative hooks)
    // ============================================================
    window.initGlobalGSAPAnimations = function() {
        if (!hasGSAP()) return;

        if (document.querySelector('.gsap-hero')) {
            gsap.from('.gsap-hero', { opacity: 0, y: -20, duration: 0.6, ease: 'power2.out', clearProps: 'all' });
        }
        if (document.querySelectorAll('.gsap-stat-card').length) {
            gsap.from('.gsap-stat-card', { opacity: 0, y: 20, scale: 0.96, stagger: 0.08, duration: 0.5, ease: 'back.out(1.2)', clearProps: 'all' });
        }
        if (document.querySelectorAll('.gsap-chart-card').length) {
            gsap.from('.gsap-chart-card', { opacity: 0, scale: 0.94, y: 15, stagger: 0.1, duration: 0.6, ease: 'power2.out', clearProps: 'all' });
        }
        if (document.querySelectorAll('.gsap-section-card').length) {
            gsap.from('.gsap-section-card', { opacity: 0, y: 25, stagger: 0.12, duration: 0.6, ease: 'power2.out', clearProps: 'all' });
        }

        document.querySelectorAll('[data-gsap-counter]').forEach(function(el) {
            var target = parseFloat(el.getAttribute('data-target-val') || '0');
            if (isNaN(target) || target === 0) return;
            var obj = { val: 0 };
            gsap.to(obj, {
                val: target,
                duration: 1.2,
                ease: 'power1.out',
                onUpdate: function() { el.innerText = Math.round(obj.val).toLocaleString(); }
            });
        });

        document.querySelectorAll('.gsap-progress-bar').forEach(function(el) {
            var width = el.getAttribute('data-target-width') || '0%';
            gsap.to(el, { width: width, duration: 0.8, ease: 'power2.out', delay: 0.2 });
        });
    };

    // Hover lift for .gsap-hover-card (de-duped via dataset)
    function initHoverCards() {
        if (!hasGSAP()) return;
        document.querySelectorAll('.gsap-hover-card').forEach(function(card) {
            if (card.dataset.gsapHoverAttached) return;
            card.dataset.gsapHoverAttached = '1';
            card.addEventListener('mouseenter', function() {
                gsap.to(card, { y: -4, duration: 0.2, ease: 'power1.out' });
            });
            card.addEventListener('mouseleave', function() {
                gsap.to(card, { y: 0, duration: 0.2, ease: 'power1.out' });
            });
        });
    }

    // ============================================================
    // Sidebar collapse + category accordion
    // ============================================================
    window.toggleSidebar = function(e) {
        if (e && e.preventDefault) {
            e.preventDefault();
            e.stopPropagation();
        }
        var shell = document.querySelector('.app-shell');
        if (!shell) return;
        var collapsed = shell.classList.toggle('sidebar-collapsed');
        localStorage.setItem('watersteward_sidebar_collapsed', collapsed ? 'true' : 'false');
    };

    window.toggleCategoryGroup = function(headerEl) {
        var shell = document.querySelector('.app-shell');
        if (shell && shell.classList.contains('sidebar-collapsed')) {
            shell.classList.remove('sidebar-collapsed');
            localStorage.setItem('watersteward_sidebar_collapsed', 'false');
        }

        var group = headerEl.closest('.sidebar-category-group');
        if (!group) return;
        var isAlreadyOpen = group.classList.contains('open');
        document.querySelectorAll('.sidebar-category-group').forEach(function(g) {
            g.classList.remove('open');
            var h = g.querySelector('.sidebar-category-header');
            if (h) h.setAttribute('aria-expanded', 'false');
        });
        if (!isAlreadyOpen) {
            group.classList.add('open');
            headerEl.setAttribute('aria-expanded', 'true');
        }
    };

    // Global keyboard shortcuts (Ctrl+B sidebar, Ctrl+K quick search)
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
            e.preventDefault();
            window.toggleSidebar();
        }
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            var input = document.getElementById('cmdSearchInput');
            if (input) input.focus();
        }
    });

    // Auto-apply saved sidebar state
    document.addEventListener('DOMContentLoaded', function() {
        if (localStorage.getItem('watersteward_sidebar_collapsed') === 'true') {
            var shell = document.querySelector('.app-shell');
            if (shell) shell.classList.add('sidebar-collapsed');
        }
    });

    // ============================================================
    // AJAX SPA navigation
    // ============================================================
    function isSkipLink(a) {
        var href = a.getAttribute('href') || '';
        if (href.charAt(0) === '#' || href.indexOf('javascript:') === 0) return true;
        if (a.target === '_blank') return true;
        if (a.hasAttribute('download')) return true;
        if (href.indexOf('/logout') !== -1) return true;
        if (/\.(pdf|png|jpg|jpeg|gif|svg|zip)(\?|$)/i.test(href)) return true;
        return false;
    }

    function ajaxNavigateTo(url) {
        var main = document.querySelector('.content');
        if (!main) { window.location.href = url; return; }

        function fail() { window.location.href = url; }

        var fadeOut = function(cb) {
            if (hasGSAP()) {
                gsap.to(main, { opacity: 0, y: -6, duration: 0.15, ease: 'power1.out', onComplete: cb });
            } else {
                main.style.opacity = '0';
                setTimeout(cb, 120);
            }
        };

        fadeOut(function() {
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
                .then(function(res) { if (!res.ok) throw new Error('bad status'); return res.text(); })
                .then(function(html) {
                    var doc = new DOMParser().parseFromString(html, 'text/html');
                    var title = doc.querySelector('title');
                    if (title) document.title = title.textContent;

                    var newMain = doc.querySelector('.content');
                    if (!newMain) { fail(); return; }

                    // Re-run scripts inside the new main (needed for chart init etc.)
                    var scriptList = Array.prototype.slice.call(newMain.querySelectorAll('script'));
                    scriptList.forEach(function(s) { s.remove(); });

                    main.innerHTML = newMain.innerHTML;

                    // Sync floating chrome (topbar + sidebar) so titles/actions/
                    // active nav states stay correct after SPA navigation.
                    var docLang = doc.documentElement ? doc.documentElement.lang : '';
                    if (docLang) document.documentElement.lang = docLang;

                    var topbar = document.querySelector('.topbar');
                    var newTopbar = doc.querySelector('.topbar');
                    if (topbar && newTopbar) topbar.innerHTML = newTopbar.innerHTML;
                    var sidebar = document.getElementById('mainSidebar');
                    var newSidebar = doc.getElementById('mainSidebar');
                    if (sidebar && newSidebar) sidebar.innerHTML = newSidebar.innerHTML;

                    scriptList.forEach(function(s) {
                        var ns = document.createElement('script');
                        if (s.src) { ns.src = s.src; }
                        else { ns.textContent = s.textContent; }
                        main.appendChild(ns);
                    });

                    document.dispatchEvent(new Event('DOMContentLoaded'));
                    window.dispatchEvent(new Event('DOMContentLoaded'));
                    initGlobalGSAPAnimations();
                    initHoverCards();

                    if (hasGSAP()) {
                        gsap.fromTo(main, { opacity: 0, y: 10 }, { opacity: 1, y: 0, duration: 0.25, ease: 'power2.out', clearProps: 'all' });
                    } else {
                        main.style.opacity = '1';
                    }
                    window.scrollTo(0, 0);
                })
                .catch(fail);
        });
    }

    document.addEventListener('click', function(e) {
        var a = e.target.closest ? e.target.closest('a[href]') : null;
        if (!a) return;
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
        if (isSkipLink(a)) return;
        var href = a.href;
        if (href.indexOf(window.location.origin) !== 0) return;
        e.preventDefault();
        history.pushState({ url: href }, '', href);
        ajaxNavigateTo(href);
    });

    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.url) ajaxNavigateTo(e.state.url);
    });

    // Floating chrome entrance (topbar + sidebar) — runs once on first paint
    var chromeAnimated = false;
    function initFloatingChrome() {
        if (chromeAnimated || !hasGSAP()) return;
        chromeAnimated = true;
        var topbar = document.querySelector('.topbar');
        var sidebar = document.getElementById('mainSidebar');
        if (topbar) {
            gsap.from(topbar, { opacity: 0, y: -14, duration: 0.5, ease: 'power2.out', delay: 0.35, clearProps: 'all' });
        }
        if (sidebar) {
            gsap.from(sidebar, { opacity: 0, x: -16, duration: 0.5, ease: 'power2.out', delay: 0.35, clearProps: 'all' });
        }
    }

    // ============================================================
    // Bootstrap
    // ============================================================
    function bootstrap() {
        initFloatingChrome();
        initGlobalGSAPAnimations();
        initHoverCards();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootstrap);
    } else {
        bootstrap();
    }
})();
