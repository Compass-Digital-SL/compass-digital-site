# Security — Compass Digital

What is hardened in code, what is policy, and what the owner must do. The code
lives in `mu-plugins/compass-security.php` (must-use, so it survives theme and
plugin changes). Legal pages are in `docs`/the theme (see §6).

---

## 1. What the code does

`mu-plugins/compass-security.php`:

- **Security headers** on every front-end response (`send_headers`):
  - `X-Content-Type-Options: nosniff`
  - `X-Frame-Options: SAMEORIGIN` (plus `frame-ancestors 'self'` in CSP)
  - `Referrer-Policy: strict-origin-when-cross-origin`
  - `Permissions-Policy` — geolocation, mic, camera, payment, USB all off
  - `X-Powered-By` removed (no PHP version leak)
  - `Cross-Origin-Opener-Policy: same-origin`
  - `Strict-Transport-Security: max-age=31536000; includeSubDomains` — **only
    over HTTPS** (`is_ssl()`), so it is never sent on a plain-HTTP dev box.
  - **Content-Security-Policy in Report-Only mode** (see §2).
- **XML-RPC off** (`xmlrpc_enabled` → false), pingback methods and the
  `X-Pingback` header removed.
- **File editor off** — `DISALLOW_FILE_EDIT` defined.
- **User-enumeration blocked** — `?author=N` and author archives redirect home
  for guests (before core's canonical redirect, which would leak the login),
  author links point home; the
  REST `/wp/v2/users` endpoints are removed for unauthenticated requests; the
  oEmbed author fields are stripped.
- **Login throttle** — 5 failed attempts per IP in 15 minutes locks that IP out
  for the rest of the window (transient-based, no table, no plugin); generic
  login error so neither username nor password is confirmed.
- **`security.txt`** — served at `/.well-known/security.txt` and `/security.txt`
  (RFC 9116), with a contact email and a self-refreshing `Expires` date.

The **quote/contact form** (the only public input) was already defended in Task
15: nonce-verified, honeypot + time-trap, per-IP rate limit,
every field sanitised and escaped, saved to a private CPT. No change needed —
verified during this task.

---

## 2. Content-Security-Policy — tuning to enforcement

CSP ships **Report-Only** first so it reports violations without breaking the
page. The policy currently allows `'unsafe-inline'` for scripts and styles
because the site still emits inline blocks (critical CSS, consent config,
JSON-LD schema, speculation rules).

**Allowed origins:** Google Analytics / Tag Manager per Google's GA4 CSP guide
(`*.googletagmanager.com`, `*.google-analytics.com`, `*.analytics.google.com` —
the wildcards cover the regional collection hosts). No frames are allowed. Images allow `self data:`; fonts are self-hosted.

Tune with the `compass_csp_directives` filter (add origins) or
`compass_security_headers` filter (whole header set).

**To enforce** once a Report-Only run on staging is clean:

1. Add a reporting endpoint and watch reports on staging for a week with real
   traffic (consent accepted + declined, a form submit, every page type).
2. Ideally, nonce the inline blocks and drop `'unsafe-inline'` from `script-src`.
3. Flip to enforcing by defining in `wp-config.php`:
   ```php
   define( 'COMPASS_CSP_ENFORCE', true );
   ```
   This swaps `Content-Security-Policy-Report-Only` for
   `Content-Security-Policy`. Leave it undefined to stay Report-Only.

---

## 3. Least-privilege roles

- **One Administrator** — the owner. Keep it to a single admin account.
- Day-to-day content editing uses the **Editor** role, not Administrator.
- Give collaborators the **lowest role that does the job** (Author/Contributor);
  remove accounts when people leave.
- The admin **file editor is disabled** — no one edits code from wp-admin;
  changes go through Git → CI → deploy.
- The `quote_request` CPT is **private** (`public=false`, not in REST); CSV
  export requires `edit_others_posts` (Editor+).
- Use **strong, unique passwords** and enable **two-factor** on the admin
  account (owner action — needs a 2FA plugin or host feature; see §5).

---

## 4. Update policy

- **Core, plugins, themes:** apply security updates **within a week** of release;
  WordPress minor/security releases should auto-update.
- **Dependencies:** Dependabot (npm/Composer/Actions) runs weekly — review and
  merge its PRs (CI gates them).
- **PHP:** stay on a supported branch (target PHP 8.3 in production).
- **security.txt `Expires`:** regenerated on every request (+1 year), so it
  never goes stale. Confirm the contact email is monitored.
- **Backups:** daily offsite DB + uploads (Task 21, `backup.yml`); run a restore
  drill before launch (`docs/RESTORE.md`).
- **Review:** re-run this checklist and `npm run seo:audit` after any major
  change, and periodically re-scan headers (e.g. securityheaders.com,
  Mozilla Observatory) on the live URL.

---

## 5. Owner must do (server / account)

- [ ] **Serve over HTTPS** with a valid certificate (HSTS only activates on TLS).
- [ ] Confirm **XML-RPC is also blocked at the server/WAF** if the host offers it
      (defence in depth; the code already disables it in WP).
- [ ] Enable **two-factor authentication** on the admin account.
- [ ] Set a **real security contact email** (currently reads the firm email from
      `mu-plugins/compass-contact.php`) and make sure it is monitored.
- [ ] Keep **one Administrator**; create Editor accounts for everyone else.
- [ ] After the first staging deploy, watch **CSP Report-Only** reports, then
      enforce (§2).
- [ ] Verify security headers on the live URL (securityheaders.com / Observatory).

---

## 6. Legal pages

Drafted and marked **DRAFT — for legal review**, matching what the site actually
collects (quote/contact form, GA4 after consent, email, spam defences):

- **Privacy Policy** — `theme/page-privacy.php` → page slug `privacy`
- **Terms of Use** — `theme/page-terms.php` → page slug `terms`
- **Cookie Policy** — `theme/page-cookie-policy.php` → page slug `cookie-policy`

Linked from the footer (Legal column) and the cookie consent bar.

**Owner must do:**
- [ ] Create three Pages in WP Admin with slugs `privacy`, `terms`,
      `cookie-policy` and assign each the matching **Page Template** (Privacy
      Policy / Terms / Cookie Policy). The templates hold the copy; the Pages can
      be empty.
- [ ] **Read and confirm** the copy; complete every `[…]` placeholder
      (registered company name and address come from Site Settings /
      `compass-contact.php`; retention period, rights/complaint route and
      governing law need your decision).
- [ ] Have them **reviewed** for the jurisdictions you serve (Sri Lanka PDPA
      No. 9 of 2022, and any others). These drafts are not legal advice.
