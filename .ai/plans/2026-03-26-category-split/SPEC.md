# SPEC: Category Split Block

**Date:** 2026-03-26
**Slug:** `category-split`
**Branch:** `homepage` (feature lives here until merged)

---

## 1. Mission Statement

Scaffold a new dynamic Gutenberg block — `wp-rig/category-split` — that renders two side-by-side product category panels with full-bleed images, a sticky text overlay on desktop, a static two-column layout on tablet, and a stacked single-column layout on mobile.

---

## 2. Design Reference

- **Figma (Desktop):** node `694:1459` — split two-panel, sticky text, white text on image
- **Figma (Mobile):** node `715:1898` — stacked panels, image then text below, dark text

### Typography tokens used

| Role             | Family                   | Style        | Size  | Line-h | Tracking |
|------------------|--------------------------|--------------|-------|--------|----------|
| Category name (desktop) | Cormorant Garamond | Light Italic | 32px  | 42px   | 0        |
| Category name (mobile)  | Cormorant Garamond | Italic       | 24px  | 31px   | 0        |
| Subtitle         | Maison Neue              | Book         | 13px  | 20px   | 0.13px   |
| Discover label   | Maison Neue              | Medium       | 11px  | 14px   | 2px      |

Maps to existing CSS variables: `--font-display`, `--font-body`.

> **Flag:** Mobile text uses `#394342` (warm charcoal). Closest existing token is `--color-text` (`#021f1d` — deep green). These differ visually. Recommend using `--color-text` for now and adding `--color-charcoal: #394342` to `_custom-properties.css` in a follow-up if sign-off confirms the difference matters.

---

## 3. Responsive Behaviour

| Breakpoint       | Query token      | Layout                      | Scroll behaviour     | Text colour |
|------------------|------------------|-----------------------------|----------------------|-------------|
| Desktop (>1024px) | (default)       | Two equal-width columns     | Text is `sticky top:0` (CSS only) | White on image |
| Tablet (≤1024px) | `--bp-tablet`   | Two equal-width columns     | Static — no sticky   | White on image |
| Mobile (≤768px)  | `--bp-mobile-md`| Single column, stacked      | No sticky            | Dark (`--color-text`) below image |

---

## 4. Block Attributes

Ten attributes total — five per panel.

| Attribute            | Type    | Default | Description                    |
|----------------------|---------|---------|--------------------------------|
| `panel1ImageId`      | integer | 0       | WP media library ID            |
| `panel1ImageUrl`     | string  | `""`    | Image src (resolved at render) |
| `panel1Name`         | string  | `""`    | Category name (e.g. SkinCare)  |
| `panel1Subtitle`     | string  | `""`    | Descriptor line                |
| `panel1DiscoverUrl`  | string  | `"/"`   | Href for Discover link         |
| `panel2ImageId`      | integer | 0       | WP media library ID            |
| `panel2ImageUrl`     | string  | `""`    | Image src (resolved at render) |
| `panel2Name`         | string  | `""`    | Category name                  |
| `panel2Subtitle`     | string  | `""`    | Descriptor line                |
| `panel2DiscoverUrl`  | string  | `"/"`   | Href for Discover link         |

---

## 5. User Stories

- **As a content editor**, I can upload separate images for Panel 1 and Panel 2 via the Inspector sidebar.
- **As a content editor**, I can set the category name, subtitle, and discover URL for each panel independently.
- **As a site visitor on desktop**, I see two full-height panels side by side; as I scroll, the category text sticks to the top while the image scrolls behind it.
- **As a site visitor on tablet**, I see the same two-column layout but without the scroll-pin effect.
- **As a site visitor on mobile**, I see each panel stacked — image above, text (dark on white/grey background) below.

---

## 6. Architectural Fit

- **Scaffold:** `npm run block:new -- category-split --title="Category Split" --dynamic`
  - No `--view` flag — scroll-pin is pure CSS sticky, no frontend JS required.
- **Auto-registration:** `inc/Blocks/Component.php` discovers `assets/blocks/category-split/block.json` automatically.
- **Styles:** Block-scoped `style.css` loaded via `enqueue_block_style()` — no changes to `global.css`.
- **CSS custom media:** Consumes existing `--bp-tablet` and `--bp-mobile-md` from `_custom-media.css` — no new breakpoints.
- **CSS variables:** Uses `--font-display`, `--font-body`, `--color-white`, `--color-text`, `--space-*`. No new variables required for v1.

---

## 7. File Plan

```
assets/blocks/category-split/
├── block.json          ← metadata, attributes, asset references
├── src/
│   ├── index.js        ← registerBlockType entry
│   └── edit.js         ← InspectorControls + ServerSideRender (mirrors homepage-hero pattern)
├── render.php          ← server-side template, BEM markup
├── style.css           ← frontend styles (desktop → tablet → mobile)
└── editor.css          ← minimal editor chrome
```

### BEM class map

```
.category-split                     ← <section> wrapper
.category-split__panel              ← each half-column <div> (×2)
.category-split__image              ← <img> absolutely fills the panel
.category-split__content-track      ← full-height block to give sticky room
.category-split__content            ← sticky text bar (position: sticky; top: 0)
.category-split__meta               ← left side: name + subtitle
.category-split__name               ← <p> H3 Italic
.category-split__subtitle           ← <p> Body Small
.category-split__discover           ← right side: <a> DISCOVER + rule
.category-split__discover-label     ← "DISCOVER" uppercase text
.category-split__discover-rule      ← 65px horizontal rule <span>
```

### CSS sticky mechanic (desktop only)

```css
/* Section is taller than viewport so scrolling occurs */
.category-split {
  display: flex;
  min-height: 100svh; /* ~viewport height — image fills it */
}

.category-split__panel {
  flex: 1 0 0;
  position: relative;
  overflow: hidden;
  min-height: 100svh;
}

.category-split__image {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Full-height track gives sticky content room to travel */
.category-split__content-track {
  position: relative;
  height: 100%;
  pointer-events: none;
}

.category-split__content {
  position: sticky;
  top: 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--space-16) var(--space-10); /* 64px 40px */
  color: var(--color-white);
  pointer-events: auto;
}

/* ── Tablet: drop sticky, keep two columns ── */
@media (--bp-tablet) {
  .category-split__content {
    position: static;
  }
}

/* ── Mobile: stack vertically, text below image ── */
@media (--bp-mobile-md) {
  .category-split {
    flex-direction: column;
    gap: var(--space-16); /* 64px */
    padding: var(--space-10) 0; /* 40px */
  }

  .category-split__panel {
    min-height: unset;
  }

  .category-split__image {
    position: static; /* back to flow so panel wraps it */
    height: 450px;
    object-fit: cover;
    width: 100%;
  }

  .category-split__content-track {
    height: auto;
  }

  .category-split__content {
    position: static;
    padding: 0 var(--space-5); /* 0 20px */
    color: var(--color-text); /* dark on white */
  }

  .category-split__name {
    font-size: 24px;
    line-height: 31px;
  }
}
```

---

## 8. Implementation Steps

1. **Scaffold** — `npm run block:new -- category-split --title="Category Split" --dynamic`
2. **`block.json`** — add 10 attributes, set `"supports": { "html": false, "align": ["full"] }`
3. **`src/edit.js`** — two `PanelBody` groups ("Panel 1", "Panel 2"), each with `MediaUpload`, `TextControl` (name, subtitle, URL). Use `ServerSideRender`.
4. **`src/index.js`** — import `edit.js`, register block (mirrors `homepage-hero/src/index.js`)
5. **`render.php`** — BEM markup, two panels, resolve image URLs via `wp_get_attachment_image_url()`, escape all output
6. **`style.css`** — desktop base styles → tablet overrides → mobile overrides (using custom media tokens)
7. **`editor.css`** — minimal: constrain `ServerSideRender` wrapper height in editor
8. **Build** — `npm run build` (or `npm run dev` during development)
9. **Verify** — insert block on homepage, confirm:
   - Two panels render with images
   - Text sticks on desktop scroll
   - Tablet: static, two columns
   - Mobile: stacked, dark text below image

---

## 9. Success Metrics

- [ ] Block appears in block inserter under "Widgets" category
- [ ] Both panels independently configurable in Inspector sidebar
- [ ] Desktop: `position: sticky` text visible while scrolling; image scrolls behind
- [ ] Tablet (≤1024px): two columns, no sticky
- [ ] Mobile (≤768px): stacked, 450px images, text below in `--color-text`
- [ ] `npm run ai:check` passes with no violations
- [ ] No JS errors in browser console
- [ ] Images resolved via `wp_get_attachment_image_url()` (handles media library moves)
- [ ] All user-editable strings escaped with `esc_html()` / `esc_url()`

---

## 10. Out of Scope (v1)

- Link wrapping the entire panel (only Discover CTA is linked)
- `mix-blend-luminosity` image treatment (user confirmed: upload pre-treated image instead)
- More than two panels
- Animation beyond CSS sticky
