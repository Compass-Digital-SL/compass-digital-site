# Go-live checklist

Actions that can only be done on the **live (or staging) server**, not in local
development. Collected here as tasks surface them, so nothing is forgotten at
launch. Owned by **Task 21 (CD pipeline)** and **Task 22 (Go-live)**; tick each
off there.

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
- [ ] **Revisit `areaServed` in the schema** if positioning as worldwide: the
  LocalBusiness/ProfessionalService node in `theme/inc/seo.php` currently states
  `areaServed: Sri Lanka` and a Colombo address. Fine for a Colombo-registered
  firm; widen in Task 17 if needed. _Source: Task 16._

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
