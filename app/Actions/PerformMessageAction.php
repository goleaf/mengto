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
            throw ValidationException::withMessages(['conversation' => __('messages.this_conversation_is_unavailable_801e86401b')]);
        }

        $message = match ($action) {
            'send-message' => $this->send($conversation, $data),
            'accept-message-request' => $this->resolveRequest($conversation, 'accepted', __('messages.message_request_accepted_0c08e1d6e1')),
            'decline-message-request' => $this->resolveRequest($conversation, 'declined', __('messages.message_request_declined_180fa2e142')),
            'archive-conversation' => $this->toggleConversation($conversation, 'archived', __('messages.conversation_archived_aebab4cd5c'), __('messages.conversation_restored_653c65d6fe')),
            'pin-conversation' => $this->toggleConversation($conversation, 'pinned', __('messages.conversation_pinned_e595a88362'), __('messages.conversation_unpinned_8c49a174f5')),
            'mute-conversation' => $this->toggleConversation($conversation, 'muted', __('messages.conversation_muted_f75205fb71'), __('messages.conversation_notifications_restored_fe16eb4491')),
            'mark-conversation-unread' => $this->markUnread($conversation),
            'block-conversation' => $this->toggleConversation($conversation, 'blocked', __('messages.sender_and_managed_profiles_blocked_c112a8cfcf'), __('messages.sender_unblocked_13863b943b')),
            'restrict-conversation' => $this->toggleConversation($conversation, 'restricted', __('messages.conversation_restricted_2973c1fd96'), __('messages.conversation_restriction_removed_c9d1bd0eb7')),
            'set-message-notifications' => $this->setNotifications($conversation, $data),
            'react-message' => $this->react($data),
            'pin-message' => $this->toggleMessageFlag($data, 'pinned_messages', __('messages.message_pinned_fe4ed67675'), __('messages.message_unpinned_f27b00a8c9')),
            'bookmark-message' => $this->toggleMessageFlag($data, 'bookmarked_messages', __('messages.saved_to_private_bookmarks_b82f79798b'), __('messages.removed_from_bookmarks_a109a62984')),
            'edit-message' => $this->edit($data),
            'delete-message-self' => $this->delete($data, 'self'),
            'delete-message-everyone' => $this->delete($data, 'everyone'),
            'report-message' => $this->report($conversation, $data),
            'vote-chat-poll' => $this->vote($conversation, $data),
            'update-chat-task' => $this->updateTask($conversation, $data),
            'start-message-call' => $this->startCall($conversation, $data),
            'update-message-call' => $this->updateCall($conversation, $data),
            'end-message-call' => $this->endCall($conversation),
            'export-conversation' => __('messages.export_request_prepared_only_your_accessible_data_will_b_974c9e2c64'),
            default => throw ValidationException::withMessages(['action' => __('messages.this_messaging_action_is_unavailable_e3554513da')]),
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
                'body' => __('messages.accept_this_message_request_before_replying_or_sharing_m_6530370b14'),
            ]);
        }

        if (($this->state->conversation($conversation)['blocked'] ?? false) === true) {
            throw ValidationException::withMessages([
                'body' => __('messages.unblock_this_conversation_before_sending_a_message_33eabc5f9f'),
            ]);
        }

        $this->state->addMessage($conversation, $data);
        $type = (string) ($data['message_type'] ?? 'text');

        return match ($type) {
            'audio' => __('messages.audio_message_added_with_a_transcription_request_d39ee4a517'),
            'image' => __('messages.photo_added_location_metadata_is_excluded_by_default_e2348397da'),
            'video' => __('messages.video_added_to_the_resumable_upload_queue_5a23f71f46'),
            'file' => __('messages.document_added_to_the_security_scan_queue_cded9864f4'),
            'pet' => __('messages.pet_profile_card_shared_with_public_fields_only_9453437470'),
            'place' => __('messages.place_card_shared_without_a_private_home_location_7f8e9c2935'),
            'event' => __('messages.event_card_shared_in_the_conversation_3211dd8c5b'),
            'task' => __('messages.shared_task_added_e69d49f198'),
            default => ($data['silent'] ?? 'no') === 'yes'
                ? __('messages.message_sent_without_a_sound_notification_296c84e72f')
                : __('messages.message_sent_a3d34439a0'),
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

        return __('messages.conversation_marked_unread_for_you_73acb2be1b');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function setNotifications(string $conversation, array $data): string
    {
        $level = (string) $data['notification_level'];
        $this->state->setConversationValue($conversation, 'notification_level', $level);

        return __('messages.message.notifications_set', [
            'level' => str($level)->headline()->toString(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function react(array $data): string
    {
        $active = $this->state->react((string) $data['message'], (string) $data['reaction']);

        return $active ? __('messages.reaction_added_5b36c76c3f') : __('messages.reaction_removed_edfe329c55');
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
                'body' => __('messages.only_a_message_sent_in_this_session_can_be_edited_75b676c977'),
            ]);
        }

        return __('messages.message_updated_with_an_edited_label_a9af5f151a');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function delete(array $data, string $scope): string
    {
        $this->state->deleteMessage((string) $data['message'], $scope);

        return $scope === 'everyone' ? __('messages.message_removed_for_everyone_74aa1f4774') : __('messages.message_removed_only_for_you_0ccd2bc178');
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

        return __('messages.report_submitted_with_the_selected_message_context_d42fb821c1');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function vote(string $conversation, array $data): string
    {
        $this->state->vote($conversation, (string) $data['poll_option']);

        return __('messages.poll_response_recorded_27c562b944');
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

        return __('messages.shared_task_status_updated_8c2e74e0d3');
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

        return __('messages.call_preflight_opened_realtime_media_starts_only_after_d_108097123b');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function updateCall(string $conversation, array $data): string
    {
        $control = (string) $data['call_control'];
        $this->state->updateCall($conversation, $control);

        return match ($control) {
            'join' => __('messages.call_session_connected_in_prototype_mode_7e86dee9ca'),
            'audio-only' => __('messages.call_switched_to_audio_only_mode_9335c3cc92'),
            'reconnect' => __('messages.call_session_reconnected_9041b41e97'),
            default => __('messages.call_control_updated_e5faf6db03'),
        };
    }

    private function endCall(string $conversation): string
    {
        $this->state->endCall($conversation);

        return __('messages.call_ended_and_temporary_device_access_released_c5ef8a9894');
    }
}
