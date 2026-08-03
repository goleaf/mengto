# Event Schedules

An event has authoritative UTC timestamps and an IANA timezone; occurrences
can override date, format, location, capacity, and status. Each schedule
session belongs to exactly one occurrence and must remain inside that
occurrence's time range and timezone.

## Durable Model

- `ForumEventTrack` provides stable, ordered agenda tracks.
- `ForumEventRoom` provides ordered physical or online spaces, independent
  capacity and accessibility information. Exact directions and online links
  are encrypted and excluded from generic serialization.
- `ForumEventSession` stores controlled type/status/reservation enums, times,
  capacity, ordering, required-session state, lock version, and idempotency.
- `ForumEventSessionStaff` assigns active event-team members as speakers,
  moderators, trainers, judges, route leaders, welfare officers, or staff.

`SaveForumEventSession` authorizes every invocation and locks the event,
occurrence, and affected session. It validates event state, event ownership of
the occurrence/track/room, occurrence boundaries, timezone, room and
occurrence capacities, and active team membership.

## Conflict Rules

Non-cancelled overlapping sessions conflict when they use the same room,
track, or team member. A schedule manager can resolve the overlap but cannot
silently bypass it. Only an event owner, administrator, or primary organizer
may retain an intentional overlap, with a reason of at least 20 characters.
The reason and conflict identifiers are encrypted on the session and recorded
in event audit history.

The workspace renders one bounded, locale-aware responsive agenda. Public
viewers do not receive draft sessions or private staff assignments. Scoped
schedule managers receive the class-based Livewire editor and keyboard-safe
edit controls. Session reservations, per-session waitlists, attendee-selected
sessions, drag reordering, and participant schedule-change notifications
remain open work and are not claimed by this slice.
