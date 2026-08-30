# Onboarding Implementation Plan

Plan date: 2026-08-30
Branch: `main`
Requirement scope: `PRD-IDENTITY-001`, `PRD-IDENTITY-003..005`,
`SYS-AUTH-001`, `SYS-AUTH-006`, privacy, localization, accessibility, seeding,
and testing contracts affected by account entry.

## Status

**Prompt 01 foundation remediation and independent review complete; the full
onboarding product and repository release remain NO-GO.**

The checkout already contains the additive onboarding aggregate, registration
bootstrap, central middleware, forward-only Actions, a class-based Livewire
flow, EN/LT/RU catalogues, and focused tests from commit `0787b76`. Prompt 01
does not recreate them. It revalidates them, repairs proven prerequisite
defects, and records later packages without claiming that the complete
onboarding product or repository release is done.

The revalidation began with `main` and `origin/main` both at
`48730147fde586108bf79477dff066e5bb1b0ec5` and a clean tree. External
processes subsequently committed and pushed the initial, review-remediation,
and mechanical-format candidates as `77a1c9cb384614d10db5d13c9c9504cbb3d45aff`,
`8826415382d9d83968568ddd01b70f3129de3f70`, and
`1ef8da9512d19bed29ef1fee84efbc07e1494cf5` before final gates completed. The
principal did not stage, commit, or push those commits and did not rewrite
history. The final focused matrix passed 156 tests and 43,718 assertions. The
full suite and current generated database/seeding evidence remain red across
unrelated Event, Places, Portal, factory, seeding, forum-history, localization,
and component work, so none of the external commits is a release approval.

## Current Repository State

- PHP 8.5, Laravel 13, Livewire 4 class-based multi-file components, Blade,
  Tailwind CSS 4, Vite, Pest 4, and Larastan are the active stack.
- PawCircle is a closed authenticated portal. Product data is unavailable to
  guests; policies remain mandatory after the outer portal boundary.
- `user_onboardings` is an additive one-to-one aggregate. A row means the
  account participates in onboarding; no row is the deliberate legacy
  exemption.
- New registrations create `User`, private personal `SocialActor`,
  `SocialActorSetting`, and `UserOnboarding` in one database transaction.
- Persisted states are `introduction`, `preferences`, `pet-relationship`,
  `privacy-discovery`, and `complete`. The server, not a URL or browser step,
  selects the rendered state.
- The current `UserFactory` default creates a verified legacy-compatible user
  with no onboarding row. This intentionally avoids trapping hundreds of
  unrelated tests. Named onboarding factory states now create the requested
  progress row plus the canonical private actor/settings aggregate.
- Compliance currently says `implemented`, not `implemented and verified`.
  That is the highest defensible status until current gaps and applicable
  repository gates are closed.

## Existing Authentication Flow

Visitor -> `register` -> `RegistrationForm` validation -> `RegisterUser`
transaction -> login -> session regeneration -> verification notice or
onboarding.

- Registration normalizes the stored email, hashes the password through the
  model cast, creates an active account, copies the request locale, applies a
  configured IANA timezone, bootstraps private identity/progress, and emits
  `Registered` after commit when verification is enabled.
- Login normalizes credentials, applies an email-and-IP limiter, rejects
  unavailable accounts, records `last_login_at`, regenerates the session,
  restores locale, and sends incomplete accounts through verification then
  onboarding before consuming the intended destination.
- Logout invalidates the session and regenerates the CSRF token.
- Password reset and confirmation remain canonical auth flows. Confirmation
  currently consumes an unvalidated intended URL and is included in the
  prerequisite remediation. Password-reset request throttling and
  session-lifecycle proof remain explicit follow-up work unless completed in
  this package.

Registration now normalizes before validation and maps a confirmed database
uniqueness conflict to localized validation without partial identity. Remaining
gaps include indistinguishable existing/new registration outcomes, recoverable
handling of synchronous verification-mail failure, reset-request rate limiting,
and direct session-ID/CSRF lifecycle assertions.

## Existing Email Verification Flow

- `EmailVerificationMode` is the single configuration boundary and defaults
  safely to enabled. Only exact boolean `false` bypasses proof.
- Enabled registration leaves the account unverified, sends `Registered`, and
  routes to `verification.notice`.
- Disabled registration stamps `email_verified_at` inside the transaction,
  sends no verification notification, and routes to onboarding.
- The verify route requires authenticated active state, an expiring absolute
  signature, request ownership/hash authorization, and route throttling.
- Verification success routes an incomplete account to onboarding without
  consuming the protected product destination. A complete/legacy account
  receives the safe intended destination or `home`.
- Repeated valid verification is persistence-idempotent.

Stale Livewire snapshots and direct Actions now deny mutation after the account
becomes unverified. Invalid/expired/wrong-user signed-link tests,
recipient-locale mail rendering, raw throttling-key prevention, and
post-commit mail-failure recovery remain required evidence.

## Existing Profile Preference Flow

`ProfilePreferencesForm` and `UpdateProfilePreferences` are canonical for the
EN/LT/RU allowlist and IANA timezone validation. Onboarding delegates to them,
then stores the selected locale in the session and redirects before the next
render so copy is not mixed-language. No second preference validator or
duplicate profile persistence is permitted.

Profile biography, media, location, interests, and prototype presenter data
are not mandatory onboarding identity. The existing hard-coded owner profile
projection is not a source of truth for onboarding.

## Existing Pet Creation Flow

The only allowed pet path is the existing class-based
`Pets\CreatePetProfile` component with `PetProfileCreateForm`,
`CreatePetProfile`, `PetProfileDuplicateReview`, and the canonical
access-request Actions.

- Creation is idempotent and defaults to private, non-discoverable,
  direct-link disabled, external-indexing disabled, and hidden location.
- Duplicate candidates are bounded, visibility-scoped, and carried through an
  encrypted expiring viewer-bound token.
- Access requests are validated, encrypted, idempotent, and do not grant
  manager rights. Review plus invitation acceptance owns that transition.
- Onboarding stores only a relationship decision and server-observed evidence;
  it accepts no browser user ID, actor key, pet ID, or return URL.
- The current bridge uses middleware redirects after create/cancel. A later UI
  package should provide a named server-controlled return context without
  trusting arbitrary destinations.

`PetProfile::managedBy()`, `visibleTo()`, and onboarding evidence now agree
with `PetProfileAccess`: current active membership is authoritative, explicit
`deny:view` is honored for projections, and creator fallback applies only when
no own manager row exists.

## Existing Privacy/Discovery Flow

- New personal actors start non-discoverable, non-recommendable,
  message-request closed, friend/follow closed, and both lists hidden.
- Onboarding delegates privacy persistence to `UpdateSocialActorSettings` and
  preserves unrelated canonical friend/follow/list policies.
- `DiscoveryPreference` rows suppress recommendations; they are relevance
  controls, not privacy consent, and onboarding must not create them.
- Pet, medical, care, exact-location, and GPS/device privacy remain in their
  owning domains. Onboarding must not copy or infer those values.
- External indexing is a pet-domain preference and is not evidence that an
  external crawler indexed or removed anything.

The privacy step requires explicit browser acknowledgement and records the
existing `privacy_discovery_completed_at` timestamp as its non-redundant
server evidence. No marketing, medical, GPS, location, or external-indexing
consent is inferred.

A separate audit found that lazy pet social actors could be discoverable while
the owning pet profile was private. Prompt 01 now caps actor provisioning,
privacy transitions, directory projection, policy checks, and direct social
mutations by canonical pet visibility. Discovery still fails open when a
legacy actor settings row is missing; that separate debt remains tracked.

## Existing Social Identity Initialization

`InitializeUserOnboarding` runs inside `RegisterUser` and idempotently creates:

1. one `User`-backed `SocialActor`;
2. one private `SocialActorSetting` row;
3. one `UserOnboarding` row at `introduction`, lock version 1.

Runtime registration no longer depends on a seeder or backfill. Legacy lazy
resolver behavior remains intentionally compatible and must not be silently
rewritten. Registration does not create fake pets, expert profiles,
organizations, groups, medical records, devices, locations, or discovery
preferences.

## Problems Found

| ID | Severity | Current problem | Prompt 01 disposition |
| --- | --- | --- | --- |
| ONB-G01 | high | Persistent Livewire transport is allowlisted, while onboarding/profile Actions checked active identity but not a verification state that changed after mount. | Fixed at component and direct Action boundaries with no-side-effect tests; real transport proof remains open. |
| ONB-G02 | high | Pet owner-FK queries and onboarding evidence ignored an own revoked/expired manager row. | Fixed with the canonical active/legacy query scope plus explicit `deny:view` projection handling; remaining onboarding relationship cases are test debt. |
| ONB-G03 | high | Private pet social actors can be provisioned discoverable, found through search, or addressed directly by a known actor key. | Fixed: cap provisioning, policy, directory, privacy synchronization, and locked follow/request targets by canonical pet visibility; direct negative regressions pass. |
| ONB-G04 | medium security | `SafeIntendedUrl` accepted backslash-relative parser-confusion values; password confirmation bypassed the service. | Fixed through one sanitizer; broader connected browser dataset remains open. |
| ONB-G05 | medium accessibility | Error summary hid real state errors, ordinary validation did not focus it, progress used a false navigation landmark, and forced-colors focus/boundaries were incomplete. | Fixed at source/render contract level; connected keyboard/zoom/forced-colors browser proof remains open. |
| ONB-G06 | medium product | Privacy completion had no explicit required browser acknowledgement. | Fixed in Livewire and the direct Action, reusing the completion timestamp as server evidence. |
| ONB-G07 | medium testing | `UserFactory` had no named onboarding states; transition tests omitted several negative/replay paths. | Named states now create the canonical private aggregate without changing the default; broader transition/concurrency coverage remains open. |
| ONB-G08 | high localization | LT/RU generic validation and auth mail are English; verification throttle referenced a missing key. | The missing onboarding-adjacent keys and new generic registration conflict text are localized; full framework validation/mail linguistic remediation remains open. |
| ONB-G09 | high release | Representative model manifest/generated DB evidence and the repository-wide suite are already stale across multiple concurrent domains. | Do not hand-edit generated evidence or claim release. Run current gates and report exact attributable vs concurrent failures. |
| ONB-G10 | medium reliability | Registration mail transport can throw after DB commit but before login/redirect. | Preserve data integrity; define and test a recoverable resend UX in a subsequent auth package unless safely completed here. |
| ONB-G11 | medium security | Existing and new registration addresses still produce distinguishable validation/auth/redirect outcomes even after replacing the specific duplicate text. | Open: design one common registration-attempt response compatible with both verification modes; keep release NO-GO. |
| ONB-G12 | low defense in depth | An existing social idempotency replay returns before locked target reauthorization. | Open: it creates no new relationship, but stale-target and revoked-source replay tests plus authoritative reload are required. |
| ONB-G13 | low future cache | Pet privacy synchronizes actor visibility but does not invoke `SocialGraphCache` invalidation. | No current affected cache reader was found; unify invalidation and add projection-version coverage before enabling cached social projections. |

## Security Risks Found

The detailed repository-grounded threat model is
`docs/audits/petsocial.miniserver.fun-threat-model.md`.

| Threat | Priority | Required control |
| --- | --- | --- |
| Direct URL or Livewire transport bypass | high impact / medium residual | Central pre-binding middleware plus fresh component/Action authorization and real transport tests. |
| Browser step/version/user tampering | medium residual | No browser identity input; locked snapshots; authenticated owner row lock and version check. |
| Conflicting stale tabs | medium | Forward-only transitions, exact replay, first-writer state, and stale conflict without side effects. |
| Intended URL parser confusion | medium | Same-origin service for every account-flow return and rejection of backslash/control/protocol-relative forms. |
| Missing onboarding row used as bypass | medium | Only canonical registration may create production accounts; missing-row legacy exemption is monitored and not generated for new accounts. |
| Verification bypass | medium | Configured verification check at HTTP, Livewire component, and direct Action boundaries. |
| Pet IDOR/private discovery | low residual / high impact | Policy-visible duplicate flow, viewer-bound tokens, active/permission-aware manager authority, actor synchronization, and pet-profile visibility checks in directory and direct social mutations. |
| Registration enumeration/delivery failure | medium / high availability | Generic localized constraint handling is present, but common observable registration outcomes and recoverable post-commit verification delivery remain open. |
| Session fixation | low residual / high impact | Regenerate after login/register, invalidate on logout, and add direct lifecycle assertions. |

## Proposed User Journey

```text
registration
  -> verification notice when configured
  -> introduction
  -> language and timezone
  -> pet relationship decision
       -> canonical pet create/duplicate/access workflow when requested
       -> or continue without a pet for now
  -> privacy explanation, choices, and explicit acknowledgement
  -> server completion check
  -> one safe intended portal URL or home
```

The separate “no pet” and “add later” survey answers are deliberately collapsed
into `not-now`: both have the same safe product behavior and no required domain
side effect. A later analytics requirement may add a non-blocking distinction;
it is not needed for secure portal entry.

## Mandatory Steps

- Active account throughout every transition.
- Email verification when the configured mode is enabled.
- Introduction acknowledgement.
- Supported locale and valid IANA timezone persisted through the canonical
  preference Action.
- One server-validated pet relationship choice: managed evidence, pending
  access-request evidence, or explicit `not-now`.
- Privacy/discovery values plus explicit acknowledgement.
- Server-side prerequisite recheck before setting `complete` exactly once.

## Optional Steps

- Creating a pet, submitting an access request, or accepting an invitation.
- Profile photo, biography, location, interests, and additional profile facts.
- Discovery suppressions, follows, suggested accounts, and notification
  customization.
- Additional pets, pet media/details, groups, organizations, expert identity,
  medical/care/device data, and precise location.

Optional work never blocks portal access and may be completed later.

## Canonical State Machine

| Current state | Accepted operation | Guard | Next state | Replay |
| --- | --- | --- | --- | --- |
| `introduction` | acknowledge | active configured-verified owner, accepted acknowledgement, expected version | `preferences` | exact already-applied request returns current state |
| `preferences` | save preferences | same owner; EN/LT/RU; IANA timezone; expected version | `pet-relationship` | later state returned without rewriting preferences |
| `pet-relationship` | choose relationship | same owner; enum; current managed/pending evidence when claimed | `privacy-discovery` | same choice is no-op; conflicting choice is stale conflict |
| `privacy-discovery` | save privacy and acknowledge | same owner; booleans; acknowledgement; both onboarding/settings versions | `complete` | completed state returned without rewriting settings/timestamps |
| `complete` | none | middleware passes; component redirects | portal | completion timestamp/version remain unchanged |

No future-step call, arbitrary step integer, foreign user, inactive or
configured-unverified account, stale lock, foreign pet/request, or browser
completion timestamp is accepted. Backward navigation is deferred: canonical
profile, social, and pet settings remain editable after completion without
rewinding onboarding history.

## Database Changes

The existing additive `user_onboardings` table is retained:

- unique indexed `user_id` with cascade delete;
- controlled `current_step` string cast to `OnboardingStep`;
- nullable controlled pet choice cast to `OnboardingPetChoice`;
- started, per-step, privacy acknowledgement/completion, and final timestamps;
- integer optimistic `lock_version`.

No redundant locale, timezone, privacy-toggle, actor, or pet columns are added.
No historical migration is edited. Portable database constraints or a repair
path for corrupted enum/timestamp invariants remain a later hardening package;
application writes and tests must preserve them now.

## Backward Compatibility

| Account | Behavior |
| --- | --- |
| Existing normal/admin/demo user with no row | Legacy exemption; normal portal/policies; values are not rewritten. |
| Existing unverified user | Verification gate remains authoritative; no implicit enrollment. |
| Existing blocked/suspended user | Availability denial precedes onboarding. |
| Existing user with incomplete row | Resumes persisted step. |
| Existing user with complete row | Normal portal access. |
| Newly registered user | Must atomically receive a private identity and incomplete row. |

No bulk backfill is performed. After production writes, rollback is an
application forward-fix that stops enforcement while retaining progress, not
destructive removal of user data.

## Runtime User Initialization

All production account creation must call `RegisterUser` and
`InitializeUserOnboarding`. Unique database keys plus `firstOrCreate` make
sequential retries idempotent. A fault-injection test must prove that a failure
inside identity/progress bootstrap rolls back `User`, actor, settings, and
onboarding together. Synchronous mail delivery occurs after the transaction
and needs a separately recoverable UX; it cannot be called database atomicity.

## Routes

- `GET /onboarding`, name `onboarding.show`, is the single wizard route.
- The persisted server state chooses the step. There is no browser-controlled
  `/onboarding/{step}` route.
- `pets.manage.create` is the only product bridge allowed while incomplete.
- Verification notice/handler, password confirmation, logout, and required
  Livewire transport remain available according to the outer account state.
- A later pet-return improvement may use a server-known enum/context; arbitrary
  return URLs remain forbidden.

## Middleware

Required order:

`StartSession -> RequirePortalAccess -> EnsureOnboardingIsComplete -> CSRF -> SubstituteBindings`

`RequirePortalAccess` owns guest, active/inactive, and conditional verification
decisions. `EnsureOnboardingIsComplete` performs one indexed one-to-one state
lookup, passes missing/complete rows, redirects safe HTML GETs, and returns a
localized `409` for product mutations/JSON. Both are persistent in Livewire.
Detailed prerequisite queries never run in middleware.

## Livewire Components

The existing `App\Livewire\Onboarding` class remains a thin coordinator with a
separate Blade view. It owns typed form/scalar presentation state, locked step
and version snapshots, focus/status events, and redirects. It does not accept
user IDs, pet IDs, actor keys, completion timestamps, arbitrary next states, or
return URLs. Business transitions stay in Actions.

## Forms

- Reuse `ProfilePreferencesForm` for locale/timezone.
- Reuse `OnboardingPrivacyForm` for the three real social settings.
- Validate explicit introduction/privacy acknowledgement at the component
  boundary and again through server transition prerequisites where applicable.
- Pet choice is parsed through `OnboardingPetChoice`; pet creation/access forms
  stay in their canonical components.

## Actions

- `InitializeUserOnboarding`: idempotent private identity/progress bootstrap.
- `AdvanceUserOnboarding`: introduction and pet-choice transitions.
- `CompleteOnboardingPreferences`: locks state and delegates canonical profile
  validation/persistence.
- `CompleteOnboardingPrivacy`: locks state, delegates canonical social settings,
  records acknowledgement/completion once.
- Existing pet create/duplicate/access and social settings Actions remain the
  only domain mutation authorities.

## Services

- `AccountEntryDestination` owns verification/onboarding gate order.
- `SafeIntendedUrl` owns every account-flow return destination.
- `EmailVerificationMode` owns the configured verification rule.
- `SocialActorResolver` owns runtime actor/settings provisioning.
- `PetProfileAccess` and the canonical managed query own pet authority.

No second state store, repository wrapper, or client-side state machine is
introduced.

## Authorization

Every HTTP request is subject to portal middleware before binding. Every
Livewire mutation and Action independently resolves the current session user,
requires active/configured-verified status, verifies identity equality, reloads
and locks the owned onboarding/settings rows, and checks policy/domain
evidence. `#[Locked]` is hydration integrity only, never authorization.

## Validation

- Locale: exact configured EN/LT/RU allowlist.
- Timezone: PHP/Laravel IANA timezone rule.
- Pet choice: backed enum; claimed relationships require current server data.
- Privacy settings: actual booleans; explicit acknowledgement is accepted.
- Step/version: server enum plus expected snapshot checked against locked DB
  state.
- Intended URL: root-relative with no backslash/control ambiguity, or exact
  application scheme/host/port.
- All errors are localized and rendered in a focusable summary plus associated
  field/group feedback.

## Pet Integration

Onboarding never creates a simplified pet record. The create/find link enters
the canonical duplicate-aware component. “Managed” is valid only for an active
manager membership or documented legacy creator fallback when no own manager
row exists. Revoked, expired, future, invited, foreign, or soft-deleted
relationships do not count. “Access requested” requires a current pending
request owned by the authenticated user and a current profile. The decision
itself grants no permission.

## Privacy Integration

All social discovery/contact switches start false. Completion preserves the
existing friend/follow/list policies and updates actor discoverability plus
settings in the same outer transaction as onboarding completion. Explicit
acknowledgement records understanding of the displayed privacy model only. It
does not create pet, discovery-preference, location, medical, GPS/device, or
marketing-consent records.

## Localization

All wizard copy lives in recursive `lang/{en,lt,ru}/onboarding.php` catalogues
with exact key and placeholder parity. Preference success redirects after
updating user/session locale. No PHP/Blade/JavaScript user-facing literal is
added. Prompt 01 will prevent raw onboarding/auth keys; full linguistic repair
of generic LT/RU validation and framework auth mail is tracked until actual
recipient-locale content tests pass.

## Accessibility

- One document `main`, one H1, sequential H2, skip link, and current-step text.
- A noninteractive ordered step list plus an explicitly named native progress
  element; no false navigation landmark.
- Focusable summary contains every localized error; ordinary validation and
  transition conflicts move focus to it; successful local transitions focus
  the next H2.
- Labels/help/error associations and required semantics for every control.
- Target-specific loading, dirty, offline, disabled, and completion feedback.
- Minimum 44px targets, visible focus including forced colors, non-color state,
  reduced-motion compatibility, 200% zoom/no-overflow, and keyboard-only flow.
- Connected checks cover 320, 375, 768, 1024, and 1440 widths in EN/LT/RU.

## Performance

- Middleware performs one indexed existence/state query and loads no pet,
  privacy, or social graph.
- Pet evidence uses bounded `exists()` queries and canonical scopes.
- Locale options are bounded; timezone identifiers are finite. No model graph
  is stored in the Livewire snapshot.
- No query runs in Blade and no relationship is lazy-loaded there.
- Completion work uses short transactions; cache invalidation remains in the
  owning social Action.

## Test Matrix

| Area | Required cases |
| --- | --- |
| Registration | Verification enabled/disabled; normalized case-insensitive email; one state/actor/settings; private defaults; session rotation; injected bootstrap rollback; mail-failure recovery. |
| Verification | Signed success; invalid/expired/unsigned/wrong-user; replay; enabled/disabled; complete/incomplete/legacy destinations; recipient locale. |
| Login/logout/reset/confirm | Complete/incomplete/unverified/inactive; intended chain; same-origin confirmation; session/CSRF lifecycle; stale guest snapshots; reset limiter. |
| Middleware | Guest, inactive, unverified, incomplete, complete, legacy; pre-binding; HTML/JSON/mutation; real Livewire transport; exact pet-create allowlist. |
| State machine | Every forward step; future/wrong-user/inactive/unverified denial; exact and conflicting replay; stale locks; two-connection race; completion exactly once. |
| Preferences | EN/LT/RU; valid/invalid timezone; unsupported locale; policy denial; DB/session locale and next-render language. |
| Pets | `not-now`; canonical create/duplicate/access; legacy creator, active manager, invited/revoked/expired/future/foreign manager; own/foreign/current request; soft-deleted profile; no permission grant. |
| Privacy | All private defaults; explicit acknowledgement required; booleans/tampering; personal actor only; settings/state rollback; private pet absent from social directory; no sensitive-domain rows. |
| Compatibility/factory | Old account/admin/demo/unverified/blocked; named UserFactory states; default remains legacy-compatible; migration up/down/reapply and user/domain preservation. |
| Localization | Recursive parity/placeholders; every step EN/LT/RU; no raw key/literal; long LT/RU; throttle and validation/mail semantics. |
| Accessibility/browser | One H1/main, named progress/list, real error summary/focus, labels, required/help associations, loading/dirty/offline, 44px, keyboard, zoom, forced colors, reduced motion, overflow and console. |

## Migration Plan

1. Keep the already-applied additive migration; do not edit or rename it.
2. Old code ignores the table; new code creates rows only through canonical
   registration.
3. Do not bulk-enrol or rewrite existing accounts/privacy.
4. Verify isolated empty and populated up/down/reapply behavior. After writes,
   treat table removal as destructive and prefer a forward fix.
5. Monitor unexpected missing rows for post-cutover accounts, transition
   conflicts, redirect loops, and non-identifying aggregate completion counts.

## Rollout Plan

1. Ship schema before or with code that tolerates its absence only according
   to the documented deployment order.
2. Smoke both verification modes, refresh/logout resume, canonical pet bridge,
   private defaults, explicit privacy acknowledgement, completion, and intended
   destination on an isolated environment.
3. Keep production debug off; validate route/config/view caches and actual
   Livewire asset HTTP status.
4. Promote compliance to verified only after focused, repository, migration,
   build, and connected-browser evidence is current on the exact candidate.

## Rollback Plan

- Before any production rows: normal reviewed migration rollback is possible.
- After rows: disable creation/enforcement through a forward fix while
  retaining progress; old application code safely ignores the additive table.
- Reverting wizard/middleware must not revert email verification, actor keys,
  privacy choices, pet authority, or other canonical facts.
- Never delete users, actors, settings, pets, access requests, or onboarding
  rows as an operational shortcut.

## Work Packages

- [x] **ONB-01 — Repository and domain audit.** Twelve narrow read-only roles
  plus principal reproduction covered auth, verification, identity, pets,
  privacy/discovery, middleware, database, Livewire, UX/a11y, localization,
  tests, and threat modeling.
- [x] **ONB-02 — Existing foundation inventory.** Additive aggregate,
  initializer, middleware, state Actions, wizard, catalogues, and existing
  tests are mapped against current code.
- [x] **ONB-03 — RED prerequisite contracts.** Added failing tests for selected
  verification, redirect, pet authority/privacy, privacy acknowledgement,
  accessibility, and factory gaps; serial runs observed the intended failures.
- [x] **ONB-04 — Minimal prerequisite remediation.** Implemented only the
  changes required by ONB-03 and reproduced independent findings; all focused
  and adjacent files pass through the isolated runner.
- [x] **ONB-05 — Documentation and changelog reconciliation.** Recorded exact
  results and remaining Prompt 02+ work; do not hand-edit generated evidence.
- [x] **ONB-06 — Frozen-diff independent review.** Architecture, security,
  database, tests, and regression roles completed. Direct private-pet access,
  `deny:view`, factory aggregate, Action acknowledgement, and uniqueness-500
  findings were fixed and retested. Registration enumeration, mail recovery,
  true concurrency/transport/browser evidence, and replay hardening remain
  explicitly open.
- [x] **ONB-07 — Final gates and publication decision.** Focused checks pass;
  full Pest/Pint/Larastan/generated database and seed gates are red, and the
  browser gate lacks a safe wrapper. Decision: NO-GO and no principal
  publication. External processes nevertheless pushed three candidate commits;
  history is preserved and the event is documented.
- [ ] **ONB-08 — Prompt 02+ wizard depth.** Safe pet return context, browser
  runner, concurrency proof, mail/validation linguistic remediation, recovery
  UX, observability, and later requirements remain unclaimed until delivered.

## Acceptance Criteria

- New registrations atomically receive one `User`, private user actor/settings,
  and resumable onboarding row; no production account path bypasses bootstrap.
- Conditional verification precedes onboarding at HTTP, Livewire, and direct
  Action boundaries.
- Incomplete users cannot read/bind/mutate ordinary portal resources and can
  use only exact account-flow and canonical pet-create routes.
- Transitions are owner-scoped, enum-driven, forward-only, idempotent,
  optimistic, and deterministic under stale/replayed submissions.
- Preferences use canonical validation; pet relationship uses canonical active
  authority; privacy uses real settings plus explicit acknowledgement.
- Completion is set once only after every mandatory prerequisite and restores
  one safe same-origin destination.
- Existing users without rows retain access and existing canonical data.
- Named factory states make incomplete/step/complete tests explicit without
  changing the default factory contract.
- EN/LT/RU, keyboard, focus, errors, loading/offline, mobile, forced-colors,
  reduced-motion, and overflow criteria are verified, not inferred.
- Documentation and compliance distinguish implemented foundation, observed
  checks, global blockers, and later work truthfully.

## Verification Commands

```bash
composer validate --strict
composer audit --locked
composer check-platform-reqs --lock
vendor/bin/pint --test
PAO_DISABLE=1 PHPSTAN_TURBO=0 vendor/bin/phpstan analyse --memory-limit=1G
php scripts/run-tests.php --compact tests/Feature/OnboardingTest.php \
  tests/Feature/Auth/AuthenticationTest.php \
  tests/Feature/Auth/ConfigurableEmailVerificationTest.php \
  tests/Feature/Auth/PortalAccessBoundaryTest.php \
  tests/Feature/PetProfileFoundationTest.php \
  tests/Feature/PetProfileDuplicateAccessRequestTest.php \
  tests/Feature/SocialRelationshipFoundationTest.php \
  tests/Feature/LocalizationTest.php \
  tests/Feature/PageIdentityStandardizationTest.php \
  tests/Feature/ArchitectureComplianceTest.php
php scripts/run-tests.php --compact
php scripts/verify-migration-cycle.php
php scripts/verify-fresh-database.php
php scripts/generate-database-domain-audit.php --check
php scripts/generate-seeding-coverage.php --check
php scripts/localize-blade-literals.php --check
php scripts/localize-php-messages.php --check
php scripts/migrate-readable-translation-keys.php --check
npm audit --package-lock-only --audit-level=high \
  --registry=https://registry.npmjs.org/ --strict-ssl=true
npm run build
php artisan route:cache && php artisan route:clear
php artisan view:cache && php artisan view:clear
git diff --check
```

Connected onboarding browser proof must use a disposable repository wrapper.
The current standalone pet duplicate/access browser script is state-changing
without the wrapper's mutation guard and must not be run against a configured
application database.

## Current Execution Status

- [x] Branch/HEAD/origin/index/worktree inventory observed before edits.
- [x] Mandatory and directly relevant documents read; stale current-state and
  release evidence classified as historical rather than silently trusted.
- [x] Twelve specialist audit roles completed read-only; principal reproduced
  the material code paths.
- [x] Focused baseline: 101 passed, 718 assertions, exit 0.
- [x] Root seed gate reproduced red: 3 tests, 2 passed, 1 failed; dynamic model
  list and 211-entry representative manifest differ across many domains.
- [x] This dedicated plan and the canonical implementation plan updated before
  new production-code changes.
- [x] Selected prerequisite tests observed RED; the combined runner's earlier
  `SIGSEGV` was excluded and serial failure evidence was used.
- [x] Review-remediated implementation and adjacent focused tests GREEN through
  the canonical isolated runner: onboarding 25/25 (169 assertions), auth 33/33
  (272), pet 16/16 (4,768), social 23/23 (532), configurable verification
  10/10 (42), portal boundary 42/42 (291), and localization 7/7 (37,644):
  **156 tests and 43,718 assertions**.
- [x] Five independent frozen-diff review roles completed and dispositioned.
  No material task-owned runtime regression remains in the covered matrix;
  reviewers retain NO-GO for the explicitly open recovery/security/evidence
  gaps.
- [x] Applicable final gates recorded. Full isolated Pest ran 2,917 tests with
  2,767 passing, 39 failing, 132,877 assertions, exit 2; failures are current
  cross-domain generated evidence, absent Places/Event/Portal code/factories,
  seed, localization, and component drift. Full Pint and configured Larastan
  are also globally red; focused task PHP formatting and production Larastan
  groups pass.
- [x] Composer validation/audit/platform, npm high audit/build, migration
  cycle, fresh database/seed, route cache, and view cache checks passed on the
  first frozen candidate. Current database-domain and factory/seeding
  generators remain red. Connected browser proof was not executed because no
  disposable onboarding wrapper exists.
- [x] Publication decision made from observed gates: NO-GO. The principal made
  no commit or push. External processes pushed `77a1c9c`, `8826415`, and the
  mechanical-format commit `1ef8da9` before gates closed; no reset, force-push,
  or history rewrite was attempted.
