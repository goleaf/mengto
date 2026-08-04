# Pet Profile Species-Aware Body Covering Work Package

Date: 2026-08-04

Status: implemented and release-verified.

## Requirement Boundary

This package selects exactly these 11 atomic requirements:

- `pet.identity.0226-pet.identity.0235`;
- `pet.identity.0237`.

The section and explanatory prompt records `pet.identity.0224`,
`pet.identity.0225`, `pet.identity.0236`, `pet.identity.0238`,
`pet.identity.0240`, and `pet.identity.0242` remain open. Search and
recommendation consumption in `pet.identity.0239`, care guidance in
`pet.identity.0241`, and groomer, shelter, or finder consumption in
`pet.identity.0243` are not implemented by this package. Private-mark
modelling, measurements, identity media, taxonomy verification, and medical
facts remain separate work.

## Data Contract

`PetBodyCoveringSchema` maps the existing broad species value to relevant
coat, feather, scale, skin, mane, and seasonal-shedding controls. Controlled
enums represent coat length and texture, undercoat, feather type, mane type,
and seasonal shedding. Hairlessness is a separate boolean. A bounded skin
observation remains private to profile managers.

The schema-versioned `body_covering` object lives in the existing encrypted
`pet_profiles.profile_data` payload. It does not replace the adjacent
structured color object or legacy appearance summary. Reptiles and fish reuse
the existing bounded scale-color clarification instead of storing a duplicate
field. No migration, backfill, or index is required.

## Mutation And Authorization

`PetBodyCoveringNormalizer` is the reusable server boundary for both the
progressive Appearance step and the durable compatibility Action. It rejects
forged enum or boolean values, overlong skin text, and hairless submissions
that also claim a coat length, texture, or undercoat. It removes fields that do
not apply to the current species before persistence.

The existing `PetProfilePolicy::update` decision remains authoritative. The
Action retains optimistic locking, replay idempotency, lifecycle and audit
evidence, and profile-cache invalidation. A compatibility caller that omits
all new keys leaves the existing nested object unchanged.

## Interface, Localization, And Privacy

The class-based Livewire Appearance step derives a small boolean presentation
schema from the server-owned species. Dog and cat profiles receive coat
controls, birds receive feather controls, fish and reptiles receive scale
coloring, horses receive mane controls, and broad exotic or other profiles may
receive the wider descriptive set. Selecting hairless clears and hides
contradictory coat controls, while server validation remains authoritative.

EN, LT, and RU provide matching field, option, help, validation, status, and
public-projection text. Controls retain linked labels and errors, visible
focus, loading, dirty, offline, keyboard, 44-pixel touch-target, and compact
mobile behavior. `PetBodyCoveringPresenter` prepares public localized scalar
values before Blade. It never includes the private skin observation.

## Query Delta

- Profile reads keep the same query count and select no additional column.
- Schema selection, normalization, completion detection, localization, and
  public presentation perform zero database queries.
- The encrypted nested payload is not filtered, joined, or ordered, so this
  package requires no database index.
- Blade receives prepared booleans and scalar arrays and performs no query,
  aggregate, or business calculation.

## Test And Release Gates

The focused Pest package covers encrypted structured persistence, adjacent
appearance compatibility, forged direct Action input, species pruning,
hairless contradictions, authorization, idempotency, strict real-route
rendering, EN/LT/RU restoration, public projection, skin privacy, malformed
legacy values, completion detection, and zero-query presentation.

Observed on the final attributable tree:

1. the focused package passed 26 tests and 104 assertions; the body-covering,
   appearance, and progressive-completion regression passed 68 tests and 371
   assertions; the complete pet-profile regression passed 179 tests and 5,377
   assertions;
2. the architecture, localization, page-identity, responsive, and focused
   package slice passed 103 tests and 67,659 assertions;
3. the exact committed tree passed the complete sequential repository suite:
   2,960 tests and 98,960 assertions in 195.350 seconds with a 1 GB PHP limit;
4. full Pint and Larastan passed without findings; Composer strict validation,
   locked audit, PHP 8.5 platform requirements, npm audit with zero
   vulnerabilities, JavaScript syntax, and isolated Vite 8.2 production build
   passed;
5. disposable SQLite applied 137 migrations, retained 218 tables, five users,
   and three pet profiles, and repeated complete seeding without count drift;
6. isolated config, event, route, and view cache compilation passed;
7. connected disposable-database Chrome saved and restored coat length,
   texture, undercoat, shedding, and a private skin note through Livewire;
8. the same browser run projected only public localized values and verified
   that the private skin note is absent;
9. desktop, 390-pixel, and 320-pixel checks reported one page heading and form,
   no overflow, raw keys, duplicate IDs, unnamed controls, undersized mobile
   targets, missing privacy disclosure, or console errors; final screenshots
   were visually reviewed;
10. immutable-source, deterministic requirement generation, PHP and Blade
    localization, and scoped staged-diff checks passed.

The reusable browser command is
`BROWSER_BASE_URL=http://127.0.0.1:PORT BROWSER_ALLOW_DATA_MUTATION=1 node scripts/pet-workspace-browser-check.mjs --covering`.

## Remaining Boundaries

- `pet.identity.0239`, `pet.identity.0241`, and `pet.identity.0243` remain open
  until their own authorized consumers exist and are tested.
- The skin note is descriptive manager-only state, not a diagnosis, medical
  record, verification result, or public claim.
- Species selection uses the existing broad local catalogue. It is not taxon,
  breed, registry, shelter, veterinarian, or document-backed classification.
- Private identifying marks, measurements, weight privacy, identity media,
  lost/found automation, search, recommendations, care, and external
  specialist workflows remain separate packages.
