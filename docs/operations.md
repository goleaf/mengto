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

## Persistent Group Operations

`/forum/groups` and `/forum/groups/{group}` are the supported persistent
directory, membership, invitation, management, lifecycle, audit, and report
surfaces. They require no queue, scheduler, cron, or external service.

Recovery after production membership exists is forward-only:

1. close the group to stop ordinary membership changes;
2. preserve membership, invitation, and event rows;
3. resolve role/ownership state through the authorized Actions;
4. correct counters through a bounded aggregate reconciliation;
5. process abuse through unified reports and moderation;
6. deploy a reviewed forward migration for schema defects.

Do not delete group events, use the compatibility catalogue as authority, or
roll back the group tables after user activity without an export and reviewed
forward plan. Production runs only `ForumGroupDefinitionSeeder`; demo graph
creation remains environment-gated. See `docs/groups.md`.

Group content adds nullable topic/guide relations and additive activity,
announcement, private-file, poll, option, and vote tables. After user activity
exists, rollback is forward-only: archive a bad file/announcement/poll,
re-associate the existing topic or guide through the authorized Action, and
reconcile poll counters from the unique vote projections. Do not delete vote
rows to change public totals, expose private storage paths, or force a stored
poll close through a scheduler; the effective close is derived from
`closes_at`.

Production deployment order is migration, normal application release, and
cache/view rebuild. `ForumGroupDemoSeeder` remains limited to local, demo, and
testing environments. It uses production Actions and stable idempotency keys
to create one topic, guide, event, announcement, private file, and all three
poll modes without duplicating records on rerun. See `docs/polls.md`.

## Forum Journal Operations

Deploy the additive journal migration before the application release, then run
the normal production-safe `ForumSystemSeeder`. The backfill recognizes only
topics whose stored type is exactly `journal`, preserves their identity and
children, and marks unknown subtype metadata for review. It is safe to rerun.
Do not execute `ForumJournalDemoSeeder` in production.

After user journal data exists, recovery is forward-only:

1. retain topic, journal, entry, version, comment, collaborator, media, and
   audit rows;
2. restore incorrect lifecycle or type state through an authorized reviewed
   Action or additive migration;
3. reconcile a missing private file from backup without changing its parent
   or exposing a public path;
4. reload the latest optimistic version before retrying a conflicting edit;
5. rebuild views/config/routes and rerun the journal workflow tests.

Do not roll back by dropping journal tables or removing the additive comment
foreign key after production writes. See `docs/journals.md`.
## Event Operations

Event workflows require no new queue, cron, websocket, or supervisor.
Deployment must run the additive migration and production-safe event backfill,
then verify `/meetups`, one legacy stable URL, one group-linked event, and
protected attendee access. Demo event seeding is forbidden in production.

For recovery, retain the database backup, inspect append-only event history,
and apply a reviewed forward Action or migration. Never repair an event by
deleting registrations, reports, reviews, or history.

## Expert Session Operations

- Session phase and question-window closure are timestamp-derived and need no
  scheduler.
- Monitor question/report throttles, pending queue age, expired host
  credentials, stale sessions, and correction conflicts.
- If a credential expires or is suspended, host mutations stop immediately;
  existing educational history remains readable under its normal policy.
- A failed transaction leaves no partial question, answer, correction, or
  history event.
- After normalized session data exists, recover through a forward fix and do
  not drop the new tables.

## Topic Lifecycle Operations

- Age warnings and state projections are computed at read time; no cron,
  queue, or scheduler is required.
- Monitor stale/retention review queues, update-request age, rejected optimistic
  versions, bump-rate denials, redirect cycles, and legal-hold review dates.
- Use owner or moderator Actions for archive, remove, restore, merge, and
  redirect. Never edit lifecycle events or physically delete a topic as
  routine cleanup.
- A legal hold must be released only through the authorized audited Action.
  Do not expose its encrypted private reason in support output.
- Repair an incorrect state through a reviewed forward transition. Preserve
  the topic ID, slug, answers, comments, reactions, subscriptions, bookmarks,
  reports, attachments, and history.
- `ForumTopicLifecycleBackfillSeeder` is rerunnable; inspect conflicts rather
  than replacing administrator-owned category rules.

See `docs/topic-lifecycle.md`.

## Pet Profile Operations

Deploy the additive pet migration before the application release. Back up the
database and record counts for `pet_profiles` and all adjacent tables. Then
run `php artisan pets:backfill-profile-foundation --chunk=500` and verify that
every legacy pet has one current manager, privacy row, slug alias, and
foundation event. Rerun the same command; all created counts must be zero.

Smoke the private create screen, owner management, manager invitation and
acceptance, privacy save, one allowed lifecycle transition, one forbidden
direct request, and one public stable-key URL. Rebuild application caches only
after backfill and clear the documented pet projection keys after any privacy
incident.

After production manager/fact/lifecycle writes exist, recovery is forward-only:
retain pets, memberships, privacy, facts, aliases, events, and adjacent module
FKs; revoke unsafe access, append corrected facts or lifecycle transitions,
and deploy an additive fix. Do not truncate, detach the legacy owner, rewrite
`profile_key`, or drop the foundation tables. See `docs/pet-profiles.md`.

## Social Relationship Operations

Deploy the additive social migration, record authoritative user/pet/expert/
group counts, and run `social:backfill-actors --dry-run --chunk=500`. Run the
write command twice and require stable actor/settings counts while legacy
encrypted connection state remains unchanged.

Smoke one public follow, one approval follow, one owner friendship, one pet
friendship under current manager authority, one decline/cancel race, one block,
one settings version conflict, and direct Livewire denial. Monitor duplicate
key failures, expired pending requests, idempotency collisions, repeated
declines, blocks, and backfill count drift. Do not treat actor-level blocking
as account-wide protection.

After production relationships exist, recover with forward Actions or an
additive migration. Preserve requests and append-only events; never repair a
graph by deleting evidence or promoting prototype state into consent.
