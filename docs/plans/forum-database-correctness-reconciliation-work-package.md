# Forum Database Correctness Reconciliation Work Package

Date: 2026-08-03

Status: verified on 2026-08-03

## Exact Scope

This Phase 3 package owns only `forum.data.0006`, `forum.data.0007`, and
`forum.data.0054` through `forum.data.0072`. It reconciles the immutable
database-correctness source contract with the live SQLite schema and current
Laravel operations. It does not claim that every table in the 38,377-atom
catalogue has been globally audited.

All existing forum topics, answers, votes, acceptances, engagements, reports,
moderation cases/actions, media interactions, adoption cases, events, and
relations must survive the package unchanged.

## Classification

| ID | Requirement | Classification | Current evidence or required closure |
| --- | --- | --- | --- |
| `forum.data.0006` | database transactions | Evidence gap | Prove rollback and retry-bounded transaction use in vote, reaction, acceptance, and case-closing operations. |
| `forum.data.0007` | database constraints | Implementation and evidence gap | Prove current foreign/unique constraints and add portable value constraints where Laravel's schema grammar supports them. |
| `forum.data.0054` | database correctness and concurrency section | Evidence gap | Close only when every child atom in this package has independent evidence. |
| `forum.data.0055` | add appropriate constraints and controls | Evidence gap | Record why each selected constraint/control is appropriate to its concrete table and operation. |
| `forum.data.0056` | foreign keys | Evidence gap | Inspect and test the answer-vote, topic-acceptance, reaction, moderation, and adoption relations. |
| `forum.data.0057` | unique indexes | Evidence gap | Prove durable uniqueness for vote, reaction, acceptance identity, case number, and event/idempotency identities. |
| `forum.data.0058` | compound indexes | Evidence gap | Prove the indexes used for answer vote totals, accepted-answer selection, moderation queues, and scoped relations. |
| `forum.data.0059` | check constraints where supported | Implementation gap | Add schema-builder enum constraints for the fixed vote/reaction value sets and verify invalid values fail at the database boundary. |
| `forum.data.0060` | enum casts | Evidence gap | Prove representative forum lifecycle, adoption-case, event, and subscription casts; add casts for constrained vote/reaction values. |
| `forum.data.0061` | JSON casts | Evidence gap | Prove structured topic, acceptance metadata, moderation metadata/evidence, and event payloads hydrate as arrays. |
| `forum.data.0062` | timestamps | Evidence gap | Prove immutable/current timestamps and lifecycle timestamps on representative records. |
| `forum.data.0063` | soft deletion or archival | Evidence gap | Prove non-destructive topic, moderation-case, and adoption-case archival/closure boundaries. |
| `forum.data.0064` | optimistic locking | Implementation and evidence gap | Retain topic/adoption locks and add a moderation-case lock version for conflict-safe closure. |
| `forum.data.0065` | transactions | Evidence gap | Prove multi-record transitions commit or roll back as one unit. |
| `forum.data.0066` | row locking | Evidence gap | Prove the selected vote, reaction, topic/answer acceptance, and case rows are locked before race-sensitive mutation. |
| `forum.data.0067` | idempotency keys | Implementation and evidence gap | Add a unique moderation-case closure key and prove replay returns the original closed state without a second close event. |
| `forum.data.0068` | database uniqueness for one-vote rules | Evidence gap | Reject a second vote for the same answer/member at the database boundary. |
| `forum.data.0069` | double voting | Evidence gap | Exercise repeated/competing vote attempts and prove one durable vote plus correct aggregate/reputation effects. |
| `forum.data.0070` | double reaction | Evidence gap | Exercise repeated/competing reaction creation and prove one durable member/photo reaction. |
| `forum.data.0071` | simultaneous answer acceptance | Evidence gap | Exercise stale competing acceptance and prove one canonical accepted answer when multiple acceptance is disabled. |
| `forum.data.0072` | simultaneous case closure | Implementation and evidence gap | Add one policy-authorized close operation using row lock, expected version, and unique idempotency key; stale competing closure must fail without duplicate events. |

## Schema And Compatibility

- Add one forward migration; never edit a historical migration.
- Change only fixed, already validated vote/reaction value columns through
  Laravel Schema Builder so SQLite receives a `CHECK` and other supported
  adapters retain their native equivalent.
- Add `lock_version` and nullable unique `closure_idempotency_key` to
  `forum_moderation_cases`, with queue/retention indexes only where a current
  query requires them.
- Defaults must be mirrored on the affected model.
- `down()` restores the prior string columns and removes only newly added
  columns/indexes. No DML backfill is required because defaults cover all
  existing cases and existing vote/reaction values are already allow-listed.

## Application Boundary

- Fixed value sets become PHP backed enums and Eloquent casts.
- `CloseForumModerationCase` is one Action. It authorizes before and after the
  lock, uses a short retry-bounded transaction, checks the expected version,
  writes `closed_at`, increments `lock_version`, records a unique closure key,
  and appends report events without rewriting prior evidence.
- A replay with the same closure key returns the existing closed case. A
  different request using a stale version fails before changing rows.
- No controller, route, Livewire, Blade, cache, or notification surface is
  added. Existing localization files receive only the validation and audit
  messages required by the Action boundary.

## Implemented Result

- The forward migration constrains vote and reaction values without replacing
  either table, and adds moderation-case optimistic versioning plus a nullable
  unique closure request key.
- `ForumVoteValue` and `PhotoReactionType` are the single PHP value catalogues;
  the corresponding Eloquent models cast to those enums while presenters keep
  their existing string output contracts.
- `RecordAnswerVote` and `PhotoInteractionState` retain their retry-bounded
  transactions and row locks while using the enum values.
- `AcceptForumAnswer` now updates the canonical `accepted_answer_id` when a
  competing selection replaces the previous single accepted answer.
- `CloseForumModerationCase` authorizes before and after its row lock, rejects
  unresolved or stale cases, treats an identical closure key as a replay,
  increments `lock_version`, and records all bounded report audit events in
  one Eloquent bulk insert. The closure path therefore has constant query
  growth from one through one hundred linked reports.
- The closure key is hidden from model serialization and is not copied into
  audit-event metadata or logs.

## Atomic Evidence Map

| ID | Independent implementation and test evidence |
| --- | --- |
| `forum.data.0006` | Retry-bounded transactions in `RecordAnswerVote`, `PhotoInteractionState`, `AcceptForumAnswer`, and `CloseForumModerationCase`; the 101-report test proves complete rollback. |
| `forum.data.0007` | Forward Schema Builder migration plus direct invalid-value, duplicate, and missing-FK rejection tests. |
| `forum.data.0054` | Entire bounded contract in `ForumDatabaseCorrectnessTest`; no adjacent concurrency atom is promoted. |
| `forum.data.0055` | Table-specific FK, unique, compound, check, version, and idempotency controls documented here and asserted through schema metadata or database rejection. |
| `forum.data.0056` | `Schema::getForeignKeys()` evidence for votes, acceptances, reactions, and moderation cases plus a direct missing-answer FK rejection. |
| `forum.data.0057` | Named vote/acceptance/case unique indexes and generated reaction unique index asserted through `Schema::getIndexes()`. |
| `forum.data.0058` | Vote aggregate, active acceptance, reaction aggregate, moderation queue, and subject/status compound indexes asserted explicitly. |
| `forum.data.0059` | Schema Builder enum changes produce SQLite `CHECK` clauses; raw invalid vote and reaction writes raise `QueryException`. |
| `forum.data.0060` | `ForumVoteValue`, `PhotoReactionType`, existing topic/adoption enums, model casts, and persisted hydration assertions. |
| `forum.data.0061` | Topic structured data, acceptance metadata, and moderation-case metadata hydrate as arrays. |
| `forum.data.0062` | Acceptance, topic archive, moderation resolution/closure, and adoption closure timestamps hydrate as immutable timestamps. |
| `forum.data.0063` | Existing topic archival and adoption/moderation closure boundaries are persisted and non-destructive. |
| `forum.data.0064` | Existing topic/adoption versions plus the new moderation `lock_version`; stale closure and canonical answer assertions cover conflicting edits. |
| `forum.data.0065` | The bounded case-limit exception occurs after an attempted case update and proves the transaction rolls back case and event writes together. |
| `forum.data.0066` | Vote, reaction, acceptance, and case operations lock their race-sensitive rows before mutation. |
| `forum.data.0067` | Nullable unique closure key, same-key replay, different-key stale rejection, and one-event assertion. |
| `forum.data.0068` | Existing `forum_votes_answer_user_unique` is asserted and a second direct answer/member vote is rejected. |
| `forum.data.0069` | Replayed `RecordAnswerVote` leaves one vote, one helpful aggregate, and the latest reason. |
| `forum.data.0070` | Direct duplicate reaction rejection plus the existing same-reaction toggle regression in `PhotoViewerTest`. |
| `forum.data.0071` | Competing answer selections leave exactly one active acceptance, one accepted answer flag, and the correct canonical topic pointer. |
| `forum.data.0072` | Policy-authorized `CloseForumModerationCase`, row lock, expected version, unique replay key, bounded audit events, rollback, and unauthorized/stale tests. |

The deterministic evidence overlay now marks only these 21 IDs verified after
the full serial suite and full Larastan passed on the combined shared tree.

## RED And Regression Tests

`ForumDatabaseCorrectnessTest` must initially fail on the missing database
checks and moderation closure contract, then prove:

1. exact foreign, unique, compound, and check constraints;
2. database rejection of invalid/duplicate values;
3. enum, JSON, integer-version, and immutable timestamp casts;
4. transaction rollback leaves no partial moderation close/event state;
5. vote and reaction uniqueness under duplicate attempts;
6. single accepted answer under stale competing selection;
7. idempotent case-close replay and stale competing close rejection;
8. existing rows survive migration rollback/reapply in the populated
   migration check.

## Acceptance Gates

1. Focused database-correctness, forum vote/reputation, photo interaction,
   topic lifecycle, moderation, and adoption tests pass sequentially.
2. Laravel Boost schema inspection confirms the intended constraints and
   indexes on the migrated SQLite database.
3. Fresh migration, repeat seed, and populated rollback/reapply pass.
4. Forum source preservation and generated requirements checks pass.
5. Pint, Larastan, the full sequential Pest suite, Composer/npm audits, and
   the production Vite build pass before evidence is marked verified.
6. Only these 21 IDs receive evidence; every other open ID remains open.
7. The staged diff is reviewed, committed, and pushed directly to `main`.

## Stop Conditions

Stop without a completion claim if a migration rewrites or loses existing
rows, a value constraint is not portable through supported Laravel grammars,
one duplicate mutation survives, a stale closure produces an event, rollback
cannot restore the prior schema, or any selected ID lacks independent
file/test evidence.

## Observed Verification

- Focused database-correctness contract: 12 tests and 74 assertions passed.
- Complete sequential repository suite: 2,303 tests and 76,179 assertions
  passed.
- Fresh isolated SQLite: 117 migrations, 196 tables, and repeated seed kept
  the user count at 5.
- Full Pint and Larastan passed; Composer validation/audit, npm audit, Vite
  8.2.0 production build, and config/event/route/view cache compilation
  passed.
- Source preservation and deterministic generation passed for 38,377
  requirements and checksum
  `cbb7d3a36f3750106c4751191ddd7d882d922ce0ae0e0b12aed318c809206ea1`.
- The loopback Chrome gate passed EN/LT/RU and 320-1920px surfaces with zero
  overflow, unnamed controls, invalid images, duplicate IDs, or console
  errors.
