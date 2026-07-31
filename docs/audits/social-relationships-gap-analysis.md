# Social Relationships Gap Analysis

Date: 2026-07-31

## Implemented Foundations We Can Reuse

- stable user, pet, expert, group, and event identities;
- pet manager permissions and server-side policies;
- encrypted compatibility state with optimistic versioning;
- unified reports, moderation cases, appeals, and private evidence;
- persistent groups and events with scoped membership;
- EN/LT/RU Blade and class-based Livewire conventions;
- cache namespace invalidation and architecture/localization tests.

## Critical Missing Capabilities

1. There is no canonical actor adapter for connecting different profile types.
2. There is no durable directed or symmetric relationship aggregate.
3. Requests do not have a complete status, expiry, replay, and audit lifecycle.
4. Following, close circle, restrictions, mute, and blocking are not isolated
   relationship types with independent effects.
5. Pet friendship cannot be accepted by authorized managers on both sides.
6. Viewer-aware relationship lists and counts cannot be queried consistently.
7. A block cannot invalidate requests, recommendations, messages, or cached
   projections across all profiles managed by an account.
8. Recommendations, safe meetings, temporary location, messaging requests,
   minor safety, anti-fraud, and lifecycle transfer rules remain unimplemented.

## First Safe Work Package

The foundation package introduces a canonical social actor adapter, typed
relationship and request aggregates, actor settings, append-only events,
server-side policies, idempotent Actions, scoped Eloquent queries, cache
invalidation, compatibility reporting, and an accessible relationship center.
It covers follows, owner friendship, pet friendship, close circle, restriction,
mute, and blocking only where the selected requirement IDs are independently
testable.

It does not claim recommendation ranking, direct messaging, meetings,
geolocation, minors, account-deletion orchestration, or advanced abuse
detection. Those remain explicit later phases rather than hidden TODOs inside
the foundation.

## Main Risks

- **Identity duplication:** mitigated by an adapter with one unique row per
  authoritative profile, not a second public profile.
- **Cross-account consent:** symmetric edges are created only from an accepted
  request under a transaction and row lock.
- **Block bypass:** the foundation enforces an actor-to-actor block before
  requests, acceptance, directory results, and graph projections. Account-wide
  expansion across every managed profile remains an explicit safety gap.
- **Race conditions:** active keys and idempotency keys are unique; Actions
  re-read locked status before transition.
- **Privacy leakage:** current lists/counts are available only to an authorized
  actor manager and no exact location, medical, ownership, or manager data
  enters the social aggregate. Public viewer-aware graph lists remain open.
- **Legacy loss:** prototype state stays encrypted and intact until a separate
  reviewed backfill can prove both endpoints.
