# Pet Profile Identifying Marks Work Package

Date: 2026-08-04

Status: implemented, release-verified, and ready for publication.

## Requirement Boundary

This package selects exactly these 17 atomic requirements:

- `pet.identity.0246-pet.identity.0256`;
- `pet.identity.0258`, `pet.identity.0260`, `pet.identity.0261`,
  `pet.identity.0263`, `pet.identity.0267`, and `pet.identity.0269`.

The section and explanatory prompt records `pet.identity.0244`,
`pet.identity.0245`, `pet.identity.0257`, `pet.identity.0259`,
`pet.identity.0266`, and `pet.identity.0268` remain open. Friend-only access in
`pet.identity.0262`, clinic access in `pet.identity.0264`, and active-search
access in `pet.identity.0265` also remain open until those consumers have
their own authoritative relationship, grant, or search-case checks.

## Data Contract

`pet_profile_identifying_marks` is a bounded normalized child of the canonical
pet profile. Each row has a globally unique stable key, controlled feature
type, encrypted bounded description, controlled visibility, deterministic
position, author/updater attribution, and nullable retirement time. The ten
feature types cover scars, spots, ear and paw features, different eye colors,
shortened tails, tattoos, unusual patterns, visible structural differences,
and visible effects of old injuries.

This package exposes only two visibility states because both are completely
enforced end to end: public profile and private verification evidence. The
historical encrypted `profile_data.identifying_marks` free text remains a
manager-only compatibility value; it is neither copied nor guessed into the
new relation. Removing a structured mark retires it instead of destroying the
stored proof.

## Mutation And Authorization

`PetIdentifyingMarkNormalizer` is the reusable server boundary for progressive
and direct Action calls. It validates list shape and the twelve-item bound,
controlled type and visibility, non-empty 500-character descriptions, unique
row IDs, and ownership of every referenced row. A foreign or retired row ID is
rejected before any write.

`PetIdentifyingMarkSynchronizer` compares the loaded bounded relation in
memory, encrypts normalized descriptions through the model cast, retires stale
rows with one scoped update, and writes active rows with one upsert. It never
queries inside the item loop. `UpdatePetProfileStep` retains its existing
policy, optimistic lock, replay idempotency, lifecycle event, ordinary audit,
cache invalidation, and compatibility behavior when the new key is omitted.

## Interface, Localization, And Privacy

The class-based Livewire Appearance step provides an add/remove list with
stable keys, labelled type and visibility selectors, bounded descriptions,
linked help and errors, manual save, autosave, loading, dirty, offline,
keyboard, focus, and 44-pixel touch behavior. EN, LT, and RU provide matching
catalogue, privacy, empty, help, public, and validation text.

`PetIdentifyingMarkPresenter` accepts a preloaded relation and prepares only
public localized scalar arrays. The public profile query fetches only active
public rows, then the presenter repeats the visibility filter as defense in
depth. Private verification descriptions never enter the public component
payload or rendered HTML. The manager workspace retains access through the
existing `PetProfilePolicy::update` decision.

## Query Delta

- The Appearance manager read adds one bounded eager-load query selecting only
  identifying-mark columns; completion uses an `EXISTS` subquery on the
  existing profile query and adds no round trip.
- The public profile adds one bounded eager-load query already scoped to the
  owning pet, public visibility, active rows, and deterministic order.
- A changed save adds one relation read, one scoped retirement update, and one
  bulk upsert regardless of whether one or twelve marks are submitted. A no-op
  emits no mark write.
- `(pet_profile_id, retired_at, position, id)` supports the manager relation;
  `(pet_profile_id, visibility, retired_at, position, id)` supports the public
  projection. Actor foreign keys have explicit indexes.
- Blade receives prepared arrays and performs no query, authorization choice,
  encryption, or enum interpretation.

## Test And Release Gates

Observed on the exact isolated release tree:

1. the focused package passed 17 tests and 94 assertions;
2. identifying marks, appearance, body covering, and progressive completion
   passed 85 tests and 465 assertions;
3. the complete pet-profile regression passed 196 tests and 5,687 assertions;
4. architecture, localization, page identity, responsive behavior, and the
   focused package passed 94 tests and 67,847 assertions;
5. the complete sequential repository suite passed 2,998 tests and 102,330
   assertions in 181.707 seconds with a 1 GB PHP limit;
6. full Pint and Larastan, Composer validation/audit/platform, npm audit,
   JavaScript syntax, Vite 8.2 build, and all cache compilation passed;
7. fresh SQLite applied 138 migrations and 219 tables, retained five users and
   three pets through repeat seeding, and passed relation rollback/reapply;
8. connected Chrome persisted both visibility modes through Livewire and
   passed public privacy plus 1440px, 390px, and 320px accessibility/geometry
   checks with no console errors; final screenshots were visually reviewed;
9. the immutable 38,377-record source and deterministic generated requirement
   outputs passed, with exactly the selected 17 IDs verified.

The reusable browser command is
`BROWSER_BASE_URL=http://127.0.0.1:PORT BROWSER_ALLOW_DATA_MUTATION=1 node scripts/pet-workspace-browser-check.mjs --marks`.

## Remaining Boundaries

- Friends, clinic, and active lost/found-search audiences remain unavailable;
  the interface does not offer a visibility choice whose authorization path is
  not yet implemented.
- A private verification mark is identity evidence supplied by a manager. It
  is not medical verification, legal ownership proof, a registry result, or a
  substitute for protected documents.
- Lost/found description generation, adoption/dispute workflows, documentary
  comparison, moderation, exports, measurements, weight privacy, and identity
  media remain separate packages.
