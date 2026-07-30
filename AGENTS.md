# AGENTS.md

This file is the mandatory repository contract for every human and automated
contributor to PawCircle. Read it before changing code.

## Repository Purpose

PawCircle is a privacy-aware pet community and care application. It combines
public social discovery with private medical records, care journals, smart
devices, marketplace workflows, expert services, lost-pet coordination,
messaging, groups, events, and places.

The application is a production-capable Laravel system. Existing validated
product behaviour, server-authoritative identity, encrypted per-user social
state, and private domain boundaries must remain consistent.

## Mandatory Reading Order

1. `AGENTS.md`
2. `docs/index.md`
3. `PRODUCT.md` and `DESIGN.md`
4. `docs/requirements.md`
5. `docs/product-requirements.md`
6. `docs/system-requirements.md`
7. `docs/non-functional-requirements.md`
8. `docs/architecture.md`, `docs/domain-model.md`, and `docs/data-model.md`
9. `docs/security.md` and `docs/authorization.md`
10. `docs/frontend.md`, `docs/livewire.md`, `docs/tailwind.md`,
    `docs/accessibility.md`, and `docs/localization.md`
11. `docs/testing.md`, `docs/seeding.md`, and `docs/deployment.md`
12. `docs/implementation-plan.md` and
    `docs/requirements/compliance-matrix.md`
13. The applicable feature specification under `docs/superpowers/specs`

Historical implementation plans under `docs/superpowers/plans` explain how an
earlier prototype slice was delivered. They are evidence, not current global
instructions.

## Conflict Resolution

Use this order:

1. This file.
2. A more specific nested `AGENTS.md`, if one is introduced.
3. Canonical requirements linked from `docs/requirements.md`.
4. Security, legal, privacy, and data-integrity requirements.
5. Product and architecture decisions.
6. Current implementation plan.
7. Tests that accurately encode the intended behaviour.
8. Existing code.
9. Historical specifications and plans.

Do not preserve an insecure implementation merely because an old test expects
it. Update the requirement, implementation, and test together.

## Technology Baseline

- PHP: `>=8.5.0 <8.6.0`
- Laravel: latest stable compatible `13.x`, constrained with `^13.0`
- Livewire: latest stable compatible `4.x`, minimum `4.3`
- Blade: server-rendered templates with no Volt
- Tailwind CSS and `@tailwindcss/vite`: latest stable compatible `4.x`,
  minimum `4.3`
- Vite and `laravel-vite-plugin`: latest mutually compatible stable versions
- Pest: stable `4.x` as the primary PHP test framework
- Larastan/PHPStan: the repository's static-analysis gate
- Eloquent: the application query and persistence layer
- SQLite: required for local and automated tests; production adapters must
  retain schema and query portability unless an ADR says otherwise

Flux and Filament are not installed. Do not add either without a current
requirement, licensing confirmation where applicable, and a documented
maintenance benefit. Do not install prerelease dependencies.

## Architectural Boundaries

- Routes declare endpoints, middleware, bindings, and names only.
- Controllers receive validated input, authorize, invoke one application
  operation, and return a response.
- Class-based Livewire components manage interactive presentation state,
  authorize every mutation, validate all browser input, and call Actions.
- Actions represent one meaningful application operation and own short
  transactional boundaries.
- Services contain cohesive reusable domain logic or external integrations.
- Models own persistence, relationships, casts, scopes, and cohesive entity
  behaviour. Models do not make external network calls.
- Policies decide model and resource authorization.
- Form Requests and Livewire form objects own substantial validation.
- API Resources own public JSON shape.
- Events describe facts that already happened. Side effects run after a
  successful commit where required.
- Repositories are allowed only for a real multi-source or persistence
  boundary. Do not wrap Eloquent mechanically.

Use constructor or method dependency injection. Do not use the container as a
service locator in domain code. Avoid god services and speculative interfaces.

## Livewire Rules

- Use normal class-based components under `app/Livewire` with separate views
  under `resources/views/livewire`.
- Never use or introduce Volt, single-file components, or anonymous PHP
  component classes.
- Keep public state small, typed, intentionally serializable, and free of
  secrets or large Eloquent graphs.
- Treat every public property and action parameter as untrusted input.
- `#[Locked]` protects hydration integrity but never replaces authorization.
- Prefer form objects for substantial forms and `#[Computed]` for derived data.
- Use stable `wire:key` values based on durable identifiers.
- Use `.live`, debounce, polling, lazy/defer, islands, streaming, and async
  behaviour only when their semantics and measured cost justify them.
- Provide loading, dirty, empty, error, and offline states where applicable.
- Test direct action invocation, authorization, validation, and repeated
  submissions.

## Blade Rules

- Never use `@php`, `@endphp`, ordinary PHP blocks, direct model access,
  queries, facades, the service container, Actions, Services, or external
  clients in Blade.
- Do not perform business, permission, SEO, monetary, status, or complex
  collection calculations in Blade.
- Do not trigger lazy-loaded relationships in Blade.
- Blade may render prepared values, simple conditions, simple loops,
  translations, Blade components, Livewire directives, and escaped output.
- Escape user-controlled output by default. Rich HTML requires an explicit,
  tested sanitization boundary.
- Use `@forelse` for collections with a meaningful empty state.
- Repeated presentation belongs in Blade components, not Livewire unless it
  needs server-backed interaction.
- First-party user-facing text must use the established localization system.

Architecture tests must prevent `@php`, `@endphp`, Volt, forbidden Blade
calls, debug functions, and `env()` outside configuration.

## Tailwind And Interface Rules

- Tailwind uses the Vite plugin and CSS-first configuration.
- Define shared design values through `@theme`; retain the deliberate SCSS
  component layer until an independently verified migration replaces it.
- Build mobile-first, then add progressive tablet and desktop behaviour.
- Never construct utility class names dynamically. Use complete class maps.
- Repeated arbitrary values become tokens or intentional custom utilities.
- Do not use `@apply` as the default component abstraction.
- Preserve visible focus, reduced-motion behaviour, forced-colors usability,
  semantic status text, sufficient contrast, 44-pixel touch targets, and a
  complete keyboard path.
- Do not introduce horizontal page overflow or hover-only interactions.
- Use Lucide icons for supported interface symbols.

## Localization Rules

- The canonical framework is Laravel language files plus locale-aware
  formatting. Do not create a second translation mechanism.
- Preserve every supported locale. The initial production locales are `en`,
  `lt`, and `ru`.
- Never hardcode new user-facing text in PHP, Blade, JavaScript,
  notifications, mail, validation, or user-facing API errors.
- Translation keys are stable contracts. Keep placeholders and plural forms
  consistent across locales.
- Locale, timezone, date, number, currency, list, and measurement formatting
  must be explicit and tested.

## Database And Eloquent Rules

- No raw SQL strings in first-party application code.
- Use Eloquent and the schema builder. Database facades are allowed for
  transactions and supported schema inspection, not ad hoc raw statements.
- Prevent N+1 queries with intentional eager loads and aggregate subqueries.
- Never query inside a Blade view or a loop.
- Never use `Model::all()` for production collections.
- Select only required columns on performance-sensitive queries while
  retaining relationship keys.
- Paginate unbounded collections. Prefer cursor pagination for large stable
  feeds and `chunkById` or `lazyById` for large processing.
- Protect business uniqueness with database constraints, not preflight
  `exists()` checks alone.
- Use short transactions, locks, and idempotency keys for race-sensitive
  operations.
- Store money as integer minor units or a deliberate decimal, never float.
- Store unambiguous timestamps and preserve the relevant source timezone.
- Production data changes use expand-and-contract migrations. Never modify a
  historical migration after production use.
- Strict Eloquent behaviour stays enabled in local and test environments.

## Security Rules

- Authentication proves identity; authorization decides capability.
- Public prototype state is never an authorization boundary.
- All mutations and private resources require an authenticated or explicitly
  scoped temporary identity.
- Policies cover list, view, create, update, delete, restore, force-delete,
  download, share, export, control, and domain-specific transitions.
- Scope sensitive queries before returning a record.
- Validate all untrusted input server-side and explicitly map validated fields.
- Store only hashes of magic and temporary access tokens. Make tokens
  expiring, purpose-bound, atomically single-use where required, and
  rate-limited.
- Private files never receive unrestricted public URLs.
- Verify webhook signatures and external event idempotency before mutations.
- Never log secrets, full tokens, session identifiers, passwords, private
  keys, payment credentials, or complete authorization headers.
- Keep CSRF and origin protections enabled. Add step-up verification to
  high-risk operations when required.
- Production debug mode is off.

## Caching And Runtime Rules

- Cache only measured, sufficiently stable work.
- Every cache entry has an owner, versioned key, scope, TTL, invalidation
  triggers, failure behaviour, and tests.
- Never share private values across users, tenants, roles, or locales.
- Use atomic locks for race-sensitive operations and stampede prevention.
- The current local stack supports queues and scheduled work, but no
  user-facing critical operation may silently depend on unavailable runtime
  infrastructure. Deployment documents define the accepted execution model.
- Queue jobs must be idempotent, bounded, retryable, and dispatched after
  commit where necessary.

## Testing And Static Analysis

- Every behaviour change needs a meaningful PHP test.
- Use feature tests for Laravel behaviour, unit tests for pure domain logic,
  Livewire tests for components, and browser tests only for browser-only
  contracts.
- Test positive and negative authorization, tenant/owner isolation,
  validation boundaries, idempotency, concurrency-sensitive behaviour,
  files, integrations, localization, cache invalidation, and seed safety.
- Use factories; do not insert ad hoc database rows in tests.
- Fake external HTTP, filesystems, notifications, mail, and events where
  appropriate. Tests must not make accidental network calls.
- Run targeted tests first, then Pint and Larastan, then the full suite.
- Database-backed suites using a shared SQLite file must run sequentially.
- Do not suppress static-analysis findings broadly.

## Factory And Seeding Rules

- Every first-party Eloquent model has a valid factory or a documented
  technical exemption.
- Factory defaults satisfy constraints. Add only meaningful states and
  explicit relationship helpers.
- Fixed reference seeders are idempotent.
- Demo identities exist only in `local`, `demo`, or `testing` environments.
- Seeders never truncate or erase production data.
- `DatabaseSeeder` is environment-safe and repeatable.
- Seed files and images are local fixtures; tests and seeds do not use the
  public internet.

## Documentation Rules

- Documentation is part of the same change as implementation and tests.
- Update requirement records, the compliance matrix, implementation plan,
  architecture, deployment notes, and changelog whenever behaviour changes.
- `docs/requirements/compliance-matrix.md` may say `implemented and verified`
  only when a relevant check actually passed.
- Preserve useful historical specifications. Mark superseded instructions
  clearly rather than leaving contradictory active documents.
- Do not publish secrets or real personal data.

## Quality Gates

Applicable final gates are:

1. Composer validation and security audit.
2. Dependency compatibility inspection.
3. PHP syntax, Pint, and Larastan.
4. Full Pest suite and architecture tests.
5. Fresh isolated migration and complete seed.
6. Fixed-seeder idempotency.
7. NPM audit and production Vite build.
8. Route, config, and view cache smoke checks.
9. Critical browser flows, responsive layouts, keyboard focus, and console.
10. Final requirement, documentation, secret, and diff review.

Never claim a gate passed without executing it and observing the result.

## Git Workflow

- Work only on `main`.
- Inspect status, staged changes, unstaged changes, and untracked files before
  editing.
- Preserve unrelated user work. Never reset, discard, force-push, or rewrite
  history.
- In a dirty shared tree, use a temporary `GIT_INDEX_FILE` to stage only the
  attributable slice.
- Inspect the complete staged diff and `git diff --check`.
- Commit coherent implementation with its tests and documentation.
- Push only after required verification passes and report the observed result.
- Do not use GitHub API or GitHub MCP when repository instructions prohibit it;
  normal local Git remains valid.

## Definition Of Done

A change is done only when its requirements are implemented, authorized,
validated, localized, accessible, tested, documented, formatted, statically
analysed, and verified at the affected runtime boundaries. No debug code,
temporary stubs, unexplained skips, stale documentation, unrelated changes, or
known fixable defects may be hidden behind a completion claim.
