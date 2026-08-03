# Pet Profile Duplicate Review And Access Request Work Package

Last updated: 2026-08-03.

Status: implemented and release-verified on the current tree; publication is pending.

## Scope

This package implements the dependency-safe creation slice
`pet.creation.0103-pet.creation.0137`, except the named managing-organization
projection in `pet.creation.0115`. That field remains open because the current
manager relationship has no authoritative organization link and the
application must not infer one from a person's unrelated memberships.

The package owns:

1. a duplicate review before creation;
2. a safe, policy-filtered possible-match card;
3. an explicit “This is my pet” access-request path;
4. an explicit “This is a different animal” continuation path;
5. co-ownership, profile-management, temporary-access,
   relationship-correction, and ownership-transfer request types;
6. private manager review with requester, role, and evidence;
7. an invitation-and-acceptance boundary for ordinary approved access;
8. a fail-closed boundary that records, but cannot generically approve, an
   ownership-transfer request.

Profile merge, organization attribution, legal-document verification,
ownership transfer, disputes, adoption handoff, and administrative duplicate
resolution remain separate Phase 18/19 packages.

## Duplicate Review Contract

`PetProfileDuplicateReview` normalizes a submitted name with Unicode-aware
Laravel string helpers, requires the same broad species, considers at most 50
policy-visible profiles, returns at most six exact normalized-name matches,
and loads media only after the bounded identity filter. The query uses
`PetProfile::visibleTo()` so another account's private or non-discoverable
profile never becomes a candidate.

The card projection contains only the stable profile key needed by the
server-backed action, name, localized species, approximate age, and the
policy-protected primary-photo route with alternative text. It excludes
profile data, facts, manager records, contacts, locations, documents,
microchip data, storage paths, and checksums.

The service encrypts a 30-minute review token containing the viewer, normalized
identity, ordered candidate keys, and expiry. `CreatePetProfile` repeats the
review at the Action boundary. A changed name/species, changed candidate set,
expired token, or modified token requires another review. Existing
idempotency replays are resolved before the duplicate check so a lost response
does not block or recreate the already accepted draft.

## Access Request Contract

`pet_profile_access_requests` stores a stable request key, requester snapshot,
typed request and role, encrypted evidence, optional bounded temporary end,
status, optimistic version, reviewer, encrypted resolution note, and the
resulting manager invitation. Nullable unique active, submission, and decision
keys prevent simultaneous pending claims and duplicate delivery while allowing
a resolved request to be followed by a later legitimate request.

`SubmitPetProfileAccessRequest` requires an active authenticated account, a
profile already visible under policy, validated evidence, a type-compatible
non-critical role, and a future temporary end no more than one year away. It
creates no manager membership and grants no capability.

`ReviewPetProfileAccessRequest` requires `manage-managers`, reauthorizes after
locking the profile, locks the request, rejects self-review, and records an
immutable lifecycle event. An approved ordinary request creates the existing
manager invitation; the requester must separately accept it before access
becomes active. Relationship correction updates only an existing active
non-critical role. Transfer approval is rejected by this Action and remains
pending for the protected ownership workflow.

## Interface And Accessibility

The existing class-based `/pets/manage/new` Livewire component pauses before
creation when safe matches exist. Candidate media is image-led, every action
is a 44-pixel button, empty and feedback states are explicit, and the request
form keeps evidence guidance and errors associated with their controls. All
text is localized in EN, LT, and RU.

Managers reach a separate policy-protected
`/pets/manage/{profile}/access-requests` component from the owners step. It
loads at most 25 pending requests with the requester relation eagerly loaded,
formats dates through `LocaleFormatter`, and never sends evidence to an
unauthorized component snapshot or route response.

## Query Delta

Before this package, minimal creation performed no duplicate lookup and had no
access-request read model. After this package:

- no-match review adds one bounded profile query;
- a match-card review uses at most three queries: profiles, current primary
  placements, and their media assets;
- token validation and candidate selection use one bounded profile query and
  do not reload media;
- manager review uses one bounded profile query, one pending-request query,
  and one eager requester query, independent of the number of rendered cards.

The focused query regression creates eight matching profiles, proves only six
cards are returned, and observes no more than three duplicate-review queries.
The existing media indexes cover the current-photo relation. A dedicated
species/discoverability/visibility/status/id index supports the bounded profile
scan, while the access-request migration adds profile/status/time and
requester/status/time indexes.

## Verification Plan

- [x] focused duplicate/access request Pest scenarios;
- [x] complete pet create/foundation/media/progressive/workspace regression;
- [x] scoped Pint;
- [x] scoped Larastan;
- [x] full Pint and full Larastan;
- [x] immutable source and deterministic requirement generation;
- [x] isolated fresh migration, rollback/reapply, and repeat seed;
- [x] Composer and NPM audits plus production Vite build;
- [x] config/event/route/view cache smoke checks;
- [x] connected desktop/mobile/320px browser review;
- [x] final sequential Pest suite;
- [ ] scoped diff, temporary-index commit, push, and publication evidence.

Observed release evidence on 2026-08-03:

- focused duplicate/access boundary: 15 tests and 68 assertions;
- pet, architecture, localization, responsive, and page-identity regression:
  156 tests and 65,826 assertions;
- final serial repository suite: 2,747 tests and 87,289 assertions in 162.853
  seconds;
- full Pint and Larastan: passed with zero findings;
- Composer strict validation/audit, PHP 8.5 platform requirements, npm audit
  with zero vulnerabilities, and the Vite 8.2.0 production build: passed;
- isolated SQLite: 132 migrations, 216 tables, complete rollback/reapply, and
  stable five-user repeated seed; the migration lifecycle test passed 2 tests
  and 11 assertions;
- isolated config/event/route/view cache compilation, immutable source check,
  and deterministic 38,377-requirement check: passed;
- disposable-database Chrome: one safe public candidate, no private-candidate
  or credential leak, real Livewire access submission, authorized manager
  evidence review, zero page overflow, raw keys, duplicate IDs, undersized or
  unnamed controls, and console errors at desktop, 375px, and 320px.

Requirement evidence marks only the 34 selected atomic IDs verified.
`pet.creation.0115` and all wider duplicate, merge, organization, proof,
dispute, and transfer IDs remain open. The 50-profile recall window is a
deliberate bounded first package; dense species populations still need an
indexed normalized-identity signal before this can become exhaustive.
