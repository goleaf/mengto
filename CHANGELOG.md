# Changelog

## Unreleased - 2026-07-31

### Forum And Animal Taxonomy

- Preserved the combined forum/taxonomy specification with a deterministic
  SHA-256 and generated 7,284 source-linked atomic requirements.
- Added a deterministic 44-root, 1,637-subcategory forum hierarchy with
  stable keys, translations, aliases, redirects, safe synchronization, topic
  backfill, and cache invalidation.
- Added versioned animal taxonomy sources, names, identifiers, changes,
  domestic classifications, breed registries, community groups, and a
  checksummed chunked import pipeline with analysis, resume, validation,
  activation, rollback, and conflict-safe history.
- Added scoped append-only reputation, audited trust and badges, qualified
  confirmations, accepted-answer history, unified reports, moderation cases,
  actions, appeals, recusal, and private evidence.
- Added the authorized moderation operations UI for triage, case assignment,
  audited actions, conflict recusal, and independent appeal review, including
  linked report state synchronization and mobile-safe tables.
- Added independent professional credential review with expiry, suspension,
  appeal, audit events, and authorized class-based Livewire administration.
- Added structured adoption/foster cases, encrypted idempotent applications,
  controlled review/adoption/follow-up/return/foster transitions,
  append-only history, taxonomy links, policy enforcement, localized Livewire
  UI, and an atomic bridge from marketplace reports to unified moderation.
- Connected adoption provider identity to independent purpose-compatible
  credential review with owner isolation, natural expiry, rejection,
  suspension, revocation, appeal propagation, private evidence boundaries,
  and an idempotent backfill path.
- Extended lost/found into structured owned-pet cases with global taxonomy
  links, encrypted historical animal snapshots, sighted/stolen/reunited
  states, immutable history, idempotent protected contact relay, advisory
  duplicate detection, reward-abuse controls, unified safety reports, and
  privacy-safe archive preservation.
- Extended the existing knowledge base into collaborative guides with
  independent review states, normalized collaborators, immutable versions and
  workflow events, optimistic editing, correction review, editorial locks,
  rollback-as-new-version, locale/taxon/jurisdiction scope, print/export, and
  an authorized class-based Livewire editor and administration registry.
- Added assigned low-risk community review panels and contextual notes with
  trust-based eligibility, conflict-aware reviewer balancing, reasoned
  one-reviewer decisions, deadline enforcement, replacement, appeals,
  append-only versions/events, author responses, moderator outcomes,
  revalidation, localized Livewire presentation, and a strict boundary to
  high-risk human moderation.
- Added opt-in peer mentorship for thirteen scopes with transparent bounded
  matching, independent professional-verification display, participant-only
  private threads, block/report safety, optional immutable feedback,
  optimistic lifecycle transitions, independently validated reputation,
  repeatable demo data, and class-based localized Livewire interfaces.
- Added an evidence overlay that prevents requirement verification without
  concrete file or test evidence; 287 atomic requirements are currently
  verified.

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
- Prevented archived lost/found cases from appearing through directories,
  direct public URLs, or poster routes while retaining owner access and all
  sightings, updates, reports, identifiers, and events.
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

- Added valid model factories for all 109 first-party Eloquent models and a
  complete automated factory/enum-state creation matrix.
- Made full demo seeding repeatable and production-safe.
- Added deterministic role/locale/privacy demo graphs and an opt-in
  production-blocked 250-profile performance seeder.
- Added an asserted temporary-SQLite fresh migration/seed verifier so
  destructive database checks cannot silently target the development file.
- Expanded Pest from a baseline of 116 tests / 3,881 assertions to the current
  checkpoint of 963 tests / 42,451 assertions.
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

- Full Pest suite checkpoint: 963 passed, 42,451 assertions in serial mode.
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
