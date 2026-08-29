# Repository Audit Work Ledger

This ledger coordinates the repository-wide discovery, foundational-repair,
independent-review, and delivery work requested on 2026-08-30. It is not a
replacement for the canonical execution plan in `docs/implementation-plan.md`.

## Working-tree protection baseline

- Branch: `main`, tracking `origin/main`.
- Remote: `origin` uses `git@github.com:goleaf/mengto.git` for fetch and push.
- Pre-existing user work: 89 staged paths and one unstaged path were present at
  the start of this task. The staged slice includes first-party documentation,
  Playwright snapshots, and screenshot deletions. The unstaged slice is
  `docs/validation-error-work-ledger.md`.
- Protection rule: audit-owned changes must be reviewed separately and staged
  through a temporary `GIT_INDEX_FILE`; no pre-existing staged or unstaged work
  may be reset, discarded, rewritten, or claimed by this task.

## Analysis work ledger

| ID | Subagent | Exclusive scope | Expected output | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| AUD-SA-01 | Repository Archaeologist | First-party tree, local Git history, entry points, modules, critical workflow call chains, legacy/dead-code evidence | Structured module map, workflow traces, structural risks, prioritized audit items | Read-only repository access | Complete |
| AUD-SA-02 | Instruction and Documentation Auditor | Root/nested instructions and every first-party Markdown file outside generated/third-party trees | Complete classified Markdown inventory, conflicts, precedence, canonical tree, durable instruction recommendations | Read-only repository access | Complete |
| AUD-SA-03 | Dependency and Runtime Auditor | Composer/npm metadata and locks, platform constraints, CI/runtime/bootstrap/deployment configuration | Resolved stack and compatibility matrix, exact blockers, safe baseline commands, dependency risks | Read-only repository access | Complete |
| AUD-SA-04 | Laravel Architecture Auditor | Routes, middleware, controllers, providers, Actions, Services, models, policies, requests, resources, events, jobs, commands | Severity-ranked architecture findings, critical dependency graph, immediate safe fixes, later phases | Results from initial structure scan helpful but not required | In progress |
| AUD-SA-05 | Livewire and Frontend Auditor | Livewire, Blade, Alpine/JavaScript, CSS/Tailwind, Vite, browser tests | Component inventory, Blade purity findings, build/lifecycle blockers, targeted tests | Dependency baseline helpful but not required | In progress |
| AUD-SA-06 | Database, Security, and Integrity Auditor | Migrations, models, constraints, queries, auth/policies, tokens, files, webhooks, caches, seed safety | Schema/integrity register, exploit-oriented security findings, immediate remediations, regression tests | Read-only repository access | In progress |
| AUD-SA-07 | Testing, Factory, and Seeder Auditor | Pest/browser suites, test config/CI, factories, seeders, fixtures, fakes, architecture tests | Exact test baseline, factory/seeder inventory, missing coverage, stabilization steps | May run only safe isolated/test commands | Pending |

Every analysis report must state: inspected scope; exact symbols and files;
confirmed findings with severity/evidence; suspected findings; missing evidence;
implementation order; tests/commands; and change risks. Agents are read-only.
The principal agent verifies high-impact claims and owns all edits and decisions.

## Independent review ledger

| ID | Reviewer | Exclusive review scope | Expected output | Dependency | Status |
| --- | --- | --- | --- | --- | --- |
| AUD-RV-01 | Repository Audit Correctness Reviewer | Final inventory, plan, audit evidence, foundational diff | Completeness scores and exact factual/scope corrections | Implementation frozen and review diff captured | Pending |
| AUD-RV-02 | Security and Data-Integrity Reviewer | Foundational diff plus adjacent auth, config, migration, file, ownership, and seed paths | Exploit-oriented findings, safe controls, blockers | Implementation frozen and review diff captured | Pending |
| AUD-RV-03 | Test and Regression Reviewer | Changed code/tests/docs, baseline failures, factories/seeders, CI command parity | Missing/false-positive/flaky tests and readiness verdict | Implementation frozen and review diff captured | Pending |

Reviewers are independent of the seven analysis agents, remain read-only until
their reports are complete, and must include severity, exact location, failure
scenario, evidence, and a proposed correction or test for every finding.

## Principal integration checkpoints

| Checkpoint | Acceptance evidence | Status |
| --- | --- | --- |
| AUD-CP-01 Discovery baseline | Git/runtime/toolchain commands captured; instruction chain loaded; all analysis reports reconciled | In progress |
| AUD-CP-02 Canonical plan gate | `docs/implementation-plan.md` contains stable audit items, owners, dependencies, paths, acceptance criteria, tests, commands, status, and rollback notes | Pending |
| AUD-CP-03 Foundational repairs | Each accepted in-scope blocker has a failing-before/passing-after regression test or reproducible check | Pending |
| AUD-CP-04 Independent review | Three required reviewers complete; every material finding is dispositioned and valid findings are fixed | Pending |
| AUD-CP-05 Delivery | Applicable final gates rerun; task-owned diff isolated; coherent commit(s) created and safe push attempted | Pending |
