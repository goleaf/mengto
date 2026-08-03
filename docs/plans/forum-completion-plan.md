# Combined Forum And Product Completion Plan

Date: 2026-08-03

Status: living canonical remaining-work roadmap

## Purpose

This plan answers what remains before the combined forum specification can be
called complete. The specification now includes the original forum and animal
taxonomy work plus the pet-profile, social-relationship, content-feed,
communication, community, and medical-record revisions.

This document does not replace the atomic catalogue or the domain master
plans. Exact requirement IDs and source coordinates remain authoritative in
`docs/requirements/forum-requirements.json` and
`docs/plans/forum-phase-requirement-index.md`.

An open requirement means that completion is not proven. It may represent:

1. a real implementation gap;
2. implemented behavior that still lacks exact tests or evidence; or
3. an external dependency that must be approved and delivered or recorded as
   genuinely blocked.

No work package may assume which case applies without inspecting the live
schema, code, tests, documentation, and generated evidence.

## Verified Baseline

The deterministic catalogue snapshot on `main` contains:

| Source stream | Requirements | Verified | Open |
| --- | ---: | ---: | ---: |
| Original forum source | 2,566 | 0 | 2,566 |
| Forum extension and taxonomy | 4,718 | 513 | 4,205 |
| Pet-profile revision | 4,135 | 205 | 3,930 |
| Social-relationships revision | 3,210 | 222 | 2,988 |
| Content-feed revision | 4,011 | 58 | 3,953 |
| Communication revision | 3,877 | 0 | 3,877 |
| Community revision | 3,576 | 35 | 3,541 |
| Medical-record revision | 3,867 | 79 | 3,788 |
| **Total** | **29,960** | **1,112** | **28,848** |

All 28,848 open records are currently `discovered`; there are no records
marked `in-progress`, `blocked`, or `intentionally-not-applicable`. The source
payload checksum is
`9f52b2f90c8f1d0dc1c957f0207b6bd89c9a57eaf3359838e51b6c377e25458d`.

## Definition Of Complete

The combined scope is complete only when all of the following are true:

- every one of the 29,960 atomic IDs is independently `verified`, or has a
  reviewed and evidenced `intentionally-not-applicable` decision;
- no requirement remains discovered, planned, in progress, or blocked;
- implementation, tests, documentation, generated matrix, progress report,
  and final audit show the same totals and behavior;
- every private read and mutation is server-authorized and viewer-scoped;
- migrations, backfills, rollback, factories, and production/demo seeders are
  non-destructive, repeatable, and SQLite-portable;
- EN/LT/RU, accessibility, responsive behavior, query budgets, cache
  invalidation, security, privacy, abuse controls, and operations are proven;
- the complete final quality-gate sequence passes on one clean `main` commit;
- `main` is clean, matches `origin/main`, and no completion claim relies on a
  different branch or worktree.

## Work-Package Protocol

Every package must follow this order:

1. Select an exact, dependency-safe set of open IDs from the generated phase
   index. Prefer 20-100 atoms unless one invariant cannot be split safely.
2. Classify every selected ID as an implementation gap, an evidence gap, or a
   real external blocker.
3. Record desired behavior, schema/index impact, migration/backfill/rollback,
   compatibility, authorization, privacy, security, abuse, localization,
   interface, accessibility, cache, query, factory, seed, test, documentation,
   and acceptance effects before editing production code.
4. Implement one cohesive operation at a time with Eloquent, Actions,
   policies, class-based multi-file Livewire, and passive Blade.
5. Run targeted tests first, then the relevant static, database, build,
   browser, and deterministic requirement checks.
6. Update evidence only for the exact independently proven IDs.
7. Inspect the complete staged diff, commit, and push directly on `main`.

## Dependency-Ordered Completion Waves

The eight waves below account for all 28,848 open IDs exactly.

| Wave | Scope | Phases | Open IDs |
| ---: | --- | --- | ---: |
| 1 | Core forum, taxonomy, trust, and moderation foundations | 3-7 | 3,098 |
| 2 | Canonical pet profile | 17-25 | 3,930 |
| 3 | Social relationships | 27-34 | 2,988 |
| 4 | Content feed and distribution | 36-44 | 3,953 |
| 5 | Communication and community interlock | 46-63 | 7,418 |
| 6 | Medical records | 64-73 | 3,788 |
| 7 | Remaining original forum workflows and interface | 8-12 | 3,174 |
| 8 | Global control evidence, release verification, and publication | 0-2, 13-14 | 499 |
|  | **Total** |  | **28,848** |

### Wave 0: Reconcile The Source Of Truth

Before production work resumes:

- rerun both deterministic source checks;
- regenerate the exact stream and phase status snapshot;
- synchronize `forum-current-progress.md`, the completeness audit, testing
  evidence, and this plan;
- identify stale claims that describe implemented code but lack atomic
  evidence;
- create the first exact Phase 3-7 reconciliation work package.

Wave 0 changes no requirement status by itself.

### Wave 1: Core Forum, Taxonomy, Trust, And Moderation

Open inventory:

| Phase | Remaining result | Open IDs |
| ---: | --- | ---: |
| 3 | Reconcile and finish additive schema, indexes, bindings, factories, policies, and compatibility backfills | 82 |
| 4 | Prove the complete 44-root/1,637-child category hierarchy, redirects, translations, cache invalidation, and preservation | 1,459 |
| 5 | Complete global taxonomy snapshots, provenance, imports, changes, search, administration, rollback, and measured budgets | 821 |
| 6 | Complete reputation, trust, badges, confirmations, conflicts, expiry, and anti-abuse behavior | 334 |
| 7 | Complete duplicate reports, entity coverage, moderation transparency, restrictions, appeals, recusal, and evidence controls | 402 |

Phase 4 and Phase 5 start with evidence reconciliation because substantial
foundations already exist. They must not be rewritten merely because their
atomic IDs are still open.

### Wave 2: Canonical Pet Profile

Execute the remaining pet plan in dependency order:

| Phase | Remaining result | Open IDs |
| ---: | --- | ---: |
| 17 | Finish canonical aggregate and release boundary | 230 |
| 18 | Complete creation, claims, identifiers, versioned facts, duplicate review, and audit data | 769 |
| 19 | Complete ownership, transfer, dispute, organization roles, privacy, and discoverability | 559 |
| 20 | Complete public projection, behavior, media, consent, social graph, and achievements | 1,015 |
| 21 | Complete private-safe cross-domain integrations and discovery | 368 |
| 22 | Complete lifecycle, deletion cooling-off, transfer continuity, and memorial behavior | 272 |
| 23 | Complete pet safety, ownership-theft, cruelty, restricted-species, evidence, and appeal workflows | 192 |
| 24 | Complete EN/LT/RU and accessible/offline-safe interfaces | 231 |
| 25 | Complete metrics, ideal scenarios, and release verification | 294 |

Pet identity, current control, privacy contraction, and lifecycle must be
proven before later social, content, community, and medical packages rely on
them.

### Wave 3: Social Relationships

| Phase | Remaining result | Open IDs |
| ---: | --- | ---: |
| 27 | Finish follows, typed relationships, settings, viewer-aware lists, and compatibility | 585 |
| 28 | Finish requests, blocks, restrictions, rate limits, minors, reports, appeals, and invalidation | 579 |
| 29 | Add pet-manager consent, neutral compatibility, safe meetings, check-ins, incidents, and termination | 402 |
| 30 | Add explainable recommendations, privacy-safe search, opt-out, diversity, and anti-enumeration | 428 |
| 31 | Integrate messaging, groups, events, walks, privacy, and notifications | 492 |
| 32 | Complete responsive, accessible, localized, weak-network interfaces | 189 |
| 33 | Complete migration, metrics, query budgets, operations, and release gates | 189 |
| 34 | Execute all social ideal scenarios end to end | 124 |

No social edge may grant pet management, medical, device, location, group, or
moderation authority.

### Wave 4: Content Feed And Distribution

| Phase | Remaining result | Open IDs |
| ---: | --- | ---: |
| 36 | Finish publication, audience, attribution, media relation, typed link, event, and projection foundation | 755 |
| 37 | Add authoring, drafts, scheduling, collaboration, versions, recovery, rights, and complaints | 433 |
| 38 | Add stories, reactions, comments, mentions, hashtags, reposts, collections, feeds, and notifications | 1,154 |
| 39 | Add private media processing, derivatives, captions, resumable delivery, retention, and restore | 287 |
| 40 | Add explainable ranking, search, analytics, experiments, and user controls | 159 |
| 41 | Complete feed safety, fraud, harassment, high-risk advice, moderation, copyright, and appeals | 379 |
| 42 | Complete authoritative domain projections and consent-bound optional AI | 262 |
| 43 | Complete localization, accessibility, local recovery, and honest weak-network states | 233 |
| 44 | Execute content release and ideal-scenario verification | 291 |

Audience contraction, blocks, moderation, and private media grants must be
checked at read time; copied domain status cannot become a competing truth.

### Wave 5: Communication And Community Interlock

Communication has 3,877 open IDs and community has 3,541. Execute them in this
dependency order:

1. Finish community identity, types, privacy, membership, roles, and rule
   governance in Phases 55-57.
2. Build canonical dialogs, messages, files, delivery, devices, archive, and
   search in Phases 46-48.
3. Build group communication in Phase 49 against the proven community
   identity and role model.
4. Deliver calls only after reviewed signaling and TURN infrastructure exists;
   deliver E2EE only after a reviewed multi-device protocol and key lifecycle
   exists (Phases 50-51).
5. Complete communication safety, fraud, moderation, interface, data controls,
   release, and scenarios in Phases 52-54.
6. Complete community forum, knowledge, events, volunteering, finance, safety,
   discovery, lifecycle, data, release, and scenarios in Phases 58-63.

Scanner, TURN, E2EE, native background execution, and external AI are real
infrastructure decisions. Missing infrastructure must create a visible
blocked requirement, never a simulated completion claim.

### Wave 6: Medical Records

| Phase | Remaining result | Open IDs |
| ---: | --- | ---: |
| 64 | Finish patient aggregate, creation, identifiers, merge, sources, timeline, and lifecycle | 529 |
| 65 | Finish roles, purpose/section/time consent, temporary access, break-glass, logs, disputes, and transfer | 345 |
| 66 | Add encounters, diagnoses, observations, symptoms, triage, measurements, devices, and trends | 474 |
| 67 | Normalize allergies, medication, dosing, administration, interactions, and reconciliation | 378 |
| 68 | Add vaccination, preventive plans, laboratory provenance, corrections, escalation, and trends | 413 |
| 69 | Add imaging, procedures, surgery, anesthesia, rehabilitation, care plans, and quality-of-life observations | 364 |
| 70 | Complete emergency, providers, documents, referral, messaging, insurance, continuity, and memorial workflows | 416 |
| 71 | Complete revisions, encryption, MFA, backups, retention, interoperability, search, research controls, and optional AI | 394 |
| 72 | Complete terminology, accessible interface, weak-network behavior, quality metrics, and release gates | 307 |
| 73 | Execute all medical ideal scenarios end to end | 168 |

One medical record must continue to follow one canonical pet while every read
and mutation uses current role, purpose, section, expiry, consent, and record
state.

### Wave 7: Remaining Original Forum Workflows

| Phase | Remaining result | Open IDs |
| ---: | --- | ---: |
| 8 | Reconcile and complete all remaining structured forum domains without copying authoritative private state | 3,003 |
| 9 | Complete privacy-first search, feed, following, saved searches, collections, notifications, and quiet hours | 88 |
| 10 | Complete class-based Livewire workflows and all presentation states | 41 |
| 11 | Complete bounded legacy analysis, ambiguity reports, review queues, and restartable backfills | 13 |
| 12 | Complete production definitions and environment-gated, idempotent demo graph | 29 |

This wave follows the domain revisions so forum integration can reference the
canonical pet, social, content, communication, community, and medical
boundaries instead of creating duplicate truth.

### Wave 8: Global Verification And Publication

The final 499 open control/release IDs span Phases 0-2 and 13-14.

1. Reread and prove source preservation, discovery, and architecture decisions
   for every source payload; evidence—not existing prose—closes Phases 0-2.
2. Run the complete requirement/source comparison and prove that every source
   line is represented exactly once.
3. Run migration, populated rollback/reapply, fresh/repeated seed, policy,
   concurrency, privacy, security, architecture, localization, query-budget,
   cache, full Pest, coverage, Pint, Larastan, Composer/NPM audit, production
   Vite build, and browser accessibility gates.
4. Exercise all critical cross-domain workflows at 320px, mobile, desktop,
   keyboard-only, reduced-motion, forced-colors, weak-network, and relevant
   locale boundaries.
5. Inspect placeholders, TODOs, hardcoded platform text, forbidden Blade,
   raw SQL, destructive operations, secrets, unstable identifiers, generated
   drift, and complete/staged diffs.
6. Synchronize every canonical document, matrix, progress record, audit,
   deployment/rollback note, operations guide, and changelog.
7. Declare completion only when the generated totals contain zero unresolved
   IDs and the clean `main` commit is pushed and observed on `origin/main`.

## Immediate Next Work Package

Create `docs/plans/forum-core-foundation-reconciliation-work-package.md` for
the first exact slice of open Phase 3-7 IDs. It must:

- compare each selected atom with the live schema, implementation, and tests;
- separate missing behavior from missing evidence;
- prioritize authorization, privacy, integrity, and preservation gaps;
- avoid rebuilding the category/taxonomy foundations already present;
- finish with exact evidence updates and the applicable package gates.

No production code should change until that work package names its exact IDs
and acceptance criteria.
