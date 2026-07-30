<?php

namespace App\Actions;

use App\Services\MessageCatalog;
use App\Services\MessageState;
use Illuminate\Validation\ValidationException;

final class PerformMessageAction
{
    public function __construct(
        private readonly MessageCatalog $catalog,
        private readonly MessageState $state,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    public function handle(array $data): array
    {
        $action = (string) $data['action'];
        $conversation = (string) $data['conversation'];
        $item = $this->catalog->conversations()[$conversation] ?? null;

        if ($item === null) {
            throw ValidationException::withMessages(['conversation' => 'This conversation is unavailable.']);
        }

        $message = match ($action) {
            'send-message' => $this->send($conversation, $data),
            'accept-message-request' => $this->resolveRequest($conversation, 'accepted', 'Message request accepted.'),
            'decline-message-request' => $this->resolveRequest($conversation, 'declined', 'Message request declined.'),
            'archive-conversation' => $this->toggleConversation($conversation, 'archived', 'Conversation archived.', 'Conversation restored.'),
            'pin-conversation' => $this->toggleConversation($conversation, 'pinned', 'Conversation pinned.', 'Conversation unpinned.'),
            'mute-conversation' => $this->toggleConversation($conversation, 'muted', 'Conversation muted.', 'Conversation notifications restored.'),
            'mark-conversation-unread' => $this->markUnread($conversation),
            'block-conversation' => $this->toggleConversation($conversation, 'blocked', 'Sender and managed profiles blocked.', 'Sender unblocked.'),
            'restrict-conversation' => $this->toggleConversation($conversation, 'restricted', 'Conversation restricted.', 'Conversation restriction removed.'),
            'set-message-notifications' => $this->setNotifications($conversation, $data),
            'react-message' => $this->react($data),
            'pin-message' => $this->toggleMessageFlag($data, 'pinned_messages', 'Message pinned.', 'Message unpinned.'),
            'bookmark-message' => $this->toggleMessageFlag($data, 'bookmarked_messages', 'Saved to private bookmarks.', 'Removed from bookmarks.'),
            'edit-message' => $this->edit($data),
            'delete-message-self' => $this->delete($data, 'self'),
            'delete-message-everyone' => $this->delete($data, 'everyone'),
            'report-message' => $this->report($conversation, $data),
            'vote-chat-poll' => $this->vote($conversation, $data),
            'update-chat-task' => $this->updateTask($conversation, $data),
            'start-message-call' => $this->startCall($conversation, $data),
            'update-message-call' => $this->updateCall($conversation, $data),
            'end-message-call' => $this->endCall($conversation),
            'export-conversation' => 'Export request prepared. Only your accessible data will be included.',
            default => throw ValidationException::withMessages(['action' => 'This messaging action is unavailable.']),
        };

        return [
            'message' => $message,
            'route' => 'pet-social.messages.index',
            'parameters' => array_filter([
                'conversation' => $conversation,
                'filter' => (string) ($data['return_filter'] ?? 'all'),
                'panel' => str_contains($action, 'call') ? 'call' : null,
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function send(string $conversation, array $data): string
    {
        $requestStatus = $this->state->requestStatuses()[$conversation] ?? 'accepted';

        if ($requestStatus === 'pending') {
            throw ValidationException::withMessages([
                'body' => 'Accept this message request before replying or sharing media.',
            ]);
        }

        if (($this->state->conversation($conversation)['blocked'] ?? false) === true) {
            throw ValidationException::withMessages([
                'body' => 'Unblock this conversation before sending a message.',
            ]);
        }

        $this->state->addMessage($conversation, $data);
        $type = (string) ($data['message_type'] ?? 'text');

        return match ($type) {
            'audio' => 'Audio message added with a transcription request.',
            'image' => 'Photo added. Location metadata is excluded by default.',
            'video' => 'Video added to the resumable upload queue.',
            'file' => 'Document added to the security scan queue.',
            'pet' => 'Pet profile card shared with public fields only.',
            'place' => 'Place card shared without a private home location.',
            'event' => 'Event card shared in the conversation.',
            'task' => 'Shared task added.',
            default => ($data['silent'] ?? 'no') === 'yes'
                ? 'Message sent without a sound notification.'
                : 'Message sent.',
        };
    }

    private function resolveRequest(string $conversation, string $status, string $message): string
    {
        $this->state->resolveRequest($conversation, $status);

        return $message;
    }

    private function toggleConversation(
        string $conversation,
        string $flag,
        string $enabled,
        string $disabled,
    ): string {
        return $this->state->toggleConversation($conversation, $flag) ? $enabled : $disabled;
    }

    private function markUnread(string $conversation): string
    {
        $this->state->setConversationValue($conversation, 'unread', true);

        return 'Conversation marked unread for you.';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function setNotifications(string $conversation, array $data): string
    {
        $level = (string) $data['notification_level'];
        $this->state->setConversationValue($conversation, 'notification_level', $level);

        return 'Conversation notifications set to '.$level.'.';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function react(array $data): string
    {
        $active = $this->state->react((string) $data['message'], (string) $data['reaction']);

        return $active ? 'Reaction added.' : 'Reaction removed.';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function toggleMessageFlag(array $data, string $flag, string $enabled, string $disabled): string
    {
        return $this->state->toggleMessageFlag((string) $data['message'], $flag) ? $enabled : $disabled;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function edit(array $data): string
    {
        if (! $this->state->editMessage((string) $data['message'], (string) $data['body'])) {
            throw ValidationException::withMessages([
                'body' => 'Only a message sent in this session can be edited.',
            ]);
        }

        return 'Message updated with an edited label.';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function delete(array $data, string $scope): string
    {
        $this->state->deleteMessage((string) $data['message'], $scope);

        return $scope === 'everyone' ? 'Message removed for everyone.' : 'Message removed only for you.';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function report(string $conversation, array $data): string
    {
        $this->state->addReport([
            'conversation' => $conversation,
            'message' => (string) $data['message'],
            'reason' => (string) $data['report_reason'],
            'body' => trim((string) $data['body']),
            'created_at' => now()->toAtomString(),
        ]);

        return 'Report submitted with the selected message context.';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function vote(string $conversation, array $data): string
    {
        $this->state->vote($conversation, (string) $data['poll_option']);

        return 'Poll response recorded.';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function updateTask(string $conversation, array $data): string
    {
        $this->state->updateTask(
            $conversation,
            (string) $data['task'],
            (string) $data['task_status'],
        );

        return 'Shared task status updated.';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function startCall(string $conversation, array $data): string
    {
        $this->state->startCall(
            $conversation,
            (string) $data['call_type'],
            ($data['recording_consent'] ?? 'no') === 'yes',
        );

        return 'Call preflight opened. Realtime media starts only after device permission and provider connection.';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function updateCall(string $conversation, array $data): string
    {
        $control = (string) $data['call_control'];
        $this->state->updateCall($conversation, $control);

        return match ($control) {
            'join' => 'Call session connected in prototype mode.',
            'audio-only' => 'Call switched to audio-only mode.',
            'reconnect' => 'Call session reconnected.',
            default => 'Call control updated.',
        };
    }

    private function endCall(string $conversation): string
    {
        $this->state->endCall($conversation);

        return 'Call ended and temporary device access released.';
    }
}
