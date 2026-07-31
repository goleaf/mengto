# Social Relationships Existing System Audit

Date: 2026-07-31

## Scope And Evidence

This audit covers the additive social-relationships revision preserved from
local source timestamp `1785521058`. Its raw SHA-256 is
`5455fc185c1348ac7233d35ec18285b850c19e0bb28cbda2dc90eeb87bc6276d`.
The combined immutable master checksum is
`ad88d55de0faf7d5fe62c97479be42f6539316a13eeae9d2bbfd8a6b3716c32d`.
The current worktree, migrations, models, policies, routes, state services,
controllers, Livewire components, translations, factories, and tests were
inspected before production implementation of this revision.

## Existing Social State

`UserDomainState` and `PersistentStateStore` provide encrypted, versioned,
per-user JSON state. `PetFriendState`, `PerformPetFriendAction`, and
`PerformConnectionAction` use that store to power the existing connection and
pet-friend previews. The storage is useful for preserving a user's prototype
choices, but it cannot establish a canonical cross-account relationship:

- the other party does not accept the same durable row;
- demo catalogue keys may not resolve to a real account or pet;
- database constraints cannot prevent duplicate or contradictory edges;
- block, request, expiry, actor, and audit rules are not shared globally;
- queryable follower/friend lists and viewer-aware counts do not exist.

The two existing design specifications explicitly describe these screens as
MVP previews and defer an Eloquent social graph, production policies,
anti-abuse controls, counters, and recommendation infrastructure.

## Existing Identities And Authority

The repository already has authoritative `User`, `PetProfile`,
`ExpertProfile`, `ForumGroup`, and event records. `PetProfile` now supports
timed manager roles and explicit permissions. A social subsystem must point to
those records and must never make a social edge equivalent to ownership,
family membership, professional access, medical access, or device control.

Every action performed from a pet or expert profile must retain the real
authenticated `User` actor. Pet-profile authority is resolved by
`PetProfilePolicy` and `PetProfileAccess`; it may not be inferred from browser
state or a social relationship.

## Existing Interfaces And Constraints

- `/circle/connections` and `/circle/pet-friends` are server-rendered preview
  routes with generic action submissions.
- The application uses Blade and class-based Livewire 4; it has no Filament,
  React, Vue, Inertia, Flux, websocket, or external search dependency.
- EN/LT/RU catalogues, reusable Blade components, notification patterns, and
  mobile/desktop layouts already exist.
- Current moderation, groups, events, profiles, and pet-profile modules are
  reusable authorities and may not be copied into social payloads.

## Query And Schema Baseline

No durable social actor, relationship, relationship request, actor setting, or
relationship event table exists. Consequently there is no database query delta
to measure against a production social graph. The first package must add
foreign keys and indexes for actor resolution, endpoint/type/status lists,
active-edge uniqueness, request inboxes, expiry processing, and append-only
audit retrieval. No raw SQL or unbounded model query is needed.

## Preservation Baseline

The legacy encrypted namespaces remain readable and are not deleted or
silently converted. Their demo keys are not reliable proof of another party's
consent, so automatic friendship import is unsafe. A bounded compatibility
report may resolve exact canonical keys and leave every ambiguous item in the
legacy store for explicit review.
