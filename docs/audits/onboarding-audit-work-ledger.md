# Onboarding Audit Work Ledger

Date: 2026-08-30  
Branch: `main`  
Status: Prompt 01 current-checkout revalidation in progress; foundation exists in `main`

## Ownership And Safety Boundary

- The principal agent owns every edit, cross-module decision, test execution,
  plan update, staged path, commit, and push.
- Specialist agents are read-only. They may inspect code, documentation,
  routes, migrations, and tests, but may not edit files, run mutating commands,
  stage changes, commit, or push.
- The pre-existing shared-tree changes visible at audit start are unrelated and
  must remain intact. Onboarding-owned paths are tracked explicitly below.
- Review roles 11 and 12 remain unassigned until the implementation diff is
  frozen; neither reviewer may participate in implementation.

## Prompt 01 Current-Checkout Revalidation

The earlier audit described the pre-foundation checkout. Commit `0787b76`
subsequently added the onboarding foundation, so every earlier finding and
status is a historical lead until reproduced against the current `main`
checkout. The principal agent owns all writes. Each specialist below is
read-only and has an exclusive evidence scope.

| ID | Specialist role | Exclusive current-checkout scope | Required deliverable | Status |
| --- | --- | --- | --- | --- |
| ONB-V01 | Authentication and registration auditor | Registration, login, logout, reset-password entry and account/session destinations only | Current transition map, reuse inventory, defects with file/line and test evidence | assigned |
| ONB-V02 | Email verification auditor | Verification configuration, notice, signed handler, resend, enabled/disabled destinations only | State/destination matrix, replay/rate-limit findings, exact evidence | assigned |
| ONB-V03 | User/profile domain auditor | User identity, profile preferences, locale/timezone, account social actor bootstrap only | Canonical identity inventory, mandatory/deferrable preference findings, exact evidence | assigned |
| ONB-V04 | Pet-profile domain auditor | Pet creation, duplicate detection/review, manager grants and access requests only | Canonical reuse map, choice/evidence matrix, bypass findings | assigned |
| ONB-V05 | Authorization and middleware auditor | Middleware ordering, route guards, bindings, intended URL and direct mutation authorization only | Before/during/after access matrix and exploit-focused findings | assigned |
| ONB-V06 | Database and migration auditor | Onboarding schema/model/factory/constraints, upgrade and rollback compatibility only | Schema correctness, concurrency/idempotency and migration-risk report | assigned |
| ONB-V07 | Livewire architecture auditor | Onboarding/auth component hydration, forms, actions and persistent middleware only | Public-state/tamper/replay analysis and v4 convention findings | assigned |
| ONB-V08 | UI/UX and accessibility auditor | Onboarding shell/view/styles, responsive keyboard/focus/error/offline semantics only | WCAG 2.2/mobile acceptance audit with exact evidence | assigned |
| ONB-V09 | EN/LT/RU localization auditor | Onboarding/auth keys, placeholders, pluralization and locale transition only | Recursive parity and rendered-language findings | assigned |
| ONB-V10 | Test architecture auditor | Onboarding/auth/pet/social test coverage and isolated runner only | Decision-to-test matrix, missing high-value negative cases and exact commands | assigned |
| ONB-V11 | Independent security reviewer | Frozen Prompt 01 attributable diff and affected trust boundaries only | Severity-ranked findings, exploit path, reproduction and disposition | reserved until diff freeze |
| ONB-V12 | Final independent code reviewer | Frozen diff, updated plans and acceptance criteria only | Requirement-by-requirement verdict and ship/no-ship assessment | reserved until diff freeze |

## Specialist Assignments

| ID | Role | Exclusive audit scope | Structured deliverable | Status |
| --- | --- | --- | --- | --- |
| ONB-A01 | Authentication and registration auditor | Guest auth routes, registration/login/logout components, forms, Actions, session regeneration and redirects | Entry-point map; post-register/login/logout behavior; reusable boundaries; defects with file/line evidence | completed; no durable onboarding, registration does not preserve intended destination, and mutation throttling/stale Livewire guest-state checks need coverage |
| ONB-A02 | Email verification auditor | Verification mode, notices, signed verification handler, verified middleware, resend behavior, auth tests | Enabled/disabled state machine; post-verification redirect; rate-limit and replay findings; exact evidence | completed; ordinary verification redirect loses intended destination and the resend mutation has no dedicated limiter |
| ONB-A03 | User/profile domain auditor | `User`, profile settings, account status, locale/timezone storage, factories/seeders, social identity bootstrap | Canonical account identity inventory; required preferences; compatibility findings; reusable operations | completed; registration creates `User` identity but not its normalized `SocialActor`/settings; existing profile and social preference Actions are reusable |
| ONB-A04 | Pet-profile domain auditor | Pet creation, management, duplicate review, access requests, manager/privacy/discovery defaults | Existing pet flow and invariants; required/optional onboarding relationship choices; reuse map; exact evidence | completed; pet creation is optional and must stay in the canonical duplicate-aware component; non-owner role redirects require policy-aware handling |
| ONB-A05 | Authorization and middleware auditor | Middleware order/aliases, portal guard, route-model binding order, intended URL, policies and direct Livewire authorization | Access matrix before/during/after onboarding; bypass risks; middleware placement recommendation | completed; onboarding must run after portal access and before bindings, preserve only GET/HEAD intended URLs, fail closed for mutations/JSON, and reauthorize every Livewire transition |
| ONB-A06 | Database and migration auditor | User/pet/social/privacy/discovery schema, constraints, casts, migration ordering, upgrade/rollback | Minimal additive schema recommendation; backfill policy; SQLite-safe constraints; rollback risks | completed; a normalized one-to-one additive table is required; canonical account/pet/social values must not be copied into it |
| ONB-A07 | Livewire architecture auditor | Class components, form objects, route hosts, hydration state, loading/dirty/offline/error patterns | Reusable component conventions; proposed component/form boundaries; tamper/replay risks | completed; each named mutation needs locked expected-step/state-version snapshots plus fresh server authorization and optimistic transition checks |
| ONB-A08 | UI/UX and accessibility auditor | Auth/profile/pet UI, shared components, mobile layout, keyboard/focus/error semantics, SCSS/Tailwind tokens | Step UX recommendation; WCAG 2.2 acceptance checklist; reuse map; exact evidence | completed; a dedicated authenticated account-flow shell and noninteractive semantic stepper avoid blocked portal chrome and misleading auth controls |
| ONB-A09 | EN/LT/RU localization auditor | Auth/profile/pet catalogues, locale middleware/session persistence, key and placeholder parity | Catalogue plan; missing concepts; language-switch timing; parity/test requirements | completed; dedicated recursive catalogues are required and locale changes must redirect or set the application locale before rendering |
| ONB-A10 | Test architecture auditor | Auth/profile/pet/middleware/database/architecture/browser test harness and isolation wrappers | TDD test matrix; focused commands; high-value negative/replay/compatibility cases | completed; P0 contracts cover atomic registration bootstrap, state/replay, pre-binding access, destination chains and auth mutation hardening before implementation |
| ONB-R11 | Independent security reviewer | Frozen onboarding-owned diff only, plus affected trust boundaries | Severity-ranked findings with exploit path, evidence, and disposition recommendation | reserved |
| ONB-R12 | Final independent code reviewer | Frozen onboarding-owned diff and final plan/acceptance criteria | Requirements/code/test review with critical/important/minor findings and ship assessment | reserved |

## Principal-Agent Evidence Checklist

- [x] Reproduce every material specialist finding in the current checkout.
- [x] Record the current-state route and state-transition map in the onboarding
      plan.
- [x] Update `docs/implementation-plan.md` before production-code changes.
- [ ] Add behavior tests first and observe the expected RED failure.
- [ ] Implement only the foundation requirements proven necessary by current
      architecture; do not claim the later prompt set is complete.
- [ ] Freeze the onboarding-owned diff before ONB-R11 and ONB-R12 review.
- [ ] Record each review finding and its fixed, rejected-with-evidence, or
      deferred-out-of-scope disposition.
- [ ] Use a temporary `GIT_INDEX_FILE` if an attributable commit becomes safe.

## Onboarding-Owned Paths

This list is initialized before implementation and must be updated whenever the
principal agent takes ownership of another path:

- `docs/audits/onboarding-audit-work-ledger.md`
- `docs/plans/onboarding-implementation-plan.md`
- onboarding-specific additions to `docs/implementation-plan.md`

## Audit Dispositions Before Implementation

| Finding | Disposition |
| --- | --- |
| Persisted state and legacy compatibility | Accepted: use one additive `user_onboardings` row per newly registered user; no row remains the explicit legacy exemption. The proposed cutoff/backfill command is rejected for this foundation because registration initializes atomically and a backfill would add rollout state without improving authorization. |
| Canonical social identity gap | Accepted: provision one user actor and settings row inside registration with explicit private defaults; do not alter the resolver's legacy lazy defaults or rewrite existing settings. |
| Intended destination loss | Accepted: login/register/verification/onboarding share one destination order and consume an internal intended URL only after all gates pass. POST/JSON/Livewire transport never overwrites it. |
| Pet workflow integration | Accepted with a narrow route bridge: onboarding never calls the pet Action directly and exposes no pet key. The exact canonical create route remains available; its manage redirect is intercepted back to onboarding, where active manager or pending-request evidence is checked. Generic portal pet routes remain blocked. |
| Generic Livewire upload concern | Accepted as a transport constraint: temporary upload/preview endpoints are not product permissions. They pass only as framework transport for an originally allowed canonical pet-create snapshot; all persisted media mutations remain component- and policy-authorized. Connected HTTP coverage is required. |
| Pet non-owner redirect defect | Reproduced and recorded, but no permission is broadened. During incomplete onboarding the central gate intercepts the manage redirect before binding and returns to onboarding. The general post-completion redirect defect is outside this foundation and remains a separate pet-workspace concern. |
| Stale guest/onboarding snapshots | Accepted: named mutations re-check session state; onboarding publishes locked expected-step and version snapshots, then Actions reload, authorize, lock, compare, and make exact replay a no-op. |
| Shell and accessibility | Accepted: use a dedicated authenticated account-flow layout and noninteractive ordered progress, not portal chrome or the guest auth story/locale controls. |
| Locale transition | Accepted: preference success stores user/session locale and redirects before rendering the next step so no mixed-language Livewire response is emitted. |
| Registration/resend throttling | Accepted as an affected auth boundary: five registrations per IP per minute and three verification resends per user-and-IP per minute, with localized failure and no side effects. |

## Observed Baseline

Before production edits, the serial isolated regression command covering auth,
portal access, intended routing, pet duplicate/access requests, social settings,
localization, and page identity passed 192 tests and 40,787 assertions on
2026-08-30. This is a focused baseline, not a repository-wide gate claim.
