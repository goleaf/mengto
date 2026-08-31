# Meetups Threat Model

Status: implementation input on 2026-08-30. This document describes the
current `ForumEvent`-backed `/meetups` attack surface and the controls required
before the delivery can be called complete. It is not a security certification.

## Scope And Assumptions

The protected surface is the authenticated `/meetups` directory, detail,
creation, editing, participant-management, invitation, communication, and
exact-location workflow. `ForumEvent`, `ForumEventRegistration`, canonical
`PetProfile` manager authority, `SocialAccountBlock`, `PlaceAccessGrant`,
Laravel notifications, and unified forum reports remain authoritative.

The attacker is an authenticated member who can tamper with every Livewire
property and method argument, replay or race requests, enumerate stable keys,
and retain stale pages. An inactive or suspended account may attempt direct
calls. Database, operating-system, and framework compromise are out of scope.
No bearer invitation link, public anonymous Meetup page, payment provider, or
continuous location tracking is introduced by this delivery.

## Assets

- Private and invitation-only Meetup existence, title, summary, schedule, and
  safe location summary.
- Exact address, coordinates, entrance instructions, online URL, emergency
  plan, private room details, and access-grant audit trail.
- Participant identity, contact details, RSVP state, attendee communication,
  and private-user visibility.
- Pet identity, media, lifecycle, species, and current owner/manager authority.
- Organizer, team, organization, and group capabilities.
- Capacity allocations, waitlist order, invitation ownership, immutable
  participation history, operations, transitions, and notification intent.
- Moderation report confidentiality and canonical account-block semantics.

## Trust Boundaries

1. Browser to route and Livewire: all identifiers, enums, pet selections,
   audiences, idempotency keys, expected versions, and apparent state are
   untrusted.
2. Controller/Livewire to Policy/Action: hiding a control never authorizes a
   mutation; every Action must resolve and authorize fresh server rows.
3. Action to database transaction: uniqueness, lock order, checksums,
   transitions, allocations, and waitlist order must remain correct under
   replay and concurrency.
4. Meetup to cross-domain authority: `SocialAccountBlock`, current
   `PetProfileManager` plus `PetProfileAccess`, Organization/Group membership,
   and `PlaceAccessGrant` cannot be copied or approximated locally.
5. Database to HTML/Livewire/notifications: protected fields must be omitted
   from the query/projection before serialization and never hidden only with
   CSS, Alpine, metadata, or a disabled control.
6. Commit to side effect: participant notifications and audit evidence may be
   emitted only for a committed, deduplicated transition.

## Threats And Required Controls

| ID | Abuse path | Impact | Required control and evidence |
| --- | --- | --- | --- |
| MT-01 | Organization membership admits an organization-linked private or invitation-only event through an unconstrained visibility branch. | Meetup existence, title, schedule, organizer, counts, and location summary leak in directory HTML/Livewire. | Every relationship branch is constrained to its matching visibility; projection-absence and direct-policy tests cover all directory periods. |
| MT-02 | A blocked organizer or participant uses a public event, invitation, RSVP, participant list, message, or exact location as a contact bypass. | Harassment, identity disclosure, and real-world co-location. | Query, Policy, invitation, admission, messaging, and reveal boundaries reuse `SocialAccountBlock`; history and seat state remain auditable, while current projection, communication, mutation, and exact-location access fail immediately. |
| MT-03 | A browser submits a foreign, view-only, inactive, denied, pending, invited, revoked, future, or expired pet-manager relationship. | Private pet disclosure and false participation. | Inside admission, review, promotion, and check-in transactions require an active user, active PetProfile, and current authority granting `ManageCare` or `ManageSocial`. View-only and inactive manager states fail. |
| MT-04 | Pet authority changes between render, initial validation, organizer approval, or waitlist promotion. | Stale authority survives into confirmed participation. | Re-resolve pet and current manager authority after locking; approval never manufactures eligibility; stale authority denies without pet projection or allocation. |
| MT-05 | Nullable `active_scope_key` and unused operation ledger allow duplicate active rows or changed-payload replay. | Duplicate RSVP, overwritten history, conflicting guest/pet selection. | Deterministic active scope plus database unique constraint; operation identity binds event, occurrence, principal, type, key, and canonical request checksum; terminal rejoin appends a new generation. |
| MT-06 | Concurrent join, approval, leave, or promotion races on stale status counts. | Overbooking, two promotions for one place, or leaked capacity. | The Meetup service locks the canonical event/occurrence and counts all seat-consuming statuses plus guests before mutation. Deterministic fit-aware promotion runs under the same boundary; a real two-process SQLite test proves the final slot cannot be double-confirmed. Generalized P17 allocation tables are not claimed as the Meetup allocator. |
| MT-07 | A forged invitation ID, another user's accepted invitation, expired invite, or cancelled Meetup is reused. | Invite-only access or participation bypass. | Invitations remain account-bound and event-scoped; a recipient-only preview reveals safe fields; acceptance rechecks identity, expiry, block, current event, join policy, and capacity and is replay-safe. |
| MT-08 | Broad registration-management capability or forged child ID exposes email, consent, private pet identity, or changes another event's row. | IDOR and excess participant disclosure. | Every child query is scoped by event; queue review, check-in, emergency contact, pet review, and removal capabilities have least-privilege field projections and independent Action authorization. |
| MT-09 | Legacy exact fields, canonical Place details, private room metadata, or an organizer-entered exact address reach a public query or component snapshot. | Home-address/coordinate disclosure. | Directory selects approximate fields only; manual UX separates public area from protected exact input; detail queries sensitive legacy fields only after authorization; Place reveal is explicit, grant/event/purpose-bound, audited, and time/state limited. |
| MT-10 | An old confirmed/attended row is selected before a current waitlisted/removed row. | Removed user retains exact location, messages, or review capability. | Active/current occurrence selection is explicit and ordered; exact access requires the matching current confirmed participation and is revoked on leave/removal/cancellation/end. |
| MT-11 | A participant forges the attendee broadcast audience or a blocked peer is serialized in messages. | Spam and block bypass. | The Action owns the actor-to-audience matrix; attendee projections suppress blocked peer identity without deleting history. |
| MT-12 | Team, organization, account, event, or registration state changes after a pre-transaction policy check. | Stale privilege mutates schedule, invites, updates, participants, or cancellation. | Re-load and reauthorize locked current rows, compare optimistic versions, and reject stale Livewire state with localized factual feedback. |
| MT-13 | Cancellation, removal, reschedule, or update writes state but notification/audit delivery races or leaks exact location. | False or duplicate notices, missing safety update, private data disclosure. | Canonical state commits before the existing notifier runs; stable deduplication keys suppress replay, recipient locale is applied, and notification copy contains only safe event context. The wider generalized notification-intent tables are not claimed by this Meetup path. |
| MT-14 | Hostile title, summary, venue, rule, update, or message content reaches raw HTML or metadata. | Stored XSS. | Plain bounded text is escaped by Blade; no raw rich HTML; hostile-marker HTML and Livewire tests remain permanent. |
| MT-15 | State-changing GET, missing CSRF, unsafe return URL, or unrestricted public indexing exposes private state. | Request forgery or private-page indexing. | Routes remain cache-safe GET reads only; Livewire mutations retain CSRF; redirects use canonical routes; protected pages send private/no-store/no-referrer/noindex headers. |
| MT-16 | Active members spam searches, creation, invitation, reporting, or messaging. | Resource and notification abuse. | Existing rate limits are reused and measured per operation; directory remains paginated and exact address is never searchable. |

## Security Acceptance Matrix

- Forged Meetup, participant, pet, invitation, update, occurrence, Place, and
  room identifiers fail inside the server boundary without a cross-record
  projection.
- Public/member/group/organization/invitation/private visibility and
  open/approval/invitation registration policy are independently tested.
- Requested, waitlisted, rejected, withdrawn, removed, blocked, and suspended
  users receive no participant-only message, identity, or exact-location data.
- Private user and private pet identity never becomes globally discoverable by
  attending; public cards use counts only.
- Same-command replay returns one logical result; changed payload conflicts;
  double join and stale approval cannot create a second active participation.
- Real separate-process SQLite tests prove final-slot and promotion integrity;
  supported row-locking adapters follow the same lock order.
- Hostile user content is escaped; all mutations are CSRF-protected and no GET
  route changes state.
- Notifications contain no exact private location and resolve all translated
  values in the recipient's locale.

## Residual Risks And Non-Goals

PawCircle cannot guarantee attendee conduct or physical safety. Rules,
reporting, canonical blocking, organizer removal, cancellation, safety
suspension, and auditable communication reduce risk but are not emergency
services. The core Meetup delivery does not collect medical proof, microchip
data, background GPS, or continuous movement; does not create a new incident,
chat, map, follower, pet-owner, or notification system; and does not claim the
broader Event P19 payment/provider capability.
