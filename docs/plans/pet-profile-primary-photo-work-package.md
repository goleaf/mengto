# Pet Profile Primary Photo Work Package

Date: 2026-08-03

Status: implemented and verified

## Requirement

This package implements exactly `pet.creation.0025`: the primary photo is an
optional part of the first pet-profile save. It does not claim the wider
`pet.media.*` gallery, tagging, consent, attribution, moderation, export, or
permanent-erasure requirements assigned to Phase 20.

## Result

The canonical `/pets/manage/new` Livewire form accepts an optional JPG, PNG,
or WebP file and a required description when a file is selected. A successful
upload is oriented, bounded to 2560 by 2560 pixels without enlargement,
re-encoded as WebP, and stored with a generated name on the private `local`
disk. Creating a profile without a photo remains valid.

The management workspace can replace, remove, and restore the primary photo.
Only one placement can own the portable `primary:{pet_profile_id}` current key.
Replaced or removed placements remain recoverable for 30 days; this package
does not physically erase an asset that may still need recovery or may later
be reused by another canonical media placement.

## Persistence

`content_media_assets` remains the shared media object and stores the storage
owner, real uploader, generated private path, MIME type, byte size, checksum,
safe dimensions, description, processing status, and retention metadata.
`pet_profile_media` is the additive pet-specific placement table. It stores the
pet, asset, actor, role, active/superseded/removed state, unique current and
upload keys, and replacement/removal/restoration timestamps.

Foreign keys prevent orphaned pets and assets. Unique keys prevent duplicate
placements, duplicate upload replays, and more than one current primary photo.
Indexes cover current profile-role reads, asset reverse lookup, and recovery
expiry. The migration is reversible and does not rewrite existing profiles or
legacy `profile_data` avatars.

## Authorization And File Delivery

`PetProfilePolicy::manageMedia` resolves the existing `manage-media`
permission at request time. The create and management components validate
browser state, and every store/remove/restore Action repeats authorization
before and inside its locked transaction. Actor-attributed lifecycle and audit
records contain stable media keys but never paths or checksums.

The nested `pets.media.show` route resolves the media beneath its pet, returns
404 for a foreign placement, and checks the pet view policy before serving a
current photo. Recoverable historical media is visible only to a current
manager with `manage-media`. `PrivateFileResponse` then accepts only `local`
and canonically contains the file under the server-derived
`pet-profiles/{profile_key}/media` directory. Responses are `no-store`,
MIME-fixed, and protected against traversal and symbolic-link escape.

## Failure And Recovery

- Invalid content, MIME, size, dimensions, or missing description fails before
  profile media persistence.
- A failed database write compensates by deleting only the newly generated
  unreferenced file.
- Replaying an upload key returns the existing placement and creates no file,
  asset, placement, event, or audit duplicate.
- Removal clears the current key but retains the private asset for 30 days.
- Restoration re-authorizes the actor, supersedes any newer current photo, and
  returns the selected recoverable placement to active state.
- Permanent erasure and cross-placement retention remain a later media-package
  concern and must not delete a still-referenced canonical asset.

## Query Delta

The creation page adds no database query until submission. A successful first
photo save adds bounded idempotency, locked profile/current placement, asset,
placement, lifecycle, and audit writes. The public profile adds two bounded
eager-load queries for the current placement and its asset; query count remains
independent of manager and media history. The management workspace adds the
same current pair plus a bounded latest-recoverable pair. Blade performs no
query or storage access.

## Verification

`PetProfileMediaLifecycleTest` covers optional creation upload, WebP
processing, generated private paths, safe metadata, inner Action validation,
negative authorization, idempotent replay, replacement, logical removal,
30-day recovery, restoration, policy-contracted delivery, private HTML, path
containment, and the complete management Livewire workflow.

`PetProfileCreateExperienceTest` covers the optional controls and conditional
description validation. `PetProfileFoundationTest` preserves photo-free
creation, public query bounds, permissions, and compatibility.

Observed on 2026-08-03:

- focused media/create/foundation: 27 tests and 1,948 assertions;
- media/schema/navigation/page-identity regression: 72 tests and 620
  assertions;
- architecture/localization/private-file/security/portal boundary: 85 tests
  and 60,963 assertions;
- isolated fresh/repeated seed: 130 migrations, 215 tables, stable five-user
  identity count, and both exits `0`;
- targeted Pint and Larastan, Composer strict validation/audit, npm audit, Vite
  production build, and the connected Chrome matrix passed;
- the first full serial run exposed three package integration gaps and a
  concurrent Discover factory defect. After correction, the final isolated
  serial suite passed 2,635 tests and 83,160 assertions in 177.476 seconds;
  full Pint and Larastan also passed.
