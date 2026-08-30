# Places Directory Query Work Ledger

Date: 2026-08-30

Canonical package: `PLA-P04` / `PLQ-01` through `PLQ-08` in
`docs/implementation-plan.md`.

## Preservation Boundary

- Branch: `main` only; baseline `ae4ac32`, initially aligned with
  `origin/main`.
- The pre-existing canonical-plan additions, modified
  `tests/Fixtures/DatabaseSeedCoverage.php`, and untracked audit ledgers are
  user-owned and excluded from this package.
- Specialists perform read-only discovery and return structured findings. The
  principal agent owns every code, test, migration, documentation, and
  cross-module edit.
- The final reviewer is independent of implementation and receives a frozen
  attributable diff after focused verification passes.

## Specialist Ledger

| Specialist ID | Exclusive scope | Required deliverable | State |
| --- | --- | --- | --- |
| PLQ-S-ELOQUENT | Eloquent query-object shape, selected columns, eager loads, relational/JSON predicates, and absence of collection query behavior | Proposed builder API, predicate map, bounded-loading risks, and source references | assigned after ledger creation |
| PLQ-S-PORTABILITY | SQLite/PostgreSQL behavioral and query-grammar equivalence only | Portable predicate/order recommendations, driver hazards, and executable equivalence checks | assigned after ledger creation |
| PLQ-S-AUTH | Policy-equivalent public/account/organization/owner/grant/status/archive/merge scoping only | Visibility truth table, ordering-of-operations invariants, leak risks, and tests | assigned after ledger creation |
| PLQ-S-INDEX | Index design derived from the final visibility/filter/order query shapes only | Candidate index table with predicate/order served, redundancy/write-cost analysis, and SQLite inspection commands | queued |
| PLQ-S-PAGINATION | Numbered pagination, deterministic secondary order, stale-page behavior, selected row, and query-string preservation only | Pagination invariants, adversarial cases, and recommended Laravel paginator integration | queued |
| PLQ-S-PERFORMANCE | More-than-500 test design, query budget, memory-growth measurement, and N+1 proof only | Deterministic dataset recipe, explicit thresholds, instrumentation, and false-positive controls | queued |
| PLQ-S-ACCESSIBILITY | No-JavaScript server-rendered directory, forms, pagination, focus/semantics, responsive/touch and external-provider independence only | Markup/browser acceptance checklist and defects grounded in current views | queued |
| PLQ-S-REVIEW | Independent final review of the frozen attributable diff; no implementation participation | Severity-ranked findings with reproduction evidence and disposition-ready references | blocked until focused implementation checks pass |

## Principal Reconciliation Rules

1. Visibility is reconciled before any filter, sort, count, selection, or
   pagination design. A convenience that weakens `PlacePolicy` is rejected.
2. Portable Eloquent/schema-builder expressions win over driver-specific SQL.
   If a supported filter cannot be represented safely over canonical data, the
   active UI contract fails closed until a portable persisted projection is
   introduced and tested.
3. Index proposals are accepted only after the final query shapes exist and
   redundant-prefix analysis is recorded.
4. Performance assertions use fixed deterministic data and subtract or compare
   baselines where PHP/test-runner noise would otherwise make them flaky.
5. Accessibility review covers the rendered response with JavaScript disabled;
   a working visual map is not a dependency for accessing every paginated row.
6. Every specialist finding receives an accepted, rejected, deferred, or
   superseded disposition. An accepted finding changes the principal-owned
   implementation and reruns the affected checks.

## Publication Checklist

- [ ] Canonical plan precedes production edits.
- [ ] Large-dataset tests fail against the old cap before implementation.
- [ ] Focused Places and performance tests pass.
- [ ] PRD-PLACE-001 is updated only after that evidence passes.
- [ ] Independent frozen-diff review is dispositioned.
- [ ] Full Pest, Pint, Larastan, migration/seed/repeat, npm build, no-JavaScript
  browser, cache smoke, dependency audit, and `git diff --check` gates pass.
- [ ] A temporary index contains only attributable files; its full staged diff
  is reviewed before the normal commit and push.
