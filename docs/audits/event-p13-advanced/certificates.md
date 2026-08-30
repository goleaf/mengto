# EVENT-S06 Certificate Discovery

Status: discovery only. No production, migration, test, translation, canonical-plan, or shared-document file was changed by this specialist.

## Requirement traceability

| Requirement | Consequence |
| --- | --- |
| `competition-016` | Versioned, final-result-linked, localized certificates with secure download and audited correction/revocation. |
| `checkin-009` | Attendance correction preserves original/current status, operator, reason, time, and certificate/result effect. |
| `feedback-006` | Certificates are an authenticated post-event follow-up resource. |
| `privacy-001`, `privacy-003`--`privacy-005` | Minimize certificate facts; secure/audit downloads; retention; former staff lose future access. |
| `event-data-001`--`event-data-009` | Reuse event, registration, and result records; add certificate state, constraints, transactions, immutable history, retention, and bounded indexes. |
| `event-auth-001`--`event-auth-004` | Add issue, regenerate, revoke, download, and audit-view abilities with direct-action and tenant tests. |
| `event-validation-001`, `event-validation-002`, `event-validation-004` | Validate source state, locale/template, correction/revocation reason, file metadata and storage. |
| `event-factory-001`, `event-factory-005`, `event-factory-008`, `event-factory-009`; `event-test-019`, `event-test-022`--`event-test-024` | Supply valid source graphs and test attendance correction, private files, follow-up, and EN/LT/RU. |
| P26 / P29 | P26 owns immutable result facts; P29 owns certificate delivery. Certificates must be versioned, result-linked, localized, secure, and correctable/revocable through audit. |

## Current authority and gap

`ForumEvent` is the sole event aggregate. `ForumEventRegistration` already carries event/occurrence, participant, locale/timezone, check-in/out timestamps, and `Attended`. `ForumEventRegistrationService::checkIn()` and `checkOut()` lock and transition registration state while writing event history. The new competition tables provide result versions/rows and result checksums. EVENT-S01 assigns issue/version/file/revocation records to EVENT-S06 and requires an achievement certificate to reference both a result row and its parent result version.

No certificate aggregate, policy, download route, private certificate file, attendance-correction record, or event notification-intent implementation exists. Reuse `PrivateFileResponse`: it permits only the `local` disk, rejects traversal/escapes, and constrains paths to the expected directory. Care/medical download actions show the intended scoped private stream and audit pattern. Never project storage paths or public URLs.

## Canonical-source interface

A certificate is derived evidence, never a source of attendance or achievement. An internal Action-only `EventCertificateSource` resolver returns a minimal typed snapshot: source kind, canonical IDs, recipient user, event/occurrence, allowed display facts, source checksum, and invalidation predicate. Blade/Livewire never resolve it or supply its facts.

| Source kind | Required canonical source | Frozen source facts |
| --- | --- | --- |
| `attendance` | Locked `ForumEventRegistration` for the same event/occurrence with authoritative `Attended` state. Check-in alone is insufficient. A future dedicated attendance record replaces this source only with a migration map. | registration/occurrence/participant IDs, attendance state, check-in/out timestamps, registration-snapshot checksum. |
| `competition_result` | Locked result row and parent result version for the same event; result version is finalized and row is awardable/final for the requested type. Publication controls public visibility, not recipient entitlement. | competition/entry/result-row/result-version IDs, result checksum, rank/status/score summary, permitted entry display snapshot. |

The resolver rejects client-provided recipient, attendance, rank, score, result version, or pet facts. Result issuance also verifies that the entry registration belongs to the resolved recipient and occurrence. Attendance issuance cannot be promoted by posting a registration status.

## Normalized schema and state

| Table | Essential columns and invariants |
| --- | --- |
| `forum_event_certificates` | event FK, nullable occurrence FK, recipient FK, certificate type, stable key, source kind, status, nullable current-version FK, source fingerprint, lock version, issued/revoked/superseded timestamps. Historical FKs restrict deletion. |
| `forum_event_certificate_sources` | One row per certificate: source kind; nullable attendance-registration FK; nullable competition-result-row/version FKs; immutable source snapshot/checksum. A portable CHECK permits exactly one source shape; Action rechecks event/recipient consistency. Use a normalized non-null source identity/active key for uniqueness, never a nullable compound unique index. |
| `forum_event_certificate_versions` | certificate FK, version number, locale, template stable key/version, immutable localized render snapshot/checksum, disk/path/original name/MIME/byte size, artifact SHA-256, issued/superseded/revoked timestamps, generation key and payload fingerprint. Unique certificate/version and generation key; never overwrite bytes/row. |
| `forum_event_certificate_events` | certificate/version FKs, actor nullable only for retained system history, type, reason, prior/new state, minimized/encrypted metadata, idempotency key, timestamp. Append-only audit plus concise safe `ForumEventHistory` summary. |
| `forum_event_certificate_download_grants` (later email-link only) | version/recipient FKs, purpose, token hash, expiry, consumed/revoked timestamps, issuer/audit relation. Unique token hash; plaintext is sent once only. Not needed in the first slice. |

State: `eligible -> generating -> issued`; `issued -> superseded` for a correction/replacement; `issued -> revoked` for withdrawn entitlement. Generation can make bounded operational failure visible only to authorized staff. Superseded/revoked versions deny ordinary download but remain checksum-verifiable to narrow audit roles; neither is deleted.

## Private storage, token, and download

Store on private `local` only under a derived path such as `event-certificates/{certificate stable key}/{version}/certificate.pdf`. The database name is presentation-only. The Action derives the directory from a locked certificate and writes a frozen localized render snapshot so profile changes, locale preference, or later result correction cannot rewrite issued evidence.

The primary route is authenticated and policy-authorized: it scopes event + certificate stable key + version, rechecks recipient/staff authority and current non-revoked state, calls `PrivateFileResponse::download()`, and appends `certificate-downloaded` audit after preparing the readable stream. Foreign event/certificate/version/path combinations return 404; return 403 only when existence may already be disclosed. Use generated safe names, PDF MIME, attachment disposition, and private/no-store cache headers. No public disk, presigned URL, unscoped version ID, or storage path is ever exposed.

First slice: no bearer links. If email delivery later needs one, issue an opaque high-entropy temporary grant only after regular authorization: hash at rest, recipient/version/purpose binding, short expiry, one use, rate limit, and automatic revoke on replacement/revocation. Raw tokens never enter logs, audit metadata, notifications, Livewire state, or persisted URLs.

## Versioning, checksum, idempotency, and transactions

1. Canonicalize and separately SHA-256 hash source snapshot, localized render payload, and artifact bytes. Checksums are integrity evidence, not authorization.
2. `IssueEventCertificate` locks event, source parent, and active normalized source identity in stable order within short `DB::transaction(..., 3)`; it reauthorizes after lock and reserves one request key. Rendering occurs after commit in an idempotent job; a second transaction attaches only a matching newly stored artifact version.
3. Same request key and payload fingerprint returns the same issue/generation; same key with different input fails. Unique source/request/version keys, not cache locks or preflight queries, prevent duplicates.
4. Regeneration never overwrites: lock certificate/current version, append `n + 1`, mark old version superseded, atomically update current pointer, and record actor/reason. Locale is version-specific; smallest durable rule is that a locale change is a successor version.
5. Revocation locks certificate/current version/source, requires a controlled reason and relevant evidence reference, revokes outstanding grants, writes append-only audit, and never deletes artifact/source/result/attendance evidence.
6. Attendance correction or appeal/result successor triggers one idempotent post-commit reconciliation keyed by that correction/successor. It re-evaluates each active certificate and revokes, supersedes/reissues, or records no effect. The correcting transaction records intended certificate effect before commit; delivery jobs cannot decide entitlement from stale data.

SQLite locking is insufficient alone. Unique keys, short writes, retry handling, and concurrent issue/revoke/regenerate tests are required.

## Policy matrix

| Ability | Allowed principal | Boundary |
| --- | --- | --- |
| List/download current own certificate | Active recipient | Recipient scope, event visibility for this limited resource, current non-revoked version only. |
| Issue from attendance | Narrow credential authority: owner/administrator/primary organizer or future credential role | Same event/organization capability, authoritative attended source, post-event rule; no former/suspended staff. |
| Issue from result | Same authority or explicit scorekeeper after result finalization | Immutable final result source; judge alone cannot issue. |
| Regenerate/correct | Narrow authority; recipient may request only | Material reason, source revalidation, successor only. |
| Revoke | Owner/administrator or explicit independent credential authority; admin override audited | Controlled reason/evidence; check-in operator, judge, vendor, volunteer, and recipient denied. |
| Audit/revoked-version view | Scoped owner/auditor/admin; recipient sees own safe status/reason | Never disclose raw path, token, private result evidence, or unrelated history. |
| Temporary grant | Bound recipient only | Hash, recipient/version/purpose, expiry, revocation and single-use checks all pass. |

Every mutation independently authorizes. Existing broad registration/competition management is not sufficient. Cross-organization/event, former staff, restricted/blocked, inactive, and ID-substitution requests deny.

## Attendance and competition integration

Attendance issuance waits for `Attended`, currently after check-out, not `Confirmed`, `CheckedIn`, or `PartiallyCheckedIn`. Implement `checkin-009` as an append-only correction with before/after state, actor, reason, time, and exact certificate/result disposition; do not later infer history from a mutable timestamp.

Achievement issuance links both immutable result row and version. An appeal changing a result creates a successor result version, then reconciles active certificates; it does not edit a result row. A private recipient certificate from a finalized result must not reveal an unpublished result publicly.

## Dedupe, factories, seed data, and adversarial tests

Create after-commit notification intents with recipient in the key (global notification dedupe is insufficient): `event-certificate-issued:{certificate}:{version}:{recipient}`, `...-replaced:{certificate}:{old}:{new}:{recipient}`, `...-revoked:{certificate}:{version}:{event}:{recipient}`, and `...-source-corrected:{certificate}:{source-event}:{recipient}`. Retries reuse an intent; actual successor/revocation sends one new safe notice with no token, score, evidence, health, or private attendance detail.

Factories need valid `forAttendedRegistration()`, `forFinalCompetitionResult()`, `issued(locale)`, `superseded()`, and `revoked(reason)` states. They create coherent event/occurrence/recipient/source checksum graphs and never bypass finalization. Demo seeds are environment-gated/idempotent, synthetic, and may use a fixture PDF but never raw tokens, real private files, fake attendance, health/payment data, or unfinalized outcomes.

Required adversarial tests:

1. Anonymous, unrelated, cross-event/organization, inactive/blocked, former staff, judge, and ordinary check-in operator cannot list, download, or mutate.
2. Confirmed/checked-in/partial/no-show cannot issue; attended can; retries create exactly one logical certificate/version/audit/intent.
3. Concurrent issue/regenerate/revoke cannot create duplicate active source/version, orphan attachment, or resurrect revoked evidence.
4. Draft/finalizing/wrong-recipient result, appeal successor, welfare withdrawal, and disqualification obey source rules without editing history.
5. Attendance correction preserves facts and executes one correct certificate disposition.
6. Traversal/symlink/disk/path/MIME/name spoofing, public URL, expired/replayed/revoked/wrong-user token, and cached private response fail; successful streams audit.
7. EN/LT/RU renders retain immutable template/render/file checksums; changing current locale cannot mutate issued evidence.
8. Archive/retention deny new issue and normal revoked/superseded download while preserving authorized audit evidence; test populated expand/contract recovery.

## Rollback and smallest durable recommendation

Material risks: partial file/database attachment, concurrent duplication, unpublished-result leakage, stale attendance after correction, broad manager authority, public/cached URLs, and destructive rollback of retained evidence. Use expand-and-contract migrations, staging artifact keys followed by transactional attachment, cleanup for unattached objects, restrictive FKs, a forward-only disable switch for new issue/grants, and preserved audit/retention access.

Smallest durable delivery: attendance-only certificates first--one logical certificate/source, immutable localized PDF versions, direct authenticated private download, append-only certificate/event audit, source/request/version unique keys, and correction-driven revoke/supersede. Reuse `PrivateFileResponse`, `ForumEventHistory`, `ForumEventRegistrationService`, and the notification centre. Add competition-result sources only after P26 has a tested finalization/appeal-successor Action; do not issue from schema-only result tables or add bearer links in that slice.

