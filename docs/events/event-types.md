# Event Type Registry

`ForumEventType` defines 16 canonical Point 13 types and preserves nine legacy
values for backwards compatibility. Each enum case exposes translated labels,
category, default icon, pet model, and capability flags for online,
recurrence, sessions, competition, ticketing, and public directory support.

Factories expose explicit states for the supported types. The demo seeder
creates one event for every canonical Point 13 case without pretending that
capability flags are completed subsystems.
