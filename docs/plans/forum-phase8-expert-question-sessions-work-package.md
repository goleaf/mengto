# Phase 8 Work Package: Verified Professional Question Sessions

Status: verified

Last updated: 2026-07-31

## Requirement Scope

This package plans, implements, and verifies exactly these 18 atomic
requirements from source section 69:

- `forum.feature.3211`: expert question sessions
- `forum.feature.3212`: support verified professional question sessions
- `forum.feature.3213`: complete session feature set
- `forum.feature.3214`: verified host
- `forum.feature.3215`: professional scope
- `forum.feature.3216`: jurisdiction
- `forum.feature.3217`: session topic
- `forum.feature.3218`: scheduled question window
- `forum.feature.3219`: question queue
- `forum.moderation.0326`: question moderation
- `forum.feature.3220`: answer status
- `forum.feature.3221`: unanswered status
- `forum.feature.3222`: source links
- `forum.feature.3223`: session archive
- `forum.feature.3224`: correction
- `forum.feature.3225`: disclaimer
- `forum.moderation.0327`: session, question, and answer reporting
- `forum.feature.3226`: no presentation as individual medical diagnosis or
  formal legal representation

No requirement outside this list will be marked verified by this package.
Cross-cutting authorization, validation, localization, accessibility,
moderation, privacy, security, migration, factory, seeder, performance, and
documentation requirements remain mandatory acceptance constraints.

## Required Reading And Repository State

Before implementation, this package was grounded in:

1. `docs/requirements/forum-source-prompt.md`, including source section 69;
2. `docs/requirements/forum-master-requirements.md`;
3. `docs/requirements/forum-requirements.json`;
4. `docs/traceability/forum-requirements-matrix.md`;
5. `docs/plans/forum-current-progress.md`;
6. current expert profiles, credentials, credential-review Actions, policies,
   translations, factories, seeders, and tests;
7. current forum topics, answers, guides, reports, notifications, moderation,
   reputation, and professional-access boundaries;
8. current forum routes, Livewire conventions, Blade architecture, supported
   locales, cache configuration, and deployment constraints;
9. the current branch, staged diff, unstaged diff, and untracked files.

The repository is on `main` at `d3f8ca3`, synchronized with `origin/main`.
The worktree contains unrelated authentication, profile, place-directory,
photo-interaction, review-summary, security-hardening, package, style,
translation, route, documentation, and test changes. They predate this
package and must not be overwritten or included in its commit.

The latest accepted isolated checkpoint is 1,514 tests and 53,062 assertions.
The latest Pint, Larastan, Vite, fresh migration, repeat-seed, cache
compilation, architecture, and responsive browser gates passed at that
checkpoint.

## Current Implementation Analysis

- `ExpertProfile` owns the public professional identity and exposes a stable
  profile type, country, publication state, and projected verification state.
- `Credential` provides independently reviewed status, jurisdiction, scope,
  expiry, renewal, suspension, revocation, private evidence, and audit
  history. Credential files and reviewer notes are hidden.
- `ForumProfessionalAccess` checks a current published profile but does not
  provide the session-specific scope and jurisdiction decision required here.
- Forum reputation, answer acceptance, and administrative status are separate
  from professional credentials and cannot qualify a host.
- `ForumReport` and `SubmitForumReport` already provide a privacy-safe
  polymorphic report boundary. The session domain must extend it instead of
  creating a second complaint system.
- `ForumNotification` supports local, deduplicated in-application notices
  without making sessions depend on a queue, websocket, cron, or scheduler.
- Collaborative guides already establish append-only versions, corrections,
  source links, and independent expert-review conventions. Sessions may reuse
  those principles but need their own scheduled queue aggregate.
- Existing topics and answers are asynchronous discussions. Reinterpreting
  them as scheduled professional sessions would weaken host verification,
  queue moderation, jurisdiction, correction, and archive invariants.
- No durable expert-session tables or routes exist.
- `docs/forum-scope.md` historically deferred live expert sessions. The
  current canonical master specification explicitly requires them, so that
  line is obsolete under the documented precedence rules.

## Desired Result

Create a durable expert-session domain in which:

- only an active owner of a published, current, independently verified expert
  profile and compatible current credential can host;
- the session records a stable professional scope and jurisdiction;
- question opening and closing are timestamp-derived and do not require cron;
- question submissions are idempotent, bounded, moderated, and ordered;
- a host can answer only eligible queued questions within the authorized
  session boundary;
- unanswered questions remain explicitly distinguishable from removed,
  declined, withdrawn, and answered questions;
- answer sources are validated HTTP(S) links and never fetched by the server;
- corrections create immutable previous-version evidence;
- archival preserves questions, answers, corrections, reports, and history;
- session, question, and answer reports reuse unified moderation;
- every session displays a localized non-diagnosis and non-legal-
  representation disclaimer;
- medical and legal subject matter never acquires a platform-guaranteed
  diagnosis, prescription, or formal legal-advice state;
- normal class-based Livewire components with separate passive Blade templates
  provide directory and workspace flows;
- all user-facing text exists in EN, LT, and RU.

## Domain Boundary

`ForumExpertSession` is a scheduled professional community event, not an
appointment, telemedicine consultation, legal engagement, ordinary forum
topic, or verified guide. It does not collect patient records, credential
documents, payments, or private professional-client communications.

The package uses:

- `ForumExpertSession` for host identity, scope, jurisdiction, schedule,
  lifecycle, topic, summary, locale, and disclaimer version;
- `ForumExpertSessionQuestion` for the ordered moderated queue;
- `ForumExpertSessionAnswer` for the current published answer;
- `ForumExpertSessionCorrection` for immutable answer revisions;
- `ForumExpertSessionHistory` for append-only lifecycle and moderation
  evidence;
- existing `ExpertProfile` and `Credential` for host qualification;
- existing `ForumReport` for complaints;
- existing `ForumNotification` for supported local notifications.

Professional scope remains an extensible stable key. The session validates it
against the host profile and reviewed credential rather than introducing a
closed enum or translating identifiers.

## Expected Files

Expected additions:

- expert-session lifecycle, question, answer, and moderation enums;
- typed create-session data;
- session, question, answer, correction, and history models;
- policies for session, question, and answer;
- host-eligibility, create, question-submit, question-moderate, answer,
  correction, archive, report, notification, and audit operations;
- class-based `ForumExpertSessionDirectory` and
  `ForumExpertSessionWorkspace` Livewire components;
- dedicated Livewire form objects and separate Blade templates;
- thin route shell controllers and wrapper views;
- one additive migration;
- factories for every new ordinary and append-only model;
- production-safe definition/backfill and environment-gated demo seeders;
- EN, LT, and RU `forum_expert_sessions.php` catalogues;
- `tests/Feature/Forum/ExpertQuestionSessionWorkflowTest.php`;
- `docs/expert-question-sessions.md`.

Expected modifications:

- `SubmitForumReport`, `User`, database seeder orchestration, forum route
  definitions, navigation where appropriate, factory/seeder coverage checks,
  architecture checks, localization checks, requirement evidence, progress,
  canonical architecture/data/auth/privacy/security/Livewire/accessibility/
  testing/seeding/operations docs, scope, decisions, and changelog.

The unrelated dirty files listed in the repository-state section are excluded
unless an additive hunk is unavoidable. Any unavoidable shared-file hunk must
be staged and reviewed independently.

## Schema Changes

The additive migration will create:

### `forum_expert_sessions`

- internal ID, immutable stable key, and unique creation idempotency key;
- host expert profile and creator user foreign keys;
- preserved host name and professional-scope snapshots;
- jurisdiction, title, summary, locale, lifecycle status, and disclaimer
  version;
- question opening and closing timestamps plus the preserved IANA time zone;
- session start and end timestamps;
- archive actor, timestamp, and localized reason;
- optimistic lock version, timestamps, and bounded directory indexes.

### `forum_expert_session_questions`

- session and author foreign keys;
- stable key and unique idempotency key;
- plain-text question body;
- queue state, moderation state, queue position, and user-safe moderation
  reason;
- selected, answered, declined, withdrawn, and removed timestamps;
- optimistic lock version and timestamps;
- unique session/queue-position and bounded session/state/order indexes.

### `forum_expert_session_answers`

- one answer per question;
- session, question, and host-author foreign keys;
- stable key, idempotency key, plain-text body, validated source-link JSON,
  answer state, current version, answered timestamp, and timestamps;
- unique question and idempotency constraints.

### `forum_expert_session_corrections`

- answer, session, and actor foreign keys;
- previous and corrected body/source snapshots;
- plain-text correction reason, version number, and immutable timestamp;
- unique answer/version constraint.

### `forum_expert_session_history`

- session, optional question/answer/actor relations;
- event type, prior/new state, reason code, localized summary key, safe
  metadata, optional unique idempotency key, and immutable timestamp.

## Data Migration Strategy

1. Add normalized tables without modifying or dropping existing forum,
   expert, credential, guide, notification, or moderation data.
2. Search first-party storage for a durable legacy expert-session source.
3. If none exists, record a zero-row backfill result rather than inferring
   sessions from ordinary topics, consultations, event titles, or expert
   publications.
4. Never infer professional scope, jurisdiction, verification, question
   status, or legal/medical authority from free text.
5. New production mutations write normalized records only.
6. Demo records are created only in local/demo/testing environments and use
   independently verified factory credentials.

## Rollback Strategy

Before normalized session data exists, the migration can remove only its new
tables. Existing records remain untouched.

After sessions exist:

1. disable session mutations;
2. export sessions, queue questions, answers, corrections, histories, and
   related report identifiers;
3. retain the tables while deploying a reviewed forward fix;
4. never rewrite ordinary topics or consultations as a rollback substitute;
5. do not drop normalized tables with user content.

## Legacy Compatibility Strategy

- Existing forum topics, answers, guides, consultations, expert profiles,
  credentials, reports, notifications, routes, and URLs remain unchanged.
- No topic or consultation is auto-converted.
- Existing credential verification continues to be authoritative.
- Existing supported locales and fallback behavior remain unchanged.
- Existing topics, replies, reactions/votes, subscriptions, bookmarks,
  reports, moderation cases, attachments, pet profiles, adoption cases,
  lost/found cases, marketplace records, translations, and administrator
  categories are preserved.

## Authorization Changes

Policies and Actions will independently authorize:

- public session listing and viewing;
- session creation by a qualified host;
- session update and archival by the host or authorized administrator;
- question submission by an active verified member during the question
  window;
- question withdrawal by its author;
- queue selection, moderation, declining, and answering by the qualified host
  or authorized moderator;
- correction by the qualified answer author or authorized administrator;
- report submission by an active member;
- report inspection only through existing moderation policy.

Every Livewire mutation reauthorizes. Hidden controls, locked IDs, host
snapshots, reputation, badges, and administrator-like labels are not
authorization.

## Validation Changes

- stable keys and idempotency keys use bounded normalized formats;
- title, summary, question, answer, correction reason, jurisdiction, and
  scope lengths are bounded;
- locale must be supported;
- question window must close after opening;
- session start/end must follow the configured question window;
- source links are bounded arrays of unique HTTP(S) URLs with labels;
- unknown source-link keys and unsupported URL schemes are rejected;
- question and answer state transitions use typed transition rules;
- archived sessions reject new submissions and answers;
- host scope and jurisdiction are revalidated when publishing, answering, and
  correcting;
- no complete request or Livewire payload is mass assigned.

## Translation And Interface Changes

- Add complete EN, LT, and RU keys for headings, fields, states, notices,
  errors, actions, loading, empty, archived, unanswered, moderation,
  correction, report, and accessibility text.
- Stable professional scope keys remain untranslated identifiers; known
  labels are translated for presentation.
- Every page has one logical heading, explicit labels, semantic status text,
  linked errors, `aria-live` feedback, visible focus, and 44px controls.
- The directory uses bounded pagination and URL-backed safe filters.
- The workspace shows schedule, scope, jurisdiction, host verification,
  disclaimer, queue state, sources, corrections, and report controls without
  exposing credential evidence.
- Loading targets are action-specific, duplicate submission is disabled, and
  no interaction requires hover or drag-and-drop.

## Cache, Performance, And Runtime

- No new runtime infrastructure is introduced.
- Session phase is derived from timestamps during reads and writes.
- Directory and workspace queries select bounded columns, eager-load required
  relations, and paginate questions.
- Host qualification uses indexed profile/credential filters and never loads
  private evidence.
- No session data is cached initially; measurement must justify later cache.
- Existing report and notification idempotency conventions are retained.
- Source URLs are never fetched, so this package introduces no SSRF surface.

## Security, Privacy, And Abuse Risks

- A user cannot self-assert professional status, scope, or jurisdiction.
- Expired, suspended, revoked, unpublished, or wrong-owner credentials reject
  host mutations.
- Credential identifiers, files, notes, and private evidence never enter
  Livewire state, Blade, notifications, history metadata, or reports.
- Questions, answers, and sources are escaped plain text/links.
- Submission and report actions are rate-limited and idempotent.
- Queue moderation records user-visible reasons without exposing moderator
  private notes.
- The host cannot present platform status as diagnosis, prescription, or
  formal legal representation.
- Archival is non-destructive and cannot erase reports or correction history.
- Concurrency constraints prevent duplicate questions, duplicate answers,
  duplicate corrections, and conflicting queue positions.

## Tests To Create And Update

The focused feature suite will prove:

1. schema constraints, casts, relationships, factories, and reversible
   migration;
2. qualified host ownership, current profile state, credential expiry,
   professional scope, and jurisdiction;
3. unverified, suspended, revoked, expired, wrong-owner, inactive, and
   popularity-only hosts are rejected;
4. session scheduling and timestamp-derived question windows;
5. idempotent question submission, deterministic queue order, withdrawal, and
   direct Livewire authorization;
6. moderation states and safe user-visible reasons;
7. one idempotent answer per eligible question and explicit unanswered state;
8. bounded validated HTTP(S) source links with no remote request;
9. immutable correction history and optimistic conflict protection;
10. non-destructive archive with preserved questions, answers, corrections,
    reports, and history;
11. unified private reports for session, question, and answer;
12. localized disclaimer and medical/legal non-authority language;
13. EN/LT/RU rendering, placeholder parity, and no raw translation keys;
14. bounded directory/workspace queries and no lazy loading;
15. repeatable production/demo seeding and production safeguards;
16. direct route and Livewire policy enforcement;
17. duplicate/concurrent answer and question consistency;
18. architecture rules: no Volt, `@php`, Blade model/service calls, unsafe
    dynamic Tailwind classes, new debug calls, or `env()` outside config.

Existing full suites, migration verifier, seed verifier, Larastan, Pint, Vite,
cache compilation, route inspection, and browser checks will be rerun.

## Documentation To Update

- this work-package plan and current progress;
- architecture decision, assumption, and conflict logs;
- `docs/expert-question-sessions.md`;
- forum scope, product/system requirements, architecture, domain model, data
  model, authorization, privacy, security, frontend, Livewire, accessibility,
  localization, testing, seeding, performance, deployment, and operations;
- current-state/gap/final audit documents;
- implementation plan, changelog, compliance/evidence overlay, and generated
  traceability artefacts.

## Acceptance Criteria

The package is complete only when:

- all five normalized models and constraints exist;
- host eligibility is independently credential-backed;
- scope, jurisdiction, schedule, queue, moderation, answer/unanswered state,
  sources, archive, correction, disclaimer, and reporting work end to end;
- direct Livewire actions authorize and validate all browser input;
- all user-facing text exists in EN, LT, and RU;
- factories and safe seeders pass on a fresh database and repeated run;
- rollback and preservation tests pass;
- focused and full PHP suites, Pint, Larastan, Vite, architecture checks,
  migration/seed verification, cache compilation, and responsive browser
  verification have observed passing results;
- each of the 18 scoped IDs has file/test evidence before it becomes
  `verified`;
- no unrelated dirty change is included in the package commit.

## Verification Procedure

1. inspect package diff and unrelated worktree changes;
2. run `vendor/bin/pint --dirty` during implementation;
3. run focused expert-session, expert-safety, moderation, localization,
   factory/seeder, route, and architecture tests;
4. run Larastan for affected and then full first-party PHP code;
5. run migration rollback and fresh migration/repeat-seed checks in isolated
   testing configuration;
6. run the full serial PHP suite and parallel suite only if isolated database
   topology makes it safe;
7. run coverage when PCOV or Xdebug is available;
8. run `npm run build`, audits, Composer validation/audit, and cache
   compilation;
9. exercise a real host/question/answer/correction/report/archive flow at
   mobile and desktop viewports, inspect console errors, focus, labels,
   overflow, and touch targets;
10. run the requirements generator in check mode;
11. update evidence and rerun the phase completeness audit;
12. stage only attributable files in an isolated index, inspect the staged
    diff, commit, push, and record exact observed results.

## Completion Evidence

- The additive five-table schema, four state enums, five models, seven Actions,
  two domain services, three policies, six Livewire forms, two class-based
  Livewire components, route controllers, passive views, translations,
  factories, and production/demo seeders are implemented.
- Credential eligibility requires a current published independently verified
  profile and a current verified credential matching both professional scope
  and jurisdiction. Administrator status, popularity, and forum reputation do
  not bypass this boundary.
- Questions are rate-limited, idempotent, author-withdrawable, private before
  approval, and deterministically queued. Answers require an eligible host and
  an approved selected question. Sources are bounded validated HTTP(S) links
  and are never fetched by the server.
- Corrections preserve immutable previous versions; archival preserves every
  question, answer, correction, report, and history row. Unified reports cover
  session, question, and answer while keeping reporter identity private.
- EN, LT, and RU include the complete session interface, validation, history,
  reporting, accessibility, and medical/legal non-authority copy.
- Focused expert-session plus architecture verification passed 31 tests and
  22,249 assertions. The expanded forum, expert, report, localization,
  factory/seeder, schema, route, and architecture slice passed 1,108 tests and
  52,428 assertions.
- The detached `f1e2fcc` package snapshot passed 1,554 serial tests and 54,317
  assertions. Pint and Larastan passed with zero findings. Its isolated
  fresh-database verifier applied 96 migrations, found 165 tables, and
  preserved five demo users after the repeated seed. The larger shared
  worktree also passed 1,594 tests and 56,870 assertions before publication.
- Composer strict validation and audit, npm audit, the Vite 8.2 production
  build, and Laravel config/event/route/view/icon cache compilation passed in
  the detached package snapshot.
- Headless Chrome exercised the same expert-session implementation in the
  current shared worktree through a real localized member/host flow at 375x812
  and 1440x900: question submission, queue approval, answer publication,
  immutable correction, private report, and archive all persisted. The final
  archived-page scan retained the answer and correction history with one H1,
  no horizontal overflow, raw translation keys, unnamed controls, undersized
  primary targets, current-page console warnings, or console errors.
- The requirements evidence overlay maps all 18 scoped IDs to implementation,
  test, documentation, and verification evidence. No scoped requirement is
  blocked or intentionally not applicable.
