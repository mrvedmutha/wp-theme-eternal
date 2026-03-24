# SPEC: Eternal Labs Header Component

**Date:** 2026-03-24
**Status:** AWAITING APPROVAL — v2 (dynamic routes + native meta box)
**Figma:** `694:1713` (transparent/white variant) · `694:1725` (black/solid variant)

---

## Mission Statement

Build a full-featured site header for Eternal Labs with three behavioural states
(transparent, sticky, solid), animated nav links, a GSAP-powered scroll hide/show
mechanism, WooCommerce cart + account integration, and a custom currency switcher
button wired to the existing `CMC_Currency_Switcher` plugin.

---

## Design Tokens (from Figma)

| Token | Value |
|---|---|
| Nav font | Maison Neue Book, 13px, 16px line-height, 0.13px letter-spacing |
| Nav color — black menu | `#394342` |
| Nav color — transparent menu | `white` |
| Header padding | `40px` horizontal · `20px` vertical |
| Logo size | `~104 × 59px` |

---

## The Three Header States

```
STATE 1 — TRANSPARENT (pages with hero)
  position: fixed · overlaps hero · white text on transparent bg
  hover → transitions to black menu (white bg, #394342 text)
  scroll past hero viewport → STATE 2

STATE 2 — STICKY (scroll-triggered, both page types)
  position: fixed · top: 0 · black menu always
  scroll UP   → slides into view  (transform: translateY(0))
  scroll DOWN → slides out of view (transform: translateY(-100%))

STATE 3 — SOLID (pages without hero)
  position: sticky · top: 0 · black menu always
  content sits BELOW header (not overlapping)
  scroll UP/DOWN → same hide/show as STATE 2
```

---

## Page-Level Header Control

A `body` CSS class drives which state is active — added via the `body_class`
filter in `inc/Header/Component.php`. No ACF plugin required.

| Body class | Header state |
|---|---|
| *(default — no class)* | STATE 3 — Solid |
| `.has-transparent-header` | STATE 1 — Transparent |

### Two-Layer Resolution System

The `is_transparent_header()` method in `inc/Header/Component.php` runs this
logic on every page load:

```
1. Check per-page meta override (set in WP admin edit screen)
   → "Force Transparent"  →  transparent
   → "Force Solid"        →  solid
   → "Auto" (default)     →  fall through to Layer 2

2. Check theme-level route rules (defined in get_transparent_routes())
   → matches a rule       →  transparent
   → no match             →  solid (default)
```

> **Shopify analogy:** Layer 2 is like `{% if template == 'index' %}` checks.
> Layer 1 is like a page metafield that overrides the template rule.

### Layer 1 — Per-page meta box (override)

A native WordPress meta box added by `inc/Header/Component.php` — **no plugin
needed**. Appears in the WP admin page/post edit screen under "Header Style":

```
Header Style
  ● Auto (follow theme rules)   ← default
  ○ Force Transparent
  ○ Force Solid
```

Stored as post meta key `_header_style` with values `auto` | `transparent` | `solid`.

### Layer 2 — Theme-level route rules (automatic)

Defined in `get_transparent_routes()` inside `inc/Header/Component.php`.
Editors never need to touch these — they are set once by a developer and apply
automatically to all matching pages:

```php
private function get_transparent_routes(): array {
    return [
        // Page templates (filename of template in theme root)
        'templates'    => [],

        // Page slugs
        'slugs'        => [ 'home', 'about' ],

        // WooCommerce / custom post types
        'post_types'   => [],

        // WordPress conditional tags (as strings)
        'conditionals' => [ 'is_front_page' ],
    ];
}
```

To add a new transparent-header route, a developer adds one line to the
relevant array — no admin UI, no plugin, no database query.

---

## Navigation Structure

### Left nav (WordPress registered menu — `primary`)
```
SKINCARE
NUTRACEUTICALS
HOUSE OF ETERNAL LABS  ▾   ← has WordPress sub-menu (dropdown)
BLOG
```

### Centre
Eternal Labs logo via `the_custom_logo()` — already supported by
`inc/Custom_Logo/Component.php`.

### Right utility bar
```
SEARCH        ← opens a search overlay (mega-search panel, Phase 2)
IN / INR  🌐  ← currency switcher (globe icon → chevron-down on hover)
LOGIN         ← WooCommerce My Account link
BAG(0)        ← WooCommerce cart with live count
```

---

## Interaction Details

### Nav link underline animation
Every primary nav link gets a CSS `::after` pseudo-element underline:
- **Mouse enter:** `scaleX(0 → 1)`, `transform-origin: left`, duration `0.3s ease`
- **Mouse leave:** `scaleX(1 → 0)`, `transform-origin: right`, duration `0.3s ease`
- Colour inherits from current header state (white or `#394342`) via a
  `--nav-underline-color` CSS custom property.

### Currency switcher button (IN / INR)
- Displays active currency code + symbol from `CMC_Currency_Manager::get_active_currency()`
- Globe SVG icon shown by default
- **On hover:** globe icon fades out (`opacity: 0`), chevron-down SVG fades in
  (`opacity: 1`) — CSS `opacity` transition, both icons absolutely positioned
  on top of each other
- Clicking opens a small dropdown of enabled currencies (rendered by
  `CMC_Currency_Switcher::get_instance()->render_switcher('buttons')`, restyled
  to match the Figma design)

### Scroll behaviour (GSAP + ScrollTrigger)
```js
// Transparent-header pages
ScrollTrigger.create({
  start: 'top -80px',       // once user scrolls past ~80px
  onEnter:  () => header.classList.add('is-scrolled'),
  onLeaveBack: () => header.classList.remove('is-scrolled'),
});

// All pages — hide on scroll down, show on scroll up
let lastY = 0;
ScrollTrigger.create({
  start: 'top top',
  onUpdate: (self) => {
    const dir = self.getVelocity() > 0 ? 'down' : 'up';
    gsap.to(header, {
      yPercent: dir === 'down' ? -100 : 0,
      duration: 0.4,
      ease: 'power2.out',
      overwrite: true,
    });
  },
});
```

---

## Files to Create / Modify

### New files
| File | Purpose |
|---|---|
| `inc/Header/Component.php` | Register menus, body class filter, WC cart count AJAX |
| `template-parts/header/nav-primary.php` | Left nav markup |
| `template-parts/header/nav-utility.php` | Right utility bar (search, currency, login, bag) |
| `assets/js/src/header.js` | GSAP scroll logic + currency dropdown toggle |
| `assets/css/src/_header-eternal.css` | All header states, animations, transitions |

### Modified files
| File | Change |
|---|---|
| `header.php` | Replace existing partials with new structure + add state class |
| `template-parts/header/branding.php` | Minor: ensure logo works in both colour variants |
| `inc/Scripts/Component.php` | Register `wp-rig-header` script (footer, defer) |
| `inc/Styles/Component.php` | Register `_header-eternal.css` |
| `inc/Theme.php` | Register new `Header\Component` |

> **Note:** `template-parts/header/mobile-menu-toggle.php` and
> `template-parts/header/custom_header.php` are not part of this feature's
> scope. Mobile header is a separate future feature.

---

## GSAP Installation

Before implementation, GSAP must be installed:
```bash
npm install gsap
```
Imported in `assets/js/src/header.js`:
```js
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
gsap.registerPlugin(ScrollTrigger);
```

---

## CSS Architecture

`_header-eternal.css` will be imported in `global.css`. Structure:

```css
/* 1. Base layout (always) */
.site-header { ... }

/* 2. Transparent state */
.has-transparent-header .site-header { ... }
.has-transparent-header .site-header:hover { ... }

/* 3. Scrolled state (JS adds .is-scrolled) */
.has-transparent-header .site-header.is-scrolled { ... }

/* 4. Sticky/Solid shared */
.site-header.is-sticky { ... }

/* 5. Nav link underline animation */
.header-nav__link::after { ... }

/* 6. Currency button icon swap */
.currency-btn .icon-globe { ... }
.currency-btn .icon-chevron { ... }
.currency-btn:hover .icon-globe { opacity: 0; }
.currency-btn:hover .icon-chevron { opacity: 1; }
```

---

## User Stories

- As a visitor on a hero page, I see through the header to the hero image on load.
- As a visitor, hovering the header on a hero page shows the white-background nav.
- As a visitor, scrolling down hides the header; scrolling up reveals it.
- As a visitor, I can switch currency from the header and the page price updates.
- As a visitor, I can see my cart item count in the header at all times.
- As an editor, I can override the header style on any individual page from the WP admin edit screen.
- As a developer, I can add new transparent-header routes by editing one array in `inc/Header/Component.php`.
- As an editor on the homepage, the header is automatically transparent without any manual setting.

---

## Success Metrics

- [ ] All three states render correctly on desktop (1440px)
- [ ] GSAP hide/show fires within 400ms of scroll direction change
- [ ] Transparent → sticky transition has no layout shift (no CLS)
- [ ] Currency switcher shows active currency and updates on selection
- [ ] WooCommerce cart count updates without page reload (AJAX)
- [ ] Passes WCAG AA colour contrast in both black and transparent states
- [ ] `npm run ai:check` passes with no errors

---

## Out of Scope (Phase 2)

- Mobile / hamburger menu
- SEARCH mega-panel
- Mega-menu for "HOUSE OF ETERNAL LABS" dropdown content
