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
the creator's current relationship to the animal, and intended profile
audience. Those four browser-controlled values are validated by the dedicated
`PetProfileCreateForm`; the action still creates one idempotent,
non-discoverable private draft with a stable profile key, manager membership,
privacy row, slug alias, and actor-attributed evidence.

Breed, taxonomy, birth details, sex, reproductive status, biography, medical
data, and care data belong to subsequent task-specific profile management.
Primary photo upload remains a separate open media package because it needs a
private-file ownership, authorization, transformation, deletion, and recovery
boundary; the creation screen does not present a non-functional upload
control. Selecting a relationship sets initial access but does not establish
legal ownership or professional verification.

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

This foundation does not claim the complete transfer, duplicate resolution,
deletion cooling-off, memorial side-effect, adoption, lost/found, media, post,
friendship, device, medical, or recommendation workflows. Their requirement
IDs remain open until their owning modules are connected and verified.

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
