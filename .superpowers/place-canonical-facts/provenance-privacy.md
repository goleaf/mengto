# Canonical Place Facts: Provenance And Privacy Review

Date: 2026-08-30  
Baseline: `main` at `ae4ac32`; discovery was read-only except for this report.  
Scope: source/evidence, verification scope, time semantics, immutable history,
visibility/encryption, retention, projection allowlists, idempotency,
concurrency, corrections, and merge provenance. Schedules, service taxonomy,
accessibility UI, and migration rollout are intentionally excluded.

## Requirement Decisions

- `PRD-PLACE-002` requires fact-level source, verification scope, freshness,
  corrections, and preserved evidence/history (`docs/product-requirements.md:94-100`).
- `DATA-INTEGRITY-005/006` require source evidence to survive correction and
  important edits to be versioned/audited (`docs/non-functional-requirements.md:21-30`).
- `SEC-DATA-001` closes exact location, private contact, and audit data by
  default (`docs/non-functional-requirements.md:3-13`). Place controls further
  prohibit exact address, coordinates, private instructions, access metadata,
  and operational venue data from public directory records
  (`docs/security.md:205-217`).
- `PLA-02-007` names source kind, submitter, evidence reference, observed time,
  verified time, scope, and freshness as one provenance contract
  (`docs/plans/places-production-master-plan.md:225-227`). `PLA-05-009/010/017`
  require distinct verification scopes, fact/source-specific freshness, and
  scope-correct history (`docs/plans/places-production-master-plan.md:379-396`).
- `PLA-CF-03/05/07` require provenance/current-version/history records,
  policy-authorized idempotent replacement/retirement, retained submitted
  evidence, stale-write rejection, encryption, immutable history, and a
  bounded projection (`docs/implementation-plan.md:647-651`).

Principal decision: extend the existing `PlaceFact` aggregate as the one
immutable observation/version ledger. Do not introduce another canonical-fact
or provenance table that silently duplicates it. Small selector, evidence-link,
or private-payload records may support that ledger, but they must reference
`place_facts` and must not become a parallel source of truth.

## Confirmed Existing Evidence

### Useful foundations to retain

1. `PlaceFact` already owns the submitted/canonical linkage, origin place,
   immediate copied-from fact, submitter/reviewer attribution, stable/field
   keys, encrypted fact value and source reference, source kind, provenance and
   visibility scopes, observed/verified timestamps, and append-only model
   guards (`app/Models/PlaceFact.php:20-61`,
   `database/migrations/2026_08_30_120000_create_place_submission_publication_tables.php:145-173`).
2. Submission revisions and events reject update/delete and retain encrypted
   private detail (`app/Models/PlaceSubmissionRevision.php:19-45`,
   `app/Models/PlaceSubmissionEvent.php:20-60`). Submission values, source
   reference, exact location, submitted-fact payload, and audit context use
   encrypted/hidden casts (`app/Models/PlaceSubmission.php:117-167`).
3. Submission creation is payload-bound and actor-scoped, repeats authorization
   inside a locked transaction, creates a revision and immutable facts, and
   rejects an idempotency-key payload conflict
   (`app/Actions/SubmitPlaceSubmission.php:50-90,116-204,367-407,452-503`).
4. Publish/link/merge copy facts instead of deleting their submitted source;
   merge records both `origin_place_id` and immediate `copied_from_fact_id`
   (`app/Actions/PublishPlaceSubmission.php:91-114`,
   `app/Actions/LinkPlaceSubmission.php:86-112`,
   `app/Actions/MergePlaceDuplicate.php:165-189`). Chained-merge coverage checks
   both origin and immediate ancestry
   (`tests/Feature/Places/PlaceSubmissionPublicationWorkflowTest.php:953-1040`).
5. Workflow transitions combine row locks, expected versions, payload
   fingerprints, unique event idempotency, and one transaction
   (`app/Services/PlaceSubmissionTransition.php:53-69,82-189,224-256`).
6. Current negative tests prove encrypted exact location does not appear in
   serialization/public projection, protected duplicate identities are hidden,
   merge redirects require safe visibility, and raw notification payloads omit
   private workflow evidence
   (`tests/Feature/Places/PlaceAuthorityFoundationTest.php:60-109`,
   `tests/Feature/Places/PlaceSubmissionPublicationWorkflowTest.php:161-177,885-951`).

These foundations should be reused; they do not yet satisfy canonical
fact-level reading or replacement.

## Confirmed Defects And Integration Conflicts

### CF-PROV-001 — High — live freshness is not fact-level

`PlaceCatalog` never loads `PlaceFact`. It labels every field with the same
verification scope and uses `places.updated_at` as both verification date and
freshness (`app/Services/PlaceCatalog.php:132-184,200-230`). Any unrelated edit
therefore makes all place information look current. This directly conflicts
with `PLA-05-010` and cannot truthfully support `PRD-PLACE-002/003`.

### CF-PROV-002 — High — publication is incorrectly treated as verification

Submission facts start as `review_only`, and publish/link copy that visibility
unchanged while stamping every copied fact `verified_at = now()`
(`app/Actions/SubmitPlaceSubmission.php:488-501`,
`app/Actions/PublishPlaceSubmission.php:93-111`,
`app/Actions/LinkPlaceSubmission.php:88-109`). Approval/publication proves a
moderation decision, not that every submitted address, phone, service, or other
fact was independently verified. `PlaceFactScope` contains workflow-copy states
only (`submitted`, `published`, `linked`, `merged`) and is not a verification
scope (`app/Enums/PlaceFactScope.php:7-13`).

### CF-PROV-003 — High — no canonical current/replacement lifecycle exists

`place_facts` has no logical fact identity beyond free-form `field_key`, no
version/current selector, no supersedes relation, no retirement/expiry state,
and no freshness deadline (`database/migrations/2026_08_30_120000_create_place_submission_publication_tables.php:145-173`).
The model is correctly immutable, but there is consequently no deterministic
way to select one current accepted version or retire a value without deleting
history. The public application still reads mutable scalar columns from
`places`, leaving the immutable evidence disconnected from the displayed truth.

### CF-PROV-004 — High — the explicit public projection is not the live boundary

`PlacePublicProjection` is used only by a test; live directory/detail paths use
`PlaceCatalog` directly. The explicit projector has a useful denylist for exact
data but exposes only coarse place verification and no fact source/history
(`app/Services/PlacePublicProjection.php:10-43`; repository search finds no
production caller). The live catalogue overlays canonical scalar values onto
fixture records and passes role-accessible records through one common shape
(`app/Services/PlaceCatalog.php:29-45,132-188`). Public, manager, moderator, and
exact-location projections are therefore not explicit independently testable
allowlists.

### CF-PROV-005 — High — fact-copy idempotency is not database protected

Publish, link, and merge use `firstOrCreate` keyed by
`(place_id, copied_from_fact_id)`, but the migration provides only ordinary
indexes and no matching unique constraint
(`app/Actions/PublishPlaceSubmission.php:91-113`,
`app/Actions/LinkPlaceSubmission.php:86-111`,
`app/Actions/MergePlaceDuplicate.php:165-188`,
`database/migrations/2026_08_30_120000_create_place_submission_publication_tables.php:166-172`).
Concurrent copies can race into duplicates or a stable-key exception; method-
level `firstOrCreate` is not a business uniqueness boundary.

### CF-PROV-006 — High — restore does not deactivate merge-derived facts

Merge appends facts to the destination. Restore changes redirect/place state
but does not identify or deactivate the facts acquired by that merge generation
(`app/Actions/MergePlaceDuplicate.php:77-109,165-189`,
`app/Actions/RestoreMergedPlace.php:50-115`). Evidence must remain, but a future
current-fact reader must not continue treating restored merge copies as active
canonical values. `PlaceFact` currently has no causal merge-event/generation
link with which to make that decision.

### CF-PROV-007 — Medium — visibility is an unvalidated free-form string

`visibility_scope` is neither an enum cast nor constrained at the database
boundary (`app/Models/PlaceFact.php:22-55`,
`database/migrations/2026_08_30_120000_create_place_submission_publication_tables.php:154-164`).
`hidden` and encryption protect default serialization/storage, but neither is
authorization. A typo or future query that omits a scope predicate can disclose
private evidence.

### CF-PROV-008 — Medium — retention/redaction is undefined

The encrypted `field_value` and `source_reference` are append-only forever;
there is no evidence retention deadline, redaction state, or separable private
payload lifecycle. `docs/data-model.md:223-233` requires category-specific
retention and the shortest suitable retention for exact location. Blanket
retention of personal source notes/precise observations is not an acceptable
implicit policy, while deleting the whole fact would violate provenance.

### CF-PROV-009 — Integration blocker in concurrent uncommitted work

The currently dirty contribution migration creates a separate correction
source/evidence/value history (`original_value`, `proposed_value`, `evidence`,
`source`, `applied_value`) without any `PlaceFact` foreign key
(`database/migrations/2026_08_30_230000_create_place_contribution_workflows.php:13-43,54-68`).
Its current tests assert a scalar `places.summary` overwrite but do not assert
creation/linkage of an immutable canonical `PlaceFact`
(`tests/Feature/Places/PlaceCorrectionWorkflowTest.php:116-152`). This file was
both staged and modified during discovery, so it is not a baseline production
defect; it is a concrete integration conflict the principal must reconcile.
Do not ship two unlinked provenance histories.

## Canonical Fact Lifecycle

The following semantics are required regardless of the schema shape selected
by the principal/schema specialist:

1. **Submitted observation.** Append a `PlaceFact` with immutable value, source
   kind, private evidence reference/payload link, submitter, `observed_at`, and
   `recorded_at` (`created_at`). It is review-only and is not canonical merely
   because it exists.
2. **Decision.** Append an authorized decision event. Acceptance creates an
   immutable canonical fact version referencing the submitted fact(s). It does
   not change verification scope unless the actor actually exercises an
   applicable verification authority and records its method/scope.
3. **Current selection.** Exactly one current selector exists per logical fact
   slot `(place, fact type, subject/discriminator, locale where applicable)`.
   The selector points to an immutable `PlaceFact`; it is not a second evidence
   ledger. Reads never infer currentness from newest ID alone.
4. **Correction/replacement.** Append the proposed fact as evidence, append the
   accepted canonical fact, link it separately to (a) the prior canonical fact
   it supersedes and (b) every supporting submitted/evidence fact, then move the
   locked selector. Keep `copied_from_fact_id` for immediate copy ancestry; do
   not overload it with supersession semantics.
5. **Retirement.** Append an explicit retire/tombstone decision and clear or
   move the selector. Do not update/delete the historical fact to make it
   disappear.
6. **Merge.** Preserve the existing origin/immediate-copy chain and associate
   every activated copy with the exact merge event/generation. Merge changes
   selectors, not evidence ownership.
7. **Restore.** Reverse only selector activation for that merge generation.
   Keep copied evidence and the merge/restore audit trail immutable.

For multiple supporting observations, use explicit fact-to-evidence links.
One `copied_from_fact_id` cannot represent multiple sources and one provenance
edge must not silently win.

## Verification And Time Semantics

Keep these concepts independent:

| Field/concept | Meaning | Rule |
| --- | --- | --- |
| `observed_at` | When the source observed the claimed condition | Nullable means unknown; never replace with ingestion time |
| `recorded_at` / `created_at` | When PawCircle received the record | Audit only; not freshness proof |
| `verified_at` | When an authorized verifier checked this exact fact | Publication/review alone must not populate it |
| `verification_scope` | `unknown`, `community_submitted`, `manager_confirmed`, `organization_confirmed`, or `independently_verified` | Separate enum from source kind and copy/provenance scope |
| `fresh_until` | Last instant confidence remains current under the policy for this fact and source scope | Freshness is derived at read time; do not persist a drifting `fresh` boolean |
| `expires_at` | Instant the represented condition/value itself ends | Expired values are not current, but remain in history |
| authority snapshot | Why the verifier had authority at decision time | Retain safe role/organization/method attribution even if current membership later ends |

Derived presentation states are `unknown` when required time/scope is absent,
`fresh` through `fresh_until`, `stale` afterward, and `expired` after value
expiry. Stale is not false and must not be silently hidden. A replacement's
freshness applies only to that replacement, not to every fact on the place.

## Projection Allowlists

All projectors must accept an already authorized place/fact query, select only
needed columns, constrain current facts before materialization, and return
prepared scalar DTO/array values. Never serialize a model/relationship graph.

| Projection | Allowed | Explicitly excluded |
| --- | --- | --- |
| Public/member | Active public place identity; current fact value only when its visibility is public; safe source-kind label; verification-scope label; coarse observed/verified date; derived freshness/expiry state; safe public history summary | Raw `source_reference` or evidence; submitter/reviewer identity; exact observation instant when it may reveal a person's movement; hashes, internal IDs, audit context, private reasons, exact/private location/contact, retired private values |
| Place manager | Public fields plus manager-visible current/history facts inside current management authority and field scope; safe evidence summary and verification method where needed to manage the fact | Reporter/submitter contact or identity by default; moderation/audit metadata; evidence marked moderator/private; exact-location payload unless separately authorized for `viewExactLocation`; unrelated organization facts |
| Moderator/reviewer | Review-queue facts, encrypted evidence after authorized decryption, submitter/reviewer attribution, precise times, hashes/fingerprints, complete lineage, decision reasons, and audit context needed for the assigned review | Unrelated place/private-domain evidence; bulk exact location by default; any value outside a named review/moderation purpose |
| Exact-location reveal | Only the minimum exact fields authorized for the account, purpose, event, and time window | Source evidence, unrelated private contact, general moderation history; never cache with public/manager projection |

Public source links require a separate reviewed public URL value with HTTP(S)
scheme/host validation and safe external-link handling. Never turn encrypted
free-form `source_reference` into an href. Public history should expose a safe
event summary, not raw before/after evidence.

Current `PlaceModerationWorkspace` eagerly decrypts and renders exact location,
source reference, audit context, and all submitted facts for every administrator
queue item (`app/Livewire/Places/PlaceModerationWorkspace.php:53-63,268-333`).
The canonical projector should require a named review purpose and should keep
exact-location reveal separately authorized/audited instead of making it a
default bulk-list field.

## Encryption, Evidence, And Retention

1. Retain the existing encrypted + hidden casts for fact values and source
   references. Keep private evidence files on the private media boundary; model
   `$hidden` is defense in depth, never the access decision.
2. Public canonical scalar values may remain queryable in their dedicated
   normalized records, but the raw submitted value/evidence copy remains
   encrypted in `PlaceFact`. Never copy private evidence into a public column.
3. Separate immutable evidence metadata/hash/lineage from redactable encrypted
   personal payload when erasure/minimization may apply. A child payload or
   evidence record referencing `PlaceFact` is acceptable; a parallel fact
   history is not. Per-record envelope encryption/crypto-erasure is preferable
   if immutable metadata must survive payload deletion.
4. Retain canonical public fact versions, hashes, decision events, and merge
   ancestry while the place/history is legally or operationally required.
   Retain safety/audit evidence according to the documented legal/security
   policy. Do not invent a duration in code before that policy is approved.
5. Give precise location, private contact, personal observation notes, and raw
   evidence an explicit `retention_until`/legal-hold decision and the shortest
   justified lifetime. Expiry removes projection/access; purge or crypto-erasure
   follows only when no legal/safety hold remains. Keep a non-reversible safe
   audit marker that redaction occurred.
6. Account deletion may null/anonymize actor attribution where policy permits;
   it must not destroy accepted fact lineage. Public output never exposes actor
   identity even before deletion.

## Idempotency And Concurrency Invariants

- Every create/replace/retire/verify/merge-activation operation has a unique
  actor-or-system scoped idempotency key plus an HMAC payload fingerprint.
  Replay with the same payload returns the original result; same key/different
  payload fails.
- Add database business uniqueness for a copied fact at a destination. At
  minimum, one destination may receive a given immediate source fact once.
- Lock the `Place` and logical current selector in deterministic order inside
  one short transaction. Require both the expected place version and expected
  current fact/selector version; reject stale versions instead of last-write
  wins.
- A unique current-selector identity prevents concurrent first versions; a
  unique successor/idempotency boundary prevents two replacements of the same
  expected fact from both becoming current.
- Fact, selector move, correction resolution, merge/restore activation, and
  append-only audit event commit or roll back together.
- Hashes are integrity/deduplication hints, not proof of truth or authorization.
  Keep them HMAC/keyed when values may be low entropy.

## Threat And Privacy Review

| Risk | Impact | Required control |
| --- | --- | --- |
| Moderation acceptance launders a weak source into “verified” | False authority, especially for emergency/contact facts | Separate source, verification scope/method, and publication decision; never auto-verify on copy |
| Place-level timestamp refreshes unrelated facts | Stale hours/contact/capability presented as current | Fact/source-specific `fresh_until` and injected reference instant |
| Source URL/note or precise observed time is public | Submitter identity, movement, contact, or token leakage | Public allowlist, coarse dates, separately validated public URL, encrypted raw reference |
| Shared presenter/cache crosses public, manager, moderator, or exact scopes | Private evidence/location disclosure | Separate projection classes/DTOs and actor/role/purpose/locale cache keys; no shared model serialization |
| Concurrent copy/replacement forks the fact chain | Duplicate or nondeterministic current truth | Database uniqueness, selector row lock, expected version, payload-bound idempotency |
| Merge copy obscures original source | Provenance laundering | Preserve `origin_place_id`, immediate source fact, source observation, and exact merge event/generation |
| Restore leaves merged facts active | Facts from a reversed merge remain canonical | Restore selector activation only; keep evidence inactive and historical |
| Correction stores a second unlinked evidence history | Conflicting audit truth and lost source chain | Correction references source/current/proposed/applied `PlaceFact` records; no duplicated source/value ledger |
| Encrypted evidence is retained indefinitely under the application key | Data minimization failure and broad breach impact | Explicit retention/legal hold, separable payload, key rotation and preferably per-record crypto-erasure |
| Free-form visibility/scope value or omitted predicate | Review-only evidence disclosure | Backed enums, validation/constraints, scoped query APIs, negative projection tests |

## Required Focused Regression Contracts

Existing tests cover append-only guards, encryption/serialization, submission
idempotency, rollback, merge ancestry, and redirect privacy. Missing contracts
that must be red before implementation are:

- public, manager, moderator, and exact-location allowlists against the same
  fact set, including absence of raw source/evidence/actor/audit values;
- each verification scope without automatic promotion on publish/link;
- per-fact observed/verified/fresh-until/expiry derivation and proof that an
  unrelated place edit cannot refresh a fact;
- one-current-version selection, correction supersession, retirement, and
  preservation of submitted evidence;
- database-enforced copy idempotency and concurrent replacement stale rejection;
- merge generation activation and restore deactivation while copied evidence
  remains queryable to authorized moderation/history code;
- correction records link source/current/proposed/applied facts rather than
  duplicating plaintext evidence/value history;
- retention expiry removes private projection without deleting immutable safe
  lineage, plus legal-hold behavior.

No test command was run for this read-only discovery; all findings above come
from bounded source, schema, documentation, and test inspection.
