# Canonical Place Schedules And DST Discovery

Date: 2026-08-30  
Scope: schedule/DST discovery for `PLA-CF-01` and the future `PLA-CF-02`/`PLA-CF-04` contracts. This is a read-only design recommendation, not implementation or verification evidence.

## Canonical requirements

- `PRD-PLACE-002` requires source, verification scope, and freshness on place details and forbids false official/live claims (`docs/product-requirements.md:94-100`).
- `PRD-PLACE-003` prioritizes open, species-capable clinics but never guarantees admission or treatment (`docs/product-requirements.md:99-100`).
- `DATA-INTEGRITY-003` requires an unambiguous instant plus relevant source timezone (`docs/non-functional-requirements.md:23-30`); `docs/data-model.md:52-60` repeats that invariant.
- `PLA-02-003/004` require timezone-aware weekly intervals, overnight support, exceptions, temporary closures, provenance, and verification expiry (`docs/plans/places-production-master-plan.md:207-218`).
- `PLA-05-004/009/010/011` require canonical schedule migration, explicit scope, fact-level freshness, and visible stale/expired states (`docs/plans/places-production-master-plan.md:358-381`).
- `PLA-15-002/005/009/012` require schedule/timezone/exception-derived state, point-of-action freshness, DST/overnight/holiday handling, and clock-controlled tests (`docs/plans/places-production-master-plan.md:703-730`).
- The newer delivery contract enumerates `open`, `closed`, `opening-soon`, `unknown`, `stale`, `temporarily-closed`, and `appointment-only`, with one injected reference instant and two DST zones (`docs/implementation-plan.md:148-178`).

## Confirmed current defects

These are observed implementation gaps, not speculative schema criticism.

1. **No place operating timezone or canonical schedule exists.** `places` has no timezone or schedule fields (`database/migrations/2026_08_03_140000_create_places_table.php:13-50`), and the later catalogue migration adds only category and contacts (`database/migrations/2026_08_03_140040_add_catalog_fields_to_places_table.php:13-22`). The only place-adjacent timezone is on optional `venues` (`database/migrations/2026_08_03_140010_create_venues_and_areas_tables.php:13-26`), whose documented purpose is event operations, not public place hours (`docs/data-model.md:385-395`). Places without venues therefore cannot own hours, and a venue timezone cannot safely stand in for place-business schedule authority.
2. **Open/closed results are static fixture assertions.** `PlaceCatalog::records()` hard-codes `open_state`, localized labels, and human prose such as a permanently open park (`app/Services/PlaceCatalog.php:370-395`), a closing-soon park (`app/Services/PlaceCatalog.php:452-476`), a 24-hour clinic and overnight on-call clinic (`app/Services/PlaceCatalog.php:770-865`), and an appointment-only shelter (`app/Services/PlaceCatalog.php:1180-1200`). No reference clock or timezone participates.
3. **Canonical database rows do not replace fixture schedule truth.** `withCanonicalAuthority()` overlays identity/contact/location/species/verification but leaves fixture `open_state`, `hours_summary`, and `special_hours` intact (`app/Services/PlaceCatalog.php:132-185`). A sparse row gets only `unknown`, which is safe, but also invented default coordinates/species outside this specialist scope (`app/Services/PlaceCatalog.php:265-340`).
4. **Seeding discards schedule facts.** `PlaceDemoSeeder` reads fixture verification time and persists place/contact/species facts, but no timezone, hours, exception, or closure (`database/seeders/PlaceDemoSeeder.php:33-95`). Static `open_state` survives only because the runtime rejoins the fixture record.
5. **Emergency/open-now admits states that are not confirmed open for an unscheduled arrival.** Emergency mode forces `open_now` (`app/Services/PlacePresenter.php:28-40`), while `matches()` treats `on-call` and `appointment-only` as open (`app/Services/PlacePresenter.php:375-384`). The directory summary does the same (`app/Services/PlacePresenter.php:604-609`). That conflicts with truthful emergency prioritization; a phone-confirmation or prior-appointment condition is not `open`.
6. **Temporary closures disappear instead of resolving explicitly.** The enum has `temporarily_closed` (`app/Enums/PlaceStatus.php:7-13`), but all public/account catalogue scopes call `active()` and require `status = active` (`app/Models/Place.php:278-323`). No code or test otherwise consumes `PlaceStatus::TemporarilyClosed`.
7. **Freshness is presentation prose, not a decision input.** Catalogue queries omit `verified_at` and `information_expires_at` (`app/Services/PlaceCatalog.php:200-230`) and overwrite fixture freshness with `places.updated_at` (`app/Services/PlaceCatalog.php:170-184`). The `recent` filter then searches localized prose for English words (`app/Services/PlacePresenter.php:409-413`). Expired hours can still be classified open.
8. **Hours presentation is category-shaped invented prose.** `PlaceContentCatalog::hours()` fabricates weekday/weekend rows based on category instead of rendering stored intervals (`app/Services/PlaceContentCatalog.php:73-97`). It cannot express real per-day exceptions or DST.
9. **Opening-state coverage is absent.** Current Place tests prove fixture filtering and a single static emergency result (`tests/Feature/PlaceDirectoryTest.php:84-104`, `tests/Feature/PlaceDirectoryTest.php:182-200`), but repository search found no Place tests for reference time, timezone, weekly/overnight resolution, exception precedence, DST gaps/folds, temporary closure, appointment-only exclusion, or stale hours. The authority test uses venue timezone only for event selection (`tests/Feature/Places/PlaceAuthorityFoundationTest.php:419-437`).

## Recommended schedule semantic contract

The schedule is owned by the **Place**, not by the viewer, user profile, PHP default timezone, or optional Venue. Rendering may use the viewer timezone as a secondary label, but state resolution always uses the schedule's IANA zone.

Only the following fields are necessary to state schedule semantics; the schema specialist may choose table names and provenance/history placement:

- one current schedule version per place: `timezone` (canonical IANA identifier), `coverage` (`complete` only for a resolvable public schedule), and schedule freshness boundary;
- weekly intervals: ISO weekday `1..7`, `opens_local`, `closes_local`, explicit `all_day`, and `access_mode` (`walk_in` or `appointment_only`);
- date exceptions: schedule-local `local_date`, mode `closed` or `special`, replacement intervals with the same time/access fields, and their own freshness boundary;
- temporary closures: normalized `starts_at`/`ends_at` instants, source IANA timezone retained, and freshness boundary;
- immutable/current version identity so a timezone or hours replacement cannot reinterpret old evidence silently.

`timezone` belongs to the schedule version. Changing it creates/replaces a version together with revalidated local intervals and exceptions; it never edits the zone in isolation. Accept identifiers present in `DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC)` only if the product deliberately supports legacy aliases; the safer default is canonical `ALL` identifiers plus `UTC`. Reject empty values, abbreviations (`EET`, `EST`), and fixed offsets (`+02:00`).

Intervals are local civil-time facts, not stored pseudo-UTC timestamps. They use minute precision `HH:MM`, `00:00` through `23:59`; `24:00` is rejected. Every resolved instant interval is half-open: `[opens_at, closes_at)`. Equal open/close is invalid; 24-hour service uses `all_day`, not `00:00-00:00`. If `closes_local < opens_local`, the interval is overnight and ends on the next local date. `all_day` spans one local midnight to the next, so it is 23 or 25 elapsed hours across DST where appropriate.

An exception replaces the complete civil date, including the carry-over portion of a prior overnight interval. `closed` yields no permitted interval on that local date. `special` supplies the only permitted intervals on that date. An overnight special interval may carry into the following date unless that following date has its own exception, which replaces that civil date in turn. This avoids a Monday `20:00-02:00` interval leaking into a Tuesday holiday closure.

`appointment_only` means the place is within a known arrival/service window but requires a pre-existing appointment. It is never equivalent to walk-in `open`, never satisfies `open_now`, and never ranks as a confirmed-open emergency clinic. Outside that interval the result is `closed`. The resolver does not claim that appointment inventory exists; external appointment scheduling remains post-MVP.

Warnings, `on-call`, and `call first` are not opening states. Warnings remain parallel facts. Phone confirmation/on-call is a contact or service access condition and must not silently become `open`. Emergency call-first guidance remains unconditional regardless of state.

### DST disambiguation

Resolve a local boundary against the timezone transition table, not by accepting Carbon/PHP parser normalization implicitly:

- unique local time: use its sole instant;
- nonexistent local time in a spring gap: clamp to the transition instant, the first valid local instant after the gap;
- ambiguous local time in a fall fold: use the earlier instant for an opening boundary and the later instant for a closing boundary;
- if resolved boundaries are equal/inverted, or cannot be resolved, the effective fact is invalid and the public result is `unknown` with a non-sensitive integrity diagnostic. Writes reject the contradiction before persistence.

This policy never opens before a requested nonexistent wall time, keeps a fall-fold interval open through both repeated clock readings, and is independent of PHP version-specific parser defaults.

Observed 2026 transitions used by the proposed tests:

- `Europe/Vilnius`: gap at `2026-03-29T01:00:00Z` (local `03:00..03:59` absent; first valid time `04:00 EEST`), fold at `2026-10-25T01:00:00Z` (local `03:00..03:59` repeated).
- `America/New_York`: gap at `2026-03-08T07:00:00Z` (local `02:00..02:59` absent; first valid time `03:00 EDT`), fold at `2026-11-01T06:00:00Z` (local `01:00..01:59` repeated).

## State precedence and freshness

Use a configurable opening-soon threshold with a canonical default of 60 minutes; compare instants, not wall-clock arithmetic. `fresh_until` is exclusive: a fact is stale when `reference >= fresh_until`.

Precedence at one injected UTC reference instant:

1. An active, fresh, bounded temporary-closure record (including the record supporting a `PlaceStatus::TemporarilyClosed` projection) => `temporarily_closed`.
2. A potentially applicable closure whose freshness has expired => `stale`; never fall through to `open`.
3. Resolve the local civil date and its exception. A fresh `closed` exception => `closed`; fresh `special` intervals replace weekly facts. A stale applicable exception => `stale`.
4. If no exception applies, use the complete weekly schedule. Expired effective schedule => `stale`.
5. Missing schedule, missing/invalid timezone, incomplete coverage, contradictory effective intervals, or a temporary-closed lifecycle value without a supporting valid closure => `unknown`.
6. Inside a fresh `appointment_only` interval => `appointment_only`.
7. Inside a fresh `walk_in` interval => `open`.
8. Otherwise, if the next fresh walk-in opening instant is within the inclusive threshold => `opening_soon`.
9. Otherwise => `closed`.

A fresh date exception can resolve its date even when recurring weekly hours are stale because it replaces them. A stale or invalid fact needed to decide the current instant cannot be hidden by another ordinary interval. Return structured `opens_at`, `closes_at`, `evaluated_at`, `timezone`, `freshness`, and reason codes alongside the enum; localized labels are presentation only. To satisfy older `PLA-15-002` wording without expanding the canonical enum, expose `closing_soon` as an annotation on `open` plus `closes_at`, not as a competing state.

## Resolution pseudocode

```text
resolve(place, referenceInstantUtc, openingSoon = 60 minutes): Result
    require referenceInstantUtc is an unambiguous instant

    schedule = currentSchedule(place)
    if schedule missing or schedule.timezone invalid:
        return UNKNOWN(reason = missing_or_invalid_schedule)

    zone = IanaZone(schedule.timezone)
    localDate = referenceInstantUtc in zone -> date

    closure = closureWhoseHalfOpenInstantRangeContains(referenceInstantUtc)
    if closure exists:
        if closure is stale or invalid: return STALE/UNKNOWN with reason
        return TEMPORARILY_CLOSED(until = closure.ends_at)
    if place lifecycle says temporarily_closed without valid active closure:
        return UNKNOWN(reason = unsupported_temporary_closure)

    effective = buildEffectiveIntervals(
        dates = localDate - 1 day through localDate + 1 day,
        schedule, zone
    )
    // For each civil date, its exception replaces weekly intervals and prior
    // overnight carry on that date. Local boundaries use the DST policy above.

    if an applicable exception/effective fact is stale:
        return STALE(reason = effective_fact_expired)
    if coverage incomplete or any needed interval is invalid/contradictory:
        return UNKNOWN(reason = incomplete_or_invalid_hours)

    current = interval where opens_at <= referenceInstantUtc < closes_at
    if current exists and current.access_mode == appointment_only:
        return APPOINTMENT_ONLY(closes_at = current.closes_at)
    if current exists:
        return OPEN(closes_at = current.closes_at,
                    closing_soon = closes_at - reference <= openingSoon)

    next = earliest fresh walk_in interval with opens_at > referenceInstantUtc
    if next exists and next.opens_at - referenceInstantUtc <= openingSoon:
        return OPENING_SOON(opens_at = next.opens_at)

    return CLOSED(opens_at = next?.opens_at)
```

`buildEffectiveIntervals` must inspect yesterday because an overnight interval may cover today, and tomorrow because the opening-soon window can cross midnight. Sort by resolved `opens_at`, then `closes_at`, then durable interval ID; no database/default iteration order may affect the outcome.

## Rejected inputs and contradictions

Reject at the Action/form-data boundary and enforce row-local checks in SQLite-portable schema where possible. Recheck the aggregate under the schedule/version lock because cross-row overlap is not safely expressible as a portable check constraint.

- non-IANA/empty timezone, timezone abbreviation, or fixed-offset timezone;
- weekday outside `1..7`, invalid date, invalid `HH:MM`, second precision, or `24:00`;
- equal endpoints without explicit `all_day`; time endpoints supplied with `all_day`;
- duplicate exact intervals, overlapping weekly intervals (including overnight carry into the next weekday), or overlapping exception intervals; adjacent intervals are valid and may be normalized only in the returned projection;
- more than one current exception for the same schedule/date, `closed` plus special intervals, or `special` without intervals;
- unbounded or zero/negative temporary closure, ambiguous local closure input without preserved resolution, or instant values without retained source timezone;
- special opening that conflicts with a known active temporary closure; replacement must explicitly retire/version the older fact rather than win by row order;
- partial weekly coverage presented as complete, expired facts presented as current, timezone changed without a schedule version replacement, or multiple current schedule versions;
- any runtime-corrupt interval whose DST-resolved instants invert/equal. Resolver returns `unknown`, records a diagnostic, and must not salvage selected sibling rows into a public `open` claim.

## Clock-controlled test matrix

Primary pure-domain coverage should live in a focused resolver test and pass an explicit immutable UTC instant. Feature wiring tests should use Laravel `travelTo()` per test, then restore time; do not use `Carbon::setTestNow()`. Expected values below are literal assertions, not recomputed with resolver logic.

| # | Zone / facts | Reference instant | Expected |
| --- | --- | --- | --- |
| 1 | Vilnius, Mon `09:00-17:00` walk-in | `2026-02-02T06:59:00Z` (08:59) | `opening_soon`, opens `07:00Z` |
| 2 | Same | `2026-02-02T07:00:00Z` | `open` |
| 3 | Same | `2026-02-02T15:00:00Z` | `closed` (half-open close boundary) |
| 4 | Vilnius, Fri `20:00-02:00` | `2026-02-06T22:30:00Z` (Sat 00:30) | `open`, attributed to Friday |
| 5 | Same | `2026-02-07T00:00:00Z` (Sat 02:00) | `closed` |
| 6 | Tuesday closed exception over Monday `20:00-02:00` | Tuesday 01:00 local | `closed`; prior-day carry suppressed |
| 7 | Fresh special opening on otherwise closed Sunday, `10:00-12:00` | `2026-02-08T08:00:00Z` | `open` |
| 8 | Fresh temporary closure overlapping a weekly/special opening | closure start, middle, then exact end | `temporarily_closed`, `temporarily_closed`, then underlying resolved state |
| 9 | Appointment-only Mon `09:00-17:00` | Mon 10:00 local, then 17:00 | `appointment_only`, then `closed`; never matches open-now |
| 10 | Missing schedule, invalid zone, or incomplete coverage (dataset) | any fixed instant | `unknown` with distinct reason code |
| 11 | Effective hours `fresh_until = 2026-02-02T08:00:00Z` | exact `08:00:00Z` | `stale` |
| 12 | Stale active closure over otherwise fresh open hours | closure middle | `stale`, not `open` |
| 13 | Opening in exactly 60 minutes / 60 minutes + 1 second | fixed instants | `opening_soon` / `closed` |
| 14 | Vilnius gap, Sun `03:30-05:00` | `2026-03-29T00:59:59Z`, then `01:00:00Z` | `opening_soon`, then `open`; gap opening clamps to `04:00 EEST` |
| 15 | Same | `2026-03-29T02:00:00Z` (`05:00 EEST`) | `closed` |
| 16 | Vilnius fold, Sun `03:30-04:30` | `2026-10-25T00:30:00Z`, `01:15:00Z`, `02:30:00Z` | `open`, `open`, `closed`; open uses earlier fold and close later fold |
| 17 | Vilnius `all_day` on gap/fold Sundays | instants on both sides of each transition | `open`; resolved day lasts 23/25 elapsed hours |
| 18 | New York gap, Sun `02:15-04:00` | `2026-03-08T06:59:59Z`, then `07:00:00Z` | `opening_soon`, then `open`; gap opening clamps to `03:00 EDT` |
| 19 | New York fold, Sun `01:15-02:15` | `2026-11-01T05:15:00Z`, `06:30:00Z`, `07:15:00Z` | `open`, `open`, `closed` |
| 20 | New York spring overnight, Sat `22:00-Sun 04:00` | `2026-03-08T07:30:00Z` | `open`; resolved range is `03:00Z-08:00Z` |
| 21 | Fresh closed/special exceptions on Vilnius and New York transition dates | fixed fold/gap instants | exception replaces weekly result with no parser-default dependence |
| 22 | DST-resolved equal/inverted interval fixture injected below write boundary | affected transition instant | `unknown` plus integrity reason; never `open` |
| 23 | Stable ordering with two same-time intervals and randomized input order | fixed instant | identical state/transition metadata in every order |
| 24 | Emergency directory wiring: open, opening-soon, appointment-only, stale, unknown, temporarily closed candidates | one frozen instant | only `open` is in confirmed-open tier; all others retain truthful distinct state and call-first copy |

Also add write-validation datasets for every rejected case above, a feature test proving `PlaceStatus::TemporarilyClosed` remains discoverable with its explicit state, a locale test asserting structured state is unchanged across `en`/`lt`/`ru`, and a query-count test proving schedule/exception/closure reads are eager-loaded and bounded. No test should assert PHP's implicit parsing of a nonexistent or ambiguous local time as the product contract.

## Principal decisions required

1. Accept the explicit DST policy (gap clamp; fold opening-earlier/closing-later) or record another deterministic policy before red tests are written.
2. Accept `appointment_only` as not open for `open_now` and emergency ranking; this intentionally corrects current fixture behavior.
3. Use the newer seven-state `PLA-CF-04` enum and represent “closing soon” as structured metadata, resolving the vocabulary mismatch with older `PLA-15-002/012`.
4. Keep temporary-closed Places discoverable and resolver-backed rather than filtering them out at `scopeActive()`.
