# Places Production Readiness Audit

Date: 2026-08-03
Status: factual repository audit; implementation work remains open
Scope: authenticated `/places`, `/places/{place}`, place creation, personal
place state, community contributions, management, venue/event integration,
privacy, accessibility, testing, and release operations

## Executive Verdict

The Places module is no longer only a visual mock-up. It has a verified
authority foundation: persisted `Place` and `Venue` identities, policy-scoped
visibility, encrypted exact location, scoped grants, reveal audits, event
links, factories, seeders, dynamic detail routes, and a server-rendered
directory.

It is not yet a production-complete Places system. Most descriptive content is
still overlaid from a twelve-record PHP fixture catalogue, most directory
filtering and pagination happens after loading a bounded in-memory collection,
and the apparent shared community workflows are stored inside each user's
private `UserDomainState`. A review, question, warning, correction, claim, or
report submitted by one user is therefore not a durable shared record for the
place manager, moderator, or another member. Newly persisted places also fail
the shared action request's hard-coded target allow-list.

The authority package remains valid, verified foundation evidence. Product
completion is reopened and must follow
`docs/plans/places-production-master-plan.md`.

Implementation update (2026-08-03): the static twelve-target request allow-list
has been removed in favor of bounded identifiers plus accessible Eloquent
resolution. Questions and one official answer now use relational,
actor-attributed, idempotent records with manager policy enforcement and a
two-account feature test. The original audit remains the baseline for all
other open findings; no claim is made here for moderation, notification,
version history, or the remaining contribution types.

## Audit Method

The audit followed the repository reading order and compared the current code
with:

- `PRODUCT.md`, `DESIGN.md`, and the canonical product, system, and
  non-functional requirements;
- architecture, domain, data, security, authorization, privacy, performance,
  caching, integration, frontend, Livewire, Tailwind, accessibility,
  localization, testing, seeding, and deployment contracts;
- `docs/superpowers/specs/2026-07-30-places-map-mvp-design.md`;
- `docs/plans/portal-place-location-venue-authority-work-package.md`;
- the current migrations, models, policies, Actions, requests, services,
  controllers, Blade components, JavaScript, factories, seeders, and Places
  tests.

This audit did not infer completion from visible controls or successful flash
messages. A capability counts as complete only when its identity,
authorization, persistence, lifecycle, cross-account behavior, presentation,
tests, and operational boundary are coherent.

## Current System Map

| Surface | Current authority | What works | Production gap |
| --- | --- | --- | --- |
| Place identity | `App\Models\Place` | Stable key, slug, owner/organization authority, visibility, lifecycle, public and encrypted exact fields | Rich facts still depend on fixture overlays; slug history is absent |
| Venue identity | `App\Models\Venue` | Place-scoped areas and event association | Management UI, capacities, accessibility detail, lifecycle, and conflict handling remain open |
| Exact location | encrypted place fields, grants, audits | Owner/scoped recipient reveal with purpose and audit | No end-user grant/revoke/reveal management flow or notification contract |
| Directory | `PlaceCatalog` + `PlacePresenter` | Server HTML, search/filter/sort controls, manual area, list/split/map modes, pagination links | Loads at most 500 rows, then filters/sorts/slices in PHP; not a complete scalable query |
| Detail | presenter + fixture content catalogue | Rich tabs and operational forms | Many facts are static/default projections rather than normalized place-owned records |
| Personal state | `PlaceState` in encrypted `UserDomainState` | Saves, follows, collections, visits, recent history, generalized location, private check-ins | Uses string keys and demo pet/recipient/collection identifiers; social visibility is not actually delivered |
| Community contributions | also `PlaceState` | Forms validate and show a result to the submitting account | Contributions are account-local, capped arrays with no shared moderation or manager visibility |
| Place creation | `CreatePlace` Action | Persists an unlisted place and supports canonical authority | Shared form ignores several accepted fields; duplicate and idempotency semantics are incomplete |
| Place management | `PlacePolicy` + owner/organization checks | Authority can be evaluated | No complete edit, archive, verification renewal, media, claim-review, or audit workspace |
| Events | canonical place/venue foreign keys | Event place selection and scoped exact access are verified | Venue operations, selected organization context, invitations, and delivery remain incomplete |
| Media | fixture URLs | Responsive image variants render | Remote Unsplash dependencies are not first-party seed fixtures or an owned media lifecycle |
| Emergency mode | presenter ranking | Call-first copy and deterministic seeded clinic examples exist | Open/species eligibility is not computed from canonical schedules and exception data |

## Verified Foundation To Preserve

- Active verified member boundary for the portal.
- Eloquent place and venue identity with explicit selected public columns.
- Public, organization, owner, and grant-scoped visibility rules.
- Encrypted exact address, coordinates, and access instructions.
- Purpose-bound expiring exact-location grants and reveal audits.
- Place location-version history and canonical event/venue linkage.
- Factories and local/demo/testing-only repeat-safe seed integration.
- Server-rendered directory and stable detail route without map JavaScript.
- EN/LT/RU presentation coverage for the currently rendered surface.
- Focused authority, directory, query-growth, fresh migration, repeat seed, and
  browser evidence already recorded by the authority work package.

These facts must not be discarded while replacing prototype data paths.

## P0 Correctness And Trust Findings

### PLA-AUD-001 — Dynamic places cannot use normal actions

`PerformActionRequest` applies `Rule::in()` with twelve fixture slugs to every
place-target action. `PlaceCatalog::find()` can resolve a newly persisted place,
but validation rejects that stable key before `PerformPlaceAction` runs. The
detail route is dynamic while mutation validation is not.

Required outcome: validate a syntactically bounded canonical identifier, bind
or resolve the place through an authorized Eloquent query, and test every
action against a newly created place plus an inaccessible place.

### PLA-AUD-002 — Shared contributions are private account-local arrays

`PlaceState` stores corrections, warnings, warning confirmations/resolutions,
reviews, questions/answers, claims, reports, and presentation history under
`places.state.v1` in `UserDomainState`. The submitter can see their own array;
the manager and another member cannot see or act on it.

Required outcome: dedicated relational records with actor identity, place ID,
status, timestamps, evidence/provenance, moderation state, policy scope,
idempotency where applicable, and cross-account tests. Keep only genuinely
private preferences in encrypted user state.

### PLA-AUD-003 — Rendered actor identity is hard-coded

`PlaceState::addReview()` and `PlaceState::addQuestion()` render Mia Carter/MC
for non-anonymous submissions instead of deriving the authenticated social
actor. This produces incorrect authorship for every other account.

Required outcome: use server-authoritative user/social actor identity; snapshot
only the display fields deliberately required for history, and test rename and
deleted-account behavior.

### PLA-AUD-004 — Success copy overstates durable behavior

Current responses say a question was sent to the community, a claim entered
verification, and a report entered the moderation queue. The underlying data
does not cross the current account boundary or reach a shared queue.

Required outcome: either remove/disable the claim until the relational path is
live or implement the claimed destination atomically before retaining the
copy. Tests must assert the receiving account or moderation queue can observe
the record.

### PLA-AUD-005 — Demo identities remain mutation inputs

The shared request and dashboard expose `scout`, `nori`, three fixed invitation
recipients, and fixed collection keys. They are not resolved from pets,
relationships, blocks, privacy, or collections managed by the authenticated
user.

Required outcome: build options from authorized canonical IDs, reject foreign
or stale IDs server-side, and never accept a display slug as authority.

## Data And Domain Findings

### PLA-AUD-006 — Static catalogue remains the rich-content source

`PlaceCatalog::records()` contains the twelve detailed records in application
code. Eloquent currently overrides identity and selected authority fields, but
hours, services, rules, prices, features, warnings, ratings, images, map facts,
routes, event keys, and recommendation signals still commonly come from the
fixture.

Required outcome: normalize durable facts into cohesive models and explicit
value objects, retain provenance/freshness, and reduce the fixture catalogue to
environment-gated seed input or remove it.

### PLA-AUD-007 — New places receive generic facts

`PlaceCatalog::defaultRecord()` substitutes Vilnius coordinates, unknown hours,
empty services, zero rating, generic pet support, a remote image, and default
safety statements. A new place is technically routable but not editorially or
operationally complete.

Required outcome: distinguish unknown from confirmed data, never invent
coordinates/species/rules, and provide a manager/community completion workflow.

### PLA-AUD-008 — Hours are not a canonical schedule

Open state and emergency ranking depend on fixture labels rather than weekly
intervals, timezone, holiday exceptions, temporary closures, overnight
intervals, and freshness.

Required outcome: one canonical schedule model and service used by list,
detail, emergency mode, filters, and structured presentation.

### PLA-AUD-009 — Ratings are not shared aggregates

Fixture ratings and account-local reviews cannot produce a trustworthy rating
or verified-review count.

Required outcome: eligible relational reviews plus aggregate subqueries or
maintained counters with transactional/invalidation tests.

### PLA-AUD-010 — Stable route identity is incomplete

The route accepts stable key or slug, but there is no explicit slug alias and
redirect history for renamed places.

Required outcome: decide canonical URL identity, preserve stable redirects,
prevent alias reuse, and update internal links without exposing private place
existence.

## Application Boundary Findings

### PLA-AUD-011 — One shared dispatcher owns unrelated operations

`PerformPlaceAction` dispatches personal preferences, geolocation, social
invitations, contributions, claims, reports, and creation. Only creation
delegates to a focused production Action.

Required outcome: dedicated Form Requests or Livewire form objects and one
application Action per meaningful transition, each with explicit policy checks
and transactional scope.

### PLA-AUD-012 — Preview controllers and raw route strings remain

The production routes still use controllers named `*PreviewController`, accept
a raw string, and ask the presenter to resolve a record. The list request
authorizes unconditionally and relies on outer middleware.

Required outcome: production names, explicit `viewAny`/`view` authorization,
scoped route-model binding or a privacy-safe resolver, and tests for archived,
private, foreign-organization, invalid, and alias routes.

### PLA-AUD-013 — Place creation does not honor its full form contract

The request accepts coordinates, hours, features, source, and relationship
fields, but the Action path does not persist most of them. Its deterministic
hash from actor/name/address/category is not an explicit request idempotency
contract, and duplicate detection compares only normalized name or address.

Required outcome: a dedicated create data object, explicit operation key,
field-specific validation, privacy-aware address rules, richer duplicate
candidates, and a review state that can safely merge submissions.

### PLA-AUD-014 — Policy vocabulary is too small

`PlacePolicy` covers view, create, update, access management, event use, and
exact reveal. It does not yet express archive/restore, verification, claim
review, contribution moderation, question answering, review response, media,
venue management, or audit access.

Required outcome: capability-specific policy methods used at every mutation
boundary and verified with allow/deny matrices.

## Query, Performance, And Cache Findings

### PLA-AUD-015 — Directory pagination occurs after a 500-row cap

`PlaceCatalog` loads at most 500 accessible rows. `PlacePresenter` then filters,
sorts, calculates, and `array_slice()` paginates. Results after row 500 are
silently absent, and filters cannot use database indexes.

Required outcome: a reusable Eloquent directory query, database-level search,
filters, stable ordering, pagination, selected columns, aggregate subqueries,
and SQLite/production-adapter portability.

### PLA-AUD-016 — Query checks do not prove scaled latency

Current tests cap statement growth for modest fixtures, which is valuable, but
do not prove index selection, result completeness beyond 500 rows, stable
cursor/offset behavior under writes, or representative latency.

Required outcome: deterministic volume fixtures, explain-plan evidence for
important filters/sorts, query/time/memory budgets, and pagination consistency
tests.

### PLA-AUD-017 — Cache ownership is not yet specified

The request-local catalogue reuse protects query count, but production cache
keys, owner, locale/user scope, TTL, invalidation triggers, stale behavior, and
private-data exclusions are not defined for place facts or aggregates.

Required outcome: cache only measured stable projections and document/test all
invalidation and failure behavior before adding shared caches.

## Privacy, Safety, And Moderation Findings

### PLA-AUD-018 — Check-in visibility exceeds delivered semantics

The form offers visibility modes, but the current check-in is stored only in
the checking-in user's encrypted state. There is no proven friend/close-circle
projection or anonymous aggregate delivery.

Required outcome: keep check-ins explicitly private until recipient scoping,
blocking, expiry, revocation, and anti-stalking behavior are implemented and
tested; do not imply live presence.

### PLA-AUD-019 — Warning lifecycle is not a shared safety system

Confirmations and resolutions are user-local, have no unique actor constraint,
and lack shared moderation, evidence access, expiry jobs, dispute state, false
report handling, or notification rules.

Required outcome: a relational warning lifecycle with bounded expiry and
moderation, plus emergency-safe presentation that never creates false certainty.

### PLA-AUD-020 — Claims cannot establish authority

A claim is stored in the claimant's private state and cannot be reviewed into a
manager/organization relationship. Evidence encryption, reviewer independence,
conflict handling, revocation, and audit are absent.

Required outcome: a dedicated claim aggregate and audited approval Action that
changes authority only after successful verification.

### PLA-AUD-021 — Reports do not enter unified moderation

Place reports are not integrated with a shared moderation case/queue and do not
prove reporter privacy or evidence retention.

Required outcome: reuse the canonical moderation boundary, keep reporter
identity scoped, and test triage, assignment, resolution, appeal, and audit.

### PLA-AUD-022 — Generalized location needs an explicit retention contract

The current rounded coordinates are encrypted per user and do not appear in the
URL, which is a useful base. Precision, retention, clearing, logs, analytics,
cache exclusion, and failure fallback still need a normative contract.

Required outcome: document and test data minimization; manual area search must
always remain usable.

## Presentation And Operational Findings

### PLA-AUD-023 — Remote fixture images are an availability dependency

Place cards and galleries use Unsplash URLs from PHP catalogues. Seed and test
fixtures therefore do not own the image lifecycle and low-bandwidth/offline
behavior is not deterministic.

Required outcome: first-party local fixtures for demo data and a private/public
media processing boundary for uploaded place media, with attribution and
moderation rules.

### PLA-AUD-024 — Responsive evidence is narrower than the product matrix

The connected browser gate covers 1440px and 375px Places surfaces and now
guards card height. The specification also names 320, 360, 390, 430, 768,
1024, 1280, and 1728 widths, keyboard-only operation, 200% zoom, reduced
motion, forced colors, and low-bandwidth behavior.

Required outcome: representative automated and manual evidence for the complete
matrix, including every interactive workflow and listener cleanup after
repeated navigation.

### PLA-AUD-025 — No complete manager/moderator workspace exists

There is no operational surface for editing facts, handling duplicates,
reviewing claims/corrections/warnings/reports, managing media/venues/grants,
renewing verification, archiving, or inspecting audit history.

Required outcome: authorized Blade/Livewire workspaces with empty/loading/error
states, server-authoritative transitions, and notification delivery that does
not depend silently on unavailable queues.

### PLA-AUD-026 — SEO and public indexing need an explicit decision

Places live inside the authenticated portal. A prior follow-on note mentions
sitemap/metadata work, but protected routes must not be exposed as public
product pages or leak private existence.

Required outcome: define noindex/canonical/metadata behavior for authenticated
pages and exclude protected URLs from public sitemaps unless product scope
changes deliberately.

## Requirement Status Reassessment

| Requirement | Audit status | Reason |
| --- | --- | --- |
| PRD-PLACE-001 | Partially implemented | Server-rendered discovery works, but the 500-row pre-load cap means the list is not complete at scale and query filters/pagination are in memory |
| PRD-PLACE-002 | Partially implemented | Detail presentation and relational cross-account questions/official answers exist; other community contributions and moderation history remain account-local or incomplete |
| PRD-PLACE-003 | Partially implemented | Call-first presentation exists, but open/species ranking is not backed by canonical schedule and service facts for all clinics |

This reassessment does not invalidate the verified location/venue authority
foundation. It narrows the claim to what the evidence proves.

## Decisions Required Before Irreversible Schema Work

1. Whether private saves/follows/collections remain encrypted compatibility
   state for the first production release or migrate immediately to relational
   tables.
2. Whether shared/collaborative collections and visible check-ins remain MVP or
   move to post-MVP until the canonical social graph is fully integrated.
3. Whether one review is allowed per account/place, per verified visit, or per
   bounded period, and how edits affect history.
4. Which claim verification methods are acceptable and which reviewer roles
   may approve or revoke authority.
5. Which warning categories may publish immediately, which require moderation,
   and their expiry/renewal rules.
6. Canonical route identity: stable key, immutable public slug, or slug alias
   history backed by a stable internal ID.
7. Place category taxonomy ownership and how multi-category places are ranked.
8. Whether public opening hours may be community-sourced and how freshness is
   represented after expiry.
9. The first release's supported media types, moderation path, retention, and
   attribution requirements.
10. Whether provider-backed directions, weather, water, tick, appointments,
    and transit remain explicitly post-MVP.

## Recommended Critical Path

1. Add red cross-account and dynamic-place action tests.
2. Remove static target validation and establish authorized canonical binding.
3. Introduce relational contributions/moderation foundations and migrate no
   private state until a compatibility/backfill plan is proven.
4. Split the shared dispatcher into focused requests and Actions with policies.
5. Move the directory to an Eloquent query with database filtering and
   pagination.
6. Normalize hours, categories, services, rules, accessibility, provenance,
   media, and rating facts.
7. Deliver creation, management, claims, corrections, warnings, reviews,
   questions, reports, and personal planning as end-to-end workflows.
8. Complete emergency, venue/event, responsive/accessibility, performance,
   seed, browser, deployment, and documentation gates.

The complete dependency-ordered task ledger and exit gates are maintained in
`docs/plans/places-production-master-plan.md`.
