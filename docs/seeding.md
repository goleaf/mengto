# Factories And Seeding

## Baseline

All 66 first-party Eloquent models now have a verified model factory. The
generated matrix records 23 explicit helper states and 412 enum-backed states;
valid existence alone is not accepted without persistence tests.

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

## Production Safeguards

- No truncation.
- No `migrate:fresh`.
- No demo accounts or fixed demo credentials are created in production.
- No real personal data.
- No public internet downloads.
- No destructive reset hidden inside a seeder.

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
