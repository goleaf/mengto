# Phase 4 Wildlife-Coexistence Category Work Package

Date: 2026-08-03

Status: implementation complete; final verification pending

## Exact Scope

This package owns exactly `forum.category.0129` through
`forum.category.0187`: 59 open Phase 4 atoms for root category 23.

The source section contains 61 atoms. `forum.moderation.0010` and
`forum.moderation.0011` cover wildlife-crime and roadkill reporting and remain
open in Phase 7. Their labels stay in the immutable category manifest, but
they receive no status or behavioral claim from this package.

## Classification

The immutable manifest and synchronizer already contain the exact category-23
root and all 55 source labels. Existing aggregate tests prove catalogue-wide
counts, uniqueness, translations, and synchronization preservation, but no
test independently pins this source section or its persisted hierarchy.

This is an evidence gap rather than a production implementation gap. The
package adds a focused executable source/persistence contract and makes no
speculative schema, route, policy, cache, or presentation change.

## Implementation Contract

1. Pin the category number, stable key, slug, source name, purpose, and all 55
   ordered source labels in a focused Pest test.
2. Synchronize through the production Action and prove exact root/child stable
   keys, slugs, positions, and reviewed EN/LT/RU root rows with bounded eager
   loading.
3. Retain the two Phase 7 labels in the exact source list while withholding
   Phase 7 evidence.
4. Reuse the validated catalogue, synchronizer, Eloquent models, factory/seed
   infrastructure, and locale contract; do not build a parallel hierarchy.
5. Keep runtime queries, schema, routes, Blade, authorization, user content,
   IDs, and cache behavior unchanged.

## Acceptance

- The focused characterization contract passes and fails on any category-23
  source, ordering, hierarchy, key, slug, or reviewed-root-locale drift.
- The related category, multilingual, localization, and schema slice passes.
- Query delta is zero because production code is unchanged.
- Pint, Larastan, full Pest, fresh/repeat seed, rollback/reapply, audits, build,
  cache compilation, manifest/source, and deterministic requirements pass
  before evidence changes.
- Only the exact 59 Phase 4 IDs move to `verified`.

Stop without evidence if either Phase 7 ID is promoted, source labels change,
the hierarchy requires a behavior not represented by these Phase 4 atoms, or
an unrelated dirty-tree file enters the commit.

## Implementation Checkpoint

- Exact scope classification complete: 59 Phase 4 atoms selected and two
  Phase 7 reporting atoms explicitly excluded.
- Focused contract: 2 tests and 12 assertions passed.
- Related category, multilingual, localization, and schema slice: 48 tests and
  36,063 assertions passed.
- The page-identity double-escaping regression was corrected and its focused
  expert-session route test passed 1 test and 9 assertions.
- The next full Pest run reached 2,410 passing tests and 79,459 assertions,
  but the gate remains red because the parallel uncommitted organization
  package has six missing-factory errors, six missing leading indexes, and one
  localization architecture failure. The package does not claim that run.
- Full Larastan, Composer strict validation/audit, npm audit, Vite 8.2.0 build,
  and deterministic manifest/source/requirements checks passed.
- Full Pint is pending because a parallel uncommitted organization test still
  needs its own formatting; targeted Pint for this package passed.
- Evidence promotion, database/cache gates, final full-suite rerun, commit, and
  push remain pending a stable shared worktree.
