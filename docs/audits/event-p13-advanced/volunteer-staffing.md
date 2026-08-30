# EVENT-S03 — Volunteer Staffing Discovery

Status: discovery only. No production, migration, test, translation, plan, or shared-document change is made by this report.

## Requirement Boundary

The authoritative source is `docs/requirements/forum-source-prompt.md` lines 60826–60935. The atomic records are `event.volunteer.0001`–`event.volunteer.0065`:

| Source group | Atomic range | Required outcome |
| --- | --- | --- |
| `volunteer-001` | 0001–0015 | Event roles cover setup, check-in, route leader/assistant, welfare, information desk, accessibility, speaker/vendor support, cleanup, transport, qualified medical support, and incident support. |
| `volunteer-002` | 0016–0027 | A role can require identity, organization membership, training, age, professional qualification, experience, equipment, and only a justified/lawful background check. |
| `volunteer-003` | 0028–0038 | A shift has role, start/end, location, supervisor, capacity, break, instructions, check-in/out, and incident-access scope. |
| `volunteer-004` | 0039–0048 | Requests, approval, assignment, waitlist, transfer, cancellation, no-show, and completion are durable operations. |
| `volunteer-005` | 0049–0055 | Participants cannot see volunteer contact, home address, private schedule, safety notes, or qualification documents. |
| `volunteer-006` | 0056–0065 | Briefing, emergency contacts, attendance, incident action, conditional partner requirement, rest/break plan, and safe refusal are provided. |

The staffing communication boundary also includes `event.communication.0005` (volunteer chat), `.0006` (staff chat), and `.0008` (emergency broadcast). The other controlled-conversation atoms remain owned by their event communication domains; staffing consumes a recipient-scope boundary and must not create parallel general chat.

## Current Reusable Authority And Gap

| Reuse | Evidence | Staffing use / limit |
| --- | --- | --- |
| Canonical event/occurrence | `ForumEvent`, `ForumEventOccurrence` | Staffing belongs to the existing event; each shift belongs to one occurrence. Do not create a second aggregate or overload `ForumEventType::VolunteerShift`. |
| Revocable authority | `ForumEventPolicy`, active `ForumEventTeamMembership`, `OrganizationMembership` | `VolunteerCoordinator` exists as a team role. Active team and organization membership must be checked at every operation; stale team rows must not authorize. |
| Session staff | `ForumEventSessionStaff`, `SaveForumEventSession` | Reusable only for a session's speaker/moderator/staff assignment. It has no qualification, application, capacity, attendance, waitlist, privacy, or transfer lifecycle. |
| Capacity/check-in pattern | `ForumEventRegistrationService` | Reuse event/occurrence locks, short transactions, FIFO waitlist, idempotency, audit style, and manual server-confirmed attendance; do not mix staffing into participant registrations. |
| Audit | `ForumEventHistory`, `ForumEventAudit` | Record operational summaries with idempotency. Never put qualification evidence, address, safety notes, or emergency contact text in audit metadata. |
| Organization restrictions | `OrganizationRestrictionCapability`, `Organization::allows()` | Existing capabilities concern registrations/participants/check-in, not volunteer staffing. Do not treat `AccessParticipantData` as volunteer-data authority. |
| Credentials | `Credential`, `CredentialPolicy` | A verified in-scope credential may be evaluated for qualified work after explicit subject/owner mapping; it is not a volunteer evidence store and documents remain private. |
| Messages/notifications | `ForumNotification`, `ForumEventNotifier`, `ForumEventMessage` | `ForumNotification` has global dedupe. Current messages support only `attendees`/`organizers`, so they cannot safely express shift, volunteer, or staff membership. |

No `ForumEventVolunteer*` model, policy, enum, migration, factory, seeder, or workflow test exists. `docs/events/volunteers.md` and `docs/events/requirements.md` correctly mark the domain unimplemented.

## Normalized Model

```text
ForumEvent
  └─ ForumEventVolunteerRole
       ├─ ForumEventVolunteerRoleRequirement (0..n)
       ├─ ForumEventVolunteerApplication
       │    └─ ForumEventVolunteerApplicationEvidence (0..n, private)
       └─ ForumEventVolunteerShift (one occurrence)
            └─ ForumEventVolunteerAssignment
                 ├─ ForumEventVolunteerAttendance (one current record)
                 └─ ForumEventVolunteerAssignmentEvent (append-only)
```

`ForumEventVolunteerRole` is an event-specific role definition, separate from the team role that authorizes a coordinator. It has an event-local stable key, a controlled kind covering all source roles, localized display content, public-identity choice, application window, and archive timestamp. A custom kind, if later needed, needs an event-owned localized label and never creates a permission.

`ForumEventVolunteerRoleRequirement` is one typed row per role: identity, membership, training, minimum age, credential, experience, equipment, or background check. It records applicability, verification method, and protected rationale/retention basis. A background check is forbidden unless the need is documented and lawful; a Boolean alone loses that decision.

`ForumEventVolunteerApplication` is the applicant's role request and qualification snapshot. It holds applicant, role, state, actor/timestamps, redacted coordinator-facing qualification result, and encrypted private answers. `...ApplicationEvidence` contains only private storage reference, content hash, evidence type, expiry/retention marker, and reviewer outcome; it never produces a public URL. Credential references are optional and must be revalidated if later revoked or expired.

`ForumEventVolunteerShift` belongs to event, occurrence, role, and an active supervisor team member. It stores UTC start/end plus occurrence timezone, capacity, room/venue reference or encrypted exact directions, check-in/out window/method, break/rest plan, briefing/instructions, emergency-contact and incident-action references, incident scope, partner minimum, and cancellation/archive state. All private instructions, emergency contacts, and safety notes use encrypted casts and hidden serialization.

`ForumEventVolunteerAssignment` joins one user to one shift and optionally an approved application. It stores state, waitlist position, qualification snapshot checksum, assignment/cancel/transfer actor and timestamps, successor link, lock version, and idempotency key. It is the capacity unit. `...Attendance` records server-confirmed check-in/out, no-show, completion, method, actor, timestamps, and correction reason. `...AssignmentEvent` preserves each transition and transfer without copying private evidence into generic event history.

### State machines

```text
application: draft -> submitted -> under_review -> approved | rejected | withdrawn | expired
assignment:  requested -> approved -> assigned | waitlisted
             waitlisted -> assigned | cancelled | withdrawn | expired
             assigned -> checked_in | cancelled | withdrawn | transferred | no_show
             checked_in -> checked_out -> completed
```

Approved application is eligibility evidence, not a commitment. A role-requirement change creates a new snapshot; it does not silently approve a new duty. A transfer terminates the source assignment and points to a successor with independently current eligibility and, where needed, explicit acceptance. No-show and completion are terminal. Repeated same-operation calls return the idempotent result; contradictory terminal changes fail. Safe refusal permits withdrawal without disclosure of a sensitive reason.

## Constraints, Capacity, Transactions, And Idempotency

Use additive migrations and `restrictOnDelete()` for operational history; archive/revoke rather than delete.

| Record | Required uniqueness and indexes |
| --- | --- |
| Role | unique `(forum_event_id, stable_key)`; index `(forum_event_id, archived_at, role_kind, id)`. Enforce active role-kind reuse in the action where a portable archived partial unique index is unavailable. |
| Requirement | unique `(role_id, requirement_kind)`; index `(role_id, is_required, id)`. |
| Application | one durable `(role_id, user_id)` row with state history; unique idempotency key; indexes `(role_id, status, submitted_at, id)` and `(user_id, status, id)`. |
| Evidence | unique `(application_id, content_hash)`; index `(application_id, retention_expires_at, id)`; private disk/path only. |
| Shift | unique stable key; indexes `(occurrence_id, starts_at, ends_at, status, id)`, `(role_id, status, starts_at, id)`, `(supervisor_user_id, starts_at, ends_at, id)`. |
| Assignment | unique `(shift_id, user_id)` and `(shift_id, idempotency_key)`; unique `(shift_id, waitlist_position)` when present; indexes `(shift_id, status, waitlist_position, id)`, `(user_id, status, id)`, successor link. |
| Attendance/history | one attendance record per assignment and unique mutation idempotency; indexes by assignment/time. |

Validate positive capacity/partner minimum, `ends_at > starts_at`, check-in range within shift, shift range within occurrence, event ownership, supervisor scope, and status-dependent fields. Portable uniqueness cannot enforce capacity, time overlap, or all active-state conditions.

Each mutation authorizes before and again inside `DB::transaction(..., 3)`. Lock in order: event, occurrence, role/shift, applicant or assignment, then candidate overlapping assignments. Re-check current organization/team membership after locking. Capacity counts states that reserve the roster place: `assigned`, `checked_in`, `checked_out`, and `completed` until occurrence closure. Waitlisted, cancelled, withdrawn, transferred, and no-show do not reserve a future place.

Allocate immutable FIFO waitlist positions while the shift is locked, ordered by `waitlist_position, id`. On cancellation, promote only the first eligible volunteer after rechecking requirements and partner conditions. Do not auto-promote during a live-shift no-show; a coordinator decides. Transfer locks source and target and changes capacity in one transaction.

Every client mutation needs an opaque idempotency key. A same aggregate/actor/operation/payload replay returns the existing result; reuse against another event, user, shift, or payload is a conflict. Increment lock version for mutable records and record immutable operational history. Browser state is never authority.

## Policy, Projection, And Communication Matrix

Add dedicated staffing abilities rather than widening `manageRegistrations` or `sendMessage`. Non-administrator access requires active account, current organization membership for organization-owned events, active team membership, and new fail-closed organization capabilities `manage_volunteer_staffing` and `access_volunteer_sensitive_data`.

| Principal | Allowed | Denied |
| --- | --- | --- |
| Owner/event administrator/primary organizer | Manage roles, shifts, applications, assignments, attendance corrections, and staffing audit. | Bypass of lawfulness, capacity locks, evidence retention, or delivery authorization. |
| Active `VolunteerCoordinator` | Event-scoped staffing operations and minimum contact/eligibility result needed for roster/supervision. | Participant records, full credential evidence, unrelated organization data, broad safety records. |
| Supervisor/check-in operator | Own shift's minimum roster, required briefing/contact relay, check-in/out/no-show. | Application evidence, address, qualification documents, other supervisors' private schedules. |
| Safety/medical lead | Assigned incident-action and only minimum roster/contact scope necessary. | Routine evidence and broad participant access. |
| Assigned volunteer | Own application/status, assigned/waitlisted shifts, briefing, rest plan, emergency relay, safe refusal, authorized staff conversation. | Others' private schedules/contact/evidence and participant health/registration data. |
| Applicant/waitlisted volunteer | Own application/status, public requirements, own waitlist state. | Roster, assignment notes, emergency contacts, other applications, staff chat before active membership. |
| Participant/public | Deliberately public role label/count and duty summary only. | Every field prohibited by `event.volunteer.0050`–`.0055`, including volunteer identity unless separately opted public. |
| Removed/expired member | No staffing query, private projection, conversation membership, notification delivery, or attendance action; historic attribution remains. | All current access despite stale browser/team data. |

Public projection includes at most role kind/title, public duty summary, non-sensitive accessibility need, and aggregate open/filled count. It excludes name, email, phone, address, private exact location/schedule, application/evidence, qualification result, safety notes, emergency contact, waitlist position, and attendance. Staff views use prepared policy-filtered DTOs; Blade receives neither models nor private relationship work. Stable public keys, not numeric IDs/idempotency keys, identify records.

`volunteer_chat` is restricted to currently assigned/waitlisted volunteers and authorized staffing coordinators for the same event; `staff_chat` to the explicit active team/shift staff set; `emergency_broadcast` additionally requires safety authority and an audited reason. Resolve membership at send and delivery. Cancellation, transfer, removal, expiry, and suspension revoke membership immediately. Do not reuse `ForumEventMessage` until its two-value audience enum and retrieval scope are replaced with controlled recipient membership.

## After-Commit Notification Dedupe

`ForumNotification.deduplication_key` is reusable for in-app dedupe but has no staffing foreign key/delivery state. Dispatch typed staffing facts only after commit (`ShouldDispatchAfterCommit` or after-commit outbox listener). The listener re-resolves currently authorized recipients, localizes from translation keys, uses `firstOrCreate`, and adds durable channel delivery only if needed later.

Use `event-volunteer:{assignment-stable-key}:{transition}:{lock-version}:{recipient-actor-key}:{channel}`. It deduplicates retries but permits later state changes. Notification text must not reveal directions, emergency contacts, qualification status, or safety notes; direct the user to the authorized workspace. Rollback sends nothing, and delivery rechecks access before resolving private content.

## Factories, Seeds, And Adversarial Tests

- Factories: role/requirement; application with draft/submitted/reviewing/approved/rejected/withdrawn/expired; private evidence helper; occurrence-bound shift with capacity/break/partner states; assignment with requested/assigned/waitlisted/attendance/no-show/cancelled/transferred states; attendance and immutable event factories. Defaults satisfy constraints and relationship helpers state meaning.
- Seeds: environment-gated stable-key `updateOrCreate`/action paths only; repeat-safe fictional non-sensitive roles/shifts/applications/assignments. Never seed background checks, real contacts, credential evidence, or incident notes. Add representative-model and coverage entries only with implementation.
- Authorization: test anonymous, participant, applicant, waitlisted/assigned volunteer, supervisor, coordinator, owner, safety lead, administrator, cross-event actor, former team/organization member, and restricted organization. Cross-tenant access must not disclose existence.
- Privacy: prove every `volunteer-005` value is absent from public/participant/Livewire/notification/serialization/unauthorized-download output; revoked recipients lose queued conversation and notification delivery.
- Validation/state: every requirement kind, background-check rationale, credential expiry/revocation, foreign ID, occurrence window, capacity, break/partner plan, supervisor scope, all allowed/forbidden transitions, duplicate attendance, cancellation/FIFO promotion, transfer eligibility, and safe refusal.
- Concurrency: final-seat double assignment, double promotion, cancellation racing transfer, and replay racing altered idempotency payload. Assert one seat, one waitlist position, one history transition, and no duplicate notification.
- Runtime: rollback emits neither history nor dispatch; after-commit retry delivers once per state version; migration rollback/reapply around populated event data; EN/LT/RU labels; private Blade/resource architecture; factory/guarded repeat-seed and bounded-roster query tests.

## Rollback Risks And Smallest Durable Boundary

Post-production rollback cannot restore private evidence or operational history; use forward fixes and document development-only schema rollback. Do not conflate volunteer duty with event-team authority, reuse registrations/session staff, depend on non-portable SQLite partial constraints for capacity, generate notifications before commit, or store qualification/background-check material in metadata, demo fixtures, browser state, public storage, or audit rows.

Implement one additive `ForumEventVolunteer*` package: typed role/requirement, occurrence-bound shift, application/private evidence, assignment/attendance/history, dedicated policies and restriction capabilities, private DTO projections, and after-commit deduplicated notification facts. Reuse event, occurrence, team/organization membership, `ForumEventAudit`, transactions, and `ForumNotification`; do not mutate participant registration capacity or overload session staff. Generic conversation persistence remains a coordinated dependency: S03 owns recipient membership and emergency authority, while the event communication owner supplies the common conversation/delivery aggregate.
