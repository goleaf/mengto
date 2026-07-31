# Pet Profile Foundation Work Package

Date: 2026-07-31

Status: verified foundation package; full pet-profile master scope remains open

## Requirement IDs

This package selects the following exact inclusive ID intervals. Individual
records remain planned unless every acceptance condition relevant to that
record is met and linked in the evidence overlay.

- Source-control contract: `forum.feature.2561`, `forum.feature.2562`,
  `forum.plan.0032`, `forum.plan.0033`.
- Core identity and actor separation: `pet.profile.0001-pet.profile.0042`,
  `pet.profile.0070-pet.profile.0091`, and
  `pet.profile.0132-pet.profile.0176`.
- Minimal creation and draft semantics: `pet.creation.0020-pet.creation.0035`,
  `pet.creation.0059-pet.creation.0102`, and
  `pet.creation.0170-pet.creation.0186`.
- Ownership and delegated management: `pet.ownership.0005-pet.ownership.0247`.
- Broad and section privacy: `pet.privacy.0003-pet.privacy.0045`.
- Audit and technical aggregate rules: `pet.data.0002-pet.data.0032` and
  `pet.data.0113-pet.data.0256`.
- First-release foundation: `pet.release.0001-pet.release.0041`.
- Executable basic scenarios: `pet.scenario.0001-pet.scenario.0039`.

## Current Implementation Analysis

`PetProfile` has a permanent key but only one owner FK, free string state,
broad visibility, and encrypted catch-all metadata. Existing generic actions
can create/update a private row, but they do not provide idempotency, manager
roles, typed permissions, immutable lifecycle events, field privacy, or a
dedicated accessible workflow. The public profile remains demo-oriented.

## Desired Result

An existing or newly created pet retains one canonical record. Every manager
uses an individual account and timed role. Critical abilities are explicit.
Minimal creation defaults to a private draft and is idempotent. Lifecycle and
privacy changes are server-authorized, versioned, audited, and immediately
reflected in viewer-aware queries. Existing IDs, owner links, slugs, keys, and
adjacent module records remain valid.

## Files

Expected new files:

- one additive pet-foundation migration;
- pet profile status/visibility, manager role/status, permission, evidence,
  and privacy audience enums;
- manager, privacy-setting, lifecycle-event, and slug-alias models/factories;
- minimal creation data/form/action support;
- manager invitation/acceptance/revocation, lifecycle transition, and privacy
  update actions;
- viewer-aware pet query service and production-safe ownership backfill
  command;
- class-based Livewire create/manage components and separate Blade views;
- EN/LT/RU keys and focused feature tests.

Expected modified files include `PetProfile`, `User`, `PetProfilePolicy`, the
existing create/update/privacy actions, grouped pet routes, database seed
orchestration where required, canonical docs, and traceability evidence.

## Schema Changes

Expand `pet_profiles` with nullable taxonomy links, typed identity/lifecycle
metadata, stable creation idempotency, optimistic version, discoverability,
and lifecycle timestamps. Add normalized manager memberships, layered privacy
settings, immutable pet events, and slug aliases. Add foreign, unique, and
query-path indexes. Do not drop or reinterpret legacy columns.

## Data Migration

The migration creates schema only. A resumable command iterates existing pets
by ID and creates one accepted primary-owner membership from each current
`user_id`; it also initializes privacy from the existing broad visibility.
Reruns are no-ops. Ambiguous or invalid rows are retained and reported rather
than deleted. Existing rows remain authorized through the compatibility owner
path until their membership exists.

## Rollback

Rollback drops only the new tables, indexes, foreign keys, and nullable
columns. It never deletes a pet or modifies medical, care, device, search,
adoption, event, report, media, social, or taxonomy records. Deployment must
not run the down migration after later features depend on the new tables.

## Legacy Compatibility

`user_id`, `profile_key`, owner-scoped `slug`, string species/breed,
`visibility`, and encrypted `profile_data` remain readable and writable.
Existing demo URLs and generic action submissions keep working. New canonical
routes use `profile_key`, so a name or slug change cannot break identity.

## Authorization And Validation

Policies resolve accepted, unexpired memberships and explicit permissions on
the server for every action. Generic edit never grants transfer, delete,
medical, exact-location, device, microchip, adoption, transaction, or memorial
rights. Form objects validate minimal name, species/unknown selection,
relationship, privacy, and idempotency key. Public Livewire properties are
treated as untrusted and immutable IDs are locked.

## Translation And Interface

All platform text and enum labels use existing EN/LT/RU catalogues. The
minimal form is one concise screen; advanced management is separated by task.
Inputs have labels and linked errors, status has text plus icon, focus order is
logical, controls meet touch targets, and content reflows at 320px/200% zoom.
No query or service resolution occurs in Blade.

## Cache, Search, And Counters

Viewer-aware projections are not cached until a permission-context key and
deterministic invalidation service exist. Privacy/lifecycle updates invalidate
the profile, public discovery, search, QR, and recommendation cache namespaces.
This package does not introduce a new search server. Counts use aggregate
queries outside loops.

## Security, Privacy, And Abuse Risks

- Creation never proves ownership; creator relationship determines initial
  role and evidence state.
- New profiles are private drafts by default.
- Exact address, routine, medical data, credentials, evidence, and private
  metadata never enter public projections.
- Replayed requests cannot create duplicate profiles, memberships, or events.
- Manager expiry and revocation are enforced at request time.
- Ownership dispute/transfer/delete remain deny-by-default until their
  dedicated workflows are implemented.

## Tests

Create migration/rollback, factory, backfill idempotency/preservation,
minimal-create idempotency, enum casts, status transition, optimistic lock,
manager permission matrix, revoked/expired access, direct Livewire action,
privacy projection, cache invalidation, canonical URL, and query-count tests.
Update existing social persistence and architecture tests. Run targeted tests
serially, then full Pest, Larastan, Pint, Vite, cache compilation, dependency
audits, and mobile/desktop browser checks.

## Documentation

Update pet audits, decisions, master/current plans, architecture, data model,
privacy/security, migrations, rollback/recovery, testing, changelog, final
living audit, and requirement evidence.

## Acceptance Criteria

1. Existing pet IDs and compatibility keys survive migration and rollback.
2. Every existing pet receives exactly one accepted primary-owner membership
   after repeated backfill.
3. Minimal creation is private, draft, and idempotent.
4. A second manager can act only within explicit active permissions.
5. Revocation or expiry blocks the next server request, including Livewire.
6. Lifecycle/privacy changes append immutable actor-attributed events.
7. Public queries never reveal private profiles or protected sections.
8. No new N+1 path, unbounded query, raw SQL, Blade query, or direct service
   resolution is introduced.
9. EN/LT/RU parity, accessibility, focused tests, and repository quality gates
   pass before selected IDs are verified.

## Verification And Completion Evidence

Completed evidence:

- isolated fresh SQLite applied 100 migrations across 177 tables;
- repeat seed/backfill retained two pets and stable 2/2/2/2 manager, privacy,
  alias, and lifecycle counts;
- rollback and re-application retained both populated pet rows;
- pet foundation plus legacy compatibility passed 21 tests and 1,603
  assertions;
- final serial repository suite passed 1,748 tests and 68,172 assertions in
  103.288 seconds;
- full Pint, Larastan level 5, Composer validation/audit, npm audit, Vite,
  config/event/route/view cache compilation, source checksum, requirement
  generation, and diff checks passed in the package cycle;
- desktop/mobile/320px browser checks passed semantic landmark, overflow,
  label, duplicate-ID, media/table, touch-target, and console gates;
- 205 exact pet IDs are verified in the evidence overlay. Selected interval
  members without complete evidence remain planned/discovered.
