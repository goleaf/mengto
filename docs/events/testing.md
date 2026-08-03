# Event Testing

`EventWorkflowTest` covers the existing route, registration, capacity,
waitlist, invitation, messaging, cancellation, review, report, backfill,
privacy, and localization workflow. `EventLifecycleFoundationTest` adds owner
and team boundaries, lifecycle transitions, occurrence/version snapshots,
multi-pet eligibility, private/unlisted access, idempotent backfill, enum
translation parity, factory states, and production-safe demo seeding.
`EventLifecycleQueryBudgetTest` fixes the idempotent six-event backfill budget
at no more than two statements. `EventLifecycleMigrationTest` runs outside the
normal `RefreshDatabase` transaction so SQLite can prove that the five
additive Point 13 migrations roll back and reapply around populated legacy
event and registration rows.

The repository browser audit now covers the event directory and a recurring
event detail at 1440x900 and 375x812. It checks one `h1` and `main`, accessible
control names, 44px mobile targets, horizontal overflow, raw translation keys,
directory-level private-location disclosure, and browser console errors. The
resulting PNG files and JSON report are runtime artifacts outside the
repository.

## Verification on 2026-08-03

- `php artisan config:clear --no-interaction && php artisan test --compact`:
  2,303 tests passed with 76,179 assertions.
- `php artisan test --compact tests/Feature/Forum/EventLifecycleFoundationTest.php`:
  7 tests passed with 464 assertions.
- event workflow, lifecycle, query-budget, route-preview, and architecture
  selection: 49 tests passed with 27,753 assertions.
- `php artisan test --compact tests/Unit/EventLifecycleMigrationTest.php`:
  1 test passed with 17 assertions.
- isolated SQLite `php artisan migrate:fresh --seed --force --no-interaction`:
  all migrations and seeders completed successfully.
- `BROWSER_BASE_URL=http://127.0.0.1:8013 npm run test:browser:a11y`:
  passed with four event viewport audits and zero console errors.
- `vendor/bin/pint --test`, `vendor/bin/phpstan analyse --no-progress`,
  `composer validate --strict`, Composer/NPM audits, and `npm run build`:
  passed; Larastan reported zero errors and both audits reported zero known
  vulnerabilities.

Provider-backed event payments, tickets, refunds, QR/offline check-in,
tracks/sessions, competition scoring, vendors, volunteers, incidents, and the
other advanced aggregates cannot be reported as tested because their durable
implementations do not exist.
