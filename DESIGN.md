---
name: Heritage Gold
colors:
  surface: '#fff8f1'
  surface-dim: '#e2d9c9'
  surface-bright: '#fff8f1'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#fcf2e2'
  surface-container: '#f7eddc'
  surface-container-high: '#f1e7d7'
  surface-container-highest: '#ebe1d1'
  on-surface: '#1f1b11'
  on-surface-variant: '#4e4633'
  inverse-surface: '#353025'
  inverse-on-surface: '#faf0df'
  outline: '#807661'
  outline-variant: '#d2c5ad'
  surface-tint: '#755b00'
  primary: '#755b00'
  on-primary: '#ffffff'
  primary-container: '#edbc1d'
  on-primary-container: '#634d00'
  inverse-primary: '#f2c023'
  secondary: '#685d4a'
  on-secondary: '#ffffff'
  secondary-container: '#eeddc6'
  on-secondary-container: '#6d614e'
  tertiary: '#006687'
  on-tertiary: '#ffffff'
  tertiary-container: '#64ceff'
  on-tertiary-container: '#005673'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffdf91'
  primary-fixed-dim: '#f2c023'
  on-primary-fixed: '#241a00'
  on-primary-fixed-variant: '#594400'
  secondary-fixed: '#f1e0c9'
  secondary-fixed-dim: '#d4c4ae'
  on-secondary-fixed: '#221a0c'
  on-secondary-fixed-variant: '#504534'
  tertiary-fixed: '#c1e8ff'
  tertiary-fixed-dim: '#73d1ff'
  on-tertiary-fixed: '#001e2b'
  on-tertiary-fixed-variant: '#004d67'
  background: '#fff8f1'
  on-background: '#1f1b11'
  surface-variant: '#ebe1d1'
  surface-base: '#FDFBF7'
  surface-card: '#FFFFFF'
  text-main: '#2C241B'
  accent-warm: '#8F250C'
  border-subtle: '#E6DFD3'
  nav-bg: '#fcfbf8'
typography:
  display-lg:
    fontFamily: Playfair Display
    fontSize: 36px
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  display-md:
    fontFamily: Playfair Display
    fontSize: 30px
    fontWeight: '700'
    lineHeight: '1.2'
  headline-sm:
    fontFamily: Playfair Display
    fontSize: 20px
    fontWeight: '700'
    lineHeight: '1.4'
  body-lg:
    fontFamily: Lato
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.5'
  body-md:
    fontFamily: Lato
    fontSize: 14px
    fontWeight: '400'
    lineHeight: '1.5'
  label-sm:
    fontFamily: Lato
    fontSize: 12px
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: 0.05em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  unit: 4px
  container-padding: 32px
  gutter: 24px
  card-padding: 24px
  nav-width: 256px
---

## Brand & Style

Heritage Gold is a premium, sophisticated wedding and event management platform. The brand personality is **Elegant, Trustworthy, and Traditional**, designed to evoke the feeling of a high-end concierge service. It blends the warmth of personal celebration with the precision of professional planning.

The design style is **Modern Corporate with an Editorial twist**. It utilizes high-contrast serif typography for headlines to create a luxury magazine feel, balanced against a clean, functional sans-serif for data-heavy dashboard elements. The interface prioritizes clarity and status, using a restricted but rich color palette to signal importance and heritage.

## Colors

The color palette is anchored in an **Earth-tone Gold and Charcoal** scheme. 

- **Primary Gold (#edbc1d):** Used for primary actions, progress indicators, and brand markers. It represents luxury and celebration.
- **Text Main (#2C241B):** A deep charcoal-brown used for primary text and headings, providing better legibility and warmth than pure black.
- **Text Muted (#8B7E6A):** A taupe-grey for secondary information and labels.
- **Accent Warm (#8F250C):** A deep terracotta-red reserved for high-priority alerts, overdue tasks, or critical status markers.
- **Surfaces:** A layered approach using off-white (#FDFBF7) for the main background and pure white (#FFFFFF) for cards to create subtle depth.

## Typography

The system uses a **Dual-Typeface Strategy**:

- **Playfair Display (Serif):** Reserved for headlines, titles, and brand elements. It conveys elegance and the "editorial" nature of a wedding. 
- **Lato (Sans-Serif):** Used for all functional UI, body copy, and navigation. It provides high legibility and a contemporary, clean feel.

**Hierarchy Rules:**
- Use `display-lg` for the primary page context (e.g., Couple's Names).
- Use `label-sm` with heavy tracking for meta-information like "Until the Särg" or secondary headers.
- Bold weights in Lato are used for emphasizing key data points (e.g., $ totals, guest counts).

## Layout & Spacing

The layout follows a **Fixed-Width Sidebar and Fluid Content** model.

- **Grid:** A standard 12-column grid is utilized for the main content area, though most dashboard views leverage a 3-column masonry-style grid for metric cards.
- **Rhythm:** An 8px (base 4px) spacing system is used.
- **Margins:** Large 32px (8 units) horizontal margins ensure the content doesn't feel cramped, maintaining an airy, premium aesthetic.
- **Breakpoints:** 
  - Mobile (<768px): Sidebar collapses into a hamburger menu; 3-column cards stack vertically.
  - Desktop (>1024px): Maximum content width of 1152px (max-w-6xl).

## Elevation & Depth

The system uses **Tonal Layering and Soft Ambient Shadows** to convey depth without the heaviness of standard Material design.

- **The Base Layer:** Background-light (#FDFBF7) provides a warm, paper-like foundation.
- **The Surface Layer:** Cards and interactive containers use pure white (#FFFFFF) with a very light 1px border (#E6DFD3).
- **Shadows:** A signature `card` shadow is used: `0 4px 20px rgba(44, 36, 27, 0.04)`. On hover, this elevates to `0 8px 30px rgba(44, 36, 27, 0.08)` to provide tactile feedback.
- **Accents:** A 4px top-border on major hero sections (using Primary Gold) indicates "active" or "primary focus" areas.

## Shapes

The shape language is **Conservative and Structured**. 

- **Corner Radius:** A base radius of 2px is used for most UI elements (buttons, inputs) to maintain a crisp, formal look. Cards use a slightly larger 4px radius (`lg`). 
- **Circular Elements:** Reserved strictly for progress rings (e.g., budget charts) and user avatars to provide a soft contrast to the otherwise rectangular grid.
- **Dividers:** Fine 1px horizontal and vertical lines in #E6DFD3 are used extensively to separate logical sections without creating heavy visual breaks.

## Components

### Buttons
- **Primary Ghost:** 1px border in Primary Gold, uppercase text, bold weight, high letter-spacing. Background is transparent, filling with 5% opacity on hover.
- **Navigation:** Icon-led with 2.5rem height. Active state uses a 2px vertical left-border and a 10% opacity primary color background.

### Cards
- Standard white background, subtle border, and signature `card` shadow. 
- Must include a `display-md` or `headline-sm` title and an icon in the top right corner (Text Muted color).

### Forms & Inputs
- **Checkboxes:** Small (2px) radius, Primary Gold color when checked. Associated text should use a strike-through and 50% opacity when the task is complete.
- **Inputs:** 1px border in `border-subtle`, with a focus state highlighting the border in Primary Gold.

### Progress Indicators
- **Linear:** 8px height, rounded-full, using `border-subtle` for the track and `primary` for the fill.
- **Radial:** Used for high-level financial data, with the percentage centered in bold Lato.

### Data Displays
- Numbers should use `display-lg` for prominence.
- Unit labels (e.g., "Days", "Total Invited") should use `label-sm` in `text-muted`.