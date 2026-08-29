# Database Domain Audit Work Ledger

This ledger coordinates the complete model, migration, relationship, factory,
seeder, constraint, and verification audit requested on 2026-08-30. It does
not replace the canonical execution plan in `docs/implementation-plan.md`.
The principal agent owns every cross-module decision and every tracked edit.

## Working-tree protection baseline

- Branch: `main`, tracking `origin/main`.
- The task began in a dirty shared tree containing staged and unstaged user
  work across documentation, application code, tests, dependency manifests,
  seeders, and Playwright captures.
- Existing work must remain byte-present. Task-owned publication uses a
  temporary `GIT_INDEX_FILE` and never resets, discards, force-pushes, or
  rewrites history.
- Destructive database checks may target only an asserted operating-system
  temporary SQLite path.

## Discovery workstreams

| ID | Specialist | Exclusive ownership | Structured deliverable | Wave | Status |
| --- | --- | --- | --- | --- | --- |
| DBA-01 | Migration and schema analyst | Migration/schema-builder truth: tables, columns, keys, indexes, checks, pivots, morph columns, lifecycle | Files/tables inspected; canonical schema map; anomalies; inferred relation edges; risks; verification | 1 | In progress |
| DBA-02 | Model analyst | Eloquent class truth: table/key configuration, traits, attributes, casts, hidden data, factories, declared relations | Files/models inspected; migration mismatches; cast/factory defects; missing or incorrect model members; risks | 1 | In progress |
| DBA-03 | Relationship graph specialist | Cross-domain relationship graph derived from schema and application usage | Complete directed graph; inverse/pivot/morph/through coverage; missing implementation; safe additions; risks | 1 | In progress |
| DBA-04 | Factory specialist | Factory definitions and all schema fields, enum/state realism, uniqueness, encrypted/custom casts | Factory coverage and validity matrix; field gaps; missing factories/states; collision risks; exact changes/tests | 2 | Pending |
| DBA-05 | Factory relationship specialist | Factory parent reuse, graph multiplication, circularity, pivot/morph helpers, relationship states | Relationship-factory graph; recursion/explosion findings; Laravel-native repair plan; count estimates | 2 | Pending |
| DBA-06 | Seeder architect | Seeder inventory, dependency ordering, environment gating, idempotency, complete 10-plus-record graph | Seeder DAG; current counts; missing domains; deterministic-user plan; safe orchestration and rollback | 2 | Pending |
| DBA-07 | Constraint and validation specialist | Form/Livewire validation, enums, custom rules, observers/events, DB uniqueness/check/date/money constraints | Constraint matrix; factory/seeder invalid-state risks; required value/state coverage; test cases | 3 | Pending |
| DBA-08 | Test and QA specialist | Pest database suites, isolated lifecycle scripts, count/field/relation/orphan/idempotency assertions | Verification matrix; current failures/gaps; exact red tests and commands; runtime-cost risks | 3 | Pending |
| DBA-09 | Independent adversarial reviewer | Frozen attributable implementation diff across models, migrations, factories, seeders, tests, and docs | Severity-ranked findings with exact evidence, dispositions required, and release verdict | Review | Pending |

Every discovery report must state files and entities inspected, confirmed
problems, inferred relationships, missing implementation, recommended changes,
validation requirements, risks, and exact verification commands. Discovery
agents make no tracked edits. The reviewer is independent from implementers.

## Principal checkpoints

| Checkpoint | Acceptance evidence | Status |
| --- | --- | --- |
| DBA-CP-01 | Repository contract, branch, dirty tree, toolchain, model/migration/factory/seeder/test inventory captured | In progress |
| DBA-CP-02 | Nine specialist workstreams reconciled into one canonical schema/relationship audit | Pending |
| DBA-CP-03 | Canonical implementation plan updated before production-code changes | Pending |
| DBA-CP-04 | Failing Pest contracts observed for missing seed/count/field/relationship behaviour | Pending |
| DBA-CP-05 | Relationships, factories, seeders, deterministic account, representative fields, and 10-plus model counts implemented | Pending |
| DBA-CP-06 | Targeted, isolated lifecycle, static, formatting, full-suite, dependency, build, and cache gates observed | Pending |
| DBA-CP-07 | Frozen diff independently reviewed; every material finding dispositioned, fixed when valid, and retested | Pending |
| DBA-CP-08 | Plan/evidence finalized; attributable diff isolated, checked, committed, and safely pushed | Pending |
