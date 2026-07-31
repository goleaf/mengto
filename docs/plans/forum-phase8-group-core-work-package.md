# Phase 8 Work Package: Persistent Group Core

Status: in progress

Last updated: 2026-07-31

## Requirement Scope

This package implements and verifies the persistent group boundary represented
by these atomic requirements:

- `forum.feature.3108` through `forum.feature.3133`
- `forum.moderation.0323`
- `forum.moderation.0324`
- `forum.feature.3140`
- `forum.feature.3141`
- `forum.feature.3142`
- `forum.search.0123`

The package intentionally does not claim the content-link requirements
`forum.feature.3134` through `forum.feature.3139` or the poll requirements
`forum.feature.3143` through `forum.feature.3160`. Those requirements need
their own schema, authorization, and concurrency work packages after this
persistent group core exists.

## Required Reading And Repository State

Before implementation, this package was grounded in:

1. `docs/requirements/forum-source-prompt.md`
2. `docs/requirements/forum-master-requirements.md`
3. `docs/traceability/forum-requirements-matrix.md`
4. `docs/plans/forum-current-progress.md`
5. `docs/superpowers/specs/2026-07-29-groups-mvp-design.md`
6. the current models, migrations, routes, group services, Blade views, tests,
   and uncommitted diff

The repository is on `main`. The worktree contains the attributable forum and
taxonomy modernization plus unrelated untracked `.agents/vendor/` content.
The vendor content must not be edited, removed, staged, or committed.

## Current Implementation Analysis

Groups are currently a localized, session-backed prototype:

- `GroupCatalog` and `GroupContentCatalog` contain six static communities.
- `GroupState` stores membership, notification, poll, recommendation, and
  report state in a per-user encrypted domain-state record.
- `PerformGroupAction` performs unversioned state transitions without a group
  policy, relational constraints, review history, or independent moderation
  evidence.
- public and `closed` are the only implemented visibility values.
- owner, administrator, moderator, steward, restricted-member, invitation,
  approval, ban, ownership transfer, closure, and archive records do not exist.
- static routes reveal only catalog entries, but there is no database-level
  query boundary preventing a future private-group enumeration defect.

The existing prototype is useful presentation material, but it is not a
production source of truth and cannot satisfy concurrency, audit, privacy, or
authorization requirements.

## Desired Result

Create a durable Eloquent group domain with:

- public, request-to-join, private, and unlisted visibility;
- owner, administrator, moderator, steward, member, and restricted-member
  roles;
- active, pending, rejected, removed, banned, and left membership states;
- localized system groups and user-authored groups;
- description, rules, language, generalized location, membership questions,
  and taxon-based species focus;
- idempotent invitations, join requests, approvals, removals, bans, role
  changes, ownership transfer, closure, and archive;
- policy enforcement at every server mutation;
- an append-only group event log;
- unified polymorphic reporting through the existing moderation system;
- public discovery queries that cannot reveal private or unlisted groups to
  unauthorized users through rows, counts, or suggestions;
- class-based Livewire directory/detail/management components with separate
  Blade templates and small validated state.

## Files Expected To Be Created

- group visibility, role, membership-state, lifecycle-state, invitation-state,
  and event-type enums under `app/Enums`
- typed group input and presentation data under `app/Data`
- `ForumGroup`, `ForumGroupMembership`, `ForumGroupInvitation`,
  `ForumGroupEvent`, and group-to-taxon pivot models as justified by the final
  schema
- corresponding factories
- `ForumGroupPolicy`
- dedicated Actions for creation, membership requests, invitations, review,
  removal, ban, ownership transfer, and lifecycle transitions
- class-based Livewire directory, detail, and management components with
  separate Blade views and form objects
- an additive migration and an idempotent production-safe group definition
  seeder
- EN, LT, and RU translation files
- `tests/Feature/Forum/GroupCoreWorkflowTest.php`
- `docs/groups.md`

## Files Expected To Be Modified

- `app/Models/User.php`
- `app/Actions/PerformGroupAction.php` only where a compatibility adapter is
  needed
- `app/Services/GroupCatalog.php`, `GroupState.php`, and `GroupPresenter.php`
  as the session prototype is retired or isolated
- group controllers, routes, and Blade entry pages
- `app/Actions/SubmitForumReport.php`
- `database/seeders/DatabaseSeeder.php`
- architecture, authorization, data-model, domain-model, frontend, Livewire,
  security, seeding, testing, operations, performance, and index documentation
- `CHANGELOG.md`, current progress, gap audit, final completeness audit, and
  requirement evidence

The exact list may shrink when an existing class can be retained safely. New
production files outside this plan require a documented plan update first.

## Schema Changes

The additive migration will create:

- `forum_groups`
- `forum_group_memberships`
- `forum_group_invitations`
- `forum_group_events`
- `forum_group_taxon`

The design will use:

- stable internal keys independent of translated names;
- foreign keys and leading indexes for every relationship path;
- unique group/user membership and group/taxon relationships;
- idempotency keys for retried commands;
- optimistic versions on mutable group and membership projections;
- restrictive deletion for append-only events;
- timestamps and explicit archive/closure fields;
- JSON only for validated bounded membership questions, localized system
  metadata, and non-relational configuration.

## Data Migration Strategy

1. Create the new tables without changing existing routes.
2. Seed the six static catalog groups with stable keys.
3. Create owner and existing demo membership rows from deterministic local
   identities only in allowed demo environments.
4. Preserve existing per-user session-domain membership state during the
   compatibility window.
5. When a signed-in user first interacts with a seeded group, synchronize the
   legacy joined or pending state once through a dedicated compatibility
   action and record the provenance.
6. Switch reads and mutations to the relational domain.
7. Retain legacy state keys until a later cleanup package proves no supported
   path reads them.

No existing topic, reply, reaction, subscription, report, attachment, pet,
marketplace, adoption, lost/found, translation, or administrator-created data
will be deleted or rewritten.

## Rollback Strategy

Before production data exists, `migrate:rollback` removes only the new group
tables. After group activity exists, rollback requires:

1. disable new group mutations;
2. export group, membership, invitation, and event records;
3. restore the prior application release;
4. retain the additive tables until a reviewed forward migration is available.

The migration does not drop legacy session-domain data or existing tables.
Append-only audit events use restrictive foreign keys and are not silently
destroyed.

## Legacy Compatibility

- Existing `/groups` and stable `/groups/{key}` URLs remain valid.
- Existing group translation keys and imagery remain presentation inputs until
  intentionally migrated.
- Existing created-content preview routes remain intact and are explicitly
  labelled legacy until their own migration package.
- Legacy group actions delegate to the new Actions when the target resolves to
  a persisted group.
- No current static content tab is claimed as persistent content until its
  corresponding requirement package is implemented.

## Authorization Changes

`ForumGroupPolicy` will define:

- list/discover visibility;
- view public identity;
- view member-only content;
- create;
- update;
- request membership;
- invite;
- review join requests;
- manage member roles;
- remove or ban a member;
- transfer ownership;
- close;
- archive;
- report;
- view the audit log.

Only an owner can transfer ownership. Administrators may manage membership and
configuration but cannot silently replace the owner. Moderators and stewards
receive bounded capabilities. Ordinary and restricted members cannot invoke
management Actions directly. Platform administrators remain subject to
explicit policy branches and audit evidence.

## Validation Changes

Livewire form objects and Action-level invariants will validate:

- stable name and key rules;
- supported visibility, role, state, locale, and notification values;
- bounded description and rules;
- generalized location only;
- membership questions and answers with allowed keys and lengths;
- taxon existence and active status;
- invitation recipient and expiration;
- legal role transitions;
- optimistic version;
- idempotency keys;
- owner-transfer confirmation;
- closure and archive reasons.

Validation never replaces authorization or database constraints.

## Translation Changes

All platform-controlled group labels, states, actions, validation messages,
empty/loading/error states, privacy notices, and audit event summaries will be
added with stable keys to EN, LT, and RU. User-authored group names,
descriptions, rules, questions, and answers remain in their original locale.

## Interface Changes

The persistent interface will provide:

- bounded directory search and visibility-aware filters;
- public identity for discoverable groups;
- member-only detail content;
- request, invitation, approval, leave, removal, and ban states;
- owner/administrator membership management;
- generalized location and taxon context;
- audit history visible only to authorized managers;
- report action integrated with the existing report-reason catalogue.

The existing static content preview may remain below the persistent identity
surface during the compatibility window, but it cannot override relational
authorization.

## Accessibility Changes

- one logical page heading;
- native links, buttons, selects, and form controls;
- labels and field-linked errors;
- textual role, visibility, and status indicators;
- an accessible error summary;
- no drag-only management action;
- 44-pixel primary touch controls;
- focus restoration after management actions;
- no horizontal page overflow at 375, 768, 1024, and 1440 widths;
- no flashing or motion-dependent status.

## Cache Changes

Only the discoverable group directory and stable system-group metadata are
cache candidates. Cache keys must include locale and a visibility-context
version. Membership, invitations, private counts, management state, and audit
history are never shared through a public cache key.

Invalidation occurs after group creation, visibility/lifecycle change,
translation change, and system seed synchronization. Authorization remains
database-backed even on a cached public list.

## Security, Privacy, And Abuse Risks

- Private and unlisted enumeration: all list, count, and suggestion queries use
  one policy-aware discoverability scope.
- Exact-location disclosure: the schema stores only generalized labels and
  optional external location taxonomy identifiers.
- Invitation spam: recipient, pair, and time-window limits are enforced.
- Join-request abuse: one current membership projection plus event history,
  bounded retries, and report/block integration.
- Privilege escalation: all role and owner transitions execute in a locked
  transaction and re-authorize after row reload.
- Last-owner removal: prohibited by Action and database-backed owner
  invariants.
- Audit destruction: group events are append-only and restrict parent deletion.
- Report leakage: reporter identity and evidence remain inside the existing
  moderation authorization boundary.
- Search leakage: private/unlisted groups produce no unauthorized row, count,
  autocomplete, feed, or suggestion evidence.

## Tests To Create Or Update

`GroupCoreWorkflowTest` will cover:

- every visibility and role enum;
- public and request-to-join membership;
- private invitation-only access;
- unlisted direct authorized access without discovery;
- question validation;
- duplicate and concurrent request protection;
- invitation expiry and one-time acceptance;
- allowed and denied approvals;
- member role changes;
- owner protection;
- member removal and ban;
- ownership transfer with audit evidence;
- close and archive transitions;
- reports and private moderation evidence;
- private/unlisted directory, count, and suggestion non-disclosure;
- wrong-role and inactive-user direct Action invocation;
- optimistic conflicts and idempotent retries;
- factories, enum states, seed idempotency, stable IDs, and no legacy loss;
- query budgets for directory and management pages;
- localized rendering in EN, LT, and RU.

Architecture/schema/factory/localization tests will be extended. Browser checks
will cover desktop/mobile discovery, a join request, an approval, a private
visibility boundary, labels, keyboard operation, overflow, and console output.

## Documentation To Update

- `docs/groups.md`
- `docs/architecture.md`
- `docs/domain-model.md`
- `docs/data-model.md`
- `docs/authorization.md`
- `docs/security.md`
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
- forum progress, audits, traceability evidence, and generated matrices

## Acceptance Criteria

1. All 32 scoped requirement IDs have file-level and passing-test evidence.
2. Four visibility states and six roles are represented by typed enums and
   enforced by policies.
3. Membership, invitation, review, removal, ban, ownership, closure, and
   archive workflows are transactional, authorized, validated, idempotent, and
   audited.
4. Private/unlisted groups cannot be inferred through unauthorized list rows,
   counts, filters, feeds, or suggestions.
5. Existing stable group URLs continue to resolve.
6. The seed is deterministic, idempotent, preserves IDs, and does not overwrite
   administrator-created groups.
7. Every first-party group model has a valid factory and meaningful states.
8. EN, LT, and RU render without raw keys or placeholder mismatch.
9. The directory and management interface are keyboard-accessible, responsive,
   and free of critical browser-console errors.
10. Targeted tests, architecture checks, Pint, Larastan, fresh/repeat seed,
    production build, and browser checks pass.

## Verification Procedure

1. Run the focused group workflow tests after each Action slice.
2. Run architecture, schema, factory/seeder, and localization tests.
3. Run Pint and Larastan on the completed package.
4. Run a fresh isolated migration and repeat seed.
5. Run the full serial Pest suite.
6. Run the production Vite build.
7. Inspect the group routes and query budgets.
8. Run desktop/mobile Playwright checks for public, request, private, and
   unlisted boundaries.
9. Run source-prompt and requirement-generator checks.
10. Inspect the complete diff and requirement evidence.

## Completion Evidence

This section remains intentionally empty until implementation and verification
are observed. The package must not be marked verified from the existence of
files alone.
