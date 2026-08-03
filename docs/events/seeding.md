# Event Seeding

`ForumEventLifecycleBackfillSeeder` is additive and production-safe.
`ForumEventDemoSeeder` exits outside local/demo/testing and creates one stable
scenario per canonical Point 13 event type, varied visibility/format/status,
team roles, a weekly series with occurrences, and integrated legacy workflow
records. The conference scenario adds two tracks, two rooms, three sessions,
an animal-rest period, public speaker assignments, accessibility directions,
and independent room capacities. Repeated runs use stable keys and preserve
row counts.

`OrganizationAuthoritySeeder` adds three reusable portal organizations with
current roles, a pending invitation, independent restrictions, suspension,
verification, audit, and repeat-safe event-authority context.

The seeders do not fabricate provider payments, tickets, scores, incidents, or
volunteer assignments. Those scenarios remain absent until their domains exist.
