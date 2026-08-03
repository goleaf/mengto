# UI Component Inventory

| Pattern | Canonical implementation | Event use |
| --- | --- | --- |
| Application shell | `x-app-shell`, `x-main-sidebar-layout`, `x-livewire-app-layout` | directory and detail shells |
| Primary navigation | `x-primary-navigation`, desktop/mobile nav items | one meetups entry |
| Page identity | `x-page-header`, `x-detail-page`, `x-detail-identity` | `x-page-header` is the target; event directory/workspace still use legacy `forum-header` |
| Legacy page identity | `.forum-header` and unclassified duplicated utility markup | measured migration inventory; care-directory and messaging-directory variants are retired, and no new consumers are allowed |
| Section identity | `x-section-heading` | canonical for `h2`; existing feed/composer `h1` uses require page-route classification |
| Status | `x-status-badge`, `x-notice`, `x-callout` | event/registration/pet state |
| Forms | `x-form-field`, `x-forum-error-summary`, action form/group/control | builder and registration mutations |
| Collections | `x-directory-page`, toolbar, result grid, empty state, pagination | event discovery |
| Event schedule | `x-event-schedule` | responsive day agenda, status, track, room, public staff and manager edit action |
| Event compatibility cards | `x-meetup-card`, `x-nearby-meetup-list`, `x-group-event-card` | catalogue/feed/group projections only |
| Live event surface | event directory/workspace Blade | canonical database-backed event data |
| Organization authority | organization directory/workspace/invitation Livewire views | shared shell, page header, panel, status, form, and action components |
| Place authority | existing place directory/detail, `x-form-field`, canonical status labels | persisted public projection and event place/venue selection without exact-location state |
| Feedback/loading/offline | flash feedback, targeted `wire:loading`, offline notices | every mutation surface |

The three compatibility card components have different host contexts and are
not removed by this package. They must continue to deep-link to canonical
event routes and must not query, expose private venue data, or invent status.
