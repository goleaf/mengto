# Current-State Audit

## Current Repository-Wide Audit Snapshot

Audit date: 2026-08-30. This dated section supersedes the older point-in-time
baseline below for current stack, inventory, and open-risk decisions; the older
record remains preserved as historical evidence.

### Scope And Authority

- The root `AGENTS.md` is the only applicable instruction file. No nested
  `AGENTS.md` or `AGENTS.override.md` exists.
- The current 350 first-party Markdown files were classified: 244 product,
  engineering, audit, and plan documents plus 106 repository-local tooling
  mirrors. The exact per-path classification, routes,
  tables, roles, and complete first-party symbol/file lists are generated in
  `docs/audits/repository-inventory.md`. `docs/events/index.md` is canonical
  and `docs/events.md` is historical.
- Seven independent discovery scopes covered repository archaeology,
  documentation authority, dependencies/runtime, Laravel architecture,
  Livewire/frontend, database/security/integrity, and testing/factories/seeds.
  Their ownership and completion record is
  `docs/audits/repository-audit-work-ledger.md`.

### Current Inventory

| Surface | Observed 2026-08-30 |
| --- | ---: |
| Routes | 180 runtime / 174 from `--except-vendor`; 167 first-party actions |
| Controllers / middleware / Form Requests | 147 / 9 / 67 |
| Actions / Services / models / policies | 226 / 155 / 204 / 47 |
| Livewire PHP / renderable components / base component / form objects | 86 / 36 / 1 / 49 |
| Blade views / anonymous components | 357 / 246 |
| Migrations / named tables / isolated runtime tables | 139 / 218 / 219 |
| Model factories / seeders | 204 / 44 |
| Factory helpers / enum-backed states | 251 / 1,521 |
| Test/support PHP files / `*Test.php` files / Pest declarations | 138 / 129 / 1,051 |
| Resource JavaScript / CSS-SCSS files | 9 / 32 |

There are no first-party Jobs, Events, Listeners, Notifications, outbound HTTP
clients, webhook handlers, or scheduled tasks. Runtime-critical workflows are
synchronous. SQLite is the required local/test boundary; destructive
verification uses only operating-system temporary database paths.

### Stack And Initial Command Baseline

| Surface | Observed baseline |
| --- | --- |
| PHP / Composer / Node / npm | 8.5.8 / 2.10.2 / 26.4.0 / 12.0.1 |
| Laravel / Livewire | 13.29.0 / 4.4.2 |
| Tailwind / Vite / Laravel Vite plugin | 4.3.3 / 8.2.2 / 3.2.0 |
| Pest / PHPUnit / Larastan | 4.7.8 / 12.5.33 / 3.10.0 |
| Composer strict validation / locked audit / platform check | Passed; zero advisories |
| Official-registry npm audit | Passed; zero vulnerabilities; configured mirror audit endpoint returned 404 |
| Canonical Artisan suite | Serial execution required; a parallel wrapper attempt was invalid and received signal 11 |
| Current Vite production build | Passed with Vite 8.2.2 |
| Isolated fresh migration and repeat seed | Passed: 139 migrations, 219 tables, five users after each seed |
| Forum requirement generation | Failed at 128 MiB; 1 GiB run proved committed generated output stale |

### Accepted Foundational Findings

The immediate repair boundary is deliberately narrow:

- remove the application-wide administrator policy bypass while retaining
  only explicit policy capabilities;
- bind account-specific care and medical grants to the authenticated actor,
  audit the real bearer for every temporary grant, and consume no view on a
  mismatch;
- replace marketplace float calculations with exact minor-unit arithmetic;
- repair the canonical test/generator memory budget, deterministic compliance
  and seeding evidence, the false-negative caption assertion, passive booking
  request preparation, and repeated-input labels;
- patch only the vulnerable CommonMark and Vite/Nano ID dependency lines and
  declare direct runtime/engine requirements.

### Implemented Foundational Repairs

The current `AUD2` revalidation additionally established these bounded
repairs with failing-before and passing-after evidence:

- `UpdateForumCategorySettings` reauthorizes the actor, locks the category,
  persists category and localized translation state in one transaction, and
  invalidates category-tree caches only after success. `AdminDashboard`
  validates browser state and delegates the one operation.
- `AdoptionDemoSeeder` and `CollaborativeGuideDemoSeeder` now fail closed when
  invoked directly outside the configured demo environments, before querying
  or mutating domain rows.
- the repository inventory normalizes framework-generated route names,
  classifies all 350 Markdown paths including tooling mirrors, preserves
  `docs/index.md` and root-document authority, and produces byte-identical
  output on consecutive runs;
- the prohibited local `public/storage` symlink was removed after its target
  was verified to contain only the retained `.gitignore`; private media remain
  route-authorized and `config('filesystems.links')` remains empty.

- Policy authorization no longer has a global administrator bypass; real Gate
  tests cover private care, medical, device, order, search, and forum records.
- Medical/care downloads and shared care writes perform downstream permission
  and file/write work while the hashed grant remains locked. Failed operations
  do not consume views or record a success audit; bound and unbound grants use
  the actual authenticated bearer.
- Marketplace inputs normalize canonical decimal strings before validated data
  is extracted. Totals use checked minor units, enforce the order column width,
  accept legacy finite JSON float deposits compatibly, and roll back rejected
  transitions. The demo marketplace seeder fails closed outside configured
  demo environments.
- `php scripts/run-tests.php` clears cached configuration before Pest boots,
  avoiding accidental non-test database reuse. Browser package gates create a
  temporary SQLite database and loopback server; the underlying stateful Node
  scripts refuse direct execution without explicit mutation consent.
- Repository, compliance, seeding, and forum catalogues have deterministic
  parity checks. Forum catalogue verification is independent from the missing
  historical prompt-entry check, so the external history blocker cannot mask
  generated-data regressions.

Broader framework upgrades, schema/query redesign, browser-storage account
isolation, large Livewire/Action decomposition, CSS modernization, provider
integration, and CI expansion are assigned to the numbered later prompts in
the active canonical plan. They are repository work, not external
limitations.

Audit date: 2026-07-30

## Protected Repository State

- Branch: `main`
- Remote: `origin`
- Initial HEAD: `97c90fe8be07abea2f0b4675cc446d80030bc010`
- Initial tracked changes: none
- Pre-existing untracked path: `.agents/vendor/`
- Package manager: NPM with `package-lock.json`; no second frontend lock file
- Database: SQLite locally and in tests

The untracked `.agents/vendor/` directory is outside this work and must not be
staged or modified.

## System Inventory

| Surface | Baseline |
| --- | ---: |
| First-party PHP files under `app` | 431 |
| First-party PHP lines under `app` | 46,222 |
| Blade templates | 288 |
| Blade lines | 18,999 |
| Models | 63 |
| Factories | 63 |
| Migrations | 68 |
| Database tables | 71 |
| Seeders | 9 |
| Policies | 12 |
| Form Requests | 58 |
| Actions | 55 |
| Services | 53 |
| Controllers | 118 |
| Pest files | 24 |
| Application routes | 124 of 128 total |

Modules:

- identity and pet profiles;
- feed, connections, friendships, groups, events, messages, and places;
- forum and knowledge;
- expert profiles, services, bookings, consultations, and reviews;
- marketplace listings, reservations, orders, disputes, and reviews;
- lost/found cases, sightings, sectors, tasks, volunteers, alerts, and reports;
- private medical records and grants;
- private care journals and grants;
- private smart devices, readings, commands, events, automations, and grants.

## Detected Baseline Versions

| Tool | Observed |
| --- | --- |
| PHP | 8.5.8 |
| Composer | 2.10.1 |
| Laravel | 13.23.0 |
| Pest | 4.7.5 |
| Tailwind CSS | 4.3.3 installed, loose `^4.0.0` declaration |
| `@tailwindcss/vite` | 4.3.3 installed, loose `^4.0.0` declaration |
| Vite | 8.1.5 installed; 8.2.0 stable available |
| `laravel-vite-plugin` | 3.1.3 |
| Node | 22.23.1 |
| Livewire | Not installed |
| Static analysis | Not configured |
| Flux / Filament / Volt | Not installed |

Authoritative package metadata showed Laravel 13.23.0 and Livewire 4.3.3 as the
current stable compatible lines during this audit. Pest 5 existed, but Pest 4
is the required primary framework and remains intentional.

## Baseline Commands And Results

| Command | Result |
| --- | --- |
| `php -v` | PHP 8.5.8 |
| `composer validate --strict` | Passed |
| `composer audit --format=json` | 0 advisories, 0 abandoned packages |
| `composer why-not php 8.5` | No blocking package |
| `composer why-not laravel/framework ^13.0` | Laravel 13.23.0 already installed |
| `php artisan test --compact` | 116 passed, 3,881 assertions, 4.18 s |
| `npm audit --json` | 0 vulnerabilities |
| `npm run build` | Passed in 458 ms |
| `php artisan migrate:fresh --seed` on isolated SQLite | Passed, 68 migrations |
| Repeated `php artisan db:seed` on isolated SQLite | Failed: unique `users.email` for `test@example.com` |

Baseline production assets:

- Tailwind CSS: 39.10 kB / 7.59 kB gzip
- SCSS component CSS: 249.90 kB / 31.90 kB gzip
- JavaScript: 9.19 kB / 3.03 kB gzip

Coverage was not available because the PHP runtime had no Xdebug or PCOV
coverage driver. Browser testing existed through the connected Playwright tool,
not as a repository package.

## Critical Findings

| Finding | Severity | Requirement | Baseline evidence | Planned resolution |
| --- | --- | --- | --- | --- |
| `ForumActor` always returns `mia-carter` | Critical | PRD-IDENTITY-002, SYS-AUTH-002 | Fixed key in `app/Services/ForumActor.php` | Bind actor to authenticated user and deny guest mutations |
| No application route uses `auth` middleware | Critical | SYS-AUTH-001..004, SEC-DATA-001 | 124 web routes, zero auth middleware | Add production auth and protect mutations/private groups |
| Policies ignore the `User` and compare fixed actor strings | Critical | TEST-POLICY-001 | Medical/care/device/listing/forum policies | Make user actor key authoritative and test all denial paths |
| Medical, care, device confidentiality middleware changes headers only | Critical | PRD-MEDICAL-003, PRD-CARE-006, PRD-DEVICE-012 | Private groups remain guest reachable | Require auth except hashed scoped temporary routes |
| No localization files or translation calls | High | I18N-001..005 | Zero translation usages | Establish `en`, `lt`, `ru`; migrate visible strings |
| Livewire 4 absent | High | SYS-LIVEWIRE-001..003 | Composer package absent | Add class-based auth/account interactive flows |
| No static analysis | High | TEST-QUALITY-001 | No PHPStan/Larastan config | Add compatible Larastan and fix findings |
| `DatabaseSeeder` is not repeatable | High | SEED-REFERENCE-001, SEED-SAFETY-001 | Duplicate `test@example.com` failure | Split reference/demo seeders and use stable upserts |
| Many feature designs explicitly prohibited PHP tests | High | TEST-FEATURE-001 | Historical specs and only 24 test files | Mark historical constraint superseded and add coverage |
| User-facing text is hardcoded in Blade/PHP | High | I18N-002 | 288 Blade files, no translations | Mechanical extraction plus reviewed locale files |
| Product prototype state remains in session services | Medium | PRD-SOCIAL-* | `PrototypeState`, `EventState`, etc. | Preserve UX, document boundaries, migrate security-critical state first |
| CSS component bundle is large | Medium | PERF-ASSET-001 | 249.90 kB | Token alignment and measured incremental reduction |
| Application name remains `Laravel` | Low | OPS-DOCS-001 | `php artisan about` | Set safe PawCircle defaults |
| Public storage link absent | Informational | SYS-FILE-001 | `php artisan about` | Keep private disks; document optional public link |

## Local Database Incident During Audit

An intended isolated `migrate:fresh --seed` command was first invoked without
the nested process receiving the temporary `DB_DATABASE` value. It therefore
ran once against the local development SQLite database. The command completed
and recreated the repository's demo records, but local sessions and any manual
uncommitted database-only records were lost. No tracked file changed.

Subsequent destructive checks use an explicit temporary database path in the
command environment. This incident remains recorded for transparency.

## PHP 8.5 Initial Applicability

| Feature or change | Decision | Reason / location |
| --- | --- | --- |
| URI extension | Applicable to future user-controlled URL validation | Use only at an SSRF/open-redirect boundary |
| Pipe operator | Not applicable | Existing transformations are clearer as methods/collections |
| Clone-with | Candidate for immutable DTO variation | No current DTO justified it at baseline |
| `#[NoDiscard]` | Candidate for critical command results | Add only where ignored result creates a defect |
| `#[Override]` | Applicable to meaningful overrides | Use in materially changed compatible classes |
| `array_first` / `array_last` | Applicable when clearer | Replace custom empty checks only in touched code |
| Partitioned cookies | Not applicable | No legitimate embedded cross-site workflow |
| Persistent cURL sharing | Not applicable | Laravel HTTP client is the integration boundary |
| Non-canonical casts and legacy serialization deprecations | Applicable audit | Remove any first-party occurrence; no suppression |

## Laravel 13 Initial Applicability

| Feature | Decision |
| --- | --- |
| `^13.0` dependency and PHP 8.5 | Required |
| Origin-aware request forgery protection | Required; do not disable |
| Modern bootstrap middleware/exception configuration | Already used; review and retain |
| Modern class-based Livewire 4 | Required for intentional interactive flows |
| `Cache::touch` | Not used until a semantic TTL-extension case exists |
| Image API | Candidate only for a real image lifecycle |
| JSON:API, vector search, AI, Reverb, Octane, Horizon, Telescope | Not applicable without current product/operations need |

## Latest Observed Modernization State

| Surface | Final observed state |
| --- | ---: |
| PHP | 8.5.8 |
| Laravel | 13.23.0 |
| Livewire | 4.3.4 |
| Tailwind / Vite plugin | 4.3.3 |
| Vite | 8.2.0 |
| Pest / PHPUnit | 4.7.5 / 12.5.30 |
| Larastan / PHPStan | 3.10.0 / 2.2.7, level 5 |
| First-party models / factory coverage | 167 / 167 models covered |
| Explicit / enum-backed factory states | 145 / 897 |
| Migrations / tables | 101 / 182 |
| Application / total routes | 156 / 170 |
| Full PHP suite | 1,861 passed, 69,718 assertions |

Final production assets:

- font CSS: 1.31 kB / 0.32 kB gzip;
- Tailwind application CSS: 51.15 kB / 9.63 kB gzip;
- retained semantic SCSS CSS: 264.61 kB / 35.00 kB gzip;
- application JavaScript: 33.04 kB / 10.36 kB gzip;
- PhotoSwipe JavaScript: 58.82 kB / 17.06 kB gzip.

The Tailwind increase reflects the auth, responsive, forced-colors, and
localization states added during modernization. The semantic SCSS bundle was
not rewritten without measured proof because it is the current design-system
implementation for hundreds of components.

## Finding Resolution

| Baseline finding | Final disposition | Evidence |
| --- | --- | --- |
| Fixed `mia-carter` actor | Resolved for protected persistence | `User.actor_key`, `ForumActor`, auth/policy tests |
| No authenticated application boundary | Resolved | grouped route middleware and guest redirect tests |
| Policies ignored authenticated user | Resolved for critical models | policy implementations and authorization tests |
| Guest-reachable medical/care/device data | Resolved | route middleware, scoped grants, privacy tests |
| No localization architecture | Resolved structurally; linguistic review remains | `lang/{en,lt,ru}`, extraction scripts, localization tests |
| Livewire absent | Resolved | class-based multi-file auth/account components and tests |
| No static analysis | Resolved | Larastan level 5, zero errors |
| Non-repeatable seeding | Resolved | repeat-seed test and safe fresh-database script |
| Historical no-test instructions | Resolved | historical files are subordinate; full suite expanded |
| Hardcoded Blade/PHP messages | Resolved | localization extractor, parameterized presentation keys, and architecture gates |
| Session-backed social prototype state | Resolved | durable `PetProfile`, encrypted `UserDomainState`, Actions, and persistence tests |
| Large semantic SCSS asset | Measured and retained | final Vite asset report |
| Default application identity | Resolved | `config/platform.php`, environment-safe defaults |

## Current Audit Status

The first-pass defects that could be repaired without selecting external
hardware or a provider were implemented and regression-tested. The compliance
matrix for the original modernization remains its historical evidence. The
forum extension's authoritative matrix currently records 513 verified
requirements and 6,771 planned or discovered requirements, with no blocked or
intentionally-not-applicable records.

This modernization does not claim that physical hardware transport or
provider-side processing exists, and it does not claim that the complete forum
master specification is finished. Exact external boundaries and remaining
forum work are recorded per requirement rather than hidden in a generic
future-work list.
