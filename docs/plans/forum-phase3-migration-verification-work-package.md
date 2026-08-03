# Phase 3 Migration Verification Work Package

Date: 2026-08-03

Status: implemented and verified

## Exact Scope

This package owns the final 13 open Phase 3 requirements:

- `forum.data.0001`, `forum.data.0003`, `forum.data.0004`, and
  `forum.data.0005`;
- `forum.data.0085` and `forum.data.0087` through `forum.data.0093`;
- `forum.data.0099`.

No requirement from another phase may inherit this package's evidence.

## Classification

The current repository already contains additive Schema Builder migrations,
foreign/unique/check constraints, leading-index inspection, state enums,
model casts, populated compatibility tests for sensitive packages, and an
isolated fresh-migration/repeat-seed verifier. The remaining gap is a global
executable proof that every migration file:

1. has explicit `up()` and `down()` operations;
2. applies in timestamp order on isolated SQLite;
3. rolls back in reverse order without an unhandled error;
4. reapplies after the complete rollback;
5. produces the exact migration ledger with no skipped or duplicate file;
6. accepts the complete production-safe seed twice after reapplication.

This is a verification and operational-control package. It must not add a
speculative migration merely to satisfy a generic source heading.

## Implementation Contract

1. Add a standalone verifier that creates one random database under the
   canonical system temporary directory and asserts the configured connection
   before any destructive command.
2. Compare the migration ledger to the sorted migration filenames after both
   applies.
3. Roll back exactly the applied count, require an empty migration ledger and
   absence of the application `users` table, then reapply all migrations.
4. Run the complete seeder twice after reapplication and require a stable user
   count.
5. Always disconnect and remove the temporary database in `finally`.
6. Add Pest coverage for the verifier and for migration-source architecture:
   every first-party migration has typed `up()`/`down()` and contains no raw
   SQL escape hatch.
7. Reuse `SchemaIntegrityTest` for leading foreign-key indexes and the existing
   domain tests for constraints, backed enums, casts, populated compatibility,
   and rollback/reapply behavior.

## Migration And Compatibility Strategy

- Schema change: none.
- Data migration: none.
- Production rollback: unchanged; each delivered package retains its own
  documented expand-and-contract/forward-fix boundary.
- Compatibility: the verifier uses a disposable SQLite database only and can
  never target the configured development or production database.
- Failure behavior: return non-zero, preserve the exact failing Artisan output
  in the exception, disconnect, and delete the temporary file.

## Acceptance

- RED proves the lifecycle verifier is absent.
- The focused migration lifecycle and schema-integrity tests pass.
- The verifier reports the exact current migration-file count, zero remaining
  ledger rows after rollback, exact second ledger parity, complete seed, and
  stable repeated seed.
- Pint, Larastan, the full sequential Pest suite, dependency audits, Vite,
  cache compilation, immutable source, and deterministic requirement
  generation pass.
- Only these 13 IDs move to `verified`, leaving Phase 3 with zero open IDs.

Stop without evidence if the verifier can address a non-temporary database,
one migration is skipped, rollback fails, the second ledger differs, seed
changes the stable identity count, a historical migration is edited, or an
unrelated dirty-tree file enters the commit.

## Final Verification

- Focused lifecycle contract: 2 tests and 11 assertions.
- Direct isolated cycle: 118 files applied, 0 ledger rows after rollback, 118
  files reapplied, 200 tables, and stable 5-user repeated seed.
- Related schema/factory/constraint/cast/populated-rollback slice: 1,611 tests
  and 4,795 assertions.
- Complete sequential repository suite: 2,362 tests and 78,760 assertions.
- Full Pint and Larastan passed; Composer strict validation and audit, npm
  audit, Vite 8.2.0 production build, and config/event/route/view cache
  compilation passed.
- Fresh isolated database verification applied 118 migrations, created 200
  tables, and preserved 5 users after repeated seeding.
- Immutable source preservation and deterministic generation of all 38,377
  atomic requirements passed.

The exact 13 requirements are verified. Phase 3 has no remaining open IDs;
this does not promote any requirement in another phase.
