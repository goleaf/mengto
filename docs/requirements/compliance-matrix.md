# Laravel Compliance Matrix

Status meanings: `verified` has current automated or command evidence;
`active` is an enforced rule with review evidence; `partial` has explicit debt;
`N/A` is outside the installed stack or current product boundary.

| ID | Status | Current evidence | Remaining boundary |
|---|---|---|---|
| LAR-01 | verified | Laravel 13, PHP >=8.3, Blade, Tailwind 4; dependency manifests | Livewire/Flux/Filament/Volt are not installed |
| LAR-02 | partial | Thin invokable controllers, Actions, Services, DI; request service locator removed | Large legacy catalog/presenter classes need feature-by-feature decomposition |
| LAR-03 | verified | Named grouped routes, middleware, route model binding, controller tests | Continue route ownership checks for new domains |
| LAR-04 | verified | `ArchitectureComplianceTest`; class-based view-data components | New Blade must stay free of PHP/class calls |
| LAR-05 | N/A | No Livewire package or public Livewire state | Reassess only if installed for a product feature |
| LAR-06 | verified | Form Request layer, method injection, upload rules, negative feature tests | Localize validation if another UI locale is activated |
| LAR-07 | partial | Policies/Gates and negative ownership tests | `ForumActor` is prototype identity, not production authentication |
| LAR-08 | verified | Hashed expiring one-time medical/care/device access grants and tests | Keep token lifecycle tests with every grant type |
| LAR-09 | partial | CSRF, escaping, guarded fillable fields, audit models, security/private headers | Production auth/MFA and external integration threat models are future gates |
| LAR-10 | verified | Eloquent-only audit, strict non-production models, eager loads, bounded query tests | EXPLAIN is required only when tables exceed the 10k-row threshold |
| LAR-11 | verified | 68 applied migrations; indexed foreign/filtered fields; factories and constraints | Use expand/backfill/contract for future large production tables |
| LAR-12 | partial | Database cache, explicit listing TTL/invalidation, lock/idempotency patterns | Redis/Memcached are not configured; add only from measured infrastructure need |
| LAR-13 | N/A | No public versioned JSON API | API Resources become mandatory when one is introduced |
| LAR-14 | N/A | No current external HTTP client integration | Client classes/timeouts/retries/fakes required before adding one |
| LAR-15 | verified | Filesystem disks, validated uploads, private authorized downloads, fakes | Define retention per future production privacy policy |
| LAR-16 | partial | Product UI is currently English; forum content records language | Translation-key migration is required before enabling a second UI locale |
| LAR-17 | verified | Mobile-first design system, semantic controls, focus and responsive feature tests; desktop/mobile browser smoke | Repeat browser smoke for shared visual changes |
| LAR-18 | verified | Query-bound tests, pagination, selected columns, production Vite build | Capture TTFB/bundle baselines when a production-like environment exists |
| LAR-19 | verified | Composer/NPM audit and lock files; no package added in this audit | Document and audit every future package |
| LAR-20 | N/A | This project has not declared the shared-hosting workerless constraint | Activate web chunks only if deployment requirements demand it |
| LAR-21 | active | Transactions precede side effects in persistence flows; audit events persisted | Add delivery audit with real notification channels |
| LAR-22 | verified | Pest feature/architecture suite and factories for every model | Add browser E2E when authentication replaces the prototype actor |
| LAR-23 | verified | Pint, enums, method injection, no runtime `env()` or request service locator | Continue bounded decomposition of oversized legacy classes |
| LAR-24 | active | Laravel exception reporting, JSON negotiation, secret-safe audit metadata | Production logging/retention requires deployment configuration |
| LAR-25 | verified | 63 models and 63 factories; domain seed data used by flows | Production seeding must remain non-destructive |
| LAR-26 | verified | README, architecture, standard, matrix, plan, changelog | Update these in the same commit as future behavior |
| LAR-27 | verified | Main branch, scoped status/diff review, task commit and normal push | Never include `.agents/vendor/` unless explicitly requested |
| LAR-28 | partial | Repository/docs/schema audit, official docs fallback, tests/build/audits | Context7 quota and Boost MCP tools were unavailable during this audit |
| LAR-29 | active | Safe env example, deployment checklist, production build commands | Actual backup/cache/smoke execution belongs to deployment infrastructure |
| LAR-30 | verified | Project-specific decisions documented in README/architecture | Do not import unrelated project rules |
| LAR-31 | verified | This register, matrix, tests, docs, scoped commit workflow | Re-audit statuses for every substantial feature |

## Audit Snapshot

- Runtime: PHP 8.5.8, Laravel 13.23.0.
- Schema: 71 tables, 68 applied migrations, SQLite local database.
- Routes: 124 named routes; no route closures.
- Models/factories: 63/63.
- Baseline suite: 110 tests, 929 assertions.
- Post-hardening suite: 116 tests, 3,881 assertions.
- Quality gates: Pint, Composer strict validation, Vite production build,
  production cache compilation, and desktop/mobile browser smoke passed.
- Browser smoke: `/` at 1440 px and `/devices` at 390 px; no console errors and
  no mobile document overflow.
- Dependency audit: no known Composer advisories; NPM production audit clean.
- Query delta for this audit: no new application queries. Strict Eloquent
  exposed and corrected incomplete selected-column projections.

The matrix does not label absent production identity, infrastructure, or an
uninstalled framework as complete. Those are explicit product/deployment
boundaries, not hidden omissions.
