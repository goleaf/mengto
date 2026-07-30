<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicalAccessRequest extends FormRequest
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
                    'veterinarian', 'clinic', 'caregiver', 'co-owner',
                    'sitter', 'groomer', 'rehabilitation-specialist', 'shelter',
                ]),
            ],
            'label' => ['required', 'string', 'max:180'],
            'sections' => ['required', 'array', 'min:1', 'max:8'],
            'sections.*' => [
                'required',
                Rule::in([
                    'summary', 'emergency', 'timeline', 'medications',
                    'vaccinations', 'weight', 'documents', 'reminders',
                ]),
            ],
            'allow_download' => ['nullable', 'boolean'],
            'allow_edit' => ['nullable', 'boolean'],
            'max_views' => ['required', 'integer', 'min:1', 'max:100'],
            'expires_in_hours' => ['required', 'integer', 'min:1', 'max:720'],
            'privacy_acknowledged' => ['accepted'],
        ];
    }
}
