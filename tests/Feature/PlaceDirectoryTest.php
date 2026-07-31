<?php

declare(strict_types=1);

use App\Services\PlacePresenter;
use App\Services\PlaceState;

test('the server rendered place directory is searchable and paginated without browser location', function () {
    $firstPage = $this->get(route('places.index', [
        'view' => 'list',
        'sort' => 'name',
    ]));

    $firstPage
        ->assertOk()
        ->assertViewHas('places', function (array $places): bool {
            $names = array_column($places['items'], 'name');
            $sortedNames = $names;
            natcasesort($sortedNames);

            return count($places['items']) === 6
                && $names === array_values($sortedNames)
                && $places['pagination']['current_page'] === 1
                && $places['pagination']['last_page'] === 2
                && $places['pagination']['total'] === 12
                && $places['location']['enabled'] === false;
        })
        ->assertSee('data-place-pagination', false)
        ->assertSee('page=2', false)
        ->assertSee('Location not shared')
        ->assertSee('role="list"', false)
        ->assertSee('role="listitem"', false);

    expect(responseXPath($firstPage)->query('//*[@data-place-card]')->length)->toBe(6);
    expect(responseXPath($firstPage)->query('//main//h1')->length)->toBe(1)
        ->and(responseXPath($firstPage)->query('//*[@data-place-card]//h3')->length)->toBe(6)
        ->and(responseXPath($firstPage)->query('//*[@data-place-pagination]')->length)->toBe(1);

    $secondPage = $this->get(route('places.index', [
        'view' => 'list',
        'sort' => 'name',
        'page' => 2,
    ]));

    $secondPage
        ->assertOk()
        ->assertViewHas('places', fn (array $places): bool => count($places['items']) === 6
            && $places['pagination']['current_page'] === 2
            && str_contains((string) $places['pagination']['previous_url'], 'sort=name'));

    expect(responseXPath($secondPage)->query('//*[@data-place-card]')->length)->toBe(6);
});

test('place directory renders reusable responsive regions without a comparison table', function () {
    $response = $this->get(route('places.index'));
    $xpath = responseXPath($response);

    $response
        ->assertOk()
        ->assertSee('data-place-search-form', false)
        ->assertSee('data-place-map', false)
        ->assertSee('data-place-x=', false)
        ->assertSee('data-place-y=', false)
        ->assertSee('data-place-pagination', false)
        ->assertDontSee('style="--marker-', false)
        ->assertDontSee('<table', false);

    expect($xpath->query('//section[contains(concat(" ", normalize-space(@class), " "), " place-search ")]')->length)->toBe(1)
        ->and($xpath->query('//section[contains(concat(" ", normalize-space(@class), " "), " place-directory__controls ")]')->length)->toBe(1)
        ->and($xpath->query('//section[contains(concat(" ", normalize-space(@class), " "), " place-results ")]')->length)->toBe(1)
        ->and($xpath->query('//section[contains(concat(" ", normalize-space(@class), " "), " place-comparison ")]')->length)->toBe(1);
});

test('place filters are allow listed and deterministic', function () {
    $response = $this->get(route('places.index', [
        'view' => 'list',
        'category' => 'emergency-vet',
        'species' => 'bird',
        'sort' => 'distance',
    ]));

    $response
        ->assertOk()
        ->assertViewHas('places', fn (array $places): bool => array_column($places['items'], 'key') === [
            'paws-24-veterinary-center',
        ])
        ->assertSee('Paws 24 Veterinary Center')
        ->assertDontSee('Night Paw Clinic');

    $this->from(route('places.index'))
        ->get(route('places.index', ['category' => 'not-a-category']))
        ->assertRedirect(route('places.index'))
        ->assertSessionHasErrors('category');
});

test('place details expose provenance freshness services safety and community review surfaces', function () {
    $response = $this->get(route('places.show', [
        'place' => 'paws-24-veterinary-center',
        'tab' => 'corrections',
    ]));

    $response
        ->assertOk()
        ->assertViewHas('place', fn (array $place): bool => isset(
            $place['verification']['scope'],
            $place['verification']['updated_at'],
            $place['data_freshness'],
            $place['services'],
            $place['rules'],
            $place['accessibility'],
        ))
        ->assertViewHas('content', fn (array $content): bool => array_key_exists('warnings', $content)
            && array_key_exists('reviews', $content)
            && array_key_exists('questions', $content)
            && array_key_exists('history', $content)
            && array_key_exists('verification', $content)
            && isset($content['gallery'][0]['source']))
        ->assertViewHas('corrections', fn (array $corrections): bool => $corrections === [])
        ->assertSee('Correction status')
        ->assertSee('Verified scope')
        ->assertSee('Important changes require evidence and review');

    foreach (['services', 'rules', 'reviews', 'questions', 'updates'] as $tab) {
        $this->get(route('places.show', [
            'place' => 'paws-24-veterinary-center',
            'tab' => $tab,
        ]))->assertOk();
    }
});

test('place correction and warning history retains evidence and source records', function () {
    $this->get(route('places.index'))->assertOk();

    $state = app(PlaceState::class);
    $state->addCorrection('paws-24-veterinary-center', [
        'field' => 'hours',
        'current_value' => 'Open all day',
        'proposed_value' => 'Call before arrival overnight',
        'evidence' => 'Dated entrance notice',
        'visited_at' => '2026-07-30',
        'source' => 'personal-visit',
    ]);
    $warning = $state->addWarning('paws-24-veterinary-center', [
        'title' => 'Temporary entrance closure',
        'detail' => 'Use the north entrance.',
    ]);

    expect($state->corrections('paws-24-veterinary-center')[0])
        ->toMatchArray([
            'evidence' => 'Dated entrance notice',
            'source' => 'personal-visit',
            'status' => 'submitted',
        ])
        ->and($state->warnings('paws-24-veterinary-center')[0])
        ->toMatchArray([
            'key' => $warning['key'],
            'source' => 'Community report awaiting review',
            'status' => 'new',
        ])
        ->and($state->history('paws-24-veterinary-center'))
        ->toHaveCount(2);

    $detail = app(PlacePresenter::class)->detail('paws-24-veterinary-center', 'updates');

    expect($detail)->not->toBeNull()
        ->and($detail['content']['history'])->toHaveCount(3);
});

test('emergency veterinary mode requires open species capable clinics and gives direct guarded actions', function () {
    $response = $this->get(route('places.index', [
        'emergency' => 1,
        'species' => 'bird',
        'view' => 'list',
    ]));

    $response
        ->assertOk()
        ->assertViewHas('places', fn (array $places): bool => $places['emergency'] === true
            && array_column($places['items'], 'key') === ['paws-24-veterinary-center']
            && $places['items'][0]['call_url'] !== null
            && $places['items'][0]['route_url'] !== '')
        ->assertSee('Call before travel')
        ->assertSee('not guaranteed')
        ->assertSee('href="tel:', false)
        ->assertSee('openstreetmap.org/directions', false)
        ->assertDontSee('Night Paw Clinic');
});

test('unknown place details return not found', function () {
    $this->get('/places/unknown-place')->assertNotFound();
});
