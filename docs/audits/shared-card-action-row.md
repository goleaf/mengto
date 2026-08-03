# Shared Card Action Row Audit

Date: 2026-08-03

## Decision

Group and marketplace cards share one bounded action-row layout through
`x-card-action-row`. The component owns only wrapping, minimum-width safety,
spacing, alignment, and the optional equal-fill behaviour. It does not own
labels, endpoints, payloads, active state, authorization, or domain ordering.

The group card keeps its directory shell, organizer block, membership state,
and recommendation dismissal. The marketplace card keeps its commerce shell,
price, seller, availability, facts, save operation, and detail destination.
The marketplace title and excerpt additionally reuse `x-card-heading` and
`x-card-description`; its media and commerce footer remain domain-specific.

## Shared Contract

- `data-card-action-row` is the stable semantic and browser-test hook.
- Rows always use `min-inline-size: 0` and `flex-wrap: wrap`.
- Fill rows give every non-icon action a bounded `8rem` flex basis while icon
  actions retain the shared 44-pixel control size.
- Server-backed forms remain `display: contents`, so their action controls
  participate in the same layout without invalid nested interaction.
- Caller attributes remain mergeable and complete utility classes remain
  statically discoverable by Tailwind.

## Classification

| Consumer | Shared row | Other shared leaves | Shell decision |
| --- | --- | --- | --- |
| group card | adopt | heading, description, media, stats | retain directory and group composition |
| marketplace listing card | adopt | heading and description | retain commerce card, media, facts, seller, and price composition |

Listing cards must not adopt `x-directory-card`: price placement, seller
identity, inventory facts, commerce actions, and marketplace media geometry
are product-specific. This is leaf and layout reuse, not a universal card.

## Data And Query Boundary

No controller, request, presenter, model, route, authorization, cache, query,
or persistence behaviour changes. Query delta is `0`.

## Acceptance Contract

`SharedCardActionRowTest` covers attribute forwarding, fill-mode rendering,
six group rows, listing heading hierarchy, shared descriptions, synchronized
media/title destinations, and the removal of duplicate group/marketplace
rules. Existing group, listing, linked-media, localization, and browser suites
remain release gates.

## Verification Evidence

Observed on the isolated attributable snapshot on 2026-08-03:

- RED proof: all three new contracts failed because the component and migrated
  consumers did not exist;
- shared action/group/listing/linked-media slice: 44 tests and 579 assertions;
- full serial Pest: 2,698 tests and 85,920 assertions;
- full Pint and Larastan passed with zero findings;
- production Vite build, strict Composer validation, Composer audit, and NPM
  audit passed with zero advisories;
- fresh SQLite migration and complete seed passed; the repeated seed preserved
  5 users, 12 listings, and 215 tables;
- config, route, and Blade view cache smoke passed;
- authenticated Chrome passed Groups at 320, 375, 768, 1024, 1440, and 1920
  pixels across EN/LT/RU and Marketplace at 320 and 1440 pixels;
- all 12 visible marketplace cards rendered one wrapping row, one shared `h3`,
  one shared description, in-viewport actions, and no sub-44-pixel targets;
- browser overflow, invalid images, unnamed controls, raw localization keys,
  and console errors remained zero; mobile and desktop screenshots were
  inspected.
