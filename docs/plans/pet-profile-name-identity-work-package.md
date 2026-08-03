# Pet Profile Name Identity Work Package

Date: 2026-08-03

Status: implemented, release-verified, and published.

## Requirement Boundary

This package verifies exactly these 20 atomic requirements:

- `pet.identity.0003`, `pet.identity.0007`, `pet.identity.0009`;
- `pet.identity.0014-pet.identity.0020`;
- `pet.identity.0022`, `pet.identity.0024`, `pet.identity.0025`;
- `pet.identity.0027`, `pet.identity.0029`, `pet.identity.0032`;
- `pet.identity.0034`, `pet.identity.0036`, `pet.identity.0038`, and
  `pet.identity.0040`.

The package does not verify the section headers or the cross-domain consistency
requirements `pet.identity.0005` and `pet.identity.0011`. Chat, events,
medical records, care journals, notifications, exports, moderation history,
and every other historical projection still need an explicit name-change
contract before those two requirements can advance.

## Delivered Contract

`pet_profiles.name` remains the current canonical display name. The additive
`pet_profile_names` table stores typed alternative names and historical names
without changing the profile key, slug, foreign keys, or adjacent-domain
links. Its supported purposes are nickname, previous name, shelter name,
official name, localized name, and responds-to name.

Every alternative has:

- a normalized comparison/search value;
- private, manager, or public visibility;
- an optional locale for localized names;
- an explicit searchable flag;
- recorder attribution and recorded time;
- a reversible soft-delete boundary.

The database protects per-profile normalized uniqueness. Search, profile
projection, and recorder lookup have explicit indexes. The migration is
additive and its rollback removes only the new table.

## Mutation And History

`AddPetProfileName` and `RemovePetProfileName` are the only explicit alias
mutation operations. They authorize against the freshly locked profile,
validate the requested purpose and visibility, maintain optimistic profile
versioning, record lifecycle and ordinary audit evidence, and invalidate the
profile cache. A private name can be removed only by the account that recorded
it.

Both canonical profile-update Actions preserve the former current name as a
previous name whenever a real rename succeeds. A newly created history entry
is private. An active alternative that already represented that same name is
reclassified as previous while retaining its explicit visibility; a previously
removed entry is restored privately. The stable profile key, canonical route,
slug aliases, media, care, medical, device, social, and history relationships
are unchanged. Replays and no-op updates do not create duplicate history.

## Validation And Privacy

`ValidPetProfileName` accepts international letters, combining marks,
numbers, spaces, and ordinary name punctuation. It rejects control
characters, emoji-only values, excessive repeated-character spam, and exact
reserved system identities. All browser input is validated again by the form
and Action boundaries.

Private alternatives are visible and searchable only to their recorder.
Manager alternatives are visible to currently authorized managers. Public
alternatives are the only alternatives projected to the public profile.
Manager/workspace search never broadens the viewer's existing profile scope;
it applies the same name visibility predicate inside the existing policy-
scoped query.

## Interface And Localization

The Basics step now renders current-name history and an accessible alternative
name editor. It offers localized EN, LT, and RU purpose/visibility choices,
linked help and error text, a conditional locale field, keyboard-operable
controls, loading state, and mobile touch targets of at least 44 pixels.

The public profile shows only public alternatives. The central pet workspace
can resolve an accessible old or alternative name while continuing to render
the current canonical profile card.

## Query Delta

- The ordinary pet workspace query count is unchanged. A non-empty name search
  adds one indexed correlated `EXISTS` predicate to the existing query, not a
  second round trip.
- The manager Basics step adds one bounded eager-load query for viewer-visible
  alternative names.
- The public profile adds one bounded eager-load query for public alternative
  names.
- Rendering a list of names performs no query inside a Blade loop and does not
  grow queries per name.

## Verification Evidence

Observed on PHP 8.5.8, Laravel 13.23.0, Livewire 4.3.4, Pest 4.7.5, and SQLite:

- `PetProfileNameIdentityTest`: 9 tests and 61 assertions;
- related pet, architecture, localization, page-identity, responsive, and
  schema regression: 139 tests and 66,717 assertions;
- complete sequential repository suite: 2,801 tests and 89,231 assertions in
  162.266 seconds;
- full Pint and Larastan: passed, with zero static-analysis errors;
- Composer strict validation, locked dependency audit, and PHP platform check:
  passed with no vulnerability advisory;
- npm audit and Vite 8.2 production build: passed with zero vulnerability;
- config, event, route, and view cache compilation: passed;
- disposable SQLite: 134 migrations and 217 tables, with successful fresh and
  repeated seed and five retained demo users;
- forum source checksum and deterministic 38,377-requirement generation:
  passed;
- disposable-database Chrome: a real manager alias mutation, public projection,
  and old-name workspace search passed at desktop, 375px, and 320px with zero
  overflow, raw keys, duplicate IDs, undersized or unnamed mobile controls,
  private-name leaks, or console errors.

The implementation commits are `7d68280` and the history-edge correction
`9a9eaf8`; the release evidence commit is `eccfbb4`. All three are published on
`origin/main`.

## Remaining Boundaries

- Cross-domain rename projections and historical text snapshots remain open.
- Public/global discovery by alternative name is intentionally not introduced.
- Duplicate merge, ownership proof/transfer, verified taxonomy/breed,
  found-animal coordination, abuse controls, and moderation workflows retain
  their own dependency-bound packages.
- Name deletion retention and export/redaction semantics need their later
  privacy and lifecycle decisions.
