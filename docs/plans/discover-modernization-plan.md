# Discover Modernization Audit And Delivery Plan

## Scope And Decision

This work replaces the existing authenticated `/discover` page in place. It
does not add a second shell, a public landing page, another search engine, or a
parallel event/group/place/expert/pet domain. The canonical decision is to make
Discover a small explainable recommendation hub that hands every resource to
its authoritative first-party module.

Status: implementation and repository-wide release verification complete on
2026-08-03; attributable publication is recorded in the delivery checklist.

## Baseline Audit

### Previous execution path

`DiscoverPreviewController` called `PreviewService::discoverData()`, filtered
the returned PHP array through `DirectoryFilter`, and rendered
`discover/index.blade.php` with three discover-specific result components.

### Previous user-visible behavior

- The search summary was hard-coded to "calm weekend walks" in Richmond.
- Exactly four demo cards represented Scout, Ari, one meetup, and one group.
- Local pulse numbers, trending topics, and a weekend promotion were presented
  without a current database source or recommendation explanation.
- The result set mixed member, pet, event, and group concepts without a page
  purpose or a path to other important portal modules.

### Functional defects

- Search accepted arbitrary filter state but did not search canonical models.
- Invalid filters silently behaved as all results.
- No result-count, empty, hidden, privacy, or unavailable state was based on
  current records.
- There was no way to tune or reset recommendations.
- People, proximity, activity, and popularity claims could not be explained by
  authoritative source data.
- Places and experts, both mature canonical directories, were absent.

### Security and privacy defects

- The static presenter bypassed current model visibility scopes.
- Account blocks, actor blocks, recommendation settings, archive state, and
  private/unlisted visibility were not part of the selection boundary.
- There was no explicit guarantee that private venue or exact address fields
  could not enter a card or notification-style preview.

### Architecture defects

- Discovery was coupled to the large compatibility `PreviewService`.
- The three page-specific result components duplicated existing page, action,
  status, linked-media, and empty-state primitives.
- The page acted as an isolated demonstration instead of a portal aggregate.
- The route had generic shell coverage but almost no behavior-specific tests.

### UX and accessibility defects

- The page did not answer what Discover was for or how it differed from global
  search and the feed.
- Static pulse/trending panels competed with the primary action.
- Recommendation cards had no "why" explanation or relevance controls.
- Filter state and semantic invalid-state behavior were not testable.

## Target User Jobs

1. Scan a balanced set of current public options outside the feed.
2. Narrow recommendations by a simple phrase or one domain.
3. Understand why each resource is present.
4. Open the authoritative event, group, place, specialist, pet, member, or post page.
5. Hide an irrelevant item or category and reverse that decision.
6. Trust that blocked identities and private locations are absent.

## Canonical Boundaries

### Included

- upcoming public events;
- public or request-to-join communities;
- publicly discoverable places;
- published specialists;
- public discoverable pet profiles outside the viewer household;
- active verified member actors outside the viewer account;
- current posts visible through the canonical publication audience scope;
- category/query URL state;
- item/category preference controls;
- named deep links and localized reasons.

### Deliberately excluded

- organization recommendations until a non-authority public directory exists;
- exact-distance claims without consented, current location context;
- sponsored ranking, AI ranking, opaque scoring, and inferred sensitive data;
- advanced filters and pagination already owned by module directories.

## Requirement Mapping

| Requirement | Implementation | Evidence | Status |
| --- | --- | --- | --- |
| `PRD-SOCIAL-001` privacy-safe portal discovery | seven category projections in `DiscoveryCatalog`, `members.show`, canonical `content.show` | `DiscoverExperienceTest` | verified |
| Validated query/category state | `BrowseDiscoveryRequest`, `DiscoveryCategory` | search/filter test | verified |
| Explainable recommendation | translated card reason | page/browser tests | verified |
| Account and actor blocks | `SocialBlockService::blockedActorIdsFor()` | block bypass test | verified |
| Recommendation opt-out | `SocialActorSetting`, `DiscoveryPreference` | privacy/preference tests | verified |
| Private location minimization | explicit selects/projections | exact-location assertions | verified |
| Constant query growth | bounded per-category queries | query-count test | verified |
| EN/LT/RU and long text | discovery translation files | locale test and 320px browser run | verified |
| Semantic responsive UI | canonical Blade components and SCSS | DOM/browser audit | verified |
| Canonical deep links | named routes and `x-linked-media` | linked-media test | verified |

## Delivery Phases

### Phase 1: discovery and baseline

- Read repository instructions, portal registries, design inventory, routes,
  models, scopes, factories, seeders, tests, and compatibility presenters.
- Preserve unrelated dirty worktree changes.
- Capture route, test, and current data baseline.

Status: complete.

### Phase 2: page purpose and information architecture

- Define page identifier, audience, entry/exit paths, categories, states,
  primary action, and anti-goals.
- Select existing canonical module destinations.
- Document why members use canonical actors and why organizations are not
  guessed into the page.

Status: complete.

### Phase 3: server-side catalogue

- Add controlled category and preference-scope enums.
- Add `BrowseDiscoveryRequest` and strict URL validation.
- Implement a bounded `DiscoveryCatalog` using explicit selects and existing
  visibility scopes.
- Apply archive, visibility, block, and recommendation boundaries before
  presentation.
- Format dates, counts, labels, reasons, and canonical destinations server-side.

Status: complete.

### Phase 4: preference lifecycle

- Add reversible indexed preference schema and model factory.
- Add owner policy, validated request, idempotent Action, throttled route, and
  server-confirmed feedback.
- Implement item/category hide and complete reset.

Status: complete.

### Phase 5: UI replacement

- Replace the old page, query panel, result list, result card, local pulse,
  trending panel, and weekend promotion.
- Reuse canonical page header, search, status, media, action, notice, and empty
  state primitives.
- Add four discovery components only for the new aggregate semantics.
- Add responsive SCSS without arbitrary dynamic classes or decorative gradients.

Status: complete.

### Phase 6: security, accessibility, localization, and performance

- Verify private/unlisted and blocked records are absent.
- Verify exact private locations are never selected or rendered.
- Add EN/LT/RU translation parity and locale-aware formatting.
- Verify one h1/main, alt text, named controls, no duplicate IDs, forced colors,
  reduced motion, 44px actions, and no 320/375/1440 overflow.
- Verify constant query count as all catalogues grow.

Status: complete.

### Phase 7: repository synchronization and release

- Update portal page map, module registry, route matrix, workflow registry,
  component inventory, migration matrix, compliance evidence, implementation
  plan, and changelog.
- Run scoped formatting, static analysis, targeted tests, architecture tests,
  fresh migration/seed, production build, browser checks, and full serial tests.
- Inspect the attributable diff, commit only discovery work, and push main.

Status: complete. The clean attributable discovery slice is release-verified
and prepared for publication to `origin/main`.

## Acceptance Checklist

- [x] Purpose is understandable without instructional marketing copy.
- [x] Recommendations come from current first-party database records.
- [x] Seven canonical discovery directions are available.
- [x] Member results use active verified user actors and a policy-scoped dynamic destination.
- [x] Post results reuse `ContentPublication::visibleTo()` and the canonical content route.
- [x] All filters are allow-listed URL state.
- [x] Every recommendation has a factual translated reason.
- [x] Every card deep-links to an authoritative named route.
- [x] Private and unlisted resources are excluded.
- [x] Exact private location fields are not selected or rendered.
- [x] Account-level and actor-level blocks are enforced.
- [x] `is_recommendable=false` is enforced.
- [x] Item/category hide and reset are idempotent and owner-scoped.
- [x] Empty and hidden-category states are explicit.
- [x] No Blade query or business logic was introduced.
- [x] Query growth is constant and result collections are bounded.
- [x] EN/LT/RU render without raw keys.
- [x] 320px Lithuanian text has no horizontal overflow.
- [x] Desktop/mobile media loads and has alt text.
- [x] Browser console is clean.
- [x] Final serial repository suite passes: 2,657 tests and 84,589 assertions.
- [x] Attributable commit is published to `origin/main`.

## Final Verification Record

- Discovery feature: 12 tests, 121 assertions.
- Linked-media discovery contract: 1 test, 5 assertions.
- Integrated social/content/portal/architecture/seeding slice: 1,832 tests,
  35,859 assertions.
- Final serial repository suite: 2,657 tests, 84,589 assertions.
- Fresh and repeat seed: 130 migrations, 215 tables, five users retained.
- Query projection: 12 queries, 16 results, seven non-empty sections in the
  current seeded viewer context; growth remains constant by feature test.
- Full PHPStan, full Pint, Composer audit, npm audit, Vite production
  build, and 1440/375/320px Chrome checks passed.

## Rollback And Data Safety

The migration is additive and reversible. Removing the discovery preference
table loses only user relevance suppressions, not blocks or domain records.
The GET route can return to a prior controller implementation independently,
but the preferred forward fix is to correct `DiscoveryCatalog`; no public
resource or private location is copied into the preference table.

## Verification Commands

```bash
php artisan test --compact tests/Feature/DiscoverExperienceTest.php
php artisan test --compact tests/Feature/LinkedMediaNavigationContractTest.php --filter='discover result media'
vendor/bin/pint --test <discovery PHP paths>
vendor/bin/phpstan analyse --no-progress
npm run build
php artisan serve --host=127.0.0.1 --port=8026
BROWSER_BASE_URL=http://127.0.0.1:8026 npm run test:browser:discover
php scripts/verify-fresh-database.php
php artisan test --compact
```
