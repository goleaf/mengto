# Event Financial And Attendance Work Ledger

Date: 2026-08-30
Principal owner: `/root`
Canonical plan: `docs/implementation-plan.md`

This ledger coordinates the read-only specialist discovery and independent
review for the provider-ready event financial, ticket, QR, offline scanning,
and attendance package. The principal owns every cross-domain decision,
repository edit, test-first implementation step, finding disposition, final
verification command, commit, and push.

The task starts on `main` at `ae4ac32`. The initial working tree contained
unrelated user-owned changes to `docs/implementation-plan.md`,
`tests/Fixtures/DatabaseSeedCoverage.php`, and several untracked audit ledgers.
After discovery began, concurrent workstreams added or staged substantial
Places, Portal, forum-category, event-planning, seeding, generated-evidence,
and test changes. Every such path and hunk remains byte-preserved and outside
this delivery. Publication must ignore the shared index and use a temporary
`GIT_INDEX_FILE` containing only attributable paths and hunks.

## Discovery roles

| ID | Specialist | Exclusive scope | Structured deliverable | Dependencies | Editing boundary | Status |
| --- | --- | --- | --- | --- | --- | --- |
| EFA-D1 | Payment integrity specialist | Event payment attempts, amount/currency ownership, payment/refund/dispute state graphs, idempotency, races, failure and expiry | State/invariant matrix, lock order, negative and concurrency cases, exact affected symbols | Event, security, integration, and P19 requirements | Read-only | complete; durable independent aggregates, server-owned values, stable lock order, financial idempotency, refund bounds, and cancellation integration required |
| EFA-D2 | Provider-boundary specialist | Existing external clients/configuration, null/disabled drivers, gateway DTO/error contracts, timeout/rate-limit/malformed/partial-failure behavior | Provider applicability map, minimal real-boundary interface, disabled-state and fake-client test contract | Current integration inventory and SYS-HTTP-001 | Read-only | complete; no provider exists, disabled capability and test-only fake boundary required, no production adapter or paid enablement allowed |
| EFA-D3 | Webhook-security specialist | Raw request/signature order, timestamp tolerance, provider-event uniqueness, replay/out-of-order handling, acknowledgement and redacted audit | Threat/state model, signature sequence, event schema/processing contract, adversarial fake matrix | Provider boundary and SYS-WEBHOOK-001 | Read-only | complete; route must remain disabled without a provider, verifier owns provider-specific semantics, unique inbox precedes mutation, raw payloads are not retained |
| EFA-D4 | Ticket-state specialist | Ticket type/price/sales/inventory/limits/eligibility, reservation, issue, cancellation, transfer exclusions, refund linkage | Ticket and reservation graph, database constraints/indexes, eligibility and inventory race cases | Existing event registration/capacity model and P19 | Read-only | queued |
| EFA-D5 | QR-security specialist | Opaque credential generation/digest/purpose/event scope/expiry/revocation, identity-sensitive use, replay and duplicate scans | QR threat model, payload allowlist, credential lifecycle, concurrent-scan tests | Ticket state and P21 | Read-only | queued |
| EFA-D6 | Offline-synchronization specialist | Scanner/device sessions, minimized capture, operation identity, source/server time, ordered reconciliation, conflicts, retention/deletion | Offline record/schema contract, sync algorithm, fail-closed precedence table, partial-batch tests | Ticket/QR state and P21 | Read-only | queued |
| EFA-D7 | Accounting specialist | Immutable financial audit, balanced amount-purpose records, reconciliation runs/items, receipts, corrections, refund/chargeback evidence | Ledger/reconciliation model, invariants, mismatch handling, retention and operator evidence tests | Payment/provider/webhook findings | Read-only | queued |
| EFA-D8 | Testing specialist | Pest layers, HTTP/webhook fakes, no-network enforcement, factories/seeds, SQLite concurrency, localization/accessibility/browser gates | Red-first test matrix, fixtures, exact commands, deterministic concurrency and browser scenarios | Findings from EFA-D1 through EFA-D7 | Read-only | queued |

Every discovery report must contain: inspected scope and artifacts; confirmed
severity-ranked findings with evidence; suspected findings labelled as such;
missing evidence; recommended implementation order; exact tests/commands; and
change risks. Findings are advisory until the principal reproduces or validates
them.

## Independent review

| ID | Reviewer | Exclusive scope | Structured deliverable | Dependencies | Editing boundary | Status |
| --- | --- | --- | --- | --- | --- | --- |
| EFA-R1 | Independent financial/attendance reviewer | Frozen attributable diff covering schema, enums, models, Actions, policies, provider/webhook boundary, QR/offline reconciliation, Livewire/Blade/locales, factories/seeds, tests, and docs | Requirement-by-requirement verdict plus severity-ranked reproducible findings; separate security/integrity and code-quality verdicts | Completed implementation and frozen diff package | Review-only; cannot be an implementer | blocked on implementation |

The principal records every review finding below with an accepted, corrected,
or evidence-based rejected disposition. Critical and important in-scope
findings must be corrected and their affected gates rerun before publication.

## Principal checkpoints

| Checkpoint | Acceptance evidence | Status |
| --- | --- | --- |
| Initial tree protection | Branch, HEAD, staged/unstaged/untracked paths, and unrelated ownership recorded | complete |
| Canonical requirements | P19/P21, event, security, privacy, integration, authorization, testing, seeding, deployment, and applicable historical spec read | complete |
| Discovery reconciliation | EFA-D1 through EFA-D8 complete; high-impact claims independently reproduced | pending |
| Canonical plan gate | Stable IDs, dependencies, owner, exact paths, acceptance, verification, state, and rollback saved before production edits | pending |
| Test-first implementation | Each behavior change has an observed relevant failure before production code and a passing focused rerun after it | pending |
| Independent review | Frozen diff reviewed; every finding disposition recorded and valid issues fixed | pending |
| Final verification | Payment, ticket, QR, security, concurrency, full Pest, Pint, Larastan, migration/seed, npm build, browser, docs, diff, and secret gates observed | pending |
| Publication | Temporary-index staged diff is attributable, committed on `main`, and pushed only after required gates permit it | pending |

## Review finding dispositions

No review findings exist before the implementation freeze.
