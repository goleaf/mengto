<?php

declare(strict_types=1);

use App\Enums\ExpertProfileStatus;
use App\Models\ExpertProfile;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Services\ExpertTaxonomy;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

const EXPERT_DIRECTORY_UI_KEYS = [
    'a_verification_badge_explains_what_was_checked_it_is_never_a_guarantee_of_outcome',
    'all_areas',
    'all_specialist_types',
    'any_availability',
    'any_format',
    'any_language',
    'apply',
    'ask_for_price',
    'availability',
    'book',
    'by_request',
    'city',
    'clear_filters',
    'client_rating',
    'compare_scope_species_independently_checked_credentials_availability_language_and_price_before_sharing_any_private_pet_information',
    'create_professional_profile',
    'directory_statistics_are_not_available_yet',
    'every_species',
    'expert_directory_summary',
    'find_the_right_specialist_for_this_pet',
    'format',
    'from',
    'language',
    'matches_the_current_directory',
    'matching_professionals',
    'name_skill_city_or_approach',
    'need_urgent_veterinary_help',
    'new_profile',
    'next_time',
    'no_availability_options',
    'no_exact_match_yet',
    'no_formats',
    'no_languages',
    'no_sorting_options',
    'no_specialist_types',
    'no_specializations',
    'no_species_options',
    'price',
    'problem',
    'professional_workspace',
    'qualification_verified',
    'remove_one_filter_try_a_nearby_city_or_browse_newly_verified_specialists_who_accept_online_consultations',
    'scope_details_pending',
    'search',
    'show_only_profiles_with_a_checked_professional_qualification',
    'sort',
    'specialist',
    'specializations',
    'species',
    'verified_professional_community',
    'verified_reviews',
    'view_profile',
    'vilnius',
    'why_this_profile_matches',
];

const EXPERT_DIRECTORY_STATS_MESSAGE_KEYS = [
    'accepting_clients',
    'published_profiles',
    'qualification_verified',
    'qualifications_verified',
    'species_covered',
];

const EXPERT_DIRECTORY_RENDERED_UI_KEYS = [
    'a_verification_badge_explains_what_was_checked_it_is_never_a_guarantee_of_outcome',
    'all_areas',
    'all_specialist_types',
    'any_availability',
    'any_format',
    'any_language',
    'apply',
    'availability',
    'book',
    'city',
    'client_rating',
    'compare_scope_species_independently_checked_credentials_availability_language_and_price_before_sharing_any_private_pet_information',
    'create_professional_profile',
    'every_species',
    'expert_directory_summary',
    'find_the_right_specialist_for_this_pet',
    'format',
    'from',
    'language',
    'matching_professionals',
    'name_skill_city_or_approach',
    'need_urgent_veterinary_help',
    'new_profile',
    'next_time',
    'price',
    'problem',
    'professional_workspace',
    'qualification_verified',
    'search',
    'show_only_profiles_with_a_checked_professional_qualification',
    'sort',
    'specialist',
    'specializations',
    'species',
    'verified_professional_community',
    'verified_reviews',
    'view_profile',
    'why_this_profile_matches',
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

    foreach (['no_exact_match_yet', 'remove_one_filter_try_a_nearby_city_or_browse_newly_verified_specialists_who_accept_online_consultations'] as $key) {
        $response->assertSee(trans("ui.{$key}", locale: $locale));
    }
})->with(['lt', 'ru']);

test('the expert domain has a complete shared localization contract', function (): void {
    foreach (['en', 'lt', 'ru'] as $locale) {
        expect(File::exists(lang_path("{$locale}/experts.php")), $locale)->toBeTrue();
    }

    $english = Arr::dot(require lang_path('en/experts.php'));

    expect($english)->toHaveCount(77);

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
            if ($locale === 'lt' && $key === 'vilnius') {
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
    $pet = PetProfile::factory()->for($this->authenticatedUser)->create([
        'profile_key' => 'pet-luna',
        'name' => 'Luna',
    ]);
    PetProfileManager::factory()
        ->for($pet, 'profile')
        ->for($this->authenticatedUser)
        ->create();
    $taxonomy = app(ExpertTaxonomy::class);

    expect(ExpertProfileStatus::Pending->label())->toBe(trans('experts.profile_statuses.pending'))
        ->and($taxonomy->types()['avian-veterinarian'])->toBe(trans('experts.types.avian-veterinarian'))
        ->and($taxonomy->species()['bird'])->toBe(trans('experts.species.bird'))
        ->and($taxonomy->specializations()['avian-medicine'])->toBe(trans('experts.specializations.avian-medicine'))
        ->and($taxonomy->formats()['video'])->toBe(trans('experts.formats.video'))
        ->and($taxonomy->languages()['Lithuanian'])->toBe(trans('experts.languages.Lithuanian'))
        ->and($taxonomy->availability()['today'])->toBe(trans('experts.availability.today'))
        ->and($taxonomy->sortOptions()['rating'])->toBe(trans('experts.sort_options.rating'))
        ->and($taxonomy->pets()['pet-luna'])->toBe('Luna');
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

    expect($singleProfileQueryCount)->toBe(7)
        ->and(count($queries))->toBe(7);
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
