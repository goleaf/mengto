# Events, Attendance, And Clubs

> Point 13 status and the canonical detailed documentation now live in
> `docs/events/index.md`. This historical Phase 8 document remains valid for
> the original durable meetup workflow; where it differs, the Point 13
> lifecycle, occurrence, version, team, and multi-pet boundaries take
> precedence.

## Purpose And Boundary

`ForumEvent` is the authoritative platform event aggregate. It owns the
organizer snapshot, type, visibility, physical/online format, schedule,
capacity, registration policy, public location scope, protected access
details, attendance requirements, animal restrictions, accessibility,
pricing metadata, photo consent, welfare rules, cancellation, and history.

`ForumGroup` remains the club aggregate. A club event has an optional group
relation; the event policy first enforces group visibility and membership.
`ForumGroupActivity` remains a compatibility calendar projection and links to
one canonical event. New group activities create that event in the same
transaction.

The retired JSON event state may retain only personal interest, calendar, and
reminder preferences. Registrations, payment claims, check-in, organizer
actions, attendee messages, reviews, and reports cannot mutate that state.
The legacy `/compose/meetup` URL redirects to the authoritative class-based
Livewire event form.

## Event Model

An event has a stable route key and:

- an optional current organizer user plus preserved key/name snapshots;
- a controlled type, visibility, format, status, and locale;
- timezone-aware start/end timestamps;
- optional capacity, one registration policy, and a waitlist flag;
- a public location scope and encrypted exact location/online URL;
- attendance, vaccination jurisdiction, animal-age, accessibility, photo
  consent, welfare, emergency, cost, currency, and refund fields;
- optional global-taxonomy and group links;
- an optimistic `lock_version`, cancellation fields, and archival timestamp.

Point 13 extends that aggregate with a separate authoritative owner, pet and
accessibility status enums, explicit lifecycle dates, an event version,
recurring series and concrete occurrences, scoped team roles, occurrence-aware
registrations, accepted snapshots, and one eligibility row per selected pet.

Money is stored in integer minor units. A positive price and refund policy may
be published, but registration rejects paid checkout until a verified payment
integration exists. The application never records a synthetic charge.

## Visibility And Protected Access

Every event requires an active authenticated account with a verified email.
Within that portal boundary, public events are visible to members, group
events require current group access, and private events require a current,
accepted, unexpired invitation. Administrators retain audited operational
access.

Exact locations, online access URLs, emergency plans, registration
requirements notes, invitation messages, private review safety feedback,
idempotency keys, and internal metadata are hidden from normal serialization.
Exact access details are loaded only after `viewAccessDetails` authorization
for an organizer, administrator, or confirmed/checked-in attendee.

Organizer verification is projected from a current, independently reviewed
professional credential. Event volume, reactions, karma, trust, or reviews
cannot create a professional badge.

## Registration And Attendance

`ForumEventRegistrationService` owns registration, review, cancellation,
waitlist promotion, and check-in:

1. every request reauthorizes the event and validates browser input;
2. one user/event row and unique idempotency keys prevent duplicates;
3. the event row is locked while capacity and ordering are evaluated;
4. open registration confirms immediately, approval registration remains
   pending, and invitation-only registration requires an accepted invitation;
5. a full event assigns a stable waitlist position only when enabled;
6. cancellation retains the row and promotes the next eligible waitlisted
   registration transactionally;
7. only a confirmed registration can be checked in;
8. each transition writes append-only event history.

Guest count consumes capacity. Pet selection is limited to a profile owned by
the registrant and is checked against configured age bounds. Requirements
acceptance is explicit. Vaccination text is an event condition, not a health
credential, and no private medical document is collected.

The current registration flow also supports several active pet profiles that
the participant owns or co-manages. Missing or manually reported medical and
eligibility data remains pending review and cannot silently pass check-in.

## Invitations, Updates, And Messages

Organizers can invite an active internal user, set an expiry, and revoke an
invitation. Recipients alone accept or decline. Expired invitations grant
neither visibility nor registration authority.

Updates are append-only and either public or attendee-only. Rescheduling and
cancellation create updates and history in the same transaction. Attendee
messages are visible only to the organizer and currently eligible attendees;
an organizer-only audience never leaks to participants. Action idempotency
keys are unique and actor/event ownership is checked on replay.

Notifications use the existing in-application notification model. This
workflow does not require a queue, scheduler, websocket server, or cron job.

## Reviews, Reporting, And Consent

Only a checked-in participant may publish one post-event review. Reviews store
a rating, recommendation flag, public text, optional private moderator safety
feedback, and moderation status. A locked event/review lookup and database
uniqueness serialize competing submissions.

Reports use the unified polymorphic `ForumReport` pipeline. Reporter identity
and evidence remain private under the moderation policies. Event cancellation
or review removal never deletes reports or audit history.

Photo consent is recorded per registration and defaults to ask-first. It is
not inferred from attendance. Media publication remains subject to the
separate upload, consent, and moderation boundaries.

## Livewire Interface

`ForumEventDirectory` and `ForumEventWorkspace` are class-based Livewire 4
components with separate Blade views and form objects.

- Directory search, type, format, and period are URL-backed and reset bounded
  pagination.
- Queries apply visibility before search, counts, eager loading, and
  pagination.
- Public component state contains scalar filters, form values, and locked
  identifiers rather than model graphs or protected details.
- Every mutation reloads the record and reauthorizes the concrete action.
- Loading targets, disabled submit states, offline messaging, validation
  summaries, empty states, semantic headings, labels, tables, and
  confirmation prompts are localized.
- Event pages expose a normal link and server-rendered route when
  `wire:navigate` is unavailable.

The interface uses the existing responsive design tokens. It has no
drag-only operation, flashing urgency treatment, or color-only state.

## Backfill And Seeding

`BackfillForumEvents` converts each first-party `EventCatalog` record into one
system-managed canonical event using the legacy key as its stable URL. It also
links each unlinked group activity to one `group-event-{stable_key}` event.
Backfill uses stable identifiers and never infers sensitive event meaning from
free-text titles.

`ForumEventBackfillSeeder` is production-safe and additive.
`ForumEventDemoSeeder` runs only in local, demo, or testing environments and
uses production Actions. Repeated execution preserves event IDs, group
activity links, registrations, invitations, messages, reviews, reports, and
history.

Deployment order:

1. back up the database;
2. deploy code and the additive event migration;
3. run migrations;
4. run the production-safe forum system seeder or the event backfill seeder;
5. verify old meetup stable keys, group activity links, and protected access;
6. build assets and warm supported caches.

Never run the demo seeder or `migrate:fresh` against a real environment.

## Recovery

- A failed transaction leaves no partial registration or event history.
- A repeated idempotency key returns the same actor-owned result; a conflicting
  owner is rejected.
- A cancelled event is restored only through a reviewed forward Action that
  records new history.
- A bad legacy mapping is corrected by updating the canonical relation while
  retaining the legacy key and append-only history.
- Missing encrypted access data is an incident; never substitute a public
  field or log the secret.
- After event data exists, schema rollback is forward-only. Do not drop event
  tables to recover an application deployment.

## Verification

Primary coverage is
`tests/Feature/Forum/EventWorkflowTest.php`, with route compatibility in
`tests/Feature/MeetupDirectoryPreviewTest.php`, group-event integration in
`tests/Feature/Forum/GroupContentAndPollWorkflowTest.php`, and schema,
factory, seeder, localization, and architecture coverage in their shared
feature suites.

The exact requirement scope, commands, observed counts, browser checks, and
completion evidence are maintained in
`docs/plans/forum-phase8-events-work-package.md`.
