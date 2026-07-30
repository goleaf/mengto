# PawCircle

PawCircle is a server-rendered Laravel application for pet profiles, social
connections, care journals, private medical records, lost-pet coordination,
marketplace listings, events, places, messaging, and smart-device management.

## Runtime Stack

- PHP 8.3 or newer
- Laravel 13
- Blade with reusable anonymous and class-based components
- Eloquent models and SQLite for the current local prototype
- Tailwind CSS 4, Sass, and Vite 8
- Pest 4 for feature and architecture tests

Livewire, Flux, Filament, Volt, and a JavaScript application framework are not
installed. Do not introduce them merely to satisfy a generic stack preference.
Re-evaluate them only for a feature with a measured need and a documented
migration plan.

## Source Of Truth

Read these documents before editing:

1. `AGENTS.md` for execution and repository rules.
2. `PRODUCT.md` for product boundaries and prototype status.
3. `DESIGN.md` for responsive and accessibility conventions.
4. `docs/requirements/laravel-engineering-standard.md` for the consolidated
   Laravel requirements.
5. `docs/requirements/compliance-matrix.md` for current evidence and debt.
6. `docs/architecture.md` for request, domain, persistence, and view boundaries.
7. `docs/implementation-plan.md` and the applicable feature plan/spec.

`AGENTS.md` is authoritative when agent instructions overlap. Product-specific
documents override generic engineering preferences when they define an explicit
runtime or business constraint.

## Local Setup

```bash
composer run setup
```

For the normal development processes:

```bash
composer run dev
```

The local application uses the URL configured in `.env`. Never commit `.env`,
tokens, production credentials, private uploads, or generated runtime caches.

## Verification

Run checks serially when they share generated assets or the SQLite database:

```bash
vendor/bin/pint --dirty
npm run build
php artisan migrate:status
php artisan route:list
php artisan test
composer validate --strict
composer audit
npm audit --omit=dev
```

Building assets while browser or feature tests read the Vite manifest can cause
a false missing-manifest failure. Finish `npm run build` before starting tests.

## Delivery

Work on `main`, preserve unrelated changes, stage only task-owned files, inspect
the staged diff, create one attributable commit, and push normally to the
configured upstream. Never reset, force-push, or absorb an unrelated untracked
path.
