<?php

use Illuminate\Support\Facades\Route;

test('the home feed renders as a functional app shell', function () {
    expect(Route::has('home'))->toBeTrue();

    $response = $this->get(route('home'));

    $response
        ->assertSuccessful()
        ->assertSee('PawCircle')
        ->assertSee('data-section="feed"', false)
        ->assertSee('data-section="pets"', false)
        ->assertSee('data-section="meetups"', false)
        ->assertSee('data-section="groups"', false)
        ->assertSee('data-section="tips"', false)
        ->assertSee('<form', false)
        ->assertDontSee('Laravel');

    $xpath = responseXPath($response);

    expect($xpath->query('//main//form')->length)->toBeGreaterThan(0)
        ->and($xpath->query('//main//button[not(@disabled)]')->length)->toBeGreaterThan(0)
        ->and($xpath->query('//header[@data-site-header]/*[@data-header-utility]')->length)->toBe(1)
        ->and($xpath->query('//header[@data-site-header]/*[@data-header-primary]')->length)->toBe(1)
        ->and($xpath->query('//header[@data-site-header]//nav[@data-navigation-variant="desktop"]')->length)->toBe(1)
        ->and($xpath->query('//header[@data-site-header]//nav[@data-navigation-variant="desktop"]//a[@data-nav-item]')->length)->toBe(13)
        ->and($xpath->query('//nav[@data-navigation-variant="mobile"]//a[@data-nav-item]')->length)->toBe(11)
        ->and($xpath->query('//*[@data-header-utility]//*[@data-header-link="discover"]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-header-utility]//*[@data-header-link="profile"]')->length)->toBe(1)
        ->and($xpath->query('//a[@data-nav-item="feed" and @aria-current="page"]')->length)->toBe(2);
});
