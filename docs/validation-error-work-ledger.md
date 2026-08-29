# Validation And Error Work Ledger

This ledger coordinates the read-only discovery and independent review required
for the repository-wide validation and error-contract package. The canonical
execution plan remains `docs/implementation-plan.md`.

## Discovery Analysts

| ID | Analyst | Exclusive scope | Expected output | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| VAL-A01 | HTTP Request Validation Analyst | Routes, controllers, Form Requests, middleware, API, uploads, imports, and related tests | Endpoint-validation matrix, unsafe payload findings, and Form Request candidates | Governing requirements | complete |
| VAL-A02 | Livewire Form and Action Validation Analyst | Livewire components, form objects, public state, action parameters, uploads, and component tests | Action-validation map, refactor candidates, and direct-call test gaps | Governing requirements | complete |
| VAL-A03 | Domain Rule and State-Transition Analyst | Requirements, Actions, models, enums, policies, transactions, constraints, and transition tests | Invariant catalogue, reusable-rule candidates, database backstops, and ordering | Governing requirements | complete |
| VAL-A04 | Input Normalization and Canonicalization Analyst | Identifier, email, phone, Unicode, locale, money, URL, date, file-name, and import normalization | Normalization policy, collision risks, and reusable candidates | VAL-A01 and VAL-A02 inventories | in progress |
| VAL-A05 | Typed Data Contract Analyst | Request/action/service/job/event/integration/presentation payload boundaries | Contract modernization map with justified and rejected DTO/value-object candidates | VAL-A01 through VAL-A03 inventories | in progress |
| VAL-A06 | Error Contract and Exception Analyst | Exception configuration, HTTP/API/Livewire errors, logs, localization, external failures, and tests | Error taxonomy, disclosure findings, mappings, and localized test requirements | Login reproduction evidence | in progress |
| VAL-A07 | Validation and Error Test Analyst | Existing validation, boundary, malicious, locale, tenant, file, constraint, and browser tests | Test matrix, fixture needs, exact negative assertions, and commands | VAL-A01 through VAL-A06 findings | pending |

All analysts are read-only during discovery. Each report must identify inspected
scope, exact evidence, severity, uncertainty, missing coverage, implementation
order, tests, commands, and change risk. The principal agent owns reconciliation
and implementation.

## Independent Reviewers

| ID | Reviewer | Scope | Dependency | Status |
| --- | --- | --- | --- | --- |
| VAL-R01 | Validation Completeness Reviewer | Changed and adjacent input paths, state transitions, nested data, files, constraints, and negative tests | Implementation frozen for review | pending |
| VAL-R02 | Typed Contract and Mass-Assignment Reviewer | End-to-end field maps, normalization order, DTO/value-object use, and privileged writes | Implementation frozen for review | pending |
| VAL-R03 | Error Disclosure and Behavior Reviewer | Web, Livewire, API, exception, logging, localization, and operator diagnostics | Implementation frozen for review | pending |

Review findings and principal-agent dispositions will be recorded here and in
`docs/code-review.md` before publication.
