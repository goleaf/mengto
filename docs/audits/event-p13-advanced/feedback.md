# EVENT-S07 — Event Feedback Discovery Report

Date: 2026-08-30  
Status: discovery only — no production implementation, migration, test, translation, plan, or canonical documentation was changed.

## Requirement Boundary

The immutable source is docs/requirements/forum-source-prompt.md, section 24, feedback-001 through feedback-008. Generated feedback/archive atoms are event.feedback.archive.0001 through event.feedback.archive.0074, all planned / discovered; lost-property privacy is additionally event.media.privacy.0091. P29 requires typed private/public feedback, verified-attendance markers, organizer final reports, follow-up, and archive rules.

| Source requirement | Exact IDs | Boundary |
| --- | --- | --- |
| feedback-001: separate event, organizer, venue, trainer, speaker, session, accessibility, safety, ticket feedback; no forced public review | event.feedback.archive.0001-.0012 | Authenticated typed feedback with explicit visibility; private is the default. |
| feedback-002: verified role marker without private attendance disclosure | .0013-.0021 | Server-derived, minimum-necessary source marker; never browser input. |
| feedback-003: anonymous internal safety, harassment, accessibility, staff conduct, welfare feedback | .0022-.0028 | Private, organizer-blind feedback with restricted moderation/safety readers. |
| feedback-004: moderation and bombing, retaliation, doxxing, personal-attack, fabrication, incentive protection | .0029-.0037 | Event-specific feedback state/history, plus reuse of existing report/case/recusal architecture. |
| feedback-005: organizer final report | .0038-.0052 | Versioned, policy-scoped final report with typed source links only. |
| feedback-006: follow-up resources, certificates, results, photos, next event, support, lost property, incidents, refunds | .0053-.0063 | Typed follow-up links that reauthorize their source at display/delivery. |
| feedback-007: privacy-safe lost property | .0064, event.media.privacy.0091 | Canonical lost-property/contact-relay reuse; never publish contacts. |
| feedback-008: safe archive and no registration | .0065-.0074 | Separate allowlisted archive projection; it cannot reuse the present archived-event policy path. |

Selected cross-cutting evidence: event.notification.calendar.0025 is feedback notification; applicable authorization, validation, localization, factory, seed, testing, performance, Livewire, documentation, and release atoms are not promoted by this discovery report.

## Current State And Reuse

| Existing authority | Reuse | Gap / do not reuse as |
| --- | --- | --- |
| ForumEvent, occurrence, session, team, registrations | Canonical identity, scope and verified participation source. | Free-form target, public attendee roster, or alternative event aggregate. |
| ForumEventReview, SubmitForumEventReview, form/workspace, ForumEventPolicy::review | Foundation review: event lock, one review/reviewer, key replay, validation, eager-loaded bounded public list. | Full feedback: currently generic automatic publication, published/hidden only, no targets/visibility/source marker/disclosure/history/case link; policy allows a merely confirmed registration. |
| ForumReport, SubmitForumEventReport, reason catalogue, ForumModerationCase, actions, recusal | Private report details and established reasons include doxxing, fabricated-review, review-bombing, coordinated-voting and conflict of interest. | Feedback root or organizer moderation. The generic ten-per-hour report cap is not feedback anti-abuse control. |
| ForumEventHistory / event audit | Event audit and idempotent-action style. | Private feedback event/version history. |
| ForumNotification, ForumEventNotifier | Localized in-app notification, unique dedupe key. | A private-feedback outbox; direct notification risks retry duplication and disclosure. |
| Place, PlaceAccessGrant, SearchCase, ContentMediaAsset | Venue privacy, lost-pet escalation/contact relay, private media patterns. | Public venue/contact/attachment feedback fields. |
| ForumEventStatus::Archived and registration policy | Terminal archive and no-registration concepts. | Public archive: ForumEventPolicy::view() currently denies all archived events. |

EventWorkflowTest only proves checked-in submission, same-key replay, outsider refusal, and generic event reports. Current query tests cover lifecycle/directory, not feedback aggregates; localization parity covers current registries, not a typed feedback catalogue. The event index therefore correctly treats the feedback aggregate as unverified.

## Normalized Durable Model

Use an additive ForumEventFeedback package. Feedback, final report, follow-up, and archive projections have different readers and retention, so remain separate.

### Feedback root, targets, verification, history

forum_event_feedback:

- Required forum_event_id; nullable occurrence/session only after same-event validation; author_user_id, stable key, locale/timezone snapshot, submission idempotency key, lock_version.
- Closed visibility (private_internal, public_opt_in), anonymity_mode (identified, organizer_blind), status (submitted, pending_moderation, published, hidden, withdrawn, removed), and incentive_disclosure (none, provided, offered, unknown).
- Encrypted text and hidden triage metadata. Anonymous means shielded from organizer/target/public, not unaccountable: only a narrowly authorised independent reviewer can resolve the author.

forum_event_feedback_targets:

- Feedback FK; closed kind: event, organizer, venue, trainer, speaker, session, accessibility, safety, ticket_process; rating 1–5 where applicable.
- Concrete, non-polymorphic FKs only to canonical target authorities: forum_event_session_id, place_id, user_id, and later trainer/speaker/ticket FKs. Validate target kind, role and event ancestry under lock. Unique feedback-target pair.
- One participant has one active feedback root per event/occurrence; a root can contain distinct typed targets. Do not accept arbitrary IDs or a generic morph pair.

forum_event_feedback_verifications:

- Feedback FK, source_kind (registered, checked_in, online_attended, vendor, volunteer, speaker), canonical source row/snapshot, evaluation time, public marker.
- Action derives source after locked reload; public display emits only a localized broad marker. It never exposes status timeline, check-in time/method, ticket, pet, invitation, staff assignment, venue/access detail, or a missing-source reason.

forum_event_feedback_events:

- Append-only feedback FK, actor, event type, status change, reason/translation key, safe metadata, idempotency key and time. Reject mutation/deletion; corrections append a version/event and never copy source text to metadata.

State transitions: submitted to pending_moderation to published; submitted to private_internal; pending_moderation/published to hidden or removed; every non-terminal state to withdrawn; hidden/removed back to pending_moderation only through independent moderation with reason. Private internal feedback never becomes public through a toggle. Withdrawal removes public projection/aggregate but retains private audit and lawful moderation hold.

### Final reports, follow-up, archive

forum_event_final_reports and version rows carry one versioned report per event, author/editor, draft/submitted/published/superseded, lock/version/idempotency keys, public summary separated from encrypted operational fields. Typed links may represent attendance/no-show, schedule, safety, incidents, vendors, volunteers, result, certificate, ticket/refund only when the source aggregate exists. Until P20/P21/P22/P26/P27/P28 authorities exist, the field is unavailable, never zero or simulated.

forum_event_follow_up_items has a closed kind plus exactly one source link. Reauthorize every resource/certificate/result/photo/next-event/support-contact relay/lost-property/unresolved-incident/refund display. A link is not a continuing capability after staff removal or source revocation.

forum_event_archive_projections is allowlisted and versioned: final status, date, organizer public identity, public summary, approved public media/results, authorised future-series link. It excludes registration, private feedback, low-count metrics, exact venue/contact, attendance, safety/incident, ticket/refund and unpublished follow-up. Registration action independently refuses archive state.

## Policy, Privacy, Moderation, And Abuse

| Principal | Allowed | Denied |
| --- | --- | --- |
| Eligible participant | Create/replay/withdraw own feedback and view own safe receipt. | False source/target, others private feedback, turning internal feedback public. |
| Organizer, target subject, ordinary staff | Published projection/thresholded aggregate; final report only with dedicated authority. | Organizer-blind identity, private safety/harassment/accessibility/welfare content, status decision or retaliation. |
| Independent safety/moderation reviewer | Necessary private content, classify/redact/hide/link report/case with audit and recusal. | Own conflict case, silent source edit, author/attendance disclosure, fabricated aggregate. |
| Administrator | Existing case/action/appeal/recusal authority. | Treat reports as proof; publish private data. |
| Public | Permitted published feedback, broad marker, thresholded aggregate/archive. | Private content/identity/source, low-count data, moderation, contacts, location. |

Feedback reports reuse SubmitForumReport only after it deliberately supports the feedback subject. Reuse the existing reason taxonomy, OpenForumModerationCase, policies, action catalogue, appeal and recusal. A report routes triage; it is not automatic hiding, punishment, or proof. Organizer/target/staff conflict requires recorded recusal and replacement before restricted access.

Screen and route but do not auto-convict: private data patterns, personal attacks, targeted harassment, missing incentive disclosure, high-velocity/correlation signals and source mismatch enter pending_moderation/case. Doxxing gets immediately restricted visibility and existing sensitive-data-removal handling. Bombing controls are source eligibility, one active root/actor/event, per-target dedupe, actor+IP-aware limits, correlation signals, pending state and published-only aggregates. Retaliation controls are organizer-blind private feedback, independent recusal, minimum notification content and no organizer status/identity power.

## Idempotency, Limits, Aggregates, Notifications

Each mutation takes an opaque idempotency key. In a short transaction, lock feedback/event/source rows, revalidate actor/event/target/payload and return only identical replay; cross-user/event/target/payload reuse is conflict. Database constraints: unique submission key, unique active event-or-occurrence/author root, unique target pair, unique event history key. Lock status/projection rows in stable order; never hold a lock during screening/delivery.

Use named limits keyed by actor_id plus normalized IP, separately for creation, state/visibility mutation and reports. Exact rates are a release-policy measurement, but tests must prove independent actors do not share quota, changing IP cannot bypass account cap, retry costs no quota, and a refused mutation creates no row/history/notification. Existing generic report limit remains defence in depth only.

Use a private materialized forum_event_feedback_aggregate_projections row per event/occurrence/target, updated within the same moderation transaction: published unique contributor count, integer rating sum, version, timestamp. This avoids raw SQL and direct grouping over text. Public presenter emits nothing until at least five distinct source-verified published contributors exist for that exact target/scope; below five it emits no count, average, histogram, delta or threshold hint. At/above five, show only half-star rounded average and broad marker; never histogram, source-role breakdown, individual contribution or time delta. Recompute on hide/remove/withdraw/reopen. Index final lookup as forum_event_id, occurrence_id, target_kind, target_key, published_contributor_count, id subject to populated SQLite EXPLAIN QUERY PLAN.

Create a feedback notification intent in the transaction and deliver after commit. Database-unique dedupe key is event-feedback:stable-key:event-type:version:recipient-actor-key:channel. Worker locks intent, reauthorizes recipient/event membership, localizes from a key, stores only a safe workspace link, and marks delivered/failed. It includes no body, reporter, source, safety category, location or suppressed aggregate. Rollback emits nothing; state-version retry sends once.

## Factories, Seeds, Adversarial Tests, Rollback

- Factories: registered/checked-in/online/vendor/volunteer/speaker sources; all feedback target/visibility/anonymity/status/incentive states; event/version; report/case/recusal; projection; final report/version/follow-up/archive. Defaults satisfy constraints and relationship helpers name their scope.
- Seeds: environment-gated fictional stable-key scenarios only; public-safe summaries/test media. Never seed contact/attendance detail, private safety/harassment feedback, incentive evidence, or moderator identity. Repeat seed preserves counts/projection versions.
- Authorization/privacy: unauthenticated, outsider, registered-not-checked-in, checked-in, online attendee, target, organizer, former staff, conflicted/independent moderator, administrator, wrong event, restricted organization, blocked user. Assert prohibited data is absent from Blade, Livewire state, JSON/model serialization, cache, notification and archive.
- Adversarial: forged marker/target/source; fabricated attendance; concurrent/replayed/key-conflict submits; bomb accounts and account/IP bypass; organizer retaliation/report flood; doxxing phone/email/address; attack; undisclosed incentive; case recusal; withdrawal/removal aggregate decrement; archived registration; later source revocation.
- Runtime: feedback list/aggregate query counts remain flat one-to-twelve rows; threshold remains silent below five; populated SQLite plan is measured; EN/LT/RU parity/localized marker/receipt; migration rollback/reapply around old reviews; fresh/repeat seed; after-commit exactly-once and rollback-none notification checks.

Post-production rollback cannot safely resurrect removed private text, moderation state, projections or notification receipts; use forward repair. Do not widen ForumEventReview into a catch-all, expose low-count aggregates, use free-form JSON targets, let organizers inspect anonymous internal feedback, or fabricate ticket/vendor/volunteer/incident/certificate facts.

## Smallest Durable Recommendation

First ship a narrow additive feedback package: existing-FK event/organizer/venue/session/accessibility/safety targets; private-by-default feedback; server-derived registered/checked-in marker; independent moderation/report/case reuse; append-only history; actor+IP-aware limiter; five-contributor-suppressed versioned projection; after-commit deduplicated safe notifications. Keep trainer, speaker, ticket, vendor, volunteer, certificates, incidents, refunds, lost-property, final-report/follow-up and archive source links as typed deferred contracts until upstream authority exists.
