# Implementation Plan

## Current Audit: 2026-07-30

1. `verified` Inventory runtime, dependencies, routes, schema, indexes, foreign
   keys, models, factories, controllers, requests, policies, Blade, tests, and
   Git state.
2. `verified` Establish the engineering standard, architecture, compliance
   matrix, README, deployment guide, and changelog.
3. `verified` Remove manual container resolution from Form Requests and use
   Laravel method injection.
4. `verified` Enable strict Eloquent outside production and repair every
   incomplete projection exposed by the full suite.
5. `verified` Consolidate private response middleware and add global response
   security headers.
6. `verified` Remove PHP directives and framework class calls from Blade;
   use presenters or class-based Blade components.
7. `verified` Add architecture and response-security regression tests.
8. `verified` Run Pint, migrations, route inspection, production build, full
   suite, Composer validation/audit, NPM audit, and browser smoke.
9. `verified` Inspect a scoped staged diff, commit on `main`, push, and verify
   the remote hash and exact committed files. The commit containing this plan
   is the scoped delivery artifact.

## Recorded Debt

- Replace `ForumActor` with production authentication before a production
  launch; retain policies and actor-key migration compatibility.
- Decompose the largest catalog/presenter/action classes along feature
  boundaries only when their behavior is being changed and protected by focused
  tests.
- Migrate English view literals to translation keys before enabling a second UI
  locale.
- Establish production observability, backups, log retention, deployment cache,
  and post-deploy smoke checks in the target infrastructure.
- Introduce Redis, queue workers, external APIs, Livewire, Flux, Filament, or
  browser E2E only for an explicit product requirement with measured benefit.

Debt is not marked complete in the compliance matrix and must not be presented
as production readiness.
