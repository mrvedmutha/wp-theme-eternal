# WP Rig AI Agents Guide

WP Rig is a modern, component-based WordPress theme development framework, starter theme, and build toolkit. WP Rig is very opinionated and provides tooling necssary to follow modern theme development best practices and coding standards. This file serves as the entry point for AI agents to understand the theme's structure and conventions through specialized skills.

## AI Agent Skills

For 2026-ready AI agents, specialized "skills" in the `/.ai/skills/` directory provide step-by-step recipes and architectural guidance. Use these skills to ensure your changes follow WP Rig's opinionated standards.

### Core Architecture & Conventions
- [**Feature Planning (Contract-First)**](.ai/skills/feature-planning/SKILL.md): Strategy for planning and specifying new features before implementation.
- [**Architecture & Conventions**](.ai/skills/architecture/SKILL.md): Theme structure, file mappings, and coding standards.
- [**Create a New Component**](.ai/skills/create-component/SKILL.md): Recipe for scaffolding and registering new theme features.
- [**Advanced Templating**](.ai/skills/advanced-templating/SKILL.md): Creating and using template tags via the `wp_rig()` singleton.

### Development & Build System
- [**npm Scripts**](.ai/skills/npm-scripts/SKILL.md): Using the build system for JS, CSS, and theme bundling.
- [**Theme Bundling & Root Folders**](.ai/skills/theme-bundling/SKILL.md): Managing root-level folders (like WooCommerce template overrides) for the production bundle.
- [**Styles & CSS**](.ai/skills/styles/SKILL.md): Managing CSS partials, variables, and the style build process.
- [**Modern Dev Workflow**](.ai/skills/modern-dev-workflow/SKILL.md): Using the Vite-powered dev server and managing local configuration.
- [**Gutenberg Blocks**](.ai/skills/gutenberg-blocks/SKILL.md): Scaffolding and managing theme-scoped blocks.

### Customization & Extension
- [**PHP Filters & Hooks**](.ai/skills/php-filters/SKILL.md): Complete guide to extending assets and behavior via hooks.
- [**Theme Settings (Options Framework)**](.ai/skills/theme-settings/SKILL.md): Adding settings via React-based Options framework.
- [**EZ Customizer**](.ai/skills/ez-customizer/SKILL.md): Adding Customizer settings via JSON configuration.
- [**Child Theme Development**](.ai/skills/child-theme-development/SKILL.md): Scaffolding and managing child themes.
- [**WP-CLI Commands**](.ai/skills/wp-cli/SKILL.md): Custom commands for environment setup and management.

### Quality Assurance
- [**E2E Testing (Playwright)**](.ai/skills/e2e-testing/SKILL.md): Running Playwright tests, accessibility audits, and regression screenshots.
- [**Code Quality Standards**](.ai/skills/code-quality-standards/SKILL.md): Adhering to PHPStan, PHPCS, Rector, and Prettier.
- [**PHPUnit Testing**](.ai/skills/phpunit-testing/SKILL.md): Writing unit and integration tests for PHP components.

## Capabilities

### WP Rig Documentation (MCP)

Access real-time documentation from wprig.io using the integrated Model Context Protocol server.

- **Command**: `npm run mcp`
- **Tools**:
  - `search_wprig_docs`: Find guides and best practices.
  - `get_wprig_doc`: Retrieve full documentation content by slug.

## Usage for Agents

1. **Locate the relevant skill** for your task in the list above.
2. **Follow the recipes** and conventions documented in the skill's `SKILL.md` file.
3. **Prefer specialized tools** like `npm run create-rig-component` or `wp rig` commands over manual file operations where applicable.
