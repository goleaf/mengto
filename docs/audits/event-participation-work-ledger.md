# Event Participation Work Ledger

Date: 2026-08-30

Scope: P17 registration and P18 capacity/waitlist completion on the canonical
`ForumEvent` aggregate. The repository baseline was clean `main` at
`ae4ac3241f99b05645dcc07316f424dfb877892e`; all place, forum-catalogue,
advanced-event, and other concurrent work visible afterward is external
ownership and must remain unstaged by this delivery.

## Source And Boundary Decision

The controlling sources are `AGENTS.md`, `docs/events/registration.md`,
`docs/events/eligibility.md`, `docs/events/state-machines.md`,
`docs/events/media-and-privacy.md`, `docs/privacy.md`, and P17-P18 of
`docs/plans/portal-events-completion-master-plan.md`, backed by the immutable
registration, eligibility, and capacity source sections. `ForumEvent`, its
occurrences/sessions, canonical users/pets/organizations/places, Laravel
notifications, language files, and the existing class-based Livewire workspace
remain authoritative. P19 payments/tickets and provider delivery remain
unavailable; ticket-type capacity may reference an opaque canonical key but
must not invent payment success or a checkout timer.

The source range labelled `event.eligibility.0104` in the generated matrix is
the capacity no-false-guarantee atom. It is dispositioned at the waitlist
boundary and is not evidence of an additional eligibility requirement.

## Specialist Assignments And Evidence

| Specialist | Exclusive scope | Evidence | Edit ownership |
| --- | --- | --- | --- |
| Registration state machine | Multi-pet authority, eligibility snapshot generations, transitions, stale decisions, cancellation/completion, replay | `/tmp/evp-registration-state-report.md` | Read-only; principal owns edits |
| Capacity/concurrency/waitlists | Typed pools, allocations, holds/offers, ordering, lock order, final-slot and promotion races | `/tmp/evp-capacity-concurrency-report.md` | Read-only; principal owns edits |
| Authorization/notification/UI | Role and field matrix, after-commit notification intents, Livewire states, localization, accessibility, factories/seeds/browser | `/tmp/evp-auth-notify-ui-report.md` | Read-only; principal owns edits |
| Independent final reviewer | Frozen attributable implementation diff | Assigned only after implementation and gates are ready | Review-only; principal dispositions and fixes |

## Reproduced Findings And Dispositions

| ID | Severity | Reproduced finding | Disposition and acceptance evidence |
| --- | --- | --- | --- |
| EVP-F01 | critical | Registration replay returns an existing row without binding actor/scope/payload, while renewed submission overwrites the one accepted snapshot. | Add a scoped operation ledger with canonical request checksum/result and immutable registration snapshot generations. Same command returns the exact result; changed payload conflicts; a renewed lifecycle appends evidence. |
| EVP-F02 | critical | Pet authority and eligibility are checked before the write lock, occurrence age uses the parent event time, and boolean approval manufactures confirmed pet eligibility. | Reload current active managed pets and relevant manager authority inside the transaction; evaluate at occurrence time; store an immutable per-pet decision generation; organizer review cannot overwrite evidence with a bulk status update. |
| EVP-F03 | critical | Capacity is inferred from registration counts and cannot acquire event, occurrence, session, resource, animal/species, or ticket-type constraints as one unit. | Make typed capacity pool/allocation rows the admission authority. Sort and lock every required pool; create all allocations or none; retain compatibility scalar fields only as bounded inputs/backfill sources. |
| EVP-F04 | critical | Sequential `max(waitlist_position)+1` conflicts with the event-global unique index and immediate promotion has no backed offer, expiry, or revalidation. | Store one active deterministic entry per registration with server request time and stable ID tie-breaker. Waitlist promotion creates one configured short-lived capacity hold/offer, atomically revalidates, and confirms only on acceptance. Direct free admission creates no hold. |
| EVP-F05 | critical | `manageRegistrations` combines queue review, check-in, and emergency roles and sends raw contact, consent, pet, and eligibility data to every such Livewire client. | Split queue/review/remove/promote/check-in/emergency/media capabilities. All presenter outputs use explicit role allowlists; ordinary queues omit raw contact and private eligibility/medical evidence; Actions reauthorize locked current rows. |
| EVP-F06 | critical | Notifications are written after the transition transaction without a durable intent, and status-only keys suppress legitimate later transitions. | Create recipient-scoped transition-versioned notification intents inside the same transaction and deliver after commit. Replay and listener retry create no duplicate; rollback creates no notification. |
| EVP-F07 | important | Whole-event cancellation and reschedule bypass per-registration transition versions, capacity release, stale acceptance/eligibility, history, and recipient notification. | Integrate cancellation and occurrence movement with the participation re-evaluator. Preserve terminal attendance, transition other active rows explicitly, release allocations once, append history, mark snapshots/decisions stale, and notify after commit. |
| EVP-F08 | important | Optimistic versions are incremented but never compared, and organizer identity can remain authoritative after team/organization removal. | Every external mutation carries an expected registration/pool version; zero-row compare-and-update is stale. Policy checks use current active team and organization authority, never historical attribution alone, and repeat inside the lock. |
| EVP-F09 | important | Existing UI hides expired invitations, chooses one registration with unordered `first()`, derives actions from status in Blade, and silently caps the manager queue at 500. | Return keyed occurrence registrations and policy-prepared action descriptors, render every requested localized state and non-guarantee text, and use bounded server pagination. Blade remains passive with complete error/loading/offline/focus behavior. |

## Canonical Lock And State Contracts

All participation writes use this lock order: operation, event, occurrences in
ascending ID, waitlists in ascending ID, capacity pools in ascending ID,
registrations in ascending ID, then entries, holds/offers, and allocations.
Audit transitions and notification intents are appended last. SQLite uses the
configured immediate transaction mode; databases with row locks use the same
ordering. No code path may lock a registration and then seek a pool.

New participant withdrawal writes `withdrawn`; organizer removal/event
cancellation writes `cancelled_by_organizer`; rejection writes `rejected`.
Legacy `cancelled` and `declined` remain readable. `confirmed`, partial/check-in
states, and completion use allocation rows rather than status-derived counts.
Waitlisted entries consume no capacity. A configured waitlist offer uses a
live expiring hold. Terminal states release current holds/allocations exactly
once and preserve historical rows. Payment states remain unreachable until
P19.

The implemented graph must cover invited at the invitation boundary and
registration states pending, confirmed, waitlisted, rejected,
cancelled-by-organizer, withdrawn, expired, checked-in, attended/completed, and
their applicable existing intermediate states. Every transition validates the
source state, actor capability, current event/occurrence, current acceptance and
eligibility checksums, expected version, capacity effect, immutable history,
and operation replay in one transaction.

## Verification Ledger

| Gate | Required evidence | Current status |
| --- | --- | --- |
| Existing event baseline | Focused lifecycle/workflow/schedule/migration tests | Passed before implementation: 33 tests, 742 assertions |
| Red contracts | Schema/constraint, state, replay, privacy, notification, migration, two-process final-slot/promotion tests | In progress |
| Focused green | Event participation feature/unit/concurrency suites | Pending |
| Repository gates | Syntax, Pint, Larastan, full serial Pest, migration/seed repeat, Composer/npm audits, Vite/cache smokes | Pending |
| Browser | Authenticated participant/manager/check-in journeys at mobile/tablet/desktop, keyboard/focus/overflow/console/privacy | Pending |
| Generated evidence | Forum source preservation and requirement generator checks | Pending |
| Independent review | Frozen diff, reproduced findings, dispositions, affected reruns | Pending |
| Publication | Temporary-index staged diff, commit on `main`, push fast-forward | Pending |

No requirement atom moves to implemented or verified until its exact behavior
and recorded gate pass. Rollback before production use removes only additive
participation objects; after persisted use, disable entry points and forward-fix
while retaining registration, decision, allocation, waitlist, operation,
transition, and notification history.
