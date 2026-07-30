# Authorization

## Principles

- Authentication and authorization are separate checks.
- A route being reachable does not grant resource access.
- A hidden or disabled control is not security.
- `#[Locked]` state is not security.
- Browser actor, owner, user, tenant, role, and permission identifiers are
  untrusted.
- Sensitive query scoping happens before serialization or presentation.

## Actor Resolution

`ForumActor` is the compatibility adapter between authenticated `User` records
and existing string actor keys. It may expose a non-privileged guest identity
for public presentation, but a guest key can never create, mutate, manage,
share, export, or control a protected resource.

Policies inspect the passed `User` and compare `User::actor_key` to ownership
or grant records. They do not rely on an ambient fixed identity.

## Capability Matrix

| Resource | Public view | Owner/member | Scoped recipient | Administrator |
| --- | --- | --- | --- | --- |
| Published forum/knowledge | Yes | Create/engage/manage own | N/A | Moderate with audit |
| Expert/listing public profile | Yes | Manage own / participant transitions | Booking participant only | Moderate with audit |
| Lost/found public case | Privacy-safe only | Owner/coordinator controls | Assigned volunteer subset | Safety moderation |
| Medical record | No | Full owner-selected capabilities | Selected sections/actions until expiry | No implicit clinical bypass |
| Care journal | No | Owner-selected capabilities | Selected sections/actions until expiry | No implicit family bypass |
| Smart device | No | Owner-selected view/control | Selected fields/actions until expiry | No implicit camera/GPS bypass |
| Exact GPS/camera/door | No | Explicit high-risk capability | Time-window capability | Step-up and audit required |

## Required Policy Methods

Use applicable methods:

- `viewAny`, `view`, `create`, `update`, `delete`, `restore`, `forceDelete`
- `manage`, `moderate`, `publish`, `approve`
- `download`, `upload`, `share`, `export`
- `control`, `viewPreciseLocation`, `viewCamera`
- domain transitions such as `reserve`, `cancelReservation`, `coordinate`,
  `completeTask`, or `recordDose`

Every method has positive and negative tests for owner, non-owner, privileged
role, wrong scope, inactive/blocked actor, missing relationship, and invalid
state as applicable. The cross-policy contract is exercised by
`tests/Feature/Auth/PolicyMatrixTest.php`; workflow tests cover route-level
authorization and domain transitions.

Precise smart-device views, management, and command submission additionally
require a fresh password confirmation. This step-up check supplements
`SmartDevicePolicy`; it never replaces owner/grant authorization.

## Temporary Grants

A grant defines:

- owner and resource;
- recipient identity or anonymous token purpose;
- visible sections/fields;
- permitted commands/actions;
- start and expiry;
- revocation and last access;
- audit metadata.

Revocation invalidates server access and dependent cache keys immediately.
Downloaded historical copies cannot be remotely revoked, so exports disclose
that consequence and use short-lived links.

## Failure Behaviour

- Guests are redirected to login for normal protected web pages.
- Authenticated unauthorized actors receive 403 or a privacy-preserving 404
  when existence itself is sensitive.
- JSON receives the stable error envelope and correct HTTP status.
- Authorization failure never mutates data and never exposes protected values
  in validation errors or logs.
