# Canonical Place Facts: Accessibility And Localization Discovery

Date: 2026-08-30  
Scope: read-only specialist discovery for the canonical emergency-veterinary Place slice  
Status: current defects confirmed; implementation and native linguistic review remain pending

## Evidence reviewed

- `AGENTS.md`; `docs/accessibility.md`; `docs/localization.md`
- PRD-PLACE-002/003, UI-A11Y-001..005, UI-RESPONSIVE-001..002, and I18N-001..005
- PLA-P05/P15 and the active PLA-CF section of `docs/implementation-plan.md`
- The Place canonical-facts work ledger and the applicable Places design specification
- Current Place presenter/catalog, Blade components/pages, SCSS, EN/LT/RU language files, PHP tests, and browser checks

No tracked file, schema, test, or production-code change was made. This report does not define schedule/DST algorithms or service-taxonomy internals.

## Confirmed current defects

### 1. Emergency eligibility and wording overstate canonical knowledge — high

`PlacePresenter::directory()` selects the emergency view, but `matches()` currently treats fixture/catalog values as sufficient: a matching species list plus `open`, `closing-soon`, `open-with-warning`, `on-call`, or `appointment-only` is considered open, while emergency eligibility is a boolean category-like flag. `PlaceCatalog::withCanonicalAuthority()` overlays a database Place onto the fixture record without replacing those fixture schedule, service, species, distance, or emergency facts.

The rendered copy then says that suitable open/on-call clinics matching the selected species are shown. This can state more than the canonical source supports and conflicts with PRD-PLACE-002/003 and PLA-P15.

Evidence: `app/Services/PlacePresenter.php:28-50,356-445`; `app/Services/PlaceCatalog.php:132-185`; `lang/en/place_directory.php` and locale counterparts under `summary.emergency_description` and `map.clinics_title`.

### 2. Appointment-only and uncertain hours are not semantically distinct — high

`appointment-only` receives the positive tone and is included in the same open-now matching/counting group as `open`, `on-call`, and warning states. The current UI has no explicit appointment-only qualifier that prevents a walk-in inference. It also lacks the active plan's separate `opening_soon`, `status_unknown`, and `stale_schedule` presentations.

Status badges do contain text, so the existing UI is not color-only. The defect is that the underlying text/tone grouping is materially ambiguous.

Evidence: `app/Services/PlacePresenter.php:279-284,377-384,604-624`; `docs/implementation-plan.md:190-207`.

### 3. Source, scope, and freshness are aggregate rather than fact-specific — high

The catalog derives one generic verification label and one generic freshness value from the Place record and `places.updated_at`. Cards show neither value; the hero and comparison show only aggregate verification/freshness. A user therefore cannot determine, at the call decision, whether the hours, phone, species capability, or emergency service fact is supported, what the verification scope covered, or whether that fact is stale.

Evidence: `app/Services/PlaceCatalog.php:170-184`; `resources/views/components/place-card.blade.php:45-117`; `resources/views/components/place-hero.blade.php:54-57`; `resources/views/components/place-comparison.blade.php`.

### 4. Unknown species can be presented as dog-and-cat support — high

`PlaceCatalog::defaultRecord()` substitutes dog and cat when the canonical Place has no species entries. The interface consequently cannot distinguish supported, unsupported/not listed, and unknown species states. That is unsafe for the emergency workflow and violates the unavailable/unknown-state requirement.

Evidence: `app/Services/PlaceCatalog.php:270-306`.

### 5. Missing service/species facts have no explicit unavailable state — medium

Current cards show positive values when present but do not render an explicit localized unavailable or unknown state for requested emergency service and selected species. Absence can therefore be read as omission rather than uncertainty.

Evidence: `resources/views/components/place-card.blade.php`; `resources/views/components/place-hero.blade.php`; PLA-P05/P15.

### 6. Phone actions are not prepared from one validated canonical boundary — medium/high

The presenter creates `tel:` by stripping every character except `+` and digits from any non-null phone. One submission path validates an 8–15 digit public phone, but the canonical Place creation action currently applies only a string/length rule. A malformed value can therefore become an unsafe or unusable call action.

Evidence: `app/Services/PlacePresenter.php:286-291`; `app/Actions/CreatePlace.php:203`; `app/Actions/SubmitPlaceSubmission.php:302-310`.

The safe UI contract is: a validated, normalized, public phone produces a prepared call action; an absent or invalid/non-public phone produces localized passive unavailable text. Blade must not normalize or validate it, and must never render an empty/disabled `tel:` control.

### 7. Call-first guidance is not proven unconditionally — high

The emergency description currently includes call-before-travel and no-guarantee text, which is a useful baseline. It is tied to the current emergency summary, however, and tests cover only the successful fixture-driven emergency result. There is no evidence that the guidance remains visible for empty results, absent phone, absent location, stale/unknown hours, temporarily closed, or appointment-only results in every locale.

Evidence: `tests/Feature/PlaceDirectoryTest.php:182-200`; `lang/*/place_directory.php` emergency summary.

### 8. Long localized state text can be clipped — medium

The shared status badge uses no-wrap plus ellipsis. Canonical Lithuanian and Russian labels such as stale-schedule and appointment-only are substantially longer than existing labels; truncating the only visible state text would undermine non-color communication. No Place browser assertion detects status-label clipping.

Evidence: `resources/scss/_content.scss:92-107`; `scripts/accessibility-browser-check.mjs:3913-4079`.

### 9. Existing accessibility evidence does not cover the canonical emergency surface — medium

Current Place browser checks cover the ordinary directory/detail at 1440 and 375 pixels. The broader page-identity matrix exercises multiple widths/locales, forced colors, and simulated zoom, but does not enter emergency mode or seed all canonical states. Neither suite asserts emergency safety copy, source/scope/freshness, unavailable phone/service/species, keyboard order, long state-label reflow, or no-JavaScript operation.

Evidence: `package.json` (`test:browser:places`); `scripts/run-browser-check.php`; `scripts/accessibility-browser-check.mjs:266-346,3913-4079`.

## Required presentation contract

### Status semantics

Use one server-prepared state code and one localized visible label per result. Text is authoritative; color and Lucide icons may reinforce it but never replace it. Decorative icons use `aria-hidden="true"`. Initial server-rendered status must not use a live region.

| Prepared state | Required visible meaning | Required qualifier |
| --- | --- | --- |
| `open_now` | Open now | None unless appointment-only also applies |
| `opening_soon` | Opening soon, including a locale-formatted time when known | Must not be styled or announced as already open |
| `closed` | Closed | Next opening may be shown only as a prepared, supported value |
| `status_unknown` | Hours/status unknown | Call first remains visible |
| `stale_schedule` | Schedule may be outdated | Visible stale wording beside the schedule, not tooltip-only |
| `temporarily_closed` | Temporarily closed | Must not use positive/open tone |
| `appointment_only` | Appointment only | Must explicitly say that walk-in availability is not implied |

Do not expose an accessibility name that contradicts visible text. Status changes caused by an actual user interaction may use a narrowly scoped `aria-live="polite"`; the existing map-selection live region is the appropriate pattern.

### Facts and uncertainty

At the point where a person decides whether to call or open details, render prepared values for:

- fact source, public source action when safe, and verification scope;
- observed/verified time and freshness (`current`, `stale`, or `unknown`);
- requested service state (`available`, `unavailable`, or `unknown`);
- selected species state (`supported`, `not listed/not supported`, or `unknown`);
- phone action or explicit phone-unavailable text.

Use a compact semantic definition list or equivalent labelled structure. Never communicate unavailable/unknown solely through a dash, blank space, muted color, icon, or disabled action. Do not link private/internal evidence. A public source URL must be prepared server-side, restricted to a safe public `http`/`https` destination, have a descriptive accessible name, and use `rel="noopener noreferrer"` when opened in a new tab.

### Safety guidance

The emergency page must render a persistent localized note before results, including for empty results and degraded/no-location operation:

> Call the clinic before you travel. PawCircle does not diagnose and cannot guarantee admission, wait time, clinician availability, species acceptance, or treatment.

This message is unconditional. A concise card-local reminder may supplement it but cannot be the only copy. Use a semantic note/aside and visible text; do not use `role="alert"` for static guidance.

### Passive Blade and actions

- Keep the page server rendered and operational with JavaScript disabled; no Livewire component is required for these facts.
- Blade receives complete state labels, tone/class maps, fact labels/values, safe URLs, and action availability. It performs no queries, normalization, permission checks, time calculations, translation-key construction, or fact inference.
- A result is an `article` with a programmatically associated heading. The results collection remains a semantic list or clearly labelled region.
- Call and details use native anchors. Filters use native labelled controls. No click-only container or hover-only disclosure may be introduced.
- The text-list equivalent remains available independently of the map.

### Layout and interaction

- All call, details, public-source, filter, and map-marker actions have a minimum 44 by 44 CSS-pixel target.
- Focus is visible in normal and forced-colors modes and is not obscured by sticky content.
- DOM/tab order follows visual order: safety note, filters, results, then each result's actions. Enter activates native links/buttons; Space activates native buttons.
- At 320 CSS pixels and at 200% zoom, content reflows without horizontal page overflow, loss, overlap, or two-dimensional scrolling. Long LT/RU state/source labels wrap rather than ellipsize.
- In forced colors, state text remains present, focus uses a system color, and selected/current state has a semantic attribute such as `aria-current` or `aria-pressed`, not only a background.
- Under `prefers-reduced-motion: reduce`, status/focus meaning does not depend on animation and nonessential transitions are effectively removed.

Existing token/mixin foundations already provide 44-pixel actions and forced-color focus for standard controls (`resources/scss/_tokens.scss`, `_controls.scss`, and `_place-directory.scss`). The canonical slice should reuse them and add coverage; this is a recommendation, not proof that the new surface passes.

## Localization contract

Place the new emergency-presentation keys in the existing Laravel language catalogs, preferably the feature-owned `lang/{en,lt,ru}/place_directory.php`. Do not create a parallel translator. Use complete stable keys rather than dynamic concatenation.

Minimum key families:

- `emergency.safety.call_first` and `emergency.safety.no_guarantees`
- `emergency.states.open_now`, `opening_soon`, `closed`, `status_unknown`, `stale_schedule`, `temporarily_closed`, `appointment_only`
- `emergency.qualifiers.appointment_only`
- `emergency.facts.source`, `scope`, `verified_at`, `observed_at`, `freshness.current`, `freshness.stale`, `freshness.unknown`
- `emergency.services.available`, `unavailable`, `unknown`
- `emergency.species.supported`, `not_supported`, `unknown`
- `emergency.actions.call`, `phone_unavailable`, `view_details`, `open_source`
- `emergency.results.empty_title` and `emergency.results.empty_body`

Use the same placeholder names in all locales, for example `:place`, `:phone`, `:time`, `:source`, and `:species`. Locale-aware time formatting must occur before Blade rendering.

Current automated inspection found equal flattened key counts (188 in each `place_directory.php`, 247 in each `places.php`) and no current key-set differences. Existing global localization tests compare placeholders, while `PlaceDirectoryLocalizationTest` also checks feature key parity. Those checks do not validate the new keys, rendering semantics, or linguistic quality until the slice is implemented.

### Draft terminology for review

The following is implementation guidance only. It has **not** received native-speaker linguistic review and must not be recorded as reviewed or verified.

| Meaning | English | Lithuanian draft | Russian draft |
| --- | --- | --- | --- |
| Open now | Open now | Dabar dirba | Открыто сейчас |
| Opening soon | Opening soon (:time) | Netrukus atsidarys (:time) | Скоро откроется (:time) |
| Closed | Closed | Uždaryta | Закрыто |
| Unknown | Hours unknown | Darbo laikas nežinomas | Часы работы неизвестны |
| Stale | Schedule may be outdated | Darbo laiko informacija gali būti pasenusi | Расписание может быть устаревшим |
| Temporarily closed | Temporarily closed | Laikinai uždaryta | Временно закрыто |
| Appointment only | Appointment only | Tik iš anksto susitarus | Только по предварительной записи |
| Service unavailable | Service unavailable | Paslauga neteikiama | Услуга недоступна |
| Service unknown | Service availability unknown | Nežinoma, ar paslauga teikiama | Доступность услуги неизвестна |
| Species unknown | Species support unknown | Nežinoma, kokias gyvūnų rūšis priima | Неизвестно, какие виды животных принимают |

Safety-copy draft:

- EN: `Call the clinic before you travel. PawCircle does not diagnose and cannot guarantee admission, wait time, clinician availability, species acceptance, or treatment.`
- LT: `Prieš vykdami paskambinkite klinikai. PawCircle nenustato diagnozių ir negarantuoja priėmimo, laukimo trukmės, specialisto prieinamumo, pasirinktos gyvūno rūšies priėmimo ar gydymo.`
- RU: `Позвоните в клинику перед поездкой. PawCircle не ставит диагнозы и не гарантирует приём, время ожидания, наличие специалиста, приём выбранного вида животного или лечение.`

## Exact verification assertions

### PHP/Pest

1. Flatten EN/LT/RU feature catalogs and assert identical key sets for every new key family.
2. Extract `:[A-Za-z_]+` placeholders for every value and assert identical placeholder sets across locales.
3. Render every state in every locale and assert one visible localized state label, the correct `lang` attribute, no raw `places.*`/`place_directory.*` key, and no positive/open semantic for closed, unknown, stale, temporarily closed, or appointment-only.
4. Assert the unconditional safety note on populated, empty, no-phone, no-location, stale, unknown, temporarily-closed, and appointment-only responses.
5. Assert appointment-only explicitly states the qualifier and never satisfies an `open_now` presentation assertion.
6. Assert requested service and selected species each render available/supported, unavailable/not-supported, and unknown states explicitly.
7. Assert each rendered fact uses only its prepared public source, scope, and freshness values; missing values render localized unknown text.
8. Assert a valid public phone renders one normalized `tel:+<8-15 digits>` action whose accessible label includes place and displayed phone; absent/invalid/non-public phone renders no `tel:` link and does render localized unavailable text.
9. Assert public source actions allow only prepared `http`/`https` URLs; unsafe/private source values produce no external link. New-tab links must contain both `noopener` and `noreferrer`.
10. Add an architecture assertion that the canonical Blade contains no `@php`, model/facade/service/action access, translation-key concatenation, phone normalization, schedule computation, or permission logic.

### Browser matrix

Seed deterministic cards for all seven states plus no-phone, unavailable-service, unsupported-species, unknown-species, empty-results, and no-location cases. Exercise at least:

- 320px EN; 375px RU; 768px LT;
- 1024px RU with forced colors active;
- a 1280px window at 200% effective zoom/reflow;
- 1440px EN and 1920px LT;
- reduced motion in each run, plus one JavaScript-disabled server-rendered run.

For each applicable case, assert exactly:

1. One `main`, one `h1`, valid heading order, no duplicate IDs, no unnamed controls/images, and the text-list equivalent available without the map.
2. The safety note precedes the results in DOM and visual order and contains localized call-first plus all no-guarantee concepts, including empty/no-phone/no-location cases.
3. Every card has a durable `data-opening-state`; its visible text matches that state; its status icon is decorative; and non-open states are not distinguishable only by color.
4. Each LT/RU status, source, scope, freshness, service, and species label satisfies `scrollWidth <= clientWidth` unless intentionally wrapping, has no ellipsis/text clipping, and does not overlap adjacent actions.
5. Every actionable call/details/source/filter/marker target has a computed width and height of at least 44px.
6. Repeated Tab traversal reaches controls in the expected order; focused elements have a nonzero visible outline or equivalent in normal and forced colors; Enter/Space behavior follows the native element.
7. Call links match `^tel:\\+[0-9]{8,15}$` and expose place plus phone in the accessible name. No-phone cards contain zero `tel:` links and one visible localized unavailable marker.
8. External source links resolve only to `http:`/`https:`, have a descriptive accessible name, and, when `target="_blank"`, include `noopener noreferrer`.
9. Fact source, scope, and freshness are visible next to the decision/action area; stale facts have explicit stale text. Service/species unavailable and unknown states are visible, not blank or icon-only.
10. Appointment-only is visible as appointment-only, is not exposed as `open_now`, and has no positive-only styling; temporarily closed, unknown, and stale receive equivalent non-open checks.
11. `document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1` at every viewport; 200% reflow retains all facts and actions without two-dimensional scrolling.
12. Forced-colors mode retains status text, visible focus, a perceivable card/action boundary, and a semantic selected/current attribute. Reduced-motion mode removes nonessential transition/animation without hiding information.
13. With JavaScript disabled, safety guidance, all prepared facts, call availability/unavailability, and details actions remain usable; no provider or map script is required.
14. Browser console/page errors are zero and the DOM contains no raw `places.*` or `place_directory.*` translation key.

## Disposition

The existing general Place page has useful semantic, focus, touch-target, reflow, forced-color, and reduced-motion foundations, but it is not sufficient evidence for PLA-CF. EVD-06 and EVD-07 should remain pending until the prepared canonical view model, localized passive Blade rendering, native-reviewed wording, feature tests, and the emergency-specific browser matrix above have all passed.
