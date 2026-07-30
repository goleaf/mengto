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
