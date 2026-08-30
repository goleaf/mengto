# Authorization Work Ledger

Date: 2026-08-30
Principal owner: `/root`
Canonical execution plan: `docs/implementation-plan.md`

This ledger coordinates the read-only discovery, principal-owned integration,
independent review, and delivery work for policies, roles, permissions,
ownership, privacy, resource state, and tenant isolation. It does not replace
the canonical execution plan.

## Working-tree protection baseline

- Initial branch: `main`, tracking `origin/main`, three local commits ahead.
- Remote: `origin` uses `git@github.com:goleaf/mengto.git` for fetch and push.
- Initial tree: a large mixed staged, unstaged, and untracked slice from other
  active work was present before AUTH09 began.
- Protection rule: AUTH09 edits are principal-owned, inspected path by path,
  and staged through a temporary `GIT_INDEX_FILE`. Existing changes will not be
  reset, cleaned, stashed, overwritten, rewritten, or claimed by AUTH09.

## Discovery roles

| ID | Subagent | Exclusive read-only scope | Expected structured output | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| AUTH09-D1 | Role and Capability Matrix Analyst | Role/permission tables, enums, middleware, policies, UI, seeders, admin features, and requirement documents | Canonical role-capability matrix; conflicts and cleanup; policy/gate coverage; required tests | Current instructions and repository | Assigned wave 1 |
| AUTH09-D2 | Policy Coverage Analyst | Policies, gates, controllers, Livewire, API, commands, downloads, imports/exports, and admin actions | Public-action policy table; missing/incorrect methods; role/state test requirements | Current instructions and repository | Assigned wave 1 |
| AUTH09-D3 | Livewire Authorization Analyst | Livewire components, form objects, listeners, uploads/downloads, sort/reorder methods, and tests | Component-action authorization map; bypass scenarios; exact code/test needs | Current instructions and repository | Assigned wave 1 |
| AUTH09-D4 | Route, Controller, API, and Command Authorization Analyst | Web/API routes, controllers, middleware, bindings, console commands, schedules, maintenance endpoints, responses | Entry-point authorization matrix; missing controls; binding/scoping fixes and tests | Wave 1 evidence helpful, not required | Queued wave 2 |
| AUTH09-D5 | Tenant Isolation and Data-Scoping Analyst | Tenant models/queries/scopes, bindings, caches, files, jobs, notifications, exports/imports, seeds | Tenant-boundary map; leakage findings; query/policy/cache/file/test changes | Wave 1 evidence helpful, not required | Queued wave 2 |
| AUTH09-D6 | Ownership, Privacy, and State Authorization Analyst | Ownership, memberships, invites, relationships, privacy, lifecycle, moderation, policies | State-aware truth tables; domain guards/policies; positive and negative tests | Wave 1 evidence helpful, not required | Queued wave 2 |
| AUTH09-D7 | Authorization Test Designer | Policies, gates, routes, Livewire, API, files, factories, seeders, and existing tests | Complete authorization test matrix; factory states; exact assertions/commands | AUTH09-D1 through D6 reports | Queued wave 3 |

Every discovery report must contain: inspected scope; exact artifacts; confirmed
severity-ranked findings with evidence; suspected findings; missing evidence;
implementation order; tests and verification commands; and change risks. All
agents are read-only. The principal agent validates material claims, resolves
conflicts, owns every architectural decision, and performs all edits.

## Principal integration checkpoints

| ID | Acceptance evidence | Status |
| --- | --- | --- |
| AUTH09-CP1 | Instructions, canonical documents, current Git state, and framework metadata inspected | In progress |
| AUTH09-CP2 | Seven discovery reports reconciled and material findings reproduced | Pending |
| AUTH09-CP3 | Dedicated stable-ID section saved in `docs/implementation-plan.md` before production edits | Pending |
| AUTH09-CP4 | Each behavior change follows observed RED, minimal GREEN, and targeted regression verification | Pending |
| AUTH09-CP5 | Frozen task-owned diff reviewed by all three independent reviewers; every finding disposition recorded | Pending |
| AUTH09-CP6 | Applicable final gates observed; documentation synchronized; isolated commit and safe push attempted | Pending |

## Independent review roles

| ID | Reviewer | Exclusive read-only scope | Expected output | Dependency | Status |
| --- | --- | --- | --- | --- | --- |
| AUTH09-R1 | Authorization Bypass Reviewer | Frozen routes, Livewire, API, commands, files, exports/imports, policies, tests, and adjacent mutation paths | Severity-ranked bypasses with exact location, scenario, evidence, correction, and regression test | Implementation frozen | Pending |
| AUTH09-R2 | Tenant Leakage Reviewer | Frozen tenant-scoped queries, bindings, caches, files, jobs, notifications, exports, and tests | Cross-tenant leakage findings and two-tenant reproductions with exact fixes/tests | Implementation frozen | Pending |
| AUTH09-R3 | Policy and Authorization Test Reviewer | Frozen policies, gates, tests, factories, seeders, matrix, compliance evidence, and requirement IDs | Coverage gaps, test corrections, and final readiness verdict | Implementation frozen | Pending |

Reviewers must be independent from discovery/implementation work, inspect
behavior rather than style alone, and remain read-only until their reports are
complete. Each finding requires severity, exact location, concrete failure
scenario, evidence, and a proposed correction or test. The principal records
accepted, fixed, or evidence-based rejected dispositions here before delivery.

## Finding dispositions

No findings have been dispositioned yet.

