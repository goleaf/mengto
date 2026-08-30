# Global Linked Media Navigation Plan

Plan date: 2026-08-03

Status: implemented and verified

## Goal

Make representative images behave consistently across PawCircle:

> When a card, row, or identity block already exposes an authorized internal
> detail/profile link for the represented object, its representative image or
> placeholder must navigate to the exact same destination.

The work is global, but it is not a blind replacement of every `<img>` with an
anchor. Galleries, photo viewers, current-page heroes, QR codes, private file
previews, maps, video controls, upload previews, and images inside another
interactive control retain their own semantics.

This plan is intentionally not time-boxed. Work advances only after each
package satisfies its acceptance and quality gates.

## User Outcome

- A person can click the photograph, avatar, cover, or placeholder they are
  already looking at instead of hunting for a small adjacent title link.
- Image and text destinations never disagree.
- Cards with action buttons keep those actions independent; the implementation
  does not turn the entire card into an unsafe nested link.
- Keyboard, screen-reader, touch, reduced-motion, and forced-colors behaviour
  remain complete.
- Private or unavailable resources do not gain discoverable links merely
  because an image is rendered.

## Current Repository Evidence

The 2026-08-03 audit found:

- 342 first-party Blade templates;
- 73 templates that render `<img>`, `x-responsive-image`, `x-card-media`,
  `x-avatar`, or `x-initials-avatar`;
- 45 media-bearing templates that also contain a normal link or link
  component in the same file;
- several shared presentation components, so changing one primitive can affect
  many routes;
- a mixed current state: some domains already implement linked media correctly
  while comparable preview cards do not.

Connected browser inspection of `/pets` confirmed:

| Pet | Text destination | Image destination |
| --- | --- | --- |
| Scout | `@mia-carter/scout` | None |
| Nori | `@mia-carter/nori` | None |
| Maple, Olive, Pico, Clover | No implemented profile destination | None |

Scout and Nori are the concrete regression: the presenter already supplies a
valid profile route, the title and action link use it, but `x-card-media` has no
navigation contract. The four informational preview pets must remain passive
until a real detail route exists; this plan never invents a destination.

## Canonical Interaction Contract

### Eligible media

Representative media is linked when all conditions are true:

1. The image or placeholder represents one identifiable resource.
2. The same rendered component already exposes a normal internal GET link to
   that resource, or its server-side presenter supplies that authorized link.
3. The destination is a real named route or a same-origin URL prepared by the
   server.
4. The current user is allowed to discover and view that destination.
5. The media region contains no nested link, button, form control, disclosure,
   video control, or other interactive descendant.

### Destination equality

- The media link and adjacent title/detail link use the same canonical URL,
  including required route parameters and safe query context.
- Blade does not infer a route from an image path, model ID, slug, title, or
  browser state.
- Filter, return, pagination, and conversation context may be retained only
  when the existing text link already retains them.
- `target="_blank"` is prohibited for internal detail/profile navigation.
- External attribution, map directions, telephone links, downloads, and media
  viewer fallbacks keep their explicit external or file semantics.

### Optional targets

- `null` means the media is deliberately passive.
- A missing target never renders `href="#"`, an empty `href`, JavaScript
  navigation, a guessed route, or a disabled anchor.
- Placeholders inherit the same target as an available image. A missing upload
  must not make a real resource harder to open.

### Accessibility

- Use a normal `<a href>`; no clickable `div`, JavaScript-only redirect, or
  `wire:click` is introduced for navigation.
- Every linked-media anchor has a localized accessible name that describes the
  destination, for example “Open Scout profile”.
- Existing useful image alt text remains useful; decorative identity images
  inside an already named wrapping link keep empty alt text.
- Links retain visible focus where they are part of the normal tab order.
- The initial implementation keeps links keyboard reachable. Any later
  reduction of duplicate image/title tab stops requires a documented manual
  screen-reader decision; it is not applied globally by assumption.
- No linked-media wrapper may contain another anchor, button, input, textarea,
  select, summary, audio, or video element.
- Pointer hover is an enhancement only. Touch and keyboard navigation work
  without hover.
- The rendered page remains usable at 200% zoom and without horizontal
  overflow.

### Visual behaviour

- The complete media rectangle is the hit target.
- Hover may apply a subtle token-based tint and at most a small image scale;
  there is no layout shift.
- A quiet open/details affordance may appear in a corner when it improves
  discoverability, but it does not obscure status badges or important media.
- `prefers-reduced-motion` removes scaling/transition motion.
- Forced-colors mode preserves a visible link boundary/focus indicator.
- Existing aspect ratios, responsive `srcset`, `sizes`, eager/lazy loading,
  dimensions, crop, decoding, and fetch priority remain unchanged.

## Required Architecture

### Passive image primitive remains passive

`resources/views/components/responsive-image.blade.php` remains responsible
only for responsive image output. It must not start resolving routes or
conditionally creating anchors.

This prevents gallery, hero, upload, and private-media consumers from becoming
links accidentally.

### New linked-media wrapper

Add `resources/views/components/linked-media.blade.php` with explicit props:

- `href: ?string`;
- `label: ?string`;
- `variant: card|avatar|thumbnail|placeholder`;
- `external: bool = false` only for an explicitly supported non-detail case;
- slot content containing the prepared image/placeholder and non-interactive
  badges.

Contract:

- with a non-null internal `href`, render a normal anchor;
- with null `href`, render a passive layout wrapper using the same geometry;
- merge caller classes through the attribute bag;
- never call `route()`, models, services, policies, or the container;
- never accept raw JavaScript, an onclick callback, or browser-supplied IDs.

### Extend card media deliberately

Extend `resources/views/components/card-media.blade.php` with optional
`href` and `linkLabel` props and delegate wrapping to `x-linked-media`.

All four current `x-card-media` consumers have non-interactive overlay slots:

- `pet-directory-card`;
- `neighbor-card`;
- `meetup-card`;
- `group-card`.

An architecture test must keep those slots free of interactive descendants.
If an interactive overlay is added later, it must be moved outside the media
anchor rather than nested inside it.

### Avatar links stay explicit

`x-avatar` and `x-initials-avatar` remain passive primitives. Identity
components wrap them in `x-linked-media` only when a profile/detail target is
already available. This keeps comments, private participant lists, managers,
and current-page identity heroes from acquiring guessed links.

### Presenter contract

Presenters and Livewire computed projections prepare an optional target:

```php
'media_target' => [
    'url' => route('pets.profile', $profile),
    'label' => __('presentation.open_profile', ['name' => $profile->name]),
],
```

Rules:

- emit the target only after visibility scoping and authorization;
- keep private resource identifiers out of public/browser state unless the
  authorized rendered link already requires them;
- use the same target object for image, title, and details action where they
  share a destination;
- do not add a database query solely to build the URL;
- route generation belongs to the presenter/controller/Livewire projection,
  not the Blade component;
- existing `detail_url`, `show_url`, `profile_route`, or `href` contracts may
  be adapted at the caller during migration; do not rewrite every presenter
  merely to rename keys.

## Initial Surface Matrix

### A. Confirmed gaps with an existing destination

These are the first implementation targets.

| Surface | Current text/action target | Required media behaviour | Primary files |
| --- | --- | --- | --- |
| Pet directory | Optional pet profile route | Link image only when `profile_route` exists | `components/pet-directory-card.blade.php`, `PreviewService`, `PetProfileCatalog`, `CreatedContentPresenter` |
| Group directory | Optional group detail route | Link cover only when `detail_route` exists | `components/group-card.blade.php`, `GroupPresenter`, `PreviewService` |
| Meetup directory | Optional meetup detail route | Link cover only when `detail_route` exists | `components/meetup-card.blade.php`, `PreviewService`, `CreatedContentPresenter` |
| Neighbor directory | Optional profile route | Link cover only when `profile_route` exists | `components/neighbor-card.blade.php`, `PreviewService` |
| Profile pet rows | Optional pet profile route | Link pet image to the same profile | `components/profile-pet-card.blade.php`, `PetProfileCatalog` |
| Discover results | Result route | Link result image to exact result URL | `components/discover-result.blade.php`, discover presenter/controller |
| Expert cards | Expert profile route | Link avatar/placeholder to expert profile | `components/expert-card.blade.php`, `ExpertPresenter` or current directory projection |
| Owner identity | Optional member profile route | Link avatar only when the name is linked | `components/owner-identity.blade.php`, `owner-summary.blade.php` |
| Booking expert summary | Expert profile route | Link avatar/placeholder to the same expert profile | `components/booking-content.blade.php` |
| Marketplace order item | Listing back/detail route | Link snapshot cover/placeholder to the listing only while listing access exists | `marketplace/orders/show.blade.php`, `ListingPresenter` |
| Message thread identity | Conversation details route | Link conversation avatar to details, not to a guessed member profile | `components/messaging-thread-header.blade.php`, `messaging-context.blade.php`, `MessagePresenter` |

### B. Already compliant; verify and harmonize without churn

These surfaces already place representative media inside an anchor or make the
complete item a single link:

- `components/place-card.blade.php`;
- `components/listing-card.blade.php`;
- `components/search-case-card.blade.php`;
- `components/care-journal-card.blade.php`;
- `components/medical-record-card.blade.php`;
- `components/device-card.blade.php` placeholder/icon region;
- `components/connection-identity.blade.php`;
- `components/messaging-inbox.blade.php`;
- `components/header-actions.blade.php`;
- `components/pet-friend-switcher.blade.php`;
- `components/post-heading.blade.php` author avatar;
- `components/story-rail.blade.php`;
- `components/media-link-card.blade.php`;
- `components/walk-message-summary.blade.php`;
- `components/post-media-gallery.blade.php` for its separate full-size viewer
  contract.

For these files, add regression evidence and shared styling only where the
rendered behaviour is equivalent. Do not rewrite proven markup just to use a
new component.

### C. Conditional targets requiring presenter/domain work

These media elements may become links only after the same projection has a
real authorized destination:

- `components/group-pet-card.blade.php`;
- `components/member-list.blade.php`;
- `components/mutual-neighbor-list.blade.php`;
- `components/profile-manager-list.blade.php`;
- `components/share-recipient-item.blade.php`;
- `components/activity-item.blade.php`;
- `components/group-chat-preview.blade.php`;
- member, pet, friend, and nearby rows inside
  `components/group-dashboard.blade.php` and `place-dashboard.blade.php`;
- `components/messaging-thread-header.blade.php` and
  `messaging-context.blade.php`;
- forum group, mentorship, event, and expert-session identity rows when their
  Livewire projection already contains a policy-safe profile/detail URL.

The absence of a target is a product/data prerequisite, not permission to
guess a slug or expose a private member.

### D. Separate media-viewer behaviour

These images must open or retain their media-specific experience rather than
navigate to a surrounding resource detail page:

- `components/post-media-gallery.blade.php` and PhotoSwipe;
- `components/pet-gallery.blade.php` if/when it adopts the canonical viewer;
- place gallery photos in `components/place-dashboard.blade.php`;
- forum topic media in `forum/show.blade.php`;
- forum journal media in
  `livewire/forum/forum-journal-timeline.blade.php`;
- lost/found, marketplace, medical, and care galleries/attachments where the
  product exposes a full-size or download action.

Viewer links keep progressive enhancement, intrinsic dimensions, keyboard
controls, Escape, focus restoration, authorization, and protected media URLs.
They are not rewritten to the card's detail route.

### E. Deliberate non-links

The following remain passive unless a separate approved requirement changes
their purpose:

- current-page cover, hero, and profile identity images in `detail-hero`,
  `context-hero`, `profile-hero`, `profile-identity`, `place-hero`, expert
  detail, pet detail, medical detail, care detail, lost/found detail, and event
  workspace views;
- QR codes and printable poster images;
- camera/video call previews and participant tiles;
- maps, map markers, diagrams, charts, and route previews;
- upload previews, editor images, validation examples, and form controls;
- private documents, evidence, medical/care/journal attachments without an
  independently authorized media/download route;
- decorative avatars and initials where no profile/details link is rendered;
- placeholders for resources that have no canonical internal detail page;
- external attribution images whose existing action is an external source.

## Delivery Packages

### LMN-00: Requirement and baseline ledger

1. Add a canonical product requirement stating the eligible-media contract.
2. Add a non-functional navigation requirement covering semantic anchors,
   target equality, authorization, nested-interaction prevention, and
   responsive/accessibility behaviour.
3. Add an initial inventory appendix or generated test dataset containing every
   eligible surface, selector, expected target selector, audience, and status.
4. Record the exact pre-change `/pets` regression for Scout and Nori.
5. Mark already-compliant, blocked, viewer-owned, current-page, and passive
   surfaces explicitly so absence of change is explainable.

Acceptance:

- every candidate has one classification and owner;
- no `unknown` item advances to implementation;
- the requirement and compliance matrix do not claim verification yet.

### LMN-01: RED contract tests

Create `tests/Feature/LinkedMediaNavigationContractTest.php` before production
changes.

The test dataset records:

- route and authenticated actor fixture;
- card/list selector;
- representative media selector;
- canonical text/action link selector;
- whether the image is expected linked, passive, viewer-owned, or excluded;
- required authorization state.

Assertions:

- eligible image has an ancestor anchor;
- image and canonical text/action anchors have identical resolved `href`;
- optional target renders no anchor when null;
- placeholder follows the same target contract;
- link has a meaningful accessible name;
- linked media contains no nested interactive descendant;
- internal targets do not open a new tab;
- unauthorized/private rows and counts stay absent rather than rendering a
  dead or forbidden link.

Add a component-focused test for `x-linked-media` and `x-card-media` null,
internal, placeholder, attribute-merge, alt, and slot behaviour.

### LMN-02: Shared Blade and visual primitives

1. Add `x-linked-media`.
2. Extend `x-card-media` with optional `href` and `linkLabel`.
3. Add `.linked-media` variants to `resources/scss/_media.scss` using existing
   tokens and mixins.
4. Preserve image dimensions, `srcset`, `sizes`, loading, decoding, and fetch
   priority exactly.
5. Add reduced-motion and forced-colors rules.
6. Prohibit nested interactive descendants through architecture tests.

Acceptance:

- null mode is visually identical and non-interactive;
- linked mode is a normal anchor with visible focus;
- hover produces no layout shift;
- no JavaScript is required;
- Vite production build is warning-free.

### LMN-03: Preview directories

Migrate:

1. `/pets` through `pet-directory-card`;
2. `/neighbors` through `neighbor-card`;
3. `/meetups` through `meetup-card`;
4. `/groups` through `group-card`;
5. mixed `/circle` entries that reuse those components.

Rules:

- route-bearing cards receive the same target for title, media, and details;
- route-less preview cards remain passive;
- action controls such as Follow, RSVP, Save, Join, or Invite remain separate;
- media badges/times remain non-interactive and safe inside the media link.

Acceptance:

- Scout and Nori images open their profiles;
- informational pets without routes remain passive;
- Apartment Pets and Small Dog Social covers open their implemented detail
  routes while preview-only cards remain passive;
- no card contains nested anchors/buttons;
- all directory filters/actions still work.

### LMN-04: Profile, discovery, and relationship surfaces

Migrate and verify:

- `profile-pet-card`;
- `discover-result`;
- `owner-identity` when `routeName` is present;
- connection and pet-friend cards already using `connection-identity`;
- profile-manager, mutual-neighbor, group-pet, and member lists only where the
  presenter already supplies an authorized route;
- recommendation, share-recipient, and activity rows only when their existing
  text/action link identifies the same represented resource.

Acceptance:

- avatar and name destinations are identical;
- revoked/private relationships remove the complete row or target before
  rendering;
- no new public Livewire model/relationship graph is introduced;
- block, mute, follow, request, share, and report controls remain independent.

### LMN-05: Persisted resource directories

Validate and harmonize without unnecessary rewrites:

- `/places`;
- `/marketplace` and related listing cards;
- `/lost-found`;
- `/medical-records`;
- `/care-journals`;
- `/devices`;
- `/experts`.

Required corrections include expert-card avatar/placeholder linking and the
marketplace order snapshot image linking back to its authorized listing.

Security requirements:

- portal media URLs remain authenticated and contained;
- image links never expose storage paths;
- private medical/care/device links use already authorized server projections;
- an order snapshot does not grant access to a listing that its policy no
  longer permits; use a passive snapshot when the canonical listing link is
  unavailable.

Acceptance:

- image, placeholder, title, and details targets match;
- no query is added in Blade or a loop;
- no additional resource becomes discoverable through href, count, alt, or
  aria-label leakage.

### LMN-06: Messaging and booking identities

1. Link expert avatar/placeholder in booking context to the existing expert
   profile.
2. Link message-thread avatar to the existing conversation-details route when
   that is the same visible identity context.
3. Keep conversation list items that are already whole-row anchors unchanged.
4. Do not guess member or pet profile URLs from conversation labels.
5. Keep call-stage images passive; calls are controls, not navigation.

Acceptance:

- avatar/details target equality is tested;
- filter/search/return context is retained where the current details link
  retains it;
- blocked/restricted conversations do not gain broader profile visibility;
- audio/video/info controls and mobile back navigation remain functional.

### LMN-07: Feed, publications, groups, and forum

1. Preserve linked author avatars in `post-heading`.
2. Preserve PhotoSwipe as publication image click behaviour.
3. Migrate any legacy single-image feed branch to a clear existing behaviour:
   canonical viewer when media metadata exists, otherwise post thread only
   when the image and thread link represent the same target.
4. Do not turn group-post images into links until a real post/topic detail URL
   is present in the projection.
5. Link persistent forum group/event/session/journal identity media only when
   the policy-scoped Livewire projection already exposes the same detail URL.
6. Keep forum topic and journal media on their media/viewer/download contract.

Acceptance:

- no PhotoSwipe, caption, transcript, sensitive-media disclosure, reaction,
  comment, poll, or moderation interaction is nested inside a detail link;
- repeated Livewire navigation does not lose targets or duplicate listeners;
- unauthorized forum/group records remain excluded before rendering.

### LMN-08: Gallery and detail-page media audit

Review every deliberate exception rather than leaving it implicit:

- pet, place, forum, journal, marketplace, lost/found, care, and medical
  galleries;
- profile, place, event, pet, expert, medical, care, marketplace, and
  lost/found heroes;
- posters, QR codes, maps, call previews, video posters, attachment previews,
  and uploads.

For galleries lacking a viewer, decide separately whether the image should
open the existing protected full-size media route. This is not replaced by a
self-link to the current detail page.

Acceptance:

- every media-bearing template has a recorded `detail`, `profile`, `viewer`,
  `download`, `external`, `current-page`, `action`, `decorative`, or `passive`
  classification;
- no current-page self-link is added merely to satisfy a count;
- QR, video, map, and upload interactions retain their correct semantics.

### LMN-09: Localization and accessibility closure

1. Add stable linked-media labels to `lang/en`, `lang/lt`, and `lang/ru`.
2. Verify placeholder sets and grammar for profile, pet, group, meetup, expert,
   listing, record, journal, conversation, and generic detail targets.
3. Review duplicate image/title links with keyboard and screen readers.
4. Verify visible focus, 200% zoom, reduced motion, forced colors, touch, and
   high-contrast modes.
5. Verify meaningful alt text and decorative alt boundaries.

Acceptance:

- no raw translation key or hardcoded first-party label is rendered;
- no unnamed link exists;
- no nested interactive content exists;
- destination purpose is understandable outside visual context;
- translated labels do not create overflow.

### LMN-10: Automated global enforcement

Add three persistent gates:

1. **Rendered contract test** with the route/surface dataset.
2. **Blade architecture test** rejecting interactive descendants inside
   linked-media/card-media slots and raw `onclick` navigation.
3. **Browser smoke matrix** that clicks representative images and asserts the
   observed destination, focus, overflow, and console state.

The rendered dataset must include at least:

- pets, neighbors, groups, meetups, circle, discover;
- places, marketplace, lost/found, experts;
- medical records, care journals, devices;
- profile/relationship directories;
- messages and booking context;
- feed/publication viewer;
- persistent forum groups, events, journals, expert sessions, and mentorship
  where seeded policy-safe targets exist.

Every newly introduced media-bearing card must either join the dataset or add
a documented exclusion with rationale.

### LMN-11: Browser and performance verification

Viewports:

- 320x700;
- 375x812;
- 768x900;
- 1024x768;
- 1440x900;
- 1920x1080.

Browser assertions:

- representative media click reaches the same URL as the title/detail link;
- Ctrl/Cmd-click and context-menu behaviour remain native;
- keyboard focus is visible and order is logical;
- no nested-interaction activation or accidental adjacent button submission;
- no horizontal page overflow;
- no broken images, duplicate IDs, console errors, failed Livewire requests,
  or unexpected external navigation;
- reduced-motion and forced-colors modes remain usable;
- image dimensions and layout stability are unchanged.

Performance/query assertions:

- no additional application query on preview/static pages;
- no N+1 increase on database-backed directories;
- no new image variants or larger card payloads;
- Vite CSS/JS size delta is recorded;
- Telescope/Debugbar query comparison is recorded for database-backed
  directory routes where available.

### LMN-12: Documentation, release, and publication

Update in the same verified implementation series:

- `docs/product-requirements.md`;
- `docs/non-functional-requirements.md`;
- `docs/frontend.md`;
- `docs/accessibility.md`;
- `docs/testing.md`;
- `docs/implementation-plan.md`;
- `docs/requirements/compliance-matrix.md`;
- `CHANGELOG.md`;
- this plan's status/evidence ledger.

Only mark the requirement `implemented and verified` after all applicable
packages and global gates pass.

## Test Plan

### Feature tests

- Extend existing directory tests rather than duplicating their route and
  fixture setup.
- Use XPath to compare exact `href` values between media and title/action
  links.
- Cover both image and placeholder states.
- Cover optional route present/absent states.
- Cover authenticated owner, allowed member, unauthorized member, inactive
  actor, revoked grant, private group, and hidden profile states as applicable.
- Assert that hidden resources expose neither media nor href.

### Component tests

- `x-linked-media` internal/null/external/attributes/label/slot variants;
- `x-card-media` linked and passive modes;
- avatar identity linked/passive modes;
- no nested interactivity;
- descriptive alt and accessible name.

### Livewire tests

- initial render contains the server-authorized target;
- tampered public state cannot change the target resource;
- repeated refresh/navigation retains the canonical URL;
- revoked access removes the link on the next render;
- no navigation uses `wire:click` as an authorization shortcut.

### Browser tests

- pointer click on image;
- keyboard activation of the link;
- native open-in-new-tab gesture for internal anchors;
- focus visibility and return behaviour around viewer-owned media;
- no action-button activation when clicking media;
- no layout shift or overflow across the viewport matrix;
- no console/network errors.

### Quality gates

Run sequentially where database-backed SQLite is shared:

1. focused linked-media/component tests;
2. affected domain directory tests;
3. Livewire tests;
4. Blade/architecture tests;
5. Pint;
6. Larastan;
7. full Pest suite;
8. fresh isolated migration and seed plus fixed-seeder idempotency;
9. Composer validate and audit;
10. NPM audit and Vite production build;
11. route/config/view cache smoke checks;
12. connected browser viewport, keyboard, click, and console matrix;
13. final requirement, documentation, secret, and scoped diff review.

## Query Delta

Expected query delta: zero.

URLs are derived from already prepared visible resources. If a presenter needs
an additional relationship only to identify a destination, it must be included
in the existing eager load or projection rather than queried inside Blade or a
loop. Any non-zero query delta blocks the package until justified and measured.

## Risks And Mitigations

| Risk | Mitigation |
| --- | --- |
| Nested links/buttons create invalid HTML | Link only the media region; architecture and rendered DOM tests reject interactive descendants |
| Image and title navigate differently | Prepare one server target and assert exact href equality |
| Private resource becomes enumerable | Emit target only after scope/policy checks; test negative actors |
| Gallery viewer is replaced by detail navigation | Classify viewer-owned media before migration and preserve PhotoSwipe/protected media routes |
| Current-page heroes gain pointless self-links | Explicit current-page exclusion |
| Duplicate keyboard stops become noisy | Manual keyboard/screen-reader review before any tabindex policy; do not hide links globally |
| Hover animation causes motion or layout shift | Tokenized transform only, reduced-motion override, fixed media geometry |
| Optional preview cards get fake routes | Null target renders passive media; no guessed slugs or hash links |
| Shared primitive changes dozens of pages unexpectedly | RED component tests, staged migration, no navigation inside `responsive-image` |
| Livewire target is browser-controlled | Server projection reloads/authorizes; public state stays scalar and untrusted |
| Dirty shared tree mixes unrelated work | Main-only workflow, explicit status/diff inspection, temporary scoped index |

## Stop Conditions

Stop the affected item/package and document the blocker when:

- no canonical internal GET route exists;
- the adjacent link does not represent the same object as the image;
- authorization or visibility cannot be proven before rendering;
- the media region contains interactive controls that cannot be safely moved;
- the image belongs to a viewer, download, QR, map, video, upload, or
  current-page hero contract;
- adding the target requires a new query or exposes private state without a
  reviewed projection change;
- EN/LT/RU accessible labels are incomplete;
- focused, architecture, Larastan, full-suite, build, or browser gates fail;
- concurrent dirty-tree work overlaps the same file and cannot be reconciled
  without changing another contributor's scope.

Do not count a stopped item as implemented. Record it as blocked with its exact
route, policy, data, or interaction prerequisite.

## Git And Delivery Strategy

- Work only on the existing `main` branch.
- Re-read status, staged/unstaged changes, and untracked files before every
  package.
- Preserve unrelated work; never reset, discard, re-clone, force-push, or
  rewrite history.
- Use a temporary `GIT_INDEX_FILE` for each attributable package in a dirty
  shared tree.
- Keep each commit coherent: component/contract, one domain migration, its
  tests, and its documentation/evidence.
- Inspect the complete staged diff and run `git diff --check`.
- Push to `origin/main` only after the required gates for the attributable
  slice and repository baseline are observed and reported accurately.

Recommended commit sequence:

1. requirement ledger and RED tests;
2. linked-media primitive and styles;
3. preview directories;
4. profile/discovery/relationship surfaces;
5. persisted resource directories;
6. messaging and bookings;
7. feed/forum/community boundaries;
8. global enforcement and browser evidence;
9. final documentation/compliance closure.

## Implementation Evidence — 2026-08-03

- `x-linked-media` and the compatible `x-card-media` extension render a normal
  anchor only for an explicit presenter-supplied target; missing targets remain
  passive.
- Eligible pet, group, neighbor, meetup, discover, profile, expert, booking,
  message, and marketplace media now reuse the exact adjacent canonical URL.
- The contract fixture accounts for all 73 audited media-bearing Blade
  templates and records intentional viewer, current-page, action, decorative,
  protected-download, composite, and passive exclusions.
- The focused contract passed 19 tests and 279 assertions; the affected
  feature/architecture slice passed 67 tests and 27,626 assertions.
- Pint, targeted and full Larastan, Composer validation/audit, npm audit, Vite
  build, cache smoke checks, fresh isolated SQLite migration, and repeated seed
  passed.
- Connected browser checks covered `/pets`, `/groups`, `/discover`, and
  `/messages?conversation=ari` at 320, 375, 768, 1024, 1440, and 1920 pixels.
  All 24 combinations had no overflow, unnamed linked media, nested interactive
  descendants, or console warnings/errors. Pointer and keyboard activation
  reached the canonical pet profile and exposed visible focus.
- The final serial Pest command passed 2,303 tests and 76,111 assertions in
  131.130 seconds after an earlier concurrent loader conflict disappeared.

## Definition Of Done

The global work is complete only when:

- every one of the 73 audited media-bearing templates has a current
  classification;
- every eligible representative image and placeholder links to the exact same
  authorized internal target as its title/details link;
- every non-link has a documented reason rather than an accidental omission;
- no nested interactive markup, fake link, JavaScript navigation, self-link,
  target mismatch, private target leak, N+1 query, or horizontal overflow
  remains;
- EN/LT/RU labels, meaningful alt text, focus, keyboard, touch, 200% zoom,
  reduced motion, and forced colors are verified;
- targeted and full automated gates pass;
- the connected browser matrix passes on representative routes and audiences;
- documentation, compliance evidence, changelog, commits, and pushed `main`
  match the observed result.

Until then, the status remains `proposed` or `in progress`, never
`implemented and verified`.
