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

## Implementation Specialists

| ID | Specialist | Exclusive scope | Expected output | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| D01 | Authentication Test Agent | Authentication only: login, logout, registration, invitation/reset/verification/magic-link/MFA/token replay/rate limit/session behavior and auth-localized messages | Current behavior matrix; exact uncovered branches; required auth factories/fixtures; RED tests and smallest application fixes; commands/results | Core instruction, auth/security and test-strategy reading | in progress |
| D02 | Authorization Test Agent | Authorization only: every policy/gate/role/ownership/tenant/direct action/private resource/IDOR boundary | Policy-action-role matrix; exact missing policy/HTTP/Livewire tests; required factory graphs; RED tests and smallest application fixes; commands/results | Core instruction, authorization/security and test-strategy reading | in progress |
| D03 | Domain and Database Test Agent | Domain/database only: models, enums, casts, scopes, soft deletes, Actions, migrations/constraints/transactions/races, factories and seeders | Domain branch catalogue; constraint/relationship/migration/factory/seeder gaps; RED tests and smallest application fixes; commands/results | Core instruction, domain/data/seeding and test-strategy reading | in progress |
| D04 | Livewire Test Agent | Livewire only: all class components/forms, mount/state/locked fields/actions/redirects/events/pagination/filters/URL/session/uploads/sort/query counts/forged calls | Component-action matrix; PHP-test vs browser-only gaps; RED tests and smallest component fixes; commands/results | Core instruction, Livewire/frontend and test-strategy reading | pending |
| D05 | API and Integration Test Agent | API/integrations only: JSON routes/resources/idempotency/malformed JSON/pagination/errors, clients/webhooks/timeouts/retries/network isolation/log redaction | Contract/failure matrix; exact missing tests/fakes/fixtures; RED tests and smallest application fixes; commands/results | Core instruction, security/deployment and test-strategy reading | pending |
| D06 | Files and Cache Test Agent | Files/cache only: upload validation/private download/replace/delete/cleanup/thumbnails and cache scopes/TTL/invalidation/locks/stampede | Lifecycle/key matrix; exact missing tests/fixtures/lock probes; RED tests and smallest application fixes; commands/results | Core instruction, security/caching/deployment and test-strategy reading | pending |
| D07 | Architecture Test Agent | Architecture/localization/security guardrails only: Volt/Blade/env/debug/dynamic Tailwind/frontend/demo-account/no-network prohibitions plus EN/LT/RU keys/placeholders/plurals/formatting | Enforceable architecture rules and bounded exclusions; missing regression tests; exact commands/results | Core instruction, architecture/frontend/localization/testing reading | pending |
| D08 | Coverage and Mutation Risk Agent | Coverage/test-quality only: framework/config/counts/skips/assertions/meaningful branches, mutation-survival risks, clocks/random/files/network, parallel/order/repeat safety | Coverage feasibility and target; risk-ranked likely surviving mutations; false-positive/flakiness findings; exact commands/results | Baseline commands and test inventory | pending |

Every specialist report must include: inspected scope; examined files/classes/
routes/tables/components/workflows; confirmed findings with severity and
evidence; suspected findings and missing evidence; missing coverage;
implementation order; required fixtures/factories/seeders; concrete files the
principal may change; suggested tests/commands; and change risks. Specialists
must not commit, push, reset, stage, or edit outside their exclusive scope.

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
| P03 | Reconcile discovery into canonical plan | D01-D08, P02 | Stable plan IDs with owner, files, criteria, commands, rollback and status | pending |
| P04 | Implement test infrastructure and architecture rules | P03 | RED/GREEN evidence and targeted tests | pending |
| P05 | Add risk-prioritized behavioral coverage and fix exposed defects | P04 | Regression tests, targeted suites and application-fix evidence | pending |
| P06 | Run independent review and dispositions | P05, R01-R04 | Valid findings fixed and rejected findings reasoned | pending |
| P07 | Run final gates and synchronize documentation | P06 | Exact observed commands/results and honest matrix statuses | pending |
| P08 | Publish only attributable changes | P07 | Temporary-index staged diff, commit hashes and push result | pending |
