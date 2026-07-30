# Pet Directory Implementation Plan

> Historical delivery record. Production requirements, security rules, testing gates, and runtime versions are governed by `docs/index.md` and its canonical documents.

> Execute with TDD and keep the current no-database prototype boundary.

**Goal:** Add a polished static `/pets` directory and connect it to the shared
PawCircle navigation.

**Architecture:** Extend the existing preview service, add one invokable
controller and named route, compose one directory view from a reusable card
component, and generalize the shared shell navigation state.

**Stack:** Laravel 13, Blade, Pest 4, Tailwind CSS 4, Vite.

---

## Task 1: Establish The Failing Directory Contract

**Files:**

- Create: `tests/Feature/PetDirectoryPreviewTest.php`

- [x] Generate the Pest feature test with Artisan.
- [x] Assert that `pet-social.pets.index` exists.
- [x] Assert the page contains directory header, filters, and collection
  sections.
- [x] Assert six pet identities and descriptive image text render.
- [x] Assert Scout links to `pet-social.pets.scout`.
- [x] Assert preview controls are disabled and no form renders.
- [x] Run the test and confirm RED because the route does not exist.

Command:

```bash
php artisan test --compact tests/Feature/PetDirectoryPreviewTest.php
```

## Task 2: Add The Static Data Boundary

**Files:**

- Modify: `app/Services/PreviewService.php`

- [x] Add `petDirectoryData()` with a documented array shape.
- [x] Add a private `directoryPets()` method containing six explicit records.
- [x] Keep all content preparation in PHP.
- [x] Keep the feature-owned data query count at zero.

## Task 3: Add The Route And Controller

**Files:**

- Create: `app/Http/Controllers/PetDirectoryPreviewController.php`
- Modify: `routes/web.php`

- [x] Generate the invokable controller through Artisan.
- [x] Method-inject `PreviewService`.
- [x] Return `pet-social.pets.index` with complete service data.
- [x] Register `/pets` before `/pets/scout` in the existing grouped routes.
- [x] Name the route `pet-social.pets.index`.

## Task 4: Build The Directory UI

**Files:**

- Create: `resources/views/components/pet-social/pet-directory-card.blade.php`
- Create: `resources/views/pet-social/pets/index.blade.php`

- [x] Build a reusable image-first card with explicit props.
- [x] Use stable 4:3 images and descriptive alt text.
- [x] Render traits with `@forelse` and an empty state.
- [x] Render the directory collection with `@forelse`.
- [x] Build a disabled search, filter, and sort toolbar without a form.
- [x] Use one, two, and three responsive grid columns.
- [x] Keep Scout as the only card with an active profile link.

## Task 5: Connect Shared Navigation

**Files:**

- Modify: `resources/views/components/pet-social/app-shell.blade.php`
- Modify: `resources/views/pet-social/pets/show.blade.php`

- [x] Add an `activeSection` prop with a Feed default.
- [x] Add active Feed and Pets links to desktop navigation.
- [x] Replace mobile Feed and Pets preview buttons with links.
- [x] Keep Meet and Tips disabled.
- [x] Apply `aria-current="page"` to the active destination.
- [x] Mark the profile and directory pages as part of Pets.

## Task 6: Reach Green

- [x] Run the focused directory test.
- [x] Run existing feed and profile feature tests.
- [x] Fix only contract regressions introduced by this slice.

Commands:

```bash
php artisan test --compact tests/Feature/PetDirectoryPreviewTest.php
php artisan test --compact tests/Feature/PreviewTest.php
php artisan test --compact tests/Feature/PetProfilePreviewTest.php
```

## Task 7: Verify The Complete Slice

- [x] Run Pint.
- [x] Run the full Pest suite.
- [x] Build production assets.
- [x] Inspect application routes.
- [x] Audit Blade and application files for prohibited query and interaction
  patterns.
- [x] Verify the live route returns HTTP 200.
- [x] Check 320, 768, 1024, and 1440 pixel layouts.
- [x] Check console output, broken images, overflow, focus, and navigation.
- [x] Capture final desktop and mobile screenshots.
- [x] Record that no commit is possible because the parent project is not a Git
  repository.

Commands:

```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact
npm run build
php artisan route:list --except-vendor
```

## Task 8: Close Independent Review

- [x] Request a read-only implementation and screenshot review.
- [x] Keep mobile navigation available throughout long pages.
- [x] Add content clearance for the fixed mobile navigation.
- [x] Strengthen DOM assertions for card count, headings, disabled controls,
  and active destinations.
- [x] Enable `RefreshDatabase` for Feature tests.
- [x] Add lazy, asynchronous card image loading.
- [x] Re-run focused tests, Pint, the full suite, the production build, static
  audits, and browser checks after review fixes.
