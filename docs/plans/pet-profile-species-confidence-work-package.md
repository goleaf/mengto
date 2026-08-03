# Pet Profile Species Confidence Work Package

Last updated: 2026-08-03.

Status: implemented, release-verified, and published to `origin/main`.

## Scope

This dependency-safe package completes the honest incomplete-species section
`pet.creation.0170-pet.creation.0186`. It does not create a temporary found-pet
workflow, ownership transfer, adoption handoff, proof process, or taxonomy
verification claim.

The stored broad species remains one controlled value such as `cat`, `dog`, or
`unknown`. A separate `species_confidence` enum stores `confirmed`, `possible`,
or `unidentified`. Possible identification is accepted only for cat and dog;
unknown always normalizes to unidentified, while incompatible browser values
normalize to confirmed rather than creating a false possible category.

## Data And Action Contract

The additive migration defaults legacy identified rows to confirmed and uses
one bounded Eloquent update to mark existing `unknown` rows as unidentified.
It changes no pet key, ownership, lifecycle, taxonomy, medical, care, device,
search, adoption, or social relationship.

Create, generic update, and progressive basics Actions normalize confidence at
the server boundary. The enum cast keeps application state typed. Creation and
step events record the changed field without exposing a private fact. A manager
or specialist with existing update permission may later correct the broad
species or its confidence without replacing the canonical profile.

## Interface And Projection

The canonical create and basics forms expose one linked, localized confidence
control. Cat and dog offer identified or possible; unknown offers only not
identified; other controlled groups offer identified. The create species
control uses a justified Livewire live update so the compatible choices change
before submission. The existing progressive form refreshes the choices in the
same server round trip as its change-triggered save.

`PetSpeciesLabel` is the reusable projection boundary. Workspace, public
profile, duplicate card, and invitation presenters select the new column and
render `Possibly: Cat/Dog` instead of silently presenting the guess as a fact.
Downstream medical, care, search, and marketplace records continue receiving
the normalized broad species and do not receive a new invented species value.

## Query Delta

There is no new runtime query. Existing select lists gain one scalar column.
The migration adds one bounded set update for legacy `unknown` profiles. No
filter or ordering uses `species_confidence`, so no new index is required.
The existing indexed broad-species duplicate query remains unchanged.

## Verification

- focused species-confidence suite: 6 tests and 38 assertions;
- related pet, architecture, localization, page-identity, responsive, and
  schema regression: 164 tests and 65,953 assertions;
- migration/factory/seeder verification: 1,735 tests and 5,150 assertions;
- final sequential repository suite: 2,757 tests and 87,427 assertions in
  161.849 seconds;
- full Pint and Larastan: passed with zero findings;
- Composer strict validation/audit, PHP 8.5 platform requirements, npm audit
  with zero vulnerabilities, Vite 8.2.0 build, and config/event/route/view
  cache compilation: passed;
- fresh disposable SQLite applied 133 migrations, retained 216 tables, and
  completed the seed; the migration lifecycle and factory/seeder suites passed;
- Laravel schema inspection confirmed the non-null 24-character column and
  existing broad-species indexes;
- disposable-database Chrome exercised the real possible-dog control,
  duplicate review, access request, and manager review with zero overflow,
  raw keys, duplicate IDs, undersized or unnamed controls, credential/private
  candidate leaks, or console errors at desktop, 375px, and 320px.
- scoped implementation commit `90eab92` passed `git diff --cached --check`;
  the observed push advanced `origin/main` from `128b8ae` to `90eab92`.

## Remaining Boundaries

Adoption transfer `pet.creation.0138-pet.creation.0156` depends on the protected
ownership workflow. Temporary found-animal creation
`pet.creation.0157-pet.creation.0169` depends on the lost/found aggregate.
Species confidence does not verify taxonomy, breed, sex, age, ownership, or
professional status. Those requirements retain their existing open state.
