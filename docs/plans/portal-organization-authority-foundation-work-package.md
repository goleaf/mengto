# Portal Organization Authority Foundation

Implementation date: 2026-08-03

Status: implementation complete; final publication pending

## Scope

This package establishes one organization tenant authority shared by the
portal and `ForumEvent`. It does not create parallel user, event, payment,
marketplace, shelter, notification, or location records.

Implemented:

- `Organization`, current/expiring memberships, account-bound invitations,
  independent restrictions, and append-only audit records;
- owner, administrator, event, finance, safety, marketplace, shelter, member,
  and read-only audit roles with independent Policy abilities;
- verified, suspended, and archived organization states;
- eight independently persisted operational capability restrictions;
- idempotent create, invite, invitation response, member removal,
  restriction, and suspension Actions;
- one authenticated class-based Livewire directory, workspace, and signed
  invitation response flow using the existing portal shell;
- responsible organization linkage for the canonical `ForumEvent` aggregate;
- organization-only event visibility, builder selection, former-staff
  revocation, invitation membership checks, publication, registration,
  participant-data, and check-in restrictions;
- EN, LT, and RU labels, factories, role states, and an environment-gated,
  repeatable demo scenario.

## Security Boundaries

- Browser organization IDs are untrusted and revalidated against current
  membership, role, organization status, and capability restrictions.
- Removed or expired members cannot recover access through stale event-team
  rows. Authored history remains attributable.
- Emergency participant access requires both current organization membership
  and an assigned safety, welfare, or medical event role.
- Invitation tokens are hashed at rest, account-bound, expiring, signed,
  single-use, and absent from Livewire public properties and snapshots. The
  account-bound response keeps only an encrypted temporary session value and
  removes it after the response is committed.
- Ordinary members do not receive member email addresses, restriction reason
  codes, audit metadata, or private event participant data.
- Every Livewire mutation reloads server-owned records and delegates to an
  Action that authorizes again inside its transaction.

## Data And Query Contract

The additive migrations create five organization tables and add one nullable,
indexed responsible-organization foreign key to `forum_events`. Unknown legacy
ownership remains null; no organization is inferred from names, email domains,
event creators, seller activity, or display strings.

The organization directory uses `accessibleTo()`, an aggregate membership
count, explicit selected columns, deterministic bounded pagination, and a
prepared array projection before Blade. Removing the unused owner eager load
reduces its data path from three statements to two: paginator count plus one
organization query with the membership aggregate subquery. The query-budget
test keeps directory rendering at no more than 12 statements and permits at
most one additional statement when volume grows from one to twelve tenants.

Workspaces eager load no more than 100 memberships and 50 active restrictions.
Email and restriction projections are loaded only for an authorized manager.
Event builder options use one constrained Eloquent query with active
membership, event-manager role, organization status, and `create_events`
restriction checks.

## Demo World

`OrganizationAuthoritySeeder` adds three organizations, four memberships, one
pending invitation, nine restrictions, and six audit events. It is limited to
configured demo environments and is idempotent under repeated complete seeding.

## Verification

- organization authority: 14 tests, 109 assertions;
- organization plus event lifecycle/workflow: 39 tests, 702 assertions;
- targeted production-code Larastan: zero errors;
- fresh database: 121 migrations, 205 tables, seed and repeat seed exit `0`;
- complete migration cycle: 121 applied, zero remaining after rollback, 121
  reapplied, seed and repeat seed exit `0`.

Composer strict validation and audit, npm audit with zero vulnerabilities,
Vite 8.2.0 production build, Laravel cache compilation, localization scan,
and deterministic forum source, manifest, and requirements checks also pass.
The final full-suite and Pint rerun remain publication gates; their observed
results must replace this paragraph before the package is called published.

## Deliberate Follow-On Scope

- Active organization selection and the global switcher remain P05 context
  work; this package supplies the tenant query and Policy contract only.
- Canonical places, organization locations, venues, private-location grants,
  and public projections remain P03 and P04.
- Payment recipient, settlement, result-entry, marketplace, and shelter
  mutations must consume these roles and restrictions when their downstream
  packages create the corresponding durable domains.
- Verified-organization review, expiry, dispute, and renewal workflows remain
  open, as do invitation notification delivery, revocation, resend, and audit
  presentation.
- Richer profile/edit/archive flows, audit export, browser coverage, and exact
  `portal.*`/`event.*` evidence promotion remain open.

The package is a foundation, not a claim that all of P02 is complete. Do not
expand its completion status when a downstream ability exists only as an enum,
Policy method, or interface control.
