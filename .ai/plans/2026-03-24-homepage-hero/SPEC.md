# SPEC: Homepage Hero Section (`homepage-hero`)

**Date:** 2026-03-24
**Status:** AWAITING APPROVAL — v2 (fonts + animations added)

---

## 1. Mission Statement

Build a full-width, visually immersive homepage hero section for the Eternal Labs brand, matching the Figma design (`node 694:1442`). All content (image, headline, subtext, CTA label, CTA URL) must be editable from the WordPress Customizer — no hardcoded content in templates.

---

## 2. Design Compliance

> No `.ai/STYLE-GUIDE.md` exists yet. This feature introduces the first formal design tokens and will seed it.

### Design tokens introduced by this feature

| Token | Value | Usage |
|---|---|---|
| `--color-hero-bg` | `#fbc3b6` | Hero section background fallback |
| `--font-display-h1` | Cormorant Garamond Light | Hero headline |
| `--font-body-small` | Maison Neue Book | Hero subtext |
| `--font-cta` | DM Sans Medium | CTA label |
| `--hero-heading-size` | `52px` | H1 font size |
| `--hero-heading-lh` | `58px` | H1 line height |
| `--hero-heading-ls` | `-1px` | H1 letter spacing |
| `--hero-body-size` | `13px` | Body copy font size |
| `--hero-body-ls` | `0.13px` | Body letter spacing |
| `--hero-cta-size` | `11px` | CTA font size |
| `--hero-cta-ls` | `1.98px` | CTA letter spacing |

### Visual layout (from Figma)
- Full viewport width and height (`100vw`, `min-height: 100vh`)
- Two layered images creating depth (background atmospheric + product foreground)
- Content block: lower-left, `41px` from edge, `60px` bottom padding
- White text throughout
- CTA: all-caps label + thin white 1px underline rule

---

## 3. DM Sans Font Loading

**Where:** `inc/Fonts/Component.php` → `get_google_fonts()` array (line 228)

The Fonts component already loads Cormorant Garamond via the `$google_fonts` array. DM Sans is added to the **same array** — no new component needed, no new enqueue hook.

```php
$google_fonts = array(
    'Cormorant Garamond' => array( '300', '300i', '400', '400i', '500', '500i', '600', '600i', '700', '700i' ),
    'DM Sans'            => array( '100', '100i', '200', '200i', '300', '300i', '400', '400i', '500', '500i', '600', '600i', '700', '700i', '800', '800i', '900', '900i' ),
);
```

**Why all weights:** User explicitly said "load what weight is there, will see later" — load the full available range (100–900 + italics) so any weight can be used without revisiting this file. The `display=swap` parameter is already set by the component.

> **Note:** DM Sans on Google Fonts supports the full 100–900 weight range via variable font axis. The component builds the API URL automatically from this array.

---

## 4. Architectural Fit (WP Rig Classic Theme)

**Theme type:** `classic` (per `config/config.json`)
**Blocks:** Not required — single-instance section

### Files to create / modify

| File | Action | Purpose |
|---|---|---|
| `inc/Fonts/Component.php` | **Modify** | Add DM Sans (all weights) to `get_google_fonts()` |
| `inc/Homepage_Hero/Component.php` | **Create (scaffold)** | Registers hero JS on front page via `wp_rig_js_files` filter |
| `template-parts/homepage-hero.php` | **Create** | Section HTML, reads Customizer values |
| `assets/css/src/_homepage-hero.css` | **Create** | Hero styles + CTA underline animation CSS |
| `assets/css/src/style.css` | **Modify** | `@import` the new CSS partial |
| `assets/js/src/homepage-hero.js` | **Create** | GSAP entrance + CTA hover animations |
| `inc/EZ_Customizer/themeCustomizeSettings.json` | **Modify** | Add `homepage_hero` section + 5 settings |

### Front page integration
The template part is included in `front-page.php` (or created if absent) via:
```php
get_template_part( 'template-parts/homepage-hero' );
```

### Hooks used
| Hook | Component | Purpose |
|---|---|---|
| `wp_rig_js_files` | `Homepage_Hero\Component` | Registers `homepage-hero.min.js`, front-page only |
| `wp_rig_google_fonts` | _(via array in Fonts component)_ | DM Sans loaded via existing font pipeline |

---

## 5. Animations

GSAP `^3.14.2` is already installed as a project dependency.

### A. Entrance animation — text reveal on page load

All three content elements animate in **sequentially on page load** (no ScrollTrigger needed — this is the first visible section). Each element starts `opacity: 0, y: -20px` and animates to `opacity: 1, y: 0`.

| Element | Delay | Duration | Ease |
|---|---|---|---|
| `<h1>` heading | `0.2s` | `0.8s` | `power2.out` |
| `<p>` subtext | `0.5s` | `0.7s` | `power2.out` |
| CTA wrapper | `0.8s` | `0.6s` | `power2.out` |

Implementation — `assets/js/src/homepage-hero.js`:
```js
import { gsap } from 'gsap';

const hero = document.querySelector( '.homepage-hero' );
if ( hero ) {
    const tl = gsap.timeline( { defaults: { ease: 'power2.out' } } );
    tl.from( hero.querySelector( '.hero-heading' ),  { opacity: 0, y: -20, duration: 0.8 }, 0.2 )
      .from( hero.querySelector( '.hero-subtext' ),  { opacity: 0, y: -20, duration: 0.7 }, 0.5 )
      .from( hero.querySelector( '.hero-cta' ),      { opacity: 0, y: -20, duration: 0.6 }, 0.8 );
}
```

### B. CTA underline hover — enter/exit animation

The CTA uses a thin white `<span class="hero-cta__line">` absolutely positioned below the label text.

- **Mouseenter:** line scales from 0 → 1 from left (`transformOrigin: 'left center'`)
- **Mouseleave:** line scales from 1 → 0 from right (`transformOrigin: 'right center'`), then resets origin to left ready for next enter

```js
const cta = hero.querySelector( '.hero-cta' );
const line = cta.querySelector( '.hero-cta__line' );

gsap.set( line, { scaleX: 0, transformOrigin: 'left center' } );

cta.addEventListener( 'mouseenter', () => {
    gsap.killTweensOf( line );
    gsap.set( line, { transformOrigin: 'left center' } );
    gsap.to( line, { scaleX: 1, duration: 0.3, ease: 'power2.out' } );
} );

cta.addEventListener( 'mouseleave', () => {
    gsap.killTweensOf( line );
    gsap.set( line, { transformOrigin: 'right center' } );
    gsap.to( line, { scaleX: 0, duration: 0.25, ease: 'power2.in', onComplete: () => {
        gsap.set( line, { transformOrigin: 'left center' } );
    } } );
} );
```

### C. PHP component for script registration

`inc/Homepage_Hero/Component.php` — scaffolded via `npm run create-rig-component "Homepage Hero"`:
- Hooks into `wp_rig_js_files` (same pattern as `Header\Component`)
- Sets `global: false` so the script only loads on pages where needed
- Uses `is_front_page()` conditional — only enqueues on the homepage

```php
public function filter_register_script( array $js_files ): array {
    if ( ! is_front_page() ) {
        return $js_files;
    }
    $js_files['wp-rig-homepage-hero'] = array(
        'file'    => 'homepage-hero.min.js',
        'global'  => false,
        'footer'  => true,
        'loading' => 'defer',
        'deps'    => array(),
    );
    return $js_files;
}
```

---

## 6. EZ Customizer Settings

**New section:** `homepage_hero` — "Homepage Hero"

| Setting ID | Label | Type | Default |
|---|---|---|---|
| `hero_image` | Hero Background Image | `media` | _(empty — falls back to CSS bg color)_ |
| `hero_heading` | Hero Headline | `text` | `The Pinnacle of Longevity Supplements` |
| `hero_subtext` | Hero Subtext | `textarea` | `Advanced nutraceutical formulations developed to support wellbeing from within.` |
| `hero_cta_label` | CTA Button Label | `text` | `SHOP NOW` |
| `hero_cta_url` | CTA URL | `url` | `/shop` |

---

## 7. User Stories

- **As a site admin**, I want to update the hero headline from WP Admin without touching code.
- **As a site admin**, I want to swap the hero background image from the media library.
- **As a site admin**, I want to change the CTA destination URL (e.g., point to a sale page).
- **As a visitor**, I see a full-screen hero that matches the Figma brand design.
- **As a visitor using a screen reader**, the heading is a proper `<h1>` and the CTA is a keyboard-focusable `<a>`.

---

## 8. Success Metrics

- [ ] Hero renders at full viewport width/height on the front page
- [ ] All 5 Customizer settings save and reflect on reload
- [ ] Changing the CTA URL from Customizer updates the `<a href>` on the frontend
- [ ] Swapping the hero image from the media library updates the background
- [ ] Heading is `<h1>`, CTA is `<a>` (not `<button>`) — accessibility compliant
- [ ] No hardcoded content in PHP templates
- [ ] DM Sans (all weights) visible in DevTools → Network → Fonts
- [ ] Heading, subtext, CTA animate in sequentially from top on page load
- [ ] CTA underline draws in left→right on mouseenter
- [ ] CTA underline draws out right→left on mouseleave
- [ ] No GSAP errors in console
- [ ] `npm run dev` builds without errors

---

## 9. Technical Plan (The Contract)

### Step 1 — Fonts
Modify `inc/Fonts/Component.php` → add `'DM Sans'` with full weight array to `get_google_fonts()`.

### Step 2 — Scaffold PHP component
Run `npm run create-rig-component "Homepage Hero"` — this auto-wires the component into `inc/Theme.php`.
Then implement:
- `initialize()` → `add_filter( 'wp_rig_js_files', ... )`
- `filter_register_script()` → registers `homepage-hero.min.js`, front-page only

### Step 3 — EZ Customizer
Modify `inc/EZ_Customizer/themeCustomizeSettings.json` — add `homepage_hero` section + 5 settings.

### Step 4 — Template Part
Create `template-parts/homepage-hero.php`:
- Read all 5 values via `get_theme_mod()`
- Render `<section class="homepage-hero">` with inline `style="background-image:..."` for the media image
- `<h1 class="hero-heading">`, `<p class="hero-subtext">`, `<a class="hero-cta">` with `<span class="hero-cta__line">`
- Escape all output: `esc_url()`, `esc_html()`, `esc_attr()`

### Step 5 — CSS Partial
Create `assets/css/src/_homepage-hero.css`:
- CSS custom properties for all design tokens
- Full-viewport section, `#fbc3b6` background fallback
- Two-layer image stacking via absolute positioning
- Content block: lower-left, `60px` bottom padding
- CTA line: `position: absolute`, `height: 1px`, `width: 100%`, `background: white`, `transform-origin: left center`

### Step 6 — Import CSS
Add `@import '_homepage-hero.css';` to `assets/css/src/style.css`.

### Step 7 — JS Animation
Create `assets/js/src/homepage-hero.js` with GSAP entrance timeline + CTA underline hover (as specified in §5 above).

### Step 8 — Front Page Template
Create or update `front-page.php` to call `get_template_part( 'template-parts/homepage-hero' )`.

### Step 9 — Build & Verify
Run `npm run dev` and verify:
- Hero renders at `http://localhost:8881`
- Text animates in on load (heading → subtext → CTA)
- CTA underline draws in/out on hover
- DM Sans loads in DevTools Network tab

---

## 10. Out of Scope

- Mobile/responsive breakpoints (separate task)
- GSAP scroll animations on the hero (separate task)
- ACF integration (Customizer is sufficient for this phase)
