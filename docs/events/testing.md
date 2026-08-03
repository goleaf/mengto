# Event Testing

`EventWorkflowTest` covers the existing route, registration, capacity,
waitlist, invitation, messaging, cancellation, review, report, backfill,
privacy, and localization workflow. `EventLifecycleFoundationTest` adds owner
and team boundaries, lifecycle transitions, occurrence/version snapshots,
multi-pet eligibility, private/unlisted access, idempotent backfill, enum
translation parity, factory states, and production-safe demo seeding.
`EventScheduleWorkflowTest` covers reversible schema, policy boundaries,
idempotent session creation, encrypted room data, occurrence/timezone/capacity
validation, room/track/staff conflicts, audited overrides, private staff and
draft visibility, Livewire create/edit, translations, and factories.
`EventLifecycleQueryBudgetTest` fixes the idempotent six-event backfill budget
at no more than two statements. `EventLifecycleMigrationTest` runs outside the
normal `RefreshDatabase` transaction so SQLite can prove that the five
additive lifecycle migrations roll back and reapply around populated legacy
event and registration rows. The schedule test independently rolls back and
reapplies the sixth Point 13 schedule migration.

`OrganizationAuthorityFoundationTest` covers tenant creation, signed
single-use invitation response, token-state privacy, removal and historical
attribution, specialist role separation, independent suspension capabilities,
wrong-tenant and former-staff event access, emergency safety access,
organization event creation, invitation, registration and check-in boundaries,
workspace data minimization, EN/LT/RU labels, factories, idempotent guarded
seeding, and bounded directory queries.

`PlaceAuthorityFoundationTest` covers reversible indexed schema, encrypted
exact fields and versions, public projection, account/purpose/expiry grant
boundaries, audited reveal, grant revocation after a move, former organization
staff, unlisted candidate isolation, confirmed-registration enforcement for
attendance grants, event place/venue linkage, Livewire state minimization,
factories, and guarded repeat seeding. `PlaceDirectoryTest`
proves the server-rendered directory and dynamic detail route consume the same
persisted authority without exposing private or archived records.

The repository browser audit covers the event directory, a recurring event,
the three-session conference schedule, the canonical place directory/detail,
and the organization directory and workspace at 1440x900 and 375x812. It
checks one `h1` and `main`, accessible control names, 44px mobile targets,
horizontal overflow, raw translation keys, private-location and
verification-evidence disclosure, persisted place cards, expected session
count, and browser console errors. PNG files and the JSON report remain
runtime artifacts outside the repository.

## Verification on 2026-08-03

- `composer test`: 2,362 tests passed with 78,760 assertions.
- schedule workflow: 7 tests passed with 131 assertions; the 30-session
  projection used five queries and 17,654 bytes.
- event workflow, lifecycle, query-budget, route-preview, lifecycle migration,
  and schedule selection: 37 tests passed with 781 assertions.
- architecture: 20 tests passed with 27,685 assertions; localization: 7 tests
  passed with 30,162 assertions; factory/seeder inventory: 1,557 tests passed
  with 4,613 assertions; schema integrity: 2 tests passed with 3 assertions.
- `php scripts/verify-fresh-database.php`: 118 migrations and 200 tables;
  complete seed and repeat seed both exited `0` and preserved five users.
- `BROWSER_BASE_URL=http://127.0.0.1:8013 npm run test:browser:a11y`:
  passed with six event viewport audits and zero console errors.
- targeted Pint, `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`,
  `composer validate --strict`, Composer/NPM audits, and `npm run build`:
  passed; Larastan reported zero errors and both audits reported zero known
  vulnerabilities.

The later organization-authority checkpoint passed 14 focused tests with 109
assertions and 39 organization/event lifecycle tests with 702 assertions.
The final serial suite passed 2,484 tests with 80,398 assertions in 163.378
seconds. Fresh and rollback/reapply verification covered 121 migrations and
205 tables; initial and repeated seeding both exited `0`. Four organization
browser audits passed with no overflow, unnamed controls, raw organization
keys, private evidence leaks, undersized mobile controls, or console errors.
The exact checkpoint is recorded in
`docs/plans/portal-organization-authority-foundation-work-package.md`.

The place-authority checkpoint passed 20 focused tests with 153 assertions,
the full serial suite passed 2,579 tests with 81,626 assertions, and isolated
fresh/repeat seeding covered 126 migrations and 211 tables with stable entity
counts. Four place browser audits passed with no overflow, unnamed controls,
raw place keys, private exact-location leaks, undersized mobile controls, or
console errors. Place-card height is now measured and bounded at 480px for the
desktop split view and 720px for mobile; observed ranges were 384-473px and
614-654px. The first run found a 16x16 `open_now` checkbox target; the control
was corrected to 44px before the complete browser rerun passed. The
exact checkpoint is recorded in
`docs/plans/portal-place-location-venue-authority-work-package.md`.

Provider-backed event payments, tickets, refunds, QR/offline check-in,
session reservations/waitlists, competition scoring, vendors, volunteers,
incidents, and the other advanced aggregates cannot be reported as tested
because their durable implementations do not exist.
