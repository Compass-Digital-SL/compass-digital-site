# Progress

## Tasks
- [x] 1 Scaffolding
- [x] 2 Local WP env
- [x] 3 Theme and build
- [x] 4 CI
- [x] 5 SEO strategy
- [x] 6 Design system
- [x] 7 Navigation
- [x] 8 Hero and motion
- [x] 9 Post types and settings
- [x] 10 Home page
- [x] 11 Service pages
- [x] 12 Industry pages
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

### Task 12 — Industry pages (2026-11-18)
**Done**
- **Industries hub** (`theme/page-industries.php`, slug `industries`) + **one shared
  template** (`theme/page-industry.php`, `Template Name: Industry`) used by all five
  industry pages: Retail shops, Restaurants & cafes, Travel & tourism, Ecommerce brands,
  Corporates & SMEs.
- **Single content source** `theme/inc/industries-content.php` (`compass_industries_data()`),
  keyed by slug. Each page: outcome-led headline + lede, intro prose (industry-specific
  pains in customer language from `SEO.md`), a "Sounds like you if…" signs aside, pains →
  fixes, **"Services that fit"** cards that cross-link to the matching `/services/<slug>/`
  pages with *how* each one helps, relevant work (queried by the `industry` taxonomy term),
  5 FAQs, a related-services strip (+ back to the hub), and a quote CTA. **500+ words of
  unique copy per page** (Travel renders ~1,900 words incl. nav/footer). Travel covers
  **booking enquiry flows, multilingual readiness, mobile-first, Google Maps & reviews,
  and WhatsApp enquiries** as required.
- **Schema** (`theme/inc/industries.php` for titles/meta; FAQ/Breadcrumb helpers reused
  from `inc/services.php`): each industry page emits `FAQPage` + `BreadcrumbList` JSON-LD
  from the same copy; hub emits `BreadcrumbList`. Per-page keyword-map `<title>` + meta
  description via `pre_get_document_title` + `wp_head`, scoped to these pages.
- **CTA pre-fill**: reuses `compass_quote_url()` (`?service=<industry>`), matching the
  quote form field.
- **CSS**: reuses the Task 11 `.service-*` classes; added a small `.industry-fit` block to
  `services.css`. Built CSS **43.2KB raw / 8.68KB gzip** (budget 40KB gzip). No new JS —
  FAQ reuses `.accordion`/`ui.js`, reveals reuse `[data-reveal]`.
- **Pages seeded**: `seed_industry_pages()` added to the WP-CLI seeder (idempotent) creates
  the hub + 5 child pages at `/industries/<slug>/` with the template assigned.
- **Tests**: `/industries/` and `/industries/travel-and-tourism/` added to the Playwright
  smoke list and Lighthouse config. Verified locally — all pages 200, titles/meta/single
  H1/schema correct, cross-links to services present, 4/4 smoke+axe pass, Stylelint + ESLint
  + Vite build clean; PHPCS clean on all new/changed files.

**Decisions**
- Mirrors Task 11 exactly: one shared `Template Name` template selected by page slug, one
  data source driving both copy and structured data, child slugs = industry slugs.
- Related work uses the existing `industry` taxonomy (term names match `work_term`); only 3
  seeded samples have industries, so Ecommerce/Corporates pages simply omit the work section
  until real case studies land (Task 13).
- Reused the service-page CSS wholesale rather than a new stylesheet, keeping the gzip
  budget comfortable.

**Next**
- Task 13: Work portfolio.

### Task 11 — Service pages (2026-11-18)
**Done**
- **Services hub** (`theme/page-services.php`, slug `services`) + **one shared template**
  (`theme/page-service.php`, `Template Name: Service`) used by all six service pages:
  Website design & development, SEO, POS systems, Mobile app development, Ecommerce
  websites, Custom software.
- **Single content source** `theme/inc/services-content.php` (`compass_services_data()`),
  keyed by slug. Each page: outcome-led headline, who-it's-for, pains-we-solve (customer
  language from `SEO.md`), what's included, shared Listen→Plan→Build→Support process,
  tech & deliverables, "quote on request" pricing note + what affects the price (**no
  prices anywhere**), related industries (cross-links), related work (queried by `service`
  taxonomy term), 5 FAQs, and a "Get a quote for this" CTA. **600+ words of unique copy
  per page** (SEO page renders ~2,260 words), weaving each page's primary + secondary
  keywords naturally in the BRAND.md voice.
- **Schema** (`theme/inc/services.php`): each service page emits `Service`
  (provider = Organization, areaServed Sri Lanka, hasOfferCatalog), `FAQPage` and
  `BreadcrumbList` JSON-LD, generated from the same copy; hub emits `BreadcrumbList`.
  Per-page keyword-map `<title>` and meta description via `pre_get_document_title` +
  `wp_head` (scoped to these pages).
- **CTA pre-fill**: `compass_quote_url()` appends `?service=<name>` to `/get-a-quote/`,
  matching the existing `_compass_service` field for the quote form to read.
- **CSS** `theme/assets/src/css/services.css` (tokens/components only, no hard-coded
  hex), imported in `main.css`. Built CSS 42.9KB raw / **8.6KB gzip** (budget 40KB).
  No new JS — FAQ reuses `.accordion`/`ui.js`, reveals reuse existing `[data-reveal]`.
- **Pages seeded**: `seed_service_pages()` added to the WP-CLI seeder (idempotent) creates
  the hub + 6 child pages at `/services/<slug>/` with the template assigned.
- **Tests**: `/services/` and `/services/seo/` added to the Playwright smoke list and
  Lighthouse config. Verified locally — all pages 200, titles/meta/H1/schema correct,
  no prices, 4/4 smoke+axe pass, PHPCS + Stylelint + Vite build clean.

**Decisions**
- One shared `Template Name` template (assigned via `_wp_page_template`) selected by page
  slug, rather than six `page-<slug>.php` files — keeps all copy in one data source and
  the structured data mirroring the visible copy.
- Child page slugs equal the full service slugs (e.g. `/services/website-design-and-development/`)
  so breadcrumbs, URLs and the data keys all line up.
- Titles/metas/`Service`/`FAQPage`/`BreadcrumbList` are emitted locally for these pages and
  are easy to lift out; **Task 16** generalises titles, metas and schema (incl. global
  Organization/LocalBusiness) site-wide, at which point `compass_services_head()` and the
  title filter can be unhooked.
- The front-end quote form isn't built yet (**Task 15**); the pre-fill is delivered as a
  `?service=<name>` query param the form will read.

**Next**
- Task 12: Industry pages.

### Task 10 — Home page (2026-11-18)
**Done**
- Built the eight home sections after the hero in `front-page.php`, each a self-contained
  part in `theme/template-parts/home/` (pain-selector, services, industries, work, process,
  proof, faq, cta). Real, human, plain-English copy from the keyword map — no lorem, one `<h1>`
  (the hero's); every section uses `<h2>` + the design-system `.section-header`.
- **“Where are you stuck?” pain-point selector**: six cards whose anchors line up with the Task 8
  hero bearings (`#seo #websites #pos #app #ecommerce #travel`). Each tailored panel gives the
  problem in the customer's words, how we fix it, one honest proof point, and buttons to the
  service page + Get a quote. **Works with JS off** as anchored sections (all panels rendered
  and visible); `ui.js` `initPainSelector` enhances it into a swap-in-place selector driven by
  card clicks, the hero bearings and the URL hash (back/forward, deep links), keyboard-accessible.
- Services preview (6, `.card--glass` hover), industries strip (5 chips), recent-work scroll-snap
  carousel (featured `work`, falls back to latest 3, `WP_Query`), how-we-work 4 steps with a
  CSS line that draws on reveal, proof (number counters + featured testimonials + four honest
  guarantees), FAQ (6 Q&As on the `.accordion` pattern) with inline **FAQPage** JSON-LD, and the
  final CTA band “Tell us where you want to go. We'll send a quote.”
- New `assets/src/css/home.css` (tokens only) imported into `main.css`. `.front-page` now runs
  full-bleed so section bands reach the edges; the hero is unaffected (it centres its own inner).

**Decisions**
- Proof section stays dark-first (its stat/quote/guarantee cards are dark); the `.section--light`
  variant would have fought their text colours for no gain.
- Proof numbers are honest standards we hold to (Lighthouse 95+, <2s load, 6 services, 100% owned)
  rather than invented client metrics.
- The pain selector uses progressive enhancement over pure-CSS `:target` so the hero bearings
  (which `hero.js` scrolls without changing the hash) still switch the active panel.

**Checks**
- `[data-reveal]` hiding is now wrapped in `@media (scripting: enabled)` (hero.css) and `main.js`
  runs page-level reveal/counters on the home page, so below-hero content is visible with JS off
  and animates with JS on. stylelint + eslint clean; PHPCS (theme) 0 errors/0 warnings; Vite build
  green (CSS 7.91KB gzip, home JS ≈ 6KB gzip — budgets intact); Playwright 6/6 pass incl. axe a11y
  on the home page; one `<h1>`, FAQPage schema present, all bearing anchors resolve.

**Owner action**
- Flag the three strongest case studies as **Featured** and add a couple of **Testimonials**
  (Featured) so the Recent-work carousel and Proof quotes fill with real content.

**Next**
- Task 11: Service pages (the six `/services/<slug>/` pages the home now links to).

### Task 9 — Post types and settings (2026-11-18)
**Done**
- New **Compass Core** plugin (`plugins/compass-core/`, maps to
  `wp-content/plugins/compass-core`) — all content lives in a plugin, not the theme, so it
  survives a theme change. Modular `includes/` files, prefix `compass_core_`, text domain
  `compass-core`.
- **Post types**: `work` (public case studies: single + `/work/` archive), `testimonial`
  (ui-only, not public), `quote_request` (**private** — `public=false`,
  `publicly_queryable=false`, `rewrite=false`, no REST; enquiries never reach the front end).
- **Taxonomies**: `industry` and `service`, hierarchical, attached to Work, `show_in_rest`.
- **Native meta boxes** (no ACF, `includes/meta-boxes.php`): one field-def map drives rendering
  + a nonce'd, per-type sanitised save. Work: client, year, live URL, tech, problem, solution,
  results, gallery, featured. Testimonial: author name, role, rating, URL, featured. Quote
  request: name, email, phone, company, service, budget, message, source, **status**. Meta keys
  `_compass_*`, registered via `register_post_meta()` (REST for all but quote requests).
  Gallery uses the core `wp.media` picker (admin jQuery only; front end stays jQuery-free).
- **Quote Requests admin** (`includes/quote-requests.php`): custom columns (name/email/phone/
  service/status pill), a **status filter** dropdown, and **CSV export** (`admin-post.php`,
  nonce + `edit_others_posts`, respects the active filter), newest first.
- **Site Settings** page (Settings → Site Settings, `includes/settings.php`): phone, WhatsApp,
  email, address, service area, registration number, opening hours, social links — one
  sanitised option array via the Settings API. The theme reads them: the plugin overlays them
  onto the theme's existing `compass_contact_details` filter (header/footer/command-menu/
  WhatsApp update with no theme edit), plus `compass_get_setting()` for direct reads.
- **Seeding**: WP-CLI `wp compass seed [--fresh]` (`includes/class-compass-core-cli.php`),
  wired into `scripts/setup.sh` after plugin activation. Creates 3 Work + 3 testimonials,
  each clearly marked placeholder (`[Sample]` title + `_compass_placeholder` meta), idempotent.
- PHPCS now also scans `plugins/` (text domain `compass-core` registered); `.wp-env.json`
  remapped to `wp-content/plugins/compass-core → ./plugins/compass-core`.

**Decisions**
- Quote requests are a locked-down private CPT rather than a DB table: native admin UI,
  trash/restore and capability checks for free, with zero front-end surface.
- Theme reads settings through the **existing** `compass_contact_details` filter so Task 7's
  nav/footer light up automatically; no theme files changed this task.
- Gallery stored as a CSV of attachment IDs in one meta row (simple, no repeater library).

**Checks**
- PHPCS (theme + plugin) 0 errors; `php -l` clean on all plugin files; Vite build green
  (CSS 6.61KB gzip, home JS 2.93KB gzip — budgets intact; plugin ships no front-end JS/CSS).

**Owner action**
- Fill **Settings → Site Settings** with real contact/registration/social details.
- Replace or delete the `[Sample]` Work and Testimonials before launch (`wp compass seed --fresh`
  removes them).

**Next**
- Task 10: Home page (sections should carry the Task 8 bearing ids; can read featured Work/
  testimonials from these post types).

### Task 8 — Hero and motion (2026-11-18)
**Done**
- **"The Expedition" hero** (`front-page.php`): full-screen dark scene, inline-SVG brass
  compass (ring, 72 ticks, cardinals, needle) ringed by 6 bearings (Be found / Look better /
  Sell smarter / Get an app / Sell online / Welcome travellers). Headline "Find your way
  forward." is real text (LCP), per-word spans; sub-line + Request-a-quote/Explore CTAs.
  Hero exposes `data-waypoint` so the scroll rail lights up.
- **Reusable motion system** (`assets/src/js/motion.js`): scroll-reveal (IntersectionObserver,
  unobserve on reveal), letter reveal (keeps accessible text via `aria-label`), number
  counters, magnetic buttons, cursor glow, ≤3-layer parallax. All no-op under
  prefers-reduced-motion; pointer effects gated to `(pointer: fine)`.
- **Hero behaviour** (`assets/src/js/hero.js`): needle follows pointer via rAF lerp; nearest
  bearing (±30° arc) glows; touch devices get a slow autonomous drift; **device-motion tilt
  is opt-in** behind an "Enable motion" tap (iOS `requestPermission` gesture). Bearing/Explore
  clicks smooth-scroll + focus-handoff to the target id (Task 10 sections).
- **CSS** (`assets/src/css/hero.css`): tokens only; compass geometry, bearings placed with
  container-query units (`cqmin`) on desktop and a 2-col list on mobile; glow states; reveal/
  parallax/glow base + full static reduced-motion equivalent. SVG gradient stops driven by
  `color-mix` (no hard-coded hex).
- **Lazy-loading** (`main.js`): hero.js imported only when `[data-hero]` exists; motion.js only
  when reveal/counter/magnetic/parallax hooks exist — so inner pages never parse hero JS.

**Decisions**
- No GSAP: CSS + WAAPI + rAF cover everything, keeping the budget tiny.
- Bearings link to on-page ids (`#seo`, `#websites`, `#pos`, `#app`, `#ecommerce`, `#travel`);
  Task 10's tailored sections must carry those ids (scroll falls back to native jump until then).
- Hero paints instantly: SVG is `aria-hidden`, headline is real text, no layout-shifting reveal.
- Device tilt stays opt-in per the brief (touch button, off by default).

**Checks**
- Lint (CSS/JS/PHPCS), build and e2e all pass (6/6, axe clean on home, no console errors).
- Budgets: hero JS = hero(1.38) + motion(1.41) ≈ **2.8KB gzip** (< 25KB); homepage JS
  ≈ 5.7KB gzip (< 90KB); CSS **6.56KB gzip** (< 40KB). hero/motion are split, on-demand chunks.
- Verified desktop + 375px in-browser: needle tracks pointer, active bearing glows, mobile
  reflows to a clean list.

**Next**
- Task 9: Post types and settings. Task 10 home sections should expose the bearing ids above.

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

### Task 5 — SEO strategy (2026-11-18)
**Done**
- Rewrote `docs/SEO.md` into the full strategy (docs only, no code): audience + 18 pain
  points in customers' words; URL structure; keyword map (1 primary + 3–5 secondary),
  search intent, and under-limit title/meta for all 15 pages (home, 6 services, 5 industries,
  Work, About, Contact); internal-linking rules; schema plan (Organization, WebSite,
  LocalBusiness, Service, FAQPage, BreadcrumbList, Article, CreativeWork); 20 prioritised
  blog topics; E-E-A-T plan; 90-day content calendar.

**Decisions**
- Base city Colombo; service area all Sri Lanka (city pushes: Colombo, Kandy, Galle, Jaffna) —
  per owner. Local keyword sets use service + Sri Lanka, service + city, industry + service.
- Quote CTA URL is `/get-a-quote/` (matches the page created in Task 2), not the stub's
  `/request-a-quote/`.
- Implementation deferred: schema/titles/metas land in Task 16, local SEO + analytics in Task 17.

**Next**
- Task 6: Design system.
