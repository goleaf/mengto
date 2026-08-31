<?php

declare(strict_types=1);

use App\Enums\PetManagerRole;
use App\Enums\PetManagerStatus;
use App\Enums\UserStatus;
use App\Http\Controllers\PetProfileWorkspaceController;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

it('routes the canonical pet workspace through the Eloquent controller', function (): void {
    $route = Route::getRoutes()->getByName('pets.index');

    expect($route)->not->toBeNull()
        ->and($route?->getActionName())->toBe(PetProfileWorkspaceController::class);

    $this->get(route('pets.index'))
        ->assertOk()
        ->assertSee('data-section="pet-profile-workspace"', false)
        ->assertSee('data-section="pet-workspace-header"', false)
        ->assertSee('data-section="pet-workspace-filters"', false)
        ->assertSee(route('pets.manage.create'), false)
        ->assertSee(route('pets.manage.invitations'), false)
        ->assertSee(route('discover.index', ['category' => 'pets']), false)
        ->assertDontSee('Pets nearby')
        ->assertDontSee('Maple')
        ->assertDontSee('Clover');
});

it('shows only owned and actively shared profiles in the workspace', function (): void {
    $owned = PetProfile::factory()->for($this->authenticatedUser)->draft()->create([
        'name' => 'Owned Draft',
    ]);
    $sharedOwner = User::factory()->create();
    $shared = PetProfile::factory()->for($sharedOwner)->discoverable()->create([
        'name' => 'Shared Care',
    ]);
    PetProfileManager::factory()
        ->for($shared, 'profile')
        ->for($this->authenticatedUser)
        ->create(['role' => PetManagerRole::Caregiver]);

    $invited = PetProfile::factory()->for($sharedOwner)->create(['name' => 'Invitation Only']);
    PetProfileManager::factory()
        ->for($invited, 'profile')
        ->for($this->authenticatedUser)
        ->invited()
        ->create();

    $suspended = PetProfile::factory()->for($sharedOwner)->create(['name' => 'Suspended Access']);
    PetProfileManager::factory()
        ->for($suspended, 'profile')
        ->for($this->authenticatedUser)
        ->create(['status' => PetManagerStatus::Suspended]);

    PetProfile::factory()->for($sharedOwner)->discoverable()->create(['name' => 'Other Public Pet']);

    $response = $this->get(route('pets.index'));
    $xpath = responseXPath($response);

    $response
        ->assertOk()
        ->assertSee('Owned Draft')
        ->assertSee('Shared Care')
        ->assertDontSee('Invitation Only')
        ->assertDontSee('Suspended Access')
        ->assertDontSee('Other Public Pet');

    expect($xpath->query('//h1')->length)->toBe(1)
        ->and($xpath->query('//article[@data-pet-workspace-profile]')->length)->toBe(2)
        ->and($xpath->query('//article[@data-pet-workspace-profile]//h2')->length)->toBe(2)
        ->and($xpath->query('//input[@id="pet-workspace-search" and not(@disabled)]')->length)->toBe(1)
        ->and($xpath->query('//button[@type="submit" and not(@disabled)]')->length)->toBeGreaterThan(0);

    $ownedCard = $xpath->query('//article[@data-pet-workspace-profile][.//h2[normalize-space()="Owned Draft"]]')->item(0);
    $sharedCard = $xpath->query('//article[@data-pet-workspace-profile][.//h2[normalize-space()="Shared Care"]]')->item(0);

    expect($ownedCard)->not->toBeNull()
        ->and($sharedCard)->not->toBeNull()
        ->and($xpath->query('.//a[@href="'.route('pets.manage.show', $owned).'" ]', $ownedCard)->length)->toBeGreaterThan(0)
        ->and($xpath->query('.//a[@href="'.route('pets.manage.show', $shared).'" ]', $sharedCard)->length)->toBe(0)
        ->and($xpath->query('.//a[@href="'.route('pets.profile', $shared).'" ]', $sharedCard)->length)->toBeGreaterThan(0);
});

it('searches filters sorts and paginates managed profiles with safe url state', function (): void {
    PetProfile::factory()->for($this->authenticatedUser)->discoverable()->create([
        'name' => 'Alpha Birch',
        'species' => 'dog',
        'breed' => 'Border Collie',
    ]);
    PetProfile::factory()->for($this->authenticatedUser)->draft()->create([
        'name' => 'Beta Maple',
        'species' => 'cat',
        'breed' => 'Domestic Shorthair',
    ]);
    PetProfile::factory()->count(13)->for($this->authenticatedUser)->create();

    $this->get(route('pets.index', ['q' => 'Border', 'filter' => 'owned', 'sort' => 'name']))
        ->assertOk()
        ->assertSee('Alpha Birch')
        ->assertDontSee('Beta Maple');

    $this->get(route('pets.index', ['filter' => 'drafts']))
        ->assertOk()
        ->assertSee('Beta Maple')
        ->assertDontSee('Alpha Birch');

    $page = $this->get(route('pets.index'));
    $xpath = responseXPath($page);

    expect($xpath->query('//article[@data-pet-workspace-profile]')->length)->toBe(12)
        ->and($xpath->query('//nav[@aria-label="Pet profile pages"]')->length)->toBe(1);

    $this->get(route('pets.index', ['filter' => 'foreign', 'sort' => 'unsafe']))
        ->assertRedirect()
        ->assertSessionHasErrors(['filter', 'sort']);
});

it('renders a true empty state separately from a filtered empty state', function (): void {
    $this->get(route('pets.index'))
        ->assertOk()
        ->assertSee(__('pet_workspace.empty_title'))
        ->assertSee(route('pets.manage.create'), false);

    PetProfile::factory()->for($this->authenticatedUser)->create(['name' => 'Pippa']);

    $this->get(route('pets.index', ['q' => 'not-present']))
        ->assertOk()
        ->assertSee(__('pet_workspace.filtered_empty_title'))
        ->assertSee(route('pets.index'), false);
});

it('surfaces pending invitations without treating them as active access', function (): void {
    $owner = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->create(['name' => 'Pending Share']);
    PetProfileManager::factory()
        ->for($profile, 'profile')
        ->for($this->authenticatedUser)
        ->invited()
        ->create(['ends_at' => now()->addDay()]);

    $this->get(route('pets.index'))
        ->assertOk()
        ->assertSee('data-section="pet-workspace-invitations"', false)
        ->assertSee(__('pet_workspace.review_invitations'))
        ->assertDontSee('Pending Share');
});

it('localizes the workspace in every supported locale without exposing translation keys', function (string $locale): void {
    $this->authenticatedUser->forceFill(['locale' => $locale])->save();
    PetProfile::factory()->for($this->authenticatedUser)->create(['name' => 'Baks']);

    $response = $this->get(route('pets.index'));

    $response
        ->assertOk()
        ->assertSee(trans('pet_workspace.title', locale: $locale))
        ->assertDontSee('pet_workspace.')
        ->assertDontSee('@php', false);

    expect(responseXPath($response)->query('//h1')->length)->toBe(1);
})->with(['en', 'lt', 'ru']);

it('rejects inactive accounts at the portal boundary', function (): void {
    $inactive = User::factory()->create(['status' => UserStatus::Suspended]);

    $this->actingAs($inactive)
        ->get(route('pets.index'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('feedback', __('auth.login.account_unavailable'));
});

it('keeps workspace query count bounded as managed profile volume grows', function (): void {
    PetProfile::factory()->count(12)->for($this->authenticatedUser)->create();

    DB::connection()->enableQueryLog();
    $this->get(route('pets.index'))->assertOk();
    $baseline = count(DB::getQueryLog());
    DB::flushQueryLog();

    PetProfile::factory()->count(40)->for($this->authenticatedUser)->create();

    DB::flushQueryLog();
    $this->get(route('pets.index'))->assertOk();
    $expanded = count(DB::getQueryLog());
    DB::connection()->disableQueryLog();

    expect($baseline)->toBeLessThanOrEqual(12)
        ->and($expanded)->toBeLessThanOrEqual($baseline + 1);
});
