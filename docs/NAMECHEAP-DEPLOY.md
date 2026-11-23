# Going live on Namecheap shared hosting — owner runbook

A beginner-friendly, step-by-step guide to putting this site live using Namecheap
shared hosting (cPanel) and the GitHub deploy pipeline already in this repo.

Related docs: [`GO-LIVE.md`](GO-LIVE.md) (full owner checklist),
[`SECRETS.md`](SECRETS.md) (every GitHub secret, by name), [`RESTORE.md`](RESTORE.md)
(backups/restore).

---

## First: how this site actually goes live (read once)

This is **not** a "upload files with FileZilla" site. It is wired for automated
deployment:

- **GitHub builds the code** — runs the Vite build and assembles only your custom
  code: `theme/`, `plugins/compass-core/`, `mu-plugins/`.
- It **copies that over SSH** to the server into a new folder `releases/<timestamp>`,
  then flips a `current` symlink to point at it (instant switch, instant rollback).
- **Push to `main` → deploys to staging automatically.**
  **Push a tag `v1.0.0` → deploys to production, but pauses for you to click Approve.**
- The **database and uploaded images are never touched** by deploys — they live
  permanently on the server. Only code ships.

So "going live" =
1. buy hosting + domain,
2. install WordPress once on the server,
3. give GitHub the SSH keys/paths as secrets,
4. push `main` (→ staging),
5. tag `v1.0.0` and approve (→ production).

---

## ⚠️ Two Namecheap-shared-hosting gotchas (decide before buying)

The pipeline was designed for a server you fully control (a VPS or managed
WordPress host). On Namecheap **shared** hosting it *can* work, but two things in
the committed code will not "just run":

1. **Namecheap SSH uses port `21098`, not the default `22`.** This is now handled
   in code (Task 39): the deploy action takes an optional `SSH_PORT` secret. Just
   set the `SSH_PORT` repository secret to `21098` in Phase 3 — **no code change
   needed**. (Leave it unset on a host that uses port 22.)
2. **The pipeline only ships your custom code** (`wp-content/themes/...`), not full
   WordPress. So a normal WordPress install must already exist on the server, and
   the `current`-symlink docroot has to be wired to reuse that install's core +
   `wp-config.php` + `uploads`. On shared hosting this is the fiddly part.

Good news: Namecheap SSH is **key-based and not IP-locked**, so GitHub's rotating
IPs are fine — that common blocker doesn't bite you. And **WP-CLI** (the `wp`
command the pipeline needs) is usually present — you verify it with one command
below.

If you'd rather not fight shared-hosting limits, a **cheap VPS** (or managed WP
host) makes this pipeline work exactly as written. The rest of this guide assumes
**Namecheap shared (cPanel)**.

---

## Phase 1 — Buy the domain + hosting

1. Go to **namecheap.com**, search your domain (e.g. `compassdigital.lk` or
   `.com`), add to cart.
2. Add a **shared hosting** plan. Pick **Stellar Plus** or **Stellar Business**
   (not the cheapest Stellar) — you want more SSH, inodes, and room for a staging
   subdomain. All include **cPanel**.
3. Check out. If domain + hosting are on the same account, Namecheap auto-links
   them. If the domain is elsewhere, point its **nameservers** to the ones
   Namecheap emails you.
4. Wait for the **"Hosting is active"** email — it gives your **cPanel login URL**,
   **username**, and **server hostname** (like `serverXXX.web-hosting.com`). Keep
   these.

## Phase 2 — One-time server setup (in cPanel)

Log into cPanel (link from the activation email).

### 2a. Install WordPress (the real, persistent install)

- Open **WordPress Toolkit** (or **Softaculous → WordPress**) → **Install**.
- Install on your **main domain** for production. Set admin user/password (save
  them).
- Create a **staging subdomain**: cPanel → **Domains** → create
  `staging.yourdomain.com`, then install a second WordPress there.
  Password-protect its folder (cPanel → **Directory Privacy**) so staging stays
  private.

### 2b. Turn on SSH and note the port

- cPanel → **Manage Shell** (or **SSH Access**) → enable it.
  **Namecheap SSH port is `21098`.** Write it down.

### 2c. Check WP-CLI exists

cPanel → **Terminal**:

```bash
which wp && wp --info
```

A path + version = good. "command not found" means the pipeline's `wp cache flush`
steps won't run — raise it before continuing.

### 2d. Create a deploy-only SSH key (on your Windows PC, PowerShell)

```powershell
ssh-keygen -t ed25519 -f "$HOME\.ssh\compass_deploy" -C "github-deploy" -N '""'
```

This makes `compass_deploy` (private — goes to GitHub) and `compass_deploy.pub`
(public — goes to the server). **Never share the private one.**

- Put the **public** key on the server: cPanel → **SSH Access → Manage SSH Keys →
  Import**, paste the contents of `compass_deploy.pub`, then **Authorize** it.

### 2e. Get the server's host fingerprint (PowerShell — note `-p`)

```powershell
ssh-keyscan -p 21098 serverXXX.web-hosting.com
```

Copy **all** printed lines — that is your `SSH_KNOWN_HOSTS` value.

### 2f. Create the release folders (cPanel → Terminal)

```bash
mkdir -p ~/deploy/production/releases ~/deploy/staging/releases
```

Each site's docroot then needs to serve from `…/current`, reusing the WordPress
core + `wp-config.php` + `uploads` from the install in 2a. **This wiring is the
tricky shared-hosting step.** The clean alternative is to adapt the deploy to
update the existing WP install's `wp-content` directly instead of flipping a
whole-docroot symlink (see "Recommendation" at the end).

## Phase 3 — Give GitHub the secrets (nothing secret goes in chat)

> ### ⚠️ This is the on-switch — deploys are OFF until it's done
>
> Right now the repository has **no secrets, no variables, and no environments**,
> so **every deploy job is skipped**. That is by design: a merge to `main` or a
> `v1.0.0` tag will build successfully but **ship nothing** until the items below
> exist. (`v1.0.0` is already tagged and built — it just skipped the deploy.)
>
> You need **all three**, or the deploy stays skipped:
> 1. **Repository secrets** — the SSH connection (table below).
> 2. **Two Environments** (`staging`, `production`) with their **environment
>    secrets** — the paths/URLs, plus the production *approval reviewer*.
> 3. **Repository variables** `STAGING_URL` and `PRODUCTION_URL` — the literal
>    on-switch. The jobs check `vars.…_URL != ''`; miss these and nothing runs
>    even with every secret set.
>
> After setting all three, **re-run the Deploy workflow on the `v1.0.0` tag**
> (GitHub → Actions → Deploy → the v1.0.0 run → *Re-run all jobs*), or push the
> next tag (e.g. `v1.0.1`). A re-run with the config in place no longer skips.

Repo on GitHub → **Settings → Secrets and variables → Actions**.

**Repository secrets** (Secrets tab):

| Name | Value |
|---|---|
| `SSH_HOST` | `serverXXX.web-hosting.com` |
| `SSH_USER` | your cPanel username |
| `SSH_KEY` | full contents of the **private** `compass_deploy` file |
| `SSH_KNOWN_HOSTS` | the `ssh-keyscan` output from 2e |
| `SSH_PORT` | `21098` (Namecheap's SSH port; omit entirely on a host that uses `22`) |

**Create two Environments** — Settings → **Environments** → New:

- `staging` (no rules).
- `production` → add a **Required reviewer: yourself**. This is what makes a
  `v1.0.0` tag wait for your approval.

**Environment secrets** — add under the matching environment:

| Name | Env | Value |
|---|---|---|
| `STAGING_DEPLOY_PATH` | staging | `/home/USER/deploy/staging` |
| `STAGING_WP_PATH` | staging | staging WordPress docroot |
| `STAGING_URL` | staging | `https://staging.yourdomain.com` |
| `PRODUCTION_DEPLOY_PATH` | production | `/home/USER/deploy/production` |
| `PRODUCTION_WP_PATH` | production | live docroot |
| `PRODUCTION_URL` | production | `https://yourdomain.com` |

**The on-switch** — Settings → Secrets and variables → Actions → **Variables** tab
→ add `STAGING_URL` and `PRODUCTION_URL` again as **variables** (same values).
Until these variables exist, the deploy jobs stay **skipped** on purpose.
(Full list: [`SECRETS.md`](SECRETS.md).)

## Phase 4 — First deploy to staging

- Merge your work into `main` (open a PR from your branch, merge it).
- GitHub → **Actions** tab → watch the **Deploy** workflow. It builds, ships to
  staging, and smoke-tests. A failed smoke test **auto-rolls back**.
- Visit `https://staging.yourdomain.com`, click around, submit a test quote.

## Phase 5 — Owner tasks before production (from `GO-LIVE.md`)

These can't be done in code — do them on the server / WP admin:

- In `wp-config.php` on **live**, add:
  `define( 'WP_ENVIRONMENT_TYPE', 'production' );` — **critical**: anything else
  keeps the whole site `noindex` and Google will drop it.
- Fill the real contact details / social URLs in `mu-plugins/compass-contact.php`,
  and the `[…]` placeholders in `/privacy/`, `/terms/`, `/cookie-policy/`.
- Confirm HTTPS works (cPanel → **SSL/TLS Status**, AutoSSL; force http→https
  redirect).
- WP Admin → **Settings → Site Settings**: paste GA4 ID and Search Console token;
  set Google Maps / reviews links.

## Phase 6 — Go live (the "production yml" moment)

Once staging looks right, on **your PC** (PowerShell), from the repo:

```powershell
git checkout main; git pull; git tag v1.0.0; git push origin v1.0.0
```

- Pushing the `v*.*.*` tag triggers the **production** job in `deploy.yml`.
- It builds, then **pauses** at the `production` environment gate.
- GitHub → **Actions** → the running deploy → **Review deployments → Approve**.
- It ships to live, smoke-tests `/` and `/get-a-quote/`, and rolls back
  automatically if anything 500s.

Future releases: merge to `main` (→ staging), then tag `v1.0.1`, `v1.1.0`, etc. and
approve (→ live).

## Phase 7 — After launch

- Submit `https://yourdomain.com/wp-sitemap.xml` to Google Search Console.
- Run the SEO audit against live: `SITE_URL=https://yourdomain.com npm run seo:audit`.
- Enable the daily **Backup** workflow (add the `BACKUP_S3_*` secrets, see
  [`SECRETS.md`](SECRETS.md)) and do one manual run.
- Re-scan headers at securityheaders.com; enable 2FA on the admin account.

Full post-launch checklist: [`GO-LIVE.md`](GO-LIVE.md).

---

## Recommendation

The custom SSH port is **already solved** in code (set `SSH_PORT=21098`, Task 39).
The one remaining shared-hosting trip-up is Phase 2f — the docroot ↔ `current`
symlink wiring. It goes away if the deploy action is adapted to update the existing
WordPress install's `wp-content` directly instead of flipping a whole-docroot
symlink — the natural fit for shared hosting. Consider doing that as its own task
before the first production deploy (or use a VPS / managed WP host, where the
pipeline works exactly as written).

---

## Quick reference — what triggers what

| You do | GitHub does |
|---|---|
| Merge/push to `main` | Build → deploy to **staging** (auto) |
| Push tag `vX.Y.Z` | Build → **pause** → you Approve → deploy to **production** |
| Smoke test fails | Auto **rollback** to previous release |

_Source of truth: `.github/workflows/deploy.yml`, `.github/actions/ssh-deploy/action.yml`._
