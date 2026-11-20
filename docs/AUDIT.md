# Pre-launch audit — Compass Digital

## Re-audit after the v2 redesign (2026-11-20, Task 26)

Run against the local site after Task 24. Lint, build and all 84 Chromium e2e/axe
tests were already green; these gaps were found by crawling and probing instead.

**Fixed in Task 26**
- [x] **Broken main-menu links (4 × 404)** — `inc/nav.php` used old slugs
  (`website-design-development`, `restaurants-cafes`, `travel-tourism`,
  `corporates-smes`). Also fixed in the seeded "website cost" article.
- [x] **Login name leak** — `/?author=1` was canonical-redirected to
  `/author/admin/` before the block ran. Now blocked at priority 1, author
  archives redirect home for guests, author links point home.
- [x] **Critical CSS missing in production** — it was read from `assets/src`,
  which the deploy artifact excludes. Vite now writes a minified
  `assets/dist/critical.css` (tokens + critical layer, no comments).
- [x] **Stale form nonce under page cache** — `/contact/` also carries the form;
  contact, quote and thank-you now send `no-store`; GO-LIVE cache rule updated.
- [x] **Schema** — Organization `contactPoint.areaServed` `LK` → `Worldwide`;
  `availableLanguage` English only (Sinhala/Tamil were not offered).
- [x] **Titles** — home, contact, quote, legal, 404, search and thank-you had the
  default "Page – site name"; services hub ended in "| Compass".
- [x] **CSP** — GA4 hosts widened to Google's documented wildcards
  (`*.google-analytics.com`, `*.analytics.google.com`), else analytics breaks
  the day CSP is enforced.
- [x] **Headers** — dropped the obsolete `interest-cohort` (console error on every
  page); removed `X-Powered-By`.
- [x] **Comments** — no comments feed advertised; comments/pings closed (no
  template shows them, but `wp-comments-post.php` accepted them).
- [x] **CI** — `npm run seo:audit` now runs after the e2e tests, so a broken
  internal link fails the build. The crawler now decodes `&#038;` in hrefs (it
  reported false `?service=x&` redirects).

**Still open**
- Hero compass needle overlaps the intro text at 360px; on phones the cookie card
  + bottom bar cover ~40% of the first screen (design call).
- Footer column-title links are 15px tall (pass on spacing, but tight).
- `docs/SEO.md` keyword map still targets "Sri Lanka" while the site is worldwide.
- Organization `logo` comes from the Customizer logo only — keep it set.
- Owner: placeholder home text, LKR budgets, sample work/testimonials, social
  URLs, legal `[…]` placeholders, `docs/GROWTH.md`, real Lighthouse on staging.

---

_Original audit date: 2026-11-19. Full review of code, security, SEO, performance, features,
accessibility, UI/UX and the outstanding owner ("You do") actions from the build
playbook. Read alongside `docs/PROGRESS.md` (task log) and `docs/GO-LIVE.md`
(server-side owner steps)._

---

## 1. Status at a glance

**Built and verified (Tasks 1–19, 21):** scaffolding, local WP env, theme +
Vite build, CI, SEO strategy, design system, navigation, hero/motion, post types
+ settings, home, 6 service pages, 5 industry pages, work portfolio, About/
Process/FAQ/Insights, quote + contact flow, technical SEO + schema, local SEO +
analytics, performance pass, accessibility + QA, CD pipeline.

**Not done — blocks go-live:**

| # | Task | State | Why it blocks launch |
|---|------|-------|----------------------|
| **20** | **Security & legal** | **Not started** | No security headers, no WP hardening, no `security.txt`; Privacy/Terms/Cookie pages are empty. Legal exposure + weak security posture. |
| 22 | Go-live | Not started (depends on owner buying hosting/domain) | The actual launch runbook. |
| 23 | Post-launch growth | Not started | `docs/GROWTH.md` not written. |

**The single most important gap is Task 20.** Everything else is either owner
account/money work (listed in §7) or polish (§3–6).

---

## 2. Task 20 — Security & legal (the big outstanding build)

The playbook's Task 20 prompt was never run. Concretely missing in code:

### Security hardening (none present — confirmed by repo search)
- [ ] **Security headers** — no `X-Frame-Options`, `Referrer-Policy`,
  `Permissions-Policy`, `X-Content-Type-Options`, HSTS, or **CSP (report-only
  first)** anywhere in the theme, plugin or mu-plugins.
- [ ] **Disable XML-RPC** (`xmlrpc_enabled` → false; it is still on).
- [ ] **Disable the file editor** — `DISALLOW_FILE_EDIT` not defined.
- [ ] **Limit login attempts** — no throttling.
- [ ] **Block user enumeration** — `/?author=1` and the REST users endpoint are
  not restricted.
- [ ] **Harden REST/author archives** beyond the current noindex.
- [ ] **`security.txt`** — not present (`/.well-known/security.txt`).
- [ ] **Update policy / least-privilege roles** — not documented.

### Legal pages (empty shells only)
- The `Privacy`, `Terms` pages were created empty in Task 2; there is **no
  Cookie Policy page** and **no drafted content** for any of them (no
  `page-privacy.php` / `page-terms.php`, no body copy).
- [ ] Draft **Privacy Policy**, **Terms**, **Cookie Policy** matching what the
  site actually collects (quote form data, GA4, email), marked **DRAFT** for the
  owner's legal review, with placeholders for registration details.
- The cookie **consent bar is live** (Task 17) but has nowhere to link for the
  policy itself — this is a visible gap for a site that sets GA4 cookies.

**Recommendation:** run Task 20 on its own branch (`task/20-security-legal`)
before any production deploy. Put the headers in `mu-plugins` (survives theme
changes), ship CSP in **report-only** first so it can be tuned against the real
inline scripts (speculation rules, consent, schema JSON-LD) without breaking the
page.

---

## 3. Code audit

**Strong.** Clean, modern, well-factored.

- **Architecture is right:** content/data in `plugins/compass-core` (survives a
  theme swap), presentation in the theme, business details in `mu-plugins`
  (survives both). SEO/schema centralised in `theme/inc/seo.php` so no duplicate
  tags.
- **WordPress discipline:** native meta boxes (no ACF), nonces, `register_post_meta`,
  capability checks (`edit_others_posts` on CSV export), private `quote_request`
  CPT (`public=false`, no REST).
- **No jQuery on the front end**; JS is per-feature code-split and lazy-loaded.
- **Escaping/sanitisation** on the quote handler is thorough (see §4).

**Minor code notes (non-blocking):**
- `.wp-env.json` pins WordPress to **7.1.4** — a dev-box workaround (noted in
  Task 2). Confirm production runs **current WP** (hosting, not wp-env).
- `plugins/compass-core/includes/` and `theme/assets/fonts|og` showed as empty in
  the tree snapshot — verify the include files and generated OG/font assets are
  actually committed (CI build would catch a missing include; OG PNGs are
  committed per Task 16, re-confirm before deploy).
- Local CRLF vs LF: PHPCS warnings suppressed locally (autocrlf artifact); CI
  runs on LF/Linux. Fine, just know it.

---

## 4. Security audit (what *is* built)

The **quote/contact form** — the main public input surface — is well defended:
- Nonce verified; honeypot + <2s time trap (silent drop); per-IP rate limit
  (5 / 15 min); optional Cloudflare Turnstile (degrades to off when unkeyed).
- Every field sanitised (`sanitize_text_field`, `sanitize_email`,
  `sanitize_textarea_field`, `absint`), `wp_unslash` applied, server-side
  validation, `esc_url_raw` + same-origin check on the redirect `source`.
- Enquiries saved to a locked-down private CPT; failed sends never block the user.

**Gaps (all are Task 20, see §2):** no site-wide headers/CSP, XML-RPC on, file
editor on, no login throttling, user enumeration open, no `security.txt`.
Front-end bloat/fingerprinting (generator, RSD, WLW, pingback) **is** stripped
(`theme/inc/cleanup.php`) — good.

---

## 5. SEO audit

**Comprehensive and well-implemented.**
- Single SEO engine: canonical, OG, X cards, per-page titles/meta (under limits),
  pre-generated 1200×630 share images.
- Schema graph: Organization, WebSite (SearchAction), LocalBusiness, Service,
  FAQPage, BreadcrumbList, Article, CreativeWork — JSON-LD, de-duplicated vs Rank
  Math (overlapping modules disabled via exported config).
- Env-aware indexability: `noindex` everywhere except production; `robots.txt`
  serves `Disallow: /` off-production. Trimmed core sitemap (private CPT, author,
  thank-you, styleguide excluded). 301 redirect map + trailing-slash consistency.
- Audit script (`npm run seo:audit`) for broken links / missing titles / dup H1 /
  missing alt.

**Outstanding (owner, server-side — in GO-LIVE):**
- [ ] **Set `WP_ENVIRONMENT_TYPE=production` on live** — if wrong, the whole site
  stays `noindex` and Google drops it. _Highest-risk single switch._
- [ ] Run `seo:audit` against the deployed URL; validate schema in Rich Results
  Test; submit sitemap to Search Console + Bing.
- [ ] **Worldwide positioning pass (decided, not done):** `areaServed` is
  `Sri Lanka`/`LK` only; copy reads SL-first. Broaden schema + home/SEO copy to
  "from Sri Lanka, worldwide" (own small branch). Source: Task 17 decision.

## 6. Performance audit

**Budgets enforced in CI, with headroom.**
- Zero render-blocking CSS (tokens + hand-authored critical layer inlined,
  @font-face inlined, full sheet deferred via preload/onload). LCP = headline
  text, not SVG.
- Homepage **CSS ~11 KB gz** (budget 40) and **JS ~6 KB gz** (budget 90) — large
  margin. Speculation-rules prefetch, WebP/AVIF, lazy below-fold, width/height set.
- LHCI fails CI on perf < 0.95 / LCP > 1.8s / CLS > 0.05 / TBT > 200ms on
  throttled 4G.

**Caveat:** real runtime LCP/CLS/score were **never measured locally** —
Docker/wp-env is unavailable in the build sandbox. **Capture real Lighthouse
numbers on staging before launch** (currently only the CI/LHCI config proves
budgets, not a real run). Server caching (HTML cache rule, Nginx equivalent,
compression) is documented in GO-LIVE but owner/host-side.

## 7. Features & functions

All playbook features present: command menu (⌘K), mobile bottom bar, scroll rail,
compass hero with motion system, pain-point selector, 6 services, 5 industries,
work portfolio with filters, Insights blog with TOC/related/share, 3-step quote
flow (works without JS), contact, thank-you, styleguide. Progressive enhancement
throughout (everything works JS-off). No checkout (quote-only) as specified.

**Content still placeholder (owner):**
- [ ] 3 `[Sample]` Work case studies + 3 sample testimonials — replace with real
  work/names/results (`wp compass seed --fresh` clears samples).
- [ ] Flag the 3 strongest case studies **Featured** + add Featured testimonials
  so the home carousel/proof fill.
- [ ] Team placeholders on About — swap for real people/bios.
- [ ] `mu-plugins/compass-contact.php`: phone/email are real; **social URLs are
  placeholders** — set real Facebook/Instagram/LinkedIn before launch.

## 8. Accessibility / UI / UX

**WCAG 2.2 AA audited (Task 19).** Keyboard path, visible focus (brass ring),
skip link, SR labels, AA contrast (measured in DESIGN.md), reduced-motion static
equivalents, ≥44px touch targets, 200% zoom reflow, no 320px overflow. Cross-
browser Playwright (Chromium/Firefox/WebKit) at 360/768px + axe in CI.
- 5-second test passes; every service ≤3 clicks; no dead-end pages.
- **Known:** Firefox couldn't launch on the Windows dev box (env/AV, not a site
  issue) — it runs in CI (Linux). Confirm the CI Firefox run is green.

## 9. Non-functional

- **CI:** PHPCS, ESLint, Stylelint, Vite build, `php -l`, Playwright smoke + axe,
  LHCI budgets on every PR. Dependabot (npm/Composer/actions) weekly.
- **CD:** push `main` → staging, `vX.Y.Z` tag → production (manual approval),
  symlink releases + instant rollback on smoke failure, keep last 5. Daily offsite
  DB+uploads backup workflow. Restore guide (`docs/RESTORE.md`).
- **Docs:** BRAND, DESIGN, SEO, LOCAL-SEO, SECRETS, RESTORE, GO-LIVE, PROGRESS.
  Missing: **`docs/GROWTH.md`** (Task 23).

---

## 10. Outstanding owner "You do" actions (from the playbook)

Grouped by when. Full server detail in `docs/GO-LIVE.md`, secrets in
`docs/SECRETS.md`, local SEO in `docs/LOCAL-SEO.md`.

### Accounts / money (buy + create)
- [ ] Buy the **domain** (Namecheap — noted, not Cloudflare) and **hosting**
  (PHP 8.3, SSH, WP-CLI, daily backups, free SSL, LiteSpeed/Nginx, SG/India DC).
- [ ] Create **GitHub Environments** `staging` + `production` (required reviewer
  on production) and add **every secret** from `docs/SECRETS.md` (SSH_*,
  DEPLOY/WP paths, URLs, TURNSTILE_*, SMTP_*, BACKUP_S3_*) — in GitHub only.
- [ ] Create a **deploy-only SSH key**; on the host make `releases/` + point
  docroot at `current`.
- [ ] Create the **offsite backup bucket** (S3/B2/R2/Wasabi) + run one manual
  backup + a restore drill.
- [ ] Free accounts: **Search Console, GA4, Google Business Profile, Bing
  Webmaster** (playbook A7).
- [ ] **Cloudflare Turnstile** keys + **SMTP** details → Site Settings (or
  `COMPASS_*` constants). Without SMTP, owner/auto-reply emails rely on host mail.

### Server / config at launch
- [ ] **`WP_ENVIRONMENT_TYPE=production`** on live (critical — §5).
- [ ] Password-protect staging; keep it `staging` (noindex).
- [ ] HTML cache rule + exclude `/get-a-quote/` + `/thank-you/`; Nginx cache block
  if Nginx; confirm compression.
- [ ] First deploy to staging → smoke/Lighthouse → tag `v1.0.0` → approve prod.

### Content / Site Settings (WP Admin)
- [ ] Fill **Settings → Site Settings** (phone, WhatsApp, email, address, service
  area, reg number, hours, social) + Local&maps + GA4/GSC IDs.
- [ ] Replace sample Work/testimonials, feature the best, real team bios, real
  social URLs (§7).

### Review / verify (owner must read)
- [ ] **Read and confirm the legal pages** once Task 20 drafts them (A8).
- [ ] After launch: GA4 Realtime + events fire; schema validates; sitemap
  submitted; share images render; redirects/forms/emails work.
- [ ] Follow `docs/LOCAL-SEO.md` (GBP, Bing Places, directories) + (Task 23)
  `docs/GROWTH.md` weekly/monthly routine once written.

---

## 11. Recommended order before launch

1. **Task 20** — security headers (CSP report-only) + WP hardening + `security.txt`
   + draft legal pages. _Blocks launch._
2. **Worldwide positioning** copy/schema pass (own branch).
3. **Task 23** — write `docs/GROWTH.md`.
4. Owner: buy hosting/domain, wire secrets + environments (§10).
5. First **staging** deploy → capture **real Lighthouse numbers** (§6) + run
   `seo:audit` on the live URL + validate schema.
6. Replace placeholder content; confirm legal pages.
7. Tag `v1.0.0`, approve production, work `docs/GO-LIVE.md` line by line.
