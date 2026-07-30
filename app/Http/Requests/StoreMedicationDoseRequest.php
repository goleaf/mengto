<?php

namespace App\Http\Requests;

use App\Enums\MedicationDoseStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicationDoseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'medication_id' => ['required', 'integer', 'exists:medications,id'],
            'idempotency_key' => ['required', 'uuid'],
            'scheduled_for' => ['required', 'date'],
            'administered_at' => ['nullable', 'date'],
            'status' => [
                'required',
                Rule::in(array_column(MedicationDoseStatus::cases(), 'value')),
            ],
            'dose_given' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1500'],
        ];
    }
}
