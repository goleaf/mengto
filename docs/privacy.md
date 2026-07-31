# Privacy

## Canonical Boundary

Privacy decisions are server-side authorization decisions applied before
queries, counts, snippets, files, exports, and Livewire presentation data are
returned. Hidden controls, client-owned IDs, route secrecy, cache keys, or
`#[Locked]` properties are not authorization.

## Data Classes

- Public: explicitly published forum, profile, guide, and directory fields.
- Member-scoped: content intended only for active authenticated users.
- Relationship-scoped: owner, participant, selected collaborator, current
  group member, or current temporary grant data.
- Sensitive: medical/care records, exact lost-animal locations, contact
  details, applications, credential evidence, report evidence, and private
  media.
- Secret: passwords, session/token material, private keys, provider
  credentials, and complete authorization headers.

Sensitive and secret values are never included in public search, counts,
autocomplete, recommendation explanations, cache entries without complete
scope, browser state, or logs.

## Forum Journals

Forum journal visibility is canonical on the parent topic. Public directory
queries return only public non-group journals. Member, expert, link-only,
group, and private journals require current policy authorization. Selected
collaborators are explicit, revocable, role-bounded, and audited. Exports and
private media repeat authorization at request time.

Forum journals never copy private care entries, medications, temporary grants,
care media, private pet documents, or exact home locations. Later revocation
or privacy changes affect future access without erasing historical
attribution.

## Retention And Recovery

Archival preserves user content and audit history. Restrictions, reports,
legal holds, ownership records, and transaction evidence are not removed by a
block or visibility change. Any retention deletion must follow the owning
domain's documented policy and preserve legally or operationally required
evidence.

Implemented controls and incident procedures are detailed in
`docs/security.md`, `docs/authorization.md`, and `docs/operations.md`.
## Event Privacy

Public discovery selects no exact location, online URL, emergency plan,
private invitation text, attendee requirements note, or private review safety
feedback. These fields are encrypted and excluded from model serialization.
Private-event search visibility depends on a current accepted invitation.
Reports use the unified moderation privacy boundary and never disclose the
reporter to the reported organizer by default.

Photo consent is explicit per registration. Event cancellation, archive, and
backfill preserve registrations, invitations, reports, reviews, messages, and
history instead of deleting evidence.

## Expert Session Privacy

Public session projections include the preserved host name, professional
scope, jurisdiction, topic, schedule, approved questions, published answers,
source labels/URLs, correction history, and disclaimer. Credential evidence,
credential identifiers, reviewer notes, file paths, idempotency keys, pending
questions belonging to other users, reporter identity, and moderation evidence
remain private.

Pending queue authorization is applied before matching or presentation.
Archival retains audit-bearing records; it does not publish previously private
queue content.

## Topic Lifecycle Privacy

Public topic output contains only public-safe state, age warning, and bounded
event history. Update-request prose is visible only to its requester, the
topic owner, or an administrator. Legal-hold evidence and release reasons are
administrator-only, encrypted, and excluded from model serialization.

Removal, archive, merge, redirect, retention review, and restoration preserve
content and moderation evidence without making private topics searchable or
granting an administrator an ordinary private-content read bypass. Redirects
occur only after destination authorization. See `docs/topic-lifecycle.md`.

## Pet Profile Privacy

New pet profiles default to private, non-discoverable, direct-link disabled,
and external-indexing disabled. The broad profile audience caps every section
audience. Discovery, link access, owner/manager labels, and location precision
are independent explicit settings.

Public pet projection is allowlisted. It excludes encrypted facts, evidence,
exact location, contacts, medical/care/device data, documents, private manager
metadata, and idempotency material. Manager expiry and revocation are checked
at read and mutation time. A hidden, archived, merged, disputed, or deletion
pending state cannot become public through a stale UI control.

Privacy and lifecycle Actions update optimistic versions, append actor evidence,
and invalidate the known public profile, canonical, directory, search, and
recommendation keys. A setting that records external-indexing preference is not
evidence that third-party search engines have already removed an old copy.
See `docs/pet-profiles.md` for the exact implemented boundary.

## Social Relationship Privacy

Social directory queries are bounded and explicitly select public actor
presentation fields. They exclude non-discoverable actors, blocked pairs,
exact location, medical/care/device data, credentials, documents, ownership
evidence, manager structure, and hidden group membership.

The relationship center exposes requests, edges, and counts only to a user who
can represent the selected actor. List-visibility preferences are stored, but
no viewer-facing public friend/follower list is claimed in the foundation.
Blocking is applied before request creation/acceptance and before directory or
graph projection, then both endpoint cache namespaces are invalidated.
