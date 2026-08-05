---
name: Modern Steward
theme:
  primary: '#059669'          # emerald 600
  primary-deep: '#065F46'     # emerald 800
  primary-soft: '#ECFDF5'     # emerald 50
  primary-wash: '#D1FAE5'     # emerald 100
  surface: '#ffffff'
  canvas: '#F8FAF8'
  surface-dim: '#F1F5F9'
  outline: '#E2E8F0'
  on-surface: '#0F172A'
  on-surface-variant: '#64748B'
  success: '#059669'
  success-soft: '#D1FAE5'
  warning: '#D97706'
  warning-soft: '#FEF3C7'
  danger: '#E11D48'
  danger-soft: '#FFE4E6'
  info: '#0284C7'
  info-soft: '#E0F2FE'
  shadow-card: '0 1px 3px rgba(15,23,42,0.06), 0 4px 14px rgba(15,23,42,0.05)'
  shadow-hover: '0 6px 18px rgba(15,23,42,0.09), 0 10px 26px rgba(5,150,105,0.10)'
typography:
  ui: Inter
  display: Outfit
  ethiopic: Noto Sans Ethiopic
  tabular: "'Inter', ui-monospace, monospace"
spacing:
  unit: 4px
  container-padding: 24px
  gutter: 16px
  card-padding: 20px
  nav-width: 296px
  nav-width-collapsed: 104px
radius:
  DEFAULT: 12px
  lg: 12px
  md: 8px
  full: 9999px
---

## Brand & Style

**Modern Steward** is the design language for the WaterSteward Enterprise System administration suite. It pairs the trust and reliability of a municipal utility with a crisp, contemporary, high-utility interface. The personality is **Calm, Dependable, and Efficient** — a system an operator trusts for a decade of daily billing work.

The implementation is **utility-first Tailwind CSS 4** compiled at runtime via the official CDN script. No build step. Component conventions are codified as utility strings and reused verbatim across every Blade view. Where utility classes cannot express something (page base rules, sidebar flyouts, print layout, JS-injected HTML), the rule lives in `public/assets/css/app.css`.

## Colors

Anchored in **Emerald & Slate**.

- **Primary Emerald (`#059669`)**: primary buttons, active states, focus rings, chart series, success. Deep `#065F46` for text on tinted pills; soft `#ECFDF5`/`#D1FAE5` for backgrounds.
- **On-surface (`#0F172A`)**: near-black slate for headings and values.
- **Muted (`#64748B`)**: metadata, eyebrows, table headers, captions.
- **Surfaces**: white cards on a very light canvas (`#F8FAF8`); `#F1F5F9` for zebra rows and footer strips; `#E2E8F0` for all 1px borders.
- **Semantic**: emerald (Active/Paid/Approved), rose (Unpaid/DC/Rejected), amber (Pending), sky (informational).

## Typography

A **dual-typeface strategy with a native-feeling hierarchy**:

- **Inter**: all UI — body, labels, tables, nav.
- **Outfit**: display headings in hero page headers and chart-card titles.
- **Noto Sans Ethiopic**: guaranteed fallback for Amharic/Oromo string labels when the current locale renders localised text.
- **Tabular figures** (`font-mono tabular-nums`) for every numeric value that represents money, meter readings, or counts, so columns align perfectly in ledgers.

**Hierarchy rules:**
- Page hero: 11px uppercase-tracking eyebrow + 22px bold heading with an emerald 24px icon + 13px muted subtitle.
- KPI value: 24px bold tabular, label as 11px bold uppercase with generous tracking.
- Table headers: 11px bold uppercase slate; cells 13px; strong values bold slate-900.

## Layout & Spacing

**Fixed floating sidebar, fluid content** in a CSS grid (`296px 1fr`), collapsing to `104px 1fr` with `.sidebar-collapsed` persisted in `localStorage`.

- **Rhythm**: 4px base unit; page sections breathe at 16–24px gaps; page blocks padded 20px.
- **Cards** are `bg-white border border-slate-200 rounded-xl shadow-card` — depth comes from border + a whisper-soft shadow rather than heavy elevation.
- **Hover**: interactive cards lift via `--shadow-hover` and a faint emerald wash on rows (`hover:bg-emerald-50/60`).
- **Breakpoints**: `sm` stacks KPI grids to 2 columns; `lg` restores 4; content grids collapse to single column under `lg`.

## Elevation & Depth

Tonal layering is the primary depth signal.

- Base layer: canvas `#F8FAF8`.
- Surface layer: white cards with a 1px `#E2E8F0` border.
- Hero accent: a 4px emerald strip (`h-1 bg-emerald-600`) on primary sections and report tables.
- Signature shadows: `--shadow-card` at rest, `--shadow-hover` on lift.
- Floating elements (FAB, flyouts, toasts): stronger elevation with an emerald glow (`0 8px 24px rgba(5,150,105,0.45)` for the FAB trigger).

## Shapes

**Friendly radii, few hard corners.**

- Cards/panels: 12px.
- Buttons/inputs: 8px.
- Pills/badges/avatars: fully rounded.
- Wide data tables: the scroll container is rounded; inner rows are square for legibility.

## Components

### Buttons
- **Primary**: emerald 600 fill → 700 on hover, white bold 12px text, 8px radius, `shadow-sm`, 16px icons.
- **Secondary**: slate-100 fill → 200 hover, slate-700 text.
- **Danger**: rose 600 → 700. **Warning**: amber 500 → 600.

### Cards
- White surface, 1px slate-200 border, 12px radius, `--shadow-card`.
- KPI cards follow the label/value/meta pattern with tabular values and `data-gsap-counter` count-up.
- Chart cards wrap a fixed-height canvas (190/220/240px) with an emerald-tinted series and muted gridlines.

### Forms & Inputs
- Inputs: 1px slate-200 border, 8px radius, `focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500`.
- Selects use the `.fancy` class (global fallback in `app.css`) or the same input recipe.
- Labels: 11px bold uppercase with wide tracking.

### Tables
- Sticky header conventions with zebra rows (`odd:bg-white even:bg-slate-50/40`) and emerald hover wash.
- Wide tables wrapped in `.scrollable-table` with the green `.scroll-progress` bar.

### Status Pills
- Tinted 10px bold uppercase pills: `bg-{color}-100 text-{color}-800 border-{color}-300`, optionally with a leading icon.

### Modals, Toasts, Dialogs
- `.modal-backdrop` + `.modal.v2` white rounded-2xl with a soft gradient header; `openModal`/`closeModal` toggle `.show`.
- `showToast` into `#toastContainer`; `confirmDialog` returns a promise; GSAP variants via `openGSAPModal`/`closeGSAPModal` and `showGSAPToast`.

### Charts (Chart.js)
- Destroy-before-init: `Chart.getChart(ctx)?.destroy()`.
- Run once on `document.readyState`, ~50ms `setTimeout` after SPA content swaps.
- Series use emerald `#059669`; doughnut palettes mix emerald/blue/indigo/amber/violet; gridlines `rgba(15,23,42,0.05)`.

### Print
- `@media print` hides sidebar/topbar/toolbar/FAB/no-print.
- Thermal receipts (`bills/print`) render as a standalone 240px card with `!important` overrides.
