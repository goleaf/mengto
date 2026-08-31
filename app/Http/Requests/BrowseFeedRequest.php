<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class BrowseFeedRequest extends FormRequest
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
            'feed' => [
                'nullable',
                Rule::in([
                    'home',
                    'following',
                    'friends',
                    'pets',
                    'local',
                    'groups',
                    'experts',
                    'shelters',
                    'alerts',
                    'video',
                    'photos',
                    'saved',
                    'drafts',
                    'archive',
                ]),
            ],
            'sort' => ['nullable', Rule::in(['recommended', 'latest'])],
            'type' => [
                'nullable',
                Rule::in([
                    'all',
                    'text',
                    'photo',
                    'video',
                    'question',
                    'poll',
                    'event',
                    'lost',
                    'adoption',
                    'expert',
                    'group',
                    'repost',
                ]),
            ],
            'pet' => [
                'nullable',
                'string',
                'max:120',
                'regex:/^(all|dogs|cats|[a-z0-9][a-z0-9-]*)$/',
            ],
            'page' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }
}
