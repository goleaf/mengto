# Content Feed Foundation Work Package

Date: 2026-08-01

Status: released and independently verified

## Exact Verified Requirement IDs

The following 58 independently tested atoms are verified for phase 36. This is
not a completion claim for the rest of the phase.

- real actor and represented profile attribution:
  `content.foundation.0046-content.foundation.0049`,
  `content.foundation.0054-content.foundation.0055`, and
  `content.foundation.0060`;
- one canonical original: `content.foundation.0074` and
  `content.foundation.0076`;
- canonical publication fields:
  `content.foundation.0118-content.foundation.0130` and
  `content.foundation.0132`;
- published status: `content.foundation.0150`;
- tested audiences: `content.foundation.0171-content.foundation.0177`,
  `content.foundation.0179`, `content.foundation.0181`,
  `content.foundation.0185`, and `content.foundation.0189`;
- hidden-profile protection: `content.foundation.0192` and
  `content.foundation.0200`;
- typed pet link: `content.foundation.0203-content.foundation.0204`;
- text without mandatory media: `content.type.0003`;
- independent media fields: `content.data.0004-content.data.0008`,
  `content.data.0012-content.data.0013`, and
  `content.data.0015-content.data.0017`;
- reusable media relation: `content.data.0019` and `content.data.0021`;
- structured audience and exclusions: `content.data.0028`,
  `content.data.0032`, and `content.data.0036`;
- publication idempotency: `content.data.0050`, `content.data.0054`, and
  `content.data.0058`.

All other phase 36 atoms remain open, including complete type-specific
behavior, moderation state, translation versions, event credentials,
temporary-link credentials, and private media delivery.

## Implementation Contract

`ContentPublication` is the single original. It stores the authenticated user,
publishing `SocialActor`, representation-role snapshot, type, status, locale,
text, publication and expiry times, optimistic version, idempotency key, and
creation fingerprint. Separate records own the audience rule, interaction
capabilities, domain links, media, publication-media placement, and immutable
events.

`CreateContentPublication` authorizes the current user against the publishing
actor, requires the pet-specific `Publish` permission, rejects a public post
outside the user, pet, expert, or group profile privacy ceiling, validates
audience context, and creates the aggregate in one transaction. Replaying the
same command returns the same record; reusing the key for a different payload
fails.

## Read-Time Privacy

`ContentPublication::visibleTo()` is shared by direct detail authorization and
the chronological projection. It checks published state, expiry, publishing
actor status, profile-aware broad visibility, account blocks, current social
relationships, current group membership, selected inclusions, and explicit
exclusions on every request. Narrow audiences remain available to authorized
viewers even when the publishing actor is not directory-discoverable. Unknown
event and temporary-link credentials fail closed.

## Schema And Compatibility

Six additive migrations create only `content_*` tables and indexed foreign-key,
chronological, processing, expiry, and idempotency paths. Existing expert
publications, photo assets, comments, reactions, and encrypted prototype state
remain untouched. The compatibility command reports bounded counts and never
reads or imports encrypted payload content.

## Blade Flow

Grouped public routes call thin invokable controllers. Controllers delegate to
`ContentChronologicalFeed` and `ContentFeedPresenter`; the service selects
bounded columns, eager-loads all rendered relationships, and cursor-paginates.
Blade receives arrays only and performs no Eloquent query or authorization
decision.

## Release Gates

1. Fresh migration and isolated rollback preserve every pre-existing table.
2. Direct URL and feed access produce the same audience result.
3. Every supported audience has positive, negative, and revocation coverage.
4. One blocked or excluded actor cannot recover access through another edge.
5. Twenty records render fifteen items within the recorded query budget.
6. Private disk paths and checksums never enter HTML.
7. EN/LT/RU labels, factories, routes, command, generator, Pint, Larastan,
   full Pest, audits, build, cache compilation, and browser checks pass.
8. Only the exact candidate IDs with final evidence become `verified`.
