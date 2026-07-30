<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PerformExpertActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in([
                'toggle-save',
                'toggle-subscribe',
                'submit-verification',
                'report',
            ])],
            'reason' => [
                Rule::requiredIf($this->input('action') === 'report'),
                'nullable',
                Rule::in([
                    'false-qualification',
                    'dangerous-advice',
                    'forged-document',
                    'hidden-advertising',
                    'fraud',
                    'threats',
                    'medical-data-exposure',
                    'animal-cruelty',
                    'service-not-delivered',
                    'other',
                ]),
            ],
            'details' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
