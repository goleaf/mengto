<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

final class MessageState
{
    private const STATE_NAMESPACE = 'messaging.state.v1';

    public function __construct(private readonly PersistentStateStore $states) {}

    /**
     * @return array<string, mixed>
     */
    public function conversation(string $key): array
    {
        return $this->state()['conversations'][$key] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function messages(string $conversation): array
    {
        return array_values(array_filter(
            $this->state()['messages'],
            static fn (array $message): bool => ($message['conversation'] ?? '') === $conversation,
        ));
    }

    /**
     * @return array<string, string>
     */
    public function requestStatuses(): array
    {
        return $this->state()['requests'];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function call(string $conversation): ?array
    {
        return $this->state()['calls'][$conversation] ?? null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function reports(): array
    {
        return $this->state()['reports'];
    }

    public function pollSelection(string $conversation): ?string
    {
        return $this->state()['polls'][$conversation] ?? null;
    }

    public function taskStatus(string $conversation, string $task, string $default): string
    {
        return $this->state()['tasks'][$conversation][$task] ?? $default;
    }

    public function markRead(string $conversation): void
    {
        $state = $this->state();
        $state['conversations'][$conversation]['unread'] = false;
        $this->store($state);
    }

    public function toggleConversation(string $conversation, string $flag): bool
    {
        $state = $this->state();
        $current = (bool) ($state['conversations'][$conversation][$flag] ?? false);
        $state['conversations'][$conversation][$flag] = ! $current;
        $this->store($state);

        return ! $current;
    }

    public function setConversationValue(string $conversation, string $key, string|bool $value): void
    {
        $state = $this->state();
        $state['conversations'][$conversation][$key] = $value;
        $this->store($state);
    }

    public function resolveRequest(string $conversation, string $status): void
    {
        $state = $this->state();
        $state['requests'][$conversation] = $status;
        $state['conversations'][$conversation]['unread'] = false;

        if ($status === 'accepted') {
            $state['messages'][] = $this->newMessage(
                conversation: $conversation,
                body: __('messages.message_request_accepted_you_can_now_share_messages_and__474a282278'),
                type: 'system',
                mine: false,
            );
        }

        $this->store($state);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addMessage(string $conversation, array $data): string
    {
        $state = $this->state();
        $id = 'local-'.Str::lower(Str::random(10));
        $state['messages'][] = [
            ...$this->newMessage(
                conversation: $conversation,
                body: trim((string) ($data['body'] ?? '')),
                type: (string) ($data['message_type'] ?? 'text'),
                mine: true,
                id: $id,
            ),
            'meta' => $this->messageMeta((string) ($data['message_type'] ?? 'text')),
            'reply' => trim((string) ($data['reply_to'] ?? '')) ?: null,
            'silent' => ($data['silent'] ?? 'no') === 'yes',
            'scheduled_for' => trim((string) ($data['scheduled_for'] ?? '')) ?: null,
        ];
        $state['messages'] = array_slice($state['messages'], -120);
        $this->store($state);

        return $id;
    }

    public function react(string $message, string $reaction): bool
    {
        $state = $this->state();
        $current = $state['reactions'][$message] ?? null;

        if ($current === $reaction) {
            unset($state['reactions'][$message]);
            $active = false;
        } else {
            $state['reactions'][$message] = $reaction;
            $active = true;
        }

        $this->store($state);

        return $active;
    }

    public function toggleMessageFlag(string $message, string $flag): bool
    {
        $state = $this->state();
        $items = $state[$flag] ?? [];

        if (in_array($message, $items, true)) {
            $state[$flag] = array_values(array_filter(
                $items,
                static fn (string $item): bool => $item !== $message,
            ));
            $active = false;
        } else {
            $items[] = $message;
            $state[$flag] = array_values(array_unique($items));
            $active = true;
        }

        $this->store($state);

        return $active;
    }

    public function editMessage(string $message, string $body): bool
    {
        $state = $this->state();

        foreach ($state['messages'] as &$item) {
            if (($item['id'] ?? '') !== $message || ! ($item['mine'] ?? false)) {
                continue;
            }

            $item['body'] = trim($body);
            $item['edited'] = true;
            $this->store($state);

            return true;
        }

        return false;
    }

    public function deleteMessage(string $message, string $scope): void
    {
        $state = $this->state();
        $key = $scope === 'everyone' ? 'deleted_everyone' : 'deleted_self';
        $state[$key][] = $message;
        $state[$key] = array_values(array_unique($state[$key]));
        $this->store($state);
    }

    /**
     * @param  array<string, mixed>  $report
     */
    public function addReport(array $report): void
    {
        $state = $this->state();
        array_unshift($state['reports'], $report);
        $state['reports'] = array_slice($state['reports'], 0, 30);
        $this->store($state);
    }

    public function vote(string $conversation, string $option): void
    {
        $state = $this->state();
        $state['polls'][$conversation] = $option;
        $this->store($state);
    }

    public function updateTask(string $conversation, string $task, string $status): void
    {
        $state = $this->state();
        $state['tasks'][$conversation][$task] = $status;
        $this->store($state);
    }

    public function startCall(string $conversation, string $type, bool $recordingConsent): void
    {
        $state = $this->state();
        $state['calls'][$conversation] = [
            'id' => 'call-'.Str::lower(Str::random(8)),
            'type' => $type,
            'status' => 'preflight',
            'started_at' => now()->toAtomString(),
            'recording_consent' => $recordingConsent,
            'microphone' => true,
            'camera' => $type === 'video',
            'captions' => false,
            'quality' => __('messages.checking_connection_537585d95f'),
        ];
        $this->store($state);
    }

    public function updateCall(string $conversation, string $control): void
    {
        $state = $this->state();
        $call = $state['calls'][$conversation] ?? null;

        if ($call === null) {
            return;
        }

        $call = match ($control) {
            'join' => [...$call, 'status' => 'connected', 'quality' => __('messages.connection_stable_0fc2aaa131')],
            'microphone' => [...$call, 'microphone' => ! $call['microphone']],
            'camera' => [...$call, 'camera' => ! $call['camera']],
            'captions' => [...$call, 'captions' => ! $call['captions']],
            'audio-only' => [...$call, 'type' => 'audio', 'camera' => false, 'quality' => __('messages.audio_only_224b45b631')],
            'reconnect' => [...$call, 'status' => 'connected', 'quality' => __('messages.reconnected_20a447dbc6')],
            default => $call,
        };

        $state['calls'][$conversation] = $call;
        $this->store($state);
    }

    public function endCall(string $conversation): void
    {
        $state = $this->state();
        $call = $state['calls'][$conversation] ?? null;

        if ($call !== null) {
            $state['call_history'][] = [
                ...$call,
                'conversation' => $conversation,
                'status' => 'ended',
                'ended_at' => now()->toAtomString(),
            ];
        }

        unset($state['calls'][$conversation]);
        $this->store($state);
    }

    /**
     * @return array<string, mixed>
     */
    public function decorateMessage(array $message): array
    {
        $state = $this->state();
        $id = (string) ($message['id'] ?? '');

        if (in_array($id, $state['deleted_self'], true)) {
            return [...$message, 'hidden' => true];
        }

        if (in_array($id, $state['deleted_everyone'], true)) {
            return [
                ...$message,
                'body' => __('messages.message_deleted_7e94d4b9a4'),
                'type' => 'deleted',
                'meta' => null,
                'reply' => null,
                'deleted' => true,
            ];
        }

        return [
            ...$message,
            'hidden' => false,
            'reaction' => $state['reactions'][$id] ?? null,
            'pinned' => in_array($id, $state['pinned_messages'], true),
            'bookmarked' => in_array($id, $state['bookmarked_messages'], true),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function state(): array
    {
        return $this->states->get(self::STATE_NAMESPACE, [
            'conversations' => [],
            'requests' => ['luna-request' => 'pending'],
            'messages' => [],
            'reactions' => [],
            'pinned_messages' => [],
            'bookmarked_messages' => [],
            'deleted_self' => [],
            'deleted_everyone' => [],
            'polls' => [],
            'tasks' => [],
            'calls' => [],
            'call_history' => [],
            'reports' => [],
        ]);
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function store(array $state): void
    {
        $this->states->put(self::STATE_NAMESPACE, $state);
    }

    /**
     * @return array<string, mixed>
     */
    private function newMessage(
        string $conversation,
        string $body,
        string $type,
        bool $mine,
        ?string $id = null,
    ): array {
        return [
            'id' => $id ?? 'system-'.Str::lower(Str::random(10)),
            'conversation' => $conversation,
            'sender' => $mine ? __('messages.mia_carter_0e5b29cc3b') : __('messages.brand_name'),
            'time' => __('messages.now_fe18013d93'),
            'datetime' => now()->toAtomString(),
            'body' => $body,
            'mine' => $mine,
            'type' => $type,
            'meta' => null,
            'reply' => null,
            'edited' => false,
            'status' => $mine
                ? __('messages.message.sent')
                : __('messages.message.delivered'),
            'status_code' => $mine ? 'sent' : 'delivered',
            'reactions' => [],
        ];
    }

    private function messageMeta(string $type): ?string
    {
        return match ($type) {
            'audio' => __('messages.audio_message_0_18_transcript_requested_84dedac6db'),
            'image' => __('messages.photo_location_metadata_removed_ae2cf1fc7f'),
            'video' => __('messages.video_captions_can_be_added_49b10f94a3'),
            'file' => __('messages.document_virus_scan_required_before_download_2fa77c9312'),
            'pet' => __('messages.pet_profile_card_public_fields_only_ed899af941'),
            'place' => __('messages.place_card_exact_home_location_excluded_fbc22c1fa7'),
            'event' => __('messages.event_card_registration_status_private_4ec236ba31'),
            'task' => __('messages.shared_task_awaiting_owner_9df29e4ca5'),
            default => null,
        };
    }
}
