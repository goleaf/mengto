<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PerformForumActionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $action = $this->string('action')->toString();

        return [
            'action' => ['required', Rule::in([
                'toggle-bookmark',
                'set-subscription',
                'vote-answer',
                'accept-answer',
                'resolve-topic',
                'reopen-topic',
                'report-topic',
                'report-answer',
                'block-author',
                'mark-notification-read',
                'convert-to-knowledge',
            ])],
            'topic_id' => [
                Rule::requiredIf(in_array($action, [
                    'toggle-bookmark',
                    'set-subscription',
                    'resolve-topic',
                    'reopen-topic',
                    'report-topic',
                    'convert-to-knowledge',
                ], true)),
                'nullable',
                'integer',
                'exists:forum_topics,id',
            ],
            'answer_id' => [
                Rule::requiredIf(in_array($action, ['vote-answer', 'accept-answer', 'report-answer'], true)),
                'nullable',
                'integer',
                'exists:forum_answers,id',
            ],
            'notification_id' => [
                Rule::requiredIf($action === 'mark-notification-read'),
                'nullable',
                'integer',
                'exists:forum_notifications,id',
            ],
            'author_key' => [
                Rule::requiredIf($action === 'block-author'),
                'nullable',
                'string',
                'max:80',
                'regex:/^[a-z0-9-]+$/',
            ],
            'value' => [
                Rule::requiredIf(in_array($action, ['set-subscription', 'vote-answer'], true)),
                'nullable',
                'string',
                'max:80',
            ],
            'reason' => [
                Rule::requiredIf(in_array($action, ['report-topic', 'report-answer'], true)),
                'nullable',
                Rule::in([
                    'spam',
                    'duplicate',
                    'dangerous-advice',
                    'misinformation',
                    'animal-cruelty',
                    'fraud',
                    'personal-data',
                    'harassment',
                    'hidden-advertising',
                    'illegal-sale',
                    'copyright',
                    'other',
                ]),
            ],
            'details' => ['nullable', 'string', 'max:1200'],
        ];
    }
}
