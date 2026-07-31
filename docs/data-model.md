# Data Model

## Storage Baseline

- 68 migrations created 71 SQLite tables at baseline; the current additive
  schema has 92 migrations and 140 tables after identity, care-sync,
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
| Social identity/state | `pet_profiles`, encrypted/versioned `user_domain_states` |
| Forum | topics, answers, comments, votes, engagements, blocks, polymorphic reports, report events/evidence, categories/translations/aliases/redirects, topic definitions, reputation/trust/badges, confirmations, moderation cases/actions/appeals/recusals, notifications, persistent groups/memberships/invitations/events/taxon links |
| Animal taxonomy | versioned sources/imports/versions/issues, taxa, names, external identifiers, change history, domestic classifications, breed registries, community groups |
| Knowledge | articles, append-only versions, corrections, normalized collaborators, append-only workflow events |
| Experts | profiles, credentials, services, availability slots, bookings, document grants, consultations, publications, reviews, engagements, reports |
| Marketplace and adoption | listings, listing engagements, reservations, compatibility listing reports, orders, disputes, listing reviews, adoption cases, encrypted applications, append-only adoption events |
| Lost/found | cases, sightings, sectors, tasks, volunteers, updates, alerts, reports, immutable case events, encrypted contact relays |
| Care | journals, routines, tasks, entries, media, access grants |
| Medical | records, events, vaccinations, weights, medications, doses, documents, reminders, access grants |
| Devices | devices, assignments, readings, events, commands, safe zones, automations, runs, access grants, lifecycle records |
| Cross-domain | audit logs |

## Identity Strategy

The existing schema uses human-readable actor keys. `users.actor_key` becomes
the unique authoritative bridge without destructive rewrites. New ownership
foreign keys may be added later through expand-and-contract only after every
legacy key has a verified user mapping.

## Integrity Rules

- Foreign keys protect child ownership where a relational parent exists.
- Composite unique constraints protect one care/medical record per owner and
  pet, one engagement per actor/target, one order/review per eligible source,
  and one idempotent external command/event.
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
- Guide translation groups permit only one article per locale. Version numbers,
  collaborator roles, correction review lookup, workflow history, public state,
  and review dates have dedicated compound indexes. Optimistic `lock_version`
  protects content saves, while append-only versions and events preserve
  rollback and transition evidence.
- Taxonomy imports remain inactive until a completed validated version is
  explicitly activated; source identifiers never replace stable internal keys.

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
