# Canonical Place Service Taxonomy Review

Date: 2026-08-30
Scope: stable service definitions and labels, place offerings and availability,
veterinary emergency capability, species/size eligibility, scope precedence,
unknown/unavailable semantics, deterministic seed keys, and category
non-inference. This is read-only discovery for `PLA-CF-01`; it does not cover
schedule/DST resolution, provenance privacy, accessibility, or migration
rollout.

## Status

`design recommended; current production path is not canonical`

The requirements demand canonical services and truthful emergency eligibility:
`PRD-PLACE-002/003` require service details and open species-capable emergency
results without guarantees (`docs/product-requirements.md:94-100`);
`PLA-02-005` requires service definitions/offerings with species, size,
availability, provenance, and status
(`docs/plans/places-production-master-plan.md:207-224`); and `PLA-15-004`
expressly forbids deriving capability from category
(`docs/plans/places-production-master-plan.md:703-720`). The active canonical
facts plan repeats that no category or missing row implies capability and that
readers must require a canonical veterinary service plus species capability
(`docs/implementation-plan.md:541-570`).

## Confirmed defects and gaps

### Critical — emergency capability is inferred from category

- A database-backed place with no service record is assigned
  `emergency = ($category === 'emergency-vet')` in
  `app/Services/PlaceCatalog.php:264-340`.
- Emergency mode forcibly sets category `emergency-vet`, open-now, and mode
  `emergency` (`app/Services/PlacePresenter.php:28-50`); matching then checks
  category membership plus the derived boolean, not a veterinary offering or
  capability (`app/Services/PlacePresenter.php:375-445`).
- A submission in that category is required to have only a phone and hours;
  neither a controlled emergency service definition nor species eligibility is
  required (`app/Actions/SubmitPlaceSubmission.php:327-334`). Submitted
  `facts.services.*` values are merely arbitrary distinct strings
  (`app/Actions/SubmitPlaceSubmission.php:263-270`).

This directly violates `PLA-15-004`. Category may narrow the candidate query,
but it can never establish emergency intake, triage, treatment, or species
capability.

### Critical — missing eligibility becomes invented positive support

- `PlaceCatalog` treats both null and an explicit empty `species_rules` array
  as permission to restore fixture species
  (`app/Services/PlaceCatalog.php:137-165`).
- A non-fixture canonical place with no species facts is assigned dogs and cats,
  and every supported size band, by default
  (`app/Services/PlaceCatalog.php:264-307`).
- `Place` persists only free-form JSON `species_rules`; there is no taxon or
  size relation (`database/migrations/2026_08_03_140000_create_places_table.php:13-58`,
  `app/Models/Place.php:74-168`).

Null/absent, explicitly empty, supported, and unsupported are therefore
collapsed. This is the exact invented-common-pet behavior forbidden by
`PLA-05-012` (`docs/plans/places-production-master-plan.md:379-388`).

### High — services and prices are fixture presentation, not definitions or offerings

- The current catalogue stores mixed localized display strings and raw English
  tokens in arrays. Veterinary examples include emergency triage, `x-ray`,
  ultrasound, laboratory, surgery, avian care, and inpatient care
  (`app/Services/PlaceCatalog.php:810-839`), while another clinic uses a
  different free-form subset (`app/Services/PlaceCatalog.php:841-906`).
- `PlaceDemoSeeder` persists category and free-form species only; it does not
  persist service definitions, offerings, sizes, or offering availability
  (`database/seeders/PlaceDemoSeeder.php:33-95`).
- The detail presenter derives labels with `Str::headline()` and pairs a
  service to the price at the same modulo position, so unrelated prices can be
  attached to services when list lengths differ
  (`app/Services/PlaceContentCatalog.php:116-135`). It also gives all commercial
  and veterinary fixture rows the same generic “availability may change”
  status rather than rendering a stored state.
- Existing `services` is an expert-profile booking aggregate, not a global
  place taxonomy: it requires `expert_profile_id` and owns duration, booking
  price, capacity, and availability slots
  (`database/migrations/2026_07_30_084910_create_services_table.php:14-41`,
  `app/Models/Service.php:44-95`). Reusing it would couple place facts to an
  unrelated owner/lifecycle.

### High — emergency tests prove fixture behavior, not the stated capability contract

- Every directory test seeds `PlaceDemoSeeder`
  (`tests/Feature/PlaceDirectoryTest.php:14-16`).
- The emergency test asserts that a fixture with category, hard-coded open
  state, hard-coded bird slug, and phone is returned
  (`tests/Feature/PlaceDirectoryTest.php:182-200`). It has no category-only
  counterexample, unavailable offering, missing capability, offering-level
  species mismatch, place-level-only species support, or unknown state.
- The submission test called “operational emergency facts” fails a category
  row for missing phone/hours but does not prove a canonical emergency service
  capability (`tests/Feature/Places/PlaceSubmissionPublicationWorkflowTest.php:1078-1109`).

The current green tests must not be treated as evidence that emergency
capability is canonical.

### Medium — species identity bypasses the global taxonomy

- Directory species filters and request validation use broad hard-coded slugs
  (`app/Services/PlaceCatalog.php:80-91`,
  `app/Http/Requests/BrowsePlacesRequest.php:39-40`).
- The repository already has stable global `Taxon` identities, active scope,
  names, and versions (`app/Models/Taxon.php:21-80,193-196`). Core seed keys
  include higher taxa such as Aves/Reptilia
  (`database/seeders/CoreAnimalTaxonomySeeder.php:195-204`) and exact companion
  taxa such as domestic dog, cat, rabbit, ferret, rodents, and birds
  (`database/seeders/CoreAnimalTaxonomySeeder.php:249-271`).
- `LocalizedTaxonName` already enforces verified current-locale/fallback common
  names before scientific-name fallback
  (`app/Services/LocalizedTaxonName.php:13-79`; canonical policy at
  `docs/localization.md:95-111`).

Creating another `dog|cat|bird|exotic` place-species enum would duplicate and
weaken the established taxonomy. “Exotic” is not a species identity and must
not be silently expanded to all animals.

### Medium — size filtering duplicates only part of the canonical enum

- Directory options duplicate five size strings
  (`app/Services/PlaceCatalog.php:94-107`; locale copies at
  `lang/{en,lt,ru}/place_directory.php:138-145`).
- `PetSizeCategory` already defines stable values and localized labels,
  including `individual` and `not-applicable`
  (`app/Enums/PetSizeCategory.php:7-25`). Null is deliberately “unrecorded,”
  not any-size support (`docs/data-model.md:211-215`; the enum cast is at
  `app/Models/PetProfile.php:116-131`).

The five comparable bands should be reused, while `individual` and
`not-applicable` must remain non-match outcomes rather than wildcard sizes.

### Medium — submission fact evidence is append-only but not a queryable service taxonomy

`PlaceFact` correctly retains immutable evidence
(`app/Models/PlaceFact.php:15-62`), but submission facts are flattened into
encrypted string values with free-form field keys
(`app/Actions/SubmitPlaceSubmission.php:452-502`; schema at
`database/migrations/2026_08_30_120000_create_place_submission_publication_tables.php:145-173`).
Those rows should remain provenance/history input; they cannot be the live
offering, species, size, or emergency eligibility projection.

## Recommended normalized taxonomy and relations

The schema specialist owns final columns and index names. The service-specific
logical model should be:

| Relation | Purpose and required identity |
| --- | --- |
| `place_service_definitions` | Global, language-independent reference rows. Unique immutable `stable_key`; stable `label_key` and optional `description_key`; controlled `domain`; deterministic `position`; active/retired lifecycle. No place, expert, price, or live availability fields. |
| `place_service_capabilities` | Small global semantic vocabulary used by evaluators, with unique immutable `stable_key`. The required emergency gate is `place-capability.veterinary.emergency-intake`. A capability is not a place category or label. |
| `place_service_definition_capability` | Unique `(place_service_definition_id, place_service_capability_id)` mapping. It lets one canonical definition satisfy an evaluator capability without matching display text. Seed-only/system-controlled. |
| `place_service_offerings` | One place’s statement that it offers one definition. Unique `(place_id, place_service_definition_id)` current projection plus its own stable key, controlled availability state, access mode, display position, and optional offering-specific public note. Price data belongs to a separately structured price representation, never parallel arrays or float. |
| `place_service_offering_taxon` | Offering-level species eligibility using a FK to global `taxa`, controlled `eligibility`, and `includes_descendants`. Unique `(place_service_offering_id, taxon_id)`. This is the only species relation that can prove emergency compatibility. |
| `place_service_offering_size` | Offering-level size eligibility using `PetSizeCategory` values and controlled `eligibility`. Unique `(place_service_offering_id, size_category)`. Permit only `very-small`, `small`, `medium`, `large`, `very-large`; reject `individual` and `not-applicable` as eligibility rows. |
| `place_taxon_eligibilities` | Optional general place-access species rules, FK-backed by global `taxa`; unique `(place_id, taxon_id)`. Useful for parks, shops, cafes, or general clinic access, but never proof that a particular offering supports the taxon. |
| `place_size_eligibilities` | Optional general place-access size rules, using the same five comparable `PetSizeCategory` values; unique `(place_id, size_category)`. Never inherited into an offering. |

Do not use one nullable-owner table or a polymorphic eligibility table: separate
place/offering tables preserve real foreign keys and portable uniqueness on
SQLite. Add reverse lookup indexes beginning with `taxon_id` / `size_category`
for directory and emergency queries, in addition to every owner-first unique
index. `Taxon` should gain explicit `placeServiceOfferings()` and optional
`places()` relations; `Place` should gain `serviceOfferings()`,
`taxonEligibilities()`, and `sizeEligibilities()`.

### Place-level versus offering-level support

These scopes are independent, not inherited:

- Place-level eligibility means the animal may generally access or be handled
  at the place. It does not say which service is available.
- Offering-level eligibility means the named service is documented for that
  taxon/size at that place.
- A place-level positive must never populate missing offering-level rows.
- An offering-level positive must never imply general access to unrelated
  areas or services.
- A positive/negative contradiction between the two scopes is a reviewable
  conflict. For emergency ranking it fails to the uncertainty tier; it is not
  resolved by “more positive wins.”
- Emergency species compatibility always requires an explicit positive on the
  emergency offering. General place eligibility is explanatory context only.

This prevents “clinic accepts birds” from becoming “clinic provides emergency
avian care,” and prevents an avian service from implying that every clinic
service supports birds.

## Controlled state semantics

Use two independent enums rather than overloading one status:

1. Offering lifecycle: `active`, `retired`.
2. Current availability knowledge: `available`, `temporarily_unavailable`,
   `unavailable`, `unknown`.

If appointment/call intake is needed, store it separately as access mode:
`walk_in`, `appointment_required`, `call_required`, `unknown`. “Available”
means the place currently represents that it offers the service; it never
promises real-time stock, clinician presence, admission, wait time, or outcome.

Eligibility rows use `eligible` or `ineligible`; absence means unknown.
Therefore:

| Stored condition | Meaning | Positive filter/eligibility behavior |
| --- | --- | --- |
| No offering row | Service not recorded; not evidence of absence | Never match as capable |
| Active offering + `unknown` | Service relationship recorded, present availability not established | Unknown candidate only; never “available” |
| Active offering + `available` | Service is affirmatively listed, subject to freshness/schedule evaluation elsewhere | May pass the service gate |
| `temporarily_unavailable` | Explicit current suspension while retaining the offering identity/history | Do not pass; show explicit temporary unavailability |
| `unavailable` | Explicit negative current fact | Do not pass; never convert to unknown or hide it as missing |
| `retired` | Historical offering no longer in the current projection | Never pass or appear as current |
| No taxon/size row | Eligibility unknown | Never claim a match |
| `eligible` row | Explicit support at that exact scope | Match only that scope and taxon expansion rule |
| `ineligible` row | Explicit exclusion at that exact scope | Never match; retain as an explicit negative fact |

An empty collection is “no recorded eligibility,” not “all.” “Any species” in
the UI means no species filter was selected; it must not create a wildcard
eligibility fact. If a place truly supports a higher taxon, store that real
Taxon (for example `taxon.core.aves`) with `includes_descendants = true` rather
than an `all`, `bird`, or `exotic` pseudo-taxon. Descendant matching must use
the active accepted taxonomy version and reject inactive/synonym rows unless
they are resolved to their accepted taxon.

## Veterinary emergency eligibility contract

The taxonomy portion of the eligibility evaluator should return a structured
result, not a boolean inferred by the presenter:

1. Start from an authorized active place; a `VeterinaryClinic` type may bound
   the query but does not prove capability.
2. Require an active offering whose definition maps to
   `place-capability.veterinary.emergency-intake`.
3. Require offering availability `available`. Unknown availability is an
   uncertainty candidate; unavailable/temporarily unavailable/retired fail.
4. When a taxon is selected, require an offering-level `eligible` relation for
   that exact accepted taxon or an ancestor relation explicitly marked
   `includes_descendants`. Missing support is `species_capability_unknown`;
   explicit `ineligible` is `species_not_supported`.
5. When a comparable size is selected, apply the same offering-level rule.
   A pet size of null, `individual`, or `not-applicable` yields
   `size_capability_unknown/not_applicable`, never a positive wildcard.
6. A scope contradiction, inactive taxon, or unresolved synonym cannot enter
   the confirmed-capable tier.
7. Pass the resulting reason codes to the separate schedule/ranker layer. That
   layer may rank confirmed-capable results and then uncertainty candidates,
   but it must not upgrade any taxonomy failure.

Suggested result codes are stable, language-independent values:
`capable`, `capability_unknown`, `capability_unavailable`,
`species_supported`, `species_capability_unknown`,
`species_not_supported`, `size_supported`, `size_capability_unknown`, and
`size_not_supported`. Labels/explanations belong to language files.

## Deterministic reference and demo seed keys

Use immutable semantic keys; update seeded rows by key so repeated seed retains
IDs. Never derive keys from localized labels. Recommended initial veterinary
definitions based on the current fixtures are:

- `place-service.veterinary.emergency-triage`
- `place-service.veterinary.general-consultation`
- `place-service.veterinary.vaccination`
- `place-service.veterinary.diagnostics.radiography`
- `place-service.veterinary.diagnostics.ultrasound`
- `place-service.veterinary.diagnostics.laboratory`
- `place-service.veterinary.surgery`
- `place-service.veterinary.inpatient-care`
- `place-service.veterinary.avian-care`
- `place-service.veterinary.dentistry`
- `place-service.veterinary.rehabilitation`

Map only `place-service.veterinary.emergency-triage` (and any later reviewed
equivalent) to `place-capability.veterinary.emergency-intake`. Diagnostics,
surgery, “24-hour,” a phone number, veterinary place type, or emergency place
category must not receive that mapping automatically.

Demo offering keys should be deterministic and place-qualified, for example:

- `place-offering.paws-24-veterinary-center.emergency-triage`
- `place-offering.paws-24-veterinary-center.avian-care`
- `place-offering.night-paw-clinic.emergency-triage`
- `place-offering.green-paw-neighborhood-clinic.general-consultation`

Demo taxon relations should resolve seeded stable keys to IDs, for example
`taxon.core.canis-lupus-familiaris`, `taxon.core.felis-catus`, and either exact
bird taxa or a reviewed `taxon.core.aves` descendant rule. Never seed `dog`,
`bird`, `rodent`, or `exotic` strings into the new relations.

The current `services` fixture bucket must be classified before synchronization:
route distances, viewpoints, rest/water points, dog-park zones/equipment, and
shop product categories are facilities/routes/inventory rather than service
offerings. In particular, product categories such as food/carriers must not be
migrated as live inventory because `PRD-PLACE-002` forbids false live inventory.
Only semantically reviewed service/program rows should create definitions and
offerings.

## Localization contract

- Add one Laravel catalogue such as `lang/{en,lt,ru}/place_services.php`; do
  not store translated labels as taxonomy identity. This follows the existing
  language-file architecture (`docs/localization.md:13-48`).
- A system definition stores keys such as
  `place_services.definitions.veterinary.emergency_triage.label` and
  `.description`; all three locale catalogues must have identical keys and
  placeholders.
- Availability, access mode, eligibility, and result reason codes receive
  complete keys under `place_services.states.*`, `place_services.access.*`,
  `place_services.eligibility.*`, and `place_services.reasons.*`.
- Taxon labels must come from `LocalizedTaxonName`; scientific names remain
  source data, not Laravel translation strings.
- Size labels must call `PetSizeCategory::label()`; remove the duplicate place
  label ownership once the canonical reader is switched.
- User-authored offering notes remain in their supplied locale and must not be
  mistaken for translated system definitions.
- Do not call `Str::headline()` on service keys or localized labels. Presenters
  receive prepared localized labels and explicit state text.

## Required red-contract focus for principal/testing specialist

At minimum, make these fail before implementation:

1. An `emergency-vet` category with phone/hours but no emergency offering is
   never confirmed capable.
2. An emergency offering with unknown/unavailable availability is not in the
   confirmed tier.
3. Place-level bird support without offering-level bird support is not
   species-capable.
4. Offering-level Aves descendant support matches a seeded bird taxon; dog-only
   and explicit bird exclusion do not.
5. Null/empty species and size relations never become common pets/all sizes.
6. `individual` and `not-applicable` size values never act as wildcard matches.
7. Repeat seeding is count/ID stable and every stored service key resolves in
   EN/LT/RU.
8. A service remains correctly paired with its own structured price and state;
   no parallel-array/modulo association remains.
9. Facility and inventory fixture values do not become service offerings.

## Recommendation summary

Adopt a separate global place-service taxonomy and capability vocabulary,
place-owned offerings, and distinct place/offering taxon and size relations.
Reuse the global `Taxon` identity/localized-name boundary and the existing
`PetSizeCategory` enum. Treat absence as unknown, explicit negative as
unavailable/ineligible, and require an explicitly available emergency offering
plus offering-level species support. Category is only a discovery hint and is
never capability evidence.
