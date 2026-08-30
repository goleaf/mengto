<?php

declare(strict_types=1);

use App\Enums\ListingType;
use App\Enums\SellerType;
use App\Models\Listing;
use App\Services\ListingTaxonomy;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

const MARKETPLACE_DIRECTORY_UI_KEYS = [
    'all_categories',
    'all_types',
    'any_availability',
    'any_condition',
    'any_option',
    'any_seller',
    'apply',
    'availability',
    'available',
    'available_now',
    'buy_exchange_rehome_or_book_without_exposing_your_phone_number_or_home_address_before_both_sides_agree',
    'category',
    'cities',
    'city',
    'clear_filters',
    'community_marketplace',
    'community_status_is_not_a_guarantee_inspect_items_and_verify_services_before_payment',
    'condition',
    'create_listing',
    'every_pet_type',
    'for_adoption',
    'for_rent',
    'free',
    'handover',
    'item_service_city_or_category',
    'listing_type',
    'location',
    'marketplace_statistics_are_unavailable',
    'marketplace_summary',
    'no_availability_filters',
    'no_categories',
    'no_condition_filters',
    'no_exact_match_yet',
    'no_handover_options',
    'no_listing_types',
    'no_pet_types',
    'no_price_filters',
    'no_seller_filters',
    'no_sort_options',
    'pet',
    'pet_type_not_specified',
    'platform_only_contact',
    'price',
    'remove_one_filter_search_a_nearby_city_or_create_a_clear_request_in_the_forum',
    'save',
    'saved',
    'search',
    'seller',
    'shelter_needs',
    'sort',
    'suitable_pets',
    'useful_things_and_trusted_pet_services',
    'verified_seller',
    'view',
    'vilnius',
];

const MARKETPLACE_DIRECTORY_DEFENSIVE_UI_KEYS = [
    'marketplace_statistics_are_unavailable',
    'no_availability_filters',
    'no_categories',
    'no_condition_filters',
    'no_handover_options',
    'no_listing_types',
    'no_pet_types',
    'no_price_filters',
    'no_seller_filters',
    'no_sort_options',
    'pet_type_not_specified',
    'saved',
    'verified_seller',
];

const MARKETPLACE_DIRECTORY_EMPTY_UI_KEYS = [
    'no_exact_match_yet',
    'remove_one_filter_search_a_nearby_city_or_create_a_clear_request_in_the_forum',
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

    expect($english)->toHaveCount(106);

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
            if ($locale === 'lt' && $key === 'vilnius') {
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
        ->and($taxonomy->categories()['pet-sitting'])->toBe(trans('marketplace.categories.pet-sitting'))
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
