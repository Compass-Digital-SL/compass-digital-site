# Go-live checklist

Actions that can only be done on the **live (or staging) server**, not in local
development. Collected here as tasks surface them, so nothing is forgotten at
launch. Owned by **Task 21 (CD pipeline)** and **Task 22 (Go-live)**; tick each
off there.

## Deployment pipeline (Task 21 — owner, after buying hosting)

CD is built: push to `main` → staging, a `vX.Y.Z` tag → production (manual
approval). Full secret inventory in **`docs/SECRETS.md`**; restore steps in
**`docs/RESTORE.md`**. You must do this once on GitHub + the host:

- [ ] **Buy hosting + SSH access**, and create a staging subdomain/site
  (e.g. `staging.compassdigital.lk`) alongside live.
- [ ] **Add every secret** from `docs/SECRETS.md` in GitHub → Settings → Secrets
  and variables → Actions. Paste values into GitHub only — never into chat.
- [ ] **Create the GitHub Environments** `staging` and `production`; add a
  **Required reviewer** (yourself) on `production` so tag deploys wait for approval.
- [ ] **Create a deploy-only SSH key**, put its public half in the host's
  `authorized_keys`, and `ssh-keyscan` the host for `SSH_KNOWN_HOSTS`.
- [ ] **On the host:** create `<DEPLOY_PATH>/releases/`, point each site's docroot
  at `<DEPLOY_PATH>/current` (the symlink the pipeline flips), and confirm `wp`
  (WP-CLI) is on `PATH`.
- [ ] **Password-protect staging** (HTTP Basic Auth / `.htpasswd`) and keep
  `WP_ENVIRONMENT_TYPE=staging` so it stays noindex.
- [ ] **First release:** merge to `main`, watch the **Deploy** workflow deploy to
  staging, smoke-test + Lighthouse pass; then tag `v1.0.0`, approve the
  `production` gate.

## Backups (Task 21 — owner)

- [ ] **Create an offsite bucket** (S3 / Backblaze B2 / Cloudflare R2 / Wasabi) and
  add the `BACKUP_S3_*` secrets. The scheduled **Backup** workflow then runs daily
  (02:15 UTC); trigger a first run manually to confirm it works.
- [ ] _(Optional, belt-and-braces)_ also enable the **host's own** daily DB+files
  backup if the plan includes one — independent of GitHub.
- [ ] **Do a restore drill** into staging once backups exist (see `docs/RESTORE.md`).

## Environment

- [ ] **Set `WP_ENVIRONMENT_TYPE`** on each host.
  - Live: add `define( 'WP_ENVIRONMENT_TYPE', 'production' );` to `wp-config.php`
    (or the host's environment setting). **Required** — anything other than
    `production` makes the whole site `noindex` and serves `Disallow: /` in
    robots.txt, so if this is wrong on live, Google drops the site.
  - Staging/test: `'staging'`; leave unset on local. These stay noindex on purpose.
  - _Source: Task 16, `theme/inc/seo.php` (`compass_seo_robots`, `compass_robots_txt`)._

## Business details (NAP + social)

- [ ] **Replace the sample contact details** in `mu-plugins/compass-contact.php`
  (mapped to `wp-content/mu-plugins/` via `.wp-env.json`; a must-use plugin, so a
  theme update never wipes it). Phone/WhatsApp/email are set to real sample
  values; **the Facebook/Instagram/LinkedIn URLs are placeholders — swap for the
  real pages before launch.** Feeds the footer, contact page and the
  Organization / LocalBusiness schema.
- [ ] **Widen positioning to worldwide before deploy (decided — do not forget).**
  Compass works **from Sri Lanka for the whole world**, not Sri Lanka only. The
  site currently reads Sri Lanka–first in several places; broaden the wording and
  the structured data (keep Colombo as the base, add global reach). Touch points:
  - `theme/inc/seo.php` — `compass_localbusiness_schema()` / Organization
    `contactPoint`: `areaServed` is `Sri Lanka` / `LK`. Add **Worldwide** (e.g.
    `areaServed: ["LK", "Worldwide"]` or a broader `areaServed`).
  - `theme/template-parts/home/local-area.php` — service-area block copy and the
    city chips say "across Sri Lanka"; add a clear "…and worldwide, remotely" line.
  - `docs/SEO.md §Base city / service area` and the home/meta copy — currently
    "all of Sri Lanka, overseas Sri Lankan-owned businesses secondary". Reframe
    worldwide delivery as a primary audience, not just a secondary one.
  - Keep **NAP, address and Google Business Profile** as the Colombo base (local
    SEO still wants a real place); this is about *reach*, not moving the office.
  - Best handled as its own small copy/positioning pass (its own branch) before
    launch. _Source: Task 17 (owner decision, 2026-11-19); supersedes the Task 16
    "revisit areaServed" note._

## Legal pages (Task 20 — owner)

The Pages exist (`privacy`, `terms`, `cookie-policy`, each assigned its template)
and the drafts render. Before launch:

- [ ] **Read and confirm the three legal pages** — `/privacy/`, `/terms/`,
  `/cookie-policy/` — and **fill every `[…]` placeholder**: registered company
  name + address (from Site Settings / `mu-plugins/compass-contact.php`), the
  data-retention period, the rights/complaint route, and the governing law.
- [ ] **Have them legally reviewed** for every jurisdiction you serve (Sri Lanka
  PDPA No. 9 of 2022, and any others). The drafts are a starting point, not legal
  advice. _Source: Task 20, `docs/SECURITY.md` §6._

## Security — headers, CSP and accounts (Task 20 — owner)

Hardening ships in `mu-plugins/compass-security.php` (headers, CSP Report-Only,
XML-RPC off, file-editor off, user-enumeration blocked, login throttle,
`security.txt`). These are all **done at/after go-live**, not in local dev — they
need the real host, HTTPS and the production admin account:

- [ ] **At first deploy — serve over HTTPS.** HSTS activates only on TLS
  (`is_ssl()`); confirm the cert and a http→https redirect at the host.
- [ ] **On staging, then at go-live — enforce CSP.** On staging, let CSP run in
  Report-Only with real traffic (consent accepted + declined, a form submit,
  every page type); add a reporting endpoint if you want the violations
  collected. Once the run is clean, **enforce** by adding
  `define( 'COMPASS_CSP_ENFORCE', true );` to `wp-config.php`. Do not enforce
  before that observation run — the CSP still allows `'unsafe-inline'` and should
  be tightened to nonces first. _Full path: `docs/SECURITY.md` §2._
- [ ] **If the site sits behind Cloudflare or another reverse proxy** — tell the
  rate limits (quote form, chat) where the real visitor IP is, or every visitor
  shares one limit. In `wp-config.php`:
  `define( 'COMPASS_CLIENT_IP_HEADER', 'HTTP_CF_CONNECTING_IP' );` (Cloudflare) or
  `'HTTP_X_FORWARDED_FOR'` (other proxies). Only set it when **all** traffic goes
  through that proxy — otherwise the header can be faked.
- [ ] **Spam check** — add **both** Turnstile keys (site + secret) in Site Settings.
  With only one set, the check stays off.
- [ ] **Right after go-live — enable two-factor authentication** on the production
  admin account and keep a single Administrator (Editors for everyone else).
  _`docs/SECURITY.md` §3._
- [ ] Set a **monitored security contact** (the `security.txt` reads the firm
  email from `compass-contact.php`).
- [ ] After deploy, **re-scan headers** on the live URL (securityheaders.com /
  Mozilla Observatory).

## Performance — server caching (Task 18)

Static theme assets are long-cached by a committed `theme/assets/.htaccess`
(`Cache-Control: public, max-age=31536000, immutable` for JS/CSS/fonts/images).
That covers Apache/cPanel (Namecheap) hosting with no extra setup. Two things are
still server-side:

- [ ] **HTML caching rule.** Pages must **not** be cached immutably (content
  changes, and quote/thank-you pages are per-visit). If a full-page cache plugin
  or host cache is enabled, set HTML `Cache-Control` to roughly
  `public, max-age=0, s-maxage=600, must-revalidate` (short shared cache, always
  revalidate) and **exclude** `/get-a-quote/`, `/contact/` and `/thank-you/` from caching
  (they carry the form nonce; the theme also sends `no-store` on them).
- [ ] **Nginx hosts only:** `.htaccess` is ignored. Add the equivalent to the
  server block:
  ```nginx
  location ~* \.(js|mjs|css|woff2|avif|webp|png|jpe?g|svg|gif|ico)$ {
      add_header Cache-Control "public, max-age=31536000, immutable";
      access_log off;
  }
  ```
- [ ] **Confirm compression** is on at the host (gzip or brotli for HTML/CSS/JS).
  Most managed hosts enable it by default; the `.htaccess` requests `mod_deflate`
  as a fallback.

_Source: Task 18. Budgets are enforced in CI (`lighthouserc.json` + `budget.json`);
see `docs/PROGRESS.md` Task 18._

## SEO verification (after deploy to staging/live)

- [ ] **Run the SEO audit** against the deployed site:
  `SITE_URL=https://staging.example.com npm run seo:audit` — expect zero broken
  links, no missing/duplicate titles or H1s, no images missing `alt`.
  _Needs Docker only for the **local** run; against a remote URL it just needs Node._
- [ ] **Validate schema** for one of each template in Google's Rich Results Test
  (home, a service, an industry, a case study, an Insights post, Contact, FAQ).
- [ ] **Submit `/wp-sitemap.xml`** to Google Search Console (Task 17 sets up the
  account) and confirm robots.txt on live shows the sitemap line, not `Disallow: /`.
- [ ] **Confirm share images** render: paste a few live URLs through a social
  debugger (Facebook Sharing Debugger / X Card Validator). Re-run
  `npm run og:generate` if the brand or copy changed.

## Local SEO + analytics (Task 17 — owner, via Site Settings)

Full step-by-step owner guide: **`docs/LOCAL-SEO.md`**. In short, in
**WP Admin → Settings → Site Settings**:

- [ ] **Local & maps:** paste the **Google Maps link** and **Google reviews
  link** from the verified Google Business Profile; optionally the
  **latitude/longitude**. Enter **average rating / review count** only once there
  are real Google reviews (feeds `aggregateRating` schema).
- [ ] **Analytics & verification:** paste the **GA4 Measurement ID** (`G-…`) and
  the **Search Console verification** token (or set `COMPASS_GA4_ID` /
  `COMPASS_GSC_VERIFICATION` as constants in `wp-config.php`). Analytics loads
  only after cookie consent + page idle; the Search Console tag verifies the site.
- [ ] **Verify tracking** after launch: accept cookies on the live site and check
  GA4 **Realtime** shows the visit, then that `quote_submitted`, `whatsapp_click`,
  `phone_click`, `service_view` and `command_menu_used` events arrive.
- [ ] **Follow `docs/LOCAL-SEO.md`**: create/verify Google Business Profile, set up
  Bing Places, gather reviews, and build consistent NAP citations in directories.
  _Source: Task 17._

## Rank Math (only if the owner wants the plugin)

- [ ] Import `docs/seo/rank-math-settings.json` (Rank Math → Status & Tools →
  Import & Export) so its overlapping modules stay off and never duplicate the
  theme. See `docs/seo/README.md`. Skip entirely if not using Rank Math.
