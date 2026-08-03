# Pet Profile Current Progress

Last updated: 2026-08-03.

## Current State

- Source revision preserved: 155,417 bytes.
- Revision SHA-256: `2f45d1f423e3ac0755db8b91aeea0c07315c19fb8e7f40647e3c068de5e256bc`.
- Master SHA-256: `a7cde460775a0339e8a82490a41e9a9a557296a28846dfaadff4df17bad53717`.
- New atomic requirements: 4,135.
- Total master atomic requirements: 11,419.
- Existing 7,284 requirement IDs retained in their original order.
- Pet Gate 0 source preservation and extraction: complete.
- Pet Gate 1 repository discovery: documented.
- Pet Gate 2 detailed implementation planning: complete for the selected
  foundation package; later pet phases remain planned.
- The canonical identity/access foundation is implemented, migrated,
  translated, tested, documented, and verified. This is not completion of the
  full 4,135-requirement pet-profile revision.
- The progressive completion slice `pet.creation.0036-pet.creation.0058` is
  implemented, localized, documented, and verified; later pet requirements
  remain open.
- The draft-autosave slice `pet.creation.0071-pet.creation.0081` is implemented
  and verified with the canonical `/pets` workspace and dedicated requirement
  evidence overlay.
- The bounded duplicate-review and encrypted access-request slice
  `pet.creation.0103-pet.creation.0137` is implemented, release-verified, and
  published for 34 selected IDs. `pet.creation.0115` remains open because
  organization attribution is not yet authoritative.
- The honest incomplete-species slice `pet.creation.0170-pet.creation.0186` is
  implemented, release-verified, and published. Possible cat/dog is stored
  separately from the normalized broad species.
- The bounded pet-name identity slice verifies 20 selected
  `pet.identity.0003-pet.identity.0040` requirements for current, typed
  alternative, historical, visibility-aware, and manager-searchable names. It
  is implemented, release-verified, and published.
- The birth-precision and automatically advancing age slice verifies 17
  selected `pet.identity.0160-pet.identity.0186` requirements. It is
  implemented, release-verified, and published with exact, estimated, month,
  year, age-estimate, unknown, and optional celebration-day modes.

## Current Work Package

The current package preserves honest birth precision and calculates age from
the stored fact or observation instead of persisting a stale manual value. It
preserves all existing pet and adjacent-domain links while adding typed modes,
one server normalization boundary, range-aware event eligibility, localized
public projection, and an optional explicitly unverified celebration day.

## Creation Interface Refinement

- `/pets/manage/new` is now the only rendered creation flow;
  `/compose/pet` redirects to it and first-party Add pet links target it
  directly.
- The first save contains only name, broad species, relationship, and intended
  audience. A dedicated Livewire form object validates those four public
  values and maps them to the existing idempotent creation action.
- Taxonomy, breed, birth details, sex, reproductive status, and biography were
  removed from the first screen and remain available through advanced profile
  management.
- The responsive Blade/SCSS surface now has linked help and error text,
  44-pixel controls, loading/dirty/offline states, a mobile-safe action area,
  explicit private-draft guidance, and EN/LT/RU parity.
- Focused creation/foundation tests pass 18 tests and 1,677 assertions;
  architecture/localization checks pass 28 tests and 59,795 assertions. Pint,
  targeted Larastan, diff checks, and the Vite production build pass.
- The final serial repository suite passed 2,586 tests and 81,835 assertions
  in 175.725 seconds with isolated config, route, service, package, event, and
  compiled-view cache paths. Two earlier shared-cache attempts were invalidated
  by concurrent Vite/cache/database writes and are not presented as passing
  evidence.
- A focused dependency-free Chrome audit passed at 1440 by 900 and 375 by 812:
  one H1/main/form, exactly four creation fields, no horizontal overflow,
  unnamed or sub-44-pixel controls, raw translation keys, or console errors.
  Desktop and mobile screenshots were visually reviewed after correcting the
  submit icon size.

## Optional Primary Photo

- `pet.creation.0025` now has a real optional create control instead of a
  placeholder. Photo-free creation remains valid; selecting a photo requires a
  short accessible description.
- The shared `content_media_assets` object is linked through the additive
  `pet_profile_media` placement table. Uploads use generated names on private
  `local` storage and are oriented, scaled, stripped through re-encoding, and
  stored as WebP.
- The `manage-media` policy protects upload, replacement, removal, restoration,
  and historical recovery. The nested response route repeats profile/media
  scoping and canonical owning-directory containment; paths and checksums never
  enter rendered HTML.
- Replacement/removal retains a 30-day recovery window. Permanent asset
  erasure remains open until shared-reference retention is implemented.
- Focused media/create/foundation tests pass 27 tests and 1,948 assertions;
  the 72-test integration regression, targeted Larastan, isolated fresh and
  repeated seed, Vite build, and connected Chrome matrix also pass. The first
  full serial run exposed and drove correction of three package gaps and a
  concurrent Discover factory defect. The final isolated serial suite then
  passed 2,635 tests and 83,160 assertions; full Pint and Larastan also pass.

## Progressive Completion Workspace

- `pet.creation.0036-pet.creation.0058` now maps to twelve centrally rendered,
  URL-backed steps in the exact source order. Only the active body renders;
  every step has an explanation, ordinary link, independent save, and a
  mutation-free skip path.
- `UpdatePetProfileStep` explicitly allowlists seven descriptive sections,
  reauthorizes the managed profile, checks an optimistic version under a row
  lock, keeps replay idempotent, updates compatibility state, records immutable
  lifecycle/audit evidence, and invalidates the profile cache.
- The existing photo, manager, privacy, stable preview, and lifecycle Actions
  are composed into their respective steps instead of being duplicated.
- Appearance, character, social preferences, and coarse location stay in the
  existing encrypted profile data. The UI rejects exact-location collection
  by design and presents social notes as observations rather than diagnoses or
  compatibility guarantees.
- The protected documents step stores a versioned encrypted
  `microchip-record`; `change-microchip` authorization controls both detailed
  loading and mutation, while actual files remain outside this package.
- Navigation status is derived from saved values and bounded existence
  subqueries. No completion table, migration, backfill, percentage score, or
  new public projection was added.
- The focused progressive suite passes 6 tests and 71 assertions; the combined
  canonical create/legacy redirect, pet foundation, media, and progressive
  regression passes 33 tests and 2,601 assertions. The final serial repository
  suite passes 2,657 tests and 84,589 assertions; static, dependency, isolated
  database/cache, source-generation, build, and connected responsive browser
  gates also pass. The 23 selected requirement IDs are verified.

## Completion Evidence

- Additive migration produced 100 migrations and 177 tables on isolated fresh
  SQLite; rollback/re-application retained the populated pet rows.
- Repeat seeding/backfill retained two pets and created no duplicate manager,
  privacy, alias, or lifecycle rows.
- Pet foundation plus legacy compatibility tests passed 21 tests and 1,603
  assertions; the final serial repository suite passed 1,748 tests and 68,172
  assertions in 103.288 seconds.
- Full Pint, Larastan level 5, Composer strict validation/audit, npm audit,
  Vite build, cache compilation, source preservation, and requirement
  generation checks passed in the package cycle.
- Desktop, mobile, and 320px browser checks found no overflow, unnamed
  controls, duplicate IDs, invalid images/tables, undersized mobile actions,
  or console errors on pet create/manage/invitation/public surfaces.
- 205 exact pet requirement IDs are verified by the foundation evidence
  overlay; every other pet ID remains open.

## Draft Autosave

- Basics, age/sex, breed/origin, appearance, character, social preferences,
  and broad location save after a committed native form change while retaining
  their manual save actions.
- A browser step must match the closed step enum and active workspace step.
  The same form validation, managed-profile authorization, field allowlist,
  optimistic lock, lifecycle evidence, audit, and cache invalidation apply to
  automatic and manual requests.
- One locked request key is reused until success and then rotated. Existing
  replay handling prevents a lost-response retry from duplicating the accepted
  lifecycle operation.
- A reusable passive live region exposes saving and unsaved state. Temporary
  photos are explicitly unsaved; sensitive and operational forms are not
  silently persisted.
- A small first-party adapter keeps only a numeric pending revision in the
  authenticated page. Returning connectivity retries one pending descriptive
  form through the same action; a revision-matched server event prevents an
  older response from clearing newer input. No profile value enters browser
  storage.
- The focused progressive test passes 27 tests and 159 assertions, including
  all-step wiring, validation-key stability, six bounded client-revision
  acknowledgement cases, no-op replay, fresh-mount recovery, and mismatched
  step rejection. The isolated reconnect suite passes 2,692 tests and 85,091
  assertions, and the final complete current-tree suite passes 2,695 tests and
  85,875 assertions; a connected disposable-database browser check proves one
  ordinary save request, one intentionally failed offline request, one
  automatic reconnect retry, persistence after reload, mobile reflow, and
  restoration of the original seeded value. All eleven atomic requirement IDs
  remain verified by their dedicated evidence overlay.

## Pet Name Identity

- `pet_profiles.name` remains the current canonical name. Successful renames
  retain the former value as a typed previous name without changing the stable
  profile key, canonical URL, slug aliases, or adjacent-domain links. New
  history is private; an active existing alias retains its selected visibility.
- Alternative names support nickname, previous, shelter, official, localized,
  and responds-to purposes, normalized uniqueness, optional locale, recorder
  attribution, searchability, soft deletion, and private, manager, or public
  visibility.
- Add/remove Actions reload, authorize, and lock the managed pet; maintain
  optimistic versioning; record lifecycle and audit evidence; and invalidate
  cache. A private entry remains removable only by its recorder.
- The localized Basics workspace exposes the bounded editor and history. The
  public profile receives only public alternatives. The existing policy-scoped
  workspace query can resolve a viewer-visible alternative through one indexed
  correlated predicate without an extra round trip.
- Focused verification passed 9 tests and 61 assertions; the related regression
  passed 139 tests and 66,717 assertions. The final sequential suite passed
  2,801 tests and 89,231 assertions; static, dependency, disposable database,
  source-generation, build, cache, and connected Chrome gates also passed.
- Exactly 20 selected `pet.identity.0003-pet.identity.0040` records carry the
  package evidence. Cross-domain name consistency requirements
  `pet.identity.0005` and `pet.identity.0011` remain open.

## Birth Precision And Derived Age

- Exact date, estimated date, month/year, year only, current age estimate, and
  unknown are distinct typed states. Optional celebration month/day is stored
  separately and never presented as a verified birth date.
- One server-side normalizer is reused by creation, generic update,
  progressive update, autosave, and manual save. It rejects impossible/future
  values and clears fields that do not belong to the selected mode.
- One reusable calculator returns an age range at the current reference time.
  Age estimates advance from their recorded timestamp; month/year facts keep
  their uncertainty; unknown remains unknown.
- Workspace, public profile, duplicate review, lost/found, and event
  registration use the same prepared age semantics. An uncertain age satisfies
  event limits only when its complete range is eligible.
- Focused verification passed 15 tests and 83 assertions; the affected
  regression passed 138 tests and 4,382 assertions. The isolated sequential
  suite passed 2,831 tests and 90,267 assertions; static, dependency,
  disposable database, build, cache, and connected Chrome gates also passed.
- Exactly 17 selected `pet.identity.0160-pet.identity.0186` records carry this
  package evidence. Section prompts, life-stage labels, medical verification,
  breed provenance, ownership, and found-animal workflows remain open.

## Next Package

Select the next dependency-safe breed-provenance or appearance-fact package.
Do not treat safe candidate review, possible species, alternative-name
history, or birth precision as duplicate merge, proof verification, taxonomy
verification, lost/found coordination, dispute resolution, organization
attribution, cross-domain rename propagation, or ownership transfer.

Plan and implement the next dependency-safe pet slice from
`docs/plans/pet-profile-master-plan.md`. Ownership proof and transfer,
destructive lifecycle, media, social graph, lost/found, adoption,
medical/care/device links, recommendations, analytics, and advanced privacy
must not inherit verified status from this foundation.

For creation specifically, the progressive completion flow
(`pet.creation.0036-pet.creation.0058`) is verified. The optional primary photo
(`pet.creation.0025`) is implemented by the narrower media package. Draft
autosave (`pet.creation.0071-pet.creation.0081`) is release-verified and
covered by its dedicated requirement evidence overlay. The remaining
gallery and full `pet.media.*` scope stay open until their own work packages
are selected.
