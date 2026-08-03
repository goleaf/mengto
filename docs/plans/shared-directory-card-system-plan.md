# Shared Directory Card System Plan

Date: 2026-08-03

Status: active. The `/groups` repair and first directory-family migration are
implemented, verified in an isolated staged-tree snapshot, and published to
`origin/main`; the explicitly open future migration waves remain pending.

This plan has no artificial task-count limit. New tasks belong here only when
they preserve the audit decisions in
`docs/audits/groups-shared-card-ux-audit.md`; visual similarity alone is not a
reason to expand the abstraction.

## Goal

Deliver a stable, accessible, localized card composition system in which
media, copy, footer, typography, and common statistics can be changed once,
while domain-specific product meaning remains in small explicit components.

## Success Criteria

- `/groups` never visually merges image and text at supported viewport sizes.
- Every direct directory-card consumer uses the same media/body/footer
  structure when a footer exists.
- Shared heading and description rules have one implementation.
- Group metrics reuse the common definition-list component.
- No query, authorization, route, presenter, or persistence behaviour changes.
- Browser checks catch overlap, overflow, missing separation, unequal row
  heights, inaccessible media links, raw translation keys, and console errors.
- The migration path for other card families is explicit and does not create a
  mega-component.

## P00 — Baseline, Scope, And Ownership

- [x] Confirm work is on `main`.
- [x] Inspect staged, unstaged, and untracked changes before editing.
- [x] Identify unrelated concurrent icon-system and pet-profile work.
- [x] Record `/groups` as a compatibility directory and `/forum/groups` as the
  canonical persistent surface.
- [x] Trace route to request, controller, presenter, page, result grid, and card.
- [x] Capture focused PHP and production-build baseline results.
- [x] Restrict the implementation to the attributable UI, tests, browser gate,
  audit, and plan slice.
- [x] Re-check repository state immediately before staging.
- [x] Use a temporary Git index for the attributable commit.
- [x] Inspect the complete staged diff and reject unrelated hunks.

## P01 — Executable RED Contracts

- [x] Add a rendered-page assertion for six shared group cards.
- [x] Require direct `media` and `body` structural regions.
- [x] Require one footer within the card body.
- [x] Require shared heading and description hooks.
- [x] Require an explicit media/body border and opaque body surface.
- [x] Add semantic `h2`/`h3` shared heading coverage.
- [x] Add linked and unlinked heading coverage.
- [x] Observe the new tests fail before implementation.
- [ ] Add a standalone contract test for every documented media ratio if a new
  ratio is introduced.
- [ ] Add an architecture ratchet preventing raw card title/description markup
  in direct directory-card consumers after all consumers are migrated.

## P02 — Shared Structural Primitives

- [x] Give `x-directory-card` one identifiable root contract.
- [x] Isolate media with minimum-width, overflow, background, and border rules.
- [x] Isolate body with opaque background, padding, and flexible layout rules.
- [x] Add an optional named footer region with bottom alignment.
- [x] Keep caller attributes and domain hooks supported.
- [x] Harden `x-card-media` containment and block image layout.
- [x] Define explicit portrait, square, wide, and landscape ratio mappings.
- [x] Add reusable `x-card-heading` with explicit hierarchy and optional link.
- [x] Add reusable `x-card-description` with bounded spacing variants.
- [x] Add intentional two-item behaviour to `x-stat-grid`.
- [ ] Document a deprecation path before removing any existing public prop.
- [ ] Reject arbitrary free-form layout variants if bounded variants suffice.

## P03 — `/groups` Repair

- [x] Remove the group-only opt-out from shared full-height cards.
- [x] Remove duplicated group title CSS.
- [x] Remove duplicated group description CSS.
- [x] Replace group metric markup with `x-stat-grid`.
- [x] Move metric preparation to the class-based component.
- [x] Move organizer and actions into the shared footer region.
- [x] Remove duplicated footer push/padding rules.
- [x] Preserve privacy, official, recommendation, event, organizer, and action
  semantics.
- [x] Preserve responsive image sources and accessible linked-media labels.
- [x] Verify live EN, LT, and RU rendering with long content.
- [x] Verify joined/unjoined and secondary-action states in a real browser.

## P04 — Direct Directory-Family Migration

- [x] Move pet directory title to `x-card-heading` with correct `h2` semantics.
- [x] Move pet owner/action content into the shared footer.
- [x] Move neighbour title and status copy to shared typography primitives.
- [x] Move neighbour mutual/action content into the shared footer.
- [x] Move meetup title and description to shared typography primitives.
- [x] Move meetup place/host/RSVP content into the shared footer.
- [x] Confirm no consumer retains a duplicate body-to-footer border.
- [x] Confirm all direct consumers retain their meaningful grid-level empty
  states.
- [x] Confirm every linked media destination matches its visible title link.

## P05 — Component API Hardening

- [ ] Add component-level documentation examples for each supported primitive.
- [ ] Decide whether title props should remain text-only or support a safe slot;
  default to text-only until a real need exists.
- [x] Validate or normalize unknown heading levels without enabling arbitrary
  HTML tags.
- [x] Validate or normalize unknown media ratios to landscape.
- [ ] Define whether cards without media use a separate component or an
  explicit placeholder slot.
- [ ] Define a compact-card composition separately if two real consumers need
  it.
- [x] Keep all user values escaped and prohibit rich HTML in shared headings and
  descriptions.
- [ ] Add downward-only duplication metrics once the initial migration settles.

## P06 — Interaction And Footer Consistency

- [ ] Inventory card footers containing primary and secondary actions.
- [ ] Classify navigation, toggle, destructive, and form-submit semantics.
- [x] Ensure no nested interactive elements are introduced by media/title links.
- [ ] Verify shared controls expose current pressed/active state.
- [ ] Verify loading and repeated-submit behaviour for server-backed actions.
- [x] Standardize action-row wrapping at 320 pixels without shrinking touch
  targets below 44 pixels.
- [ ] Define when an entire card may be linked; default to explicit media/title
  links to avoid nested controls.
- [ ] Preserve domain confirmation and authorization boundaries.

## P07 — Media And Asset Consistency

- [ ] Inventory all media-bearing card families and their source dimensions.
- [ ] Verify width/height attributes prevent layout shift.
- [ ] Verify `srcset` and `sizes` reflect actual grid widths.
- [ ] Verify eager loading is restricted to the first meaningful above-fold
  image.
- [ ] Define local fallback imagery by domain; never use runtime public network
  fixtures.
- [ ] Test overlay containment in RTL and long-badge states.
- [ ] Verify media links retain distinct accessible labels.
- [ ] Keep private media behind its policy-protected delivery route.
- [ ] Measure Largest Contentful Paint before changing image formats or preload
  behaviour.

## P08 — Accessibility Matrix

- [ ] Verify one page `h1` and logical card heading order.
- [ ] Verify keyboard access to every media, title, and action target.
- [ ] Verify visible focus in normal and forced-colour modes.
- [ ] Verify non-colour status text remains present.
- [ ] Verify 200% and 400% zoom without two-axis scrolling.
- [ ] Verify screen-reader names for image and title links are not misleading.
- [ ] Verify high-contrast separator visibility.
- [ ] Verify browser text scaling does not clip fixed-height regions.
- [ ] Verify reduced motion remains unaffected.
- [ ] Add axe or an equivalent automated audit only if its version and rules are
  reproducible in this repository.

## P09 — Responsive And Localization Matrix

- [x] Add browser geometry coverage for 320, 375, 768, 1024, 1440, and 1920.
- [x] Assert image containment, boundary adjacency, separator width, body
  padding/background, footer presence, and equal same-row height.
- [x] Add desktop and mobile screenshots.
- [x] Execute the browser matrix against the attributable implementation.
- [x] Exercise EN, LT, and RU with the longest realistic seeded strings.
- [ ] Verify RTL geometry if an RTL locale enters supported scope.
- [ ] Verify cards inside narrow side panels separately from directory grids.
- [ ] Verify print output if directory printing becomes a requirement.
- [ ] Add container-query rules only after a real embedded consumer requires
  them.

## P10 — Wider Card-Family Classification

- [x] Audit `discovery-result-card` for leaf-primitive reuse.
- [x] Audit `expert-card` for leaf-primitive reuse.
- [ ] Audit `place-card` and `place-dashboard` as one product family.
- [x] Audit `listing-card` against price and order-state requirements.
- [ ] Audit `search-case-card` against urgency and location requirements.
- [ ] Audit `profile-pet-card` and `profile-card-pet` for duplication.
- [ ] Audit `feed-card` and `group-post-card` without adopting the directory
  shell.
- [ ] Audit care and medical cards under their privacy requirements.
- [ ] Audit device and booking cards under their operational requirements.
- [ ] Record each outcome as adopt-shell, adopt-leaf, keep-domain, merge, or
  retire.
- [ ] Require two concrete consumers before introducing a new shared variant.

## P11 — Progressive Migration Waves

- [x] Wave A: leaf typography reuse in structurally compatible public cards.
- [ ] Wave B: media containment reuse in cards already using responsive images.
- [ ] Wave C: footer composition in bounded public directory cards.
- [ ] Wave D: consolidate duplicate profile-pet implementations if their
  contracts prove equivalent.
- [ ] Wave E: retire obsolete CSS only after zero-consumer proof.
- [ ] Run targeted tests and screenshots after every wave.
- [ ] Keep commits small enough to revert one card family independently.
- [ ] Do not mix authorization, data migration, or route changes into visual
  migration commits.

## P12 — Documentation And Governance

- [x] Write the deep audit and abstraction decision record.
- [x] Write the dependency-ordered implementation plan and stop conditions.
- [x] Add the shared card family to the canonical UI component inventory.
- [x] Add the directory-family migration decisions to the UI migration matrix.
- [x] Link the audit and plan from the documentation index.
- [x] Update the groups documentation with the compatibility-card boundary.
- [x] Add browser command and artifact expectations to testing documentation.
- [x] Add an implementation entry to the changelog after verification.
- [x] Mark only observed checks as verified.
- [ ] Keep future tasks open rather than presenting planned work as complete.

## P13 — Quality Gates

- [x] Focused baseline: group and linked-media tests.
- [x] RED proof: new structure tests failed before implementation.
- [x] First GREEN proof: group, linked-media, pet, and meetup tests passed.
- [x] Re-run all focused directory and component tests after final edits.
- [x] Run Blade view compilation.
- [x] Run PHP syntax checks for changed PHP files.
- [x] Run Pint on the attributable PHP slice.
- [x] Run Larastan and report baseline failures distinctly.
- [x] Run the production Vite build.
- [x] Run the authenticated browser accessibility/geometry suite.
- [x] Inspect generated mobile and desktop screenshots.
- [x] Run the relevant broader architecture/localization regression slice.
- [x] Run the full serial Pest suite when shared repository activity permits an
  isolated attributable checkpoint.
- [x] Run composer validation/security audit and npm audit for release evidence.
- [x] Run isolated fresh migration and repeated seeding for release evidence.
- [x] Run route/config/view cache smoke checks.

## P14 — Publication And Recovery

- [x] Re-read the original request against the final staged diff.
- [x] Confirm query delta remains zero.
- [x] Confirm no secret, debug output, prototype marker, or unrelated file is
  staged.
- [x] Commit the coherent implementation, tests, and documentation on `main`.
- [x] Push normally to `origin/main`; never force-push.
- [x] Record commit ID and observed push result.
- [ ] If a regression is discovered, prefer a narrow forward fix; revert only
  the attributable commit when forward repair is unsafe.
- [ ] Monitor the canonical `/forum/groups` surface for unintended shared-style
  effects even though it does not consume this compatibility card.

Publication evidence: implementation commit
`0da0c3c7181716bab5651599aa5028525412a2e2` advanced `origin/main` from
`f23681ad0791da31a1fdd87bb5772cf62c075ff9` on 2026-08-03.

## P15 — Continuation Wave 1

- [x] Add EN/LT/RU long-copy and membership-state feature contracts.
- [x] Add executable fallback and escaping contracts for shared primitives.
- [x] Normalize unsupported media ratios to landscape.
- [x] Localize the class-based group fallback action.
- [x] Reuse shared typography leaves in discovery and expert cards only.
- [x] Remove superseded discovery-only title and description CSS.
- [x] Expand the authenticated browser matrix across EN/LT/RU and six widths.
- [x] Verify no clipped copy, undersized action, raw key, overflow, or console
  error in the connected browser.
- [x] Inspect LT and RU mobile screenshots.
- [x] Run focused PHP, localization, formatting, and production-build checks.
- [x] Run full static analysis and the final serial repository suite.
- [x] Inspect and publish the core attributable continuation diff on `main`
  (`3b0a4e5`).
- [x] Publish the localized fallback and final evidence follow-up on `main`
  (`24d8dd8`).

Continuation publication evidence: core commit `3b0a4e5` and localized
evidence follow-up `24d8dd8` both advanced `origin/main` normally on
2026-08-03.

## P16 — Shared Action Row And Marketplace Leaves

- [x] Prove Groups and Marketplace have the same bounded wrap and touch-target
  layout requirement without claiming a shared domain shell.
- [x] Add the anonymous `x-card-action-row` component with mergeable attributes
  and an explicit fill mode.
- [x] Move the group membership controls to the shared row while retaining the
  established browser hook and all group semantics.
- [x] Move marketplace save/detail controls to the shared row.
- [x] Reuse shared heading and description leaves in listing cards with `h3`
  semantics under the marketplace results heading.
- [x] Remove the superseded group action and marketplace excerpt CSS rules.
- [x] Add RED/GREEN feature contracts for both consumers and destination
  synchronization.
- [x] Verify Groups at six widths across EN/LT/RU and Marketplace at 320 and
  1440 pixels in authenticated Chrome.
- [x] Inspect group and marketplace mobile/desktop screenshots.
- [x] Run the final full static, test, seed, cache, dependency, and diff gates.
- [ ] Publish the isolated attributable slice to `origin/main`.

## Completion Rule

The `/groups` repair can be complete while the wider migration remains open.
The repair is complete only when P00 through P04, the applicable P12 records,
all currently applicable P13 gates, and P14 publication are done. P05 through
P11 remain an intentionally unlimited improvement backlog and must not block a
verified narrow fix unless an audit discovers a direct regression.
