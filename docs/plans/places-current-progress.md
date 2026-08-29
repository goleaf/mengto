# Places Current Progress

Updated: 2026-08-03
Status: incomplete; 9 Places task IDs are complete and 339 remain open.
Plan: `docs/plans/places-production-master-plan.md`
Audit: `docs/audits/places-production-readiness-audit.md`
Current package: `PLA-P01 / PLA-P11 — Immediate Correctness And First Shared Workflow`
Delivery status: implementation in progress; the dynamic target boundary and
first relational question/answer slice are implemented and targeted-tested
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
| Complete server-rendered directory at scale | Open | Catalogue loads at most 500 records before in-memory filters/pagination |
| Dynamic place actions | Partial | Static twelve-slug validation is removed; dynamic save and inaccessible-place regressions pass, while the full action matrix remains open |
| Shared corrections/warnings/reviews/questions | Partial | Questions/official answers are relational and cross-account; corrections, warnings, and reviews remain in encrypted `places.state.v1` |
| Shared claims and moderation reports | Open | Stored per account; no receiving reviewer/moderation queue |
| Canonical facts and schedules | Open | Rich content still primarily fixture/default-driven |
| Creation and duplicate review | Partial | Canonical place persists; accepted fields, duplicate workflow, and idempotency contract remain incomplete |
| Manager and moderator operations | Open | Policies/foundation exist; complete workspaces and transitions do not |
| Emergency canonical ranking | Open | Call-first UI exists; schedule/capability source is not normalized for all clinics |
| Full accessibility/browser matrix | Open | Current focused 1440px/375px evidence is narrower than the product matrix |
| Production release | Not eligible | Required functional, migration, static, full-suite, browser, and operations gates remain open |

## Highest-Risk Findings

1. Most shared-looking contribution types remain isolated to the submitting
   account and cannot be managed across accounts.
2. Non-anonymous review authorship is still hard-coded to Mia Carter; question
   authorship now comes from the authenticated user.
3. Success messages still claim community, verification, and moderation delivery
   that the current storage path does not provide.
4. The directory silently excludes accessible records after its 500-row
   catalogue cap.
5. Emergency ranking and most rich facts are not yet driven by canonical
   schedules, capabilities, and provenance.

## Decisions Pending

- canonical URL and slug-alias policy;
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

1. Complete dynamic target tests for every remaining Places action and malformed
   or stale identifiers.
2. Add two-account red tests for review visibility,
   warning/report moderation visibility, and claim review.
3. Add question moderation, manager notifications, rate limits, answer versions,
   and correction history.
4. Use the proven question/answer pattern for reports, warnings, reviews,
   corrections, and claims in dependency order.

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

The remaining package checkboxes stay open until their specific runtime and
quality gates are observed.
