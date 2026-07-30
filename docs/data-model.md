# Data Model

## Storage Baseline

- 68 migrations created 71 SQLite tables at baseline; the modernized schema has
  76 migrations and 74 tables after additive identity, care-sync, social-state,
  device-lifecycle, retention, event-grouping, and index work.
- Tests use isolated in-memory or temporary SQLite.
- Schema and Eloquent queries remain portable unless an ADR explicitly accepts
  a production-engine-specific optimization.
- No first-party database views, triggers, or raw SQL are active.

## Table Groups

| Domain | Tables |
| --- | --- |
| Framework | `users`, password reset, sessions, cache/locks, jobs/batches/failed jobs |
| Social identity/state | `pet_profiles`, encrypted/versioned `user_domain_states` |
| Forum | topics, answers, comments, votes, engagements, blocks, reports, notifications |
| Knowledge | articles, versions, corrections |
| Experts | profiles, credentials, services, availability slots, bookings, document grants, consultations, publications, reviews, engagements, reports |
| Marketplace | listings, listing engagements, reservations, listing reports, orders, disputes, listing reviews |
| Lost/found | cases, sightings, sectors, tasks, volunteers, updates, alerts, reports |
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
