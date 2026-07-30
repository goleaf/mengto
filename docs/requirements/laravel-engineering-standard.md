# Laravel Engineering Standard

This document is the normalized, project-applicable version of the consolidated
Laravel requirements supplied on 2026-07-30. Every numbered source section and
every bullet within its declared range is normative. Stable IDs let plans,
tests, and reviews cite requirements without copying the entire source prompt.

## Interpretation

- `MUST` is a release requirement for code in the affected surface.
- `SHOULD` requires a documented reason when not followed.
- A technology-specific group is `N/A` when that technology is not installed.
- A project-specific constraint applies only when a product document activates
  it. Generic preferences never justify an unrelated package or architecture.
- Existing prototype boundaries remain explicit debt; documentation must not
  describe them as production security.

## Requirement Register

### LAR-01 Runtime Stack (source 1.1-1.15)

Use the project-supported PHP and Laravel versions. Prefer server-rendered
interaction and the installed UI stack. When Livewire is present, use supported
class-based multi-file components, never Volt by default. Use Tailwind 4 and
installed Flux components according to their official APIs. Do not add or
duplicate a JavaScript framework without measured technical need.

### LAR-02 Architecture (source 2.1-2.18)

UI layers coordinate requests but do not own business rules. Controllers remain
thin; Actions represent cohesive mutations; Services represent genuinely shared
multi-step behavior. Use explicit dependency injection. Repositories,
interfaces, value objects, and extra layers require a real boundary. Centralize
repetition without fragmenting simple behavior or creating oversized classes.

### LAR-03 Controllers And Routes (source 3.1-3.15)

Routes are named, grouped, declarative, middleware-protected, and use scoped
route model binding. Route files contain no business closures. Controllers
receive validated requests, authorize, delegate, and return one response type.
Interactive full-page components may replace controllers only when their stack
is installed and domain behavior remains outside the UI.

### LAR-04 Passive Blade (source 4.1-4.25)

Blade contains no `@php`, PHP tags, database/model/service/action/container/
facade calls, hidden relationship queries, complex collection construction,
business calculations, SEO generation, or large inline CSS/JavaScript. It may
render prepared data with simple conditions and loops. Repeated markup belongs
to Blade or installed UI components. All user-facing content follows the
project localization policy.

### LAR-05 Livewire (source 5.1-5.37)

If Livewire is installed: use class components and Form objects for complex
forms; keep browser state minimal and untrusted; lock sensitive identifiers;
authorize and validate every mutation; use stable keys; control render queries;
use lazy/defer/islands/navigation only where beneficial; clean up JavaScript
resources; debounce/throttle repeated work; expose loading, success, error,
empty, offline, and progress states; keep long operations idempotent.

### LAR-06 Validation (source 6.1-6.18)

All request and component input is untrusted. Complex HTTP input uses Form
Request classes; only validated, normalized values cross the boundary. Reusable
rules use rule classes or shared sources. Validate uniqueness in its tenant
scope, files by type/content/size, boundary values, and localized messages.
Validation never substitutes for authorization or database constraints.

### LAR-07 Authentication And Authorization (source 7.1-7.19)

Authentication establishes identity; policies and gates establish capability.
Authorize model and special actions on the server, including every interactive
mutation and tenant scope. Hidden controls are not security. Critical actions
may require password/MFA confirmation. Auth endpoints are rate-limited and do
not disclose account existence. Logout invalidates the session and CSRF token.
Demo identities remain outside production.

### LAR-08 Magic Links And Tokens (source 8.1-8.12)

Use long cryptographic random tokens, store only hashes, set purpose and expiry,
consume atomically once, revoke immediately, rate-limit generation/validation,
avoid token logging, and invalidate older tokens when the business rule calls
for replacement.

### LAR-09 Security (source 9.1-9.24)

No repository secrets, production debug data, public stack traces, raw
user HTML, unverified webhook, unsafe URL fetch, uncontrolled mass assignment,
or client-trusted hidden fields. Keep CSRF/origin protection, output escaping,
SSRF controls, rate limits, webhook/payment idempotency, and audit trails for
privileged changes. Critical ownership, role, balance, and status fields are
never broadly assignable.

### LAR-10 Eloquent And Database Access (source 10.1-10.34)

Use Eloquent, eager loading, aggregate subqueries, deliberate projections,
scopes, pagination, stable chunking/lazy iteration, real query plans, matching
indexes, foreign keys, unique constraints, transactions, and bounded deadlock
retries. No query in a loop or Blade, unbounded `get`, accidental lazy loading,
string-built SQL, or long external calls inside a transaction. Large schema
changes use compatible expand/backfill/contract phases.

### LAR-11 Migrations And Schema (source 11.1-11.18)

Each migration has one concern, reversible methods, safe existing-data behavior,
intentional nullability/defaults, proper types, money precision, constraints,
indexes, and backfill plans. Preserve required SQLite compatibility and avoid
engine-only features where portability is required. Factories, seeders, and
tests track schema and constraint changes.

### LAR-12 Cache, Redis, And Memcached (source 12.1-12.25)

Cache only measured expensive stable work. Every key has ownership, scope, TTL,
invalidation, and tests. Prevent private cross-user data, stampedes, and stale
critical state. Use atomic locks with bounded ownership/release. Redis suits
locks, counters, rate limits, and sessions; Memcached suits simple read caches.
Do not install or use two stores merely to satisfy a checklist.

### LAR-13 API Responses (source 13.1-13.18)

Public JSON uses API Resources and explicit fields, conditional loaded
relations, stable errors/statuses/pagination, and a real versioning strategy
when external compatibility is promised. Resources do not query. Imports,
payments, and webhooks are idempotent and correlated without logging secrets.
External failures cannot leave half-saved local state.

### LAR-14 External HTTP Integrations (source 14.1-14.17)

Use Laravel's HTTP client through a dedicated client, config-backed endpoints
and credentials, connection/request timeouts, bounded safe retries, explicit
status and JSON-shape checks, correlation IDs where useful, secret-safe logs,
and `Http::fake()` tests. Never call `env()` from domain code or retry an
unprotected non-idempotent request.

### LAR-15 Files And Media (source 15.1-15.23)

Use configured disks, generated names, type/content/size/dimension validation,
central image presets, thumbnails, cleanup, and explicit model-deletion policy.
Private media requires authorized or expiring access. Seeders cover upload
surfaces. Preserve lawful streaming behavior and never bypass DRM or access
controls. Product-specific watermark and purchase checks apply only where
activated.

### LAR-16 Localization (source 16.1-16.25)

Preserve the established localization system and locales; do not create a
parallel system. User-visible text, validation, notifications, email, SEO, and
client text use stable translation keys when localization is active. Preserve
placeholders, pluralization, fallback, locale formatting, and URL policy. A
single-language product keeps that language until product requirements add
another locale; enabling another locale requires migrating remaining literals.

### LAR-17 Tailwind And UI (source 17.1-17.28)

Design mobile-first with tokens and reusable components. Prefer installed Flux
components to duplicate controls. Keep visible focus, labels, keyboard/focus
management, field-linked errors, non-color status cues, reduced motion,
complete dark-mode support when enabled, loading/empty/error/success states,
mobile table strategy, no horizontal overflow, and touch-sized controls.

### LAR-18 Performance (source 18.1-18.32)

Measure query count/time, TTFB, HTML/snapshot/bundle size, request count, and
memory before and after relevant changes. Fix N+1, indexes, projections,
pagination, image size, render boundaries, and state size before infrastructure
expansion. Cache only with invalidation. Octane, Horizon, Reverb, Telescope, and
new packages require measured benefit and an operations plan.

### LAR-19 Packages (source 19.1-19.18)

Prefer framework and installed capabilities. New packages must support current
PHP/Laravel, have a compatible license, active maintenance, no duplicate
function, security review, committed lock changes, documented purpose/config/
rollback, and verified behavior. Automated refactors may not silently alter
business behavior.

### LAR-20 Web-Only Long Operations (source 20.1-20.22)

Only projects explicitly targeting shared hosting prohibit worker/cron/SSH
dependencies. There, long work uses small web-triggered idempotent chunks with
persisted stage/cursor/progress/error, retry/resume/cancel, locking, and bounded
requests. Never keep one request open indefinitely or fake a queue with it.

### LAR-21 Events And Notifications (source 21.1-21.12)

Events describe completed facts; listeners own one reaction. Prefer direct
Actions over opaque event chains. Side effects occur after successful commit,
are localized and idempotent, and do not roll back correct critical data merely
because email failed. Track delivery when the product depends on it.

### LAR-22 Tests (source 22.1-22.32)

Behavior changes require tests. Cover critical domain logic, positive/negative
authorization, validation boundaries, roles, tenant isolation, interactive
actions, sensitive state, filters/pagination, cache invalidation, races,
idempotency, uploads, locale/fallback, factories, and realistic seed relations.
Use feature tests for Laravel behavior, unit tests for pure logic, installed
component/browser tools for their surfaces, and fakes for integrations. Run
relevant tests and report any check not run.

### LAR-23 Code Quality (source 23.1-23.30)

Follow Laravel/PHP naming and formatting. Use descriptive focused methods,
remove dead/debug/unused code, centralize repeated checks, use enums and typed
objects where they add value, inject dependencies, avoid service locator and
runtime `env()`, centralize statuses/config/text, run Pint, and do not suppress
analysis or catch universal exceptions without handling.

### LAR-24 Errors And Logging (source 24.1-24.18)

Do not swallow exceptions or expose internal messages. Model expected business
failures explicitly; report unexpected failures with safe actor/tenant/request/
object context and correct severity. Never log passwords, tokens, payment data,
or unfiltered requests. Bound retries, avoid log floods, record terminal errors,
and expose a support-safe incident identifier when needed.

### LAR-25 Factories, Seeders, Demo Data (source 25.1-25.15)

Every principal model has a valid factory with useful states. Seeders create
coherent relationships, roles, empty/normal/complex states, and required sample
media without destructive production refresh. Demo login and fixed credentials
exist only in local/demo environments.

### LAR-26 Documentation (source 26.1-26.30)

Read source-of-truth documents before editing. Maintain a requirements index,
implementation plan, architecture, compliance matrix, README, configuration
examples, integration/cache/package notes, translation keys, permissions, and
changelog in the same change as behavior. One canonical document owns each
rule category; stale or conflicting descriptions must be removed.

### LAR-27 Git Workflow (source 27.1-27.22)

For this repository work on `main`, inspect status/diffs first, preserve
unrelated work, avoid destructive commands/history rewrites/force push, stage a
task-owned slice, inspect it for secrets/artifacts, commit code/tests/docs
together, and push normally. GitHub MCP restrictions do not prevent local Git.

### LAR-28 Agent And MCP Workflow (source 28.1-28.21)

Inspect code and Markdown, maintain a complete plan, implement without needless
questions, follow existing architecture, use only relevant MCP tools, prefer
official docs, use Boost/Context7/browser tools when installed and available,
report exact failures/fixes, preserve behavior during refactors, and prove each
improvement with code, test, or measurement.

### LAR-29 Deployment And Production (source 29.1-29.20)

Production disables debug, reads secrets through config, builds production
assets, installs production dependencies, applies supported config/route/view
caches, runs controlled non-destructive migrations, and has backup/rollback,
protected health checks, minimal storage permissions, private files, log/temp
retention, infrastructure-matched cache/session, and post-deploy smoke checks.

### LAR-30 Project-Specific Rules (source 30.1-30.16)

SQLite, workerless operation, `main`-only Git, demo login, invite/QR behavior,
private pet data, store currencies/refunds/watermarks, HLS/Plyr, UI language,
changelog language, and Flux Pro apply only in projects whose product or agent
documents explicitly activate them. Never globalize these constraints.

### LAR-31 Conflict Resolution And Priority (source 31 and final 1-23)

Livewire owns interaction, not domain behavior. Cache only measured stable work.
Write tests and run the relevant feasible set. Use local Git when GitHub MCP is
forbidden. Choose queue workers on supported servers and web chunks only where
shared hosting requires them. Do not globalize SQLite. Work order is: read
truth, detect installed versions, preserve architecture/behavior, enforce
passive Blade and server validation/authorization, prevent N+1, scope cache and
localization, use installed UI APIs, justify packages, test, update documents,
preserve unrelated work, commit, push, and report unverified facts honestly.

## Enforcement

Automated architecture tests enforce the objective subset: passive Blade,
absence of service locator in Form Requests, no runtime `env()`, no prohibited
raw SQL, named routes, model factories, and security headers. The compliance
matrix records requirements that need product or infrastructure evidence rather
than a static code assertion.
