# Places Production Completion Master Plan

Plan version: 2026-08-03
Status: implementation in progress. Nine task IDs are complete and 339 remain
open; the verified place/location/venue authority foundation does not close
the Places contribution, scale, management, safety, or release packages.
Goal: turn `/places` from a strong authority-backed prototype into a complete,
shared, privacy-aware, production-operable Places system without regressing the
verified place/venue authority foundation

## Plan Contract

This is the living canonical completion plan for Places. It supersedes the idea
that the verified authority work package completed the whole product surface;
it does not supersede that package's implementation evidence.

The task ledger has no artificial item limit. New findings receive a stable
task ID in the relevant package, dependencies, acceptance evidence, and a
conservative status. Tasks are never deleted merely to make progress appear
complete. A superseded task remains visible with its replacement.

Status vocabulary:

- `[ ]` not started or not currently proven;
- `[~]` implementation exists but the package gate is not proven;
- `[x]` implemented and verified with recorded evidence;
- `[!]` blocked by an explicit product, security, provider, or environment
  decision.

The initial ledger keeps production implementation tasks open. Completed
planning-control tasks in PLA-P00 point to current documentation evidence;
existing authority evidence is listed separately and must be preserved.

## Source And Conflict Order

1. `AGENTS.md` and any future nested repository contract.
2. Canonical product, system, non-functional, security, privacy, authorization,
   data, accessibility, localization, testing, seeding, and deployment
   requirements.
3. `docs/superpowers/specs/2026-07-30-places-map-mvp-design.md` where it does not
   conflict with canonical requirements.
4. `docs/audits/places-production-readiness-audit.md` as the dated factual
   baseline.
5. This plan and its future approved decisions.
6. Verified tests that accurately encode intended behavior.
7. Current implementation and historical prototype plans.

## Preserved Foundation

The following are implemented foundation, not open tasks to rewrite:

- Eloquent `Place` and `Venue` identity;
- owner and organization authority;
- public/unlisted/private/archived visibility semantics;
- encrypted exact address, coordinates, and access instructions;
- scoped, expiring exact-location grants and reveal audits;
- place location-version history;
- canonical event-to-place and event-to-venue links;
- factories, environment-safe seed integration, dynamic details, and the
  current authenticated server-rendered surface.

Every package must extend these boundaries through additive migrations and
compatible transitions.

## Release Scope

### Required for production-complete MVP

- complete server-rendered directory and detail behavior without geolocation
  or map JavaScript;
- canonical categories, contact facts, hours, services, species/size rules,
  accessibility, safety, source, verification, freshness, and media;
- working creation, duplicate review, management, claim, correction, warning,
  review, question/answer, and reporting workflows;
- truthful private saves, follows, visits, collections, check-ins, and
  invitations within the explicitly delivered privacy scope;
- emergency vet ranking from canonical schedule and capability facts with an
  unconditional call-first safety message;
- venue/event integration, authorization, localization, accessibility,
  performance, observability, migration, seeding, and deployment gates.

### Explicitly post-MVP unless a new decision promotes them

- external appointment booking or payment;
- venue rental transactions and live inventory;
- live crowd density, friend-presence tracking, or continuous GPS;
- offline map tiles and turn-by-turn provider navigation;
- provider weather, water quality, tick, transit, or official registry feeds;
- automatic pet-profile suitability filters without explicit member input;
- real-time collaborative collection editing;
- external business-verification automation and analytics dashboards;
- sitter, hotel, memorial, or other new category verticals.

Provider-dependent work must never block a truthful provider-free core.

## Dependency Map

```text
PLA-P00 truth/evidence
  -> PLA-P01 red tests + immediate correctness
  -> PLA-P02 relational foundations
      -> PLA-P03 application/authorization boundaries
          -> PLA-P04 directory query
          -> PLA-P05 canonical facts
          -> PLA-P06 creation/duplicates
          -> PLA-P07 management/claims
          -> PLA-P08 corrections
          -> PLA-P09 warnings
          -> PLA-P10 reviews
          -> PLA-P11 questions/reports
          -> PLA-P12 personal/social planning
          -> PLA-P13 media
          -> PLA-P14 location/map/routes
          -> PLA-P15 emergency
          -> PLA-P16 venues/events/access
              -> PLA-P17 presentation/localization/accessibility
              -> PLA-P18 performance/cache/security/observability
                  -> PLA-P19 migration/seeding/backfill
                      -> PLA-P20 automated/browser verification
                          -> PLA-P21 release/documentation

PLA-P22 is a post-MVP provider backlog and cannot silently enter the critical
path.
```

Packages may overlap only when their migrations, owned files, and acceptance
tests do not conflict. Database-backed suites using the shared SQLite file run
sequentially.

## PLA-P00 — Truthful Baseline And Execution Control

Dependencies: none
Exit gate: the plan, current-state ledger, requirement statuses, and critical
path agree with observable code and tests

- [x] `PLA-00-001` Add a Places current-progress ledger with current package,
  last verified commit, checks, blockers, and next exact task.
- [x] `PLA-00-002` Record the authority work package as verified foundation,
  not whole-product completion.
- [x] `PLA-00-003` Reconcile PRD-PLACE-001 through PRD-PLACE-003 status with the
  readiness audit.
- [ ] `PLA-00-004` Map every Places MVP statement to a package and acceptance
  test; retain post-MVP statements separately.
- [ ] `PLA-00-005` Inventory every Places route, controller, request, Action,
  policy method, model, service, view, component, JavaScript entry, migration,
  factory, seeder, test, and documentation owner.
- [ ] `PLA-00-006` Inventory every place-related field currently supplied by
  `PlaceCatalog::records()` and assign a canonical owner or explicit removal.
- [ ] `PLA-00-007` Inventory every key in `places.state.v1` and classify it as
  private preference, shared domain record, ephemeral state, or migration-only
  compatibility state.
- [ ] `PLA-00-008` Record the current query counts, response sizes, memory,
  render timing, and browser behavior for list and detail as a comparison
  baseline.
- [ ] `PLA-00-009` Record a privacy data-flow diagram for exact location,
  generalized location, check-ins, claims, evidence, and reports.
- [ ] `PLA-00-010` Decide canonical URL identity and slug alias behavior before
  introducing editable slugs.
- [ ] `PLA-00-011` Decide first-release review eligibility and update rules.
- [ ] `PLA-00-012` Decide warning publication, confirmation, expiry, dispute,
  and moderation policy.
- [ ] `PLA-00-013` Decide claim verification methods, reviewer roles, evidence
  retention, and revocation policy.
- [ ] `PLA-00-014` Decide whether private saves/follows/collections migrate in
  MVP or remain encrypted compatibility state.
- [ ] `PLA-00-015` Decide whether non-private check-in visibility and shared
  collections remain MVP or become explicit post-MVP.
- [ ] `PLA-00-016` Define Places rollout flags, compatibility period, rollback
  points, and stop conditions.

## PLA-P01 — Red Tests And Immediate Correctness

Dependencies: PLA-P00 decisions needed by each test
Exit gate: known P0 defects have failing regression tests first, then pass
without weakening validation or authorization

- [x] `PLA-01-001` Add a test proving a newly persisted accessible place can be
  saved from its dynamic detail route.
- [ ] `PLA-01-002` Add dynamic-place tests for follow, collection, visit,
  check-in, invitation, correction, warning, review, question, claim, and
  report submission.
- [ ] `PLA-01-003` Add deny tests proving an inaccessible/private/archived
  dynamic place cannot be targeted by any action.
- [x] `PLA-01-004` Replace the twelve-slug request allow-list with bounded
  canonical identifier validation plus authorized server-side resolution.
- [x] `PLA-01-005` Ensure malformed, overlong, Unicode-confusable, and stale
  target identifiers fail with localized validation without disclosing private
  existence.
- [ ] `PLA-01-006` Add a two-account test proving a submitted review is not
  currently visible to another member; use it as the red relational contract.
- [x] `PLA-01-007` Add a member-to-manager question visibility/answer test.
- [ ] `PLA-01-008` Add a member-to-moderator warning/report visibility test.
- [ ] `PLA-01-009` Add a claimant-to-reviewer claim visibility/decision test.
- [ ] `PLA-01-010` Add tests proving displayed author identity comes from the
  authenticated actor rather than Mia Carter constants.
- [ ] `PLA-01-011` Remove or temporarily neutralize success messages that claim
  delivery to an unimplemented shared destination.
- [ ] `PLA-01-012` Add a source test that rejects demo-only actor, pet,
  recipient, or collection defaults in production mutation paths.
- [ ] `PLA-01-013` Add repeated-submission tests for every state-changing form
  before assigning final idempotency behavior.
- [ ] `PLA-01-014` Add CSRF, unauthenticated, inactive-account, and unverified-
  email rejection tests for all Places mutations.
- [ ] `PLA-01-015` Add a regression proving validation errors return to the
  correct detail tab with old input and no duplicate mutation.

## PLA-P02 — Relational Domain Foundations

Dependencies: PLA-P00 decisions, PLA-P01 red contracts
Exit gate: shared workflows have normalized, indexed, factory-backed models and
reversible migrations without changing production behavior prematurely

- [ ] `PLA-02-001` Design a place category taxonomy with stable keys,
  localization contracts, ordering, activation, and multi-category pivot.
- [ ] `PLA-02-002` Add place contact-point records for phone, email, website,
  booking link, purpose, verification, visibility, and ordering.
- [ ] `PLA-02-003` Add canonical weekly opening intervals with timezone and
  overnight support.
- [ ] `PLA-02-004` Add schedule exceptions for holidays, temporary closures,
  special openings, provenance, and verification expiry.
- [ ] `PLA-02-005` Add service definitions and place service offerings with
  species, size, price representation, availability, provenance, and status.
- [ ] `PLA-02-006` Add structured pet rules, facility facts, safety facts, and
  accessibility facts without inventing unknown values.
- [ ] `PLA-02-007` Add place source/provenance records with source kind,
  submitter, evidence reference, observed time, verified time, scope, and
  freshness state.
- [ ] `PLA-02-008` Add place correction records, field-specific proposals,
  source snapshot/version, evidence, review state, reviewer, and applied
  mutation link.
- [ ] `PLA-02-009` Add place warning records with category, bounded area,
  evidence, status, published/expiry/resolution times, author, and moderator.
- [ ] `PLA-02-010` Add unique warning confirmation records scoped to warning and
  actor.
- [ ] `PLA-02-011` Add warning dispute/report records and audited moderation
  transitions.
- [ ] `PLA-02-012` Add place review records with actor, optional managed pet,
  visit eligibility, criteria, rating, content, anonymity presentation mode,
  moderation status, and history.
- [ ] `PLA-02-013` Add one controlled owner/manager response aggregate per
  review with edit history.
- [ ] `PLA-02-014` Add place questions with author, status, moderation fields,
  and one versioned official answer.
- [ ] `PLA-02-015` Add place management claims with claimant, organization,
  requested role/scope, encrypted evidence/contact, verification method,
  reviewer, expiry, decision, and revocation.
- [ ] `PLA-02-016` Integrate place reports with the canonical moderation case
  boundary or document and implement an equivalent shared aggregate.
- [ ] `PLA-02-017` Add place duplicate candidates and merge decisions with
  survivor, alias/redirect, evidence, reviewer, and audit.
- [ ] `PLA-02-018` Add optional relational saved-place and followed-place
  records if PLA-00-014 chooses immediate migration.
- [ ] `PLA-02-019` Add relational visit/check-in/invitation/collection records
  only for semantics promoted into MVP by PLA-00-015.
- [ ] `PLA-02-020` Add place slug aliases if required by PLA-00-010.
- [ ] `PLA-02-021` Add all leading foreign-key, uniqueness, status/time, and
  frequently filtered composite indexes at migration time.
- [ ] `PLA-02-022` Add explicit enums and casts for every persisted lifecycle;
  do not use free-form status strings.
- [ ] `PLA-02-023` Define `$fillable`, hidden sensitive fields, relationships,
  scopes, and factories for every new first-party model.
- [ ] `PLA-02-024` Add schema-integrity tests for constraints, indexes,
  cascades/restrict behavior, encrypted fields, and money/time representations.
- [ ] `PLA-02-025` Prove every migration rolls back on SQLite and the supported
  production adapter without editing historical production migrations.

## PLA-P03 — Application, Validation, And Authorization Boundaries

Dependencies: PLA-P02
Exit gate: each meaningful mutation has one focused validated, authorized,
idempotent operation and routes contain no business logic

- [ ] `PLA-03-001` Replace preview controller names with production directory
  and detail controllers while preserving named-route compatibility.
- [ ] `PLA-03-002` Authorize `PlacePolicy::viewAny` explicitly in the directory
  request/controller path.
- [ ] `PLA-03-003` Introduce privacy-safe scoped route-model binding or a
  dedicated resolver for stable key, canonical slug, and aliases.
- [ ] `PLA-03-004` Authorize `PlacePolicy::view` before presenting detail data.
- [ ] `PLA-03-005` Split the shared Places branch out of
  `PerformActionRequest` into dedicated Form Requests or class-based Livewire
  form objects.
- [ ] `PLA-03-006` Split saves, follows, collections, visits, check-ins,
  location, invitations, corrections, warnings, reviews, questions, claims,
  reports, and creation into focused Actions.
- [ ] `PLA-03-007` Add policy methods for archive, restore, manage facts,
  verify, review claims, moderate corrections/warnings/reviews/questions,
  respond, manage media, manage venues, and view audit.
- [ ] `PLA-03-008` Add positive and negative policy matrices for owner,
  organization roles, claimant, ordinary member, moderator, admin, grant
  recipient, inactive user, and outsider.
- [ ] `PLA-03-009` Resolve pets from server-authoritative managed-pet relations;
  never trust a profile slug alone.
- [ ] `PLA-03-010` Resolve invitation recipients from authorized social
  relationships and enforce blocks, restrictions, and recipient privacy.
- [ ] `PLA-03-011` Resolve collections from the authenticated owner and reject
  stale or foreign IDs.
- [ ] `PLA-03-012` Give every retry-sensitive mutation an explicit operation
  UUID/key and enforce uniqueness inside its domain scope.
- [ ] `PLA-03-013` Map validated fields explicitly into typed data objects;
  remove accepted-but-ignored fields.
- [ ] `PLA-03-014` Keep transactions short and place side effects after commit
  where required.
- [ ] `PLA-03-015` Add rate-limit policies for warnings, reviews, questions,
  claims, reports, invitations, and repeated exact-location reveals.
- [ ] `PLA-03-016` Ensure localized validation/authorization errors do not leak
  hidden place, organization, claimant, reporter, or grant data.
- [ ] `PLA-03-017` Add controller/action architecture tests preventing queries
  and business logic from returning to Blade or route declarations.

## PLA-P04 — Scalable Server-Rendered Directory

Dependencies: PLA-P03 and required PLA-P02 facts
Exit gate: all accessible places can be discovered through one database-backed,
indexed, deterministic, localized, paginated query without JavaScript

- [ ] `PLA-04-001` Introduce a `VisiblePlacesQuery` or equivalent focused query
  service that returns an Eloquent builder, not an array catalogue.
- [ ] `PLA-04-002` Apply accessible/public/organization/owner scope before any
  search, count, aggregate, or pagination.
- [ ] `PLA-04-003` Select only card fields and required relationship keys.
- [ ] `PLA-04-004` Move category and multi-category filtering into Eloquent.
- [ ] `PLA-04-005` Move species and pet-size filtering into indexed relational
  predicates.
- [ ] `PLA-04-006` Move opening-state filtering into the canonical schedule
  query/service with an explicit reference time and timezone.
- [ ] `PLA-04-007` Move distance filtering/sorting to a portable bounded
  strategy without raw SQL in first-party application code.
- [ ] `PLA-04-008` Move accessibility, safety, leash, fencing, water, lighting,
  parking, price, rating, verification, crowd-knowledge, and visit-time filters
  to canonical facts or remove any unsupported option.
- [ ] `PLA-04-009` Implement deterministic text search over allowed public
  fields with normalized locale-aware tokens.
- [ ] `PLA-04-010` Replace substring-based natural-language side effects with a
  documented deterministic parser or remove the feature until it can be
  trustworthy.
- [ ] `PLA-04-011` Preserve manual city/area search when location permission is
  absent, denied, cleared, or unavailable.
- [ ] `PLA-04-012` Choose stable offset pagination for bounded data or cursor
  pagination for scale and document the ordering invariant.
- [ ] `PLA-04-013` Preserve validated query strings across pagination, mode,
  view, sorting, locale, and back navigation.
- [ ] `PLA-04-014` Guarantee no silent 500-row completeness cap.
- [ ] `PLA-04-015` Load rating, review count, warning existence, saves/follows,
  and other card aggregates through eager loads or aggregate subqueries with no
  loop queries.
- [ ] `PLA-04-016` Produce the map projection from the same authorized result
  set and public coordinate boundary as the list.
- [ ] `PLA-04-017` Ensure map-only interactions have list/form equivalents and
  selected state is server-recoverable.
- [ ] `PLA-04-018` Add empty, no-match, invalid-filter, stale-page, and last-page
  behavior with localized recovery actions.
- [ ] `PLA-04-019` Add tests with more than 500 accessible records proving
  completeness, stable order, and privacy.
- [ ] `PLA-04-020` Record explain-plan evidence for the high-volume search,
  category, opening, verification, rating, and sort paths.

## PLA-P05 — Canonical Place Facts And Presentation

Dependencies: PLA-P02 and PLA-P03
Exit gate: every rendered fact is canonical, source-aware, freshness-aware, and
truthful for fixture and newly created places

- [ ] `PLA-05-001` Define the complete place public projection and private
  manager projection with explicit fields.
- [ ] `PLA-05-002` Migrate fixture categories to the canonical taxonomy and
  prove multi-category rendering/filtering.
- [ ] `PLA-05-003` Migrate fixture contacts to canonical contact points with
  validated schemes and safe external-link behavior.
- [ ] `PLA-05-004` Migrate fixture hours to timezone-aware intervals and
  exception records.
- [ ] `PLA-05-005` Migrate services and pricing without float money.
- [ ] `PLA-05-006` Migrate species, size, leash, access, indoor/outdoor, and
  facility rules into structured facts.
- [ ] `PLA-05-007` Migrate accessibility facts with status, source, verified
  time, and unknown-state presentation.
- [ ] `PLA-05-008` Migrate safety facts and separate stable facility facts from
  temporary warnings.
- [ ] `PLA-05-009` Represent unknown, community-submitted, manager-confirmed,
  organization-confirmed, and independently verified scopes distinctly.
- [ ] `PLA-05-010` Compute freshness by fact/source scope rather than treating
  `places.updated_at` as proof that all information is current.
- [ ] `PLA-05-011` Add stale/expired presentation that invites correction and
  never silently hides uncertainty.
- [ ] `PLA-05-012` Remove default Vilnius coordinates and invented common-pet
  support for places that lack confirmed facts.
- [ ] `PLA-05-013` Remove generic facts that can be misread as confirmed
  services, safety, accessibility, or emergency capability.
- [ ] `PLA-05-014` Replace static event keys with canonical event relationships
  and authorized event projections.
- [ ] `PLA-05-015` Replace static nearby items with an authorized, bounded,
  public-coordinate query.
- [ ] `PLA-05-016` Remove unsupported weather/crowd/analytics claims or label
  deterministic historical/demo data explicitly and keep it out of production.
- [ ] `PLA-05-017` Add fact-level update history visible at the correct public,
  manager, or moderator scope.
- [ ] `PLA-05-018` Add detail tests for each category and for sparse, stale,
  archived, unlisted, private, and newly completed places.

## PLA-P06 — Place Submission, Duplicate Review, And Publication

Dependencies: PLA-P02, PLA-P03, PLA-P05
Exit gate: a member can submit a truthful place, recover from validation,
resolve duplicate candidates, and see an authorized review/publication state

- [ ] `PLA-06-001` Create a dedicated place submission request/form and typed
  data object.
- [ ] `PLA-06-002` Define category-specific required and optional fields without
  requiring an exact/public address for place types that should use a general
  area.
- [ ] `PLA-06-003` Validate contact URLs, phone/email, coordinates, schedule,
  services, rules, features, source, and evidence per field.
- [ ] `PLA-06-004` Show and persist the exact difference between public region,
  public point/address, and encrypted exact location.
- [ ] `PLA-06-005` Use an explicit operation idempotency key carried across
  validation retries.
- [ ] `PLA-06-006` Create all submitted facts and provenance in one short
  transaction with the place.
- [ ] `PLA-06-007` Implement duplicate candidate scoring over normalized name,
  address, phone, website, coordinates, category, and aliases.
- [ ] `PLA-06-008` Scope duplicate presentation so private/unlisted places do
  not leak to unauthorized submitters.
- [ ] `PLA-06-009` Allow a member to confirm an existing public place, propose
  a correction, or continue a distinct submission.
- [ ] `PLA-06-010` Route ambiguous/private duplicates to an authorized review
  queue without revealing protected facts.
- [ ] `PLA-06-011` Define submitted, needs-information, duplicate-review,
  approved, rejected, published, and withdrawn transitions.
- [ ] `PLA-06-012` Add manager/moderator decisions with reasons and immutable
  audit history.
- [ ] `PLA-06-013` Send user-visible status through synchronous fallback plus
  after-commit notifications when queues are available.
- [ ] `PLA-06-014` Add tests for duplicate races, repeated submits, hidden
  duplicates, stale operation keys, rejected fields, and successful publish.

## PLA-P07 — Place Management, Verification, And Claims

Dependencies: PLA-P02, PLA-P03, PLA-P05, PLA-P06
Exit gate: legitimate managers can maintain place facts and authority through
audited workflows while unauthorized users cannot infer or mutate them

- [ ] `PLA-07-001` Build an authorized server-rendered manager workspace for
  places the actor can manage.
- [ ] `PLA-07-002` Add selected organization context where an actor manages
  multiple organizations; never infer authority from a browser value alone.
- [ ] `PLA-07-003` Support editing identity, categories, summary, contacts,
  hours, services, rules, accessibility, safety, and public location through
  focused Actions.
- [ ] `PLA-07-004` Route exact-location changes through the existing encrypted
  update/version Action and audit boundary.
- [ ] `PLA-07-005` Add optimistic concurrency/version checks to prevent silent
  overwrites between managers and accepted corrections.
- [ ] `PLA-07-006` Add archive and restore transitions with impact previews for
  events, venues, links, grants, and discovery.
- [ ] `PLA-07-007` Define verification scopes, evidence, reviewer, verified-at,
  expires-at, renewal, downgrade, and revocation.
- [ ] `PLA-07-008` Build a claim submission flow using server-authoritative
  claimant and organization identities.
- [ ] `PLA-07-009` Encrypt claim contact/evidence and expose it only to scoped
  reviewers.
- [ ] `PLA-07-010` Prevent duplicate/conflicting active claims and enforce
  reviewer conflict-of-interest rules.
- [ ] `PLA-07-011` Approve a claim atomically into owner/organization
  membership/management authority with an audit event.
- [ ] `PLA-07-012` Support request-more-information, reject, expire, withdraw,
  revoke, and appeal states.
- [ ] `PLA-07-013` Notify affected existing managers of authority changes
  without leaking evidence.
- [ ] `PLA-07-014` Add an audit viewer for authorized managers/moderators with
  redacted sensitive values.
- [ ] `PLA-07-015` Add complete owner/organization/claimant/reviewer/outsider
  tests, including race and revocation cases.

## PLA-P08 — Corrections And Provenance

Dependencies: PLA-P02, PLA-P03, PLA-P05, PLA-P07
Exit gate: corrections are shared evidence-backed proposals with safe review,
conflict detection, application, attribution, and history

- [ ] `PLA-08-001` Replace account-local correction arrays with relational
  correction records keyed by canonical place ID.
- [ ] `PLA-08-002` Restrict correctable fields to an explicit field map with
  field-specific validation and presentation.
- [ ] `PLA-08-003` Capture the current fact/version at submission without
  trusting browser-supplied current values.
- [ ] `PLA-08-004` Accept evidence references, personal observation date,
  source kind, and optional private reviewer note under the correct visibility.
- [ ] `PLA-08-005` Deduplicate equivalent pending corrections without dropping
  independent evidence.
- [ ] `PLA-08-006` Let authorized managers/moderators accept, partially accept,
  request information, reject, withdraw, supersede, and reopen.
- [ ] `PLA-08-007` Apply accepted corrections through field-specific update
  Actions, not direct generic assignment.
- [ ] `PLA-08-008` Detect stale source versions and require an explicit merge
  decision.
- [ ] `PLA-08-009` Preserve original/proposed/applied values, provenance,
  reviewer, decision reason, and public-safe history.
- [ ] `PLA-08-010` Update freshness/verification only for the affected scope and
  according to evidence quality.
- [ ] `PLA-08-011` Notify submitter and managers after commit with synchronous
  status fallback.
- [ ] `PLA-08-012` Test cross-account visibility, hidden facts, conflicts,
  races, idempotency, authorization, localization, and audit output.

## PLA-P09 — Temporary Warnings And Safety Moderation

Dependencies: PLA-P02, PLA-P03, PLA-P05, PLA-P07
Exit gate: warnings are shared, bounded, abuse-resistant safety records with
truthful active/resolved states and no false emergency guarantee

- [ ] `PLA-09-001` Define warning categories, severity, evidence requirement,
  default TTL, renewal, and publication policy.
- [ ] `PLA-09-002` Replace account-local warning arrays with canonical warning
  records and place relationships.
- [ ] `PLA-09-003` Store only a bounded public area; reject exact home/private
  coordinates from public warning text and metadata.
- [ ] `PLA-09-004` Sanitize user content and keep rich HTML outside the warning
  boundary unless explicitly required.
- [ ] `PLA-09-005` Enforce one confirmation per actor/warning and preserve the
  confirmation timestamp.
- [ ] `PLA-09-006` Derive confirmation counts through aggregate subqueries or
  deliberate maintained counters.
- [ ] `PLA-09-007` Allow author, manager, moderator, and system expiry actions
  only according to explicit policy.
- [ ] `PLA-09-008` Implement active, needs-review, disputed, resolved, expired,
  rejected, and removed states with reasons.
- [ ] `PLA-09-009` Add rate limits, duplicate detection, block enforcement,
  abuse reporting, and moderator escalation.
- [ ] `PLA-09-010` Ensure expiry is correct without relying solely on a user
  opening the page; provide scheduler/runtime fallback behavior.
- [ ] `PLA-09-011` Present timestamps, source, confirmation confidence,
  uncertainty, and call-first emergency guidance accessibly.
- [ ] `PLA-09-012` Prevent warnings from permanently changing rating or
  verification without a separate audited decision.
- [ ] `PLA-09-013` Test two-account confirmation, concurrent confirmation,
  expiry, dispute, false report, moderation, privacy, and notification behavior.

## PLA-P10 — Reviews And Manager Responses

Dependencies: PLA-P02, PLA-P03, PLA-P05, PLA-P07
Exit gate: reviews are eligible, shared, attributable or deliberately
anonymous in presentation, moderated, aggregatable, and manager-responsive

- [ ] `PLA-10-001` Implement the review eligibility rule chosen in PLA-00-011.
- [ ] `PLA-10-002` Derive verified-visit status server-side from canonical visit
  evidence; never trust a checkbox or user-state label.
- [ ] `PLA-10-003` Resolve the optional managed pet through authorized pet
  identity and decide whether it is publicly displayed.
- [ ] `PLA-10-004` Replace Mia Carter/MC constants with the authenticated social
  actor projection.
- [ ] `PLA-10-005` Define anonymity as a presentation/privacy mode while
  retaining accountable moderator identity.
- [ ] `PLA-10-006` Enforce rating and criterion validation per category and
  locale-independent stored values.
- [ ] `PLA-10-007` Support controlled edit/update with immutable version
  history instead of duplicate stacking.
- [ ] `PLA-10-008` Add moderation, report, hidden, restored, and author-deleted
  states with retention/legal-hold behavior.
- [ ] `PLA-10-009` Allow one policy-authorized manager response with versioned
  edits; a manager cannot delete or rewrite the review.
- [ ] `PLA-10-010` Compute rating, count, verified count, and criteria summaries
  from visible eligible reviews.
- [ ] `PLA-10-011` Define Bayesian/minimum-count behavior before exposing ranked
  recommendations by rating.
- [ ] `PLA-10-012` Invalidate only owned aggregates/projections after review or
  moderation transitions.
- [ ] `PLA-10-013` Test cross-account display, author rename/deletion, anonymous
  presentation, duplicate race, moderation, response authorization, aggregates,
  and pagination.

## PLA-P11 — Questions, Official Answers, And Reports

Dependencies: PLA-P02, PLA-P03, PLA-P05, PLA-P07
Exit gate: questions reach the place community/manager, official answers are
authorized and versioned, and reports reach protected moderation

- [x] `PLA-11-001` Replace account-local question arrays with shared relational
  questions.
- [ ] `PLA-11-002` Derive the question author from canonical actor identity and
  apply block/restriction/rate-limit rules.
- [ ] `PLA-11-003` Define open, answered, needs-information, duplicate, closed,
  hidden, and removed states.
- [ ] `PLA-11-004` Notify authorized managers of new questions after commit with
  a runtime-safe fallback.
- [x] `PLA-11-005` Authorize official answers through management scope, not a
  presentation boolean.
- [ ] `PLA-11-006` Enforce one current official answer with immutable versions,
  answer author, timestamp, and correction reason.
- [ ] `PLA-11-007` Permit community responses only if product scope explicitly
  requires them; otherwise avoid implying an unsupported discussion thread.
- [ ] `PLA-11-008` Add duplicate linking and public-safe moderation reasons.
- [ ] `PLA-11-009` Route question/review/warning/place reports into the unified
  moderation boundary with typed subject relations.
- [ ] `PLA-11-010` Protect reporter identity and evidence from subject owners
  unless policy explicitly permits disclosure.
- [ ] `PLA-11-011` Implement triage, assignment, request-information,
  resolution, appeal, reopen, and audit transitions.
- [ ] `PLA-11-012` Test member-to-manager visibility, organization roles,
  concurrent answers, report privacy, moderator actions, notifications, and
  localized errors.

## PLA-P12 — Personal Places And Social Planning

Dependencies: PLA-P00 decisions, PLA-P02/P03, canonical pet/social identity
Exit gate: every personal/social control has truthful persistence, ownership,
privacy, expiry, and cross-account semantics

- [ ] `PLA-12-001` Replace place slugs in new personal state with canonical
  place IDs and provide alias/backfill compatibility.
- [ ] `PLA-12-002` Define save semantics and idempotent toggle/add/remove
  behavior across devices.
- [ ] `PLA-12-003` Define follow semantics, notification preferences, delivery,
  muting, invalidation, and place archival behavior.
- [ ] `PLA-12-004` Build collection options from the current account instead of
  fixed keys.
- [ ] `PLA-12-005` Implement create/rename/delete/reorder/private collection
  behavior if collections remain MVP.
- [ ] `PLA-12-006` Add collaborative/share behavior only after membership,
  invitation, block, revocation, and audit contracts exist.
- [ ] `PLA-12-007` Resolve visit pets from managed canonical pet profiles.
- [ ] `PLA-12-008` Define visit history retention, correction, deletion, export,
  and pet-transfer behavior.
- [ ] `PLA-12-009` Keep check-ins private by default and label them as private
  until a real recipient projection exists.
- [ ] `PLA-12-010` Scope check-in idempotency/locking by actor and place so one
  member cannot block another member's private check-in.
- [ ] `PLA-12-011` Define two-hour expiry, early end, stale cleanup, history,
  offline failure, and clock-skew behavior.
- [ ] `PLA-12-012` Implement friend/close-circle/anonymous visibility only with
  canonical relationship, block, audience, expiry, and anti-stalking tests.
- [ ] `PLA-12-013` Resolve invitation recipients from allowed relationships and
  enforce blocks, privacy, rate limits, expiry, response, and idempotency.
- [ ] `PLA-12-014` Integrate invitations with messages/notifications without
  exposing exact location before authorization.
- [ ] `PLA-12-015` Define recent-place history limits, retention, clearing, and
  device/account synchronization.
- [ ] `PLA-12-016` Test owner isolation, foreign IDs, slug changes, archived
  places, pet transfer, recipient blocks, expiry, races, and repeated requests.

## PLA-P13 — Place Media Lifecycle

Dependencies: PLA-P02, PLA-P03, PLA-P07
Exit gate: demo and user media are owned, processed, moderated, accessible, and
available without accidental external fixture dependencies

- [ ] `PLA-13-001` Define supported image/video types, byte/dimension/duration
  limits, attribution, consent, moderation, retention, and deletion policy.
- [ ] `PLA-13-002` Add canonical place media records with owner, status,
  ordering, focal data, alt text per locale strategy, and audit fields.
- [ ] `PLA-13-003` Store uploads on the correct public/private disk and avoid
  unrestricted URLs for pending/private evidence.
- [ ] `PLA-13-004` Validate content and file signatures, randomize paths, and
  prevent executable or cross-domain file access.
- [ ] `PLA-13-005` Generate responsive variants asynchronously with a safe
  synchronous placeholder/failure path.
- [ ] `PLA-13-006` Require meaningful alt text or mark deliberately decorative
  media; localize first-party labels.
- [ ] `PLA-13-007` Add manager upload, reorder, feature, replace, archive, and
  delete Actions with policies.
- [ ] `PLA-13-008` Add community media contribution/review only if explicitly
  required; otherwise keep upload manager-only.
- [ ] `PLA-13-009` Replace Unsplash demo URLs with repository-owned local
  fixtures and documented licenses/attribution.
- [ ] `PLA-13-010` Prevent remote network access during tests and seeding.
- [ ] `PLA-13-011` Define cache headers, invalidation, broken-file fallback, and
  orphan cleanup.
- [ ] `PLA-13-012` Test malicious files, oversized uploads, unauthorized reads,
  variant failure, alt text, archival, seed repeatability, and low bandwidth.

## PLA-P14 — Generalized Location, Map, And Routes

Dependencies: PLA-P04, PLA-P05, PLA-P12
Exit gate: location is optional and minimized, map/list results agree, and all
route behavior has accessible provider-free fallbacks

- [ ] `PLA-14-001` Document generalized-location precision, retention, storage,
  clearing, logging, cache, analytics, and consent semantics.
- [ ] `PLA-14-002` Keep manual city/area origin first-class and usable when
  geolocation is unsupported, denied, slow, or revoked.
- [ ] `PLA-14-003` Validate coordinate range and round/minimize before
  persistence; never include location in URL, logs, shared cache, or analytics.
- [ ] `PLA-14-004` Define distance accuracy labels and avoid false exactness for
  generalized origins or public approximate place points.
- [ ] `PLA-14-005` Ensure the map consumes only the current authorized paginated
  or explicitly bounded result projection.
- [ ] `PLA-14-006` Synchronize list selection, map selection, focus, URL/query
  state, and back navigation without trapping keyboard focus.
- [ ] `PLA-14-007` Add clustered/overlapping marker handling with accessible
  list equivalents.
- [ ] `PLA-14-008` Keep every filter, sort, card, and detail action functional
  with JavaScript disabled.
- [ ] `PLA-14-009` Define canonical route geometry, source, direction,
  distance, elevation if supported, accessibility, safety, and freshness.
- [ ] `PLA-14-010` Store route geometry through a portable validated format and
  never expose private path endpoints.
- [ ] `PLA-14-011` Provide text route summaries and external navigation links
  only with safe schemes and explicit provider handoff.
- [ ] `PLA-14-012` Test denied permission, cleared state, invalid coordinates,
  sparse map data, repeated navigation, listener cleanup, keyboard use, and
  public/private coordinate separation.

## PLA-P15 — Emergency Veterinary Mode

Dependencies: PLA-P04, PLA-P05, canonical schedules/services
Exit gate: emergency mode safely ranks eligible clinics from canonical facts,
states uncertainty, and always instructs the member to call first

- [ ] `PLA-15-001` Define the veterinary capability taxonomy required for
  emergency eligibility and species handling.
- [ ] `PLA-15-002` Derive current open/closing/unknown/temporarily-closed state
  from canonical schedule, timezone, and exceptions.
- [ ] `PLA-15-003` Rank open and species-capable clinics first, then unknown
  candidates, with deterministic distance/tie behavior.
- [ ] `PLA-15-004` Never classify a clinic as capable from a generic place
  category alone.
- [ ] `PLA-15-005` Show source and freshness for hours, phone, and capability at
  the point of action.
- [ ] `PLA-15-006` Render call-first guidance unconditionally and never promise
  availability, admission, response time, treatment, or outcome.
- [ ] `PLA-15-007` Make the primary emergency call link safe, accessible, and
  absent when the phone is unknown.
- [ ] `PLA-15-008` Preserve a provider-free list when location is unavailable;
  do not hide all clinics behind distance.
- [ ] `PLA-15-009` Handle overnight schedules, daylight-saving changes,
  holidays, stale facts, no eligible results, and phone-only fallback.
- [ ] `PLA-15-010` Add category-specific moderation/escalation for incorrect
  emergency facts.
- [ ] `PLA-15-011` Add EN/LT/RU safety-copy parity and locale/timezone tests.
- [ ] `PLA-15-012` Add clock-controlled ranking tests and browser flows for
  open, closing, closed, unknown, stale, no-phone, and no-location states.

## PLA-P16 — Venues, Events, Exact Access, And Organizations

Dependencies: PLA-P03, PLA-P05, PLA-P07, existing authority foundation
Exit gate: managers can operate venues and event organizers can select/reveal
only authorized place facts through complete audited workflows

- [ ] `PLA-16-001` Build authorized venue list/create/edit/archive flows under
  a canonical place.
- [ ] `PLA-16-002` Validate venue name, type, capacity, access, accessibility,
  private instructions, and active status.
- [ ] `PLA-16-003` Prevent venue selection outside its place or across an
  unauthorized organization boundary.
- [ ] `PLA-16-004` Define event eligibility for public, unlisted, private,
  archived, and grant-scoped places and venues.
- [ ] `PLA-16-005` Preserve stable event links when a place/venue is renamed;
  define behavior when archived or merged.
- [ ] `PLA-16-006` Add manager-controlled grant creation UI with recipient,
  purpose, expiry, scope, and confirmation.
- [ ] `PLA-16-007` Add grant listing, revoke, expire, and replacement flows with
  audit history and no token disclosure.
- [ ] `PLA-16-008` Add exact-location reveal UI with purpose, step-up decision,
  explicit action, audit, and safe failure.
- [ ] `PLA-16-009` Notify recipients/managers of grant and revocation changes
  without exposing exact data in notification payloads.
- [ ] `PLA-16-010` Confirm registration/attendance access transitions and revoke
  event-scoped access when registration is no longer eligible.
- [ ] `PLA-16-011` Add selected organization context and membership expiry to
  event-place/venue management.
- [ ] `PLA-16-012` Prevent public cards, caches, exports, logs, emails, calendar
  payloads, and analytics from containing exact private facts.
- [ ] `PLA-16-013` Add cross-organization, expired membership, pending
  registration, revoked grant, archived venue, merged place, and reveal-audit
  tests.

## PLA-P17 — Blade, Interaction, Localization, And Accessibility

Dependencies: functional packages for each surface
Exit gate: every workflow is localized, keyboard-complete, responsive,
stateful, and truthful across the supported matrix

- [ ] `PLA-17-001` Keep all database/domain calculations out of Blade and pass
  prepared view data or typed view models.
- [ ] `PLA-17-002` Replace repeated Places presentation with anonymous Blade
  components that have explicit props and meaningful empty states.
- [ ] `PLA-17-003` Use class-based Livewire only where server-backed interaction
  materially benefits the workflow; do not introduce Volt.
- [ ] `PLA-17-004` Provide loading, dirty, success, validation, authorization,
  conflict, empty, error, and offline states for interactive flows.
- [ ] `PLA-17-005` Preserve form values and focus after validation errors.
- [ ] `PLA-17-006` Ensure every icon control has an accessible name and every
  field has a programmatic label, description, and error association.
- [ ] `PLA-17-007` Keep touch targets at least 44px and visible focus under all
  themes and forced colors.
- [ ] `PLA-17-008` Provide a complete keyboard path through filters, results,
  map alternatives, tabs, dialogs, forms, media, and manager queues.
- [ ] `PLA-17-009` Ensure reduced-motion behavior removes non-essential motion
  without hiding state changes.
- [ ] `PLA-17-010` Verify 200% zoom, text spacing, screen-reader landmarks,
  heading order, status announcements, and no horizontal page overflow.
- [ ] `PLA-17-011` Verify responsive layouts at 320, 360, 375, 390, 430, 768,
  1024, 1280, 1440, and 1728 pixels.
- [ ] `PLA-17-012` Keep the current card-height regression gate while allowing
  legitimate content expansion without truncating critical facts.
- [ ] `PLA-17-013` Route all new user-facing text through Laravel language files
  for EN/LT/RU with placeholder/plural parity.
- [ ] `PLA-17-014` Format dates, times, numbers, distances, lists, and money
  explicitly for locale and timezone.
- [ ] `PLA-17-015` Prevent raw translation keys and unsafe user HTML from
  rendering in any success/error/empty/history state.
- [ ] `PLA-17-016` Test JavaScript-disabled completion for discovery and all
  server-form workflows that do not inherently require browser APIs.
- [ ] `PLA-17-017` Test repeated navigation and teardown so map/dialog/listener
  code does not duplicate handlers or leak memory.
- [ ] `PLA-17-018` Run a manual assistive-technology and low-bandwidth review for
  the critical directory, detail, emergency, submission, and management flows.

## PLA-P18 — Performance, Cache, Security, And Observability

Dependencies: functional query paths stable
Exit gate: representative scale stays within explicit budgets, private data is
excluded, and operators can diagnose failures without logging secrets

- [ ] `PLA-18-001` Define query, database time, server render, payload, memory,
  JavaScript, image, and interaction budgets for list/detail/manager pages.
- [ ] `PLA-18-002` Add deterministic volume fixtures for places, categories,
  facts, reviews, warnings, venues, grants, and events.
- [ ] `PLA-18-003` Measure query counts at one, typical, and high volume and
  assert no row-dependent N+1 growth.
- [ ] `PLA-18-004` Record explain plans for every frequent where/order/join path
  against representative volume and verify intended indexes.
- [ ] `PLA-18-005` Measure and bound PHP memory after removing the in-memory
  catalogue/filter/pagination path.
- [ ] `PLA-18-006` Add cache only for measured stable work and document owner,
  versioned key, locale/user/role scope, TTL, invalidation, failure, and tests.
- [ ] `PLA-18-007` Keep exact locations, generalized origin, check-ins,
  claimant/reporter identity, evidence, private invitations, and grants out of
  shared caches.
- [ ] `PLA-18-008` Add cache invalidation tests for fact, schedule, review,
  warning, verification, media, archive, merge, and grant changes.
- [ ] `PLA-18-009` Add rate-limit and abuse telemetry without logging message
  bodies, evidence, coordinates, tokens, session IDs, or authorization headers.
- [ ] `PLA-18-010` Define structured operational events for submission,
  moderation, claims, exact reveals, failures, and queue fallback.
- [ ] `PLA-18-011` Add slow-query/request observability and alerts with
  privacy-safe identifiers.
- [ ] `PLA-18-012` Threat-model enumeration, IDOR, hidden-place search,
  duplicate leakage, stored XSS, malicious URLs/files, claim fraud, warning
  abuse, review manipulation, stalking, and exact-location disclosure.
- [ ] `PLA-18-013` Test CSRF, mass assignment, policy bypass, stale role/grant,
  signed/token scope if introduced, and sensitive logging exclusions.
- [ ] `PLA-18-014` Verify Content Security Policy and external-link behavior for
  map/media/contact/provider integrations.
- [ ] `PLA-18-015` Define operational dashboards and incident runbooks for
  failed media processing, stale schedules, moderation backlog, and reveal
  anomalies.

## PLA-P19 — Migration, Backfill, Factories, And Seeding

Dependencies: target schemas and workflows stable
Exit gate: existing demo and compatibility state migrate safely, fresh/repeat
seeds are deterministic, and rollback/recovery is documented and tested

- [ ] `PLA-19-001` Build a versioned mapping from every static fixture field to
  canonical relational records.
- [ ] `PLA-19-002` Backfill categories, contacts, hours, services, rules,
  accessibility, safety, sources, media, reviews/questions only where their
  provenance is truthful.
- [ ] `PLA-19-003` Do not convert fictional account-local community arrays into
  shared public contributions without an explicit provenance/privacy decision.
- [ ] `PLA-19-004` Build a resumable, idempotent compatibility-state migrator for
  private saves/follows/collections/visits if their storage changes.
- [ ] `PLA-19-005` Use `lazyById`/`chunkById` for large backfills and persist
  checkpoints without raw SQL.
- [ ] `PLA-19-006` Add dry-run counts, mismatch reporting, retry behavior, and
  deterministic reconciliation.
- [ ] `PLA-19-007` Add factories with valid defaults and meaningful states for
  every new model.
- [ ] `PLA-19-008` Update demo seeders to use local fixtures and canonical
  relationships only in local/demo/testing.
- [ ] `PLA-19-009` Keep fixed taxonomy/reference seeders repeat-safe and never
  truncate production data.
- [ ] `PLA-19-010` Add seed scenarios for sparse/unknown, stale, verified,
  private, organization, emergency, duplicate, disputed warning, moderated
  review, pending claim, and archived/merged places.
- [ ] `PLA-19-011` Verify fresh migration and complete seed in an isolated
  database, then repeat fixed seeders and compare stable counts.
- [ ] `PLA-19-012` Verify production-environment guards prevent demo identities
  and media from being created.
- [ ] `PLA-19-013` Document expand/backfill/switch/contract deployment order,
  compatibility window, rollback, and recovery from partial progress.
- [ ] `PLA-19-014` Add post-deploy integrity checks for orphan facts, invalid
  aliases, duplicate active claims, overlapping schedules, missing media, and
  private-field projection leaks.

## PLA-P20 — Automated And Browser Verification Matrix

Dependencies: PLA-P01 through PLA-P19 as applicable
Exit gate: focused, full, static, migration, build, cache, and browser gates pass
with exact recorded evidence and no hidden skips

- [ ] `PLA-20-001` Add model/factory unit tests for all new relationships,
  casts, scopes, invariants, and enum states.
- [ ] `PLA-20-002` Add Action tests for every transition, validation boundary,
  authorization path, idempotency key, race, and repeated submission.
- [ ] `PLA-20-003` Add list/detail feature tests for each visibility, category,
  locale, sparse fact set, and lifecycle state.
- [ ] `PLA-20-004` Add cross-account tests for every shared contribution and
  manager/moderator response.
- [ ] `PLA-20-005` Add owner/organization/role-expiry/grant-revocation isolation
  tests.
- [ ] `PLA-20-006` Add exact/generalized location privacy tests across HTML,
  JSON attributes, redirects, sessions, caches, logs, notifications, mail,
  exports, and errors.
- [ ] `PLA-20-007` Add concurrency tests for duplicate submissions, warning
  confirmation, review uniqueness, official answer, claim approval, merge,
  grants, and check-ins.
- [ ] `PLA-20-008` Add localization parity/render tests for EN/LT/RU and
  explicit locale/timezone formatting.
- [ ] `PLA-20-009` Add architecture tests preventing Blade queries/business
  logic, `@php`, Volt, raw SQL, broad model loads, and secrets in code.
- [ ] `PLA-20-010` Add query-count, explain-plan, payload, memory, and cache-
  invalidation tests at representative volume.
- [ ] `PLA-20-011` Add filesystem/media tests with fakes and no accidental
  network requests.
- [ ] `PLA-20-012` Add browser flows for search/filter/sort/pagination/view/map
  state and JavaScript-disabled fallback.
- [ ] `PLA-20-013` Add browser flows for dynamic place save/follow/visit/check-
  in/collection/invitation and expiry/revocation.
- [ ] `PLA-20-014` Add browser flows for submit/duplicate/review/publish and
  manager fact editing.
- [ ] `PLA-20-015` Add browser flows for correction, warning/confirmation/
  resolution, review/response, question/answer, claim, and report moderation.
- [ ] `PLA-20-016` Add browser flows for emergency mode, venue selection,
  grants, exact reveal, and event registration access.
- [ ] `PLA-20-017` Run the full responsive, keyboard, focus, reduced-motion,
  forced-colors, zoom, console, image, overflow, and low-bandwidth matrix.
- [ ] `PLA-20-018` Run targeted tests first, then Pint, Larastan, Composer
  validation/audit, npm audit/build, cache smoke checks, isolated fresh/repeat
  seed, and the full serial Pest suite.
- [ ] `PLA-20-019` Record exact commands, exits, test/assertion counts,
  durations, browser viewports, manual checks, and any baseline failures.
- [ ] `PLA-20-020` Re-run affected gates after the final diff and never promote
  a requirement to verified from older or partial evidence.

## PLA-P21 — Rollout, Operations, Documentation, And Publication

Dependencies: PLA-P20
Exit gate: production rollout and rollback are executable, documentation tells
the truth, the final diff is attributable, and the verified commit is published

- [ ] `PLA-21-001` Update architecture, domain model, data model, security,
  authorization, privacy, frontend, accessibility, localization, performance,
  caching, integration, testing, seeding, deployment, and operations documents
  for delivered behavior.
- [ ] `PLA-21-002` Update PRD-PLACE requirement evidence and compliance status
  only from current observed gates.
- [ ] `PLA-21-003` Update the implementation plan, documentation index, current
  progress, known external limitations, and changelog without erasing
  historical evidence.
- [ ] `PLA-21-004` Document queue/scheduler requirements and synchronous
  fallbacks for critical user-visible transitions.
- [ ] `PLA-21-005` Document rollout flags, expand/backfill/switch/contract order,
  health checks, and stop/rollback conditions.
- [ ] `PLA-21-006` Add privacy-safe operational verification for sample list,
  detail, emergency, manager, moderation, and exact-reveal flows.
- [ ] `PLA-21-007` Add post-deploy checks for query regression, errors, queue
  backlog, stale schedule rate, moderation backlog, media failure, and reveal
  anomalies.
- [ ] `PLA-21-008` Define rollback behavior for new writes during the
  compatibility window; never discard accepted user contributions silently.
- [ ] `PLA-21-009` Perform final secret, personal-data, generated-artifact,
  untracked-file, source-prompt, and complete diff review.
- [ ] `PLA-21-010` In a dirty shared tree, stage only attributable files through
  a temporary index and inspect the complete staged diff plus `git diff --check`.
- [ ] `PLA-21-011` Commit coherent implementation with tests and documentation
  on `main`; never include unrelated user work.
- [ ] `PLA-21-012` Push only after required verification passes and record the
  observed remote result and commit ID.
- [ ] `PLA-21-013` Monitor the rollout, execute rollback when a stop condition is
  met, and append evidence to the current-progress ledger.

## PLA-P22 — Explicit Post-MVP Provider And Expansion Backlog

Dependencies: production-complete provider-free core and a new approved
requirement
Exit gate: none for MVP; every promoted item requires its own design, privacy,
provider, failure, cost, accessibility, testing, and rollback package

- [ ] `PLA-22-001` Evaluate external appointment scheduling without collecting
  payment or medical data outside an approved boundary.
- [ ] `PLA-22-002` Evaluate payments and venue rental with server-authoritative
  inventory, entitlement, refund, dispute, and webhook idempotency.
- [ ] `PLA-22-003` Evaluate directions/route providers with consent, CSP,
  attribution, rate, cost, outage, and manual fallback.
- [ ] `PLA-22-004` Evaluate provider map tiles and offline behavior with license,
  cache, privacy, storage, and accessibility review.
- [ ] `PLA-22-005` Evaluate official opening-hours/business registry ingestion
  with provenance, conflict, refresh, and provider outage behavior.
- [ ] `PLA-22-006` Evaluate weather, water-quality, tick, and environmental feeds
  without presenting stale data as current safety advice.
- [ ] `PLA-22-007` Evaluate transit integration with locale, accessibility,
  provider limits, and provider-free fallback.
- [ ] `PLA-22-008` Evaluate live crowd signals only with strong anti-stalking,
  aggregation, minimum cohort, expiry, abuse, and opt-in controls.
- [ ] `PLA-22-009` Evaluate real-time friend presence only after social audience,
  blocks, revocation, background-location, and safety reviews.
- [ ] `PLA-22-010` Evaluate automatic pet suitability only after canonical pet
  facts, explicit consent, explainability, and safe unknown handling.
- [ ] `PLA-22-011` Evaluate collaborative collections with membership,
  invitations, roles, conflicts, audit, revocation, and offline merge behavior.
- [ ] `PLA-22-012` Evaluate external business verification and analytics with
  lawful basis, minimization, retention, deletion, export, and cost controls.
- [ ] `PLA-22-013` Evaluate new sitter, hotel, memorial, and other category
  verticals as separate domain designs rather than free-form category records.

## Package Acceptance Rules

Every package must satisfy all applicable conditions before `[x]`:

1. A requirement or audit finding names the behavior.
2. The schema/model boundary protects uniqueness, privacy, and lifecycle.
3. A dedicated request/form validates browser input.
4. A policy authorizes list, view, and every mutation.
5. A focused Action owns the transition and transactional boundary.
6. Blade renders only prepared values; JavaScript is progressive enhancement.
7. EN/LT/RU, accessibility, responsive, empty/error/offline states are covered.
8. Positive, negative, cross-account, repeated, and race-sensitive tests pass.
9. Query/payload/cache/security evidence meets the package budget.
10. Migration, seed, deployment, rollback, and documentation evidence is
    current.

## Stop Conditions

Stop the affected package and record a blocker when:

- a product choice would materially alter privacy, review eligibility,
  verification, warning, claim, collection, or check-in semantics;
- a migration cannot preserve existing authoritative place/venue/location data;
- authorization cannot be decided from canonical server-side identity;
- a proposed cache or provider boundary could expose exact/private data;
- a test failure indicates an unrelated baseline defect that cannot be safely
  separated;
- concurrent work owns the same files or migrations and safe integration is not
  established;
- required runtime/provider behavior cannot fail safely;
- a gate cannot be run or its observed result contradicts the completion claim.

## Immediate Next Slice

The first implementation slice is deliberately narrow:

1. Complete PLA-P00 decisions that affect target binding and shared
   contributions.
2. Add PLA-01-001 through PLA-01-010 as red tests.
3. Fix dynamic target resolution without changing contribution storage.
4. Introduce the smallest relational question/answer vertical slice so one
   member can ask and an authorized manager can answer across accounts.
5. Run targeted tests, Pint, Larastan, and the affected browser flow before
   starting another contribution type.

This slice proves the architectural pattern for warnings, reviews,
corrections, claims, and reports while keeping the diff reviewable.

Implementation checkpoint (2026-08-03): steps 2-4 are partially delivered.
Dynamic save and inaccessible-target tests now exercise canonical resolution,
and the first cross-account question/official-answer path is relational,
policy-authorized, actor-attributed, and idempotent. The unchecked task IDs
above remain open, including complete dynamic-action coverage, moderation,
notifications, answer versioning, rate limits, and the remaining contribution
types.
