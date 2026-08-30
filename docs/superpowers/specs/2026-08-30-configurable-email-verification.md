# Configurable Email Verification

## Goal

Allow an operator to enable or disable PawCircle email verification through
one environment-backed application configuration value without leaving the
portal, route middleware, registration flow, or persisted account state in
contradictory modes.

The deployed environment will initially use
`EMAIL_VERIFICATION_ENABLED=false`. The committed `.env.example` default
remains `true` so a new installation retains proof-of-email ownership unless
an operator deliberately disables it.

## Configuration Contract

- `EMAIL_VERIFICATION_ENABLED=true` preserves the current flow: a new account
  remains unverified, receives Laravel's verification notification, is sent to
  the verification notice, and cannot use product routes protected by the
  central portal boundary or `verified` middleware.
- `EMAIL_VERIFICATION_ENABLED=false` disables proof-of-email ownership: a new
  account receives `email_verified_at` during registration, receives no
  verification notification, and is sent directly into the authenticated
  portal.
- Application code reads the value only through `config/platform.php`; it does
  not call `env()` outside configuration.
- Configuration caching must preserve both boolean values. Automated tests
  keep the secure enabled mode as their default and override application
  configuration explicitly when exercising the disabled mode.

Disabling this setting is an explicit reduction in account-identity assurance.
It does not weaken authentication, active-account enforcement, policies,
private-resource scopes, password confirmation, CSRF, throttling, or any
professional, organization, provider, or evidence-verification workflow.

## Request And Registration Flow

`RequirePortalAccess` remains the outer boundary before route-model binding.
It continues to reject guests and inactive users in both modes. It redirects
an active unverified user to the verification notice only while email
verification is enabled.

The application's `verified` middleware alias becomes configuration-aware.
When verification is enabled, it delegates to Laravel's existing verified
email semantics. When verification is disabled, it passes an already
authenticated request onward; route-specific authentication, active-account,
authorization, throttling, and response-protection middleware remain in force.

`RegisterUser` remains the only public registration operation. In enabled
mode it creates an unverified account and dispatches the existing `Registered`
event. In disabled mode it marks the new account verified before returning it
and does not dispatch that event, preventing an unnecessary verification
email. The Livewire registration component chooses its destination from the
persisted result: unverified accounts go to the verification notice and
verified accounts go to `home`.

The verification notice must never be displayed while the setting is
disabled. A direct request or Livewire action redirects to `home`. Existing
signed verification links may safely resolve to the normal authenticated home
flow; they do not reopen or weaken product access.

## Existing Account Activation

Changing the setting does not rely on a request-time write. A dedicated,
idempotent Artisan operation activates existing accounts in bounded batches.
It has a dry-run mode and refuses to mutate accounts while email verification
is enabled.

The operation selects only users that are both active and have a null
`email_verified_at`, locks and updates each bounded batch transactionally, and
writes a non-sensitive audit record for every changed account in the same
transaction. It does not activate blocked, suspended, deleted, or already
verified accounts. Repeating the operation changes zero additional accounts.

Before the deployed database is changed, the operator creates a timestamped
SQLite backup and records the eligible count. The current observed baseline is
one user in total, one active user, and one active user awaiting email
verification. After execution, the active pending count must be zero and the
database integrity check must succeed.

## Data And Rollback Semantics

Automatically activated accounts intentionally keep their non-null
`email_verified_at` if the environment value is later returned to `true`.
Re-enabling verification applies to future registrations and any future
account that is explicitly placed into an unverified state; it does not
silently revoke access from accounts activated under the disabled mode.

The activation audit records identify accounts changed by the operational
transition without storing email addresses or other unnecessary personal
data. Database rollback is therefore a deliberate operator decision based on
the backup and audit evidence, never an automatic mass nulling of verification
timestamps.

## Testing Contract

Feature tests must prove:

- enabled mode keeps registration pending, sends the verification
  notification, redirects to the notice, and blocks protected product routes;
- disabled mode persists `email_verified_at`, sends no verification
  notification, redirects registration to `home`, and permits an otherwise
  authorized active account through both the central portal boundary and an
  explicit `verified` route;
- disabled mode redirects direct verification-notice access and its resend
  action to `home` without sending mail;
- enabled mode remains the default for the automated suite regardless of the
  deployed `.env` value;
- the activation operation reports dry-run counts without writes, changes
  only active pending users, writes matching audit evidence, is bounded, and
  is idempotent;
- the activation operation refuses writes when verification is enabled;
- guests and inactive users remain denied in both modes.

Run the focused authentication and portal-boundary tests first, followed by
Pint, Larastan, the complete sequential Pest suite, configuration and route
cache smokes, and the applicable deployment/database checks. The final report
must distinguish the code/test result from the separately observed database
activation result.

## Documentation And Traceability

The implementation updates the canonical system, security, authorization,
testing, deployment, implementation-plan, compliance-matrix, and changelog
records so statements about verified portal membership are conditional on the
enabled mode. Generated documentation is refreshed only through its
first-party generator.

## Non-Goals

- No second translation system, new verification copy, or visual redesign.
- No change to password, login, session, account-status, policy, temporary
  token, professional credential, organization, provider, or pet-evidence
  verification semantics.
- No schema migration and no destructive rebuild of the deployed database.
- No automatic reversal of verification timestamps when the setting is
  re-enabled.
