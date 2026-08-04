<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

test('the message center system contract is complete in every supported locale', function (): void {
    $english = Arr::dot(require lang_path('en/messaging.php'));

    expect($english)->toHaveCount(61);

    foreach (['lt', 'ru'] as $locale) {
        $localized = Arr::dot(require lang_path("{$locale}/messaging.php"));

        expect(array_keys($localized), $locale)->toBe(array_keys($english));

        foreach ($localized as $key => $value) {
            expect($value, "{$locale}.messaging.{$key}")
                ->toBeString()
                ->not->toBe('')
                ->not->toBe($english[$key]);
        }
    }
});

test('the message center renders localized folder inbox type and relative time copy', function (string $locale): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    $copy = require lang_path("{$locale}/messaging.php");
    $response = $this->get(route('messages.index'))->assertOk();

    foreach (Arr::flatten([
        data_get($copy, 'folders.label'),
        array_values((array) data_get($copy, 'folders.items')),
        data_get($copy, 'inbox.label'),
        data_get($copy, 'inbox.search_label'),
        data_get($copy, 'inbox.search_placeholder'),
        data_get($copy, 'inbox.search_action'),
        data_get($copy, 'inbox.conversations_label'),
        array_values((array) data_get($copy, 'types')),
        array_values((array) data_get($copy, 'relative')),
    ]) as $value) {
        $response->assertSee((string) $value);
    }

    $response->assertSee(trans(
        'messages.the_riverside_entrance_works_i_can_keep_mochi_on_the_out_41eb85053a',
        locale: 'en',
    ));

    $xpath = responseXPath($response);

    expect($xpath->query('//*[@data-messaging-folders]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-messaging-folders]//a')->length)->toBe(9)
        ->and($xpath->query('//*[@data-messaging-folders]//a//*[@data-ui-icon]')->length)->toBe(9)
        ->and($xpath->query('//*[@data-messaging-inbox]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-messaging-conversation-type]')->length)->toBe(8)
        ->and($xpath->query('//*[@data-messaging-conversation-time]')->length)->toBe(8);
})->with(['lt', 'ru']);

test('the message thread top level system chrome renders in every supported locale', function (string $locale): void {
    $this->authenticatedUser->update(['locale' => $locale]);
    $copy = require lang_path("{$locale}/messaging.php");

    $thread = $this->get(route('messages.index', ['conversation' => 'ari']))->assertOk();

    foreach ([
        data_get($copy, 'page.browser_title'),
        data_get($copy, 'page.eyebrow'),
        data_get($copy, 'page.heading'),
        data_get($copy, 'page.description'),
        data_get($copy, 'page.meta_label'),
        data_get($copy, 'page.new_message'),
        data_get($copy, 'thread.back'),
        data_get($copy, 'thread.audio_preflight'),
        data_get($copy, 'thread.audio_call'),
        data_get($copy, 'thread.video_preflight'),
        data_get($copy, 'thread.video_call'),
        data_get($copy, 'thread.details'),
        data_get($copy, 'thread.messages_label'),
        data_get($copy, 'relative.today'),
    ] as $value) {
        $thread->assertSee((string) $value);
    }

    $this->get(route('messages.index', [
        'conversation' => 'ari',
        'message_q' => 'no-system-message-can-match-this-query',
    ]))
        ->assertOk()
        ->assertSee((string) data_get($copy, 'page.clear_search'))
        ->assertSee((string) data_get($copy, 'thread.empty_title'))
        ->assertSee((string) data_get($copy, 'thread.empty_description'));

    $request = $this->get(route('messages.index', ['conversation' => 'luna-request']))->assertOk();

    foreach (Arr::flatten((array) data_get($copy, 'request')) as $value) {
        $request->assertSee((string) $value);
    }

    $this->get(route('messages.index', ['conversation' => 'paws-vet']))
        ->assertOk()
        ->assertSee((string) data_get($copy, 'professional.status_label'));

    $this->get(route('messages.index', ['conversation' => 'vingis-walk']))
        ->assertOk()
        ->assertSee((string) data_get($copy, 'channels.label'));

    $this->post(route('messages.actions'), [
        'action' => 'decline-message-request',
        'conversation' => 'luna-request',
    ])->assertRedirect();

    $this->get(route('messages.index', ['conversation' => 'luna-request']))
        ->assertOk()
        ->assertSee((string) data_get($copy, 'page.declined_title'))
        ->assertSee((string) data_get($copy, 'page.declined_description'));
})->with(['lt', 'ru']);

test('localized message folders retain stable filter values', function (string $locale): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    $response = $this->get(route('messages.index'))->assertOk();
    $xpath = responseXPath($response);
    $filters = ['all', 'unread', 'friends', 'groups', 'events', 'specialists', 'family', 'requests', 'archived'];

    $values = array_map(
        static function (DOMNode $node): string {
            parse_str((string) parse_url((string) $node->attributes?->getNamedItem('href')?->nodeValue, PHP_URL_QUERY), $query);

            return (string) ($query['filter'] ?? '');
        },
        iterator_to_array($xpath->query('//*[@data-messaging-folders]//a')),
    );

    expect($values)->toBe($filters);
})->with(['lt', 'ru']);

test('message center source uses its domain and the browser rejects English system fallbacks', function (): void {
    $presenter = File::get(app_path('Services/MessagePresenter.php'));
    $catalog = File::get(app_path('Services/MessageCatalog.php'));
    preg_match(
        '/private function presentConversations\(.*?(?=private function presentMessages)/s',
        $presenter,
        $conversationPresenter,
    );

    expect($presenter)
        ->toContain("__('messaging.folders.", 'conversationTypeLabel')
        ->toContain(
            "__('messaging.page.eyebrow')",
            "__('messaging.page.heading')",
            "__('messaging.page.description')",
        )
        ->and($conversationPresenter[0] ?? '')
        ->not->toBe('')
        ->not->toContain("'type_label' => Str::headline");

    foreach (['yesterday', 'monday', 'sunday', 'saturday', 'friday', 'today'] as $key) {
        expect($catalog)->toContain("__('messaging.relative.{$key}')");
    }

    foreach ([
        resource_path('views/components/messaging-folders.blade.php'),
        resource_path('views/components/messaging-inbox.blade.php'),
        resource_path('views/messages/index.blade.php'),
        resource_path('views/components/messaging-thread-header.blade.php'),
        resource_path('views/components/messaging-request.blade.php'),
        resource_path('views/components/messaging-professional-banner.blade.php'),
        resource_path('views/components/messaging-channels.blade.php'),
        resource_path('views/components/messaging-message-list.blade.php'),
    ] as $path) {
        expect(File::get($path), $path)
            ->not->toContain("__('ui.", "__('messages.")
            ->toContain("__('messaging.");
    }

    $browser = File::get(base_path('scripts/accessibility-browser-check.mjs'));
    expect($browser)->toContain(
        'englishMessagingCopy',
        'messagingCopy.length === 42',
        'threadActionTitles',
        'messageListLabel',
        'English messaging system fallback remains.',
        'messaging folder labels are clipped',
        'messaging controls below 44px',
    );
});
