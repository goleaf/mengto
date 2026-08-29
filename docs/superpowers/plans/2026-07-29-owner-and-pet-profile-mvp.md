# Owner And Pet Profile MVP Implementation Plan

> Historical delivery record. Production requirements, security rules, testing gates, and runtime versions are governed by `docs/index.md` and its canonical documents. Unchecked boxes below are preserved prototype history, not current PawCircle backlog items.

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build separate, responsive, session-backed social profiles for Mia Carter, Scout, and Nori with independent URLs, audiences, follows, tabs, privacy, editing, sharing, blocking, and reporting.

**Architecture:** A dedicated profile presenter creates complete owner and pet page contracts. A small visibility service resolves audience-specific fields, while the existing prototype state and action endpoint persist temporary interactions. Blade pages remain one-component compositions assembled from `ui`, `layout`, `object`, and `feature` layers.

**Tech Stack:** Laravel 12, invokable controllers, Form Requests, session-backed prototype state, Blade anonymous components, SCSS, Lucide Blade icons, Vite, Playwright.

---

### Task 1: Profile Request And Canonical Routes

**Files:**
- Create: `app/Http/Requests/BrowseProfileRequest.php`
- Modify: `app/Http/Controllers/MemberProfilePreviewController.php`
- Modify: `app/Http/Controllers/PetProfilePreviewController.php`
- Modify: `routes/web.php`

- [ ] Add validation for `tab` values `overview`, `pets`, `posts`, `about`, `feed`, `photos`, `friends`, `care`, and `family`.
- [ ] Add validation for `view` values `owner`, `public`, `follower`, and `friend`.
- [ ] Add `/@mia-carter`, `/@mia-carter/scout`, and `/@mia-carter/nori` as grouped named routes.
- [ ] Convert `/profile/mia-carter` and `/pets/scout` into named compatibility redirects.
- [ ] Keep static routes before generated pet routes and constrain every dynamic segment.
- [ ] Run `php artisan route:list --path=@mia-carter` and verify all three canonical pages are present.

### Task 2: Audience Visibility Resolver

**Files:**
- Create: `app/Services/ProfileVisibility.php`

- [ ] Define the visibility options `public`, `members`, `followers`, `friends`, `owners`, and `hidden`.
- [ ] Implement `allows(string $visibility, string $audience): bool`.
- [ ] Implement `options(): array` for composer select fields.
- [ ] Keep this service free of session, HTTP, and Blade concerns.
- [ ] Run `php -l app/Services/ProfileVisibility.php`.

### Task 3: Profile Prototype State

**Files:**
- Modify: `app/Services/PrototypeState.php`

- [ ] Store pet overrides by slug so Scout and Nori never share editable values.
- [ ] Preserve compatibility with the existing Scout state.
- [ ] Add owner and per-pet privacy getters with safe defaults.
- [ ] Add owner and per-pet privacy update methods.
- [ ] Add a bounded report collection containing target, reason, description, and timestamp.
- [ ] Keep all state writes behind focused public methods.
- [ ] Run `php -l app/Services/PrototypeState.php`.

### Task 4: Dedicated Profile Presenter

**Files:**
- Create: `app/Services/ProfilePresenter.php`
- Modify: `app/Services/PreviewService.php`

- [ ] Define Mia, Scout, and Nori base identity data with stable image dimensions and alt text.
- [ ] Add unique handles and canonical route metadata.
- [ ] Build separate owner and pet action arrays for owner/public/follower/friend contexts.
- [ ] Build owner tabs and pet tabs with direct query URLs.
- [ ] Apply visibility to location, pets, posts, friends, activity, and care sections.
- [ ] Build profile completion, verification, language, interest, family, and privacy summaries.
- [ ] Expose owner, pet, owner page, pet page, pet list, and pet-moment methods.
- [ ] Delegate existing owner and Scout data calls from the large preview service to the profile presenter.
- [ ] Remove duplicated owner, pet, and profile-page arrays from the preview service.
- [ ] Run PHP syntax checks for both services.

### Task 5: Generic Profile UI Primitives

**Files:**
- Create: `resources/views/components/ui/tab-list.blade.php`
- Create: `resources/views/components/ui/action-list.blade.php`
- Create: `resources/views/components/ui/progress-meter.blade.php`
- Create: `resources/views/components/object/profile-hero.blade.php`
- Modify: `resources/views/components/object/profile-identity.blade.php`
- Modify: `resources/views/pet-social/neighbors/show.blade.php`
- Delete after migration: `resources/views/components/object/member-profile-hero.blade.php`
- Delete after migration: `resources/views/components/object/pet-profile-hero.blade.php`

- [ ] Make tabs real links with `aria-current`, optional icons, counts, and a stable mobile rail.
- [ ] Make action-list render links and POST actions through the existing action-control primitive.
- [ ] Make progress-meter expose readable text and native progress semantics.
- [ ] Generalize the profile hero for people, pets, and neighbor profiles.
- [ ] Include cover, avatar, handle, metadata, status, badges, statistics, and responsive actions.
- [ ] Replace all old member/pet hero usages before deleting specialized components.
- [ ] Verify object components depend only on `ui` and `object`.

### Task 6: Shared Profile Workflow Components

**Files:**
- Create: `resources/views/components/feature/profile-view-switcher.blade.php`
- Create: `resources/views/components/feature/profile-safety-actions.blade.php`
- Create: `resources/views/components/object/profile-badge-list.blade.php`
- Create: `resources/views/components/object/profile-manager-list.blade.php`

- [ ] Render audience previews as a compact segmented navigation control.
- [ ] Preserve the active tab when changing audience.
- [ ] Render block and report controls away from primary profile actions.
- [ ] Render verification labels with distinct icons and text.
- [ ] Render pet managers as human identities with explicit roles.
- [ ] Provide empty and privacy-restricted states through existing notice/empty-state primitives.

### Task 7: Owner Profile Composition

**Files:**
- Create: `resources/views/components/feature/owner-profile.blade.php`
- Modify: `resources/views/components/feature/profile-pet-list.blade.php`
- Replace: `resources/views/pet-social/profile/show.blade.php`

- [ ] Make the page view contain only `<x-feature.owner-profile :profile="$profile" />`.
- [ ] Render hero, audience preview, tabs, and the selected tab body.
- [ ] Overview: biography, pet cards, recent moments, profile completion.
- [ ] Pets: all audience-visible pets with canonical profile links.
- [ ] Posts: owner moments or an explicit privacy notice.
- [ ] About: languages, interests, public facts, badges, and privacy summary.
- [ ] Show edit/privacy controls only in owner mode.
- [ ] Show independent owner social actions in non-owner preview modes.

### Task 8: Pet Profile Composition

**Files:**
- Create: `resources/views/components/feature/pet-profile.blade.php`
- Replace: `resources/views/pet-social/pets/show.blade.php`
- Modify: `resources/views/components/object/owner-summary.blade.php`

- [ ] Make the page view contain only `<x-feature.pet-profile :profile="$profile" />`.
- [ ] Reuse the generic profile hero, audience preview, and tab list.
- [ ] Feed: pet-specific moments.
- [ ] About: story, temperament tags, facts, and compatibility.
- [ ] Photos: responsive gallery.
- [ ] Friends: pet-friend context and walk actions.
- [ ] Care: respect pet-specific visibility and render a privacy notice when hidden.
- [ ] Family: show owner/manager roles and explain that people perform every action.
- [ ] Use distinct follow and friend targets for Scout and Nori.

### Task 9: Composer Support For Profiles

**Files:**
- Modify: `app/Http/Controllers/ComposerController.php`
- Modify: `app/Services/ComposerCatalog.php`
- Modify: `app/Services/PreviewService.php`
- Modify: `resources/views/components/feature/composer-form.blade.php`
- Modify: `routes/web.php`

- [ ] Pass selected pet/target context into composer data.
- [ ] Make pet-profile editing work independently for Scout and Nori.
- [ ] Add owner privacy and pet privacy forms using visibility selects.
- [ ] Add a report form with reason and description.
- [ ] Support cancel-route parameters without hardcoded URLs.
- [ ] Constrain composer kinds to the expanded supported list.
- [ ] Keep every field rendered through `x-ui.form-field`.

### Task 10: Profile Actions And Validation

**Files:**
- Modify: `app/Http/Requests/PerformActionRequest.php`
- Modify: `app/Actions/PerformAction.php`

- [ ] Add `toggle-friend`, `toggle-pet-friend`, and `toggle-block`.
- [ ] Add `update-profile-privacy` and `update-pet-privacy`.
- [ ] Add `create-profile-report`.
- [ ] Require and constrain targets for every profile action.
- [ ] Validate every visibility field against the visibility service options.
- [ ] Store report text only after validation.
- [ ] Return users to the canonical owner or pet route with flash feedback.
- [ ] Keep critical actions separate from social toggle collections.

### Task 11: SCSS And Responsive Profile Layout

**Files:**
- Modify: `resources/scss/_profile.scss`
- Modify only if needed: `resources/scss/_controls.scss`

- [ ] Consolidate owner and pet hero styling under `.profile-*`.
- [ ] Add horizontally scrollable tabs without page-level overflow.
- [ ] Add stable cover, avatar, action, badge, progress, and manager dimensions.
- [ ] Keep profile cards at the project radius and avoid nested cards.
- [ ] Ensure 44-pixel mobile controls and thumb-reachable actions.
- [ ] Ensure cover focal areas remain visible at 320 through 1440 pixels.
- [ ] Reuse existing color tokens without creating a one-hue profile palette.

### Task 12: Product And Design Documentation

**Files:**
- Modify: `PRODUCT.md`
- Modify: `DESIGN.md`

- [ ] Document owner and pet profiles as separate social identities.
- [ ] Document independent handles, follow targets, and privacy.
- [ ] Document query-backed tabs and audience preview.
- [ ] Document the generic profile component contract and dependency direction.
- [ ] Record the publication-feed handoff: posts must carry an owner or pet identity.

### Task 13: Static Verification

**Files:**
- No test files created or modified.

- [ ] Run Pint on only the modified PHP files.
- [ ] Run PHP syntax checks on new PHP classes.
- [ ] Run `php artisan view:cache`.
- [ ] Run `npm run build`.
- [ ] Audit component definitions and references; expect zero missing components.
- [ ] Audit layer direction; expect zero violations.
- [ ] Search runtime code for `<x-pet-social` and expect zero matches.
- [ ] Search Blade for database access and expect zero matches.
- [ ] Run `git diff --check` and `git diff --cached --check`.
- [ ] Confirm `git status --short -- tests` is empty.

### Task 14: Browser Workflow Verification

**Files:**
- No persistent browser artifacts.

- [ ] Open owner and both pet canonical URLs.
- [ ] Verify owner/public/follower/friend audience switching.
- [ ] Verify every owner and pet tab.
- [ ] Verify owner follow does not activate pet follow.
- [ ] Verify Scout follow does not activate Nori follow.
- [ ] Verify friend, block, share, report, edit, and privacy flows.
- [ ] Verify legacy profile URLs redirect to canonical URLs.
- [ ] Verify hidden care/location/posts render privacy notices.
- [ ] Verify screenshots at 320, 375, 768, 1024, and 1440 pixels.
- [ ] Audit overflow, touch targets, images, headings, IDs, focus, CLS, reduced motion, console, and network errors.
- [ ] Remove generated screenshots and console logs.

### Task 15: Final Review

**Files:**
- Review all files changed by Tasks 1-14.

- [ ] Compare the implementation with the MVP boundary in the approved design.
- [ ] Verify no real security guarantee is implied by session-backed controls.
- [ ] Verify owner and pet identity data never merge implicitly.
- [ ] Verify every page remains a small component-only composition.
- [ ] Report verification truthfully, including the unchanged stale PHP-test baseline.
