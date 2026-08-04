# Pet Profile Master Plan

Date: 2026-07-31

## Contract

This plan is additive to `docs/plans/forum-master-plan.md`. The generated phase
index assigns all 4,135 `pet.*` requirements to phases 17-25. Exact individual
IDs and source coordinates live in
`docs/plans/forum-phase-requirement-index.md`; the domain ranges below are
compact indexes, not generic completion checkboxes. No range is verified until
each record in it has independent file- or test-level evidence.

Every phase starts by rereading the source, master requirements, matrix,
progress, decisions, current repository state, and uncommitted changes. Its
work-package plan must name exact selected IDs and all schema, data,
rollback, compatibility, authorization, validation, translation, interface,
accessibility, cache, security, privacy, abuse, test, documentation,
acceptance, verification, and evidence effects.

## Control Phase 15: Preserve And Atomize Revision

**Control IDs:** `forum.feature.2561`, `forum.feature.2562`,
`forum.plan.0032`, `forum.plan.0033`.

Preserve the exact dated revision, verify its checksum, append it without
altering prior payloads, generate stable atomic requirements, and prove the
first 7,284 IDs remain unchanged. No schema or runtime behavior changes.

## Control Phase 16: Audit And Domain Design

**Coverage:** all 4,135 pet requirements are analyzed by source coordinate;
implementation remains assigned to phases 17-25.

Document current schema, data, ownership, privacy, routes, presentation,
integrations, moderation, taxonomy, translations, cache/search, tests, and
deployment constraints. Record additive architecture, assumptions, conflicts,
data migration, rollback, abuse, accessibility, and compatibility decisions.

## Phase 17: Canonical Aggregate And Release Boundary

**IDs:** `pet.profile.0001-pet.profile.0210` and
`pet.release.0001-pet.release.0068` (278 records).

Establish one stable pet identity, explicit human/pet separation, species-
neutral core, habitat boundary, creator semantics, lifecycle states, and the
first stable release contract. Preserve all IDs and links. Rollback removes
only unreferenced additive structures.

## Phase 18: Creation, Identity Facts, And Audit Data

**IDs:** `pet.creation.0001-pet.creation.0186`,
`pet.identity.0001-pet.identity.0351`, and
`pet.data.0001-pet.data.0308` (845 records).

Implement private minimal drafts, resumable/idempotent creation, bounded
duplicate suggestions, claims, names/aliases, taxonomy/breed confidence,
birth precision/derived age, appearance/measurements, critical fact
provenance, conflict review, immutable audit, and consistent counters.

The 2026-08-03 creation-interface refinement makes
`/pets/manage/new` the single canonical entry point and limits the first save
to name, broad species, relationship, and intended audience. Advanced facts
remain in the profile workspace. `pet.creation.0025` (optional primary photo)
is implemented by the focused private-file placement, processing,
replacement, removal, and recovery package;
`pet.creation.0036-pet.creation.0058` is implemented by the focused
twelve-step progressive completion workspace rather than being folded back
into the first screen. Its final verified state remains conditional on the
package gates recorded in the current-progress document. The subsequent
`pet.creation.0071-pet.creation.0081` draft-autosave slice reuses the same
partial-update Action with a locked, success-rotated request key and native
change-triggered Livewire calls; its release gates and dedicated atomic
traceability evidence are verified. Its reconnect extension retains only a
numeric form revision in page memory, automatically retries a pending active
step after `online`, and clears pending state only after a revision-matched
server confirmation; private profile values are never queued in browser
storage.

The bounded, policy-visible duplicate review and encrypted access-request
package for `pet.creation.0103-pet.creation.0137` is verified for 34 selected
IDs. Ordinary approval creates the existing manager invitation and still
requires requester acceptance; ownership transfer is recordable but cannot
pass through the generic review action. The managing organization projection
in `pet.creation.0115` remains open until a pet manager has an authoritative
organization relationship. Exact boundaries and evidence are in
`docs/plans/pet-profile-duplicate-access-work-package.md`.

The honest incomplete-species package completes
`pet.creation.0170-pet.creation.0186` with a typed confidence value separate
from the normalized broad species. Possible cat/dog remains explicitly
unconfirmed in public and manager projections, while unknown stays unknown.
It does not claim found-animal coordination, taxonomy verification, adoption,
or ownership transfer. Evidence is in
`docs/plans/pet-profile-species-confidence-work-package.md`.

The pet-name identity package implements the current-name, typed alternative,
rename-history, per-name visibility, and manager-workspace search boundary for
20 selected `pet.identity.0003-pet.identity.0040` records. It preserves the
stable profile identity and all adjacent links. Cross-domain name propagation
requirements `pet.identity.0005` and `pet.identity.0011`, public/global alias
discovery, merge, ownership, and taxonomy remain open. Exact evidence is in
`docs/plans/pet-profile-name-identity-work-package.md`.

The birth-precision package implements 17 selected
`pet.identity.0160-pet.identity.0186` records with typed exact, estimated,
month, year, age-estimate, unknown, and optional celebration-day states. One
server normalizer serves every mutation path, and one calculator derives an
advancing point or range for localized projection and conservative event
eligibility. Life-stage, medical verification, breed/taxonomy provenance,
ownership, and found-animal behavior remain open. Exact evidence is in
`docs/plans/pet-profile-birth-precision-work-package.md`.

The breed-origin package implements 35 selected
`pet.identity.0072-pet.identity.0122` records with an explicit overall type,
up to four normalized origins, per-origin confidence and source, and optional
mixed percentages. One server normalizer and one bounded synchronizer serve
every mutation path while the original breed field remains a compatibility
snapshot. Taxonomy verification/import, protected document evidence,
breed-based discovery, behavior, health, ownership, and found-animal effects
remain open. Exact evidence is in
`docs/plans/pet-profile-breed-origin-work-package.md`.

The life-stage package implements 11 selected records within
`pet.identity.0189-pet.identity.0204` with species-group-specific automatic
thresholds, conservative handling of uncertain ranges, and an attributed
authorized override. `pet.identity.0202` remains open because the package adds
no recommendation or discovery-filter consumption. Medical verification,
ownership, taxonomy import, and lost/found behavior also remain open. Exact
boundaries and evidence are in
`docs/plans/pet-profile-life-stage-work-package.md`.

The appearance-color package implements 12 selected records within
`pet.identity.0207-pet.identity.0221` with one controlled primary color, up to
four unique additional colors, spots/stripes/gradient patterns, and bounded
species-neutral, feather, scale, and seasonal clarification. The structured
payload remains on the existing encrypted compatibility field and adds no
query or migration. Automatic lost/found description generation
`pet.identity.0223`, coat, private marks, measurements, and identity media
remain open. Exact boundaries and evidence are in
`docs/plans/pet-profile-appearance-color-work-package.md`.

## Phase 19: Ownership And Privacy

**IDs:** `pet.ownership.0001-pet.ownership.0393` and
`pet.privacy.0001-pet.privacy.0221` (614 records).

Implement timed manager roles, permission overrides, organization-safe
management, critical approvals, evidence, transfer/foster/dispute flows,
section privacy, discoverability, owner/location/routine/medical protection,
block safety, and cache/search invalidation. All direct actions are policy-
checked and transaction-safe.

## Phase 20: Public Profile, Behavior, Media, And Social Graph

**IDs:** `pet.behavior.0001-pet.behavior.0322`,
`pet.media.0001-pet.media.0182`,
`pet.public-profile.0001-pet.public-profile.0160`, and
`pet.social.0001-pet.social.0365` (1,029 records).

Implement safe public projections, canonical URLs/QR/contact relay,
contextual non-stigmatizing behavior, managed media and consent, pet
subscriptions/friendships/kinship, albums/tags/mentions, posts, audiences,
versions, achievements, and verified working/training statuses.

## Phase 21: Cross-Domain Integrations And Discovery

**IDs:** `pet.integration.0001-pet.integration.0286` and
`pet.discovery.0001-pet.discovery.0082` (368 records).

Link the canonical pet to medical, care, devices, lost/found, places, events,
groups, marketplace, experts, adoption, documents, and insurance without
copying private data. Add transparent opt-out recommendations and never claim
behavioral, medical, or legal certainty.

## Phase 22: Full Lifecycle And Memorial

**IDs:** `pet.lifecycle.0001-pet.lifecycle.0272` (272 records).

Implement timeline/stages, reminder consent, hide/archive/delete cooling-off,
content disposition, transfer continuity, memorial activation, reminder and
commercial suppression, trusted memorial management, export, restore, and
auditable recovery.

## Phase 23: Pet Safety And Moderation

**IDs:** `pet.moderation.0001-pet.moderation.0192` (192 records).

Extend unified reports/cases/actions for fake profiles, ownership theft,
stolen media, cruelty, restricted species, unsafe sales, evidence retention,
proportionate freezes, reasons, appeals, restoration, and least-privilege
review. Automated signals never prove guilt or ownership.

## Phase 24: Localization And Accessible Interfaces

**IDs:** `pet.translation.0001-pet.translation.0063` and
`pet.interface.0001-pet.interface.0180` (243 records).

Use stable data identifiers and EN/LT/RU platform text, preserve original
user content and exact identifiers, and deliver keyboard/screen-reader/mobile/
zoom/offline-safe class-based Livewire workflows. No full taxonomy tree or
private aggregate enters public Livewire state.

## Phase 25: Quality Metrics And Scenario Verification

**IDs:** `pet.quality.0001-pet.quality.0088` and
`pet.scenario.0001-pet.scenario.0206` (294 records).

Add privacy-safe aggregate metrics and executable scenario tests for creation,
multiple pets, co-owners, shelter/foster/adoption, found/duplicate/rename/lost,
dispute, professional status, sitter expiry, closed profiles, and memorial.
Run full migrations, repeat seed, tests, static analysis, formatting, build,
query budgets, security/privacy checks, and real browser verification before
final traceability claims.
