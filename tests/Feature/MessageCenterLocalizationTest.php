<?php

declare(strict_types=1);

use App\View\Components\MessagingComposer;
use App\View\Components\MessagingMessage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

test('the message center system contract is complete in every supported locale', function (): void {
    $english = Arr::dot(require lang_path('en/messaging.php'));

    expect($english)
        ->toHaveCount(132)
        ->toHaveKeys([
            'composer.tools.image',
            'composer.schedule_help',
            'message.actions.report_default_body',
            'message.types.professional',
            'message.reactions.care',
            'message.status.sent',
            'message.status.delivered',
        ]);

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

test('the message composer renders localized copy with stable attachment codes and canonical icons', function (
    string $locale,
): void {
    $this->authenticatedUser->update(['locale' => $locale]);
    $copy = require lang_path("{$locale}/messaging.php");

    $response = $this->get(route('messages.index', ['conversation' => 'ari']))->assertOk();

    foreach (Arr::flatten([
        data_get($copy, 'composer.label'),
        data_get($copy, 'composer.replying'),
        data_get($copy, 'composer.cancel_reply'),
        data_get($copy, 'composer.draft_saving'),
        data_get($copy, 'composer.draft_saved'),
        data_get($copy, 'composer.message_type'),
        array_values((array) data_get($copy, 'composer.tools')),
        data_get($copy, 'composer.send_quietly'),
        data_get($copy, 'composer.send'),
        data_get($copy, 'composer.schedule'),
        data_get($copy, 'composer.send_at'),
        data_get($copy, 'composer.schedule_help'),
        data_get($copy, 'composer.privacy'),
    ]) as $value) {
        $response->assertSee((string) $value);
    }

    $xpath = responseXPath($response);
    $tools = [
        'image' => 'image',
        'video' => 'video',
        'file' => 'paperclip',
        'audio' => 'mic',
        'pet' => 'paw-print',
        'place' => 'map-pin',
        'event' => 'calendar-days',
        'task' => 'list-checks',
    ];
    $component = new MessagingComposer(
        conversation: ['key' => 'ari'],
        activeFilter: 'all',
        sender: 'Mia',
    );

    expect($xpath->query('//*[@data-messaging-composer]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-messaging-composer]//*[@data-message-type-button]')->length)
        ->toBe(count($tools))
        ->and(array_column($component->tools, 'type'))->toBe(array_keys($tools))
        ->and(array_column($component->tools, 'icon'))->toBe(array_values($tools));

    foreach ($tools as $type => $icon) {
        expect($xpath->query(
            "//*[@data-messaging-composer]//*[@data-message-type-button='{$type}']//*[@data-ui-icon='{$icon}']",
        )->length, $type)->toBe(1);
    }
})->with(['lt', 'ru']);

test('every supported message type and reaction resolves through the messaging contract', function (
    string $locale,
): void {
    app()->setLocale($locale);
    $copy = require lang_path("{$locale}/messaging.php");
    $icons = [
        'audio' => 'audio-lines',
        'image' => 'image',
        'video' => 'video',
        'file' => 'file-text',
        'place' => 'map-pin',
        'event' => 'calendar-days',
        'expert' => 'stethoscope',
        'listing' => 'store',
        'pet' => 'paw-print',
        'poll' => 'list-checks',
        'task' => 'clipboard-check',
        'walk' => 'route',
        'call' => 'phone-call',
        'system' => 'sparkles',
        'deleted' => 'message-square-off',
        'text' => 'message-circle',
        'announcement' => 'megaphone',
        'status' => 'circle-dot',
        'warning' => 'triangle-alert',
        'professional' => 'badge-check',
    ];

    foreach ($icons as $type => $icon) {
        $component = new MessagingMessage(
            message: [
                'id' => "catalog-{$type}",
                'sender' => 'Mia',
                'body' => 'Prepared content',
                'mine' => true,
                'type' => $type,
                'reaction' => null,
            ],
            conversation: ['key' => 'ari'],
        );

        expect($component->icon, "{$locale}:{$type}:icon")->toBe($icon)
            ->and($component->typeLabel, "{$locale}:{$type}:label")
            ->toBe((string) data_get($copy, "message.types.{$type}"))
            ->and($component->statusLabel)->toBe((string) data_get($copy, 'message.status.read'));
    }

    foreach (array_keys((array) data_get($copy, 'message.reactions')) as $reaction) {
        $component = new MessagingMessage(
            message: [
                'id' => "reaction-{$reaction}",
                'sender' => 'Ari',
                'body' => 'Prepared content',
                'mine' => false,
                'type' => 'text',
                'reaction' => $reaction,
            ],
            conversation: ['key' => 'ari'],
        );

        expect($component->reactionLabel, "{$locale}:{$reaction}")
            ->toBe((string) data_get($copy, "message.reactions.{$reaction}"))
            ->and($component->statusLabel)->toBe((string) data_get($copy, 'message.status.delivered'));
    }

    $sent = new MessagingMessage(
        message: [
            'id' => 'local-sent',
            'sender' => 'Mia',
            'body' => 'Prepared content',
            'mine' => true,
            'type' => 'text',
            'status_code' => 'sent',
        ],
        conversation: ['key' => 'ari'],
    );

    expect($sent->statusCode)->toBe('sent')
        ->and($sent->statusLabel)->toBe((string) data_get($copy, 'message.status.sent'));
})->with(['en', 'lt', 'ru']);

test('a newly sent message keeps its stable sent status in the current locale', function (
    string $locale,
): void {
    $this->authenticatedUser->update(['locale' => $locale]);
    $body = "Local status contract {$locale}";

    $this->post(route('messages.actions'), [
        'action' => 'send-message',
        'conversation' => 'ari',
        'message_type' => 'text',
        'body' => $body,
    ])->assertRedirect(route('messages.index', [
        'conversation' => 'ari',
        'filter' => 'all',
    ]));

    $response = $this->get(route('messages.index', ['conversation' => 'ari']))
        ->assertOk()
        ->assertSee($body)
        ->assertSee(trans('messaging.message.status.sent', locale: $locale));
    $xpath = responseXPath($response);

    expect($xpath->query(
        '//*[@data-messaging-message-status-code="sent"]',
    )->length)->toBe(1);
})->with(['lt', 'ru']);

test('message action chrome and structured message labels render in the current locale', function (
    string $locale,
): void {
    $this->authenticatedUser->update(['locale' => $locale]);
    $copy = require lang_path("{$locale}/messaging.php");

    $response = $this->get(route('messages.index', ['conversation' => 'ari']))->assertOk();

    foreach (Arr::flatten([
        data_get($copy, 'message.actions_label'),
        Arr::except((array) data_get($copy, 'message.actions'), ['edit', 'save_edit']),
        data_get($copy, 'message.types.place'),
        data_get($copy, 'message.types.audio'),
        Arr::only((array) data_get($copy, 'message.status'), ['read', 'delivered']),
        data_get($copy, 'message.audio_play'),
    ]) as $value) {
        $response->assertSee((string) $value);
    }

    $xpath = responseXPath($response);

    expect($xpath->query('//*[@data-messaging-message]')->length)->toBe(4)
        ->and($xpath->query('//*[@data-messaging-message-type="place"]//*[@data-ui-icon="map-pin"]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-messaging-message-type="audio"]//*[@data-ui-icon="audio-lines"]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-messaging-message-status]')->length)->toBe(4)
        ->and($xpath->query('//*[@data-audio-toggle]')->length)->toBe(1);
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
    $state = File::get(app_path('Services/MessageState.php'));
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

    expect($catalog)->toContain("'status_code' => \$mine ? 'read' : 'delivered'")
        ->and($state)->toContain("'status_code' => \$mine ? 'sent' : 'delivered'");

    foreach ([
        resource_path('views/components/messaging-folders.blade.php'),
        resource_path('views/components/messaging-inbox.blade.php'),
        resource_path('views/messages/index.blade.php'),
        resource_path('views/components/messaging-thread-header.blade.php'),
        resource_path('views/components/messaging-request.blade.php'),
        resource_path('views/components/messaging-professional-banner.blade.php'),
        resource_path('views/components/messaging-channels.blade.php'),
        resource_path('views/components/messaging-message-list.blade.php'),
        resource_path('views/components/messaging-composer.blade.php'),
        resource_path('views/components/messaging-message.blade.php'),
    ] as $path) {
        expect(File::get($path), $path)
            ->not->toContain("__('ui.", "__('messages.")
            ->toContain("__('messaging.");
    }

    $composer = File::get(app_path('View/Components/MessagingComposer.php'));
    $message = File::get(app_path('View/Components/MessagingMessage.php'));

    expect($composer)
        ->toContain("__('messaging.composer.tools.")
        ->not->toContain("__('ui.", "__('messages.", 'Str::headline')
        ->and($message)
        ->toContain("__('messaging.message.types.", "__('messaging.message.reactions.")
        ->not->toContain("__('ui.", "__('messages.", 'Str::headline');

    $browser = File::get(base_path('scripts/accessibility-browser-check.mjs'));
    expect($browser)->toContain(
        'englishMessagingCopy',
        'messagingCopy.length === 87',
        "route.path === '/messages' && viewport.width < 832",
        "['delivered', 'read', 'delivered', 'delivered']",
        'threadActionTitles',
        'messageListLabel',
        'English messaging system fallback remains.',
        'messaging folder labels are clipped',
        'messaging controls below 44px',
    );
});
