# Phase 8 Work Package: Events, Attendance, And Clubs

Status: verified

Last updated: 2026-07-31

## Requirement Scope

This package plans, implements, and verifies exactly these 27 atomic
requirements from source section 68:

- `forum.feature.3187`: events, attendance, and clubs
- `forum.feature.3188`: extend events with a durable workflow
- `forum.feature.3189`: organizer
- `forum.feature.3190`: verified organizer status
- `forum.feature.3191`: event type
- `forum.feature.3192`: species and taxon scope
- `forum.feature.3193`: location
- `forum.feature.3194`: online or physical format
- `forum.feature.3195`: start and end
- `forum.feature.3196`: capacity
- `forum.feature.3197`: waitlist
- `forum.feature.3198`: registration
- `forum.feature.3199`: invitation
- `forum.feature.3200`: attendance requirements
- `forum.feature.3201`: vaccination requirements where lawful
- `forum.feature.3202`: animal age restrictions
- `forum.interface.0009`: accessibility
- `forum.feature.3203`: cost
- `forum.feature.3204`: refund policy
- `forum.feature.3205`: cancellation
- `forum.feature.3206`: updates
- `forum.feature.3207`: attendee communication
- `forum.moderation.0325`: event reporting
- `forum.feature.3208`: post-event review
- `forum.feature.3209`: photo consent
- `forum.feature.3210`: animal welfare rules
- `forum.plan.0076`: emergency contact plan

No requirement outside this list will be marked verified by this package.
Cross-cutting authorization, validation, localization, accessibility,
moderation, taxonomy, migration, factory, seeder, performance, and
documentation requirements remain mandatory acceptance constraints.

## Required Reading And Repository State

Before implementation, this package was grounded in:

1. `docs/requirements/forum-source-prompt.md`, including source section 68;
2. `docs/requirements/forum-master-requirements.md`;
3. `docs/requirements/forum-requirements.json`;
4. `docs/traceability/forum-requirements-matrix.md`;
5. `docs/plans/forum-current-progress.md`;
6. `docs/superpowers/specs/2026-07-29-events-mvp-design.md`;
7. current meetup routes, controllers, presenters, catalogues, session state,
   forms, Blade components, translations, factories, seeders, policies, and
   tests;
8. current forum group, taxonomy, professional-verification, report,
   notification, and user models;
9. the current branch, staged diff, unstaged diff, and untracked files.

The repository is on `main` at `5dd1b9c`, synchronized with `origin/main`.
The worktree contains unrelated place-directory changes in
`app/View/Components/PlaceDirectory.php`, `resources/scss/app.scss`,
place-directory Blade components, and `tests/Feature/PlaceDirectoryTest.php`.
Those files predate this package and must not be edited, staged, or committed
with event work.

The latest complete serial checkpoint is 1,437 tests and 51,568 assertions.
The latest dependency, build, static-analysis, migration, seeding,
localization, and architecture gates passed at that checkpoint.

## Current Implementation Analysis

- `/meetups` renders eight translated catalogue records through
  `EventCatalog`, `EventContentCatalog`, and `EventPresenter`.
- All registration, waitlist, prototype payment, check-in, calendar,
  reminder, messaging, announcement, review, report, schedule, and event
  status mutations are stored in a user-scoped `UserDomainState` JSON
  snapshot through `EventState`.
- A cache lock protects only a single user's session registration mutation.
  It cannot enforce global event capacity, one registration per user, stable
  waitlist order, organizer authorization, or cross-user concurrency.
- Existing event routes accept a hard-coded list of catalogue keys. There is
  no durable event route binding or administrator-created event support.
- Organizer verification is a catalogue label rather than a projection of
  the independent credential domain.
- Exact locations, online links, attendance notes, vaccination notes, and
  emergency plans have no normalized ownership or encrypted persistence
  boundary.
- Existing group activities persist basic title, dates, format, location, and
  capacity, but they do not own registration, attendance, invitations,
  taxon scope, privacy, reviews, reports, or event history.
- `ForumGroup` already represents clubs and private subcommunities. A second
  club model is not justified.
- `Taxon` and the `forum_group_taxon` relation already provide stable animal
  identity. Event species scope must reuse that global taxonomy.
- `ExpertProfile`, `Credential`, and `ForumProfessionalAccess` already provide
  independent, expiry-aware professional verification. Event reputation or
  organizer activity must never create verification.
- `SubmitForumReport` already owns privacy-safe polymorphic reports and must be
  extended for events instead of creating an event-only complaint table.
- The existing `ForumNotification` model supports in-application
  notifications without requiring a new queue, websocket, cron, or external
  notification dependency.
- Existing application CSS and shell already provide visible focus,
  reduced-motion handling, minimum touch targets, responsive grids, semantic
  status presentation, and localized navigation. Event controls must reuse
  those conventions.

## Desired Result

Create one durable event domain in which:

- every event has a stable key, organizer identity, type, visibility, format,
  timezone-aware start and end, capacity, requirements, cost, cancellation
  terms, animal-welfare rules, and a protected emergency plan;
- event-to-taxon links use the global taxonomy and can represent a species or
  an appropriate higher rank without duplicating species lists;
- clubs are represented by an optional `ForumGroup` relation;
- organizer verification is calculated from current professional
  verification and is never inferred from popularity, karma, or event count;
- exact locations and online access links are disclosed only to authorized
  organizers and eligible attendees;
- registration, approval, waitlist promotion, cancellation, check-in, and
  capacity changes are transactional, idempotent, and database constrained;
- invitations have an audited state and cannot bypass event visibility,
  account status, capacity, or registration validation;
- attendance requirements, lawful vaccination requirements, animal age
  limits, accessibility information, and photo consent are explicit;
- paid-event metadata is represented in integer minor units, but the
  application never pretends to charge money without a real payment
  integration;
- cancellation, rescheduling, public updates, attendee messages, and
  post-event reviews preserve history;
- event reports reuse unified moderation;
- the public directory and event workspace are normal class-based Livewire 4
  components with separate passive Blade templates;
- the existing stable meetup URLs and first-party catalogue records survive
  an idempotent additive backfill.

## Domain Boundary

`ForumEvent` is the platform event aggregate. `ForumGroup` remains the club
aggregate. `ForumGroupActivity` remains a compatibility projection for
already-created group calendar items and receives a nullable link to the
canonical event. New group activities create a canonical event in the same
transaction.

The package uses:

- `ForumEvent` for identity, schedule, format, visibility, capacity, public
  location, protected access details, rules, cost, cancellation, and lifecycle;
- `ForumEventRegistration` for one user's application, waitlist, attendance,
  consent, and protected requirements note;
- `ForumEventInvitation` for explicit internal invitations;
- `ForumEventUpdate` for append-only public or attendee updates;
- `ForumEventMessage` for bounded attendee communication;
- `ForumEventReview` for one eligible post-event review per user;
- `ForumEventHistory` for immutable mutation evidence;
- `forum_event_taxon` for global taxonomy scope;
- existing `ForumReport` for complaints;
- existing `ForumNotification` for supported in-application notices;
- existing `ExpertProfile` and `Credential` records for organizer
  verification.

## Expected Files

Expected additions:

- event, format, visibility, registration, invitation, update, consent, and
  review enums under `app/Enums`;
- typed create-event and registration data under `app/Data`;
- event, registration, invitation, update, message, review, and history
  models;
- an event policy;
- create, register, cancel, promote, invite, publish-update, send-message,
  submit-review, cancel-event, report, backfill, and audit Actions/services;
- class-based `ForumEventDirectory` and `ForumEventWorkspace` Livewire
  components with separate form objects and Blade templates;
- thin shell controllers and event wrapper views;
- one additive event migration;
- factories for every new model;
- production-safe legacy backfill and environment-gated demo seeders;
- EN, LT, and RU `forum_events.php` catalogues;
- `tests/Feature/Forum/EventWorkflowTest.php`;
- `docs/events.md`.

Expected modifications:

- `ForumGroup`, `ForumGroupActivity`, `User`, `SubmitForumReport`,
  `CreateForumGroupActivity`, route definitions, meetup controllers, and
  event presentation entry points;
- database orchestration, factory/seeder coverage tests, architecture tests,
  schema checks, localization checks, and route coverage;
- canonical architecture, domain, data, authorization, privacy, security,
  Livewire, accessibility, localization, seeding, testing, performance,
  migration, operation, progress, audit, changelog, evidence, and generated
  traceability documents.

No place-directory or place stylesheet file belongs to this package.

## Schema Changes

The additive migration will create:

### `forum_events`

- internal ID, immutable stable key, and unique creation idempotency key;
- optional organizer user, preserved organizer key, and organizer name
  snapshot;
- optional `ForumGroup` club relation;
- title, summary, event type, visibility, format, status, and locale;
- timezone-aware start and end;
- nullable capacity, registration policy, and waitlist flag;
- public location scope plus encrypted exact location and online link;
- attendance, lawful vaccination, animal-age, accessibility, photo-consent,
  and animal-welfare requirements;
- cost in integer minor units, ISO currency, and refund policy;
- encrypted emergency contact plan;
- system-management and legacy-source metadata;
- cancellation actor, timestamp, reason, optimistic lock version, archive
  timestamp, and timestamps;
- indexes for public schedule, organizer schedule, club schedule, type,
  status, and location discovery.

### `forum_event_taxon`

- event and taxon foreign keys;
- primary-scope flag and timestamps;
- unique event/taxon relation and reverse lookup index.

### `forum_event_registrations`

- event, user, optional pet profile, stable key, and unique idempotency key;
- unique event/user relation;
- state, attendance format, guest count, photo consent, accepted requirements;
- encrypted requirements note;
- stable waitlist position, check-in method and timestamp;
- cancellation timestamp and reason;
- optimistic lock version and timestamps;
- indexes for event/state/waitlist, user/state, and check-in reporting.

### `forum_event_invitations`

- event, inviter, invited user, stable key, idempotency key, state, expiry,
  response timestamp, and timestamps;
- unique event/invited-user relation;
- recipient/state and event/state indexes.

### `forum_event_updates`

- event, author, stable key, idempotency key, type, audience, title, body,
  publication timestamp, and timestamps;
- event/audience/time and author/time indexes.

### `forum_event_messages`

- event, sender, stable key, idempotency key, audience, plain-text body, and
  timestamps;
- bounded event/time and sender/time indexes.

### `forum_event_reviews`

- event, reviewer, stable key, idempotency key, rating, title, body, status,
  and timestamps;
- unique event/reviewer relation and event/status/time index.

### `forum_event_history`

- event, optional actor and subject user, event type, from/to state, reason
  code, localized summary key, metadata, unique optional idempotency key, and
  immutable creation timestamp.

### `forum_group_activities`

- nullable unique canonical `forum_event_id`;
- existing rows remain intact and can be linked by idempotent backfill.

## Data Migration Strategy

1. Create additive event tables and the nullable compatibility link.
2. Materialize each first-party `EventCatalog` record under its existing key.
   Preserve the URL key, title, content, schedule, format, capacity, location
   scope, cost, and registration rules. Do not create production users merely
   to satisfy organizer ownership.
3. Resolve an organizer user only by an existing stable actor key. Otherwise
   retain the organizer key/name snapshot and keep management admin-only.
4. Convert each existing `ForumGroupActivity` into one canonical event using
   its exact title, summary, dates, timezone, format, location, capacity,
   creator, and group. Link the activity row to that event.
5. Never classify event type, species, vaccination requirements, or sensitive
   location from title keywords.
6. Leave unknown optional fields empty and visible in an authorized migration
   review report.
7. Preserve existing `UserDomainState` event snapshots as read-only migration
   evidence until their user-specific state can be explicitly reconciled.
   New mutations write only normalized event records.
8. Run the backfill idempotently from a production-safe seeder/Action.

## Rollback Strategy

Before normalized event activity, the migration may remove only the newly
created tables and compatibility link. Existing catalogue code, group
activities, users, groups, topics, and social state remain unchanged.

After normalized registrations or updates exist:

1. disable event mutations;
2. export events, registrations, invitations, updates, messages, reviews,
   history, taxon links, and reports;
3. retain encrypted/private fields and event URLs;
4. restore application compatibility with a reviewed forward fix;
5. do not drop event tables or overwrite group activities to recover from a
   deployment defect.

## Legacy Compatibility

- Existing `/meetups` and named routes remain.
- Existing event stable keys remain route identifiers.
- Existing first-party event catalogue records are backfilled without
  changing their keys.
- Existing group activities remain readable and gain a canonical event link.
- Existing topics, replies, reactions, votes, subscriptions, bookmarks,
  reports, moderation cases, attachments, pet profiles, adoption cases,
  lost-and-found cases, marketplace records, translations, and
  administrator-created categories are untouched.
- Existing session event state remains readable only during transition. It
  cannot override database capacity, permissions, or normalized state.
- The prototype payment simulation is not migrated as a completed financial
  transaction. Cost and refund information remain visible; paid registration
  reports that checkout is unavailable until a real provider boundary exists.

## Authorization Changes

Policies will independently authorize:

- public and restricted event viewing;
- private access details;
- event creation;
- organizer management;
- registration, cancellation, check-in, and review;
- invitation creation and response;
- public update publication;
- attendee communication;
- waitlist promotion;
- event cancellation;
- event reporting.

Every Livewire mutation reloads the locked event ID, checks the active user,
authorizes the exact operation, and validates the latest server state.
Hidden buttons, invitation rows, verified organizer labels, and locked
properties are never treated as authorization.

## Validation Changes

Validation covers:

- bounded title and summary;
- known event, format, visibility, registration, consent, and state values;
- timezone and future ordered start/end values;
- capacity, guests, animal ages, cost minor units, currency, and rating;
- public location for physical events and protected URL for online events;
- taxon and pet ownership;
- lawful vaccination context without collecting unnecessary medical records;
- bounded accessibility, welfare, attendance, refund, cancellation, update,
  message, review, and emergency-plan text;
- invitation recipient existence, account state, duplicate invitation, and
  expiry;
- one registration and one review per event/user;
- idempotency keys;
- registration and review eligibility.

## Translation And Interface Changes

- All platform text uses `forum_events.*` keys in EN, LT, and RU.
- User-authored event content remains in its original locale.
- Scientific names remain unmodified; localized common names use the existing
  taxonomy fallback.
- The directory provides bounded search, type, format, species, and schedule
  filters with URL state and pagination.
- The workspace shows public schedule and requirements, organizer
  verification context, capacity, cost, accessibility, welfare, updates, and
  authorized private access details.
- Registration, invitation, update, message, review, cancellation, and report
  forms provide precise loading, success, error, and offline states.

## Accessibility Changes

- One page `h1`, logical headings, labels, error summaries, and field errors.
- Status and capacity are expressed in text and not color alone.
- Exact map/location content has a textual equivalent.
- Every control has an accessible name and minimum 44-pixel target.
- Waitlist and registration changes use an `aria-live` status.
- Dialog-only interaction is avoided; destructive actions use localized
  confirmation plus server authorization.
- No drag-and-drop, hover-only, flashing, or motion-dependent workflow is
  introduced.

## Cache And Performance

- Event directory queries use explicit columns, constrained eager loading,
  counts, and bounded pagination.
- Organizer, group, taxon, registration, update, message, and review data are
  eager loaded or aggregated without per-row queries.
- Capacity reads use registration aggregates; capacity mutations lock the
  event row.
- High-level taxonomy caches remain owned by the taxonomy domain.
- Event-detail caches are not introduced until authorization-sensitive key
  scope and invalidation are measurable.
- Event updates, cancellation, registration, invitations, reviews, and taxon
  changes define deterministic future cache invalidation points.

## Security, Privacy, And Abuse Risks

- Exact locations, online links, requirements notes, and emergency plans are
  encrypted and hidden from serialization.
- Public queries never select protected access fields.
- One-event/user constraints, row locks, idempotency keys, and bounded action
  rates prevent duplicate seats and waitlist races.
- Organizer verification is current, scope-aware professional verification,
  not event popularity or reputation.
- Vaccination requirements are event policies, not proof of participant
  health. Participant health evidence is not collected in this package.
- Paid events do not collect payment credentials or claim successful payment.
- Event reports keep reporter identity private through unified moderation.
- Reviews require eligible historical participation.
- Attendee communication excludes cancelled, declined, and waitlisted users
  unless the message audience explicitly permits them.
- Cancellation cannot silently erase registrations, invitations, messages,
  updates, reviews, reports, or history.

## Tests To Create Or Update

Create focused PHP coverage for:

- all enum and model casts, relationships, hidden fields, factories, and
  factory states;
- create-event validation, idempotency, group association, taxon association,
  and policy boundaries;
- public, member, group, and private visibility;
- current professional verification projection and expiry;
- exact location, online link, requirements note, and emergency-plan privacy;
- open, approval, invitation-only, full, and waitlist registration;
- duplicate and concurrent registration;
- waitlist promotion and stable ordering;
- cancellation, capacity release, history, and notification;
- invitation create/respond/revoke/expiry;
- event updates and attendee communication eligibility;
- lawful vaccination and age restrictions;
- accessibility, cost, refund, photo consent, and welfare rendering;
- post-event review eligibility and uniqueness;
- event report authorization and privacy;
- first-party catalogue and group-activity backfill;
- seeder rerun idempotency and ID preservation;
- Livewire locked state, direct mutation authorization, validation, filters,
  pagination, loading/offline markup, and private payload boundaries;
- no N+1 queries in directory/detail;
- fresh migration, rollback shape, schema constraints, and route coverage;
- EN, LT, and RU rendering and translation-key parity.

Update existing meetup tests to assert normalized behavior without deleting
the route and UI contracts they protect.

## Documentation To Update

- `docs/events.md`
- `docs/architecture.md`
- `docs/domain-model.md`
- `docs/data-model.md`
- `docs/authorization.md`
- `docs/privacy.md`
- `docs/security.md`
- `docs/livewire.md`
- `docs/accessibility.md`
- `docs/localization.md`
- `docs/seeding.md`
- `docs/testing.md`
- `docs/performance.md`
- `docs/migrations.md`
- `docs/operations.md`
- `docs/plans/forum-current-progress.md`
- `docs/audits/forum-gap-analysis.md`
- `docs/audits/forum-final-completeness-audit.md`
- `docs/traceability/forum-implementation-evidence.md`
- `CHANGELOG.md`
- generated requirements and compliance matrix

## Acceptance Criteria

The package is complete only when:

1. all 27 scoped requirements are implemented and evidence-linked;
2. the normalized event schema migrates from zero and rolls back in isolation;
3. first-party catalogue and group activities backfill idempotently;
4. stable event keys and existing meetup URLs remain valid;
5. every new model has a valid factory and meaningful states;
6. production and demo seeders are separated and rerunnable;
7. policies cover every mutation and private read;
8. capacity, waitlist, invitation, cancellation, and review races are
   database constrained and tested;
9. exact access details, notes, and emergency plans are not leaked;
10. event reports reuse unified moderation;
11. class-based Livewire directory and workspace work in EN, LT, and RU;
12. no Volt, `@php`, Blade query, hardcoded platform string, unsafe HTML,
    dynamic Tailwind class, unbounded query, or new operational dependency is
    introduced;
13. targeted and full PHP tests, Pint, Larastan, translation checks,
    architecture checks, fresh migration/seed, Composer checks, npm audit,
    production Vite build, route/config/view caches, and browser checks pass;
14. final documentation and generated traceability reflect actual evidence;
15. only owned event changes are committed and pushed.

## Verification Procedure

After each pass:

1. inspect the owned diff and repository status;
2. run Pint on modified PHP files;
3. run focused event tests;
4. run Larastan on affected code;
5. run localization and architecture checks;
6. run the production frontend build after interface changes;
7. rerun migration and seeder checks after schema/seed changes;
8. update this plan, requirement evidence, and current progress.

Before completion:

- run the full serial PHP suite;
- run the supported parallel suite only if database isolation is safe;
- run coverage when the environment provides a compatible coverage driver;
- run fresh migration and full seeding twice;
- run Composer validation/audit and JavaScript audit;
- run full Pint and Larastan;
- run production Vite, route inspection, config cache, route cache, view cache,
  boot smoke, translation, architecture, query-budget, and browser checks;
- inspect the complete diff and staged diff;
- regenerate requirements and compliance documents;
- verify every scoped ID has file/test evidence;
- commit and push only the owned event package.

## Completion Evidence

All 27 scoped requirements are implemented and evidence-linked.

- `2026_07_31_001230_create_forum_event_tables.php` adds eight normalized
  event tables, the taxon pivot, and the nullable group-activity link. Its
  isolated `down()`/`up()` test preserves legacy group activity rows and
  caught and fixed SQLite unique-index removal ordering.
- Seven event models, seven factories, typed enums/data, two policies, four
  focused services, eleven Actions, eight Livewire form objects, and two
  class-based Livewire components implement the durable workflow.
- The old event mutation endpoint now permits only personal interest,
  calendar, and reminder preferences. Legacy event creation is rejected, and
  legacy create/report composer URLs redirect to the canonical Livewire
  event flow.
- `ForumEventBackfillSeeder` preserves first-party stable keys and group
  activity identity. `ForumEventDemoSeeder` remains environment-gated and
  repeatable.
- `php artisan test tests/Feature/Forum/EventWorkflowTest.php --compact`
  passed 18 tests and 125 assertions, including authorization, validation,
  protected fields, idempotency, capacity/waitlist behavior, invitations,
  cancellation, updates, attendee messaging, reviews, unified reports,
  legacy URL/backfill compatibility, factories, query growth, and reversible
  migration behavior.
- `php artisan test --compact` passed the complete serial repository suite:
  1,514 tests and 53,062 assertions in the isolated staged snapshot.
- The event/meetup/group/social regression slice passed 45 tests and 287
  assertions before the additional rollback regression was added; the final
  serial suite includes that regression.
- Architecture, localization, factory/seeder, and schema coverage passed
  1,049 tests and 49,125 assertions. Full Pint and Larastan passed with zero
  formatting or static-analysis findings.
- `php scripts/verify-fresh-database.php` passed fresh migration and repeated
  seeding with 95 migrations, 160 tables, and a stable demo-user count of 5
  in the isolated staged snapshot.
- Composer strict validation/audit and NPM high-severity audit passed with no
  advisories. Vite 8.2.0 production build and config, route, and Blade cache
  compilation passed.
- A real Lithuanian-member browser flow at 375x812 and 1440x900 exercised
  URL-backed Livewire search and persisted an event registration. Directory
  and detail pages each had one `main` and one `h1`, no horizontal overflow,
  raw translation keys, unnamed buttons, undersized workflow controls,
  failed requests, console warnings, console errors, or page errors.
- Coverage percentage was not measured because the PHP 8.5 CLI has neither
  PCOV nor Xdebug. Parallel Pest was intentionally not run against the shared
  SQLite test topology; the complete suite was run serially.

The canonical evidence overlay and generated requirements matrix contain the
exact implementation, test, and documentation paths. Existing topics,
replies, reactions, votes, subscriptions, bookmarks, reports, moderation
cases, attachments, pet profiles, adoption cases, lost/found cases,
marketplace records, translations, group activities, and
administrator-created categories were not rewritten or deleted.
