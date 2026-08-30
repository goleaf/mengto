# Event Requirements Status

| Area | Current status | Evidence or gap |
| --- | --- | --- |
| Canonical aggregate and type registry | Implemented and tested | `ForumEvent`, `ForumEventType` |
| Lifecycle, ownership, versions | Implemented and tested | lifecycle Actions, versions, team membership |
| Series, occurrences, timezone | Implemented and tested | series/occurrence models and occurrence-scoped sessions |
| Tracks, rooms, sessions | Implemented and tested | durable schedule models, Livewire editor, responsive agenda |
| Schedule conflict control | Implemented and tested | room/track/staff overlap detection and audited owner override |
| Pet registration and eligibility | Implemented and tested | registration service and pet rows |
| Meetup privacy and exact locations | Focused implementation verified | deny-first visibility/block policy, absent unauthorized payload markers, encrypted manual exact access, scoped audited Place reveal |
| Meetup capacity/waitlist | Focused implementation verified | locked event/occurrence allocation, active uniqueness, deterministic promotion, real two-process final-place race |
| Tickets/payments/refunds | Not implemented | no verified event payment provider or ticket aggregate |
| QR/offline check-in | Not implemented | manual server-confirmed check-in only |
| Competitions | Implementation present; release verification pending | competition migration, 14 models, policies, and create/entry/judge/score/correction Actions exist; factories, complete entrypoints, focused/full gates, and release evidence remain incomplete |
| Vendors/volunteers | Not implemented | no verified durable vendor/sponsor or volunteer-shift workflow |
| Event incidents/weather plans | Not implemented | generic report and emergency plan only |
| EN/LT/RU UI | Implemented for current surface | recursive key-parity test |
| Browser/performance release evidence | Measured for implemented surface | schedule has a five-query/20 KB budget; six event viewport audits pass |

The machine-readable source of truth is
`docs/requirements/forum-requirements.json`; every unproved Point 13 atom
remains `planned` or `discovered` there.
