# Technical SEO — how it fits together (Task 16)

The theme owns SEO. Everything a search engine or social scraper reads is emitted
by the theme, from one place, so there is a single source of truth and no
duplicate tags. Rank Math is optional and, when used, is configured to stay out
of the theme's way.

## What the theme outputs

| Concern | Where | Notes |
|---|---|---|
| `<title>` | per-section `pre_get_document_title` filters | keyword-map titles from `docs/SEO.md` §3 |
| Meta description | `compass_meta_description()` in `theme/inc/seo.php` | centralised; per-section printers are unhooked |
| Canonical URL | `compass_canonical_url()` | singular, archives, pagination-aware |
| Open Graph + X cards | `compass_seo_head()` | with generated share images |
| Share images | `theme/assets/og/*.png` | run `npm run og:generate` after a brand/copy change |
| robots meta | `compass_seo_robots()` via `wp_robots` | staging/local = noindex; production = index + large previews; thin/utility pages noindex |
| robots.txt | `compass_robots_txt()` | production allows crawl + sitemap; non-production disallows all |
| XML sitemap | core `/wp-sitemap.xml`, trimmed in `seo.php` | no author sitemap; styleguide, thank-you, quote requests excluded |
| 301 redirects | `compass_redirects()` map + `template_redirect` | version-controlled; survives the plugin being off |
| Trailing slashes | WordPress `redirect_canonical` | permalink structure `/%postname%/` enforces one canonical form |
| Schema (identity) | `seo.php` — Organization, WebSite, LocalBusiness | front page + Contact, with stable `@id` anchors |
| Schema (per page) | templates — Service, FAQPage, BreadcrumbList, Article, CreativeWork | JSON-LD, one primary entity per page |

### Environment gate

`wp_get_environment_type()` drives indexability. Set `WP_ENVIRONMENT_TYPE`
(`local`, `staging`, `production`) in `wp-config.php` / the host. Anything other
than `production` outputs `noindex,nofollow` site-wide and a `Disallow: /`
robots.txt, so staging and local copies never rank. Production must report
`production`.

## Rank Math

Rank Math is **not required** — the theme is complete without it. If the owner
wants Rank Math for Redirections, the 404 monitor and Search Console, import
`rank-math-settings.json` (Rank Math → Status & Tools → Import & Export). That
baseline turns **off** every module that would duplicate the theme:

- **Rich Snippets / Schema** — theme emits JSON-LD.
- **Sitemap** — the site uses the core `/wp-sitemap.xml`.
- **Breadcrumbs** — theme renders breadcrumbs + `BreadcrumbList`.
- **Meta description** (`add_meta_description: off`) — theme owns descriptions.

Rank Math's Titles & Meta module cannot be removed, so to stop it emitting a
second title/OG/canonical, keep the site's SEO owned by the theme and, if both
somehow run, drop Rank Math's frontend head with a one-line must-use plugin:

```php
// wp-content/mu-plugins/compass-seo-owner.php
add_action( 'init', function () {
	remove_all_actions( 'rank_math/head' );
} );
```

Pick one owner. Here it is the theme.

## Audit

`npm run seo:audit` crawls the running local site and reports broken links,
missing/empty titles, missing or duplicate `<h1>`, heading-hierarchy skips,
images with no `alt` attribute, missing canonical/description, and orphan pages.
It exits non-zero on errors (add `--strict` to fail on warnings too):

```bash
npm run wp:start
npm run seo:audit                       # defaults to http://localhost:8888
SITE_URL=http://localhost:8888 npm run seo:audit --strict
```

### Alt-text rule

`alt=""` is treated as a valid decorative image and passes; a missing `alt`
attribute is an error. Content and case-study images must carry meaningful alt
text; purely decorative images use an explicit empty `alt`.
