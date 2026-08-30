# Application Architecture Refactor Work Ledger

Task: `06. Application Architecture and Business-Logic Refactoring`

Baseline HEAD: `9540fe83756833ae1c6d22053e883a07dca9f014`

Branch: `main`

Initial shared-tree state: 478 staged entries, 32 unstaged entries, and 6
untracked entries. All pre-existing work is foreign unless this ledger or the
canonical plan records task ownership explicitly.

## Discovery assignments

| ID | Specialist | Exclusive scope | Expected output | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| ARCH06-D01 | Domain Boundary Mapper | Domain requirements, models/migrations, actions/services, workflow and cross-domain dependency mapping | Structured domain map, invariants, owners, dependency direction, implementation order, tests, and risks | Instruction and documentation baseline | Assigned |
| ARCH06-D02 | Route and Controller Responsibility Analyst | `routes/**`, `app/Http/Controllers/**`, middleware, bindings, Form Requests, and HTTP contract tests | Structured route/controller refactor map with authorization, validation, action, response, and route-cache evidence | Instruction and documentation baseline | Assigned |
| ARCH06-D03 | Livewire Responsibility Analyst | `app/Livewire/**`, matching `resources/views/livewire/**`, form objects, direct mutations, and Livewire tests | Structured component responsibility report, extraction candidates, mutation authorization map, tests, and risks | Instruction and documentation baseline | Assigned |
| ARCH06-D04 | Model and Persistence Responsibility Analyst | `app/Models/**`, casts, relationships, scopes, observers, migrations, factories, and model/data tests | Structured model responsibility matrix, schema alignment, side effects, candidates, tests, and risks | Instruction and documentation baseline | Assigned |
| ARCH06-D05 | Action, Service, and Contract Analyst | `app/Actions/**`, `app/Services/**`, contracts, DTOs/value objects/helpers, call sites, transaction ownership | Structured keep/refactor/remove matrix, proposed typed contracts, DI/transaction plan, tests, and risks | Instruction and documentation baseline | Assigned |
| ARCH06-D06 | Event and Side-Effect Analyst | Events, listeners, observers, notifications, mail, jobs, webhooks, and commit ordering | Structured side-effect flow, event/listener findings, ordering/idempotency tests, and risks | Instruction and documentation baseline | Assigned |
| ARCH06-D07 | Architecture Test Designer | Architecture tests, namespace layout, Blade, routes, env usage, Volt ban, and practical dependency rules | Structured architecture-test specification, exclusions, false-positive analysis, expected failures, and commands | Instruction and documentation baseline | Assigned |

All discovery assignments are read-only. Specialists may not edit repository
files, may not silently widen their scope, and must label uncertainty.

## Review assignments

| ID | Reviewer | Exclusive scope | Expected output | Dependency | Status |
| --- | --- | --- | --- | --- | --- |
| ARCH06-R01 | Domain Design Reviewer | Frozen task diff: boundaries, invariants, transactions, abstractions, side effects, tests | Severity-ranked findings with exact locations, failure scenarios, evidence, and corrections | Implementation frozen | Pending |
| ARCH06-R02 | Maintainability and Dependency Reviewer | Frozen task diff: dependency direction, DI, naming, helpers, interfaces, providers, architecture tests | Severity-ranked maintainability findings and boundary corrections | Implementation frozen | Pending |
| ARCH06-R03 | Behavioral Regression Reviewer | Frozen task diff: routes, responses, Livewire flows, persistence, events, side effects, requirements, tests | Behavioral-equivalence verdict and severity-ranked regressions or missing tests | Implementation frozen | Pending |

Review assignments are read-only until the principal agent has dispositioned
their findings. Reviewers must be independent from discovery/implementation
work on the same affected slice.

## Principal-agent checkpoints

- [x] Initial branch, remote, HEAD, staged, unstaged, and untracked state captured.
- [ ] Mandatory documents and project conventions read.
- [ ] Discovery reports received and material findings independently reproduced.
- [ ] `docs/implementation-plan.md` updated before production-code changes.
- [ ] TDD implementation passes completed without absorbing foreign work.
- [ ] Frozen diff independently reviewed and all findings dispositioned.
- [ ] Applicable final gates observed.
- [ ] Task-owned temporary-index commit created and `main` push result recorded.
