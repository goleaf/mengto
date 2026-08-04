<?php

declare(strict_types=1);

use App\Services\PreviewService;
use App\Services\SharePresenter;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

test('the share page owns a complete localized contract', function (): void {
    $english = Arr::dot(require lang_path('en/sharing.php'));

    expect($english)
        ->toHaveCount(42)
        ->toHaveKeys([
            'page.title',
            'channels.email.title',
            'channels.text.action',
            'neighbors.empty.description',
            'details.type',
            'privacy.description',
            'message.subject',
            'targets.pet_moment.type',
            'targets.community.eyebrow',
            'targets.meetup.type',
            'targets.member_profile.eyebrow',
            'targets.pet_profile.type',
        ]);

    foreach (['lt', 'ru'] as $locale) {
        $localized = Arr::dot(require lang_path("{$locale}/sharing.php"));

        expect(array_keys($localized), $locale)->toBe(array_keys($english));

        foreach ($english as $key => $value) {
            preg_match_all('/:[a-z_]+/', (string) $value, $englishPlaceholders);
            preg_match_all('/:[a-z_]+/', (string) $localized[$key], $localizedPlaceholders);

            expect($localized[$key], "{$locale}.sharing.{$key}")
                ->toBeString()
                ->not->toBe('')
                ->not->toBe($value)
                ->and($localizedPlaceholders[0])
                ->toBe($englishPlaceholders[0]);
        }
    }
});

test('the share page renders localized system copy and target taxonomy', function (string $locale): void {
    $this->authenticatedUser->update(['locale' => $locale]);
    app()->setLocale($locale);
    $copy = require lang_path("{$locale}/sharing.php");
    $data = app(PreviewService::class)->shareData('apartment-pets');

    expect($data)->toBeArray()
        ->and(data_get($data, 'share.item.type'))->toBe((string) data_get($copy, 'targets.community.type'))
        ->and(data_get($data, 'share.item.eyebrow'))->toBe((string) data_get($copy, 'targets.community.eyebrow'))
        ->and(data_get($data, 'share.copy.channels.count'))->toBe(trans_choice('sharing.channels.count', 3, ['count' => 3]))
        ->and(data_get($data, 'share.copy.neighbors.count'))->toBe(trans_choice('sharing.neighbors.count', 4, ['count' => 4]));

    $response = $this->get(route('share.show', ['target' => 'apartment-pets']))->assertOk();

    foreach (Arr::flatten([
        Arr::except((array) data_get($copy, 'page'), ['title']),
        Arr::except((array) data_get($copy, 'channels'), ['count', 'empty']),
        Arr::except((array) data_get($copy, 'neighbors'), ['count', 'empty']),
        Arr::except((array) data_get($copy, 'details'), ['empty']),
        data_get($copy, 'privacy'),
        data_get($copy, 'targets.community'),
    ]) as $value) {
        $response->assertSee((string) $value);
    }

    $response
        ->assertSee(trans_choice('sharing.channels.count', 3, ['count' => 3]))
        ->assertSee(trans_choice('sharing.neighbors.count', 4, ['count' => 4]));
})->with(['lt', 'ru']);

test('the share page keeps stable channel codes and canonical icons', function (): void {
    $response = $this->get(route('share.show', ['target' => 'apartment-pets']))->assertOk();
    $xpath = responseXPath($response);
    $channelIcons = [
        'email' => 'mail',
        'text' => 'message-square-text',
        'original' => 'external-link',
    ];

    expect($xpath->query('//*[@data-share-page]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-share-page]//*[@data-share-channel]')->length)->toBe(3)
        ->and($xpath->query('//*[@data-share-page]//*[@data-share-recipient]')->length)->toBe(4)
        ->and($xpath->query('//*[@data-share-page]//*[@data-share-recipient-action]//*[@data-ui-icon="send"]')->length)->toBe(4)
        ->and($xpath->query(
            '//*[@data-share-page]/*[contains(concat(" ", normalize-space(@class), " "), " text-link ")]//*[@data-ui-icon="arrow-left"]',
        )->length)->toBe(1)
        ->and($xpath->query('//*[@data-share-page]//*[@data-share-open-original]//*[@data-ui-icon="external-link"]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-share-page]//*[@data-share-privacy]//*[@data-ui-icon="shield-check"]')->length)->toBe(1);

    foreach ($channelIcons as $code => $icon) {
        expect($xpath->query(
            "//*[@data-share-channel='{$code}']//*[@data-share-channel-icon]//*[@data-ui-icon='{$icon}']",
        )->length, $code)->toBe(1)
            ->and($xpath->query(
                "//*[@data-share-channel='{$code}']//*[@data-share-channel-action]//*[@data-ui-icon='arrow-up-right']",
            )->length, "{$code}:action")->toBe(1);
    }

    expect($xpath->query('//*[@data-share-channel="email"]//a[starts-with(@href, "mailto:")]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-share-channel="text"]//a[starts-with(@href, "sms:")]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-share-channel="original"]//a[contains(@href, "/groups/apartment-pets")]')->length)->toBe(1);
});

test('every supported share target resolves through a stable section code', function (
    string $section,
    string $target,
): void {
    app()->setLocale('ru');

    $share = app(SharePresenter::class)->present([
        'route' => 'home',
        'route_parameters' => [],
        'active_section' => $section,
        'type' => 'legacy type',
        'eyebrow' => 'legacy eyebrow',
        'title' => 'Stable title',
    ], []);

    expect(data_get($share, 'item.type'))->toBe((string) trans("sharing.targets.{$target}.type"))
        ->and(data_get($share, 'item.eyebrow'))->toBe((string) trans("sharing.targets.{$target}.eyebrow"));
})->with([
    'pet moment' => ['feed', 'pet_moment'],
    'community' => ['groups', 'community'],
    'meetup' => ['meetups', 'meetup'],
    'member profile' => ['profile', 'member_profile'],
    'pet profile' => ['pets', 'pet_profile'],
]);

test('the share presenter adds no Eloquent query', function (): void {
    $queries = 0;
    DB::listen(static function (QueryExecuted $query) use (&$queries): void {
        $queries++;
    });

    $share = app(SharePresenter::class)->present([
        'route' => 'home',
        'route_parameters' => [],
        'active_section' => 'groups',
        'type' => 'legacy type',
        'eyebrow' => 'legacy eyebrow',
        'title' => 'Stable title',
    ], []);

    expect($share)->toBeArray()
        ->and($queries)->toBe(0);
});

test('the share page source stays inside its domain and browser ratchet', function (): void {
    $presenter = File::get(app_path('Services/SharePresenter.php'));
    $viewSources = collect([
        resource_path('views/share/show.blade.php'),
        resource_path('views/components/share-channel-grid.blade.php'),
        resource_path('views/components/share-channel-card.blade.php'),
        resource_path('views/components/share-recipient-list.blade.php'),
        resource_path('views/components/share-recipient-item.blade.php'),
    ])->map(fn (string $path): string => File::get($path))->implode("\n");
    $browser = File::get(base_path('scripts/accessibility-browser-check.mjs'));

    expect($presenter)
        ->toContain("__('sharing.", "'code' => 'email'", "'code' => 'text'", "'code' => 'original'")
        ->not->toContain("__('ui.", "__('messages.", "__('presentation.")
        ->and($viewSources)
        ->toContain('data-share-page', 'data-share-channel', 'data-share-recipient')
        ->not->toContain("__('ui.", "__('messages.", "__('presentation.")
        ->and($browser)
        ->toContain(
            'shareCopy.length === 30',
            "['mail', 'arrow-up-right', 'message-square-text', 'arrow-up-right', 'external-link', 'arrow-up-right']",
            'share controls below 44px',
            'English share fallback remains',
            'page-identity-share-',
        );
});
