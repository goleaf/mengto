# Phase 8 Lost And Found Work Package

Last reviewed: 2026-07-31.

## Requirement Scope

This work package covers these exact requirement identifiers:

- `forum.feature.3013`, `forum.feature.3014`, `forum.feature.3015`;
- `forum.feature.3016`, `forum.feature.3017`, `forum.feature.3018`,
  `forum.feature.3019`, `animal.taxonomy.0107`;
- `forum.feature.3020`, `forum.feature.3021`, `forum.feature.3022`,
  `forum.feature.3023`, `forum.feature.3024`, `forum.feature.3025`,
  `forum.feature.3026`, `forum.feature.3027`, `forum.feature.3028`,
  `forum.feature.3029`, `forum.feature.3030`, `forum.search.0121`,
  `forum.feature.3031`, `forum.feature.3032`, `forum.feature.3033`,
  `forum.feature.3034`, `forum.feature.3035`, `forum.feature.3036`,
  `forum.feature.3037`, `forum.feature.3038`, `forum.reputation.0280`,
  `forum.feature.3039`, `forum.feature.3040`, `forum.feature.3041`,
  `forum.feature.3042`, `forum.feature.3043`, `forum.feature.3044`,
  `forum.feature.3045`, `forum.search.0122`, `forum.feature.3046`,
  `forum.reputation.0281`, `forum.feature.3047`, `forum.security.0018`,
  `forum.feature.3048`, `forum.moderation.0320`;
- `forum.test.0114`, `forum.test.0115`, `forum.test.0116`,
  `forum.test.0117`, `forum.reputation.0334`, `forum.test.0118`,
  `forum.test.0119`, `forum.test.0120`, and `forum.test.0121`.

No scoped identifier becomes verified until its own behavior is represented by
file-level and passing-test evidence in the deterministic requirement overlay.

## Current Implementation Analysis

The repository already has the correct primary domain. `SearchCase` is a
structured lost/found record with a unique public case number, private exact
location and contact data, generalized public coordinates, photos, animal
description, sightings, volunteers, sectors, tasks, public updates, local
alerts, poster/QR output, nearby clinics and shelters, reports, authorization,
factories, repeatable demo data, and 16 focused feature tests.

Important gaps remain:

- pet identity is represented only by an unscoped key and animal taxonomy by
  free-form strings;
- status updates are audited but do not have a dedicated immutable case event;
- the platform contact value is protected but no explicit contact-relay
  operation exists;
- duplicate prevention covers one owner's active pet only and does not provide
  advisory cross-account suggestions;
- false sighting and reward scam reports do not enter the unified moderation
  pipeline;
- reward disclosure has no structured, privacy-safe boundary;
- case type/status and supporting option catalogues contain hardcoded English;
- the domain has lost/found types and returned states but lacks explicit
  stolen, sighted, and reunited values required by the specification.

## Desired Result

Keep `SearchCase` as the canonical case entity and extend it additively.
Existing case IDs, public codes, slugs, sightings, tasks, volunteers, alerts,
reports, updates, photos, and URLs remain valid.

Every new or backfilled case may reference an owned `PetProfile`, one active
`Taxon`, and one compatible `DomesticClassification`, while preserving a safe
case-time animal snapshot. Unknown or unmapped animals continue to work.

The domain supports lost, found, sighted, and stolen case types and a distinct
reunited terminal state. Status changes append an immutable event. Public
reward information is limited to a safe statement; payment instructions and
private settlement data are rejected. Contact occurs through an encrypted,
idempotent relay request without revealing either party's private contact
details.

Duplicate detection is advisory. It uses bounded active-case candidates and
structured species/taxon, area, date, and animal-description signals. It never
merges or rejects a case automatically. False sightings and reward scams write
both compatibility reports and the unified moderation record.

## Affected Files

Expected additions:

- one additive lost-and-found integrity migration;
- `SearchCaseEvent` and `SearchContactRelay` models/factories;
- a typed contact-relay data object and action;
- a duplicate-candidate service;
- one contact-relay request/controller/route;
- EN/LT/RU lost-and-found catalogues;
- focused policy, privacy, idempotency, duplicate, migration, seed, and
  integration tests.

Expected modifications:

- `SearchCase`, `Sighting`, `PetProfile`, `Taxon`, and domestic classification
  relationships;
- search case/status enums, request validation, actions, presenter, policy,
  factories, and demo seeder;
- public and coordination Blade views;
- unified report subject support;
- data-model, security, testing, seeding, operations, changelog, progress,
  traceability, assumption, and ADR documents.

## Schema And Data Migration

Add nullable foreign keys for pet, taxon, domestic classification, duplicate
canonical case, and reunited confirmer. Add a versioned encrypted animal
snapshot, safe reward fields, lock version, reunion and archive timestamps,
plus leading composite indexes for actual lookup patterns.

Add immutable case-event and encrypted contact-relay tables. The relay uses a
unique idempotency key and bounded states. No current table is dropped or
renamed.

The production-safe backfill resolves only exact owner/profile keys and exact
stable taxonomy mappings. Ambiguous data remains unchanged and receives an
explicit review marker in snapshot metadata. It never infers sensitive
identity from title keywords.

## Rollback

Rollback removes only additive relations, projections, and new empty-capable
tables after dropping their indexes and foreign keys. Legacy string species,
breed, pet key, status updates, audit logs, and contact storage remain.
Operators must not roll back after new relay/event records become required by
deployed application code without first deploying compatible readers.

## Legacy Compatibility

- Existing route binding remains slug based.
- Existing public codes and posters remain.
- Existing `returned` and `self-returned` statuses remain readable.
- Existing string species/breed fields remain the display fallback.
- Existing reports continue to persist while unified moderation is added
  atomically for authenticated reporters.
- Existing public generalized-location behavior remains unchanged.

## Authorization And Validation

- Owners and coordinators retain private workspace access.
- Only active authenticated users may send a contact-relay message or a
  unified report.
- Relay recipients are derived server-side from the case owner.
- The sender cannot relay to their own case.
- Contact message, purpose, and idempotency key are validated.
- Taxon, breed, pet, sighting, and duplicate IDs are ownership/scoped before
  mutation.
- Reunited state requires owner/coordinator confirmation and preserves history.
- Reward text rejects payment credentials, transfer instructions, and direct
  contact disclosure.

## Translation And Interface

Move platform-controlled case types, statuses, species shortcuts, sizes,
microchip states, confidence/contact options, tasks, volunteer capabilities,
report reasons, sorting, relay states, and safety messages into the existing
EN/LT/RU architecture. Scientific names remain unchanged.

Public pages show a text status, safe reward statement, taxonomy context,
duplicate/canonical notice where applicable, and a protected relay form. They
never show exact location, hidden marks, contact values, relay message bodies,
or private taxonomy review metadata.

## Accessibility

All new fields have labels and associated errors. Status does not rely on
color. Contact submission has action-specific loading/disabled behavior where
Livewire is used later, but the normal HTML form remains fully operable.
Controls expose at least a 44px effective touch target, one page heading is
preserved, and location continues to have a textual alternative.

## Cache, Security, Privacy, And Abuse

Directory cache invalidation remains owned by `SearchCase`. Duplicate results
are not shared through permission-insensitive cache entries. Relay content is
encrypted, hidden from serialization, rate limited, retained for bounded
operations review, and never included in public presentation.

Exact locations, contact details, hidden marks, animal private snapshot fields,
reporter identity, and moderation evidence remain private. Duplicate
suggestions, search counts, and snippets include only publicly visible cases.

## Tests

Create or update tests for:

- all existing lost-and-found behavior;
- explicit stolen, sighted, and reunited states;
- safe pet/taxon/breed linking and ambiguous fallback;
- case-time snapshot privacy;
- exact/public location separation;
- relay authorization, encryption, idempotency, ownership, and public
  non-disclosure;
- advisory duplicate scoring and no automatic merge/rejection;
- immutable status history and safe closure;
- sightings, confirmation, and repeated submission;
- false-sighting and reward-scam unified moderation reports;
- reward validation and private-data rejection;
- poster/QR/share preservation;
- factory states, repeat seed, migration indexes, foreign keys, and backfill;
- localized option parity for EN, LT, and RU;
- bounded query counts and no private search exposure;
- direct route and action authorization.

## Acceptance Criteria

1. Every scoped requirement is mapped to an implemented behavior or remains
   conservatively unverified.
2. Existing lost/found data and URLs survive the additive migration.
3. Exact location and direct contact data remain private by default.
4. Lost, found, sighted, stolen, and reunited are explicit controlled values.
5. Pet and global taxonomy links are server-scoped and optional.
6. Status history and relay records are immutable or append-only.
7. Contact relay never discloses private contact values.
8. Duplicate detection remains advisory and public-scope safe.
9. False-sighting and reward-scam reports enter unified moderation.
10. Focused tests, Pint, Larastan, architecture/localization checks, fresh
    migration/seed, full suite, production build, and browser checks pass
    before evidence is marked verified.

## Verification Procedure

Run:

- focused lost-and-found, unified moderation, policy, migration, factory, and
  seed tests;
- full Pint and Larastan;
- architecture and localization tests;
- fresh isolated migration/seed and repeat seed;
- the complete PHP suite;
- Vite production build after interface changes;
- Playwright at mobile and desktop widths;
- deterministic requirement generation and final phase completeness review.

Completion evidence is recorded only after the commands finish successfully.

## Implementation Result

The scoped package is implemented and verified. Existing `SearchCase` IDs,
slugs, public codes, photos, sightings, updates, reports, alerts, tasks, and
volunteers remain authoritative. Migration `2026_07_31_000800` adds only
nullable/defaulted relations, snapshot/integrity fields, leading indexes, and
the new event/relay tables.

Implemented boundaries:

- owned active pet selection and optional active taxon/domestic classification
  links;
- encrypted case-time animal snapshot with species, breed, sex, age, size,
  color, temperament, and collar/accessory context;
- lost, found, sighted, stolen, and reunited controlled values;
- rounded public and encrypted exact location, map/text alternatives, bounded
  radius, poster, QR, share route, nearby clinics/shelters, sightings,
  volunteer tasks, and timeline;
- immutable case-created/status-changed/archive events and optimistic reunion
  and archive locking;
- encrypted idempotent contact relay with owner-derived recipient;
- safe reward disclosure, advisory duplicate matching, and unified
  false-sighting/reward-scam moderation reports;
- closed-case archival that removes public and poster access while preserving
  every operational relation and stopping active alerts/tasks/volunteers;
- reusable class-based Livewire taxonomy selector with locked configuration,
  bounded results, and singular-selection mode.

## Verification Evidence

- Lost/found and Livewire focused suite: 45 tests, 309 assertions, passed.
- Policy/schema regression slice: 32 tests, 151 assertions, passed.
- Architecture/localization/factory gates: 702 tests, 40,074 assertions,
  passed.
- Factory/taxonomy slice: 680 tests, 1,978 assertions, passed.
- Full serial Pest suite: 963 tests, 42,451 assertions, passed.
- Full Pint check: passed.
- Full Larastan level 5: zero errors.
- Fresh isolated verifier: 87 migrations, 122 tables, five demo users before
  and after repeated seed, both exits zero.
- Deterministic requirement generation/check: 7,284 records passed; 147 total
  repository requirements now carry verified evidence.
- Vite 8.2.0 production build: passed in 497 ms.
- Playwright at 375x812 and 1440x900: no main-content overflow, one `h1`, all
  workflow touch targets at least 44 px, and no current-page warning/error.

No scoped requirement remains blocked or intentionally not applicable.
