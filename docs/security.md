# Security

## Security Model

PawCircle contains public social data and highly sensitive medical, care,
location, camera, household, and device data. Sensitive data is closed by
default and requires authenticated ownership or an explicit scoped grant.

## Implemented And Required Controls

### Identity And Session

- Laravel session authentication.
- Session regeneration after login.
- Session invalidation and CSRF regeneration after logout.
- Rate limits for login, registration, reset, verification, temporary links,
  uploads, integration callbacks, and mutation-heavy endpoints.
- Production prohibition for demo identities and fixed actor fallbacks.

### Authorization

- Policies for model/resource actions.
- Authenticated actor key derived server-side.
- Query scoping before private model retrieval.
- Direct Livewire action authorization.
- Fresh password confirmation for precise device pages and remote commands.
- Separate view, share, export, command, precise location, camera, and
  administrative capabilities.

### Professional And Adoption Identity

- Professional verification and forum reputation are independent.
- Credential owners cannot approve their own evidence.
- Credential review and appeal decisions are authorized, idempotent, audited,
  expiring, suspendable, rejectable, and revocable.
- Private credential files, identifiers, evidence, and reviewer notes remain
  hidden from public serialization and adoption pages.
- Adoption providers are matched by server-owned account identity and a
  purpose-compatible credential; browser IDs and the legacy seller-trust flag
  are not proof.
- Natural expiry removes the public verified state without requiring a
  scheduler or deleting history.

### Lost And Found Privacy

- Lost and stolen cases can reference only the authenticated owner's active
  pet profile. Taxon and domestic classification IDs are resolved and scoped
  server-side.
- Exact coordinates, location notes, hidden identifying marks, direct contact
  values, case-time animal snapshots, and relay messages are encrypted and
  excluded from public serialization. Public maps receive rounded coordinates
  and textual area context.
- Contact uses an authorized, idempotent relay; the recipient is derived from
  the case owner and neither party's direct contact value is exposed.
- Reward text rejects transfer instructions, payment codes, links, and direct
  contact details. False-sighting and reward-scam reports enter the unified
  private moderation pipeline.
- Archival is owner/coordinator-only, requires a closed case and explicit
  confirmation, uses optimistic locking, preserves immutable history, and
  fails closed for public direct URLs and posters.

### Tokens

- Cryptographically random raw token returned once.
- SHA-256 or stronger digest persisted.
- Purpose, owner, scope, expiry, revocation, use, and audit metadata.
- Atomic single use when the grant semantics require it.
- Rate-limited lookup with no raw token logging.

### HTTP And Browser

- CSRF and Laravel 13 origin-aware request forgery protection enabled.
- Security headers for clickjacking, referrer, MIME sniffing, and permissions.
- Escaped output by default.
- No raw user HTML without sanitization.
- Safe redirect and URL validation.

### Files

- Private disks for medical/care/device evidence.
- Generated storage names.
- MIME/content/size/dimension validation.
- Authorization on every download.
- Compensation cleanup on partial failure.

### Integrations

- Configuration-only credentials.
- Explicit connect/total timeouts and response-size limits.
- Safe bounded retries only for idempotent operations.
- Webhook signature and event replay protection.
- Redacted structured logs.
- No real external calls in automated tests.

### Logging

Allowed context includes request ID, user ID, actor key, target type/ID,
provider, external request ID, and attempt number.

Never log passwords, sessions, raw reset/access tokens, authorization headers,
private keys, payment credentials, or complete private records.

## Threat Review

| Threat | Control |
| --- | --- |
| IDOR / cross-owner access | Auth middleware, actor bridge, policies, scoped queries, negative tests |
| Session fixation | Regenerate on login; invalidate on logout |
| CSRF / hostile origin | Laravel CSRF and origin protection |
| XSS | Escaped Blade, no raw HTML, sanitizer boundary |
| SQL injection | Eloquent/query builder, no raw user SQL |
| Mass assignment | Explicit fillable/field maps |
| Token replay | Digest, expiry, revocation, atomic consumption |
| Duplicate payment/device/care command | Idempotency key, unique constraint, lock |
| Path traversal/private file leak | Configured disk, generated paths, authorization |
| SSRF | Scheme/host/IP/redirect/size/time validation |
| Webhook forgery/replay | Signature and provider event uniqueness |
| Cache privacy leak | Actor/role/locale scope and invalidation |
| Covert camera/location access | Explicit time capability, step-up, view audit |
| Lost-case exact location/contact leak | Rounded public coordinates, encrypted private fields, relay-only contact, fail-closed archive policy |
| Private/unlisted group enumeration | One discoverability scope before rows/counts; direct view policy; invitation/member checks |
| Draft guide or private editorial leak | Public-state scope, policy-protected editor/export, bounded public translation lookup, escaped body |
| Fraudulent guide authority | Independent scoped community review, current credential-backed expert review, no popularity conversion |
| Concurrent guide overwrite/history rewrite | Optimistic lock version, append-only snapshots/events, rollback as a new version |
| Mentorship contact or private-thread disclosure | Participant policy, block checks on each contact mutation, dedicated private store, restrictive audit foreign keys |
| Mentorship authority/reputation farming | Independent credential boundary, capacity and pair limits, idempotency, two-party interaction evidence, uninvolved administrator validation |
| Group poll manipulation | Parent-scoped policy, one user/poll row, locked transaction, typed options, idempotency, visibility rules |
| Private group file disclosure | Private disk, generated path, content validation, request-time parent/child authorization |
| Log disclosure | Redaction and structured allow-list |

## Persistent Group Security

- Private and unlisted groups are excluded before pagination, totals, and
  suggestions for unauthorized users.
- Membership, role, ownership, invitation, and lifecycle writes use explicit
  policies, validated DTOs, transactions, row locks, optimistic versions, and
  database uniqueness.
- Group location stores a generalized label only; exact household location is
  not part of the group schema.
- Invitations expire, are one-use, pair-limited, rate-limited, and auditable.
- Owner removal and silent audit deletion fail closed.
- Group reports enter the unified moderation boundary; reporter identity and
  private evidence are not exposed to the group owner or reported user.
- The static compatibility catalogue cannot grant persistent access.

See `docs/groups.md`.

Precise device pages and every remote device command use Laravel's
`password.confirm` middleware. `App\Livewire\Auth\ConfirmPassword` validates the
current password through a rate-limited server action, records the framework's
fresh-confirmation timestamp, and returns only to the intended same-origin
route. Direct command submission without a fresh confirmation has no side
effect.

## Incident Response

1. Preserve safe audit and request identifiers.
2. Revoke sessions, temporary links, device commands, and provider tokens as
   applicable.
3. Disable only the affected integration or remote capability.
4. Keep local safety functions available where possible.
5. Patch and add a regression test.
6. Notify affected users according to legal and product policy.
7. Record the finding and resolution in the changelog and compliance matrix.

## Community Review Controls

Community review is restricted to a closed low-risk type allowlist. Reviewer
selection excludes interested parties, exposes no private report evidence,
requires reasoned one-reviewer/one-panel submissions, and records immutable
events. Community notes require trusted proposers, bounded evidence, rate
limits, independent assessment, escaped presentation, versioned moderator
changes, and explicit deletion denial for content authors.

## Mentorship Controls

Mentorship uses broad optional location only, platform-only communication,
escaped append-only messages, pair/block/capacity controls, explicit safety
acknowledgements, and participant-only reports. The report form exposes the
unified reason catalogue, requires an explicit truthfulness confirmation, and
never infers conviction from urgency.

## Group Content And Poll Controls

- Every group child is resolved under an authorized parent group; a child ID
  cannot broaden membership or management access.
- Group topics and guides remain excluded from public directories unless the
  corresponding group content is publicly visible to the current viewer.
- Group files use the private local disk, content-derived MIME validation,
  generated storage names, checksums, bounded size, request-time policy
  authorization, and compensation cleanup after a failed write.
- Poll action parameters and option IDs are untrusted. The server reloads the
  poll and its options, checks group membership, lifecycle, eligibility,
  closure, cardinality, duplicate ranks, and editability, then writes inside a
  locked transaction.
- One database row per poll/user prevents duplicate voting. Anonymous means
  voter identity is hidden from the interface; it does not remove the private
  anti-abuse identity needed for uniqueness and authorization.
- Result and voter projections apply the configured visibility rule. A
  location-limited poll uses only the group's generalized location key and
  does not expose private addresses.
- Poll output carries a localized authority disclaimer and has no integration
  with credential verification, diagnosis, legal conclusions, scientific
  confirmation, or automatic moderation.

See `docs/polls.md`.

Completion reputation is withheld when interaction evidence is missing, the
participants block one another, or a related report is open. Trust and karma
never project professional status. See `docs/mentorship.md`.

## Forum Journal Controls

- Topic visibility is the sole privacy source; public discovery excludes
  member, expert, link-only, group, and private journals before rows/counts.
- Public Livewire state contains locked scalar IDs and bounded form values,
  never models, relationship graphs, paths, credentials, or care records.
- Entry, collaborator, comment, media, export, and archive actions reload and
  authorize their parent. Nested media routes additionally reject a media row
  outside the route journal.
- Mutations use validation, unique idempotency keys, short transactions, and
  optimistic versions. Archived journals reject writes.
- Journal prose stays escaped plain text.
- Images require actual image content, bounded dimensions/size, generated
  private paths, encrypted original names, alt text, and request-time policy
  checks.
- No journal event creates professional verification, trust authority,
  reputation, streak loss, punitive ranking, or public negative score.

See `docs/journals.md`, `docs/privacy.md`, and `docs/files.md`.
