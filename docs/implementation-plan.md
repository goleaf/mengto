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
- Normalized 165 stable active requirement identifiers.
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
- Added forum-wide focusable validation summaries with field-linked errors,
  semantic data-table captions, accessible media description/transcript/WebVTT
  contracts, text map alternatives, 44-pixel controls, contrast assertions,
  and a dependency-free Chrome desktop/mobile/reflow smoke runner.
- Corrected 320 px booking overflow and verified representative viewport
  widths, one logical `h1`, auth focus behavior, and a clean browser console.
- Completed the server-rendered place contract with validated deterministic
  pagination, preserved filter URLs, source/freshness/history coverage, and a
  guarded emergency clinic mode with direct call and route actions.
- Replaced publication-photo new-tab enlargement with one progressively
  enhanced PhotoSwipe viewer. Stable server-resolved photo keys now scope
  shared policy-authorized reactions and escaped comments to each individual
  photo through indexed relational records and a one-member/one-photo unique
  constraint. The shared gallery component provides localized zoom,
  keyboard/touch navigation, URL deep links, focus restoration, and responsive
  bottom/side social panels.
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
- Adopted Laravel 13's first-party image component through one
  `StorePublicImage` Action for every public photo-upload seam, with bounded
  input dimensions, EXIF orientation, WebP output, generated names, localized
  failures, and focused HTTP/action regressions.
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

## Current Delivery: Forum Topic-Type Schema Runtime

Status: `verified` on 2026-08-03

- Extract one typed catalogue for system field schemas and capability rules.
- Resolve active definitions through one bounded, versioned cache with model
  and synchronization invalidation.
- Enforce location, species, attachment, answer-rating, accepted-answer, and
  notification constraints at HTTP and direct Action boundaries.
- Persist the resolved definition ID and schema version on generic topic
  create/update while preserving existing structured data.
- Promote only the exact 20 scoped Phase 3 IDs after the complete gate passes;
  leave the 13 migration/final-audit IDs open.

The complete gate passed 2,360 tests and 78,407 assertions, full Pint and
Larastan, dependency audits, Vite/cache compilation, isolated migration and
repeat seed, immutable source preservation, and deterministic generation.

The executable contract and stop conditions are maintained in
`docs/plans/forum-topic-type-schema-runtime-work-package.md`.

## Current Delivery: Complete Phase 3 Migration Verification

Status: `implemented and verified` on 2026-08-03

- Audit every first-party migration for typed `up()`/`down()` methods and raw
  SQL escape hatches.
- Apply all migration filenames to an asserted disposable SQLite database,
  compare the ledger, roll every migration back, and reapply the exact set.
- Run the complete production-safe seed twice after reapplication and require
  stable identities.
- Reuse schema-integrity, constraints, enums/casts, factories, populated
  compatibility, and package rollback tests instead of adding a speculative
  migration.
- Passed the focused 2-test/11-assertion contract, the related
  1,611-test/4,795-assertion persistence slice, and the complete sequential
  2,362-test/78,760-assertion suite.
- Passed full Pint/Larastan, dependency audits, production build, cache
  compilation, fresh migration/repeat seed, complete rollback/reapply, source
  preservation, and deterministic requirement generation.

Exact scope, stop conditions, and final evidence are in
`docs/plans/forum-phase3-migration-verification-work-package.md`.

## Current Delivery: Phase 4 Before-Ownership Category

Status: `implemented and verified` on 2026-08-03

- Validate the complete source-derived category manifest before runtime use:
  checksum, schema version, exact totals, root sequence, required fields,
  stable-key/slug formats, hierarchy prefixes, and global uniqueness.
- Prove exact source-to-manifest-to-database metadata and ordering for category
  21 while leaving its two Phase 5 taxonomy labels unpromoted.
- Move database readiness checks into the locale-tree cache miss, reducing a
  warm localized read from 2 database queries to 0.
- Passed the focused 13-test/38-assertion contract, related
  72-test/5,949-assertion slice, and complete sequential
  2,384-test/78,891-assertion suite plus every applicable release gate.

Exact scope, exclusions, and evidence are in
`docs/plans/forum-phase4-before-ownership-category-work-package.md`.

## Current Delivery: Event Schedule Foundation

Status: `implemented and verified` on 2026-08-03

- Added occurrence-scoped tracks, rooms, sessions, and staff assignments with
  reversible schema, indexes, encrypted private data, enum state, factories,
  and repeat-safe demo records.
- Added one row-locked, idempotent schedule Action with policy checks,
  occurrence/timezone/capacity validation, room/track/staff overlap detection,
  and owner-level audited override.
- Added one responsive shared schedule component and a class-based Livewire
  create/edit surface in the existing event workspace. Public viewers do not
  receive drafts or private staff assignments.
- Added direct Action, policy, schema, encryption, conflict, Livewire,
  localization, factory, and seeder tests in
  `EventScheduleWorkflowTest` and `EventLifecycleFoundationTest`.
- Passed 2,362 sequential PHP tests with 78,760 assertions, full
  Pint/Larastan, isolated fresh migration plus repeat seed, production build
  and audits, and six event desktop/mobile browser audits with zero console
  errors.

Session attendee reservations/waitlists, schedule-change notifications,
venue entities beyond event-scoped rooms, and keyboard reordering remain open
and are not claimed by this delivery.

## Current Planning: Portal And Events Completion

Status: `execution-ready` on 2026-08-03; implementation packages pending

The live audit found 3,449 `portal.*` requirements still without Point 12
evidence and 4,883 of 4,968 `event.*` requirements still planned/discovered.
The current code is not empty: it has one authenticated shell, 162 first-party
routes, the canonical event aggregate, lifecycle/occurrence foundations,
multi-pet registration, manual attendance, and occurrence-scoped schedules.
The gap is therefore a combination of missing advanced domains and existing
portal behavior that has not yet been reconciled against atomic requirements.

The factual audit is
`docs/audits/portal-events-completion-gap-analysis.md`. The unbounded,
dependency-ordered implementation contract is
`docs/plans/portal-events-completion-master-plan.md`. Its 36 packages begin
with evidence reconciliation, organization and location authority, then add
portal contexts/search/calendar/dashboard infrastructure before completing
event builder, eligibility, registration, capacity, payment, check-in, safety,
specialized event domains, UI migration, seeding, verification, and release.

## Current Delivery: Global Page Identity Standardization

Status: `in progress`; first safe directory slice targeted-verified on
2026-08-03

The complete route classification, canonical `x-page-header` contract,
thirteen priority regression routes, forum category/subcategory information
architecture, meetup upgrade gate, global migration waves, and final quality
gates are recorded in
`docs/plans/global-page-identity-standardization-plan.md`.

The first slice extended `x-page-header` with a stable accessible heading,
metadata, and action regions. It migrated medical records, care journals,
lost-and-found, marketplace, experts, and messages while retaining the
existing reference-directory consumers. The nine message folders remain above
the messaging shell, and the global linked-media navigation remains unchanged.

Focused and full Pest, localization, Pint, Larastan, Vite, dependency audits,
cache smoke, isolated migration/seed/idempotency, and authenticated responsive
browser checks passed for this slice. The 108-route classification ledger and
all current shared-header caller IDs are now executable contracts. The stable
requirement ID, remaining migration red contracts and shared-component browser
fixtures, meetups and forum migrations, scoped publication, and final global
audit remain open; this delivery is not globally complete.

## Completed Delivery: Guest Join Page

Status: `superseded` by the authenticated portal boundary on 2026-08-03

- Replaced the guest root prototype feed with the localized, privacy-aware
  joining experience specified in `docs/plans/join-landing-page-plan.md`.
- Preserved the stable `home` route name; active verified members enter the
  canonical content feed and active unverified members to email verification.
- Used one primary account-creation action, passive Blade, first-party product
  presentation, current design tokens, and no guest database query.
- Removed fictional member identity and private member navigation from the
  guest document.
- Verified route state, auth continuity, EN/LT/RU, metadata, accessibility,
  320-1920 pixel browser behavior, Pint, Larastan, 2,037 serial Pest tests,
  and the production Vite build. The still-tested prototype feed was retained
  behind the authenticated `preview.feed` route instead of being deleted.

The market and settings rationale is recorded in
`docs/audits/pet-social-network-benchmark.md`. A consolidated settings center
is a separate future work package and must be mapped to exact open requirement
IDs before implementation.

## Current Delivery: Authenticated Portal Boundary

Status: `verified` on 2026-08-03

- Added one central session-aware boundary before route-model binding and made
  it persistent across Livewire updates.
- Reduced anonymous access to localized login, registration, and password
  recovery; JSON product requests return `401` without product data.
- Restricted product access to active verified accounts while retaining
  route-specific policies, grants, throttles, and step-up checks.
- Revoked anonymous medical/care/device token-share access as an outer route
  boundary without weakening token expiry, scope, revocation, or audit rules.
- Disabled direct local storage serving and public storage-link generation.
  Product uploads render through a canonically contained authenticated media
  route with bounded content types.
- Verified 2,092 serial Pest tests and 73,983 assertions, including guest
  zero-query denial, route ordering, Livewire upload/preview denial, token
  shares, traversal, unsupported content, and symbolic-link escape.
- Verified the complete non-test release gate: dependency audits, production
  build, fresh migration and repeated seed, cache smoke checks, generated
  requirement checks, and authenticated browser flows with the EN/LT/RU
  account-entry shell at 320-1920 pixels and no console errors.

The exhaustive execution and release plan is
`docs/plans/authenticated-portal-access-plan.md`.

## Current Delivery: Forum Database Correctness Reconciliation

Status: `verified` on 2026-08-03

- Added portable fixed-value constraints and backed enum casts for answer
  votes and photo reactions.
- Added moderation-case optimistic versioning and unique closure request keys.
- Added one policy-authorized, row-locked, retry-bounded, idempotent close
  Action with transactional bulk audit events and no new route or UI.
- Corrected the canonical topic answer pointer during competing single-answer
  acceptance.
- Added direct database, rollback/reapply, authorization, replay, stale-write,
  archival/cast, duplicate-attempt, and constant-query-growth tests.
- Verified the complete combined gate: 2,303 tests and 76,179 assertions,
  117 fresh migrations / 196 tables / stable repeat seed, full Pint/Larastan,
  dependency audits, Vite/cache compilation, deterministic 38,377-record
  generation, and EN/LT/RU loopback Chrome checks.

Exact scope, evidence, remaining gates, and stop conditions are in
`docs/plans/forum-database-correctness-reconciliation-work-package.md`.

## Current Delivery: Global Linked Media Navigation

Status: `verified` on 2026-08-03

- Added one passive Blade primitive that links representative media only from
  an explicit server-prepared canonical target and remains passive when the
  target is absent.
- Migrated eligible pet, group, neighbor, meetup, discovery, profile, expert,
  booking, messaging, and marketplace projections without adding queries or
  guessing routes.
- Preserved viewer, gallery, current-page, QR, map, video, upload, action, and
  private-download semantics through an exhaustive 73-template inventory.
- Added EN/LT/RU accessible labels, visible focus, reduced-motion and
  forced-colors behavior, exact-destination tests, nested-interactive source
  guards, and responsive browser checks.
- Passed the 19-test contract, 67-test affected slice, Pint, Larastan,
  dependency audits, Vite build, cache smoke checks, fresh isolated migration,
  repeat seed, and 24-route/viewport browser matrix.
- The final serial repository suite passed 2,303 tests and 76,111 assertions in
  131.130 seconds after an earlier concurrent loader conflict disappeared.

The exhaustive scope, classifications, acceptance criteria, and gate evidence
are recorded in `docs/plans/global-linked-media-navigation-plan.md`.
