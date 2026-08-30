# EVENT-S02 Commercial Participants Discovery

Date: 2026-08-30  
Status: discovery only; no production, migration, test, translation, plan, or
shared-document changes were made.

## Scope and requirement manifest

The authoritative source is section 17, `vendors, sponsors, and marketplace
integration`, in `docs/requirements/forum-source-prompt.md:60735-60823`.
Its generated, still-planned `event-vendor-sponsor` atoms are exactly
`event.vendor.sponsor.0001` through `event.vendor.sponsor.0059`
(`docs/traceability/forum-requirements-matrix.md:35590-35648`):

| Source section and range | Atom IDs | Required boundary |
| --- | --- | --- |
| `vendor-001`, lines 60738-60753 | 0001-0014 | seller/organization application; categories, booth needs, documents, insurance, payment, staff, equipment, electricity, safety, approval |
| `vendor-002`, lines 60756-60760 | 0015-0017 | reuse marketplace seller verification; prohibit a weaker event-only identity |
| `vendor-003`, lines 60763-60767 | 0018-0020 | enforce marketplace prohibited-product policy even for physical sales |
| `vendor-004`, lines 60770-60782 | 0021-0031 | area/size/table/electricity/setup/teardown/staff/accessibility/inspection assignment |
| `vendor-005`, lines 60785-60796 | 0032-0040 | separate booth fee, ticket, deposit, product sale, donation, and sponsor-payment purposes |
| `sponsor-001`, lines 60799-60812 | 0041-0052 | sponsor/organization, contribution, public display/logo/sessions/prizes/disclosure/contract/restrictions |
| `sponsor-002`, lines 60815-60823 | 0053-0059 | no attendee-data disclosure without consent, adoption or safety influence, secret scoring influence, or undisclosed recommendation |

P27 has the same delivery scope and its three acceptance constraints in
`docs/plans/portal-events-completion-master-plan.md:722-737`. It depends on
P02 + P03 + P16 (`:102`); it must not absorb P15 venue-area work, P19 payments,
P20 tickets, P24 exhibitions, P25 speaker sessions, or P26 scoring. The
current event status honestly records competitions/vendors/volunteers as not
implemented (`docs/events/requirements.md:13-16`), and the event-specific
overview says vendor applications, booths, deposits, contracts, placements,
disclosures, and isolated ledgers are absent (`docs/events/vendors-and-sponsors.md:3-9`).

## Current-state and reuse map

| Existing authority | Reuse | Do not reuse it as |
| --- | --- | --- |
| `ForumEvent` and occurrences | One canonical event and event/occurrence timing; current event history/idempotency/audit conventions. `ForumEvent` is explicitly the only event aggregate (`docs/events/index.md:9-10`). | A seller, sponsor, booth, contract, payment, or attendee-export authority. No commercial models, migrations, policies, Actions, factories, or tests exist. |
| `ForumEventTeamMembership` / `ForumEventPolicy` | Current organizer/team authorization. `VendorCoordinator` is an existing event role; organizer membership remains organization-scoped. | Vendor or sponsor identity, a right to participant data, safety actions, judging, adoption decisions, or a generic `manageTeam` substitute for commercial review. |
| `Organization`, membership, restrictions and audit | An organization can be an accountable commercial applicant/sponsor and supplies current membership, active/suspended state, verified status and `manageMarketplace`/`manageFinance`/`manageSafety` separation. `Organization::isVerified()` also expires correctly. | Proof that an individual seller is marketplace-verified; a sponsor contract/contact record; permission for every organization member to inspect commercial documents. |
| `Place` / `Venue` | Link the event's canonical venue and use its privacy/access rules. Area records may be event-scoped operational subdivisions of that venue. | A public booth map, a copied exact address, or delegated right to reveal/use a private place. Existing tests distinguish attendance access from event-operations access. |
| `Listing`, `ListingSafety`, marketplace reservations/orders | Existing listing category vocabulary, ListingSafety prohibited-item/text assessment, listing moderation and ordinary marketplace order/deposit semantics can be called as policy inputs. | A seller-verification aggregate. `is_verified_seller` is a per-listing boolean, `CreateListing` always initially writes `false`, and there is no durable verified seller profile/expiry/document decision. It therefore cannot alone satisfy vendor-002. Marketplace `Order` is also a listing sale ledger, not an event booth/deposit/sponsorship ledger. |
| Existing event Action patterns | `DB::transaction`, locked reload, payload-bound idempotency conflict detection, immutable history, and after-commit notifications from the current event lifecycle. | A claim that commercial notifications, expiration sweep, or commercial projection already exists. |

The first implementation decision must therefore be explicit: either establish
the promised reusable marketplace seller-verification authority before allowing
vendor approval, or block individual vendors as `verification_unavailable`.
Approving from `Listing::is_verified_seller` would create exactly the weaker,
listing-dependent identity forbidden by atoms 0016-0017.

## Normalized durable design

Keep commercial participation event-scoped and keep a natural person
accountable for every mutable record. An organization is optional acting
context, never an unverified replacement identity.

1. `EventCommercialPackage`: `forum_event_id`, `kind` (`vendor` or `sponsor`),
   stable key, version, lifecycle (`draft`, `offered`, `retired`), capacity,
   published eligibility/fee terms, currency, and private internal notes.
   `EventCommercialPackageBenefit` defines a package's named benefits and
   whether public disclosure is mandatory; it does not grant roles.
2. `EventVendorApplication`: event, applicant `user_id`, optional acting
   `organization_id`, chosen vendor package, application version, product
   category IDs/snapshot, booth/equipment/electrical/staff/accessibility
   requirements, required-document state, verification snapshot reference,
   status, submitted/reviewed/expiry/cancel timestamps, and an opaque
   idempotency key. Store documents as private records/files, not URLs or a
   mutable JSON blob.
3. `EventVendorReview`: immutable, append-only review decisions/requests for
   changes/rejection reasons, reviewer, decision timestamp and requirement
   snapshot. `EventVendorContact` has named, minimal, purpose-scoped contacts
   (operations, emergency, finance), verified owner/organization relation and
   revocation timestamps.
4. `EventCommercialArea`: event and optional occurrence, opaque public label,
   capacity/physical constraints, setup/teardown windows, electrical/table/
   accessibility/safety requirements, and private operational location notes.
   `EventVendorAssignment` links an approved application to an area and stores
   the accepted operational snapshot, inspection state and cancellation/
   expiry timestamps. It is the booth assignment; never put booth data in a
   generic registration or team-membership row.
5. `EventSponsorship`: event, accountable `user_id`, optional sponsor
   `organization_id`, sponsor package, contribution type/value/currency,
   lifecycle, acceptance/termination timestamps, restriction snapshot and
   private contract reference/checksum. `EventSponsorshipBenefit` materializes
   accepted package benefits. `EventSponsorshipPlacement` is a separately
   reviewed concrete placement (event page, permitted session reference, prize
   acknowledgement, or physical area) with mandatory disclosure text/key and
   publication dates.
6. `EventCommercialHistory` (or an extension of `ForumEventHistory` only if
   its schema can express commercial subject and idempotency safely) records
   append-only transitions without contacts, documents, contracts, or exact
   locations. A dedicated record is the clearer boundary if several subject
   kinds are introduced.

Suggested state graphs:

```text
vendor application: draft -> submitted -> under_review
  -> changes_requested -> submitted | approved -> assigned -> completed
  -> rejected | withdrawn/cancelled | expired

sponsorship: draft -> proposed -> under_review -> offered -> accepted
  -> active -> fulfilled -> closed
  -> rejected | withdrawn/cancelled | expired | terminated

placement: draft -> reviewed -> scheduled -> published -> ended/removed
```

Terminal records stay attributable. Cancellation/expiry revokes current area,
placement and contact access; it does not delete accepted terms, review
decisions, payment references, inspection facts, or disclosure history.

## Integrity, concurrency and transaction boundary

- Use additive migrations only. Foreign keys should `restrictOnDelete()` for
  event/application/package/area/contracts and `nullOnDelete()` only for an
  actor that may be erased while its historical decision must survive.
- Enforce one active submitted/reviewable application per `(event,
  applicant_user_id, acting_organization_id, vendor-package)` with a nullable
  `active_identity` unique key (cleared only at a terminal state), rather than
  a racy preflight check. Require a single accountable `user_id`; validate an
  optional organization is active and the actor has current marketplace
  authority there.
- Area assignment must enforce one active assignment per physical resource.
  Model multiple booths as separate areas/resources; unique `(area_id,
  active_identity)` prevents two allocations. Index review queues by
  `(forum_event_id,status,submitted_at,id)`, applicant workspace by
  `(applicant_user_id,status,updated_at,id)`, area schedule by
  `(forum_event_id,occurrence_id,status,setup_starts_at,id)`, and sponsor
  public projection by `(forum_event_id,status,public_starts_at,id)`.
- Give every mutation a client idempotency key unique in its subject scope and
  compare the stored normalized request/transition before returning a prior
  success; reuse with a different payload must be a validation conflict.
  Lock the event, relevant application/sponsorship and area/package capacity
  rows inside one short transaction. Re-authorize after reload.
- Approval must atomically re-check seller verification, product categories,
  documents/insurance requirements, event status and package capacity before
  writing its immutable review and advancing state. Assignment/inspection is
  a subsequent locked operation. A verification expiry, organization
  suspension, event cancellation, or package retirement cannot be skipped by
  an already-open browser form.
- Do not create an event payment implementation in this package. P27 can
  record purpose-separated external/payment-reference placeholders only after
  the authoritative payment package exists; it must never map booth fees,
  deposits, sponsorship contributions, tickets, donations, and product sales
  into one `Order` or one amount field. `docs/events/index.md:46-49` says
  provider-backed event payment is not verified.

## Authorization and controlled projections

| Capability | Allowed actor | Explicit denial |
| --- | --- | --- |
| Draft/submit/withdraw a vendor application | active verified accountable applicant; acting organization needs current marketplace authority | another seller, stale/removed organization member, normal attendee, vendor coordinator acting as applicant |
| Review/approve/reject/request changes | administrator or current event `VendorCoordinator`, gated by responsible-organization active membership and appropriate organization marketplace/safety capability | applicant's own review, finance-only member, sponsor, ordinary organizer lacking the scoped role |
| Assign booth/record setup or safety inspection | coordinator plus operations/safety authority, respectively; current place event-operations access remains separately required for private venues | attendee, vendor without delegated operational role, sponsor, attendance-only place grantee |
| View application docs/contacts | applicant; minimum coordinator/reviewer role; finance contact only for finance-purpose rows; safety contact only for safety operations | public, attendees, other vendors, sponsors, global organization members, generic team viewers |
| Create/manage sponsor proposal/contract | accountable sponsor representative; organizer administrator/coordinator with organization finance authority; legal/finance reviewer as separately scoped role | sponsor gets no event-team role automatically, vendor, attendee, safety/adoption/judging staff absent independent role |
| Publish a sponsor placement | authorized commercial reviewer after accepted sponsorship, permitted benefit and disclosure validation | any undisclosed placement, expired/terminated contract, recommendation/professional profile presentation without label |
| Read public projection | event viewers only after normal event visibility has passed | contact, contract, contribution amount unless chosen public, documents, internal restriction/review/area/security detail, attendee data |

Public presenters should return only event-visible sponsor/vendor display name,
approved logo/media through an existing safe media boundary, public category or
placement label, optional public area label, explicit sponsorship disclosure,
and date-bounded availability. They must use a separate allowlisted presenter
and event visibility query; no model serialization, broad relation loading,
or shared cache key without event visibility/locale/version. Vendor application
status, exact setup locations, contacts, documents, insurance, fees/deposits,
contract, private notes and all attendee/registration relations stay absent.

Sponsorship must never be implemented as a `ForumEventTeamMembership` or as a
permission override. Such a connection would bypass the source's hard bans on
attendee-data, adoption, safety and secret scoring influence. A sponsor who
legitimately performs another role requires that separate, disclosed,
authorized record and policy path.

## Contacts, expiry, cancellation and notifications

Contacts are purpose-bound private data. Encrypt recoverable telephone/email
or private instructions, hide them from serialization, scope queries before
load, and expose a platform-mediated contact action where direct disclosure is
not necessary. Do not copy private organization metadata or the venue's exact
address into commercial records. Preserve only role/title and revocation facts
in audit history.

Expiry is evaluated both synchronously before every commercial mutation and by
an idempotent scheduled transition. The worker finds only due open rows using
the indexed expiry fields, locks each row, confirms it is still open, changes
state once, revokes active assignment/placement visibility and writes history.
Event cancellation/safety suspension must run a dedicated transactional
commercial-cancellation Action; it should terminate operational access and
public placement immediately while preserving a truthful contract/payment
recovery state for the finance package.

Create a `commercial_notification_deliveries` outbox/deduplication record
unique on `(commercial_subject_type, commercial_subject_id, event_type,
transition_version, recipient_user_id)`. Insert it in the same transaction as
the history transition; send notification after commit. This prevents a retry,
browser replay, sweep/job overlap, or event cancellation cascade from sending
multiple approvals, expiries or cancellations. No sponsor notification may
address an attendee or imply an attendee-data export.

## Factories, seeds and adversarial proof

Every new model needs a factory with explicit states: vendor draft/submitted/
reviewable/approved/rejected/expired/cancelled; verified/unavailable seller;
area available/assigned/inspection-failed; sponsorship proposed/accepted/
active/expired/terminated; and disclosed/undisclosed/removed placement.
Relationship helpers must create a deliberately selected event, accountable
user, optional organization, and package rather than hidden graphs.

Add an environment-gated, fixed-key seeder scenario for one approved verified
vendor with a safe category and one disclosed sponsor. Run fresh migration,
seed, repeat seed, and assert counts/no duplicate active identities. Do not
seed contracts, insurance documents, direct contacts, payment data, real logos
or private venue data. Existing `ForumEventDemoSeeder`, `OrganizationAuthoritySeeder`
and `MarketplaceExpansionSeeder` are inputs only; they do not prove this scope.

Minimum adversarial feature/Action tests:

- individual and organization applicants cannot submit/approve after seller or
  organization verification expires, organization suspension, membership
  removal, event archive/cancellation, stale idempotency or payload replay;
- direct physical-sale categories run the same prohibited-product assessment
  and blocked goods cannot reach review approval, assignment or public display;
- two approval/assignment requests for the final package/area cannot overbook;
  duplicate active applications and conflicting idempotency keys fail;
- review/inspection/assignment enforce current coordinator, marketplace,
  finance, safety and place-operation authorities separately;
- a sponsor cannot enumerate registrations, contacts, exports, safety cases,
  adoption records or competition scores, including through eager-loaded
  presenter/resource/Livewire state; an independently assigned role is tested
  under its own policy and disclosure;
- public/unlisted/private/organization/invitation event projections neither
  leak a vendor contact, contract, exact location nor cache data across event
  visibility/locale; expired/terminated placements disappear;
- cancellation and expiry are atomic, repeat-safe, preserve immutable history,
  revoke current access, and yield one notification delivery per recipient;
- seed repeatability, factory validity, document private-storage authorization,
  EN/LT/RU key parity, keyboard-visible sponsorship disclosure, no-N+1 review
  queue and bounded public projection queries all pass.

## Integration and rollback risks

1. The advertised marketplace seller-verification reuse is currently an
   unresolved prerequisite, not an implementable service. Shipping an
   event-only boolean or approving from a listing field violates vendor-002.
2. P15's venue-area topology and P19 payment truth are absent. Introducing
   booth geometry or payment settlement here risks duplicate venue/payment
   authorities. Use limited operational area labels and payment references
   only, then expand after the owning packages are verified.
3. Organization permission changes immediately invalidate commercial authority;
   all Actions and queued sweeps must reload membership/restrictions. Never
   cache authorization decisions or contact rows across that change.
4. A public sponsor card is a privacy and endorsement boundary. Cache
   invalidation must occur on disclosure/placement/contract/status change;
   rollback must unpublish cards first, then forward-fix data rather than drop
   contractual/audit records.
5. New private files and contacts raise deletion/retention/legal-hold
   dependencies owned by P29. Until that package exists, record only a
   controlled private-file reference and document retention as pending; do not
   promise deletion/export behavior.

## Smallest durable implementation recommendation

Do not begin P27 production work until its seller-verification authority and
P15/P19 ownership decisions are recorded. The first coherent slice after that
decision is vendor applications only: immutable application/review history,
verification/prohibited-goods gate, private documents and contacts, and no
payments, public vendor directory, booths, sponsor contracts, placements, or
attendee exports. It establishes the source's security floor without faking
unavailable payment or venue capabilities. A second slice can add areas and
assignments after P15; sponsorship packages/contracts/placements/disclosures
should be a third slice with its own independent policy and public-projection
tests.
