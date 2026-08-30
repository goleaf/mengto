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
| F4-AS-A1 | Requirements and source analyst | Immutable source section, generated JSON/index/matrix/evidence, prior Phase 4 package records | Exact 58-ID inventory; dependency exclusions; stale-evidence findings; required generator/doc updates | complete; report reconciled |
| F4-AS-A2 | Architecture, database, and security analyst | Manifest, checksum/loader/synchronizer, migrations, category/translation models, policies, caches, factories, seeders | Source-to-persistence map; preservation/authorization/cache/query risks; implementation gaps versus evidence gaps; exact tests | complete; report reconciled |
| F4-AS-A3 | UI, localization, accessibility, and test analyst | Forum category presenters/Livewire/Blade, EN/LT/RU catalogues, nearby category tests, browser scripts | Render/localization/a11y/responsive contract; missing coverage; proposed red test and target commands | complete; report reconciled |
| F4-AS-R1 | Independent final reviewer | Frozen attributable diff and observed verification ledger; no implementation role | Severity-ranked findings, requirement-by-requirement disposition, false-claim audit, release verdict | complete; no-go, promotion demoted |

Agents perform no tracked writes. Each report must name files inspected,
confirmed facts, inferred risks, exact requirement IDs affected, recommended
changes, and reproducible verification commands. The principal reproduces all
material findings before acting.

## Discovery Dispositions

- Corrected the initially drafted nonexistent Phase 5 exclusion to
  `animal.taxonomy.0021`; `forum.moderation.0012` remains the Phase 7
  exclusion. Both stay open while their labels remain in the exact hierarchy.
- Accepted the evidence-gap verdict and added one focused source, persistence,
  locale-trust, preservation, validation, and public-projection contract.
- Accepted the category-navigator fixed-scale finding. The contract failed on
  Georgia plus `clamp(1.25rem, 2vw, 1.65rem)` and passed after the narrow SCSS
  correction to the inherited product font and `1.125rem`/`1.35` title scale.
- Deferred shared hardcoded forum filters/sorts, non-public administrator
  category projection, and SQL-level catalogue bounds because they are real
  broader debts but not behaviors owned by the selected 58 category atoms.
- No schema, Policy, Form Request, Action, Service, model, factory, seeder,
  Blade, or locale-catalogue change is justified for this package.

## Observed Gate Ledger

| Gate | Observed result |
| --- | --- |
| Exact selection and status isolation | pass; exactly 58 selected IDs are implemented/tested/in-progress, both later-phase IDs remain discovered |
| Focused Pest | pass; 7 tests, 104 assertions |
| Related category/cache/administration | pass; 62 tests, 6,097 assertions |
| Localization/responsive/schema/factory | pass; 50 tests, 37,056 assertions |
| Targeted Chrome | failed on current rerun before Chrome: isolated seed stops at `ReviewFactory.php:69` in unrelated concurrent work; an earlier four-state run passed with zero console errors |
| Manifest and requirement generators | pass; 44 roots, 1,637 children, 38,377 atoms |
| Larastan | pass; zero errors |
| Fresh/repeat seed | earlier pass: 139 migrations, 219 tables, stable 10 users; current seed graph is not green because `ReviewFactory.php:69` aborts |
| Rollback/reapply/repeat seed | earlier pass: 139 to 0 to 139, 219 tables, stable 10 users; current repeat seed is not green for the same factory blocker |
| Composer validation/audit/platform | pass; no advisories or missing requirements |
| NPM audit and Vite build | pass; zero vulnerabilities and Vite 8.2.2 production build |
| Isolated Laravel caches | pass; config, event, route, and view compilation |
| Coverage | externally blocked; Pest exits 1 because PCOV/Xdebug is unavailable |
| Source reconstruction | externally blocked; Codex history entry `1785397895` is unavailable |
| Full Pest | failed in shared work: 3,891 tests, 3,881 pass, 109,110 assertions, 9 failures and 1 error outside the selected package |
| Architecture | failed on concurrent repository-inventory drift, four unrelated PHP literals, and the source-history blocker |
| Full Pint | failed on three unrelated database-package files; focused package Pint passes |
| Full browser | failed in the shared environment: monolithic run exceeded 900 seconds; a bounded run later lost process state after selected assertions passed |
| Generated documentation | compliance and seeding outputs match; repository inventory awaits the concurrent database package's file-set regeneration |

## Package Checkpoints

| Checkpoint | Evidence | Status |
| --- | --- | --- |
| F4-AS-CP1 | Contract, branch, dirty-tree baseline, exact selection, current plan, and ledger recorded | complete |
| F4-AS-CP2 | Canonical reading and three specialist reports reconciled | complete |
| F4-AS-CP3 | Focused behavior contract observed failing for the correct reason | complete; one typography failure, 5 tests passing |
| F4-AS-CP4 | Minimal implementation passes focused and related tests | complete: focused 7/104; related 62/6,097 plus 50/37,056 |
| F4-AS-CP5 | Exact evidence overlay and generated documentation agree | pending final promotion: exact 58-ID in-progress delta; 1,727 verified, 58 in progress, 36,592 discovered globally; 239/58/1,166 in Phase 4 |
| F4-AS-CP6 | Targeted and complete quality gates observed; exact blockers retained | pending |
| F4-AS-CP7 | Frozen diff independently reviewed; findings fixed and retested | pending |
| F4-AS-CP8 | Temporary-index diff contains only attributable paths; commit and push observed | pending |

## Independent Review Disposition

The independent reviewer returned a no-go because required non-external full
Pest, Architecture, full Pint, full browser, and generated-inventory gates did
not pass. The finding is accepted: all 58 records remain implemented and
tested but are demoted from a final verified result to `in-progress` until the
failed gates are green. The reviewer also identified stale completeness-audit
wording, which is corrected with the exact source-history and current full
suite results. Its follow-up confirmed the demoted status and counts, retained
the no-go verdict, and requested that historical seed passes be distinguished
from the current `ReviewFactory.php:69` blocker; the gate rows now do so.

Attributable publication is not currently possible. Concurrent repository
work swept parts of this package into already-pushed mixed commits `f605d58`
and `153ae45`. No history rewrite is permitted, no coherent package-commit
claim is made, and CP7/CP8 remain pending. Any later publication must freeze a
temporary-index diff containing only the remaining Phase 4 corrections.
