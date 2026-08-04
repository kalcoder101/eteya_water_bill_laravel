# Eteya Water Bill — Modern High-Utility Design System (design.md)

Inspired by high-utility modern enterprise applications and design principles from the EOS architecture, this design system combines the clarity of **Corporate Modernism** with the visual polish of **Persian Indigo & Slate** styling.

---

## 🎨 Color Palette & Tokens

### Primary Palette
- **Persian Indigo (`--persian-indigo`)**: `#27187E` — Brand primary, headers, active navigation accents.
- **Deep Indigo (`--persian-indigo-deep`)**: `#1A1054` — High contrast text, deep surfaces, dark mode contrast.
- **Bright Indigo (`--persian-indigo-bright`)**: `#4A3AB8` — Primary buttons, active state indicators, hover highlights.
- **Indigo Wash (`--indigo-wash`)**: `#ECEBFA` — Subtle container backgrounds, active item backgrounds.
- **Indigo Border (`--indigo-border`)**: `#E5E3F5` — Dividers, input borders, card frames.

### Neutral Surfaces
- **Ghost White (`--ghost-white`)**: `#F7F7FF` — Base application background canvas.
- **Surface (`--surface`)**: `#FFFFFF` — Main card backgrounds, modal windows, table containers.
- **Surface Tint (`--surface-tint`)**: `#F2F1FB` — Secondary headers, table header rows, sidebar footers.

### Status Signals
- **Success**: `#16A34A` / Soft: `#DCFCE7` — Paid bills, active accounts, completed operations.
- **Warning**: `#D97706` / Soft: `#FEF3C7` — Disconnected accounts, pending corrections.
- **Danger**: `#DC2626` / Soft: `#FEE2E2` — Overdue bills, rejected complaints, deletion alerts.
- **Info**: `#0891B2` / Soft: `#CFFAFE` — Informational callouts, meter stats.

---

## 📐 Layout & Sidebar Collapse System

### Responsive Layout
The interface uses a two-column grid shell:
- **Expanded Sidebar**: `264px` width. Full brand title, nav labels, role details, and footer credits.
- **Collapsed Sidebar**: `72px` width. Icon-only navigation, brand logo focus, tooltips on hover, maximized workspace for data tables & ledgers.

### Sidebar Collapse Behavior
- **Toggle Control**: Located in topbar next to the page title. Clicking toggles `.sidebar-collapsed` class on `.app-shell`.
- **State Persistence**: Preserved in `localStorage` under key `eteya_sidebar_collapsed` (`'true'` / `'false'`).
- **Smooth Animation**: `transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1)`.

---

## 📦 Components Spec

### 1. Cards & Panels (`.panel`, `.stat-card`, `.card`)
- **Base Surface**: `#FFFFFF` background with `1px solid var(--indigo-border)` border.
- **Border Radius**: `12px` (`--r-lg`).
- **Elevation**: `shadow-sm` (`0 1px 3px rgba(39, 24, 126, 0.06)`). On hover, elevates with `translateY(-2px)` and `shadow-md`.
- **Accent Top Bar**: Optional top border accent (e.g. `border-top: 3px solid var(--persian-indigo)`).

### 2. Modals & Floating Windows (`.modal-backdrop`, `.modal-window`)
- **Backdrop**: `rgba(26, 16, 84, 0.45)` with `backdrop-filter: blur(6px)`.
- **Window Container**: Glassmorphic / clean white surface, `16px` radius, `0 20px 48px rgba(26, 16, 84, 0.24)` ambient shadow.
- **Header Bar**: Integrated title, subtitle, and close button (`&times;`).

### 3. Status Badges & Pills (`.badge`)
- **Rounded**: `999px` (`--r-pill`).
- **Padding**: `4px 10px`.
- **Font**: `11px`, `fontWeight: 600`, uppercase tracking `0.04em`.

### 4. High-Density Data Tables (`.table`)
- **Row Height**: `44px` standard, `36px` compact.
- **Headers**: Uppercase label text, `--text-muted`, `--surface-tint` background.
- **Hover**: Subtle row highlight (`#F7F7FF`) for scannability.

---

## 🔤 Typography & Hierarchy

- **Font Family**: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif.
- **Page Titles**: `22px`, `fontWeight: 700`, `--persian-indigo-deep`.
- **Card Headings**: `16px`, `fontWeight: 600`.
- **Tabular Data / Codes / Currency**: Monospace font (`JetBrains Mono`, `Consolas`).
