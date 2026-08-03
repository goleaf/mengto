# Event State Machines

`ForumEventStatus`, `ForumEventRegistrationStatus`,
`ForumEventTeamMembershipStatus`, and `ForumEventVerificationStatus` are backed
enums. Event transitions are explicit in `canTransitionTo()` and executed by
`TransitionForumEventStatus` under authorization and row lock.

Registration status independently controls seat consumption, review,
confirmation, attendance, cancellation, and refund labels. Payment is not an
event status and cannot override eligibility. Cancellation preserves event,
registration, version, and history rows.

Ticket, payment, refund, competition, volunteer, vendor, incident, appeal, and
certificate state machines remain unimplemented.
