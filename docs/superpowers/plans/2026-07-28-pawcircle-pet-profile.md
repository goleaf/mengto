# PawCircle Pet Profile Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a polished static Scout profile at `/pets/scout` and connect it to the existing PawCircle feed.

**Architecture:** Two invokable controllers receive complete view-data arrays from `App\Services\PawCirclePreviewService`. A page-layout-agnostic Blade shell wraps both the existing three-column feed and the new responsive profile, while focused anonymous components render profile identity, facts, gallery, and owner content.

**Tech Stack:** Laravel 13, PHP 8.3, Blade, Tailwind CSS 4, Pest 4, Vite 8.

**Repository note:** This directory has no `.git` metadata. Commit steps are replaced with explicit no-Git checkpoints; no repository initialization is in scope.

---

## File Map

- Create `app/Services/PawCirclePreviewService.php`: own all static PawCircle preview content.
- Modify `app/Http/Controllers/PetSocialPreviewController.php`: delegate home data preparation to the service.
- Create `app/Http/Controllers/PetProfilePreviewController.php`: return Scout's profile view.
- Modify `routes/web.php`: register the fixed, named Scout route in the existing group.
- Modify `resources/views/components/pet-social/app-shell.blade.php`: accept a generic page slot.
- Modify `resources/views/pet-social/index.blade.php`: own the existing feed grid inside the generic shell.
- Create `resources/views/pet-social/pets/show.blade.php`: compose the profile page.
- Create `resources/views/components/pet-social/pet-profile-hero.blade.php`: render Scout's identity.
- Create `resources/views/components/pet-social/pet-facts.blade.php`: render care and compatibility lists.
- Create `resources/views/components/pet-social/pet-gallery.blade.php`: render stable responsive images and an empty state.
- Create `resources/views/components/pet-social/owner-summary.blade.php`: render Mia's compact profile.
- Modify `resources/views/components/pet-social/profile-card.blade.php`: link Scout from the home page.
- Create `tests/Feature/PetProfilePreviewTest.php`: cover the route, static contract, and home-page connection.

### Task 1: Profile Route Contract

**Files:**

- Create: `tests/Feature/PetProfilePreviewTest.php`

- [x] **Step 1: Write the failing route test**

```php
<?php

use Illuminate\Support\Facades\Route;

test('the Scout profile renders as a static preview page', function () {
    expect(Route::has('pet-social.pets.scout'))->toBeTrue();

    $response = $this->get(route('pet-social.pets.scout'));

    $response
        ->assertSuccessful()
        ->assertSee('Scout')
        ->assertSee('data-section="pet-profile"', false)
        ->assertDontSee('<form', false);
});
```

- [x] **Step 2: Verify RED**

Run:

```bash
php artisan test --compact tests/Feature/PetProfilePreviewTest.php
```

Expected: FAIL because `pet-social.pets.scout` is not registered.

- [x] **Step 3: Record the no-Git checkpoint**

Run:

```bash
test -d .git && git status --short || printf 'NO_GIT_REPOSITORY\n'
```

Expected: `NO_GIT_REPOSITORY`.

### Task 2: Shared Preview Data And Minimal Profile

**Files:**

- Create: `app/Services/PawCirclePreviewService.php`
- Modify: `app/Http/Controllers/PetSocialPreviewController.php`
- Create: `app/Http/Controllers/PetProfilePreviewController.php`
- Modify: `routes/web.php`
- Modify: `resources/views/components/pet-social/app-shell.blade.php`
- Modify: `resources/views/pet-social/index.blade.php`
- Create: `resources/views/pet-social/pets/show.blade.php`
- Test: `tests/Feature/PetProfilePreviewTest.php`
- Test: `tests/Feature/PetSocialPreviewTest.php`

- [x] **Step 1: Add the shared data service**

Create a final service with these public contracts:

```php
<?php

namespace App\Services;

final class PawCirclePreviewService
{
    /**
     * @return array{
     *     owner: array{name: string, location: string, avatar: string, summary: string},
     *     pets: array<int, array{name: string, type: string, breed: string, age: string, status: string, profile_route: string|null}>,
     *     posts: array<int, array{author: string, pet: string, time: string, body: string, image: string, tags: array<int, string>, stats: array{paws: string, replies: string}}>,
     *     meetups: array<int, array{title: string, place: string, time: string, attendees: string}>,
     *     groups: array<int, array{name: string, members: string, topic: string}>,
     *     tips: array<int, array{title: string, description: string}>
     * }
     */
    public function homePageData(): array
    {
        return [
            'owner' => $this->owner(),
            'pets' => $this->pets(),
            'posts' => $this->posts(),
            'meetups' => $this->meetups(),
            'groups' => $this->groups(),
            'tips' => $this->tips(),
        ];
    }

    /**
     * @return array{
     *     owner: array{name: string, location: string, avatar: string, summary: string},
     *     pet: array<string, mixed>,
     *     recentMoments: array<int, array<string, mixed>>
     * }
     */
    public function scoutProfileData(): array
    {
        return [
            'owner' => $this->owner(),
            'pet' => $this->scout(),
            'recentMoments' => $this->scoutMoments(),
        ];
    }
}
```

Move the existing `owner()`, `posts()`, `meetups()`, `groups()`, and `tips()` arrays from `PetSocialPreviewController` into private methods on the service without changing their visible content. Move `pets()` and add one presentation contract to each item:

```php
'profile_route' => 'pet-social.pets.scout',
```

for Scout, and:

```php
'profile_route' => null,
```

for Nori.

Add `scout()` with this exact data shape:

```php
private function scout(): array
{
    return [
        'name' => 'Scout',
        'species' => 'Dog',
        'breed' => 'Border Collie mix',
        'age' => '4 years',
        'location' => 'Portland, OR',
        'status' => 'Available for park walks',
        'story' => 'Scout is happiest when a walk has a destination, a few new smells, and enough time to watch the world. At home, he settles quickly beside Mia and takes his role as window lookout very seriously.',
        'profile_image' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
        'cover_image' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=1600&h=760&q=85',
        'facts' => [
            ['label' => 'Energy', 'value' => 'High, with a calm indoor routine'],
            ['label' => 'Size', 'value' => 'Medium · 42 lb'],
            ['label' => 'Best walk', 'value' => '45–60 minutes'],
            ['label' => 'Vaccinations', 'value' => 'Up to date'],
            ['label' => 'Food note', 'value' => 'Chicken-free treats'],
        ],
        'compatibility' => [
            ['label' => 'Dogs', 'value' => 'Friendly after a calm hello'],
            ['label' => 'Children', 'value' => 'Comfortable with older children'],
            ['label' => 'Cats', 'value' => 'Needs a slow introduction'],
        ],
        'gallery' => [
            [
                'image' => 'https://images.unsplash.com/photo-1551717743-49959800b1f6?auto=format&fit=crop&w=1200&h=900&q=85',
                'alt' => 'Scout running across an open field',
                'caption' => 'The long route at Maple Loop.',
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1537151608828-ea2b11777ee8?auto=format&fit=crop&w=900&h=900&q=85',
                'alt' => 'Scout resting after an afternoon walk',
                'caption' => 'Post-walk quiet time.',
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=900&h=900&q=85',
                'alt' => 'Scout waiting attentively outdoors',
                'caption' => 'Practicing a patient wait.',
            ],
        ],
    ];
}
```

Add two recent moments using the existing `feed-card` contract:

```php
private function scoutMoments(): array
{
    return [
        [
            'author' => 'Mia Carter',
            'pet' => 'Scout',
            'time' => 'Yesterday',
            'body' => 'Scout chose the shady trail, carried his own collapsible bowl, and still had enough energy for one last lap around the garden.',
            'image' => 'https://images.unsplash.com/photo-1551717743-49959800b1f6?auto=format&fit=crop&w=1200&h=900&q=85',
            'tags' => ['trail day', 'Scout'],
            'stats' => ['paws' => '94', 'replies' => '16'],
        ],
        [
            'author' => 'Mia Carter',
            'pet' => 'Scout',
            'time' => '4 days ago',
            'body' => 'A calm cafe visit and a very serious inspection of every bicycle parked outside. Progress looks different every week.',
            'image' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=1200&h=900&q=85',
            'tags' => ['city practice', 'small wins'],
            'stats' => ['paws' => '121', 'replies' => '21'],
        ],
    ];
}
```

- [x] **Step 2: Make both controllers thin**

Replace the home controller body with:

```php
<?php

namespace App\Http\Controllers;

use App\Services\PawCirclePreviewService;
use Illuminate\Contracts\View\View;

class PetSocialPreviewController extends Controller
{
    public function __invoke(PawCirclePreviewService $preview): View
    {
        return view('pet-social.index', $preview->homePageData());
    }
}
```

Create the profile controller:

```php
<?php

namespace App\Http\Controllers;

use App\Services\PawCirclePreviewService;
use Illuminate\Contracts\View\View;

class PetProfilePreviewController extends Controller
{
    public function __invoke(PawCirclePreviewService $preview): View
    {
        return view('pet-social.pets.show', $preview->scoutProfileData());
    }
}
```

- [x] **Step 3: Register the fixed named route**

Add the controller import and route inside the existing group:

```php
use App\Http\Controllers\PetProfilePreviewController;

Route::middleware('web')->name('pet-social.')->group(function (): void {
    Route::get('/', PetSocialPreviewController::class)->name('preview');
    Route::get('/pets/scout', PetProfilePreviewController::class)->name('pets.scout');
});
```

- [x] **Step 4: Generalize the shared shell**

Change the shell to render:

```blade
<main class="mx-auto max-w-7xl px-4 py-5 pb-8 sm:px-6 lg:px-8">
    {{ $slot }}
</main>
```

Keep the existing document head, top navigation, and mobile navigation unchanged.
Move the former three-column `<main>` classes into a wrapper inside
`resources/views/pet-social/index.blade.php`.

- [x] **Step 5: Add the minimal profile view**

```blade
<x-pet-social.app-shell :owner="$owner">
    <section data-section="pet-profile" class="rounded-lg border border-paw-line bg-white p-6 shadow-sm">
        <p class="text-xs font-semibold uppercase text-paw-leaf">{{ $pet['species'] }}</p>
        <h1 class="mt-2 text-3xl font-semibold text-paw-ink">{{ $pet['name'] }}</h1>
        <p class="mt-2 text-sm text-paw-muted">{{ $pet['breed'] }} · {{ $pet['age'] }}</p>
    </section>
</x-pet-social.app-shell>
```

- [x] **Step 6: Verify GREEN and home regression**

Run:

```bash
php artisan test --compact tests/Feature/PetProfilePreviewTest.php tests/Feature/PetSocialPreviewTest.php
```

Expected: 2 passing tests.

### Task 3: Profile Content Components

**Files:**

- Modify: `tests/Feature/PetProfilePreviewTest.php`
- Create: `resources/views/components/pet-social/pet-profile-hero.blade.php`
- Create: `resources/views/components/pet-social/pet-facts.blade.php`
- Create: `resources/views/components/pet-social/pet-gallery.blade.php`
- Create: `resources/views/components/pet-social/owner-summary.blade.php`
- Modify: `resources/views/pet-social/pets/show.blade.php`

- [x] **Step 1: Extend the feature contract**

Add assertions after `assertSee('data-section="pet-profile"', false)`:

```php
->assertSee('data-section="care"', false)
->assertSee('data-section="compatibility"', false)
->assertSee('data-section="owner"', false)
->assertSee('data-section="gallery"', false)
->assertSee('data-section="recent-moments"', false)
->assertSee('aria-disabled="true"', false)
->assertSee('About Scout')
```

- [x] **Step 2: Verify RED**

Run:

```bash
php artisan test --compact tests/Feature/PetProfilePreviewTest.php
```

Expected: FAIL because the profile sections do not exist.

- [x] **Step 3: Implement focused components**

`pet-profile-hero` receives `pet` and renders a stable cover image, overlapping
profile image, one `h1`, breed/age/location metadata, status badge, and a
disabled `Plan a walk` button.

`pet-facts` receives `title`, `facts`, and `section`; it renders a bordered
section containing a `dl` and uses:

```blade
@forelse ($facts as $fact)
    <div class="grid gap-1 border-b border-paw-line py-3 last:border-b-0 sm:grid-cols-[7rem_1fr]">
        <dt class="text-xs font-semibold uppercase text-paw-muted">{{ $fact['label'] }}</dt>
        <dd class="text-sm leading-6 text-paw-ink">{{ $fact['value'] }}</dd>
    </div>
@empty
    <p class="text-sm text-paw-muted">No details available.</p>
@endforelse
```

`pet-gallery` receives `photos`; it renders figures in a responsive two-column
grid, makes the first figure span both columns from `sm`, and uses stable
`aspect-[4/3]` image boxes. Its empty state is `No photos shared yet.`

`owner-summary` receives `owner`; it renders Mia's avatar, name, location, and
summary in a compact bordered section with `data-section="owner"`.

All components start with `@props`, use `$attributes->merge()`, and contain no
queries, method calls, or inline CSS/JavaScript.

- [x] **Step 4: Compose the complete profile**

Use this page order:

```blade
<x-pet-social.app-shell :owner="$owner">
    <div data-section="pet-profile" class="grid gap-5">
        <x-pet-social.pet-profile-hero :pet="$pet" />

        <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <section class="rounded-lg border border-paw-line bg-white p-5 shadow-sm lg:col-start-1 lg:row-start-1">
                <p class="text-xs font-semibold uppercase text-paw-leaf">Life with Scout</p>
                <h2 class="mt-2 text-xl font-semibold text-paw-ink">About Scout</h2>
                <p class="mt-3 text-sm leading-7 text-paw-muted">{{ $pet['story'] }}</p>
            </section>

            <aside class="grid content-start gap-4 lg:col-start-2 lg:row-span-3 lg:row-start-1">
                <x-pet-social.pet-facts title="Care profile" section="care" :facts="$pet['facts']" />
                <x-pet-social.pet-facts title="Good company" section="compatibility" :facts="$pet['compatibility']" />
                <x-pet-social.owner-summary :owner="$owner" />
            </aside>

            <x-pet-social.pet-gallery class="lg:col-start-1 lg:row-start-2" :photos="$pet['gallery']" />

            <section data-section="recent-moments" class="lg:col-start-1 lg:row-start-3">
                <p class="text-xs font-semibold uppercase text-paw-leaf">From Mia</p>
                <h2 class="mt-2 text-xl font-semibold text-paw-ink">Recent moments</h2>
                <div class="mt-4 grid gap-4">
                    @forelse ($recentMoments as $post)
                        <x-pet-social.feed-card :post="$post" />
                    @empty
                        <p class="rounded-lg border border-dashed border-paw-line bg-white p-6 text-sm text-paw-muted">
                            No moments shared yet.
                        </p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-pet-social.app-shell>
```

- [x] **Step 5: Verify GREEN**

Run:

```bash
php artisan test --compact tests/Feature/PetProfilePreviewTest.php
```

Expected: 1 passing test.

### Task 4: Home-To-Profile Connectivity

**Files:**

- Modify: `tests/Feature/PetProfilePreviewTest.php`
- Modify: `resources/views/components/pet-social/profile-card.blade.php`

- [x] **Step 1: Write the failing connectivity test**

Append:

```php
test('the home preview links Scout to the pet profile', function () {
    $response = $this->get(route('pet-social.preview'));

    $response
        ->assertSuccessful()
        ->assertSee('href="'.route('pet-social.pets.scout').'"', false);
});
```

- [x] **Step 2: Verify RED**

Run:

```bash
php artisan test --compact --filter="home preview links Scout"
```

Expected: FAIL because Scout is plain text.

- [x] **Step 3: Render route-aware pet names**

Inside the existing `@forelse`, replace the name heading with:

```blade
@if ($pet['profile_route'])
    <h4 class="min-w-0 text-sm font-semibold text-paw-ink">
        <a href="{{ route($pet['profile_route']) }}" class="rounded-sm underline-offset-4 hover:text-paw-leaf hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf">
            {{ $pet['name'] }}
        </a>
    </h4>
@else
    <h4 class="min-w-0 text-sm font-semibold text-paw-ink">{{ $pet['name'] }}</h4>
@endif
```

- [x] **Step 4: Verify GREEN**

Run:

```bash
php artisan test --compact tests/Feature/PetProfilePreviewTest.php tests/Feature/PetSocialPreviewTest.php
```

Expected: 3 passing tests.

### Task 5: Quality And Browser Verification

**Files:**

- Verify all files changed in Tasks 1–4.
- Modify only files with confirmed formatting, accessibility, or responsive defects.

- [x] **Step 1: Format PHP**

Run:

```bash
vendor/bin/pint --dirty --format agent
```

Expected: PASS.

- [x] **Step 2: Run all tests**

Run:

```bash
php artisan test --compact
```

Expected: all tests pass.

- [x] **Step 3: Audit prohibited patterns**

Run:

```bash
rg -n "@foreach|DB::|Model::all|<form|method=\"post\"|fetch\\(|axios|x-data" app routes resources tests
```

Expected: no prohibited production usage; the literal `<form` may appear only in negative test assertions.

- [x] **Step 4: Build production assets**

Run:

```bash
npm run build
```

Expected: Vite exits successfully.

- [x] **Step 5: Verify routes**

Run:

```bash
php artisan route:list --except-vendor
```

Expected: both `pet-social.preview` and `pet-social.pets.scout` are listed.

- [x] **Step 6: Browser verification**

Open `https://mengto.test/pets/scout` at widths 320, 768, 1024, and 1440.
At every width verify:

- No horizontal scrolling or overlapping content.
- Scout, care, owner, gallery, and recent moments are visible.
- The mobile navigation remains in normal document flow.
- Images have stable dimensions.
- The console has no errors or warnings.

- [x] **Step 7: Final no-Git checkpoint**

Run:

```bash
test -d .git && git status --short || printf 'NO_GIT_REPOSITORY\n'
```

Expected: `NO_GIT_REPOSITORY`.
