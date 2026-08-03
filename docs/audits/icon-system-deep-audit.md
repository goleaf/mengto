# Icon System Deep Audit

Date: 2026-08-03

Status: complete deep audit; remediation is implemented and verified in an attributable snapshot

## Scope

This audit covers every first-party Blade template, shared action and navigation
primitive, the installed icon package, SCSS icon sizing, inline SVG, raw
pictographic characters, and visible native links and buttons. It does not
classify charts, photographs, avatars, QR codes, maps, or brand marks as icons.

The repeatable source is `scripts/icon-system-audit.php`. The audit reads files
only and performs no database, network, route, or application mutation.

## Measured Baseline

| Measure | Before the first wave | After the first wave |
| --- | ---: | ---: |
| Blade templates | 349 | 350 |
| Canonical `x-ui-icon` calls | 0 | 828 |
| Direct `x-lucide-*` calls | 698 | 0 |
| Files with direct Lucide calls | 146 | 0 |
| Unique direct Lucide names | 180 | 0 |
| Dynamic Lucide debt outside the primitive | 83 | 0 |
| Legacy `icon` class attributes | 310 | 0 |
| Legacy SCSS icon selectors migrated | 41 | 0 remaining |
| Raw pictographic-symbol files | 1 | 0 |
| Non-Lucide icon systems | 0 | 0 |
| Missing installed direct Lucide names | 0 | 0 |

The attributable source contains 508 native anchors/buttons. A conservative markup
heuristic initially identified 97 text-only candidates across 60 files. Manual
review added icons to 45 unambiguous actions and retained 52 intentional
text-only elements across 41 files.

The retained exception classes are accessibility skip links; brand, avatar,
person, pet, device, place, listing, conversation, and article-title links;
filter, locale, poll, taxonomy-result, selection-row, and pagination controls;
source attribution and literal invitation URLs; and compact footer navigation.
These controls communicate through their text or content identity, and adding
a decorative symbol would add noise or imply a stronger action than exists.

## Findings

### A. One library, multiple rendering contracts

The application already uses `mallardduck/blade-lucide-icons` 1.26.32 and no
Font Awesome, Heroicons, Material Symbols, Bootstrap Icons, or MDI markup was
found. The visual inconsistency came from direct component calls, dynamic
component calls, arbitrary Tailwind sizes, and legacy SCSS classes rather than
from competing icon libraries.

### B. No canonical primitive existed

Icon name, size, fill, stroke, and accessibility attributes were repeated in
individual templates. A change to stroke or size therefore required hundreds
of edits and new code could introduce another local convention.

The remediation adds `x-ui-icon`. It owns current-color strokes, no fill, a
1.9-pixel stroke, decorative/informative ARIA behavior, and ten named size
steps from `xs` through `hero`. The emitted SVG has no legacy class or local
size contract. The component is flat at `components/ui-icon.blade.php`, which
preserves the repository's anonymous-component path contract while avoiding
the vendor-provided generic `x-icon` name.

### C. Size vocabulary was not bounded

Direct calls use `.icon`, `.icon--xs`, `.icon--sm`, and arbitrary values from
`size-3.5` through `size-24`. Some variation is intentional hierarchy, but the
same semantic role can currently receive different sizes on different pages.
The canonical component converts those values into named roles instead of
allowing a new arbitrary size at every call site.

### D. Desktop navigation discarded prepared icon data

Every primary-navigation destination already had a server-owned Lucide name,
but only the mobile navigation rendered it. The first wave now renders one
small decorative icon with every one of the thirteen desktop destinations and
retains the visible text and current-page state.

### E. Raw pictograms bypassed the system

One automation flow used a Unicode arrow between trigger and action. It is now
a canonical `arrow-right` icon with hidden decorative semantics and wrapping
text. No raw pictographic character remains in Blade.

### F. Inline SVG has one legitimate exemption

`components/medical-weight-chart.blade.php` owns the only inline SVG. It is a
data visualization, not a replaceable interface icon, and remains explicitly
allowlisted. Any second inline SVG now fails the audit unless it receives an
equally specific documented exemption.

### G. The executable audit was audited too

The first dynamic detector only recognized an empty `lucide-` string and could
miss a ternary dynamic component such as `lucide-history`. The detector and
feature contract now count every `lucide-` occurrence outside `x-ui-icon`, the
remaining knowledge status consumer was migrated, and the migration check also
fails when a dynamic Lucide consumer bypasses the primitive.

### H. Shared-work overlap was resolved before migration

Eight dynamic calls initially overlapped unfinished discovery/place work, so
the first pass preserved them. Their owning commits subsequently reached
`main`; the files became clean and the same canonical migration then reduced
dynamic debt to zero without combining unrelated changes.

## Current Result

- Added the canonical icon primitive and bounded SCSS sizes.
- Migrated all 698 direct calls in 146 files and all 83 dynamic consumers
  through the canonical primitive.
- Migrated 41 legacy SCSS selectors and removed the emitted compatibility
  class, leaving direct, dynamic, Blade-class, and style-selector debt at zero.
- Added icons to 45 unambiguous text actions and documented the 52 intentional
  content, choice, attribution, skip-link, pagination, and compact-navigation
  exceptions.
- Added desktop primary-navigation icons without removing labels.
- Replaced the raw automation arrow.
- Added a downward-only audit ratchet and feature contract.
- Kept routes, Eloquent, controllers, policies, validation, persistence, and
  query count unchanged.

## Reproduction

```bash
php scripts/icon-system-audit.php --check
php scripts/migrate-icon-system.php --check
php artisan test tests/Feature/IconSystemContractTest.php
npm run build
php artisan view:cache
```

The verified attributable snapshot passed 2,639 Pest tests with 83,214
assertions, full Pint, Larastan over 1,385 files, fresh migration, complete and
repeat seeding, dependency audits, production Vite build, cache compilation,
and the 33-screenshot EN/LT/RU accessibility browser matrix from 320 through
1920 pixels with no console errors or horizontal overflow.

The completion plan and exact zero-debt gates are in
`docs/plans/icon-system-unlimited-plan.md`.
