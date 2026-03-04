# WP Rig AI Agents Guide

WP Rig is a modern, component-based WordPress theme development framework, starter theme, and build toolkit. WP Rig is very opinionated and provides tooling necssary to follow modern theme development best practices and coding standards. This file serves as the entry point for AI agents to understand the theme's structure and conventions through specialized skills.

## AI Agent Skills

For 2026-ready AI agents, specialized "skills" in the `/.ai/skills/` directory provide step-by-step recipes and architectural guidance. Use these skills to ensure your changes follow WP Rig's opinionated standards.

### Core Architecture & Conventions
- [**Feature Planning (Contract-First)**](.ai/skills/feature-planning/SKILL.md): Strategy for planning and specifying new features before implementation.
- [**Architecture & Conventions**](.ai/skills/architecture/SKILL.md): Theme structure, file mappings, and coding standards.
- [**Create a New Component**](.ai/skills/create-component/SKILL.md): Recipe for scaffolding and registering new theme features.

### Development & Build System
- [**npm Scripts**](.ai/skills/npm-scripts/SKILL.md): Using the build system for JS, CSS, and theme bundling.
- [**Theme Bundling & Root Folders**](.ai/skills/theme-bundling/SKILL.md): Managing root-level folders (like WooCommerce template overrides) for the production bundle.
- [**Styles & CSS**](.ai/skills/styles/SKILL.md): Managing CSS partials, variables, and the style build process.

### Customization & Extension
- [**PHP Filters & Hooks**](.ai/skills/php-filters/SKILL.md): Complete guide to extending assets and behavior via hooks.
- [**Theme Settings (Options Framework)**](.ai/skills/theme-settings/SKILL.md): Adding settings via React-based Options framework.
- [**WP-CLI Commands**](.ai/skills/wp-cli/SKILL.md): Custom commands for environment setup and management.

### Quality Assurance
- [**Testing (E2E)**](.ai/skills/testing/SKILL.md): Running Playwright tests and regression screenshots.

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
