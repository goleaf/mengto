# Phase 4 Special-Needs Category Work Package

Date: 2026-08-03

Status: implemented and verified

## Exact Scope

This package owns exactly 63 open Phase 4 requirements:

- `forum.category.0069` through `forum.category.0128` for root category 22;
- `forum.category.1429` for locale-scoped category-translation caching;
- `forum.category.1431` for seed-synchronization cache invalidation;
- `forum.category.1454` for complete system-category locale rows.

No adjacent category, generic administration, or Phase 5 requirement inherits
this package's evidence.

## Classification

The immutable manifest and synchronizer already contain the exact category-22
source hierarchy and three locale rows per system category. Root translations
are reviewed in EN/LT/RU; subcategory source names remain English until a
reviewed locale-specific value exists. The current tree resolver selects a
target-locale row even when `is_reviewed` is false, so an unfinished or
machine-suggested value can replace the reviewed fallback.

This package closes that runtime trust gap and proves the exact source,
persistence, fallback, cache, and invalidation chain. It adds no schema,
parallel translation system, or automatic translation provider.

## Implementation Contract

1. Include `is_reviewed` in every bounded translation projection used by the
   category tree and root-option resolver.
2. Select a requested-locale value only when reviewed; otherwise select the
   reviewed configured fallback-locale value, then the stable server fallback.
3. Preserve locale-scoped cache keys and current synchronization/admin
   invalidation behavior.
4. Add a focused Pest contract for:
   - exact category-22 root metadata and all 54 ordered subcategories;
   - exact manifest-to-database keys, slugs, positions, and locale rows;
   - rejection of an unreviewed target-locale override;
   - adoption of the same override only after review;
   - all-system-category locale completeness and seed cache invalidation via
     the existing multilingual contract.
5. Keep routes, Blade, schema, IDs, source labels, and user content unchanged.

## Acceptance

- RED demonstrates that an unreviewed target-locale name is currently shown.
- The focused test and category/multilingual/cache regression slice pass.
- The query budget remains at zero statements for a warm locale tree.
- Pint, Larastan, full Pest, fresh/repeat seed, rollback/reapply, audits, build,
  cache compilation, manifest/source, and deterministic requirement checks
  pass before evidence changes.
- Only the exact 63 IDs move to `verified`.

Stop without evidence if a source name changes, a locale cache leaks into
another locale, an unreviewed value is rendered, a custom translation is
overwritten, a schema change appears, or an unrelated dirty-tree file enters
the commit.

## Implementation Checkpoint

- RED: an unreviewed Lithuanian child name replaced the reviewed English
  fallback in the rendered locale tree.
- Focused contract: 4 tests and 17 assertions passed after implementation.
- Related category, multilingual/cache, localization, and schema slice: 46
  tests and 36,051 assertions passed.
- Query shape is unchanged; the added review flag travels in the existing
  eager-loaded translation queries and warm locale-tree reads remain at zero
  database statements.
- Complete sequential repository suite: 2,396 tests and 79,143 assertions.
- Full Pint and Larastan passed with zero findings; Composer strict validation
  and audit, npm audit with zero vulnerabilities, and the Vite 8.2.0
  production build passed.
- Fresh isolated SQLite verification passed 118 migrations, 200 tables, and a
  stable 5-user repeat seed. The complete migration cycle passed 118-to-0-to-
  118 with the same repeat-seed result.
- Config, event, route, and view cache compilation passed, followed by a clean
  optimize clear.
- The exact 44-root/1,637-subcategory manifest, immutable source checksum, and
  deterministic 38,377-requirement checks passed.
- The package changes no route, Blade template, schema, or browser
  interaction, so browser-only and migration-content gates are not applicable.

The exact 63 requirements are verified. All other Phase 4 and Phase 5
requirements remain open.
