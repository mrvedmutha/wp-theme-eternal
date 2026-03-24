# SPEC: Typography & Base CSS — Eternal Labs Brand

**Date:** 2026-03-24
**Status:** AWAITING APPROVAL

---

## Objective

Replace all default WP Rig typography tokens and base styles with the Eternal Labs brand system as defined in the Figma Brand Stylesheet (node `694:4703`).

---

## Scope of Changes

### 1. `inc/Fonts/Component.php`
- Replace `get_google_fonts()` array: remove `Roboto Condensed` & `Montserrat`, add **Cormorant Garamond** with variants `300`, `300i`, `400`, `400i` (Light = 300, Regular = 400).

### 2. `assets/css/src/_fonts.css` *(new file)*
- Declare `@font-face` for **Maison Neue** using `assets/fonts/MaisonNeue/MaisonNeue.woff2` and `.woff`.
- Single file available — map `font-weight: 300 400` as Book, `font-weight: 500 600` as Medium (same file, synthesized until proper variants are supplied).
- Import this file at the top of `global.css`.

### 3. `assets/css/src/_custom-media.css`
Replace all existing tokens with:

| Token | Direction | Value |
|---|---|---|
| `--bp-4k` | `min-width` | `2560px` |
| `--bp-xl` | `min-width` | `1600px` |
| `--bp-desktop` | `max-width` | `1200px` |
| `--bp-tablet` | `max-width` | `1024px` |
| `--bp-mobile-md` | `max-width` | `768px` |
| `--bp-mobile-sm` | `max-width` | `450px` |
| `--bp-mobile-xs` | `max-width` | `375px` |

> **Note:** Existing references to `--narrow-menu-query`, `--wide-menu-query`, `--content-query`, `--sidebar-query` in navigation/header CSS will break and need a follow-up pass.

### 4. `assets/css/src/_custom-properties.css`
Replace all existing tokens with Eternal Labs brand tokens:

```css
/* Fonts */
--font-display: 'Cormorant Garamond', Georgia, serif;
--font-body:    'Maison Neue', 'Helvetica Neue', Arial, sans-serif;

/* Colors */
--color-primary:   #021f1d;
--color-white:     #ffffff;
--color-grey:      #f5f5f5;
--color-muted:     #868686;
--color-black:     #000000;
--color-rule:      #dcd7cd;

/* Spacing — 4pt grid */
--space-1: 4px;   --space-2: 8px;   --space-3: 12px;
--space-4: 16px;  --space-5: 20px;  --space-6: 24px;
--space-8: 32px;  --space-10: 40px; --space-12: 48px;
--space-16: 64px; --space-20: 80px;

/* Layout */
--page-width:    1440px;
--content-width: 760px;
--page-padding:  80px;
--section-gap:   80px;
--body-gap:      24px;
--nav-height:    80px;
```

### 5. `assets/css/src/_typography.css`
Replace all existing rules with the Eternal Labs type scale. Headings use `clamp()` for fluid scaling between mobile floor and 1440px desktop ceiling.

| Role | Selector | Size (clamp or fixed) | Line Height | Tracking | Weight |
|---|---|---|---|---|---|
| Hero | `.hero h1` | `clamp(44px, 5.56vw, 80px)` | `1.1` | `-0.02em` | 300 |
| H1 | `h1` | `clamp(32px, 3.61vw, 52px)` | `1.12` | `-0.019em` | 300 |
| H2 | `h2` | `clamp(26px, 2.78vw, 40px)` | `1.2` | `-0.05em` | 400 |
| H3 | `h3` | `clamp(22px, 2.22vw, 32px)` | `1.31` | `0` | 400 |
| H3 Italic | `h3 em, h3 i` | inherits | inherits | `0` | 300 italic |
| H4 | `h4` | `clamp(19px, 1.67vw, 24px)` | `1.29` | `0` | 400 |
| H5 | `h5` | `clamp(17px, 1.39vw, 20px)` | `1.3` | `0` | 400 |
| Body Large | `.body-large` | `17px` | `30px` | `0` | 300 |
| Body Base | `body, p` | `15px` | `23px` | `0.01em` | 300 |
| Body Small | `small, .body-small` | `13px` | `20px` | `0.01em` | 300 |
| Nav | `nav a` | `13px` | `16px` | `0.01em` | 300 |
| Eyebrow | `.eyebrow` | `11px` | `14px` | `0.18em` | 500 |
| Button | `button, .btn` | `11px` | `14px` | `0.18em` | 500 |
| Caption | `figcaption, .caption` | `11px` | `16px` | `0.08em` | 300 |

---

## Files Changed Summary

| File | Action |
|---|---|
| `inc/Fonts/Component.php` | Update `get_google_fonts()` |
| `assets/css/src/_fonts.css` | **Create** — Maison Neue `@font-face` |
| `assets/css/src/_custom-media.css` | Replace breakpoints |
| `assets/css/src/_custom-properties.css` | Replace brand tokens |
| `assets/css/src/_typography.css` | Replace type scale |
| `assets/css/src/global.css` | Add `@import "_fonts.css"` |

---

## Out of Scope (follow-up)

- Updating old breakpoint references (`--narrow-menu-query` etc.) in nav/header CSS
- Button component styles
- Dark mode palette for Eternal Labs brand

---

## Approval

- [ ] Approved to proceed with implementation
