# SPEC: Version Control Setup — Eternal Labs

**Date:** 2026-03-24
**Status:** AWAITING APPROVAL

---

## Objective

Set up a complete version control system for `mrvedmutha/wp-theme-eternal`:
1. A **Version Control skill** for AI agents at `.ai/skills/version-control/SKILL.md`
2. **Hard-gated commit automation** via Husky + lint-staged + commitlint
3. A `develop` branch as the integration base going forward

---

## Part A — AI Agent Skill

**File:** `.ai/skills/version-control/SKILL.md`

Covers:
- Branching strategy (main → develop → feature/fix branches)
- Conventional commit format with examples for this project
- Branch naming conventions (`feature/`, `fix/`, `chore/`, `refactor/`)
- PR checklist before merging to develop
- Release process: develop → main + version tag
- What the automated gates check and how to fix failures

---

## Part B — Automation (Hard Gate)

### Packages to install (devDependencies)
- `husky` — git hook runner
- `lint-staged` — runs linters only on staged files
- `@commitlint/cli` + `@commitlint/config-conventional` — enforces commit message format

### Git hooks

| Hook | Trigger | What runs |
|---|---|---|
| `pre-commit` | Every `git commit` | lint-staged (stylelint + phpcs on staged files) + `npm run build` |
| `commit-msg` | Every `git commit` | commitlint — rejects non-conventional messages |

### `lint-staged` config (in `package.json`)
```json
"lint-staged": {
  "assets/css/src/**/*.css": ["stylelint --fix"],
  "**/*.php": ["vendor/bin/phpcs --standard=WordPress"]
}
```

### `commitlint` config — `commitlint.config.js`
```js
module.exports = { extends: ['@commitlint/config-conventional'] }
```

### Allowed commit types for this project
`feat` | `fix` | `chore` | `refactor` | `style` | `docs` | `test` | `build`

---

## Part C — Branch Setup

1. Create `develop` branch from current `master`
2. Commit today's typography work onto `develop` with message:
   `feat(typography): add Eternal Labs brand type scale and base CSS tokens`
3. Push `develop` to GitHub

---

## Files Created/Modified

| File | Action |
|---|---|
| `.ai/skills/version-control/SKILL.md` | Create — agent skill |
| `commitlint.config.js` | Create |
| `.husky/pre-commit` | Create |
| `.husky/commit-msg` | Create |
| `package.json` | Update — add lint-staged config + husky prepare script |

---

## Approval

- [ ] Approved to proceed
