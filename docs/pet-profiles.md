# Pet Profiles

## Implemented Foundation

`PetProfile` is the canonical animal identity. Its permanent `profile_key`
survives display-name, slug, manager, visibility, and lifecycle changes. The
legacy `user_id`, owner-scoped slug, species/breed strings, and encrypted
`profile_data` remain compatibility fields; no existing pet or adjacent-domain
record is replaced.

The foundation provides:

- a private, non-discoverable, idempotent minimal-create workflow;
- typed species, status, visibility, manager role, manager state, evidence,
  and permission values;
- nullable links to the shared scientific and domestic taxonomy;
- timed, individually attributable manager memberships with permission
  grants and denials;
- one layered privacy row per profile;
- optimistic profile updates and append-only lifecycle evidence;
- versioned encrypted critical facts with source, precision, verification,
  privacy, and replacement history;
- stable public URLs based on `profile_key`, retained slug aliases, and a QR
  representation of the stable route;
- class-based Livewire create, manage, invitation, and public-profile
  interfaces in EN, LT, and RU.

## Minimal Creation Experience

`/pets/manage/new` is the only pet-creation interface. The historical
`/compose/pet` URL remains compatible by redirecting to that canonical,
policy-protected Livewire route, and every first-party Add pet action links to
the canonical route directly.

The first save asks only for a name or temporary name, broad animal group,
the creator's current relationship to the animal, intended profile audience,
and an optional primary photo with an accessible description. The identity
values are validated by `PetProfileCreateForm`, while `PetProfileMediaForm`
validates the optional image; the action still creates one idempotent,
non-discoverable private draft with a stable profile key, manager membership,
privacy row, slug alias, and actor-attributed evidence.

Breed, taxonomy, birth details, sex, reproductive status, biography, medical
data, and care data belong to subsequent task-specific profile management.
The photo uses the canonical content media asset, a pet-specific placement,
generated private storage, image orientation/scaling/WebP processing, the
existing `manage-media` permission, and a policy-protected nested media route.
Replacement and logical removal retain a 30-day recoverable placement; no
browser state or rendered HTML receives the storage path or checksum. Selecting
a relationship sets initial access but does not establish legal ownership,
copyright, or professional verification.

## Primary Photo Lifecycle

One nullable unique current key enforces a single active primary photo per pet.
Upload replay is idempotent, and replacement preserves the prior placement as
superseded. Removal is reversible for 30 days. Restore repeats authorization,
can supersede a newer photo, and records immutable lifecycle and ordinary audit
evidence without recording file paths.

The current photo follows the profile view policy. Historical recoverable
photos require `manage-media`. File delivery is limited to the authenticated
portal, the private `local` disk, and the server-derived owning directory, with
canonical containment before streaming. Full galleries, pet/media tags,
moderation, attribution/licensing workflows, and permanent retention cleanup
remain Phase 20 work.

## Progressive Completion Workspace

After minimal creation, `/pets/manage/{petProfile}` exposes the twelve source
ordered steps through the URL-backed `step` query value. The navigator remains
in the central content column, renders only one active body, uses ordinary
links, and treats an invalid browser value as `basics`. Every step explains why
its values are requested. Skipping only navigates; it does not write a
completion flag or reduce a disclosure-based score.

Basics, age/sex, breed/origin, appearance, character, social preferences, and
broad location save independently through `UpdatePetProfileStep`. The Action
reloads the managed profile, authorizes the authenticated user, maps only the
step allowlist, locks and checks `lock_version`, records one idempotent
lifecycle event and audit entry, and invalidates the profile cache. Ordinary
descriptions remain in encrypted `profile_data`; broad location never accepts
an exact address or coordinates. Existing photo, manager, privacy, preview,
and lifecycle Actions remain the only mutation paths for their steps.

The documents step records one versioned encrypted `microchip-record` fact
containing status, an optional identifier, and document-readiness state. It is
private, hidden from serialization and public projection, and guarded by the
separate `change-microchip` permission. Roles without that permission receive
neither the value nor its completion signal. Actual document files remain in
the dedicated medical/document boundary. An existing identifier is never
pre-filled into the resting Livewire snapshot; an authorized editor may leave
the replacement input blank to retain the encrypted server-side value.

Navigation state is derived from already saved values. Relationship existence
uses bounded subqueries, and only the active step eager loads its detailed
relations. The mobile navigator is a contained ordered scroll-snap row, while
larger screens retain the complete grid, so the active form stays near the
current viewport without introducing page-level overflow. No completion
table, profile percentage, migration, or backfill is introduced by this
package. Exact scope is in
`docs/plans/pet-profile-progressive-completion-work-package.md`.

## Draft Autosave

The seven ordinary descriptive forms use native change events to save the
complete active step after a committed edit or blur. The class-based Livewire
component rejects a browser-provided step unless it is both a known enum value
and the active URL-backed step, then validates through the existing form
object and delegates to `UpdatePetProfileStep`.

A locked random idempotency key remains stable until the server accepts the
save and rotates only after success. A transport retry therefore reaches the
existing lifecycle-event replay boundary instead of recording a second
accepted operation. The manual save button uses the same path and remains a
keyboard and degraded-network fallback.

The passive save-status component announces saving and unsaved state. File
selection is explicitly unsaved until upload succeeds. Photos, managers,
privacy, protected microchip data, and lifecycle transitions keep their
explicit authorized submit operations; values are not copied into browser
local storage. A first-party reconnect adapter retains only the active form's
numeric revision in page memory. It repeats one pending ordinary Livewire save
after `online` and clears pending state only when the server confirms the same
revision, so a delayed response cannot hide newer unsaved input. Terminating
the tab can discard an unconfirmed focused edit, while every server-confirmed
value remains durable. Exact scope and remaining release gates are in
`docs/plans/pet-profile-draft-autosave-work-package.md`.

## Duplicate Review And Access Requests

Before a new draft is persisted, the canonical create component checks a
bounded set of profiles already visible to the authenticated account. It
matches normalized name plus broad species and exposes only a safe card:
name, localized species, approximate age, and an already policy-protected
primary photo. Private profiles, facts, documents, location, contacts,
manager data, storage paths, and checksums are excluded.

An encrypted short-lived token binds the viewer, submitted identity, current
candidate set, and expiry. The create Action verifies the token again; changing
the form or candidate set requires a new review. The user must explicitly
choose the different-animal path to continue creation when a match exists.

The existing-profile path records a typed `PetProfileAccessRequest` with
encrypted evidence and no immediate rights. The current manager reviews the
requester, requested role, and evidence through a separate policy-protected
Livewire component. Ordinary approval creates a manager invitation that the
requester must still accept. Relationship correction can update only an
existing non-critical role. Ownership-transfer requests are recorded but
cannot be approved through the standard action. Exact scope is in
`docs/plans/pet-profile-duplicate-access-work-package.md`.

## Species Confidence

The canonical pet keeps a controlled broad `species` for discovery and domain
links while `species_confidence` records whether that value is confirmed,
possible, or unidentified. Possible is accepted only for cat or dog; unknown
always becomes unidentified, and incompatible browser input is normalized at
the Action boundary.

Create and progressive basics forms expose only meaningful localized choices.
Workspace, public profile, invitations, and duplicate cards use
`PetSpeciesLabel` so a possible cat or dog is never displayed as a confirmed
fact. Existing managers with update permission can correct the value without
creating a new profile or changing its stable key. The exact package is in
`docs/plans/pet-profile-species-confidence-work-package.md`.

## Current And Alternative Names

`pet_profiles.name` is the current canonical name. A successful manager rename
keeps the former value as a previous name without changing the stable profile
key, canonical route, slug aliases, media, care, medical, device, social, or
audit relationships. A new history entry is private; an existing active
alternative retains its deliberately selected visibility when it becomes a
previous name.

The additive `pet_profile_names` relation supports nickname, previous, shelter,
official, localized, and responds-to purposes. Every entry has normalized
uniqueness within the pet, recorder attribution, optional locale, a searchable
flag, and private, manager, or public visibility. Private entries are visible
and searchable only to their recorder; manager entries require current manager
access; only public entries reach the public profile.

The Basics workspace is the authorized mutation and history surface. Pet
workspace search can resolve an accessible old or alternative name but still
returns the current canonical profile card. No public/global alias discovery is
introduced. Exact scope, query delta, remaining cross-domain consistency work,
and release evidence are in
`docs/plans/pet-profile-name-identity-work-package.md`.

## Birth Precision And Derived Age

The Age and sex step stores the certainty of a birth fact independently from
its value. Supported modes are exact date, estimated date, month and year,
year only, current age estimate, and unknown. An optional celebration month
and day is a chosen annual date and is never treated as proof of birth.

`PetBirthDetailsNormalizer` validates and canonicalizes every creation,
generic-update, progressive-update, autosave, and manual-save payload. It
rejects future and impossible values, retains only fields compatible with the
selected mode, and records when an age estimate was observed. The browser
cannot forge a precision/value combination that bypasses this server boundary.

`PetProfileAgeCalculator` derives age at read time. Exact and estimated dates
produce a point value; month-only and year-only values produce an uncertainty
range; an age estimate advances from its observation time; unknown has no age
projection. `PetProfileAgeLabel` applies the active locale without converting
an estimate into an exact fact. Event eligibility uses the same range and
requires its entire span to satisfy configured age limits.

Existing profile reads select four additional nullable scalar columns but add
no query. The new values are not filtered, sorted, or joined, so no index is
required. Exact scope, release evidence, and remaining life-stage and
verification work are in
`docs/plans/pet-profile-birth-precision-work-package.md`.

## Life Stage

The profile projects newborn, juvenile, young, adult, senior, or unknown from
the existing age range and a controlled animal-group catalogue. Dog, cat,
bird, rabbit, rodent, fish, reptile, and horse groups have separate thresholds;
unsupported groups do not inherit dog rules. An uncertain species, unknown
age, or age range crossing a threshold stays unknown.

The derived stage is recalculated at read time and is never stored. An
authorized profile manager may record a nullable clarification with actor and
observation time; clearing it restores automatic calculation. The override is
not medical verification. Workspace and public views receive the localized
stage and source from `PetLifeStagePresenter` without exposing provenance IDs
or timestamps. Exact scope and remaining cross-domain work are in
`docs/plans/pet-profile-life-stage-work-package.md`.

## Structured Appearance Color

The Appearance step stores one optional controlled primary color, up to four
unique additional colors, spots/stripes/gradient patterns, and optional
bounded clarification for general color, feathers, scales, and seasonal
changes. This species-neutral catalogue is stored in the existing encrypted
profile payload and does not replace the legacy free-text summary.

`PetAppearanceNormalizer` validates the full semantic payload for both the
progressive step and durable compatibility update Action. It rejects forged,
duplicated, over-capacity, or overlong values independently of Livewire.
`PetAppearancePresenter` converts stored values into localized scalars and
locale-aware lists before Blade renders them, without adding a query.

Public profiles expose the structured visible description but never the
manager-only identifying-marks value. Automatic lost/found description,
coat/mark structure, measurements, identity media, and search consumption stay
open. Exact scope is in
`docs/plans/pet-profile-appearance-color-work-package.md`.

## Species-Aware Body Covering

The Appearance step derives relevant controls from the existing broad species.
Mammals can describe coat length, texture, undercoat, or hairlessness; birds
can describe feather type; fish and reptiles reuse scale-color clarification;
horses can describe mane type; and relevant species can describe seasonal
shedding. A bounded skin observation is available to managers for every broad
species but remains private and is not a medical fact.

`PetBodyCoveringNormalizer` validates controlled values for both Livewire and
direct Action calls, rejects hairless/coat contradictions, and prunes fields
that do not apply to the stored species. The schema-versioned object stays in
the existing encrypted `profile_data` payload, alongside rather than in place
of structured color and legacy appearance text.

`PetBodyCoveringPresenter` prepares only localized public scalar values and
performs no query. Blade never receives the private skin observation. Search,
recommendations, care guidance, groomer/shelter/finder consumption, private
mark modelling, measurements, taxonomy verification, and medical records
remain separate boundaries. Exact scope is in
`docs/plans/pet-profile-body-covering-work-package.md`.

## Structured Identifying Marks

The Appearance step stores up to twelve identifying marks as normalized child
rows. Each row has a stable key, one of ten controlled visible feature types,
an encrypted bounded description, deterministic order, actor attribution, and
a nullable retirement time. Removing an entry retires rather than physically
deletes it. The historical encrypted free-text value remains private and
readable without being guessed into the new structure.

Only public and private-verification visibility are currently offered because
both are enforced through the complete path. The public profile eager loads
only active public rows and `PetIdentifyingMarkPresenter` repeats the audience
filter before Blade. Private verification evidence stays in the authorized
manager workspace and never enters public HTML. Friends, clinic, and active
search access remain open integrations rather than misleading choices.

The normalizer rejects forged type, visibility, description, duplicate, and
cross-pet row values. The synchronizer performs one scoped retirement update
and one bulk upsert with no query inside its bounded item loop. Exact scope is
in `docs/plans/pet-profile-identifying-marks-work-package.md`.

## Breed Origin And Provenance

The Breed or origin step stores the overall description separately from up to
four named origins. One breed, mixed origin, several possible breeds, no
breed, and unknown are distinct states. Each named origin keeps its own
confirmed/documented, owner-reported, or suspected confidence; document,
pedigree, shelter, veterinarian, genetic-test, owner-assumption, or unknown
source; and an optional percentage for mixed ancestry.

`PetBreedOriginNormalizer` validates the complete semantic payload once for
creation, generic update, progressive update, autosave, and manual save.
`PetBreedOriginSynchronizer` replaces the bounded normalized relation through
one delete and one upsert without accepting a row key from another pet. The
historical `pet_profiles.breed` value remains a compatibility snapshot rather
than the authoritative structured record.

Legacy breed strings remain owner-reported and are not guessed into the
taxonomy catalogue. A photograph never changes confidence or source. Public
profiles show the prepared confidence and provenance next to the breed value
and explicitly avoid character, health, compatibility, or value conclusions.
Exact scope, query delta, remaining evidence workflows, and release evidence
are in `docs/plans/pet-profile-breed-origin-work-package.md`.

## Identity And Compatibility

An account and a pet are separate aggregates. Every pet mutation runs as an
authenticated user and records the actor, manager role where available, pet,
reason, time, and optimistic version. Pet profiles never authenticate, accept
terms, pay, or own credentials.

Existing integrations continue to use the same `pet_profiles.id` and
`profile_key`. `PetProfileStatusCast` reads the historical `inactive` value as
`archived`; the bounded backfill persists the canonical value. Existing slugs
remain aliases, while new links use the permanent key.

## Manager Access

`pet_profile_managers` is the authorization relationship. A membership owns
the user, pet, role, state, start/end time, inviter/revoker, evidence state,
encrypted metadata, optional permission overrides, and lock version. Access
is evaluated at request time, so expiry or revocation takes effect even in an
already-open browser tab.

Primary owner, co-owner, legal representative, family member, shelter, foster
carer, sitter, caregiver, profile administrator, specialist, finder, previous
owner, organization administrator, volunteer, and other are distinct roles.
Critical permissions such as transfer, deletion, medical data, exact
location, primary-owner or microchip changes, adoption, marketplace, and
memorial activation are separate abilities. A role existing in the enum does
not prove legal ownership or professional verification.

## Privacy

The profile-wide audience is a maximum. Section rules can only narrow it.
Discovery, direct-link access, external indexing preference, owner/manager
display mode, and public location precision are independent settings. Public
queries additionally require a publicly eligible lifecycle state,
`visibility=public`, and discoverability.

Public presentation selects only name, species/common taxonomy label,
scientific name, breed, age label, status, bio, permitted owner label,
coarsened location, and avatar. Encrypted facts, exact location, evidence,
private metadata, medical data, contacts, documents, manager graph, and
idempotency keys are not part of that projection.

## Lifecycle

The implemented state registry contains draft, active, foster care, shelter,
seeking home, adoption in progress, transferred, lost, found, identity
unverified, disputed ownership, hidden, memorial, merged, deletion pending,
and archived. `PetProfileLifecycle` validates transitions, locks the row,
rejects stale versions, updates state timestamps, applies bounded discovery
effects, records an immutable event, and invalidates known cache keys.

This foundation does not claim the complete transfer, duplicate merge,
deletion cooling-off, memorial side-effect, adoption, lost/found, media
gallery, post, friendship, device, medical, or recommendation workflows. Their
requirement IDs remain open until their owning modules are connected and
verified.

## Data Migration

Run the additive migration, then:

```bash
php artisan pets:backfill-profile-foundation --chunk=500
```

The command uses bounded `chunkById` reads and short per-profile transactions.
It creates one primary-owner membership from the legacy owner, one privacy
row, one slug alias, and one idempotent backfill event. Rerunning it preserves
all IDs and creates no duplicates. It never truncates or classifies ambiguous
taxonomy data.

Before deployment, back up the database and record pet/profile-manager/privacy
counts. After deployment, rerun the command and verify that the created counts
are zero. Once production has written manager, fact, or lifecycle rows, retain
the additive tables and recover with a forward fix instead of a destructive
rollback.

## Recovery

- Revoke a compromised manager through the authorized action; do not delete
  membership history.
- Correct a lifecycle mistake with an allowed reviewed transition; do not edit
  lifecycle events.
- Correct a critical fact by recording a replacement; do not overwrite its
  provenance.
- If privacy is unexpectedly broad, set the profile to private/hidden, clear
  the documented cache keys, and investigate the immutable event and audit
  records.
- If backfill is interrupted, rerun the same command. Existing unique keys and
  idempotent event keys make continuation safe.

## Verification

`tests/Feature/PetProfileFoundationTest.php` covers migration shape, enum
casts, idempotent creation and backfill, preservation, permission boundaries,
expiry/revocation, optimistic transitions, immutable facts/events, privacy,
cache invalidation, stable URLs, Livewire direct authorization, translation
parity, and bounded query behavior. `SocialPersistenceTest` preserves the
legacy action contract. Browser checks cover desktop, mobile, and 320px reflow,
semantic landmarks, names, touch targets, overflow, and console errors.

The implementation plan and exact execution evidence are in
`docs/plans/pet-profile-foundation-work-package.md`; requirement-level status
is generated from `docs/traceability/forum-requirement-evidence.json`.
The optional primary-photo boundary and its narrower verification are in
`docs/plans/pet-profile-primary-photo-work-package.md`.
