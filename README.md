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

## Continuous integration

GitHub Actions runs on every pull request and every push to `main`
(`.github/workflows/ci.yml`). It must pass before a PR is merged.

What it checks:

| Job | Checks |
| --- | --- |
| **Lint & static checks** | PHP CodeSniffer (WordPress standard), ESLint, Stylelint, `php -l` syntax check. |
| **Build theme (Vite)** | `npm run build` produces the hashed assets. |
| **E2E, a11y & Lighthouse** | Boots `wp-env`, runs Playwright smoke tests (home, quote page, 404 — load with no console errors), an axe accessibility scan, and Lighthouse CI against the budgets in `CLAUDE.md` (Lighthouse 95+, LCP < 1.8s, CLS < 0.05, JS < 90KB gz, CSS < 40KB gz; INP is checked in the lab as Total Blocking Time < 200ms). |

npm and Composer downloads are cached between runs.
[Dependabot](.github/dependabot.yml) opens weekly update PRs for npm, Composer and the Actions themselves.

### Run the same checks locally
```bash
composer install        # one-time: installs PHPCS + WordPress standard
npm install             # one-time: installs the CI tooling
composer run lint:php   # PHP CodeSniffer
npm run lint:js         # ESLint
npm run lint:css        # Stylelint
npm run build           # Vite build
npm run test:e2e        # Playwright smoke + axe (needs wp-env running + seeded)
npm run lhci            # Lighthouse CI budgets (needs wp-env running)
```

## Turn on branch protection (do this once in GitHub)

CI only *reports* status. GitHub will still let anyone merge a red PR or push
straight to `main` until you switch on branch protection. Do this once, after
the first CI run has appeared on a PR (so the check names exist to pick from).

1. Push this branch and open a pull request into `main` at least once, and let
   the **CI** workflow finish. This makes its job names show up in the settings.
2. In your browser, go to the repository on **github.com**.
3. Click the **Settings** tab (top of the repo, you must be an admin/owner).
4. In the left menu click **Branches**.
5. Under **Branch protection rules** click **Add branch ruleset**
   (or **Add rule** on the classic screen).
6. In **Branch name pattern** type exactly: `main`
7. Tick **Require a pull request before merging**.
   - Under it, set **Required approvals** to `1` (solo project: `0` is fine).
   - Tick **Dismiss stale pull request approvals when new commits are pushed**.
8. Tick **Require status checks to pass before merging**.
   - Tick **Require branches to be up to date before merging**.
   - In the search box add these three checks (type the name, click it):
     - `Lint & static checks`
     - `Build theme (Vite)`
     - `E2E, a11y & Lighthouse`
9. Tick **Require conversation resolution before merging**.
10. Tick **Do not allow bypassing the above settings** (so even admins follow the rule).
11. Leave **Allow force pushes** and **Allow deletions** **unticked**.
12. Click **Create** / **Save changes** at the bottom.

From now on `main` can only change through a pull request whose CI is green.

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
