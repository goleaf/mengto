# Pet Profile Breed Origin Work Package

Date: 2026-08-04

Status: implemented, release-verified, and published.

## Requirement Boundary

This package implements exactly these 35 atomic requirements:

- breed description and confidence: `pet.identity.0072`,
  `pet.identity.0074`, `pet.identity.0076-pet.identity.0083`,
  `pet.identity.0085`, and `pet.identity.0087`;
- mixed ancestry: `pet.identity.0090`, `pet.identity.0092`,
  `pet.identity.0094-pet.identity.0099`, `pet.identity.0101`, and
  `pet.identity.0103`;
- provenance: `pet.identity.0106-pet.identity.0113`,
  `pet.identity.0115`, `pet.identity.0117`, `pet.identity.0118`,
  `pet.identity.0120`, and `pet.identity.0122`.

The source headings and explanatory prompts between these statements remain
open. Taxonomy verification/import, medical conclusions, character or health
prediction, ownership, adoption, lost/found coordination, shows, group
recommendations, and organization attribution are separate work packages.

## Data Contract

`PetBreedOriginType` distinguishes one known breed, mixed origin, several
possible breeds, no breed, and unknown. Up to four ordered
`PetProfileBreedOrigin` rows retain either an active breed-classification key
or an owner-entered name together with independent confidence, source, and an
optional mixed-origin percentage. `PetBreedConfidence` distinguishes
confirmed/documented, owner-reported, and suspected information.

`PetBreedSource` distinguishes document, pedigree, shelter, veterinarian,
genetic test, owner assumption, and unknown. Source and confidence are stored
independently from the breed value. A photograph is never an input to this
contract and cannot promote reported or suspected ancestry to confirmed.

The additive migration keeps `pet_profiles.breed` as a bounded compatibility
snapshot for existing consumers, adds a nullable profile-level origin type,
and creates the normalized child table with uniqueness, ordering, lookup, and
classification indexes. A null type means legacy data has not yet been
reviewed; explicit `unknown` and `no-breed` remain distinct user choices. No
ambiguous legacy string is automatically matched to the taxonomy catalogue.

## Mutation And Compatibility Boundary

`PetBreedOriginNormalizer` is the reusable server-authoritative validation
boundary. It validates profile type and row-count invariants, active breed
classifications for the selected taxon, distinct entries, allowed confidence
and source values, percentages from 1 through 100, and a combined percentage
not above 100. It also creates the bounded legacy snapshot without changing
the normalized truth.

`PetBreedOriginSynchronizer` requires the relation to be eager loaded, accepts
only row keys already owned by the profile, deletes stale rows in one bounded
operation, and upserts the normalized set in one operation. Creation, generic
update, progressive update, autosave, and manual save reuse the normalizer and
synchronizer inside the existing short transactions, authorization,
optimistic locking, idempotency, lifecycle evidence, audit, and cache
invalidation boundaries.

Legacy clients that send only `breed` are mapped to one owner-reported entry
with owner-assumption provenance. Existing structured data is not downgraded
when an unrelated legacy update leaves the compatibility string unchanged.

## Interface, Localization, And Privacy

The Breed or origin step exposes the five overall types, up to four ordered
entries, separate confidence and source controls, and optional percentages
only for mixed origin. The user explicitly adds and removes entries; changing
the overall type cannot race autosave with an unfinished row. Empty mixed
composition remains valid and is described without forcing a false breed.

Every control has a stable label, help or error relationship, loading state,
keyboard path, and at least a 44-pixel target. EN, LT, and RU provide matching
labels, help, validation, trust, source, and non-discrimination text. The
public profile receives prepared display rows only when the profile itself is
publicly eligible; it shows the information level and source but does not
expose internal keys or infer character, health, or compatibility.

## Query Delta

- The Breed or origin management step adds one bounded eager-load query for
  the profile's normalized entries.
- Selecting a known taxon may add one bounded indexed catalogue query, capped
  at 200 active breed classifications; the currently empty local catalogue
  falls back to owner-entered names without weakening validation.
- The public profile adds one bounded eager-load query for origin rows and
  formats the prepared projection without further queries.
- Normalization loads all referenced classification IDs in one explicit-select
  query. Synchronization uses one stale-row delete and one upsert regardless
  of whether the payload contains one through four entries.
- Blade performs no query, aggregate, authorization decision, or provenance
  calculation.

## Verification Evidence

Observed on PHP 8.5.8, Laravel 13, Livewire 4.3.4, Pest 4.7.5, and SQLite:

- `PetProfileBreedOriginTest`: 7 tests and 55 assertions;
- focused pet-profile, progressive, foundation, and social regression: 55
  tests and 3,726 assertions;
- complete pet-profile regression: 121 tests and 4,144 assertions;
- complete clean-cache sequential repository suite: 2,867 tests and 93,321
  assertions in 199.794 seconds with a 1 GB PHP limit required by the existing
  3200 by 2000 lost/found image fixture;
- full Pint and Larastan, plus targeted `git diff --check`, passed with zero
  findings;
- Composer strict validation, locked audit, PHP platform requirements, npm
  audit with zero vulnerabilities, and the Vite 8.2 production build passed;
- a fresh disposable SQLite database applied all 136 migrations and completed
  the full deterministic seed; repeated seeding retained 218 tables, five
  users, three pet profiles, and the two browser-created origin rows;
- rollback and reapplication removed and restored only the new table/column
  while retaining all three seeded pet profiles; config, event, route, and
  view cache compilation passed in an isolated namespace;
- disposable-database Chrome changed the real Livewire origin type, added two
  entries, saved and reloaded separate confidence/source/percentage values,
  verified their public projection, and audited desktop, 390px, and 320px with
  zero overflow, raw translation keys, duplicate IDs, unnamed or undersized
  controls, privacy leaks, or console errors;
- the immutable source checksum and deterministic 38,377-requirement check
  passed before the evidence overlay update.

Implementation commit `3b38e2e` passed the scoped staged diff review and the
complete isolated release gates, then was pushed to `origin/main`. The final
generated evidence check passed after adding exactly 35 unique requirement
IDs.

## Remaining Boundaries

- Canonical breed catalogue ingestion, synonyms, translations, registry
  authority, and taxonomy verification remain open. The local catalogue was
  empty during implementation, so no guessed backfill is permitted.
- Documentary, pedigree, shelter, veterinary, or genetic verification needs a
  later protected evidence workflow; this package records the selected source
  but does not fabricate or publish private documents.
- Breed-based search, groups, shows, recommendations, analytics, and event
  behavior remain open and must preserve the explicit confidence level.
- Character, health, dangerousness, compatibility, value, or eligibility must
  never be inferred solely from a breed or mixed-origin label.
