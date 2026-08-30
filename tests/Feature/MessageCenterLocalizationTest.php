<?php

declare(strict_types=1);

use App\Services\MessageCatalog;
use App\Services\MessageState;
use App\View\Components\MessagingCallStage;
use App\View\Components\MessagingComposer;
use App\View\Components\MessagingContext;
use App\View\Components\MessagingMessage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

test('the message center system contract is complete in every supported locale', function (): void {
    $english = Arr::dot(require lang_path('en/messaging.php'));

    expect($english)
        ->toHaveCount(364)
        ->toHaveKeys([
            'conversations.ari.purpose',
            'context.controls.pin',
            'context.back_to_conversation',
            'context.safety.private_description',
            'context.boundary.accessibility_value',
            'call_stage.types.video',
            'call_stage.statuses.connected',
            'call_stage.qualities.reconnected',
            'call_stage.device.permission_denied',
            'call_stage.controls.reconnect',
            'call_stage.actions.end',
            'feedback.conversation.notifications_set',
            'feedback.notification_levels.important',
            'feedback.send.image',
            'feedback.call.ended',
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

test('conversation context renders localized metadata with stable action codes and canonical icons', function (
    string $locale,
): void {
    $this->authenticatedUser->update(['locale' => $locale]);
    app()->setLocale($locale);
    $copy = require lang_path("{$locale}/messaging.php");
    $catalog = app(MessageCatalog::class)->conversations();

    expect(array_keys($catalog))->toBe([
        'ari',
        'family-care',
        'vingis-walk',
        'paws-vet',
        'foster-adoption',
        'lost-luna',
        'trail-tails',
        'luna-request',
    ]);

    foreach ($catalog as $key => $conversation) {
        $translationKey = str_replace('-', '_', $key);

        foreach (['purpose', 'avatar_alt', 'verified', 'presence', 'response', 'privacy', 'role'] as $field) {
            expect($conversation[$field], "{$locale}:{$key}:{$field}")
                ->toBe((string) data_get($copy, "conversations.{$translationKey}.{$field}"));
        }
    }

    $response = $this->get(route('messages.index', ['conversation' => 'ari']))->assertOk();

    foreach (Arr::flatten([
        data_get($copy, 'context.label'),
        data_get($copy, 'context.identity_note'),
        data_get($copy, 'context.search_label'),
        data_get($copy, 'context.search_placeholder'),
        data_get($copy, 'context.controls_label'),
        data_get($copy, 'context.members.title'),
        data_get($copy, 'context.shared.title'),
        Arr::only((array) data_get($copy, 'context.shared'), [
            'pet_profiles',
            'places',
            'public_places',
            'events',
            'create_from_chat',
            'files',
            'scanned',
        ]),
        data_get($copy, 'context.safety.title'),
        Arr::except((array) data_get($copy, 'context.safety'), [
            'empty',
            'unrestrict',
            'unblock',
        ]),
        data_get($copy, 'context.boundary.title'),
        Arr::except((array) data_get($copy, 'context.boundary'), ['status', 'unavailable']),
    ]) as $value) {
        $response->assertSee((string) $value);
    }

    $xpath = responseXPath($response);
    $actions = [
        'pin-conversation' => ['pin', (string) data_get($copy, 'context.controls.pin')],
        'mute-conversation' => ['bell-off', (string) data_get($copy, 'context.controls.mute')],
        'archive-conversation' => ['archive', (string) data_get($copy, 'context.controls.archive')],
        'mark-conversation-unread' => ['mail', (string) data_get($copy, 'context.controls.unread')],
        'restrict-conversation' => ['shield-minus', (string) data_get($copy, 'context.safety.restrict')],
        'block-conversation' => ['ban', (string) data_get($copy, 'context.safety.block')],
        'export-conversation' => ['download', (string) data_get($copy, 'context.safety.export')],
    ];

    expect($xpath->query('//*[@data-messaging-context]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-messaging-context-identity-note]//*[@data-ui-icon="user-round-check"]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-messaging-context]//form/input[@name="action"]')->length)->toBe(count($actions));

    foreach ($actions as $action => [$icon, $label]) {
        $response->assertSee($label);
        expect($xpath->query(
            "//*[@data-messaging-context]//form[input[@name='action' and @value='{$action}']]//*[@data-ui-icon='{$icon}']",
        )->length, $action)->toBe(1);
    }

    $professional = $this->get(route('messages.index', ['conversation' => 'paws-vet']))->assertOk();
    foreach (Arr::flatten([
        data_get($copy, 'context.professional.title'),
        data_get($copy, 'context.professional.status'),
        data_get($copy, 'context.professional.assigned'),
        data_get($copy, 'context.professional.hours'),
        data_get($copy, 'context.professional.waiting_photo'),
        data_get($copy, 'context.professional.working_hours'),
        data_get($copy, 'context.professional.privacy'),
    ]) as $value) {
        $professional->assertSee((string) $value);
    }
    expect(responseXPath($professional)->query('//*[@data-messaging-context-professional]')->length)->toBe(1);

    $poll = $this->get(route('messages.index', ['conversation' => 'vingis-walk']))->assertOk();
    foreach (Arr::flatten(Arr::except((array) data_get($copy, 'context.poll'), ['empty'])) as $value) {
        $poll->assertSee((string) $value);
    }
    expect(responseXPath($poll)->query('//*[@data-messaging-context-poll]//form/input[@name="poll_option"]')->length)->toBe(3);

    $tasks = $this->get(route('messages.index', ['conversation' => 'family-care']))->assertOk();
    foreach (['title', 'statuses.assigned', 'statuses.in_progress'] as $key) {
        $tasks->assertSee((string) data_get($copy, "context.tasks.{$key}"));
    }
    expect(responseXPath($tasks)->query('//*[@data-messaging-context-tasks]//form/input[@name="task"]')->length)->toBe(2);

    $component = new MessagingContext(
        conversation: [
            'pinned' => false,
            'muted' => false,
            'archived' => false,
            'restricted' => false,
            'blocked' => false,
        ],
        context: [],
        members: [],
        poll: null,
        tasks: [],
        professional: null,
        activeFilter: 'all',
        messageQuery: '',
        coverage: [],
    );

    expect(array_column($component->controls, 'action'))->toBe(array_slice(array_keys($actions), 0, 4))
        ->and(array_column($component->controls, 'icon'))->toBe(['pin', 'bell-off', 'archive', 'mail'])
        ->and(array_column($component->safetyActions, 'action'))->toBe(array_slice(array_keys($actions), 4))
        ->and(array_column($component->safetyActions, 'icon'))->toBe(['shield-minus', 'ban', 'download']);
})->with(['lt', 'ru']);

test('the conversation details route exposes the localized context surface at every width', function (
    string $locale,
): void {
    $this->authenticatedUser->update(['locale' => $locale]);
    $copy = require lang_path("{$locale}/messaging.php");

    $response = $this->get(route('messages.details', ['conversation' => 'ari']))
        ->assertOk()
        ->assertSee((string) data_get($copy, 'context.label'))
        ->assertSee((string) data_get($copy, 'context.back_to_conversation'))
        ->assertSee((string) data_get($copy, 'context.identity_note'));
    $xpath = responseXPath($response);

    expect($xpath->query('//*[@data-messaging-center and @data-details-open]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-messaging-context]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-messaging-context]/nav/a[@href]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-messaging-context]/nav/a//*[@data-ui-icon="arrow-left"]')->length)->toBe(1);

    $stylesheet = File::get(resource_path('scss/_messaging.scss'));
    expect($stylesheet)
        ->toContain(
            '@media (max-width: 74.999rem)',
            '.messaging-page[data-details-open]',
            '.messaging-context__back',
        );
})->with(['lt', 'ru']);

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
        'messages.the_riverside_entrance_works_i_can_keep_mochi_on_the_outside_lane',
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

test('the call stage renders localized copy with stable state codes and canonical controls', function (
    string $locale,
): void {
    $this->authenticatedUser->update(['locale' => $locale]);
    $copy = require lang_path("{$locale}/messaging.php");

    $this->post(route('messages.actions'), [
        'action' => 'start-message-call',
        'conversation' => 'ari',
        'call_type' => 'video',
        'recording_consent' => 'no',
    ])->assertRedirect(route('messages.index', [
        'conversation' => 'ari',
        'filter' => 'all',
        'panel' => 'call',
    ]));

    $stored = app(MessageState::class)->call('ari');
    expect($stored)
        ->toBeArray()
        ->toHaveKey('type', 'video')
        ->toHaveKey('status', 'preflight')
        ->toHaveKey('quality_code', 'checking')
        ->not->toHaveKey('quality');

    $response = $this->get(route('messages.index', ['conversation' => 'ari']))->assertOk();

    foreach (Arr::flatten([
        data_get($copy, 'call_stage.label'),
        str_replace(
            [':type', ':status'],
            [data_get($copy, 'call_stage.types.video'), data_get($copy, 'call_stage.statuses.preflight')],
            (string) data_get($copy, 'call_stage.status_line'),
        ),
        data_get($copy, 'call_stage.qualities.checking'),
        data_get($copy, 'call_stage.device.not_requested'),
        data_get($copy, 'call_stage.recording_off'),
        array_values((array) data_get($copy, 'call_stage.checks')),
        data_get($copy, 'call_stage.consent_title'),
        array_values((array) data_get($copy, 'call_stage.boundary')),
        Arr::only((array) data_get($copy, 'call_stage.controls'), [
            'mute',
            'camera_off',
            'captions_on',
            'audio_only',
        ]),
        data_get($copy, 'call_stage.actions.close'),
        data_get($copy, 'call_stage.actions.join'),
    ]) as $value) {
        $response->assertSee((string) $value);
    }

    $xpath = responseXPath($response);
    $controls = [
        'microphone' => 'mic',
        'camera' => 'video',
        'captions' => 'captions',
        'audio-only' => 'phone',
    ];

    expect($xpath->query('//*[@data-call-stage and not(@hidden)]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-call-stage][@data-call-type-code="video"]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-call-stage][@data-call-status-code="preflight"]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-call-stage][@data-call-quality-code="checking"]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-call-stage]//*[@data-call-device]')->length)->toBe(2)
        ->and($xpath->query('//*[@data-call-stage]//input[@name="call_control"]')->length)->toBe(count($controls) + 1);

    foreach ($controls as $control => $icon) {
        expect($xpath->query(
            "//*[@data-call-stage]//form[input[@name='call_control' and @value='{$control}']]//*[@data-ui-icon='{$icon}']",
        )->length, $control)->toBe(1);
    }

    $component = new MessagingCallStage(
        conversation: ['key' => 'ari'],
        call: [
            'type_code' => 'video',
            'status_code' => 'preflight',
            'microphone' => true,
            'camera' => true,
            'captions' => false,
        ],
        boundary: ['transport' => '', 'recording' => '', 'emergency' => ''],
        activeFilter: 'all',
    );

    expect(array_column($component->controls, 'control'))->toBe(array_keys($controls))
        ->and(array_column($component->controls, 'icon'))->toBe(array_values($controls));

    $this->post(route('messages.actions'), [
        'action' => 'update-message-call',
        'conversation' => 'ari',
        'call_control' => 'join',
    ])->assertRedirect();

    $connected = $this->get(route('messages.index', ['conversation' => 'ari']))->assertOk();
    $connectedXPath = responseXPath($connected);

    expect(app(MessageState::class)->call('ari'))
        ->toHaveKey('status', 'connected')
        ->toHaveKey('quality_code', 'stable')
        ->and($connectedXPath->query('//*[@data-call-stage][@data-call-status-code="connected"]')->length)->toBe(1)
        ->and($connectedXPath->query('//*[@data-call-stage][@data-call-quality-code="stable"]')->length)->toBe(1)
        ->and($connectedXPath->query(
            '//*[@data-call-stage]//form[input[@name="call_control" and @value="reconnect"]]//*[@data-ui-icon="refresh-cw"]',
        )->length)->toBe(1);

    $connected
        ->assertSee((string) data_get($copy, 'call_stage.qualities.stable'))
        ->assertSee((string) data_get($copy, 'call_stage.controls.reconnect'))
        ->assertSee((string) data_get($copy, 'call_stage.actions.end'));

    $this->post(route('messages.actions'), [
        'action' => 'update-message-call',
        'conversation' => 'ari',
        'call_control' => 'audio-only',
    ])->assertRedirect();

    $audio = $this->get(route('messages.index', ['conversation' => 'ari']))->assertOk();
    $audioXPath = responseXPath($audio);

    expect(app(MessageState::class)->call('ari'))
        ->toHaveKey('type', 'audio')
        ->toHaveKey('quality_code', 'audio_only')
        ->and($audioXPath->query('//*[@data-call-stage][@data-call-type-code="audio"]')->length)->toBe(1)
        ->and($audioXPath->query('//*[@data-call-stage][@data-call-quality-code="audio_only"]')->length)->toBe(1)
        ->and($audioXPath->query('//*[@data-call-stage]//input[@name="call_control" and @value="camera"]')->length)->toBe(0)
        ->and($audioXPath->query('//*[@data-call-stage]//input[@name="call_control" and @value="audio-only"]')->length)->toBe(0);

    $audio->assertSee((string) data_get($copy, 'call_stage.qualities.audio_only'));
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

test('message mutations return localized domain feedback for every action family', function (
    string $locale,
): void {
    $this->authenticatedUser->update(['locale' => $locale]);
    $copy = require lang_path("{$locale}/messaging.php");
    $assertFeedback = function (array $payload, string $key, ?string $expected = null) use ($copy): void {
        $this->post(route('messages.actions'), [
            'conversation' => 'ari',
            ...$payload,
        ])
            ->assertRedirect()
            ->assertSessionHas('feedback', $expected ?? (string) data_get($copy, $key));
    };

    $this->post(route('messages.actions'), [
        'action' => 'send-message',
        'conversation' => 'luna-request',
        'message_type' => 'text',
        'body' => 'Pending request boundary',
    ])->assertSessionHasErrors([
        'body' => (string) data_get($copy, 'feedback.errors.request_pending'),
    ]);

    $assertFeedback([
        'action' => 'accept-message-request',
        'conversation' => 'luna-request',
    ], 'feedback.request.accepted');
    $assertFeedback([
        'action' => 'decline-message-request',
        'conversation' => 'luna-request',
    ], 'feedback.request.declined');

    foreach ([
        ['archive-conversation', 'feedback.conversation.archived'],
        ['archive-conversation', 'feedback.conversation.restored'],
        ['pin-conversation', 'feedback.conversation.pinned'],
        ['pin-conversation', 'feedback.conversation.unpinned'],
        ['mute-conversation', 'feedback.conversation.muted'],
        ['mute-conversation', 'feedback.conversation.notifications_restored'],
        ['mark-conversation-unread', 'feedback.conversation.marked_unread'],
        ['restrict-conversation', 'feedback.conversation.restricted'],
        ['restrict-conversation', 'feedback.conversation.restriction_removed'],
        ['export-conversation', 'feedback.conversation.export_prepared'],
    ] as [$action, $key]) {
        $assertFeedback(['action' => $action], $key);
    }

    $assertFeedback(['action' => 'block-conversation'], 'feedback.conversation.blocked');
    $this->post(route('messages.actions'), [
        'action' => 'send-message',
        'conversation' => 'ari',
        'message_type' => 'text',
        'body' => 'Blocked conversation boundary',
    ])->assertSessionHasErrors([
        'body' => (string) data_get($copy, 'feedback.errors.conversation_blocked'),
    ]);
    $assertFeedback(['action' => 'block-conversation'], 'feedback.conversation.unblocked');

    $notificationLevel = (string) data_get($copy, 'feedback.notification_levels.important');
    $assertFeedback(
        ['action' => 'set-message-notifications', 'notification_level' => 'important'],
        'feedback.conversation.notifications_set',
        str_replace(':level', $notificationLevel, (string) data_get($copy, 'feedback.conversation.notifications_set')),
    );

    foreach ([
        ['react-message', ['message' => 'ari-1', 'reaction' => 'care'], 'feedback.message.reaction_added'],
        ['react-message', ['message' => 'ari-1', 'reaction' => 'care'], 'feedback.message.reaction_removed'],
        ['pin-message', ['message' => 'ari-1'], 'feedback.message.pinned'],
        ['pin-message', ['message' => 'ari-1'], 'feedback.message.unpinned'],
        ['bookmark-message', ['message' => 'ari-1'], 'feedback.message.bookmarked'],
        ['bookmark-message', ['message' => 'ari-1'], 'feedback.message.bookmark_removed'],
        ['delete-message-self', ['message' => 'ari-1'], 'feedback.message.removed_self'],
        ['delete-message-everyone', ['message' => 'ari-2'], 'feedback.message.removed_everyone'],
        ['report-message', [
            'message' => 'ari-3',
            'report_reason' => 'personal-data',
            'body' => 'Localized moderation feedback check',
        ], 'feedback.message.reported'],
    ] as [$action, $payload, $key]) {
        $assertFeedback(['action' => $action, ...$payload], $key);
    }

    $assertFeedback([
        'action' => 'vote-chat-poll',
        'conversation' => 'vingis-walk',
        'poll_option' => 'saturday-morning',
    ], 'feedback.poll_recorded');
    $assertFeedback([
        'action' => 'update-chat-task',
        'conversation' => 'family-care',
        'task' => 'evening-walk',
        'task_status' => 'completed',
    ], 'feedback.task_updated');

    foreach ([
        ['audio', 'feedback.send.audio', 'Audio feedback contract'],
        ['image', 'feedback.send.image', 'Image feedback contract'],
        ['video', 'feedback.send.video', 'Video feedback contract'],
        ['file', 'feedback.send.file', 'File feedback contract'],
        ['pet', 'feedback.send.pet', 'Pet feedback contract'],
        ['place', 'feedback.send.place', 'Place feedback contract'],
        ['event', 'feedback.send.event', 'Event feedback contract'],
        ['task', 'feedback.send.task', 'Task feedback contract'],
    ] as [$type, $key, $body]) {
        $assertFeedback([
            'action' => 'send-message',
            'message_type' => $type,
            'body' => $body,
        ], $key);
    }
    $assertFeedback([
        'action' => 'send-message',
        'message_type' => 'text',
        'body' => 'Silent feedback contract',
        'silent' => 'yes',
    ], 'feedback.send.silent');
    $assertFeedback([
        'action' => 'send-message',
        'message_type' => 'text',
        'body' => 'Default feedback contract',
    ], 'feedback.send.sent');

    $localMessage = Arr::last(app(MessageState::class)->messages('ari'));
    expect($localMessage)->toBeArray()->toHaveKey('id');
    $assertFeedback([
        'action' => 'edit-message',
        'message' => (string) $localMessage['id'],
        'body' => 'Edited local feedback contract',
    ], 'feedback.message.updated');
    $this->post(route('messages.actions'), [
        'action' => 'edit-message',
        'conversation' => 'ari',
        'message' => 'ari-4',
        'body' => 'Catalog messages remain immutable',
    ])->assertSessionHasErrors([
        'body' => (string) data_get($copy, 'feedback.errors.message_not_editable'),
    ]);

    $assertFeedback([
        'action' => 'start-message-call',
        'call_type' => 'video',
        'recording_consent' => 'no',
    ], 'feedback.call.preflight_opened');
    foreach ([
        ['join', 'feedback.call.connected'],
        ['audio-only', 'feedback.call.audio_only'],
        ['reconnect', 'feedback.call.reconnected'],
        ['captions', 'feedback.call.control_updated'],
    ] as [$control, $key]) {
        $assertFeedback([
            'action' => 'update-message-call',
            'call_control' => $control,
        ], $key);
    }
    $assertFeedback(['action' => 'end-message-call'], 'feedback.call.ended');
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
    $callStage = File::get(app_path('View/Components/MessagingCallStage.php'));
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
        resource_path('views/components/messaging-context.blade.php'),
        resource_path('views/components/messaging-call-stage.blade.php'),
    ] as $path) {
        expect(File::get($path), $path)
            ->not->toContain("__('ui.", "__('messages.")
            ->toContain("__('messaging.");
    }

    $composer = File::get(app_path('View/Components/MessagingComposer.php'));
    $context = File::get(app_path('View/Components/MessagingContext.php'));
    $message = File::get(app_path('View/Components/MessagingMessage.php'));
    $action = File::get(app_path('Actions/PerformMessageAction.php'));

    expect($composer)
        ->toContain("__('messaging.composer.tools.")
        ->not->toContain("__('ui.", "__('messages.", 'Str::headline')
        ->and($context)
        ->toContain("__('messaging.context.controls.", "__('messaging.context.safety.")
        ->not->toContain("__('ui.", "__('messages.", 'Str::headline')
        ->and($message)
        ->toContain("__('messaging.message.types.", "__('messaging.message.reactions.")
        ->not->toContain("__('ui.", "__('messages.", 'Str::headline')
        ->and($callStage)
        ->toContain("__('messaging.call_stage.controls.")
        ->not->toContain("__('ui.", "__('messages.", 'Str::headline')
        ->and($action)
        ->toContain("__('messaging.feedback.")
        ->not->toContain("__('ui.", "__('messages.", 'Str::headline');

    preg_match('/public function startCall\(.*?(?=public function endCall)/s', $state, $callState);
    expect($callState[0] ?? '')
        ->not->toBe('')
        ->toContain("'quality_code' => 'checking'", "'quality_code' => 'stable'")
        ->not->toContain("'quality' =>", "__('ui.", "__('messages.");

    foreach (['ari', 'family_care', 'vingis_walk', 'paws_vet', 'foster_adoption', 'lost_luna', 'trail_tails', 'luna_request'] as $key) {
        expect($catalog)->toContain("__('messaging.conversations.{$key}.purpose')");
    }

    $browser = File::get(base_path('scripts/accessibility-browser-check.mjs'));
    expect($browser)->toContain(
        'englishMessagingCopy',
        'messagingCopy.length === 127',
        "route.path === '/messages' && viewport.width < 832",
        "['delivered', 'read', 'delivered', 'delivered']",
        'contextActionCodes',
        "['pin', 'bell-off', 'archive', 'mail', 'shield-minus', 'ban', 'download']",
        'callCopy.length === 22',
        "['video', 'preflight', 'checking']",
        "['mic', 'video', 'captions', 'phone']",
        'call dialog did not focus its first close control',
        'call controls below 44px',
        'call control labels are clipped',
        'call footer is not reachable by dialog scrolling',
        'page-identity-messages-call-',
        'details route marker is missing',
        'conversation details are hidden',
        'details controls below 44px',
        'page-identity-messages-details-',
        'threadActionTitles',
        'messageListLabel',
        'English messaging system fallback remains.',
        'messaging folder labels are clipped',
        'messaging controls below 44px',
    );
});
