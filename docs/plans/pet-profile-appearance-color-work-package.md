# Pet Profile Appearance Color Work Package

Date: 2026-08-04

Status: implemented, release-verified, and published.

## Requirement Boundary

This package selects exactly these 12 atomic requirements:

- `pet.identity.0207-pet.identity.0215`;
- `pet.identity.0217`, `pet.identity.0219`, and `pet.identity.0221`.

The section and explanatory prompt records `pet.identity.0205`,
`pet.identity.0206`, `pet.identity.0216`, `pet.identity.0218`,
`pet.identity.0220`, and `pet.identity.0222` remain open. Automatic lost-pet
description generation in `pet.identity.0223` is not implemented by this
package. Coat type, private identifying marks, measurements, identity media,
lost/found coordination, discovery, and verification remain separate work.

## Data Contract

`PetAppearanceColor` is the controlled species-neutral color catalogue and
`PetAppearancePattern` contains spots, stripes, and gradient. The existing
encrypted `pet_profiles.profile_data` payload stores schema-versioned
appearance data: one optional primary color, up to four unique additional
colors, any unique controlled patterns, and bounded text for general color,
feather, scale, and seasonal clarification.

No migration or backfill is required. Existing free-text appearance summaries
and identifying marks remain readable and editable as compatibility values.
An empty structured submission removes only the nested `appearance` object;
it does not erase either legacy value or any adjacent profile data.

## Mutation And Authorization

`PetAppearanceNormalizer` is the reusable server boundary for the progressive
Appearance step and the durable compatibility Action. It rejects forged enum
values, duplicate selections, a primary color repeated as an additional color,
more than four additional colors, non-string values, and overlong text even
when an Action is invoked directly.

The existing `PetProfilePolicy::update` decision remains authoritative. The
Action retains optimistic locking, idempotent mutation evidence, ordinary
audit, and profile-cache invalidation. A no-op submission does not increment
the profile version or create a lifecycle event.

## Interface, Localization, And Privacy

The class-based Livewire Appearance step provides a labelled primary select,
large label-backed check targets for additional colors and patterns, and
bounded text areas for species-neutral, feather, scale, and seasonal detail.
The primary selection is removed from additional selections in the interactive
component and repeated server-side validation remains authoritative.

EN, LT, and RU contain matching catalogue, field, help, validation, status, and
public-projection text. `PetAppearancePresenter` prepares localized scalar
values and locale-aware lists before Blade renders them. Public profiles show
only the structured visible color description. The existing identifying-marks
value stays manager-only and is never copied into this projection.

## Query Delta

- Profile reads keep the same query count and select no additional column.
- Normalization, localization, list formatting, completion detection, and
  public projection perform zero database queries.
- The encrypted JSON-compatible payload is not filtered, joined, or ordered in
  this package, so no index is required.
- Blade receives prepared scalar arrays and performs no query, aggregate, or
  business calculation.

## Test And Release Gates

The focused Pest package covers encrypted structured persistence, legacy-data
compatibility, forged direct Action payloads, selection bounds and uniqueness,
authorization, idempotency, no-op behavior, localized Livewire restoration,
public projection, identifying-mark privacy, completion detection, and zero-
query presentation.

Observed on the final attributable tree:

1. the focused package passed 15 tests and 108 assertions; the focused package
   plus architecture passed 35 tests and 30,944 assertions; the complete
   pet-profile regression passed 153 tests and 4,829 assertions;
2. the clean final sequential repository suite passed 2,923 tests and 96,639
   assertions in 169.946 seconds with a 1 GB PHP limit for the existing
   high-resolution public-image fixture;
3. full Pint and Larastan passed with no findings;
4. fresh disposable SQLite applied 137 migrations, retained 218 tables and
   five stable users, and passed a second complete seed;
5. Composer strict validation, locked security audit, PHP platform
   requirements, npm audit with zero vulnerabilities, and the isolated Vite
   8.2 production build passed;
6. config, event, route, and view cache compilation passed, followed by
   explicit per-cache cleanup before the final suite;
7. connected disposable-database Chrome saved and restored primary/additional
   colors, patterns, and clarification through Livewire, verified the localized
   public projection without identifying marks, and found no overflow, raw
   keys, duplicate IDs, unnamed or undersized targets, or console errors at
   1440px, 390px, or 320px. Final screenshots were visually reviewed after the
   shared compact-checkbox correction;
8. immutable-source, deterministic 38,377-requirement, PHP-localization,
   JavaScript-syntax, and scoped staged-diff checks passed. Implementation
   commit `61efa6f` was pushed to `origin/main`; the evidence overlay verifies
   exactly the 12 completed IDs while leaving `pet.identity.0223` open.

## Remaining Boundaries

- `pet.identity.0223` remains open: no lost/found report or alert automatically
  consumes the description yet.
- Coat structure, private marks, measurements, weight privacy, taxonomy-backed
  species rules, documentary verification, and identity photographs remain
  open under `pet.identity.0224-pet.identity.0351`.
- Search, recommendations, events, care, medical, devices, marketplace,
  adoption, moderation, and analytics do not consume appearance until their
  own packages define authorization, privacy, and invalidation behavior.
