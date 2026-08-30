# Factories And Seeding

## Current Gate State

Factory and representative-seed completeness is currently **not verified**.
The final release audit discovers 262 persistent model classes;
`generate-seeding-coverage.php --check` reports 25 missing factories, and the
database-domain audit reports 51 models absent from the representative
manifest. The committed `docs/seeding-coverage.md` and older 204-model counts
are therefore historical evidence, not current completion proof.

Generic enum substitution is deliberately not treated as a valid domain
state: lifecycle variants require invariant-aware named helpers and focused
assertions. Regenerate the matrix with
`php scripts/generate-seeding-coverage.php --write` and require exact byte
parity with `php scripts/generate-seeding-coverage.php --check`. Running the
generator without an option prints the prospective document to standard output
without changing the repository.

## Seeder Layers

1. Reference data: stable, deterministic, idempotent configuration/domain
   records.
2. Roles and permissions: idempotent and environment-independent.
3. Development/demo: realistic graph, local/demo/testing only.
4. Test support: minimal focused fixtures invoked explicitly.
5. Performance volume: opt-in and never part of normal `DatabaseSeeder`.

`DatabaseSeeder` orchestrates a safe order and may be run repeatedly.
`PerformanceSeeder` creates 250 deterministic pet profiles only when invoked
explicitly in a local, demo, or testing environment.

The deterministic `demo-unverified` identity remains pending when configured
email verification is enabled. When the mode is disabled, root seeding stamps
that identity as verified so repeat seeding cannot recreate an active pending
account. This timestamp is an operational bypass marker, not independent proof
of email ownership.

## Historical Representative Dataset Contract

The earlier 204-model checkpoint recorded that `DatabaseSeeder` created
exactly ten users and at least ten records for each model then in its manifest.
That checkpoint does not cover the newly discovered models. The first
user is the deterministic `user@example.com` identity with actor key
`mia-carter`; it has verified, localized account data and connected pet,
social, organization, forum, mentorship, adoption, lost-pet, medical, care,
expert, marketplace, place, and device records. Identity synchronization
refuses an actor-key/email conflict instead of overwriting either account.

`RepresentativeDomainSeeder` reads the canonical
`RepresentativeModelManifest` and tops up only models below the target count.
It reuses bounded parent pools, allocates unique relationship parents where the
schema requires them, applies model-specific invariant overrides, and never
deletes records merely to reach an exact count. A final set of idempotent
foundation/backfill passes completes derived relationships introduced by the
representative factories on the first root-seed run.

`RepresentativeFieldCoverageSeeder` then updates only named deterministic
demo aggregates (stable email, actor key, slug, reference, UUID, or stable
key) and creates two natural-keyed order lifecycle examples. It never selects
or rewrites an arbitrary first row. This layer supplies representative
nullable lifecycle values and non-empty structured payloads while preserving
unrelated records and owner/buyer/seller coherence.

Calling `make()` on a default Laravel factory may persist a required
relationship parent because Laravel expands `belongsTo` factories during
attribute resolution. Focused tests instead guarantee that `make()` never
writes to, transitions, or replaces an explicitly supplied aggregate; this is
the boundary needed to prevent hidden mutation of caller-owned records.

All eight application-owned pivot tables receive at least ten connected rows,
including their timestamps and meaningful position, status, role, consent,
eligibility, taxonomy-context, or snapshot metadata. The clean-seed tests cap
non-reference model growth and the complete model-plus-pivot graph so factory
relationships cannot silently multiply into an unbounded dataset.

Forum reference seeding is split into deterministic category, topic-type,
reputation, moderation, taxonomy-source, core-taxonomy, and community-group
seeders. Adoption case synchronization is production-safe and non-destructive;
the private sample application remains development/demo/testing only. The demo
organization provider reaches verified state only through the same submitted,
reviewed, and approved credential action used by the application; repeated
seeding reuses its deterministic profile and credential without duplicating
audit events.
Lost/found integrity backfill resolves only exact owner/profile matches,
preserves existing IDs and content, creates missing immutable creation events
idempotently, and leaves ambiguous animal identity for review.
`CollaborativeGuideDemoSeeder` is a demo-only repair/synchronization layer for
the two stable knowledge examples. It assigns deterministic translation groups,
discussion links, maintainers, version snapshots, and immutable creation events
without replacing IDs or user content. Stable uniqueness makes reruns
idempotent. Direct invocation fails closed before any query unless the current
environment is explicitly allowed.
`MentorshipDemoSeeder` is environment-gated and reuses the audited trust and
mentorship Actions. It synchronizes one opted-in mentor profile, two
independent scopes, one accepted private mentorship, and one message from each
participant. Stable request/message idempotency keys prevent duplicates on
rerun.
`ForumGroupDefinitionSeeder` synchronizes six system-managed group definitions
by stable key without replacing administrator-created groups or IDs.
`ForumGroupDemoSeeder` is environment-gated and creates owner, member, private,
unlisted, invitation, and request examples without production identities. It
also creates one member topic, guide, activity, announcement, private fixture,
and single/multiple/ranked poll examples after the base forum graph exists.
Repeated runs preserve memberships, content IDs, private paths, poll rows, and
append-only evidence through stable slugs and idempotent Actions.
`ForumJournalBackfillSeeder` is production-safe and converts only explicitly
journal-typed topics into the normalized one-to-one extension. It preserves
topic identity and marks missing/invalid subtype metadata for review.
`ForumJournalDemoSeeder` is environment-gated and uses deterministic
production Actions to create representative types, entries, metrics,
milestones, setbacks, collaborators, and comments without duplicating records
on rerun.
`OrganizationAuthoritySeeder` is environment-gated and reuses production
Actions to create one verified rescue, one community with a pending
account-bound invitation, and one suspended venue. Stable organization,
invitation, restriction, and audit request identities keep repeat execution
at three organizations, four memberships, one invitation, nine restrictions,
and six audit events.
`PlaceAuthoritySeeder` first creates the public, organization, and protected
place/venue examples through production Actions. `PlaceDemoSeeder` then
synchronizes the twelve localized directory fixtures by stable key without
replacing IDs or user-created places. Repeated seeding preserves the canonical
creation idempotency keys used by event/location authority workflows.
`DiscoveryDemoSeeder` creates its publications through the production Action.
The Mia-authored `page-identity-demo-post` has a stable route key and
idempotency key so the disposable authenticated browser matrix can verify a
real authorized content-detail deep link even when representative social
blocks correctly remove other actors' publications from Mia's feed.

## Production Safeguards

- No truncation.
- No `migrate:fresh`.
- No demo accounts or fixed demo credentials are created in production.
- No real personal data.
- No public internet downloads.
- No destructive reset hidden inside a seeder.
- Seeded marketplace records synchronize by stable slug and preserve IDs on
  rerun; adoption cases synchronize by existing listing ID. The marketplace
  demo seeder independently fails closed unless the current environment is in
  the configured demo-seed allowlist, even when it is invoked directly.
- Adoption demo and collaborative-guide demo seeders independently apply the
  same fail-closed allowlist before reading or mutating records, so invoking a
  child seeder directly cannot bypass `DatabaseSeeder`.
- Collaborative demo guide synchronization targets only two fixed demo slugs
  and runs only after the environment-gated demo users and forum graph exist.
- Mentorship demo synchronization runs only in configured demo environments
  and never writes fake mentorships from production-safe definition seeders.
- Group definitions update only system-managed metadata; group demo records
  are prohibited outside local, demo, and testing environments.
- Group demo files use the private configured disk and are never created by
  the production-safe definition seeder.
- Journal backfill never infers a type from prose, and journal demo content is
  prohibited in production.
- Organization demo identities must already exist in an explicitly allowed
  environment; organization ownership is never inferred from display text,
  email domains, event creators, or marketplace activity.
- Place demo synchronization never publishes protected exact coordinates and
  never recreates an intentionally deleted user-created place.

## Coverage Matrix

The detailed per-model matrix is generated and maintained in
`docs/seeding-coverage.md`, with:

- model;
- factory;
- meaningful states/helpers;
- reference/demo seeder;
- local fixture;
- tests;
- precise exemption.

## Required Tests

- `FactoryAndSeederTest` persists every default factory and every supported
  zero-argument helper/state;
- `CompleteDatabaseSeederTest` must reconcile the manifest with current runtime
  discovery, enforces the 10-record minimum and graph budgets, checks the
  deterministic user and major relations, verifies pivot metadata and private
  files, derives orphan checks from every schema foreign key, and proves a
  second root-seed run preserves model and pivot counts;
- `SeededFieldCoverageTest` rejects null required columns, requires either a
  representative non-null value or an exact schema-qualified domain reason
  for every nullable column, requires non-empty representative structured
  payloads, and proves unrelated rows survive the coverage pass;
- relationship suites verify both sides of schema-derived associations and
  custom pivot casts/metadata;
- unique constraints, foreign keys, and the conflicting deterministic-email
  fail-closed path remain valid;
- fresh isolated migration plus root seed succeeds;
- production safeguard prevents demo identity creation;
- primary pages render from seeded data;
- seeded private files exist on the selected fake disk.

Fresh verification uses:

```bash
php scripts/verify-fresh-database.php
```

The script creates and asserts a system temporary SQLite path before invoking
`migrate:fresh --seed`, then repeats `db:seed` and compares stable counts. The
root-seed, field-coverage, foreign-key, and pivot checks likewise run against an
isolated testing database; they must never target a configured production or
shared development database.

`DatabaseSeeder` deliberately uses model factories for its guarded demo graph,
so `fakerphp/faker` is a runtime Composer dependency. This keeps explicitly
allowed local, demo, and testing seed operations functional after
`composer install --no-dev`; it does not permit demo seeding in production.

## Demo Accounts

The primary deterministic demo identity is `user@example.com`; the remaining
demo identities use `*.example.test` addresses. Development passwords are
stored through the application's normal password hashing boundary and these
accounts may exist only when `APP_ENV` is `local`, `demo`, or `testing`.
Production attempts must fail closed.

## Event Seeders

`ForumEventBackfillSeeder` is production-safe, additive, and rerunnable. It
preserves legacy stable keys and links existing group activities without
rewriting registrations or user content. `ForumEventDemoSeeder` is restricted
to local, demo, and testing environments and creates deterministic end-to-end
event states through production Actions.

Every event model has a factory. The event workflow test creates all seven
factory-backed models, and the shared factory/seeder suite validates schema
constraints plus repeat execution.

## Expert Session Seeders

`ForumExpertSessionBackfillSeeder` is production-safe and does not infer a
session from prose or another domain. `ForumExpertSessionDemoSeeder` is
environment-gated and creates a deterministic verified host, credential,
session, moderated question, and answer. Repeated execution preserves stable
session and credential identities. Every new model has a valid factory and
meaningful lifecycle states.

## Topic Lifecycle Seeders

`ForumTopicLifecycleBackfillSeeder` is production-safe and runs after category
definitions and again after legacy demo topics are synchronized. It processes
topics with `chunkById`, fills only missing lifecycle timestamps, creates one
stable baseline event per topic, and creates missing category rules without
overwriting administrator-owned rows.

`ForumTopicLifecycleDemoSeeder` is local/demo/testing only. It creates
deterministic outdated, archived, restored, and legal-hold examples through
the production lifecycle boundary. The four lifecycle child models have
factories and meaningful states. Fresh seed and repeat `ForumSystemSeeder`
execution are verified against a temporary SQLite database.

## Place Submission Demo Scenarios

`PlaceSubmissionDemoSeeder` is restricted to configured local, demo, and test
environments. It deterministically creates twenty connected submissions across
submitted, duplicate-review, needs-information, approved, rejected, published,
withdrawn, existing-link, merge, and restored histories. Every new persistent
model has a bounded factory; ten merge/restore scenarios retain redirect and
copied-fact provenance. `user@example.com` owns a navigable scenario. Root and
repeat seeding retain ten users and stable identities without truncation.
