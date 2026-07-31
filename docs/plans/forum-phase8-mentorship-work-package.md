# Phase 8 Mentorship Work Package

Status: implemented and verified on 2026-07-31.

## Requirement Scope

This work package contains 47 atomic requirements:

- `forum.reputation.0023`, `forum.reputation.0063`,
  `forum.reputation.0109`, `forum.reputation.0244`;
- `forum.feature.2752` through `forum.feature.2768`;
- `forum.search.0031`;
- `forum.category.1390`;
- `forum.feature.2769` through `forum.feature.2782`;
- `forum.reputation.0268`, `forum.reputation.0269`,
  `forum.reputation.0270`;
- `forum.moderation.0034`;
- `forum.search.0032`;
- `forum.feature.2783`;
- `forum.feature.3311`, `forum.feature.3312`;
- `forum.plan.0087`;
- `forum.feature.3348`.

No unrelated group, feed, subscription, marketplace, event, poll, or generic
messaging requirement is claimed by this package.

## Current Implementation Analysis

- The forum already has an append-only reputation ledger with a seeded
  `mentoring` dimension, audited trust levels including `mentor`, and badges
  including `mentor`.
- Professional verification is independent from reputation and evaluates
  credential status and natural expiry.
- The global taxonomy, category tree, user locale/timezone, unified reports,
  report-and-block path, and active-account controls already exist.
- `ForumBlock` can prevent contact, while `SubmitForumReport` currently
  supports forum content and structured cases but not mentorship subjects.
- The current message center is a prototype presentation/state system. It is
  not an acceptable persistence boundary for mentorship communication.
- No mentor opt-in profile, supported scope, matching, request, acceptance,
  private thread, completion, feedback, or validated reputation workflow
  exists.

## Desired Result

Provide optional peer mentorship for all thirteen required types. Mentors opt in
and define bounded supported scopes. Eligible users can discover matched
mentors and request help. The mentor accepts or declines; accepted pairs use a
private, audited in-platform thread. Either party can end, block, or report.
Completion feedback is optional. Mentoring reputation is granted only after an
administrator validates abuse-resistant completion evidence.

Mentors are always presented as peers. Professional authority is displayed
only when an independent, current professional credential exists.

## Affected Files

Expected new files:

- additive mentorship migration;
- enums for mentorship type, profile state, request state, and event type;
- `ForumMentorProfile`, `ForumMentorScope`, `ForumMentorship`,
  `ForumMentorshipMessage`, `ForumMentorshipFeedback`, and
  `ForumMentorshipEvent`;
- one factory per model;
- typed request/profile data objects;
- matching and eligibility services;
- Actions for opt-in, request, response, message, completion/end, feedback,
  and validation;
- `ForumMentorshipPolicy`;
- a class-based Livewire mentorship workspace and separate Blade view;
- EN/LT/RU translations;
- focused feature, policy, privacy, concurrency, schema, factory, and Livewire
  tests;
- `docs/mentorship.md`.

Expected modifications:

- User relationships;
- unified report subject support and counterpart resolution;
- authenticated forum routes or forum administration discovery;
- factory/seeder coverage;
- architecture, domain, data, authorization, Livewire, security, operations,
  testing, progress, changelog, and traceability documents.

## Schema Changes

The migration is expand-only:

- one mentor profile per user with status, public-safe summary, languages,
  location/timezone, availability, communication preferences, capacity,
  safety acknowledgement, and optimistic lock;
- normalized mentor scopes with mentorship type, optional category/taxon,
  experience summary, professional-verification requirement, stable scope key,
  activity state, and matching indexes;
- mentorship requests with mentor, mentee, scope, type, state, request text,
  matching context, both safety acknowledgements, communication preference,
  lifecycle timestamps, completion validation, idempotency, and optimistic
  lock;
- private participant messages with immutable sender/body/timestamp and
  idempotency;
- optional per-party completion feedback with database uniqueness;
- append-only lifecycle events.

No existing message, forum, report, reputation, taxonomy, or user table is
replaced.

## Data Migration Strategy

No legacy mentorship rows exist. The feature starts empty and opt-in. Existing
users, messages, blocks, reports, credentials, categories, taxa, trust,
badges, and reputation records remain untouched.

## Rollback Strategy

Before production use, rollback drops only new mentorship tables in
foreign-key-safe order. After mentorship data exists, application rollback
must retain the additive tables. Recovery uses forward state transitions and
append-only events, not history deletion.

## Legacy Compatibility Strategy

The existing message center remains unchanged for current prototype
conversations. Mentorship uses a dedicated private thread because its
authorization, lifecycle, safety acknowledgement, retention, reporting, and
audit requirements are stricter. Existing forum blocks and reports are reused
instead of creating parallel safety systems.

## Authorization Changes

- Only active, email-verified users with current mentor trust or
  administrators may activate a mentor profile.
- Only the profile owner manages profile/scopes.
- A mentee cannot request themselves, a blocked user, an inactive mentor, an
  inactive scope, or a mentor at capacity.
- Only the selected mentor accepts or declines.
- Only both active participants view the mentorship or post a thread message.
- Either participant may end, block, report, or submit one optional feedback
  record.
- Only administrators validate completion and trigger mentoring reputation.
- Professional scope display requires a current independent credential.
- Direct Livewire calls repeat policy/action authorization.

## Validation Changes

Use closed enums for profile/request/type states. Validate locale/language,
timezone, bounded public summary, capacity, availability, communication
preferences, category/taxon ownership, request reason, messages, feedback,
state transitions, safety acknowledgement, lock version, and idempotency.
Mass assignment uses explicit field maps.

## Translation Changes

Add stable `forum_mentorship` keys for all thirteen types, states, fields,
actions, boundaries, safety notices, professional labels, matching reasons,
empty/loading/offline/success/error states, validation, reports, and
accessibility text in EN/LT/RU.

## Interface Changes

Add one class-based Livewire workspace with:

- mentor opt-in/profile form;
- supported-scope management;
- bounded matched-mentor results with transparent matching reasons;
- request form and safety acknowledgement;
- participant request/active/completed views;
- private thread;
- accept, decline, end, block/report, complete, and optional feedback actions;
- administrator completion validation where authorized.

The interface is keyboard and touch operable, uses semantic headings and
labels, has precise loading targets, visible errors/status, offline feedback,
no drag-only controls, and no user-facing hardcoded text.

## Accessibility Changes

Provide one logical page heading, field labels, error summary, status
announcements, visible focus, minimum 44px controls, non-color state labels,
descriptive action names, reduced-motion-safe feedback, and responsive
single-column behavior on mobile.

## Cache Changes

No permission-sensitive mentorship or message data is globally cached.
Bounded match results are queried directly for correctness. Any later cache
must include requester, locale, scope, block, trust, credential, and profile
versions and deterministic invalidation.

## Security Risks

- counterpart enumeration;
- blocked-user contact;
- private thread disclosure;
- mentor impersonation or false professional authority;
- duplicate requests/messages;
- unsafe off-platform contact requests;
- reputation farming;
- completion validation by an involved party;
- stale-state/concurrent transitions.

Controls include policy-scoped reads, participant IDs derived server-side,
block checks in every contact mutation, escaped messages, bounded text,
database uniqueness, idempotency, row locks, explicit professional status,
administrator validation, and immutable events.

## Privacy Risks

Mentor location is a broad optional scope, never an exact address. Messages,
request text, feedback, and report evidence are participant/moderator-only.
Public match cards expose only opted-in summary, types, broad scope,
availability, languages, and independently derived professional status.

## Abuse Risks

Requests are rate limited and capped. A pair cannot hold duplicate open
mentorships for the same scope. Blocks prevent new requests/messages.
Completion reputation requires validated lifecycle evidence and is
idempotent. Either participant can end, block, and submit a unified report.
Mentorship never grants medical, legal, or professional authority.

## Tests To Create

- all thirteen mentorship types;
- profile opt-in, pause, capacity, scope validation, and ownership;
- category/taxon/language/location/time/communication matching;
- reputation/trust and independently verified-expertise matching;
- self, block, inactive, unverified, capacity, duplicate, and rate-limit
  rejection;
- accept/decline and every state transition;
- both safety acknowledgements and boundary copy;
- participant-only thread, escaped output, idempotent messages, and block
  enforcement;
- either-party end, block, and report;
- optional feedback and one-feedback-per-party constraint;
- completion validation, independent administrator, no self-validation,
  idempotent mentoring reputation, and no premature reputation;
- no professional label from karma/trust alone;
- append-only events and concurrency constraints;
- direct Livewire authorization, locked identity, validation, localization,
  loading/offline markup, and responsive rendering;
- factory defaults/states and fresh/repeat seed compatibility.

## Tests To Update

- `FactoryAndSeederTest`;
- `SchemaIntegrityTest`;
- `ArchitectureComplianceTest`;
- `LocalizationTest`;
- unified moderation tests for mentorship reporting.

## Documentation To Update

- `docs/mentorship.md`;
- architecture, domain model, data model, authorization, Livewire, security,
  operations, testing, seeding coverage, progress, gap analysis, completeness
  audit, changelog, and traceability evidence.

## Acceptance Criteria

- All 47 scoped IDs have passing file/test evidence.
- All required types, matching inputs, and workflow steps are represented.
- Private thread and requests are inaccessible to outsiders and blocked users.
- Reputation is absent before independent completion validation and
  idempotently recorded afterward.
- Trust/reputation never creates professional status.
- Every state mutation is authorized, validated, transactional, audited, and
  race-safe.
- EN/LT/RU, factories, schema, full PHP tests, Pint, Larastan, fresh/repeat
  seed, Vite, and mobile/desktop browser checks pass.

## Verification Procedure

1. Run focused schema/policy/action/Livewire mentorship tests.
2. Run moderation, reputation, architecture, localization, and factory tests.
3. Run Pint and targeted/full Larastan.
4. Run the serial full Pest suite.
5. Run the isolated fresh migration and repeated seed verifier.
6. Run the production Vite build.
7. Inspect public/participant/admin views at 375x812 and 1440x900 for focus,
   overflow, touch targets, raw keys, privacy, and console output.
8. Regenerate requirement and seeding matrices.
9. Record evidence for all 47 IDs and run a package completeness audit.

## Completion Evidence

- Additive schema: six mentorship tables, required foreign keys, uniqueness,
  leading indexes, optimistic locks, idempotency, and restrictive
  audit-history deletion behavior.
- Runtime: six models/factories, four enums, typed data objects, matcher and
  eligibility services, eight mutation Actions, audit service, policy, User
  relationships, and unified report support.
- Interface: three class-based Livewire components with separate Blade
  templates, reusable taxonomy selector, authenticated route, forum navigation
  entry, EN/LT/RU parity, 88 report reasons, explicit truthfulness confirmation,
  and no forbidden Blade behavior.
- Seeding: environment-gated `MentorshipDemoSeeder` creates a deterministic
  active end-to-end graph and repeats without duplicate records.
- Focused verification:
  `php artisan test tests/Feature/Forum/MentorshipWorkflowTest.php
  --stop-on-failure` passed 29 tests and 117 assertions.
- Cross-domain verification passed 899 tests and 43,799 assertions.
- Full serial Pest passed 1,263 tests and 46,373 assertions.
- Full Larastan passed with zero errors; Vite 8.2.0 production build passed.
- Fresh verification passed 91 migrations and 135 tables; fresh and repeated
  seed exits were zero and the user count remained five.
- Playwright at 375x812 and 1440x900 found one H1, no horizontal overflow, raw
  translation keys, unnamed/unlabeled controls, workflow targets below 44px,
  or console warnings/errors. A real private Livewire message was submitted
  successfully.
- All 47 scoped requirement IDs are represented by the deterministic evidence
  overlay and regenerated traceability matrix.
