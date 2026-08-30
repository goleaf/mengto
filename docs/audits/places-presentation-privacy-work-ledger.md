# Places Presentation And Privacy Work Ledger

Date: 2026-08-30
Branch: `main`
Scope: `PLA-P12` through `PLA-P16`
Status: discovery pending

## Preservation Boundary

The shared tree contains unrelated in-progress Place submission,
contribution/moderation, canonical-fact, scalable-directory, and email-
verification work. Those changes are user-owned. Specialists are read-only,
must not edit tracked files, and write their reports only to the exclusive
`/tmp/pawcircle-ppb-*.md` paths below. The principal owns every code,
documentation, test, review-disposition, Git, and publication decision.

The attributable baseline is the exact working-tree snapshot captured after
the canonical `PLA-PPB-*` plan section was added. Publication uses a temporary
Git index and excludes all pre-existing hunks and untracked files.

## Specialist Assignments

| ID | Specialist | Exclusive scope | Required deliverable | Report path | Status |
| --- | --- | --- | --- | --- | --- |
| PPB-A1 | Private media | Existing private/public image pipelines, Place media needs, upload validation, transforms, moderation, delivery, deletion, retention, factories/tests | Concrete reuse map, threats, exact recommended files/interfaces/tests, conflicts with dirty work | `/tmp/pawcircle-ppb-media.md` | pending |
| PPB-A2 | Location privacy | Public region/approximate point, encrypted exact fields, grants, recipients, purpose, expiry, revocation, audit, HTML/cache/notification/export leak surfaces | Data-flow and leak map, exact gaps, tests, safe migration/rollback recommendations | `/tmp/pawcircle-ppb-location.md` | pending |
| PPB-A3 | Social planning | Saves, follows, collections, visits, check-ins, invitations, encrypted-versus-relational decision, social graph/block/recipient constraints | State classification, lifecycle/retention/idempotency design, exact gaps/files/tests | `/tmp/pawcircle-ppb-social.md` | pending |
| PPB-A4 | Maps | Server list/text alternative, current map payload and JavaScript lifecycle, approximate-coordinate boundary, focus/selection/teardown | Progressive-enhancement audit, private serialization risks, exact UI/JS/test changes | `/tmp/pawcircle-ppb-maps.md` | pending |
| PPB-A5 | Route and geocoding providers | Existing integrations/config/HTTP patterns and optional geocoder/router contracts | Provider interface/DTO/config/error/fallback design with timeout, 429, response-size and no-network tests | `/tmp/pawcircle-ppb-providers.md` | pending |
| PPB-A6 | Venue integration | Place/Venue/VenueArea/Organization/ForumEvent identity, policy, grant, event-registration, archive/merge boundaries | Reuse map, missing management/reveal flows, cross-tenant/privacy tests, no-duplication checks | `/tmp/pawcircle-ppb-venues.md` | pending |
| PPB-A7 | Accessibility | Place list/detail/map/media/personal/grant/venue mobile and keyboard contracts across EN/LT/RU | WCAG/reflow/focus/touch/text-alternative findings and repeatable browser scenarios | `/tmp/pawcircle-ppb-accessibility.md` | pending |
| PPB-R1 | Independent final privacy reviewer | Frozen attributable diff only after implementation; no implementation role | Severity-ranked privacy/security findings, reproduction steps, requirement coverage, release verdict | `/tmp/pawcircle-ppb-final-review.md` | blocked on frozen diff |

## Principal Reconciliation Contract

1. Reproduce every material discovery finding before using it.
2. Resolve cross-scope decisions against canonical requirements and record the
   result in `docs/implementation-plan.md` before irreversible work.
3. Begin each behavior with an observed failing focused test.
4. Keep specialists independent from final review; PPB-R1 receives only the
   frozen attributable diff and canonical requirements.
5. Record each final finding as accepted, fixed, rejected with evidence, or
   external blocker. Rerun every affected check after a fix.

## Verification Ledger

| Gate | Command/evidence | Result |
| --- | --- | --- |
| Starting branch/status/diff | `git branch --show-current`, status, staged/unstaged/untracked inventory | captured; materially dirty shared tree |
| Focused red/green contracts | Pending | pending |
| Private-file security | Pending | pending |
| Grant expiry/revocation/audit | Pending | pending |
| Provider no-network/error matrix | Pending | pending |
| Migration/backfill/seed | Pending | pending |
| Pint/Larastan/full Pest | Pending | pending |
| NPM/Vite/cache/browser | Pending | pending |
| Independent final privacy review | Pending frozen diff | pending |
| Temporary-index staged diff/commit/push | Pending all gates | pending |
