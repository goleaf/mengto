# Authenticated Identity Refactor Work Ledger

## Control Record

- Work item: `AIR-001`
- Branch: `main`
- Starting HEAD: `24c9b513c7b06114e416dd6007b18d41d3ca3e61`
- Starting `origin/main`: `24c9b513c7b06114e416dd6007b18d41d3ca3e61`
- Principal owner: primary Codex agent; all repository writes remain principal-owned.
- Scope: registration, authenticated identity, personal `SocialActor`, header, self-profile routing and presentation, onboarding consistency, demo-data isolation, localization, tests, security review, documentation, verification, commit, and push.
- Delivery rule: preserve all pre-existing staged, unstaged, and untracked forum/meetup work; use an isolated temporary Git index for task-owned staging.

## Confirmed Defect To Reproduce

A registered and authenticated account can persist its submitted `users.name` while the authenticated presentation path replaces that identity with the translated prototype owner `Mia Carter`. The normal authenticated profile link may also target the demo-specific `profile.mia` route. Both hypotheses must be reproduced on current `main` before implementation.

## Target Invariants

- `users.id`, `users.actor_key`, `users.name`, `users.email`, `users.locale`, `users.timezone`, `users.status`, and `users.email_verified_at` are the canonical account facts.
- A successful registration atomically provisions the exact `User`, one personal user-type `SocialActor`, privacy-first `SocialActorSetting`, and `UserOnboarding`.
- The Livewire registration boundary authenticates the exact `RegisterUserResult::user` and regenerates the session.
- Authenticated header and self-profile presentation receive an explicit current `User`; translations and `PrototypeState` cannot supply runtime identity.
- Self-profile routing resolves the current user's stable personal `SocialActor.actor_key`; demo compatibility routes never serve as the normal self route.
- Optional Mia data remains isolated as a deliberate demo fixture only.

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
| Governance and Git baseline | Complete | Starting branch and SHAs recorded; unrelated dirty work classified as pre-existing. |
| Specialist audit waves | In progress | `AIR-A`, `AIR-B`, and `AIR-C` complete; `AIR-D` through `AIR-F` active; `AIR-G` through `AIR-J` queued. |
| Root-cause reproduction | In progress | Static current-main trace confirms persisted `User` is replaced by translated prototype identity and the header links `profile.mia`; focused RED request evidence remains required. |
| Canonical plan update | Complete | Active `AIR-001` architecture, affected boundaries, tests, migration decision, rollback, and ordered delivery IDs recorded in `docs/implementation-plan.md` before production code. |
| TDD implementation | Pending | Red/green evidence required for each behavior slice. |
| Independent reviews | Pending | Registration, authentication, identity, SocialActor, profile, demo isolation, security, localization, and test-value reviews. |
| Quality gates | Pending | All user-specified PHP, database, Composer, frontend, cache, diff, and runtime checks. |
| Delivery | Pending | Task-owned isolated-index commit and observed `origin/main` push. |

## Rollback

Rollback is the single task-owned commit produced by this ledger. No historical migration or dependency change is planned. If runtime verification fails after publication, revert only that commit and preserve all unrelated shared-tree work.
