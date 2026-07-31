# Operations

## Health

The public health response reveals only minimal status. Detailed dependency
health requires operator authorization.

Monitor:

- application boot and error rate;
- database connectivity/latency;
- cache and session availability;
- queue depth/failures when enabled;
- private storage read/write capacity;
- integration timeout/error/rate-limit rates;
- device offline/stale rates;
- log and temporary-file growth;
- security/audit anomalies.

## Ownership And Retention

The machine-readable defaults live in `config/platform.php`. Environment
specific log routing may change the destination, but it may not remove the
owner or silently extend retention.

| Signal | Owner | Retention | Collection and response |
| --- | --- | --- | --- |
| Application and request logs | Platform operations | 14 days | Daily rotation by default; alert on error-rate or disk-growth thresholds |
| Security and domain audit records | Security and privacy | 365 days | Database audit records; access is limited to authorized support/owners |
| Integration and job terminal failures | Platform operations | 30 days after an integration/job is enabled | Persist terminal status and provider request ID without credentials or payload dumps |
| Temporary uploads and generated files | Platform operations | 1 day unless a domain policy promotes the file | Monitor growth; remove abandoned files through the approved maintenance path |

Every application web response carries a server-generated `X-Request-ID`.
Laravel log context includes only request ID, method, named route, and the
authenticated user's internal ID/actor key. Incoming request IDs are not
trusted. Domain object IDs are added only by the operation that owns them.
Passwords, session IDs, tokens, authorization headers, request bodies, precise
location, and private media never belong in generic request context.

## Incident Priorities

- Critical: private data exposure, unauthorized camera/GPS/door access,
  payment mutation, medication/device duplicate command, active exploit.
- High: authentication outage, medical/care unavailability, lost-pet
  coordination failure, persistent seed/migration corruption.
- Normal: isolated integration or interface degradation with safe fallback.

## Response

1. Confirm and scope without exposing more data.
2. Preserve safe evidence and correlation IDs.
3. Revoke/disable the narrow affected capability.
4. Apply fallback for critical care/safety.
5. Fix with regression test.
6. Verify and document recovery.
7. Complete notification/post-incident obligations.

## Routine Checks

- dependency advisories;
- failed jobs and exhausted retries;
- storage/log retention;
- expired/revoked grant access;
- inactive sessions/device tokens;
- backup restoration exercise;
- migration and seed smoke on isolated data;
- critical browser/accessibility smoke after release.

## Moderation Operations

The forum administration moderation tab is the supported human-operated path:

1. triage an unassigned report and open an idempotent case;
2. assign an active administrator;
3. inspect only the bounded linked reports and append-only event history;
4. record an action with rule, policy basis, private evidence reason, target,
   scope, dates, and any required independent senior approval;
5. recuse immediately when a controlled conflict reason applies;
6. route eligible appeals to an administrator other than the original action
   actor;
7. preserve action, reversal, case, report, and appeal history.

Priority never proves a violation. Permanent suspension and legal/safety
referral definitions require a different active administrator as senior
approver. Recusal private notes and appeal/report evidence remain private.
Interactive operations reject cases with more than 100 linked reports; such a
case requires a separately authorized bounded operations procedure rather than
an extended browser request.

## Adoption Provider Verification

Provider identity is refreshed whenever an adoption listing is synchronized
and whenever a linked credential review or successful appeal changes status.
`AdoptionCaseSeeder` is the production-safe, idempotent backfill path for
existing adoption listings. It preserves listing and case identifiers and does
not infer verification from the legacy seller-trust flag.

Natural credential expiration is evaluated on every read, so the public state
does not depend on a scheduler. Operators should use the credential review
queue to renew, suspend, reject, or revoke evidence; they must not edit the
adoption projection directly. Recovery consists of correcting the credential
through an authorized audited transition and rerunning the adoption case
synchronizer or seeder.

## Lost And Found Closure And Archive

The case owner or coordinator closes or confirms reunion through the
coordination workspace. The transition uses the displayed lock version,
appends an immutable event, stops queued/sent alerts, cancels unfinished
search tasks, and releases active volunteers.

Archival is available only after a terminal status. It removes the case,
poster, and directory entry from public access while preserving the stable
case ID, slug, public code, sightings, updates, reports, relays, attachments,
and event history. Operators must not delete archived cases to perform routine
cleanup.

Recovery from an incorrect archive is a controlled forward operation: verify
the owner and audit history, restore `archived_at` through a separately
authorized maintenance change, append a restoration event, and reactivate
alerts only after reviewing current location/privacy data. Database rollback
alone must not be used to erase events or expose a stale closed search.

## Collaborative Guide Operations

The guide tab in forum administration is the bounded discovery surface for
every workflow state. Editors use the guide workspace for content, workflow,
collaborators, correction review, locks, and rollback.

Recovery rules:

1. restore incorrect content through version rollback, which appends another
   snapshot and event;
2. correct state through a valid forward transition with an explicit reason;
3. resolve stale edits by reloading and reconciling the displayed lock version;
4. never update or delete `knowledge_versions` or
   `knowledge_workflow_events`;
5. retain the additive guide schema during application rollback after
   collaborative data has been accepted.

The public article, print, and export routes expose only published/outdated
guides unless the current user has update authority. Correction source URLs are
stored and displayed but never fetched by the server.

## Community Review Operations

The topic-level community-note panel is the normal proposal, author-response,
review, moderator-decision, and appeal surface. Deadlines fail closed during
submission, so no scheduler is required. A changed pending note cancels the
old panel and assignments; restart review only after validating the revised
evidence.

Never modify or delete panel events or note versions. Recover with an appeal,
audited moderator outcome, versioned revision, or replacement reviewer. Keep
the additive tables during application rollback after production use. See
`docs/community-review.md`.

## Mentorship Operations

`/forum/mentorship` is the user-facing opt-in, matching, request, private
thread, completion, feedback, block, and report surface. No queue, scheduler,
cron, or external messaging infrastructure is required.

Recovery is forward-only after user data exists:

1. pause the mentor profile or affected scope;
2. block contact and submit a unified report when safety requires it;
3. reload stale optimistic-lock state before a new transition;
4. process report/case/appeal through normal moderation;
5. reverse an incorrect reputation event through the reputation ledger;
6. preserve messages, feedback, and lifecycle events.

Do not delete a mentorship to resolve a dispute. Database restrictions prevent
deletion once append-only evidence exists. The demo graph is local/demo/testing
only and is synchronized by `MentorshipDemoSeeder`. See
`docs/mentorship.md`.
