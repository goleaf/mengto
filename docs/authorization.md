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
| Public publication photo | View photo and shared engagement | Active member may react/comment | N/A | Safety moderation remains separate |
| Persistent group | Discoverable identity only | Membership/content by visibility and role | Live invitation only | Explicit policy plus audit |
| Group content/poll | No independent public grant | Active member; creator/manager writes by role | Visible voters/results only when configured | Explicit group policy; no anonymous-ballot identity UI |

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

Photo interaction routes require authentication and active-account middleware.
`PhotoInteractionRequest` allow-lists action data; `PerformPhotoInteraction`
resolves the photo against the public server catalogue before
`PhotoReactionPolicy` or `PhotoCommentPolicy` permits a relational mutation.
The browser cannot create an engagement target by submitting an arbitrary key.

Precise smart-device views, management, and command submission additionally
require a fresh password confirmation. This step-up check supplements
`SmartDevicePolicy`; it never replaces owner/grant authorization.

## Group Content Authorization

`ForumGroupPolicy` owns `createContent`, `manageContent`,
`publishAnnouncement`, `uploadFile`, and `createPoll`. Topic and knowledge
policies additionally require the parent group boundary when a record is
grouped. Activity, announcement, file, and poll policies resolve their parent
group server-side.

The Livewire workspace authorizes member content before querying any child
rows. Association, file storage/download/archive, poll creation, and vote
casting authorize again in dedicated Actions. Poll closure, active-account
state, membership, trusted assignment, location scope, option ownership, and
optimistic vote version are server decisions.

## Collaborative Guide Authorization

- Public users view only `published` and `outdated` guides.
- Active members may propose corrections against visible guides.
- Guide creation, content edits, collaboration changes, correction decisions,
  rollback, locks, publication, archival, and replacement are policy methods,
  not interface assumptions.
- Community review requires scoped trust and an independent reviewer.
- Expert review requires current verified professional status and an
  independent reviewer; the administrator bypass is deliberately not applied.
- Every `KnowledgeGuideEditor` mutation reloads the record and invokes the
  policy or the corresponding authorized Action.

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

## Community Review

- Eligible trust is required to propose; verified review trust is required to
  receive an assignment.
- Requesters, subject authors, existing reviewers, and declared conflicts are
  excluded from reviewer selection.
- Only the assigned active reviewer may submit one decision before deadline.
- Only administrators may replace reviewers manually, enter moderator review,
  publish/reject/archive notes, or override a panel.
- Subject authors may respond but cannot edit or delete an approved note.
- Appeal access is limited to the panel requester or subject author.
- Every Livewire mutation reloads its target and repeats authorization in the
  Action.

See `docs/community-review.md`.

## Peer Mentorship

- Verified active users with current mentor trust, or administrators, may
  activate an opted-in profile.
- Only the profile owner manages profile/scopes.
- The requester cannot select themselves, a blocked/inactive/full mentor, or
  an unavailable scope.
- Only the selected mentor accepts or declines.
- Only participants view/message; either participant may end, block, report,
  or submit one feedback record.
- Only an uninvolved administrator validates completion.
- Reputation, trust, badges, or engagement cannot create professional
  verification.
- Every Livewire numeric argument is reloaded and authorized in the policy or
  Action layer.

See `docs/mentorship.md`.

## Persistent Groups

- Active verified members may create groups.
- Public and request-to-join groups are discoverable; unlisted groups require
  a direct URL; private groups require owner, administrator, active
  membership, or current invitation.
- Member-only content requires active membership, ownership, or platform
  administration.
- Owners and group administrators update configuration. Owner transfer is
  owner-only.
- Owner, administrator, moderator, and steward capabilities are bounded by
  `ForumGroupRole`; member management cannot remove or demote the owner.
- Close/archive, invitation, membership review, restriction, reporting, and
  audit viewing are independent policy abilities.
- Every Livewire mutation reloads the group and related record and repeats
  policy authorization inside the Action.

See `docs/groups.md`.

## Forum Journals

- Active authenticated users may create journals.
- Public journals are guest-readable. Member, expert, group, link-only, and
  private states apply explicit topic/journal policy rules before data
  selection.
- Owners manage collaborators and archive journals.
- Active editors may create/edit entries, upload media, comment, and export.
- Active viewers may read and comment but cannot mutate entries, media, access,
  or lifecycle.
- Expert-only access requires current independently verified professional
  evidence; reputation and trust do not substitute.
- Every controller and Livewire Action reloads the journal and nested subject,
  verifies parent consistency, and authorizes again.

See `docs/journals.md`.
## Event Abilities

`ForumEventPolicy` controls listing, viewing, creation, updates, cancellation,
registration, invitation, attendee management, protected access details,
updates, communication, reviews, and reports.
`ForumEventRegistrationPolicy` controls cancellation of a concrete
registration. Every Livewire mutation reloads its record and repeats policy
authorization; `#[Locked]` identifiers are only hydration protection.

Private events require an accepted, unexpired invitation. Exact location,
online access, and the emergency plan require organizer, administrator, or
confirmed/checked-in attendee access. Group events additionally enforce the
current group policy. Professional organizer presentation reads independent
current credential evidence and cannot be granted by event reputation.

## Verified Expert Sessions

- Any guest may view a published, non-archived session and approved public
  queue content.
- Only an active verified member may submit a question during the timestamp-
  derived question window.
- Pending questions are limited to their author, a currently qualified host,
  and administrators.
- Only the question author may withdraw an eligible question.
- Only the currently qualified host or an authorized administrator may
  moderate the queue; administrators do not acquire host-answer authority.
- Only the current independently credentialed host may publish or correct an
  answer in the session scope and jurisdiction.
- Active users may report only subjects they are authorized to view.
- Archive is authorized and auditable; delete, restore, and force-delete are
  not application abilities.

Every Livewire action reauthorizes the current database record. Locked IDs,
hidden controls, popularity, trust, and reputation are not authorization.
