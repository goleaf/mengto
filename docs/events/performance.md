# Event Performance

Directory queries apply visibility before bounded pagination and eager-load
only owner/group/registration facts used by cards. Workspace occurrences and
organizer queues are bounded and explicitly projected. Capacity and check-in
operate on indexed keys under short transactions.

The 2026-08-03 idempotent-backfill measurement on 25 fully initialized events
fell from 125 queries before filtering to one query after filtering, with zero
event, registration, or pet-link changes. `EventLifecycleQueryBudgetTest`
enforces a maximum of two queries for a complete six-event fixture.

Final Livewire-payload and browser latency measurements remain pending. The
browser layout audit does verify zero horizontal overflow for the event
directory and recurring-event detail at desktop and mobile viewports. Public
caches never include exact venue, registration, eligibility, or private
message data.
