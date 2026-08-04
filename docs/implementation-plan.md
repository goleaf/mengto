# Production Modernization Plan

Plan date: 2026-07-30

This living plan records work that was actually performed. A pass is
`verified` only when its listed check completed successfully. Requirement-level
status remains authoritative in
`docs/requirements/compliance-matrix.md`.

## Current Delivery: General Pet Size Category

Status: `implemented and release-verified` on 2026-08-04.

- Store one nullable controlled category on the canonical pet profile without
  inferring a default from species, breed, image, weight, or legacy text.
- Reuse one server normalizer through the existing authorized Appearance
  Action while preserving optimistic locking, idempotency, audit, cache
  invalidation, no-op behavior, and omitted-input compatibility.
- Render an accessible EN/LT/RU editor and query-free public projection that
  explicitly distinguishes the category from measurements and medical facts.
- Add the profile-side `(size_category, status, id)` index without pretending
  a marketplace, place, event, service, carrier, product, or search consumer
  has been delivered.
- Keep exact measurements and public, household, or clinical weight privacy
  outside this nine-requirement package.

Exact scope and observed evidence belong in
`docs/plans/pet-profile-size-category-work-package.md`.

## Previous Delivery: Structured Pet Identifying Marks

Status: `implemented and release-verified` on 2026-08-04.

- Store up to twelve ordered identifying marks as normalized child rows with
  stable keys, controlled types, encrypted descriptions, actor attribution,
  visibility, and reversible retirement.
- Reuse one server normalizer and one synchronizer through the existing
  authorized Appearance Action while preserving optimistic locking,
  idempotency, audit, cache invalidation, and omission compatibility.
- Offer only public and private-verification visibility until friend, clinic,
  and active-search consumers have authoritative access checks.
- Render an accessible EN/LT/RU manager editor and eager-load only active
  public rows for the public profile, with a second presenter-side filter.
- Preserve the legacy encrypted free-text value as private compatibility data.

Exact scope and observed evidence belong in
`docs/plans/pet-profile-identifying-marks-work-package.md`.

## Previous Delivery: Species-Aware Pet Body Covering

Status: `implemented and release-verified` on 2026-08-04.

- Select coat, feather, scale, skin, mane, and shedding controls from the
  existing broad species without trusting browser-submitted applicability.
- Reuse one server normalizer across progressive and durable compatibility
  mutation paths while retaining authorization, optimistic locking,
  idempotency, audit, and cache invalidation.
- Store the schema-versioned object in the existing encrypted profile payload,
  reuse structured scale-color clarification, and add no migration or query.
- Render EN/LT/RU workspace and public projections while keeping the bounded
  skin observation manager-only.
- Keep search, recommendations, care, groomer/shelter/finder consumption,
  private marks, measurements, identity media, and medical facts outside this
  11-requirement package.

Exact scope and observed evidence belong in
`docs/plans/pet-profile-body-covering-work-package.md`.

## Previous Delivery: Pet Appearance Color

Status: `implemented, release-verified, and published` on 2026-08-04.

- Store one controlled primary color, up to four unique additional colors,
  three controlled patterns, and bounded species-neutral clarification in the
  existing encrypted profile payload.
- Reuse one server normalizer across progressive and durable compatibility
  mutation paths while retaining authorization, optimistic locking,
  idempotency, audit, and cache invalidation.
- Render EN/LT/RU workspace and public projections through one presenter with
  no new query, migration, or Blade business logic.
- Preserve legacy free text and keep identifying marks out of the public
  projection.
- Keep automatic lost/found descriptions, coat, marks, measurements, identity
  media, and cross-domain consumption outside this 12-requirement package.

Exact scope and observed evidence belong in
`docs/plans/pet-profile-appearance-color-work-package.md`.

## Previous Delivery: Pet Life Stage

Status: `implemented, release-verified, and published` on 2026-08-04.

- Derive newborn, juvenile, young, adult, senior, or unknown at read time from
  the existing honest age range and controlled animal-group thresholds.
- Keep separate dog, cat, bird, rabbit, rodent, fish, reptile, and horse
  boundaries; never use dog thresholds as a global fallback.
- Store only an authorized manual clarification with actor and time, and label
  it separately from an automatic result or medical verification.
- Reuse one server normalizer in progressive and durable compatibility update
  paths, retaining authorization, optimistic locking, idempotency, audit, and
  cache invalidation.
- Render EN/LT/RU workspace and public projections with no added query or
  private provenance disclosure.

Exact scope and final observed evidence belong in
`docs/plans/pet-profile-life-stage-work-package.md`.

## Previous Delivery: Pet Breed Origin And Provenance

Status: `implemented, verified, and published` on 2026-08-04.

- Preserve the legacy breed string as a bounded compatibility snapshot while
  storing an explicit one, mixed, possible-multiple, no-breed, or unknown
  overall state and up to four normalized origins.
- Keep confidence, information source, and optional mixed percentage separate
  from the breed value; a photograph cannot upgrade a reported or suspected
  origin to confirmed.
- Reuse one server normalizer and one owned-relation synchronizer across
  creation, generic update, progressive update, autosave, and manual save.
- Render localized trust/provenance controls and an honest public projection
  without queries or business logic in Blade.
- Keep taxonomy ingestion/verification, protected evidence documents,
  breed-based discovery, behavior, health, ownership, and lost/found effects
  outside this 35-requirement package.

Exact scope and observed evidence are recorded in
`docs/plans/pet-profile-breed-origin-work-package.md`.

## Current Delivery: Pet Name Identity

Status: `implemented, verified, and published` on 2026-08-03.

- Keep `pet_profiles.name` as the current canonical name and preserve all stable
  identity and adjacent-domain links during rename.
- Store typed nickname, previous, shelter, official, localized, and responds-to
  alternatives with normalized uniqueness, attribution, locale, and explicit
  visibility.
- Preserve the old current name automatically after a successful rename.
- Search only viewer-visible alternatives inside the existing policy-scoped pet
  workspace and expose only public alternatives on the public profile.
- Keep cross-domain name propagation, global/public alias discovery, merge,
  ownership, and taxonomy verification outside this package.

Exact scope and observed evidence are recorded in
`docs/plans/pet-profile-name-identity-work-package.md`.

## Previous Delivery: Honest Species Confidence

Status: `implemented, verified, and published` on 2026-08-03.

- Preserve the controlled broad species used by search and integrations.
- Store confirmed, possible, or unidentified confidence separately.
- Allow possible identification only for cat and dog and normalize every
  browser-controlled combination again in the Action.
- Render possible species honestly in every selected public, workspace,
  invitation, and duplicate projection.
- Keep adoption, found-animal coordination, ownership, and taxonomy
  verification outside this package.

Exact scope and observed evidence are recorded in
`docs/plans/pet-profile-species-confidence-work-package.md`.

## Current Delivery: Pet Duplicate Review And Access Requests

Status: `implemented, verified, and published` on 2026-08-03.

- Pause canonical pet creation on bounded, policy-visible name/species matches
  and expose only the safe identity/photo projection.
- Bind the explicit different-animal decision to an encrypted expiring review
  token that the creation Action verifies again.
- Store typed access requests with encrypted evidence, unique active/replay
  keys, reviewer attribution, and optimistic state.
- Grant no immediate capability; ordinary approval creates the existing
  invitation and requester acceptance activates it.
- Keep ownership transfer outside generic approval and leave organization
  attribution open until its relationship is authoritative.

Exact scope, observed gates, and remaining non-goals are recorded in
`docs/plans/pet-profile-duplicate-access-work-package.md`.

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

## Current Delivery: Phase 4 Special-Needs Category

Status: `implemented and verified` on 2026-08-03

- Prove the complete category-22 source-to-manifest-to-database hierarchy,
  including 54 exact ordered children and all system-category locale rows.
- Require reviewed target/fallback translations in the category tree and root
  selector so unfinished translations cannot replace trusted source text.
- Preserve the existing locale cache/invalidation contract with zero added
  queries and zero warm-tree database statements.
- Passed the focused 4-test/17-assertion contract, related
  46-test/36,051-assertion slice, and complete sequential
  2,396-test/79,143-assertion suite plus every applicable release gate.

Exact scope, exclusions, and evidence are in
`docs/plans/forum-phase4-special-needs-category-work-package.md`.

## Current Delivery: Phase 4 Wildlife-Coexistence Category

Status: `implemented and verified` on 2026-08-03

- Prove the complete category-23 source-to-manifest-to-database hierarchy,
  including its exact root metadata and all 55 ordered source labels.
- Prove exact synchronized root/child stable keys, slugs, positions, and
  reviewed EN/LT/RU root rows without changing production behavior.
- Retain the wildlife-crime and roadkill reporting labels in the immutable
  source list while keeping their `forum.moderation.0010/.0011` requirements
  open in Phase 7.
- Passed the focused 2-test/12-assertion contract, related
  31-test/36,368-assertion slice, and complete sequential
  2,484-test/80,398-assertion suite plus every applicable release gate.

Exact scope, exclusions, and evidence are in
`docs/plans/forum-phase4-wildlife-coexistence-category-work-package.md`.

## Current Delivery: Phase 4 One-Health Category

Status: `implemented and verified` on 2026-08-03

- Prove the complete category-24 source-to-manifest-to-database hierarchy,
  including its exact root metadata and all 42 ordered source labels.
- Add the missing localized boundary explaining that One-Health discussions do
  not replace a physician, veterinarian, public-health authority, or emergency
  service; render it only for the selected root or child category.
- Version the localized category-tree cache payload without adding a database
  statement, route, schema, model, Policy, or parallel taxonomy.
- Passed the focused 3-test/21-assertion contract, related
  47-test/36,632-assertion slice, complete sequential
  2,586-test/81,835-assertion suite, desktop/mobile Chrome audit, and every
  applicable release gate.

Exact scope and evidence are in
`docs/plans/forum-phase4-one-health-category-work-package.md`.

## Current Delivery: Unified Forum Topic Editor

Status: `implemented and verified` on 2026-08-03

- Replaced the detached right sidebar on `/forum/ask` with one coherent editor
  shell whose complete five-item publishing guidance precedes the form.
- Reorganized the unchanged authoring controls into three labelled context,
  response, and optional-media sections without changing authorization,
  validation, persistence, taxonomy interaction, or query count.
- Added reviewed EN/LT/RU presentation copy and responsive desktop/mobile
  styling with semantic headings, visible control states, and 44-pixel mobile
  targets.
- Passed the focused 2-test/33-assertion contract, complete isolated
  2,588-test/82,043-assertion suite, full Pint/Larastan, dependency audits,
  production Vite build, compiled views, and desktop/mobile Chrome audit.

Exact scope and evidence are in
`docs/plans/forum-topic-editor-redesign-work-package.md`.

## Current Delivery: Progressive Pet Profile Completion

Status: `implemented and verified` on 2026-08-03 for
`pet.creation.0036-pet.creation.0058` only.

- Preserved `/compose/pet` as a compatibility redirect to the canonical
  minimal private-draft creation screen and moved the subsequent workflow into
  twelve ordered URL-backed steps.
- Added one central responsive navigator, only one active body, independent
  step saves, mutation-free skipping, purpose explanations, and text-based
  saved/optional state without a disclosure score.
- Added allowlisted optimistic partial updates, active-step relationship
  loading, one reusable current-manager policy projection, and bounded
  navigation existence queries.
- Stored microchip readiness and an optional identifier as one private,
  encrypted, versioned fact guarded by `change-microchip`; unauthorized roles
  receive no value, completion signal, form field, or mutation control.
- Verified EN/LT/RU parity, responsive desktop/mobile/320px rendering,
  keyboard/focus/console contracts, 130-migration fresh/rollback/reapply and
  repeat seed, dependency/static/build/cache gates, and the final serial suite
  of 2,657 tests and 84,589 assertions.

Exact scope and open follow-up requirements are in
`docs/plans/pet-profile-progressive-completion-work-package.md`.

## Current Delivery: Pet Profile Draft Autosave

Status: `implemented and verified` on 2026-08-03 for
`pet.creation.0071-pet.creation.0081` with dedicated atomic evidence.

- Added change/blur-driven saves to the seven ordinary descriptive steps while
  retaining the manual submission path.
- Rejected unknown and inactive step parameters before mutation and retained
  the existing form validation, managed-profile policy, allowlist, row lock,
  optimistic version, audit, lifecycle evidence, and cache invalidation.
- Added a locked idempotency key that rotates only after a successful response,
  plus one reusable accessible save-status component and explicit temporary
  photo unsaved state.
- Added page-memory reconnect recovery: a numeric form revision marks pending
  input, `online` retries the same ordinary Livewire action once, and only a
  matching server confirmation clears that revision. No profile value is
  stored in browser persistence.
- The focused progressive suite passes 27 tests and 159 assertions, including
  all-step wiring, persistence after a fresh mount, validation-key stability,
  six bounded client-revision acknowledgement cases, no-op replay, and
  mismatched-step non-mutation.
- The integrated current tree passed full Pint, zero-error Larastan, the
  isolated 2,692-test/85,091-assertion reconnect suite, the final complete
  2,695-test/85,875-assertion current-tree suite, production Vite build, cache
  smoke,
  dependency audits, and authenticated EN/RU/LT browser verification including
  a real failed network request, one automatic reconnect retry, reload, and
  value-restoration cycle.

Exact scope, evidence, and non-goals are in
`docs/plans/pet-profile-draft-autosave-work-package.md`.

## Current Delivery: Unified Icon System

Status: `implemented and verified` on 2026-08-03.

- Audited every first-party Blade template, direct/dynamic Lucide call, legacy
  size class, inline SVG, raw pictogram, foreign icon system, and native
  interactive candidate.
- Added `x-ui-icon` as the single size, stroke, fill, color, and ARIA primitive
  plus a downward-only executable audit.
- Migrated all 698 direct calls across 146 files, reduced dynamic debt from 83
  to zero, migrated 41 legacy SCSS selectors, removed all 310 legacy class
  attributes, and removed the last raw pictogram.
- Added prepared icons to all thirteen desktop primary-navigation destinations
  while retaining visible labels and current-page semantics.
- Added icons to 45 unambiguous actions; the remaining 52 candidates were
  reviewed and recorded as intentional text/content controls. Static debt is
  zero.
- Passed 2,639 Pest tests / 83,214 assertions, full Pint, Larastan over 1,385
  files, dependency audits, production build, cache compilation, fresh and
  repeat seeding, and a 33-screenshot EN/LT/RU browser matrix from 320 through
  1920 pixels without overflow or console errors.

The factual baseline is `docs/audits/icon-system-deep-audit.md`; the unlimited
execution ledger is `docs/plans/icon-system-unlimited-plan.md`.

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

## Current Delivery: P02 Organization Authority Foundation

Status: `implemented, verified, and published foundation` on 2026-08-03

The first P02 slice adds the canonical organization tenant, nine independent
membership roles, account-bound expiring invitations, operational
restrictions, suspension, append-only audit, guarded demo seeds, localized
class-based Livewire workspaces, and responsible-organization event authority.
Wrong/former tenant access fails in queries and Policies, ordinary members do
not receive email or restriction reasons, and invitation tokens remain hashed
in records and absent from public Livewire state.

This does not close P02. Organization locations, selected context switching,
verification review/renewal, notification delivery and revocation, downstream
finance/marketplace/shelter operations, authoritative backfill, and exact
portal/event evidence remain open. Scope and current gate evidence are in
`docs/plans/portal-organization-authority-foundation-work-package.md`.

## Current Delivery: Global Page Identity Standardization

Status: `in progress`; directory, workflow, event, and forum presentation waves
verified on 2026-08-03

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

The current messaging continuation also replaces locale-dependent call state
with stable codes, moves call control/icon preparation into a class-based Blade
component, localizes the complete preflight surface in EN/LT/RU, and makes the
existing conversation-details route display its context panel at mobile and
tablet widths with a localized return control. This reuses the same protected
route, presenter, and context projection and adds no query or authorization
path.

The next detail-page audit closes the `/share/{target}` English-fallback
surface with a dedicated 42-leaf EN/LT/RU contract. `SharePresenter` resolves
five stable target families and three stable delivery channels, prepares
recipient actions and canonical icon names, and leaves destination, access,
and mutation boundaries unchanged. The measured presenter query delta is zero.

The following deliberate-profile audit closes `/neighbors/ari-jensen`. The
neighbors domain grows from 71 to 160 exact-parity leaves, and
`NeighborProfilePresenter` now prepares the profile copy, statistics,
follow/message/walk action payloads, pet routine, mutuals, communities, and
canonical icon names. The profile-led hero remains intentional, all existing
destinations and authenticated mutation boundaries remain unchanged, and the
presenter query delta is zero.

The next Package 8 wave migrated the medical-record, care-journal,
lost-and-found, marketplace, and expert create/edit/booking flows, the device
directory and connect flow, and both professional-workspace states. Their ten
route-ledger entries now enforce the canonical page identity, and the retired
device directory selector was removed.

The same wave then standardized the canonical database content feed, social
preview feed, all prepared composer modes, the knowledge directory, and all
three knowledge editor modes. This retires page-level feed headings and
knowledge uses of the generic forum header without changing their filters,
forms, authorization, or Livewire state.

The event directory and database-backed event workspace now share the same
page identity while preserving the event image, status, organizer context,
privacy policy, lifecycle, and registration controls. Created prototype event
details remain classified under the deliberate detail-hero contract.

The forum directory, topic editor/detail, persistent groups, journals,
mentorship, expert sessions, and administration now use the canonical header.
The directory also exposes all roots while rendering only the active root's
direct children; child selection is validated and filtered server-side through
an indexed Eloquent scope.

The category tree is now a dedicated anonymous Blade component in the main
discussion column instead of a narrow left sidebar. Its progressive root
catalogue, selected-category purpose, breadcrumbs, and complete child grid
retain the existing query-string contract and zero-query warm tree cache. The
desktop directory keeps only the contextual knowledge/update rail; tablet and
mobile layouts move that rail below the topic stream without horizontal page
overflow. Display labels use Unicode-safe sentence capitalization while the
source manifest, stable keys, aliases, and slugs remain unchanged.

Shared actions and filter chips now retain the 44-pixel touch target at desktop
as well as mobile widths. The Blade localization audit also understands bound
component props, preventing already-localized values from being rewritten and
double escaped.

The complete isolated Pest run passed 2,484 tests and 80,398 assertions. The
275-test affected-domain run passed 3,488 assertions; Pint, Larastan,
localization, Vite, dependency audits, cache smoke, migration lifecycle,
isolated migration/seed/idempotency, forum-source preservation, and diff checks
also passed. Authenticated browser checks covered 16 route families at 375 px,
the forum at 1,440 px, a selected subcategory, `/meetups`, and an event
workspace with one canonical header, one `h1`, no horizontal page overflow,
44-pixel actions, no console errors, and no SQL error. The live classification
ledger covers all 111 current first-party GET routes. The stable requirement
ID, remaining locale/zoom/forced-colors fixtures, deliberate detail/workspace
exception audit, scoped publication, and final global audit remain open; this
delivery is not globally complete.

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

## Current Delivery: Canonical Places And Venues

Status: `verified` on 2026-08-03

- Replaced the static place identity boundary with policy-scoped Eloquent
  places, venue areas, exact-location grants and audits, and dynamic detail
  routes for newly submitted places.
- Retained the complete server-rendered directory, map alternative, emergency
  clinic mode, EN/LT/RU content, and encrypted per-user saves, follows, visits,
  private check-ins, corrections, warnings, reviews, and questions.
- Added reversible indexed migrations, production Actions, privacy-safe public
  projections, idempotent authority/catalog seeders, explicit factories, and
  event-to-place/venue links.
- Verified 13 directory tests with 140 assertions, 20 authority tests with 153
  assertions, scoped Pint and Larastan, 126 fresh migrations across 211 tables,
  repeated seed stability, dependency audits, Vite/cache compilation, and the
  final serial suite of 2,579 tests with 81,626 assertions.
- Verified authenticated desktop/mobile browser flows for `/places` and
  `/places/vingis-quiet-loop` with no overflow, broken images, raw translation
  keys, unnamed controls, console errors, or protected-address disclosure.
  The browser gate also caps place-card height at 480px on desktop and 720px on
  mobile; the final measured ranges were 384-473px and 614-654px respectively.

The authority, privacy, lifecycle, schema, and acceptance decisions are
recorded in
`docs/plans/portal-place-location-venue-authority-work-package.md`.

## Current Delivery: Canonical Portal Discovery

Status: `release verified` on 2026-08-03; attributable publication prepared.

- Replaced the static four-card discovery demonstration, fictional Richmond
  query, local pulse, trending topics, and weekend promotion with one bounded
  database-backed recommendation hub.
- Reused current event, group, place, expert, pet, user-actor, publication,
  social-block, localization, media, status, action, shell, and deep-link architecture.
- Added strict query/category validation, account and actor block filtering,
  `is_recommendable` filtering, public-only projections, and explicit omission
  of exact event/place locations.
- Added user-owned, policy-scoped, idempotent item/category hide and reset
  preferences with reversible indexed schema and a factory.
- Added canonical discovery directions, toolbar, sections, cards, empty/hidden
  states, EN/LT/RU translations, constant-query tests, and a repeatable browser
  gate for 1440/375/320px including long Lithuanian content.
- Added active verified member recommendations, a stable policy- and
  block-scoped `members.show` profile, and visible post recommendations through
  `ContentPublication::visibleTo()` and the canonical content route.
- Targeted evidence passes 12 feature tests / 121 assertions and the linked-media
  discovery contract. The final serial suite passes 2,657 tests / 84,589
  assertions; fresh migration plus repeat seeding, Larastan, Pint, dependency
  audits, production Vite, and the three-viewport browser gate also pass with
  no overflow, broken media, private-location leak, unnamed control, raw key,
  or console error. The all-category service projection is 12 bounded queries
  for 16 recommendations across all seven sections in the current demo world.

The baseline audit, architecture decisions, complete delivery plan, and release
evidence are in `docs/plans/discover-modernization-plan.md`; the stable page
contract is `docs/portal/discovery.md`.

## Current Delivery: Canonical Pet Workspace

Status: `implemented and release verified` on 2026-08-03.

- Replace `/pets` static nearby-pet fixtures and session-only Follow controls
  with one policy-aware Eloquent workspace for owned and actively shared
  `PetProfile` records.
- Keep cross-user pet recommendations under `/discover?category=pets`, expose
  pending manager invitations separately, and reuse canonical creation,
  profile, care, health, media, status, action, and pagination contracts.
- Validate query/filter/sort URL state, paginate at twelve, eagerly load the
  current manager and primary protected media, and keep query growth constant.
- Provide purpose-specific empty/filtered-empty states and full EN/LT/RU copy.
- Verify desktop and mobile behavior through the repeatable loopback Chrome
  gate in `scripts/pet-workspace-browser-check.mjs`.
- Release evidence includes the 2,670-test/84,934-assertion serial suite,
  zero-error Larastan, dependency audits, production Vite build, isolated fresh
  migration/seed and repeat-seed checks, and the three-viewport browser gate.

The baseline, decisions, implementation passes, security boundaries, and gate
evidence are recorded in `docs/plans/pet-workspace-modernization-plan.md`.
