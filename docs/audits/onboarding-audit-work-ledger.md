# Onboarding Audit Work Ledger

Date: 2026-08-30
Branch: `main`
Status: Prompt 03 authentication boundary implemented and focused-verified;
repository release remains NO-GO

## Ownership And Safety Boundary

- The principal agent owns every edit, cross-module decision, test execution,
  plan update, staged path, commit, and push.
- Specialist agents are read-only. They may inspect code, documentation,
  routes, migrations, and tests, but may not edit files, run mutating commands,
  stage changes, commit, or push.
- The pre-existing shared-tree changes visible at audit start are unrelated and
  must remain intact. Onboarding-owned paths are tracked explicitly below.
- Final architecture, security, database, test, and regression review roles
  were assigned only after each implementation diff was frozen. No specialist
  participated in implementation; their completed dispositions are recorded
  below.

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
| ONB-P02-A | State-machine designer | Existing onboarding plan, enums, aggregate, transition Actions, wizard consumption only | Canonical state/transition/entry/replay/backward-navigation/malformed-state recommendation and exact gaps | completed read-only; prerequisite, malformed and replay gaps accepted |
| ONB-P02-B | Database migration reviewer | `users` and `user_onboardings` migrations/schema/model/indexes plus SQLite/production portability only | Additive-schema, deployed-migration immutability, legacy compatibility, rollback/reapply and malformed-row risk report | completed read-only; no schema edit or extra index justified |
| ONB-P02-C | Backward-compatibility reviewer | Existing/admin/demo/unverified/blocked/suspended accounts and default `UserFactory` assumptions only | Compatibility matrix and any way Prompt 02 could trap or reactivate a legacy account | completed read-only; no-row legacy exemption retained |
| ONB-P02-D | Factory and seeder reviewer | `UserFactory`, `UserOnboardingFactory`, deterministic user/demo seeders and repeat-seed assumptions only | Valid-state matrix, impossible combinations, default-compatibility and repeat-seed findings | completed read-only; generic evidence-free pet-choice parameters removed; global manifest drift retained as blocker |
| ONB-P02-E | Security reviewer | Direct Action misuse, foreign user, forged step/version/choice, replay, stale tabs, timestamps and malformed persistence only | Exploit-focused threat cases, severity, reproduction path and required regression tests | completed read-only; fail-open completion, stale evidence and non-equivalent replay fixed |
| ONB-P02-F | Test reviewer | Existing onboarding/migration/factory/registration tests and safe isolated wrappers only | Requirement-to-test matrix, false positives, missing positive/negative/migration cases and exact focused commands | completed read-only; false replay/stale contracts and populated migration gap fixed |
| ONB-P02-G | Final independent reviewers | Frozen Prompt 02 attributable diff only; reviewers must not implement | Architecture/database/security/test/regression verdict and material findings with reproduction steps | completed; three independent reviewers returned SHIP for 24-path SHA-256 `10a17725230fc1316ba4b64a9b0ba8d5f94bda687e829ffd9be63644117279d4`; all findings disposed |

Prompt 02 preserves the already-selected compatibility rule: an onboarding row
means the account participates in onboarding; a missing row is the explicit
legacy-complete exemption. A contrary recommendation must prove a current
security or data-integrity defect before the principal may replace that rule.

### Prompt 02 Finding Dispositions

| Finding | Classification and disposition | Evidence |
| --- | --- | --- |
| Valid `complete` rows with missing checkpoints or malformed enum/lock values could pass or crash readers | Valid HIGH defense boundary; added raw enum-safe `OnboardingState`, strengthened model completion truth, and routed middleware/destination/Livewire/Actions through the guarded facts | malformed HTTP/render, direct recovery, missing-prerequisite dataset and invalid-lock tests pass |
| Completion trusted pet evidence captured at the prior step | Valid HIGH workflow-integrity gap; one bounded checker now runs at selection and again inside completion | revoked manager, expired pending request, rejected/cancelled/expired requests, approved active grant and completion recheck tests pass |
| Later-state early returns accepted changed payloads or arbitrary stale versions | Valid MEDIUM concurrency/idempotency gap; replay now requires the immediate successor, expected version + 1, and equivalent persisted payload | introduction/preferences/pet/privacy exact and conflicting replay tests pass; original timestamps and settings remain unchanged |
| Parameterized factory states could claim managed/requested evidence without creating it | Valid MEDIUM test-data defect; generic states now deliberately represent only `not-now`; evidence-dependent tests must build canonical relations | factory lifecycle test asserts exact steps, timestamps and locks; no call site required removed parameters |
| Existing migration-cycle wrapper did not prove upgrade behavior on populated data | Valid HIGH evidence gap; added a temporary SQLite verifier that migrates to the predecessor, inserts active/unverified/blocked/suspended/admin users, applies/down/applies the package migration and compares captured identity/account fields | five users preserved, zero onboarding rows, all three migration exits 0 |
| Add a second migration or onboarding columns to `users` | Rejected: the deployed normalized table already holds the minimum queryable lifecycle state; duplicate storage would create split authority | schema inventory and migration reviewers found no missing field or justified index |
| Bulk-create complete rows for legacy/demo accounts | Rejected: missing row is the explicit compatibility exemption; backfill would add redundant state and could trap existing users | legacy helper/portal tests and populated migration verifier preserve no-row accounts; verification and availability remain independent |
| Under-versioned or malformed timestamp rows could still present as complete | Valid HIGH first-freeze finding; strict raw timestamp parsing and step-position lock invariants now fail closed | unit and HTTP regressions cover under-versioned complete and malformed stored timestamps |
| Preference replay compared a stale caller model | Valid MEDIUM first-freeze finding; replay reads persisted locale/timezone inside the locked transition | exact stale-model replay passes and changed payload still conflicts |
| Privacy replay provisioned missing social identity | Valid MEDIUM first-freeze finding; completion/replay now locks and reads an existing canonical actor/settings pair and never provisions it | direct completed-row replay fails closed with zero actor/settings side effects |
| Authoritative aggregate allowed mass assignment | Valid MEDIUM first-freeze finding; `UserOnboarding` guards every attribute and the initializer uses controlled `insertOrIgnore` | mass-assignment rejection plus registration/initializer idempotency tests pass |
| Generic Livewire update allowlist admitted stale product snapshots | Valid HIGH first-freeze finding; signed original-route persistent middleware now aborts incomplete product hydration with `409` | real HTTP Livewire snapshot regression preserves locale/timezone without mutation |
| Completion evidence check was not locked against revoke/review | Valid MEDIUM first-freeze finding; the completion transaction locks the qualifying manager/request or legacy pet row before settings/completion writes | sequential lifecycle regressions pass; production locking order independently reviewed, while true two-connection proof remains later evidence |
| Expired request button disagreed with the domain checker | Valid MEDIUM first-freeze finding; the computed UI fact delegates to the same evidence service | Livewire render regression hides expired access-request continuation |
| Package verifier selected its temporary database after application bootstrap | Valid MEDIUM evidence finding; random SQLite path and environment are now established before bootstrap | package apply/down/up verifier exits 0 and cleans only its random temp file |
| Invalidated pet evidence dead-ended an account at privacy | Valid HIGH re-review finding; a dedicated owner-scoped versioned Action replaces the choice with `not-now` without rewinding privacy | revoked-manager refusal, recovery, exact replay, stale rejection, completion and Livewire recovery tests pass |
| Onboarding GET repaired missing social identity with public defaults | Valid MEDIUM re-review finding; UI repair now provisions canonical private actor/settings while direct completion remains side-effect-free | incomplete missing-identity HTTP regression asserts discoverability, both request policies, both list visibilities, recommendations and message requests are private |
| Malformed `started_at` resolved to an unrendered complete step | Valid MEDIUM re-review finding; resolver returns introduction and guarded acknowledgement repairs start while clearing contradictory future checkpoints | unit plus HTTP/direct transition regressions pass |
| Locked evidence could still reference a soft-deleted pet | Valid MEDIUM re-review finding; completion locks a non-deleted profile before request/manager evidence in canonical profile-request-manager order | soft-deleted managed-pet completion regression fails closed |
| Pet deferral recovery lacked complete negative authorization/state evidence | Valid MEDIUM test-review gap; retained the production guard and added direct no-side-effect coverage | unverified, blocked, suspended, introduction, preferences, pet and complete direct calls are rejected without choice/version mutation |
| Malformed-start recovery test did not prove every contradictory future checkpoint was cleared | Valid MEDIUM test-review gap; strengthened exact repair and replay assertions | preferences, pet choice, pet, privacy and completion checkpoints are null; version advances once and exact replay preserves it |
| Missing-identity regression did not assert every private social default | Valid MEDIUM privacy-evidence gap; expanded the HTTP regression without changing production behavior | all seven canonical private defaults are asserted after provisioning |

### Prompt 02 Observed Evidence Before Final Review

- RED: 13 tests, 3 passed, 21 assertions, five failures and five errors; the
  extended 18-test contract then had 13 passing, 77 assertions and five
  intended failures.
- GREEN: onboarding 65/65 (396 assertions); onboarding/auth/portal 150/150
  (1,001 assertions); pet/social 54/54 (5,368 assertions).
- Migration: package populated apply/down/up preserved five users and created
  zero legacy rows; full 150-migration down/up and fresh/repeat seed passed,
  retaining 10 users and 293 tables.
- Focused Pint and attributable production Larastan pass. Full Larastan remains
  red with 34 non-onboarding Event/Places/forum errors; full Pint reports only
  non-onboarding paths.
- Final full Pest: 2,957 tests, 2,807 passed, 39 failed, 133,140 assertions,
  415,525 ms, exit 2.
  Failures are generated DB/seed drift and absent Event/Places/Portal code,
  factories, routes, or presentation files, not the focused onboarding slice.
- Composer validate/audit/platform, npm audit/build, route/view caches and
  localization generators pass. Generated database audit and seed coverage
  remain red. Repository publication remains NO-GO.

## Prompt 03 Authentication And Portal-Boundary Integration

Prompt 03 starts from `main`, `HEAD`, and `origin/main` at
`4b71974fb22e944f423b7b1bf164540ae9514faf`. The Prompt 02 attributable
working tree remains uncommitted and is preserved. Prompt 03 reuses the
existing `AccountEntryDestination`, `SafeIntendedUrl`, central
`RequirePortalAccess` plus `EnsureOnboardingIsComplete` order, persistent
Livewire middleware, registration initializer, and canonical pet-create flow.
The principal owns every edit and test. Specialists are read-only and may not
change files, Git state, runtime configuration, or databases.

| ID | Specialist role | Exclusive Prompt 03 scope | Required deliverable | Status |
| --- | --- | --- | --- | --- |
| ONB-P03-A | Registration flow auditor | Register component/form/Action, authentication, session rotation, locale, verification notification and destination only | Exact enabled/disabled flow, duplicate-notification risks, missing tests and file/line evidence | completed read-only; post-commit delivery 500 and scattered destinations reproduced |
| ONB-P03-B | Login and intended-URL auditor | Login, AuthenticateUser, session `url.intended`, remember/limiter/locale, SafeIntendedUrl and password confirmation only | Destination matrix, crafted-URL abuse paths, consumption/preservation decision and missing tests | completed read-only; password-confirm consumption and parser-confusing inputs reproduced |
| ONB-P03-C | Email-verification auditor | Mode service, notice/resend, signed controller, signature/throttle, enabled/disabled/completed/incomplete behavior only | Exact verification matrix, precedence/loop risks and required regression changes | completed read-only; signed-route ordering sound, mail localization/feedback/recovery gaps reproduced |
| ONB-P03-D | Middleware and route-boundary auditor | Global/route middleware order, bindings, route names, incomplete allowlist and pet subflow only | Minimal exact allowlist, loop/binding/query analysis and representative route matrix | completed read-only; exact base/pet-step allowlists and locale-order gap recorded |
| ONB-P03-E | Security reviewer | Intended/open redirects, direct routes, JSON, password confirmation, stale sessions and account precedence only | Severity-ranked reproducible bypass hypotheses and precise mitigations/tests | completed read-only; intended, JSON, inactive and direct Action/Livewire threats dispositioned |
| ONB-P03-F | Regression-test reviewer | Existing auth/onboarding/portal/pet tests and isolated runner only | Requirement-to-test matrix, obsolete expectations, false positives and exact focused commands | completed read-only; missing completed/legacy/disabled/pet transport and notification-state cases added |
| ONB-P03-G | Final independent reviewers | Frozen attributable Prompt 03 diff only; reviewers must not implement | Auth, verification, middleware, Livewire and regression verdicts with reproduced material findings | completed in successive frozen reviews; all safely fixable in-scope runtime findings were fixed and scoped reviewers returned SHIP; registration enumeration remains an explicit release blocker |

The selected integration remains intentionally small: unavailable-account
handling stays outside the destination resolver; configured verification is
checked before onboarding; a pending lifecycle destination never consumes the
session intended URL; only a completed account may consume a same-origin safe
destination. The canonical pet create component remains the sole onboarding
pet bridge, and its existing duplicate/access-request authorization is not
relaxed.

### Prompt 03 Independent Review Dispositions

| Finding | Classification and disposition | Evidence |
| --- | --- | --- |
| Broad `Throwable` catch around all `Registered` listeners | Valid MEDIUM; fixed by making only the notification boundary recoverable while unrelated listener exceptions retain normal failure semantics | Notification-transport and unrelated-listener tests pass |
| Resend retained stale success/error state | Valid MEDIUM; reset only the resend state before each attempt | Same-component success-to-failure and failure-to-success tests pass |
| `NotificationSkipped` was treated as delivered; scalar result was lost across Laravel clones | Valid MEDIUM; a clone-stable shared delivery tracker is set only by the synchronous mail channel `afterSending` hook | Exception, skip, success and repeated-state matrices pass |
| Blocked/suspended or stale active-to-blocked component could resend directly | Valid MEDIUM; mount denies inactive accounts and mutation reloads canonical status before limiter/notification | Blocked, suspended and stale direct Livewire tests send nothing |
| Pet component guard was not an Action/transaction lifecycle boundary | Valid HIGH; policies reload canonical status/state, and both mutations lock a fresh user then any onboarding row and reauthorize through `Gate::forUser` | Wrong-step, unverified, stale-blocked, completed, legacy and verification-disabled tests pass |
| Pet return feedback disappeared and LT/RU JSON used the prior locale | Valid MEDIUM; onboarding uses the shared flash key and `SetLocale` precedes both lifecycle middleware | Creation/access-request follow-up and localized JSON/order tests pass |
| Registration outcomes disclose whether an address exists | Valid security debt; not safely fixable while this prompt's mandated success path immediately authenticates a new account but must never authenticate an attacker as an existing account | Explicit release NO-GO pending a common registration-attempt/recovery product contract; no false fix claim |
| Two-connection `FOR UPDATE` interleavings | Evidence blocker, not a reproduced defect; SQLite cannot prove production-adapter row-lock behavior | Require supported production-adapter create/access-request race tests before release |
| Positive signed upload/preview and connected browser/a11y flow | Evidence blocker; real stale portal/pet Livewire update denial is already covered | Keep exact positive transport/browser work open; do not call all Livewire evidence missing |

### Prompt 03 Final Gate Evidence

- Focused serial matrix: **272 tests, 43,817 assertions, exit 0** across auth,
  configurable verification, portal boundary, onboarding, pet, security and
  EN/LT/RU localization files.
- Targeted task Pint and production Larastan passed. Full Pint remained red on
  unrelated Event/Places/forum files; full Larastan reported 34 unrelated
  errors.
- Full isolated Pest: **3,034 total, 2,884 passed, 39 failed, 133,454
  assertions, 423,077 ms, exit 2**. The failures are generated-evidence and
  unfinished Event/Places/Portal/shared-component work, not the focused Prompt
  03 files.
- Composer validate/audit/platform, npm audit/build, onboarding migration,
  150-migration rollback/reapply, fresh/repeat seed, route/view caches and diff
  checks passed. Database-domain/seeding evidence and immutable forum-source
  preservation remained red.
- No connected onboarding browser or production-adapter two-connection race
  was available. Registration enumeration also remains a product-contract
  blocker. Publication verdict: **NO-GO; no principal commit or push**.

## Prompt 04 Livewire Wizard Delivery

Prompt 04 inventory began with `main`, `HEAD`, and `origin/main` at
`4b71974fb22e944f423b7b1bf164540ae9514faf`. The shared Prompt 02/03 working
tree had 45 staged and 23 unstaged paths with overlap and no untracked files.
While the read-only audits ran, an external process committed and pushed that
shared candidate, including this ledger's initial Prompt 04 entry, as
`6efe0241bc23771ef17510bd843ef66a4a6cd23d`; the principal did not create that
commit or push and preserved history. Prompt 04 production work begins from a
clean `main` at `6efe024`. Discovery
specialists are read-only, receive exclusive scopes, and may not edit, stage,
commit, push, or run a state-changing browser/database command. The final
reviewer is reserved until the implementation diff is frozen.

| ID | Specialist role | Exclusive Prompt 04 scope | Required deliverable | Status |
| --- | --- | --- | --- | --- |
| ONB-P04-A | Livewire architecture reviewer | Existing `Onboarding` component, forms, Actions, locked/computed state, events, redirects and hydration only | Recommended thin-component boundaries, reuse map, stale/replay risks and exact evidence | completed read-only; guarded deferral, stale recovery, focused hydration and semantic progress accepted |
| ONB-P04-B | Existing design-system reviewer | Onboarding/auth/application shells, shared Blade controls, tokens, panels, form/error/status patterns only | Reusable primitives, layout recommendation and exact visual-contract gaps | completed read-only; dedicated shell retained, canonical action/surface/type tokens selected |
| ONB-P04-C | Mobile UX reviewer | Onboarding layout at 320/360/375/390, tablet and desktop; progress/actions/long copy/touch only | Responsive structure and measurable overflow/touch acceptance matrix | completed read-only; compact mobile progress and non-stretched desktop actions selected |
| ONB-P04-D | Accessibility reviewer | Onboarding landmarks, headings, progress, focus, labels/errors, keyboard, forced colors, reduced motion and zoom only | WCAG 2.2 defect list and semantic/browser acceptance matrix | completed read-only; progress/group/status findings accepted; Back finding rejected because server entry forbids prior states |
| ONB-P04-E | Livewire security reviewer | Public-property tampering, direct methods, stale snapshots, replay, identity/step/return manipulation only | Exploit-focused cases, severity, reproduction and required regression tests | completed read-only; cross-account signed snapshots, stale-state trapping and evidence-deferral bypasses reproduced, fixed and covered |
| ONB-P04-F | Localization-layout reviewer | Recursive EN/LT/RU onboarding catalogues, placeholder parity, raw keys and long-label layout only | Key/layout gaps with exact render/scanner evidence | completed read-only with D; 61-key baseline parity and scanners were green, localized preference validation and browser reflow accepted; Prompt 04 added one matched progress key per locale |
| ONB-P04-G | Test reviewer | Current onboarding/auth/architecture/localization/browser tests and isolated runners only | Requirement-to-test matrix, false positives, missing negative/browser cases and exact commands | completed read-only; component matrix, strict testing-only fixtures and canonical browser-wrapper mode accepted; connected Chrome execution remains required |
| ONB-P04-H | Final independent reviewer | Frozen Prompt 04 attributable diff only; must not participate in discovery or implementation | Architecture, Livewire security, UX, accessibility and regression verdict with reproduced findings | completed; post-fix hash `f597d93a39caf189a2759de65a0d50d882b85de20f45a5b6b449449aa614a150` confirmed SHIP for the scoped runtime/test candidate |

The selected baseline is the existing class-based `App\Livewire\Onboarding`
and separate view/layout. Prompt 04 will not introduce a second route,
component hierarchy, state store, or client-side step counter merely to match
a hypothetical file name. The canonical forward-only state graph currently
does not permit general backward transitions; the UI must not expose a Back
mutation until that domain contract exists.

## Prompt 05 Preference Step Delivery

Prompt 05 planning started on `main` at externally published
`9f8d74e9da5c1a1774721827d7350d98d03eaa68` with the attributable Prompt 04
review-remediation tree preserved. Before Prompt 05 production edits, an
external process published that Prompt 04 remediation as
`a1fa4668f7636eec2db532f2d4a5fc7a130ec4da`; the principal did not commit or
push it. Planning was updated before every Prompt 05 production edit. Every
specialist below is read-only; the principal owns all decisions and writes.

| ID | Specialist role | Exclusive Prompt 05 scope | Required deliverable | Status |
| --- | --- | --- | --- | --- |
| ONB-P05-A | Profile preference domain auditor | Profile form/action, User fields, session locale and authorization only | Canonical reuse map, duplication and transaction risks | completed read-only; one rule source and auth-model synchronization findings accepted |
| ONB-P05-B | Localization auditor | supported locale config, middleware, EN/LT/RU parity, labels and fallback only | Locale source/application/parity findings | completed read-only; missing help keys and immediate application gap reproduced and fixed |
| ONB-P05-C | Timezone auditor | current timezone rule/options/defaults/DST/tests only | IANA acceptance/performance/normalization matrix | completed read-only; `timezone:all`, deterministic PHP list and exact values retained |
| ONB-P05-D | Livewire UX reviewer | Preferences hydration/save, errors, focus, loading/offline/dirty and mobile only | Component/presentation defect matrix | completed read-only; required semantics, help associations and invalid styling fixed |
| ONB-P05-E | Security reviewer | unsupported values, forged/stale state, direct method and cross-user mutation only | Exploit-focused negative test matrix | completed read-only; malformed replay and stale inactive mutations reproduced, fixed and covered |
| ONB-P05-F | Final independent reviewer | Frozen Prompt 05 diff; no implementation participation | Architecture/localization/security/accessibility/regression verdict | completed; initial NO-GO cross-account Profile Settings snapshot reproduced, RED/GREEN fixed, and two-file hash `57843270388ccd8394d09a83457745dc46f386149ea05952e6067e759a8363cd` confirmed SHIP |

The final reviewer proved that a signed `ProfileSettings` snapshot mounted for
account A could submit its values after the session changed to account B. The
direct Action authorization test could not cover this component-boundary
confusion because the component resolved B and then legitimately authorized
B updating B. The principal reproduced `200` instead of `403`, added a locked
mount account binding plus hydration/mutation guard, observed the regression
GREEN, and received SHIP on the corrected two-file diff. Snapshots missing the
new ID default to zero and fail closed before mutation.

## Prompt 06 Pet Relationship Delivery

Prompt 06 planning started on `main` at
`7c96e504a5bfc9d8e32259971b25157bcb67fa3f`, equal to `origin/main`, while the
uncommitted Prompt 05 security/documentation reconciliation and unrelated
meetups ledger/threat-model work remain preserved. The principal owns every
write. Discovery specialists are read-only and have exclusive scopes; the
final reviewer did not participate in Prompt 06 implementation.

| ID | Specialist role | Exclusive Prompt 06 scope | Required deliverable | Status |
| --- | --- | --- | --- | --- |
| ONB-P06-A | Pet identity auditor | `PetProfile` identity, ownership, status, actor and canonical creation only | Safest canonical creation/relationship integration and exact reusable paths | assigned read-only |
| ONB-P06-B | Duplicate-detection auditor | Candidate visibility, bounds, review tokens, confirm-different and direct-creation protection only | Privacy-preserving duplicate and return-flow findings | assigned read-only |
| ONB-P06-C | Pet-management auditor | Manager status/role, starts/ends/revocation, permissions and creator fallback only | Exact active-management predicate and edge-case matrix | assigned read-only |
| ONB-P06-D | Access-request auditor | Request types, evidence, expiry, encryption, idempotency, approval/invitation only | Onboarding-safe request paths and pending-evidence decision | queued read-only after ONB-P06-A |
| ONB-P06-E | Pet-privacy auditor | Creation visibility, discovery/index/direct-link/location defaults and public projections only | Default-safety findings and required regressions | queued read-only after ONB-P06-B |
| ONB-P06-F | UX/accessibility reviewer | Four truthful choices, owned/managed summary, empty/pending/cancel states, mobile and semantics only | UI state map and measurable accessibility risks | queued read-only after ONB-P06-C |
| ONB-P06-G | Security reviewer | Foreign/private pets, forged return/input, stale tokens/managers/components and completion bypass only | Exploit-focused negative-test matrix | queued read-only after ONB-P06-D |
| ONB-P06-H | Test reviewer | Existing pet/onboarding coverage, missing positive/negative/query/idempotency cases only | Minimal non-duplicative Prompt 06 test matrix | queued read-only after ONB-P06-E |
| ONB-P06-I | Final independent reviewer | Frozen attributable Prompt 06 diff; no discovery or implementation participation | Pet-domain/auth/privacy/UX/regression verdict | reserved read-only |

Selected starting decisions: keep the existing canonical create, duplicate
review and access-request operations; a current pending request may resolve the
`care for an existing pet` onboarding decision but never grants management;
`no pet` and `add later` become distinct controlled choices while legacy
`not-now` remains readable; return behavior is derived only from the persisted
authenticated onboarding state, never a browser URL or durable session flag.

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
- `app/Actions/InitializeUserOnboarding.php`
- `app/Actions/FollowSocialActor.php`
- `app/Actions/RegisterUser.php`
- `app/Actions/SendSocialRelationshipRequest.php`
- `app/Actions/CompleteOnboardingPreferences.php`
- `app/Actions/CompleteOnboardingPrivacy.php`
- `app/Actions/DeferOnboardingPetRelationship.php`
- `app/Actions/UpdatePetProfilePrivacy.php`
- `app/Actions/UpdateProfilePreferences.php`
- `app/Livewire/Auth/ConfirmPassword.php`
- `app/Livewire/Forms/Auth/RegistrationForm.php`
- `app/Livewire/Onboarding.php`
- `app/Livewire/ProfileSettings.php`
- `app/Models/PetProfile.php`
- `app/Models/User.php`
- `app/Models/UserOnboarding.php`
- `app/Services/EmailVerificationMode.php`
- `app/Services/OnboardingPetEvidence.php`
- `app/Services/OnboardingState.php`
- `app/Services/SafeIntendedUrl.php`
- `app/Services/SocialActorAccess.php`
- `app/Services/SocialActorDirectory.php`
- `app/Services/SocialActorResolver.php`
- `database/factories/UserFactory.php`
- `database/factories/UserOnboardingFactory.php`
- `lang/{en,lt,ru}/auth.php`
- `lang/{en,lt,ru}/onboarding.php`
- `resources/views/components/onboarding-layout.blade.php`
- `resources/views/livewire/onboarding.blade.php`
- `tests/Feature/Auth/AuthenticationTest.php`
- `tests/Feature/OnboardingTest.php`
- `tests/Feature/Onboarding/OnboardingMigrationTest.php`
- `tests/Feature/Onboarding/OnboardingPersistenceTest.php`
- `tests/Feature/Onboarding/OnboardingTransitionTest.php`
- `tests/Unit/Services/OnboardingStateTest.php`
- `scripts/verify-onboarding-migration.php`
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
| Synchronous verification notification can fail after account commit | Valid HIGH availability/recovery gap; fixed in Prompt 03 without rolling back the committed account | Exception and standard skip fault injection preserve authentication, show localized recovery, and never return HTTP 500 |
| Two-connection concurrency, positive signed upload/preview, and browser/a11y matrices are incomplete | Valid evidence gaps; real stale portal/pet Livewire updates and signed verification-link cases are already covered | Deferred release evidence; no production-grade completion claim |
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
