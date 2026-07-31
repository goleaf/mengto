# Files And Media

## Storage Rules

- Use configured Laravel disks; do not hardcode absolute paths.
- Public and private ownership are explicit per domain.
- Generate storage names; never trust an original filename or extension.
- Validate actual content, MIME type, size, and image dimensions where
  applicable.
- Keep private files outside unrestricted public access.
- Authorize every private response at request time.
- Use a compensation cleanup when a database write fails after a file write.
- Do not serialize disk paths, encrypted names, or authorization metadata.

## Existing Boundaries

Private medical, care, moderation, credential, group, mentorship, adoption,
lost/found, and journal evidence remains on private storage. Public profile or
topic imagery is served only where the owning domain explicitly permits it.
Every controller or Action must verify both the file and its parent resource
to prevent cross-parent route-binding access.

## Forum Journal Images

`StoreForumJournalMedia` validates real image content, dimensions, byte size,
alt text, caption, parent entry ownership, journal mutability, and
idempotency. It stores generated names on the private local disk and records a
checksum. `PrepareForumJournalMediaResponse` and
`ForumJournalMediaController` reload the parent and apply
`ForumJournalMediaPolicy` before streaming a response.

Archive preserves media. A missing file is reported as a safe not-found
condition; the application does not substitute another path or reveal
filesystem details. See `docs/journals.md`.
