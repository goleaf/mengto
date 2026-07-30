<?php

namespace App\Services;

use Illuminate\Support\Str;

final class PawCircleMessagePresenter
{
    public function __construct(
        private readonly PawCircleMessageCatalog $catalog,
        private readonly PawCircleMessageState $state,
        private readonly PawCircleProfilePresenter $profiles,
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
        $call = $this->state->call($selectedKey);

        return [
            'owner' => $this->profiles->owner(),
            'summary' => [
                'eyebrow' => 'Private communication',
                'title' => 'Messages and calls',
                'description' => 'Talk to pet people, family, specialists, groups, and event organizers without exposing personal contact details.',
                'count' => count($conversations).' dialogs · '.$this->unreadCount($conversations).' unread',
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
            ],
            'messages' => $messages,
            'channels' => $this->catalog->channels()[$selectedKey] ?? [],
            'active_channel' => (string) ($filters['channel'] ?? $selected['channel']),
            'members' => $this->catalog->members()[$selectedKey] ?? [
                ['name' => 'Mia Carter', 'role' => 'Owner', 'pet' => implode(', ', $selected['pet_names'])],
                ['name' => $selected['name'], 'role' => $selected['role'], 'pet' => $selected['pet']],
            ],
            'context' => $this->context($selected),
            'poll' => $this->poll($selectedKey),
            'tasks' => $this->tasks($selectedKey),
            'professional' => $this->professional($selected),
            'call' => $call,
            'call_boundary' => [
                'transport' => 'Local preflight and call-session controls are active. A realtime WebRTC provider is not connected in this prototype.',
                'recording' => 'Recording never starts silently and is unavailable without explicit consent and a storage provider.',
                'emergency' => 'Calls and chats are not emergency veterinary services.',
            ],
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
            ['key' => 'all', 'label' => 'All', 'icon' => 'inbox'],
            ['key' => 'unread', 'label' => 'Unread', 'icon' => 'mail'],
            ['key' => 'friends', 'label' => 'Friends', 'icon' => 'user-round'],
            ['key' => 'groups', 'label' => 'Groups', 'icon' => 'users-round'],
            ['key' => 'events', 'label' => 'Events', 'icon' => 'calendar-days'],
            ['key' => 'specialists', 'label' => 'Specialists', 'icon' => 'badge-check'],
            ['key' => 'family', 'label' => 'Family', 'icon' => 'house'],
            ['key' => 'requests', 'label' => 'Requests', 'icon' => 'message-square-more'],
            ['key' => 'archived', 'label' => 'Archive', 'icon' => 'archive'],
        ];
    }

    /**
     * @param  array<string, mixed>  $conversation
     * @return array<string, mixed>
     */
    private function context(array $conversation): array
    {
        return [
            'identity_note' => 'Messages are always sent by a person. Pet profiles only provide context.',
            'linked_pets' => $conversation['pet_names'],
            'shared_cards' => [
                ['icon' => 'paw-print', 'label' => 'Pet profiles', 'value' => count($conversation['pet_names']).' linked'],
                ['icon' => 'map-pinned', 'label' => 'Places', 'value' => $conversation['type'] === 'event' ? '1 private point' : 'Public places only'],
                ['icon' => 'calendar-days', 'label' => 'Events', 'value' => in_array($conversation['type'], ['event', 'search'], true) ? '1 active' : 'Create from chat'],
                ['icon' => 'files', 'label' => 'Files', 'value' => $conversation['professional'] ? 'Time-limited access' : 'Scanned before access'],
            ],
            'safety' => [
                ['icon' => 'shield-check', 'title' => 'Private by default', 'description' => 'Phone, email, home address, exact location, medical history, and payment data are not exposed automatically.'],
                ['icon' => 'map-pin-off', 'title' => 'Location expires', 'description' => 'Temporary location sharing requires chosen recipients, an end time, and a visible stop control.'],
                ['icon' => 'triangle-alert', 'title' => 'Report with context', 'description' => 'A report can include selected messages, media, a call, or the surrounding sequence without publishing it.'],
            ],
            'media_sections' => [
                ['label' => 'Photos', 'count' => 2],
                ['label' => 'Video', 'count' => 1],
                ['label' => 'Audio', 'count' => 1],
                ['label' => 'Documents', 'count' => $conversation['professional'] ? 1 : 0],
                ['label' => 'Places', 'count' => 1],
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
            'status' => $conversation['key'] === 'paws-vet' ? 'Waiting for client photo' : 'Visit scheduled',
            'hours' => 'Mon-Sat · 08:00-20:00',
            'assigned' => $conversation['key'] === 'paws-vet' ? 'Dr. Emilia Vaitke' : 'Adoption team',
            'queue' => 'Assigned · no advertising consent',
            'privacy' => 'Internal notes are visually separate and never rendered to the client.',
            'urgent' => 'If life may be at risk, do not wait for a chat or video consultation. Contact a local emergency clinic.',
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
            'question' => 'When should the next calm walk start?',
            'selected' => $this->state->pollSelection($conversation),
            'options' => [
                ['key' => 'saturday-morning', 'label' => 'Saturday morning', 'votes' => 6],
                ['key' => 'saturday-evening', 'label' => 'Saturday evening', 'votes' => 3],
                ['key' => 'sunday-morning', 'label' => 'Sunday morning', 'votes' => 4],
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
                ['key' => 'evening-walk', 'label' => 'Evening walk', 'status' => 'assigned', 'owner' => 'Alex'],
                ['key' => 'buy-food', 'label' => 'Buy Scout\'s food', 'status' => 'in-progress', 'owner' => 'Mia'],
            ],
            'lost-luna' => [
                ['key' => 'sector-c', 'label' => 'Check sector C', 'status' => 'completed', 'owner' => 'Tomas'],
            ],
            'paws-vet' => [
                ['key' => 'photo-before-friday', 'label' => 'Send one clear photo', 'status' => 'assigned', 'owner' => 'Mia'],
            ],
            default => [],
        };

        return array_map(fn (array $task): array => [
            ...$task,
            'status' => $this->state->taskStatus($conversation, $task['key'], $task['status']),
        ], $tasks);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function coverage(): array
    {
        return [
            ['label' => 'Available now', 'value' => 'Personal requests, rich messages, group/event/family/professional contexts, moderation, call preflight'],
            ['label' => 'Provider boundary', 'value' => 'Realtime WebRTC transport, media storage, malware scanning, transcription, translation, and end-to-end encryption'],
            ['label' => 'Privacy baseline', 'value' => 'People remain accountable senders; linked pets never reveal medical or location data automatically'],
            ['label' => 'Accessibility', 'value' => 'Keyboard controls, text statuses, captions/transcripts surfaces, reduced motion, and non-color indicators'],
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
