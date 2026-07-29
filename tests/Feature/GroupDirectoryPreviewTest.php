<?php

use App\Http\Controllers\GroupDirectoryPreviewController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

test('the group directory renders as a static local community catalog', function () {
    $route = Route::getRoutes()->getByName('pet-social.groups.index');

    expect($route)
        ->not->toBeNull()
        ->and($route?->uri())->toBe('groups')
        ->and($route?->methods())->toBe(['GET', 'HEAD'])
        ->and($route?->getActionName())->toBe(GroupDirectoryPreviewController::class)
        ->and($route?->gatherMiddleware())->toContain('web');

    config()->set('session.driver', 'database');

    $this->expectsDatabaseQueryCount(0);

    $response = $this->get(route('pet-social.groups.index'));

    $response
        ->assertSuccessful()
        ->assertCookieMissing(config('session.cookie'))
        ->assertCookieMissing('XSRF-TOKEN')
        ->assertSee('<title>Groups | PawCircle</title>', false)
        ->assertSee('data-section="group-header"', false)
        ->assertSee('data-section="group-summary"', false)
        ->assertSee('data-section="group-filters"', false)
        ->assertSee('data-section="group-directory"', false)
        ->assertSee('Apartment Pets PDX')
        ->assertSee('Trail Tails')
        ->assertSee('Cat People of Portland')
        ->assertSee('Foster Network PDX')
        ->assertSee('Recommended')
        ->assertSee('alt="Dog and cat resting together on a couch at home"', false)
        ->assertSee('alt="Dogs running together in a neighborhood park"', false)
        ->assertSee('alt="Two fluffy cats sitting together indoors"', false)
        ->assertSee('alt="Foster dog resting on a blue couch"', false);

    expect($route?->excludedMiddleware())->toContain(
        StartSession::class,
        ShareErrorsFromSession::class,
        PreventRequestForgery::class,
    );

    $xpath = pawCircleResponseXPath($response);

    expect($xpath->query('//h1')->length)->toBe(1)
        ->and(trim((string) $xpath->query('//h1')->item(0)?->textContent))->toBe('Find your people and their pets')
        ->and($xpath->query('//article[@data-group-card]')->length)->toBe(4)
        ->and($xpath->query('//section[@data-section="group-directory"]/h2')->length)->toBe(1)
        ->and($xpath->query('//article[@data-group-card]//h3')->length)->toBe(4)
        ->and($xpath->query('//article[@data-group-card]//img[@loading="eager" and @fetchpriority="high" and @srcset and @sizes]')->length)->toBe(1)
        ->and($xpath->query('//article[@data-group-card]//img[@loading="lazy" and @decoding="async" and @srcset and @sizes]')->length)->toBe(3)
        ->and($xpath->query('//article[@data-group-card]//a')->length)->toBe(0)
        ->and($xpath->query('//main//button[@disabled]')->length)->toBe(10)
        ->and($xpath->query('//main//button[not(@disabled)]')->length)->toBe(0)
        ->and($xpath->query('//main//button[@aria-pressed="true" and @disabled]')->length)->toBe(1)
        ->and($xpath->query('//main//a')->length)->toBe(0)
        ->and($xpath->query('//main//input | //main//select | //main//textarea')->length)->toBe(0)
        ->and($xpath->query('//main//*[@role="button" and not(@aria-disabled="true")]')->length)->toBe(0)
        ->and($xpath->query('//main//form')->length)->toBe(0)
        ->and($xpath->query('//header//nav[@aria-label="Preview navigation"]')->length)->toBe(0);
});

test('groups navigation is active in the catalog and linked from existing pages', function () {
    $groupsUrl = route('pet-social.groups.index');

    $groupsResponse = $this->get($groupsUrl);
    $feedResponse = $this->get(route('pet-social.preview'));
    $petsResponse = $this->get(route('pet-social.pets.index'));
    $profileResponse = $this->get(route('pet-social.pets.scout'));
    $meetupsResponse = $this->get(route('pet-social.meetups.index'));

    foreach ([$groupsResponse, $feedResponse, $petsResponse, $profileResponse, $meetupsResponse] as $response) {
        $response
            ->assertSuccessful()
            ->assertSee('href="'.$groupsUrl.'"', false)
            ->assertSee('data-nav-item="groups"', false);
    }

    $groupsXPath = pawCircleResponseXPath($groupsResponse);
    $feedXPath = pawCircleResponseXPath($feedResponse);
    $petsXPath = pawCircleResponseXPath($petsResponse);
    $meetupsXPath = pawCircleResponseXPath($meetupsResponse);

    expect($groupsXPath->query('//a[@data-nav-item="groups" and @aria-current="page"]')->length)->toBe(2)
        ->and($groupsXPath->query('//a[@data-nav-item="feed" and @aria-current]')->length)->toBe(0)
        ->and($groupsXPath->query('//a[@data-nav-item="pets" and @aria-current]')->length)->toBe(0)
        ->and($groupsXPath->query('//a[@data-nav-item="meetups" and @aria-current]')->length)->toBe(0)
        ->and($feedXPath->query('//a[@data-nav-item="feed" and @aria-current="page"]')->length)->toBe(2)
        ->and($petsXPath->query('//a[@data-nav-item="pets" and @aria-current="page"]')->length)->toBe(2)
        ->and($meetupsXPath->query('//a[@data-nav-item="meetups" and @aria-current="page"]')->length)->toBe(2);
});

test('the group card renders an explicit empty tags state', function () {
    $group = [
        'name' => 'Test Group',
        'category' => 'Local',
        'members' => '12 members',
        'activity' => '3 posts this week',
        'topic' => 'Friendly introductions',
        'description' => 'A test community for nearby pets and their people.',
        'organizer' => 'Test Organizer',
        'organizer_initials' => 'TO',
        'image' => 'https://example.test/group.jpg',
        'image_small' => 'https://example.test/group-small.jpg',
        'image_medium' => 'https://example.test/group-medium.jpg',
        'image_alt' => 'Test pets together',
        'tags' => [],
    ];

    $card = Blade::render(
        '<x-pet-social.group-card :group="$group" />',
        ['group' => $group],
    );

    expect($card)->toContain('Open to new neighbors.');
});
