# Onboarding Audit Work Ledger

Date: 2026-08-30
Branch: `main`
Status: Prompt 01 foundation reviewed; repository release remains NO-GO

## Ownership And Safety Boundary

- The principal agent owns every edit, cross-module decision, test execution,
  plan update, staged path, commit, and push.
- Specialist agents are read-only. They may inspect code, documentation,
  routes, migrations, and tests, but may not edit files, run mutating commands,
  stage changes, commit, or push.
- The pre-existing shared-tree changes visible at audit start are unrelated and
  must remain intact. Onboarding-owned paths are tracked explicitly below.
- Final architecture, security, database, test, and regression review roles
  remain unassigned until the implementation diff is frozen. No specialist
  participates in implementation.

## Prompt 02 Persistent-State Revalidation

Prompt 02 starts from `main` at
`1ef8da9512d19bed29ef1fee84efbc07e1494cf5`. The existing
`user_onboardings` aggregate, migration, enums, factories, registration
initializer, optimistic Actions, middleware, and tests are current production
evidence and must not be duplicated in `users`. The principal owns every edit,
test, review disposition, staged path, commit, and push. Every specialist below
is read-only and must report exact file/line evidence without changing the
shared checkout or running a mutating database/Git command.

| ID | Specialist role | Exclusive Prompt 02 scope | Required deliverable | Status |
| --- | --- | --- | --- | --- |
| ONB-P02-A | State-machine designer | Existing onboarding plan, enums, aggregate, transition Actions, wizard consumption only | Canonical state/transition/entry/replay/backward-navigation/malformed-state recommendation and exact gaps | assigned wave 1 |
| ONB-P02-B | Database migration reviewer | `users` and `user_onboardings` migrations/schema/model/indexes plus SQLite/production portability only | Additive-schema, deployed-migration immutability, legacy compatibility, rollback/reapply and malformed-row risk report | assigned wave 1 |
| ONB-P02-C | Backward-compatibility reviewer | Existing/admin/demo/unverified/blocked/suspended accounts and default `UserFactory` assumptions only | Compatibility matrix and any way Prompt 02 could trap or reactivate a legacy account | assigned wave 1 |
| ONB-P02-D | Factory and seeder reviewer | `UserFactory`, `UserOnboardingFactory`, deterministic user/demo seeders and repeat-seed assumptions only | Valid-state matrix, impossible combinations, default-compatibility and repeat-seed findings | queued wave 2 |
| ONB-P02-E | Security reviewer | Direct Action misuse, foreign user, forged step/version/choice, replay, stale tabs, timestamps and malformed persistence only | Exploit-focused threat cases, severity, reproduction path and required regression tests | queued wave 2 |
| ONB-P02-F | Test reviewer | Existing onboarding/migration/factory/registration tests and safe isolated wrappers only | Requirement-to-test matrix, false positives, missing positive/negative/migration cases and exact focused commands | queued wave 2 |
| ONB-P02-G | Final independent reviewer | Frozen Prompt 02 attributable diff only; reviewer must not implement | Architecture/database/security/test/regression verdict and material findings with reproduction steps | unassigned until diff freeze |

Prompt 02 preserves the already-selected compatibility rule: an onboarding row
means the account participates in onboarding; a missing row is the explicit
legacy-complete exemption. A contrary recommendation must prove a current
security or data-integrity defect before the principal may replace that rule.

## Prompt 01 Current-Checkout Revalidation

The earlier audit described the pre-foundation checkout. Commit `0787b76`
subsequently added the onboarding foundation, so every earlier finding and
status is a historical lead until reproduced against the current `main`
checkout. The principal agent owns all writes. Each specialist below is
read-only and has an exclusive evidence scope.

| ID | Specialist role | Exclusive current-checkout scope | Required deliverable | Status |
| --- | --- | --- | --- | --- |
| ONB-V01 | Authentication and registration auditor | Registration, login, logout, reset-password entry and account/session destinations only | Current transition map, reuse inventory, defects with file/line and test evidence | completed on `4873014`; safe redirect, rate/delivery/session evidence gaps recorded |
| ONB-V02 | Email verification auditor | Verification configuration, notice, signed handler, resend, enabled/disabled destinations only | State/destination matrix, replay/rate-limit findings, exact evidence | completed on `4873014`; ordering correct, signed/mail/locale/recovery gaps recorded |
| ONB-V03 | User/profile domain auditor | User identity, profile preferences, locale/timezone, account social actor bootstrap only | Canonical identity inventory, mandatory/deferrable preference findings, exact evidence | completed on `4873014`; private bootstrap confirmed; stale-verification and seed gaps recorded |
| ONB-V04 | Pet-profile domain auditor | Pet creation, duplicate detection/review, manager grants and access requests only | Canonical reuse map, choice/evidence matrix, bypass findings | completed on `4873014`; canonical flow confirmed; owner-FK/revoked authority mismatch recorded |
| ONB-V05 | Authorization and middleware auditor | Middleware ordering, route guards, bindings, intended URL and direct mutation authorization only | Before/during/after access matrix and exploit-focused findings | completed on `4873014`; order confirmed; redirect parser and JSON gaps recorded |
| ONB-V06 | Database and migration auditor | Onboarding schema/model/factory/constraints, upgrade and rollback compatibility only | Schema correctness, concurrency/idempotency and migration-risk report | completed on `4873014`; additive design confirmed; seed/evidence/concurrency risks recorded |
| ONB-V07 | Livewire architecture auditor | Onboarding/auth component hydration, forms, actions and persistent middleware only | Public-state/tamper/replay analysis and v4 convention findings | completed on `4873014`; boundaries sound; stale guest/focus/real transport gaps recorded |
| ONB-V08 | UI/UX and accessibility auditor | Onboarding shell/view/styles, responsive keyboard/focus/error/offline semantics only | WCAG 2.2/mobile acceptance audit with exact evidence | completed on `4873014`; error/progress/forced-colors/loading gaps recorded |
| ONB-V09 | EN/LT/RU localization auditor | Onboarding/auth keys, placeholders, pluralization and locale transition only | Recursive parity and rendered-language findings | completed on `4873014`; structural parity confirmed; validation/mail/raw-key gaps recorded |
| ONB-V10 | Test architecture auditor | Onboarding/auth/pet/social test coverage and isolated runner only | Decision-to-test matrix, missing high-value negative cases and exact commands | completed on `4873014`; positive baseline strong; transport/concurrency/browser negatives missing |
| ONB-V11P | Privacy and discovery auditor | Account/pet/social privacy, indexing, exact location, medical/GPS boundaries | Safe defaults, canonical persistence, leakage and consent report | completed on `4873014`; account defaults private; private-pet actor leak reproduced by code path |
| ONB-V12T | Security threat reviewer | Onboarding trust boundaries, assets, attacker paths and mitigations | Stable threat IDs, priorities, tests and focus paths | completed on `4873014`; threat model written by principal |

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
| ONB-R01 | Independent architecture reviewer | Frozen onboarding-owned diff and abstraction boundaries | Duplication, state authority, Action/component/model boundary and ship assessment | completed; three material findings reproduced and remediated |
| ONB-R02 | Independent security reviewer | Frozen onboarding-owned diff plus affected trust boundaries | Exploit paths, verification, replay, privacy, IDOR and ship assessment | completed and repeated after remediation; pet bypasses closed, registration enumeration remains |
| ONB-R03 | Independent database reviewer | Frozen schema, transaction, factory and compatibility boundary | Migration/data safety, aggregate validity and rollback assessment | completed; factory/race findings fixed, demo no-row finding rejected as contrary to the selected compatibility rule |
| ONB-R04 | Independent test reviewer | Frozen implementation and test matrix | Missing negatives, false positives, transport/concurrency and migration evidence | completed; focused suites green, material mail/transport/concurrency gaps remain |
| ONB-R05 | Independent regression reviewer | Full frozen range and legacy/product adjacency | Existing-account, factory, pet authority and repository regression assessment | completed; no material task-owned runtime regression found, global release failures reproduced |

## Principal-Agent Evidence Checklist

- [x] Reproduce every material specialist finding in the current checkout.
- [x] Record the current-state route and state-transition map in the onboarding
      plan.
- [x] Update `docs/implementation-plan.md` before production-code changes.
- [x] Add behavior tests first and observe the expected RED failure.
- [x] Implement only the foundation requirements proven necessary by current
      architecture; do not claim the later prompt set is complete.
- [x] Freeze the onboarding-owned diff before the five independent reviews.
- [x] Record each review finding and its fixed, rejected-with-evidence, or
      deferred-out-of-scope disposition.
- [x] Do not create a task commit while material gates are red. Three external
      processes nevertheless committed and pushed the frozen candidates; the
      principal did not stage, commit, or push them and did not rewrite history.

## Onboarding-Owned Paths

This list is initialized before implementation and must be updated whenever the
principal agent takes ownership of another path:

- `docs/audits/onboarding-audit-work-ledger.md`
- `docs/audits/petsocial.miniserver.fun-threat-model.md`
- `docs/plans/onboarding-implementation-plan.md`
- onboarding-specific additions to `docs/implementation-plan.md`
- `CHANGELOG.md`
- `app/Actions/AdvanceUserOnboarding.php`
- `app/Actions/FollowSocialActor.php`
- `app/Actions/RegisterUser.php`
- `app/Actions/SendSocialRelationshipRequest.php`
- `app/Actions/CompleteOnboardingPreferences.php`
- `app/Actions/CompleteOnboardingPrivacy.php`
- `app/Actions/UpdatePetProfilePrivacy.php`
- `app/Actions/UpdateProfilePreferences.php`
- `app/Livewire/Auth/ConfirmPassword.php`
- `app/Livewire/Forms/Auth/RegistrationForm.php`
- `app/Livewire/Onboarding.php`
- `app/Livewire/ProfileSettings.php`
- `app/Models/PetProfile.php`
- `app/Services/EmailVerificationMode.php`
- `app/Services/SafeIntendedUrl.php`
- `app/Services/SocialActorAccess.php`
- `app/Services/SocialActorDirectory.php`
- `app/Services/SocialActorResolver.php`
- `database/factories/UserFactory.php`
- `lang/{en,lt,ru}/auth.php`
- `lang/{en,lt,ru}/onboarding.php`
- `resources/views/components/onboarding-layout.blade.php`
- `resources/views/livewire/onboarding.blade.php`
- `tests/Feature/Auth/AuthenticationTest.php`
- `tests/Feature/OnboardingTest.php`
- `tests/Feature/PetProfileFoundationTest.php`
- `tests/Feature/SocialRelationshipFoundationTest.php`

## Frozen Review And Publication Events

- Initial task baseline: `48730147fde586108bf79477dff066e5bb1b0ec5`.
- First frozen implementation diff SHA-256:
  `c3a3a750036290ce89116d2e5ccd033bf822316461b44b018da7b3a39b0ad32a`.
- While independent reviewers were reading that exact diff, an external
  process created and pushed `77a1c9cb384614d10db5d13c9c9504cbb3d45aff`.
- After the principal reproduced and fixed review findings, another external
  process created and pushed
  `8826415382d9d83968568ddd01b70f3129de3f70` before final gates completed.
- After focused Pint, an external process separately committed and pushed the
  two mechanical import/PHPDoc changes as
  `1ef8da9512d19bed29ef1fee84efbc07e1494cf5`.
- These events are observed repository history, not principal publication
  approval. History was not rewritten, reset, or force-pushed. The later
  documentation reconciliation remains unpublished while required repository
  gates are red.

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
| Stale configured-verification boundary | Accepted for Prompt 01: onboarding/profile components and direct transition Actions must freshly deny mutation when verification is enabled and the current account is unverified. |
| Intended URL parser agreement | Accepted for Prompt 01: reject backslashes and control bytes and reuse `SafeIntendedUrl` after password confirmation. |
| Revoked/expired pet creator evidence | Accepted for Prompt 01: the canonical managed query must implement the same legacy fallback as `PetProfileAccess`; onboarding will call that scope. |
| Private pet social actor leakage | Accepted for Prompt 01 minimum: new/lazy pet actor visibility and directory projection must be capped by the canonical pet profile visibility. |
| Privacy acknowledgement and wizard a11y | Accepted for Prompt 01: require an explicit acknowledgement, render actual errors, dispatch focus for ordinary validation, and correct progress/loading/forced-colors semantics. |
| UserFactory onboarding states | Accepted without changing defaults: named incomplete/preferences/pets/privacy/completed states compose `UserOnboardingFactory`; the default remains legacy-exempt to preserve unrelated tests. |
| Broader auth/localization/seed/browser gaps | Deferred but not hidden: verification-mail recovery/localization, LT/RU generic validation, reset limiter/session proof, cross-domain representative manifest drift, real concurrency, and a disposable onboarding browser runner remain release blockers or Prompt 02+ work unless completed and verified in this package. |

## Observed Baseline

Before the current production edits, the principal observed 101 onboarding and
auth tests with 718 assertions pass on `4873014`. The separately executed root
seed gate ran three tests: two passed and one failed because the 211-entry
representative manifest does not equal the current 263-model inventory. The
older 192-test/40,787-assertion run remains historical evidence only. Neither
focused result is a repository-wide gate claim.

## Prompt 01 TDD Evidence

- The first combined four-file run was terminated by external `SIGSEGV` before
  assertions, so it was not used as behavior evidence.
- Serial RED runs reproduced the selected defects: onboarding reported 15/23
  passing with five assertion failures and three errors; auth reported 30/33
  passing with two failures and one database-constraint error; pet foundation
  reported the revoked-owner failure plus the pre-existing 12-query budget
  drift; social foundation reported the private-pet actor failure.
- After the minimal remediation, serial focused runs passed: onboarding 24/24
  with 161 assertions, auth 33/33 with 270 assertions, pet foundation 15/15
  with 4,763 assertions, and social foundation 23/23 with 530 assertions.
- The public-pet query budget now proves exactly one cheap indexed
  `user_onboardings` boundary query and caps the complete page at 13 queries;
  the prior 12-query expectation predated central onboarding enforcement.
- Adjacent configurable-verification, portal-boundary, and localization files
  passed 10/10 (42 assertions), 42/42 (291 assertions), and 7/7 (37,641
  assertions). These are focused checks, not full-suite evidence.

## Independent Review Dispositions

| Review finding | Classification and disposition | Evidence after disposition |
| --- | --- | --- |
| Private pet actor could be targeted directly despite profile privacy | Valid HIGH; fixed in the canonical actor policy boundary, privacy synchronization, and post-lock target authorization | Directory plus direct follow/request negative tests pass; privacy flip synchronizes the existing actor |
| Active manager with explicit `deny:view` remained in `visibleTo()` | Valid MEDIUM; fixed without changing the independent `managedBy()` relationship fact | Pet foundation projection/access regression passes |
| Named onboarding factory states omitted the required private actor/settings aggregate | Valid MEDIUM; fixed by composing the canonical private runtime initializer while retaining the default legacy factory | Named-state aggregate assertions pass |
| Introduction acknowledgement was enforced only by Livewire | Valid MEDIUM; fixed in `AdvanceUserOnboarding`; replay remains idempotent | Direct Action denial and accepted/replayed transition assertions pass |
| Normalized database uniqueness race returned 500 | Valid MEDIUM; fixed by mapping only a confirmed email constraint conflict to localized validation and rolling back partial identity | Direct Action conflict test passes with unchanged user/onboarding/actor/settings counts |
| Duplicate registration disclosed the exact database/account reason | Partially fixed: raw/specific wording removed and rate limit retained; reviewer correctly found the success/error/auth/redirect outcomes still distinguish account existence | Deferred to the common registration-attempt/recovery design; security verdict remains NO-GO |
| Existing idempotent social replay returns before locked target reauthorization | Valid LOW defense-in-depth gap; no new mutation occurs and the source already owns the returned relationship/request | Deferred with stale-target/revoked-source replay tests required |
| Pet privacy now synchronizes actor visibility without calling `SocialGraphCache` invalidation | LOW non-material future-cache concern; no current reader of the affected social cache keys was found | Defer until cached social projections are enabled; unify invalidation and add a projection-version regression first |
| Synchronous verification notification can fail after account commit | Valid HIGH availability/recovery gap, pre-existing and already recorded in the plan | Deferred to a recoverable delivery UX plus fault-injection test; release remains NO-GO |
| Real Livewire update, two-connection concurrency, full signed-link, populated migration, and browser/a11y matrices are incomplete | Valid evidence gaps | Deferred to ONB-08; no production-grade completion claim |
| Demo/root seeder should create onboarding rows | Rejected for this package: existing demo accounts are legacy users by the selected compatibility contract; an incomplete row would trap them and a completed row would add redundant state | Default factory and missing-row middleware compatibility tests remain authoritative; representative seed drift is separately red |

## Final Gate Evidence

- Canonical isolated focused run: onboarding 25/25 (169 assertions), auth
  33/33 (272), pet 16/16 (4,768), social 23/23 (532), configurable
  verification 10/10 (42), portal boundary 42/42 (291), and localization 7/7
  (37,644): **156 tests, 43,718 assertions, exit 0**.
- Post-format affected rerun: onboarding plus pet **41 tests, 4,937
  assertions, exit 0**.
- Focused Pint on every task-owned PHP file: exit 0 after two mechanical
  import/PHPDoc fixes. Focused Larastan on both production-code groups: zero
  errors. Explicitly passing Pest files to Larastan is outside the configured
  analysis boundary and produced 167 `Pest\\PendingCalls\\TestCall` typing
  errors; no suppression or test weakening was added.
- Full isolated Pest: **2,917 tests, 2,767 passed, 39 failed, 132,877
  assertions, exit 2**. Failures are global generated database/repository/forum
  evidence drift, missing Place/Event/Portal classes/factories/routes, seeding,
  and unrelated localization/component defects; the focused onboarding matrix
  remained green.
- Composer validation/audit/platform, npm high audit/build, migration cycle,
  fresh database/seed, and route/view cache smokes passed earlier on the first
  frozen candidate. Full Pint, full Larastan, database-domain generation, and
  seeding coverage remain red from current cross-domain repository drift.
- Connected onboarding browser proof was not run because no disposable
  onboarding wrapper exists; the standalone pet browser script is mutation
  capable and forbidden against the configured database.
