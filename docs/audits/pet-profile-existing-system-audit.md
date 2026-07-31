# Pet Profile Existing System Audit

Date: 2026-07-31

## Scope And Evidence

This audit covers the additive pet-profile revision preserved at source
timestamp `1785514046`. The exact revision is 155,417 bytes with SHA-256
`2f45d1f423e3ac0755db8b91aeea0c07315c19fb8e7f40647e3c068de5e256bc`.
It was inspected together with the current `main` worktree, migrations,
models, policies, routes, presenters, seeds, factories, tests, and live SQLite
schema. No production code was changed before this audit and plan existed.

## Current Aggregate

`PetProfile` is a standalone Eloquent model with a stable unique
`profile_key`, owner-scoped slug, display name, string species and breed,
single date of birth, broad visibility/status strings, encrypted
`profile_data`, timestamps, and soft deletion. The table has one required
`user_id`; therefore the current schema models one account owner rather than
multiple role-bearing managers.

The live local schema has 14 columns, unique indexes on `profile_key` and
`(user_id, slug)`, an index on `(visibility, status, id)`, and a cascading
foreign key to `users`. Current seeded data contains two public pet profiles,
two medical records, two care journals, three search cases, and one adoption
case.

## Current Interfaces

- `/pets` and two stable demo URLs render server-side Blade through presenter
  arrays and reusable components.
- The profile and directory use a database override when a matching active
  profile exists, but their canonical content remains a hard-coded demo
  catalogue.
- There is no authenticated universal create/edit workflow, draft autosave,
  ownership invitation, transfer, field-level privacy editor, lifecycle
  action panel, or pet-profile administration screen.
- Existing pages already provide a useful visual baseline and EN/LT/RU
  catalogues, but are not the required managed lifecycle.

## Current Authorization

`PetProfilePolicy` allows public viewing only for active/public profiles and
owner viewing for the single `user_id`. Create requires an active user.
Update/delete/restore require that same owner. Force deletion also requires an
administrator. There are no server-side abilities for co-owners, family,
sitters, shelters, foster carers, profile editors, evidence reviewers,
critical actions, disputes, transfers, or memorial managers.

## Current Integrations

- Lost/found, adoption, forum reports, and forum-event registrations have
  nullable foreign keys to `pet_profiles`.
- Medical records, care journals, device assignments/readings/events, and
  some presentation paths still use stable string `pet_profile_key` values.
- Search-case integrity already supports both the legacy key and a pet-profile
  foreign key.
- Global scientific taxonomy and domestic classification modules exist, but
  `pet_profiles.species` and `pet_profiles.breed` are not linked to them.
- Existing unified moderation can target a pet profile, but no complete
  pet-specific ownership-fraud and lifecycle action surface exists.

## Existing Safety Positives

- `profile_data` is encrypted and hidden from serialization.
- Lost/found exact locations and contact relays already have stronger private
  boundaries than the current public pet preview.
- Medical, care, device, adoption, taxonomy, moderation, and event modules
  already provide reusable policies and domain actions.
- The application uses class-based Livewire 4, Eloquent, policies, factories,
  EN/LT/RU translations, SQLite-compatible migrations, and serial tests.

## Deployment Constraints

The application must remain functional with the existing synchronous local
and shared-host deployment. Critical profile operations cannot require a new
queue worker, websocket server, external search engine, image-recognition
provider, phone-masking provider, or scheduler. Timestamp-derived expiry and
safe request-time enforcement remain valid; optional external services need
feature flags and fallbacks.

## Preservation Baseline

The migration strategy must preserve the two current pet rows and every
existing string-key or foreign-key relation. `user_id`, `profile_key`, legacy
species/breed strings, and encrypted `profile_data` remain compatibility
fields during the expand phase. No title-, photo-, or name-only automatic
merge is permitted.
