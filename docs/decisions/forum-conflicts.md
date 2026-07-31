# Forum Requirement Conflicts

## Conflict 1: Prior Twenty-Category Subcategory Text

The additive extension requires every earlier subcategory but the recovered
primary source does not contain a separate numbered hierarchy matching roots
1–20. It does contain the full original forum prompt and numerous category,
species, place, audience, and topic-type requirements.

**Resolution:** Preserve both recovered prompts verbatim, seed the 20 named
roots, map every recovered category concept to a stable child or structured
dimension, and never invent “verbatim” source text. The matrix records the
source line for every recovered requirement. This source-history gap remains
visible until an older exact hierarchy is supplied, but it does not block
implementation of all recovered requirements.

## Conflict 2: Laravel-Only Versus Livewire Administration

The existing forum is conventional Blade/controllers while the extension asks
for a Livewire administration area and selectors.

**Resolution:** Preserve stable public pages and add Livewire only to
stateful, server-backed administration and selection flows. Business logic
remains in Actions and Services.

## Conflict 3: Full Taxonomy Versus Repository Size

The source requires all accepted animal taxa but forbids millions of manually
typed seeder rows.

**Resolution:** Keep a small production-safe core seed in source control and
provide a complete versioned importer for an approved local Catalogue of Life
snapshot. Imported row counts are evidence from the actual snapshot, not a
hardcoded claim.

## Conflict 4: Queue Guidance Versus Runtime Independence

General rules prefer jobs for expensive work, while critical functionality
may not depend on new unavailable infrastructure.

**Resolution:** Taxonomy processing is chunked and resumable behind a database
lock. A command and authorized web continuation use the same action. Queue
dispatch is optional, never the only execution path.

## Conflict 5: Raw SQL Prohibition Versus Import Performance

Repository rules prohibit raw SQL strings. Large imports need bulk operations.

**Resolution:** Use Eloquent/query builder `upsert`, chunking, schema-builder
indexes, and supported database transactions. Any future engine-specific
optimization needs a new ADR and cross-database fallback.

## Conflict 6: Deferred Live Expert Sessions Versus Current Master Specification

`docs/forum-scope.md` classified live expert sessions as a later-release item.
The current canonical source section 69 explicitly requires verified
professional question sessions as part of the complete forum.

**Resolution:** The current canonical requirement has higher precedence than
the historical release note. Implement normalized, scheduled community
question sessions now, rewrite the stale scope statement, and preserve the
medical/legal non-authority and independent credential boundaries. This
decision does not create private telemedicine, appointments, realtime video,
or formal legal representation.
