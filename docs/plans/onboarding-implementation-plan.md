# Onboarding Implementation Plan

Plan date: 2026-08-30
Branch: `main`
Requirement scope: `PRD-IDENTITY-001`, `PRD-IDENTITY-003..005`,
`SYS-AUTH-001`, `SYS-AUTH-006`, privacy, localization, accessibility, seeding,
and testing contracts affected by account entry.

## Status

**Prompt 05 canonical language/timezone integration and independent scoped
review are complete; connected Chrome evidence and repository-wide release
gates remain blocked. Prompt 06 canonical pet-relationship integration is
planned and in progress. The complete onboarding product and repository
release remain unclaimed.**

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

Prompt 02 began with `main`, `HEAD`, and `origin/main` all at
`1ef8da9512d19bed29ef1fee84efbc07e1494cf5`. Five attributable documentation
files from Prompt 01 were already unstaged and are preserved. The deployed
`2026_08_30_270000_create_user_onboardings_table.php` migration is treated as
immutable. Re-audit confirms that the normalized one-to-one aggregate already
provides every required persistent field, unique account ownership, cascade
cleanup, optimistic `lock_version`, enum casts, runtime registration
initialization, named factory states, and missing-row legacy compatibility.
Prompt 02 therefore adds no duplicate `users` columns and no speculative
second schema migration. Its implementation target is the missing focused
state resolver/model helpers, safe malformed-row handling, completion
prerequisite revalidation, and dedicated transition/compatibility/schema test
coverage.

During Prompt 02 an external process committed and pushed the previously
unstaged Prompt 01 documentation plus the Prompt 02 ledger as
`4b71974fb22e944f423b7b1bf164540ae9514faf`. The principal did not stage,
commit, or push that event and did not rewrite history. Current implementation
work continues from that fast-forwarded `main` while preserving the shared
index exactly.

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

Registration now normalizes before validation, maps a confirmed database
uniqueness conflict to localized validation without partial identity, and
recovers from a failed or deliberately skipped post-commit verification
delivery without returning HTTP 500 or losing the authenticated session.
Remaining gaps include indistinguishable existing/new registration outcomes,
reset-request rate limiting, and direct session-ID/CSRF lifecycle assertions.

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

Stale Livewire snapshots and direct Actions deny mutation after the account
becomes unverified or inactive. Invalid/expired/wrong-user/replayed signed-link,
recipient-locale mail and signed action URL, raw throttling-key prevention,
post-commit failure/skip recovery, and resend stale-state behavior have focused
test evidence. Positive signed upload/preview browser evidence remains open.

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
| Registration enumeration/delivery failure | medium / high availability | Delivery exceptions and standard notification skips are recoverable and localized; common observable existing/new registration outcomes remain open. |
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
| `introduction` | acknowledge | active configured-verified owner, accepted acknowledgement, expected version | `preferences` | only the immediate successor, expected version + 1, and repeated acknowledgement are a no-op |
| `preferences` | save preferences | same owner; EN/LT/RU; IANA timezone; expected version | `pet-relationship` | only the immediate successor, expected version + 1, and identical locale/timezone are a no-op |
| `pet-relationship` | choose relationship | same owner; enum; current managed/pending evidence when claimed | `privacy-discovery` | same choice is no-op; conflicting choice is stale conflict |
| `privacy-discovery` | save privacy and acknowledge | same owner; booleans; acknowledgement; both onboarding/settings versions; locked current pet evidence or `not-now` | `complete` | only matching booleans and both immediately preceding versions preserve settings/timestamps as a no-op |
| `privacy-discovery` | defer invalidated pet relationship | same owner; expected version; controlled `not-now` destination | `privacy-discovery` | an exact immediately preceding retry is a no-op |
| `complete` | none | middleware passes; component redirects | portal | completion timestamp/version remain unchanged |

No future-step call, arbitrary step integer, foreign user, inactive or
configured-unverified account, stale lock, foreign pet/request, or browser
completion timestamp is accepted. Backward navigation is deferred: canonical
profile, social, and pet settings remain editable after completion without
rewinding onboarding history.

Prompt 02 preserves this exact graph:

```text
NEW REGISTRATION
       |
       v
INTRODUCTION -> PREFERENCES -> PET RELATIONSHIP -> PRIVACY / DISCOVERY -> COMPLETE
```

The only forward edge from each incomplete state is the edge shown. Exact
replay of an already-applied operation is a no-op; a conflicting replay or
stale version fails without state regression. General backward navigation is
not a persistent transition in this version. A narrow same-step recovery lets
the user replace revoked, expired, rejected, deleted, or otherwise unavailable
pet evidence with the canonical `not-now` choice, so optional pet setup can
never dead-end portal access. The state resolver may report a conceptual
previous step for presentation, but `canEnter` permits only the current
persisted step. A missing row means legacy-complete. An unknown raw
step or pet-choice value never means complete: it resolves to the earliest
safe step or fails the completion guard and can be repaired only through a
guarded forward transition or an explicit operator repair.

Persisted timestamps must parse exactly as database timestamps, and the raw
lock version must be an integer at least equal to the persisted step position.
A malformed or under-versioned `complete` row therefore fails closed rather
than opening the portal. An invalid `started_at` resolves to `introduction`;
only an authorized acknowledged introduction transition repairs it and
removes contradictory future checkpoints.

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

Prompt 02 re-audit found no justified additive schema change. The existing
columns are exactly `user_id`, `current_step`, `pet_relationship_choice`,
`started_at`, `introduction_completed_at`, `preferences_completed_at`,
`pet_relationship_completed_at`, `privacy_discovery_completed_at`,
`completed_at`, `lock_version`, and Laravel timestamps. The unique `user_id`
index serves ownership and idempotency; middleware checks one account row by
that index, so an additional completion index has no current query
justification. Rollback/reapply evidence comes from the disposable SQLite
migration-cycle wrapper and the package-specific populated legacy verifier,
not from editing the applied migration or touching the configured application
database.

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
`InitializeUserOnboarding`. Unique database keys plus `insertOrIgnore` followed
by an owner-scoped read make sequential and racing retries idempotent without
opening authoritative state to mass assignment. A fault-injection test proves that a failure
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

The shared Livewire transport route remains reachable long enough to verify
its signed snapshot. Persistent middleware then evaluates the signed original
route: incomplete accounts may hydrate onboarding, canonical pet setup, and
account-recovery snapshots only; stale product snapshots abort with `409`
before a component mutation runs.

## Livewire Components

The existing `App\Livewire\Onboarding` class remains a thin coordinator with a
separate Blade view. It owns typed form/scalar presentation state, locked step
and version snapshots, focus/status events, and redirects. It does not accept
user IDs, pet IDs, actor keys, completion timestamps, arbitrary next states, or
return URLs. Business transitions stay in Actions.

Prompt 04 keeps the existing single `/onboarding` route, class-based component,
and focused authenticated layout. The server-derived persisted step selects the
only rendered input screen. A computed presentation map supplies step number,
localized label, current state, and completed state to one semantic ordered
progress list; no public step counter is introduced. The component rechecks the
fresh aggregate before every mutation and redirects a stale snapshot to the
canonical current screen with localized feedback.

The canonical state graph is forward-only. `OnboardingState::canEnter()` permits
only the current persisted state and current transition Actions reject stale
expected steps and versions. Prompt 04 therefore does not render a Back control:
the prompt requires Back only where the state machine permits it, and a
presentation-only previous screen would create a second, browser-authoritative
state machine. Previously persisted preferences remain editable later through
the canonical profile settings flow.

## Forms

- Reuse `ProfilePreferencesForm` for locale/timezone.
- Reuse `OnboardingPrivacyForm` for the three real social settings.
- Add a small `OnboardingPetChoiceForm` for the radio-card browser input; it
  parses only the controlled `OnboardingPetChoice` enum while evidence remains
  enforced by the existing Action and canonical pet queries.
- Introduction advances from an explicit Continue action without a meaningless
  checkbox; privacy acknowledgement remains explicit, unchecked, validated at
  the component boundary, and enforced by the completion Action.
- Add onboarding-specific localized validation messages/attribute names while
  continuing to reuse the profile preference rules and Action.
- Pet creation/access forms stay in their canonical components.

## Actions

- `InitializeUserOnboarding`: idempotent private identity/progress bootstrap.
- `AdvanceUserOnboarding`: introduction and pet-choice transitions.
- `CompleteOnboardingPreferences`: locks state and delegates canonical profile
  validation/persistence.
- `DeferOnboardingPetRelationship`: keeps the account at privacy while
  atomically replacing invalidated relationship evidence with `not-now`.
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
- `OnboardingPetEvidence` performs the same bounded canonical evidence check at
  pet selection and immediately before completion; the completion check locks
  a non-deleted profile first, then its qualifying request/manager or legacy
  pet evidence in the completion transaction. Pending requests must still be current, while an approved
  request counts only with its active granted manager.
- `OnboardingState` is the Prompt 02 read-only interpreter for raw enum-safe
  current/pet values, completion truth, next/previous presentation,
  current-step entry, and completion-prerequisite checks.

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
the canonical duplicate-aware component. Four new-account choices are stored
as controlled domain values: manage a pet, care for an existing pet, no pet,
and add later. The earlier `not-now` value remains a backward-compatible
read-only equivalent for rows saved before Prompt 06; new UI does not collapse
the two distinct user intents. “Managed” is valid only for an active
manager membership or documented legacy creator fallback when no own manager
row exists. Revoked, expired, future, invited, foreign, or soft-deleted
relationships do not count. “Access requested” requires either a current,
non-expired pending request owned by the authenticated user and a current
profile, or an approved request backed by its currently active granted manager.
Rejected, cancelled, expired, unlinked approved, or expired-temporary requests
do not count. The decision itself grants no permission.

A current pending request satisfies only the onboarding decision to care for
an existing pet so that external approval cannot trap an account. It never
creates `PetProfileManager`, never authorizes private pet data, and must still
be approved and accepted through the canonical invitation lifecycle. New pet
creation continues to create the canonical draft profile, manager, privacy,
alias, actor/lifecycle and audit records with private, non-discoverable,
non-indexable, non-direct-link and hidden-location defaults. The safe return
context is the authenticated persisted onboarding step: canonical pet Actions
re-read it under lock and the destination resolver returns to onboarding only
while that step is current. No `return_url`, `next`, arbitrary route name or
session flag is accepted, so there is no stale return state to clean up.

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
| Pets | no-pet; add-later; legacy `not-now`; canonical create/duplicate/access; bounded owned/managed summary; legacy creator, active manager, invited/revoked/expired/future/foreign manager; own/foreign/current request; soft-deleted/private candidate; safe return; no permission grant. |
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
- [x] **ONB-P02-01 — Current persistent-state revalidation and planning.**
  Reconcile Prompt 02 with the deployed normalized aggregate, preserve the
  no-row legacy exemption, and record seven read-only specialist scopes before
  production edits.
- [x] **ONB-P02-02 — RED state and compatibility contracts.** Added focused tests
  for safe malformed values, semantic account helpers, exact step traversal,
  start/completion idempotency, prerequisite rechecks, cross-user/stale
  rejection, legacy statuses, schema shape, enum casts, and valid factory
  combinations. The initial 13-test RED run produced five failures and five
  errors; an extended 18-test run then reproduced five selected failures.
- [x] **ONB-P02-03 — Minimal state hardening.** Added the focused state resolver
  and small `User` facts, route existing transition readers through safe raw
  interpretation, and reject impossible completion without changing the
  deployed schema, factory default, pet workflow, or verification state.
- [x] **ONB-P02-04 — Migration/factory/seeder evidence.** Focused tests,
  package-specific populated rollback/reapply, the complete migration cycle,
  fresh database, and repeat seed are green. Five representative legacy users
  retain every captured identity/lifecycle field and receive no onboarding row.
- [x] **ONB-P02-05 — Frozen-diff independent review.** Architecture,
  database, security, tests, and regression concerns are reviewed by an agent
  that made no implementation edits; every material finding is reproduced and
  dispositioned before publication. The first freeze produced valid findings
  for malformed completion, stale replay data, Livewire transport, pet-evidence
  locking, replay side effects, mass assignment, UI evidence parity, and
  verifier isolation; the first re-review then found missing pet recovery,
  public-default identity repair, malformed-start dead-end, and deleted-profile
  evidence. The second test review then identified three evidence-only gaps:
  negative authorization/state coverage for pet deferral, exact malformed-start
  repair assertions, and complete private social-default assertions. All were
  remediated. Architecture/database/regression, security, and test/privacy/
  compatibility reviewers independently returned SHIP for the 24-path candidate
  SHA-256 `10a17725230fc1316ba4b64a9b0ba8d5f94bda687e829ffd9be63644117279d4`.
- [ ] **ONB-P02-06 — Documentation, gates, and publication.** Reconcile both
  plans, ledger, and changelog with observed evidence, run applicable gates,
  inspect an isolated task index, and commit/push only if material gates pass.
- [x] **ONB-P03-01 — Authentication-boundary re-audit and plan.** Re-read the
  registration, login, verification, password-confirmation, intended-URL,
  middleware, Livewire, pet-create and focused-test paths; preserve the Prompt
  02 working tree; record seven read-only roles and the exact Prompt 03 package
  before production changes.
- [x] **ONB-P03-02 — RED lifecycle contracts.** Added focused failing tests
  for reproduced gaps in destination centralization, intended URL safety,
  lifecycle precedence, pet-route least privilege, JSON and real Livewire
  transport while retaining existing session/limiter/signature assertions.
- [x] **ONB-P03-03 — Minimal auth integration remediation.** Reused one focused
  account-entry resolver from registration, login, verification, confirmation
  and onboarding completion; preserved intended URLs across mandatory gates and
  consumed them only after completion; kept the central middleware allowlist
  explicit and step-aware where the audit proves it necessary.
- [x] **ONB-P03-04 — Focused and adjacent verification.** Ran onboarding,
  authentication, configurable verification, portal boundary, pet and
  Livewire transport suites plus targeted Pint and Larastan.
- [x] **ONB-P03-05 — Frozen independent review.** Independent auth-security,
  verification-security, middleware, Livewire and completed-user regression
  reviewers inspected successive frozen remediation hashes; every material
  finding was reproduced, dispositioned and rerun. The final scoped reviewers
  returned SHIP; production-adapter two-connection lock evidence remains a
  release-evidence blocker, not a reproduced code defect.
- [x] **ONB-P03-06 — Documentation, gates and publication decision.** Recorded
  exact diagrams, allowlist, commands, counts, global blockers, diff ownership,
  commit and push status without claiming Prompt 04 UI work.
- [x] **ONB-P04-01 — Wizard re-audit and delivery design.** Re-read the current
  component, forms, Actions, state service, route, layout, catalogues, design
  system and tests; recorded eight exclusive specialist roles before code
  changes. Retain the one class-based component and forward-only server state.
- [x] **ONB-P04-02 — RED wizard contracts.** Added focused component, semantic,
  localization, stale/replay, optional-evidence and disposable-browser-wrapper
  tests; the initial 15-case suite produced 14 intended failures before the
  implementation and the expanded 23-case suite now passes.
- [x] **ONB-P04-03 — Thin interaction and responsive UI remediation.** Implemented
  computed progress, semantic radio/checkbox groups, explicit loading/offline/
  dirty/error feedback, localized validation, stale-state recovery, guarded
  pet deferral, focused hydration and mobile-first design-system-aligned views.
- [ ] **ONB-P04-04 — Connected browser evidence.** The canonical isolated
  runner now contains onboarding coverage at 320x800, 360x800, 375x812,
  390x844, 768x1024 and 1440x900,
  including overflow, focus, touch target, zoom/forced-colors, locale, console
  and network assertions against disposable data. Isolation and seeding pass,
  but installed Chrome 152 exits with `SIGSEGV`/139 before page assertions, so
  connected evidence remains open rather than inferred.
- [ ] **ONB-P04-05 — Independent frozen-diff review.** A reviewer excluded from
  discovery and implementation must cover architecture, Livewire security,
  UX, accessibility and regression; every material finding is reproduced and
  dispositioned before publication.
- [ ] **ONB-P04-06 — Documentation, gates and publication.** Record exact tests,
  commands, failures, browser evidence, diff ownership, commit and push output;
  publish only if every applicable material gate is green.
- [x] **ONB-P05-01 — Preference-domain re-audit and delivery plan.** Re-read the
  canonical profile form/action, locale middleware/configuration, onboarding
  transition boundary, profile/auth tests and EN/LT/RU catalogues before any
  Prompt 05 production edit; retain one profile-preference persistence path.
- [x] **ONB-P05-02 — RED preference contracts.** Prove initial hydration,
  configured locale options, real IANA timezone acceptance/rejection, immediate
  LT/RU application, stale/replay safety, resume and normal-settings
  consistency before changing runtime behavior.
- [x] **ONB-P05-03 — Canonical preference integration.** Reuse
  `ProfilePreferencesForm` and `CompleteOnboardingPreferences` /
  `UpdateProfilePreferences`; persist only `users.locale` and `users.timezone`,
  apply the saved locale to session/current response, and let the canonical
  onboarding Action advance the server state.
- [x] **ONB-P05-04 — Accessible localized presentation.** Keep one H1, labelled
  44px selects, associated help/errors, summary focus, dirty/loading/offline
  status, wrapping EN/LT/RU copy and no browser-owned identity/step state.
- [x] **ONB-P05-05 — Independent review and verification.** Review architecture,
  localization/timezone correctness, Livewire security, accessibility and
  profile regression on a frozen diff; rerun focused/full applicable gates and
  publish only when every material gate is green.
- [x] **ONB-P06-01 — Pet-domain re-audit and delivery plan.** Re-read canonical
  pet identity, creation, duplicate review, manager, access-request, privacy,
  onboarding and authorization boundaries; record nine exclusive reviewer
  roles and the chosen pending-request/return semantics before runtime edits.
- [ ] **ONB-P06-02 — RED pet relationship contracts.** Prove distinct no-pet
  and add-later choices, active relationship edge cases, bounded summaries,
  pending request semantics, safe returns, completion defense and abuse cases.
- [ ] **ONB-P06-03 — Canonical relationship integration.** Extend the existing
  controlled choice model compatibly, centralize bounded relationship
  presentation, and reuse the canonical manager/request evidence predicates
  without creating onboarding pet records or permissions.
- [ ] **ONB-P06-04 — Canonical create, duplicate and request bridge.** Keep
  duplicate tokens/candidate visibility and access-request idempotency intact;
  derive onboarding return solely from locked authenticated server state while
  preserving normal creation redirects.
- [ ] **ONB-P06-05 — Accessible localized Pets experience.** Render four
  semantic radio choices, safe bounded relationship summaries, clear empty and
  pending states, canonical actions, EN/LT/RU copy, errors, loading and offline
  feedback with one H1 and 44px targets.
- [ ] **ONB-P06-06 — Independent review, gates and publication.** Freeze the
  attributable diff, reproduce/disposition pet-domain, authorization,
  security, privacy, UX and test findings, run focused/full gates, and commit /
  push only if every material gate is green.

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
  tests/Feature/Onboarding/OnboardingMigrationTest.php \
  tests/Feature/Onboarding/OnboardingPersistenceTest.php \
  tests/Feature/Onboarding/OnboardingTransitionTest.php \
  tests/Unit/Services/OnboardingStateTest.php \
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
php scripts/verify-onboarding-migration.php
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

### Prompt 04

- [x] Initial inventory observed `main`, `HEAD`, and `origin/main` at
  `4b71974fb22e944f423b7b1bf164540ae9514faf`, with 45 staged and 23 unstaged
  shared Prompt 02/03 paths and zero untracked files. While read-only Prompt 04
  audits ran, an external process committed and pushed that shared candidate,
  including the initial Prompt 04 ledger, as
  `6efe0241bc23771ef17510bd843ef66a4a6cd23d`. The principal did not create that
  commit or push; history is preserved. Prompt 04 production work starts from
  the now-clean `main` at that commit.
- [x] Governing frontend, Livewire, Tailwind, accessibility, localization,
  authentication, security, testing, onboarding and implementation documents
  re-read; code and current tests were inspected rather than inferred.
- [x] Current architecture selected before production edits: keep one route,
  one class component and server state; reuse canonical preference/privacy
  boundaries; add only one controlled pet-choice form; do not invent Back while
  `OnboardingState::canEnter()` forbids previous-state entry.
- [x] RED wizard/security/localization/browser contracts observed: 15 tests
  produced 14 intended failures; browser isolation had no onboarding mode.
- [x] Production interaction and presentation remediation implemented. After
  independent review fixes, the expanded wizard suite passes 29 tests / 230
  assertions; the selected onboarding/auth/browser-isolation/architecture
  slice passes 214 of 218 tests / 54,915 assertions, with only four previously
  open generated-evidence/factory/forum-history architecture failures. Focused
  production Larastan was rerun uncached with `--debug` and passes.
- [x] Focused and adjacent gates observed. The broader selected slice passed
  271 of 277 tests; its six failures are the already-open 25 missing
  cross-domain factories, stale generated compliance/inventory/forum history,
  and pre-existing shared JS lifecycle/literal contracts. Browser database
  preparation and `OnboardingBrowserSeeder` pass, then Chrome 152 crashes with
  `SIGSEGV` before the connected assertions.
- [x] Independent architecture/security, UX/accessibility and regression
  reviews completed. Reproduced account-snapshot, progress, validation/focus,
  pet-evidence mapping, process-lifecycle, query-budget and flat-surface defects
  were fixed with regressions. The final reviewer confirmed frozen hash
  `f597d93a39caf189a2759de65a0d50d882b85de20f45a5b6b449449aa614a150`
  as SHIP for the scoped runtime/test candidate.
- [x] Prompt 04 changelog and plans reconciled. An external process published
  the reviewed package as `a1fa466`; the principal did not commit or push it.
  Connected Chrome and repository-wide gates remain explicit release blockers.

### Prompt 05

- [x] Prompt 05 scope added to both canonical plans before runtime changes:
  canonical form/action reuse, configured locale source, IANA timezone,
  immediate locale application, resume/profile consistency, accessibility,
  security and EN/LT/RU evidence.
- [x] Six read-only specialist roles completed. The final reviewer returned
  initial NO-GO for a cross-account signed Profile Settings snapshot, the
  principal reproduced `200` instead of `403`, fixed it with a locked mount
  binding/hydration guard, and the reviewer confirmed corrected hash
  `57843270388ccd8394d09a83457745dc46f386149ea05952e6067e759a8363cd`
  as SHIP.
- [x] RED preference-domain/component contracts observed: the initial 26-test
  file had 12 passing, 10 failing and four errors (147 assertions), reproducing
  raw help keys, missing immediate locale application, malformed replay and
  stale inactive-account mutation.
- [x] Canonical implementation completed: `ProfilePreferenceRules` is shared by
  `ProfilePreferencesForm` and `UpdateProfilePreferences`; the Action reloads
  fresh account availability; onboarding/profile Livewire synchronizes the
  fresh guard plus session/application locale; the Preferences view has
  required/help/error semantics and matching EN/LT/RU copy.
- [x] Focused verification is green: Preferences + Wizard 58/58 (490), the
  auth/onboarding transition matrix 150/150 (1,004) before the final added
  negative cases, scoped architecture 10/10 (47,356), Blade boundary 1/1
  (2,457), localization 7/7 (37,713), task Pint, three localization scanners
  and targeted Larastan all pass. This was the pre-final-review checkpoint;
  final review and broad evidence follow below.
- [x] Post-review verification is green: Preferences + Wizard 59/59 (493),
  Preferences + Authentication 63/63 (535), the cross-account RED/GREEN
  regression 1/1 (3), and the final five-file onboarding/auth matrix 154/154
  (1,026); task Pint and targeted Larastan pass. Composer strict/audit/platform,
  npm high audit/build, isolated onboarding migration, fresh/repeat seed, route
  cache and view cache also pass.
- [x] Broad and connected evidence recorded without inflating completion. Full
  Pest ran 3,093 tests: 2,943 passed, 39 failed, 134,027 assertions, exit 2 in
  440,296 ms; failures remain in stale generated database evidence and missing
  Places/Portal/shared implementations. Full Pint remains red only on unrelated
  Event/Places/forum work; full Larastan reports 34 unrelated errors. The
  disposable onboarding browser migrated and seeded successfully, then Chrome
  closed DevTools before any page assertion. Publication remains NO-GO.

### Prompt 06

- [x] Git inventory observed `main`, `HEAD`, and `origin/main` at
  `7c96e504a5bfc9d8e32259971b25157bcb67fa3f`; final Prompt 05 reconciliation
  remains staged/unstaged and unrelated meetups files are excluded.
- [x] Governing product, architecture, security, authorization, pet profile,
  privacy, Livewire, accessibility, localization, testing, seeding and
  deployment documents were re-read before runtime changes.
- [x] Initial code trace confirms the canonical duplicate-aware create and
  access-request domains already exist; Prompt 06 will extend rather than copy
  them. A pending current request may resolve onboarding but grants no access.
- [ ] RED tests, implementation, focused verification, independent frozen-diff
  review, broad gates and publication decision remain in progress.

### Prompt 03

- [x] Branch, HEAD, origin, staged, unstaged and untracked inventory observed.
  `main`, `HEAD` and `origin/main` are
  `4b71974fb22e944f423b7b1bf164540ae9514faf`; the attributable Prompt 02
  working tree is preserved and no reset/clean/stash was used.
- [x] Governing and directly relevant auth, verification, security, routing,
  Livewire, testing, portal-boundary and onboarding documents re-read; current
  code was traced rather than inferred from Prompt 02 status.
- [x] Both canonical plans and the work ledger updated before Prompt 03
  production changes.
- [x] Six specialist discovery reports received and independently reproduced.
- [x] RED behavior observed for selected missing contracts, including intended
  consumption, pet-step transport, direct/stale Actions, localized JSON,
  notification exception/skip, resend state and inactive replay boundaries.
- [x] Minimal production changes implemented with focused GREEN evidence.
  Registration delivery no longer produces the reported post-commit HTTP 500;
  auth entry points share one destination resolver; lifecycle middleware is
  ordered after locale and before bindings; intended URLs and pet setup are
  server-authoritative.
- [x] Exact route allowlist, enabled/disabled lifecycle diagrams, focused tests
  and changelog reconciled. Positive signed upload/preview browser coverage is
  recorded as open rather than described as complete Livewire evidence.
- [x] Independent review completed across auth, verification, middleware,
  Livewire, pet transaction, architecture/database/test and completed-user
  regression scopes. Broad `Registered` error handling, stale resend state,
  notification skip/clone handling, inactive resend, pet Action status/step
  races, feedback and locale ordering were fixed and rerun. Registration
  enumeration is explicitly deferred because the mandated immediate-login
  success flow cannot be made externally identical to an existing-account
  attempt without a separate product contract.
- [x] Final gates, ownership, commit and push results recorded. Focused scope,
  Composer, npm, isolated migration/fresh/repeat-seed, route/view caches and
  diff checks are green. Full Pest, full Pint, full Larastan, generated
  database/seeding evidence, immutable forum-source preservation, production-
  adapter concurrency, and connected onboarding browser evidence are not
  green. No principal commit or push was made.

Prompt 03 observed evidence on the final implementation:

- Focused serial matrix: authentication 33/33 (272 assertions), configurable
  verification 10/10 (42), portal boundary 42/42 (291), onboarding directory
  110/110 (452), onboarding integration 29/29 (192), pet create 5/5 (31), pet
  duplicate/access 15/15 (68), pet foundation 16/16 (4,768), security surface
  5/5 (36), localization 7/7 (37,665): **272 tests and 43,817 assertions**.
- Targeted Pint passed; targeted production Larastan passed with zero errors.
  Full `pint --test` remained red only across unrelated Event/Places/forum and
  adjacent work. Full Larastan completed with **34 unrelated errors**.
- Full isolated Pest completed **3,034 tests, 2,884 passed, 39 failed, 133,454
  assertions, 423,077 ms, exit 2**. Failures remain in generated database
  audit drift and unfinished Event/Places/Portal/shared-component work; the
  focused Prompt 03 files above stayed green.
- `verify-onboarding-migration.php` preserved five legacy users through
  apply/down/up. The full migration cycle reapplied 150 migrations and repeat
  seed preserved 10 users; fresh verification produced 293 tables, 150
  migrations and the same stable 10-user repeat seed.
- Composer strict validation, locked audit and platform requirements passed;
  npm high audit found zero vulnerabilities and Vite built successfully;
  route and view cache create/clear smokes passed as `www`.
- Database-domain audit remains red because 52 discovered models (including
  `UserOnboarding`) are absent from its stale manifest; seeding coverage reports
  25 missing factories. Forum requirement generation verified 38,377 atomic
  requirements, while immutable-source preservation remains red because source
  prompt entry `1785397895` is absent.
- No connected onboarding browser flow was run: the repository has no focused
  disposable onboarding wrapper. Real stale portal/pet Livewire update tests
  pass, but positive signed upload/preview, production-adapter row-lock races,
  and connected responsive/keyboard/console evidence remain explicit release
  blockers.
- Final Git refresh observed `main`, HEAD and `origin/main` at
  `4b71974fb22e944f423b7b1bf164540ae9514faf`; 55 changed paths are present in
  the shared tree (45 staged, 23 unstaged, with overlap; zero untracked). The
  shared index was preserved. Global and security gates prohibit commit/push.

### Prompt 02

- [x] Branch, `HEAD`, `origin/main`, staged, unstaged, and untracked state
  observed before Prompt 02 edits. The branch is `main`; `HEAD` and
  `origin/main` are `1ef8da9512d19bed29ef1fee84efbc07e1494cf5`; no staged or
  untracked paths were present; five Prompt 01 documentation files were
  unstaged and are preserved.
- [x] Governing contract, prior onboarding plan, canonical requirements, and
  current aggregate/enum/model/factory/registration/middleware/Action/test
  implementation re-read. The Prompt 01 one-to-one design still matches code.
- [x] Prompt 02 seven-role read-only ledger created before delegation; the
  state-machine, database, compatibility, factory/seeder, security, and test
  reviewers completed without modifying files.
- [x] This plan and `docs/implementation-plan.md` updated before Prompt 02
  production changes.
- [x] Focused RED behavior observed: the first run had 13 tests, 3 passed, 21
  assertions, five failures and five errors; the extended run had 18 tests, 13
  passed, 77 assertions and five intended failures.
- [x] Minimal production hardening implemented and focused GREEN observed:
  state resolver/model helpers, exact replay guards, completion prerequisite
  and locked pet-evidence rechecks, controlled factory states, malformed-state
  recovery, replay side-effect prevention, and stale Livewire snapshot denial.
  The current onboarding slice passes 65/65 with 396 assertions;
  onboarding plus auth/portal passes 150/150 with 1,001 assertions; pet/social
  regression passes 54/54 with 5,368 assertions.
- [x] Disposable rollback/reapply, fresh database, and repeat seed verified.
  The package verifier preserved five legacy users through apply/down/up with
  zero enrollment rows. The full 150-migration cycle returned to zero and
  reapplied all 150; fresh/repeat seed retained 10 users and 293 tables.
- [x] Independent final review completed and every material finding disposed.
  The first frozen review and first re-review were NO-GO; all reproduced
  findings have focused GREEN regressions. Three independent reviewers verified
  the same 24-path hash and returned SHIP; no material finding remains open.
- [ ] Applicable final gates, exact test/assertion counts, diff ownership,
  commit, and push results recorded. Composer validation/audit/platform,
  focused Pint, task production Larastan, npm audit/build, route/view caches,
  localization generators, migration, fresh DB, repeat seed, and focused tests
  are green. Final full Pest is red at 2,957 tests / 2,807 passed / 39 failed /
  133,140 assertions / 415,525 ms / exit 2; full Pint reports unrelated
  Event/Places/forum files;
  full Larastan reports 34 unrelated Event/Places/forum errors; generated DB
  audit and seed coverage remain red. Therefore no principal commit or push is
  authorized.

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
