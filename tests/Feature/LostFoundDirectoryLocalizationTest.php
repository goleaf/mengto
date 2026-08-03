<?php

declare(strict_types=1);

use App\Models\SearchCase;
use Illuminate\Support\Facades\File;

const LOST_FOUND_DIRECTORY_UI_KEYS = [
    'active_9234069589',
    'active_local_searches_a0b657fac3',
    'animal_3f257e684a',
    'any_status_ac78229d6b',
    'apply_31e392d1c0',
    'area_024dc204d7',
    'city_fc33f73246',
    'clear_filters_7179ea0035',
    'every_species_5c8dedc378',
    'found_b0ee315f4a',
    'generalized_locations_f8a7558000',
    'last_seen_21fd79c7de',
    'lost_found_217c655848',
    'map_points_in_text_form_591995251f',
    'missing_6be36ca49e',
    'missing_and_found_8b1afe085a',
    'name_color_area_or_code_e3a84ea921',
    'newest_verified_activity_first_280c662fa5',
    'no_locations_available_c1f36516a2',
    'no_matching_reports_0867fe8beb',
    'no_public_map_points_match_these_filters_2df593b49d',
    'no_report_types_5701e2167c',
    'no_sort_options_dd1d70e52f',
    'no_species_available_5b8f473ec2',
    'no_statuses_b34efdc994',
    'open_the_matching_card_and_send_the_actual_5f3fd1d897',
    'report_a_sighting_join_a_coordinated_task_or_e5e0bbe8c2',
    'report_an_animal_6188a5d89e',
    'report_type_8c9986f3ba',
    'search_49c266baaa',
    'search_activity_summary_b377289d94',
    'search_map_c802781b2d',
    'search_reports_70565872e3',
    'search_statistics_are_unavailable_94eb5a5737',
    'see_an_animal_d106478c0f',
    'sightings_4906ba1ea4',
    'sort_bec69036aa',
    'status_920e413c7d',
    'try_a_wider_area_or_clear_one_of_c761fc97ac',
    'vilnius_c283e0869a',
    'visible_search_area_b5fd8ec109',
    'volunteers_6ec733ad33',
];

const LOST_FOUND_DIRECTORY_DEFENSIVE_UI_KEYS = [
    'no_report_types_5701e2167c',
    'no_sort_options_dd1d70e52f',
    'no_species_available_5b8f473ec2',
    'no_statuses_b34efdc994',
    'search_map_c802781b2d',
    'search_statistics_are_unavailable_94eb5a5737',
];

const LOST_FOUND_DIRECTORY_EMPTY_UI_KEYS = [
    'no_locations_available_c1f36516a2',
    'no_matching_reports_0867fe8beb',
    'no_public_map_points_match_these_filters_2df593b49d',
    'try_a_wider_area_or_clear_one_of_c761fc97ac',
];

test('the lost and found directory renders its system copy in the authenticated users locale', function (
    string $locale,
): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    SearchCase::factory()->create([
        'pet_name' => 'Scout Locale Test',
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
            if ($locale === 'lt' && $key === 'vilnius_c283e0869a') {
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
