<?php

declare(strict_types=1);

use AppModelsPlace;
use AppModelsUser;
use AppServicesPlaceIdentityNormalizer;
use IlluminateDatabaseEloquentFactoriesSequence;

test('the complete visible place result remains reachable beyond six hundred rows', function (): void {
    createScalableDirectoryPlaces($this->authenticatedUser, 613);

    $privateOwner = User::factory()->create();
    Place::factory()->for($privateOwner, 'owner')->private()->create([
        'stable_key' => 'scale-directory-private-sentinel',
        'slug' => 'scale-directory-private-sentinel',
        'name' => 'Scale directory private sentinel',
        'normalized_name' => 'scale directory private sentinel',
    ]);

    $pageWithinFormerCatalogCeiling = $this->get(route('places.index', [
        'q' => 'Scale directory',
        'sort' => 'name',
        'view' => 'list',
        'page' => 84,
    ]));

    $pageWithinFormerCatalogCeiling
        ->assertOk()
        ->assertViewHas('places', static fn (array $places): bool => $places['pagination']['total'] === 613
            && $places['pagination']['last_page'] === 103
            && count($places['items']) === 6);

    $pageBeyondFormerValidatorCeiling = $this->get(route('places.index', [
        'q' => 'Scale directory',
        'sort' => 'name',
        'view' => 'list',
        'page' => 101,
    ]));

    $pageBeyondFormerValidatorCeiling
        ->assertOk()
        ->assertViewHas('places', static fn (array $places): bool => $places['pagination']['current_page'] === 101
            && $places['pagination']['total'] === 613
            && array_column($places['items'], 'key') === [
                'scale-directory-place-0601',
                'scale-directory-place-0602',
                'scale-directory-place-0603',
                'scale-directory-place-0604',
                'scale-directory-place-0605',
                'scale-directory-place-0606',
            ])
        ->assertDontSee('Scale directory private sentinel');
});

test('directory pagination uses an immutable secondary order for tied names', function (): void {
    $normalizer = app(PlaceIdentityNormalizer::class);

    Place::factory()
        ->count(14)
        ->for($this->authenticatedUser, 'owner')
        ->sequence(static fn (Sequence $sequence): array => [
            'stable_key' => sprintf('tied-directory-place-%02d', $sequence->index + 1),
            'slug' => sprintf('tied-directory-place-%02d', $sequence->index + 1),
            'creation_idempotency_key' => sprintf('tied-directory-place-create-%02d', $sequence->index + 1),
            'name' => 'Tied directory place',
            'normalized_name' => $normalizer->name('Tied directory place'),
        ])
        ->public()
        ->create();

    $pageOne = $this->get(route('places.index', [
        'q' => 'Tied directory',
        'sort' => 'name',
        'view' => 'list',
    ]));
    $pageTwo = $this->get(route('places.index', [
        'q' => 'Tied directory',
        'sort' => 'name',
        'view' => 'list',
        'page' => 2,
    ]));
    $repeatedPageOne = $this->get(route('places.index', [
        'q' => 'Tied directory',
        'sort' => 'name',
        'view' => 'list',
    ]));

    $expectedFirstPage = [
        'tied-directory-place-01',
        'tied-directory-place-02',
        'tied-directory-place-03',
        'tied-directory-place-04',
        'tied-directory-place-05',
        'tied-directory-place-06',
    ];

    $pageOne->assertOk()->assertViewHas(
        'places',
        static fn (array $places): bool => array_column($places['items'], 'key') === $expectedFirstPage,
    );
    $pageTwo->assertOk()->assertViewHas(
        'places',
        static fn (array $places): bool => array_column($places['items'], 'key') === [
            'tied-directory-place-07',
            'tied-directory-place-08',
            'tied-directory-place-09',
            'tied-directory-place-10',
            'tied-directory-place-11',
            'tied-directory-place-12',
        ],
    );
    $repeatedPageOne->assertOk()->assertViewHas(
        'places',
        static fn (array $places): bool => array_column($places['items'], 'key') === $expectedFirstPage,
    );
});

function createScalableDirectoryPlaces(User $owner, int $count): void
{
    $normalizer = app(PlaceIdentityNormalizer::class);

    Place::factory()
        ->count($count)
        ->for($owner, 'owner')
        ->sequence(static function (Sequence $sequence) use ($normalizer): array {
            $position = $sequence->index + 1;
            $key = sprintf('scale-directory-place-%04d', $position);
            $name = sprintf('Scale directory place %04d', $position);

            return [
                'stable_key' => $key,
                'slug' => $key,
                'creation_idempotency_key' => 'create-'.$key,
                'name' => $name,
                'normalized_name' => $normalizer->name($name),
            ];
        })
        ->public()
        ->create();
}
