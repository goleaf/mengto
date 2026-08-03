# Event Requirements Status

| Area | Current status | Evidence or gap |
| --- | --- | --- |
| Canonical aggregate and type registry | Implemented and tested | `ForumEvent`, `ForumEventType` |
| Lifecycle, ownership, versions | Implemented and tested | lifecycle Actions, versions, team membership |
| Series, occurrences, timezone | Implemented and tested | series/occurrence models and occurrence-scoped sessions |
| Tracks, rooms, sessions | Implemented and tested | durable schedule models, Livewire editor, responsive agenda |
| Schedule conflict control | Implemented and tested | room/track/staff overlap detection and audited owner override |
| Pet registration and eligibility | Implemented and tested | registration service and pet rows |
| Privacy and exact locations | Implemented and tested | encrypted casts and policies |
| Capacity/waitlist | Legacy event scope implemented | occurrence-aware capacity and promotion require broader concurrency coverage |
| Tickets/payments/refunds | Not implemented | no verified event payment provider or ticket aggregate |
| QR/offline check-in | Not implemented | manual server-confirmed check-in only |
| Competitions/vendors/volunteers | Not implemented | no durable aggregate/state machines |
| Event incidents/weather plans | Not implemented | generic report and emergency plan only |
| EN/LT/RU UI | Implemented for current surface | recursive key-parity test |
| Browser/performance release evidence | Measured for implemented surface | schedule has a five-query/20 KB budget; six event viewport audits pass |

The machine-readable source of truth is
`docs/requirements/forum-requirements.json`; every unproved Point 13 atom
remains `planned` or `discovered` there.
