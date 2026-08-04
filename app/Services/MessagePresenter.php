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
        $call = $this->state->call($selectedKey);
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
                    ['name' => __('messages.mia_carter_0e5b29cc3b'), 'role' => __('messages.owner_4b1b8aa360'), 'pet' => implode(', ', $selected['pet_names'])],
                    ['name' => $selected['name'], 'role' => $selected['role'], 'pet' => $selected['pet']],
                ],
            ),
            'context' => $this->context($selected),
            'poll' => $this->poll($selectedKey),
            'tasks' => $this->tasks($selectedKey),
            'professional' => $this->professional($selected),
            'call' => $call === null ? null : [
                ...$call,
                'type_label' => Str::headline((string) $call['type']),
                'status_label' => Str::headline((string) $call['status']),
            ],
            'call_boundary' => [
                'transport' => __('messages.local_preflight_and_call_session_controls_are_active_a_r_4c9879ef87'),
                'recording' => __('messages.recording_never_starts_silently_and_is_unavailable_witho_28696294de'),
                'emergency' => __('messages.calls_and_chats_are_not_emergency_veterinary_services_2f8969f9de'),
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
     * @param  array<string, mixed>  $conversation
     * @return array<string, mixed>
     */
    private function context(array $conversation): array
    {
        return [
            'identity_note' => __('messages.messages_are_always_sent_by_a_person_pet_profiles_only_p_0956a55d9e'),
            'linked_pets' => $conversation['pet_names'],
            'shared_cards' => [
                [
                    'icon' => 'paw-print',
                    'label' => __('messages.pet_profiles_6d3a4fd8d3'),
                    'value' => trans_choice('presentation.linked_count', count($conversation['pet_names']), [
                        'count' => count($conversation['pet_names']),
                    ]),
                ],
                ['icon' => 'map-pinned', 'label' => __('messages.places_eb5cfb7367'), 'value' => $conversation['type'] === 'event' ? __('messages.1_private_point_a6bf2bc8ef') : __('messages.public_places_only_4c3f5db83b')],
                ['icon' => 'calendar-days', 'label' => __('messages.events_8d14f6e72d'), 'value' => in_array($conversation['type'], ['event', 'search'], true) ? __('messages.1_active_82e489fddb') : __('messages.create_from_chat_676cd19bbf')],
                ['icon' => 'files', 'label' => __('messages.files_abc7e98928'), 'value' => $conversation['professional'] ? __('messages.time_limited_access_d0477c64cf') : __('messages.scanned_before_access_b8816f1cac')],
            ],
            'safety' => [
                ['icon' => 'shield-check', 'title' => __('messages.private_by_default_f52e06762e'), 'description' => __('messages.phone_email_home_address_exact_location_medical_history__000cb043e1')],
                ['icon' => 'map-pin-off', 'title' => __('messages.location_expires_b719177905'), 'description' => __('messages.temporary_location_sharing_requires_chosen_recipients_an_0aa88fb3cd')],
                ['icon' => 'triangle-alert', 'title' => __('messages.report_with_context_42f5adb71a'), 'description' => __('messages.a_report_can_include_selected_messages_media_a_call_or_t_39bc694fe4')],
            ],
            'media_sections' => [
                ['label' => __('messages.photos_5e3147ab51'), 'count' => 2],
                ['label' => __('messages.video_d534be829e'), 'count' => 1],
                ['label' => __('messages.audio_bc1b88907d'), 'count' => 1],
                ['label' => __('messages.documents_b4e929d8bc'), 'count' => $conversation['professional'] ? 1 : 0],
                ['label' => __('messages.places_eb5cfb7367'), 'count' => 1],
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
            'status' => $conversation['key'] === 'paws-vet' ? __('messages.waiting_for_client_photo_0bc37e1318') : __('messages.visit_scheduled_63d6c02ec9'),
            'hours' => __('messages.mon_sat_08_00_20_00_3d4b4879fc'),
            'assigned' => $conversation['key'] === 'paws-vet' ? __('messages.dr_emilia_vaitke_a0f21f8b96') : __('messages.adoption_team_9b86634596'),
            'queue' => __('messages.assigned_no_advertising_consent_4dd184f146'),
            'privacy' => __('messages.internal_notes_are_visually_separate_and_never_rendered__407a8b56ec'),
            'urgent' => __('messages.if_life_may_be_at_risk_do_not_wait_for_a_chat_or_video_c_373ae0bc7e'),
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
            'question' => __('messages.when_should_the_next_calm_walk_start_2807110e76'),
            'selected' => $this->state->pollSelection($conversation),
            'options' => [
                ['key' => 'saturday-morning', 'label' => __('messages.saturday_morning_9c8d80b4eb'), 'votes' => 6],
                ['key' => 'saturday-evening', 'label' => __('messages.saturday_evening_58938b1a0b'), 'votes' => 3],
                ['key' => 'sunday-morning', 'label' => __('messages.sunday_morning_d0cc9cb260'), 'votes' => 4],
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
                ['key' => 'evening-walk', 'label' => __('messages.evening_walk_51fede72ad'), 'status' => 'assigned', 'owner' => __('messages.alex_db74c940d4')],
                ['key' => 'buy-food', 'label' => __('messages.buy_scout_s_food_d4ac46fe53'), 'status' => 'in-progress', 'owner' => __('messages.mia_4150950870')],
            ],
            'lost-luna' => [
                ['key' => 'sector-c', 'label' => __('messages.check_sector_c_235e386018'), 'status' => 'completed', 'owner' => __('messages.tomas_86c496b088')],
            ],
            'paws-vet' => [
                ['key' => 'photo-before-friday', 'label' => __('messages.send_one_clear_photo_6f132e5bb7'), 'status' => 'assigned', 'owner' => __('messages.mia_4150950870')],
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
                'status_label' => Str::headline($status),
            ];
        }, $tasks);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function coverage(): array
    {
        return [
            ['label' => __('messages.available_now_2a4729fa76'), 'value' => __('messages.personal_requests_rich_messages_group_event_family_profe_c82506fb3d')],
            ['label' => __('messages.provider_boundary_18eba0c102'), 'value' => __('messages.realtime_webrtc_transport_media_storage_malware_scanning_12fbdb85a3')],
            ['label' => __('messages.privacy_baseline_51c53e36f3'), 'value' => __('messages.people_remain_accountable_senders_linked_pets_never_reve_6fc5efbaeb')],
            ['label' => __('messages.accessibility_d3368cbffe'), 'value' => __('messages.keyboard_controls_text_statuses_captions_transcripts_sur_857da2cf45')],
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
