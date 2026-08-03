# Portal Discovery

## Purpose

`discover.index` is the authenticated portal's recommendation hub. It helps an
active member move from a broad need to a small, explainable set of public
resources and then deep-links to the authoritative module. It is not global
search, a second directory system, a social popularity chart, or a replacement
for event, group, place, expert, or pet catalogues.

The stable page identifier is `portal.discovery.index`. The page owner is the
authenticated member. The required context is the member's locale, timezone,
block graph, managed social actors, and discovery preferences. The primary
action is opening one recommended resource. Secondary actions are narrowing
the view, opening a full module directory, hiding one recommendation, hiding a
category, and resetting hidden choices.

## Information Architecture

The page has four ordered regions:

1. Canonical `x-page-header` with the page purpose and bounded result count.
2. Five need-based directions linking to events, communities, places,
   specialists, and pets.
3. A validated search and category toolbar.
4. Grouped recommendation sections using one canonical card and one explicit
   recommendation reason per result.

The `all` view returns no more than three items per category. A selected
category returns no more than twelve. Full catalogues remain responsible for
pagination and advanced filters.

People are intentionally absent because the repository has no canonical,
policy-scoped public member-profile route. Organizations are intentionally
absent because their current directory is an authority and membership surface,
not a public recommendation directory. Neither omission may be filled with a
guessed route or a static card.

## Eligibility And Privacy

Recommendations are selected by `DiscoveryCatalog` from existing first-party
models and scopes:

| Category | Source | Required boundary | Destination |
| --- | --- | --- | --- |
| Events | `ForumEvent` | public, current, discoverable state, not archived | `meetups.show` |
| Communities | `ForumGroup` | active, public/request-to-join, discoverable to viewer | `forum.groups.show` |
| Places | `Place` | `publiclyDiscoverable()` | `places.show` |
| Specialists | `ExpertProfile` | published and recommendable | `experts.show` |
| Pets | `PetProfile` | public, active, discoverable, outside viewer household | `pets.profile` |

Both account-level and actor-level blocks are applied before a result is
prepared. Existing social actors with `is_recommendable=false` are excluded.
Legacy public records without a social actor remain eligible until actor
backfill, but owner-level account blocks still exclude them. This avoids a
silent loss of valid public records while retaining all known safety choices.

Private, unlisted, organization-only, group-only, invitation-only, archived,
or otherwise unavailable records never enter the result projection. Event
exact locations, place exact addresses, online secrets, attendee data, medical
data, and private profile fields are neither selected nor serialized. Detail
routes reauthorize the resource independently.

## Recommendation Reasons

Every card contains a translated factual reason based only on source data,
such as an upcoming public event in a broad region, a public community linked
to a region, a public place with current regional information, a published
specialist with verified qualifications, or a discoverable pet profile outside
the viewer's household. There is no hidden score, AI decision, popularity
claim, false proximity, paid boost, or inferred sensitive characteristic.

## Routes And Mutation Boundary

| Method | Route | Purpose | Authorization |
| --- | --- | --- | --- |
| GET | `discover.index` | Browse validated recommendations | active verified portal member |
| POST | `discover.preferences.store` | Hide an item/category or reset choices | active member plus `DiscoveryPreferencePolicy` |

`BrowseDiscoveryRequest` allow-lists `q` and `category`. The query is a bounded
80-character string and category is `DiscoveryCategory`. The preference
request allow-lists action, scope, category, target-key shape, reason, and
return state. The mutation is throttled and idempotent.

## Preference Model

`DiscoveryPreference` records one user-owned item or category suppression.
The unique key is `(user_id, scope, category, target_key)`. Category
preferences use `*` as the internal target. The policy permits only active
members to create/reset and only the owner to delete one preference. Reset
deletes only the authenticated member's rows.

These preferences are relevance controls, not permanent marketing consent,
safety blocks, or a replacement for the canonical social block system.

## Page States

- Initial: bounded grouped recommendations.
- Filtered: one validated category and/or query.
- Empty: no matching public recommendation, with a route back to the complete
  discovery view.
- Hidden category: explicit notice and reset action.
- Personalized: hidden-count summary and reset action.
- Validation failure: standard localized Laravel error and safe redirect.
- Unauthorized/inactive: denied by the portal boundary before catalogue work.

The page contains no speculative loading state because it is server rendered.
Preference actions report server-confirmed feedback after redirect.

## UI Contract

The canonical components are:

- `x-discovery-category-nav`
- `x-discovery-toolbar`
- `x-discovery-section`
- `x-discovery-result-card`
- existing `x-page-header`, `x-search-field`, `x-status-badge`,
  `x-linked-media`, `x-responsive-image`, `x-action-control`, `x-notice`, and
  `x-empty-state`

Cards keep media and title on the same canonical destination. Status is text
plus semantic tone. The recommendation reason is not color-only. The layout is
one column on mobile, two columns from 640px, and three from 1280px. Direction
cards become five columns from 1024px. Forced colors retain selected and reason
boundaries; all actionable mobile targets are at least 44px.

## Localization And Performance

All page, category, action, reason, state, media, and feedback text lives in
`lang/{en,lt,ru}/discovery.php`. Dates and counts use `LocaleFormatter`; event
times retain the event timezone. Browser verification includes the longer
Lithuanian layout at 320px and restores the seeded account locale afterward.

The all-category view performs a constant number of bounded queries: user
preferences, block boundaries, and at most one result query per category.
Adding catalogue rows does not add queries. No result relationship is lazily
loaded in Blade, and no unbounded collection is exposed.

## Verification

Primary executable evidence:

- `tests/Feature/DiscoverExperienceTest.php`
- the discovery case in `tests/Feature/LinkedMediaNavigationContractTest.php`
- `scripts/discovery-browser-check.mjs`
- `php artisan test --compact tests/Feature/DiscoverExperienceTest.php`
- `npm run build`
- `BROWSER_BASE_URL=http://127.0.0.1:8026 npm run test:browser:discover`

The implementation verifies the event, group, place, expert, and pet slice of
`PRD-SOCIAL-001`. Owner and post recommendations remain in the canonical
neighbor/feed surfaces until policy-scoped recommendation projections and a
stable public member destination exist. The complete full-suite and release-
gate result belongs in the current delivery record, not in this stable
architecture document.
