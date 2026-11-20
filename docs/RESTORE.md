# Backup & restore

The daily job (`.github/workflows/backup.yml`, 02:15 UTC) pushes two dated files
to offsite S3-compatible storage:

- `db/db-<stamp>.sql.gz` — full database dump (`wp db export`, utf8mb4).
- `uploads/uploads-<stamp>.tar.gz` — `wp-content/uploads` media.

Backups older than 30 days are pruned automatically. Secret names are in
`docs/SECRETS.md` (`BACKUP_S3_*`). You can also trigger a backup any time from
GitHub → Actions → **Backup** → **Run workflow**.

## Rolling back a bad deploy (no backup needed)

A deploy keeps the last 5 releases under `<DEPLOY_PATH>/releases/`, and `current`
is a symlink. The pipeline auto-rolls back if the smoke test fails. To roll back
manually, SSH to the host:

```sh
cd <DEPLOY_PATH>/releases
ls -1dt */            # newest first — pick the previous good one
ln -sfn "<DEPLOY_PATH>/releases/<good-release>" "<DEPLOY_PATH>/current.tmp"
mv -Tf "<DEPLOY_PATH>/current.tmp" "<DEPLOY_PATH>/current"
wp --path=<WP_PATH> cache flush
```

Code-only rollback never touches uploads or the database.

## Restoring from an offsite backup

Use this if data (not just code) was lost. You need the `BACKUP_S3_*` credentials
and the AWS CLI (`aws`); add `--endpoint-url <BACKUP_S3_ENDPOINT>` for non-AWS stores.

1. **List and fetch** the backup to restore (pick a `<stamp>`):

   ```sh
   aws s3 ls s3://<BUCKET>/db/
   aws s3 cp s3://<BUCKET>/db/db-<stamp>.sql.gz .
   aws s3 cp s3://<BUCKET>/uploads/uploads-<stamp>.tar.gz .
   ```

2. **Database** — on the host (or copy the file there first):

   ```sh
   gunzip -c db-<stamp>.sql.gz | wp --path=<WP_PATH> db import -
   wp --path=<WP_PATH> cache flush
   ```

   > Importing replaces the current database. Take a fresh `wp db export` first if
   > the live data is still partly good.

3. **Uploads** — extract and sync back into place:

   ```sh
   tar -xzf uploads-<stamp>.tar.gz          # -> ./uploads/
   rsync -a uploads/ <WP_PATH>/wp-content/uploads/
   ```

4. **Verify** — load the site, check media renders and recent content is present.
   If the URL changed, run `wp search-replace <old-url> <new-url> --skip-columns=guid`.

## Restore drill

Twice a year, restore the latest DB + uploads into the **staging** environment and
confirm the site loads. A backup you have never restored is not a backup.
