<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

test('the meetup directory renders a functional event schedule', function () {
    expect(Route::has('meetups.index'))->toBeTrue();

    $response = $this->get(route('meetups.index'));

    $response
        ->assertSuccessful()
        ->assertSee('<title>Events | PawCircle</title>', false)
        ->assertSee('data-section="event-header"', false)
        ->assertSee('data-section="event-summary"', false)
        ->assertSee('data-section="event-directory-results"', false)
        ->assertSee('class="event-toolbar__form"', false)
        ->assertSee('event-grid', false)
        ->assertSee('Urgent neighborhood search for Scout')
        ->assertSee('Calm walk at Laurelhurst Park')
        ->assertSee('Puppy socialization lab')
        ->assertSee('Travel-ready pet webinar')
        ->assertSee('Registration open')
        ->assertSee('Search events')
        ->assertSee('Event view');

    $xpath = responseXPath($response);

    expect($xpath->query('//h1')->length)->toBe(1)
        ->and(trim((string) $xpath->query('//h1')->item(0)?->textContent))->toBe('Find a gathering that fits you and your pet')
        ->and($xpath->query('//article[contains(concat(" ", normalize-space(@class), " "), " event-card ")]')->length)->toBe(8)
        ->and($xpath->query('//article[contains(concat(" ", normalize-space(@class), " "), " event-card ") and @role="listitem"]')->length)->toBe(8)
        ->and($xpath->query('//article[contains(concat(" ", normalize-space(@class), " "), " event-card ")]//h2')->length)->toBe(8)
        ->and($xpath->query('//article[contains(concat(" ", normalize-space(@class), " "), " event-card ")]//time[@datetime]')->length)->toBe(8)
        ->and($xpath->query('//section[@data-section="event-directory-results"]/*[@role="list" and contains(concat(" ", normalize-space(@class), " "), " sm:grid-cols-2 ") and contains(concat(" ", normalize-space(@class), " "), " xl:grid-cols-3 ") and not(contains(concat(" ", normalize-space(@class), " "), " xl:grid-cols-4 "))]')->length)->toBe(1)
        ->and($xpath->query('//main//input[@id="event-search" and not(@disabled)]')->length)->toBe(1)
        ->and($xpath->query('//main//button[not(@disabled)]')->length)->toBeGreaterThan(0)
        ->and($xpath->query('//main//a')->length)->toBeGreaterThan(0)
        ->and($xpath->query('//main//form')->length)->toBeGreaterThan(0);
});

test('meetups navigation is active on the schedule and linked from existing pages', function () {
    $meetupsUrl = route('meetups.index');

    $meetupsResponse = $this->get($meetupsUrl);
    $feedResponse = $this->get(route('home'));
    $petsResponse = $this->get(route('pets.index'));
    $profileResponse = $this->get(route('pets.scout'));

    foreach ([$meetupsResponse, $feedResponse, $petsResponse, $profileResponse] as $response) {
        $response
            ->assertSuccessful()
            ->assertSee('href="'.$meetupsUrl.'"', false)
            ->assertSee('data-nav-item="meetups"', false);
    }

    $meetupsXPath = responseXPath($meetupsResponse);
    $feedXPath = responseXPath($feedResponse);
    $petsXPath = responseXPath($petsResponse);

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
        '<x-meetup-card :meetup="$meetup" />',
        ['meetup' => $meetup],
    );

    expect($card)->toContain('All friendly pets welcome.');
});
