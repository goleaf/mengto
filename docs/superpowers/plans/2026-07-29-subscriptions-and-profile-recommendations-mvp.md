# PawCircle Subscriptions And Profile Recommendations MVP Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a session-backed relationship center with independent subscriptions, private requests, settings, explainable recommendations, and Following-feed integration.

**Architecture:** Add a focused immutable catalog and presenter beside the existing feed/profile services. Store exact-target relationship state in `PawCirclePrototypeState`, mutate it only through the validated shared Action, and keep all Blade views display-only.

**Tech Stack:** Laravel Blade, Form Requests, PHP session state, named routes, shared Blade components, SCSS, Vite, Lucide Blade icons, Playwright.

---

### Task 1: Define The Browsing Contract

**Files:**
- Create: `app/Http/Requests/BrowseConnectionsRequest.php`

- [ ] Allow only the documented `tab`, `type`, and `sort` values.
- [ ] Keep all parameters nullable so the presenter owns defaults.
- [ ] Reject arrays and unknown values through Laravel validation.

### Task 2: Build The Immutable Connection Catalog

**Files:**
- Create: `app/Services/PawCircleConnectionCatalog.php`

- [ ] Define stable namespaced targets for owners, pets, organizations,
  specialists, groups, and topics.
- [ ] Include public-safe identity, route, image, labels, follower count, and
  private/verified flags.
- [ ] Define followers, incoming requests, initial subscriptions, and grouped
  recommendations with explicit reasons.
- [ ] Provide `find()` and target allow-list methods without session reads.

### Task 3: Extend Prototype Relationship State

**Files:**
- Modify: `app/Services/PawCirclePrototypeState.php`

- [ ] Add exact-target subscriptions seeded from catalog-compatible keys.
- [ ] Add request status, notification level, favorite, muted, removed follower,
  dismissed recommendation, and undo state.
- [ ] Add focused getters and mutation methods for each state transition.
- [ ] Keep existing generic toggles backward compatible.

### Task 4: Present Display-Ready Relationship Data

**Files:**
- Create: `app/Services/PawCircleConnectionPresenter.php`

- [ ] Build summary counts and tab definitions.
- [ ] Filter and sort following/follower/request/recommendation records.
- [ ] Decorate each row with its exact current action state.
- [ ] Exclude blocked and dismissed targets from recommendation output.
- [ ] Expose target validation and compact profile action helpers.

### Task 5: Validate And Execute Relationship Actions

**Files:**
- Modify: `app/Http/Requests/PerformPawCircleActionRequest.php`
- Modify: `app/Actions/PerformPawCircleAction.php`

- [ ] Add allow-listed actions for follow, request, favorite, mute, notification
  level, recommendation dismissal/undo, and follower removal.
- [ ] Validate target key, notification level, and return tab.
- [ ] Resolve targets through the presenter before every mutation.
- [ ] Return named routes and accessible feedback for each result.

### Task 6: Add Controller And Grouped Routes

**Files:**
- Create: `app/Http/Controllers/ConnectionCenterPreviewController.php`
- Modify: `routes/web.php`

- [ ] Add `GET /circle/connections` under the existing `web` middleware and
  `pet-social.` name prefix.
- [ ] Inject validated URL state into the presenter.
- [ ] Render one connection-center view with no controller business logic.

### Task 7: Build Shared Relationship Objects

**Files:**
- Create: `resources/views/components/object/connection-identity.blade.php`
- Create: `resources/views/components/object/connection-state.blade.php`
- Create: `resources/views/components/object/recommendation-reason.blade.php`
- Create: `resources/views/components/object/connection-card.blade.php`

- [ ] Keep avatar, verification, type, location, counts, and state compact.
- [ ] Use existing `ui` atoms and icon components.
- [ ] Render private-profile and verification meaning with text.

### Task 8: Build Shared Relationship Features

**Files:**
- Create: `resources/views/components/feature/connection-toolbar.blade.php`
- Create: `resources/views/components/feature/connection-list.blade.php`
- Create: `resources/views/components/feature/recommendation-grid.blade.php`
- Create: `resources/views/components/feature/connection-dashboard.blade.php`

- [ ] Render tabs, summary, type filter, sorting, empty states, and actions.
- [ ] Use POST forms with CSRF for every state change.
- [ ] Keep recommendation dismissal undo visible after mutation.
- [ ] Avoid nested cards and duplicated markup.

### Task 9: Assemble The Thin Page

**Files:**
- Create: `resources/views/pet-social/connections/index.blade.php`
- Modify: `resources/views/pet-social/circle/index.blade.php`

- [ ] Compose the page from the shared dashboard component.
- [ ] Point the Circle page header action to connection management.
- [ ] Keep page title, active navigation state, and headings semantic.

### Task 10: Integrate Profile Follow Actions

**Files:**
- Modify: `app/Services/PawCircleProfilePresenter.php`
- Modify: relevant profile and directory presenters only where an exact target
  is already available.

- [ ] Replace ambiguous generic follow keys with namespaced targets.
- [ ] Link a followed profile to its relationship settings.
- [ ] Keep owner and pet subscription states independent.

### Task 11: Integrate The Following Feed

**Files:**
- Modify: `app/Services/PawCircleFeedPresenter.php`

- [ ] Filter Following mode by exact subscribed author or represented target.
- [ ] Exclude muted and blocked targets.
- [ ] Preserve chronological sorting and post de-duplication.
- [ ] Keep the main recommendation feed unchanged.

### Task 12: Add Responsive SCSS

**Files:**
- Create: `resources/scss/_connections.scss`
- Modify: `resources/scss/app.scss`

- [ ] Add stable list rows, recommendation grids, filters, settings menus, and
  state badges.
- [ ] Preserve 44 px touch targets and prevent horizontal overflow.
- [ ] Use the existing multi-color token palette and maximum 8 px card radius.
- [ ] Respect reduced motion and visible keyboard focus.

### Task 13: Update Product Documentation

**Files:**
- Modify: `PRODUCT.md`
- Modify: `DESIGN.md`

- [ ] Add the Point 3 MVP route, product rules, component boundaries, and
  production deferrals.
- [ ] Keep the subscription/friendship distinction explicit.

### Task 14: Run Static And HTTP Verification

**Files:**
- Verify only; do not create test files.

- [ ] Run Pint on changed PHP files.
- [ ] Run `php artisan view:cache`.
- [ ] Run `npm run build`.
- [ ] Check all tabs, filter combinations, and invalid values over HTTP.
- [ ] Run `git diff --check` and forbidden-pattern scans.

### Task 15: Run Browser Workflow Verification

**Files:**
- Verify only; remove generated browser artifacts afterward.

- [ ] Check 320, 375, 768, 1024, and 1440 px layouts.
- [ ] Exercise follow/unfollow and private request/cancel.
- [ ] Exercise favorite, mute, notification level, dismiss/undo, follower
  removal, and block.
- [ ] Verify the Following feed reflects exact subscriptions.
- [ ] Check broken images, overflow, duplicate IDs, target sizes, and console
  messages.
