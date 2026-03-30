# SPEC: Eternal Subscription Plugin (`eternal-subscription`)

**Date:** 2026-03-27
**Status:** AWAITING APPROVAL — v1
**Figma Reference:** `aJ4VjKdFNahXA6Ly4jkRtJ` — node 694:7214 (Eternal Privé Man PDP — purchase type selector)
**Depends on:** `woocommerce` (required), `custom-multi-currency` (optional — soft dependency)

---

## 1. Mission Statement

Build a standalone WordPress plugin — `eternal-subscription` — that adds a "Supply Plan" purchase option to any WooCommerce product. An admin can enable subscription-style supply plans per product, configure which multiplier tiers to offer (3 / 6 / 9 / 12 months), and set per-tier pricing with either a percentage discount or a fixed final price. The plugin works natively in the WooCommerce product editor (classic pricing tab, same pattern as `custom-multi-currency`), handles cart price overrides at checkout, and reads the active currency from `CMC_Currency_Manager` when the currency plugin is active.

This is **not** a recurring billing plugin. Each supply plan is a one-time order for N units (or N months' worth) shipped together at a single payment. No subscriptions API, no Stripe recurring charges.

---

## 2. What This Plugin Is and Is NOT

| Capability | In scope | Notes |
|---|---|---|
| Per-product enable/disable toggle | ✅ | Shown in WC product editor "General" tab area |
| Multiplier tiers: 3 / 6 / 9 / 12 months | ✅ | Admin selects which tiers to offer per product |
| Custom label per tier | ✅ | e.g. "3 Month Plan" — editable |
| MRP display (base price × multiplier) | ✅ | Auto-calculated or manually overridden |
| Discount type per tier: Percentage | ✅ | e.g. 15% off total MRP |
| Discount type per tier: Fixed Total | ✅ | e.g. ₹12,999 flat for the whole tier |
| Final computed price shown in admin | ✅ | Read-only computed field next to inputs |
| Per-currency final price override | ✅ | Follows CMC pattern — one price field per enabled currency per tier |
| Cart price override at correct total | ✅ | WC filter overrides line price at checkout |
| Order meta — saves plan details to order | ✅ | Plan label + tier saved as order item meta |
| Recurring billing / Stripe subscriptions | ❌ | Out of scope entirely |
| Free trial periods | ❌ | Out of scope |

---

## 3. Figma Design Reference (node 694:7214)

The purchase-type selector on the Eternal Privé Man PDP shows:

```
○  One Time Purchase                          ₹4,000
●  Subscription
   [ Buy 3-Month Plan  ∨ ]
   (Includes: 3 X Boxes of ETERNAL MAN + 3 X Boxes of ETERNAL YOUTH anti-aging)
                                             ₹14,000
```

**Key design decisions derived from Figma:**
- "One Time Purchase" = standard WC price (no plugin involvement)
- "Subscription" = supply plan managed by this plugin
- Plan selector is a dropdown inside the radio option (not a separate block)
- Price shown is the **total** for the selected plan, not per-unit
- The "(Includes: ...)" note is per-plan copy entered by the admin

---

## 4. Architectural Fit

**Plugin location:** `wp-content/plugins/eternal-subscription/`
**Admin UI pattern:** Classic WC product data metabox (same as `custom-multi-currency`) — **not** Gutenberg sidebar
**Why classic metabox:** Pricing fields in WC belong in the "General" product data tab. Using classic hooks (`woocommerce_product_options_pricing`, `woocommerce_process_product_meta`) keeps subscription pricing visually grouped with the CMC multi-currency pricing fields, which the admin is already familiar with.
**CMC integration:** Soft dependency. Checked via `class_exists( 'CMC_Currency_Manager' )`. If CMC is inactive, plugin shows only base-currency fields.

### Plugin file structure

```
wp-content/plugins/eternal-subscription/
├── eternal-subscription.php          # Bootstrap: headers, constants, loader
├── inc/
│   ├── class-esp-product-fields.php  # WC admin metabox: enable toggle + tier config
│   ├── class-esp-cart.php            # Cart price override + order meta
│   └── class-esp-frontend.php        # REST field registration + public getters
└── assets/
    ├── css/
    │   └── esp-admin.css             # Admin accordion styles (mirrors CMC pattern)
    └── js/
        └── esp-admin.js              # Toggle show/hide of tier sections in admin
```

> No build step required — no React, no `@wordpress/scripts`. Admin UI uses native WC PHP field helpers + vanilla JS, consistent with CMC.

---

## 5. Meta Key Structure

All meta stored as individual `wp_postmeta` rows. The four supported multipliers are `3`, `6`, `9`, `12`. Meta keys use `_esp_` prefix (Eternal Supply Plan).

### Product-level keys

| Meta Key | Type | Description |
|---|---|---|
| `_esp_enabled` | `0` or `1` | Master toggle — is any supply plan available on this product? |

### Per-tier keys (generated for each multiplier in `[3, 6, 9, 12]`)

Replace `{N}` with the multiplier value (3, 6, 9, or 12).

| Meta Key | Type | Description |
|---|---|---|
| `_esp_{N}m_active` | `0` or `1` | Is this specific tier offered on this product? |
| `_esp_{N}m_label` | `string` | Admin-editable tier label. Default: `"{N} Month Plan"` |
| `_esp_{N}m_contents_note` | `string` | Small-print note shown below plan selector. e.g. "Includes: 3 X Boxes..." |
| `_esp_{N}m_discount_type` | `string` | `percentage` or `fixed_total` |
| `_esp_{N}m_discount_value` | `decimal` | Percentage (e.g. `15`) or fixed total in base currency (e.g. `12999`) |
| `_esp_{N}m_mrp_override` | `decimal` | Optional MRP override. Empty = auto-calculated as `base_price × N`. |

### Per-tier per-currency keys

Generated dynamically for each CMC-enabled currency. Replace `{N}` with multiplier, `{CUR}` with lowercase currency code (e.g. `usd`, `eur`, `gbp`).

| Meta Key | Type | Description |
|---|---|---|
| `_esp_{N}m_final_{CUR}` | `decimal` | Final price for this tier in this currency. Leave empty to auto-calculate. |

**Auto-calculation logic (when `_esp_{N}m_final_{CUR}` is empty):**
1. Get CMC regular price for this currency: `CMC_Product_Fields::get_product_price( $id, $CUR, 'regular' )`
2. If CMC price exists: `base = cmc_regular_price`; else: `base = WC regular price in base currency`
3. If `discount_type === 'percentage'`: `final = (base × N) × (1 - discount_value/100)`
4. If `discount_type === 'fixed_total'`: convert base-currency fixed total to this currency using a simple ratio (`fixed_total_inr / base_currency_price × cmc_price`)

> Note: auto-calculation for non-base currencies is a best-effort estimate. Admin should always set per-currency final prices explicitly for accurate international pricing.

---

## 6. WC Admin UI (`class-esp-product-fields.php`)

Hooked to: `woocommerce_product_options_pricing` + `woocommerce_process_product_meta`
Same pattern as `CMC_Product_Fields`.

### Layout in product editor "General" tab

```
── WooCommerce native: Regular Price, Sale Price ──────────────────────
── CMC Multi-Currency Pricing (if CMC active) ─────────────────────────

┌─ Supply Plan Pricing ────────────────────────────────────────────────┐
│                                                                       │
│  [✓] Enable Supply Plans for this product                            │
│                                                                       │
│  ▼ 3 Month Plan  ─────────────────────────────────────────── [×]   │
│    Label:          [ 3 Month Plan              ]                      │
│    Contents Note:  [ Includes: 3 X Boxes...    ]                      │
│    MRP Override:   [ _________ ]  (leave empty = ₹4,999 × 3 = auto) │
│    Discount Type:  (●) Percentage  ( ) Fixed Total                    │
│    Discount Value: [ 15      ] %                                      │
│    Final Price:    ₹12,747  ← computed, read-only                    │
│                                                                       │
│    ── Per-Currency Final Prices (optional override) ─────────────── │
│    USD Final:  [ ________ ]   EUR Final:  [ ________ ]               │
│    GBP Final:  [ ________ ]   ...                                     │
│                                                                       │
│  ▼ 6 Month Plan  ─────────────────────────────────────────── [×]   │
│    ... (same structure) ...                                           │
│                                                                       │
│  [+ Add Tier]   (shows unchecked tiers from 3/6/9/12 not yet active) │
└───────────────────────────────────────────────────────────────────────┘
```

**Admin JS behaviour (`esp-admin.js`):**
- Entire "Supply Plan Pricing" section hidden by default — shown only when enable toggle is checked
- Tier sections are collapsible accordions (same CSS pattern as CMC)
- "Discount Type" radio change → updates the "%" vs currency symbol label on the discount value field
- Discount value input + MRP display → JS computes and updates "Final Price" read-only field in real time
- "Add Tier" button → reveals next inactive tier section; "×" button → hides/unchecks a tier
- Per-currency fields section shown only if CMC is active (PHP outputs a data attribute; JS reads it)

### Save handler

```php
public function save_fields( $post_id ) {
    // Save master toggle
    update_post_meta( $post_id, '_esp_enabled', isset( $_POST['_esp_enabled'] ) ? '1' : '0' );

    foreach ( [3, 6, 9, 12] as $n ) {
        $prefix = "_esp_{$n}m";

        update_post_meta( $post_id, "{$prefix}_active",         isset( $_POST["{$prefix}_active"] ) ? '1' : '0' );
        update_post_meta( $post_id, "{$prefix}_label",          sanitize_text_field( $_POST["{$prefix}_label"] ?? '' ) );
        update_post_meta( $post_id, "{$prefix}_contents_note",  sanitize_textarea_field( $_POST["{$prefix}_contents_note"] ?? '' ) );
        update_post_meta( $post_id, "{$prefix}_discount_type",  sanitize_key( $_POST["{$prefix}_discount_type"] ?? 'percentage' ) );
        update_post_meta( $post_id, "{$prefix}_discount_value", wc_format_decimal( $_POST["{$prefix}_discount_value"] ?? 0 ) );
        update_post_meta( $post_id, "{$prefix}_mrp_override",   wc_format_decimal( $_POST["{$prefix}_mrp_override"] ?? '' ) );

        // Per-currency final prices
        if ( class_exists( 'CMC_Currency_Manager' ) ) {
            foreach ( CMC_Currency_Manager::get_additional_currencies() as $currency ) {
                $cur = strtolower( $currency );
                $key = "{$prefix}_final_{$cur}";
                update_post_meta( $post_id, $key, wc_format_decimal( $_POST[$key] ?? '' ) );
            }
        }
    }
}
```

---

## 7. Public API (`class-esp-frontend.php`)

These methods are used by the theme's PDP template to render the plan selector UI.

```php
class ESP_Frontend {

    /**
     * Is supply plan enabled for this product?
     */
    public static function is_enabled( int $product_id ): bool {
        return '1' === get_post_meta( $product_id, '_esp_enabled', true );
    }

    /**
     * Get all active tiers for a product, with computed prices for the active currency.
     *
     * @return array[] Each element: {
     *   'months'        => int,
     *   'label'         => string,
     *   'contents_note' => string,
     *   'mrp'           => float,     // display MRP (for strikethrough)
     *   'final_price'   => float,     // actual price at checkout
     *   'currency'      => string,    // active currency code
     *   'symbol'        => string,    // currency symbol
     * }
     */
    public static function get_active_tiers( int $product_id ): array { ... }

    /**
     * Get final price for a specific tier + currency.
     * Returns the stored override if set, otherwise auto-calculates.
     */
    public static function get_tier_price( int $product_id, int $months, string $currency ): float { ... }
}
```

These methods are the **only surface** the theme templates touch. Templates never read raw meta keys.

---

## 8. Cart Integration (`class-esp-cart.php`)

### Adding to cart

The theme's "ADD TO BAG" button submits a hidden input `eternal_supply_months` (value: `3`, `6`, `9`, or `12`, or `0` for one-time purchase).

```php
// Hook 1: tag cart item with supply plan data
add_filter( 'woocommerce_add_cart_item_data', function( $data, $product_id ) {
    $months = (int) ( $_POST['eternal_supply_months'] ?? 0 );

    if ( $months > 0 && ESP_Frontend::is_enabled( $product_id ) ) {
        $currency    = class_exists( 'CMC_Currency_Manager' )
                       ? CMC_Currency_Manager::get_active_currency()
                       : get_option( 'woocommerce_currency' );
        $final_price = ESP_Frontend::get_tier_price( $product_id, $months, $currency );
        $tier        = self::get_tier_meta( $product_id, $months );

        $data['_esp_months']      = $months;
        $data['_esp_label']       = $tier['label'];
        $data['_esp_final_price'] = $final_price;
        $data['_esp_currency']    = $currency;
    }

    return $data;
}, 10, 2 );

// Hook 2: override line item price before totals are calculated
add_action( 'woocommerce_before_calculate_totals', function( $cart ) {
    foreach ( $cart->get_cart() as $item ) {
        if ( ! empty( $item['_esp_final_price'] ) ) {
            $item['data']->set_price( (float) $item['_esp_final_price'] );
        }
    }
} );

// Hook 3: display plan details in cart and checkout
add_filter( 'woocommerce_get_item_data', function( $data, $item ) {
    if ( ! empty( $item['_esp_label'] ) ) {
        $data[] = [
            'name'  => __( 'Supply Plan', 'eternal-subscription' ),
            'value' => esc_html( $item['_esp_label'] ),
        ];
    }
    return $data;
}, 10, 2 );

// Hook 4: persist to order item meta after checkout
add_action( 'woocommerce_checkout_create_order_line_item', function( $item, $cart_key, $values ) {
    if ( ! empty( $values['_esp_months'] ) ) {
        $item->add_meta_data( __( 'Supply Plan', 'eternal-subscription' ), $values['_esp_label'], true );
        $item->add_meta_data( '_esp_months', $values['_esp_months'], true );
    }
}, 10, 3 );
```

---

## 9. Currency Plugin Integration Points

The subscription plugin integrates with `custom-multi-currency` at three points:

| Point | How |
|---|---|
| **Admin fields** | `CMC_Currency_Manager::get_additional_currencies()` — generates per-currency final price input fields per tier |
| **Price calculation** | `CMC_Product_Fields::get_product_price( $id, $currency, 'regular' )` — gets the CMC base price for a currency when auto-calculating tier price |
| **Cart** | `CMC_Currency_Manager::get_active_currency()` — identifies which currency to use when setting the cart item price |

All three calls are wrapped in `class_exists( 'CMC_Currency_Manager' )` guards so the plugin works standalone if CMC is deactivated.

**CMC's own price override filters** (`woocommerce_product_get_price` etc.) run on the **product object**. The subscription plugin overrides at a higher level — `woocommerce_before_calculate_totals` on the **cart item** — so there is no filter collision. CMC handles single-unit display prices; the subscription plugin handles the total when a supply plan is selected.

---

## 10. WC Admin Product List Column

The plugin adds a "Supply Plan" column to the WC Products list table so admins can see at a glance which products have plans enabled.

Hook: `manage_product_posts_columns` + `manage_product_posts_custom_column`

```
Title                  | Price   | Supply Plan  | Categories | Tags | ...
-----------------------|---------|--------------|------------|------|----
Eternal Privé Man      | ₹4,000  | 3M / 6M / 12M | Supplements | ...
Argan Oil Supreme iQ   | ₹4,999  | —            | Hair & Body | ...
```

---

## 11. User Stories

- **As a product manager**, I can open any WooCommerce product and see a "Supply Plan Pricing" section in the General tab that shows whether plans are enabled.
- **As a product manager**, I can enable supply plans for a product and configure 3, 6, 9, or 12-month tiers independently.
- **As a product manager**, I can set a label and contents note per tier (e.g. "3 Month Plan", "Includes 3 × boxes...").
- **As a product manager**, I can set a percentage discount for a tier and see the final price computed automatically.
- **As a product manager**, I can alternatively enter a fixed final price for a tier (overrides percentage calculation).
- **As a product manager**, I can override the final price per currency (USD, EUR, GBP, etc.) so international customers see accurate pricing.
- **As a product manager**, I can see which products have supply plans enabled from the WC Products list view.
- **As a shopper**, on the PDP I can see a "One Time Purchase" option and a "Supply Plan" option; selecting "Supply Plan" reveals a dropdown of available tiers (3M / 6M / etc.) with the plan price.
- **As a shopper**, when I add a supply plan to cart, the cart shows the correct total and the plan name ("3 Month Plan") as item detail.
- **As a shopper**, when CMC switches my currency to USD, all supply plan prices display in USD using the per-currency prices the admin set.
- **As a site administrator**, supply plan details (plan name, months) are saved to each WooCommerce order so fulfilment can see what was ordered.

---

## 12. Success Metrics

- [ ] Plugin activates without PHP errors; deactivates cleanly
- [ ] "Supply Plan Pricing" section appears in the WC product editor General tab
- [ ] Section is hidden by default and appears only when enable toggle is checked
- [ ] Saving a product with 3M + 6M tiers active persists all meta keys correctly (`_esp_enabled`, `_esp_3m_active`, etc.)
- [ ] Computed "Final Price" in admin updates in real time as discount value is typed
- [ ] Per-currency fields appear only when CMC is active
- [ ] Cart shows plan label ("3 Month Plan") as item detail
- [ ] Cart total equals the tier final price (not base price × multiplier)
- [ ] Order item meta contains supply plan label and months after checkout
- [ ] Products list column shows active tier labels or "—" correctly
- [ ] If CMC deactivated: plugin still works using base WC currency only
- [ ] If `eternal-subscription` deactivated: CMC and WC continue to work normally (no breakage)
- [ ] PHPCS passes on all plugin PHP files
- [ ] `wp_postmeta` contains no orphaned keys after a product save cycle

---

## 13. Technical Plan (The Contract)

### Step 1 — Plugin Bootstrap
Create `eternal-subscription.php`:
- Plugin header, `ESP_VERSION` and `ESP_PATH` constants
- `add_action( 'woocommerce_loaded', 'esp_init' )` — wait for WC
- `esp_init()` requires all 3 class files and instantiates them
- No build step needed

### Step 2 — Product Fields (admin UI)
Create `inc/class-esp-product-fields.php`:
- Hook `woocommerce_product_options_pricing` → `add_fields()`
- Render enable toggle via `woocommerce_wp_checkbox()`
- Loop `[3, 6, 9, 12]` — render each tier section using `woocommerce_wp_text_input()` for label, note, mrp override, discount value; custom radio group for discount type; read-only span for computed final price
- If CMC active: inner loop over `CMC_Currency_Manager::get_additional_currencies()` → render per-currency final price fields
- Hook `woocommerce_process_product_meta` → `save_fields()` (see §6)
- Hook `admin_enqueue_scripts` → enqueue `assets/css/esp-admin.css` + `assets/js/esp-admin.js` on product pages

### Step 3 — Admin CSS + JS
Create `assets/css/esp-admin.css` — mirrors CMC accordion styles, add computed price read-only display style.
Create `assets/js/esp-admin.js` — vanilla JS:
- Toggle section visibility on enable checkbox change
- Accordion open/close per tier
- Real-time final price computation on discount value / type change

### Step 4 — Frontend Public API
Create `inc/class-esp-frontend.php`:
- `ESP_Frontend::is_enabled( $product_id )` — reads `_esp_enabled`
- `ESP_Frontend::get_active_tiers( $product_id )` — loops active tiers, calls `get_tier_price()`, builds array
- `ESP_Frontend::get_tier_price( $product_id, $months, $currency )` — reads stored override or auto-calculates

### Step 5 — Cart Integration
Create `inc/class-esp-cart.php`:
- 4 hooks as specified in §8

### Step 6 — Products List Column
Add to `class-esp-product-fields.php`:
- `manage_product_posts_columns` → add "Supply Plan" column header
- `manage_product_posts_custom_column` → output active tier labels or "—"

### Step 7 — Activate and Test
- Activate plugin
- Create a test product with 3M + 6M tiers, percentage discount
- Verify admin computed price updates in real time
- Add to cart with supply plan selected, verify cart total
- Switch CMC currency, verify plan price updates
- Place test order, check order item meta

### Step 8 — Quality Check
PHPCS on all PHP files. No `var_dump`, no dead code.

---

## 14. Out of Scope

- Frontend template for the plan selector UI (purchase-type radio + dropdown) → PDP template spec
- Recurring billing or automatic renewal → not planned
- Subscription management page (pause, cancel, reorder) → not planned
- WooCommerce REST API exposure of subscription tiers → future spec
- Email customisation for supply plan orders → future spec
