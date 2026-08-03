# UI Migration Matrix

| Existing surface | Point 13 decision | Status |
| --- | --- | --- |
| `/meetups` catalogue directory | database-backed `ForumEventDirectory` in shared shell | migrated previously; extended |
| `/meetups/{event}` detail | canonical `ForumEventWorkspace` | migrated previously; extended |
| `meetup-card` | compatibility presenter card, canonical route target | preserved |
| nearby meetup list | compact contextual projection, canonical directory link | preserved |
| group event card | group calendar projection linked to `ForumEvent` | preserved |
| created meetup URL | compatibility created-content detail | preserved |
| booking forms | remain booking domain; no false event ticket reuse | intentionally separate |
| generic calendar/activity cards | remain projections; occurrence aggregate is event truth | documented boundary |
| event status rendering | shared `status-badge` and translated enum labels | standardized |
| multi-pet registration | semantic checkbox fieldset and error summary | migrated |
| occurrence selection | bounded responsive select/list in workspace | added |
| manual check-in/out | shared action patterns and server-confirmed state | improved |

No obsolete CSS or JavaScript was added. QR scanner, schedule builder, ticket,
competition, incident, vendor, and volunteer components do not exist and are
not represented as migrated.
