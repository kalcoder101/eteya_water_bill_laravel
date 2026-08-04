# Eteya Water Bill — Modern Steward Design System (design.md)

A utility-first Tailwind CSS 4 implementation inspired by the **EOS Architecture (Modern Steward)**. Styling is authored as Tailwind utilities directly in Blade views; `public/assets/css/app.css` only carries what utilities cannot express (base rules, sidebar contract, print styles, JS-dependent classes). Fonts, colors, and shadows are declared once per view in `<style type="text/tailwindcss">` `@theme` tokens and via the CDN.

---

## 🎨 Color Palette & Tokens

- **Primary (Emerald `#059669`)**: Primary buttons, focus rings, active nav, success signals, chart series.
- **Accent Scale (50–800)**: `#ECFDF5` → `#065F46` for soft tints and strong text on tinted pills.
- **Surfaces**: Pure white cards (`bg-white`) on a slate canvas; `border-slate-200` dividers; `bg-slate-50` for zebra rows / table headers.
- **Text**: `text-slate-900` primary, `text-slate-500` metadata, `text-slate-700` emphasis.
- **Semantic Status Colors**: emerald (Active/Paid/Approved), rose (Unpaid/DC/Rejected), amber (Pending), sky (informational).
- **Shadows**: `--shadow-card` (`0 1px 3px rgba(15,23,42,0.06), 0 4px 14px rgba(15,23,42,0.05)`) and `--shadow-hover`.

## 🔤 Typography

- **Inter**: UI, body, labels.
- **Outfit**: Display/headings (used via `font-serif`-style headings in chart cards).
- **Noto Sans Ethiopic**: For Amharic/ጥ content.
- **Tabular data** (`font-mono tabular-nums`): Financial ledgers, meter readings, KPI values.

## 📂 Layout & Sidebar

- **App shell**: CSS grid `296px 1fr` (expanded) ↔ `104px 1fr` (`.sidebar-collapsed`, saved to `localStorage`).
- **Floating sidebar** (`#mainSidebar`): brand header, accent-category accordions (`emerald`/`indigo`/`gold`) with description flyouts in collapsed mode, user footer with role badge and logout.
- **Topbar**: page title/subtitle, quick search, primary page action, segmented language switcher, user chip.
- **Shortcuts**: `Ctrl+B` / `Ctrl+K` toggle sidebar; `Ctrl+K` also opens quick-command modal.

## 📦 Components Spec

### Cards
- **Standard**: `bg-white border border-slate-200 rounded-xl shadow-card`, optional 4px emerald top accent (`h-1 bg-emerald-600`).
- **Chart cards**: `.gsap-chart-card` wrapper, `h-[190px]`/`h-[220px]` canvas with `flex items-center justify-center` fallback.
- **KPI cards**: `p-5 rounded-xl bg-white border border-slate-200 shadow-card space-y-2` with `text-[11px] uppercase tracking-wider font-bold` label and `text-2xl font-bold font-mono tabular-nums` value (`data-gsap-counter` + `data-target-val`).
- **Mini KPI**: `flex items-center gap-4 p-4` + `w-11 h-11 rounded-lg` tinted icon tile.

### Buttons
- **Primary**: `inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm`.
- **Secondary**: `bg-slate-100 hover:bg-slate-200 text-slate-700`.
- **Danger/Confirm**: `bg-rose-600 hover:bg-rose-700 text-white`.

### Forms
- **Inputs**: `w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm` with `focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500`.
- **Selects**: `.fancy` class (styled globally in `app.css`) or the input recipe.
- **Labels**: `block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5`.

### Tables
- `w-full text-[13px]`; thead `bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold`; rows `border-b border-slate-100 odd:bg-white even:bg-slate-50/40 hover:bg-emerald-50/60`; numeric cells `font-mono tabular-nums`.
- Wide tables use `.scrollable-table` + `.scroll-progress` + `.table-scroll-view` with the green progress bar.

### Status Pills
`inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider border` with `bg-{color}-100 text-{color}-800 border-{color}-300`.

### Modals & Toasts
- Modals: `.modal-backdrop` + `.modal.v2 bg-white rounded-2xl shadow-2xl w-full max-w-[540px] overflow-hidden flex flex-col`, gradient header, `openModal/closeModal` toggling `.show`.
- Toasts: `showToast(msg, 'success'|'error'|'warning'|'info')` rendered into `#toastContainer`.
- Confirms: `confirmDialog(title, body, 'danger')` promise.
- GSAP hooks: `openGSAPModal/closeGSAPModal`, `gsap-hero`, `gsap-stat-card`, `gsap-chart-card`, `gsap-section-card`, `data-gsap-counter`.

### Empty states & alerts
- Empty: `text-center py-10 px-6 text-slate-500` with muted icon.
- Alert: `flex items-center gap-2.5 rounded-lg border border-l-4 border-{color}-200 border-l-{color}-500 bg-{color}-50 text-{color}-800`.

## 🖨 Printing
`@media print` hides `.sidebar, .topbar, .toolbar, .page-actions, .fab-wrapper, .no-print`; the thermal receipt (`bills/print`) is a 240px standalone card with `!important` overrides.
