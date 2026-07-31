# Phase 8 Adoption, Foster, And Rescue Work Package

Last reviewed: 2026-07-31.

## Requirement Scope

This work package implements the following exact requirement identifiers:

- `forum.feature.3049`, `forum.feature.3050`, `forum.feature.3051`,
  `forum.feature.3052`, `forum.test.0092`, `forum.feature.3053`,
  `forum.feature.3054`, `animal.taxonomy.0108`, `forum.feature.3055`,
  `forum.feature.3056`, `forum.feature.3057`, `forum.feature.3058`,
  `forum.feature.3059`, `forum.feature.3060`, `forum.feature.3061`,
  `forum.feature.3062`, `forum.feature.3063`, `forum.feature.3064`,
  `forum.plan.0074`, `forum.feature.3065`, `forum.feature.3066`,
  `forum.feature.3067`, `forum.feature.3068`, `forum.feature.3069`,
  `forum.feature.3070`, `forum.feature.3071`, `forum.feature.3072`,
  `forum.feature.3073`, `forum.feature.3074`, `forum.feature.3075`,
  `forum.feature.3076`, `forum.feature.3077`, `forum.feature.3078`,
  `forum.feature.3079`, `forum.feature.3080`, `forum.feature.3081`,
  `forum.security.0019`, `forum.moderation.0321`, `forum.plan.0075`,
  `forum.feature.3082`.
- Related domain and verification requirements: `forum.feature.3317`,
  `forum.feature.3318`, `forum.security.0036`, `forum.security.0037`,
  `animal.taxonomy.0716`,
  `forum.test.0123`, `forum.test.0124`, `forum.test.0125`,
  `forum.moderation.0379`, and `forum.test.0126`.

No identifier in this list may become verified until its own evidence exists in
the generated compliance data and final matrix.

## Current Implementation Analysis

`Listing` already provides the authoritative public animal listing, owner or
shelter identity, moderation state, location, species summary, fee prohibition,
transport options, reporting, and safe platform contact. Adoption listings
already enter manual moderation and cannot behave as ordinary paid sales.

`Reservation` currently records a minimal adoption questionnaire and supports
request, accept, decline, cancel, and completion. It remains the marketplace
request record for backward compatibility, but it does not provide an
adoption-specific state machine, taxon and breed relations, private applicant
data isolation, home checks, references, meetings, contracts, trial periods,
foster transfers, or append-only adoption history.

The application has no general organization model. Existing `seller_type`,
`is_business`, `business_name`, and `is_verified_seller` fields therefore
remain the canonical provider identity boundary for this package.

## Desired Result

An adoption listing has one structured adoption case. The case links the
existing listing to optional pet, taxon, and domestic classification records
and stores only safe public animal and placement facts. Applications are
private, encrypted records visible only to the applicant, listing owner, and
authorized administrators. Valid state transitions are transactional,
idempotent, authorization-checked, optimistic-lock aware, and represented in an
append-only event history.

The existing listing URL, owner identity, reports, reactions, reservations,
orders, attachments, and public marketplace behavior remain unchanged.

## Affected Files

Expected additions:

- one additive migration for adoption cases, applications, and event history;
- adoption case/application/provider/placement state enums;
- `AdoptionCase`, `AdoptionApplication`, and `AdoptionEvent` models;
- corresponding factories and model relationships;
- typed application input data and application/transition actions;
- case and application policies;
- a class-based Livewire application workflow with a separate Blade view;
- EN, LT, and RU translations;
- a production-safe synchronization seeder and development demo graph;
- feature, policy, privacy, transition, idempotency, factory, seeder, and
  Livewire tests.

Expected modifications:

- `Listing`, `PetProfile`, `Taxon`, and `DomesticClassification` relationships;
- the marketplace listing view and presenter;
- `DatabaseSeeder` orchestration;
- architecture, data model, seeding, security, testing, progress, changelog,
  and traceability documentation.

## Schema And Migration Strategy

The migration is additive. It creates:

- a one-to-one `adoption_cases` record keyed by an immutable case number and
  existing `listing_id`;
- private `adoption_applications` keyed by an idempotency UUID and unique
  applicant/case pair;
- append-only `adoption_events` for both case and application transitions.

Foreign keys protect listing, user, pet, taxonomy, classification, reviewer,
and event ownership. Compound indexes cover owner/application queues and case
status browsing. Applicant profile, references, home-check notes, contract
metadata, and review metadata use encrypted casts and are never selected for
public presentation.

Existing adoption listings are synchronized by stable listing ID. Ambiguous
pet, taxon, and breed matches remain null and are not guessed from titles.

## Data Migration And Backfill

The synchronizer creates a case for each existing adoption listing without
changing the listing or existing reservation. It copies only deterministic
public fields from the listing and records a `legacy-listing-synchronized`
event. Existing reservation questionnaires are not copied because their
semantics and consent do not prove that they may be promoted into the new
application workflow. They remain available through the legacy listing flow.

## Rollback

Rollback removes only the new adoption tables after foreign-key checks. It does
not alter or delete listings, reservations, orders, reports, attachments,
users, pet profiles, taxonomy, or administrator-created data. Application
rollback in production requires an export and retention review because the
new tables can contain private case records.

## Legacy Compatibility

Existing marketplace and listing actions remain functional. New applications
are linked to the authoritative listing and do not create duplicate public
adoption posts. Existing adoption reservations remain readable and actionable.
Routes and slugs are preserved.

## Authorization And Validation

- Active verified members may apply only to published, approved adoption cases
  they do not own.
- Applicants may inspect and withdraw only their own applications.
- Listing owners may view and transition applications for their own cases.
- Administrators may review, but all direct Livewire calls reauthorize.
- Applicant, reviewer, case, and listing identifiers are reloaded server-side.
- Application text, enum state, dates, currency, fee, references, consent, and
  structured arrays are validated and normalized.
- Owners may not silently edit applicant-supplied private information.

## Translation And Interface

All platform text is added to the existing EN/LT/RU language files. The
Livewire component stores only the listing ID, small form state, selected
application ID, target state, and optimistic lock version in browser-visible
state. It provides empty, loading, validation, success, restricted, and
offline states.

## Accessibility

The workflow uses native labels, field error associations, a summary alert,
semantic headings, action-specific loading text, keyboard-accessible controls,
non-color status labels, and no drag-only interaction.

## Cache, Security, Privacy, And Abuse

No permission-sensitive application collection is cached. Listing and category
caches remain unchanged. Private applicant data is encrypted, hidden from model
serialization, omitted from public presenters, and never written to audit
metadata. Submission is throttled by the existing authenticated route and
idempotency/uniqueness constraints. Owners cannot apply to themselves, and
repeated applications reuse the existing case/applicant boundary.

## Tests

Create or update tests for:

- additive migration constraints and fresh migration;
- factory/default and every enum-backed state;
- deterministic synchronization and rerun idempotency;
- private applicant serialization and public page exclusion;
- owner, applicant, unrelated user, blocked user, and administrator policies;
- direct Livewire action authorization;
- valid and invalid transitions;
- duplicate submission and stale optimistic-lock handling;
- withdrawal, screening, home check, references, meeting, reservation,
  contract, trial, adoption, foster, transfer, return, failure, and closure;
- append-only history after closure;
- listing report compatibility;
- taxon and breed links;
- EN/LT/RU rendering and validation;
- no N+1 query pattern in the bounded owner queue.

## Acceptance Criteria

1. Existing adoption listings receive exactly one structured case.
2. Public case data includes required animal, placement, fee, and transport
   fields without private applicant content.
3. Applicants can submit one validated idempotent private application.
4. Only authorized parties can inspect or transition an application.
5. Every transition follows the state map and writes an immutable event.
6. Adoption, foster, transfer, return, failure, and closure preserve history.
7. Pet/taxon/breed relations are optional and never inferred unsafely.
8. Listing reports continue through the unified reporting boundary.
9. Fresh migration, seed rerun, target tests, Pint, Larastan, architecture,
   localization, and production build checks pass.

## Verification Procedure And Completion Evidence

Run, record, and preserve the observed result of:

- focused migration/factory/seeder tests;
- adoption feature, policy, privacy, transition, concurrency, and Livewire
  tests;
- relevant marketplace regression tests;
- Pint on modified PHP;
- Larastan on affected application classes;
- localization architecture checks;
- fresh isolated migration and seed;
- production Vite build after interface changes;
- phase diff, requirement evidence, and preservation review.

Evidence is recorded in `forum-current-progress.md`, the compliance evidence
overlay, and the final completeness audit. A file existing is not sufficient
evidence.

## Implementation Result

The additive schema, models, policies, actions, Livewire form/component/view,
EN/LT/RU catalogues, factories, seeders, legacy synchronization, unified
listing-report bridge, and transition tests are implemented. Adoption,
screening, home check, references, meeting, reservation, contract, trial,
follow-up, return, failed placement, foster placement, foster transfer, and
closure all use controlled transactional transitions and append-only events.

The exact field and safety requirements are recorded as verified in
`docs/traceability/forum-requirement-evidence.json`. The broad package headings
`forum.feature.3049` and `forum.feature.3050`, plus identity-verification
requirement `forum.test.0092`, are now verified through the independent,
purpose-compatible credential boundary documented in
`forum-phase8-provider-identity-work-package.md`.

Observed checks:

- focused adoption and marketplace tests: 20 passed, 154 assertions;
- factory and enum-state slice: 517 passed, 1,526 assertions;
- selected Larastan: 0 errors;
- deterministic requirement generation/check: 7,284 records;
- fresh isolated migration and full seed: passed;
- translation check and production Vite build: passed;
- Playwright at 375px and 1440px: Lithuanian fallback correct, no horizontal
  overflow, no current-page console warnings or errors.
- provider identity follow-up: 32 focused tests / 127 assertions, full Pest
  936 / 41,585, Larastan 0 errors, all 86 migrations and seeders completed,
  repeat seed passed, production build passed, and EN identity rendering
  passed browser privacy/responsiveness checks at 375px and 1440px.
