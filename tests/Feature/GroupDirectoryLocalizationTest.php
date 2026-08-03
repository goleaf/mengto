<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

const GROUP_DIRECTORY_RENDERED_COPY = [
    'directory.summary_label',
    'directory.your_groups',
    'directory.joined_communities',
    'directory.nearby',
    'directory.portland_communities',
    'directory.this_week',
    'directory.posts_across_groups',
    'directory.create_group',
    'directory.filters_label',
    'directory.filter_categories_label',
    'directory.sort_label',
    'directory.search_label',
    'directory.search_placeholder',
    'directory.filters.recommended',
    'directory.filters.joined',
    'directory.filters.local',
    'directory.filters.breed',
    'directory.filters.care',
    'directory.filters.official',
    'directory.sort.active',
    'directory.sort.members',
    'directory.sort.name',
    'directory.privacy.closed',
    'directory.privacy.public',
    'directory.actions.hide_suggestion',
    'directory.actions.joined',
    'directory.actions.request_to_join',
    'directory.actions.join_group',
    'directory.card.official',
    'directory.card.summary',
    'directory.card.members',
    'directory.card.activity',
    'directory.card.results_title',
];

test('the group directory renders localized system and catalogue copy', function (string $locale): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    $response = $this->get(route('groups.index'))->assertOk();

    $response->assertSee(trans('ui.why_this_profile_11657790a4', locale: $locale));

    foreach (GROUP_DIRECTORY_RENDERED_COPY as $key) {
        $response->assertSee(trans("groups.{$key}", locale: $locale));
    }

    foreach ([
        'catalog.apartment_pets.category',
        'catalog.apartment_pets.description',
        'catalog.apartment_pets.image_alt',
        'catalog.apartment_pets.tags.0',
        'catalog.apartment_pets.recommendation_reason',
        'catalog.apartment_pets.next_event',
    ] as $key) {
        $response->assertSee(trans("groups.{$key}", locale: $locale));
    }

    $xpath = responseXPath($response);

    expect($xpath->query('//*[@data-group-summary]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-group-filters]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-group-results-title]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-group-card-category]')->length)->toBe(6)
        ->and($xpath->query('//*[@data-group-card-privacy]')->length)->toBe(6)
        ->and($xpath->query('//*[@data-group-card-reason]')->length)->toBe(6)
        ->and($xpath->query('//*[@data-group-card-description]')->length)->toBe(6)
        ->and($xpath->query('//*[@data-group-card-tags]')->length)->toBe(6)
        ->and($xpath->query('//*[@data-group-card-metrics]')->length)->toBe(6)
        ->and($xpath->query('//*[@data-group-card-event]')->length)->toBe(6)
        ->and($xpath->query('//*[@data-group-card-organizer]')->length)->toBe(6)
        ->and($xpath->query('//*[@data-group-card-primary-action]')->length)->toBe(6);
})->with(['lt', 'ru']);

test('the group catalogue has a complete shared localization contract', function (): void {
    foreach (['en', 'lt', 'ru'] as $locale) {
        expect(File::exists(lang_path("{$locale}/groups.php")), $locale)->toBeTrue();
    }

    $english = Arr::dot(require lang_path('en/groups.php'));

    expect(array_keys($english))->toContain(...GROUP_DIRECTORY_RENDERED_COPY);

    foreach (['lt', 'ru'] as $locale) {
        $localized = Arr::dot(require lang_path("{$locale}/groups.php"));

        expect(array_keys($localized), $locale)->toBe(array_keys($english));

        foreach ($english as $key => $value) {
            if (preg_match('/^catalog\.[^.]+\.(?:name|organizer)$/', $key) === 1) {
                continue;
            }

            expect($localized[$key], "{$locale}.groups.{$key}")
                ->not->toBe($value);
        }

        expect(trans('ui.why_this_profile_11657790a4', locale: $locale))
            ->not->toBe(trans('ui.why_this_profile_11657790a4', locale: 'en'));
    }
});

test('the group catalogue keeps every first party tag inside the localization contract', function (): void {
    $source = File::get(app_path('Services/GroupCatalog.php'));

    expect($source)
        ->not->toMatch("/'tags'\\s*=>\\s*\\[/")
        ->toContain(
            "__('groups.catalog.apartment_pets.tags')",
            "__('groups.catalog.trail_tails.tags')",
            "__('groups.catalog.cat_people.tags')",
            "__('groups.catalog.foster_network.tags')",
            "__('groups.catalog.portland_labradors.tags')",
            "__('groups.catalog.senior_companions.tags')",
        );
});

test('the browser matrix rejects group body fallbacks', function (): void {
    $browser = File::get(base_path('scripts/accessibility-browser-check.mjs'));
    $directory = File::get(resource_path('views/groups/index.blade.php'));
    $results = File::get(resource_path('views/components/group-directory-results.blade.php'));
    $card = File::get(resource_path('views/components/group-card.blade.php'));

    expect($browser)
        ->toContain(
            'englishGroupCopy',
            'groupCopy.length === 32',
            'English group body fallback remains.',
        )
        ->and($directory)
        ->toContain('data-group-summary', 'data-group-filters')
        ->and($results)
        ->toContain('data-group-results-title')
        ->and($card)
        ->toContain(
            'data-group-card-category',
            'data-group-card-privacy',
            'data-group-card-reason',
            'data-group-card-description',
            'data-group-card-tags',
            'data-group-card-metrics',
            'data-group-card-event',
            'data-group-card-organizer',
            'data-group-card-primary-action',
        );
});
