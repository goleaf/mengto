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
BROWSER_BASE_URL=http://127.0.0.1:8000 npm run test:browser:a11y
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
latest recorded complete serial checkpoint reports 2,092 passing tests and
73,983 assertions after the authenticated portal boundary package.
This checkpoint is not a final coverage claim while requirements remain
unimplemented.

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

## Topic Lifecycle Verification

`ForumTopicLifecycleTest` covers additive schema/indexes, canonical and legacy
states, row/optimistic locking, reversible removal preservation, encrypted
legal hold, update request privacy/idempotency/review, bump cooldown,
redirect/301, private-group ownership isolation, class-based Livewire direct
authorization, rendered history/stale warnings, idempotent backfill, and
append-only events. `ForumTopicStatusTest` adds the pure legacy compatibility
and visibility contract.

Package evidence:

- lifecycle suite: 13 tests and 133 assertions;
- related forum, seeder, localization, and architecture slice: 1,155 tests and
  51,061 assertions;
- final integrated serial checkpoint: 1,656 tests and 57,951 assertions;
- Larastan: zero errors;
- fresh database: 98 migrations, 172 tables, full seed passed;
- repeated production-safe forum seed: passed;
- Playwright: public history, stale notice, permanent redirect, 375px
  overflow, and current-console checks passed.

Coverage remains unavailable because PHP 8.5 has neither PCOV nor Xdebug.
Exact commands are in the lifecycle work-package plan.

## Forum Accessibility Verification

`ForumAccessibilityTest` covers localized media-description and transcript
requirements, WebVTT content validation, generated storage paths, escaped
transcripts, caption tracks, legacy alternatives, compensation cleanup, and a
single complete keyed error summary. `ResponsiveInterfaceTest` verifies
critical contrast and focus/touch/reflow source contracts.
`ArchitectureComplianceTest` enforces table captions/scoped headers, textual
map alternatives, navigation-safe validation semantics, and the absence of
custom dialog or drag-only forum behavior.

Package evidence:

- focused accessibility/architecture/responsive: 28 tests and 22,936
  assertions;
- expanded forum regression: 268 tests and 2,756 assertions;
- localization: 7 tests and 27,453 assertions;
- final serial repository suite: 1,666 tests and 58,350 assertions in 91.946
  seconds;
- full Larastan: zero errors;
- full Pint, Composer strict validation/audit, npm audit, Vite build, and
  config/event/route/view cache compilation: passed;
- fresh isolated SQLite: 98 migrations, 172 tables, five users, 1,681
  categories, and 13 topics; repeated `DatabaseSeeder`: passed;
- `BROWSER_BASE_URL=http://127.0.0.1:8791 npm run test:browser:a11y`: passed
  desktop, mobile, reflow, keyboard focus, invalid submit, admin table, touch
  target, and console checks.

The browser runner requires a locally reachable isolated application and a
Chromium-compatible executable. It does not install or contact a second
browser-test framework.

## Multilingual Behavior Verification

`ForumMultilingualBehaviorTest` covers the additive provenance migration,
definition-key resolution in EN/LT/RU, reviewed root-category rows, all-locale
cache invalidation, recipient-locale notifications, original-content
preservation, human translation attribution, private/draft source denial,
locale uniqueness, locked Livewire identity, connected draft creation,
translation-action availability, scientific-name invariance, verified
common-name preference, explicit-locale unidentified fallback,
taxonomy-selector synonym context, and denial of translation editing when a
guide has no translation family.

Package evidence:

- final multilingual/guide/auth/localization/architecture slice: 75 tests and
  56,753 assertions;
- final serial repository suite: 1,684 tests and 64,597 assertions in 97.037
  seconds;
- full Larastan: zero errors;
- full Pint and first-party PHP syntax: passed;
- isolated fresh SQLite: 99 migrations and 172 tables; complete seed and
  repeated `DatabaseSeeder` preserved five users;
- additive translation migration rollback and re-application: passed;
- Composer strict validation/audit and npm audit: zero advisories;
- Vite 8.2.0 production build and config/event/route/view cache compilation:
  passed;
- dependency-free headless Chrome: English desktop/mobile/320px forum,
  Lithuanian desktop/mobile translation source, Russian forum, full-document
  locale changes/restoration, no raw keys, overflow, unnamed controls, invalid
  tables/images, or console errors.

The coverage command was executed and returned
`Code coverage driver not available. Did you install Xdebug or PCOV?`.

## Pet Profile Foundation Verification

`PetProfileFoundationTest` covers additive schema, enum and legacy-status
casts, idempotent create/backfill, owner and adjacent-data preservation,
manager invitation/acceptance/revocation/expiry, permission overrides, direct
Livewire authorization, optimistic lifecycle transitions, immutable events,
encrypted versioned facts, layered privacy, cache invalidation, stable URLs,
bounded queries, and EN/LT/RU key/placeholder parity. `SocialPersistenceTest`
keeps the historical `category/detail` update contract compatible while the
new form uses explicit species and breed inputs.

Package evidence:

- foundation plus legacy compatibility: 21 tests and 1,603 assertions;
- final serial repository suite: 1,748 tests and 68,172 assertions in 103.288
  seconds;
- isolated fresh SQLite: 100 migrations and 177 tables; repeat seed/backfill
  preserved stable pet and foundation counts;
- populated rollback/re-application retained every pet row;
- Pint, Larastan level 5, Composer strict validation/audit, npm audit, Vite,
  cache compilation, source preservation, and requirement generation passed;
- browser audit passed create/manage/invitations/public pet pages on desktop,
  mobile, and 320px with no accessibility, overflow, or console finding.

Percentage coverage remains unavailable because the PHP 8.5 CLI has neither
PCOV nor Xdebug. Exact commands and limits are in
`docs/plans/pet-profile-foundation-work-package.md`.

## Social Relationship Foundation Verification

`SocialRelationshipFoundationTest` covers additive indexed schema, reversible
rollback, bounded idempotent actor backfill, public and approval follows,
independent reverse subscriptions, owner and pet friendship consent, durable
friends/group/event eligibility, revoked pet-manager access, close-circle
permission isolation, block effects, cancel/accept serialization, persistent
expiry, optimistic privacy, hidden directed controls, bounded search and query
count, direct Livewire authorization, immutable events, and EN/LT/RU parity.

Package evidence:

- focused social suite: 22 tests and 432 assertions;
- expanded pet/social/architecture slice: 63 tests and 26,709 assertions;
- schema/factory/social regression: 1,250 tests and 4,055 assertions;
- final serial repository suite: 1,861 tests and 69,718 assertions in 90.930
  seconds;
- isolated migration/seed/backfill/rollback, Pint, Larastan, dependency audits,
  Vite, cache compilation, deterministic specification checks, and responsive
  English/Russian browser accessibility checks passed.

Exactly 158 atomic social IDs carry this evidence. Account-wide safety,
anti-abuse, recommendations, messages, meetings, notifications, minors, public
graph projection, transfer, deletion, and memorial behavior remain open.

## Social Relationship Safety Verification

`SocialRelationshipSafetyTest` covers reversible additive schema, real-account
blocking across current/future user/pet/expert/group actors, preserved care
roles, owner-only unblock without restoration, actor-switch-resistant rolling
and low-acceptance limits, permanent recipient repeat prevention, encrypted
normalized context, contact/detail and duplicate-template rejection, private
idempotent reports with optional account block, direct policy denial, and the
Livewire report flow with actor-specific accessible actions.

Package evidence:

- focused safety: 8 tests and 65 assertions;
- combined social foundation/safety: 30 tests and 593 assertions;
- architecture/schema/factory/accessibility slice: 1,292 tests and 29,221
  assertions;
- final serial repository suite: 1,872 tests and 70,764 assertions in 107.953
  seconds;
- isolated fresh SQLite: 103 migrations, 183 tables, repeated seed preserved
  five users;
- Composer/npm audits, Pint, Larastan, Vite, deterministic source generation,
  and English/Russian desktop/mobile/320px Chrome checks passed.

Exactly 64 additional atomic IDs carry this package evidence, bringing the
verified social total to 222. Cross-account/device correlation, minors,
messages/calls, meetings/location, recommendations, notifications, appeals,
public graph projection, transfer, deletion, and memorial behavior remain
open.

## Guest Join Page Verification

This section records the now-superseded public join delivery. The authenticated
portal package below is the current contract.

The historical package covered a passive localized join document. The current
`JoinLandingPageTest` instead proves that guest `/` stores its intended URL and
redirects with zero application queries, that verified/unverified/inactive
destinations remain correct, and that EN/LT/RU account entry plus validated
guest language switching still work. Existing prototype-feed and photo
behavior remains covered through the authenticated `preview.feed`
compatibility route.

Package evidence:

- focused join/auth/architecture slice: 60 tests and 26,885 assertions;
- final serial repository suite: 2,037 tests and 73,073 assertions in 108.887
  seconds;
- Pint and Larastan level 5: passed with zero errors;
- Composer strict validation/audit and npm audit: zero advisories;
- Vite production build and config/event/route/view cache compilation: passed;
- isolated fresh SQLite: 111 migrations, 191 tables, and repeat seed preserved
  five users;
- immutable forum source and generated 29,960-requirement checks: passed;
- dependency-free Chrome: EN/LT/RU at 320, 375, 768, 1024, 1440, and 1920
  pixels with one `h1`, zero horizontal overflow, visible skip-link focus,
  44-pixel scoped actions, no external images/member chrome, and no console
  errors.

## Authenticated Portal Boundary Coverage

`PortalAccessBoundaryTest` and `PortalMediaAccessTest` cover the exact account
entry allowlist; zero-query guest denial before route-model binding; HTML and
JSON behavior; inactive and unverified accounts; every inherited web route;
Livewire upload/preview denial; disabled local serving/storage links; intended
login redirects; authenticated media types; legacy media URL conversion;
missing, traversal, absolute, cross-domain, unsupported, and symbolic-link
escape paths. Existing authorization, token-share, forum, lost/found,
marketplace, auth, and architecture suites remain unchanged defense in depth.

Current evidence:

- focused portal boundary: 52 tests and 303 assertions;
- media-domain regression: 39 tests and 309 assertions;
- architecture: 20 tests and 26,727 assertions;
- full serial: 2,092 tests and 73,983 assertions in 111.749 seconds.

## Forum Topic Type Schema Reconciliation

`ForumTopicTypeSchemaContractTest` verifies every source-listed stable type,
active versioned definitions, EN/LT/RU translation contracts, typed JSON
casts, normalized foreign-key/index storage, absence of per-type columns, and
idempotent synchronization that preserves IDs, topic relations, structured
data, and custom definitions.

Package evidence:

- focused contract: 3 tests and 486 assertions;
- related seed, factory, and architecture slice: 61 tests and 26,837
  assertions;
- full sequential suite: 2,040 tests and 73,559 assertions;
- fresh database: 111 migrations and 191 tables; repeated seed preserved 5
  users;
- Pint and Larastan passed; Composer/npm audits and Vite build passed.
