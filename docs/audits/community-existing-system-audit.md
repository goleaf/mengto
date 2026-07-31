# Community Existing System Audit

Date: 2026-08-01

## Existing Canonical Aggregate

`ForumGroup` is already the durable community aggregate. It has a stable key,
owner, localized display fields, rules, visibility, lifecycle state, locale,
location scope, membership questions, member count, optimistic lock, and audit
events. Replacing it with a second `Community` model would split URLs,
membership, moderation, events, and knowledge into competing sources of truth.

The existing group workspace is class-based Livewire with a separate Blade
view. Public Livewire state contains scalar identifiers and form input only;
actions reload the group and authorize on the server.

## Existing Community Modules

The repository already links groups to:

- memberships, roles, invitations, review state, notifications, and audit;
- forum topics, comments, moderation reports, and member blocks;
- announcements, activities, files, polls, and persistent group content;
- canonical `ForumEvent` records, registration, waitlists, attendance, and
  private safety details;
- `KnowledgeArticle` guides with versions, corrections, review, sources, and
  localization;
- taxonomy, expert credentials, adoption, lost-and-found, marketplace, and
  volunteer-adjacent workflows.

These domains remain authoritative. A community presents or links them; it
does not copy event status, professional verification, adoption state, search
case state, payment state, or medical facts into mutable group text.

## Membership Gap Found

Before this package, `forum_group_memberships` was unique by group and real
account. A user could publish elsewhere as a pet or expert `SocialActor`, but
could join a group only as the undifferentiated account. That contradicted the
community requirement that the visible participant profile and the real
controlling account be separate facts.

The first community package adds a required `social_actor_id`, keeps
`user_id` as the accountable real account, scopes uniqueness to group and
actor, records the accepted rule version and time, and lets a user select an
eligible personal, pet, or expert profile. The server rechecks control of that
actor and the group's allowed actor types for every join or invitation accept.

## Existing Coverage That Is Not Yet Complete Point 6

Current visibility values are public, request-to-join, private, and unlisted.
Current lifecycle values are active, closed, and archived. Existing fixed
roles cover owner, administrator, moderator, steward, member, and restricted
member. Those useful foundations do not yet prove the complete Point 6 model.

The following remain open:

- canonical shelter and organization actors and participation;
- chapters, subgroups, scoped sections, and inherited governance;
- custom and expiring roles with capability-level grants;
- rule-version history, translations, re-consent, and effective dates;
- every requested membership status and recovery transition;
- knowledge curation workflows specific to communities;
- volunteer boards, shifts, fundraising, sponsorship, and financial reports;
- community merge, split, transfer, deletion grace period, and export;
- anti-raid automation, coordinated harassment analysis, and minor-specific
  community controls across all modules;
- recommendation explanations, sensitive-signal exclusions, digest controls,
  offline mutation queues, and the complete accessibility scenario matrix.

## Data And Query Boundaries

Membership reads use indexed group/actor and user/state/actor paths. Eligible
profiles are bounded to one personal actor, at most 100 managed pets and 50
expert profiles; group actors are deliberately excluded from community
membership until organization delegation has a dedicated policy. The group
directory retains its existing query ceiling and Blade performs no queries.

## Compatibility And Rollback

The migration is additive and backfills every legacy membership to the real
user's personal `SocialActor` in bounded chunks. A rollback is lossless while
each account has at most one profile membership per group. Once distinct
profiles from one account have joined the same group, the old unique key
cannot represent the data; rollback therefore refuses before changing the
schema instead of silently deleting a membership.
