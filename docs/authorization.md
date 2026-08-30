# Authorization

## Principles

- Authentication and authorization are separate checks.
- A route being reachable does not grant resource access.
- A hidden or disabled control is not security.
- `#[Locked]` state is not security.
- Browser actor, owner, user, tenant, role, and permission identifiers are
  untrusted.
- Sensitive query scoping happens before serialization or presentation.
- Portal authentication is an outer boundary: every product route, mutation,
  token share, Livewire upload/preview, and product-media response requires an
  active authenticated account. Product policy checks still apply afterward.
- Verified-email state is an additional outer-boundary requirement only while
  configured email verification is enabled. Disabling it does not weaken
  active-account, policy, scoped-grant, or step-up authorization checks and
  does not represent independent proof that the member owns the address.

## Actor Resolution

`ForumActor` is the compatibility adapter between authenticated `User` records
and existing string actor keys. Legacy guest identity support does not grant
route access and cannot create, read, mutate, manage, share, export, or control
a product resource.

Policies inspect the passed `User` and compare `User::actor_key` to ownership
or grant records. They do not rely on an ambient fixed identity.

Administrator status is not a global authorization override. A policy may
grant a named moderation or operational capability explicitly, but private
owner resources and ordinary private topic visibility do not inherit access.

## Capability Matrix

| Resource | Portal member view | Owner/member | Scoped recipient | Administrator |
| --- | --- | --- | --- | --- |
| Published forum/knowledge | Yes | Create/engage/manage own | N/A | Moderate with audit |
| Expert/listing public profile | Yes | Manage own / participant transitions | Booking participant only | Moderate with audit |
| Lost/found public case | Privacy-safe only | Owner/coordinator controls | Assigned volunteer subset | Safety moderation |
| Medical record | No | Full owner-selected capabilities | Selected sections/actions until expiry | No implicit clinical bypass |
| Care journal | No | Owner-selected capabilities | Selected sections/actions until expiry | No implicit family bypass |
| Smart device | No | Owner-selected view/control | Selected fields/actions until expiry | No implicit camera/GPS bypass |
| Exact GPS/camera/door | No | Explicit high-risk capability | Time-window capability | Step-up and audit required |
| Public publication photo | View photo and shared engagement | Active member may react/comment | N/A | Safety moderation remains separate |
| Pet primary photo | Current photo follows pet view policy | `manage-media` upload/replace/remove/restore | Historical photo only during authorized recovery | Explicit policy plus audit |
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

- Active portal members view only `published` and `outdated` guides unless a
  stronger policy grant applies.
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
- authenticated recipient identity and token purpose;
- visible sections/fields;
- permitted commands/actions;
- start and expiry;
- revocation and last access;
- audit metadata.

Revocation invalidates server access and dependent cache keys immediately.
Downloaded historical copies cannot be remotely revoked, so exports disclose
that consequence and use short-lived links.

For an account-bound grant, the stored recipient key must match the current
authenticated actor before any counter or audit mutation. An unbound token is
still usable only inside the authenticated portal, and its audit actor is the
actual bearer rather than the grant's display metadata.

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
- Portal-visible journals are readable by active verified members. Expert,
  group, link-only, and private states apply explicit topic/journal policy
  rules before data selection.
- Owners manage collaborators and archive journals.
- Active editors may create/edit entries, upload media, comment, and export.
- Active viewers may read and comment but cannot mutate entries, media, access,
  or lifecycle.
- Expert-only access requires current independently verified professional
  evidence; reputation and trust do not substitute.
- Every controller and Livewire Action reloads the journal and nested subject,
  verifies parent consistency, and authorizes again.

See `docs/journals.md`.
## Organization Abilities

`OrganizationPolicy` separates tenant viewing, membership administration,
event organization, finance, safety, marketplace, shelter, restriction, and
read-only audit abilities. A current active membership is required unless a
deliberate platform-administrator path applies. Specialist roles do not imply
one another.

All organization routes require an active verified account. The directory is
query-scoped with `accessibleTo()` before models reach presentation. Workspace
and invitation components reload and authorize on hydration; every mutation
authorizes again inside its Action and transaction. Former members and wrong-
organization actors fail closed.

Organization-bound event visibility requires current membership. Event
creation/invitation/registration/check-in/publication paths independently
consult the responsible organization's current capability restrictions.
Assigned safety/welfare/medical staff retain only the explicit emergency
participant-data override; a restricted owner does not.

## Place And Venue Abilities

`PlacePolicy` separates public/detail viewing, creation, management, event use,
access administration, and exact-location reveal. Owners and current
organization roles with `canManagePlaces()` may manage a place. Ordinary
organization members may view organization-visible records but cannot update,
grant access, or use them for an event. Former members fail closed at use time.

An active public place may be used for an event. Unlisted, organization, and
private places additionally require current management authority or an active
`event_operations` grant. Exact reveal accepts any active purpose-specific
exact-location grant, reauthorizes inside the Action transaction, and records
the account, grant, event context, channel, and purpose in the audit timeline.

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

- Any active verified portal member may view a published, non-archived session
  and approved portal-visible queue content.
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

## Forum Topic Lifecycle

- Public visibility is evaluated before lifecycle presentation. Topic authors
  do not bypass private group or journal membership, and administrators do not
  receive an implicit bypass to ordinary private topics.
- Owners may update, request reopening, bump, archive, remove, or restore only
  eligible owned topics and only when category rules and legal holds permit.
- Readers may request an update only for a topic they may already view.
- Administrators may moderate state, review requests, redirect/merge, and
  manage legal holds, but policy checks still protect destination visibility
  and private topic access.
- Every HTTP and Livewire mutation reloads the topic/request, authorizes,
  validates current state and optimistic version, and then invokes the Action.
- `#[Locked]` protects hydration identity only; it does not grant access.

See `docs/topic-lifecycle.md`.

## Moderation Case Closure

- Only an administrator allowed to update the concrete moderation case may
  close it.
- `CloseForumModerationCase` authorizes both the caller-supplied model and the
  current row after acquiring its lock.
- An unresolved case cannot close. A stale optimistic version or a different
  request against an already closed case fails without an audit write.
- Replaying the original closure request key returns the existing closed case
  and does not create a second report event.
- The closure key is hidden from serialization and is never accepted as an
  authority substitute.

## Progressive Pet Profile Completion

- Opening the management component requires `PetProfilePolicy::update` for the
  current authenticated individual; a locked profile ID is only a hydration
  guard.
- Every descriptive-step mutation delegates to `UpdatePetProfileStep`, which
  reloads the profile through `managedBy`, repeats `update` authorization, and
  checks the locked current row before writing.
- Photo, owner/manager, privacy, and lifecycle steps retain their more specific
  existing policy abilities and Actions.
- The documents step delegates to `RecordPetProfileFact` with
  `microchip-record`. Both detailed loading and mutation require
  `change-microchip`; generic `edit-basics` does not reveal or modify the
  protected record. A manager who may edit other profile sections but lacks
  this critical permission receives a localized non-editable explanation
  instead of protected inputs or a mutation control.
- A normal `?step=` link and the mutation-free skip operation have no authority
  effect.

## Place Submission Capability Matrix

Active verified submitters may create, view their own record, answer an
information request, choose a member-visible duplicate, continue as distinct,
or withdraw while state permits it. Unrelated members, blocked actors,
inactive accounts, and unverified accounts receive no submission capability.

An active manager may view/review only when the canonical organization,
linked place, or duplicate candidate is in that manager's current scope.
Managers may request information or link that candidate; they cannot approve,
reject, publish, merge, restore, or act on their own submission. Those critical
decisions require a different active verified administrator. Every Action
rechecks policy against locked current state.
