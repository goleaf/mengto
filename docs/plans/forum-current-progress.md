# Forum Current Progress

Last updated: 2026-07-31.

## Current Phase

Phases 0–2 are complete. Additive schema, forum categories, animal taxonomy,
reputation/confirmation, and moderation foundations have been implemented.
Phase 8 structured-community work and Phase 10 Livewire administration are in
progress. Verification remains incremental; no incomplete phase is described
as complete.

## Completed Evidence

- Combined source prompt preserved with SHA-256
  `6f8a7f987c336a2247755cae1c2fd66dea66d83cfbf038b5fe31aa848097d773`.
- 7,284 atomic requirements generated with full source coordinates.
- Every atomic requirement assigned to one primary phase.
- Existing forum, policies, requests, migrations, adjacent domains,
  translations, seeds, factories, and focused tests inventoried.
- Targeted baseline: 11 tests, 68 assertions, passed.
- First architecture/source/conflict decisions recorded.
- Fifteen additive migrations preserve all legacy forum, credential, listing,
  pet, and marketplace records.
- Deterministic forum manifest contains 44 roots and 1,637 children.
- Forum category synchronization, aliases, redirects, topic backfill, cache
  invalidation, and admin-created category preservation are covered.
- Versioned, checksummed, chunked, resumable taxonomy import supports analysis,
  validation, activation, cancellation, and rollback without activating failed
  imports.
- Core taxonomy, community groups, domestic classifications, and
  Catalogue of Life source registration are seeded idempotently.
- Append-only scoped reputation, answer voting/acceptance, audited trust,
  badges, confirmations, quorum/diversity limits, and reversals are covered.
- The unified moderation foundation contains all 88 reasons, 31 action
  definitions, private evidence, report events, cases, appeals, and recusals.
- Authorized class-based Livewire moderation operations now cover private
  triage, case assignment/reassignment, audited action recording, controlled
  recusal, and independent appeal review without exposing reporter identity.
- Class-based Livewire taxonomy selector and forum administration dashboard
  are implemented with bounded queries and direct authorization checks.
- Independent professional credential review, expiry/suspension projection,
  appeal review, private evidence handling, and admin Livewire controls are
  implemented.
- Adoption provider identity now reuses that independent review boundary:
  purpose-compatible owner credentials drive pending, verified, expired,
  rejected, suspended, and revoked public states; legacy seller trust cannot
  create an adoption identity badge.
- Adoption listings now synchronize to private structured cases with
  idempotent applications, optimistic locking, complete adoption/foster
  transitions, follow-up, append-only history, policy enforcement, taxonomy
  links, and a class-based Livewire workflow.
- Marketplace reports now write atomically to both the compatibility table and
  the unified polymorphic moderation pipeline.
- Lost/found now uses owned pet and global taxonomy links, encrypted historical
  animal snapshots, explicit lost/found/sighted/stolen/reunited states,
  immutable case events, protected idempotent contact relays, advisory
  duplicate detection, unified false-sighting/reward-scam reports, and
  privacy-safe archival that preserves the complete operational history.
- Requirement evidence is a deterministic overlay. `287` atomic requirements
  are verified with file/test evidence and none are currently marked
  in-progress.
- Current repository checkpoint: mentorship focused 29 / 117;
  architecture/localization/schema/factory/mentorship 899 / 43,799; full Pest
  1,263 / 46,373; full Pint passed; full Larastan 0 errors; fresh verifier
  passed 91 migrations and 135 tables with stable
  repeat-seed counts; the
  Vite 8.2.0 production build passed.
- Playwright verified the lost/found editor at 375x812 and 1440x900 with one
  page heading, no main-content overflow, no workflow target below 44px, and
  no current-page console warning or error.
- Playwright verified the Lithuanian adoption workflow at 375px and 1440px:
  localized fallback copy, one page heading, no horizontal overflow, no
  unnamed visible buttons, and no current-page console warnings or errors.
- Playwright also completed the moderation triage/assignment/action workflow
  and verified the administration view at 375px and 1440px with no root
  overflow, raw translation keys, unnamed visible buttons, or console errors.
- Playwright verified adoption provider identity at 375px and 1440px with one
  page heading, no root overflow, raw keys, private credential fields, unnamed
  visible buttons, warnings, or errors; both report checkbox labels expose
  44px touch targets.
- Collaborative guides are implemented through an additive schema, immutable
  history, independent review policy, optimistic edits, correction and
  collaborator Actions, a class-based Livewire editor, public locale variants,
  print/export, admin discovery, and repeatable demo synchronization. The
  focused guide/topic/knowledge/admin slice passed 37 tests and 245 assertions;
  the guide plus factory/seeder slice passed 730 tests and 2,209 assertions;
  the final full suite passed 1,050 tests and 43,663 assertions. Fresh
  migration/repeat-seed, Larastan, production build, and mobile/desktop browser
  checks also passed; all 32 scoped requirement IDs are verified.
- Community review panels and contextual notes are implemented through an
  additive schema, balanced trust-based reviewer assignment, conflict
  replacement, deadline enforcement, append-only events/versions, optimistic
  revision, moderator review, revalidation, appeal, EN/LT/RU, and a
  class-based Livewire interface. Focused tests passed 52 / 165; full Pest
  passed 1,172 / 44,853; Larastan, fresh/repeat seed, Vite, and mobile/desktop
  browser checks passed. All 61 scoped requirement IDs are verified.
- Peer mentorship is implemented for all thirteen required types with
  opt-in profiles, independent category/taxon scopes, transparent bounded
  matching, participant-only messaging, explicit block/report safety,
  optional immutable feedback, optimistic transitions, independent completion
  validation, and idempotent mentoring reputation/badges. Focused tests passed
  29 / 117; full Pest passed 1,263 / 46,373; Larastan, fresh/repeat seed, Vite,
  and mobile/desktop browser checks passed. All 47 scoped IDs are verified.

## Phase Counts

| Phase | Atomic requirements | Status |
| ---: | ---: | --- |
| 0 | 10 | verified |
| 1 | 186 | documented; final reread pending |
| 2 | 9 | documented |
| 3 | 82 | implemented; full-suite verification pending |
| 4 | 1,463 | implemented for recovered hierarchy; final traceability pending |
| 5 | 825 | core/import pipeline implemented; full external snapshot pending |
| 6 | 347 | foundation, panels/notes, and mentorship verified; additional trust packages remain |
| 7 | 463 | operations verified; duplicate grouping, transparency, and broad entity coverage remain |
| 8 | 3,345 | adoption, lost/found, and collaborative-guide packages verified; remaining structured domains planned |
| 9 | 93 | planned |
| 10 | 67 | in progress |
| 11 | 13 | topic category/taxonomy backfill implemented; reports pending |
| 12 | 29 | fixed definitions implemented; demo graph pending |
| 13 | 131 | planned |
| 14 | 221 | planned |

## Next Verified Pass

1. Reuse existing lost/found, expert, marketplace, care-journal, knowledge,
   pet, and social records as authoritative adjacent domains.
2. Add only missing structured-community relations and workflows.
3. Add policy, validation, factory, seed, and direct-action tests for each
   completed work package.
4. Extend privacy-first search and following without indexing restricted data.
5. Update evidence, rerun migration/seed checks, and perform the next phase
   completeness audit.

## Preservation Ledger

The implementation must preserve existing topics, replies, reactions/votes,
subscriptions, bookmarks, reports, attachments, pet profiles, adoption and
lost/found records, marketplace records, translations, and
administrator-created categories. No preservation claim is marked verified
until migration tests cover representative rows.
