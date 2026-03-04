---
description: Guide to managing CSS styles, partials, and builds in WP Rig.
globs: assets/css/src/**/*.css, build-css.js
---

# WP Rig Styles & CSS

This guide describes how to work with CSS in WP Rig.

## CSS Structure

Source files are in `assets/css/src/` and processed by `build-css.js`.

| File | Purpose |
|------|---------|
| `global.css` | Main entry point - imports all partials |
| `_custom-properties.css` | CSS custom properties (variables) |
| `_custom-media.css` | Custom media query breakpoints |
| `_reset.css` | CSS reset/normalize |
| `_typography.css` | Font styles, headings, text |
| `_elements.css` | Base HTML element styles |
| `_links.css` | Link styles and states |
| `_header.css` | Site header styles |
| `_navigation.css` | Navigation menu styles |
| `_footer.css` | Site footer styles |
| `_forms.css` | Form element styles |
| `_media.css` | Images, video, embeds |
| `_blocks.css` | WordPress block styles |
| `_accessibility.css` | Screen reader and a11y styles |
| `_utility.css` | Utility/helper classes |
| `content.css` | Post/page content styles (conditional) |
| `sidebar.css` | Sidebar styles (conditional) |
| `widgets.css` | Widget styles |
| `comments.css` | Comment section styles (conditional) |
| `front-page.css` | Front page specific styles |
| `editor/editor-styles.css` | Block editor styles |
| `admin/theme-settings.css` | Admin settings page styles |

## Common Style Tasks

### Change the header styles

1. Edit `assets/css/src/_header.css`
2. Run `npm run dev` to rebuild and watch
3. Header styles are imported via `global.css`

### Add a new CSS partial

1. Create `assets/css/src/_yourfile.css`
2. Import it in `assets/css/src/global.css` with `@import "_yourfile.css";`
3. Run `npm run dev` to rebuild

## Conventions

- **CSS partials**: Source files in `assets/css/src/` should be prefixed with an underscore (e.g., `_header.css`) unless they are intended to be enqueued as standalone files (like `content.css`).
- **Conditional styles**: Use `inc/Styles/Component.php` and the `wp_rig_css_files` filter to load styles only on pages that need them.
