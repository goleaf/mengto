# Phase 8 Work Package: Journals And Progress Tracking

Status: implemented and verified

Last updated: 2026-07-31

## Requirement Scope

This package plans, implements, and verifies exactly these 27 atomic
requirements from source section 67:

- `forum.feature.3161`: journals and progress tracking
- `forum.feature.3162`: support topic journals
- `forum.feature.3163`: training journal
- `forum.feature.3164`: behavior journal
- `forum.feature.3165`: recovery journal
- `forum.feature.3166`: weight journal
- `forum.feature.3167`: rehabilitation journal
- `forum.feature.3168`: adoption adaptation journal
- `forum.feature.3169`: foster journal
- `forum.feature.3170`: aquarium journal
- `forum.feature.3171`: terrarium journal
- `forum.feature.3172`: pregnancy and newborn journal
- `forum.feature.3173`: senior-care journal
- `forum.feature.3174`: journal feature set
- `forum.feature.3175`: entries
- `forum.feature.3176`: dates
- `forum.feature.3177`: structured measurements
- `forum.feature.3178`: images
- `forum.feature.3179`: milestones
- `forum.feature.3180`: setbacks
- `forum.security.0020`: privacy
- `forum.feature.3181`: selected collaborators
- `forum.feature.3182`: comments
- `forum.feature.3183`: progress charts where appropriate
- `forum.feature.3184`: export
- `forum.feature.3185`: archive
- `forum.feature.3186`: no harmful gamification or missed-update shame

No requirement outside this list will be marked verified by this package.
Cross-cutting authorization, validation, localization, accessibility,
filesystem, migration, factory, seeder, performance, and documentation rules
remain mandatory acceptance constraints.

## Required Reading And Repository State

Before implementation, this package was grounded in:

1. `docs/requirements/forum-source-prompt.md`, including source section 67;
2. `docs/requirements/forum-master-requirements.md`;
3. `docs/requirements/forum-requirements.json`;
4. `docs/traceability/forum-requirements-matrix.md`;
5. `docs/plans/forum-current-progress.md`;
6. `docs/domain-model.md`, `docs/data-model.md`, `docs/privacy.md`,
   `docs/security.md`, `docs/files.md`, `docs/livewire.md`, and
   `docs/testing.md` where present;
7. current care-journal, forum-topic, topic-type, comment, policy, upload,
   translation, route, presenter, Livewire, factory, seeder, and test code;
8. the current branch, commit history, staged diff, unstaged diff, and
   untracked files.

The repository is on `main` at `55a4880`, synchronized with `origin/main`, and
the worktree was clean when this plan was created. The complete serial
checkpoint is 1,384 tests and 50,006 assertions. Existing commits and
unrelated vendor material under `.agents/vendor/` must not be modified or
folded into this package.

## Current Implementation Analysis

- `CareJournal` is a private operational care domain. It owns routines, tasks,
  medication-adjacent data, encrypted measurements, temporary access grants,
  and private care media. Converting it into a public forum feature would
  weaken its privacy boundary and overload its purpose.
- `ForumTopicType::Journal` and its seeded topic-type definition already
  exist, and existing topics can be explicitly typed as journals.
- `ForumTopic` already provides the public discussion shell, category, group,
  author, locale, pet/taxon context, lifecycle, engagement, and moderation
  integration required by a topic journal.
- Generic `ForumTopic::structured_data` is suitable for a versioned journal
  descriptor but not for queryable entry history, collaborators,
  measurements, or media ownership.
- `ForumComment` already belongs to a topic and its database `answer_id` is
  nullable, but the current create path only supports comments attached to an
  answer. A nullable journal-entry relation can reuse the established comment
  entity without inventing a parallel discussion system.
- Existing forum topic media is stored as a JSON projection on the public
  disk. Journal-entry images require a normalized private-by-default file
  boundary with generated names, content validation, request-time policy
  checks, alt text, and deterministic cleanup.
- Topic visibility currently excludes only `private` records from the public
  directory. Member, expert, group, and link-only semantics require explicit
  policy and directory behavior before they can safely protect journals.
- No durable forum-journal entry, collaborator, measurement, archive, export,
  chart, or class-based Livewire workflow exists.
- An existing journal topic is safe to recognize by its explicit
  `ForumTopicType::Journal` value. Titles and prose are not safe migration
  classifiers.

## Desired Result

Create one forum-journal domain in which:

- every journal is backed by exactly one existing `ForumTopic`;
- all required journal types are typed and translated;
- old explicitly typed journal topics can be backfilled idempotently without
  changing their IDs, URLs, replies, reactions, subscriptions, reports, media,
  or moderation history;
- owners and selected collaborators can add dated entries, milestones,
  setbacks, validated numeric measurements, images, and comments;
- public, member, group, expert, link, and private visibility is enforced
  before rows, counts, files, exports, and Livewire payloads are returned;
- bounded presentation data can render accessible progress tables and native
  progress indicators without a client chart framework;
- owners can archive journals without deleting content;
- owners and editors can export an authorized, deterministic JSON record;
- no streak, missed-day, punishment, rank, or shame mechanic exists.

## Domain Boundary

`ForumTopic` remains the canonical publication and discussion shell.
`ForumJournal` owns journal-specific lifecycle and relationships.
`CareJournal` remains unchanged as private operational care data.

The package will use:

- one `ForumJournal` per journal topic;
- `ForumJournalEntry` for dated narrative records;
- `ForumJournalEntryVersion` for immutable pre-edit snapshots;
- `ForumJournalMeasurement` for queryable numeric values;
- `ForumJournalCollaborator` for explicit viewer/editor access;
- `ForumJournalMedia` for private-disk images;
- the existing `ForumComment` table with an additive nullable
  `forum_journal_entry_id` relation for entry comments;
- existing `AuditLog` for creation, collaborator, edit, archive, media, and
  export evidence instead of a second generic audit framework.

## Expected Files

Expected additions:

- journal, entry, collaborator, and media enums under `app/Enums`;
- typed journal and entry data under `app/Data`;
- `ForumJournal`, `ForumJournalEntry`, `ForumJournalMeasurement`,
  `ForumJournalCollaborator`, and `ForumJournalMedia` models;
- `ForumJournalPolicy` and `ForumJournalMediaPolicy`;
- create, backfill, entry create/update, collaborator grant/revoke, comment,
  media store/download, archive, and export Actions;
- a typed `ForumJournalMetricRegistry`;
- class-based `ForumJournalDirectory` and `ForumJournalTimeline` Livewire
  components with separate form objects and Blade templates;
- thin journal directory, export, and media controllers;
- an additive migration named after the next available timestamp;
- factories for every new Eloquent model;
- a production-safe backfill/definition seeder and an environment-gated demo
  seeder;
- EN, LT, and RU `forum_journals.php` catalogues;
- `tests/Feature/Forum/ForumJournalWorkflowTest.php`;
- `docs/journals.md`.

Expected modifications:

- `ForumTopic`, `ForumComment`, and their policies/factories;
- forum presenter, topic view, routes, topic-type definition seeder, database
  seeder, factory/seeder matrix, localization checks, schema checks, and
  architecture checks;
- canonical architecture, domain/data, authorization, privacy, security,
  files, frontend, Livewire, accessibility, localization, seeding, testing,
  performance, operations, progress, audits, changelog, evidence, and
  generated traceability documents.

The exact file set may change when implementation evidence proves a narrower
existing abstraction is sufficient. Any additional production file must be
recorded here before the package is marked verified.

## Schema Changes

The additive migration will create:

### `forum_journals`

- internal ID and unique stable key;
- unique `forum_topic_id`;
- nullable owner user relation plus preserved owner actor key;
- extensible journal type string;
- active or archived status;
- start date and timezone;
- optimistic `lock_version`;
- archive actor, timestamp, and reason code;
- migration-review metadata for legacy topics;
- timestamps;
- indexes for owner/status/update, type/status/update, and status/update.

Privacy remains canonical on `forum_topics.visibility`; it will not be
duplicated into a second field that can drift.

### `forum_journal_collaborators`

- journal, user, role, state, grantor, grant/revoke timestamps, and timestamps;
- unique journal/user relation;
- user/state and journal/state/role indexes.

The owner cannot be added as a collaborator. Revocation preserves the row and
audit evidence.

### `forum_journal_entries`

- journal and author relations;
- unique stable key and unique idempotency key;
- entry, milestone, or setback kind;
- occurrence timestamp and source timezone;
- title, plain-text body, optimistic lock version, and timestamps;
- journal/date and author/date indexes.

### `forum_journal_measurements`

- entry relation;
- stable metric key, decimal numeric value, unit, display position, and
  timestamps;
- unique entry/metric relation;
- metric and entry indexes.

Metric definitions and allowed ranges live in one typed registry. User input
cannot invent arbitrary units or executable presentation data.

### `forum_journal_entry_versions`

- entry and editor relations;
- monotonically increasing version number;
- immutable JSON snapshot containing the prior entry and measurement values;
- bounded edit-reason code and creation timestamp;
- unique entry/version relation and entry/date index.

An edit writes the prior snapshot before changing the entry. Version records
are not mutable through an application Action.

### `forum_journal_media`

- entry and uploader relations;
- stable key and idempotency key;
- private disk, generated path, encrypted original name, actual MIME type,
  byte size, checksum, alt text, optional caption, status, and timestamps;
- unique disk/path and entry/status/date indexes.

### `forum_comments`

- nullable `forum_journal_entry_id` foreign key;
- nullable unique idempotency key for the new journal-comment path;
- entry/status/date index.

Existing answer comments retain their current columns and behavior.

## Data Migration Strategy

1. Create all new structures without changing existing topic rows.
2. Select only topics whose typed `type` is exactly `journal`.
3. For each topic without a journal row, create an idempotent journal:
   - preserve topic ID, slug, author, category, group, language, visibility,
     timestamps, content, comments, reports, subscriptions, and media;
   - use a valid `structured_data.journal_type` when present;
   - otherwise use a neutral `general` journal type and set
     `requires_type_review` metadata;
   - never infer a sensitive type from title or body keywords.
4. Synchronize the topic-type definition schema for new journal creation.
5. Record backfill counts and review-required topics.
6. Do not convert `CareJournal` records or copy private care entries.

The backfill must be restartable and run through a production-safe explicit
seeder/Action. A repeated run must not create duplicate journal rows or alter
already curated journal types.

## Rollback Strategy

Before real journal activity, rollback may remove the new tables and additive
comment columns; forum topics and all pre-existing content remain.

After journal activity exists:

1. disable journal mutations;
2. export new journal, entry, version, measurement, collaborator, media, and comment
   relations;
3. retain private files;
4. restore application compatibility through a reviewed forward fix;
5. never drop tables or files merely to recover from a deployment defect.

The migration `down()` may remove only structures introduced by this
migration. It must remove comment foreign keys before journal-entry tables.

## Legacy Compatibility

- Ordinary topics, answers, answer comments, reactions, reports,
  subscriptions, bookmarks, group links, and moderation behavior remain
  unchanged.
- Existing explicit journal topics remain at the same URL and gain a neutral
  normalized journal record.
- The existing forum editor may continue creating generic topics. The new
  journal UI is the authoritative path for structured journal creation.
- Existing `CareJournal` routes, policies, encrypted data, grants, reports,
  exports, and media remain independent and unchanged.
- Journal topics retain standard forum report and blocking behavior.

## Authorization Changes

Server-side policies will cover:

- creating a journal topic;
- viewing every visibility mode;
- viewing private entry rows, counts, chart data, comments, images, and
  exports;
- adding and editing entries;
- adding milestones and setbacks;
- uploading and downloading images;
- creating entry comments;
- granting and revoking collaborators;
- archiving;
- exporting.

Owner, editor, and viewer rights:

- owner: all journal operations;
- active editor: view, create/edit entries, upload images, comment, export;
- active viewer: view, comment, export;
- revoked collaborator: no collaborator-derived access.

Administrators retain the repository's existing policy override. Group
journals additionally require group member-content access. Direct Livewire
actions and controller routes must repeat authorization; hidden controls are
not a security boundary.

## Validation Changes

Journal creation:

- title 5-180 characters;
- body 10-10,000 characters;
- an existing active category stable key;
- journal type from the typed registry;
- start date in a reasonable range;
- IANA timezone from the authenticated user's configured context;
- existing supported locale;
- visibility from the allowed journal subset;
- optional pet context only from an authorized existing source.

Entries:

- title 2-180 characters;
- body 2-10,000 characters;
- kind from the enum;
- occurrence time no more than a small clock-skew allowance in the future;
- explicit timezone;
- idempotency key;
- expected version for updates.

Measurements:

- metric key allowed for the selected journal type;
- numeric decimal value;
- registry-defined range and canonical unit;
- no duplicate metric key in one entry;
- bounded list size.

Images:

- actual image content only;
- JPEG, PNG, or WebP;
- maximum 5 MiB;
- bounded dimensions;
- required alt text;
- optional bounded caption;
- generated filename and private storage.

Comments and collaborators:

- bounded plain text;
- active user and visible entry;
- idempotency;
- selected user exists and is active;
- role from the enum;
- no owner self-collaboration.

## Translation Changes

Add stable EN, LT, and RU keys for:

- journal types, status, entry kinds, collaborator roles and states;
- metric labels and units;
- create and timeline forms;
- validation and policy-safe feedback;
- privacy descriptions;
- entry, milestone, setback, chart, comment, image, export, and archive UI;
- loading, empty, success, error, and offline states;
- alt-text guidance and accessible names;
- neutral no-streak and no-shame guidance.

All catalogues must have key and placeholder parity. Scientific names and
user-authored content are not translated.

## Interface Changes

`ForumJournalDirectory` will provide:

- owner/collaborator journal list with bounded pagination;
- a journal creation form;
- explicit privacy and type descriptions;
- loading, offline, validation, success, and empty states.

`ForumJournalTimeline` will provide:

- chronological entries with dates and kind labels;
- entry creation and optimistic editing for authorized users;
- structured metric inputs selected from the registry;
- image upload with alt text and caption;
- entry comments;
- collaborator management for owners;
- accessible progress tables and native progress elements where numeric data
  makes comparison meaningful;
- export and archive controls;
- no streak, missed-day, rank, or punitive UI.

The topic view will embed the timeline using only a locked scalar journal ID.
Blade remains passive and receives prepared presentation arrays.

## Accessibility Changes

- one logical page heading on the directory and existing topic page;
- correctly nested section headings;
- labels and descriptions for every form field;
- error summaries and field-linked errors;
- status feedback through a polite live region;
- semantic time elements for entry dates;
- native tables and progress elements with textual values;
- required alt text for content images;
- icon-only controls with accessible names;
- keyboard-operable edit, collaborator, archive, export, and upload paths;
- no drag-only interaction;
- 44px minimum workflow targets;
- no flashing or decorative motion;
- neutral language that does not shame missed updates.

## Cache Changes

No new cache is planned initially. Journal lists and timelines must first pass
bounded-query tests using selective columns, eager loading, pagination, and
limited chart history.

If measurement metadata is cached later, its owner, key, locale scope, TTL,
and invalidation must be documented. Journal privacy, collaborators, files,
and counts must never be served from an unscoped shared cache.

Mutations update the parent topic's `last_activity_at` transactionally so
existing forum ordering remains coherent.

## Security Risks And Controls

- Private journal leakage: policy authorization precedes entry, comment,
  count, chart, media, and export queries.
- Visibility drift: privacy has one source on the topic, not duplicated
  journal state.
- Insecure direct object reference: entry, comment, media, and collaborator
  subjects are reloaded under their parent journal.
- Upload spoofing: content MIME and dimensions are validated; generated names
  are used; files remain on the local private disk.
- Path disclosure: media path and encrypted original name are hidden from
  serialization and never sent to Livewire.
- XSS: journal prose and comments remain escaped plain text.
- Duplicate/replayed mutation: database unique keys plus transactional
  idempotency.
- Concurrent edit: optimistic entry and journal versions.
- Archived mutation: every mutation rejects archived journals.
- Group leakage: group policy remains an additional requirement.
- Public directory leakage: only public, non-group topics are discoverable;
  member, expert, link, group, and private journals require direct policy
  evaluation.

## Privacy Risks And Controls

- A forum journal never reads or copies private `CareEntry`, medication,
  measurement, access-grant, or care-media data.
- Selected collaborators are explicit, revocable, and audited.
- Public journal context uses only fields already approved for the topic.
- Private files are never exposed by a storage URL.
- Exports require current policy authorization and include only the selected
  journal's normalized records.
- Revocation affects future access without deleting historical attribution.
- Archival preserves the record and removes it from active discovery.

## Abuse Risks And Controls

- Comment creation remains rate limited and policy gated.
- Media upload is bounded and rate limited.
- Collaborators cannot grant collaborators or archive the journal.
- Editor access does not transfer ownership.
- Setbacks are neutral entry kinds, not negative score events.
- No missed-day calculations, reminders with guilt language, streak loss,
  leaderboards, or reputation events are introduced.
- Journal activity does not create professional verification, medical
  authority, trust level, or marketplace reputation.

## Tests To Create

`ForumJournalWorkflowTest` will cover at least:

1. every supported journal type and translated label;
2. topic/journal one-to-one creation and typed structured data;
3. safe idempotent legacy backfill and review-required fallback;
4. preservation of existing topic IDs, answers, comments, engagements,
   reports, group links, and URLs;
5. public visibility;
6. member visibility for guests, active users, and inactive users;
7. private owner, editor, viewer, stranger, and revoked collaborator access;
8. group journal access;
9. link-only non-discovery;
10. expert-only access when supported by existing verified-profile evidence;
11. direct route and direct Livewire action authorization;
12. entry date, kind, body, and idempotency validation;
13. milestone and setback creation;
14. optimistic update conflict and immutable pre-edit version;
15. every type-specific metric registry;
16. invalid metric, range, unit, duplicate, and oversized metric lists;
17. chart projection, ordering, bounds, and textual fallback;
18. valid private image upload and generated filename;
19. spoofed MIME, invalid dimensions, oversized image, missing alt text;
20. owner/editor/viewer/stranger media download boundaries;
21. entry comment creation and parent consistency;
22. collaborator grant, role change, revoke, and owner protection;
23. export content and authorization;
24. archive preservation and post-archive mutation rejection;
25. no reputation, streak, or punitive side effect;
26. factory creation and meaningful states;
27. production-safe backfill and environment-gated demo seeding;
28. repeated seed stability;
29. EN/LT/RU key and placeholder parity;
30. bounded query count as entries, comments, metrics, and media grow;
31. migration schema, foreign keys, unique constraints, and indexes;
32. passive Blade, no Volt, no unsafe environment calls, and route-name
    coverage.

Existing topic visibility, directory, group, comment, factory/seeder,
localization, architecture, schema, and full-suite tests will be rerun.

## Documentation To Update

- this work-package plan
- `docs/journals.md`
- `docs/architecture.md`
- `docs/domain-model.md`
- `docs/data-model.md`
- `docs/authorization.md`
- `docs/privacy.md`
- `docs/security.md`
- `docs/files.md`
- `docs/frontend.md`
- `docs/livewire.md`
- `docs/accessibility.md`
- `docs/localization.md`
- `docs/seeding.md`
- `docs/testing.md`
- `docs/performance.md`
- `docs/operations.md`
- `docs/index.md`
- `CHANGELOG.md`
- current progress, gap audit, final completeness audit, requirement evidence,
  compliance matrix, and generated summaries

## Acceptance Criteria

1. All 27 scoped IDs have file-level, test-level, and documentation evidence.
2. Every journal has exactly one preserved forum topic shell.
3. `CareJournal` privacy and behavior remain unchanged.
4. All eleven named journal types plus a neutral legacy fallback are typed,
   seeded, translated, and tested.
5. Entries, dates, measurements, images, milestones, setbacks, selected
   collaborators, comments, charts, export, and archive are durable.
6. Privacy is enforced before queries and serialization for every interface,
   controller, file, and export path.
7. Existing explicit journal topics backfill idempotently without title/body
   inference or content loss.
8. Entry mutation is idempotent and optimistic-edit conflicts are rejected.
9. Journal images are content-validated, private, safely named, and
   authorized at request time.
10. Public discovery cannot reveal member, expert, link, group, or private
    journals.
11. No streak, missed-day, punishment, rank, shame, or authority side effect
    exists.
12. EN, LT, and RU render without raw keys or placeholder mismatch.
13. Targeted tests, regression tests, architecture/schema/factory/localization
    checks, Pint, Larastan, fresh/repeat seed, full serial suite, production
    build, cache compilation, and browser checks pass.

## Verification Procedure

1. Inspect the diff after schema, Action, policy, and Livewire slices.
2. Run focused journal tests after each domain slice.
3. Run topic visibility, group, comment, architecture, schema,
   factory/seeder, and localization tests.
4. Run Pint on modified PHP files and Larastan at repository level 5.
5. Run a fresh isolated migration, seed, and repeat seed.
6. Run the full serial Pest suite.
7. Run the production Vite build after interface changes.
8. Compile config, route, and Blade caches.
9. Run desktop/mobile Playwright checks for privacy, collaborators, entries,
   metrics, image labels, progress semantics, archive, overflow, focus, touch
   targets, and console output.
10. Run source-prompt checksum and requirement-generator checks.
11. Update evidence, progress, audits, and this plan with exact results.
12. Inspect the complete diff for unrelated changes, secrets, generated local
    artifacts, placeholders, debug calls, hardcoded text, `@php`, unsafe Blade
    access, public file paths, and destructive data operations.

## Completion Evidence

All 27 scoped IDs are implemented and verified.

- `database/migrations/2026_07_31_001220_create_forum_journal_tables.php`
  adds normalized journals, collaborators, entries, measurements, immutable
  entry versions, private media, and journal comments without replacing
  existing topics or private operational care journals. It includes
  restrictive foreign keys, idempotency and optimistic-version constraints,
  and indexes whose leading columns match production access paths.
- Typed enums, data objects, factories, models, policies,
  `ForumJournalMetricRegistry`, and eleven Actions implement the authorized,
  validated, transactional lifecycle. Entry timestamps are normalized from
  the user's declared timezone before the future-time boundary is evaluated.
- `ForumJournalDirectory` and `ForumJournalTimeline` are class-based,
  multi-file Livewire components. Lifecycle `boot()` injection supplies
  non-persisted formatter, taxonomy, and metric services. Five form objects
  keep public state bounded; stable journal identity is locked and every
  mutation reauthorizes the server-loaded subject.
- EN, LT, and RU catalogues cover types, states, fields, errors, progress
  alternatives, safety copy, loading/offline feedback, and actions without a
  second localization system.
- `ForumJournalBackfillSeeder` recognizes only explicitly typed legacy journal
  topics. Repeat execution preserves curated rows and IDs.
  `ForumJournalDemoSeeder` is environment-gated.
- `php artisan test tests/Feature/Forum/ForumJournalWorkflowTest.php` passed
  17 tests and 164 assertions.
- The journal/forum regression slice passed 1,025 tests and 4,334 assertions.
- The architecture/localization slice passed 38 tests and 44,600 assertions;
  the app-shell regression slice passed 61 tests and 19,471 assertions after
  removing nested `main` landmarks from six child views.
- `php artisan test` passed 1,437 tests and 51,568 assertions in serial mode.
- `php scripts/verify-fresh-database.php` passed 94 migrations and 152 tables;
  fresh and repeated seeds exited successfully with a stable user count of 5.
- Pint passed. Larastan/PHPStan level 5 reported zero errors. Composer strict
  validation and audit passed. NPM high-severity audit reported zero
  vulnerabilities. The Vite 8.2.0 production build passed.
- Config, route, and Blade caches compiled, and all three journal routes were
  present.
- A real Lithuanian private-journal flow was exercised through Playwright.
  Journal creation and a local-time milestone entry with three measurements
  persisted. At 375x812 and 1440x900 the page had one `main`, one `h1`, no
  horizontal overflow, no raw translation keys, no unnamed buttons, no
  visible unlabeled controls, and no console warnings or errors.
- The source checksum and deterministic 7,284-requirement generator checks
  passed. Requirement evidence and canonical generated matrices were
  regenerated from the evidence overlay.
