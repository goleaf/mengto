# Phase 8 Work Package: Group Content And Polls

Status: verified

Last updated: 2026-07-31

## Requirement Scope

This package plans, implements, and verifies exactly these 24 atomic
requirements:

- `forum.feature.3134`: group topics
- `forum.feature.3135`: group events
- `forum.feature.3136`: group guides
- `forum.feature.3137`: group polls
- `forum.feature.3138`: group files
- `forum.feature.3139`: group announcements
- `forum.feature.3143`: polls and community decisions
- `forum.feature.3144`: support the configured poll modes
- `forum.feature.3145`: single-choice polls
- `forum.feature.3146`: multiple-choice polls
- `forum.feature.3147`: ranked-choice polls
- `forum.feature.3148`: anonymous polls
- `forum.feature.3149`: visible-voter polls
- `forum.feature.3150`: public poll results
- `forum.feature.3151`: results visible after voting
- `forum.feature.3152`: results visible after poll closure
- `forum.feature.3153`: votes editable before closure
- `forum.feature.3154`: non-editable votes
- `forum.feature.3155`: poll eligibility restrictions
- `forum.feature.3156`: trusted-member polls
- `forum.feature.3157`: location-limited polls
- `forum.feature.3158`: group polls
- `forum.feature.3159`: closure derived from `closes_at` without cron
- `forum.feature.3160`: polls cannot prove medical, legal, or scientific truth

No requirement outside this list will be marked verified by this package.
Related event, guide, topic, file, trust, location, and moderation behavior is
reused where it already exists; broader requirements remain independently
tracked.

## Required Reading And Repository State

Before implementation, this package was grounded in:

1. `docs/requirements/forum-source-prompt.md`
2. `docs/requirements/forum-master-requirements.md`
3. `docs/traceability/forum-requirements-matrix.md`
4. `docs/plans/forum-current-progress.md`
5. `docs/plans/forum-phase8-group-core-work-package.md`
6. `docs/superpowers/specs/2026-07-29-groups-mvp-design.md`
7. `docs/superpowers/specs/2026-07-29-events-mvp-design.md`
8. current group, topic, knowledge, event-prototype, storage, policy,
   localization, factory, seeder, route, Livewire, Blade, and test code
9. the current branch, commit history, staged diff, unstaged diff, and
   untracked files

The repository is on `main`, four commits ahead of `origin/main`, and the
worktree was clean when this plan was created. Existing commits and unrelated
vendor material under `.agents/vendor/` must not be rewritten, removed, or
silently folded into this package.

## Current Implementation Analysis

- `ForumGroup`, memberships, invitations, policy checks, lifecycle actions,
  audit events, visibility-safe discovery, Livewire workspace, factories, and
  deterministic seeds are relational and verified.
- `ForumTopic` and `KnowledgeArticle` are durable Eloquent entities but cannot
  currently belong to a group.
- Group topics, events, guides, resources, announcements, and polls rendered by
  `GroupContentCatalog` are localized static presentation fixtures.
- `GroupState` stores one string poll choice in a per-user encrypted state
  record. It has no normalized options, voter eligibility, result visibility,
  immutable decision evidence, database uniqueness, ranked choices, multiple
  choices, close time, or concurrent-vote protection.
- Meetups use `EventCatalog`, `EventContentCatalog`, and `EventState`; they are
  an intentionally isolated session-backed product prototype rather than a
  relational event source of truth.
- Existing private document patterns use the configured `local` disk and
  authorize downloads at request time. Group files do not yet have an
  ownership model or download boundary.
- Existing trust data is stored separately from forum karma. Poll eligibility
  must consume that boundary rather than infer authority from reaction counts.

## Desired Result

Create one durable group-content surface in which authorized group members can:

- associate existing forum topics and knowledge guides with a group;
- create bounded group events without depending on the legacy event prototype;
- upload and download authorized private group files;
- publish versioned group announcements;
- create single-choice, multiple-choice, and ranked-choice polls;
- configure anonymous or visible voter identity;
- configure public, after-vote, or after-close results;
- configure editable or final votes;
- restrict voting to active group members, trusted members, or the group's
  declared location scope;
- vote exactly once per poll projection, with an authorized edit replacing the
  prior choices rather than creating another vote;
- derive closure from the configured timestamp at read and mutation time;
- see a localized notice that poll results express community preference and
  never establish medical, legal, or scientific truth.

All group content must inherit the group's membership boundary. Private or
unlisted content must not leak through direct routes, counts, downloads,
results, voter lists, or search-oriented projections.

## Files Expected To Be Created

- poll type, voter-visibility, result-visibility, eligibility, and lifecycle
  enums under `app/Enums`
- a small group-content role enum only if announcement or pin semantics require
  it after schema review
- typed poll input and vote data under `app/Data`
- `ForumPoll`, `ForumPollOption`, `ForumPollVote`, `ForumGroupAnnouncement`,
  `ForumGroupFile`, and `ForumGroupActivity` models
- corresponding factories
- `ForumPollPolicy`, `ForumGroupFilePolicy`, and policies for other directly
  routed group content where the group policy is insufficient
- dedicated Actions for topic/guide association, event creation, announcement
  publication, file upload/removal, poll creation, and vote casting
- a private file download controller
- an additive migration with constraints and indexes
- EN, LT, and RU poll/group-content translation catalogues
- a focused group-content and poll feature test
- a focused poll concurrency and result-visibility test
- `docs/polls.md`

The exact class list may shrink when one existing boundary can safely own the
behavior. New production files outside this plan require a plan update first.

## Files Expected To Be Modified

- `app/Models/ForumGroup.php`
- `app/Models/ForumTopic.php`
- `app/Models/KnowledgeArticle.php`
- `app/Models/User.php` when inverse relationships are useful
- `app/Policies/ForumGroupPolicy.php`
- existing topic and knowledge policies only where group visibility must be
  enforced by their direct routes
- `app/Livewire/Forum/GroupWorkspace.php`
- `resources/views/livewire/forum/group-workspace.blade.php`
- `app/Livewire/Forum/GroupManagement.php` and its view when content creation
  belongs on the management surface
- `routes/web.php`
- factories for topics and guides
- `DatabaseSeeder` and group demo seeding only when deterministic examples are
  needed
- EN, LT, and RU localization catalogues
- architecture, data-model, domain-model, authorization, security, privacy,
  Livewire, accessibility, seeding, testing, performance, operations, current
  progress, audits, traceability evidence, generated matrices, and changelog
  documentation

## Schema Changes

The additive migration is expected to:

- add nullable, indexed `forum_group_id` foreign keys to `forum_topics` and
  `knowledge_articles`;
- create `forum_group_activities` for durable group event identity, bounded
  time/location metadata, lifecycle state, creator, and optimistic version;
- create `forum_group_announcements` with author, localized user content,
  publication window, version, and archive state;
- create `forum_group_files` with uploader, private disk/path, safe generated
  name, original display name, detected MIME type, byte size, checksum,
  description, lifecycle state, and timestamps;
- create `forum_polls`, `forum_poll_options`, and `forum_poll_votes`;
- enforce unique option keys and positions per poll;
- enforce one vote projection per poll and user at database level;
- store ordered vote choices as a validated bounded JSON list so one schema
  supports single, multiple, and ranked modes without one column per option;
- index group content, active event time, announcement publication, file
  listing, poll closure, and vote aggregation query patterns;
- use restrictive or nulling foreign-key behavior that preserves audit and
  content history appropriately.

Translated names are never identifiers. User-facing labels remain content,
while stable keys, integer IDs, and enum values define relationships.

## Data Migration Strategy

1. Add nullable group relations and new tables without changing current URLs.
2. Keep all existing topics, replies, reactions, subscriptions, bookmarks,
   reports, attachments, guides, events, and session prototype state intact.
3. Seed deterministic group examples only in the existing local/demo boundary.
4. Do not infer group ownership for legacy topics or guides from titles.
5. Do not migrate static catalog poll totals into user votes because their
   actors and eligibility cannot be proven.
6. Render a clearly labelled legacy preview only where no relational content
   exists during the compatibility window.
7. Persist every new mutation in relational tables and stop writing new group
   poll state to `GroupState`.
8. Record unresolved legacy event associations for later event migration
   rather than guessing them.

No existing identifier or user content is rewritten by this package.

## Rollback Strategy

Before production use, a normal migration rollback removes only the additive
tables and nullable group columns. After group content exists:

1. disable group-content mutations;
2. export group activities, announcements, files, polls, options, and votes;
3. retain private file objects until their records are reconciled;
4. deploy the previous application release;
5. retain the additive tables for a forward correction instead of dropping
   user content.

Rollback never truncates existing topic, guide, group, report, or user tables.
File deletion is a separately authorized lifecycle action, not a migration
side effect.

## Legacy Compatibility Strategy

- Existing topic, guide, group, and meetup routes remain stable.
- A topic or guide without a group relation behaves exactly as before.
- Existing session-backed meetup records remain available through their
  current routes; new group activities use the relational group boundary.
- Existing group preview content remains presentation-only fallback until a
  group has durable content.
- Existing static poll votes are not represented as verified relational votes.
- `PerformGroupAction` may retain a compatibility branch for legacy preview
  routes but durable group workspaces use the new Actions.

## Authorization Changes

`ForumGroupPolicy` will gain explicit abilities for:

- create member content;
- manage group content;
- publish announcements;
- upload and remove files;
- create polls;
- view poll results and voter identities.

Directly routed models will re-authorize against the parent group:

- only active group members and authorized platform administrators can view
  member content;
- restricted members cannot create content;
- owner, administrator, moderator, and steward capabilities remain bounded by
  existing role methods;
- only the uploader or a content manager may remove a file;
- only eligible active users may vote;
- voter identities are visible only when configured and the viewer may access
  member content;
- anonymous poll configuration never removes the server-side voter row needed
  to prevent duplicate voting and abuse.

Every Livewire mutation and download route performs its own authorization.
Hidden controls and locked IDs are not authorization.

## Validation Changes

Validation will cover:

- group membership and lifecycle state;
- existing topic/guide ownership and group compatibility;
- event title, summary, format, timezone, date order, bounded location, and
  capacity;
- announcement title/body/publication dates;
- detected file MIME type, size, original-name length, description, and safe
  generated storage name;
- poll question, optional context, 2-20 unique options, stable option keys,
  mode, visibility, result policy, editability, eligibility, location
  requirement, and future close time;
- vote option existence, cardinality, uniqueness, rank ordering, eligibility,
  closure, poll version, vote version, and idempotency key.

Validation does not replace policies, unique constraints, transactions, or row
locks.

## Translation Changes

All platform-controlled labels, enum states, actions, validation errors,
loading/offline/empty states, file safety messages, poll visibility language,
result restrictions, closure text, and the medical/legal/scientific disclaimer
will have stable EN, LT, and RU keys.

User-authored topic, guide, event, announcement, file-description, poll, and
option text remains in its original locale. It is never silently translated.

## Interface Changes

The class-based group workspace will provide:

- bounded tabs or sections for topics, events, guides, polls, files, and
  announcements;
- clear empty states;
- a manager/member content form appropriate to the actor's role;
- an accessible poll ballot for each supported mode;
- precise loading and error feedback for the active mutation;
- result visibility explanations;
- visible closure and eligibility state;
- private file download commands;
- links to existing topic and guide pages;
- no direct service, model, or collection transformation in Blade.

Large relational projections are computed server-side with bounded selects and
eager loading. Public Livewire state contains only IDs, small input values, and
safe presentation arrays.

## Accessibility Changes

- native radio controls for single choice;
- native checkboxes for multiple choice;
- numbered selects or an equivalent keyboard-operable control for ranked
  choice, with no drag-only requirement;
- grouped fields with `fieldset` and `legend`;
- field-linked validation errors and a form error summary;
- textual closed, anonymous, eligibility, and result states;
- visible focus and at least 44-pixel primary controls;
- file controls with labels and accepted-format guidance;
- loading and success feedback announced through a status region;
- no color-only result indication;
- no horizontal overflow at 375, 768, 1024, and 1440 pixel widths.

## Cache Changes

Group content remains database-backed in this package. Only stable option
definitions and bounded aggregate results are candidates for later caching.
No permission-sensitive result, voter identity, private file list, or
announcement is placed in a shared public cache.

If an existing group directory cache is affected, it is invalidated after
content-count changes through the established group cache version. Poll voting
does not flush unrelated caches. Any result cache introduced later must include
poll version and visibility context.

## Security, Privacy, And Abuse Risks

- Group leakage: every query starts from an authorized parent group.
- File leakage: files use the private `local` disk, generated names, detected
  MIME validation, authorized downloads, and no public URL.
- Path traversal: storage paths are generated server-side and never accepted
  from Livewire input.
- Double voting: unique poll/user constraint plus a locked transaction.
- Replay: idempotency keys return the existing vote projection.
- Vote tampering: option IDs are reloaded under the poll inside the Action.
- Ballot stuffing: active membership, trusted/location rules, account status,
  and one-vote uniqueness are server-authoritative.
- Anonymous poll misunderstanding: public identity is hidden, but the
  anti-abuse record remains private and access controlled.
- Result inference: after-vote and after-close policies suppress aggregate
  totals until eligible.
- Medical/legal/scientific misuse: a permanent localized disclaimer is shown;
  poll results are never written to confirmation, credential, diagnosis, or
  reputation authority records.
- Private group search leakage: new content does not enter public topic, guide,
  event, file, poll, or announcement projections unless the group policy
  permits the viewer.
- Concurrent edits: mutable content and vote projections use optimistic
  versions and row locks where races affect invariants.

## Privacy Risks

- Original file names can contain personal data, so they are displayed only to
  authorized members and are not used as storage paths.
- Voter identity is never shown for anonymous polls.
- Visible-voter lists remain member-only and bounded.
- Location-limited polls store only the group's generalized location scope,
  not a participant's exact address.
- Removed users retain audit-safe vote records but lose content access.
- Group closure or archive removes mutation access without destroying history.

## Abuse Risks

- Poll creation is limited to authorized group contributors.
- Trusted eligibility uses the existing trust boundary, not karma, badges, or
  raw activity.
- Repeated edits may be rate limited and remain auditable through timestamps
  and vote version.
- Options are bounded and normalized to prevent duplicate or deceptive labels.
- Managers cannot inspect anonymous voter identities through the normal UI.
- Files and announcements remain reportable through the existing unified
  moderation boundary where applicable.

## Tests To Create Or Update

Focused feature and unit tests will cover:

- topic and guide association authorization and preservation;
- private group content non-disclosure;
- event, announcement, and file creation validation;
- private file storage and authorized/unauthorized download;
- all poll type, voter-visibility, result-visibility, and eligibility enums;
- single, multiple, and ranked vote validation;
- unique and concurrent vote behavior;
- editable and non-editable vote behavior;
- trusted-member and location-limited eligibility;
- group membership and wrong-group option rejection;
- closure derived from `closes_at` without scheduler state;
- result suppression before vote or closure;
- anonymous and visible voter rendering;
- medical/legal/scientific disclaimer rendering;
- factories and meaningful states;
- fresh migration and rollback shape;
- EN, LT, and RU key and placeholder parity;
- query budgets and N+1 prevention;
- architecture rules for Blade, Volt, environment calls, and debug code.

Browser checks will cover member and manager group workspaces at mobile and
desktop widths, keyboard ballot operation, file controls, result states,
private direct access, overflow, focus, and console output.

## Documentation To Update

- this work-package plan
- `docs/groups.md`
- `docs/polls.md`
- `docs/architecture.md`
- `docs/domain-model.md`
- `docs/data-model.md`
- `docs/authorization.md`
- `docs/security.md`
- `docs/privacy.md` when present
- `docs/frontend.md`
- `docs/livewire.md`
- `docs/accessibility.md`
- `docs/localization.md`
- `docs/testing.md`
- `docs/performance.md`
- `docs/operations.md`
- `docs/index.md`
- `CHANGELOG.md`
- current progress, gap audit, final completeness audit, requirement evidence,
  compliance matrix, and generated summaries

## Acceptance Criteria

1. All 24 scoped IDs have file-level, test-level, and documentation evidence.
2. Topics, guides, events, polls, files, and announcements are durable group
   content rather than session-only fixtures.
3. Private and unlisted group content cannot be inferred or accessed by an
   unauthorized user.
4. All three poll choice modes, both voter visibility modes, all three result
   visibility modes, both editability modes, and all scoped eligibility modes
   are typed, validated, and tested.
5. One poll/user projection is enforced by the database and transaction logic.
6. A poll rejects votes after `closes_at` without a cron or scheduler state
   transition.
7. Polls never create professional, medical, legal, scientific, confirmation,
   or authority evidence.
8. Group files are private, content-validated, safely named, and downloaded
   only after request-time authorization.
9. Existing topic, guide, group, meetup, reaction, report, subscription,
   attachment, and user records are preserved.
10. EN, LT, and RU render without raw keys or placeholder mismatch.
11. Targeted tests, architecture/schema/factory/localization checks, Pint,
    Larastan, fresh/repeat seed, production build, and browser checks pass.

## Verification Procedure

1. Inspect the diff after each schema, Action, policy, and Livewire slice.
2. Run focused group-content and poll tests after each domain slice.
3. Run architecture, schema, factory/seeder, and localization tests.
4. Run Pint on modified PHP files and Larastan at repository level 5.
5. Run a fresh isolated migration, seed, and repeat seed.
6. Run the full serial Pest suite.
7. Run the production Vite build after interface changes.
8. Inspect route, query-budget, and direct-authorization behavior.
9. Run desktop/mobile Playwright checks for private access, ballot modes,
   files, labels, focus, overflow, and console output.
10. Run source-prompt checksum and requirement-generator checks.
11. Update evidence, progress, audits, and this plan with exact results.
12. Inspect the complete diff for unrelated changes, secrets, generated local
    artifacts, placeholders, debug calls, hardcoded text, `@php`, and unsafe
    Blade access.

## Completion Evidence

All 24 scoped IDs are implemented and verified.

- `2026_07_31_001210_create_forum_group_content_and_poll_tables.php` adds the
  nullable legacy associations and six normalized tables without changing or
  deleting an existing topic, guide, group, event, attachment, reaction,
  subscription, report, or user row.
- Nine Actions own topic/guide association, activity, announcement, poll,
  ballot, and private-file operations. Policies authorize both Livewire
  mutations and the route-scoped file download.
- `GroupContentWorkspace` is a class-based Livewire component with four form
  objects, locked record keys, eager-loaded bounded projections, precise
  loading/offline states, and a separate passive Blade template.
- Single, multiple, and ranked ballots; anonymous and visible-voter modes;
  public, after-vote, and after-close results; editable and final ballots; and
  member, trusted-member, and location eligibility are represented by typed
  enums and exercised through the production Action path.
- Poll closure is derived from `closes_at`. Poll results are explicitly
  described as preference rather than medical, legal, scientific,
  professional, or confirmation authority.
- Files use generated names, content-derived MIME validation, hashes, the
  private local disk, request-time policy checks, and cleanup compensation.
- `ForumGroupDemoSeeder` creates a deterministic topic, guide, event,
  announcement, private file, and all three poll modes only in allowed
  environments. Repeated execution preserves stable counts.

Executed verification:

- `php artisan test --compact tests/Feature/Forum/GroupContentAndPollWorkflowTest.php`:
  18 tests, 72 assertions, passed.
- `php artisan test --compact tests/Feature/Forum/CommunityReviewAndNotesTest.php`:
  52 tests, 165 assertions, passed.
- Group-content plus architecture slice: 33 tests, 18,366 assertions, passed.
- `php artisan test --compact`: 1,384 tests, 50,006 assertions, passed in
  serial mode.
- `php scripts/verify-fresh-database.php`: 93 migrations and 146 tables;
  fresh seed and repeated seed passed with user count stable at 5.
- `vendor/bin/phpstan analyse --memory-limit=1G --no-progress`: zero errors.
- `vendor/bin/pint --dirty`, `composer validate --strict`, `composer audit`,
  `npm audit --audit-level=high`, Vite 8.2.0 production build, config cache,
  route cache, and view cache: passed.
- Source preservation and requirement generation checks passed for 7,284
  atomic requirements and SHA-256
  `6f8a7f987c336a2247755cae1c2fd66dea66d83cfbf038b5fe31aa848097d773`.
- Playwright at 375x812 and 1440x900 persisted a real single-choice vote and
  observed one H1, no horizontal overflow, no unlabeled controls, no package
  control below 44px, and no current-navigation console errors.

Coverage percentage remains unavailable because the PHP 8.5 runtime has no
PCOV or Xdebug. This environmental limitation does not replace the passing
focused, architecture, regression, and full-suite evidence above.
