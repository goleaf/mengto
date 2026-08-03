# Groups Shared Card UX Audit

Date: 2026-08-03

## Decision Summary

The `/groups` compatibility directory must use the same structural card
contract as the other bounded catalogue previews. The contract is composition,
not a domain-neutral mega-card:

- `x-directory-card` owns the card shell and the direct `media`, `body`, and
  optional `footer` regions;
- `x-card-media` owns responsive image containment, aspect ratio, and optional
  linked-media behaviour;
- `x-card-heading` owns the allowed heading levels, title typography, wrapping,
  and optional destination;
- `x-card-description` owns readable copy spacing, line height, wrapping, and
  colour;
- existing `x-status-badge`, `x-recommendation-reason`, `x-tag-list`,
  `x-stat-grid`, `x-icon-text`, and `x-action-control` own their established
  responsibilities;
- `x-group-card` retains only group-specific ordering and group-specific
  content.

This removes the image/text collision and creates one maintenance point for
future media separation, padding, card height, title, description, and footer
changes without forcing feed, commerce, medical, or operational cards into a
shape that does not fit their semantics.

## Route And Product Boundary

`GET /groups` is handled by `GroupDirectoryPreviewController`, receives
validated filters through `BrowseGroupsRequest`, and renders a bounded
presentation prepared by `GroupPresenter`. It is a compatibility preview.

`GET /forum/groups` remains the canonical persistent group directory with
database-backed visibility, membership, invitation, and management rules. A
visual refactor of `/groups` must not imply that prototype actions or preview
state are an authorization boundary, and it must not duplicate the persistent
group application layer.

## Observed Defect

The old shared shell rendered the media slot and body consecutively but did not
declare them as isolated regions. At the same time, `.group-card` overrode the
shared equal-height layout with `align-self: start` and `block-size: auto`.
Group title, description, two-column metrics, footer spacing, and the media/body
boundary were implemented locally.

That combination caused four UX and maintenance risks:

1. image overflow, overlays, or browser rounding could visually merge with the
   text because neither region owned containment and an opaque surface;
2. there was no explicit separator between media and copy;
3. cards in the same results row could have unrelated heights;
4. changing title, copy, metric, or footer rules required editing multiple
   component and SCSS files.

## Measured Component Topology

The current repository has four direct `x-directory-card` and `x-card-media`
consumers:

| Consumer | Shared shell before audit | Shared title/copy before audit | Shared footer before audit | Decision |
| --- | --- | --- | --- | --- |
| `x-group-card` | yes | no | no | migrate now |
| `x-pet-directory-card` | yes | no | no | migrate now |
| `x-neighbor-card` | yes | no | no | migrate now |
| `x-meetup-card` | yes | no | no | migrate now |

The wider component directory also contains media-bearing care, connection,
content, device, discovery, expert, feed, forum, listing, medical, place,
profile, search, service, sharing, and walking cards. Their existence is not
evidence that they share one DOM or interaction contract.

## Duplication Classification

### Safe shared structure

These rules are stable across catalogue cards and belong in shared primitives:

- clipped full-height article shell;
- media containment and aspect-ratio map;
- a visible media/body border;
- opaque, padded, flexible body surface;
- optional bottom-aligned footer with one separator;
- semantic `h2` or `h3` title with an optional link;
- readable, wrapping descriptive copy;
- two- and four-item statistic grid columns.

### Domain composition that stays local

These rules carry product meaning and must not become boolean switches on a
generic component:

- group privacy, official, recommendation, membership, and organizer state;
- pet owner/profile actions;
- neighbour mutual-contact context;
- meetup date, host, location, and RSVP state;
- place emergency/opening/review information;
- marketplace price and order state;
- medical privacy and clinical status;
- feed interaction and publication context.

### Candidates for later composition

Other cards may adopt one or more leaf primitives after their own contract is
verified. They should not automatically adopt `x-directory-card`:

- discovery and expert cards may reuse heading/description/media primitives;
- place, listing, and search cards need a deliberate directory-shell adapter
  because their metadata and actions have different geometry;
- feed, group-post, medical, care, booking, and device cards should keep their
  domain shells and only compose leaf primitives where semantics match.

## Shared API Contract

### `x-directory-card`

- renders one `article[role=listitem][data-ui-card]`;
- media is the first direct region and always contained;
- body is the second direct region, opaque, padded, flexible, and minimum-width
  safe;
- an optional named `footer` stays inside the body and is pushed to the bottom;
- callers may add domain hooks but may not change the region ordering.

### `x-card-media`

- delegates accessible optional linking to `x-linked-media`;
- delegates image source selection and dimensions to `x-responsive-image`;
- supports the explicit `portrait`, `square`, `wide`, and default `landscape`
  aspect-ratio vocabulary;
- contains media and overlays and never owns domain badges.

### `x-card-heading`

- accepts prepared text and an optional prepared URL;
- renders `h2` only for directory-item headings that sit directly under the
  page heading, otherwise `h3`;
- owns wrapping, typography, and bounded spacing variants;
- does not infer hierarchy from URL or route state.

### `x-card-description`

- renders escaped slot content in a semantic paragraph;
- owns readable line height, wrapping, colour, and bounded spacing;
- does not truncate product information or calculate presentation values.

### `x-stat-grid`

- receives prepared labelled values;
- automatically maps two and four items to intentional columns;
- does not query or calculate aggregates in Blade.

## Accessibility And Responsive Findings

The implementation contract covers the repository viewports at 320, 375, 768,
1024, 1440, and 1920 CSS pixels.

- Image alternative text remains supplied by each presenter.
- A linked image keeps an explicit accessible link label.
- Heading level is explicit and testable.
- Text uses `break-words` and `text-pretty`; card and regions use `min-w-0`.
- Media uses hidden overflow and the body uses an opaque background.
- The media/body separator is visible without relying on colour alone for any
  status meaning.
- Existing actions retain the shared minimum target, focus, keyboard, loading,
  and state behaviour.
- Equal-height behaviour is scoped to cards sharing a CSS grid row; mobile
  cards are free to grow with localized copy.
- The change adds no animation and therefore introduces no reduced-motion
  branch.

Long EN/LT/RU strings, forced colours, 200% zoom, keyboard flow, and browser
console output remain release gates even when the structural DOM tests pass.

## Data And Query Analysis

This refactor does not change the controller, request, presenter query path,
route, model, relationship, or aggregate strategy. `GroupCard` converts two
already-prepared strings into the input shape expected by `x-stat-grid`.

Expected query delta: `0`. Any measured increase is a regression and blocks
publication.

## Risks And Controls

| Risk | Control |
| --- | --- |
| Shared component change silently affects sibling directories | targeted tests for groups, pets, neighbours, meetups, and linked media |
| Card footer creates duplicate separators | footer region owns the single separator; callers remove local border/padding |
| Different copy lengths break row alignment | flexible body plus bottom footer and multi-viewport geometry assertion |
| New generic component accumulates domain switches | keep domain content in domain cards and document stop conditions |
| Dirty shared worktree mixes unrelated changes | attributable temporary index and complete staged-diff review |
| Compatibility preview is mistaken for canonical group authority | preserve the `/groups` versus `/forum/groups` documentation boundary |

## Stop Conditions

Do not migrate another card merely to increase component adoption. Stop and
design a separate composition contract when any of these is true:

- the root element is not a result-list article;
- media is optional, multi-item, user-editable, or an interaction workspace;
- footer state must remain adjacent to a particular content block;
- the card has private, financial, clinical, moderation, or real-time state
  whose semantics would be obscured by the directory contract;
- adopting the shell requires route, query, authorization, or domain changes;
- visual similarity is the only shared property.

## Acceptance Evidence

The package is eligible for publication only after all of the following are
observed on the attributable diff:

- focused PHP component and directory tests;
- linked-media navigation regression tests;
- Blade compilation and PHP syntax;
- Pint and Larastan;
- production Vite build;
- authenticated browser geometry and accessibility checks at all six target
  widths;
- screenshots at 375 and 1440 pixels;
- `git diff --check`, staged-diff review, and secret review;
- appropriate broader/full-suite results, with any unrelated baseline failure
  reported instead of hidden.

Observed on the isolated attributable staged tree on 2026-08-03:

- focused architecture/localization/directory slice: 88 tests and 61,421
  assertions;
- full serial Pest: 2,645 tests and 83,332 assertions;
- Larastan: 1,385 files with zero errors;
- fresh database: 130 migrations, 215 tables, and stable user count 5 after a
  repeated seed;
- Composer validation and audit plus NPM audit: passed with zero known
  vulnerabilities;
- config, event, route, and view cache generation: passed;
- Chrome: six cards passed the complete geometry contract at all six widths,
  all six lazy images loaded before mobile/desktop capture, and console errors
  remained zero;
- `git diff --check`: passed for the attributable temporary index.

The full shared working tree also ran 2,654 tests while other contributors had
uncommitted work. It passed 2,651 and failed three unrelated naming/member-route
tests. Exporting the attributable temporary index removed those foreign changes
and produced the clean full result above.

## Continuation Wave 1

The first post-publication continuation keeps the shell boundary unchanged and
closes the highest-value API and localization gaps:

- unsupported heading levels normalize to semantic `h3` output;
- unsupported media ratios normalize to the landscape contract;
- title and description values remain escaped at the shared primitive boundary;
- group fallback membership labels use the active locale catalogue;
- the six browser widths are distributed across EN, LT, and RU, retain long
  copy without clipping, and exercise joined, pending, unjoined, and secondary
  actions with 44-pixel targets;
- `discovery-result-card` adopts heading and description leaves and
  `expert-card` adopts the heading leaf, while neither adopts the directory
  shell because their content topology remains domain-specific.

This wave changes no route, query, authorization, model, persistence, cache, or
external-service path. Query delta remains `0`.

Fresh continuation evidence on the attributable snapshot:

- focused card/discovery/expert regression: 50 tests and 631 assertions;
- full serial Pest: 2,662 tests and 84,714 assertions;
- full Pint, Larastan, PHP/JavaScript syntax, and production Vite build passed;
- forum source preservation, 38,377 generated requirements, localization, and
  icon-system ratchets passed;
- connected Chrome passed all EN/LT/RU group viewports with zero clipping,
  undersized actions, raw keys, overflow, broken images, or console errors.

The concurrently dirty tree passed 2,654 of 2,662 tests; all eight failures
were caused by the separate uncommitted pet flow requesting unavailable dynamic
component `lucide-clipboard-heart`. That foreign file set is absent from the
attributable continuation snapshot.
