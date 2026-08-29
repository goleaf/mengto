# API Contracts and Integrations Work Ledger

Date: 2026-08-30
Principal owner: `/root`
Canonical plan: `docs/implementation-plan.md`

This ledger coordinates the read-only discovery and independent review roles
required for the API contracts, integrations, webhooks, payments, and
idempotency work. The principal agent owns all edits, reconciliation,
implementation decisions, and final verification.

## Discovery roles

| Role ID | Subagent | Exclusive scope | Expected output | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| API19-D1 | `api_endpoint_contract_analyst` | API routes, controllers, requests, middleware, public specs, consumers, and endpoint tests | Structured endpoint inventory with request, response, auth, authorization, versioning, error, compatibility, and test findings | Repository instructions and current tree | In progress |
| API19-D2 | `api_resource_serialization_analyst` | API Resources, collections, DTOs, model serialization/casts/visibility, relation loading, and pagination metadata | Structured resource-field matrix with exposure, query, enum/date/money/null, compatibility, and test findings | Repository instructions and current tree | In progress |
| API19-D3 | `external_http_sdk_analyst` | Laravel HTTP calls, provider SDKs, integration configuration, credentials, timeouts, retries, response mapping, logging, and client tests | Structured provider matrix and client/gateway, timeout, retry, error-map, redaction, and test recommendations | Repository instructions and current tree | In progress |
| API19-D4 | `webhook_signature_replay_analyst` | Webhook routes/controllers, raw body/signature verification, event persistence, ordering, acknowledgment, transactions, jobs, logs, and tests | Structured webhook threat/state model and adversarial replay/idempotency test matrix | Repository instructions and current tree | Queued |
| API19-D5 | `payment_integrity_analyst` | Payment models, actions, clients, controllers/Livewire, order/subscription linkage, money/currency, state transitions, reconciliation, logs, and tests | Structured payment state/integrity report and financial negative/concurrency tests | Repository instructions and current tree | Queued |
| API19-D6 | `idempotency_retry_architect` | Request/event keys, constraints, locks, transactions, response replay, retention, retries, and side-effect boundaries | Structured operation-level idempotency matrix with schema/action/test recommendations | Repository instructions and current tree | Queued |
| API19-D7 | `integration_error_observability_analyst` | Integration exceptions, API errors, logs, correlation context, retries, health/operator signals, notifications, and tests | Structured error/observability contract with redaction and failure-path recommendations | Repository instructions and current tree | Queued |
| API19-D8 | `integration_test_fixture_architect` | HTTP fakes, SDK adapters/mocks, payload/signature fixtures, clocks, factories, database constraints, no-network enforcement, and tests | Structured deterministic test matrix, fixture registry, and exact verification commands | Repository instructions and current tree | Queued |

All discovery roles are read-only. They must report: inspected scope; concrete
artifacts; confirmed severity-ranked findings with evidence; explicitly
labelled suspected findings; missing evidence; implementation order; suggested
tests and commands; and change risks. Findings become plan work only after the
principal agent reproduces or validates them.

## Independent review roles

| Role ID | Subagent | Exclusive scope | Expected output | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| API19-R1 | `api_contract_reviewer` | Final routes, requests, resources, DTOs, models, API tests, and contract documentation | Severity-ranked contract findings and readiness verdict with exact locations and failure scenarios | Implementation freeze and review diff | Blocked on implementation |
| API19-R2 | `webhook_payment_security_reviewer` | Final webhook/payment routes, raw-body verification, event/idempotency storage, state machines, transactions, locks, and tests | Adversarial security/integrity findings and replay/payment readiness verdict | Implementation freeze and review diff | Blocked on implementation |
| API19-R3 | `integration_failure_idempotency_reviewer` | Final clients/gateways, config, retries, errors, logs, idempotency storage, and tests | Failure/idempotency findings and resilience verdict | Implementation freeze and review diff | Blocked on implementation |

Review roles remain read-only unless the principal explicitly delegates a
narrowly scoped correction after triage. Each finding must include severity,
exact location, reproducible failure scenario, evidence, and a proposed fix or
regression test. The principal records acceptance, correction, or an
evidence-based rejection in this ledger and the canonical plan.

## Principal integration checkpoints

| Checkpoint | Acceptance evidence | Status |
| --- | --- | --- |
| Initial tree protection | Branch, remotes, staged/unstaged/untracked inspection recorded; unrelated work identified | Complete |
| Discovery reconciliation | Every API19-D role completed and high-impact claims independently validated | Pending |
| Canonical plan gate | Stable IDs, dependencies, owners, files, acceptance criteria, tests, commands, statuses, and rollback notes saved before production edits | Pending |
| Test-first implementation | Each behavior change has an observed failing test before implementation and a passing targeted test after it | Pending |
| Independent review | API19-R1 through API19-R3 completed; every finding disposition recorded | Pending |
| Final verification | Applicable targeted and repository-wide gates observed and recorded without real network access | Pending |
| Git delivery | Task-owned diff isolated, committed on `main`, and pushed only when safe | Pending |
