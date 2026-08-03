# Icon System Unlimited Completion Plan

Date: 2026-08-03

Status: current baseline implemented and verified; unlimited regression ledger remains active

## Objective

Give every PawCircle interface icon one visual and accessibility contract,
place an icon beside every action where an unambiguous icon improves scanning,
and remove every undocumented local icon convention without changing product
behavior, authorization, or data flow.

The plan ends only at measured zero debt. A completed wave is not permission to
declare the full icon system complete.

## Non-Negotiable Contract

1. Lucide is the only first-party interface icon library.
2. `x-ui-icon` is the only icon-rendering primitive.
3. Icons inherit `currentColor`, use no fill, and use a 1.9-pixel stroke.
4. Sizes use `xs`, `sm`, `md`, `lg`, `xl`, `2xl`, `3xl`, `4xl`, `display`, or
   `hero`; no new arbitrary `size-*` value is allowed.
5. An icon paired with visible text is decorative and has `aria-hidden=true`.
6. An informative standalone icon has `role=img` and a localized accessible
   label.
7. An icon-only control keeps its localized accessible name on the control,
   not only on the SVG or a tooltip.
8. Status, urgency, success, privacy, and danger never rely on icon or color
   alone.
9. Icons do not replace photographs, avatars, maps, charts, QR codes, brand
   marks, or meaningful written instructions.
10. Every mobile action retains at least a 44-by-44-pixel target, visible focus,
    forced-colors usability, reduced-motion safety, and no page overflow.

## Completion Gates

The work is complete only when the repeatable audit reports all of the
following:

- `direct_lucide_instances = 0`;
- `dynamic_lucide_debt = 0` outside `x-ui-icon`;
- `legacy_icon_class_instances = 0`;
- `foreign_icon_files = []`;
- `pictogram_files = []`;
- `missing_direct_lucide_icons = []`;
- no unapproved inline SVG;
- every text-only interactive candidate is either migrated or recorded as an
  intentional text-only exception with a reason;
- every icon-only control has an accessible name;
- desktop/mobile browser matrices have no overflow, undersized target,
  duplicate ID, missing accessible name, broken icon, raw key, or console
  error.

## Phase Ledger

### Phase 0 — Inventory And Ratchet

Status: implemented and verified

- Inventory every first-party Blade file and installed Lucide name.
- Record direct, dynamic, canonical, legacy-class, inline-SVG, foreign-library,
  raw-pictogram, and text-only-control counts.
- Add `scripts/icon-system-audit.php --check` and a downward-only ratchet.
- Preserve the medical weight chart as the only documented inline-SVG
  exemption.

### Phase 1 — Canonical Primitive And Global Navigation

Status: implemented and verified

- Add `x-ui-icon` with one stroke, fill, size, color, and ARIA contract.
- Emit only the canonical `.ui-icon` class and semantic size modifier.
- Migrate the shared action, link, notice, empty-state, status, statistics,
  navigation, messaging, care, device, forum, marketplace, lost/found, expert,
  and medical dynamic consumers whose files are attributable.
- Render prepared icons in all thirteen desktop and eleven mobile primary
  destinations.
- Replace the raw automation arrow.

### Phase 2 — Shared Direct-Icon Components

Status: implemented and verified

- Migrate direct Lucide calls in reusable components before page templates.
- Start with action menus, form fields, feedback, cards, badges, tab lists,
  toolbars, pagination, media controls, and Livewire shared surfaces.
- Convert arbitrary sizes to named semantic sizes.
- Ratchet direct calls and legacy class counts after every coherent slice.

### Phase 3 — Application Shell And Account Surfaces

Status: implemented and verified

- Audit header utilities, profile menu, notifications, messages, search,
  account entry, registration, verification, password recovery, session
  controls, and logout.
- Ensure every icon-only header control has a localized accessible name.
- Keep authentication meaning explicit in text; do not replace security copy
  with icons.

### Phase 4 — Forms, Filters, And Mutation Actions

Status: implemented and verified

- Map create, edit, save, send, upload, download, delete, restore, archive,
  cancel, retry, filter, clear, sort, search, share, and report intents to one
  stable Lucide name each.
- Review all 97 baseline text-only candidates manually.
- Add icons only when they improve recognition; record justified text-only
  exceptions for prose links, pagination, dense definitions, and controls with
  no stable universal symbol.
- Preserve Form Request/Livewire validation, CSRF, method spoofing, disabled,
  loading, dirty, error, and repeated-submission behavior.
- Current review migrated 45 action candidates and recorded the remaining 52
  as intentional content-identity, choice, attribution, skip-link, pagination,
  or compact-navigation exceptions in the deep audit.

### Phase 5 — Feedback, State, Safety, And Empty Surfaces

Status: implemented and verified

- Standardize success, information, warning, urgent, blocked, private,
  verified, pending, offline, empty, and unavailable icons.
- Keep semantic text beside every state.
- Verify contrast, forced colors, and screen-reader output.

### Phase 6 — Social And Communication Domains

Status: implemented and verified

- Publication feed, reactions, comments, reposts, saved items, following,
  friendships, profiles, groups, direct messages, calls, sharing, and
  notifications.
- Preserve server-authoritative identity and exact actor/pet attribution.
- Verify action menus and reaction controls through keyboard and touch paths.

### Phase 7 — Care, Health, Device, And Expert Domains

Status: implemented and verified

- Medical records, emergency summaries, care journals, tasks, medicines,
  measurements, smart-device telemetry, automations, expert discovery,
  bookings, and consultations.
- Keep the medical weight chart exemption scoped to visualization only.
- Do not let an icon imply diagnosis, device certainty, or professional
  verification that the server has not established.

### Phase 8 — Places, Discovery, Events, And Lost/Found

Status: implemented and verified

- Preserve the completed zero dynamic-debt state across discovery and places.
- Standardize category, map, location, route, safety, venue, schedule,
  registration, sighting, coordination, poster, and recovery actions.
- Verify map alternatives and exact-location privacy remain unchanged.

### Phase 9 — Forum, Knowledge, Marketplace, And Organizations

Status: implemented and verified

- Forum directories, topic editor/detail, taxonomy, moderation, mentorship,
  journals, events, knowledge, listings, orders, organization authority, and
  tenant workspaces.
- Preserve immutable taxonomy sources and run forum preservation/generation
  checks for any affected forum evidence or plan change.

### Phase 10 — Remove Compatibility Layer

Status: implemented and verified

- Migrate SCSS selectors from `.icon` to `.ui-icon` or semantic parent hooks.
- Remove the emitted legacy `.icon` class and old `.icon--xs/.icon--sm` rules.
- Set direct, dynamic, and legacy debt ratchets to zero.
- Reject any new direct Lucide call in architecture tests.

### Phase 11 — Full Release Verification

Status: implemented and verified for the current attributable baseline

- Focused icon contract and affected feature tests.
- Full sequential Pest and architecture suite.
- Pint and Larastan.
- Composer validation/audit and npm audit.
- Production Vite build and Blade/config/event/route cache compilation.
- Fresh migration, complete seed, and repeat seed where the combined release
  includes persistence changes.
- Browser matrix at 320, 375, 768, 1280, 1440, and 1920 pixels across every
  route family, including keyboard focus, forced colors, reduced motion, long
  Lithuanian/Russian text, and console output.
- Final source audit, staged-diff review, secret scan, attributable commit, and
  verified `origin/main` SHA.

Observed verification: 2,639 Pest tests / 83,214 assertions, full Pint,
Larastan over 1,385 files, Composer validation/audit, npm audit, Vite build,
compiled config/routes/views, fresh migration, complete and repeat seed, and
33 browser screenshots across EN/LT/RU and 320–1920-pixel layouts with no
console errors, overflow, unnamed controls, or undersized audited targets.

## Current Remaining Ledger

After the complete static migration and first semantic action pass:

- canonical icon calls: 828 using 187 statically named icons;
- direct Lucide calls: 0 across 0 files;
- dynamic debt: 0;
- legacy class attributes: 0;
- legacy SCSS selectors: 0;
- text-only interaction candidates: 52 intentional exceptions across 41
  files, reduced from the 97-candidate baseline;
- foreign libraries: 0;
- raw pictograms: 0;
- missing installed direct icon names: 0;
- unapproved inline SVG: 0.

These numbers may only move toward zero. The audit ratchets must be lowered in
the same change that removes debt.

The current actionable migration ledger is zero. This unlimited plan remains
active as a regression contract: every new route, component, action, state, or
icon must pass the same static, semantic, accessibility, and browser gates.

## Stop Conditions

Stop a slice without publication if it overlaps unattributable work, changes
action meaning, hides visible text, reduces a touch target, introduces a raw
SVG/pictogram, breaks a locale, creates overflow, loses an accessible name,
adds a second icon system, or lacks relevant runtime evidence.

All work stays on the existing `main` branch. Each coherent verified wave is
committed and pushed directly to `origin/main` with unrelated work excluded by
a temporary scoped index.
