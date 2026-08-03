# Event Domain Model

`ForumEvent` owns identity, owner, creator, group context, format, visibility,
type, status, timezone, public region, encrypted access details, pet rules,
capacity, price metadata, cancellation, and optimistic version.

`ForumEventSeries` owns recurrence defaults. `ForumEventOccurrence` owns one
scheduled truth. `ForumEventVersion` preserves accepted snapshots.
`ForumEventTeamMembership` grants one scoped event role.
`ForumEventRegistration` stores the participant snapshot and occurrence;
`ForumEventRegistrationPet` stores each pet eligibility decision.

Existing invitation, update, message, review, history, taxon, report,
notification, group, credential, and pet-profile relations remain reused.
