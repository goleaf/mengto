# Phase 4 One-Health Category Work Package

Date: 2026-08-03

Status: implemented and verified

## Exact Scope

This package owns exactly `forum.category.0188` through
`forum.category.0236`: 49 open Phase 4 atoms for root category 24.

The immutable source section defines the root identity and purpose, 42 ordered
subcategories, and a user-visible boundary stating that discussion does not
replace a physician, veterinarian, public-health authority, or emergency
service. Every atom in the section is classified as `forum-category` Phase 4;
no later-phase atom is promoted by this package.

## Classification

The immutable manifest and production synchronizer already contained the exact
category-24 root, purpose, source labels, stable keys, slugs, positions, and
reviewed EN/LT/RU root translations. Aggregate tests covered catalogue-wide
counts and synchronization, but did not independently pin this source section.

The explicit professional and emergency-care notice was not rendered when the
category was selected. That is a production presentation gap, not only an
evidence gap.

## Implementation Contract

1. Pin the category number, stable key, slug, source name, purpose, and all 42
   ordered source labels in a focused Pest test.
2. Synchronize through the production Action and prove exact child stable keys,
   slugs, positions, and reviewed EN/LT/RU root rows with bounded eager loading.
3. Add an optional localized category notice to `ForumCategoryTree`, available
   in both database and manifest-fallback modes without another query.
4. Render the notice only while the One-Health root or one of its children is
   selected; retain a semantic `role="note"` and the existing forum safety
   presentation.
5. Version the category-tree cache key so a pre-deployment cached v2 payload
   cannot omit the new field or cause an undefined-key error.
6. Reuse the canonical manifest, synchronizer, Eloquent models, tree, and
   localization framework. Do not introduce another hierarchy or hardcoded
   user-facing Blade text.

## Query Delta

The production query delta is zero. The optional notice is resolved from
Laravel language files while the existing localized tree is built, then is
stored in the same per-locale cache payload. No model, migration, route,
authorization, pagination, or aggregate query changes.

## Acceptance

- The focused contract fails on any category-24 source, ordering, hierarchy,
  key, slug, locale-row, notice-copy, or conditional-rendering drift.
- The related category, multilingual, localization, architecture, and schema
  slice passes.
- The immutable source and deterministic 38,377-requirement checks pass before
  evidence regeneration.
- Pint, Larastan, full Pest, fresh/repeat seed, rollback/reapply, dependency
  audits, build, and cache compilation pass before the 49 IDs move to
  `verified`.
- The focused notice is covered by the server-rendered feature test; no new
  browser interaction, layout primitive, route, or schema is introduced.

Stop without evidence if any ID outside `forum.category.0188` through
`forum.category.0236` is promoted, source text changes, the notice appears on
unrelated categories, or an unrelated dirty-tree file enters the commit.

## Final Evidence

- RED contract: 3 tests executed; the two manifest/persistence checks passed
  and the notice check failed on the missing `notice` tree field.
- Focused implementation contract: 3 tests and 21 assertions passed.
- Related category, multilingual/cache, localization, and schema slice: 47
  tests and 36,632 assertions passed.
- One-Health, forum-directory, architecture, responsive-interface, and
  localization slice: 39 tests and 59,906 assertions passed.
- Complete sequential repository suite: 2,586 tests and 81,835 assertions.
- Full Pint and Larastan passed; Larastan reported zero errors. A direct
  PHPStan invocation on the Pest file alone was non-representative and reported
  Pest pending-call methods; the configured full analysis and production-file
  focused analysis both passed.
- Fresh isolated SQLite verification applied 126 migrations and created 211
  tables; initial and repeated seed both exited `0` and retained five users.
  Complete rollback/reapply verification passed 2 tests and 11 assertions.
- Composer strict validation and audit, npm audit with zero vulnerabilities,
  Vite 8.2.0 production build, and config/event/route/view cache compilation
  passed; the caches were cleared after verification.
- The immutable source hash, exact 44-root/1,637-subcategory manifest, and
  deterministic 38,377-requirement generation checks passed.
- Isolated Chrome checked the selected category at 1440x900 and 375x812. Both
  viewports rendered one semantic boundary and 42 source subcategories with no
  overflow, unnamed controls, raw translation keys, undersized mobile
  controls, invalid images, duplicate IDs, or console errors.
- The evidence overlay promotes exactly `forum.category.0188` through
  `forum.category.0236`. Production query delta is zero; no schema, route,
  Policy, model, or cache-read count changed.
