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

- [ ] **Add real contact details** via a must-use plugin so a theme update never
  wipes them: `wp-content/mu-plugins/compass-contact.php` filtering
  `compass_contact_details` — real phone, WhatsApp (digits only), email, address
  and the Facebook/Instagram/LinkedIn URLs. Feeds the footer **and** the
  Organization / LocalBusiness schema. Placeholders live in
  `theme/inc/nav.php` (`compass_contact_details`). _Source: Task 16._

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

## Rank Math (only if the owner wants the plugin)

- [ ] Import `docs/seo/rank-math-settings.json` (Rank Math → Status & Tools →
  Import & Export) so its overlapping modules stay off and never duplicate the
  theme. See `docs/seo/README.md`. Skip entirely if not using Rank Math.
