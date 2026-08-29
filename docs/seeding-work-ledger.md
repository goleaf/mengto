# Seeding System Work Ledger

This ledger coordinates the read-only discovery and independent review work for
the safe reference, development, demo, test-support, and performance seeding
system. The principal agent owns all integration decisions and production-code
edits.

## Discovery analysts

| ID | Subagent | Exclusive scope | Expected output | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| SEED-A01 | Reference Data and Idempotency Analyst | Reference tables, constants, migrations, and existing fixed seeders | Reference-data catalog, stable-key/update policy, idempotency tests, migration notes | Governing docs available | Pending |
| SEED-A02 | Roles and Permissions Seeder Analyst | Authorization requirements, policies, role/permission models, assignments, and seeders | Role/permission matrix, non-destructive synchronization policy, authorization smoke tests | Governing docs available | Pending |
| SEED-A03 | Demo Workflow and Persona Designer | Product routes, pages, models, factories, workflows, personas, and ownership boundaries | Persona/workflow specification, entity graph, expected pages, non-production access policy | Governing docs available | Pending |
| SEED-A04 | Edge-Case and High-Volume Data Designer | Validation/status boundaries, pagination, UI states, and performance data | Edge-case catalog, opt-in volume profiles/commands, performance checks | Governing docs available | Pending |
| SEED-A05 | Media and External-State Seeder Analyst | Storage, media/documents, provider/payment/webhook/import fixtures | Fixture registry, storage lifecycle, render/file tests | Governing docs available | Pending |
| SEED-A06 | Production Safety and Environment-Gating Analyst | Seeder entry points, environment gates, deploy/CI scripts, credentials, destructive behavior | Safety threat model, gates/tests, deployment restrictions | Governing docs available | Pending |
| SEED-A07 | Seeder Orchestration and Performance Analyst | Seeder dependencies, events, hashing, transactions, failure/retry, runtime cost | Orchestration phases, performance targets, retry behavior | Governing docs available | Pending |
| SEED-A08 | Seeder Verification Architect | Test harness, authentication, routes, storage, locales, fresh/repeated seeding | Verification matrix, exact commands, fixture/assertion requirements | Governing docs available | Pending |

## Independent reviewers

| ID | Subagent | Exclusive scope | Expected output | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| SEED-R01 | Seeder Integrity Reviewer | Final seeders, factories, migrations, constraints, and tests | Severity-ranked integrity findings and idempotency verdict | Implementation frozen | Pending |
| SEED-R02 | Demo Scenario Reviewer | Final personas, workflows, routes/pages, locales, assets, and tests | Scenario gaps and demo-usability verdict | Implementation frozen | Pending |
| SEED-R03 | Production Safety Reviewer | Final gates, commands, deployment docs/scripts, credentials, and storage | Safety findings and production-seeding verdict | Implementation frozen | Pending |

## Coordination rules

- Discovery and review agents are read-only unless the principal assigns a
  later, narrowly scoped fix.
- No two agents own an editable file. The principal agent alone integrates
  accepted findings and updates implementation files.
- Every report must distinguish confirmed evidence from suspected findings and
  include scope, exact locations, missing evidence, implementation order,
  tests/commands, and change risk.
- Status changes require the principal agent to inspect the agent report and
  independently validate material claims.

