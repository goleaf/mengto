# EVENT-S01 — Competitions Discovery Report

Date: 2026-08-30
Scope: EVENT-S01 only; discovery-only. No production, migration, test,
translation, canonical-plan, or shared-document file was changed.

## Executive finding

`ForumEventType::Competition` is only a controlled event-type value. There is
no competition aggregate, category, entry, judge, conflict, score, result,
appeal, prize, certificate, or competition-specific history table, model,
Action, Policy ability, route, Livewire surface, factory, seeder, or test.
This is correctly documented as unimplemented in `docs/events/competitions.md`
and must remain so until the durable boundary below exists.

The event foundation supplies useful authority but must not be mistaken for
competition implementation:

| Reusable authority | Current source | Safe reuse | Limit |
| --- | --- | --- | --- |
| Canonical event, owner/organization/visibility | `ForumEvent`, `ForumEventPolicy`, Point 13 migrations | Parent aggregate, event visibility, active account/team/organization boundary | It has no category or result-level authorization. |
| Registration, pet links, accepted snapshot | `ForumEventRegistration`, `ForumEventRegistrationPet`, `ForumEventRegistrationService` | Entry eligibility input and evidence of a participant's event registration | A registration is not a competition entry and cannot represent teams or organizations alone. |
| Event versions and snapshots | `ForumEventVersion`, `ForumEventLifecycleSnapshot`, `InitializeForumEventLifecycle` | Event context lineage | It cannot substitute for exact competition-rule/scoring snapshots and accepted rule versions. |
| Team membership and roles | `ForumEventTeamMembership`, `ForumEventTeamRole::{Judge,Scorekeeper,WelfareOfficer,Auditor}` | Baseline active event-team and tenant-membership check | Role assignment neither verifies judge identity nor scopes a judge to categories/entries. |
| Transaction, lock, idempotency pattern | `TransitionForumEventStatus`, `ForumEventRegistrationService`, `SaveForumEventSession` | Short `DB::transaction(..., 3)`, parent row `lockForUpdate()`, unique idempotency keys, retriable integrity conflicts | Competition result computation needs a stronger immutable snapshot and explicit finalization gate. |
| Event audit and notification dedupe | `ForumEventAudit`/`forum_event_history`, `ForumEventNotifier`/`forum_notifications.deduplication_key` | Event-level summary audit and recipient-specific notification outbox row | Score/correction/result evidence needs a dedicated append-only domain history; notification keys are globally unique. |
| Independent reviewer patterns | credential and moderation appeal Actions; `ForumReviewAssignment` | Lock appeal and decision rows; reject an original reviewer | Generic moderation/credential records must not be polymorphically repurposed as competition appeals. |
| Credential authority | `Credential`, `CredentialPolicy`, credential verification Actions | Verify and snapshot a judge's required credential where the rule requires one | Verification is qualification evidence, not automatic competition authority. |

The closest existing test style is the event lifecycle/schedule suite: action
invocation with real SQLite records, direct policy checks, encryption checks,
idempotent replays, row-lock-backed mutation, and exact public/private Livewire
projection. It contains no competition coverage.

## Requirement authority and traceability

The immutable source is `docs/requirements/forum-source-prompt.md`, lines
60400-60634. The generated atomic catalogue places this scope in
`event-competition`; all atoms are currently `implementation_status: planned`
and `verification_status: discovered` (for example `event.competition.0001`).
No atom in this report is promoted.

| Source section | Atomic ID range | Design consequence |
| --- | --- | --- |
| `competition-001` entity | `event.competition.0001-.0017` | A separately versioned competition must define its event, organizer, rules, categories/divisions, eligibility, entries, judges, scoring/ties, withdrawal/disqualification/appeal rules, prizes, and result visibility. |
| `competition-002` versioned rules | `.0018-.0020` | Persist the exact rule version accepted by each entry; material changes must create a new version and trigger selective renewed acceptance. |
| `competition-003` categories/divisions | `.0021-.0030` | Structured legitimate criteria only, with a human-reviewed non-discrimination boundary. |
| `competition-004` entry | `.0031-.0039` | Entry representation supports person, animal, pair, team, organization, and permitted multiple animals while retaining registration and eligibility snapshots. |
| `competition-005` judges | `.0040-.0048` | Verified identity/role, category assignment, disclosure, narrow entry access, scoring windows, and audit are first-class data. |
| `competition-006` scoring model | `.0049-.0061` | Immutable criteria/scale/calculation/precision/tie/missing-score/correction/finalization configuration. |
| `competition-007` precision | `.0062-.0068` | Integer scaled units and documented rounding/aggregation/tie comparison; no float. |
| `competition-008` independent score | `.0069-.0078` | Database uniqueness per judge, entry, criterion and an immutable score revision trail. |
| `competition-009` correction | `.0079-.0086` | Original, replacement, actor/reason/time, and result effect must survive correction. |
| `competition-010` finalization | `.0087-.0095` | Locked, validated, deterministic result-version creation blocks ordinary edits and concurrent finalization. |
| `competition-011` disqualification | `.0096-.0105` | Rule/evidence/official/time/safe explanation/appeal/prize effect; private incident detail stays private. |
| `competition-012` welfare withdrawal | `.0106-.0108` | Handler may withdraw; public projection is neutral `withdrawn`, never punitive. |
| `competition-013` appeals | `.0109-.0118` | Deadline/rule/evidence/conflict-free reviewer/decision/result-version effect/notification. |
| `competition-014` results | `.0119-.0128` | Only selected public fields after verified final publication; no private contact/address/health/incident leakage. |
| `competition-015` prizes | `.0129-.0138` | Separate prize lifecycle and transparent sponsor relationship; sponsor cannot influence judging without disclosed authorized judge role. |
| `competition-016` certificates | `.0139-.0145` | Versioned, final-result-linked, localized, private-download and audited correction/revocation contract with EVENT-S06. |
| `competition-017` anti-cheating | `.0146-.0157` | Typed review signals for investigation, never invisible risk scores, automatic accusations, or public allegations. |

P26 in `docs/plans/portal-events-completion-master-plan.md` (lines 702-720)
adds acceptance gates for judge isolation, row-locked deterministic
finalization, conflict denial, neutral welfare withdrawal, no gambling, and
human-reviewed anti-cheating. The P29 certificate acceptance (lines 757-777)
is a cross-scope dependency, not authority to implement EVENT-S06 here.

## Proposed normalized boundary

Use `forum_event_` prefixes and the existing canonical event only; do not make
a second event aggregate or encode this domain in `ForumEvent::metadata`.
All controlled states below should be backed enums, localized through the
existing `forum_events` catalogue or a dedicated parity-tested event catalogue.

### Core, rules, categories, and eligibility

| Entity | Essential columns and relations | State / invariant |
| --- | --- | --- |
| `forum_event_competitions` | `forum_event_id`, `stable_key`, `organizer_user_id`, nullable `responsible_organization_id`, `current_rule_version_number`, `current_result_version_number`, `result_visibility`, `status`, `lock_version`, `finalization_started_at`, `finalized_at`, `published_at`, `published_by_user_id`, timestamps | Owns only one competition; an event may contain several. States: `draft -> registration_open -> judging_open -> finalizing -> finalized -> published`, with `cancelled`/`archived`; no transition skips configuration validation. |
| `forum_event_competition_rule_versions` | competition FK, `version_number`, `kind`, `reason_code`, canonical rule snapshot and checksum, `is_material`, `created_by_user_id`, `published_at`, `superseded_at`, timestamps | Immutable after insert. Unique `(competition_id, version_number)`; snapshot carries tie, disqualification, welfare, appeal, prize, result-visibility and scoring-definition references/checksums. |
| `forum_event_competition_categories` | competition FK, stable key, name/description, position, eligibility definition version/snapshot checksum, active flag | Category is a stable competition-owned identity; ordering is `(position,id)`. It does not derive classes from protected animal/participant data. |
| `forum_event_competition_divisions` | category FK, stable key, name, position, `criteria` (structured allow-listed criterion definitions), eligibility snapshot checksum, active flag | Divisions are subordinate to a category. Creation/change requires organizer review and explicit reason; free-form discriminatory targeting is not a rule language. |
| `forum_event_competition_eligibility_evaluations` | entry FK, rule-version FK, category/division FK, `status`, evaluated-by/at, `reason_code`, encrypted private evidence/reviewer notes, public-safe conditions, checksum | Append evaluations rather than overwrite. `eligible` is a prerequisite for scoring/finalization, subject to later welfare/disqualification state. |
| `forum_event_competition_rule_acceptances` | entry FK, rule-version FK, accepted-by user, locale/timezone, accepted-at, snapshot checksum, idempotency key | Exact version and acceptance are immutable. A material rule change creates a new acceptance requirement; an entry remains pending until required acceptance exists. |

### Entries, judges, scores, outcomes, and history

| Entity | Essential columns and relations | State / invariant |
| --- | --- | --- |
| `forum_event_competition_entries` | competition/category/division/rule-version FKs, required `forum_event_registration_id`, stable key, `representation_type`, public display name, `status`, registered-by user, submitted/withdrawn/disqualified timestamps, `lock_version`, public safe status | States: `draft -> awaiting_acceptance -> awaiting_eligibility -> eligible -> judging -> finalized`, with `withdrawn` and `disqualified` terminal for ordinary scoring. Registration must be owned/authorized and compatible with the occurrence. |
| `forum_event_competition_entry_members` | entry FK, nullable user/pet/organization FKs, `member_role`, position, registration/evidence snapshot reference | Exactly one member subject per row, enforced with a portable check constraint; entry has one or more members. Application validates representation cardinality and managed-pet ownership. Never copy health/contact data into public fields. |
| `forum_event_competition_judge_assignments` | competition/category/division FKs, user FK, optional credential FK plus verification snapshot/checksum, event-team-membership FK, role, status, scoring starts/ends, assigned by/at, lock version | Status: `invited -> active -> recused/removed/expired`. Scope every score query to active assignment, assigned category/division, window, and current credential/rule requirement. |
| `forum_event_competition_judge_conflicts` | judge-assignment FK, entry FK, conflict type, disclosed-at, status, encrypted details/evidence, reviewed-by/at, resolution, audit key | An open/confirmed conflict is a hard scoring deny for that entry (and all configured connected entries); a disclosure is not an approval to score. |
| `forum_event_competition_score_criteria` | category/rule-version FK, stable key, name, position, `minimum_units`, `maximum_units`, `scale_factor`, `weight_basis_points`, required comment flag, drop-eligible flag | Criterion definition is immutable once judging starts. Scale factor and bounds are integer units; weight is integer basis points. |
| `forum_event_competition_scores` | judge-assignment/entry/criterion FKs, `current_revision_number`, submitted-at, lock version | Unique `(judge_assignment_id, entry_id, criterion_id)` makes one judge's score independent and prevents overwrite of a different judge. It records no mutable value field beyond a current revision pointer. |
| `forum_event_competition_score_revisions` | score FK, revision number, `value_units`, comment, submitted/corrected-by user, `reason_code`, corrected-from revision FK, immutable score input checksum, created-at | Unique `(score_id, revision_number)`. Initial revision must be submitted by assigned judge; correction references the prior revision and requires reason. Only before finalization; original values remain queryable. |
| `forum_event_competition_disqualifications` | entry/rule-version FKs, rule reference, official user, encrypted evidence reference, safe participant explanation, appeal availability/deadline, prize effect, effective-at, revoked-at | A disqualification is an audited official decision, not a score. It removes score eligibility and is private except safe status/explanation to the affected participant. |
| `forum_event_competition_welfare_withdrawals` | entry FK, handler user, actor user, reason code, encrypted detail, withdrawn-at | Handler can create it for an owned entry. It freezes scoring and projects only `withdrawn` publicly. |
| `forum_event_competition_result_versions` | competition FK, version number, source rule version/checksums, deterministic algorithm identifier, finalization idempotency key, status, calculated/finalized/published timestamps, actor, checksum | Unique `(competition_id, version_number)` and unique finalization idempotency key. Immutable after finalization; later appeal correction creates a new version, never edits an old one. |
| `forum_event_competition_result_rows` | result-version/entry/category/division FKs, rank, tie group/order, final status, score-summary units, public display-name/organization snapshots | Unique `(result_version_id, entry_id)`. Sort comparator is frozen in its parent snapshot; public values are deliberately copied from permitted public projection only. |
| `forum_event_competition_appeals` | entry/result-version/rule-version FKs, appellant, category/rule, private evidence references, submitted/deadline timestamps, status, reviewer, decision, result effect, decision timestamp, active key | States: `submitted -> under_review -> upheld/modified/reversed/rejected/expired`. Reviewer must have no judge/official/conflict connection. One open appeal per entry/result version through nullable unique `active_key`. |
| `forum_event_competition_prizes` and `..._awards` | competition/category FKs; source/sponsor/eligibility/tax-delivery/acceptance/unclaimed fields; award FK to immutable result row | Prize is a separate non-wager record. Sponsor relation is disclosed and cannot grant judging ability. |
| `forum_event_competition_review_signals` | competition/entry nullable FKs, typed signal, evidence reference, created-by/system source, status, assigned reviewer, resolution, private notes | States: `open -> triaged -> resolved/dismissed`. It is an internal, human-reviewed queue; no numeric risk score, public label, automatic action, or direct result mutation. |
| `forum_event_competition_history` | competition FK, optional subject type/id, actor user, type, reason code, previous/new state, encrypted/minimized metadata, idempotency key, created-at | Append-only audit for sensitive domain facts. Also emit a concise event-level `ForumEventHistory` summary when useful; it cannot be the sole evidence record. |

### Certificates linkage contract

EVENT-S06 should own certificate issue/version/file/revocation tables. P26 only
needs a foreign-keyable source contract: a certificate issue references the
immutable `forum_event_competition_result_rows.id` **and**
`result_version_id`; it is issued only from a finalized/published result
version. An appeal whose result effect changes a row creates a replacement
result version and asks the certificate Action to supersede/revoke/reissue via
its audited workflow. Do not create a parallel competition certificate file or
public URL.

## Database integrity, precision, and performance

1. Use `foreignId()->constrained()->restrictOnDelete()` for historical
   competition, rule, entry, score, result, appeal, and award relationships.
   Event archival/restriction replaces deletion. `nullOnDelete()` is suitable
   only for historical actor attribution where loss of the user must not delete
   evidence.
2. Create stable key and idempotency-key unique indexes for every external or
   repeatable mutation. Include at least the unique keys named above, plus
   `(entry_id, rule_version_id)` acceptance, `(judge_assignment_id, entry_id)`
   conflict, `(score_id,revision_number)`, `(competition_id,version_number)`
   for rules/results, and an active appeal key.
3. Index the actual bounded read paths: `(competition_id,status,id)`,
   `(category_id,division_id,status,id)`, `(entry_id,status,id)`,
   `(judge_assignment_id,status,scoring_ends_at,id)`,
   `(result_version_id,category_id,division_id,rank,tie_order,id)`,
   `(competition_id,status,deadline_at,id)` for appeals, and
   `(competition_id,created_at,id)` for history. Select display fields and
   relationship keys, eager-load one projection graph, and paginate judge and
   organizer queues.
4. Store every score as signed/unsigned integer `value_units`, never float or
   decimal calculated in PHP. `scale_factor` (for example 1000) and min/max
   are fixed in the criterion snapshot. Weight is integer basis points.
   Aggregation uses integer arithmetic with a documented rounding mode (for
   example half-even at a stated final unit), an overflow guard, and a frozen
   deterministic comparator: final units descending, configured tie rule,
   then immutable entry stable key ascending. The rule snapshot records scale,
   rounding, aggregation, dropped-score selection, and tie comparison.
5. Do not rely on a nullable multi-column unique index for category-wide judge
   assignment semantics. Model every assignment against a concrete category
   (and optional explicit division) or use a normalized assignment-scope table
   with non-null scope identity; SQLite/MySQL treat NULL uniqueness differently.
6. Enforce portable `CHECK` constraints for one entry-member subject and score
   ranges where the schema builder supports them, but repeat validation in the
   Action because SQLite test coverage and production adapters must agree.
   Do not use raw SQL to express business rules.

## Mutation, lock, idempotency, and immutable-history design

Every Action authorizes before and again after acquiring the locked parent
rows; validates a typed DTO/Form Request; maps only validated fields; runs a
short `DB::transaction(..., 3)`; writes append-only history before commit; and
dispatches notifications/jobs only after commit.

| Operation | Lock and idempotency | Required outcome |
| --- | --- | --- |
| Rule publish/material change | Lock competition + current rule version; unique `(competition, version)` and request key | New immutable version; find affected entries; create renewed-acceptance requirements only when material. |
| Entry submit / eligibility decision | Lock competition, category/division, registration, entry; entry request key unique | Snapshot registration/rule/members; never trust client pet or organization IDs; refuse inactive/expired registration or unmet acceptance. |
| Assign judge / disclose conflict | Lock competition and assignment; unique assignment/scope and conflict key | Confirm active team/organization authority, identity/credential prerequisite, category scope, then deny score where conflict is open/confirmed. |
| Submit score | Lock competition, assignment, entry, criterion and score row; unique score triple/request key | Recheck judging state/window/category/conflict/entry eligibility; append initial revision only. Same key with different payload is validation conflict, not a silent retry. |
| Correct score | Lock competition, score, current revision; correction request key | Only authorized actor before finalization; append a revision with original link/reason, audit the pending result effect. |
| Welfare withdrawal/disqualification | Lock competition + entry; command key | Atomically change entry operational status, record private evidence/safe explanation, stop scoring; welfare projection is neutral. |
| Finalize | Lock competition first, then entries/assignments/scores/withdrawals/disqualifications in stable primary-key order; unique finalization key | Move parent to `finalizing`; verify snapshot, required submitted scores, eligibility, conflicts, withdrawals/disqualifications; compute once from locked immutable revisions; insert a new result version/rows/checksum; move to `finalized`. A retry returns the same version only if payload/checksum match. |
| Publish result | Lock competition + target finalized version; publish request key | Publish a complete immutable version atomically; never expose draft calculations. |
| Appeal decide | Lock appeal, entry, source version, competition; decision key | Recheck independent reviewer and deadline. A changed result creates a successor result version, supersedes public pointer, audits effect, and invokes certificate replacement contract after commit. |

`lockForUpdate()` is advisory/no-op in SQLite but remains required for adapters
that support row locks. SQLite correctness therefore also depends on a single
short write transaction, unique request/version keys, bounded transaction
retries, and assertions that a duplicate finalization cannot create a second
version. Do not use cache locks as the sole correctness control; they may be a
short-lived throughput optimization only after database correctness exists.

The Action layer must be the only mutable writer. Existing data is not
immutable merely because a model hides it, so result/rule/score revision rows
need no update/delete Actions, final-class models with restricted fillable
fields, audit tests for rejected ordinary edits, and database delete
restrictions. Never edit a result row to resolve an appeal.

## Policy matrix and projections

| Ability | Allowed principal | Required scope / denial |
| --- | --- | --- |
| View public competition/results | Any actor permitted by `ForumEventPolicy::view`, only when result visibility and publication state allow | No scores, conflicts, evidence, eligibility, contact, health, address, private team or anti-cheating data. |
| View own entry / accept rules / withdraw welfare / appeal | Active entrant/handler with an owned entry/member and permitted event registration | Own entry only; welfare detail is private; no judge data or other entries. |
| Create/configure categories, rules, prizes, publish | Event owner/administrator/primary or co-organizer, still inside organization restriction capability | Explicit new competition ability; do not reuse broad schedule management. |
| Eligibility/disqualification | Explicit assigned competition official with only required participant data; welfare officer can act for welfare | Scope category/entry; official cannot decide a connected entry without documented independent path. |
| Judge queue/view/score | Active judge assignment with verified prerequisite, assigned category/division, open scoring window, no open/confirmed conflict | Only assigned entries and own score revisions; no other judge scores or private conflicts. |
| Score correction | Original judge before finalization, or explicit scorekeeper/organizer authority as rule permits | Must append correction; never overwrite or alter another judge's score. |
| Finalize/publish | Explicit scorekeeper/organizer authority, separate from ordinary judge role | No finalization with incomplete/invalid score set; no public result until publish. |
| Review appeal / anti-cheating signal | Explicit independent reviewer/auditor with no judge, original official, appellant, entry-member, sponsor, or unresolved conflict relation | Private evidence only; no automatic allegation/penalty. |
| Certificate issue/download/revoke | EVENT-S06 owner, source-recipient and authorized staff only | Private storage/download authorization; result source pointer must be final and immutable. |
| Audit/history | Narrow organizer/auditor/admin scope; entrants see only their participant-safe decisions | Do not reveal private evidence or unrelated member data. |

Implement separate public projection DTOs/resources (category/result card and
ranked result row) and private operations DTOs (judge queue, organizer review,
appeal). Blade and Livewire receive already-scoped primitive arrays, never
models or encrypted evidence. Public rows may contain only the fields allowed
by `competition-014`: category, pre-approved entry display name, result/rank,
score summary, allowed organization display, and verified final status.

## Notification dedupe points

Use the existing `ForumEventNotifier` only with a recipient component because
`forum_notifications.deduplication_key` is globally unique. Each key should
also identify an immutable version or lock version:

- material-rule renewal: `competition-rule-renewal:{competition}:{ruleVersion}:{entry}:{user}`;
- eligibility decision/request: `competition-eligibility:{entry}:{evaluation}:{user}`;
- judge assignment, conflict action, and scoring-window reminder:
  `competition-judge:{assignment}:{state-or-window}:{user}`;
- score correction notice to the affected judge/organizer:
  `competition-score-correction:{score}:{revision}:{user}`;
- result publication/supersession: `competition-result:{competition}:{resultVersion}:{user}`;
- appeal receipt/decision: `competition-appeal:{appeal}:{state}:{user}`; and
- certificate issued/revoked/replaced: owned by EVENT-S06 and keyed by its
  certificate version and recipient.

Do not notify before commit. A result retry must not duplicate a notification;
a successor result version intentionally produces a new notification.

## Factories, guarded seeds, and test matrix

Factories should produce valid minimal graph states, not arbitrary raw arrays:
competition with immutable rule version; category/division; confirmed event
registration and owned pet; eligible entry with acceptance; active scoped judge
with appropriate credential state; initial score revisions; finalized result
version; closed appeal. Use named states such as `eligible()`,
`conflictedJudge()`, `judgingOpen()`, `finalized()`, `withdrawnForWelfare()`,
and `disqualified()`. Recycle shared event/organization/user parents and
create mutable data inside each Pest test. All factories must respect existing
foreign-key restrictions and supported locales.

Demo seed data belongs only in local/demo/testing and must use stable keys,
environment gates, idempotent `firstOrCreate`-style reconciliation, no real
documents/private health data, no payment simulation, no fake certificate
download, and no fabricated cheating accusations. Minimum useful scenarios:

1. a public published competition with two categories, a legitimate tie and a
   neutral welfare withdrawal;
2. an organization-only competition with private judge/eligibility details;
3. a material rule revision awaiting a participant's renewed acceptance;
4. an open conflict that blocks a judge score; and
5. a finalized result followed by an appeal successor result/certificate
   replacement fixture once EVENT-S06 exists.

| Test class / scenario | Defect prevented |
| --- | --- |
| Migration rollback/reapply around populated events | Missing FK/index/check/unique and destructive upgrade. |
| Policy matrix datasets (anonymous, entrant, unrelated entrant, inactive/revoked judge, scorekeeper, organizer, welfare officer, auditor, administrator) | Role alone or former membership leaks authority. |
| Cross-event/category/division/organization queries | ID substitution and tenant/privacy leakage. |
| Entry representation and member ownership datasets | Unauthorized pet/organization substitution, duplicate member, wrong registration, invalid cardinality. |
| Rule acceptance material-change test with frozen time | Old rule silently retained after a material change. |
| Judge identity/window/category/conflict tests | Scoring outside assignment, after expiry, or for a connected entry. |
| Score uniqueness and correction history tests | One judge overwrites another, losing original score, bad reason, correction after finalization. |
| Exact scale/rounding/drop/tie datasets | Float drift, nondeterministic equal totals, wrong tie comparator, overflow. |
| Two concurrent finalization attempts plus same-key/different-payload replay | Duplicate result versions, concurrent score edits, or silent idempotency collision. |
| Missing score, disqualification, welfare withdrawal finalization tests | Publish incomplete/ineligible result or expose punitive welfare status. |
| Appeal deadline/independent-reviewer/result-successor tests | Original judge/official reviews appeal, old version mutates, certificate source drifts. |
| Public resource/Livewire rendering and HTML escaping | Contact/address/health/evidence/conflict/anti-cheating detail leaks or unescaped display name. |
| Notification fake assertions | Duplicate/out-of-transaction notifications and globally colliding keys. |
| Factory persistence, guarded repeat seed, locale parity, bounded query budget | Broken fixtures/seeds, unlocalized state, N+1 organizer/judge/public tables. |

Run targeted feature/policy/action tests first, then Pint and Larastan, serial
SQLite database tests, a fresh migration/complete/repeat seed check, and a
public/participant/judge/organizer browser/accessibility flow after UI exists.

## Integration and rollback risks

- P26 depends on P16/P21/P22 per the master-plan graph. In particular,
  organization authority, event lifecycle/registration, and schedule/occurrence
  scope must remain source-of-truth; do not copy their roles or private venue
  data into competition records.
- EVENT-S06 certificates, EVENT-S07 feedback, EVENT-S08 retention/legal hold,
  P27 sponsors, payment/ticket work, and incident/welfare work are neighboring
  scopes. Establish only explicit foreign-key/event contracts; do not absorb
  their schemas into this delivery.
- Existing `ForumEventHistory` and notifications use globally unique keys.
  Ambiguous or reused keys can turn independent operations into silent no-ops.
- Existing `ForumEventTeamRole::Judge` and seeded competition type are
  presentation/authority scaffolding only. Treating either as proof of verified
  judges, scoring, or public results would violate the current docs.
- Raw encrypted text in existing event tables does not establish retention,
  evidence-file, or secure-download boundaries. Evidence references must use
  the applicable private media authority rather than URL strings.
- A migration must be additive and reversible before production use. If rollout
  is interrupted after records exist, leave competition features disabled behind
  a capability/state gate, retain immutable records, and roll forward with a
  corrective migration; do not drop evidence/result tables to “undo” a launch.

## Smallest durable implementation recommendation

Deliver the first vertical slice as an internal, non-public **configuration,
entry, judge-conflict, immutable-score, and deterministic-finalization**
boundary for one competition category/division. It should include the core
tables, immutable rule/score/result/history records, explicit policies,
Actions, real factories and targeted Pest coverage; publish only after results
are finalized. Defer prizes, certificate file issuance, automated signals,
rich entry representations beyond supported registration/pet pair, and public
UI expansion to their owning follow-up packages, but reserve the normalized
foreign keys and version contracts now. This is the smallest slice that can
truthfully meet P26's judge-isolation, conflict, precise-score, result-version,
and no-gambling acceptance criteria without creating schema debt.
