<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

final class MessagePresenter
{
    public function __construct(
        private readonly MessageCatalog $catalog,
        private readonly MessageState $state,
        private readonly ProfilePresenter $profiles,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function page(array $filters, bool $detailsOpen = false): array
    {
        $catalog = $this->catalog->conversations();
        $selectedKey = (string) ($filters['conversation'] ?? 'ari');
        $selectedKey = isset($catalog[$selectedKey]) ? $selectedKey : 'ari';
        $this->state->markRead($selectedKey);
        $conversations = $this->presentConversations($catalog, $selectedKey);
        $activeFilter = (string) ($filters['filter'] ?? 'all');
        $query = Str::lower(trim((string) ($filters['q'] ?? '')));
        $visibleConversations = array_values(array_filter(
            $conversations,
            fn (array $conversation): bool => $this->matchesConversation(
                $conversation,
                $activeFilter,
                $query,
            ),
        ));
        $selected = $catalog[$selectedKey];
        $messages = $this->presentMessages($selectedKey, (string) ($filters['message_q'] ?? ''));
        $requestStatus = $this->state->requestStatuses()[$selectedKey] ?? 'accepted';
        $conversationState = $this->state->conversation($selectedKey);
        $call = $this->presentCall($this->state->call($selectedKey));
        $detailsUrl = route('messages.details', ['conversation' => $selectedKey]);

        return [
            'owner' => $this->profiles->owner(),
            'summary' => [
                'eyebrow' => __('messaging.page.eyebrow'),
                'title' => __('messaging.page.heading'),
                'description' => __('messaging.page.description'),
                'count' => __('presentation.dialogs_with_unread', [
                    'dialogs' => trans_choice('presentation.dialogs_count', count($conversations), [
                        'count' => count($conversations),
                    ]),
                    'unread' => __('presentation.unread_count', [
                        'count' => $this->unreadCount($conversations),
                    ]),
                ]),
                'unread_count' => $this->unreadCount($conversations),
                'request_count' => count(array_filter(
                    $this->state->requestStatuses(),
                    static fn (string $status): bool => $status === 'pending',
                )),
            ],
            'filters' => $this->filters(),
            'active_filter' => $activeFilter,
            'query' => (string) ($filters['q'] ?? ''),
            'message_query' => (string) ($filters['message_q'] ?? ''),
            'conversations' => $visibleConversations,
            'selected' => [
                ...$selected,
                'request_status' => $requestStatus,
                'accepted' => ! $selected['request'] || $requestStatus === 'accepted',
                'archived' => (bool) ($conversationState['archived'] ?? false),
                'pinned' => (bool) ($conversationState['pinned'] ?? false),
                'muted' => (bool) ($conversationState['muted'] ?? false),
                'blocked' => (bool) ($conversationState['blocked'] ?? false),
                'restricted' => (bool) ($conversationState['restricted'] ?? false),
                'notification_level' => (string) ($conversationState['notification_level'] ?? 'all'),
                'details_url' => $detailsUrl,
                'media_target' => [
                    'url' => $detailsUrl,
                    'label' => __('presentation.open_conversation', ['name' => $selected['name']]),
                ],
            ],
            'messages' => $messages,
            'channels' => $this->catalog->channels()[$selectedKey] ?? [],
            'active_channel' => (string) ($filters['channel'] ?? $selected['channel']),
            'members' => array_map(
                static fn (array $member): array => [
                    ...$member,
                    'initial' => Str::substr((string) $member['name'], 0, 1),
                ],
                $this->catalog->members()[$selectedKey] ?? [
                    ['name' => __('messages.mia_carter'), 'role' => __('messaging.context.roles.owner'), 'pet' => implode(', ', $selected['pet_names'])],
                    ['name' => $selected['name'], 'role' => $selected['role'], 'pet' => $selected['pet']],
                ],
            ),
            'context' => $this->context($selected),
            'poll' => $this->poll($selectedKey),
            'tasks' => $this->tasks($selectedKey),
            'professional' => $this->professional($selected),
            'call' => $call,
            'call_boundary' => $this->callBoundary(),
            'panel' => (string) ($filters['panel'] ?? ($detailsOpen ? 'context' : '')),
            'details_open' => $detailsOpen,
            'thread_first' => isset($filters['conversation']) || $detailsOpen,
            'reports_count' => count($this->state->reports()),
            'coverage' => $this->coverage(),
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $catalog
     * @return array<int, array<string, mixed>>
     */
    private function presentConversations(array $catalog, string $selectedKey): array
    {
        $requestStatuses = $this->state->requestStatuses();

        return array_map(function (array $conversation) use ($selectedKey, $requestStatuses): array {
            $state = $this->state->conversation($conversation['key']);
            $requestStatus = $requestStatuses[$conversation['key']] ?? 'accepted';
            $unread = (bool) ($state['unread'] ?? false)
                ? max(1, $conversation['unread'])
                : $conversation['unread'];

            if ($conversation['key'] === $selectedKey || $requestStatus !== 'pending' && $conversation['request']) {
                $unread = 0;
            }

            return [
                ...$conversation,
                'selected' => $conversation['key'] === $selectedKey,
                'unread' => $unread,
                'request_status' => $requestStatus,
                'type_label' => $this->conversationTypeLabel((string) $conversation['type']),
                'archived' => (bool) ($state['archived'] ?? false),
                'pinned' => (bool) ($state['pinned'] ?? false),
                'muted' => (bool) ($state['muted'] ?? false),
                'blocked' => (bool) ($state['blocked'] ?? false),
            ];
        }, array_values($catalog));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function presentMessages(string $conversation, string $query): array
    {
        $messages = [
            ...$this->catalog->messages($conversation),
            ...$this->state->messages($conversation),
        ];
        $messages = array_map(
            fn (array $message): array => $this->state->decorateMessage($message),
            $messages,
        );
        $messages = array_values(array_filter(
            $messages,
            static fn (array $message): bool => ! ($message['hidden'] ?? false),
        ));
        $query = Str::lower(trim($query));

        if ($query === '') {
            return $messages;
        }

        return array_values(array_filter(
            $messages,
            static fn (array $message): bool => Str::contains(
                Str::lower(implode(' ', array_filter([
                    (string) ($message['sender'] ?? ''),
                    (string) ($message['body'] ?? ''),
                    (string) ($message['meta'] ?? ''),
                    (string) ($message['reply'] ?? ''),
                ]))),
                $query,
            ),
        ));
    }

    /**
     * @param  array<string, mixed>  $conversation
     */
    private function matchesConversation(array $conversation, string $filter, string $query): bool
    {
        $matchesFilter = match ($filter) {
            'unread' => $conversation['unread'] > 0,
            'friends' => $conversation['category'] === 'friends',
            'groups' => $conversation['category'] === 'groups',
            'events' => $conversation['category'] === 'events',
            'specialists' => $conversation['category'] === 'specialists',
            'organizations' => $conversation['category'] === 'organizations',
            'family' => $conversation['category'] === 'family',
            'requests' => $conversation['request_status'] === 'pending',
            'archived' => $conversation['archived'],
            default => ! $conversation['archived'],
        };

        if (! $matchesFilter || $query === '') {
            return $matchesFilter;
        }

        return Str::contains(
            Str::lower(implode(' ', [
                $conversation['name'],
                $conversation['pet'],
                $conversation['purpose'],
                $conversation['preview'],
                $conversation['handle'],
            ])),
            $query,
        );
    }

    /**
     * @return array<int, array{key: string, label: string, icon: string}>
     */
    private function filters(): array
    {
        return [
            ['key' => 'all', 'label' => __('messaging.folders.items.all'), 'icon' => 'inbox'],
            ['key' => 'unread', 'label' => __('messaging.folders.items.unread'), 'icon' => 'mail'],
            ['key' => 'friends', 'label' => __('messaging.folders.items.friends'), 'icon' => 'user-round'],
            ['key' => 'groups', 'label' => __('messaging.folders.items.groups'), 'icon' => 'users-round'],
            ['key' => 'events', 'label' => __('messaging.folders.items.events'), 'icon' => 'calendar-days'],
            ['key' => 'specialists', 'label' => __('messaging.folders.items.specialists'), 'icon' => 'badge-check'],
            ['key' => 'family', 'label' => __('messaging.folders.items.family'), 'icon' => 'house'],
            ['key' => 'requests', 'label' => __('messaging.folders.items.requests'), 'icon' => 'message-square-more'],
            ['key' => 'archived', 'label' => __('messaging.folders.items.archived'), 'icon' => 'archive'],
        ];
    }

    private function conversationTypeLabel(string $type): string
    {
        return match ($type) {
            'personal' => __('messaging.types.personal'),
            'family' => __('messaging.types.family'),
            'event' => __('messaging.types.event'),
            'professional' => __('messaging.types.professional'),
            'organization' => __('messaging.types.organization'),
            'search' => __('messaging.types.search'),
            'group' => __('messaging.types.group'),
            'request' => __('messaging.types.request'),
            default => throw new \UnexpectedValueException("Unsupported conversation type [{$type}]."),
        };
    }

    /**
     * @param  array<string, mixed>|null  $call
     * @return array<string, mixed>|null
     */
    private function presentCall(?array $call): ?array
    {
        if ($call === null) {
            return null;
        }

        $typeCode = in_array($call['type'] ?? null, ['audio', 'video'], true)
            ? (string) $call['type']
            : 'audio';
        $statusCode = in_array($call['status'] ?? null, ['preflight', 'connected'], true)
            ? (string) $call['status']
            : 'preflight';
        $qualityCode = in_array($call['quality_code'] ?? null, ['checking', 'stable', 'audio_only', 'reconnected'], true)
            ? (string) $call['quality_code']
            : ($statusCode === 'connected' ? 'stable' : 'checking');

        return [
            ...$call,
            'type' => $typeCode,
            'type_code' => $typeCode,
            'type_label' => match ($typeCode) {
                'video' => __('messaging.call_stage.types.video'),
                default => __('messaging.call_stage.types.audio'),
            },
            'status' => $statusCode,
            'status_code' => $statusCode,
            'status_label' => match ($statusCode) {
                'connected' => __('messaging.call_stage.statuses.connected'),
                default => __('messaging.call_stage.statuses.preflight'),
            },
            'quality_code' => $qualityCode,
            'quality' => match ($qualityCode) {
                'stable' => __('messaging.call_stage.qualities.stable'),
                'audio_only' => __('messaging.call_stage.qualities.audio_only'),
                'reconnected' => __('messaging.call_stage.qualities.reconnected'),
                default => __('messaging.call_stage.qualities.checking'),
            },
        ];
    }

    /**
     * @return array{transport: string, recording: string, emergency: string}
     */
    private function callBoundary(): array
    {
        return [
            'transport' => __('messaging.call_stage.boundary.transport'),
            'recording' => __('messaging.call_stage.boundary.recording'),
            'emergency' => __('messaging.call_stage.boundary.emergency'),
        ];
    }

    /**
     * @param  array<string, mixed>  $conversation
     * @return array<string, mixed>
     */
    private function context(array $conversation): array
    {
        return [
            'identity_note' => __('messaging.context.identity_note'),
            'linked_pets' => $conversation['pet_names'],
            'shared_cards' => [
                [
                    'icon' => 'paw-print',
                    'label' => __('messaging.context.shared.pet_profiles'),
                    'value' => trans_choice('messaging.context.shared.linked_count', count($conversation['pet_names']), [
                        'count' => count($conversation['pet_names']),
                    ]),
                ],
                ['icon' => 'map-pinned', 'label' => __('messaging.context.shared.places'), 'value' => $conversation['type'] === 'event' ? __('messaging.context.shared.private_point') : __('messaging.context.shared.public_places')],
                ['icon' => 'calendar-days', 'label' => __('messaging.context.shared.events'), 'value' => in_array($conversation['type'], ['event', 'search'], true) ? __('messaging.context.shared.active_event') : __('messaging.context.shared.create_from_chat')],
                ['icon' => 'files', 'label' => __('messaging.context.shared.files'), 'value' => $conversation['professional'] ? __('messaging.context.shared.time_limited') : __('messaging.context.shared.scanned')],
            ],
            'safety' => [
                ['icon' => 'shield-check', 'title' => __('messaging.context.safety.private_title'), 'description' => __('messaging.context.safety.private_description')],
                ['icon' => 'map-pin-off', 'title' => __('messaging.context.safety.location_title'), 'description' => __('messaging.context.safety.location_description')],
                ['icon' => 'triangle-alert', 'title' => __('messaging.context.safety.report_title'), 'description' => __('messaging.context.safety.report_description')],
            ],
            'media_sections' => [
                ['label' => __('messages.photos'), 'count' => 2],
                ['label' => __('messages.video'), 'count' => 1],
                ['label' => __('messages.audio'), 'count' => 1],
                ['label' => __('messages.documents'), 'count' => $conversation['professional'] ? 1 : 0],
                ['label' => __('messages.places'), 'count' => 1],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function professional(array $conversation): ?array
    {
        if (! $conversation['professional']) {
            return null;
        }

        return [
            'case' => $conversation['handle'],
            'status' => $conversation['key'] === 'paws-vet' ? __('messaging.context.professional.waiting_photo') : __('messaging.context.professional.visit_scheduled'),
            'hours' => __('messaging.context.professional.working_hours'),
            'assigned' => $conversation['key'] === 'paws-vet' ? __('messages.dr_emilia_vaitke') : __('messaging.context.professional.adoption_team'),
            'queue' => __('messaging.context.professional.queue'),
            'privacy' => __('messaging.context.professional.privacy'),
            'urgent' => __('messaging.context.professional.urgent'),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function poll(string $conversation): ?array
    {
        if (! in_array($conversation, ['vingis-walk', 'trail-tails'], true)) {
            return null;
        }

        return [
            'question' => __('messaging.context.poll.question'),
            'selected' => $this->state->pollSelection($conversation),
            'options' => [
                ['key' => 'saturday-morning', 'label' => __('messaging.context.poll.saturday_morning'), 'votes' => 6],
                ['key' => 'saturday-evening', 'label' => __('messaging.context.poll.saturday_evening'), 'votes' => 3],
                ['key' => 'sunday-morning', 'label' => __('messaging.context.poll.sunday_morning'), 'votes' => 4],
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function tasks(string $conversation): array
    {
        $tasks = match ($conversation) {
            'family-care' => [
                ['key' => 'evening-walk', 'label' => __('messages.evening_walk'), 'status' => 'assigned', 'owner' => __('messages.alex')],
                ['key' => 'buy-food', 'label' => __('messages.buy_scout_s_food'), 'status' => 'in-progress', 'owner' => __('messages.mia')],
            ],
            'lost-luna' => [
                ['key' => 'sector-c', 'label' => __('messages.check_sector_c'), 'status' => 'completed', 'owner' => __('messages.tomas')],
            ],
            'paws-vet' => [
                ['key' => 'photo-before-friday', 'label' => __('messages.send_one_clear_photo'), 'status' => 'assigned', 'owner' => __('messages.mia')],
            ],
            default => [],
        };

        return array_map(function (array $task) use ($conversation): array {
            $status = $this->state->taskStatus(
                $conversation,
                $task['key'],
                $task['status'],
            );

            return [
                ...$task,
                'status' => $status,
                'status_label' => match ($status) {
                    'assigned' => __('messaging.context.tasks.statuses.assigned'),
                    'in-progress' => __('messaging.context.tasks.statuses.in_progress'),
                    'completed' => __('messaging.context.tasks.statuses.completed'),
                    default => __('messaging.context.tasks.statuses.skipped'),
                },
            ];
        }, $tasks);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function coverage(): array
    {
        return [
            ['label' => __('messaging.context.boundary.available_label'), 'value' => __('messaging.context.boundary.available_value')],
            ['label' => __('messaging.context.boundary.provider_label'), 'value' => __('messaging.context.boundary.provider_value')],
            ['label' => __('messaging.context.boundary.privacy_label'), 'value' => __('messaging.context.boundary.privacy_value')],
            ['label' => __('messaging.context.boundary.accessibility_label'), 'value' => __('messaging.context.boundary.accessibility_value')],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $conversations
     */
    private function unreadCount(array $conversations): int
    {
        return array_sum(array_map(
            static fn (array $conversation): int => (int) $conversation['unread'],
            $conversations,
        ));
    }
}
