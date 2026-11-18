# Compass Digital

**Find your way forward.** Websites, SEO and POS systems.

Marketing website for Compass Digital, a Sri Lankan software and digital services firm.
Built as a classic WordPress PHP theme with a Vite asset build.

## Status
In early setup. See [docs/PROGRESS.md](docs/PROGRESS.md) for the task list and what is next.

## Docs
- [Brand](docs/BRAND.md)
- [Design system](docs/DESIGN.md)
- [SEO](docs/SEO.md)
- [Progress](docs/PROGRESS.md)
- [Working rules for Claude](CLAUDE.md)

## Stack
- WordPress (classic PHP theme, no page builders, no ACF)
- Vite for CSS/JS builds, no jQuery on the front end

## Workflow
- One branch per task: `task/NN-name`
- Conventional commits
- Pull request into `main`

## Local WordPress

A full WordPress site runs on your machine with [`wp-env`](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/)
(Docker). It serves PHP 8.3 + WordPress 7.1.4 at **http://localhost:8888**,
with the theme loaded from `./theme` and custom plugins from `./plugins`.
Rank Math SEO and Query Monitor (dev only) are installed for you.

### One-time setup
1. Install **Docker Desktop** and make sure it is running.
2. Install **Node.js** (18 or newer) and **Git Bash** (comes with Git for Windows).
3. From the project folder, install the tooling:
   ```bash
   npm install
   ```

### Start it
```bash
npm run wp:start
```
The first run downloads WordPress and the plugins, so give it a few minutes.
When it finishes, open **http://localhost:8888**.

### Seed the content
Run this once after the first start to configure the site (pretty permalinks,
Asia/Colombo timezone, search-engine indexing off, comments off, sample content
removed, and the empty pages created with Home set as the front page):
```bash
npm run wp:seed
```

### Log in to the dashboard
- Admin: **http://localhost:8888/wp-admin**
- Username: `admin`
- Password: `password`

### Everyday commands
| Command | What it does |
| --- | --- |
| `npm run wp:start` | Start WordPress (and build the containers on first run). |
| `npm run wp:stop` | Stop WordPress. Your data is kept. |
| `npm run wp:seed` | Apply the Compass Digital defaults and create the pages. |
| `npm run wp:reset` | Wipe the database back to a clean install, then re-seed. |

> **Note:** this local site is set to discourage search engines and must never be
> made public. It is for development only.

> **WordPress version:** pinned to **7.1.4** in `.wp-env.json`. To move to a newer
> release, edit the `core` version and run `npm run wp:reset`.

> **Connectivity workaround (Windows):** the `wp:*` scripts run wp-env through
> `scripts/wp-env.js` instead of calling `wp-env` directly. wp-env detects whether
> it is online with `dns.resolve()`, which fails on some Windows/VPN setups even
> when the internet works (symptom: *"Could not find the current WordPress version
> in the cache and the network is not available."*). The wrapper makes that check
> fall back to the OS resolver. Nothing to configure — just use the `npm run wp:*`
> commands. See the comment in `scripts/wp-env.js` for details.
