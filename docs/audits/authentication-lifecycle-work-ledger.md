# Authentication Lifecycle Work Ledger

Status: discovery in progress on 2026-08-30.

This ledger owns the bounded read-only specialist work for the account-
authentication lifecycle delivery. The principal agent is the sole integrator
and production-code writer. Every specialist must distinguish confirmed
evidence from suspicion and return: inspected scope; exact files, symbols,
routes, tables, and workflows; severity-ranked confirmed findings; suspected
findings requiring principal validation; missing evidence; implementation
order; exact tests/commands; and change risks. No discovery specialist may
edit repository files.

## Protected Baseline

- Branch: `main`, tracking `origin/main`, initially ahead by three commits.
- Initial tree: 478 staged entries, 31 unstaged entries, and six untracked
  entries. Existing staged and unstaged hunks are user- or parallel-worker-
  owned unless exact task attribution is established.
- Auth-adjacent files already modified before this delivery include
  `AuthenticateUser`, `RegisterUser`, the login/register/verification
  Livewire components, `VerifyEmailController`, `User`,
  `AppServiceProvider`, `routes/web.php`, auth catalogues, auth tests,
  configuration, seeders, canonical docs, and generated compliance evidence.
- Task publication must use a temporary `GIT_INDEX_FILE`; no existing index
  entry may be reset, discarded, or absorbed.

## Analysis Specialists

| Specialist | Exclusive primary scope | Expected output | Dependencies | Status |
| --- | --- | --- | --- | --- |
| Login and Session Security Analyst | Login/remember/logout/session rotation, CSRF lifecycle, web/API guards, account-state middleware, session/cookie configuration, login tests | Login/session threat model; exact defects; config/code/test changes | Repository contract and protected baseline | queued |
| Registration and Invitation Workflow Analyst | Public registration plus account/organization invitation issuance, acceptance, revocation, expiry, recipient/tenant/role binding, transactionality | Invitation state machine; bypass/race findings; implementation and tests | Repository contract and protected baseline | queued |
| Password and Verification Analyst | Password broker, reset/confirmation, email verification routes/controllers/Livewire/notifications, localized messages/tests | Token lifecycle; required changes; success/negative/expiry/replay tests | Repository contract and protected baseline | queued |
| Magic Link and One-Time Token Analyst | Cross-cutting inventory of magic/access/invitation/action/API tokens, digest/schema/log/URL/response behavior, replay/concurrency | One-time-token inventory; ranked findings; standard design and suite | Repository contract and protected baseline | queued |
| MFA and Recovery Analyst | Installed MFA capability, enrollment/challenge/trusted devices/recovery codes/reset/admin recovery and absence evidence | MFA state/threat model; applicable fixes; enrollment/challenge/recovery tests | Repository contract and protected baseline | queued |
| Authentication Rate-Limit and Abuse Analyst | Limiter definitions and middleware for login/register/invite/reset/verification/magic/MFA/recovery/API auth; proxy/store behavior | Key/threshold/store matrix; definitions; deterministic abuse tests | Repository contract and protected baseline | queued |
| Authentication Test and State-Machine Analyst | Auth requirements, all auth tests/factories/seeders, observable states, missing expiry/replay/concurrency coverage | Complete behavior matrix; missing factories/states; exact verification commands | Reports may be reconciled after all domain specialists return | queued |

## Independent Reviewers

These reviewers start only after the attributable implementation diff is
frozen. They must remain independent from all discovery/implementation work
and read behavior, requirements, configuration, and negative tests rather
than relying on green output.

| Reviewer | Exclusive review lens | Required finding format | Dependency | Status |
| --- | --- | --- | --- | --- |
| Authentication Security Reviewer | Bypass, fixation, enumeration, account state, cookies, direct HTTP/Livewire invocation, environment defaults | Severity, exact location, failure scenario, evidence, correction/test, verified protections | Frozen attributable diff | blocked on implementation |
| Token Replay and Concurrency Reviewer | Invitation/reset/verification/magic/recovery lifecycle, conditional writes, constraints, transactions, logs | Replay/concurrency finding set and readiness verdict | Frozen attributable diff | blocked on implementation |
| Authentication Regression Reviewer | Intended journeys, redirects, remember behavior, notifications, seeded roles, a11y and EN/LT/RU messages | Regressions, missing journey tests, acceptance recommendation | Frozen attributable diff | blocked on implementation |

## Principal Reconciliation Rules

1. Reproduce every high/critical claim against the current combined working
   tree before accepting it.
2. Record accepted findings as stable `AUTH-LC-*` entries in
   `docs/implementation-plan.md` before production-code changes.
3. Preserve explicit non-applicability when the product has no implemented
   flow; do not invent MFA, magic login, impersonation, invite-only
   registration, API authentication, or tenant semantics without a canonical
   requirement.
4. Add an observed failing PHP test before each behavior correction, then run
   the smallest green slice before broader checks.
5. Record independent-review dispositions here and in the canonical plan;
   never promote compliance status without fresh verification.
