# Meetups Completion Work Ledger

Status: discovery in progress on 2026-08-30.

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

## Discovery analysts

Each analyst must report exact files and symbols, current behavior, reusable
authority, gaps, severity-ranked risks, proposed acceptance tests, and any
uncertainty. Analysts may run read-only searches and tests but must not modify
files, caches, databases, Git state, generated output, or dependencies.

| ID | Analyst | Exclusive scope | Required structured deliverable | Status |
| --- | --- | --- | --- | --- |
| MEET-A | Existing Meetups/Events Auditor | `/meetups`, event routes, `ForumEvent` models, migrations, Actions, Policies, Livewire, views, tests, factories, seeders, and docs | Canonical reuse decision; current route-to-domain map; implemented/gap inventory; duplication hazards | assigned wave 1 |
| MEET-B | Domain/Data Model Reviewer | Event, occurrence, registration, participation, waitlist, invitation, update, history, cancellation, and derived lifecycle state | Proposed aggregate/relationship/state-transition map; schema invariants; additive migration needs; invalid-state risks | assigned wave 1 |
| MEET-C | Pet Domain Reviewer | `PetProfile`, ownership, active managers, access requests/invitations, revocation/expiry/suspension, species, privacy, and media | Canonical pet-authority predicate; participation-pet projection; private-pet disclosure rules; regression cases | assigned wave 1 |
| MEET-D | Location/Privacy Reviewer | `Place`, venue, exact-location grant/audit/version, event public region, address/coordinates, metadata and Livewire serialization | Field-by-audience disclosure matrix; exact-location authorization rule; leak paths and tests | pending wave 2 |
| MEET-E | RSVP/Capacity Reviewer | Registration/participation Actions, locks, constraints, idempotency, capacity, requests, waitlist ordering and promotion | Transaction and lock-order design; replay/stale/concurrency cases; capacity/waitlist acceptance tests | pending wave 2 |
| MEET-F | Organizer Workflow Reviewer | Organizer authority, SocialActor/organization/group capability, create/draft/publish/edit/manage/update/cancel/history flows | Organizer authority map; lifecycle validation differences; material-change and management gaps | pending wave 2 |
| MEET-G | Security Reviewer | IDOR, organizer/pet/participant forgery, visibility/join-policy bypass, invite tokens, exact-location leaks, block/account status, XSS and mass assignment | Threat inventory with assets/trust boundaries/abuse paths/mitigations and adversarial regression cases | pending wave 3 |
| MEET-H | Moderation/Safety Reviewer | Canonical blocks, reports, removals, suspended users, organizer safety rules, incidents and discussion boundaries | Reuse map; safe policy for blocked/removed/suspended actors; moderation/reporting gaps and tests | pending wave 3 |
| MEET-I | Mobile UX Reviewer | Discover/detail/create/edit/manage/invite journeys at 320-430 px, loading/offline/stale/empty states and touch interactions | Page/journey/state matrix; prioritized mobile defects; browser assertions | pending wave 3 |
| MEET-J | Accessibility Reviewer | WCAG 2.2 AA engineering practices for headings, forms, choices, errors, announcements, focus, keyboard, zoom, forced colors and motion | Semantic/interaction matrix; failure scenarios; automated and browser acceptance checks | pending wave 4 |
| MEET-K | Localization Reviewer | EN/LT/RU Meetup/Event keys, enums, validation, notifications, date/time/timezone formatting and long copy | Key/placeholder parity and terminology report; formatting gaps; representative locale tests | pending wave 4 |
| MEET-L | Performance Reviewer | Discovery, detail, organizer management, counts, pet summaries, pagination, query scopes and indexes | Current query map; N+1/unbounded risks; justified query ceilings/indexes and performance tests | pending wave 4 |
| MEET-M | Test Reviewer | Existing event/meetup/pet/place/social/auth/security/localization/a11y/performance/factory/seed/browser coverage | Risk-based regression matrix; overmocking/false-positive gaps; exact focused and full commands | pending wave 5 |

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
