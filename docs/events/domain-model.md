# Event Domain Model

`ForumEvent` owns identity, owner, creator, group context, format, visibility,
type, status, timezone, public region, encrypted access details, pet rules,
capacity, price metadata, cancellation, and optimistic version.

`ForumEventSeries` owns recurrence defaults. `ForumEventOccurrence` owns one
scheduled truth. `ForumEventVersion` preserves accepted snapshots.
`ForumEventTeamMembership` grants one scoped event role.
`ForumEventRegistration` stores the participant snapshot and occurrence;
`ForumEventRegistrationPet` stores each pet eligibility decision.

`ForumEventTrack` groups public agenda content. `ForumEventRoom` represents an
event-scoped physical or online space and encrypts exact directions and access
links. `ForumEventSession` belongs to exactly one occurrence and may reference
one track and room; it owns UTC times, IANA timezone, capacity, reservation
policy, status, ordering, optimistic lock version, and an encrypted conflict
override snapshot. `ForumEventSessionStaff` assigns a scoped event-team member
to a session role and independently controls public attribution.

Existing invitation, update, message, review, history, taxon, report,
notification, group, credential, and pet-profile relations remain reused.
