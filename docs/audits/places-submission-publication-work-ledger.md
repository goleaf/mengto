# Places Submission And Publication Work Ledger

Date: 2026-08-30
Branch: `main`
Baseline: `153ae45c2bc6864ec6061dc407d82be68a437c26`
Plan owner: `PLA-SUB-01` through `PLA-SUB-08` in
`docs/implementation-plan.md`

## Preservation Contract

- The shared tree was materially dirty before this package. Every pre-existing
  staged, unstaged, and untracked change remains user-owned and must be
  preserved byte-for-byte.
- Discovery and specialist work are read-only. The principal agent owns all
  implementation and documentation edits.
- Database verification uses only the repository's isolated test runner and
  temporary lifecycle scripts. No configured development or production
  database may be reset.
- A final attributable diff is frozen before independent review. Publication
  uses a temporary Git index and happens only after the required gates pass.

## Specialist Assignments

| Scope | Exclusive responsibility | Required deliverable | Edit authority | Status |
| --- | --- | --- | --- | --- |
| Workflow modeling | Current place submission entry points, canonical lifecycle, identities, data ownership, and UI/state map | Files inspected; lifecycle and entity graph; gaps; exact recommendations; risks; validation/test requirements | read-only | complete; separate submission aggregate and explicit publication transition selected |
| Duplicate detection | Deterministic normalization, candidate retrieval/scoring, canonical-organization and coordinate rules, race/idempotency behavior | Files inspected; signal/score specification; schema/index needs; privacy/race risks; tests | read-only | complete; normalized, coordinate, organization, pending-submission, and active merge-alias signals implemented as suggestions |
| Security and policy | Submitter/non-owner/moderator/place-manager/blocked/inactive matrix, protected redirects, rate limits, abuse and audit context | Files inspected; capability matrix; disclosure threats; exact controls; negative tests | read-only | complete; explicit eligibility, ownership, independent-review, manager-scope, redaction, rate, and audit controls implemented |
| Moderation and provenance | Confirm/link/merge/reject/request-info/reopen/restore state machine, merge history, rollback, redirect retention | Files inspected; transition table; invariant and transaction design; failure/recovery tests | read-only | complete; version-locked transactional transitions and append-only history implemented |
| Testing and browser | Pest/Livewire/schema/seed/concurrency matrix and connected browser journey design | Files inspected; executable test matrix; fixture strategy; isolation and browser commands; risks | read-only | complete; focused, two-process, lifecycle, seed, and desktop/mobile browser contracts implemented |
| Independent final review | Frozen attributable diff after implementation | Severity-ranked findings with paths/lines, reproduction, required fixes, and release recommendation | read-only and independent | in progress |

## Principal Decision Record

- A submission is a durable aggregate separate from `Place`; it begins in a
  controlled review state and cannot become discoverable through creation
  alone.
- Submitted facts are append-only field-level evidence. Publication/link/merge
  creates attributable canonical fact records without overwriting the original
  evidence.
- Duplicate signals create review candidates only. A human-authorized Action
  is required to link or merge.
- Old identifiers resolve through a retained redirect record only when both
  source disclosure and destination access are authorized.
- Submission and moderation idempotency are separate database-enforced
  boundaries. Notifications run only after a successful state transaction.

## Review Dispositions

The production and test diff is frozen after focused verification. Independent
review findings and principal dispositions are appended here before final
publication; no reviewer may edit tracked files.

## Resumed Specialist Revalidation

The resumed 2026-08-30 execution uses fresh read-only specialists. Their
reports supplement the completed discovery above and do not transfer edit or
publication authority away from the principal.

| Scope | Deliverable | Status |
| --- | --- | --- |
| Workflow modeling | Revalidate lifecycle, aggregate ownership, server-authoritative input, notification timing, and UI state completeness | pending |
| Duplicate detection | Revalidate normalization signals, deterministic ranking, privacy-safe candidate projection, race behavior, and advisory-only semantics | pending |
| Moderation and provenance | Revalidate every transition, independence rule, merge/restore rollback, immutable history, and redirect retention | pending |
| Security and policy | Revalidate role matrix, disclosure resistance, rate/abuse controls, idempotency, audit context, and blocked/inactive denial | pending |
| Testing and browser | Revalidate required Pest, concurrency, seed, migration, localization, accessibility, and browser coverage | pending |
| Independent final review | Review the frozen attributable diff after all valid specialist findings are dispositioned | pending |
