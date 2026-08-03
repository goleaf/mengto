# Places Shared Card Classification

## Decision

The Places family shares stable card leaves but does not share one universal
shell.

| Surface | Decision | Reason |
| --- | --- | --- |
| `place-card` | adopt `x-card-media`, `x-card-heading`, and `x-card-description` | The public directory card has a canonical linked image and ordinary escaped title and summary. |
| `place-card` shell | keep domain | Its grid changes between list, split-map, and narrow layouts; category, warning, facts, and actions are Places-specific. |
| `place-dashboard` | keep domain | The detail workspace owns gallery, services, rules, specialists, reviews, questions, updates, provenance, and safety sections. |

The directory shell must therefore not be added to either Places component.
This avoids variant flags for map geometry and prevents a global component from
owning Places-specific state.

## Implementation Boundary

- `x-card-media` owns responsive image rendering, the synchronized destination,
  accessible link label, eager-loading hint, aspect fallback, and containment.
- `x-card-heading` owns the semantic `h3`, escaped name, and synchronized title
  destination.
- `x-card-description` owns escaped summary typography and safe wrapping.
- `place-card` retains category, location, status, warnings, recommendation
  reason, facts, save, route, call, and detail actions.
- `places-map.js` reads the shared `data-card-heading` hook instead of a
  Places-only title class, while retaining the canonical heading link as the
  map-selection destination.

No controller, presenter, authorization, query, cache, model, route, or
persistence behavior changes in this wave. Query delta is `0`.

## Local Media Boundary

Places no longer relies on a runtime public image host. `PlaceMediaCatalog`
owns the bounded category-to-asset mapping and returns only root-relative
first-party URLs under `public/images/places`.

| Domain category | First-party visual family |
| --- | --- |
| park, route | tree-lined public walking path |
| dog park | secure fenced exercise field |
| vet, emergency vet | calm veterinary consultation room |
| grooming | quiet grooming workspace |
| pet cafe, unknown fallback | pet-friendly community courtyard |
| pet store | accessible neighborhood pet shop |
| shelter | humane introduction and volunteer area |

Each family has primary, secondary, and tertiary crops. Every crop has exact
576x432, 900x675, and 1200x900 JPEG candidates. Cards, the detail hero, and the
photo gallery use the shared responsive-image implementation, so `srcset`
descriptors match real intrinsic widths and every candidate retains a 4:3
ratio.

The seven source scenes were created as first-party natural-editorial assets
with the built-in image-generation workflow. The prompt set required safe
pet-care scenes, European neighborhood context, crop-safe landscape framing,
no text, no logos, no trademarks, no watermarks, no recognizable faces, and no
unsafe animal handling. Generated source PNGs remain outside the repository;
only the selected optimized delivery variants are published.

## Acceptance Contract

`PlaceSharedCardCompositionTest` verifies six seeded cards, one shared media,
heading, and description leaf per card, synchronized media/title destinations,
the retained map-aware shell, the semantic JavaScript hook, and the separate
detail-dashboard boundary.

`PlaceLocalMediaTest` verifies all twelve demo places and every gallery item,
rejects public-host URLs or query-string transformations, checks every local
file exists, checks exact responsive dimensions, and requires seven distinct
primary visual families.

The existing connected-browser Places audit additionally verifies shared
regions, unclipped copy, image containment, destination synchronization,
marker-selection synchronization, touch targets, overflow, private-location
isolation, raw translation keys, and console errors at desktop and mobile
sizes. Run the focused form with `npm run test:browser:places`; its JSON report
and four screenshots are written to `BROWSER_OUTPUT_DIR`.

## Verification Evidence

Observed on the isolated attributable tree on 2026-08-03:

- place/shared/linked-media slice: 34 tests and 459 assertions;
- full serial Pest: 2,671 tests and 84,956 assertions;
- full Pint and Larastan: passed with zero findings;
- production Vite build, Composer validation/audit, and npm audit: passed with
  zero dependency advisories;
- fresh SQLite database: 130 migrations and 215 tables; repeated complete seed
  preserved 5 demo users;
- config, route, and Blade view cache smoke: passed;
- connected Chrome: directory and detail passed at 1440x900 and 375x812 with
  six loaded directory images, no clipped shared copy, mismatched destinations,
  escaped media, undersized controls, overflow, raw keys, private-location
  leaks, or console errors; marker selection remained synchronized.

Local-media continuation evidence observed on an isolated attributable tree on
2026-08-03:

- RED proof: both new local-media tests failed against the former Unsplash
  URLs;
- place/media/authority slice: 48 tests and 1,177 assertions passed;
- full serial Pest: 2,695 tests and 85,875 assertions passed;
- full Pint, Larastan, production Vite build, Composer validation/audit, and npm
  audit passed with zero findings or advisories;
- fresh SQLite database: 130 migrations and 215 tables; repeated complete seed
  preserved 5 demo users and 14 places;
- config, route, and Blade view cache smoke passed;
- connected Chrome passed the Places directory and detail at 1440x900 and
  375x812 with six locally served card images, no invalid images, overflow,
  clipped copy, undersized controls, raw keys, private-location leaks, or
  console errors; the four screenshots and all seven source-scene crop families
  were inspected.
