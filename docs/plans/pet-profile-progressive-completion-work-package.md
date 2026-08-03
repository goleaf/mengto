# Pet Profile Progressive Completion Work Package

Last updated: 2026-08-03.

Status: implemented and verified for `pet.creation.0036-pet.creation.0058`.

## Scope

This package implements the exact progressive-completion slice
`pet.creation.0036-pet.creation.0058` after the verified minimal pet creation
and optional-primary-photo packages. It does not claim the later draft,
autosave, ownership-proof, transfer, gallery, medical-document, or full pet
media requirements.

## Product Contract

The authenticated pet-management workspace exposes twelve centrally placed,
URL-addressable steps in the canonical source order:

1. basics;
2. photos;
3. age and sex;
4. breed or origin;
5. appearance;
6. character;
7. social preferences;
8. location;
9. owners and co-owners;
10. privacy;
11. documents and microchip;
12. preview.

Only the active step body is rendered. Every step explains why its values are
requested, can be opened again from a stable query-string value, and can be
skipped without a database mutation. A skipped or optional step is not treated
as a defect and no percentage score pressures a person to disclose private
information.

## Persistence And Operations

- Existing profile columns retain name, broad species, taxonomy, breed, birth
  details, sex, and reproductive status.
- Ordinary descriptive values are stored only in the existing encrypted
  `pet_profiles.profile_data` allowlist: appearance summary, identifying marks,
  temperament summary, story, social preferences, meeting preferences, broad
  location label, and location precision.
- Basics, age/sex, origin, appearance, character, social preferences, and
  location each use a partial-update Action. The Action explicitly maps the
  fields permitted for that step, locks the profile row, checks
  `lock_version`, records one idempotent lifecycle event and audit entry, and
  invalidates the existing profile cache.
- Photos, manager access, privacy, lifecycle transitions, and stable preview
  keep using their already-authorized Actions.
- Documents/microchip stores a single encrypted, versioned
  `microchip-record` fact. It records only readiness, status, and an optional
  identifier; private files remain the responsibility of the dedicated
  medical/document domain. The `change-microchip` policy boundary protects the
  operation.
- No completion table is added. Step state is a presentation derived from the
  already-saved values and bounded relationship-existence subqueries. This
  avoids duplicated state, migrations, backfills, and misleading completion
  claims.

## Data Minimization And Privacy

- Location accepts a broad human-readable label and controlled precision, not
  an exact address or coordinates.
- Social preferences are descriptive and must not be presented as a medical
  diagnosis, safety guarantee, or compatibility guarantee.
- Microchip values remain encrypted, private, hidden from model serialization,
  absent from public profile projections, and absent from logs and rendered
  navigation.
- The public profile remains governed by its existing privacy projection; this
  package does not make newly entered values public by itself.

## Query Contract

The profile row uses explicit selected columns and relationship-existence
subqueries for navigation state. One narrow current-manager projection is
reused for request policy checks. Only relations required by the active step
are eager loaded: media for photos, managers and users for owners, privacy
settings for privacy, the current microchip fact for documents, and recent
lifecycle events for preview. Query count must remain bounded as unrelated
manager, event, media, and fact history grows.

## Interface And Accessibility

- The twelve-step navigator lives in the main content column, not in a second
  left sidebar.
- Mobile uses a contained horizontal scroll-snap row so the active form remains
  near the navigator; tablet and desktop retain the complete grid.
- Navigation uses real links and `aria-current="step"`; all controls keep a
  minimum 44-pixel target and visible focus.
- Each form provides associated labels, help, linked error text, loading,
  dirty, offline, success, and validation states.
- The layout is mobile-first, does not require horizontal page scrolling, and
  remains usable with keyboard, reduced motion, forced colors, and 200% zoom.
- All first-party text has matching `en`, `lt`, and `ru` translations.

## Acceptance Evidence

- Focused progressive: 6 tests and 71 assertions.
- Canonical create/legacy redirect plus pet foundation, media, and progressive:
  33 tests and 2,601 assertions.
- Pet plus architecture/localization/responsive/page-identity checkpoint: 107
  tests and 64,205 assertions against final generated evidence.
- Final serial repository suite: 2,657 tests and 84,589 assertions in 166.963
  seconds.
- Full Pint and Larastan, Composer strict validation/audit, zero-vulnerability
  npm audit, and production Vite build passed.
- Isolated SQLite applied 130 migrations across 215 tables, completely rolled
  back and reapplied all migrations, and retained five users on repeat seed.
- Isolated config/event/route/view caches, source checksum, and deterministic
  38,377-requirement generation passed.
- Connected Chrome verified desktop, mobile, and 320px pet create/manage/public
  layouts, keyboard focus, touch targets, alternatives, identifiers, raw keys,
  page overflow, and console output; all audited defect counts were zero.

## Explicit Non-Goals

- silent field-level autosave;
- a numeric profile-completion score;
- document upload or medical-record duplication;
- exact-location collection;
- ownership proof or primary-owner transfer;
- permanent media deletion or full gallery management;
- marking requirements outside `pet.creation.0036-pet.creation.0058` verified.
