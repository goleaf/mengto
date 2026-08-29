# Forum Phase 4 Animal-Science Work Ledger

Date: 2026-08-30

This ledger coordinates read-only discovery and independent review for the
category-25 package. The principal agent is the sole tracked-file editor and
owns all cross-module decisions, integration, tests, evidence, commits, and
pushes.

## Protected Baseline

- Branch and initial task HEAD: `main` at
  `fdaf7292a152ae61b85e17cf1ce69449d6d4292f`, matching `origin/main`.
- The tree already contains a large staged database/repository/auth delivery,
  three unstaged documentation edits, and additional untracked database tests.
  Those paths are user-owned and must remain byte-present.
- Publication uses a temporary `GIT_INDEX_FILE`; no reset, checkout, clean,
  force-push, history rewrite, broad add, or destructive database target is
  permitted.
- Selected requirements: exactly `forum.category.0237` through
  `forum.category.0294`. `animal.taxonomy.0021` and
  `forum.moderation.0012` are excluded later-phase dependencies.

## Specialist Workstreams

| ID | Specialist | Exclusive read-only scope | Structured deliverable | Status |
| --- | --- | --- | --- | --- |
| F4-AS-A1 | Requirements and source analyst | Immutable source section, generated JSON/index/matrix/evidence, prior Phase 4 package records | Exact 58-ID inventory; dependency exclusions; stale-evidence findings; required generator/doc updates | pending |
| F4-AS-A2 | Architecture, database, and security analyst | Manifest, checksum/loader/synchronizer, migrations, category/translation models, policies, caches, factories, seeders | Source-to-persistence map; preservation/authorization/cache/query risks; implementation gaps versus evidence gaps; exact tests | pending |
| F4-AS-A3 | UI, localization, accessibility, and test analyst | Forum category presenters/Livewire/Blade, EN/LT/RU catalogues, nearby category tests, browser scripts | Render/localization/a11y/responsive contract; missing coverage; proposed red test and target commands | pending |
| F4-AS-R1 | Independent final reviewer | Frozen attributable diff and observed verification ledger; no implementation role | Severity-ranked findings, requirement-by-requirement disposition, false-claim audit, release verdict | blocked until implementation diff freezes |

Agents perform no tracked writes. Each report must name files inspected,
confirmed facts, inferred risks, exact requirement IDs affected, recommended
changes, and reproducible verification commands. The principal reproduces all
material findings before acting.

## Package Checkpoints

| Checkpoint | Evidence | Status |
| --- | --- | --- |
| F4-AS-CP1 | Contract, branch, dirty-tree baseline, exact selection, current plan, and ledger recorded | complete |
| F4-AS-CP2 | Canonical reading and three specialist reports reconciled | pending |
| F4-AS-CP3 | Focused behavior contract observed failing for the correct reason | pending |
| F4-AS-CP4 | Minimal implementation passes focused and related tests | pending |
| F4-AS-CP5 | Exact evidence overlay and generated documentation agree | pending |
| F4-AS-CP6 | Targeted and complete quality gates observed; exact blockers retained | pending |
| F4-AS-CP7 | Frozen diff independently reviewed; findings fixed and retested | pending |
| F4-AS-CP8 | Temporary-index diff contains only attributable paths; commit and push observed | pending |
