<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class BrowsePetFriendsRequest extends FormRequest
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
            'pet' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/'],
            'tab' => ['nullable', Rule::in(['friends', 'requests', 'discover', 'walks'])],
            'intent' => ['nullable', Rule::in(['all', 'walk', 'play', 'training', 'neighbor'])],
            'sort' => ['nullable', Rule::in(['compatibility', 'recent', 'name'])],
            'q' => ['nullable', 'string', 'max:80'],
        ];
    }
}
