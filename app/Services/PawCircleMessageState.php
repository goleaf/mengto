<?php

namespace App\Services;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Str;

final class PawCircleMessageState
{
    private const SESSION_KEY = 'paw-circle.messaging.v1';

    public function __construct(private readonly Session $session) {}

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
                body: 'Message request accepted. You can now share messages and request a call.',
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
            'quality' => 'Checking connection',
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
            'join' => [...$call, 'status' => 'connected', 'quality' => 'Connection stable'],
            'microphone' => [...$call, 'microphone' => ! $call['microphone']],
            'camera' => [...$call, 'camera' => ! $call['camera']],
            'captions' => [...$call, 'captions' => ! $call['captions']],
            'audio-only' => [...$call, 'type' => 'audio', 'camera' => false, 'quality' => 'Audio only'],
            'reconnect' => [...$call, 'status' => 'connected', 'quality' => 'Reconnected'],
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
                'body' => 'Message deleted',
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
        return $this->session->get(self::SESSION_KEY, [
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
        $this->session->put(self::SESSION_KEY, $state);
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
            'sender' => $mine ? 'Mia Carter' : 'PawCircle',
            'time' => 'Now',
            'datetime' => now()->toAtomString(),
            'body' => $body,
            'mine' => $mine,
            'type' => $type,
            'meta' => null,
            'reply' => null,
            'edited' => false,
            'status' => $mine ? 'Sent' : 'Delivered',
            'reactions' => [],
        ];
    }

    private function messageMeta(string $type): ?string
    {
        return match ($type) {
            'audio' => 'Audio message · 0:18 · transcript requested',
            'image' => 'Photo · location metadata removed',
            'video' => 'Video · captions can be added',
            'file' => 'Document · virus scan required before download',
            'pet' => 'Pet profile card · public fields only',
            'place' => 'Place card · exact home location excluded',
            'event' => 'Event card · registration status private',
            'task' => 'Shared task · awaiting owner',
            default => null,
        };
    }
}
