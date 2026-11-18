#!/usr/bin/env bash
#
# Seeds the local WordPress install with sane defaults for Compass Digital.
# Run against the running wp-env container. Safe to run repeatedly.
#
# Usage:  npm run wp:seed   (or: bash scripts/setup.sh)
#
set -euo pipefail

# Run WP-CLI inside the wp-env "cli" container.
# Go through scripts/wp-env.js so the dns connectivity-detection workaround applies here too.
wp() {
  node scripts/wp-env.js run cli wp "$@"
}

echo "==> Checking WordPress is up..."
wp core is-installed

echo "==> Activate the Compass Digital theme"
# wp-env mounts ./theme into wp-content/themes/ (folder basename = "theme") but
# does not reliably activate it, so without this the default theme renders and
# pages lack #main / the theme's landmarks. Resolve the slug from the theme's
# style.css "Theme Name" so this survives a folder rename, then activate it.
THEME_SLUG="$(wp theme list --field=name --format=csv 2>/dev/null | grep -ix -e theme -e compass-digital | head -n1 || true)"
if [ -z "$THEME_SLUG" ]; then
  echo "!! Could not find the Compass Digital theme among installed themes:" >&2
  wp theme list --fields=name,title,status >&2 || true
  exit 1
fi
wp theme activate "$THEME_SLUG"

echo "==> Permalinks -> /%postname%/"
wp rewrite structure '/%postname%/' --hard
wp rewrite flush --hard

echo "==> Timezone -> Asia/Colombo"
wp option update timezone_string 'Asia/Colombo'

echo "==> Discourage search engines (local must never be indexed)"
wp option update blog_public 0

echo "==> Disable comments site-wide"
wp option update default_comment_status closed
wp option update default_ping_status closed
# Turn comments off on anything that already exists.
wp post list --format=ids | xargs -r wp post update --comment_status=closed --ping_status=closed || true

echo "==> Delete sample content (Hello World post, sample page, draft)"
wp post delete 1 2 3 --force 2>/dev/null || true
wp comment delete 1 --force 2>/dev/null || true
# Remove default plugins we never want (Akismet, Hello Dolly).
wp plugin delete akismet hello 2>/dev/null || true

echo "==> Create site pages (idempotent)"
create_page() {
  local title="$1"
  local existing
  existing="$(wp post list --post_type=page --post_status=publish,draft --field=ID --name="$(echo "$title" | tr '[:upper:] ' '[:lower:]-')" 2>/dev/null || true)"
  if [ -z "$existing" ]; then
    wp post create --post_type=page --post_status=publish --post_title="$title" --porcelain
  else
    echo "$existing"
  fi
}

HOME_ID="$(create_page 'Home')"
create_page 'Services'       >/dev/null
create_page 'Industries'     >/dev/null
create_page 'Work'           >/dev/null
create_page 'About'          >/dev/null
create_page 'Insights'       >/dev/null
create_page 'Contact'        >/dev/null
create_page 'Get a quote'    >/dev/null
create_page 'Privacy'        >/dev/null
create_page 'Terms'          >/dev/null

echo "==> Set Home as the static front page"
wp option update show_on_front 'page'
wp option update page_on_front "$HOME_ID"
wp option update page_for_posts 0

echo ""
echo "Done. WordPress is seeded at http://localhost:8888"
echo "Admin: http://localhost:8888/wp-admin  (user: admin / pass: password)"
