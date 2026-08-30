# UI Migration Matrix

| Existing surface | Point 13 decision | Status |
| --- | --- | --- |
| direct/dynamic Lucide calls and arbitrary icon sizes | migrate through `x-ui-icon`, add icons to unambiguous actions, ratchet all legacy debt to zero | implemented and verified at zero debt with full release and browser gates |
| reference directories (`/pets`, `/places`, `/groups`, `/neighbors`) | retain canonical `x-page-header` path and verify semantic hook | targeted-verified |
| `/pets` static nearby-pet prototype | replace six fixture cards, session-only Follow actions, and PHP-array filters with the owned/shared `PetProfileWorkspace`, validated Eloquent filters, invitation state, real profile links, and canonical empty states | migrated and release-verified across EN/LT/RU at 1440/375/320px |
| direct compatibility directory cards (`group`, `pet`, `neighbor`, `meetup`) | compose shared media/body/footer, heading, description, and compatible statistic primitives while retaining domain content locally | migrated, isolated staged-tree verified, and published |
| `/discover` static demo | replace hard-coded query/results/pulse/trending/weekend panels with the database-backed explainable recommendation hub | migrated and browser-verified |
| discovery member destination | replace guessed/static owner links with `members.show`, a policy-scoped minimized profile composed from canonical profile/content primitives | added and targeted-verified |
| legacy discover result components | remove `discover-query`, `discover-results`, and `discover-result`; compose canonical primitives through four discovery components | removed and targeted-verified |
| private care directories (`/medical-records`, `/care-journals`) | canonical `x-page-header` with privacy context and authorized create action | migrated and targeted-verified |
| operational directories (`/lost-found`, `/marketplace`, `/experts`) | canonical `x-page-header` with domain content below identity | migrated and targeted-verified |
| `/messages` | canonical `x-page-header`; nine-folder toolbar remains above messaging shell | migrated and targeted-verified |
| `/meetups` catalogue directory | database-backed `ForumEventDirectory` in shared shell with canonical `x-page-header` | migrated and targeted-verified |
| `/meetups/{event}` detail | canonical `ForumEventWorkspace` with canonical `x-page-header` | migrated and classified as detail |
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

No new obsolete CSS or JavaScript was added by the event package; the retired
`forum-header` family now has zero active Blade or SCSS consumers. QR scanner,
participant session-reservation, ticket, competition, incident, vendor, and
volunteer components do not exist and are not represented as migrated.
