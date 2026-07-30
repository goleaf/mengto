<?php

namespace App\Http\Requests;

use App\Enums\CareEntryStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCareTaskCompletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'idempotency_key' => ['required', 'uuid'],
            'status' => [
                'required',
                Rule::in([
                    CareEntryStatus::Completed->value,
                    CareEntryStatus::Partial->value,
                    CareEntryStatus::Refused->value,
                    CareEntryStatus::Skipped->value,
                    CareEntryStatus::NeedsHelp->value,
                ]),
            ],
            'completion_note' => ['nullable', 'string', 'max:2000'],
            'confirm_duplicate' => ['nullable', 'boolean'],
        ];
    }
}
