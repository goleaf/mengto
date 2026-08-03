# Event Seeding

`ForumEventLifecycleBackfillSeeder` is additive and production-safe.
`ForumEventDemoSeeder` exits outside local/demo/testing and creates one stable
scenario per canonical Point 13 event type, varied visibility/format/status,
team roles, a weekly series with occurrences, and integrated legacy workflow
records. Repeated runs use stable keys and preserve row counts.

The seeder does not fabricate provider payments, tickets, scores, incidents,
volunteer assignments, or organization tenants. Those scenarios remain absent
until their domains exist.
