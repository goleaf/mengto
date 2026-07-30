# Deployment Checklist

## Before Deployment

- Create and verify a database and private-storage backup.
- Review the release diff, migration plan, rollback path, dependency audits,
  environment keys, storage permissions, and disk capacity.
- Set `APP_ENV=production`, `APP_DEBUG=false`, the canonical `APP_URL`, and
  production cache/session/mail/database drivers through the environment.
- Keep secrets outside the repository and read them through config files.
- Install Composer dependencies without development packages and build assets.

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
```

Run destructive or large-table migrations only through a separately reviewed
expand/backfill/contract plan. Do not automate a destructive refresh.

## Runtime

- Point the public web root at `public/`; private storage must not be web served.
- Configure HTTPS, least-privilege filesystem/database users, log rotation,
  temporary-file cleanup, backups, health-check access, and process supervision
  for any queue workers actually used.
- Ensure cache and session drivers match available infrastructure.
- Do not expose Telescope, debug pages, logs, health internals, or private
  exports without authorization and retention limits.

## Smoke Checks

After deployment verify the health endpoint according to its access policy,
home page, ownership denials, create/update forms, private medical/care/device
headers, file uploads/downloads, temporary link expiry/revocation, marketplace
mutations, and production assets. Confirm the migration state and inspect logs
for new exceptions without exposing them to users.
