# Event Performance

Directory queries apply visibility before bounded pagination and eager-load
only owner/group/registration facts used by cards. Workspace occurrences and
organizer queues are bounded and explicitly projected. Capacity and check-in
operate on indexed keys under short transactions.

The 2026-08-03 idempotent-backfill measurement on 25 fully initialized events
fell from 125 queries before filtering to one query after filtering, with zero
event, registration, or pet-link changes. `EventLifecycleQueryBudgetTest`
enforces a maximum of two queries for a complete six-event fixture.

`EventScheduleWorkflowTest` renders 30 sessions with a shared track, room, and
speaker in exactly five schedule queries. The measured locale-aware projection
was 17,654 bytes and the regression ceiling is 20,000 bytes. The query count
does not grow with sessions. The workspace caps schedule reads at 500 rows and
eager-loads every displayed relation with explicit columns.

The browser layout audit verifies zero horizontal overflow for the event
directory, recurring-event detail, and three-session conference schedule at
desktop and mobile viewports. End-to-end latency measurement remains pending.
Public caches never include exact venue, registration, eligibility, or private
message data.
