# PawCircle Architecture

## Request Flow

The default server-rendered flow is:

`route -> middleware -> route binding -> Form Request -> policy/gate -> Action
or focused Service -> Eloquent -> Presenter/view data -> Blade component`.

- Routes stay declarative, named, grouped, and middleware-protected.
- Controllers are invokable coordinators.
- Form Requests normalize and validate untrusted input. Their dependencies use
  method injection; they do not resolve the container manually.
- Policies and gates protect server actions. Validation never replaces them.
- Actions own cohesive mutations and transaction boundaries.
- Services own reusable multi-step read or domain behavior.
- Presenters prepare labels, dates, counts, URLs, state, and component contracts.
- Blade renders prepared values with simple loops and conditions.

## Persistence

Eloquent is the only application query layer. Projection scopes own explicit
`select()` lists; filter scopes remain composable and must not silently reset a
caller's projection. Relationships used by views are eager loaded. Aggregates
use `withCount`, `withAvg`, `withExists`, or dedicated bounded queries.

Non-production environments enable Eloquent strict mode to catch lazy loading,
missing selected attributes, and silently discarded input. Schema changes use
new reversible migrations with indexes and constraints matching observed query
patterns. SQLite portability is required by the current local prototype.

## Privacy And Security

Medical records, care journals, smart devices, location, and temporary access
responses are private and non-cacheable. Shared links store only token hashes,
expire, can be revoked, enforce view/permission limits, and write audit records.
Global responses add content-type, frame, referrer, and permissions headers;
private middleware can strengthen the referrer policy.

The current `ForumActor` is a deterministic prototype identity boundary.
Policies still enforce ownership, but this is not production authentication.
A production release requires real authenticated identities, secure recovery,
session lifecycle, MFA for critical operations, and a migration of actor keys.

## Frontend

The frontend is Blade, Tailwind 4, Sass, and small progressive JavaScript.
There is no Livewire, Flux, Filament, Volt, React, Vue, Inertia, or Svelte.
Computed view values belong in presenters or class-based Blade components.
Mobile-first layout, visible focus, keyboard access, touch targets, non-color
status cues, reduced motion, and stable responsive dimensions are required.

## Localization

The UI is currently English. Content may record its own language, but a second
UI locale is not active. Existing literals are accepted only under this
single-locale product boundary. Before another UI locale is enabled, migrate
visible strings, validation, notifications, metadata, pluralization, and date/
number formatting to one canonical translation system with fallback tests.

## Cache And Long Work

Cache is introduced only for measured expensive stable reads and must document
key scope, TTL, invalidation, and tests. Redis, Memcached, queue workers, Octane,
Horizon, Reverb, and Telescope are not architectural defaults.

Long work uses queues when deployment supports workers. If a future shared-host
deployment forbids workers/cron, use persisted, locked, idempotent web chunks
with progress, resume, retry, cancellation, and bounded request duration.

## External Boundaries

New HTTP clients require config-backed endpoints, secret-safe logs, connection
and request timeouts, explicit status/schema validation, bounded idempotent
retry, and fake-backed tests. New JSON APIs use Resources. New packages require
compatibility, license, maintenance, security, measured benefit, and rollback
documentation.
