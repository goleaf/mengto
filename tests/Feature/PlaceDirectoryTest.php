<?php

declare(strict_types=1);

use App\Models\Place;
use App\Models\UserDomainState;
use App\Services\PlacePresenter;
use App\Services\PlaceState;
use Database\Seeders\PlaceDemoSeeder;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(PlaceDemoSeeder::class);
});

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

    foreach (['overview', 'services', 'rules', 'specialists', 'reviews', 'questions', 'updates'] as $tab) {
        $this->get(route('places.show', [
            'place' => 'paws-24-veterinary-center',
            'tab' => $tab,
        ]))
            ->assertOk()
            ->assertDontSee('prototype', false)
            ->assertDontSee('Prototype', false);
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

test('place demo catalog is database backed and repeatable', function () {
    expect(Place::query()->publiclyDiscoverable()->count())->toBe(12);

    $this->seed(PlaceDemoSeeder::class);

    expect(Place::query()->publiclyDiscoverable()->count())->toBe(12);

    Place::query()
        ->where('stable_key', 'vingis-quiet-loop')
        ->firstOrFail()
        ->delete();

    $this->get(route('places.index', ['q' => 'Vingis Park']))
        ->assertOk()
        ->assertViewHas('places', fn (array $places): bool => $places['pagination']['total'] === 0)
        ->assertDontSee('Vingis Park quiet loop');
});

test('the legacy place composer redirects to the reviewed submission workflow', function () {
    $this->post(route('actions.perform'), [
        'action' => 'create-place',
        'title' => 'Riverside safety park',
        'body' => 'A public park with water and a clearly marked quiet walking loop.',
        'category' => 'park',
        'city' => 'Vilnius Riverside',
        'place_address' => 'Public entrance, River Street 10',
        'rules' => 'Leashes are required beside the cycle path.',
        'place_relationship' => 'visitor',
    ])->assertRedirect(route('places.submissions.create'));

    expect(Place::query()->where('name', 'Riverside safety park')->exists())->toBeFalse();

    $this->get(route('compose', ['kind' => 'place']))
        ->assertRedirect(route('places.submissions.create'));
});

test('private and archived places never enter the public directory', function () {
    Place::factory()->private()->create(['name' => 'Hidden foster handoff']);
    Place::factory()->archived()->create(['name' => 'Closed training field']);

    $this->get(route('places.index', ['view' => 'list']))
        ->assertOk()
        ->assertViewHas('places', fn (array $places): bool => $places['pagination']['total'] === 12)
        ->assertDontSee('Hidden foster handoff')
        ->assertDontSee('Closed training field');
});

test('place visit controls persist and render through the shared action boundary', function () {
    $place = 'vingis-quiet-loop';

    foreach ([
        ['action' => 'toggle-place-save', 'target' => $place],
        ['action' => 'toggle-place-follow', 'target' => $place],
        ['action' => 'mark-place-visited', 'target' => $place, 'place_pet' => 'scout'],
        [
            'action' => 'check-in-place',
            'target' => $place,
            'place_pet' => 'scout',
            'place_visibility' => 'private',
        ],
    ] as $payload) {
        $this->post(route('actions.perform'), $payload)
            ->assertRedirect(route('places.show', [
                'place' => $place,
                'tab' => 'overview',
            ]));
    }

    expect(UserDomainState::query()
        ->whereBelongsTo($this->authenticatedUser)
        ->where('namespace', 'places.state.v1')
        ->exists())->toBeTrue();

    $this->get(route('places.show', ['place' => $place]))
        ->assertOk()
        ->assertViewHas('place', fn (array $presented): bool => $presented['saved'] === true
            && $presented['followed'] === true
            && $presented['visited'] === true)
        ->assertViewHas('check_in', fn (?array $checkIn): bool => $checkIn !== null
            && $checkIn['pet'] === 'scout'
            && $checkIn['visibility'] === 'private')
        ->assertSee('Check-in active')
        ->assertSee('Visit saved');
});

test('community place contributions survive redirects and appear on their detail tabs', function () {
    $place = 'vingis-quiet-loop';

    $this->post(route('actions.perform'), [
        'action' => 'create-place-correction',
        'target' => $place,
        'place_field' => 'hours',
        'place_current_value' => 'Open all day',
        'body' => 'The west gate closes during maintenance.',
        'place_evidence' => 'Dated notice photographed at the west gate.',
        'place_source' => 'personal-visit',
        'place_visit_date' => '2026-08-02',
    ])->assertRedirect(route('places.show', [
        'place' => $place,
        'tab' => 'corrections',
    ]));

    $this->post(route('actions.perform'), [
        'action' => 'create-place-warning',
        'target' => $place,
        'title' => 'West gate maintenance',
        'category' => 'road-closure',
        'body' => 'Use the eastern public entrance until repairs finish.',
        'place_zone' => 'West gate',
    ])->assertRedirect(route('places.show', [
        'place' => $place,
        'tab' => 'updates',
    ]));

    $this->post(route('actions.perform'), [
        'action' => 'create-place-review',
        'target' => $place,
        'place_rating' => 5,
        'place_pet' => 'scout',
        'place_review_criterion' => 'safety',
        'place_anonymous' => 'no',
        'body' => 'The quiet loop was clearly marked and easy to leave early.',
    ])->assertRedirect(route('places.show', [
        'place' => $place,
        'tab' => 'reviews',
    ]));

    $this->post(route('actions.perform'), [
        'action' => 'create-place-question',
        'target' => $place,
        'body' => 'Is the west entrance open after maintenance?',
        'place_idempotency_key' => (string) Str::uuid(),
    ])->assertRedirect(route('places.show', [
        'place' => $place,
        'tab' => 'questions',
    ]));

    $this->get(route('places.show', ['place' => $place, 'tab' => 'corrections']))
        ->assertOk()
        ->assertSee('The west gate closes during maintenance.')
        ->assertSee('Dated notice photographed at the west gate.');

    $this->get(route('places.show', ['place' => $place, 'tab' => 'updates']))
        ->assertOk()
        ->assertSee('West gate maintenance')
        ->assertSee('Use the eastern public entrance until repairs finish.');

    $this->get(route('places.show', ['place' => $place, 'tab' => 'reviews']))
        ->assertOk()
        ->assertSee('The quiet loop was clearly marked and easy to leave early.');

    $this->get(route('places.show', ['place' => $place, 'tab' => 'questions']))
        ->assertOk()
        ->assertSee('Is the west entrance open after maintenance?');
});

test('place directory query count stays constant as the catalog grows', function () {
    $queries = [];
    DB::listen(static function (QueryExecuted $query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $this->get(route('places.index', ['view' => 'list']))->assertOk();
    $baseline = count($queries);

    Place::factory()
        ->count(30)
        ->public()
        ->create();
    $queries = [];

    $this->get(route('places.index', ['view' => 'list']))->assertOk();

    expect(count($queries))->toBeLessThanOrEqual($baseline + 1);
});
