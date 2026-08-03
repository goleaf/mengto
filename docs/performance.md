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

## Latest Measured State

- Latest recorded full Pest suite: 2,027 tests and 72,581 assertions in
  115.528 seconds after the canonical medical patient-identity/access package.
- Vite 8.2 production build: passed.
- Font CSS: 1.31 kB, 0.32 kB gzip.
- Tailwind application CSS: 51.15 kB, 9.63 kB gzip.
- Retained semantic SCSS CSS: 264.61 kB, 35.00 kB gzip.
- Application JavaScript: 33.04 kB, 10.36 kB gzip.
- PhotoSwipe JavaScript chunk: 58.82 kB, 17.06 kB gzip.
- Application routes: 156 of 170 total.

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
`tests/Feature/Forum/GroupCoreWorkflowTest.php` verifies bounded group
directory and management query counts with memberships, invitations, events,
owners, and taxonomy focus present. Group discovery paginates, selects
presentation columns, and eager loads constrained relations. The global
taxonomy tree is never serialized into Livewire state.
`tests/Feature/Forum/GroupContentAndPollWorkflowTest.php` compares the
class-based group-content workspace with one and ten polls. Eager-loaded
options/current votes and one precomputed trust decision keep query growth
constant rather than multiplying policy and result lookups per poll.
`ForumJournalDirectory` paginates journals and eager loads only topic
presentation fields. `ForumJournalTimeline` caps entries and chart points,
loads comments/measurements/media/collaborators in bounded projections, and
never serializes the full taxonomy or private storage data. Metrics are
aggregated from normalized selected columns; no query runs in Blade.
`OrganizationDirectory` scopes before pagination, selects presentation
columns, and computes active membership counts in the organization query. The
unused owner eager load was removed, reducing the directory data path from
three statements to two. Its executable budget keeps twelve organizations to
at most one statement above a single organization. Organization workspaces
cap memberships at 100 and active restrictions at 50 and load private
projections only for authorized managers.
`PlaceCatalog` scopes persisted place authority before presentation, selects
only public columns, constrains organization memberships to the current
account, and reuses one request-local keyed projection for list and detail
lookups. `PlaceAuthorityFoundationTest` caps the catalogue at four statements
for one accessible place and permits at most one additional statement when
the fixture grows to twelve; `PlaceDirectoryTest` independently holds the
rendered directory to the one-place baseline plus one statement after adding
30 records. Exact address, coordinates, private instructions, grants, and
audits are absent from both selected columns and shared caches.

All previously uncovered foreign keys gained leading indexes. The deterministic
performance seeder supports repeatable local growth tests; production latency
and explain-plan evidence must still be measured against the selected
deployment database before a capacity change.
## Event Query Contract

Event discovery applies visibility and date filters before pagination, selects
only presentation columns, eager loads bounded taxonomy context, and computes
registration counts/review averages in SQL. Detail queries load each required
relation once. Capacity and waitlist mutations lock one event row and the
small relevant registration set rather than scanning the catalogue.

No event-detail cache is introduced because protected access depends on user,
invitation, group, and registration context. Stable taxonomy and category
caches remain owned by their existing domains.

## Expert Session Query Contract

Directory queries apply publication, archive, search, scope, and schedule
filters before bounded pagination. Workspace queries select presentation
columns and load host, queue author, answer, and correction data in bounded
relations. Pending question visibility is resolved before presentation.

Candidate host profiles eager-load credentials once, and eligibility reuses
the loaded relation. No session-detail cache is introduced because queue
visibility depends on author, current credential, and moderation context.

## Topic Lifecycle Query Contract

The read-time projection performs one indexed category-rule lookup and no
write. History and request queries select only presentation columns and use
configured limits of 20. The topic ID, event timeline, requester timeline,
state/age, legal-hold, and redirect indexes match the implemented access
patterns; every new foreign key has a leading index.

The Livewire panel holds scalar/form state only and does not serialize topic
models or relationship graphs. The browser check found no horizontal overflow
at 375px. After integration with the current public-image implementation, the
Vite 8.2 build contains 50.35 kB Tailwind CSS, 263.56 kB semantic CSS,
30.02 kB application JavaScript, and a separate 58.82 kB PhotoSwipe chunk
before gzip. The accessibility adapter accounts for the current application
JavaScript increase and performs one document observer plus idempotent
initialization rather than one listener per form or Livewire component.
