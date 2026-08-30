<?php

declare(strict_types=1);

use App\Services\NeighborProfilePresenter;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

const NEIGHBOR_PROFILE_IDENTITY_KEYS = [
    'profile.identity.name',
    'profile.identity.handle',
    'profile.identity.neighborhood',
    'profile.pet.name',
    'profile.pet.owner_name',
    'profile.mutual_neighbors.mia.name',
    'profile.mutual_neighbors.jamie.name',
    'profile.mutual_neighbors.noah.name',
    'profile.mutual_neighbors.lena.name',
    'profile.communities.apartment_pets.name',
    'profile.communities.trail_tails.name',
    'profile.pet.routine.route_value',
    'profile.moments.first.author',
    'profile.moments.first.pet',
    'profile.moments.second.author',
    'profile.moments.second.pet',
];

test('the neighbor profile contract is complete in every supported locale', function (): void {
    $english = Arr::dot(require lang_path('en/neighbors.php'));

    expect($english)->toHaveKeys([
        'profile.page.title',
        'profile.page.back',
        'profile.page.actions_label',
        'profile.hero.summary_label',
        'profile.hero.summary_unavailable',
        'profile.sections.about.eyebrow',
        'profile.sections.about.title',
        'profile.sections.interests.title',
        'profile.sections.interests.empty',
        'profile.sections.mutual_neighbors.title',
        'profile.sections.mutual_neighbors.count',
        'profile.sections.mutual_neighbors.empty',
        'profile.sections.communities.title',
        'profile.sections.communities.empty',
        'profile.sections.moments.eyebrow',
        'profile.sections.moments.title',
        'profile.sections.moments.empty',
        'profile.actions.follow',
        'profile.actions.following',
        'profile.actions.message',
        'profile.actions.plan_walk',
        'profile.identity.bio',
        'profile.pet.status',
        'profile.pet.routine.route_label',
        'profile.pet.routine.time_label',
        'profile.pet.routine.cafe_label',
        'profile.moments.first.body',
        'profile.moments.second.body',
    ]);

    foreach (['lt', 'ru'] as $locale) {
        $localized = Arr::dot(require lang_path("{$locale}/neighbors.php"));

        expect(array_keys($localized), $locale)->toBe(array_keys($english));

        foreach (Arr::where(
            $localized,
            static fn (mixed $value, string $key): bool => str_starts_with($key, 'profile.')
                && is_string($value)
                && ! in_array($key, NEIGHBOR_PROFILE_IDENTITY_KEYS, true),
        ) as $key => $value) {
            expect($value, "{$locale}.neighbors.{$key}")
                ->not->toBe('')
                ->not->toBe($english[$key]);
        }
    }
});

test('the neighbor profile renders its localized domain contract', function (string $locale): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    $copy = require lang_path("{$locale}/neighbors.php");
    $response = $this->get(route('neighbors.ari'))->assertOk();

    foreach ([
        data_get($copy, 'profile.page.back'),
        data_get($copy, 'profile.hero.summary_label'),
        data_get($copy, 'profile.sections.about.eyebrow'),
        data_get($copy, 'profile.sections.interests.title'),
        data_get($copy, 'profile.sections.mutual_neighbors.title'),
        data_get($copy, 'profile.sections.communities.title'),
        data_get($copy, 'profile.sections.moments.eyebrow'),
        data_get($copy, 'profile.actions.plan_walk'),
        data_get($copy, 'profile.identity.bio'),
        data_get($copy, 'profile.identity.avatar_alt'),
        data_get($copy, 'profile.identity.cover_image_alt'),
        data_get($copy, 'profile.pet.status'),
        data_get($copy, 'profile.moments.second.body'),
    ] as $value) {
        $response->assertSee((string) $value);
    }

    $xpath = responseXPath($response);

    expect($xpath->query('//*[@data-neighbor-profile]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-neighbor-profile-hero]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-neighbor-profile-pet]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-neighbor-profile-mutuals]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-neighbor-profile-communities]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-neighbor-profile-moments]')->length)->toBe(1);
})->with(['en', 'lt', 'ru']);

test('the neighbor profile uses one canonical lucide icon language', function (): void {
    $xpath = responseXPath($this->get(route('neighbors.ari'))->assertOk());

    expect($xpath->query('//*[@data-neighbor-profile]//*[@data-ui-icon]')->length)->toBeGreaterThanOrEqual(16)
        ->and($xpath->query('//*[@data-neighbor-profile-pet-meta]//*[@data-ui-icon]')->length)->toBe(2)
        ->and($xpath->query('//*[@data-neighbor-profile-routine]//*[@data-ui-icon]')->length)->toBe(3)
        ->and(array_map(
            static fn (DOMNode $node): string => (string) $node->attributes?->getNamedItem('data-ui-icon')?->nodeValue,
            iterator_to_array($xpath->query('//*[@data-neighbor-profile-routine]//*[@data-ui-icon]')),
        ))->toBe(['route', 'sunrise', 'coffee']);
});

test('the neighbor profile has a dedicated presenter and passive blade boundary', function (): void {
    $presenterPath = app_path('Services/NeighborProfilePresenter.php');

    expect(File::exists($presenterPath))->toBeTrue();

    $previewSource = File::get(app_path('Services/PreviewService.php'));
    preg_match(
        '/public function ariNeighborProfileData\(\): array(?<body>.*?)(?=public function notificationCenterData)/s',
        $previewSource,
        $profileMethod,
    );

    expect($profileMethod['body'] ?? '')
        ->not->toBe('')
        ->toContain('neighborProfile->present')
        ->not->toContain("__('ui.", "__('messages.", "__('presentation.");

    foreach ([
        resource_path('views/neighbors/show.blade.php'),
        resource_path('views/components/neighbor-pet-summary.blade.php'),
        resource_path('views/components/mutual-neighbor-list.blade.php'),
    ] as $path) {
        expect(File::get($path), $path)
            ->not->toContain("__('ui.", "__('messages.", "__('presentation.", "route('actions.perform')")
            ->toContain('$copy');
    }

    expect(File::get(base_path('scripts/accessibility-browser-check.mjs')))
        ->toContain(
            'englishNeighborProfileCopy',
            'neighborProfileCopy.length === 58',
            'English neighbor profile body fallback remains.',
            'page-identity-neighbor-profile-',
        );
});

test('the neighbor profile presenter performs no database queries', function (): void {
    $queries = [];
    DB::listen(static function (QueryExecuted $query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    app(NeighborProfilePresenter::class)->present(
        owner: [
            'name' => 'Test owner',
            'location' => 'Test location',
            'avatar' => '/test-owner.jpg',
            'summary' => 'Test summary',
        ],
        recentMoments: [],
        followed: false,
    );

    expect($queries)->toBe([]);
});
