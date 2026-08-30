# Repository Performance Audit Work Ledger

Date: 2026-08-30

This ledger owns the measured repository-wide performance audit identified by
`PERF-AUD-01` through `PERF-AUD-10` in `docs/implementation-plan.md`. The
principal agent owns all cross-module decisions, repository edits, specialist
dispositions, verification, and publication. Discovery specialists are
read-only and must not edit the shared working tree. The independent reviewer
receives only the frozen attributable diff after implementation and focused
verification.

The audit starts on `main` at `ae4ac32`, aligned with `origin/main`. The working
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
| Query analysis | Directories, feeds, search, dashboards, workspaces, calendars, message lists, Places, Events, medical/care timelines, device screens, exports; Eloquent call sites and Blade/presenter boundaries | Ranked path inventory with exact files/symbols, current query shape, evidence for N+1/lazy loads/unbounded reads/PHP filtering/per-row aggregates/over-selection/unstable pagination, representative fixture proposal, and reproduction command | queued |
| Index analysis | Migrations, schema, model scopes, filtered/sorted joins and actual query patterns; SQLite and configured production-driver portability | Query-to-index matrix, existing-index coverage, `EXPLAIN` evidence plan, only justified additive index candidates, write-cost/duplication risks, migration and rollback notes | queued |
| Livewire payload analysis | `app/Livewire`, form objects, computed properties, Livewire views, browser snapshots and interaction requests | Component inventory with public-property types, model/graph exposure, repeated serialization, snapshot/request byte measurements, duplicate request evidence, safe scalar/computed alternatives and enforceable budgets | queued |
| Cache isolation analysis | First-party cache and lock consumers, keys, scopes, locale/role/tenant boundaries, invalidation and failure paths | Cache registry with owner/key/scope/TTL/invalidation/lock/failure/tests; confirmed leakage, stale-data, stampede, global-flush, or cache-masking findings with reproductions | queued |
| Asset performance analysis | Vite inputs/chunks, Tailwind and SCSS outputs, first-party JavaScript lifecycle, images, fonts, browser network requests | Current raw/gzip bundle table, duplicated CSS/JS or request evidence, image dimension/variant findings, repeated-navigation request/listener checks, and measured fix candidates | queued |
| Runtime operations analysis | Seeders, generators, exports, maintenance commands, jobs, deployment/runtime scripts and data iteration | Boundedness and resumability inventory, unbounded generator/export/seeder findings, memory/time measurement design, safe chunk/cursor/stream remediation and operational rollback | queued |
| Metrics and observability analysis | Request correlation middleware, logging configuration, slow-operation context, health and operational ownership | Correlation-header/log-context verification, secret-redaction review, measurable slow-operation gaps, exact safe context allow-list, thresholds/owners/retention and tests | queued |
| Independent final review | Frozen attributable diff and recorded before/after evidence only; no implementation role | Severity-ranked requirement, correctness, query/index, privacy/cache, Livewire, asset, runtime, observability, portability and test-quality findings with reproduction evidence and separate approval verdict | blocked until diff freeze |

## Principal disposition rules

1. Reproduce every material specialist finding against the current shared tree.
2. Reject speculative optimizations and record why they were not implemented.
3. Start every behavior change with an observed failing regression test.
4. Keep specialist scopes disjoint; the principal alone reconciles cross-domain
   query, schema, cache, presentation, and operations decisions.
5. Freeze only attributable paths for review, reproduce every material review
   finding, record its disposition, fix valid in-scope findings, and rerun the
   affected checks before final gates.

