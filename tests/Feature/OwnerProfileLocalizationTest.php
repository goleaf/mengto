<?php

declare(strict_types=1);

use App\Services\ProfilePresenter;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

const OWNER_PROFILE_IDENTITY_KEYS = [
    'owner.identity.name',
    'owner.identity.handle',
    'owner.details.joined_value',
];

test('the owner profile contract is complete in every supported locale', function (): void {
    $english = Arr::dot(require lang_path('en/member_profiles.php'));

    expect($english)->toHaveKeys([
        'owner.page.title',
        'owner.hero.summary_label',
        'owner.hero.summary_unavailable',
        'owner.hero.actions_label',
        'owner.tabs.label',
        'owner.tabs.overview',
        'owner.tabs.pets',
        'owner.tabs.posts',
        'owner.tabs.about',
        'owner.preview.title',
        'owner.preview.label',
        'owner.preview.audiences.owner',
        'owner.preview.audiences.public',
        'owner.preview.audiences.follower',
        'owner.preview.audiences.friend',
        'owner.sections.about.title',
        'owner.sections.pets.title',
        'owner.sections.posts.title',
        'owner.sections.details.title',
        'owner.sections.interests.title',
        'owner.sections.languages.title',
        'owner.sections.privacy.title',
        'owner.sections.completion.title',
        'owner.sections.badges.title',
        'owner.sections.availability.title',
        'owner.sections.safety.title',
        'owner.restrictions.pets.title',
        'owner.restrictions.posts.title',
        'owner.identity.bio',
        'owner.actions.edit',
        'owner.actions.settings',
        'owner.actions.privacy',
        'owner.actions.share',
        'owner.actions.follow',
        'owner.actions.friend',
        'owner.actions.message',
        'owner.completion.detail',
    ]);

    $englishOwner = Arr::where(
        $english,
        static fn (mixed $value, string $key): bool => str_starts_with($key, 'owner.'),
    );

    foreach (['lt', 'ru'] as $locale) {
        $localized = Arr::dot(require lang_path("{$locale}/member_profiles.php"));
        $localizedOwner = Arr::where(
            $localized,
            static fn (mixed $value, string $key): bool => str_starts_with($key, 'owner.'),
        );

        expect(array_keys($localizedOwner), $locale)->toBe(array_keys($englishOwner));

        foreach ($localizedOwner as $key => $value) {
            expect($value, "{$locale}.member_profiles.{$key}")->toBeString()->not->toBe('');

            if (! in_array($key, OWNER_PROFILE_IDENTITY_KEYS, true)) {
                expect($value, "{$locale}.member_profiles.{$key}")->not->toBe($englishOwner[$key]);
            }
        }
    }
});

test('the owner profile renders its localized system and first party copy', function (string $locale): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    $copy = require lang_path("{$locale}/member_profiles.php");
    $response = $this->get(route('profile.mia'))->assertOk();

    foreach ([
        data_get($copy, 'owner.hero.summary_label'),
        data_get($copy, 'owner.tabs.label'),
        data_get($copy, 'owner.preview.title'),
        data_get($copy, 'owner.preview.audiences.owner'),
        data_get($copy, 'owner.sections.about.eyebrow'),
        data_get($copy, 'owner.sections.about.title'),
        data_get($copy, 'owner.sections.pets.eyebrow'),
        data_get($copy, 'owner.sections.posts.eyebrow'),
        data_get($copy, 'owner.sections.completion.title'),
        data_get($copy, 'owner.sections.badges.title'),
        data_get($copy, 'owner.sections.availability.title'),
        data_get($copy, 'owner.identity.bio'),
        data_get($copy, 'owner.actions.edit'),
        data_get($copy, 'owner.actions.settings'),
        data_get($copy, 'owner.actions.privacy'),
        data_get($copy, 'owner.actions.share'),
    ] as $value) {
        $response->assertSee((string) $value);
    }

    $xpath = responseXPath($response);

    expect($xpath->query('//*[@data-owner-profile]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-owner-profile-hero]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-owner-profile-preview]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-owner-profile-overview]')->length)->toBe(1);
})->with(['en', 'lt', 'ru']);

test('owner profile tab and audience codes stay locale independent', function (string $locale): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    foreach (['overview', 'pets', 'posts', 'about'] as $tab) {
        foreach (['owner', 'public', 'follower', 'friend'] as $view) {
            $xpath = responseXPath($this->get(route('profile.mia', compact('tab', 'view')))->assertOk());

            expect($xpath->query("//*[@data-owner-profile][@data-owner-profile-tab='{$tab}'][@data-owner-profile-audience='{$view}']")->length)
                ->toBe(1)
                ->and($xpath->query("//*[@data-profile-tab='{$tab}'][@aria-current='page']")->length)
                ->toBe(1)
                ->and($xpath->query("//*[@data-profile-audience='{$view}'][@aria-current='page']")->length)
                ->toBe(1);
        }
    }
})->with(['en', 'lt', 'ru']);

test('owner profile uses one canonical lucide icon language', function (): void {
    $xpath = responseXPath($this->get(route('profile.mia'))->assertOk());

    expect(array_map(
        static fn (DOMNode $node): string => (string) $node->attributes?->getNamedItem('data-ui-icon')?->nodeValue,
        iterator_to_array($xpath->query('//*[@data-owner-profile-overview]//*[@data-owner-profile-section-icon]//h2//*[@data-ui-icon]')),
    ))->toBe(['user-round', 'paw-print', 'images', 'gauge', 'badge-check', 'calendar-clock']);
});

test('owner profile blade consumes prepared copy without generic translation domains', function (): void {
    $view = File::get(resource_path('views/components/owner-profile.blade.php'));

    expect($view)
        ->toContain('$profile[\'copy\']', 'data-owner-profile')
        ->not->toContain("__('ui.", "__('messages.", "__('presentation.");

    foreach ([
        resource_path('views/components/profile-view-switcher.blade.php'),
        resource_path('views/components/profile-safety-actions.blade.php'),
    ] as $path) {
        expect(File::get($path), $path)->toContain('$copy');
    }

    $presenter = File::get(app_path('Services/ProfilePresenter.php'));
    preg_match(
        '/public function ownerPage\(.*?\): array(?<body>.*?)(?=public function petPage)/s',
        $presenter,
        $ownerPage,
    );

    expect($ownerPage['body'] ?? '')
        ->not->toBe('')
        ->toContain("__('member_profiles.owner.")
        ->not->toContain("__('ui.", "__('presentation.");

    expect(File::get(base_path('scripts/accessibility-browser-check.mjs')))
        ->toContain(
            'englishOwnerProfileCopy',
            'ownerProfileCopy.length === 55',
            'English owner profile body fallback remains.',
            'page-identity-owner-profile-',
            'data-owner-profile-focus-target',
            "matches(':focus-visible')",
        );
});

test('the owner profile presenter skips unrelated pet and moment queries', function (): void {
    $queries = [];
    DB::listen(static function (QueryExecuted $query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    app(ProfilePresenter::class)->ownerPage(tab: 'about', audience: 'friend');

    expect($queries)
        ->toHaveCount(1)
        ->each->not->toContain('from "pet_profiles"');
});
