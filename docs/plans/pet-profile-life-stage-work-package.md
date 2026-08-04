# Pet Profile Life Stage Work Package

Date: 2026-08-04

Status: implemented, release-verified, and published.

## Requirement Boundary

This package selects exactly these 11 atomic requirements:

- `pet.identity.0189-pet.identity.0196`;
- `pet.identity.0198`, `pet.identity.0200`, and `pet.identity.0204`.

The section heading and explanatory prompts `pet.identity.0187`,
`pet.identity.0188`, `pet.identity.0197`, `pet.identity.0199`,
`pet.identity.0201`, `pet.identity.0202`, and `pet.identity.0203` remain open.
Appearance, measurements, medical verification, taxonomy ingestion,
recommendations, discovery filters, ownership, and lost/found behavior remain
separate work packages. In particular, `pet.identity.0202` cannot be verified
until recommendations and filters actually consume the stage.

## Data Contract

`PetLifeStage` is the controlled generic value: newborn, juvenile, young,
adult, senior, or unknown. The automatic value is never persisted. A nullable
`life_stage_override` records only an authorized clarification, while
`life_stage_override_by_user_id` and `life_stage_override_at` retain its actor
and observation time. Removing an override restores automatic calculation and
clears those two provenance fields.

The additive migration is reversible and adds one indexed nullable actor
foreign key. It neither rewrites existing pets nor changes their stable keys,
manager relationships, privacy, lifecycle, medical records, care journals, or
other domain links.

## Resolution Rules

`PetLifeStageResolver` uses the existing advancing age range and a controlled
animal-group threshold catalogue. Dog, cat, bird, rabbit, rodent, fish,
reptile, and horse groups have independent boundaries; no dog fallback is
applied to another group. Exact and bounded age information produces a stage
only when both ends of the range belong to the same category.

Possible or unidentified species, unknown age, unsupported animal groups, and
age ranges that cross a threshold resolve to unknown. An authorized manual
override wins but is labelled as a profile-manager clarification, not a
medical or documentary verification. The current broad-group catalogue is a
presentation default; exact taxon and breed-specific clinical guidance is not
inferred.

## Mutation And Authorization

`PetLifeStageOverrideNormalizer` is the reusable server boundary for the
progressive Age and sex step and the durable compatibility update Action. It
rejects unknown browser values even when a caller invokes the Action directly,
keeps no-op submissions mutation-free, and preserves the original actor/time
when the selected override is unchanged.

The existing `PetProfilePolicy::update` permission remains authoritative. A
primary owner or another manager may clarify the value only when their active
membership grants `edit-basics`; a specialist therefore needs the same
explicit permission override. Every accepted change keeps optimistic locking,
idempotent lifecycle evidence, ordinary audit, and profile-cache invalidation.

## Interface, Localization, And Privacy

The class-based Livewire Age and sex form adds one labelled select with an
automatic choice and six controlled manual values. It retains linked help and
errors, native-change autosave, manual save, loading, dirty, offline, and
keyboard behavior. The workspace shows the resolved label and its source.

`PetLifeStagePresenter` prepares all Blade data. EN, LT, and RU contain matching
generic, dog, cat, bird, reptile, horse, source, help, field, and validation
labels. Public profiles show the stage and whether it is automatic, manually
clarified, or unavailable; they never expose the actor identifier or timestamp.

## Query Delta

- Existing profile reads retain the same query count and select three
  additional nullable scalar columns.
- Age-to-stage resolution, source selection, and localization perform zero
  database queries.
- The nullable actor foreign key has a composite index for referential and
  maintenance access; stage values are not filtered or sorted in this package.
- Blade receives prepared scalar arrays and performs no query, aggregate, or
  business calculation.

## Test And Release Gates

The focused Pest package covers schema, enum casts, group-specific boundaries,
uncertain inputs, automatic advancement, zero-query presentation, authorized
specialist clarification, clearing, invalid direct Action input, unauthorized
mutation, legacy Action normalization, idempotency, no-op provenance, and
localized workspace/public output.

Observed on the final attributable tree:

1. the focused package passed 17 tests and 67 assertions; the affected
   regression passed 80 tests and 4,049 assertions; the complete pet-profile
   regression passed 138 tests and 4,445 assertions;
2. the final isolated sequential repository suite passed 2,903 tests and
   94,998 assertions in 170.033 seconds;
3. full Pint and Larastan passed with no findings;
4. fresh disposable SQLite applied 137 migrations and retained 218 tables,
   three pet profiles, and five users through rollback/reapplication and two
   complete seed runs;
5. Composer strict validation, locked security audit, PHP 8.5 platform
   requirements, npm audit with zero vulnerabilities, and the Vite 8.2
   production build passed;
6. isolated config, event, route, and view cache compilation passed;
7. connected disposable-database Chrome saved and restored a manual Senior
   stage, projected it publicly without actor or time, then restored automatic
   calculation through Livewire. Desktop, 390px, and 320px audits found no
   overflow, raw keys, duplicate IDs, unnamed or undersized controls, privacy
   disclosure, or console errors, and the focused selector retained a visible
   keyboard-focus ring;
8. immutable-source, deterministic 38,377-requirement, generated matrix, and
   scoped staged-diff checks passed. Implementation commit `3c7db2e` was pushed
   to `origin/main`, and the evidence overlay verifies only the 11 completed
   IDs while leaving `pet.identity.0202` open.

## Remaining Boundaries

- Exact taxon, breed, clinical, or organization-owned stage catalogues remain
  open; the current map is a broad-group presentation default.
- Medical or documentary verification cannot be created by a manual profile
  override.
- Recommendations, filtering, events, care, devices, marketplace, adoption,
  lost/found, and reminders do not consume the stage until their own packages
  establish authorization and privacy behavior.
- Appearance, coat, marks, measurements, public-weight privacy, and identity
  photographs remain open under `pet.identity.0205-pet.identity.0351`.
