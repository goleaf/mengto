<?php

declare(strict_types=1);

use App\Enums\ListingType;
use App\Enums\SellerType;
use App\Models\Listing;
use App\Services\ListingTaxonomy;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

const MARKETPLACE_DIRECTORY_UI_KEYS = [
    'all_categories_9d5097a837',
    'all_types_f10988e79e',
    'any_availability_372ccd0787',
    'any_condition_8d8c95487f',
    'any_option_2fc1501a67',
    'any_seller_d512c305bd',
    'apply_31e392d1c0',
    'availability_12f67f8539',
    'available_e674447337',
    'available_now_2a4729fa76',
    'buy_exchange_rehome_or_book_without_exposing_your_a7174cb664',
    'category_292c06f004',
    'cities_95697d1449',
    'city_fc33f73246',
    'clear_filters_7179ea0035',
    'community_marketplace_1525148f3c',
    'community_status_is_not_a_guarantee_inspect_items_4a9153ad08',
    'condition_39b36d38d6',
    'create_listing_815d30caa6',
    'every_pet_type_b4aed4a4ff',
    'for_adoption_0435a17996',
    'for_rent_03cc104614',
    'free_f411a1fb62',
    'handover_c012b47252',
    'item_service_city_or_category_2e46b38259',
    'listing_type_329627e862',
    'location_15b61974b2',
    'marketplace_statistics_are_unavailable_c530fed378',
    'marketplace_summary_f9ecef7b29',
    'no_availability_filters_fa18f0256c',
    'no_categories_29b8c8b535',
    'no_condition_filters_e8ebcb68ab',
    'no_exact_match_yet_85432de381',
    'no_handover_options_a7e9700b2b',
    'no_listing_types_1b2c3d3c8d',
    'no_pet_types_e1150f17ef',
    'no_price_filters_764fac2c44',
    'no_seller_filters_a0963d00ee',
    'no_sort_options_dd1d70e52f',
    'pet_8f0d1b30eb',
    'pet_type_not_specified_af58a4e2cc',
    'platform_only_contact_51f3af5138',
    'price_93c91c851e',
    'remove_one_filter_search_a_nearby_city_or_528eb60b39',
    'save_1509f561f2',
    'saved_b5c120b316',
    'search_49c266baaa',
    'seller_01498fa31d',
    'shelter_needs_939002282f',
    'sort_bec69036aa',
    'suitable_pets_f64e6eef51',
    'useful_things_and_trusted_pet_services_0b2d0b997a',
    'verified_seller_8988c729d5',
    'view_dcc839a401',
    'vilnius_c283e0869a',
];

const MARKETPLACE_DIRECTORY_DEFENSIVE_UI_KEYS = [
    'marketplace_statistics_are_unavailable_c530fed378',
    'no_availability_filters_fa18f0256c',
    'no_categories_29b8c8b535',
    'no_condition_filters_e8ebcb68ab',
    'no_handover_options_a7e9700b2b',
    'no_listing_types_1b2c3d3c8d',
    'no_pet_types_e1150f17ef',
    'no_price_filters_764fac2c44',
    'no_seller_filters_a0963d00ee',
    'no_sort_options_dd1d70e52f',
    'pet_type_not_specified_af58a4e2cc',
    'saved_b5c120b316',
    'verified_seller_8988c729d5',
];

const MARKETPLACE_DIRECTORY_EMPTY_UI_KEYS = [
    'no_exact_match_yet_85432de381',
    'remove_one_filter_search_a_nearby_city_or_528eb60b39',
];

test('the marketplace directory renders localized system and taxonomy copy', function (string $locale): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    Listing::factory()->create([
        'title' => 'Marketplace Locale Test',
        'cover_url' => null,
        'category' => 'walking-gear',
        'species' => ['dog'],
        'availability' => 'in-stock',
    ]);

    $response = $this->get(route('marketplace.index'))->assertOk();

    foreach (array_diff(
        MARKETPLACE_DIRECTORY_UI_KEYS,
        MARKETPLACE_DIRECTORY_DEFENSIVE_UI_KEYS,
        MARKETPLACE_DIRECTORY_EMPTY_UI_KEYS,
    ) as $key) {
        $response->assertSee(trans("ui.{$key}", locale: $locale));
    }

    foreach ([
        'listing_types.sale',
        'categories.walking-gear',
        'species.dog',
        'availability.in-stock',
        'seller_types.private',
    ] as $key) {
        $response->assertSee(trans("marketplace.{$key}", locale: $locale));
    }

    expect(responseXPath($response)->query('//*[@data-listing-card]')->length)->toBe(1);
})->with(['lt', 'ru']);

test('the marketplace empty result follows the authenticated users locale', function (string $locale): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    $response = $this->get(route('marketplace.index', ['q' => 'no-marketplace-locale-match']))
        ->assertOk();

    foreach (MARKETPLACE_DIRECTORY_EMPTY_UI_KEYS as $key) {
        $response->assertSee(trans("ui.{$key}", locale: $locale));
    }
})->with(['lt', 'ru']);

test('the marketplace has a complete shared localization contract', function (): void {
    foreach (['en', 'lt', 'ru'] as $locale) {
        expect(File::exists(lang_path("{$locale}/marketplace.php")), $locale)->toBeTrue();
    }

    $english = Arr::dot(require lang_path('en/marketplace.php'));

    expect($english)->toHaveCount(105);

    foreach (['lt', 'ru'] as $locale) {
        $localized = Arr::dot(require lang_path("{$locale}/marketplace.php"));

        expect(array_keys($localized), $locale)->toBe(array_keys($english));

        foreach ($english as $key => $value) {
            expect($localized[$key], "{$locale}.marketplace.{$key}")
                ->not->toBe($value);
        }
    }

    foreach (MARKETPLACE_DIRECTORY_UI_KEYS as $key) {
        foreach (['lt', 'ru'] as $locale) {
            if ($locale === 'lt' && $key === 'vilnius_c283e0869a') {
                continue;
            }

            expect(trans("ui.{$key}", locale: $locale), "{$locale}.ui.{$key}")
                ->not->toBe(trans("ui.{$key}", locale: 'en'));
        }
    }
});

test('the shared marketplace services resolve labels from the active locale', function (string $locale): void {
    app()->setLocale($locale);
    $taxonomy = app(ListingTaxonomy::class);

    expect(ListingType::Sale->label())->toBe(trans('marketplace.listing_types.sale'))
        ->and(ListingType::Adoption->requestLabel())->toBe(trans('marketplace.request_labels.adoption'))
        ->and(SellerType::PrivateSeller->label())->toBe(trans('marketplace.seller_types.private'))
        ->and($taxonomy->categories()['walking-gear'])->toBe(trans('marketplace.categories.walking-gear'))
        ->and($taxonomy->disputeReasons()['not-delivered'])->toBe(trans('marketplace.dispute_reasons.not-delivered'));
})->with(['lt', 'ru']);

test('the browser matrix rejects marketplace body fallbacks', function (): void {
    $browser = File::get(base_path('scripts/accessibility-browser-check.mjs'));
    $directory = File::get(resource_path('views/marketplace/index.blade.php'));

    expect($browser)
        ->toContain(
            'englishMarketplaceCopy',
            'marketplaceCopy.length === 41',
            'English marketplace body fallback remains.',
        )
        ->and($directory)
        ->toContain('data-marketplace-stats', 'data-marketplace-stat');
});
