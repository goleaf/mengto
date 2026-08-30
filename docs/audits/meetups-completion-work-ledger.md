# Meetups Completion Work Ledger

Status: implementation complete; final gates and independent review in progress
on 2026-08-30.

This ledger coordinates the production completion of `/meetups` as the
meetup-focused presentation of PawCircle's canonical `ForumEvent` aggregate.
It does not authorize a parallel Meetup/Event, pet-authority, relationship,
notification, discussion, place, or moderation system. The principal agent
owns every repository write and cross-module decision. All named analysts and
reviewers are read-only.

## Protected baseline

- Branch: `main`.
- Starting `HEAD`: `a1fa4668f7636eec2db532f2d4a5fc7a130ec4da`.
- Starting `origin/main`: `a1fa4668f7636eec2db532f2d4a5fc7a130ec4da`.
- Starting staged paths: none.
- Starting untracked paths: none.
- Pre-existing unstaged path:
  `tests/Feature/Onboarding/OnboardingWizardTest.php`.
- The pre-existing onboarding hunk remains user-owned and is excluded from
  every Meetup edit, formatter invocation, staged diff, commit, and push.
- Publication will use a temporary `GIT_INDEX_FILE` containing only
  attributable Meetup paths and hunks.

### Concurrent baseline change

While the read-only audit was running, a separate onboarding delivery moved
both local `main` and `origin/main` to
`7c96e504a5bfc9d8e32259971b25157bcb67fa3f`. That commit included this ledger
alongside its onboarding-owned paths. The principal did not create or amend
that commit and treats the synchronized commit as the new immutable base. The
Meetup delivery will not revert, amend, restage, or otherwise absorb any of its
onboarding changes.

## Discovery analysts

Each analyst must report exact files and symbols, current behavior, reusable
authority, gaps, severity-ranked risks, proposed acceptance tests, and any
uncertainty. Analysts may run read-only searches and tests but must not modify
files, caches, databases, Git state, generated output, or dependencies.

| ID | Analyst | Exclusive scope | Required structured deliverable | Status |
| --- | --- | --- | --- | --- |
| MEET-A | Existing Meetups/Events Auditor | `/meetups`, event routes, `ForumEvent` models, migrations, Actions, Policies, Livewire, views, tests, factories, seeders, and docs | Canonical reuse decision; current route-to-domain map; implemented/gap inventory; duplication hazards | completed; report disposition pending |
| MEET-B | Domain/Data Model Reviewer | Event, occurrence, registration, participation, waitlist, invitation, update, history, cancellation, and derived lifecycle state | Proposed aggregate/relationship/state-transition map; schema invariants; additive migration needs; invalid-state risks | completed; report disposition pending |
| MEET-C | Pet Domain Reviewer | `PetProfile`, ownership, active managers, access requests/invitations, revocation/expiry/suspension, species, privacy, and media | Canonical pet-authority predicate; participation-pet projection; private-pet disclosure rules; regression cases | completed; report disposition pending |
| MEET-D | Location/Privacy Reviewer | `Place`, venue, exact-location grant/audit/version, event public region, address/coordinates, metadata and Livewire serialization | Field-by-audience disclosure matrix; exact-location authorization rule; leak paths and tests | completed; report dispositioned |
| MEET-E | RSVP/Capacity Reviewer | Registration/participation Actions, locks, constraints, idempotency, capacity, requests, waitlist ordering and promotion | Transaction and lock-order design; replay/stale/concurrency cases; capacity/waitlist acceptance tests | completed; report dispositioned |
| MEET-F | Organizer Workflow Reviewer | Organizer authority, SocialActor/organization/group capability, create/draft/publish/edit/manage/update/cancel/history flows | Organizer authority map; lifecycle validation differences; material-change and management gaps | completed; report dispositioned |
| MEET-G | Security Reviewer | IDOR, organizer/pet/participant forgery, visibility/join-policy bypass, invite tokens, exact-location leaks, block/account status, XSS and mass assignment | Threat inventory with assets/trust boundaries/abuse paths/mitigations and adversarial regression cases | completed; report dispositioned |
| MEET-H | Moderation/Safety Reviewer | Canonical blocks, reports, removals, suspended users, organizer safety rules, incidents and discussion boundaries | Reuse map; safe policy for blocked/removed/suspended actors; moderation/reporting gaps and tests | completed; report dispositioned |
| MEET-I | Mobile UX Reviewer | Discover/detail/create/edit/manage/invite journeys at 320-430 px, loading/offline/stale/empty states and touch interactions | Page/journey/state matrix; prioritized mobile defects; browser assertions | completed; report dispositioned |
| MEET-J | Accessibility Reviewer | WCAG 2.2 AA engineering practices for headings, forms, choices, errors, announcements, focus, keyboard, zoom, forced colors and motion | Semantic/interaction matrix; failure scenarios; automated and browser acceptance checks | completed; report dispositioned |
| MEET-K | Localization Reviewer | EN/LT/RU Meetup/Event keys, enums, validation, notifications, date/time/timezone formatting and long copy | Key/placeholder parity and terminology report; formatting gaps; representative locale tests | completed; report dispositioned |
| MEET-L | Performance Reviewer | Discovery, detail, organizer management, counts, pet summaries, pagination, query scopes and indexes | Current query map; N+1/unbounded risks; justified query ceilings/indexes and performance tests | completed; report dispositioned |
| MEET-M | Test Reviewer | Existing event/meetup/pet/place/social/auth/security/localization/a11y/performance/factory/seed/browser coverage | Risk-based regression matrix; overmocking/false-positive gaps; exact focused and full commands | completed; report dispositioned |
| MEET-N | Notifications And Invitations Reviewer | Account-bound invitations, recipient-locale notifications, intent/deduplication/deep links, material updates and cancellation | Reachable acceptance flow; recipient/action matrix; transition-versioned notification tests | completed; report dispositioned |
| MEET-O | Requirements Traceability Reviewer | User contract against canonical Event P12-P18 requirements, docs, generators and compliance evidence | Conflict/source map and exact evidence-update obligations | completed; report dispositioned |

## Principal Architecture Decisions

- `/meetups` remains a specialized, mobile-first route and presentation layer
  over the one canonical `ForumEvent` aggregate. No `Meetup`, meetup attendee,
  pet authority, follower/block, Place, invitation, notification, discussion,
  or moderation duplicate will be introduced.
- The Event owner/organizer User remains the authoritative host. Authorized
  organization and group context continues through existing foreign keys,
  memberships, policies, and team roles; a browser never selects an arbitrary
  organizer identity.
- Meetup-facing lifecycle is intentionally simple: private `Draft`, explicit
  publication, `Cancelled`, and time-derived upcoming/ongoing/past state. The
  richer general Event enum remains backward compatible; fullness is derived
  from capacity allocations rather than persisted solely for Meetup cards.
- Visibility and registration policy remain separate. Public, followers,
  member, organization, group, invitation and private access reuse canonical
  social authorities; open, approval and invitation admission remain distinct.
- Payload-bound P17 operations, the active-scope invariant, accepted snapshots,
  transition rows, and the locked `ForumEventRegistrationService` are the sole
  Meetup RSVP mutation path. The generalized capacity-pool/hold/allocation,
  typed-waitlist, and notification-intent tables remain wider Event
  infrastructure and are not falsely described as the active Meetup allocator.
- Human capacity is `1 + guest_count`; pet participation never consumes the
  human pool unless a future explicit typed pet pool is configured. Waitlist
  order is server-owned priority, requested time, then identifier.
- Attending-pet authority requires an active User, active `PetProfile`, and a
  current manager/owner relationship for which `PetProfileAccess` permits
  `View`, rechecked inside every register/approve/promote transaction. Requiring
  `ManageSocial` merely to attend is rejected because it would exclude valid
  caregiver and sitter participation; publishing as the pet actor still
  requires that stronger permission.
- Public discovery receives only an approximate location projection. Legacy
  exact access remains encrypted and current-confirmed-state gated. Canonical
  Place exact access is explicit, event/purpose/grant bound, audited, and never
  placed in an unauthorized component snapshot, hidden field, metadata, or
  notification.
- Canonical account blocks stop discovery, direct participation, invitations,
  participant communication, identity projection, and protected location
  access between organizer and participant. Historical rows are preserved.
- Account-bound invitation records remain preferable to bearer invite tokens.
  Pending recipients receive only a safe acceptance projection; acceptance
  rechecks identity, block, event, eligibility, and capacity.
- No scheduled status job, reminder-only cron, continuous GPS, mandatory
  browser geolocation, new map provider, medical-document collection, or new
  real-time/chat infrastructure is added.

## Reproduced Findings And Dispositions

| ID | Severity | Reproduced current behavior | Disposition / required evidence |
| --- | --- | --- | --- |
| MEET-F01 | critical | Organization membership branch projected private/invitation cards. | Fixed in deny-first visibility scope; focused projection tests pass. |
| MEET-F02 | critical | Pending private invitee could not open the invitation-response UI. | Fixed with a safe recipient-only preview; exact/participant data remains absent. |
| MEET-F03 | critical | Active uniqueness and replay authority were unwritten. | Fixed with active scope, payload-bound operations, transitions and a passing two-process final-place race. Generalized pool tables remain unused and documented honestly. |
| MEET-F04 | critical | Create wrote discoverable `Scheduled` and had no draft/publish/edit flow. | Fixed with dedicated create/edit/manage routes, incomplete private drafts, explicit publish validation and occurrence synchronization. |
| MEET-F05 | high | Changed replay was accepted and terminal rejoin overwrote history. | Fixed: changed checksum conflicts; terminal rejoin creates a new active generation. |
| MEET-F06 | high | Pet authority was stale and approval manufactured eligibility. | Fixed through transaction-time `PetProfileAccess::View` and manager-state revalidation. |
| MEET-F07 | high | Blocks were absent from Meetup boundaries. | Fixed in query/policy/register/message/reveal paths; historical attendance is retained but inaccessible. |
| MEET-F08 | high | Place reveal was unreachable and participation selection could be stale. | Fixed with latest registration selection and explicit audited grant-bound Place reveal. |
| MEET-F09 | high | Cancellation/reschedule left occurrence and notification state split. | Fixed for base occurrences, active registrations, safe updates, history and post-commit notifications. |
| MEET-F10 | high | Management loaded 500 mixed registrations. | Fixed with paginated registration cards and bounded invitation projection. |
| MEET-F11 | high | Prototype RSVP was a second mutation path. | Canonical Meetup routes/actions are authoritative; final diff review must confirm no task-owned fake mutation remains. |
| MEET-F12 | important | Notification placeholders/locale and material lifecycle notices were incomplete. | Fixed using recipient locale and deduplicated existing `ForumNotification`; generalized intent tables are not claimed. |
| MEET-F13 | important | Create/manage were dense and action state incomplete. | Route split and responsive card/form controls implemented; real browser review pending. |
| MEET-F14 | important | Raw species/internal copy and new locale keys risked drift. | Localized species/labels added; recursive EN/LT/RU parity passes. |
| MEET-F15 | important | Management was unbounded and query growth unmeasured. | Registration pagination and constant-growth detail/manage query regression pass. |
| MEET-F16 | important | Hard boundaries lacked real concurrency/security evidence. | Dedicated focused suite and real two-process race pass; browser gate pending. |

## Independent final reviewers

Final reviewers receive a frozen attributable diff plus the observed command
ledger. They remain independent from implementation, reproduce every material
finding, and do not edit files. The principal records each disposition, adds a
failing regression before fixing a confirmed behavior defect, and reruns the
affected checks.

| ID | Reviewer | Review boundary | Status |
| --- | --- | --- | --- |
| MEET-R1 | Laravel Architecture | Routes, controllers, Livewire, Actions, Policies, Models, Blade and migrations | pending implementation freeze |
| MEET-R2 | Meetup Domain | Complete organizer, lifecycle, participation, capacity, waitlist, invitation, update and history state machines | pending implementation freeze |
| MEET-R3 | Pet Domain | Pet authority, manager-state handling, species rules, media and private-pet disclosure | pending implementation freeze |
| MEET-R4 | Location Privacy | Exact/approximate location through queries, HTML, Livewire, metadata, notifications and removed states | pending implementation freeze |
| MEET-R5 | Security | IDOR, forgery, invite replay/scope, races, block/account status, XSS and mass assignment | pending implementation freeze |
| MEET-R6 | UX/Mobile | Attendee and organizer journeys, responsive states, stale/offline behavior and touch operation | pending implementation freeze |
| MEET-R7 | Accessibility | Semantics, keyboard, focus, announcements, zoom, contrast, forced colors and reduced motion | pending implementation freeze |
| MEET-R8 | Localization | EN/LT/RU key/placeholder parity, terminology, validation, notifications and timezone display | pending implementation freeze |
| MEET-R9 | Performance | Query bounds, pagination, aggregates, payload size, eager loading and indexes | pending implementation freeze |
| MEET-R10 | Tests | Behavioral value, negative paths, real database/domain usage, race coverage and gate completeness | pending implementation freeze |

## Finding disposition rules

- `confirmed`: reproduced in the current checkout with exact evidence; a
  behavior fix requires an observed failing test first.
- `already satisfied`: direct current implementation and a relevant passing
  test prove the requested behavior.
- `not applicable`: the canonical architecture makes the requested mechanism
  unnecessary, with the replacement behavior and evidence named.
- `deferred`: permitted only for a genuine external dependency or explicitly
  excluded product capability; it cannot be described as complete.
- `rejected`: evidence shows the suggestion would duplicate an authority,
  weaken privacy/security, or conflict with a higher-priority requirement.

No completion or publication claim may rely solely on this ledger, historical
test counts, file presence, or an analyst's report.
