# Testing

## Framework

Pest 4 is the primary PHP framework. PHPUnit 12 is its compatible underlying
runner and is not a second test style.

## Test Layers

- Feature: routes, middleware, auth, policies, validation, persistence,
  downloads, integrations, and rendered pages.
- Unit: pure state transitions, value objects, normalization, and safety rules.
- Livewire: component state, forms, direct action authorization, repeated
  submission, events, redirects, and rendered states.
- Policies: every method across all 13 policies is exercised for applicable
  owner/participant, outsider, administrator, blocked, guest, relationship, and
  resource-state cases.
- Browser: browser permissions, media teardown, focus/modal behaviour,
  responsive overflow, maps, and repeated navigation.
- Architecture: repository constraints.
- Collaborative guides: complete state graph, independent reviewers,
  authorization, optimistic edits, immutable versions/events, rollback,
  corrections, translation families, export/print privacy, Livewire identity,
  admin discovery, popularity non-conversion, factories, and repeat seeding.

## Required Commands

Targeted:

```bash
php artisan test --compact tests/Feature/RelevantTest.php
vendor/bin/pint --dirty
PAO_DISABLE=1 vendor/bin/phpstan analyse --memory-limit=1G
```

Final:

```bash
composer validate --strict
composer audit
vendor/bin/pint --test
PAO_DISABLE=1 vendor/bin/phpstan analyse --memory-limit=1G
php artisan test --compact
php artisan test --parallel
php scripts/verify-fresh-database.php
npm audit --audit-level=high
npm run build
```

Parallel tests run only when database isolation is safe.

## Coverage

Meaningful branch coverage is mandatory for authentication, authorization,
tenant/owner isolation, tokens, payment/order transitions, device commands,
medication doses, and care idempotency. The total application target is at
least 90% when a supported coverage driver is installed.

Do not write assertion-free tests or exclude difficult first-party code to
inflate the percentage. If no coverage driver exists, record the exact
environmental blocker while retaining all other behavioural tests.

## Fakes And Isolation

- `RefreshDatabase` for database-integrated tests.
- Factories for records.
- `Http::fake()` and stray-request prevention.
- `Storage::fake()` for file lifecycle.
- Notification, mail, event, and queue fakes only where observing the fake
  preserves the behaviour being tested.
- Temporary SQLite path for destructive migration/seeder checks.

Never run `migrate:fresh` against the local or production database as a test.
`scripts/verify-fresh-database.php` creates an operating-system temporary
SQLite file, asserts that it is outside the repository, and only then runs the
destructive command.

## Baseline And Current Checkpoint

The modernization baseline was 116 passing tests and 3,881 assertions. The
latest complete serial checkpoint reports 1,338 passing tests and 48,931
assertions. This checkpoint is not a final coverage claim while requirements
remain unimplemented.

Pest coverage cannot run in the current environment because PHP 8.5 has neither
PCOV nor Xdebug. The expected failing command and its exact reason remain part
of final evidence:

```bash
php artisan test --coverage --min=90 --compact
```

## Community Review Coverage

`CommunityReviewAndNotesTest` covers all low-risk and prohibited panel types,
balanced selection, conflict replacement, deadlines, one-vote constraints,
all note purposes and moderator outcomes, stale-review cancellation,
append-only history, optimistic locking, appeals, privacy, and direct
Livewire authorization. Architecture, localization, schema, factory, fresh
migration/seed, static-analysis, build, and browser checks remain required
alongside that focused suite.

## Mentorship Coverage

`MentorshipWorkflowTest` covers all thirteen types, profile eligibility,
optimistic locking, scope ownership/idempotency, independent credentials,
bounded transparent matching, blocks/capacity, request idempotency,
accept/decline/cancel, private messages, participant-only reports, explicit
truthfulness confirmation, optional feedback, independent completion
validation, reputation/badge idempotency, append-only/deletion constraints,
factories, repeated demo seed, route/Livewire authorization, tampered filters,
translation parity, and a 45-query first-render budget.

Current evidence:

- focused: 29 tests, 117 assertions;
- architecture/schema/factory/localization/mentorship: 899 tests, 43,799
  assertions;
- full serial: 1,263 tests, 46,373 assertions;
- fresh database: 91 migrations, 135 tables, repeat seed passed;
- Larastan: zero errors;
- Playwright: desktop/mobile accessibility, privacy, overflow, report options,
  and real Livewire message submission passed.

## Persistent Group Coverage

`GroupCoreWorkflowTest` covers four visibility states, six roles, lifecycle
and membership enums, creation, public join, reviewed join, private
invitations, invitation expiry/revocation, rejection, leave/removal/ban, role
change, owner protection and transfer, close/reopen/archive, unified reports,
policy denial, idempotency, optimistic conflicts, private/unlisted discovery,
factories, deterministic seeding, localization, Livewire, and query budgets.

Current evidence:

- focused: 22 tests, 1,208 assertions;
- full serial: 1,338 tests, 48,931 assertions;
- fresh database: 92 migrations, 140 tables, repeat seed passed;
- Larastan: zero errors;
- Playwright: private visibility, desktop/mobile overflow, labels, touch
  targets, management evidence, and console output passed.

The authentication rate-limit test freezes framework time so its strict
localized 60-second assertion cannot fail merely because rendering crosses a
wall-clock second.
