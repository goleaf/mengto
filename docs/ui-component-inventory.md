# UI Component Inventory

| Pattern | Canonical implementation | Event use |
| --- | --- | --- |
| Application shell | `x-app-shell`, `x-main-sidebar-layout`, `x-livewire-app-layout` | directory and detail shells |
| Primary navigation | `x-primary-navigation`, desktop/mobile nav items | one meetups entry |
| Page identity | `x-page-header`, `x-detail-page`, `x-detail-identity` | title, back link, current status |
| Status | `x-status-badge`, `x-notice`, `x-callout` | event/registration/pet state |
| Forms | `x-form-field`, `x-forum-error-summary`, action form/group/control | builder and registration mutations |
| Collections | `x-directory-page`, toolbar, result grid, empty state, pagination | event discovery |
| Event compatibility cards | `x-meetup-card`, `x-nearby-meetup-list`, `x-group-event-card` | catalogue/feed/group projections only |
| Live event surface | event directory/workspace Blade | canonical database-backed event data |
| Feedback/loading/offline | flash feedback, targeted `wire:loading`, offline notices | every mutation surface |

The three compatibility card components have different host contexts and are
not removed by this package. They must continue to deep-link to canonical
event routes and must not query, expose private venue data, or invent status.
