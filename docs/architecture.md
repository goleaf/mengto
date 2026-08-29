# PawCircle Architecture

## System Shape

PawCircle is one Laravel 13 application with server-rendered Blade pages,
class-based Livewire 4 components for intentional interactive flows, Eloquent
persistence, progressive JavaScript enhancements, and a Vite-built Tailwind
and SCSS interface.

Normalized domain records use dedicated Eloquent models. Social modules whose
payloads remain catalog-shaped use encrypted, versioned `UserDomainState`
records behind server-authoritative Actions; shared publication-photo
engagement uses normalized `PhotoAsset`, `PhotoReaction`, and `PhotoComment`
records. Browser sessions may preserve non-sensitive UI preferences, but are
never an authorization, payment, confidential-storage, social-mutation, or
provider-integration boundary.

## Request Flow

Conventional HTTP:

```text
route + middleware
  -> route binding
  -> Form Request normalization / authorization / validation
  -> controller action authorization
  -> Action or cohesive Service
  -> Eloquent transaction / external client
  -> presenter or Resource
  -> Blade / redirect / JSON
```

Livewire:

```text
route or Blade host
  -> class-based component mount authorization
  -> minimal typed public state / form object
  -> action validation + authorization
  -> Action or Service
  -> computed/presentation data
  -> separate Blade template
```

Blade does not perform data access, service resolution, policy decisions, or
business calculations.

## Collaborative Guide Boundary

`KnowledgeArticle` is the canonical guide aggregate. `CreateKnowledgeGuide`,
`SaveKnowledgeGuideRevision`, `TransitionKnowledgeGuide`,
`ReviewKnowledgeCorrection`, `ManageKnowledgeCollaborator`,
`SetKnowledgeEditorialLock`, and `RollbackKnowledgeGuideVersion` are explicit
use cases. `KnowledgeGuideHistory` owns immutable snapshots and workflow
events. The Livewire editor coordinates those use cases but does not duplicate
their transactions or policy rules.

Forum topics remain discussion records. `PerformForumAction` may create one
submitted guide from one resolved topic only after an explicit owner action.
The resulting guide retains source/discussion links and normalized
attribution. No ranking, vote, or reaction path calls that conversion.

## Domain Modules

| Module | Persistence | Primary application boundary |
| --- | --- | --- |
| Identity | `users`, `pet_profiles`, sessions, password reset | Auth controllers/Livewire, policies, `ForumActor` |
| Organizations | Eloquent tenant, membership, invitation, restriction, audit | Tenant scopes, Policies, Actions, class-based Livewire |
| Forum and knowledge | Eloquent | Form Requests, class-based Livewire, use-case Actions, policies, presenters |
| Experts | Eloquent | Profile/booking Actions and participant policies |
| Marketplace | Eloquent | Locked state transitions and order Actions |
| Lost/found | Eloquent | Search Actions, owner/coordinator policies |
| Medical | Eloquent + private files | Section-scoped grants and download Actions |
| Care | Eloquent + private media | Journal policies, task/entry Actions, grants |
| Devices | Eloquent | Command/read/event/lifecycle Actions, device policies, grants |
| Social | Encrypted/versioned `user_domain_states`, immutable catalog content, relational photo engagement | Authenticated Actions, policies, catalogue resolution, constraints, optimistic versioning |
| Places | Eloquent place/venue authority plus encrypted/versioned per-user interaction state | Policy-scoped catalog, validated filters/actions, privacy-safe projections, provider boundaries |

## Identity Compatibility Boundary

Existing domain tables use string keys such as `owner_key`, `actor_key`,
`buyer_key`, and `seller_key`. A destructive replacement with foreign keys is
not required for production authentication.

`users.actor_key` is the authoritative unique bridge:

- authenticated code derives the current key from `Auth::user()`;
- the browser cannot submit or change the effective actor key;
- existing records remain compatible;
- new records receive the authenticated key;
- a later expand-and-contract migration may add user foreign keys where the
  relationship is unambiguous.

See `docs/decisions/0001-authenticated-actor-keys.md`.

## Data And Transaction Boundaries

- Database constraints protect foreign keys and uniqueness.
- Actions own short transactions and row locks.
- Monetary calculations use decimal strings converted to integer minor units;
  floats never cross the marketplace calculation or persisted snapshot
  boundary.
- External HTTP does not execute inside a database transaction.
- Payment, device command, medication dose, care entry, sighting, booking,
  webhook, and temporary token operations require idempotency.
- Audit records are written in the same transaction as critical changes where
  rollback consistency matters.
- Side effects execute after commit when their observation before commit would
  be unsafe.

## File And Image Boundaries

- Form Requests validate upload content, size, extension, and image dimensions
  before an Action may persist a file.
- `StorePublicImage` is the shared Laravel image-processing boundary for public
  marketplace, lost/found, sighting, and forum-topic photos. It performs
  orientation, bounded scaling, WebP encoding, and generated-name storage.
- Private medical, care, credential, and forum-journal media remain on private
  disks behind their existing policies and download Actions.
- `PrivateFileResponse` is the shared response boundary for existing private
  downloads and inline media. It accepts only the private local disk and a
  server-derived owning directory, then verifies canonical root, directory,
  regular-file, and symlink containment before creating the response.
- Pet primary photos reuse `ContentMediaAsset` and add only a pet-specific
  placement. `StorePrivateImage` normalizes orientation, bounds dimensions,
  re-encodes to WebP, and writes a generated name to `local`;
  `PreparePetProfileMediaResponse` repeats pet/placement policy and owning-
  directory checks before delegating to `PrivateFileResponse`.
- Blade renders only prepared paths and URLs; it never reads, transforms, or
  stores an image.

## Presentation Boundaries

- Blade components render prepared data.
- Messaging persists locale-independent call type, lifecycle, and quality
  codes. `MessagePresenter` resolves their current-locale labels and
  `MessagingCallStage` prepares the fixed control/icon map before passive Blade
  rendering. The conversation-details route reuses the policy-scoped context
  projection and changes responsive visibility only; it does not introduce a
  second data or authorization path.
- Share targets retain their original destination, media, title, and active
  navigation section, while `SharePresenter` maps the stable section code to
  current-locale target taxonomy, delivery channels, prepared recipient
  actions, link metadata, and privacy copy. The presenter performs no Eloquent
  query; Blade renders the prepared projection and canonical icon names.
- The deliberate Ari neighbor profile keeps its profile-led hero while
  `NeighborProfilePresenter` owns the complete current-locale identity,
  statistics, action payloads, pet routine, mutual-neighbor, community, and
  section projection. The presenter performs no Eloquent query; the existing
  authenticated state and interaction services remain the only upstream data
  boundaries, and profile Blade components render prepared copy and canonical
  icon names without resolving routes or mutations.
- The deliberate Mia owner profile keeps its profile-led hero while
  `ProfilePresenter` prepares the localized identity, stable tab and audience
  codes, privacy summary, actions, section copy, and canonical icon names.
  Data-heavy pet and moment projections are loaded only for tabs that render
  them; Blade remains passive and the existing authenticated route and action
  boundaries remain authoritative.
- Class-based Livewire components own server-backed interaction.
- Alpine is the Livewire-provided client-state layer; no second Alpine install.
- Existing vanilla JavaScript enhances map, message, publication-photo, and browser-media
  interactions and must initialize/teardown on Livewire navigation.
- Tailwind owns utility tokens and responsive primitives.
- The existing SCSS layer owns mature semantic component selectors until
  measured migrations replace them.

## Runtime Boundaries

Local/default configuration uses SQLite plus database-backed cache, session,
and queue. Tests use in-memory SQLite, array cache/session, and sync queue.

`ForumTopicTypeSchemaCatalog` is the immutable source for system topic-type
definitions. `ForumTopicTypeSchemaRegistry` projects active database rows into
typed DTOs through one bounded, explicitly selected query and the versioned
`forum:topic-type-schemas:v1` cache key. Model saves/deletes and system
synchronization invalidate that key. An empty definition table uses the
catalogue during bootstrap; once any definition row exists, an absent or
inactive stable key fails closed. Blade never resolves this registry.

User-visible critical care, medical, safety, and device commands must retain a
safe synchronous or local fallback. Queue-backed operations are allowed only
when deployment provides a worker and the job is idempotent, bounded, and
observable.

## Error Boundary

- Validation failures return localized field errors.
- Authorization failures do not reveal private resource details.
- Expected domain conflicts use explicit exceptions or typed results.
- External dependency failures map to safe recoverable states.
- Unexpected exceptions are reported with a request/incident identifier and
  safe structured context.
- Production never exposes SQL, paths, secrets, or stack traces.

## PHP 8.5 Applicability

| Feature | Applicable | Decision |
| --- | --- | --- |
| URI extension | Yes at URL trust boundaries | Prefer standards-based validation when available; retain framework rule compatibility |
| Pipe operator | No current need | Method chains are clearer in this codebase |
| Clone-with | Candidate | Use only with a justified readonly DTO |
| `#[NoDiscard]` | Candidate | Use for critical internal results only |
| `#[Override]` | Yes | Add to meaningful modified overrides |
| `array_first` / `array_last` | Yes | Use in touched code where clearer |
| Partitioned cookies | No | No cross-site embedded application |
| Persistent cURL sharing | No | Laravel HTTP client remains the boundary |

## Laravel 13 Applicability

| Feature | Use case | Decision / evidence |
| --- | --- | --- |
| Modern `bootstrap/app.php` | Middleware and exception configuration | Retain and extend |
| Origin-aware request forgery protection | All session mutations | Required, never disabled |
| API Resources | Public JSON | Required where JSON contract exists |
| `Cache::touch` | Semantic TTL extension | Not used without a concrete tested case |
| Image API | Media variants | Candidate only when implementing transformations |
| AI/vector/semantic APIs | No current provider requirement | Not applicable |
| Queue attributes/routing | Bounded background work | Apply only with operational worker |

## Architecture Verification

- `tests/Feature/ArchitectureComplianceTest.php`
- policy/authentication feature tests
- Livewire component tests
- Larastan
- route and cache build checks
- browser console and repeated-navigation checks

## Community Review Boundary

Low-risk classification uses assigned `ForumReviewPanel` records, independent
`ForumReviewAssignment` rows, and append-only panel events. Contextual
`ForumCommunityNote` records use append-only versions and never overwrite
review evidence. Review panels are structurally limited to seven low-risk
types; unified moderation remains authoritative for safety, legal, privacy,
fraud, credential, cruelty, and permanent-account decisions.

See `docs/community-review.md`.

## Peer Mentorship Boundary

Mentorship is a dedicated private workflow, not an extension of the prototype
message center. `ForumMentorProfile` and `ForumMentorScope` expose only
opted-in public-safe matching data. `ForumMentorship` owns request and lifecycle
state; messages, feedback, and events are append-only evidence protected from
parent deletion.

`MentorMatcher` and `MentorshipEligibility` form the read/eligibility boundary.
Actions form the only mutation boundary and repeat authorization, validation,
transaction, idempotency, lock, and audit controls. Trust and mentoring
reputation may establish community eligibility, while professional status is
derived only from a separate current credential.

See `docs/mentorship.md`.

## Persistent Group Boundary

`ForumGroup` is the authoritative group aggregate. Memberships, invitations,
taxon focus, and append-only events are relational; the previous
`UserDomainState` group payload remains a compatibility presentation boundary
and cannot grant persistent membership or management authority.

Dedicated Actions own creation, request/review, invitation response,
restriction, role, ownership, and lifecycle transactions. `ForumGroupPolicy`
owns every read and mutation decision. `GroupDirectory`, `GroupWorkspace`, and
`GroupManagement` coordinate those boundaries with bounded eager-loaded
projections and never serialize private group graphs into public state.

Topics and guides retain their existing identities and gain an optional group
relation. Events, announcements, private files, polls, options, and one
user/poll vote projection are normalized group children. Dedicated Actions
own every association and mutation; the group policy is rechecked before
child policy decisions. Poll closure is derived from `closes_at`, voting uses
a short locked transaction plus database uniqueness, and poll results never
write professional, medical, legal, scientific, or confirmation authority.

See `docs/groups.md` and `docs/polls.md`.

## Forum Journal Boundary

`ForumTopic` remains the publication, visibility, category, group,
localization, engagement, and moderation shell. `ForumJournal` is a
one-to-one extension that owns typed progress state, dated entries,
measurements, selected collaborators, immutable pre-edit versions, and private
media. The existing `ForumComment` model is reused through an additive
journal-entry relation.

`CareJournal` remains a separate private operational-care aggregate. No care
entry, medication-adjacent field, access grant, or private care file is copied
into a forum journal.

Dedicated Actions own creation, legacy backfill, entry mutation,
collaboration, comments, files, archive, and export. Policies run before
child data is queried. The two class-based Livewire components coordinate
small scalar/form state and bounded computed presentation arrays.

See `docs/journals.md`.

## Organization Authority Boundary

`Organization` is the canonical tenant identity. Memberships provide current
role projections; invitations provide account-bound entry; restrictions
disable independent capabilities; audit events retain append-only actor and
subject evidence. No display string, email domain, event creator, or
marketplace record is allowed to infer organization ownership.

Class-based Livewire components expose only locked numeric identity, forms,
feedback, and prepared bounded projections. Tenant queries scope before
presentation. Actions authorize before and after locks where applicable and
own idempotent transactions. `ForumEvent.responsibleOrganization` consumes
this authority without moving event ownership into the organization model.

Selected-organization context and organization locations remain open P02
boundaries and must not be simulated with browser state.

## Event And Club Boundary

`ForumEvent` is the durable event aggregate; `ForumGroup` remains the club
aggregate. Group calendar activities link to a canonical event and create it
inside the same transaction. Registration, invitations, updates, attendee
messages, reviews, and history are normalized children. Dedicated Actions own
all mutations, and policies run before protected fields or child records are
queried.

The previous JSON event state now owns only personal interest, calendar, and
reminder preferences. Legacy creation URLs redirect to the class-based
Livewire workflow, so there is one authoritative mutation path. See
`docs/events/index.md`.

## Verified Expert Session Boundary

`ForumExpertSession` is a scheduled educational aggregate separate from
appointments, consultations, ordinary topics, events, and guides. It owns the
scope/jurisdiction snapshot and schedule; questions, answers, corrections, and
history are normalized children. Current `ExpertProfile` plus `Credential`
state remains the sole professional-authority boundary.

Dedicated Actions own transactional mutations, policies run before private
queue data is returned, and timestamp-derived windows avoid a scheduler
dependency. Source URLs are validated but never fetched. Reports reuse unified
moderation. See `docs/expert-question-sessions.md`.

## Forum Topic Lifecycle Boundary

`ForumTopic` remains the stable content aggregate. `ForumTopicLifecycle` owns
canonical state transitions, row and optimistic locking, lifecycle timestamps,
terminal lock projection, and append-only history. Update requests and legal
holds are normalized children; category lifecycle rules own age and bump
thresholds.

State-changing Actions authorize and validate before delegating to the shared
service. `ForumTopicLifecycleProjection` computes stale, necropost, archive,
retention, and bump state without writes. Physical deletion is not an ordinary
topic operation, and legacy state values remain readable while new mutations
write canonical values. See `docs/topic-lifecycle.md`.

## Forum Database Correctness Boundary

The database owns durable foreign, unique, compound, and fixed-value
constraints. Backed enums mirror fixed vote and reaction values in PHP.
Race-sensitive vote, reaction, answer-acceptance, and moderation closure
operations use short retry-bounded transactions and row locks.

`CloseForumModerationCase` is the sole case-closure operation in this package.
It combines policy authorization, expected-version validation, a unique
idempotency key, one case update, and one bounded bulk append of report audit
events. No controller, route, Livewire, or Blade layer duplicates that logic.

## Canonical Pet Profile Boundary

`PetProfile` remains the one reusable animal aggregate for social, care,
medical, device, search, adoption, event, marketplace, and future relations.
No module may create an alternative species list or pet identity. The stable
`profile_key` is the cross-interface URL identity; numeric FKs remain the
database relationship boundary.

`PetProfileManager` is the actor-to-pet authorization edge. Policies delegate
to `PetProfileAccess`, and mutation Actions repeat server authorization before
locking and writing. `PetProfilePrivacySetting` owns profile and section
audiences. `PetProfileLifecycleEvent` and the ordinary `AuditLog` retain
actor-attributed history. `PetProfileFact` is reserved for critical values
that need provenance, verification, privacy, and replacement history.

The Livewire components coordinate validated form objects and Actions. Blade
views receive bounded arrays and do not query models or resolve services.
Existing `user_id`, slug, string species/breed, and encrypted profile data are
compatibility fields during expansion. See `docs/pet-profiles.md`.

Breed ancestry is a bounded normalized child relation, not a replacement pet
identity or a taxonomy authority. `PetBreedOriginNormalizer` owns semantic
validation, `PetBreedOriginSynchronizer` owns the short relational write, and
`PetBreedOriginPresenter` owns the public-safe projection. The legacy breed
string is derived compatibility data. Confidence and source stay independent,
and no media or appearance path may promote either value.

Structured appearance color remains inside the existing encrypted
`profile_data` compatibility boundary. `PetAppearanceNormalizer` owns the
controlled color/pattern catalogue, cardinality, uniqueness, and bounded-text
rules for every mutation path; `PetAppearancePresenter` owns the localized
public-safe scalar projection. Blade never interprets stored enum values, and
identifying marks are intentionally excluded from the public projection.

Species-aware body-covering facts share that encrypted compatibility boundary.
`PetBodyCoveringSchema` maps server-owned broad species to relevant controls,
`PetBodyCoveringNormalizer` validates and prunes every mutation payload, and
`PetBodyCoveringPresenter` owns the localized public-safe scalar projection.
The manager-only skin observation is intentionally absent from that presenter;
it is descriptive profile state rather than a diagnosis or medical record.

Identifying marks are a bounded normalized child relation because each item
needs its own stable key, encrypted description, order, lifecycle, actor, and
audience. `PetIdentifyingMarkNormalizer` owns the server validation boundary,
`PetIdentifyingMarkSynchronizer` owns the short scoped retirement/upsert, and
`PetIdentifyingMarkPresenter` owns the public-safe projection. The public read
is scoped to active public rows before hydration and filtered again by the
presenter. Private-verification rows remain manager-only; friend, clinic, and
active-search audiences are not offered until those authoritative consumers
exist.

The general pet size is one nullable enum-backed scalar on the canonical
profile. `PetSizeCategoryNormalizer` owns untrusted mutation input and
`PetSizeCategoryPresenter` owns the localized public-safe projection. Null is
unrecorded, and no domain derives the category from species, breed, media,
weight, or legacy text. The composite size/status/id index is an enabling
profile primitive only; compatibility decisions remain owned by their future
place, product, service, event, transport, or search consumers.

## Canonical Social Relationship Boundary

`SocialActor` is a one-to-one internal adapter around an authoritative user,
pet, expert, or group profile. It owns no credentials, biography, ownership,
medical data, or independent identity. Requests, active typed relationships,
per-actor settings, and append-only events are separate aggregates.

Mutation Actions authorize the real authenticated user against the represented
actor, re-read locked rows, enforce active/idempotency uniqueness, and
invalidate both endpoint cache namespaces. Directed and symmetric edges share
one model but use type-defined key semantics. Social trust never grants pet,
professional, medical, device, adoption, marketplace, or location authority.
See `docs/social-relationships.md`.

## Progressive Pet Profile Completion Boundary

The progressive completion workspace adds no second aggregate or completion
table. `PetProfileCompletionStep` supplies stable presentation keys,
`PetProfileCompletionPresenter` derives non-scoring navigation state from the
selected profile row and bounded existence attributes, and
`UpdatePetProfileStep` owns the allowlisted partial transaction. Detailed
media, manager, privacy, fact, and lifecycle relations are loaded only for the
active URL-backed step. `microchip-record` remains a versioned encrypted
`PetProfileFact` behind the critical microchip permission rather than becoming
ordinary public profile data.
