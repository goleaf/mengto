# Event Requirements Status

| Area | Current status | Evidence or gap |
| --- | --- | --- |
| Canonical aggregate and type registry | Implemented and tested | `ForumEvent`, `ForumEventType` |
| Lifecycle, ownership, versions | Implemented and tested | lifecycle Actions, versions, team membership |
| Series, occurrences, timezone | Foundation implemented | series/occurrence models; no schedule tracks |
| Pet registration and eligibility | Implemented and tested | registration service and pet rows |
| Privacy and exact locations | Implemented and tested | encrypted casts and policies |
| Capacity/waitlist | Legacy event scope implemented | occurrence-aware capacity and promotion require broader concurrency coverage |
| Tickets/payments/refunds | Not implemented | no verified event payment provider or ticket aggregate |
| QR/offline check-in | Not implemented | manual server-confirmed check-in only |
| Competitions/sessions/vendors/volunteers | Not implemented | no durable aggregate/state machines |
| Event incidents/weather plans | Not implemented | generic report and emergency plan only |
| EN/LT/RU UI | Implemented for current surface | recursive key-parity test |
| Browser/performance release evidence | Partially measured | backfill query budget passes; complete browser and payload release gates remain open |

The machine-readable source of truth is
`docs/requirements/forum-requirements.json`; every unproved Point 13 atom
remains `planned` or `discovered` there.
