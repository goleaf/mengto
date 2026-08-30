# Blade And Browser Lifecycle Modernization Work Ledger

Date: 2026-08-30

Status: discovery active; all analysts are read-only and the principal owns
every edit, disposition, verification result, commit, and push decision.

## Protected Baseline

- Branch: `main` at `9540fe83756833ae1c6d22053e883a07dca9f014`.
- Remote baseline: `origin/main` at
  `462539c`; local `main` is three commits ahead.
- Initial shared state: 485 tracked changed entries, including 478 index
  entries and 27 worktree entries, plus 11 untracked entries.
- Every byte present before this ledger is user-owned. No analyst may edit the
  checkout. The principal will stage only attributable files through a
  temporary `GIT_INDEX_FILE` and will not reset, clean, stash, rewrite, or
  force-push shared work.
- Governing delivery IDs are `BFA-01` through `BFA-07` and `BLM-A1` through
  `BLM-R3` in `docs/implementation-plan.md`.

## Discovery Assignments

| ID | Specialist | Exclusive read-only scope | Expected structured output | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| BLM-A1 | Blade Purity and Data-Flow Analyst | All first-party Blade pages, components, layouts, mail, errors, pagination and fragments; PHP/direct dependency/business/permission/SEO/collection/relation/literal/raw-output paths and their callers | Scope; examined files/symbols; severity-ranked confirmed evidence; suspected findings; missing evidence; implementation order; tests/commands; change risks; destination owner for each violation | Contract and canonical architecture/frontend/security/localization docs | assigned wave 1 |
| BLM-A2 | Blade Component and Presentation Contract Analyst | Repeated cards, forms, tables, buttons, status, modal, empty/error, navigation and layout patterns; component APIs, slots, semantics and responsive states | Required eight-part report plus consolidation map, explicit props/slots/defaults/states and usage/test changes | Contract, design system and component inventory | assigned wave 1 |
| BLM-A3 | Flux Compatibility and Accessibility Analyst | Composer/NPM lock metadata, installed/licensed Flux capability, Flux-like custom forms/modals/menus/tables/notifications, focus, keyboard, validation, locale and theme behavior | Required eight-part report plus Flux usage matrix, invalid/deprecated list, replace/retain decisions and accessibility tests | Installed package metadata and canonical frontend/accessibility docs | assigned wave 1 |
| BLM-A4 | Alpine Integration Analyst | JavaScript entries, packages, Livewire bootstrap, Alpine plugins/globals, every first-party `x-` directive, CSP/data boundaries and server/client state ownership | Required eight-part report plus Alpine ownership map, duplicate/conflict inventory and migration/lifecycle tests | Installed Livewire/Alpine metadata | queued wave 2 |
| BLM-A5 | JavaScript Navigation Lifecycle Analyst | All first/third-party widgets and modules, listeners, timers, observers, media/maps/editors, Vite loading, `wire:navigate`, back/forward, logout and account switch | Required eight-part report plus lifecycle registry, init/destroy/leak/stale-state findings and browser-test requirements | BLM-A4 is informative but not blocking | queued wave 2 |
| BLM-A6 | Raw HTML and XSS Boundary Analyst | Raw Blade echo, Markdown/rich text, email, previews, JSON-LD, SVG, URLs, script data and third-party widgets with origin-to-sink traces | Required eight-part report plus XSS boundary map, sanitizer ownership, malicious payloads and adversarial tests | Canonical security/frontend rules | queued wave 2 |
| BLM-A7 | Frontend Architecture Test Analyst | Pest architecture suite, Blade tree, package metadata, fixtures, reliable static checks, false positives and intentional exceptions | Required eight-part report plus maintainable architecture-check specification and failing-fixture strategy | BLM-A1/A3/A4/A6 findings are informative but not blocking | queued wave 3 |

Every report must contain: (1) inspected scope; (2) examined files, symbols,
routes or workflows; (3) confirmed findings with severity and exact evidence;
(4) suspected findings needing principal validation; (5) missing coverage or
unavailable evidence; (6) recommended implementation order; (7) exact tests
and verification commands; and (8) risks. Guesses must be labelled as such.

## Independent Review Assignments

| ID | Reviewer | Frozen read-only scope | Required output | Dependency | Status |
| --- | --- | --- | --- | --- | --- |
| BLM-R1 | Blade Architecture Reviewer | Attributable changed Blade plus preparation owners and architecture tests | Severity, exact location, failure scenario, evidence, correction/test, ownership and architecture-test gaps | Implementation freeze | pending |
| BLM-R2 | Flux and Accessibility Reviewer | Changed custom/Flux components, forms, semantics, focus, keyboard, themes, locales and tests | Severity-ranked compatibility/accessibility findings and verified usage matrix | Implementation freeze | pending |
| BLM-R3 | JavaScript Lifecycle and XSS Reviewer | Changed JS/Alpine/raw output/URL/widget wrappers and browser tests | Reproduction-backed listener/timer/stale-state/CSP/XSS findings with required fixes/tests | Implementation freeze | pending |

Reviewers are independent from discovery specialists and remain read-only
unless this ledger later delegates one narrow fix after their report is
complete. The principal reproduces every material finding, records each
accepted or rejected disposition, fixes valid in-scope defects test-first, and
reruns affected checks before any publication.

## Publication Rule

No BFA completion, verification, commit, or push claim is permitted unless the
exact attributable candidate passes the applicable repository gates and all
material review findings are closed. Concurrent full-gate failure blocks this
delivery's commit and push; it is not reclassified as an external limitation.
