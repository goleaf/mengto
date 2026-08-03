# Forum Current Progress

Last updated: 2026-08-03.

## Current Phase

Source preservation, atomic extraction, repository discovery, and domain
planning are documented for all ten source payloads, but their control IDs
still require exact evidence before Phases 0-2 can be called verified. The
combined catalogue has 1,161 verified IDs and 37,216 open IDs. Forum/taxonomy,
pet, social, content, community, and medical foundations are implemented in
independently evidenced slices. Communication, complete portal architecture,
the expanded event lifecycle, and all remaining depth/release packages stay
open. Verification remains incremental, and no incomplete phase is described
as complete. The dependency-ordered remaining work is in
`docs/plans/forum-completion-plan.md`.

## Completed Evidence

- Master source prompt plus dated pet-profile, social, content, communication,
  community, medical, portal-architecture, and event-lifecycle revisions
  preserved
  with SHA-256
  `cbb7d3a36f3750106c4751191ddd7d882d922ce0ae0e0b12aed318c809206ea1`.
- 38,377 atomic requirements generated with full source coordinates. The
  4,135 `pet.*`, 3,210 `social.*`, 4,011 `content.*`, 3,877
  `communication.*`, 3,576 `community.*`, 3,867 `medical.*`, 3,449
  `portal.*`, and 4,968 `event.*` records did not renumber preceding IDs.
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
- Requirement evidence is a deterministic overlay. `1,161` atomic requirements
  are verified with file/test evidence and none are currently marked
  in-progress.
- The pet-profile foundation preserves the existing aggregate while adding
  permanent identity, typed lifecycle, timed multi-manager permissions,
  layered privacy, append-only events, versioned facts, stable URLs/QR,
  idempotent backfill, and localized class-based Livewire workflows. Its
  focused compatibility slice passed 21 tests and 1,603 assertions; the final
  serial suite passed 1,748 tests and 68,172 assertions. Exactly 205 pet IDs
  are verified; all remaining pet requirements stay open.
- The social revision is preserved and atomized across 18 domains and phases
  27-34. The canonical actor/request/relationship/audit foundation, bounded
  backfill, policies, class-based Livewire relationship center, translations,
  and profile-level controls are implemented. Exactly 222 social IDs are
  verified after the foundation and account-safety packages passed focused,
  full-suite, migration/seed/rollback, static-analysis, build, cache, and
  desktop/mobile/320px browser gates. Later social packages remain open.
- The content revision is preserved and atomized across 22 domains and phases
  36-44. Its existing-system audit distinguishes durable domain content from
  private prototype state, and its ADRs define canonical publication,
  read-time audience, original-preserving distribution, independent media,
  authoritative typed links, unified moderation, and workerless processing
  boundaries. The first canonical publication package verifies 58 content
  IDs; 3,953 content IDs remain open.
- Communication has 3,877 open IDs and no production atom is yet verified.
  Community profile-scoped membership verifies 35 IDs; 3,541 community IDs
  remain open. Canonical medical patient identity/access and explicit
  knowledge states verify 79 IDs; 3,788 medical IDs remain open.
- The complete portal-architecture revision contains 3,449 open IDs in Phase
  74. Existing authenticated entry and module shells are implementation
  foundations only; no `portal.*` atom is verified yet.
- The complete event-lifecycle revision contains 4,968 open IDs in Phase 75.
  The durable event aggregate and the current lifecycle/occurrence/version
  extension are foundations only; no `event.*` atom is verified yet.
- Latest recorded complete serial repository checkpoint: 2,040 tests and
  73,559 assertions.
- Current repository checkpoint: expert-session plus architecture verification
  passed 31 tests and 22,249 assertions. The detached `f1e2fcc` package
  snapshot passed full Pint and Larastan, 1,554 serial tests and 54,317
  assertions, and 96 migrations across 165 tables with stable repeat-seed
  counts. Composer/npm audits and the Vite 8.2.0 production build passed. The
  larger current shared worktree also passed 1,594 tests and 56,870 assertions.
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
- Persistent groups are implemented with four visibility states, six roles,
  reviewed membership, expiring invitations, restriction/ban/leave,
  ownership transfer, close/reopen/archive, append-only events, unified
  reports, taxon focus, privacy-scoped discovery, deterministic definitions,
  demo data, and class-based Livewire directory/workspace/management. Focused
  tests passed 22 / 1,208; full Pest passed 1,338 / 48,931; fresh/repeat seed,
  Larastan, Vite, and mobile/desktop browser checks passed. All 32 scoped IDs
  are verified.
- Group topics, guides, events, announcements, private files, and polls are
  now durable and membership-scoped. All three ballot modes, voter/result
  visibility modes, editable/final behavior, trusted/location eligibility,
  timestamp-derived closure, private downloads, repeatable demo data, and a
  class-based accessible Livewire workspace are verified. Focused tests passed
  18 / 72; the final full Pest suite passed 1,384 / 50,006; fresh/repeat seed,
  Larastan, Vite, and desktop/mobile browser checks passed. All 24 scoped IDs
  are verified.
- Forum progress journals are implemented for all eleven named types plus a
  neutral legacy type. Dated entries, normalized measurements, milestones,
  setbacks, selected collaborators, comments, immutable edit history, private
  content-validated media, textual progress alternatives, export, archive,
  explicit legacy backfill, and non-punitive language are covered. Focused
  tests passed 17 / 164; the final full suite passed 1,437 / 51,568;
  fresh/repeat seed, Larastan, Vite, cache compilation, and real
  mobile/desktop Livewire flows passed. All 27 scoped IDs are verified.
- Events, attendance, and clubs now use a durable relational aggregate linked
  to existing groups and global taxa. Capacity and waitlist changes are
  transactional, access details are encrypted and policy-scoped, organizer
  verification remains independent, event history is append-only, reports
  reuse unified moderation, and stable legacy URLs/backfill are preserved.
  Focused tests passed 18 / 125; fresh/repeat seed, rollback, Larastan, Pint,
  Vite, cache compilation, and real mobile/desktop Livewire search and
  registration flows passed; the isolated serial suite passed 1,514 / 53,062.
  All 27 scoped IDs are verified.
- Verified professional question sessions use independently reviewed current
  credentials, exact scope and jurisdiction, timestamp-derived question
  windows, private moderated queues, idempotent answers, immutable corrections,
  non-destructive archives, unified reports, and explicit medical/legal
  non-authority language. Focused tests passed 31 / 22,249 and the expanded
  regression slice passed 1,108 / 52,428; the detached package snapshot passed
  1,554 / 54,317. A real mobile/desktop localized flow covered submission,
  moderation, answer, correction, report, and archive without overflow, raw
  keys, undersized primary controls, or console errors. All 18 scoped IDs are
  verified.
- Forum topics now use a durable lifecycle boundary with canonical and
  compatibility states, optimistic and row locking, immutable events,
  category-specific staleness and necropost rules, reversible removal,
  update requests, controlled bumping, merge/redirect history, and encrypted
  legal holds. The implementation preserves topic identity and every existing
  relation, performs no scheduled write-on-read transition, and exposes a
  policy-scoped class-based Livewire panel. Focused tests passed 13 tests and
  133 assertions; after integration with the current `origin/main`, the full
  serial suite passed 1,656 tests and 57,951 assertions in 94.750 seconds on
  Livewire 4.3.4. Fresh and repeated seeding, full Larastan, Pint, Vite,
  dependency audits, and real mobile/desktop browser checks also passed. All
  33 scoped IDs are verified.
- The complete source-section 71 forum accessibility contract is implemented:
  focusable linked validation summaries, semantic tables, meaningful media
  descriptions, escaped video transcripts, optional content-validated WebVTT
  captions, localized legacy alternatives, 44px controls, contrast/reflow
  guards, textual map alternatives, and no drag-only path. Focused checks
  passed 28 / 22,936; the full suite passed 1,666 / 58,350; fresh/repeat seed,
  full Larastan/Pint, audits, Vite/cache compilation, and a real headless
  Chrome desktop/mobile/320px/login/invalid-submit/admin flow passed. All 24
  scoped IDs are verified.
- The source-section 72 multilingual contract is implemented through the
  existing EN/LT/RU architecture: reviewed system category and moderation
  definitions, recipient-locale notifications, full-document locale changes,
  source-preserving human guide translation provenance, private-source
  authorization, and verified common-name to exact-scientific-name fallback.
  The final focused multilingual/guide/auth/localization/architecture slice
  passed 75 tests and 56,753 assertions; the final suite passed 1,684 tests
  and 64,597 assertions. Fresh/repeat seed passed across 99 migrations and 172
  tables; Pint, Larastan, audits, Vite/cache compilation, and reproducible
  EN/LT/RU headless-Chrome checks passed. All 41 scoped IDs are verified.
- The first Phase 3 reconciliation slice proves the normalized configurable
  topic-type foundation without rebuilding it. All source-listed type keys
  have active versioned definitions, EN/LT/RU translation contracts, stable
  internal identity, idempotent synchronization, preserved custom rows and
  topic relations, and versioned structured JSON instead of per-type columns.
  The focused contract passed 3 tests and 486 assertions; the related slice
  passed 61 tests and 26,837 assertions; the full suite passed 2,040 tests and
  73,559 assertions. Exactly 28 scoped persistence IDs are verified while the
  unimplemented configurable capabilities remain open.
- The second Phase 3 reconciliation slice verifies 21 exact database
  correctness IDs. Portable vote/reaction constraints, enum casts,
  moderation-case optimistic/idempotent closure, canonical accepted-answer
  replacement, populated rollback/reapply, and constant closure query growth
  are proven. The final combined suite passed 2,303 tests and 76,179
  assertions; fresh/repeat seed passed 117 migrations and 196 tables with a
  stable 5-user count; full Pint/Larastan, audits, Vite/cache, deterministic
  generation, and loopback EN/LT/RU browser gates passed.

## Requirement Status Snapshot

| Source stream | Requirements | Verified | Open |
| --- | ---: | ---: | ---: |
| Original forum source | 2,566 | 0 | 2,566 |
| Forum extension and taxonomy | 4,718 | 562 | 4,156 |
| Pet-profile revision | 4,135 | 205 | 3,930 |
| Social-relationships revision | 3,210 | 222 | 2,988 |
| Content-feed revision | 4,011 | 58 | 3,953 |
| Communication revision | 3,877 | 0 | 3,877 |
| Community revision | 3,576 | 35 | 3,541 |
| Medical-record revision | 3,867 | 79 | 3,788 |
| Portal-architecture revision | 3,449 | 0 | 3,449 |
| Event-lifecycle revision | 4,968 | 0 | 4,968 |
| **Total** | **38,377** | **1,161** | **37,216** |

The exact per-phase open counts and execution order are maintained in
`docs/plans/forum-completion-plan.md`; the generated phase index remains the
authority for individual IDs.

## Next Verified Pass

1. Select the next exact dependency-safe slice from the 33 open Phase 3 IDs;
   do not infer a global schema claim from the verified 21-ID package.
2. Continue the current Phase 75 event foundation: lifecycle migrations,
   authorization, backfill idempotency, query budgets, factories, seeding,
   localization, and compatibility tests. Do not promote the broad 4,968-ID
   stream from one foundation slice.
3. Reconcile the Phase 74 and Phase 75 sources into exact work packages,
   then update only independently evidenced IDs.
4. Repeat the package protocol and its complete gates without a time, token,
   package-count, or branch cutoff until the generated open total is zero.

## Preservation Ledger

The implementation must preserve existing topics, replies, reactions/votes,
subscriptions, bookmarks, reports, attachments, pet profiles, adoption and
lost/found records, marketplace records, translations, and
administrator-created categories. No preservation claim is marked verified
until migration tests cover representative rows.
