# SPEC: Eternal Product Detail Page (PDP) Template

**Date:** 2026-03-30
**Status:** AWAITING APPROVAL — v1
**Feature slug:** `eternal-pdp`

**Figma File:** `aJ4VjKdFNahXA6Ly4jkRtJ`

| Section | Node ID |
|---|---|
| Buy Box — Simple | `694:5674` |
| Buy Box — Variable (Variants) | `694:5379` |
| Buy Box — Subscription | `694:7214` |
| Editorial Feature panel (skincare) #1 | `694:6331` |
| Editorial Feature panel (skincare) #2 | `694:6338` |
| Key Ingredients — 2-col white | `694:6348` |
| Editorial Feature panel (supplement) #1 | `694:7314` |
| Editorial Feature panel (supplement) #2 | `694:7321` |
| Key Ingredients — 3-col tinted with title | `694:7943` |

**Depends on:**
- `eternal-product-meta` plugin — product meta fields
- `eternal-subscription` plugin — supply plan tiers (`ESP_Frontend` class)
- WooCommerce — product object, variations, cart

---

## 1. Mission Statement

Build the WooCommerce single-product (PDP) template for the Eternal Labs storefront. The PDP supports three buy-box states (simple, variable, subscription), a sticky two-column gallery layout, an accordion detail section, and a series of editorial and key-ingredient sections below the buy box — all driven by the product meta registered in `eternal-product-meta` and supply plan data from `eternal-subscription`.

No UI chrome is hardcoded. Every section renders conditionally based on whether its data field is populated.

---

## 2. Design Compliance

### Design tokens (from Figma)

| Token | Value | Usage |
|---|---|---|
| `--color-primary` | `#021f1d` | Text, CTA bg, borders |
| `--color-secondary` | `#868686` | Muted text, breadcrumb |
| `--color-bg-tint` | `#f5f5f5` | Image containers, tinted section bg |
| `--color-white` | `#ffffff` | CTA text, white bg sections |
| `--font-display` | Cormorant Garamond | H1–H5, editorial headlines |
| `--font-body` | Maison Neue (`font-weight: 400`) | Body copy, labels, accordions |
| `--font-ui` | Maison Neue (`font-weight: 500`) | Eyebrow, buttons, caption |

### Typography scale (from Figma)

| Style name | Family | Weight | Size | Line-height | Tracking |
|---|---|---|---|---|---|
| `Eyebrow` | Maison Neue | 500 | 9px | 12px | 1.26px |
| `H2` | Cormorant Garamond | 400 | 40px | 48px | -2px |
| `H3` | Cormorant Garamond | 400 | 32px | 40px | 0 |
| `H3 Italic` | Cormorant Garamond | 300 italic | 32px | 42px | 0 |
| `H5` | Cormorant Garamond | 400 | 20px | 26px | 0 |
| `Body/Base` | Maison Neue | 400 | 15px | 23px | 0.15px |
| `Body/Small` | Maison Neue | 400 | 13px | 20px | 0.13px |
| `Body/Large` | Maison Neue | 400 | 24px | normal | 0 |
| `Caption` | Maison Neue | 400 | 11px | 16px | 0.88px |
| `Button Small` | Maison Neue | 500 | 10px | 13px | 1.6px |

> **Font note:** Only one Maison Neue file exists (`assets/fonts/MaisonNeue/MaisonNeue.*`). The `_fonts.css` `@font-face` declarations map it to weight ranges `300–400` and `500–700` (browser synthesises heavier weights from the same file). All CSS must use `font-family: 'Maison Neue'` with the appropriate `font-weight` value — never reference `Maison Neue Book`, `Maison Neue Medium`, or `Maison Neue Bold` as separate families.

### Style guide reference
CSS custom properties already defined in `assets/css/src/_custom-properties.css` must be reused. New properties for PDP-specific tokens to be appended.

---

## 3. Architectural Fit

**Template type:** Classic WooCommerce template override
**Theme type:** `classic` (per `config/config.json`)

### New files to create

```
wp-content/themes/wprig/
├── woocommerce/                              ← NEW directory (WC template overrides)
│   ├── single-product.php                   ← Main PDP wrapper template
│   └── content-single-product.php           ← Product content shell (clears WC hooks)
│
├── template-parts/
│   └── product/                             ← NEW directory
│       ├── part-pdp-breadcrumb.php          ← Breadcrumb bar
│       ├── part-pdp-gallery.php             ← Sticky left: thumbnails + hero image
│       ├── part-pdp-buybox.php              ← Right column: all buy-box states
│       ├── part-pdp-buybox-simple.php       ← Qty + CTA (no extras)
│       ├── part-pdp-buybox-variable.php     ← Variant dropdown(s) + qty + CTA
│       ├── part-pdp-buybox-subscription.php ← Radio cards + plan dropdown + qty + CTA
│       ├── part-pdp-accordion.php           ← Description / Ingredients / How-to panels
│       ├── part-pdp-feature.php             ← Single editorial feature panel (sticky text + image)
│       └── part-pdp-ingredients.php         ← Key ingredients section (1–3 cards)
│
├── inc/
│   └── Product_Detail/
│       └── Component.php                    ← NEW component: template tags + asset registration
│
└── assets/
    ├── css/src/
    │   └── _product-detail.css              ← NEW CSS partial for PDP
    └── js/src/
        └── product-detail.ts                ← NEW TS: accordion, qty, variants, subscription toggle
```

### WC template override strategy

WooCommerce auto-discovers template overrides in `{theme}/woocommerce/`. The `single-product.php` override gives us full layout control. We clear all default WC action hooks via `remove_action()` inside `content-single-product.php` and rebuild the layout entirely with our template parts.

### Component registration

`inc/Product_Detail/Component.php` implements `Component_Interface` and is registered in `inc/Theme.php`. It is responsible for:
- Enqueuing `_product-detail.css` and `product-detail.ts` on `is_product()` pages only
- Providing static template tag methods (e.g. `wp_rig()->get_product_meta()`, `wp_rig()->get_supply_plans()`) used by template parts
- Localising the JS with variation data and subscription tier data

---

## 4. Data Layer

### 4.1 Three data sources

Every template part reads data through the `Product_Detail\Component` template tag methods. No template part accesses raw meta keys or plugin classes directly.

```php
// In any template part:
$meta    = wp_rig()->get_product_meta( get_the_ID() );
$plans   = wp_rig()->get_supply_plans( get_the_ID() );
$product = wc_get_product( get_the_ID() );
```

### 4.2 `get_product_meta( int $product_id ): array`

Returns a sanitised, decoded array of all product meta fields:

| Key | Type | Source meta key | Notes |
|---|---|---|---|
| `caption` | `string` | `product_caption` | Eyebrow label |
| `french_text` | `string` | `product_french_text` | H5 subtitle |
| `tagline` | `string` | `product_tagline` | Short tagline |
| `card_bg` | `string` | `product_card_bg` | Hex bg colour |
| `buy_box_amount` | `string` | `product_buy_box_amount` | e.g. "100" |
| `buy_box_unit` | `string` | `product_buy_box_unit` | e.g. "ml" |
| `buy_box_ingredients` | `string` | `product_buy_box_ingredients` | Markdown/plain text |
| `buy_box_caution` | `string` | `product_buy_box_caution` | Plain text |
| `buy_box_how_to_apply` | `string` | `product_buy_box_how_to_apply` | Markdown bold supported |
| `buy_box_bonus_tip` | `string` | `product_buy_box_bonus_tip` | Optional tip |
| `ingredients_title` | `string` | `product_ingredients_title` | Section heading |
| `key_ingredients` | `array` | `product_key_ingredients` | JSON decoded: `[{image_id, image_url, name, description}]` |
| `features` | `array` | `product_features` | JSON decoded: `[{image_id, image_url, heading, body}]` |
| `benefits_bullets` | `string` | `product_benefits_bullets` | Newline-separated |
| `notes_top` | `string` | `product_notes_top` | |
| `notes_middle` | `string` | `product_notes_middle` | |
| `notes_base` | `string` | `product_notes_base` | |

### 4.3 `get_supply_plans( int $product_id ): array`

Delegates to `ESP_Frontend::get_active_tiers()` if `class_exists('ESP_Frontend')` and `ESP_Frontend::is_enabled()`. Returns `[]` otherwise.

Each tier element: `{ months, label, contents_note, mrp, final_price, currency, symbol }`

### 4.4 Text encoding cleanup

`product_features[].body` may contain literal escape sequences from the DB (`rn` for `\r\n`, `u2014` for `—`, `u2019` for `'`). The `get_product_meta()` method normalises these before returning:

```php
$body = str_replace( ['rn', 'u2014', 'u2019', 'u2013'], ["\n", '—', ''', '–'], $body );
```

---

## 5. Template Layout

### 5.1 Page structure

```
single-product.php
└── content-single-product.php
    ├── part-pdp-breadcrumb.php
    └── <div class="pdp-layout">
        ├── [LEFT] part-pdp-gallery.php          (sticky)
        └── [RIGHT] <div class="pdp-right">
            ├── part-pdp-buybox.php
            │   ├── Eyebrow (caption)
            │   ├── H3 product name + H5 french_text
            │   ├── Tagline
            │   ├── Stars + review count          (hidden if review_count === 0)
            │   ├── [Variable only] Variant selector
            │   ├── [Subscription only] Purchase mode radio cards
            │   ├── Price block                   (with strike-through if sale)
            │   ├── Unit label (buy_box_amount + buy_box_unit)
            │   ├── Quantity stepper + ADD TO BAG
            │   └── part-pdp-accordion.php
            │       ├── Description               (open by default)
            │       ├── Ingredients & Safety      (closed)
            │       └── How to Apply / How to Use (closed)
            └── [below buy box, full width]
                ├── part-pdp-feature.php          (×N, one per features[])
                └── part-pdp-ingredients.php      (conditional on key_ingredients)
```

### 5.2 Gallery (`part-pdp-gallery.php`)

- Left strip: 4 thumbnails, 80×100px each, `#f5f5f5` bg — main image first, then `gallery_image_urls[0..2]`
- Main panel: 555×700px, `#f5f5f5` bg, main product image centred with drop-shadow
- Both wrapped in `position: sticky; top: 0`
- Clicking a thumbnail swaps the main panel image (JS)

### 5.3 Buy Box states

**Determined by:**
```php
$is_variable     = $product->is_type( 'variable' );
$plans           = wp_rig()->get_supply_plans( get_the_ID() ); // calls ESP_Frontend::get_active_tiers()
$is_subscription = ! empty( $plans );
// Simple = neither of the above
// Edge case: ESP_Frontend::is_enabled() true but get_active_tiers() returns [] → treat as Simple
```

> **Edge case:** If `_esp_enabled = 1` on a product but no tiers have `active: true`, `get_active_tiers()` returns `[]`. In this state `$is_subscription` is `false` and the template renders the simple buy-box. The subscription radio cards are never shown with an empty plan dropdown.

**Simple:** no extra selector — renders price block immediately after stars.

**Variable:**
- Loop `$product->get_variation_attributes()` to render one `<select>` per attribute
- Label above: `Choose {Attribute Name}` (Caption style, 11px)
- Select styled as bordered rounded box (0.5px `#202727` border, 5px radius, 32px height, chevron icon)
- Default option: first variation label
- On change: JS calls `wp_ajax_woocommerce_get_variation` (WC built-in) to update price display and hidden variation ID input
- Price range shown until a valid variation is selected

**Subscription:**
- Two radio option cards, 437px wide, 0.5px `#777` border, 5px radius:
  1. **One Time Purchase** — price = `$product->get_price()` — right-aligned
  2. **Subscription** (default selected if `$plans` has active tiers):
     - Expands to show plan `<select>` dropdown (bordered, rounded, chevron)
     - Below dropdown: `contents_note` in Caption style
     - Right-aligned: tier `final_price` formatted in active currency
- Selecting a plan updates the MRP block price via JS
- Hidden input `eternal_supply_months` populated from selected plan on form submit

### 5.4 Price block

```
MRP  ₹{regular_price}̶  ₹{price}     ← sale state (strike-through on regular_price)
MRP  ₹{price}                         ← no sale
     / {buy_box_amount}{buy_box_unit}
(Incl. of all taxes)
```

Sale detection: `$product->get_regular_price() > $product->get_price()` — or per-variation for variable products.

For subscription state: price block updates dynamically via JS when a plan tier is selected.

### 5.5 Accordion (`part-pdp-accordion.php`)

Three panels, separated by 0.5px hairline dividers. Each panel: label row (left: uppercase 13px text, right: +/− icon) + collapsible content area.

**Panel 1 — Description** (open by default, minus icon):
- `$product->get_description()` via `wp_kses_post()` — preserves `<strong>`, `<p>`, `<br>`
- If `$meta['notes_top']` non-empty (all three notes set): renders "Key Notes" block:
  ```
  Key Notes
  Top: {notes_top}
  Middle: {notes_middle}
  Base: {notes_base}
  ```

**Panel 2 — Ingredients and Safety** (closed, plus icon):
- `$meta['buy_box_ingredients']` — rendered as paragraphs, `<strong>` preserved (markdown `**text**` converted to `<strong>`)
- `$meta['buy_box_caution']` — rendered below in `#868686` muted style

**Panel 3 — How to Apply / How to Use** (closed, plus icon):
- Label: "HOW TO APPLY" for skincare; "HOW TO USE" for supplements — determined by product category or a fallback: if `buy_box_how_to_apply` starts with `**Use:**` → label becomes "HOW TO USE"
- `$meta['buy_box_how_to_apply']` — markdown bold (`**text**`) converted to `<strong>`, newlines to `<br>` or `<p>` breaks
- If `$meta['buy_box_bonus_tip']` non-empty: renders below as a styled tip block

Markdown-to-HTML helper: a private `parse_markdown_light( string $text ): string` method on the Component converts `**bold**` → `<strong>bold</strong>` and `\r\n\r\n` → `</p><p>`.

### 5.6 Editorial Feature panels (`part-pdp-feature.php`)

Rendered once per `$meta['features']` item. Layout:

```
[LEFT ~40% sticky]              [RIGHT ~60% full-bleed image]
H3 Italic heading               800px tall, object-cover
Body/Base paragraphs            white bg
```

- Left: `padding-left: 40px; padding-top: 40px; position: sticky; top: 0`
- Right: 719px wide × 800px tall, `overflow: hidden`
- Image: `wp_get_attachment_image( $feature['image_id'], 'full' )` — falls back to `$feature['image_url']` if attachment unavailable
- Body text: `\r\n\r\n` splits into `<p>` elements; encoding sequences normalised by `get_product_meta()`

### 5.7 Key Ingredients section (`part-pdp-ingredients.php`)

Conditional: only renders if `count( $meta['key_ingredients'] ) > 0`.

**Structure:**
```
[If ingredients_title set]
  <section class="pdp-ingredients pdp-ingredients--tinted">
    <h2 class="pdp-ingredients__title">{ingredients_title}</h2>
[Else]
  <section class="pdp-ingredients">
```

Inside: flex row, `justify-content: center`, `gap: 10px`, `padding: 0 20px 80px`.

**Card count → column behaviour:**

| Count | Layout |
|---|---|
| 1 | Single card centred, `max-width: 460px` |
| 2 | Two cards centred, each `width: 460px` |
| 3 | Three cards, each `width: 460px` |

Each card:
- 460×550px image container, `#f5f5f5` bg, `overflow: hidden`
- If `image_id > 0`: `wp_get_attachment_image( $image_id, 'large' )`
- If `image_id === 0` or empty `image_url`: renders `#f5f5f5` placeholder box
- Below image: `name` (Body/Base, primary) + `description` (Body/Base, `#868686`)

---

## 6. JavaScript (`product-detail.ts`)

Single TypeScript file, loaded only on `is_product()`. Responsibilities:

### 6.1 Gallery thumbnail switching
- Click thumbnail → update main hero `<img>` `src` + `srcset`
- Active thumbnail gets CSS `--border` highlight

### 6.2 Accordion
- Click panel header → toggle `aria-expanded` on header + `hidden` on content
- Panel 1 starts open (`aria-expanded="true"`, content visible)
- Panels 2 and 3 start closed

### 6.3 Quantity stepper
- `+` button increments value; `−` button decrements (min: 1)
- Directly updates the hidden WC quantity `<input name="quantity">`

### 6.4 Variant switching (variable products only)
- On `<select>` change: collect all attribute selections, call WC AJAX endpoint `woocommerce_get_variation`
- On success: update price display, main image, availability message
- On no-match: reset price display to range
- Data localised via `wp_localize_script`: `{ nonce, ajaxUrl, productId, variations }`

### 6.5 Subscription purchase mode (subscription products only)
- Radio card selection:
  - Select "One Time Purchase" → show base price in MRP block; set `eternal_supply_months` to `0`
  - Select "Subscription" → expand plan dropdown; set price to first active tier
- Plan dropdown change → update MRP block price to selected tier `final_price`; update `eternal_supply_months` hidden input
- Data localised: `{ plans: [{ months, label, finalPrice, symbol }] }`

### 6.6 Add to bag form
- Standard WC `add-to-cart` form submission
- For subscription: appends `eternal_supply_months` as hidden input before submit
- For variable: standard WC variation ID mechanism (no change needed)

---

## 7. CSS (`_product-detail.css`)

Follows the existing CSS partial convention (no Tailwind — plain CSS with custom properties).

### Key layout classes

```
.pdp-breadcrumb           — breadcrumb bar
.pdp-layout               — flex row, justify-between, gap, full-width
.pdp-gallery              — left column: sticky, flex col
.pdp-gallery__thumbs      — vertical strip of thumbnail containers
.pdp-gallery__thumb       — 80×100px, #f5f5f5, overflow hidden
.pdp-gallery__hero        — 555×700px hero image container
.pdp-right                — right column: 555px, flex col
.pdp-buybox               — buy box top section
.pdp-buybox__eyebrow      — eyebrow label (Eyebrow type style)
.pdp-buybox__name         — H3 product name
.pdp-buybox__subtitle     — H5 french_text
.pdp-buybox__tagline      — Body/Base tagline
.pdp-buybox__stars        — star row (hidden via .is-hidden when no reviews)
.pdp-buybox__variant      — variant selector block (variable only)
.pdp-buybox__plans        — subscription radio cards (subscription only)
.pdp-buybox__plan-card    — individual radio card (border, radius)
.pdp-buybox__plan-card--expanded — active subscription card state
.pdp-buybox__price        — MRP block
.pdp-buybox__price-mrp    — "MRP" label
.pdp-buybox__price-regular — regular price with strike-through
.pdp-buybox__price-amount  — actual price (large)
.pdp-buybox__price-unit    — "/ 100ml" unit
.pdp-buybox__price-tax    — "(Incl. of all taxes)"
.pdp-buybox__actions      — qty stepper + CTA row
.pdp-qty                  — quantity stepper wrapper
.pdp-qty__display         — qty number display
.pdp-qty__btn             — +/- button
.pdp-cta                  — ADD TO BAG button (full width, dark bg)
.pdp-divider              — 0.5px hairline hr
.pdp-accordion            — accordion wrapper
.pdp-accordion__item      — individual panel
.pdp-accordion__header    — clickable header row
.pdp-accordion__icon      — +/- icon
.pdp-accordion__body      — collapsible content (hidden attribute toggled)
.pdp-feature              — editorial feature panel
.pdp-feature__text        — sticky left column
.pdp-feature__headline    — H3 Italic headline
.pdp-feature__body        — Body/Base paragraphs
.pdp-feature__image       — right image container (800px tall)
.pdp-ingredients          — key ingredients section
.pdp-ingredients--tinted  — modifier: #f5f5f5 bg + padding-top for title
.pdp-ingredients__title   — H2 centred section heading
.pdp-ingredients__grid    — flex row, centred, gap
.pdp-ingredients__card    — individual ingredient card
.pdp-ingredients__img     — 460×550px image container
.pdp-ingredients__name    — ingredient name (Body/Base, primary)
.pdp-ingredients__desc    — ingredient description (Body/Base, secondary)
```

---

## 8. User Stories

- **As a shopper**, I see the product breadcrumb, gallery with thumbnails, and all buy-box information above the fold.
- **As a shopper browsing a simple product**, I see the price (with strike-through if on sale), unit, and an ADD TO BAG button.
- **As a shopper browsing a variable product**, I see a "Choose Fragrance" dropdown; selecting a variant updates the price and main image.
- **As a shopper browsing a subscription product**, I can choose "One Time Purchase" or "Supply Plan"; selecting a plan tier updates the displayed price.
- **As a shopper**, I can expand the Description, Ingredients, and How to Apply accordion panels to read product details.
- **As a shopper**, scrolling below the buy box reveals editorial feature panels (full-bleed image + sticky text) followed by a key ingredients showcase.
- **As a shopper**, if a product has no reviews, the star row is not shown.
- **As a content editor**, if I leave `product_ingredients_title` empty, no section heading or tinted background appears on the key ingredients section.
- **As a content editor**, if I add only 1 or 2 key ingredients, the cards are centred correctly.
- **As a developer**, all template parts are isolated — each receives its data via `wp_rig()->get_product_meta()` and `wp_rig()->get_supply_plans()`, never reading raw meta keys.

---

## 9. Success Metrics

- [ ] `single-product.php` renders without PHP warnings or notices
- [ ] All three buy-box states render correctly for their product types (verified with Rosemary Oil, Argan Oil, Eternal Privé Man)
- [ ] Strike-through pricing shows for Rosemary Oil (`price 1599 < regular_price 1799`)
- [ ] Variable product: selecting a variant updates price display via AJAX (no page reload)
- [ ] Subscription product: selecting "Supply Plan" + a tier updates the price block; `eternal_supply_months` hidden input is populated correctly
- [ ] Accordion panels toggle open/close with correct ARIA attributes
- [ ] Description panel is open by default; Ingredients and How to Apply are closed
- [ ] Gallery thumbnail click swaps the hero image
- [ ] Quantity stepper increments/decrements; value never goes below 1
- [ ] Stars row hidden when `review_count === 0`
- [ ] Feature panels render once per `product_features` item, in order
- [ ] Key ingredients: 1 card centred, 2 cards centred pair, 3 cards row — all correct
- [ ] `product_ingredients_title` set → tinted bg + H2 title visible; empty → white bg, no title
- [ ] Ingredient card with `image_id: 0` renders placeholder, not a broken image
- [ ] `npm run ai:check` passes — PHPCS + PHPStan green
- [ ] Lighthouse accessibility score ≥ 90 on PDP (keyboard navigable, ARIA on accordion)
- [ ] No JS console errors on any of the three product types

---

## 10. Technical Plan (The Contract)

### Step 1 — Register the Component

**File:** `inc/Product_Detail/Component.php`

- Implements `Component_Interface` and `Templating_Component_Interface`
- Registers via `inc/Theme.php` (append to components array)
- `initialize()`:
  - `add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] )`
- `enqueue_assets()`:
  - Guard: `if ( ! is_product() ) return;`
  - No `wp_enqueue_style()` — CSS is delivered via `global.css` import (see §10 Step 4)
  - `wp_enqueue_script( 'eternal-pdp', ... 'product-detail.js', ['jquery'], ... , true )`
  - `wp_localize_script( 'eternal-pdp', 'EternalPDP', $this->get_js_data() )`
- `get_js_data()`: returns array with `ajaxUrl`, `nonce`, `productId`, `variations` (for variable), `plans` (for subscription)
- Template tag methods (accessible via `wp_rig()->`):
  - `get_product_meta( int $product_id ): array`
  - `get_supply_plans( int $product_id ): array`
  - `parse_markdown_light( string $text ): string`
  - `format_price( float $amount, string $currency = '' ): string`

### Step 2 — WooCommerce Template Overrides

**File:** `woocommerce/single-product.php`
- Minimal: `get_header()`, open `.pdp-page` wrapper, `woocommerce_content()`, `get_footer()`

**File:** `woocommerce/content-single-product.php`
- Remove all default WC hooks (title, price, add-to-cart, tabs, related)
- Set up product global: `global $product; $product = wc_get_product();`
- Fetch shared data: `$meta = wp_rig()->get_product_meta( get_the_ID() ); $plans = wp_rig()->get_supply_plans( get_the_ID() );`
- Include: breadcrumb part → open `.pdp-layout` → gallery part → right column → close layout
- Right column includes: buybox part, then loop `$meta['features']` → feature part, then ingredients part

### Step 3 — Template Parts

Implement in order:

1. **`part-pdp-breadcrumb.php`** — reads `$product->get_categories()`, outputs `HOME / {cat} / {name}`
2. **`part-pdp-gallery.php`** — main image + gallery images from `$product`
3. **`part-pdp-buybox.php`** — detects buy-box state, includes sub-template:
   - `part-pdp-buybox-simple.php`
   - `part-pdp-buybox-variable.php`
   - `part-pdp-buybox-subscription.php`
   - Then always includes `part-pdp-accordion.php`
4. **`part-pdp-feature.php`** — accepts `$args['feature']` array
5. **`part-pdp-ingredients.php`** — accepts `$args['meta']` array

### Step 4 — CSS Partial

**File:** `assets/css/src/_product-detail.css`
- Import in `assets/css/src/global.css` — this is the theme's actual CSS entry point (not `style.css`). All page-specific partials (`_homepage-hero.css`, `_category-split.css`, etc.) live here. Append:
  ```css
  @import "_product-detail.css";
  ```
- The `Product_Detail\Component` does **not** call `wp_enqueue_style()` — `global.css` delivers the styles sitewide, consistent with every other component in the theme.
- Write all classes from §7
- Use existing `var(--color-*)` custom properties; define any new PDP-specific ones at top of file

### Step 5 — TypeScript

**File:** `assets/js/src/product-detail.ts`
- Import from `./utils` if shared utilities exist
- DOMContentLoaded guard
- Implement all 6 modules from §6
- Compile via existing `npm run build:js`

### Step 6 — Register in Build

Verify `assets/css/src/style.css` imports `_product-detail.css`. Verify `package.json` / `config.json` build entries include `product-detail.ts` → `product-detail.js`.

### Step 7 — Test All Three Product Types

| Test | Product | Expected |
|---|---|---|
| Simple, sale pricing | Rosemary Oil (ID 151) | Strike-through ₹1,799, price ₹1,599, unit `/10ml` |
| Variable, variant select | Argan Oil Supreme iQ (ID 162) | Dropdown updates price; FRAGRANCE 001 = ₹4,799, FRAGRANCE 002 = ₹4,099 |
| Subscription, plan select | Eternal Privé Man (ID 165) | Radio cards; 3M plan = ₹10,000; 6M plan = ₹22,000 |
| Features | All products | Feature panels render in order, text properly decoded |
| Key ingredients (3-col + title) | Eternal Privé Man | "Powerful Longevity Actives" title, tinted bg, 3 cards |
| Key ingredients (3-col + title) | Argan Oil Supreme iQ | "Inside the Nourishing Science" title, 3 cards |
| Key ingredients (none) | (any product with no ingredients) | Section not rendered |
| No reviews | All current products | Star row hidden |

### Step 8 — Quality Check

```bash
npm run ai:check
```

Fix all PHPCS, PHPStan, and Prettier violations before marking complete.

---

## 11. Out of Scope

- Mobile / responsive layout → separate spec (desktop-first for now)
- Reviews / ratings rendering (WC native) → future spec
- Related products / upsells section → future spec
- Wishlist functionality → future spec
- Breadcrumb structured data (JSON-LD) → future spec
- Cart slide-out on "ADD TO BAG" → future spec
- Inventory / back-order messaging → future spec
- `product_benefits_bullets` rendering → deferred (no Figma node provided yet)
- `product_skin_type`, `product_type_label`, `product_display_tags` pill rendering → listing page spec
