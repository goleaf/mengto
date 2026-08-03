# Forum Core Foundation Reconciliation Work Package

Status: implemented and verified

Last updated: 2026-08-03

## Purpose

Reconcile the first dependency-safe Phase 3 forum foundation slice against the
live schema, models, definitions, seeders, and tests before adding more forum
behavior. This package records evidence for behavior that already exists; it
does not rebuild the normalized topic-type foundation or infer completion for
adjacent schema capabilities.

## Exact Requirement Scope

This package owns exactly 28 atomic requirements from source section 43:

- `forum.data.0011`: configurable topic types;
- `forum.data.0012` through `forum.data.0035`: discussion, question, case,
  journal, guide, urgent request, emergency alert, lost animal, found animal,
  sighting, adoption listing, foster request, volunteer request, service
  review, product review, place review, event, poll, comparison, checklist,
  marketplace listing, support request, correction request, and identification
  request;
- `forum.data.0036`: organization announcement;
- `forum.data.0051`: do not add uncontrolled columns for every topic type;
- `forum.data.0052`: use a maintainable versioned structured-data approach.

The source also names research discussion. Its atom is
`forum.search.0033` in Phase 9, so the seeded value may be checked as supporting
compatibility evidence but that requirement remains open. The section heading
`forum.data.0010` and capability atoms `forum.data.0037` through
`forum.data.0050` also remain open until their complete runtime behavior is
implemented and independently verified. No requirement outside the 28 IDs
above may be marked verified by this package.

## Required Reading And Repository State

The package is grounded in:

1. `AGENTS.md` and the canonical documentation reading order;
2. source section 43 in `docs/requirements/forum-source-prompt.md`;
3. `docs/requirements/forum-master-requirements.md` and the generated JSON;
4. the generated traceability matrix and Phase 3 index;
5. `docs/plans/forum-master-plan.md`, `docs/plans/forum-current-progress.md`,
   and `docs/plans/forum-completion-plan.md`;
6. `docs/decisions/forum-architecture-decisions.md`,
   `docs/audits/forum-existing-system-audit.md`, and
   `docs/audits/forum-gap-analysis.md`;
7. the current topic enum, normalized model and migration, topic model,
   definition seeder, taxonomy backfill, factories, and forum tests.

The repository was clean on `main` at `7dd5533`, synchronized with
`origin/main`, when this package was selected. Work remains main-only and must
preserve unrelated user changes.

## Reconciliation Result

### Evidence gaps selected by this package

All 28 scoped requirements are implemented but lack atomic evidence:

- `App\Enums\ForumTopicType` contains every scoped stable type key without
  using localized display text as identity.
- `forum_topic_types` stores each definition under an internal primary key and
  a unique stable key, with translation keys, a schema version, JSON field
  schema, JSON configuration, moderation and lifecycle capabilities.
- `ForumTopicType` casts schema/configuration JSON and boolean capabilities to
  typed PHP values and exposes an active scope.
- `ForumTopicTypeSeeder` synchronizes system definitions by stable key with an
  idempotent Eloquent upsert. It preserves database IDs, topic relations, and
  separately created non-system definitions.
- `forum_topics` links to a normalized type and stores one versioned
  `structured_data` JSON document instead of adding a column for every
  possible topic-type field.
- Existing high-value workflows use dedicated relations and tables where
  queryability, ownership, privacy, or lifecycle requires them.

### Implementation gaps deliberately left open

The current definition rows describe only part of the configurable runtime
contract. Category restrictions, per-type permissions, complete validation,
archival, contact restrictions, attachment rules, SEO, privacy, and
notification behavior are not all enforced by one authoritative boundary.
Those atoms stay open and require a later RED-first implementation package.

## Acceptance Criteria

The focused test must prove that:

1. every selected source topic type has a matching string-backed enum case and
   an active system definition;
2. definitions use unique stable keys and non-empty EN/LT/RU translation
   contracts instead of translated names as identifiers;
3. every definition has a positive schema version, typed field schema, and
   typed configuration;
4. the topic model casts `structured_data` and its version and relates it to
   the normalized definition;
5. the schema uses normalized foreign keys plus versioned JSON and does not
   contain per-type columns for source-specific fields;
6. repeated definition seeding preserves the same system IDs, an attached
   topic, and a custom non-system definition;
7. the focused test, related taxonomy tests, architecture tests, Pint,
   Larastan, the full serial suite, fresh isolated migration/seed, dependency
   audits, and the production build pass;
8. exact requirement evidence is added only after the observed gates pass.

## Expected Changes

- Add `tests/Feature/Forum/ForumTopicTypeSchemaContractTest.php` as the
  executable reconciliation contract.
- Update this package, current progress, data-model/testing documentation, and
  `docs/traceability/forum-requirement-evidence.json` with observed evidence.
- Regenerate the forum requirement catalogue and matrices through the
  canonical generator.

No production code or migration is planned. If the focused contract discovers
a real behavior gap, stop evidence publication, reclassify the affected atom
here, add a RED test, and implement the smallest additive fix before marking
that atom verified.

## Query And Data-Safety Budget

This package adds no request-time query. The test may query bounded definition
rows and schema metadata. Production seeding remains one bounded enum pass and
one Eloquent upsert. No raw SQL, destructive rewrite, remote taxonomy lookup,
or broad production collection is introduced.

## Observed Verification Evidence

- Focused topic-type schema contract: 3 tests, 486 assertions.
- Related category seed, taxonomy factory, and architecture regression: 61
  tests, 26,837 assertions.
- Full sequential repository suite: 2,040 tests, 73,559 assertions.
- Fresh isolated database: 111 migrations, 191 tables, and a repeated seed
  preserved the user count at 5.
- Pint passed; Larastan reported zero errors.
- Composer validation and audit passed; npm audit reported zero
  vulnerabilities; the Vite 8.2.0 production build passed.
- Deterministic source preservation and requirement generation passed after
  the exact evidence overlay was regenerated.

The selected 28 atoms are therefore verified as existing behavior. The
neighboring Phase 3 capability atoms and all other forum requirements remain
open.

## Verification And Stop Conditions

Run database-backed checks sequentially. Required commands are:

```bash
php artisan test --compact tests/Feature/Forum/ForumTopicTypeSchemaContractTest.php
php artisan test --compact tests/Feature/Forum/ForumCategorySeedTest.php tests/Feature/Database/ForumTaxonomyFactoryTest.php
php artisan test --compact tests/Feature/ArchitectureComplianceTest.php
vendor/bin/pint --test
PAO_DISABLE=1 vendor/bin/phpstan analyse --memory-limit=1G --no-progress
php artisan test --compact
php scripts/verify-fresh-database.php
composer validate --strict
composer audit
npm audit --audit-level=high
npm run build
php scripts/preserve-forum-source-prompt.php --check
php scripts/generate-forum-requirements.php --check
git diff --check
```

Stop and leave affected IDs open if a definition is missing, a seed changes a
stable ID or deletes custom data, the schema requires destructive conversion,
or any relevant gate exposes an unresolved defect. Successful reconciliation
does not imply that Phase 3 or the forum as a whole is complete.
