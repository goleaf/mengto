<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrowseProfileRequest extends FormRequest
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
            'tab' => [
                'nullable',
                'string',
                Rule::in([
                    'overview',
                    'pets',
                    'posts',
                    'about',
                    'feed',
                    'photos',
                    'friends',
                    'care',
                    'family',
                ]),
            ],
            'view' => [
                'nullable',
                'string',
                Rule::in(['owner', 'public', 'follower', 'friend']),
            ],
        ];
    }
}
