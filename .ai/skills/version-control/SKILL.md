---
description: Step-by-step guide for version control operations in the Eternal Labs WP theme project.
model: claude-haiku-4-5-20251001
globs: .husky/**, commitlint.config.cjs, .gitignore
---

# Version Control — Eternal Labs

> **Recommended model:** `claude-haiku-4-5-20251001`
> Version control tasks are well-defined and fast — Haiku handles them efficiently.

---

## Repository

- **Remote:** `https://github.com/mrvedmutha/wp-theme-eternal.git`
- **Solo project** — no PR review required, but PRs to `develop` are still good practice for traceability.

---

## Branch Strategy

```
main          ← production only — tagged releases
  └── develop ← integration branch — all features merge here
        └── feature/<slug>   ← new features
        └── fix/<slug>       ← bug fixes
        └── chore/<slug>     ← deps, config, tooling
        └── refactor/<slug>  ← code restructuring
```

### Rules
- **Never commit directly to `main`**
- All work branches off `develop`
- `main` is only updated by merging `develop` when ready for release
- Delete feature branches after merging

---

## Branch Naming

| Type | Format | Example |
|---|---|---|
| Feature | `feature/<short-slug>` | `feature/header-component` |
| Bug fix | `fix/<short-slug>` | `fix/nav-mobile-overlap` |
| Maintenance | `chore/<short-slug>` | `chore/update-deps` |
| Refactor | `refactor/<short-slug>` | `refactor/typography-tokens` |
| Release | `release/<version>` | `release/1.1.0` |

---

## Starting New Work

```bash
# Always branch from develop
git checkout develop
git pull origin develop
git checkout -b feature/<slug>
```

---

## Commit Message Format — Conventional Commits

```
<type>(<scope>): <short description>

[optional body]

[optional footer]
```

### Types
| Type | When to use |
|---|---|
| `feat` | New feature or component |
| `fix` | Bug fix |
| `chore` | Dependencies, config, tooling |
| `refactor` | Code restructuring (no behavior change) |
| `style` | CSS / formatting changes only |
| `docs` | Documentation only |
| `test` | Adding or updating tests |
| `build` | Build system changes |
| `revert` | Reverting a previous commit |

### Scopes (common for this project)
`typography` | `fonts` | `header` | `footer` | `nav` | `blocks` | `tokens` | `build` | `deps`

### Examples
```
feat(typography): add eternal labs fluid type scale
fix(nav): restore mobile breakpoint after token rename
chore(deps): upgrade husky to v9
style(header): adjust padding to 4pt grid
refactor(tokens): migrate spacing to css custom properties
```

### Rules enforced by commitlint
- Type must be from the allowed list above
- Description must be lower-case
- Header must be under 100 characters
- No period at end of subject line

---

## Pre-commit Gate (Hard)

Every commit automatically runs:

1. **lint-staged** — stylelint on staged CSS files, phpcs on staged PHP files
2. **`npm run build`** — full CSS + JS build must pass

If either fails, the commit is **blocked**. Fix the errors shown, re-stage, and retry.

### Common fixes
| Error | Fix |
|---|---|
| stylelint error | Run `npm run lint:css` to see all, fix manually or auto-fix with `stylelint --fix` |
| phpcs error | Run `vendor/bin/phpcs --standard=WordPress <file>` to see violations |
| Build error | Run `npm run build` standalone to see the full error output |

---

## Finishing Work — Merging to Develop

```bash
# On your feature branch, ensure it's up to date
git checkout develop
git pull origin develop
git checkout feature/<slug>
git rebase develop

# Merge back
git checkout develop
git merge --no-ff feature/<slug> -m "feat(<scope>): merge feature/<slug>"
git push origin develop

# Clean up
git branch -d feature/<slug>
```

---

## Releasing to Main

```bash
git checkout main
git merge --no-ff develop -m "chore(release): v<version>"
git tag -a v<version> -m "v<version>"
git push origin main --tags
git checkout develop
```

Version format: `MAJOR.MINOR.PATCH` — update `version` in `package.json` and `style.css` before tagging.

---

## Hotfix (Production Bug)

```bash
git checkout main
git checkout -b fix/<slug>
# ... fix the bug ...
git commit -m "fix(<scope>): <description>"
git checkout main
git merge --no-ff fix/<slug>
git tag -a v<patch-version> -m "v<patch-version>"
git push origin main --tags

# Back-merge into develop
git checkout develop
git merge --no-ff main
git push origin develop
git branch -d fix/<slug>
```

---

## Quick Reference

```bash
# Start work
git checkout develop && git pull && git checkout -b feature/<slug>

# Save progress
git add <files>
git commit -m "feat(<scope>): <description>"

# Finish feature
git checkout develop && git merge --no-ff feature/<slug> && git push origin develop

# Release
git checkout main && git merge --no-ff develop && git tag v<version> && git push origin main --tags
```
