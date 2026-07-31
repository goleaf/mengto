# Social Relationships Architecture Decisions

Date: 2026-07-31

## ADR-SOC-001: Canonical Actor Adapter

Use one `SocialActor` adapter per authoritative `User`, `PetProfile`,
`ExpertProfile`, or supported organization profile. The adapter is an internal
relationship endpoint with a stable key; it contains no independent public
identity, credentials, ownership, medical data, or profile biography.

This avoids polymorphic endpoint duplication while preserving the rule that
one real entity has one authoritative profile.

## ADR-SOC-002: Separate Requests From Relationships

`SocialRelationshipRequest` stores proposal lifecycle, message, context,
expiry, repeat limits, idempotency, and the real user actor. Acceptance creates
or reactivates a `SocialRelationship` in the same transaction. Declined,
cancelled, expired, hidden, blocked, and reported requests remain auditable.

## ADR-SOC-003: Typed Direction Semantics

Relationship type defines direction:

- directed: follow, close circle, restriction, mute, block, temporary or
  professional contact;
- symmetric: owner friendship, pet friendship, household acquaintance.

Symmetric active keys use a canonical endpoint order. Directed keys retain
source and target order. Different types may coexist between the same actors.

## ADR-SOC-004: Social Trust Never Grants Administration

Friend, follower, close-circle, or acquaintance status does not grant pet
management, medical, device, location, ownership, adoption, marketplace, or
professional permissions. Those remain within their authoritative modules and
policies.

## ADR-SOC-005: Actor Attribution Is Immutable Audit Data

Every request and mutation stores both the represented social actor and the
authenticated `User` who performed it. Append-only relationship events record
before/after status, reason, context, timestamp, and idempotency key.

## ADR-SOC-006: Privacy Is Checked At Read Time

Friend/follower lists, counts, requests, and relationship projections are
resolved through policies and scoped services on every request. The first
package exposes graph data only to an authorized manager; future public
projections must include viewer and visibility context in any cache key.
Security-sensitive transitions invalidate both endpoint namespaces
immediately.

## ADR-SOC-007: Compatibility State Is Not Consent

Existing `connections.state.v1` and `pet-friends.state.v1` payloads are kept as
legacy personal state. They are not automatically promoted to mutual
relationships because many endpoints are demo catalogue keys and the remote
party never consented.
