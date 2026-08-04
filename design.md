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

## 📂 Navigation & Simplified Sidebar Collapse Layout

### 1. Expanded Mode (Open Sidebar)
- **Top Tip Header**: Features enterprise logo, title, and a single rectangular `panel-left` collapse button (`.sidebar-collapse-toggle`) on the top right tip of the sidebar header.
- **Bottom Footer**: Displays standard Logout link and developer credits. **No redundant bottom collapse button** when open.
- **Topbar Header**: Clean topbar starting directly with page title and search bar (**No redundant toggle button in topbar**).

### 2. Collapsed Mode (Collapsed Sidebar)
- **Top Header**: Centered enterprise logo badge + centered rectangular collapse toggle button.
- **Bottom Footer**: Centered rectangular collapse toggle button + centered rectangular Logout icon button.

### 3. Rich Flyout Cards (`.nav-flyout`)
Hovering over any collapsed icon displays a floating flyout card with category badge, title, description, and active status.
