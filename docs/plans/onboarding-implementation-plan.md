# Onboarding Implementation Plan

Plan date: 2026-08-30  
Branch: `main`  
Delivery status: audit complete; RED-first implementation starting

## Goal And Scope

Deliver a server-authoritative, resumable onboarding path between account
creation/email verification and the authenticated portal. The foundation must
preserve the existing authentication, pet identity, duplicate-review, access
request, social graph, profile-preference, privacy, and discovery authorities.
It must not claim that later onboarding prompts or every planned account
setting are complete.

This plan is the source of truth for the onboarding delivery. The specialist
ownership and finding ledger is
`docs/audits/onboarding-audit-work-ledger.md`; the repository-wide delivery
entry remains `docs/implementation-plan.md`.

## Current-State Audit

### Entry points and observed transitions

| Entry or transition | Current behavior | Onboarding consequence |
| --- | --- | --- |
| Registration | `RegisterUser` creates one active `User`, assigns a server-generated immutable account actor key, normalized email, request locale, configured timezone, and optionally an immediate verification timestamp. `Register` logs the user in, regenerates the session, persists locale, and redirects to email verification or `home`. | There is no durable onboarding row, social actor bootstrap, onboarding redirect, or resumable progress. |
| Login | `AuthenticateUser` normalizes credentials, limits attempts, requires an active account, records last login, regenerates the session, restores locale, and consumes Laravel's `url.intended`. | A verified incomplete user would currently enter the portal. An unverified user can lose the protected intended URL when central middleware subsequently redirects to verification. |
| Email verification | Configuration can require or operationally bypass proof. The signed route authorizes the current user, is expiring/throttled, and `fulfill()` is persistence-idempotent. Success always redirects to `home`; resend is a Livewire mutation without a dedicated limiter. | Verification has no onboarding handoff and does not preserve an intended deep link. Onboarding initialization cannot depend only on `Registered` or `Verified`, because those events are deliberately absent in disabled mode. |
| Logout | Framework logout invalidates the session and regenerates the CSRF token. | Progress must be database-backed; session is suitable only for a temporary return destination. |
| Profile preferences | `ProfilePreferencesForm` and `UpdateProfilePreferences` validate the EN/LT/RU allowlist and IANA timezone, authorize self-update, and persist both values on `users`. | Reuse this operation; do not copy locale/timezone validation into Blade or a second domain service. |
| Social identity/privacy | `SocialActorResolver` lazily creates a user actor and one settings row with historically permissive defaults. `UpdateSocialActorSettings` policy-checks and optimistic-locks settings. Actor discoverability is a separate field. | A new account does not yet receive every canonical social identity. Provision it during registration with privacy-first defaults, while preserving legacy lazy defaults. The onboarding privacy transition must atomically cover actor discoverability and settings. |
| Pet creation | The class-based `CreatePetProfile` flow already owns validation, idempotency, duplicate candidates, duplicate review tokens, safe creation defaults, manager grants, private location/privacy rows, and access-request submission. Newly created pets are non-discoverable. | Reuse the existing route/component/Actions. Onboarding records only the relationship decision and confirms durable evidence; it does not create a second pet form or duplicate detector. |
| Portal boundary | `RequirePortalAccess` runs in the web stack after the session and before route bindings, admits only explicit guest/unverified routes, handles JSON, and is persistent for Livewire. | Add a second fail-closed onboarding boundary after portal access and before bindings. It must not expose bound models or block its own Livewire requests. |
| Home | Authenticated eligible users are redirected from `/` to `content.index`. | Keep `/` as the canonical portal entry. Use a separate named onboarding route and let the boundary select it. |

### Existing-user behavior

There is no onboarding state in the current schema or application. Existing
accounts can access the portal after the existing active-account and optional
verification checks without configuring profile, pet, privacy, or discovery
preferences. Compatibility therefore uses the absence of an onboarding row as
an explicit legacy exemption; it is not interpreted as an incomplete account.

### Canonical identities issued today

- Registration always creates `User` and `users.actor_key`.
- Registration does not create the normalized user `SocialActor` or
  `SocialActorSetting`; they are created lazily by social flows or explicit
  backfill/seeding.
- Registration does not create a pet, pet manager, pet social actor, or
  compatibility `UserDomainState`; those absences are valid.
- Owner and pet identities and their actor keys remain separate contracts.

## Discovered Problems

| ID | Severity | Problem | Required disposition |
| --- | --- | --- | --- |
| ONB-F01 | critical foundation | No durable, server-authoritative onboarding state or portal gate exists. | Add one-to-one state, transitions, middleware, and direct-action authorization. |
| ONB-F02 | high | Newly registered accounts lack their normalized user social actor/settings until an unrelated social flow runs. | Provision exactly one user actor/settings row in the registration transaction with privacy-first defaults. |
| ONB-F03 | high | Login and verification can consume or lose a protected intended URL before all account gates are satisfied. | Route through one destination decision and consume only after verification and onboarding complete. |
| ONB-F04 | high | Historical social actor defaults are discoverable/recommendable/message-open. | Preserve legacy defaults for legacy lazy creation, but use explicit private defaults for new-account provisioning. |
| ONB-F05 | high | Pet creation, duplicate review, and access requests are complex existing authorities that a new wizard could accidentally bypass. | Link to and detect evidence from the canonical pet workflow; never reproduce its mutations in onboarding. |
| ONB-F06 | high | Registration and verification resend mutations do not have dedicated application limiters, and stale Livewire guest snapshots need an explicit fail-closed account-state check. | Cover with RED security tests and bounded mutation limiters/guards in the auth foundation slice. |
| ONB-F07 | medium | Social settings update does not include actor discoverability. | Add one atomic onboarding operation that reuses the existing settings Action and locks the onboarding/actor rows. |
| ONB-F08 | medium | Profile settings presentation contains prototype owner destinations that are not a safe onboarding source of truth. | Reuse only the validated preference operation and neutral form patterns. |
| ONB-F09 | medium | Current tests do not cover durable resume, direct route bypass, wrong-user state, transition replay, legacy exemption, or the complete intended-URL chain. | Add focused feature/Livewire/database/architecture cases before implementation. |

## Evaluated Designs

### Option A — columns on `users`

Store a completion flag/current step directly on `users`. This is simple but
mixes authentication identity with workflow history, provides weak per-step
evidence, and makes future expansion/backward compatibility harder.

### Option B — one-to-one onboarding aggregate (selected)

Create an additive `user_onboardings` table with a controlled current step,
per-step timestamps, explicit pet decision, completion timestamp, and lock
version. A missing row means legacy exemption; registration creates the row
for new accounts. This provides resumability, transactional transitions,
optimistic concurrency, rollback-friendly deployment, and clear authorization.

### Option C — derive completion from existing account/pet/social facts

Infer onboarding from non-default locale/timezone, pet relationships, and
social settings. This cannot distinguish an accepted default from an unseen
form, cannot record a legitimate no-pet choice, and makes completion unstable
when the user later changes a setting. It is rejected.

## Canonical State Machine

```text
registered
    |
    +-- verification required and pending --> verify-email
    |
    `-- verified/bypassed ------------------>
                                                introduction
                                                     |
                                                preferences
                                                     |
                                              pet-relationship
                                           /         |          \
                                     managed pet  access request  not now
                                           \         |          /
                                             privacy-discovery
                                                     |
                                                  complete
                                                     |
                                          intended URL or portal
```

The persisted steps are `introduction`, `preferences`, `pet-relationship`,
`privacy-discovery`, and `complete`. Transitions are forward-only, lock and
reload the authenticated user's row, validate the expected current step, and
are replay-safe. Repeated submission of an already completed transition
returns the existing state without duplicating effects. A stale, future-step,
foreign-user, or browser-supplied state identifier is rejected.

### Mandatory, skippable, and deferrable choices

| Step | Requirement |
| --- | --- |
| Introduction | Mandatory acknowledgement; no marketing consent is inferred. |
| Account preferences | Mandatory explicit confirmation of locale and timezone; current safe defaults may be accepted unchanged. |
| Pet relationship | Mandatory explicit choice. Creating a pet is optional. A user may prove an existing managed pet, submit a canonical access request after duplicate review, or choose `not-now`. |
| Privacy and discovery | Mandatory explicit confirmation of user-actor discoverability, recommendation inclusion, and message-request preference. Initial defaults are off/private. |
| Completion | Server transition only after prior evidence. Pet/profile expansion, public discovery, notification settings, export/deletion, and additional pets remain available later and are not implied complete. |

Email verification is conditionally mandatory according to the existing
configuration. It is an account gate before onboarding, not a mutable
onboarding step.

## Required Database Changes

Add `user_onboardings` in one new expand-only, SQLite-portable migration after
the current latest migration:

| Column | Contract |
| --- | --- |
| `id` | Internal primary key; never accepted from browser state. |
| `user_id` | Required unique foreign key to `users`, cascade delete. |
| `current_step` | Required bounded string backed by `OnboardingStep`; default `introduction`. |
| `pet_relationship_choice` | Nullable bounded string backed by `OnboardingPetChoice`. |
| `started_at` | Required server timestamp. |
| `introduction_completed_at` | Nullable server timestamp. |
| `preferences_completed_at` | Nullable server timestamp. |
| `pet_relationship_completed_at` | Nullable server timestamp. |
| `privacy_discovery_completed_at` | Nullable server timestamp. |
| `completed_at` | Nullable server timestamp; only the final transition sets it. |
| `lock_version` | Unsigned integer, default `1`, incremented on every transition. |
| timestamps | Normal Eloquent timestamps. |

The unique user constraint is the race-safe idempotency boundary. No backfill
is performed. Existing users remain operational because missing state is a
legacy exemption. New registrations create the row in the same transaction as
`User`, `SocialActor`, and `SocialActorSetting`, so no partially initialized
account can commit.

Add `UserOnboarding`, its factory, explicit fillable fields, enum casts, and a
typed `User::onboarding()` relation. Factory defaults must satisfy every schema
constraint and provide meaningful per-step/completed states.

## Routes And Destinations

| Method | Name | URI | Boundary |
| --- | --- | --- | --- |
| GET | `onboarding.show` | `/onboarding` | `web`, central portal boundary, `auth`, `active`; verification enforced by central boundary |
| existing GET | `pets.manage.create` | `/pets/manage/new` | Temporarily allowed while onboarding is incomplete; all pet mutations stay in the existing component/Actions. |

No anonymous onboarding endpoint, route closure, public state token, or
client-selected user/state key is introduced. The onboarding page uses a
dedicated authenticated account-flow shell derived from shared auth design
tokens, and is classified in the global route/page-identity ledgers.

Post-auth destination order is:

1. inactive-account response;
2. email verification when configured and pending;
3. incomplete onboarding when a row exists;
4. validated internal intended destination;
5. `home`/content feed.

Login, registration, verification, onboarding completion, and central
middleware must apply the same order. The Laravel session may retain
`url.intended`, but it is consumed only at step 4.

## Middleware Behavior

Add `EnsureOnboardingIsComplete` immediately after `RequirePortalAccess` and
before `SubstituteBindings` in the priority list and web stack.

- Guests, inactive users, and conditionally unverified users remain owned by
  `RequirePortalAccess`; the onboarding middleware never weakens those gates.
- A user without an onboarding row is treated as a legacy-complete account.
- A completed row passes through.
- An incomplete row may access `onboarding.show`, `logout`, password
  confirmation, verification endpoints, the canonical pet-create bridge, and
  framework Livewire update/upload/preview endpoints required by an allowed
  page. Other HTML GET requests store the internal intended URL and redirect
  to onboarding before route binding.
- JSON/API expectations receive a localized `409 Conflict` problem response
  with a safe onboarding URL; they never receive HTML or a private resource.
- Non-GET product mutations fail closed rather than being converted to a
  redirect that could hide data loss.
- The middleware is registered as persistent Livewire middleware, while every
  onboarding action still reauthorizes and reloads the current user/state.
- Query strings may be retained only through Laravel's internal intended URL;
  hosts/schemes are never accepted from public onboarding input.

## Livewire, Actions, Forms, And Policies

### Class-based component

`App\Livewire\Onboarding` with
`resources/views/livewire/onboarding.blade.php` owns small typed presentation
state only. It exposes locked `expectedStep`, `onboardingLockVersion`, and
`socialSettingsLockVersion` snapshots so a stale tab cannot execute the next
step accidentally. It still derives the authoritative state and relationship
evidence from a fresh authorized query on every named mutation, exposes no user
or onboarding primary key, and uses locked state only as hydration
integrity—not authorization.

### Form objects

- Reuse `ProfilePreferencesForm` for locale/timezone and
  `UpdateProfilePreferences` for the persisted account preference mutation.
- Add an onboarding privacy form containing only booleans for discoverability,
  recommendation inclusion, and message requests. Existing controlled social
  policy/list values remain server-owned in this foundation and are not
  silently broadened.
- Pet relationship choices are allow-listed enums; pet data itself remains in
  `CreatePetProfile` and its existing forms.

### Application operations

- `InitializeUserOnboarding`: idempotently provision the onboarding row and
  privacy-first user social actor/settings inside registration's transaction.
- `AdvanceUserOnboarding`: lock the authenticated user's state, validate the
  expected transition, record step timestamps/choice, and increment the lock.
- `CompleteOnboardingPreferences`: compose the existing preference Action and
  state transition in one outer transaction.
- `CompleteOnboardingPrivacy`: authorize the personal actor, atomically update
  actor discoverability and existing social settings, then complete the state.
- Pet evidence checks use scoped Eloquent relationships for an active manager
  grant or the authenticated user's current access request. They never accept
  a pet ID supplied by the onboarding page.

`UserPolicy` and `SocialActorPolicy` remain the mutation authorities. Add a
focused `UserOnboardingPolicy` only if the aggregate is addressed outside the
authenticated user's relationship; otherwise the Actions must enforce exact
ownership directly and tests must prove wrong-user denial. No administrator
bypass is introduced.

## Privacy And Security Defaults

Newly registered user actors start with:

- `is_discoverable = false`;
- `is_recommendable = false`;
- `allow_message_requests = false`;
- friend requests disabled and follows approval- or nobody-gated until the
  user explicitly confirms broader contact settings;
- friend/follower lists hidden or minimally count-only.

These are explicit new-account provisioning values, not a historical migration
of legacy social settings. Pet creation retains its existing private,
non-discoverable, no-external-indexing, hidden-location defaults.

Registration is limited to five attempts per minute per IP and verification
resend to three attempts per minute per user-and-IP key. Stale guest-only
Livewire actions must re-check the current session
actor. No token, session identifier, intended URL, private pet candidate, or
email-proof material is logged or placed in public component state.

## Localization

- Add one `onboarding.php` catalogue with recursive key parity in `lang/en`,
  `lang/lt`, and `lang/ru`.
- Cover page title, progress text, every step heading/body/control, mandatory
  versus optional wording, pet choices, privacy explanations, validation,
  conflict/replay feedback, offline/loading text, and completion feedback.
- Reuse `auth.locales.*`, existing timezone identifiers, pet catalogue terms,
  and social policy labels where their semantics are exact.
- Persist locale only through `UpdateProfilePreferences`, then update session
  locale immediately so the current onboarding response and later errors use
  the selected language.
- Keep stable enum/database codes language-neutral; no translated value is
  stored.

## Accessibility And Mobile Interface

- Use a dedicated authenticated `onboarding-layout` with a skip link, calm
  brand mark, one task workspace, and logout/help after the task. It reuses
  design tokens and field/button primitives but does not expose portal
  navigation, the auth marketing story, or the guest-only language switcher.
- One `<main>` and one `<h1>`; a semantic ordered step list exposes
  `aria-current="step"` and localized textual progress.
- Steps are noninteractive in this forward-only flow; a native `<progress>`
  supplies the numeric four-step position without requiring a horizontal
  mobile gesture.
- Every input has a persistent label, description where consequences matter,
  and linked error text. Errors use text plus non-color cues and move/focus to
  a useful summary after submission.
- Buttons and links have at least 44-by-44 CSS-pixel targets, visible focus,
  logical DOM/tab order, and no hover-only behavior.
- Mutations expose target-specific loading/disabled text, dirty state,
  explicit server error state, and a Livewire offline notice. Repeated
  submission remains safe server-side.
- Privacy choices explain the real audience effect before confirmation; no
  preselected consent, precise location, public contact, or dark pattern is
  introduced.
- Mobile-first single-column layout has no horizontal overflow at 320 pixels;
  wider layouts may enhance spacing but not reorder meaning.
- Existing reduced-motion, forced-colors, contrast, auth-shell, field, button,
  and Lucide icon contracts are reused. Decorative icons are hidden from
  assistive technology.

## Test Matrix

### RED-first focused tests

| Area | Required cases |
| --- | --- |
| Registration bootstrap | Enabled and disabled verification create exactly one onboarding row, one user social actor, and one settings row; transaction rollback leaves none; replay/resolver does not duplicate identities; private defaults are exact. |
| State machine | Happy path, each forward transition, refresh/logout/login resume, duplicate submit, stale lock, future-step call, wrong user, inactive user, and completed replay. |
| Portal boundary | New incomplete user redirected before binding; legacy user and completed user pass; guest/unverified behavior unchanged; onboarding and pet-create bridge allowed; product POST/JSON fail closed; Livewire updates remain viable. |
| Destinations | Guest deep link -> login -> verification if required -> onboarding -> original internal URL; registration and disabled verification variants; no stale intended leak after completion. |
| Preferences | EN/LT/RU allowlist, invalid locale/timezone, policy denial, persistence and same-request session locale. |
| Pet relationship | Existing owner/active manager evidence, canonical pending access request evidence, `not-now`, foreign/revoked/expired manager and foreign request denial; no pet is created by onboarding. |
| Privacy | Personal actor only, boolean tampering, optimistic conflict, exact private defaults, atomic actor/settings/state update, rollback, and later settings consistency. |
| Auth hardening | Registration/resend throttles; stale authenticated login/register Livewire invocation rejects; session rotation/invalidation remains covered. |
| Localization/a11y | Recursive EN/LT/RU parity, no hardcoded first-party text, one H1/main, labels/errors/live states/step semantics, 44-pixel controls, keyboard/focus, forced colors, reduced motion, 320/375/1440 viewport and console checks. |
| Architecture/data | Class-based Livewire/separate Blade, no `@php`/Volt/raw SQL/dynamic Tailwind, route coverage/classification, explicit model factory/fillable/casts, migration up/down/reapply, fresh factory. |
| Compatibility | Existing user with no row enters portal unchanged; no legacy social/privacy row is rewritten; optional backout treats missing state as legacy exemption. |

Focused commands use the repository isolation wrapper:

```bash
php scripts/run-tests.php --compact tests/Feature/OnboardingTest.php
php scripts/run-tests.php --compact \
  tests/Feature/Auth/AuthenticationTest.php \
  tests/Feature/Auth/ConfigurableEmailVerificationTest.php \
  tests/Feature/Auth/PortalAccessBoundaryTest.php \
  tests/Feature/PetProfileDuplicateAccessRequestTest.php \
  tests/Feature/SocialRelationshipFoundationTest.php \
  tests/Feature/ArchitectureComplianceTest.php \
  tests/Feature/LocalizationTest.php \
  tests/Feature/PageIdentityStandardizationTest.php
```

After focused GREEN: PHP syntax, Pint, Larastan, the full serial Pest suite,
fresh isolated migration/seed, idempotent seed, Composer/npm audits, Vite build,
cache smokes, and connected browser checks remain required before publication.

## Migration And Backward Compatibility Strategy

1. Deploy the additive nullable-timestamp table while old code ignores it.
2. Deploy application code that creates state only for new registrations and
   treats missing state as legacy exemption.
3. Do not bulk-enrol existing users or rewrite legacy social settings.
4. Monitor registration transaction failures, onboarding redirect loops,
   transition conflicts, completion rates by non-identifying aggregate, and
   verification/onboarding return-path errors.
5. A later product decision may explicitly invite legacy accounts through an
   opt-in row; it is not part of this migration.

The schema and queries remain SQLite-compatible and avoid database-specific
check SQL. Enums are validated in application code and covered by tests;
business uniqueness uses the unique foreign key.

## Rollout Strategy

- Release behind the structural fact of a newly created onboarding row, not a
  client feature flag: existing accounts are unaffected by default.
- Run migration status/up/down/reapply in an isolated database before deploy.
- Smoke both email-verification modes, one new registration, resume after
  logout, the pet duplicate/access-request bridge, privacy defaults, completion,
  and restoration of a protected intended route.
- Keep production debug off and verify route/config/view caches and real
  Livewire asset HTTP status before enabling broad traffic.
- Promote compliance evidence only to `implemented` after focused checks; use
  `implemented and verified` only after every cited gate actually passes.

## Rollback Strategy

- Before production onboarding writes, normal migration rollback may remove
  the additive table.
- After writes exist, prefer a forward fix. Application rollback is safe only
  to a version that ignores the additive table and therefore restores the
  legacy-exemption behavior.
- If redirect loops appear, a forward fix can stop creating/enforcing rows
  while retaining progress for later recovery. Do not delete user, social,
  pet, access-request, or onboarding records.
- Reverting UI/middleware must not revert account verification, actor keys,
  social privacy choices, or pet ownership facts.

## Acceptance Criteria

- A new registration atomically receives `User`, durable onboarding state,
  one user `SocialActor`, and one privacy-first settings row.
- Conditional email verification remains authoritative and precedes
  onboarding without losing a protected intended URL.
- An incomplete newly registered user cannot access or mutate ordinary portal
  resources by direct route, binding, JSON, or Livewire action.
- The user can resume the exact server step after refresh, logout/login, or a
  duplicate submission.
- Locale/timezone, pet relationship choice, and privacy/discovery choices use
  existing canonical domain operations and server evidence.
- Creating a pet remains optional; creation, duplicate detection, and access
  requests stay entirely inside the canonical pet workflow.
- Existing users without onboarding rows retain current portal access and
  existing social/privacy values.
- The interface is fully localized in EN/LT/RU, keyboard complete,
  screen-reader meaningful, mobile-first, reduced-motion/forced-colors safe,
  and free of horizontal overflow.
- Every changed behavior has meaningful positive and negative automated tests;
  no gate is reported as passed without observed output.
- The plan and compliance evidence distinguish this implemented foundation
  from remaining requirements in later onboarding prompts.

## Executable Delivery Ledger

| ID | Dependency | Owner | Affected paths | Acceptance and verification | Status | Rollback |
| --- | --- | --- | --- | --- | --- | --- |
| ONB-01 | Repository contract and canonical docs | Principal plus ONB-A01..A10 | Audit ledger, this plan, canonical implementation plan | Current routes/state/authority/reuse, dirty ownership, design choice, compatibility, tests, rollout and rollback are recorded | completed | Revert onboarding planning additions only |
| ONB-02 | ONB-01 | Principal | Focused onboarding/auth tests | Required behavior is first observed RED for missing schema/state/middleware/UI | in progress | Revert attributable test additions |
| ONB-03 | ONB-02 | Principal | Migration, enums, model, factory, user relation | Additive schema is reversible, factory-valid, unique per user, and legacy-exempt | planned | Roll back table only before production writes; forward-fix later |
| ONB-04 | ONB-03 | Principal | Registration initializer, social actor bootstrap, auth destination flow | Both verification modes provision exact private identities atomically; intended URL survives all gates | planned | Stop provisioning/enforcement; retain committed identities |
| ONB-05 | ONB-03..04 | Principal | State Actions, middleware, Livewire persistence, route | Direct/replay/stale/wrong-user requests fail closed; resumability and legacy exemption pass | planned | Remove gate/route while preserving progress rows |
| ONB-06 | ONB-05 | Principal | Onboarding component, forms, Blade, route/page ledgers, styles | Mandatory acknowledgement/preferences/privacy and optional canonical pet bridge are usable and accessible | planned | Remove onboarding UI/gate; retain canonical account/pet/social facts |
| ONB-07 | ONB-04..06 | Principal | EN/LT/RU, architecture/security/data/frontend/testing/deployment docs, generator and changelog | Locale parity and source-of-truth checks pass; evidence matches observed status | planned | Revert attributable documentation/catalogue additions |
| ONB-08 | ONB-02..07 | Independent ONB-R11/ONB-R12 then principal | Frozen onboarding-owned diff | Every material security/code finding is reproduced, dispositioned, fixed when valid, and affected tests rerun | planned | Revert unsafe finding-specific change before publication |
| ONB-09 | ONB-08 | Principal | Repository gates and browser runtime | Applicable final gates are observed and recorded without masking unrelated failures | planned | No push on an open material finding or failed gate |
| ONB-10 | ONB-09 | Principal | Exact temporary index, commit, push | Only attributable hunks are staged; staged diff/check pass; `main` fast-forwards and push result is factual | planned | Normal revert only; never rewrite history |

Implementation order is `ONB-02` through `ONB-10`. The state may reach
`complete` for an individual user once these foundation steps pass, while this
delivery remains explicitly incomplete against any later onboarding prompt
whose functional requirements have not yet been incorporated and verified.
