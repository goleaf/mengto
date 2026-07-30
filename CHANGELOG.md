# Changelog

## Unreleased - 2026-07-30

### Runtime And Architecture

- Raised the runtime contract to PHP `>=8.5.0 <8.6.0`, Laravel `^13.0`,
  Livewire `^4.3.3`, Tailwind/Vite plugin `^4.3.3`, and Vite `^8.2.0`.
- Added Larastan 3.10 at PHPStan level 5 and resolved all reported
  first-party findings without a broad baseline.
- Added production authentication around the existing actor-key ownership
  model, class-based multi-file Livewire auth flows, active-account middleware,
  signed verification, reset, rate limiting, and policy enforcement.
- Kept routes declarative, Blade passive, and persisted mutations in Actions or
  cohesive Services.

### Security And Data

- Removed the fixed prototype actor from protected operations and made the
  authenticated user's immutable actor key authoritative.
- Closed medical, care, device, order, booking, and coordination data to guests
  while retaining hashed, scoped, expiring temporary grants.
- Added identity fields through an additive migration and added leading indexes
  for 37 foreign keys across 25 existing tables.
- Added durable pet profiles, encrypted/versioned per-user social state,
  care-sync metadata, device retention/safety metadata, lifecycle records, and
  grouped device-event provenance through additive migrations.
- Preserved private file authorization, encrypted sensitive values,
  idempotency, source provenance, and audit behavior in critical workflows.
- Added fail-closed stolen/blocked-device policy while retaining owner-only
  lost-mode activation.
- Added baseline browser security headers and environment-gated demo accounts.

### Frontend And Localization

- Added one Laravel localization architecture for `en`, `lt`, and `ru` with
  validated persisted locale selection and English fallback.
- Extracted 2,340 Blade literals plus complete action, HTTP, Livewire, and
  service messages; dynamic sentences now use named placeholders and plural
  forms.
- Added locale-aware presentation formatting for user dates, times, numbers,
  percentages, currency, lists, measurements, and coordinates.
- Added automated guards against untranslated static/interpolated Blade text,
  `@php`, Volt, direct application calls in Blade, and unsafe environment reads.
- Added explicit Tailwind source detection, shared design tokens, visible
  focus, reduced-motion and forced-colors support.
- Corrected narrow-screen booking overflow and verified representative
  320-1920 px viewport behavior.

### Factories, Seeders, And Tests

- Added valid model factories for all 66 first-party Eloquent models, 23
  explicit helper states, and automated coverage of 412 enum-backed states.
- Made full demo seeding repeatable and production-safe.
- Added deterministic role/locale/privacy demo graphs and an opt-in
  production-blocked 250-profile performance seeder.
- Added an asserted temporary-SQLite fresh migration/seed verifier so
  destructive database checks cannot silently target the development file.
- Expanded Pest from a baseline of 116 tests / 3,881 assertions to 696 tests /
  31,698 assertions, then removed the two meaningless framework example tests.
- Added auth, authorization, localization, schema, factory/seeder,
  architecture, and responsive regression coverage.

### Documentation And Operations

- Established canonical requirements, architecture, domain/data/security,
  frontend, Livewire/Tailwind, accessibility, localization, testing, seeding,
  performance, deployment, operations, audit, ADR, and traceability documents.
- Catalogued 163 active requirements with evidence-backed status and generated
  a per-model seeding matrix.
- Documented deployment, forward-fix migration strategy, production seeding
  restrictions, provider boundaries, and the local-database audit incident.

### Verification

- Full Pest suite: 696 passed, 31,698 assertions in both serial and parallel
  process-isolated runs.
- Larastan/PHPStan level 5: zero errors.
- Composer strict validation and security audit: passed, zero advisories.
- NPM high-severity audit: passed, zero vulnerabilities.
- Production Vite 8.2 build: passed.
- Fresh temporary SQLite migration and repeated seeding: passed.
- Config, route, and Blade cache compilation: passed.
- Coverage percentage remains unavailable because this PHP 8.5 runtime has no
  PCOV or Xdebug driver.

### Upgrade Notes

- Production requires PHP 8.5 and must run all pending additive migrations.
- Deploy both Composer and NPM lock files and rebuild frontend assets.
- Do not run demo seeders in production.
- New social mutations persist in encrypted per-user state; historical
  transient browser-session values were never production records and are not
  imported.
