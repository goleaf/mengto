# Universal Registration, Identity, And Runtime Entity Removal Plan

> **For agentic workers:** execute this plan with test-first changes and independent read-only review. The principal agent owns all repository writes because this is a dirty shared `main` checkout.

**Plan ID:** `URE-001`  
**Plan date:** 2026-08-31  
**Goal:** Make registration, onboarding, authenticated identity, profiles, and every production directory/detail surface derive entity instances exclusively from authorized persisted models, while new accounts begin with no pets or other user-owned content.

**Architecture:** `User` is the account source of truth, its personal `SocialActor` is the stable social/routing identity, and `SocialActorSetting` plus `UserOnboarding` are the mandatory privacy-first account companions. Production presenters format persisted models; they never define named people, animals, relationships, content, or statistics. Demo entity instances are ordinary canonical records created only by explicit environment-gated seed/factory infrastructure.

**Tech stack:** PHP 8.5, Laravel 13, class-based Livewire 4 with separate Blade views, Eloquent, SQLite-compatible schema/query behavior, Tailwind CSS 4/Vite, Pest 4, Larastan/PHPStan, EN/LT/RU.

## Global Constraints

- Work only on `main`; preserve every unrelated staged, unstaged, and untracked byte.
- Production runtime may describe entity types and system behavior but may not define named entity instances.
- Allowed named fixtures are limited to `database/seeders/`, `database/factories/`, `tests/`, and explicit test fixtures. Historical documentation may retain clearly identified non-executable examples.
- Registration creates one `User`, one personal `SocialActor`, one `SocialActorSetting`, one `UserOnboarding`, and no pet/content/social records.
- Human and pet display names are mutable data; stable actor/profile keys are routing identifiers and never browser-supplied authority.
- No historical production migration is rewritten. Any unsafe released-data compatibility behavior receives an additive forward repair and deployment evidence.
- Privacy, discoverability, blocks, active-account state, configurable verification, onboarding, pet authority, and exact-location boundaries remain fail-closed.
- Blade remains passive, Livewire remains class-based, routes remain declarative, and database queries remain bounded and model-aware.
- EN/LT/RU keys and placeholders remain aligned; persisted names and user content are not translated.
- The package is not publishable until applicable full gates and independent reviews are observed green.

## No Built-In Entity Law

Production runtime includes `app/`, `routes/`, `resources/`, `lang/`, `config/`, `bootstrap/`, `public/` runtime manifests, and `database/migrations/`. Those paths may contain neutral vocabulary such as “User”, “Pet”, “Organizer”, or “No pets yet”, but cannot encode a named person, animal, handle, biography, avatar, relationship, entity-specific route, or fabricated domain statistic as an application record.

Neutral initials, generic icons, generic paw placeholders, and honest zero-data messages are allowed because they do not pretend to be records. Stock portraits/pet photos, fake biographies/locations, and non-zero fabricated counts are not allowed.

## Phase 0 — Repository And Git Safety (`URE-00`)

- [x] Record branch `main`, starting HEAD `d92cf5e74593a0a79b23c721ffc874fb76eba3d2`, and matching `origin/main`.
- [x] Record the mixed shared tree before this package. The pre-existing identity delivery and unrelated EventCompetition/Place/portal work are interleaved across staged and unstaged changes.
- [x] Adopt selective task-owned staging through a temporary `GIT_INDEX_FILE` only after verification.
- [ ] Recheck remote state immediately before any commit or push.

Rollback: revert only an eventual coherent `URE-001` commit. Never reset, clean, stash, or rewrite the shared tree.

## Phase 1 — Complete Hardcoded Entity Inventory (`URE-01`)

Initial confirmed runtime defects:

- `app/Services/EventContentCatalog.php` creates a named Mochi attendee.
- `app/Services/GroupContentCatalog.php` creates a named Jamie record.
- `lang/{en,lt,ru}/messages.php`, `lang/{en,lt,ru}/ui.php`, and `lang/{en,lt,ru}/groups.php` contain Ari/Mochi/Jamie/Theo/Bean identity, biography, image-alt, relationship, and organizer records.
- `app/Services/PrototypeState.php` and `app/Services/PreviewService.php` remain production dependencies of feed, circle, compose, notification, neighbor, post, share, and walk controllers/presenters/actions.
- `routes/web.php` still exposes preview controllers for normal product routes, a literal `/groups/apartment-pets-pdx` route/default, and a hardcoded six-group route allowlist.
- Several `*PreviewController`, `*Catalog`, and `*Presenter` classes still need semantic classification even where known names are absent.
- The released `2026_07_30_155124_add_identity_fields_to_users_table.php` migration contains the historical `test@example.com` to `mia-carter` compatibility mapping. It must not be edited; a production data audit and additive repair decision are required.

Required inventory output is recorded in `docs/audits/universal-runtime-entity-removal-work-ledger.md` with one of: production runtime defect, legitimate system vocabulary, seed/factory fixture, test fixture, or historical documentation. Searches include known literals and semantic patterns: first-record fallbacks, literal stable-key queries, entity defaults, static name/avatar/bio arrays, stock media, fixed counts, hardcoded action targets, and browser/session keys.

Acceptance:

- [ ] Every normal product route and its controller/service source is classified.
- [ ] Every remaining runtime occurrence is attributable to neutral vocabulary or a verified false positive.
- [ ] Migrations receive a separate immutable-history classification.

## Phase 2 — Canonical Domain Map (`URE-02`)

| Surface | Canonical persisted source | Required access boundary |
| --- | --- | --- |
| Account identity | `User` | web guard, active state, configured verification |
| Social/profile identity | personal user-type `SocialActor` and `SocialActorSetting` | viewer-aware actor policy/access and account blocks |
| Onboarding | `UserOnboarding` | current authenticated user; forward-only optimistic state |
| Pets | `PetProfile`, `PetProfileManager`, access requests, privacy settings | `PetProfileAccess` and policies |
| Feed/posts/comments | `ContentPublication` and normalized engagement | publishing actor and audience policy |
| Neighbors/discovery | eligible `SocialActor`/`User` query | active/verified/discoverable/block scope before pagination |
| Messages | canonical conversation domain if present; otherwise no production route claiming unavailable persistence | participant-scoped authorization |
| Meetups | `ForumEvent`, registration/invitation/message/update relations | `ForumEventPolicy`, blocks, participation and location access |
| Groups | `ForumGroup`, memberships/invitations/content | `ForumGroupPolicy` and membership visibility |
| Places | `Place` and normalized child relations | `PlacePolicy` and public/exact-location separation |
| Notifications | real persisted lifecycle/domain notification records | authenticated recipient scope |

No second profile, pet, message, meetup, group, or notification domain will be introduced.

## Phase 3 — Registration And New-Account Baseline (`URE-03`)

Files: `app/Actions/RegisterUser.php`, auth Livewire form/component, account initialization Actions/services, factories, and registration feature tests.

- [ ] Add/retain a failing regression proving trimmed Unicode name/email normalization, exact returned user authentication, and session rotation.
- [ ] Prove the transaction produces exactly one User/actor/setting/onboarding and rolls back on mandatory initialization failure.
- [ ] Prove zero pets, managers, access requests, publications, conversations/messages, relationships, groups, meetups, and prototype state side effects.
- [ ] Prove `Registered` and verification behavior target only the created user after commit.
- [ ] Prove enabled/disabled verification destinations and onboarding precedence.

No schema migration is expected.

## Phase 4 — Onboarding (`URE-04`)

Files: onboarding Actions/models/Livewire/form objects/views and onboarding tests.

- [ ] New pet choice is unanswered; no named/default animal is selected.
- [ ] “No pet” and “Add later” complete successfully with zero pet/domain side effects.
- [ ] “Create a pet” navigates to the authorized canonical creation flow and inserts nothing before valid form submission.
- [ ] Logout/login resume and stale Livewire actions remain account-scoped.
- [ ] Deprecated prototype session namespaces are purged without removing locale, intended URL, CSRF, or authentication state.

## Phase 5 — Authenticated Identity And Self Profile (`URE-05`)

Files: `AuthenticatedUserPresenter`, `AppShell`, header/navigation components, `MemberProfileController`, `MemberProfileCatalog`, SocialActor access/policies, profile settings, and tests.

- [ ] Shell name is `User.name`; initials/avatar and accessible label belong to that user.
- [ ] Shell URL is `members.show` for that user's personal `SocialActor.actor_key`.
- [ ] Caller-supplied owner arrays, prototype state, translations, and global caches cannot override the web-guard principal.
- [ ] Self and arbitrary member views use one catalog with viewer-aware owner/private/public/block behavior.
- [ ] Profile facts and counts use persisted data or honest zero states.

## Phase 6 — Remove Runtime Prototype Entity Sources (`URE-06`)

Files include `PrototypeState`, `PreviewService`, `ProfilePresenter`, remaining preview controllers, static catalogues/presenters, legacy components/views, and their callers.

- [ ] Write architecture regressions that fail on current runtime named instances and production dependencies on prototype entity stores.
- [ ] Migrate one product surface at a time through red/green tests.
- [ ] Remove entity-default method arguments, literal action targets, static owner/pet/person arrays, and stock entity media.
- [ ] Delete a prototype class/view/controller only after `rg`, routes, views, tests, and container references prove zero canonical consumers.
- [ ] Retain `UserDomainState` only where it is an encrypted server-authoritative persisted compatibility record; never treat catalogue fixtures or session state as an entity database.

## Phase 7 — Database-Backed Production Surfaces (`URE-07`)

Each surface receives a factory-driven feature test with arbitrary names that do not occur in source, an empty-database test, privacy/authorization coverage, bounded ordering/pagination, and no query-in-Blade behavior.

- [ ] Feed/content: real `ContentPublication` rows and audiences; zero-content state.
- [ ] Neighbors/discovery: real eligible actors, discoverability/block filtering, search, stable pagination.
- [ ] Messages: real participant/conversation/message records if the canonical schema exists; otherwise remove/disable the false product surface with an honest localized unavailable/empty boundary until a separately specified canonical domain exists.
- [ ] Meetups: real `ForumEvent`/registrations/organizer/pets/capacity/location projections.
- [ ] Groups: real `ForumGroup`/memberships/content; remove literal group paths/allowlists.
- [ ] Pets: real authorized `PetProfile` records and canonical `profile_key` routes.
- [ ] Notifications: recipient-scoped persisted lifecycle/domain notifications only.
- [ ] Places, posts, comments, connections, pet friends, walks, compose/share/detail pages: real stable-key inputs and persisted records or honest empty states.

## Phase 8 — Demo Seed And Factory Isolation (`URE-08`)

Files: `DatabaseSeeder`, demo seeders, factories, `config/platform.php`, seeding tests and docs.

- [ ] Default/core seed orchestration creates reference data only; demo orchestration is explicit.
- [ ] Demo execution uses `platform.demo_seed_environments`, fails closed elsewhere, and is repeat-safe.
- [ ] Stable seed-owned identifiers detect collisions; display names, common email, ID 1, or first rows never select/adopt a real user.
- [ ] Default User factory callbacks create no pet/content/social graph; graph states are explicit and named.
- [ ] Demo records use canonical models, dynamic routes, and ordinary policies.

## Phase 9 — Legacy State And Existing Data Cleanup (`URE-09`)

- [ ] Enumerate session, cookie, localStorage, sessionStorage, Alpine, and cache keys containing prototype identity/entity state.
- [ ] Add a one-time versioned cleanup that removes only obsolete prototype keys during authentication transition.
- [ ] Add a dry-run operational command/script for existing demo rows using seed-owned stable identifiers. It enumerates relations, detects modified/ambiguous/link-to-real-data cases, and refuses deletion without explicit operator execution.
- [ ] Document backup, staging rehearsal, forward repair, rollback, and cache/session deployment steps.
- [ ] Do not auto-delete by name, email domain, row number, or first record.

## Phase 10 — Permanent Guardrails (`URE-10`)

Test files: architecture hardcode boundary, route contracts, runtime seed/factory imports, registration/onboarding zero baseline, arbitrary record rendering, rename, two-user isolation, locale parity, and privacy/IDOR.

Guardrails must:

- report file and token for known demo identifiers in production runtime;
- exclude only seeders, factories, tests, explicit fixtures, and classified history;
- forbid runtime calls/imports of seeders/factories while allowing model `HasFactory` type imports;
- reject literal person/pet/entity route instances and defaults;
- reject production route dependencies on prototype entity stores;
- reject first-user/first-pet identity fallbacks and entity-key default parameters;
- prove fresh migrations with no demo seed can register/onboard and render zero-data surfaces;
- prove arbitrary factory names render and canonical renames propagate without source edits.

## Phase 11 — Verification And Delivery (`URE-11`)

Focused serial order:

1. registration and configurable verification;
2. onboarding and zero-default-pets;
3. SocialActor provisioning and shell identity;
4. self/member/pet privacy and blocks;
5. feed, neighbors, messages, meetups, groups, places, notifications;
6. seed isolation and architecture guardrails;
7. EN/LT/RU, accessibility, browser storage, and query budgets.

Final commands:

```bash
composer validate --strict
composer audit --locked
composer check-platform-reqs
vendor/bin/pint
PAO_DISABLE=1 vendor/bin/phpstan analyse --memory-limit=1G
php scripts/run-tests.php --compact
php scripts/verify-fresh-database.php
npm audit --registry=https://registry.npmjs.org --audit-level=high
npm run build
php artisan route:cache
php artisan route:clear
php artisan view:cache
php artisan view:clear
git diff --check
```

Independent final review covers registration/transactions, onboarding/zero pets, shell identity, SocialActor/self profile, person hardcodes, pet hardcodes, prototype removal, routes/binding, demo seeds/factories, production directories, security/IDOR, localization/accessibility, performance/N+1, and test value. All attributable critical/high findings are fixed and affected checks rerun.

Publication uses a temporary selective index, complete staged-diff review, one coherent commit, a fresh `origin/main` comparison, and a normal `git push origin main`. Any material red gate leaves `URE-001` NO-GO with no commit or push.

