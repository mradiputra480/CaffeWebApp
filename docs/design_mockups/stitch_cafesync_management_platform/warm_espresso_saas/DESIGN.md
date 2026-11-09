---
name: Warm Espresso SaaS
colors:
  surface: '#fff8f4'
  surface-dim: '#e0d9d3'
  surface-bright: '#fff8f4'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#faf2ed'
  surface-container: '#f4ece7'
  surface-container-high: '#eee7e1'
  surface-container-highest: '#e9e1dc'
  on-surface: '#1e1b18'
  on-surface-variant: '#554336'
  inverse-surface: '#33302c'
  inverse-on-surface: '#f7efea'
  outline: '#887364'
  outline-variant: '#dbc2b0'
  surface-tint: '#904d00'
  primary: '#8d4b00'
  on-primary: '#ffffff'
  primary-container: '#b15f00'
  on-primary-container: '#fffbff'
  inverse-primary: '#ffb77d'
  secondary: '#775845'
  on-secondary: '#ffffff'
  secondary-container: '#ffd4bd'
  on-secondary-container: '#795a48'
  tertiary: '#006b2c'
  on-tertiary: '#ffffff'
  tertiary-container: '#00873a'
  on-tertiary-container: '#f7fff2'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffdcc3'
  primary-fixed-dim: '#ffb77d'
  on-primary-fixed: '#2f1500'
  on-primary-fixed-variant: '#6e3900'
  secondary-fixed: '#ffdbc8'
  secondary-fixed-dim: '#e7bea8'
  on-secondary-fixed: '#2c1608'
  on-secondary-fixed-variant: '#5d402f'
  tertiary-fixed: '#7ffc97'
  tertiary-fixed-dim: '#62df7d'
  on-tertiary-fixed: '#002109'
  on-tertiary-fixed-variant: '#005320'
  background: '#fff8f4'
  on-background: '#1e1b18'
  surface-variant: '#e9e1dc'
typography:
  headline-xl:
    fontFamily: Plus Jakarta Sans
    fontSize: 40px
    fontWeight: '700'
    lineHeight: 48px
  headline-xl-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 36px
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 26px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  body-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 18px
  label-lg:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
  label-sm:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '600'
    lineHeight: 14px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1.5rem
  gutter-mobile: 1rem
  margin: 2rem
  margin-mobile: 1rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
---

## Brand & Style

This design system establishes an operational, high-efficiency dashboard environment calibrated specifically for multi-unit cafe operators, floor managers, and baristas. Balancing utilitarian clarity with the craft-driven atmosphere of specialty coffee, the interface avoids sterile enterprise tropes in favor of an inviting, warm-neutral foundation accented by amber highlights.

The visual direction combines **Modern Corporate SaaS** rigor with **Warm Minimalist Tactility**. The experience delivers immediate scan-ability under busy operational conditions: dense metric groups, order statuses, inventory warnings, and POS flows maintain exceptional legibility through high-contrast typographic tiers, gentle 1px architectural borders, and airy negative space.

## Colors

The palette grounds high-throughput SaaS workflows in warm earth tones and high-contrast neutrals:

- **Interactive Primary (`#D97706`)**: Warm Amber drives interactive touchpoints, key selection states, focused tab pills, and active filter highlights.
- **Brand Secondary (`#3B2314`)**: Deep Espresso provides authority and anchors navigation sidebars, high-emphasis headers, and dark badge variants.
- **Functional Tertiary (`#16A34A`)**: Success Emerald designates fulfilled orders, healthy stock levels, and revenue gains.
- **Canvas & Neutral Surfaces**: Base canvas sits on `#FDFBF7` (Soft Warm Off-White), nested against pure `#FFFFFF` surface cards framed with delicate `#E7E5E4` outlines.
- **Text & Content**: Primary reading color is `#1E1B18` (Deep Charcoal) for optimal contrast on light surfaces, paired with `#78716C` (Muted Slate) for operational metadata and secondary labels.
- **Alert Tones**: Warning and low-inventory triggers leverage `#EA580C`, while destructive actions, system errors, and out-of-stock items resolve to `#DC2626`.

## Typography

Typography establishes an intentional contrast between human warmth and operational precision:

- **Display & Headings (Plus Jakarta Sans)**: Used for high-impact metric counters, register totals, page headers, and modal titles. Its slightly open apertures and rounded geometry soften the dense informational load.
- **Data & Text Body (Inter)**: Handles transactional tables, kitchen display tickets, form controls, and nested list items. Its tall x-height and neutral character provide friction-free reading across tablet kiosks and desktop screens.
- **Tabular Figures**: Numeric data, POS prices, and inventory units should strictly apply `font-feature-settings: "tnum"` to prevent horizontal jitter during real-time data updates.

## Elevation & Depth

Visual depth is achieved through ambient, diffused shadows coupled with structural 1px hairline dividers rather than stark drop shadows:

- **Level 0 (Flat Canvas)**: `#FDFBF7` base page color without shadow or elevation.
- **Level 1 (Surface Cards & Table Containers)**: Pure white `#FFFFFF` surface bound by a 1px border (`#E7E5E4`) and a subtle ambient shadow: `0px 4px 20px rgba(0, 0, 0, 0.04)`.
- **Level 2 (Hover States & Active Dropdowns)**: Elevated panels and active cards introduce a soft double shadow: `0px 8px 24px rgba(30, 27, 24, 0.06), 0px 2px 6px rgba(30, 27, 24, 0.02)`.
- **Level 3 (Modals & Slide-over Drawers)**: Heavy operational sheets and transaction dialogs use `0px 20px 40px rgba(30, 27, 24, 0.12)`, supported by a 40% opacity warm neutral backdrop (`rgba(30, 27, 24, 0.4)`).

## Shapes

The design system adopts **Level 2 (Rounded)** curvature across all structural containers and UI building blocks:

- Standard input fields, cards, and modal dialogs utilize consistent 8px–12px border radii (`0.5rem` to `0.75rem`).
- Micro-elements such as tags, badges, and toggle tabs deliberately extend into full pill geometry (`9999px`) to create clear tactile affordances for touch-screen operation.
- Heavy structural frames (dashboard canvas wrappers, major modal sheets) maintain a maximum radius of 16px (`1rem`) to keep the interface balanced and professional.

## Components

### Buttons
- **Primary**: Solid Warm Amber (`#D97706`) background, white text (`#FFFFFF`), `font-weight: 600`, 8px corner radius. Hover state transitions to `#B45309`.
- **Secondary**: Deep Espresso (`#3B2314`) fill with `#FFFFFF` text for major administrative confirmations.
- **Outline / Ghost**: 1px `#E7E5E4` border on `#FFFFFF` surface with `#1E1B18` text. Hover background shifts to `#FDFBF7`.

### Pill Tabs & Filter Chips
- Capsule-shaped (`rounded-full`) horizontal switchers. Inactive items use transparent backgrounds with `#78716C` text. Active items use solid `#3B2314` or `#D97706` fills with crisp white text, or an elevated white pill over a muted `#F5F3EF` track.

### Cards & Metric Tiles
- Pure `#FFFFFF` surfaces with 12px rounded corners and a 1px `#E7E5E4` border. Metric headers pair a small muted category label (`label-sm`) with a bold Plus Jakarta Sans numerical value, accompanied by positive or negative inline indicator tags.

### Form Inputs & Selectors
- `#FFFFFF` background, 1px `#E7E5E4` stroke, 8px radius, and 40px standard touch target height. Focus states apply a 1px `#D97706` border with a 3px ambient focus ring (`rgba(217, 119, 6, 0.15)`).

### Badges & Status Indicators
- Inventory and order states feature muted pastel backgrounds with high-contrast text:
  - Ready / Completed: `#DCFCE7` background with `#16A34A` text.
  - Low Stock / Prep: `#FFEDD5` background with `#EA580C` text.
  - Out of Stock / Void: `#FEE2E2` background with `#DC2626` text.

### Data Tables & POS Counters
- Clean zebra-free tabular structures separated by horizontal 1px `#F5F3EF` dividers. Header cells use `label-sm` in all-caps tracking with `#78716C` text color. Interactive rows transition to `#FDFBF7` on hover with a smooth 150ms ease.