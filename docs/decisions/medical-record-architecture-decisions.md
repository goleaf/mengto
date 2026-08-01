# Medical Record Architecture Decisions

Date: 2026-08-01

## ADR-MEDICAL-001: MedicalRecord Remains The Aggregate

Extend the existing `MedicalRecord` and its child records. Do not create a
second clinical-history aggregate. Existing events, vaccinations, medicines,
doses, documents, reminders, grants, URLs, and audit history remain valid.

## ADR-MEDICAL-002: PetProfile Is The Canonical Patient Identity

Every new medical record references one persistent `PetProfile`. A unique
`pet_profile_id` enforces one canonical record per real pet. `owner_id`,
`owner_key`, and `pet_profile_key` are retained as historical compatibility
metadata; once a canonical link exists they do not grant access.

Ownership transfer changes control of `PetProfile`, not the identity of the
medical record. The old owner loses future medical access unless a current
manager grant independently permits it. The new primary owner receives access
through the canonical profile without copying clinical history.

## ADR-MEDICAL-003: Legacy Rows Stay Available During Migration

The canonical foreign key is nullable for legacy compatibility. Deterministic
rows are backfilled from actor key, profile owner, and profile slug. Ambiguous
rows remain unlinked and retain their prior owner-key authorization until a
reviewed reconciliation workflow links them. No legacy row is deleted.

## ADR-MEDICAL-004: Medical Rights Are Explicit Capabilities

`view-medical` and `manage-medical` are separate pet-profile permissions.
Primary owners, legal representatives, organization administrators, co-owners,
and shelter managers can manage by default. Foster carers and specialists can
view but cannot mutate unless an explicit active override grants management.
Expiry, revocation, and deny overrides are checked on the server.

## ADR-MEDICAL-005: Unknown Is A First-Class State

Allergy and current-medication knowledge use stable enum values: `unknown`,
`not-provided`, `none-known`, and `known`. Empty content never decides which
state applies. Emergency, shared, and ordinary record views show the status
text when no item is available.

## ADR-MEDICAL-006: Domain Modules Keep Their Own Evidence

Medical facts stay separate from public pet profiles, social content, finance,
advertising, and organization notes. Provider, laboratory, insurance, research,
and AI integrations must reference the canonical record through purpose-bound
access; they may not create a parallel record or broaden access through a UI
label, cached URL, or copied text.
