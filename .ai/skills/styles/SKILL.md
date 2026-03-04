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
2. Import it in the relevant css file (ex. `assets/css/src/global.css`) with `@import "_yourfile.css";`
3. Run `npm run dev` to rebuild if dev server is not already running

## Visual Verification (Ralph Loop)

For visual changes, use Playwright to ensure regressions are avoided:

1.  **Baseline**: Run `npm run test:e2e:screenshot -- SCREENSHOT_NAME="before-change.png"` before editing styles.
2.  **Edit**: Apply your CSS changes.
3.  **Verify**: Run `npm run test:e2e:screenshot -- SCREENSHOT_NAME="after-change.png"` and compare the results in `tests/e2e/specs/screenshot.spec.ts-snapshots/`.
4.  **Component Focus**: Use `SCREENSHOT_SELECTOR` to capture only the element you're styling (e.g., `.site-header`).

## Conventions

- **CSS partials**: Source files in `assets/css/src/` should be prefixed with an underscore (e.g., `_header.css`) unless they are intended to be enqueued as standalone files (like `content.css`).
- **Conditional styles**: Use `inc/Styles/Component.php` and the `wp_rig_css_files` filter to load styles only on pages that need them.
- **CSS variables**: Use CSS variables for theme colors, spacing, and other design tokens. Define them in `assets/css/src/_custom-properties.css` and use them throughout the theme.
- **Media queries**: Use custom media queries to manage our responsive breakpoints and always reference these instead of statically writing media query values. Define them in `assets/css/src/_custom-media.css`. Our CSS build process adds these media queries to all CSS files.
- **CSS nesting**: Use CSS nesting to organize styles and avoid deep selectors. Nesting should be used sparingly and only when necessary. Avoid nesting more than 3 levels deep.
- **CSS specificity**: Avoid using high specificity selectors. Use BEM (Block Element Modifier) naming convention to keep selectors short and readable. Use utility classes sparingly and only when necessary.
- **CSS comments**: Use comments to explain complex styles or decisions. Keep comments concise and relevant. Avoid commenting on obvious code.
- **CSS formatting**: Use consistent indentation and spacing. Follow the CSS style guide provided by the project. Avoid unnecessary whitespace and trailing commas.
- **CSS linting**: Use a CSS linter to catch common mistakes and enforce best practices. Configure the linter to match the project's style guide and run it as part of the build process. WP Rig comes with stylelint configured.
- **CSS animations**: Use CSS animations to enhance user experience and create smooth transitions. Keep animations short and avoid using them on elements that are frequently interacted with. Override animations with @prefers-reduced-motion media query to disable animations for users who prefer reduced motion.
- **CSS performance**: Keep header, navigation, global styles and other styles likely to be needed above the fold separate from other styles to improve page load performance. 100% of the CSS should be loaded asynchronously.
