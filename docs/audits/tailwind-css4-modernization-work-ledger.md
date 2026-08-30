# Tailwind CSS 4 Modernization Work Ledger

Date: 2026-08-30

Status: discovery in progress; production changes are principal-owned and test-first

## Protected baseline

- Branch: `main`; no branch or worktree may be created.
- Start state: `main...origin/main` was three commits ahead with a materially
  dirty shared index and working tree.
- All pre-existing staged, unstaged, and untracked content remains user-owned.
- Analysts are read-only. They must not edit files, install packages, run a
  mutating browser flow, stage changes, commit, or push.
- The principal owns every cross-module decision, test, implementation edit,
  documentation update, review disposition, temporary index, commit, and push.
- A reviewer must not review a scope that they implemented. Review starts only
  after the attributable diff is frozen.

## Analyst assignments

| ID | Requested role | Exclusive read-only scope | Required deliverable | Status |
| --- | --- | --- | --- | --- |
| TW4-A1 | Tailwind Upgrade Agent | `package.json`, lockfile, installed package graph, Vite/Tailwind integration, legacy config/plugin/PostCSS evidence, current official 4.3 guidance | Exact version/configuration map, obsolete dependency disposition, compatibility and rollback risks | assigned |
| TW4-A2 | CSS Source Detection Agent | `resources/css/app.css`, Blade, Livewire/PHP class maps, JavaScript, vendor pagination sources, ignored paths and generated CSS | Complete source registry, missing/overbroad source findings, source smoke selectors, exact commands | assigned |
| TW4-A3 | Design Token Agent | `DESIGN.md`, current Tailwind tokens, SCSS token layer, arbitrary/repeated values, brand and status contrast | Current/target token inventory, preserved brand mapping, repeated-value candidates, contrast risks | assigned |
| TW4-A4 | Responsive Layout Agent | Navigation, sidebars, dialogs, forms, filters, pagination, media, grids, tables, charts and toolbars across 320..1920 CSS pixels | Prioritized reproducible defects, affected selectors/components, mobile-first/container-query recommendations | assigned |
| TW4-A5 | Accessibility Styling Agent | Focus, target size, long copy, status, reduced motion, forced colors, pointer/hover behavior and CSS-dependent semantics | Severity-ranked styling defects, exact reproduction, WCAG impact and verification cases | assigned |
| TW4-A6 | Dynamic Class Safety Agent | First-party Blade/PHP/JS/CSS utility construction, safelist equivalents, arbitrary values and class maps | Unsafe dynamic-class inventory, false-positive rules, complete static replacements and ratchet proposal | assigned |
| TW4-A7 | Frontend Build Agent | Build/lint scripts, Vite manifest/assets, raw/gzip sizes, emitted selectors, browser tooling and critical pages | Reproducible baseline, missing-utility method, visual/browser matrix and output-size thresholds | assigned |

The four-slot runtime requires three analyst waves; exclusivity is logical, not
simultaneous. Later analysts must treat earlier reports as evidence only and
must independently reproduce any material claim in their own scope.

## Reviewer assignments

| ID | Requested role | Frozen review scope | Required deliverable | Status |
| --- | --- | --- | --- | --- |
| TW4-R1 | Tailwind Architecture Reviewer | Package/config/CSS-first/source/token/test/documentation diff and compiled output | Critical/important/minor findings, reproduction and architecture verdict | pending implementation freeze |
| TW4-R2 | Responsive UI Reviewer | Critical routes at the required viewport/locale/zoom matrix | Reproducible overflow, clipping, layout, touch and interaction findings | pending built assets |
| TW4-R3 | Accessibility Styling Reviewer | Focus, contrast, motion, forced colors, status, target and keyboard-visible styling | WCAG-linked findings and state-by-state verification verdict | pending built assets |
| TW4-R4 | Build Output Reviewer | Lock/config/manifest/assets/source coverage and required generated selectors | Build integrity, missing-utility and size-regression verdict | pending clean build |

## Finding disposition contract

Every finding receives one disposition: `accepted`, `rejected with evidence`,
`already satisfied`, `out of scope with owner`, or `blocked with exact cause`.
Accepted behavior defects require an observed RED contract before the smallest
fix. After fixes, the affected focused checks and the complete frontend build
repeat before final repository gates.

