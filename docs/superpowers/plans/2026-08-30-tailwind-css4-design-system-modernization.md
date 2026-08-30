# Tailwind CSS 4 Design-System Modernization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use test-first implementation and independent frozen-diff review. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete PawCircle's stable Tailwind CSS 4.3.3 CSS-first architecture and a consistent, responsive, accessible design-token system without replacing the deliberate brand or unverified SCSS components.

**Architecture:** `@tailwindcss/vite` compiles one explicit-source Tailwind entry. CSS-first theme, utility and variant contracts own reusable tokens and primitives; the existing semantic SCSS asset remains separately built until measured component migrations prove safe.

**Tech Stack:** Tailwind CSS 4.3.3, `@tailwindcss/vite` 4.3.3, Vite 8.2.2, Sass Embedded 1.103.1, Laravel 13 Blade, Livewire 4, Pest 4, existing isolated Chrome runner.

## Global Constraints

- Work only on `main`; preserve every unrelated dirty-tree byte.
- Use the latest stable compatible Tailwind 4.x, currently verified as 4.3.3.
- Do not add Flux, Filament, Volt, a second Alpine, a PostCSS Tailwind pipeline, or prerelease packages.
- Preserve EN/LT/RU, visible focus, 44px targets, reduced motion, forced colors and the Neighborhood Noticeboard brand.
- Do not remove the semantic SCSS layer without independent selector and browser evidence.
- Do not publish or push while an applicable required gate or material review finding remains open.

---

### Task 1: Freeze discovery and architecture contracts

**Files:**
- Modify: `PLANS.md`
- Modify: `docs/implementation-plan.md`
- Create: `docs/audits/tailwind-css4-modernization-work-ledger.md`
- Create: `tests/Feature/TailwindCssArchitectureTest.php`

- [ ] Record installed versions, current CSS/Vite/source/PostCSS state and rollback.
- [ ] Dispatch seven exclusive read-only analyst scopes and reconcile every finding.
- [ ] Add CSS-first/source/token/dynamic-class architecture assertions.
- [ ] Run the focused test and observe expected RED failures for missing target contracts.

### Task 2: Complete CSS-first theme and utility architecture

**Files:**
- Modify: `resources/css/app.css`
- Create if decomposition stays justified: `resources/css/theme.css`
- Create if decomposition stays justified: `resources/css/utilities.css`
- Modify only with reproduced need: `resources/scss/_tokens.scss`

- [ ] Preserve the Vite plugin and explicit Tailwind import.
- [ ] Complete semantic theme namespaces and align shared SCSS variables without visual brand drift.
- [ ] Register only justified custom variants/utilities and use `@variant` where it improves preference-state maintenance.
- [ ] Run the focused test and production build; inspect required selectors.

### Task 3: Close source, dynamic-class and repeated-value gaps

**Files:**
- Modify only confirmed emitters in: `app/Livewire/**`, `app/View/Components/**`, `resources/views/**`, `resources/js/**`
- Modify: `tests/Feature/TailwindCssArchitectureTest.php`

- [ ] Replace confirmed interpolated fragments with complete controlled class maps.
- [ ] Add the smallest missing source registrations or inline sources.
- [ ] Move repeated arbitrary values to a theme token or focused utility.
- [ ] Prove the focused ratchet fails without each required class and passes after correction.

### Task 4: Implement measured responsive and accessibility styling

**Files:**
- Modify only confirmed component selectors/views under `resources/scss/**` and `resources/views/**`
- Modify existing browser runner assertions only when they encode a reproduced contract.

- [ ] Reproduce each accepted overflow, clipping, long-copy, table, dialog, navigation, form, filter, media, grid or toolbar defect.
- [ ] Apply mobile-first, logical, container-aware and pointer-capability styling where justified.
- [ ] Verify focus, target, motion and forced-color states with semantic text retained.
- [ ] Run focused PHP/browser checks at 320, 375, 768, 1024, 1280, 1440 and 1920px.

### Task 5: Document feature applicability and runtime evidence

**Files:**
- Create: `docs/tailwind-feature-matrix.md`
- Modify: `docs/tailwind.md`
- Modify: `docs/design-system.md`
- Modify: `docs/frontend.md`
- Modify: `docs/accessibility.md`
- Modify through generator source/output: `docs/requirements/compliance-matrix.md`
- Modify: `CHANGELOG.md`

- [ ] Record used/not-applicable reasons, implementation, responsive/a11y effects and exact verification.
- [ ] Keep statuses conservative until checks actually pass.
- [ ] Regenerate deterministic compliance output and confirm byte parity.

### Task 6: Independent review, correction and publication

**Files:**
- Modify: `docs/audits/tailwind-css4-modernization-work-ledger.md`
- Modify affected implementation/tests/docs only for reproduced valid findings.

- [ ] Freeze the attributable diff and dispatch four independent requested reviewers.
- [ ] Disposition every finding; fix valid in-scope issues and repeat focused checks.
- [ ] Repeat frontend lint/source checks, production build, output inspection and browser/console checks.
- [ ] Run applicable final repository gates, complete temporary-index diff/secret review, commit and push only if green.

