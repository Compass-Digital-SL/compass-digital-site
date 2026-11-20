# Secrets

Every GitHub Actions secret the pipelines need, **by name only**. No value is
ever written here, in the workflows, or asked for in chat — you paste each value
**into GitHub only**.

## Where to add them

GitHub → the repo → **Settings → Secrets and variables → Actions**.

- **Repository secrets** (used by every workflow): the SSH, Turnstile, SMTP and
  backup secrets below.
- **Environment secrets** (scoped per environment — create the environments
  first, see "Environments" at the end): the `STAGING_*` and `PRODUCTION_*`
  values. Putting the production ones on the `production` environment is what
  lets its **required-reviewer** approval gate them.

Add each by its exact name. After the first production deploy you can rotate any
of them here without touching code.

## Deploy — SSH (repository secrets)

| Name | What it is |
|------|-----------|
| `SSH_HOST` | Hostname/IP of the server the site runs on (e.g. `server123.webhost.com`). |
| `SSH_USER` | SSH/SFTP username for that host. |
| `SSH_KEY` | **Private** SSH key (full PEM, incl. the BEGIN/END lines) whose public half is in the host's `~/.ssh/authorized_keys`. Use a deploy-only key, not your personal one. |
| `SSH_KNOWN_HOSTS` | Output of `ssh-keyscan <SSH_HOST>` — pins the host key so the deploy can't be MITM'd. Run `ssh-keyscan server123.webhost.com` and paste all lines. |

## Deploy — paths & URLs (environment secrets)

Add under the matching environment (`staging` / `production`).

| Name | Environment | What it is |
|------|-------------|-----------|
| `STAGING_DEPLOY_PATH` | staging | Base dir that holds `releases/` and the `current` symlink, e.g. `/home/user/deploy/staging`. |
| `STAGING_WP_PATH` | staging | WordPress docroot on staging where `wp-cli` runs (often `.../current` or the web root the symlink is served from). |
| `STAGING_URL` | staging | Public staging URL, e.g. `https://staging.compassdigital.lk`. |
| `PRODUCTION_DEPLOY_PATH` | production | Same as above, for live, e.g. `/home/user/deploy/production`. |
| `PRODUCTION_WP_PATH` | production | Live WordPress docroot. |
| `PRODUCTION_URL` | production | Live site URL, e.g. `https://compassdigital.lk`. |

> **Also add `STAGING_URL` / `PRODUCTION_URL` as _repository variables_**
> (Settings → Secrets and variables → Actions → Variables), same values.
> They are the on-switch: until `STAGING_URL` is set the staging deploy job is
> **skipped** (main stays green before hosting exists); same for
> `PRODUCTION_URL` and tag deploys. They also feed the environment's display
> link, where GitHub does not allow secrets.

> The web server's docroot should point at `<DEPLOY_PATH>/current` so flipping the
> symlink switches the live code atomically. Set this up once on the host.

## Turnstile (repository secrets)

Cloudflare Turnstile protects the quote form (Task 15 / 20).

| Name | What it is |
|------|-----------|
| `TURNSTILE_SITE_KEY` | Public site key (rendered in the form). |
| `TURNSTILE_SECRET_KEY` | Secret key used server-side to verify the token. |

> These are consumed by the site at runtime (via `wp-config.php` constants or
> Site Settings on the server). They are listed here so every name lives in one
> place; set the live values on the host, not only in GitHub, if the theme reads
> them from `wp-config.php`.

## SMTP (repository secrets)

Transactional email for quote notifications.

| Name | What it is |
|------|-----------|
| `SMTP_HOST` | SMTP server hostname. |
| `SMTP_PORT` | Port (usually `587` for STARTTLS). |
| `SMTP_USER` | SMTP username. |
| `SMTP_PASS` | SMTP password / app password. |

> Like Turnstile, these are site-runtime values — set them on the host
> (`wp-config.php` / mail plugin). Kept here so the full secret inventory is one list.

## Offsite backups (repository secrets)

Used by `.github/workflows/backup.yml`. Any S3-compatible store works
(AWS S3, Backblaze B2, Cloudflare R2, Wasabi).

| Name | What it is |
|------|-----------|
| `BACKUP_S3_BUCKET` | Bucket name backups are written to. |
| `BACKUP_S3_ACCESS_KEY` | Access key ID. |
| `BACKUP_S3_SECRET_KEY` | Secret access key. |
| `BACKUP_S3_REGION` | Region (e.g. `us-east-1`; for R2 use `auto`). |
| `BACKUP_S3_ENDPOINT` | Custom endpoint URL for non-AWS providers (e.g. `https://<acct>.r2.cloudflarestorage.com`). Leave **unset** for AWS S3. |

## Environments (do this once, before the first deploy)

GitHub → **Settings → Environments**:

1. Create **`staging`** — no protection rules needed (auto-deploys on merge to `main`).
2. Create **`production`** — add a **Required reviewer** (yourself). This is the
   manual approval gate: a `v1.0.0` tag build pauses until you approve it.
3. Add the `STAGING_*` / `PRODUCTION_*` secrets under the matching environment.

## Staging must stay private

Staging is set to **noindex + password protected** on the server, not in code:

- Add HTTP Basic Auth (host control panel or `.htpasswd`) over the staging docroot.
- Keep `WP_ENVIRONMENT_TYPE=staging` so the theme serves `noindex` + `Disallow: /`
  (see `docs/GO-LIVE.md` → Environment). Never set it to `production` on staging.
