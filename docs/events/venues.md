# Event Venues

`Place` is the reusable persisted identity for public spaces, organization
locations, private homes, foster locations, and temporary meeting points. One
place may have one operational `Venue`, and a venue may define typed
`VenueArea` records with independent human, animal, species, and accessibility
facts. Event rooms may reference one venue area without duplicating place
identity.

Events and occurrences store nullable `place_id` and `venue_id` links plus a
public location scope. They do not copy an exact address from a canonical
place. Legacy event-only exact location remains encrypted for backward
compatibility, but the current event builder no longer carries that field in
Livewire state.

Private exact address, exact coordinates, and arrival instructions are
encrypted. A current manager or account-bound expiring grant is required to
reveal them. Each reveal is audited. Location changes preserve an encrypted
version and revoke current and future grants so stale tickets cannot expose the
old destination.

Public place projections expose only region and explicitly public address or
coordinates. Private places are absent from public catalogue authority queries.
Route geometry, expiring offline venue packages, and venue confirmation review
remain open downstream packages.
