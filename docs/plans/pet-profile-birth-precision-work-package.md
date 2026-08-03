# Pet Profile Birth Precision Work Package

Date: 2026-08-03

Status: implemented, release-verified, and published to `origin/main`.

## Requirement Boundary

This package verifies exactly these 17 atomic requirements:

- `pet.identity.0160-pet.identity.0166`;
- `pet.identity.0168`, `pet.identity.0170`, `pet.identity.0172`, and
  `pet.identity.0174`;
- `pet.identity.0177`, `pet.identity.0179`, `pet.identity.0181`,
  `pet.identity.0182`, `pet.identity.0184`, and `pet.identity.0186`.

The section headings and explanatory prompts `pet.identity.0157-.0159`,
`pet.identity.0167`, `pet.identity.0169`, `pet.identity.0171`,
`pet.identity.0173`, `pet.identity.0175`, `pet.identity.0176`,
`pet.identity.0178`, `pet.identity.0180`, `pet.identity.0183`, and
`pet.identity.0185` remain open. Life-stage labels, medical verification,
ownership, taxonomy, and breed provenance remain separate work packages.

## Data Contract

`PetBirthDatePrecision` distinguishes exact date, estimated date, month and
year, year only, current age estimate, and unknown. The existing nullable
`birth_date` remains the compatible date value for date-backed modes. The
additive migration adds an age estimate in months, its observation timestamp,
and an optional celebration month/day without changing the stable pet key or
any adjacent-domain relationship.

`PetBirthDetailsNormalizer` is the reusable server-authoritative boundary. It
validates each mode, rejects future or impossible values, clears incompatible
fields, derives only the compatible stored date, preserves an unchanged age
estimate's observation timestamp, and treats the celebration day as an
optional chosen annual date rather than a verified birth fact. Creation,
generic update, progressive step update, and Livewire autosave all reuse this
same service.

## Derived Age And Eligibility

`PetProfileAgeCalculator` returns a month range at a supplied reference time.
Exact and estimated dates yield one derived value; month-only and year-only
facts retain their uncertainty as a range; an age estimate advances from its
recorded timestamp; unknown yields no age. No manually stored age becomes
stale.

`PetProfileAgeLabel` formats the result in the active locale and preserves the
certainty level in workspace, public profile, duplicate review, and lost/found
projections. Event registration uses the same range and accepts an uncertain
pet only when the complete range satisfies the configured minimum and maximum,
so a fabricated January 1 date cannot grant eligibility.

## Interface And Privacy

The Age and sex step exposes six localized precision choices and only the
controls compatible with the selected mode. Current display, linked help and
errors, loading/dirty/offline behavior, manual save, and native-change autosave
remain available. Celebration month/day is optional and explicitly described
as unverified. The public profile receives only the existing public age
projection plus the chosen celebration day; observation timestamps and raw
storage fields are not rendered.

EN, LT, and RU provide matching labels, help, validation, approximate/range
age output, and celebration text. Desktop, 390px, and 320px browser checks
retain one H1/form, complete keyboard controls, 44-pixel targets, and no page
overflow, raw keys, duplicate IDs, unnamed controls, or console errors.

## Query Delta

- Existing profile reads keep the same query count and select four additional
  nullable scalar columns where age is projected.
- Age calculation, localized labels, celebration formatting, and event
  eligibility perform no database query.
- The migration does not add an index because none of the new values is used
  for filtering, ordering, or joining.
- Blade performs no query or aggregate and receives prepared projection data.

## Verification Evidence

Observed on PHP 8.5.8, Laravel 13, Livewire 4.3.4, Pest 4.7.5, and SQLite:

- `PetProfileBirthPrecisionTest`: 15 tests and 83 assertions;
- affected pet, event, lost/found, duplicate, and workspace regression: 138
  tests and 4,382 assertions;
- complete pet-profile regression: 114 tests and 3,801 assertions;
- isolated complete sequential repository suite: 2,831 tests and 90,267
  assertions in 163.089 seconds;
- full Pint and Larastan: passed with zero findings;
- Composer strict validation, locked audit, and PHP platform requirements:
  passed with no vulnerability advisory;
- npm audit and Vite 8.2 production build: passed with zero vulnerabilities;
- fresh disposable SQLite applied all 135 migrations and retained 217 tables,
  five users, and three pet profiles across repeated complete seeding;
- config, event, route, and view cache compilation: passed;
- disposable-database Chrome exercised the real Livewire precision change,
  age-estimate save/reload, public projection, and responsive form at desktop,
  390px, and 320px with zero overflow, raw keys, duplicate IDs, undersized or
  unnamed controls, privacy leaks, or console errors.

The implementation commit is `8c97ede`, and the generated requirement-evidence
commit is `8ad4e40`. Both passed staged diff inspection and were published when
the observed push advanced `origin/main` from `0f79ced` to `8ad4e40`.

## Remaining Boundaries

- Life-stage calculation and species-specific thresholds remain open.
- Medical or documentary verification of birth facts remains in the protected
  medical/fact provenance boundary.
- Breed, taxonomy, ownership, adoption, found-animal, and dispute provenance
  are not inferred from a date or age estimate.
- Changes to privacy granularity, reminders, analytics, and lifecycle behavior
  require their later dependency-bound packages.
