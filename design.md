# Eteya Water Bill — Modern High-Utility Design System (design.md)

Inspired by design principles from the **EOS Architecture (Modern Steward & Heritage Gold)**, this design system combines high-utility minimalism with rich corporate modernism.

---

## 🎨 Color Palette & Tokens (EOS Specs)

### Primary & Accent Palette
- **Primary (Emerald `#10B981` / `#006C49`)**: Reserved for completion, active progress signals, primary buttons, success badges, and focus borders.
- **Secondary (Heritage Gold `#FEA619` / `#EDBC1D`)**: Used for focus elements, highlights, warning tags, and premium status markers.
- **Tertiary (Deep Indigo `#27187E` / `#1A1054`)**: Base branding, dark sidebar background canvas, and rich headers.

### Surfaces & Tonal Layers
- **Surface Canvas (`--surface`)**: `#FFFFFF` / `#F8FAFC` — Main workspace background.
- **Surface Dim / Container (`--surface-container`)**: `#F0F3FF` / `#F9F9FF` — Card containers and secondary panel backgrounds.
- **Outlines & Dividers (`--outline`)**: `1px solid #E2E8F0` / `#DEE8FF` — Tonal borders providing structure without visual heavy shadows.
- **Text Primary (`--on-surface`)**: `#111C2D` / `#1E293B` — Deep charcoal for maximum readability.
- **Text Secondary (`--on-surface-variant`)**: `#64748B` — Secondary labels, metadata, and subtext.

---

## 📂 Navigation & Sidebar Layout

### 1. Tip-Positioned Collapse Toggle
- **Expanded Mode**: Top tip collapse button (`.sidebar-collapse-toggle`) is positioned at the top-right corner of the sidebar brand header with high contrast hover effects.
- **Collapsed Mode**: Enterprise logo badge + collapse toggle button are vertically aligned at the top header; bottom footer displays collapsed toggle + logout icon button.

### 2. Accordion Category Groups (Expanded Mode)
- Clicking category headers (`Operations`, `Reports & Ledger`, `Administration`) expands the target list and automatically closes other category lists.
- Rotating chevron indicator (`chevron-down`).

### 3. Category Flyout Panels (Collapsed Mode)
- Hovering over a collapsed category group icon displays a floating popup card (`.category-flyout-panel`) with module links, titles, and descriptions.

---

## 📦 Components Spec

### Cards & Panels (`.panel`, `.stat-card`)
- **Base Surface**: `#FFFFFF` background with `1px solid #E2E8F0` border and `12px` (`--r-lg`) radius.
- **Hero Accent**: Top 4px border gradient (Emerald/Gold/Indigo) for focus metrics.
- **Elevation**: Tonal layering with soft ambient diffusion shadow (`0 4px 20px rgba(30, 41, 59, 0.06)`).

### Typography & Tabular Numbers
- **Font**: Inter font family across all headings, body text, and labels.
- **Data Display**: `font-feature-settings: 'tnum'` enabled for tabular numbers in financial ledgers and customer tables.

### Status Chips & Badges
- **Success (`.badge-success`)**: Light emerald background (`#DCFCE7`) with dark green text (`#16A34A`).
- **Warning (`.badge-warning`)**: Light gold background (`#FEF3C7`) with dark gold text (`#D97706`).
- **Danger (`.badge-danger`)**: Light red background (`#FEE2E2`) with dark red text (`#DC2626`).
