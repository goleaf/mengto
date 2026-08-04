# Pet Profile Size Category Work Package

Date: 2026-08-04

Status: implemented and release-verified; ready for publication.

## Requirement Boundary

This package selects exactly these nine atomic requirements:

- `pet.identity.0272-pet.identity.0279`;
- `pet.identity.0283`.

The section and explanatory prompt records `pet.identity.0270`,
`pet.identity.0271`, `pet.identity.0280`, `pet.identity.0282`,
`pet.identity.0284`, and `pet.identity.0286` remain open. Cross-domain purpose
and result records `pet.identity.0281`, `pet.identity.0285`, and
`pet.identity.0287` also remain open because this package does not add a
transport, service, marketplace, event, search, place, product, or carrier
consumer.

## Data Contract

`pet_profiles.size_category` is nullable and cast to `PetSizeCategory`. The
seven controlled values are very small, small, medium, large, very large,
individual measurements needed, and not applicable to this species. `null`
means unrecorded; no migration, factory, species, breed, photograph, weight,
or legacy text invents a category.

The individual option means the general scale is insufficient and a future
consumer must use separate measurements. It does not store or imply a weight,
height, circumference, clinical assessment, medical fact, or compatibility
decision. The explicit `(size_category, status, id)` index provides the
profile-side primitive for a future bounded active-profile filter without
claiming that any consumer exists now.

## Mutation And Authorization

`PetSizeCategoryNormalizer` is the reusable server boundary for direct Action
input. It accepts only the controlled string values or an explicit empty
value, rejects arrays, booleans, and unknown values, and never guesses from
adjacent facts. `UpdatePetProfileStep` writes the scalar through the existing
Appearance transaction while retaining managed-profile scoping, policy
authorization, row and optimistic locking, replay idempotency, lifecycle and
ordinary audit evidence, cache invalidation, no-op behavior, and compatibility
when the key is omitted.

## Interface, Localization, And Public Projection

The class-based Livewire Appearance step exposes one labelled native select
with linked help and errors, a clear unrecorded state, measurement-boundary
guidance, manual save, autosave, loading, dirty, offline, keyboard, focus, and
44-pixel touch behavior. EN, LT, and RU provide matching labels, descriptions,
public notices, validation text, and all seven option labels.

`PetSizeCategoryPresenter` converts the already selected scalar into a
localized public-safe array and performs zero queries. The public profile
renders the broad category and its explanation as manager-reported context,
not a verified measurement or medical fact. Blade receives prepared values
and does not query, authorize, or interpret an enum.

## Query Delta

- Manager, Action, and public profile reads add one selected scalar column and
  zero database round trips.
- Enum options, completion detection, normalization, and public presentation
  add zero queries.
- A changed save uses the existing transaction and profile update; a no-op,
  omitted value, or idempotent replay performs no additional profile write.
- The composite index supports a future size/status/keyset predicate. No
  marketplace, place, event, service, product, carrier, recommendation, or
  search query is added by this package.

## Test And Release Gates

Observed on the isolated release tree:

1. the focused package passed 22 tests and 85 assertions;
2. size, appearance, body covering, identifying marks, and progressive
   completion passed 107 tests and 550 assertions;
3. the complete pet-profile regression passed 218 tests and 5,910 assertions;
4. architecture, localization, page identity, responsive behavior, and the
   focused package passed 99 tests and 68,534 assertions;
5. the complete sequential repository suite passed 3,045 tests and 103,962
   assertions in 173.604 seconds with a 1 GB PHP limit;
6. full Pint and Larastan, Composer strict validation/audit/platform, npm audit
   with zero vulnerabilities, JavaScript syntax, Vite 8.2 build, and config,
   event, route, and view cache compilation passed;
7. fresh SQLite applied 139 migrations and retained 219 tables, five users,
   and three pets through repeat seeding; rollback/reapply retained all pets
   and removed/restored only the new column and index;
8. connected Chrome saved and restored the individual-measurements category,
   verified its public explanation, and passed 1440px, 390px, and 320px
   geometry, translation-key, duplicate-ID, 44-pixel target, and console checks;
   final screenshots were visually reviewed;
9. the immutable 38,377-record source and deterministic generated requirement
   outputs passed, with exactly the selected nine IDs verified.

The reusable browser command is
`BROWSER_BASE_URL=http://127.0.0.1:PORT BROWSER_ALLOW_DATA_MUTATION=1 node scripts/pet-workspace-browser-check.mjs --size`.

## Remaining Boundaries

- Actual weight, height, body length, chest, neck, back, muzzle, wingspan,
  shell, and other species-specific measurements remain
  `pet.identity.0288-pet.identity.0308` and later work.
- Public, household, and clinical weight privacy remains
  `pet.identity.0309-pet.identity.0324` and is not inferred here.
- Compatibility rules need their own consumer-owned constraints, units,
  authorization, explanation, override, test, and index review before any
  place, product, service, event, transport, or search result is filtered.
