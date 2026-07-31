# Forum Phase 8 Content Lifecycle Work Package

Date: 2026-07-31

Status: verified

## Mandatory Reading

This package was planned after reading:

1. `docs/requirements/forum-source-prompt.md`
2. `docs/requirements/forum-master-requirements.md`
3. `docs/requirements/forum-requirements.json`
4. `docs/traceability/forum-requirements-matrix.md`
5. `docs/plans/forum-current-progress.md`
6. `docs/decisions/forum-architecture-decisions.md`
7. the topic, answer, category, policy, route, presenter, migration, factory,
   seeder, translation, and test implementation at `256b6b7`

## Requirement Scope

This package contains exactly 33 active atomic requirements:

| Requirement ID | Source requirement |
| --- | --- |
| `forum.feature.3227` | content lifecycle |
| `forum.feature.3228` | topic lifecycle states may include |
| `forum.feature.3229` | draft |
| `forum.feature.3230` | published |
| `forum.moderation.0328` | pending moderation |
| `forum.feature.3231` | needs clarification |
| `forum.feature.3232` | open |
| `forum.feature.3233` | answered |
| `forum.feature.3234` | partially solved |
| `forum.feature.3235` | solved |
| `forum.feature.3236` | disputed |
| `forum.feature.3237` | outdated |
| `forum.feature.3238` | locked |
| `forum.feature.3239` | archived |
| `forum.feature.3240` | merged |
| `forum.feature.3241` | redirected |
| `forum.feature.3242` | removed |
| `forum.feature.3243` | restored |
| `forum.feature.3244` | lifecycle support |
| `forum.feature.3245` | stale-content warning |
| `forum.feature.3246` | update request |
| `forum.feature.3247` | author update |
| `forum.feature.3248` | community update proposal |
| `forum.feature.3249` | reopening |
| `forum.feature.3250` | controlled bumping |
| `forum.feature.3251` | necropost warning |
| `forum.feature.3252` | automatic read-time state calculation |
| `forum.feature.3253` | manual archive |
| `forum.category.1400` | category archive rules |
| `forum.feature.3254` | retention |
| `forum.feature.3255` | legal hold where required |
| `forum.feature.3256` | restore |
| `forum.feature.3257` | do not delete user content merely because it is old |

No other requirement is marked complete through this package.

## Current Implementation Analysis

- `ForumTopicStatus` has draft, review, published, needs-clarification,
  answered, resolved, partially-resolved, unanswered, closed, locked, merged,
  and archived values. It lacks the complete canonical vocabulary and a
  transition contract.
- `forum_topics` already stores `lock_version`, `archived_at`, and
  `merged_into_topic_id`; it has no immutable lifecycle history, update
  request, legal hold, retention, staleness, bump, removal, or restoration
  fields.
- `DeleteTopic` physically deletes the topic and public media. That conflicts
  with retention, legal hold, restoration, and old-content preservation.
- `PerformForumAction`, `CreateAnswer`, and `AcceptForumAnswer` mutate status
  directly without a shared state machine or audit history.
- Category metadata can carry arbitrary JSON, but no typed category lifecycle
  rule exists.
- Public topic rendering has no stale or necropost warning and no lifecycle
  management surface.
- Existing topics, answers, comments, acceptances, engagements, subscriptions,
  reports, attachments, group relations, taxonomy relations, guide links, and
  stable slugs are production-relevant and must remain intact.

## Desired Result

- One canonical topic state enum covers every required state while retaining
  readable legacy values during the compatibility period.
- One domain service validates transitions, uses optimistic and row locking,
  synchronizes lifecycle timestamps and lock state, and appends immutable
  events.
- Removal is a reversible visibility transition. It never deletes topic text,
  replies, reactions, subscriptions, reports, or media.
- Category-specific staleness, necropost, archive, retention, reopen, and bump
  rules are normalized and production-safe.
- Stale and necropost state is calculated at read time from timestamps and
  rules. A GET request never silently mutates the database.
- Active legal holds prevent removal, archive, merge, redirect, and destructive
  maintenance while preserving private reasons for administrators.
- Members can request an update or propose contextual replacement text.
  Authors or administrators can review those requests; accepted proposals do
  not silently rewrite authorship.
- Authors can record a material update, reopen, bump within configured limits,
  archive, remove, and restore where policy and state allow.
- Administrators can moderate state, apply or release legal holds, and create
  audited merge or redirect targets without deleting the source record.
- A class-based Livewire component with a separate passive Blade view presents
  warnings, history, and authorized actions with minimal browser state.

## Affected Files

Expected new files:

- `app/Enums/ForumTopicLifecycleEventType.php`
- `app/Enums/ForumTopicUpdateRequestKind.php`
- `app/Enums/ForumTopicUpdateRequestStatus.php`
- `app/Models/ForumCategoryLifecycleRule.php`
- `app/Models/ForumTopicLifecycleEvent.php`
- `app/Models/ForumTopicLegalHold.php`
- `app/Models/ForumTopicUpdateRequest.php`
- `app/Services/ForumTopicLifecycle.php`
- `app/Services/ForumTopicLifecycleProjection.php`
- `app/Actions/RequestForumTopicUpdate.php`
- `app/Actions/ReviewForumTopicUpdateRequest.php`
- `app/Actions/BumpForumTopic.php`
- `app/Actions/SetForumTopicLegalHold.php`
- `app/Actions/RedirectForumTopic.php`
- `app/Livewire/Forms/ForumTopicLifecycleForm.php`
- `app/Livewire/Forum/ForumTopicLifecyclePanel.php`
- `resources/views/livewire/forum/forum-topic-lifecycle-panel.blade.php`
- one additive lifecycle migration
- four model factories
- production-safe lifecycle definition/backfill seeder
- environment-gated lifecycle demo seeder
- `lang/*/forum_topic_lifecycle.php`
- lifecycle feature and policy tests
- lifecycle architecture, operation, and recovery documentation

Expected modified files include:

- `ForumTopicStatus`, `ForumTopic`, `ForumCategory`, `ForumTopicPolicy`
- `DeleteTopic`, `UpdateTopic`, `CreateAnswer`, `AcceptForumAnswer`,
  `PerformForumAction`
- topic controller/presenter/show view
- forum system and demo seeder orchestration
- factory/seeding/translation/architecture/schema tests
- canonical architecture, data, authorization, security, privacy,
  accessibility, localization, testing, seeding, deployment, operations,
  progress, gap, audit, changelog, and traceability documents

## Schema Changes

The additive migration will:

1. add lifecycle timestamps and an indexed state-entered timestamp to
   `forum_topics`;
2. add an active legal-hold marker, retention deadline, and reversible removal
   and restoration timestamps;
3. create a one-to-one category lifecycle rule table;
4. create append-only topic lifecycle events;
5. create structured update requests and community proposals;
6. create legal hold records with private reason and release audit fields;
7. add foreign keys, unique constraints, and query-pattern indexes;
8. leave every existing topic and relationship untouched.

## Data Migration And Backfill

- The migration creates structure only.
- `ForumTopicLifecycleBackfillSeeder` processes bounded `chunkById` batches.
- Existing status values remain readable; no uncertain state is inferred from
  title, body, or activity alone.
- Missing `state_entered_at` uses the topic update, publish, or creation time.
- Every category receives one idempotent default rule without overwriting an
  administrator-modified rule.
- Existing archived rows retain their IDs and timestamps.
- A repeat run updates only missing system defaults and creates no duplicate
  rule or history rows.

## Rollback Strategy

- Before new lifecycle writes, the additive migration can be rolled back by
  dropping only new tables and columns.
- After production lifecycle events exist, use a forward fix; do not drop the
  audit, hold, request, or restoration history.
- No rollback path deletes forum content.
- Stable slugs and all existing foreign relations remain valid throughout.

## Legacy Compatibility

- Existing `review`, `resolved`, `partially-resolved`, `unanswered`, and
  `closed` values remain enum-readable.
- Public scopes recognize both legacy and canonical visible states.
- Existing resolve/reopen requests delegate to the new lifecycle service.
- Existing accepted-answer and answer-created paths keep their behavior while
  recording canonical solved/answered transitions and audit evidence.
- Existing delete route remains addressable but performs reversible removal.

## Authorization

- All lifecycle mutations require an active authenticated user.
- Topic owners can manage only their own eligible topics.
- Non-owners can request or propose updates only for topics they may view.
- Only administrators can force moderation states, apply/release legal holds,
  merge, redirect, or review another user's private lifecycle evidence.
- Direct Livewire action calls repeat authorization.
- Hidden controls, locked IDs, and client-supplied versions are not trusted.

## Validation

- Status, request kind, request state, and event type use enum validation.
- Reasons are trimmed, bounded, and required for sensitive operations.
- Community proposals require substantive text.
- Merge/redirect targets must exist, differ from the source, remain visible to
  the actor, and cannot create a target cycle.
- Optimistic lock versions must match.
- Bumps must satisfy category cooldown and state rules.
- Restore, archive, remove, and legal-hold transitions validate current state.

## Translation And Interface

- All new labels, states, warnings, controls, validation, history, empty,
  loading, offline, success, and error text use stable EN/LT/RU keys.
- Scientific and user-generated text is not translated or replaced.
- The topic page shows stale, necropost, legal/retention-safe public notices,
  lifecycle status, and bounded history.
- Private legal-hold reasons never enter public or ordinary-owner state.

## Accessibility

- The lifecycle panel uses semantic headings, field labels, linked errors,
  native buttons, `aria-live` status, visible focus, text plus icon status,
  keyboard-complete actions, and minimum 44px primary targets.
- No state relies on color, hover, drag-and-drop, or animation.
- Confirmations remain explicit for remove, archive, merge, redirect, and
  legal-hold actions.

## Cache And Performance

- No new cache is introduced until a measured stable read requires it.
- The public projection performs bounded indexed queries and never loads full
  history or requests.
- The panel limits history and update requests.
- Category rules use one eager-loaded relation or one indexed lookup.
- Query-count tests guard topic rendering against N+1 growth.

## Security, Privacy, And Abuse Risks

- Removal and restoration must not bypass report or legal evidence.
- Private hold reasons and moderator identities are policy protected.
- Bump cooldown, per-user update-request limits, and uniqueness prevent spam.
- Community proposals never overwrite the author's content automatically.
- Merge/redirect cycle checks prevent redirect loops.
- Public responses do not disclose removed topic body to unauthorized users.
- The implementation adds no raw HTML, remote fetch, queue dependency, cron
  dependency, or public private-file URL.

## Tests To Create

- all canonical and legacy enum values deserialize safely;
- every allowed and forbidden state transition;
- optimistic and concurrent transition protection;
- owner/non-owner/admin/blocked-user policy boundaries;
- delete route preserves topic, replies, reports, subscriptions, and media;
- removal hides public content and restoration re-exposes eligible content;
- legal hold blocks sensitive transitions and records apply/release history;
- category rules are unique, idempotent, and administrator-safe;
- staleness and necropost projection uses timestamps without database writes;
- update requests/proposals validate, rate-limit, review, and preserve author
  content;
- author updates close accepted requests and append history;
- bump cooldown and category prohibition;
- merge/redirect target and cycle validation plus old-URL redirect;
- Livewire direct action authorization, tampered ID/version, loading, dirty,
  offline, error, success, and empty states;
- EN/LT/RU key parity and rendered warnings;
- factory defaults and meaningful states;
- fresh and repeated seed;
- foreign-key index coverage and bounded topic query count;
- regression coverage for answer creation, accepted answer, resolve/reopen,
  archive, and existing topic routes.

## Documentation To Update

- architecture, domain model, data model, authorization, privacy, security
- frontend, Livewire, accessibility, localization
- testing, seeding, performance, deployment, operations, recovery
- gap analysis, final completeness audit, current progress, changelog
- requirement evidence, generated JSON catalogue, and matrix

## Acceptance Criteria

1. All 33 scoped IDs have exact implementation, test, and documentation
   evidence.
2. Existing topics and all related data remain present after remove, archive,
   merge, redirect, restore, backfill, and repeated seed.
3. No forbidden transition or direct Livewire mutation succeeds.
4. Staleness and necropost state is correct without a scheduler or write on
   read.
5. Legal holds prevent sensitive lifecycle operations and remain auditable.
6. Public views never disclose private hold/request evidence.
7. Fresh migration, repeated seed, focused tests, expanded regression, full
   suite, Pint, Larastan, audits, build, cache compilation, and mobile/desktop
   browser checks pass where supported.
8. Coverage is reported factually; absence of a driver is not a passing claim.

## Verification Procedure

1. `php scripts/generate-forum-requirements.php --check`
2. focused lifecycle, policy, schema, factory, seeder, localization, route,
   and architecture tests
3. expanded forum regression suite
4. full serial PHP suite in an isolated SQLite worktree
5. `vendor/bin/pint --test`
6. `PAO_DISABLE=1 vendor/bin/phpstan analyse --memory-limit=1G --no-progress`
7. `composer validate --strict` and `composer audit`
8. `npm audit --audit-level=high` and `npm run build`
9. `php scripts/verify-fresh-database.php`
10. config, route, and view cache compilation
11. real authenticated mobile and desktop Livewire lifecycle flow
12. final diff, source preservation, traceability, and no-placeholder audit

## Completion Evidence

- `tests/Feature/Forum/ForumTopicLifecycleTest.php` passed 13 tests and 133
  assertions for transitions, first publication time, preservation, legal
  holds, staleness, necroposting, requests, bumping, redirect, policy,
  Livewire, translations, factories, seed repeatability, indexes, rollback,
  and query bounds.
- The related forum/database regression slice passed 1,155 tests and 51,061
  assertions after the terminal-lock and leading-index fixes.
- `php artisan test --compact --colors=never` passed 1,613 tests and 55,337
  assertions in 94.727 seconds on the final isolated package state with
  Livewire 4.3.4.
- `vendor/bin/phpstan analyse --no-progress --memory-limit=2G` completed with
  zero errors. Full Pint completed after removing the one reported unused
  import.
- `php scripts/verify-fresh-database.php` and an isolated manual verification
  applied 97 migrations across 169 tables, completed the full seed, and
  completed a repeated `ForumSystemSeeder` run without duplicate definitions.
- `npm run build` passed with Vite 8.2.0. The generated assets included
  51.52 kB Tailwind CSS, 225.89 kB semantic CSS, and 12.31 kB JavaScript
  before gzip.
- A real HTTP/Livewire browser flow rendered a public stale topic and bounded
  lifecycle history, followed a durable 301 topic redirect, and produced no
  current-page console warning or error.
- At 375x812 the document and lifecycle panel remained within the 375px
  viewport with no horizontal overflow. The same page was also inspected at
  desktop width.
- An initial browser run exposed two runtime defects: cached configuration
  against an earlier schema and mismatched lifecycle history-limit keys. The
  cache was rebuilt, the keys were corrected to the canonical configuration,
  a regression assertion was added, and the clean rerun passed.
- All 33 scoped requirement IDs have implementation, test, documentation, and
  verification evidence in
  `docs/traceability/forum-requirement-evidence.json`.
- No coverage percentage is claimed because the environment has no Xdebug or
  PCOV coverage driver.
