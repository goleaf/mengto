# Pet Profile Gap Analysis

Date: 2026-07-31

## Summary

The repository has a presentational pet-profile slice and several strong
adjacent domains, but it does not yet provide the canonical digital identity,
multi-manager ownership, granular privacy, evidence provenance, or complete
lifecycle required by the revision. The safe path is to evolve `PetProfile`
in place and link existing modules to it, never to create a second pet entity.

## Critical Gaps

| Area | Existing state | Required state | Resolution |
| --- | --- | --- | --- |
| Identity | Stable key plus owner-scoped slug | One lifelong canonical identity | Preserve `profile_key`; add canonical state, lifecycle history, aliases, and redirects |
| Ownership | One cascading `user_id` | Multiple timed role/permission links | Add manager memberships and keep `user_id` as compatibility primary owner during backfill |
| Status | Free string `active` | Stable enum and audited transitions | Add extensible lifecycle enum, transition action, and immutable events |
| Privacy | One broad visibility string | Profile maximum plus section/field overrides | Add privacy settings with viewer-aware query service and deterministic invalidation |
| Taxonomy | Species/breed strings | Shared taxon and domestic classifications | Add nullable references; retain legacy strings and ambiguous-review state |
| Facts | Plain columns/catch-all JSON | Precision, provenance, verification, privacy, history | Add versioned fact records only for critical data |
| Creation | No real workflow | Minimal draft, autosave, duplicate suggestions, claim | Class-based Livewire form with idempotent action and bounded safe candidates |
| URLs | Two hard-coded demo routes | Stable canonical route and old URL redirect | Route-bind stable profile key and mutable slug alias without breaking demos |
| Media | Presenter URLs | Managed media objects with purpose/privacy/consent | Reuse storage validation and add pet media assignments in a later package |
| Lifecycle | Soft delete only | Hide/archive/delete cooling-off/memorial/transfer/dispute | Dedicated actions and auditable states; no irreversible direct delete |
| Integrations | Mixed FK and string keys | One aggregate reused everywhere | Add FKs in bounded backfills while retaining compatibility keys |
| Moderation | Generic report target | Pet ownership/fraud/safety workflows | Reuse unified reports/cases/actions with pet-specific reasons and freezes |
| UI | Static Blade preview | Complete accessible management | Incremental class-based Livewire surfaces using existing components |

## Highest-Risk Failure Modes

- Replacing `user_id` before manager backfill could orphan current profiles.
- Making slug globally canonical could break existing owner-scoped URLs.
- Eagerly converting species/breed strings could attach the wrong taxon.
- Treating a finder, shelter worker, sitter, or creator as legal owner could
  expose documents, locations, devices, or transfer rights.
- Caching public projections without viewer and privacy context could disclose
  data after a privacy change.
- A broad encrypted JSON document alone cannot enforce field-level access,
  provenance, uniqueness, or concurrent critical actions.
- A table per optional field would create an unmaintainable schema. Only facts
  needing provenance/history receive normalized records.

## Planned Compatibility Strategy

1. Expand `pet_profiles` with nullable canonical fields and version columns.
2. Add manager, permission, lifecycle-event, privacy, alias, idempotency, and
   critical fact tables with indexes and constraints.
3. Backfill one primary-owner manager from every existing `user_id` without
   changing the current owner or profile ID.
4. Dual-read old strings and new relations; dual-write stable compatibility
   keys where an adjacent module still requires them.
5. Add bounded review queues for ambiguous taxonomy and duplicate candidates.
6. Contract legacy fields only in a future explicitly verified migration; no
   contraction is part of the current work.

## Gate Result

Gate 1 discovery is complete. Gate 2 planning and Gates 3-6 are verified only
for the selected canonical identity/access foundation package. The full pet
revision still has open Gate 3 implementation, Gate 4 tests, Gate 5
documentation, and Gate 6 traceability work. Exactly 205 pet IDs have package
evidence; no unselected requirement is implied complete.
