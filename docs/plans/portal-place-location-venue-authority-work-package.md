# Portal Place, Location, And Venue Authority

Implementation date: 2026-08-03

Status: authority foundation implemented and release verification complete

## Scope

This P03 package establishes one persisted place authority shared by the
existing place presentation and canonical `ForumEvent`. It does not replace
device telemetry, lost-pet sightings, shelter/foster ownership, or event
occurrences with parallel location records.

Implemented:

- `Place`, one optional `Venue`, typed `VenueArea` records, encrypted location
  history, expiring account-bound grants, and append-only exact-read audit;
- controlled type, visibility, lifecycle, verification, accessibility,
  access-purpose, venue, and venue-area enums with EN, LT, and RU labels;
- owner and current organization-role Policy authority, bounded accessible
  place queries, and immediate former-member revocation;
- idempotent place creation and access grants, audited exact reveal, and a
  row-locked location-change Action that versions old facts and revokes every
  non-revoked grant, including grants that start in the future;
- a public projection that cannot serialize exact address, exact coordinates,
  private instructions, metadata, or grant data;
- canonical `place_id`, `venue_id`, and `venue_area_id` event links, occurrence
  propagation, and material version snapshots without copying exact addresses;
- the existing add-place workflow now persists an unlisted review candidate;
- the existing event builder carries only place/venue IDs and public location
  text in Livewire state; exact location is resolved only through the audited
  server Action;
- the existing place directory and stable dynamic detail route now read
  policy-scoped persisted records; seeded catalogue records preserve localized
  editorial presentation while canonical public facts remain authoritative;
- factories for all six new models and environment-gated repeatable authority
  plus directory seeders with public, organization, and protected foster
  locations.

## Security Boundaries

- Exact place fields use encrypted casts and are hidden from model arrays.
- Public discovery queries include only active public records. Private records
  do not affect catalogue output, counts, static overlays, or event options for
  unauthorized accounts.
- Exact reveal requires a current manager or an active account-bound grant,
  reauthorizes after a row lock, and appends an audit row before returning data.
- Grant replay verifies place, recipient, event, and purpose; an idempotency key
  cannot cross those boundaries.
- Organization membership is evaluated at use time. Removed staff lose update,
  grant, and exact-location access without deleting their historical authorship.
- Event cards and occurrences retain only the public region and canonical IDs.
  Exact foster or private-home data is never copied into event rows or public
  Livewire properties.
- Attendance grants permit an audited exact-location reveal but cannot make a
  private place selectable for a new event. Only a manager, a public place, or
  a current event-operations grant crosses that separate Policy boundary.
- An attendance grant additionally requires the matching event and a current
  confirmed/checked-in/attended registration; a submitted, pending, waitlisted,
  rejected, or missing registration cannot receive exact event access.

## Data And Query Contract

Five additive reversible migrations add six tables, canonical catalogue
fields, and three nullable event
foreign-key links. Directory, access-window, organization, owner, event,
occurrence, venue, audit, and version lookups have composite indexes matching
their bounded Eloquent queries.

The event builder uses one bounded event-usable place query. Selecting a place adds one
authorization existence check and one bounded venue query. The legacy place
catalogue uses no more than four bounded statements, constrains any eager-loaded
membership to the current account, and reuses its request-local keyed result
for `all()` and `find()` calls. Moving from one to twelve accessible places adds
no more than one statement. No exact field is selected for catalogue output.

## Demo World

`PlaceAuthoritySeeder` adds three places, two venues, two venue areas, three
grant states, one exact-read audit, one encrypted location version, and three
event links. It creates a second event version when canonical place linkage is
materially introduced. It is limited to configured demo environments and does
not change stable entity counts when rerun. `PlaceDemoSeeder` synchronizes all
twelve public directory fixtures by stable key without replacing IDs.

## Verification

- focused place authority: 20 tests, 153 assertions;
- place plus complete factory/seeder coverage: 1,701 tests, 5,138 assertions;
- place directory plus event lifecycle/workflow regression: 58 tests, 886 assertions;
- related place, event, schema, migration, and social regression: 80 tests,
  608 assertions;
- isolated fresh migration/seed/repeat seed: 126 migrations, 211 tables, stable
  five-user demo identity count, and both exits `0`;
- complete isolated migration lifecycle: 2 tests, 11 assertions;
- full serial Pest suite: 2,579 tests, 81,626 assertions;
- full Pint and Larastan: passed, with zero static-analysis errors;
- Composer validation and security audit: passed, with zero known
  vulnerabilities;
- NPM audit and the Vite production build: passed, with zero known
  vulnerabilities;
- config, route, view, and event cache smoke checks: passed, followed by a
  successful cache clear;
- isolated browser audit: desktop and mobile place directory/detail surfaces
  passed with one `h1` and `main`, zero overflow, zero unnamed controls, zero
  raw place translation keys, zero private-location leaks, zero undersized
  mobile controls, and zero console errors; responsive card heights measured
  384-473px on desktop and 614-654px on mobile against enforced 480/720px
  ceilings;
- the Vingis route now uses a relevant forest-path asset rather than a
  mismatched landscape image, and split-view media fills a compact stable
  column without changing the mobile card contract;
- no raw SQL, Blade query, Volt component, exact-address Livewire property, or
  private catalogue overlay was introduced.

The first browser run exposed a 16x16 `open_now` checkbox target. Its actual
form control was raised to the shared 44px target and the complete browser
audit then passed; the audit was not weakened to hide the defect.

## Deliberate Follow-On Scope

- P04 owns final page identifiers, localized metadata, sitemap/cache policy,
  and the remaining place detail/public-directory presentation audit; stable
  dynamic routes and persisted records are already active.
- P05 owns selected place/organization context and global navigation switching.
- Route geometry, operational offline packages, access notifications, material
  event-change delivery, venue confirmation review, and grant administration UI
  remain later event packages.
- Device coordinates, lost-pet sightings, foster records, and medical location
  facts retain their current domain ownership and may reference `Place` only
  through an explicit later integration.

This package is a verified authority foundation, not a claim that every Point
13 venue, map, route, offline, incident, or public-SEO requirement is complete.
