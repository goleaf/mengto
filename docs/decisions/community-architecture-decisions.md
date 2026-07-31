# Community Architecture Decisions

Date: 2026-08-01

## ADR-COMMUNITY-001: ForumGroup Is The Community Aggregate

Reuse `ForumGroup` as the stable community identity. Forum, chat-compatible
content, knowledge, events, files, polls, and future modules reference its ID.
Renames and ownership changes must not create a second group identity.

## ADR-COMMUNITY-002: Membership Stores Account And Display Profile

Every membership stores the authenticated `User` and one represented
`SocialActor`. The actor is the visible participation context; the user is the
accountable controller used for authorization and audit. A pet profile never
creates anonymity.

Membership uniqueness is `(forum_group_id, social_actor_id)`. One account may
therefore participate through distinct controlled profiles without duplicate
membership for the same profile.

## ADR-COMMUNITY-003: Actor Eligibility Is Server Authoritative

The browser submits only an actor key. `CommunityMembershipActorEligibility`
reloads bounded controlled actors, rejects foreign or inactive actors, applies
the group's actor-type allowlist, and excludes group actors. Policies and
Actions repeat the check; hiding an option in Blade grants no right.

Personal, pet, and expert actors are enabled in this package. Shelter and
organization participation remains closed until those actors have canonical
ownership and delegation rules.

## ADR-COMMUNITY-004: Rule Acceptance Is A Membership Snapshot

A join or invitation acceptance records the current group rules version, the
acceptance time, and the represented profile. This proves which version the
membership accepted. A future rule-history package must add immutable rule
versions, effective dates, localized variants, and re-consent; the integer
snapshot alone is not that complete workflow.

## ADR-COMMUNITY-005: Modules Keep Their Authoritative State

`ForumTopic`, `KnowledgeArticle`, `ForumEvent`, adoption, lost-and-found,
marketplace, expert credentials, and moderation keep their own state machines
and policies. Community projections link to them. User text cannot impersonate
an official event, credential, adoption, payment, or moderation status.

## ADR-COMMUNITY-006: Permissions Are Capabilities, Not UI Labels

Every community mutation is authorized on the server against current account,
profile, membership, role, state, group state, and relevant module context.
Fixed role names remain compatibility data. Future custom roles must compose
explicit capabilities scoped by module, section, subgroup, project, and time.

## ADR-COMMUNITY-007: Privacy Contracts Every Projection

Private or secret membership, content, member lists, files, search snippets,
recommendations, notifications, and caches must share one read-time policy.
Leaving, removal, blocking, expiry, or role revocation contracts every surface.
No private scope is inferred only from a hidden navigation item.

## ADR-COMMUNITY-008: Lifecycle Changes Preserve Ownership And Evidence

Archive, merge, split, transfer, and deletion are explicit audited workflows.
They cannot widen an audience, re-enrol a removed user, discard another
author's rights, erase an active report, or orphan financial and event records.
Irreversible deletion is deferred until those domain contracts exist.

## ADR-COMMUNITY-009: Rollback Must Refuse Lossy Downgrades

Legacy account-scoped membership cannot encode two profile memberships for the
same account and group. The additive migration supports lossless rollback for
compatible rows and deliberately refuses a lossy rollback before schema
mutation once profile multiplicity exists.
