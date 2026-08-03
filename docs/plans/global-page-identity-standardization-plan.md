# Global Page Identity Standardization Plan

Plan date: 2026-08-03

Status: proposed; repository, schema, and authenticated browser baseline audited;
implementation has not started

This plan is intentionally not time-boxed. Work advances only when the current
package satisfies its acceptance, accessibility, data, and quality gates.

## 1. Problem

PawCircle does not currently have one dependable visual and semantic contract
for the introductory block at the top of a portal page. The same hierarchy —
context, page title, description, optional count, and actions — is rendered by
four different implementations:

1. the shared `x-page-header` component;
2. duplicated Tailwind markup inside individual Blade views;
3. the care-specific `.care-directory-header` pattern;
4. the forum-specific `.forum-header` pattern with a separate serif scale.

The result is visible drift in typeface, title size, line height, spacing,
alignment, borders, action placement, responsive behaviour, and accessible
structure. It also makes later maintenance page-specific when the content is
conceptually the same.

The named forum directory has an additional information-architecture problem:
the server already has a large localized category hierarchy, but the main
directory does not expose its subcategories in a bounded, understandable
navigation flow. The named meetup directory also needs a durable schema and
query compatibility gate because it previously failed when the local database
did not contain `forum_event_team_memberships`.

## 2. User Outcome

- Every eligible portal page begins with the same visual hierarchy:
  eyebrow/context, one `h1`, description, optional metadata, and optional
  actions.
- Typography, spacing, borders, responsive behaviour, focus treatment, and
  touch targets are identical wherever the same page-introduction pattern is
  used.
- Private-care context remains obvious without receiving a separate design
  system.
- Page actions stay visible, wrap predictably, and never require horizontal
  scrolling.
- The forum exposes categories and the selected category's subcategories as
  normal, keyboard-accessible navigation.
- `/meetups` remains renderable after a fresh installation and after a real
  incremental upgrade.
- The first implementation covers the twelve named routes, then continues
  through a complete portal inventory so the same drift cannot remain on less
  visible pages.

## 3. Audited Baseline

Authenticated browser inspection on 2026-08-03 produced this baseline:

| Route | Current header family | Observed title treatment | Planned result |
| --- | --- | --- | --- |
| `/pets` | `x-directory-page` -> `x-page-header` | shared sans, 30/36 px desktop | canonical reference |
| `/medical-records` | duplicated utility markup | shared sans, 36/40 px | migrate |
| `/care-journals` | `.care-directory-header` | shared sans, 36/40 px | migrate |
| `/meetups` | `.forum-header` | Georgia, about 54/53 px | migrate after stability gate |
| `/places` | `x-directory-page` -> `x-page-header` | shared sans, 30/36 px | retain and verify |
| `/lost-found` | duplicated utility markup | shared sans, 36/40 px | migrate |
| `/marketplace` | duplicated utility markup | shared sans, 36/40 px | migrate |
| `/experts` | duplicated utility markup | shared sans, 36/40 px | migrate |
| `/forum` | `.forum-header` | Georgia, about 54/53 px | migrate and restructure IA |
| `/groups` | `x-directory-page` -> `x-page-header` | shared sans, 30/36 px | retain and verify |
| `/neighbors` | `x-directory-page` -> `x-page-header` | shared sans, 30/36 px | retain and verify |
| `/discover` | `x-page-header` | shared sans, 30/36 px | retain and verify |

All twelve routes currently return an application page without horizontal page
overflow in the audited local environment. `/meetups` currently returns `200`
and the event query explicitly selects `owner_user_id`. The migration that
creates event versions and team memberships is now tracked on `main`, and the
table is present in the current SQLite schema. This is current-state evidence,
not proof that every incremental environment is repaired; the upgrade path
remains an explicit work package below.

Repository-wide search also found `.forum-header` on knowledge, forum editors,
topic detail, event and group workspaces, journals, mentorship, expert
sessions, administration, and community notes. Those screens belong to the
global inventory wave even when they are not part of the first twelve routes.

## 4. Scope

### 4.1 Required first wave

- `/pets`
- `/medical-records`
- `/care-journals`
- `/meetups`
- `/places`
- `/lost-found`
- `/marketplace`
- `/experts`
- `/forum`
- `/groups`
- `/neighbors`
- `/discover`

### 4.2 Required global continuation

Inventory every authenticated first-party GET page and classify its opening
region into one of these contracts:

1. **Directory or index:** use the canonical `x-page-header`.
2. **Create or edit workflow:** use the same page-identity typography and
   spacing, with form actions in a predictable action region.
3. **Resource detail or operational workspace:** use a deliberately documented
   detail/workspace hero that consumes the same tokens but may include entity
   state, ownership, or operational controls.
4. **Authentication:** retain `x-auth-page-header` as a documented exception.
5. **Full-screen modal, media viewer, or focused tool:** no page header when a
   semantic page introduction would be redundant.

Every exception must be recorded with its route, component, reason, and owner.
“It already looks different” is not a valid exception.

### 4.3 Explicit non-goals of the header package

- It does not redesign every card, table, map, form, or empty state in the same
  commit.
- It does not merge public and private authorization rules.
- It does not move domain queries into Blade.
- It does not introduce a second CSS framework, JavaScript UI framework,
  Filament, Flux, Volt, React, Vue, or Inertia.
- It does not render all 1,637 forum subcategories into every forum response.
- It does not edit an already-used historical migration to repair an upgrade.
- It does not make an unavailable page or action discoverable merely for
  visual consistency.

## 5. Canonical Shared Component Contract

Evolve `resources/views/components/page-header.blade.php`; do not create a
parallel general-purpose header component.

### 5.1 Semantic order

The component renders this stable structure:

1. `<header>` landmark with an optional stable `data-section` hook;
2. localized eyebrow or context label;
3. exactly one page-level `h1`;
4. localized description;
5. optional prepared metadata such as a result count or privacy status;
6. optional action slot containing server-authorized controls.

The component remains presentational. It receives strings, URLs, scalar state,
and prepared action data. It does not access models, policies, facades,
services, routes by guessed identifiers, or the database.

### 5.2 Proposed API

Keep the existing public API compatible during migration, then converge on:

- `eyebrow` — required localized text;
- `title` — required localized text;
- `description` — required localized text;
- `headingId` — optional stable ID when another region references the title;
- `meta` slot — optional count, privacy label, or concise status;
- `actions` slot — optional action controls;
- normal Blade attributes for `aria-*` and stable testing hooks.

The current `count` and single-action props remain as a temporary compatibility
layer. After all consumers have moved to slots, remove compatibility only in a
separate, test-backed cleanup commit.

### 5.3 Visual token contract

Use the existing `/pets` family as the starting reference and verify the final
values visually:

- one application sans-serif family; no directory-specific Georgia override;
- mobile-first title scale around 24 px, increasing to 30 px on wider screens;
- title line height around 1.2 and weight 600;
- eyebrow at the existing compact uppercase scale;
- description at the existing readable body scale with a maximum measure near
  70 characters;
- a single border, padding, content gap, and desktop alignment contract;
- actions that wrap below or beside the copy without clipping;
- minimum 44 px interactive targets;
- visible focus, forced-colors support, and reduced-motion compatibility;
- no hover-only meaning and no horizontal page overflow.

Tokens belong in the existing CSS-first Tailwind/theme and SCSS component
layer. Page views must not restate the title scale with one-off utility lists.

### 5.4 Content policy

- Eyebrow states the context, not a promotional slogan.
- Title names the current page in plain language.
- Description explains the page outcome in one or two short sentences.
- Private contexts may show the existing lock icon or privacy label in `meta`,
  but use the same typography and spacing.
- A header contains at most one primary action and two secondary actions.
  Additional controls move to the following page toolbar.
- Counts describe the current result set and remain semantically separate from
  the page title.

## 6. Data Flow and Reusable Presentation Shape

Controllers, presenters, and Livewire components prepare the header data before
rendering. A reusable presentation array or typed view-data object may use this
shape:

```php
[
    'eyebrow' => __('...'),
    'title' => __('...'),
    'description' => __('...'),
    'count' => $preparedCountLabel,
    'actions' => $authorizedActions,
]
```

This is a data-shape example, not permission to add translation or policy logic
to Blade. URLs and action visibility remain server-authoritative. Livewire
public state stays small and contains no secrets or large model graphs.

Where `x-directory-page` already composes `x-page-header`, retain that path.
Do not duplicate the header in the calling view.

## 7. Work Packages

Each package is independently reviewable. Do not begin a later package while a
blocking gate in the current one is unresolved.

### Package 0 — Requirements, inventory, and red contracts

1. Assign a stable product requirement ID for canonical page identity.
2. Record the twelve-route baseline and the global classification matrix.
3. Add a focused architecture/feature contract that finds every eligible page
   and detects duplicate general-purpose header families.
4. Add route-level assertions for one `h1`, the canonical header hook, escaped
   localized content, and authorized actions.
5. Capture browser screenshots and computed typography at the required
   breakpoints before changing CSS.
6. Record representative query counts so the component migration cannot hide a
   query regression.

Acceptance:

- every named route has an owner, current template/component, desired
  component, and test target;
- new contract tests fail for the known divergent implementations and pass for
  the current canonical pages;
- no production markup has changed yet.

### Package 1 — `/meetups` schema and query stability

1. Verify the tracked event lifecycle migration on a fresh empty SQLite
   database and on a copy representing the previous application state.
2. Verify that migration history and physical schema agree for
   `forum_event_team_memberships` and its required indexes/foreign keys.
3. If a deployed environment can have a migration marked as applied while the
   table is absent, add a new forward-only reconciliation migration or a safe
   diagnostic/repair command. Never edit the historical migration.
4. Keep event visibility in model scopes and retain all columns needed by the
   scope, policy, presenter, and relationships in explicit projections.
5. Add regression coverage for authenticated visibility, private/group/event
   membership paths, missing optional relationships, and pagination.
6. Verify `/meetups` before any visual refactor so a design change cannot mask
   a data failure.

Acceptance:

- a fresh migrate and seed renders `/meetups`;
- an incremental migration path renders `/meetups`;
- no missing-table or missing-selected-attribute exception occurs;
- visibility and authorization tests include negative cases;
- event directory pagination does not introduce N+1 queries.

### Package 2 — Shared component foundation

1. Extend `x-page-header` with stable heading ID, metadata, and action slots.
2. Centralize type, spacing, border, layout, focus, touch, and wrapping rules.
3. Preserve compatibility with `x-directory-page` and current callers.
4. Add component rendering tests for no action, count only, one action,
   multiple actions, long translations, and escaped content.
5. Add browser fixtures for narrow viewport, 200% zoom, and forced colors.

Acceptance:

- the component performs zero database queries;
- its DOM order and accessible name hierarchy are stable;
- long Russian, Lithuanian, and English strings do not overlap or overflow;
- actions remain reachable by keyboard and touch.

### Package 3 — Reference directories

Apply and verify the canonical contract on:

- `/pets`;
- `/places`;
- `/groups`;
- `/neighbors`;
- `/discover`.

Most of these already use `x-page-header` directly or through
`x-directory-page`. This package removes local overrides, normalizes content
and action placement, and establishes the golden browser snapshots.

Acceptance:

- all five pages share the same computed font family, size, line height,
  padding, and border behaviour at the same breakpoint;
- page-specific filters, maps, counts, and actions begin below or inside their
  documented slots without layout shifts.

### Package 4 — Private care directories

Apply the shared component on:

- `/medical-records`;
- `/care-journals`.

Preserve the “private care/family workspace” meaning as localized metadata.
Keep privacy explanations, pet scoping, alerts, and creation permissions
unchanged. Move `Create journal` into the canonical action region only when the
current user is authorized.

Acceptance:

- both pages use the same visual structure as `/pets`;
- privacy is still announced to sighted and assistive-technology users;
- no private record, pet, file, or family data becomes publicly discoverable;
- medical and journal query counts do not increase.

### Package 5 — Operational directories

Replace duplicated utility headers on:

- `/lost-found`;
- `/marketplace`;
- `/experts`.

Keep emergency/status communication below the page identity. Keep marketplace
money, listing state, and expert verification server-authoritative. Do not
blend page actions with card-level actions.

Acceptance:

- the three pages use canonical page identity and action slots;
- urgent lost-pet states retain stronger semantic status treatment without a
  separate page-title system;
- filters and actions wrap at 320 px without page overflow.

### Package 6 — Event directory presentation

After Package 1 is green, replace the `.forum-header` in the Livewire event
directory with `x-page-header`.

1. Keep filter values in typed, validated Livewire URL state.
2. Keep loading, offline, empty, error, and pagination states.
3. Keep event creation authorization and substantial form logic in the
   component/form object, not the view.
4. Move creation to the canonical action/toolbar hierarchy without making the
   entire creation form part of the header.

Acceptance:

- `/meetups` uses the canonical title system;
- filtering and pagination continue to work through direct Livewire action
  invocation tests and browser interaction;
- no event fields or authorization paths are lost.

### Package 7 — Forum page identity and category information architecture

The repository currently seeds and tests 44 root categories and 1,637 direct
subcategories. The main forum view receives category data but renders only root
entries in its current sidebar. The fix must make the hierarchy usable without
placing the entire taxonomy into one DOM.

1. Replace the forum directory's `.forum-header` with `x-page-header`.
2. Place category navigation immediately below the page header rather than in
   an ambiguous decorative sidebar.
3. Render all 44 root categories as ordinary, localized GET links in a
   responsive grid or disclosure navigator.
4. When a root is active, render only that root's direct subcategories in a
   second bounded region.
5. Add a validated `subcategory` input to the browse request.
6. Validate that the selected subcategory is a child of the selected root;
   reject or normalize mismatched pairs on the server.
7. Add focused `ForumCategoryTree` methods for root navigation and children of
   one active root. Cache by locale and selected root with documented TTL and
   invalidation; do not load all descendants by default.
8. Add an Eloquent topic scope/presenter path for the selected normalized
   category/subcategory. Preserve legacy category mapping until its documented
   migration is complete.
9. Preserve filter, sort, search, language, and pagination query parameters in
   category navigation.
10. Keep category preparation in the presenter/service. Blade only renders the
    prepared hierarchy.

Acceptance:

- the 44 roots are visible, reachable, and localized;
- selecting a root exposes exactly its direct children;
- selecting a child filters the topic list and retains an understandable
  active/breadcrumb state;
- invalid or cross-root child input cannot escape the selected hierarchy;
- the response does not render all 1,637 subcategories;
- query count remains bounded and has no category/topic N+1 path;
- navigation works with keyboard, touch, 200% zoom, and JavaScript disabled.

### Package 8 — Global page inventory and migration

Use route inventory plus Blade/Livewire searches to migrate every remaining
eligible first-party page. The known starting list includes:

- forum topic detail and editor;
- knowledge directory and guide editor;
- forum groups and group workspaces;
- forum events and event workspaces;
- forum journals and timelines;
- mentorship and expert sessions;
- community notes and forum administration;
- connections, pet friends, circle, notifications, walks, profiles, and
  settings;
- create/manage pet flows and other authenticated directories discovered by
  route inspection.

Do not blindly replace a detail/workspace hero. First classify it, then either
use `x-page-header` or a documented token-compatible detail/workspace variant.

Acceptance:

- every authenticated first-party GET route is present in the migration
  matrix;
- every eligible directory uses `x-page-header`;
- every exception is deliberate, documented, and covered by a structural
  test;
- no new `.forum-header`, `.care-directory-header`, or duplicated utility
  header is allowed by architecture tests.

### Package 9 — CSS and compatibility cleanup

1. Remove `.care-directory-header` only after its last valid consumer moves.
2. Split `.forum-header` usages that are true page identity from small internal
   section headings; replace internal headings with a distinct, semantically
   named section component.
3. Remove the oversized serif page-title rules after the last page-identity
   consumer moves.
4. Remove temporary `x-page-header` compatibility props after all callers use
   slots.
5. Delete duplicated inline header utility sequences.
6. Retain detail typography only where the global classification explicitly
   permits it.

Acceptance:

- source search finds one general-purpose portal page-header family;
- the CSS build has no orphaned page-header rules;
- no visual regression is hidden by stale selectors.

### Package 10 — Localization and documentation

1. Normalize header translation keys across `en`, `lt`, and `ru` without
   creating a second translation system.
2. Keep placeholders and plural forms identical across locales.
3. Update design-system documentation, frontend rules, component inventory,
   UI migration matrix, implementation plan, requirements, compliance matrix,
   testing guidance, and changelog.
4. Mark a requirement implemented and verified only after its actual gates
   pass.
5. Keep forum requirement/evidence generation and immutable-source checks in
   the same change whenever forum requirements or evidence are touched.

Acceptance:

- no new user-facing header text is hardcoded;
- all supported locales render without missing-key fallbacks;
- current documentation and implementation describe the same component
  contract.

### Package 11 — Verification, scoped publication, and follow-up audit

1. Run targeted tests after each package.
2. Run Pint and Larastan before the full suite.
3. Run the complete Pest suite sequentially.
4. Run fresh isolated migration and complete seed, then fixed-seeder
   idempotency.
5. Run Composer validation/security audit and NPM audit/production build.
6. Run route, config, and view cache smoke checks.
7. Run authenticated browser checks on all twelve named routes and the global
   representative matrix.
8. Review complete diff, secrets, documentation, requirements, and query
   delta.
9. Commit coherent packages on `main` with a temporary scoped Git index when
   the shared tree contains unrelated work.
10. Push only verified attributable commits to `origin/main`.
11. Re-run the route/header inventory after cleanup; any remaining unexplained
    variant reopens the plan.

## 8. Query Delta Contract

The header itself must add zero queries on every page.

| Area | Before | Required after |
| --- | --- | --- |
| Header component | presentation only | zero queries |
| Existing directory data | current measured baseline | no increase |
| Forum roots | current full-tree preparation may include all children | bounded root navigation query/cache |
| Active forum children | mixed into full hierarchy | one bounded prepared lookup for the selected root, cacheable by locale/root |
| Forum topics | current paginated presenter query | same or fewer queries, no N+1 |
| Meetups | paginated event query with eager loads/aggregates | same bounded pattern, no missing projection data |

Record observed counts in tests or profiling evidence before claiming an
improvement. An estimate is not a passed gate.

## 9. Blade Usage Contract

Eligible pages should reduce to prepared data plus the shared component:

```blade
<x-page-header
    :eyebrow="$header['eyebrow']"
    :title="$header['title']"
    :description="$header['description']"
>
    <x-slot:meta>
        {{ $header['count'] }}
    </x-slot:meta>

    <x-slot:actions>
        {{-- Render only server-authorized, prepared controls. --}}
    </x-slot:actions>
</x-page-header>
```

Blade must not calculate permissions, query categories, derive URLs from IDs,
or call models/services. Loops use prepared collections and meaningful empty
states.

## 10. Filament Integration

Filament is not installed and is outside this plan. No Filament Resource,
widget, panel, dependency, or styling layer will be introduced. If a future
current requirement adds Filament, it must consume the same documented design
tokens independently rather than becoming a prerequisite for portal pages.

## 11. Tests and Quality Gates

### 11.1 Focused automated coverage

- `PageHeaderComponentTest` for slots, escaping, semantic order, and variants;
- a portal page-header contract test covering the twelve named routes;
- medical record and care journal feature/authorization tests;
- pet, place, lost-and-found, marketplace, expert, group, neighbor, and
  discover feature tests;
- Livewire event directory tests for mount, validation, authorization,
  filtering, pagination, offline/loading-compatible markup, and repeated
  actions;
- event lifecycle migration/schema regression tests;
- forum directory tests for root category, subcategory, invalid hierarchy,
  preserved filters, locale, authorization, empty state, and pagination;
- forum category seed tests preserving 44 roots and 1,637 children;
- architecture tests preventing new duplicate header families and forbidden
  Blade logic.

### 11.2 Browser matrix

For every named route, test at 320, 375, 768, 1024, 1440, and 1920 px:

- HTTP/application success with no exception page;
- exactly one page `h1`;
- canonical header hook and computed typography;
- no horizontal page overflow;
- no overlap, clipping, or detached actions;
- complete keyboard route and visible focus;
- 44 px action targets;
- 200% zoom;
- reduced motion and forced colors;
- no browser console errors;
- `en`, `lt`, and `ru` long-string behaviour.

Additional browser flows:

- create journal action visibility and navigation;
- meetup search/filter/pagination and event opening;
- forum root selection, child selection, direct URL restoration, back/forward
  navigation, empty result, and invalid child handling.

### 11.3 Final repository gates

- `composer validate`;
- Composer security audit;
- dependency compatibility inspection;
- PHP syntax checks;
- Pint;
- Larastan;
- complete sequential Pest suite including architecture tests;
- fresh isolated migration and complete seed;
- fixed-seeder idempotency;
- NPM audit;
- production Vite build;
- route, config, and view cache smoke checks;
- final requirement, documentation, secret, and staged-diff review;
- `git diff --check`.

## 12. Stop Conditions

Stop the current package and report the blocker when any of these occurs:

- the event migration ledger and physical schema disagree and a safe forward
  repair has not been established;
- `/meetups` fails before the visual migration;
- a named page lacks an authoritative route, authorization rule, or product
  requirement needed for its action;
- the forum taxonomy no longer matches the canonical seeded hierarchy without
  a documented requirement change;
- category/subcategory filtering would expose a private topic or bypass a
  visibility scope;
- an implementation would require queries or authorization logic in Blade;
- an `en`, `lt`, or `ru` translation is incomplete;
- a query budget, accessibility check, browser flow, static analysis, build,
  migration, seed, or test gate fails;
- an attributable edit overlaps unrelated uncommitted user/agent work and
  cannot be isolated safely;
- the proposed component API requires a broad breaking change without a
  compatibility wave.

Do not hide or waive a stop condition to keep the visual migration moving.

## 13. Commit Strategy

Work only on `main`. Suggested coherent commits are:

1. tests/docs: establish page-identity and route inventory contracts;
2. fix: harden meetup incremental schema/query compatibility, if still needed;
3. feat: extend canonical page-header component and tokens;
4. refactor: migrate reference and private-care directories;
5. refactor: migrate operational and event directories;
6. feat: expose bounded forum category/subcategory navigation;
7. refactor: migrate remaining portal page identities;
8. chore: remove legacy header families and close documentation evidence.

Each commit includes its own relevant tests and documentation. In a dirty
shared worktree, use a temporary `GIT_INDEX_FILE`, inspect the complete staged
diff, and never commit or revert unrelated work.

## 14. Definition of Done

The plan is complete only when:

- all twelve named routes use the canonical page-identity contract;
- every authenticated GET route has been classified and every eligible route
  has been migrated;
- the portal has one general-purpose page-header family;
- `/meetups` passes fresh-install and incremental-upgrade schema/query tests;
- `/forum` visibly exposes all root categories and only the active root's
  subcategories, with valid server-side filtering;
- header rendering adds no queries and directory query budgets do not regress;
- authorization, privacy, localization, responsive behaviour, keyboard access,
  focus, touch, zoom, forced colors, and no-overflow checks pass;
- obsolete markup and CSS are removed;
- requirements, design documentation, migration matrix, compliance evidence,
  testing notes, and changelog match the implementation;
- every applicable quality gate was actually executed and observed green;
- only attributable commits were pushed to `origin/main`.
