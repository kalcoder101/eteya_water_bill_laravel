// WaterSteward Enterprise — Vite entry point.
// Alpine.js is bundled and started by Livewire 4.

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

window.openModal = function(id) {
    var m = document.getElementById(id);
    if (m) m.classList.add('show');
};

window.closeModal = function(id) {
    var m = document.getElementById(id);
    if (m) m.classList.remove('show');
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
