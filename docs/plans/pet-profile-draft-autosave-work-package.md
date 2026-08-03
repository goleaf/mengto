# Pet Profile Draft Autosave Work Package

Last updated: 2026-08-03.

Status: implemented and verified for
`pet.creation.0071-pet.creation.0081` on 2026-08-03.

## Scope

This package continues the verified twelve-step pet-profile workspace with a
safe autosave boundary for the seven ordinary descriptive steps:

1. basics;
2. age and sex;
3. breed or origin;
4. appearance;
5. character;
6. social preferences;
7. broad location.

Photo, manager, privacy, protected-document, and lifecycle operations retain
their explicit authorized submit actions. File selection visibly remains
unsaved until the upload action succeeds. This package does not silently
persist a microchip identifier, invite, privacy decision, publication, or
status transition.

## Persistence Contract

- Native form change events send the complete active step to the class-based
  Livewire component. Text controls therefore save after a committed edit or
  blur, while selects save immediately after selection.
- The browser-provided step value is matched against both the closed step enum
  and the current URL-backed step. A mismatched or unsupported step is rejected
  before validation or mutation.
- Every value is validated by the existing step-specific Livewire form method
  and is then passed to `UpdatePetProfileStep`; Blade performs no mapping,
  authorization, or persistence logic.
- One locked random idempotency key is retained for the current save attempt.
  It rotates only after a successful response, so a transport retry cannot
  create a second lifecycle event for the same accepted request.
- `UpdatePetProfileStep` continues to requery the managed profile, authorize
  the actor, lock the row, verify `lock_version`, explicitly map permitted
  fields, record immutable evidence, invalidate the cache, and preserve all
  other profile sections.
- The manual save button remains available as a keyboard-accessible and
  degraded-network fallback. It uses the same operation and idempotency key.

## Interface Contract

- A reusable passive Blade component exposes `saving`, `unsaved`, and saved
  status through one polite atomic live region.
- The status never claims a validation failure was saved; field errors and the
  existing error summary remain visible.
- The global offline notice remains present. Autosave does not queue mutations
  in local storage and therefore does not place profile or location values on
  a shared device outside the authenticated server boundary.
- Temporary photo selection is explicitly labelled unsaved. Sensitive
  microchip input and all operational forms retain explicit submission.

## Query And Security Contract

Autosave introduces no read query on the initial page render and no schema,
cache, or background-worker dependency. A successful autosave uses the same
bounded action query shape as manual step save. Repeated request delivery is
deduplicated through the existing lifecycle-event idempotency boundary.

The idempotency key is locked Livewire state and contains no user data. All
public state and the step parameter remain untrusted; policies, the managed
profile scope, validation, optimistic locking, and explicit field allowlists
remain server authoritative.

## Verification Evidence

- `PetProfileProgressiveCompletionTest`: 20 tests and 128 assertions pass.
- The autosave scenario proves rendered change wiring, persistence of both
  appearance fields, one lifecycle event, restoration after a fresh component
  mount, and rejection of a mismatched step without another event.
- The existing action scenario proves a replayed idempotency key creates only
  one update event and a stale write is rejected.

## Completed Gates

- [x] combined create, media, foundation, and progressive regression;
- [x] full Pint and Larastan;
- [x] Blade compilation and production Vite build;
- [x] authenticated browser verification of the integrated workspace across
  EN/LT/RU desktop and mobile states; the mutating autosave scenario ran only
  against a freshly migrated disposable SQLite database;
- [x] dependency audits and cache/view smoke checks; no schema or seed change
  belongs to this package;
- [x] dedicated atomic requirement evidence overlay and generated traceability
  refresh.

The final current serial suite passed 2,685 tests with 85,057 assertions. The
progressive-to-workspace order regression passed 30 tests with 202 assertions.
The connected browser gate additionally changed the appearance form through a
real DOM change, observed the autosave and offline status contracts, reloaded
to prove server persistence, restored the original displayed value, and
reported no console errors. The harness requires both `--autosave` and
`BROWSER_ALLOW_DATA_MUTATION=1`; that combination is valid only with a
disposable database because lifecycle and audit history intentionally remain
immutable. The attributable implementation was published directly from
`main` to `origin/main` as commit `fc47895` (`feat: autosave pet profile
drafts`).

## Explicit Non-Goals

- storing drafts in `localStorage` or another unencrypted browser cache;
- background upload of a photo before alternative text validation;
- autosaving microchip identifiers, invitations, privacy rules, or lifecycle
  transitions;
- a numeric completion score;
- claiming `pet.creation.0059-pet.creation.0070` complete from this package;
- ownership proof, transfer, duplicate resolution, or the full gallery.
