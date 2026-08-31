<?php

declare(strict_types=1);

use App\Models\ForumEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

test('the meetup directory renders a functional event schedule', function () {
    ForumEvent::factory()
        ->count(8)
        ->sequence(
            ['title' => 'Calm walk at Laurelhurst Park'],
            ['title' => 'Puppy socialization lab'],
            ['title' => 'Travel-ready pet webinar'],
            ['title' => 'Accessible senior-animal club'],
            ['title' => 'Rescue volunteer orientation'],
            ['title' => 'Community bird-care session'],
            ['title' => 'Aquarium keeper meetup'],
            ['title' => 'First-time owner circle'],
        )
        ->create();

    expect(Route::has('meetups.index'))->toBeTrue();

    $response = $this->get(route('meetups.index'));

    $response
        ->assertSuccessful()
        ->assertSee('<title>Community events</title>', false)
        ->assertSee('data-section="event-directory"', false)
        ->assertSee('Calm walk at Laurelhurst Park')
        ->assertSee('Puppy socialization lab')
        ->assertSee('Travel-ready pet webinar')
        ->assertSee('Search events')
        ->assertSee('Create an event');

    $xpath = responseXPath($response);

    expect($xpath->query('//h1')->length)->toBe(1)
        ->and(trim((string) $xpath->query('//h1')->item(0)?->textContent))->toBe('Events and clubs')
        ->and($xpath->query('//section[@data-section="event-directory"]//article')->length)->toBe(8)
        ->and($xpath->query('//section[@data-section="event-directory"]//article//h3')->length)->toBe(8)
        ->and($xpath->query('//main//input[@type="search" and not(@disabled)]')->length)->toBeGreaterThan(0)
        ->and($xpath->query('//main//button[not(@disabled)]')->length)->toBeGreaterThan(0)
        ->and($xpath->query('//main//a')->length)->toBeGreaterThan(0)
        ->and($xpath->query('//main//form')->length)->toBeGreaterThan(0);
});

test('meetups navigation is active on the schedule and linked from existing pages', function () {
    $meetupsUrl = route('meetups.index');

    $meetupsResponse = $this->get($meetupsUrl);
    $feedResponse = $this->get(route('preview.feed'));
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
        'media_target' => [
            'url' => route('meetups.index'),
            'label' => 'Open event',
        ],
    ];

    $card = Blade::render(
        '<x-meetup-card :meetup="$meetup" />',
        ['meetup' => $meetup],
    );

    expect($card)->toContain('All friendly pets welcome.')
        ->toContain('href="'.route('meetups.index').'"')
        ->not->toContain('toggle-meetup')
        ->not->toContain(route('actions.perform'));
});
