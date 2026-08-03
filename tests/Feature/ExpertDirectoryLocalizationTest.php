<?php

declare(strict_types=1);

use App\Enums\ExpertProfileStatus;
use App\Models\ExpertProfile;
use App\Services\ExpertTaxonomy;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

const EXPERT_DIRECTORY_UI_KEYS = [
    'a_verification_badge_explains_what_was_checked_it_60a615c5a3',
    'all_areas_e278d7e38e',
    'all_specialist_types_49963b2ce1',
    'any_availability_372ccd0787',
    'any_format_f676243c0e',
    'any_language_fb3b2acda0',
    'apply_31e392d1c0',
    'ask_for_price_98fd0280eb',
    'availability_12f67f8539',
    'book_909cb81127',
    'by_request_6abaa6de2b',
    'city_fc33f73246',
    'clear_filters_7179ea0035',
    'client_rating_9bc6657b50',
    'compare_scope_species_independently_checked_credentials_availability_lan_8ad672b4c3',
    'create_professional_profile_30276b75d3',
    'directory_statistics_are_not_available_yet_85714e0ba6',
    'every_species_5c8dedc378',
    'expert_directory_summary_c16800d8b6',
    'find_the_right_specialist_for_this_pet_21bb34d7d0',
    'format_2f343666aa',
    'from_2181976934',
    'language_a4fe65264e',
    'matches_the_current_directory_f43672c613',
    'matching_professionals_90981c4569',
    'name_skill_city_or_approach_b76092e837',
    'need_urgent_veterinary_help_6caf36da92',
    'new_profile_fcf4f3f4d5',
    'next_time_651c943284',
    'no_availability_options_9f0f7c913f',
    'no_exact_match_yet_85432de381',
    'no_formats_56383a7a25',
    'no_languages_4f66d9089f',
    'no_sorting_options_16178b569d',
    'no_specialist_types_47f1a88292',
    'no_specializations_724014462a',
    'no_species_options_12dedb4a25',
    'price_93c91c851e',
    'problem_a1c5ae7bcb',
    'professional_workspace_eb8eb6dde6',
    'qualification_verified_bfd453f9ac',
    'remove_one_filter_try_a_nearby_city_or_d6f942f12c',
    'scope_details_pending_4f7b588ab8',
    'search_49c266baaa',
    'show_only_profiles_with_a_checked_professional_qualification_f3531a6cd2',
    'sort_bec69036aa',
    'specialist_8302f971b5',
    'specializations_b2561b50e1',
    'species_56205e12c2',
    'verified_professional_community_f3f93b61ff',
    'verified_reviews_dd3744117b',
    'view_profile_d4788f256f',
    'vilnius_c283e0869a',
    'why_this_profile_matches_8f43aa2b22',
];

const EXPERT_DIRECTORY_STATS_MESSAGE_KEYS = [
    'accepting_clients_34310f30b5',
    'published_profiles_948161d31f',
    'qualification_verified_bfd453f9ac',
    'qualifications_verified_a49aaeb0ef',
    'species_covered_acf9471e40',
];

const EXPERT_DIRECTORY_RENDERED_UI_KEYS = [
    'a_verification_badge_explains_what_was_checked_it_60a615c5a3',
    'all_areas_e278d7e38e',
    'all_specialist_types_49963b2ce1',
    'any_availability_372ccd0787',
    'any_format_f676243c0e',
    'any_language_fb3b2acda0',
    'apply_31e392d1c0',
    'availability_12f67f8539',
    'book_909cb81127',
    'city_fc33f73246',
    'client_rating_9bc6657b50',
    'compare_scope_species_independently_checked_credentials_availability_lan_8ad672b4c3',
    'create_professional_profile_30276b75d3',
    'every_species_5c8dedc378',
    'expert_directory_summary_c16800d8b6',
    'find_the_right_specialist_for_this_pet_21bb34d7d0',
    'format_2f343666aa',
    'from_2181976934',
    'language_a4fe65264e',
    'matching_professionals_90981c4569',
    'name_skill_city_or_approach_b76092e837',
    'need_urgent_veterinary_help_6caf36da92',
    'new_profile_fcf4f3f4d5',
    'next_time_651c943284',
    'price_93c91c851e',
    'problem_a1c5ae7bcb',
    'professional_workspace_eb8eb6dde6',
    'qualification_verified_bfd453f9ac',
    'search_49c266baaa',
    'show_only_profiles_with_a_checked_professional_qualification_f3531a6cd2',
    'sort_bec69036aa',
    'specialist_8302f971b5',
    'specializations_b2561b50e1',
    'species_56205e12c2',
    'verified_professional_community_f3f93b61ff',
    'verified_reviews_dd3744117b',
    'view_profile_d4788f256f',
    'why_this_profile_matches_8f43aa2b22',
];

test('the expert directory renders localized system and taxonomy copy', function (string $locale): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    ExpertProfile::factory()->create([
        'public_name' => 'Expert Locale Test',
        'avatar_url' => null,
        'primary_type' => 'avian-veterinarian',
        'species' => ['bird'],
        'specializations' => ['avian-medicine'],
        'languages' => ['Lithuanian'],
        'formats' => ['video'],
    ]);

    $response = $this->get(route('experts.index'))->assertOk();

    foreach (EXPERT_DIRECTORY_RENDERED_UI_KEYS as $key) {
        $response->assertSee(trans("ui.{$key}", locale: $locale));
    }

    foreach (EXPERT_DIRECTORY_STATS_MESSAGE_KEYS as $key) {
        $response->assertSee(trans("messages.{$key}", locale: $locale));
    }

    foreach (['types.avian-veterinarian', 'species.bird', 'specializations.avian-medicine', 'formats.video', 'languages.Lithuanian'] as $key) {
        $response->assertSee(trans("experts.{$key}", locale: $locale));
    }

    expect(responseXPath($response)->query('//*[@data-expert-card]')->length)->toBe(1);
})->with(['lt', 'ru']);

test('the expert empty result follows the authenticated users locale', function (string $locale): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    $response = $this->get(route('experts.index', ['q' => 'no-expert-locale-match']))
        ->assertOk();

    foreach (['no_exact_match_yet_85432de381', 'remove_one_filter_try_a_nearby_city_or_d6f942f12c'] as $key) {
        $response->assertSee(trans("ui.{$key}", locale: $locale));
    }
})->with(['lt', 'ru']);

test('the expert domain has a complete shared localization contract', function (): void {
    foreach (['en', 'lt', 'ru'] as $locale) {
        expect(File::exists(lang_path("{$locale}/experts.php")), $locale)->toBeTrue();
    }

    $english = Arr::dot(require lang_path('en/experts.php'));

    expect($english)->toHaveCount(80);

    foreach (['lt', 'ru'] as $locale) {
        $localized = Arr::dot(require lang_path("{$locale}/experts.php"));

        expect(array_keys($localized), $locale)->toBe(array_keys($english));

        foreach ($english as $key => $value) {
            expect($localized[$key], "{$locale}.experts.{$key}")
                ->not->toBe($value);
        }
    }

    foreach (EXPERT_DIRECTORY_UI_KEYS as $key) {
        foreach (['lt', 'ru'] as $locale) {
            if ($locale === 'lt' && $key === 'vilnius_c283e0869a') {
                continue;
            }

            expect(trans("ui.{$key}", locale: $locale), "{$locale}.ui.{$key}")
                ->not->toBe(trans("ui.{$key}", locale: 'en'));
        }
    }

    foreach (EXPERT_DIRECTORY_STATS_MESSAGE_KEYS as $key) {
        foreach (['lt', 'ru'] as $locale) {
            expect(trans("messages.{$key}", locale: $locale), "{$locale}.messages.{$key}")
                ->not->toBe(trans("messages.{$key}", locale: 'en'));
        }
    }
});

test('shared expert labels resolve from the active locale', function (string $locale): void {
    app()->setLocale($locale);
    $taxonomy = app(ExpertTaxonomy::class);

    expect(ExpertProfileStatus::Pending->label())->toBe(trans('experts.profile_statuses.pending'))
        ->and($taxonomy->types()['avian-veterinarian'])->toBe(trans('experts.types.avian-veterinarian'))
        ->and($taxonomy->species()['bird'])->toBe(trans('experts.species.bird'))
        ->and($taxonomy->specializations()['avian-medicine'])->toBe(trans('experts.specializations.avian-medicine'))
        ->and($taxonomy->formats()['video'])->toBe(trans('experts.formats.video'))
        ->and($taxonomy->languages()['Lithuanian'])->toBe(trans('experts.languages.Lithuanian'))
        ->and($taxonomy->availability()['today'])->toBe(trans('experts.availability.today'))
        ->and($taxonomy->sortOptions()['rating'])->toBe(trans('experts.sort_options.rating'))
        ->and($taxonomy->pets()['scout'])->toBe(trans('experts.pets.scout'));
})->with(['lt', 'ru']);

test('the expert directory query count stays bounded as profiles grow', function (): void {
    ExpertProfile::factory()->create();

    $queries = [];
    DB::listen(static function (QueryExecuted $query) use (&$queries): void {
        $queries[] = $query;
    });

    $this->get(route('experts.index'))->assertOk();
    $singleProfileQueryCount = count($queries);

    ExpertProfile::factory()->count(15)->create();
    $queries = [];

    $this->get(route('experts.index'))->assertOk();

    expect($singleProfileQueryCount)->toBe(6)
        ->and(count($queries))->toBe(6);
});

test('the browser matrix rejects expert body fallbacks', function (): void {
    $browser = File::get(base_path('scripts/accessibility-browser-check.mjs'));
    $directory = File::get(resource_path('views/experts/index.blade.php'));

    expect($browser)
        ->toContain(
            'englishExpertCopy',
            'expertCopy.length === 37',
            'English expert body fallback remains.',
        )
        ->and($directory)
        ->toContain('data-expert-stats', 'data-expert-stat', 'data-expert-filters');
});
