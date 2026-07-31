# Deployment

## Requirements

- PHP `>=8.5.0 <8.6.0`
- Required extensions: Ctype, cURL, DOM, Exif, Fileinfo, Filter, Hash, Intl,
  JSON, Mbstring, OpenSSL, PDO plus selected driver, Session, Tokenizer, XML;
  GD or Imagick when image transformations are enabled; URI for PHP 8.5 URL
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

Set `IMAGE_DRIVER=gd` or `IMAGE_DRIVER=imagick` and install the matching PHP
extension. Public photo uploads depend on the production
`intervention/image:^4.0` package through Laravel's first-party image API; the
release smoke must upload one image and confirm the stored result is a bounded
WebP file.

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

Public marketplace, lost/found, sighting, and forum-topic images are processed
synchronously through `StorePublicImage`: orient, scale down to the configured
box, encode, and store with a generated name. Capacity planning must include
the CPU and memory cost of the largest accepted image dimensions.

## Rollback

Prefer forward fixes after an additive migration. Rolling application code
back is safe only while the schema remains backward compatible. Never restore
old code that cannot understand new mandatory data. Restore database backup
only through an approved incident process.
## Event Migration And Backfill

`2026_07_31_001230_create_forum_event_tables.php` adds event tables,
constraints, indexes, and the nullable group-activity link without rewriting
legacy data. `BackfillForumEvents` then converts first-party catalogue records
and unlinked group activities using stable keys and idempotent lookups.

After migration, run the production-safe event backfill through the normal
forum system seeder, then verify old stable URLs, event counts, group links,
registration counts, and protected fields. Rollback is safe only before event
data is used. After production writes, recover through a forward fix; do not
drop event tables.

## Expert Session Migration

`2026_07_31_001240_create_forum_expert_session_tables.php` is additive and
does not rewrite topics, answers, guides, events, consultations, profiles, or
credentials. Run migrations, then the production-safe forum system seeder.
Verify host qualification, queue privacy, source links, archive retention, and
the public disclaimer. Demo seeding remains forbidden outside configured
non-production environments. After user data exists, use a forward fix rather
than dropping the session tables.
