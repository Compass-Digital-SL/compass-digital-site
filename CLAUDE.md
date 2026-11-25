# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

# Compass Digital — Claude working rules

Marketing site for Compass Digital (Sri Lanka): websites, SEO and POS systems.
WordPress classic PHP theme, built with Vite. Visitors request a quote; there is no checkout.

## Key docs
- `docs/PROGRESS.md` — task list, decisions, what is next. Read first, every task.
- `docs/BRAND.md` — name, palette, voice, services, industries.
- `docs/DESIGN.md` — design system, tokens, motion rules.
- `docs/SEO.md` — SEO strategy, keywords, technical SEO rules.
- `docs/RUNNING.md` — local run checklist; `docs/NAMECHEAP-DEPLOY.md`, `docs/SECRETS.md`, `docs/RESTORE.md` — deploy, secrets, rollback.

## Standing rules
1. At the start of each task read only `docs/PROGRESS.md` and the docs the task names.
   Never scan the whole repo.
2. Do one task only, then stop.
3. Branch per task (`task/NN-name`), small conventional commits, PR to `main`.
4. WordPress classic PHP theme + Vite build. No page builders. No ACF.
   No jQuery on the front end. Do not add a plugin for anything under 50 lines of code.
5. Performance budget (mobile):
   - Lighthouse 95+
   - LCP < 1.8s, CLS < 0.05, INP < 200ms
   - Homepage JS < 90KB gzipped, CSS < 40KB gzipped
6. Accessibility: WCAG 2.2 AA, keyboard friendly, honour `prefers-reduced-motion`.
7. For tasks labelled **L**: give a plan in 10 bullets or fewer and wait for "go".
8. Never read or print `.env` files or secrets. Ask instead.
9. Before finishing: run lint, build and tests, update `docs/PROGRESS.md`
   (done, decisions, next), commit.
10. Final message: 10 lines or fewer — what changed, the URL to check,
    anything the owner must do, and a reminder to type `/clear`.

## Commands
Run from the repo root. One-time: `npm install`, `npm run theme:install`, `composer install`.

| Command | Purpose |
| --- | --- |
| `npm run wp:start` / `wp:stop` | wp-env (Docker) WordPress at http://localhost:8888 (`admin` / `password`) |
| `npm run wp:seed` | Activate theme, create pages, sample content (idempotent; re-run after pulls that add pages) |
| `npm run wp:reset` | Wipe DB and re-seed |
| `npm run dev` | Vite dev server (live reload) |
| `npm run build` | Production asset build into `theme/assets/dist` (gitignored) |
| `composer run lint:php` / `fix:php` | PHPCS (WordPress standard, `phpcs.xml`) / auto-fix |
| `npm run lint:js` / `lint:css` | ESLint / Stylelint |
| `npm run test:e2e` | Playwright smoke + axe (site must be running and seeded) |
| `npm run lhci` | Lighthouse CI budgets (`lighthouserc.json`, `budget.json`) |

Single test: `npx playwright test tests/e2e/smoke.spec.js --project=chromium -g "<title>"`.
Point tests at another host with `SITE_URL=...`.
Always use the `npm run wp:*` scripts, never `wp-env` directly: `scripts/wp-env.js` patches a DNS
connectivity check. WP-CLI: `node scripts/wp-env.js run cli wp <args>`.

## Architecture
Three deployable pieces, mapped into WordPress by `.wp-env.json`:
- **`theme/`** — classic theme, presentation only. `functions.php` just requires the
  `inc/*.php` modules in order (setup, assets, seo, services, industries, work, quote…).
  Page content for services/industries lives in PHP arrays (`inc/*-content.php`) rendered by
  `page-service.php` / `page-industry.php`; sections are in `template-parts/`.
- **`plugins/compass-core/`** — all client data so it survives a theme change: CPTs (Work case
  studies, Testimonials, Quote Requests), Industry/Service taxonomies, native meta boxes,
  Site Settings page, quote form handler, chat/AI assist, analytics/consent, WP-CLI commands.
  The theme only reads this data.
- **`mu-plugins/`** — always-on: security headers/CSP (Report-Only) and contact details that
  override placeholders in `theme/inc/nav.php`.

Asset pipeline (`theme/vite.config.js` + `theme/inc/assets.php`): single entry
`assets/src/js/main.js` (imports CSS). Dev server writes `assets/dist/hot`; PHP sees it and
loads from `localhost:5173`, otherwise it enqueues hashed files from
`assets/dist/.vite/manifest.json`. The build also emits `assets/dist/critical.css`
(`tokens.css` + `assets/critical.css`, minified) which PHP inlines in `<head>`.
`assets/src` is excluded from deploys, so anything PHP reads at runtime must be in `dist`.
Design tokens live in `assets/src/css/tokens.css`.

## CI / deploy
- `ci.yml` (PRs + `main`): PHPCS/ESLint/Stylelint/`php -l`, Vite build, then wp-env +
  Playwright + axe + Lighthouse CI. Must be green to merge.
- `deploy.yml`: push to `main` → staging; tag `v*.*.*` → production (manual approval).
  Ships code only (theme, compass-core, mu-plugins) via rsync into timestamped releases with an
  atomic `current` symlink; uploads and DB are never touched. Theme deploys as `themes/compass`.

## Conventions
- Commits: Conventional Commits (`feat:`, `fix:`, `docs:`, `chore:`, `refactor:`, `perf:`, `test:`, `ci:`).
- Branches: `task/NN-short-name` (e.g. `task/03-theme-build`).
- Line endings LF, UTF-8, 2-space indent — see `.editorconfig`. PHP uses **tabs** (WordPress
  standard, enforced by PHPCS) even though `.editorconfig` says 4 spaces; match existing files.
- Copy follows the voice in `docs/BRAND.md`: plain, confident, warm, no jargon, no hype.
- Colours come from design tokens only; never hard-code hex values in components.
