# PawCircle

PawCircle is a privacy-aware pet community and care platform. It combines
owner-managed pet identities, social discovery, forums, expert consultations,
marketplace workflows, lost-pet coordination, private medical records, care
journals, and smart-device management in one server-rendered application.

## Technology Baseline

- PHP 8.5
- Laravel 13
- Livewire 4 class-based multi-file components
- Blade for server-rendered presentation
- Tailwind CSS 4 through the Vite plugin
- Eloquent persistence
- Pest 4 and Larastan

Flux, Flux Pro, Filament, Volt, and a client-side application framework are not
installed. Volt is prohibited. Optional packages require an evidence-backed
use case and must preserve the repository's operational constraints.

## Read First

1. [`AGENTS.md`](AGENTS.md)
2. [`docs/index.md`](docs/index.md)
3. [`docs/requirements.md`](docs/requirements.md)
4. [`docs/product-requirements.md`](docs/product-requirements.md)
5. [`docs/system-requirements.md`](docs/system-requirements.md)
6. [`docs/non-functional-requirements.md`](docs/non-functional-requirements.md)
7. [`docs/architecture.md`](docs/architecture.md)
8. [`docs/requirements/compliance-matrix.md`](docs/requirements/compliance-matrix.md)
9. [`docs/implementation-plan.md`](docs/implementation-plan.md)

`PRODUCT.md` and `DESIGN.md` remain the product and visual references.
Documents under `docs/superpowers/plans` are historical delivery records, not
current execution instructions.

## Local Installation

Requirements:

- PHP `>=8.5.0 <8.6.0` with Fileinfo, Intl, PDO and the remaining
  extensions reported by `composer check-platform-reqs`
- Composer 2
- Node.js `^20.19.0 || >=22.12.0`
- npm 10 or newer, using the committed `package-lock.json`

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
npm ci
npm run build
```

Use only local or demo data for seed operations:

```bash
php artisan db:seed
```

`DatabaseSeeder` refuses unsafe demo-account creation outside allowed
environments. See [`docs/seeding.md`](docs/seeding.md).

## Development

```bash
composer run dev
```

The normal development command starts the application, queue listener, log
viewer, and Vite process. No current user workflow depends on a queue worker,
scheduler, or long-running process; production may therefore deploy the web
application and built assets alone until a provider-backed integration
explicitly introduces another runtime. See [`docs/deployment.md`](docs/deployment.md).

## Quality Gates

Run shared SQLite checks serially:

```bash
composer validate --strict
composer audit --locked
vendor/bin/pint
PAO_DISABLE=1 vendor/bin/phpstan analyse --memory-limit=1G
php scripts/run-tests.php --compact
php scripts/verify-fresh-database.php
npm audit --package-lock-only --audit-level=high --registry=https://registry.npmjs.org/ --strict-ssl=true
npm run build
php artisan route:list --except-vendor
```

The fresh-database script creates and asserts an operating-system temporary
SQLite path before running any destructive migration. Never replace it with an
unverified `migrate:fresh` command. Browser verification and the exact final
acceptance commands are documented in
[`docs/testing.md`](docs/testing.md).

## Current Product Boundaries

Forum, experts, marketplace, lost/found, medical, care, devices, accounts, and
pet profiles use normalized Eloquent records. Social feed, connection,
friendship, group, event, messaging, and place interactions use
server-authoritative Actions and encrypted per-user `UserDomainState`
persistence; browser sessions are not their source of truth.

External payment, WebRTC, geocoding/routing, hardware-vendor, push, and AI
providers are not configured. The per-requirement status and exact effect are
recorded in
[`docs/requirements/compliance-matrix.md`](docs/requirements/compliance-matrix.md).

## Security

Do not commit `.env`, credentials, tokens, private uploads, database snapshots,
or runtime caches. Report vulnerabilities through the private process in
[`SECURITY.md`](SECURITY.md), not a public issue.

## Delivery

Work on `main`, preserve unrelated changes, stage only task-owned files, inspect
the staged diff, commit a coherent verified change, and push normally. Never
reset, discard unrelated work, rewrite history, or force-push.
