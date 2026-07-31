# Phase 6 Community Review Work Package

Status: completed and verified on 2026-07-31.

## Requirement Scope

Community review panels:

- `forum.feature.2655` through `forum.feature.2684`;
- `forum.moderation.0029`;
- `forum.moderation.0030`;
- `forum.category.1387`;
- `forum.translation.0009`;
- `forum.plan.0072`.

Community notes:

- `forum.feature.2704` through `forum.feature.2728`;
- `forum.translation.0010`.

The package therefore contains 61 atomic requirements. No adjacent reaction,
ranking, mentorship, search, feed, or group requirement is claimed by this
pass.

## Current Implementation Analysis

- `ForumConfirmation` and `CastForumConfirmationVote` already provide
  consensus, quorum, diversity, expiry, and medical/legal risk boundaries.
  They do not model assigned review panels, reviewer replacement, deadlines,
  moderator overrides, appeals, or anonymized review evidence.
- Trust-level definitions already include `trusted-contributor`,
  `community-reviewer`, `category-steward`, `moderator`, and
  `senior-moderator`. Trust is audited and does not itself grant professional
  verification.
- Unified moderation already handles high-risk reports, private evidence,
  cases, recusal, actions, and appeals. Community panels must never replace
  that path.
- Forum topics and answers have stable internal IDs, author ownership, policies,
  and public visibility rules. There is no contextual community-note model,
  immutable note version, note review, or public presentation.
- The forum uses class-based multi-file Livewire, EN/LT/RU translations,
  SQLite-compatible migrations, Eloquent-only application queries, and
  bounded server-side projections.

## Desired Result

Provide optional, assigned, audited panels for low-risk classification and
quality review. Provide trusted-user contextual notes on topics and answers
with evidence, independent assessment, author response, controlled
publication, version history, revalidation, and moderator override. Public
readers see only published or revalidation-due notes; reviewers see only the
minimum subject context required for their assignment.

## Architecture

- `ForumReviewPanel` represents one low-risk review request.
- `ForumReviewAssignment` represents one independently assigned reviewer,
  conflict declaration, vote, reasoning, replacement, and completion state.
- `ForumReviewPanelEvent` is append-only audit history.
- `ForumCommunityNote` represents the current note projection and references a
  topic or answer through a controlled subject type and ID.
- `ForumCommunityNoteVersion` is append-only and preserves every authored,
  author-response, review, revision, publication, rejection, archive, and
  revalidation change.
- Notes may use a panel for independent community assessment. Moderator
  override is explicit, reasoned, audited, and appeal-eligible through the
  existing moderation appeal boundary when a moderation case exists.
- A dedicated eligibility service centralizes trust, account state,
  assignment-load balancing, conflict exclusion, and high-risk rejection.
- Public presentation uses prepared arrays from the Livewire component. Blade
  remains passive and escaped.

## Affected Files

Expected new files:

- additive migration for panel, assignment, event, note, and note-version
  tables;
- enums for panel type/state, assignment state/decision, note type/status, and
  workflow event type;
- five Eloquent models and factories;
- `CommunityReviewEligibility`;
- Actions for panel creation/assignment, vote submission, reviewer
  replacement, moderator decision, note proposal, author response, note
  revision, and note workflow transition;
- `ForumReviewPanelPolicy` and `ForumCommunityNotePolicy`;
- `CommunityNoteForm`;
- class-based `CommunityNotesPanel` and separate Blade view;
- focused feature, policy, concurrency, factory, and migration tests;
- EN/LT/RU translation files and `docs/community-review.md`.

Expected modifications:

- topic/answer/user relationships;
- forum topic presentation to mount the community-note component;
- administration navigation or bounded queue projection where useful;
- factory/seeder coverage tests;
- architecture, authorization, data-model, domain-model, Livewire, security,
  testing, operations, progress, changelog, and traceability documents.

## Schema Changes

The migration is expand-only. It adds:

- panel subject, type, risk class, requester, state, required reviewer count,
  deadline, decision, moderator override, appeal linkage, and timestamps;
- unique active-panel and state/deadline indexes;
- assignment user, state, decision, reasoning, conflict declaration,
  anonymized display key, replacement lineage, assigned/submitted timestamps,
  and a database unique one-reviewer rule;
- append-only panel events with actor, event, from/to state, reason,
  translation key, metadata, and idempotency key;
- note subject, proposer, type, status, current body/evidence, author response,
  jurisdiction/species context, panel link, version, publication,
  revalidation, archive, moderator decision, and optimistic lock;
- append-only note versions containing the complete safe historical snapshot.

All nullable foreign keys receive leading indexes. No legacy table or content
is deleted.

## Data Migration And Compatibility

No legacy community-note data exists. Existing topics, answers, votes,
confirmations, reports, and moderation cases remain unchanged. The feature is
opt-in and begins empty. The migration can roll back by dropping only newly
introduced tables after foreign-key-safe ordering.

## Authorization And Validation

- Only active users with a current trusted-contributor-or-higher community
  trust assignment, or administrators, may propose notes.
- Subject visibility is authorized before note creation or viewing.
- A proposer, subject author, blocked/suspended user, conflicted reviewer, and
  reviewer without an eligible trust level cannot independently review.
- Only an assigned reviewer can submit one decision before the deadline.
- Only administrators can override a panel, replace a reviewer manually,
  publish a safety note without quorum, reject, archive, or revise an approved
  note after publication.
- Subject authors may respond but cannot remove or suppress an approved note.
- Panel types and note types use server-side closed enums; body, evidence,
  reasoning, jurisdiction, species context, dates, and identifiers are
  validated and bounded.
- High-risk categories are rejected from panel creation and directed to
  unified moderation.

## Translation And Interface

Every platform-controlled label, status, type, action, validation message,
empty state, safety notice, and accessibility label is added to EN/LT/RU.
The Livewire component uses locked subject identity, minimal scalar form state,
precise loading targets, offline/dirty states, field errors, semantic
headings, minimum 44px actions, and no drag-only interaction.

## Cache, Security, Privacy, And Abuse

- No permission-sensitive panel or note query is globally cached.
- Published-note projections may later be cached only with subject, locale,
  visibility, and version scope; this package uses bounded direct queries.
- Reviewers receive public subject context and anonymized evidence summaries,
  never unrelated private report evidence.
- Append-only events and versions prevent secret rewrites.
- Idempotency keys and database uniqueness prevent duplicate assignment,
  voting, version, and moderator-action races.
- Rate limits are enforced per proposer/subject and per reviewer/panel.
- Harassment controls require eligible proposers, content-focused guidance,
  evidence, independent review, per-subject rate limits, an open-note cap, and
  auditable moderator rejection.

## Tests

Create tests for:

- schema, constraints, leading indexes, casts, relationships, and rollback;
- every supported low-risk panel type and every prohibited high-risk decision;
- balanced eligible reviewer selection, exclusions, deadline, replacement, and
  independent one-vote rules;
- limited/anonymized reviewer projection;
- moderator override, appeal linkage, and immutable event history;
- all note types and workflow states;
- topic and answer notes, evidence, author response, independent assessment,
  publication, revision, rejection, archive, and revalidation;
- author inability to remove approved safety notes;
- append-only versions and optimistic conflict handling;
- direct Livewire authorization, locked identity, validation, loading/offline
  markup, public visibility, and EN/LT/RU parity;
- concurrent duplicate assignment/vote/version prevention;
- factory defaults/states and fresh/repeat seeding compatibility;
- no query from Blade, no Volt, no `@php`, and no unescaped note HTML.

## Acceptance Criteria

- All 61 scoped requirements have implemented and passing evidence.
- Panels can decide only the seven enumerated low-risk task types.
- All eleven prohibited high-risk areas fail closed into normal moderation.
- Reviewer selection is balanced, eligible, conflict-aware, and independently
  auditable.
- Every reviewer decision has reasoning, deadline enforcement, and immutable
  history.
- Notes support all eleven contextual purposes and the complete required
  workflow.
- Published safety notes cannot be removed by the subject author.
- Moderator changes create new versions/events and never rewrite history.
- Public and private projections are policy-safe and query-bounded.
- EN/LT/RU, factories, migrations, full PHP tests, Pint, Larastan, Vite, fresh
  seed, and responsive browser checks pass before evidence is verified.

## Verification Procedure

1. Run focused panel/note/schema/policy/Livewire tests.
2. Run factory, localization, architecture, and query-budget tests.
3. Run Pint and Larastan.
4. Run the serial full Pest suite.
5. Run the isolated fresh migration and repeat-seed verifier.
6. Run the production Vite build.
7. Inspect public and reviewer interfaces at 375x812 and 1440x900, including
   keyboard focus, overflow, raw keys, unnamed controls, and console output.
8. Regenerate requirement and seeding matrices.
9. Record exact evidence for each scoped requirement.

## Rollback And Recovery

Rollback drops only this package's new tables. A failed migration leaves all
legacy forum data untouched. Runtime actions use short transactions and
bounded retries. Duplicate execution is recovered through unique constraints
and idempotency keys. An interrupted panel remains pending until deadline and
may receive an audited replacement reviewer; no cron process is required to
enforce expiration because deadline validity is calculated at action time.

## Completion Evidence

- Additive migration: `2026_07_31_001000_create_forum_community_review_tables.php`.
- Focused panel/note suite: 52 tests and 165 assertions passed.
- Architecture/localization/schema/factory slice: 808 tests and 42,279
  assertions passed.
- Full serial Pest suite: 1,172 tests and 44,853 assertions passed.
- Targeted Larastan: zero errors.
- Fresh database verifier: 90 migrations, 129 tables, fresh seed and repeated
  seed passed with stable user count 5.
- Vite 8.2.0 production build passed.
- Playwright at 375x812 and 1440x900 found one H1, no horizontal overflow,
  raw translation keys, unnamed buttons, controls below 44px, or current-page
  console warnings/errors; direct Livewire proposal also completed.
- All 61 scoped requirement IDs are `verified` in
  `docs/traceability/forum-requirement-evidence.json`.
