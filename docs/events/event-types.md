# Event Type Registry

`ForumEventType` defines 16 canonical Point 13 types and preserves nine legacy
values for backwards compatibility. Each enum case exposes a translated label,
category, and capability flags for online, recurrence, sessions, competition,
ticketing, and safety review. Default icon, participant/pet templates, required
fields, organizer types, and public-directory metadata remain open registry
work and are not inferred from the current capability methods.

Factories expose explicit states for the supported types. The demo seeder
creates one event for every canonical Point 13 case without pretending that
capability flags are completed subsystems.
