# Frontend Architecture

## Layers

| Layer | Responsibility |
| --- | --- |
| Blade pages | Server-rendered document structure and prepared data |
| Blade components | Reusable presentation without persistence or queries |
| Livewire components | Authorized, validated server-backed interaction |
| Alpine | Local ephemeral UI state supplied by Livewire |
| Vanilla modules | Map, messaging, and browser media progressive enhancement |
| Tailwind CSS | Utility tokens, responsive primitives, state variants |
| SCSS | Existing semantic component system pending measured migration |

No React, Vue, Svelte, jQuery, Axios, Volt, Flux, or Filament is part of the
current architecture.

## Blade Data Contract

- Controllers/Livewire prepare every value.
- Blade loops only over prepared bounded collections.
- Presentation conditions are allowed; business/state/permission decisions
  are not.
- User content is escaped.
- Repeated markup becomes a Blade component with explicit props.
- All visible static text uses translation keys.

## Livewire Navigation Lifecycle

For any `wire:navigate` page:

1. normal anchor navigation remains functional;
2. document title, focus, scroll, and navigation announcement are restored;
3. maps, media tracks, observers, timers, and event listeners are torn down;
4. initialization is idempotent after repeated navigation;
5. persisted DOM never contains stale authorization-sensitive data.

## JavaScript Boundaries

`resources/js/forum.js`, `messaging-center.js`, `photo-viewer.js`, and
`places-map.js` may:

- manage local DOM state;
- request browser media/geolocation only after explicit user action;
- synchronize accessible controls and visualizations;
- provide cleanup hooks.

They may not:

- decide authorization;
- issue custom fetch mutations when Livewire/forms already provide them;
- embed secrets or private server data;
- claim provider delivery or recording that does not exist.

## Publication Photo Viewer

`resources/views/components/post-media-gallery.blade.php` is the single
full-size publication-photo entry point. It retains a normal full-size anchor
as the no-JavaScript fallback and progressively enhances each bounded gallery
with PhotoSwipe 5.4.4. Responsive `srcset` metadata, stable per-photo keys,
localized controls, keyboard/touch navigation, zoom, URL deep links, modal
semantics, and exact-trigger focus restoration are browser-owned presentation
concerns.

`FeedPresenter` prepares the public photo contract and batch-loads shared
`PhotoAsset`, `PhotoReaction`, and `PhotoComment` data. The
`PhotoInteractionController` delegates authenticated, active-member mutations
to `PerformPhotoInteraction`; the Action resolves the photo key against the
server catalogue before policy-authorized relational changes. A unique
database constraint allows one reaction per member and photo. The
`photo-social-panel` Blade component remains passive, renders the latest 40
escaped comments with the full count, and posts ordinary CSRF-protected forms
with duplicate-safe comment idempotency keys.

At narrow widths the social panel is a scrollable bottom sheet below the
reserved image area. At desktop widths it becomes a fixed 27-rem side panel.
The viewer must keep at least 44-pixel controls and avoid image/panel overlap
and horizontal page overflow from 320 through 1920 pixels.

## Linked Representative Media

`x-responsive-image` remains a passive image primitive. `x-linked-media`
adds a normal semantic anchor only when a presenter supplies an explicit
optional `media_target` containing the already authorized canonical URL and a
localized accessible label. `x-card-media` delegates only this presentation
boundary and never discovers routes, models, or permissions in Blade.

Representative photographs, avatars, covers, and placeholders on migrated
pet, group, neighbor, meetup, discovery, expert, booking, messaging, profile,
and marketplace surfaces use the exact same URL as the adjacent title or
details affordance. A missing target renders a passive container instead of a
fake link. Galleries and photo viewers, current-page heroes, QR codes, maps,
video and call previews, upload controls, private downloads, and media inside
another interactive control preserve their existing semantics. Source guards
reject nested interactive descendants inside the linked media primitive.

## Interface States

Every data surface defines applicable loading, empty, filtered-empty, success,
recoverable error, fatal error, offline, unauthorized, disabled, pending, and
completed states. Status is textual and not color-only.

## Authentication Interface

Anonymous visitors are redirected to account entry before product route-model
binding. `/` is a protected portal destination, not a marketing or discovery
page. All guest and protected account-access pages use `components.auth-layout` and
the shared flat `auth-*` field, header, status, submit, and flow-link
components. The layout is mobile-first: a compact brand story precedes the
form at narrow widths, while desktop uses a two-column story and form
workspace. The form remains the first interactive task in logical keyboard
order after the home link.

Livewire owns only typed form and submission state. Shared Blade components
own labels, autocomplete, help and validation associations, loading copy, and
offline feedback. Password-type `auth-field` instances also own one native,
localized visibility button. Each field remains statically masked before
Alpine initializes, keeps its visibility state independent and browser-local,
and does not add a server request or persistent browser value.
`resources/scss/_auth.scss` owns responsive composition, 44-pixel targets,
focus, reduced-motion-safe transitions, and forced-colors boundaries.
Account-access links and post-auth redirects use ordinary document navigation
so Vite preload tags are not inserted twice during Livewire page transitions;
mutations themselves remain server-authoritative Livewire operations. See
`docs/superpowers/specs/2026-07-31-auth-interface-redesign.md`.

## Organization Workspaces

`/organizations` and `/organizations/{organization}` use the canonical page
header, bounded content panels, native forms, localized feedback, visible
focus, stable database-backed keys, and mobile-first grids. The invitation
response is an account-bound canonical page reached by an ordinary signed
link; accepting or declining remains a server-authoritative mutation.

Blade receives only prepared presentation values. Member emails and internal
restriction reasons are absent rather than visually hidden when the current
actor lacks management authority. Organization browser coverage remains an
open P02 release gate and is not implied by server-render tests.

## Peer Mentorship

`/forum/mentorship` composes three class-based Livewire components for
discovery, mentor-scope management, and private participant workflows. The
components keep filters and form data in small validated state, expose why a
mentor matched, and retain normal form semantics for keyboard and assistive
technology users. Private messages, lifecycle actions, report reasons, and
truthfulness acknowledgement are prepared by PHP and rendered through
localized Blade templates. See `docs/mentorship.md`.

## Verification

- architecture tests for passive Blade and no Volt;
- translation-key checks;
- Vite production build;
- responsive screenshots/checks at 320, 375, 768, 1024, 1440, and wide
  desktop;
- keyboard and focus review;
- no console/network errors on critical flows;
- repeated Livewire navigation teardown check.
- publication-photo viewer checks at 320, 375, 768, 1024, 1280, 1440, and
  1920 pixels, including URL updates, next/previous, zoom, Escape, focus
  return, disabled guest mutations, 44-pixel targets, and image/panel overlap.
- mentorship desktop/mobile checks for overflow, labels, 44-pixel action
  targets, private-thread visibility, report controls, and a real Livewire
  message mutation.

## Persistent Groups

`/forum/groups` is the persistent directory; `/forum/groups/{group}` is the
authorized workspace and management host. Directory, workspace, and
management are independent class-based Livewire components with normal links,
native form controls, localized labels/errors, action-specific loading,
offline feedback, bounded pagination, and empty/private/invited/member states.

The static `/groups` route remains a clearly separate compatibility preview.
It does not render or mutate relational membership authority. See
`docs/groups.md`.

`GroupContentWorkspace` is composed inside the authorized persistent group
workspace. It renders bounded announcement, topic, guide, event, file, and
poll sections and exposes manager controls through native expandable forms.
Poll ballots use native radio, checkbox, and select controls with fieldsets,
legends, textual result states, action-specific loading, and 44-pixel mobile
targets. Files remain ordinary authorized download links, and normal links
remain functional with or without `wire:navigate`.

The rendered package was checked at 1440x900 and 375x812. It had one page
heading, no horizontal overflow, no unnamed controls, 44-pixel poll controls,
successful Livewire vote/update feedback, and no current-page console errors.
See `docs/polls.md`.

## Forum Journals

`/forum/journals` hosts a class-based Livewire directory and creation form.
The existing topic page composes the journal timeline for an explicitly
journal-backed topic. Normal links remain functional and route/controller
authorization protects export and media responses.

The timeline uses native forms, semantic time elements, tables and progress
elements, textual metric fallbacks, field-linked validation, precise loading
targets, an offline status, and neutral milestone/setback language. It does
not require a chart library, drag-and-drop, hover, or client-owned business
state. See `docs/journals.md`.

## Expert Question Sessions

`/forum/expert-sessions` uses a class-based Livewire directory and a
server-rendered session workspace. The directory supports bounded URL-backed
search, scope, and period filters. The workspace coordinates the moderated
question queue, answer sources, immutable corrections, reports, archive, and
localized safety disclaimer.

Blade receives prepared arrays and never queries models or services. Pending
questions are removed by server policy before rendering. Normal links remain
functional without `wire:navigate`; no workflow requires hover,
drag-and-drop, or client-owned business state. See
`docs/expert-question-sessions.md`.

## Progressive Pet Profile Interface

The authenticated pet workspace keeps all twelve completion destinations in
the main content column and uses ordinary `?step=` links. Only one section body
is present at a time, the selected link uses `aria-current="step"`, and every
step provides its purpose, saved/optional text state, mutation-free skip, and
44-pixel actions. It has no left subnavigation, numeric disclosure score,
hover-only control, or exact-location input. On narrow screens the ordered
navigator becomes one contained horizontal scroll-snap row so the active form
does not start below twelve vertically stacked cards; tablet and desktop keep
the complete two/three-column grid without page-level overflow.

The separate forms preserve linked labels, help and error text, targeted
loading, dirty feedback, offline status, keyboard focus, and responsive grid
collapse. The preview step retains the stable profile link, QR alternative,
policy-filtered public route, lifecycle form, and bounded audit table.
