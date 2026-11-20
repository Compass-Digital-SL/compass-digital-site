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
- [x] 13 Work portfolio
- [x] 14 About/Process/FAQ/Insights
- [x] 15 Quote flow
- [x] 16 Technical SEO
- [x] 17 Local SEO and analytics
- [x] 18 Performance
- [x] 19 Accessibility and QA
- [x] 20 Security and legal
- [x] 21 CD pipeline
- [ ] 22 Go-live
- [ ] 23 Post-launch
- [x] 24 Premium redesign (v2 design system, worldwide positioning)
- [ ] 25 Sinhala language version (future: real /si/ URLs + hreflang, native review)
- [x] 26 Audit fixes after the redesign (see `docs/AUDIT.md` top section)
- [x] 27 AI sales-assistant chatbot (Gemini proxy + lazy vanilla widget)

## Pre-launch audit (2026-11-19)

Full code / security / SEO / performance / features / a11y / UX review and the
outstanding owner actions: **`docs/AUDIT.md`**. Headlines:
- ~~Blocks launch: Task 20 (Security & legal) is not started~~ **Done** — security
  headers + CSP (Report-Only), WP hardening, `security.txt`, and drafted
  Privacy/Terms/Cookie pages. See the Task 20 log entry and `docs/SECURITY.md`.
- Also outstanding: Task 22 (go-live), Task 23 (`docs/GROWTH.md`), the decided
  **worldwide positioning** copy/schema pass, and real Lighthouse numbers
  (never measured locally — Docker unavailable; capture on staging).
- Owner "You do" actions (hosting/domain, GitHub secrets + environments, backup
  bucket, `WP_ENVIRONMENT_TYPE=production`, Site Settings, replace sample
  content, confirm legal pages) are collected in `docs/AUDIT.md` §10 and
  `docs/GO-LIVE.md`.

## Log

### CI site-audit warnings — meta descriptions + heading order (2026-11-20)
**Done** (task/28)
- Cleared the non-blocking audit warnings left after the broken-link fix.
- **Meta descriptions**: added `compass_meta_description()` branches for the quote page
  (`page-get-a-quote.php`) and the three legal pages (privacy / terms / cookie-policy),
  which previously fell through to the empty-tagline fallback. Mirrors the title
  gap-filler's `is_page_template()` detection.
- **Heading order**: `/work/` jumped `h1 → h3` (hero title, then work-card titles). Added a
  visually-hidden `<h2>Case studies</h2>` (`.screen-reader-text`) before the grid so the
  cards' `h3`s nest correctly. No visual change.
- The remaining audit lines are query-string duplicates of these same pages; all resolve
  once the base page is fixed. No errors were present (build was already green).

### Scroll-driven compass watermark (2026-11-20)
**Done** (task/29)
- A faint (~5% opacity) fixed compass, pinned bottom-right and cropped, whose needle
  rotates anti-clockwise in proportion to scroll progress. Rendered on every template
  **except the home page**.
- Files: `template-parts/compass-watermark.php` (inline SVG, `aria-hidden`, decorative),
  `assets/src/css/compass.css`, `initScrollCompass()` in `assets/src/js/motion.js`, wired
  from `main.js` (lazy — only when `[data-compass]` is present), included from `footer.php`
  behind `! is_front_page()`.
- Kept behind content (`.site-main` / `.site-footer` raised to `z-index: 1`) so it never
  lowers text contrast; sticky header untouched. Transform-only, one rAF write per scroll
  frame. Static under `prefers-reduced-motion` and hidden in print. Rotation span tunable
  via `data-compass-turn` (default 540°).

### CI site-audit fix — broken internal links (2026-11-20)
**Done** (PR #31 / #64)
- The CI "SEO crawl" step failed with 6 errors (3 broken internal links): `/cookie-policy/`,
  `/insights/category/websites/`, `/insights/category/pos-payments/`. The 35 warnings
  (missing meta/canonical, `h1→h3` skips, orphans) are non-blocking and were left.
- **Cookie Policy page**: `scripts/setup.sh` seeded Privacy + Terms but not Cookie Policy,
  so the footer link 404'd in CI. Added `create_page 'Cookie Policy'` (template auto-applies
  by slug via `page-cookie-policy.php`).
- **Insights category archives**: `wp compass seed` switches the permalink structure to
  `/insights/%postname%/` in-memory, so the rewrite flush inside that process kept the old
  `/` front and wrote category rules at `/category/<slug>/`. The front end boots with front
  `/insights/`, emitting `/insights/category/<slug>/` links with no matching rule → 404
  (`seo` only survived via a stray 301 guess to `/services/seo/`). Fix: a second
  `wp rewrite flush --hard` in `setup.sh` **after** the seed — a fresh WP-CLI process boots
  from the persisted structure, recomputes the `/insights/` front, and regenerates matching
  category rules.
- **Decision**: fix lives on `task/27` (which already contains all of `task/26`), so PR #31
  carries both; PR #30 superseded and closed.
- `.mockup-tmp/` (gitignored local scratch) removed.

### Task 27 — AI sales-assistant chatbot (2026-11-20)
**Done**
- **Server-side proxy** (the API key never reaches the browser): REST route
  `compass/v1/chat` in Compass Core (`includes/chat.php`). Requires a same-origin
  `wp_rest` nonce, per-IP rate limit via transients (20 msg / 10 min), max message
  length 1000, history capped at 12 turns, rejects non-chat payloads. Returns plain
  text `{reply,state}`; graceful fallback to the quote form on error/quota/no-key.
- **Provider adapter** (`includes/chat-providers.php`) — one module to swap. Default
  Google **Gemini** free tier (`gemini-2.0-flash`). Key read server-side via
  `compass_core_secret('gemini_api_key')` → constant `COMPASS_GEMINI_API_KEY` or
  Site Settings. Swap to Groq/Workers AI by adding one function + the
  `compass_chat_provider` filter.
- **Knowledge** in an editable data file (`data/chat-knowledge.php`, filterable):
  persona/voice from BRAND.md, services, industries, curated FAQ, and a strict
  **pricing stance** (never invents prices — routes money questions to the quote).
- **Widget**: vanilla-JS chat, **lazy-loaded via dynamic import on first open**
  (`theme/assets/src/js/chat.js` + `css/chat.css` → their own Vite chunk, 2.2 KB +
  1.6 KB gz). Initial budgets untouched (main JS 5.3 KB gz, main CSS 15.3 KB gz).
  Built from design tokens only (glass panel, Fraunces heading, teal accent, pill
  buttons), focus-trapped, ESC to close, honours `prefers-reduced-motion`, WCAG 2.2 AA.
- **Consent + privacy**: launcher appears only after cookie consent (`consent.js`
  now fires `compass:consent-granted`); privacy page gained an "AI chat assistant"
  section noting messages are processed by Google (Gemini).

**Decisions**
- Gating chat on the existing analytics-cookie consent (simplest honest gate; chat
  sends messages off-site to Google, so it is opt-in like analytics).
- Launcher markup + its tiny CSS are printed by PHP (token-based inline, like the
  consent bar) so only the heavy panel is deferred; nothing ships on load but a button.
- Model output is escaped then auto-linked client-side; only our own error copy is
  trusted HTML. Safety thresholds BLOCK_ONLY_HIGH, max 400 output tokens.

**Next / owner action**
- Set the Gemini key: add `define('COMPASS_GEMINI_API_KEY','…');` to wp-config.php
  (preferred) or paste it in Settings → Compass → AI assistant. Free key from Google
  AI Studio. Until set, the assistant and its launcher stay hidden.
- Confirm the free-tier limits suit expected traffic; tune the per-IP rate limit in
  `includes/chat.php` if needed. Review `data/chat-knowledge.php` wording.

### Task 26 — Audit fixes after the redesign (2026-11-20)
**Done** (full list in `docs/AUDIT.md`, "Re-audit" section)
- Main-menu 404s fixed (4 wrong slugs in `inc/nav.php`, plus the seeded article).
- Login-name leak via `?author=N` → `/author/<login>/` closed; author archives off
  for guests.
- Critical CSS now built to `assets/dist/critical.css` (minified) — the deploy
  excluded `assets/src`, so production would have lost the inlined tokens.
- `no-store` on contact/quote/thank-you (form nonce vs page cache).
- Worldwide `contactPoint`, proper titles for every page, GA4 CSP wildcards,
  `interest-cohort` and `X-Powered-By` removed, comments closed.
- CI runs `npm run seo:audit` (broken links fail the build); crawler decodes `&#038;`.
- **PR #29 CI fix**: phpcs failed on two one-line keyed arrays (`front-page.php`,
  `header/branding.php`); fixed on `task/24-premium-redesign`.
- **Load performance** (owner's DevTools Lighthouse showed `NO_FCP`: that run was a
  background tab; headless Lighthouse paints at 1.0 s). The real problem it hid:
  when the deferred main stylesheet landed, every element with a `transition`
  animated from its critical-CSS style to its final style (hundreds of layout
  transitions). Now `html.css-pending` disables transitions until the sheet has
  applied. Header scroll handler reads layout before writing and no longer reads it
  at start-up; consent.js is `defer`. Mobile Lighthouse (local) 0.71 → 0.90–0.95,
  TBT 860 → ~190 ms, LCP 2.1 → 1.1 s, CLS 0.
- **Pain-selector CLS**: the pain section shipped all panels stacked for no-JS,
  then JS added `.is-enhanced` + `[hidden]` after load, collapsing a tall column
  to one panel (a large post-load shift Lighthouse mis-attributed to the web font).
  The collapsed one-panel layout is now also applied at first paint via `html.js`
  (flag set in the pre-paint head script), so JS-on never shifts; JS-off still
  shows every panel. CSS-only + one classList line; no change to `ui.js`.

**Decisions**
- Fallback titles live in `inc/seo.php` at priority 20 and only fill gaps left by
  the section filters.
- Form pages send `no-store` from the theme rather than relying on the host's
  cache exclusions alone.
- The transition guard is JS-scoped (class set by the inline head script), so
  JS-off visitors keep transitions; window `load` is the fallback that clears it.
- Left as a design call: the hero's ever-spinning SVG rings and long entrance
  fades keep Speed Index noisy (4–7 s locally); local TTFB is 2–4 s (wp-env with
  WP_DEBUG), so staging numbers will differ.

**Next**
- Merge Task 24 then this branch; Task 22 (Go-live). Design call on the 360px hero
  needle overlap and the phone cookie card.

### Task 24 — Premium redesign (2026-11-20)
**Done**
- **Design system v2** (`tokens.css`, `docs/DESIGN.md`): near-black dark-first theme
  plus a light theme (system preference or header toggle), teal accent, big light
  Fraunces headings, hairline structure. Every text pair measured AA in both themes.
- **Visitor controls**: theme toggle and a tiny accent picker (6 presets, each shade
  auto-adjusted to AA), remembered in the visitor's browser and re-applied before first
  paint (`inc/theme-mode.php`, `js/theme-mode.js`).
- **Header/footer**: floating glass island header (hides on scroll down), quiet
  Ctrl/⌘ K search pill, theme-aware wordmark from the official logo files, footer
  sitemap without a street address, bottom scroll-progress bar, right-middle section
  rail, cookie bar as a floating card.
- **Home**: approved mockup built 1:1 — editorial hero with a pointer-following compass
  needle, pain selector (list + sticky panel), numbered services list, industry tiles,
  process, promise + guarantees, "Where we work" (visitor's local time), split FAQ, big
  CTA. Replaces the Task 8 "Expedition" hero.
- **Inner pages**: shared page header restyled; every template inherits the new
  components; 404 redesigned; card image placeholders; duplicate breadcrumbs removed.
- **Worldwide positioning**: Sri Lanka / Colombo targeting removed from all copy, meta
  titles/descriptions and share images; schema `areaServed` = Worldwide. Legal pages
  keep their Sri Lankan law references; the registered address stays in schema only.
- **Favicon** (`inc/favicon.php`, `assets/brand/favicon.{svg,ico}`, `apple-touch-icon.png`):
  the compass mark on a white disc with an ink ring, clear on light and dark tab bars;
  also served at `/favicon.ico`.
- **Fixes found on the way**: hidden menus overriding `[hidden]`, consent accept button
  contrast, quote form stealing focus on load, 320px reflow on Contact.
- Checks: stylelint + eslint clean; build OK (CSS 15.2KB gz, main JS 5KB gz, home HTML
  22KB gz); Playwright + axe all 84 Chromium tests pass; no PHP notices on any page
  (checked via Novamira). Firefox/WebKit could not launch locally (CI runs them);
  phpcs is blocked by the local shell policy (CI runs it).

**Decisions**
- The theme wordmark takes priority over the Customizer logo (a single uploaded image
  cannot switch between dark and light).
- No italic Fraunces face is shipped; heading emphasis is teal and upright. Adding the
  italic file later is a one-file change (`assets/fonts/README.md`).
- Hero spec numbers replace the duplicate counters in the Proof section.
- The theme favicon replaces any Customizer Site Icon (the uploaded full wordmark is
  unreadable at 16px). Favicon needle red is `#D7191F`, a deeper take on the logo red
  for a more formal look and better contrast on white.
- Sinhala is deferred to Task 25 (needs real `/si/` pages, not a text swap).

**Owner must do**
- Delete the placeholder text "This will be the landing page" from the Home page in the
  editor (it renders under the home sections).
- Optional: clear the Site Icon in Customizer → Site Identity (it is no longer used).
- Decide the quote form budget currency (still LKR) for worldwide clients.
- Replace the sample insights/case studies (they still mention Sri Lanka) before launch.

**Next**
- Task 22 (Go-live) as planned; Task 25 when the Sinhala copy is reviewed.

### Fix — CI + deploy green after Task 20 (2026-11-20)
**Done**
- **Deploy workflow rejected at parse time** (0 s runs): `environment.url` used
  `secrets.*`, which GitHub forbids there. Now `vars.STAGING_URL` /
  `vars.PRODUCTION_URL` (documented in `docs/SECRETS.md`).
- **Deploy jobs gated on those repo variables**: no hosting yet, so staging /
  production jobs are skipped (not failed) until `STAGING_URL` /
  `PRODUCTION_URL` exist. Build still runs on every push to main.
- **CSP console errors** (all smoke "no console errors" tests): a Report-Only
  policy with no reporting endpoint logs an error in Chromium/WebKit. Added
  `report-uri` + `report-to csp` + `Reporting-Endpoints` header pointing at a new
  `POST /wp-json/compass/v1/csp-report` route (logs one capped line to the PHP
  error log, returns 204). `upgrade-insecure-requests` is now sent only when
  `COMPASS_CSP_ENFORCE` is on (it is ignored, with an error, in Report-Only).
- **Flaky axe `color-contrast`** (mostly WebKit): axe sampled text mid
  scroll-reveal fade (foreground ≈ background). New `tests/e2e/helpers.js`
  `settleMotion()` waits for finite animations/transitions before axe; both
  specs share `axeBlocking()`. Real palette contrast is unchanged and passes.

**Decisions**
- Report endpoint logs to `error_log` only — no DB table, nothing to clean up.

**Next**
- Owner, after buying hosting: add `STAGING_URL` / `PRODUCTION_URL` as
  repository variables (this switches deploys on) plus the SSH/path secrets.
- Review `CSP report:` lines in the staging error log before enforcing CSP.

### Task 20 — Security and legal (2026-11-19)
**Done**
- **`mu-plugins/compass-security.php`** (must-use, survives theme/plugin swaps):
  - Security headers on every front-end response — `X-Content-Type-Options`,
    `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy`, `Permissions-Policy`
    (geo/mic/cam/payment/USB/FLoC off), `Cross-Origin-Opener-Policy`, and
    **HSTS only over TLS**.
  - **CSP in Report-Only first** (`Content-Security-Policy-Report-Only`), allowing
    self + GA4 + Turnstile; filterable (`compass_csp_directives` /
    `compass_security_headers`). Flip to enforcing with
    `define( 'COMPASS_CSP_ENFORCE', true )` after a clean staging run.
  - **XML-RPC off** + pingback methods/header removed; **`DISALLOW_FILE_EDIT`**.
  - **User-enumeration blocked** — `?author=N` redirect for guests, REST
    `/wp/v2/users` removed for unauthenticated, oEmbed author stripped.
  - **Login throttle** (5 fails / IP / 15 min) + generic login error.
  - **`security.txt`** at `/.well-known/security.txt` (+ `/security.txt`), RFC 9116,
    self-refreshing `Expires`.
- **Legal pages, marked DRAFT for the owner's legal review**, matching what the
  site actually collects (quote/contact form, GA4 after consent, email, spam
  defences): `theme/page-privacy.php`, `theme/page-terms.php`,
  `theme/page-cookie-policy.php` (+ `assets/src/css/legal.css`). Footer Legal
  column now links the Cookie Policy. Placeholders (`[…]`) for registration
  details, retention, rights/complaint route and governing law.
- **`docs/SECURITY.md`** — headers rationale, CSP-tuning path, **least-privilege
  roles**, **update policy**, and the owner server/account checklist.
- **Verified (not changed):** the quote/contact form is already nonce-verified,
  fully sanitised/escaped, honeypot + time-trap + rate-limit + optional Turnstile,
  saved to a private CPT (Task 15) — no rework needed.

**Decisions**
- Hardening in **mu-plugins**, not the theme, so it cannot be switched off and
  survives a theme change (per the pre-launch audit recommendation).
- **CSP Report-Only first** with a temporary `'unsafe-inline'` for the inline
  blocks (critical CSS, consent, JSON-LD, speculation rules); nonce + enforce is
  the documented next step, not this task.
- `DISALLOW_FILE_EDIT` defined in code (travels with the repo) rather than left to
  wp-config.
- Legal pages as **page templates** (slug-based, like the other pages) so the
  owner creates empty Pages and assigns the template; copy lives in the repo.

**Owner must do** — see `docs/SECURITY.md`:
- Create Pages `privacy` / `terms` / `cookie-policy` and assign the templates;
  **read, confirm and complete** the `[…]` placeholders; have them legally
  reviewed.
- Serve over HTTPS, enable 2FA on the single admin, keep one Administrator, watch
  CSP Report-Only on staging then enforce, verify headers on the live URL.

**Next**
- Task 22 (Go-live): work `docs/GO-LIVE.md`, first staging deploy, tag `v1.0.0`.

### Task 21 — Continuous deployment (2026-11-19)
**Done**
- **`.github/workflows/deploy.yml`** — push to `main` → **staging** (auto); push a
  `v*.*.*` tag → **production** gated by the `production` GitHub Environment
  (required reviewer = manual approval). One `build` job assembles the code-only
  release (theme + `plugins/compass-core` + `mu-plugins`, excludes `node_modules`,
  `assets/src`, `.vite`) and both environments deploy the **same artifact**.
- **`.github/actions/ssh-deploy`** (composite) — rsync the release to
  `releases/<ts-sha>`, flip the `current` symlink atomically, `wp cache flush` +
  `rewrite flush`, smoke-test (`/` and `/get-a-quote/` return 200/3xx), run
  Lighthouse (non-blocking), **auto-rollback** to the previous release on smoke
  failure, and prune to the last **5** releases. Uploads and DB are never in the
  artifact, so nothing but code moves.
- **`.github/workflows/backup.yml`** — daily (02:15 UTC) + manual offsite backup:
  `wp db export` (gzip) and an `uploads` tarball pushed to S3-compatible storage,
  30-day retention. Read-only against the server.
- **`docs/SECRETS.md`** — every secret **name** (SSH_HOST/USER/KEY/KNOWN_HOSTS,
  STAGING_/PRODUCTION_ DEPLOY_PATH/WP_PATH/URL, TURNSTILE_*, SMTP_*, BACKUP_S3_*)
  with purpose + where to add it; no values. **`docs/RESTORE.md`** — rollback +
  DB/uploads restore guide and a restore-drill reminder. Owner action items added
  to **`docs/GO-LIVE.md`** (Deployment pipeline + Backups).

**Decisions**
- **Symlink releases** (not in-place rsync) so rollback is an instant re-point and
  the last 5 builds stay on disk. Web docroot must point at `<DEPLOY_PATH>/current`.
- **Backups via a GitHub-scheduled workflow** as the dependable fallback; a
  host-level cron is offered as optional belt-and-braces in GO-LIVE.
- **Staging privacy** (noindex + Basic Auth) is a server/host setting, not code —
  documented, not automated, since it depends on the host control panel.
- Lighthouse on deploy is **non-blocking** (reports, never fails the deploy); the
  smoke test is the hard gate that triggers rollback.

**Owner must do (after buying hosting)** — see `docs/SECRETS.md` + `docs/GO-LIVE.md`:
add every secret in GitHub, create the `staging`/`production` Environments (required
reviewer on production), set up the deploy SSH key + `releases/` dir + `current`
docroot, and create the offsite backup bucket.

**Next**
- Task 22 (Go-live): work the `docs/GO-LIVE.md` checklist, first real deploy, tag `v1.0.0`.

### Task 19 — Accessibility, UX QA and device testing (2026-11-19)
**Done**
- **Full WCAG 2.2 AA audit** across every page (home, services hub + service, industries
  hub + industry, work archive, about, process, FAQ, insights index + post, quote, contact,
  thank-you, 404). Keyboard path, visible focus, screen-reader labels, contrast (already
  measured in `docs/DESIGN.md`), form errors, reduced-motion, touch targets and 200 % zoom.
- **Cross-browser + responsive Playwright suite** (`tests/e2e/a11y.spec.js`): added
  **Firefox and WebKit** projects to `playwright.config.js` (was Chromium-only) and a new
  spec that runs on all three — axe (critical/serious) at **360 px and 768 px** on every
  page, **no horizontal overflow at 320 px** (reflow, WCAG 1.4.10), **200 % zoom** reflow,
  the **skip link**, **visible keyboard focus** on header nav + command menu, **≥44 px**
  mobile-bar touch targets, and **reduced-motion** rendering with no console errors.
- **CI now installs all three browsers** (`.github/workflows/ci.yml`:
  `playwright install --with-deps chromium firefox webkit`) so the cross-browser suite runs
  on every PR.
- **One real fix:** the command-menu search input removed its focus outline with no
  replacement — added a `:focus-within` brass ring on `.command-menu__search`
  (`theme/assets/src/css/nav.css`) so keyboard users always see focus (WCAG 2.4.7 / 2.4.11).
- **UX review:** home passes the 5-second test (real-text H1 as LCP, clear lede, six labelled
  service bearings, primary "Get a quote" CTA); every service is reachable in ≤3 clicks
  (header nav / home bearings / mobile bar → hub → service); **no page is a dead end** —
  each ends with a next-step CTA (service/industry/process/FAQ/services → "Request a quote",
  contact → the form + WhatsApp, 404 → back-home + search).

**Results**
- **Chromium + WebKit: all green** locally (smoke + axe + the new a11y/responsive suite).
- **Firefox:** the config + CI cover it, but it **could not launch on this Windows dev box**
  (`browserType.launch: spawn UNKNOWN`, persists after `playwright install --force`) — an
  environment/AV block, not a site issue. It runs in CI (Linux).

**Decisions**
- Kept the responsive axe matrix to **360/768 px** in the new spec; the existing smoke suite
  already covers the default desktop width, so 1440 isn't duplicated.
- Skip-link and focus tests use `.focus()` + polling rather than a `Tab` press: WebKit does
  not Tab to links without full-keyboard-access, and the reveal has a CSS transition to settle.

**Next**
- Task 20 (Security + legal): WP hardening, security headers, legal pages.

### Task 18 — Performance (2026-11-19)
**Done**
- **Zero render-blocking CSS on first paint** (`theme/inc/assets.php`): design tokens
  (`tokens.css`) + a slim hand-authored critical layer (`theme/assets/critical.css` — base,
  header, hero) are **inlined in `<head>`**; the **@font-face rules are inlined** with absolute
  woff2 URLs (the separate `fonts.css` request is gone); the **full stylesheet now loads
  non-render-blocking** via `rel=preload` + `onload` rel-swap with a `<noscript>` fallback. The LCP
  font + the two variable fonts stay preloaded (`inc/fonts.php`).
- **Speculation Rules prefetch** (`theme/inc/perf.php`): same-origin document links are prefetched on
  hover / pointer-down (moderate eagerness), excluding `wp-admin`, `wp-login`, `rel="nofollow"` and
  `data-no-prefetch`. Prefetch only (no prerender) so analytics never double-counts.
- **Image pipeline** (`inc/perf.php`): WebP/AVIF uploads allowed, image quality pinned to 82. Work /
  Insights cards already ship `loading="lazy"` + `decoding="async"`; WordPress core supplies
  `width`/`height` + `srcset`/`sizes` for library images, and the inlined critical CSS reserves the
  hero height so the deferred sheet cannot shift it.
- **Long-cache headers** (`theme/assets/.htaccess`): hashed JS/CSS, fonts and images cached
  `public, max-age=31536000, immutable`, with `mod_deflate` as a compression fallback. HTML cache
  rule + the nginx equivalent documented in `docs/GO-LIVE.md` (server-side, Task 21/22).
- **CI performance budgets that fail the build** (`lighthouserc.json` + `budget.json`): LHCI runs 5 key
  URLs (home, services, work, get-a-quote, contact) on **throttled 4G mobile**, 3 runs, median, and
  **errors** on perf score < 0.95, LCP > 1.8 s, CLS > 0.05 or TBT > 200 ms; transfer budgets cap JS
  90 KB / CSS 40 KB / fonts 180 KB and third-party count. The CI `e2e` job already calls `npm run lhci`.
- Lint (ESLint, Stylelint), the Vite build and PHPCS are clean on changed files.

**Before → after**
- **Render-blocking stylesheets in `<head>`: 2 → 0** (was `compass-main` + `fonts.css`; now both
  inlined/deferred).
- **Asset budgets (unchanged bytes, now enforced):** homepage **CSS 11.08 KB gzip** (budget 40 KB) and
  **JS ≈ 6.1 KB gzip** — `main` 3.34 + `hero` 1.38 + `motion` 1.41 (budget 90 KB). Both pass with large
  headroom; JS was already per-feature code-split (`js/main.js`), so no unused JS per template.
- **Runtime metrics (Lighthouse score, LCP, CLS, TBT)** are produced by the CI `e2e` job (wp-env +
  LHCI); they were **not measured locally** — Docker/wp-env is unavailable in this sandbox, same
  constraint noted in Tasks 16–17.

**Decisions**
- **Inlined critical CSS is hand-authored and lean, not per-template-extracted.** The whole bundle is
  only 11 KB gzip and largely shared across templates, so a `critical`/`penthouse` extraction step
  (headless browser + running site at build time, plus a heavy dev dep) wasn't worth it. Inlining
  tokens + a base/header/hero layer and deferring the rest removes the render-blocking requests with
  no new tooling. `unused-css-rules` is a **warn** in LHCI to flag regressions without blocking.
- **Prefetch, not prerender**, in Speculation Rules — prerender executes page JS and would fire GA4 /
  consent logic on pages the visitor may never open.
- **Budgets live in CI**, not just docs: `lighthouserc.json` thresholds mirror CLAUDE.md rule 5 exactly,
  so a regression fails the PR.

**Next**
- Task 19 (Accessibility + QA): run the full LHCI + axe suite against the live wp-env once Docker is
  available, confirm the perf budgets pass green, and capture the real LCP/CLS/score numbers.

### Task 17 — Local SEO + analytics (2026-11-19)
**Done**
- **Site Settings extended** (`plugins/compass-core/includes/settings.php`): two new sections —
  **Local & maps** (Google Maps link, Google reviews link, latitude/longitude, average rating,
  review count) and **Analytics & verification** (GA4 Measurement ID, Search Console token). IDs are
  entered by the owner, never hard-coded; GA4/GSC can alternatively be PHP constants
  (`COMPASS_GA4_ID`, `COMPASS_GSC_VERIFICATION`). Sanitised on save (GA4 uppercased, GSC meta-wrapper
  stripped to a token, geo/rating kept numeric).
- **Analytics + consent** (`plugins/compass-core/includes/analytics.php` + `assets/consent.js`):
  Search Console verification is a plain meta tag (no cookies). **GA4 loads only after the visitor
  accepts cookies AND the page is idle** (`requestIdleCallback`), never blocking first paint; with no
  consent nothing loads and every event is a silent no-op. A **minimal cookie consent bar** (no
  library, ~2.4 KB) with Accept/Decline, token-styled, plus a **"Cookie preferences"** link in the
  footer that reopens it. Choice stored in `localStorage`.
- **Event tracking** (via `window.compassTrack`): `quote_submitted` (thank-you page), `whatsapp_click`,
  `phone_click`, `service_view` (with the service slug), `command_menu_used` (observed on the command
  menu opening). Page-level events are decided in PHP and fired once after consent.
- **Local content** (`theme/template-parts/home/local-area.php` + `local.css`): a service-area block
  on the home page — Colombo base, Kandy/Galle/Jaffna + island-wide, NAP pulled from Site Settings so
  it stays consistent everywhere, an **embed-free "Open in Google Maps" link** (no heavy iframe), and
  a **reviews** block ready for the real Google reviews link. The home proof section gains a
  "Read more reviews on Google" link when the reviews URL is set.
- **LocalBusiness schema finalised** (`theme/inc/seo.php`): street address, `geo` coordinates,
  `hasMap`, and `aggregateRating` are added from Site Settings when present (rating only with real
  figures). Opening hours kept out of schema — owner free-text doesn't match schema.org syntax.
- **`docs/LOCAL-SEO.md`**: donkey-level, step-by-step owner guide for Google Business Profile, Bing
  Places, reviews and local directories, plus a table of which setting powers what.
- ESLint, Stylelint and the Vite build are clean (CSS 11.1 KB gzip; consent.js served separately,
  outside the bundle). PHPCS clean on new files (CI ignores warnings; local CRLF is an autocrlf
  artifact).

**Decisions**
- **Consent + analytics live in the plugin, not the theme**, so they survive a theme change (a
  compliance/data concern), and so the GA4 ID (plugin-owned setting) stays with its loader. The bar is
  styled with the theme's design tokens via CSS variables — no hard-coded colours, no theme rebuild.
- **No analytics library and no gtag until consent + idle** to protect the performance budget; the
  consent script is vanilla and self-contained.
- **Event binding is DOM-based** (delegated click listeners + a MutationObserver on the command menu),
  so it needs no changes to the theme's Vite bundle.

**Next (owner, after launch):** follow `docs/LOCAL-SEO.md` — create/verify Google Business Profile,
paste the Maps + reviews links and GA4/GSC IDs into Site Settings, set up Bing Places and directories.

### Task 16 — Technical SEO + schema (2026-11-19)
**Done**
- **One SEO engine** (`theme/inc/seo.php`): a single source of truth for the document head, so
  there are no duplicate tags. It centralises the **meta description** (the per-section printers in
  services/industries/insights/work/company are unhooked on `wp_loaded`; the title filters stay),
  and emits **canonical URLs** (pagination-aware), **Open Graph** + **X (Twitter) cards**,
  `article:published/modified_time`, and the identity **schema** graph.
- **Share images**: `scripts/og/generate-og.mjs` (sharp) renders 8 branded 1200×630 PNGs into
  `theme/assets/og/` (home + per section + default); `npm run og:generate`. The head maps each
  request to the right image and uses a post/case-study featured image when present.
- **Schema graph** (JSON-LD, no duplicates): identity nodes **Organization + WebSite
  (SearchAction) + LocalBusiness/ProfessionalService** in the head on Home, LocalBusiness on
  Contact, with stable `@id` anchors; per-page **Service, FAQPage, BreadcrumbList, Article,
  CreativeWork** stay in the templates (already present).
- **Indexability by environment**: `wp_robots` outputs **noindex,nofollow** site-wide on
  local/staging (`wp_get_environment_type() !== 'production'`) and `max-image-preview:large` on
  production; search, 404, author/date archives and paginated subpages are noindex everywhere.
  `robots.txt` allows crawl + advertises `/wp-sitemap.xml` on production and `Disallow: /` elsewhere.
- **Sitemap**: core `/wp-sitemap.xml`, trimmed — no author sitemap, private `quote_request`
  excluded; styleguide and thank-you already self-exclude.
- **Redirects + trailing slashes**: a version-controlled `compass_redirects()` 301 map applied on
  `template_redirect` (filterable, survives a plugin toggle); core `redirect_canonical` enforces the
  single trailing-slash form for `/%postname%/`.
- **Rank Math**: `docs/seo/rank-math-settings.json` baseline turns off every overlapping module
  (Schema, Sitemap, Breadcrumbs, meta description) so the plugin, if used, never duplicates the
  theme; kept for Redirections / 404 / Search Console. See `docs/seo/README.md`.
- **Audit**: `scripts/seo/audit.mjs` (`npm run seo:audit`) crawls the running local site and reports
  broken links, missing/empty titles, missing or duplicate H1, heading-hierarchy skips, images with
  no `alt`, missing canonical/description, and orphan pages; exits non-zero on errors.
- PHPCS, ESLint, Stylelint and the Vite build are clean; OG images generated and committed.

**Decisions**
- **Theme owns SEO, not the plugin.** The theme already emitted per-section titles/meta/schema;
  Task 16 generalised the head into one engine rather than handing output to Rank Math, so SEO is
  version-controlled and works with zero plugins. Rank Math's overlapping modules are disabled by
  the exported config (one owner — no duplicate tags).
- **Share images are pre-generated PNGs**, committed to the repo, so production needs no render
  toolchain; `sharp` is a dev-only dependency used by the generator.
- **Indexability keys off `WP_ENVIRONMENT_TYPE`**, so staging/local can never leak into the index
  and production is explicit.

**Next**
- Task 17 (Local SEO + analytics): finalise LocalBusiness geo/opening hours, Google Business Profile
  + Search Console, analytics events. Run `npm run seo:audit` against the live site once Docker is
  available (not runnable in this sandbox) to confirm zero broken links / missing alt.
- **Go-live actions** (set `WP_ENVIRONMENT_TYPE=production`, add real NAP + social, validate schema,
  submit sitemap) are server-side, so they are parked in **`docs/GO-LIVE.md`** for Tasks 21 and 22.

### Task 15 — Quote flow + Contact (2026-11-18)
**Done**
- **Quote form** (`theme/page-get-a-quote.php` + shared part `theme/template-parts/quote-form.php`):
  a friendly **3-step** request — (1) services *multi-select* + industry, (2) message, timeline,
  optional budget, (3) name, email, phone/WhatsApp, preferred contact, consent. It is **one real
  `<form>` with every field in the DOM**, so it works fully **without JavaScript** as one long form;
  `quote.js` (lazy, ~0.7KB gz, loaded only where the form exists) layers the stepper + inline
  validation on top. **Pre-fills from URL params** — the singular `?service=<name>` used by every
  site-wide CTA pre-checks the matching service, and `?industry=` pre-selects.
- **Server handler** (`plugins/compass-core/includes/quote-handler.php`, posts to `admin-post.php`):
  validates every field server-side; on failure **flashes errors + the visitor's own input** via a
  short transient and redirects back with an accessible error summary (no typing lost); on success
  saves a private **`quote_request`** (`_compass_*` meta, services joined, status `new`), emails the
  owner a clean summary + sends the visitor an auto-reply, then redirects to **`/thank-you/`**.
- **Spam defence**: honeypot (silent drop), min-time trap (<2s), per-IP rate limit (5 / 15 min), and
  **Cloudflare Turnstile** — site/secret keys from Site Settings *or* PHP constants, **skipped
  gracefully when unset**, so it works now and activates when keys are added (Task 21).
- **Contact page** (`theme/page-contact.php`): the same form (marked source `contact`, lands in the
  same Quote Requests), plus business details, a **pre-filled WhatsApp link** and a clear
  **one-business-day** response promise. Emits ContactPage + BreadcrumbList JSON-LD.
- **Thank-you page** (`theme/page-thank-you.php`, noindex): confirmation + next steps + a
  `generate_lead` analytics event (dataLayer + gtag-safe) for Task 17. Seeded with its template by
  `wp compass seed` (`seed_quote_pages()`), which also assigns templates to Get-a-quote / Contact.
- **Settings** (`includes/settings.php`): new **Quote & email** section — notification recipient,
  Turnstile site/secret, and SMTP host/port/user/pass/encryption/from. Each value is overridable by a
  `COMPASS_*` constant (env/wp-config). SMTP is wired via `phpmailer_init`, used only when a host is set.
- **Admin**: Quote Requests meta box + CSV export now also carry Industry, Timeline and Preferred
  contact.
- **Verified on the live local server**: form renders, URL pre-fill works, a full submission returns
  `302 → /thank-you/?sent=1` (saves first), a bad submission bounces back with the error flash, and the
  honeypot drops silently. `/get-a-quote/`, `/contact/`, `/thank-you/` + a JS-on/JS-off submission test
  added to the Playwright smoke list. Stylelint, ESLint, Vite build and PHPCS clean (CSS **10.9KB gz**,
  budget 40KB; `quote.js` a separate **0.67KB gz** chunk).

**Decisions**
- The handler lives in **compass-core**, not the theme, so enquiries survive a theme change — matching
  the existing `quote_request` CPT. The theme owns only presentation + the option lists.
- Failed submits use a **random-token transient flash** (errors + input) rather than a session, so the
  form replays accessibly with no data loss and no reliance on cookies.
- Turnstile + SMTP **degrade to off** when unconfigured, so the whole flow is testable before the owner
  supplies keys in Task 21 — no hard dependency on external services.

**Next**
- Task 16: Technical SEO.
- **Owner (Task 21)**: add Turnstile keys + SMTP details in **Settings → Site Settings → Quote & email**
  (or as `COMPASS_*` constants). After deploy, run `npm run wp:seed` once so `/thank-you/` exists.

### Task 14 — About / Process / FAQ / Insights (2026-11-18)
**Done**
- **About** (`theme/page-about.php`, `Template Name: About`, slug `about`): honest origin
  story, mission + vision, the four brand values, and **team placeholders** the owner swaps
  for real people. Emits **AboutPage** + BreadcrumbList JSON-LD.
- **Process** (`theme/page-process.php`, `Template Name: Process`): the four steps (Listen,
  Plan, Build, Support) **in depth**, **what we need from the client** at each, and **timelines
  in ranges** (per step + a summary band). Emits **HowTo** + BreadcrumbList JSON-LD.
- **FAQ** (`theme/page-faq.php`, `Template Name: FAQ`): global FAQ in **grouped, accessible
  accordions** (reuses the `.accordion` component + `ui.js`). Answers are in the DOM without
  JS, so **FAQPage** JSON-LD matches visible content.
- **Insights blog**: index at **`/insights/`** (`theme/home.php`, the static Posts page) with
  **category filter chips** — real links to the category archives (work with JS off, shareable),
  enhanced by `insights.js` to filter cards in place via the History API. Secondary category/tag
  archives reuse `theme/archive.php`. Posts live at **`/insights/<slug>/`**.
- **Single post** (`theme/single.php`): **reading time**, a **table of contents** built from the
  post's own h2/h3 (anchor ids injected server-side; `insights.js` adds scroll-spy), **author box**,
  **related posts** by category, **share links** (plain WhatsApp / Facebook / LinkedIn / X / email
  URLs — no third-party scripts — plus a JS copy-link button), and a **soft quote CTA**. Emits
  **BlogPosting** + BreadcrumbList JSON-LD.
- **Content source** `theme/inc/company.php` (About/Process/FAQ copy + titles/metas + schema
  helpers) and `theme/inc/insights.php` (reading time, TOC, related, share, Article schema,
  blog titles/metas). Shared **post card** `theme/template-parts/post-card.php` (index + archives).
- **Three starter articles** (1000+ words each, in brand voice, with money-page links), seeded via
  `wp compass seed`: *How much does a website cost in Sri Lanka?* (→ Website), *How to get your
  business found on Google in Sri Lanka* (→ SEO), *Choosing a POS system for a Sri Lankan shop*
  (→ POS). Top three of the SEO content list.
- **Nav/footer** already linked About + Insights (Task 7), so no orphans.
- **Tests**: `/about/`, `/process/`, `/faq/`, `/insights/` and a single post added to the Playwright
  smoke list — **28/28 pass** (no console errors, no critical/serious axe violations). CSS build
  **56.5KB raw / 10.3KB gz** (budget 40KB gz); `insights.js` is a **1.0KB gz** lazy chunk. Stylelint,
  ESLint and Vite build clean; PHPCS clean on all new files (local CRLF sniff excluded, as before).

**Decisions**
- The Insights section uses a **static Posts page** (`show_on_front=page`, a `home` page →
  `front-page.php`, an `insights` page → `page_for_posts`) plus permalink `/insights/%postname%/`,
  all set idempotently by the seeder — so `/insights/` is the index and posts sit under it, matching
  `docs/SEO.md`. Front-page behaviour is unchanged (still `front-page.php`).
- Share links are **plain URLs**, not an SDK, to keep the JS budget and privacy intact; only the
  copy-link button is enhanced.
- Followed the services/work pattern: one theme-driven copy source per section, JSON-LD generated
  from the same copy, templates hold no body content. Title/meta/schema stays scoped here and
  unhooks when **Task 16** generalises it site-wide.

**Next**
- Task 15: Quote flow.

### Task 13 — Work portfolio (2026-11-18)
**Done**
- **/work/ archive** (`theme/archive-work.php`, the `work` post-type archive) — a
  filterable grid of case studies with **filter chips by Service and by Industry**.
  Chips are real links carrying `?service=` / `?industry=` args: the list is filtered
  **server-side** (`compass_work_filter_archive()` in `theme/inc/work.php`,
  `pre_get_posts`), so every filtered URL is **shareable and works with JS off**.
  `theme/assets/src/js/work-filter.js` (lazy-loaded, ~0.7KB gz) enhances the chips to
  filter the cards **in place** and keep the URL in sync via the **History API**.
- **Single case study** (`theme/single-work.php`): problem → **approach** → solution →
  **results (numbers)** → **tech** → **gallery** (lazy, `decoding=async`, width/height set,
  serves the uploaded WebP/AVIF) → **testimonial** → **next project** link → **quote CTA**
  (pre-filled with the case study's service). Emits **CreativeWork** + BreadcrumbList JSON-LD
  (`compass_work_schema()`), with the testimonial as a nested `Review`.
- **Shared card component** `theme/template-parts/work-card.php` — the home "Recent work"
  preview (`template-parts/home/work.php`) and the archive now render the **same** card;
  its CSS moved to a new `theme/assets/src/css/work.css`. Card carries `data-services` /
  `data-industries` for client-side filtering.
- **Admin, no ACF**: added **Approach**, **Testimonial quote** and **Testimonial author**
  fields to the native Work meta box (`plugins/compass-core/includes/meta-boxes.php`); a case
  study is one editor screen (title, excerpt, featured image, the detail fields, Industry +
  Service terms → Publish). The README has a step-by-step "Add a case study" guide.
- **Breadcrumbs**: added `work` single + archive branches to the shared part. The three
  `[Sample]` seeded case studies were enriched (approach + quote) so the demo is complete.
- **Tests**: `/work/` and `/work/?service=seo` added to the Playwright smoke list. CSS
  **46.5KB raw / 9.1KB gz** (budget 40KB gz); the filter JS is a separate chunk loaded only
  on /work/. Stylelint, ESLint and Vite build clean; PHPCS clean on all new/changed files
  (local CRLF sniff excluded — committed blobs are LF, CI runs on Linux).

**Decisions**
- Used the existing `work` CPT + `industry`/`service` taxonomies and the native meta boxes
  (no ACF) — the portfolio is just content, editable in one screen.
- Filtering is **single-select per facet, combinable across facets** (Service AND Industry);
  server and JS behave identically, so shared URLs and the live UI never disagree.
- The `/work/` archive is the CPT archive (`has_archive => 'work'`), whose rewrite rule wins
  over the shadowed placeholder `work` page — kept for consistency with the rest of the site.
- Testimonials are inline on the case study (self-contained, one screen) rather than a
  cross-linked Testimonial post, so "add in under 5 minutes" holds.

**Next**
- Task 14: About / Process / FAQ / Insights.

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
