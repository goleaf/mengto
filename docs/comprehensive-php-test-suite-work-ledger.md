# Comprehensive PHP Test Suite Work Ledger

Task owner: principal Codex agent  
Branch: `main`  
Started: 2026-08-30  
Canonical plan: `docs/implementation-plan.md`

This ledger coordinates the repository-wide PHP verification task. Discovery
and review specialists are read-only. The principal agent owns plan changes,
implementation, reconciliation, test execution, documentation, staging,
commits, and publication. Status changes require a structured report or
directly observed verification evidence.

## Discovery Specialists

| ID | Specialist | Exclusive scope | Expected output | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| D01 | Authentication Test Specialist | Auth requirements/code, guards, sessions, cookies, tokens, invitations, reset, verification, MFA, throttles, auth factories/seeders/tests | State/action matrix, fixture gaps, high-risk gaps, tests and commands | Core instruction and test-strategy reading | in progress |
| D02 | Authorization and Tenant Test Specialist | Policies, gates, protected routes, tenant/owner/member/privacy scopes, private files/caches/commands | Role/action/state matrix, factory graphs, severity-ranked gaps | Core instruction and authorization reading | in progress |
| D03 | Domain, Database, and State-Machine Test Specialist | Actions, services, models, enums, migrations, constraints, transactions, locks, factories and database tests | Domain/database catalogue, migration fixtures, branch targets | Core instruction, domain and data-model reading | in progress |
| D04 | Livewire Test Specialist | Every class component, form object, separate view, public property/method, policy/query/event/upload/sort/pagination boundary | Component/action matrix, PHP-test gaps, browser-only gaps | Core instruction, Livewire and frontend reading | pending |
| D05 | API and Integration Test Specialist | API/JSON routes, Resources, clients, webhooks, provider DTOs, payments, retries, fakes and network isolation | Contract matrix, fixture registry, failure gaps | Core instruction, integrations/security reading | pending |
| D06 | Files, Cache, and Performance Test Specialist | Upload/download/media lifecycle, cache ownership/TTL/invalidation/locks, query-heavy pages and bounded processing | File/cache/performance plan, fixtures/instrumentation, regression thresholds | Core instruction, files/cache/performance reading | pending |
| D07 | Localization and Accessibility Test Specialist | EN/LT/RU catalogues and formatting, critical semantics, keyboard/focus/reflow and browser runners | Locale/a11y matrix, PHP/browser gaps, manual notes | Core instruction, localization/accessibility reading | pending |
| D08 | Architecture Test Specialist | AGENTS rules, source boundaries, Blade, config, dependency metadata, no-network and test architecture | Enforceable rule design, exclusions, maintenance guidance | Core instruction and architecture reading | pending |
| D09 | Coverage, Flakiness, and Test-Quality Analyst | Pest/PHPUnit config, CI, coverage, assertions, skips/risks, clocks/random/files/network, parallel/order/repeat behavior | Quality report, risk-ranked gaps, stabilization/threshold policy | Baseline commands and test inventory | pending |

Every discovery report must include: inspected scope; examined files/classes/
routes/tables/components/workflows; confirmed findings with severity and
evidence; suspected findings and missing evidence; missing coverage;
implementation order; suggested tests/commands; and change risks.

## Independent Review Specialists

| ID | Reviewer | Exclusive review scope | Expected output | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| R01 | Test Quality Reviewer | Changed tests, fixtures, mocks, application fixes and requirement links | Exact behavioral findings, strengthening/removal recommendations, confidence verdict | Broad implementation frozen | blocked by implementation |
| R02 | Missing Scenario Reviewer | Critical workflow branches across auth, policy, Livewire, domain, integrations, files/cache/locales | Severity backlog with exact locations, scenarios, tests and requirement IDs | Broad implementation frozen | blocked by implementation |
| R03 | Flakiness and False-Positive Reviewer | Test config, factories, seeds, fakes, time, random, queues, files, browser waits and parallelism | Determinism findings, stabilization required, repeat evidence | Broad implementation frozen | blocked by implementation |
| R04 | Coverage Integrity Reviewer | Coverage config/report, exclusions, removed tests and uncovered high-risk code | Integrity findings, required tests/exclusion reversals, factual coverage statement | Final coverage attempt/report | blocked by implementation |

Each review finding must state severity, exact location, reproducible failure
scenario, evidence, and a proposed correction or test. Reviewers remain
read-only unless the principal delegates a later narrow fix.

## Principal Integration Ledger

| ID | Work item | Dependency | Acceptance evidence | Status |
| --- | --- | --- | --- | --- |
| P01 | Capture repository and dependency inventory | Core reading | Version, route, policy, component, model, migration, test and CI counts | in progress |
| P02 | Capture honest baseline | P01 | Serial/parallel/coverage attempts with counts, durations, skips/risks/failures | pending |
| P03 | Reconcile discovery into canonical plan | D01-D09, P02 | Stable plan IDs with owner, files, criteria, commands, rollback and status | pending |
| P04 | Implement test infrastructure and architecture rules | P03 | RED/GREEN evidence and targeted tests | pending |
| P05 | Add risk-prioritized behavioral coverage and fix exposed defects | P04 | Regression tests, targeted suites and application-fix evidence | pending |
| P06 | Run independent review and dispositions | P05, R01-R04 | Valid findings fixed and rejected findings reasoned | pending |
| P07 | Run final gates and synchronize documentation | P06 | Exact observed commands/results and honest matrix statuses | pending |
| P08 | Publish only attributable changes | P07 | Temporary-index staged diff, commit hashes and push result | pending |
