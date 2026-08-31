<?php

declare(strict_types=1);

use App\Models\SearchCase;
use Illuminate\Support\Facades\File;

const LOST_FOUND_DIRECTORY_UI_KEYS = [
    'active',
    'active_local_searches',
    'animal',
    'any_status',
    'apply',
    'area',
    'city',
    'clear_filters',
    'every_species',
    'found',
    'generalized_locations',
    'last_seen',
    'lost_found',
    'map_points_in_text_form',
    'missing',
    'missing_and_found',
    'name_color_area_or_code',
    'newest_verified_activity_first',
    'no_locations_available',
    'no_matching_reports',
    'no_public_map_points_match_these_filters',
    'no_report_types',
    'no_sort_options',
    'no_species_available',
    'no_statuses',
    'open_the_matching_card_and_send_the_actual_observation_time_general_area_direction_and_a_photo_when_it_is_safe',
    'report_a_sighting_join_a_coordinated_task_or_help_a_found_animal_reach_a_verified_owner_without_exposing_private_addresses',
    'report_an_animal',
    'report_type',
    'search',
    'search_activity_summary',
    'search_map',
    'search_reports',
    'search_statistics_are_unavailable',
    'see_an_animal',
    'sightings',
    'sort',
    'status',
    'try_a_wider_area_or_clear_one_of_the_filters',
    'vilnius',
    'visible_search_area',
    'volunteers',
];

const LOST_FOUND_DIRECTORY_DEFENSIVE_UI_KEYS = [
    'no_report_types',
    'no_sort_options',
    'no_species_available',
    'no_statuses',
    'search_map',
    'search_statistics_are_unavailable',
];

const LOST_FOUND_DIRECTORY_EMPTY_UI_KEYS = [
    'no_locations_available',
    'no_matching_reports',
    'no_public_map_points_match_these_filters',
    'try_a_wider_area_or_clear_one_of_the_filters',
];

test('the lost and found directory renders its system copy in the authenticated users locale', function (
    string $locale,
): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    SearchCase::factory()->create([
        'pet_name' => 'Birch Locale Test',
        'species' => 'dog',
        'cover_url' => null,
    ]);

    $response = $this->get(route('lost-found.index'))->assertOk();

    foreach (array_diff(
        LOST_FOUND_DIRECTORY_UI_KEYS,
        LOST_FOUND_DIRECTORY_DEFENSIVE_UI_KEYS,
        LOST_FOUND_DIRECTORY_EMPTY_UI_KEYS,
    ) as $key) {
        $response->assertSee(trans("ui.{$key}", locale: $locale));
    }

    $response
        ->assertSee(trans('lost_found.type.lost', locale: $locale))
        ->assertSee(trans('lost_found.status.active', locale: $locale))
        ->assertSee(trans('lost_found.species.dog', locale: $locale));

    $xpath = responseXPath($response);
    $stats = $xpath->query('//*[@data-lost-found-stat]');
    $lastStat = $stats->item($stats->length - 1);

    expect($stats->length)->toBe(5)
        ->and($lastStat?->attributes?->getNamedItem('class')?->nodeValue)
        ->toContain('col-span-2', 'md:col-span-1')
        ->and($xpath->query('//*[@data-search-case-card]')->length)->toBe(1);
})->with(['lt', 'ru']);

test('the lost and found empty results and map states follow the authenticated users locale', function (
    string $locale,
): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    $response = $this->get(route('lost-found.index', ['q' => 'no-match-locale-test']))
        ->assertOk();

    foreach (LOST_FOUND_DIRECTORY_EMPTY_UI_KEYS as $key) {
        $response->assertSee(trans("ui.{$key}", locale: $locale));
    }
})->with(['lt', 'ru']);

test('the lost and found directory has complete non english system copy', function (): void {
    foreach (['lt', 'ru'] as $locale) {
        foreach (LOST_FOUND_DIRECTORY_UI_KEYS as $key) {
            if ($locale === 'lt' && $key === 'vilnius') {
                continue;
            }

            expect(trans("ui.{$key}", locale: $locale), "{$locale}.ui.{$key}")
                ->not->toBe(trans("ui.{$key}", locale: 'en'));
        }
    }

    expect(trans_choice('presentation.sightings_count', 1, ['count' => 1], 'lt'))
        ->toBe('1 pastebėjimas')
        ->and(trans_choice('presentation.sightings_count', 2, ['count' => 2], 'lt'))
        ->toBe('2 pastebėjimai')
        ->and(trans_choice('presentation.tasks_count', 1, ['count' => 1], 'lt'))
        ->toBe('1 užduotis')
        ->and(trans_choice('presentation.tasks_count', 2, ['count' => 2], 'lt'))
        ->toBe('2 užduotys');
});

test('the browser matrix rejects lost and found body fallbacks and the empty mobile stat cell', function (): void {
    $browser = File::get(base_path('scripts/accessibility-browser-check.mjs'));
    $directory = File::get(resource_path('views/lost-found/index.blade.php'));

    expect($browser)
        ->toContain(
            'englishLostFoundCopy',
            'lostFoundCopy.length === 34',
            'English lost-and-found body fallback remains.',
            'the final search statistic leaves an empty mobile grid cell.',
        )
        ->and($directory)
        ->toContain(
            'data-lost-found-stats',
            'data-lost-found-stat',
            "'col-span-2 md:col-span-1' => \$loop->last",
        );
});
