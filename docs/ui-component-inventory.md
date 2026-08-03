# UI Component Inventory

| Pattern | Canonical implementation | Event use |
| --- | --- | --- |
| Application shell | `x-app-shell`, `x-main-sidebar-layout`, `x-livewire-app-layout` | directory and detail shells |
| Primary navigation | `x-primary-navigation`, desktop/mobile nav items | one meetups entry |
| Page identity | `x-page-header`, `x-detail-page`, `x-detail-identity` | `x-page-header` is the target; event directory/workspace still use legacy `forum-header` |
| Legacy page identity | `.forum-header` and unclassified duplicated utility markup | measured migration inventory; care-directory and messaging-directory variants are retired, and no new consumers are allowed |
| Section identity | `x-section-heading` | canonical for `h2`; existing feed/composer `h1` uses require page-route classification |
| Iconography | `x-ui-icon` backed only by Lucide | canonical name, semantic size, stroke, fill, color, and ARIA contract; direct/dynamic and legacy debt are zero |
| Status | `x-status-badge`, `x-notice`, `x-callout` | event/registration/pet state |
| Forms | `x-form-field`, `x-forum-error-summary`, action form/group/control | builder and registration mutations |
| Collections | `x-directory-page`, toolbar, result grid, empty state, pagination | event discovery |
| Directory result cards | `x-directory-card`, `x-card-media`, `x-card-heading`, `x-card-description`, optional named footer | shared structural contract for group, pet, neighbour, and meetup compatibility cards; domain ordering remains local |
| Discovery directions | `x-discovery-category-nav` plus canonical section heading | seven module entry paths |
| Discovery filtering | `x-discovery-toolbar`, `x-search-field`, filter chips | validated query/category and preference reset |
| Discovery recommendations | `x-discovery-section`, `x-discovery-result-card` | bounded cards with status, media, reason, deep link, hide action |
| Event schedule | `x-event-schedule` | responsive day agenda, status, track, room, public staff and manager edit action |
| Event compatibility cards | `x-meetup-card`, `x-nearby-meetup-list`, `x-group-event-card` | catalogue/feed/group projections only |
| Live event surface | event directory/workspace Blade | canonical database-backed event data |
| Organization authority | organization directory/workspace/invitation Livewire views | shared shell, page header, panel, status, form, and action components |
| Place authority | existing place directory/detail, `x-form-field`, canonical status labels | persisted public projection and event place/venue selection without exact-location state |
| Feedback/loading/offline | flash feedback, targeted `wire:loading`, offline notices | every mutation surface |

The three compatibility card components have different host contexts and are
not removed by this package. They must continue to deep-link to canonical
event routes and must not query, expose private venue data, or invent status.

The discovery card is the only card for the cross-module recommendation hub.
It composes existing status, linked-media, responsive-image, action, and icon
components and is not a replacement for full module directory cards.
Dynamic member detail reuses `x-page-header`, `x-main-sidebar-layout`,
`x-content-publication-card`, `x-content-panel`, and `x-definition-list`; it
does not add a second profile-card system.

The directory-card family shares only stable result-card structure: contained
media, opaque copy surface, semantic title, readable description, and optional
bottom footer. Feed, commerce, medical, care, device, place, and other
domain-rich cards are not automatically part of this family. Their migration
requires the classification and stop conditions in
`docs/audits/groups-shared-card-ux-audit.md`.
