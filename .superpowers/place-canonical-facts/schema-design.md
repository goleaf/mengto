# Canonical Place Facts: Relational Schema Proposal

Date: 2026-08-30
Scope: schema design only for `PLA-CF-01` / `PLA-CF-03`; no schedule-resolution algorithm, UI, translation copy, or test-suite design.

## Outcome

Add normalized, versioned, source-linked tables beside the existing Place authority and submission schema. Keep `places` as the durable identity/authorization aggregate, keep `place_facts` as immutable encrypted submission and merge evidence, and make the new typed relations the canonical query/read boundary. Do not turn the existing encrypted `place_facts.field_value` into an EAV query store.

This directly supports the required multi-category taxonomy, localized names, ordered contacts, schedules/exceptions/closures, service offerings and eligibility, structured rules/facility/safety/accessibility/parking/transport facts, fact-level provenance/freshness, and retained replacement history. Requirements explicitly demand those records and prohibit category/missing-data inference (`docs/product-requirements.md:94-100`, `docs/plans/places-production-master-plan.md:207-265`, `docs/plans/places-production-master-plan.md:358-398`, `docs/plans/places-production-master-plan.md:703-731`, `docs/implementation-plan.md:148-186`).

## Confirmed Current-State Defects And Conflicts

These are observed gaps in the current schema/code, not optional design preferences.

| Finding | Exact evidence | Consequence |
| --- | --- | --- |
| Canonical facts are still place-wide scalar/JSON projections. A place has one category, one phone/email/site, one verification status/expiry, and JSON/text accessibility/species/rules/facility-like fields. | `database/migrations/2026_08_03_140000_create_places_table.php:22-48`; `database/migrations/2026_08_03_140040_add_catalog_fields_to_places_table.php:13-22` | Cannot represent multiple independently sourced/versioned facts or fact-level freshness. One `places.updated_at` or `information_expires_at` cannot establish freshness for phone, hours, capability, rules, and accessibility separately. This conflicts with `PLA-02-001..007` and `PLA-05-001..017`.
| Place schedules cannot use the existing venue timezone as their general authority. | `database/migrations/2026_08_03_140010_create_venues_and_areas_tables.php:13-30` makes a venue optional and one-to-one with a place, and stores timezone only on `venues`. | Parks, routes, clinics, shelters, and other non-venue places have no canonical timezone owner for opening facts.
| `place_facts` is untyped encrypted evidence, not a relational current-fact store. | `database/migrations/2026_08_30_120000_create_place_submission_publication_tables.php:145-173`; `app/Models/PlaceFact.php:43-55`; flattened keys are created at `app/Actions/SubmitPlaceSubmission.php:452-503`. | `field_value` cannot be filtered, joined, ordered, or type-checked. Keys such as `hours.*`, `services.*`, and `features.*` have no foreign keys to controlled definitions. It is correct evidence storage, but cannot truthfully power bounded schedule/service/species/accessibility queries.
| Concurrent canonical fact copying is not protected by a database uniqueness boundary. | `place_facts` has only `stable_key` uniqueness and ordinary copied-from index at `database/migrations/2026_08_30_120000_create_place_submission_publication_tables.php:145-173`; publication relies on `firstOrCreate(['place_id', 'copied_from_fact_id'])` at `app/Actions/PublishPlaceSubmission.php:91-114` (the same pattern is used by link/merge actions). | Two transactions can both observe absence and insert duplicate copies. Add a unique `(place_id, copied_from_fact_id)` constraint for non-null copied rows before making these rows a compatibility/provenance bridge.
| `place_facts` permits invalid ownership/lineage shapes at database level. | `place_submission_id`, `place_submission_revision_id`, `place_id`, and `copied_from_fact_id` are all independently nullable at `database/migrations/2026_08_30_120000_create_place_submission_publication_tables.php:145-153`; there is no cross-column check. | Orphan facts and a revision belonging to a different submission are representable. The new canonical tables must not inherit this ambiguity; compatibility validation should detect it before synchronization.
| Persisted lifecycle/scope values are generally unconstrained strings; `visibility_scope` is not even enum-cast on `PlaceFact`. | String columns at `database/migrations/2026_08_30_120000_create_place_submission_publication_tables.php:155-164`; casts at `app/Models/PlaceFact.php:45-55`; core Place strings at `database/migrations/2026_08_03_140000_create_places_table.php:24-42`. | Direct/database writes can create values outside the PHP enum vocabulary, contrary to `PLA-02-022` and `DATA-INTEGRITY-001` (`docs/non-functional-requirements.md:21-30`).
| Current factory defaults invent facts the active plan explicitly says must not be inferred. | `database/factories/PlaceFactory.php:25-73` defaults to Vilnius address/coordinates, `species_rules => ['dog']`, and a pet rule; `database/factories/PlaceSubmissionFactory.php:66-70` defaults hours/rules/features. | A generic factory record appears location-known and dog-capable. Canonical-model factories need sparse/unknown defaults; capability, exact/generalized location, hours, accessibility, and verification belong in explicit states.

Additional compatibility risk: model-level immutability guards on `PlaceFact` (`app/Models/PlaceFact.php:58-62`) do not create database immutability and are bypassable by lower-level/bulk writes. The repository prohibits triggers/raw SQL, so retained evidence must be protected through restrictive FKs, no update path, narrowly fillable Actions, and explicit integrity checks rather than a claimed hard database guarantee.

## Shared Versioning And Provenance Pattern

Use this pattern on every fact-bearing current/history table below:

- `stable_key varchar(190) unique`: immutable row/version identity.
- `version unsignedInteger`: monotonically increases within the logical fact.
- `is_current boolean default true` plus nullable `current_key varchar(190) unique`: only the current version receives a deterministic key such as `place:{id}:contact:{logical-key}`. Nullable unique keys are portable to SQLite and avoid partial indexes.
- nullable `replaces_*_id` self-FK, `restrictOnDelete()`, and **unique**: one prior version cannot fork into two replacements.
- `place_source_id` restrictive FK: every asserted current value has one provenance envelope.
- `recorded_at`, nullable `retired_at`, and timestamps. Historic rows remain queryable and are never overwritten with a new value.
- `lock_version unsignedInteger default 0` only on independently mutable aggregate roots (`place_schedules`, offerings, current attribute rows), not on immutable child intervals.

Application invariants mirror schema constraints: `is_current` iff `current_key` is non-null and `retired_at` is null; replacements belong to the same place/logical key; version increments exactly once. Cross-column checks should be added only through a portable first-party schema-builder API. Do not introduce raw SQL or engine-specific partial indexes merely to express them (`docs/data-model.md:41-59`, `docs/data-model.md:123-132`).

## Table-By-Table Proposal

### 1. Provenance and controlled definitions

| Table | Columns and enums | FKs / uniqueness / leading indexes | Relationships and factory contract |
| --- | --- | --- | --- |
| `place_sources` | `id`, `place_id`, `stable_key`, `source_kind` (reuse/extend `PlaceSubmissionSource`), `verification_scope` (`community_submitted`, `manager_confirmed`, `organization_confirmed`, `independently_verified`), `visibility_scope` (`public`, `manager`, `moderator`, `private`), nullable `submitted_by_user_id`, nullable `organization_id`, nullable encrypted `source_reference` text, nullable encrypted `private_evidence` text, nullable `observed_at`, `verified_at`, `expires_at`, `superseded_at`, `created_at`; no `updated_at` | Restrict place/organization; actors null on delete with explicit leading index. Unique stable key. Index `(place_id, verification_scope, expires_at, id)`, `(submitted_by_user_id, created_at, id)`, `(organization_id, created_at, id)`. | `Place hasMany sources`; source belongs to place/submitter/org and hasMany evidence links. Factory default is community-submitted, manager-visible, unverified, no private evidence; explicit verified/stale/private states.
| `place_source_evidence` | `place_source_id`, `place_fact_id`, `position unsignedInteger`, `created_at` | Restrictive FKs; primary/unique `(place_source_id, place_fact_id)`; reverse index `(place_fact_id, place_source_id)`. | Bridges new canonical rows to existing immutable `place_facts` without copying decrypted evidence into public/queryable columns. Factory only via an explicit source plus existing fact.
| `place_categories` | `id`, `stable_key varchar(120)`, `name_translation_key varchar(190)`, nullable `description_translation_key`, `position`, `is_active`, timestamps | Unique stable key and name translation key; index `(is_active, position, id)`. | Definition hasMany assignments. Deterministic idempotent reference factory/seeder; labels remain Laravel translation keys, not a second translation system.
| `place_service_definitions` | `id`, `stable_key varchar(120)`, `name_translation_key`, nullable `description_translation_key`, `service_domain` (including veterinary), `is_emergency_capability boolean`, `position`, `is_active`, timestamps | Unique stable key/translation key; index `(service_domain, is_emergency_capability, is_active, position, id)`. | HasMany offerings. Definitions are fixed reference data. A category never implies a service or emergency capability.
| `place_attribute_definitions` | `id`, `stable_key varchar(120)`, `domain` (`facility`, `pet_rule`, `safety`, `accessibility`, `parking`, `transport`), `value_kind` (`state`, `boolean`, `integer`, `decimal`, `text`), translation keys, nullable `unit_code`, `is_public_safe`, `is_filterable`, `position`, `is_active`, timestamps | Unique stable key; index `(domain, is_active, position, id)` and `(domain, is_filterable, is_active, id)`. | HasMany attribute values. Deterministic reference records; no public-safe default is automatically asserted for a place.

`place_sources` is the provenance envelope; source references/evidence remain encrypted `TEXT` and hidden because encrypted casts are non-queryable and ciphertext length is variable. Public facts, controlled keys, status/scope, timestamps, and HMAC hashes remain unencrypted because they are filter/join operands. Never encrypt FKs, enum/status columns, normalized search values, currency, or time boundaries.

### 2. Names, categories, and contacts

| Table | Columns and enums | FKs / uniqueness / leading indexes | Relationships and factory contract |
| --- | --- | --- | --- |
| `place_names` | Shared version fields; `place_id`, `locale varchar(10)`, `kind` (`primary`, `alternate`), `name varchar(180)`, `normalized_name varchar(190)`, `position`, `place_source_id` | Restrict place/source/self replacement. Unique nullable current key and unique replacement FK. Index `(place_id, locale, is_current, kind, position, id)` and `(locale, normalized_name, is_current, place_id, id)`. Add nullable `primary_place_locale_key` unique to enforce one current primary per place/locale. | Place hasMany names/currentNames. Factory requires explicit locale/name and source; alternate/historical states are opt-in. Existing `places.name`, `locale`, and `normalized_name` remain temporary default-locale projections.
| `place_category_assignments` | Shared version fields; `place_id`, `place_category_id`, `position`, `is_primary`, `place_source_id`, nullable `active_position_key`, nullable `primary_place_key` | Restrict all FKs. Current key represents place/category. Nullable `active_position_key` unique prevents duplicate current ordering; nullable `primary_place_key` unique enforces one current primary category per place. Index `(place_id, is_current, position, id)` and `(place_category_id, is_current, place_id, id)`. | Place/category hasMany assignments. Factory requires explicit place/category/source; primary is opt-in. Existing `places.catalog_category` remains the primary-category projection only.
| `place_contact_points` | Shared version fields; `place_id`, `type` (`phone`, `email`, `website`, `booking_link`), `purpose` (`general`, `emergency`, `booking`, `accessibility`, `after_hours`), `visibility_scope`, nullable `public_value varchar(2048)`, nullable encrypted `private_value text`, nullable `normalized_value varchar(500)`, nullable `value_hash char(64)`, `position`, `place_source_id`, nullable `active_position_key` | Restrict FKs; unique current/replacement/position keys. Index `(place_id, type, is_current, position, id)`, `(type, purpose, is_current, place_id, id)`, `(normalized_value, type, is_current, place_id)`. Enforce exactly one public/private value in validation and a portable check if supported. | Place hasMany contacts/currentContacts. Factory default is a public general contact only when a value is supplied; emergency/booking/private/stale states explicit. Legacy `public_phone/email/website` are first matching public projections, never authoritative.

### 3. Schedule storage (schema only)

| Table | Columns and enums | FKs / uniqueness / leading indexes | Relationships and factory contract |
| --- | --- | --- | --- |
| `place_schedules` | Shared version fields; `place_id`, `timezone varchar(64)`, `mode` (`regular`, `appointment_only`, `on_call`, `unknown`), `place_source_id`, `lock_version` | One current key per place; restrict place/source/self. Index `(place_id, is_current, id)`. | Place hasMany schedule versions and hasOne current schedule. Sparse factory defaults to `unknown`; a known timezone/mode is explicit.
| `place_opening_intervals` | `id`, `place_schedule_id`, `weekday unsignedTinyInteger` (1..7), `opens_at time`, `closes_at time`, `spans_next_day boolean`, `position`, timestamps | Cascade only as an owned child of a never-deleted schedule version; unique `(place_schedule_id, weekday, opens_at, closes_at)`; index `(place_schedule_id, weekday, opens_at, id)`. Portable enum/range check where available; otherwise enum/value-object validation. | Schedule hasMany intervals. Factory never creates intervals for an unknown schedule; overnight is explicit.
| `place_schedule_exceptions` | `id`, `place_schedule_id`, `local_date date`, `kind` (`closed`, `special_opening`, `modified_hours`, `appointment_only`), nullable public `reason_code`, `place_source_id`, timestamps | Unique `(place_schedule_id, local_date)`; restrict source, cascade owned schedule; index `(place_schedule_id, local_date, id)`. | HasMany exception intervals. One envelope per local date avoids contradictory same-day exception rows.
| `place_schedule_exception_intervals` | `id`, `place_schedule_exception_id`, `opens_at`, `closes_at`, `spans_next_day`, `position`, timestamps | Unique exact interval; index `(place_schedule_exception_id, position, id)`; cascade owned exception. | Present only for opening/modified exceptions; closed exceptions have none (application/check invariant).
| `place_temporary_closures` | Shared version fields; `place_id`, `starts_at`, nullable `ends_at`, `timezone varchar(64)`, `status` (`planned`, `active`, `ended`, `cancelled`), public `reason_code`, nullable encrypted `private_detail`, `place_source_id` | Restrict FKs/self; unique current/replacement. Index `(place_id, status, starts_at, ends_at, id)` and `(status, starts_at, ends_at, place_id, id)`. Validate `ends_at > starts_at`. | Place hasMany closures/currentClosures. Factory supplies explicit bounded interval; no default closure.

The place schedule owns its IANA timezone. `venues.timezone` remains event-operational and must not be silently copied as the place schedule authority.

### 4. Services and eligibility

| Table | Columns and enums | FKs / uniqueness / leading indexes | Relationships and factory contract |
| --- | --- | --- | --- |
| `place_service_offerings` | Shared version fields; `place_id`, `place_service_definition_id`, `availability` (`available`, `appointment_only`, `on_call`, `temporarily_unavailable`, `unavailable`, `unknown`), `species_scope` and `size_scope` (`all`, `restricted`, `unknown`, `not_applicable`), `pricing_type` (`free`, `fixed`, `from`, `range`, `quote`, `unknown`), nullable `price_min_minor`, `price_max_minor`, `currency char(3)`, `position`, `place_source_id`, `lock_version`, nullable `active_position_key` | Restrict FKs/self. Current key represents place/service; unique replacement/position keys. Index `(place_id, is_current, position, id)`, `(place_service_definition_id, availability, is_current, place_id, id)`, and `(place_id, availability, is_current, id)`. Validate non-negative minor units, max >= min, and currency/amount presence per pricing type. | Place/definition hasMany offerings. Factory default availability is `unknown` with unknown eligibility and no money; available/emergency/appointment/unavailable/priced states explicit.
| `place_service_offering_taxa` | `place_service_offering_id`, `taxon_id`, `eligibility` (`supported`, `unsupported`, `conditional`, `unknown`), `includes_descendants`, nullable public `condition_code`, timestamps | Composite primary/unique `(place_service_offering_id, taxon_id)`; restrict offering and active global `taxa`; reverse index `(taxon_id, eligibility, place_service_offering_id)`. | Offering belongsToMany taxa with typed pivot. Reuse canonical animal taxonomy; do not store species slugs or duplicate taxonomy.
| `place_service_offering_sizes` | `place_service_offering_id`, `size_category varchar(32)` cast to existing `PetSizeCategory`, `eligibility`, nullable `condition_code`, timestamps | Composite primary/unique `(place_service_offering_id, size_category)`; index `(size_category, eligibility, place_service_offering_id)`. | Reuses `PetSizeCategory` (`app/Enums/PetSizeCategory.php:7-15`). Missing pivot rows do not mean supported; offering `size_scope` controls semantics.

Emergency eligibility requires a current offering whose definition explicitly carries the emergency veterinary capability and an explicit supported taxon/size rule when the offering scope is restricted. `PlaceType::VeterinaryClinic` and category assignment alone are never sufficient (`docs/plans/places-production-master-plan.md:709-718`).

### 5. Structured rules/facility/safety/accessibility/parking/transport facts

| Table | Columns and enums | FKs / uniqueness / leading indexes | Relationships and factory contract |
| --- | --- | --- | --- |
| `place_attribute_values` | Shared version fields; `place_id`, `place_attribute_definition_id`, `state` (`present`, `absent`, `conditional`, `unknown`, `not_applicable`), nullable `boolean_value`, `integer_value`, `decimal_value decimal(12,4)`, `text_value varchar(1000)`, nullable encrypted `private_detail text`, nullable `unit_code`, `position`, `visibility_scope`, `place_source_id`, `lock_version` | Restrict place/definition/source/self. Current key represents place/definition; unique replacement. Index `(place_id, is_current, position, id)`, `(place_attribute_definition_id, state, is_current, place_id, id)`, `(place_id, visibility_scope, is_current, id)`. Exactly one typed value matching definition `value_kind` is an Action-level invariant plus portable check where possible. | Place/definition hasMany values and currentValue. Factory default state is explicit `unknown`, with no typed value; present/absent/conditional/private/stale variants are opt-in.

This controlled-definition/value shape is deliberately not the existing `place_facts` EAV: definition FKs control keys, public filter operands remain typed/queryable, sensitive details stay encrypted, one current version is database-unique, and every version references provenance. Temporary hazard/warning workflows remain outside this table; a stable safety facility fact is not a temporary warning (`PLA-05-008`).

## Schema-Testable Invariants

1. Every canonical value belongs to one existing place and one provenance source for that same place.
2. Every logical fact has at most one current row (`current_key` unique), and every prior row can be replaced at most once (`replaces_*_id` unique).
3. Every foreign key has a leading index unless it is already the leading column of a primary/unique/composite index.
4. One current primary category exists at most per place; multiple current non-primary categories are allowed.
5. One current primary name exists at most per place/locale; alternate names may coexist in deterministic position order.
6. Public/private contact payloads are mutually exclusive. Private contact/evidence/detail columns are encrypted `TEXT`, hidden, and never indexed; public normalized values may be indexed.
7. Schedule weekdays are 1..7; one exception envelope exists per schedule/local date; an interval cannot be zero-length; an overnight interval is represented explicitly, never inferred from reversed times.
8. Money uses integer minor units and ISO currency, never float; ranges cannot invert.
9. Offering absence or missing eligibility rows never means available/supported. `unknown`, `unavailable`, and restricted scopes are explicit.
10. A service definition/category/type cannot confer emergency capability by itself; eligibility requires a current offering plus the applicable explicit taxon/size evidence.
11. Attribute definitions control domain and value type. Missing values cannot be rendered as confirmed present/absent; `unknown` is a first-class state.
12. Existing `place_facts` stay encrypted and immutable evidence. A copied canonical evidence row is unique by `(place_id, copied_from_fact_id)`; new current relations link to `place_sources`/evidence rather than decrypting facts in SQL.
13. Source freshness is per source/fact through `observed_at`, `verified_at`, and `expires_at`; `places.updated_at` is never freshness evidence.

## Compatibility With Existing `places`, `place_submissions`, And `place_facts`

- Preserve all historical migrations. Add new migrations only.
- Preserve `place_submissions.submitted_facts` as the encrypted original request snapshot (`app/Models/PlaceSubmission.php:117-167`) and preserve its flattened immutable `place_facts` rows. Do not rewrite or delete either during normalization.
- At publication/link, map allow-listed legacy keys (`name`, `type`, `catalog_category`, public contacts/location, `hours.*`, `services.*`, `features.*`, `rules`) into typed canonical rows and create `place_sources` linked through `place_source_evidence`. Unknown/unmappable keys remain evidence and are reported, not guessed.
- Retain `places.name/locale/normalized_name`, `catalog_category`, public contact columns, `verification_*`, `information_expires_at`, accessibility/species/rules fields, and venue JSON fields as read-compatible projections during dual-write. New readers must use typed relations; later removal requires a separate release and parity evidence.
- Add the missing unique `(place_id, copied_from_fact_id)` constraint only after a bounded duplicate audit and deterministic reconciliation. Validate facts with neither a submission nor place, mismatched submission/revision ownership, and copied facts whose source/place lineage is invalid before synchronization.
- Do not copy `place_facts.visibility_scope = review_only` into canonical public visibility automatically. Evidence visibility and asserted-value visibility are separate decisions.
- The existing Place relationships (`app/Models/Place.php:176-275`) should gain typed `hasMany`/current scoped relations; inverse relations belong on every new model. Avoid a generic polymorphic relation because it would lose enforceable FKs.

## Recommended Additive Migration Grouping

1. **Reference and provenance:** create category, service-definition, attribute-definition, source, and source-evidence tables; seed reference definitions idempotently.
2. **Identity/contact facts:** create names, category assignments, and contacts; add model relations/factories. No reader switch.
3. **Schedules:** create schedule versions, weekly intervals, exceptions/exception intervals, and temporary closures. No resolution behavior in the migration.
4. **Services/eligibility:** create offerings and taxon/size eligibility relations; reuse `taxa` and `PetSizeCategory`.
5. **Structured attributes:** create typed attribute values and controlled definitions.
6. **Compatibility hardening:** audit/reconcile duplicate copied facts, then add `(place_id, copied_from_fact_id)` uniqueness. Keep orphan/lineage checks as a pre-switch validation command if the cross-column constraint cannot be expressed portably without forbidden raw SQL.
7. **Bounded synchronization (command/Action, not schema migration):** checkpoint by `places.id`/`place_facts.id`, map only allow-listed deterministic fixture/submission facts, dual-write projections, report unknown/conflicting values, and prove repeatability.
8. **Reader switch:** only after parity and bounded eager-load evidence; remove fixture/default truth, not the compatibility columns. Column removal is a later expand-and-contract release.

Rollback is safe only before canonical writes. After any canonical source/value history is written, disable writers/readers as needed and forward-fix; do not drop fact, source, or history tables. This matches the repository migration safety sequence (`docs/data-model.md:123-132`) and `PLA-CF-03/05/06` rollback boundaries (`docs/implementation-plan.md:172-177`).

## Recommendations Requiring Principal Disposition

These are design choices, not confirmed current defects:

1. Adopt the existing nullable unique `current_key` versioning pattern used by `pet_profile_facts` (`database/migrations/2026_07_31_001270_create_pet_profile_foundation.php:203-240`) across canonical Place fact tables.
2. Keep category/service/attribute labels in Laravel translation files through stored translation keys; reserve `place_names` for actual locale-specific names supplied for a place.
3. Use the global `taxa` relation for species eligibility and the existing `PetSizeCategory` enum for size eligibility; do not introduce Place-only species/size vocabularies.
4. Make generic Place factory defaults sparse/unknown, with small explicit relation states (`withPublicContact`, `withKnownSchedule`, `withEmergencyVeterinaryOffering`, `withAccessibilityFact`, `staleSource`). This avoids large surprise graphs while satisfying the factory contract.
5. Keep temporary warnings/reviews/questions/corrections in their dedicated relational workflows. Only stable structured facts belong in `place_attribute_values`; no polymorphic “everything about a place” table should be added.
