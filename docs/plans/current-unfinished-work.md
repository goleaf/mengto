# Current Unfinished Work Audit

Audit date: 2026-08-30

Status: release audit reconciliation in progress; product implementation and
release verification remain incomplete. Current gate evidence is in
`docs/reports/final-release-verification.md`.

## Scope And Counting Rules

This audit classifies the current `docs/implementation-plan.md` and active
documents directly under `docs/plans`. The files under
`docs/superpowers/plans` are historical prototype delivery records and are not
current backlog sources. Their unchecked boxes are therefore not counted as
unfinished production work.

Completed work-package documents remain preserved as evidence and retain an
explicit completed or verified status. Superseded plans remain preserved and
are excluded from the active backlog. Generated atomic requirements remain the
authority for exact forum/product IDs; the grouped totals below are not a
second requirement catalogue.

The prior clean-tree reconciliation was performed at `93a4595`. The final
release audit began at `ae4ac32`; a concurrent process published `462539c0`
and later left local `main` three documentation commits ahead of
`origin/main`. Because the shared tree remains concurrently modified, counts
below are inventory facts rather than a release-ready baseline.

## Current Result

The current implementation is not globally complete.

- The combined forum/product catalogue contains 38,377 atomic requirements:
  1,727 verified, 58 `in-progress`, and 36,592 still `discovered`.
- The canonical 170-requirement matrix currently labels 148 rows
  `implemented`, 9 not applicable, 10 externally blocked, and 3 partially
  implemented. `Implemented` is not treated as `verified`: the final audit
  found generic or invalid evidence on many rows and multiple required gates
  fail.
- Five supplemental Point 13 evidence rows add three verified records, one
  intentionally-not-applicable payment record, and one partially implemented
  advanced-events record; they are not part of the canonical count of 169.
- The Places execution ledger has 325 open and 23 completed checkboxes.
- The shared-directory-card ledger has 56 open and 117 completed checkboxes.
- The icon migration has zero actionable migration debt. Its plan remains only
  as a regression ratchet.
- Global page identity is implemented across its main migration waves, but its
  final exception, requirement-ID, query-baseline, cleanup, and body-copy audit
  remains open.

The counts above overlap by design. For example, Places and event work also
appear in the 36,650 unresolved atomic requirements. They must not be added
together as a single backlog total.

## Canonical Requirement Gaps

### Partially implemented

1. `PRD-PLACE-001`: remove the 500-record in-memory completeness ceiling and
   deliver indexed, database-backed filtering, ordering, and pagination under
   `PLA-P04`.
2. `PRD-PLACE-002`: replace account-local corrections, warnings, reviews,
   claims, and reports with shared authorized workflows; finish question
   moderation, notification, rate limiting, and answer-version history under
   `PLA-P02` and `PLA-P07` through `PLA-P11`.
3. `PRD-PLACE-003`: calculate emergency clinic open/species capability from
   canonical timezone-aware schedules, exceptions, and services under
   `PLA-P15`.
4. Supplemental `EVENT-P13-ADVANCED`: finish durable tickets/refunds,
   tracks/sessions depth, competitions, vendors/sponsors, volunteer shifts,
   QR/offline check-in, incidents/weather, certificates, and feedback.

### Externally blocked

1. `PRD-DEVICE-003`: select live GPS hardware/provider and credentials.
2. `PRD-DEVICE-004`: select and integrate a physical feeder provider.
3. `PRD-DEVICE-005`: select fountain/litter hardware with supported
   interlocks.
4. `PRD-DEVICE-006`: select a camera provider and define streaming, retention,
   and export-redaction contracts.
5. `PRD-DEVICE-007`: select enclosure and vehicle telemetry sensors.
6. `PRD-DEVICE-008`: select smart-door hardware with obstruction telemetry and
   physical execution proof.
7. `PRD-DEVICE-013`: select a reconnect-capable device gateway.
8. `PRD-DEVICE-014`: select vendor APIs for firmware, subscription, recall,
   vulnerability, and ownership workflows.
9. `PRD-DEVICE-015`: select a consented AI provider and approve its processing
   disclosures.
10. `TEST-COVERAGE-001`: install a PHP 8.5-compatible PCOV or Xdebug driver and
    run the 90% coverage gate.

The supplemental `EVENT-P13-PAYMENTS` row is deliberately not applicable until
a verified payment provider exists. It is not an unfinished implementation
claim.

## Combined Atomic Backlog

The exact individual open IDs and source text are already recorded in
`docs/requirements/forum-requirements.json` and
`docs/plans/forum-phase-requirement-index.md`. They can be listed without
ambiguity with:

```bash
jq -r '.requirements[]
    | select(.final_result != "verified" and .final_result != "intentionally-not-applicable")
    | [.requirement_id, .implementation_phase, .normalized_implementation_requirement]
    | @tsv' docs/requirements/forum-requirements.json
```

### Open source streams

| Source stream | Open IDs |
| --- | ---: |
| Original forum source | 2,566 |
| Forum extension and taxonomy | 3,888 |
| Pet-profile revision | 3,717 |
| Social-relationships revision | 2,988 |
| Content-feed revision | 3,953 |
| Communication revision | 3,877 |
| Community revision | 3,541 |
| Medical-record revision | 3,788 |
| Portal-architecture revision | 3,449 |
| Event-lifecycle revision | 4,883 |
| **Total** | **36,650** |

### Open phase results

| Phase | Open IDs | Remaining result |
| ---: | ---: | --- |
| 0 | 10 | Source-control evidence |
| 1 | 186 | Repository discovery evidence |
| 2 | 9 | Architecture and planning evidence |
| 3 | 0 | Complete and verified |
| 4 | 1,224 | Remaining category hierarchy and preservation |
| 5 | 821 | Taxonomy provenance, imports, search, and administration |
| 6 | 334 | Reputation, trust, badges, expiry, and abuse controls |
| 7 | 402 | Moderation coverage, transparency, appeals, and evidence |
| 8 | 3,003 | Remaining structured forum domains |
| 9 | 88 | Forum search, feeds, follows, collections, and notifications |
| 10 | 41 | Remaining Livewire and presentation states |
| 11 | 13 | Legacy analysis and restartable backfills |
| 12 | 29 | Production definitions and safe demo graph |
| 13 | 118 | Release controls |
| 14 | 176 | Remaining multilingual and global verification controls |
| 17 | 230 | Canonical pet aggregate and release boundary |
| 18 | 556 | Pet creation, identity facts, duplicate review, and audit data |
| 19 | 559 | Pet ownership, transfer, disputes, privacy, and discovery |
| 20 | 1,015 | Pet public profile, behavior, media, and social graph |
| 21 | 368 | Pet cross-domain integrations and discovery |
| 22 | 272 | Pet lifecycle, deletion, transfer continuity, and memorials |
| 23 | 192 | Pet safety, ownership theft, cruelty, and appeals |
| 24 | 231 | Pet localization and accessible/offline-safe interfaces |
| 25 | 294 | Pet metrics, scenarios, and release verification |
| 27 | 585 | Remaining follows, relationships, settings, and lists |
| 28 | 579 | Remaining social requests, blocks, reports, and appeals |
| 29 | 402 | Pet consent, safe meetings, check-ins, and incidents |
| 30 | 428 | Explainable recommendations and privacy-safe search |
| 31 | 492 | Messaging, group, event, walk, privacy, and notification links |
| 32 | 189 | Social responsive, accessible, localized interfaces |
| 33 | 189 | Social migration, metrics, operations, and release gates |
| 34 | 124 | Social end-to-end scenarios |
| 36 | 755 | Remaining publication, audience, media, and projection foundation |
| 37 | 433 | Content authoring, drafts, collaboration, and lifecycle |
| 38 | 1,154 | Stories, reactions, comments, distribution, and notifications |
| 39 | 287 | Private media processing, derivatives, delivery, and retention |
| 40 | 159 | Feed ranking, search, analytics, and controls |
| 41 | 379 | Content safety, fraud, moderation, copyright, and appeals |
| 42 | 262 | Authoritative domain projections and consent-bound AI |
| 43 | 233 | Content localization, accessibility, and recovery states |
| 44 | 291 | Content release and scenario verification |
| 46 | 703 | Canonical dialogs, participants, contacts, and identity |
| 47 | 503 | Messages, media, files, and structured sharing |
| 48 | 528 | Delivery, devices, workflow, archive, and search |
| 49 | 416 | Group communication |
| 50 | 292 | Audio/video calls after signaling and TURN approval |
| 51 | 196 | Reviewed E2EE and key lifecycle |
| 52 | 542 | Communication safety, AI, fraud, reports, and controls |
| 53 | 286 | Communication interface and data controls |
| 54 | 411 | Communication quality, release, and scenarios |
| 55 | 464 | Community identity, types, hierarchy, and participation |
| 56 | 713 | Community creation, privacy, membership, and invitations |
| 57 | 353 | Community roles, governance, rules, and consent |
| 58 | 164 | Structured community forum |
| 59 | 357 | Community knowledge base |
| 60 | 321 | Events, volunteering, fundraising, and finance |
| 61 | 477 | Community safety and moderation |
| 62 | 341 | Discovery, localization, interface, and reputation |
| 63 | 351 | Community lifecycle, data, quality, and release |
| 64 | 529 | Medical patient aggregate, creation, identifiers, and timeline |
| 65 | 345 | Medical roles, consent, temporary access, and break-glass |
| 66 | 474 | Encounters, diagnoses, observations, triage, and measurements |
| 67 | 378 | Allergies, medication, dosing, interactions, and reconciliation |
| 68 | 413 | Vaccination, prevention, laboratories, and corrections |
| 69 | 364 | Imaging, procedures, surgery, rehabilitation, and care plans |
| 70 | 416 | Emergency, providers, documents, insurance, and continuity |
| 71 | 394 | Medical revisions, security, retention, interoperability, and AI |
| 72 | 307 | Medical interface, terminology, quality, and release gates |
| 73 | 168 | Medical end-to-end scenarios |
| 74 | 3,449 | Complete canonical portal architecture |
| 75 | 4,883 | Complete event lifecycle |

## Places Execution Ledger

The exact 339 unchecked tasks remain in
`docs/plans/places-production-master-plan.md`. Their package distribution is:

| Package | Open | Completed |
| --- | ---: | ---: |
| PLA-P00 baseline and execution control | 13 | 3 |
| PLA-P01 red tests and immediate correctness | 11 | 4 |
| PLA-P02 relational domain foundations | 25 | 0 |
| PLA-P03 application, validation, and authorization | 17 | 0 |
| PLA-P04 scalable server-rendered directory | 20 | 0 |
| PLA-P05 canonical facts and presentation | 18 | 0 |
| PLA-P06 submission, duplicate review, and publication | 14 | 0 |
| PLA-P07 management, verification, and claims | 15 | 0 |
| PLA-P08 corrections and provenance | 12 | 0 |
| PLA-P09 warnings and safety moderation | 13 | 0 |
| PLA-P10 reviews and manager responses | 13 | 0 |
| PLA-P11 questions, official answers, and reports | 10 | 2 |
| PLA-P12 personal places and social planning | 16 | 0 |
| PLA-P13 media lifecycle | 12 | 0 |
| PLA-P14 generalized location, map, and routes | 12 | 0 |
| PLA-P15 emergency veterinary mode | 12 | 0 |
| PLA-P16 venues, events, access, and organizations | 13 | 0 |
| PLA-P17 Blade, interaction, localization, and accessibility | 18 | 0 |
| PLA-P18 performance, cache, security, and observability | 15 | 0 |
| PLA-P19 migration, backfill, factories, and seeding | 14 | 0 |
| PLA-P20 automated and browser verification | 20 | 0 |
| PLA-P21 rollout, operations, documentation, and publication | 13 | 0 |
| PLA-P22 explicit post-MVP provider/expansion backlog | 13 | 0 |
| **Total** | **339** | **9** |

The immediate next slice is the remaining dynamic-action test matrix, then
two-account review/warning/claim tests, then question moderation and history,
followed by shared reports, warnings, reviews, corrections, and claims.

## Portal And Events Packages

No package in `docs/plans/portal-events-completion-master-plan.md` is globally
closed. P02 organization authority and P03 place/location/venue authority have
verified foundations, but their downstream package scope remains open.

The unfinished packages are P00 factual inventory, P01 Point 12 evidence,
P02 organization depth, P03 location/venue depth, P04 public projections, P05
active context/navigation, P06 notifications/deep links, P07 calendar/feeds,
P08 search/discovery, P09 dashboards/workspaces, P10 settings, P11 command
palette/quick actions/feed, P12 event types, P13 builder/drafts/templates,
P14 recurrence/occurrences, P15 routes/group walks/weather, P16 eligibility,
P17 registration, P18 capacity/reservations/waitlists, P19 payments/refunds,
P20 announcements/conversations, P21 tickets/QR/check-in/offline attendance,
P22 safety/incidents/escalation, P23 training, P24 exhibitions/adoption, P25
conferences/online access, P26 competitions, P27 vendors/sponsors, P28
volunteers/staff shifts, P29 media/privacy/feedback/archive, P30 portal
integration, P31 repository-wide UI migration, P32 factories/demo world, P33
automated/browser/security/performance proof, P34 documentation/evidence, and
P35 final release/recovery.

## Shared Directory Cards

The exact 56 unchecked tasks remain in
`docs/plans/shared-directory-card-system-plan.md`. Completed repair and
migration waves are already checked. Open distribution:

| Package | Open |
| --- | ---: |
| P01 executable contracts | 2 |
| P02 primitive deprecation/variant decisions | 2 |
| P05 component API hardening | 5 |
| P06 interaction/footer consistency | 6 |
| P07 media/asset consistency | 9 |
| P08 accessibility matrix | 10 |
| P09 responsive/localization conditional cases | 4 |
| P10 wider card-family classification | 8 |
| P11 progressive migration waves | 7 |
| P12 ongoing documentation guardrail | 1 |
| P14 recovery/monitoring guardrails | 2 |
| **Total** | **56** |

## Global Page Identity

The remaining checkpoint in
`docs/plans/global-page-identity-standardization-plan.md` is:

1. assign the stable requirement ID and finish representative query baselines;
2. audit every classified detail/workspace exception;
3. finish the remaining priority-page RU/LT non-header fallback audit;
4. remove the unused historical message-details presenter/template chain and
   complete the final global follow-up audit and documentation cleanup.

## Completed, Superseded, And Historical Plans

The following are not unfinished backlog sources:

- authenticated portal, discovery, global linked-media navigation, canonical
  pet workspace, and the current zero-debt icon migration baseline;
- all focused forum work-package documents for implemented Phase 3, Phase 4
  category 21-24, Phase 6, Phase 7, the delivered Phase 8 domains, Phase 10,
  Phase 14, topic lifecycle/editor, and topic-type schema runtime;
- the content-feed foundation, social foundation/safety, organization-authority
  foundation, and place/location/venue-authority foundation packages;
- all focused pet-profile work packages currently present in `docs/plans`;
- the guest join plan, which is superseded by the authenticated portal
  boundary;
- all ten `docs/superpowers/plans` files, which are historical prototype plans
  and not current production trackers.

Their completion does not close the parent master plans or adjacent atomic
requirements.

## Verification Limitations Observed During This Audit

- `php -d memory_limit=-1 scripts/generate-forum-requirements.php --check`
  passes for all 38,377 records. The default 128 MB PHP limit is insufficient
  for this generator in the current environment.
- `php scripts/preserve-forum-source-prompt.php --check` cannot reconstruct the
  immutable prompt because the current local Codex history no longer contains
  the ten historical timestamped entries. The preserved file still passes its
  internal checksum and deterministic generation check; restoring the archived
  history is required for the reconstruction gate itself.
- The focused architecture suite currently reports 19 passing tests and one
  failure at that same source-reconstruction assertion. No application-code
  regression was reported by the other 19 architecture tests.
- `php scripts/generate-compliance-matrix.php --check` now performs a real
  byte comparison and passes in the attributable working tree. This proves
  generated-file parity only; the blanket `implemented` defaults and generic
  per-ID evidence remain release-blocking semantic defects.
