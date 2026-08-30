# Shared Directory Card Completion Work Ledger

Date: 2026-08-30

Status: active

## Preservation Boundary

- Branch: `main`.
- Starting commit: `50089371abca198699c73b36055f14a9d33405aa`.
- The starting tree contains a large pre-existing staged change set, including
  the canonical implementation plan and Places/authentication work. Every
  pre-existing staged byte is user-owned and must remain present.
- Specialists are read-only. They may inspect repository files and run
  non-mutating commands, but they do not edit tracked files, stage, commit,
  push, change runtime data, or start shared-state servers.
- The principal owns all cross-family decisions, tracked edits, test-driven
  implementation, finding dispositions, final gates, and publication.
- Attributable publication uses a temporary `GIT_INDEX_FILE`; no unrelated
  staged or unstaged hunk may enter the card-system commit.

## Specialist Assignments

| ID | Specialist | Exclusive scope | Required structured deliverable | Status |
| --- | --- | --- | --- | --- |
| SDC-S01 | Component API design | `resources/views/components/directory-card.blade.php`, `card-media`, `card-heading`, `card-description`, `card-action-row`, their tests and canonical component documentation | Complete primitive/prop/default/normalization/deprecation table; text-only heading decision; proven or rejected media-less and compact topologies; ratchet recommendations without a mega-component | complete: 18 findings, no blocker |
| SDC-S02 | Consumer inventory | All first-party Blade/Livewire/PHP/CSS references that render or style cards, rows, identity blocks, compact results, and directory/media items | Exhaustive path/symbol/host-route inventory; family classification using exactly adopt-shell, adopt-leaf, keep-domain, merge, or retire; zero-consumer candidates and footer-action inventory | complete: 32 families and 87 action contracts, no blocker |
| SDC-S03 | Media behaviour | Responsive images, linked media, fallback assets, public/private media presenters/routes/policies and media-bearing card consumers | Per-family src/srcset/sizes/width/height/loading/fallback/link-label/privacy/layout-shift matrix with concrete defects and evidence | complete: 18 media families and 25 findings; live DPR/LCP evidence deferred to browser specialist |
| SDC-S04 | Accessibility | Card semantics, headings, links/buttons/forms/toggles, status/error/loading/offline markup, CSS focus/forced-colors/reduced-motion/touch-target rules | WCAG-oriented source audit covering nested interaction, names, keyboard, focus, 44px, pressed/active/disabled/error/repeat states, heading order, zoom risks, and precise fixes/tests | in progress |
| SDC-S05 | Localization | All card-related EN/LT/RU keys, placeholders/plurals, presenter labels, accessible names, status/error/action text | Locale parity and hardcoded-copy inventory; placeholder/plural mismatches; long-copy/accessible-name risks; exact tests or fixes | in progress |
| SDC-S06 | Responsive geometry | Shared card CSS/SCSS/Tailwind, grids, side panels, compact rows, media containment, action wrapping | Consumer-to-container geometry matrix; width/height/overflow/fixed-height/two-axis-scroll/200%-400%-zoom risks; concrete browser assertions | in progress |
| SDC-S07 | Browser testing | Repeatable authenticated component/feature browser flows after implementation | Commands, accounts/routes/locales/viewports/emulations, geometry/accessibility assertions, console/image checks, and screenshot artifact manifest | waiting |
| SDC-S08 | Independent visual review | Frozen attributable diff and final screenshots only; reviewer must not implement | Diff/screenshot review with Critical/Important/Minor findings, route/viewport evidence, explicit spec and quality verdicts, and release recommendation | waiting |

## Principal Reconciliation Contract

1. Reproduce every material specialist finding from repository or browser
   evidence before accepting it.
2. Resolve classification conflicts from canonical requirements, not visual
   resemblance.
3. Add failing observable-behaviour tests before production changes.
4. Keep each migration family-sized and preserve routes, queries,
   authorization, private-media rules, and domain transitions.
5. Freeze the complete attributable diff before SDC-S08, record every review
   disposition here, fix valid in-scope findings, and rerun affected checks.

## Finding Disposition

The principal reproduced the first-wave reports against current source and
accepted the following design boundaries before executable contracts are
added:

- The shared directory shell remains exactly media, body, and optional footer;
  no domain, compact, or media-less switches will be added.
- Shared headings remain prepared, escaped text with bounded `h2`/`h3` and
  spacing variants. Rich heading slots are rejected because all eight current
  consumers pass scalar text and keep badges/status outside the heading.
- Share channel and recipient records are the only two proven equivalent
  compact resource/action rows. Any shared composition is separate from the
  directory shell and retains domain-owned leading/action slots.
- The direct shell set remains group, pet, neighbour, and compatibility
  meetup. Place, search, profile, feed, group-post, medical, care, device,
  booking, discovery, expert, and marketplace retain their product topology;
  only explicitly compatible leaves may migrate.
- Zero-consumer searches accept `.card-icon-button` and `x-person-summary`
  with its selector/fixture/generated-inventory closure as retirement
  candidates. Profile pet implementations are both live and non-equivalent.
- The 87-action ledger is the interaction baseline. Navigation, toggle,
  destructive, plain form-submit, browser-local, and private-download
  semantics remain domain-owned; the shared form primitive may expose only
  generic pending/disabled/repeated-submit state.
- Media containment fixes may add truthful intrinsic attributes, lazy/eager
  intent, accessible names, and shared image/avatar leaves without changing
  destinations or record access. Broader record-lifecycle media authorization
  findings require model-bound security work and are not to be hidden by a
  visual component migration.

## Verification Evidence

No completion gate is recorded until its command has been run against the
attributable implementation and its output observed.
