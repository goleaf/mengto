# Deployment

## Requirements

- PHP `>=8.5.0 <8.6.0`
- Required extensions: Ctype, cURL, DOM, Exif, Fileinfo, Filter, Hash, Intl,
  JSON, Mbstring, OpenSSL, PDO plus selected driver, Session, Tokenizer, XML;
  GD or Imagick when image transformations are enabled; URI for PHP 8.5 URL
  boundaries where used
- Composer 2
- Node `^20.19.0 || >=22.12.0` and npm 10 or newer, matching
  `package.json`
- Writable `storage` and `bootstrap/cache`
- Configured database, cache, session, mail, filesystem, and optional queue
  services

## Current Release Status

The final audit records **NO-GO** in
`docs/reports/final-release-verification.md`. Do not deploy the current tree:
required code, static, generated-evidence, coverage, populated-migration,
readiness, browser, and documentation gates are not green. The repository also
does not yet ship an atomic release-directory switch or a dependency-readiness
probe; `/up` is liveness only.

## Required Release Sequence After The NO-GO Is Cleared

1. Freeze one reviewed commit and attach passing evidence for every applicable
   gate to that exact commit.
2. Create a versioned release directory without changing the active release.
3. As the configured runtime account, run
   `composer install --no-dev --classmap-authoritative --no-interaction`, then
   `npm ci` and `npm run build`; do not overwrite assets in the active release.
4. Configure environment secrets outside the repository and run dependency
   compatibility/preflight checks without exposing them.
5. Quiesce writes or enter a documented maintenance window. Capture one
   correlated, encrypted, checksummed, off-host backup manifest covering the
   database, `storage/app/private`, and required configuration references.
6. Verify the backup in an isolated restore rehearsal. Record RPO/RTO,
   retention, operator, source commit, schema ledger, and object counts.
7. From the versioned release, run `scripts/artisan-runtime migrate --force`
   only after every package-specific populated-data preflight passes. Never
   run `migrate:fresh` or demo seeders in production.
8. Run authenticated database/private-storage/cache/session readiness plus
   critical smoke checks while traffic is still contained. The current
   repository lacks this readiness probe, so this step is presently blocked.
9. Atomically switch the active release, run `scripts/artisan-runtime optimize`,
   restart managed PHP processes and only approved queue workers, then enable
   traffic.
10. Verify liveness, readiness, login, authorization denials, private files,
    forms, critical commands, assets, logs, and request IDs. On a pre-write
    failure, switch back to the prior release; after new-schema writes, retain
    data and use a reviewed forward fix.

The verification record must include the deployed commit, PHP/Laravel/Livewire
versions, migration batch, asset manifest timestamp, `/up` result, one
authenticated request ID, and the operator performing the smoke. A release is
not considered verified merely because dependency installation completed.

If route caching reports a non-cacheable first-party route, fix the route
before release. Framework/package infrastructure routes are evaluated
separately.

## Laravel Runtime Account

Run every production Artisan command through `scripts/artisan-runtime`. The
wrapper executes directly when the caller is `www`, delegates a `root` caller
to `www`, and refuses any other account. Direct `root` execution is prohibited
because mixed-owner files in `storage/framework/views` cause Blade compilation
to fail with `touch(): Utime failed: Operation not permitted` under PHP-FPM.

Before enabling traffic, verify the runtime directories and warmed files:

```bash
chown -R www:www storage bootstrap/cache
scripts/artisan-runtime view:cache
find storage bootstrap/cache -type f ! -user www -print
find storage bootstrap/cache -type d ! -user www -print
runuser -u www -- test -r bootstrap/cache
runuser -u www -- test -w storage/framework
```

Both `find` commands must print nothing and both permission probes must return
zero. `PAWCIRCLE_RUNTIME_USER` may override the account only when the PHP-FPM
pool intentionally uses a different service identity.

## Environment

`APP_ENV=production`, `APP_DEBUG=false`, a valid `APP_KEY`, trusted application
URL, database, cache/session, mail, private filesystem, and logging values are
required. New environment variables must have safe `.env.example` and config
entries; runtime code reads `config()`, never `env()`.

### Email verification mode

`EMAIL_VERIFICATION_ENABLED=true` is the secure default. Set it to exactly
`false` only after accepting that new and activated accounts will receive an
`email_verified_at` timestamp without independent proof that they own the
address. Authentication, active-account checks, policies, scoped private
access, and step-up verification remain mandatory.

For an intentional transition to disabled mode, use this order:

1. Set `EMAIL_VERIFICATION_ENABLED=false` and run
   `scripts/artisan-runtime config:clear`.
2. Back up the exact configured database and verify the backup integrity.
3. Inspect and apply pending migrations with
   `scripts/artisan-runtime migrate:status` and `migrate --force`.
4. Run `DatabaseSeeder` only in an explicitly allowed non-production
   environment.
5. Run `scripts/artisan-runtime auth:activate-pending-email-users --dry-run`,
   then run the command without `--dry-run` and repeat it to prove
   idempotence. Only active pending accounts are changed; blocked and suspended
   accounts remain pending and each changed account receives a non-sensitive
   audit record.
6. Verify zero active accounts remain pending, check database integrity, warm
   caches, and smoke registration plus authenticated portal access.

Re-enabling the flag restores verification for future registrations and
middleware checks. It deliberately does not erase existing verification
timestamps; any account-level reversal requires an audited recovery decision.

Set `IMAGE_DRIVER=gd` or `IMAGE_DRIVER=imagick` and install the matching PHP
extension. Public photo uploads depend on the production
`intervention/image:^4.0` package through Laravel's first-party image API; the
release smoke must upload one image and confirm the stored result is a bounded
WebP file.

Use a rotating production log destination. The repository default is
`LOG_CHANNEL=stack`, `LOG_STACK=daily`, and `LOG_DAILY_DAYS=14`; an external
collector may replace the destination while preserving the ownership,
redaction, and retention contract in `docs/operations.md`.

Slow-request records default to `SLOW_REQUEST_LOGGING_ENABLED=true` and
`SLOW_REQUEST_THRESHOLD_MS=1000`. A non-positive or invalid threshold disables
the reporter. Operators may disable it explicitly during a logging incident;
the request-ID header remains active. Detailed records are capped at 60 per
named route and response status per minute and contain no URL, query, body,
headers, session value, private location, or media payload.

Production Vite filenames are content hashed. The web server should return
those JS, CSS, font, and image files with a long-lived immutable cache policy.
The 2026-08-30 audit observed only a 12-hour CSS/JS maximum age and no explicit
font cache header on the active host; deployment remains responsible for
correcting and rechecking that host configuration before promotion.

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

Private disks and product-media disks must not map to unrestricted public
URLs. Do not run `php artisan storage:link`; `config/filesystems.php` has no
public link contract. Remove any legacy `public/storage` link during rollout
before enabling traffic. Verify that `storage.local` routes are absent and
that forum, marketplace, lost/found, and sighting media load only through the
authenticated `portal-media.show` route. Verify file permissions, token
behaviour, retention, traversal/symlink rejection, and partial-upload cleanup.

Public marketplace, lost/found, sighting, and forum-topic images are processed
synchronously through `StorePublicImage`: orient, scale down to the configured
box, encode, and store with a generated name. Capacity planning must include
the CPU and memory cost of the largest accepted image dimensions.

## Rollback

Prefer forward fixes after an additive migration. Rolling application code
back is safe only while the schema remains backward compatible. Never restore
old code that cannot understand new mandatory data. Restore database backup
only through an approved incident process.

### Place submission and merge redirects

The place-submission schema is additive. Before any submission, moderation, or
merge write, the complete migration ledger can be rolled back and reapplied by
the isolated lifecycle verifier. Once a source identifier has more than one
merge generation, the active-identifier migration deliberately refuses its
`down()` path: collapsing those rows into the former globally unique column
would discard field provenance and redirect audit history. Keep the additive
schema, disable the affected mutation if containment is required, and deploy an
audited forward fix. A database restore is an incident procedure, not an
application rollback. The merge Action itself remains transactional: a failed
copy or redirect write rolls back the place state, copied facts, aliases, event,
and notification dispatch together.

## Progressive Pet Profile Release

The `pet.creation.0036-pet.creation.0058` workspace adds no migration, backfill,
queue dependency, public file route, environment value, or destructive data
rewrite. Deploy it with the normal release sequence. Smoke `/compose/pet` to
the canonical creation redirect, create a private draft, open multiple
`?step=` destinations, verify a mutation-free skip, save one descriptive step,
and confirm a role without `change-microchip` receives no protected form or
completion signal. Rollback is application-code-only while the existing
profile/fact schema remains available; facts written by the release stay valid
for the previous model contract.
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

## Topic Lifecycle Migration

`2026_07_31_001250_create_forum_topic_lifecycle_tables.php` adds topic
timestamps/indexes plus category rule, event, update-request, and legal-hold
tables without rewriting or deleting existing content. Deploy the migration
before lifecycle-capable application code, then run the production-safe
`ForumSystemSeeder` only when the release procedure explicitly includes
reference synchronization.

Verify public/owner/moderator visibility, one stale warning, one archive and
restore, one authorized redirect, one active legal hold, old slug continuity,
and preservation of answers, reactions, subscriptions, reports, and
attachments. Do not run `ForumTopicLifecycleDemoSeeder` in production. After
production lifecycle writes exist, retain the additive schema and recover with
an audited forward transition or migration.

## Topic-Type Schema Runtime Synchronization

This runtime package adds no migration and no environment key. Deploy the code
after the existing `forum_topic_types` and normalized `forum_topics` columns
are present. When the release procedure includes reference synchronization,
run the production-safe forum system seeder; it upserts system definitions by
stable key, preserves numeric IDs and custom definitions, and invalidates the
versioned schema cache.

After deployment, verify one generic topic create/update retains the current
schema version, an inactive type is rejected, and answer-rating,
accepted-answer, and notification mutations obey the active configuration.
Rebuild application caches normally. A database or cache failure must remain
visible and fail the mutation; the immutable catalogue fallback is only for an
existing but empty definition table during bootstrap.

## Complete Migration Lifecycle Verification

Before a release that changes schema, run both isolated controls:

```bash
php scripts/verify-fresh-database.php
php scripts/verify-migration-cycle.php
```

The lifecycle verifier asserts a random canonical temporary path, applies
every migration, rolls the complete ledger back to zero, reapplies every file,
and repeats the production-safe seed. It always disconnects and deletes the
temporary SQLite file. It does not replace package-specific populated-data,
forward-fix, and retention review.

## Pet Profile Foundation Migration

`2026_07_31_001270_create_pet_profile_foundation.php` expands the existing pet
aggregate and creates manager, privacy, lifecycle-event, slug-alias, and fact
tables without deleting or remapping any pet or adjacent-domain record.

1. Back up and record existing pet and integration counts.
2. Run `scripts/artisan-runtime migrate --force`.
3. Run `scripts/artisan-runtime pets:backfill-profile-foundation --chunk=500`.
4. Rerun the backfill and require zero newly created manager/privacy/alias rows.
5. Smoke create, manage, invitation, privacy, lifecycle, stable URL, and direct
   authorization paths.
6. Rebuild config, event, route, and view caches.

Rollback was verified on an isolated populated SQLite database before release.
After production writes use the new tables, keep the additive schema and use a
reviewed forward fix; do not drop pet authorization or audit history.

## Social Relationship Foundation Migration

`2026_07_31_182248_create_social_relationship_foundation.php` adds actor,
settings, request, relationship, and event tables without rewriting any
authoritative profile or encrypted compatibility state.

1. Back up and record user, pet, expert, group, and legacy social-state counts.
2. Run `scripts/artisan-runtime migrate --force`.
3. Run `scripts/artisan-runtime social:backfill-actors --dry-run --chunk=500`.
4. Run the write backfill twice and require stable adapter/settings counts.
5. Smoke the authenticated `/circle/social` workflow and one denied direct
   request from an unauthorized user.
6. Rebuild config, event, route, and view caches.

Rollback is safe only before production social writes depend on these tables.
After that point, retain relationship/event evidence and deploy a reviewed
forward fix.

## Social Relationship Safety Migration

`2026_07_31_235900_add_social_request_safety.php` adds account blocks, request
fingerprint/risk/repeat fields, account-block event attribution, and report
idempotency. `2026_07_31_235910_add_social_account_block_foreign_key_indexes.php`
adds the leading foreign-key indexes as a separately reversible schema concern.
Neither migration converts an existing profile block into a broader account
block because the historical intent is ambiguous.

1. Deploy the foundation and complete actor backfill first.
2. Run `scripts/artisan-runtime migrate --force` and verify every new foreign key has a
   leading index.
3. Smoke recipient decline-and-prevent, report without block, report with
   account block, directory exclusion, and unblock without restoration.
4. Confirm request limits aggregate across two represented pet profiles.
5. Rebuild application caches and run the desktop/mobile/320px social browser
   audit.

Rollback is limited to the pre-dependency window. Once account blocks or
private reports exist, preserve their evidence and use a forward migration.

## Pet Progressive Completion Deployment

The twelve-step completion package adds no migration, table, backfill, queue,
cache store, environment key, or public-file route. Deploy the application
code and translations together, then rebuild config, event, route, and view
caches. Smoke at least basics, photos, privacy, protected documents, preview,
an invalid `?step=` value, and a denied microchip write by a non-critical role.

Confirm the existing encrypted casts can read `profile_data` and
`pet_profile_facts.value`, the `microchip-record` key is present in cached
configuration, and logs or rendered HTML contain no identifier. Rollback is an
application-code rollback; already stored versioned facts remain valid and
must not be deleted.

## Pet Breed Origin Migration

`2026_08_04_000100_add_breed_origin_to_pet_profiles.php` adds one nullable
profile type and the indexed normalized origin relation. It performs no
ambiguous legacy backfill: existing breed strings remain readable and become
owner-reported compatibility data only when the profile is edited.

1. Back up and record pet-profile and domestic-classification counts.
2. Run `scripts/artisan-runtime migrate --force` and inspect the new FK/index shape.
3. Smoke one legacy string, one explicit unknown profile, and one mixed profile
   with two different source/confidence values.
4. Confirm the public projection shows provenance without internal keys or
   private documents and that a photo change does not change confidence.
5. Rebuild config, event, route, and view caches.

Rollback is safe only before normalized origin rows are relied upon. After
production writes begin, retain the additive schema and deploy a reviewed
forward fix; do not guess legacy strings into taxonomy classifications.

## Pet Appearance Color Deployment

The structured appearance-color package adds no migration, table, index,
backfill, queue, cache store, environment key, or public-file route. Deploy the
application code and all three locale catalogues together, then rebuild config,
event, route, and view caches.

Smoke an empty profile, a primary color with four different additional colors,
all three patterns, feather/scale/seasonal clarification, one invalid direct
payload, reload restoration, and the public projection. Confirm identifying
marks remain absent from public HTML and the encrypted `profile_data` cast can
read both legacy and schema-versioned payloads. Rollback is an application-code
rollback; already stored schema-versioned data is preserved and ignored safely
by older compatibility readers.

## Pet Species-Aware Body Covering Deployment

The body-covering package adds no migration, table, index, backfill, queue,
cache store, environment key, or public-file route. Deploy the application code
and EN, LT, and RU catalogues together, then rebuild config, event, route, and
view caches.

Smoke at least one coat-bearing, bird, reptile or fish, and horse profile.
Confirm irrelevant fields stay absent, hairlessness clears coat facts, invalid
direct enum and boolean payloads fail, values survive reload, and the public
projection excludes the private skin observation. Rollback is an application-
code rollback; stored schema-versioned data remains encrypted and is ignored
safely by older compatibility readers.

## Pet Identifying Marks Migration

`2026_08_04_024152_create_pet_profile_identifying_marks_table.php` adds the
normalized encrypted identifying-mark relation and its manager, public, and
actor indexes. It does not guess structured rows from the historical private
free-text value, create public data, or require a queue, cache store,
environment key, or public-file route.

1. Back up and record pet-profile counts, then run
   `scripts/artisan-runtime migrate --force` and inspect the foreign keys and indexes.
2. Smoke an empty profile, one public mark, one private-verification mark,
   reorder, removal/retirement, invalid direct input, and reload restoration.
3. Confirm the public route receives only the active public row and rendered
   HTML contains neither private descriptions nor encrypted database values.
4. Rebuild config, event, route, and view caches.

Rollback is safe only before production writes depend on this relation. After
that point retain the encrypted proof and use a reviewed forward migration;
do not destroy retired evidence or copy it into the legacy free-text field.

## Pet General Size Category Migration

`2026_08_04_034056_add_size_category_to_pet_profiles_table.php` adds one
nullable controlled scalar and `(size_category, status, id)` index. It does
not backfill, infer, or publish a value and adds no queue, cache store,
environment key, or public-file route.

1. Back up and record pet-profile counts, then run
   `scripts/artisan-runtime migrate --force` and inspect the column and composite index.
2. Confirm existing rows remain null and a manager can save, clear, and reload
   each controlled value while forged direct input fails.
3. Confirm the public profile labels a recorded value as broad manager-provided
   context and exposes neither measurement nor medical claims.
4. Rebuild config, event, route, and view caches.

Rollback drops only the new index and nullable column and is safe before a
deployed consumer depends on recorded categories. After production values are
used, retain them and deploy a reviewed forward fix rather than silently
discarding user-supplied facts.

## Place Submission Publication Migration

`2026_08_30_120000_create_place_submission_publication_tables.php` is additive.
It adds normalized and merge-destination columns to `places`, backfills
identity values in bounded chunks, then creates the review, fact, candidate,
event, redirect, identity-lock, and database-notification tables with
restrictive foreign keys and leading indexes.

Back up the database and record place counts before deployment. After migrate,
verify normalized coverage and foreign keys, then smoke submission, duplicate
review, publication, merge redirect, and protected redirect denial. Rollback
is safe only before submissions or merges exist. After production writes, use
a reviewed forward fix and retain audit, provenance, and redirect history.

## Unreleased Event And Places Schema Packages

The following current packages are **not production-cleared**. Their source
exists, but exact populated-data preflight, adapter behavior, lock/duration,
post-migrate smoke, rollback cutoff, and forward-recovery evidence is not yet
complete:

| Migration | Package | Current release disposition |
| --- | --- | --- |
| `2026_08_30_081125_create_event_participation_workflow.php` | Participation snapshots, eligibility, capacity, waitlist, notification intents | Empty SQLite rollback/reapply passes in the audit tree; active-scope collision preflight and production-adapter DDL atomicity remain unproved |
| `2026_08_30_100100_create_forum_event_competition_tables.php` | Competition categories, entries, judges, scoring, results, appeals | Implementation present; factories, manifest, reachable workflow, populated lifecycle, and release evidence incomplete |
| `2026_08_30_121000_add_active_identifier_to_place_merge_redirects.php` | Active merge identifier | Package-specific populated smoke/recovery record missing |
| `2026_08_30_122000_create_place_submission_detection_lock.php` | Duplicate-detection lock | Lock contention, timeout, cleanup, and recovery runbook missing |
| `2026_08_30_130000_create_place_management_claim_tables.php` | Management claims/authority | Provider/reviewer operations and populated recovery runbook incomplete |
| `2026_08_30_230000_create_place_contribution_workflows.php` | Relational corrections, warnings, reviews, and questions | Compatibility migration and moderation operations remain incomplete |
| `2026_08_30_240000_create_place_presentation_privacy_tables.php` | Media, invitations, presentation privacy | Empty SQLite rollback defect corrected in the audit tree; production-write rollback remains forward-only |
| `2026_08_30_250000_create_place_emergency_fact_tables.php` | Emergency facts | Canonical schedule/service population and emergency-ranking smoke incomplete |
| `2026_08_30_260000_add_measured_performance_indexes.php` | Measured query indexes | Concurrent working-tree migration; not part of the published baseline and not release-reviewed |

Do not apply these packages merely because the empty-database lifecycle passes.
After writes exist, preserve audit, access, registration, contribution,
competition, media, and emergency facts and recover through an additive
forward migration.
