# Repository Performance Audit Work Ledger

Date: 2026-08-30

This ledger owns the measured repository-wide performance audit identified by
`PERF-AUD-01` through `PERF-AUD-10` in `docs/implementation-plan.md`. The
principal agent owns all cross-module decisions, repository edits, specialist
dispositions, verification, and publication. Discovery specialists are
read-only and must not edit the shared working tree. The independent reviewer
receives only the frozen attributable diff after implementation and focused
verification.

The audit restarts on `main` at `462539c0`, aligned with `origin/main`. The working
tree already contains unrelated user-owned plan, fixture, and audit-ledger work.
Those bytes remain outside this audit. Attributable publication uses a
temporary `GIT_INDEX_FILE` and a complete staged-diff review.

## Measurement contract

Every candidate path needs a deterministic representative fixture and a
recorded baseline for database query count, response or snapshot bytes, peak
memory delta, and elapsed wall time. A finding is confirmed only when the
measurement or a static boundedness invariant demonstrates a regression risk.
Each accepted fix records the same measurements afterward and adds an explicit
numeric or constant-growth regression budget. Timing and memory observations
are evidence, not brittle CI assertions, unless the repository has a stable
machine-independent boundary for the metric.

Cache is considered only after query shape, selection, eager loading,
pagination, indexes, and serialization are corrected. A cache proposal must
name its owner, versioned key, user/organization/role/locale scope, TTL,
invalidation triggers, lock and wait behavior, unavailable-store behavior,
and isolation/stampede tests. Private values may never share a key across any
of those scopes.

## Specialist assignments

| Specialist | Exclusive read-only scope | Structured deliverable | Status |
| --- | --- | --- | --- |
| Query analysis | Directories, feeds, search, dashboards, workspaces, calendars, message lists, Places, Events, medical/care timelines, device screens, exports; Eloquent call sites and Blade/presenter boundaries | Ranked path inventory with exact files/symbols, current query shape, evidence for N+1/lazy loads/unbounded reads/PHP filtering/per-row aggregates/over-selection/unstable pagination, representative fixture proposal, and reproduction command | completed; material Place/search/journal findings reproduced and fixed |
| Index analysis | Migrations, schema, model scopes, filtered/sorted joins and actual query patterns; SQLite and configured production-driver portability | Query-to-index matrix, existing-index coverage, `EXPLAIN` evidence plan, only justified additive index candidates, write-cost/duplication risks, migration and rollback notes | completed; seven SQLite plans and migration reversal reproduced |
| Livewire payload analysis | `app/Livewire`, form objects, computed properties, Livewire views, browser snapshots and interaction requests | Component inventory with public-property types, model/graph exposure, repeated serialization, snapshot/request byte measurements, duplicate request evidence, safe scalar/computed alternatives and enforceable budgets | completed; runtime budgets green, no rewrite justified |
| Cache isolation analysis | First-party cache and lock consumers, keys, scopes, locale/role/tenant boundaries, invalidation and failure paths | Cache registry with owner/key/scope/TTL/invalidation/lock/failure/tests; confirmed leakage, stale-data, stampede, global-flush, or cache-masking findings with reproductions | completed; category/search leaks and four stampede/failure paths fixed |
| Asset performance analysis | Vite inputs/chunks, Tailwind and SCSS outputs, first-party JavaScript lifecycle, images, fonts, browser network requests | Current raw/gzip bundle table, duplicated CSS/JS or request evidence, image dimension/variant findings, repeated-navigation request/listener checks, and measured fix candidates | completed; bundle/no-duplicate trace recorded and dimensions fixed |
| Runtime operations analysis | Seeders, generators, exports, maintenance commands, jobs and deployment/runtime scripts | Boundedness and resumability inventory, unbounded generator/export/seeder findings, memory/time measurement design, safe chunk/cursor/stream remediation and operational rollback | completed; export fixed and verifier exit concern not reproduced |
| Metrics and observability analysis | Request correlation middleware, logging configuration, slow-operation context, health and operational ownership | Correlation-header/log-context verification, secret-redaction review, measurable slow-operation gaps, exact safe context allow-list, thresholds/owners/retention and tests | completed after two review cycles; material findings fixed |
| Independent final review | Frozen attributable diff and recorded before/after evidence only; no implementation role | Severity-ranked requirement, correctness, query/index, privacy/cache, Livewire, asset, runtime, observability, portability and test-quality findings with reproduction evidence and separate approval verdict | blocked until diff freeze |

## Required superagent revalidation wave

The 2026-08-30 continuation uses seven fresh read-only agents. They may write
reports only under `/tmp`; they must not edit, stage, commit, migrate a shared
database, start a shared server, or alter runtime configuration.

| Agent | Exclusive revalidation scope | Required output | Status |
| --- | --- | --- | --- |
| Query Performance Agent | Eloquent query shape, query budgets, N+1, selected columns, pagination, measured index consumers | Reproduced high/medium findings with exact files, fixtures and commands; distinguish existing foreign edits | pending |
| Cache Architecture Agent | Cache registry, keys, scopes, TTL, stale behavior, invalidation, fallback and cache-before-query risks | Complete lifecycle matrix and leakage/stampede reproductions | pending |
| Redis and Lock Agent | Configured stores, lock support/ownership/timeout, counters/rates/sessions/queues and deployment readiness | Store/lock capability evidence and only justified Redis recommendations | pending |
| Livewire Payload Agent | Public properties, computed projections, pagination/lazy/defer/isolate/islands, request and payload budgets | Component/payload inventory with reproducible snapshot/update evidence | pending |
| Frontend Asset Agent | Vite manifest/chunks, CSS/JS raw+gzip, images/fonts/network lifecycle | Before/current size and request table, consumer-backed fix candidates only | pending |
| Concurrency Agent | Imports, payments, tokens, cache regeneration, state changes, maintenance, idempotency and race tests | Race/lock/timeout/retry findings with safe reproduction design | pending |
| Performance Test Agent | Query/payload/cache/lock/concurrency regression-test value and flake resistance | Coverage gap matrix and exact targeted/full command plan | pending |

## Principal disposition rules

1. Reproduce every material specialist finding against the current shared tree.
2. Reject speculative optimizations and record why they were not implemented.
3. Start every behavior change with an observed failing regression test.
4. Keep specialist scopes disjoint; the principal alone reconciles cross-domain
   query, schema, cache, presentation, and operations decisions.
5. Freeze only attributable paths for review, reproduce every material review
   finding, record its disposition, fix valid in-scope findings, and rerun the
   affected checks before final gates.
