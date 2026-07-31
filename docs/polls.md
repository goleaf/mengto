# Group Content And Polls

## Scope

This document is the canonical implementation contract for persistent group
topics, guides, activities, announcements, private files, and polls. It covers
`forum.feature.3134` through `forum.feature.3139` and
`forum.feature.3143` through `forum.feature.3160`.

The implementation extends the persistent group boundary described in
`docs/groups.md`. It does not turn the legacy session-backed group preview or
event prototype into an authoritative data source.

## Content Model

- Existing `ForumTopic` and `KnowledgeArticle` records may gain one nullable
  `forum_group_id`. Association preserves the record ID, history, replies,
  reactions, subscriptions, reports, and attachments.
- `ForumGroupActivity` stores a bounded physical, online, or hybrid event with
  creator, schedule, timezone, generalized location, capacity, lifecycle, and
  archive state.
- `ForumGroupAnnouncement` stores an authored publication and optional expiry
  window.
- `ForumGroupFile` stores metadata for a generated path on the private `local`
  disk. The original filename is presentation-only and member-only.
- `ForumPoll`, `ForumPollOption`, and `ForumPollVote` store one poll definition,
  bounded choices, and one current vote projection per poll and user.

Public forum and knowledge scopes exclude grouped records. Direct topic,
guide, and file routes authorize the current group membership before returning
content, counts, or bytes.

## Poll Contract

Poll types are single choice, multiple choice, and ranked choice. Voter
identity is anonymous or visible. Results are public, visible after the current
user votes, or visible after closure. Votes may be editable before closure or
final after the first submission.

Eligibility is one of:

- active group members;
- active group members with a current trusted community assignment;
- active group members within the group's generalized location scope.

Location eligibility never stores or compares a member's exact address.
Trusted eligibility consumes the existing trust-level boundary and never
derives authority from karma, reactions, badges, purchases, or post volume.

`closes_at` is authoritative at both read and mutation time. No queue,
scheduler, cron process, or status-flipping job is required to reject a late
vote. Cancelled or archived polls are also closed.

Every poll displays a localized boundary: community preferences are not proof
of a medical diagnosis, legal conclusion, or scientific fact.

## Concurrency And Idempotency

`CastForumPollVote` reloads the poll under a row lock, rechecks closure and
authorization, validates every selected option against the poll, and locks the
existing user vote. A unique `(forum_poll_id, user_id)` constraint enforces one
projection at the database boundary.

Creation and voting commands carry unique idempotency keys. An exact retry
returns the prior record. A conflicting reuse is rejected. Editable votes
replace choices using optimistic `lock_version`; option counters and the poll
total are updated in the same bounded transaction.

Rank order is stored as a bounded validated JSON list. It is not used as a
generic unvalidated topic payload.

## Files

`StoreForumGroupFile` accepts only validated PDF, plain-text, JPEG, PNG, and
WebP content up to 10 MiB. It detects MIME from content, creates an opaque
server-side path, records SHA-256 and byte size, and removes the stored object
if database persistence fails.

`PrepareForumGroupFileDownload` authorizes membership at request time and
returns the configured disk/path only after verifying that the active file
exists. Archive is a separate authorized lifecycle action. No public URL is
created.

## Livewire

`GroupContentWorkspace` is a class-based Livewire component with a separate
Blade view. Public state is limited to forms, selected option IDs, optimistic
versions, and idempotency tokens. It never serializes groups, polls, files,
queries, or service objects into the browser snapshot.

The workspace uses native radio, checkbox, and ranked select controls. Ranked
polls have a keyboard alternative and do not require drag-and-drop. Loading,
offline, validation, empty, result-hidden, closed, and success states are
localized in EN, LT, and RU.

The component authorizes member content once before its bounded projections.
Each mutation still reloads and authorizes its target in the dedicated Action;
presentation authorization is never a replacement for command authorization.

## Seeding

`ForumGroupDemoSeeder` runs only in configured local/demo/testing
environments. After forum topics and guides exist, it creates one associated
topic, one associated guide, an activity, announcement, private text fixture,
and single, multiple, and ranked poll examples through production Actions.
Stable slugs and idempotency keys make reruns non-destructive.

Production-safe group definitions do not create fake content or files.

## Recovery

The migration is additive. Before production activity, rollback removes the
new tables and nullable relations. Once group content exists, disable writes,
retain the tables and private files, export affected records, and deploy a
forward correction. Dropping tables or deleting file objects is not an
acceptable recovery shortcut.

## Verification

`tests/Feature/Forum/GroupContentAndPollWorkflowTest.php` covers association,
privacy, all poll modes, result and voter visibility, eligibility, vote edits,
idempotency, timestamp-derived closure, private files, direct Livewire access,
public-directory exclusion, and a query-count regression budget.

`tests/Feature/Database/FactoryAndSeederTest.php` creates every new model,
exercises every enum-backed state, and verifies repeated full seeding.
Architecture, schema, localization, static-analysis, build, browser, and fresh
database evidence is recorded in the phase plan and requirement matrix.
