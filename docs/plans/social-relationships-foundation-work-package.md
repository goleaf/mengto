# Social Relationships Foundation Work Package

Date: 2026-07-31

Status: foundation implemented and release-verified; later packages remain open

## Exact Requirement IDs

These inclusive intervals define the package's candidate scope. They are not a
bulk verification claim: each record remains open until its own acceptance
behavior is linked in the evidence overlay after all release gates pass.

- Relationship separation and core types:
  `social.relationship.0029-social.relationship.0103`,
  `social.relationship.0121-social.relationship.0152`, and
  `social.relationship.0198-social.relationship.0225`.
- Request creation, settings, lifecycle, concurrency, and duplicate control:
  `social.request.0001-social.request.0033` and
  `social.request.0066-social.request.0222`.
- Public/private follows and unsubscribe:
  `social.follow.0001-social.follow.0091` and
  `social.follow.0110-social.follow.0125`.
- Two-sided pet-friendship creation:
  `social.pet-friendship.0001-social.pet-friendship.0048`.
- Block, restriction, mute, unfriend, and quiet denial:
  `social.safety.0002-social.safety.0103`.
- Social graph visibility and request notifications:
  `social.privacy-notifications.0001-social.privacy-notifications.0102`.
- Canonical data, direction, uniqueness, idempotency, race handling, audit,
  dynamic authorization, and invalidation:
  `social.data.0001-social.data.0162`.
- Mobile/desktop relationship center and accessibility:
  `social.interface.0001-social.interface.0037`,
  `social.interface.0054-social.interface.0090`, and
  `social.interface.0107-social.interface.0174`.
- Stable translated relationship types: `social.translation.0039-social.translation.0049`.
- Minimum relationship/request/safety release contract:
  `social.release.0001-social.release.0042` and
  `social.release.0061-social.release.0081`.
- Executable foundation scenarios:
  `social.scenario.0001-social.scenario.0015`,
  `social.scenario.0042-social.scenario.0059`, and
  `social.scenario.0077-social.scenario.0081`.

## Desired Result

Authorized actors can follow public user/pet/expert/group profiles, request a
private follow, request and accept owner or pet friendship, create a directed
close-circle/mute/restrict/block edge, end an active relationship, and inspect
their manager-only social graph. Each implemented mutation is attributed to a
real user, idempotent, transactional, policy checked, and audited. No social
relationship grants pet or professional administrative access.

## Schema And Indexes

Add actor adapters, actor settings, relationship requests, relationships, and
append-only relationship events. Index actor resolution, endpoint/type/status
lists, expiry, inbox/outbox, and actor audit paths. Use a nullable unique
`active_key` to enforce one active edge/request while retaining history.

## Migration And Compatibility

Schema expansion is additive. A bounded idempotent command creates adapters
for existing authoritative profiles and reports exact/ambiguous legacy state.
It does not delete or automatically promote prototype relationships. Rollback
drops only the new social tables after deployment dependencies are removed.

## Authorization And Privacy

Policies validate the authenticated user's authority over the represented
actor. Pet actions reuse pet manager permissions. A block is checked before
request creation, acceptance, directory projection, and graph projection. The
current relationship center is manager-only; list/count preferences are stored
for later viewer-facing projections but are not claimed as a complete public
graph. Medical, exact location, ownership evidence, credentials, hidden
groups, and private family structure are excluded.

## Interface

Replace no existing URL. Add a class-based Livewire relationship center with
separate Blade view, locked actor/request IDs, bounded pagination, clear empty
and error states, explicit confirmation for destructive actions, and complete
keyboard/screen-reader labels. Existing preview routes remain compatible.

## Tests And Acceptance

1. Fresh migration, rollback, and repeated actor backfill preserve all current
   records and legacy state.
2. Directed and symmetric active keys reject duplicate edges under replay.
3. Accept versus cancel is serialized and produces one final state.
4. Public follow, private follow request, owner friendship, and pet friendship
   require the intended consent and authority.
5. Close circle never grants management rights.
6. Block/restrict/mute/unfriend have distinct server-enforced effects.
7. Revoked pet authority blocks the next request.
8. Manager-only lists/counts exclude blocked or unauthorized actors; public
   viewer-aware graph projection remains a later package.
9. Every critical mutation writes one actor-attributed immutable event.
10. EN/LT/RU, direct Livewire authorization, query bounds, accessibility,
    Pint, Larastan, full Pest, build, cache, and browser checks pass before
    any selected ID is marked verified.

## Release Evidence

- 158 exact IDs from the candidate intervals are verified in the deterministic
  overlay; no interval was bulk-promoted.
- Focused social suite: 22 tests and 432 assertions.
- Expanded pet/social/architecture slice: 63 tests and 26,709 assertions.
- Schema/factory/social regression: 1,250 tests and 4,055 assertions.
- Final serial repository suite: 1,861 tests and 69,718 assertions in 90.930
  seconds.
- Isolated migration, seed, repeat backfill, rollback/re-application, static
  analysis, formatting, dependency audits, build, cache compilation, source
  checks, requirement generation, and browser accessibility checks passed.
