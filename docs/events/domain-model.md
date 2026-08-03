# Event Domain Model

`ForumEvent` owns identity, owner, creator, group context, format, visibility,
type, status, timezone, public region, encrypted access details, pet rules,
capacity, price metadata, cancellation, and optimistic version.

`Organization` is the optional responsible tenant. It owns current/expiring
memberships, account-bound invitations, verification state, independent
operational restrictions, and append-only audit events. It does not replace
the event owner, organizer, event-team membership, payment recipient, venue,
group, or professional profile.

`Place` is the canonical location identity. It separates safe public facts
from encrypted exact location and owns account-bound access grants, reveal
audits, and material location versions. One optional `Venue` supplies
operational timezone and independent people/animal capacities; typed
`VenueArea` records may back event rooms. Events and occurrences reference
these records without copying exact addresses.

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
