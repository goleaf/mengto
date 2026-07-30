<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PerformBookingActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in([
                'cancel',
                'request-reschedule',
                'revoke-document',
                'complete-consultation',
            ])],
            'reason' => ['nullable', 'string', 'max:500'],
            'document_grant_id' => [
                Rule::requiredIf($this->input('action') === 'revoke-document'),
                'nullable',
                'integer',
            ],
            'client_summary' => [
                Rule::requiredIf($this->input('action') === 'complete-consultation'),
                'nullable',
                'string',
                'min:30',
                'max:6000',
            ],
            'action_plan' => ['nullable', 'array', 'max:12'],
            'action_plan.*' => ['nullable', 'string', 'max:500'],
            'referral_summary' => ['nullable', 'string', 'max:2000'],
            'follow_up_until' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}
