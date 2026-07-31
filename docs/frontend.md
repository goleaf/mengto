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

`resources/js/forum.js`, `messaging-center.js`, and `places-map.js` may:

- manage local DOM state;
- request browser media/geolocation only after explicit user action;
- synchronize accessible controls and visualizations;
- provide cleanup hooks.

They may not:

- decide authorization;
- issue custom fetch mutations when Livewire/forms already provide them;
- embed secrets or private server data;
- claim provider delivery or recording that does not exist.

## Interface States

Every data surface defines applicable loading, empty, filtered-empty, success,
recoverable error, fatal error, offline, unauthorized, disabled, pending, and
completed states. Status is textual and not color-only.

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
