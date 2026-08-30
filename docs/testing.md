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
- Policies: every method across all 47 policies is exercised for applicable
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
php scripts/run-tests.php --compact tests/Feature/RelevantTest.php
vendor/bin/pint --dirty
PAO_DISABLE=1 vendor/bin/phpstan analyse --memory-limit=1G
```

Translation-key changes additionally run:

```bash
php scripts/run-tests.php --compact tests/Unit/Support/ReadableTranslationKeyTest.php tests/Unit/Support/PhpMessageLiteralClassifierTest.php tests/Feature/LocalizationTest.php tests/Feature/PageIdentityStandardizationTest.php
php scripts/run-tests.php --compact --filter='translation keys never use generated digest suffixes|readable translation key migration remains clean' tests/Feature/ArchitectureComplianceTest.php
php scripts/localize-blade-literals.php --check
php scripts/localize-php-messages.php --check
php scripts/migrate-readable-translation-keys.php --check
```

The migration check and architecture ratchet must report zero catalogue keys
and zero references ending in a generated ten-hex digest. A new normalized
collision is resolved with a reviewed semantic key, never an automatic suffix.

Email-verification changes must include
`tests/Feature/Auth/ConfigurableEmailVerificationTest.php` together with the
authentication and central portal-boundary suites. `phpunit.xml` and the
isolated PHP/browser/database wrappers force the secure enabled mode so a
deployed disabled value cannot silently weaken the default test baseline;
individual tests opt into disabled mode through the configuration repository.

Final:

```bash
composer validate --strict
composer audit
vendor/bin/pint --test
PAO_DISABLE=1 vendor/bin/phpstan analyse --memory-limit=1G
php scripts/run-tests.php --compact
php scripts/verify-fresh-database.php
npm audit --audit-level=high
npm run build
npm run test:browser:a11y
```

Focused browser packages may expose a narrower command, such as
`npm run test:browser:animal-science`, but they supplement rather than replace
the complete browser gate.

The repository test runner is mandatory because it forces `APP_ENV=testing`,
the SQLite `:memory:` connection, array cache/session/mail, and synchronous
queues before Artisan boots; it also removes inherited `DB_URL` and clears
cached application configuration before Laravel boots Pest. It resolves and
opens that isolated connection as a fail-closed preflight before any
database-backed test can run. In-memory SQLite is intentional here: the
database exists only inside the Pest process and cannot resolve to a persistent
development or production path. Direct `php artisan test` is not a safe
full-suite command after a config-cache smoke check. `composer test --` routes
through the same wrapper and forwards every following Pest argument.

The database values in `phpunit.xml` use forced overrides as a second boundary
for ordinary direct PHPUnit/Pest invocations. The canonical wrapper remains
required because it also owns a cryptographically named per-process storage
directory and private cache paths under the operating-system temporary
directory. It validates that exact path before cleanup and removes it after
Pest exits, so tests cannot compile Blade views or create filesystem fakes in
production `storage`. A hostile-environment regression runs the real wrapper
with production-like inherited settings and proves that a sentinel SQLite
database remains untouched. The SQLite-backed suite runs serially. Browser
package scripts create and destroy
their own temporary SQLite database, seed it in `testing`, start a loopback
server on an ephemeral port, and explicitly authorize only that isolated Node
runner to mutate seeded state. Calling either underlying `.mjs` runner directly
without `BROWSER_ALLOW_DATA_MUTATION=1` fails before browser discovery.

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
- immutable forum source and generated 29,960-requirement checks: passed at
  that historical package checkpoint; the current 38,377-record catalogue
  requires a fresh combined check before any new evidence claim;
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

## Forum Topic Type Schema Runtime

`ForumTopicTypeSchemaRuntimeTest` verifies complete required/optional field
metadata and validation descriptors, all capability groups, stable internal
identity, immutable pre-seed fallback, one cold and zero warm definition
queries, model/seeder cache invalidation, active-schema create/update version
retention, location/species/media validation, and direct Action enforcement
for ratings, accepted answers, and notification levels.

Implementation-stage evidence on 2026-08-03:

- focused runtime contract: 5 tests and 1,481 assertions;
- related forum regression slice: 61 tests and 2,380 assertions;
- attributable Larastan slice: zero errors;
- architecture/localization slice: 27 tests and 57,522 assertions.

Final package evidence: the complete sequential suite passed 2,360 tests and
78,407 assertions in 132.854 seconds; full Pint and Larastan, dependency
audits, Vite/cache compilation, isolated migration/repeat seed, source
preservation, and deterministic requirement generation passed.

## Forum Database Correctness Reconciliation

`ForumDatabaseCorrectnessTest` covers the selected Phase 3 transaction,
constraint, cast, timestamp, archival, locking, idempotency, and race atoms.
It inspects exact FK/unique/compound indexes, bypasses model casts to prove
database `CHECK` rejection, exercises duplicate vote/reaction writes, replaces
a competing accepted answer, and verifies authorized/idempotent/stale case
closure plus complete rollback after a post-update bounded-limit failure.

The populated migration test runs `down()` and `up()` around representative
vote, reaction, and moderation rows. A query-growth assertion compares closure
with one and twenty linked reports; both execute the same number of database
statements. Exact commands and final gate evidence are maintained in
`docs/plans/forum-database-correctness-reconciliation-work-package.md`.

## Complete Migration Lifecycle Verification

`MigrationLifecycleVerificationTest` guards every first-party migration source
for typed `up()`/`down()` boundaries and forbidden raw-SQL escape hatches. It
then executes `scripts/verify-migration-cycle.php`, which creates an asserted
temporary SQLite database, compares all filenames to the migration ledger,
rolls back the complete ledger, reapplies it, and runs the production-safe seed
twice before removing the database in `finally`.

Final evidence on 2026-08-03:

- focused lifecycle contract: 2 tests and 11 assertions;
- direct lifecycle report: 118 files applied, 0 ledger rows after rollback,
  118 files reapplied, 200 tables, and stable 5-user repeated seed;
- migration lifecycle, schema integrity, factories/seeders, taxonomy,
  database-correctness, and populated event rollback slice: 1,611 tests and
  4,795 assertions;
- complete sequential repository suite: 2,362 tests and 78,760 assertions;
- full Pint and Larastan: passed with zero findings;
- Composer strict validation/audit, npm audit, Vite production build, and
  config/event/route/view cache compilation: passed;
- fresh isolated migration and repeated seed: 118 migrations, 200 tables, and
  a stable 5-user count;
- immutable source preservation and deterministic 38,377-requirement
  generation: passed.

Exact scope and final-gate status are recorded in
`docs/plans/forum-phase3-migration-verification-work-package.md`.

## Phase 4 Before-Ownership Category

`ForumBeforeOwnershipCategoryTest` rejects corrupt schema/checksum/count,
sequence, duplicate-key/slug, hierarchy, and empty-name manifests. It proves
the exact category-21 source metadata and all 60 source labels, exact persisted
child keys/slugs/positions, reviewed root translations, and the localized tree
query budget.

Final evidence on 2026-08-03:

- RED: corrupt aggregate metadata was accepted and a warm cache read executed
  2 database statements;
- focused contract: 13 tests and 38 assertions;
- related category, multilingual/cache, schema, and taxonomy-factory slice:
  72 tests and 5,949 assertions;
- query delta: warm locale tree 2 statements before and 0 after; cold loading
  remains at or below 6;
- complete sequential suite: 2,384 tests and 78,891 assertions;
- full Pint/Larastan, Composer/npm audits, Vite, Laravel cache compilation,
  fresh/repeat seed, rollback/reapply, manifest/source/requirements generation:
  passed.

The package changes no route, Blade, or browser interaction, so a browser-only
gate is not applicable. Exact scope is in
`docs/plans/forum-phase4-before-ownership-category-work-package.md`.

## Phase 4 Special-Needs Category And Translation Trust

`ForumSpecialNeedsCategoryTest` proves the exact category-22 metadata and all
54 ordered source subcategories, exact synchronized keys/slugs/positions and
three root locale rows, rejection of unreviewed target-locale values in both
the full tree and root selector, and adoption after human review. The existing
multilingual contract proves all-system-category locale completeness and
seed-synchronization invalidation.

Final evidence on 2026-08-03:

- RED: an unreviewed Lithuanian child name replaced the reviewed English
  fallback;
- focused contract: 4 tests and 17 assertions;
- related category, multilingual/cache, localization, and schema slice: 46
  tests and 36,051 assertions;
- query delta: 0 additional statements; the review flag uses the existing
  eager-loaded projections and warm locale-tree reads remain at 0;
- complete sequential suite: 2,396 tests and 79,143 assertions;
- full Pint/Larastan, Composer/npm audits, Vite, Laravel cache compilation,
  fresh/repeat seed, rollback/reapply, and manifest/source/requirements
  generation: passed.

The package changes no route, Blade, schema, or browser interaction, so a
browser-only gate is not applicable. Exact scope is in
`docs/plans/forum-phase4-special-needs-category-work-package.md`.

## Phase 4 Wildlife-Coexistence Category

`ForumWildlifeCoexistenceCategoryTest` pins the exact category-23 number,
stable key, slug, source name, purpose, and all 55 ordered source labels. It
then runs the production synchronizer and proves exact persisted root/child
stable keys, slugs, positions, and reviewed EN/LT/RU root rows. The two source
labels assigned to `forum.moderation.0010/.0011` stay present but unpromoted.

Final evidence on 2026-08-03:

- focused contract: 2 tests and 12 assertions;
- related category, multilingual, localization, and schema slice: 31 tests and
  36,368 assertions;
- query delta: 0 production statements because production code is unchanged;
- complete sequential suite: 2,484 tests and 80,398 assertions;
- full Pint/Larastan, Composer/npm audits, Vite, Laravel cache compilation,
  fresh/repeat seed, rollback/reapply, and manifest/source/requirements
  generation: passed.

The package changes no route, Blade, schema, cache, or browser interaction, so
browser-only and migration-content gates are not applicable. Exact scope is in
`docs/plans/forum-phase4-wildlife-coexistence-category-work-package.md`.

## Phase 4 One-Health Category And Professional Boundary

`ForumOneHealthCategoryTest` pins the exact category-24 number, stable key,
slug, source name, purpose, and all 42 ordered source labels. It proves the
persisted child keys/slugs/positions and reviewed EN/LT/RU root rows, then
checks the localized physician, veterinarian, public-health, and emergency
service boundary appears only while the One-Health category is selected. The
category-tree cache moved from v2 to v3 so an old cached shape cannot omit the
new optional notice field.

Final evidence on 2026-08-03:

- RED: the source and synchronized hierarchy passed, while the notice contract
  failed on an undefined `notice` field;
- focused contract: 3 tests and 21 assertions;
- related category, multilingual/cache, localization, and schema slice: 47
  tests and 36,632 assertions;
- directory, architecture, responsive, and localization slice: 39 tests and
  59,906 assertions;
- query delta: 0 production statements; the notice is localized while the
  existing tree payload is built and cached;
- complete sequential suite: 2,586 tests and 81,835 assertions;
- full Pint/Larastan, Composer/npm audits, Vite, Laravel cache compilation,
  fresh/repeat seed, rollback/reapply, and manifest/source/requirements
  generation: passed;
- isolated desktop/mobile Chrome: one boundary, 42 subcategories, and no
  overflow, raw keys, unnamed controls, undersized mobile controls, invalid
  images, duplicate IDs, or console errors.

Exact scope is in
`docs/plans/forum-phase4-one-health-category-work-package.md`.

## Phase 4 Animal-Science Category

`ForumAnimalScienceCategoryTest` pins the exact category-25 number, stable key,
slug, source name, purpose, all 54 ordered labels, synchronized child keys and
positions, reviewed EN/LT/RU root rows, reviewed-only child fallback state,
administrator-category preservation, stable public links, and server rejection
of an unknown child key. It also protects the canonical fixed category-heading
typography and the isolated package browser entrypoint.

Evidence observed on 2026-08-30:

- RED: five existing source/persistence/UI contracts passed while the shared
  category heading failed because it used Georgia and a viewport-scaled
  `clamp(1.25rem, 2vw, 1.65rem)` size; the browser-entry contract separately
  failed before the package-specific wrapper existed;
- focused GREEN: 7 tests and 104 assertions after the narrow SCSS correction
  and isolated wrapper were present;
- related category/cache/administration slice: 62 tests and 6,097 assertions;
  localization, responsive, schema, and taxonomy-factory slice: 50 tests and
  37,056 assertions;
- exact manifest and deterministic requirement generation: 44 roots, 1,637
  children, and 38,377 atomic requirements passed;
- earlier isolated Chrome: 1440px English, 375px Lithuanian, 320px English
  reflow, and 1024px Russian forced colors passed with exact localized root
  copy, 54 stable children, both later-phase labels, Instrument Sans at 18px, visible
  focus, 44px targets, zero overflow, zero raw keys, and zero console errors;
- current isolated Chrome rerun: failed before launch while seeding unrelated
  concurrent factory work; `ReviewFactory.php:69` rejected an incoherent
  completed booking;
- immutable-source reconstruction remains externally blocked by missing Codex
  history entry `1785397895`, and the coverage gate remains externally blocked
  because neither PCOV nor Xdebug is installed.

Exact scope and final gate dispositions are in
`docs/plans/forum-phase4-animal-science-category-work-package.md`.

## Forum Topic Editor Redesign Verification

`ForumTopicEditorLayoutTest` protects the `/forum/ask` authoring contract. It
requires one shared editor shell, the five-item publishing checklist directly
before the form, no detached sidebar, three ordered semantic sections, all 23
named authoring fields, the taxonomy selector, and both draft and publish
intents.

Final evidence on 2026-08-03:

- RED: the structural contract failed because the unified shell did not exist;
- focused contract: 2 tests and 33 assertions;
- related forum, accessibility, page-identity, responsive, localization, and
  architecture slice: 86 of 87 tests passed; the remaining failure is an
  unrelated shared-worktree pet-media route-classification change;
- isolated `main` plus only this package: 2,588 tests and 82,043 assertions;
- query delta: 0 production statements because no route, controller, Eloquent,
  Livewire, policy, validation, or persistence code changed;
- full Pint and Larastan, Composer strict validation/audit, npm audit, Vite
  production build, and compiled Blade views: passed;
- isolated Chrome at 1440x900 and 375x812: one unified shell, guidance before
  the form, five checklist items, no sidebar, three sections, and no overflow,
  raw keys, unnamed controls, undersized mobile targets, invalid media,
  duplicate IDs, or console errors.

Exact scope is in
`docs/plans/forum-topic-editor-redesign-work-package.md`.

## Icon System Verification

`IconSystemContractTest` proves the canonical decorative/informative ARIA
states, bounded size and stroke classes, shared-component delegation, and one
icon per visible desktop/mobile primary-navigation destination. It also
ratchets direct, dynamic, and legacy icon debt and rejects foreign systems,
raw pictograms, missing installed Lucide names, and unapproved inline SVG.

Current verified evidence on 2026-08-03:

- RED: 4 tests failed because `x-ui-icon` did not exist, shared primitives
  rendered Lucide directly, desktop navigation rendered zero icons, and
  dynamic debt exceeded the intended ratchet;
- the first full run exposed the nested anonymous-component naming violation;
  the final flat `x-ui-icon` contract plus naming check passed 5 tests and 40
  assertions;
- source audit: 350 attributable Blade files, 828 canonical calls, zero direct
  calls, zero dynamic-debt calls, zero legacy class attributes/selectors, zero foreign
  systems, zero raw pictograms, zero missing installed names, and one
  allowlisted chart SVG;
- the 97-candidate baseline was manually reduced to 52 documented intentional
  text/content exceptions;
- production Vite build and complete Blade compilation passed after the final
  semantic action wave;
- full sequential regression: 2,639 tests and 83,214 assertions passed;
- full Pint passed and Larastan analysed 1,385 files with no errors;
- fresh migration, complete seed, and repeat seed passed;
- Composer validation/audit, npm audit, configuration/route/view caches, and
  the production Vite build passed;
- the accessibility browser matrix produced 33 EN/LT/RU screenshots across
  320–1920-pixel layouts with no overflow, unnamed controls, undersized audited
  targets, raw translation keys, or console errors.

Exact findings and the zero-debt plan are in
`docs/audits/icon-system-deep-audit.md` and
`docs/plans/icon-system-unlimited-plan.md`.

## Shared Directory Card Verification

`GroupDirectoryPreviewTest` protects the direct media/body/footer regions,
opaque body surface, visible separator, semantic linked and unlinked headings,
localized fallback actions, and the four direct catalogue-card composition
contracts. The existing pet, meetup, and linked-media suites protect sibling
rendering and navigation.

The authenticated browser runner audits all six `/groups` cards at 320, 375,
768, 1024, 1440, and 1920 CSS pixels. It requires adjacent non-overlapping
media/body geometry, a visible separator, contained images, an opaque padded
body, shared headings/descriptions/footers, equal heights within each grid row,
no horizontal overflow, no raw translation keys, and no console errors. It
writes `group-directory-mobile.png` and `group-directory-desktop.png` to the
configured browser output directory.

Verified package evidence on 2026-08-03:

- RED: the structural group-card, shared-heading, and fallback-localization
  contracts failed before their implementations existed;
- isolated staged-tree architecture/localization/directory slice: 88 tests
  and 61,421 assertions;
- isolated staged-tree full serial suite: 2,645 tests and 83,332 assertions;
- Blade compilation, targeted Pint, PHP syntax, full Larastan across 1,385
  files, and the production Vite build passed;
- Chrome passed the six-width geometry assertions for six cards with a 0-pixel
  region gap, 1-pixel separator, 20-pixel body padding, no escaped image, zero
  same-row height spread, six of six images loaded before mobile and desktop
  capture, zero raw translation keys, and zero console errors;
- browser cleanup now waits for Chrome exit and uses bounded temporary-profile
  removal retries;
- fresh verification applied 130 migrations, created 215 tables, and retained
  five users after the repeat seed;
- Composer validation/audit, NPM audit, and config/event/route/view cache
  generation passed; implementation commit `0da0c3c` was published to
  `origin/main`.

The concurrently dirty shared tree passed 2,651 of 2,654 tests. Its three
failures came from uncommitted foreign icon/member/pet-profile work and are not
present in the isolated staged tree.

Exact findings and the continuing backlog are in
`docs/audits/groups-shared-card-ux-audit.md` and
`docs/plans/shared-directory-card-system-plan.md`.

Continuation wave 1 adds `SharedCardSystemContinuationTest` and extends
`GroupDirectoryPreviewTest`. The focused group/discovery/expert/linked-media
slice passed 50 tests and 631 assertions. Connected Chrome passed the six group
viewports distributed across EN, LT, and RU, including long-copy clipping,
membership-state, 44-pixel action, raw-key, overflow, image, and console
assertions. LT and RU mobile screenshots were inspected. The clean attributable
snapshot passed full Pint, Larastan, Vite, and 2,662 serial tests with 84,714
assertions. The shared dirty tree's eight failures were isolated to the foreign
uncommitted pet icon `lucide-clipboard-heart` and are absent from this snapshot.

## Canonical Pet Workspace And Draft Autosave

`PetProfileWorkspaceTest` verifies the authenticated controller boundary,
owned/active-manager isolation, validated database search/filter/sort,
pagination, distinct empty states, invitation separation, EN/LT/RU output,
inactive-account rejection, and bounded query growth. The progressive profile
suite verifies idempotent descriptive-step autosave, reload persistence, and
mismatched-step rejection while sensitive operations retain explicit submits.

Observed release evidence on 2026-08-03:

- focused progressive/workspace order slice: 37 tests and 233 assertions;
- final isolated reconnect slice: 2,692 tests and 85,091 assertions;
- targeted and full Pint plus Larastan: passed with zero findings;
- Composer strict validation/audit and npm audit: passed with zero known
  vulnerabilities;
- fresh SQLite migration/seed: 130 migration files, 215 tables and five users;
  the repeat seed retained the same counts;
- config, route and view cache compilation plus production Vite build: passed;
- connected Chrome: EN 1440, RU 375 and LT 320 passed with no overflow,
  undersized mobile controls, duplicate IDs, raw keys, private leaks, prototype
  actions, or console errors; the autosave check observed one Livewire request,
  reload persistence, visible offline/unsaved state, mobile reflow, and full
  restoration of the displayed seeded values on a freshly migrated disposable
  SQLite database. The mutating mode requires `--autosave` together with
  `BROWSER_ALLOW_DATA_MUTATION=1` and must never target a shared database;
- Chrome network emulation forced exactly one failed offline Livewire request;
  the in-page revision remained pending, reconnect emitted exactly one retry,
  reload proved server persistence, and no private draft value was written to
  persistent browser storage;
- browser cleanup waits for Chrome exit before removing its temporary profile,
  eliminating the reproduced `ENOTEMPTY` post-audit race.

Exact scope and continuing pet backlog are in
`docs/plans/pet-workspace-modernization-plan.md` and
`docs/plans/pet-profile-current-progress.md`.

## Organization Authority Foundation

`OrganizationAuthorityFoundationTest` covers idempotent creation, owner
membership, account-bound signed invitations, encrypted temporary token
state, single use, removal, role separation, suspension and individual
restrictions, event integration, route/Livewire tenant isolation, EN/LT/RU,
factory helpers, guarded repeat seeding, event-builder scoping, bounded
directory queries, and private workspace projections.

Observed evidence on 2026-08-03:

- focused organization contract: 14 tests and 109 assertions;
- organization plus event lifecycle/workflow: 39 tests and 702 assertions;
- complete serial suite: 2,484 tests and 80,398 assertions in 163.378 seconds;
- full Larastan: zero findings;
- migration lifecycle: 121-to-0-to-121 files, 205 tables, and stable 5-user
  repeated seed;
- Composer strict validation/audit, npm audit with zero vulnerabilities, Vite
  8.2.0 build, cache compilation, localization, and deterministic forum
  source/manifest/requirements checks: passed.
- connected browser matrix: organization directory and workspace at 1440x900
  and 375x812, with zero overflow, unnamed controls, raw organization keys,
  private verification-evidence leaks, undersized mobile controls, or console
  errors.

Targeted Pint and the scoped publication diff check passed. Exact implemented
and open P02 scope is recorded in
`docs/plans/portal-organization-authority-foundation-work-package.md`.

## Canonical Place And Venue Authority

`PlaceAuthorityFoundationTest` covers encrypted exact fields, public
projection, organization and grant authority, confirmed-registration access,
audited reveal, location-version history, grant revocation, canonical event
links, bounded queries, factories, and repeat-safe seeding. `PlaceDirectoryTest`
proves the existing server-rendered directory and stable detail route consume
the same persisted authority while private and archived records remain absent.

Observed package evidence on 2026-08-03:

- focused place authority: 20 tests and 153 assertions;
- place directory plus event lifecycle/workflow regression: 58 tests and 886
  assertions;
- factory/seeder inventory with place coverage: 1,682 tests and 4,987
  assertions;
- isolated fresh/repeat seed: 126 migrations, 211 tables, stable five-user
  identity count, and both exits `0`;
- full Pint, Larastan, Composer validation/audit, npm audit, Vite build, and
  Laravel cache smoke checks: passed with zero reported errors or known
  dependency vulnerabilities;
- browser matrix: desktop/mobile place directory and detail passed with zero
  overflow, unnamed controls, raw place keys, protected-location leaks,
  undersized mobile controls, or console errors. Desktop place cards measured
  384-473px and mobile cards 614-654px against enforced 480/720px ceilings.

The complete suite total and scoped publication evidence are maintained in
`docs/plans/portal-place-location-venue-authority-work-package.md`.

## Linked Media Navigation Verification

`LinkedMediaNavigationContractTest` verifies the semantic linked/passive
component contract, exact media/title destination equality for pets, groups,
neighbors, meetups, discovery, profiles, experts, bookings, messages, and
marketplace orders, and source guards against nested interactive content. Its
fixture classifies all 71 first-party media-bearing Blade templates, including
viewer, current-page, action, decorative, protected-download, composite, and
passive exclusions.

Observed package evidence on 2026-08-03:

- focused contract: 19 tests and 279 assertions;
- affected feature/architecture slice: 67 tests and 27,626 assertions;
- full Pint, targeted Larastan, full Larastan, Composer validation/audit, npm
  audit, Vite build, cache compilation, isolated fresh SQLite migration and
  repeated seed: passed;
- connected browser matrix: four representative routes at six widths from 320
  through 1920 pixels, with no overflow, unnamed media links, nested
  interactive descendants, or console warnings/errors; pointer and keyboard
  navigation reached the canonical pet profile;
- query delta: zero new queries; presenters derive URLs from existing prepared
  projections and Blade performs no lookup.

The final serial repository suite passed 2,303 tests and 76,111 assertions in
131.130 seconds. An earlier concurrent final-class loader conflict disappeared
before this final rerun and is not recorded as an active blocker.

## Pet Primary Photo Verification

`PetProfileMediaLifecycleTest` protects the optional create upload, generated
private path, image normalization and WebP output, valid factory defaults,
single-current placement, authorization, idempotent replacement, logical
removal, 30-day recovery, restoration, and canonically contained response.
The create and management Livewire flows, public-profile projection, private
HTML boundary, route classification, and foreign-key indexes are covered by
the related feature and architecture tests.

Observed package evidence on 2026-08-03:

- focused media/create/foundation slice: 27 tests and 1,948 assertions;
- media plus schema, linked-media, and page-identity regression: 72 tests and
  620 assertions;
- architecture, localization, private-file, security, and portal boundary:
  85 tests and 60,963 assertions;
- isolated fresh and repeated seed: 130 migrations, 215 tables, stable
  five-user identity count, and both exits `0`;
- targeted Pint and Larastan, Composer strict validation/audit, npm audit, and
  the Vite production build passed;
- the connected Chrome matrix passed desktop/mobile pet create, management,
  and public-profile surfaces with no overflow, unnamed controls, missing
  image alternatives, undersized mobile targets, duplicate IDs, or console
  errors; management screenshots were reviewed at 1440x900 and 375x812;
- the first serial full-suite run exposed three package integration gaps and a
  concurrent Discover factory defect; after correction, the final isolated
  serial suite passed 2,635 tests and 83,160 assertions in 177.476 seconds.

Exact scope and the open wider media boundary are recorded in
`docs/plans/pet-profile-primary-photo-work-package.md`.

## Pet Progressive Completion Verification

`PetProfileProgressiveCompletionTest` covers the twelve URL-backed central
steps, one active body, invalid-step normalization, mutation-free skipping,
independent descriptive saves, preservation of other sections, optimistic
stale-write rejection, idempotent replay, encrypted microchip replacement,
identifier clearing, critical-permission denial, and bounded query growth.

Verified package evidence on 2026-08-03:

- RED: six tests failed on the missing enum, Action, component methods, and
  protected record flow;
- focused progressive suite: 6 tests and 71 assertions passed;
- canonical create/legacy redirect, pet foundation, media, and progressive
  regression: 33 tests and 2,601 assertions passed;
- pet plus architecture/localization/responsive/page-identity checkpoint: 107
  tests and 64,205 assertions passed against final generated evidence;
- final serial repository suite: 2,657 tests and 84,589 assertions passed in
  166.963 seconds;
- full Pint and Larastan, Composer strict validation/audit, npm audit with zero
  vulnerabilities, and the Vite production build passed;
- isolated SQLite applied 130 migrations across 215 tables, rolled all 130
  back, reapplied them, and retained five users across repeat seeding;
- isolated config/event/route/view cache compilation and the immutable source
  checksum plus 38,377-requirement generation checks passed;
- connected Chrome passed desktop, mobile, and 320px pet create/manage/public
  flows with zero page overflow, unnamed controls, missing alternatives,
  undersized mobile targets, duplicate IDs, raw keys, or console errors; the
  final mobile screenshot was inspected after the contained scroll-row fix.

Exact scope is in
`docs/plans/pet-profile-progressive-completion-work-package.md`.

## Pet Duplicate Review And Access Request Verification

`PetProfileDuplicateAccessRequestTest` covers policy-visible bounded identity
matching, private-profile exclusion, encrypted expiring review tokens,
explicit different-animal continuation, typed encrypted evidence, temporary
access bounds, idempotent submission, fresh locked authorization, manager-only
review, relationship correction, invitation acceptance, and protected
ownership-transfer handling.

Verified package evidence on 2026-08-03:

- focused duplicate/access suite: 15 tests and 68 assertions;
- related pet, architecture, localization, page-identity, and responsive
  regression: 156 tests and 65,826 assertions;
- final serial repository suite: 2,747 tests and 87,289 assertions in 162.853
  seconds;
- full Pint and Larastan, Composer validation/audit and platform requirements,
  npm audit with zero vulnerabilities, Vite production build, and isolated
  config/event/route/view cache compilation: passed;
- isolated SQLite applied 132 migrations across 216 tables, rolled all 132
  back, reapplied them, and retained five users across repeated seeding;
- immutable forum source and deterministic 38,377-requirement checks passed;
- disposable-database Chrome submitted the real Livewire access request and
  rendered authorized manager evidence at desktop, 375px, and 320px with zero
  overflow, private-candidate or credential leaks, raw keys, duplicate IDs,
  undersized or unnamed controls, and console errors.

The reusable browser command is
`BROWSER_BASE_URL=http://127.0.0.1:PORT node scripts/pet-duplicate-access-browser-check.mjs`.
It requires a disposable database containing the named public and private
candidate fixtures documented by the work package.

## Pet Species Confidence Verification

`PetProfileSpeciesConfidenceTest` covers possible cat/dog creation,
unidentified storage, incompatible browser normalization, authorized
correction, honest public/workspace labels, and conditional control choices.

Observed evidence on 2026-08-03:

- focused suite: 6 tests and 38 assertions;
- related pet, architecture, localization, page-identity, responsive, and
  schema regression: 164 tests and 65,953 assertions;
- migration/factory/seeder verification: 1,735 tests and 5,150 assertions;
- final serial suite: 2,757 tests and 87,427 assertions in 161.849 seconds;
- full Pint, Larastan, Composer/platform/audit, npm audit, Vite build, and
  config/event/route/view cache gates passed;
- fresh disposable SQLite applied 133 migrations and retained 216 tables;
- disposable Chrome exercised the real possible-dog selection, duplicate
  review, access request, and manager review at desktop, 375px, and 320px with
  zero overflow, raw keys, duplicate IDs, undersized or unnamed controls,
  private/credential leaks, or console errors.

## Pet Name Identity Verification

`PetProfileNameIdentityTest` covers Unicode-aware validation, reserved and spam
rejection, all supported name purposes, normalized uniqueness, manager and
recorder authorization, automatic previous-name history, stable profile
identity, privacy projections, old-name manager search, direct Livewire
mutation, and EN/LT/RU parity.

Observed evidence on 2026-08-03:

- focused suite: 9 tests and 61 assertions;
- related pet, architecture, localization, page-identity, responsive, and
  schema regression: 139 tests and 66,717 assertions;
- final sequential suite: 2,801 tests and 89,231 assertions in 162.266 seconds;
- full Pint and Larastan, Composer/platform/audit, npm audit, Vite build, and
  config/event/route/view cache gates passed;
- fresh disposable SQLite applied 134 migrations, retained 217 tables and five
  users, and passed repeated seeding;
- disposable Chrome added a real alternative name, verified its public
  projection, and resolved the current profile by that name in the manager
  workspace at desktop, 375px, and 320px with zero accessibility, privacy,
  overflow, raw-key, duplicate-ID, or console finding.

The reusable browser command is
`BROWSER_BASE_URL=http://127.0.0.1:PORT BROWSER_ALLOW_DATA_MUTATION=1 node scripts/pet-workspace-browser-check.mjs --names`.

## Pet Birth Precision Verification

`PetProfileBirthPrecisionTest` covers all six precision modes, impossible and
future input, normalized persistence, advancing age estimates, month/year
ranges, localized labels, public celebration projection, direct Actions,
Livewire save/reload, and migration rollback/reapplication.

Observed evidence on 2026-08-03:

- focused suite: 15 tests and 83 assertions;
- affected pet, event, lost/found, duplicate, and workspace regression: 138
  tests and 4,382 assertions;
- complete pet-profile regression: 114 tests and 3,801 assertions;
- isolated sequential suite: 2,831 tests and 90,267 assertions in 163.089
  seconds;
- full Pint and Larastan, Composer/platform/audit, npm audit, Vite build, and
  config/event/route/view cache gates passed;
- fresh disposable SQLite applied 135 migrations, retained 217 tables, five
  users, and three pet profiles, and passed repeated complete seeding;
- disposable Chrome changed the real Livewire precision, saved and reloaded an
  advancing age estimate, verified its public projection, and audited desktop,
  390px, and 320px layouts with zero accessibility, privacy, overflow,
  raw-key, duplicate-ID, or console finding.

The reusable browser command is
`BROWSER_BASE_URL=http://127.0.0.1:PORT BROWSER_ALLOW_DATA_MUTATION=1 node scripts/pet-workspace-browser-check.mjs --birth`.

## Pet Breed Origin Verification

`PetProfileBreedOriginTest` covers one and multiple origins, all overall
states, confidence and source separation, optional percentage validation,
active same-taxon classifications, legacy-client compatibility, foreign row
keys, authorized Livewire editing, public trust projection, localization, and
bounded query growth.

Observed evidence on 2026-08-04:

- focused suite: 7 tests and 55 assertions;
- affected regression: 55 tests and 3,726 assertions;
- complete pet-profile regression: 121 tests and 4,144 assertions;
- complete sequential suite: 2,867 tests and 93,321 assertions in 199.794
  seconds using a 1 GB runner limit for the existing high-resolution
  lost/found image fixture;
- full Pint, Larastan, Composer/platform/audit, npm audit, Vite build,
  JavaScript syntax, and diff checks passed;
- fresh disposable SQLite applied 136 migrations, retained 218 tables, five
  users and three pet profiles across repeated seeding, and passed explicit
  rollback/reapplication of the new migration;
- config, event, route, and view caches compiled in an isolated namespace;
- disposable Chrome selected mixed origin, explicitly added two entries,
  saved and restored their distinct names, confidence, sources, and
  percentages, and verified the public projection;
- desktop, 390px, and 320px checks found no overflow, raw translation keys,
  duplicate IDs, unnamed or undersized controls, privacy leaks, or console
  errors.

The reusable browser command is
`BROWSER_BASE_URL=http://127.0.0.1:PORT BROWSER_ALLOW_DATA_MUTATION=1 node scripts/pet-workspace-browser-check.mjs --breed`.

## Pet Appearance Color Verification

`PetProfileAppearanceTest` covers encrypted structured persistence, catalogue
and text bounds, duplicate and forged direct Action input, authorization,
idempotency, no-op behavior, EN/LT/RU Livewire restoration, public projection,
identifying-mark privacy, legacy compatibility, completion detection, and
zero-query presentation.

Observed evidence on 2026-08-04:

- focused suite: 15 tests and 108 assertions;
- focused plus architecture: 35 tests and 30,944 assertions;
- complete pet-profile regression: 153 tests and 4,829 assertions;
- clean isolated sequential suite: 2,923 tests and 96,639 assertions in
  169.946 seconds with the documented 1 GB limit for the existing large image
  fixture;
- full Pint, Larastan, Composer/platform/audit, npm audit, isolated Vite build,
  JavaScript syntax, and staged-diff checks passed;
- fresh disposable SQLite applied 137 migrations, retained 218 tables and five
  users, and passed repeated complete seeding;
- config, event, route, and view caches compiled, then were individually
  cleared before the final suite;
- disposable Chrome saved and restored structured color data, verified the
  public projection without private identifying marks, and passed desktop,
  390px, and 320px geometry, labels, touch targets, raw-key, duplicate-ID, and
  console checks. The final screenshots were visually reviewed.

The reusable browser command is
`BROWSER_BASE_URL=http://127.0.0.1:PORT BROWSER_ALLOW_DATA_MUTATION=1 node scripts/pet-workspace-browser-check.mjs --appearance`.

## Pet Species-Aware Body Covering Verification

`PetProfileBodyCoveringTest` covers encrypted structured persistence, adjacent
appearance compatibility, forged direct Action values, species pruning,
hairless contradictions, authorization, replay idempotency, strict real-route
rendering, EN/LT/RU restoration, public projection, private skin isolation,
malformed legacy input, completion detection, and zero-query presentation.

Observed package evidence on 2026-08-04:

- focused suite: 26 tests and 104 assertions;
- body-covering, appearance, and progressive-completion regression: 68 tests
  and 371 assertions;
- complete pet-profile regression: 179 tests and 5,377 assertions;
- architecture, localization, page-identity, responsive, and focused package
  slice: 103 tests and 67,659 assertions;
- exact committed-tree complete sequential repository suite: 2,960 tests and
  98,960 assertions in 195.350 seconds with a 1 GB PHP limit;
- disposable Chrome saved and restored coat, undercoat, shedding, and private
  skin data through Livewire and verified the privacy-safe public projection;
- desktop, 390px, and 320px geometry, labels, touch targets, raw-key,
  duplicate-ID, privacy, and console checks passed;
- full Pint and Larastan, Composer strict validation, locked audit, PHP platform
  requirements, npm audit, JavaScript syntax, isolated Vite build, disposable
  migration and repeated seed, cache compilation, immutable source,
  deterministic traceability generation, and scoped staged-diff checks passed.

The reusable browser command is
`BROWSER_BASE_URL=http://127.0.0.1:PORT BROWSER_ALLOW_DATA_MUTATION=1 node scripts/pet-workspace-browser-check.mjs --covering`.

## Pet Structured Identifying Marks Verification

`PetProfileIdentifyingMarkTest` covers the normalized schema and indexes,
encrypted storage, controlled types and visibility, bounded item and text
counts, cross-pet and retired-key rejection, authorization, optimistic replay,
no-op behavior, retirement, EN/LT/RU restoration, legacy compatibility,
completion detection, public/private projection, and zero-query presentation.

Observed package evidence on 2026-08-04:

- focused suite: 17 tests and 94 assertions;
- marks, appearance, body-covering, and completion regression: 85 tests and
  465 assertions;
- complete pet-profile regression: 196 tests and 5,687 assertions;
- architecture, localization, page-identity, responsive, and focused package
  slice: 94 tests and 67,847 assertions;
- exact isolated complete sequential repository suite: 2,998 tests and
  102,330 assertions in 181.707 seconds with a 1 GB PHP limit;
- full Pint and Larastan, Composer strict validation, locked audit, PHP
  platform requirements, npm audit, JavaScript syntax, Vite 8.2 build,
  disposable migration and repeated seed, cache compilation, immutable source,
  and deterministic requirement generation passed;
- connected Chrome added, saved, and restored a public ear feature and private
  verification tattoo through Livewire, exposed only the public value, and
  passed desktop, 390px, and 320px geometry, labels, touch targets, raw-key,
  duplicate-ID, privacy, and console checks; screenshots were visually
  reviewed.

The reusable browser command is
`BROWSER_BASE_URL=http://127.0.0.1:PORT BROWSER_ALLOW_DATA_MUTATION=1 node scripts/pet-workspace-browser-check.mjs --marks`.

## Pet General Size Category Verification

`PetProfileSizeCategoryTest` covers the nullable column and composite index,
all seven localized enum values, cast persistence and clearing, forged direct
Action input, authorization, omission, no-op and replay idempotency, EN/LT/RU
Livewire restoration, strict real-route projection, completion detection, and
query-free public presentation.

The connected browser gate uses a disposable database, changes the native
size select through a real DOM event, waits for a Livewire request, reloads the
stored value, verifies the public explanation and measurement boundary, and
audits desktop, 390px, and 320px layouts for overflow, raw keys, duplicate IDs,
44-pixel selector size, and console errors.

Observed package evidence on 2026-08-04:

- focused suite: 22 tests and 85 assertions;
- size, appearance, body-covering, identifying-mark, and completion regression:
  107 tests and 550 assertions;
- complete pet-profile regression: 218 tests and 5,910 assertions;
- architecture, localization, page-identity, responsive, and package slice:
  99 tests and 68,534 assertions;
- exact isolated complete sequential suite: 3,045 tests and 103,962 assertions
  in 173.604 seconds with a 1 GB PHP limit;
- full Pint, Larastan, Composer strict validation/audit/platform, npm audit,
  Vite 8.2 build, JavaScript syntax, cache compilation, fresh migration,
  repeat seed, rollback/reapply, source preservation, deterministic generation,
  and connected-browser checks passed; the screenshots were visually reviewed.

The reusable browser command is
`BROWSER_BASE_URL=http://127.0.0.1:PORT BROWSER_ALLOW_DATA_MUTATION=1 node scripts/pet-workspace-browser-check.mjs --size`.

## Messaging Context And Feedback Verification

`MessageCenterLocalizationTest` guards exact EN/LT/RU key parity, all eight
stable conversation metadata projections, the class-based context action/icon
maps, professional, poll, task, shared, safety, and boundary rendering, every
mutation feedback family, immutable catalogue-message editing, request/block
send boundaries, notification levels, and sent/read/delivered codes.

Observed package evidence on 2026-08-04:

- focused suite: 21 tests and 3,007 assertions;
- affected messaging, authentication, responsive, linked-media, and
  page-identity slices: 133 tests and 4,233 assertions;
- complete sequential repository suite: 2,964 tests and 101,228 assertions in
  189.477 seconds with a 1 GB PHP limit;
- the page-identity browser matrix passed 91 audits and 26 screenshots across
  320-1920 pixels, EN/RU/LT, forced colors, reduced motion, and effective 200%
  zoom with zero console errors or undersized messaging controls;
- full Pint and Larastan, Composer strict validation, locked audit, PHP
  platform requirements, npm audit, Vite build, JavaScript syntax, and
  config/event/route/view cache gates passed;
- fresh disposable SQLite applied 137 migrations, retained 218 tables and five
  users, and passed repeated complete seeding;
- both forum-source checks, deterministic 38,377-requirement generation, the
  PHP localizer, 180-route smoke, and 851-call canonical icon audit passed.

The reusable browser command is
`npm run test:browser:page-identity`.

## Measured Repository Performance Audit

The 2026-08-30 audit adds deterministic regression coverage for the complete
Place result set, private lost/found coordination, stable care/medical/device
pagination, seven measured indexes and SQLite query plans, maximum-valid
Livewire snapshots, cache audience/privacy/failure behavior, request
correlation and slow streams, bounded forum-journal child projections, and a
constant-query full journal export.

Run the observed-value probes with:

    PERFORMANCE_REPORT=1 php artisan test --compact \
      tests/Feature/Places/PlaceDirectoryScalabilityTest.php \
      tests/Feature/SearchCoordinationPerformanceTest.php \
      tests/Feature/LivewirePayloadBudgetTest.php \
      tests/Feature/Forum/ForumJournalWorkflowTest.php

The focused post-fix measurement run reported Place at 4 queries/99,628 bytes,
lost/found coordination at 10 queries/77,051 bytes, journal export at 4
queries/90,737 bytes, journal timeline at 8,083 bytes, and ManagePetProfile at
58,579 initial HTML/18,406 snapshot/58,161 update bytes. The combined focused
cache, index, stable-pagination, payload, observability, forum-tree, timeline,
and export selection passed 22 tests and 224 assertions; the complementary
Place/Livewire/index/cache/observability selection passed 22 tests and 232
assertions. These are focused results, not a substitute for the final serial
suite.

## Place Submission Verification

`PlaceSubmissionPublicationWorkflowTest` covers persistence privacy,
append-only history, server validation, category rules, two-account isolation,
member choices, manager/administrator capabilities, review transitions, replay
conflicts, merge rollback/restore, redirects, notifications, rates, and
Livewire routes. `PlaceSubmissionConcurrencyTest` uses two PHP processes
against disposable SQLite to prove matching simultaneous submissions both
survive and one receives a deterministic candidate.

`php scripts/run-browser-check.php places` checks desktop/mobile directory and
detail, invalid recovery, successful pending state, protected duplicate
redaction, localization keys, touch targets, overflow, screenshots, and
console errors on a disposable seeded database.

## Neighbor Profile Localization And Icon Verification

`NeighborProfileLocalizationTest` guards exact EN/LT/RU key parity, the
dedicated zero-query presenter, prepared follow/message/walk actions, passive
Blade boundaries, all localized profile fixtures, stable semantic hooks, and
the canonical pet-metadata and routine icon maps. The directory contract keeps
its independent 71-leaf ratchet while sharing the same `neighbors` domain.

Observed package evidence on 2026-08-04:

- focused profile suite: 7 tests and 403 assertions;
- combined neighbor directory/profile suite: 14 tests and 887 assertions;
- affected authorization, preview, responsive, page-identity, linked-media,
  and shared-component slice: 136 tests and 2,210 assertions;
- complete sequential shared-checkout suite: 3,016 tests and 103,577
  assertions in 180.402 seconds with a 1 GB PHP limit;
- the page-identity browser matrix passed 91 primary audits, seven call flows,
  seven message-details flows, seven neighbor-profile flows, seven share flows,
  and 34 screenshots across 320-1920 pixels, EN/RU/LT, forced colors, reduced
  motion, and effective 200% zoom;
- all 57 audited profile values, targeted section/pet/routine/community icon
  maps, 44-pixel targets, clipping, overflow, raw-key, and console checks
  passed; RU 375px and EN 1440px screenshots were visually reviewed;
- full Pint and Larastan, Composer strict validation, locked audit, PHP
  platform requirements, npm audit, JavaScript syntax, Vite 8.2 production
  build, and isolated config/event/route/view cache compilation passed;
- fresh disposable SQLite applied, fully rolled back, reapplied, and seeded all
  138 migrations, retained 219 tables and five users across repeated seeding,
  and the 180-route smoke, both forum-source checks, deterministic
  38,377-requirement generation, PHP localizer, and 859-call canonical icon
  audit passed.

The reusable browser command is
`npm run test:browser:page-identity`.

## Owner Profile Localization And Icon Verification

`OwnerProfileLocalizationTest` guards exact 131-leaf EN/LT/RU parity, stable
tab and audience codes, prepared presentation and action data, passive Blade
boundaries, the six overview section icons, and tab-aware query isolation.

Observed package evidence on 2026-08-04:

- exact-tree focused suite: 10 tests and 1,090 assertions;
- affected profile, preview, responsive, page-identity, linked-media, and
  authentication-boundary slice: 128 tests and 2,368 assertions;
- the page-identity browser matrix passed 91 primary audits plus seven each of
  call-stage, message-details, share, neighbor-profile, and owner-profile
  flows across 320-1920 pixels, EN/RU/LT, forced colors, reduced motion, and
  effective 200% zoom;
- all 54 owner-profile values, stable codes, hero, summary, tab, audience,
  section, and badge icon maps, visible focus, 44-pixel targets, clipping,
  overflow, raw-key, and console checks passed;
- the first browser run exposed 42-pixel audience controls at 320 pixels; the
  repeated production-build run passed after restoring the canonical
  44-pixel minimum and produced 36 screenshots;
- exact-tree complete sequential suite: 3,055 tests and 105,425 assertions in
  176.605 seconds with a 1 GB PHP limit;
- exact-tree full Pint and Larastan, Composer strict validation, locked audit,
  PHP 8.5 platform requirements, npm audit, JavaScript syntax, Vite 8.2 build,
  config/event/route/view cache compilation, 179-route and 173-first-party
  route smoke, both forum-source checks, deterministic 38,377-requirement
  generation, PHP localizer, and 859-call canonical icon audit passed;
- disposable SQLite applied all 139 migrations, fully rolled them back,
  reapplied them, retained 219 tables and five users, and passed repeated
  complete seeding.

The reusable browser command is
`npm run test:browser:page-identity`.

## Messaging Call Stage And Details Verification

`MessageCenterLocalizationTest` now also covers stable call-state persistence,
current-locale presentation, video preflight, connection, reconnect,
audio-only transition, class-prepared control/icon maps, and the responsive
conversation-details route.

Observed package evidence on 2026-08-04:

- focused suite: 25 tests and 3,394 assertions;
- affected messaging, preview, responsive, page-identity, and linked-media
  slice: 98 tests and 4,367 assertions;
- complete sequential shared-checkout suite: 2,998 tests and 102,279 assertions
  in 187.881 seconds with a 1 GB PHP limit;
- full Pint and Larastan, Composer strict validation, locked audit, PHP
  platform requirements, npm audit, production Vite build, and JavaScript
  syntax passed;
- the page-identity browser matrix passed 91 primary audits, seven real
  video-preflight flows, seven details-route flows, and 30 screenshots across
  320-1920 pixels, EN/RU/LT, forced colors, reduced motion, and effective 200%
  zoom;
- the call audits found no missing or English fallback copy, unstable state
  code, noncanonical control icon, focus failure, clipped label, unreachable
  footer, sub-44-pixel target, dialog overflow, or console error;
- compact details showed only the context surface with a localized return
  action, while wide desktop retained the complete three-column conversation.
- fresh disposable SQLite applied 138 migrations, retained 219 tables and five
  users, and passed repeated complete seeding;
- isolated config/event/route/view cache compilation, the 180-route smoke,
  both forum-source checks, deterministic 38,377-requirement generation, the
  PHP localizer, and the 854-call canonical icon audit passed.

The reusable browser command is
`npm run test:browser:page-identity`.

## Share Page Localization And Icon Verification

`SharePageLocalizationTest` guards exact 42-leaf EN/LT/RU parity, placeholder
parity, five stable target families, three stable delivery-channel codes,
prepared message/subject/recipient copy, canonical icons, destination schemes,
domain isolation, and the zero-query `SharePresenter` boundary.

Observed package evidence on 2026-08-04:

- focused suite: 11 tests and 458 assertions;
- affected preview, responsive, page-identity, linked-media, and portal access
  slice: 124 tests and 1,698 assertions;
- stabilized complete sequential shared-checkout suite: 3,009 tests and
  102,905 assertions in 183.677 seconds with a 1 GB PHP limit;
- the page-identity browser matrix passed 91 primary audits, seven call flows,
  seven message-details flows, seven share flows, and 32 screenshots across
  320-1920 pixels, EN/RU/LT, forced colors, reduced motion, and effective 200%
  zoom;
- all 30 visible share values, three channel codes, 13 page icons, 44-pixel
  targets, clipping, overflow, raw-key, and console checks passed; RU 375px and
  EN 1440px screenshots were visually reviewed;
- full Pint and Larastan, Composer strict validation, locked audit, PHP
  platform requirements, npm audit, JavaScript syntax, Vite 8.2 production
  build, and isolated config/event/route/view cache compilation passed;
- fresh disposable SQLite applied, fully rolled back, reapplied, and seeded all
  138 migrations, retained 219 tables and five users across repeated seeding,
  and the 180-route smoke, both forum-source checks, deterministic
  38,377-requirement generation, PHP localizer, and 854-call canonical icon
  audit passed.

The reusable browser command is
`npm run test:browser:page-identity`.
## Onboarding Authentication Boundary Coverage

The focused authentication/onboarding matrix covers enabled and disabled
registration, session regeneration, recipient-locale verification mail,
delivery failure recovery, signed/expired/tampered/wrong-user/replayed
verification links, persisted login resume, password confirmation, first safe
intended URL ownership, parser-confusing redirect inputs, account-status and
verification precedence, representative portal routes, localized JSON
conflicts, pet-route least privilege, direct pet Actions, real stale Livewire
snapshots, logout/login resume, completed rows, and legacy no-row accounts.

Run database-backed files sequentially through `scripts/run-tests.php`. A PHP
process crash is environment evidence, not a passing or failing assertion;
rerun the same isolated file and record both the crash and the deterministic
result.
