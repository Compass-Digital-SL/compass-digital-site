# Running the site locally — quick checklist

Everyday guide for running Compass Digital on your own machine. Run all commands
in the **VS Code terminal** (Git Bash profile), from the project folder.
Full background is in [README.md](../README.md).

## One-time setup (only the first time ever)
- [ ] Install **Docker Desktop**, **Node.js 18+**, and **Git for Windows** (Git Bash).
- [ ] `npm install`
- [ ] `composer install`  *(only needed if you'll run the PHP linter)*

## Every time you start working
1. [ ] Start **Docker Desktop** — wait until it says *running*.
2. [ ] `npm run wp:start`   — starts WordPress (first run takes a few minutes)
3. [ ] `npm run build`      — builds the theme CSS/JS
4. [ ] Open **http://localhost:8888**  ·  dashboard **/wp-admin** (`admin` / `password`)

> After pulling new code that adds pages (e.g. the Thank-you page), also run
> `npm run wp:seed` once — otherwise new pages 404.

## While editing CSS / JS (live reload)
- [ ] `npm run dev`  — leave running, refresh the browser as you edit
- [ ] `Ctrl+C` to stop it, then `npm run build` for the real production assets

## When you finish
- [ ] `npm run wp:stop`  — stops WordPress, keeps your data

## Command cheat-sheet
| Command | What it does |
| --- | --- |
| `npm run wp:start` | Start WordPress |
| `npm run wp:stop` | Stop WordPress (data kept) |
| `npm run wp:seed` | Apply defaults + create/seed pages (safe to re-run) |
| `npm run wp:reset` | Wipe the database and re-seed (clean slate) |
| `npm run dev` | Live-reload dev server for CSS/JS |
| `npm run build` | Production asset build |
| `npm run test:e2e` | Playwright smoke + accessibility (needs site running + seeded) |

## Before committing a task's work
- [ ] `npm run lint:css`
- [ ] `npm run lint:js`
- [ ] `composer run lint:php`
- [ ] `npm run build`
- [ ] `npm run test:e2e`   *(needs the site running + seeded)*

## Test the quote flow
1. [ ] Site running + seeded (`wp:start` then `wp:seed`)
2. [ ] Open `/get-a-quote/`, fill it in, submit
3. [ ] You should land on `/thank-you/`
4. [ ] The enquiry shows in **wp-admin → Quote Requests** (CSV export available)

## Turn on the real keys (when ready — Task 21)
Add in **wp-admin → Settings → Site Settings → Quote & email**, or as
`COMPASS_*` constants in `wp-config.php` (safest for secrets — never commit them):
- [ ] **Cloudflare Turnstile** site key + secret (free bot protection)
- [ ] **SMTP** host / port / user / password / encryption / from + notification email

Until these are set, the form still works: spam falls back to the
honeypot/time/rate-limit checks, and email uses the server's default mailer.

## Troubleshooting
- **`localhost:8888` won't load** → Docker Desktop isn't running, or `wp:start` hasn't
  finished. Start Docker, re-run `npm run wp:start`.
- **New page shows 404** → run `npm run wp:seed`.
- **CSS/JS changes not showing** → run `npm run build` (or use `npm run dev`), then
  hard-refresh the browser (`Ctrl+F5`).
- **"network is not available" on start (Windows/VPN)** → already handled; always use the
  `npm run wp:*` commands (they go through `scripts/wp-env.js`).
