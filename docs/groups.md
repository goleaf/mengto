# Persistent Forum Groups

## Scope

Persistent groups are the canonical group identity, membership, invitation,
management, audit, discovery, and reporting boundary. The earlier `/groups`
preview remains a compatibility presentation route; authoritative persistent
records live under `/forum/groups`.

The compatibility preview composes the canonical public directory-card
primitives for its media, body, heading, description, statistics, and footer.
Those primitives own containment, spacing, responsive geometry, and accessible
optional links; `x-group-card` owns only group-specific badges, recommendation,
event, organizer, and action ordering. This visual composition does not make
preview state authoritative and does not replace persistent group policies or
Actions. The rationale and migration boundary are recorded in
`docs/audits/groups-shared-card-ux-audit.md` and
`docs/plans/shared-directory-card-system-plan.md`.

The group-core package verifies `forum.feature.3108` through `forum.feature.3133`,
`forum.moderation.0323`, `forum.moderation.0324`,
`forum.feature.3140` through `forum.feature.3142`, and
`forum.search.0123`. Persistent topics, activities, guides, polls, private
files, and announcements are implemented by the separate boundary documented
in `docs/polls.md`; their existence does not broaden group-core permissions.

## Domain Model

`ForumGroup` owns the current projection:

- stable key, owner, localized system metadata, description, rules;
- visibility, lifecycle status, default locale, generalized location;
- bounded membership questions and taxon-based species focus;
- active-member counter and optimistic `lock_version`.

`ForumGroupMembership` stores one user projection per group. Roles are owner,
administrator, moderator, steward, member, and restricted member. States are
active, pending, rejected, removed, banned, and left.

`ForumGroupInvitation` stores an expiring, one-use invitation. It carries a
stable idempotency key and an `open_key` that prevents more than one pending
invitation for the same group/recipient pair.

`ForumGroupEvent` is append-only audit evidence for creation, membership,
invitation, role, ownership, lifecycle, and report activity. Routine
operations never edit or delete event history.

Group content is stored in `forum_group_activities`,
`forum_group_announcements`, `forum_group_files`, `forum_polls`,
`forum_poll_options`, and `forum_poll_votes`, plus nullable group relations on
topics and knowledge guides. These records inherit the member-content boundary
and do not expose private groups through public directories.

The `forum_group_taxon` pivot links groups to the global animal taxonomy. It
does not create a parallel species list.

## Visibility

- `public`: discoverable and immediately joinable.
- `request-to-join`: discoverable; membership requires an approved request.
- `private`: absent from unauthorized discovery and viewable only by owner,
  platform administrator, active member, or invited recipient.
- `unlisted`: absent from discovery but reachable by an authorized direct URL.

Archived groups are hidden from ordinary members. Private and unlisted rows,
counts, filters, and invitation details are scoped before presentation.
Member-only content requires active membership, ownership, or platform
administration even when public group identity is visible.

## Mutation Boundary

Every write uses a dedicated Action:

- `CreateForumGroup`
- `RequestForumGroupMembership`
- `ReviewForumGroupMembership`
- `InviteForumGroupMember`
- `RespondToForumGroupInvitation`
- `RevokeForumGroupInvitation`
- `ChangeForumGroupMemberRole`
- `RestrictForumGroupMember`
- `LeaveForumGroup`
- `TransferForumGroupOwnership`
- `TransitionForumGroup`
- `AssociateForumTopicWithGroup`
- `AssociateKnowledgeGuideWithGroup`
- `CreateForumGroupActivity`
- `PublishForumGroupAnnouncement`
- `CreateForumPoll`
- `CastForumPollVote`
- `StoreForumGroupFile`
- `ArchiveForumGroupFile`

Actions reload and authorize records, validate typed input, use bounded
transactions and row locks where a race changes state, check optimistic
versions, enforce unique/idempotency constraints, maintain the member counter,
and append an audit event. The owner cannot be removed through member
management. Ownership transfer is owner-only and updates both membership
projections atomically.

Unified reports use `SubmitForumReport`; reporter identity and evidence remain
inside the existing moderation authorization boundary.

## Livewire Interface

`GroupDirectory`, `GroupWorkspace`, and `GroupManagement` are normal class-based
Livewire components with separate Blade templates.

- Public state contains scalar filters, form values, short answers, IDs, and
  displayed lock versions only.
- Computed results use bounded pagination and explicit eager loading.
- Every action argument is untrusted and resolved and authorized again.
- Search covers stable group metadata and active scientific taxonomy names
  without serializing the taxonomy tree.
- Loading, offline, validation, empty, membership, invitation, and lifecycle
  states are localized.
- Management controls use native labeled form controls and provide textual
  role/status output.

## Seeding

`ForumGroupDefinitionSeeder` synchronizes six system-managed definitions by
stable key. It updates only system-managed metadata, preserves IDs and
relationships, and does not overwrite administrator-created groups.

`ForumGroupDemoSeeder` is restricted to local, demo, and testing environments.
It creates deterministic owner/member/invitation examples plus a topic, guide,
activity, announcement, private file, and three poll modes through the same
domain constraints used by production code. Repeated full seeding is
idempotent and non-destructive.

## Caching And Counters

Membership, invitations, private visibility, and audit history are read from
the database and are never shared through a public cache key. The group
directory currently uses indexed bounded queries rather than a result cache.
If a measured cache is introduced, its key must include locale and visibility
context and invalidate after creation, visibility/lifecycle change,
translation change, and system synchronization.

`active_member_count` is maintained by membership Actions. Repair consists of
an authorized bounded aggregate reconciliation; operators must not clear all
application caches or edit the counter without audit evidence.

## Deployment And Recovery

The additive migration creates `forum_groups`, `forum_group_memberships`,
`forum_group_invitations`, `forum_group_events`, and `forum_group_taxon`.
It drops no legacy table or content.

Before group activity, migration rollback removes only these new tables. Once
production activity exists, disable group writes, retain/export the tables,
restore application compatibility, and deploy a reviewed forward fix.
Deleting append-only events is not a recovery procedure.

## Verification

- `tests/Feature/Forum/GroupCoreWorkflowTest.php`: 22 tests and 1,208
  assertions for visibility, roles, policies, requests, invitations,
  membership management, ownership, lifecycle, reporting, idempotency,
  privacy, seeding, localization, factories, query budgets, and Livewire.
- Full serial Pest checkpoint: 1,338 tests and 48,931 assertions.
- Fresh temporary SQLite verification: 92 migrations, 140 tables, repeated
  seed with stable user count 5.
- Pint and Larastan: passed with zero Larastan errors.
- Playwright at 375x812 and 1440x900: one `h1`, no document overflow, zero
  unlabeled visible form controls, zero unnamed visible buttons, zero controls
  below 44px, and no current-page console warnings/errors. Private group management,
  owner membership, and a pending invitation were visible only to an
  authorized administrator; an ordinary member received 403.

The requirement evidence is maintained in
`docs/traceability/forum-requirement-evidence.json`.
