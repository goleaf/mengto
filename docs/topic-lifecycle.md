# Forum Topic Lifecycle

This document is the canonical operational and implementation contract for
`forum.feature.3227` through `.3257`, `forum.moderation.0328`, and
`forum.category.1400`.

## State Model

New writes use the canonical states:

`draft`, `published`, `pending-moderation`, `needs-clarification`, `open`,
`answered`, `partially-solved`, `solved`, `disputed`, `outdated`, `locked`,
`archived`, `merged`, `redirected`, `removed`, and `restored`.

Legacy rows remain readable during the compatibility period:

| Legacy value | Canonical interpretation |
| --- | --- |
| `review` | `pending-moderation` |
| `resolved` | `solved` |
| `partially-resolved` | `partially-solved` |
| `unanswered` | `open` |
| `closed` | `locked` |

`ForumTopicLifecycle` is the only general state-transition boundary. It locks
the topic row, verifies the browser-visible optimistic version, validates the
transition graph, synchronizes timestamps and lock state, and appends an
immutable event in the same transaction. Answer publication, answer
acceptance, journal archive, owner removal, moderator state changes, merge,
redirect, restore, and author update delegate to this boundary.

## Preservation

Removal, archive, merge, and redirect are state changes, not physical deletion.
The topic ID, slug, body, answers, comments, reactions/votes, engagements,
subscriptions, bookmarks, reports, attachments, taxonomy/group/guide links,
and lifecycle evidence remain stored. A redirect or merge preserves the old
route and returns a permanent redirect only after the viewer is authorized to
see the destination.

Old age alone never deletes or mutates a topic. Staleness, necropost, archive
review, and retention review are projections calculated at read time from
topic timestamps and the category rule.

## Category Rules

Each category may own one `ForumCategoryLifecycleRule` defining:

- stale and necropost thresholds;
- archive-review and retention-review thresholds;
- bump cooldown;
- owner reopen, archive, remove, and bump permissions;
- whether automatic archive is enabled.

Automatic archive defaults to disabled. No scheduler or read request changes
topic state. The production-safe backfill creates missing system rules without
overwriting an administrator-owned rule.

## Update And Reopen

An authorized reader may submit either an update request or a community
proposal. Requests are bounded, rate-limited, idempotent, and visible only to
the requester, topic owner, or administrator. Accepting a proposal records the
decision; it does not silently replace the author's content.

A material author edit records an `author-updated` event and may reopen
outdated content through the validated transition graph. Solved topics remain
open to later corrections. Controlled bumping changes the activity timestamp
only after the category cooldown and policy checks pass.

## Legal Hold And Retention

An active legal hold prevents archive, removal, merge, redirect, and
destructive maintenance. Private hold and release reasons are encrypted and
hidden from serialization. Applying and releasing a hold is administrator
only and appends immutable audit evidence.

After lifecycle data exists, recovery is forward-only. Restore or redirect
through an authorized Action; never edit or delete lifecycle events, erase a
hold, or drop the lifecycle tables to repair a production record.

## Interface

`ForumTopicLifecyclePanel` is a class-based Livewire 4 component with a
separate passive Blade view. Public state contains only the locked topic ID,
optimistic version, selected moderation state, bounded form fields, and short
feedback text. Models, query builders, legal-hold evidence, and policy results
remain server-side.

The panel provides:

- stale and necropost notices with the reference date;
- bounded public-safe version history;
- owner reopen, solve, bump, archive, remove, and restore controls;
- update request/proposal and authorized review forms;
- administrator state, redirect/merge, and legal-hold controls;
- action-specific loading, offline, error, confirmation, and success states.

All text lives in `lang/{en,lt,ru}/forum_topic_lifecycle.php`. Controls use
native buttons, labels, semantic status regions, visible text plus icons, and
minimum 44-pixel targets. No lifecycle operation requires hover,
drag-and-drop, animation, JavaScript-only mutation, queue, cron, or supervisor.

## Seeding And Migration

`2026_07_31_001250_create_forum_topic_lifecycle_tables.php` is additive. It
adds lifecycle columns and four normalized tables with foreign keys,
uniqueness, and leading/query-pattern indexes.

`ForumTopicLifecycleBackfillSeeder` is production-safe, chunked, idempotent,
and conservative. It derives only missing timestamps from existing stored
timestamps and records the existing state without classifying prose.
`ForumTopicLifecycleDemoSeeder` is restricted to local, demo, and testing
environments.

Deployment order:

1. back up the database;
2. deploy compatible code and run the additive migration;
3. run the production-safe forum system seed when the release procedure
   explicitly requires it;
4. verify one public, stale, archived, removed, restored, redirected, and
   legal-hold topic;
5. keep the additive schema on application rollback after production writes.

## Verification

Primary evidence:

- `tests/Unit/ForumTopicStatusTest.php`
- `tests/Feature/Forum/ForumTopicLifecycleTest.php`
- `tests/Feature/Auth/PolicyMatrixTest.php`
- `tests/Feature/Database/SchemaIntegrityTest.php`
- `tests/Feature/Database/FactoryAndSeederTest.php`
- `tests/Feature/Forum/ForumJournalWorkflowTest.php`
- `tests/Feature/ArchitectureComplianceTest.php`
- `tests/Feature/LocalizationTest.php`

The completed package plan and exact command results are recorded in
`docs/plans/forum-phase8-content-lifecycle-work-package.md`.
