# Production Modernization Plan

Plan date: 2026-07-30

## Active Delivery: Universal Runtime Entity Removal (`URE-001`)

Status: `2026-08-31 audit and architecture reconciliation in progress; publication NO-GO`.

The authenticated-identity work proved the real `User`/personal `SocialActor`
path, but a broader re-audit confirms production still contains a parallel
prototype entity store: named people and pets in translations and catalogues,
normal product routes backed by `PrototypeState`/`PreviewService`, and literal
group routes/allowlists. `URE-001` supersedes any historical prototype contract
that treats those records as product behavior. The complete plan is
`docs/plans/universal-registration-identity-and-demo-data-removal-plan.md`; the
exclusive read-only specialist scopes and finding dispositions are recorded in
`docs/audits/universal-runtime-entity-removal-work-ledger.md`.

The target invariant is one database-driven runtime: registration creates one
User, personal SocialActor, privacy-first setting and onboarding row with zero
user-owned content; authenticated chrome and self profile use that same actor;
and feeds, directories, messages, meetups, groups, posts, pets, places and
notifications render authorized persisted records or honest localized empty
states. Named fixture instances may exist only behind explicit seed/factory/test
infrastructure and ordinary canonical routes/policies.

The package phases are `URE-00` Git safety, `URE-01` inventory, `URE-02` domain
map, `URE-03` registration baseline, `URE-04` onboarding, `URE-05` shell/self
profile, `URE-06` prototype removal, `URE-07` database-backed surfaces,
`URE-08` seed/factory isolation, `URE-09` legacy/existing-data cleanup,
`URE-10` permanent guardrails, and `URE-11` verification/delivery. Production
changes begin only after this plan checkpoint. No historical migration will be
rewritten, no automatic production data purge is authorized, and no commit or
push is allowed while any material full gate is red.

## Active Delivery: Canonical Authenticated Identity (`AIR-001`)

Status: `2026-08-31 implementation and focused verification complete;
publication remains NO-GO while repository-wide gates are red`.

This re-audit began on clean `main` at
`d92cf5e74593a0a79b23c721ffc874fb76eba3d2`, equal to the observed
`origin/main`. That baseline already contains a partial authenticated-user
presenter and regression tests from an earlier mixed delivery. This delivery
must complete, independently verify, and publish the coherent identity slice
without claiming the pre-existing partial work as newly authored. Any later
unrelated bytes remain user-owned and must not enter this delivery's temporary
index. The attributable audit record is
`docs/audits/authenticated-identity-refactor-work-ledger.md`.

### 2026-08-31 universal-account scope reconciliation

The delivery scope now requires canonical dynamic routes only and an explicit
zero-default-pets invariant. The earlier compatibility assumption is
superseded: Mia/Scout/Nori preview routes, the parallel prototype owner page,
person-specific runtime translations, demo action targets, and per-user
prototype ownership defaults are production removal candidates. Optional demo
records may exist only as ordinary User, SocialActor, SocialActorSetting and
PetProfile rows created by explicitly environment-gated seeders.

The expanded audit confirmed that registration and onboarding do not currently
create pets, but this must be protected as a transactional invariant: one User,
one personal User-type SocialActor, one privacy-first SocialActorSetting, one
UserOnboarding, and zero PetProfile, manager, or pet-access-request rows. The
no-pet and add-later onboarding paths must preserve zero pets; pet creation
remains a separate authorized operation.

The migration deletes the hardcoded profile.mia and Scout/Nori route family,
migrates every real profile to members.show or the canonical PetProfile route,
removes the owner prototype presenter/view/translation subtree after proving
it unused, and replaces authenticated demo defaults with canonical persisted
records or honest localized empty states. This includes authorship, pets,
relationships, messages, notifications, care/device/booking choices and
counts—not only header text.

Affected boundaries now include registration/native fallback and rollback,
verification events, onboarding pet-choice behavior, AppShell, canonical
member/pet routing, ProfilePresenter cleanup, PrototypeState defaults,
authenticated content attribution, demo seeding guards, EN/LT/RU, architecture
hardcode guards, unseeded fresh-database verification, and final dead-code
review. No schema or dependency change is planned.

Acceptance requires an unseeded Andrej journey with exact authentication and
zero pets, no-pet and add-later journeys, explicit pet creation separately,
Alice/Bob/logout isolation, canonical owner/public/private/block profiles, no
demo-named production route, and repository-wide proof that remaining demo
names exist only in allowed seed/test/history locations. AIR-01 through AIR-10
remain the ordered ledger, expanded to specialists AIR-A through AIR-L; no item
may be marked verified before its observed checks pass.

### Confirmed defect and root cause

Registration already normalizes and persists the submitted `users.name`, creates
the exact `User` inside the account transaction, provisions that user's private
personal `SocialActor`, `SocialActorSetting`, and `UserOnboarding`, then returns
the same model for Livewire authentication and session regeneration. Historical
root-cause verification identified downstream substitution in
`ProfilePresenter::owner()` plus a header link to `profile.mia`. Current `main`
already routes authenticated shell identity through `AuthenticatedUserPresenter`
and the current actor URL, and its non-Mia shell tests pass in isolation.

The re-audit nevertheless confirms the migration is incomplete:

- authenticated/member controller call sites still depend on ambient
  `ProfilePresenter::owner()` even though the shell now ignores caller-supplied
  prototype owner arrays;
- the canonical member page omits owner-visible private pets, lacks honest
  aggregate and verification presentation, and returns policy `403` for a
  private actor where the privacy boundary requires an indistinguishable `404`;
- production profile settings and action redirects still target `profile.mia`,
  while explicit demo preview code retains translated Mia identity, fake
  profile facts, and prototype state;
- the base feature-test setup authenticates Mia globally, which can mask
  identity leaks, and the fresh-database verifier seeds instead of proving a
  no-demo registration journey;
- the registration form advertises a native `POST` fallback to a GET-only route,
  while failure rollback of mandatory identity initialization is unproved.

The result is a production identity split-brain:

```text
registered User / personal SocialActor
                x
translated Mia prototype / profile.mia
```

No new identity column or profile model is required. Historical Mia routes and
fixtures may remain only as named demo compatibility data and may never act as
an authenticated-owner fallback.

### Target architecture and migration strategy

- `users.id` and the canonical `User` account fields remain authoritative;
  `User::name` is the only current-account display-name source.
- `SocialActor.user_id -> User.id` remains the one-to-one personal social
  adapter. Its stable `SocialActor.actor_key` supplies the canonical
  `members.show` URL; a person's mutable name is never a route identity.
- A cohesive authenticated-user presenter accepts an explicit `User` and
  returns only header data: canonical name, safe initials/no-photo state,
  accessible profile label, and the current actor URL. It does not read
  translations or prototype/session state for identity and does not cache a
  user projection globally.
- `AppShell` resolves the authenticated principal at its presentation boundary
  and uses the explicit presenter. A guest receives guest controls and no Mia
  owner. Stale caller-supplied prototype arrays cannot define authenticated
  chrome.
- Canonical member presentation remains viewer-aware through
  `MemberProfileController`, `SocialActorPolicy`, `SocialActorAccess`, block
  checks, and `MemberProfileCatalog`. Owners may view their non-discoverable
  actor; other viewers remain subject to status, verification,
  discoverability, privacy, and block rules.
- Prototype owner pages, Scout/Nori pages, reports, and name-specific
  compatibility URLs are removed from the runtime route/view/presenter graph.
  Optional demo records use the same canonical dynamic routes as every other
  record. Production self-profile navigation never calls `profile.mia`, and
  `PrototypeState` never overrides the authenticated header or member profile.
- Fresh registrations require no seeder or backfill. One already-released
  2026-07-30 migration still recognizes `test@example.com` as the legacy Mia
  fixture and assigns its old friendly actor key. Repository governance
  forbids rewriting that production history. AIR therefore records it as the
  sole migration hardcode exception, blocks publication until production data
  is audited, and requires any affected non-demo row to be repaired through a
  separately reviewed forward data-compatibility change. The regression guard
  forbids that exception from spreading to any later migration.

### Affected boundaries

Application paths include the new authenticated-owner presentation boundary,
`AppShell`, `SiteHeader`, `HeaderActions`, canonical member controller/catalog,
profile presenter/demo preview boundary, avatar/owner identity components,
registration/authentication security hardening that is directly verified in
scope, EN/LT/RU copy, and architecture/security tests. Documentation includes
architecture, frontend, Livewire, security, authorization, testing, deployment,
requirements compliance, changelog, this plan, and the work ledger. Existing
forum/meetup paths are excluded except for attributable conflict-safe hunks
required by a shared route or test inventory.

### Ordered implementation ledger

| ID | Dependency | Owner | Affected paths/modules | Acceptance criteria | Required tests / verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AIR-01 | Repository contract and audit ledger | Principal plus read-only AIR-A..J | Auth, User, SocialActor, presentation, navigation, demo data, locales, tests | Current data flow and every Mia/prototype occurrence are classified with exact evidence; unrelated dirty work remains untouched | Git baseline, static call graph, specialist reports | completed | Revert only AIR planning/audit additions |
| AIR-02 | AIR-01 | Principal | Focused registration/header/member/security tests | RED protects exact-user provisioning, privacy defaults, locale invariance, account switching, logout, native registration fallback, rollback, and self/public access; already-green shell behavior is retained as baseline evidence | Focused Pest files fail only for intended current gaps before their production fixes | completed; universal journey and architecture guards added | Revert AIR tests only |
| AIR-03 | AIR-02 | Principal | Authenticated-user presenter, AppShell/header/avatar presentation | Header name comes from `User::name`; URL comes from that user's `SocialActor`; no avatar inherits another person's image; guest/logout render no prior identity; normal header never calls `profile.mia` | Presenter unit/feature tests, header DOM assertions, EN/LT/RU matrix, query bound | completed; focused verification green | Revert presenter/shell slice; clear views |
| AIR-04 | AIR-02..03 | Principal | Member profile controller/catalog, owner-profile/demo boundary | Canonical member/self page uses the viewed User and real member-since/pets/posts/social facts or honest empty states; owner-private access works; unrelated and blocked viewers remain denied; demo preview remains explicit | Self/public/private/block/IDOR/profile-count tests | completed; prototype owner routes/views removed | Revert profile slice; retain actor/settings/data |
| AIR-05 | AIR-02..04 | Principal | Registration/auth/Livewire lifecycle hardening | Exact created User remains authenticated after session rotation; transaction rollback is complete; enabled/disabled verification and onboarding order remain intact; credential forms cannot degrade to GET; no browser field can set authority | Registration matrix, session/event/rollback/stale-component/security tests | completed; focused verification green | Revert lifecycle hardening; do not alter account data |
| AIR-06 | AIR-03..05 | Principal | EN/LT/RU and accessibility copy, demo/prototype isolation | Runtime identity is data, labels are localized with placeholder parity, dynamic accessible profile labels are correct, person-specific demo copy is isolated, every remaining Mia/profile.mia occurrence is classified | Locale datasets, translation parity, architecture scans, accessibility assertions | completed; focused locale/profile and architecture checks green | Revert copy/components together; clear views |
| AIR-07 | AIR-03..06 | Independent read-only registration/auth/identity/actor/profile/demo/security/locale/test reviewers; principal dispositions | Frozen attributable diff | Reviewers explicitly answer source-of-truth/routing/demo/privacy questions; every reproduced in-scope finding is fixed and affected checks rerun | Reviewer reports and post-fix focused checks | completed; all nine final reviews dispositioned, attributable findings fixed, released-migration exception retained as publication no-go | Revert only an unsafe attributable fix |
| AIR-08 | AIR-07 | Principal | Documentation and complete repository boundary | Architecture, authorization, security, frontend, Livewire, testing, deployment, requirements/compliance and changelog describe observed behavior without claiming unrun gates | Documentation, generated-evidence, secret and full diff review | implemented; final reviewer dispositions and gate evidence pending | Revert AIR documentation only |
| AIR-09 | AIR-08 | Principal | Full locked application/runtime boundary | Pint, Larastan, focused/full Pest, fresh database, Composer, npm/Vite, route/view/config cache, browser where available, and diff gates run with observed results | User-specified commands plus applicable repository gates | NO-GO: focused identity checks and fresh verifier pass; full Pest and full Larastan remain red on unfinished repository modules | Stop publication on material failure |
| AIR-10 | AIR-09 | Principal | Temporary Git index on `main` | Only AIR-owned paths/hunks are staged; staged diff/check are clean; coherent commit is created and non-force pushed only after gates | Temporary-index staged review, commit SHA, `git push origin main` result | blocked by AIR-09; no commit or push authorized | Do not commit/push on failure; after publication use an ordinary reviewed revert |

### Observed verification and publication decision — 2026-08-31

- The current post-review identity slice passes 430 tests with 3,443
  assertions, covering registration/authentication, verification modes,
  onboarding and zero-pet behavior, canonical shell identity, EN/LT/RU member
  presentation, self/public/private/block profiles, demo-seeder isolation,
  honest share/photo fixtures and hardcode guards.
- The earlier complete focused registration/profile/social group passed 236
  tests with 1,419 assertions. The corrected isolated fresh verifier passed
  with exact Andrej authentication, one actor/setting/onboarding, completed
  add-later onboarding and zero pet/manager/access-request rows before and
  after repeat demo seeding.
- Targeted Larastan for the changed identity/presenter/Livewire/seeder boundary
  is green. The required full Larastan run remains red with 49 findings in
  unrelated unfinished EventCompetition/Place modules.
- The required final-tree full Pest run remains red: 3,224 tests executed,
  3,027 passed, 55 failed, 142 errored, and 124,744 assertions. The ordinary
  wrapper first encountered the host's intermittent signal 11; the repeat with
  CLI opcache disabled completed and produced these counts. Failures remain
  dominated by missing unfinished EventCompetition/Place/Portal classes and
  stale repository-wide generated/audit contracts; this delivery does not
  weaken those gates.
- Composer validation/audit/platform checks, npm audit/build, route/view cache,
  and the fresh verifier were observed green. The connected canonical-identity
  browser journey passes with Andrej's exact actor URL, one H1, 44-pixel target
  coverage, visible keyboard focus, forced colors, reduced motion, zero
  overflow, no demo copy, clean logout, and zero console/resource errors. The
  broader page-identity browser remains red at the unfinished event workspace.
- Because repository-wide Pest, Larastan, generated evidence, and connected
  browser proof are not all green, AIR-001 must not be committed or pushed.

### Acceptance and rollback

The decisive journey is a fresh, unseeded database registering `Andrej Prus`,
authenticating that exact row, completing the configured verification and
onboarding gates, rendering `Andrej Prus` in authenticated chrome, and opening
that user's stable member-actor URL with no inherited Mia avatar, location,
bio, pets, posts, counts, or badges. A second `Anna Kowalska` session must
replace every current-account value, and logout must remove the prior identity.
The same account name remains unchanged under EN/LT/RU while only interface
copy changes.

Rollback is the eventual single AIR-owned commit. The package adds no schema,
destructive data operation, dependency, queue, cache, or public-media change.
Clear compiled route/config/view caches after rollback; preserve all user,
actor, setting, onboarding, publication, relationship, block, and demo rows.

## Active Delivery: Full Stack And Dependency Upgrade (Prompt 03)

Status: `mandatory discovery completed; implementation authorized after this
plan checkpoint` on 2026-08-30.

This delivery began on `main` at
`9540fe83756833ae1c6d22053e883a07dca9f014`, three commits ahead of
`origin/main`, with 478 pre-existing changed paths across staged, unstaged and
untracked states. Those bytes remain user-owned. The task-owned work ledger is
`docs/audits/full-stack-dependency-upgrade-work-ledger.md`. Eight required
read-only analysts completed Composer, PHP 8.5, Laravel 13, Livewire/Flux,
Tailwind/Vite, testing, supply-chain and reproducibility discovery before any
production-code change. The principal owns integration, TDD, documentation,
runtime remediation, an attributable temporary index, publication decisions
and every finding disposition.

### Exact current and target matrix

Authoritative package metadata was refreshed on 2026-08-30. A range in the
target column is the supported contract; the resolved value is the exact lock
target. All retained packages are stable.

| Surface | Current declaration / resolved | Target declaration / resolved | Decision |
| --- | --- | --- | --- |
| PHP | `>=8.5.0 <8.6.0` / `8.5.8` CLI and FPM | retain PHP 8.5 contract; host patch target `8.5.10` | Major baseline is met; no platform emulation. Host panel currently pins 8.5.8, so patch replacement requires a supported panel build and ABI validation. |
| Composer | 2.10.2 | 2.10.3 | Repository locks remain usable; host self-update and missing verification keys are an operator/toolchain exception, never a Composer platform override. |
| Laravel | `^13.0` / `13.29.0` | retain / `13.29.0` | Latest stable compatible 13.x; no framework lock churn. |
| Livewire | `^4.3.4` / `4.4.2` | retain / `4.4.2` | Latest stable compatible 4.x; add project generator config for class-based components. |
| Flux / Flux Pro / Volt | absent | absent | No requirement or licence evidence; do not add. |
| Tailwind CSS | `^4.3.3` / `4.3.3` | retain / `4.3.3` | Latest stable CSS-first release. |
| `@tailwindcss/vite` | `^4.3.3` / `4.3.3` | retain / `4.3.3` | Latest stable compatible plugin. |
| Vite | `^8.2.2` / `8.2.2` | retain / `8.2.2` | Latest stable supported release. |
| `laravel-vite-plugin` | `^3.1.3` / `3.2.0` | retain / `3.2.0` | Latest stable and requires Vite 8. |
| Node | `^20.19.0 || >=22.12.0` / `26.4.0` | `>=22.12.0 <27.0.0`; `.node-version=26.4.0` | `concurrently@10` invalidates the documented Node 20 floor. |
| npm | `>=10` and `npm@12.0.1` / `12.0.1` | `>=12.0.2 <13`; `npm@12.0.2` | Latest stable package-manager patch; one package manager and one v3 lock. |
| Pest / PHPUnit | Pest `^4.7` / `4.7.8`; PHPUnit `12.5.33` transitive | retain | Repository and Laravel 13 require Pest 4 / PHPUnit 12; reject Pest 5. |
| Larastan / PHPStan | `^3.10` / `3.10.0`; `2.2.9` transitive | retain | Latest direct stable compatible stack. |
| Pint / Mockery / Collision / Pao | `1.30.5` / `1.6.15` / `8.9.5` / `1.1.4` | retain | Current stable compatible releases. |
| Lucide Blade icons | `^1.26` / `1.26.32` | `^2.0` / `2.0.9` | Only justified direct Composer major; no removed existing icon names, but package asset layout changed. |
| Instrument Sans | mutable Google build fetch | `@fontsource/instrument-sans:^5.3.0` / `5.3.0` | Lock font bytes and remove clean-build network dependence; OFL-1.1. |
| Bacon QR / Faker / Intervention Image / Tinker | `3.1.1` / `1.24.1` / `4.3.2` / `3.0.2` | retain | Current and used or operationally required; Faker stays production because approved no-dev seeding uses factories. |
| Boost / Pail | `2.7.0` / `1.2.7` | retain | Current Laravel 13-compatible development tooling. |
| PhotoSwipe / Sass Embedded / concurrently | `5.4.4` / `1.103.1` / `10.0.5` | retain | Current stable; concurrently establishes Node 22+ floor. |

### Accepted blockers, removals and exceptions

| ID | Category | Evidence and decision | Resolution evidence / owner / condition |
| --- | --- | --- | --- |
| FS03-DEP-01 | required upgrade | `mallardduck/blade-lucide-icons:^1.26` alone blocks 2.0.9; v2 moves core SVGs to `resources/svg/icons` | Targeted Composer update with scripts disabled, one-package lock diff, icon contract/audit and browser sampling; principal |
| FS03-DEP-02 | required replacement | Vite's Google provider fetches mutable CSS/font bytes outside npm locks on a clean build | Add Fontsource 5.3.0, use the plugin's `fontsource()` provider, prove clean build and no Google URL; principal |
| FS03-DEP-03 | removal | `php-http/discovery` is absent but pre-authorized as a Composer plugin | Remove stale permission; Composer validate/install/audit; principal |
| FS03-SUPPLY-01 | required repair | 148/148 npm `resolved` URLs use undeclared `registry.npmmirror.com`; its advisory endpoint returns 404 | Project `.npmrc` pins official registry/TLS, npm 12 regenerates lock, host/integrity/version review and official audit pass; principal |
| FS03-REPRO-01 | required repair | `composer setup` uses mutable `npm install`; Node 20 claim conflicts with concurrently 10 | Use `npm ci`, encode Node/npm bounds and exact `.node-version`, synchronize setup/deployment docs; principal |
| FS03-LW-01 | required configuration | Package default `make:livewire` creates prohibited emoji SFC because app config is absent | RED config contract then minimal complete `make_command` override for class/view convention; principal |
| FS03-TEST-01 | existing gate failure | Shared-tree Larastan baseline has 34 findings in unrelated active event/place work | No broad ignore or ownership theft. Re-run on frozen tree; only task regression is in scope. Owners of the existing modules must clear findings before release. |
| FS03-TEST-02 | temporary environment exception | No PCOV/Xdebug, so the 90% coverage command cannot start | `TEST-COVERAGE-001`; server/CI operator installs PCOV only for test SAPI, then canonical wrapper must pass before release. |
| FS03-ENV-01 | temporary host exception | Official PHP patch is 8.5.10, but the custom panel build script pins 8.5.8 and PHP/extension binaries are not RPM-owned | Server operator supplies a reviewed 8.5.10 panel build, backs up current binaries/config, validates every PECL ABI, FPM config/socket and HTTP smoke before release. PHP 8.5 major target remains met meanwhile. |
| FS03-ENV-02 | required host remediation | Live runtime reports `APP_ENV=local`, an insecure session cookie, FPM `display_errors=On`, PRC INI timezone and root-owned runtime caches | Safely change only runtime configuration/ownership, cache as `www`, reload FPM only after config test, and verify sanitized HTTP attributes; principal/operator |
| FS03-TOOL-01 | temporary host exception | Composer 2.10.2 lacks configured tags/dev verification keys; 2.10.3 is current | Server operator installs official keys and checksum-verified Composer 2.10.3 before release; repository remains free of a bundled alternate Composer. |
| FS03-OPS-01 | retained release blocker | No CI/container, atomic release switch or authenticated readiness probe exists | Keep deployment NO-GO and existing `SYS-RUNTIME-002` / `OPS-DEPLOYMENT-001` ownership; dependency work must not claim production release readiness. |

Rejected upgrades: Pest 5 would violate the mandatory Pest 4 baseline and
drive PHPUnit 13 plus broad transitive churn; Flux, Volt, Filament, Horizon,
Fortify, Socialite, Octane, Reverb, Telescope, Sanctum, Scout and Cashier have
no current requirement or operational boundary. Full-page `Route::livewire()`
migration is non-blocking and belongs to a separately tested route change.

### Ordered implementation ledger

| ID | Dependency | Owner | Affected paths/modules | Acceptance criteria | Required tests / verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| FS03-01 | Repository contract and eight analyst reports | Principal | Work ledger, manifests, runtime, docs | Baseline, exact versions, findings, decisions and ownership are recorded before production edits | Git inventory, Composer/npm/runtime diagnostics, analyst reports | completed | Revert only task planning additions |
| FS03-02 | FS03-01 | Principal | `tests/Feature/DependencyManifestTest.php`, architecture/icon contracts | RED proves official lock provenance, locked installer, Node floor, local font, class generator and Lucide 2 asset contract | Focused tests fail for the intended missing contracts | pending | Revert test additions |
| FS03-03 | FS03-02 | Principal | `composer.json`, `composer.lock`, `scripts/icon-system-audit.php` | Lucide 2.0.9 is the only Composer package changed; stale plugin permission is gone; all used icon names resolve | Composer validate/audit/dry install; icon audit and contract | pending | Restore constraint and regenerate lock with targeted Composer command; clear view/icon caches |
| FS03-04 | FS03-02 | Principal | `.npmrc`, `.node-version`, `package.json`, `package-lock.json`, `vite.config.js` | Official registry only, npm scripts disabled, strict engines, Node/npm matrix true, Fontsource bytes locked, no remote font fetch | Lock host/version/integrity diff, clean isolated `npm ci`, audit, build, JS syntax and browser typography | pending | Restore previous manifest/config through npm, regenerate prior lock; previous Google provider is application-only rollback |
| FS03-05 | FS03-02 | Principal | `config/livewire.php` | New generators produce normal class plus separate view without emoji/SFC defaults; existing runtime behavior unchanged | Focused config/architecture and representative Livewire suites; config/route/view caches | pending | Remove app override and clear config cache |
| FS03-06 | FS03-03..05 | Principal | `.env` runtime only, PHP INI/runtime directories, README and deployment/testing/frontend/Livewire/Tailwind/security docs | Production environment and cookie/error/timezone/ownership controls are observed; docs match exact stack/install semantics; no secrets enter Git | FPM config test, sanitized cookie check, ownership probes, `artisan about`, docs/diff review | pending | Restore exact backed-up runtime lines; reload only after configuration test; source docs use normal revert |
| FS03-07 | FS03-03..06 | Principal | Locked dependency/build/application boundary | Targeted gates, boot/routes/caches, isolated migration+seed+idempotence, clean installs, build and applicable browser checks execute; baseline failures are attributed exactly | Commands in the next subsection | pending | Stop publication; revert unsafe task-owned group only |
| FS03-08 | FS03-07 | Three independent read-only reviewers; principal dispositions | Frozen attributable diff and command evidence | Dependency resolution, runtime compatibility and gate integrity reviewers disposition every finding; valid defects fixed and rerun | Reviewer reports, focused post-fix checks | pending | Revert reviewer-induced unsafe fix only |
| FS03-09 | FS03-08 | Principal | Plan, ledger, compliance matrix, changelog and affected first-party docs | Documentation states only observed results; generated evidence remains generator-identical | Documentation, generator, secret and complete diff review | pending | Revert documentation slice and regenerate prior generated evidence |
| FS03-10 | FS03-09 | Principal | Temporary Git index on `main` | Only task-owned paths/hunks staged; staged diff/check clean; commit created only after applicable gates; push only if all required gates and remote safety pass | Temporary-index staged diff, `git diff --check`, commit hash, `git push origin main` result | pending | Do not commit/push on material failure; after publication use an ordinary reviewed revert |

### Checkpoints and reproducible command set

Composer operations run serially because coincident diagnostics produced two
host-level signal-11 failures while sequential reruns passed. Targeted updates
use `--no-scripts`; package discovery and any required project scripts run
separately under the runtime account after diff inspection. Lock files are
never hand-edited. npm 12 and `package-lock.json` are the sole JavaScript
resolution authority.

```bash
php -v
php -m
composer validate --strict
composer diagnose
composer check-platform-reqs --lock
composer audit --locked
composer outdated --direct --locked
composer install --dry-run --no-scripts --no-plugins --no-interaction
composer install --dry-run --no-dev --no-scripts --no-plugins --no-interaction --classmap-authoritative

npm ci --ignore-scripts --registry=https://registry.npmjs.org/ --strict-ssl=true
npm ls --all
npm audit --package-lock-only --audit-level=high --registry=https://registry.npmjs.org/ --strict-ssl=true
npm run build

php scripts/icon-system-audit.php --check
php scripts/run-tests.php --compact tests/Feature/DependencyManifestTest.php tests/Feature/IconSystemContractTest.php tests/Feature/FrontendArchitectureTest.php tests/Feature/ArchitectureComplianceTest.php
vendor/bin/pint --test
PAO_DISABLE=1 PHPSTAN_TURBO=0 vendor/bin/phpstan analyse --memory-limit=1G --no-progress
php scripts/run-tests.php --compact
php scripts/verify-fresh-database.php
scripts/artisan-runtime about
scripts/artisan-runtime config:cache
scripts/artisan-runtime route:cache
scripts/artisan-runtime view:cache
php artisan route:list --except-vendor
npm run test:browser:a11y
```

The compatibility fallback never weakens PHP 8.5, Laravel 13, Livewire 4,
Tailwind 4, Vite 8 or stable-only constraints. If a selected package becomes
externally unavailable, retain the last verified lock, stop publication,
record exact registry/package evidence under the corresponding `FS03-*` item
and resume only with an authoritative stable release or a fully implemented,
tested framework-native replacement. No prerelease, fork, mutable branch or
platform override is an accepted fallback.

## Active Delivery: Complete Laravel 13 Framework Modernization

Status: `protected baseline recorded; mandatory read-only specialist discovery
starting` on 2026-08-30.

This delivery reconciles the current application with Laravel 13 bootstrap,
configuration, routing, middleware, provider, exception, authentication,
session, cache, queue, filesystem, mail, logging, testing, and deployment
boundaries without replacing project-specific behavior with skeleton defaults.
It began on `main` at `9540fe83756833ae1c6d22053e883a07dca9f014`,
three commits ahead of `origin/main`, with 478 staged paths, 27 additional
unstaged paths, and 11 untracked paths already present. Every pre-existing byte
remains user-owned. Discovery and review agents are read-only; the principal
owns the plan, cross-module decisions, production edits, tests, documentation,
isolated staging, commit, and safe push.

### Laravel 13 specialist work ledger

Every analyst returns: inspected scope; exact files, symbols, routes, tables,
and workflows; severity-ranked confirmed findings with evidence; suspected
findings needing principal validation; missing evidence; implementation order;
tests and commands; and change risks. Reviewers additionally provide exact
locations, reproducible failure scenarios, proposed corrections, and a final
verdict. Agents do not edit the shared tree.

| ID | Agent | Exclusive scope and expected output | Dependencies | Status |
| --- | --- | --- | --- | --- |
| L13M-A1 | Laravel Bootstrap and Configuration Analyst | `bootstrap/app.php`, `bootstrap/providers.php`, all `config/**`, `public/index.php`, `artisan`, `.env.example`, Composer discovery; bootstrap/config migration map with preserve/replace/remove decisions and boot/cache tests | Repository contract, canonical architecture/security/deployment docs, protected Git baseline | assigned wave 1 |
| L13M-A2 | Routing and Middleware Analyst | `routes/**`, bootstrap middleware callbacks, `app/Http/Middleware/**`, bindings, limiters, trusted hosts/proxies, CSRF/origin, maintenance and `/up`; route/middleware findings map, registration changes, security/regression tests | Repository contract and portal/auth boundaries | assigned wave 1 |
| L13M-A3 | Service Container and Provider Analyst | `app/Providers/**`, provider discovery, bindings, macros, events, observers, boot side effects and resolution paths; provider/binding map, lifecycle fixes, resolution tests | Repository contract and current container/provider code | assigned wave 1 |
| L13M-A4 | Exception, Logging, and Response Analyst | bootstrap exception callbacks, exception classes, error pages/API responses, `config/logging.php`, request context/redaction; error taxonomy, safe response/logging behavior, tests | L13M-A1 bootstrap evidence and security/logging contracts | queued wave 2 |
| L13M-A5 | Authentication, Session, and Security Configuration Analyst | auth/session/cookie/password reset/verification/login/logout/rate-limit/CSRF/origin configuration and flows; security findings, framework changes, regression tests | L13M-A2 route/middleware evidence and canonical auth/security contracts | queued wave 2 |
| L13M-A6 | Laravel 13 Feature Applicability Analyst | current Laravel 13 attributes, cache TTL, images, Resources, queues, origin, health, AI/vector and first-party integrations; applicability matrix with approved candidates and exact rejections | Current dependency/runtime inventory and L13M-A1..A3 maps | queued wave 2 |
| L13M-A7 | Framework Regression Test Analyst | existing boot/route/middleware/provider/auth/session/cache/filesystem/mail/queue/exception/cache-build tests; behavior-focused regression plan and critical smoke commands | L13M-A1..A6 accepted evidence | queued wave 3 |
| L13M-R1 | Laravel Architecture Reviewer | Frozen attributable diff across bootstrap/config/providers/middleware/routes/exceptions; framework correctness, lost-behavior risks, cache/boot reproduction, corrections | Completed implementation and frozen review package; independent of analysts | pending |
| L13M-R2 | Laravel Security Configuration Reviewer | Frozen auth/session/cookie/CSRF/origin/rate/log/proxy/host/health diff; realistic attack review, config-cache behavior, verified-control list | Completed implementation and frozen review package; independent of analysts | pending |
| L13M-R3 | Framework Regression Reviewer | Frozen tests/smokes/cache/deployment evidence; CLI/HTTP behavior, middleware order, provider side effects, route/error drift, readiness verdict | Completed implementation and frozen review package; independent of analysts | pending |
| L13M-P | Principal integrator | Reproduce material findings; update this section with stable implementation items, acceptance, commands and rollback before production edits; execute TDD, fixes, documentation, isolated Git publication | L13M-A1..A7 reports | in progress |

The detailed implementation ledger, accepted/rejected finding dispositions,
Laravel 13 applicability matrix, checkpoint results, review dispositions, and
publication result will be maintained in this same section after discovery and
before any production-code change.

## Active Delivery: PHP85 Repository-Wide Source Modernization

Status: `protected baseline recorded; read-only specialist discovery starting`
on 2026-08-30.

This delivery inspects every first-party PHP runtime, application, migration,
factory, seeder, script, and test boundary for PHP 8.5 correctness. It accepts
only evidence-backed compatibility, warning, type, framework-contract, or
maintainability changes; broad syntax churn, persisted-value changes, public
shape changes, and mechanical `strict_types` additions are prohibited. The
principal owns every cross-module decision and edit. Analysts and reviewers
remain read-only unless this ledger later delegates one non-overlapping fix.

The task began on `main` at
`9540fe83756833ae1c6d22053e883a07dca9f014`, three commits ahead of
`origin/main`. The shared tree already contained 478 staged paths, 25 unstaged
paths, six untracked paths, and 20 paths with both staged and unstaged changes.
Every pre-existing byte remains user-owned. Publication may use only an
attributable temporary `GIT_INDEX_FILE` after complete verification; no
existing index or working-tree change may be reset, cleaned, stashed, or
silently included.

### PHP85 specialist work ledger

| ID | Agent | Exclusive read-only scope | Expected structured output | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| PHP85-A1 | PHP Deprecation and Warning Scanner | All first-party PHP, bootstrap, migrations, seeders, scripts, custom packages, full-error runtime output, version shims and suppression | Severity catalogue with exact reproductions, first-party/vendor separation, fixes, tests, unavailable evidence and risks | Repository contract, runtime and source inventory | assigned wave 1 |
| PHP85-A2 | Type Safety Analyst | Native/docblock parameter, return, property, closure, iterable, array-shape and collection contracts across callers, schema and tests | Prioritized type-hardening map, safe strict-types candidates, DTO/value-object candidates only where justified, inheritance risks, tests and commands | Repository contract and schema/framework contracts | assigned wave 1 |
| PHP85-A3 | Serialization, Reflection, and Magic-Method Analyst | Serialized/cache/session/queue objects, magic methods, reflection, attributes, proxies, mocks and closures | Serialization risk report, compatibility/invalidation needs, round-trip tests, missing evidence and change risks | Runtime configuration and persistence contracts | assigned wave 1 |
| PHP85-A4 | Modern PHP Design Analyst | Closed states, immutable transport data, mapping/switch code, URLs and critical results; PHP 8.5 URI, clone-with, pipe, `#[NoDiscard]`, `#[Override]`, array helpers | Applicability matrix with approved/rejected candidates, reasons, locations, migration cost and tests | A1-A3 evidence is informative but not blocking | queued wave 2 |
| PHP85-A5 | Framework Contract Compatibility Analyst | Laravel, Livewire, Pest/PHPUnit and Eloquent extension points, overrides, lifecycle hooks, commands, policies, notifications/jobs and strict external boundaries | Parent/interface compatibility checklist, exact signature defects, proxy/mock risks and callback tests | Installed locked package contracts | queued wave 2 |
| PHP85-A6 | PHP Test Compatibility Analyst | Pest/PHPUnit config, bootstrap, providers/datasets, mocks, warning handling, helpers, process isolation, skips and assertion semantics | Test-migration list, strict-error reproductions, count/skip comparison, helper fixes and missing language regressions | A1 runtime evidence is informative but not blocking | queued wave 2 |
| PHP85-R1 | PHP Type and Runtime Reviewer | Frozen attributable PHP diff, public boundaries, casts, overrides and runtime output | Severity-ranked runtime/type findings with callers, edge values, exact failure scenarios and PHP 8.5 cleanliness verdict | Implementation freeze and targeted evidence | pending |
| PHP85-R2 | Backward-Compatibility Reviewer | Frozen enum/value/serialization/session/cache/queue/API/model/migration/extension-point diff and rollout | Breaking-change findings, old-to-new traces, migration/rollback requirements and approved exceptions | Implementation freeze and compatibility tests | pending |
| PHP85-R3 | PHP Test and Deprecation Reviewer | Frozen tests/config/static-analysis/warning evidence, suppressions, skips and regression coverage | Verification gaps, exact strict-error reruns, required tests/config fixes and evidence verdict | Implementation freeze and targeted evidence | pending |

Every analyst report must state inspected scope and symbols, confirmed findings
with severity/evidence, suspected findings requiring principal validation,
missing coverage, implementation order, exact tests/commands, and change risk.
The principal will reproduce every material claim before it becomes an
implementation item. After discovery, this section will receive the ordered
stable `PHP85-*` implementation ledger, accepted/rejected applicability
matrix, serialization rollout decision, test-first sequence, exact gates,
review dispositions, documentation evidence, rollback and publication state
before any production-code edit.

## Active Delivery: BFA Blade And Frontend Boundary Audit

Status: `planned; read-only specialist discovery starting` on 2026-08-30.

This repository-wide delivery makes Blade a passive presentation layer,
audits reusable Blade components, records Flux as absent/not applicable,
retains only Livewire-provided Alpine, repairs custom JavaScript lifecycle
defects, and adds permanent architecture/browser ratchets. The complete
inventory, selected boundaries, six exclusive discovery scopes, three
independent review scopes, TDD sequence, rollback and publication rules are in
`PLANS.md`.

The audit began on `main` at
`9540fe83756833ae1c6d22053e883a07dca9f014`, three commits ahead of
`origin/main`, in a materially dirty shared tree. All pre-existing staged,
unstaged and untracked content remains user-owned. The principal owns every
edit and may publish only an attributable temporary-index slice after the
exact candidate passes all applicable gates.

| ID | Dependency | Owner | Affected paths/modules | Acceptance criteria | Required verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| BFA-01 | Repository contract and mandatory docs | Principal | `PLANS.md`, this plan, protected baseline | Scope, ownership, agent ledger, acceptance, tests and rollback exist before production edits | Git/source inventory and planning diff | in progress | Revert planning additions only |
| BFA-02 | BFA-01 | Six exclusive read-only specialists; principal dispositions | Blade, components, manifests, Alpine, JavaScript, frontend tests | Every material finding has file:line evidence and an owning server/presentation boundary | Specialist reports and principal reproduction | pending | Documentation-only disposition |
| BFA-03 | BFA-02 | Principal | Architecture, feature and browser tests | RED contracts cover all requested forbidden Blade boundaries, Flux/Alpine applicability and navigation lifecycle | Observed intended RED, then focused GREEN | pending | Revert attributable tests |
| BFA-04 | BFA-03 | Principal | Smallest presenter/Livewire/controller/Blade/component/JS slices | Passive escaped localized Blade, no hidden lazy load, explicit component APIs and idempotent init/teardown | Focused render/query/localization/JS/browser tests | pending | Revert coherent test/fix pairs |
| BFA-05 | BFA-04 | Principal | Required docs, generator/source and changelog | Documentation and compliance evidence match only observed implementation/results | Generator parity and docs/diff review | pending | Revert docs and regenerate prior evidence |
| BFA-06 | BFA-04..05 | Three independent reviewers; principal dispositions | Frozen attributable diff | Every critical/important finding is fixed or rejected with evidence and affected checks rerun | Independent reports and post-fix reruns | pending | Revert unsafe finding-specific fix |
| BFA-07 | BFA-06 | Principal | Exact attributable candidate on `main` | Full required gates pass, isolated index contains no foreign work, commit and fast-forward push succeed | Pest/Pint/Larastan/audits/migration/seed/Vite/cache/browser/diff/secret/push evidence | pending | No commit/push on a failed material gate; normal revert only after publication |

Implementation is test-first in order `BFA-01` through `BFA-07`. Flux is not
installed and will not be added without a separate current requirement and
licence evidence. Existing zero-occurrence scans are only baselines; they do
not close expression-level, lazy-loading, duplication or lifecycle review.

## Active Delivery: Resumable Account Onboarding Foundation

Status: `Prompt 06 canonical pet relationship implementation, focused
verification and independent re-review are complete; a concurrent process
published the pre-final combined candidate as c2b081a without principal
approval; full repository and connected Chrome gates remain red, so no
follow-up publication or complete onboarding release is claimed` on
2026-08-30.

This delivery revalidates and hardens the already-present server-authoritative,
resumable onboarding boundary between conditional email verification and
ordinary portal access. The full current-state audit, selected one-to-one
state design, state machine, database, route/middleware, Livewire,
localization, accessibility, test, rollout, rollback, acceptance, and exact
task ledger are in
`docs/plans/onboarding-implementation-plan.md`. Specialist ownership and
finding dispositions are recorded in
`docs/audits/onboarding-audit-work-ledger.md`.

The selected compatibility rule is additive and fail-safe: only newly
registered accounts receive a `user_onboardings` row; an existing account with
no row retains its current portal access and social/privacy values. New account
creation must atomically provision the durable state plus one privacy-first
canonical user social actor/settings row. Pet creation remains optional and
continues exclusively through the existing duplicate-aware pet workflow.

Current-checkout discovery reproduced five prerequisite defects selected for
this package: configured verification can be bypassed by a stale Livewire
snapshot unless the component and direct Actions re-check it; backslash-style
intended URLs and password confirmation do not share one safe redirect
boundary; pet creator-FK evidence disagrees with revoked/expired manager
authority and private pet actors can leak into social search; privacy
completion has no explicit acknowledgement; and the wizard's error/progress/
forced-colors semantics are incomplete. Named `UserFactory` onboarding states
and the missing localized verification-throttle key are also required. The
planned implementation paths are the affected onboarding/auth Actions and
components, `SafeIntendedUrl`, canonical pet/social scopes, factory states,
Blade/catalogues, focused tests, this plan, the threat model, ledger, and
changelog. The already-applied migration is not edited.

Independent review then reproduced a direct known-key private-pet social
bypass, `deny:view` projection leak, incomplete named factory aggregate,
component-only introduction acknowledgement, and normalized uniqueness 500.
Those findings were fixed and focused tests pass. The same review retained
NO-GO for observably different duplicate-registration outcomes,
production-adapter row-lock concurrency, positive signed upload/preview and
connected accessibility/browser proof, plus current repository-wide gates.
Post-commit verification exception/skip recovery, real stale product/pet
Livewire update denial, and signed verification-link matrices are now covered.
Exact dispositions and command evidence are in the onboarding plan and work
ledger.

| ID | Dependency | Owner | Affected paths/modules | Acceptance criteria | Required verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| ONB-01 | Repository contract and canonical identity/auth/profile/pet/privacy docs | Principal plus twelve read-only specialist roles | Ledger, dedicated plan, canonical plan, onboarding threat model | Current state, reusable authorities, selected design, risks, compatibility, rollout and rollback are evidence-backed before production edits | Route/code/schema/test inventory, specialist reports, principal reproduction and planning diff | completed | Revert onboarding planning additions only |
| ONB-02 | ONB-01 | Principal | Existing migration/model/factory/enums, registration initializer, middleware, Actions, Livewire, Blade, EN/LT/RU and tests | Actual foundation is mapped without duplicating working business logic; current focused baseline is observed | 101 focused tests and 718 assertions passed; root seed gate separately reproduced red from stale cross-domain manifest | completed revalidation; global NO-GO | Disable enforcement while preserving rows; never edit the applied migration |
| ONB-03 | ONB-02 | Principal | Focused onboarding/auth/pet/social/factory/accessibility tests | RED contracts reproduce stale-unverified mutation, unsafe intended forms, revoked/expired pet evidence, private pet discovery, missing privacy acknowledgement, incomplete error/progress semantics and factory-state gaps | Each selected regression fails for the intended reason before implementation | completed; serial RED evidence recorded | Revert attributable test additions |
| ONB-04 | ONB-03 | Principal | Onboarding/profile Actions/components, redirect service/confirmation, pet/social authority, factory states, Blade and EN/LT/RU | Smallest implementation closes ONB-03 without changing legacy users, migration history, canonical pet workflow or unrelated privacy values | Focused GREEN after every coherent slice; adjacent auth/pet/social/localization/architecture regressions | completed focused and after review fixes; current Prompt 03 matrix passes 272 tests and 43,817 assertions across ten serial groups | Revert coherent test/fix pair; retain schema and canonical data |
| ONB-05 | ONB-04 | Principal | Dedicated plan, canonical plan, ledger, threat model, changelog and generated-evidence checks | Documentation records only observed results, exact remaining Prompt 02+ work and current repository blockers; generated files remain generator-owned | Documentation/source/generator parity and diff review | completed for Prompt 01; generated database/seeding evidence remains globally red and was not hand-edited | Revert attributable documentation only |
| ONB-06 | ONB-04..05 | Five independent read-only reviewer roles then principal | Frozen attributable diff: architecture, security, database, tests, regression | Every material finding is reproduced and fixed or rejected/deferred with evidence; affected checks rerun | Independent reports and a complete disposition ledger | completed; reproduced runtime findings, including verification-delivery recovery, were fixed and rerun; registration enumeration and production-adapter/browser evidence gaps remain NO-GO | Revert unsafe finding-specific change before publication |
| ONB-07 | ONB-06 | Principal | Exact attributable candidate on `main` | Applicable Composer/Pint/Larastan/Pest/migration/seed/npm/build/cache/browser/diff/secret gates pass; isolated index includes only task-owned files; normal push succeeds | Exact commands, exit codes, staged diff/check, commit and push output | verification completed with NO-GO: focused 272 tests / 43,817 assertions green; full Pest 3,034 total / 2,884 passed / 39 failed / 133,454 assertions / exit 2; full Pint, 34-error full Larastan, generated DB/seed/source and browser gates not green. Principal made no commit/push | Preserve history; publish no further task slice until gates and open security/evidence findings close |
| ONB-P02-01 | ONB-01..07 | Principal plus six exclusive read-only discovery reviewers | Existing `user_onboardings` migration/model/enums/Actions, `User`, factories/seeders, tests, both plans and Prompt 02 ledger | Revalidate the deployed normalized aggregate without duplicating state in `users`; record exact state graph, compatibility rule, schema decision, implementation paths, tests and rollback before production edits | Git inventory, current code/schema/test audit, six specialist reports, planning diff | completed; all six read-only reports dispositioned and no duplicate migration/columns added | Revert Prompt 02 planning additions only |
| ONB-P02-02 | ONB-P02-01 | Principal | New focused state/transition/persistence tests plus existing onboarding test | RED contracts prove missing semantic User facts, safe malformed-state interpretation, prerequisite recheck, idempotent start/completion, stale/cross-user rejection, legacy-status compatibility, schema/casts and valid factory states | Canonical isolated runner; each new behavior must fail for the intended missing rule before implementation | completed; initial 13-test RED had five failures/five errors, extended 18-test RED had five intended failures | Revert attributable tests |
| ONB-P02-03 | ONB-P02-02 | Principal | Focused `OnboardingState` service, small `User` helpers, existing state readers and transition guards | Existing one-to-one persistence remains authoritative; invalid raw enum/timestamp/version values never grant completion or crash the portal boundary; only current-state entry is allowed; impossible completion is rejected; timestamps and forward progress remain immutable/idempotent; invalidated optional pet evidence has a controlled `not-now` recovery; stale product Livewire snapshots cannot bypass the gate | Focused GREEN plus adjacent onboarding/auth/portal tests, Pint and production Larastan | completed focused; onboarding 65/65 (396 assertions), auth/portal combined 150/150 (1,001 assertions), pet/social 54/54 (5,368 assertions), focused Pint and attributable Larastan green | Revert coherent code/test slice; retain deployed schema/data |
| ONB-P02-04 | ONB-P02-03 | Principal | Existing migration/factories/seeders and disposable verification wrappers | Migration remains additive/reversible; full rollback/reapply succeeds on isolated SQLite; fresh DB and repeat seed preserve stable user counts; default factory remains legacy-ready while named states are internally valid | `verify-onboarding-migration.php`, `verify-migration-cycle.php`, `verify-fresh-database.php`, focused factory/schema tests and seed evidence | completed; five populated legacy users preserved through apply/down/up with no rows, 150 migrations reapplied, fresh/repeat seed retained 10 users | No configured database mutation; forward-fix after production writes |
| ONB-P02-05 | ONB-P02-03..04 | Independent non-implementing reviewers; principal dispositions | Frozen attributable Prompt 02 diff | Architecture, database, security, test and legacy-regression findings are reproduced then fixed, rejected, or deferred with evidence | Frozen diff hash, reviewer report, affected reruns and disposition ledger | completed; all runtime and test-evidence findings fixed; three independent reviewers returned SHIP for the same 24-path SHA-256 `10a17725230fc1316ba4b64a9b0ba8d5f94bda687e829ffd9be63644117279d4` | Revert unsafe finding-specific change |
| ONB-P02-06 | ONB-P02-05 | Principal | Both plans, ledger, changelog, exact attributable candidate on `main` | Documentation records exact migration/fields/enums/factory/transition/test/gate evidence; isolated staging contains only task files; commit/push occurs only after material gates pass | Required Composer/Pint/Larastan/Pest/migration/seed/npm/build/cache/diff checks, isolated index review, commit/push output | in progress; focused and infrastructure checks green, but full Pest (39 failures), full Pint, full Larastan (34 errors), generated DB audit and seed coverage are red, so commit/push is prohibited | Do not commit/push on material failure; normal revert only after publication |
| ONB-P03-01 | ONB-P02-03..06 | Principal plus six exclusive read-only discovery reviewers | Auth components/Actions, verification controller/mode, destination services, central middleware, routes, Livewire persistence, pet-create bridge, tests and both plans | Reconcile current implementation with Prompt 03; record exact enabled/disabled lifecycle, intended-URL ownership, minimal incomplete allowlist, JSON/Livewire behavior and rollback before production edits | Git inventory, code/route/test trace, six specialist reports and planning diff | completed; plans and seven-role ledger were created before production edits and all discovery reports were reproduced/dispositioned | Revert Prompt 03 planning additions only |
| ONB-P03-02 | ONB-P03-01 | Principal | New focused authentication/onboarding access tests plus legitimate updates to existing auth expectations | RED proves any remaining password-confirm, completed verification, crafted intended URL, step-scoped pet route, representative portal, JSON, Livewire and resume gaps | Canonical isolated runner; each selected regression fails for the intended missing rule | completed; focused RED reproduced intended, pet-route, feedback, locale, direct/stale Action, notification exception/skip and resend-state defects before fixes | Revert attributable tests |
| ONB-P03-03 | ONB-P03-02 | Principal | `AccountEntryDestination`, auth/verification/confirmation consumers, `SafeIntendedUrl`, onboarding middleware and pet return bridge where proven necessary | One server destination authority preserves intended URLs through verification/onboarding, consumes only for complete users, rejects crafted URLs, enforces availability → verification → onboarding → portal, and grants only required onboarding routes | Focused GREEN after each coherent slice; adjacent auth/portal/pet/Livewire tests; targeted Pint/Larastan | completed focused; post-commit verification delivery is recoverable, locale precedes lifecycle denial, resend and pet Actions recheck fresh status/state, and safe intended URLs are consumed only after completion | Revert coherent code/test pairs; retain persistent onboarding rows |
| ONB-P03-04 | ONB-P03-03 | Principal | Authentication/onboarding/security/testing documentation and changelog | Enabled/disabled diagrams, exact allowlist, JSON/Livewire/intended semantics, observed commands and remaining Prompt 04 UI work are truthful | Documentation and source/generator parity; diff review | completed; canonical verification spec, security, authorization, Livewire, testing and portal plan contain the final observed gate results and NO-GO decision | Revert Prompt 03 documentation only |
| ONB-P03-05 | ONB-P03-03..04 | Five independent non-implementing reviewer roles; principal dispositions | Frozen attributable Prompt 03 diff | Auth, verification, middleware, Livewire and completed-user regression reviewers reproduce and disposition every material finding | Frozen diff hash, reports, finding ledger and affected reruns | completed in successive frozen remediation reviews; all safely fixable in-scope runtime findings were fixed with scoped SHIP verdicts; common registration-attempt design and production-adapter row-lock concurrency remain release blockers | Revert unsafe finding-specific correction |
| ONB-P03-06 | ONB-P03-05 | Principal | Exact attributable candidate on `main` through isolated `GIT_INDEX_FILE` | Required focused/full gates and ownership checks are recorded; commit/push occurs only if every material gate passes | Composer/Pint/Larastan/Pest/migration/seed/npm/build/cache/diff/secret and browser evidence | completed with NO-GO: focused 272 tests / 43,817 assertions green; full Pest 3,034 total / 2,884 passed / 39 failed / 133,454 assertions / exit 2; full Pint and 34-error Larastan red on unrelated work; generated DB/seed and immutable source evidence red; no commit/push | No commit/push on material failure; normal reviewed revert only after publication |
| ONB-P04-01 | ONB-P03-03..06 | Principal plus seven exclusive read-only discovery roles | Existing onboarding component/forms/Actions/state service, route/layout, design system, EN/LT/RU, component and browser tests, both plans and ledger | Reconcile Prompt 04 with current code before production edits; retain one server-authoritative component and document forward-only navigation, reusable forms, mobile/a11y contracts and browser evidence plan | Git inventory, code/view/test trace, seven specialist reports and planning diff | completed planning; external process published prior shared candidate as `6efe024`, current Prompt 04 tree starts clean | Revert Prompt 04 planning additions only |
| ONB-P04-02 | ONB-P04-01 | Principal | `OnboardingWizardTest`, focused Action/form and browser-wrapper contracts | RED proves completed progress semantics, meaningless intro friction, localized validation, semantic groups, stale recovery, evidence-safe deferral, direct/tampered method safety and isolated browser mode gaps | Canonical serial runner; every selected contract fails for its intended missing behavior | completed; initial 15-case suite had 14 intended failures, expanded suite passes 23 tests / 191 assertions | Revert attributable tests only |
| ONB-P04-03 | ONB-P04-02 | Principal | Existing `Onboarding` component/view/layout, focused forms/Action, catalogues/styles | Database state exclusively selects the screen; transitions recheck server state; no browser step/identity/return URL exists; UI is mobile-first, semantic, keyboard complete, localized and exposes error/loading/dirty/offline status without fake controls | Focused GREEN after each coherent slice; adjacent auth/profile/pet/architecture/localization checks; Pint/Larastan | completed; focused 91 tests / 545 assertions, targeted Larastan and localization scanners pass | Revert coherent implementation/test pair; retain persisted onboarding data |
| ONB-P04-04 | ONB-P04-03 | Principal | Canonical isolated browser wrapper and focused onboarding CDP check | Disposable connected flow proves 320/360/375/390/768/1440 overflow, 44px targets, keyboard/focus, locale, Finish, zoom/forced-colors and console/network contracts without production data | Wrapper isolation assertion and connected onboarding browser run | implementation complete, execution blocked: disposable migration/seed and onboarding fixture pass; installed Chrome 152 exits `SIGSEGV`/139 before page assertions | Remove onboarding browser mode/script only; do not alter production data |
| ONB-P04-05 | ONB-P04-03..04 | Independent non-implementing reviewer; principal dispositions | Frozen attributable Prompt 04 diff | Architecture, Livewire security, UX, accessibility and completed-user/auth regressions are reproduced then fixed, rejected or deferred with evidence | Frozen diff hash, reviewer report, finding ledger and affected reruns | completed; final hash `f597d93a39caf189a2759de65a0d50d882b85de20f45a5b6b449449aa614a150` confirmed SHIP | Revert unsafe finding-specific correction |
| ONB-P04-06 | ONB-P04-05 | Principal | Changelog, both plans, ledger and exact attributable candidate on `main` | Exact gates and ownership are recorded; commit and push occur only after every applicable material gate passes | Composer/Pint/Larastan/Pest/migration/seed/npm/build/cache/browser/diff/secret and normal push output | externally published as `a1fa466`; principal made no commit/push; connected Chrome and global gates remain NO-GO | No commit/push on material failure; normal reviewed revert only after publication |
| ONB-P05-01 | ONB-P04-03 | Principal plus five read-only discovery specialists | `ProfilePreferencesForm`, `UpdateProfilePreferences`, `CompleteOnboardingPreferences`, onboarding component, locale middleware/config, profile/auth/onboarding tests and both plans | Reconcile the actual shared preference domain before production edits; record locale/timezone source, immediate-application semantics, stale/replay behavior, accessibility and rollback without a second persistence path | Git/code/test trace, six-role ledger and planning diff | completed; plans preceded production edits and five discovery roles were dispositioned | Revert Prompt 05 planning additions only |
| ONB-P05-02 | ONB-P05-01 | Principal | Focused onboarding preference/component/profile/auth tests | RED proves hydration, configured EN/LT/RU choices, UTC/Europe/Vilnius/DST zone validity, malformed input rejection, immediate language switch, resume, stale/replay and settings consistency | Canonical isolated serial runner; each new behavior fails for its intended reason | completed; 26-test RED reproduced 10 failures and four errors before implementation | Revert Prompt 05 tests only |
| ONB-P05-03 | ONB-P05-02 | Principal | Existing profile form/action and onboarding coordination Action/component/view/catalogues | One canonical validator and user persistence path owns locale/timezone; saved locale is applied to DB, session and next response; server transition remains optimistic/idempotent and accepts no user/step input | Focused GREEN onboarding/profile/auth/localization tests, Pint and production Larastan | completed focused; Preferences + Wizard 58/58, related matrix 150/150, Pint and targeted Larastan green | Revert coherent runtime/test pair; retain canonical user values |
| ONB-P05-04 | ONB-P05-03 | Principal | Preference-step Blade/component presentation and EN/LT/RU catalogue keys | Labelled/help-associated touch-size selects, summary/field focus, dirty/loading/offline status, one H1, long-copy wrapping and passive Blade remain usable without pet/domain queries | Rendered DOM, localization scanners/parity and connected browser matrix when host permits | completed statically/feature-level; parity/scanners and rendered DOM green, connected Chrome remains blocked by host `SIGSEGV` | Revert presentation/catalogue slice |
| ONB-P05-05 | ONB-P05-03..04 | Independent architecture/localization/security/accessibility/regression reviewers; principal dispositions | Frozen attributable Prompt 05 diff | No duplicated preference rules, locale/timezone injection, stale transition, inaccessible validation or normal-profile regression remains | Reviewer reports, disposition ledger and affected reruns | completed after remediation; initial cross-account Profile Settings snapshot NO-GO reproduced and corrected hash `57843270388ccd8394d09a83457745dc46f386149ea05952e6067e759a8363cd` confirmed SHIP | Revert unsafe finding-specific correction |
| ONB-P05-06 | ONB-P05-05 | Principal | Changelog, both plans, ledger and exact attributable candidate on `main` | Exact focused/full gates and ownership are recorded; commit/push occurs only when every material gate passes | Composer/Pint/Larastan/Pest/migration/seed/npm/build/cache/browser/diff/secret and normal push output | NO-GO publication: external process published pre-final-review `7c96e50`; principal made no commit/push; final security remediation and evidence reconciliation remain uncommitted while full Pest/Pint/Larastan and Chrome gates are red | No commit/push on material failure; normal reviewed revert only after publication |
| ONB-P06-01 | ONB-P05-03..06 | Principal plus eight exclusive discovery specialists | Pet identity/create/duplicate/manager/request/privacy/onboarding/UI/tests, both plans and ledger | Reconcile actual canonical pet-domain behavior before runtime edits; record four-choice semantics, active relationship and pending-request predicates, safe server-only return, privacy defaults and rollback | Git/code/test trace, nine-role ledger and planning diff | completed; all discovery findings dispositioned before implementation | Revert Prompt 06 planning additions only |
| ONB-P06-02 | ONB-P06-01 | Principal | Focused onboarding pet/component/transition/integration tests plus reused pet-domain suites | RED proves distinct no-pet/add-later values, owned/active/expired/invited cases, bounded summary, duplicate/privacy safety, pending request, return isolation, replay/stale/direct bypass and completion defense | Canonical isolated serial runner; every selected regression fails for its intended missing contract | completed RED; selected gaps failed for their intended contracts | Revert attributable tests only |
| ONB-P06-03 | ONB-P06-02 | Principal | Existing choice enum/form, `OnboardingPetEvidence`, focused presenter/status service, onboarding Action/component/view/catalogues | Server state validates four truthful choices; managed requires canonical active evidence; care-existing accepts current request evidence without access; summaries are bounded; legacy `not-now` stays readable | Focused GREEN onboarding/pet/auth/privacy tests, Pint and production Larastan | completed focused; final extended run 279/279 (6,191 assertions), reviewer run 112/112 (5,233), targeted Pint/PHPStan green | Revert coherent runtime/test pair; preserve canonical pets/requests |
| ONB-P06-04 | ONB-P06-02..03 | Principal | Existing `CreatePetProfile`, duplicate review, access request and destination bridge; additive duplicate hash migration | Canonical creation/duplicate/access/privacy remain authoritative; exact duplicate matching is indexed and token-bound; request expiry is audited; legacy/new replay is safe; persisted step alone selects return | Focused duplicate/request/create/normal-redirect/privacy/security GREEN plus migration cycle | completed focused; 151→0→151 migration ledger and repeat seed 10→10 green | Revert bridge only; retain created domain records |
| ONB-P06-05 | ONB-P06-03..04 | Principal | Pets step Blade/component and EN/LT/RU catalogues | Four semantic radio cards, safe bounded summaries, empty/pending guidance, labels/errors/action-specific loading/offline and long text remain accessible/mobile without Blade queries | Rendered DOM, parity/scanners, browser matrix when host permits | completed code/DOM/localization; connected Chrome closes DevTools before page assertions | Revert presentation/catalogue slice |
| ONB-P06-06 | ONB-P06-03..05 | Independent pet-domain/authorization/security/privacy/UX/test reviewers; principal dispositions | Frozen attributable Prompt 06 diff, changelog/plans and isolated candidate | No canonical-domain duplication, IDOR/privacy leak, false management claim, return bypass, inaccessible state or regression remains; publish only after material gates pass | Reviewer reports, focused/full Composer/Pint/Larastan/Pest/migration/seed/npm/build/cache/browser/diff/secret evidence | independent re-review GO; publication NO-GO because full Pest has 47 failures and connected browser/architecture gates remain red; concurrent process already pushed pre-final combined `c2b081a`, principal made no follow-up commit/push | No commit/push on material failure; normal reviewed revert only after publication |

Implementation resumes immediately after this plan update and follows TDD in
the order `ONB-03` through `ONB-07`. Individual users may complete the current
foundation, but the delivery must not be represented as satisfying functional
requirements from later onboarding prompts until those requirements are
incorporated and independently verified.

## Active Delivery: I18N-KEY Readable Translation Keys

Status: `implemented; targeted verification passed; publication blocked by concurrent full-gate failures` on
2026-08-30.

This delivery removes every mechanically generated ten-hex-digest suffix from
the `messages` and `ui` translation catalogues and every exact first-party
reference. It replaces the two SHA-based generator paths with one readable,
collision-aware naming contract and adds a permanent architecture ratchet.
The approved design is
`docs/superpowers/specs/2026-08-30-readable-translation-keys-design.md`; the
executable TDD plan is
`docs/superpowers/plans/2026-08-30-readable-translation-keys.md`.

The work runs on `main` in a materially dirty shared tree. All pre-existing
staged, unstaged, and untracked changes remain user-owned. Catalogue and
reference files necessarily overlap concurrent work, so the migration must
preserve their current values and unrelated hunks while publication uses an
attributable temporary index and a complete diff review.

| ID | Dependency | Owner | Affected paths/modules | Acceptance criteria | Required verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| I18N-KEY-01 | Approved readable-key design | Principal | Current Git inventory, `lang/{en,lt,ru}/{messages,ui}.php`, localization scripts and references | Exact hashed-key inventory, generator root cause, dirty-tree overlap, collision inventory, scope, and rollback are recorded before production edits | Git baseline, catalogue/reference counts, design and plan diff | completed | Revert planning documents only |
| I18N-KEY-02 | I18N-KEY-01 | Principal | `ReadableTranslationKeyTest`, `PhpMessageLiteralClassifierTest`, `ArchitectureComplianceTest` | RED contracts prove readable normalization, fail-closed collisions, operator-log isolation, and repository-wide rejection of ten-hex translation suffixes | Focused tests observed failing for missing helpers, diagnostic false positives, and current 5,860 keys | completed | Revert attributable test additions |
| I18N-KEY-03 | I18N-KEY-02 | Principal | shared script helpers and both localization scripts | Existing English values reuse stable keys; new unambiguous text gets a readable key; operator logs remain untranslated; ambiguity stops with an actionable error; no digest/random/counter fallback exists | Helper tests and both localization script checks | completed | Revert helper/script changes |
| I18N-KEY-04 | I18N-KEY-03 | Principal | deterministic migration command, six catalogues, exact PHP/Blade/test references | Exactly 5,860 hashed keys migrate without locale-value/placeholder loss; every reference resolves; 42 collision groups receive reviewed meaningful names; check mode is clean and non-mutating | Migration RED/write/GREEN counts, locale parity, no-old-key scan, focused rendering/contracts | targeted verified | Normal revert of the coherent source migration; no database action |
| I18N-KEY-05 | I18N-KEY-04 | Principal | localization/testing/compliance/changelog documentation | Contributor workflow and evidence match observed behavior; no requirement is promoted beyond executed checks | Documentation/diff/source-generator review | completed | Revert documentation with implementation |
| I18N-KEY-06 | I18N-KEY-02..05 | Independent reviewer then principal | Frozen attributable diff and full repository gates | Every material finding is reproduced and dispositioned; focused/full tests, Pint, Larastan, audits, build, caches and applicable browser checks are green before publication | Complete commands, staged diff, `git diff --check`, commit hash and push output | blocked: full Pest, Pint, Larastan, places backfill, and browser a11y are red in concurrent work; both localization checks are green; do not publish | Do not push on a material failure; normal revert after publication |

Implementation order is `I18N-KEY-02` through `I18N-KEY-06`. Translation
values, placeholders, stored domain identifiers, user prose, and generated
forum evidence are outside the rename boundary.

## Active Delivery: EVENT-P13-ADVANCED Durable Event Operations

Status: `approved; canonical requirements read and specialist discovery starting`
on 2026-08-30.

This delivery implements the bounded advanced Point 13 scope requested for
competitions, commercial participants, volunteer staffing, incidents, weather,
certificates, feedback, archive, and retention on the existing `ForumEvent`
aggregate. It does not promote unrelated ticket/payment, QR/offline check-in,
route geometry, training, exhibition, conference, or media-gallery packages.
The exclusive specialist scopes, dirty-tree exclusions, findings, and review
dispositions live in
`docs/audits/event-p13-advanced-work-ledger.md`. The principal owns the shared
schema, cross-domain decisions, production edits, evidence promotion, commit,
and push.

### Delivery contract

- Advanced records remain children of canonical `ForumEvent` and, where
  configured, `ForumEventOccurrence`; no parallel event, user, pet,
  organization, place, marketplace, notification, media, or moderation
  authority is introduced.
- Competition rules, categories, entries, eligibility, judge assignments,
  scaled-integer criteria/scores, corrections, result versions, appeals, and
  certificate sources are relational. Finalization is deterministic,
  transactionally locked, idempotent, and append-only after publication.
- Vendor and sponsor records separate application/review state, packages,
  benefits, areas, contacts, expiry/cancellation, disclosure, and public-safe
  projections. Event participation never weakens marketplace verification or
  grants attendee exports.
- Volunteer roles, required skills, applications, shifts, capacity,
  assignments, substitutions, cancellations, attendance, and communication
  scopes are relational. Removal revokes current operational access while
  preserving attribution.
- Incident records distinguish severity, category, occurrence/location,
  reporter, responder, factual sources, actions, evidence metadata,
  resolution, and private append-only history. Public views never expose
  evidence, private people/pet facts, or fault claims.
- Weather uses one explicitly configured adapter contract. Disabled or failed
  providers make no accidental network claim; manual observations remain
  attributable; stale observations fail visibly; provider data alone cannot
  cancel, postpone, suspend, or otherwise transition an event.
- Certificates derive only from canonical attendance or published achievement
  sources. Issue, regenerate, revoke, locale, checksum, and version history are
  auditable; private downloads are authenticated, authorized, expiring where
  shared, and never use unrestricted public URLs.
- Feedback supports typed public or private visibility, moderation,
  attendance/source verification, idempotency, rate limiting, and
  privacy-preserving aggregates that remain suppressed below a configured
  minimum distinct-contributor threshold.
- Archive projections are explicit allowlists. Retention rules, legal holds,
  deletion requests, authorized exports, expiry, and redaction are relational,
  idempotent, audited, and fail closed around incidents, results,
  certificates, consent, and immutable history.
- Every public property/action argument is untrusted. Policies cover every
  advanced aggregate and mutation; Actions own short transactions and row
  locks; database constraints enforce uniqueness/capacity/idempotency; only
  committed events may create recipient-locale deduplicated notifications.
- Class-based Livewire components use separate passive Blade views, localized
  EN/LT/RU strings, bounded queries, stable keys, visible loading/error/empty/
  offline states, non-color status, keyboard paths, focus, and 44-pixel touch
  targets. No JSON placeholder or UI-only capability may satisfy an item.

| ID | Dependency | Owner | Affected paths/modules | Acceptance criteria | Required verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| EVENT-ADV-01 | Existing event/organization/place/marketplace/moderation foundations; P22/P26-P29 requirements | Principal plus nine read-only domain specialists | Canonical plan/ledger, event requirements/docs, current schema/code/tests | Existing authority, exact requirement manifest, shared data boundaries, dirty-tree ownership, risks, rollback, and every specialist disposition are recorded before production edits | Git/inventory baseline, specialist reports, plan/ledger diff | in progress | Revert only this delivery's planning additions |
| EVENT-ADV-02 | EVENT-ADV-01 | Principal | Focused advanced-event feature/unit tests | Failing contracts cover schema constraints, direct authorization, tenant/owner isolation, stale IDs, replay, concurrency, privacy, state transitions, provider failure/staleness, aggregation threshold, retention/hold/export/redaction, and notification rollback/deduplication before production code | Each focused test is observed failing for the missing behavior | planned | Revert only attributable red contracts |
| EVENT-ADV-03 | EVENT-ADV-02 | Principal | Additive SQLite-portable advanced-event migrations, enums, models, factories | Every requested subdomain is relational, indexed, enum-cast, factory-backed, hidden/redacted where private, uniquely constrained, and reversible before production writes | Migration up/down/reapply, schema/model/factory tests | planned | Roll back additive tables only before use; forward-fix after writes |
| EVENT-ADV-04 | EVENT-ADV-03 | Principal | Competition Policies, Actions, services, projections, notifications | Eligibility and judge conflicts fail closed; scaled scores cannot overwrite another judge; corrections preserve originals; finalization/correction/appeal/publication are deterministic, versioned, locked, idempotent, and privacy-safe | Competition authorization/state/replay/tie/conflict/concurrency/history/public-projection tests | planned | Disable mutations/publication and forward-fix retained history |
| EVENT-ADV-05 | EVENT-ADV-03 | Principal | Vendor/sponsor and volunteer Policies, Actions, projections, notifications | Applications, reviews, packages/benefits/areas/contacts and staffing roles/skills/shifts/capacity/substitution/cancellation/attendance follow explicit state machines; expiry/removal revokes access; public projections are allowlisted | Commercial/staff role, wrong-tenant, capacity, replay, expiry, privacy, notification tests | planned | Disable new applications/assignments; retain audited rows |
| EVENT-ADV-06 | EVENT-ADV-03 | Principal | Incident, weather-provider, certificate, feedback, archive/retention Policies and Actions | Factual incidents, manual/provider weather, canonical certificate sources, moderated feedback, safe aggregates, holds, deletion, export and redaction satisfy the delivery contract without automatic provider cancellation | Incident/source/action/evidence/history, HTTP fake/manual/stale/no-auto-cancel, certificate version/revoke/download, abuse/threshold, legal-hold/export/redaction tests | planned | Disable entry points/providers/exports; preserve protected audit evidence |
| EVENT-ADV-07 | EVENT-ADV-04..06 | Principal | Class-based advanced event Livewire workspace/form/view/routes and bounded presenters | Authorized members can operate every in-scope workflow; direct calls reauthorize/reload; public/member/private projections, all interface states, responsiveness, keyboard/focus and locale parity are truthful | Livewire tamper/direct-call/replay, query/payload, localization, architecture and browser tests | planned | Remove advanced workspace route/UI while retaining relational data |
| EVENT-ADV-08 | EVENT-ADV-03..07 | Principal | Advanced factories, deterministic demo seeder, database seeder integration, EN/LT/RU, provider/deployment config | Meaningful state matrices and integrated scenarios are count-stable and environment-safe; provider configuration has explicit disabled/manual fallback; translations remain recursively aligned | Factory coverage, production guard, fresh/repeat seed, config/locale checks | planned | Remove demo synchronization and disable provider; never delete user records |
| EVENT-ADV-09 | EVENT-ADV-04..08 | Independent final reviewer then principal | Frozen attributable diff and finding ledger | Reviewer is independent of implementation; every material finding is reproduced, dispositioned, fixed when valid, and affected checks rerun before promotion | Review package, disposition ledger, focused reruns, `git diff --check` | planned | Revert unsafe finding-specific changes before publication |
| EVENT-ADV-10 | EVENT-ADV-09 | Principal | Event/data/security/authorization/frontend/testing/seeding/deployment docs, exact requirement evidence, changelog, full repository | `EVENT-P13-ADVANCED` is promoted only from observed focused/full gates; generated evidence remains byte-current; one attributable temporary-index commit fast-forwards `main` and pushes only after all required gates pass | Focused suites, full serial Pest, syntax/Pint/Larastan, Composer/npm audits, migration/seed repeat, Vite, route/config/view caches, browser, source/generator, secret and staged-diff checks | planned | Do not promote/push on any open material finding or failed gate; forward-fix migrated production data |

Implementation order is `EVENT-ADV-01` through `EVENT-ADV-10`. Every behavior
change begins with an observed failing test. Cross-domain schema is added only
after the specialist findings are reconciled; evidence status remains
`planned`/`discovered` until the exact implementation and verification exists.

## Active Delivery: Portal Events P12-P16 Core Completion

Status: `approved; canonical reading complete and specialist discovery
starting` on 2026-08-30.

This delivery executes P12 through P16 of
`docs/plans/portal-events-completion-master-plan.md` against the existing
`ForumEvent` aggregate. It does not create a second event, place, pet,
organization, document, notification, translation, or authorization system.
The exclusive read-only specialist scopes, dirty-tree exclusions, findings,
and dispositions are recorded in
`docs/audits/portal-events-p12-p16-work-ledger.md`; the principal owns every
cross-module decision and edit.

### Delivery contract

- One typed registry owns each event type's translated metadata, validated
  configuration, builder sections, organizer/participant/pet model, risk
  defaults, recurrence/session/directory capabilities, icon, factory state,
  and deterministic seed scenario. Legacy mappings are explicit and unsafe or
  exploitative configurations fail closed.
- One class-based Livewire builder persists the server-authoritative draft and
  current step. Templates copy reusable configuration only, never ownership,
  participants, invitations/access links, registration state, or stale
  eligibility evidence. Material edits append immutable versions and history.
- `ForumEventSeries` owns validated recurrence defaults and
  `ForumEventOccurrence` remains the stable scheduled truth. Additional,
  skipped, one-occurrence, future-occurrence, cancelled, postponed,
  rescheduled, and moved instances preserve history and version defaults and
  overrides independently.
- Schedules store UTC instants, an IANA timezone, and deliberate wall time.
  All-day, overnight, multi-day, ambiguous, and nonexistent DST times have
  explicit behavior. Tracks, rooms, sessions, speakers/staff, capacity, and
  ordering remain occurrence-scoped.
- Group walks reuse canonical places/venues. Public routes and meeting points
  are approximate and text-complete; exact geometry, directions, access, and
  operational detail remain private and policy-scoped.
- Typed participant and pet requirements cover multi-pet species/taxon, age,
  vaccination, documents, membership, accessibility, and role where required.
  Unknown, expired, disputed, missing, not assessed, or manual-review facts do
  not pass silently.
- Registrations retain the accepted event version and minimal eligibility
  snapshots. Material pet or event changes re-evaluate affected decisions;
  exceptions are requirement-specific, expiring, audited, and independently
  authorized.
- Only committed material changes create deduplicated recipient-locale
  notifications. Rollback, no-op, non-material draft, and private-detail-only
  edits send none; public and notification projections stay privacy-safe.
- Every new model has a bounded factory. Deterministic scenarios use stable
  identities and production Actions, rerun without drift, and remain blocked
  outside local, demo, or testing environments.

| ID | Dependency | Owner | Affected paths/modules | Acceptance criteria | Required verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| PEV-01 | Approved P12-P16 request; P02/P03 foundations | Principal plus read-only event-domain, builder, recurrence, timezone, schedule, eligibility, authorization, and migration specialists | Canonical plan/ledger, event docs, current event code/tests | Authority, state machines, schema, privacy, dirty-tree ownership, exact scope, and specialist dispositions are recorded before production edits | Git/inventory baseline, specialist reports, plan/ledger diff | in progress | Revert planning-only additions |
| PEV-02 | PEV-01 | Principal | Type metadata/value objects, configuration validator, compatibility mapping, factories/tests | Canonical and legacy types resolve through one registry used by builder, domain validation, presentation, factories, and tests; invalid configurations reject | Red/green type/configuration tests | planned | Revert registry and retain readable legacy values |
| PEV-03 | PEV-02 | Principal | Additive builder/template/version/history migration; models, factories, policies, forms, Actions | Draft step/version are server-owned; safe templates and immutable versions are constrained, indexed, authorized, idempotent, and populated-data safe | Migration/rollback, stale/concurrent organizer, wrong-account, template privacy, history tests | planned | Roll back before use; forward-fix after production writes |
| PEV-04 | PEV-03 | Principal | Class-based builder/form/view, routes, presenter, EN/LT/RU | Resumable steps, readiness, previews, loading/dirty/error/empty/offline states, direct authorization, locale parity, keyboard/focus, and touch targets pass | Livewire tamper/direct-call/replay, localization, browser tests | planned | Remove builder UI while retaining valid drafts/versions |
| PEV-05 | PEV-03 | Principal | Recurrence/occurrence migration, value objects/services, occurrence Actions, factories/tests | Valid daily/weekly/weekday/monthly/custom/fixed rules generate stable unique occurrences; one/future edits and lifecycle changes never rewrite past instances | Boundary, invalid-rule, stable-key, one/future edit, cancellation, migration tests | planned | Disable expansion/mutations; retain evidence and forward-fix |
| PEV-06 | PEV-05 | Principal | Timezone/DST normalization, occurrence/session integration, presenters/tests | UTC/IANA/wall-time data is unambiguous; multi-day, DST gap/fold, ordering, session/speaker capacity, and occurrence bounds are deterministic | DST matrix, multi-day, overlap, ordering, capacity, locale-format tests | planned | Disable affected edits and preserve instants/history |
| PEV-07 | PEV-05..06 and P03 | Principal | Route/meeting-point/route-group schema, models, Actions, policies, presenters, forms/factories/tests | Walk routes reuse place/venue authority; exact operational data never enters public cards/notifications; text alternatives remain complete | Public/private projection, wrong-account, capacity, serialization, Livewire/browser tests | planned | Remove route UI/disable mutations; retain encrypted routes |
| PEV-08 | PEV-02..07 | Principal | Requirement/decision/evidence/exception/snapshot schema, evaluator, policies/forms/Actions/factories/tests | Multi-pet and participant decisions are minimal, versioned, privacy-scoped, registration-snapshotted, exception-safe, and re-evaluated after material changes | Eligibility matrix, expiry/dispute/review, pet/event change, exception, wrong-account, concurrency, privacy tests | planned | Disable new decisions; retain snapshots/evidence |
| PEV-09 | PEV-03..08 | Principal | Material-change classifier and after-commit notification boundary | One committed material change produces one privacy-safe localized notification; rollback/no-op/non-material edits produce none | After-commit, rollback, dedupe, locale, audience, revoked-access tests | planned | Disable delivery while retaining versions/history |
| PEV-10 | PEV-02..09 | Principal | Demo seeder, root seed, canonical/generated docs, changelog | Factories/scenarios cover the implemented matrix; docs/evidence state only observed behavior | Factory/seeder, fresh/repeat seed, source/generator, docs/diff/secret checks | planned | Remove demo/docs additions; never delete user evidence |
| PEV-11 | PEV-01..10 | Independent reviewers then principal | Frozen attributable diff and runtime boundaries | Domain, privacy, migration/concurrency, localization/accessibility, and regression findings are reproduced, dispositioned, fixed when valid, and rerun | Focused events, full Pest, Pint, Larastan, migration/seed, npm/build, browser, cache, requirement-generation, staged diff | planned | Revert before production use; forward-fix production data |

Test-first order is registry, builder/template/version, recurrence/timezone/DST,
routes and eligibility snapshots, then notification materiality. Focused gates
and an independent frozen-diff review precede generated evidence and the full
release gate.

## Active Delivery: Canonical Emergency Veterinary Discovery

Status: `planned; specialist discovery and red contracts next` on 2026-08-30.

This delivery closes the bounded `PRD-PLACE-003` / `PLA-P15` emergency
discovery slice by replacing fixture category, hours, species, distance, and
ranking assumptions with canonical server-side facts. It also implements the
minimum `PLA-P02`, `PLA-P04`, `PLA-P05`, and `PLA-P14` dependencies required by
that slice: additive canonical weekly schedules and exceptions, canonical
veterinary service offerings and supported species, a portal-visible Eloquent
query, fact-scoped freshness and verification, and privacy-safe approximate
distance. It does not claim the remaining Places packages complete.

The task starts on `main` at
`ae4ac3241f99b05645dcc07316f424dfb877892e`, aligned with `origin/main`, in a
dirty shared tree containing concurrent Places planning, database-seed
coverage, and audit-ledger work. Every existing staged, unstaged, and untracked
path is user-owned unless an attributable hunk is recorded below. No existing
change may be reset, discarded, rewritten, or included in this delivery.
Publication uses a temporary `GIT_INDEX_FILE` and the complete staged diff is
reviewed before commit.

### Design and safety decisions

- The candidate query starts with active public `Place` rows whose canonical
  type is `veterinary_clinic`; category text alone never establishes emergency
  capability. Only a canonical emergency service offering may do so.
- One place schedule owns a validated IANA timezone, fact verification state,
  observed/verified/fresh-until times, weekly intervals, and date exceptions.
  Overnight intervals are evaluated across their local-date boundary. A full
  closure exception wins over ordinary intervals; a special-opening exception
  replaces that date's weekly intervals. `PlaceStatus::TemporarilyClosed`
  always wins over schedule data.
- The evaluator is clock-controlled and pure after its bounded eager-loaded
  input. It exposes `open_now`, `opening_soon`, `closed`, `status_unknown`,
  `stale_schedule`, and `temporarily_closed`; appointment-only is an explicit
  qualifier and never implies walk-in acceptance. A schedule is stale when
  its fact expiry has passed. Missing or invalid timezone, intervals,
  verification evidence, or dates fail closed to an uncertainty state.
- A canonical emergency offering records availability, supported species,
  verification, provenance/freshness, and appointment-only semantics. Null
  supported species means `species_capability_unknown`; an explicit list that
  omits the selected species means `species_not_supported`; neither becomes a
  compatible result.
- Ranking is a lexicographic tuple prepared on the server: verified emergency
  capability, opening state, schedule/capability freshness, species
  compatibility, appointment-only qualifier, privacy-safe approximate
  distance when both generalized origin and public place point are valid, and
  stable place ID as the final tie-break. The presentation receives localized
  explanations for the factors; it never recomputes them in Blade or
  JavaScript.
- Emergency mode is a complete server-rendered list and normal HTML form/link
  flow. It does not require JavaScript, geolocation, a map, route provider, or
  exact origin. It returns only call and authorized public-detail actions.
  Approximate origin coordinates stay inside the server calculation and are
  absent from returned view data, HTML, JavaScript, logs, URLs, and shared
  caches.
- The EN/LT/RU safety message is unconditional. It tells the member to call
  first and states that PawCircle does not diagnose or guarantee admission,
  waiting time, clinician availability, species acceptance, or treatment.

### Specialist work ledger

Specialists are read-only during discovery and review. Their scopes are
exclusive, reports identify exact files, requirements, tests, and severity,
and the principal owns every cross-scope decision and tracked edit. An
implementer cannot act as the independent reviewer for the same slice.

| Specialist ID | Exclusive scope | Required deliverable | Dependency | Status |
| --- | --- | --- | --- | --- |
| EVD-S01 Veterinary safety | Clinical-risk copy, call-first prominence, non-diagnosis and non-guarantee boundaries, species-mismatch handling | Severity-ranked copy/state review with safety-equivalent EN/LT/RU intent and unsafe-failure examples | Canonical design above | pending |
| EVD-S02 Schedule computation | IANA timezone, DST, weekly/overnight intervals, date exceptions, temporary closure, appointment-only, verification and freshness precedence | State table, edge-case matrix, proposed pure evaluator interface, and canonical schedule red tests | EVD-01 plan | pending |
| EVD-S03 Ranking | Candidate eligibility, deterministic tuple, unknown/mismatch ordering, approximate-distance fallback and stable ties | Ranking truth table, tie-break contract, factor explanations, and adversarial cases | EVD-S02 facts | pending |
| EVD-S04 Privacy | Portal-visible scoping, generalized origin, public coordinates, HTML/JS/log/cache/URL exclusion and no-provider fallback | Data-flow review with leak assertions and exact private-location tests | Existing place authority | pending |
| EVD-S05 Localization | EN/LT/RU catalogue ownership, safety equivalence, key/placeholder parity, status and ranking-factor wording | Catalogue/key map, semantic parity review, long-copy risks, and locale test matrix | EVD-S01 states | pending |
| EVD-S06 Accessibility | Mobile call/details actions, heading/status semantics, focus, 44-pixel targets, non-color states, no-JavaScript order and reflow | WCAG-oriented markup review and desktop/mobile/keyboard acceptance matrix | Server presentation design | pending |
| EVD-S07 Testing | Pest layers, factories, time/locale isolation, DST/overnight/holiday/malformed/private/no-results/tie cases, Places/full-suite/static/build/browser gates | Exact red/green test inventory, command sequence, isolation risks, and coverage-gap review | EVD-S01..06 | pending |
| EVD-R01 Independent review | Frozen attributable diff after implementation | Reproduce material findings, record every disposition, require affected reruns, and issue release-readiness verdict | EVD-01..08 | pending |

### Delivery ledger

| ID | Dependency | Owner | Affected paths | Acceptance criteria | Verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| EVD-01 | PRD-PLACE-003, PLA-P15, schedule/service/privacy/safety requirements | Principal plus EVD-S01..07 | This ledger and read-only specialist reports | Existing authority, dirty-tree ownership, state precedence, privacy, safety, accessibility, localization, test, and rollback contracts are mapped before production edits | Repository baseline, requirement trace, specialist reports | in progress | Revert only this planning addition |
| EVD-02 | EVD-01 | Principal | `tests/Unit/Places/**`, `tests/Feature/Places/**`, `tests/Feature/PlaceDirectoryTest.php` | Failing behavior contracts cover DST, overnight, holiday exceptions, temporary closure, missing/invalid timezone, stale/unverified schedules, appointment-only, species compatible/unknown/mismatch, public-only candidates, deterministic ties, malformed coordinates, no origin, no results, private-location exclusion, localized safety copy, and no-JavaScript HTML | Focused red commands observed before implementation | pending | Revert only attributable red contracts |
| EVD-03 | EVD-02 | Principal | Additive migration; new Places enums/models/factories; `Place` relations | Schedule, interval, exception, and veterinary offering facts are normalized, constrained, indexed, enum-cast, factory-backed, SQLite-portable, and reversible before production writes | Schema/factory tests and isolated migration rollback/reapply | pending | Roll back additive tables only before production writes; forward-fix afterward |
| EVD-04 | EVD-03 | Principal | Schedule and veterinary capability evaluators/value objects | Clock-controlled evaluation implements documented precedence and never promotes missing, stale, invalid, unverified, appointment-only, or incompatible facts | Unit red/green matrix including Vilnius DST boundaries | pending | Revert evaluator with its tests while retaining unused additive tables |
| EVD-05 | EVD-03..04 | Principal | Portal-visible veterinary query, approximate-distance calculator, deterministic ranker | One bounded eager-loaded Eloquent query scopes public active veterinary places first; rank tuple and explanations are stable; malformed/missing coordinates yield unknown distance without failure or leakage | Query-count, privacy, eligibility, distance, and tie tests | pending | Revert query/ranker and fall back to disabled emergency results, never fixture ranking |
| EVD-06 | EVD-04..05 | Principal | Focused emergency presenter and emergency Blade/components | Emergency output is server-prepared, localized, and usable with JavaScript, maps, geolocation, and providers absent; call renders only for a canonical public phone; approved public details remain available; every card exposes textual states and ranking factors | Feature/render/no-script tests and Blade architecture checks | pending | Revert presentation slice and disable emergency entry point if canonical facts are unavailable |
| EVD-07 | EVD-03..06 | Principal | `PlaceDemoSeeder`, relevant factories, `lang/{en,lt,ru}/place_directory.php`, SCSS only if existing tokens cannot express the markup | Deterministic safe examples cover every state without internet data; EN/LT/RU wording is safety-equivalent and key/placeholder complete; mobile actions retain 44-pixel targets and visible focus | Seed repeatability, locale parity, three-locale render, mobile/keyboard checks | pending | Remove demo-only synchronization and revert locale/presentation keys together |
| EVD-08 | EVD-02..07 | EVD-R01 and principal | Frozen attributable diff | Independent findings are reproduced and dispositioned; valid in-scope findings are fixed and affected checks rerun before documentation promotion | Review package, disposition ledger, focused reruns | pending | Revert unsafe finding-specific change |
| EVD-09 | EVD-08 | Principal | PRD/compliance/Places/data/privacy/security/testing/deployment/current-progress docs and changelog only after proof | Canonical schedule tests pass before PRD-PLACE-003 is updated; documentation claims match observed focused Places, full Pest, Pint, Larastan, npm build, mobile browser, and isolated migration evidence | Exact commands from `docs/testing.md`, user-requested complete Places checks, diff/secret review, and temporary-index staged diff | pending | Revert coherent documentation/evidence commit; never mark unobserved gates verified |

Implementation order is `EVD-01` through `EVD-09`. Every behavior change
begins with an observed failing test. `PRD-PLACE-003` and its compliance row
remain partially implemented until the canonical schedule test matrix passes;
failed later gates remain explicit even if focused emergency behavior is
green.

This living plan records work that was actually performed. A pass is
`verified` only when its listed check completed successfully. Requirement-level
status remains authoritative in
`docs/requirements/compliance-matrix.md`.

The reconciled current backlog is maintained in
`docs/plans/current-unfinished-work.md`. Completed deliveries below are release
evidence, not active backlog items.

## Active Delivery: Global Page Identity Completion

Status: `implementation focused checks green; responsive browser gate in progress`
on 2026-08-30.

This delivery closes the remaining work in
`docs/plans/global-page-identity-standardization-plan.md` without changing
route destinations, authorization, active navigation, message-folder order,
authorized back links, or deep-link behavior. It starts from clean `main` at
`ae4ac32`, aligned with `origin/main`. The specialist ownership, evidence
format, and independent-review boundary are recorded in
`docs/audits/global-page-identity-completion-work-ledger.md`. Discovery
specialists are read-only; the principal owns every attributable tracked edit,
cross-module decision, final verification result, commit, and push. The live
worktree now also contains unrelated in-progress event, place, seeding, and
repository-audit slices. Those paths remain outside this delivery unless an
exact overlapping Global Page Identity change is already present; publication
must therefore use a temporary index built from the attributable path and hunk
set rather than the ambient staging area.

| ID | Dependency | Owner | Affected paths | Acceptance criteria | Verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| GPI-C01 | Existing global plan and canonical route matrix | Principal plus route-classification specialist | Canonical requirements, `docs/portal/route-matrix.md`, executable route ledger and route tests | One new stable requirement ID owns the page-identity contract; every current first-party GET route appears exactly once and is classified as directory, detail, workspace, editor, dashboard, settings, authentication, shared access, print/export, or deliberate special case with runtime owner, identity component, exception reason, and test evidence | Route JSON inventory, route-ledger parity, duplicate/missing classification tests | focused verified: 118 runtime names, 118 ledger rows, 118 matrix rows, zero drift | Revert requirement, generated evidence, and route-ledger updates together |
| GPI-C02 | GPI-C01 | Principal plus query-budget specialist | Page presenters/controllers/Livewire components and focused performance tests | Representative directory, detail, and workspace routes have observed constant query ceilings; page headers, breadcrumbs, actions, navigation, and Blade rendering trigger no lazy loading or N+1 queries | Red/green query-count tests, strict Eloquent, route renders with small and expanded fixtures | focused verified: zero-query chrome; 5-query directory, 12-query detail, 4-query workspace; bounded 6-query expert dashboard | Revert query preparation and its budget tests as one slice |
| GPI-C03 | GPI-C01 | Principal plus localization specialist | Priority presenters/catalogues/views, `lang/{en,lt,ru}`, localization tests | Remaining priority-page RU/LT non-header system copy is reviewed; English fallbacks, raw keys, placeholder mismatches, and locale-dependent overflow are detected and fixed while authored/proper content remains unchanged | Locale tree/placeholder parity, focused render tests, browser comparison against English baseline | focused verified: static priority body contract covers 1,078 assertions; final browser comparison pending GPI-C06 | Revert the affected locale/presenter slice together |
| GPI-C04 | GPI-C01..03 | Principal plus detail/workspace and dead-code specialists | Detail/profile/workspace heroes, message-details presenter/template chain, duplicate Blade headers, SCSS | Every detail/workspace exception is audited; only semantically distinct token-compatible heroes remain; the historical message-details chain is removed only after zero active route/include/test/deep-link proof; retired selectors and duplicate implementations have zero consumers | Structural route tests, exhaustive source/deep-link inventory, Blade compilation, dead-code ratchets | focused verified: 39 detail/workspace dispositions; retired cohort and selector ratchets green | Restore one proven consumer chain or selector with its covering test if the zero-consumer proof was wrong |
| GPI-C05 | GPI-C01..04 | Principal plus accessibility specialist | Page identity, breadcrumbs, actions, navigation, detail/workspace heroes and responsive styles | Every rendered page has exactly one correct `h1` and a documented identity component; keyboard focus, 44px targets, forced colors, reduced motion, long RU/LT copy, and 200% zoom preserve operation and heading hierarchy | Focused accessibility/architecture tests and isolated browser assertions | focused verified: nullable content title and shared identity/navigation checks green; runtime matrix pending GPI-C06 | Revert the affected presentation slice without changing route or authorization behavior |
| GPI-C06 | GPI-C01..05 | Responsive browser specialist and principal | `scripts/accessibility-browser-check.mjs` through `scripts/run-browser-check.php page-identity`, package command, temporary browser artifacts | The authenticated matrix covers 320, 375, 768, 1024, 1280 at effective 200% zoom, 1440, and 1920 widths, all priority routes/locales, forced colors, reduced motion, keyboard focus, no horizontal overflow, no raw keys/fallback copy, stable navigation/back links/deep links, and clean console output | `npm run test:browser:page-identity` with disposable SQLite/loopback runtime and screenshot review | in progress | Revert browser assertions only if they encode a disproven contract; retain valid product fixes |
| GPI-C07 | GPI-C01..06 | Independent reviewer then principal | Frozen attributable diff, final exception matrix, screenshots, and evidence | Independent review dispositions every route, query, localization, dead-code, accessibility, responsive, and preservation finding; valid in-scope findings are fixed and affected checks rerun | Frozen review package, finding ledger, post-fix focused verification | planned | Revert finding-specific corrections that cannot be made safe |
| GPI-C08 | GPI-C07 | Principal | Requirements, compliance generator/output, global plan, UI inventories, testing/deployment notes, changelog, complete attributable diff | Final exception audit and documentation match observed behavior; focused tests, full sequential Pest, Pint, Larastan, dependency audits, Vite build, complete browser matrix, route/config/view cache smokes, dead-code scan, source generators, and diff/secret checks pass before one coherent commit and push on `main` | Exact final commands, observed exits/counts, complete staged diff, commit hash and push output | planned | Revert the coherent commit normally; never rewrite history |

Implementation order is `GPI-C01` through `GPI-C08`. Every behavior change
starts with an observed failing test. CSS, Blade, presenter, or template code
is removed only after reproducible zero-consumer evidence. Database-backed
tests remain sequential and every browser mutation uses the repository's
disposable database and loopback runtime.

## Active Delivery: Measured Repository-Wide Performance Audit

Status: `implementation and focused measurement complete; final gates and independent frozen-diff review pending` on 2026-08-30.

This delivery audits directories, feeds, search, dashboards, workspaces,
calendars, message lists, Places, Events, medical and care timelines, device
screens, exports, seeders, generators, and browser assets against
`PERF-QUERY-001..003`, `PERF-LIVEWIRE-001`, `PERF-ASSET-001`,
`PERF-CACHE-001`, `SYS-CACHE-001`, `SYS-LOG-001`, and the deployment/runtime
contracts. It fixes only reproduced problems. Every performance-sensitive path
receives a deterministic representative dataset, recorded query count,
response or snapshot size, peak-memory delta, elapsed time, an explicit
regression budget, and a comparable after measurement.

The task runs on `main` from `462539c0`, aligned with `origin/main` at the
2026-08-30 audit restart,
inside a materially dirty shared tree. Existing Place, event, email-
verification, seed-coverage, plan, and audit-ledger work is user-owned and
must remain outside this delivery. Specialist discovery is read-only under
`docs/audits/repository-performance-audit-work-ledger.md`; the principal owns
all edits and cross-module decisions. Any attributable commit uses a temporary
`GIT_INDEX_FILE` and is pushed only after the required gates and frozen-diff
review pass.

### Delivery decisions

- Correct authorization/scoping, query shape, selected columns, eager loading,
  database aggregation, deterministic pagination, and justified indexes before
  considering cache. Do not cache around a bad query.
- Query-count and payload budgets are deterministic test contracts. Elapsed
  time and peak memory are recorded before and after on the same runtime; they
  become automated failures only where a stable non-flaky invariant exists.
- Add indexes only from observed filter/join/order patterns, verify their plan
  use at representative volume, avoid duplicate prefixes, and retain SQLite
  plus configured production-database grammar portability.
- Livewire public state stays typed scalar/small-array input. Models, builders,
  relationship graphs, private fields, and large derived catalogues stay in
  bounded computed/server-prepared projections. Browser measurements must
  distinguish initial HTML, snapshot, update payload, and duplicate requests.
- New or retained cache entries require a measured benefit plus owner,
  versioned key, account/organization/role/locale scope, TTL, invalidation,
  bounded lock/wait behavior, unavailable-store behavior, and isolation plus
  stampede tests. Private cache values never cross any scope.
- Request IDs remain server-generated. Slow-operation logging is structured,
  sampled/bounded where appropriate, and restricted to safe identifiers,
  duration/count/size measurements and named operation context; it never logs
  secrets, request bodies, exact locations, private media, tokens, sessions,
  credentials, or authorization headers.

| ID | Dependency | Owner | Affected paths/modules | Acceptance criteria | Verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| PERF-AUD-01 | Canonical performance/cache/observability/data/Livewire/deployment requirements | Principal plus read-only query, index, Livewire, cache, asset, runtime and metrics specialists | Canonical plan, performance work ledger, current repository and dirty-tree inventory | Every requested surface and antipattern has an owner, exact inventory path and reproducible measurement method before production edits; unrelated bytes are identified | Starting status/diffs, specialist reports, principal dispositions | completed; eight scopes dispositioned | Revert this plan slice and ledger only |
| PERF-AUD-02 | PERF-AUD-01 | Principal | Performance test helpers, deterministic factories/fixtures or explicit `PerformanceSeeder` extension, measurement artifacts | Each selected sensitive path has representative deterministic small/large data and records baseline query count, response/snapshot bytes, peak memory and elapsed time without touching shared data | Focused baseline commands on isolated testing data; fixture repeatability and seed-safety tests | focused verified; observed values in audit report | Revert audit-only helpers/fixtures; no production data change |
| PERF-AUD-03 | PERF-AUD-02 | Principal | Directory/feed/search/dashboard/workspace/calendar/message/place/event/medical/care/device/export queries, presenters and passive views | Confirmed N+1, lazy-load, `Model::all()`, unbounded `get()`, PHP filtering/pagination, per-row aggregate, over-selection and unstable pagination findings are removed; sensitive scopes precede retrieval | Red/green feature/query-budget tests, small-versus-large constant-growth assertions, response/payload budgets and repeated-page stability | focused verified; full suite has 36 unrelated concurrent failures | Revert each query/presenter slice independently without schema loss |
| PERF-AUD-04 | PERF-AUD-02..03 | Principal | New additive index migration only where query-plan evidence justifies it | Each added index maps to an actual predicate/join/order, avoids redundant prefixes, is used or materially improves the representative plan, retains relationship keys, and is reversible before production use | Schema/index assertions, representative SQLite `EXPLAIN`, configured production grammar inspection, fresh/rollback/reapply migration checks | focused verified: seven plans and reversible migration | Roll back the additive index migration before production use; afterward forward-fix |
| PERF-AUD-05 | PERF-AUD-02..03 | Principal | Class-based Livewire components/forms/computed projections/views and browser request lifecycle | Confirmed oversized public state, Eloquent graphs, repeated serialization and duplicate interaction/request issues are removed without changing authorization or accessible states | Livewire direct-action tests, initial/update snapshot byte budgets, query budgets, browser network/listener checks and no duplicate request evidence | focused verified; no production rewrite justified | Revert component/presentation optimization while retaining domain fixes |
| PERF-AUD-06 | PERF-AUD-02..05 | Principal | Existing cache/lock consumers and only measured new cache candidates | Every cache has the complete declared lifecycle; confirmed leakage/stampede/staleness is fixed; cold/warm/failure behavior is equivalent and scoped; no cache masks a query defect | Cross-user/organization/role/locale isolation tests, invalidation tests, concurrent regeneration/lock tests, unavailable-store fallback and cold/warm measurements | focused verified: isolation, invalidation, lock, fallback | Bump/remove the attributable versioned key or disable the cache path; retain source-of-truth reads |
| PERF-AUD-07 | PERF-AUD-01..02 | Principal | Vite inputs, Tailwind/SCSS/JavaScript chunks, image contracts and browser lifecycle | Only measured duplicate or oversized asset/request defects are changed; production raw/gzip sizes and request counts are recorded; first-party images retain dimensions/variants; no lifecycle duplication is introduced | Before/after Vite manifest and gzip table, JavaScript/source tests, responsive browser network/console/layout checks, 10% regression gate | focused verified; 748 ms build and Places browser pass; broader a11y/discovery blocked outside slice | Revert the isolated asset change and restore prior manifest inputs |
| PERF-AUD-08 | PERF-AUD-01..02 | Principal | Seeders, generators, exports, commands/jobs and runtime scripts | Confirmed unbounded work uses portable bounded chunks/cursors/streams, deterministic progress and idempotent resume/failure behavior; ordinary requests do not hide long work | Focused command/seeder/generator/export tests, representative memory/time records, repeat/resume/failure checks and production-environment denial | focused verified; 149-migration rollback/reapply and repeat seed passed | Disable the operation or restore prior bounded entry point; preserve checkpoints and generated source data |
| PERF-AUD-09 | PERF-AUD-01..08 | Principal | Request-context middleware, logging/operations configuration and tests | Every response retains a server request ID; slow measured operations expose safe bounded correlation context with owner/retention and no secret/private payload; normal requests avoid noisy duplicate records | Observability and architecture tests, header/log assertions, redaction scans, representative slow-path trigger and normal-path non-trigger | focused verified: 11 tests, 158 assertions | Disable attributable slow-operation reporting while retaining request correlation |
| PERF-AUD-10 | PERF-AUD-02..09 | Independent reviewer then principal | Frozen attributable diff, performance/cache/testing/data/deployment/operations docs, changelog and all affected runtime boundaries | Before/after evidence contains observed values only; every material review finding is dispositioned and valid findings fixed; all applicable release gates pass before isolated commit/push | Performance tests, full serial Pest, Pint, Larastan, migration/seed/repeat, Composer/npm audits, Vite/bundle inspection, cache isolation, cache smokes, browser network/responsive/keyboard/console checks, docs/secret/diff/staged-diff review and push result | no-go pending independent review: full Pest 36 failures, Larastan 30 findings, a11y/discovery/source checks blocked | Revert coherent commit normally; use forward fixes for any migrated production schema/data |

Implementation order is `PERF-AUD-01` through `PERF-AUD-10`. Measurement and
an observed failing test precede each behavior change. Specialist suggestions
remain advisory until the principal reproduces them, and documentation may
claim improvement only from comparable observed before/after values.

## Active Delivery: Place Presentation And Privacy Boundaries

Status: `planned; specialist discovery and test-first implementation pending`
on 2026-08-30.

This delivery completes the boundary shared by `PLA-P12` through `PLA-P16`
without claiming the still-open canonical-facts, community-moderation, or
emergency-ranking packages. It preserves canonical `Place`, `Venue`,
`VenueArea`, exact-location grant/audit/version, organization, event, and
encrypted `places.state.v1` foundations. Specialist scopes and review
hand-offs are recorded in
`docs/audits/places-presentation-privacy-work-ledger.md` before delegation.

The task runs on `main` in a materially dirty shared tree. Existing Place
submission, contribution, facts, directory, and email-verification changes are
user-owned and remain outside this delivery. The principal records
attributable hunks and uses a temporary `GIT_INDEX_FILE` for publication.

### Canonical decisions

- Saves, follows, private collections, visits, recent history, generalized
  origin, and private check-ins remain encrypted versioned per-user state,
  use canonical place IDs, and expose only explicit `private` visibility.
  Collaborative collections and visible presence stay outside this delivery.
- Invitations become relational because sender, recipient, relationship/block
  eligibility, expiry, response, revocation, idempotency, and audit require a
  shared lifecycle.
- Place media is image-only for the first lifecycle. Originals stage on the
  private disk; only content-validated, auto-oriented, bounded, re-encoded
  derivatives may be approved. Every response is authenticated and authorized.
- Public location is a region plus a deliberately approximate point. Exact
  facts stay encrypted and are revealed only through account-, purpose-,
  event-, and time-bound grants; every success is audited.
- The complete server-rendered list and textual map/route alternatives are
  authoritative. JavaScript receives only the authorized bounded approximate
  projection and never receives exact private coordinates.
- Geocoding and routing are optional configured adapters. Disabled providers
  make no network call. Enabled clients use configured endpoints, explicit
  timeouts and response limits, normalized DTOs, 429 handling, safe errors,
  and HTTP fakes with stray calls blocked.
- Places remain canonical identities for organizations, venues, venue areas,
  and events. Exact facts are never copied into public cards, caches,
  notifications, exports, calendars, or analytics.

| ID | Dependency | Owner | Affected paths | Acceptance criteria | Verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| PLA-PPB-01 | PLA-P12..P16 and authority foundation | Principal plus read-only specialists | Plan, ledger, requirements, audits, code inventory | Media, personal-state, location, providers, venues/events, privacy, and UI boundaries are mapped before code edits; unrelated dirty work is identified | Specialist reports and starting status/diff evidence | in progress | Revert planning-only additions |
| PLA-PPB-02 | PLA-PPB-01 | Principal | Focused Places/private-file/provider/migration/seed/UI tests | Tests first cover canonical personal state, invitation isolation/expiry, staged media and authorized delivery, exact-location non-serialization/grants, provider fallback/errors, and canonical venue/event reuse | Each focused test fails for missing behavior before implementation | planned | Revert only new red contracts |
| PLA-PPB-03 | PLA-PPB-02 | Principal | Additive migration, media/invitation models, enums, policies, factories, state compatibility | Relational media/invitations preserve lifecycle, idempotency, moderation, retention, recipient, and audit; private state upgrades idempotently from slugs to canonical IDs | Migration/backfill, schema/factory, replay, and isolation tests | planned | Roll back before writes; afterward preserve evidence and forward-fix |
| PLA-PPB-04 | PLA-PPB-03 | Principal | Media Actions and response route | MIME/content, byte/dimension limits, orientation, safe derivatives, attribution, ordering, moderation, cleanup, deletion, retention, containment, and authorization are enforced | Malicious, oversize, orientation, failure, foreign, deleted, and unauthorized file tests | planned | Disable writes/responses and retain records/files for reviewed cleanup |
| PLA-PPB-05 | PLA-PPB-03 | Principal | Personal state and invitation Actions/presentation | Personal records use canonical IDs, bounded retention, private visibility, and ownership; invitations use eligible recipients, expiry, revocation/response, blocks, idempotency, and safe delivery | Two-account, foreign-ID, archive, expiry, block, replay, transfer, and compatibility tests | planned | Disable mutations and preserve history |
| PLA-PPB-06 | PLA-PPB-03 | Principal | Location projection and grant/revoke/reveal management | Approximate public and encrypted exact facts stay separate; grants are recipient/purpose/event/time scoped and revocable; every reveal is audited; exact facts stay out of secondary channels | HTML/source/serialization/cache/notification/export tests plus expiry/revocation/audit cases | planned | Disable reveal/grant UI while retaining audit/history |
| PLA-PPB-07 | PLA-PPB-02 | Principal | Provider contracts, DTOs, configured clients, fallback | Disabled adapters perform no request; enabled geocoder/router normalize bounded responses and safely map timeout, 429, malformed, and oversize responses | Stray-request prevention and provider success/failure tests | planned | Set drivers to `null`; provider-free core remains active |
| PLA-PPB-08 | PLA-PPB-03..07 | Principal | Class-based Livewire/HTTP, passive Blade, map JS/CSS, EN/LT/RU | Server list works without JS; mobile/keyboard/text alternatives and precise states remain accessible; map/list sync tears down cleanly; fallback copy is truthful | Direct-action, locale, architecture, Vite, and browser checks | planned | Revert enhancement/UI routes while preserving server list/data |
| PLA-PPB-09 | PLA-PPB-03..08 | Principal | Place/venue/event/organization integration, factories, seeders | Existing canonical IDs are reused with no duplicate identity/exact snapshot; normal/private/expired/revoked/moderated/provider-free scenarios rerun unchanged | Event/place regression, fresh migration, migration cycle, complete/repeat seed | planned | Disable management surfaces; keep additive canonical relations |
| PLA-PPB-10 | PLA-PPB-02..09 | Independent reviewers and principal | Frozen attributable diff, docs, release gates | Findings are reproduced/dispositioned/fixed; focused/full Pest, private files, Pint, Larastan, audits, build, caches, browser, diff, secrets, commit, and push reflect observed results only | Frozen review package and exact final commands | planned | Revert coherent commit; never delete retained private/audit data |

Implementation order is `PLA-PPB-01` through `PLA-PPB-10`. No production
behavior precedes an observed failing test. Provider absence and denied
geolocation remain supported states; evidence advances only after exact checks.

## Active Delivery: Event Participation, Capacity, And Waitlists

Status: `approved; specialist findings dispositioned and red contracts in progress` on
2026-08-30.

This delivery implements the P17 registration and P18 capacity/waitlist slice
of `docs/plans/portal-events-completion-master-plan.md` against the canonical
`ForumEvent` aggregate. It starts from clean `main` at `ae4ac32`, preserves the
existing payment/ticket provider boundary, and owns only the attributable event
participation schema, models, Actions/services, policies, Livewire workspace,
translations, factories, seed scenario, tests, and synchronized documentation.

The selected architecture is an additive relational workflow: registrations
retain immutable decision snapshots and optimistic versions; normalized typed
capacity pools and allocations cover event, occurrence, session, resource, and
future ticket-type scopes; one ordered waitlist entry represents each active
registration; and an operation ledger returns the canonical result for a
replayed command. This is preferred over JSON counters, application-only
preflight counts, or one column per capacity type because database constraints,
row locks, deterministic ordering, and SQLite-portable integrity must remain
independently testable. Ordinary free registration allocates inside one short
transaction. Expiring reservations are created only by an explicitly configured
flow that must hold a scarce place across requests; the unavailable paid checkout
does not receive a simulated timer or provider success.

The exact source manifest is `event.eligibility.0001` through
`event.eligibility.0104`, `event.registration.0001` through
`event.registration.0159`, and `event.capacity.waitlist.0001` through
`event.capacity.waitlist.0078`, with publication status updated only for atoms
proved by this package. P19 payments/tickets, guest-pet public identity,
competition/vendor/volunteer aggregates, and provider-backed delivery remain
outside this package unless an existing canonical identity can be referenced
without inventing a parallel subsystem.

### Specialist work ledger

| Specialist | Exclusive discovery/review scope | Required structured deliverable | Editing boundary |
| --- | --- | --- | --- |
| Registration state-machine specialist | Registration/eligibility enums, transitions, immutable decision snapshots, multi-pet authority, stale decision detection, cancellation/completion semantics | Current-state gap table, exact transition graph, snapshot schema, invalid/replay cases, source requirement IDs | Read-only discovery; no repository edits |
| Capacity/concurrency/waitlist specialist | Typed pools, allocations, optional expiring holds, final-slot races, ordered waitlist, atomic promotion, optimistic versions, duplicate constraints | Portable schema/index proposal, lock order, concurrency test design, deadlock/retry risks, deterministic ordering contract | Read-only discovery; no repository edits |
| Authorization/notification/UI specialist | Participant/organizer policy matrix, removed organizer behavior, private attendee projection, after-commit deduplication, Livewire UI, localization/accessibility, deterministic factories/seeds | Role/data-field matrix, notification key matrix, UI state/interaction contract, test and browser matrix | Read-only discovery; no repository edits |
| Independent final reviewer | Frozen attributable diff after implementation | Requirement-by-requirement state-machine, concurrency, privacy, notification, and UI findings with severity and reproduction evidence | Review only; must not be an implementer |

The principal owns every cross-module decision and repository edit. Specialist
findings are advisory until reproduced and dispositioned. Review begins only
after the attributable diff is frozen; every material finding is recorded,
valid findings are fixed, and affected checks are rerun before publication.

| ID | Dependency | Owner | Affected paths/modules | Acceptance criteria | Required verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| EVP-01 | P16 authority plus P17/P18 source and clean `main` baseline | Principal plus three read-only specialists | Canonical event/eligibility/privacy docs, current schema/models/services/policies/Livewire/tests, this ledger | Existing behavior, source IDs, privacy boundary, unsupported provider scope, lock order, and attributable paths are mapped before production edits | Repository inventory, source/generator checks, specialist reports and dispositions | completed | Revert this planning section only |
| EVP-02 | EVP-01 | Principal | Additive participation migration; registration, participant/pet decision, capacity pool/allocation/hold/waitlist/operation models and factories | Database constraints prevent duplicate active scope/allocation/waitlist/operation results; snapshot/checksum, optimistic versions, indexes, hidden sensitive fields, expiry, and rollback are portable to SQLite | Red schema/model/constraint/cast/factory tests; populated rollback/reapply | in progress | Roll back before production writes; after use deploy a forward fix and preserve audit/history |
| EVP-03 | EVP-02 | Principal | Registration state machine, eligibility evaluator/snapshotter, register/replay/withdraw/re-evaluate Actions | One or more currently managed eligible pets register once; the exact decision snapshot is retained; wrong account, stale eligibility, duplicate key/payload conflicts, inactive pet, and invalid transition fail without side effects; replay returns the same registration | Red-green Action/policy/idempotency/snapshot/stale-pet tests | planned | Disable mutations; preserve registrations and evidence; forward-fix after writes |
| EVP-04 | EVP-02..03 | Principal | Capacity allocator, optional expiring reservation Action, ordered waitlist and promotion Actions | Event/occurrence/session/resource/ticket-type pools are typed; direct free allocation is transactional; holds exist only for configured cross-request scarcity; final slot cannot double allocate; waitlist order uses explicit priority then request time then ID; promotion locks pool/entry/registration, revalidates, and is replay-safe | Two-process final-slot, simultaneous promotion, expiry/release, multi-pool, capacity reduction/increase tests | planned | Disable affected pool; release active holds idempotently; retain allocation/waitlist history |
| EVP-05 | EVP-03..04 | Principal | Organizer approve/reject/remove/promote/cancel Actions, event/occurrence/pet/capacity change integration, policies and privacy projections | Explicit transition matrix covers invited, pending, confirmed, waitlisted, rejected, cancelled, withdrawn, expired, checked-in, and completed where applicable; removed organizers and wrong accounts fail; event cancellation and occurrence movement re-evaluate atomically; attendee fields are role-minimized and medical/eligibility detail never enters ordinary projections | Role/policy matrix, stale version, cancellation/move/pet-change, privacy serialization and direct-action tests | planned | Remove organizer controls; preserve state/history and use compensating authorized transitions |
| EVP-06 | EVP-03..05 | Principal | After-commit event notification records/listener/notification classes | Registration, review, waitlist, promotion, cancellation, expiry, and revalidation notifications deduplicate by recipient/event/registration/transition version; rollback sends none; replay sends none; delivery uses recipient locale | Notification replay/rollback/locale/recipient tests and operation-ledger assertions | planned | Disable listener/queue delivery while retaining durable deduplication evidence |
| EVP-07 | EVP-03..06 | Principal | Class-based `ForumEventWorkspace`, form/view/presenter, shared status/actions, EN/LT/RU, accessibility styles | Authorized pet selection and organizer queue expose full, ineligible, invited, pending, confirmed, waitlisted, cancelled, and expired text states; no private evidence; loading/error/empty/offline feedback, native keyboard path, visible focus, 44px targets, no horizontal overflow, deterministic waitlist explanation | Livewire direct-call/tamper/replay tests, locale parity, browser journeys at required widths with keyboard/console/privacy checks | planned | Remove new controls/projections while leaving the domain workflow available to trusted server callers |
| EVP-08 | EVP-02..07 | Principal | `ForumEvent*Factory`, `ForumEventDemoSeeder`, event docs, architecture/data/security/authorization/testing/seeding/deployment/changelog, generator evidence | Deterministic confirmed/pending/waitlisted/ineligible/invited/cancelled/expired and scarce-capacity scenarios are repeat-safe and environment-gated; docs state only observed behavior and exact evidence | Factory inventory, fresh/repeat seed, generated source checks, documentation/source/diff/secret scans | planned | Revert demo/docs with package; never remove persisted production registrations |
| EVP-09 | EVP-01..08 | Independent reviewer then principal | Frozen attributable diff and every affected runtime boundary | Every requirement and specialist concern is dispositioned; valid findings are fixed; no unresolved critical/important state-machine, race, authorization, privacy, notification, or UI defect remains | Focused event and concurrency suites, full serial Pest, Pint, Larastan, migration/seed, dependency audits, npm build, cache smokes, browser journeys, frozen-diff review, `git diff --check` | planned | Revert the coherent commit normally only before production use; otherwise forward-fix schema/data and disable entry controls |

Implementation order is EVP-01 through EVP-09. Each behavioral step starts
with an observed failing test. The principal records specialist dispositions
before schema work, fixes focused failures before broad gates, and uses an
isolated temporary index if unrelated work appears before publication.

## Active Delivery: Meetups Implementation Plan

Status: `implementation present on synchronized main; fresh final-gate and
independent-review revalidation in progress` on 2026-08-30.

This delivery completes `/meetups` as PawCircle's mobile-first social
projection over the one canonical `ForumEvent` aggregate. It does not create a
`Meetup` model/table or parallel participant, pet, relationship, location,
invitation, notification, discussion, report, or moderation authority. The
existing P12-P16 event foundation and the active P17/P18 delivery above are
dependencies, not optional alternatives. Exact discovery evidence, principal
dispositions, protected dirty-tree ownership, specialist assignments, and the
final review ledger are recorded in
`docs/audits/meetups-completion-work-ledger.md`; abuse paths and security
acceptance are recorded in `docs/audits/meetups-threat-model.md`.

The audit began at synchronized `main`/`origin/main`
`a1fa4668f7636eec2db532f2d4a5fc7a130ec4da` with one unrelated onboarding
hunk. During read-only discovery, a separate onboarding delivery moved both
references to `7c96e504a5bfc9d8e32259971b25157bcb67fa3f` and later resumed its own
dirty edits. The principal accepts the synchronized commit as the new base,
preserves every onboarding path byte-for-byte, and will stage only Meetup-owned
paths through a temporary `GIT_INDEX_FILE`.

The fresh release revalidation began with a clean staged, unstaged, and
untracked tree at synchronized `main`/`origin/main`
`24c9b513c7b06114e416dd6007b18d41d3ca3e61`. The earlier onboarding-owned
hunk is no longer present. Historical focused counts below remain discovery
evidence only; MTP-13 through MTP-15 require newly observed commands, a frozen
current diff, independent review dispositions, and a normal fast-forward push
only if this revalidation creates an attributable commit.

### Current State And Reuse Decision

- `GET /meetups` and `GET /meetups/{event}` already render class-based
  `ForumEventDirectory` and `ForumEventWorkspace` components. A legacy
  compatibility route and home/sidebar prototype cards still expose a fake
  `PrototypeState` RSVP and must not remain a second mutation path.
- `ForumEvent` already owns the organizer User, optional organization/group,
  type, visibility, registration policy, UTC schedule plus IANA timezone,
  capacity, waitlist switch, pet policy/taxa, approximate and encrypted access
  details, Place/Venue, occurrences, versions, registrations and pet pivots,
  invitations, updates, messages, reviews, reports, history, team roles, and
  cancellation fields.
- P17/P18 storage for active scopes, immutable snapshots/decisions, operation
  replay, transitions, typed capacity, allocations/holds, ordered waitlists,
  and notification intents exists, but current application writes do not use
  it. The schema test alone is not implementation evidence.
- Confirmed strengths retained are query-level pagination, escaped user
  content, server-assigned organizer identity, separate visibility/join policy,
  account-bound invitations, encrypted legacy access fields, canonical Place
  grant/audit Actions, unified reports/updates/messages/notifications, and
  exact EN/LT/RU catalogue key and placeholder parity.
- Confirmed release blockers include an organization/private discovery leak,
  unreachable pending-invite acceptance, inactive uniqueness after the P17
  migration, replay/history loss, count-only capacity, stale pet authority,
  missing account-block enforcement, missing draft/edit/publish/removal flows,
  unsynchronized cancellation/reschedule, absent Place reveal integration,
  unbounded mixed management projections, incomplete notifications, and
  shallow race/privacy/mobile/accessibility evidence.

### Selected Domain And State Contracts

Meetup routes remain identifiers, middleware and presentation only. The route
surface becomes:

```text
GET /meetups
GET /meetups/create
GET /meetups/{event}
GET /meetups/{event}/edit
GET /meetups/{event}/manage
```

Every mutation remains a CSRF-protected Livewire call into a cohesive Action.
The server resolves the organizer and every child row; no browser-provided
organizer, owner, status, participant, pet, invitation, occurrence, Place,
Venue, update, session, capacity counter, waitlist priority, or disclosure flag
is authoritative.

The Meetup-facing Event lifecycle is:

```text
Draft -> explicit readiness validation -> Published
Published -> Cancelled
Published + clock -> derived upcoming / ongoing / past
Published + allocations -> derived available / full / waitlist
```

The richer general Event status enum remains readable for backward
compatibility. Meetup cards do not require cron and do not persist `Full` or
`Completed` merely because time or capacity changed. Drafts remain organizer
only; cancelled records retain safe participant history and accept no new
participation; material mutation after start is restricted to an authorized
update, safety action, correction, or cancellation.

Visibility remains independent from admission. Public, followers, members,
organization, group, unlisted, invitation, and private modes reuse canonical
User/SocialActor/group/organization/block authority. Open admission confirms
only after allocation; approval creates `Pending`; invitation admission
requires the current account-bound invitation; full eligible requests enter
the canonical waitlist only when enabled.

The core participation graph is:

```text
none -> Pending | Confirmed | Waitlisted
Pending -> Confirmed | Waitlisted | Rejected | Withdrawn
Waitlisted -> Confirmed | Withdrawn | CancelledByOrganizer
Confirmed -> Withdrawn | CancelledByOrganizer | CheckedIn
CheckedIn -> Attended | Completed
```

Legacy terminal statuses remain readable. A terminal rejoin creates a new
immutable participation generation rather than rewriting the historical row.
The organizer is a host and consumes no attendee row or human-capacity unit.
Human quantity is `1 + guest_count`; selected pets never consume the human pool
unless a separate typed pet pool is deliberately configured.

Meetup participation writes use one short transaction that locks the event,
selected occurrence, current active registration and deterministic waitlist
row before deciding state. A canonical request checksum binds operation type,
event, occurrence, principal, guest quantity, pets, consent and accepted
rules. Exact replay returns the same result; a changed payload conflicts.
SQLite uses the configured immediate transaction and row-locking adapters use
`FOR UPDATE`. The generalized P17 capacity-pool/hold/allocation tables remain
future wider-event infrastructure and are not described as the active Meetup
allocator.

Attending pets use `ForumEventRegistrationPet` and canonical Taxon data. At
register, approval, promotion and selected-pet update, the transaction requires
an active account, active `PetProfile`, and current owner/manager authority for
which canonical access grants `ManageCare` or `ManageSocial`; view-only access
does not authorize Meetup representation. This supports current caregivers and
social managers without treating ordinary viewing as authority to register a
pet. Pending, invited, suspended, revoked, future, expired, explicitly
denied, or legacy-revoked authority fails. Public presentation uses confirmed
aggregate counts and localized species only; private pet identifiers/media
remain absent unless the viewer independently has canonical access.

Discovery and cards select only public/approximate location fields. Requested,
waitlisted, rejected, withdrawn, removed, blocked, and suspended accounts never
receive protected access. A current confirmed participant or authorized
operator may explicitly reveal a matching canonical Place through an active
event/purpose grant; `RevealPlaceExactLocation` reauthorizes under lock and
audits the view. Legacy exact location remains encrypted and query-minimized.
No exact address, coordinates, private instructions, room details, URL, or
emergency plan may enter unauthorized HTML, Livewire state, hidden controls,
metadata, JSON, notification text, or search.

Account blocks prevent organizer/participant discovery, direct view, invite,
join, approval, attendee communication, participant identity, and exact access.
Existing participation is retained without exposing either party; participant
identity, messaging and protected access are denied immediately. A block alone
does not silently rewrite attendance history or reallocate a seat. Explicit
leave/removal remains the audited capacity-releasing transition.
Inactive/suspended organizers cannot
create or mutate and their Meetups accept no new participation until a
canonical moderation decision. Reports, safety suspension, and unified
moderation are reused; no moderator receives pet ownership authority.

### Delivery Ledger

| ID | Dependency | Owner | Affected paths/modules | Acceptance criteria | Required verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| MTP-01 | Repository contract; P12-P18 sources; synchronized base | Principal plus MEET-A through MEET-O read-only specialists | Mandatory docs, routes, Event/Pet/Place/Social/moderation/notification code and tests, Meetup ledger/threat model | Current implementation, reuse decision, authority conflicts, dirty ownership, lifecycle, privacy, concurrency, UX and test gaps are evidence-backed before production edits | Git/source inventory, 15 specialist reports, principal reproduction, 32-test/664-assertion focused baseline | completed | Revert only Meetup planning/audit additions |
| MTP-02 | MTP-01 | Principal | Focused Meetup security, lifecycle, participation, pet, place, invitation, notification, output, query, locale, accessibility and concurrency tests | RED contracts reproduce organization/private projection, blocked access, pending-invite deadlock, draft leak, inactive scope, changed-payload replay, final-slot/promotion race, stale pet/location authority, forged child IDs and cancellation/history | Serial focused tests plus isolated two-process SQLite workers | completed; 72 tests / 38,738 assertions focused checkpoint | Revert only new tests/fixtures |
| MTP-03 | MTP-02 | Principal | `ForumEvent` visibility scopes, `ForumEventPolicy`, Meetup controllers/routes, canonical block integration and private-response boundary | Every visibility branch is query-scoped; pending recipient gets safe invite preview; confirmed participant access survives invite expiry where appropriate; blocked/inactive parties fail discovery/view/join/message/reveal; drafts never project | Visibility/block/account/route/private-header tests, HTML and Livewire projection assertions | implemented; final gates pending | Disable protected routes or restore prior scope only with the new deny-first tests retained |
| MTP-04 | MTP-02..03; P12-P14 | Principal | Dedicated class-based Event editor/form/views, create/update/publish/material-change Actions, routes/controllers, occurrences/history | Create saves an incomplete private draft; explicit publish validates complete future schedule; edit reauthorizes under lock; material capacity/privacy/location changes synchronize the base occurrence and notify eligible participants; past material changes fail | Create/edit/publish/stale/material-change/authorization tests; browser journey pending | implemented; browser/final gates pending | Remove new editor routes; retain drafts/history and disable publication; forward-fix persisted data |
| MTP-05 | MTP-02..04 | Principal | Participation operation/transition models, active-scope invariant, locked registration/capacity/waitlist service and factories | One active participation is DB-enforced; same replay is stable and changed payload conflicts; terminal rejoin creates a new generation; capacity never exceeds the event/occurrence bound; approval and deterministic promotion are atomic | Constraint/state/replay tests and real two-process final-slot race | implemented; final gates pending | Disable admission; retain immutable participation evidence; forward-fix after production writes |
| MTP-06 | MTP-05; P16 canonical pet authority | Principal | Current pet eligibility evaluator, registration/review Actions, workspace pet projection and localized species | Owned and current actively managed visible pets may attend; foreign/inactive/denied/pending/invited/revoked/expired authority fails under the transaction; approval rechecks authority; private pet remains aggregate-only | Manager-state/stale/private projection tests plus adjacent Pet regressions pending final run | implemented; final gates pending | Disable pet selection without deleting historical pivots; never create parallel ownership data |
| MTP-07 | MTP-03..06; canonical Place boundary | Principal | Meetup approximate/manual exact fields, current-participation selector, canonical Place grant/reveal and private response headers | Cards use approximate fields; manual exact data is confirmed-state scoped; current confirmed users with a matching Place grant explicitly reveal audited details; requested/waitlisted/removed/ended/blocked users cannot; stale grants become unusable through current-state reauthorization | Exact marker absence, positive grant/audit, stale removal and private-header tests | implemented; final gates pending | Hide reveal controls; existing event grant stays unusable without current participation |
| MTP-08 | MTP-03..07 | Principal | Account-bound invitation accept/revoke/list Actions and safe preview; existing recipient-locale `ForumNotification` delivery | Invitation is account/event scoped, expiring and revocable; acceptance rechecks capacity/eligibility; wrong user fails; material update/removal/cancellation notifications are deduplicated and exact-location safe | Wrong-user/expiry/revoke/capacity/dedupe/locale/privacy tests | implemented; final gates pending | Disable delivery while retaining domain state; revoke pending access without deleting invitation history |
| MTP-09 | MTP-03..08 | Principal | Dedicated manage route/component, paginated registration queue, bounded invitations, removal/safety Actions, canonical reports/blocks | Requests, going, waitlist, invitations, updates and history are bounded and phone-oriented; approve/reject/remove/promote/cancel reauthorize locked scoped rows; reports and blocks are reused | Child-ID authorization, pagination/query, block/removal/cancellation tests | implemented; browser/final gates pending | Remove manager controls while retaining authoritative Actions/history |
| MTP-10 | MTP-04..09 | Principal | Meetup directory/detail/editor/manage Livewire and Blade | Discover/My/Invitations remain simple; cards have contextual CTAs; create/edit choices are semantic; loading/offline/error states and 44px controls are present | Livewire semantic tests pass; real browser widths/zoom/keyboard/console pending | implemented; browser gate pending | Revert presentation only while keeping secure server Actions |
| MTP-11 | MTP-03..10 | Principal | EN/LT/RU catalogues, validation attributes, recipient locale, `PetSpeciesLabel`, timezone and bounded query projections | Recursive key/placeholder parity remains; species/status labels are localized; directory/detail/manage use aggregates and pagination; query growth stays bounded | Localization parity and focused query tests passed; full/browser gates pending | focused verified; final gates pending | Revert locale/performance slice independently |
| MTP-12 | MTP-03..11 | Principal | Event/registration factories, canonical Event/Meetup/security/frontend/testing docs, changelog and generated evidence checks | Factories cover draft/published/approval/invite/capacity/past/cancelled states; docs describe only observed behavior; generated evidence is not hand-edited | Factory tests and fresh/repeat seed passed; generator/docs checks pending | in progress | Revert seed/docs/evidence independently; never hand-edit generated outputs |
| MTP-13 | MTP-02..12 | Principal | All affected runtime boundaries and repository | Focused Meetup, Pet, Social/block, auth, privacy, security, localization, accessibility and query suites pass, followed by Composer/platform/audits, Pint, Larastan, full serial Pest, fresh DB, seed repeat, npm audit/build, route/config/view cache and diff checks | Exact observed commands, exit codes and counts recorded; no historical pass substituted | planned | No publication; forward-fix attributable failures and report independent environmental blockers exactly |
| MTP-14 | MTP-13 | MEET-R1 through MEET-R10 independent read-only reviewers; Principal dispositions | Frozen attributable diff and command ledger | Architecture, lifecycle, pet, location, security, UX/mobile, accessibility, localization, performance and test reviewers reproduce every material finding; principal fixes all attributable High/Critical defects and reruns affected checks | Frozen-diff reports, finding disposition table and post-fix evidence | planned | Revert unsafe finding-specific fix; reopen affected delivery item |
| MTP-15 | MTP-13..14 | Principal | Exact attributable candidate on current `main` | Complete diff and staged diff contain no onboarding/user work; `git diff --check` passes; coherent commit is created; origin is unchanged or safely refreshed; normal fast-forward push to `origin/main` succeeds | Temporary-index ownership, staged file list/diff, commit hash, pre-push origin comparison and push output | planned | No commit/push on any failed material gate; after publication use a normal revert/forward fix, never history rewrite |

Implementation order is `MTP-01` through `MTP-15`. Tests precede every
behavioral fix. No requirement, compliance row, forum atom, quality gate,
browser journey, commit, or push moves to verified from file presence, a
specialist opinion, a historical run, or an unobserved command.

## Active Delivery: PLA-P04 Scalable Place Directory Query

Status: `approved; canonical plan saved and specialist discovery in progress`
on 2026-08-30.

This delivery replaces the capped, collection-backed `PlaceCatalog` directory
pipeline with a policy-aware database query and bounded presentation pipeline.
The complete directory remains server rendered and usable without JavaScript,
maps, geolocation, or an external provider. Rich demo content may decorate only
the current result page; it cannot decide visibility, filtering, ordering,
counts, selection, or pagination. `PRD-PLACE-001` remains unchanged until the
more-than-500-place acceptance dataset and its performance evidence pass.

The task starts on synchronized `main` at `ae4ac32`. The pre-existing changes
to this canonical plan, the seed-coverage fixture and the untracked audit
ledgers are unrelated user-owned work and remain byte-preserved. Specialist
discovery is read-only and uses the exclusive scopes recorded in
`docs/audits/places-directory-query-work-ledger.md`; the principal owns every
cross-module decision and tracked edit. Any publication uses a temporary
`GIT_INDEX_FILE` so unrelated changes cannot enter the attributable commit.

### Delivery contract

- One focused query object returns an Eloquent builder whose first concern is
  active, non-archived, non-merged, policy-equivalent public/account/owner and
  organization visibility. Every search, filter, sort, count, selection and
  page operates inside that boundary.
- Supported category, species, size, accessibility, service, opening,
  verification, region, text and related directory controls use portable
  query-builder predicates over canonical searchable fields. Unsupported
  prototype-only controls fail safely or are removed from the active contract;
  no PHP collection is a second query engine.
- Offset pagination remains the public contract for numbered, crawlable links.
  Every ordering has a deterministic stable-key or identifier tie-breaker,
  validated query parameters survive page links, and stale pages resolve to a
  bounded valid page without loading the complete result set.
- Queries select only card projection fields and relationship keys. Only
  relations used by current-page presentation are eager loaded; query and
  memory budgets stay independent of complete table size.
- Additive indexes follow the observed visibility/filter/order predicates and
  are verified against SQLite and the supported PostgreSQL query grammar. No
  database-specific SQL is scattered through controllers, models, Actions,
  Livewire components, or Blade.

| ID | Dependency | Owner | Affected paths | Acceptance criteria | Verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| PLQ-01 | PRD-PLACE-001, PLA-P04, database-performance and place-visibility contracts | Principal plus Eloquent, portability, authorization, indexing, pagination, performance and accessibility specialists | Canonical plan, work ledger, Places query/schema/presentation/test inventory | Existing behavior, supported filters, policy boundary, query shape, indexes, accessibility and dirty-tree ownership are reconciled before implementation | Read-only source/schema/test inventory and dispositioned specialist reports | in progress | Revert this delivery plan and ledger only |
| PLQ-02 | PLQ-01 | Principal | New large-dataset Places performance/feature tests and test helpers | More than 500 accessible records prove completeness, private exclusion, correct filters, stable ordering, deterministic pages, safe invalid input, explicit query budget, bounded memory and no N+1 | Focused red run records failures caused by the current 500-row cap and collection pipeline | planned | Revert only the new acceptance tests/helpers |
| PLQ-03 | PLQ-02 | Principal | Additive Places directory projection migration, `Place`, factory and environment-safe demo synchronization if required | Canonical searchable scalars/relations are portable, default safely, retain existing data, and carry indexes derived from real predicates and ordering | Fresh migration, rollback rehearsal before production use, schema/index assertions, SQLite/PostgreSQL grammar inspection | planned | Roll back the additive migration before production use; forward-fix after production writes |
| PLQ-04 | PLQ-03 | Principal | `VisiblePlacesQuery` or equivalent focused query object and visibility scopes | The object returns `Builder<Place>`; active/archive/merge and policy-equivalent public/account/organization/owner/grant visibility precede all search/filter/sort/page operations; selected columns retain relation keys | Query-object, policy matrix and SQL/query-log assertions | planned | Revert query object and restore prior call site while retaining additive nullable fields |
| PLQ-05 | PLQ-04 | Principal | Place directory request/query/presenter/catalog boundaries | Every supported persisted filter, deterministic ordering, count and pagination run in the database; the 500 cap, unbounded `get`, filtering loops, `usort`, collection pagination and full-result decoration are absent | Focused filters/order/pagination tests, source architecture assertions, explicit query and memory budgets | planned | Restore the prior presenter boundary without dropping migrated data |
| PLQ-06 | PLQ-05 | Principal | Server-prepared directory presentation, Blade components and pagination links | Current-page rows, selected row, summaries, map fallback and comparison are bounded prepared values; GET forms and page links preserve validated parameters; the complete list works with JavaScript disabled and no external services | Blade/HTTP accessibility assertions and real-browser no-JavaScript desktop/mobile journeys | planned | Revert presentation-only changes while retaining query correctness |
| PLQ-07 | PLQ-02..06 | Principal | Places/data/performance/testing/accessibility docs, progress records, changelog, and finally PRD-PLACE-001 evidence | Documentation matches observed behavior; PRD-PLACE-001 is promoted only after the large-dataset suite passes; generated evidence is updated only through its generator | Large-dataset evidence first, then documentation generator/check and source/diff review | planned | Revert documentation/evidence independently; never retain an unverified completion claim |
| PLQ-08 | PLQ-01..07 | Independent final reviewer then principal | Frozen attributable diff and all affected runtime boundaries | Every material finding is reproduced and dispositioned; valid findings are fixed and affected checks rerun; all requested gates pass before an isolated commit and push on `main` | Focused Places/performance tests, full sequential Pest, Pint, Larastan, migration/seed/repeat, dependency and npm audits, Vite build, cache smokes, no-JS browser checks, `git diff --check`, staged diff and push result | planned | Revert the coherent commit normally; forward-fix migrated production data |

Implementation order is `PLQ-01` through `PLQ-08`. Tests precede production
behavior. The independent reviewer receives a frozen attributable diff only
after implementation and focused checks are green; PRD evidence is the final
documentation promotion after the large-dataset proof, never a statement of
intent.

## Active Delivery: Relational Place Contributions And Moderation

Status: `planned; implementation begins test-first` on 2026-08-30.

This delivery implements `PLA-P08` through `PLA-P11` and the directly required
`PLA-P02`, `PLA-P03`, `PLA-P17`, `PLA-P19`, and `PLA-P20` support. It replaces
shared-looking corrections, warnings, reviews, and reports in encrypted
account-local `places.state.v1` with canonical place relations, completes the
existing question/official-answer relation, and reuses the unified forum
moderation case boundary for Place subjects. Private saves, follows,
collections, generalized location, visits, check-ins, invitations, claims,
recent history, and unpromoted compatibility keys remain encrypted account
state and are outside this delivery.

The task starts from clean `main` at `ae4ac32`, aligned with `origin/main`.
The earlier PLA-P06 migrations and publication workflow are preserved. All
schema work is additive; the compatibility backfill never deletes or mutates
legacy payloads. Contracting legacy keys is prohibited until a later release
proves relational parity, rollback, retention, and production reconciliation.

### Product And Data Decisions

- One current review is allowed per authenticated author and place. An author
  may replace it through a versioned edit, delete it reversibly, and restore it.
  Optional managed-pet context is accepted only from a current pet-management
  relation. Visit eligibility is derived server-side from existing private
  visit compatibility state; an unverified visit context is labelled rather
  than represented as verified.
- Review anonymity is presentation-only. Author identity remains available to
  authorized moderators and is never replaced with a hard-coded persona.
- Correctable Place fields use a closed field map. Submission captures the
  server-side original value and `places.lock_version`; acceptance applies a
  field-specific mutation under a row lock and records the applied value and
  immutable event history.
- Low/medium warnings publish immediately with a bounded default expiry;
  high/critical warnings enter `needs_review`. Publication, dispute,
  resolution, expiry, removal, rejection, and appeal are explicit enum-backed
  states. Expiry is enforced on reads and by a repeat-safe maintenance Action,
  so correctness does not depend solely on scheduler availability.
- Place questions use open, answered, needs-information, duplicate, closed,
  hidden, and removed states. Exactly one official answer projection is kept,
  while every edit appends an immutable answer version. Closing/reopening and
  official answers require current place-management or administrator scope.
- Place, correction, warning, review, and question reports are polymorphic
  `ForumReport` records. Reporter identity and evidence remain hidden from the
  subject and place managers; existing idempotent case opening, assignment,
  action events, appeals, reversal, retention, and administrator-only
  operations remain authoritative.
- Database notifications use unique deduplication keys, the recipient's
  validated locale, and `DB::afterCommit`. Database delivery is the synchronous
  fallback and does not require a worker.

### Work And Specialist Ledger

| ID | Dependency | Exclusive owner | Affected paths or modules | Acceptance criteria | Verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| PLC-01 | PLA-P08..P11 and canonical moderation/review/notification requirements | Principal plus read-only corrections, warnings, reviews, questions, reports, moderation, authorization, migration, and final-review specialists | Canonical docs, PlaceState compatibility behavior, current Places/forum moderation schema/actions/policies/UI/tests | Every legacy key, reusable boundary, product decision, conflict, and attributable path is mapped before production edits; specialist reports are structured and independently reconciled | Repository inventory, specialist reports, clean-base confirmation | in progress | Revert this planning section only |
| PLC-02 | PLC-01 | Corrections specialist implements; independent authorization reviewer | New correction enums/models/factories/migration, focused Actions/policy methods/tests | Exact field/fact, original value/version, proposal, explanation, evidence, submitter/reviewer, moderation status, resolution, canonical mutation link, deduplication, stale-conflict handling, and immutable history are relational and policy-scoped | Observed red/green correction feature, policy, schema, concurrency, and cross-account tests | planned | Roll back additive tables only before production writes; afterward forward-fix |
| PLC-03 | PLC-01 | Warnings specialist implements; independent moderation reviewer | New warning enums/models/factories/migration, lifecycle Actions/tests | Category, severity, scope, source, evidence, one confirmation per actor, publication, expiry, dispute, resolution, appeal, moderation history, duplicate/rate controls, and read/write expiry fallback are durable and auditable | Observed red/green warning lifecycle, two-account, expiry, appeal, deduplication, and race tests | planned | Preserve warning/history rows; disable UI and forward-fix after writes |
| PLC-04 | PLC-01 | Reviews specialist implements; independent authorization reviewer | New review/response enums/models/factories/migration, Actions/tests | Authenticated author, optional server-resolved managed pet, eligibility context, rating dimensions, text, presentation anonymity, moderation, immutable versions, reversible delete/restore, exact review response relation, verified manager scope, and response versions are enforced | Observed red/green cross-account, hard-coded-author, uniqueness, moderation, restore, manager-scope, aggregate, and race tests | planned | Preserve versions and soft-deleted rows; disable mutations and forward-fix |
| PLC-05 | PLC-01 | Questions specialist implements; independent notifications reviewer | Existing question/answer tables and models plus additive versions/events, Actions/tests | Cross-account visibility, explicit states/moderation, official-answer authorization, answer versions, close/reopen, reporting, and after-commit deduplicated manager/author notifications are complete | Observed red/green question concurrency, authorization, version, closure, report, locale, and notification tests | planned | Retain existing answer projection; disable new transitions and forward-fix |
| PLC-06 | PLC-03..05 | Reports/moderation specialist implements; independent privacy reviewer | `SubmitForumReport`, report policy/presenters, Place subjects, canonical cases/actions/appeals/tests | Place contribution reports enter one canonical moderation boundary; reporter privacy, idempotency, assignment, action history, resolution, appeal, reopen/reversal, and typed subject relations are proven without copying private evidence | Focused report/case/action/appeal/privacy tests and direct authorization checks | planned | Remove Place subject allowlist/UI while retaining moderation records |
| PLC-07 | PLC-02..06 | Principal implements integration; independent accessibility reviewer | Class-based Place contributions Livewire component/form/view, routes/detail composition, EN/LT/RU | Accessible localized forms and histories expose precise loading, validation, success, empty, offline, status, focus, keyboard, touch, responsive, and privacy behavior; public state stays scalar and every mutation reloads and authorizes its target | Livewire direct-action, localization, architecture, browser, console, keyboard, and overflow checks | planned | Remove component mount while retaining relational data and Actions |
| PLC-08 | PLC-02..06 | Migration specialist implements; independent data reviewer | Compatibility backfill Action/command/checkpoint model, factories, deterministic demo seeder, deployment/data-model docs | `places.state.v1` contributions import in bounded chunks with dry-run, checkpoints, deterministic reconciliation, retry safety, per-record transactions, no legacy deletion, repeatable seeds, parity report, and documented retention/rollback | Backfill red/green repeatability, partial-failure/resume, fresh migration/rollback, complete seed, repeat seed, and production-guard tests | planned | Stop backfill; preserve checkpoints, targets, and encrypted source; forward-fix discrepancies |
| PLC-09 | PLC-02..08 | Independent corrections/warnings/reviews/questions/reports/moderation/authorization/migration reviewers plus principal | Frozen attributable diff and recorded review dispositions | Each specialist reviews outside its implementation scope; principal reproduces material findings, records every disposition, fixes valid findings, and reruns affected gates | Frozen diff packages, review reports, focused reruns, `git diff --check` | planned | Revert unsafe finding-specific hunks only |
| PLC-10 | PLC-09 | Independent final reviewer plus principal | PLA status, current progress, compliance/data/security/authorization/testing/seeding/deployment/localization docs, changelog, full repository | All Places/moderation/migration/seed/Pest/Pint/Larastan/dependency/Vite/cache/browser/diff/secret gates are observed; `PRD-PLACE-002` advances only from those results; one attributable commit is pushed on `main` only if every required gate permits | Exact final command output, temporary-index staged diff when the tree becomes shared/dirty, commit and push evidence | planned | Revert coherent task commit normally; never delete imported legacy state |

Implementation order is PLC-01, the first failing contracts for PLC-02 through
PLC-06, their minimal schema and Actions, PLC-07, PLC-08, then independent
PLC-09 review and PLC-10 release gates. Every behavior change begins with an
observed failing test. The principal owns all cross-module decisions and final
edits; specialists receive exclusive scopes and may not silently broaden them.

## Active Delivery: Canonical Place Facts, Schedules, And Emergency Truth

Status: `planning and specialist discovery in progress` on 2026-08-30.

This delivery replaces the production Places catalogue's fixture/default truth
with normalized canonical records under `PRD-PLACE-002`, `PRD-PLACE-003`,
`PLA-P02`, `PLA-P05`, and `PLA-P15`. Dedicated relational records own
categories and localized names, contacts, operating schedules and exceptions,
services and eligibility, structured rules/facts, and source/provenance.
Unknown, stale, closed, temporarily closed, appointment-only, and unavailable
states remain explicit; no category or missing record implies a confirmed
capability. Important replacements retain immutable source evidence and
version history.

The delivery starts on `main` at `ae4ac32`, aligned with `origin/main`.
Specialist discovery is read-only and recorded in
`docs/audits/place-canonical-facts-work-ledger.md`; the principal owns every
cross-domain decision and tracked edit. Concurrent changes remain user-owned
until exact attributable hunks are established. Publication uses a temporary
`GIT_INDEX_FILE` while unrelated work is present.

| ID | Dependency | Owner | Affected paths | Acceptance criteria | Verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| PLA-CF-01 | PRD-PLACE-002/003, PLA-P02/P05/P15, current place authority | Principal plus schema, schedule/DST, taxonomy, provenance, accessibility, migration, and testing specialists | Canonical requirements, current Places schema/catalogue/presentation, specialist ledger | One additive normalized design maps every requested record, public/private projection, source/freshness state, idempotency boundary, index, history rule, and rollback before production edits | Repository inventory, specialist reports, principal conflict review | in progress | Revert planning-only additions |
| PLA-CF-02 | PLA-CF-01 | Principal | `tests/Feature/Places/**`, `tests/Feature/PlaceDirectoryTest.php`, focused unit tests | Failing contracts cover schema integrity, duplicate/idempotent facts, authorization, privacy, DST gaps/folds, overnight intervals, date exceptions, temporary closures, appointment-only, stale/unknown states, service/species/size eligibility, contradictory intervals, unavailable services, and concurrency | Focused red runs through the isolated repository test wrapper | planned | Revert only new red contracts |
| PLA-CF-03 | PLA-CF-02 | Principal | Additive Places migration; `app/Enums/Place*`; `app/Models/Place*`; `database/factories/Place*` | Stable categories/localized names, ordered contacts, schedule/timezone/interval/exception/closure records, services/offerings/species/sizes, structured rule/safety/accessibility/parking/transport facts, provenance/current-version/history records, constraints, casts, encryption, relationships, and factories satisfy SQLite-portable integrity | Schema/model/factory contracts plus isolated migration rollback/reapply | planned | Roll back only before canonical writes; after writes retain schema and forward-fix |
| PLA-CF-04 | PLA-CF-03 | Principal | Focused schedule and freshness domain services | One injected reference instant resolves open, closed, opening soon, unknown, stale, temporarily closed, and appointment-only in the place's IANA timezone; overnight, DST gap/fold, exception precedence, and missing timezone behavior are deterministic | Clock-controlled unit/feature tests in Europe/Vilnius and a second DST zone | planned | Revert resolver while retaining normalized schedule evidence |
| PLA-CF-05 | PLA-CF-03..04 | Principal | Typed fact-management data, Actions, `PlacePolicy`, provenance/version services | Policy-authorized idempotent create/replace/retire operations lock the place/current fact, preserve submitted evidence, reject stale/concurrent versions and contradictory schedule writes, encrypt private contacts/evidence, and append immutable history | Policy matrix, direct Action, privacy, idempotency, and concurrent-process tests | planned | Disable mutations and forward-fix; never delete retained evidence/history |
| PLA-CF-06 | PLA-CF-03..05 | Principal | Place taxonomy/reference seeder, bounded canonical fixture synchronization, `DatabaseSeeder` integration | Existing demo places receive deterministic canonical facts without IDs changing; repeat seed is count-stable; production paths have no invented Vilnius coordinates/species/services/default facts and no rich-fact fallback to fixture catalogues | Focused seeder tests, isolated fresh/repeat seed, fixture/default source scan | planned | Remove demo-only synchronization before production use; preserve user facts |
| PLA-CF-07 | PLA-CF-04..06 | Principal | `PlaceCatalog`, `PlacePresenter`, `PlacePublicProjection`, detail/directory controllers and query consumers | Bounded eager-loaded database projections replace fixture/default facts on directory, detail, and emergency paths; source/freshness/verification are prepared before Blade; emergency eligibility requires canonical veterinary service plus species capability | Focused Places tests, query-count assertions, no-fixture production-path scan | planned | Revert readers only while normalized records remain available for forward recovery |
| PLA-CF-08 | PLA-CF-07 | Principal plus accessibility specialist review | Place Blade/components/SCSS if needed; `lang/{en,lt,ru}/{places,place_directory}.php`; browser script | Reviewed EN/LT/RU labels explain unknown/stale/source/scope and unconditional call-first safety; passive Blade renders semantic non-color status, safe phone/source actions, focus/touch/reflow behavior, and no private evidence | Locale/architecture tests plus desktop, 375px, 320px, keyboard, console, and screenshot review | planned | Revert presentation and translations together without changing canonical facts |
| PLA-CF-09 | PLA-CF-03..08 | Principal | Data/security/authorization/testing/seeding/deployment/current-progress/compliance/changelog docs and generators | Canonical documents describe only observed behavior, migration/forward-fix boundaries, public/private data, schedule semantics, and exact verification evidence; generated files remain byte-current | Documentation generation/checks, source/secret/diff review | planned | Revert documentation/evidence with the attributable implementation slice |
| PLA-CF-10 | PLA-CF-02..09 | Independent final reviewer and principal | Frozen attributable diff and complete repository gates | Every material finding is reproduced and dispositioned; valid findings are fixed and affected checks rerun; focused Places, rollback/reapply, repeat seed, full Pest, Pint, Larastan, dependency audits, Vite, caches, browser, docs generation, diff, and secret gates pass before commit/push | Frozen review package and exact observed final commands | planned | Do not publish on an open material finding or failed gate; revert coherent commit normally after publication |

Implementation order is `PLA-CF-01` through `PLA-CF-10`. Every production
behavior begins with an observed failing test. Schema changes are additive and
SQLite-portable; lifecycle checks use disposable databases only. Canonical
readers switch only after deterministic synchronization is proven, and the old
fixture/default path is removed rather than retained as silent fallback truth.

## Active Delivery: Shared Directory Card Completion

Status: `inventory and implementation planning in progress` on 2026-08-30.

This delivery completes the explicitly open work in
`docs/plans/shared-directory-card-system-plan.md` without creating a
domain-neutral mega-component. The established `x-directory-card` shell stays
limited to proven media/body/footer directory topology; shared media,
heading, description, and action-row leaves may be adopted independently;
domain-rich operational cards retain their own implementation. Existing
routes, queries, authorization, private-media delivery, and domain behaviour
are invariant.

The task starts on `main` at `5008937` in a materially dirty shared tree whose
staged email-verification and Places work is unrelated user-owned work. The
specialist ledger is
`docs/audits/shared-directory-card-completion-work-ledger.md`. Discovery
specialists are read-only, the principal owns every tracked edit and
cross-family decision, and publication uses a temporary `GIT_INDEX_FILE` so
pre-existing staged work remains byte-preserved and excluded.

| ID | Dependency | Owner | Affected paths | Acceptance criteria | Verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| SDC-01 | Existing shared-card contract and active plan | Principal plus component-API and consumer-inventory specialists | Shared-card plan, component contract, UI inventories, Blade/CSS/test inventory | Every card, row, identity, compact result, media-bearing item, and footer action is mapped to adopt-shell, adopt-leaf, keep-domain, merge, or retire; every primitive and bounded prop is documented | Exhaustive `rg` inventories, component call graph, zero-consumer evidence, specialist reconciliation | in progress | Revert planning and audit records only |
| SDC-02 | SDC-01 | Principal plus media specialist | Shared card/image components, presenters, private media routes, relevant tests | Intrinsic dimensions, responsive sources/sizes, loading priority, local fallbacks, accessible linked media, layout stability, and policy-protected delivery are correct for every affected family | Rendered markup contracts, route/policy tests, browser image and geometry assertions | planned | Revert family-specific leaf adoption and media tests together |
| SDC-03 | SDC-01 | Principal plus interaction/accessibility specialists | Card footers, shared actions, Livewire/forms, CSS, EN/LT/RU | Navigation, toggle, destructive, and submit controls retain native semantics; no nested interaction; 44px targets; loading, disabled, active/pressed, error, offline, and duplicate-submit states are exposed where applicable | Test-first Blade/Livewire contracts, localization parity, keyboard/focus/forced-colors/reduced-motion checks | planned | Revert the affected family composition while retaining its prior authorized action path |
| SDC-04 | SDC-01..03 | Principal plus responsive-geometry specialist | Proven public-directory and compact consumer views, shared CSS/Blade primitives | Text-only escaped headings and bounded variants are explicit; media-less and compact abstractions exist only with two equivalent consumers; supported widths and 200/400 percent zoom avoid clipping and two-axis scrolling | Component tests plus authenticated geometry matrix at 320, 375, 768, 1024, 1440, and 1920 pixels | planned | Revert new shared variant and restore the prior domain shell |
| SDC-05 | SDC-01..04 | Principal | Place, search-case, pet-profile, feed, group-post, medical, care, device, and booking card families | Equivalent implementations merge, eligible leaves/shells migrate, domain-specific topology stays local, and obsolete CSS/components are removed only after reproducible zero-consumer proof | Affected feature suites, architecture ratchets, source inventory, Blade compilation | planned | Revert one family migration independently; restore removed asset only with its proven consumer |
| SDC-06 | SDC-05 | Browser-testing specialist and principal | Browser scripts and generated screenshot artifacts | Authenticated EN/LT/RU journeys prove keyboard access, screen-reader names, focus, forced colors, reduced motion, 44px targets, responsive media, zoom, no two-axis scroll, no console errors, and unchanged destinations | Repeatable browser commands, geometry output, screenshot capture | planned | Revert browser-only assertion changes if they encode an invalid contract |
| SDC-07 | SDC-01..06 | Independent visual reviewer and principal | Frozen attributable diff and screenshots | Independent review dispositions every material API, visual, responsive, accessibility, localization, and regression finding; valid findings are fixed and affected checks rerun | Frozen-diff review package, screenshot report, finding disposition ledger | planned | Revert finding-specific changes that cannot be made safe |
| SDC-08 | SDC-07 | Principal | Complete attributable implementation, tests, documentation, and evidence | Every open checklist item is updated individually from observed evidence; focused and affected tests, Blade compilation, full Pest, Pint, Larastan, npm build, browser checks, screenshot review, and `git diff --check` pass before isolated publication | Exact final commands, temporary-index staged diff, normal commit and push result | planned | Revert the coherent attributable commit normally; never rewrite history |

Implementation order is SDC-01 through SDC-08. Behavioural changes begin with
an observed failing test. Migration waves stay family-sized and independently
revertible; no CSS or component is retired before all Blade, PHP, JavaScript,
test, and documentation consumers are proven absent.

## Active Delivery: PLA-P07 Place Management And Verification Claims

Status: `approved; implementation planning and specialist discovery in progress`
on 2026-08-30.

This delivery implements `PLA-P07` as a canonical relational claim, scoped
management-authority, verification-evidence, audit, notification, expiry,
revocation, transfer, and abuse-report workflow. The exact specialist
ownership and preservation boundary is recorded in
`docs/audits/places-management-verification-work-ledger.md`. The existing
dirty tree is user-owned; the principal is the only editor for this package.

### Delivery contract

- Claimant, represented organization, requested role and scopes, verification
  method, safe evidence metadata, lifecycle state, reviewer decision,
  expiration, revocation, optimistic version, idempotency, and immutable audit
  history are relational and server-authoritative.
- Evidence files use a configured private disk, generated paths, content-based
  validation, request-time authorization, parent containment, and cleanup on
  failed persistence. No unrestricted public URL is created.
- The state machine has exactly `pending`, `needs_information`,
  `under_review`, `approved`, `rejected`, `expired`, `revoked`, and
  `superseded`; every transition is a focused authorized Action using a short
  transaction and locked current row.
- Approved claims grant only explicit current capabilities. Removal, scope
  change, expiry, revocation, and controlled transfer invalidate future
  authority without erasing historical attribution.
- Notifications are dispatched only after committed transitions and are
  idempotent. Audit and abuse-report records retain safe identifiers and
  reasons without evidence bodies, tokens, storage paths, or secrets.
- All user-facing states, errors, validation, notifications, and management
  presentation use aligned EN, LT, and RU translation contracts.

| ID | Dependency | Owner | Affected paths/modules | Acceptance criteria | Required verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| PCLM-01 | PLA-P02, PLA-P03, PLA-P05, PLA-P06; approved request | Principal | Canonical plan, work ledger, Places/organization/private-file/authorization docs and current code | Existing behavior and dirty-tree ownership are mapped; specialist decisions are dispositioned before schema design | Read-only inventory, `git status`, specialist reports | in progress | Revert package documentation only |
| PCLM-02 | PCLM-01 | Principal | Claim/scope/audit/evidence/report enums, additive migration, models, factories | Portable relational integrity, hidden sensitive fields, private evidence metadata, exact lifecycle, optimistic versions, conflict and idempotency constraints | Red schema/model/factory/state tests, migration rollback | planned | Roll back additive migration before use; forward-fix after production use |
| PCLM-03 | PCLM-02 | Principal | Submission/evidence Actions, claim policy, private evidence controller/response, rate limit | Eligible active authenticated members submit once; organization identity is server-authoritative; actual content is validated; foreign/direct access denies without disclosure | Red submission, wrong-account, conflict, upload, rate, and private-file tests | planned | Disable entry routes and retain private evidence for reviewed cleanup |
| PCLM-04 | PCLM-02..03 | Principal | Needs-information, resubmit, review-start, approve, reject, expire, revoke, supersede Actions | Every transition reauthorizes, locks, checks version/state/idempotency and records a safe audit; reviewers recuse from conflicts; concurrent approvals produce one authority result | Red transition matrix, stale/replay, recusal, and two-process concurrency tests | planned | Revoke newly granted authority through audited forward transition |
| PCLM-05 | PCLM-04 | Principal | Scoped place manager authority and Policy integration | Approval grants only requested/approved scopes; ordinary members, wrong organization, former/revoked/expired managers, blocked/inactive actors, and outsiders fail closed | Full role/capability policy matrix and revoked-management tests | planned | Audited revocation or manager removal; never delete attribution |
| PCLM-06 | PCLM-04..05 | Principal | Remove, change-scope, transfer, expiration operation, official-response scope projection | Manager removal, narrowed scope, expiry, revocation, and controlled transfer are atomic and audited; historical responses retain actor attribution and display the verification scope effective when authored | Removal/scope/transfer/expiry/history/official-response tests | planned | Audited compensating scope or transfer transition |
| PCLM-07 | PCLM-03..06 | Principal | Claim and manager Livewire workspaces/forms/views/routes, EN/LT/RU catalogues | Class-based server-rendered workflows expose only authorized minimal state and provide loading, error, empty, offline, focus, keyboard, touch, and translated status behavior | Livewire direct-action/tamper tests, locale parity, responsive browser checks | planned | Remove routes/components while retaining relational evidence |
| PCLM-08 | PCLM-04..07 | Principal | After-commit transition notifications, abuse reports, expiration command/schedule, audit viewer | One notification per committed transition/recipient; rollback sends none; abuse reports are private and rate-limited; expiration is bounded/idempotent; audit presentation is redacted | Notification idempotency/rollback, report privacy/rate, command repeat tests | planned | Disable schedule and delivery; preserve claim/audit state |
| PCLM-09 | PCLM-02..08 | Principal | Seeders and canonical requirements, data, security, authorization, files, testing, deployment, Places progress/master plan, compliance generator/output, changelog | Demo data is environment-safe/repeatable; PLA-P07 has direct implementation and test evidence; no generated evidence is hand-edited | Fresh/repeat seed, generator check, source/diff/secret scans | planned | Revert documentation and demo additions with the code package |
| PCLM-10 | PCLM-01..09 | Independent reviewer then principal | Frozen attributable diff and all affected runtime boundaries | Every reproduced finding is dispositioned; all required gates pass before an isolated coherent commit and push on `main` | Focused/full Pest, Pint, Larastan, migration/seed, browser, npm/build, cache smokes, independent security review, staged diff | planned | Revert coherent commit normally; use forward-fix for migrated production data |

### Test-first execution order

1. Write and observe failing schema/state/private-file tests, then implement
   only the relational foundation needed to pass.
2. Write and observe failing transition, policy, concurrency, expiry,
   notification, and abuse-report tests before their focused Actions.
3. Write and observe failing Livewire, localization, private response, and
   official-scope presentation tests before adding routes and views.
4. Run focused verification, freeze the attributable diff for independent
   review, disposition every finding, then run the complete release gates.

## Active Delivery: Configurable Email Verification

Status: `implementation and focused verification complete; runtime rollout and final gates pending` on 2026-08-30.

This delivery implements the approved
`docs/superpowers/specs/2026-08-30-configurable-email-verification.md`
contract. The deployed environment will use
`EMAIL_VERIFICATION_ENABLED=false`; new registrations will be activated
without a verification email, while `true` preserves the current fail-closed
verification flow. Authentication, active-account checks, policies, scoped
private access, password confirmation, and all non-email verification domains
remain mandatory in both modes.

The task starts on `main` at `38713ac` in a materially dirty shared tree whose
Places implementation and pending migrations are unrelated user-owned work.
Those files remain byte-preserved and outside this delivery's commit. The
approved email-verification slice uses a temporary `GIT_INDEX_FILE` for any
task commit. The current deployed SQLite baseline contains one active account
with a null `email_verified_at`; mutation requires a timestamped backup,
observed pre/post counts, and an integrity check.

| ID | Dependency | Owner | Affected paths | Acceptance criteria | Verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| EVR-01 | Approved configurable-email-verification specification | Principal | `.env.example`, `config/platform.php`, `phpunit.xml`, email-verification mode service | One boolean environment value is read only through configuration; secure enabled mode is the committed and automated-test default; invalid/non-false values fail closed | Focused configuration feature tests and config-cache smoke | implemented; focused tests passed | Set the deployed value to `true` and revert the configuration slice |
| EVR-02 | EVR-01 | Principal | `RequirePortalAccess`, configuration-aware `verified` middleware, `bootstrap/app.php` | Guests and inactive users remain denied in both modes; enabled mode blocks active unverified users; disabled mode permits only otherwise authenticated/active/authorized access through central and explicit verified middleware | Red/green portal-boundary tests, route middleware inspection, auth regressions | implemented; 47 focused tests passed | Restore Laravel's default alias and unconditional central verification check |
| EVR-03 | EVR-01..02 | Principal | `RegisterUser`, registration and verification Livewire components, auth tests | Enabled mode creates a pending account, sends one verification notification, and redirects to notice; disabled mode atomically stamps `email_verified_at`, sends no verification notification, redirects home, and never renders/resends the verification notice | Red/green registration, notification, direct-page, and Livewire-action tests | implemented; 79 focused tests passed | Re-enable verification; already activated accounts intentionally retain their timestamp |
| EVR-04 | EVR-01 | Principal | Bounded activation Action, Artisan command, audit records, auth tests | Dry-run is non-mutating; writes are refused while enabled; disabled execution activates only active pending accounts in locked bounded transactions, writes one non-sensitive audit per account, and is idempotent | Action/command tests covering active, blocked, suspended, verified, repeat, audit, and enabled-mode refusal | implemented; focused tests passed | Re-enable verification; use backup and audit evidence for deliberate account-level reversal only |
| EVR-05 | EVR-01..04 | Principal | `DatabaseSeeder`, canonical requirements/security/authorization/testing/seeding/deployment docs, compliance generator/output, changelog | Disabled-mode demo seeding does not recreate an active pending email account; repeated seed remains stable; all canonical statements describe the conditional assurance boundary and generated output is byte-current | Focused seeder test, generator diff/check, documentation and secret review | implemented; generator and architecture checks pending | Revert documentation and conditional demo timestamp with the code slice |
| EVR-06 | EVR-01..05 | Principal/operator | Deployed `.env`, `database/database.sqlite`, runtime caches | Set the deployed value to `false`; back up the exact SQLite database; inspect and apply pending migrations; run the environment-safe root seeder; dry-run and execute pending-account activation; prove zero active pending accounts and successful SQLite integrity | `scripts/artisan-runtime migrate:status`, `migrate --force`, `db:seed --force`, activation dry-run/write/repeat, count query, `PRAGMA integrity_check` through a supported SQLite client | planned | Restore the timestamped database backup only through deliberate operator recovery; setting `true` affects future registrations |
| EVR-07 | EVR-02..06 | Principal | Complete attributable diff and affected runtime | Targeted tests, Pint, Larastan, complete sequential Pest, isolated fresh/repeat seed, dependency audits, Vite build, config/route/view cache smokes, HTTP auth flow, diff, and secret checks are observed and truthfully recorded before task commit | Exact repository quality gates and post-mutation runtime evidence | planned | Revert the coherent code/docs commit; preserve database activation unless an explicit audited recovery is chosen |

Implementation order is `EVR-01` through `EVR-07`. Every behavior change starts
with an observed failing test. Operational migration and seeding happen only
after a database backup and after the pending migration list is captured; the
three currently pending Places migrations are outside this implementation
slice even though the user-requested runtime migration command may apply them.

## Active Delivery: Shared Place Submission And Publication

Status: `implementation and focused verification complete; independent review
and final repository gates in progress` on 2026-08-30.

The 2026-08-30 resumed execution revalidates the existing attributable slice
with fresh read-only workflow, duplicate-detection, moderation, security, and
testing specialists before the independent frozen-diff review. The principal
continues to own every edit, finding disposition, final gate, and publication
decision.

This delivery implements the complete `PLA-P06` workflow on top of the
preserved place/location authority foundation and the applicable `PLA-P02`,
`PLA-P03`, and `PLA-P05` contracts. The exclusive specialist scopes,
deliverables, and independent-review boundary are recorded in
`docs/audits/places-submission-publication-work-ledger.md`. Specialists remain
read-only; the principal owns every cross-module decision and tracked edit.

The task began on `main` at
`153ae45c2bc6864ec6061dc407d82be68a437c26`, aligned with `origin/main`, in a
materially dirty shared tree. Every pre-existing staged, unstaged, and
untracked path is unrelated user-owned work unless this ledger proves an exact
attributable hunk. Publication will use a temporary `GIT_INDEX_FILE`; no
existing change may be reset, discarded, or included accidentally.

| ID | Dependency | Owner | Affected paths | Acceptance criteria | Verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| PLA-SUB-01 | PLA-P02/P03/P05/P06 and preserved authority baseline | Principal plus read-only workflow, duplicate, moderation, and security specialists | Places plans/audit ledger, existing schema/models/actions/policies/routes/UI | Canonical lifecycle, identities, privacy boundaries, duplicate signals, role capabilities, idempotency, notification timing, and rollback are mapped before production edits | Repository inventory, specialist reports, attributable-path review | complete | Revert planning-only additions |
| PLA-SUB-02 | PLA-SUB-01 | Principal | `tests/Feature/Places/**`, focused unit tests | Red tests cover validated active-member submission, two-account isolation, deterministic duplicate candidates, repeated/concurrent operations, moderation transitions, merge rollback, redirects, authorization, notifications, provenance, and audit history | Focused Pest failures observed before implementation | complete; red/green contracts retained | Revert only the new red contracts |
| PLA-SUB-03 | PLA-SUB-02 | Principal | Additive migration, Places enums/models/factories and existing `Place`/`User` relations | Submission, fact provenance, candidate, event, merge redirect, and notification persistence is indexed, privacy-safe, reversible, factory-backed, and enum-cast | Fresh migration, rollback cycle, schema/factory contracts | complete; 140-file migration cycle and 227-table fresh seed pass | Roll back the additive migration before production writes; forward-fix after writes |
| PLA-SUB-04 | PLA-SUB-03 | Principal | Places data objects, normalizers, duplicate service, submission/moderation/merge Actions, policies | Server-authoritative input creates pending review state; candidates are deterministic suggestions; transitions, merges, restore/reopen, idempotency, abuse controls, after-success notifications, provenance, and audit are transactional and policy-scoped | Action, policy, concurrency, privacy, rollback, and notification tests | complete; isolated two-process race passes | Revert implementation with its schema only before production writes; otherwise forward-fix |
| PLA-SUB-05 | PLA-SUB-04 | Principal | Class-based Places Livewire components/forms/views, routes, EN/LT/RU catalogues | Members can submit and inspect safe status; authorized reviewers can act; loading, validation, pending, duplicate, approved, rejected, empty, and offline states are localized, keyboard-usable, and non-leaking | Livewire direct-action tests, locale parity, view/cache checks, browser journeys | complete; dedicated desktop/mobile Places browser journey passes | Revert route/component/presentation slice while preserving submitted records |
| PLA-SUB-06 | PLA-SUB-03..05 | Principal plus testing specialist | Places factories, deterministic demo seeder, root seed integration, database tests | Every new model has a bounded factory; deterministic pending/duplicate/needs-info/published/rejected/merged scenarios are repeat-safe and environment-gated | Factory/seeder tests, isolated fresh/repeat seed, count and relation checks | complete; twenty submissions and ten merge/restore scenarios seed repeat-safely | Remove demo-only synchronization; never delete user submissions |
| PLA-SUB-07 | PLA-SUB-02..06 | Independent final reviewer and principal | Frozen attributable diff | Independent review reproduces every material finding; valid findings are fixed and affected gates rerun | Review report, disposition ledger, focused reruns | in progress | Revert unsafe finding-specific change |
| PLA-SUB-08 | PLA-SUB-07 | Principal | PLA-P06 evidence, current progress, compliance/data/security/testing/seeding/deployment docs, changelog | Documentation matches observed behavior; focused Places, migration/seed, full Pest, Pint, Larastan, dependency, Vite, cache, browser, diff, and secret gates pass before one attributable commit | Exact final command evidence and temporary-index diff | in progress; final full-suite rerun and publication disposition remain | Revert the coherent task commit normally; never rewrite history |

Implementation order is `PLA-SUB-01` through `PLA-SUB-08`. Tests precede
production behavior. New submissions remain review records until an authorized
publication transition; duplicate scoring never merges by itself. Protected
identifiers and pending facts are scoped before presentation, and audit or
provenance rows are retained through merge and restore operations.

## Active Delivery: Forum Phase 4 Animal-Science Category

Status: `implemented; final verification pending` on 2026-08-30.

The unresolved placeholder in the initiating request is resolved from the
canonical `forum-current-progress` next-pass instruction: this delivery owns
the next dependency-safe Phase 4 source section, category 25, and no wider
phase. The exact 58-ID scope is `forum.category.0237` through
`forum.category.0294`; the two same-section atoms assigned to Phases 5 and 7
remain open. The acceptance, dependency, verification, rollback, and exact-ID
contracts are recorded in
`docs/plans/forum-phase4-animal-science-category-work-package.md`; specialist
coordination is recorded in
`docs/audits/forum-phase4-animal-science-work-ledger.md`.

| ID | Dependency | Owner | Affected paths | Acceptance criteria | Verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| F4-AS-01 | Canonical source and generated catalogue | Principal | Package plan and work ledger | All 58 selected records are listed and reconciled; adjacent Phase 5/7 records are excluded | Exact `jq` inventory and source/generator checks | complete | Revert planning files only |
| F4-AS-02 | F4-AS-01 | Principal | Category manifest/synchronizer/model/factory/seeder paths if required | Existing implementation is retained when correct; every real schema, persistence, or localization defect is test-first repaired | Focused red/green feature contract | complete | Revert finding-specific implementation with its test |
| F4-AS-03 | F4-AS-01..02 | Principal | EN/LT/RU, server-prepared UI, accessibility/responsive paths if required | Category 25 renders the exact localized root/purpose/ordered hierarchy without exposing later-phase completion claims | Focused HTTP and browser checks | complete | Revert presentation change and translations together |
| F4-AS-04 | F4-AS-02..03 | Principal | Requirement evidence and generated progress/traceability documents | Exactly the 58 implemented/tested IDs remain in progress until all required release gates pass; generated artifacts remain deterministic | Source preservation, forum generator, exact overlay delta | pending final promotion; source-history and failed repository gates retained | Revert evidence entry and regenerate |
| F4-AS-05 | F4-AS-04 | Independent reviewer and principal | Frozen attributable diff | Material findings are reproduced, dispositioned, fixed, and retested; full final gates either pass or retain exact blockers | Review report, targeted/full gates, temporary-index diff, `git diff --check` | pending | Revert the coherent package commit normally |

Implementation order is F4-AS-01 through F4-AS-05. Production code cannot
precede a failing behavior contract. The package is committed through a
temporary index so the pre-existing staged database/repository/auth work stays
byte-present and outside this delivery.

## Active Delivery: Complete Database Domain Audit And Implementation

Status: `implementation and independent rereview complete; publication blocked
by unavailable immutable forum-history evidence` on 2026-08-30.

This section is the canonical execution record for the complete migration,
model, relationship, factory, seeder, and database-integrity pass. The
specialist work ledger is
`docs/audits/database-domain-audit-work-ledger.md`. Discovery is read-only;
the principal agent owns every tracked edit and begins test-driven
implementation immediately after this plan is saved.

### Protected baseline and current inventory

- Branch and initial task HEAD: `main` at
  `fdaf7292a152ae61b85e17cf1ce69449d6d4292f`, matching `origin/main`.
- The task began in a dirty shared tree. All pre-existing staged, unstaged, and
  untracked work is preserved. The attributable publication slice will use a
  temporary `GIT_INDEX_FILE`.
- Runtime: PHP 8.5.8, Laravel 13.23.0, Pest 4, SQLite for isolated automated
  verification, and the repository's configured Pint and Larastan gates.
- Audited baseline: 139 migrations create or alter 218 named tables plus
  Laravel's migration ledger at runtime, with 3,478 columns and 514 foreign
  keys. The generated database-domain audit records every index and unique
  constraint. All 204 concrete application models have factories; 44 Seeder
  classes plus the representative manifest and demo guard trait provide root
  orchestration, focused seeders, and bounded representative top-up.
- A safe temporary-SQLite fresh migrate/seed completed and remained stable on
  a second seed, but produced only five users. Across the 203 models, 163 had
  fewer than ten rows and 70 had none. The existing dynamic factory suite
  passed 1,791 tests and 5,313 assertions, proving individual factory
  persistence but not complete representative seeding.
- Confirmed discovery defects before implementation: six `belongsTo`
  declarations infer nonexistent columns; `forum_topic_moves` is the only
  application-owned table without a corresponding model/factory; additional
  schema-backed child and inverse relationship candidates require final
  usage review before addition.
- Final implementation inventory: all 204 concrete application models have a
  valid factory and at least ten rows after a clean root seed; the generated
  audit covers all 3,395 model-contract columns, 514 foreign keys, 941 declared
  relationships, 267 explicit factory helpers, and the complete model/pivot
  seed graph. The deterministic `user@example.com` account is one of exactly
  ten clean-seed users and remains unique after a repeat seed.
- Safety incident: before the isolated runner was hardened, one exploratory
  factory command wrote additive sample rows to the configured shared SQLite
  database. No rows were deleted or overwritten. All subsequent destructive
  or persistence verification used asserted operating-system temporary
  SQLite databases; the pre-existing shared data was left untouched.

### Delivery items

Every item records dependencies, ownership, affected paths, acceptance,
verification, status, and rollback. Discovery specialists do not edit tracked
files. The principal implements and dispositions every finding; DBA-09 is an
independent reviewer of the frozen attributable diff.

| ID | Dependency | Owner | Affected paths | Acceptance criteria | Verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DBA-01 | None | Schema specialist | `database/migrations/**`, schema evidence | Every migration/table/column/key/index/pivot/morph edge is inventoried from a fresh isolated database | Migration inventory and schema introspection artifact | discovery complete | Remove evidence only |
| DBA-02 | DBA-01 | Model specialist | `app/Models/**` | Every concrete model is mapped to its table, key, traits, casts, factory, and declared relations; mismatches are enumerated | Reflection/model-to-schema audit | complete | Remove evidence only |
| DBA-03 | DBA-01..02 | Relationship specialist | `app/Models/**` | Every reliable schema-backed child relation and appropriate inverse/pivot/morph relation exists with explicit relation types and correct keys | Red/green schema relationship contracts and round-trip tests | complete | Revert relationship methods and paired tests |
| DBA-04 | DBA-01..02 | Factory specialist | `database/factories/**` | Every applicable model has a valid realistic factory; required, unique, enum, encrypted, JSON, date, money, and representative nullable values satisfy schema/domain rules | Dynamic factory persistence and field-coverage tests | complete | Revert factory/state changes together |
| DBA-05 | DBA-03..04 | Factory-graph specialist | Factories and relation helpers | Parent reuse avoids circular creation and exponential graphs; pivot and polymorphic metadata are valid | Factory graph audit plus bounded count assertions | complete | Revert graph helpers only |
| DBA-06 | DBA-01..05 | Seeder specialist | `database/seeders/**` | Root seeding is dependency-ordered, environment-safe, reasonably idempotent, creates `user@example.com`, and creates a coherent graph with at least ten rows for every concrete persistent application model without deleting existing data | Isolated fresh seed, repeat seed, counts, pivots, and target-account assertions | complete | Remove additive representative seeder and restore root orchestration |
| DBA-07 | DBA-01..06 | Constraint specialist | Models, factories, seeders, validation, enums | Database and application uniqueness, checks, state/date/money constraints, casts, and tenant/owner boundaries are reflected in generated data | Constraint matrix and focused negative/positive Pest coverage | complete | Revert finding-specific change |
| DBA-08 | DBA-03..07 | QA specialist | `tests/Feature/Database/**`, lifecycle scripts | Tests prove factories, required/representative fields, root seeding, counts, target user, relations, pivots, foreign keys, uniqueness, and repeatability on isolated SQLite | Targeted Pest suites and fresh lifecycle scripts | complete; 1,130 database feature tests / 4,047 assertions and both isolated lifecycle scripts passed | Revert tests/scripts with their implementation slice |
| DBA-09 | DBA-08 | Independent reviewer | Frozen attributable diff | Migration-model-factory-seeder-test chain is adversarially reviewed; every material finding is reproduced, dispositioned, fixed when valid, and retested | Review report and rerun affected gates | complete; three independent adversarial rereviews report release ready with no material attributable finding | Revert unsafe finding-specific repair |
| DBA-10 | DBA-09 | Principal | Documentation, complete task slice | Audit/schema evidence and seeding docs are current; targeted/full PHP gates, Pint, Larastan, Composer checks, npm audit/build, caches, isolated migrate/seed, generator parity, forum preservation checks, diff/secret review pass or an exact external blocker is recorded; coherent attributable commit is safely pushed | Definition-of-done gate list and Git evidence | implementation and evidence complete; publication blocked, so no commit or push | Revert task commit normally; never rewrite history |

### Final observed verification and publication disposition

- Partitioned PHP verification: 2,442 tests and 105,112 assertions; 2,441
  tests passed. The sole failure is the immutable forum-source preservation
  contract because required historical entry `1785397895` is unavailable.
- The database feature partition passed 1,130 tests and 4,047 assertions. The
  complete unit partition passed 32 tests and 218 assertions. The audit
  regeneration subprocess that once received signal 11 passed on an isolated
  retry; the oversized combined PHP 8.5 process remains operationally
  unstable, so final verification is recorded from bounded sequential runs.
- Fresh lifecycle passed with 139 migrations, 219 runtime tables, ten users
  before and after repeat seeding, complete rollback to zero migrations, and
  successful reapplication of all 139 migrations.
- Pint, Larastan, Composer validation/audit/platform checks, official-registry
  npm audit, production Vite build, isolated config/route/view cache smoke,
  all three generated-evidence parity checks, and the 38,377-item forum
  requirements generator passed.
- Publication is blocked by the required forum-source preservation failure.
  Repository policy permits no push until required gates pass; no task commit
  or push was made in the dirty shared tree.

### Implementation and verification order

1. Reconcile DBA-01 through DBA-07 findings into a durable schema and model
   audit, then observe failing Pest contracts for confirmed defects.
2. Correct relationship keys and inverses, add the missing topic-move model and
   factory, and repair only evidence-backed factory/state gaps.
3. Build an environment-gated representative seeder in dependency order. It
   tops up deficits without truncating data, uses real model instances rather
   than hardcoded IDs, connects pivots with their metadata, and makes
   `user@example.com` a deterministic, verified, fully connected account.
4. Prove every concrete model reaches at least ten rows on a clean seed,
   meaningful nullable fields have representative non-null coverage where
   valid, all foreign keys pass, the second seed does not regress counts, and
   key relationship round trips resolve.
5. Run targeted tests first, then fresh lifecycle checks, Pint, Larastan, the
   full Pest suite, Composer validation/audit/platform checks, npm audit/build,
   route/config/view-cache smoke checks, documentation generators, forum
   source/generator checks, independent review, and final attributable diff
   review. Update these statuses only from observed results.

## Active Delivery: Complete Repository Audit And Foundational Repair

Status: `implementation approved from repository evidence` on 2026-08-30.

This section is the canonical execution record for prompt 01. Discovery was
performed read-only by the seven specialist scopes in
`docs/audits/repository-audit-work-ledger.md`; production changes begin only
after this section was saved. Later numbered prompts under `docs/prompts/`
remain the owners of broad modernization and are not silently pulled into this
foundational pass.

### Protected Git And Instruction Baseline

- Initial branch and HEAD: `main` at
  `93a4595b136c3e0a8b7f4671215af91487d5f9e7`, tracking `origin/main` at the
  same commit.
- Initial tree: 89 staged paths and one unstaged path. The unrelated slice
  contains first-party documentation, Playwright capture YAML, screenshot
  deletions, and the unstaged `docs/validation-error-work-ledger.md` update.
  A concurrent workspace operation committed most of that slice as
  `fdaf7292a152ae61b85e17cf1ce69449d6d4292f` and advanced `origin/main` while
  this audit was running. The remaining Playwright YAML deletions remain
  unrelated. Both states are preserved and excluded from the audit commit
  through a temporary `GIT_INDEX_FILE`.
- Applicable instruction chain: root `AGENTS.md` only. No first-party nested
  `AGENTS.md` or `AGENTS.override.md` exists. `CLAUDE.md` is a supporting
  pointer, not an override.
- Authority order: `AGENTS.md`; canonical requirements; security/privacy/data
  integrity; accepted architecture/ADRs; this plan and subordinate domain
  plans; accurate tests/code; supporting evidence; historical plans/specs.

### Complete First-Party Markdown Classification

The initial audit classified all 235 first-party Markdown files. The path
patterns below preserve that initial snapshot. Concurrent first-party work
raised the live total to 241; the generated, per-path authority table in
`docs/audits/repository-inventory.md` is the exhaustive current inventory.
Generated/tooling/vendor trees
under `.agents`, `.claude`, `.cursor`, `vendor`, `node_modules`, and runtime
caches are excluded.

| Paths | Count | Authority |
| --- | ---: | --- |
| `AGENTS.md`, `CHANGELOG.md`, `DESIGN.md`, `PRODUCT.md`, `SECURITY.md` | 5 | Canonical |
| `CLAUDE.md`, `README.md` | 2 | Supporting entry points |
| Canonical cross-cutting documents named by `docs/index.md`, from `docs/accessibility.md` through `docs/topic-lifecycle.md` | 36 | Canonical |
| `docs/{api-integrations-work-ledger,code-review,comprehensive-php-test-suite-work-ledger,current-state-audit,design-system,known-limitations,seeding-work-ledger,ui-component-inventory,ui-migration-matrix,validation-error-work-ledger}.md` | 10 | Supporting/living evidence |
| `docs/events.md` | 1 | Historical; superseded by `docs/events/index.md` |
| `docs/seeding-coverage.md` | 1 | Generated evidence |
| `docs/components/shared-card-primitives.md` | 1 | Canonical component contract |
| `docs/audits/*.md`, excluding `pet-social-network-benchmark.md` | 20 | Supporting dated/living evidence |
| `docs/audits/pet-social-network-benchmark.md` | 1 | Historical research |
| Ten architecture/feature decision files under `docs/decisions/` | 10 | Canonical decisions |
| Eight `*-assumptions.md` and `*-conflicts.md` files under `docs/decisions/` | 8 | Supporting decision evidence |
| `docs/events/index.md` | 1 | Canonical event-system index |
| Every other `docs/events/*.md` | 29 | Supporting event specifications/evidence |
| Eleven named domain master/completion plans under `docs/plans/` | 11 | Canonical within scope; subordinate to this plan |
| Other `docs/plans/*.md`, excluding the two following rows | 58 | Supporting work-package evidence |
| `docs/plans/join-landing-page-plan.md` | 1 | Historical/superseded |
| `docs/plans/forum-phase-requirement-index.md` | 1 | Generated evidence |
| `docs/portal/*.md` | 8 | Supporting portal contracts |
| `docs/requirements/{forum-source-prompt,laravel-engineering-standard}.md` | 2 | Canonical; source prompt is immutable |
| `docs/requirements/{compliance-matrix,forum-master-requirements}.md` | 2 | Generated canonical evidence |
| `docs/superpowers/plans/*.md` | 10 | Historical prototype evidence |
| `docs/superpowers/specs/*.md` | 16 | Historical/subordinate product sources |
| `docs/traceability/forum-requirements-matrix.md` | 1 | Generated living evidence |

There is no competing global plan. Domain plans remain scoped. No `PLANS.md`
or ceremonial replacement is required.

### Repository And Runtime Inventory

| Surface | Current factual inventory |
| --- | --- |
| Routes | 180 runtime routes after `optimize:clear`, including one Boost development route; 179 audited application/framework routes; 167 first-party `App\\` actions; 173 named |
| HTTP/runtime entry points | `routes/web.php`, five console commands, no scheduled tasks, no first-party Jobs/Events/Listeners/Notifications |
| Application layers | 147 controllers; 9 middleware; 67 Form Requests; 226 Actions; 155 Services; 204 models; 47 policies; 1 API Resource; 1 service provider |
| Livewire/Blade | 86 Livewire PHP files: 37 components and 49 form objects; 36 Livewire views; 357 Blade views including 246 anonymous Blade components; no Volt/Flux/Filament |
| Persistence | 139 migrations create 218 named tables; isolated fresh migrate/seed reports 219 including Laravel's migration ledger; 514 declared constrained foreign keys sampled by integrity tests |
| Factory/seed | 204 model factories plus `ApplicationFactory`; 44 Seeder classes plus the representative manifest and demo guard trait; 267 explicit invariant-aware factory helpers |
| Tests | 120 feature files, 3 unit files, 128 PHP files including support/bootstrap, 1,025 Pest declarations, zero Pest browser files, standalone Node browser runners |
| Frontend | 9 resource JavaScript modules, 1 Tailwind CSS entry, 32 CSS/SCSS files, npm lock v3; PhotoSwipe is the only production npm dependency |
| Roles/capabilities | Active/blocked account status plus explicit administrator flag; pet-manager, forum-group, journal-collaborator, knowledge-collaborator, organization, event-team, and event-session role enums; policies are authoritative |
| Integrations/processes | No first-party outbound HTTP client, webhook, worker job, or scheduler; private/local and authenticated portal-file adapters; synchronous queues; operator-run deployment documentation |
| Cache | Public listing/search aggregates with TTL/invalidation plus taxonomy caches and atomic locks for taxonomy, place, event, and state mutations |
| Localization | Laravel language catalogues for `en`, `lt`, and `ru`, 45 files per locale; `LocaleFormatter` depends on Intl |

The module map is: bootstrap/access; identity; pet/taxonomy; encrypted social
compatibility state; normalized social/content; forum/knowledge/community;
organizations; experts/bookings; marketplace/adoption; lost/found; medical;
care; devices; places; and cross-cutting file/audit services. Active
compatibility classes are not dead merely because they retain `Prototype` or
`Preview` names.

### Resolved Stack And Dependency Baseline

| Surface | Declared / locked | Factual audit result |
| --- | --- | --- |
| PHP | `>=8.5 <8.6` / 8.5.8 | Boots; Intl, PDO SQLite, GD, Imagick present locally; direct extension requirements incomplete |
| Laravel | `^13.0` / 13.23.0 | Boots; 13.29.0 available but deferred to prompt 03 |
| Livewire | `^4.3.4` / 4.3.4 | Compatible; 4.4.2 deferred to prompt 03 |
| Tailwind | `^4.3.3` / 4.3.3 | Current stable line |
| Vite/plugin | 8.2.0 / 3.1.3 | Nano ID advisory requires targeted Vite patch; broader updates deferred |
| Pest/PHPUnit | 4.7.5 / 12.5.30 | Correct major line; canonical Artisan test process lacks required memory configuration |
| Larastan/PHPStan | 3.10.0 / 2.2.7 | Baseline passed at level 5 with 1 GiB |
| Lock state | Composer lock + npm lock only | CommonMark has six advisories; Nano ID has one high advisory using official npm registry |

### Critical Workflow Traces

| Workflow | Validation and authorization | Persistence / side effects | Current test evidence or gap |
| --- | --- | --- | --- |
| Registration | Livewire form; server-generated actor key; framework auth/session regeneration | User insert then framework `Registered` event | Auth and portal-boundary tests |
| Pet creation | Livewire form, duplicate review, policy and idempotency | Transaction: profile, manager, privacy, alias, lifecycle, audit; optional protected photo | Pet foundation/create/duplicate tests |
| Social mutation | `PerformActionRequest`, portal/auth/active middleware, Action authorization | Locked/versioned encrypted `UserDomainState` | Social persistence tests; decomposition deferred |
| Medical/care temporary access | Owner policy, authenticated active bearer, expiring hashed token, optional account binding, section/file permission and row lock | Downstream download/write succeeds inside the grant transaction before view/audit mutation; actual bearer is audited | Bound mismatch, unbound different-bearer, denied side-effect, download and shared-entry tests |
| Device command | Password confirmation, throttle, request, explicit `controlCommand` policy | Locked/idempotent command, state/event/audit transaction | Smart-device and real Gate tests; no global administrator bypass |
| Marketplace acceptance | Decimal-normalizing request, listing policy, scoped reservation, row locks | Reservation/listing transition, checked minor-unit total, immutable Order and audit | Precision, exact rental/deposit, maximum-width, rollback and call-site tests |
| Forum topic publication | Request plus runtime schema, policy, media normalization | Transactional topic/taxon/lifecycle creation and compensating media cleanup | Topic lifecycle/schema tests |

### Reproducible Initial Baseline

| Command | Observed result before repair |
| --- | --- |
| `composer validate --strict` | Pass |
| `composer audit --locked` | Fail: six `league/commonmark` 2.8.3 advisories |
| Official-registry `npm audit --package-lock-only --audit-level=high` | Fail: high Nano ID 3.3.16 advisory |
| `php artisan about` / uncached route discovery | Pass; 180 total / 169 non-vendor routes |
| `php artisan test --compact` | Fatal at 128 MiB in taxonomy/factory loading |
| `php -d memory_limit=1G artisan test ...` | Still fatal: Artisan child Pest process remains at 128 MiB |
| Direct Pest with `php -d memory_limit=1G` on factory/seeder suite | Pass: 1,791 tests / 5,313 assertions |
| `ForumAccessibilityTest` | Fail: 5 pass / 1 false-negative DOMXPath assertion / 49 assertions |
| `vendor/bin/pint --test` | Pass |
| `PAO_DISABLE=1 vendor/bin/phpstan analyse --memory-limit=1G --no-progress` | Pass, zero errors |
| `npm run build` | Pass; Vite 8.2.0, 670 ms |
| `php scripts/verify-fresh-database.php` | Pass: 139 migrations, 219 tables, repeat seed stable at five users |
| Forum requirement generator | Fatal at 128 MiB; with 1 GiB reports generated JSON stale |
| Forum source preservation | External failure: source entry `1785397895` is unavailable |
| Seeding coverage generator | Committed evidence stale: 203 models / 246 helpers / 1,521 enum states now |

### Accepted Findings And Execution Items

Every item names its dependency, owner, files, acceptance criterion, test,
verification command, status, and rollback. `Principal` is the sole editor.

| ID | Dependency | Owner | Files/modules | Acceptance criteria | Required test / verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AUD-01 | None | Principal | Git/index and work ledger | Initial user slice remains byte-present and task commit contains only attributable work | Final status, staged/unstaged diffs, temporary-index staged diff | complete | Restore saved index tree, never working files |
| AUD-02 | AUD-01 | Seven read-only auditors | Entire first-party tree | All requested analysis reports reconciled; high-impact claims directly validated | Inventory commands and exact file/symbol sampling | complete | Documentation-only reversal |
| AUD-03 | AUD-02 | Principal | This plan and current-state audit | Stable implementation/deferred IDs exist before runtime edits | Diff proves plan precedes production changes | complete | Revert documentation section only |
| AUD-04 | AUD-03 | Principal | `AppServiceProvider`, `ChangeForumTopicState`, authorization tests | No blanket admin Gate bypass; private care/medical/device/order/search resources deny unrelated admins while explicit policy admin abilities remain | Red/green real-Gate authorization tests; policy/security slices | review-corrected; targeted verified | Restore hook only if equivalent explicit policy controls are added first |
| AUD-05 | AUD-04 | Principal | Care/medical/device grant resolvers, downloads and shared writes | Bound grants require the authenticated recipient; unbound grants log the actual authenticated bearer; mismatch/denial consumes nothing; authorization and shared care writes remain in one transaction | Bound and different-bearer tests, denied side-effect tests, transactional architecture check | review-corrected; targeted verified | Revert binding as one coherent slice; token hashing/expiry unchanged |
| AUD-06 | AUD-03 | Principal | `CreateOrder`, marketplace request validation, marketplace seeder, exact amount value object | No float money calculation; canonical decimals and schema-width totals remain exact; demo seeder fails closed outside allowed environments | Unit amount tests plus precision, quantity/rental/deposit/boundary/rollback/seeder regressions | review-corrected; targeted verified | Revert call sites and value object together; no schema change |
| AUD-07 | AUD-03 | Principal | Composer/npm manifests and locks | Targeted CommonMark and Nano ID advisories removed; required direct extensions and Node/package-manager floors declared; no broad upgrade | Composer validate/audit/platform; official npm audit; build | targeted verified; concurrent broad Composer slice excluded | Revert manifest/lock slice atomically |
| AUD-08 | AUD-03 | Principal | `phpunit.xml`, safe test/browser runners, forum generator/test and generated outputs | Canonical tests clear cached config and have 1 GiB; stateful browser gates own disposable SQLite/loopback runtime and direct runners fail closed; forum output is deterministic independently of external history | Cached-config runner smoke, browser refusal/isolation tests, generator `--check`, architecture tests | review-corrected; targeted verified | Revert config/runners/generated outputs atomically |
| AUD-09 | AUD-08 | Principal | Compliance/seeding generators and evidence | Point 13 supplemental rows survive generation; real cache use is not marked N/A; seeding evidence is byte-current | Generator byte-parity architecture tests | targeted verified | Regenerate from reverted generator |
| AUD-10 | AUD-03 | Principal | Forum accessibility test, expert booking/editor Blade and controller | Caption test asserts actual behavior; repeated inputs have labels; Blade receives prepared request data | Targeted feature/architecture/render tests and build | targeted verified | Revert each independent UI/test slice |
| AUD-11 | AUD-04..10 | Principal | `AGENTS.md`, generated inventory, audit/architecture/security/testing/seeding/deployment/index/review/changelog docs | Current claims are dated, per-path/symbol inventories are byte-current, canonical links agree, commands reproduce, no stale pass claim remains | Documentation generators, link/source scans, diff review | in progress | Revert task documentation only |
| AUD-12 | AUD-11 | Three independent reviewers | Frozen attributable diff | Audit, security/integrity, and test reviewers disposition every finding; valid findings fixed | Review ledger plus rerun targeted/full gates | review complete; corrections targeted verified | Revert finding-specific change if correction is unsafe |
| AUD-13 | AUD-12 | Principal | Complete repository | Applicable final gates pass or only exact external blockers remain; coherent main commit and safe push | Full verification list and Git evidence | pending | Revert task commit normally; never rewrite history |

### Independent Review Dispositions

- `AUD-RV-01` exact-inventory, direct SQLite-extension, device-binding,
  money-wiring, lock-provenance, and workflow-staleness findings were accepted.
  The deterministic inventory generator and per-path Markdown authority table,
  manifest architecture checks, behavioral regressions, targeted lock audit,
  and corrected workflow chains resolve them.
- `AUD-RV-02` premature grant consumption, marketplace demo-seeder safety,
  decimal precision/schema width, device binding coverage, and legacy JSON
  money compatibility findings were accepted and regression-tested. Child
  disclosure redesign remains assigned to prompts 09/10 because this pass did
  not alter that product boundary. Grant `actor_role` remains the scoped role
  named by the grant while `actor_key` is always the authenticated bearer; it
  is audit metadata, not identity authorization.
- `AUD-RV-03` care-write TOCTOU, unsafe browser mutation, cached-config test
  execution, missing real-Gate/unbound-bearer tests, masked forum generation,
  money branch coverage, generator drift, formatting, and stale-documentation
  findings were accepted. The transaction wrappers, disposable runners,
  split deterministic/external tests, expanded regressions, and synchronized
  evidence resolve them subject to `AUD-13` final gates.
- Codex `/review` is not available in this environment; the three required
  behavior-focused independent reviewers supplied the review boundary.

### Explicit Deferred Modernization Ownership

| Finding | Owning later prompt | Reason it is not an audit-time repair |
| --- | --- | --- |
| Broad stable dependency upgrades | 03 | Current prompt permits only advisory/platform blockers |
| Large Actions/services, prototype-state split, orphan candidates | 06 | Requires characterization and domain-by-domain migration |
| Missing schema relationships, upload/DB atomicity, forum pagination and vote bounds | 07, 20, 21 | Requires additive schema/query/file design and volume tests |
| Shared-browser forum/message/care offline draft isolation | 12, then 23 review | Requires versioned browser-storage migration and two-account browser coverage |
| Nested child non-disclosure and form service location | 09 and 10 | Requires consistent binding/error-contract changes |
| Large Livewire components, hydration/key gaps, direct Livewire persistence | 11 | Cross-component state and action extraction require dedicated tests |
| Tailwind/SCSS bundle modernization | 13 | Existing build is valid; visual migration requires measured proof |
| Remaining accessibility/browser-route coverage | 14 | Requires isolated connected browser matrix |
| Device-to-medical literals and native linguistic review | 15 | Localization-specific ownership and native review |
| Whole-schema factory/seed idempotency expansion | 16 and 17 | Needs explicit append-only table policy |
| Authenticated-by-default test foundation and CI | 18 and 23 | Broad test bootstrap/automation change |
| External providers/webhooks/payments | 19 and 22 | Provider selection/credentials and runtime process topology are absent |

Suspected plaintext moderation/mentorship fields and device timezone provenance
remain unclassified until the owning requirement is confirmed; no destructive
migration or speculative rewrite is authorized here.

## Active Revalidation: Prompt 01 Repository Audit And Foundational Repairs

Status: `plan saved; implementation in progress` on 2026-08-30.

This `AUD2` section records the current prompt-01 revalidation. It supplements,
rather than rewrites, the completed `AUD-*` history above. Production edits for
this revalidation begin only after this section is saved.

### Protected State And Governing Evidence

- The audit started on `main` at `fdaf7292a152ae61b85e17cf1ce69449d6d4292f`,
  aligned with `origin/main`, with a materially dirty shared tree containing a
  large staged audit/auth/forum/database/documentation slice and 18 additional
  unstaged paths. While discovery was read-only, concurrent repository work
  committed and pushed `f605d58` and `153ae45`; those commits are external to
  this revalidation and must not be claimed, reverted, or restaged here.
- The applicable repository instruction chain is the root `AGENTS.md`; no
  nested first-party `AGENTS.md` or `AGENTS.override.md` exists. `docs/index.md`
  is the documentation source-of-truth index. The existing
  `docs/implementation-plan.md` remains the one canonical global plan.
- The documentation auditor classified 350 repository Markdown files: 244
  non-tooling first-party documents and 106 repository skill/instruction mirror
  documents. Canonical, supporting, generated, historical, and tooling-mirror
  status must be emitted per path by `docs/audits/repository-inventory.md`.
  Repository-local skill examples that conflict with `AGENTS.md` are
  non-authoritative; the root contract wins.

### Current Inventory And Baseline

| Surface | Revalidated inventory / evidence |
| --- | --- |
| Runtime routes | 180 routes from `route:list --json`; 174 with `--except-vendor`; 167 `App\\` actions; all 179 audited non-Boost routes named, including unstable generated names found in the inventory generator |
| Application modules | 147 controllers, 9 middleware, 67 Form Requests, 226 Actions, 155 Services, 204 models, 47 policies, 1 API Resource, 1 provider, no first-party jobs/webhooks/outbound clients |
| Livewire / presentation | 36 renderable class components plus 49 form objects, 36 Livewire views, 357 Blade views, 246 anonymous Blade components, 9 JavaScript modules, 1 Tailwind entry and 31 SCSS files; no Volt, Flux, Filament, impure Blade, or duplicate Alpine |
| Persistence | 139 migrations, 218 named schema tables plus Laravel's migration ledger at runtime, 204 model factories plus `ApplicationFactory`, and 44 seeder files |
| Tests | 129 `*Test.php` files: 126 Feature and 3 Unit; 1,051 static Pest declarations, 6 datasets, no detected skip/todo markers, and five standalone browser commands |
| Localization / cache / process | 45 language files for each of `en`, `lt`, and `ru`; 10 sampled cache/lock consumers; database-backed queue and operator-managed deployment, with no scheduler or first-party queued jobs |
| Stack | PHP 8.5.8; Laravel 13.29.0; Livewire 4.4.2; Tailwind and `@tailwindcss/vite` 4.3.3; Vite 8.2.2; Laravel Vite plugin 3.2.0; Pest 4.7.8; PHPUnit 12.5.33; Larastan 3.10 / PHPStan 2.2.9; Node 26.4.0 / npm 12.0.1 |

Critical workflow traces remain the registration, pet creation, social-state,
medical/care/device temporary access, device command, marketplace acceptance,
and forum-publication chains recorded above. This revalidation additionally
traced forum category administration through
`AdminDashboard::saveCategory()`: validated Livewire state and component
authorization currently lead to two independent writes and cache invalidation,
without one Action-owned transaction. The accepted immediate repair makes that
chain authorize again inside a focused Action, lock and update the category and
translation atomically, then invalidate cache only after success.

Observed baseline: PHP/application boot, Composer strict validation/audit and
platform checks passed; `composer outdated --direct --strict` returned `1` only
for out-of-scope next-major alternatives; official-registry npm audit passed
with zero vulnerabilities while the configured mirror's audit endpoint returned
404; production Vite build passed; route/about commands passed; generated
repository and seeding evidence were stale; forum generation passed when rerun
serially; immutable forum-source preservation remains blocked by missing source
entry `1785397895`. An attempted parallel invocation of database-backed test
wrappers produced signal 11, so every authoritative PHP suite remains serial as
required by `AGENTS.md`.

### Accepted Findings, Repairs, And Deferred Ownership

| ID | Dependency | Owner | Affected paths/modules | Acceptance criteria | Required tests / verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AUD2-01 | None | Principal | Git state and audit ledger | Shared staged/unstaged work and concurrent commits remain intact and attributable | Status, complete diffs, final task-only staged diff | complete | Never reset; unstage only task-owned paths if required |
| AUD2-02 | AUD2-01 | Seven read-only specialist auditors | Entire first-party tree | Required structured reports are reconciled; material findings are independently sampled | Framework inventories, exact symbol/schema/document checks | complete | Documentation-only correction |
| AUD2-03 | AUD2-02 | Principal | This plan | Stable dependencies, owners, acceptance, tests, status, and rollback exist before production edits | Git diff and ledger chronology | complete | Revert this additive section only |
| AUD2-04 | AUD2-03 | Principal | `scripts/generate-repository-inventory.php`, generated inventory, architecture tests | Two consecutive generations are byte-identical; generated route names cannot inject randomness; every first-party Markdown path has correct authority, including skill mirrors | Red/green deterministic-generator test, generator byte-parity test | implemented; focused test passed 1 test / 7 assertions | Revert generator/test and regenerate previous evidence |
| AUD2-05 | AUD2-03 | Principal | Forum category Action, `AdminDashboard`, focused forum tests | Category and translation update in one authorized locked transaction; cache invalidation occurs only after success; component delegates one operation | Red/green Action authorization/success/rollback-oriented tests and Livewire administration test | implemented; focused slice passed 16 / 86 | Revert Action/delegation together; no schema change |
| AUD2-06 | AUD2-03 | Principal | `AdoptionDemoSeeder`, `CollaborativeGuideDemoSeeder`, factory/seeder tests | Direct production invocation fails before any mutation; allowed environments retain deterministic behavior | Red/green production-denial tests and affected seeder tests | implemented; guard test passed 2 / 2 | Revert guard/tests together |
| AUD2-07 | AUD2-03 | Principal | Local `public/storage`, `storage`, `bootstrap/cache`, media/config regression | Prohibited public-storage symlink is absent; private-link config remains empty; runtime paths are owned by `www:www` | Red/green private-media test, exact `readlink`/`find` ownership checks | link removed and media test passed 10 / 25; final ownership check pending | Recreate link only if a future approved public-media ADR replaces the private boundary; restore documented owner if changed |
| AUD2-08 | AUD2-04..07 | Principal | Audit, architecture, seeding, review, limitations, compliance, changelog, plan | Counts, authority, commands, findings, limitations, and statuses describe only observed current state; generated evidence is byte-current | All documentation generators/checks and link/secret/diff review | pending | Revert task documentation; regenerate generated files |
| AUD2-09 | AUD2-08 | Principal | Complete repository | Targeted tests, serial full Pest, Pint, Larastan, isolated fresh migration/seed, official npm audit/build, cache and browser smoke checks pass or expose exact external blockers | Canonical commands recorded with observed counts/exits | pending | Revert only the failing attributable repair |
| AUD2-10 | AUD2-09 | Three new independent reviewers | Frozen task diff and adjacent boundaries | Audit-correctness, security/integrity, and regression reviewers disposition every behavior finding; valid findings are fixed and rerun | Review ledger plus post-fix targeted/full checks | pending | Revert finding-specific correction if unsafe |
| AUD2-11 | AUD2-10 | Principal | Task-owned Git slice | Coherent commit is created on `main`; push occurs only if origin remains safe and credentials work | Staged diff, `diff --check`, commit hash, push output, final status | pending | Normal revert commit only; never rewrite history |

The following evidence-backed findings are deferred to their existing owners:
browser-storage account isolation and JavaScript teardown to prompt 12;
Livewire monolith/key/offline-state work to prompt 11; nested-resource
non-disclosure and temporary-access revocation races to prompts 09 and 10;
care file/database atomicity and parent-qualified task integrity to prompts 07,
20, and 21; Composer extension and Node-floor normalization to prompt 03;
behavioral route coverage, opt-in test authentication, global outbound-request
prevention, and CI/coverage to prompts 18 and 23. These items require broader
contract or schema design and are not hidden as audit-time fixes.

## Active Delivery: Blade And Browser Lifecycle Modernization

Status: `discovery in progress` on 2026-08-30.

This delivery is governed by `SYS-FRONTEND-001`, `SYS-FRONTEND-002`,
`SYS-LIVEWIRE-001`, `SEC-WEB-002`, `TEST-ARCH-001`, `TEST-SECURITY-001`, and
the applicable accessibility, localization, responsive, and quality
requirements. Discovery is read-only; the principal agent owns integration,
production changes, final review dispositions, verification, publication,
and rollback decisions.

### Work Ledger

| ID | Subagent | Exclusive discovery scope | Expected output | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| BLM-A1 | Blade Purity and Data-Flow Analyst | All first-party Blade data access, PHP, business calculations, permission/SEO logic, relation access, literals, and raw rendering | Violation inventory, traced callers, correct owners, architecture-test candidates, risks, and commands | Repository contract and canonical frontend/security documents | pending |
| BLM-A2 | Blade Component and Presentation Contract Analyst | Repeated cards, forms, tables, actions, status, modal, empty/error, navigation, and layout markup | Consolidation map, explicit props/slots/defaults/states, usage/test updates, risks, and commands | BLM-A1 evidence is informative but not blocking | pending |
| BLM-A3 | Flux Compatibility and Accessibility Analyst | Installed package/license evidence and every Flux-like or custom form/modal/menu/table/notification use | Capability matrix, invalid/deprecated list, retain/replace decision, accessibility tests, risks, and commands | Installed Composer/NPM metadata | pending |
| BLM-A4 | Alpine Integration Analyst | JavaScript entry points, packages, Livewire bootstrap, Alpine plugins/globals, `x-` directives, CSP/data boundaries | Ownership map, duplicate/conflict findings, migration/lifecycle tests, risks, and commands | Installed Livewire/Alpine metadata | pending |
| BLM-A5 | JavaScript Navigation Lifecycle Analyst | First- and third-party widgets, listeners, timers, observers, media, maps, editors, Vite loading, navigation/account transitions | Lifecycle registry, init/destroy risks, wrapper/browser requirements, risks, and commands | BLM-A4 runtime map is informative but not blocking | pending |
| BLM-A6 | Raw HTML and XSS Boundary Analyst | Blade raw echo, Markdown/rich text, email, preview, JSON-LD, SVG, URLs, script data, and third-party widgets | Origin-to-sink map, sanitizer controls, adversarial tests, risks, and commands | Canonical security and frontend rules | pending |
| BLM-A7 | Frontend Architecture Test Analyst | Existing Pest architecture suite, Blade tree, package metadata, static checks, fixtures, and false-positive exclusions | Maintainable check specification, fixture strategy, exceptions, risks, and commands | Findings from BLM-A1/A3/A4/A6 are informative but not blocking | pending |
| BLM-R1 | Blade Architecture Reviewer | Final changed views and their PHP preparation boundaries | Independent severity-ranked findings and exact failure scenarios | Implementation freeze | pending |
| BLM-R2 | Flux and Accessibility Reviewer | Final Flux/custom components, forms, focus, keyboard, themes, translations, and tests | Independent compatibility/accessibility findings and verified usage list | Implementation freeze | pending |
| BLM-R3 | JavaScript Lifecycle and XSS Reviewer | Final modules, Alpine, raw output, URLs, widgets, teardown, and browser tests | Independent lifecycle/XSS attack findings and reproductions | Implementation freeze | pending |

The discovery reports will be reconciled into implementation items in this
section before production code changes begin. Analysts and reviewers are
read-only unless a later ledger revision delegates one narrowly isolated fix.

## Active Work Ledger: Tailwind CSS 4 And Design System

Status: `discovery complete; implementation authorized after this checkpoint`
on 2026-08-30. This ledger is the coordination boundary for the
repository-wide Tailwind CSS-first migration. All discovery and review agents
are read-only; the principal agent owns reconciliation, implementation, tests,
documentation, Git integration, and publication.

| ID | Agent | Exclusive scope | Expected output | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| TW13-WL-A1 | Tailwind Upgrade and Configuration Analyst | Package metadata, NPM lock, Vite, Tailwind/PostCSS config, CSS entries, plugins, presets, scripts, Node compatibility | Configuration migration map, dependency changes, visual risk and rollback notes | Repository contract and canonical frontend/Tailwind docs | complete; report accepted and dispositioned |
| TW13-WL-A2 | Tailwind Source Detection and Dynamic Class Analyst | Blade, PHP class maps, Livewire, JavaScript, CSS sources, vendor templates, safelists | Source registry, unsafe dynamic-class findings, explicit-map and build-test needs | Repository contract and canonical Tailwind rules | complete; report accepted and dispositioned |
| TW13-WL-A3 | Design Token and Theme Architect | Brand/theme values, colors, typography, spacing, breakpoints, containers, radii, shadows, z-index, motion, component variants | Token inventory, target `@theme` model, repeated-value migration and contrast tests | Product and design documents | complete; report accepted and dispositioned |
| TW13-WL-A4 | Responsive Layout Analyst | Public/auth layouts, navigation, sidebars, cards, grids, tables, forms, filters, dialogs, drawers, media, charts, pagination | Screen/component matrix, prioritized defects, container-query and layout recommendations | Frontend, accessibility, localization, and active feature contracts | complete; report accepted and dispositioned |
| TW13-WL-A5 | Tailwind Accessibility Styling Analyst | Forms, controls, links, badges, alerts, dialogs, menus, tables, loading/disabled/error states, themes | Accessibility-style findings, utility/token changes, verification scenarios | WCAG and repository accessibility contracts | complete; report accepted and dispositioned |
| TW13-WL-A6 | Modern Tailwind Feature Applicability Analyst | Installed Tailwind 4 capabilities and reusable component/layout opportunities | Feature matrix with approved locations and rejected candidates/reasons | Exact installed version and browser contract | complete; report accepted and dispositioned |
| TW13-WL-A7 | CSS Duplication and Component Abstraction Analyst | CSS/SCSS, Blade class lists, components, `@apply`, arbitrary values, specificity and dead CSS | Duplication plan, dead-CSS candidates, component/token/utility decisions | Source-detection and token findings | complete; report accepted and dispositioned |
| TW13-WL-A8 | Frontend Build and Visual Verification Analyst | Build scripts/output, manifests, asset sizes, browser/visual tooling and critical pages | Build baseline, critical visual checklist, regression-test and screenshot plan | Existing dependencies and browser runners | complete; report accepted and dispositioned |
| TW13-WL-R1 | Tailwind Architecture Reviewer | Final package/config/CSS/source/token diff and production output | Severity-ranked findings and release-readiness verdict | Implementation freeze and final diff | pending |
| TW13-WL-R2 | Responsive UI Reviewer | Final critical screens across widths, locales, zoom, touch and keyboard | Reproducible responsive findings and required fixes | Built assets and isolated browser fixture | pending |
| TW13-WL-R3 | Accessibility Styling Reviewer | Final focus, contrast, status, motion, forced-colors, touch and theme states | Reproducible accessibility findings and verified state checklist | Built assets and isolated browser fixture | pending |
| TW13-WL-R4 | Build Output Reviewer | Final lock/config/manifest/assets and critical generated selectors | Build findings, size comparison and release recommendation | Clean production build | pending |

### TW13 protected baseline and configuration migration map

The task began on `main` at
`9540fe83756833ae1c6d22053e883a07dca9f014`, three commits ahead of
`origin/main`, with 478 pre-existing changed paths across staged, unstaged and
untracked states. Those bytes remain user-owned. Package, lock, npm registry
and font-provider files also changed concurrently during discovery; their
final state must be frozen and attributed before release. The baseline
production build succeeded with Tailwind CSS 53,426 bytes, retained SCSS
311,032 bytes, application JavaScript 35,099 bytes and PhotoSwipe JavaScript
58,832 bytes. Those provisional numbers are not a final comparison because a
concurrent Fontsource change altered the build while analysts were reading it.

| Surface | Observed state and exact resolved version | CSS-first target / retention decision | Verification |
| --- | --- | --- | --- |
| Tailwind | `tailwindcss@4.3.3`; `@tailwindcss/vite@4.3.3` | Retain current stable pair and Vite plugin; no prerelease or PostCSS fallback | `npm ls`; isolated `npm ci`; production build |
| Vite | `vite@8.2.2`; `laravel-vite-plugin@3.2.0` | Retain current stable compatible integration and the three intentional entries | Manifest reachability and entry-count contract |
| Node/npm | Node 26.4.0 and npm 12.0.1 in the execution environment; active dependency plan requires Node `>=22.12 <27` and npm 12 | Adopt the stricter active repository contract and synchronize documentation; do not reintroduce Node 20 while `concurrently@10` requires Node 22 | engines, package-manager and clean-install checks |
| CSS entry | `@import 'tailwindcss' source(none)` plus six explicit sources | Keep CSS-first import; narrow pagination sources to exact Tailwind templates; keep Blade, JS, Livewire PHP and class-component PHP sources | Source-registry test and emitted-selector probe |
| SCSS entry | Deliberate, later-loaded component layer; no Less and zero `@apply` | Retain as a separate Vite entry; bridge shared primitives to CSS theme variables; remove only proven dead selectors | Sass build, mixed-component browser styles and size comparison |
| JavaScript Tailwind config | No `tailwind.config.*`, preset, safelist or legacy directive exists | No compatibility config is needed; do not create one | Architecture scan |
| PostCSS | No `postcss.config.*`, Tailwind PostCSS, import plugin or first-party Autoprefixer wiring exists | Nothing to migrate or delete | Manifest and dependency scan |
| Source plugin paths | Broad Laravel and Livewire pagination globs | Replace with Laravel `tailwind.blade.php` and Livewire `tailwind.blade.php` plus `simple-tailwind.blade.php`; no vendor-wide source | Exact source-registry assertion |
| Custom variant | Unused custom `forced-colors` duplicates Tailwind 4 built-in support | Remove after RED architecture coverage; use the built-in variant and a base forced-color fallback | Architecture and generated CSS checks |
| Font | Instrument Sans brand family; concurrent Fontsource output contains Latin only | Preserve the family and add `latin-ext` for Lithuanian; accept only locally locked glyph-complete output | Manifest unicode ranges, LT browser sample and font requests |

Every previous theme value is retained or deliberately corrected: brand
colors remain unchanged; the existing `xs` breakpoint, radii, shadows, easing
and z-index values move under correct Tailwind namespaces; the invalid
`--duration-interface` name becomes `--transition-duration-interface`.
There is no legacy container, animation or plugin value to migrate. The
default Tailwind namespaces remain enabled because active views still use
default colors, spacing, radii and shadows.

### TW13 accepted discovery and design model

Production CSS currently omits active `paw-canvas`, `paw-surface`,
`paw-accent`, `paw-border`, `paw-teal`, `status-information`, `border-subtle`,
`border-strong` and `text-muted` utility families. This is a token-ownership
defect, not a general source-scanner failure: representative Blade, PHP,
Laravel pagination and Livewire pagination utilities are present. Runtime
utility concatenation was not confirmed; the architecture ratchet must still
cover JavaScript templates, PHP interpolation/concatenation and Blade
fragments so the guarantee cannot regress.

The target token layers are:

- primitive brand colors: cream, paper, ink, muted, line, leaf, mint, coral
  and sun, with the current literal values preserved;
- semantic aliases: canvas, surface, subtle/strong border, control border,
  muted text, focus, success, warning foreground/background, danger and info;
- component decisions: control/panel radii, touch target, panel/control/dialog
  shadows, content/reading containers, eyebrow tracking, z-index and interface
  duration/easing. Component-only map geometry and one-off table widths remain
  local.

Warning body text receives a dedicated foreground because the existing warning
token has only 3.71:1 contrast on white. Interactive borders receive a darker
control-border token because the deliberate light separator is not visible
enough as a control boundary. Repeated SCSS primitives reference the emitted
CSS variables. Exact brand literals in the forum layer may be replaced
mechanically only in bounded, separately verified passes; wholesale SCSS or
form-component conversion is rejected for this delivery.

Accepted responsive and accessibility corrections are: render all 13 mobile
destinations instead of slicing two routes away; allow translated desktop
navigation to wrap without horizontal overflow; allow four-column hero stats
and service-card copy to wrap at 320 pixels; restore visible messaging-search
focus; preserve focus in forced colors when `outline-none` utilities would
otherwise win; enlarge search-map marker controls to 44 pixels and use a
contrasting focus color; replace color-only/low-contrast warning and notice
text; use native list/button semantics for taxonomy results instead of an
incomplete listbox; and layer `dvh` after `vh` in messaging and place-map
surfaces.

Approved Tailwind 4 features are CSS-first theme/source ownership, logical
properties, existing `:has()` state, built-in motion/forced-color/pointer
variants where a real state uses them, and layered dynamic viewport units.
Container queries are deferred because no nested-container defect was
reproduced. Dark mode remains intentionally not applicable. Masks, text
shadows, view transitions, custom feature-showcase utilities and a mass
ARIA/data-variant rewrite are rejected because they add compatibility or
private-DOM lifecycle risk without a requirement.

### TW13 ordered implementation and verification ledger

| ID | Dependency | Owner | Affected paths/modules | Acceptance criteria | Required tests / verification command | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| TW13-01 | Eight analyst reports and protected Git baseline | Principal | This plan and TW13 work ledger | Exact configuration, accepted/rejected findings, ownership and rollback recorded before production edits | Ledger/report reconciliation and attributable diff | completed | Revert only TW13 planning additions |
| TW13-02 | TW13-01 | Principal | New Tailwind architecture and build-output tests/scripts | RED proves missing semantic selectors, unsafe construction coverage, exact source paths, modern variant ownership, intentional entries and LT font coverage | Focused Pest test; production selector script against baseline | pending | Revert new tests/scripts |
| TW13-03 | TW13-02 | Principal | `resources/css/app.css`, `_tokens.scss`, affected Blade/Livewire class maps | Required primitive/semantic/component variables compile; every active semantic utility is emitted; obsolete aliases and duplicate custom variant are gone; brand is unchanged | Focused architecture test; `npm run build`; selector script; contrast assertions | pending | Revert theme/source/alias group and restore previous build |
| TW13-04 | TW13-03 | Principal | Navigation, stats, service-card, messaging, map, taxonomy, forum warning/notice and viewport SCSS/views | All routes remain reachable; no 320/375/768/1024/1280/1440/1920 overflow; long EN/LT/RU copy wraps; touch, keyboard, forced colors and reduced motion stay usable | RED/green feature tests; Sass build; retained browser reports and screenshots | pending | Revert per component; no database rollback |
| TW13-05 | TW13-03 | Principal | Repeated arbitrary utilities, forum primitives and confirmed dead selectors | Only proven duplication is removed; local layout values remain local; zero new `@apply`; source output unchanged except intended selectors | Repetition/dead-consumer scan, focused component tests, size comparison | pending | Restore individual selector/class migration |
| TW13-06 | TW13-02 | Principal | Browser CDP client, browser wrapper and viewport/report contracts | CDP close/error rejects pending work with causal stderr; true 1280 viewport runs; failure evidence may be retained without retaining sessions, credentials or temporary database | Node/Pest harness tests and isolated browser run | pending | Revert harness-only group |
| TW13-07 | TW13-03..06 | Principal | Frozen dependency tree, production assets and critical routes | Isolated `npm ci`, audit and clean build pass; selector/manifest/font contracts pass; final raw/gzip delta is recorded from comparable builds | npm/version/audit/build commands; browser matrix; console/resource inspection | pending | Stop release and restore last verified lock/assets |
| TW13-08 | TW13-07 | Four independent read-only reviewers; principal dispositions | Frozen attributable diff and built runtime | Architecture, responsive, accessibility and build reviewers report exact evidence; every valid finding is fixed and rerun | Reviewer reports, finding dispositions and focused/full reruns | pending | Revert only an unsafe reviewer-induced fix |
| TW13-09 | TW13-08 | Principal | Tailwind/design-system/frontend/accessibility/localization/testing/deployment docs, compliance generator/output and changelog | Documentation describes observed implementation and commands; generated compliance evidence is byte-identical | Documentation scan, forum generator checks if applicable, diff/secret review | pending | Revert documentation slice and regenerate prior evidence |
| TW13-10 | TW13-09 | Principal | Temporary Git index on `main` | Only attributable files/hunks are staged; staged diff/check are clean; commit and push occur only after applicable gates | Temporary-index review, commit hash and observed `git push origin main` result | pending | Do not publish on a material failure; use a reviewed ordinary revert after publication |

The required browser matrix is 320, 375, 768 portrait, 1024 landscape, true
1280, 1440 and 1920 CSS pixels; EN/LT/RU and long content; 200 percent reflow;
keyboard and touch; hover-disabled input; reduced motion; forced colors; page
overflow; console and asset status. The clean-build selector contract samples
critical semantic utilities plus `max-w-[90%]`, the PHP-produced
`md:grid-cols-[minmax(0,1fr)_18rem]`, Laravel `ring-gray-300` and Livewire
`ring-blue-300`. Browser artifacts may be retained only in a sanitized
explicit output path; temporary databases, cookies and credentials must still
be destroyed.

## Active Work Ledger: Complete Localization And Hardcoded Text Removal

Status: `PLANS.md and protected work ledger recorded; discovery wave 1 assigned` on
2026-08-30. The `LC15-*` identifiers are
the exclusive coordination boundary for this repository-wide localization
delivery. Analysts and reviewers work read-only; the principal agent owns the
canonical plan, implementation, tests, documentation, Git integration, and
publication. Independent scopes run in waves because the shared agent pool has
four total slots.

| ID | Agent | Exclusive scope | Expected output | Dependencies | Status |
| --- | --- | --- | --- | --- | --- |
| LC15-A1 | Locale Architecture and Routing Analyst | Locale configuration, middleware, routes, sessions/cookies, user preferences, language and JSON files, database-translated content, and HTTP/Livewire/mail/notification/API/job locale flow | Locale architecture map; canonical locales/fallback; routing, persistence, invalid-locale, tenant/content-locale and RTL findings; tests and commands | Repository contract and canonical architecture, security, frontend, Livewire, and localization documents | complete; report accepted |
| LC15-A2 | Hardcoded String Scanner | PHP, Blade, Livewire, JavaScript, validation, exceptions, notifications, mail, API output, accessibility, SEO, fixtures, and tests | Classified literal inventory; stable-key migration map; intentional nonlocalized exceptions; implementation order and scanner tests | Existing localizer scripts and translation conventions | complete; report accepted |
| LC15-A3 | Translation Consistency Agent | All EN/LT/RU catalogues, JSON translations, validation, mail/notification templates, pluralization, placeholders, nesting, escaping, terminology, and dead keys | Locale parity report; mismatch inventory; consolidation and human-review recommendations; representative plural tests | Canonical locale tree and current translation references | complete; report accepted |
| LC15-A4 | Validation and Notification Translation Agent | Form Requests, Livewire validation, exceptions, notifications, mailables, deferred side effects, user-facing JSON and provider failure mapping | Communication localization matrix; recipient-locale and serialization defects; required fixes and tests | Locale architecture evidence and Laravel communication boundaries | complete; report accepted |
| LC15-A5 | Formatting and Pluralization Agent | Dates, times, relative time, timezone, numbers, percentages, currency, lists, measurements, counts, coordinates, exports, reports, JavaScript and Blade formatting | Formatting ownership policy; direct-format/fragment inventory and locale/timezone/currency/plural edge tests | Installed Intl/framework capabilities and existing formatter service | complete; report accepted |
| LC15-A6 | Localized Content and SEO Analyst | Public pages, presenters/SEO builders, locale routes, slugs, database translations, canonical/alternate metadata, Open Graph, JSON-LD, authored-content boundaries and tests | Localized public-content matrix; fallback/indexing/escaping defects; applicable and not-applicable SEO decisions with required tests | Locale architecture and current public-route/indexability evidence | complete; report accepted |
| LC15-A7 | Localization Test and Automation Analyst | Pest/architecture/browser suites, scanners, factories, seeders, critical routes and communications, fallback/switch/persistence, long/Unicode/RTL fixtures and deterministic timezones | Test/automation plan; coverage gaps; exact commands; scanner false-positive controls; deterministic fixture and long-text/RTL decisions | Findings from LC15-A1 through LC15-A6 and current test/tooling inventory | assigned wave 3 |
| LC15-R1 | Translation Coverage Reviewer | Final changed source, locale catalogues, notifications, mail, API errors, accessibility/SEO strings, tests and scanners | Severity-ranked literal/key/placeholder/escaping findings with exact locations and failure scenarios | Frozen attributable diff and completed implementation | pending |
| LC15-R2 | Locale Behavior and Formatting Reviewer | Final locale selection/persistence/fallback, timezone/number/currency/plural behavior, recipient locale and deferred work | Severity-ranked behavior findings and locale-architecture readiness verdict | Frozen attributable diff and completed targeted checks | pending |
| LC15-R3 | Localization Regression and UX Reviewer | Critical pages/components/forms/errors across EN/LT/RU, long/Unicode content, responsive layouts and accessibility labels | Severity-ranked mixed-language, clipping, terminology and journey findings with exact pages/locales | Built assets, deterministic fixtures and connected browser environment | pending |

## Current Delivery: General Pet Size Category

Status: `implemented and release-verified` on 2026-08-04.

- Store one nullable controlled category on the canonical pet profile without
  inferring a default from species, breed, image, weight, or legacy text.
- Reuse one server normalizer through the existing authorized Appearance
  Action while preserving optimistic locking, idempotency, audit, cache
  invalidation, no-op behavior, and omitted-input compatibility.
- Render an accessible EN/LT/RU editor and query-free public projection that
  explicitly distinguishes the category from measurements and medical facts.
- Add the profile-side `(size_category, status, id)` index without pretending
  a marketplace, place, event, service, carrier, product, or search consumer
  has been delivered.
- Keep exact measurements and public, household, or clinical weight privacy
  outside this nine-requirement package.

Exact scope and observed evidence belong in
`docs/plans/pet-profile-size-category-work-package.md`.

## Superseded Interface Continuation: Owner Profile

Status: superseded by AIR-001 on 2026-08-31. The person-specific owner route,
presenter, translations, and profile page described below were prototype
evidence and are no longer a production contract.

- Keep `/@mia-carter` as a deliberate authenticated profile hero while moving
  its complete first-party and system surface into one 131-leaf EN/LT/RU
  contract.
- Keep tab and audience state locale-independent, prepare all routes, actions,
  privacy values, copy, and icon names in the presenter, and leave Blade as a
  passive renderer.
- Add one canonical Lucide icon language to the hero, tabs, audience preview,
  overview sections, badges, and safety controls.
- Skip pet and moment queries for tabs that do not render those collections;
  the `about/friend` projection now issues one state query and no pet-profile
  query instead of seven total queries.
- Retain 44-pixel targets, keyboard focus, reduced motion, forced-colors
  usability, EN/LT/RU parity, and zero horizontal overflow from 320 to 1920
  pixels.
- Preserve this browser ratchet while continuing through the remaining
  deliberate detail/workspace profiles; exact-tree Pint, Larastan, 3,055-test
  Pest, dependency, migration, seed, cache, source-preservation, route, icon,
  and diff gates passed before publication.

## Previous Delivery: Structured Pet Identifying Marks

Status: `implemented and release-verified` on 2026-08-04.

- Store up to twelve ordered identifying marks as normalized child rows with
  stable keys, controlled types, encrypted descriptions, actor attribution,
  visibility, and reversible retirement.
- Reuse one server normalizer and one synchronizer through the existing
  authorized Appearance Action while preserving optimistic locking,
  idempotency, audit, cache invalidation, and omission compatibility.
- Offer only public and private-verification visibility until friend, clinic,
  and active-search consumers have authoritative access checks.
- Render an accessible EN/LT/RU manager editor and eager-load only active
  public rows for the public profile, with a second presenter-side filter.
- Preserve the legacy encrypted free-text value as private compatibility data.

Exact scope and observed evidence belong in
`docs/plans/pet-profile-identifying-marks-work-package.md`.

## Previous Delivery: Species-Aware Pet Body Covering

Status: `implemented and release-verified` on 2026-08-04.

- Select coat, feather, scale, skin, mane, and shedding controls from the
  existing broad species without trusting browser-submitted applicability.
- Reuse one server normalizer across progressive and durable compatibility
  mutation paths while retaining authorization, optimistic locking,
  idempotency, audit, and cache invalidation.
- Store the schema-versioned object in the existing encrypted profile payload,
  reuse structured scale-color clarification, and add no migration or query.
- Render EN/LT/RU workspace and public projections while keeping the bounded
  skin observation manager-only.
- Keep search, recommendations, care, groomer/shelter/finder consumption,
  private marks, measurements, identity media, and medical facts outside this
  11-requirement package.

Exact scope and observed evidence belong in
`docs/plans/pet-profile-body-covering-work-package.md`.

## Previous Delivery: Pet Appearance Color

Status: `implemented, release-verified, and published` on 2026-08-04.

- Store one controlled primary color, up to four unique additional colors,
  three controlled patterns, and bounded species-neutral clarification in the
  existing encrypted profile payload.
- Reuse one server normalizer across progressive and durable compatibility
  mutation paths while retaining authorization, optimistic locking,
  idempotency, audit, and cache invalidation.
- Render EN/LT/RU workspace and public projections through one presenter with
  no new query, migration, or Blade business logic.
- Preserve legacy free text and keep identifying marks out of the public
  projection.
- Keep automatic lost/found descriptions, coat, marks, measurements, identity
  media, and cross-domain consumption outside this 12-requirement package.

Exact scope and observed evidence belong in
`docs/plans/pet-profile-appearance-color-work-package.md`.

## Previous Delivery: Pet Life Stage

Status: `implemented, release-verified, and published` on 2026-08-04.

- Derive newborn, juvenile, young, adult, senior, or unknown at read time from
  the existing honest age range and controlled animal-group thresholds.
- Keep separate dog, cat, bird, rabbit, rodent, fish, reptile, and horse
  boundaries; never use dog thresholds as a global fallback.
- Store only an authorized manual clarification with actor and time, and label
  it separately from an automatic result or medical verification.
- Reuse one server normalizer in progressive and durable compatibility update
  paths, retaining authorization, optimistic locking, idempotency, audit, and
  cache invalidation.
- Render EN/LT/RU workspace and public projections with no added query or
  private provenance disclosure.

Exact scope and final observed evidence belong in
`docs/plans/pet-profile-life-stage-work-package.md`.

## Previous Delivery: Pet Breed Origin And Provenance

Status: `implemented, verified, and published` on 2026-08-04.

- Preserve the legacy breed string as a bounded compatibility snapshot while
  storing an explicit one, mixed, possible-multiple, no-breed, or unknown
  overall state and up to four normalized origins.
- Keep confidence, information source, and optional mixed percentage separate
  from the breed value; a photograph cannot upgrade a reported or suspected
  origin to confirmed.
- Reuse one server normalizer and one owned-relation synchronizer across
  creation, generic update, progressive update, autosave, and manual save.
- Render localized trust/provenance controls and an honest public projection
  without queries or business logic in Blade.
- Keep taxonomy ingestion/verification, protected evidence documents,
  breed-based discovery, behavior, health, ownership, and lost/found effects
  outside this 35-requirement package.

Exact scope and observed evidence are recorded in
`docs/plans/pet-profile-breed-origin-work-package.md`.

## Current Delivery: Pet Name Identity

Status: `implemented, verified, and published` on 2026-08-03.

- Keep `pet_profiles.name` as the current canonical name and preserve all stable
  identity and adjacent-domain links during rename.
- Store typed nickname, previous, shelter, official, localized, and responds-to
  alternatives with normalized uniqueness, attribution, locale, and explicit
  visibility.
- Preserve the old current name automatically after a successful rename.
- Search only viewer-visible alternatives inside the existing policy-scoped pet
  workspace and expose only public alternatives on the public profile.
- Keep cross-domain name propagation, global/public alias discovery, merge,
  ownership, and taxonomy verification outside this package.

Exact scope and observed evidence are recorded in
`docs/plans/pet-profile-name-identity-work-package.md`.

## Previous Delivery: Honest Species Confidence

Status: `implemented, verified, and published` on 2026-08-03.

- Preserve the controlled broad species used by search and integrations.
- Store confirmed, possible, or unidentified confidence separately.
- Allow possible identification only for cat and dog and normalize every
  browser-controlled combination again in the Action.
- Render possible species honestly in every selected public, workspace,
  invitation, and duplicate projection.
- Keep adoption, found-animal coordination, ownership, and taxonomy
  verification outside this package.

Exact scope and observed evidence are recorded in
`docs/plans/pet-profile-species-confidence-work-package.md`.

## Current Delivery: Pet Duplicate Review And Access Requests

Status: `implemented, verified, and published` on 2026-08-03.

- Pause canonical pet creation on bounded, policy-visible name/species matches
  and expose only the safe identity/photo projection.
- Bind the explicit different-animal decision to an encrypted expiring review
  token that the creation Action verifies again.
- Store typed access requests with encrypted evidence, unique active/replay
  keys, reviewer attribution, and optimistic state.
- Grant no immediate capability; ordinary approval creates the existing
  invitation and requester acceptance activates it.
- Keep ownership transfer outside generic approval and leave organization
  attribution open until its relationship is authoritative.

Exact scope, observed gates, and remaining non-goals are recorded in
`docs/plans/pet-profile-duplicate-access-work-package.md`.

## Pass 1: Protect And Baseline

Status: `verified`

- Inspected branch, remote, tracked/untracked state, locks, runtime,
  dependencies, routes, schema, source, tests, and first-party Markdown.
- Preserved the pre-existing untracked `.agents/vendor/` tree.
- Captured Composer/NPM audits, full Pest baseline, Vite assets, routes,
  migrations, seed repeatability, and the absence of a coverage driver.
- Recorded the accidental local SQLite rebuild without concealing its impact.

Evidence: `docs/current-state-audit.md`.

## Pass 2: Canonical Documentation And Requirements

Status: `verified`

- Established `docs/index.md` and canonical product, system, non-functional,
  architecture, domain, data, security, authorization, frontend, Livewire,
  Tailwind, accessibility, localization, testing, seeding, performance,
  caching, integration, deployment, operations, audit, review, and limitation
  documents.
- Preserved historical feature specifications and plans while making their
  prototype-only authority explicit.
- Normalized 165 stable active requirement identifiers.
- Generated one traceability row per requirement and one factory row per model.

Verification:

```bash
php scripts/generate-compliance-matrix.php
php scripts/generate-seeding-coverage.php
php artisan test --compact tests/Feature/ArchitectureComplianceTest.php
```

## Pass 3: Runtime And Dependencies

Status: `verified`

- Constrained PHP to `>=8.5.0 <8.6.0` and Laravel to `^13.0`.
- Added Livewire 4.3 class-based components and Larastan 3.10/PHPStan 2.2.
- Updated Vite to 8.2 and retained Tailwind/Vite integration 4.3.3.
- Preserved Pest 4 as the intentional primary test style.
- Kept the single NPM lock and added no speculative infrastructure package.

Verification: Composer validation/audit/why-not/outdated, NPM audit/outdated,
application boot, Larastan, Pest, and production build.

## Pass 4: Identity, Authentication, And Authorization

Status: `verified` for the persisted application boundary

- Added immutable unique actor keys, account locale/timezone/status, and an
  explicit administrator capability through an additive migration.
- Added class-based Livewire login, registration, password reset, password
  confirmation, and account forms with separate Blade templates.
- Bound ownership to the authenticated actor rather than a fixed prototype
  identity.
- Protected mutations and private medical, care, device, order, booking, and
  coordination routes.
- Added active-account middleware, rate limits, signed verification, session
  regeneration/invalidation, policy checks, and environment-gated demo users.
- Added Laravel's fresh-password step-up contract to precise device pages and
  remote device commands; failed or missing confirmation produces no command.

## Pass 5: Localization And Passive Presentation

Status: `verified`

- Added validated `en`, `lt`, and `ru` locale handling with `en` fallback.
- Extracted static Blade, PHP, JavaScript, validation, and notification text
  to stable keys; the PHP localizer reports zero remaining eligible literals.
- Replaced dynamic count/status sentences with placeholder/plural-aware
  `presentation.php` keys.
- Added architecture checks for static and interpolated Blade literals,
  locale-key parity, placeholder parity, localized auth pages, and validation.
- Compiled every Blade template after migration.

Lithuanian and Russian catalogues are structurally complete and pass key and
placeholder parity. Native linguistic review is an external editorial step,
not an implementation gap.

## Pass 6: Data, Queries, Factories, And Seeders

Status: `verified`

- Added the identity migration and a reversible migration providing a leading
  index for every previously uncovered foreign key.
- Enabled strict Eloquent behavior outside production and repaired model
  projections exposed by it.
- Added valid factories for all 66 first-party Eloquent models through a typed
  application factory base and explicit model factories.
- Added 23 explicit helper states and verified 412 enum-backed states.
- Made `DatabaseSeeder` repeatable and production-gated.
- Added an opt-in, deterministic, production-blocked performance seeder.
- Added safe fresh-database verification using an asserted system temporary
  SQLite path.
- Added bounded query checks for care journals and smart devices.

Verification:

```bash
php artisan test --compact tests/Feature/Database
php scripts/verify-fresh-database.php
```

## Pass 7: Livewire, Tailwind, Accessibility, And Browser Behavior

Status: `verified`

- Kept Livewire class-based and multi-file; Volt remains prohibited and absent.
- Added minimal typed/form-object state, validation, precise loading targets,
  dirty/offline feedback, and server-authoritative auth actions.
- Added explicit Tailwind source detection and design tokens while preserving
  the mature SCSS component layer.
- Added visible focus, reduced-motion and forced-colors behavior.
- Added forum-wide focusable validation summaries with field-linked errors,
  semantic data-table captions, accessible media description/transcript/WebVTT
  contracts, text map alternatives, 44-pixel controls, contrast assertions,
  and a dependency-free Chrome desktop/mobile/reflow smoke runner.
- Corrected 320 px booking overflow and verified representative viewport
  widths, one logical `h1`, auth focus behavior, and a clean browser console.
- Completed the server-rendered place contract with validated deterministic
  pagination, preserved filter URLs, source/freshness/history coverage, and a
  guarded emergency clinic mode with direct call and route actions.
- Replaced publication-photo new-tab enlargement with one progressively
  enhanced PhotoSwipe viewer. Stable server-resolved photo keys now scope
  shared policy-authorized reactions and escaped comments to each individual
  photo through indexed relational records and a one-member/one-photo unique
  constraint. The shared gallery component provides localized zoom,
  keyboard/touch navigation, URL deep links, focus restoration, and responsive
  bottom/side social panels.
- Added durable `PetProfile` records and encrypted, versioned
  `UserDomainState` persistence so social mutations survive sessions without
  exposing browser-controlled authority.
- Added a durable IndexedDB care queue with source time, idempotency, conflict
  metadata, and duplicate-safe synchronization.

## Pass 8: Security And Quality

Status: `verified` for implemented boundaries

- Repaired fixed-actor authorization, guest access to private domains, session
  fixation/logout behavior, environment-gated demo identities, private media
  authorization, idempotent mutations, sensitive serialization, and baseline
  browser headers.
- Added architecture guards for Blade, Volt, environment reads, database
  shortcuts, route shape, model factories/fillable fields, localization, and
  debug leakage.
- Adopted Laravel 13's first-party image component through one
  `StorePublicImage` Action for every public photo-upload seam, with bounded
  input dimensions, EXIF orientation, WebP output, generated names, localized
  failures, and focused HTTP/action regressions.
- Removed meaningless framework example tests.
- Formatted all PHP and resolved Larastan level 5 findings without a baseline.

Provider-specific SSRF, webhook, media transport, and redacted integration-log
tests remain not applicable until those entry points exist.

## Pass 9: Final Verification

Status: `verified`

Completed:

- full serial Pest: 696 passed, 31,698 assertions;
- full parallel Pest: 696 passed, 31,698 assertions;
- Larastan: zero errors;
- production Vite build;
- Blade compilation;
- Composer/NPM security audits;
- isolated fresh migration and repeat seed;
- config, route, and view cache build plus application boot;
- syntax, dependency, translation, and generated-matrix checks;
- connected browser desktop/mobile/auth/private-flow review;
- expected coverage failure caused only by the missing PCOV/Xdebug driver.

Final staged-diff verification is part of Pass 10 because it must inspect the
exact temporary Git index used for publication.

## Pass 10: Publication

Status: `verified`

The canonical Markdown has been synchronized, evidence matrices regenerated,
and generated browser artifacts removed. Publication used a temporary Git
index that excluded the pre-existing `.agents/vendor/` tree. The exact staged
diff passed `git diff --cached --check`, the coherent modernization commit was
created on `main`, and the observed push advanced `origin/main`.

No blocked or not-applicable requirement may be described as implemented in
the release report.

## Current Delivery: Forum Topic-Type Schema Runtime

Status: `verified` on 2026-08-03

- Extract one typed catalogue for system field schemas and capability rules.
- Resolve active definitions through one bounded, versioned cache with model
  and synchronization invalidation.
- Enforce location, species, attachment, answer-rating, accepted-answer, and
  notification constraints at HTTP and direct Action boundaries.
- Persist the resolved definition ID and schema version on generic topic
  create/update while preserving existing structured data.
- Promote only the exact 20 scoped Phase 3 IDs after the complete gate passes;
  leave the 13 migration/final-audit IDs open.

The complete gate passed 2,360 tests and 78,407 assertions, full Pint and
Larastan, dependency audits, Vite/cache compilation, isolated migration and
repeat seed, immutable source preservation, and deterministic generation.

The executable contract and stop conditions are maintained in
`docs/plans/forum-topic-type-schema-runtime-work-package.md`.

## Current Delivery: Complete Phase 3 Migration Verification

Status: `implemented and verified` on 2026-08-03

- Audit every first-party migration for typed `up()`/`down()` methods and raw
  SQL escape hatches.
- Apply all migration filenames to an asserted disposable SQLite database,
  compare the ledger, roll every migration back, and reapply the exact set.
- Run the complete production-safe seed twice after reapplication and require
  stable identities.
- Reuse schema-integrity, constraints, enums/casts, factories, populated
  compatibility, and package rollback tests instead of adding a speculative
  migration.
- Passed the focused 2-test/11-assertion contract, the related
  1,611-test/4,795-assertion persistence slice, and the complete sequential
  2,362-test/78,760-assertion suite.
- Passed full Pint/Larastan, dependency audits, production build, cache
  compilation, fresh migration/repeat seed, complete rollback/reapply, source
  preservation, and deterministic requirement generation.

Exact scope, stop conditions, and final evidence are in
`docs/plans/forum-phase3-migration-verification-work-package.md`.

## Current Delivery: Phase 4 Before-Ownership Category

Status: `implemented and verified` on 2026-08-03

- Validate the complete source-derived category manifest before runtime use:
  checksum, schema version, exact totals, root sequence, required fields,
  stable-key/slug formats, hierarchy prefixes, and global uniqueness.
- Prove exact source-to-manifest-to-database metadata and ordering for category
  21 while leaving its two Phase 5 taxonomy labels unpromoted.
- Move database readiness checks into the locale-tree cache miss, reducing a
  warm localized read from 2 database queries to 0.
- Passed the focused 13-test/38-assertion contract, related
  72-test/5,949-assertion slice, and complete sequential
  2,384-test/78,891-assertion suite plus every applicable release gate.

Exact scope, exclusions, and evidence are in
`docs/plans/forum-phase4-before-ownership-category-work-package.md`.

## Current Delivery: Phase 4 Special-Needs Category

Status: `implemented and verified` on 2026-08-03

- Prove the complete category-22 source-to-manifest-to-database hierarchy,
  including 54 exact ordered children and all system-category locale rows.
- Require reviewed target/fallback translations in the category tree and root
  selector so unfinished translations cannot replace trusted source text.
- Preserve the existing locale cache/invalidation contract with zero added
  queries and zero warm-tree database statements.
- Passed the focused 4-test/17-assertion contract, related
  46-test/36,051-assertion slice, and complete sequential
  2,396-test/79,143-assertion suite plus every applicable release gate.

Exact scope, exclusions, and evidence are in
`docs/plans/forum-phase4-special-needs-category-work-package.md`.

## Current Delivery: Phase 4 Wildlife-Coexistence Category

Status: `implemented and verified` on 2026-08-03

- Prove the complete category-23 source-to-manifest-to-database hierarchy,
  including its exact root metadata and all 55 ordered source labels.
- Prove exact synchronized root/child stable keys, slugs, positions, and
  reviewed EN/LT/RU root rows without changing production behavior.
- Retain the wildlife-crime and roadkill reporting labels in the immutable
  source list while keeping their `forum.moderation.0010/.0011` requirements
  open in Phase 7.
- Passed the focused 2-test/12-assertion contract, related
  31-test/36,368-assertion slice, and complete sequential
  2,484-test/80,398-assertion suite plus every applicable release gate.

Exact scope, exclusions, and evidence are in
`docs/plans/forum-phase4-wildlife-coexistence-category-work-package.md`.

## Current Delivery: Phase 4 One-Health Category

Status: `implemented and verified` on 2026-08-03

- Prove the complete category-24 source-to-manifest-to-database hierarchy,
  including its exact root metadata and all 42 ordered source labels.
- Add the missing localized boundary explaining that One-Health discussions do
  not replace a physician, veterinarian, public-health authority, or emergency
  service; render it only for the selected root or child category.
- Version the localized category-tree cache payload without adding a database
  statement, route, schema, model, Policy, or parallel taxonomy.
- Passed the focused 3-test/21-assertion contract, related
  47-test/36,632-assertion slice, complete sequential
  2,586-test/81,835-assertion suite, desktop/mobile Chrome audit, and every
  applicable release gate.

Exact scope and evidence are in
`docs/plans/forum-phase4-one-health-category-work-package.md`.

## Current Delivery: Unified Forum Topic Editor

Status: `implemented and verified` on 2026-08-03

- Replaced the detached right sidebar on `/forum/ask` with one coherent editor
  shell whose complete five-item publishing guidance precedes the form.
- Reorganized the unchanged authoring controls into three labelled context,
  response, and optional-media sections without changing authorization,
  validation, persistence, taxonomy interaction, or query count.
- Added reviewed EN/LT/RU presentation copy and responsive desktop/mobile
  styling with semantic headings, visible control states, and 44-pixel mobile
  targets.
- Passed the focused 2-test/33-assertion contract, complete isolated
  2,588-test/82,043-assertion suite, full Pint/Larastan, dependency audits,
  production Vite build, compiled views, and desktop/mobile Chrome audit.

Exact scope and evidence are in
`docs/plans/forum-topic-editor-redesign-work-package.md`.

## Current Delivery: Progressive Pet Profile Completion

Status: `implemented and verified` on 2026-08-03 for
`pet.creation.0036-pet.creation.0058` only.

- Preserved `/compose/pet` as a compatibility redirect to the canonical
  minimal private-draft creation screen and moved the subsequent workflow into
  twelve ordered URL-backed steps.
- Added one central responsive navigator, only one active body, independent
  step saves, mutation-free skipping, purpose explanations, and text-based
  saved/optional state without a disclosure score.
- Added allowlisted optimistic partial updates, active-step relationship
  loading, one reusable current-manager policy projection, and bounded
  navigation existence queries.
- Stored microchip readiness and an optional identifier as one private,
  encrypted, versioned fact guarded by `change-microchip`; unauthorized roles
  receive no value, completion signal, form field, or mutation control.
- Verified EN/LT/RU parity, responsive desktop/mobile/320px rendering,
  keyboard/focus/console contracts, 130-migration fresh/rollback/reapply and
  repeat seed, dependency/static/build/cache gates, and the final serial suite
  of 2,657 tests and 84,589 assertions.

Exact scope and open follow-up requirements are in
`docs/plans/pet-profile-progressive-completion-work-package.md`.

## Current Delivery: Pet Profile Draft Autosave

Status: `implemented and verified` on 2026-08-03 for
`pet.creation.0071-pet.creation.0081` with dedicated atomic evidence.

- Added change/blur-driven saves to the seven ordinary descriptive steps while
  retaining the manual submission path.
- Rejected unknown and inactive step parameters before mutation and retained
  the existing form validation, managed-profile policy, allowlist, row lock,
  optimistic version, audit, lifecycle evidence, and cache invalidation.
- Added a locked idempotency key that rotates only after a successful response,
  plus one reusable accessible save-status component and explicit temporary
  photo unsaved state.
- Added page-memory reconnect recovery: a numeric form revision marks pending
  input, `online` retries the same ordinary Livewire action once, and only a
  matching server confirmation clears that revision. No profile value is
  stored in browser persistence.
- The focused progressive suite passes 27 tests and 159 assertions, including
  all-step wiring, persistence after a fresh mount, validation-key stability,
  six bounded client-revision acknowledgement cases, no-op replay, and
  mismatched-step non-mutation.
- The integrated current tree passed full Pint, zero-error Larastan, the
  isolated 2,692-test/85,091-assertion reconnect suite, the final complete
  2,695-test/85,875-assertion current-tree suite, production Vite build, cache
  smoke,
  dependency audits, and authenticated EN/RU/LT browser verification including
  a real failed network request, one automatic reconnect retry, reload, and
  value-restoration cycle.

Exact scope, evidence, and non-goals are in
`docs/plans/pet-profile-draft-autosave-work-package.md`.

## Current Delivery: Unified Icon System

Status: `implemented and verified` on 2026-08-03.

- Audited every first-party Blade template, direct/dynamic Lucide call, legacy
  size class, inline SVG, raw pictogram, foreign icon system, and native
  interactive candidate.
- Added `x-ui-icon` as the single size, stroke, fill, color, and ARIA primitive
  plus a downward-only executable audit.
- Migrated all 698 direct calls across 146 files, reduced dynamic debt from 83
  to zero, migrated 41 legacy SCSS selectors, removed all 310 legacy class
  attributes, and removed the last raw pictogram.
- Added prepared icons to all thirteen desktop primary-navigation destinations
  while retaining visible labels and current-page semantics.
- Added icons to 45 unambiguous actions; the remaining 52 candidates were
  reviewed and recorded as intentional text/content controls. Static debt is
  zero.
- Passed 2,639 Pest tests / 83,214 assertions, full Pint, Larastan over 1,385
  files, dependency audits, production build, cache compilation, fresh and
  repeat seeding, and a 33-screenshot EN/LT/RU browser matrix from 320 through
  1920 pixels without overflow or console errors.

The factual baseline is `docs/audits/icon-system-deep-audit.md`; the unlimited
execution ledger is `docs/plans/icon-system-unlimited-plan.md`.

## Current Delivery: Event Schedule Foundation

Status: `implemented and verified` on 2026-08-03

- Added occurrence-scoped tracks, rooms, sessions, and staff assignments with
  reversible schema, indexes, encrypted private data, enum state, factories,
  and repeat-safe demo records.
- Added one row-locked, idempotent schedule Action with policy checks,
  occurrence/timezone/capacity validation, room/track/staff overlap detection,
  and owner-level audited override.
- Added one responsive shared schedule component and a class-based Livewire
  create/edit surface in the existing event workspace. Public viewers do not
  receive drafts or private staff assignments.
- Added direct Action, policy, schema, encryption, conflict, Livewire,
  localization, factory, and seeder tests in
  `EventScheduleWorkflowTest` and `EventLifecycleFoundationTest`.
- Passed 2,362 sequential PHP tests with 78,760 assertions, full
  Pint/Larastan, isolated fresh migration plus repeat seed, production build
  and audits, and six event desktop/mobile browser audits with zero console
  errors.

Session attendee reservations/waitlists, schedule-change notifications,
venue entities beyond event-scoped rooms, and keyboard reordering remain open
and are not claimed by this delivery.

## Current Planning: Portal And Events Completion

Status: `in progress` on 2026-08-20; P02 organization authority and P03
place/location/venue authority have verified foundations, while every parent
package and the remaining portal/event scope stay open

The live audit found 3,449 `portal.*` requirements still without Point 12
evidence and 4,883 of 4,968 `event.*` requirements still planned/discovered.
The current code is not empty: it has one authenticated shell, 162 first-party
routes, the canonical event aggregate, lifecycle/occurrence foundations,
multi-pet registration, manual attendance, and occurrence-scoped schedules.
The gap is therefore a combination of missing advanced domains and existing
portal behavior that has not yet been reconciled against atomic requirements.

The factual audit is
`docs/audits/portal-events-completion-gap-analysis.md`. The unbounded,
dependency-ordered implementation contract is
`docs/plans/portal-events-completion-master-plan.md`. Its 36 packages begin
with evidence reconciliation, organization and location authority, then add
portal contexts/search/calendar/dashboard infrastructure before completing
event builder, eligibility, registration, capacity, payment, check-in, safety,
specialized event domains, UI migration, seeding, verification, and release.

## Current Delivery: P02 Organization Authority Foundation

Status: `implemented, verified, and published foundation` on 2026-08-03

The first P02 slice adds the canonical organization tenant, nine independent
membership roles, account-bound expiring invitations, operational
restrictions, suspension, append-only audit, guarded demo seeds, localized
class-based Livewire workspaces, and responsible-organization event authority.
Wrong/former tenant access fails in queries and Policies, ordinary members do
not receive email or restriction reasons, and invitation tokens remain hashed
in records and absent from public Livewire state.

This does not close P02. Organization locations, selected context switching,
verification review/renewal, notification delivery and revocation, downstream
finance/marketplace/shelter operations, authoritative backfill, and exact
portal/event evidence remain open. Scope and current gate evidence are in
`docs/plans/portal-organization-authority-foundation-work-package.md`.

## Current Delivery: Global Page Identity Standardization

Status: `in progress`; directory, workflow, event, and forum presentation waves
verified on 2026-08-03

The complete route classification, canonical `x-page-header` contract,
thirteen priority regression routes, forum category/subcategory information
architecture, meetup upgrade gate, global migration waves, and final quality
gates are recorded in
`docs/plans/global-page-identity-standardization-plan.md`.

The first slice extended `x-page-header` with a stable accessible heading,
metadata, and action regions. It migrated medical records, care journals,
lost-and-found, marketplace, experts, and messages while retaining the
existing reference-directory consumers. The nine message folders remain above
the messaging shell, and the global linked-media navigation remains unchanged.

The current messaging continuation also replaces locale-dependent call state
with stable codes, moves call control/icon preparation into a class-based Blade
component, localizes the complete preflight surface in EN/LT/RU, and makes the
existing conversation-details route display its context panel at mobile and
tablet widths with a localized return control. This reuses the same protected
route, presenter, and context projection and adds no query or authorization
path.

The detail-page audit closes the `/share/{target}` English-fallback surface
with a dedicated 41-leaf EN/LT/RU contract. `SharePresenter` resolves five
stable target families and three stable delivery channels, keeps a measured
zero-query delta, and now renders a localized empty recipient state instead of
prototype neighbors.

The earlier deliberate `/neighbors/ari-jensen` prototype profile delivery is
superseded by AIR-001. Its name-specific route, presenter, statistics, pet
routine and action payloads are removed from the runtime; the canonical
neighbor directory now renders honest data or an empty state, and arbitrary
member profiles use `members.show` with viewer-aware privacy.

The next Package 8 wave migrated the medical-record, care-journal,
lost-and-found, marketplace, and expert create/edit/booking flows, the device
directory and connect flow, and both professional-workspace states. Their ten
route-ledger entries now enforce the canonical page identity, and the retired
device directory selector was removed.

The same wave then standardized the canonical database content feed, social
preview feed, all prepared composer modes, the knowledge directory, and all
three knowledge editor modes. This retires page-level feed headings and
knowledge uses of the generic forum header without changing their filters,
forms, authorization, or Livewire state.

The event directory and database-backed event workspace now share the same
page identity while preserving the event image, status, organizer context,
privacy policy, lifecycle, and registration controls. Created prototype event
details remain classified under the deliberate detail-hero contract.

The forum directory, topic editor/detail, persistent groups, journals,
mentorship, expert sessions, and administration now use the canonical header.
The directory also exposes all roots while rendering only the active root's
direct children; child selection is validated and filtered server-side through
an indexed Eloquent scope.

The category tree is now a dedicated anonymous Blade component in the main
discussion column instead of a narrow left sidebar. Its progressive root
catalogue, selected-category purpose, breadcrumbs, and complete child grid
retain the existing query-string contract and zero-query warm tree cache. The
desktop directory keeps only the contextual knowledge/update rail; tablet and
mobile layouts move that rail below the topic stream without horizontal page
overflow. Display labels use Unicode-safe sentence capitalization while the
source manifest, stable keys, aliases, and slugs remain unchanged.

Shared actions and filter chips now retain the 44-pixel touch target at desktop
as well as mobile widths. The Blade localization audit also understands bound
component props, preventing already-localized values from being rewritten and
double escaped.

The complete isolated Pest run passed 2,484 tests and 80,398 assertions. The
275-test affected-domain run passed 3,488 assertions; Pint, Larastan,
localization, Vite, dependency audits, cache smoke, migration lifecycle,
isolated migration/seed/idempotency, forum-source preservation, and diff checks
also passed. Authenticated browser checks covered 16 route families at 375 px,
the forum at 1,440 px, a selected subcategory, `/meetups`, and an event
workspace with one canonical header, one `h1`, no horizontal page overflow,
44-pixel actions, no console errors, and no SQL error. The live classification
ledger covers all 111 current first-party GET routes. The stable requirement
ID, remaining locale/zoom/forced-colors fixtures, deliberate detail/workspace
exception audit, scoped publication, and final global audit remain open; this
delivery is not globally complete.

## Completed Delivery: Guest Join Page

Status: `superseded` by the authenticated portal boundary on 2026-08-03

- Replaced the guest root prototype feed with the localized, privacy-aware
  joining experience specified in `docs/plans/join-landing-page-plan.md`.
- Preserved the stable `home` route name; active verified members enter the
  canonical content feed and active unverified members to email verification.
- Used one primary account-creation action, passive Blade, first-party product
  presentation, current design tokens, and no guest database query.
- Removed fictional member identity and private member navigation from the
  guest document.
- Verified route state, auth continuity, EN/LT/RU, metadata, accessibility,
  320-1920 pixel browser behavior, Pint, Larastan, 2,037 serial Pest tests,
  and the production Vite build. The still-tested prototype feed was retained
  behind the authenticated `preview.feed` route instead of being deleted.

The market and settings rationale is recorded in
`docs/audits/pet-social-network-benchmark.md`. A consolidated settings center
is a separate future work package and must be mapped to exact open requirement
IDs before implementation.

## Current Delivery: Authenticated Portal Boundary

Status: `verified` on 2026-08-03

- Added one central session-aware boundary before route-model binding and made
  it persistent across Livewire updates.
- Reduced anonymous access to localized login, registration, and password
  recovery; JSON product requests return `401` without product data.
- Restricted product access to active verified accounts while retaining
  route-specific policies, grants, throttles, and step-up checks.
- Revoked anonymous medical/care/device token-share access as an outer route
  boundary without weakening token expiry, scope, revocation, or audit rules.
- Disabled direct local storage serving and public storage-link generation.
  Product uploads render through a canonically contained authenticated media
  route with bounded content types.
- Verified 2,092 serial Pest tests and 73,983 assertions, including guest
  zero-query denial, route ordering, Livewire upload/preview denial, token
  shares, traversal, unsupported content, and symbolic-link escape.
- Verified the complete non-test release gate: dependency audits, production
  build, fresh migration and repeated seed, cache smoke checks, generated
  requirement checks, and authenticated browser flows with the EN/LT/RU
  account-entry shell at 320-1920 pixels and no console errors.

The exhaustive execution and release plan is
`docs/plans/authenticated-portal-access-plan.md`.

## Current Delivery: Forum Database Correctness Reconciliation

Status: `verified` on 2026-08-03

- Added portable fixed-value constraints and backed enum casts for answer
  votes and photo reactions.
- Added moderation-case optimistic versioning and unique closure request keys.
- Added one policy-authorized, row-locked, retry-bounded, idempotent close
  Action with transactional bulk audit events and no new route or UI.
- Corrected the canonical topic answer pointer during competing single-answer
  acceptance.
- Added direct database, rollback/reapply, authorization, replay, stale-write,
  archival/cast, duplicate-attempt, and constant-query-growth tests.
- Verified the complete combined gate: 2,303 tests and 76,179 assertions,
  117 fresh migrations / 196 tables / stable repeat seed, full Pint/Larastan,
  dependency audits, Vite/cache compilation, deterministic 38,377-record
  generation, and EN/LT/RU loopback Chrome checks.

Exact scope, evidence, remaining gates, and stop conditions are in
`docs/plans/forum-database-correctness-reconciliation-work-package.md`.

## Current Delivery: Global Linked Media Navigation

Status: `verified` on 2026-08-03

- Added one passive Blade primitive that links representative media only from
  an explicit server-prepared canonical target and remains passive when the
  target is absent.
- Migrated eligible pet, group, neighbor, meetup, discovery, profile, expert,
  booking, messaging, and marketplace projections without adding queries or
  guessing routes.
- Preserved viewer, gallery, current-page, QR, map, video, upload, action, and
  private-download semantics through an exhaustive 73-template inventory.
- Added EN/LT/RU accessible labels, visible focus, reduced-motion and
  forced-colors behavior, exact-destination tests, nested-interactive source
  guards, and responsive browser checks.
- Passed the 19-test contract, 67-test affected slice, Pint, Larastan,
  dependency audits, Vite build, cache smoke checks, fresh isolated migration,
  repeat seed, and 24-route/viewport browser matrix.
- The final serial repository suite passed 2,303 tests and 76,111 assertions in
  131.130 seconds after an earlier concurrent loader conflict disappeared.

The exhaustive scope, classifications, acceptance criteria, and gate evidence
are recorded in `docs/plans/global-linked-media-navigation-plan.md`.

## Current Delivery: Canonical Places And Venues

Status: `verified` on 2026-08-03

- Replaced the static place identity boundary with policy-scoped Eloquent
  places, venue areas, exact-location grants and audits, and dynamic detail
  routes for newly submitted places.
- Retained the complete server-rendered directory, map alternative, emergency
  clinic mode, EN/LT/RU content, and encrypted per-user saves, follows, visits,
  private check-ins, corrections, warnings, reviews, and questions.
- Added reversible indexed migrations, production Actions, privacy-safe public
  projections, idempotent authority/catalog seeders, explicit factories, and
  event-to-place/venue links.
- Verified 13 directory tests with 140 assertions, 20 authority tests with 153
  assertions, scoped Pint and Larastan, 126 fresh migrations across 211 tables,
  repeated seed stability, dependency audits, Vite/cache compilation, and the
  final serial suite of 2,579 tests with 81,626 assertions.
- Verified authenticated desktop/mobile browser flows for `/places` and
  `/places/vingis-quiet-loop` with no overflow, broken images, raw translation
  keys, unnamed controls, console errors, or protected-address disclosure.
  The browser gate also caps place-card height at 480px on desktop and 720px on
  mobile; the final measured ranges were 384-473px and 614-654px respectively.

The authority, privacy, lifecycle, schema, and acceptance decisions are
recorded in
`docs/plans/portal-place-location-venue-authority-work-package.md`.

## Current Delivery: Canonical Portal Discovery

Status: `release verified` on 2026-08-03; attributable publication prepared.

- Replaced the static four-card discovery demonstration, fictional Richmond
  query, local pulse, trending topics, and weekend promotion with one bounded
  database-backed recommendation hub.
- Reused current event, group, place, expert, pet, user-actor, publication,
  social-block, localization, media, status, action, shell, and deep-link architecture.
- Added strict query/category validation, account and actor block filtering,
  `is_recommendable` filtering, public-only projections, and explicit omission
  of exact event/place locations.
- Added user-owned, policy-scoped, idempotent item/category hide and reset
  preferences with reversible indexed schema and a factory.
- Added canonical discovery directions, toolbar, sections, cards, empty/hidden
  states, EN/LT/RU translations, constant-query tests, and a repeatable browser
  gate for 1440/375/320px including long Lithuanian content.
- Added active verified member recommendations, a stable policy- and
  block-scoped `members.show` profile, and visible post recommendations through
  `ContentPublication::visibleTo()` and the canonical content route.
- Targeted evidence passes 12 feature tests / 121 assertions and the linked-media
  discovery contract. The final serial suite passes 2,657 tests / 84,589
  assertions; fresh migration plus repeat seeding, Larastan, Pint, dependency
  audits, production Vite, and the three-viewport browser gate also pass with
  no overflow, broken media, private-location leak, unnamed control, raw key,
  or console error. The all-category service projection is 12 bounded queries
  for 16 recommendations across all seven sections in the current demo world.

The baseline audit, architecture decisions, complete delivery plan, and release
evidence are in `docs/plans/discover-modernization-plan.md`; the stable page
contract is `docs/portal/discovery.md`.

## Current Delivery: Canonical Pet Workspace

Status: `implemented and release verified` on 2026-08-03.

- Replace `/pets` static nearby-pet fixtures and session-only Follow controls
  with one policy-aware Eloquent workspace for owned and actively shared
  `PetProfile` records.
- Keep cross-user pet recommendations under `/discover?category=pets`, expose
  pending manager invitations separately, and reuse canonical creation,
  profile, care, health, media, status, action, and pagination contracts.
- Validate query/filter/sort URL state, paginate at twelve, eagerly load the
  current manager and primary protected media, and keep query growth constant.
- Provide purpose-specific empty/filtered-empty states and full EN/LT/RU copy.
- Verify desktop and mobile behavior through the repeatable loopback Chrome
  gate in `scripts/pet-workspace-browser-check.mjs`.
- Release evidence includes the 2,670-test/84,934-assertion serial suite,
  zero-error Larastan, dependency audits, production Vite build, isolated fresh
  migration/seed and repeat-seed checks, and the three-viewport browser gate.

The baseline, decisions, implementation passes, security boundaries, and gate
evidence are recorded in `docs/plans/pet-workspace-modernization-plan.md`.

## Active Delivery: Portal Point 12 Completion

Status: `approved; canonical reading complete and specialist discovery
starting` on 2026-08-30.

This delivery completes the remaining `PLA-P01` and `PLA-P04` through
`PLA-P11` Portal packages from
`docs/plans/portal-events-completion-master-plan.md`, plus the directly
required `PLA-P33` through `PLA-P35` evidence and release work. It reconciles
the exact 3,449 `portal.*` atomic requirements against executable behavior and
does not create parallel user, pet, organization, place, message,
notification, media, payment, report, event, or search-case systems. The
exclusive specialist scopes and dispositions are recorded in
`docs/audits/portal-point-12-completion-work-ledger.md`; the principal owns
cross-module decisions, integration edits, evidence promotion, and
publication.

The start baseline is `main` at
`ae4ac3241f99b05645dcc07316f424dfb877892e`, aligned with `origin/main` before
work began. A concurrent Places/events/seeding workstream modified the shared
tree after that clean observation. Every pre-existing or concurrent hunk is
excluded from this delivery unless its ownership is explicitly transferred;
publication will use a temporary index containing only the attributable Portal
patch.

### Delivery contract

- One explicit named-route guest allowlist admits only account entry, public
  directories, public-safe detail projections, legal/static pages, health,
  and deliberately scoped temporary shares. Public presenters select an
  allowlisted projection at the query boundary and cannot expose participant
  lists, exact locations, tickets, incidents, medical facts, private activity,
  private media, hidden relationships, or non-public counts.
- Every promoted page has a canonical URL. Compatibility URLs may redirect
  only after resolving a safe canonical target; authorization failures and
  deleted or unavailable resources never leak a target through redirect
  location or timing-sensitive post-filtering.
- One request-scoped Portal context owns the authenticated account, selected
  manageable pet, selected active organization membership, locale, timezone,
  and capabilities. Stored identifiers are untrusted and are reauthorized on
  every HTTP request and every Livewire boot/hydration. Deep links preserve
  their explicit resource context and never silently switch a global context.
- One typed navigation registry supplies desktop and mobile primary
  navigation, active-module state, breadcrumbs, badges, contextual actions,
  safe back destinations, and palette navigation. No Blade template invents
  authorization or destination rules.
- Existing domain notifications feed one policy-scoped notification
  projection. Deep links are resolved at read time to authorized canonical
  resources and degrade to localized unavailable/deleted states without
  leaking private identifiers or content.
- Calendar, feed, global search, discovery, dashboards, and workspaces are
  bounded read projections over existing aggregates. Providers scope access
  before counts/results; public search uses a separate minimal projection;
  private results never enter a post-filtered collection or cache.
- Settings and data controls reuse the current user, privacy, notification,
  relationship, session, export, and deletion boundaries. Unsupported
  providers remain explicit unavailable states. Quick actions and the command
  palette invoke real routes or authorized server Actions, and destructive
  operations require their normal confirmation/step-up boundary.
- Shared empty, filtered-empty, loading, dirty, offline, unavailable,
  forbidden, and deleted states are localized in EN/LT/RU, keyboard complete,
  screen-reader explicit, forced-colors safe, reduced-motion safe, and mobile
  first. Each new projection has an explicit constant query ceiling and each
  Livewire surface has a measured serialized-payload ceiling.
- Factories produce valid bounded rows and deterministic local/demo/testing
  scenarios exercise guest, member, multi-pet, organization, professional,
  organizer, moderator, unavailable, and privacy-denied paths without network
  access or production identities.
- A Portal atom is promoted only after direct implementation plus a named
  automated or browser/security check proves the exact behavior. Existence of
  a route, component, enum, model, translation key, or document is never
  sufficient evidence.

| ID | Dependency | Owner | Affected paths/modules | Acceptance criteria | Required verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| POR-01 | Approved request and canonical Point 12 source | Principal plus public-projection, context/navigation, notification, search, dashboard, settings, quick-action, privacy, accessibility, and evidence specialists | Canonical requirements, portal plan/docs, routes, middleware, current foundations, evidence overlays | Current behavior is mapped to exact atomic IDs; conflicts and reused authorities are dispositioned; no atom is promoted from existence | Clean-base/current dirty-tree capture, route/page matrix regeneration, specialist reports, plan/ledger diff | in progress | Revert planning-only additions |
| POR-02 | POR-01 | Principal | Routes, `RequirePortalAccess`, public requests/controllers/presenters/resources, policies, public views, canonical redirects | Guest allowlist is explicit; owner, pet, organization, place, event, result, and archive projections expose only public allowlisted fields; compatibility redirects are safe | Red/green guest allowlist, enumeration, private-field, canonical-link, deleted/forbidden, query-budget, localization tests; public browser/security matrix | planned | Remove guest route names and public entry points; authenticated portal remains intact |
| POR-03 | POR-01 | Principal | Portal context value object/resolver, middleware, session keys, Livewire base concern, pet/organization switch actions and UI | Stale, forged, suspended, former-member, wrong-owner, or deleted context fails closed and is reauthorized on HTTP and hydration; deep links do not silently switch | Context forgery/revocation/request/hydration/replay tests and multi-context browser flows | planned | Disable switchers and clear only Portal context keys |
| POR-04 | POR-03 | Principal | Typed navigation/page registry, shared shell/header/mobile dock, breadcrumbs, contextual actions, back destinations | Desktop/mobile derive from one registry; current module, breadcrumbs, badges, actions, and returns are capability-scoped and canonical | Registry/route coverage, authorization, locale parity, keyboard, active-state, responsive browser tests | planned | Revert registry consumers to existing primary navigation |
| POR-05 | POR-02..04 | Principal | Existing notification model/delivery adapters, notification projection/controller/view/preferences/deep-link resolver | One notification center has bounded categories, safe previews, grouping, preferences, unread state, and authorization-at-read canonical deep links | Recipient isolation, revoked/deleted target, preview privacy, dedupe, locale, query-budget, browser tests | planned | Disable center links/delivery adapters while retaining domain records |
| POR-06 | POR-02..04 | Principal | Existing event/care/task/booking/content aggregates, calendar/feed projection, routes/views/export | Calendar and feed aggregate authorized source records without duplicating source state; public/external projection is minimal and private fields never serialize | Source-visibility, timezone, recurrence, revoked access, query-budget, cache-scope, export, browser tests | planned | Remove aggregate routes and keep source modules authoritative |
| POR-07 | POR-02..04 | Principal | Global search provider registry, query request/presenter/controller/view, existing Discovery and search-case systems | Search is typed, grouped, bounded, canonical, privacy-safe, scope-first, and explains empty/unavailable results; discovery remains the recommendation authority | Cross-account/blocked/private/count leak, pagination, query-budget, locale, keyboard, browser tests | planned | Remove global provider registry/route; retain domain search and Discovery |
| POR-08 | POR-03..07 | Principal | Role/capability widget registry, member/organization/professional/organizer/moderator dashboards and workspaces | Widgets reuse existing Actions and authoritative aggregates, prioritize urgent work, enforce role/tenant scope, and expose explicit states | Role matrix, former-member, wrong-tenant, count/query budget, locale, responsive browser tests | planned | Remove registry-driven dashboards without deleting domain data |
| POR-09 | POR-03..05 | Principal | Existing profile settings plus privacy, notifications, sessions, export/deletion data-control interfaces | Complete settings IA reuses authoritative records, authorizes every mutation, represents unavailable providers honestly, and provides safe export/deletion requests | Livewire direct-call/tamper/replay, policy, validation, idempotency, locale, payload-budget, browser tests | planned | Hide new settings sections and retain stored preferences/requests |
| POR-10 | POR-03..09 | Principal | Command/quick-action registries, palette Livewire component, shell trigger, existing routes/Actions | Results are capability-scoped and canonical; keyboard/focus behavior is complete; mutations use real authorized operations and destructive actions retain confirmation | Direct-call, forged target, revoked context, payload/query budget, keyboard and mobile browser tests | planned | Remove palette/quick-action entry points; underlying routes remain |
| POR-11 | POR-02..10 | Principal | Shared state components, EN/LT/RU files, CSS/JS, factories/seeders, query/payload assertions | Locale parity and all shared states pass; explicit ceilings are enforced; deterministic scenarios cover promoted roles and privacy boundaries | Localization/accessibility tests, factory/seed repeatability, focused performance tests, responsive/forced-colors/reduced-motion/offline browser checks | planned | Revert shared presentation/scenario additions only |
| POR-12 | POR-01..11 | Independent privacy, accessibility, and final reviewers, then principal | Frozen attributable diff, route/page matrices, Portal docs, exact evidence overlay, generated requirements/matrices, changelog | Every finding is reproduced and dispositioned; only directly proven Portal atoms advance; canonical/generated docs match behavior; no unrelated work is published | Focused domains, full Pest, Pint, Larastan, migration/rollback/fresh/repeat seed, Composer/npm audits, Vite, browser/security suites, cache smoke, source preservation, requirement generation, diff/secret review | planned | Revert unpublished Portal slice; after production use forward-fix data and disable affected entry points |

Implementation is test-first in dependency order: public projection and
canonical URLs, context, navigation, notifications/calendar/search,
dashboards/settings, quick actions, then shared state/performance/demo work.
Independent privacy and accessibility review run against a frozen attributable
diff before exact evidence promotion and the final repository gate.

## Active Delivery: Forum Category 25 Verification And Evidence Closure

Status: `approved; exact manifest selected and final verification starting`
on 2026-08-30.

This delivery closes the already implemented animal-science/evidence category
without inventing new behavior or inheriting adjacent taxonomy/moderation
work. The three prerequisite read-only audits agree that the selected atoms
have direct source, persistence, validation, presentation, localization,
factory, seed, and focused-test evidence; their honest current classification
is `implemented but not fully verified` because required repository gates were
previously not green. The shared checkout is concurrently dirty with
unrelated Places, events, portal, performance, seeding, and interface work.
Those paths remain user-owned and publication uses a temporary index.

### Exact requirement-ID manifest

This package owns exactly the following 58 Phase 4 extension atoms and no
others:

```text
forum.category.0237 forum.category.0238 forum.category.0239
forum.category.0240 forum.category.0241 forum.category.0242
forum.category.0243 forum.category.0244 forum.category.0245
forum.category.0246 forum.category.0247 forum.category.0248
forum.category.0249 forum.category.0250 forum.category.0251
forum.category.0252 forum.category.0253 forum.category.0254
forum.category.0255 forum.category.0256 forum.category.0257
forum.category.0258 forum.category.0259 forum.category.0260
forum.category.0261 forum.category.0262 forum.category.0263
forum.category.0264 forum.category.0265 forum.category.0266
forum.category.0267 forum.category.0268 forum.category.0269
forum.category.0270 forum.category.0271 forum.category.0272
forum.category.0273 forum.category.0274 forum.category.0275
forum.category.0276 forum.category.0277 forum.category.0278
forum.category.0279 forum.category.0280 forum.category.0281
forum.category.0282 forum.category.0283 forum.category.0284
forum.category.0285 forum.category.0286 forum.category.0287
forum.category.0288 forum.category.0289 forum.category.0290
forum.category.0291 forum.category.0292 forum.category.0293
forum.category.0294
```

`animal.taxonomy.0021` and `forum.moderation.0012` originate in the same
immutable source section but remain `discovered` in Phases 5 and 7. No
original-source `forum.feature.*` atom, category-26 atom, untranslated-child
contract, or broader forum control/release requirement receives status from
this package.

### Delivery contract

- Reinspect the manifest, existing category migration/models/Policy, catalogue
  validator, transactional synchronizer, locale-scoped tree cache, request
  validation, presenter/controller, passive navigator Blade, EN/LT/RU reviewed
  root copy, factories, seeders, focused tests, browser harness, documentation,
  and evidence overlay. File presence alone is never proof.
- Existing focused coverage already records the required RED/GREEN behavior
  for the fixed category heading and isolated browser entrypoint. Because this
  closure adds no new behavior, schema, policy, or interface, another failing
  test created solely to manufacture evidence is prohibited. Any newly
  reproduced attributable defect must receive a failing behavioral test before
  its smallest fix.
- Run verification against a clean `main` baseline containing the committed
  category implementation so unrelated, actively changing untracked files do
  not enter test discovery or formatting results. Re-run documentation and
  generated-artifact checks against the final attributable documentation
  patch.
- The missing local Codex history entry `1785397895` and absent PCOV/Xdebug
  coverage driver are external environment blockers. Execute both checks and
  record their exact result; do not convert either to a pass. Immutable payload
  checksums and deterministic generation remain independent required gates.
- Only after every non-external gate passes may the evidence overlay set the
  exact 58 atoms to `current_implementation_status: verified`,
  `implementation_status: implemented`, `verification_status: verified`, and
  `final_result: verified`. Generated catalogue, matrix, counts, manual plans,
  progress, testing evidence, final audit, compliance evidence, and changelog
  must then agree.
- Independent final review receives a frozen attributable diff and observed
  command ledger. Every material finding is reproduced and dispositioned;
  valid in-scope findings are fixed and affected checks rerun before staging.

| ID | Dependency | Owner | Affected paths/modules | Acceptance criteria | Required verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| FAC-C25-01 | Immutable source, Phase 4 category foundations, prior category-25 implementation | Principal plus FAC-S01, FAC-S02, and FAC-S11 read-only specialists | Source/category/evidence corpus, live schema/code/tests, coordination ledger | Exact 58-ID manifest and two exclusions are reconciled across every required layer; all selected atoms remain honestly `implemented but not fully verified` before gates | Source/checksum inventory, exact `jq` status query, specialist reports, Git provenance | complete | Revert this plan/ledger slice only |
| FAC-C25-02 | FAC-C25-01 | Principal; FAC-S12 read-only testing specialist | Existing category-25 and related forum/category/localization/schema/architecture tests | Existing focused tests prove all selected behavior; no speculative test or implementation is added; an attributable defect, if found, is first observed by a failing test | Focused 7-test contract and related suites through `scripts/run-tests.php` | in progress | Revert only an attributable test/fix pair |
| FAC-C25-03 | FAC-C25-02 | Principal | Clean baseline release boundaries; no production paths unless a red test proves a selected defect | Full Pest, Architecture, Pint, Larastan, migration/fresh/repeat seed, Composer/platform/audit, npm/build, cache, category and complete browser gates pass; external blockers remain explicit | Commands in `docs/testing.md`, migration lifecycle verifier, both browser commands, source/category/generator checks | planned | No runtime rollback when no production change exists; revert any test-first fix normally |
| FAC-C25-04 | FAC-C25-03 | Principal | `docs/traceability/forum-requirement-evidence.json`, generator-owned outputs, forum plans/progress/audits/testing/compliance/changelog | Only the 58 manifest IDs advance; both exclusions and all other atoms retain their prior states; generated and manual totals agree with observed gates | Regenerate through `scripts/generate-forum-requirements.php`, then exact status/count and byte-parity checks | planned | Revert overlay/manual evidence and regenerate prior outputs |
| FAC-C25-05 | FAC-C25-02..04 | Independent FAC-S13 reviewer, then principal | Frozen attributable diff, gate ledger, isolated temporary index | Review has separate requirement-compliance and code-quality verdicts; all findings are dispositioned; staged diff contains no unrelated bytes | Frozen diff review, `git diff --check`, `git diff --cached --check`, complete staged diff, branch/remote recheck | planned | Discard only the unpublished attributable patch; never alter unrelated work |
| FAC-C25-06 | FAC-C25-05 | Principal | One evidence-closure commit on `main` | Coherent attributable commit is created only after review and required verification, then pushed fast-forward to `origin/main` with observed result | Commit/tree inspection, push output, final `main...origin/main` status | planned | Normal revert of the coherent evidence commit; regenerated outputs revert with overlay |

After FAC-C25-06, select category 26 as a new exact dependency-safe package;
do not fold it into this delivery. The later source-stream audits remain
read-only planning evidence and cannot promote their requirements.

## Active Delivery: Provider-Ready Event Finance, Tickets, And Attendance

Status: `approved; canonical reading and specialist discovery in progress` on
2026-08-30.

This delivery implements the provider-neutral P19 event finance/ticket
foundation and the P21 secure attendance foundation against the canonical
`ForumEvent`, occurrence, registration, organization-authority, pet-eligibility,
and capacity boundaries. It does not select, emulate, or claim a payment
provider. Paid checkout remains disabled unless a separately approved real
provider is configured, while free tickets, durable state, reconciliation,
secure credentials, online scanning, offline capture/reconciliation, and
manual authorized lookup remain provider-independent.

The task began on `main` at `ae4ac32`. The shared tree contains concurrent,
unrelated Places, Portal, forum-category, event-planning, seeding, generated
evidence, and test work. Those paths and hunks remain user-owned. The
attributable boundary, specialist assignments, frozen-review rules, and
finding dispositions are recorded in
`docs/audits/event-financial-attendance-work-ledger.md`. The principal owns all
repository edits and cross-domain decisions. Publication uses a temporary
`GIT_INDEX_FILE` and cannot include the shared index or unrelated bytes.

### Architecture and integrity contract

- Ticket types own an immutable-at-reservation integer-minor-unit price and
  uppercase currency, sales window, bounded or explicitly unlimited inventory,
  per-reservation and per-account limits, active state, occurrence scope, and
  typed registration-eligibility rule. Server locks and database uniqueness,
  not preflight UI checks, protect inventory and limits.
- Reservations, economic payments, provider attempts, refunds,
  disputes/chargebacks, issued tickets, provider events, reconciliation
  records, and immutable financial audit entries are separate durable records.
  Registration state never serves as payment truth, and payment success never
  overrides event cancellation, eligibility failure, capacity loss, ticket
  revocation, or registration cancellation.
- Reservation transitions are explicit: `active -> awaiting_payment ->
  confirmed`, with terminal `expired` and `cancelled` exits. A free reservation
  confirms and issues from server authority without fabricating a payment.
  Paid reservations require a configured provider and remain held only for a
  bounded server-owned expiry.
- Payment attempts transition through `created`, `submitted`, `pending`, and
  `succeeded`, or terminal `failed`, `expired`, and `cancelled`. The economic
  payment independently derives `unpaid`, `pending`, `paid`,
  `partially_refunded`, `refunded`, `disputed`, and `charged_back`. Refunds are
  durable partial/full operations with their own pending/succeeded/failed or
  reversed outcome. Dispute/chargeback evidence is never encoded as a refund.
- The browser supplies only a durable resource reference and operation key.
  Event, occurrence, actor, ticket type, amount, currency, price checksum,
  refund balance, eligibility, and issuance predicates are re-derived from
  locked server state. Same scoped operation key and request fingerprint
  replays the original result; a changed fingerprint conflicts. Concurrent
  refunds cannot exceed captured value.
- One event-specific provider interface exists only at the actual external
  payment boundary. The default disabled implementation performs no network
  request and exposes a typed unavailable capability. Provider I/O never runs
  under database locks. Timeout, rate limit, malformed response, and ambiguous
  partial failure leave a retry-safe attempt requiring reconciliation; they do
  not create a paid, confirmed, ticketed, or refunded browser claim.
- A webhook is accepted only for a configured provider. Its signature and
  freshness are checked against the raw request body before any decoded field
  is trusted. A provider-scoped event digest is inserted uniquely before
  financial mutation. Replays acknowledge the canonical prior result;
  amount/currency/reference mismatches fail closed; raw payloads, credentials,
  authorization headers, checkout URLs, and card data are never persisted or
  logged.
- Reconciliation compares provider observations with immutable internal
  amount, currency, reference, and state facts. Each run/item and every
  financial transition writes append-only, actor/source-attributed safe audit
  evidence. Corrections are new records and compensating transitions, never
  history rewrites.
- Each issued ticket may receive an opaque random credential containing only a
  non-semantic version prefix and secret. Only a digest is stored. Purpose,
  event, occurrence, ticket, expiry, revocation, refund/dispute status, and
  eligibility are checked server-side. QR content never contains database IDs,
  attendee identity, pet/medical facts, private location/access instructions,
  or authorization claims.
- Scan operations have a client-generated UUID and safe event, occurrence,
  authenticated scanner, device digest, source time, server time, entrance,
  channel, result, and reason context. One conditional authoritative
  registration/ticket transition plus unique operation identity prevents two
  accepted check-ins during concurrent scans; later attempts are immutable
  duplicates or conflicts.
- Offline capture encrypts the temporary raw credential locally, records only
  the bounded scan envelope, and deletes accepted/rejected material after
  reconciliation. The server reauthorizes the scanner and reevaluates every
  current predicate. Revoked, refunded, disputed, invalid, expired,
  wrong-event, cancelled, or ineligible server state always defeats offline
  observations. Batch results are per-operation and support safe partial
  retry. Manual lookup is event-scoped, minimal, bounded, and policy-authorized.
- The class-based Livewire scanning workspace and passive Blade view provide
  precise online, queued-offline, synchronizing, accepted, duplicate,
  conflict, invalid, revoked/refunded, unavailable-provider, empty, loading,
  and error states in EN, LT, and RU. External scanners and manual entry retain
  a full keyboard path, labelled controls, status announcements, visible
  focus, 44-pixel targets, reduced-motion and forced-colors behavior, and no
  horizontal overflow.
- Provider tests use an application fake at the interface boundary and an
  exact fake HTTP adapter harness only; stray requests are prohibited. Success,
  timeout, invalid signature, duplicate event, malformed response, rate limit,
  ambiguous/partial failure, refund, and reconciliation tests never contact a
  real provider.

The canonical lock order is event, occurrence/capacity pool in ascending ID,
ticket type, reservation, registration, ticket, payment, attempt, then refund,
dispute/chargeback, provider-event, reconciliation, credential, and scan rows
in ascending ID. External calls happen between committed phases, never while
this lock set is held.

| ID | Dependency | Owner | Affected paths/modules | Acceptance criteria | Required verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| EFA-01 | P17/P18 authority; P19/P21 sources; security/privacy/integration contract; current shared-tree baseline | Principal plus EFA-D1 through EFA-D8 read-only specialists | Canonical event/payment/ticket/check-in/offline/security/privacy docs; current event aggregate; work ledger; this plan | Current behavior, exact provider absence, state/integrity gaps, lock order, additive path boundary, and specialist findings are reproduced and dispositioned before production edits | Source inventory, dirty-tree evidence, specialist reports, targeted baseline tests, plan/ledger diff | in progress | Revert only this planning and ledger slice |
| EFA-02 | EFA-01 | Principal | Focused Pest feature/unit/concurrency/architecture tests and test-only provider/webhook fakes | Red tests isolate ticket definition, price snapshot, inventory/eligibility/limit races, every state transition, disabled checkout, provider failures, signature/dedupe, reconciliation/audit immutability, QR disclosure/revocation/replay, offline precedence/partial retry, authorization/locales/accessibility | Each new behavior is observed failing before its production implementation; fakes assert no stray network | planned | Revert only new red contracts |
| EFA-03 | EFA-02 | Principal | Additive event-finance/attendance migration; enums; models; factories; model relationships; architecture guards | SQLite-portable constraints/indexes enforce scoped uniqueness, minor-unit/currency fields, operation/provider-event identity, bounded relationships, append-only audit/scan evidence, and reversible fresh-install schema; every first-party model has a factory | Schema/cast/relation/factory/constraint/rollback-reapply tests and architecture checks | planned | Roll back before writes; after use preserve financial/attendance evidence and forward-fix |
| EFA-04 | EFA-03 | Principal | Ticket-type, reservation, free-ticket issue, expiry/cancel Actions; policy abilities; registration/capacity integration | Authorized organizers define ticket types; eligible users receive server-priced reservations within windows/inventory/limits; free issue is atomic/idempotent; paid reservations fail closed without provider; cancellation/expiry releases capacity once | Positive/negative policy, window, eligibility, old/new price, limit, final-ticket concurrency, replay/payload-conflict, cancel/expiry tests | planned | Disable ticket-type/reservation mutations; preserve issued/history rows and forward-fix after use |
| EFA-05 | EFA-03..04 | Principal | Event payment provider contract/DTOs/errors, disabled driver/config/availability, payment/start/result/refund/dispute/chargeback Actions | One event-specific real-boundary contract; disabled/unknown configuration sends nothing; server values own every request; external calls occur outside transactions; ambiguous outcomes reconcile; refunds cannot overrun captured value; paid browser returns never mutate success | Disabled/no-request, success, timeout, 429, malformed, partial failure, wrong amount/currency/reference, attempt/refund replay, cancellation, dispute/chargeback, concurrency tests | planned | Set driver to disabled, hide checkout/refund entry points, retain attempts/audits for reconciliation |
| EFA-06 | EFA-03..05 | Principal | Stateless webhook route/controller/request boundary, provider verification DTO, event/result Actions, throttling and redacted audit | Raw-body signature/freshness precede decode/trust; provider event uniqueness precedes mutation; duplicates are idempotent; out-of-order/mismatch/unknown/processing failure are retry-safe and never browser-authoritative; secrets and raw bodies are absent | Valid, invalid-signature, stale, duplicate, malformed, unknown, mismatch, out-of-order, processing-failure/retry, rate-limit, redaction, CSRF-origin separation tests | planned | Disable webhook route/provider key; retain receipt/result records for reviewed replay |
| EFA-07 | EFA-03..06 | Principal | Immutable financial audit and reconciliation run/item models/Actions; operator policies; cancellation/reschedule integration | Every financial transition has append-only before/after/source/actor evidence; reconciliation is repeatable and records match/mismatch/missing/ambiguous outcomes; corrections compensate; paid cancellation/reschedule cannot silently release or confirm | Immutability, balance, replay, mismatch, operator separation, cancellation/reschedule, provider-unavailable reconciliation tests | planned | Disable reconciliation commands/UI; retain immutable evidence and use compensating records |
| EFA-08 | EFA-03..07 | Principal | Ticket credential issue/rotate/revoke and scan Actions; attendance policies; online scan and manual lookup | Random one-time-returned credential with stored digest and server scope; no sensitive QR payload; revoked/refunded/disputed/wrong-event/expired/ineligible tickets fail closed; conditional transition accepts once under concurrent scans; manual lookup is scoped/minimal/authorized | Payload/source scans, digest/rotation/revocation, wrong-event, refund precedence, duplicate/replay/payload-conflict, two-process scan, manual lookup isolation/query-budget tests | planned | Disable issue/scan/manual controls; revoke active credentials while retaining ticket/audit history |
| EFA-09 | EFA-08 | Principal | Offline scan batch request/controller/Action, encrypted IndexedDB queue module, CSRF/auth/throttling, retention cleanup | Unique operations capture bounded device/source context; encrypted local records sync in bounded batches; current server truth wins; per-item accepted/duplicate/conflict/rejected results retry safely; reconciled secrets are deleted; no offline claim becomes authoritative before sync | Offline replay, changed payload, old/future time, partial batch/failure/retry, wrong account/event/device, revoked/refunded-after-capture, storage/crypto unavailable, JS source/behavior tests | planned | Remove offline enhancement and endpoint; online/manual scanning and durable server history remain |
| EFA-10 | EFA-04..09 | Principal | Class-based event ticket/attendance Livewire components, passive Blade, event workspace integration, EN/LT/RU translations, CSS/JS | Paid/free/unavailable distinctions are server-prepared and truthful; no browser-only payment/ticket/refund state; scanner feedback and manual fallback meet mobile, keyboard, screen-reader, focus, reduced-motion, forced-colors, touch-target, and privacy contracts | Livewire direct-call/tamper/state tests, locale parity, architecture, production Vite, authenticated responsive/keyboard/offline/console browser matrix | planned | Remove new UI entry points and JS while retaining provider-neutral domain/audit records |
| EFA-11 | EFA-03..10 | Principal | Event factories/demo seeder; events/payment/ticket/check-in/offline/security/authorization/architecture/data/testing/seeding/deployment/integration docs; implementation plan; compliance evidence and changelog | Deterministic free, paid-unavailable, pending, paid, partial/full-refund, disputed, valid/revoked/duplicate/offline-conflict/manual scenarios are environment-safe and repeatable; docs describe only observed provider applicability and gate results | Factory inventory, fresh migration/complete seed/repeat seed, source/generator checks, docs/diff/secret scans | planned | Revert demo/docs with package; never delete durable production finance/attendance evidence |
| EFA-12 | EFA-01..11 | Independent EFA-R1 reviewer, then principal | Frozen attributable diff and command ledger | Every requested requirement and specialist concern is independently reviewed and dispositioned; valid findings are fixed; no unresolved critical/important provider, financial, ticket, QR, offline, authorization, privacy, localization, accessibility, migration, race, or test defect remains | Payment/ticket/QR/security/concurrency suites; full serial Pest; Pint; Larastan; Composer validation/audit/platform check; fresh migration/full seed/repeat seed; npm audit/build; route/config/view caches; responsive authenticated browser/offline/keyboard/console flows; generated docs; staged diff/secrets | planned | Revert the coherent unpublished slice; after production writes disable entry points and forward-fix schema/data |

Implementation is test-first in dependency order `EFA-01` through `EFA-12`.
Payment applicability and exact requirement evidence remain unchanged until
every applicable gate has been observed and the independent review is closed.

## Active Delivery: Final Repository-Wide Production Release Audit

Status: `approved; mandatory reading complete and read-only specialist audit
starting` on 2026-08-30.

This release audit reconciles the live `main` tree against every canonical
requirement, active plan/progress record, generated requirement artefact,
changelog, deployment instruction, and production quality gate. It does not
inherit completion from any historical snapshot or currently active delivery.
The exclusive read-only specialist scopes, protected dirty-tree baseline,
finding dispositions, gate evidence, and publication rules are recorded in
`docs/audits/final-release-audit-work-ledger.md`.

The audit began at `ae4ac3241f99b05645dcc07316f424dfb877892e`, aligned with
`origin/main`, on a materially dirty shared tree containing concurrent Places,
event, Portal, page-identity, performance, seeding, interface, test, generated
evidence, and documentation work. Every pre-existing or concurrent byte stays
user-owned. Reviewers are read-only; the principal owns every edit and may use
only an attributable temporary-index slice if all release conditions are met.

| ID | Dependency | Owner | Affected paths/modules | Acceptance criteria | Required verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| FRA-01 | Repository contract and complete mandatory reading | Principal | Canonical plan and final-audit ledger | Exact baseline, ownership, twelve exclusive reviewer scopes, evidence semantics, release gates, and rollback are recorded before production edits or delegation | Git/remote/runtime/status evidence and canonical documentation inventory | complete | Revert audit-only planning additions |
| FRA-02 | FRA-01 | Eleven independent read-only discovery specialists; principal dispositions | Entire first-party repository | Requirements, security, database, architecture, Livewire/Blade, localization/accessibility, performance/cache, integrations, testing/coverage, deployment/rollback, and documentation findings are reproduced and classified | Structured specialist reports and principal read-only reproduction | in progress | Documentation-only correction |
| FRA-03 | FRA-02 | Principal | Tests and smallest attributable implementation/documentation slices | Every valid fixable material finding receives test-first correction where behavior changes; unrelated work remains untouched | Targeted red/green checks, static analysis, exact ownership diff | pending | Revert only the attributable test/fix pair |
| FRA-04 | FRA-02..03 | Principal | Current-state/unfinished/domain progress, requirements/evidence, generated outputs, changelog, deployment/operations, final report | Active statuses and exact evidence match live implementation and observed results; useful history is clearly historical; external blockers remain blocked | Generator parity, route/symbol/link/reference scans, documentation review | pending | Revert audit docs and regenerate prior outputs |
| FRA-05 | FRA-03..04 | Principal | Complete current tree | Every requested release gate runs safely with exact environment, command, exit, result, blocker, risk, and rollback evidence | Full release command ledger in `docs/reports/final-release-verification.md` | pending | Remove only disposable verification artefacts |
| FRA-06 | FRA-05 | Independent adversarial reviewer; principal dispositions | Frozen attributable diff and every release/completion claim | Every material claim is challenged, reproduced, dispositioned, fixed when valid, and affected checks rerun | Independent final review and post-fix gate evidence | pending | Revert finding-specific correction if unsafe |
| FRA-07 | FRA-06 | Principal | Temporary-index attributable slice on `main` | Commit and fast-forward push occur only if every applicable gate passes and no fixable failed gate, partial canonical requirement, unclassified active requirement, contradiction, or material review finding remains | Complete staged diff, both diff checks, branch/origin recheck, commit/push output | pending | Normal revert commit only; never rewrite history |

Execution order is `FRA-01` through `FRA-07`. Provider and hardware success
cannot be simulated; coverage and browser gates cannot be waived; historical
pass records cannot replace current execution; and a dirty concurrent slice is
reported as a blocker rather than overwritten or silently published.

## Active Delivery: Public Experience Accessibility, Responsive UX, UI States, and SEO

Status: `approved; specialist current-checkout audit and RED contracts in progress`
on 2026-08-30.

This delivery audits the anonymous entry experience, explicitly authorized
anonymous shared content, genuinely public indexable pages, and the shared UI
components needed by critical authenticated workflows. Public SEO applies only
after effective middleware proves a route is anonymous and intended for
indexing. Private portal, authentication, recovery, verification, temporary
share, and error routes receive no public SEO infrastructure. Full scope,
defect hypotheses, route/viewport/state matrices, browser plan, and acceptance
criteria are recorded in `PLANS.md`; exclusive agent ownership and reports are
recorded in `docs/audits/accessibility-responsive-seo-work-ledger.md`.

The delivery resumed on `main` with the branch three commits ahead of
`origin/main` and a materially dirty shared index/tree. Every pre-existing byte
remains user-owned. The principal owns all edits and cross-module decisions;
publication may use only an attributable temporary index after complete gates.

| ID | Dependency | Owner | Affected paths/modules | Acceptance criteria | Required verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- | --- |
| A11Y-PUB-01 | Repository contract, mandatory reading, current Git/runtime baseline | Principal plus seven named read-only specialists | `PLANS.md`, work ledger, effective route inventory, shared/public layouts/components/scripts/tests | Current semantics, keyboard/focus, screen-reader, responsive/form/state and SEO defects are reproduced and dispositioned; protected concurrent work is explicit | Git/diff baseline, effective middleware/route evidence, seven structured reports | in progress | Revert planning/ledger additions only |
| A11Y-PUB-02 | A11Y-PUB-01 | Principal | Focused Pest and existing browser-runner contracts | Each confirmed behavior defect has a minimal test observed failing for the expected reason before production edits | Exact RED commands/output for semantics, validation/focus, dialogs, live states, responsive journeys and metadata | planned | Revert test-only additions |
| A11Y-PUB-03 | A11Y-PUB-02 | Principal | Shared/public Blade/Livewire/JS, EN/LT/RU catalogues, existing design tokens/component SCSS | Native semantics, headings/landmarks/names/descriptions, form associations and focus, dialog lifecycle, keyboard alternatives, announcements, all applicable UI states, duplicate prevention and non-color/destructive status meet the plan | Focused GREEN PHP/JS/browser tests, locale parity, architecture checks | planned | Revert each coherent behavior/component slice |
| A11Y-PUB-04 | A11Y-PUB-02..03 | Principal | Navigation/forms/tables/cards/overlays/pagination/filter/toolbar styles and browser assertions | No page overflow; local table scrolling stays named/semantic; 320..1920 widths, 200%/400% reflow, long RU/LT, touch, forced colors and reduced motion retain complete operation without global blocking | Existing isolated browser runner at required viewports/preferences plus production Vite build | planned | Revert responsive presentation slice |
| A11Y-PUB-05 | A11Y-PUB-01..04 | Principal | Effective route/indexability policy, layouts/head metadata, robots only where justified | Only intended public pages are indexable; unique localized title/description, canonical, robots, OG, locale, pagination and safely encoded JSON-LD exist only when applicable; no private data leaks; sitemap remains absent unless a public URL set justifies it | Route/middleware and rendered-head tests, duplicate/leak scans, browser head inspection | planned | Restore prior metadata/robots behavior without weakening authorization |
| A11Y-PUB-06 | A11Y-PUB-03..05 | Three independent named reviewers then principal | Frozen attributable diff, browser artifacts, route/head matrix | Every material accessibility, responsive, SEO/semantic finding is reproduced and dispositioned; valid findings are fixed and affected browser/PHP tests repeat | Three independent reports and post-fix focused reruns | planned | Revert unsafe finding-specific correction |
| A11Y-PUB-07 | A11Y-PUB-06 | Principal | Requested canonical docs, generated compliance source/output, changelog, full attributable slice | Documentation states only observed behavior; all applicable repository gates pass before one coherent task-owned commit and fast-forward push on `main` | Focused/full sequential Pest, Pint/Larastan, Composer/npm audits, migrations/seeds, Vite, caches, browser, generators, secrets and staged diff | planned | Normal revert commit only; never reset or rewrite history |

Implementation is test-first in order `A11Y-PUB-01` through
`A11Y-PUB-07`. Browser unavailability, concurrent failures, or a dirty
unattributable overlap remains explicit evidence and blocks publication rather
than being waived.

## FAC-2026-08-30 — First-party Eloquent factory overhaul

**Status:** in progress  
**Owner:** principal agent (`FAC-ROOT`)  
**Dependencies:** current schema/model contracts; `docs/seeding.md`; `docs/testing.md`; repository factory/seeder gates  
**Affected paths:** `PLANS.md`, `app/Models/**` (inspection only unless a verified model defect blocks validity), `database/factories/**`, `tests/Feature/Database/**`, `tests/Fixtures/**`, `docs/factory-coverage.md`, `docs/seeding.md`, `docs/testing.md`, `docs/requirements/compliance-matrix.md`, `CHANGELOG.md`

### Acceptance criteria

- Every concrete first-party Eloquent model has a resolvable factory whose default creates one minimal valid row, or a documented technical exemption with test construction guidance and linked requirement.
- Defaults satisfy foreign keys, uniqueness, check constraints, enum domains, owner/tenant coherence, date ordering, monetary representation, lifecycle state, and required relationships without hidden large graphs.
- Meaningful lifecycle/content/role/date/optional-data states and explicit relationship helpers exist only where supported by real domain semantics.
- Local non-personal fixtures cover every factory-relevant image, document, upload, JSON payload, and external-provider response without public-network access.
- Automated tests create every default and every documented state/helper, verify uniqueness and file behavior, and include an isolated parallel-creation probe.
- Three independent reviewers inspect a frozen attributable diff; every material finding is reproduced, dispositioned, fixed when valid, and followed by affected checks.
- `PLANS.md`, seeding/testing/factory coverage, compliance evidence, and changelog remain synchronized with observed verification only.

### Verification

Targeted Pest tests first; PHP syntax; Pint; Larastan with stable serial diagnostics when needed; full Pest sequentially for shared SQLite; isolated migration/seed/idempotency; Composer validation/audit; dependency compatibility; NPM audit and production build; route/config/view cache smoke; generated-document checks applicable to touched evidence; `git diff --check`; secret and complete attributable-diff review.

### Rollback

Revert the single coherent factory-overhaul commit. The work adds test-data builders, fixtures, tests, and documentation only; it does not require destructive schema or production-data rollback.
