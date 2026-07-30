<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PerformMessageActionRequest extends FormRequest
{
    private const CONVERSATIONS = [
        'ari',
        'family-care',
        'vingis-walk',
        'paws-vet',
        'foster-adoption',
        'lost-luna',
        'trail-tails',
        'luna-request',
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $action = (string) $this->input('action');
        $messageActions = [
            'react-message',
            'pin-message',
            'bookmark-message',
            'edit-message',
            'delete-message-self',
            'delete-message-everyone',
            'report-message',
        ];

        return [
            'action' => [
                'required',
                Rule::in([
                    'send-message',
                    'accept-message-request',
                    'decline-message-request',
                    'archive-conversation',
                    'pin-conversation',
                    'mute-conversation',
                    'mark-conversation-unread',
                    'block-conversation',
                    'restrict-conversation',
                    'set-message-notifications',
                    'react-message',
                    'pin-message',
                    'bookmark-message',
                    'edit-message',
                    'delete-message-self',
                    'delete-message-everyone',
                    'report-message',
                    'vote-chat-poll',
                    'update-chat-task',
                    'start-message-call',
                    'update-message-call',
                    'end-message-call',
                    'export-conversation',
                ]),
            ],
            'conversation' => ['required', Rule::in(self::CONVERSATIONS)],
            'message' => [
                Rule::requiredIf(in_array($action, $messageActions, true)),
                'nullable',
                'string',
                'max:80',
                'regex:/^[A-Za-z0-9-]+$/',
            ],
            'body' => [
                Rule::requiredIf(in_array($action, ['send-message', 'edit-message', 'report-message'], true)),
                'nullable',
                'string',
                'max:4000',
            ],
            'message_type' => [
                Rule::requiredIf($action === 'send-message'),
                'nullable',
                Rule::in(['text', 'audio', 'image', 'video', 'file', 'pet', 'place', 'event', 'task']),
            ],
            'reply_to' => ['nullable', 'string', 'max:180'],
            'silent' => ['nullable', Rule::in(['yes', 'no'])],
            'scheduled_for' => ['nullable', 'date_format:Y-m-d\TH:i', 'after:now'],
            'reaction' => [
                Rule::requiredIf($action === 'react-message'),
                'nullable',
                Rule::in(['like', 'support', 'thanks', 'funny', 'understood', 'care']),
            ],
            'notification_level' => [
                Rule::requiredIf($action === 'set-message-notifications'),
                'nullable',
                Rule::in(['all', 'mentions', 'calls', 'important', 'organizational', 'muted']),
            ],
            'poll_option' => [
                Rule::requiredIf($action === 'vote-chat-poll'),
                'nullable',
                Rule::in(['saturday-morning', 'saturday-evening', 'sunday-morning']),
            ],
            'task' => [
                Rule::requiredIf($action === 'update-chat-task'),
                'nullable',
                Rule::in(['evening-walk', 'buy-food', 'sector-c', 'photo-before-friday']),
            ],
            'task_status' => [
                Rule::requiredIf($action === 'update-chat-task'),
                'nullable',
                Rule::in(['assigned', 'in-progress', 'completed', 'skipped']),
            ],
            'call_type' => [
                Rule::requiredIf($action === 'start-message-call'),
                'nullable',
                Rule::in(['audio', 'video']),
            ],
            'call_control' => [
                Rule::requiredIf($action === 'update-message-call'),
                'nullable',
                Rule::in(['join', 'microphone', 'camera', 'captions', 'audio-only', 'reconnect']),
            ],
            'recording_consent' => ['nullable', Rule::in(['yes', 'no'])],
            'report_reason' => [
                Rule::requiredIf($action === 'report-message'),
                'nullable',
                Rule::in([
                    'spam',
                    'fraud',
                    'threat',
                    'harassment',
                    'blackmail',
                    'personal-data',
                    'dangerous-medical-advice',
                    'animal-cruelty',
                    'illegal-content',
                    'fake-specialist',
                    'other',
                ]),
            ],
            'return_filter' => [
                'nullable',
                Rule::in([
                    'all',
                    'unread',
                    'friends',
                    'groups',
                    'events',
                    'specialists',
                    'organizations',
                    'family',
                    'requests',
                    'archived',
                ]),
            ],
        ];
    }
}
