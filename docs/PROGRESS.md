# Progress

## Tasks
- [x] 1 Scaffolding
- [x] 2 Local WP env
- [x] 3 Theme and build
- [ ] 4 CI
- [ ] 5 SEO strategy
- [x] 6 Design system
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
