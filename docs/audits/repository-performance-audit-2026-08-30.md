# Repository Performance Audit — 2026-08-30

## Scope and method

This measured audit covers directories, feeds, search, dashboards, private
workspaces, calendars, message lists, Places, Events, care and medical
timelines, device screens, exports, seeders, generators, browser assets,
caching, runtime operations, and correlation metrics. Query, index, Livewire,
cache, asset, runtime, and observability specialists worked read-only; the
principal reproduced and dispositioned their findings in the live main
checkout.

Deterministic factories and isolated SQLite were used for request/query and
payload regressions. Wall time and peak-memory delta are recorded observations,
not portable CI assertions. Query counts, result cardinality, serialized bytes,
stable ordering, index shape, visibility isolation, and cache behavior are
executable budgets.

## Confirmed and corrected

| Path | Before | After and budget |
| --- | --- | --- |
| Place name directory | PHP filtering/sorting/slicing after a 500-row catalogue cap; page 101 omitted valid rows | Database filters and stable name/id pagination; 613 public rows plus one private sentinel, 4 queries, 99,628 bytes, 6,291,456 peak-memory-delta bytes, 146.82 ms; budgets 8 queries and 196,608 bytes |
| Nearby place search | Loaded the complete canonical catalogue before category selection | Category predicate and limit execute in the database |
| Lost/found coordination | Six operational relations were unbounded; 125 sightings serialized to 95,395 bytes in 116.10 ms | Operational collections cap at 100 and history at 50 with stable tie-breakers; 77,051 bytes, 85.74 ms, 10 queries, 4,194,304 peak-memory-delta bytes |
| Care, medical, device directories | Timestamp-only simple pagination could reorder equal timestamps | Timestamp plus id ordering is enforced and inspected in SQL |
| Forum journal timeline | Paginated parents retained unbounded comments, measurements, media, and collaborators; 75 comments projected to 11,759 bytes in 83.29 ms | Limits are 50/20/10/100; 8,083 bytes in 44.84 ms and 131,072-byte budget |
| Forum journal export | Parent chunks eagerly accumulated all child graphs and query count grew by chunk; 125 entries used 8 queries | Merge-streamed parent and child cursors preserve all 125 entries in 4 queries; budget 5 queries; observed 90,737 bytes and 30.41 ms versus 89,037 bytes and 23.61 ms before |
| Forum category cache | Locale-only key leaked member/restricted/hidden categories and regeneration could stampede | Version 4 locale plus audience keys, explicit visibility filters, all-audience invalidation, 10-second lock/2-second wait, zero-SQL warm read, source fallback |
| Lost/found statistics cache | Global sightings and volunteers included private cases; child writes did not invalidate | Version 2 public-only aggregates, child invalidation, stampede lock, source fallback, isolation tests |
| Listing and topic-schema caches | Regeneration had no stampede boundary or unavailable-store behavior | Versioned/public keys retain mutation invalidation, add 10-second lock/2-second wait and bounded source fallback; listing cold/warm source queries are 6/0 |
| Request observability | Correlation and timing began after portal/auth/binding middleware; health and early redirects lacked IDs; stream work was excluded | First global middleware owns IDs and timing; route/actor context exists before application code; health/redirect/404/500 coverage; streamed success/failure outcomes; atomic 60/minute route/status cap; invalid thresholds disable |
| Browser images | 23 of 35 direct image elements omitted intrinsic dimensions | Every direct first-party image declares width and height; representative directory cards use the responsive-image component |

Seven additive indexes were accepted only after query-pattern and duplication
review: care entries by journal/started/id, device readings and events by
device/time/id, public search-case directory status/visibility/archive/time/id,
audit target/time/id, forum-event messages by event/id, and groups by
status/update/id. SQLite EXPLAIN selects each named index without a temporary
sort, and the migration down/up test is green.

## Measured non-findings and rejected speculation

- No Model::all call exists in first-party application code.
- No query, model access, service call, or lazy relationship was found in
  Blade. No per-row aggregate N+1 was reproduced on the audited list/detail
  paths.
- Existing content feeds use stable cursor pagination. Representative event,
  group, organization, care, medical, device, expert, profile, discovery, and
  message paths already have deterministic bounded-query tests; no cache was
  added to mask their query shape.
- The representative maximum-valid ManagePetProfile state measured 58,579
  initial HTML bytes, 18,406 snapshot bytes, and 58,161 update bytes against
  budgets of 196,608, 24,576, and 65,536. No public Eloquent graph or confirmed
  duplicate interaction was found, so no Livewire rewrite was justified.
- A guest login browser trace made five unique requests with no duplicate URL.
  PhotoSwipe remains one dynamic chunk. An authenticated wire:navigate network
  trace was not available and is not reported as verified.
- Fresh runtime probes of the database verification and audit-generator failure
  paths returned exit code 1, so the specialist's earlier exit-zero concern was
  not reproducible in the current checkout and no speculative script change
  was made.

## Asset and runtime disposition

The focused isolated Vite build completed in 711 ms and the final build in
748 ms. Raw/gzip sizes were 1.31/0.32 kB
font CSS, 52.77/9.98 kB Tailwind CSS, 311.03/40.25 kB semantic SCSS,
35.09/10.85 kB application JavaScript, and 58.83/17.06 kB PhotoSwipe.
Semantic SCSS is 17.5% raw and 15.0% gzip above the preceding record because
all 29 active compatibility partials remain global. No duplicated bundle or
route-safe split was proved, so active feature styles were not deleted merely
to improve a number.

The forum requirements generator checked 38,377 records in 7.28 seconds with
388,120 KiB maximum RSS. The host-specific manual release budget is 10 seconds
and 420 MiB; deterministic byte equality remains mandatory. It is a release
generator, not a request path.

The active host served content-hashed CSS/JS with only a 12-hour maximum age
and served the WOFF2 without an explicit cache header. That Nginx state is
outside this repository diff and remains a deployment correction before
promotion.

## Verification status

The final focused run passed 52 tests with 1,914 assertions. A post-Larastan
cache rerun passed another 4 tests with 15 assertions. The applicable
performance, index, stable-pagination, Livewire-payload, cache
isolation/failure, observability, forum-tree, journal timeline/export, and
affected feature contracts are green.

Observed repository gates:

- Composer strict validation, locked security audit, and platform requirement
  inspection passed. The official npm registry reported zero vulnerabilities;
  the configured mirror cannot implement the audit endpoint.
- The additive migration passed a complete isolated rollback/reapply cycle.
  At the observed snapshot, all 149 migrations produced 292 tables; fresh seed
  and repeat seed both succeeded and retained exactly 10 users.
- Production Vite build, bundle inspection, isolated config/route/view cache
  compilation, forum requirement generation for 38,377 records, and
  `git diff --check` passed.
- The isolated Places browser journey passed desktop and mobile layout,
  controls, image, overflow, private-location, console, submission, and
  moderation checks. The broader a11y run failed on concurrently introduced
  onboarding copy (`accepted community rules version` was not visible), and
  the discovery run could not start Chromium before its DevTools-port timeout.
- Full serial Pest completed 2,856 tests with 2,711 passes and 36 failures.
  Failures are outside this audit and include missing concurrent Event/Places
  factories/classes/actions, stale generated database-domain evidence, Portal
  routes/services, and a missing compact-resource component.
- Larastan reports 30 findings outside the attributable performance code,
  concentrated in concurrent Event competition and Place fact work. The two
  performance-slice findings initially reported for private search statistics
  were fixed and the focused cache regression rerun passed.
- The immutable forum-source check remains blocked by missing prompt entry
  `1785397895`; generated forum requirements themselves are byte-consistent.

Because the complete Pest, Larastan, browser, and source-preservation gates are
not green, the audit remains a no-go for publication. No completion claim,
commit, or push is permitted from these results.
