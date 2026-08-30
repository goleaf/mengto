# EVENT-S05 — Weather Boundary Discovery

Date: 2026-08-30
Status: discovery only; no production, migration, test, translation, canonical
plan, or shared-document file was changed.

## Scope, authority, and current state

This report owns only provider/manual weather observations, their normalized
weather-plan boundary, stale-data handling, and the hard rule that an external
forecast cannot itself cancel, postpone, move, or suspend an event. It does
not implement P15 venue areas/routes, P19 payments, P20 tickets, P21 check-in,
P22 incidents, or any notification provider.

The immutable source is section 21 at
`docs/requirements/forum-source-prompt.md:61151-61255`. Its still-planned,
discovered weather/cancellation atoms are:

| Source | Atomic requirements | Boundary consequence |
| --- | --- | --- |
| `weather-001` | `event.weather.cancellation.0001-.0013` | Outdoor or temperature-sensitive events need a durable plan for heat, cold, rain, ice, storm, wind, air quality, shade, water, route alternatives, and named decision authority. |
| `weather-002` | `.0014-.0021` | Each displayed observation names exactly its source: official provider, organizer, venue, professional safety lead, or participant report. Forecasts are explicitly non-guarantees. |
| `weather-003` | `.0022-.0031` | Human decision records support continue, shorten, move indoors, reroute, remove animal participation, postpone, cancel, and temporary suspension. |
| `cancel-001` | `.0032-.0045` | Cancellation records category, internal/public rationale, authority, time, affected occurrences, and downstream ticket/venue/vendor/volunteer/notification state. |
| `cancel-002` | `.0046-.0054` | Postponement preserves the original time, acceptance/withdrawal, eligibility, refund/ticket, calendar, and private-link consequences. |
| `cancel-003` | `.0055-.0062` | Format changes are material and disclose new format/access/ticket/refund/pet effects. |
| `cancel-004` | `.0063-.0071` | A live interruption can preserve attendance and record evacuation, refunds/results/rescheduling, incident, and participant-support effects. |

P15 requires normalized weather plans, source observations, thresholds,
decision authority and weather actions, and requires state, communications,
calendar, tickets and private grants to change transactionally where required
(`docs/plans/portal-events-completion-master-plan.md:459-480`). P22 adds the
safety-plan weather linkage and scoped stop/suspend Actions
(`:623-645`); `event.safety.incident.0014` is its planned weather-process
atom. `docs/integrations.md` requires an explicitly configured client,
bounded requests, schema/size validation, redacted logs, fake and
stray-request prevention, and a temporary-disable/fallback mode.

There is no weather config, adapter, model, migration, Action, policy ability,
factory, seed, or test. `ForumEvent` only has generic event metadata plus
encrypted location/access data. `CancelForumEvent` and `RescheduleForumEvent`
currently authorize generic `update`, lock the event, alter the event state or
time, create a public update and write `ForumEventHistory`; they neither carry
weather evidence nor notify. `TransitionForumEventStatus` can put the entire
event and occurrences into `SafetySuspended`; an active `SafetyLead` or
`WelfareOfficer` may make that transition, while a generic manager may make
other transitions. These are useful primitives, not the required weather
workflow, and must not be relabelled as one.

## Required separation of evidence and authority

`WeatherObservation` is evidence. A provider response, threshold comparison,
or stale-data timer may produce at most a visible `review_required` signal. It
must never invoke `CancelForumEvent`, `RescheduleForumEvent`,
`TransitionForumEventStatus`, a queue job, notification, calendar update, or
private-grant revocation by itself. It cannot select the decision, target an
occurrence, or create a `ForumEventUpdate`.

`WeatherDecision` is an authenticated, authorized, validated human operation
that may reference observations and plan version but never treats them as a
guarantee. It records the actor and the authority snapshot that was checked at
decision time. The UI must say both the source and freshness, including
`Forecast; not a guarantee`, rather than suggesting that a provider made the
decision. Provider identifiers, credentials, request URLs, raw responses and
precise private locations never enter public event updates, history metadata,
notifications, or logs.

The first implementation must route weather-caused cancellation, postponement,
format/route change and temporary suspension through this specialized Action;
generic event actions may remain for non-weather reasons but cannot be used to
bypass a selected weather-plan authority. A decision that has no qualifying
human authority is rejected even when every provider threshold is exceeded.
Conversely, an authorized safety lead can take an immediate temporary,
least-scope suspension based on a documented manual observation even when the
provider is unavailable or stale.

## Provider configuration, contract, and DTOs

Add one explicit `services.weather` configuration entry, disabled by default:

```php
'weather' => [
    'enabled' => (bool) env('EVENT_WEATHER_ENABLED', false),
    'driver' => env('EVENT_WEATHER_DRIVER', 'null'), // allow-listed adapter name
    'base_url' => env('EVENT_WEATHER_BASE_URL'),
    'api_key' => env('EVENT_WEATHER_API_KEY'),
    'connect_timeout_seconds' => 3,
    'timeout_seconds' => 8,
    'retry_attempts' => 2,
    'max_response_bytes' => 131072,
    'stale_after_minutes' => 30,
],
```

Only a deployment-controlled allow-list maps `driver` to a
`WeatherObservationProvider` implementation. The adapter receives a typed
`WeatherObservationRequest` containing server-derived event/occurrence ID,
safe location reference, requested forecast window, and correlation ID. It
returns `WeatherFetchResult` with either a typed `NormalizedWeatherObservation`
or a typed unavailable/rate-limited/invalid response. It never returns a raw
array or accepts a browser URL, coordinates, headers, driver, timeout, or
credentials. A `NullWeatherObservationProvider` is the default and returns
`disabled`; it makes no HTTP request. The adapter is constructor-injected, not
resolved from the container in a model or Blade view.

For the initial GET-only adapter, use HTTPS fixed in configuration,
`acceptJson()`, `connectTimeout(3)`, total `timeout(8)`, and at most two
bounded retries for transport failures, `429`, and `5xx` responses with
backoff and rate-limit respect. Do not retry malformed/oversize bodies or
other `4xx`; no mutation client is in scope. Reject a body above 128 KiB
before decoding (using declared length when present and actual byte length in
all cases), require JSON, and validate the exact provider schema before
normalizing. Accepted values must be finite and physically bounded: temperature
and apparent temperature, wind/gust, precipitation probability/rate, weather
condition/severity, AQI/category, ice/storm flags, observed/forecast time,
provider update time, and provider observation ID. Unknown units, impossible
ranges, missing required timestamps, forecast windows outside the requested
range, non-HTTPS configured endpoints, redirects, or extra unbounded payloads
become an unavailable observation, never a guessed value.

Log only adapter name, stable request/correlation ID, response class/status,
byte count, duration and redacted error code. Do not log API keys, authorization
headers, raw bodies, full URLs/query strings, exact coordinates, private venue
details, participant reports, or private decision notes. Manual sources work
when the provider is disabled, rate-limited, malformed, or stale; this is a
fallback, not an attempt to manufacture provider data.

## Normalized durable state

Use event-scoped, append-only evidence records rather than `ForumEvent::metadata`
or a polymorphic generic report. All controlled values are enums and all
user-facing labels use parity-tested EN/LT/RU language files.

| Record | Required data | Invariants and exposure |
| --- | --- | --- |
| `forum_event_weather_plans` | event FK, nullable occurrence FK for an override, version number, applicability (`outdoor`, `temperature_sensitive`), threshold/mitigation fields, decision-authority role, status, created/reviewed/expired timestamps, owner/reviewer, lock version | A plan is versioned; accepted versions are immutable. The occurrence must belong to the event. Application validation prevents duplicate active event-default and occurrence-override plans portably on SQLite. Public projection exposes only safe mitigation/decision guidance, never private routes or operational notes. |
| `forum_event_weather_plan_conditions` | plan FK, condition enum (`heat`, `cold`, `rain`, `ice`, `storm`, `wind`, `air_quality`), comparator, normalized integer/scaled value or boolean severity, optional forecast lead window, mitigation/action guidance | Unique `(plan_id, condition)`. Use scaled integers, not floats. Heat/cold must define the unit and animal/human applicability; condition data is configuration, not an automatic transition rule. |
| `forum_event_weather_plan_mitigations` | plan FK, enum (`shade`, `water`, `route_alternative`, `indoor_option`, `animal_removal`), availability/status, safe public label, encrypted operational detail/reference | Route alternatives reference the P15 route authority when it exists; do not copy geometry or exact locations. |
| `forum_event_weather_observations` | plan/event/optional occurrence FK, `source_type`, optional provider key/upstream ID, `kind` (`current`/`forecast`), observed/forecast window, received-at, valid-until, normalized metrics, quality/status, source actor/reporter, checksum, redacted correlation ID | Source enum is exactly official provider, organizer observation, venue observation, professional safety lead observation, participant report. Provider source must have adapter key and no user-supplied actor; manual sources must have accountable actor. Participant reports start `unverified`; they may trigger review but cannot demonstrate that conditions are safe. Store no raw body; retain a redacted schema/version/checksum sufficient for audit. Provider uniqueness is `(provider_key, upstream_id)` where supplied; otherwise a bounded fingerprint plus observation time prevents duplicate ingestion. |
| `forum_event_weather_decisions` | event/plan/optional occurrence FK, decision enum, state (`draft`, `proposed`, `decided`, `superseded`, `executed`, `failed`), decision authority role and active team-membership snapshot, actor, reason category, encrypted internal detail, public explanation, observed-at decision time, plan-version/checksum, explicit selected observation IDs/checksum, idempotency key, lock version | A decision is immutable after `decided`; corrective decisions supersede rather than edit it. The decision source is `human`; observations are references only. A unique idempotency key binds event, actor, action and normalized payload; replay with a different payload is a validation conflict. |
| `forum_event_weather_decision_occurrences` | decision/occurrence FK, original start/end/format/location-access version, proposed/actual consequence, per-occurrence execution state | An all-event decision enumerates its exact affected occurrences under the same transaction. This preserves original date/time and prevents an implicit recurrence-wide rewrite. |
| `forum_event_weather_history` | weather subject type/ID, event ID, actor or system source, event type, public-safe reason code, old/new state, idempotency/correlation key, timestamp | Append-only and redacted. It complements, but does not replace, `ForumEventHistory`; no complete manual report or private route belongs here. |

`fresh` means a valid observation whose `valid_until` is strictly later than the
injected application clock. `stale` means that instant has passed, whether the
provider call succeeded or not. It remains readable and auditable, visibly
stale, but cannot satisfy a fresh-plan review rule or be silently refreshed in
place. Provider fetch failure yields a separate unavailable record/status,
never a fabricated zero-risk observation. A scheduled refresh is best effort:
it records fresh evidence or an unavailable outcome, is idempotent, and has no
event-state side effect.

## Authority and execution rules

Add narrow `ForumEventPolicy` abilities rather than overloading `update`:

| Ability | Permitted actor | Boundary |
| --- | --- | --- |
| `viewWeatherPlan` / `viewWeatherObservation` | event viewer for safe projection; active scoped staff for private operational content | Participant reports, private route/venue detail and internal decision rationale are not public. |
| `recordWeatherObservation` | designated active organizer/venue liaison/safety lead/welfare officer; participant only through the existing report intake, not as an official observation | A browser cannot select `official_provider`, another actor, or a provider key. Venue evidence needs an accountable event staff confirmer. |
| `proposeWeatherDecision` | active plan-designated role, scoped to the event/occurrence | It validates authority, active membership, organization restriction, plan status and affected occurrence scope. |
| `decideWeatherChange` | exactly the active plan-designated decision authority, or a platform administrator with an audited break-glass reason | Owner/creator/generic `update` is insufficient. A safety lead may only decide when the plan grants that authority. |
| `emergencySuspendWeatherScope` | active `SafetyLead` or `WelfareOfficer` | May immediately stop the smallest supported scope (session/category/pet participation/occurrence/event once P22 scopes exist). It records a manual/observed factual basis and needs later authorized review to resume/escalate. |
| `resumeAfterWeatherSuspension` | plan-designated authority after a fresh/manual reviewed assessment | Provider recovery or threshold clearance cannot resume automatically. |

When current aggregate limits make a smaller P22 scope unavailable, the Action
may use the existing whole-event `SafetySuspended` transition but must record
that limitation. It must never describe a whole-event suspension as a
session/pet-only stop. `continue` is also an auditable decision: it changes no
event status but records the authority, evidence freshness, mitigation, and
next review time.

For a material action, lock the parent event, affected occurrences, active
plan version and decision row in one short transaction; re-authorize on the
locked records; verify the decision has not been superseded; write immutable
weather history and compatible `ForumEventHistory`; then apply the exact
event/occurrence change. `cancel`, `postpone`, and format/location changes
must use a specialized atomic operation that captures all required
consequences, rather than call the current generic Actions without weather
context. Calendar projection, registration eligibility revalidation, private
location/access-grant rotation, and any future ticket/vendor/volunteer work
receive an explicit idempotent consequence row/outbox entry. An unavailable
future package is recorded `not_applicable`/`pending`, never claimed complete.

Create public updates and recipient notifications only after the transaction
commits, from an idempotent outbox keyed by decision, occurrence, recipient and
message version. Existing `ForumEventNotifier` can render a localized
recipient-specific notification, but neither it nor `ForumEventUpdate` alone
is sufficient to prove all recipients were handled. Failed delivery retries
without replaying the decision; an emergency suspension displays server-side
status even if queue delivery is unavailable. No external provider call occurs
inside the decision transaction.

## Factories, seed data, and verification matrix

Add factories for every durable weather record with coherent states:
accepted default/occurrence plans, each manual source, fresh/stale/invalid
provider observations, unverified participant reports, designated-authority
decisions, superseded/replayed decisions, and each affected-occurrence shape.
Factory defaults must remain valid SQLite records and must never make a live
HTTP call. Seeder data is environment-gated and idempotent; use a static local
fixture only, label it as a manual/demo observation, keep exact routes/private
notes out of public projections, and keep the production provider disabled.

Required tests include:

- Contract/HTTP: `Http::preventStrayRequests()` plus fakes for a valid response,
  connect/total timeout, network error, 429/5xx retry bound, non-retried 4xx,
  invalid content type/JSON/schema/unit/range/time window, redirect, oversized
  declared/actual body, disabled/null driver, and redacted correlation logs.
- Clock/staleness: freeze/travel time across `valid_until`; prove stale and
  unavailable observations visibly require review, cannot pass a freshness
  rule, do not self-refresh, and never alter event/occurrence status or send a
  notification. Prove a documented manual safety observation still permits
  immediate authorized suspension during outage.
- Normalization: all five source labels persist and render correctly; provider
  arrays/raw bodies are absent from models/resources/history/public updates;
  duplicate upstream delivery and idempotency replay are safe, while the same
  idempotency key with changed payload is rejected.
- Authorization/privacy: guest, participant, reporter, wrong event/team,
  removed/expired team member, organizer without designated authority, wrong
  organization and suspended organization cannot create official observations
  or decide. Verify active safety/welfare staff have only their emergency
  scope, private notes/routes/reporter identity do not leak, and no client can
  choose provider driver/base URL/credentials or another decision actor.
- Decision safety: threshold breach and provider success alone produce no
  status change; threshold clearance cannot resume; `continue`, all seven
  material decision choices, and emergency temporary suspension are audited;
  affected occurrences are explicit; concurrent decisions lock/reject or
  replay deterministically; a later plan version cannot rewrite the prior
  decision snapshot.
- Consequences: cancellation/postponement preserve original schedule and
  apply only selected occurrences; private access/calendar consequences are
  atomic or queued from the durable outbox; notification failure does not undo
  a recorded safety action or duplicate it. Add browser checks for source,
  freshness/non-guarantee text, keyboard-operable authority controls, error and
  offline states, and no private evidence in the public workspace.

## Rollback risks and smallest durable recommendation

The primary risk is a superficially convenient cron job that maps a provider
alert directly to `ForumEventStatus`; it violates source authority, creates
unreviewable cancellations, and can expose exact locations through provider
requests/logs. A second risk is extending the generic cancel/reschedule forms
with unstructured weather fields: that loses plan version, source/freshness,
affected-occurrence and authority evidence. A third is treating an old
forecast as safe or blocking emergency human intervention when the provider is
down. Migration rollback must therefore be additive; do not alter historical
event migrations or erase observations/history. Turning the configured driver
back to `null` stops future provider calls while preserving manual workflows
and audit records; it is not a reason to delete safety decisions.

The smallest durable implementation is a vertical slice, not a provider
toggle: explicit disabled-by-default adapter configuration; versioned weather
plan; normalized provider/manual observations with stale/unavailable states;
narrow policies; immutable human decision record; and one row-locked,
idempotent temporary-suspension Action with after-commit outbox. It can safely
ship with the provider disabled and manual observations available. The other
weather decisions and P15/P22 consequence integrations must be completed
before any weather/cancellation atom is marked implemented or verified.
