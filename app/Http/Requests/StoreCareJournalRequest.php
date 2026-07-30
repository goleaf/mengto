<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCareJournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'pet_profile_key' => ['required', Rule::in(['scout', 'nori'])],
            'timezone' => ['required', 'timezone:all'],
            'current_caregiver_name' => ['nullable', 'string', 'max:120'],
            'privacy_acknowledged' => ['accepted'],
        ];
    }
}
