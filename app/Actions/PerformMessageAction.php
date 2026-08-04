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
            throw ValidationException::withMessages(['conversation' => __('messaging.feedback.errors.conversation_unavailable')]);
        }

        $message = match ($action) {
            'send-message' => $this->send($conversation, $data),
            'accept-message-request' => $this->resolveRequest($conversation, 'accepted', __('messaging.feedback.request.accepted')),
            'decline-message-request' => $this->resolveRequest($conversation, 'declined', __('messaging.feedback.request.declined')),
            'archive-conversation' => $this->toggleConversation($conversation, 'archived', __('messaging.feedback.conversation.archived'), __('messaging.feedback.conversation.restored')),
            'pin-conversation' => $this->toggleConversation($conversation, 'pinned', __('messaging.feedback.conversation.pinned'), __('messaging.feedback.conversation.unpinned')),
            'mute-conversation' => $this->toggleConversation($conversation, 'muted', __('messaging.feedback.conversation.muted'), __('messaging.feedback.conversation.notifications_restored')),
            'mark-conversation-unread' => $this->markUnread($conversation),
            'block-conversation' => $this->toggleConversation($conversation, 'blocked', __('messaging.feedback.conversation.blocked'), __('messaging.feedback.conversation.unblocked')),
            'restrict-conversation' => $this->toggleConversation($conversation, 'restricted', __('messaging.feedback.conversation.restricted'), __('messaging.feedback.conversation.restriction_removed')),
            'set-message-notifications' => $this->setNotifications($conversation, $data),
            'react-message' => $this->react($data),
            'pin-message' => $this->toggleMessageFlag($data, 'pinned_messages', __('messaging.feedback.message.pinned'), __('messaging.feedback.message.unpinned')),
            'bookmark-message' => $this->toggleMessageFlag($data, 'bookmarked_messages', __('messaging.feedback.message.bookmarked'), __('messaging.feedback.message.bookmark_removed')),
            'edit-message' => $this->edit($data),
            'delete-message-self' => $this->delete($data, 'self'),
            'delete-message-everyone' => $this->delete($data, 'everyone'),
            'report-message' => $this->report($conversation, $data),
            'vote-chat-poll' => $this->vote($conversation, $data),
            'update-chat-task' => $this->updateTask($conversation, $data),
            'start-message-call' => $this->startCall($conversation, $data),
            'update-message-call' => $this->updateCall($conversation, $data),
            'end-message-call' => $this->endCall($conversation),
            'export-conversation' => __('messaging.feedback.conversation.export_prepared'),
            default => throw ValidationException::withMessages(['action' => __('messaging.feedback.errors.action_unavailable')]),
        };

        return [
            'message' => $message,
            'route' => 'messages.index',
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
                'body' => __('messaging.feedback.errors.request_pending'),
            ]);
        }

        if (($this->state->conversation($conversation)['blocked'] ?? false) === true) {
            throw ValidationException::withMessages([
                'body' => __('messaging.feedback.errors.conversation_blocked'),
            ]);
        }

        $this->state->addMessage($conversation, $data);
        $type = (string) ($data['message_type'] ?? 'text');

        return match ($type) {
            'audio' => __('messaging.feedback.send.audio'),
            'image' => __('messaging.feedback.send.image'),
            'video' => __('messaging.feedback.send.video'),
            'file' => __('messaging.feedback.send.file'),
            'pet' => __('messaging.feedback.send.pet'),
            'place' => __('messaging.feedback.send.place'),
            'event' => __('messaging.feedback.send.event'),
            'task' => __('messaging.feedback.send.task'),
            default => ($data['silent'] ?? 'no') === 'yes'
                ? __('messaging.feedback.send.silent')
                : __('messaging.feedback.send.sent'),
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

        return __('messaging.feedback.conversation.marked_unread');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function setNotifications(string $conversation, array $data): string
    {
        $level = (string) $data['notification_level'];
        $this->state->setConversationValue($conversation, 'notification_level', $level);

        return __('messaging.feedback.conversation.notifications_set', [
            'level' => __('messaging.feedback.notification_levels.'.$level),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function react(array $data): string
    {
        $active = $this->state->react((string) $data['message'], (string) $data['reaction']);

        return $active ? __('messaging.feedback.message.reaction_added') : __('messaging.feedback.message.reaction_removed');
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
                'body' => __('messaging.feedback.errors.message_not_editable'),
            ]);
        }

        return __('messaging.feedback.message.updated');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function delete(array $data, string $scope): string
    {
        $this->state->deleteMessage((string) $data['message'], $scope);

        return $scope === 'everyone' ? __('messaging.feedback.message.removed_everyone') : __('messaging.feedback.message.removed_self');
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

        return __('messaging.feedback.message.reported');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function vote(string $conversation, array $data): string
    {
        $this->state->vote($conversation, (string) $data['poll_option']);

        return __('messaging.feedback.poll_recorded');
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

        return __('messaging.feedback.task_updated');
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

        return __('messaging.feedback.call.preflight_opened');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function updateCall(string $conversation, array $data): string
    {
        $control = (string) $data['call_control'];
        $this->state->updateCall($conversation, $control);

        return match ($control) {
            'join' => __('messaging.feedback.call.connected'),
            'audio-only' => __('messaging.feedback.call.audio_only'),
            'reconnect' => __('messaging.feedback.call.reconnected'),
            default => __('messaging.feedback.call.control_updated'),
        };
    }

    private function endCall(string $conversation): string
    {
        $this->state->endCall($conversation);

        return __('messaging.feedback.call.ended');
    }
}
