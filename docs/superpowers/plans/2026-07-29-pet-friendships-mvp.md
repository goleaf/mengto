# Pet Friendships MVP Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a responsive, interactive pet-friendship center with two-sided requests, established friendships, recommendations, and walk-planning entry points.

**Architecture:** An immutable catalog supplies pet and compatibility data, a dedicated session service owns canonical pair state, and a presenter shapes exact view data for small Blade components. One thin controller handles validated URL state, while existing POST action infrastructure performs all mutations.

**Tech Stack:** Laravel 12, Blade anonymous components, PHP session-backed prototype state, SCSS, Lucide Blade icons, Vite, Playwright.

---

### Task 1: Browsing Contract

**Files:**
- Create: `app/Http/Requests/BrowsePetFriendsRequest.php`
- Create: `app/Http/Controllers/PetFriendCenterPreviewController.php`
- Modify: `routes/web.php`

- [ ] Validate `pet`, `tab`, `intent`, `sort`, and `q` through a Form Request.
- [ ] Add the named grouped route `pet-social.pet-friends.index` at `/circle/pet-friends`.
- [ ] Keep the controller invokable and delegate all presentation to the presenter.

### Task 2: Catalog And Canonical State

**Files:**
- Create: `app/Services/PetFriendCatalog.php`
- Create: `app/Services/PetFriendState.php`

- [ ] Define allow-listed owned pets, candidate pets, owner attribution, compatibility signals, cautions, and safe locations.
- [ ] Represent every pair with one sorted canonical key.
- [ ] Seed accepted, incoming, and outgoing examples for both Scout and Nori.
- [ ] Implement send, cancel, accept, decline, pause, restore, remove, dismiss, undo, and block-safe state transitions.
- [ ] Keep the feature isolated under its own versioned session key.

### Task 3: Validated Friendship Actions

**Files:**
- Modify: `app/Http/Requests/PerformActionRequest.php`
- Modify: `app/Actions/PerformAction.php`

- [ ] Add explicit allow-listed actions for every friendship transition.
- [ ] Validate source and target pet identifiers, relationship intent, short message, meeting context, and return state.
- [ ] Reject self-friendship, duplicate active relationships, stale requests, and actions that do not match current state.
- [ ] Return to the same pet, tab, filter, and sort after every action.

### Task 4: Presentation Layer

**Files:**
- Create: `app/Services/PetFriendPresenter.php`

- [ ] Build summary metrics and source-pet tabs.
- [ ] Shape Friends, Requests, Find friends, and Walks datasets.
- [ ] Filter recommendations by query and intent.
- [ ] Sort by compatibility, recent activity, or name.
- [ ] Explain shared signals and cautions without presenting a compatibility guarantee.
- [ ] Build exact primary and secondary actions for each relationship state.

### Task 5: Shared Blade Components

**Files:**
- Create: `resources/views/components/object/compatibility-summary.blade.php`
- Create: `resources/views/components/object/pet-friend-card.blade.php`
- Create: `resources/views/components/feature/pet-friend-request-form.blade.php`
- Create: `resources/views/components/feature/pet-friend-toolbar.blade.php`
- Create: `resources/views/components/feature/pet-friend-list.blade.php`
- Create: `resources/views/components/feature/pet-friend-dashboard.blade.php`
- Create: `resources/views/pet-social/pet-friends/index.blade.php`

- [ ] Reuse generic identity, badge, action, tab, summary, and empty-state components.
- [ ] Keep the page template declarative and free of business logic.
- [ ] Use native links, forms, selects, textareas, buttons, and details menus for keyboard access.
- [ ] Keep owner attribution visible on every pet action.

### Task 6: Responsive SCSS

**Files:**
- Create: `resources/scss/_pet-friends.scss`
- Modify: `resources/scss/app.scss`

- [ ] Style semantic classes without any product prefix.
- [ ] Use one-column cards on mobile and a two-column recommendation grid at desktop width.
- [ ] Preserve 44px controls, 8px maximum card radii, stable image dimensions, wrapping text, and overflow-safe menus.
- [ ] Keep compatibility cautions visually distinct without relying on color alone.

### Task 7: Profile And Circle Integration

**Files:**
- Modify: `app/Services/ProfilePresenter.php`
- Modify: `resources/views/components/feature/pet-profile.blade.php`
- Modify: `resources/views/pet-social/circle/index.blade.php`
- Modify: `PRODUCT.md`
- Modify: `DESIGN.md`

- [ ] Replace the old generic pet-friend toggle with a link into the dedicated workflow.
- [ ] Add Manage pet friends to an owned pet's Friends tab.
- [ ] Add a pet-friend entry point beside general connection management.
- [ ] Document the distinction between following and two-sided pet friendship.

### Task 8: Verification

**Files:**
- No test files.

- [ ] Run Pint and PHP syntax checks on changed PHP files.
- [ ] Run `php artisan view:cache`.
- [ ] Run `npm run build`.
- [ ] Confirm source, compiled views, built CSS, and rendered DOM contain no `pc-`.
- [ ] Smoke all pet-friend tabs, pets, filters, profiles, messages, walks, and invalid query states.
- [ ] Use Playwright to verify send, cancel, accept, decline, pause, restore, remove, dismiss, undo, and walk navigation.
- [ ] Verify 320, 375, 768, 1024, and 1440 pixel layouts, 44px controls, no overflow, no duplicate IDs, no broken images, and no console errors.
