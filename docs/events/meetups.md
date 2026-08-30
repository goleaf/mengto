# Meetups

## Scope And Reuse Decision

`/meetups` is the mobile-first social projection of the canonical
`ForumEvent` aggregate. PawCircle does not have a second `Meetup` table,
participant system, pet authority, location store, notification service,
discussion channel, report pipeline, or block graph. General event packages
such as payments, competitions, vendors, or conferences remain governed by
the wider Point 13 documentation and are not implied by this Meetup slice.

The authenticated route surface is:

| Route | Purpose |
| --- | --- |
| `meetups.index` | Paginated Discover, My Meetups, and Invitations projections |
| `meetups.create` | Dedicated mobile-first draft/create form |
| `meetups.show` | Authorized detail and RSVP workspace |
| `meetups.edit` | Organizer edit and publish workspace |
| `meetups.manage` | Organizer requests, attendance, waitlist, invitations, updates, cancellation, and schedule workspace |

All routes use the portal account boundary plus private-response middleware.
They emit no-store, no-referrer, and noindex headers. Route identifiers never
grant access and no state-changing GET route exists.

## Lifecycle And Discovery

An organizer may save an incomplete `Draft`; it is visible only to the
organizer or an explicitly authorized operator. Publishing reloads and locks
the record, requires a complete future schedule and required safety/location
content, synchronizes the default occurrence, and records history. Published
Meetups may be cancelled but are not hard-deleted. Cancellation retains the
record and participant history, closes active participation, synchronizes
occurrences, publishes a safe update, and sends deduplicated notifications.

Upcoming, ongoing, and past presentation is derived from UTC timestamps and
the stored IANA timezone. No cron job changes a Meetup merely because its end
time passed. Discover includes only authorized, non-draft, non-cancelled,
upcoming records and sorts by the next start time. Search is limited to safe
title, public location summary, and controlled category/type data; exact
address fields are neither selected for cards nor searched. Results are
paginated.

Visibility and admission remain separate:

- visibility controls whether a member may discover or open the Meetup;
- registration policy controls open confirmation, organizer approval, or
  account-bound invitation admission;
- organization and group visibility use current canonical membership;
- block relationships are deny-first and cannot be bypassed through a direct
  URL, invitation, registration, message, or exact-location grant;
- suspended/inactive organizers cannot mutate and their Meetups accept no new
  participation.

## Participation, Capacity, And Waitlist

`ForumEventRegistration` is the durable participation record. Meetup-facing
states use `Pending`, `Confirmed`, `Waitlisted`, participant cancellation,
organizer removal, manual check-in, and historical attended/completed states.
Pending and waitlisted users are never treated as confirmed.

Registration is handled inside an immediate/row-locking transaction. The
service reloads and locks the event and occurrence, reauthorizes, recalculates
seat use (`1 + guest_count`), and then creates or transitions the registration.
The database-enforced `active_scope_key` prevents duplicate active
participation. A payload checksum binds each register operation to its event,
occurrence, account, guest quantity, selected pets, consent, and rules:
identical replay returns the same result and changed replay fails.

Capacity counts people, not pets. A full Meetup either rejects the request or
creates a waitlisted registration. Waitlist position is monotonically assigned
and promotion orders by position then durable row ID under the same event
lock. Leaving or organizer removal promotes the first entry that fits; an
approval rechecks capacity and may move the request to the waitlist. Immutable
participation-transition rows record successful state changes.

The generalized P17 capacity-pool, hold, allocation, and typed-waitlist tables
remain available for wider event work, but this Meetup implementation does not
claim to use them as its allocator. Its current guarantee is the locked event
and occurrence transaction plus database uniqueness. A two-process SQLite
race test with `IMMEDIATE` transactions proves that two simultaneous users
claiming one remaining place produce exactly one confirmed and one waitlisted
registration.

## Pet Participation

A person may attend with no pet when the configured policy permits it. Every
selected `PetProfile` is resolved server-side. Registration, organizer
approval, and waitlist promotion require an active profile and current
`PetProfileAccess::View` authority for the authenticated owner or active
manager. Pending, invited, suspended, revoked, expired, future, or explicitly
denied manager records fail. Species and age rules use the canonical Taxon and
PetProfile data; Meetup does not store a second species taxonomy or any
vaccination document, veterinary record, microchip scan, or medical proof.

Cards and detail pages expose aggregate pet/attendee information only. Joining
does not make a private user or private pet globally visible, and no private
pet identifier, name, media URL, or manager relation is serialized to an
unauthorized viewer.

## Location Privacy

`location_scope` is the approximate human-readable area used for discovery.
Legacy/manual exact instructions are encrypted on `ForumEvent` and are
projected only after `viewAccessDetails` authorizes the organizer,
administrator, or currently confirmed/check-in participant. Requested,
waitlisted, rejected, removed, cancelled, blocked, and historical-ineligible
users receive neither the field nor a CSS-hidden copy.

When a Meetup links a canonical `Place`, exact address, coordinates, and
private instructions stay on `Place`. They are never copied into the Meetup.
A confirmed participant must also hold a current event-scoped
`event_attendance` grant (or be an authorized Place manager). Exact details
are returned only after an explicit `RevealPlaceExactLocation` action, which
reauthorizes the Meetup and grant under lock and appends a Place access audit.
The Livewire state is cleared on the next hydration. Removal, cancellation,
expiry, or block makes an old grant unusable because reveal rechecks current
Meetup participation. Coordinates are not rendered by the Meetup view.

Meetups do not request browser geolocation, track background movement, or add
a map provider.

## Invitations, Organizer Controls, And Notifications

Meetup invitations are internal, authenticated, recipient-bound records with
expiry and status. There is no bearer-token share link to forward. Only the
intended active account can accept or decline; capacity and current
eligibility are checked again when that account registers. Organizers can list
and revoke pending invitations without deleting history.

Organizer operations use policies and locked records for edit, publish,
request approval/rejection, participant removal, waitlist promotion,
reschedule, update, invitation, and cancellation. Browser-supplied organizer,
participant, invitation, pet, status, or capacity values are never trusted.
Material privacy, capacity, or location changes notify confirmed participants
after commit. Reschedule, approval/rejection, promotion, removal, invitation,
and cancellation reuse `ForumNotification`, recipient locale, and stable
deduplication keys. Notification previews contain the Meetup title and safe
state copy, never an exact private address.

Existing attendee messages, updates, reviews, and unified reports are reused.
No second chat service, websocket dependency, scheduler-only reminder, or
analytics pipeline was added.

## Interface, Accessibility, And Performance

The class-based `ForumEventDirectory` and `ForumEventWorkspace` components use
separate Blade templates and form objects. Discover/My/Invitations state is
URL-backed. Create/edit choices use native fieldsets, legends, radios,
explicit labels, an error summary, textual statuses, loading-disabled actions,
offline feedback, visible focus, wrapping copy, and minimum-height primary
controls. Organizer queues use responsive cards and bounded pagination rather
than a desktop-only wide table.

Directory cards eager-load only the current member's bounded registration
projection and use aggregate counts. Detail counts use database aggregates;
organizer registrations are paginated. Query-growth tests compare one and 40
registrations for detail/manage, while the existing directory budget protects
card queries.

## Focused Evidence And Remaining Release Boundary

The 2026-08-30 focused command covering Meetup preview, event schema/workflow,
lifecycle, query budgets, security/privacy, real two-process capacity race,
and translation parity passed 72 tests with 38,738 assertions. This is focused
evidence only. Repository-wide static analysis, full serial Pest, browser
viewports, dependency audits, build/cache checks, final independent review,
commit, and push remain separate release gates until their commands are
observed and recorded in the Meetup work ledger.

Primary evidence:

- `tests/Feature/Forum/MeetupSecurityBoundaryTest.php`
- `tests/Unit/Forum/MeetupCapacityConcurrencyTest.php`
- `tests/Feature/Forum/EventWorkflowTest.php`
- `tests/Feature/Forum/EventLifecycleFoundationTest.php`
- `tests/Feature/Forum/EventLifecycleQueryBudgetTest.php`
- `tests/Feature/MeetupDirectoryPreviewTest.php`
- `docs/audits/meetups-completion-work-ledger.md`
- `docs/audits/meetups-threat-model.md`
