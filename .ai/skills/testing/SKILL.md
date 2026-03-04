---
description: Guide for running End-to-End tests and regression screenshots in WP Rig.
globs: tests/e2e/**/*.ts, playwright.config.ts
---

# WP Rig Testing (E2E)

This guide describes how to use Playwright for End-to-End testing in WP Rig.

## Files & Directories

| File | Purpose |
|------|---------|
| `playwright.config.ts` | Main Playwright configuration |
| `tests/e2e/specs/` | Test specifications (e.g., `smoke.spec.ts`) |
| `tests/e2e/specs/screenshot.spec.ts` | Custom script for regression screenshots |
| `tests/e2e/specs/screenshot.spec.ts-snapshots/` | Directory where snapshots are stored |

## Common Testing Tasks

### Run tests

1. Run `npm run test:e2e` to execute all tests.
2. Run `npm run test:e2e:ui` to open the Playwright UI for debugging.

### Take a regression screenshot

1. Use `npm run test:e2e:screenshot`
2. Optional environment variables: `SCREENSHOT_URL`, `SCREENSHOT_SELECTOR`, `SCREENSHOT_NAME`
3. Snapshots are saved in `tests/e2e/specs/screenshot.spec.ts-snapshots/`
4. Use `-- -u` flag to update/create baselines: `npm run test:e2e:screenshot -- -u`

## Best Practices

- Ensure the environment is set up with test data before running tests (`wp rig test-setup`).
- Follow accessibility guidelines and use the `@axe-core/playwright` integration for automated a11y checks.
