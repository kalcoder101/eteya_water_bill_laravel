---
name: eos-sidebar
description: Use when building, modifying, or debugging the navigation sidebar in the EOS platform — the organizer workspace sidebar (layouts/app.blade.php) or the super-admin sidebar (layouts/admin.blade.php). Covers the floating fixed sidebar shell, brand header, standalone nav items, nav-group accordions, category accent colors, hover flyouts, collapse/expand behavior, localStorage state, and the sidebar-collapsed CSS contract. Trigger keywords: sidebar, nav, navigation, menu, accordion, flyout, collapse, nav-group, toggleSidebar, toggleNavGroup, hamburger.
---

# EOS Sidebar Navigation

EOS has two sidebar variants sharing one shell and interaction model:
- **Organizer workspace** — `resources/views/layouts/app.blade.php` (`<aside id="mainSidebar">`).
- **Super-admin panel** — `resources/views/layouts/admin.blade.php` (same `<aside id="mainSidebar">`).

Both are floating, fixed, card-style sidebars. Do not reinvent them; extend the existing markup when adding a module.

## Shell structure

```html
<aside id="mainSidebar" class="w-64 border border-slate-200 bg-white flex flex-col justify-between fixed top-3 left-3 bottom-3 z-50 rounded-xl shadow-card overflow-hidden hidden md:flex transition-all duration-300 ease-in-out">
  <!-- 1. Brand header -->
  <div class="h-16 border-b border-slate-200 px-4 flex items-center justify-between sidebar-brand-container shrink-0 bg-white">
    <div class="flex items-center space-x-3 overflow-hidden">
      <div class="w-9 h-9 rounded-lg bg-emerald-50 border border-emerald-200 p-1 shadow-xs shrink-0 flex items-center justify-center">
        <img src="{{ asset('assets/images/app_icon.png') }}" alt="EOS Logo" class="w-full h-full object-contain">
      </div>
      <div class="sidebar-brand-text truncate">
        <h1 class="text-base font-serif font-bold tracking-tight text-slate-900 flex items-center gap-1.5 leading-tight">
          EOS <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-sans border border-emerald-300 font-bold uppercase tracking-wider">Concierge</span>
        </h1>
        <p class="text-[10px] font-sans text-slate-500 truncate">Event Management Platform</p>
      </div>
    </div>
    <button onclick="toggleSidebar()" title="Toggle Sidebar" class="p-1.5 rounded bg-slate-50 hover:bg-emerald-50 border border-slate-200 text-slate-600 hover:text-slate-900 transition flex items-center justify-center shrink-0">
      <span id="toggleIcon" class="material-symbols-outlined text-base">menu_open</span>
    </button>
  </div>

  <!-- 2. Navigation list -->
  <div class="p-3 space-y-1.5 overflow-y-auto flex-1 bg-white">...</div>

  <!-- 3. User footer -->
  <div class="p-3 border-t border-slate-200 shrink-0 bg-white sidebar-user-footer">...</div>
</aside>
```

- Fixed with `top-3 left-3 bottom-3`, `rounded-xl shadow-card`, `border border-slate-200`. Hidden below `md` (`hidden md:flex`); a hamburger in the mobile header calls `toggleMobileSidebar()` (organizer) to show it.
- Width `w-64` expanded, `4.5rem` collapsed.

## Nav item types

### Standalone item (single link, no children)
```html
<div class="relative sidebar-item-wrapper">
  <a href="{{ route('dashboard') }}" title="..." class="sidebar-standalone w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition group {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 hover:bg-emerald-50 hover:text-emerald-900' }}">
    <span class="material-symbols-outlined sidebar-icon shrink-0 text-emerald-600">dashboard</span>
    <span class="sidebar-text truncate">{{ __('nav.dashboard') }}</span>
  </a>
  <div class="nav-flyout"><!-- flyout mirror of the link --></div>
</div>
```

### Nav group (accordion with children)
```html
<div class="nav-group relative sidebar-item-wrapper {{ request()->routeIs('guests.*') ? 'open' : '' }}" data-group="guests">
  <button type="button" onclick="toggleNavGroup(this)" class="nav-group-trigger w-full flex items-center px-3 py-2.5 rounded-lg text-xs font-bold text-slate-700 hover:bg-sky-50 hover:text-sky-900 transition">
    <span class="material-symbols-outlined sidebar-icon shrink-0 text-sky-600">groups</span>
    <span class="nav-group-text ml-3 flex-1 text-left uppercase tracking-wider text-[10px] text-slate-400 font-bold">{{ __('nav.guests') }}</span>
    <span class="material-symbols-outlined text-sm nav-group-arrow">chevron_right</span>
  </button>
  <div class="nav-group-children pl-3 space-y-1 mt-1">
    <a href="{{ route('guests.index') }}" title="..." class="w-full flex items-center space-x-3 px-3 py-2 rounded-lg text-xs font-semibold transition group {{ request()->routeIs('guests.*') ? 'bg-sky-600 text-white shadow-xs' : 'text-slate-600 hover:bg-sky-50 hover:text-sky-900' }}">
      <span class="material-symbols-outlined text-lg shrink-0">group</span>
      <span class="truncate">{{ __('nav.guests') }}</span>
    </a>
  </div>
  <div class="nav-flyout space-y-1"><!-- flyout mirror with group label + links --></div>
</div>
```

Rules:
- Group trigger is a `<button>` with `onclick="toggleNavGroup(this)"`; children in `.nav-group-children`.
- Open the group in markup when any child route is active: `{{ request()->routeIs('guests.*') || request()->routeIs('program.*') ? 'open' : '' }}`.
- Trigger label: `text-[10px] uppercase tracking-wider text-slate-400 font-bold` (uppercase category header). Child links: `text-xs font-semibold`.
- Active child = category accent background + white text; inactive = `text-slate-600 hover:bg-{accent}-50 hover:text-{accent}-900`.
- Separators between logical sections: `<div class="py-1 px-3"><div class="border-t border-slate-200"></div></div>`.

## Category accent color map

Each group uses a fixed accent for its icon, hover, active state, and flyout:

| Module | Accent | Example classes |
|---|---|---|
| Dashboard / Settings / standalone | emerald | `bg-emerald-600 text-white`, `hover:bg-emerald-50` |
| Guests / Program / Calendar | sky | `bg-sky-600 text-white`, `hover:bg-sky-50`, icon `text-sky-600` |
| Catering / Shopping | amber | `bg-amber-600 text-white`, `hover:bg-amber-50`, icon `text-amber-600` |
| Budget / Vendors | indigo | `bg-indigo-600 text-white`, `hover:bg-indigo-50`, icon `text-indigo-600` |
| Tasks | violet/purple | `bg-purple-600 text-white`, `hover:bg-purple-50`, icon `text-purple-600` |
| Admin: Tenants / Billing / System | emerald | admin uses emerald for all groups (icons `text-emerald-600`, trigger text `text-slate-600`) |

Icons: Material Symbols Outlined — `dashboard`, `groups`, `restaurant_menu`, `shopping_cart`, `account_balance_wallet`, `storefront`, `task_alt`/`checklist`, `event_note`, `calendar_month`, `settings`, `manage_accounts`, `payments`/`receipt_long`, `tune`, `space_dashboard`.

## Collapse behavior

- `toggleSidebar()` toggles `sidebar-collapsed` on `document.body`, flips `#toggleIcon` between `menu_open` / `menu`, and persists state to localStorage (`admin_sidebar_collapsed` on admin layout, `sidebar_state` on organizer layout). Restore it in a `DOMContentLoaded` handler.
- The CSS contract (`body.sidebar-collapsed ...`), present in both layouts:
  - `#mainSidebar { width: 4.5rem !important; }`
  - Organizer: `#mainContentWrapper` moves from `md:ml-64` to `md:ml-20`; admin: `#topHeader { left: 5.75rem !important; right: 1rem !important; }` and `#mainWorkspace { margin-left: 5.75rem !important; }`.
  - Hide `.sidebar-brand-text`, `.nav-group-text`, `.nav-group-children`, `.nav-group-arrow`, `.sidebar-user-info`, `.sidebar-text`.
  - `.sidebar-brand-container` and the user footer stack vertically (column, centered, tight padding).
  - `.sidebar-icon` grows to `1.6rem`; standalone items and group triggers center with `height: 2.75rem` and zero horizontal padding.
  - Admin `.nav-flyout` only displays on hover while collapsed; organizer same via `.sidebar-collapsed .sidebar-item-wrapper:hover .nav-flyout`.
- When collapsed, `toggleNavGroup` is a no-op (`if (document.body.classList.contains('sidebar-collapsed')) return;`).

## Flyouts (collapsed hover menus)

- Each `.sidebar-item-wrapper` contains a `.nav-flyout` mirror: `position: fixed; left: 5rem; min-width: 205px; background #fff; border 1px solid #E2E8F0; border-radius 0.75rem; box-shadow 0 10px 30px -5px rgba(16,185,129,0.12), 0 4px 6px -2px rgba(16,185,129,0.05); padding 0.6rem; z-index 9999`.
- A `::before` pseudo-element (`top:0; left:-16px; width:20px; height:100%`) bridges the hover gap.
- JS attaches `mouseenter`/`mouseleave` on wrapper + flyout; only opens while sidebar is collapsed; positions via `wrapper.getBoundingClientRect().top` (clamped to `>= 10px`); closes after a 200ms timer; only one flyout open at a time.
- Group flyouts include the category label header (`text-[10px] font-bold uppercase tracking-wider` with `border-b`) followed by the same links as the accordion.

## User footer

- Organizer: avatar initial chip + name/email, wraps the whole row in a link to `profile.show`; logout is a rose ghost icon button (`bg-rose-50 hover:bg-rose-600`).
- Admin: emerald avatar chip with initials derived from name (`$adminInitials`), name + "Super Administrator", logout button, and a "Switch to Organizer View" link.
- Both use `sidebar-user-info` / `sidebar-brand-text` classes so they collapse correctly.

## Rules

1. Always keep the `.sidebar-item-wrapper` + `.nav-flyout` pairing — it is what makes collapsed flyouts work.
2. Use `request()->routeIs(...)` (wildcards like `guests.*`) to drive active/open states.
3. Match the module accent exactly across icon, trigger, hover, active, and flyout.
4. Keep every nav label through `{{ __('nav.*') }}` keys (see `eos-localization`).
5. Never change collapse geometry outside the `body.sidebar-collapsed` CSS block; both layouts must stay in sync.
6. Sidebar JS is inline in the layout under `data-layout-core="true"` (organizer) — don't duplicate it in views.
