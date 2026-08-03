# Event Organizers

Creator, authoritative owner, organizer snapshot, optional responsible group,
and active event-team members are separate. `ForumEventTeamRole` limits edit,
registration, check-in, safety, schedule, judging, payment-review, media, and
audit intent. `ForumEventPolicy` maps only currently implemented operations.

`ForumEvent` may now reference one responsible `Organization` independently of
its creator, owner, organizer snapshot, group, and event team. Organization
membership is current and role-scoped. Owner, administrator, event, finance,
safety, marketplace, shelter, member, and read-only audit roles are distinct.

Removed or expired organization members lose event visibility, private
location, participant, invitation, and check-in access even when a stale event
team row remains. Historical attribution is retained. Suspension creates
independent restrictions for creation, publication, registration, payment,
participant data, check-in, results, and invitations. Assigned emergency staff
retain minimized participant access only while their organization membership
is current.
