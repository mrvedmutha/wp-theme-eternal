# SPEC: Site Footer — Newsletter + Footer Template Part

**Date:** 2026-03-24
**Branch:** `feature/footer`
**Status:** AWAITING APPROVAL — decisions updated 2026-03-24

---

## Overview

Replace the placeholder `template-parts/footer/info.php` with a full-featured footer template part that includes:
1. A **Newsletter banner** (atmospheric background image, email form)
2. A **Main footer** (black, logo + nav columns + bottom bar)

These ship as **one unified template part**: `template-parts/footer/site-footer.php`.

---

## Sections

### 1. Newsletter Banner

| Property | Value |
|---|---|
| Background | Full-width image, configurable via Customizer (`newsletter_bg_image`) |
| Heading | "JOIN OUR NEWSLETTER" — Maison Neue, 17px |
| Subtext | Editable via Customizer (`newsletter_subtext`) |
| Email field | `<form>` posting to custom REST endpoint (`/wp-json/eternal/v1/newsletter/subscribe`) via fetch API |
| Border | `0.5px solid white` on the field wrapper |
| Width | Max ~667px, centered |

### 2. Main Footer (black)

#### 2a. Logo + Tagline Column

| Property | Value |
|---|---|
| Logo | Pulls from **Customizer Site Identity > Logo** (existing `custom_logo`) — no new setting needed |
| Tagline | Pulls from `blogdescription` option — no new setting needed |

#### 2b. Navigation Columns (4 new menu locations)

| Slug | Label | Figma Column |
|---|---|---|
| `footer_eternal` | Footer: Eternal | ETERNAL |
| `footer_shop` | Footer: Shop | SHOP |
| `footer_customer_service` | Footer: Customer Service | CUSTOMER SERVICE |
| `footer_follow_us` | Footer: Follow Us | FOLLOW US |

- Registered in `inc/Nav_Menus/Component.php` alongside the existing `primary` menu
- Rendered with `wp_nav_menu()` using a custom `Walker_Nav_Menu` or default (no sub-menus needed)
- Column headings pulled from the **menu name** set in WP Admin

#### 2c. Bottom Bar — Left

| Element | Source |
|---|---|
| Copyright | `© [year] Eternallabs` — year auto-generated with `date('Y')` |
| Privacy Policy | `get_privacy_policy_url()` (existing WP core) |
| Cookie Policy | Menu item in `footer_legal` menu location — admin sets links through WP Admin > Menus |
| Currency/Locale switcher | Reuse `CMC_Currency_Switcher::get_instance()->render_switcher('buttons')` + globe icon, identical to header |

#### 2d. Bottom Bar — Right

| Element | Source |
|---|---|
| Payment icons | Up to **4 media upload slots** in Customizer: `payment_icon_1` through `payment_icon_4` — renders only slots with an image set |
| Aspect ratio | All 4 slots constrained to **~2:1 (38×19px)** display size — same ratio enforced via CSS |
| Accessibility | Customizer text field `payment_icon_{n}_alt` for alt text per icon |
| Legal links | "Accessibility", "Terms & Conditions" — pulled from `footer_legal` menu location |
| Design credit | Hardcoded: `Design by <a href="https://wings.design">WINGS</a>` with header-style underline hover animation |

---

## New Customizer Settings (`themeCustomizeSettings.json`)

```json
// Section: "footer" added alongside existing "global"
{
  "id": "footer",
  "title": "Footer",
  "settings": [
    { "id": "newsletter_bg_image",   "label": "Newsletter Background Image", "type": "media" },
    { "id": "newsletter_subtext",    "label": "Newsletter Subtext",           "type": "text",  "default": "Subscribe and embark on a timeless beauty journey and enjoy 15% off your first purchase* above CHF 350." },
    { "id": "payment_icon_1",        "label": "Payment Icon 1",               "type": "media" },
    { "id": "payment_icon_1_alt",    "label": "Payment Icon 1 Alt Text",      "type": "text" },
    { "id": "payment_icon_2",        "label": "Payment Icon 2",               "type": "media" },
    { "id": "payment_icon_2_alt",    "label": "Payment Icon 2 Alt Text",      "type": "text" },
    { "id": "payment_icon_3",        "label": "Payment Icon 3",               "type": "media" },
    { "id": "payment_icon_3_alt",    "label": "Payment Icon 3 Alt Text",      "type": "text" },
    { "id": "payment_icon_4",        "label": "Payment Icon 4",               "type": "media" },
    { "id": "payment_icon_4_alt",    "label": "Payment Icon 4 Alt Text",      "type": "text" }
  ]
}
```

---

## Files to Create / Modify

| Action | File |
|---|---|
| **Replace** | `template-parts/footer/info.php` → `template-parts/footer/site-footer.php` |
| **Modify** | `footer.php` — update `get_template_part()` call |
| **Modify** | `inc/Nav_Menus/Component.php` — add 4 new menu locations |
| **Modify** | `inc/EZ_Customizer/themeCustomizeSettings.json` — add footer section |
| **Create** | `assets/css/src/footer.css` — footer-specific styles |
| **Modify** | `assets/css/src/global.css` or manifest — import footer.css |
| **Create** | `inc/Newsletter/Component.php` — REST endpoint, CPT registration, email notification |
| **Modify** | `inc/Newsletter/` — add 5 new menu locations (`footer_eternal`, `footer_shop`, `footer_customer_service`, `footer_follow_us`, `footer_legal`) |
| **Modify** | `functions.php` — register Newsletter component |
| **Create** | `assets/js/newsletter.js` — async fetch submit + success/error UI state |

**New:** `inc/Newsletter/Component.php` — custom newsletter component (see below).

---

## Typography

- **Font family:** `Maison Neue` (single variant)
- **Sizes:** 17px (newsletter heading), 11px (eyebrow/button labels, 2px letter-spacing), 15px (input), 13px (nav links/footer bar)
- **Colors:** `#ffffff` (headings, column titles), `#b6b6b6` (body links), `#000000` (footer bg)

---

## Newsletter Component (`inc/Newsletter/Component.php`)

- Registers custom post type: `newsletter_subscriber` (private, not public)
  - Fields: `subscriber_email`, `subscribe_date`, `subscriber_ip` (hashed)
- REST endpoint: `POST /wp-json/eternal/v1/newsletter/subscribe`
  - Validates email, checks for duplicate, saves CPT post, sends admin notification email
  - Returns JSON `{ success: true }` or `{ success: false, message: "..." }`
- WP Admin list table shows all subscribers with email + date columns
- CSV export action in admin (bulk action: "Export to CSV")

**JS behavior (`assets/js/newsletter.js`):**
- Intercepts form submit, posts JSON to REST endpoint
- Disables submit button during request
- On success: inserts text **"Newsletter subscribed successfully!"** below the input box
- On error: inserts text **"ERROR: Something went wrong. Please try again."** below the input box
- Both messages auto-remove after **5 seconds**
- No icons, no styled boxes — plain text only, same font as the newsletter section: Maison Neue, 11px, 2px letter-spacing (matches the eyebrow/SIGN UP style in that area)
- White text for success, same white for error (no red — stays on-brand)

---

## Open Questions — RESOLVED

1. **Cookie/legal links** → `footer_legal` WordPress menu ✓
2. **WINGS credit** → Hardcoded `<a href="https://wings.design">WINGS</a>` with header underline animation ✓
3. **Newsletter form** → Custom WP Rig component, no third-party plugin ✓

---

## Out of Scope

- Mobile/responsive layout (separate task)
- Mailchimp/Klaviyo integration (can be added to Newsletter component later as a webhook)
- WooCommerce-specific payment method detection
