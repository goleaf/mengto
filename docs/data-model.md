# Data Model

## Storage Baseline

- 68 migrations created 71 SQLite tables at baseline; the current additive
  schema has 111 migrations and 191 tables after identity, care-sync,
  social-state, device-lifecycle, forum taxonomy, global animal taxonomy,
  reputation, moderation, credential verification, structured-community, and
  persistent-group work.
- Tests use isolated in-memory or temporary SQLite.
- Schema and Eloquent queries remain portable unless an ADR explicitly accepts
  a production-engine-specific optimization.
- No first-party database views, triggers, or raw SQL are active.

## Table Groups

| Domain | Tables |
| --- | --- |
| Framework | `users`, password reset, sessions, cache/locks, jobs/batches/failed jobs |
| Social identity/state | `pet_profiles`, pet managers/privacy/lifecycle events/slug aliases/versioned facts/primary-media placements, encrypted/versioned `user_domain_states`, `photo_assets`, `photo_comments`, `photo_reactions` |
| Forum | topics, answers, comments, votes, engagements, blocks, polymorphic reports, report events/evidence, categories/translations/aliases/redirects/lifecycle rules, topic definitions/lifecycle events/update requests/legal holds, reputation/trust/badges, confirmations, moderation cases/actions/appeals/recusals, notifications, persistent groups/memberships/invitations/audit events/taxon links, group activities/announcements/private files/polls/options/votes |
| Animal taxonomy | versioned sources/imports/versions/issues, taxa, names, external identifiers, change history, domestic classifications, breed registries, community groups |
| Knowledge | articles with translation provenance, append-only versions, corrections, normalized collaborators, append-only workflow events |
| Experts | profiles, credentials, services, availability slots, bookings, document grants, consultations, publications, reviews, engagements, reports |
| Marketplace and adoption | listings, listing engagements, reservations, compatibility listing reports, orders, disputes, listing reviews, adoption cases, encrypted applications, append-only adoption events |
| Lost/found | cases, sightings, sectors, tasks, volunteers, updates, alerts, reports, immutable case events, encrypted contact relays |
| Care | journals, routines, tasks, entries, media, access grants |
| Medical | canonical pet-linked records, events, vaccinations, weights, medications, doses, documents, reminders, access grants |
| Devices | devices, assignments, readings, events, commands, safe zones, automations, runs, access grants, lifecycle records |
| Places and venues | places, venues/areas, exact-location grants/audits/versions, shared questions and official answers |
| Cross-domain | audit logs |

## Identity Strategy

The existing schema uses human-readable actor keys. `users.actor_key` becomes
the unique authoritative bridge without destructive rewrites. New ownership
foreign keys may be added later through expand-and-contract only after every
legacy key has a verified user mapping.

## Integrity Rules

- Foreign keys protect child ownership where a relational parent exists.
- Composite unique constraints protect one care journal per owner and pet;
  medical records use one nullable unique canonical pet foreign key while
  legacy owner/pet keys remain compatible. Other constraints protect one
  engagement per actor/target, one order/review per eligible source,
  and one idempotent external command/event.
- Publication photos use a server-resolved stable key. A unique
  `(photo_asset_id, user_id)` constraint permits one replaceable reaction per
  member/photo, while `(user_id, idempotency_key)` prevents duplicate comments.
- Check constraints are used where supported and portable; enums and
  validation remain the application contract.
- Actor, owner, role, amount, currency, status, moderation, and audit fields are
  excluded from uncontrolled mass assignment.
- Money is stored in minor units.
- Measurements retain exact numeric value plus canonical unit/processed value.
- Timestamps use a normalized instant and carry source timezone where domain
  scheduling or device travel requires it.
- Original device data and corrective human interpretations coexist.
- Adoption applications use an idempotency UUID, a unique case/applicant
  boundary, optimistic lock version, encrypted private screening data, and
  append-only events. Public cases retain only safe animal and placement facts.
- Lost/found cases retain stable public codes and slugs while linking an owned
  pet, global taxon, and domestic classification where known. The encrypted
  case-time animal snapshot preserves historical context without exposing
  private pet data. Exact coordinates, hidden marks, direct contacts, and relay
  messages remain encrypted; public coordinates are rounded.
- Lost/found state changes and archival append immutable events. Reunion and
  archival use optimistic locking. Archival removes public access, stops open
  alerts/tasks/volunteer assignments, and preserves sightings, updates,
  reports, attachments, and identifiers.
- Moderation reports remain distinct from cases. Case/report assignment,
  action, recusal, and appeal transitions run in bounded transactions under
  row locks, retain append-only report events, and never expose reporter
  identity or private operational notes through model serialization.
- Vote and photo-reaction value columns have portable schema-level value
  constraints mirrored by backed-enum casts. Moderation cases carry an
  integer optimistic version and a nullable globally unique closure request
  key; successful closure preserves linked reports and appends their audit
  events without exposing that key.
- Guide translation groups permit only one article per locale. Version numbers,
  collaborator roles, correction review lookup, workflow history, public state,
  and review dates have dedicated compound indexes. Optimistic `lock_version`
  protects content saves, while append-only versions and events preserve
  rollback and transition evidence. Nullable self/source and translator
  foreign keys plus a controlled source enum preserve translation provenance
  without guessing relationships for historical locale families.
- Taxonomy imports remain inactive until a completed validated version is
  explicitly activated; source identifiers never replace stable internal keys.
- Topic lifecycle events are append-only and optionally idempotent. Category
  lifecycle rules are one-to-one. Update requests have scoped idempotency and
  optimistic review versions. Legal holds keep one active key per topic and
  encrypted private reasons. Every nullable actor foreign key has a leading
  index.
- Pet profiles retain permanent `profile_key` identity and legacy owner/slug
  compatibility. A unique creation key prevents replayed creates. Manager
  memberships are unique per pet/user and indexed for current timed access.
  One privacy row, append-only idempotent lifecycle events, retained slug
  aliases, and one current versioned fact per pet/fact key preserve
  authorization, privacy, provenance, and correction history.
- Pet primary media links the shared `content_media_assets` object through
  `pet_profile_media`. Unique upload and nullable current keys enforce replay
  safety and one active primary placement; indexed status/recovery timestamps
  preserve replacement, logical removal, and bounded restoration history.
- New medical records reference one canonical pet profile. Historical owner
  keys do not authorize a linked record after ownership transfer. Explicit
  allergy and medication knowledge states prevent an empty encrypted array
  from being interpreted as proof of absence.

## Query Rules

- Public lists apply visibility/status scopes before pagination.
- Private lists apply authenticated ownership/grant scope before retrieval.
- Detail presenters eager load exactly the relationships they render.
- Use `withCount`, `withExists`, or aggregate subqueries rather than loading a
  relationship only to count it.
- Stable high-volume timelines use `(occurred_at, id)` or analogous composite
  ordering/indexes and cursor pagination where appropriate.
- Explain plans are recorded for measured queries once representative volume
  exceeds the small demo dataset.

## Migration Safety

1. Add compatible nullable/new structure and indexes.
2. Deploy dual-compatible reads/writes when required.
3. Backfill in idempotent bounded batches.
4. Verify counts, nulls, uniqueness, and ownership mapping.
5. Switch authoritative reads/writes.
6. Remove obsolete fields only in a later release with rollback evidence.

Never run `migrate:fresh` on a non-isolated database.

The pet-profile foundation migration and bounded backfill are documented in
`docs/pet-profiles.md`.

## Data Retention

Retention is category-specific:

- audit and safety history: retained according to security/legal policy;
- temporary grants: metadata retained for audit after token expiry/revocation;
- exact GPS, camera media, and temporary location: shortest owner-selected
  period consistent with the feature;
- technical logs: bounded operational period;
- user export/deletion: explicit workflow preserving only legally required or
  safety evidence.

The application must not infer absence of an activity from missing device data.

## Forum Topic Type Definitions

`forum_topic_types` stores configurable definitions under internal IDs and
unique stable keys. System definitions carry EN/LT/RU translation keys, a
positive schema version, a JSON field schema, JSON configuration, moderation
level, and bounded lifecycle capabilities. Repeated synchronization updates
system metadata by stable key without replacing IDs, attached topics, or
administrator-created non-system definitions.

`forum_topics.forum_topic_type_id` is the normalized relation. Type-specific
data uses `structured_data` plus `structured_data_version`; the schema does not
grow one nullable column for every possible topic type. High-value domains use
dedicated tables when ownership, privacy, lifecycle, or queryability requires
normalization. Every generic topic create/update stores the resolved active
definition version and stable type key. The typed runtime registry enforces
location/species requirements, attachment classes, answer ratings,
accepted-answer availability, and notification levels while retaining
expiration, archival, contact, SEO, and lifecycle metadata in the versioned
definition.

## Community Review Tables

`forum_review_panels` and `forum_review_assignments` store bounded low-risk
review work. Assignment uniqueness, active-panel uniqueness, state/deadline
indexes, and leading foreign-key indexes protect concurrent selection and
bounded queues. `forum_review_panel_events` is append-only.

`forum_community_notes` stores the current contextual-note projection with
optimistic `lock_version`, publication/revalidation dates, controlled subject
type, and moderator decision. `forum_community_note_versions` is append-only
and unique by note/version. The migration is additive and does not rewrite
legacy forum rows.

## Mentorship Tables

- `forum_mentor_profiles`: one per user; opt-in state, public-safe summary,
  locales, broad location, platform communication, capacity, acknowledgement,
  and optimistic lock.
- `forum_mentor_scopes`: independent type with optional category/taxon and a
  stable unique scope key.
- `forum_mentorships`: request/lifecycle projection with participant foreign
  keys, unique idempotency/open keys, acknowledgements, timestamps, and
  optimistic lock.
- `forum_mentorship_messages`: append-only participant messages with globally
  unique idempotency keys.
- `forum_mentorship_feedback`: one append-only record per mentorship/author.
- `forum_mentorship_events`: append-only lifecycle evidence.

Messages, feedback, and events use restrictive parent foreign keys so direct
deletion cannot erase an audit-bearing mentorship. All list and thread indexes
begin with their filtering foreign key. See `docs/mentorship.md`.

## Persistent Group Tables

- `forum_groups`: stable identity, owner, system flag, localized metadata,
  visibility/status, generalized location, bounded questions, counter, and
  optimistic version.
- `forum_group_memberships`: one group/user projection with role, state,
  answers, review/restriction context, idempotency, and optimistic version.
- `forum_group_invitations`: expiring invitation with pair-level open key and
  command idempotency.
- `forum_group_events`: append-only actor/subject audit evidence with optional
  idempotency.
- `forum_group_taxon`: unique relation to the reusable global taxonomy.

Discovery, owner/status, location, membership state/role, invitation state and
expiry, audit timeline, and reverse taxon paths have explicit compound
indexes. Restrictive foreign keys prevent silent audit or membership loss.
See `docs/groups.md`.

## Group Content And Poll Tables

- `forum_topics.forum_group_id` and
  `knowledge_articles.forum_group_id` are nullable indexed foreign keys; a
  grouped record retains its original identity.
- `forum_group_activities`, `forum_group_announcements`, and
  `forum_group_files` use group-first list indexes and restrictive ownership
  references.
- `forum_polls` stores typed visibility, eligibility, editability, closure,
  aggregate count, and optimistic version.
- `forum_poll_options` is unique by poll/stable key and poll/position.
- `forum_poll_votes` is unique by poll/user and by idempotency key. Ordered
  choices are validated before persistence; option counters are maintained in
  the same locked transaction.

The additive migration creates no trigger, raw SQL, destructive backfill, or
automatic legacy classification. See `docs/polls.md`.

## Forum Journal Tables

- `forum_journals` is unique by topic, stable key, and creation idempotency
  key; owner/type/status list paths have leading compound indexes.
- `forum_journal_entries` owns dated entry, milestone, or setback prose and an
  optimistic version. Stable and idempotency keys are unique.
- `forum_journal_measurements` stores one validated decimal metric per
  entry/key with a canonical unit and display position.
- `forum_journal_entry_versions` stores immutable prior snapshots with a
  unique monotonically increasing entry/version pair.
- `forum_journal_collaborators` stores one active or revoked viewer/editor
  projection per journal/user.
- `forum_journal_media` stores private generated paths, encrypted original
  names, actual MIME, checksum, size, text alternatives, and lifecycle state.
- `forum_comments.forum_journal_entry_id` reuses the established comment
  entity; the journal comment path has an optional unique idempotency key.

All schema changes are additive, use foreign keys and bounded-query indexes,
and avoid raw SQL, triggers, destructive legacy classification, or a
cross-domain transaction with external I/O. See `docs/journals.md`.
## Organization Authority Tables

- `organizations`: stable key, slug, creation idempotency, owner, localized
  defaults, lifecycle, verification, suspension, and optimistic version.
- `organization_memberships`: unique organization/user role projection with
  invitation/removal attribution, expiry, and optimistic version.
- `organization_invitations`: stable identity, unique request and token hash,
  account/role binding, expiry, response, and revocation attribution.
- `organization_restrictions`: one independently timed capability restriction
  per idempotent operation with apply/revoke attribution.
- `organization_audit_events`: append-only actor/subject, event, reason,
  translation-key, encrypted metadata, and optional unique request evidence.
- `forum_events.responsible_organization_id`: nullable restrictive foreign key
  preserving legacy event ownership while enabling a real tenant authority.

Unique constraints protect stable/request/token/membership identities.
Compound indexes cover tenant membership, invitation state/expiry,
restriction capability windows, audit timelines, and organization event
discovery. Every foreign key has a leading index. The migrations are additive
and contain no ownership inference or destructive backfill.

## Event Tables

The additive event schema consists of `forum_events`,
`forum_event_registrations`, `forum_event_invitations`,
`forum_event_updates`, `forum_event_messages`, `forum_event_reviews`,
`forum_event_history`, and `forum_event_taxon`.

Unique constraints protect stable keys, creation/action idempotency, one
registration and one review per event/user, one invitation per event/user,
and one taxon link. Compound indexes cover visible schedules, group schedules,
registration capacity/waitlist ordering, invitation status/expiry, message and
update timelines, review status, and append-only history. Every foreign key
has a leading index. The migration is additive and links group activities
through a nullable `forum_event_id`.

Exact location, online URL, emergency plan, attendee notes, invitation
messages, and private review feedback use encrypted casts. See
`docs/events.md` for lifecycle and recovery.

## Place And Venue Authority Tables

- `places` owns one stable place identity, owner or responsible organization,
  lifecycle, visibility, locale, public region/contact facts, encrypted exact
  location, factual verification/accessibility states, and optimistic version.
- `venues` provides the event-operational capacity, timezone, confirmation,
  species allocation, and encrypted staff contact/rules for one place.
- `venue_areas` defines typed rooms and zones with separate human, animal,
  species, accessibility, and private-instruction boundaries.
- `place_access_grants` stores account-, purpose-, event-, and time-bound exact
  location permission; `place_access_audits` records each reveal.
- `place_location_versions` preserves encrypted material location history.
- `place_questions` stores one canonical actor-attributed, idempotent question
  with a stable public key and explicit open/answered/hidden/closed status.
- `place_question_answers` enforces one official answer per question and keeps
  its manager author, stable key, idempotency key, body, and answer time.

`forum_events` and `forum_event_occurrences` reference the canonical place and
venue. `forum_event_rooms` may reference a venue area. Exact addresses are not
copied into event snapshots or public projections. Compound indexes cover
public catalogue, owner/organization access, grant windows, event linkage,
venue capacity, audit history, and location versions.
Question indexes cover place/status timelines and author history; unique
constraints protect question and answer idempotency plus the one-answer
boundary.

## Social Relationship Tables

- `social_actors`: one adapter per authoritative user, pet, expert, or group;
  actor-key/type/status discovery paths are indexed.
- `social_actor_settings`: one optimistic settings row per actor.
- `social_relationship_requests`: directed proposal lifecycle with source and
  target actors, real user attribution, expiry/cooldown, unique idempotency,
  nullable unique active key, encrypted optional message, normalized message
  fingerprint, risk signals, recipient stop-repeat decision, and bounded
  inbox/outbox/account-window indexes.
- `social_account_blocks`: directed real-account safety boundary with current
  initiating actors, all-managed-profiles scope, nullable unique active key,
  unique idempotency, optimistic version, and creator/revoker attribution.
- `social_relationships`: active or historical typed edge with direction,
  source/target, real creator/acceptor, optional request/context, optimistic
  version, expiry, unique idempotency, and nullable unique active key.
- `social_relationship_events`: append-only request/relationship/account-block
  evidence with actor snapshots, status transition, reason, timestamps,
  encrypted private metadata, and globally unique idempotency key.

Foreign keys preserve endpoint and actor attribution. The migration is
additive and does not rewrite encrypted prototype social state. See
`docs/social-relationships.md`.

## Expert Session Tables

The additive expert-session schema consists of `forum_expert_sessions`,
`forum_expert_session_questions`, `forum_expert_session_answers`,
`forum_expert_session_corrections`, and `forum_expert_session_history`.

Unique constraints protect stable and idempotency keys, queue position within
a session, one answer per question, and one immutable correction version per
answer. Compound indexes cover public schedule discovery, queue state/order,
answer lookup, and append-only history. Restrictive foreign keys preserve
professional, author, and audit ownership. See
`docs/expert-question-sessions.md`.
