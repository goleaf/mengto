# Places Current Progress

Updated: 2026-08-03
Plan: `docs/plans/places-production-master-plan.md`
Audit: `docs/audits/places-production-readiness-audit.md`
Current package: `PLA-P00 — Truthful Baseline And Execution Control`
Delivery status: planning and audit in progress; production completion work has
not started under the new plan
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
| Dynamic place actions | Blocked by known defect | Shared request allow-lists twelve fixture slugs |
| Shared corrections/warnings/reviews/questions | Open | Stored per account in encrypted `places.state.v1` |
| Shared claims and moderation reports | Open | Stored per account; no receiving reviewer/moderation queue |
| Canonical facts and schedules | Open | Rich content still primarily fixture/default-driven |
| Creation and duplicate review | Partial | Canonical place persists; accepted fields, duplicate workflow, and idempotency contract remain incomplete |
| Manager and moderator operations | Open | Policies/foundation exist; complete workspaces and transitions do not |
| Emergency canonical ranking | Open | Call-first UI exists; schedule/capability source is not normalized for all clinics |
| Full accessibility/browser matrix | Open | Current focused 1440px/375px evidence is narrower than the product matrix |
| Production release | Not eligible | Required functional, migration, static, full-suite, browser, and operations gates remain open |

## Highest-Risk Findings

1. A newly persisted place can render but fails normal mutations because its
   stable key is not in the static target allow-list.
2. Shared-looking contributions are isolated to the submitting account and
   cannot be managed across accounts.
3. Non-anonymous review/question authorship is hard-coded to Mia Carter.
4. Success messages claim community, verification, and moderation delivery
   that the current storage path does not provide.
5. The directory silently excludes accessible records after its 500-row
   catalogue cap.
6. Emergency ranking and most rich facts are not yet driven by canonical
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

1. Add red tests for dynamic place mutations and inaccessible targets.
2. Add two-account red tests for question/answer, review visibility,
   warning/report moderation visibility, and claim review.
3. Replace static target validation with authorized canonical place resolution.
4. Implement the first shared relational vertical slice for place
   question/official answer.
5. Use that proven pattern for reports, warnings, reviews, corrections, and
   claims in dependency order.

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

Implementation checks will be appended here when work begins. Planning
documents alone are not implementation evidence.
