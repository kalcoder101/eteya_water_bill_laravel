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

## 📂 Navigation Organization & Rectangular Panel Collapse

### 1. Modern Rectangular Collapse Controls
- **Header Toggle (`.sidebar-collapse-toggle`)**: Sleek rectangular container button in top brand header displaying the `panel-left` SVG icon (`<rect>` + dividing line + arrow).
- **Footer Toggle (`.sidebar-footer-collapse-btn`)**: Rectangular container button in sidebar footer displaying the `panel-left` SVG icon + "Collapse Sidebar" text.
- **Topbar Toggle (`.sidebar-toggle-btn`)**: Rectangular panel toggle button in topbar header.

### 2. Collapsed State Layout (Top & Bottom Icons)
When collapsed (`.sidebar-collapsed`):
- Top logo/text and footer text hide cleanly.
- Both top and bottom collapse buttons remain centered in 44px rectangular container buttons.
- The `panel-left` icon flips (`transform: scaleX(-1)`), signaling expand action.
- The Logout button collapses into a centered rectangular icon button with hover red highlights.

### 3. Rich Flyout Cards (`.nav-flyout`)
Hovering over any collapsed icon displays a floating flyout card:
- Category badge, title, subtext, and active section dot.
