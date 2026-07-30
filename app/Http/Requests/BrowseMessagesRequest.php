<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class BrowseMessagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'conversation' => [
                'nullable',
                Rule::in([
                    'ari',
                    'family-care',
                    'vingis-walk',
                    'paws-vet',
                    'foster-adoption',
                    'lost-luna',
                    'trail-tails',
                    'luna-request',
                ]),
            ],
            'filter' => [
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
            'q' => ['nullable', 'string', 'max:80'],
            'message_q' => ['nullable', 'string', 'max:120'],
            'channel' => ['nullable', 'string', 'max:40', 'regex:/^[a-z0-9-]+$/'],
            'panel' => ['nullable', Rule::in(['context', 'members', 'media', 'search', 'privacy', 'call'])],
        ];
    }
}
