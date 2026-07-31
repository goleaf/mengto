# Factories And Seeding

## Baseline

All 126 first-party Eloquent models now have a model factory and are guarded by
an architecture test. The generated matrix records 723
documented and enum-backed state cases; valid existence alone is not accepted
without persistence tests.

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
idempotent.
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

## Production Safeguards

- No truncation.
- No `migrate:fresh`.
- No demo accounts or fixed demo credentials are created in production.
- No real personal data.
- No public internet downloads.
- No destructive reset hidden inside a seeder.
- Seeded marketplace records synchronize by stable slug and preserve IDs on
  rerun; adoption cases synchronize by existing listing ID.
- Collaborative demo guide synchronization targets only two fixed demo slugs
  and runs only after the environment-gated demo users and forum graph exist.
- Mentorship demo synchronization runs only in configured demo environments
  and never writes fake mentorships from production-safe definition seeders.
- Group definitions update only system-managed metadata; group demo records
  are prohibited outside local, demo, and testing environments.
- Group demo files use the private configured disk and are never created by
  the production-safe definition seeder.

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

- every model factory creates one valid record;
- every documented state creates a valid record;
- relationships and unique/foreign constraints remain valid;
- fresh isolated migrate plus seed succeeds;
- repeated fixed/full seed is idempotent;
- production safeguard prevents demo identity creation;
- primary pages render from seeded data;
- seeded private files exist on the selected fake disk.

Fresh verification uses:

```bash
php scripts/verify-fresh-database.php
```

The script creates and asserts a system temporary SQLite path before invoking
`migrate:fresh --seed`, then repeats `db:seed` and compares stable counts.

## Demo Accounts

Documented demo identities use `*.example.test` addresses and an explicit
non-production password. They may exist only when
`APP_ENV` is `local`, `demo`, or `testing`. Production attempts must fail
closed.
