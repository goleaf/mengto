# EVENT-S04 — Safety Incidents Discovery Report

Date: 2026-08-30  
Status: discovery only — no production implementation, migration, test, translation, plan, or canonical documentation was changed.

## Requirement Boundary

The immutable source is `docs/requirements/forum-source-prompt.md`, section
22, `safety-001` through `safety-012`; the generated atomic catalogue maps
them to `event.safety.incident.0002` through
`event.safety.incident.0149`, all currently `planned / discovered`.
`event.foundation.0224` requires event-incident records to preserve observer,
time, context, and source; `event.foundation.0225` keeps medical records in
the medical module. P22 additionally requires factual incidents, source
statements, people/pet/witness/media/action/reviewer/follow-up/escalation
links and explicitly prohibits automatic diagnosis or final fault.

| Source requirement | Exact requirement coverage | Proposed boundary |
| --- | --- | --- |
| `safety-001` / atoms `.0002-.0019` | A proportional plan with responsible leads, emergency, evacuation, lost-animal, injury/conflict/weather, child/crowd/restricted-zone, communication, and documentation processes. | Separate, versioned `ForumEventSafetyPlan` and typed risk items. This report does not make the plan implementation part of the incident aggregate. |
| `safety-002` / `.0020-.0038` | Assess species, people/animal counts, environment, weather, noise, traffic, water, terrain, ventilation, food/contact, children, equipment, transport, online privacy, and payment risk. | Typed risk factors linked to the safety-plan version, not a free-form incident field. |
| `safety-003` / `.0039-.0047` | Risk-sensitive event review preserves reviewer, plan version, concerns, required changes, approval, expiry. | Immutable review decision record; event publication checks its current, unexpired approval where the type/risk policy requires it. |
| `safety-004` / `.0048-.0072` | Controlled incident categories: human/animal injury, illness, escape/missing animal, conflict, bite/scratch, property/equipment, heat/cold/weather/crowd, harassment/child/accessibility, payment/ticket fraud, privacy/recording, prohibited product, organizer absence, venue issue. | `ForumEventIncidentCategory` backed enum. It is a category, never a medical diagnosis or liability result. |
| `safety-005` / `.0073-.0091` | Event, occurrence, session or location, time, reporter, participants/animals/witnesses, factual description, immediate action/media, status, reviewer, medical/location/moderation escalation, follow-up. | Incident root plus normalized link, statement, action, evidence, escalation, and history records described below. |
| `safety-006` / `.0092-.0099` | Initial record has no blame and preserves observed fact, participant statement, organizer statement, professional assessment, and unknown. | Immutable source statements with a closed `source_kind`; no `fault`, `liable_party`, diagnosis, reputation, payment, or ranking field. |
| `safety-007` / `.0100-.0108` | Urgent incident may pause event, send emergency announcement, create medical/lost-pet/security/organizer escalation, and reconcile check-in. | A human-authorized action/escalation record. Urgency permits escalation; it does not auto-decide any outcome. |
| `safety-008` / `.0109-.0119` | Lost animal connects pet, handler, last confirmed location, check-in, route, staff, local search, lost-and-found case, temporary location access. | Reuse one `SearchCase`, its private coordination access, and `PlaceAccessGrant`; never clone a lost-pet workflow inside events. |
| `safety-009` / `.0120-.0127` | No unsupported diagnosis; may show emergency profile to an authorized person, contact designated person/professional, record first response, and open medical incident workflow. | Reuse `MedicalRecord`, `MedicalAccessGrant`, and medical-domain policy; an event incident stores only the link and operational request/result metadata. |
| `safety-010` / `.0128-.0137` | Authorized safety/moderation staff may suspend registration, check-in, a session, category, pet participation, booth, or full event without data loss. | Scoped, reversible operational actions and append-only history; only currently existing event/occurrence/session/registration/pet scopes ship in the first slice. Categories and booths wait for P26/P27 aggregates. |
| `safety-011` / `.0138-.0147` | Removal has authorized role, reason, safety/rule basis, ticket/refund/access/appeal consequences, without public shaming. | A private incident action that invokes the established registration/ticket/refund/appeal actions when those durable domains exist; never models public removal or exposes it in a participant list. |
| `safety-012` / `.0148-.0149` | Collect emergency contacts only when justified and keep them from public or ordinary participant access. | Contact details remain encrypted medical/event safety-plan data; the incident stores a contact-attempt audit, not a copied contact value. |

## Current-State Reuse Map

| Existing authority | Reuse | Do not reuse it as |
| --- | --- | --- |
| `ForumEvent`, `ForumEventOccurrence`, `ForumEventSession` | The canonical aggregate and durable event/occurrence/session identity. Event and occurrence private exact-location fields are encrypted. | An incident table or a diagnosis store. |
| `ForumEventPolicy`, `ForumEventTeamMembership`, `ForumEventTeamRole` | Existing active-account, organization, team-role, safety-lead, welfare-officer, medical-contact, access-detail, and safety-suspension decisions. `TransitionForumEventStatus` already locks, records history, and can enter `safety_suspended`. | Evidence disclosure authority or a blanket organizer override. P22 forbids ownership from deciding final safety/medical/eligibility/fault outcomes. |
| `ForumEventHistory` and `SearchCaseEvent` | Event-history vocabulary and the stronger `SearchCaseEvent` append-only/idempotency pattern. | The incident evidence trail: incident history needs its own constrained readers and metadata shape. |
| `ForumReport`, `ForumReportAttachment`, `ForumReportEvent`, `ForumReportPolicy` | Optional linkage for moderation escalation; private reporter/details/attachment fields and a moderation case relation. | The incident root. Generic reports have no occurrence/session/participant/pet/source-statement/action/medical/lost-pet semantics and report triage is administrator-only. |
| `MedicalRecord`, `MedicalAccessGrant`, `MedicalEvent`, `MedicalRecordPolicy` | Medical is its own private domain, accessed through owner/pet-manager medical permission. | Event medical notes, diagnosis, emergency-contact copies, or staff-wide medical disclosure. |
| `SearchCase`, `SearchCaseEvent`, `SearchCasePolicy` | Lost-pet escalation, encrypted exact location/contact data, lock version, append-only case history, private coordination, and protected contact relay. | A generic incident or a public record of an event attendee. |
| `Place`, `PlaceAccessGrant`, `PlaceAccessAudit`, `PlaceLocationVersion`, `PlacePolicy` | Public-region versus encrypted exact-location split; account-, purpose-, event-, expiry-, and revocation-bound exact-location grants with audit. | Storage for incident facts, or a direct disclosure to ordinary participants. |
| `ContentMediaAsset` | Private disk/path/checksum/byte-size/media-type/status metadata and durable asset identity. | Public event gallery media. Incident evidence needs an incident-specific association, download policy, capture provenance, redaction state, and retention/legal-hold control. |
| Event registrations and registration pets | Verified event participation and pet ownership/eligibility context; use them as scoped links rather than trusting a submitted user/pet ID. | Proof of fault, diagnosis, or a public incident roster. |

`docs/events/incidents.md`, `docs/events/safety.md`, and
`docs/events/requirements.md` correctly state that the dedicated incident,
source separation, urgent triage, and escalation aggregate does not exist.
The current generic report route must therefore remain described only as
moderation reporting until this boundary is delivered.

## Normalized Durable Model

Use `forum_event_incidents` as one private root per reported occurrence, never
a polymorphic extension of `ForumReport`.

### Root and lookup records

`forum_event_incidents`

- `id`; `forum_event_id` (required, restrict delete); nullable
  `forum_event_occurrence_id`, `forum_event_session_id`, `place_id`, and
  `venue_id`; `reported_by_user_id` (nullable only for a system-received,
  authenticated temporary-identity flow); `reported_at` and
  `occurred_at`/`occurred_ends_at` as immutable UTC instants; event timezone
  snapshot; `category`, `severity`, `status`, `urgency`; `assigned_reviewer_id`;
  `lock_version`; `submission_idempotency_key`; and timestamps.
- `initial_summary` is encrypted text, `location_detail` is encrypted text or
  encrypted structured coordinates, and `safe_metadata` contains only
  allowlisted non-sensitive operational keys. They are hidden from model
  serialization. The root contains no free-form fault, diagnosis, emergency
  contact, medical record body, public message, or automatic decision field.
- `severity` is a closed ordered enum (`low`, `moderate`, `high`, `critical`)
  used solely for routing and work priority. `urgency` is a separate closed
  enum (`routine`, `urgent`, `emergency`) so a high-severity historical record
  is not forced to page staff.
- `category` is the exact controlled list in `safety-004`; a later category
  change is a history event, not replacement of the original report category.

`forum_event_incident_statements`

- One or more append-only factual records: `forum_event_incident_id`,
  `source_kind` (`observed_fact`, `participant_statement`,
  `organizer_statement`, `professional_assessment`, `unknown`),
  `author_user_id` nullable, `subject_user_id` nullable, `professional_credential_id`
  nullable, `recorded_by_user_id`, `observed_at` nullable, encrypted `body`,
  `language`, `created_at`, and a per-submission idempotency key.
- `source_kind` says who/what supplied the information, not whether it is
  true. A professional assessment may reference a verified credential but
  does not create a diagnosis or final fault. The submitted initial statement
  is immutable; corrections are a new statement with a `corrects_statement_id`
  reference and a reason, never an update.

`forum_event_incident_people` and `forum_event_incident_pets`

- People: incident FK, nullable `user_id`, nullable `forum_event_registration_id`,
  role (`involved`, `witness`, `handler`, `affected_person`, `reporter`), and
  encrypted display/relationship snapshot only where an identity cannot be
  linked. A linked registration must belong to the incident event/occurrence.
- Pets: incident FK, `pet_profile_id`, nullable `forum_event_registration_pet_id`,
  role (`involved`, `affected`, `missing`, `witness_animal`), handler-person
  link nullable, and a minimal encrypted snapshot of the identity/mark needed
  for response. The Action verifies the registration-pet belongs to the
  linked event/occurrence and that the actor may name a pet; it does not expose
  another attendee's medical profile.
- Keep witnesses separate from statements: someone may be a witness without
  providing a statement, and a statement's author need not be an involved
  person.

`forum_event_incident_actions`

- An append-only operational fact: incident FK; issuer; `action_type`
  (`paused`, `suspended`, `stopped`, `removed`, `access_revoked`,
  `first_response_recorded`, `announcement_requested`,
  `checkin_reconciliation_requested`); `scope_type`; nullable FKs for the
  concrete current scope; reason/basis code; encrypted factual notes; taken
  and reversed times; reversing actor/reason; external-operation idempotency
  key.
- Initial supported scopes are `event`, `occurrence`, `session`,
  `registration`, and `registration_pet`. The Action validates an exact
  one-of scope and tenant/event ancestry. Do not add generic polymorphic IDs.
  `competition_category` and `vendor_booth` scopes are deferred until P26 and
  P27 provide their canonical FKs. This prevents an apparently valid
  suspension pointing at arbitrary data.
- An action is operational, not punitive. A removal records its basis and
  invokes the relevant access, refund/ticket, and appeal workflow when present;
  it has no public projection and no social/reputation side effect.

`forum_event_incident_evidence`

- `forum_event_incident_id`, `content_media_asset_id`, uploader/recording
  actor, `evidence_kind`, encrypted original capture time/location/note,
  safe MIME/byte-size/checksum snapshot, `redaction_status`,
  `redacted_by_user_id`, `redacted_at`, retention/hold fields, and an
  idempotency key. The file stays on a non-public disk and is delivered only
  after per-request policy authorization.
- Do not copy `disk`/`path` into public presenters or Livewire public state.
  `ForumReportAttachment` is a useful earlier private-attachment pattern, but
  lacks the redaction, access-audit, retention, and incident-specific
  provenance required here.

`forum_event_incident_escalations`

- One record per requested/created escalation: incident FK; `kind`
  (`medical`, `lost_pet`, `moderation`, `security`, `organizer`,
  `emergency_announcement`, `checkin_reconciliation`, `location_access`);
  requested/created/resolved/failed status; requester/responsible actor;
  external canonical FK (`medical_record_id`, `search_case_id`,
  `forum_report_id`, or `place_access_grant_id`) only where it exists;
  encrypted minimal operational detail; idempotency key; timestamps.
- The medical escalation may request or audit an authorized medical response;
  it must not duplicate a medical record or grant broad medical access. Lost
  pet escalation creates/links exactly one `SearchCase` through its own
  idempotent Action and uses a short purpose-bound `PlaceAccessGrant` only for
  authorized search coordination.

`forum_event_incident_history`

- Immutable audit facts: incident FK, actor/acting-as role, event type,
  prior/current status, reason code, non-sensitive metadata, idempotency key,
  and `created_at`. Implement model-level update/delete rejection as in
  `SearchCaseEvent`, plus a database uniqueness constraint for the idempotency
  key. The history is private and not a replacement for source statements.

`forum_event_incident_notification_deliveries`

- Private outbox/deduplication records: incident FK, recipient user or
  explicitly scoped destination, `notification_kind`, locale/template version,
  safety-level, event occurrence scope, delivery state, sent/failed times, and
  idempotency key. This records intended/delivered notifications without
  writing incident facts into a generic notification payload.

## State Machines And Transaction Boundaries

### Incident status

`reported -> triaged -> active -> awaiting_follow_up -> resolved -> closed`

- `reported -> urgent_active` is permitted only when a safety lead, welfare
  officer, medical contact, authorized moderator, or administrator makes the
  recorded human decision. `urgent_active` returns to `active` or
  `awaiting_follow_up`; it is not a diagnosis or fault result.
- `resolved -> active` and `closed -> active` require a human reopen reason;
  both preserve the resolution and append history. `closed` means the incident
  workflow is complete, not that a participant is at fault.
- Source statements and evidence can be appended in every non-closed state;
  closed records accept only explicitly authorized correction/retention/legal
  hold actions. No status transition deletes a row.

### Action and escalation state

- Operational actions are `requested -> applied -> reversed` or
  `requested -> failed/cancelled`; they cannot be silently overwritten.
- Escalations are `requested -> acknowledged -> linked/in_progress ->
  completed` or `failed/cancelled`; retry creates an auditable retry history,
  not a duplicate medical/search/moderation case.
- Medical, lost-pet, and moderation subdomain states remain independent. The
  incident stores a link and an operational escalation outcome only.

### Required short transactions

1. **Create incident**: authorize reporter for the event, lock/reload event
   and selected occurrence/session/registration/pet records, validate common
   ancestry and current access, insert root + initial statement + links +
   history atomically, then register notification work after commit. The root
   idempotency key returns the original incident for a repeated browser submit.
2. **Urgent action**: lock incident and scoped resource; reauthorize the
   actor's current role; make the requested scoped state mutation (or call its
   canonical Action), append action/history/outbox records, and commit before
   dispatch. A stale `lock_version` fails rather than applying an old stop or
   reversal.
3. **Escalate**: lock the incident, insert/find the unique escalation request,
   invoke the target domain Action within its documented transaction boundary,
   store only the returned canonical link, and dispatch after commit. A
   failed external/domain operation leaves a visible `failed` escalation with
   sanitized reason code, never a phantom success.
4. **Evidence**: validate, scan/process, and persist the private asset before
   attaching it. Only attach a ready asset owned/created under the incident
   operation; transactional attachment + history is idempotent. A failed
   upload never creates an accessible evidence row.

## Constraints, Indexes, And Concurrency

- Unique `forum_event_incidents.submission_idempotency_key`; root foreign keys
  use `restrictOnDelete` for incident/event history retention, with nullable
  actor FKs using `nullOnDelete` where attribution must survive account
  deletion.
- Foreign keys on every event/occurrence/session/registration/registration-pet
  relation; application validation and locked reads enforce same-event and
  same-occurrence ancestry. Do not trust browser IDs or a preflight
  `exists()` result.
- Unique incident link pairs (`incident_id,user_id,role`;
  `incident_id,pet_profile_id,role`; `incident_id,asset_id`) and unique
  per-incident action/escalation/notification idempotency keys. A database
  constraint, not a cache key, owns repeat-submit safety.
- Required measured read indexes: `(forum_event_id,status,urgency,reported_at,id)`
  for private operational queues; `(assigned_reviewer_id,status,reported_at,id)`;
  `(forum_event_occurrence_id,status,occurred_at,id)`; history
  `(incident_id,created_at,id)`; evidence `(incident_id,created_at,id)`;
  escalation `(incident_id,kind,status,id)`; and notification outbox
  `(delivery_state,created_at,id)`. Confirm final composite order with
  production-like `EXPLAIN QUERY PLAN`; do not duplicate FK indexes blindly.
- `lock_version` belongs on the mutable root and any mutable scope object, not
  on append-only rows. Use `lockForUpdate()` inside small transactions for
  state changes. Do not hold a DB lock while file processing or notification
  delivery runs.
- Schema must remain SQLite-safe: schema builder, portable FKs/unique/index
  constraints, enum strings, and application-enforced scope-XOR validation;
  no database-specific triggers, JSON querying, raw SQL, or historical
  migration edits.

## Policy Matrix And Projections

| Operation | Reporter / participant | Safety lead or welfare officer | Medical contact | Organizer / owner | Moderator / administrator | Public |
| --- | --- | --- | --- | --- | --- | --- |
| Create factual report | Active user who may report the event; submit only their statement/evidence and authorized pet links. | Yes. | Yes. | Yes, but no owner-only final decision. | Yes. | Never. |
| View own submitted statement/status | Own minimal acknowledgement and safe status only. No other people, witnesses, evidence, staff notes, exact location, medical or moderation links. | Assigned/event-scoped operational view. | Only the minimum incident/action data needed for response; medical data still requires `MedicalRecordPolicy`. | Only an explicitly scoped operational view, not all medical/evidence data. | Assigned moderator/admin under a case/triage policy. | Never. |
| Triage/assign/resolve/reopen | No. | Assigned safety/welfare staff within active event/organization authority. | May record first-response/escalation outcome, not diagnose. | May supply organizer statement and operational facts; cannot alone reach a final safety/medical/eligibility/fault outcome. | Moderator/admin with role and, for sensitive cases, assignment/recusal controls. | Never. |
| Stop/suspend scope | May request only. | Safety lead/welfare officer for event/occurrence/session/registration/pet scope. | Medical-contact role may request/record medical response; stop authority must be explicit, not implied. | Existing manager authority only where it does not override P22's independent safety boundary. | Moderator/admin under explicit scope. | Never. |
| Evidence upload/download/redact | Upload own evidence through validation; no raw storage path. Download only own permitted material. | Upload/view only incident-scoped need-to-know evidence. | Same, minimum necessary. | No default access. | Assigned moderator/admin; redaction is separately authorized/audited. | Never. |
| Medical/lost-pet escalation | Request only. | Request/link according to role. | Medical request only through medical-domain policy. | Request, not diagnosis. | Moderation/security request as scoped. | Never. |

Policies must be resource-specific (`ForumEventIncidentPolicy` plus separate
evidence/action/escalation checks), query-scope before model resolution, and
recheck active account, team membership, organization restriction, event
visibility, staff removal, event archival, and direct-object ancestry at every
mutation. A Livewire locked ID is not authorization. Policy methods should
cover at least `viewAny`, `view`, `create`, `addStatement`, `addEvidence`,
`triage`, `assign`, `act`, `escalateMedical`, `escalateLostPet`,
`escalateModeration`, `redactEvidence`, `resolve`, `reopen`, `export`, and
all delete/restore/force-delete methods (normally false).

There is no public incident projection. Public event resources, shared caches,
SEO, Livewire public properties, notification previews, attendee lists, and
analytics exclude incident IDs, category/severity, people/pets, statement
text, evidence, action, escalation, resolution, and exact location. Public
messages may state only an independently authorized event-state update (for
example, “session paused”) and must not identify a reporter, affected person,
animal, medical fact, removal, or alleged conduct.

## Evidence, Redaction, Escalation, And Notification Boundary

- Validate MIME from content and extension, bounded size/count, scan/process
  status, generated filename, checksum, and private disk. Reject active or
  unscanned content. Treat original names, EXIF/geolocation, visible faces,
  account details, and medical material as private; retain only allowlisted
  safe metadata in an operational projection.
- Evidence downloads use a server-authorized controller/Action on every
  request, with content disposition, no stable public URL, access audit, and
  no disclosure from `ContentMediaAsset` serialization. Redaction creates a
  derived asset or a withheld state while preserving the original under a
  stricter legal/retention boundary; it never overwrites forensic source data.
- Exact incident location is encrypted. Reuse `PlaceAccessGrant` for any
  time-bounded lost-pet coordination disclosure and audit each reveal. Do not
  attach an incident to a public place card or use public latitude/longitude
  as a substitute for a known last location.
- A medical escalation contains no diagnosis. It can expose an emergency
  profile only through existing medical authorization, record that a contact
  attempt/first response happened, and link the medical-domain record if
  creation is authorized. It cannot grant event staff ongoing medical access.
- A lost-pet escalation passes minimal incident context to a dedicated,
  idempotent search-case creation Action, links handler/pet/last confirmed
  location/check-in/staff as authorized, and gives temporary location access
  only to current, scoped search coordinators.
- Emergency broadcasts are explicit human actions. Build recipient queries
  server-side from the affected event/occurrence/session and currently
  eligible staff/registrations; honor the lawful urgent-delivery policy,
  minimize message details, and put one private outbox record per
  incident/recipient/kind/template-version. The unique outbox key plus
  after-commit dispatch prevents refresh/retry duplicates. Notification
  failure never rolls back a recorded urgent stop or falsely reports delivery.

## Factories, Seeds, And Adversarial Tests

Factories should provide a minimal valid incident with event, occurrence,
reporter, factual `unknown` statement, and private idempotency key; explicit
states cover every category, severity, urgent condition, statement source,
evidence redaction, action scope/reversal, escalation result, resolution, and
former staff. Factories must not fabricate medical diagnoses, contacts,
public incident media, or provider results. Seed only local/demo deterministic
non-sensitive scenarios behind existing environment gates; repeat seeding
must preserve counts and idempotency. Do not seed a real emergency contact,
medical fact, exact location, or identifiable incident narrative.

Required focused Pest coverage:

1. Every `safety-004` category and severity is accepted; invalid categories,
   malformed severity, empty factual statement, blame/fault field, diagnosis
   field, arbitrary metadata, and cross-event occurrence/session/registration
   IDs fail validation.
2. Initial statements and later correction statements preserve source kind,
   actor/time/context; update/delete attempts on statement/history/action
   records are rejected; correction never mutates original text.
3. Guest, inactive, unverified where required, unrelated attendee, wrong
   organization, blocked actor, former organizer, removed safety staff,
   ordinary organizer, unassigned moderator, and stale-ID actor cannot read
   or mutate an incident outside their scope. Direct routes, Livewire actions,
   and download endpoints all enforce this.
4. Reporter sees only a minimal acknowledgement; public cards, APIs, route
   binding, view data, Livewire payload/dehydration, cache entries, event
   exports, notification previews, logs, and error responses contain no
   incident detail, identity, pet, evidence path, exact location, medical,
   moderation, or removal information.
5. A safety lead/welfare officer can stop only an in-scope event/occurrence/
   session/registration/pet; an action cannot target a foreign object,
   category/booth before P26/P27, or be replayed. Concurrent stop/reverse or
   resolution requests observe lock-version conflict and exactly one history
   action.
6. Urgent incident creation creates one root/history/outbox set under repeated
   submission and a single emergency-delivery intent per recipient/kind. A
   dispatcher retry is idempotent and a delivery failure is visible without
   creating a second incident or rolling back the stop.
7. Medical escalation cannot expose a medical record to reporter, organizer,
   ordinary staff, wrong pet manager, or former staff; it cannot create a
   diagnosis. Lost-pet escalation creates/links one authorized `SearchCase`,
   preserves private exact location, and revokes temporary grants at expiry,
   removal, or incident closure.
8. Evidence validation rejects unsafe/oversize/unscanned uploads and forged
   asset IDs; disk/path/checksum/original name and raw EXIF never serialize.
   Authorized download/redaction is audited; an unauthorized, expired, or
   revoked actor receives no file or existence oracle.
9. Removal requires authorized role, reason/basis, private access revocation,
   and durable consequence linkage; it is absent from public views and
   attendance analytics treat welfare early departure neutrally. No payment,
   popularity, AI output, or organizer ownership can create a final safety,
   medical, eligibility, or fault decision.
10. Query-budget tests use constrained eager loads and selected fields for
    queues/history/evidence. Migration tests prove fresh and upgrade SQLite
    schemas, foreign-key integrity, unique idempotency, and indexes; factory
    and repeat-seeder tests cover all new models.

## Rollback And Smallest Durable Recommendation

The highest rollback risks are a public or cached leak of evidence/location/
medical data; a partially applied urgent action; duplicate emergency notices;
an irreversible mistaken removal; and a cross-domain medical/search record
or access grant left after incident rollback. Mitigate with expand-and-contract
migrations, additive tables only, private presenters, after-commit outbox,
idempotency constraints, append-only history, reversible scoped actions, and
compensating revocation Actions. Do not drop incident rows, delete evidence,
or collapse a medical/search case during ordinary incident closure.

The smallest durable implementation is therefore:

1. private incident root, factual statements, people/pet links, append-only
   history, closed category/severity/status/source enums, policy, factory, and
   direct-action tests;
2. private evidence association and download/redaction authorization reusing
   `ContentMediaAsset` private storage;
3. event/occurrence/session/registration/registration-pet pause/removal
   actions with transactions, optimistic locking, and private notification
   outbox; and
4. idempotent links to the existing medical, moderation, search, and place
   grant boundaries, without recreating their records or authority.

Safety-plan/risk-review persistence, weather provider work, vendor/booth and
competition-category stops, ticket/refund consequences, and full emergency
broadcast delivery should follow only when their own canonical P22/P26/P27/
P19 subdomains are present. This sequence gives P22 a factual, private,
non-diagnostic incident core without claiming that incomplete dependent
domains exist.
