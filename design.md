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

---

## 📂 Navigation Organization & Flyouts

### 1. Categorized Groups
Navigation items are organized into distinct logical sections:
- **Operations**: Dashboard, Customer Service, Bills & Printing.
- **Reports & Ledger**: Customers Ledger, Detail Statistics, Reading Correction.
- **Administration**: Account Register.

### 2. Rich Flyout Cards (`.nav-flyout`)
When the sidebar is in collapsed mode (`.sidebar-collapsed`), hovering over any icon displays a floating **Flyout Card**:
- **Category Badge**: Upper-case section name (`--persian-indigo-bright`).
- **Title & Description**: High contrast header and helpful description text.
- **Active Section Indicator**: Green dot indicator for the current active page section.
- **Positioning**: Absolute right popout with a subtle left pointer arrow.

### 3. Keyboard Shortcuts
- `Ctrl + B` (or `Cmd + B`): Toggle sidebar collapse/expand mode.
- `Ctrl + K` (or `Cmd + K`): Focus quick search / command input.

---

## 📦 Components Spec

### Cards & Panels (`.panel`, `.stat-card`, `.card`)
- **Base Surface**: `#FFFFFF` background with `1px solid var(--indigo-border)` border.
- **Border Radius**: `12px` (`--r-lg`).
- **Elevation**: `shadow-sm`. On hover, elevates with `translateY(-2px)` and `shadow-md`.

### Modals & Floating Windows (`.modal-backdrop`, `.modal-window`)
- **Backdrop**: `rgba(26, 16, 84, 0.45)` with `backdrop-filter: blur(6px)`.
- **Window Container**: Glassmorphic / clean white surface, `16px` radius, `0 20px 48px rgba(26, 16, 84, 0.24)` ambient shadow.
