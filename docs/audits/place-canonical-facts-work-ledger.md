# Canonical Place Facts Specialist Work Ledger

Date: 2026-08-30
Branch and baseline: `main` at `ae4ac32`, aligned with `origin/main`
Canonical delivery: `PLA-CF-01` through `PLA-CF-10` in
`docs/implementation-plan.md`

The specialists below perform read-only discovery or independent review. They
do not edit tracked files. Each scope is exclusive, reports concrete paths and
requirements, distinguishes confirmed defects from design suggestions, and
returns a structured deliverable under `.superpowers/place-canonical-facts/`.
The principal owns schema integration, production edits, test execution,
finding disposition, documentation, staging, commit, and push.

| Specialist | Exclusive scope | Required deliverable | Status |
| --- | --- | --- | --- |
| Schema design | Relational entities, columns, enums, foreign keys, unique/check constraints, leading indexes, relationships, factories, and public/private data boundaries for all requested facts | `schema-design.md`: table-by-table proposal, invariants, conflicts with existing schema, and SQLite portability risks | queued |
| Schedules and DST | IANA timezone ownership, weekly/overnight intervals, date openings/closures, temporary closures, appointment-only semantics, exception precedence, DST gap/fold behavior, and opening-state vocabulary | `schedule-dst.md`: deterministic resolution algorithm, invalid/contradictory cases, and clock-controlled test matrix | queued |
| Service taxonomy | Stable service definitions, localized labels, veterinary emergency capability, offering availability, species and size eligibility, and non-inference rules | `service-taxonomy.md`: normalized taxonomy/relations, seed keys, emergency eligibility, and unavailable/unknown behavior | queued |
| Provenance and privacy | Sources/evidence, verification scope, observed/verified/freshness/expiry timestamps, immutable or versioned replacement history, encryption, visibility, and public/manager/moderator projections | `provenance-privacy.md`: lifecycle and threat review, retention rules, idempotency/concurrency identity, and projection allowlists | queued |
| Accessibility and localization | Semantic presentation of every status/source/freshness state, safe phone/source actions, keyboard/focus/touch/reflow requirements, and reviewed EN/LT/RU vocabulary | `accessibility-localization.md`: UI acceptance matrix, translation-key plan, and browser assertions | queued |
| Migration rollout | Additive migration ordering, compatibility/backfill/switch sequence, rollback/forward-fix threshold, production safety, and repeated seed implications | `migration-rollout.md`: migration lifecycle, bounded synchronization, validation checks, and operational rollback plan | queued |
| Testing | Test boundaries and fixtures for schema, policy, Actions, DST, overnight/holiday/closure, stale/unknown, service/species/size, privacy, idempotency, concurrency, query counts, localization, seeding, and browser flows | `testing.md`: red-first suite map, exact commands, data isolation, and missing coverage in current tests | queued |
| Final review | Frozen attributable diff after implementation; requirement-by-requirement correctness, security/privacy, data integrity, concurrency, performance, localization, accessibility, migration, and test-quality review | `final-review.md`: severity-ranked findings, reproduced evidence, and separate spec-compliance/code-quality verdicts | blocked until frozen diff |

## Principal Disposition Record

Every specialist finding will be recorded here as accepted, rejected with
evidence, deferred as explicitly out of scope, or superseded by a higher-order
requirement. Accepted findings must name the implementation/test path and the
affected verification rerun. No final-review finding may be closed solely by
assertion.
