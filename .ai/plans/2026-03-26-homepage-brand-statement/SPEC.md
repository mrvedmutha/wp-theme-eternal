# SPEC: Homepage Brand Statement Section

**Date:** 2026-03-26
**Figma Node:** `694:1453` — Brand Statement / Intro Text Block
**Branch:** `home/hero`
**Status:** AWAITING APPROVAL

---

## 1. What We're Building

A full-width "brand statement" section that sits directly below the homepage hero. It has:

- An **800px tall** container with background `#f5f5f5`
- A **sticky inner block** that travels from `top: 80px` → rests at `bottom: 80px` as the user scrolls through the section
- The **Eternal Labs infinity logo SVG** (inserted via page editor / InnerBlocks)
- A large **editorial paragraph** in Cormorant Garamond 40px
- A **GSAP scroll-linked word reveal**: all words start grey, and as scroll progresses from the top of the section, each word transitions to dark (`#021f1d`) one by one with a slight fade

---

## 2. Approach: Gutenberg Block

Following the existing `wp-rig/homepage-hero` pattern, this will be a custom block scaffolded with `npm run block:new`.

**Block name:** `wp-rig/homepage-brand-statement`

The block will use **InnerBlocks** so the user can insert the logo SVG via the page editor (Image block or SVG block), followed by the text.

Alternatively, if InnerBlocks adds complexity, the text can be a `RichText` attribute and the logo SVG can be a `MediaUpload` attribute — **to be confirmed during build.**

---

## 3. Files to Create / Modify

| File | Action | Purpose |
|------|--------|---------|
| `blocks/homepage-brand-statement/` | **Create** (via `npm run block:new`) | Block scaffold |
| `assets/css/src/_homepage-brand-statement.css` | **Create** | Section styles |
| `assets/js/src/homepage-brand-statement.js` | **Create** | GSAP ScrollTrigger word reveal |
| `inc/Homepage_Brand_Statement/Component.php` | **Create** | Conditionally enqueue JS |
| `inc/Theme_Support/Component.php` | **Modify** | Register new component |

---

## 4. CSS Architecture

```
.homepage-brand-statement          → 800px tall, bg #f5f5f5, position: relative
.homepage-brand-statement__sticky  → position: sticky, top: 80px, padding: 80px 40px
                                     (sticky stops 80px from container bottom naturally)
.homepage-brand-statement__text    → Cormorant Garamond 400, 40px, lh 48px, ls -2px
.homepage-brand-statement__word    → each word in a <span>, color: var(--word-dim, #b0b0b0)
                                     GSAP will animate color → #021f1d + opacity 0.4 → 1
```

**Sticky stop at bottom 80px:** Achieved by setting `padding-bottom: 80px` on the outer container. CSS sticky naturally stops when its bottom edge meets the container's bottom edge.

---

## 5. JS / GSAP Animation

```
Library:  GSAP + ScrollTrigger (already installed)
Trigger:  start of section hitting top of viewport (start: "top top")
Scrub:    true (scroll-linked, not one-shot)
Effect:   each word span animates: color #b0b0b0 → #021f1d + opacity 0.4 → 1
Stagger:  words reveal sequentially via GSAP stagger on the scrubbed timeline
```

**Word splitting:** Done in JS (no SplitText plugin needed) — wrap each word in `<span class="homepage-brand-statement__word">` at runtime.

---

## 6. What's Out of Scope

- Mobile responsive styles (separate task)
- Customizer settings for the text content
- Any other homepage sections

---

## 7. Open Questions (flagged, not blocking)

- Should the text be editable via RichText in the block editor, or is it acceptable to hardcode the copy in the PHP render template for now?
- Should the logo SVG slot be InnerBlocks or a MediaUpload control?

---

## APPROVAL REQUIRED

Please confirm before implementation begins.
