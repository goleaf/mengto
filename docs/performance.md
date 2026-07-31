# Performance

## Baseline

- Full Pest suite: 4.18 seconds for 116 tests.
- Vite production build: 458 milliseconds.
- Tailwind CSS: 39.10 kB, 7.59 kB gzip.
- SCSS CSS: 249.90 kB, 31.90 kB gzip.
- JavaScript: 9.19 kB, 3.03 kB gzip.
- Application routes: 124.

The small seeded database is not representative enough for broad latency
claims. Query budgets are added to critical flows with deterministic fixtures.

## Final Measured State

- Full Pest suite: 1,263 tests and 46,373 assertions in 58.845 seconds
  serially.
- Vite 8.2 production build: passed.
- Font CSS: 1.31 kB, 0.32 kB gzip.
- Tailwind application CSS: 50.79 kB.
- Retained semantic SCSS CSS: 250.28 kB.
- JavaScript: 12.31 kB.
- Application routes: 154.

The Tailwind increase is attributable to localized authentication, responsive,
offline, reduced-motion, and forced-colors states. The larger semantic SCSS
bundle is unchanged and remains a measured incremental-migration boundary, not
an unreviewed rewrite target.

## Budgets

- No N+1 on critical list/detail pages.
- No unbounded production collection.
- No query inside a render loop or Blade.
- Livewire snapshots contain only required scalar/small state.
- A single small interaction does not rerender unrelated expensive regions.
- Images have dimensions and card-sized variants.
- Build size regressions greater than 10% require explanation.

## Measurement Workflow

1. Record query count and duration with a representative fixture.
2. Fix scopes/eager loads/indexes before adding cache.
3. Inspect an explain plan for important high-volume queries.
4. Measure Livewire requests and snapshot payload for converted components.
5. Record before/after asset sizes from Vite.
6. Add a stable test or documented manual benchmark.

## Query Budgets

`tests/Feature/CareJournalTest.php` verifies that the care journal detail
remains at or below 12 queries as timeline fixtures grow.
`tests/Feature/SmartDeviceTest.php` verifies bounded directory and detail query
counts as readings and events grow. These tests use deterministic SQLite
fixtures and are regression budgets, not production latency claims.
`tests/Feature/Forum/MentorshipWorkflowTest.php` verifies that the composed
mentorship page remains at or below 45 queries with multiple mentors, scopes,
requests, and messages.

All previously uncovered foreign keys gained leading indexes. The deterministic
performance seeder supports repeatable local growth tests; production latency
and explain-plan evidence must still be measured against the selected
deployment database before a capacity change.
