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
| DBA-01 | Migration and schema analyst | Migration/schema-builder truth: tables, columns, keys, indexes, checks, pivots, morph columns, lifecycle | Files/tables inspected; canonical schema map; anomalies; inferred relation edges; risks; verification | 1 | Complete |
| DBA-02 | Model analyst | Eloquent class truth: table/key configuration, traits, attributes, casts, hidden data, factories, declared relations | Files/models inspected; migration mismatches; cast/factory defects; missing or incorrect model members; risks | 1 | Complete |
| DBA-03 | Relationship graph specialist | Cross-domain relationship graph derived from schema and application usage | Complete directed graph; inverse/pivot/morph/through coverage; missing implementation; safe additions; risks | 1 | Complete |
| DBA-04 | Factory specialist | Factory definitions and all schema fields, enum/state realism, uniqueness, encrypted/custom casts | Factory coverage and validity matrix; field gaps; missing factories/states; collision risks; exact changes/tests | 2 | Complete |
| DBA-05 | Factory relationship specialist | Factory parent reuse, graph multiplication, circularity, pivot/morph helpers, relationship states | Relationship-factory graph; recursion/explosion findings; Laravel-native repair plan; count estimates | 2 | Complete |
| DBA-06 | Seeder architect | Seeder inventory, dependency ordering, environment gating, idempotency, complete 10-plus-record graph | Seeder DAG; current counts; missing domains; deterministic-user plan; safe orchestration and rollback | 2 | Complete |
| DBA-07 | Constraint and validation specialist | Form/Livewire validation, enums, custom rules, observers/events, DB uniqueness/check/date/money constraints | Constraint matrix; factory/seeder invalid-state risks; required value/state coverage; test cases | 3 | Complete |
| DBA-08 | Test and QA specialist | Pest database suites, isolated lifecycle scripts, count/field/relation/orphan/idempotency assertions | Verification matrix; current failures/gaps; exact red tests and commands; runtime-cost risks | 3 | Complete |
| DBA-09 | Independent adversarial reviewer | Frozen attributable implementation diff across models, migrations, factories, seeders, tests, and docs | Severity-ranked findings with exact evidence, dispositions required, and release verdict | Review | Complete; corrected tree release ready |

Every discovery report must state files and entities inspected, confirmed
problems, inferred relationships, missing implementation, recommended changes,
validation requirements, risks, and exact verification commands. Discovery
agents make no tracked edits. The reviewer is independent from implementers.

## Implementation workstreams

| ID | Implementer | Exclusive edit scope | Deliverable | Status |
| --- | --- | --- | --- | --- |
| DBA-I01 | Principal | `RepresentativeDomainSeeder`, root seeder identity conflict handling, schema-driven count/field/pivot integrity tests | Collision-free bounded root seed, complete repeatability, 10-plus records and integrity evidence | Complete |
| DBA-I02 | Constraint specialist | Owner/coordinator coherence and local absolute URLs in `ExpertProfileFactory`, `CareJournalFactory`, `ListingFactory`, `SearchCaseFactory`, `SmartDeviceFactory`; first-party image URLs in the seven reported seeders; focused tests | Valid local media and relational owner snapshots without changing representative seeder code | Complete |
| DBA-I03 | QA specialist | The four reported forum datetime Livewire form classes and new focused timezone tests only | Timezone-aware future/order validation with frozen offset-zone regressions | Complete |
| DBA-I04 | Seeder specialist | `FactoryAndSeederTest` query-listener/filesystem isolation and `scripts/run-tests.php` pre-bootstrap storage/cache isolation only | Hermetic factory verification and runnable isolated test harness | Complete |
| DBA-I05 | Constraint specialist | `ReviewFactory`, `PublicationFactory`, and a new focused aggregate-coherence test only | Verified review/booking/service coherence plus accurate review/publication aggregates | Complete |
| DBA-I06 | QA specialist | `ForumEventFactory`, `ForumEventSessionFactory`, `PetProfileFactFactory`, PetProfile factory status states, and a new focused state test only | Chronologically valid live states, complete microchip payload, and persisted status coverage | Complete |
| DBA-I07 | Seeder specialist | `CompleteDatabaseSeederTest` only | Unscoped counts, bounded growth, target identity conflict, pivot metadata, seeded private file, and schema-derived orphan assertions | Complete |
| DBA-I08 | Documentation specialist | Seeding generator, generated coverage matrix, and canonical seeding guide | Byte-identical 204-model factory/state/seeder evidence with safe `--write` and `--check` modes | Complete |
| DBA-I09 | Documentation specialist | Database-domain audit generator and generated canonical audit | Byte-identical per-model schema/relationship/constraint/factory/seeder/count evidence from isolated SQLite | Complete |

## Independent-review correction workstreams

All DBA-09 findings were reproduced or accepted from the reviewer's isolated
evidence. None is waived. The post-review implementers have exclusive paths;
the principal owns reconciliation and the independent reviewer owns the final
rereview.

| ID | Owner | Exclusive edit scope | Acceptance evidence | Status |
| --- | --- | --- | --- | --- |
| DBA-R01 | Runner-safety implementer | `scripts/run-tests.php`, Composer/PHPUnit test entry points, testing guide, hostile-environment runner regression | Exported hostile database/session/cache/queue/mail variables cannot influence the owned temporary SQLite test runtime; sentinel remains byte-identical | Complete |
| DBA-R02 | Seed-graph implementer | Medical-record and forum-journal factories/seeding plus focused ownership/topic graph tests | Canonical medical pet/owner snapshots agree; every seeded journal uses a journal topic and its author/owner identity | Complete |
| DBA-R03 | Relationship implementer | Missing schema/polymorphic inverses and relationship integrity tests | Direct, directed-pivot, taxonomy/import, registration, and report-subject inverses round-trip with metadata | Complete |
| DBA-R04 | Principal | UTC persistence, deterministic-user collision safety, expert/search lifecycle factories, side-effect-free supplied-aggregate factory `make()`, discarded-parent prevention | Finding-specific red/green tests prove every corrected invariant | Complete |
| DBA-R05 | Principal | Explicit nullable/JSON representative coverage, parameterized helper coverage, generated evidence exactness/fail-closed checks | No schema-unqualified nullable exemption; major optional fields and structured values are represented; generators report only observed verified evidence | Complete |
| DBA-R07 | Audit-evidence implementer | Database-domain audit generator, generated audit, and generator-focused tests only | Exact seeded counts and all migration files are inventoried; `--check` fails closed on missing factories, underfilled models, orphans, target-user absence, and unmapped migrations | Complete |
| DBA-R06 | Independent reviewer | Frozen post-correction diff | Every DBA-09 finding is dispositioned and no material regression remains | Complete; three independent reviewers found no material attributable issue |

## Principal checkpoints

| Checkpoint | Acceptance evidence | Status |
| --- | --- | --- |
| DBA-CP-01 | Repository contract, branch, dirty tree, toolchain, model/migration/factory/seeder/test inventory captured | Complete |
| DBA-CP-02 | Nine specialist workstreams reconciled into one canonical schema/relationship audit | Complete |
| DBA-CP-03 | Canonical implementation plan updated before production-code changes | Complete |
| DBA-CP-04 | Failing Pest contracts observed for missing seed/count/field/relationship behaviour | Complete |
| DBA-CP-05 | Relationships, factories, seeders, deterministic account, representative fields, and 10-plus model counts implemented | Complete |
| DBA-CP-06 | Targeted, isolated lifecycle, static, formatting, full-suite, dependency, build, and cache gates observed | Complete except the unavailable external forum-history entry; 2,441 of 2,442 partitioned tests pass with 105,112 assertions, and bounded sequential PHP suites avoid the unstable oversized PHP 8.5 process |
| DBA-CP-07 | Frozen diff independently reviewed; every material finding dispositioned, fixed when valid, and retested | Complete; schema/relationship and two factory/seeder adversarial rereviews report release ready |
| DBA-CP-08 | Plan/evidence finalized; attributable diff isolated, checked, committed, and safely pushed | Evidence complete; publication blocked by missing forum entry `1785397895`, so no commit or push was performed |

## P07 Eloquent, migrations, query performance, and integrity continuation

This section is the work ledger for the repository-wide P07 mission begun on
2026-08-30. It supplements the historical DBA workstreams above and does not
replace `docs/implementation-plan.md`. The principal agent is the sole
integrator and owns every edit, cross-domain decision, finding disposition,
verification claim, commit, and push decision.

### P07 working-tree protection baseline

- Branch: `main` at `9540fe83756833ae1c6d22053e883a07dca9f014`,
  tracking `origin/main` at `462539c0ff63bc80a18c7f73b402cb41df02eead`.
- Initial status contained 486 staged entries, 34 unstaged entries, eight
  untracked entries, and 492 total paths. Those existing bytes are not P07
  ownership merely because they overlap database or performance concerns.
- Discovery and review agents are read-only and may write reports only under
  `/tmp/p07-*`. They must not edit, stage, commit, push, alter configuration,
  start shared servers, or run destructive/shared-database commands.
- P07-owned publication uses a temporary `GIT_INDEX_FILE`. No reset, clean,
  stash, force push, history rewrite, historical migration edit, or destructive
  database command is permitted.

### P07 discovery assignments

Every analyst must return the eight-part structured report required by the P07
mission: inspected scope; exact entities/files; confirmed severity-ranked
findings with evidence; suspected findings; missing evidence; implementation
order; exact tests/commands; and change risks.

| ID | Specialist | Exclusive read-only scope | Report path | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| P07-A01 | Schema Inventory and Consistency Analyst | All migrations, schema discovery/generators, models-to-table/key mapping, columns, constraints, indexes, pivots, engine differences | `/tmp/p07-schema-inventory.md` | Baseline only | Assigned |
| P07-A02 | Eloquent Relationship and Cast Analyst | Models, enums, casts, accessors, observers/events, factories, serialization, mass assignment, relation keys and ownership scopes | `/tmp/p07-eloquent-relations.md` | Baseline only | Assigned |
| P07-A03 | Query Performance and N+1 Analyst | Controllers, Livewire, Actions, Services, Resources, Blade/presenters, exports/imports and query-budget tests | `/tmp/p07-query-performance.md` | Baseline only | Assigned |
| P07-A04 | Migration Safety and Data-Backfill Analyst | Migration ordering, historical immutability, deploy/rollback, schema dumps, backfills, mixed-version and SQLite portability | `/tmp/p07-migration-safety.md` | P07-A01 evidence may be reconciled by principal only | Assigned |
| P07-A05 | Data Integrity and Constraint Analyst | Requirements-to-schema/application invariants, foreign/unique/check constraints, cascades, money/timezone/nullability/status integrity | `/tmp/p07-data-integrity.md` | P07-A01 evidence may be reconciled by principal only | Assigned |
| P07-A06 | Concurrency, Transaction, and Idempotency Analyst | Race-sensitive Actions/Services, tokens, invitations, payments/orders, counters, imports/webhooks, locks/retries/after-commit effects | `/tmp/p07-concurrency.md` | Baseline only | Assigned |
| P07-A07 | Database Test and Fixture Analyst | Test database isolation, factories/seeders, lifecycle scripts, fresh/upgrade/constraint/query/concurrency coverage | `/tmp/p07-database-tests.md` | All discovery evidence reconciled later by principal | Assigned |

### P07 implementation and review ownership

| ID | Owner | Exclusive scope | Acceptance evidence | Status |
| --- | --- | --- | --- | --- |
| P07-I01 | Principal | Canonical plan, accepted schema/model/constraint/migration fixes and regression tests | TDD red/green evidence and focused commands | Pending discovery |
| P07-I02 | Principal | Accepted bounded-query/index/transaction/idempotency fixes and regression tests | Before/after budgets, plans, deterministic race evidence | Pending discovery |
| P07-I03 | Principal | Factories, seeders, isolated lifecycle verification, canonical documentation and generators | Fresh/upgrade/repeat-seed and byte-parity evidence | Pending discovery |
| P07-R01 | Independent Database Integrity Reviewer | Frozen P07 attributable diff: models, constraints, Actions, tests, representative data states | Exact invariant findings, violation attempts, deletion/restore trace | Pending implementation |
| P07-R02 | Independent Migration Safety Reviewer | Frozen P07 migrations/backfills/deployment/rollback/tests | Fresh and upgrade walkthrough, SQL/lock risk, go/no-go conditions | Pending implementation |
| P07-R03 | Independent Query Performance Reviewer | Frozen P07 query/index/pagination/metric/test diff | Before/after metrics, EXPLAIN evidence, hidden-query/high-volume review | Pending implementation |

### P07 principal checkpoints

| Checkpoint | Acceptance evidence | Status |
| --- | --- | --- |
| P07-CP01 | Instruction chain, canonical documents, current branch, dirty ownership and runtime baseline inspected | In progress |
| P07-CP02 | Seven discovery reports reconciled; every material claim independently reproduced | Pending |
| P07-CP03 | `docs/implementation-plan.md` updated before P07 production-code edits | Pending |
| P07-CP04 | Accepted defects implemented via observed red/green regression cycles | Pending |
| P07-CP05 | Fresh/upgrade/seed/constraint/strict/query/concurrency gates observed in isolated runtimes | Pending |
| P07-CP06 | Frozen P07 diff reviewed by all three independent reviewers; findings dispositioned and retested | Pending |
| P07-CP07 | Canonical docs/evidence synchronized; task-owned staged diff checked, committed and safely pushed | Pending |
