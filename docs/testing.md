# Testing

## Framework

Pest 4 is the primary PHP framework. PHPUnit 12 is its compatible underlying
runner and is not a second test style.

## Test Layers

- Feature: routes, middleware, auth, policies, validation, persistence,
  downloads, integrations, and rendered pages.
- Private-file security: traversal and absolute path representations,
  cross-domain stored paths, symlink escapes, and audit side effects that must
  not occur after a rejected download.
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
latest complete serial checkpoint reports 1,437 passing tests and 51,568
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

## Group Content And Poll Coverage

`GroupContentAndPollWorkflowTest` covers durable topic/guide association,
private directory and direct-route isolation, activities, announcements,
private file lifecycle, all three choice modes, both voter visibility modes,
all result visibility modes, editable/final ballots, trusted/location/member
eligibility, timestamp-derived closure, retry idempotency, result counters,
Livewire action authorization, and bounded query growth.

Previous group-content package checkpoint:

- focused group content and polls: 18 tests and 72 assertions;
- community review selective-projection regression: 52 tests and 165
  assertions;
- group-content plus architecture slice: 33 tests and 18,366 assertions;
- final full serial suite: 1,384 tests and 50,006 assertions;
- fresh database: 93 migrations, 146 tables, repeat seed passed.

The authentication rate-limit test freezes framework time so its strict
localized 60-second assertion cannot fail merely because rendering crosses a
wall-clock second.

## Forum Journal Coverage

`ForumJournalWorkflowTest` covers additive schema/indexes, all twelve typed
values, EN/LT/RU labels, transactional/idempotent creation, explicit legacy
backfill, guest/member/expert/private/group visibility, metric validation,
optimistic entry history, collaborator revocation, parent-scoped comments,
content-validated private files, protected export/routes, archive behavior,
direct Livewire authorization, locked identity, timezone-normalized future
boundaries, and repeated production/demo seeding.

Current verified journal package checkpoint:

- focused journal suite: 17 tests and 164 assertions;
- journal/forum regression slice: 1,025 tests and 4,334 assertions;
- architecture/localization slice: 38 tests and 44,600 assertions;
- app-shell regression slice: 61 tests and 19,471 assertions;
- final full serial suite: 1,437 tests and 51,568 assertions;
- fresh database: 94 migrations, 152 tables, repeat seed passed.

Static-analysis, build, cache, and browser details are recorded in
`docs/plans/forum-phase8-journals-work-package.md`.
## Event Coverage

`tests/Feature/Forum/EventWorkflowTest.php` covers event creation,
idempotency, validation, encryption, visibility, invitations, capacity,
waitlists, registration review, check-in, protected access, updates,
messages, rescheduling, cancellation, reviews, reports, Livewire direct-action
authorization, filters, legacy backfill, retired legacy mutations, and every
event factory.

`MeetupDirectoryPreviewTest` protects route compatibility, while
`GroupContentAndPollWorkflowTest` proves group activities create linked
canonical events. Shared architecture, localization, schema, and
factory/seeder suites cover cross-cutting gates.

Current verified event package checkpoint:

- focused event suite: 18 tests and 125 assertions;
- event/meetup/group/social regression slice: 45 tests and 287 assertions
  before the reversible-migration regression was added;
- architecture/localization/factory/schema slice: 1,049 tests and 49,125
  assertions;
- isolated final full serial suite: 1,514 tests and 53,062 assertions;
- isolated fresh database: 95 migrations, 160 tables, repeat seed passed.

Coverage remains unavailable because the PHP 8.5 CLI has neither PCOV nor
Xdebug. Parallel execution is not accepted as evidence while test processes
share one SQLite topology.

## Expert Session Verification

`ExpertQuestionSessionWorkflowTest` covers additive rollback, host credential
scope/jurisdiction/expiry, timestamp-derived windows, idempotent and
rate-limited queue submission, pending-question privacy, moderation,
answer-source validation without external I/O, immutable corrections,
optimistic conflicts, archive preservation, unified reports, direct Livewire
authorization, routes, factories, seed reruns, and EN/LT/RU catalogues.

`ExpertCredentialVerificationTest`, `ExpertDirectoryTest`, `ExpertSafetyTest`,
`ForumModerationTest`, `FactoryAndSeederTest`, `LocalizationTest`, and
`ArchitectureComplianceTest` provide integration gates. The expert-session plus
architecture slice passed 31 tests and 22,249 assertions; the expanded
regression slice passed 1,108 tests and 52,428 assertions; the detached
`f1e2fcc` package snapshot passed 1,554 tests and 54,317 assertions in serial
mode. The scoped percentage-coverage command was also attempted, but the PHP
8.5 CLI has neither Xdebug nor PCOV. Exact commands and browser evidence are
recorded in the expert-session work-package plan.
