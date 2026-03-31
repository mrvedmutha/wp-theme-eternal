# Product Gallery Lightbox & Navigation Enhancement

**Feature Planning Date:** March 31, 2026  
**Status:** ✅ Approved — Implementation in Progress  
**Confidence Score:** 98%

---

## Mission Statement

Enhance the PDP (Product Detail Page) gallery with interactive navigation controls and a full-screen lightbox modal, providing users with an immersive product browsing experience while maintaining WP Rig's performance and accessibility standards.

---

## User Stories

1. **As a customer**, I want to see navigation controls when hovering over the product image, so I can browse through product photos without scrolling to thumbnails.
2. **As a customer**, I want to click on the main product image to view it in full-screen, so I can examine product details more closely.
3. **As a customer**, I want to navigate through images in the lightbox using keyboard arrows or on-screen controls, so I can explore all product photos efficiently.
4. **As a mobile user**, I want intuitive touch controls with visible navigation hints, so I can browse product images easily on my device.
5. **As a customer**, I want to close the lightbox by clicking outside the image or pressing ESC, so I have multiple intuitive exit options.

---

## Design Compliance

### Current State

- **Style Guide:** `.ai/STYLE-GUIDE.md` does not exist yet
- **Existing Patterns:** Gallery uses fade transitions (100ms opacity), sticky positioning, and thumbnail-driven navigation
- **CSS Variables:** Uses scoped variables (`--pdp-gallery-thumb-w`, `--pdp-gallery-hero-w`, etc.)

### Design Requirements

#### Hover Controls (Gallery Hero)

- **Visual Style:** Minimal gradient overlays on left/right edges (transparent to semi-opaque)
- **Navigation Icons:** Chevron-left and chevron-right icons (inline SVG, to be created)
- **Interaction:** Gradients visible on hover; icons always visible but subtle
- **Effect:** Subtle zoom effect on hero image hover (scale: 1.02-1.05)

#### Lightbox Modal

- **Backdrop:** Semi-transparent dark overlay (rgba(0, 0, 0, 0.9))
- **Image Container:** Centered, max-width respects viewport, maintains aspect ratio
- **Navigation Controls:**
    - Chevron buttons with blend mode overlay
    - Gradients appear on interaction, fade after 3 seconds
    - Positioned absolutely left/right of image
- **Close Button:** X icon (using existing `close-btn.svg`), top-right corner
- **Thumbnail Strip:** Bottom-aligned horizontal strip (if multiple images exist)
    - Auto-scroll active thumbnail to center
    - Same thumbnail interaction as main gallery
- **Animation:** Fade in/out (300ms)

#### Responsive Behavior

- **Mobile/Touch:**
    - Navigation icons always visible in blend mode
    - Tap to reveal gradients (fade after 3s)
    - Touch swipe support for image navigation (future enhancement marker)
- **Edge Case:** Single image shows in modal but hides navigation arrows and thumbnail strip

---

## Architectural Fit

### Components Involved

- **Existing:** `Product_Detail\Component` ([inc/Product_Detail/Component.php](inc/Product_Detail/Component.php))
- **Template:** [template-parts/product/part-pdp-gallery.php](template-parts/product/part-pdp-gallery.php)
- **JavaScript:** [assets/js/src/product-detail.ts](assets/js/src/product-detail.ts)
- **CSS:** [assets/css/src/\_product-detail.css](assets/css/src/_product-detail.css)

### New Assets Required

1. **SVG Icons:**
    - `chevron-left.svg` (create in `/assets/svg/`)
    - `chevron-right.svg` (create in `/assets/svg/`)
    - Use existing `close-btn.svg` for modal close

2. **JavaScript Modules:**
    - Extend `product-detail.ts` with:
        - Lightbox modal class
        - Keyboard event handlers (left/right arrows, ESC)
        - Focus trap for accessibility
        - Lazy-loading for full-size images
        - Auto-hide gradient timer

3. **CSS Additions:**
    - Gallery hover controls (gradients, chevrons, zoom effect)
    - Modal styles (backdrop, container, navigation, close button)
    - Thumbnail strip for modal
    - Responsive breakpoints

### Hooks & Filters

No new hooks required. Existing `Product_Detail\Component::enqueue_assets()` already loads JS/CSS on product pages.

---

## Technical Plan (The "Contract")

### Phase 1: Asset Creation

#### 1.1 Create SVG Icons

```bash
# Create chevron icons (manual design in assets/svg/)
# Style: Minimal, 24×24px viewBox, sharp angles
```

**Files to Create:**

- `/assets/svg/chevron-left.svg`
- `/assets/svg/chevron-right.svg`

**Icon Spec:**

```svg
<!-- chevron-left.svg -->
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>

<!-- chevron-right.svg (mirror) -->
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
```

### Phase 2: Template Enhancement

#### 2.1 Update `part-pdp-gallery.php`

**Changes Required:**

1. Add navigation controls to `.pdp-gallery__hero`:
    - Wrap hero image in interactive container
    - Add chevron button elements (left/right)
    - Inline SVG icons with proper ARIA labels
    - Add gradient overlay divs
2. Add data attributes for lightbox functionality:
    - `data-lightbox-trigger` on hero container
    - `data-gallery-index` on current image
    - `data-total-images` count
3. Create hidden modal markup:
    - Modal container (`data-pdp-modal`)
    - Backdrop (`data-modal-backdrop`)
    - Image container with navigation
    - Close button
    - Thumbnail strip (conditional)

**Pseudo-Structure:**

```php
<div class="pdp-gallery__hero" data-lightbox-trigger>
  <!-- Gradient overlays -->
  <div class="pdp-gallery__gradient pdp-gallery__gradient--left"></div>
  <div class="pdp-gallery__gradient pdp-gallery__gradient--right"></div>

  <!-- Navigation controls -->
  <button class="pdp-gallery__nav pdp-gallery__nav--prev" aria-label="Previous image">
    <?php // Inline chevron-left SVG ?>
  </button>

  <img class="pdp-gallery__hero-img" ... />

  <button class="pdp-gallery__nav pdp-gallery__nav--next" aria-label="Next image">
    <?php // Inline chevron-right SVG ?>
  </button>
</div>

<!-- Modal (hidden by default) -->
<div class="pdp-modal" data-pdp-modal hidden>
  <div class="pdp-modal__backdrop" data-modal-backdrop></div>
  <div class="pdp-modal__container">
    <button class="pdp-modal__close" aria-label="Close">
      <?php // Inline close-btn SVG ?>
    </button>
    <div class="pdp-modal__content">
      <!-- Similar navigation structure -->
    </div>
    <div class="pdp-modal__thumbs" role="list">
      <!-- Thumbnail strip -->
    </div>
  </div>
</div>
```

### Phase 3: JavaScript Implementation

#### 3.1 Extend `product-detail.ts`

**New Classes/Functions:**

1. `PDPLightbox` class:
    - Constructor: Initialize modal, bind events
    - `open(index: number)`: Show modal at specific image
    - `close()`: Hide modal, restore scroll
    - `navigate(direction: 'prev' | 'next')`: Switch images
    - `updateImage(index: number)`: Lazy-load and display image
    - `updateThumbnails(index: number)`: Update active state, auto-scroll
    - `handleKeyboard(event: KeyboardEvent)`: Arrow keys, ESC
    - `handleGradientTimer()`: Auto-hide after 3s
    - `trapFocus()`: Accessibility focus management

2. Navigation enhancements:
    - Bind gallery hero navigation to existing thumbnail system
    - Sync hero navigation with modal navigation
    - Gradient visibility toggle on hover/touch

**Event Bindings:**

```typescript
// Gallery hero interactions
heroContainer.addEventListener("click", (e) => {
	if (!e.target.closest(".pdp-gallery__nav")) {
		lightbox.open(currentIndex);
	}
});

// Keyboard navigation
document.addEventListener("keydown", (e) => {
	if (lightbox.isOpen) {
		if (e.key === "ArrowLeft") lightbox.navigate("prev");
		if (e.key === "ArrowRight") lightbox.navigate("next");
		if (e.key === "Escape") lightbox.close();
	}
});

// Click outside to close
backdrop.addEventListener("click", () => lightbox.close());
```

#### 3.2 Lazy Loading Strategy

```typescript
interface ImageCache {
  [index: number]: {
    loaded: boolean;
    src: string;
    srcset?: string;
  };
}

// Preload adjacent images when modal opens
preloadAdjacentImages(currentIndex: number) {
  const toPreload = [currentIndex - 1, currentIndex + 1];
  toPreload.forEach(idx => {
    if (idx >= 0 && idx < totalImages && !imageCache[idx]?.loaded) {
      // Create Image object to trigger browser preload
    }
  });
}
```

### Phase 4: CSS Styling

#### 4.1 Add to `_product-detail.css`

**New Sections:**

1. **Gallery Hero Controls:**

```css
/* Gradient overlays */
.pdp-gallery__gradient {
	position: absolute;
	top: 0;
	height: 100%;
	width: 120px;
	opacity: 0;
	transition: opacity 0.3s ease;
	pointer-events: none;
}

.pdp-gallery__gradient--left {
	left: 0;
	background: linear-gradient(to right, rgba(0, 0, 0, 0.3), transparent);
}

.pdp-gallery__gradient--right {
	right: 0;
	background: linear-gradient(to left, rgba(0, 0, 0, 0.3), transparent);
}

.pdp-gallery__hero:hover .pdp-gallery__gradient {
	opacity: 1;
}

/* Zoom effect */
.pdp-gallery__hero:hover .pdp-gallery__hero-img {
	transform: scale(1.03);
	transition: transform 0.3s ease;
}

/* Navigation controls */
.pdp-gallery__nav {
	position: absolute;
	top: 50%;
	transform: translateY(-50%);
	background: transparent;
	border: none;
	color: #fff;
	width: 48px;
	height: 48px;
	cursor: pointer;
	opacity: 0.7;
	transition: opacity 0.3s ease;
	z-index: 10;
}

.pdp-gallery__nav:hover {
	opacity: 1;
}

.pdp-gallery__nav--prev {
	left: 20px;
}
.pdp-gallery__nav--next {
	right: 20px;
}
```

2. **Modal Styles:**

```css
.pdp-modal {
	position: fixed;
	inset: 0;
	z-index: 9999;
	display: flex;
	align-items: center;
	justify-content: center;
	opacity: 0;
	pointer-events: none;
	transition: opacity 0.3s ease;
}

.pdp-modal:not([hidden]) {
	opacity: 1;
	pointer-events: auto;
}

.pdp-modal__backdrop {
	position: absolute;
	inset: 0;
	background: rgba(0, 0, 0, 0.9);
}

.pdp-modal__container {
	position: relative;
	max-width: 90vw;
	max-height: 90vh;
	z-index: 1;
}

.pdp-modal__close {
	position: absolute;
	top: 20px;
	right: 20px;
	background: transparent;
	border: none;
	color: #fff;
	width: 32px;
	height: 32px;
	cursor: pointer;
	z-index: 10;
}

/* Thumbnail strip in modal */
.pdp-modal__thumbs {
	display: flex;
	gap: 8px;
	padding: 20px;
	overflow-x: auto;
	scroll-behavior: smooth;
	justify-content: center;
}

/* Responsive */
@media (max-width: 768px) {
	.pdp-modal__container {
		max-width: 95vw;
		max-height: 95vh;
	}

	.pdp-gallery__nav,
	.pdp-modal__nav {
		mix-blend-mode: difference; /* Ensure visibility on mobile */
	}
}
```

3. **Gradient Auto-Hide (via JS class toggle):**

```css
.pdp-modal--gradient-visible .pdp-modal__gradient {
	opacity: 1;
}

/* Auto-remove after 3s via setTimeout in JS */
```

### Phase 5: Testing & Validation

#### 5.1 Manual Testing Checklist

- [ ] Gallery navigation works on hover (desktop)
- [ ] Clicking hero image opens lightbox
- [ ] Lightbox navigation (buttons + keyboard arrows)
- [ ] ESC key closes modal
- [ ] Click outside image closes modal
- [ ] Thumbnail strip scrolls active into view
- [ ] Single-image products hide navigation
- [ ] Lazy loading works (check Network tab)
- [ ] Focus trap prevents tabbing outside modal
- [ ] Mobile: gradient appears/disappears on touch

#### 5.2 Automated Testing (Future)

**Playwright E2E Tests** (refer to [E2E Testing skill](../e2e-testing/SKILL.md)):

- Gallery navigation interactions
- Lightbox open/close scenarios
- Keyboard navigation
- Accessibility audit (focus management, ARIA labels)

#### 5.3 Code Quality

```bash
# Run WP Rig standards check
npm run ai:check

# Specific checks
npm run lint:css    # CSS validation
npm run lint:js     # TypeScript/JS linting
composer phpcs      # PHP coding standards
```

---

## Success Metrics

1. ✅ **Functional:**
    - All navigation methods work (buttons, keyboard, thumbnails)
    - Modal opens/closes via all specified triggers
    - Images lazy-load correctly

2. ✅ **Performance:**
    - No layout shift (CLS) from gallery enhancements
    - Modal animation smooth (60fps)
    - Images preload efficiently without blocking

3. ✅ **Accessibility:**
    - Keyboard navigation complete (arrows, ESC, Tab)
    - Focus trap in modal
    - ARIA labels on all interactive elements
    - Screen reader announcements for image changes

4. ✅ **Visual:**
    - Gradients subtle, don't distract
    - Zoom effect smooth
    - Modal responsive across devices
    - Animations match WP Rig's fade preference

5. ✅ **Code Quality:**
    - Passes `npm run ai:check`
    - No console errors/warnings
    - TypeScript types correct
    - CSS follows WP Rig variable conventions

---

## Implementation Steps (Sequential Order)

1. **Create SVG icons** (`chevron-left.svg`, `chevron-right.svg`)
2. **Update PHP template** (`part-pdp-gallery.php`) with navigation markup + modal structure
3. **Extend TypeScript** (`product-detail.ts`) with `PDPLightbox` class
4. **Add CSS styles** (`_product-detail.css`) for controls + modal
5. **Build assets** (`npm run build`)
6. **Manual testing** (checklist above)
7. **Code quality check** (`npm run ai:check`)
8. **User acceptance review**

---

## ✅ Approved Decisions

- [x] **Gradient Color:** Match theme primary color
- [x] **Zoom Level:** 1.05x scale transform
- [x] **Animation Duration:** 300ms for modal fade (default approved)
- [x] **Thumbnail Count in Modal:** Show all available thumbnails
- [x] **Touch Swipe:** Include gesture navigation in initial implementation

---

## Notes

- **No WooCommerce Core Override:** This enhancement stays within theme territory; no WC template overrides needed beyond what already exists.
- **Backwards Compatibility:** Existing thumbnail navigation remains functional; enhancements are additive.
- **Style Guide Creation:** Post-implementation, document this pattern in `.ai/STYLE-GUIDE.md` for future gallery components.

---

**Ready for Review:** Please confirm if this specification matches your vision, or request clarifications on any section before I proceed to implementation.
