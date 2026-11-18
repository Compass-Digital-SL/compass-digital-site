# Progress

## Tasks
- [x] 1 Scaffolding
- [x] 2 Local WP env
- [x] 3 Theme and build
- [x] 4 CI
- [ ] 5 SEO strategy
- [ ] 6 Design system
- [ ] 7 Navigation
- [ ] 8 Hero and motion
- [ ] 9 Post types and settings
- [ ] 10 Home page
- [ ] 11 Service pages
- [ ] 12 Industry pages
- [ ] 13 Work portfolio
- [ ] 14 About/Process/FAQ/Insights
- [ ] 15 Quote flow
- [ ] 16 Technical SEO
- [ ] 17 Local SEO and analytics
- [ ] 18 Performance
- [ ] 19 Accessibility and QA
- [ ] 20 Security and legal
- [ ] 21 CD pipeline
- [ ] 22 Go-live
- [ ] 23 Post-launch

## Log

### Task 1 — Scaffolding (2026-11-18)
**Done**
- Added CLAUDE.md (standing rules), docs/BRAND.md, docs/DESIGN.md (stub), docs/SEO.md (stub), docs/PROGRESS.md.
- Added .gitignore, .editorconfig, README.md, .github/ (PR template, issue template).
- First commit on `main`.

**Decisions**
- Default branch is `main`; every later task uses `task/NN-name` and a PR.
- `assets-to-use/` (raw logo PNGs + notes) is git-ignored as source material. Optimised copies
  (SVG/WebP, sized) get added to the theme's `assets/` in Task 3/6; originals stay in design storage.
- No lint/build/tests exist yet; they arrive in Task 3 (build) and Task 4 (CI).

**Next**
- Task 2: Local WP env.

### Task 2 — Local WP env (2026-11-18)
**Done**
- Added `.wp-env.json`: PHP 8.3, WordPress pinned to 7.1.4, port 8888, theme mapped from `./theme`,
  custom plugins mapped to `wp-content/plugins/compass-digital` from `./plugins`.
- Plugins: Rank Math SEO (`seo-by-rank-math`) in all envs; Query Monitor (`query-monitor`)
  in development only.
- Added `package.json` with `@wordpress/env` and scripts: `wp:start`, `wp:stop`,
  `wp:reset`, `wp:seed`.
- Added `scripts/setup.sh` (WP-CLI via `wp-env run cli`): permalinks `/%postname%/`,
  timezone Asia/Colombo, `blog_public 0` (never index local), comments off, sample content
  and Akismet/Hello Dolly removed, empty pages (Home, Services, Industries, Work, About,
  Insights, Contact, Get a quote, Privacy, Terms) created, Home set as static front page.
  Idempotent — safe to re-run.
- README: full local-WordPress section with every command in plain words.

**Decisions**
- `wp:reset` = `wp-env clean all` + re-seed (keeps containers, wipes the DB).
- Query Monitor kept dev-only via `env.development.plugins`; Rank Math is site-wide.
- `theme/` and `plugins/` tracked via `.gitkeep`; real code arrives in Task 3.
- Pinned `core` to `WordPress/WordPress#7.1.4` instead of `null` (latest): `core: null`
  makes wp-env fetch the latest version from api.wordpress.org, which fails on this
  Windows machine ("Could not find the current WordPress version in the cache"). 7.1.4
  is the current stable. Bump the version + `wp:reset` to upgrade.

**Next**
- Task 3: Theme and build.

### Task 3 — Theme and build (2026-11-18)
**Done**
- Classic theme `compass-digital` in `theme/`: `style.css` (theme header), `functions.php`
  (loads `inc/setup|cleanup|assets|fonts`), `theme.json` (palette, fluid type/spacing tokens),
  templates `header/footer/front-page/page/single/archive/404/index` + `template-parts/`.
- Semantic HTML: `lang` via `language_attributes()`, skip link to `#main`, one `<h1>` per
  template, landmark roles (banner/main/contentinfo), accessible mobile-nav toggle (no jQuery).
- Vite build (`theme/vite.config.js`, `theme/package.json`): root `assets/src`, output
  `assets/dist` with hashed filenames + `.vite/manifest.json`. PHP reads the manifest and
  enqueues the entry JS as a module + its CSS. Dev server writes `assets/dist/hot`; PHP then
  loads `@vite/client` + entry from the dev server for live reload. Root scripts: `npm run
  build`, `npm run dev`, `npm run theme:install`.
- Front-end bloat stripped (`inc/cleanup.php`): emojis, oEmbed discovery, `wp-block-library` /
  `classic-theme-styles` / `global-styles` CSS, jQuery, generator/RSD/WLW/shortlink/extra-feed
  head links, REST `<head>` link + Link header, X-Pingback, s.w.org dns-prefetch.
- Self-hosted fonts (`assets/fonts/`): Inter (sans) + Fraunces (serif display) variable woff2,
  Latin subset from Google Fonts, served as a static un-hashed `fonts.css`, preloaded in
  `inc/fonts.php`, `font-display: swap`, metric-adjusted `size-adjust` fallbacks to cut CLS.

**Decisions**
- Fonts kept out of the Vite graph and served at stable URLs so `<link rel=preload>` hrefs
  match the `@font-face` src (Vite would otherwise fingerprint them, breaking the preload).
- `theme.json` is the token source of truth for the editor; runtime tokens live in
  `assets/src/css/tokens.css` because `global-styles` is dequeued on the front end.
- Built `assets/dist/` is git-ignored (CI builds it in Task 4); `package-lock.json` tracked.
- Branched `task/03` on top of `task/02` (not `main`) so the wp-env setup is present; Task 2's
  PR is not merged yet.

**Next**
- Task 4: CI (lint, build, PHP checks).

### Task 4 — CI (2026-11-18)
**Done**
- `.github/workflows/ci.yml`: runs on every PR and push to `main`. Three jobs —
  **lint** (PHPCS WordPress standard, ESLint, Stylelint, `php -l`), **build** (Vite,
  uploads `theme-dist` artifact), **e2e** (boots wp-env, seeds, Playwright smoke + axe,
  Lighthouse CI). npm + Composer caches; concurrency cancels superseded runs.
- Tooling + config added so the jobs are real: root `composer.json` (squizlabs/php_codesniffer,
  wp-coding-standards/wpcs, phpcompatibility-wp) + `phpcs.xml`; `eslint.config.js` (flat, ESLint 9)
  + `.stylelintrc.json`; `playwright.config.js` + `tests/e2e/smoke.spec.js` (home `/`,
  quote `/get-a-quote/`, 404 — status + no console/page errors + axe critical/serious);
  `.lighthouserc.json` with CLAUDE.md rule-5 budgets. Root `package.json` scripts:
  `lint:js`, `lint:css`, `test:e2e`, `lhci`, plus devDeps.
- `.github/dependabot.yml`: weekly npm (root + theme), Composer, github-actions.
- PR template extended with SEO / Speed / Accessibility / Mobile checklists.
- README: CI section (what each job checks, run-locally commands) + donkey-level
  "turn on branch protection" steps for GitHub.
- `.gitignore`: Playwright report/results dirs.

**Decisions**
- INP has no Lighthouse lab metric, so the budget is enforced as Total Blocking Time < 200ms;
  real INP comes from field data later (Task 18 performance).
- Lighthouse asserts performance + accessibility ≥ 0.95 (error) and best-practices as warn;
  SEO category is not asserted because the local wp-env sets `blog_public 0` (noindex), which
  would fail SEO scoring. Real SEO checks live in Tasks 16–17.
- Build artifact is shared from the **build** job to **e2e** so both exercise the same assets.
- All config/YAML/JSON/XML validated locally; the lint/build/test runs themselves need
  `composer install` + `npm ci` (network), so they first execute on the GitHub runner.

**Next**
- Task 5: SEO strategy.
