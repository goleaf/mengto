# Production Modernization Plan

Plan date: 2026-07-30

This living plan records work that was actually performed. A pass is
`verified` only when its listed check completed successfully. Requirement-level
status remains authoritative in
`docs/requirements/compliance-matrix.md`.

## Pass 1: Protect And Baseline

Status: `verified`

- Inspected branch, remote, tracked/untracked state, locks, runtime,
  dependencies, routes, schema, source, tests, and first-party Markdown.
- Preserved the pre-existing untracked `.agents/vendor/` tree.
- Captured Composer/NPM audits, full Pest baseline, Vite assets, routes,
  migrations, seed repeatability, and the absence of a coverage driver.
- Recorded the accidental local SQLite rebuild without concealing its impact.

Evidence: `docs/current-state-audit.md`.

## Pass 2: Canonical Documentation And Requirements

Status: `verified`

- Established `docs/index.md` and canonical product, system, non-functional,
  architecture, domain, data, security, authorization, frontend, Livewire,
  Tailwind, accessibility, localization, testing, seeding, performance,
  caching, integration, deployment, operations, audit, review, and limitation
  documents.
- Preserved historical feature specifications and plans while making their
  prototype-only authority explicit.
- Normalized 163 stable active requirement identifiers.
- Generated one traceability row per requirement and one factory row per model.

Verification:

```bash
php scripts/generate-compliance-matrix.php
php scripts/generate-seeding-coverage.php
php artisan test --compact tests/Feature/ArchitectureComplianceTest.php
```

## Pass 3: Runtime And Dependencies

Status: `verified`

- Constrained PHP to `>=8.5.0 <8.6.0` and Laravel to `^13.0`.
- Added Livewire 4.3 class-based components and Larastan 3.10/PHPStan 2.2.
- Updated Vite to 8.2 and retained Tailwind/Vite integration 4.3.3.
- Preserved Pest 4 as the intentional primary test style.
- Kept the single NPM lock and added no speculative infrastructure package.

Verification: Composer validation/audit/why-not/outdated, NPM audit/outdated,
application boot, Larastan, Pest, and production build.

## Pass 4: Identity, Authentication, And Authorization

Status: `verified` for the persisted application boundary

- Added immutable unique actor keys, account locale/timezone/status, and an
  explicit administrator capability through an additive migration.
- Added class-based Livewire login, registration, password reset, password
  confirmation, and account forms with separate Blade templates.
- Bound ownership to the authenticated actor rather than a fixed prototype
  identity.
- Protected mutations and private medical, care, device, order, booking, and
  coordination routes.
- Added active-account middleware, rate limits, signed verification, session
  regeneration/invalidation, policy checks, and environment-gated demo users.
- Added Laravel's fresh-password step-up contract to precise device pages and
  remote device commands; failed or missing confirmation produces no command.

## Pass 5: Localization And Passive Presentation

Status: `verified`

- Added validated `en`, `lt`, and `ru` locale handling with `en` fallback.
- Extracted static Blade, PHP, JavaScript, validation, and notification text
  to stable keys; the PHP localizer reports zero remaining eligible literals.
- Replaced dynamic count/status sentences with placeholder/plural-aware
  `presentation.php` keys.
- Added architecture checks for static and interpolated Blade literals,
  locale-key parity, placeholder parity, localized auth pages, and validation.
- Compiled every Blade template after migration.

Lithuanian and Russian catalogues are structurally complete and pass key and
placeholder parity. Native linguistic review is an external editorial step,
not an implementation gap.

## Pass 6: Data, Queries, Factories, And Seeders

Status: `verified`

- Added the identity migration and a reversible migration providing a leading
  index for every previously uncovered foreign key.
- Enabled strict Eloquent behavior outside production and repaired model
  projections exposed by it.
- Added valid factories for all 66 first-party Eloquent models through a typed
  application factory base and explicit model factories.
- Added 23 explicit helper states and verified 412 enum-backed states.
- Made `DatabaseSeeder` repeatable and production-gated.
- Added an opt-in, deterministic, production-blocked performance seeder.
- Added safe fresh-database verification using an asserted system temporary
  SQLite path.
- Added bounded query checks for care journals and smart devices.

Verification:

```bash
php artisan test --compact tests/Feature/Database
php scripts/verify-fresh-database.php
```

## Pass 7: Livewire, Tailwind, Accessibility, And Browser Behavior

Status: `verified`

- Kept Livewire class-based and multi-file; Volt remains prohibited and absent.
- Added minimal typed/form-object state, validation, precise loading targets,
  dirty/offline feedback, and server-authoritative auth actions.
- Added explicit Tailwind source detection and design tokens while preserving
  the mature SCSS component layer.
- Added visible focus, reduced-motion and forced-colors behavior.
- Corrected 320 px booking overflow and verified representative viewport
  widths, one logical `h1`, auth focus behavior, and a clean browser console.
- Completed the server-rendered place contract with validated deterministic
  pagination, preserved filter URLs, source/freshness/history coverage, and a
  guarded emergency clinic mode with direct call and route actions.
- Added durable `PetProfile` records and encrypted, versioned
  `UserDomainState` persistence so social mutations survive sessions without
  exposing browser-controlled authority.
- Added a durable IndexedDB care queue with source time, idempotency, conflict
  metadata, and duplicate-safe synchronization.

## Pass 8: Security And Quality

Status: `verified` for implemented boundaries

- Repaired fixed-actor authorization, guest access to private domains, session
  fixation/logout behavior, environment-gated demo identities, private media
  authorization, idempotent mutations, sensitive serialization, and baseline
  browser headers.
- Added architecture guards for Blade, Volt, environment reads, database
  shortcuts, route shape, model factories/fillable fields, localization, and
  debug leakage.
- Removed meaningless framework example tests.
- Formatted all PHP and resolved Larastan level 5 findings without a baseline.

Provider-specific SSRF, webhook, media transport, and redacted integration-log
tests remain not applicable until those entry points exist.

## Pass 9: Final Verification

Status: `verified`

Completed:

- full serial Pest: 696 passed, 31,698 assertions;
- full parallel Pest: 696 passed, 31,698 assertions;
- Larastan: zero errors;
- production Vite build;
- Blade compilation;
- Composer/NPM security audits;
- isolated fresh migration and repeat seed;
- config, route, and view cache build plus application boot;
- syntax, dependency, translation, and generated-matrix checks;
- connected browser desktop/mobile/auth/private-flow review;
- expected coverage failure caused only by the missing PCOV/Xdebug driver.

Final staged-diff verification is part of Pass 10 because it must inspect the
exact temporary Git index used for publication.

## Pass 10: Publication

Status: `verified`

The canonical Markdown has been synchronized, evidence matrices regenerated,
and generated browser artifacts removed. Publication used a temporary Git
index that excluded the pre-existing `.agents/vendor/` tree. The exact staged
diff passed `git diff --cached --check`, the coherent modernization commit was
created on `main`, and the observed push advanced `origin/main`.

No blocked or not-applicable requirement may be described as implemented in
the release report.
