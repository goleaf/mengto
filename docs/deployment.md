# Deployment

## Requirements

- PHP `>=8.5.0 <8.6.0`
- Required extensions: Ctype, cURL, DOM, Fileinfo, Filter, Hash, Intl, JSON,
  Mbstring, OpenSSL, PDO plus selected driver, Session, Tokenizer, XML; GD or
  Imagick when image transformations are enabled; URI for PHP 8.5 URL
  boundaries where used
- Composer 2
- Node 22 or another Vite-supported LTS/current runtime
- Writable `storage` and `bootstrap/cache`
- Configured database, cache, session, mail, filesystem, and optional queue
  services

## Release Sequence

1. Put a risk-appropriate backup and rollback/forward-fix plan in place.
2. Deploy the reviewed commit.
3. `composer install --no-dev --classmap-authoritative --no-interaction`
4. `npm ci`
5. `npm run build`
6. Configure environment secrets outside the repository.
7. `php artisan migrate --force`
8. Never run demo seeders in production.
9. `php artisan optimize`
10. Restart managed PHP processes and approved queue workers.
11. Verify health, login, permissions, private resources, forms, downloads,
    critical commands, and assets.

The verification record must include the deployed commit, PHP/Laravel/Livewire
versions, migration batch, asset manifest timestamp, `/up` result, one
authenticated request ID, and the operator performing the smoke. A release is
not considered verified merely because dependency installation completed.

If route caching reports a non-cacheable first-party route, fix the route
before release. Framework/package infrastructure routes are evaluated
separately.

## Environment

`APP_ENV=production`, `APP_DEBUG=false`, a valid `APP_KEY`, trusted application
URL, database, cache/session, mail, private filesystem, and logging values are
required. New environment variables must have safe `.env.example` and config
entries; runtime code reads `config()`, never `env()`.

Use a rotating production log destination. The repository default is
`LOG_CHANNEL=stack`, `LOG_STACK=daily`, and `LOG_DAILY_DAYS=14`; an external
collector may replace the destination while preserving the ownership,
redaction, and retention contract in `docs/operations.md`.

## Database

- Back up before risky migrations.
- Use expand-and-contract.
- Do not modify historical production migrations.
- Do not run `migrate:fresh`.
- Backfills are bounded, idempotent, resumable, observable, and separately
  verified.

## Seeding

Normal production deployment does not run `DatabaseSeeder`. Explicit
idempotent reference/permission seeders may run only when a release requires
them and the command is documented.

## Queue And Scheduled Work

Enable a worker/scheduler only when the release contains documented jobs/tasks
and operations monitors them. Critical synchronous fallback remains available
for care, medical, search, and device safety.

## Files

Private disks must not map to unrestricted public URLs. A public storage link
is created only for deliberately public assets. Verify file permissions,
temporary URL behaviour, retention, and partial-upload cleanup.

## Rollback

Prefer forward fixes after an additive migration. Rolling application code
back is safe only while the schema remains backward compatible. Never restore
old code that cannot understand new mandatory data. Restore database backup
only through an approved incident process.
