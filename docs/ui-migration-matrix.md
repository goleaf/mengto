# UI Migration Matrix

| Existing surface | Point 13 decision | Status |
| --- | --- | --- |
| reference directories (`/pets`, `/places`, `/groups`, `/neighbors`, `/discover`) | retain canonical `x-page-header` path and verify semantic hook | targeted-verified |
| private care directories (`/medical-records`, `/care-journals`) | canonical `x-page-header` with privacy context and authorized create action | migrated and targeted-verified |
| operational directories (`/lost-found`, `/marketplace`, `/experts`) | canonical `x-page-header` with domain content below identity | migrated and targeted-verified |
| `/messages` | canonical `x-page-header`; nine-folder toolbar remains above messaging shell | migrated and targeted-verified |
| `/meetups` catalogue directory | database-backed `ForumEventDirectory` in shared shell; page identity still uses legacy `forum-header` | data/workflow migrated; page identity pending |
| `/meetups/{event}` detail | canonical `ForumEventWorkspace`; page identity still uses legacy `forum-header` | data/workflow migrated; page identity pending classification |
| `/organizations` and organization workspace | class-based Livewire tenant authority in the shared shell and canonical page-header/panel patterns | added and targeted-verified |
| event responsible organization | role- and capability-scoped select inside the existing event builder | added and targeted-verified |
| `/places` and `/places/{place}` | existing responsive directory/detail consume policy-scoped persisted places and stable dynamic slugs | data authority migrated; visual shell preserved |
| add-place composer | persist an owner-scoped unlisted review candidate through `CreatePlace` | migrated and targeted-verified |
| event place/venue fields | bounded policy-scoped selects carry IDs and public region only | added and targeted-verified |
| `meetup-card` | compatibility presenter card, canonical route target | preserved |
| nearby meetup list | compact contextual projection, canonical directory link | preserved |
| group event card | group calendar projection linked to `ForumEvent` | preserved |
| created meetup URL | compatibility created-content detail | preserved |
| booking forms | remain booking domain; no false event ticket reuse | intentionally separate |
| generic calendar/activity cards | remain projections; occurrence aggregate is event truth | documented boundary |
| event status rendering | shared `status-badge` and translated enum labels | standardized |
| multi-pet registration | semantic checkbox fieldset and error summary | migrated |
| occurrence selection | bounded responsive select/list in workspace | added |
| event schedule | canonical `x-event-schedule` backed by occurrence-scoped tracks, rooms, sessions and server conflict checks | added |
| manual check-in/out | shared action patterns and server-confirmed state | improved |

No new obsolete CSS or JavaScript was added by the event package; the
pre-existing `forum-header` family remains scheduled for migration. QR scanner,
participant session-reservation, ticket, competition, incident, vendor, and
volunteer components do not exist and are not represented as migrated.
