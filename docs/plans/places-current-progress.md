# Places Current Progress

Updated: 2026-08-30
Status: incomplete; 23 Places task IDs are complete and 325 remain open.
Plan: `docs/plans/places-production-master-plan.md`
Audit: `docs/audits/places-production-readiness-audit.md`
Current package: `PLA-P06 — Place Submission, Duplicate Review, And Publication`
Delivery status: all fourteen PLA-P06 requirements are implemented and focused
verification passes; independent review and final repository publication gates
are in progress
Last verified Places foundation commit: `f9254e2`

## Preserved Verified Baseline

The package recorded in
`docs/plans/portal-place-location-venue-authority-work-package.md` remains the
verified foundation for canonical place/venue authority, privacy, dynamic
routes, events, factories, seeding, and focused browser behavior. Its evidence
must not be read as proof that all Places MVP community and management
workflows are production-complete.

## Current Gate State

| Gate | State | Evidence or blocker |
| --- | --- | --- |
| Canonical place/venue authority | Verified foundation | Authority work package |
| Complete server-rendered directory at scale | Partial | Stable name search uses database pagination and direct detail/action lookup queries one authorized stable key or slug; advanced in-memory filter modes still begin from a 500-record catalogue |
| Dynamic place actions | Partial | Static twelve-slug validation is removed; direct authorized lookup, dynamic save, and inaccessible-place regressions pass, while the full action matrix remains open |
| Shared corrections/warnings/reviews/questions | Partial | Questions/official answers are relational and cross-account; corrections, warnings, and reviews remain in encrypted `places.state.v1` |
| Shared claims and moderation reports | Open | Stored per account; no receiving reviewer/moderation queue |
| Canonical facts and schedules | Open | Rich content still primarily fixture/default-driven |
| Creation and duplicate review | Verified for PLA-P06 | Submission aggregate, field provenance, normalized/alias candidates, member choices, idempotency, review queue, and controlled publication are implemented and focused tests pass |
| Manager and moderator operations | Partial beyond PLA-P06 | PLA-P06 link, approve, request-information, reject, reopen, publish, merge, and restore Actions are policy-scoped and audited; the broader PLA-P07 management workspace remains open |
| Emergency canonical ranking | Open | Call-first UI exists; schedule/capability source is not normalized for all clinics |
| Full accessibility/browser matrix | Open | Current focused 1440px/375px evidence is narrower than the product matrix |
| Production release | Not eligible | Required functional, migration, static, full-suite, browser, and operations gates remain open |

## Highest-Risk Findings

1. Most shared-looking contribution types remain isolated to the submitting
   account and cannot be managed across accounts.
2. The attributable final-audit slice now derives non-anonymous review name
   and Unicode initials from the authenticated account and ignores browser
   author fields; the wider relational review workflow remains open.
3. Corrections, warnings, reviews, claims, and reports outside PLA-P06 still
   require their planned relational moderation boundaries.
4. Advanced directory modes can still exclude accessible records after the
   500-row in-memory catalogue cap; direct detail/action lookup and the stable
   name-sorted directory no longer share that ceiling.
5. Emergency ranking and most rich facts are not yet driven by canonical
   schedules, capabilities, and provenance.

## Decisions Pending

- editable canonical slug policy beyond retained merge redirects;
- first-release review eligibility/update rules;
- warning publication/moderation/expiry rules;
- claim verification methods and reviewer roles;
- immediate relational migration versus compatibility retention for private
  saves/follows/collections;
- MVP versus post-MVP scope for visible check-ins and collaborative
  collections.

These decisions block irreversible schema or product claims, not the safe red
tests for known defects.

## Next Exact Work

1. Freeze and independently review the attributable PLA-P06 implementation,
   test, documentation, and migration diff.
2. Reproduce and disposition every material reviewer finding, then rerun each
   affected focused gate.
3. Run the final full Pest, Pint, Larastan, dependency, build, generator,
   isolated lifecycle, diff, and secret checks recorded under `PLA-SUB-08`.
4. Publish only if every required gate permits it; otherwise retain the exact
   blocker and do not create a completion commit.

## Preservation Ledger

- Work only on `main`.
- Preserve concurrent unrelated user-owned changes.
- Do not modify or stage a dirty shared file without first separating and
  reviewing ownership.
- Use additive migrations and a temporary Git index for attributable commits.
- Run database-backed tests sequentially.
- Never mark a package or requirement verified without current observed gate
  evidence.

## Evidence Log

| Date | Scope | Result |
| --- | --- | --- |
| 2026-08-03 | Repository and canonical-document audit | Completed; 26 grouped readiness findings recorded |
| 2026-08-03 | Unlimited completion ledger | Created with packages PLA-P00 through PLA-P22 and append-only task IDs |
| 2026-08-03 | Current Places regression baseline | 33 tests passed with 293 assertions in 3.914 seconds |
| 2026-08-03 | Affected Places suite | 43 tests passed with 353 assertions in 4.272 seconds |
| 2026-08-03 | Full serial suite in shared tree | 2,627 passed with 83,133 assertions; 8 unrelated failures from concurrent untracked `DiscoveryPreferenceFactory` not extending `ApplicationFactory` |
| 2026-08-03 | Exact isolated commit slice: Larastan and full Pest | Passed: zero Larastan errors; 2,604 tests and 82,240 assertions |
| 2026-08-03 | Exact isolated commit slice: fresh migration, full seed, and repeat seed | Passed: 128 migrations, 213 tables, stable user count 5 |
| 2026-08-30 | PLA-P06 focused action, policy, schema, Livewire, and normalization contracts | Passed; the two-process matching-submission race also passes in isolation after an intermittent PHP signal 11 in one combined run |
| 2026-08-30 | Fresh isolated root seed and complete migration cycle | Passed: 140 migrations, 227 tables, full rollback/reapply, and exactly 10 users before and after repeat seed |
| 2026-08-30 | Dedicated Places browser journey | Passed: desktop/mobile directory/detail, invalid recovery, pending submit, protected duplicate, touch target, overflow, localization-key, and console checks |
| 2026-08-30 | Static, dependency, and frontend gates before final review | Pint and Larastan passed; Composer validation/platform/audit passed; official-registry npm audit reported zero vulnerabilities; production Vite build passed |

The remaining package checkboxes stay open until their specific runtime and
quality gates are observed.
