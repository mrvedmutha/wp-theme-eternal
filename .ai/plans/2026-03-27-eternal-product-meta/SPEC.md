# SPEC: Eternal Product Meta Plugin (`eternal-product-meta`)

**Date:** 2026-03-27
**Status:** AWAITING APPROVAL — v2 (bundle fields removed → moved to `eternal-subscription` plugin)
**Figma Reference:** `aJ4VjKdFNahXA6Ly4jkRtJ` — nodes 694:1480, 694:1484, 694:5382, 694:5677, 694:7214, 694:7314, 694:7328, 694:1726, 694:1800

---

## 1. Mission Statement

Build a standalone WordPress plugin — `eternal-product-meta` — that registers all custom taxonomies and product display/content meta fields required to power the Eternal Labs storefront. This is a **pure content/display** plugin: it stores brand identity, copywriting, ingredient content, and editorial layout fields. It has no cart logic, no pricing logic, and no subscription logic — those concerns live in `eternal-subscription`.

This plugin replaces ACF (already installed but unused) with a zero-dependency, Gutenberg-native sidebar solution. All product card displays, PDP sections, listing page filters, ingredient showcases, and editorial blocks derive their content from the fields defined here.

---

## 2. Plugin Scope (what is and is NOT in this plugin)

| Concern | This plugin | Other plugin |
|---|---|---|
| Product name (FR subtitle), eyebrow, tagline | ✅ | — |
| Display tag pills (100ML, FRAGRANCE FREE) | ✅ | — |
| Card background colour | ✅ | — |
| Fragrance notes (Top/Middle/Base) | ✅ | — |
| INCI ingredients list | ✅ | — |
| How to Apply / Use | ✅ | — |
| Ingredient Cards showcase section | ✅ | — |
| Editorial section (headline + body + image) | ✅ | — |
| Custom filter taxonomies | ✅ | — |
| Subscription / bundle pricing | ❌ | `eternal-subscription` |
| Per-currency pricing | ❌ | `custom-multi-currency` |
| Cart price overrides | ❌ | `eternal-subscription` |

---

## 3. Design Compliance

Derived from Figma analysis of 9 nodes (section carousel, card component, 3 PDP variants, 2 below-PDP sections, listing page, listing card).

### Fields → Figma mapping

| Field | Figma Node(s) | Location in UI |
|---|---|---|
| `product_eyebrow` | 694:5382, 694:5677, 694:7214 | PDP subheading above product name |
| `product_name_fr` | 694:1480, 694:1484, 694:1726, 694:1800 | Second line of product name on all cards and PDP |
| `product_tagline` | 694:1480, 694:1484, 694:1726, 694:1800 | Grey italic line below product name |
| `product_display_tags` | 694:1484, 694:1726, 694:1800 | Outlined pills row (e.g. `[100ML]`, `[FRAGRANCE FREE]`) |
| `product_card_bg` | 694:1480, 694:1800 | Per-card background colour (e.g. `#f5f5f5`, `#e98282`) |
| `product_notes_*` | 694:5382 | "Key Notes" block inside Description accordion |
| `product_inci` | 694:5382, 694:5677, 694:7214 | "Ingredients and Safety" accordion body |
| `product_ingredients_disclaimer` | 694:5382, 694:5677 | Grey disclaimer text at bottom of ingredients accordion |
| `product_allergy_info` | 694:7214 | Allergy line in supplements ingredients accordion |
| `product_how_to_use` | 694:5382, 694:5677 | "How to Apply" accordion body |
| `product_storage_warnings` | 694:7214 | Storage/warnings block in supplements How to Use |
| `product_dosage_instructions` | 694:7214 | "Take 4 capsules per day…" line |
| `product_ingredient_cards` | 694:7328 | "Powerful Longevity Actives" 3-card showcase section |
| `product_editorial_*` | 694:7314 | 2-column text + image editorial section below PDP |
| Taxonomy: `product-type` | 694:1726 | Listing sidebar filter: "Product Types" |
| Taxonomy: `skin-type` | 694:1726 | Listing sidebar filter: "Skin Type" |
| Taxonomy: `product-benefit` | 694:1726 | Listing sidebar filter: "Benefits" |

### Display rules
- All fields are **optional** — a field left blank means its corresponding UI block is not rendered.
- `product_name_fr` renders only when non-empty (branding element, not a language switcher).
- `product_notes_*` — all three must be non-empty for the "Key Notes" block to render.
- `product_ingredient_cards` empty array = "Powerful Longevity Actives" section not rendered.
- `product_editorial_headline` empty = entire editorial section not rendered.

---

## 4. Architectural Fit

**Theme type:** `classic` (per `config/config.json`) — `enableBlocks: true`
**Plugin location:** `wp-content/plugins/eternal-product-meta/` (sibling to theme)
**WC dependency:** Plugin initialises only after `woocommerce_loaded`.

> **Why a plugin, not a theme component?** Custom taxonomies and product meta must survive a theme switch. Placing them in a plugin ensures data persistence regardless of theme changes.

### Plugin file structure

```
wp-content/plugins/eternal-product-meta/
├── eternal-product-meta.php          # Bootstrap: headers, constants, loader
├── inc/
│   ├── class-taxonomies.php          # register_taxonomy() for 3 filter taxonomies
│   └── class-meta-registration.php   # register_post_meta() for all 18 fields
└── src/
    ├── index.js                      # Webpack/wp-scripts entry — registers sidebar plugin
    ├── components/
    │   ├── PanelIdentity.jsx         # name_fr, eyebrow, tagline, display_tags, card_bg
    │   ├── PanelFragrance.jsx        # notes_top, notes_middle, notes_base
    │   ├── PanelIngredients.jsx      # inci, disclaimer, allergy_info
    │   ├── PanelHowToUse.jsx         # how_to_use, storage_warnings, dosage_instructions
    │   ├── PanelIngredientCards.jsx  # JSON repeater: name + description + image
    │   └── PanelEditorial.jsx        # editorial_headline, editorial_body, editorial_image_id
    └── build/                        # Compiled JS (wp-scripts build output)
```

### Hooks used

| Hook | File | Purpose |
|---|---|---|
| `init` (priority 10) | `class-taxonomies.php` | Registers 3 custom taxonomies on `product` post type |
| `init` (priority 10) | `class-meta-registration.php` | Registers 18 meta fields via `register_post_meta()` |
| `enqueue_block_editor_assets` | `eternal-product-meta.php` | Enqueues `build/index.js` in the block editor |

---

## 5. Custom Taxonomies (3 total)

All registered via `register_taxonomy()` in `class-taxonomies.php`, attached to the `product` post type, hierarchical (`'show_in_rest' => true` for Gutenberg).

| Taxonomy | Slug | UI Label | Example terms |
|---|---|---|---|
| Product Type | `product-type` | Product Types | Face Creme, Body Oil, Hair & Body Serum, Essential Oil, Dietary Supplements |
| Skin Type | `skin-type` | Skin Types | All Skin Types, Dry Skin, Sensitive Skin, Hair & Scalp Care |
| Product Benefit | `product-benefit` | Benefits | Hydration, Nourishment, Radiance, Firmness & Renewal, Revitalising Care |

---

## 6. Complete Meta Field Registry (18 fields)

All fields: `register_post_meta( 'product', $key, $args )` — `single: true`, `show_in_rest: true`.

### Panel 1 — Product Identity & Card Display

| Field Key | WP Type | Sanitize | Description |
|---|---|---|---|
| `product_name_fr` | `string` | `sanitize_text_field` | French subtitle. e.g. "L'HUILE D'ARGAN SUPRÊME iQ". Optional. |
| `product_eyebrow` | `string` | `sanitize_text_field` | PDP eyebrow label above product name. e.g. "ARGAN OIL HAIR & BODY SERUM" |
| `product_tagline` | `string` | `sanitize_text_field` | One-line tagline on cards. e.g. "Intense nourishment. Natural radiance." |
| `product_display_tags` | `string` | `sanitize_text_field` | Comma-separated pill labels. e.g. `100ML, FRAGRANCE FREE` |
| `product_card_bg` | `string` | `sanitize_hex_color` | Hex colour for card bg. Default: `#f5f5f5` |

### Panel 2 — Fragrance Notes

All three must be non-empty for the "Key Notes" block to render.

| Field Key | WP Type | Sanitize | Description |
|---|---|---|---|
| `product_notes_top` | `string` | `sanitize_text_field` | e.g. "Aldehydes, Bergamot" |
| `product_notes_middle` | `string` | `sanitize_text_field` | e.g. "Orange Blossom, Jasmine" |
| `product_notes_base` | `string` | `sanitize_text_field` | e.g. "White Musk, Orris, Honey, Vanilla, Cedar" |

### Panel 3 — Ingredients & Safety

| Field Key | WP Type | Sanitize | Description |
|---|---|---|---|
| `product_inci` | `string` | `wp_kses_post` | Full INCI list. HTML allowed for brand name formatting. |
| `product_ingredients_disclaimer` | `string` | `sanitize_textarea_field` | Legal disclaimer text rendered below INCI. |
| `product_allergy_info` | `string` | `sanitize_text_field` | e.g. "Gluten free. Lactose free." |

### Panel 4 — How To Use

| Field Key | WP Type | Sanitize | Description |
|---|---|---|---|
| `product_how_to_use` | `string` | `wp_kses_post` | How to apply / use instructions. HTML allowed. |
| `product_storage_warnings` | `string` | `wp_kses_post` | Storage and warnings. HTML allowed. |
| `product_dosage_instructions` | `string` | `sanitize_text_field` | e.g. "Take 4 capsules per day (maximum), in the morning with a glass of water." |

### Panel 5 — Ingredient Cards (JSON Repeater)

| Field Key | WP Type | Sanitize | Description |
|---|---|---|---|
| `product_ingredient_cards` | `string` | `wp_slash( wp_json_encode() )` | JSON array of `{ name, description, image_id }` objects. |

**JSON schema per item:**
```json
{
  "name": "COENZYME Q10",
  "description": "A vital mitochondrial nutrient...",
  "image_id": 482
}
```

Empty array `[]` or empty string = "Powerful Longevity Actives" section not rendered.

### Panel 6 — Editorial Section

| Field Key | WP Type | Sanitize | Description |
|---|---|---|---|
| `product_editorial_headline` | `string` | `sanitize_text_field` | e.g. "Elevated Daily Performance". Empty = section hidden. |
| `product_editorial_body` | `string` | `wp_kses_post` | 2–3 paragraphs. HTML allowed. |
| `product_editorial_image_id` | `integer` | `absint` | WP attachment ID for editorial image. |

---

## 7. Gutenberg Sidebar Implementation

### Entry point (`src/index.js`)

The plugin registers a `PluginDocumentSettingPanel` per panel group via `@wordpress/plugins`. All panels are guarded — only visible when editing `product` post type.

All panels read/write meta via `useEntityProp( 'postType', 'product', fieldKey )` from `@wordpress/core-data`.

**`PanelIngredientCards.jsx` — repeater pattern:**
- Parse `product_ingredient_cards` JSON string on mount
- Render list: `TextControl` (name) + `TextareaControl` (description) + `MediaUpload` (image) per card
- "Add Card" appends empty item; "Remove" on each card deletes it
- Every change serialises back to JSON string and saves to meta
- Drag-to-reorder deferred to v2

**`PanelEditorial.jsx` — image field:**
- Uses `MediaUpload` from `@wordpress/block-editor`
- Stores attachment ID in `product_editorial_image_id`
- Displays thumbnail preview once selected

---

## 8. User Stories

- **As a content editor**, I can fill in the French subtitle and see it appear below the English product name on all cards and the PDP.
- **As a content editor**, I can enter comma-separated pill labels and they render as outlined pills.
- **As a content editor**, I can add/remove/edit ingredient cards (name, description, image) via the sidebar repeater.
- **As a content editor**, I can fill in the editorial section headline, body, and image per product.
- **As a developer**, all 18 product meta fields are readable via the WP REST API (`/wp-json/wp/v2/product/<id>`).
- **As a developer**, all 3 filter taxonomies are queryable via WP taxonomy queries for the listing page filter sidebar.

---

## 9. Success Metrics

- [ ] Plugin activates without PHP errors or warnings
- [ ] All 3 taxonomies appear in the product editor sidebar
- [ ] All 6 sidebar panels appear when editing a `product` — not on pages/posts
- [ ] Saving a product persists all 18 fields to `wp_postmeta`
- [ ] `GET /wp-json/wp/v2/product/<id>` includes all 18 fields under `meta`
- [ ] `product_ingredient_cards` saves valid JSON and round-trips correctly
- [ ] `product_editorial_headline` empty → editorial section not rendered in theme template
- [ ] No JS console errors on product editor load
- [ ] `npm run ai:check` passes — PHPCS, PHPStan, Prettier all green
- [ ] All PHP template output escaped (`esc_html`, `esc_url`, `wp_kses_post`)

---

## 10. Technical Plan (The Contract)

### Step 1 — Plugin Bootstrap
Create `eternal-product-meta.php`:
- Plugin header, `ETERNAL_META_VERSION` + `ETERNAL_META_PATH` constants
- `add_action( 'woocommerce_loaded', 'eternal_meta_init' )` — waits for WC
- `eternal_meta_init()` requires `inc/class-taxonomies.php` and `inc/class-meta-registration.php`
- `add_action( 'enqueue_block_editor_assets', 'eternal_meta_enqueue_editor' )` — enqueues `src/build/index.js` with deps: `wp-plugins`, `wp-edit-post`, `wp-components`, `wp-data`, `wp-element`, `wp-core-data`, `wp-block-editor`

### Step 2 — Taxonomies
Create `inc/class-taxonomies.php` — registers `product-type`, `skin-type`, `product-benefit` on `init` hook.

### Step 3 — Meta Registration
Create `inc/class-meta-registration.php` — 18 `register_post_meta()` calls on `init` hook, correct type + sanitize_callback per field.

### Step 4 — Build Config
Plugin-level `package.json` with `@wordpress/scripts ^30.0.0`. Run `npm install && npm run build`.

### Step 5 — Sidebar Panels
Implement all 6 panel components in order:
1. `PanelIdentity.jsx` — 5 TextControl + ColorPalette for card_bg
2. `PanelFragrance.jsx` — 3 TextControl
3. `PanelIngredients.jsx` — WYSIWYG via `RichText` or `TextareaControl` for INCI + 2 TextareaControl
4. `PanelHowToUse.jsx` — 2 TextareaControl + 1 TextControl
5. `PanelIngredientCards.jsx` — JSON repeater with MediaUpload
6. `PanelEditorial.jsx` — TextControl + TextareaControl + MediaUpload

### Step 6 — Build, Activate, Verify
`npm run build` → activate plugin → confirm panels render → save test product → verify REST API response.

### Step 7 — Quality Check
`npm run ai:check` — fix all violations.

---

## 11. Out of Scope

- Subscription / bundle pricing → see `eternal-subscription` SPEC
- Per-currency pricing → `custom-multi-currency` (already active)
- Frontend template rendering (reads these fields) → separate template spec per section
- Listing page filter UI (AJAX, query vars) → separate spec
- Taxonomy term seeding → WP-CLI spec
