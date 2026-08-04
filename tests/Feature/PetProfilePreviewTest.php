<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

test('the Scout profile renders as a functional pet page', function () {
    expect(Route::has('pets.scout'))->toBeTrue();

    $response = $this->get(route('pets.scout'));

    $response
        ->assertSuccessful()
        ->assertSee('Scout')
        ->assertSee('data-section="pet-profile"', false)
        ->assertSee('data-section="pet-profile-hero"', false)
        ->assertSee('data-section="owner"', false)
        ->assertSee('data-section="pet-moments"', false)
        ->assertSee('data-section="pet-profile-badges"', false)
        ->assertSee('<title>Scout @mia-carter/scout | PawCircle</title>', false)
        ->assertSee('href="#main-content"', false)
        ->assertSee('Mia Carter')
        ->assertSee('alt="Scout catching a yellow frisbee on the grass"', false)
        ->assertSee('alt="Scout resting on a wooden porch"', false)
        ->assertDontSee('alt="Scout shared moment"', false)
        ->assertSee('<form', false);

    $xpath = responseXPath($response);

    expect($xpath->query('//h1[normalize-space()="Scout"]')->length)->toBe(1)
        ->and($xpath->query('//main//form')->length)->toBeGreaterThan(0)
        ->and($xpath->query('//main//button[not(@disabled)]')->length)->toBeGreaterThan(0);
});

test('the feed preview links Scout to the pet profile', function () {
    $response = $this->get(route('preview.feed'));

    $response
        ->assertSuccessful()
        ->assertSee('href="'.route('pets.scout').'"', false)
        ->assertSee('data-profile-link', false);

    expect(responseXPath($response)->query('//h2[contains(@class, "post-identity__name")][normalize-space()="Ari Jensen"]')->length)
        ->toBe(1);
});

test('owner profile links authenticated users to profile settings', function () {
    $this->get(route('profile.mia'))
        ->assertSuccessful()
        ->assertSee('href="'.route('profile.settings').'"', false)
        ->assertSee(__('member_profiles.owner.actions.settings'));
});

test('the Scout profile uses a consistent Border Collie photo set', function () {
    $response = $this->get(route('pets.scout', ['tab' => 'photos']));

    $response
        ->assertSuccessful()
        ->assertSee('photo-1654256578072-b932c33cb92e')
        ->assertSee('photo-1624361239583-7ba5ffb376f5')
        ->assertSee('photo-1621169225409-5de158d10015')
        ->assertSee('photo-1625679895477-526b21a77f0c')
        ->assertDontSee('photo-1587300003388-59208cc962cb')
        ->assertDontSee('photo-1549248602-ac385cee2120')
        ->assertDontSee('photo-1551717743-49959800b1f6')
        ->assertDontSee('photo-1537151608828-ea2b11777ee8')
        ->assertDontSee('photo-1552053831-71594a27632d');
});

test('profile accessibility markup keeps valid empty facts and readable status color', function () {
    $facts = Blade::render(
        '<x-pet-facts title="Care profile" section="care" :facts="$facts" />',
        ['facts' => []],
    );

    expect($facts)
        ->toContain('<dt class="sr-only">Details</dt>')
        ->toContain('<dd class="text-sm text-paw-muted">No details available.</dd>');

    expect(File::get(resource_path('css/app.css')))
        ->toContain('--color-paw-coral: #b44739;');
});
