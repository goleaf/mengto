# Canonical Event System

## Status

Point 13 is partially implemented. This directory records executable evidence
and open scope; it does not treat source requirements as implementation proof.
The immutable Point 13 text is Source Part J in
`docs/requirements/forum-source-prompt.md` and is normalized into 4,968 atomic
requirements under `event.*` identifiers.

## Canonical Boundary

`ForumEvent` is the only portal event aggregate. It reuses users, managed pet
profiles, groups, taxonomy, professional credentials, reports, notifications,
and the existing `/meetups` shell. `ForumGroupActivity` is a compatibility
calendar projection linked to a canonical event. `AdoptionEvent`,
`MedicalEvent`, `DeviceEvent`, and similarly named records are module audit or
workflow records, not alternative public event aggregates.

The current release adds:

- a controlled 16-type registry plus readable legacy types;
- expanded lifecycle, visibility, registration, pet-participation,
  accessibility, verification, recurrence, and team-role enums;
- authoritative owner separate from creator and scoped team membership;
- version snapshots, event series, concrete occurrences, and occurrence-aware
  registration;
- multi-pet registration with server-side ownership and eligibility checks;
- encrypted private venue/access data and policy-scoped disclosure;
- idempotent lifecycle initialization/backfill and explicit status transitions;
- EN, LT, and RU labels with recursive key parity;
- production-safe backfill and environment-gated demo data;
- class-based Livewire directory/workspace improvements using existing shared
  status, form, page, and action components.

## Current Route Surface

| Route | Purpose | Authorization |
| --- | --- | --- |
| `meetups.index` | Bounded event discovery and event creation | `ForumEventPolicy::viewAny/create` |
| `meetups.show` | Canonical event workspace by bound event | `ForumEventPolicy::view` |
| `meetups.small_dog_social` | Stable compatibility detail URL | Same bound-event policy |
| `meetups.created` | Legacy created-content compatibility URL | Existing created-content boundary |

All product routes are inside the verified active-account portal boundary.
Unlisted events are direct-link visible but excluded from directory queries.
Private, group, organization, and invitation visibility use group access,
accepted invitations, owner, administrator, or active event-team membership.

## Implemented State Machines

- Event: controlled enum plus explicit transition graph in
  `ForumEventStatus::canTransitionTo()` and `TransitionForumEventStatus`.
- Registration: draft through attended/refunded/safety review states; seat
  consumption and cancellation eligibility are enum-owned.
- Pet eligibility: one row per registration/pet with explicit verification
  status, source, conditions, and check-in/check-out times.
- Team membership: invited/active/removed/expired status and scoped roles.
- Occurrence: stable occurrence identity, series defaults, overrides, and
  occurrence status.

## Honest Gaps

The repository does not yet contain verified provider-backed event payment,
ticket, refund, receipt, donation, checkout reservation, QR/offline scanning,
tracks/sessions, competition scoring, vendor/booth, sponsor, volunteer-shift,
event incident, weather-plan, certificate, or event-feedback aggregates.
Positive event prices remain metadata and registration refuses to simulate a
charge. Existing generic booking/payment-like UI is not promoted to an event
payment implementation.

There is no canonical organization tenant model. Organization-only visibility
currently means an active authorized event-team scope; cross-organization
tenant isolation cannot be claimed until that portal aggregate exists.

## Evidence

- Domain tests: `tests/Feature/Forum/EventWorkflowTest.php`
- Point 13 foundation tests:
  `tests/Feature/Forum/EventLifecycleFoundationTest.php`
- Migrations: `2026_08_03_082151` through `2026_08_03_082155`
- Factories: `ForumEvent*Factory` and `ForumEventRegistrationPetFactory`
- Seeders: `ForumEventLifecycleBackfillSeeder`, `ForumEventDemoSeeder`
- UI: `ForumEventDirectory`, `ForumEventWorkspace`, and their Blade views
- Localization: `lang/{en,lt,ru}/forum_events.php`
- Browser contract: event directory and recurring-event detail checks in
  `scripts/accessibility-browser-check.mjs`

The detailed documents in this directory define each boundary and its current
evidence. The status matrix is `requirements.md`.
