<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

test('the meetup directory renders as a static local schedule', function () {
    expect(Route::has('pet-social.meetups.index'))->toBeTrue();

    $this->expectsDatabaseQueryCount(0);

    $response = $this->get(route('pet-social.meetups.index'));

    $response
        ->assertSuccessful()
        ->assertSee('<title>Meetups | PawCircle</title>', false)
        ->assertSee('data-section="meetup-header"', false)
        ->assertSee('data-section="meetup-schedule"', false)
        ->assertSee('data-section="meetup-filters"', false)
        ->assertSee('data-section="meetup-directory"', false)
        ->assertSee('Small dog social hour')
        ->assertSee('Rescue foster coffee walk')
        ->assertSee('Calm senior dog stroll')
        ->assertSee('Upcoming')
        ->assertDontSee('This week')
        ->assertSee('alt="Small dogs meeting in a fenced neighborhood park"', false)
        ->assertSee('alt="Pet owners taking a relaxed community walk through a park"', false)
        ->assertSee('alt="Small dogs exploring an autumn park together"', false);

    $xpath = pawCircleResponseXPath($response);

    expect($xpath->query('//h1')->length)->toBe(1)
        ->and(trim((string) $xpath->query('//h1')->item(0)?->textContent))->toBe('Meet your neighborhood pack')
        ->and($xpath->query('//article[@data-meetup-card]')->length)->toBe(3)
        ->and($xpath->query('//section[@data-section="meetup-directory"]/h2')->length)->toBe(1)
        ->and($xpath->query('//article[@data-meetup-card]//h3')->length)->toBe(3)
        ->and($xpath->query('//article[@data-meetup-card]//time[@data-meetup-date and @datetime]')->length)->toBe(3)
        ->and($xpath->query('//article[@data-meetup-card]//img[@loading="eager" and @fetchpriority="high" and @srcset and @sizes]')->length)->toBe(1)
        ->and($xpath->query('//article[@data-meetup-card]//img[@loading="lazy" and @decoding="async" and @srcset and @sizes]')->length)->toBe(2)
        ->and($xpath->query('//article[@data-meetup-card]//a')->length)->toBe(0)
        ->and($xpath->query('//main//button[@disabled]')->length)->toBe(9)
        ->and($xpath->query('//main//button[not(@disabled)]')->length)->toBe(0)
        ->and($xpath->query('//main//button[@aria-pressed="true" and @disabled]')->length)->toBe(1)
        ->and($xpath->query('//main//a')->length)->toBe(0)
        ->and($xpath->query('//main//input | //main//select | //main//textarea')->length)->toBe(0)
        ->and($xpath->query('//main//*[@role="button" and not(@aria-disabled="true")]')->length)->toBe(0)
        ->and($xpath->query('//main//form')->length)->toBe(0);
});

test('meetups navigation is active on the schedule and linked from existing pages', function () {
    $meetupsUrl = route('pet-social.meetups.index');

    $meetupsResponse = $this->get($meetupsUrl);
    $feedResponse = $this->get(route('pet-social.preview'));
    $petsResponse = $this->get(route('pet-social.pets.index'));
    $profileResponse = $this->get(route('pet-social.pets.scout'));

    foreach ([$meetupsResponse, $feedResponse, $petsResponse, $profileResponse] as $response) {
        $response
            ->assertSuccessful()
            ->assertSee('href="'.$meetupsUrl.'"', false)
            ->assertSee('data-nav-item="meetups"', false);
    }

    $meetupsXPath = pawCircleResponseXPath($meetupsResponse);
    $feedXPath = pawCircleResponseXPath($feedResponse);
    $petsXPath = pawCircleResponseXPath($petsResponse);

    expect($meetupsXPath->query('//a[@data-nav-item="meetups" and @aria-current="page"]')->length)->toBe(2)
        ->and($meetupsXPath->query('//a[@data-nav-item="feed" and @aria-current]')->length)->toBe(0)
        ->and($meetupsXPath->query('//a[@data-nav-item="pets" and @aria-current]')->length)->toBe(0)
        ->and($feedXPath->query('//a[@data-nav-item="feed" and @aria-current="page"]')->length)->toBe(2)
        ->and($petsXPath->query('//a[@data-nav-item="pets" and @aria-current="page"]')->length)->toBe(2);
});

test('the meetup card renders an explicit empty tags state', function () {
    $meetup = [
        'title' => 'Test meetup',
        'category' => 'Walk',
        'day' => 'SAT',
        'date' => '08',
        'date_label' => 'Sat, Aug 8',
        'date_accessible' => 'Saturday, August 8, 2026 at 10:00 AM',
        'datetime' => '2026-08-08T10:00:00-07:00',
        'time' => '10:00 AM',
        'place' => 'Test Park',
        'neighborhood' => 'Portland',
        'distance' => '1.0 mi',
        'attendees' => '8 neighbors going',
        'description' => 'A test gathering for local pets and their people.',
        'host' => 'Test Host',
        'host_initials' => 'TH',
        'image' => 'https://example.test/meetup.jpg',
        'image_small' => 'https://example.test/meetup-small.jpg',
        'image_medium' => 'https://example.test/meetup-medium.jpg',
        'image_alt' => 'Test meetup in a park',
        'tags' => [],
    ];

    $card = Blade::render(
        '<x-pet-social.meetup-card :meetup="$meetup" />',
        ['meetup' => $meetup],
    );

    expect($card)->toContain('All friendly pets welcome.');
});
