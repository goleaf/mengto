# Portal Calendar

`ForumEventOccurrence` is the authoritative dated event instance and
`ForumGroupActivity` remains a compatibility group-calendar projection linked
to `ForumEvent`. Event timestamps preserve UTC instants and the authoritative
IANA timezone.

The current portal does not have a canonical aggregated calendar that merges
care, medical, bookings, events, and volunteer shifts, nor a revocable external
calendar feed. Confirmed event registrations therefore cannot yet be claimed
as globally aggregated calendar items.
