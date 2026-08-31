# Authenticated Identity Refactor Work Ledger

## Control Record

- Work item: `AIR-001`
- Branch: `main`
- Starting HEAD: `d92cf5e74593a0a79b23c721ffc874fb76eba3d2`
- Starting `origin/main`: `d92cf5e74593a0a79b23c721ffc874fb76eba3d2`
- Principal owner: primary Codex agent; all repository writes remain principal-owned.
- Scope: registration, authenticated identity, personal `SocialActor`, header, self-profile routing and presentation, onboarding consistency, demo-data isolation, localization, tests, security review, documentation, verification, commit, and push.
- Delivery rule: the starting tree was clean; preserve any later concurrent or
  unrelated work and use an isolated temporary Git index for task-owned
  staging if the tree ceases to be exclusively attributable.

## Confirmed Defect To Reproduce

A registered and authenticated account can persist its submitted `users.name` while the authenticated presentation path replaces that identity with the translated prototype owner `Mia Carter`. The normal authenticated profile link may also target the demo-specific `profile.mia` route. Both hypotheses must be reproduced on current `main` before implementation.

## Target Invariants

- `users.id`, `users.actor_key`, `users.name`, `users.email`, `users.locale`, `users.timezone`, `users.status`, and `users.email_verified_at` are the canonical account facts.
- A successful registration atomically provisions the exact `User`, one personal user-type `SocialActor`, privacy-first `SocialActorSetting`, and `UserOnboarding`.
- The Livewire registration boundary authenticates the exact `RegisterUserResult::user` and regenerates the session.
- Authenticated header and self-profile presentation receive an explicit current `User`; translations and `PrototypeState` cannot supply runtime identity.
- Self-profile routing resolves the current user's stable personal `SocialActor.actor_key`; demo compatibility routes never serve as the normal self route.
- Optional Mia data remains isolated as a deliberate demo fixture only.

## Expanded Universal Account And Zero-Pets Decision

- The compatibility exception is removed. Production routes, controllers,
  presenters, views, actions, translations, and defaults may not encode Mia,
  Scout, Nori, or a parallel owner identity.
- Optional demo records are ordinary canonical rows created only by explicit
  environment-gated seeders and reached only through dynamic member/pet routes.
- Registration and onboarding must preserve exactly one personal actor,
  setting, and onboarding record while creating zero pets, pet managers, and
  pet-access requests.
- No-pet and add-later onboarding are valid zero-pet outcomes. Explicit pet
  creation is verified separately.
- Current-user pages use persisted domain facts or localized empty states;
  per-user PrototypeState storage cannot manufacture ownership, authorship,
  relationships, messages, notifications, counts, or identity.
- Specialist coverage expands through AIR-L: onboarding, pet lifecycle,
  seeders, dead code, and a frozen final independent review join the original
  registration/auth/identity/profile/security/localization/test audits.

This scope reconciliation supersedes the earlier ledger sentence permitting a
Mia compatibility fixture. Historical documents may retain the old decision as
history; current production code may not.

## Read-Only Specialist Assignments

| ID | Exclusive audit scope | Deliverable |
| --- | --- | --- |
| `AIR-A` | `RegistrationForm`, `Register`, `RegisterUser`, `RegisterUserResult` | Normalization, validation, transaction, persistence, event, authentication, session, and rollback evidence with file:line references. |
| `AIR-B` | `Login`, `Logout`, `VerifyEmail`, `AccountEntryDestination`, `RequirePortalAccess`, `SafeIntendedUrl` | Exact post-registration/login/verification/onboarding routing map and defects. |
| `AIR-C` | `User`, users migrations, `UserFactory`, user seeders | Canonical identity and provisioning invariants; first-user/demo fallback findings. |
| `AIR-D` | `SocialActor`, resolver, settings, member profile controller/catalog | Personal actor cardinality, privacy defaults, binding, self/public routing, IDOR/block behavior. |
| `AIR-E` | `ProfilePresenter`, `PrototypeState`, preview controller, profile/member views | Every static/demo owner assumption and recommended cohesive presentation boundary. |
| `AIR-F` | `AppShell`, `SiteHeader`, `HeaderActions`, `PrimaryNavigation`, authenticated shell call sites | Header identity and link sources across authenticated and guest pages. |
| `AIR-G` | Repository-wide Mia/prototype/demo semantic search | Classification as intentional fixture, compatibility route, or production identity leak. |
| `AIR-H` | EN/LT/RU identity and accessibility copy | Person-specific keys, safe neutral/parameterized replacements, placeholder parity, accessible-name findings. |
| `AIR-I` | Session identity, Livewire, IDOR, route binding, mass assignment, verification/onboarding, cache/privacy/block boundaries | Security findings with reproduction and recommended tests. |
| `AIR-J` | Existing registration/auth/profile/navigation/localization/security tests | Coverage map and minimal tests that fail on the old implementation. |

## Specialist Contract

Every specialist is read-only. Each report must contain: inspected paths, observed data flow, confirmed findings, non-findings worth preserving, exact file:line evidence, test gaps, and risks. Specialists must not edit, stage, commit, or run mutation-capable commands.

## Execution Ledger

| Phase | Status | Evidence |
| --- | --- | --- |
| Governance and Git baseline | Complete | Clean starting `main`; `HEAD` and `origin/main` both observed at `d92cf5e74593a0a79b23c721ffc874fb76eba3d2`. |
| Specialist audit waves | Complete | Initial registration/auth/identity/profile/navigation/demo/locale/security/test audits plus final registration, transaction, zero-pet, shell, SocialActor, demo, privacy, localization, dead-code and test-quality reviews were run read-only; principal reproduced and dispositioned findings. |
| Root-cause reproduction | Complete | Registration trace confirms exact-user persistence/authentication. Isolated shell tests pass for the baseline partial fix. `MemberProfileControllerTest` reproduced missing owner-private data/verification presentation and privacy `403` instead of required `404`; combined wrapper also intermittently exited with host signal 11. |
| Canonical plan update | Complete | Active `AIR-001` architecture, affected boundaries, tests, migration decision, rollback, and ordered delivery IDs recorded in `docs/implementation-plan.md` before production code. |
| TDD implementation | Complete | Exact-user/rollback/verification tests, zero-pet onboarding, Alice/Bob/logout isolation, canonical member privacy/block facts, locale matrix, demo-seeder isolation, honest share/photo fixtures, dynamic browser-contract and forbidden-hardcode regressions are implemented; the current focused slice passes 430 tests / 3,443 assertions. |
| Independent reviews | Complete | Nine final read-only reviewers inspected the frozen implementation. Reproduced seeder contamination, stale Mia browser navigation, misleading privacy copy, duplicated onboarding announcements, incomplete self-profile browser accessibility, missing HTTP fresh verification and dead profile component findings were fixed. The released migration exception is explicitly retained under migration immutability and blocks publication pending a production-data audit/forward repair decision. |
| Quality gates | NO-GO | Focused tests, targeted Larastan, the unseeded fresh verifier, Composer, npm/build, route/view cache and the connected canonical-identity browser pass. The final-tree full Pest retry with CLI opcache disabled completed at 3,224 total / 3,027 passed / 55 failed / 142 errors after the ordinary wrapper hit host signal 11; full Larastan remains red with 49 unrelated unfinished-module findings. The broader page-identity browser and generated database-audit parity are not green. |
| Delivery | Blocked | No commit or push is permitted while material repository gates remain red. Starting HEAD and `origin/main` remain unchanged. |

## Final Finding Dispositions

- Registration already persisted the submitted normalized name and returned the
  exact created `User`; the transaction now permanently protects one personal
  actor, one privacy-first setting, one onboarding row and zero default pets.
- `AppShell` alone derives authenticated chrome from the guard and
  `AuthenticatedUserPresenter`. Sixty-six Blade shell-owner attributes and
  twelve Livewire layout owner injections were removed; caller arrays cannot
  replace the principal.
- The canonical self page is `members.show` for the current personal
  `SocialActor.actor_key`. Owner/private, unrelated-viewer, verification and
  both block directions remain viewer-aware and fail closed.
- `RepresentativeDomainSeeder` now scopes reused user-owned pools to guarded
  demo accounts. Repeat seeding cannot attach pets, event registrations,
  mentorship validation, trust grants or moderation linkage to a pre-existing
  real account.
- The executable accessibility journey no longer navigates Mia or the deleted
  owner/neighbor preview pages. It reads the current header profile URL,
  verifies the same name and stable dynamic member route across locales and
  viewports, then switches to an explicit testing-only Andrej account and
  verifies exact header/profile ownership plus profile focus, target-size,
  clipping, forced-colors and reduced-motion contracts.
- The dedicated connected canonical-identity journey now passes for Andrej's
  fixed personal actor URL, 0-pet/0-post facts, dynamic profile accessible
  name, keyboard focus, 375-pixel layout, forced colors, reduced motion,
  logout identity removal and zero console/resource errors. The broader
  page-identity matrix remains independently red at unfinished event routes.
- The obsolete profile hero/identity/badge component island and its dedicated
  hero styles were removed after call-site and fixture searches proved it
  unreachable.
- The isolated database-domain audit intentionally retains its deterministic
  seeded Mia identity as demo-seed evidence. It is not a runtime dependency;
  its generated output cannot be rewritten while the unrelated persistent
  model manifest is already incomplete. The historical released migration
  containing a legacy Mia backfill remains unchanged under the migration
  immutability rule. A migration architecture guard names that sole released
  exception and forbids new migration hardcodes. Publication remains blocked
  until production data is audited; an affected non-demo row requires a
  separately reviewed forward compatibility repair rather than history edits.

## Rollback

Rollback is the single task-owned commit produced by this ledger. No historical migration or dependency change is planned. If runtime verification fails after publication, revert only that commit and preserve all unrelated shared-tree work.
