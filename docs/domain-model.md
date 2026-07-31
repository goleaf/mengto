# Domain Model

## Roles

| Role | Capabilities |
| --- | --- |
| Visitor | View public profiles, discovery, published content, public places, and privacy-safe lost/found information |
| Member | Maintain account, create permitted content, engage, report, and manage owned resources |
| Pet owner | Manage pet identity, medical record, care journal, devices, and grants |
| Co-owner / family | Receive per-pet and per-section capabilities from the owner |
| Sitter | Receive time-bounded care/device/location capabilities |
| Expert | Maintain professional profile and participate in bookings/consultations |
| Veterinarian | Receive explicitly selected medical/care/device measurements |
| Trainer / behaviour professional | Receive training, activity, behaviour, and selected media |
| Groomer | Receive grooming and handling context |
| Organizer / group moderator | Manage owned event/group state within scope |
| Marketplace buyer / seller | Manage participant-side reservations and orders |
| Search coordinator / volunteer | Coordinate assigned lost/found work without public private data |
| Platform administrator / moderator | Apply explicit audited platform capabilities, never an implicit data bypass |
| Temporary link recipient | Access only hashed-token scope until expiry/revocation |

No role is inferred from a hidden button or browser-provided identifier.

## Core Entities

### Identity

- `User`: authenticated person, immutable actor key, locale, timezone, status,
  and administrative capability.
- `PetProfile`: durable owner-scoped pet identity with independent visibility
  and profile metadata.
- `UserDomainState`: encrypted, versioned per-user persistence for social
  feed, connection, friendship, group, event, message, and place mutations.

### Forum And Knowledge

- Topic -> answers -> comments.
- Topic/answer engagements, votes, reports, blocks, notifications.
- Knowledge article -> immutable versions, collaborators, corrections, and
  append-only workflow events.

### Experts

- Expert profile -> credentials, services, availability, publications,
  engagements, reports, reviews.
- Booking -> consultation and optional scoped document grants.

### Marketplace

- Listing -> engagement, reservation, report.
- Accepted reservation -> one order.
- Order -> optional dispute and review.

### Lost And Found

- Search case -> sightings, sectors, tasks, volunteers, updates, alerts,
  reports.

### Medical

- Medical record -> events, vaccinations, weight entries, medications, doses,
  documents, reminders, access grants.

### Care

- Care journal -> routines, tasks, entries, media, access grants.

### Devices

- Smart device -> pet assignments, readings, events, commands, safe zones,
  automations, automation runs, access grants.
- Reading/event promotion creates a linked care or medical record while
  preserving the source device row.

## Important State Machines

### Listing

```text
draft -> published -> reserved -> completed
                  \-> paused / withdrawn
```

Reservation:

```text
requested -> accepted -> completed
         \-> declined / cancelled / expired
```

Order:

```text
pending -> confirmed -> completed
       \-> cancelled / disputed -> resolved
```

Every transition checks actor participation, current state, available quantity,
and idempotency under a short transaction.

### Search Case

```text
draft -> active -> resolved / closed
                \-> paused -> active
```

Precise holding/sighting details are coordinator-only.

### Medical / Care / Device Access

```text
active -> expired
      \-> revoked
```

The raw access token is returned once; only a digest is persisted.

### Device Command

```text
created -> sent -> delivered -> accepted -> completed
       \-> failed / expired / unknown
```

Unknown is a real terminal observation and must not trigger automatic replay of
a dangerous command.

### Care Task

```text
planned -> due -> completed / partially-completed
             \-> postponed / missed / cancelled / refused / needs-help
```

Completion is idempotent and audit-preserving.

### Collaborative Guide

```text
draft -> submitted-for-review -> changes-requested
                              \-> community-reviewed
                              \-> expert-reviewed
community-reviewed / expert-reviewed -> published
published -> correction-requested / outdated / archived / replaced
```

Community and expert review are independent from authorship. Expert review is
also independent from reputation and requires current professional
verification. A rollback creates another version. Locale variants share a
stable translation group but remain separate articles.

## Ownership Invariants

- Every private record has one authoritative owner actor key.
- Child routes and actions verify parent ownership and relationship.
- A grant cannot broaden the owner's capabilities or outlive its expiry.
- Revocation removes server and cached access.
- Pets remain distinct even when one device, fountain, litter box, camera,
  aquarium, or household zone is shared.
- Uncertain device attribution is stored as uncertain, never guessed.

## Community Review And Notes

```text
panel: awaiting-assignment -> in-review -> decided / overridden / appealed
                            \-> expired / cancelled
note: proposed -> in-review -> community-assessed -> moderator-review
               \-> gathering-evidence -> in-review
      moderator-reviewed -> published / rejected / archived
      published -> revised / revalidation-due
```

Panel decisions classify low-risk content and do not replace moderation.
Changing a pending note cancels the stale panel and assignments before a new
review can begin. See `docs/community-review.md`.

### Peer Mentorship

```text
requested -> active -> completed / ended
         \-> declined
requested -> cancelled
completed -> independently validated
```

The mentor and mentee acknowledge peer-support boundaries independently.
Participants may communicate only while active, and either participant may
end, block, report, or leave one optional feedback record. Completion does not
create reputation until an uninvolved administrator validates interaction
evidence, blocks, and open reports. See `docs/mentorship.md`.

### Persistent Groups

```text
group: active -> closed -> active
                \-> archived
membership: pending -> active / rejected
active -> left / removed / banned
invitation: pending -> accepted / declined / revoked / expired
activity: scheduled -> active -> completed / cancelled / archived
announcement: published -> archived
group file: active -> archived
poll: draft -> open -> closed / archived
```

Visibility is public, request-to-join, private, or unlisted. Roles are owner,
administrator, moderator, steward, member, or restricted member. Ownership
transfer updates owner projections atomically; role/member management cannot
remove the owner. Every mutation appends an event.

Existing topics and guides may be associated with one group without changing
their IDs, replies, versions, reactions, subscriptions, bookmarks, reports,
or URLs. Group activities, announcements, and files have independent stable
keys and lifecycle state. A poll owns ordered stable options and at most one
vote projection per user; that projection stores the validated selected or
ranked option IDs, while option counters are reconciled in the same locked
transaction. Closing time is effective even before the stored lifecycle value
is changed. See `docs/groups.md` and `docs/polls.md`.
