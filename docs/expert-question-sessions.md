# Verified Professional Question Sessions

## Purpose And Boundary

`ForumExpertSession` is a scheduled public educational question session hosted
by a currently verified professional. It is not an appointment, private
consultation, medical diagnosis, prescription, attorney-client engagement, or
formal legal representation.

The session domain reuses:

- `ExpertProfile` and `Credential` for independent host qualification;
- the unified `ForumReport` pipeline for session, question, and answer reports;
- the existing EN, LT, and RU localization architecture;
- ordinary Laravel cache/rate-limit infrastructure without a queue, scheduler,
  websocket server, or external search dependency.

Reputation, trust, badges, administrator status, audience size, or answer
popularity never create professional authority.

## Qualification

`ForumExpertSessionHostEligibility` requires all of the following:

1. an active user with a verified email;
2. ownership of the selected published expert profile;
3. a current verified or expiring profile projection;
4. an unexpired verified or expiring credential;
5. an exact professional-scope match;
6. an exact or global jurisdiction match.

Qualification is rechecked when a session is created, moderated, answered, or
corrected. Credential evidence, identifiers, reviewer notes, and file paths
are never copied to or exposed by a session.

## Schedule And Lifecycle

The question window, session phase, and archive state are derived from stored
UTC timestamps. The host's validated IANA time zone is preserved and used for
schedule presentation. No cron process is required to prevent early or late
submissions. The schedule order is question opening, question closing, session
start, and session end.

Published sessions may accept questions only inside the configured window.
Archival retains the complete queue, answers, corrections, reports, and
append-only history. Destructive deletion is not an application operation.

## Question Queue

Question submission requires an active verified member and:

- server authorization against the current session;
- bounded localized validation;
- a UUID idempotency key;
- a per-session user limit;
- a user-scoped rate limit;
- transactionally assigned queue position;
- pending moderation before public visibility.

Idempotent replay by the same user and session returns the original question.
A conflicting owner or session is rejected. Pending questions are visible
only to their author, a currently authorized host, or an administrator.
Approval is required before public visibility or reporting by another member.

The states `queued`, `selected`, `answered`, `declined`, `withdrawn`, and
`removed` remain distinct. Unanswered presentation is derived from these typed
states rather than inferred from reaction or reply counts.

## Answers, Sources, And Corrections

Only the currently qualified host can answer an approved eligible question.
One answer per question and unique idempotency keys are enforced by the
database.

Source links are bounded labelled HTTP(S) URLs. They are displayed as escaped
external links and are never fetched by the server, avoiding an SSRF boundary.

Corrections use optimistic version checks. Every correction stores the
previous and corrected body/source snapshots, actor, reason, and monotonically
increasing version. Session history and correction records are append-only, so
an edit never silently overwrites the only historical copy.

## Safety And Reporting

Every public workspace shows a localized disclaimer stating that the session
does not replace a veterinarian, physician, emergency service, public-health
authority, or qualified lawyer where applicable.

The platform may describe an answer as published or corrected. It never turns
an answer into a diagnosis, dosage, prescription, legal opinion, or guaranteed
professional outcome.

Sessions, approved public questions, and public answers can be reported
through `SubmitForumReport`. Reporter identity and moderation evidence remain
private. A guessed pending-question identifier does not grant read or report
access.

## Livewire Interface

`ForumExpertSessionDirectory` and `ForumExpertSessionWorkspace` are class-based
Livewire components with separate form objects and passive Blade templates.

- Directory query, scope, and period filters use stable URL state.
- Results and question queues are bounded and select presentation fields.
- Public state contains scalar filters, form values, and locked IDs rather
  than model graphs, credentials, or private moderation data.
- Every mutation reloads the subject and reauthorizes the concrete action.
- Loading, dirty, offline, validation, empty, unanswered, archived, and
  correction states are localized.
- Pages have one logical heading, explicit labels, live status regions,
  keyboard-operable controls, visible focus, and mobile-safe touch targets.

Normal links and server-rendered routes remain usable without Livewire
navigation.

## Schema

The additive migration
`2026_07_31_001240_create_forum_expert_session_tables.php` creates:

- `forum_expert_sessions`;
- `forum_expert_session_questions`;
- `forum_expert_session_answers`;
- `forum_expert_session_corrections`;
- `forum_expert_session_history`.

Stable keys, idempotency keys, one-answer-per-question, queue ordering,
answer-version ordering, foreign keys, optimistic versions, and bounded
directory/history indexes are database-enforced. Sensitive idempotency data
is hidden from model serialization.

## Backfill And Seeding

`ForumExpertSessionBackfillSeeder` is production-safe and only normalizes the
non-null disclaimer version for existing normalized sessions. No ordinary
topic, answer, consultation, guide, event, or expert publication is inferred
to be a professional session.

`ForumExpertSessionDemoSeeder` is restricted to explicitly allowed demo,
local, and testing environments. It creates one deterministic qualified host,
credential, session, moderated question, and answer. Repeated execution
preserves session and credential IDs and does not duplicate records.

## Deployment And Recovery

1. Back up the database.
2. Deploy code and run the additive migration.
3. Run `ForumSystemSeeder` or `ForumExpertSessionBackfillSeeder`.
4. Build assets and warm supported caches.
5. Verify directory, session, credential expiry, queue privacy, and reports.
6. Run demo seeding only in an explicitly allowed non-production environment.

Before user data exists, migration rollback removes only the new tables. After
sessions exist, recover through a forward fix and retain normalized records.
Never drop the session tables to roll application code back.

## Verification

Primary coverage is
`tests/Feature/Forum/ExpertQuestionSessionWorkflowTest.php`. Professional
credential, moderation, localization, architecture, factory, and seeder
integration are covered by the corresponding shared feature suites.

Exact requirement IDs, commands, observed results, and completion evidence are
maintained in
`docs/plans/forum-phase8-expert-question-sessions-work-package.md`.
