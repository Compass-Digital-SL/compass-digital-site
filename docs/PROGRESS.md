# Progress

## Tasks
- [x] 1 Scaffolding
- [x] 2 Local WP env
- [x] 3 Theme and build
- [x] 4 CI
- [ ] 5 SEO strategy
- [x] 6 Design system
- [x] 7 Navigation
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

### Task 7 — Navigation (2026-11-18)
**Done**
- One nav data source `inc/nav.php`: `compass_nav_map()` (6 services, 5 industries,
  Work, Company), `compass_primary_links()`, filterable `compass_contact_details()` +
  `compass_whatsapp_url()`. Header, command menu, mobile sheet and footer all read it.
- **Desktop header** (`header.php`, `template-parts/header/nav.php`): logo left; Services +
  Industries CSS-first dropdowns (real links, open on hover/focus, JS upgrades to click),
  Work/About/Insights; right side ghost **Navigate (K)** + brass **Get a quote**. Sticky,
  glass, shrinks on scroll (`.is-scrolled`).
- **Command menu** (`template-parts/command-menu.php` + `assets/src/js/command-menu.js`):
  full-screen glass overlay, server-rendered grouped list, K / button to open, type-to-filter,
  Arrow/Enter/Esc, focus trap, `inert` background, `[hidden]` default.
- **Mobile bottom bar** (<768px, `template-parts/mobile-bar.php`): Home, Services, Work,
  WhatsApp, Quote; Services opens a bottom sheet (Services/Industries/Company); body gets
  bottom padding so content clears the bar; safe-area insets.
- **Scroll rail** (`template-parts/scroll-rail.php`, ≥1240px): JS builds compass ticks from
  on-page `[data-waypoint][id]` sections, IntersectionObserver active state, click-to-jump;
  stays hidden until a page opts in (none yet).
- **Breadcrumbs** (`template-parts/breadcrumbs.php`): inner pages only, ancestor-aware,
  `aria-current`; ready for BreadcrumbList schema in the SEO task.
- **Footer** rebuilt into sitemap columns (Services/Industries/Company/Legal) + contact
  (phone/WhatsApp/email/address), social (hidden until set), registration line.
- **Desktop contact chip** (`template-parts/contact-chip.php`): one WhatsApp chip, desktop only.
- New `assets/src/css/nav.css` (imported into bundle), tokens only. Verified desktop +
  375px in-browser; command menu, dropdowns and sheet work; axe clean (6/6 e2e pass),
  no console errors. Bundle: CSS 5.35KB gzip, JS 2.20KB gzip — within budget.

**Decisions**
- Everything is progressive enhancement over real `<a>` links: header, command-menu,
  sheet and footer all work with JS disabled.
- Services/industries lists are hard-coded from `docs/BRAND.md` (child pages arrive in
  Tasks 11–12); URLs derived from section bases so they resolve once those pages exist.
- Vendor-prefixed `-webkit-mask`/`-webkit-backdrop-filter` dropped to satisfy the project's
  `property-no-vendor-prefix` lint rule; unprefixed `mask`/`backdrop-filter` are fine in
  current browsers and match the existing glass in `components.css`.
- Nav URL helpers return **raw** URLs; callers `esc_url()` at output (keeps PHPCS escaping
  checks honest, no double-escaping).

**Owner action**
- Contact + registration are placeholders (`+94 11 000 0000`, `hello@compassdigital.lk`,
  empty social/reg-no). Override via the `compass_contact_details` filter with real details.

**Next**
- Task 8: Hero and motion. (Hero section can expose `[data-waypoint]` anchors to light up
  the scroll rail.)

### Task 6 — Design system (2026-11-18)
**Done**
- Wrote `docs/DESIGN.md`: dark-first spec — tokens, type/spacing/radii/elevation,
  focus ring, motion, glass rules, component inventory, measured AA contrast table.
- Expanded `assets/src/css/tokens.css`: semantic dark-base tokens (surface/text/accent/
  border), derived `--color-navy-700`, `--color-teal-300` (AA link on dark), glass tokens
  (≤12px blur, 5% fill, 10% border), radii, 3 elevation steps, motion (150/300/600ms, one
  easing family), brass focus ring.
- New `assets/src/css/components.css`: buttons (primary/ghost), links, cards (+glass hover),
  tags, badges, form fields, accordion, tabs, section header; light-section overrides.
- `main.css` flipped to dark base (navy surface, ivory text) + layout primitives
  (`.container`, `.grid`); `theme.json` styles background→navy, text→ivory.
- New `assets/src/js/ui.js`: accordion + WAI-ARIA tabs (roving tabindex, arrow keys),
  no jQuery, progressive enhancement; wired into `main.js`.
- Hidden `/styleguide/` page: template `page-styleguide.php` (renders all tokens +
  components, styleguide-only CSS inlined so it never ships to real pages) + `inc/styleguide.php`
  (noindex,nofollow via `wp_robots`, excluded from core sitemap). WP page created (ID 26).

**Decisions**
- **Direction change:** site is now **dark-first** (brief's premium editorial look), reversing
  Task 3's ivory default. Ivory kept as `--surface-light` / `.section--light` for rare light blocks.
- Base teal (`#2F7F86`) is 3.1:1 on navy — **fails AA for text**; links/body on dark use
  brightened `--color-teal-300` (#5FB5BD, 6.2:1). Brass fails as small text on ivory, so the
  light-section eyebrow uses dark-brass `#7A5E1F` (5.5:1) and light links dark-teal `#1F6168`.
- Glass is restrained: header, hover cards, command menu only; documented "≤3 per viewport".
- Styleguide layout CSS inlined in the template (not the bundle) to protect the perf budget.
- Verified at 375px and laptop width in-browser; tabs/accordion interactive; CSS 8.85KB
  (2.52KB gzip), JS 1.39KB (0.58KB gzip) — well under budget.

**Next**
- Task 7: Navigation. (Header logo is currently black-on-navy — fix header/branding for the
  dark base there.)

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
