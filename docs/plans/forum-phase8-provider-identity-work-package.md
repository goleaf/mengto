# Phase 8 Adoption Provider Identity Work Package

Last reviewed: 2026-07-31.

## Requirement Scope

This work package covers these exact requirement identifiers:

- `forum.feature.3049`
- `forum.feature.3050`
- `forum.test.0092`

The package completes the identity-verification boundary of the existing
adoption, foster, and rescue case-management work. It does not claim to
implement every future organization-management requirement.

## Current Implementation Analysis

`AdoptionCase` distinguishes an organization from a private person, but its
`provider_verified` value is copied from the legacy
`listings.is_verified_seller` flag. That flag has no credential provenance,
review event, expiration behavior, or provider-purpose check.

The repository already has the stronger reusable boundary:

- `ExpertProfile` belongs to a platform user;
- `Credential` stores private evidence and public-safe metadata;
- credential review is independent, authorized, transactional, idempotent,
  auditable, expiring, suspendable, revocable, and appealable;
- credential evidence is hidden from serialization;
- karma and professional verification remain independent.

There is no general organization model. Introducing a second verification
system for adoption providers would duplicate the existing credential review
workflow and create conflicting public trust states.

## Desired Result

Every adoption case may link to the provider owner's expert profile and one
purpose-compatible credential. A private provider is verified only by a
current identity credential. An organization provider is verified only by a
current organization registration, organization role, shelter, rescue,
breeder, or organization-representative credential owned by the listing
provider.

Submitted and in-review credentials show a pending state. Expired, suspended,
revoked, and rejected credentials do not show as verified. The public status
is derived at read time so expiry remains correct without a scheduler, while a
stored projection supports bounded filtering and auditing.

## Affected Files

Expected additions:

- one additive migration for adoption-provider verification links and status;
- a controlled credential-type enum;
- an adoption-provider verification status enum;
- a provider verification resolver/synchronizer;
- focused provider identity, policy, Livewire, migration, and regression tests.

Expected modifications:

- `Credential`, `ExpertProfile`, `AdoptionCase`, `Listing`, and their factories;
- credential review and appeal actions;
- adoption case synchronization and Livewire presentation;
- expert-profile request validation and credential presentation;
- EN, LT, and RU credential/adoption translations;
- data-model, security, testing, seeding, progress, changelog, traceability,
  assumptions, and architecture-decision documentation.

## Schema Changes

Add nullable foreign keys from `adoption_cases` to `expert_profiles` and
`credentials`, plus a controlled provider identity status. Existing
`provider_verified` remains as a compatibility projection. The migration is
additive and does not rewrite any listing, application, event, credential, or
profile.

The credential must belong to the linked profile and the profile must belong
to the listing owner. These cross-table ownership rules are enforced under a
transaction by the domain synchronizer and covered by tests.

## Data Migration And Compatibility

`SynchronizeAdoptionCase` will refresh both new and existing adoption cases.
`AdoptionCaseSeeder` remains the production-safe, idempotent backfill path.
Legacy `is_verified_seller` values are preserved but no longer treated as
adoption identity proof.

Cases without a qualifying credential remain usable and visibly unverified.
No provider is automatically verified during migration.

## Rollback

Rollback removes only the new nullable links and status projection after
dropping their indexes and foreign keys. Existing provider type, compatibility
boolean, listing, credential, profile, application, and event records remain.
Re-running the prior code reads the preserved compatibility boolean.

## Authorization And Validation

- Credential owners may submit evidence but cannot independently verify it.
- Only active administrators who do not own the credential may review it.
- Adoption provider links are selected server-side; browser-provided
  credential or profile identifiers are never trusted.
- A credential cannot verify a listing owned by another user.
- Credential types are constrained to documented stable values at the request
  boundary.
- Organization-purpose credentials never imply veterinary, legal, or other
  professional authority.

## Translation And Interface

The adoption workflow displays a localized state and a short localized
explanation. It never exposes credential numbers, files, internal notes,
reviewer notes, or legal names. Credential type and verification-status labels
use the existing EN, LT, and RU catalogues.

## Accessibility

Identity status remains textual and does not rely on color or an icon alone.
Pending, verified, expired, suspended, rejected, and unverified states have
distinct readable labels.

## Cache, Security, Privacy, And Abuse

No permission-sensitive credential evidence is cached. Provider identity is
resolved through bounded indexed relations. A stale legacy seller flag cannot
create an adoption verification badge. Review, suspension, revocation, appeal,
and projection changes remain auditable.

## Tests

Create or update tests for:

- private-person identity credential matching;
- organization-purpose credential matching;
- rejection of a credential owned by another provider;
- rejection of an unrelated professional credential;
- pending, verified, expired, suspended, revoked, and rejected states;
- natural expiration without a scheduler;
- independent review and successful appeal propagation;
- preservation of private evidence;
- idempotent synchronization and append-only projection events;
- direct Livewire rendering of the current state;
- migration foreign keys, factories, seed rerun, and legacy flag preservation;
- EN, LT, and RU key parity.

## Acceptance Criteria

1. An adoption provider is never verified only because
   `listings.is_verified_seller` is true.
2. A current purpose-compatible credential owned by the listing provider is
   linked and displayed as verified.
3. A credential owned by another account or with the wrong purpose is ignored.
4. Expiration, suspension, revocation, and rejection remove the public
   verified state without deleting history.
5. Pending review is distinguishable from unverified and rejected states.
6. Credential evidence remains private.
7. Existing adoption listings, cases, applications, events, and credentials
   are preserved.
8. Focused tests, formatting, static analysis, architecture checks, fresh
   migration/seed, and the full suite pass before the three requirements are
   marked verified.

## Verification Procedure

Run:

- focused adoption and credential tests;
- direct Livewire component tests;
- factory and seeder tests;
- architecture and localization checks;
- Pint on changed PHP files;
- Larastan on affected first-party code;
- a fresh isolated migration and seed;
- the full PHP suite;
- the production frontend build when interface markup changes;
- a final requirement evidence and diff review.

Completion evidence is appended here and recorded in the deterministic
requirement overlay only after the observed checks pass.

## Implementation Result

Completed on 2026-07-31. Adoption cases now link to an owner-matched,
purpose-compatible credential selected exclusively on the server. Private
providers require current identity evidence; organization providers require a
current organization-purpose credential. The legacy seller flag remains
stored for compatibility but cannot create the public adoption identity state.

The stored projection covers bounded filtering and append-only events, while
natural expiration is derived at read time. Independent review, rejection,
suspension, revocation, and successful appeal review propagate to the adoption
case without exposing credential evidence.

Observed checks:

- focused provider, credential, and repeat-seed slice: 32 tests / 127
  assertions;
- full Pest suite: 936 tests / 41,585 assertions;
- full Pint check: passed;
- full Larastan: 0 errors;
- fresh isolated migration and seed: all 86 migrations and seeders completed;
- repeated database seed: 1 test / 8 assertions;
- Vite 8.2.0 production build: passed;
- Playwright at 375px and 1440px: current verified state rendered with one
  `h1`, no horizontal overflow, raw translation key, private credential leak,
  unnamed visible button, warning, or error; report checkbox labels expose
  44px touch targets.

All three scoped requirement IDs are recorded as verified in the deterministic
evidence overlay.
