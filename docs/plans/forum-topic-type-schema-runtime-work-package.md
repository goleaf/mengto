# Forum Topic-Type Schema Runtime Work Package

Date: 2026-08-03

Status: verified

## Exact Scope

This Phase 3 package owns exactly these 20 open requirements:

- `forum.data.0008`, `forum.data.0009`, `forum.data.0010`;
- `forum.data.0037` through `forum.data.0050`;
- `forum.data.0053`, `forum.data.0073`, and `forum.data.0084`.

It does not promote the 13 remaining general migration and final-audit IDs in
Phase 3. Those IDs require a separate repository-wide migration package and
cannot inherit evidence from one topic-schema boundary.

## Current Gap

The repository already has normalized numeric topic-type identities, stable
keys, versioned JSON schema/configuration columns, a normalized topic foreign
key, structured topic JSON, enum casts, an idempotent seeder, and compatibility
tests. The remaining contract is not complete because:

- the system definitions do not describe every source-listed schema capability;
- request validation does not enforce type-specific location, species, or
  attachment restrictions;
- topic writes do not persist the selected definition version reliably;
- answer acceptance, answer ratings, and notification levels do not consult
  the current topic-type contract;
- the stable schema catalogue is queried directly and has no owned cache,
  TTL, invalidation path, or query-budget test;
- documentation explicitly says the remaining runtime capabilities are
  tracked separately.

## Implementation Contract

1. Keep the existing `forum_topic_types` and `forum_topics` schema. No new
   migration is justified for this package.
2. Extract one typed system catalogue that defines required and optional
   fields, semantic validation metadata, expiration and archival behavior,
   location/species requirements, contact restrictions, allowed attachments,
   allowed answer ratings, accepted-answer availability, SEO behavior, and
   notification levels.
3. Keep numeric database IDs and stable non-translated keys as identity.
   Translation keys remain display metadata; external provider identifiers do
   not become primary or relationship keys.
4. Seed system definitions from that catalogue without replacing IDs,
   attached topics, or administrator-created definitions.
5. Resolve active schemas through one bounded cached registry. It selects only
   required columns and never exposes inactive definitions.
6. Validate browser input in `StoreTopicRequest`; validate every mutation
   again at the Action boundary where answer ratings, accepted answers, or
   notification settings depend on the topic type.
7. Persist the resolved schema version with every generic topic create/update.
8. Keep Blade passive. It receives prepared type options and performs no
   schema lookup, permission decision, or query.

## Cache Contract

- Owner: `ForumTopicTypeSchemaRegistry`.
- Key: `forum:topic-type-schemas:v1`.
- Scope: active schema definitions only; no viewer-private data.
- TTL: `config('taxonomy.topic_type_schema_cache_seconds')`.
- Invalidation: successful topic-type save/delete and every system-definition
  synchronization.
- Failure behavior: before definitions are seeded, use the immutable system
  catalogue; once any database definitions exist, missing/inactive keys fail
  closed.
- Verification: the first lookup may execute one bounded definition query;
  repeated lookups execute none until invalidation.

## Test Contract

Focused Pest tests must prove:

1. all source-listed system types contain the complete schema capability map;
2. stable keys and numeric IDs are independent from translated names and
   unstable external identifiers;
3. required location/species input and allowed attachments are enforced at the
   HTTP boundary;
4. create/update persist the selected active definition ID and schema version;
5. inactive definitions fail closed;
6. answer ratings, accepted answers, and notification levels obey the current
   type contract even when Actions are invoked directly;
7. the cache has a deterministic query budget and invalidates after both model
   mutation and seeder synchronization;
8. repeated seeding preserves IDs, relations, custom definitions, and current
   topic structured data;
9. the related forum, localization, architecture, and complete sequential
   suites remain green.

## Evidence And Stop Conditions

Only the 20 listed IDs may receive evidence after every applicable gate passes.
Stop without a completion claim if the implementation creates per-type topic
columns, uses translated/external values as identity, accepts an inactive
schema, trusts browser-supplied rules, caches viewer-sensitive data, loses a
custom definition, changes a topic ID/relation, or introduces an unbounded
query.

## Implementation Checkpoint

- The focused runtime contract passes 5 tests and 1,481 assertions.
- The related forum regression slice passes 61 tests and 2,380 assertions.
- The attributable Larastan slice passes with zero errors.
- The architecture/localization slice passes 27 tests and 57,522 assertions.
- The complete sequential suite passes 2,360 tests and 78,407 assertions.
- Full Pint/Larastan, Composer/npm audits, Vite/cache compilation, isolated
  118-migration/200-table migration and repeated seed, immutable source, and
  deterministic 38,377-requirement generation pass.
- Exactly the 20 scoped IDs are verified; the remaining 13 Phase 3 IDs stay
  open.
