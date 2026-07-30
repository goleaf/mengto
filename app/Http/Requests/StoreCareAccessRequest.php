<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCareAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'recipient_key' => ['nullable', 'string', 'max:80', 'regex:/^[a-z0-9-]+$/'],
            'recipient_name' => ['required', 'string', 'max:160'],
            'recipient_role' => [
                'required',
                Rule::in([
                    'co-owner', 'family', 'sitter', 'veterinarian',
                    'trainer', 'groomer', 'shelter', 'specialist',
                ]),
            ],
            'label' => ['required', 'string', 'max:180'],
            'sections' => ['required', 'array', 'min:1', 'max:10'],
            'sections.*' => [
                'required',
                Rule::in([
                    'summary', 'feeding', 'water', 'walks', 'toilet',
                    'sleep', 'activity', 'care', 'observations', 'tasks',
                ]),
            ],
            'allow_add' => ['nullable', 'boolean'],
            'allow_location' => ['nullable', 'boolean'],
            'allow_media' => ['nullable', 'boolean'],
            'max_views' => ['required', 'integer', 'min:1', 'max:200'],
            'expires_in_hours' => ['required', 'integer', 'min:1', 'max:720'],
            'privacy_acknowledged' => ['accepted'],
        ];
    }
}
