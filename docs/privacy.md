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
