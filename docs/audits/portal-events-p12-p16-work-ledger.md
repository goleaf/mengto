# Portal Events P12-P16 Work Ledger

Ledger date: 2026-08-30

Status: approved discovery and implementation coordination boundary.

## Scope And Authority

This ledger covers P12 through P16 of
`docs/plans/portal-events-completion-master-plan.md`. `ForumEvent` remains the
only canonical event aggregate. Specialists are read-only; the principal owns
all cross-module decisions, tests, production edits, documentation, Git
integration, and finding dispositions. Reviewers must be independent of every
implementation edit.

The task began on `main`, with `main...origin/main [ahead 2]`, in a materially
dirty shared tree. Pre-existing Auth, Places, canonical-document, language,
seeder, test, and browser-runner changes are user-owned. Concurrent work has
also added independent Events P17/P18 and multiple Places delivery sections to
`docs/implementation-plan.md`. Those hunks and every unrelated path remain
outside this package. Publication, if authorized by passing gates, uses a
temporary `GIT_INDEX_FILE` and an exact attributable staged diff.

## Specialist Assignments

| ID | Specialist | Exclusive read-only scope | Required structured deliverable | Status |
| --- | --- | --- | --- | --- |
| PE-S01 | Event-domain modeling | `ForumEvent`, event types/configuration, builder drafts/templates, versions/history, series/occurrences and their current tests/docs | Current invariant map; proposed focused entities/value objects; type-configuration contract; safe template-copy field matrix; material-change/version rules; exact files/tests/risks | complete; `/tmp/pawcircle-p12-p16-specialists/pe-s01-event-domain.md` |
| PE-S02 | Builder workflows | Current `ForumEventDirectory`/`ForumEventWorkspace`, forms, views, routes, Actions, presenters, accessibility/localization conventions | Explicit wizard steps; public-state contract; draft save/resume/idempotency/concurrency flow; readiness/preview/template UX; EN/LT/RU and browser/test matrix | complete; `/tmp/pawcircle-p12-p16-specialists/pe-s02-builder.md` |
| PE-S03 | Recurrence | Series/occurrence schema/models/enums/factories, recurrence source requirements, stable-key and edit semantics | Validated rule schema; deterministic generation algorithm; stable canonical key; addition/skip/one/future edit rules; cancel/postpone/reschedule/move propagation; boundary tests | complete; `/tmp/pawcircle-p12-p16-specialists/pe-s03-recurrence.md` |
| PE-S04 | Timezone and DST | Existing event/occurrence/session timestamp storage and formatting, Carbon/Intl use, relevant source requirements | UTC/IANA/wall-time storage contract; DST gap/fold policy; all-day/overnight/multi-day rules; monthly/weekday boundary cases; exact red tests and migration implications | queued |
| PE-S05 | Tracks and sessions | Track/room/session/staff models, schedule Action, capacity/order/conflict/presentation/tests | Reuse/gap map for occurrence scope, speaker model, capacity, deterministic ordering and public/private projection; minimal changes and regression tests | queued |
| PE-S06 | Eligibility | Pet/participant/event requirement source, pet authority, vaccination/document/membership/accessibility/role boundaries, registration snapshots | Typed requirement/evidence/decision/exception schema; minimal-data matrix; evaluator and re-evaluation rules; registration snapshot contract; privacy and invalid-state tests | queued |
| PE-S07 | Authorization | Event/registration/place/group/organization/pet Policies, query scoping, notification audiences, protected projections | Role/capability matrix including wrong account, former/removed team, private/group/org visibility, direct Actions, template/version/route/evidence access; privacy threat cases | queued |
| PE-S08 | Migrations and concurrency | Existing event migrations/indexes/constraints, SQLite portability, optimistic/row locking, idempotency and after-commit patterns | Additive schema/index/FK/unique plan; lock ordering; populated rollback/forward-fix; concurrent organizer and occurrence generation tests; notification commit boundary | queued |
| PE-R01 | Final domain reviewer | Frozen attributable diff after focused checks | Severity-ranked P12-P16 completeness/correctness findings with exact reproduction and requirement mapping | blocked on freeze |
| PE-R02 | Final security/data reviewer | Frozen attributable diff after focused checks | Severity-ranked authorization, privacy, integrity, migration, concurrency, idempotency, and after-commit findings | blocked on freeze |
| PE-R03 | Final UX/test reviewer | Frozen attributable diff and built application | Severity-ranked builder, localization, accessibility, browser, factory/seeding, and regression coverage findings | blocked on freeze |

## Structured Report Contract

Every discovery report must include:

1. files and exact symbols inspected;
2. current behavior and evidence, separated from planned behavior;
3. canonical invariants and conflicts;
4. proposed minimal interfaces/schema without repository edits;
5. security/privacy/data-integrity failure cases;
6. exact failing tests to write first;
7. migration/rollback or forward-fix notes where applicable;
8. risks, dependencies, and explicit non-goals.

## Principal Disposition Ledger

| Finding ID | Specialist | Finding | Reproduced evidence | Disposition | Implementation/test owner | Status |
| --- | --- | --- | --- | --- | --- | --- |
| PE-F001 | PE-S01 | All current enum cases are creatable, type behavior is fragmented, and no validated versioned configuration rejects unsafe custom capabilities. | `ForumEventType`, `ForumEventForm`, `CreateForumEvent`, factory and seeder inspection | accepted; one canonical registry will expose creatable definitions plus explicit read-only legacy mappings and validate persisted configuration | Principal; registry/configuration tests | open |
| PE-F002 | PE-S01 | Templates cannot safely clone an event; ownership, participants, private links, stale evidence, operational identities, and lifecycle state must never cross the copy boundary. | No template aggregate exists; current protected fields and relations inventoried | accepted; immutable template versions and a reconstruction allowlist will create a new private draft/checkpoint and reauthorize destination context | Principal; template privacy tests | open |
| PE-F003 | PE-S01 | Event timestamps and occurrence timestamps are currently dual authorities, and aggregate rescheduling leaves the initialized occurrence stale. | `InitializeForumEventLifecycle` and `RescheduleForumEvent` inspection | accepted; occurrence becomes actual schedule truth and event dates remain a transactionally maintained primary-occurrence projection | Principal; recurrence/reschedule regression tests | open |
| PE-F004 | PE-S01 | Initial lifecycle versions exist, but normal material edits do not append immutable versions or perform optimistic concurrency. | `ForumEventVersion`, lifecycle initializer, transition/reschedule Actions | accepted; material saves compare expected lock versions, append checksummed versions once, and classify participant impact | Principal; concurrency/version tests | open |
| PE-F005 | PE-S02 | The directory's all-at-once form creates a scheduled event and cannot represent partial, resumable, server-authoritative authoring. | Directory component/form and `CreateForumEvent` inspection | accepted; a separate class-based builder will persist bounded private checkpoints and materialize at most one canonical draft event | Principal; builder/Livewire tests | open |
| PE-F006 | PE-S02 | Readiness, safe audience preview, stale-conflict recovery, template application, approval, and publication require explicit policy/Action boundaries. | No corresponding routes, Policies, Actions, presenters, or tests exist | accepted in the user-requested P12-P16 subset; publication fails closed and preview is an allowlisted projection, never an authorization shortcut | Principal; policy/readiness/projection tests | open |
| PE-F007 | PE-S03 | Current series fields do not form a complete validated recurrence rule; fixed/monthly/custom modes cannot be generated deterministically. | Series enum/model/factory/seeder and recurrence docs inspected | accepted; a versioned normalized rule with closed frequency-specific shapes and a bounded pure generator will replace direct attribute interpretation | Principal; rule/generator tests | open |
| PE-F008 | PE-S03 | Stable occurrence identity must derive from the immutable source slot, not mutable time or display ordinal; skips and additions must remain durable. | Existing singleton, demo-numbered, and factory-ULID key schemes compared | accepted; preserve all legacy public keys, add a canonical source-slot digest, materialize skip tombstones, and key additions by immutable operation identity | Principal; identity/idempotency tests | open |
| PE-F009 | PE-S03 | One-occurrence and this-and-future edits need explicit scope, field-level override masks, independently versioned truth, and immutable source-slot cutoffs. | No scoped edit Actions, occurrence versions, or override masks exist | accepted; future propagation changes only inheritable fields and never rewrites started/past instances or accepted snapshots | Principal; one/future edit tests | open |
| PE-F010 | PE-S03 | Aggregate status/reschedule/cancel Actions can leave event, occurrence, registration, and session truth inconsistent. | Direct inspection of the three Actions and workspace occurrence selection | accepted; reconcile them through focused occurrence lifecycle operations and filter unavailable occurrence selection | Principal; lifecycle/material-change tests | open |

## Preservation Boundary

- Do not reset, discard, rewrite, reformat, or stage unrelated changes.
- Do not edit historical migrations.
- Do not create a parallel event, place, pet, organization, document,
  notification, localization, or eligibility authority.
- Do not treat P17/P18 participation/capacity work as owned by this package;
  coordinate through current shared interfaces and avoid overlapping edits.
- Do not promote generated `event.*` evidence until its specific production
  behavior and required checks have passed.
- Do not claim provider payments, tickets, QR/offline check-in, competitions,
  vendors, volunteers, incidents, or other P19+ domains.

## Final Review Protocol

After implementation and focused checks, the principal freezes one
attributable diff package. Reviewers receive the canonical P12-P16 contract,
this ledger, observed test reports, and the frozen diff. Every material
finding is reproduced and recorded here. Valid findings are fixed, affected
checks rerun, and the corrected diff is independently re-reviewed before the
full repository gates and publication decision.
