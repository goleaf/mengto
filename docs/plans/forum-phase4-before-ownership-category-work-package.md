# Phase 4 Before-Ownership Category Work Package

Date: 2026-08-03

Status: implemented and verified

## Exact Scope

This package owns exactly `forum.category.0005` through
`forum.category.0068`: the source heading, stable key, purpose, subcategory
contract, and the 58 Phase 4 subcategory atoms for root category 21.

The same source section contains `animal.taxonomy.0019` and
`animal.taxonomy.0020`. They remain open in Phase 5 even though the category
manifest must retain their two labels to preserve the complete source list.
No other Phase 4 or Phase 5 requirement inherits this package's evidence.

## Classification

The source-derived manifest and synchronizer already contain category 21, but
the runtime catalogue validates only the root count. It does not fail closed
for a wrong checksum, schema version, aggregate subcategory count, duplicate
stable key/slug, invalid hierarchy, or malformed definition. In addition, a
warm locale tree still performs schema/existence queries before reaching the
cache.

This package is therefore a runtime integrity, query optimization, and exact
evidence package. It does not create schema or duplicate the category tree.

## Implementation Contract

1. Make the catalogue path injectable for isolated fixture validation while
   retaining the canonical manifest as the default.
2. Validate schema version, source checksum, exact 44-root and 1,637-child
   totals, root numbering, required fields, stable-key/slug format, global
   uniqueness, and parent prefixes before returning definitions.
3. Move schema/existence checks inside the locale cache closure so a warm tree
   executes zero database statements. Seeder and admin invalidation retain the
   current versioned locale keys.
4. Add a focused Pest contract that:
   - rejects a corrupted aggregate count;
   - proves the exact category-21 metadata and all 60 source labels;
   - proves exact manifest-to-database child keys, slugs, positions, and root
     translations after synchronization;
   - proves cold bounded loading and zero warm database queries.
5. Keep browser state, routes, Blade, schema, authorization, and existing
   category IDs unchanged.

## Acceptance

- RED demonstrates that corrupt manifest metadata is currently accepted and
  warm cached reads still query the database.
- The focused category test and existing forum category/multilingual tests
  pass.
- Manifest generation and immutable source checks pass.
- Pint, Larastan, full Pest, fresh/repeat seed, audits, build, and cache gates
  pass before evidence changes.
- Only the exact 64 IDs move to `verified`.

Stop without evidence if any category ID/slug changes, a source subcategory is
lost, the two Phase 5 labels are promoted, a custom category is overwritten,
the cache can leak across locales, or an unrelated dirty-tree file enters the
commit.

## Implementation Checkpoint

- RED: corrupt aggregate metadata was accepted and a warm tree executed 2
  database queries.
- Focused contract: 13 tests and 38 assertions passed after implementation.
- Related category, multilingual/cache, schema, and taxonomy-factory slice:
  72 tests and 5,949 assertions passed.
- Warm locale-tree query count: 2 before, 0 after; the cold path remains
  bounded to at most 6 statements.
- Complete sequential repository suite: 2,384 tests and 78,891 assertions.
- Full Pint and Larastan passed; Composer strict validation and audit, npm
  audit, Vite 8.2.0 production build, and config/event/route/view cache
  compilation passed.
- Fresh isolated database verification passed 118 migrations, 200 tables, and
  stable 5-user repeat seeding; complete rollback/reapply also passed.
- Exact 44-root/1,637-subcategory manifest generation, immutable source
  preservation, and deterministic 38,377-requirement generation passed.

The exact 64 requirements are verified. The two Phase 5 taxonomy labels in
the same source section and every other open Phase 4 requirement remain open.
