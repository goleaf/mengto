# Pet Profile Architecture Decisions

Date: 2026-07-31

## ADR-PET-001: Evolve The Existing Aggregate

`App\Models\PetProfile` and `pet_profiles.id/profile_key` remain the canonical
pet identity. No alternate pet table or per-module species list may be added.
Existing foreign keys, string keys, URLs, records, and soft-deleted rows are
preserved.

## ADR-PET-002: Separate Actor From Pet Persona

Every mutation is performed by a real authenticated `User` through a
role-bearing manager membership. Content may be displayed as authored by a
pet persona, but the internal actor, membership, permission, time, reason, and
idempotency key are retained. A pet never authenticates or accepts legal
terms.

## ADR-PET-003: Manager Memberships, Not A Wider Owner Column

Multi-owner and delegated access use a separate membership aggregate with
role, status, start/end, inviter, evidence state, permission overrides, and
optimistic version. Existing `pet_profiles.user_id` remains a denormalized
compatibility primary owner until all consumers are migrated.

## ADR-PET-004: Explicit Critical Permissions

Roles provide conservative defaults; critical abilities are evaluated
individually by policy and domain service. Transfer, deletion, medical access,
location/device access, microchip edits, adoption publication, transactions,
and memorial activation never follow from generic edit access.

## ADR-PET-005: Lifecycle Is An Audited State Machine

Profile states use a PHP enum and controlled transition action. Each accepted
transition appends an immutable event with old/new state, actor membership,
reason, metadata, and idempotency key. Status changes do not rely on display
text and do not silently delete history.

## ADR-PET-006: Layered Privacy

`pet_profiles.visibility` remains the broad compatibility ceiling. Normalized
privacy settings define section or field audience, discoverability, external
indexing, owner display, location granularity, and link access. A projection
query applies viewer context before search, cache, counts, QR, or rendering.

## ADR-PET-007: Selective Fact Normalization

Name, taxonomy, birth precision, sex, chip/registration references, ownership,
and other disputed identity facts may use versioned provenance records.
Ordinary descriptive UI values remain typed columns or validated profile
metadata. This preserves auditability without a generic entity-attribute-value
database.

## ADR-PET-008: Advisory Duplicate Detection

Duplicate matching produces private, bounded candidates and reason signals.
It never proves ownership or merges profiles. Merge requires authorized human
review, preserves the old ID and relations, creates redirects, and remains
auditable and reversible where possible.

## ADR-PET-009: No New Mandatory Infrastructure

Expiry, cooling-off, and scheduled visibility are enforced from stored
timestamps at read/write time. Queues may improve non-critical delivery later,
but profile safety cannot depend on a worker. External photo, phone, registry,
or AI services stay optional and feature-flagged.
