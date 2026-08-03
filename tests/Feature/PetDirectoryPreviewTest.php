<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

test('the pet directory renders as a functional discovery page', function () {
    expect(Route::has('pets.index'))->toBeTrue();

    $response = $this->get(route('pets.index'));

    $response
        ->assertSuccessful()
        ->assertSee('<title>Pets | PawCircle</title>', false)
        ->assertSee('data-section="directory-header"', false)
        ->assertSee('data-section="directory-filters"', false)
        ->assertSee('data-section="pet-directory"', false)
        ->assertSee('Scout')
        ->assertSee('Nori')
        ->assertSee('Maple')
        ->assertSee('Olive')
        ->assertSee('Pico')
        ->assertSee('Clover')
        ->assertSee('alt="Scout, a black and white Border Collie, resting on grass"', false)
        ->assertSee('alt="Nori, a tabby cat, looking toward the camera"', false)
        ->assertSee('alt="Clover, a white rabbit, sitting in grass"', false)
        ->assertSee('loading="lazy"', false)
        ->assertSee('Search by name or breed');

    $xpath = responseXPath($response);

    expect($xpath->query('//h1')->length)->toBe(1)
        ->and(trim((string) $xpath->query('//h1')->item(0)?->textContent))->toBe('Pets nearby')
        ->and($xpath->query('//article[@data-directory-pet]')->length)->toBe(6)
        ->and($xpath->query('//article[@data-directory-pet]//h2')->length)->toBe(6)
        ->and($xpath->query('//section[@data-section="pet-directory"]/*[@role="list" and contains(concat(" ", normalize-space(@class), " "), " sm:grid-cols-2 ") and contains(concat(" ", normalize-space(@class), " "), " xl:grid-cols-3 ")]')->length)->toBe(1)
        ->and($xpath->query('//input[@id="directory-search" and not(@disabled)]')->length)->toBe(1)
        ->and($xpath->query('//button[normalize-space()="Follow" and not(@disabled)]')->length)->toBe(6)
        ->and($xpath->query('//form')->length)->toBeGreaterThan(0);
});

test('the directory and existing pages expose working Feed and Pets navigation', function () {
    $directoryUrl = route('pets.index');
    $feedUrl = route('preview.feed');
    $profileUrl = route('pets.scout');

    $directoryResponse = $this->get($directoryUrl);
    $directoryResponse
        ->assertSuccessful()
        ->assertSee('href="'.$profileUrl.'"', false)
        ->assertSee('href="'.$feedUrl.'"', false)
        ->assertSee('data-nav-item="pets"', false);

    $feedResponse = $this->get($feedUrl);
    $feedResponse
        ->assertSuccessful()
        ->assertSee('href="'.$directoryUrl.'"', false)
        ->assertSee('data-nav-item="feed"', false);

    $profileResponse = $this->get($profileUrl);
    $profileResponse
        ->assertSuccessful()
        ->assertSee('href="'.$directoryUrl.'"', false)
        ->assertSee('data-nav-item="pets"', false);

    $directoryXPath = responseXPath($directoryResponse);
    $feedXPath = responseXPath($feedResponse);
    $profileXPath = responseXPath($profileResponse);

    expect($directoryXPath->query('//a[@data-nav-item="pets" and @aria-current="page"]')->length)->toBe(2)
        ->and($directoryXPath->query('//a[@data-nav-item="feed" and @aria-current]')->length)->toBe(0)
        ->and($feedXPath->query('//a[@data-nav-item="feed" and @aria-current="page"]')->length)->toBe(2)
        ->and($feedXPath->query('//a[@data-nav-item="pets" and @aria-current]')->length)->toBe(0)
        ->and($profileXPath->query('//a[@data-nav-item="pets" and @aria-current="page"]')->length)->toBe(2)
        ->and($profileXPath->query('//a[@data-nav-item="feed" and @aria-current]')->length)->toBe(0);
});

test('the directory card renders an explicit empty traits state', function () {
    $pet = [
        'name' => 'Test Pet',
        'species' => 'Dog',
        'breed' => 'Mixed breed',
        'age' => '3 years',
        'owner' => 'Test Owner',
        'neighborhood' => 'Portland, OR',
        'status' => 'Quiet walk companion',
        'image' => 'https://example.test/pet.jpg',
        'image_alt' => 'Test pet resting outside',
        'traits' => [],
        'profile_route' => null,
    ];

    $card = Blade::render(
        '<x-pet-directory-card :pet="$pet" />',
        ['pet' => $pet],
    );

    expect($card)->toContain('No traits shared.');
});
